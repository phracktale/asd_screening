<?php

declare(strict_types=1);

namespace TsaRepere\Tests\Clinical;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TsaRepere\Assessment\Assessment;
use TsaRepere\Assessment\Response;
use TsaRepere\Clinical\ClinicalStructuringEngine;
use TsaRepere\Clinical\DomainStatus;
use TsaRepere\Form\AgeBand;
use TsaRepere\Form\Period;
use TsaRepere\Tests\Support\Scenario;

final class ClinicalStructuringEngineTest extends TestCase
{
    #[Test]
    public function strong_scenario_documents_social_and_rrb_domains(): void
    {
        $form = Scenario::form();
        $structure = (new ClinicalStructuringEngine())->build(Scenario::strongAdult($form), $form);

        self::assertSame(DomainStatus::Documented, $structure->domain('A1')?->status);
        self::assertSame(DomainStatus::Documented, $structure->domain('A2')?->status);
        self::assertSame(DomainStatus::Documented, $structure->domain('A3')?->status);
        self::assertSame(DomainStatus::Documented, $structure->domain('developmental_onset')?->status);
        self::assertSame(DomainStatus::Documented, $structure->domain('functional_impact')?->status);
    }

    #[Test]
    public function unassessed_domain_is_reported_as_not_assessed(): void
    {
        $assessment = new Assessment(
            id: '55555555-5555-4555-8555-555555555555',
            ageBand: AgeBand::Adult,
            responses: [new Response('A1.01', Period::Current, 3)],
        );
        $form = Scenario::form();
        $structure = (new ClinicalStructuringEngine())->build($assessment, $form);

        self::assertSame(DomainStatus::NotAssessed, $structure->domain('B4')?->status);
    }

    #[Test]
    public function temporal_inconsistency_is_flagged_as_contradictory(): void
    {
        $assessment = new Assessment(
            id: '66666666-6666-4666-8666-666666666666',
            ageBand: AgeBand::Adult,
            responses: [
                new Response('A1.01', Period::Current, 4),
                new Response('A1.01', Period::Childhood, 0),
            ],
        );
        $form = Scenario::form();
        $structure = (new ClinicalStructuringEngine())->build($assessment, $form);

        self::assertSame(DomainStatus::Contradictory, $structure->domain('A1')?->status);
        self::assertGreaterThan(0, $structure->domain('A1')?->contradictionCount);
    }
}
