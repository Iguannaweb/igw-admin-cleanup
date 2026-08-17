<?php

if (!defined('ABSPATH')) {
	exit;
}


/**
 * Admin interface for IGW Admin Cleaner.
 */
class IGW_Admin_Cleaner_Admin_Page
{
	/**
	 * Menu slug.
	 */
	const MENU_SLUG = 'igw-admin-cleaner';


	/**
	 * Initialize admin hooks.
	 *
	 * @return void
	 */
	public function init()
	{
		add_action(
			'admin_menu',
			[$this, 'register_menu']
		);

		add_action(
			'admin_post_igw_admin_cleaner_save_rule',
			[$this, 'handle_save_rule']
		);

		add_action(
			'admin_post_igw_admin_cleaner_delete_rule',
			[$this, 'handle_delete_rule']
		);

		add_action(
			'admin_post_igw_admin_cleaner_toggle_rule',
			[$this, 'handle_toggle_rule']
		);
		
		add_action(
			'admin_enqueue_scripts',
			[$this, 'enqueue_assets']
		);
		
		
	}


	/**
	 * Register plugin settings page.
	 *
	 * @return void
	 */
	public function register_menu()
	{
		add_options_page(
			__('IGW Admin Cleanup', 'igw-admin-cleaner'),
			__('IGW Admin Cleanup', 'igw-admin-cleaner'),
			'manage_options',
			self::MENU_SLUG,
			[$this, 'render_page']
		);
	}


	/**
	 * Render main admin page.
	 *
	 * @return void
	 */
	public function render_page()
	{
		if (!current_user_can('manage_options')) {
			return;
		}

		$rules = IGW_Admin_Cleaner_Rules::get_all();

		$editing_rule = null;

		if (
			isset($_GET['action'], $_GET['rule']) &&
			$_GET['action'] === 'edit'
		) {
			$rule_id = sanitize_key(
				wp_unslash($_GET['rule'])
			);

			$editing_rule = IGW_Admin_Cleaner_Rules::get(
				$rule_id
			);
		}
		
		$captured_selector = isset($_GET['igw_selector'])
		? wp_unslash($_GET['igw_selector'])
		: '';
		
		$captured_name = isset($_GET['igw_name'])
		? sanitize_text_field(
			wp_unslash($_GET['igw_name'])
		)
		: '';
		
		$form_class = (
			$editing_rule ||
			!empty($captured_selector)
		)
			? 'is-open'
			: 'is-close';

		?>
		<div class="wrap igw-admin-cleaner-wrap">
		
		<div class="igw-admin-cleaner-header">
			
			<div class="igw-admin-cleaner-header__content">
				
				<h1>
					<?php esc_html_e(
						'IGW Admin Cleanup',
						'igw-admin-cleanup'
					); ?>
				</h1>
		
				<p>
					<?php esc_html_e(
						'Manage cleanup rules and keep your WordPress admin area free from unnecessary elements.',
						'igw-admin-cleanup'
					); ?>
				</p>
				<?php
					$total_rules = count($rules);
					
					$active_rules = count(
						array_filter(
							$rules,
							function ($rule) {
								return !empty($rule['enabled']);
							}
						)
					);
					
					$detected_rules = count(
						array_filter(
							$rules,
							function ($rule) {
								return !empty($rule['last_seen']);
							}
						)
					);
					
					$never_seen = $total_rules - $detected_rules;
				?>
				<div class="igw-admin-cleaner-summary">
				
					<div class="igw-admin-cleaner-summary__card">
						<span class="igw-admin-cleaner-summary__value">
							<?php echo esc_html($total_rules); ?>
						</span>
				
						<span class="igw-admin-cleaner-summary__label">
							<?php esc_html_e(
								'Total rules',
								'igw-admin-cleanup'
							); ?>
						</span>
					</div>
				
				
					<div class="igw-admin-cleaner-summary__card">
						<span class="igw-admin-cleaner-summary__value">
							<?php echo esc_html($active_rules); ?>
						</span>
				
						<span class="igw-admin-cleaner-summary__label">
							<?php esc_html_e(
								'Active rules',
								'igw-admin-cleanup'
							); ?>
						</span>
					</div>
				
				
					<div class="igw-admin-cleaner-summary__card">
						<span class="igw-admin-cleaner-summary__value">
							<?php echo esc_html($detected_rules); ?>
						</span>
				
						<span class="igw-admin-cleaner-summary__label">
							<?php esc_html_e(
								'Detected rules',
								'igw-admin-cleanup'
							); ?>
						</span>
					</div>
				
				
					<div class="igw-admin-cleaner-summary__card">
						<span class="igw-admin-cleaner-summary__value">
							<?php echo esc_html($never_seen); ?>
						</span>
				
						<span class="igw-admin-cleaner-summary__label">
							<?php esc_html_e(
								'Never detected',
								'igw-admin-cleanup'
							); ?>
						</span>
					</div>
				
				</div>					
			</div>
			
			<div class="igw-admin-cleaner-header__actions">
			
				<button
					type="button"
					class="button button-primary"
					id="igw-admin-cleaner-add-rule"
				>
					<?php esc_html_e(
						'Add rule',
						'igw-admin-cleanup'
					); ?>
				</button>
				
				<button
					type="button"
					class="button"
					id="igw-admin-cleaner-select-element"
				>
					<?php esc_html_e(
						'Select element',
						'igw-admin-cleanup'
					); ?>
				</button>
			
			</div>
		
		</div>

			<?php $this->render_notices(); ?>
								
			<div
				id="igw-admin-cleaner-rule-form"
				class="igw-admin-cleaner-panel igw-admin-cleaner-rule-form-panel <?php echo esc_attr($form_class); ?>" 
			>
				<div class="igw-admin-cleaner-panel">
			
				<div class="igw-admin-cleaner-panel__header">
					<h2>
						<?php
						echo $editing_rule
							? esc_html__('Edit rule', 'igw-admin-cleanup')
							: esc_html__('Add new rule', 'igw-admin-cleanup');
						?>
					</h2>
				</div>
			
				<div class="igw-admin-cleaner-panel__body">
			
					<?php
					$this->render_rule_form(
						$editing_rule,
						$captured_selector,
						$captured_name
					);
					?>
			
				</div>
			
				</div>
			
			</div>

			<div class="igw-admin-cleaner-panel">
			
				<div class="igw-admin-cleaner-panel__header">
			
					<h2>
						<?php esc_html_e(
							'Cleanup rules',
							'igw-admin-cleanup'
						); ?>
					</h2>
			
				</div>
			
				<div class="igw-admin-cleaner-panel__body">
					
					<div class="igw-admin-cleaner-filters">
					
						<div class="igw-admin-cleaner-filters__search">
					
							<input
								type="search"
								id="igw-rule-search"
								placeholder="<?php esc_attr_e(
									'Search rules...',
									'igw-admin-cleanup'
								); ?>"
							>
					
						</div>
					
					
						<div class="igw-admin-cleaner-filters__select">
					
							<select id="igw-rule-status-filter">
					
								<option value="">
									<?php esc_html_e(
										'All statuses',
										'igw-admin-cleanup'
									); ?>
								</option>
					
								<option value="active">
									<?php esc_html_e(
										'Active',
										'igw-admin-cleanup'
									); ?>
								</option>
					
								<option value="disabled">
									<?php esc_html_e(
										'Disabled',
										'igw-admin-cleanup'
									); ?>
								</option>
					
							</select>
					
						</div>
					
					
						<div class="igw-admin-cleaner-filters__select">
					
							<select id="igw-rule-detection-filter">
					
								<option value="">
									<?php esc_html_e(
										'All detections',
										'igw-admin-cleanup'
									); ?>
								</option>
					
								<option value="recent">
									<?php esc_html_e(
										'Detected recently',
										'igw-admin-cleanup'
									); ?>
								</option>
					
								<option value="never">
									<?php esc_html_e(
										'Never detected',
										'igw-admin-cleanup'
									); ?>
								</option>
					
								<option value="warning">
									<?php esc_html_e(
										'Not seen recently',
										'igw-admin-cleanup'
									); ?>
								</option>
					
							</select>
					
						</div>
					
					
						<div class="igw-admin-cleaner-filters__select">
					
							<select id="igw-rule-plugin-filter">
					
								<option value="">
									<?php esc_html_e(
										'All plugins',
										'igw-admin-cleanup'
									); ?>
								</option>
					
								<?php
								$plugins = [];
					
								foreach ($rules as $rule) {
					
									if (!empty($rule['source'])) {
										$plugins[] = $rule['source'];
									}
								}
					
								$plugins = array_unique($plugins);
					
								sort(
									$plugins,
									SORT_NATURAL | SORT_FLAG_CASE
								);
					
								foreach ($plugins as $plugin) :
									?>
					
									<option value="<?php echo esc_attr(
										strtolower($plugin)
									); ?>">
										<?php echo esc_html($plugin); ?>
									</option>
					
								<?php endforeach; ?>
					
							</select>
					
						</div>
						
						<button
							type="button"
							id="igw-rule-reset-filters"
							class="button igw-admin-cleaner-reset-filters"
							hidden
						>
							<?php esc_html_e(
								'Reset filters',
								'igw-admin-cleanup'
							); ?>
						</button>
					
					
						<div class="igw-admin-cleaner-filters__count">
						
							<span id="igw-rule-visible-count">
								<?php echo esc_html(count($rules)); ?>
							</span>
						
							<?php esc_html_e(
								'of',
								'igw-admin-cleanup'
							); ?>
						
							<span id="igw-rule-total-count">
								<?php echo esc_html(count($rules)); ?>
							</span>
						
							<?php esc_html_e(
								'rules',
								'igw-admin-cleanup'
							); ?>
						
						</div>
					
					</div>
			
					<?php
					if (empty($rules)) {
			
						?>
						<div class="igw-admin-cleaner-empty">
			
							<h3>
								<?php esc_html_e(
									'No cleanup rules yet',
									'igw-admin-cleanup'
								); ?>
							</h3>
			
							<p>
								<?php esc_html_e(
									'Create your first rule to start cleaning unnecessary elements from the WordPress admin area.',
									'igw-admin-cleanup'
								); ?>
							</p>
			
						</div>
						<?php
			
					} else {
			
						$this->render_rules_table($rules);
			
					}
					?>
			
				</div>
			
			</div>

		</div>
		<?php
	}


	/**
	 * Render rule form.
	 *
	 * @param array|null $rule Existing rule.
	 * @return void
	 */
	private function render_rule_form($rule = null,$captured_selector = '',$captured_name = '')
	{
		$rule_id = $rule['id'] ?? '';

		$name = $rule['name']
		?? $captured_name;

		$selector = $rule['selector']
		?? $captured_selector;

		$source = $rule['source'] ?? '';

		$source_slug = $rule['source_slug'] ?? '';

		$action = $rule['action']
			?? IGW_Admin_Cleaner_Rules::ACTION_ELEMENT;

		$enabled = isset($rule['enabled'])
			? (bool) $rule['enabled']
			: true;

		?>
		<form
			method="post"
			action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
		>

			<input
				type="hidden"
				name="action"
				value="igw_admin_cleaner_save_rule"
			>

			<input
				type="hidden"
				name="rule_id"
				value="<?php echo esc_attr($rule_id); ?>"
			>

			<?php
			wp_nonce_field(
				'igw_admin_cleaner_save_rule',
				'igw_admin_cleaner_nonce'
			);
			?>

			<table class="form-table">

				<tr>
					<th scope="row">
						<label for="igw_rule_name">
							<?php esc_html_e('Name', 'igw-admin-cleanup'); ?>
						</label>
					</th>

					<td>
						<input
							type="text"
							id="igw_rule_name"
							name="name"
							class="regular-text"
							value="<?php echo esc_attr($name); ?>"
						>

						<p class="description">
							<?php esc_html_e(
								'A descriptive name to identify the rule.',
								'igw-admin-cleanup'
							); ?>
						</p>
					</td>
				</tr>


				<tr>
					<th scope="row">
						<label for="igw_rule_selector">
							<?php esc_html_e('CSS selector', 'igw-admin-cleanup'); ?>
						</label>
					</th>

					<td>
						<input
							type="text"
							id="igw_rule_selector"
							name="selector"
							class="regular-text code"
							value="<?php echo esc_attr($selector); ?>"
							required
						>

						<p class="description">
							<?php esc_html_e(
								'For example: #wfMenuCallout or .plugin-upgrade-banner',
								'igw-admin-cleanup'
							); ?>
						</p>
					</td>
				</tr>


				<tr>
					<th scope="row">
						<label for="igw_rule_source">
							<?php esc_html_e('Plugin / source', 'igw-admin-cleanup'); ?>
						</label>
					</th>

					<td>
						<input
							type="text"
							id="igw_rule_source"
							name="source"
							class="regular-text"
							value="<?php echo esc_attr($source); ?>"
						>

						<p class="description">
							<?php esc_html_e(
								'Optional. Example: Wordfence, Elementor, Rank Math...',
								'igw-admin-cleanup'
							); ?>
						</p>
					</td>
				</tr>


				<tr>
					<th scope="row">
						<label for="igw_rule_source_slug">
							<?php esc_html_e('Source slug', 'igw-admin-cleanup'); ?>
						</label>
					</th>

					<td>
						<input
							type="text"
							id="igw_rule_source_slug"
							name="source_slug"
							class="regular-text"
							value="<?php echo esc_attr($source_slug); ?>"
						>
					</td>
				</tr>


				<tr>
					<th scope="row">
						<label for="igw_rule_action">
							<?php esc_html_e('Action', 'igw-admin-cleanup'); ?>
						</label>
					</th>

					<td>
						<select
							id="igw_rule_action"
							name="cleanup_action"
						>
							<option
								value="element"
								<?php selected($action, 'element'); ?>
							>
								<?php esc_html_e(
									'Hide selected element',
									'igw-admin-cleanup'
								); ?>
							</option>

							<option
								value="parent"
								<?php selected($action, 'parent'); ?>
							>
								<?php esc_html_e(
									'Hide direct parent',
									'igw-admin-cleanup'
								); ?>
							</option>

							<option
								value="closest_li"
								<?php selected($action, 'closest_li'); ?>
							>
								<?php esc_html_e(
									'Hide closest <li>',
									'igw-admin-cleanup'
								); ?>
							</option>

							<option
								value="remove"
								<?php selected($action, 'remove'); ?>
							>
								<?php esc_html_e(
									'Remove element',
									'igw-admin-cleanup'
								); ?>
							</option>
						</select>
					</td>
				</tr>


				<tr>
					<th scope="row">
						<?php esc_html_e('Status', 'igw-admin-cleanup'); ?>
					</th>

					<td>
						<label>
							<input
								type="checkbox"
								name="enabled"
								value="1"
								<?php checked($enabled); ?>
							>

							<?php esc_html_e(
								'Enable this rule',
								'igw-admin-cleanup'
							); ?>
						</label>
					</td>
				</tr>

			</table>

			<?php
			submit_button(
				$rule
					? __('Update rule', 'igw-admin-cleanup')
					: __('Add rule', 'igw-admin-cleanup')
			);
			?>

			<?php if ($rule) : ?>

				<a
					href="<?php echo esc_url(
						admin_url(
							'options-general.php?page=' . self::MENU_SLUG
						)
					); ?>"
					class="button"
				>
					<?php esc_html_e(
						'Cancel',
						'igw-admin-cleanup'
					); ?>
				</a>

			<?php endif; ?>

		</form>
		<?php
	}


	/**
	 * Render rules table.
	 *
	 * @param array $rules Rules.
	 * @return void
	 */
	private function render_rules_table($rules)
	{
		?>
		<table class="igw-admin-cleaner-rules">
			
			<thead>
				<tr>
					<th><?php esc_html_e('Status', 'igw-admin-cleanup'); ?></th>
					<th><?php esc_html_e('Name', 'igw-admin-cleanup'); ?></th>
					<th><?php esc_html_e('Selector', 'igw-admin-cleanup'); ?></th>
					<th><?php esc_html_e('Action', 'igw-admin-cleanup'); ?></th>
					<th class="igw-col-detection">
						<?php esc_html_e(
							'Detection',
							'igw-admin-cleanup'
						); ?>
					</th>
					<th><?php esc_html_e('Actions', 'igw-admin-cleanup'); ?></th>
				</tr>
			</thead>

			<tbody>

			<?php foreach ($rules as $rule_id => $rule) : ?>
				<?php
					$enabled = !empty($rule['enabled'])
						? 'active'
						: 'disabled';
					
					$last_seen = isset($rule['last_seen'])
						? (int) $rule['last_seen']
						: 0;
					
					if (!$last_seen) {
					
						$detection = 'never';
					
					} elseif (
						(time() - $last_seen)
						> DAY_IN_SECONDS * 30
					) {
					
						$detection = 'warning';
					
					} else {
					
						$detection = 'recent';
					}
					
					$search_text = strtolower(
						implode(
							' ',
							[
								$rule['name'] ?? '',
								$rule['selector'] ?? '',
								$rule['source'] ?? '',
								$rule['source_slug'] ?? '',
							]
						)
					);
					
					$plugin_filter = !empty($rule['source'])
						? strtolower($rule['source'])
						: '';
					
				?>
										
				<tr
					class="igw-rule-row"
					data-status="<?php echo esc_attr($enabled); ?>"
					data-detection="<?php echo esc_attr($detection); ?>"
					data-plugin="<?php echo esc_attr($plugin_filter); ?>"
					data-search="<?php echo esc_attr($search_text); ?>"
				>

					<td>
						<?php if (!empty($rule['enabled'])) : ?>
						
							<span class="igw-admin-cleaner-badge igw-admin-cleaner-badge--active">
								<?php esc_html_e(
									'Active',
									'igw-admin-cleanup'
								); ?>
							</span>
						
						<?php else : ?>
						
							<span class="igw-admin-cleaner-badge igw-admin-cleaner-badge--disabled">
								<?php esc_html_e(
									'Disabled',
									'igw-admin-cleanup'
								); ?>
							</span>
						
						<?php endif; ?>
					</td>


					<td>
						<span class="igw-admin-cleaner-rule-name">
							<?php echo esc_html($rule['name'] ?? ''); ?>
						</span>
					
						<?php if (!empty($rule['source'])) : ?>
					
							<span class="igw-admin-cleaner-rule-source">
								<?php echo esc_html($rule['source']); ?>
							</span>
					
						<?php endif; ?>
					</td>


					<td>
						<code
							class="igw-admin-cleaner-selector"
							title="<?php echo esc_attr($rule['selector'] ?? ''); ?>"
						>
							<?php echo esc_html($rule['selector'] ?? ''); ?>
						</code>
					</td>


					


					<td>
						<?php
						echo esc_html(
							$this->get_action_label(
								$rule['action'] ?? ''
							)
						);
						?>
					</td>
					
					<td class="igw-col-detection">
						<?php
						$this->render_detection_status($rule);
						?>
					</td>


					<td>
					
						<div class="igw-admin-cleaner-actions">
					
							<a
								href="<?php echo esc_url(
									add_query_arg(
										[
											'page'   => self::MENU_SLUG,
											'action' => 'edit',
											'rule'   => $rule_id,
										],
										admin_url('options-general.php')
									)
								); ?>"
								class="button button-small igw-action-button"
							>
								<?php esc_html_e(
									'Edit',
									'igw-admin-cleanup'
								); ?>
							</a>
					
					
							<a
								href="<?php echo esc_url(
									wp_nonce_url(
										add_query_arg(
											[
												'action'  => 'igw_admin_cleaner_toggle_rule',
												'rule_id' => $rule_id,
											],
											admin_url('admin-post.php')
										),
										'igw_admin_cleaner_toggle_rule'
									)
								); ?>"
								class="button button-small igw-action-button"
							>
								<?php
								echo !empty($rule['enabled'])
									? esc_html__(
										'Disable',
										'igw-admin-cleanup'
									)
									: esc_html__(
										'Enable',
										'igw-admin-cleanup'
									);
								?>
							</a>
					
					
							<a
								href="<?php echo esc_url(
									wp_nonce_url(
										add_query_arg(
											[
												'action'  => 'igw_admin_cleaner_delete_rule',
												'rule_id' => $rule_id,
											],
											admin_url('admin-post.php')
										),
										'igw_admin_cleaner_delete_rule'
									)
								); ?>"
								class="button button-small igw-action-button igw-action-button--delete"
								onclick="return confirm('<?php echo esc_js(
									__(
										'Are you sure you want to delete this rule?',
										'igw-admin-cleanup'
									)
								); ?>');"
							>
								<?php esc_html_e(
									'Delete',
									'igw-admin-cleanup'
								); ?>
							</a>
					
						</div>
					
					</td>

				</tr>

			<?php endforeach; ?>

			</tbody>

		</table>
		<?php
	}


	/**
	 * Save rule request.
	 *
	 * @return void
	 */
	public function handle_save_rule()
	{
		if (!current_user_can('manage_options')) {
			wp_die(
				esc_html__(
					'You do not have permission to perform this action.',
					'igw-admin-cleanup'
				)
			);
		}

		check_admin_referer(
			'igw_admin_cleaner_save_rule',
			'igw_admin_cleaner_nonce'
		);

		$rule_id = isset($_POST['rule_id'])
			? sanitize_key(wp_unslash($_POST['rule_id']))
			: '';

		$data = [
			'name' => isset($_POST['name'])
				? sanitize_text_field(wp_unslash($_POST['name']))
				: '',

			'selector' => isset($_POST['selector'])
				? wp_unslash($_POST['selector'])
				: '',

			'source' => isset($_POST['source'])
				? sanitize_text_field(wp_unslash($_POST['source']))
				: '',

			'source_slug' => isset($_POST['source_slug'])
				? sanitize_key(wp_unslash($_POST['source_slug']))
				: '',

			'action' => isset($_POST['cleanup_action'])
				? sanitize_key(wp_unslash($_POST['cleanup_action']))
				: 'element',

			'enabled' => isset($_POST['enabled']),
		];


		if ($rule_id) {
			$result = IGW_Admin_Cleaner_Rules::update(
				$rule_id,
				$data
			);

			$message = 'updated';

		} else {
			$result = IGW_Admin_Cleaner_Rules::create(
				$data
			);

			$message = 'created';
		}


		if (is_wp_error($result)) {
			$this->redirect_with_message(
				'error',
				$result->get_error_message()
			);
		}


		$this->redirect_with_message(
			$message
		);
	}


	/**
	 * Delete rule request.
	 *
	 * @return void
	 */
	public function handle_delete_rule()
	{
		if (!current_user_can('manage_options')) {
			wp_die(
				esc_html__(
					'You do not have permission to perform this action.',
					'igw-admin-cleanup'
				)
			);
		}

		check_admin_referer(
			'igw_admin_cleaner_delete_rule'
		);

		$rule_id = isset($_GET['rule_id'])
			? sanitize_key(wp_unslash($_GET['rule_id']))
			: '';

		if ($rule_id) {
			IGW_Admin_Cleaner_Rules::delete($rule_id);
		}

		$this->redirect_with_message(
			'deleted'
		);
	}


	/**
	 * Enable / disable rule request.
	 *
	 * @return void
	 */
	public function handle_toggle_rule()
	{
		if (!current_user_can('manage_options')) {
			wp_die(
				esc_html__(
					'You do not have permission to perform this action.',
					'igw-admin-cleanup'
				)
			);
		}

		check_admin_referer(
			'igw_admin_cleaner_toggle_rule'
		);

		$rule_id = isset($_GET['rule_id'])
			? sanitize_key(wp_unslash($_GET['rule_id']))
			: '';

		$rule = IGW_Admin_Cleaner_Rules::get(
			$rule_id
		);

		if ($rule) {
			IGW_Admin_Cleaner_Rules::set_enabled(
				$rule_id,
				empty($rule['enabled'])
			);
		}

		$this->redirect_with_message(
			'toggled'
		);
	}


	/**
	 * Render admin notices.
	 *
	 * @return void
	 */
	private function render_notices()
	{
		if (empty($_GET['igw_message'])) {
			return;
		}

		$message = sanitize_key(
			wp_unslash($_GET['igw_message'])
		);

		$messages = [
			'created' => __(
				'Rule created successfully.',
				'igw-admin-cleanup'
			),

			'updated' => __(
				'Rule updated successfully.',
				'igw-admin-cleanup'
			),

			'deleted' => __(
				'Rule deleted successfully.',
				'igw-admin-cleanup'
			),

			'toggled' => __(
				'Rule status updated.',
				'igw-admin-cleanup'
			),
		];

		if (!isset($messages[$message])) {
			return;
		}

		?>
		<div class="notice notice-success is-dismissible">
			<p>
				<?php echo esc_html($messages[$message]); ?>
			</p>
		</div>
		<?php
	}


	/**
	 * Redirect back to plugin page.
	 *
	 * @param string $message Message code.
	 * @param string $error Optional error.
	 * @return void
	 */
	private function redirect_with_message($message, $error = '')
	{
		$args = [
			'page'        => self::MENU_SLUG,
			'igw_message' => sanitize_key($message),
		];

		if ($error) {
			$args['igw_error'] = rawurlencode($error);
		}

		wp_safe_redirect(
			add_query_arg(
				$args,
				admin_url('options-general.php')
			)
		);

		exit;
	}


	/**
	 * Get human-readable action label.
	 *
	 * @param string $action Action.
	 * @return string
	 */
	private function get_action_label($action)
	{
		$labels = [
			'element' => __(
				'Hide element',
				'igw-admin-cleanup'
			),

			'parent' => __(
				'Hide parent',
				'igw-admin-cleanup'
			),

			'closest_li' => __(
				'Hide closest <li>',
				'igw-admin-cleanup'
			),

			'remove' => __(
				'Remove element',
				'igw-admin-cleanup'
			),
		];

		return $labels[$action]
			?? $action;
	}


	/**
	 * Format a stored timestamp as relative time.
	 *
	 * @param int $timestamp Timestamp.
	 * @return string
	 */
	private function format_timestamp($timestamp)
	{
		$timestamp = (int) $timestamp;
	
		if (!$timestamp) {
			return __(
				'Never',
				'igw-admin-cleanup'
			);
		}
	
		return sprintf(
			__('%s ago', 'igw-admin-cleanup'),
			human_time_diff(
				$timestamp,
				time()
			)
		);
	}
	
	/**
	 * Load admin page assets.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 * @return void
	 */
	public function enqueue_assets($hook_suffix)
	{
		if ($hook_suffix !== 'settings_page_' . self::MENU_SLUG) {
			return;
		}
	
		wp_enqueue_style(
			'igw-admin-cleaner-admin',
			IGW_ADMIN_CLEANER_URL . 'assets/css/admin.css',
			[],
			IGW_ADMIN_CLEANER_VERSION
		);
		
		/*
		 * Admin interface.
		 */
		wp_enqueue_script(
			'igw-admin-cleaner-admin',
			IGW_ADMIN_CLEANER_URL . 'assets/js/admin.js',
			[
				'wp-i18n',
			],
			IGW_ADMIN_CLEANER_VERSION,
			true
		);
		
		wp_set_script_translations(
			'igw-admin-cleaner-admin',
			'igw-admin-cleanup'
		);
		
		
	}
	
	
	
	/**
	 * Render rule detection status.
	 *
	 * @param array $rule Rule data.
	 * @return void
	 */
	private function render_detection_status($rule)
	{
		$last_seen = isset($rule['last_seen'])
			? (int) $rule['last_seen']
			: 0;
	
		$last_checked = isset($rule['last_checked'])
			? (int) $rule['last_checked']
			: 0;
	
		$seen_count = isset($rule['seen_count'])
			? (int) $rule['seen_count']
			: 0;
	
		$now = time();
	
		/*
		 * Never detected.
		 */
		if (!$last_seen) {
	
			?>
			<div class="igw-detection igw-detection--never">
	
				<span class="igw-detection__status">
					<?php esc_html_e(
						'Never detected',
						'igw-admin-cleanup'
					); ?>
				</span>
	
				<?php if ($last_checked) : ?>
	
					<span class="igw-detection__meta">
						<?php
						printf(
							esc_html__(
								'Checked %s ago',
								'igw-admin-cleanup'
							),
							esc_html(
								human_time_diff(
									$last_checked,
									$now
								)
							)
						);
						?>
					</span>
	
				<?php else : ?>
	
					<span class="igw-detection__meta">
						<?php esc_html_e(
							'Not checked yet',
							'igw-admin-cleanup'
						); ?>
					</span>
	
				<?php endif; ?>
	
			</div>
			<?php
	
			return;
		}
	
	
		/*
		 * Recently detected:
		 * less than 30 days ago.
		 */
		$age = $now - $last_seen;
	
		if ($age <= DAY_IN_SECONDS * 30) {
	
			$status_class = 'igw-detection--seen';
	
			$status_label = __(
				'Detected recently',
				'igw-admin-cleanup'
			);
	
		} else {
	
			$status_class = 'igw-detection--warning';
	
			$status_label = __(
				'Not seen recently',
				'igw-admin-cleanup'
			);
		}
	
		?>
	
		<div class="igw-detection <?php echo esc_attr($status_class); ?>">
	
			<span class="igw-detection__status">
				<?php echo esc_html($status_label); ?>
			</span>
	
			<span class="igw-detection__meta">
	
				<?php
				printf(
					esc_html__(
						'Seen %1$s ago · %2$d detections',
						'igw-admin-cleanup'
					),
					esc_html(
						human_time_diff(
							$last_seen,
							$now
						)
					),
					$seen_count
				);
				?>
	
			</span>
	
			<?php if ($last_checked) : ?>
	
				<span class="igw-detection__meta">
	
					<?php
					printf(
						esc_html__(
							'Last checked %s ago',
							'igw-admin-cleanup'
						),
						esc_html(
							human_time_diff(
								$last_checked,
								$now
							)
						)
					);
					?>
	
				</span>
	
			<?php endif; ?>
	
		</div>
	
		<?php
	}
}