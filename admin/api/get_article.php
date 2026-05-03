<?php
// On remonte de deux niveaux pour atteindre admin/config_admin.php
require_once '../config_admin.php';

header('Content-Type: application/json');

if (isset($_GET['file'])) {
    // Nettoyage du nom de fichier pour la sécurité
    $filename = basename($_GET['file']);
    $path = JSON_ARTICLES_DIR . $filename;

    if (file_exists($path)) {
        echo file_get_contents($path);
        exit;
    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Fichier introuvable sur le disque : ' . $filename]);
        exit;
    }
}

http_response_code(400);
echo json_encode(['error' => 'Paramètre fichier manquant']);