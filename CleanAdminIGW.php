<?php
/**
 * Plugin Name: IGW Admin Cleanup
 * Description: Hide unnecessary elements from the WordPress admin panel using CSS selectors.
 * Version: 0.1.1
 * Author: Francisco Gálvez
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: igw-admin-cleanup
 */
 
 if (!defined('ABSPATH')) {
     exit;
 }


/**
 * Añadir página de configuración
 */
add_action('admin_menu', function () {

    add_options_page(
        'IGW Admin Cleanup',
        'IGW Admin Cleanup',
        'manage_options',
        'tinco-admin-cleaner',
        'tinco_admin_cleaner_page'
    );

});


/**
 * Registrar configuración
 */
add_action('admin_init', function () {

    register_setting(
        'tinco_admin_cleaner_settings',
        'tinco_admin_cleaner_selectors',
        [
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
            'default'           => '',
        ]
    );

});


/**
 * Página de administración
 */
function tinco_admin_cleaner_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $selectors = get_option('tinco_admin_cleaner_selectors', '');

    ?>

    <div class="wrap">

        <h1>Clean up your control panel of unnecessary items!</h1>

        <p>Add one CSS selector per line. The elements found will be automatically hidden in the admin panel.</p>

        <form method="post" action="options.php">

            <?php settings_fields('tinco_admin_cleaner_settings'); ?>

            <textarea
                name="tinco_admin_cleaner_selectors"
                rows="15"
                style="width:100%;max-width:900px;font-family:monospace;"
            ><?php echo esc_textarea($selectors); ?></textarea>

            <p>Examples:</p>

            <pre>
#wfMenuCallout
.plugin-promo-banner
.some-plugin-upgrade-notice
            </pre>

            <?php submit_button(); ?>

        </form>

    </div>

    <?php
}


/**
 * Ocultar elementos
 */
add_action('admin_footer', function () {

    $selectors = get_option('tinco_admin_cleaner_selectors', '');

    if (!$selectors) {
        return;
    }

    $selectors = preg_split('/\r\n|\r|\n/', $selectors);
    $selectors = array_filter(array_map('trim', $selectors));

    if (!$selectors) {
        return;
    }

    ?>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        const selectors = <?php echo wp_json_encode(array_values($selectors)); ?>;

        selectors.forEach(function(selector) {

            try {

                document.querySelectorAll(selector).forEach(function(element) {

                    /*
                     * If the selector is inside an LI,
                     * we hide the entire menu item.
                     */
                    const li = element.closest('li');

                    if (li) {
                        li.style.display = 'none';
                    } else {
                        element.style.display = 'none';
                    }

                });

            } catch(e) {
                console.warn(
                    'IGW Admin Cleanup: invalid selector:',
                    selector
                );
            }

        });

    });
    </script>

    <?php

});