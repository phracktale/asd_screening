<?php

declare(strict_types=1);

namespace TsaRepere\Psychometric;

/**
 * Levee lorsqu'une operation (affichage d'items, calcul de score) est interdite
 * par la licence de l'instrument.
 */
final class LicenseViolationException extends \RuntimeException
{
}
