(function () {
	'use strict';


	/**
	 * Initialize add/edit rule panel.
	 */
	function initRuleForm() {

		const button = document.getElementById(
			'igw-admin-cleaner-add-rule'
		);

		const panel = document.getElementById(
			'igw-admin-cleaner-rule-form'
		);

		/*
		 * Do not stop the rest of admin.js if these
		 * elements do not exist.
		 */
		if (!button || !panel) {
			return;
		}

		button.addEventListener(
			'click',
			function () {

				panel.classList.toggle('is-open');

			}
		);

	}


	/**
	 * Initialize rule search and filters.
	 */
	function initRuleFilters() {

		const search = document.getElementById(
			'igw-rule-search'
		);

		const status = document.getElementById(
			'igw-rule-status-filter'
		);

		const detection = document.getElementById(
			'igw-rule-detection-filter'
		);

		const plugin = document.getElementById(
			'igw-rule-plugin-filter'
		);

		const count = document.getElementById(
			'igw-rule-visible-count'
		);

		const rows = document.querySelectorAll(
			'.igw-rule-row'
		);
		
		const reset = document.getElementById(
			'igw-rule-reset-filters'
		);


		/*
		 * There is nothing to filter.
		 */
		if (!rows.length) {
			return;
		}


		/**
		 * Apply all active filters.
		 */
		function filterRules() {

			const searchValue = search
				? search.value.trim().toLowerCase()
				: '';

			const statusValue = status
				? status.value
				: '';

			const detectionValue = detection
				? detection.value
				: '';

			const pluginValue = plugin
				? plugin.value
				: '';
			
			const hasActiveFilters =
				!!searchValue ||
				!!statusValue ||
				!!detectionValue ||
				!!pluginValue;
			
			if (reset) {
				reset.hidden = !hasActiveFilters;
			}

			let visible = 0;


			rows.forEach(function (row) {

				const rowSearch =
					(row.dataset.search || '').toLowerCase();

				const rowStatus =
					row.dataset.status || '';

				const rowDetection =
					row.dataset.detection || '';

				const rowPlugin =
					(row.dataset.plugin || '').toLowerCase();


				const matchesSearch =
					!searchValue ||
					rowSearch.includes(searchValue);

				const matchesStatus =
					!statusValue ||
					rowStatus === statusValue;

				const matchesDetection =
					!detectionValue ||
					rowDetection === detectionValue;

				const matchesPlugin =
					!pluginValue ||
					rowPlugin === pluginValue;


				const matches =
					matchesSearch &&
					matchesStatus &&
					matchesDetection &&
					matchesPlugin;


				if (matches) {

					row.style.display = '';
					visible++;

				} else {

					row.style.display = 'none';

				}

			});


			/*
			 * Update visible rules counter.
			 */
			if (count) {
				count.textContent = visible;
			}

		}


		/*
		 * Search while typing.
		 */
		if (search) {

			search.addEventListener(
				'input',
				filterRules
			);

		}


		/*
		 * Select filters.
		 */
		[
			status,
			detection,
			plugin
		].forEach(function (select) {

			if (!select) {
				return;
			}

			select.addEventListener(
				'change',
				filterRules
			);

		});


		/*
		 * Initial filtering.
		 */
		filterRules();
		
		if (reset) {
		
			reset.addEventListener(
				'click',
				function () {
		
					if (search) {
						search.value = '';
					}
		
					if (status) {
						status.value = '';
					}
		
					if (detection) {
						detection.value = '';
					}
		
					if (plugin) {
						plugin.value = '';
					}
		
					filterRules();
		
					if (search) {
						search.focus();
					}
		
				}
			);
		
		}

	}


	/**
	 * Initialize admin interface.
	 */
	function init() {

		initRuleForm();
		initRuleFilters();

	}


	/*
	 * admin.js is loaded in the footer, but this also
	 * protects us if loading behavior changes later.
	 */
	if (document.readyState === 'loading') {

		document.addEventListener(
			'DOMContentLoaded',
			init
		);

	} else {

		init();

	}

})();