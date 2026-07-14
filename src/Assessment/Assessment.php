<?php

declare(strict_types=1);

namespace TsaRepere\Assessment;

use TsaRepere\Form\AgeBand;

/**
 * Une evaluation en cours ou terminee : contexte, consentements, reponses et
 * eventuel risque urgent. Objet de travail du coeur metier (les entites
 * persistees cote Symfony viendront s'y adosser, specification 10.4).
 */
final class Assessment
{
    /** @var list<Response> */
    private array $responses = [];

    /**
     * @param list<Response> $responses
     */
    public function __construct(
        public readonly string $id,
        public readonly AgeBand $ageBand,
        public readonly string $respondentType = 'self',
        public readonly string $mode = 'anonymous',
        public readonly UrgentRisk $urgentRisk = new UrgentRisk(),
        array $responses = [],
    ) {
        foreach ($responses as $response) {
            $this->addResponse($response);
        }
    }

    /**
     * Enregistre une reponse. Unicite par (question_code, period) : une nouvelle
     * reponse pour le meme couple remplace la precedente (upsert, review P1-12).
     * Ainsi le resultat ne depend plus de l'ordre d'insertion.
     */
    public function addResponse(Response $response): void
    {
        foreach ($this->responses as $index => $existing) {
            if ($existing->questionCode === $response->questionCode && $existing->period === $response->period) {
                $this->responses[$index] = $response;
                $this->responses = array_values($this->responses);

                return;
            }
        }
        $this->responses[] = $response;
    }

    /** @return list<Response> */
    public function responses(): array
    {
        return $this->responses;
    }

    /**
     * Reponses portant sur un code d'item donne.
     *
     * @return list<Response>
     */
    public function responsesForCode(string $code): array
    {
        return array_values(array_filter(
            $this->responses,
            static fn (Response $r): bool => $r->questionCode === $code,
        ));
    }

    public function hasExternalInformant(): bool
    {
        if ($this->respondentType !== 'self') {
            return true;
        }
        foreach ($this->responses as $response) {
            if (\in_array($response->sourceType, ['parent_report', 'partner_report', 'professional_observation'], true)) {
                return true;
            }
        }

        return false;
    }
}
