<?php

declare(strict_types=1);

namespace TsaRepere\Form;

/**
 * Definition immuable et versionnee d'un item du formulaire structure.
 *
 * Le formulaire principal est une trame originale de recueil (specification 6.1)
 * et n'est pas une echelle psychometrique validee.
 */
final readonly class QuestionDefinition
{
    /**
     * @param list<Period>   $periods    Periodes pour lesquelles l'item peut etre renseigne.
     * @param list<AgeBand>  $ageRoutes  Tranches d'age auxquelles l'item s'applique.
     */
    public function __construct(
        public string $code,
        public string $section,
        public string $domain,
        public string $labelFr,
        public array $periods,
        public array $ageRoutes,
        public string $responseScale,
        public bool $requiredForCompletion,
    ) {
    }

    public function appliesTo(AgeBand $ageBand): bool
    {
        return \in_array($ageBand, $this->ageRoutes, true);
    }

    public function supportsPeriod(Period $period): bool
    {
        return \in_array($period, $this->periods, true);
    }
}
