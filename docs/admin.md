# Administration — Documentation Nucleus

> Interface d'administration découplée du front.  
> Produit les JSON consommés par le public.

---

## Philosophie

- L'admin **connaît le front** — elle charge `config.php` via `config_admin.php`
- Le front **ne connaît pas l'admin** — jamais l'inverse
- Mêmes conventions que le public : `DIR_*`, BEM, `htmlspecialchars`, zéro `../` en dur

---

## Architecture

```
admin/
├── api/
│   ├── create_page_file.php  ← crée inc/pages/{id}.php standard
│   ├── delete_article.php
│   ├── delete_gallery.php
│   ├── delete_image.php
│   ├── delete_page.php
│   ├── get_article.php
│   ├── get_gallery.php
│   ├── get_page.php
│   ├── list_articles.php
│   ├── list_galleries.php
│   ├── list_images.php
│   ├── list_pages.php
│   ├── rename_image.php
│   ├── save_article.php
│   ├── save_gallery.php
│   ├── save_menus.php
│   ├── save_page.php
│   └── upload_image.php
├── css/
│   ├── admin.css             ← classes génériques partagées
│   ├── login.css
│   └── pages/
│       ├── articles.css
│       ├── medias.css
│       ├── medias_images.css
│       ├── menus.css
│       └── pages.css
├── inc/
│   ├── footer.php
│   ├── head.php
│   ├── header.php
│   └── main.php
├── js/
│   ├── article_editor.js
│   └── page_builder.js
├── pages/
│   ├── articles.php
│   ├── dashboard.php
│   ├── galleries.php
│   ├── medias.php
│   ├── medias_images.php
│   ├── menus.php
│   └── pages.php
├── src/
│   ├── folder_manager.class.php
│   ├── gallery_manager.class.php  ← à auditer
│   ├── image_uploader.class.php
│   └── model/
│       └── config_model.php       ← doublon — à supprimer
├── tests/
│   ├── test_api_v2.php
│   ├── test_audit.php
│   ├── test_block_registry.php
│   ├── test_component_model.php
│   ├── test_hash.php
│   └── test_json_handler.php
├── config_admin.php
├── index.php
├── login.php
└── login.class.php
```

**Fichiers partagés front/admin (dans `src/`) :**
- `src/core/component_model.php` — CRUD générique articles
- `src/core/page_model.php` — CRUD pages
- `src/core/block_registry.php` — types : title, text, list, link, image (`dataType: null` pour image)
- `src/model/config_model.php` — source unique langues et config
- `src/utils/json_handler.php` — lecture/écriture JSON atomique

---

## Configuration — `config_admin.php`

**Convention** : tout fichier admin charge `config_admin.php` en première ligne.

### Constantes exposées

| Constante | Valeur |
|---|---|
| `ADMIN_PATH` | Chemin absolu vers `/admin/` |
| `JSON_PAGES_DIR` | `DIR_JSON . 'pages/'` |
| `JSON_ARTICLES_DIR` | `DIR_JSON . 'articles/'` |
| `JSON_GALLERIES_DIR` | `DIR_JSON . 'galleries/'` |
| `GALLERIES_DIR` | `DIR_IMG_CONTENT . 'galleries/'` |
| `ADMIN_PAGES` | `['dashboard', 'pages', 'articles', 'medias', 'medias_images', 'menus', 'galleries']` |
| `SESSION_LIFETIME` | `3600` |
| `UPLOAD_MAX_SIZE` | `2 Mo` |
| `UPLOAD_ALLOWED_TYPES` | `jpeg, png, webp` |

---

## Modèles

### `ConfigModel` — `src/model/config_model.php`
- `getLangs()` → `[['code' => 'fr', 'label' => 'Français'], ...]`
- `getDefaultLang()` → `$langs[0]['code'] ?? 'fr'`
- Source de vérité pour les langues

> `admin/src/model/config_model.php` est un doublon — à supprimer.

### `ComponentModel` — `src/core/component_model.php`
- `new ComponentModel($storageDir, $langs, $componentType)`
- `$langs` → `array_column(ConfigModel::getLangs(), 'code')`

### `PageModel` — `src/core/page_model.php`
- Types de références : `article_ref`, `gallery_ref`
- `createEmpty($title)` — page vide en `draft`
- `exists($id)` — vérifié par `save_menus.php` avant création

### `FolderManager` — `admin/src/folder_manager.class.php`
- `create($name)` — répertoire + `thumbs/` systématique
- `rename`, `delete`, `list`, `exists`

### `ImageUploader` — `admin/src/image_uploader.class.php`
- Retourne `['base' => 'home/photo', 'ext' => 'jpg']`
- Grand format 1280px + miniature 400px
- Conversion JPG systématique

---

## API — `admin/api/`

**Articles**

| Fichier | Méthode | Rôle |
|---|---|---|
| `list_articles.php` | GET | Liste les articles |
| `get_article.php` | GET | Charge un article |
| `save_article.php` | POST | Crée ou met à jour |
| `delete_article.php` | POST | Supprime |

**Pages**

| Fichier | Méthode | Rôle |
|---|---|---|
| `list_pages.php` | GET | Liste les layouts |
| `get_page.php` | GET | Charge un layout |
| `save_page.php` | POST | Crée ou met à jour |
| `delete_page.php` | POST | Supprime |
| `create_page_file.php` | POST | Crée `inc/pages/{id}.php` standard |

**Galeries**

| Fichier | Méthode | Rôle |
|---|---|---|
| `list_galleries.php` | GET | Liste les galeries JSON |
| `get_gallery.php` | GET | Charge une galerie |
| `save_gallery.php` | POST | Crée ou met à jour |
| `delete_gallery.php` | POST | Supprime |

**Menus**

| Fichier | Méthode | Rôle |
|---|---|---|
| `save_menus.php` | POST | Sauvegarde `menus.json` + crée pages manquantes |

**Images**

| Fichier | Méthode | Rôle |
|---|---|---|
| `list_images.php` | GET | Liste répertoires ou images |
| `upload_image.php` | POST | Upload + resize |
| `delete_image.php` | POST | Supprime original + thumb |
| `rename_image.php` | POST | Renomme original + thumb |

---

## Galeries JSON — `galleries.php`

Les galeries sont des **composants de pages au même niveau que les articles** — ni l'un n'inclut l'autre. Un layout de page peut contenir des `article_ref` et des `gallery_ref` en parallèle.

### Format `json/galleries/{folder}.json`

```json
{
    "type": "gallery_ref",
    "folder": "ghitta",
    "title": { "fr": "Titre", "en": "Title" },
    "images": [
        {
            "src": "photo.jpg",
            "alt":     { "fr": "Description", "en": "Description" },
            "caption": { "fr": "Légende",     "en": "Caption" }
        }
    ]
}
```

- `folder` — correspond à un répertoire dans `public/img/content/`
- `title` — multilingue, piloté par `config.json`
- `alt` — obligatoire pour l'accessibilité
- `caption` — optionnel

### Éditeur `galleries.php`
- Sidebar liste les galeries existantes
- Sélection du répertoire d'images
- Titre multilingue avec onglets langue
- Lignes images — `src` via navigateur médias, `alt` et `caption` multilingues

---

## `gallery_ref` dans le layout de page — deux modes

```json
{ "type": "gallery_ref", "folder": "ghitta" }
```
→ **Mode simple** — toutes les images du répertoire, sans métadonnées

```json
{ "type": "gallery_ref", "folder": "ghitta", "gallery": "ghitta" }
```
→ **Mode riche** — charge `json/galleries/ghitta.json`, titre + alt + caption

`folder` et `gallery` peuvent différer — plusieurs galeries JSON peuvent référencer le même répertoire.

### Dans `page_builder.js`
- Premier `<select>` — répertoire d'images (`data-folder`)
- Second `<select>` — galerie JSON optionnelle (`data-gallery`) — "Rendu simple" si vide
- Prévisualisation — titre + nombre d'images + lien vers `galleries.php`

---

## Menus — `menus.php` + `save_menus.php`

- Deux sections sur une page — `Main_menu` et `RS_menu`
- Langues pilotées par `config.json` — les langues orphelines sont perdues à la sauvegarde
- Réordonnement par boutons ↑↓
- **Création automatique** — nouvelle entrée → `json/pages/{page}.json` créé en `draft`

---

## Pages — `pages.php` + `create_page_file.php`

- Sidebar liste les layouts `json/pages/`
- Indicateur : `📄` si `inc/pages/{page}.php` absent, `✓` si présent
- Clic `📄` → crée le fichier standard sans rechargement

**Coexistence des deux modèles :**
```
inc/main.php
  → inc/pages/{page}.php existe ? → charge le fichier PHP (logique libre)
  → sinon                          → PageRenderer depuis json/pages/{page}.json
```

---

## Bloc image — format JSON

```json
{
    "type": "image",
    "src": "home/photo.jpg",
    "alt": "Description"
}
```

- `src` — chemin relatif depuis `public/img/content/`
- `dataType: null` dans `BlockRegistry` — pas de champ `data` multilingue
- Rendu : `<img class="nucleus-image" src="..." alt="..." loading="lazy">`

---

## Module contacts — fermé

Coordonnées gérées via articles standards.

---

## Langues

```php
$langKeys = array_column(ConfigModel::getLangs(), 'code');

foreach (ConfigModel::getLangs() as $langue) {
    $langue['code'];
    $langue['label'];
}
```

---

## Sessions

```php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

---

## CSS

| Fichier | Rôle |
|---|---|
| `admin.css` | Layout, boutons, inputs, messages — toutes pages |
| `pages/articles.css` | Éditeur articles |
| `pages/medias.css` | Noms de répertoires |
| `pages/medias_images.css` | Thumbnails, renommage |
| `pages/pages.css` | Page builder |

---

## JavaScript

### `article_editor.js`
- Types : `title`, `text`, `list`, `link`, `image`
- Navigateur médias — modale, `list_images.php`, sélection → `block-src`
- `API_BASE = 'api/'`

### `page_builder.js`
- Classe `PageEditor`, registry pattern
- `loadResources()` — articles + galeries JSON en parallèle
- Bloc `gallery_ref` — deux selects : répertoire + galerie JSON optionnelle
- Prévisualisation galerie — mode simple ou riche selon sélection
- `window.LANG_CODES` et `window.LANG_LABELS` injectés par `pages.php`

---

## Ce qui a été fait

### Sessions 4-5 — 2026-05-08/10
- Audit et correction config, sessions, chemins
- Migration `api/v2/` → `api/`
- Fermeture module contacts

### Session 6 — 2026-05-16
- `FolderManager`, `ImageUploader`
- Pages `medias.php`, `medias_images.php`
- Bloc `image` complet

### Session 7 — 2026-05-16
- Éditeur de menus + création automatique de pages
- `pages.php` — indicateur PHP + `create_page_file.php`

### Session 8 — 2026-05-22
- Galeries JSON — API complète, `galleries.php`
- `page_builder.js` — deux modes gallery_ref, prévisualisation
- Front adapté ✅

### Session 9 — 2026-05-23
- Architecture galeries validée — composants de pages, pas dans les articles
- Nettoyage vestiges JS et PHP effectué
- `admin.md` mis à jour

---

## Ce qu'il reste à faire

### Court terme
- [ ] Supprimer `admin/src/model/config_model.php` — doublon
- [ ] Supprimer `admin/pages/galleries.old.php` — vestige
- [ ] Supprimer `admin/gallery_image_management.php` — vestige pré-refacto
- [ ] Supprimer `src/core/page_renderer.beforegaley.php` — backup
- [ ] Supprimer `public/img/deco/logo-old.svg` et `logo.reold.svg`
- [ ] Déplacer modale médias hors du `<form>` dans `articles.php`
- [ ] Vérifier `admin/css/pages/contacts.css` — module contacts fermé

### Moyen terme
- [ ] Auditer `admin/tests/`
- [ ] Auditer `gallery_manager.class.php`
- [ ] Migrer CSS inline `showNotification()` vers classes
- [ ] Sécuriser uploads — vérification MIME réelle
- [ ] `login.php` — nettoyer HTML, passer en français
- [ ] Page "Configuration" — éditer `config.json` (titre, langues)
- [ ] Admin logo — upload SVG sécurisé (`upload_logo.php` produit, à intégrer)
- [ ] Balises OG — alimentées depuis JSON page/article courant
- [ ] `.htaccess` — sécuriser `/config/`, `/json/`, `/src/`

---

## Ambitions — pistes ouvertes

### Routing automatique
Fallback dans `inc/main.php` — si aucun `.php` dédié, `PageRenderer` prend le relais.

### Galeries — architecture validée et définitive
Articles et galeries sont des composants de pages de même niveau. Pas de galerie dans un article.

### PHP libre dans l'éditeur
Éditer `inc/pages/{page}.php` depuis l'admin — phase 2 de `create_page_file.php`.

### Brouillons
`status: draft` dans le modèle — logique front à implémenter.

---

## Tests — `admin/tests/`

| Fichier | Composant |
|---|---|
| `test_api_v2.php` | Endpoints API |
| `test_audit.php` | Audit général |
| `test_block_registry.php` | `BlockRegistry` |
| `test_component_model.php` | `ComponentModel` |
| `test_hash.php` | Hash login |
| `test_json_handler.php` | `JsonHandler` |

---

*Dernière mise à jour : session 9 — 2026-05-23*  
*Prochaine session : nettoyage vestiges + routing automatique.*
