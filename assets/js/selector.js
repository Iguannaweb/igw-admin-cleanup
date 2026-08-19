(function () {
	'use strict';
	
	const {
		__,
		sprintf
	} = wp.i18n;
	
	const previewElements = [];

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
	
		const candidates =
			generateSelectorCandidates(
				element
			);
		
		const selector =
			candidates.length
				? candidates[0].selector
				: generateSelector(element);
	
		stopSelectorMode();
	
		showResultPanel(
			element,
			selector,
			candidates
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
	 * Generate multiple selector candidates for an element.
	 *
	 * @param {HTMLElement} element
	 *
	 * @return {Array}
	 */
	function generateSelectorCandidates(element) {
	
		const candidates = [];
	
		/**
		 * Helper to add a candidate only once.
		 */
		function addCandidate(
			selector,
			type,
			label
		) {
	
			if (!selector) {
				return;
			}
	
			let count = 0;
	
			try {
	
				count =
					document.querySelectorAll(
						selector
					).length;
	
			} catch (error) {
	
				return;
	
			}
	
			/*
			 * Ignore selectors that do not match anything.
			 */
			if (!count) {
				return;
			}
	
			/*
			 * Avoid duplicates.
			 */
			if (
				candidates.some(
					function (candidate) {
						return (
							candidate.selector === selector
						);
					}
				)
			) {
				return;
			}
	
			const candidate = {
				selector: selector,
				type: type,
				label: label,
				count: count
			};
			
			
			candidate.quality =
				evaluateSelectorCandidate(
					candidate
				);
			
			
			candidates.push(
				candidate
			);
	
		}
	
	
		/*
		 * 1. ID.
		 */
		if (element.id) {
	
			addCandidate(
				'#' + cssEscape(element.id),
				'id',
				__(
					'Unique ID',
					'igw-admin-cleanup'
				)
			);
	
		}
	
	
		/*
		 * 2. data-* attributes.
		 */
		Array.from(
			element.attributes
		).forEach(function (attribute) {
	
			if (
				!attribute.name.startsWith(
					'data-'
				)
			) {
				return;
			}
	
			if (!attribute.value) {
				return;
			}
	
			addCandidate(
				element.tagName.toLowerCase() +
					'[' +
					attribute.name +
					'="' +
					escapeAttributeValue(
						attribute.value
					) +
					'"]',
				'data',
				sprintf(
					__(
						'Attribute %s',
						'igw-admin-cleanup'
					),
					attribute.name
				)
			);
	
		});
	
	
		/*
		 * 3. Other useful attributes.
		 */
		const usefulAttributes = [
			'aria-label',
			'name',
			'role',
			'title'
		];
	
		usefulAttributes.forEach(
			function (attributeName) {
	
				if (
					!element.hasAttribute(
						attributeName
					)
				) {
					return;
				}
	
				const value =
					element.getAttribute(
						attributeName
					);
	
				if (!value) {
					return;
				}
	
				addCandidate(
					element.tagName.toLowerCase() +
						'[' +
						attributeName +
						'="' +
						escapeAttributeValue(value) +
						'"]',
					'attribute',
					sprintf(
						__(
							'Attribute %s',
							'igw-admin-cleanup'
						),
						attributeName
					)
				);
	
			}
		);
	
	
		/*
		 * 4. href.
		 */
		if (
			element.tagName.toLowerCase() === 'a' &&
			element.hasAttribute('href')
		) {
	
			const href =
				element.getAttribute('href');
	
			if (href) {
	
				addCandidate(
					'a[href="' +
						escapeAttributeValue(href) +
						'"]',
					'attribute',
					__(
						'Link URL',
						'igw-admin-cleanup'
					)
				);
	
			}
	
		}
	
	
		/*
		 * 5. Individual useful classes.
		 */
		Array.from(
			element.classList
		)
			.filter(isUsefulClass)
			.forEach(function (className) {
	
				addCandidate(
					'.' + cssEscape(className),
					'class',
					__(
						'CSS class',
						'igw-admin-cleanup'
					)
				);
	
			});
	
	
		/*
		 * 6. Combined class selector.
		 */
		const classSelector =
			generateClassSelector(element);
	
		if (classSelector) {
	
			addCandidate(
				classSelector,
				'classes',
				__(
					'Combined CSS classes',
					'igw-admin-cleanup'
				)
			);
	
		}
	
	
		/*
		 * 7. Context selector.
		 */
		const contextSelector =
			generateContextSelector(
				element
			);
	
		if (contextSelector) {
	
			addCandidate(
				contextSelector,
				'context',
				__(
					'Parent context',
					'igw-admin-cleanup'
				)
			);
	
		}
	
	
		/*
		 * 8. Full path as final fallback.
		 */
		const pathSelector =
			generatePathSelector(
				element
			);
	
		if (pathSelector) {
	
			addCandidate(
				pathSelector,
				'path',
				__(
					'DOM path',
					'igw-admin-cleanup'
				)
			);
	
		}
	
	
		/**
		 * Sort candidates.
		 *
		 * Unique selectors first, then by preferred type.
		 */
		const typePriority = {
			id: 1,
			data: 2,
			attribute: 3,
			classes: 4,
			class: 5,
			context: 6,
			path: 7
		};
	
	
		candidates.sort(
			function (a, b) {
		
				/*
				 * Unique selectors first.
				 */
				if (
					a.count === 1 &&
					b.count !== 1
				) {
					return -1;
				}
		
				if (
					b.count === 1 &&
					a.count !== 1
				) {
					return 1;
				}
		
		
				/*
				 * Higher quality first.
				 */
				if (
					a.quality &&
					b.quality &&
					a.quality.score !==
						b.quality.score
				) {
		
					return (
						b.quality.score -
						a.quality.score
					);
		
				}
		
		
				/*
				 * Selector type as fallback.
				 */
				return (
					(typePriority[a.type] || 99) -
					(typePriority[b.type] || 99)
				);
		
			}
		);
	
	
		/*
		 * Keep the list manageable.
		 */
		return candidates.slice(0, 5);
	
	}
	
	/**
	 * Evaluate selector quality.
	 *
	 * This is a heuristic, not a guarantee.
	 *
	 * @param {Object} candidate
	 *
	 * @return {Object}
	 */
	function evaluateSelectorCandidate(candidate) {
	
		let score = 50;
	
		let status = 'acceptable';
	
		const reasons = [];
	
		const selector =
			candidate.selector || '';
	
		const type =
			candidate.type || '';
	
		const count =
			Number(candidate.count || 0);
	
	
		/*
		 * Match count.
		 */
		if (count === 1) {
	
			score += 25;
	
			reasons.push(
				__(
					'Matches a single element',
					'igw-admin-cleanup'
				)
			);
	
		} else if (count <= 3) {
	
			score += 5;
	
			reasons.push(
				__(
					'Matches a small number of elements',
					'igw-admin-cleanup'
				)
			);
	
		} else if (count <= 5) {
	
			score -= 10;
	
			reasons.push(
				__(
					'Matches multiple elements',
					'igw-admin-cleanup'
				)
			);
	
		} else {
	
			score -= 30;
	
			reasons.push(
				__(
					'Selector is too broad',
					'igw-admin-cleanup'
				)
			);
	
		}
	
	
		/*
		 * Selector type.
		 */
		switch (type) {
	
			case 'id':
	
				score += 25;
	
				reasons.push(
					__(
						'Uses an element ID',
						'igw-admin-cleanup'
					)
				);
	
				break;
	
	
			case 'data':
	
				score += 15;
	
				reasons.push(
					__(
						'Uses a data attribute',
						'igw-admin-cleanup'
					)
				);
	
				break;
	
	
			case 'attribute':
	
				score += 10;
	
				reasons.push(
					__(
						'Uses a semantic attribute',
						'igw-admin-cleanup'
					)
				);
	
				break;
	
	
			case 'classes':
	
				score += 5;
	
				reasons.push(
					__(
						'Uses combined CSS classes',
						'igw-admin-cleanup'
					)
				);
	
				break;
	
	
			case 'class':
	
				/*
				 * Neutral.
				 */
				reasons.push(
					__(
						'Uses a CSS class',
						'igw-admin-cleanup'
					)
				);
	
				break;
	
	
			case 'context':
	
				score -= 5;
	
				reasons.push(
					__(
						'Depends on parent context',
						'igw-admin-cleanup'
					)
				);
	
				break;
	
	
			case 'path':
	
				score -= 25;
	
				reasons.push(
					__(
						'Depends on DOM structure',
						'igw-admin-cleanup'
					)
				);
	
				break;
	
		}
	
	
		/*
		 * Structural selectors.
		 */
		if (
			selector.includes(':nth-child(') ||
			selector.includes(':nth-of-type(')
		) {
	
			score -= 25;
	
			reasons.push(
				__(
					'Uses positional selectors',
					'igw-admin-cleanup'
				)
			);
	
		}
	
	
		/*
		 * Very long selectors usually depend on
		 * too much DOM structure.
		 */
		if (selector.length > 150) {
	
			score -= 15;
	
			reasons.push(
				__(
					'Selector is unusually long',
					'igw-admin-cleanup'
				)
			);
	
		} else if (selector.length > 90) {
	
			score -= 5;
	
		}
	
	
		/*
		 * Detect values that look dynamically generated.
		 */
		if (
			/[a-f0-9]{16,}/i.test(selector)
		) {
	
			score -= 20;
	
			reasons.push(
				__(
					'Contains a value that may be dynamically generated',
					'igw-admin-cleanup'
				)
			);
	
		}
	
	
		/*
		 * UUID-like values.
		 */
		if (
			/[a-f0-9]{8}-[a-f0-9]{4}-[1-5a-f0-9]{4}-[89ab0-9a-f]{4}-[a-f0-9]{12}/i
				.test(selector)
		) {
	
			score -= 25;
	
			reasons.push(
				__(
					'Contains a UUID-like value',
					'igw-admin-cleanup'
				)
			);
	
		}
	
	
		/*
		 * Long numeric values may be IDs, timestamps
		 * or dynamically generated references.
		 */
		if (
			/\d{8,}/.test(selector)
		) {
	
			score -= 15;
	
			reasons.push(
				__(
					'Contains a long numeric value',
					'igw-admin-cleanup'
				)
			);
	
		}
	
	
		/*
		 * Limit score.
		 */
		score =
			Math.max(
				0,
				Math.min(
					100,
					score
				)
			);
	
	
		/*
		 * Human-readable state.
		 */
		if (score >= 80) {
	
			status = 'good';
	
		} else if (score >= 55) {
	
			status = 'acceptable';
	
		} else {
	
			status = 'fragile';
	
		}
	
	
		return {
			score: score,
			status: status,
			reasons: reasons
		};
	
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
		selector,
		candidates = []
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
		
		
		
		let candidatesHtml = '';
		
		if (candidates.length) {
			
			
		
			candidatesHtml = `
				<div class="igw-selector-result__field">
		
					<span class="igw-selector-result__label">
						${escapeHtml(
							__(
								'Suggested alternatives',
								'igw-admin-cleanup'
							)
						)}
					</span>
		
					<div class="igw-selector-candidates">
		
						${candidates
							.map(function (candidate) {
								
								const quality =
									candidate.quality || {
										score: 0,
										status: 'acceptable',
										reasons: []
									};
								
								
								let qualityLabel = '';
								
								switch (quality.status) {
								
									case 'good':
								
										qualityLabel =
											__(
												'Good option',
												'igw-admin-cleanup'
											);
								
										break;
								
								
									case 'fragile':
								
										qualityLabel =
											__(
												'Fragile',
												'igw-admin-cleanup'
											);
								
										break;
								
								
									default:
								
										qualityLabel =
											__(
												'Acceptable',
												'igw-admin-cleanup'
											);
								
										break;
								
								}
		
								let statusClass =
									'multiple';
		
								if (candidate.count === 1) {
		
									statusClass =
										'precise';
		
								} else if (
									candidate.count > 5
								) {
		
									statusClass =
										'broad';
		
								}
		
		
								const matchText =
									candidate.count === 1
										? __(
											'1 matching element',
											'igw-admin-cleanup'
										)
										: sprintf(
											__(
												'%d matching elements',
												'igw-admin-cleanup'
											),
											candidate.count
										);
								const qualityTitle =
								quality.reasons.join(' · ');
		
								return `
									<button
										type="button"
										class="igw-selector-candidate"
										data-selector="${escapeHtmlAttribute(
											candidate.selector
										)}"
										title="${escapeHtmlAttribute(
											qualityTitle
										)}"
									>
		
										<span
											class="igw-selector-candidate__selector"
										>
											${escapeHtml(
												candidate.selector
											)}
										</span>
		
										<span
											class="igw-selector-candidate__meta"
										>
		
											<span>
												${escapeHtml(
													candidate.label
												)}
											</span>
		
											<span
												class="igw-selector-candidate__status
												igw-selector-candidate__status--${statusClass}"
											>
												${escapeHtml(
													matchText
												)}
											</span>
											<span
												class="igw-selector-candidate__quality
												igw-selector-candidate__quality--${quality.status}"
											>
												${escapeHtml(
													qualityLabel
												)}
											</span>
		
										</span>
		
									</button>
								`;
		
							})
							.join('')}
		
					</div>
		
				</div>
			`;
		
		}

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
					${candidatesHtml}
					<div
						class="igw-selector-result__matches"
					>
					</div>

				</div>
				
				<div class="igw-selector-result__field">
				
					<label
						class="igw-selector-result__label"
						for="igw-selector-result-action"
					>
						${escapeHtml(
							__(
								'Action',
								'igw-admin-cleanup'
							)
						)}
					</label>
				
					<select
						id="igw-selector-result-action"
						class="widefat"
					>
				
						<option value="element">
							${escapeHtml(
								__(
									'Hide selected element',
									'igw-admin-cleanup'
								)
							)}
						</option>
				
						<option value="parent">
							${escapeHtml(
								__(
									'Hide direct parent',
									'igw-admin-cleanup'
								)
							)}
						</option>
				
						<option value="closest_li">
							${escapeHtml(
								__(
									'Hide closest <li>',
									'igw-admin-cleanup'
								)
							)}
						</option>
				
						<option value="remove">
							${escapeHtml(
								__(
									'Remove element',
									'igw-admin-cleanup'
								)
							)}
						</option>
				
					</select>
				
				</div>

			</div>
			

			<div class="igw-selector-result__footer">
			
				<button
					type="button"
					class="button igw-selector-result__preview"
				>
					${escapeHtml(
						__(
							'Preview',
							'igw-admin-cleanup'
						)
					)}
				</button>
			
				<button
					type="button"
					class="button igw-selector-result__restore"
					hidden
				>
					${escapeHtml(
						__(
							'Restore',
							'igw-admin-cleanup'
						)
					)}
				</button>
			
				<button
					type="button"
					class="button igw-selector-result__cancel"
				>
					${escapeHtml(
						__(
							'Cancel',
							'igw-admin-cleanup'
						)
					)}
				</button>
			
				<button
					type="button"
					class="button button-primary igw-selector-result__use"
				>
					${escapeHtml(
						__(
							'Use selector',
							'igw-admin-cleanup'
						)
					)}
				</button>
			
			</div>
		`;


		document.body.appendChild(
			resultPanel
		);
		
		const candidateButtons =
		resultPanel.querySelectorAll(
			'.igw-selector-candidate'
		);
		
		candidateButtons.forEach(
			function (button) {
		
				button.addEventListener(
					'click',
					function () {
		
						const candidateSelector =
							button.dataset.selector;
		
						if (!candidateSelector) {
							return;
						}
		
		
						const input =
							resultPanel.querySelector(
								'#igw-selector-result-value'
							);
		
						if (!input) {
							return;
						}
		
		
						/*
						 * Restore any active preview.
						 */
						restorePreview();
		
		
						/*
						 * Apply candidate.
						 */
						input.value =
							candidateSelector;
		
		
						/*
						 * Recalculate matches.
						 */
						updateMatchStatus(
							candidateSelector
						);
		
		
						/*
						 * Reset preview controls.
						 */
						if (previewButton) {
							previewButton.hidden = false;
						}
		
						if (restoreButton) {
							restoreButton.hidden = true;
						}
		
		
						/*
						 * Update visual selected state.
						 */
						candidateButtons.forEach(
							function (candidateButton) {
		
								candidateButton.classList.remove(
									'is-selected'
								);
		
							}
						);
		
		
						button.classList.add(
							'is-selected'
						);
		
					}
				);
		
			}
		);
		
		candidateButtons.forEach(
			function (button) {
		
				if (
					button.dataset.selector ===
					selector
				) {
		
					button.classList.add(
						'is-selected'
					);
		
				}
		
			}
		);
		
		/*
		 * Show initial selector match information.
		 */
		updateMatchStatus(selector);


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
		
		const selectorInput =
			resultPanel.querySelector(
				'#igw-selector-result-value'
			);
		
		const previewButton =
			resultPanel.querySelector(
				'.igw-selector-result__preview'
			);
		
		const restoreButton =
			resultPanel.querySelector(
				'.igw-selector-result__restore'
			);
			
		const actionSelect =
		resultPanel.querySelector(
			'#igw-selector-result-action'
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
		
		if (previewButton) {
		
			previewButton.addEventListener(
				'click',
				function () {
		
					const input =
						resultPanel.querySelector(
							'#igw-selector-result-value'
						);
		
					if (!input) {
						return;
					}
		
		
					const action =
						actionSelect
							? actionSelect.value
							: 'element';
					
					
					previewSelector(
						input.value.trim(),
						action
					);
		
		
					previewButton.hidden = true;
		
					if (restoreButton) {
						restoreButton.hidden = false;
					}
		
				}
			);
		
		}
		
		if (restoreButton) {
		
			restoreButton.addEventListener(
				'click',
				function () {
		
					restorePreview();
		
					restoreButton.hidden = true;
		
					if (previewButton) {
						previewButton.hidden = false;
					}
		
				}
			);
		
		}
		
		if (selectorInput) {
		
			selectorInput.addEventListener(
				'input',
				function () {
		
					/*
					 * Restore any active preview before
					 * evaluating the new selector.
					 */
					restorePreview();
		
		
					/*
					 * Reset preview controls.
					 */
					if (previewButton) {
						previewButton.hidden = false;
					}
		
					if (restoreButton) {
						restoreButton.hidden = true;
					}
		
		
					/*
					 * Recalculate matches.
					 */
					updateMatchStatus(
						selectorInput.value.trim()
					);
		
				}
			);
		
		}
		
		if (actionSelect) {
		
			actionSelect.addEventListener(
				'change',
				function () {
		
					restorePreview();
		
		
					if (previewButton) {
						previewButton.hidden = false;
					}
		
		
					if (restoreButton) {
						restoreButton.hidden = true;
					}
		
				}
			);
		
		}

	}
	
	/**
	 * Update selector match information.
	 *
	 * @param {string} selector
	 */
	function updateMatchStatus(selector) {
	
		if (!resultPanel) {
			return;
		}
	
		const matchesBox =
			resultPanel.querySelector(
				'.igw-selector-result__matches'
			);
	
		if (!matchesBox) {
			return;
		}
	
		let matchCount = 0;
		let matchStatus = 'precise';
		let matchLabel = '';
	
		try {
	
			matchCount =
				document.querySelectorAll(
					selector
				).length;
	
		} catch (error) {
	
			matchCount = 0;
			matchStatus = 'error';
	
			matchLabel =
				__(
					'Invalid selector',
					'igw-admin-cleanup'
				);
	
		}
	
	
		if (matchStatus !== 'error') {
	
			if (matchCount === 0) {
	
				matchStatus = 'error';
	
				matchLabel =
					__(
						'No matching elements',
						'igw-admin-cleanup'
					);
	
			} else if (matchCount === 1) {
	
				matchStatus = 'precise';
	
				matchLabel =
					__(
						'1 matching element',
						'igw-admin-cleanup'
					);
	
			} else if (matchCount <= 5) {
	
				matchStatus = 'multiple';
	
				matchLabel = sprintf(
					__(
						'%d matching elements',
						'igw-admin-cleanup'
					),
					matchCount
				);
	
			} else {
	
				matchStatus = 'broad';
	
				matchLabel = sprintf(
					__(
						'%d matching elements',
						'igw-admin-cleanup'
					),
					matchCount
				);
	
			}
	
		}
	
	
		matchesBox.className =
			'igw-selector-result__matches ' +
			'igw-selector-result__matches--' +
			matchStatus;
	
	
		matchesBox.textContent =
			matchLabel;
	
	}

	/**
	 * Use selected selector.
	 *
	 * If we are already on the IGW Admin Cleanup page,
	 * open the rule form directly.
	 *
	 * Otherwise redirect to the plugin page with the
	 * selected selector as a URL parameter.
	 */
	function useSelectedSelector() {
	
		if (!resultPanel) {
			return;
		}
	
	
		/*
		 * Selected CSS selector.
		 */
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
	
	
		/*
		 * Selected cleanup action.
		 */
		const actionSelect =
			resultPanel.querySelector(
				'#igw-selector-result-action'
			);
	
	
		const selectedAction =
			actionSelect
				? actionSelect.value
				: 'element';
	
	
		/*
		 * Check whether we are already on
		 * the IGW Admin Cleanup page.
		 */
		const ruleForm =
			document.getElementById(
				'igw-admin-cleaner-rule-form'
			);
	
	
		const selectorInput =
			document.getElementById(
				'igw_rule_selector'
			);
	
	
		const actionInput =
			document.getElementById(
				'igw_rule_action'
			);
	
	
		/*
		 * We are already on the plugin page.
		 */
		if (
			ruleForm &&
			selectorInput
		) {
	
			selectorInput.value =
				selector;
	
	
			if (actionInput) {
	
				actionInput.value =
					selectedAction;
	
			}
	
	
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
	
	
		/*
		 * Pass selector.
		 */
		url.searchParams.set(
			'igw_selector',
			selector
		);
	
	
		/*
		 * Pass selected element text as proposed rule name.
		 */
		if (selectedElementText) {
	
			url.searchParams.set(
				'igw_name',
				selectedElementText
			);
	
		}
	
	
		/*
		 * Pass selected action.
		 */
		url.searchParams.set(
			'igw_action',
			selectedAction
		);
	
	
		/*
		 * Restore any active preview before leaving.
		 */
		restorePreview();
	
	
		window.location.href =
			url.toString();
	
	}


	/**
	 * Remove result panel.
	 */
	function removeResultPanel() {

		restorePreview();
		
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
	 * Preview selector effect using the selected action.
	 *
	 * @param {string} selector
	 * @param {string} action
	 */
	function previewSelector(
		selector,
		action
	) {
	
		restorePreview();
	
	
		if (!selector) {
			return;
		}
	
	
		let elements;
	
	
		try {
	
			elements =
				document.querySelectorAll(
					selector
				);
	
		} catch (error) {
	
			return;
	
		}
	
	
		const previewTargets = [];
	
	
		elements.forEach(function (element) {
	
			let target = element;
	
	
			switch (action) {
	
				case 'parent':
	
					if (element.parentElement) {
						target =
							element.parentElement;
					}
	
					break;
	
	
				case 'closest_li':
	
					const listItem =
						element.closest('li');
	
					if (listItem) {
						target = listItem;
					}
	
					break;
	
	
				case 'remove':
	
					/*
					 * Preview only.
					 * We simulate removal using display:none
					 * so the DOM can be safely restored.
					 */
					target = element;
	
					break;
	
	
				case 'element':
				default:
	
					target = element;
	
					break;
	
			}
	
	
			if (
				!target ||
				previewTargets.includes(target)
			) {
				return;
			}
	
	
			previewTargets.push(target);
	
	
			previewElements.push({
				element: target,
				display:
					target.style.display,
				priority:
					target.style.getPropertyPriority(
						'display'
					)
			});
	
	
			target.style.setProperty(
				'display',
				'none',
				'important'
			);
	
		});
	
	}
	
	function restorePreview() {
	
		previewElements.forEach(function (item) {
	
			if (!item.element) {
				return;
			}
	
	
			if (item.display) {
	
				item.element.style.setProperty(
					'display',
					item.display,
					item.priority
				);
	
			} else {
	
				item.element.style.removeProperty(
					'display'
				);
	
			}
	
		});
	
	
		previewElements.length = 0;
	
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