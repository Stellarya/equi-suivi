const activeStatus = 'active';

function updateHorseCardsVisibility(toggleInput, horseCards) {
    const shouldShowOnlyActiveHorses = toggleInput.checked;

    horseCards.forEach((horseCard) => {
        const horseStatus = horseCard.dataset.horseStatus;

        if (shouldShowOnlyActiveHorses && horseStatus !== activeStatus) {
            horseCard.classList.add('d-none');
            return;
        }

        horseCard.classList.remove('d-none');
    });
}

export function initHorseListFilter() {
    const toggleInput = document.querySelector('[data-horse-active-filter-input]');
    const horseCards = document.querySelectorAll('[data-horse-card]');

    if (!toggleInput || horseCards.length === 0) {
        return;
    }

    updateHorseCardsVisibility(toggleInput, horseCards);

    toggleInput.addEventListener('change', () => {
        updateHorseCardsVisibility(toggleInput, horseCards);
    });
}