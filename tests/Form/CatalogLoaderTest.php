<?php

declare(strict_types=1);

namespace TsaRepere\Tests\Form;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TsaRepere\Form\AgeBand;
use TsaRepere\Form\CatalogLoader;
use TsaRepere\Form\FormDefinition;

final class CatalogLoaderTest extends TestCase
{
    private const string CATALOG = __DIR__ . '/../../packages/assessment-schema/data/question_catalog.csv';

    private function load(): FormDefinition
    {
        return (new CatalogLoader('1.0.0'))->loadFromCsv(self::CATALOG);
    }

    #[Test]
    public function it_loads_the_canonical_catalog(): void
    {
        $form = $this->load();

        self::assertSame('1.0.0', $form->version);
        self::assertNotEmpty($form->questions);
        self::assertTrue($form->has('A1.01'));
        self::assertSame('A1', $form->get('A1.01')?->domain);
    }

    #[Test]
    public function it_routes_items_by_age_band(): void
    {
        $form = $this->load();

        // A1.01 est route child|adolescent|adult : absent chez le tout-petit.
        self::assertFalse($form->get('A1.01')?->appliesTo(AgeBand::Toddler));
        self::assertTrue($form->get('A1.01')?->appliesTo(AgeBand::Adult));

        // A1.03 est route pour toutes les tranches, tout-petit inclus.
        self::assertTrue($form->get('A1.03')?->appliesTo(AgeBand::Toddler));

        $toddlerItems = $form->forAgeBand(AgeBand::Toddler);
        $adultItems = $form->forAgeBand(AgeBand::Adult);

        self::assertLessThan(\count($adultItems), \count($toddlerItems));
        foreach ($toddlerItems as $item) {
            self::assertTrue($item->appliesTo(AgeBand::Toddler));
        }
    }

    #[Test]
    public function camouflage_items_target_adolescents_and_adults_only(): void
    {
        $form = $this->load();

        // Section E = camouflage (aligne sur la spec 6.8 apres correction E/F).
        self::assertSame('camouflaging', $form->get('E.01')?->domain);
        self::assertFalse($form->get('E.01')?->appliesTo(AgeBand::Child));
        self::assertTrue($form->get('E.01')?->appliesTo(AgeBand::Adult));
    }

    #[Test]
    public function sections_and_domains_are_consistent(): void
    {
        $form = $this->load();

        // Correspondance section -> domaines autorises (review P1-1).
        $allowed = [
            'A' => ['A1', 'A2', 'A3'],
            'B' => ['B1', 'B2', 'B3', 'B4'],
            'E' => ['camouflaging'],
            'F' => ['differential'],
        ];

        foreach ($form->questions as $q) {
            if (isset($allowed[$q->section])) {
                self::assertContains(
                    $q->domain,
                    $allowed[$q->section],
                    \sprintf('Item %s : domaine %s incoherent avec la section %s', $q->code, $q->domain, $q->section),
                );
            }
        }
    }
}
