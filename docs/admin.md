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
├── api/                  ← Endpoints API actifs
│   ├── delete_article.php
│   ├── delete_image.php
│   ├── delete_page.php
│   ├── get_article.php
│   ├── get_page.php
│   ├── list_articles.php
│   ├── list_images.php      ← lecture seule, répertoires + thumbs
│   ├── list_pages.php
│   ├── rename_image.php
│   ├── save_article.php
│   ├── save_page.php
│   └── upload_image.php
├── css/
│   ├── admin.css            ← classes génériques partagées
│   ├── login.css
│   └── pages/
│       ├── articles.css
│       ├── medias.css       ← spécifique medias.php
│       ├── medias_images.css ← spécifique medias_images.php
│       └── pages.css
├── inc/
│   ├── footer.php
│   ├── head.php
│   ├── header.php
│   └── main.php
├── js/
│   ├── article_editor.js    ← éditeur articles + navigateur médias
│   └── page_builder.js
├── pages/
│   ├── articles.php
│   ├── dashboard.php
│   ├── galleries.php
│   ├── medias.php           ← gestion des répertoires
│   ├── medias_images.php    ← gestion des images d'un répertoire
│   └── pages.php
├── src/
│   ├── folder_manager.class.php   ← CRUD répertoires + thumbs/
│   ├── gallery_manager.class.php  ← à auditer
│   ├── image_uploader.class.php   ← upload + resize + thumbs
│   └── model/
│       └── config_model.php       ← doublon — à supprimer
├── tests/
│   ├── test_api_v2.php
│   ├── test_audit.php
│   ├── test_block_registry.php
│   ├── test_component_model.php
│   └── test_json_handler.php
├── config_admin.php
├── index.php
├── login.php
└── login.class.php
```

**Fichiers partagés front/admin (dans `src/`) :**
- `src/core/component_model.php` — CRUD générique, utilisé par les API articles
- `src/core/page_model.php` — CRUD pages
- `src/core/block_registry.php` — validation des blocs (types : title, text, list, link, image)
- `src/model/config_model.php` — source unique pour les langues et la config
- `src/utils/json_handler.php` — lecture/écriture JSON atomique

---

## Configuration — `config_admin.php`

Responsabilités :
- Inclure `config.php` (socle front — chemins, modèles, langues, menus)
- Définir les chemins propres à l'admin (`ADMIN_PATH`, `JSON_PAGES_DIR`, `JSON_ARTICLES_DIR`, `GALLERIES_DIR`)
- Déclarer la whitelist des pages admin (`ADMIN_PAGES`)
- Configurer et démarrer la session PHP
- Définir les limites et types d'upload autorisés

**Convention** : tout fichier admin charge `config_admin.php` en première ligne — jamais `config.php` directement, jamais après une vérification de session.

### Constantes exposées

| Constante | Valeur |
|---|---|
| `ADMIN_PATH` | Chemin absolu vers `/admin/` |
| `JSON_PAGES_DIR` | `DIR_JSON . 'pages/'` |
| `JSON_ARTICLES_DIR` | `DIR_JSON . 'articles/'` |
| `GALLERIES_DIR` | `DIR_IMG_CONTENT . 'galleries/'` |
| `ADMIN_PAGES` | `['dashboard', 'pages', 'articles', 'medias', 'medias_images']` |
| `SESSION_LIFETIME` | `3600` |
| `UPLOAD_MAX_SIZE` | `2 Mo` |
| `UPLOAD_ALLOWED_TYPES` | `jpeg, png, webp` |

---

## Modèles

### `ConfigModel` — `src/model/config_model.php`
Partagé avec le front. Utilisable dans les deux contextes.

- `getLangs()` → `[['code' => 'fr', 'label' => 'Français'], ...]`
- `getDefaultLang()` → `$langs[0]['code'] ?? 'fr'`
- `clearCache()` disponible pour les tests

> `admin/src/model/config_model.php` est un doublon — à supprimer.

### `ComponentModel` — `src/core/component_model.php`
CRUD générique pour les composants (articles, tout futur type).

- Constructeur : `new ComponentModel($storageDir, $langs, $componentType)`
- `$langs` → `array_column(ConfigModel::getLangs(), 'code')`
- Délègue la validation à `BlockRegistry`

### `PageModel` — `src/core/page_model.php`
CRUD pour les layouts de pages.

- Types de références autorisés : `article_ref`, `gallery_ref`

### `FolderManager` — `admin/src/folder_manager.class.php`
CRUD répertoires de médias.

- `create($name)` — crée le répertoire + `thumbs/` systématiquement
- `rename($old, $new)`, `delete($name)`, `list()`, `exists($name)`
- `basename()` sur tous les chemins entrants — sécurisé

### `ImageUploader` — `admin/src/image_uploader.class.php`
Upload et traitement d'images.

- Entrée : `$_FILES` + nom de répertoire
- Sortie : `['base' => 'home/photo', 'ext' => 'jpg']`
- Grand format (1280px) + miniature (400px) générés automatiquement
- Conversion JPG systématique — PNG/WebP avec fond blanc
- Ne crée pas les dossiers — `FolderManager` s'en charge

---

## API — `admin/api/`

Tous les endpoints suivent le même contrat :
- `config_admin.php` chargé en **première ligne**
- Auth vérifiée immédiatement après (`$_SESSION['user']`)
- `Content-Type: application/json` systématique
- Réponse unifiée `['success' => bool, ...]`

**Articles**

| Fichier | Méthode | Rôle |
|---|---|---|
| `list_articles.php` | GET | Liste les articles |
| `get_article.php` | GET | Charge un article |
| `save_article.php` | POST | Crée ou met à jour un article |
| `delete_article.php` | POST | Supprime un article |

**Pages**

| Fichier | Méthode | Rôle |
|---|---|---|
| `list_pages.php` | GET | Liste les layouts |
| `get_page.php` | GET | Charge un layout |
| `save_page.php` | POST | Crée ou met à jour un layout |
| `delete_page.php` | POST | Supprime un layout |

**Images**

| Fichier | Méthode | Rôle |
|---|---|---|
| `list_images.php` | GET | Liste répertoires ou images d'un répertoire |
| `upload_image.php` | POST | Upload + resize via `ImageUploader` |
| `delete_image.php` | POST | Supprime original + thumb |
| `rename_image.php` | POST | Renomme original + thumb avec slugify |

---

## Bloc image — format JSON

```json
{
    "type": "image",
    "src": "home/photo.jpg",
    "alt": "Description de l'image"
}
```

- `src` — chemin relatif depuis `public/img/content/`
- `alt` — obligatoire pour l'accessibilité
- Pas de champ `data` multilingue — `dataType: null` dans `BlockRegistry`

**Rendu front** — `ArticleRenderer::renderImage()` :
```html
<img class="nucleus-image" src="/public/img/content/home/photo.jpg" alt="..." loading="lazy">
```

**Workflow** :
1. Upload via `medias_images.php` + `upload_image.php`
2. Sélection via navigateur médias dans l'éditeur (`btn-browse-media`)
3. Stockage du chemin dans le JSON article
4. Rendu par `ArticleRenderer`

---

## Module contacts — fermé

Les coordonnées sont des articles standards via `ArticleRenderer`. API, page et JS contacts supprimés.

---

## Langues

```php
// Pattern à utiliser partout
$langKeys = array_column(ConfigModel::getLangs(), 'code');

foreach (ConfigModel::getLangs() as $langue) {
    $langue['code'];
    $langue['label'];
}
```

> L'ancienne forme `array_keys` / `foreach ($langs as $code => $label)` est abandonnée.

---

## Sessions

Gérées exclusivement dans `config_admin.php` :

```php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

`login.class.php` utilise `session_status() === PHP_SESSION_NONE` pour éviter le double démarrage.

---

## CSS

| Fichier | Rôle |
|---|---|
| `admin.css` | Reset, variables, layout, boutons, inputs — toutes pages |
| `css/pages/articles.css` | Éditeur articles |
| `css/pages/medias.css` | Spécifique `medias.php` — noms de répertoires |
| `css/pages/medias_images.css` | Spécifique `medias_images.php` — thumbnails |
| `css/pages/pages.css` | Page builder |

---

## JavaScript

### `article_editor.js`
- `SUPPORTED_LANGS` depuis `data-config` du DOM
- `API_BASE = 'api/'`
- Types de blocs : `title`, `text`, `list`, `link`, `image`
- Navigateur médias intégré — modale, `list_images.php`, sélection → remplit `block-src`
- `addEventListener` uniquement, `escapeHtml()` via `textContent`

### `page_builder.js`
- Architecture classe `PageEditor`
- Registry pattern — `article_ref`, `gallery_ref`, `ui_component`
- `window.availableGalleries` injecté par `pages.php`

---

## Ce qui a été fait

### Session 4 — 2026-05-08
- Audit `config_admin.php`, `config_model.php` — versions corrigées
- Correction langue `code`/`label` dans les endpoints et `articles.php`
- Cartographie arborescence — vestiges identifiés

### Session 5 — 2026-05-10
- Application des corrections — config, session, chemins
- Migration `admin/api/v2/` → `admin/api/`
- Fermeture module contacts
- Tests complets articles et pages ✅

### Session 6 — 2026-05-16
- `FolderManager` — classe générique CRUD répertoires
- `ImageUploader` — réécriture, WebP supporté, retourne `{base, ext}`
- Pages `medias.php` et `medias_images.php` — fragments admin
- API images — `upload_image.php`, `delete_image.php`, `rename_image.php`, `list_images.php`
- Bloc `image` intégré — `BlockRegistry`, `ArticleRenderer`, `article_editor.js`
- Navigateur médias dans l'éditeur articles
- CSS factorisé — commun dans `admin.css`, spécifiques dans `css/pages/`

---

## Ce qu'il reste à faire

### Court terme
- [ ] Supprimer `admin/src/model/config_model.php` — doublon
- [ ] Déplacer modale médias hors du `<form>` dans `articles.php`
- [ ] Nettoyer `admin/pages/galleries.php` — bloc session commenté

### Moyen terme
- [ ] Auditer `admin/tests/` — cinq fichiers à vérifier
- [ ] Auditer `gallery_manager.class.php` — statut inconnu
- [ ] Migrer CSS inline de `showNotification()` vers classes
- [ ] Sécuriser uploads — vérification MIME réelle
- [ ] `login.php` — nettoyer HTML, passer en français

---

## Ambitions — pistes ouvertes

### Routing automatique
Fallback dans `inc/main.php` — si aucun `.php` dédié, `PageRenderer` prend le relais.

### Éditeur de menus
Créer une page et l'ajouter au menu en une opération — cohérence garantie.

### Types de pages
Pages JSON via `PageRenderer` + pages statiques PHP — les deux coexistent.

### Brouillons
`status: draft` déjà dans le modèle — logique front à implémenter.

---

## Tests — `admin/tests/`

| Fichier | Composant testé |
|---|---|
| `test_api_v2.php` | Endpoints API |
| `test_audit.php` | Audit général |
| `test_block_registry.php` | `BlockRegistry` |
| `test_component_model.php` | `ComponentModel` |
| `test_json_handler.php` | `JsonHandler` |

---

*Dernière mise à jour : session 6 — 2026-05-16*  
*Prochaine session : nettoyage vestiges + audit tests.*
