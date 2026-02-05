<?php
// Définir les constantes de chemin
define('ROOT_PATH', dirname(__DIR__));
define('PUBLIC_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'public');
define('SRC_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'src');
define('INC_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'inc');
define('JSON_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'json');
define('ADMIN_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'admin');

// Démarrer la session si besoin (pour l'admin plus tard)
// session_start();

// Charger ConfigModel
require_once SRC_PATH . DIRECTORY_SEPARATOR . 'model' . DIRECTORY_SEPARATOR . 'config_model.php';

// Instancier ConfigModel avec le chemin du JSON
$configModel = new ConfigModel(JSON_PATH . DIRECTORY_SEPARATOR . 'config.json'); // ✅ On donne le chemin ici

// Charger le contrôleur de page
require_once SRC_PATH . DIRECTORY_SEPARATOR . 'controller' . DIRECTORY_SEPARATOR . 'page_controller.php';

$pageController = new PageController($configModel); // ✅ On passe la config au contrôleur

$lang = $_GET['lang'] ?? 'fr';
if (!in_array($lang, ['fr', 'en', 'nl'])) {
    $lang = 'fr';
}

$pageId = $_GET['page'] ?? 'home';

$pageController->renderPage($pageId, $lang);
?>