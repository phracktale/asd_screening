<?php

declare(strict_types=1);

namespace TsaRepere\Assessment;

/**
 * Elements de risque immediat declares a l'ecran 2 (specification 6.3).
 *
 * Regle de securite (7.5) : un risque suicidaire ou un danger immediat ne doit
 * jamais etre traite par la logique TSA ; il declenche une orientation d'urgence
 * et interrompt l'interpretation standard.
 */
final readonly class UrgentRisk
{
    public const string YES = 'yes';
    public const string NO = 'no';
    public const string UNKNOWN = 'unknown';

    public function __construct(
        public string $suicidalIdeation = self::UNKNOWN,
        public string $immediateDanger = self::UNKNOWN,
        public string $acutePsychosisOrMania = self::UNKNOWN,
    ) {
    }

    public function isPresent(): bool
    {
        return $this->suicidalIdeation === self::YES
            || $this->immediateDanger === self::YES
            || $this->acutePsychosisOrMania === self::YES;
    }

    /** @return list<string> libelles des facteurs declencheurs */
    public function triggeredReasons(): array
    {
        $reasons = [];
        if ($this->suicidalIdeation === self::YES) {
            $reasons[] = 'idees suicidaires declarees';
        }
        if ($this->immediateDanger === self::YES) {
            $reasons[] = 'danger immediat declare';
        }
        if ($this->acutePsychosisOrMania === self::YES) {
            $reasons[] = 'etat aigu (psychose ou manie) declare';
        }

        return $reasons;
    }
}
