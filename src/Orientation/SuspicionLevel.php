<?php

declare(strict_types=1);

namespace TsaRepere\Orientation;

/**
 * Niveau de suspicion produit par l'application (specification 7.4).
 * "urgent" court-circuite l'interpretation TSA (regle de securite 7.5).
 */
enum SuspicionLevel: string
{
    case Low = 'low';
    case Intermediate = 'intermediate';
    case High = 'high';
    case Indeterminate = 'indeterminate';
    case Urgent = 'urgent';
}
