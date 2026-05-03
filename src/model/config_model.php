<?php
/**
 * ConfigModel - Gestion de la configuration globale
 * Nucleus CMS
 * 
 * Utilisable en contexte admin (ROOT_PATH défini par config_admin.php)
 * et en contexte public (ROOT_PATH défini par config/config.php)
 */

class ConfigModel
{
    private static ?array $langs = null;
    private static ?array $config = null;

    // =========================================================
    // ACCÈS À LA CONFIG BRUTE
    // =========================================================

    private static function loadConfig(): void
    {
        $configPath = ROOT_PATH . 'json/config.json';

        if (!file_exists($configPath)) {
            self::$config = [];
            self::$langs = ['fr' => 'Français'];
            return;
        }

        $content = file_get_contents($configPath);
        self::$config = json_decode($content, true) ?? [];

        // Construction du tableau de langues [['code' => 'fr', 'label' => 'Français']]
        self::$langs = [];
        if (isset(self::$config['langs']) && is_array(self::$config['langs'])) {
            foreach (self::$config['langs'] as $langItem) {
                if (isset($langItem['code'], $langItem['label'])) {
                    self::$langs[] = [
                        'code' => $langItem['code'],
                        'label' => $langItem['label']
                    ];
                }
            }
        }

        if (empty(self::$langs)) {
            self::$langs = [['code' => 'fr', 'label' => 'Français']];
        }
    }

    private static function getConfig(): array
    {
        if (self::$config === null) {
            self::loadConfig();
        }
        return self::$config;
    }

    // =========================================================
    // LANGUES — utilisé par l'admin et le public
    // =========================================================

    /**
     * Retourne les langues disponibles
     * @return array ['code' => 'Label', ...]
     */
    public static function getLangs(): array
    {
        if (self::$langs === null) {
            self::loadConfig();
        }
        return self::$langs;
    }

    /**
     * Retourne la langue par défaut (première de la liste)
     */
    public static function getDefaultLang(): string
    {
        $langs = self::getLangs();
        return $langs[0]['code'] ?? 'fr';
    }

    // =========================================================
    // SITE — utilisé par le public
    // =========================================================

    /**
     * Retourne le titre du site
     * config.json : "titleWebsite": ["mascarade", "-bdx", ".fr"]
     */
    public static function getTitle(): string
    {
        $cfg = self::getConfig();
        $parts = $cfg['titleWebsite'] ?? ['Site'];
        return is_array($parts) ? implode('', $parts) : (string) $parts;
    }


    // =========================================================
    // UTILITAIRES
    // =========================================================

    /**
     * Reset du cache (utile pour les tests)
     */
    public static function clearCache(): void
    {
        self::$langs = null;
        self::$config = null;
    }
}
