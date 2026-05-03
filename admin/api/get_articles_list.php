<?php
require_once __DIR__ . '/../config_admin.php';
// Scanne le dossier des articles et renvoie le JSON
$files = array_diff(scandir(JSON_ARTICLES_DIR), array('..', '.'));
echo json_encode(array_values(filter_articles($files))); // Filtre pour ne garder que .json

function filter_articles($array) {
    return array_filter($array, function($f) { return str_contains($f, '.json'); });
}