<?php
class PageModel {
    public static function getPage($pageId) {
        $filePath = __DIR__ . '/../../json/pages/' . basename($pageId) . '.json';
        if (!file_exists($filePath)) {
            return null;
        }
        $json = file_get_contents($filePath);
        return json_decode($json, true);
    }

    public static function getPagesList() {
        $dir = __DIR__ . '/../../json/pages/';
        $files = array_diff(scandir($dir), ['..', '.']);
        return array_map(function($file) {
            return str_replace('.json', '', $file);
        }, $files);
    }
}
?>