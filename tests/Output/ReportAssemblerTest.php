<?php

declare(strict_types=1);

namespace TsaRepere\Tests\Output;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TsaRepere\Output\ReportAssembler;
use TsaRepere\Psychometric\LicenseRegistry;
use TsaRepere\Psychometric\ScoringEngine;
use TsaRepere\Support\Disclaimer;
use TsaRepere\Tests\Support\Scenario;

final class ReportAssemblerTest extends TestCase
{
    private const string REGISTRY = __DIR__ . '/../../packages/instrument-definitions/license_registry.json';
    private const string OUTPUT_SCHEMA = __DIR__ . '/../../packages/assessment-schema/schemas/assessment_output.schema.json';

    #[Test]
    public function it_assembles_four_separate_outputs_with_ml_disabled(): void
    {
        $form = Scenario::form();
        $report = (new ReportAssembler())->assemble(
            Scenario::strongAdult($form),
            $form,
            '2026-07-13T10:00:00+00:00',
        );

        // ML desactivable : reste nul tant qu'aucun modele valide n'est branche.
        self::assertNull($report['ml_estimate']);
        // Les sorties restent separees, jamais fusionnees en un score global.
        self::assertArrayHasKey('psychometric_results', $report);
        self::assertArrayHasKey('clinical_structure', $report);
        self::assertArrayHasKey('orientation', $report);
        self::assertArrayHasKey('completeness', $report);
        self::assertSame(Disclaimer::STANDARD, $report['disclaimer']);
    }

    #[Test]
    public function psychometric_results_carry_entered_scores(): void
    {
        $form = Scenario::form();
        $scoring = new ScoringEngine(LicenseRegistry::fromJsonFile(self::REGISTRY));
        $instrument = $scoring->recordEnteredScore('AQ-10-adult', '1.0', 7.0);

        $report = (new ReportAssembler())->assemble(
            Scenario::strongAdult($form),
            $form,
            '2026-07-13T10:00:00+00:00',
            [$instrument],
        );

        self::assertCount(1, $report['psychometric_results']);
        self::assertSame('AQ-10-adult', $report['psychometric_results'][0]['instrument_code']);
        self::assertSame(7.0, $report['psychometric_results'][0]['score']);
    }

    #[Test]
    public function evidence_trace_links_statements_to_source_items(): void
    {
        $form = Scenario::form();
        $report = (new ReportAssembler())->assemble(
            Scenario::strongAdult($form),
            $form,
            '2026-07-13T10:00:00+00:00',
        );

        self::assertNotEmpty($report['evidence_trace']);
        foreach ($report['evidence_trace'] as $trace) {
            self::assertNotEmpty($trace['source_refs']);
        }
    }

    #[Test]
    public function output_never_contains_a_forbidden_diagnostic_phrase(): void
    {
        $form = Scenario::form();
        foreach ([
            Scenario::strongAdult($form),
            Scenario::lowAdult($form),
            Scenario::sparseAdult(),
            Scenario::urgentAdult($form),
        ] as $assessment) {
            $report = (new ReportAssembler())->assemble($assessment, $form, '2026-07-13T10:00:00+00:00');
            $haystack = strtolower((string) json_encode($report, \JSON_UNESCAPED_UNICODE));
            foreach (Disclaimer::forbiddenPhrases() as $phrase) {
                self::assertStringNotContainsString($phrase, $haystack, \sprintf('Formulation interdite trouvee : %s', $phrase));
            }
        }
    }

    #[Test]
    public function output_conforms_to_the_declared_json_schema_shape(): void
    {
        // Verification legere de forme (les cles requises de premier niveau).
        $schema = json_decode((string) file_get_contents(self::OUTPUT_SCHEMA), true, flags: \JSON_THROW_ON_ERROR);
        $form = Scenario::form();
        $report = (new ReportAssembler())->assemble(Scenario::strongAdult($form), $form, '2026-07-13T10:00:00+00:00');

        foreach ($schema['required'] as $requiredKey) {
            self::assertArrayHasKey($requiredKey, $report, \sprintf('Cle requise absente : %s', $requiredKey));
        }
    }
}
