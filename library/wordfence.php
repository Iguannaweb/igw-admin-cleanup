<?php

if (!defined('ABSPATH')) {
	exit;
}

return [

	[
		'id'            => 'wordfence-upgrade-menu',

		'plugin_name'   => 'Wordfence Security',
		'plugin_file'   => 'wordfence/wordfence.php',

		'name'          => 'Upgrade to Premium menu',

		'description'   => 'Premium upgrade link displayed in the Wordfence administration menu.',

		'selector'      => '#wfMenuCallout',

		'action'        => 'closest_li',

		'category'      => 'upsell',

		'verified'      => true,

		'last_verified' => '2026-08-19',
	],

];