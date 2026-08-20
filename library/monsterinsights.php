<?php

if (!defined('ABSPATH')) {
	exit;
}

return [

	/*
	 * Test library entry.
	 *
	 * The selector has not been verified against
	 * MonsterInsights and must not be considered
	 * production-ready.
	 */
	[
		'id'            => 'monsterinsights-test-upsell',

		'plugin_name'   => 'MonsterInsights',
		'plugin_file'   => 'google-analytics-for-wordpress/googleanalytics.php',

		'name'          => 'MonsterInsights promotional element',

		'description'   => 'Test library rule used to verify library visibility for plugins that are not installed.',

		'selector'      => '.monsterinsights-test-upsell',

		'action'        => 'element',

		'category'      => 'upsell',

		'verified'      => false,

		'last_verified' => '',
	],

];