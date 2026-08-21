const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

let lastFocusedElement = null;

export function initModals() {
    const openButtons = document.querySelectorAll('[data-modal-open]');

    openButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const modal = document.getElementById(button.dataset.modalOpen);

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

    // Le piège de focus doit être branché sur chaque modale, sinon Tab
    // ressort du dialogue et parcourt la page masquée derrière.
    document.querySelectorAll('[data-modal]').forEach((modal) => {
        modal.addEventListener('keydown', (event) => trapFocus(modal, event));
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
    lastFocusedElement = document.activeElement;

    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('has-open-modal');

    const firstInput = modal.querySelector(FOCUSABLE);

    if (firstInput !== null) {
        firstInput.focus();
    }
}

function closeModal(modal) {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('has-open-modal');

    if (lastFocusedElement !== null) {
        lastFocusedElement.focus();
        lastFocusedElement = null;
    }
}

function trapFocus(modal, event) {
    if (event.key !== 'Tab') {
        return;
    }

    const focusables = Array.from(modal.querySelectorAll(FOCUSABLE))
        .filter(element => element.offsetParent !== null);

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