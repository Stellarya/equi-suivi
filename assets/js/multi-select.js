// assets/js/multi-select.js
export function initMultiselect() {
    const tagSelectContainers = document.querySelectorAll('[data-tag-select]');

    tagSelectContainers.forEach(container => {
        const nativeSelect = container.querySelector('[data-tag-select-input]');
        const visualSelector = container.querySelector('[data-tag-select-selector]');
        const tagContainer = container.querySelector('[data-tag-container]');

        // Function to refresh the display of tags based on the selected options
        function updateTags() {
            tagContainer.innerHTML = '';
            
            Array.from(nativeSelect.options).forEach(option => {
                if (option.selected) {
                    const tag = document.createElement('span');
                    tag.className = 'discipline-tag';
                    tag.innerHTML = `
                        ${option.text}
                        <button type="button" class="remove-tag" data-id="${option.value}">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    `;
                    tagContainer.appendChild(tag);
                }
            });
        }

        // Add event from the visual selector
        visualSelector.addEventListener('change', (e) => {
            const val = e.target.value;
            if (!val) return;

            const optionToSelect = nativeSelect.querySelector(`option[value="${val}"]`);
            if (optionToSelect) {
                // FORCE l'état sélectionné à "true" sans toucher aux autres options
                optionToSelect.selected = true;
                
                // Optionnel : Déclenche l'événement change sur le select natif pour Symfony/les autres scripts
                nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                
                updateTags();
            }
            
            // Reset the visual selector on the placeholder
            visualSelector.value = '';
        });

        // Delete event when the small cross of a tag is clicked
        tagContainer.addEventListener('click', (e) => {
            const removeBtn = e.target.closest('.remove-tag');
            if (!removeBtn) return;

            const idToRemove = removeBtn.getAttribute('data-id');
            const optionToDeselect = nativeSelect.querySelector(`option[value="${idToRemove}"]`);
            if (optionToDeselect) {
                optionToDeselect.selected = false;
                
                // Déclenche l'événement change ici aussi
                nativeSelect.dispatchEvent(new Event('change', { bubbles: true }));
                
                updateTags();
            }
        });

        // Initialization on load (Useful when modifying an existing horse)
        updateTags();
    });
}