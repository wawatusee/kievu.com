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
├── api/                  ← Ancienne API (à supprimer après migration)
│   ├── delete_article.php
│   ├── galleries_api.php
│   ├── get_article.php
│   ├── get_articles_list.php
│   ├── get_contacts_list.php
│   ├── save_article.php
│   ├── save_contact.php
│   ├── save_page.php
│   └── v2/               ← API active (migration → racine api/ prévue session 5)
│       ├── delete_article.php
│       ├── delete_page.php
│       ├── get_article.php
│       ├── get_page.php
│       ├── list_articles.php
│       ├── list_pages.php
│       ├── page_model.php    ← vestige — actif dans src/core/
│       ├── save_article.php
│       ├── save_page.php
│       └── archives/         ← à supprimer
├── inc/
│   ├── footer.php
│   ├── head.php
│   ├── header.php
│   └── main.php
├── js/
│   ├── article_editor.js
│   ├── article_editor.old.js   ← vestige
│   ├── contact_editor.js
│   ├── page_builder.js
│   ├── page_builder copy.js    ← vestige
│   └── page_builder.old.js     ← vestige
├── pages/
│   ├── articles.php
│   ├── contacts.php
│   ├── dashboard.php
│   ├── galleries.php
│   └── pages.php
├── src/
│   ├── gallery_manager.class.php
│   ├── image_uploader.class.php
│   └── model/
│       ├── admin_article_model.php  ← vestige — remplacé par src/core/component_model.php
│       └── config_model.php         ← doublon de src/model/config_model.php — à clarifier
├── config_admin.php
├── index.php
├── login.php / login.class.php
├── register.php / register.class.php
└── save_article.php      ← vestige racine admin — doublon api/v2/
```

**Fichiers partagés front/admin (dans `src/`) :**
- `src/core/component_model.php` — CRUD générique, utilisé par les API articles
- `src/core/page_model.php` — CRUD pages
- `src/core/block_registry.php` — validation des blocs
- `src/model/config_model.php` — source unique pour les langues et la config
- `src/utils/json_handler.php` — lecture/écriture JSON atomique

> `admin/api/v2/` sera migré vers `admin/api/` en session 5. L'ancienne `admin/api/` sera supprimée.

---

## Configuration — `config_admin.php`

Responsabilités :
- Inclure `config.php` (socle front — chemins, modèles, langues, menus)
- Définir les chemins propres à l'admin (`ADMIN_PATH`, `JSON_PAGES_DIR`, `JSON_ARTICLES_DIR`, `GALLERIES_DIR`)
- Déclarer la whitelist des pages admin (`ADMIN_PAGES`)
- Configurer la session PHP
- Définir les limites et types d'upload autorisés

**Convention** : tout fichier admin charge `config_admin.php` — jamais `config.php` directement.

### Constantes exposées

| Constante | Valeur |
|---|---|
| `ADMIN_PATH` | Chemin absolu vers `/admin/` |
| `JSON_PAGES_DIR` | `DIR_JSON . 'pages/'` |
| `JSON_ARTICLES_DIR` | `DIR_JSON . 'articles/'` |
| `GALLERIES_DIR` | `DIR_IMG_CONTENT . 'galleries/'` |
| `ADMIN_PAGES` | `['dashboard', 'pages', 'articles']` |
| `SESSION_LIFETIME` | `3600` |
| `UPLOAD_MAX_SIZE` | `2 Mo` |
| `UPLOAD_ALLOWED_TYPES` | `jpeg, png, webp` |

---

## Modèles

### `ConfigModel` — `src/model/config_model.php`
Partagé avec le front. Utilisable dans les deux contextes — `ROOT_PATH` est défini avant le chargement.

- Pattern cache statique — `loadConfig()` ne lit le fichier qu'une fois
- `getLangs()` → `[['code' => 'fr', 'label' => 'Français'], ...]`
- `getDefaultLang()` → `$langs[0]['code'] ?? 'fr'`
- `clearCache()` disponible pour les tests

> `admin/src/model/config_model.php` est un doublon — à supprimer, utiliser uniquement `src/model/config_model.php`.

### `ComponentModel` — `src/core/component_model.php`
CRUD générique pour les composants (articles, contacts, tout futur type).

- Constructeur : `new ComponentModel($storageDir, $langs, $componentType)`
- `$langs` attend un tableau de codes : `['fr', 'en']` — produit par `array_column(ConfigModel::getLangs(), 'code')`
- Délègue la validation à `BlockRegistry`
- Sauvegarde atomique via `JsonHandler`

> `admin/src/model/admin_article_model.php` est un vestige — remplacé par `ComponentModel`.

### `PageModel` — `src/core/page_model.php`
CRUD pour les layouts de pages.

- Types de références autorisés : `article_ref`, `gallery_ref`
- Validation du layout à la sauvegarde
- `admin/api/v2/page_model.php` est un vestige — actif uniquement dans `src/core/`

---

## API — `admin/api/v2/`

Tous les endpoints suivent le même contrat :

- Auth vérifiée en tête (`$_SESSION['user']`)
- `Content-Type: application/json` systématique
- Réponse unifiée `['success' => bool, ...]`
- Erreurs explicites avec code HTTP approprié

**Articles**

| Fichier | Méthode | Rôle |
|---|---|---|
| `list_articles.php` | GET | Liste les articles (`?meta=1` pour les métadonnées) |
| `get_article.php` | GET | Charge un article (`?file=nom.json`) |
| `save_article.php` | POST | Crée ou met à jour un article |
| `delete_article.php` | POST | Supprime un article |

**Pages**

| Fichier | Méthode | Rôle |
|---|---|---|
| `list_pages.php` | GET | Liste les layouts de pages |
| `get_page.php` | GET | Charge un layout (`?file=nom.json`) |
| `save_page.php` | POST | Crée ou met à jour un layout |
| `delete_page.php` | POST | Supprime un layout |

---

## Langues

### Structure en vigueur (nouvelle forme)

```php
// ConfigModel::getLangs() retourne :
[
    ['code' => 'fr', 'label' => 'Français'],
    ['code' => 'en', 'label' => 'Anglais']
]
```

### Patterns à utiliser partout

```php
// Extraire les codes
$langKeys = array_column(ConfigModel::getLangs(), 'code');

// Itérer
foreach (ConfigModel::getLangs() as $langue) {
    $langue['code'];
    $langue['label'];
}

// Langue par défaut
$langs[0]['code'] ?? 'fr';
```

> ⚠️ L'ancienne forme (`array_keys`, `foreach ($langs as $code => $label)`) est abandonnée.

---

## JavaScript

### `article_editor.js`
- `SUPPORTED_LANGS` alimenté depuis `data-config` du DOM — produit par `articles.php`
- `API_BASE = 'api/v2/'` — à mettre à jour lors de la migration
- `addEventListener` uniquement — zéro `onclick` inline
- `escapeHtml()` via `textContent` — pas de regex
- Gestion des erreurs sur chaque `fetch` avec `try/catch`

### `page_builder.js`
- Architecture orientée classe — `PageEditor`
- Registry pattern pour les types de blocs (`article_ref`, `gallery_ref`, `ui_component`)
- `window.availableGalleries` injecté par `pages.php` via `json_encode`
- URLs `api/v2/` en dur dans chaque méthode — pas de constante centralisée, à corriger lors de la migration

---

## Sessions

Gérées dans `config_admin.php` — ne pas appeler `session_start()` dans les endpoints ni dans `index.php`.

```php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
session_start();
```

> `index.php` appelle actuellement `session_start()` avant `config_admin.php` — doublon à régler.

---

## Fichiers non lus — à auditer

| Fichier | Priorité |
|---|---|
| `admin/inc/main.php` | Haute — routing des pages admin |
| `src/core/block_registry.php` | Haute — utilisé par `ComponentModel` |
| `admin/pages/contacts.php` | Moyenne |
| `admin/pages/galleries.php` | Moyenne |
| `admin/login.php` / `login.class.php` | Moyenne |
| `admin/js/contact_editor.js` | Basse |

---

## Ce qui a été fait

### Session 4 — 2026-05-08

- Audit complet de `config_admin.php` — version corrigée produite
- Correction langue `code`/`label` dans les 4 endpoints API articles et `articles.php`
- Audit `config_model.php` — fallback ancienne forme identifié
- Audit `article_editor.js` — conforme, rien à corriger
- Lecture de l'ensemble des fichiers admin soumis
- Cartographie complète de l'arborescence — vestiges et doublons identifiés
- Création de ce fichier de documentation

---

## Ce qu'il reste à faire

### Court terme

- [ ] Corriger `delete_article.php` — `array_keys` → `array_column`
- [ ] Corriger `config_model.php` — fallback `$langs` ancienne forme (ligne 20) + docblock `getLangs()`
- [ ] Appliquer la version corrigée de `config_admin.php`
- [ ] Régler le double `session_start()` entre `index.php` et `config_admin.php`

### Migration v2 → racine (session 5)

- [ ] Supprimer les `session_start()` en dur dans les 5 endpoints articles + pages
- [ ] Supprimer les `require_once config_model.php` redondants dans les endpoints articles
- [ ] `API_BASE = 'api/v2/'` → `'api/'` dans `article_editor.js`
- [ ] Corriger les URLs `api/v2/` en dur dans `page_builder.js`
- [ ] Mettre à jour les chemins d'include (`__DIR__ . '/../../config_admin.php'` → à recalculer)
- [ ] Supprimer `admin/api/v2/page_model.php` — vestige
- [ ] Supprimer `admin/api/v2/archives/`
- [ ] Supprimer l'ancienne `admin/api/` après validation

### Nettoyage vestiges

- [ ] Supprimer `admin/save_article.php` (racine)
- [ ] Supprimer `admin/src/model/admin_article_model.php`
- [ ] Supprimer `admin/js/article_editor.old.js`, `page_builder copy.js`, `page_builder.old.js`
- [ ] Clarifier `admin/src/model/config_model.php` — doublon de `src/model/config_model.php`

### Moyen terme

- [ ] Auditer `admin/inc/main.php`, `block_registry.php`, pages contacts et galleries
- [ ] Migrer le CSS inline de `showNotification()` vers des classes
- [ ] Sécuriser les uploads — vérification MIME réelle, pas seulement l'extension
- [ ] Module contacts — `json/contacts/` vide, API non migrée en v2
- [ ] Module galleries — `gallery_manager.class.php` et `image_uploader.class.php` à auditer

---

*Dernière mise à jour : session 4 — 2026-05-08*  
*Prochaine session : corrections restantes + migration v2 → racine.*
