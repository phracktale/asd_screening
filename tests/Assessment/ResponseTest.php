<?php

declare(strict_types=1);

namespace TsaRepere\Tests\Assessment;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TsaRepere\Assessment\Assessment;
use TsaRepere\Assessment\DifferentialStatus;
use TsaRepere\Assessment\Response;
use TsaRepere\Form\AgeBand;
use TsaRepere\Form\Period;

final class ResponseTest extends TestCase
{
    #[Test]
    public function a_categorical_unknown_is_never_read_as_diagnosed(): void
    {
        // Regression du bug "unknown" contient "known" (review P0-2).
        $response = Response::categorical('F.01', Period::Lifetime, DifferentialStatus::Unknown);

        self::assertTrue($response->isCategorical());
        self::assertSame(DifferentialStatus::Unknown, $response->differentialStatus());
        self::assertNotSame(DifferentialStatus::Diagnosed, $response->differentialStatus());
        self::assertFalse($response->isAnswered());
    }

    #[Test]
    public function ordinal_and_categorical_values_are_mutually_exclusive(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Response(
            questionCode: 'F.01',
            period: Period::Lifetime,
            value: 3,
            kind: \TsaRepere\Assessment\ResponseKind::Categorical,
            categoricalValue: DifferentialStatus::Suspected->value,
        );
    }

    #[Test]
    public function upsert_keeps_the_latest_value_for_a_code_and_period(): void
    {
        // review P1-12 : le resultat ne doit pas dependre de l'ordre d'insertion.
        $assessment = new Assessment(
            id: '77777777-7777-4777-8777-777777777777',
            ageBand: AgeBand::Adult,
        );
        $assessment->addResponse(Response::ordinal('A1.01', Period::Current, 1));
        $assessment->addResponse(Response::ordinal('A1.01', Period::Current, 4)); // correction

        $responses = $assessment->responsesForCode('A1.01');
        self::assertCount(1, $responses);
        self::assertSame(4, $responses[0]->intensity());
    }
}
