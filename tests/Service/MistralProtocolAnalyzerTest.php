<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\ProtocolFigure;
use App\Service\MistralProtocolAnalyzer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class MistralProtocolAnalyzerTest extends TestCase
{
    /**
     * Extrait réel d'un protocole FFE Club 2 Grand Prix tel que rendu par
     * l'OCR Mistral : deux pages, la seconde sans ligne d'en-tête.
     */
    private const PAGE_ONE = <<<'MARKDOWN'
        |  Fig | MOUVEMENTS | IDEES DIRECTRICES | Note 0 à 10 | Co en | OBSERVATIONS  |
        | --- | --- | --- | --- | --- | --- |
        |  1 | A Entrée au trot de travail X Arrêt progressif. | Rectitude, fluidité des transitions. | 6,5 | 1 | 1 peu ouvert  |
        |  2 | C Piste à main gauche HS Trot de travail | Maintien du mouvement en avant. | 6,5 | 1 | Ø ds coin M  |
        |  3 | SF Changement de main | Activité et augmentation des foulées. | 6,0 | 1 |   |
        MARKDOWN;

    private const PAGE_TWO = <<<'MARKDOWN'
        |  4 | B Cercle à droite de 15 m | Maintien d'un léger pli interne. | 7,0 | 1 |   |
        | --- | --- | --- | --- | --- | --- |
        |  5 | I Arrêt progressif. Immobilité. Salut | Le cheval est droit. | 6,5 | 1 | se décale  |
        |  Quitter la piste au pas libre. |   |   |  |  |   |
        |  TOTAL/220 |   |   |  |  | Commentaires  |
        |  **NOTES D'ENSEMBLE** |   |   |  |  |   |
        |  1 | Allures (Conformité de chacune des 3 allures) |   | 6,5 | 1 | à l'attitude qui se dégrade  |
        |  2 | Impulsion (Le cheval se porte en avant) |   | 6,0 | 2 |   |
        |  Total/300 |   |   | 187,5 pts |  |   |
        |  POURCENTAGE EVENTUEL A DEDUIRE |   |   |  |  | Conversion en pourcentage soit 62,5 %  |
        MARKDOWN;

    public function testAnalyzeReadsScoresFromBothPages(): void
    {
        $result = $this->analyzeMarkdown();

        $scores = [];

        foreach ($result->figures as $figure) {
            $scores[$figure->section . ':' . $figure->number] = $figure->score;
        }

        self::assertSame(6.5, $scores['technical:1']);
        self::assertSame(6.5, $scores['technical:2']);
        self::assertSame(6.0, $scores['technical:3']);

        // La page 2 n'a pas d'en-tête : elle doit malgré tout être lue.
        self::assertSame(7.0, $scores['technical:4']);
        self::assertSame(6.5, $scores['technical:5']);
    }

    public function testAnalyzeSeparatesCollectiveMarksFromTechnicalFigures(): void
    {
        $result = $this->analyzeMarkdown();

        $scores = [];

        foreach ($result->figures as $figure) {
            $scores[$figure->section . ':' . $figure->number] = $figure->score;
        }

        // La numérotation repart à 1 : les notes d'ensemble ne doivent pas
        // écraser les figures techniques 1 et 2.
        self::assertSame(6.5, $scores['collective:1']);
        self::assertSame(6.0, $scores['collective:2']);
        self::assertSame(6.5, $scores['technical:1']);
        self::assertSame(6.5, $scores['technical:2']);
    }

    public function testAnalyzeNeverMistakesCoefficientForScore(): void
    {
        $result = $this->analyzeMarkdown();

        foreach ($result->figures as $figure) {
            if ($figure->score !== null) {
                self::assertGreaterThanOrEqual(4.0, $figure->score);
            }
        }
    }

    public function testAnalyzeExtractsOnlyHandwrittenComments(): void
    {
        $result = $this->analyzeMarkdown();

        $comments = [];

        foreach ($result->figures as $figure) {
            $comments[$figure->section . ':' . $figure->number] = $figure->comment;
        }

        self::assertSame('1 peu ouvert', $comments['technical:1']);
        self::assertNull($comments['technical:3']);

        // Le libellé imprimé ("Idées directrices") ne doit jamais être pris
        // pour une annotation du juge.
        self::assertNull($comments['collective:2']);
    }

    public function testAnalyzeExtractsJudgeDeclaredTotals(): void
    {
        $result = $this->analyzeMarkdown();

        self::assertSame(187.5, $result->declaredTotal);
        self::assertSame(62.5, $result->declaredPercentage);
    }

    public function testAnalyzeReturnsEmptyReadingForUnreadFigure(): void
    {
        $result = $this->analyzeMarkdown([
            ProtocolFigure::SECTION_TECHNICAL => [1, 2, 3, 4, 5, 6],
            ProtocolFigure::SECTION_COLLECTIVE => [1, 2],
        ]);

        $missing = null;

        foreach ($result->figures as $figure) {
            if ($figure->section === ProtocolFigure::SECTION_TECHNICAL && $figure->number === 6) {
                $missing = $figure;
            }
        }

        // Une figure absente du document produit une lecture vide,
        // jamais une figure manquante : l'applier ne doit pas échouer.
        self::assertNotNull($missing);
        self::assertNull($missing->score);
        self::assertSame(0.0, $missing->confidence);
    }

    public function testAnalyzeThrowsQuotaExceptionOnTooManyRequests(): void
    {
        $client = new MockHttpClient(new MockResponse('{"message":"rate limited"}', ['http_code' => 429]));
        $analyzer = new MistralProtocolAnalyzer($client, 'test-key', 'mistral-ocr-latest');

        $this->expectException(\App\Exception\QuotaExceededException::class);

        $analyzer->analyze($this->fixtureFile(), [ProtocolFigure::SECTION_TECHNICAL => [1]], 'C');
    }

    /**
     * @param array<string, int[]>|null $expectedFigures
     */
    private function analyzeMarkdown(?array $expectedFigures = null): \App\Dto\AnalysisResult
    {
        $body = json_encode([
            'pages' => [
                ['markdown' => self::PAGE_ONE],
                ['markdown' => self::PAGE_TWO],
            ],
            'document_annotation' => json_encode([
                'test_label' => 'Club 2 Grand Prix',
                'general_comment' => null,
            ]),
        ], JSON_THROW_ON_ERROR);

        $client = new MockHttpClient(new MockResponse($body, [
            'response_headers' => ['content-type' => 'application/json'],
        ]));

        $analyzer = new MistralProtocolAnalyzer($client, 'test-key', 'mistral-ocr-latest');

        return $analyzer->analyze(
            $this->fixtureFile(),
            $expectedFigures ?? [
                ProtocolFigure::SECTION_TECHNICAL => [1, 2, 3, 4, 5],
                ProtocolFigure::SECTION_COLLECTIVE => [1, 2],
            ],
            'C',
        );
    }

    private function fixtureFile(): string
    {
        $path = sys_get_temp_dir() . '/protocol-fixture.pdf';

        if (!file_exists($path)) {
            file_put_contents($path, "%PDF-1.4\n%%EOF\n");
        }

        return $path;
    }
}