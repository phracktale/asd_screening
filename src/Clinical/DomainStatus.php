<?php

declare(strict_types=1);

namespace TsaRepere\Clinical;

/**
 * Statut de documentation d'un domaine clinique (specification 7.3).
 * Il organise le dossier ; il ne reproduit pas un critere diagnostique DSM.
 */
enum DomainStatus: string
{
    case Documented = 'documented';
    case PossiblyDocumented = 'possibly_documented';
    case NotDocumented = 'not_documented';
    case Contradictory = 'contradictory';
    case NotAssessed = 'not_assessed';
}
