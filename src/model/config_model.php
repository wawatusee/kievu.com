<?php
class ConfigModel {
    private static $config = null;

    public static function getConfig() {
        if (self::$config === null) {
            $filePath = __DIR__ . '/../../json/config.json';
            if (!file_exists($filePath)) {
                throw new Exception("Fichier config.json introuvable.");
            }
            $json = file_get_contents($filePath);
            self::$config = json_decode($json, true);
        }
        return self::$config;
    }

    public static function getLangs() {
        $config = self::getConfig();
        return $config['config']['langs'] ?? [];
    }

    public static function getLangKeys() {
        return array_keys(self::getLangs());
    }
}
?>