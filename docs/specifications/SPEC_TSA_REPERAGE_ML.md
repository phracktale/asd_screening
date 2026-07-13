# Spécification exhaustive — Application de repérage du trouble du spectre de l’autisme (TSA)

**Nom de travail :** `TSA-Repère`  
**Version de la spécification :** 1.0  
**Date :** 13 juillet 2026  
**Statut :** spécification de cadrage scientifique, clinique, fonctionnel et technique  
**Destinataires :** équipe produit, développeurs, data scientists, neuropsychologues, psychiatres, médecins, responsables qualité, DPO et partenaires de recherche

---

## Table des matières

- [0. Résumé exécutif](#0-resume-executif)
- [1. Positionnement clinique et terminologie](#1-positionnement-clinique-et-terminologie)
  - [1.1 Finalité](#11-finalite)
  - [1.2 Publics cibles](#12-publics-cibles)
  - [1.3 Utilisations exclues](#13-utilisations-exclues)
  - [1.4 Vocabulaire produit](#14-vocabulaire-produit)
- [2. Sources scientifiques principales](#2-sources-scientifiques-principales)
  - [2.1 Article de départ](#21-article-de-depart)
  - [2.2 Jeux de données directement utilisés dans l’article](#22-jeux-de-donnees-directement-utilises-dans-larticle)
  - [2.3 Autres sources potentielles de données](#23-autres-sources-potentielles-de-donnees)
- [3. Traduction française structurée de l’article de Hasan et al.](#3-traduction-francaise-structuree-de-larticle-de-hasan-et-al)
  - [3.1 Titre](#31-titre)
  - [3.2 Résumé traduit](#32-resume-traduit)
  - [3.3 Introduction traduite](#33-introduction-traduite)
  - [3.4 Description des données traduite](#34-description-des-donnees-traduite)
  - [3.5 Pipeline méthodologique traduit](#35-pipeline-methodologique-traduit)
  - [3.6 Méthodes de transformation traduites](#36-methodes-de-transformation-traduites)
  - [3.7 Méthodes d’importance des variables traduites](#37-methodes-dimportance-des-variables-traduites)
  - [3.8 Classifieurs traduits](#38-classifieurs-traduits)
  - [3.9 Protocole expérimental traduit](#39-protocole-experimental-traduit)
  - [3.10 Résultats traduits](#310-resultats-traduits)
  - [3.11 Importance des variables traduite](#311-importance-des-variables-traduite)
  - [3.12 Discussion traduite](#312-discussion-traduite)
  - [3.13 Conclusion traduite](#313-conclusion-traduite)
- [4. Audit critique scientifique et informatique du papier](#4-audit-critique-scientifique-et-informatique-du-papier)
  - [4.1 Cible circulaire](#41-cible-circulaire)
  - [4.2 Risque de fuite par la variable `result` ou `screening_score`](#42-risque-de-fuite-par-la-variable-result-ou-screeningscore)
  - [4.3 Suréchantillonnage et validation croisée](#43-surechantillonnage-et-validation-croisee)
  - [4.4 Taille et qualité des cohortes](#44-taille-et-qualite-des-cohortes)
  - [4.5 Incohérence du tableau 1](#45-incoherence-du-tableau-1)
  - [4.6 Absence de validation externe](#46-absence-de-validation-externe)
  - [4.7 Absence de calibration clinique](#47-absence-de-calibration-clinique)
  - [4.8 Interprétation abusive de l’importance des variables](#48-interpretation-abusive-de-limportance-des-variables)
  - [4.9 Terminologie clinique inadaptée](#49-terminologie-clinique-inadaptee)
  - [4.10 Code non officiel trouvé en ligne](#410-code-non-officiel-trouve-en-ligne)
- [5. Stratégie produit retenue](#5-strategie-produit-retenue)
  - [5.1 Trois moteurs indépendants](#51-trois-moteurs-independants)
  - [5.2 Modes d’utilisation](#52-modes-dutilisation)
- [6. Formulaire d’évaluation fonctionnelle](#6-formulaire-devaluation-fonctionnelle)
  - [6.1 Principes](#61-principes)
  - [6.2 Écran 1 — Information et consentement](#62-ecran-1-information-et-consentement)
  - [6.3 Écran 2 — Contexte général](#63-ecran-2-contexte-general)
  - [6.4 Section A — Communication et interactions sociales](#64-section-a-communication-et-interactions-sociales)
  - [6.5 Section B — Comportements, intérêts et sensorialité](#65-section-b-comportements-interets-et-sensorialite)
  - [6.6 Section C — Histoire développementale](#66-section-c-histoire-developpementale)
  - [6.7 Section D — Retentissement](#67-section-d-retentissement)
  - [6.8 Section E — Compensation et camouflage](#68-section-e-compensation-et-camouflage)
  - [6.9 Section F — Hypothèses différentielles et comorbidités](#69-section-f-hypotheses-differentielles-et-comorbidites)
  - [6.10 Section G — Documents et informateurs](#610-section-g-documents-et-informateurs)
  - [6.11 Instruments standardisés intégrables](#611-instruments-standardises-integrables)
- [7. Logique d’interprétation](#7-logique-dinterpretation)
  - [7.1 Sorties distinctes](#71-sorties-distinctes)
  - [7.2 Évaluation de la complétude](#72-evaluation-de-la-completude)
  - [7.3 Compatibilité structurée avec les domaines](#73-compatibilite-structuree-avec-les-domaines)
  - [7.4 Niveaux de suspicion](#74-niveaux-de-suspicion)
  - [7.5 Règles de sécurité](#75-regles-de-securite)
- [8. Stratégie de données](#8-strategie-de-donnees)
  - [8.1 Couches de stockage](#81-couches-de-stockage)
  - [8.2 Registre de provenance](#82-registre-de-provenance)
  - [8.3 Taxonomie des cibles](#83-taxonomie-des-cibles)
  - [8.4 Déduplication](#84-deduplication)
  - [8.5 Normalisation sémantique](#85-normalisation-semantique)
  - [8.6 Séparation des attributs](#86-separation-des-attributs)
- [9. Pipeline machine learning](#9-pipeline-machine-learning)
  - [9.1 Objectifs distincts](#91-objectifs-distincts)
  - [9.2 Baselines obligatoires](#92-baselines-obligatoires)
  - [9.3 Pipeline anti-fuite](#93-pipeline-anti-fuite)
  - [9.4 Validation](#94-validation)
  - [9.5 Métriques](#95-metriques)
  - [9.6 Optimisation du seuil](#96-optimisation-du-seuil)
  - [9.7 Calibration et abstention](#97-calibration-et-abstention)
  - [9.8 Explicabilité](#98-explicabilite)
  - [9.9 Équité](#99-equite)
  - [9.10 Model card](#910-model-card)
- [10. Architecture logicielle](#10-architecture-logicielle)
  - [10.1 Choix recommandé](#101-choix-recommande)
  - [10.2 Principe de résilience](#102-principe-de-resilience)
  - [10.3 Arborescence monorepo](#103-arborescence-monorepo)
  - [10.4 Entités principales](#104-entites-principales)
  - [10.5 API principale](#105-api-principale)
  - [10.6 Contrat ML](#106-contrat-ml)
- [11. Génération du rapport](#11-generation-du-rapport)
  - [11.1 Rapport grand public](#111-rapport-grand-public)
  - [11.2 Rapport professionnel](#112-rapport-professionnel)
  - [11.3 Exigence de traçabilité](#113-exigence-de-tracabilite)
- [12. Sécurité, RGPD et données de santé](#12-securite-rgpd-et-donnees-de-sante)
  - [12.1 Qualification des données](#121-qualification-des-donnees)
  - [12.2 Principes](#122-principes)
  - [12.3 Hébergement](#123-hebergement)
  - [12.4 Mesures techniques](#124-mesures-techniques)
  - [12.5 LLM et documents](#125-llm-et-documents)
- [13. Réglementation produit](#13-reglementation-produit)
  - [13.1 Phase d’information et de repérage](#131-phase-dinformation-et-de-reperage)
  - [13.2 Aide à la décision clinique](#132-aide-a-la-decision-clinique)
  - [13.3 Préparation à une éventuelle qualification de dispositif médical](#133-preparation-a-une-eventuelle-qualification-de-dispositif-medical)
  - [13.4 Intelligence artificielle](#134-intelligence-artificielle)
- [14. Tests et assurance qualité](#14-tests-et-assurance-qualite)
  - [14.1 Tests fonctionnels](#141-tests-fonctionnels)
  - [14.2 Tests scientifiques](#142-tests-scientifiques)
  - [14.3 Tests de sécurité](#143-tests-de-securite)
  - [14.4 Accessibilité](#144-accessibilite)
- [15. Critères d’acceptation du MVP](#15-criteres-dacceptation-du-mvp)
- [16. Plan de réalisation](#16-plan-de-realisation)
  - [Phase 0 — Gouvernance](#phase-0-gouvernance)
  - [Phase 1 — Reproduction scientifique](#phase-1-reproduction-scientifique)
  - [Phase 2 — MVP sans modèle clinique](#phase-2-mvp-sans-modele-clinique)
  - [Phase 3 — Données cliniques](#phase-3-donnees-cliniques)
  - [Phase 4 — Modèle expérimental](#phase-4-modele-experimental)
  - [Phase 5 — Pilote clinique](#phase-5-pilote-clinique)
- [17. Risques projet](#17-risques-projet)
- [18. Décisions d’architecture à prendre à l’ouverture du projet](#18-decisions-darchitecture-a-prendre-a-louverture-du-projet)
- [19. Livrables techniques attendus](#19-livrables-techniques-attendus)
- [20. Références et liens](#20-references-et-liens)
  - [Article principal](#article-principal)
  - [Données](#donnees)
  - [Instruments](#instruments)
  - [Recommandations et réglementation à intégrer au dossier qualité](#recommandations-et-reglementation-a-integrer-au-dossier-qualite)
- [21. Conclusion de cadrage](#21-conclusion-de-cadrage)


## 0. Résumé exécutif

L’objectif est de construire une application de **repérage et d’orientation du TSA**, et non un outil autonome de diagnostic. L’application doit :

1. recueillir de manière structurée les signes actuels et développementaux compatibles avec un TSA ;
2. calculer, lorsque les droits d’utilisation le permettent, les scores de questionnaires validés ;
3. produire une synthèse explicable destinée à la personne évaluée et, avec son accord, à un professionnel ;
4. estimer un niveau de suspicion et d’incertitude ;
5. détecter les informations manquantes et les principales hypothèses différentielles ;
6. soutenir la recherche par un pipeline de données et de machine learning reproductible ;
7. refuser de conclure lorsque les données sont insuffisantes ou contradictoires.

Le papier de Hasan et al. (IEEE Access, 2023) constitue un point de départ technique utile, mais ses performances annoncées ne doivent pas être transposées à un usage clinique. Les classes de ses quatre jeux de données sont principalement dérivées d’un seuil appliqué aux mêmes dix réponses AQ-10 ou Q-CHAT-10 utilisées comme variables prédictives. Un modèle peut donc apprendre à reconstituer le seuil du questionnaire plutôt qu’à prédire un diagnostic clinique indépendant. Cette circularité explique vraisemblablement une partie importante des exactitudes de 97 à 99 %.

La stratégie retenue est donc à trois étages :

- **moteur psychométrique transparent**, qui calcule les scores officiels sans machine learning ;
- **moteur de structuration clinique**, qui organise les éléments compatibles avec les critères diagnostiques, le retentissement, l’histoire développementale et les diagnostics différentiels ;
- **moteur statistique expérimental**, limité aux données dont la cible est indépendante des variables prédictives et capable de s’abstenir.

L’application ne doit jamais afficher « vous êtes autiste » ou « vous n’êtes pas autiste ». La formulation attendue est par exemple :

> « Les réponses recueillies montrent un niveau de suspicion élevé et justifient une évaluation spécialisée. Ce résultat n’est pas un diagnostic. »

ou :

> « Les données sont insuffisantes ou contradictoires pour estimer correctement le niveau de suspicion. »

---

## 1. Positionnement clinique et terminologie

### 1.1 Finalité

L’application a pour finalité le **repérage**, la **préparation d’un entretien clinique** et l’**orientation**. Elle vise à réduire la perte d’informations, faciliter le recueil d’éléments développementaux et produire une synthèse structurée. Elle ne remplace ni l’examen clinique, ni l’entretien développemental, ni l’observation directe, ni l’évaluation des diagnostics différentiels.

### 1.2 Publics cibles

- adulte s’interrogeant sur un éventuel TSA ;
- parent ou proche répondant pour un enfant ou un adolescent ;
- professionnel de santé ou psychologue préparant une évaluation ;
- chercheur utilisant un module distinct, pseudonymisé et soumis à gouvernance ;
- administrateur scientifique gérant les versions des questionnaires, règles et modèles.

### 1.3 Utilisations exclues

- diagnostic automatisé ;
- décision d’accès à un soin, une prestation, une assurance, un emploi ou une scolarité ;
- triage d’urgence psychiatrique ;
- évaluation médico-légale ;
- dépistage d’une population sans information et consentement appropriés ;
- utilisation d’images faciales, de la voix ou de traces sociales pour inférer un TSA sans protocole de recherche spécifique ;
- entraînement sur les réponses des utilisateurs sans consentement de recherche séparé.

### 1.4 Vocabulaire produit

| Terme | Définition dans l’application |
|---|---|
| Repérage | Identification d’éléments justifiant éventuellement une évaluation spécialisée. |
| Dépistage | Application d’un outil standardisé dans une population définie. Le terme est réservé aux instruments et protocoles dont la validation et les conditions d’utilisation sont connues. |
| Diagnostic | Conclusion clinique posée par un professionnel compétent après une évaluation complète. L’application ne pose pas de diagnostic. |
| Compatibilité avec les critères | Correspondance structurée entre des éléments déclarés et des domaines diagnostiques. Ce n’est pas une validation de critère par un clinicien. |
| Niveau de suspicion | Catégorie de sortie : faible, intermédiaire, élevé ou indéterminé. |
| Confiance | Qualité et complétude des preuves disponibles, distincte de la probabilité statistique. |
| Score psychométrique | Score calculé conformément aux règles officielles d’un instrument autorisé. |
| Probabilité ML | Estimation produite par un modèle calibré ; elle doit toujours être accompagnée de son domaine de validité. |

---

## 2. Sources scientifiques principales

### 2.1 Article de départ

Hasan, S. M. Mahedy, et al. **A Machine Learning Framework for Early-Stage Detection of Autism Spectrum Disorders.** IEEE Access, vol. 11, 2023, p. 15038-15057. DOI : `10.1109/ACCESS.2022.3232490`.

Le document est publié sous licence **Creative Commons Attribution 4.0**, ce qui autorise sa traduction et son adaptation sous réserve d’attribution.

URL DOI : https://doi.org/10.1109/ACCESS.2022.3232490

### 2.2 Jeux de données directement utilisés dans l’article

| Cohorte | Source officielle | Identifiant / DOI | Taille annoncée | Licence |
|---|---|---|---:|---|
| Tout-petits | Kaggle — Autism screening data for toddlers | https://www.kaggle.com/datasets/fabdelja/autism-screening-for-toddlers | 1 054 | vérifier les conditions Kaggle et la provenance du fichier ; ne pas supposer que la licence de la page couvre automatiquement tous les usages |
| Enfants | UCI — Autistic Spectrum Disorder Screening Data for Children | https://archive.ics.uci.edu/dataset/419/autistic+spectrum+disorder+screening+data+for+children | 292 | CC BY 4.0 |
| Adolescents | UCI — Autistic Spectrum Disorder Screening Data for Adolescent | https://archive.ics.uci.edu/dataset/420/autistic+spectrum+disorder+screening+data+for+adolescent | 104 | CC BY 4.0 |
| Adultes | UCI — Autism Screening Adult | https://archive.ics.uci.edu/dataset/426/autism+screening+adult | 704 | CC BY 4.0 |

DOI UCI :

- adultes : https://doi.org/10.24432/C5F019
- enfants : https://doi.org/10.24432/C5659W
- adolescents : https://doi.org/10.24432/C5V89T

### 2.3 Autres sources potentielles de données

Ces sources ne doivent pas être fusionnées automatiquement avec les données UCI. Elles servent à préparer une phase de recherche clinique mieux fondée.

| Source | Intérêt | Accès | Usage recommandé |
|---|---|---|---|
| SPARK / SFARI Base | cohortes très larges, données phénotypiques, médicales et génétiques, diagnostics déclarés ou documentés selon les collections | accès contrôlé aux chercheurs | développement et validation de variables développementales, sous protocole et accord institutionnel |
| NIMH Data Archive / ancien NDAR | nombreuses études, instruments cliniques, phénotypes, imagerie et données longitudinales | accès contrôlé | création de cohortes avec diagnostic mieux documenté et groupes cliniques comparateurs |
| ABIDE I et II | imagerie et données phénotypiques multi-sites | accès de recherche | hors MVP questionnaire ; utile uniquement pour un programme séparé d’imagerie, pas pour le formulaire principal |
| Healthy Brain Network | données transdiagnostiques pédiatriques, cognition, santé mentale, parfois imagerie | conditions de recherche | groupes différentiels TDAH, anxiété, troubles du langage et autres TND |
| cohorte prospective française | données linguistiques et cliniques adaptées au contexte français | à créer | indispensable avant toute revendication de performance clinique en France |

URLs de repérage :

- SFARI Base : https://base.sfari.org/
- SPARK : https://sparkforautism.org/
- NIMH Data Archive : https://nda.nih.gov/
- ABIDE : http://fcon_1000.projects.nitrc.org/indi/abide/
- Healthy Brain Network : https://fcon_1000.projects.nitrc.org/indi/cmi_healthy_brain_network/

---

## 3. Traduction française structurée de l’article de Hasan et al.

> Cette annexe constitue une traduction fidèle du contenu scientifique de l’article : résumé, introduction, méthode, algorithmes, protocole expérimental, résultats, discussion et conclusion. Les références bibliographiques et biographies d’auteurs ne sont pas traduites ligne à ligne. Les équations conservent leur notation originale. Les tableaux numériques sont synthétisés lorsqu’une reproduction intégrale n’apporte pas d’information supplémentaire au développement de l’application.

### 3.1 Titre

**Un framework d’apprentissage automatique pour la détection précoce des troubles du spectre de l’autisme**

### 3.2 Résumé traduit

Le trouble du spectre de l’autisme est un trouble neurodéveloppemental qui affecte la vie quotidienne des personnes concernées. Même s’il ne peut être « éliminé », des interventions précoces peuvent réduire certaines difficultés et soutenir le développement. Les auteurs proposent un framework destiné à évaluer plusieurs techniques d’apprentissage automatique pour le repérage précoce du TSA.

Quatre stratégies de mise à l’échelle des variables sont comparées : le transformateur quantile, le transformateur de puissance, la normalisation par ligne et le MaxAbsScaler. Les jeux ainsi transformés sont classés par huit algorithmes : AdaBoost, forêt aléatoire, arbre de décision, k plus proches voisins, Naive Bayes gaussien, régression logistique, machine à vecteurs de support et analyse discriminante linéaire.

Les expériences portent sur quatre jeux standards correspondant aux tout-petits, enfants, adolescents et adultes. Les auteurs comparent l’exactitude, la courbe ROC, le score F1, la précision, le rappel, le coefficient de corrélation de Matthews, le kappa et la log-loss. Ils rapportent les meilleures exactitudes suivantes : 99,25 % pour les tout-petits avec AdaBoost, 97,95 % pour les enfants avec AdaBoost, 97,12 % pour les adolescents avec l’analyse discriminante linéaire et 99,03 % pour les adultes avec cette même méthode.

Les meilleurs résultats auraient été obtenus après normalisation pour les tout-petits et les enfants, et après transformation quantile pour les adolescents et adultes. Quatre méthodes de sélection ou d’évaluation des caractéristiques sont ensuite utilisées pour classer les variables : gain d’information, ratio de gain, ReliefF et corrélation.

Les auteurs concluent que l’ajustement des méthodes d’apprentissage automatique peut contribuer au repérage du TSA et que l’analyse de l’importance des variables peut soutenir les professionnels lors du dépistage.

### 3.3 Introduction traduite

Le TSA apparaît au cours du développement précoce et affecte la communication sociale, les interactions et les comportements. Le terme « spectre » traduit une grande diversité d’expressions et d’intensités. Les auteurs rappellent que l’identification clinique peut être complexe et que l’âge du diagnostic varie selon la visibilité des signes.

Ils présentent les questionnaires courts et les applications mobiles comme une voie permettant d’accélérer le repérage. L’application ASDTests, fondée sur Q-CHAT et AQ-10, a servi à constituer des jeux de données ouverts publiés sur UCI et Kaggle. Plusieurs travaux antérieurs ont appliqué des méthodes de règles, de forêts aléatoires, d’arbres, de régression logistique, de SVM, de réseaux neuronaux et de sélection de caractéristiques à des données de TSA.

L’étude rassemble quatre jeux de données, procède au traitement des valeurs manquantes et à l’encodage, compare quatre transformations de variables, huit classifieurs et quatre méthodes d’importance des caractéristiques.

Les contributions revendiquées sont :

- un framework commun à plusieurs tranches d’âge ;
- le traitement du déséquilibre de classes par suréchantillonnage aléatoire ;
- la comparaison de quatre transformations de variables ;
- la comparaison de huit classifieurs ;
- une analyse de l’importance des variables ;
- une comparaison expérimentale sur quatre jeux de données.

### 3.4 Description des données traduite

Les quatre jeux sont issus de Kaggle et d’UCI. Ils reposent sur des réponses recueillies via ASDTests et sur Q-CHAT-10 ou AQ-10. Un score total de 0 à 10 est calculé ; dans les données, un seuil de 6 est utilisé pour définir une classe positive.

Le tableau 1 publié dans l’article indique :

| Jeu | Nombre de sujets | Classe positive publiée | Classe négative publiée | Hommes | Femmes |
|---|---:|---:|---:|---:|---:|
| Tout-petits | 1 054 | 728 | 326 | 735 | 319 |
| Enfants | 292 | 76 | 28 | 208 | 84 |
| Adolescents | 104 | 185 | 107 | 49 | 49 |
| Adultes | 704 | 508 | 196 | 367 | 337 |

**Erreur éditoriale importante :** les nombres de classes des lignes « enfants » et « adolescents » sont manifestement inversés, puisque 76 + 28 = 104 et 185 + 107 = 292. Cette incohérence doit être explicitement signalée dans toute reproduction.

Les variables décrites sont : âge, genre, ethnicité, antécédent de jaunisse à la naissance, membre de la famille avec trouble envahissant du développement ou autisme, personne ayant rempli le questionnaire, pays de résidence, utilisation antérieure de l’application, type de méthode de dépistage, réponses A1 à A10, score de dépistage et classe finale.

### 3.5 Pipeline méthodologique traduit

Le pipeline proposé suit les étapes suivantes :

1. collecte des quatre jeux de données ;
2. imputation des valeurs manquantes par la moyenne ;
3. encodage one-hot des variables catégorielles ;
4. suréchantillonnage aléatoire de la classe minoritaire ;
5. application d’une méthode de transformation des variables ;
6. entraînement et comparaison de huit classifieurs ;
7. analyse des performances ;
8. sélection du meilleur couple transformation / classifieur ;
9. calcul de l’importance des variables par quatre méthodes.

### 3.6 Méthodes de transformation traduites

- **Transformateur quantile :** transforme la distribution d’une variable pour la rapprocher d’une distribution cible, généralement uniforme ou normale.
- **Transformateur de puissance :** réduit l’asymétrie et rapproche la distribution d’une forme gaussienne, avec une transformation de type Box-Cox ou Yeo-Johnson.
- **Normalizer :** normalise chaque observation, et non chaque colonne, selon une norme définie.
- **MaxAbsScaler :** divise chaque valeur par la valeur absolue maximale de la colonne.

### 3.7 Méthodes d’importance des variables traduites

- **Gain d’information :** mesure la réduction d’entropie produite par la connaissance d’une variable.
- **Ratio de gain :** corrige le gain d’information par l’entropie de la variable.
- **ReliefF :** estime la pertinence d’une variable en comparant des observations voisines appartenant ou non à la même classe.
- **Corrélation :** évalue l’association linéaire entre une variable et la classe.

### 3.8 Classifieurs traduits

#### AdaBoost

AdaBoost combine plusieurs classifieurs faibles. À chaque itération, les observations mal classées reçoivent davantage de poids. La prédiction finale est une combinaison pondérée des prédictions individuelles.

#### Forêt aléatoire

Une forêt aléatoire construit plusieurs arbres sur des échantillons et sous-ensembles de variables aléatoires. La classe finale résulte généralement d’un vote majoritaire.

#### Arbre de décision

L’arbre divise récursivement les données selon les variables maximisant un critère tel que le gain d’information. Les feuilles correspondent à des décisions de classe.

#### K plus proches voisins

KNN affecte à une observation la classe majoritaire parmi ses voisins les plus proches selon une distance, ici euclidienne.

#### Naive Bayes gaussien

Cette méthode estime, pour chaque classe, une distribution gaussienne des variables numériques et applique la règle de Bayes sous une hypothèse d’indépendance conditionnelle.

#### Régression logistique

La régression logistique estime la probabilité d’appartenance à une classe à partir d’une combinaison linéaire des variables transformée par une fonction logistique.

#### Machine à vecteurs de support

La SVM recherche une frontière maximisant la marge entre classes. L’article utilise un noyau radial RBF pour traiter des séparations non linéaires.

#### Analyse discriminante linéaire

La LDA suppose des distributions gaussiennes par classe avec covariance commune et recherche une combinaison linéaire séparant les classes.

### 3.9 Protocole expérimental traduit

Les auteurs utilisent Google Colab, Python et scikit-learn. Ils appliquent une validation croisée en dix plis. Neuf plis servent à l’entraînement et un pli au test, puis l’opération est répétée dix fois.

Les métriques sont :

- exactitude ;
- précision positive ;
- rappel ou sensibilité ;
- score F1 ;
- aire sous la courbe ROC ;
- coefficient de corrélation de Matthews ;
- kappa ;
- log-loss.

### 3.10 Résultats traduits

#### Exactitude

- tout-petits : meilleur résultat annoncé 99,25 % avec AdaBoost ;
- enfants : 97,95 % avec AdaBoost ;
- adolescents : 97,12 % avec LDA ;
- adultes : 99,03 % avec LDA.

#### Précision positive

- tout-petits : 99,95 % avec AdaBoost après transformateur de puissance ;
- enfants : 96,16 % avec régression logistique et MaxAbsScaler ;
- adolescents : 97,25 % avec arbre de décision après transformateur de puissance ;
- adultes : 98,16 % avec SVM après transformation quantile.

#### Rappel

- tout-petits : 98,45 % avec AdaBoost après normalisation ;
- enfants : 97,72 % avec régression logistique après normalisation ;
- adolescents : 97,36 % avec AdaBoost après normalisation ;
- adultes : plusieurs configurations atteignent 100 % dans les tableaux publiés.

#### ROC

- tout-petits : jusqu’à 99,99 % avec régression logistique ou AdaBoost selon la transformation ;
- enfants : 99,73 % avec Naive Bayes gaussien ;
- adolescents : 99,72 % avec AdaBoost ou LDA ;
- adultes : 99,99 % avec LDA.

#### Score F1

- tout-petits : 99,14 % avec AdaBoost ;
- enfants : 97,02 % avec AdaBoost ;
- adolescents : 97,69 % avec AdaBoost ;
- adultes : 99,11 % avec LDA.

#### Kappa, MCC et log-loss

Les auteurs rapportent des valeurs très élevées de kappa et de MCC, jusqu’à environ 99 %, ainsi que des log-loss très faibles. Ces résultats sont cohérents avec une cible très directement liée aux dix réponses utilisées en entrée, mais ne démontrent pas une performance diagnostique externe.

### 3.11 Importance des variables traduite

Dans les quatre cohortes, les réponses A1 à A10 dominent largement les variables démographiques. Selon les méthodes :

- chez les tout-petits, A5, A6, A7 et A9 ressortent fréquemment ;
- chez les enfants, A4, A8, A9 et A10 sont souvent importantes ;
- chez les adolescents, A3, A4, A5 et A10, ainsi que le pays de résidence dans certaines méthodes, prennent une place importante ;
- chez les adultes, A5, A6 et A9 dominent plusieurs classements.

L’importance du pays ou de l’ethnicité peut refléter des biais de recrutement, de langue ou de codage et ne doit pas être interprétée comme un facteur causal.

### 3.12 Discussion traduite

Les auteurs concluent que les meilleurs classifieurs varient selon l’âge : AdaBoost pour les tout-petits et enfants, LDA pour adolescents et adultes. Ils estiment que la normalisation convient mieux aux deux premières cohortes et la transformation quantile aux deux autres.

Ils proposent d’utiliser les classements de variables pour aider les professionnels à identifier les caractéristiques les plus importantes pendant le dépistage.

### 3.13 Conclusion traduite

L’étude propose un framework d’apprentissage automatique pour quatre tranches d’âge et compare plusieurs transformations, classifieurs et méthodes d’importance. Les auteurs considèrent ces modèles comme des outils potentiellement utiles aux professionnels.

Ils reconnaissent toutefois une limitation centrale : la quantité de données est insuffisante pour construire un modèle généralisable à toutes les étapes de la vie. Ils proposent de collecter davantage de données afin d’améliorer la généralisation au TSA et à d’autres troubles neurodéveloppementaux.

---

## 4. Audit critique scientifique et informatique du papier

### 4.1 Cible circulaire

La faiblesse principale est la définition de la classe. Le score de dépistage est calculé à partir des dix réponses A1 à A10, et un seuil de 6 sert à produire la classe positive. Or les mêmes dix réponses sont utilisées comme variables prédictives. Le modèle apprend donc une fonction proche de :

```text
classe_positive = somme(A1...A10) >= 6
```

Cette tâche est presque déterministe. Une exactitude proche de 100 % n’indique pas une capacité à identifier un diagnostic clinique indépendant.

### 4.2 Risque de fuite par la variable `result` ou `screening_score`

Certains fichiers adultes contiennent un champ `result` ou score total. Si ce champ est inclus dans les variables, la fuite de cible devient explicite. Le pipeline doit supprimer systématiquement :

- score total dérivé des items ;
- classe source ;
- tout champ calculé après le questionnaire ;
- toute variable révélant directement le diagnostic ou le résultat du test.

### 4.3 Suréchantillonnage et validation croisée

L’article ne démontre pas clairement que le suréchantillonnage est effectué **à l’intérieur de chaque pli d’entraînement**. S’il est appliqué avant la séparation en plis, des duplications d’une même observation peuvent se retrouver dans l’entraînement et le test, ce qui gonfle artificiellement les performances.

La reproduction correcte doit utiliser un pipeline `imbalanced-learn` dans lequel l’imputation, l’encodage, la transformation et le suréchantillonnage sont ajustés uniquement sur le pli d’entraînement.

### 4.4 Taille et qualité des cohortes

- 104 adolescents est un effectif faible pour comparer de nombreuses combinaisons de modèles et de transformations ;
- les groupes ne sont pas des cohortes cliniques représentatives ;
- les données proviennent d’une application de questionnaire et peuvent contenir autosélection, erreurs de saisie et doublons ;
- les groupes de contrôle ne représentent pas nécessairement les diagnostics différentiels rencontrés en pratique ;
- les distributions géographiques et culturelles sont hétérogènes ;
- les variables `ethnicity` et `country` comportent des orthographes et catégories instables.

### 4.5 Incohérence du tableau 1

Les effectifs positifs et négatifs des enfants et adolescents sont inversés dans le tableau de l’article. La somme des classes « enfants » donne 104, et celle des « adolescents » donne 292.

### 4.6 Absence de validation externe

La validation croisée interne ne suffit pas. Il faut tester le modèle sur :

- une cohorte provenant d’un autre site ;
- une autre période ;
- une autre langue ;
- une population clinique comprenant TDAH, anxiété, TOC, traumatisme, troubles du langage et déficience intellectuelle ;
- une cohorte française si l’application vise la France.

### 4.7 Absence de calibration clinique

Un modèle peut avoir une bonne AUC tout en produisant des probabilités mal calibrées. Le projet doit mesurer le Brier score, l’erreur de calibration, les courbes de calibration et la valeur clinique des seuils.

### 4.8 Interprétation abusive de l’importance des variables

Une mesure d’importance n’établit ni causalité, ni validité clinique. Les variables de pays, ethnicité ou utilisateur de l’application peuvent capter des artefacts. Leur utilisation en production doit être évitée, sauf analyse de biais.

### 4.9 Terminologie clinique inadaptée

L’article emploie parfois un vocabulaire de « maladie à éradiquer » ou de « sévérité à réduire ». L’application doit adopter une terminologie neurodéveloppementale, descriptive et non stigmatisante. Elle peut parler de besoins de soutien, de difficultés fonctionnelles et de qualité de vie.

### 4.10 Code non officiel trouvé en ligne

Un dépôt GitHub public porte le même titre, mais il n’est pas attribué aux auteurs de l’article et a été publié plus tard. Il contient une application Flask et des notebooks. Il ne doit pas être intégré directement :

- présence d’identifiants SMTP en clair dans un commit ;
- authentification artisanale ;
- modèles sérialisés sans provenance ;
- absence de documentation scientifique et de tests ;
- sorties formulées comme un diagnostic ;
- sécurité incompatible avec des données de santé.

Le dépôt peut servir uniquement à comparer des fichiers ou repérer des noms de modèles, jamais comme base de production.

---

## 5. Stratégie produit retenue

### 5.1 Trois moteurs indépendants

#### Moteur 1 — Psychométrie

- charge la définition versionnée d’un instrument ;
- affiche les items uniquement si la licence le permet ;
- applique les règles officielles de cotation ;
- conserve la version, la langue et la provenance ;
- affiche le score et l’interprétation officielle ;
- ne transforme jamais un seuil de questionnaire en diagnostic.

#### Moteur 2 — Structuration clinique

- organise les observations selon communication sociale, comportements répétitifs, sensorialité, développement précoce et retentissement ;
- distingue l’âge actuel et l’enfance ;
- prend en compte un informateur externe ;
- repère les contradictions et données manquantes ;
- génère une synthèse narrative et un tableau de preuves ;
- ne valide pas automatiquement un critère diagnostique.

#### Moteur 3 — Statistique expérimentale

- est désactivable ;
- utilise uniquement des modèles validés et versionnés ;
- affiche le domaine de validité ;
- produit une probabilité calibrée et un niveau d’incertitude ;
- peut répondre « indéterminé » ;
- ne reçoit pas les variables protégées pour la prédiction, sauf justification scientifique ;
- utilise les variables protégées uniquement pour auditer l’équité.

### 5.2 Modes d’utilisation

| Mode | Utilisateur | Données stockées | Sortie |
|---|---|---|---|
| Découverte anonyme | grand public | aucune donnée nominative ; session locale ou temporaire | score et recommandations générales |
| Dossier personnel | personne connectée | questionnaire, documents, synthèses | historique et export PDF |
| Professionnel | clinicien autorisé | dossier partagé avec consentement | synthèse détaillée, commentaires et validation humaine |
| Recherche | équipe autorisée | données pseudonymisées et consentement séparé | export contrôlé, cohortes, analyses |
| Administration scientifique | comité | règles, instruments, modèles, versions | publication et retrait de versions |

---

## 6. Formulaire d’évaluation fonctionnelle

### 6.1 Principes

Le formulaire principal est une **trame originale de recueil clinique**, pas un questionnaire validé. Il ne doit pas être présenté comme une échelle psychométrique. Les instruments validés sont intégrés comme modules distincts, selon leurs licences.

Chaque réponse doit pouvoir être renseignée pour deux périodes :

- **actuellement / six à douze derniers mois** ;
- **enfance / période développementale précoce** lorsque pertinent.

Échelle recommandée :

- 0 — jamais ou pas du tout ;
- 1 — rarement / léger ;
- 2 — parfois / modéré ;
- 3 — souvent / marqué ;
- 4 — très souvent / très marqué ;
- U — inconnu ;
- NA — non applicable.

Chaque item peut être complété par : exemple concret, âge d’apparition, contextes, fréquence, retentissement, source de l’information et degré de confiance.

### 6.2 Écran 1 — Information et consentement

Champs obligatoires :

- finalité de l’outil ;
- confirmation que le résultat n’est pas un diagnostic ;
- âge de la personne évaluée ;
- identité du répondant : soi-même, parent, conjoint, proche, professionnel ;
- consentement au traitement des réponses ;
- consentement séparé et facultatif à la recherche ;
- choix de stockage : anonyme sans sauvegarde, compte personnel, dossier professionnel.

### 6.3 Écran 2 — Contexte général

- âge ou date de naissance ;
- genre déclaré ;
- sexe assigné à la naissance, facultatif et uniquement si utile à l’audit ;
- langue principale ;
- pays de développement et de scolarisation ;
- niveau de communication orale ;
- présence d’une déficience intellectuelle connue ou suspectée ;
- diagnostic TDAH ou autres TND ;
- diagnostics psychiatriques et neurologiques connus ;
- raison de la demande ;
- urgence ou risque actuel : idées suicidaires, danger, rupture aiguë — ces éléments déclenchent une information d’urgence et interrompent l’interprétation standard.

### 6.4 Section A — Communication et interactions sociales

#### A1. Réciprocité socio-émotionnelle

1. Difficulté à initier un échange sans objectif concret.
2. Difficulté à maintenir un échange réciproque plutôt qu’un monologue ou un retrait.
3. Difficulté à partager spontanément intérêts, plaisir ou émotions.
4. Réponses sociales perçues comme trop faibles, trop intenses ou décalées.
5. Tendance à trop se confier ou, au contraire, à ne presque rien partager.
6. Fatigue ou saturation rapide pendant les interactions.

#### A2. Communication non verbale

1. Contact visuel inconfortable, évité, forcé ou difficile à doser.
2. Difficulté à interpréter expressions faciales, posture, gestes ou ton de voix.
3. Décalage entre contenu verbal et expression non verbale.
4. Difficulté à utiliser spontanément les gestes pour soutenir la communication.
5. Expression faciale ou prosodie jugée inhabituelle par l’entourage.
6. Besoin de contrôler consciemment le regard, la posture ou les expressions.

#### A3. Relations

1. Difficulté à se faire des amis ou à comprendre comment une relation se construit.
2. Difficulté à entretenir les liens sans raison pratique.
3. Difficulté à adapter le comportement à différents contextes sociaux.
4. Compréhension tardive des faux pas ou attentes implicites.
5. Difficulté avec les conversations informelles, rites sociaux ou politesses.
6. Préférence marquée pour des relations structurées, prévisibles ou centrées sur un intérêt.

### 6.5 Section B — Comportements, intérêts et sensorialité

#### B1. Répétitions motrices, verbales ou utilisation répétitive d’objets

1. Gestes répétitifs, balancements, tapotements, manipulation constante d’objets.
2. Répétition de mots, phrases, sons, dialogues ou mélodies.
3. Jeux ou scénarios répétitifs et peu variables.
4. Besoin d’aligner, classer ou disposer certains objets de manière précise.
5. Comportements répétitifs servant à se calmer ou se concentrer.

#### B2. Besoin de stabilité et difficulté avec le changement

1. Détresse face à un changement inattendu.
2. Besoin d’anticiper précisément les événements.
3. Routines ou procédures personnelles difficiles à modifier.
4. Pensée rigide ou difficulté à envisager plusieurs solutions.
5. Réactions intenses lorsque l’ordre, le rangement ou la séquence attendue est perturbé.

#### B3. Intérêts spécifiques

1. Intérêts très intenses ou absorbants.
2. Accumulation exhaustive d’informations sur certains sujets.
3. Difficulté à interrompre une activité liée à un intérêt.
4. Tendance à ramener les échanges vers un domaine d’intérêt.
5. Investissement disproportionné par rapport aux autres activités ou obligations.

#### B4. Particularités sensorielles

1. Hypersensibilité aux sons.
2. Hypersensibilité à la lumière ou à certains motifs visuels.
3. Hypersensibilité aux textures, vêtements, contacts ou températures.
4. Sensibilité aux odeurs ou goûts.
5. Recherche de pression, mouvement, stimulation vestibulaire ou tactile.
6. Réaction faible à la douleur, au froid, à la faim ou à d’autres signaux corporels.
7. Surcharge dans les environnements riches en stimuli.

### 6.6 Section C — Histoire développementale

1. Premières inquiétudes et âge approximatif.
2. Développement du langage.
3. Pointage, gestes sociaux et attention conjointe.
4. Réponse au prénom.
5. Jeux symboliques et imitation.
6. Relations avec les pairs.
7. Particularités sensorielles précoces.
8. Routines et réactions aux transitions.
9. Intérêts précoces inhabituels par leur intensité.
10. Régression de langage ou compétences sociales.
11. Observations dans les bulletins scolaires ou dossiers médicaux.
12. Présence d’un parent ou proche pouvant apporter une hétéro-anamnèse.

Chaque élément doit accepter : oui, non, incertain, âge, exemple et source documentaire.

### 6.7 Section D — Retentissement

Évaluer séparément :

- études et apprentissages ;
- emploi et organisation du travail ;
- autonomie quotidienne ;
- couple et famille ;
- amitiés ;
- santé et accès aux soins ;
- gestion administrative ;
- alimentation ;
- sommeil ;
- mobilité et transports ;
- loisirs ;
- épuisement, shutdown, meltdown ou récupération après sollicitation.

Pour chaque domaine : absence, léger, modéré, important, majeur, inconnu ; exemples et aménagements déjà utilisés.

### 6.8 Section E — Compensation et camouflage

Applicable surtout aux adolescents et adultes :

1. Préparation mentale de scripts avant une interaction.
2. Imitation volontaire des comportements sociaux d’autrui.
3. Forçage du contact visuel ou des expressions.
4. Analyse consciente de règles sociales que d’autres semblent appliquer intuitivement.
5. Écart important entre fonctionnement public et épuisement privé.
6. Perte temporaire de capacités après une journée sociale.
7. Diagnostic ou reconnaissance tardive malgré des difficultés anciennes.
8. Impression de jouer un rôle social.

Cette section ne doit pas produire seule une hausse automatique du score ; elle sert à interpréter l’absence apparente de signes observables.

### 6.9 Section F — Hypothèses différentielles et comorbidités

Le formulaire ne diagnostique pas ces troubles. Il repère les domaines nécessitant une exploration :

- TDAH ;
- anxiété sociale ;
- trouble anxieux généralisé ;
- dépression ;
- trouble obsessionnel-compulsif ;
- psychotraumatisme ;
- trouble de la personnalité ;
- psychose ou trouble bipolaire ;
- trouble développemental du langage ;
- déficience intellectuelle ;
- troubles spécifiques des apprentissages ;
- trouble de la coordination ;
- déficience auditive ou visuelle ;
- épilepsie ou affection neurologique ;
- trouble du sommeil ;
- douleur chronique, fatigue ou affection médicale altérant les interactions ;
- consommation de substances ;
- effets médicamenteux.

Pour chaque domaine : diagnostic connu, suspicion, non, inconnu, traitement actuel et impact possible sur les réponses.

### 6.10 Section G — Documents et informateurs

- bulletins scolaires ;
- carnet de santé ;
- bilans orthophoniques, psychomoteurs, neuropsychologiques ;
- compte rendu psychiatrique ou pédiatrique ;
- témoignage d’un parent ;
- témoignage du conjoint ;
- observation d’un enseignant ou collègue ;
- score d’un instrument validé effectué ailleurs.

Les documents ne doivent pas être analysés automatiquement sans consentement explicite. Toute extraction par IA doit conserver le passage source et permettre une validation humaine.

### 6.11 Instruments standardisés intégrables

#### AQ-10 adulte

Le formulaire officiel est protégé par copyright. L’application peut :

- intégrer le questionnaire uniquement après vérification des droits ;
- sinon proposer un lien vers la source officielle ;
- permettre au professionnel de saisir le score obtenu ailleurs ;
- conserver le nom, la version, la langue, la date et le répondant.

Source officielle du document anglais : https://docs.autismresearchcentre.com/tests/AQ10.pdf

Le seuil de 6 conduit à envisager une évaluation spécialisée ; il ne confirme pas un TSA.

#### Q-CHAT-10

Le Q-CHAT-10 est utilisé dans le jeu tout-petits. Les droits et la traduction française doivent être vérifiés avant intégration. Aucun item ne doit être reformulé sous le même nom sans autorisation.

#### M-CHAT-R/F

L’instrument impose des conditions spécifiques : absence de modification, maintien du copyright et licence requise pour la distribution dans un logiciel ou sur un site, sauf certains usages internes ou non lucratifs définis par les auteurs. Une demande de licence est nécessaire pour une application distribuée.

Source : https://www.mchatscreen.com/mchat-rf/

#### SRS-2, ADOS-2, ADI-R, Vineland et autres outils propriétaires

Ces instruments ne doivent pas être reproduits sans licence. L’application peut stocker les résultats saisis par un professionnel et joindre un compte rendu, mais pas afficher les items ni recalculer les scores sans droit explicite.

---

## 7. Logique d’interprétation

### 7.1 Sorties distinctes

L’application produit quatre objets indépendants :

1. `psychometric_results` — scores officiels ;
2. `clinical_structure` — domaines documentés et qualité des preuves ;
3. `ml_estimate` — probabilité expérimentale et incertitude ;
4. `orientation` — recommandation prudente.

Ils ne doivent pas être additionnés dans un score global opaque.

### 7.2 Évaluation de la complétude

Un indice de complétude doit considérer :

- proportion d’items répondus ;
- présence d’informations sur l’enfance ;
- présence d’exemples concrets ;
- présence d’un informateur ou document ;
- cohérence temporelle ;
- contradictions ;
- contexte de fatigue, dépression ou crise pouvant modifier les réponses.

Catégories : suffisante, partielle, insuffisante.

### 7.3 Compatibilité structurée avec les domaines

Pour le résumé clinique, un domaine est :

- documenté ;
- possiblement documenté ;
- non documenté ;
- contradictoire ;
- non évalué.

La compatibilité globale ne peut être « forte » que si :

- les trois dimensions de communication sociale sont documentées ou possiblement documentées ;
- au moins deux dimensions de comportements répétitifs/sensoriels sont documentées ;
- des éléments existent depuis le développement précoce ;
- un retentissement est rapporté ;
- les données ne sont pas mieux expliquées de façon évidente par un état aigu ou une autre condition non explorée.

Cette logique sert à organiser le dossier, pas à reproduire mécaniquement un diagnostic DSM.

### 7.4 Niveaux de suspicion

#### Faible

Peu d’éléments convergents, histoire développementale peu compatible, retentissement absent ou explication alternative plus probable. La sortie doit préciser que l’outil peut manquer certaines présentations, notamment en cas de camouflage.

#### Intermédiaire

Plusieurs éléments sont compatibles mais les informations sont incomplètes, limitées à une période ou confondues avec d’autres troubles.

#### Élevé

Convergence entre plusieurs domaines, présence ancienne et retentissement significatif. Recommandation d’évaluation spécialisée.

#### Indéterminé

Données manquantes, réponses contradictoires, état aigu, âge hors domaine du modèle ou incertitude statistique trop élevée.

### 7.5 Règles de sécurité

- une alerte de risque suicidaire ou danger immédiat ne doit jamais être traitée par le modèle TSA ;
- aucune conclusion négative catégorique ;
- les résultats ne sont pas communiqués à un tiers sans consentement ;
- pour un mineur, les modalités de consentement et d’autorité parentale doivent être définies juridiquement ;
- les sorties doivent être accessibles, non stigmatisantes et compréhensibles.

---

## 8. Stratégie de données

### 8.1 Couches de stockage

```text
raw/              fichiers sources immuables, hashés, jamais modifiés
staging/          parsing et harmonisation
curated/          cohortes documentées, dédupliquées et versionnées
features/         variables calculées sans fuite de cible
splits/           partitions train/validation/test figées
models/           artefacts, métriques, model cards
reports/          audits qualité, biais, dérive et reproductibilité
```

### 8.2 Registre de provenance

Chaque ligne doit être reliée à :

- source et URL ;
- version ou date de téléchargement ;
- licence ;
- citation ;
- définition de la cible ;
- mode de recrutement ;
- langue ;
- pays ;
- critères d’inclusion et exclusion ;
- présence d’un diagnostic clinique indépendant ;
- niveau de confiance de la cible ;
- transformations appliquées.

### 8.3 Taxonomie des cibles

| Niveau | Définition | Usage ML autorisé |
|---|---|---|
| L0 | classe dérivée du score des mêmes items | reproduction méthodologique uniquement ; pas de revendication clinique |
| L1 | diagnostic auto-déclaré sans document | exploration ; validation externe obligatoire |
| L2 | diagnostic déclaré par parent ou dossier sans détail du protocole | développement prudent |
| L3 | diagnostic clinique documenté selon critères reconnus | entraînement principal possible |
| L4 | consensus multidisciplinaire avec instruments et diagnostics différentiels | référence pour validation clinique |

Le modèle de production ne doit pas être entraîné principalement sur L0.

### 8.4 Déduplication

- hash exact des lignes normalisées ;
- détection des doublons quasi identiques ;
- recherche de sujets présents dans plusieurs fichiers ;
- regroupement par identifiant de participant lorsque disponible ;
- séparation des membres d’une même famille entre plis si la structure familiale peut créer une dépendance ;
- journal des suppressions.

### 8.5 Normalisation sémantique

Exemples :

- `m`, `male`, `M` → catégorie normalisée ;
- `yes`, `YES`, `1` → booléen ;
- `jundice` et `jaundice` → champ unique ;
- pays et ethnicités conservés en données brutes puis mappés vers une nomenclature documentée ;
- `relation` normalisée : self, parent, caregiver, clinician, relative, other.

### 8.6 Séparation des attributs

- variables de prédiction ;
- variables d’audit d’équité ;
- identifiants ;
- variables interdites car postérieures à la cible ;
- variables dérivées.

`country`, `ethnicity`, `sex` et `gender` ne doivent pas être utilisés par défaut pour prédire. Ils servent à mesurer les écarts de performance.

---

## 9. Pipeline machine learning

### 9.1 Objectifs distincts

#### Expérience A — Reproduction du papier

But : reproduire les résultats, y compris la cible dérivée du questionnaire, pour comprendre le pipeline. Le rapport doit porter la mention « reproduction non clinique ».

#### Expérience B — Modèle sans score dérivé

Retirer score total et variables postérieures. La cible reste toutefois circulaire si elle est dérivée des items ; cette expérience mesure seulement la capacité à reconstituer le seuil.

#### Expérience C — Cible clinique indépendante

Entraîner sur des données L3/L4, avec diagnostics différentiels et validation externe. Seule cette expérience peut prétendre soutenir un usage réel.

### 9.2 Baselines obligatoires

- règle officielle du questionnaire ;
- régression logistique pénalisée ;
- arbre peu profond ;
- forêt aléatoire ;
- gradient boosting ;
- modèle nul basé sur la prévalence.

Aucun modèle complexe ne doit être retenu s’il n’apporte pas un bénéfice robuste, calibré et explicable par rapport à la règle simple.

### 9.3 Pipeline anti-fuite

```python
Pipeline([
    ("preprocess", ColumnTransformer([
        ("num", Pipeline([
            ("imputer", SimpleImputer(strategy="median")),
            ("scaler", StandardScaler())
        ]), numeric_columns),
        ("cat", Pipeline([
            ("imputer", SimpleImputer(strategy="most_frequent")),
            ("encoder", OneHotEncoder(handle_unknown="ignore"))
        ]), categorical_columns)
    ])),
    ("sampler", RandomOverSampler(random_state=seed)),
    ("classifier", LogisticRegression(...))
])
```

Le pipeline réel doit être construit avec `imblearn.pipeline.Pipeline` pour garantir que le suréchantillonnage ne s’applique qu’aux données d’entraînement.

### 9.4 Validation

Ordre de préférence :

1. test externe par source ou site ;
2. `GroupKFold` par participant, famille ou centre ;
3. validation temporelle ;
4. validation croisée imbriquée ;
5. bootstrap pour intervalles de confiance.

Le jeu de test final doit être gelé avant le choix du modèle.

### 9.5 Métriques

- sensibilité ;
- spécificité ;
- VPP et VPN ajustées à la prévalence cible ;
- AUC ROC ;
- AUC précision-rappel ;
- F1, mais pas comme unique critère ;
- MCC ;
- Brier score ;
- calibration slope/intercept ;
- expected calibration error ;
- taux d’abstention ;
- taux d’erreur parmi les réponses conclusives ;
- courbe de décision clinique ;
- performances et intervalles de confiance par sous-groupe.

### 9.6 Optimisation du seuil

Le seuil n’est pas choisi pour maximiser l’exactitude. Il est défini selon l’usage :

- en repérage, priorité à la sensibilité mais avec un taux de faux positifs acceptable ;
- possibilité de deux seuils : en dessous, suspicion faible ; au-dessus, élevée ; entre les deux, indéterminée ;
- seuils validés sur une cohorte externe et verrouillés par version.

### 9.7 Calibration et abstention

- calibration isotone ou sigmoïde sur un jeu dédié ;
- intervalle de prédiction ou méthode conforme si pertinente ;
- abstention si variables hors distribution, données manquantes excessives ou probabilité proche de la zone grise ;
- détecteur de dérive et d’out-of-distribution.

### 9.8 Explicabilité

L’interface affiche :

- variables ou domaines ayant contribué ;
- éléments manquants ;
- raison de l’abstention ;
- limites du modèle ;
- version et cohorte de validation.

Les explications SHAP sont réservées au module professionnel ou recherche et ne doivent pas être présentées comme des causes.

### 9.9 Équité

Audits obligatoires :

- genre et sexe ;
- âge ;
- langue ;
- pays ;
- niveau intellectuel ;
- diagnostic TDAH ;
- anxiété/dépression ;
- répondant soi-même ou proche ;
- données complètes ou incomplètes.

Le modèle est bloqué si un sous-groupe important présente une sensibilité ou calibration nettement inférieure sans mesure de mitigation.

### 9.10 Model card

Chaque modèle possède :

- identifiant et version ;
- date ;
- données d’entraînement ;
- critères d’inclusion ;
- variables ;
- cible ;
- performances globales et par groupe ;
- limites ;
- seuils ;
- environnement logiciel ;
- hash de l’artefact ;
- responsable de validation ;
- statut : expérimental, pilote, validé, retiré.

---

## 10. Architecture logicielle

### 10.1 Choix recommandé

- **application principale :** Symfony 7.x, PHP 8.3 ou version supportée au démarrage ;
- **interface :** Twig + Symfony UX / Stimulus / Turbo, ou front séparé si besoin ;
- **base :** PostgreSQL ;
- **service ML :** Python 3.12+, FastAPI, scikit-learn, imbalanced-learn, pandas, MLflow ;
- **messagerie :** Symfony Messenger ;
- **stockage documentaire :** objet S3 compatible, chiffré ;
- **authentification :** OIDC/OAuth2, MFA pour professionnels ;
- **conteneurs :** Docker ;
- **observabilité :** logs structurés, traces, métriques, alertes ;
- **CI/CD :** tests automatiques, scans de dépendances et migrations contrôlées.

### 10.2 Principe de résilience

Le moteur psychométrique et la structuration clinique doivent fonctionner même si le service ML est indisponible. L’échec du ML ne doit jamais empêcher l’utilisateur d’obtenir son score officiel ou sa synthèse.

### 10.3 Arborescence monorepo

```text
/apps
  /web-symfony
  /ml-api
/packages
  /assessment-schema
  /instrument-definitions
  /shared-openapi
/data-pipeline
  /ingestion
  /quality
  /training
  /evaluation
  /model-cards
/docs
  /clinical
  /scientific
  /privacy
  /regulatory
/infrastructure
  /docker
  /terraform
  /monitoring
/tests
  /e2e
  /security
```

### 10.4 Entités principales

#### User

`id`, `email`, `roles`, `status`, `mfa_enabled`, `created_at`, `deleted_at`.

#### Subject

Personne évaluée : `id`, `owner_id`, `birth_year`, `age_band`, `preferred_language`, `consent_status`, données minimisées.

#### Assessment

`id`, `subject_id`, `mode`, `status`, `started_at`, `completed_at`, `form_version`, `created_by`, `respondent_type`.

#### QuestionDefinition

`code`, `section`, `label`, `help`, `response_type`, `age_routes`, `version`, `license_id`.

#### Response

`assessment_id`, `question_code`, `period`, `value`, `unknown`, `example_text`, `source_type`, `confidence`, `answered_at`.

#### InstrumentResult

`instrument_code`, `instrument_version`, `language`, `raw_score`, `interpretation`, `scoring_engine_version`, `date`.

#### ClinicalDomainSummary

`domain_code`, `status`, `evidence_count`, `contradiction_count`, `confidence`, `narrative`.

#### ModelPrediction

`model_version`, `probability`, `risk_band`, `uncertainty`, `abstained`, `reason`, `feature_schema_hash`.

#### Orientation

`level`, `message_template_version`, `recommendations`, `urgent_flag`.

#### Consent

`purpose`, `version`, `granted_at`, `withdrawn_at`, `proof`.

#### AuditEvent

acteur, action, ressource, horodatage, finalité, adresse réseau pseudonymisée et résultat.

### 10.5 API principale

```text
POST   /api/assessments
GET    /api/assessments/{id}
PATCH  /api/assessments/{id}
POST   /api/assessments/{id}/responses
POST   /api/assessments/{id}/complete
GET    /api/assessments/{id}/report
POST   /api/instruments/{code}/score
POST   /api/ml/predict
GET    /api/models/{version}/card
POST   /api/consents
DELETE /api/subjects/{id}
POST   /api/exports/{assessmentId}
```

### 10.6 Contrat ML

Entrée :

```json
{
  "assessment_id": "uuid",
  "model_requested": "adult-clinical-v1",
  "features": {
    "a1_reciprocity": 0.72,
    "a2_nonverbal": 0.54,
    "a3_relationships": 0.81,
    "b1_repetitive": 0.40,
    "b2_change": 0.77,
    "b3_interests": 0.68,
    "b4_sensory": 0.75,
    "developmental_evidence": 0.62,
    "functional_impact": 0.70
  },
  "missingness": {
    "developmental_history": 0.25
  }
}
```

Sortie :

```json
{
  "model_version": "adult-clinical-v1.2.0",
  "probability": 0.74,
  "calibrated": true,
  "risk_band": "intermediate",
  "uncertainty": 0.18,
  "abstained": false,
  "domain_validity": "adult_fr_clinical",
  "warnings": [
    "developmental_history_incomplete"
  ]
}
```

---

## 11. Génération du rapport

### 11.1 Rapport grand public

- rappel du caractère non diagnostique ;
- niveau de suspicion ;
- score des outils autorisés ;
- domaines principalement rapportés ;
- informations insuffisantes ;
- recommandation concrète ;
- ressources fiables ;
- possibilité de télécharger ou supprimer les données.

### 11.2 Rapport professionnel

- contexte ;
- répondant ;
- chronologie ;
- tableau des domaines A1-A3 et B1-B4 ;
- exemples verbatim ;
- retentissement ;
- diagnostics et hypothèses différentielles ;
- scores instrumentaux ;
- documents disponibles ;
- prédiction expérimentale séparée ;
- incertitudes ;
- zones à approfondir ;
- signature et validation humaine.

### 11.3 Exigence de traçabilité

Chaque phrase générée doit pouvoir être reliée aux réponses ou documents sources. Un texte généré par LLM ne peut pas créer un fait absent. Le rapport doit distinguer :

- citation ou donnée explicite ;
- synthèse directe ;
- inférence ;
- information manquante.

---

## 12. Sécurité, RGPD et données de santé

### 12.1 Qualification des données

Les réponses révélant un état mental, un risque de trouble ou un parcours de soins sont des données de santé ou susceptibles de le devenir par leur usage. Elles relèvent des catégories particulières du RGPD.

### 12.2 Principes

- minimisation ;
- finalité explicite ;
- base juridique documentée ;
- consentement séparé pour la recherche lorsque requis ;
- durée de conservation définie ;
- droit d’accès, rectification, portabilité et effacement selon le cadre applicable ;
- analyse d’impact relative à la protection des données avant production ;
- registre des traitements ;
- contrat de sous-traitance ;
- procédure de violation de données.

### 12.3 Hébergement

Si l’application héberge pour le compte d’un professionnel ou établissement des données de santé à caractère personnel dans le cadre de soins, l’exigence HDS doit être analysée et probablement appliquée. Ne pas utiliser un hébergement générique sans qualification juridique.

### 12.4 Mesures techniques

- TLS moderne ;
- chiffrement des bases et objets ;
- clés gérées séparément ;
- secrets hors dépôt ;
- MFA pour comptes sensibles ;
- RBAC et principe du moindre privilège ;
- journaux d’accès inaltérables ;
- sauvegardes chiffrées et testées ;
- suppression vérifiable ;
- séparation environnements ;
- données de production interdites en développement ;
- pseudonymisation des exports de recherche ;
- scans SAST, DAST et dépendances ;
- tests d’intrusion avant pilote clinique.

### 12.5 LLM et documents

- aucune donnée envoyée à un fournisseur externe sans contrat et information claire ;
- option de traitement local ou dans un environnement conforme ;
- interdiction d’utiliser les données pour entraîner un modèle tiers par défaut ;
- redaction des identifiants avant traitement ;
- conservation du texte source et validation humaine.

---

## 13. Réglementation produit

### 13.1 Phase d’information et de repérage

Une application strictement informative, qui restitue un questionnaire et recommande de consulter sans fournir une décision médicale personnalisée, peut être positionnée hors dispositif médical selon sa finalité et sa communication. Cette qualification doit être confirmée par une analyse réglementaire écrite.

### 13.2 Aide à la décision clinique

Si le logiciel fournit une probabilité ou une recommandation utilisée pour diagnostiquer, orienter ou prendre une décision médicale individuelle, il peut relever du règlement européen sur les dispositifs médicaux en tant que logiciel dispositif médical. La finalité déclarée, les allégations marketing et l’usage réel sont déterminants.

### 13.3 Préparation à une éventuelle qualification de dispositif médical

Même pour un MVP non médical, l’architecture doit permettre ultérieurement :

- système qualité ;
- gestion des risques ISO 14971 ;
- cycle de vie logiciel IEC 62304 ;
- utilisabilité IEC 62366-1 ;
- évaluation clinique ;
- cybersécurité et surveillance après mise sur le marché ;
- gestion des changements de modèle ;
- traçabilité des exigences et tests.

### 13.4 Intelligence artificielle

Une analyse spécifique du règlement européen sur l’IA doit être conduite si le système est intégré à un dispositif médical ou utilisé dans un contexte à haut risque. Le projet doit conserver documentation des données, performances, supervision humaine, logs et gestion des risques.

---

## 14. Tests et assurance qualité

### 14.1 Tests fonctionnels

- routage correct selon l’âge ;
- sauvegarde et reprise ;
- gestion de `inconnu` et `non applicable` ;
- calcul exact des instruments ;
- règles d’abstention ;
- export ;
- suppression ;
- consentement ;
- accessibilité clavier et lecteur d’écran.

### 14.2 Tests scientifiques

- reproduction exacte des règles ;
- absence de variables de fuite ;
- transformations ajustées uniquement sur l’entraînement ;
- reproductibilité avec seeds ;
- intervalles de confiance ;
- tests de permutation ;
- audit de calibration ;
- tests par sous-groupe ;
- test sur source externe ;
- tests de dérive.

### 14.3 Tests de sécurité

- contrôle d’accès horizontal et vertical ;
- injection ;
- XSS/CSRF ;
- SSRF ;
- téléversement de documents ;
- secrets ;
- bruteforce ;
- journalisation ;
- chiffrement ;
- restauration de sauvegarde ;
- suppression des données.

### 14.4 Accessibilité

- WCAG 2.2 AA comme cible ;
- langage clair ;
- mode faible stimulation ;
- désactivation des animations ;
- contraste ;
- progression visible ;
- possibilité de pause ;
- explications courtes et longues ;
- aucune limite de temps non nécessaire.

---

## 15. Critères d’acceptation du MVP

Le MVP est accepté si :

1. l’utilisateur comprend avant de commencer que l’outil ne diagnostique pas ;
2. le formulaire est versionné et routé selon l’âge ;
3. les réponses peuvent être inconnues ou non applicables ;
4. le score officiel, lorsqu’autorisé, est calculé par tests unitaires sur tous les cas limites ;
5. la synthèse cite les éléments sources ;
6. le système peut conclure « indéterminé » ;
7. le ML peut être désactivé sans casser le parcours ;
8. aucune variable postérieure à la cible n’est transmise au modèle ;
9. les performances sont affichées avec intervalles de confiance et calibration ;
10. les données sont chiffrées et les droits d’accès testés ;
11. l’utilisateur peut exporter et supprimer son dossier ;
12. le modèle n’affiche jamais une conclusion diagnostique ;
13. les licences des questionnaires intégrés sont documentées ;
14. un comité clinique a validé les textes de sortie ;
15. une DPIA et une analyse réglementaire ont été réalisées avant le pilote.

---

## 16. Plan de réalisation

### Phase 0 — Gouvernance

- comité clinique et personnes concernées ;
- décision de finalité ;
- analyse des licences ;
- DPO ;
- analyse réglementaire ;
- protocole de validation.

### Phase 1 — Reproduction scientifique

- téléchargement et hash des quatre jeux ;
- rapport de qualité ;
- reproduction du papier ;
- démonstration de la circularité ;
- notebooks reproductibles ;
- rapport de limites.

### Phase 2 — MVP sans modèle clinique

- formulaire original ;
- moteur psychométrique ;
- synthèse ;
- compte utilisateur facultatif ;
- export ;
- sécurité et accessibilité.

### Phase 3 — Données cliniques

- partenariat CRA, centre hospitalier, cabinet ou laboratoire ;
- protocole éthique ;
- consentement ;
- collecte prospective ;
- groupes différentiels ;
- double cotation ;
- suivi de qualité.

### Phase 4 — Modèle expérimental

- cible L3/L4 ;
- validation imbriquée ;
- validation externe ;
- calibration ;
- audit d’équité ;
- étude d’utilisabilité ;
- model card.

### Phase 5 — Pilote clinique

- mode silencieux : le modèle ne modifie pas la décision ;
- comparaison avec évaluation habituelle ;
- analyse des erreurs ;
- validation prospective ;
- décision de qualification réglementaire.

---

## 17. Risques projet

| Risque | Gravité | Mesure |
|---|---:|---|
| confusion entre repérage et diagnostic | élevée | wording, écrans de consentement, validation clinique, interdiction d’allégations |
| modèle apprenant le score du questionnaire | élevée | taxonomie des cibles, tests de fuite, séparation psychométrie/ML |
| faux négatifs chez femmes ou personnes camouflant | élevée | section camouflage, audit par genre, données cliniques diversifiées |
| faux positifs liés au TDAH/anxiété | élevée | groupes différentiels, synthèse prudente, pas de score unique |
| licence questionnaire violée | élevée | registre des licences, modules désactivés sans autorisation |
| fuite de données de santé | critique | architecture sécurité, HDS, DPIA, tests d’intrusion |
| dérive du modèle | élevée | monitoring, seuils de dérive, retrait de version |
| hallucination du rapport IA | élevée | génération ancrée, preuves, validation humaine |
| utilisation discriminatoire | critique | CGU, contrôle d’accès, interdiction des usages emploi/assurance |
| surinterprétation par l’utilisateur | élevée | explications, incertitude, orientation professionnelle |

---

## 18. Décisions d’architecture à prendre à l’ouverture du projet

1. MVP anonyme uniquement ou comptes personnels ?
2. Public adulte en premier, ou parcours multi-âge dès la V1 ?
3. Intégration d’un instrument sous licence ou simple saisie de score ?
4. Hébergement HDS dès le début ou prototype sans données nominatives ?
5. Partenaire clinique disponible pour la collecte prospective ?
6. Statut prévu : outil grand public, recherche, professionnel ou dispositif médical ?
7. Front Symfony UX ou SPA séparée ?
8. Usage ou non d’un LLM pour la synthèse ?
9. Export PDF simple ou dossier interopérable FHIR ?
10. Politique de conservation et de suppression ?

---

## 19. Livrables techniques attendus

- dépôt monorepo ;
- dictionnaire de données ;
- registre de licences ;
- OpenAPI ;
- JSON Schema du formulaire ;
- moteur de règles avec tests ;
- pipeline ETL ;
- notebooks de reproduction ;
- package Python d’entraînement ;
- service d’inférence ;
- model card ;
- rapport de calibration ;
- rapport d’équité ;
- DPIA ;
- threat model ;
- protocole de validation clinique ;
- manuel utilisateur et professionnel ;
- procédure de retrait d’un modèle.

---

## 20. Références et liens

### Article principal

- Hasan et al., IEEE Access 2023 : https://doi.org/10.1109/ACCESS.2022.3232490

### Données

- UCI adultes : https://archive.ics.uci.edu/dataset/426/autism+screening+adult
- UCI enfants : https://archive.ics.uci.edu/dataset/419/autistic+spectrum+disorder+screening+data+for+children
- UCI adolescents : https://archive.ics.uci.edu/dataset/420/autistic+spectrum+disorder+screening+data+for+adolescent
- Kaggle tout-petits : https://www.kaggle.com/datasets/fabdelja/autism-screening-for-toddlers
- SFARI Base : https://base.sfari.org/
- NIMH Data Archive : https://nda.nih.gov/
- ABIDE : http://fcon_1000.projects.nitrc.org/indi/abide/

### Instruments

- AQ-10 officiel anglais : https://docs.autismresearchcentre.com/tests/AQ10.pdf
- M-CHAT-R/F et conditions : https://www.mchatscreen.com/mchat-rf/

### Recommandations et réglementation à intégrer au dossier qualité

- NICE CG142 — Autism spectrum disorder in adults : https://www.nice.org.uk/guidance/cg142
- Haute Autorité de santé — rechercher les recommandations TSA enfant/adolescent et adulte dans leur version en vigueur : https://www.has-sante.fr/
- CNIL — données de santé et traitements : https://www.cnil.fr/
- Agence du Numérique en Santé — HDS : https://esante.gouv.fr/
- Commission européenne — règlement dispositifs médicaux et guides MDCG : https://health.ec.europa.eu/medical-devices-sector_en

---

## 21. Conclusion de cadrage

Il est techniquement possible de construire immédiatement une application de repérage structurée, explicable et utile. Les quatre jeux du papier suffisent pour reproduire la méthode et démontrer les problèmes de fuite de cible. Ils ne suffisent pas pour valider une détection clinique du TSA.

Le projet doit donc commencer par un produit sans prétention diagnostique, avec un moteur de formulaire et de synthèse solide. Le machine learning clinique vient ensuite, après acquisition de données dont la cible est indépendante, inclusion de groupes différentiels et validation externe. Cette séparation protège à la fois l’utilisateur, la qualité scientifique et la viabilité réglementaire du projet.
