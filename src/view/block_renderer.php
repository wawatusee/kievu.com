<?php
function renderBlock($block, $lang)
{
    switch ($block['type']) {
        case 'title':
            echo '<h' . $block['level'] . '>' . htmlspecialchars($block['text'][$lang]) . '</h' . $block['level'] . '>';
            break;
        case 'text':
            echo '<p>' . nl2br(htmlspecialchars($block['content'][$lang])) . '</p>';
            break;
        case 'gallery':
            echo '<div class="gallery" data-ref="' . htmlspecialchars($block['ref']) . '">';
            echo '<p>Gallery: ' . htmlspecialchars($block['ref']) . '</p>';
            echo '</div>';
            break;
        case 'component':
            echo '<div class="component" data-name="' . htmlspecialchars($block['name']) . '">';
            echo '<p>Component: ' . htmlspecialchars($block['name']) . '</p>';
            echo '</div>';
            break;
    }
}
?>