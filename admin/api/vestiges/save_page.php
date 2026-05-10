<?php
require_once __DIR__ . '/../config_admin.php';

header('Content-Type: application/json');

// Vérification de la méthode
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
    exit;
}

// Récupération des données JSON envoyées par le JS
$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data || !isset($data['filename'])) {
    echo json_encode(['success' => false, 'error' => 'Données invalides']);
    exit;
}

// Nettoyage du nom de fichier
$filename = basename($data['filename']);
if (strpos($filename, '.json') === false) {
    $filename .= '.json';
}

// Chemin complet vers le dossier des pages
$file_path = JSON_PAGES_DIR . $filename;

// On prépare le contenu à enregistrer (titre + structure des blocs)
$content_to_save = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

// Tentative d'écriture
if (file_put_contents($file_path, $content_to_save)) {
    echo json_encode(['success' => true, 'message' => 'Page enregistrée avec succès']);
} else {
    echo json_encode(['success' => false, 'error' => 'Impossible d\'écrire dans ' . JSON_PAGES_DIR]);
}