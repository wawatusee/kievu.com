/* =====================================================
   NUCLEUS — LIGHTBOX
   Isolé — remplaçable par toute autre librairie
   Interface : liens .gallery-item__link avec href vers full size
===================================================== */

document.addEventListener('DOMContentLoaded', () => {

    // Créer la lightbox dans le DOM
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.innerHTML = `
        <button class="lightbox__close" aria-label="Fermer">×</button>
        <img class="lightbox__img" src="" alt="">
    `;
    document.body.appendChild(lightbox);

    const img   = lightbox.querySelector('.lightbox__img');
    const close = lightbox.querySelector('.lightbox__close');

    // Ouvrir
    document.querySelectorAll('.gallery-item__link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            img.src = link.href;
            img.alt = link.querySelector('img')?.alt || '';
            lightbox.classList.add('lightbox--open');
        });
    });

    // Fermer — clic sur fond ou bouton close ou Escape
    const closeLightbox = () => {
        lightbox.classList.remove('lightbox--open');
        img.src = '';
    };

    close.addEventListener('click', closeLightbox);

    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) closeLightbox();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeLightbox();
    });

});