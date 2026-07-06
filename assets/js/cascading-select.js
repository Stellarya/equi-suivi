export function initCascadingSelects() {
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('js-region-select') || e.target.classList.contains('js-department-select')) {
            const form = e.target.closest('.js-cascading-form');
            const container = e.target.closest('.js-location-container');
            
            if (!form || !container) return;

            const formData = new FormData(form);
            
            fetch(form.action || window.location.href, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newDocForm = doc.querySelector('.js-cascading-form');
                
                if (newDocForm) {
                    const newContainer = newDocForm.querySelector('.js-location-container');
                    if (newContainer) {
                        // On remplace le HTML des blocs Département et Écurie filtrés
                        container.querySelector('.js-department-wrapper').innerHTML = newContainer.querySelector('.js-department-wrapper').innerHTML;
                        container.querySelector('.js-ranch-wrapper').innerHTML = newContainer.querySelector('.js-ranch-wrapper').innerHTML;
                    }
                }
            })
            .catch(err => console.error('Erreur AJAX cascade:', err));
        }
    });
}