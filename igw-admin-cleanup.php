<?php
/**
 * Plugin Name:       IGW Admin Cleaner
 * Plugin URI:        https://iguannaweb.com/
 * Description:       Hide unnecessary elements from the WordPress admin panel using configurable cleanup rules.
 * Version:           0.3.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Francisco Gálvez
 * Author URI:        https://iguannaweb.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       igw-admin-cleanup
 * Domain Path:       /languages
 */

if (!defined('ABSPATH')) {
    exit;
}


/**
 * Plugin constants.
 */
define('IGW_ADMIN_CLEANER_VERSION', '0.3.1');
define('IGW_ADMIN_CLEANER_DB_VERSION', '2');

define('IGW_ADMIN_CLEANER_FILE', __FILE__);
define('IGW_ADMIN_CLEANER_PATH', plugin_dir_path(__FILE__));
define('IGW_ADMIN_CLEANER_URL', plugin_dir_url(__FILE__));
define('IGW_ADMIN_CLEANER_BASENAME', plugin_basename(__FILE__));


/**
 * Load required classes.
 */
require_once IGW_ADMIN_CLEANER_PATH . 'includes/class-igw-rules.php';
require_once IGW_ADMIN_CLEANER_PATH . 'includes/class-igw-migrations.php';
require_once IGW_ADMIN_CLEANER_PATH . 'includes/class-igw-admin-cleanup.php';

require_once IGW_ADMIN_CLEANER_PATH . 'admin/class-igw-admin-page.php';


/**
 * Plugin activation.
 */
function igw_admin_cleaner_activate()
{
    /*
     * Store the plugin version if this is a fresh installation.
     *
     * Existing installations will be handled later by the
     * migration system.
     */
    if (false === get_option('igw_admin_cleaner_version')) {
        add_option(
            'igw_admin_cleaner_version',
            IGW_ADMIN_CLEANER_VERSION,
            '',
            false
        );
    }

    /*
     * Do not create the DB version here for existing installations.
     *
     * The migration class needs to determine whether this is
     * an old installation that still uses the textarea option:
     *
     * tinco_admin_cleaner_selectors
     */
}
register_activation_hook(
    __FILE__,
    'igw_admin_cleaner_activate'
);


/**
 * Plugin deactivation.
 *
 * We intentionally keep all configuration and cleanup rules.
 */
function igw_admin_cleaner_deactivate()
{
    /*
     * Nothing to remove here.
     *
     * Plugin options must remain available if the user
     * temporarily deactivates the plugin.
     */
}
register_deactivation_hook(
    __FILE__,
    'igw_admin_cleaner_deactivate'
);


/**
 * Initialize plugin.
 */
function igw_admin_cleaner_init()
{
    /*
     * Run migrations before initializing the cleanup engine.
     */
    IGW_Admin_Cleaner_Migrations::maybe_migrate();

    /*
     * Start the plugin.
     */
    $plugin = new IGW_Admin_Cleanup();
    $plugin->init();
    
    $admin_page = new IGW_Admin_Cleaner_Admin_Page();
    $admin_page->init();
}
add_action(
    'plugins_loaded',
    'igw_admin_cleaner_init'
);