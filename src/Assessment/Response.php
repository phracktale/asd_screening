<?php

declare(strict_types=1);

namespace TsaRepere\Assessment;

use TsaRepere\Form\Period;

/**
 * Reponse a un item pour une periode donnee.
 *
 * Deux natures (specification 6.1 et 6.9) :
 *  - ordinale : entier 0-4, ou "unknown" / "not_applicable" ;
 *  - categorielle : une valeur de {@see DifferentialStatus} (items differentiels).
 *
 * Le statut categoriel est desormais porte par un champ discrimine et non plus
 * infere depuis le texte libre (review P0-2, correction du bug unknown -> known).
 */
final readonly class Response
{
    public const string UNKNOWN = 'unknown';
    public const string NOT_APPLICABLE = 'not_applicable';

    /**
     * @param int|string|null $value            0..4, self::UNKNOWN, self::NOT_APPLICABLE (ordinal) ; null si categoriel
     * @param string|null     $categoricalValue valeur de DifferentialStatus si $kind est Categorical
     */
    public function __construct(
        public string $questionCode,
        public Period $period,
        public int|string|null $value = null,
        public ?string $impact = null,
        public ?string $example = null,
        public ?string $sourceType = null,
        public ?string $sourceConfidence = null,
        public ?float $ageOfOnset = null,
        public ResponseKind $kind = ResponseKind::Ordinal,
        public ?string $categoricalValue = null,
    ) {
        if ($kind === ResponseKind::Ordinal) {
            $this->assertOrdinal($value, $categoricalValue);
        } else {
            $this->assertCategorical($value, $categoricalValue);
        }
    }

    /** Fabrique d'une reponse ordinale. */
    public static function ordinal(
        string $questionCode,
        Period $period,
        int|string $value,
        ?string $impact = null,
        ?string $example = null,
        ?string $sourceType = null,
        ?string $sourceConfidence = null,
        ?float $ageOfOnset = null,
    ): self {
        return new self(
            questionCode: $questionCode,
            period: $period,
            value: $value,
            impact: $impact,
            example: $example,
            sourceType: $sourceType,
            sourceConfidence: $sourceConfidence,
            ageOfOnset: $ageOfOnset,
            kind: ResponseKind::Ordinal,
        );
    }

    /** Fabrique d'une reponse categorielle (differentiels). */
    public static function categorical(
        string $questionCode,
        Period $period,
        DifferentialStatus $status,
        ?string $comment = null,
        ?string $sourceType = null,
    ): self {
        return new self(
            questionCode: $questionCode,
            period: $period,
            value: null,
            example: $comment,
            sourceType: $sourceType,
            kind: ResponseKind::Categorical,
            categoricalValue: $status->value,
        );
    }

    public function isCategorical(): bool
    {
        return $this->kind === ResponseKind::Categorical;
    }

    public function differentialStatus(): ?DifferentialStatus
    {
        if (!$this->isCategorical() || $this->categoricalValue === null) {
            return null;
        }

        return DifferentialStatus::tryFrom($this->categoricalValue);
    }

    /** Une reponse ordinale est "conclusive" si elle porte une valeur numerique. */
    public function isAnswered(): bool
    {
        return $this->kind === ResponseKind::Ordinal && \is_int($this->value);
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

    private function assertOrdinal(int|string|null $value, ?string $categoricalValue): void
    {
        if ($categoricalValue !== null) {
            throw new \InvalidArgumentException('Une reponse ordinale ne doit pas porter de valeur categorielle.');
        }
        if (\is_int($value)) {
            if ($value < 0 || $value > 4) {
                throw new \InvalidArgumentException('La valeur numerique doit etre comprise entre 0 et 4.');
            }

            return;
        }
        if ($value !== self::UNKNOWN && $value !== self::NOT_APPLICABLE) {
            throw new \InvalidArgumentException(\sprintf('Valeur de reponse ordinale invalide : %s', var_export($value, true)));
        }
    }

    private function assertCategorical(int|string|null $value, ?string $categoricalValue): void
    {
        if ($value !== null) {
            throw new \InvalidArgumentException('Une reponse categorielle ne doit pas porter de valeur ordinale.');
        }
        if ($categoricalValue === null || DifferentialStatus::tryFrom($categoricalValue) === null) {
            throw new \InvalidArgumentException(\sprintf('Valeur categorielle invalide : %s', var_export($categoricalValue, true)));
        }
    }
}
