<?php
/**
 * Page : services
 * Rendu piloté par json/pages/test-menu.json
 */
require_once ROOT_PATH . 'src/core/page_renderer.php';

$renderer = new PageRenderer(APP_LANG);
$renderer->render('test-menu');