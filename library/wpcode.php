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

];