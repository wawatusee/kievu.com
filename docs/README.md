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
│   ├── pages/
│   └── menus.json
├── public/               ← Seul dossier exposé au web
│   ├── index.php         ← Point d'entrée unique
│   ├── css/
│   ├── js/
│   │   └── pages/        ← JS spécifique par page (chargé si existant)
│   └── img/
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

### Tests
Pas de framework de test (trop tôt dans la refacto). Convention : un fichier `tests/test_*.php` par composant critique, écrit au moment de la correction. Zéro dépendance externe.

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
- Définir toutes les constantes de chemins (`ROOT_PATH`, `DIR_JSON`, `DIR_IMG`...)
- Charger et parser `config.json`
- Dériver les constantes (`SITE_TITLE`, `LANG_DEFAULT`, `PAGE_ARRAY`...)
- Instancier les modèles (`ConfigModel`, `MenusModel`)

**Convention de nommage des constantes :**

| Préfixe | Usage |
|---|---|
| `DIR_*` | Chemins absolus serveur |
| `PUBLIC_*` | Chemins relatifs navigateur |
| `APP_*` | État de l'application (langue courante...) |

### `config/config_admin.php` — Config admin

- Inclut `config.php` (l'admin connaît le front, pas l'inverse)
- Ajoute : limites d'upload, types autorisés, paramètres de session

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
    <a class="site-header__logo">        ← logo SVG en <img>
    <nav class="site-nav" id="siteNav">  ← menu principal
    <div class="site-header__controls">  ← langue + burger
```

### Conventions BEM appliquées

| Classe | Rôle |
|---|---|
| `.site-header` | Block header |
| `.site-header__logo` | Element logo |
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

### Logo
Fichier : `public/img/deco/logo.svg`  
Intégration : balise `<img>` — pas de SVG inline.

### Sélecteur de langue
Construction dynamique avec `http_build_query` — conserve tous les paramètres GET existants en remplaçant uniquement `lang`.

### Burger mobile
Animé en CSS pur (trois `<span>` → croix). Le JS toggle uniquement les classes `.site-nav--open` et `.burger--open` et l'attribut `aria-expanded`.

---

## Footer — `inc/footer.php`

- Données de contact chargées depuis `json/articles/contact-coordonnees.json`
- Menu footer via `ViewMenu(APP_LANG, '')` — pas de lien actif
- `RS_menu` chargé depuis `$menuRS` disponible via `config.php`
- Logo en `<img>` — pas de SVG inline

### Classes BEM

| Classe | Rôle |
|---|---|
| `.site-footer` | Block footer |
| `.site-footer__grid` | Grille contact + menu |
| `.site-footer__bloc` | Colonne générique |
| `.site-footer__title` | Titre de bloc |
| `.site-footer__text` | Texte |
| `.site-footer__link` | Lien de contact |
| `.site-footer__rs` | Bloc réseaux sociaux |
| `.site-footer__logo` | Logo bas de page |
| `.rs-link` | Lien RS |
| `.rs-icon--{nom}` | Icône spécifique (facebook, instagram...) |

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
- `isSinglePage()` supprimée
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
- Rendu statique par blocs : `title`, `text`, `list`, `link`
- Méthode `t()` — fallback `?:` sur chaîne vide (corrigé — `??` ne suffisait pas)
- Fallback : langue demandée → `fr` → `en` → chaîne vide

### `JsonHandler`
- Lecture sécurisée avec exceptions explicites
- Écriture atomique via fichier `.tmp`
- `listFiles`, `exists`, `delete` disponibles

---

## Design System CSS

### Hiérarchie des feuilles de style

| Niveau | Fichier | Rôle |
|---|---|---|
| 1 | `style.css` | Variables, reset, typo, utilitaires |
| 2 | `header.css` | Header + nav (fusion de l'ancien menu.css) |
| 2 | `main.css` | Contenant principal, sections |
| 2 | `footer.css` | Pied de page |
| 3 | `pages/{page}.css` | Surcharges spécifiques à une page |

Chaque niveau 2 et 3 **pioche dans les variables** de `style.css` — jamais de valeurs codées en dur.

### Variables clés

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
- `RS_menu` alimente les liens réseaux sociaux
- Les titres sont multilingues sur `Main_menu`, simples sur `RS_menu`

---

## Lancement serveur local

```powershell
php -S localhost:8000 -t public
```

- `-t public` expose uniquement `/public/` — cohérent avec la prod
- Ouvrir `http://localhost:8000` dans le navigateur

---

## Conventions à respecter

- **Chemins** : toujours des constantes `DIR_*` (absolus) ou `PUBLIC_*` (navigateur) — jamais de `../` en dur
- **Nommage CSS** : BEM — `.block__element--modifier`
- **Nommage JS** : `camelCase`, `addEventListener` uniquement — pas de `onclick` inline
- **Sécurité** : toujours `htmlspecialchars()` sur les variables affichées, whitelist sur `$page`
- **Config** : une seule source de vérité — `config.json` pour le métier, `config.php` dérive les constantes
- **Tests** : tout point de friction corrigé → un `tests/test_*.php` associé
- **Erreurs** : jamais silencieuses — `error_log` minimum, exception explicite si critique

---

## Ce qu'il reste à faire

### Court terme — CSS

- [ ] **`header.css`** — vérifier et compléter les classes BEM
- [ ] **`footer.css`** — à écrire entièrement
- [ ] **Logo** — créer `public/img/deco/logo.svg`

### Moyen terme — contenu

- [ ] **Balises OG** — alimentées depuis le JSON de la page ou de l'article courant
- [ ] **API v2** — unifier le contrat (structure de réponse, gestion des erreurs, lecture vs écriture)
- [ ] **`admin/`** — audit complet, aligner sur `config_admin.php`
- [ ] **`.htaccess`** — sécuriser `/config/`, `/json/`, `/src/`

### Long terme — ambitions

- [ ] **Internationalisation complète** — traductions des contenus JSON par langue
- [ ] **Système de templates** — pages construites depuis des blocs JSON réutilisables
- [ ] **Galeries** — finaliser `gallery_manager.class.php` et l'interface admin
- [ ] **Gestion des contacts** — finaliser l'API v2 contacts
- [ ] **Kit de démarrage** — nettoyer le projet pour en faire un template réutilisable vierge
- [ ] **Tests** — formaliser la couverture sur les composants critiques

---

*Dernière mise à jour : session 3 — 2026-05-03*  
*Prochaine session : CSS header et footer — classes BEM à styler.*
