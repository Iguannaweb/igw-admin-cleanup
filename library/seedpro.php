<?php

if (!defined('ABSPATH')) {
	exit;
}

return [

	[
		'id'            => 'seedprod-lite-upgrade-bar',

		'plugin_name'   => 'SeedProd',
		'plugin_file'   => 'coming-soon/coming-soon.php',

		'name'          => 'Upgrade bar',

		'description'   => 'Upgrade promotion bar displayed in SeedProd administration pages.',

		'selector'      => '.seedprod-lite-upgrade-bar',

		'action'        => 'element',

		'category'      => 'upsell',

		'verified'      => true,

		'last_verified' => '2026-08-24',
	],

	[
		'id'            => 'seedprod-notification-bar',

		'plugin_name'   => 'SeedProd',
		'plugin_file'   => 'coming-soon/coming-soon.php',

		'name'          => 'Notification bar',

		'description'   => 'Notification bar displayed in the SeedProd administration interface.',

		'selector'      => '.seedprod-notification-bar',

		'action'        => 'element',

		'category'      => 'notice',

		'verified'      => true,

		'last_verified' => '2026-08-24',
	],
	[
		'id'            => 'seedprod-upgrade-menu',
	
		'plugin_name'   => 'SeedProd',
		'plugin_file'   => 'coming-soon/coming-soon.php',
	
		'name'          => 'Upgrade menu entry',
	
		'description'   => 'Upgrade to Pro entry displayed in the SeedProd administration menu.',
	
		'selector'      => '#sp-lite-admin-menu__upgrade',
	
		'action'        => 'closest_li',
	
		'category'      => 'upsell',
	
		'verified'      => true,
	
		'last_verified' => '2026-08-24',
	],
	[
		'id'            => 'seedprod-ai-theme-builder-menu',
		'plugin_name'   => 'SeedProd',
		'plugin_file'   => 'coming-soon/coming-soon.php',
		'name'          => 'AI Theme Builder menu entry',
		'description'   => 'AI Theme Builder entry displayed in the SeedProd administration menu.',
		'selector'      => '#toplevel_page_seedprod_lite .wp-submenu a[href*="page=seedprod_lite_ai_themes"]',
		'action'        => 'closest_li',
		'category'      => 'upsell',
		'verified'      => true,
		'last_verified' => '2026-08-24',
	],
	
	[
		'id'            => 'seedprod-manage-with-ai-menu',
		'plugin_name'   => 'SeedProd',
		'plugin_file'   => 'coming-soon/coming-soon.php',
		'name'          => 'Manage with AI menu entry',
		'description'   => 'Manage with AI entry displayed in the SeedProd administration menu.',
		'selector'      => '#toplevel_page_seedprod_lite .wp-submenu a[href*="page=seedprod_lite_manage_with_ai"]',
		'action'        => 'closest_li',
		'category'      => 'upsell',
		'verified'      => true,
		'last_verified' => '2026-08-24',
	],
	
	[
		'id'            => 'seedprod-website-builder-menu',
		'plugin_name'   => 'SeedProd',
		'plugin_file'   => 'coming-soon/coming-soon.php',
		'name'          => 'Website Builder menu entry',
		'description'   => 'Website Builder entry displayed in the SeedProd administration menu.',
		'selector'      => '#toplevel_page_seedprod_lite .wp-submenu a[href*="page=seedprod_lite_website_builder"]',
		'action'        => 'closest_li',
		'category'      => 'upsell',
		'verified'      => true,
		'last_verified' => '2026-08-24',
	],
	[
		'id'            => 'seedprod-popups-menu',
	
		'plugin_name'   => 'SeedProd',
		'plugin_file'   => 'coming-soon/coming-soon.php',
	
		'name'          => 'Popups menu entry',
	
		'description'   => 'Popups menu entry for a Pro-only feature displayed in the SeedProd administration menu.',
	
		'selector'      => '#toplevel_page_seedprod_lite .wp-submenu a[href*="page=seedprod_lite_popups"]',
	
		'action'        => 'closest_li',
	
		'category'      => 'upsell',
	
		'verified'      => true,
	
		'last_verified' => '2026-08-24',
	],
	[
		'id'            => 'seedprod-pro-features',
	
		'plugin_name'   => 'SeedProd',
		'plugin_file'   => 'coming-soon/coming-soon.php',
	
		'name'          => 'Pro feature blocks',
	
		'description'   => 'Pro-only feature blocks displayed in the SeedProd Landing Pages administration screen.',
	
		'selector'      => '.seedprod-pro-feature',
	
		'action'        => 'element',
	
		'category'      => 'upsell',
	
		'verified'      => true,
	
		'last_verified' => '2026-08-24',
	],

];