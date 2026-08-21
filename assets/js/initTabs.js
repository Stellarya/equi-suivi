export function initTabs() {
    document.querySelectorAll('[role="tablist"]').forEach((tablist) => {
        const tabs = Array.from(tablist.querySelectorAll('[role="tab"]'));

        if (tabs.length === 0) {
            return;
        }

        function activate(tab, moveFocus = true) {
            tabs.forEach((otherTab) => {
                const isSelected = otherTab === tab;

                otherTab.classList.toggle('details-tabs__item--active', isSelected);
                otherTab.setAttribute('aria-selected', isSelected ? 'true' : 'false');
                // Un seul onglet dans l'ordre de tabulation : on entre dans le
                // groupe avec Tab, on circule ensuite avec les flèches.
                otherTab.setAttribute('tabindex', isSelected ? '0' : '-1');

                const panel = document.getElementById(otherTab.dataset.tab);

                if (panel !== null) {
                    panel.classList.toggle('details-tab-content--active', isSelected);
                    panel.hidden = !isSelected;
                }
            });

            if (moveFocus) {
                tab.focus();
            }
        }

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => activate(tab, false));

            tab.addEventListener('keydown', (event) => {
                const currentIndex = tabs.indexOf(tab);
                let targetIndex = null;

                if (event.key === 'ArrowRight') {
                    targetIndex = (currentIndex + 1) % tabs.length;
                } else if (event.key === 'ArrowLeft') {
                    targetIndex = (currentIndex - 1 + tabs.length) % tabs.length;
                } else if (event.key === 'Home') {
                    targetIndex = 0;
                } else if (event.key === 'End') {
                    targetIndex = tabs.length - 1;
                }

                if (targetIndex === null) {
                    return;
                }

                event.preventDefault();
                activate(tabs[targetIndex]);
            });
        });

        const initiallySelected = tabs.find(tab => tab.getAttribute('aria-selected') === 'true');
        activate(initiallySelected || tabs[0], false);
    });
}