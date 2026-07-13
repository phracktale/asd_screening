<?php

declare(strict_types=1);

namespace TsaRepere\Tests\Orientation;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TsaRepere\Clinical\ClinicalStructuringEngine;
use TsaRepere\Clinical\CompletenessEvaluator;
use TsaRepere\Orientation\OrientationEngine;
use TsaRepere\Orientation\SuspicionLevel;
use TsaRepere\Tests\Support\Scenario;

final class OrientationEngineTest extends TestCase
{
    private function orient(\TsaRepere\Assessment\Assessment $assessment): \TsaRepere\Orientation\OrientationResult
    {
        $form = Scenario::form();
        $structure = (new ClinicalStructuringEngine())->build($assessment, $form);
        $completeness = (new CompletenessEvaluator())->evaluate($assessment, $form, $structure);

        return (new OrientationEngine())->determine($assessment, $structure, $completeness);
    }

    #[Test]
    public function urgent_risk_short_circuits_asd_interpretation(): void
    {
        $result = $this->orient(Scenario::urgentAdult(Scenario::form()));

        self::assertSame(SuspicionLevel::Urgent, $result->level);
        self::assertNotNull($result->urgentReason);
        self::assertStringContainsStringIgnoringCase('urgence', $result->message);
    }

    #[Test]
    public function insufficient_data_yields_indeterminate(): void
    {
        $result = $this->orient(Scenario::sparseAdult());

        self::assertSame(SuspicionLevel::Indeterminate, $result->level);
    }

    #[Test]
    public function strong_convergence_yields_high(): void
    {
        $result = $this->orient(Scenario::strongAdult(Scenario::form()));

        self::assertSame(SuspicionLevel::High, $result->level);
        self::assertStringContainsStringIgnoringCase('evaluation specialisee', $result->message);
    }

    #[Test]
    public function few_convergent_elements_yield_low(): void
    {
        $result = $this->orient(Scenario::lowAdult(Scenario::form()));

        self::assertSame(SuspicionLevel::Low, $result->level);
    }

    #[Test]
    public function no_output_contains_a_categorical_diagnosis(): void
    {
        foreach ([
            Scenario::strongAdult(Scenario::form()),
            Scenario::lowAdult(Scenario::form()),
            Scenario::sparseAdult(),
        ] as $assessment) {
            $message = strtolower($this->orient($assessment)->message);
            self::assertStringContainsString('n\'est pas un diagnostic', $message);
            self::assertStringNotContainsString('vous etes autiste', $message);
        }
    }
}
