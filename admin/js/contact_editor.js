document.addEventListener('DOMContentLoaded', () => {
    const contactNameInput = document.getElementById('contact-name');
    const filenamePreview = document.getElementById('contact-filename-preview');
    const btnAddSocial = document.getElementById('btn-add-social');
    const socialContainer = document.getElementById('social-links-container');
    const btnSave = document.getElementById('btn-save-contact');

    // 1. Génération du nom de fichier (Slug)
    contactNameInput.addEventListener('input', () => {
        const slug = contactNameInput.value
            .toLowerCase()
            .trim()
            .replace(/[^\w\s-]/g, '')
            .replace(/[\s_-]+/g, '-')
            .replace(/^-+|-+$/g, '');

        filenamePreview.textContent = slug ? slug + '.json' : 'nouveau.json';
    });

    // 2. Ajouter un réseau social via le template
    btnAddSocial.addEventListener('click', () => {
        const template = document.getElementById('social-row-template');
        const clone = document.importNode(template.content, true);
        socialContainer.appendChild(clone);
    });

    // 3. Sauvegarde des données
    btnSave.addEventListener('click', async () => {
        const filename = filenamePreview.textContent;
        if (!contactNameInput.value.trim()) {
            alert("Veuillez entrer au moins un nom.");
            return;
        }

        // Collecte des réseaux sociaux
        const socials = [];
        document.querySelectorAll('.social-row').forEach(row => {
            const platform = row.querySelector('.social-platform').value;
            const value = row.querySelector('.social-value').value.trim();
            if (value) {
                socials.push({ platform, value });
            }
        });

        // --- CONSTRUCTION DYNAMIQUE DU MULTILINGUE ---
        const roleObj = {};
        const addressObj = {};

        if (window.siteLangs && Array.isArray(window.siteLangs)) {
            window.siteLangs.forEach(lang => {
                // On récupère les valeurs des champs générés par le PHP
                const roleInput = document.getElementById(`contact-role-${lang}`);
                const addressInput = document.getElementById(`contact-address-${lang}`);

                if (roleInput) roleObj[lang] = roleInput.value;
                if (addressInput) addressObj[lang] = addressInput.value;
            });
        }

        // Objet final
        const contactData = {
            name: document.getElementById('contact-name').value,
            phone: document.getElementById('contact-phone').value,
            email: document.getElementById('contact-email').value,
            map_url: document.getElementById('contact-map-url').value,
            role: roleObj,        // Utilise l'objet dynamique
            address: addressObj,  // Utilise l'objet dynamique
            socials: socials
        };

        try {
            const response = await fetch('api/save_contact.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    filename: filename,
                    data: contactData
                })
            });

            const result = await response.json();
            if (result.success) {
                alert('Fiche contact enregistrée avec succès !');
                loadContactsList();
            } else {
                alert('Erreur : ' + result.error);
            }
        } catch (error) {
            console.error('Erreur lors de la sauvegarde:', error);
            alert('Erreur de connexion au serveur.');
        }
    });

    // 4. Charger la liste des contacts existants (Sidebar)
    async function loadContactsList() {
        const listElement = document.getElementById('contacts-list');
        try {
            const response = await fetch('api/get_contacts_list.php');
            const files = await response.json();

            listElement.innerHTML = '';
            files.forEach(file => {
                const li = document.createElement('li');
                li.textContent = file;
                li.onclick = () => loadContact(file); // On verra cette fonction plus tard pour l'édition
                listElement.appendChild(li);
            });
        } catch (e) {
            listElement.innerHTML = '<li>Erreur de chargement</li>';
        }
    }
    // 5. Charger les données d'un contact existant pour modification
    async function loadContact(filename) {
        try {
            const response = await fetch(`../json/contacts/${filename}?t=${Date.now()}`);
            const data = await response.json();

            // Fonction utilitaire interne pour remplir un champ s'il existe
            const fillField = (id, value) => {
                const el = document.getElementById(id);
                if (el) {
                    el.value = value || '';
                } else {
                    console.warn(`Champ introuvable dans le HTML : ${id}`);
                }
            };

            // Remplissage sécurisé
            fillField('contact-name', data.name);
            fillField('contact-phone', data.phone);
            fillField('contact-email', data.email);
            fillField('contact-map-url', data.map_url); // C'est ici que ça plantait !

            filenamePreview.textContent = filename;

            // Boucle multilingue (déjà sécurisée normalement)
            if (window.siteLangs) {
                window.siteLangs.forEach(lang => {
                    fillField(`contact-role-${lang}`, data.role ? data.role[lang] : '');
                    fillField(`contact-address-${lang}`, data.address ? data.address[lang] : '');
                });
            }

            // Remplissage des réseaux sociaux
            socialContainer.innerHTML = ''; // On vide l'existant
            if (data.socials && Array.isArray(data.socials)) {
                data.socials.forEach(social => {
                    // On réutilise la logique de clonage du template
                    const template = document.getElementById('social-row-template');
                    const clone = document.importNode(template.content, true);

                    // On pré-remplit les valeurs
                    clone.querySelector('.social-platform').value = social.platform;
                    clone.querySelector('.social-value').value = social.value;

                    socialContainer.appendChild(clone);
                });
            }
            if (data.role) {
                document.getElementById('contact-role-fr').value = data.role.fr || '';
                document.getElementById('contact-role-en').value = data.role.en || '';
            }

            // Pour l'Adresse (C'était manquant dans ton load)
            if (data.address) {
                document.getElementById('contact-address-fr').value = data.address.fr || '';
                document.getElementById('contact-address-en').value = data.address.en || '';
            }
        } catch (error) {
            console.error('Erreur chargement contact:', error);
            alert('Impossible de charger cette fiche.');
        }
    }
    loadContactsList();
});