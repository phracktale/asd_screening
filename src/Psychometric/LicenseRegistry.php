<?php

declare(strict_types=1);

namespace TsaRepere\Psychometric;

/**
 * Registre de licences des instruments, charge depuis le JSON canonique
 * (packages/instrument-definitions/license_registry.json).
 *
 * Aucun item d'instrument protege ne doit etre affiche ni recalcule tant que
 * la licence ne l'autorise pas explicitement (specification 6.11).
 */
final class LicenseRegistry
{
    /** @var array<string, LicenseRecord> */
    private array $records = [];

    /**
     * @param list<LicenseRecord> $records
     */
    public function __construct(array $records = [])
    {
        foreach ($records as $record) {
            $this->records[$record->instrumentCode] = $record;
        }
    }

    public static function fromJsonFile(string $path): self
    {
        if (!is_readable($path)) {
            throw new \RuntimeException(\sprintf('Registre de licences illisible : %s', $path));
        }
        $decoded = json_decode((string) file_get_contents($path), true, flags: \JSON_THROW_ON_ERROR);
        if (!\is_array($decoded) || !isset($decoded['records']) || !\is_array($decoded['records'])) {
            throw new \RuntimeException('Structure du registre de licences invalide.');
        }

        $records = [];
        foreach ($decoded['records'] as $row) {
            $records[] = new LicenseRecord(
                instrumentCode: (string) $row['instrument_code'],
                instrumentName: (string) ($row['instrument_name'] ?? $row['instrument_code']),
                rightsHolder: (string) ($row['rights_holder'] ?? 'inconnu'),
                status: (string) ($row['status'] ?? 'unknown'),
                itemsDisplayable: (bool) ($row['items_displayable'] ?? false),
                scoringAllowed: (bool) ($row['scoring_allowed'] ?? false),
                scoreEntryAllowed: (bool) ($row['score_entry_allowed'] ?? false),
                threshold: isset($row['threshold']) ? (int) $row['threshold'] : null,
                itemCount: isset($row['item_count']) ? (int) $row['item_count'] : null,
                sourceUrl: isset($row['source_url']) ? (string) $row['source_url'] : null,
                notes: isset($row['notes']) ? (string) $row['notes'] : null,
            );
        }

        return new self($records);
    }

    public function get(string $instrumentCode): ?LicenseRecord
    {
        return $this->records[$instrumentCode] ?? null;
    }

    public function canDisplayItems(string $instrumentCode): bool
    {
        return $this->get($instrumentCode)?->itemsDisplayable ?? false;
    }

    public function canScoreInApp(string $instrumentCode): bool
    {
        return $this->get($instrumentCode)?->scoringAllowed ?? false;
    }

    public function canEnterScore(string $instrumentCode): bool
    {
        return $this->get($instrumentCode)?->scoreEntryAllowed ?? false;
    }
}
