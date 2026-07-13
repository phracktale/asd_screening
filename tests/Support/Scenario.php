<?php

declare(strict_types=1);

namespace TsaRepere\Tests\Support;

use TsaRepere\Assessment\Assessment;
use TsaRepere\Assessment\Response;
use TsaRepere\Assessment\UrgentRisk;
use TsaRepere\Form\AgeBand;
use TsaRepere\Form\CatalogLoader;
use TsaRepere\Form\FormDefinition;
use TsaRepere\Form\Period;

/**
 * Fabriques de scenarios pour les tests du coeur metier.
 */
final class Scenario
{
    public const string CATALOG = __DIR__ . '/../../packages/assessment-schema/data/question_catalog.csv';

    public static function form(): FormDefinition
    {
        return (new CatalogLoader('1.0.0'))->loadFromCsv(self::CATALOG);
    }

    /**
     * Scenario de forte compatibilite : tous les items adulte marques,
     * antecedents developpementaux et retentissement majeur.
     */
    public static function strongAdult(FormDefinition $form): Assessment
    {
        $responses = [];
        foreach ($form->forAgeBand(AgeBand::Adult) as $q) {
            if (!$q->supportsPeriod(Period::Current)) {
                continue;
            }
            $responses[] = new Response(
                questionCode: $q->code,
                period: Period::Current,
                value: 3,
                impact: \in_array($q->domain, ['A1', 'A2', 'A3'], true) ? 'major' : null,
                example: 'exemple concret rapporte',
                sourceType: 'parent_report',
            );
            // Antecedents precoces sur les domaines sociaux et RRB.
            if ($q->supportsPeriod(Period::Childhood)) {
                $responses[] = new Response($q->code, Period::Childhood, 3);
            }
        }

        return new Assessment(
            id: '11111111-1111-4111-8111-111111111111',
            ageBand: AgeBand::Adult,
            respondentType: 'parent',
            responses: $responses,
        );
    }

    /**
     * Scenario faible : beaucoup d'items renseignes mais tous a 0, sans enfance.
     */
    public static function lowAdult(FormDefinition $form): Assessment
    {
        $responses = [];
        foreach ($form->forAgeBand(AgeBand::Adult) as $q) {
            if (!$q->supportsPeriod(Period::Current)) {
                continue;
            }
            $responses[] = new Response($q->code, Period::Current, 0);
        }

        return new Assessment(
            id: '22222222-2222-4222-8222-222222222222',
            ageBand: AgeBand::Adult,
            responses: $responses,
        );
    }

    /**
     * Scenario insuffisant : deux items seulement.
     */
    public static function sparseAdult(): Assessment
    {
        return new Assessment(
            id: '33333333-3333-4333-8333-333333333333',
            ageBand: AgeBand::Adult,
            responses: [
                new Response('A1.01', Period::Current, 2),
                new Response('A2.01', Period::Current, 3),
            ],
        );
    }

    /**
     * Scenario avec risque urgent declare.
     */
    public static function urgentAdult(FormDefinition $form): Assessment
    {
        $base = self::strongAdult($form);

        return new Assessment(
            id: '44444444-4444-4444-8444-444444444444',
            ageBand: AgeBand::Adult,
            respondentType: 'self',
            urgentRisk: new UrgentRisk(suicidalIdeation: UrgentRisk::YES),
            responses: $base->responses(),
        );
    }
}
