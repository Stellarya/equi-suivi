<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\AnalysisResult;
use App\Dto\FigureReading;
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

    public function analyze(
        string $filePath,
        array $expectedFigureNumbers,
        string $judgePosition,
    ): AnalysisResult {
        $response = $this->httpClient->request('POST', self::ENDPOINT, [
            'auth_bearer' => $this->mistralApiKey,
            'timeout' => 120,
            'json' => [
                'model' => $this->mistralOcrModel,
                'document' => $this->buildDocumentChunk($filePath),
                'document_annotation_prompt' => $this->buildPrompt($expectedFigureNumbers),
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

        $annotation = json_decode(
            $payload['document_annotation'] ?? '',
            true,
            flags: JSON_THROW_ON_ERROR,
        );

        return $this->toAnalysisResult($annotation, $judgePosition);
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
     * @param int[] $expectedFigureNumbers
     */
    private function buildPrompt(array $expectedFigureNumbers): string
    {
        return sprintf(
            'Ce document est un protocole de dressage équestre de la FFE, éventuellement sur plusieurs pages. '
            . 'Extrais la note manuscrite et le commentaire manuscrit de chaque ligne du tableau. '
            . 'Le document comporte exactement %d figures, numérotées : %s. '
            . 'Retourne une entrée par figure, y compris celles que tu ne parviens pas à lire : '
            . 'dans ce cas mets score à null et confidence à 0. '
            . 'Les notes vont de 0 à 10 par pas de 0,5. N\'invente jamais une note.',
            count($expectedFigureNumbers),
            implode(', ', $expectedFigureNumbers),
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
            'required' => ['test_label', 'general_comment', 'figures'],
            'properties' => [
                'test_label' => ['type' => ['string', 'null']],
                'general_comment' => ['type' => ['string', 'null']],
                'figures' => [
                    'type' => 'array',
                    'items' => [
                        'type' => 'object',
                        'additionalProperties' => false,
                        'required' => ['number', 'score', 'comment', 'confidence'],
                        'properties' => [
                            'number' => ['type' => 'integer'],
                            'score' => ['type' => ['number', 'null']],
                            'comment' => ['type' => ['string', 'null']],
                            'confidence' => ['type' => 'number'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * @param array<string, mixed> $annotation
     */
    private function toAnalysisResult(array $annotation, string $judgePosition): AnalysisResult
    {
        $figures = [];

        foreach ($annotation['figures'] ?? [] as $row) {
            $figures[] = new FigureReading(
                number: (int) $row['number'],
                score: $this->parseScore($row['score'] ?? null),
                comment: $row['comment'] ?? null,
                confidence: (float) ($row['confidence'] ?? 0.0),
            );
        }

        return new AnalysisResult(
            judgePosition: $judgePosition,
            testLabel: $annotation['test_label'] ?? null,
            generalComment: $annotation['general_comment'] ?? null,
            figures: $figures,
        );
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