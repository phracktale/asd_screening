<?php

declare(strict_types=1);

namespace TsaRepere\Clinical;

/**
 * Synthese structuree d'un domaine (specification 10.4, ClinicalDomainSummary).
 */
final readonly class DomainSummary
{
    public const string CONFIDENCE_LOW = 'low';
    public const string CONFIDENCE_MEDIUM = 'medium';
    public const string CONFIDENCE_HIGH = 'high';

    /**
     * @param list<string> $sourceRefs codes d'items ayant contribue
     */
    public function __construct(
        public string $code,
        public DomainStatus $status,
        public string $confidence,
        public int $evidenceCount,
        public int $contradictionCount,
        public ?string $summary,
        public array $sourceRefs,
    ) {
    }

    /** Le domaine apporte-t-il un element convergent ? */
    public function isConvergent(): bool
    {
        return $this->status === DomainStatus::Documented
            || $this->status === DomainStatus::PossiblyDocumented;
    }
}
