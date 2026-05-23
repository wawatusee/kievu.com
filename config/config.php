<?php
// =========================================================
// CHEMINS ABSOLUS (serveur)
// =========================================================
define('ROOT_PATH', realpath(__DIR__ . '/../') . DIRECTORY_SEPARATOR);
define('DIR_JSON', ROOT_PATH . 'json/');
define('DIR_SRC', ROOT_PATH . 'src/');
define('DIR_INC', ROOT_PATH . 'inc/');
define('DIR_IMG', ROOT_PATH . 'public/img/');
define('DIR_IMG_CONTENT', DIR_IMG . 'content/');
define('DIR_IMG_DECO', DIR_IMG . 'deco/');

// =========================================================
// CHEMINS PUBLICS (navigateur)
// =========================================================
if (!defined('BASE_PATH')) {
    require_once __DIR__ . '/env.php';
}

define('PUBLIC_PATH', BASE_PATH . '/public/');
define('PUBLIC_IMG', PUBLIC_PATH . 'img/');
define('PUBLIC_IMG_CONTENT', PUBLIC_IMG . 'content/');

// =========================================================
// MODÈLES
// =========================================================
require_once DIR_SRC . 'model/config_model.php';
require_once DIR_SRC . 'model/menus_model.php';

// =========================================================
// CONFIGURATION DU SITE
// =========================================================
$str_titleWebSite = ConfigModel::getTitle();

// =========================================================
// LANGUES (disponibles uniquement — la détection sort d'ici)
// =========================================================
$langs = ConfigModel::getLangs();
$defaultLang = ConfigModel::getDefaultLang();
define('LANG_DEFAULT', $defaultLang);

// =========================================================
// MENUS
// =========================================================
$menus = new MenusModel(DIR_JSON . 'menus.json');
$menuMain = $menus->getMenu('Main_menu');
$menuRS = $menus->getMenu('RS_menu');
$pagesDuMenus = array_column((array) $menuMain, 'page');
define('PAGE_ARRAY', $pagesDuMenus);