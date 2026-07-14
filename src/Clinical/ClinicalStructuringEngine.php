<?php

declare(strict_types=1);

namespace TsaRepere\Clinical;

use TsaRepere\Assessment\Assessment;
use TsaRepere\Assessment\DifferentialStatus;
use TsaRepere\Assessment\Response;
use TsaRepere\Form\FormDefinition;
use TsaRepere\Form\Period;

/**
 * Moteur 2 - Structuration clinique (specification 5.1 et 7.3).
 *
 * Organise les observations par domaine, distingue l'actuel et l'enfance,
 * repere les divergences temporelles et le retentissement, exploite le
 * camouflage et resume l'incertitude differentielle. Il ne valide jamais
 * automatiquement un critere diagnostique.
 */
final class ClinicalStructuringEngine
{
    /** Intensite a partir de laquelle un item soutient un domaine ("parfois/modere"). */
    private const int SUPPORT_THRESHOLD = 2;
    /** Intensite marquee ("souvent/marque"). */
    private const int STRONG_THRESHOLD = 3;
    /** Nombre minimal d'items concordants pour qu'un domaine soit "documente" (review P0-10). */
    private const int MIN_SUPPORT_FOR_DOCUMENTED = 2;

    private const array SOCIAL_RRB_DOMAINS = ['A1', 'A2', 'A3', 'B1', 'B2', 'B3', 'B4'];

    public function build(Assessment $assessment, FormDefinition $form): ClinicalStructure
    {
        $domains = [];
        foreach (self::SOCIAL_RRB_DOMAINS as $domainCode) {
            $domains[$domainCode] = $this->summariseDomain($assessment, $form, $domainCode);
        }
        $domains['developmental_onset'] = $this->summariseDevelopmentalOnset($assessment, $form);
        $domains['functional_impact'] = $this->summariseFunctionalImpact($assessment);

        return new ClinicalStructure(
            domains: $domains,
            differentialExploration: $this->differentialExploration($assessment, $form),
            camouflaging: $this->summariseCamouflage($assessment, $form),
            differentialUncertainty: $this->differentialUncertainty($assessment, $form),
        );
    }

    private function summariseDomain(Assessment $assessment, FormDefinition $form, string $domainCode): DomainSummary
    {
        $codes = $this->codesForDomain($form, $domainCode);

        $answeredCurrent = 0;
        $supporting = 0;
        $strong = 0;
        $discrepancies = 0;
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

            if ($this->isTemporallyDivergent($responses)) {
                ++$discrepancies;
            }
        }

        if ($answeredCurrent === 0 && $discrepancies === 0) {
            return new DomainSummary($domainCode, DomainStatus::NotAssessed, DomainSummary::CONFIDENCE_LOW, 0, 0, null, []);
        }

        $status = $this->deriveStatus($answeredCurrent, $supporting, $strong, $discrepancies);
        $confidence = $this->deriveConfidence($supporting, $hasExample, $assessment);

        return new DomainSummary(
            code: $domainCode,
            status: $status,
            confidence: $confidence,
            evidenceCount: $supporting,
            contradictionCount: $discrepancies,
            summary: $this->domainNarrative($domainCode, $status, $supporting, $answeredCurrent),
            sourceRefs: $sourceRefs,
        );
    }

    private function deriveStatus(int $answered, int $supporting, int $strong, int $discrepancies): DomainStatus
    {
        if ($discrepancies > 0) {
            return DomainStatus::Contradictory; // valeur schema ; libelle "divergence temporelle" en sortie
        }
        if ($answered === 0) {
            return DomainStatus::NotAssessed;
        }
        // Un seul item ne suffit plus a "documenter" un domaine (review P0-10).
        if ($strong >= self::MIN_SUPPORT_FOR_DOCUMENTED
            || ($supporting >= self::MIN_SUPPORT_FOR_DOCUMENTED && ($supporting / $answered) >= 0.5)) {
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
            return new DomainSummary('developmental_onset', DomainStatus::NotAssessed, DomainSummary::CONFIDENCE_LOW, 0, 0, 'Aucun element developpemental precoce renseigne. Trame C a completer (voir KNOWN_ISSUES).', []);
        }

        $status = match (true) {
            $supporting >= self::MIN_SUPPORT_FOR_DOCUMENTED => DomainStatus::Documented,
            $supporting >= 1 => DomainStatus::PossiblyDocumented,
            default => DomainStatus::NotDocumented,
        };
        $confidence = $assessment->hasExternalInformant() && $supporting >= self::MIN_SUPPORT_FOR_DOCUMENTED
            ? DomainSummary::CONFIDENCE_HIGH
            : ($supporting >= 1 ? DomainSummary::CONFIDENCE_MEDIUM : DomainSummary::CONFIDENCE_LOW);

        $narrative = match ($status) {
            DomainStatus::Documented => 'Plusieurs elements rapportes pour la periode developpementale precoce.',
            DomainStatus::PossiblyDocumented => 'Quelques elements rapportes pour la periode developpementale precoce.',
            default => 'Elements developpementaux precoces renseignes mais peu marques.',
        };

        return new DomainSummary('developmental_onset', $status, $confidence, $supporting, 0, $narrative, array_values(array_unique($sourceRefs)));
    }

    private function summariseFunctionalImpact(Assessment $assessment): DomainSummary
    {
        $recorded = 0;
        $support = 0;
        $strong = 0;
        $sourceRefs = [];

        foreach ($assessment->responses() as $response) {
            $impact = $response->impact;
            if ($impact === null || $impact === 'unknown') {
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
            return new DomainSummary('functional_impact', DomainStatus::NotAssessed, DomainSummary::CONFIDENCE_LOW, 0, 0, 'Aucun retentissement renseigne. Trame D a completer (voir KNOWN_ISSUES).', []);
        }

        $status = match (true) {
            $strong >= 1 => DomainStatus::Documented,
            $support >= 1 => DomainStatus::PossiblyDocumented,
            default => DomainStatus::NotDocumented,
        };

        // Le resume suit desormais le statut (review P0-11).
        $narrative = match ($status) {
            DomainStatus::Documented => 'Retentissement fonctionnel important a majeur rapporte sur au moins un domaine de vie.',
            DomainStatus::PossiblyDocumented => 'Retentissement fonctionnel modere rapporte.',
            default => 'Retentissement renseigne mais leger ou absent sur les domaines rapportes.',
        };

        return new DomainSummary(
            'functional_impact',
            $status,
            $strong >= 1 ? DomainSummary::CONFIDENCE_MEDIUM : DomainSummary::CONFIDENCE_LOW,
            $strong + $support,
            0,
            $narrative,
            array_values(array_unique($sourceRefs)),
        );
    }

    /**
     * Resume du camouflage (section E du catalogue, domaine "camouflaging").
     * Il ne fait jamais monter la suspicion a lui seul (specification 6.8) ; il
     * sert a interpreter l'absence apparente de signes observables (review P1-7).
     */
    private function summariseCamouflage(Assessment $assessment, FormDefinition $form): ?DomainSummary
    {
        $codes = $this->codesForDomain($form, 'camouflaging');
        if ($codes === []) {
            return null;
        }

        $answered = 0;
        $supporting = 0;
        $sourceRefs = [];
        foreach ($codes as $code) {
            $current = $this->firstForPeriod($assessment->responsesForCode($code), Period::Current);
            if ($current === null || !$current->isAnswered()) {
                continue;
            }
            ++$answered;
            if ($current->intensity() >= self::SUPPORT_THRESHOLD) {
                ++$supporting;
                $sourceRefs[] = $code;
            }
        }

        if ($answered === 0) {
            return new DomainSummary('camouflaging', DomainStatus::NotAssessed, DomainSummary::CONFIDENCE_LOW, 0, 0, 'Camouflage non evalue.', []);
        }

        $status = match (true) {
            $supporting >= self::MIN_SUPPORT_FOR_DOCUMENTED => DomainStatus::Documented,
            $supporting >= 1 => DomainStatus::PossiblyDocumented,
            default => DomainStatus::NotDocumented,
        };

        return new DomainSummary(
            'camouflaging',
            $status,
            $supporting >= 1 ? DomainSummary::CONFIDENCE_MEDIUM : DomainSummary::CONFIDENCE_LOW,
            $supporting,
            0,
            'Strategies de compensation / camouflage rapportees. A prendre en compte pour interpreter une faible visibilite des signes.',
            $sourceRefs,
        );
    }

    /**
     * Exploration differentielle (section F du catalogue, domaine "differential").
     * Le formulaire ne diagnostique pas ces troubles ; il repere les domaines a
     * explorer (specification 6.9). Le statut provient d'une reponse categorielle
     * typee (plus aucune lecture depuis le texte libre, review P0-2).
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
            $status = DifferentialStatus::NotReported;
            $comment = null;
            foreach ($assessment->responsesForCode($question->code) as $response) {
                $parsed = $response->differentialStatus();
                if ($parsed !== null) {
                    $status = $parsed;
                    $comment = $response->example;
                }
            }
            $out[] = [
                'code' => $question->code,
                'status' => $status->value,
                'comment' => $comment ?? $question->labelFr,
            ];
        }

        return $out;
    }

    private function differentialUncertainty(Assessment $assessment, FormDefinition $form): DifferentialUncertainty
    {
        $unresolved = 0;
        $suspected = 0;
        $diagnosed = 0;

        foreach ($form->questions as $question) {
            if ($question->domain !== 'differential') {
                continue;
            }
            foreach ($assessment->responsesForCode($question->code) as $response) {
                $status = $response->differentialStatus();
                if ($status === null) {
                    continue;
                }
                if ($status->isUnresolved()) {
                    ++$unresolved;
                } elseif ($status->isSuspected()) {
                    ++$suspected;
                } elseif ($status === DifferentialStatus::Diagnosed) {
                    ++$diagnosed;
                }
            }
        }

        return match (true) {
            $unresolved >= 1 => DifferentialUncertainty::Unresolved,
            $suspected >= 1 => DifferentialUncertainty::Significant,
            $diagnosed >= 1 => DifferentialUncertainty::Limited,
            default => DifferentialUncertainty::None,
        };
    }

    /**
     * Divergence temporelle (anciennement "contradiction", review P1-8) : le meme
     * item est declare marque (>=3) pour une periode et explicitement absent (0)
     * pour une autre. Ce n'est pas necessairement une contradiction (evolution,
     * compensation, contexte) mais un point a clarifier en entretien.
     *
     * @param list<Response> $responses
     */
    private function isTemporallyDivergent(array $responses): bool
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
            DomainStatus::Contradictory => \sprintf('Domaine %s : divergence temporelle entre periodes, a clarifier en entretien.', $domainCode),
            DomainStatus::NotAssessed => \sprintf('Domaine %s : non evalue.', $domainCode),
        };
    }
}
