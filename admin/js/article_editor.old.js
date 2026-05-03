/**
 * Gestionnaire de l'éditeur d'articles multilingue
 */

// Initialisation des données de configuration
const configEl = document.getElementById('editor-langs');
const SUPPORTED_LANGS = JSON.parse(configEl.dataset.config);
let activeLang = 'fr';
let originalCreationDate = null;

// --- Gestion des Blocs (Templates) ---
const BlockTemplates = {
    title: (id, data = null) => createBlockWrapper(id, 'title', 'Titre (H2)', `
        ${generateLangInputs(id, 'input', '', data)}
        <input type="hidden" class="block-level" value="${data ? data.level : 2}">
    `),
    text: (id, data = null) => createBlockWrapper(id, 'text', 'Paragraphe', `
        ${generateLangInputs(id, 'textarea', '', data)}
    `),
    list: (id, data = null) => createBlockWrapper(id, 'list', 'Liste à puces', `
        ${generateLangInputs(id, 'textarea', 'Séparez les éléments par une virgule', data)}
    `),
    link: (id, data = null) => {
        const urlValue = (data && data.url) ? data.url : '';
        return createBlockWrapper(id, 'link', 'Lien / Bouton', `
            <div class="url-field-container" style="margin-bottom: 10px;">
                <label style="font-size: 0.8rem; font-weight: bold; color: var(--text-muted);">URL :</label>
                <input type="text" class="block-url" placeholder="https://..." value="${urlValue}">
            </div>
            <label style="font-size: 0.8rem; font-weight: bold; color: var(--text-muted);">Texte du lien :</label>
            ${generateLangInputs(id, 'input', '', data)} 
        `);
    }
};

// --- Fonctions Utilitaires ---
function addBlock(type, data = null) {
    if (BlockTemplates[type]) {
        const id = data ? (data.id || Date.now()) : Date.now();
        const newBlock = BlockTemplates[type](id, data);
        document.getElementById('blocks-workspace').appendChild(newBlock);
    }
}

function generateLangInputs(blockId, tag, placeholderSuffix = '', blockData = null) {
    return SUPPORTED_LANGS.map(lang => {
        let val = '';
        if (blockData) {
            if (blockData.content && blockData.content[lang]) val = blockData.content[lang];
            else if (blockData.text && blockData.text[lang]) val = blockData.text[lang];
        }

        const placeholder = placeholderSuffix ? `${placeholderSuffix} (${lang})` : `Texte en ${lang}...`;

        return `
            <div class="lang-field" data-lang="${lang}" style="display: ${lang === activeLang ? 'block' : 'none'}">
                ${tag === 'input'
                ? `<input type="text" class="data-${lang}" value="${val}" placeholder="${placeholder}">`
                : `<textarea class="data-${lang}" placeholder="${placeholder}">${val}</textarea>`
            }
            </div>
        `;
    }).join('');
}

function createBlockWrapper(id, type, label, content) {
    const div = document.createElement('div');
    div.className = 'block-item';
    div.dataset.id = id;
    div.dataset.type = type;
    div.innerHTML = `
        <div class="block-header">
            <span class="block-type-label">${label}</span>
            <button type="button" class="btn-delete" onclick="this.closest('.block-item').remove()">×</button>
        </div>
        <div class="block-body">${content}</div>
    `;
    return div;
}

async function loadArticle(filename) {
    try {
        const response = await fetch(`api/get_article.php?file=${filename}`);
        if (!response.ok) throw new Error('Erreur réseau');

        const data = await response.json();
        originalCreationDate = data.meta.created || null;

        const workspace = document.getElementById('blocks-workspace');
        workspace.innerHTML = '';

        const titleInput = document.getElementById('article-title');
        if (titleInput) {
            titleInput.value = data.meta.id;
            document.getElementById('generated-id').textContent = data.meta.id;
        }

        data.content.forEach(block => {
            addBlock(block.type, block);
        });

        console.log("Article chargé :", filename);
    } catch (error) {
        console.error("Erreur:", error);
        alert("Impossible de charger l'article.");
    }
}

function resetEditor() {
    if (!confirm("Voulez-vous vraiment créer un nouvel article ?")) return;
    originalCreationDate = null;
    document.getElementById('article-title').value = '';
    document.getElementById('generated-id').textContent = '--';
    document.getElementById('blocks-workspace').innerHTML = '';
    window.scrollTo(0, 0);
}

// --- SAUVEGARDE (CORRIGÉE) ---
async function saveArticle() {
    const articleIdInput = document.getElementById('article-title');
    const articleId = articleIdInput ? articleIdInput.value.trim() : "";

    if (!articleId) {
        return alert("L'article doit avoir un ID (utilisez le champ titre en haut).");
    }

    const articleData = {
        type: "article",
        meta: {
            id: articleId,
            created: originalCreationDate || new Date().toISOString().split('T')[0],
            updated: new Date().toISOString().split('T')[0]
        },
        content: []
    };

    const blocks = document.querySelectorAll('.block-item');
    blocks.forEach(block => {
        const type = block.dataset.type;
        const blockObj = { type: type };

        if (type === 'link') {
            const urlInput = block.querySelector('.block-url');
            blockObj.url = urlInput ? urlInput.value : '#';
        }

        if (type === 'title') {
            const levelInput = block.querySelector('.block-level');
            blockObj.level = levelInput ? parseInt(levelInput.value) : 2;
        }

        const dataKey = (type === 'title') ? 'text' : 'content';
        blockObj[dataKey] = {};

        SUPPORTED_LANGS.forEach(lang => {
            const field = block.querySelector(`.data-${lang}`);
            if (field) {
                let value = field.value;
                if (type === 'list') {
                    blockObj[dataKey][lang] = value.split(',').map(item => item.trim()).filter(item => item !== "");
                } else {
                    blockObj[dataKey][lang] = value;
                }
            }
        });

        articleData.content.push(blockObj);
    });

    try {
        const response = await fetch('api/save_article.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(articleData)
        });

        const result = await response.json();
        if (result.success) {
            alert("Article enregistré avec succès !");
            location.reload();
        } else {
            throw new Error(result.error);
        }
    } catch (error) {
        console.error("Erreur sauvegarde:", error);
        alert("Erreur lors de l'enregistrement.");
    }
}
async function deleteArticle(filename) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer définitivement l'article "${filename}" ?`)) {
        return;
    }

    try {
        const response = await fetch('api/delete_article.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ filename: filename })
        });

        const result = await response.json();
        if (result.success) {
            location.reload(); // On recharge pour mettre la sidebar à jour
        } else {
            alert("Erreur : " + result.error);
        }
    } catch (error) {
        console.error("Erreur suppression:", error);
    }
}

// --- Événements ---

window.switchEditorLang = function (lang) {
    activeLang = lang;
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.toggle('active', btn.dataset.lang === lang);
    });
    document.querySelectorAll('.lang-field').forEach(field => {
        field.style.display = (field.dataset.lang === lang) ? 'block' : 'none';
    });
};

document.getElementById('article-title').addEventListener('input', function (e) {
    const slug = e.target.value
        .toLowerCase()
        .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
        .replace(/[^a-z0-9]/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');
    document.getElementById('generated-id').textContent = slug || '--';
});

document.getElementById('add-block-trigger').addEventListener('click', () => {
    const type = document.getElementById('new-block-type').value;
    addBlock(type);
});

document.addEventListener('DOMContentLoaded', () => {
    // Chargement d'articles existants
    document.querySelectorAll('.load-article-link').forEach(link => {
        link.addEventListener('click', async function (e) {
            e.preventDefault();
            const filename = this.getAttribute('data-filename');
            if (confirm("Charger cet article ? Les modifications non enregistrées seront perdues.")) {
                await loadArticle(filename);
            }
        });
    });
    document.querySelectorAll('.btn-delete-file').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation(); // Empêche de déclencher le chargement de l'article
            const filename = btn.getAttribute('data-filename');
            deleteArticle(filename);
        });
    });

    // Bouton Nouveau
    const newBtn = document.getElementById('new-article-btn');
    if (newBtn) newBtn.addEventListener('click', resetEditor);

    // Bouton Sauvegarder
    const saveBtn = document.getElementById('save-article-btn');
    if (saveBtn) saveBtn.addEventListener('click', saveArticle);
});