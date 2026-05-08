export function initModals() {
    const openButtons = document.querySelectorAll('[data-modal-open]');

    openButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const modalId = button.dataset.modalOpen;
            const modal = document.getElementById(modalId);

            if (modal !== null) {
                openModal(modal);
            }
        });
    });

    document.querySelectorAll('[data-modal-close]').forEach((button) => {
        button.addEventListener('click', () => {
            const modal = button.closest('[data-modal]');

            if (modal !== null) {
                closeModal(modal);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key !== 'Escape') {
            return;
        }

        document.querySelectorAll('[data-modal].is-open').forEach((modal) => {
            closeModal(modal);
        });
    });
}

function openModal(modal) {
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('has-open-modal');

    const firstInput = modal.querySelector('input, button, select, textarea, a[href]');

    if (firstInput !== null) {
        firstInput.focus();
    }
}

function closeModal(modal) {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('has-open-modal');
}