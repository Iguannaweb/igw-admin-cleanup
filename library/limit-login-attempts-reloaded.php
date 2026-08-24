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
	[
		'id'            => 'llar-premium-tab',
	
		'plugin_name'   => 'Limit Login Attempts Reloaded',
		'plugin_file'   => 'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
	
		'name'          => 'Premium and extensions tab',
	
		'description'   => 'Premium and extensions tab displayed in the Limit Login Attempts Reloaded administration navigation.',
	
		'selector'      => '.nav-tab[href*="page=limit-login-attempts"][href*="tab=premium"]',
	
		'action'        => 'element',
	
		'category'      => 'upsell',
	
		'verified'      => true,
	
		'last_verified' => '2026-08-24',
	],
	[
		'id'            => 'llar-premium-promotion-blocks',
	
		'plugin_name'   => 'Limit Login Attempts Reloaded',
		'plugin_file'   => 'limit-login-attempts-reloaded/limit-login-attempts-reloaded.php',
	
		'name'          => 'Premium promotion blocks',
	
		'description'   => 'Premium upgrade promotional blocks displayed in Limit Login Attempts Reloaded administration pages.',
	
		'selector'      => '.add_block__under_table.image_plus:has(.button__transparent_orange)',
	
		'action'        => 'element',
	
		'category'      => 'upsell',
	
		'verified'      => true,
	
		'last_verified' => '2026-08-24',
	],

];