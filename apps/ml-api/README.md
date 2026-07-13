# apps/ml-api

Emplacement réservé au service d'inférence (Python 3.12+, FastAPI, scikit-learn,
imbalanced-learn, MLflow), chapitre 10.1 de la spécification.

**Hors périmètre du MVP.** Le moteur 3 (statistique / ML expérimental) n'est branché
qu'après acquisition de données à **cible indépendante** (Phases 3–4). Tant qu'aucun
modèle validé et versionné n'est disponible, `ml_estimate` reste `null` dans la sortie,
et l'application doit rester pleinement fonctionnelle sans ce service (chap. 10.2).

Contrat d'API cible : voir §10.6 de la spécification.
