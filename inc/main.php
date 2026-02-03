<main>
    <h1><?= htmlspecialchars($page['meta']['title']['fr']) ?></h1>
    <?php
    foreach ($page['content'] as $block) {
        renderBlock($block, 'fr'); // TODO: gérer la langue dynamique
    }
    ?>
</main>