<?php
class PageController
{
    private $configModel;

    public function __construct($configModel)
    {
        $this->configModel = $configModel;
    }

    public function renderPage($pageId, $lang = 'fr')
    {
        $page = $this->loadPage($pageId);
        if (!$page) {
            http_response_code(404);
            echo "Page non trouvée.";
            return;
        }

        // Charger les vues
        require_once SRC_PATH . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'page_view.php';
        renderPageView($page, $lang, $this->configModel);
    }

    private function loadPage($pageId)
    {
        $filePath = JSON_PATH . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . basename($pageId) . '.json';

        if (!file_exists($filePath)) {
            return null;
        }

        $json = file_get_contents($filePath);
        return json_decode($json, true);
    }
}
?>