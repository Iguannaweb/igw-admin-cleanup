<?php

if (!defined('ABSPATH')) {
	exit;
}

/**
 * IGW Admin Cleanup rule library.
 *
 * Individual plugin rules are stored in separate files
 * to keep the library easy to maintain and extend.
 */

$library_files = [
	'wordfence.php',
	'wpcode.php',
	'limit-login-attempts-reloaded.php',
	'jeg-elementor-kit.php',
	'monsterinsights.php',
	'ultimate-addons-for-elementor.php',
	'seedpro.php',
];

$rules = [];

foreach ($library_files as $library_file) {

	$file = __DIR__ . '/' . $library_file;

	if (!file_exists($file)) {
		continue;
	}

	$plugin_rules = require $file;

	if (!is_array($plugin_rules)) {
		continue;
	}

	$rules = array_merge(
		$rules,
		$plugin_rules
	);
}

return $rules;
