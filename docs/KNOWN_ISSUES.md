# Points ouverts et incohérences relevées

Ce document liste les écarts constatés entre les artefacts de spécification pendant
le démarrage du MVP. Ils méritent une décision de l'administration scientifique
avant d'être figés.

## 1. Sections E / F inversées entre le texte de la spec et le catalogue CSV

- `SPEC_TSA_REPERAGE_ML.md` §6.8 décrit **Section E = Compensation et camouflage**
  et §6.9 **Section F = Hypothèses différentielles et comorbidités**.
- `data/question_catalog.csv` code au contraire **E.\*\* = différentiel** (`domain=differential`)
  et **F.\*\* = camouflage** (`domain=camouflaging`).

Le code métier suit le **CSV** (source de vérité machine). À harmoniser : soit corriger le
texte, soit renuméroter le CSV.

## 2. Échelle de réponse des items différentiels non représentable dans le schéma

- Le catalogue déclare pour les items différentiels l'échelle `known|suspected|no|unknown`.
- `schemas/evaluation_form.schema.json` n'autorise pour `value` que `0..4`, `unknown` ou
  `not_applicable`.

Ces réponses catégorielles ne rentrent pas dans le type `value`. En attendant une décision,
le moteur de structuration lit le statut différentiel depuis le champ texte `example`
(voir `ClinicalStructuringEngine::differentialExploration()`). Proposition : ajouter au schéma
un type de réponse `categorical` avec un vocabulaire dédié aux sections différentielles.

## 3. Sections C, D, G absentes du catalogue

Le schéma autorise les codes `C`, `D`, `G` (histoire développementale, retentissement,
documents) mais `question_catalog.csv` ne les contient pas encore. Le moteur reconstitue
provisoirement :
- l'**onset développemental** à partir des réponses de période `childhood`/`early_development` ;
- le **retentissement fonctionnel** à partir du champ `impact` des réponses.

À compléter par de vrais items C/D/G lorsque la trame sera étendue.

## 4. Incohérence du tableau 1 de l'article (rappel)

La spec (§4.5) signale déjà l'inversion des effectifs enfants/adolescents dans l'article
de Hasan et al. À reporter explicitement dans tout notebook de reproduction (Phase 1).
