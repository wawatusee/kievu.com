# NUCLEUS — Manifeste & Documentation

> Framework PHP procédural, headless, multilingue et réutilisable.  
> Pensé pour être compris d'un coup d'œil, repris sans douleur.

---

## Philosophie

**Nucleus** est un socle de site web PHP sans framework, sans POO forcée, sans magie cachée.

Les choix structurants :

- **Procédural organisé** — pas de MVC, pas d'orienté objet imposé. La logique est lisible de haut en bas.
- **JSON comme base de données** — les contenus (articles, pages, menus, config) vivent en fichiers JSON. Pas de BDD relationnelle, pas de dépendance serveur lourde.
- **CMS headless** — une interface d'administration découplée du front. Le front consomme les JSON, l'admin les produit.
- **Zéro framework** — pas de Composer, pas de React, pas de Laravel. PHP natif, JS natif, CSS natif.
- **Réutilisable** — la structure est conçue pour être dupliquée et adaptée à d'autres projets en ne touchant qu'à la configuration.

---

## Architecture générale

```
/
├── config/               ← Configuration (technique + métier)
├── docs/                 ← Documentation du projet
├── inc/                  ← Includes front (head, header, main, footer)
├── json/                 ← Base de données JSON
│   ├── articles/
│   ├── galleries/        ← JSON de galeries riches (titre, alt, caption)
│   ├── pages/
│   └── menus.json
├── public/               ← Seul dossier exposé au web
│   ├── index.php         ← Point d'entrée unique
│   ├── css/
│   ├── js/
│   │   ├── menu.js       ← burger responsive
│   │   ├── lightbox.js   ← lightbox galerie — chargé globalement
│   │   └── pages/        ← JS spécifique par page (chargé si existant)
│   └── img/
│       ├── content/      ← images de contenu — organisées par sous-dossier
│       └── deco/         ← logo.svg, icônes RS dans deco/rs/
├── src/                  ← Logique métier
│   ├── core/
│   ├── model/
│   ├── utils/
│   └── view/
└── tests/                ← Scripts de test par composant
```

> **Règle absolue** : seul `/public/` est accessible depuis le web. Tout le reste est hors racine publique.

---

## Décisions architecturales

### `$singlePage` déprécié
Le mode single-page scroll (2015-2020) est abandonné. Aucun projet actif ne l'utilise. SEO, performance et accessibilité favorisent le multi-pages.

Supprimé de : `config.json`, `config.php`, `config_model.php`, `main.php`, `header.php`, `view_menus.php`.  
`inc/nav.php` supprimé — absorbé dans `header.php`.

### Module contacts fermé
Les coordonnées de contact sont des articles comme les autres — `contact-coordonnees.json` fonctionne via `ArticleRenderer` avec des blocs `text` et `link`.

Exception future : si un formulaire d'envoi de message est envisagé, il nécessitera un composant dédié avec traitement PHP.

### Hiérarchie des titres
Les articles commencent à `h2` — le `h1` appartient à la page, pas aux articles. Le niveau est piloté par le JSON (`"level": 2`). Le CSS qualifie par niveau HTML (`h2.nucleus-title`, `h3.nucleus-title`) pour préserver l'indépendance de chaque niveau.

### Bloc image — philosophie
Le JSON stocke l'identité de l'image (`src`), pas ses propriétés d'affichage. Pas de `width`, `height`, ni `align` dans la donnée — le CSS et le contexte décident. L'auteur uploade ses médias via le gestionnaire avant d'écrire son article.

### Galerie — composant de page
La galerie est un composant de page (`gallery_ref`) — pas un bloc d'article. Elle vit dans `json/pages/{page}.json`, pas dans `json/articles/`. Cette séparation préserve la simplicité du système d'articles.

### SVG inline — logo et icônes RS
Les SVG sont injectés via `file_get_contents()` plutôt qu'en balise `<img>`. Avantages : contrôle CSS total (`fill`, animations, variables), pas de requête HTTP supplémentaire.

Les couleurs sont pilotées par des variables CSS :
```css
--logo-fill:   var(--color-accent);
--logo-stroke: var(--color-primary-dark);
--logo-text:   var(--color-accent);
```

Le texte du logo est masqué dans le footer via `.site-footer .decor-logo text { display: none; }`.

Les icônes RS sont injectées depuis `public/img/deco/rs/{titre}.svg` — le nom du fichier correspond au champ `titre` du `RS_menu`.

### Tests
Pas de framework de test. Convention : un fichier `tests/test_*.php` par composant critique, écrit au moment de la correction. Zéro dépendance externe.

Fichiers de test existants :
- `tests/test_menus_model.php` ✓
- `tests/test_view_menus.php` ✓
- `tests/test_config_model.php` ✓

---

## Configuration

### `config/config.json` — Config métier (éditable via l'admin)

```json
{
    "titleWebsite": ["mon-", "site", ".fr"],
    "repImg": "content",
    "repImgDeco": "deco",
    "langs": [
        {"code": "fr", "label": "Français"},
        {"code": "en", "label": "Anglais"}
    ]
}
```

- `langs` : liste des langues disponibles, le premier est la langue par défaut
- Les chemins (`repImg`, `repImgDeco`) sont des **clés logiques** — les chemins absolus sont construits par `config.php`

### `config/config.php` — Config technique (dev uniquement)

Responsabilités :
- Définir toutes les constantes de chemins (`ROOT_PATH`, `DIR_JSON`, `DIR_IMG`, `DIR_IMG_DECO`...)
- Charger et parser `config.json`
- Dériver les constantes (`SITE_TITLE`, `LANG_DEFAULT`, `PAGE_ARRAY`...)
- Instancier les modèles (`ConfigModel`, `MenusModel`)

**Convention de nommage des constantes :**

| Préfixe | Usage |
|---|---|
| `DIR_*` | Chemins absolus serveur |
| `PUBLIC_*` | Chemins relatifs navigateur |
| `APP_*` | État de l'application (langue courante...) |

**Constantes disponibles :**

```php
define('ROOT_PATH',          realpath(...));
define('DIR_JSON',           ROOT_PATH . 'json/');
define('DIR_IMG',            ROOT_PATH . 'public/img/');
define('DIR_IMG_CONTENT',    DIR_IMG . 'content/');
define('DIR_IMG_DECO',       DIR_IMG . 'deco/');
define('PUBLIC_PATH',        '/public/');
define('PUBLIC_IMG',         PUBLIC_PATH . 'img/');
define('PUBLIC_IMG_CONTENT', PUBLIC_IMG . 'content/');
```

### `config/config_admin.php` — Config admin

- Inclut `config.php` (l'admin connaît le front, pas l'inverse)
- Session démarrée ici — jamais dans les endpoints ou pages
- Ajoute : `ADMIN_PATH`, `JSON_PAGES_DIR`, `JSON_ARTICLES_DIR`, `GALLERIES_DIR`
- Limites upload : 2 Mo, types `jpeg`, `png`, `webp`

**Convention** : tout fichier admin charge `config_admin.php` en première ligne — jamais `config.php` directement.

---

## Point d'entrée — `public/index.php`

Séquence d'exécution :

```
1. require config.php        ← chemins, constantes, modèles, menus
2. Détection de $lang        ← ?lang=fr, fallback sur LANG_DEFAULT
3. Détection de $page        ← ?page=home, fallback sur premier menu, whitelist PAGE_ARRAY
4. HTML / head.php           ← balises meta, CSS, title
5. header.php                ← logo, nav, sélecteur de langue, burger
6. main.php                  ← contrôleur central, chargement de la page
7. footer.php                ← pied de page
```

### Détection de langue

```php
if (isset($_GET['lang']) && in_array($_GET['lang'], array_column($langs, 'code'))) {
    $lang = $_GET['lang'];
} else {
    $lang = LANG_DEFAULT;
}
define('APP_LANG', $lang);
```

### Détection de page

```php
if (isset($_GET['page']) && in_array($_GET['page'], PAGE_ARRAY)) {
    $page = htmlspecialchars($_GET['page']);
} else {
    $page = $defaultPage;
}
```

`PAGE_ARRAY` est construit depuis `menus.json` — la whitelist est vivante, pas codée en dur.

---

## Contrôleur central — `inc/main.php`

`$page` est détecté et validé en amont dans `public/index.php`.  
`PAGE_ARRAY` est construit depuis `menus.json` via `config.php`.

```php
if (in_array($page, PAGE_ARRAY)) {
    require_once __DIR__ . '/pages/' . $page . '.php';
} else {
    require_once __DIR__ . '/pages/404.php';
}
```

---

## Header & Navigation — `inc/header.php`

### Structure HTML

```
<header class="site-header">
    <div class="header-title">       ← logo SVG inline + titre
    <div class="site-header__controls">
        <nav class="site-nav">       ← menu principal
        <div class="lang-switcher">  ← sélecteur de langue
        <button class="burger">      ← burger mobile
```

### Logo — SVG inline

```php
<div class="decor-logo">
    <?php echo file_get_contents(DIR_IMG_DECO . 'logo.svg'); ?>
</div>
```

Le SVG contient le texte du titre et les deux triangles. Les couleurs sont contrôlées via variables CSS. Le titre HTML est supprimé — il vit dans le SVG.

### Conventions BEM

| Classe | Rôle |
|---|---|
| `.site-header` | Block header |
| `.header-title` | Conteneur logo + titre |
| `.decor-logo` | Conteneur SVG inline |
| `.site-header__controls` | Element contrôles |
| `.site-nav` | Block nav |
| `.site-nav--open` | Modifier nav ouverte (mobile) |
| `.nav__link` | Element lien de nav |
| `.nav__link--active` | Modifier lien actif |
| `.lang-switcher` | Block sélecteur de langue |
| `.lang--active` | Modifier langue active |
| `.burger` | Block burger |
| `.burger--open` | Modifier burger ouvert |
| `.burger__bar` | Element barre du burger |

### Variables locales — `header.css`

```css
--header-direction:   row;
--header-justify:     space-between;
--header-align:       center;
--header-gap:         var(--spacing-sm);
--header-padding:     var(--spacing-xs) var(--spacing-md);
--header-logo-height: 52px;
--header-bg:          var(--color-primary);
```

### Sélecteur de langue
Construction dynamique avec `http_build_query` — conserve tous les paramètres GET existants en remplaçant uniquement `lang`.

### Burger mobile
Animé en CSS pur (trois `<span>` → croix). Le JS toggle `.site-nav--open`, `.burger--open` et `aria-expanded`. Fermeture automatique au clic sur un lien.

---

## Footer — `inc/footer.php`

- Données de contact chargées depuis `json/articles/contact-coordonnees.json`
- Menu footer via `ViewMenu(APP_LANG, '')` — pas de lien actif
- Icônes RS — SVG inline depuis `public/img/deco/rs/{titre}.svg`
- Logo SVG inline — texte masqué via CSS

### Logo footer

```php
<div class="decor-logo">
    <?php echo file_get_contents(DIR_IMG_DECO . 'logo.svg'); ?>
</div>
```

### Icônes RS

```php
$svgPath = DIR_IMG_DECO . 'rs/' . $label . '.svg';
if (file_exists($svgPath)) {
    echo file_get_contents($svgPath);
} else {
    echo '<span class="sr-only">' . $label . '</span>';
}
```

Le fichier SVG doit porter le même nom que le champ `titre` du `RS_menu` — ex: `facebook.svg`, `instagram.svg`.

### Variables locales — `footer.css`

```css
--footer-bg:          var(--color-primary);
--footer-color:       white;
--footer-link-color:  rgba(255, 255, 255, 0.8);
--footer-logo-height: 50px;
--footer-padding:     var(--spacing-lg) var(--spacing-md);
--footer-gap:         var(--spacing-lg);
--footer-col-min:     200px;
```

### Classes BEM

| Classe | Rôle |
|---|---|
| `.site-footer` | Block footer |
| `.site-footer__grid` | Grille auto-répartie |
| `.site-footer__bloc` | Colonne générique |
| `.site-footer__title` | Titre de bloc |
| `.site-footer__text` | Texte |
| `.site-footer__link` | Lien de contact |
| `.site-footer__rs` | Bloc réseaux sociaux |
| `.site-footer__logo` | Logo centré |
| `.rs-link` | Lien RS — cercle cliquable |
| `.rs-link--{nom}` | Variante par réseau |

---

## Modèles — `src/model/`

### `MenusModel`
- Charge `json/menus.json`
- `getMenu(string $menuType)` retourne `null` si le type est absent
- Exceptions explicites si fichier absent ou JSON malformé
- `tests/test_menus_model.php` ✓

### `ConfigModel`
- Pattern cache statique — `loadConfig()` ne lit le fichier qu'une fois
- `getLangs()` retourne `[['code' => 'fr', 'label' => 'Français']]`
- `getDefaultLang()` retourne `$langs[0]['code']`
- `getTitle()` retourne le titre depuis `titleWebsite`
- `clearCache()` disponible pour les tests
- `tests/test_config_model.php` ✓

---

## Vue — `src/view/view_menus.php`

### `ViewMenu`

```php
new ViewMenu(APP_LANG, $page)   // header — lien actif
new ViewMenu(APP_LANG, '')      // footer — pas de lien actif
```

- `$lang` et `$currentPage` définis au constructeur
- `getViewMainMenu(array $menuArray): string`
  - Génère les liens `.nav__link`
  - Ajoute `.nav__link--active` sur la page courante
  - Fallback titre : langue demandée → `fr` → `$item->page`
  - `htmlspecialchars` sur le label
- `tests/test_view_menus.php` ✓

---

## Core — `src/core/`

### `PageRenderer`
- Charge `json/pages/{id}.json`
- Dispatche chaque entrée du layout : `article_ref`, `gallery_ref`, `ui_component`
- Instanciation : `new PageRenderer(APP_LANG)`
- Erreurs loggées via `error_log` — silencieuses en prod, traçables

### `ArticleRenderer`
- Rendu statique par blocs : `title`, `text`, `list`, `link`, `image`
- Méthode `t()` — fallback `?:` sur chaîne vide
- Fallback : langue demandée → `fr` → `en` → chaîne vide

### `BlockRegistry`
- Source de vérité pour les types de blocs valides
- Chaque type déclare : `label`, `fields`, `dataType`
- `dataType: null` — pas de champ `data` multilingue (ex: `image`)
- Types enregistrés : `title`, `text`, `list`, `link`, `image`

### `JsonHandler`
- Lecture sécurisée avec exceptions explicites
- Écriture atomique via fichier `.tmp`
- `listFiles`, `exists`, `delete` disponibles

---

## Blocs de contenu — format JSON

### Blocs multilingues

```json
{"type": "title", "level": 2, "data": {"fr": "Titre", "en": "Title"}}
{"type": "text", "data": {"fr": "Texte...", "en": "Text..."}}
{"type": "list", "data": {"fr": ["item1", "item2"], "en": ["item1", "item2"]}}
{"type": "link", "url": "https://...", "data": {"fr": "Texte", "en": "Text"}}
```

### Bloc image — sans champ `data`

```json
{"type": "image", "src": "home/photo.jpg", "alt": "Description"}
```

- `src` — chemin relatif depuis `public/img/content/`
- `alt` — texte alternatif — obligatoire pour l'accessibilité
- Pas de `width`, `height`, `align` — le CSS décide

### Association image + texte

```json
[
    {"type": "image", "src": "home/photo.jpg", "alt": "..."},
    {"type": "text", "data": {"fr": "Légende ou texte associé"}}
]
```

---

## Galerie — `gallery_ref`

Composant de page — pas d'article. Déclaré dans `json/pages/{page}.json` :

### Mode simple — scan du dossier

```json
{"type": "gallery_ref", "folder": "accueil"}
```

Affiche toutes les images de `public/img/content/accueil/` sans métadonnées.

### Mode riche — JSON de galerie

```json
{"type": "gallery_ref", "folder": "accueil", "gallery": "accueil-selection"}
```

Charge `json/galleries/accueil-selection.json` — titre multilingue, sélection d'images, alt et caption par image.

### Structure `json/galleries/{id}.json`

```json
{
    "title": {"fr": "Titre", "en": "Title"},
    "images": [
        {
            "src": "photo.jpg",
            "alt": {"fr": "Description", "en": "Description"},
            "caption": {"fr": "Légende", "en": "Caption"}
        }
    ]
}
```

- `title` — optionnel, multilingue
- `alt` — multilingue, vide accepté
- `caption` — optionnel, multilingue — absent = pas de `<figcaption>`
- Le même dossier peut alimenter plusieurs galeries JSON différentes

### Rendu — `PageRenderer::renderGalleryRef()`
- Thumbs utilisés si `{folder}/thumbs/` existe, sinon originaux
- Lien vers le full size sur chaque image
- `loading="lazy"` natif
- Classes BEM : `.nucleus-gallery`, `.nucleus-gallery__title`, `.gallery-grid`, `.gallery-item`, `.gallery-item__link`, `.gallery-item__img`, `.gallery-item__caption`

### Workflow
1. Créer un répertoire via l'admin médias
2. Uploader les images — thumbs générés automatiquement
3. Mode simple : ajouter `gallery_ref` avec `folder` dans le layout
4. Mode riche : créer un JSON dans `json/galleries/` via l'admin, référencer avec `gallery`

### Lightbox — `public/js/lightbox.js`
- Chargé globalement dans `head.php`
- S'active uniquement si des `.gallery-item__link` sont présents
- Navigation : flèches ← → dans l'interface + touches `ArrowLeft` / `ArrowRight`
- Boucle circulaire — dernière image → première
- Flèches masquées si une seule image
- Fermeture : clic fond, bouton ×, touche Escape
- Isolé — remplaçable par toute autre librairie sans toucher au HTML

Classes BEM lightbox :

| Classe | Rôle |
|---|---|
| `.lightbox` | Overlay |
| `.lightbox--open` | Modifier ouvert |
| `.lightbox__img` | Image plein écran |
| `.lightbox__close` | Bouton fermeture |
| `.lightbox__prev` | Flèche précédente |
| `.lightbox__next` | Flèche suivante |

---

## Gestionnaire de médias — `admin/`

### Workflow
1. L'auteur uploade ses images via `admin/pages/medias_images.php`
2. `ImageUploader` génère grand format (1280px) + thumb (400px)
3. Structure : `public/img/content/{dir}/photo.jpg` + `{dir}/thumbs/photo.jpg`
4. Dans l'éditeur d'article — bouton "Parcourir" ouvre le navigateur de médias
5. Clic sur une image → remplit automatiquement le champ `src`

---

## Design System CSS

### Hiérarchie des feuilles de style

| Niveau | Fichier | Rôle |
|---|---|---|
| 1 | `style.css` | Variables globales, reset, typo, utilitaires, décoration SVG |
| 2 | `header.css` | Header + nav |
| 2 | `main.css` | Contenant principal + blocs nucleus + galerie |
| 2 | `footer.css` | Pied de page |
| 3 | `pages/{page}.css` | Surcharges spécifiques à une page |

### Section 12 — `style.css` — Décoration SVG inline

```css
.decor-logo {
    --logo-fill:   var(--color-accent);
    --logo-stroke: var(--color-primary-dark);
    --logo-text:   var(--color-accent);
}

.decor-logo svg {
    width:   auto;
    height:  100%;
    display: block;
}
```

### Classes nucleus — produites par `ArticleRenderer`

| Classe | Rôle |
|---|---|
| `.nucleus-article` | Conteneur article |
| `h1.nucleus-title` | Titre niveau 1 — accent border-bottom |
| `h2.nucleus-title` | Titre niveau 2 |
| `h3.nucleus-title` | Titre niveau 3 |
| `h4-6.nucleus-title` | Titres mineurs |
| `.nucleus-text` | Paragraphe |
| `.nucleus-link` | Lien ou bouton |
| `.nucleus-list` | Liste à puces — marker accent |
| `.nucleus-image` | Image responsive — lazyload natif |

### Classes galerie — produites par `PageRenderer`

| Classe | Rôle |
|---|---|
| `.nucleus-gallery` | Conteneur galerie |
| `.nucleus-gallery__title` | Titre optionnel |
| `.gallery-grid` | Grille auto-fill |
| `.gallery-item` | Figure individuelle |
| `.gallery-item__link` | Lien vers full size |
| `.gallery-item__img` | Miniature |
| `.gallery-item__caption` | Légende optionnelle |
| `.lightbox` | Overlay lightbox |
| `.lightbox--open` | Modifier ouvert |
| `.lightbox__img` | Image plein écran |
| `.lightbox__close` | Bouton fermeture |

### Variables globales clés — `style.css`

```css
/* Couleurs */
--color-primary, --color-primary-dark, --color-accent
--color-bg, --color-surface, --color-text, --color-muted, --color-border

/* Typographie */
--font-base, --font-title
--fs-xs, --fs-sm, --fs-md, --fs-lg, --fs-xl

/* Espacement */
--spacing-xs, --spacing-sm, --spacing-md, --spacing-lg

/* Largeurs */
--width-content: 1100px
--width-wide: 1200px

/* Divers */
--radius, --transition
```

---

## Menus — `json/menus.json`

```json
{
    "Main_menu": [
        {
            "page": "home",
            "titre": { "fr": "Accueil", "en": "Home" }
        }
    ],
    "RS_menu": [
        {
            "page": "https://www.facebook.com/...",
            "titre": "facebook"
        }
    ]
}
```

- `Main_menu` alimente `PAGE_ARRAY` et la navigation principale
- `RS_menu` alimente les icônes RS du footer — le champ `titre` doit correspondre au nom du fichier SVG dans `public/img/deco/rs/`

---

## Lancement serveur local

```powershell
php -S localhost:8000 -t public
```

---

## Déploiement — gestion des chemins navigateur

### Le problème

`PUBLIC_PATH` définie comme constante statique `/public/` suppose que le site est toujours à la racine du domaine. Deux cas cassent cette hypothèse :

- **Windows en local** — `DIRECTORY_SEPARATOR` produit des backslashes qui corrompent les URLs
- **Déploiement dans un sous-dossier** — `/tao/`, `/nucleus/` — les chemins hardcodés ne remontent pas au bon niveau

### Solution actuelle — détection dynamique via `$_SERVER`

En production sur hébergement mutualisé (OVH) :

```php
$docRoot  = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
$rootPath = rtrim(str_replace('\\', '/', ROOT_PATH), '/');
$basePath = str_replace($docRoot, '', $rootPath);
define('PUBLIC_PATH',        $basePath . '/public/');
define('PUBLIC_IMG',         PUBLIC_PATH . 'img/');
define('PUBLIC_IMG_CONTENT', PUBLIC_IMG . 'content/');
```

Fonctionne sur OVH mutualisé. Fragilité : `DOCUMENT_ROOT` peut varier selon la configuration Apache/Nginx du serveur.

### Solution cible — `config/env.php` ← à implémenter

Un fichier ignoré par git, à créer manuellement à chaque déploiement :

```php
<?php
// config/env.php — NE PAS COMMITTER
define('BASE_PATH', '/sous-dossier');  // vide si racine du domaine
```

Dans `config.php` :

```php
require_once __DIR__ . '/env.php';
define('PUBLIC_PATH',        BASE_PATH . '/public/');
define('PUBLIC_IMG',         PUBLIC_PATH . 'img/');
define('PUBLIC_IMG_CONTENT', PUBLIC_IMG . 'content/');
```

**Avantages :** zéro détection magique, explicite, compatible tous serveurs.  
**Inconvénient :** une étape manuelle à l'installation — à documenter dans le README de déploiement.

### Redirection `index.php` racine — dynamique

```php
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host     = $_SERVER['HTTP_HOST'];
$base     = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
header('Location: ' . $protocol . '://' . $host . $base . '/public/', true, 301);
exit;
```

### Injection PHP → JS

Quand une constante `PUBLIC_*` doit traverser la frontière PHP → JS, passer par `data-*` :

```php
<!-- PHP -->
data-public-content='<?= htmlspecialchars(PUBLIC_IMG_CONTENT, ENT_QUOTES) ?>'
```

```javascript
// JS
const PUBLIC_CONTENT = document.getElementById('el').dataset.publicContent;
```

Ne jamais hardcoder `/public/` dans le JS. Toute URL navigateur passe par `PUBLIC_IMG_CONTENT`. Tout chemin serveur passe par `DIR_*`. Ces deux mondes ne se mélangent jamais.

### Fichiers communs à synchroniser entre projets

Lors d'une fusion entre deux projets basés sur Nucleus, ces fichiers sont partagés et doivent être traités avec précaution :

| Fichier | Risque |
|---|---|
| `config/config.php` | `PUBLIC_PATH` dynamique vs statique |
| `public/index.php` | Redirection dynamique |
| `src/model/config_model.php` | Structure langues |
| `src/core/block_registry.php` | Types de blocs |
| `src/core/article_renderer.php` | Rendu blocs |
| `inc/header.php` | Structure HTML |
| `public/css/style.css` | Variables CSS |
| `admin/api/save_article.php` | Langues dynamiques |
| `admin/api/get_article.php` | Langues dynamiques |
| `admin/api/delete_article.php` | Langues dynamiques |
| `admin/api/list_articles.php` | Langues dynamiques |
| `admin/pages/articles.php` | Interface éditeur |
| `admin/js/article_editor.js` | Logique éditeur |

---

## Conventions à respecter

- **Chemins** : toujours des constantes `DIR_*` (absolus) ou `PUBLIC_*` (navigateur) — jamais de `../` en dur
- **Nommage CSS** : BEM — `.block__element--modifier`
- **Variables CSS** : chaque composant expose ses variables locales en tête de fichier
- **SVG** : inline via `file_get_contents(DIR_IMG_DECO . 'fichier.svg')` — couleurs via variables CSS
- **Titres** : les articles commencent à `h2` — `h1` appartient à la page
- **Blocs** : le JSON décrit ce que c'est, le CSS décrit comment ça s'affiche
- **Galerie** : composant de page uniquement — jamais dans un article
- **Nommage JS** : `camelCase`, `addEventListener` uniquement — pas de `onclick` inline
- **Sécurité** : toujours `htmlspecialchars()` sur les variables affichées, whitelist sur `$page`
- **Config** : une seule source de vérité — `config.json` pour le métier, `config.php` dérive les constantes
- **Tests** : tout point de friction corrigé → un `tests/test_*.php` associé
- **Erreurs** : jamais silencieuses — `error_log` minimum, exception explicite si critique
- **Langues** : `array_column(getLangs(), 'code')` pour les codes, `foreach ($langs as $langue)` pour itérer
- **Médias** : uploader avant d'éditer — l'éditeur ne gère que les chemins, pas les fichiers

---

## Ce qu'il reste à faire

### Court terme

- [ ] **Balises OG** — alimentées depuis le JSON de la page ou de l'article courant
- [ ] **`.htaccess`** — sécuriser `/config/`, `/json/`, `/src/`
- [ ] **Admin logo** — interface upload et remplacement du logo depuis l'admin

### Moyen terme

- [ ] **CSS admin** — migrer le CSS inline de `showNotification()` vers des classes
- [ ] **Uploads** — vérification MIME réelle, pas seulement l'extension
- [ ] **`login.php`** — nettoyer le HTML, passer en français
- [ ] **Nettoyage vestiges** — `article_editor.old.js`, `page_builder.old.js`, `image_uploader.class.old.php`

### Long terme — ambitions

- [ ] **Routing automatique** — fallback `PageRenderer` si pas de fichier `.php` dédié
- [ ] **Brouillons** — exploiter `status: draft` côté front
- [ ] **Internationalisation complète** — traductions des contenus JSON par langue
- [ ] **Kit de démarrage** — template réutilisable vierge stabilisé
- [ ] **Tests** — formaliser la couverture sur les composants critiques

---

*Dernière mise à jour : session 9 — 2026-05-22*  
*Prochaine session : balises OG + admin logo.*
