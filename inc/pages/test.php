<?php
/**
 * Page : test
 * Rendu piloté par json/pages/test.json
 */
require_once ROOT_PATH . 'src/core/page_renderer.php';

$renderer = new PageRenderer(APP_LANG);
$renderer->render('test');