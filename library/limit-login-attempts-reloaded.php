<?php

if (!defined('ABSPATH')) {
	exit;
}

return [

	[
		'id'            => 'llar-premium-menu',

		'plugin_name'   => 'Limit Login Attempts Reloaded',
		'plugin_file'   => 'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',

		'name'          => 'Premium menu entry',

		'description'   => 'Premium upgrade entry displayed in the plugin administration menu.',

		'selector'      => '.llar-submenu-premium-item',

		'action'        => 'closest_li',

		'category'      => 'upsell',

		'verified'      => true,

		'last_verified' => '2026-08-19',
	],

];