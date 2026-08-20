<?php

if (!defined('ABSPATH')) {
	exit;
}


/**
 * IGW Admin Cleanup rule library.
 */
class IGW_Admin_Cleaner_Library
{
	/**
	 * Get all library rules.
	 *
	 * @return array
	 */
	public static function get_all()
	{
		$file = IGW_ADMIN_CLEANER_PATH . 'library/rules.php';

		if (!file_exists($file)) {
			return [];
		}

		$rules = require $file;

		if (!is_array($rules)) {
			return [];
		}

		return $rules;
	}


	/**
	 * Get a single library rule.
	 *
	 * @param string $library_id Library rule ID.
	 * @return array|null
	 */
	public static function get($library_id)
	{
		$library_id = sanitize_key($library_id);

		if (!$library_id) {
			return null;
		}

		foreach (self::get_all() as $rule) {

			if (
				!empty($rule['id']) &&
				sanitize_key($rule['id']) === $library_id
			) {
				return $rule;
			}
		}

		return null;
	}


	/**
	 * Group library rules by plugin.
	 *
	 * @return array
	 */
	public static function get_grouped_by_plugin()
	{
		$groups = [];

		foreach (self::get_all() as $rule) {

			if (empty($rule['plugin_file'])) {
				continue;
			}

			$plugin_file = $rule['plugin_file'];

			if (!isset($groups[$plugin_file])) {

				$groups[$plugin_file] = [
					'plugin_name' => $rule['plugin_name'] ?? $plugin_file,
					'plugin_file' => $plugin_file,
					'rules'       => [],
				];
			}

			$groups[$plugin_file]['rules'][] = $rule;
		}

		return $groups;
	}


	/**
	 * Get installed WordPress plugins.
	 *
	 * @return array
	 */
	public static function get_installed_plugins()
	{
		if (!function_exists('get_plugins')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return get_plugins();
	}


	/**
	 * Check whether a plugin is installed.
	 *
	 * @param string $plugin_file Plugin file.
	 * @return bool
	 */
	public static function is_plugin_installed($plugin_file)
	{
		$plugins = self::get_installed_plugins();

		return isset($plugins[$plugin_file]);
	}


	/**
	 * Check whether a plugin is active.
	 *
	 * @param string $plugin_file Plugin file.
	 * @return bool
	 */
	public static function is_plugin_active($plugin_file)
	{
		if (!function_exists('is_plugin_active')) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return is_plugin_active($plugin_file);
	}


	/**
	 * Get rules belonging to installed plugins.
	 *
	 * @return array
	 */
	public static function get_for_installed_plugins()
	{
		return array_values(
			array_filter(
				self::get_all(),
				function ($rule) {

					if (empty($rule['plugin_file'])) {
						return false;
					}

					return self::is_plugin_installed(
						$rule['plugin_file']
					);
				}
			)
		);
	}


	/**
	 * Get rules belonging to active plugins.
	 *
	 * @return array
	 */
	public static function get_for_active_plugins()
	{
		return array_values(
			array_filter(
				self::get_all(),
				function ($rule) {

					if (empty($rule['plugin_file'])) {
						return false;
					}

					return self::is_plugin_active(
						$rule['plugin_file']
					);
				}
			)
		);
	}


	/**
	 * Check whether a library rule has already been
	 * added to the user's cleanup rules.
	 *
	 * @param string $library_id Library rule ID.
	 * @return bool
	 */
	public static function is_rule_installed($library_id)
	{
		$library_id = sanitize_key($library_id);

		if (!$library_id) {
			return false;
		}

		foreach (
			IGW_Admin_Cleaner_Rules::get_all()
			as $rule
		) {

			if (
				!empty($rule['library_id']) &&
				sanitize_key($rule['library_id']) === $library_id
			) {
				return true;
			}
		}

		return false;
	}


	/**
	 * Import a library rule into the user's cleanup rules.
	 *
	 * @param string $library_id Library rule ID.
	 * @return string|WP_Error Rule ID or WP_Error.
	 */
	public static function install_rule($library_id)
	{
		$library_id = sanitize_key($library_id);

		if (!$library_id) {
			return new WP_Error(
				'igw_invalid_library_rule',
				__(
					'Invalid library rule.',
					'igw-admin-cleanup'
				)
			);
		}


		if (
			self::is_rule_installed(
				$library_id
			)
		) {
			return new WP_Error(
				'igw_library_rule_exists',
				__(
					'This library rule has already been added.',
					'igw-admin-cleanup'
				)
			);
		}


		$library_rule =
			self::get($library_id);


		if (!$library_rule) {
			return new WP_Error(
				'igw_library_rule_not_found',
				__(
					'The requested library rule could not be found.',
					'igw-admin-cleanup'
				)
			);
		}


		return IGW_Admin_Cleaner_Rules::create([
			'name'        => $library_rule['name'] ?? '',
			'selector'    => $library_rule['selector'] ?? '',
			'source'      => $library_rule['plugin_name'] ?? '',
			'source_slug' => self::get_plugin_slug(
				$library_rule['plugin_file'] ?? ''
			),
			'action'      => $library_rule['action']
				?? IGW_Admin_Cleaner_Rules::ACTION_ELEMENT,
			'enabled'     => true,
			'library_id'  => $library_id,
		]);
	}


	/**
	 * Get plugin slug from plugin file.
	 *
	 * Example:
	 *
	 * wordfence/wordfence.php
	 * becomes
	 * wordfence
	 *
	 * @param string $plugin_file Plugin file.
	 * @return string
	 */
	private static function get_plugin_slug($plugin_file)
	{
		if (!$plugin_file) {
			return '';
		}

		$parts = explode(
			'/',
			$plugin_file
		);

		return sanitize_key(
			$parts[0]
		);
	}
}