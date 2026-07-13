<?php

declare(strict_types=1);

namespace TsaRepere\Orientation;

use TsaRepere\Assessment\Assessment;
use TsaRepere\Clinical\ClinicalStructure;
use TsaRepere\Clinical\Completeness;
use TsaRepere\Clinical\DomainStatus;

/**
 * Determine le niveau de suspicion et le message d'orientation
 * (specification 7.3, 7.4 et 7.5).
 *
 * Principes non negociables :
 *  - la securite prime : un risque urgent court-circuite toute interpretation TSA ;
 *  - aucune conclusion diagnostique, ni positive ni negative categorique ;
 *  - l'application peut et doit repondre "indetermine" quand les donnees ne
 *    permettent pas de conclure.
 */
final class OrientationEngine
{
    public function determine(
        Assessment $assessment,
        ClinicalStructure $structure,
        Completeness $completeness,
    ): OrientationResult {
        // 1. Securite : risque urgent traite en priorite absolue.
        if ($assessment->urgentRisk->isPresent()) {
            return $this->urgent($assessment);
        }

        // 2. Donnees insuffisantes ou contradictoires -> indetermine.
        if ($completeness->level === Completeness::INSUFFICIENT || \count($completeness->contradictions) > 0) {
            return $this->indeterminate($completeness);
        }

        // 3. Compatibilite structuree (specification 7.3).
        $socialConvergent = $this->countConvergent($structure, ['A1', 'A2', 'A3']);
        $rrbDocumented = $this->countDocumented($structure, ['B1', 'B2', 'B3', 'B4']);
        $devConvergent = $structure->domain('developmental_onset')?->isConvergent() ?? false;
        $impactConvergent = $structure->domain('functional_impact')?->isConvergent() ?? false;

        $strongCompatibility = $socialConvergent === 3
            && $rrbDocumented >= 2
            && $devConvergent
            && $impactConvergent;

        if ($strongCompatibility && $completeness->level === Completeness::SUFFICIENT) {
            return $this->high();
        }

        if ($socialConvergent >= 2 && ($rrbDocumented >= 1 || $devConvergent)) {
            return $this->intermediate();
        }

        return $this->low();
    }

    private function urgent(Assessment $assessment): OrientationResult
    {
        $reason = implode(', ', $assessment->urgentRisk->triggeredReasons());

        return new OrientationResult(
            level: SuspicionLevel::Urgent,
            message: 'Des elements de risque immediat ont ete signales. Cette situation ne releve pas d\'un outil de reperage : contactez sans attendre un service d\'urgence ou une ligne d\'aide. L\'analyse de reperage du TSA est suspendue.',
            recommendedActions: [
                'En cas de danger immediat, appeler le 15 (SAMU) ou le 112.',
                'Contacter le 3114, numero national de prevention du suicide (24h/24, gratuit).',
                'Se rapprocher d\'un professionnel de sante ou des urgences les plus proches.',
            ],
            urgentReason: $reason !== '' ? $reason : 'risque immediat declare',
        );
    }

    private function indeterminate(Completeness $completeness): OrientationResult
    {
        $message = \count($completeness->contradictions) > 0
            ? 'Les reponses recueillies comportent des elements contradictoires. Les donnees sont insuffisantes ou contradictoires pour estimer correctement le niveau de suspicion. Ce resultat n\'est pas un diagnostic.'
            : 'Les donnees sont insuffisantes pour estimer correctement le niveau de suspicion. Ce resultat n\'est pas un diagnostic.';

        return new OrientationResult(
            level: SuspicionLevel::Indeterminate,
            message: $message,
            recommendedActions: [
                'Completer le questionnaire, notamment les elements de l\'enfance.',
                'Ajouter des exemples concrets et, si possible, le temoignage d\'un proche.',
                'Reprendre l\'evaluation dans de meilleures conditions si l\'etat du moment a pu influencer les reponses.',
            ],
        );
    }

    private function high(): OrientationResult
    {
        return new OrientationResult(
            level: SuspicionLevel::High,
            message: 'Les reponses recueillies montrent un niveau de suspicion eleve et justifient une evaluation specialisee. Ce resultat n\'est pas un diagnostic.',
            recommendedActions: [
                'Solliciter une evaluation aupres d\'un professionnel ou d\'une equipe specialisee dans le TSA.',
                'Conserver ce compte rendu et les exemples pour preparer l\'entretien.',
                'Rassembler les documents developpementaux disponibles (bulletins, carnet de sante, bilans).',
            ],
        );
    }

    private function intermediate(): OrientationResult
    {
        return new OrientationResult(
            level: SuspicionLevel::Intermediate,
            message: 'Plusieurs elements recueillis sont compatibles avec un TSA, mais les informations sont incompletes ou pourraient correspondre a d\'autres situations. Un avis professionnel est recommande. Ce resultat n\'est pas un diagnostic.',
            recommendedActions: [
                'En parler avec un medecin ou un psychologue pour un premier avis.',
                'Completer les elements developpementaux et le retentissement au quotidien.',
                'Explorer d\'autres hypotheses possibles avec un professionnel.',
            ],
        );
    }

    private function low(): OrientationResult
    {
        return new OrientationResult(
            level: SuspicionLevel::Low,
            message: 'Peu d\'elements convergents ont ete recueillis a ce stade. Cet outil peut cependant manquer certaines presentations, notamment en cas de camouflage. Ce resultat n\'est pas un diagnostic.',
            recommendedActions: [
                'Si des difficultes persistent ou s\'aggravent, consulter un professionnel.',
                'Reprendre l\'evaluation ulterieurement si de nouveaux elements apparaissent.',
            ],
        );
    }

    /**
     * @param list<string> $codes
     */
    private function countConvergent(ClinicalStructure $structure, array $codes): int
    {
        $count = 0;
        foreach ($codes as $code) {
            if ($structure->domain($code)?->isConvergent() ?? false) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @param list<string> $codes
     */
    private function countDocumented(ClinicalStructure $structure, array $codes): int
    {
        $count = 0;
        foreach ($codes as $code) {
            if (($structure->domain($code)?->status ?? null) === DomainStatus::Documented) {
                ++$count;
            }
        }

        return $count;
    }
}
