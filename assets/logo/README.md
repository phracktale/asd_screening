# Logo Apparaître

Logo reconstruit en vectoriel natif (SVG), **fond transparent** : aucun aplat blanc,
le « A » est réellement évidé dans le disque (`fill-rule="evenodd"`), il laisse donc
passer la couleur du support.

## Fichiers

| Fichier | Usage | Poids |
| --- | --- | --- |
| `apparaitre-logo.svg` | Logo complet (symbole + mot-symbole), texte converti en tracés | ~2,8 Ko |
| `apparaitre-mark.svg` | Symbole seul, carré 1:1 — favicon, avatar, app icon | ~0,3 Ko |
| `apparaitre-logo-text.svg` | Même logo, mais mot-symbole en `<text>` éditable | ~0,6 Ko |

`apparaitre-logo.svg` est la version de référence : le texte étant vectorisé, le rendu
est identique partout, sans dépendre de la fonte installée sur le poste client.

`apparaitre-logo-text.svg` reste pratique pour retoucher le libellé ou l'interlettrage,
mais nécessite **Montserrat Light (300)** ; sans elle, le navigateur retombe sur la pile
`Helvetica Neue, Arial, sans-serif` et le rendu diffère. À réserver aux maquettes.

## Couleurs

| Rôle | Hex | Usage |
| --- | --- | --- |
| Corail | `#E4533E` | Symbole |
| Ardoise | `#3C4757` | Mot-symbole |

Contraste `#3C4757` sur blanc : **9,4:1** — conforme WCAG 2.1 AA et AAA pour le texte.

## Intégration

```html
<!-- Balise img : le SVG est décoratif ici, le nom est déjà dans le lien -->
<a href="/"><img src="/assets/logo/apparaitre-logo.svg" alt="Apparaître" width="240" height="199"></a>
```

```html
<!-- Inline : permet de piloter les couleurs en CSS (thème sombre, survol…) -->
<svg class="logo" role="img" aria-labelledby="logo-title">…</svg>
```

Le SVG porte déjà `role="img"` et un `<title>` référencé par `aria-labelledby` : en inline,
il est correctement restitué par les lecteurs d'écran. En `<img>`, c'est l'attribut `alt`
qui fait foi — mettez `alt=""` si le logo est redondant avec un texte adjacent.

Pensez à `width` **et** `height` (ou un `aspect-ratio` en CSS) pour éviter le décalage de
mise en page au chargement (CLS).

## Géométrie

Repère du symbole : carré `0 0 200 200`, disque de rayon 100 centré en (100, 100).

- Sommet du « A » : `(100, 36)` — pieds à `y = 162`, de `x = 34` à `x = 166`
- Contre-forme : sommet `(100, 88)`, base de `x = 70` à `x = 130`

Mot-symbole : Montserrat Light (300), capitales, interlettrage 0,15 em, corps 42 unités,
ligne de base à `y = 271,4`, soit 42 unités sous le disque.

Zone de protection recommandée autour du logo : **la moitié du rayon du disque** (50 unités
du repère, soit 25 % de la largeur du symbole).

## Régénérer

Les fichiers sont produits par un script qui convertit `APPARAÎTRE` en tracés via
`fontTools` à partir de `Montserrat-Light.ttf` (Google Fonts, licence OFL).
Pour modifier le libellé, l'interlettrage ou le corps, il faut relancer cette conversion —
sinon, éditez `apparaitre-logo-text.svg` et revectorisez ensuite.
