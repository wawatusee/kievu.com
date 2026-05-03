<?php
// Pas besoin de require config ou head ici, ils sont déjà chargés par index.php
$targetDir = JSON_PAGES_DIR;
$galleriesDir = '../public/img/content/galleries/';
$galleryFolders = [];
if (is_dir($galleriesDir)) {
    // On récupère uniquement les noms des sous-dossiers
    $galleryFolders = array_values(array_diff(scandir($galleriesDir), ['..', '.', 'thumbs', 'original']));
}
// On récupère la liste des fichiers de structure de page
$files = [];
if (is_dir($targetDir)) {
    $files = array_diff(scandir($targetDir), array('..', '.'));
}
?>

<div class="admin-editor-container">
    <aside class="admin-sidebar">
        <h4>Pages du site (Layout)</h4>
        <ul id="file-list">
            <?php foreach ($files as $file):
                if (str_contains($file, '.json')): ?>
                    <li class="sidebar-item">
                        <div class="item-main">
                            <a href="#" class="load-page-link" data-filename="<?= $file ?>">
                                <?= str_replace('.json', '', $file) ?>
                            </a>
                        </div>
                        <div class="item-actions">
                            <button type="button" class="btn-delete-file" data-filename="<?= $file ?>"
                                title="Supprimer le layout">
                                🗑️
                            </button>
                        </div>
                    </li>
                <?php endif; endforeach; ?>
        </ul>
        <button id="btn-new-page" class="btn-secondary" style="width:100%; margin-top:15px;">+ Nouveau Layout
            Page</button>
    </aside>

    <section id="builder-workspace">
        <input type="text" id="page-title" class="main-title-input" placeholder="Nom de la page (ex: contact)">
        <p class="id-preview-container">Fichier : <span id="generated-filename">nouveau.json</span></p>

        <div id="page-blocks-container">
        </div>

        <div class="editor-actions-bar">
            <div class="add-block-controls">
                <select id="select-block-type">
                    <option value="article_ref">Insérer un Article (JSON)</option>
                    <option value="gallery_ref">Insérer une Galerie Photo</option>
                    <option value="ui_component">Composant UI (Hero, Form...)</option>
                </select>
                <button id="btn-add-block" class="btn-primary">+ Ajouter</button>
            </div>
            <button id="btn-save-page" class="btn-success">Enregistrer le Layout</button>
        </div>
    </section>
</div>

<script src="js/page_builder.js"></script>
<script>
    window.availableGalleries = <?= json_encode($galleryFolders) ?>;
</script>