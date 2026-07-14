<?php

declare(strict_types=1);

namespace TsaRepere\Output;

use TsaRepere\Assessment\Assessment;
use TsaRepere\Clinical\ClinicalStructure;
use TsaRepere\Clinical\ClinicalStructuringEngine;
use TsaRepere\Clinical\Completeness;
use TsaRepere\Clinical\CompletenessEvaluator;
use TsaRepere\Clinical\DomainSummary;
use TsaRepere\Form\FormDefinition;
use TsaRepere\Orientation\OrientationEngine;
use TsaRepere\Orientation\OrientationResult;
use TsaRepere\Psychometric\InstrumentResult;
use TsaRepere\Support\Disclaimer;

/**
 * Orchestre les moteurs et assemble la sortie conforme a
 * assessment_output.schema.json (specification 7.1 et 11.3).
 *
 * Les quatre objets de sortie restent separes : ils ne sont jamais additionnes
 * en un score global opaque. Le moteur ML est desactivable : ml_estimate reste
 * nul tant qu'aucun modele valide n'est branche (specification 10.2).
 */
final class ReportAssembler
{
    public function __construct(
        private readonly ClinicalStructuringEngine $structuring = new ClinicalStructuringEngine(),
        private readonly CompletenessEvaluator $completenessEvaluator = new CompletenessEvaluator(),
        private readonly OrientationEngine $orientation = new OrientationEngine(),
    ) {
    }

    /**
     * @param list<InstrumentResult>       $instrumentResults resultats psychometriques deja calcules/saisis
     * @param array<string, mixed>|null    $mlEstimate        sortie du moteur ML si branche, sinon null
     *
     * @return array<string, mixed>
     */
    public function assemble(
        Assessment $assessment,
        FormDefinition $form,
        string $generatedAt,
        array $instrumentResults = [],
        ?array $mlEstimate = null,
    ): array {
        $structure = $this->structuring->build($assessment, $form);
        $completeness = $this->completenessEvaluator->evaluate($assessment, $form, $structure);
        $orientation = $this->orientation->determine($assessment, $structure, $completeness);

        return [
            'assessment_id' => $assessment->id,
            'generated_at' => $generatedAt,
            'form_version' => $form->version,
            'completeness' => $this->mapCompleteness($completeness),
            'psychometric_results' => array_map($this->mapInstrument(...), $instrumentResults),
            'clinical_structure' => $this->mapStructure($structure),
            'ml_estimate' => $mlEstimate,
            'orientation' => $this->mapOrientation($orientation),
            'evidence_trace' => $this->mapEvidenceTrace($structure),
            'disclaimer' => Disclaimer::STANDARD,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapCompleteness(Completeness $completeness): array
    {
        return [
            'level' => $completeness->level,
            'answered_ratio' => $completeness->answeredRatio,
            'developmental_history' => $completeness->developmentalHistory,
            'informant_available' => $completeness->informantAvailable,
            'blocks' => $completeness->blocks,
            // Cle "contradictions" conservee pour le schema ; contenu = divergences temporelles.
            'contradictions' => $completeness->discrepancies,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapInstrument(InstrumentResult $result): array
    {
        return [
            'instrument_code' => $result->instrumentCode,
            'version' => $result->instrumentVersion,
            'score' => $result->rawScore,
            'official_interpretation' => $result->officialInterpretation,
            'licensed' => $result->licensed,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapStructure(ClinicalStructure $structure): array
    {
        $out = [];
        foreach (['A1', 'A2', 'A3', 'B1', 'B2', 'B3', 'B4', 'developmental_onset', 'functional_impact'] as $code) {
            $summary = $structure->domain($code);
            if ($summary !== null) {
                $out[$code] = $this->mapDomain($summary);
            }
        }
        $out['differential_exploration'] = $structure->differentialExploration;
        $out['differential_uncertainty'] = $structure->differentialUncertainty->value;
        if ($structure->camouflaging !== null) {
            $out['camouflaging'] = $this->mapDomain($structure->camouflaging);
        }

        return $out;
    }

    /**
     * @return array<string, mixed>
     */
    private function mapDomain(DomainSummary $summary): array
    {
        return [
            'status' => $summary->status->value,
            'confidence' => $summary->confidence,
            'evidence_count' => $summary->evidenceCount,
            'contradiction_count' => $summary->contradictionCount,
            'summary' => $summary->summary,
            'source_refs' => $summary->sourceRefs,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function mapOrientation(OrientationResult $orientation): array
    {
        return [
            'level' => $orientation->level->value,
            'message' => $orientation->message,
            'recommended_actions' => $orientation->recommendedActions,
            'urgent_reason' => $orientation->urgentReason,
            'graded_level_validated' => $orientation->gradedLevelValidated,
            'notes' => $orientation->notes,
        ];
    }

    /**
     * Chaque affirmation structurante est rattachee a ses items sources
     * (specification 11.3 : tracabilite obligatoire).
     *
     * @return list<array<string, mixed>>
     */
    private function mapEvidenceTrace(ClinicalStructure $structure): array
    {
        $trace = [];
        foreach ($structure->domains as $code => $summary) {
            if ($summary->sourceRefs === []) {
                continue;
            }
            $trace[] = [
                'statement_id' => 'domain:' . $code,
                'evidence_type' => 'explicit_response',
                'source_refs' => $summary->sourceRefs,
                'confidence' => $summary->confidence,
            ];
        }

        return $trace;
    }
}
