<?php
/**
 * API v2 - Suppression d'un article
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

$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

$filename = $data['filename'] ?? null;

if (!$filename) {
    echo json_encode(['success' => false, 'error' => 'Nom de fichier manquant']);
    exit;
}

$langs = ['fr', 'en'];
$model = new ComponentModel(JSON_ARTICLES_DIR, $langs, 'article');

$result = $model->delete($filename);

echo json_encode($result);