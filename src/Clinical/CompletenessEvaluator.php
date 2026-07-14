<?php

declare(strict_types=1);

namespace TsaRepere\Clinical;

use TsaRepere\Assessment\Assessment;
use TsaRepere\Form\FormDefinition;
use TsaRepere\Form\Period;

/**
 * Evalue la completude par blocs (specification 7.2, review P1-9). Cet indice
 * conditionne la capacite de l'application a conclure ou a repondre "indetermine".
 */
final class CompletenessEvaluator
{
    private const int DEV_SUFFICIENT_MIN = 3;

    public function evaluate(Assessment $assessment, FormDefinition $form, ClinicalStructure $structure): Completeness
    {
        $applicable = $form->forAgeBand($assessment->ageBand);
        $applicableCount = \count($applicable);

        $answeredCodes = [];
        $childhoodAnswered = 0;
        foreach ($assessment->responses() as $response) {
            if (!$response->isAnswered()) {
                continue;
            }
            $answeredCodes[$response->questionCode] = true;
            if (\in_array($response->period, [Period::Childhood, Period::EarlyDevelopment], true)) {
                ++$childhoodAnswered;
            }
        }

        $answeredRatio = $applicableCount > 0
            ? min(1.0, \count($answeredCodes) / $applicableCount)
            : 0.0;

        $developmental = match (true) {
            $childhoodAnswered >= self::DEV_SUFFICIENT_MIN => Completeness::DEV_SUFFICIENT,
            $childhoodAnswered >= 1 => Completeness::DEV_PARTIAL,
            default => Completeness::DEV_MISSING,
        };

        $blocks = $this->blocks($structure, $developmental, $assessment);
        $discrepancies = $this->collectDiscrepancies($structure);

        return new Completeness(
            level: $this->deriveLevel($answeredRatio, $blocks),
            answeredRatio: round($answeredRatio, 3),
            developmentalHistory: $developmental,
            informantAvailable: $assessment->hasExternalInformant(),
            blocks: $blocks,
            discrepancies: $discrepancies,
        );
    }

    /**
     * @return array<string, bool>
     */
    private function blocks(ClinicalStructure $structure, string $developmental, Assessment $assessment): array
    {
        $socialAssessed = $this->countAssessed($structure, ['A1', 'A2', 'A3']);
        $rrbAssessed = $this->countAssessed($structure, ['B1', 'B2', 'B3', 'B4']);

        $differentialReviewed = false;
        foreach ($structure->differentialExploration as $entry) {
            if (!\in_array($entry['status'], ['not_reported', 'unknown'], true)) {
                $differentialReviewed = true;
                break;
            }
        }

        return [
            'core_social_complete' => $socialAssessed >= 2,
            'core_rrb_complete' => $rrbAssessed >= 2,
            'developmental_history_complete' => $developmental === Completeness::DEV_SUFFICIENT,
            'functional_impact_complete' => ($structure->domain('functional_impact')?->status ?? DomainStatus::NotAssessed) !== DomainStatus::NotAssessed,
            'differential_review_complete' => $differentialReviewed,
            'informant_evidence_complete' => $assessment->hasExternalInformant(),
        ];
    }

    /**
     * @param array<string, bool> $blocks
     */
    private function deriveLevel(float $answeredRatio, array $blocks): string
    {
        $coreCovered = $blocks['core_social_complete'] && $blocks['core_rrb_complete'];

        if ($answeredRatio < 0.3 || !$coreCovered) {
            return Completeness::INSUFFICIENT;
        }

        if ($coreCovered
            && $blocks['developmental_history_complete']
            && $blocks['functional_impact_complete']
            && $answeredRatio >= 0.6) {
            return Completeness::SUFFICIENT;
        }

        return Completeness::PARTIAL;
    }

    /**
     * @param list<string> $codes
     */
    private function countAssessed(ClinicalStructure $structure, array $codes): int
    {
        $count = 0;
        foreach ($codes as $code) {
            if (($structure->domain($code)?->status ?? DomainStatus::NotAssessed) !== DomainStatus::NotAssessed) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @return list<string>
     */
    private function collectDiscrepancies(ClinicalStructure $structure): array
    {
        $out = [];
        foreach ($structure->domains as $summary) {
            if ($summary->status === DomainStatus::Contradictory) {
                $out[] = \sprintf('Domaine %s : divergence temporelle entre periodes (a clarifier).', $summary->code);
            }
        }

        return $out;
    }
}
