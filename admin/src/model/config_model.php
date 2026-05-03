<?php
/**
 * ConfigModel - Gestion de la configuration globale
 * Nucleus CMS - Session 2
 */

class ConfigModel
{
    private static ?array $langs = null;
    private static ?array $config = null;

    /**
     * Récupère les langues disponibles
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
     * Récupère la langue par défaut
     * @return string Code langue (ex: 'fr')
     */
    public static function getDefaultLang(): string
    {
        $langs = self::getLangs();
        return array_key_first($langs) ?? 'fr';
    }

    /**
     * Charge la configuration depuis le JSON
     */
    private static function loadConfig(): void
    {
        $configPath = ROOT_PATH . 'json/config.json';
//$configPath = '../../json/config.json';
        // DEBUG - À retirer après
        error_log('ConfigModel: Chemin = ' . $configPath);
        error_log('ConfigModel: Existe = ' . (file_exists($configPath) ? 'OUI' : 'NON'));

        if (!file_exists($configPath)) {
            self::$langs = ['fr' => 'Français', 'en' => 'English'];
            return;
        }

        $content = file_get_contents($configPath);
        $data = json_decode($content, true);

        // DEBUG - À retirer après
        error_log('ConfigModel: langs brut = ' . print_r($data['langs'], true));

        self::$langs = [];

        if (isset($data['langs']) && is_array($data['langs'])) {
            foreach ($data['langs'] as $langItem) {
                foreach ($langItem as $code => $label) {
                    self::$langs[$code] = $label;
                }
            }
        }

        // DEBUG - À retirer après
        error_log('ConfigModel: langs final = ' . print_r(self::$langs, true));

        if (empty(self::$langs)) {
            self::$langs = ['fr' => 'Français', 'en' => 'English'];
        }
    }

    /**
     * Reset le cache (utile pour tests)
     */
    public static function clearCache(): void
    {
        self::$langs = null;
        self::$config = null;
    }
}