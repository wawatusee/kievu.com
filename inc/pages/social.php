<?php
/**
 * Page : Home
 * Rendu piloté par json/pages/social.json
 */

require_once ROOT_PATH . 'src/core/page_renderer.php';

// Langue courante (à adapter selon ton système de langue)
$lang = $_SESSION['lang'] ?? 'fr';

$renderer = new PageRenderer($lang);
$renderer->render('social');