/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import 'bootstrap';
import 'bootstrap/dist/css/bootstrap.min.css';
import '@fortawesome/fontawesome-free/css/all.min.css';
import './styles/app.scss';
import DataTable from 'datatables.net-dt';
import 'datatables.net-dt/css/dataTables.dataTables.css'
import { initTopbarDropdown } from './js/topbar-dropdown.js';
import { initModals } from './js/modal.js';
import { initHorseListFilter } from './js/horse-list-filter.js';
import { initMultiselect } from './js/multi-select.js';
import { initCascadingSelects } from './js/cascading-select.js';

document.addEventListener('DOMContentLoaded', () => {
    //Topbar
    initTopbarDropdown();

    //Modals
    initModals();

    //Horse active filter
    initHorseListFilter();

    //Multi-select
    initMultiselect();
    initCascadingSelects();

    //Rider
    const riderGalopsTable = document.querySelector('#rider-galops-table');

    if(riderGalopsTable !== null) {
        new DataTable(riderGalopsTable, {
            searching: false,
            paging: false,
            info: false,
            ordering: true,
            language: {
                emptyTable: 'Aucun galop renseigné pour le moment',
                zeroRecords: 'Aucun résultat correspondant'
            },
            order: [[1, 'desc']]
        })
    }
    
});
document.querySelectorAll('.details-tabs__item').forEach(button => {
    button.addEventListener('click', () => {
        const targetId = button.dataset.tab;

        document.querySelectorAll('.details-tabs__item').forEach(tabButton => {
            tabButton.classList.remove('details-tabs__item--active');
        });

        document.querySelectorAll('.details-tab-content').forEach(content => {
            content.classList.remove('details-tab-content--active');
        });

        button.classList.add('details-tabs__item--active');

        document
            .getElementById(targetId)
            .classList.add('details-tab-content--active');
    });
});