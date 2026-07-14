# Points ouverts, corrections et dette

Suivi des écarts relevés entre les artefacts de spécification et le code, enrichi
par une relecture critique du cœur métier. Classement par priorité.

Légende : ✅ corrigé · 🟡 partiel · ⬜ à faire.

## P0 — bloquants avant toute démonstration utilisateur

| # | Sujet | État | Détail |
|---|---|---|---|
| P0-2 | Stockage catégoriel dans `example` + bug `unknown → known` | ✅ | Ancienne lecture par sous-chaîne supprimée. Nouveau type de réponse **catégorielle** discriminé (`ResponseKind`, `DifferentialStatus`), vocabulaire `diagnosed / suspected / under_evaluation / ruled_out / not_reported / unknown`. Schéma du formulaire mis à jour (oneOf ordinal/catégoriel). Test de non-régression : `ResponseTest::a_categorical_unknown_is_never_read_as_diagnosed`. |
| P0-11 | Résumé du retentissement constant, contredisant le statut | ✅ | `summariseFunctionalImpact()` génère un narratif aligné sur le statut. Test `ReviewFixesTest::functional_impact_summary_matches_its_status`. |
| P0-10 | Un seul item pouvait « documenter » un domaine | ✅ | Minimum de 2 items concordants (`MIN_SUPPORT_FOR_DOCUMENTED`). Test `ReviewFixesTest::a_single_marked_item_does_not_document_a_domain`. |
| P0-6 | Différentiels ignorés par l'orientation | ✅ | `DifferentialUncertainty` (none/limited/significant/unresolved) calculée et consommée : une incertitude majeure interdit une conclusion « élevée ». Un diagnostic concomitant **ne** baisse **pas** la suspicion. Tests `unresolved_differential_blocks_a_high_conclusion`, `diagnosed_comorbidity_does_not_prevent_a_high_conclusion`. |
| P0-5 | Niveaux gradués issus d'une trame non validée | ✅ | Les niveaux faible/intermédiaire/élevé sortent marqués `graded_level_validated: false` et préfixés « [Prototype non validé cliniquement] ». Activables via `new OrientationEngine(gradedLevelsValidated: true)` une fois validés sur cohorte. Urgent et indéterminé ne sont pas des graduations. |
| P0-3 | Vraies sections C (développement) et D (retentissement) absentes | ⬜ | Voir « Différé » ci-dessous. Le cœur reconstruit provisoirement l'onset précoce (période `childhood`) et le retentissement (champ `impact`). Ces proxys sont signalés dans les narratifs. |

## P1 — cohérence fonctionnelle

| # | Sujet | État | Détail |
|---|---|---|---|
| P1-1 | Sections E/F inversées entre spec et CSV | ✅ | CSV réaligné sur la spec : **E = camouflage**, **F = différentiel**. Test `sections_and_domains_are_consistent`. |
| P1-7 | Camouflage collecté puis ignoré | ✅ | `summariseCamouflage()` produit un résumé ; il ne fait jamais monter la suspicion seul mais nuance une faible visibilité des signes (note d'orientation). Test `reported_camouflage_is_summarised`. |
| P1-8 | « Contradiction » pour une simple divergence de période | ✅ | Concept renommé **divergence temporelle** dans les libellés. Une divergence isolée n'affirme plus rien de fort et ne force plus tout en « indéterminé » ; il faut ≥ 2 divergences pour basculer en indéterminé. |
| P1-12 | Réponses dépendantes de l'ordre d'insertion | ✅ | `Assessment::addResponse()` fait un **upsert** par (code, période) : dernière valeur gagnante. Test `upsert_keeps_the_latest_value_for_a_code_and_period`. |
| P1-9 | Complétude artificiellement élevée | 🟡 | Complétude désormais **par blocs** (social / RRB / développemental / retentissement / différentiel / informateur). « Suffisant » exige social + RRB + développemental + retentissement. Reste limité tant que C/D ne sont pas de vrais items (P0-3). |

## P2 — reproductibilité scientifique (Phase 1, `data-pipeline/`)

| # | Sujet | État | Détail |
|---|---|---|---|
| P2-a | Effectifs des jeux en assertions exécutables | ⬜ | `assert len(children) == 292`, etc., calculés depuis les fichiers téléchargés — ne jamais recopier le tableau 1 (inversé) de l'article. |
| P2-b | Notebook de reproduction figé + registre versions/hash/env | ⬜ | À produire dans `data-pipeline/` avec mention « reproduction non clinique ». |
| P2-c | Checklist **TRIPOD+AI** pour toute publication/validation de modèle | ⬜ | Référence de reporting pour les modèles de prédiction clinique (remplace TRIPOD 2015). |

## Différé — décision d'architecture à valider avant implémentation

Redesign du modèle de recueil vers des collections top-level dédiées, plutôt que de
tout faire passer par `responses[]` :

```
responses[]              items ordinaux A/B (+ camouflage E)
developmental_history[]  vraie trame C : langage, attention conjointe, jeu symbolique,
                         réponse au prénom, régression, âge des premières inquiétudes…
functional_impacts[]     vraie trame D : retentissement par domaine de vie
                         (emploi, autonomie, famille, soins, sommeil…)
differential_conditions[] section F structurée (statut + actuel/historique + traitement
                         + capacité à expliquer certaines réponses + preuves)
informants[]             objet informateur structuré (aujourd'hui : seul respondent_type
                         global + source_type facultatif par réponse)
documents[]              déjà modélisés comme collection top-level dans le schéma
evidence[]               traçabilité
```

Impacts : nouveaux JSON Schemas, migration éventuelle des évaluations déjà stockées,
révision du dénominateur de complétude. À arbitrer avec le comité avant de figer.
