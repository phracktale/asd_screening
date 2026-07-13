<?php

declare(strict_types=1);

namespace TsaRepere\Form;

/**
 * Ensemble versionne des items du formulaire. Le routage par tranche d'age
 * (specification 6, chap. 18 decision multi-age) est assure par forAgeBand().
 */
final readonly class FormDefinition
{
    /** @var array<string, QuestionDefinition> indexe par code d'item */
    private array $byCode;

    /**
     * @param list<QuestionDefinition> $questions
     */
    public function __construct(
        public string $version,
        public array $questions,
    ) {
        $byCode = [];
        foreach ($questions as $question) {
            if (isset($byCode[$question->code])) {
                throw new \InvalidArgumentException(
                    \sprintf('Code d\'item duplique dans le formulaire : %s', $question->code)
                );
            }
            $byCode[$question->code] = $question;
        }
        $this->byCode = $byCode;
    }

    /**
     * Items applicables a une tranche d'age donnee.
     *
     * @return list<QuestionDefinition>
     */
    public function forAgeBand(AgeBand $ageBand): array
    {
        return array_values(array_filter(
            $this->questions,
            static fn (QuestionDefinition $q): bool => $q->appliesTo($ageBand),
        ));
    }

    public function get(string $code): ?QuestionDefinition
    {
        return $this->byCode[$code] ?? null;
    }

    public function has(string $code): bool
    {
        return isset($this->byCode[$code]);
    }
}
