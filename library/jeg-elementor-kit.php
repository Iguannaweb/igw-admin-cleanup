<?php

if (!defined('ABSPATH')) {
	exit;
}

return [

	[
		'id'            => 'jegkit-admin-bar-pro',

		'plugin_name'   => 'Jeg Elementor Kit',
		'plugin_file'   => 'jeg-elementor-kit/jeg-elementor-kit.php',

		'name'          => 'Admin bar Pro entry',

		'description'   => 'Pro promotion displayed in the WordPress administration bar.',

		'selector'      => '#wp-admin-bar-jeg-kit-pro',

		'action'        => 'closest_li',

		'category'      => 'upsell',

		'verified'      => true,

		'last_verified' => '2026-08-19',
	],

	[
		'id'            => 'jegkit-top-upgrade-banner',

		'plugin_name'   => 'Jeg Elementor Kit',
		'plugin_file'   => 'jeg-elementor-kit/jeg-elementor-kit.php',

		'name'          => 'Top upgrade banner',

		'description'   => 'Upgrade promotion displayed at the top of Jeg Elementor Kit administration pages.',

		'selector'      => '.jkit-top-upgrade-banner',

		'action'        => 'element',

		'category'      => 'upsell',

		'verified'      => true,

		'last_verified' => '2026-08-19',
	],

	[
		'id'            => 'jegkit-event-sales-pricing',

		'plugin_name'   => 'Jeg Elementor Kit',
		'plugin_file'   => 'jeg-elementor-kit/jeg-elementor-kit.php',

		'name'          => 'Event sales pricing promotion',

		'description'   => 'Promotional pricing element displayed in Jeg Elementor Kit administration pages.',

		'selector'      => '.f-pricing.is-event-sales',

		'action'        => 'element',

		'category'      => 'upsell',

		'verified'      => true,

		'last_verified' => '2026-08-19',
	],

	[
		'id'            => 'jegkit-notice-banner',

		'plugin_name'   => 'Jeg Elementor Kit',
		'plugin_file'   => 'jeg-elementor-kit/jeg-elementor-kit.php',

		'name'          => 'Notice banner',

		'description'   => 'Promotional notice banner displayed by Jeg Elementor Kit.',

		'selector'      => '.jkit-notice-banner',

		'action'        => 'element',

		'category'      => 'notice',

		'verified'      => true,

		'last_verified' => '2026-08-19',
	],

	[
		'id'            => 'jegkit-upgrade-banner',

		'plugin_name'   => 'Jeg Elementor Kit',
		'plugin_file'   => 'jeg-elementor-kit/jeg-elementor-kit.php',

		'name'          => 'Upgrade banner',

		'description'   => 'Upgrade banner displayed in Jeg Elementor Kit administration pages.',

		'selector'      => '.jkit-upgrade-banner',

		'action'        => 'element',

		'category'      => 'upsell',

		'verified'      => true,

		'last_verified' => '2026-08-19',
	],

	[
		'id'            => 'jegkit-discount-banner',

		'plugin_name'   => 'Jeg Elementor Kit',
		'plugin_file'   => 'jeg-elementor-kit/jeg-elementor-kit.php',

		'name'          => 'Discount banner',

		'description'   => 'Discount promotion displayed in Jeg Elementor Kit administration pages.',

		'selector'      => '.jkit-discount-banner',

		'action'        => 'element',

		'category'      => 'upsell',

		'verified'      => true,

		'last_verified' => '2026-08-19',
	],

];