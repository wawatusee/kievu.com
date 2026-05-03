<?php
// Racine absolue du projet (remonte d'un niveau depuis /admin)
define('ROOT_PATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);

// Racine de l'administration
define('ADMIN_PATH', __DIR__ . DIRECTORY_SEPARATOR);
// Dans config_admin.php
define('GALLERIES_DIR', ROOT_PATH . 'public/img/content/galleries' . DIRECTORY_SEPARATOR);

// Pages accessibles dans l’admin
define('ADMIN_PAGES', [
    'dashboard',
    'pages',
    'articles'
]);

// Dossiers JSON des contenus (Utilisation de ROOT_PATH pour la clarté)
define('JSON_PAGES_DIR', ROOT_PATH . 'json/pages' . DIRECTORY_SEPARATOR);
define('JSON_ARTICLES_DIR', ROOT_PATH . 'json/articles' . DIRECTORY_SEPARATOR);

// Chemin vers le loader et les modèles
define('JSON_LOADER', ROOT_PATH . 'src/utils/json_loader.php');
// Charger automatiquement les outils de base pour toute l'admin
require_once JSON_LOADER;