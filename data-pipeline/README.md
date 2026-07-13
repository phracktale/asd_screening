# data-pipeline

Pipeline de données et de machine learning **reproductible** (chapitres 8 et 9),
réservé à la **Phase 1 — Reproduction scientifique** puis aux phases cliniques.

**Hors périmètre du MVP.** À développer dans un espace de recherche séparé.

Sous-dossiers prévus :

- `ingestion/` — téléchargement et hash des jeux sources (voir `docs/specifications/data/data_sources.csv`)
- `quality/` — rapports de qualité, déduplication, provenance
- `training/` — pipeline anti-fuite (`imblearn.pipeline`), baselines obligatoires
- `evaluation/` — validation externe, calibration, audit d'équité
- `model-cards/` — model cards versionnées

Rappel critique (chap. 4) : la cible des 4 jeux du papier est **circulaire** (dérivée d'un
seuil appliqué aux mêmes items utilisés en entrée). Toute reproduction doit porter la mention
« reproduction non clinique » et démontrer explicitement cette fuite de cible.
