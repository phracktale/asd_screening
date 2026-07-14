<?php

declare(strict_types=1);

namespace TsaRepere\Assessment;

/**
 * Nature d'une reponse. Un item ordinal porte une intensite 0-4 ; un item
 * categoriel (differentiels, section F) porte une valeur de vocabulaire dediee.
 *
 * Ce discriminant remplace l'ancien contournement qui stockait un statut
 * categoriel dans le champ texte libre `example` (voir KNOWN_ISSUES P0-2).
 */
enum ResponseKind: string
{
    case Ordinal = 'ordinal';
    case Categorical = 'categorical';
}
