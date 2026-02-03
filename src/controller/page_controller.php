<?php
class PageController {
    public function renderPage($pageId) {
        $page = $this->loadPage($pageId);
        if (!$page) {
            http_response_code(404);
            echo "Page non trouvée.";
            return;
        }

        require_once __DIR__ . '/../view/page_view.php';
        renderPageView($page);
    }

    private function loadPage($pageId) {
        $filePath = __DIR__ . '/../../json/pages/' . basename($pageId) . '.json';
        if (!file_exists($filePath)) return null;

        $json = file_get_contents($filePath);
        return json_decode($json, true);
    }
}
?>