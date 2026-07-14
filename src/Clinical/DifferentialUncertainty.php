<?php

declare(strict_types=1);

namespace TsaRepere\Clinical;

/**
 * Degre d'incertitude differentielle non resolue (review P0-6).
 *
 * Une condition differentielle concomitante ne doit pas faire baisser la
 * suspicion de TSA, mais une ambiguite majeure non exploree doit empecher une
 * sortie trop affirmative (specification 7.3 : donnees pas mieux expliquees par
 * une autre condition non exploree).
 */
enum DifferentialUncertainty: string
{
    case None = 'none';
    case Limited = 'limited';
    case Significant = 'significant';
    case Unresolved = 'unresolved';

    /** L'incertitude est-elle suffisante pour interdire une conclusion affirmative ? */
    public function blocksAffirmativeConclusion(): bool
    {
        return $this === self::Significant || $this === self::Unresolved;
    }
}
