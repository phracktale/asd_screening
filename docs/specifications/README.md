# Pack de démarrage — TSA-Repère

Ce dossier contient une spécification de projet pour une application de repérage et d'orientation du trouble du spectre de l'autisme.

## Fichiers

- `SPEC_TSA_REPERAGE_ML.md` : spécification clinique, scientifique, fonctionnelle et technique.
- `schemas/evaluation_form.schema.json` : schéma JSON du formulaire.
- `schemas/assessment_output.schema.json` : schéma JSON de la sortie et de sa traçabilité.
- `data/data_sources.csv` : catalogue initial des jeux de données.
- `data/question_catalog.csv` : catalogue initial des items du formulaire original.

## Limite essentielle

Le formulaire original sert à structurer les informations. Il n'est pas une échelle psychométrique validée. Les questionnaires existants ne doivent être reproduits dans le logiciel qu'après vérification de leurs licences.

## Démarrage recommandé

1. créer le dépôt et copier ce pack dans `/docs/specification` ;
2. ouvrir les décisions listées au chapitre 18 ;
3. créer un registre de licences ;
4. développer d'abord le moteur de formulaire et de synthèse sans modèle clinique ;
5. reproduire le papier dans un espace de recherche séparé ;
6. rechercher un partenaire clinique avant tout entraînement à visée réelle.
