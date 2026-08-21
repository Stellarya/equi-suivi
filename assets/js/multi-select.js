// assets/js/multi-select.js
export function initMultiselect() {
    const tagSelectContainers = document.querySelectorAll('[data-tag-select]');

    tagSelectContainers.forEach(container => {
        const nativeSelect = container.querySelector('[data-tag-select-input]');
        const visualSelector = container.querySelector('[data-tag-select-selector]');
        const tagContainer = container.querySelector('[data-tag-container]');

        if (nativeSelect === null || visualSelector === null || tagContainer === null) {
            return;
        }

        // Gabarit du libellé du bouton de retrait, fourni par Twig via data-remove-label.
        // Ex. : "Retirer la discipline %name%"
        const removeLabelTemplate = tagContainer.dataset.removeLabel || 'Retirer %name%';

        function buildTag(option) {
            const tag = document.createElement('span');
            tag.className = 'discipline-tag';

            // textContent uniquement sur le libellé : pas d'injection HTML possible.
            const labelNode = document.createElement('span');
            labelNode.className = 'discipline-tag__label';
            labelNode.textContent = option.text;

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'remove-tag';
            removeButton.dataset.id = option.value;
            removeButton.setAttribute('aria-label', removeLabelTemplate.replace('%name%', option.text));

            const icon = document.createElement('i');
            icon.className = 'fa-solid fa-xmark';
            icon.setAttribute('aria-hidden', 'true');

            removeButton.appendChild(icon);
            tag.appendChild(labelNode);
            tag.appendChild(removeButton);

            return tag;
        }

        function updateTags() {
            tagContainer.replaceChildren();

            Array.from(nativeSelect.options)
                .filter(option => option.selected)
                .forEach(option => tagContainer.appendChild(buildTag(option)));
        }

        visualSelector.addEventListener('change', (event) => {
            const value = event.target.value;

            if (!value) {
                return;
            }

            const optionToSelect = Array.from(nativeSelect.options)
                .find(option => option.value === value);

            if (optionToSelect !== undefined) {
                optionToSelect.selected = true;
                nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                updateTags();
            }

            visualSelector.value = '';
        });

        tagContainer.addEventListener('click', (event) => {
            const removeButton = event.target.closest('.remove-tag');

            if (removeButton === null) {
                return;
            }

            const idToRemove = removeButton.dataset.id;
            const optionToDeselect = Array.from(nativeSelect.options)
                .find(option => option.value === idToRemove);

            if (optionToDeselect !== undefined) {
                optionToDeselect.selected = false;
                nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                updateTags();

                // Le bouton cliqué vient d'être détruit : sans ça le focus repart sur <body>.
                visualSelector.focus();
            }
        });

        updateTags();
    });
}