<?php
class ConfigModel
{
    private $config = null;
    private $filePath;

    /**
     * Constructeur : charge la config depuis un fichier JSON
     */
    public function __construct($filePath = null)
    {
        $this->filePath = $filePath ?? JSON_PATH . DIRECTORY_SEPARATOR . 'config.json';

        if (!file_exists($this->filePath)) {
            throw new Exception("Fichier config introuvable : " . $this->filePath);
        }

        $json = file_get_contents($this->filePath);
        $this->config = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("Erreur JSON dans " . $this->filePath . " : " . json_last_error_msg());
        }

        if (!isset($this->config['config']) || !is_array($this->config['config'])) {
            throw new Exception("Structure invalide dans " . $this->filePath . " : clé 'config' manquante ou non-array.");
        }
    }

    /**
     * Retourne la configuration complète
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Retourne les langues configurées
     */
    public function getLangs()
    {
        return $this->config['config']['langs'] ?? [];
    }

    /**
     * Retourne les clés des langues
     */
    public function getLangKeys()
    {
        return array_keys($this->getLangs());
    }

    /**
     * Retourne le titre du site
     */
    public function getSiteTitle()
    {
        return $this->config['config']['titleWebSite'][0] ?? 'KIEVU.COM';
    }

    /**
     * Retourne une valeur par clé (ex: config.config.langs.fr)
     */
    public function get($key, $default = null)
    {
        $keys = explode('.', $key);
        $value = $this->config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}
?>