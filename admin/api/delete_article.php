<?php
// Inclusion de ton fichier de config ou d'initialisation pour charger les constantes
require_once '../config_admin.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $filename = $data['filename'] ?? '';

    // Sécurité : basename empêche la manipulation de chemin
    $filename = basename($filename);

    // Utilisation de ta constante globale
    $path = JSON_ARTICLES_DIR . $filename;

    if (file_exists($path)) {
        if (unlink($path)) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Erreur de permission lors de la suppression']);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Fichier introuvable : ' . $filename]);
    }
    exit;
}