<?php

declare(strict_types=1);

namespace TsaRepere\Orientation;

/**
 * Recommandation prudente d'orientation (specification 7.1, sortie "orientation").
 */
final readonly class OrientationResult
{
    /**
     * @param list<string> $recommendedActions
     */
    public function __construct(
        public SuspicionLevel $level,
        public string $message,
        public array $recommendedActions,
        public ?string $urgentReason = null,
    ) {
    }
}
