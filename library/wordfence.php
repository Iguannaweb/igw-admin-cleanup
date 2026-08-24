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
	[
		'id'            => 'wordfence-satisfaction-prompt',
	
		'plugin_name'   => 'Wordfence Security',
		'plugin_file'   => 'wordfence/wordfence.php',
	
		'name'          => 'Satisfaction survey banner',
	
		'description'   => 'Satisfaction survey prompt displayed at the top of Wordfence administration content.',
	
		'selector'      => '#wordfenceSatisfactionPrompt',
	
		'action'        => 'element',
	
		'category'      => 'notice',
	
		'verified'      => true,
	
		'last_verified' => '2026-08-24',
	],
	[
		'id'            => 'wordfence-audit-log-premium-only',
	
		'plugin_name'   => 'Wordfence Security',
		'plugin_file'   => 'wordfence/wordfence.php',
	
		'name'          => 'Audit Log Premium notice',
	
		'description'   => 'Premium-only Audit Log notice displayed in Wordfence administration pages.',
	
		'selector'      => '#wordfenceAuditLogPremiumOnly',
	
		'action'        => 'element',
	
		'category'      => 'upsell',
	
		'verified'      => true,
	
		'last_verified' => '2026-08-24',
	],
	[
		'id'            => 'wordfence-audit-log-premium-callout',
	
		'plugin_name'   => 'Wordfence Security',
		'plugin_file'   => 'wordfence/wordfence.php',
	
		'name'          => 'Audit Log Premium promotion',
	
		'description'   => 'Premium Audit Log promotional block displayed in Wordfence administration pages.',
	
		'selector'      => '.wf-flex-row.wf-add-bottom-small:has(.wf-audit-log-premium-callout)',
	
		'action'        => 'element',
	
		'category'      => 'upsell',
	
		'verified'      => true,
	
		'last_verified' => '2026-08-24',
	],

];