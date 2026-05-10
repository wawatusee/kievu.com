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
│   ├── delete_page.php
│   ├── galleries_api.php
│   ├── get_article.php
│   ├── get_page.php
│   ├── list_articles.php
│   ├── list_pages.php
│   ├── save_article.php
│   └── save_page.php
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
│   └── pages.php
├── src/
│   ├── gallery_manager.class.php
│   ├── image_uploader.class.php
│   └── model/
│       └── admin_article_model.php  ← vestige — à supprimer
├── config_admin.php
├── index.php
├── login.php
└── login.class.php
```

**Fichiers partagés front/admin (dans `src/`) :**
- `src/core/component_model.php` — CRUD générique, utilisé par les API articles
- `src/core/page_model.php` — CRUD pages
- `src/core/block_registry.php` — validation des blocs
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

> `admin/src/model/admin_article_model.php` est un vestige — à supprimer.

### `ComponentModel` — `src/core/component_model.php`
CRUD générique pour les composants (articles, contacts, tout futur type).

- Constructeur : `new ComponentModel($storageDir, $langs, $componentType)`
- `$langs` attend un tableau de codes : `['fr', 'en']` — produit par `array_column(ConfigModel::getLangs(), 'code')`
- Délègue la validation à `BlockRegistry`
- Sauvegarde atomique via `JsonHandler`

### `PageModel` — `src/core/page_model.php`
CRUD pour les layouts de pages.

- Types de références autorisés : `article_ref`, `gallery_ref`
- Validation du layout à la sauvegarde

---

## API — `admin/api/`

Tous les endpoints suivent le même contrat :

- `config_admin.php` chargé en **première ligne**
- Auth vérifiée immédiatement après (`$_SESSION['user']`)
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

## Module contacts — décision de fermeture

Le module contacts est fermé. Les coordonnées de contact sont des articles comme les autres — `contact-coordonnees.json` fonctionne via `ArticleRenderer` avec des blocs `text` et `link`. Le footer et la page contact le consomment sans friction.

**Supprimé :**
- `json/contacts/`
- `admin/api/get_contacts_list.php` et `save_contact.php`
- `admin/pages/contacts.php`
- `admin/js/contact_editor.js`

**Non concerné :** `ADMIN_PAGES` ne contenait pas `contacts` — aucune modification nécessaire.

**Exception future :** si un formulaire d'envoi de message est envisagé, il nécessitera un composant dédié avec traitement PHP — hors périmètre actuel.

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

## Sessions

Gérées exclusivement dans `config_admin.php` :

```php
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_samesite', 'Strict');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
```

**Règles :**
- `config_admin.php` toujours en première ligne de chaque fichier admin
- Ne jamais appeler `session_start()` dans les endpoints ou pages
- `login.class.php` utilise `session_status() === PHP_SESSION_NONE` pour éviter le double démarrage

---

## JavaScript

### `article_editor.js`
- `SUPPORTED_LANGS` alimenté depuis `data-config` du DOM — produit par `articles.php`
- `API_BASE = 'api/'` — mis à jour lors de la migration session 5
- `addEventListener` uniquement — zéro `onclick` inline
- `escapeHtml()` via `textContent` — pas de regex
- Gestion des erreurs sur chaque `fetch` avec `try/catch`

### `page_builder.js`
- Architecture orientée classe — `PageEditor`
- Registry pattern pour les types de blocs (`article_ref`, `gallery_ref`, `ui_component`)
- `window.availableGalleries` injecté par `pages.php` via `json_encode`
- URLs `api/` — mis à jour lors de la migration session 5

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

### Session 5 — 2026-05-10

- `config_admin.php` corrigé — `config.php` inclus, session centralisée, chemins dérivés de `DIR_*`
- `config_model.php` corrigé — `$config` statique supprimé, `clearCache()` nettoyé, `getTitle()` ajouté
- Migration langue `delete_article.php` — `array_keys` → `array_column`
- Résolution double `session_start()` — `session_status()` dans `config_admin.php` et `login.class.php`
- Correction ordre `config_admin.php` / vérification session dans `index.php` et tous les endpoints
- Suppression `admin/src/model/config_model.php` — doublon
- Suppression `require_once config_model.php` redondants dans les endpoints
- Migration `admin/api/v2/` → `admin/api/` — chemins d'include mis à jour
- `API_BASE` mis à jour dans `article_editor.js` et `page_builder.js`
- Suppression `admin/api/v2/` et `archives/`
- Tests complets articles et pages — get, save, delete ✅
- Fermeture module contacts — suppression API, page, JS, json/contacts/

---

## Ce qu'il reste à faire

### Nettoyage vestiges

- [x] `admin/src/model/admin_article_model.php` supprimé
- [x] Tests déplacés dans `admin/tests/` — audit prévu après les révisions en cours
- [ ] Nettoyer `admin/pages/galleries.php` — bloc session commenté

### Moyen terme

- [ ] Auditer `admin/pages/galleries.php` et les classes `gallery_manager` / `image_uploader`
- [ ] Migrer le CSS inline de `showNotification()` vers des classes
- [ ] Sécuriser les uploads — vérification MIME réelle, pas seulement l'extension
- [ ] `login.php` — nettoyer le HTML, passer en français

---

*Dernière mise à jour : session 5 — 2026-05-10*  
*Prochaine session : nettoyage vestiges + audit galleries.*

---

## Ambitions — pistes ouvertes

### Routing automatique
Aujourd'hui chaque page nécessite deux fichiers : `json/pages/{page}.json` (admin) et `inc/pages/{page}.php` (front). Piste : fallback automatique dans `inc/main.php` — si aucun fichier `.php` dédié n'existe, `PageRenderer` prend le relais. Les fichiers PHP resteraient optionnels, réservés aux pages avec logique spécifique.

### Éditeur de menus
`menus.json` est édité à la main. Un éditeur admin permettrait de créer une page et l'ajouter au menu en une seule opération — cohérence garantie entre navigation et contenu.

### Types de pages
- Pages pilotées par JSON — modèle actuel via `PageRenderer`
- Pages statiques PHP — pour les cas avec logique spécifique
- Les deux peuvent coexister avec le fallback routing

### Brouillons
`status: draft` est déjà présent dans le modèle JSON des pages et articles. Il manque la logique qui l'exploite côté front — ne pas rendre une page ou un article en `draft`. L'admin pourrait filtrer l'affichage par statut.

> Ces pistes sont ouvertes — à trancher quand le socle est stabilisé.

---

## Tests — `admin/tests/`

Fichiers déplacés depuis la racine admin en session 5. À auditer après les révisions en cours.

| Fichier | Composant testé |
|---|---|
| `test_api_v2.php` | Endpoints API |
| `test_audit.php` | Audit général |
| `test_block_registry.php` | `BlockRegistry` |
| `test_component_model.php` | `ComponentModel` |
| `test_json_handler.php` | `JsonHandler` |
