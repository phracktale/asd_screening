<?php

declare(strict_types=1);

namespace TsaRepere\Form;

/**
 * Periode a laquelle se rapporte une reponse. La specification (6.1) impose de
 * distinguer le fonctionnement actuel de la periode developpementale precoce.
 */
enum Period: string
{
    case Current = 'current';
    case Childhood = 'childhood';
    case EarlyDevelopment = 'early_development';
    case Lifetime = 'lifetime';
    case NotApplicable = 'not_applicable';
}
