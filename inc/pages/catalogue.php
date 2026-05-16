<?php
/**
 * Page : catalogue
 * Rendu piloté par json/pages/catalogue.json
 */
require_once ROOT_PATH . 'src/core/page_renderer.php';

$renderer = new PageRenderer(APP_LANG);
$renderer->render('catalogue');