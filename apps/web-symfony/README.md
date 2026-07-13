# apps/web-symfony

Emplacement réservé à l'application web (Symfony 7 / PHP 8.3+, Twig + Symfony UX),
conformément au choix recommandé au chapitre 10.1 de la spécification.

**Non encore initialisée.** Le cœur métier (`src/`, namespace `TsaRepere\`) est
développé en amont, framework-agnostique et testé ; l'application web viendra
l'exposer (formulaire multi-âge, parcours de consentement, génération de rapport).

Rappel (chap. 10.2) : le moteur psychométrique et la structuration clinique doivent
fonctionner même si le service ML est indisponible.
