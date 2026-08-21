const activeStatus = 'active';

function updateHorseCardsVisibility(toggleInput, horseCards) {
    const shouldShowOnlyActiveHorses = toggleInput.checked;
    let visibleCount = 0;

    horseCards.forEach((horseCard) => {
        const horseStatus = horseCard.dataset.horseStatus;
        const isHidden = shouldShowOnlyActiveHorses && horseStatus !== activeStatus;

        horseCard.classList.toggle('d-none', isHidden);

        if (!isHidden) {
            visibleCount += 1;
        }
    });

    return visibleCount;
}

function announceCount(counter, visibleCount) {
    if (counter === null) {
        return;
    }

    // Gabarit fourni par Twig, ex. : "%count% chevaux affichés"
    const template = counter.dataset.countLabel || '%count%';
    counter.textContent = template.replace('%count%', visibleCount);
}

export function initHorseListFilter() {
    const toggleInput = document.querySelector('[data-horse-active-filter-input]');
    const horseCards = document.querySelectorAll('[data-horse-card]');
    const counter = document.querySelector('[data-horse-count]');

    if (!toggleInput || horseCards.length === 0) {
        return;
    }

    // Au chargement on filtre sans annoncer : le contenu n'a pas encore changé
    // pour l'utilisateur, une région live qui parle à l'arrivée est parasite.
    updateHorseCardsVisibility(toggleInput, horseCards);

    toggleInput.addEventListener('change', () => {
        const visibleCount = updateHorseCardsVisibility(toggleInput, horseCards);
        announceCount(counter, visibleCount);
    });
}