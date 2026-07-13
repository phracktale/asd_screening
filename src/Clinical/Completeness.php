<?php

declare(strict_types=1);

namespace TsaRepere\Clinical;

/**
 * Indice de completude des informations recueillies (specification 7.2).
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
     * @param list<string> $contradictions
     */
    public function __construct(
        public string $level,
        public float $answeredRatio,
        public string $developmentalHistory,
        public bool $informantAvailable,
        public array $contradictions,
    ) {
    }

    public function isSufficient(): bool
    {
        return $this->level === self::SUFFICIENT;
    }
}
