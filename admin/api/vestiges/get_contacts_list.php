<?php
header('Content-Type: application/json');

// Chemin vers le dossier des contacts
$directory = '../../json/contacts/';

// Créer le dossier s'il n'existe pas encore
if (!is_dir($directory)) {
    mkdir($directory, 0777, true);
}

// Scanner le dossier et filtrer uniquement les fichiers .json
$files = scandir($directory);
$contacts = array_filter($files, function($file) {
    return strpos($file, '.json') !== false;
});

// Réorganiser les index du tableau et renvoyer en JSON
echo json_encode(array_values($contacts));