<?php

declare(strict_types=1);

namespace TsaRepere\Psychometric;

/**
 * Entree du registre de licences d'un instrument (specification 6.11 / chap. 0).
 * Controle ce que l'application a le droit d'afficher et de calculer.
 */
final readonly class LicenseRecord
{
    public function __construct(
        public string $instrumentCode,
        public string $instrumentName,
        public string $rightsHolder,
        public string $status,
        public bool $itemsDisplayable,
        public bool $scoringAllowed,
        public bool $scoreEntryAllowed,
        public ?int $threshold,
        public ?int $itemCount,
        public ?string $sourceUrl = null,
        public ?string $notes = null,
    ) {
    }
}
