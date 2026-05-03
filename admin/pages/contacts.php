<?php

// Charger la config
$configJson = file_get_contents('../json/config.json');
$configData = json_decode($configJson, true);
$activeLangs = $configData['config']['langs']; // ['fr' => 'Français', 'en' => 'English', ...]
?>
<div class="editor-container">
    <section id="contact-editor" class="main-editor">
        <div class="editor-header">
            <label>Nom du contact (Identité)</label>
            <input type="text" id="contact-name" class="main-title-input" placeholder="Ex: Jean Dupont">
            <p class="id-preview-container">Fichier : <span id="contact-filename-preview">nouveau.json</span></p>
        </div>

        <div class="editor-section">
            <h3><i class="fas fa-id-card"></i> Informations de base</h3>
            <div class="grid-2-col">
                <div class="input-group">
                    <label>Téléphone</label>
                    <input type="text" id="contact-phone" placeholder="+32(0)485 00 00 00">
                </div>
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" id="contact-email" placeholder="nom@exemple.be">
                </div>
            </div>

        </div>
        <div class="editor-section">
            <h3><i class="fas fa-id-card"></i> Rôles (Multilingue)</h3>
            <div class="grid-langs">
                <?php foreach ($activeLangs as $code => $name): ?>
                    <div class="input-group">
                        <label>Rôle (
                            <?= $name ?>)
                        </label>
                        <input type="text" id="contact-role-<?= $code ?>" class="input-role" data-lang="<?= $code ?>"
                            placeholder="Ex: Maître Verrier">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="editor-section">
            <h3><i class="fas fa-map-marker-alt"></i> Localisation (Multilingue)</h3>
            <div class="grid-langs">
                <?php foreach ($activeLangs as $code => $name): ?>
                    <div class="input-group">
                        <label>Adresse (
                            <?= $name ?>)
                        </label>
                        <textarea id="contact-address-<?= $code ?>" class="input-address" data-lang="<?= $code ?>"
                            rows="3"></textarea>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="input-group">
                <label>Lien OpenStreetMap (URL court osm.org)</label>
                <input type="text" id="contact-map-url" placeholder="https://osm.org/go/...">
            </div>
        </div>


        <div class="editor-section">
            <h3><i class="fas fa-share-alt"></i> Réseaux Sociaux & Liens</h3>
            <div id="social-links-container">
            </div>
            <button type="button" id="btn-add-social" class="btn-secondary">
                <i class="fas fa-plus"></i> Ajouter un réseau social
            </button>
        </div>

        <div class="editor-actions-bar">
            <button id="btn-save-contact" class="btn-success">
                <i class="fas fa-save"></i> Enregistrer la fiche contact
            </button>
        </div>
    </section>

    <aside class="editor-sidebar">
        <h3>Fiches existantes</h3>
        <ul id="contacts-list" class="file-list">
            <li class="loading">Chargement des contacts...</li>
        </ul>
    </aside>
</div>

<template id="social-row-template">
    <div class="social-row">
        <select class="social-platform">
            <option value="whatsapp">WhatsApp</option>
            <option value="instagram">Instagram</option>
            <option value="facebook">Facebook</option>
            <option value="linkedin">LinkedIn</option>
            <option value="website">Site Web</option>
        </select>
        <input type="text" class="social-value" placeholder="Identifiant ou lien complet">
        <button class="btn-remove-row" onclick="this.parentElement.remove()">×</button>
    </div>

</template>

<script>
    window.siteLangs = <?= json_encode(array_keys($activeLangs)) ?>;
    console.log("Langues actives chargées :", window.siteLangs); // Pour vérifier dans la console
</script>

<script src="js/contact_editor.js"></script>