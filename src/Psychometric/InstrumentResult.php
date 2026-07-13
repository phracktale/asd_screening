<?php

declare(strict_types=1);

namespace TsaRepere\Psychometric;

/**
 * Resultat d'un instrument psychometrique. Ne transforme jamais un seuil de
 * questionnaire en diagnostic (specification 5.1, moteur 1).
 */
final readonly class InstrumentResult
{
    public const string SOURCE_CALCULATED = 'calculated_in_app';
    public const string SOURCE_ENTERED_USER = 'entered_by_user';
    public const string SOURCE_ENTERED_PRO = 'entered_by_professional';
    public const string SOURCE_IMPORTED = 'imported_document';

    public function __construct(
        public string $instrumentCode,
        public string $instrumentVersion,
        public float $rawScore,
        public ?int $threshold,
        public bool $thresholdReached,
        public string $officialInterpretation,
        public string $source,
        public bool $licensed,
        public string $language = 'fr-FR',
    ) {
    }
}
