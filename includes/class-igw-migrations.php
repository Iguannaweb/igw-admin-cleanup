<?php

if (!defined('ABSPATH')) {
	exit;
}


/**
 * Handle IGW Admin Cleaner data migrations.
 */
class IGW_Admin_Cleaner_Migrations
{
	/**
	 * Option used to store the internal data version.
	 */
	const DB_VERSION_OPTION = 'igw_admin_cleaner_db_version';


	/**
	 * Legacy option used by version 0.1.x.
	 */
	const LEGACY_SELECTORS_OPTION = 'tinco_admin_cleaner_selectors';


	/**
	 * Run pending migrations.
	 *
	 * @return void
	 */
	public static function maybe_migrate()
	{
		$installed_version = get_option(
			self::DB_VERSION_OPTION,
			'1'
		);

		/*
		 * Fresh installation or installation coming from 0.1.x.
		 */
		if (version_compare($installed_version, '2', '<')) {
			self::migrate_to_v2();
		}
	}


	/**
	 * Migrate legacy textarea selectors to the new rule system.
	 *
	 * @return bool
	 */
	private static function migrate_to_v2()
	{
		/*
		 * Get the old textarea value.
		 */
		$legacy_selectors = get_option(
			self::LEGACY_SELECTORS_OPTION,
			''
		);

		/*
		 * If there are no legacy selectors, this may simply be
		 * a fresh installation of version 0.2.0 or later.
		 */
		if (empty($legacy_selectors)) {

			update_option(
				self::DB_VERSION_OPTION,
				'2',
				false
			);

			self::update_plugin_version();

			return true;
		}


		/*
		 * Convert textarea contents into individual selectors.
		 */
		$selectors = preg_split(
			'/\r\n|\r|\n/',
			$legacy_selectors
		);

		if (!is_array($selectors)) {
			return false;
		}

		$selectors = array_map(
			'trim',
			$selectors
		);

		$selectors = array_filter(
			$selectors,
			function ($selector) {
				return $selector !== '';
			}
		);

		/*
		 * Remove exact duplicates from the old textarea.
		 */
		$selectors = array_unique($selectors);


		/*
		 * If after cleanup there are no selectors, finish migration.
		 */
		if (empty($selectors)) {

			update_option(
				self::DB_VERSION_OPTION,
				'2',
				false
			);

			self::update_plugin_version();

			return true;
		}


		/*
		 * Import each old selector as a new rule.
		 */
		foreach ($selectors as $selector) {

			/*
			 * Avoid creating duplicate rules if the migration
			 * has already been partially executed.
			 */
			if (
				IGW_Admin_Cleaner_Rules::selector_exists(
					$selector
				)
			) {
				continue;
			}


			$result = IGW_Admin_Cleaner_Rules::create([
				'name'        => self::generate_imported_rule_name(
					$selector
				),
				'selector'    => $selector,
				'source'      => '',
				'source_slug' => '',
				'action'      => IGW_Admin_Cleaner_Rules::ACTION_CLOSEST_LI,
				'enabled'     => true,
			]);


			/*
			 * Stop migration if one rule cannot be created.
			 *
			 * We intentionally do not update the DB version,
			 * so WordPress will retry the migration later.
			 */
			if (is_wp_error($result)) {

				self::log_error(
					sprintf(
						'Could not migrate selector "%s": %s',
						$selector,
						$result->get_error_message()
					)
				);

				return false;
			}
		}


		/*
		 * Mark migration as successfully completed.
		 */
		update_option(
			self::DB_VERSION_OPTION,
			'2',
			false
		);


		/*
		 * Store the current plugin version.
		 */
		self::update_plugin_version();


		/*
		 * IMPORTANT:
		 *
		 * Do not delete the legacy option yet.
		 *
		 * Keeping it for at least one or two releases gives us
		 * an easy fallback if a migration problem is discovered.
		 */
		return true;
	}


	/**
	 * Generate a readable name for imported legacy rules.
	 *
	 * @param string $selector CSS selector.
	 * @return string
	 */
	private static function generate_imported_rule_name($selector)
	{
		return sprintf(
			__('Imported rule: %s', 'igw-admin-cleaner'),
			$selector
		);
	}


	/**
	 * Update stored plugin version.
	 *
	 * @return void
	 */
	private static function update_plugin_version()
	{
		update_option(
			'igw_admin_cleaner_version',
			IGW_ADMIN_CLEANER_VERSION,
			false
		);
	}


	/**
	 * Log migration errors only when WP_DEBUG is enabled.
	 *
	 * @param string $message Error message.
	 * @return void
	 */
	private static function log_error($message)
	{
		if (
			defined('WP_DEBUG') &&
			WP_DEBUG
		) {
			error_log(
				'IGW Admin Cleaner migration: ' . $message
			);
		}
	}
}