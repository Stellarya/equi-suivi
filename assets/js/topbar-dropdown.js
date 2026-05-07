import { event } from "jquery";

export function initTopbarDropdown() {
    const dropdownWrapper = document.querySelectorAll('[data-dropdown="user-menu]');

    dropdownWrapper.forEach((dropdownWrapper) => {
        const trigger = dropdownWrapper.querySelector('[data-dropdown-trigger]');
        const menu = dropdownWrapper.querySelector('[data-dropdown-menu]');

        if(trigger === null || menu === null) {
            return;
        }

        const openDropdown = () => {
            dropdownWrapper.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
        };

        const closeDropdown = () => {
            dropdownWrapper.classList.remove('is-open');
            trigger.setAttribute('aria-expanded', 'false')
        };

        const toggleDropdown = () => {
            if(dropdownWrapper.classList.contains('is-open')) {
                closeDropdown();

                return
            }

            openDropdown();
        };

        trigger.addEventListener('click', (event) =>  {
            event.stopPropagation();
            toggleDropdown();
        });

        menu.addEventListener('click', (event) => {
            event.stopPropagation();

            const clickedLink = event.target.closest('a');

            if(clickedLink !== null) {
                closeDropdown();
            }
        });

        document.addEventListener('click', (event) => {
            if(!dropdownWrapper.contains(event.target)) {
                closeDropdown();
            }
        });

        document.addEventListener('keydown', (event) => {
            if(event.key === 'Escape') {
                closeDropdown();
                trigger.focus();
            }
        })
    })
}