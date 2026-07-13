<?php

declare(strict_types=1);

namespace TsaRepere\Tests\Psychometric;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TsaRepere\Psychometric\InstrumentResult;
use TsaRepere\Psychometric\LicenseRegistry;
use TsaRepere\Psychometric\LicenseViolationException;
use TsaRepere\Psychometric\ScoringEngine;

final class ScoringEngineTest extends TestCase
{
    private const string REGISTRY = __DIR__ . '/../../packages/instrument-definitions/license_registry.json';

    private function engine(): ScoringEngine
    {
        return new ScoringEngine(LicenseRegistry::fromJsonFile(self::REGISTRY));
    }

    #[Test]
    public function it_refuses_to_compute_a_licensed_instrument_in_app(): void
    {
        $this->expectException(LicenseViolationException::class);
        $this->engine()->scoreFromBinaryItems('AQ-10-adult', '1.0', array_fill(0, 10, 1));
    }

    #[Test]
    public function it_allows_recording_a_score_entered_elsewhere(): void
    {
        $result = $this->engine()->recordEnteredScore('AQ-10-adult', '1.0', 7.0);

        self::assertSame('AQ-10-adult', $result->instrumentCode);
        self::assertSame(7.0, $result->rawScore);
        self::assertSame(6, $result->threshold);
        self::assertTrue($result->thresholdReached);
        self::assertSame(InstrumentResult::SOURCE_ENTERED_PRO, $result->source);
    }

    #[Test]
    public function a_score_below_threshold_never_excludes_asd(): void
    {
        $result = $this->engine()->recordEnteredScore('AQ-10-adult', '1.0', 3.0);

        self::assertFalse($result->thresholdReached);
        self::assertStringContainsStringIgnoringCase('n\'exclut pas', $result->officialInterpretation);
        self::assertStringContainsStringIgnoringCase('n\'est pas un diagnostic', $result->officialInterpretation);
    }

    #[Test]
    public function unknown_instrument_is_rejected(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->engine()->recordEnteredScore('DOES-NOT-EXIST', '1.0', 1.0);
    }
}
