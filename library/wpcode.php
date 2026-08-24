<?php

if (!defined('ABSPATH')) {
	exit;
}

return [

	[
		'id'            => 'wpcode-sidebar-upgrade-pro',

		'plugin_name'   => 'WPCode',
		'plugin_file'   => 'insert-headers-and-footers/ihaf.php',

		'name'          => 'Upgrade to Pro sidebar',

		'description'   => 'Upgrade promotion displayed in the WPCode administration sidebar.',

		'selector'      => '.wpcode-sidebar-upgrade-pro',

		'action'        => 'element',

		'category'      => 'upsell',

		'verified'      => true,

		'last_verified' => '2026-08-19',
	],
	[
		'id'            => 'wpcode-pro-type-lite-tabs',
	
		'plugin_name'   => 'WPCode',
		'plugin_file'   => 'insert-headers-and-footers/ihaf.php',
	
		'name'          => 'Pro feature tabs',
	
		'description'   => 'Tabs for Pro-only features displayed in the WPCode administration interface.',
	
		'selector'      => '.wpcode_pro_type_lite',
	
		'action'        => 'element',
	
		'category'      => 'upsell',
	
		'verified'      => true,
	
		'last_verified' => '2026-08-24',
	],

];