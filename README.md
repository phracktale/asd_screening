# TSA-Repère

Application de **repérage et d'orientation** du trouble du spectre de l'autisme (TSA).

> ⚠️ **Ce projet ne pose pas de diagnostic.** Il structure le recueil d'informations,
> calcule les scores d'instruments *lorsque la licence le permet*, produit une synthèse
> explicable et estime un **niveau de suspicion** (faible / intermédiaire / élevé / **indéterminé**).
> Seul un professionnel compétent peut conclure à un TSA après évaluation complète.

La spécification complète (clinique, scientifique, fonctionnelle, technique) se trouve dans
[`docs/specifications/`](docs/specifications/).

## État d'avancement

Développement démarré sur la **Phase 2 — MVP sans modèle clinique** (plan de réalisation, chap. 16),
avec un **périmètre multi-âge** (tout-petit / enfant / adolescent / adulte).

Ce qui est en place aujourd'hui — le **cœur métier**, framework-agnostique et testé :

| Brique | Emplacement | Rôle (spec) |
|---|---|---|
| Moteur de formulaire multi-âge | `src/Form/` | Routage par tranche d'âge, versionné (chap. 6) |
| Moteur 1 — Psychométrie | `src/Psychometric/` | Scores officiels + **registre de licences** (chap. 5.1, 6.11) |
| Moteur 2 — Structuration clinique | `src/Clinical/` | Domaines documentés, complétude, contradictions (chap. 7.2, 7.3) |
| Orientation & sécurité | `src/Orientation/` | Niveaux de suspicion, règle d'urgence, « indéterminé » (chap. 7.4, 7.5) |
| Assemblage du rapport | `src/Output/` | 4 sorties séparées + traçabilité, ML désactivable (chap. 7.1, 11.3) |

Le **moteur 3 (ML expérimental)** est volontairement **absent du MVP** : `ml_estimate`
reste `null`. Il ne sera branché qu'après des données à cible indépendante (Phases 3–4).

## Structure du dépôt (monorepo, chap. 10.3)

```
src/                          Cœur métier PHP (namespace TsaRepere\)
tests/                        Tests PHPUnit
bin/demo.php                  Démonstration exécutable de bout en bout
packages/
  assessment-schema/          Schémas JSON + catalogue d'items (source de vérité)
  instrument-definitions/     Registre de licences des instruments
apps/
  web-symfony/                Application web Symfony (à venir)
  ml-api/                     Service d'inférence Python/FastAPI (Phases 3–4)
data-pipeline/                Ingestion / qualité / entraînement / évaluation (Phase 1)
infrastructure/              Docker, monitoring (à venir)
docs/specifications/          Spécification de cadrage
```

## Démarrage

Prérequis : PHP ≥ 8.2, Composer.

```bash
composer install
composer test          # exécute la suite PHPUnit
php bin/demo.php        # affiche un rapport JSON d'exemple
```

## Principes de conception (non négociables)

- **Jamais** de formulation diagnostique (« vous êtes autiste » / « vous ne l'êtes pas »).
  Un test de non-régression le vérifie sur toutes les sorties.
- **Séparation stricte** des sorties : psychométrie, structuration, ML, orientation ne sont
  jamais additionnés en un score global opaque.
- **Sécurité d'abord** : un risque urgent déclaré court-circuite toute interprétation TSA.
- **Licences respectées** : aucun item d'instrument protégé n'est affiché ou recalculé sans droits.
- **Résilience** : le formulaire et la synthèse fonctionnent même si le service ML est absent.

## Points ouverts

Voir [`docs/KNOWN_ISSUES.md`](docs/KNOWN_ISSUES.md) pour les incohérences relevées dans les
artefacts de spécification, et le chapitre 18 de la spec pour les décisions d'architecture restantes.
