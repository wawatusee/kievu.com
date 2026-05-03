<?php
require_once '../config_admin.php';

header('Content-Type: application/json');

// Récupération du flux JSON envoyé par le JS
$jsonInput = file_get_contents('php://input');
$data = json_decode($jsonInput, true);

if (!$data || !isset($data['meta']['id'])) {
    echo json_encode(['success' => false, 'error' => 'Données invalides']);
    exit;
}

// Construction du nom de fichier
$filename = $data['meta']['id'] . '.json';
$filepath = JSON_ARTICLES_DIR . $filename;

// Écriture du fichier (avec formatage pour que le JSON soit lisible)
$success = file_put_contents($filepath, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

if ($success !== false) {
    echo json_encode(['success' => true, 'file' => $filename]);
} else {
    echo json_encode(['success' => false, 'error' => 'Impossible d\'écrire le fichier']);
}