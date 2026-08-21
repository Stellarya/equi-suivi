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

        button.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }

        event.preventDefault();
        button.click();
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

let lastFocusedElement = null; 

function openModal(modal) {
    lastFocusedElement = document.activeElement;

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

    if(lastFocusedElement !== null) {
        lastFocusedElement.focus();
        lastFocusedElement = null;
    }
}

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

function trapFocus(modal, event) {
    if (event.key !== 'Tab') {
        return;
    }

    const focusables = Array.from(modal.querySelectorAll(FOCUSABLE));

    if (focusables.length === 0) {
        return;
    }

    const first = focusables[0];
    const last = focusables[focusables.length - 1];

    if (event.shiftKey && document.activeElement === first) {
        event.preventDefault();
        last.focus();
        return;
    }

    if (!event.shiftKey && document.activeElement === last) {
        event.preventDefault();
        first.focus();
    }
}