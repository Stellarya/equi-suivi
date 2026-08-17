<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\AnalysisResult;
use App\Dto\FigureReading;
use App\Entity\ProtocolFigure;
use App\Exception\QuotaExceededException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MistralProtocolAnalyzer implements ProtocolAnalyzerInterface
{
    private const ENDPOINT = 'https://api.mistral.ai/v1/ocr';

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly string $mistralApiKey,
        private readonly string $mistralOcrModel,
    ) {}

    /**
     * @param array<string, int[]> $expectedFigures
     */
    public function analyze(string $filePath, array $expectedFigures, string $judgePosition): AnalysisResult
    {
        $response = $this->httpClient->request('POST', self::ENDPOINT, [
            'auth_bearer' => $this->mistralApiKey,
            'timeout' => 120,
            'json' => [
                'model' => $this->mistralOcrModel,
                'document' => $this->buildDocumentChunk($filePath),
                'document_annotation_prompt' => $this->buildPrompt($expectedFigures),
                'document_annotation_format' => [
                    'type' => 'json_schema',
                    'json_schema' => [
                        'name' => 'dressage_protocol',
                        'strict' => true,
                        'schema' => $this->schema(),
                    ],
                ],
            ],
        ]);

        $statusCode = $response->getStatusCode();

        if ($statusCode === 429) {
            throw new QuotaExceededException('Quota Mistral atteint (429).');
        }

        if ($statusCode >= 400) {
            throw new \RuntimeException(
                sprintf('Mistral a répondu %d : %s', $statusCode, $response->getContent(false))
            );
        }

        $payload = $response->toArray();

        $pageCount = $payload['usage_info']['pages_processed'] ?? 0;

        $annotation = json_decode($payload['document_annotation'] ?? '{}', true) ?? [];
        $parsed = $this->parseMarkdownTables($payload['pages'] ?? [], $expectedFigures);

        $figures = [];

        foreach ($expectedFigures as $section => $numbers) {
            foreach ($numbers as $number) {
                $figures[] = $parsed['readings'][$section . ':' . $number]
                    ?? new FigureReading($section, $number, null, null, 0.0);
            }
        }

        return new AnalysisResult(
            judgePosition: $judgePosition,
            testLabel: $annotation['test_label'] ?? null,
            generalComment: $annotation['general_comment'] ?? null,
            figures: $figures,
            declaredTotal: $parsed['declaredTotal'],
            declaredPercentage: $parsed['declaredPercentage'],
        );
    }

    /**
     * @param array<int, array<string, mixed>> $pages
     * @param array<string, int[]> $expectedFigures
     * @return array{
     *     readings: array<string, FigureReading>,
     *     declaredTotal: float|null,
     *     declaredPercentage: float|null
     * }
     */
    private function parseMarkdownTables(array $pages, array $expectedFigures): array
    {
        $readings = [];
        $declaredTotal = null;
        $declaredPercentage = null;

        $section = ProtocolFigure::SECTION_TECHNICAL;
        $lastNumber = null;

        foreach ($pages as $page) {
            foreach (preg_split('/\R/', (string) ($page['markdown'] ?? '')) as $line) {
                $line = trim($line);

                if ($line === '') {
                    continue;
                }

                if (preg_match('/notes\s*d.{0,3}\s*ensemble/iu', $line) === 1) {
                    $section = ProtocolFigure::SECTION_COLLECTIVE;
                    $lastNumber = null;
                    continue;
                }

                if (preg_match('#total\s*/\s*\d+#iu', $line) === 1
                    && preg_match('/(\d+(?:[.,]\d+)?)\s*pts/iu', $line, $m) === 1
                ) {
                    $declaredTotal = (float) str_replace(',', '.', $m[1]);
                    continue;
                }

                if (preg_match('/conversion en pourcentage.*?(\d+(?:[.,]\d+)?)\s*%/iu', $line, $m) === 1) {
                    $declaredPercentage = (float) str_replace(',', '.', $m[1]);
                    continue;
                }

                if (!str_starts_with($line, '|')) {
                    continue;
                }

                $cells = array_map(trim(...), explode('|', trim($line, '|')));

                if ($this->isSeparatorRow($cells)) {
                    continue;
                }

                // 1. Le numéro de figure : première cellule qui est un entier nu.
                $numberIndex = null;

                foreach ($cells as $index => $cell) {
                    if (preg_match('/^\d{1,2}$/', $cell) === 1) {
                        $numberIndex = $index;
                        break;
                    }
                }

                if ($numberIndex === null) {
                    continue;
                }

                $number = (int) $cells[$numberIndex];

                if ($section === ProtocolFigure::SECTION_TECHNICAL
                    && $lastNumber !== null
                    && $number <= $lastNumber
                ) {
                    $section = ProtocolFigure::SECTION_COLLECTIVE;
                }

                if (!in_array($number, $expectedFigures[$section] ?? [], true)) {
                    continue;
                }

                // 2. La note : première cellule décimale après le numéro.
                //    Sur les protocoles FFE la note s'écrit toujours 6,5 / 7,0,
                //    alors que le coefficient est un entier nu : aucune confusion.
                $score = null;
                $scoreIndex = null;

                for ($index = $numberIndex + 1, $count = count($cells); $index < $count; $index++) {
                    if (preg_match('/^\d{1,2}[.,]\d$/', $cells[$index]) !== 1) {
                        continue;
                    }

                    $candidate = $this->parseScore($cells[$index]);

                    if ($candidate !== null && $candidate >= 0.0 && $candidate <= 10.0) {
                        $score = $candidate;
                        $scoreIndex = $index;
                        break;
                    }
                }

                if ($score === null) {
                    continue;
                }

                // 3. Le commentaire : dernière cellule non vide APRÈS la note.
                //    Avant elle se trouvent les colonnes imprimées (mouvements,
                //    idées directrices) qu'il ne faut jamais confondre avec
                //    l'annotation manuscrite du juge.
                $comment = null;

                for ($index = count($cells) - 1; $index > $scoreIndex; $index--) {
                    $cell = $cells[$index];

                    if ($cell !== '' && preg_match('/^\d{1,2}([.,]\d)?$/', $cell) !== 1) {
                        $comment = $cell;
                        break;
                    }
                }

                $lastNumber = $number;

                $readings[$section . ':' . $number] ??= new FigureReading(
                    section: $section,
                    number: $number,
                    score: $score,
                    comment: $comment,
                    confidence: 1.0,
                );
            }
        }

        return [
            'readings' => $readings,
            'declaredTotal' => $declaredTotal,
            'declaredPercentage' => $declaredPercentage,
        ];
    }

    /**
     * @param string[] $cells
     */
    private function isSeparatorRow(array $cells): bool
    {
        if ($cells === []) {
            return false;
        }

        foreach ($cells as $cell) {
            if (preg_match('/^:?-{2,}:?$/', $cell) !== 1) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, string>
     */
    private function buildDocumentChunk(string $filePath): array
    {
        if (!is_readable($filePath)) {
            throw new \RuntimeException(sprintf('Fichier illisible : %s', $filePath));
        }

        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        $dataUri = sprintf('data:%s;base64,%s', $mimeType, base64_encode(file_get_contents($filePath)));

        if ($mimeType === 'application/pdf') {
            return ['type' => 'document_url', 'document_url' => $dataUri];
        }

        return ['type' => 'image_url', 'image_url' => $dataUri];
    }

    /**
     * @param array<string, int[]> $expectedFigures
     */
    private function buildPrompt(array $expectedFigures): string
    {
        $technical = $expectedFigures[ProtocolFigure::SECTION_TECHNICAL] ?? [];
        $collective = $expectedFigures[ProtocolFigure::SECTION_COLLECTIVE] ?? [];

        return sprintf(
            'Protocole de dressage FFE sur plusieurs pages. '
            . 'Le tableau principal contient %d figures techniques numérotées %s. '
            . 'Le bloc "NOTES D\'ENSEMBLE" contient %d lignes dont la numérotation repart à 1 (%s). '
            . 'Pour chaque ligne, extrais la note manuscrite et le commentaire manuscrit. '
            . 'Ignore les lignes TOTAL, pénalités et pourcentages. '
            . 'Mets score à null pour toute note que tu ne lis pas avec certitude. N\'invente jamais une note.',
            count($technical),
            implode(', ', $technical),
            count($collective),
            implode(', ', $collective),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function schema(): array
    {
        return [
            'type' => 'object',
            'additionalProperties' => false,
            'required' => ['test_label', 'general_comment'],
            'properties' => [
                'test_label' => ['type' => ['string', 'null']],
                'general_comment' => ['type' => ['string', 'null']],
            ],
        ];
    }

    private function parseScore(mixed $raw): ?float
    {
        if ($raw === null) {
            return null;
        }

        if (is_int($raw) || is_float($raw)) {
            return (float) $raw;
        }

        if (!is_string($raw)) {
            return null;
        }

        $normalized = str_replace(',', '.', trim($raw));

        if ($normalized === '' || !is_numeric($normalized)) {
            return null;
        }

        return (float) $normalized;
    }
}