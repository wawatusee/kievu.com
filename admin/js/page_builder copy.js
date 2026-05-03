/**
 * page_builder.js
 */
// 1. Templates des Blocs
const PageBlockTemplates = {
    article_ref: (id, data = {}) => {
        const selected = data.filename || '';
        console.log("Rendu du bloc article_ref pour :", selected); // Nouveau log

        // On vérifie si window.availableArticles existe et n'est pas vide
        const options = (window.availableArticles && window.availableArticles.length > 0)
            ? window.availableArticles.map(file =>
                `<option value="${file}" ${selected === file ? 'selected' : ''}>${file}</option>`
            ).join('')
            : `<option value="${selected}">${selected} (chargement...)</option>`;

        return `
        <div class="block-item" data-id="${id}" data-type="article_ref">
            <div class="block-header">
                <strong>📄 Section : Article</strong>
                <button class="btn-delete-block" onclick="this.closest('.block-item').remove()">×</button>
            </div>
            <div class="block-body">
                <select class="data-filename">
                    <option value="">-- Sélectionner un article --</option>
                    ${options}
                </select>
            </div>
        </div>`;
    },
    ui_component: (id, data = {}) => {
        return `
                <div class="block-item" data-id="${id}" data-type="ui_component">
                    <div class="block-header">
                        <strong>⚙️ Composant UI</strong>
                        <button class="btn-delete-block" onclick="this.closest('.block-item').remove()">×</button>
                    </div>
                    <div class="block-body">
                        <select class="data-comp-name">
                            <option value="hero" ${data.name === 'hero' ? 'selected' : ''}>Bannière Hero</option>
                            <option value="contact" ${data.name === 'contact' ? 'selected' : ''}>Formulaire Contact</option>
                            <option value="gallery" ${data.name === 'gallery' ? 'selected' : ''}>Grille Galerie</option>
                        </select>
                    </div>
                </div>`;
    },
    gallery_ref: (id, data = {}) => {
        const selectedId = data.folder || ''; // On garde 'folder' comme clé pour la compatibilité
        const options = (window.availableGalleries || [])
            .map(gallery => {
                // gallery.id est le nom du dossier, gallery.name est le nom lisible
                return `<option value="${gallery.id}" ${selectedId === gallery.id ? 'selected' : ''}>${gallery.name}</option>`;
            })
            .join('');

        return `
    <div class="block-item" data-id="${id}" data-type="gallery_ref">
        <div class="block-header">
            <strong>🖼️ Galerie Photo</strong>
            <button class="btn-delete-block" onclick="this.closest('.block-item').remove()">×</button>
        </div>
        <div class="block-body">
            <select class="data-folder">
                <option value="">-- Choisir une galerie --</option>
                ${options}
            </select>
        </div>
    </div>`;
    },
    contact_ref: (id, data = {}) => {
        const selected = data.filename || '';
        // On utilise window.availableContacts chargé via l'API
        const options = (window.availableContacts || [])
            .map(file => `<option value="${file}" ${selected === file ? 'selected' : ''}>${file}</option>`)
            .join('');

        return `
            <div class="block-item" data-id="${id}" data-type="contact_ref">
                <div class="block-header">
                    <strong>👤 Référence Contact</strong>
                    <button class="btn-delete-block" onclick="this.closest('.block-item').remove()">×</button>
                </div>
                <div class="block-body">
                    <select class="data-filename">
                        <option value="">-- Sélectionner une fiche contact --</option>
                        ${options}
                    </select>
                </div>
            </div>`;
    }
};
async function loadPageLayout(filename) {
    console.log("1. Clic détecté sur :", filename);
    const container = document.getElementById('page-blocks-container');

    try {
        const response = await fetch(`../json/pages/${filename}?t=${Date.now()}`);
        if (!response.ok) throw new Error("Fichier introuvable");

        const data = await response.json();
        console.log("2. Données JSON reçues :", data);

        document.getElementById('page-title').value = data.title || '';
        document.getElementById('generated-filename').textContent = filename;
        container.innerHTML = '';

        // Correction ici : on vérifie bien l'existence du layout
        if (data.layout && Array.isArray(data.layout)) {
            console.log("3. Analyse du layout, nb de blocs :", data.layout.length);

            data.layout.forEach((blockData, index) => {
                const id = 'block_' + Date.now() + '_' + index;

                if (PageBlockTemplates[blockData.type]) {
                    const html = PageBlockTemplates[blockData.type](id, blockData);
                    container.insertAdjacentHTML('beforeend', html);
                } else {
                    console.error("Type de bloc inconnu :", blockData.type);
                }
            }); // FIN du forEach
            console.log("4. Reconstruction terminée.");
        } else {
            console.warn("Le fichier chargé ne contient pas de tableau 'layout'.");
        } // FIN du if/else layout

    } catch (e) {
        console.error("Erreur détaillée lors du chargement :", e);
        alert("Erreur lors du chargement du layout.");
    } // FIN du catch
} // FIN de la fonction

document.addEventListener('DOMContentLoaded', () => {
    const blocksContainer = document.getElementById('page-blocks-container');
    const btnAddBlock = document.getElementById('btn-add-block');
    const selectBlockType = document.getElementById('select-block-type');
    const btnSavePage = document.getElementById('btn-save-page');
    const pageTitleInput = document.getElementById('page-title');
    const generatedFilename = document.getElementById('generated-filename');


    if (pageTitleInput && generatedFilename) {
        pageTitleInput.addEventListener('input', () => {
            const slug = pageTitleInput.value
                .toLowerCase()
                .trim()
                .replace(/[^\w\s-]/g, '') // Supprime les caractères spéciaux
                .replace(/[\s_-]+/g, '-') // Remplace espaces et underscores par un tiret
                .replace(/^-+|-+$/g, ''); // Nettoie les tirets aux extrémités

            generatedFilename.textContent = slug ? slug + '.json' : 'nouveau.json';
        });
    }

    // 2. Récupérer TOUTES les ressources nécessaires
    // Chemin relatif depuis le dossier /admin/ où se trouve ton JS
    const galleryIndexPath = '../public/img/content/galleries/galleries_index.json';

    Promise.all([
        fetch('api/get_articles_list.php').then(res => res.json()),
        fetch('api/get_contacts_list.php').then(res => res.json()),
        fetch(`${galleryIndexPath}?t=${Date.now()}`).then(res => {
            if (!res.ok) return []; // Si le fichier n'existe pas encore, on renvoie un tableau vide
            return res.json();
        })
    ]).then(([articles, contacts, galleries]) => {
        window.availableArticles = articles;
        window.availableContacts = contacts;
        window.availableGalleries = galleries;

        console.log("Builder synchronisé directement sur l'index JSON.");
        btnAddBlock.disabled = false;
    }).catch(err => {
        console.error("Erreur critique au chargement des ressources :", err);
    });

    // 3. Ajouter un bloc
    btnAddBlock.addEventListener('click', () => {
        const type = selectBlockType.value;
        const id = 'block_' + Date.now();
        const html = PageBlockTemplates[type](id);
        blocksContainer.insertAdjacentHTML('beforeend', html);

    });

    // 4. Sauvegarde
    btnSavePage.addEventListener('click', () => {
        const blocks = [];
        document.querySelectorAll('.block-item').forEach(el => {
            const type = el.dataset.type;
            const blockData = { type: type };
            if (type === 'article_ref') blockData.filename = el.querySelector('.data-filename').value;
            if (type === 'ui_component') blockData.name = el.querySelector('.data-comp-name').value;
            if (type === 'gallery_ref') blockData.folder = el.querySelector('.data-folder').value;
            blocks.push(blockData);
        });

        const payload = {
            title: pageTitleInput.value,
            filename: pageTitleInput.value.toLowerCase().replace(/\s+/g, '-') + '.json',
            layout: blocks
        };

        fetch('api/save_page.php', {
            method: 'POST',
            body: JSON.stringify(payload)
        })
            .then(res => res.json())
            .then(res => {
                if (res.success) alert('Structure de page enregistrée !');
            });
    });
    //5. Écouteur pour charger une page
    document.getElementById('file-list').addEventListener('click', (e) => {
        const link = e.target.closest('.load-page-link');
        if (link) {
            e.preventDefault();
            const filename = link.dataset.filename;
            loadPageLayout(filename);
        }
    });
});