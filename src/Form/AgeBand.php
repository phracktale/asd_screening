<?php

declare(strict_types=1);

namespace TsaRepere\Form;

/**
 * Tranche d'age de la personne evaluee. Determine le routage du formulaire
 * (chapitre 6 de la specification) et le domaine de validite des instruments.
 */
enum AgeBand: string
{
    case Toddler = 'toddler';
    case Child = 'child';
    case Adolescent = 'adolescent';
    case Adult = 'adult';

    /**
     * Bornes indicatives utilisees pour le routage. Elles ne constituent pas
     * un decoupage clinique officiel et peuvent etre revisees par le comite.
     */
    public static function fromAgeYears(float $ageYears): self
    {
        return match (true) {
            $ageYears < 0 => throw new \InvalidArgumentException('L\'age ne peut pas etre negatif.'),
            $ageYears < 4 => self::Toddler,
            $ageYears < 12 => self::Child,
            $ageYears < 18 => self::Adolescent,
            default => self::Adult,
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Toddler => 'Tout-petit',
            self::Child => 'Enfant',
            self::Adolescent => 'Adolescent',
            self::Adult => 'Adulte',
        };
    }
}
