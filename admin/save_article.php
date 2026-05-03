<?php
// Inclusion explicite selon le manifeste 
require_once __DIR__ . '/../src/utils/json_loader.php';
require_once __DIR__ . '/src/model/admin_article_model.php';

header('Content-Type: application/json');

// Vérification de la session (Sécurité) 
session_start();
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Accès refusé']);
    exit;
}

// Récupération du JSON envoyé par Fetch
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['meta']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Données JSON invalides']);
    exit;
}

try {
    $adminModel = new AdminArticleModel();
    $fileName = basename($data['meta']['id']) . '.json'; // Protection basename 
    $path = __DIR__ . '/../json/articles/' . $fileName;

    // Utilisation de votre utilitaire pour sauvegarder 
    JsonLoader::save($path, $data);

    echo json_encode(['success' => true, 'id' => $data['meta']['id']]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}