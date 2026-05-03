<?php
class AdminArticleModel {
    private string $storagePath;

    public function __construct() {
        // Chemin basé sur l'arborescence fournie 
        $this->storagePath = __DIR__ . '/../../json/articles/';
    }

    /**
     * Génère un slug propre pour le nom de fichier
     */
    public function generateId(string $title): string {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
        return $slug . '-' . bin2hex(random_bytes(2)); // Ajout d'un suffixe pour l'unicité
    }

    /**
     * Initialise un nouvel article vide selon le Manifeste
     */
    public function createEmpty(string $titleFr): array {
        $id = $this->generateId($titleFr);
        return [
            "type" => "article",
            "meta" => [
                "id" => $id,
                "author" => $_SESSION['user'] ?? 'admin',
                "created" => date('Y-m-d'),
                "status" => "draft"
            ],
            "content" => [] 
        ];
    }
}