<?php
// Désactiver l'affichage d'erreurs parasites qui casseraient le JSON
ini_set('display_errors', 0);
header('Content-Type: application/json');

try {
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);

    if (!$data || !isset($data['filename'])) {
        throw new Exception('Données invalides ou nom de fichier manquant.');
    }

    // Le chemin doit remonter de /admin/api/ vers /json/contacts/
    $directory = '../../json/contacts/';
    
    // Créer le dossier s'il n'existe pas
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    $path = $directory . $data['filename'];

    if (file_put_contents($path, json_encode($data['data'], JSON_PRETTY_PRINT))) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Impossible d\'écrire dans le fichier. Vérifiez les permissions.');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}