<?php


// On récupère la liste des articles existants pour le menu latéral ou la gestion
$articlesDir = JSON_ARTICLES_DIR;
$existingArticles = array_diff(scandir($articlesDir), array('..', '.'));

?>


<div class="admin-editor-container">
    <aside class="admin-sidebar">
        <h4>Articles existants</h4>
        <div class="sidebar-actions" style="margin-bottom: 20px;">
            <button type="button" id="new-article-btn" class="btn-primary" style="width: 100%;">
                + Nouvel Article
            </button>
        </div>
        <ul>
            <?php foreach ($existingArticles as $file): ?>
                <li class="sidebar-item">
                    <div class="item-main">
                        <a href="#" class="load-article-link" data-filename="<?= $file ?>">
                            <?= str_replace('.json', '', $file) ?>
                        </a>
                    </div>
                    <div class="item-actions">
                        <button type="button" class="btn-delete-file" data-filename="<?= $file ?>"
                            title="Supprimer définitivement">
                            🗑️
                        </button>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </aside>

    <main class="admin-content">

        <?php
        // Inclusion explicite selon vos règles 
        if (defined('ADMIN_PATH')) {
            require_once ADMIN_PATH . 'src/model/config_model.php';
        } else {
            // Fallback ou erreur explicite
            die("Erreur système : ADMIN_PATH non définie.");
        }
        // Utilisation du modèle pour récupérer les langues configurées
        $langs = ConfigModel::getLangs(); // Récupère { "fr": "Français", ... }
        $langKeys = array_keys($langs);
        ?>
        <div class="lang-tabs-wrapper">
            <nav class="lang-tabs-container" id="editor-langs" data-config='<?= json_encode($langKeys) ?>'>

                <?php foreach ($langs as $code => $label): ?>
                    <button type="button" class="tab-btn <?= $code === 'fr' ? 'active' : '' ?>" data-lang="<?= $code ?>"
                        onclick="switchEditorLang('<?= $code ?>')">
                        <?= $label ?>
                    </button>
                <?php endforeach; ?>
            </nav>
        </div>

        <form id="article-builder">
            <section class="meta-section">
                <input type="text" id="article-title" placeholder="Titre de l'article..." class="main-title-input">
                <p class="id-preview">ID : <span id="generated-id">--</span></p>
            </section>

            <div id="blocks-workspace"></div>

            <div class="editor-actions-bar">
                <div class="add-block-controls">
                    <select id="new-block-type">
                        <option value="text">Paragraphe</option>
                        <option value="title">Titre (H2)</option>
                        <option value="list">Liste à puces</option>
                        <option value="link">Lien / Bouton</option>
                    </select>
                    <button type="button" id="add-block-trigger" class="btn-secondary">
                        + Ajouter un bloc
                    </button>
                </div>

                <div class="save-controls">
                    <button type="button" id="save-article-btn" class="btn-save">
                        Enregistrer l'Article
                    </button>
                </div>
            </div>
        </form>
    </main>
</div>

<script src="js/article_editor.js" defer></script>