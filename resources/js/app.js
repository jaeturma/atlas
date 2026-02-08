import './bootstrap';

import $ from 'jquery';
import Alpine from 'alpinejs';
import 'flowbite';

window.$ = window.jQuery = $;

// DataTables
import 'datatables.net-dt';
import 'datatables.net-buttons/js/dataTables.buttons.min';
import 'datatables.net-select/js/dataTables.select.min';
import 'datatables.net-responsive/js/dataTables.responsive.min';

window.Alpine = Alpine;

// Global DataTables defaults and length-dropdown width fix
const fixLengthSelectWidth = (api) => {
	const container = api.table().container();
	const select = container?.querySelector('.dataTables_length select');
	if (select) {
		select.style.minWidth = '18rem';
		select.style.paddingRight = '3.5rem';
		select.style.width = 'auto';
	}
};

$.extend(true, $.fn.dataTable.defaults, {
	pageLength: 10,
	lengthMenu: [[5, 10, 15, 25, 50, 100], [5, 10, 15, 25, 50, 100]],
	initComplete: function () {
		fixLengthSelectWidth(this.api());
	},
	drawCallback: function () {
		fixLengthSelectWidth(this.api());
	},
});

Alpine.start();
