<main>
    <h1><?= htmlspecialchars($page['meta']['title'][$lang]) ?></h1>
    <?php
    // Charger le renderer de blocs
    $root = dirname(__DIR__, 1); // remonte de 1 niveau : inc → racine
    $blockRendererPath = $root . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'view' . DIRECTORY_SEPARATOR . 'block_renderer.php';

    if (!file_exists($blockRendererPath)) {
        die("Fichier block_renderer.php introuvable : " . $blockRendererPath);
    }

    require_once $blockRendererPath;

    foreach ($page['content'] as $block) {
        renderBlock($block, $lang);
    }
    ?>
</main>