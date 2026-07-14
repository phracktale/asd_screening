<?php

declare(strict_types=1);

namespace TsaRepere\Orientation;

/**
 * Recommandation prudente d'orientation (specification 7.1, sortie "orientation").
 *
 * `gradedLevelValidated` indique si le niveau gradue (faible/intermediaire/eleve)
 * repose sur des seuils valides cliniquement. Tant que ce n'est pas le cas, la
 * graduation est un prototype de recherche (review P0-5).
 */
final readonly class OrientationResult
{
    /**
     * @param list<string> $recommendedActions
     * @param list<string> $notes remarques d'interpretation (camouflage, divergences, differentiels)
     */
    public function __construct(
        public SuspicionLevel $level,
        public string $message,
        public array $recommendedActions,
        public ?string $urgentReason = null,
        public bool $gradedLevelValidated = false,
        public array $notes = [],
    ) {
    }

    /** Le niveau est-il une graduation de suspicion (par opposition a urgent/indetermine) ? */
    public function isGraded(): bool
    {
        return \in_array($this->level, [SuspicionLevel::Low, SuspicionLevel::Intermediate, SuspicionLevel::High], true);
    }
}
