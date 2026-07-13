<?php

declare(strict_types=1);

namespace TsaRepere\Form;

/**
 * Charge le catalogue d'items depuis le CSV canonique
 * (packages/assessment-schema/data/question_catalog.csv) vers une FormDefinition.
 *
 * Le CSV reste la source de verite editable ; ce loader ne fait aucune
 * interpretation clinique.
 */
final class CatalogLoader
{
    public function __construct(
        private readonly string $version = '1.0.0',
    ) {
    }

    public function loadFromCsv(string $path): FormDefinition
    {
        if (!is_readable($path)) {
            throw new \RuntimeException(\sprintf('Catalogue introuvable ou illisible : %s', $path));
        }

        $handle = fopen($path, 'rb');
        if ($handle === false) {
            throw new \RuntimeException(\sprintf('Impossible d\'ouvrir le catalogue : %s', $path));
        }

        try {
            $header = fgetcsv($handle, escape: '');
            if ($header === false) {
                throw new \RuntimeException('Catalogue vide.');
            }
            $columns = array_flip($header);
            $this->assertColumns($columns);

            $questions = [];
            while (($row = fgetcsv($handle, escape: '')) !== false) {
                if ($row === [null] || $row === []) {
                    continue; // ligne vide
                }
                $questions[] = $this->mapRow($row, $columns);
            }
        } finally {
            fclose($handle);
        }

        return new FormDefinition($this->version, $questions);
    }

    /**
     * @param array<string, int> $columns
     */
    private function assertColumns(array $columns): void
    {
        $required = ['code', 'section', 'domain', 'label_fr', 'periods', 'age_routes', 'response_scale', 'required_for_completion'];
        foreach ($required as $name) {
            if (!isset($columns[$name])) {
                throw new \RuntimeException(\sprintf('Colonne manquante dans le catalogue : %s', $name));
            }
        }
    }

    /**
     * @param list<string>       $row
     * @param array<string, int> $columns
     */
    private function mapRow(array $row, array $columns): QuestionDefinition
    {
        return new QuestionDefinition(
            code: trim($row[$columns['code']]),
            section: trim($row[$columns['section']]),
            domain: trim($row[$columns['domain']]),
            labelFr: trim($row[$columns['label_fr']]),
            periods: $this->parsePeriods($row[$columns['periods']]),
            ageRoutes: $this->parseAgeRoutes($row[$columns['age_routes']]),
            responseScale: trim($row[$columns['response_scale']]),
            requiredForCompletion: filter_var($row[$columns['required_for_completion']], \FILTER_VALIDATE_BOOL),
        );
    }

    /**
     * @return list<Period>
     */
    private function parsePeriods(string $raw): array
    {
        $periods = [];
        foreach ($this->splitPipe($raw) as $token) {
            $periods[] = Period::from($token);
        }

        return $periods;
    }

    /**
     * @return list<AgeBand>
     */
    private function parseAgeRoutes(string $raw): array
    {
        $bands = [];
        foreach ($this->splitPipe($raw) as $token) {
            $bands[] = AgeBand::from($token);
        }

        return $bands;
    }

    /**
     * @return list<string>
     */
    private function splitPipe(string $raw): array
    {
        return array_values(array_filter(
            array_map('trim', explode('|', $raw)),
            static fn (string $s): bool => $s !== '',
        ));
    }
}
