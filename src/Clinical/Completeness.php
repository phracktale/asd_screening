<?php

declare(strict_types=1);

namespace TsaRepere\Clinical;

/**
 * Indice de completude des informations recueillies (specification 7.2, review P1-9).
 *
 * La completude est evaluee par blocs pour eviter qu'un remplissage partiel
 * (social uniquement) soit annonce "suffisant" alors que l'histoire
 * developpementale ou le retentissement manquent.
 */
final readonly class Completeness
{
    public const string SUFFICIENT = 'sufficient';
    public const string PARTIAL = 'partial';
    public const string INSUFFICIENT = 'insufficient';

    public const string DEV_SUFFICIENT = 'sufficient';
    public const string DEV_PARTIAL = 'partial';
    public const string DEV_MISSING = 'missing';

    /**
     * @param array<string, bool> $blocks         completude par bloc fonctionnel
     * @param list<string>        $discrepancies  divergences temporelles a clarifier
     */
    public function __construct(
        public string $level,
        public float $answeredRatio,
        public string $developmentalHistory,
        public bool $informantAvailable,
        public array $blocks,
        public array $discrepancies,
    ) {
    }

    public function isSufficient(): bool
    {
        return $this->level === self::SUFFICIENT;
    }
}
