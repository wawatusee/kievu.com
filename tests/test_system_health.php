<?php
/**
 * tests/test_system_health.php
 * Test de santé global — chemins, JSON, constantes, modèles
 * Lancer depuis la racine : php tests/test_system_health.php
 * Ou depuis le navigateur en dev (à retirer en prod)
 */

require_once __DIR__ . '/../config/config.php';

$ok = 0;
$fail = 0;

function assert_test(string $label, bool $condition, string $detail = ''): void
{
    global $ok, $fail;
    if ($condition) {
        echo "✓ {$label}\n";
        $ok++;
    } else {
        echo "✗ {$label}" . ($detail ? " — {$detail}" : '') . "\n";
        $fail++;
    }
}

echo "=== NUCLEUS — Test de santé système ===\n\n";

// =========================================================
// CONSTANTES
// =========================================================
echo "--- Constantes ---\n";

assert_test('ROOT_PATH défini', defined('ROOT_PATH'));
assert_test('DIR_JSON défini', defined('DIR_JSON'));
assert_test('DIR_IMG défini', defined('DIR_IMG'));
assert_test('DIR_IMG_CONTENT défini', defined('DIR_IMG_CONTENT'));
assert_test('DIR_IMG_DECO défini', defined('DIR_IMG_DECO'));
assert_test('PUBLIC_PATH défini', defined('PUBLIC_PATH'));
assert_test('PUBLIC_IMG défini', defined('PUBLIC_IMG'));
assert_test('PUBLIC_IMG_CONTENT défini', defined('PUBLIC_IMG_CONTENT'));
assert_test('LANG_DEFAULT défini', defined('LANG_DEFAULT'));
assert_test('PAGE_ARRAY défini', defined('PAGE_ARRAY'));

// =========================================================
// CHEMINS SERVEUR
// =========================================================
echo "\n--- Chemins serveur ---\n";

assert_test('ROOT_PATH existe', is_dir(ROOT_PATH), ROOT_PATH);
assert_test('DIR_JSON existe', is_dir(DIR_JSON), DIR_JSON);
assert_test('DIR_IMG existe', is_dir(DIR_IMG), DIR_IMG);
assert_test('DIR_IMG_CONTENT existe', is_dir(DIR_IMG_CONTENT), DIR_IMG_CONTENT);
assert_test('DIR_IMG_DECO existe', is_dir(DIR_IMG_DECO), DIR_IMG_DECO);

// =========================================================
// FICHIERS JSON CRITIQUES
// =========================================================
echo "\n--- Fichiers JSON critiques ---\n";

assert_test('config.json existe', file_exists(ROOT_PATH . 'config/config.json'));
assert_test('menus.json existe', file_exists(DIR_JSON . 'menus.json'));

$pagesDir = DIR_JSON . 'pages/';
$articlesDir = DIR_JSON . 'articles/';
$galleriesDir = DIR_JSON . 'galleries/';

assert_test('json/pages/ existe', is_dir($pagesDir), $pagesDir);
assert_test('json/articles/ existe', is_dir($articlesDir), $articlesDir);
assert_test('json/galleries/ existe', is_dir($galleriesDir), $galleriesDir);

// =========================================================
// MODÈLES
// =========================================================
echo "\n--- Modèles ---\n";

$langs = ConfigModel::getLangs();
assert_test('ConfigModel::getLangs() retourne un array', is_array($langs));
assert_test('Au moins une langue disponible', count($langs) > 0);
assert_test('Première langue a code et label', isset($langs[0]['code'], $langs[0]['label']));
assert_test('LANG_DEFAULT correspond à langs[0]', LANG_DEFAULT === $langs[0]['code']);

$menus = new MenusModel(DIR_JSON . 'menus.json');
$mainMenu = $menus->getMenu('Main_menu');
assert_test('MenusModel charge Main_menu', is_array($mainMenu));
assert_test('Main_menu contient au moins une page', count($mainMenu) > 0);
assert_test('PAGE_ARRAY non vide', count(PAGE_ARRAY) > 0);

// =========================================================
// PAGES
// =========================================================
echo "\n--- Pages ---\n";

foreach (PAGE_ARRAY as $pageId) {
    $jsonExists = file_exists($pagesDir . $pageId . '.json');
    $phpExists = file_exists(ROOT_PATH . 'inc/pages/' . $pageId . '.php');
    assert_test(
        "Page '{$pageId}' — JSON ou PHP présent",
        $jsonExists || $phpExists,
        $jsonExists ? 'JSON ✓' : 'JSON ✗' . ' / ' . ($phpExists ? 'PHP ✓' : 'PHP ✗')
    );
}

// =========================================================
// ASSETS PUBLICS
// =========================================================
echo "\n--- Assets publics ---\n";

assert_test('logo.svg existe', file_exists(DIR_IMG_DECO . 'logo.svg'));
assert_test('style.css existe', file_exists(ROOT_PATH . 'public/css/style.css'));
assert_test('header.css existe', file_exists(ROOT_PATH . 'public/css/header.css'));
assert_test('main.css existe', file_exists(ROOT_PATH . 'public/css/main.css'));
assert_test('footer.css existe', file_exists(ROOT_PATH . 'public/css/footer.css'));
assert_test('menu.js existe', file_exists(ROOT_PATH . 'public/js/menu.js'));
assert_test('lightbox.js existe', file_exists(ROOT_PATH . 'public/js/lightbox.js'));

// =========================================================
// PUBLIC_PATH — cohérence navigateur
// =========================================================
echo "\n--- Chemins navigateur ---\n";

assert_test('PUBLIC_PATH commence par /', str_starts_with(PUBLIC_PATH, '/'));
assert_test('PUBLIC_PATH finit par /', str_ends_with(PUBLIC_PATH, '/'));
assert_test('PUBLIC_IMG_CONTENT non vide', !empty(PUBLIC_IMG_CONTENT));
assert_test(
    'Pas de backslash dans PUBLIC_PATH',
    !str_contains(PUBLIC_PATH, '\\'),
    PUBLIC_PATH
);

// =========================================================
// RÉSUMÉ
// =========================================================
echo "\n=== {$ok} passed, {$fail} failed ===\n";