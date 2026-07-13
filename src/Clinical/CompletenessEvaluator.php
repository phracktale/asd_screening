<?php

declare(strict_types=1);

namespace TsaRepere\Clinical;

use TsaRepere\Assessment\Assessment;
use TsaRepere\Form\FormDefinition;
use TsaRepere\Form\Period;

/**
 * Evalue la completude (specification 7.2). Cet indice conditionne la capacite
 * de l'application a conclure ou a repondre "indetermine".
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

        $contradictions = $this->collectContradictions($structure);

        $level = $this->deriveLevel($answeredRatio, $developmental);

        return new Completeness(
            level: $level,
            answeredRatio: round($answeredRatio, 3),
            developmentalHistory: $developmental,
            informantAvailable: $assessment->hasExternalInformant(),
            contradictions: $contradictions,
        );
    }

    private function deriveLevel(float $answeredRatio, string $developmental): string
    {
        if ($answeredRatio < 0.3 || ($answeredRatio < 0.5 && $developmental === Completeness::DEV_MISSING)) {
            return Completeness::INSUFFICIENT;
        }
        if ($answeredRatio >= 0.7 && $developmental !== Completeness::DEV_MISSING) {
            return Completeness::SUFFICIENT;
        }

        return Completeness::PARTIAL;
    }

    /**
     * @return list<string>
     */
    private function collectContradictions(ClinicalStructure $structure): array
    {
        $out = [];
        foreach ($structure->domains as $summary) {
            if ($summary->status === DomainStatus::Contradictory) {
                $out[] = \sprintf('Domaine %s : incoherence temporelle entre periodes.', $summary->code);
            }
        }

        return $out;
    }
}
