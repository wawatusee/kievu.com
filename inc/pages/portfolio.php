<?php
/**
 * Page : portfolio
 * Rendu piloté par json/pages/portfolio.json
 */
require_once ROOT_PATH . 'src/core/page_renderer.php';

$renderer = new PageRenderer(APP_LANG);
$renderer->render('portfolio');