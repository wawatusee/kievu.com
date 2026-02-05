<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    // Charger ConfigModel (déjà fait dans index.php)
    // $configModel est disponible

    $title = $page['meta']['title'][$lang] ?? 'Page';
    $siteTitle = $configModel->getSiteTitle();

    // Récupérer l'ID de la page (ex: 'home', 'about')
    $pageId = $page['meta']['id'] ?? 'default';

    // Chemin de la feuille de style de la page
    $pageCssPath = PUBLIC_PATH . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'pages' . DIRECTORY_SEPARATOR . $pageId . '.css';
    $pageCssUrl = '/css/pages/' . $pageId . '.css';
    ?>
    <title><?= htmlspecialchars($title) ?> | <?= htmlspecialchars($siteTitle) ?></title>
    <link rel="stylesheet" href="/css/style.css">
    <?php if (file_exists($pageCssPath)) : ?>
        <link rel="stylesheet" href="<?= htmlspecialchars($pageCssUrl) ?>">
    <?php endif; ?>
</head>
<body>