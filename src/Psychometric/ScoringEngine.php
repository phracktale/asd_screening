<?php

declare(strict_types=1);

namespace TsaRepere\Psychometric;

/**
 * Moteur 1 - Psychometrie (specification 5.1).
 *
 * Transparent, sans machine learning. Il applique les regles officielles d'un
 * instrument uniquement lorsque la licence le permet, conserve la provenance,
 * et n'interprete jamais un seuil comme un diagnostic.
 */
final class ScoringEngine
{
    public function __construct(
        private readonly LicenseRegistry $licenses,
    ) {
    }

    /**
     * Calcule le score a partir de points binaires deja cotes item par item
     * (0 ou 1). Autorise uniquement si la licence permet le calcul en application.
     *
     * @param list<int> $binaryItemScores
     */
    public function scoreFromBinaryItems(
        string $instrumentCode,
        string $instrumentVersion,
        array $binaryItemScores,
        string $language = 'fr-FR',
    ): InstrumentResult {
        $record = $this->requireRecord($instrumentCode);

        if (!$record->scoringAllowed) {
            throw new LicenseViolationException(\sprintf(
                'Le calcul en application de "%s" n\'est pas autorise par sa licence. Utilisez la saisie d\'un score obtenu ailleurs.',
                $instrumentCode,
            ));
        }

        if ($record->itemCount !== null && \count($binaryItemScores) !== $record->itemCount) {
            throw new \InvalidArgumentException(\sprintf(
                'L\'instrument "%s" attend %d items, %d fournis.',
                $instrumentCode,
                $record->itemCount,
                \count($binaryItemScores),
            ));
        }

        foreach ($binaryItemScores as $point) {
            if ($point !== 0 && $point !== 1) {
                throw new \InvalidArgumentException('Chaque item cote doit valoir 0 ou 1.');
            }
        }

        $score = array_sum($binaryItemScores);

        return $this->buildResult(
            $record,
            $instrumentVersion,
            (float) $score,
            InstrumentResult::SOURCE_CALCULATED,
            $language,
        );
    }

    /**
     * Enregistre un score obtenu ailleurs (saisi par un professionnel ou importe).
     * Toujours autorise si la licence permet la saisie de score.
     */
    public function recordEnteredScore(
        string $instrumentCode,
        string $instrumentVersion,
        float $score,
        string $source = InstrumentResult::SOURCE_ENTERED_PRO,
        string $language = 'fr-FR',
    ): InstrumentResult {
        $record = $this->requireRecord($instrumentCode);

        if (!$record->scoreEntryAllowed) {
            throw new LicenseViolationException(\sprintf(
                'La saisie d\'un score pour "%s" n\'est pas autorisee.',
                $instrumentCode,
            ));
        }

        return $this->buildResult($record, $instrumentVersion, $score, $source, $language);
    }

    private function buildResult(
        LicenseRecord $record,
        string $instrumentVersion,
        float $score,
        string $source,
        string $language,
    ): InstrumentResult {
        $threshold = $record->threshold;
        $reached = $threshold !== null && $score >= $threshold;

        return new InstrumentResult(
            instrumentCode: $record->instrumentCode,
            instrumentVersion: $instrumentVersion,
            rawScore: $score,
            threshold: $threshold,
            thresholdReached: $reached,
            officialInterpretation: $this->interpretation($threshold, $reached),
            source: $source,
            licensed: $record->status === 'permitted',
            language: $language,
        );
    }

    private function interpretation(?int $threshold, bool $reached): string
    {
        if ($threshold === null) {
            return 'Score enregistre. Aucun seuil officiel configure ; se referer au manuel de l\'instrument. Ce score n\'est pas un diagnostic.';
        }

        return $reached
            ? \sprintf('Score au moins egal au seuil (%d) : une evaluation specialisee est a envisager. Ce score n\'est pas un diagnostic.', $threshold)
            : \sprintf('Score inferieur au seuil (%d). Ce resultat n\'exclut pas un TSA, notamment en cas de camouflage. Ce score n\'est pas un diagnostic.', $threshold);
    }

    private function requireRecord(string $instrumentCode): LicenseRecord
    {
        $record = $this->licenses->get($instrumentCode);
        if ($record === null) {
            throw new \RuntimeException(\sprintf('Instrument inconnu du registre de licences : %s', $instrumentCode));
        }

        return $record;
    }
}
