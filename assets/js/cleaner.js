(function () {
	'use strict';

	if (
		typeof IGWAdminCleaner === 'undefined' ||
		!Array.isArray(IGWAdminCleaner.rules) ||
		IGWAdminCleaner.rules.length === 0
	) {
		return;
	}


	/**
	 * Rules reported as detected during the current page load.
	 *
	 * This prevents multiple AJAX requests for the same rule.
	 */
	const reportedRules = new Set();
	
	/**
	 * Rules reported as checked during the current page load.
	 */
	const checkedRules = new Set();


	/**
	 * Elements already processed.
	 *
	 * WeakSet allows elements to be garbage collected when
	 * they disappear from the DOM.
	 */
	const processedElements = new WeakSet();


	/**
	 * MutationObserver instance.
	 */
	let observer = null;


	/**
	 * Small debounce timer used when the DOM changes repeatedly.
	 */
	let observerTimer = null;


	/**
	 * Process all configured rules.
	 */
	function processRules() {

		IGWAdminCleaner.rules.forEach(function (rule) {

			processRule(rule);

		});

	}


	/**
	 * Process a single cleanup rule.
	 *
	 * @param {Object} rule
	 */
	function processRule(rule) {

		if (
			!rule ||
			!rule.id ||
			!rule.selector ||
			!rule.action
		) {
			return;
		}

		let elements;

		try {
		
			elements = document.querySelectorAll(rule.selector);
		
		} catch (error) {
		
			console.warn(
				'IGW Admin Cleaner: invalid selector:',
				rule.selector
			);
		
			return;
		}
		
		
		/*
		 * The selector was successfully queried,
		 * regardless of whether an element was found.
		 */
		reportRuleChecked(rule.id);
		
		
		if (!elements.length) {
			return;
		}


		let detected = false;


		elements.forEach(function (element) {

			if (processedElements.has(element)) {
				return;
			}


			const result = applyAction(
				element,
				rule.action
			);


			if (result) {

				processedElements.add(element);

				detected = true;

			}

		});


		/*
		 * Report the rule only once during the current page load.
		 */
		if (detected) {
			reportRuleSeen(rule.id);
		}

	}


	/**
	 * Apply a cleanup action.
	 *
	 * @param {HTMLElement} element
	 * @param {string} action
	 *
	 * @return {boolean}
	 */
	function applyAction(element, action) {

		if (!(element instanceof HTMLElement)) {
			return false;
		}


		switch (action) {

			/*
			 * Hide the selected element.
			 */
			case 'element':

				hideElement(element);

				return true;


			/*
			 * Hide the direct parent.
			 */
			case 'parent':

				if (!element.parentElement) {
					return false;
				}

				hideElement(element.parentElement);

				return true;


			/*
			 * Hide the closest LI.
			 *
			 * If no LI exists, fall back to hiding
			 * the selected element itself.
			 */
			case 'closest_li':

				const listItem = element.closest('li');

				if (listItem) {

					hideElement(listItem);

				} else {

					hideElement(element);

				}

				return true;


			/*
			 * Remove the selected element from the DOM.
			 */
			case 'remove':

				element.remove();

				return true;


			default:

				console.warn(
					'IGW Admin Cleaner: unknown action:',
					action
				);

				return false;
		}

	}


	/**
	 * Hide an element.
	 *
	 * @param {HTMLElement} element
	 */
	function hideElement(element) {

		/*
		 * Store a marker so the element can be identified
		 * during debugging if necessary.
		 */
		element.dataset.igwAdminCleanerHidden = '1';

		/*
		 * setProperty with "important" gives us more resistance
		 * against aggressive plugin CSS.
		 */
		element.style.setProperty(
			'display',
			'none',
			'important'
		);

	}


	/**
	 * Report that a rule has been detected.
	 *
	 * @param {string} ruleId
	 */
	function reportRuleSeen(ruleId) {

		if (reportedRules.has(ruleId)) {
			return;
		}


		reportedRules.add(ruleId);


		/*
		 * If AJAX data is unavailable, cleanup should still work.
		 */
		if (
			!IGWAdminCleaner.ajaxUrl ||
			!IGWAdminCleaner.nonce
		) {
			return;
		}


		const data = new URLSearchParams();

		data.append(
			'action',
			'igw_admin_cleaner_rule_seen'
		);

		data.append(
			'nonce',
			IGWAdminCleaner.nonce
		);

		data.append(
			'rule_id',
			ruleId
		);


		fetch(
			IGWAdminCleaner.ajaxUrl,
			{
				method: 'POST',

				credentials: 'same-origin',

				headers: {
					'Content-Type':
						'application/x-www-form-urlencoded; charset=UTF-8'
				},

				body: data.toString()
			}
		)
		.then(function (response) {

			if (!response.ok) {
				throw new Error(
					'HTTP ' + response.status
				);
			}

			return response.json();

		})
		.catch(function (error) {

			/*
			 * A statistics failure must never interfere
			 * with admin cleanup.
			 */
			console.debug(
				'IGW Admin Cleaner: detection could not be recorded.',
				error
			);

		});

	}
	
	/**
	 * Report that a rule has been checked.
	 *
	 * @param {string} ruleId
	 */
	function reportRuleChecked(ruleId) {
	
		if (checkedRules.has(ruleId)) {
			return;
		}
	
	
		checkedRules.add(ruleId);
	
	
		/*
		 * If AJAX data is unavailable, cleanup should still work.
		 */
		if (
			!IGWAdminCleaner.ajaxUrl ||
			!IGWAdminCleaner.nonce
		) {
			return;
		}
	
	
		const data = new URLSearchParams();
	
		data.append(
			'action',
			'igw_admin_cleaner_rule_checked'
		);
	
		data.append(
			'nonce',
			IGWAdminCleaner.nonce
		);
	
		data.append(
			'rule_id',
			ruleId
		);
	
	
		fetch(
			IGWAdminCleaner.ajaxUrl,
			{
				method: 'POST',
	
				credentials: 'same-origin',
	
				headers: {
					'Content-Type':
						'application/x-www-form-urlencoded; charset=UTF-8'
				},
	
				body: data.toString()
			}
		)
		.then(function (response) {
	
			if (!response.ok) {
				throw new Error(
					'HTTP ' + response.status
				);
			}
	
			return response.json();
	
		})
		.catch(function (error) {
	
			console.debug(
				'IGW Admin Cleaner: rule check could not be recorded.',
				error
			);
	
		});
	
	}


	/**
	 * Start observing dynamically added DOM elements.
	 */
	function startObserver() {

		if (
			!IGWAdminCleaner.observeDynamicContent ||
			typeof MutationObserver === 'undefined' ||
			!document.body
		) {
			return;
		}


		observer = new MutationObserver(function (mutations) {

			let relevantChange = false;


			for (const mutation of mutations) {

				if (
					mutation.type === 'childList' &&
					mutation.addedNodes.length > 0
				) {

					relevantChange = true;

					break;

				}

			}


			if (!relevantChange) {
				return;
			}


			/*
			 * Some plugins modify the DOM many times in a row.
			 *
			 * Wait briefly and process all changes together.
			 */
			if (observerTimer) {
				clearTimeout(observerTimer);
			}


			observerTimer = setTimeout(
				function () {

					processRules();

				},
				100
			);

		});


		observer.observe(
			document.body,
			{
				childList: true,
				subtree: true
			}
		);

	}


	/**
	 * Initialize cleaner.
	 */
	function init() {

		processRules();

		startObserver();

	}


	/*
	 * cleaner.js is normally loaded in the footer, but keeping
	 * this check makes the script safe if that changes later.
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