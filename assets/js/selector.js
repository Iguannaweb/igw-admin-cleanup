(function () {
	'use strict';
	
	const { __ } = wp.i18n;

	/**
	 * Selector mode state.
	 */
	let selectorMode = false;

	let currentElement = null;

	let overlay = null;

	let toolbar = null;

	let resultPanel = null;
	let selectedElementText = '';


	/**
	 * Start selector mode.
	 */
	function startSelectorMode() {

		if (selectorMode) {
			return;
		}

		selectorMode = true;

		document.body.classList.add(
			'igw-selector-mode'
		);

		createOverlay();
		createToolbar();

		document.addEventListener(
			'mousemove',
			handleMouseMove,
			true
		);

		document.addEventListener(
			'click',
			handleClick,
			true
		);

		document.addEventListener(
			'keydown',
			handleKeyDown,
			true
		);

	}


	/**
	 * Stop selector mode.
	 */
	function stopSelectorMode() {

		selectorMode = false;

		currentElement = null;

		document.body.classList.remove(
			'igw-selector-mode'
		);

		document.removeEventListener(
			'mousemove',
			handleMouseMove,
			true
		);

		document.removeEventListener(
			'click',
			handleClick,
			true
		);

		document.removeEventListener(
			'keydown',
			handleKeyDown,
			true
		);

		removeOverlay();
		removeToolbar();

	}


	/**
	 * Mouse movement.
	 *
	 * @param {MouseEvent} event
	 */
	function handleMouseMove(event) {

		if (!selectorMode) {
			return;
		}

		const element = event.target;

		if (!isSelectableElement(element)) {
			return;
		}

		currentElement = element;

		highlightElement(element);

	}


	/**
	 * Element selection.
	 *
	 * @param {MouseEvent} event
	 */
	function handleClick(event) {
	
		if (!selectorMode) {
			return;
		}
	
		const element = event.target;
	
		if (!isSelectableElement(element)) {
			return;
		}
	
		/*
		 * Prevent links, buttons and other controls from running.
		 */
		event.preventDefault();
		event.stopPropagation();
		event.stopImmediatePropagation();
	
		currentElement = element;
	
		selectedElementText =
			element.textContent
				? element.textContent
					.trim()
					.replace(/\s+/g, ' ')
					.substring(0, 100)
				: '';
	
		const selector =
			generateSelector(element);
	
		stopSelectorMode();
	
		showResultPanel(
			element,
			selector
		);
	}


	/**
	 * Keyboard controls.
	 *
	 * @param {KeyboardEvent} event
	 */
	function handleKeyDown(event) {

		if (
			selectorMode &&
			event.key === 'Escape'
		) {
			event.preventDefault();

			stopSelectorMode();
		}

	}


	/**
	 * Check whether an element can be selected.
	 *
	 * @param {Element} element
	 *
	 * @return {boolean}
	 */
	function isSelectableElement(element) {

		if (!(element instanceof HTMLElement)) {
			return false;
		}


		/*
		 * Ignore our own selector UI.
		 */
		if (
			element.closest(
				'.igw-selector-toolbar'
			) ||
			element.closest(
				'.igw-selector-result'
			)
		) {
			return false;
		}


		/*
		 * Ignore the highlight overlay.
		 */
		if (
			element.classList.contains(
				'igw-selector-overlay'
			)
		) {
			return false;
		}


		return true;

	}


	/**
	 * Create highlight overlay.
	 */
	function createOverlay() {

		if (overlay) {
			return;
		}

		overlay = document.createElement('div');

		overlay.className =
			'igw-selector-overlay';

		document.body.appendChild(
			overlay
		);

	}


	/**
	 * Remove highlight overlay.
	 */
	function removeOverlay() {

		if (!overlay) {
			return;
		}

		overlay.remove();

		overlay = null;

	}


	/**
	 * Highlight selected element.
	 *
	 * @param {HTMLElement} element
	 */
	function highlightElement(element) {

		if (!overlay) {
			return;
		}

		const rect =
			element.getBoundingClientRect();


		overlay.style.top =
			(rect.top + window.scrollY) + 'px';

		overlay.style.left =
			(rect.left + window.scrollX) + 'px';

		overlay.style.width =
			rect.width + 'px';

		overlay.style.height =
			rect.height + 'px';

	}


	/**
	 * Create selector mode toolbar.
	 */
	function createToolbar() {

		if (toolbar) {
			return;
		}

		toolbar = document.createElement('div');

		toolbar.className =
			'igw-selector-toolbar';

		toolbar.innerHTML = `
			<div class="igw-selector-toolbar__content">

				<strong>
					IGW Admin Cleanup
				</strong>

				<span>
					${escapeHtml(
						__( 'Select the element you want to hide', 'igw-admin-cleanup' )
					)}
				</span>

			</div>

			<button
				type="button"
				class="button igw-selector-toolbar__cancel"
			>
				${escapeHtml(
					__( 'Cancel', 'igw-admin-cleanup' )
				)}
			</button>
		`;


		document.body.appendChild(
			toolbar
		);


		const cancelButton =
			toolbar.querySelector(
				'.igw-selector-toolbar__cancel'
			);


		if (cancelButton) {

			cancelButton.addEventListener(
				'click',
				function (event) {

					event.preventDefault();
					event.stopPropagation();

					stopSelectorMode();

				}
			);

		}

	}


	/**
	 * Remove toolbar.
	 */
	function removeToolbar() {

		if (!toolbar) {
			return;
		}

		toolbar.remove();

		toolbar = null;

	}


	/**
	 * Generate a CSS selector.
	 *
	 * Priority:
	 *
	 * 1. Unique ID
	 * 2. Stable classes
	 * 3. Useful attributes
	 * 4. Parent context
	 * 5. nth-child fallback
	 *
	 * @param {HTMLElement} element
	 *
	 * @return {string}
	 */
	function generateSelector(element) {

		/*
		 * 1. Unique ID.
		 */
		if (
			element.id &&
			isUniqueSelector(
				'#' + cssEscape(element.id)
			)
		) {
			return '#' + cssEscape(element.id);
		}


		/*
		 * 2. Classes.
		 */
		const classSelector =
			generateClassSelector(element);

		if (
			classSelector &&
			isUniqueSelector(classSelector)
		) {
			return classSelector;
		}


		/*
		 * 3. Useful attributes.
		 */
		const attributeSelector =
			generateAttributeSelector(element);

		if (
			attributeSelector &&
			isUniqueSelector(attributeSelector)
		) {
			return attributeSelector;
		}


		/*
		 * 4. Try using parent context.
		 */
		const contextualSelector =
			generateContextSelector(element);

		if (contextualSelector) {
			return contextualSelector;
		}


		/*
		 * 5. Final fallback.
		 */
		return generatePathSelector(element);

	}


	/**
	 * Generate selector based on classes.
	 *
	 * @param {HTMLElement} element
	 *
	 * @return {string}
	 */
	function generateClassSelector(element) {

		if (!element.classList.length) {
			return '';
		}


		const classes =
			Array.from(element.classList)
				.filter(isUsefulClass)
				.slice(0, 3);


		if (!classes.length) {
			return '';
		}


		return classes
			.map(function (className) {

				return '.' + cssEscape(className);

			})
			.join('');

	}


	/**
	 * Filter classes that are likely to be unstable
	 * or created by WordPress / JavaScript state.
	 *
	 * @param {string} className
	 *
	 * @return {boolean}
	 */
	function isUsefulClass(className) {

		if (!className) {
			return false;
		}


		const ignoredClasses = [
			'active',
			'current',
			'open',
			'closed',
			'selected',
			'hidden',
			'visible',
			'hover',
			'focus',
			'disabled'
		];


		if (
			ignoredClasses.includes(
				className.toLowerCase()
			)
		) {
			return false;
		}


		/*
		 * Avoid classes that look like generated hashes.
		 */
		if (
			/^[a-f0-9]{8,}$/i.test(className)
		) {
			return false;
		}


		return true;

	}


	/**
	 * Generate selector using useful attributes.
	 *
	 * @param {HTMLElement} element
	 *
	 * @return {string}
	 */
	function generateAttributeSelector(element) {

		const tag =
			element.tagName.toLowerCase();


		/*
		 * Links are especially useful because promotional links
		 * often contain stable product URLs.
		 */
		if (
			tag === 'a' &&
			element.hasAttribute('href')
		) {

			const href =
				element.getAttribute('href');


			if (href) {

				try {

					const url =
						new URL(
							href,
							window.location.origin
						);


					if (url.hostname) {

						return (
							'a[href*="' +
							escapeAttributeValue(
								url.hostname
							) +
							'"]'
						);

					}

				} catch (error) {
					// Ignore invalid URL.
				}

			}

		}


		const usefulAttributes = [
			'data-id',
			'data-slug',
			'data-plugin',
			'aria-label',
			'name'
		];


		for (
			const attribute
			of usefulAttributes
		) {

			if (
				element.hasAttribute(attribute)
			) {

				const value =
					element.getAttribute(attribute);

				if (!value) {
					continue;
				}


				return (
					tag +
					'[' +
					attribute +
					'="' +
					escapeAttributeValue(value) +
					'"]'
				);

			}

		}


		return '';

	}


	/**
	 * Try selector with parent context.
	 *
	 * @param {HTMLElement} element
	 *
	 * @return {string}
	 */
	function generateContextSelector(element) {

		let parent =
			element.parentElement;


		while (
			parent &&
			parent !== document.body
		) {

			let parentSelector = '';


			if (parent.id) {

				parentSelector =
					'#' +
					cssEscape(parent.id);

			} else {

				parentSelector =
					generateClassSelector(parent);

			}


			if (parentSelector) {

				let childSelector = '';


				if (element.id) {

					childSelector =
						'#' +
						cssEscape(element.id);

				} else {

					childSelector =
						generateClassSelector(element);

				}


				if (!childSelector) {

					childSelector =
						element.tagName
							.toLowerCase();

				}


				const selector =
					parentSelector +
					' ' +
					childSelector;


				if (
					isUniqueSelector(selector)
				) {
					return selector;
				}

			}


			parent =
				parent.parentElement;

		}


		return '';

	}


	/**
	 * Generate a full CSS path as final fallback.
	 *
	 * @param {HTMLElement} element
	 *
	 * @return {string}
	 */
	function generatePathSelector(element) {

		const parts = [];

		let current = element;


		while (
			current &&
			current !== document.body
		) {

			if (current.id) {

				parts.unshift(
					'#' +
					cssEscape(current.id)
				);

				break;

			}


			let part =
				current.tagName.toLowerCase();


			const usefulClasses =
				Array.from(current.classList)
					.filter(isUsefulClass);


			if (usefulClasses.length) {

				part +=
					'.' +
					usefulClasses
						.slice(0, 2)
						.map(cssEscape)
						.join('.');

			} else {

				const parent =
					current.parentElement;


				if (parent) {

					const siblings =
						Array.from(
							parent.children
						).filter(
							function (child) {

								return (
									child.tagName ===
									current.tagName
								);

							}
						);


					if (siblings.length > 1) {

						const index =
							siblings.indexOf(
								current
							) + 1;


						part +=
							':nth-of-type(' +
							index +
							')';

					}

				}

			}


			parts.unshift(part);

			current =
				current.parentElement;

		}


		return parts.join(' > ');

	}


	/**
	 * Check whether selector matches exactly one element.
	 *
	 * @param {string} selector
	 *
	 * @return {boolean}
	 */
	function isUniqueSelector(selector) {

		try {

			return (
				document.querySelectorAll(
					selector
				).length === 1
			);

		} catch (error) {

			return false;

		}

	}


	/**
	 * Escape CSS identifier.
	 *
	 * @param {string} value
	 *
	 * @return {string}
	 */
	function cssEscape(value) {

		if (
			window.CSS &&
			typeof window.CSS.escape === 'function'
		) {
			return window.CSS.escape(value);
		}


		return value.replace(
			/([^\w-])/g,
			'\\$1'
		);

	}


	/**
	 * Escape attribute values.
	 *
	 * @param {string} value
	 *
	 * @return {string}
	 */
	function escapeAttributeValue(value) {

		return String(value)
			.replace(/\\/g, '\\\\')
			.replace(/"/g, '\\"');

	}


	/**
	 * Show result panel.
	 *
	 * @param {HTMLElement} element
	 * @param {string} selector
	 */
	function showResultPanel(
		element,
		selector
	) {

		removeResultPanel();


		resultPanel =
			document.createElement('div');


		resultPanel.className =
			'igw-selector-result';


		const tag =
			element.tagName.toLowerCase();

		
			
		const text =
			element.textContent
				? element.textContent
					.trim()
					.replace(/\s+/g, ' ')
					.substring(0, 120)
				: '';


		resultPanel.innerHTML = `
			<div class="igw-selector-result__header">

				<strong>
					${escapeHtml(
						__( 'Element selected', 'igw-admin-cleanup' )
					)}
				</strong>

				<button
					type="button"
					class="igw-selector-result__close"
					aria-label="Close"
				>
					×
				</button>

			</div>

			<div class="igw-selector-result__body">

				<div class="igw-selector-result__field">

					<span class="igw-selector-result__label">
						${escapeHtml(
							__( 'Element', 'igw-admin-cleanup' )
						)}
					</span>

					<code>
						&lt;${escapeHtml(tag)}&gt;
					</code>

				</div>

				${
					text
						? `
						<div class="igw-selector-result__field">

							<span class="igw-selector-result__label">
								${escapeHtml(
									__( 'Content', 'igw-admin-cleanup' )
								)}
							</span>

							<span>
								${escapeHtml(text)}
							</span>

						</div>
						`
						: ''
				}

				<div class="igw-selector-result__field">

					<label
						class="igw-selector-result__label"
						for="igw-selector-result-value"
					>
						${escapeHtml(
							__( 'Suggested selector', 'igw-admin-cleanup' )
						)}
					</label>

					<input
						type="text"
						id="igw-selector-result-value"
						class="regular-text code"
						value="${escapeHtmlAttribute(selector)}"
					>

				</div>

			</div>

			<div class="igw-selector-result__footer">

				<button
					type="button"
					class="button igw-selector-result__cancel"
				>
					${escapeHtml(
						__( 'Cancel', 'igw-admin-cleanup' )
					)}
				</button>

				<button
					type="button"
					class="button button-primary igw-selector-result__use"
				>
					${escapeHtml(
						__( 'Use selector', 'igw-admin-cleanup' )
					)}
				</button>

			</div>
		`;


		document.body.appendChild(
			resultPanel
		);


		const closeButton =
			resultPanel.querySelector(
				'.igw-selector-result__close'
			);

		const cancelButton =
			resultPanel.querySelector(
				'.igw-selector-result__cancel'
			);

		const useButton =
			resultPanel.querySelector(
				'.igw-selector-result__use'
			);


		if (closeButton) {

			closeButton.addEventListener(
				'click',
				removeResultPanel
			);

		}


		if (cancelButton) {

			cancelButton.addEventListener(
				'click',
				removeResultPanel
			);

		}


		if (useButton) {

			useButton.addEventListener(
				'click',
				useSelectedSelector
			);

		}

	}


	/**
	 * Use selected selector.
	 *
	 * If we are already on the IGW Admin Cleaner page,
	 * open the rule form directly.
	 *
	 * Otherwise redirect to the plugin page with the
	 * selected selector as a URL parameter.
	 */
	function useSelectedSelector() {
	
		if (!resultPanel) {
			return;
		}
	
	
		const selectedSelector =
			resultPanel.querySelector(
				'#igw-selector-result-value'
			);
	
	
		if (!selectedSelector) {
			return;
		}
	
	
		const selector =
			selectedSelector.value.trim();
	
	
		if (!selector) {
			return;
		}
	
	
		const ruleForm =
			document.getElementById(
				'igw-admin-cleaner-rule-form'
			);
	
	
		const selectorInput =
			document.getElementById(
				'igw_rule_selector'
			);
	
	
		/*
		 * We are already on the IGW Admin Cleaner page.
		 */
		if (
			ruleForm &&
			selectorInput
		) {
	
			selectorInput.value =
				selector;
	
			ruleForm.classList.add(
				'is-open'
			);
	
			removeResultPanel();
	
			selectorInput.focus();
	
			ruleForm.scrollIntoView({
				behavior: 'smooth',
				block: 'start'
			});
	
			return;
		}
	
	
		/*
		 * We are on another WordPress admin page.
		 */
		if (
			typeof IGWAdminCleanerSelector === 'undefined' ||
			!IGWAdminCleanerSelector.adminUrl
		) {
			return;
		}
	
	
		const url =
			new URL(
				IGWAdminCleanerSelector.adminUrl,
				window.location.origin
			);
		
		
	
	
		url.searchParams.set(
			'igw_selector',
			selector
		);
		
		if (selectedElementText) {
		
			url.searchParams.set(
				'igw_name',
				selectedElementText
			);
		
		}
	
	
		window.location.href =
			url.toString();
	
	}


	/**
	 * Remove result panel.
	 */
	function removeResultPanel() {

		if (!resultPanel) {
			return;
		}

		resultPanel.remove();

		resultPanel = null;

	}


	/**
	 * HTML escaping.
	 *
	 * @param {string} value
	 *
	 * @return {string}
	 */
	function escapeHtml(value) {

		return String(value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');

	}


	/**
	 * HTML attribute escaping.
	 *
	 * @param {string} value
	 *
	 * @return {string}
	 */
	function escapeHtmlAttribute(value) {

		return escapeHtml(value);

	}


	/**
	 * Initialize selector controls.
	 */
	function init() {
	
		/*
		 * Button inside IGW Admin Cleaner page.
		 */
		const pageButton =
			document.getElementById(
				'igw-admin-cleaner-select-element'
			);
	
	
		/*
		 * Button from WordPress admin bar.
		 */
		const adminBarButton =
			document.querySelector(
				'#wp-admin-bar-igw-admin-cleaner-select-element > a'
			);
	
	
		if (pageButton) {
	
			pageButton.addEventListener(
				'click',
				function (event) {
	
					event.preventDefault();
	
					startSelectorMode();
	
				}
			);
	
		}
	
	
		if (adminBarButton) {
	
			adminBarButton.addEventListener(
				'click',
				function (event) {
	
					event.preventDefault();
	
					startSelectorMode();
	
				}
			);
	
		}
	
	}


	if (document.readyState === 'loading') {

		document.addEventListener(
			'DOMContentLoaded',
			init
		);

	} else {

		init();

	}

})();