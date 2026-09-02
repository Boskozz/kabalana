import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.onClick = (event) => {
            const img = event.target.closest('img');
            if (!img) {
                return;
            }
            const modalEl = document.getElementById('lightboxModal');
            if (!modalEl) {
                return;
            }
            const modalImg = modalEl.querySelector('.lightbox-image');
            modalImg.src = img.currentSrc || img.src;
            modalImg.alt = img.alt || '';
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        };
        this.element.addEventListener('click', this.onClick);
    }

    disconnect() {
        this.element.removeEventListener('click', this.onClick);
    }
}
