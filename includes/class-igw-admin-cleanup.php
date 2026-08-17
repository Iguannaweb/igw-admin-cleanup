<?php

if (!defined('ABSPATH')) {
	exit;
}


/**
 * Main IGW Admin Cleaner controller.
 */
class IGW_Admin_Cleanup
{
	/**
	 * Initialize plugin hooks.
	 *
	 * @return void
	 */
	public function init()
	{
		/*
		 * Only run cleanup logic inside wp-admin.
		 */
		if (!is_admin()) {
			return;
		}

		add_action(
			'admin_enqueue_scripts',
			[$this, 'enqueue_admin_assets']
		);

		/*
		 * AJAX endpoint used to record detected rules.
		 *
		 * The JavaScript engine will use this later when a selector
		 * has actually been found in the current admin page.
		 */
		add_action(
			'wp_ajax_igw_admin_cleaner_rule_seen',
			[$this, 'ajax_rule_seen']
		);
		
		add_action(
			'wp_ajax_igw_admin_cleaner_rule_checked',
			[$this, 'ajax_rule_checked']
		);
	}


	/**
	 * Enqueue cleanup assets.
	 *
	 * @return void
	 */
	public function enqueue_admin_assets()
	{
		$rules = IGW_Admin_Cleaner_Rules::get_enabled();

		/*
		 * If there are no active rules, there is nothing to process.
		 */
		if (empty($rules)) {
			return;
		}


		wp_enqueue_script(
			'igw-admin-cleaner',
			IGW_ADMIN_CLEANER_URL . 'assets/js/cleaner.js',
			[],
			IGW_ADMIN_CLEANER_VERSION,
			true
		);
		/*
		 * Admin interface JavaScript.
		 */
		wp_enqueue_script(
			'igw-admin-cleaner-admin',
			IGW_ADMIN_CLEANER_URL . 'assets/js/admin.js',
			[],
			IGW_ADMIN_CLEANER_VERSION,
			true
		);


		/*
		 * Prepare only the data needed by JavaScript.
		 *
		 * We deliberately do not expose internal information such as
		 * detection counters, timestamps or library metadata.
		 */
		$javascript_rules = [];

		foreach ($rules as $rule_id => $rule) {

			if (
				empty($rule['selector']) ||
				empty($rule['action'])
			) {
				continue;
			}

			$javascript_rules[] = [
				'id'       => sanitize_key($rule_id),
				'selector' => $rule['selector'],
				'action'   => sanitize_key($rule['action']),
			];
		}


		if (empty($javascript_rules)) {
			return;
		}


		/*
		 * Pass configuration to cleaner.js.
		 */
		wp_localize_script(
			'igw-admin-cleaner',
			'IGWAdminCleaner',
			[
				'rules'   => array_values($javascript_rules),

				'ajaxUrl' => admin_url('admin-ajax.php'),

				'nonce'   => wp_create_nonce(
					'igw_admin_cleaner_rule_seen'
				),

				/*
				 * This will later allow us to enable/disable
				 * dynamic DOM observation from the settings page.
				 */
				'observeDynamicContent' => true,
			]
		);
	}


	/**
	 * AJAX endpoint used to record when a rule is found.
	 *
	 * @return void
	 */
	public function ajax_rule_seen()
	{
		/*
		 * Cleanup rules are only relevant to authenticated
		 * WordPress administrators.
		 */
		if (!current_user_can('manage_options')) {
			wp_send_json_error(
				[
					'message' => __(
						'You do not have permission to perform this action.',
						'igw-admin-cleaner'
					),
				],
				403
			);
		}


		check_ajax_referer(
			'igw_admin_cleaner_rule_seen',
			'nonce'
		);


		$rule_id = isset($_POST['rule_id'])
			? sanitize_key(
				wp_unslash($_POST['rule_id'])
			)
			: '';


		if (empty($rule_id)) {
			wp_send_json_error(
				[
					'message' => __(
						'Invalid rule ID.',
						'igw-admin-cleaner'
					),
				],
				400
			);
		}


		$rule = IGW_Admin_Cleaner_Rules::get($rule_id);

		if (!$rule) {
			wp_send_json_error(
				[
					'message' => __(
						'The requested rule does not exist.',
						'igw-admin-cleaner'
					),
				],
				404
			);
		}


		/*
		 * Do not record detections for disabled rules.
		 */
		if (empty($rule['enabled'])) {
			wp_send_json_error(
				[
					'message' => __(
						'The requested rule is disabled.',
						'igw-admin-cleaner'
					),
				],
				400
			);
		}


		$result = IGW_Admin_Cleaner_Rules::record_seen(
			$rule_id
		);


		if (!$result) {
			wp_send_json_error(
				[
					'message' => __(
						'The detection could not be recorded.',
						'igw-admin-cleaner'
					),
				],
				500
			);
		}


		wp_send_json_success(
			[
				'rule_id' => $rule_id,
			]
		);
	}
	
	/**
	 * AJAX endpoint used to record when a rule has been checked.
	 *
	 * @return void
	 */
	public function ajax_rule_checked()
	{
		if (!current_user_can('manage_options')) {
			wp_send_json_error(
				[
					'message' => __(
						'You do not have permission to perform this action.',
						'igw-admin-cleaner'
					),
				],
				403
			);
		}
	
		check_ajax_referer(
			'igw_admin_cleaner_rule_seen',
			'nonce'
		);
	
		$rule_id = isset($_POST['rule_id'])
			? sanitize_key(
				wp_unslash($_POST['rule_id'])
			)
			: '';
	
		if (empty($rule_id)) {
			wp_send_json_error(
				[
					'message' => __(
						'Invalid rule ID.',
						'igw-admin-cleaner'
					),
				],
				400
			);
		}
	
		$rule = IGW_Admin_Cleaner_Rules::get($rule_id);
	
		if (!$rule) {
			wp_send_json_error(
				[
					'message' => __(
						'The requested rule does not exist.',
						'igw-admin-cleaner'
					),
				],
				404
			);
		}
	
		if (empty($rule['enabled'])) {
			wp_send_json_error(
				[
					'message' => __(
						'The requested rule is disabled.',
						'igw-admin-cleaner'
					),
				],
				400
			);
		}
	
		$result = IGW_Admin_Cleaner_Rules::record_checked(
			$rule_id
		);
	
		if (!$result) {
			wp_send_json_error(
				[
					'message' => __(
						'The rule check could not be recorded.',
						'igw-admin-cleaner'
					),
				],
				500
			);
		}
	
		wp_send_json_success(
			[
				'rule_id' => $rule_id,
			]
		);
	}
}