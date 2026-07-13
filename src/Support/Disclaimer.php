<?php

declare(strict_types=1);

namespace TsaRepere\Support;

/**
 * Textes d'avertissement standard. L'application ne pose jamais de diagnostic
 * (specification 0 et 7.5) et n'affiche jamais une conclusion categorique.
 */
final class Disclaimer
{
    public const string STANDARD = 'Cet outil realise un reperage et une orientation. Il ne pose pas de diagnostic. '
        . 'Seul un professionnel competent peut, apres une evaluation complete, conclure a un trouble du spectre de l\'autisme. '
        . 'Un resultat faible n\'exclut pas un TSA, notamment en cas de camouflage.';

    /**
     * Formulations interdites en sortie (utilise par les tests de non-regression).
     *
     * @return list<string>
     */
    public static function forbiddenPhrases(): array
    {
        return [
            'vous etes autiste',
            'vous n\'etes pas autiste',
            'vous n etes pas autiste',
            'diagnostic de tsa confirme',
            'vous souffrez d\'autisme',
        ];
    }
}
