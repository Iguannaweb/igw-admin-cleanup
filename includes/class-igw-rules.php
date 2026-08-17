<?php

if (!defined('ABSPATH')) {
	exit;
}


/**
 * Manage IGW Admin Cleaner rules.
 */
class IGW_Admin_Cleaner_Rules
{
	/**
	 * WordPress option where all cleanup rules are stored.
	 */
	const OPTION_NAME = 'igw_admin_cleaner_rules';


	/**
	 * Allowed actions.
	 */
	const ACTION_ELEMENT    = 'element';
	const ACTION_PARENT     = 'parent';
	const ACTION_CLOSEST_LI = 'closest_li';
	const ACTION_REMOVE     = 'remove';


	/**
	 * Get all rules.
	 *
	 * @return array
	 */
	public static function get_all()
	{
		$rules = get_option(self::OPTION_NAME, []);

		if (!is_array($rules)) {
			return [];
		}

		return $rules;
	}


	/**
	 * Get a single rule.
	 *
	 * @param string $rule_id Rule ID.
	 * @return array|null
	 */
	public static function get($rule_id)
	{
		$rule_id = sanitize_key($rule_id);

		if (empty($rule_id)) {
			return null;
		}

		$rules = self::get_all();

		if (!isset($rules[$rule_id])) {
			return null;
		}

		return $rules[$rule_id];
	}


	/**
	 * Create a cleanup rule.
	 *
	 * @param array $data Rule data.
	 * @return string|WP_Error Rule ID or WP_Error.
	 */
	public static function create($data)
	{
		if (!is_array($data)) {
			return new WP_Error(
				'igw_invalid_rule_data',
				__('Invalid rule data.', 'igw-admin-cleaner')
			);
		}

		$rule = self::prepare_rule($data);

		if (is_wp_error($rule)) {
			return $rule;
		}

		$rules = self::get_all();

		$rule_id = self::generate_rule_id();

		$rule['id'] = $rule_id;

		$rules[$rule_id] = $rule;

		if (!self::save($rules)) {
			return new WP_Error(
				'igw_rule_save_failed',
				__('The rule could not be saved.', 'igw-admin-cleaner')
			);
		}

		return $rule_id;
	}


	/**
	 * Update an existing rule.
	 *
	 * @param string $rule_id Rule ID.
	 * @param array  $data    New rule data.
	 * @return bool|WP_Error
	 */
	public static function update($rule_id, $data)
	{
		$rule_id = sanitize_key($rule_id);

		if (empty($rule_id)) {
			return new WP_Error(
				'igw_invalid_rule_id',
				__('Invalid rule ID.', 'igw-admin-cleaner')
			);
		}

		$rules = self::get_all();

		if (!isset($rules[$rule_id])) {
			return new WP_Error(
				'igw_rule_not_found',
				__('The requested rule does not exist.', 'igw-admin-cleaner')
			);
		}

		if (!is_array($data)) {
			return new WP_Error(
				'igw_invalid_rule_data',
				__('Invalid rule data.', 'igw-admin-cleaner')
			);
		}

		/*
		 * Preserve internal values that must not be lost
		 * when editing a rule.
		 */
		$existing = $rules[$rule_id];

		$merged = array_merge(
			$existing,
			$data
		);

		$rule = self::prepare_rule($merged, $existing);

		if (is_wp_error($rule)) {
			return $rule;
		}

		$rule['id']         = $rule_id;
		$rule['created_at'] = isset($existing['created_at'])
			? (int) $existing['created_at']
			: time();

		$rule['updated_at'] = time();

		$rules[$rule_id] = $rule;

		return self::save($rules);
	}


	/**
	 * Delete a rule.
	 *
	 * @param string $rule_id Rule ID.
	 * @return bool
	 */
	public static function delete($rule_id)
	{
		$rule_id = sanitize_key($rule_id);

		if (empty($rule_id)) {
			return false;
		}

		$rules = self::get_all();

		if (!isset($rules[$rule_id])) {
			return false;
		}

		unset($rules[$rule_id]);

		return self::save($rules);
	}


	/**
	 * Enable or disable a rule.
	 *
	 * @param string $rule_id Rule ID.
	 * @param bool   $enabled Enabled state.
	 * @return bool|WP_Error
	 */
	public static function set_enabled($rule_id, $enabled)
	{
		return self::update(
			$rule_id,
			[
				'enabled' => (bool) $enabled,
			]
		);
	}


	/**
	 * Get only enabled rules.
	 *
	 * @return array
	 */
	public static function get_enabled()
	{
		$rules = self::get_all();

		return array_filter(
			$rules,
			function ($rule) {
				return !empty($rule['enabled']);
			}
		);
	}


	/**
	 * Check if a selector already exists.
	 *
	 * @param string      $selector Selector to search.
	 * @param string|null $exclude_rule_id Optional rule to exclude.
	 * @return bool
	 */
	public static function selector_exists($selector, $exclude_rule_id = null)
	{
		$selector = self::sanitize_selector($selector);

		if (empty($selector)) {
			return false;
		}

		$exclude_rule_id = $exclude_rule_id
			? sanitize_key($exclude_rule_id)
			: null;

		foreach (self::get_all() as $rule_id => $rule) {

			if ($exclude_rule_id && $rule_id === $exclude_rule_id) {
				continue;
			}

			if (
				isset($rule['selector']) &&
				$rule['selector'] === $selector
			) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Record that a rule has been detected.
	 *
	 * To avoid excessive database writes, last_seen is only
	 * updated when enough time has passed since the previous
	 * recorded detection.
	 *
	 * @param string $rule_id Rule ID.
	 * @param int    $minimum_interval Minimum seconds between writes.
	 * @return bool
	 */
	public static function record_seen($rule_id, $minimum_interval = 21600)
	{
		$rule_id = sanitize_key($rule_id);

		if (empty($rule_id)) {
			return false;
		}

		$rules = self::get_all();

		if (!isset($rules[$rule_id])) {
			return false;
		}

		$now       = time();
		$last_seen = isset($rules[$rule_id]['last_seen'])
			? (int) $rules[$rule_id]['last_seen']
			: 0;

		/*
		 * Default: only record one detection every 6 hours.
		 */
		if (
			$last_seen > 0 &&
			($now - $last_seen) < absint($minimum_interval)
		) {
			return true;
		}

		$rules[$rule_id]['last_seen'] = $now;

		$rules[$rule_id]['seen_count'] =
			isset($rules[$rule_id]['seen_count'])
				? ((int) $rules[$rule_id]['seen_count'] + 1)
				: 1;

		return self::save($rules);
	}


	/**
	 * Record that a rule has been checked.
	 *
	 * To avoid excessive database writes, last_checked is only
	 * updated when enough time has passed since the previous check.
	 *
	 * @param string $rule_id Rule ID.
	 * @param int    $minimum_interval Minimum seconds between writes.
	 * @return bool
	 */
	public static function record_checked($rule_id, $minimum_interval = 21600)
	{
		$rule_id = sanitize_key($rule_id);
	
		if (empty($rule_id)) {
			return false;
		}
	
		$rules = self::get_all();
	
		if (!isset($rules[$rule_id])) {
			return false;
		}
	
		$now = time();
	
		$last_checked = isset($rules[$rule_id]['last_checked'])
			? (int) $rules[$rule_id]['last_checked']
			: 0;
	
		/*
		 * Default: only record one check every 6 hours.
		 */
		if (
			$last_checked > 0 &&
			($now - $last_checked) < absint($minimum_interval)
		) {
			return true;
		}
	
		$rules[$rule_id]['last_checked'] = $now;
	
		return self::save($rules);
	}


	/**
	 * Prepare and sanitize rule data.
	 *
	 * @param array $data     Rule data.
	 * @param array $existing Existing rule, if any.
	 * @return array|WP_Error
	 */
	private static function prepare_rule($data, $existing = [])
	{
		$name = isset($data['name'])
			? sanitize_text_field($data['name'])
			: '';

		$selector = isset($data['selector'])
			? self::sanitize_selector($data['selector'])
			: '';

		$source = isset($data['source'])
			? sanitize_text_field($data['source'])
			: '';

		$source_slug = isset($data['source_slug'])
			? sanitize_key($data['source_slug'])
			: '';

		$action = isset($data['action'])
			? sanitize_key($data['action'])
			: self::ACTION_ELEMENT;

		$enabled = isset($data['enabled'])
			? (bool) $data['enabled']
			: true;

		$library_id = isset($data['library_id'])
			? sanitize_key($data['library_id'])
			: '';

		/*
		 * Selector is the only absolutely required value.
		 */
		if (empty($selector)) {
			return new WP_Error(
				'igw_empty_selector',
				__('The CSS selector cannot be empty.', 'igw-admin-cleaner')
			);
		}

		/*
		 * If no name is provided, use the selector as a temporary
		 * human-readable name.
		 */
		if (empty($name)) {
			$name = $selector;
		}

		if (!self::is_valid_action($action)) {
			return new WP_Error(
				'igw_invalid_action',
				__('Invalid cleanup action.', 'igw-admin-cleaner')
			);
		}

		$now = time();

		return [
			'id'           => isset($existing['id'])
				? sanitize_key($existing['id'])
				: '',

			'name'         => $name,
			'selector'     => $selector,

			'source'       => $source,
			'source_slug'  => $source_slug,

			'action'       => $action,
			'enabled'      => $enabled,

			'library_id'   => $library_id,

			'created_at'   => isset($existing['created_at'])
				? (int) $existing['created_at']
				: $now,

			'updated_at'   => $now,

			'last_seen'    => isset($existing['last_seen'])
				? (int) $existing['last_seen']
				: 0,

			'last_checked' => isset($existing['last_checked'])
				? (int) $existing['last_checked']
				: 0,

			'seen_count'   => isset($existing['seen_count'])
				? (int) $existing['seen_count']
				: 0,
		];
	}


	/**
	 * Sanitize a CSS selector.
	 *
	 * CSS selectors may contain characters such as:
	 *
	 * > + ~ [ ] = " ' : # . *
	 *
	 * so sanitize_key() must not be used here.
	 *
	 * @param string $selector CSS selector.
	 * @return string
	 */
	private static function sanitize_selector($selector)
	{
		if (!is_string($selector)) {
			return '';
		}

		$selector = trim($selector);

		/*
		 * Selectors are stored as plain text.
		 *
		 * Remove null bytes and line breaks while preserving
		 * CSS selector syntax.
		 */
		$selector = str_replace(
			["\0", "\r", "\n"],
			'',
			$selector
		);

		/*
		 * A selector never needs HTML tags.
		 */
		$selector = wp_strip_all_tags($selector);

		return trim($selector);
	}


	/**
	 * Check whether an action is supported.
	 *
	 * @param string $action Action name.
	 * @return bool
	 */
	public static function is_valid_action($action)
	{
		return in_array(
			$action,
			self::get_actions(),
			true
		);
	}


	/**
	 * Get supported actions.
	 *
	 * @return array
	 */
	public static function get_actions()
	{
		return [
			self::ACTION_ELEMENT,
			self::ACTION_PARENT,
			self::ACTION_CLOSEST_LI,
			self::ACTION_REMOVE,
		];
	}


	/**
	 * Generate a unique rule ID.
	 *
	 * @return string
	 */
	private static function generate_rule_id()
	{
		do {
			$uuid = wp_generate_uuid4();

			$rule_id = 'rule_' . str_replace('-', '', $uuid);

			$rules = self::get_all();

		} while (isset($rules[$rule_id]));

		return $rule_id;
	}


	/**
	 * Save all rules.
	 *
	 * @param array $rules Rules.
	 * @return bool
	 */
	private static function save($rules)
	{
		if (!is_array($rules)) {
			return false;
		}

		/*
		 * update_option() returns false both when an update fails
		 * and when the new value is identical to the current value.
		 *
		 * Therefore, first check whether there is actually anything
		 * to change.
		 */
		$current = self::get_all();

		if ($current === $rules) {
			return true;
		}

		return update_option(
			self::OPTION_NAME,
			$rules,
			false
		);
	}
}