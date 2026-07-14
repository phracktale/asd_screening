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
 *  - l'application peut et doit repondre "indetermine" ;
 *  - la graduation faible/intermediaire/eleve repose sur des seuils NON valides
 *    cliniquement : elle est marquee comme prototype tant que $gradedLevelsValidated
 *    est faux (review P0-5) ;
 *  - une incertitude differentielle majeure empeche une sortie affirmative (P0-6) ;
 *  - le camouflage nuance une faible visibilite des signes (P1-7).
 */
final class OrientationEngine
{
    public function __construct(
        private readonly bool $gradedLevelsValidated = false,
    ) {
    }

    public function determine(
        Assessment $assessment,
        ClinicalStructure $structure,
        Completeness $completeness,
    ): OrientationResult {
        // 1. Securite : risque urgent traite en priorite absolue.
        if ($assessment->urgentRisk->isPresent()) {
            return $this->urgent($assessment);
        }

        // 2. Donnees insuffisantes ou trop de divergences -> indetermine.
        if ($completeness->level === Completeness::INSUFFICIENT || \count($completeness->discrepancies) >= 2) {
            return $this->indeterminate($completeness);
        }

        // 3. Compatibilite structuree (specification 7.3).
        $socialConvergent = $this->countConvergent($structure, ['A1', 'A2', 'A3']);
        $rrbDocumented = $this->countDocumented($structure, ['B1', 'B2', 'B3', 'B4']);
        $devConvergent = $structure->domain('developmental_onset')?->isConvergent() ?? false;
        $impactConvergent = $structure->domain('functional_impact')?->isConvergent() ?? false;

        $notes = $this->interpretationNotes($structure, $completeness);

        $strongCompatibility = $socialConvergent === 3
            && $rrbDocumented >= 2
            && $devConvergent
            && $impactConvergent
            && $completeness->level === Completeness::SUFFICIENT
            // Une incertitude differentielle majeure interdit une sortie affirmative.
            && !$structure->differentialUncertainty->blocksAffirmativeConclusion()
            // Une divergence temporelle isolee empeche aussi la conclusion la plus forte.
            && \count($completeness->discrepancies) === 0;

        if ($strongCompatibility) {
            return $this->high($notes);
        }

        if ($socialConvergent >= 2 && ($rrbDocumented >= 1 || $devConvergent)) {
            return $this->intermediate($notes);
        }

        return $this->low($structure, $notes);
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
            gradedLevelValidated: false,
        );
    }

    private function indeterminate(Completeness $completeness): OrientationResult
    {
        $message = \count($completeness->discrepancies) > 0
            ? 'Les reponses recueillies comportent des divergences a clarifier. Les donnees sont insuffisantes ou trop divergentes pour estimer correctement le niveau de suspicion. Ce resultat n\'est pas un diagnostic.'
            : 'Les donnees sont insuffisantes pour estimer correctement le niveau de suspicion. Ce resultat n\'est pas un diagnostic.';

        return new OrientationResult(
            level: SuspicionLevel::Indeterminate,
            message: $message,
            recommendedActions: [
                'Completer le questionnaire, notamment les elements de l\'enfance et le retentissement.',
                'Ajouter des exemples concrets et, si possible, le temoignage d\'un proche.',
                'Reprendre l\'evaluation dans de meilleures conditions si l\'etat du moment a pu influencer les reponses.',
            ],
            gradedLevelValidated: false,
            notes: $completeness->discrepancies,
        );
    }

    /**
     * @param list<string> $notes
     */
    private function high(array $notes): OrientationResult
    {
        return new OrientationResult(
            level: SuspicionLevel::High,
            message: $this->prototypePrefix() . 'Les reponses recueillies montrent un niveau de suspicion eleve et justifient une evaluation specialisee. Ce resultat n\'est pas un diagnostic.',
            recommendedActions: [
                'Solliciter une evaluation aupres d\'un professionnel ou d\'une equipe specialisee dans le TSA.',
                'Conserver ce compte rendu et les exemples pour preparer l\'entretien.',
                'Rassembler les documents developpementaux disponibles (bulletins, carnet de sante, bilans).',
            ],
            gradedLevelValidated: $this->gradedLevelsValidated,
            notes: $notes,
        );
    }

    /**
     * @param list<string> $notes
     */
    private function intermediate(array $notes): OrientationResult
    {
        return new OrientationResult(
            level: SuspicionLevel::Intermediate,
            message: $this->prototypePrefix() . 'Plusieurs elements recueillis sont compatibles avec un TSA, mais les informations sont incompletes ou pourraient correspondre a d\'autres situations. Un avis professionnel est recommande. Ce resultat n\'est pas un diagnostic.',
            recommendedActions: [
                'En parler avec un medecin ou un psychologue pour un premier avis.',
                'Completer les elements developpementaux et le retentissement au quotidien.',
                'Explorer d\'autres hypotheses possibles avec un professionnel.',
            ],
            gradedLevelValidated: $this->gradedLevelsValidated,
            notes: $notes,
        );
    }

    /**
     * @param list<string> $notes
     */
    private function low(ClinicalStructure $structure, array $notes): OrientationResult
    {
        $message = $this->prototypePrefix()
            . 'Peu d\'elements convergents ont ete recueillis a ce stade. Cet outil peut cependant manquer certaines presentations, notamment en cas de camouflage. Ce resultat n\'est pas un diagnostic.';

        return new OrientationResult(
            level: SuspicionLevel::Low,
            message: $message,
            recommendedActions: [
                'Si des difficultes persistent ou s\'aggravent, consulter un professionnel.',
                'Reprendre l\'evaluation ulterieurement si de nouveaux elements apparaissent.',
            ],
            gradedLevelValidated: $this->gradedLevelsValidated,
            notes: $notes,
        );
    }

    /**
     * Remarques d'interpretation non graduees (camouflage, differentiels, divergences).
     *
     * @return list<string>
     */
    private function interpretationNotes(ClinicalStructure $structure, Completeness $completeness): array
    {
        $notes = [];

        if ($structure->hasReportedCamouflage()) {
            $notes[] = 'Des strategies de camouflage sont rapportees : une faible visibilite des signes ne doit pas etre interpretee comme leur absence.';
        }

        if ($structure->differentialUncertainty->blocksAffirmativeConclusion()) {
            $notes[] = 'Une ou plusieurs hypotheses differentielles restent a explorer avant toute conclusion. Une condition concomitante n\'exclut pas un TSA.';
        }

        foreach ($completeness->discrepancies as $discrepancy) {
            $notes[] = $discrepancy;
        }

        return $notes;
    }

    private function prototypePrefix(): string
    {
        return $this->gradedLevelsValidated
            ? ''
            : '[Prototype non valide cliniquement] ';
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
