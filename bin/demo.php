<?php

declare(strict_types=1);

/**
 * Demonstration du coeur metier : construit une evaluation adulte, calcule la
 * synthese et affiche la sortie JSON conforme a assessment_output.schema.json.
 *
 * Usage : php bin/demo.php
 */

require __DIR__ . '/../vendor/autoload.php';

use TsaRepere\Assessment\Assessment;
use TsaRepere\Assessment\Response;
use TsaRepere\Form\AgeBand;
use TsaRepere\Form\CatalogLoader;
use TsaRepere\Form\Period;
use TsaRepere\Output\ReportAssembler;
use TsaRepere\Psychometric\LicenseRegistry;
use TsaRepere\Psychometric\ScoringEngine;

$form = (new CatalogLoader('1.0.0'))
    ->loadFromCsv(__DIR__ . '/../packages/assessment-schema/data/question_catalog.csv');

// Quelques reponses adulte marquees + antecedents + retentissement.
$responses = [];
foreach ($form->forAgeBand(AgeBand::Adult) as $q) {
    if (!$q->supportsPeriod(Period::Current)) {
        continue;
    }
    $responses[] = new Response(
        questionCode: $q->code,
        period: Period::Current,
        value: 3,
        impact: in_array($q->domain, ['A1', 'A2', 'A3'], true) ? 'major' : null,
        example: 'exemple rapporte par la personne',
        sourceType: 'self_report',
    );
    if ($q->supportsPeriod(Period::Childhood)) {
        $responses[] = new Response($q->code, Period::Childhood, 3);
    }
}

$assessment = new Assessment(
    id: '11111111-1111-4111-8111-111111111111',
    ageBand: AgeBand::Adult,
    respondentType: 'self',
    responses: $responses,
);

// Score AQ-10 saisi par un professionnel (calcul en app interdit par la licence).
$scoring = new ScoringEngine(
    LicenseRegistry::fromJsonFile(__DIR__ . '/../packages/instrument-definitions/license_registry.json')
);
$aq10 = $scoring->recordEnteredScore('AQ-10-adult', '1.0', 8.0);

$report = (new ReportAssembler())->assemble(
    $assessment,
    $form,
    (new DateTimeImmutable('now'))->format(DATE_ATOM),
    [$aq10],
);

echo json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), \PHP_EOL;
