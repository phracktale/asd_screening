<?php

declare(strict_types=1);

namespace TsaRepere\Tests\Clinical;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TsaRepere\Assessment\Assessment;
use TsaRepere\Assessment\DifferentialStatus;
use TsaRepere\Assessment\Response;
use TsaRepere\Clinical\ClinicalStructuringEngine;
use TsaRepere\Clinical\CompletenessEvaluator;
use TsaRepere\Clinical\DifferentialUncertainty;
use TsaRepere\Clinical\DomainStatus;
use TsaRepere\Form\AgeBand;
use TsaRepere\Form\Period;
use TsaRepere\Orientation\OrientationEngine;
use TsaRepere\Orientation\SuspicionLevel;
use TsaRepere\Tests\Support\Scenario;

final class ReviewFixesTest extends TestCase
{
    #[Test]
    public function a_single_marked_item_does_not_document_a_domain(): void
    {
        // review P0-10 : un seul item positif ne suffit plus.
        $assessment = new Assessment(
            id: '88888888-8888-4888-8888-888888888888',
            ageBand: AgeBand::Adult,
            responses: [Response::ordinal('A1.01', Period::Current, 3)],
        );
        $form = Scenario::form();
        $structure = (new ClinicalStructuringEngine())->build($assessment, $form);

        self::assertSame(DomainStatus::PossiblyDocumented, $structure->domain('A1')?->status);
    }

    #[Test]
    public function functional_impact_summary_matches_its_status(): void
    {
        // review P0-11 : le resume ne doit plus contredire le statut.
        $assessment = new Assessment(
            id: '99999999-9999-4999-8999-999999999999',
            ageBand: AgeBand::Adult,
            responses: [
                Response::ordinal('A1.01', Period::Current, 2, impact: 'none'),
                Response::ordinal('A1.02', Period::Current, 2, impact: 'mild'),
            ],
        );
        $form = Scenario::form();
        $structure = (new ClinicalStructuringEngine())->build($assessment, $form);
        $impact = $structure->domain('functional_impact');

        self::assertSame(DomainStatus::NotDocumented, $impact?->status);
        self::assertStringNotContainsString('majeur', (string) $impact?->summary);
        self::assertStringContainsString('leger ou absent', (string) $impact?->summary);
    }

    #[Test]
    public function reported_camouflage_is_summarised(): void
    {
        // review P1-7 : le camouflage n'est plus une donnee morte.
        $assessment = new Assessment(
            id: 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            ageBand: AgeBand::Adult,
            responses: [
                Response::ordinal('E.01', Period::Current, 3),
                Response::ordinal('E.02', Period::Current, 3),
            ],
        );
        $form = Scenario::form();
        $structure = (new ClinicalStructuringEngine())->build($assessment, $form);

        self::assertNotNull($structure->camouflaging);
        self::assertSame(DomainStatus::Documented, $structure->camouflaging?->status);
        self::assertTrue($structure->hasReportedCamouflage());
    }

    #[Test]
    public function unresolved_differential_blocks_a_high_conclusion(): void
    {
        // review P0-6 : une hypothese differentielle non tranchee empeche "eleve".
        $form = Scenario::form();
        $base = Scenario::strongAdult($form);
        $base->addResponse(Response::categorical('F.01', Period::Lifetime, DifferentialStatus::UnderEvaluation));

        $structure = (new ClinicalStructuringEngine())->build($base, $form);
        self::assertSame(DifferentialUncertainty::Unresolved, $structure->differentialUncertainty);

        $completeness = (new CompletenessEvaluator())->evaluate($base, $form, $structure);
        $result = (new OrientationEngine())->determine($base, $structure, $completeness);

        self::assertNotSame(SuspicionLevel::High, $result->level);
    }

    #[Test]
    public function graded_levels_are_flagged_as_non_validated_by_default(): void
    {
        // review P0-5 : la graduation est un prototype tant qu'elle n'est pas validee.
        $form = Scenario::form();
        $result = (new OrientationEngine())->determine(
            Scenario::strongAdult($form),
            $structure = (new ClinicalStructuringEngine())->build(Scenario::strongAdult($form), $form),
            (new CompletenessEvaluator())->evaluate(Scenario::strongAdult($form), $form, $structure),
        );

        self::assertTrue($result->isGraded());
        self::assertFalse($result->gradedLevelValidated);
        self::assertStringContainsString('Prototype non valide', $result->message);
    }

    #[Test]
    public function diagnosed_comorbidity_does_not_prevent_a_high_conclusion(): void
    {
        // Une condition concomitante diagnostiquee ne doit pas annuler la suspicion.
        $form = Scenario::form();
        $base = Scenario::strongAdult($form);
        $base->addResponse(Response::categorical('F.01', Period::Lifetime, DifferentialStatus::Diagnosed));

        $structure = (new ClinicalStructuringEngine())->build($base, $form);
        $completeness = (new CompletenessEvaluator())->evaluate($base, $form, $structure);
        $result = (new OrientationEngine())->determine($base, $structure, $completeness);

        self::assertSame(SuspicionLevel::High, $result->level);
    }
}
