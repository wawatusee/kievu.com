<?php
/**
 * API v2 - Liste des articles
 */

session_start();

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifié']);
    exit;
}

require_once __DIR__ . '/../../config_admin.php';
require_once ROOT_PATH . 'src/core/component_model.php';

header('Content-Type: application/json; charset=utf-8');

$withMeta = isset($_GET['meta']) && $_GET['meta'] === '1';

$langs = ['fr', 'en'];
$model = new ComponentModel(JSON_ARTICLES_DIR, $langs, 'article');

$list = $model->listAll($withMeta);

echo json_encode($list);