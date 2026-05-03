<?php
require_once '../config_admin.php';
require_once '../src/gallery_manager.class.php';
require_once '../src/image_uploader.class.php';

header('Content-Type: application/json');

// On récupère l'action et la galerie
$action = $_POST['action'] ?? null;
$galleryName = $_POST['galleryName'] ?? null;

if (!$galleryName || !$action) {
    echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
    exit;
}

try {
    $baseDir = realpath(ROOT_PATH . 'public/img/content/galleries/');
    $mgr = new GalleryManager($baseDir);

    // On récupère le chemin sécurisé (lance une exception si la galerie n'existe pas)
    $galleryPath = $baseDir . DIRECTORY_SEPARATOR . strtoupper(str_replace(' ', '-', $galleryName)) . DIRECTORY_SEPARATOR;

    switch ($action) {
        case 'upload':
            if (!isset($_FILES['images']))
                throw new Exception("Aucun fichier reçu");

            $uploader = new ImageUploader($galleryPath);
            $uploadedFiles = [];

            // Gestion multi-fichiers (boucle)
            foreach ($_FILES['images']['tmp_name'] as $key => $tmpName) {
                $file = [
                    'name' => $_FILES['images']['name'][$key],
                    'tmp_name' => $tmpName,
                    'error' => $_FILES['images']['error'][$key],
                    'size' => $_FILES['images']['size'][$key]
                ];
                $uploadedFiles[] = $uploader->upload($file);
            }

            $mgr->refreshIndex(); // On met à jour le pont JSON
            echo json_encode(['success' => true, 'files' => $uploadedFiles]);
            break;

        case 'list':
            // Liste les images pour l'affichage des miniatures dans l'admin
            $thumbDir = $galleryPath . 'thumbs/';
            $images = glob($thumbDir . "*.{jpg,jpeg,png,gif}", GLOB_BRACE);
            $response = [];
            foreach ($images as $img) {
                $name = basename($img);
                $response[] = [
                    'name' => $name,
                    'url' => '../public/img/content/galleries/' . basename($galleryPath) . '/thumbs/' . $name
                ];
            }
            echo json_encode(['success' => true, 'thumbnails' => $response]);
            break;
        case 'rename':
            $oldName = $_POST['oldName'] ?? null;
            $newName = $_POST['newName'] ?? null;
            if (!$oldName || !$newName)
                throw new Exception("Noms manquants");

            $mgr->renameGallery($oldName, $newName); // Utilise la méthode de ton Manager
            // refreshIndex() est déjà appelé à l'intérieur de renameGallery dans notre version audit
            echo json_encode(['success' => true, 'message' => 'Galerie renommée']);
            break;

        case 'deleteGallery':
            $galleryName = $_POST['galleryName'] ?? null;

            if ($galleryName) {
                // Attention : assure-toi que ta méthode s'appelle bien deleteGallery
                $result = $mgr->deleteGallery($galleryName);
                echo json_encode(['success' => $result]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Nom de galerie manquant']);
            }
            exit;

        case 'delete':
            $galleryName = $_POST['galleryName'] ?? null;
            $filename = $_POST['filename'] ?? null;

            if ($galleryName && $filename) {
                // On passe les deux arguments ici
                $result = $mgr->deleteImage($galleryName, $filename);
                echo json_encode(['success' => $result]);
            } else {
                echo json_encode(['success' => false, 'error' => 'Données manquantes']);
            }
            exit;
        case 'refresh':
            $success = $mgr->refreshIndex();
            echo json_encode([
                'success' => $success,
                'message' => $success ? 'Index rafraîchi' : 'Erreur lors du rafraîchissement'
            ]);
            break;

        default:
            throw new Exception("Action non reconnue");
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}