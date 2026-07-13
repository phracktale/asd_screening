<?php

declare(strict_types=1);

namespace TsaRepere\Assessment;

use TsaRepere\Form\Period;

/**
 * Reponse a un item pour une periode donnee. La valeur suit l'echelle de la
 * specification (6.1) : entier 0-4, ou "unknown" / "not_applicable".
 */
final readonly class Response
{
    public const string UNKNOWN = 'unknown';
    public const string NOT_APPLICABLE = 'not_applicable';

    /**
     * @param int|string  $value  0..4, self::UNKNOWN ou self::NOT_APPLICABLE
     */
    public function __construct(
        public string $questionCode,
        public Period $period,
        public int|string $value,
        public ?string $impact = null,
        public ?string $example = null,
        public ?string $sourceType = null,
        public ?string $sourceConfidence = null,
        public ?float $ageOfOnset = null,
    ) {
        if (\is_int($value)) {
            if ($value < 0 || $value > 4) {
                throw new \InvalidArgumentException('La valeur numerique doit etre comprise entre 0 et 4.');
            }
        } elseif ($value !== self::UNKNOWN && $value !== self::NOT_APPLICABLE) {
            throw new \InvalidArgumentException(\sprintf('Valeur de reponse invalide : %s', $value));
        }
    }

    /** Une reponse est "conclusive" si elle porte une valeur numerique exploitable. */
    public function isAnswered(): bool
    {
        return \is_int($this->value);
    }

    public function isUnknown(): bool
    {
        return $this->value === self::UNKNOWN;
    }

    public function isNotApplicable(): bool
    {
        return $this->value === self::NOT_APPLICABLE;
    }

    /** Intensite numerique (0 si non conclusive). */
    public function intensity(): int
    {
        return \is_int($this->value) ? $this->value : 0;
    }
}
