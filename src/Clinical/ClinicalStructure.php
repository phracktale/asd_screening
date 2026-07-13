<?php

declare(strict_types=1);

namespace TsaRepere\Clinical;

/**
 * Resultat du moteur 2 : ensemble des domaines documentes plus l'exploration
 * differentielle. Aucun score global opaque n'est produit (specification 7.1).
 */
final readonly class ClinicalStructure
{
    /**
     * @param array<string, DomainSummary>            $domains  indexe par code de domaine
     * @param list<array{code: string, status: string, comment: ?string}> $differentialExploration
     */
    public function __construct(
        public array $domains,
        public array $differentialExploration = [],
    ) {
    }

    public function domain(string $code): ?DomainSummary
    {
        return $this->domains[$code] ?? null;
    }

    /** @return list<DomainSummary> */
    public function socialDomains(): array
    {
        return $this->pick(['A1', 'A2', 'A3']);
    }

    /** @return list<DomainSummary> */
    public function repetitiveSensoryDomains(): array
    {
        return $this->pick(['B1', 'B2', 'B3', 'B4']);
    }

    /**
     * @param list<string> $codes
     * @return list<DomainSummary>
     */
    private function pick(array $codes): array
    {
        $out = [];
        foreach ($codes as $code) {
            if (isset($this->domains[$code])) {
                $out[] = $this->domains[$code];
            }
        }

        return $out;
    }
}
