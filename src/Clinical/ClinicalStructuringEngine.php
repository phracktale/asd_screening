<?php

declare(strict_types=1);

namespace TsaRepere\Clinical;

use TsaRepere\Assessment\Assessment;
use TsaRepere\Assessment\Response;
use TsaRepere\Form\FormDefinition;
use TsaRepere\Form\Period;

/**
 * Moteur 2 - Structuration clinique (specification 5.1 et 7.3).
 *
 * Organise les observations par domaine, distingue l'actuel et l'enfance,
 * repere les contradictions temporelles et le retentissement. Il ne valide
 * jamais automatiquement un critere diagnostique.
 */
final class ClinicalStructuringEngine
{
    /** Intensite a partir de laquelle un item soutient un domaine ("parfois/modere"). */
    private const int SUPPORT_THRESHOLD = 2;
    /** Intensite marquee ("souvent/marque"). */
    private const int STRONG_THRESHOLD = 3;

    private const array SOCIAL_RRB_DOMAINS = ['A1', 'A2', 'A3', 'B1', 'B2', 'B3', 'B4'];

    public function build(Assessment $assessment, FormDefinition $form): ClinicalStructure
    {
        $domains = [];
        foreach (self::SOCIAL_RRB_DOMAINS as $domainCode) {
            $domains[$domainCode] = $this->summariseDomain($assessment, $form, $domainCode);
        }
        $domains['developmental_onset'] = $this->summariseDevelopmentalOnset($assessment, $form);
        $domains['functional_impact'] = $this->summariseFunctionalImpact($assessment);

        return new ClinicalStructure($domains, $this->differentialExploration($assessment, $form));
    }

    private function summariseDomain(Assessment $assessment, FormDefinition $form, string $domainCode): DomainSummary
    {
        $codes = $this->codesForDomain($form, $domainCode);

        $answeredCurrent = 0;
        $supporting = 0;
        $strong = 0;
        $contradictions = 0;
        $sourceRefs = [];
        $hasExample = false;

        foreach ($codes as $code) {
            $responses = $assessment->responsesForCode($code);
            $current = $this->firstForPeriod($responses, Period::Current);

            if ($current !== null && $current->isAnswered()) {
                ++$answeredCurrent;
                if ($current->intensity() >= self::SUPPORT_THRESHOLD) {
                    ++$supporting;
                    $sourceRefs[] = $code;
                }
                if ($current->intensity() >= self::STRONG_THRESHOLD) {
                    ++$strong;
                }
                if ($current->example !== null && $current->example !== '') {
                    $hasExample = true;
                }
            }

            if ($this->isCodeContradictory($responses)) {
                ++$contradictions;
            }
        }

        if ($answeredCurrent === 0 && $contradictions === 0) {
            return new DomainSummary($domainCode, DomainStatus::NotAssessed, DomainSummary::CONFIDENCE_LOW, 0, 0, null, []);
        }

        $status = $this->deriveStatus($answeredCurrent, $supporting, $strong, $contradictions);
        $confidence = $this->deriveConfidence($supporting, $hasExample, $assessment);

        return new DomainSummary(
            code: $domainCode,
            status: $status,
            confidence: $confidence,
            evidenceCount: $supporting,
            contradictionCount: $contradictions,
            summary: $this->domainNarrative($domainCode, $status, $supporting, $answeredCurrent),
            sourceRefs: $sourceRefs,
        );
    }

    private function deriveStatus(int $answered, int $supporting, int $strong, int $contradictions): DomainStatus
    {
        if ($contradictions > 0) {
            return DomainStatus::Contradictory;
        }
        if ($answered === 0) {
            return DomainStatus::NotAssessed;
        }
        if ($strong >= 2 || ($supporting / $answered) >= 0.5) {
            return DomainStatus::Documented;
        }
        if ($supporting >= 1) {
            return DomainStatus::PossiblyDocumented;
        }

        return DomainStatus::NotDocumented;
    }

    private function deriveConfidence(int $supporting, bool $hasExample, Assessment $assessment): string
    {
        if ($supporting >= 3 && $hasExample && $assessment->hasExternalInformant()) {
            return DomainSummary::CONFIDENCE_HIGH;
        }
        if ($supporting >= 1) {
            return DomainSummary::CONFIDENCE_MEDIUM;
        }

        return DomainSummary::CONFIDENCE_LOW;
    }

    private function summariseDevelopmentalOnset(Assessment $assessment, FormDefinition $form): DomainSummary
    {
        $answered = 0;
        $supporting = 0;
        $sourceRefs = [];

        foreach (self::SOCIAL_RRB_DOMAINS as $domainCode) {
            foreach ($this->codesForDomain($form, $domainCode) as $code) {
                foreach ($assessment->responsesForCode($code) as $response) {
                    if (!\in_array($response->period, [Period::Childhood, Period::EarlyDevelopment], true)) {
                        continue;
                    }
                    if (!$response->isAnswered()) {
                        continue;
                    }
                    ++$answered;
                    if ($response->intensity() >= self::SUPPORT_THRESHOLD) {
                        ++$supporting;
                        $sourceRefs[] = $code;
                    }
                }
            }
        }

        if ($answered === 0) {
            return new DomainSummary('developmental_onset', DomainStatus::NotAssessed, DomainSummary::CONFIDENCE_LOW, 0, 0, 'Aucun element developpemental precoce renseigne.', []);
        }

        $status = match (true) {
            $supporting >= 2 => DomainStatus::Documented,
            $supporting >= 1 => DomainStatus::PossiblyDocumented,
            default => DomainStatus::NotDocumented,
        };
        $confidence = $assessment->hasExternalInformant() && $supporting >= 2
            ? DomainSummary::CONFIDENCE_HIGH
            : ($supporting >= 1 ? DomainSummary::CONFIDENCE_MEDIUM : DomainSummary::CONFIDENCE_LOW);

        return new DomainSummary('developmental_onset', $status, $confidence, $supporting, 0, 'Elements rapportes pour la periode developpementale precoce.', array_values(array_unique($sourceRefs)));
    }

    private function summariseFunctionalImpact(Assessment $assessment): DomainSummary
    {
        $recorded = 0;
        $support = 0;
        $strong = 0;
        $sourceRefs = [];

        foreach ($assessment->responses() as $response) {
            $impact = $response->impact;
            if ($impact === null || $impact === 'unknown' || $impact === 'none') {
                if ($impact === 'none') {
                    ++$recorded;
                }
                continue;
            }
            ++$recorded;
            if (\in_array($impact, ['important', 'major'], true)) {
                ++$strong;
                $sourceRefs[] = $response->questionCode;
            } elseif ($impact === 'moderate') {
                ++$support;
                $sourceRefs[] = $response->questionCode;
            }
        }

        if ($recorded === 0) {
            return new DomainSummary('functional_impact', DomainStatus::NotAssessed, DomainSummary::CONFIDENCE_LOW, 0, 0, 'Aucun retentissement renseigne.', []);
        }

        $status = match (true) {
            $strong >= 1 => DomainStatus::Documented,
            $support >= 1 => DomainStatus::PossiblyDocumented,
            default => DomainStatus::NotDocumented,
        };

        return new DomainSummary(
            'functional_impact',
            $status,
            $strong >= 1 ? DomainSummary::CONFIDENCE_MEDIUM : DomainSummary::CONFIDENCE_LOW,
            $strong + $support,
            0,
            'Retentissement fonctionnel rapporte sur au moins un domaine de vie.',
            array_values(array_unique($sourceRefs)),
        );
    }

    /**
     * Exploration differentielle (section F du catalogue, domaine "differential").
     * Le formulaire ne diagnostique pas ces troubles ; il repere les domaines a
     * explorer (specification 6.9). Les reponses attendues sont known/suspected/no/unknown.
     *
     * @return list<array{code: string, status: string, comment: ?string}>
     */
    private function differentialExploration(Assessment $assessment, FormDefinition $form): array
    {
        $out = [];
        foreach ($form->questions as $question) {
            if ($question->domain !== 'differential') {
                continue;
            }
            $responses = $assessment->responsesForCode($question->code);
            $status = 'not_reported';
            foreach ($responses as $response) {
                // Les items differentiels utilisent une echelle categorielle stockee
                // dans example (voir note d'incoherence CSV/schema dans le README).
                $raw = strtolower((string) $response->example);
                $status = match (true) {
                    str_contains($raw, 'known') || str_contains($raw, 'connu') => 'known',
                    str_contains($raw, 'suspected') || str_contains($raw, 'suspect') => 'suspected',
                    str_contains($raw, 'unknown') || str_contains($raw, 'inconnu') => 'unknown',
                    default => $status,
                };
            }
            $out[] = ['code' => $question->code, 'status' => $status, 'comment' => $question->labelFr];
        }

        return $out;
    }

    /**
     * Contradiction temporelle : le meme item est declare marque (>=3) pour une
     * periode et explicitement absent (0) pour une autre.
     *
     * @param list<Response> $responses
     */
    private function isCodeContradictory(array $responses): bool
    {
        $max = null;
        $min = null;
        foreach ($responses as $response) {
            if (!$response->isAnswered()) {
                continue;
            }
            $v = $response->intensity();
            $max = $max === null ? $v : max($max, $v);
            $min = $min === null ? $v : min($min, $v);
        }

        return $max !== null && $min !== null && $max >= self::STRONG_THRESHOLD && $min === 0;
    }

    /**
     * @param list<Response> $responses
     */
    private function firstForPeriod(array $responses, Period $period): ?Response
    {
        foreach ($responses as $response) {
            if ($response->period === $period) {
                return $response;
            }
        }

        return null;
    }

    /**
     * @return list<string>
     */
    private function codesForDomain(FormDefinition $form, string $domainCode): array
    {
        $codes = [];
        foreach ($form->questions as $question) {
            if ($question->domain === $domainCode) {
                $codes[] = $question->code;
            }
        }

        return $codes;
    }

    private function domainNarrative(string $domainCode, DomainStatus $status, int $supporting, int $answered): string
    {
        return match ($status) {
            DomainStatus::Documented => \sprintf('Domaine %s : elements convergents (%d/%d items marques).', $domainCode, $supporting, $answered),
            DomainStatus::PossiblyDocumented => \sprintf('Domaine %s : quelques elements rapportes (%d/%d).', $domainCode, $supporting, $answered),
            DomainStatus::NotDocumented => \sprintf('Domaine %s : peu ou pas d\'elements marques.', $domainCode),
            DomainStatus::Contradictory => \sprintf('Domaine %s : reponses contradictoires entre periodes, a clarifier en entretien.', $domainCode),
            DomainStatus::NotAssessed => \sprintf('Domaine %s : non evalue.', $domainCode),
        };
    }
}
