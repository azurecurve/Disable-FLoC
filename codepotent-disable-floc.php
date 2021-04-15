<?php

/**
 * -----------------------------------------------------------------------------
 * Plugin Name: Disable FloC
 * Description: Disable Google's next-gen tracking technology.
 * Version: 1.0.0
 * Author: Code Potent
 * Author URI: https://codepotent.com
 * Plugin URI: https://codepotent.com/classicpress/plugins/
 * Text Domain: codepotent-disable-floc
 * Domain Path: /languages
 * -----------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.txt.
 * -----------------------------------------------------------------------------
 * Copyright 2021, Code Potent
 * -----------------------------------------------------------------------------
 *           ____          _      ____       _             _
 *          / ___|___   __| | ___|  _ \ ___ | |_ ___ _ __ | |_
 *         | |   / _ \ / _` |/ _ \ |_) / _ \| __/ _ \ '_ \| __|
 *         | |__| (_) | (_| |  __/  __/ (_) | ||  __/ | | | |_
 *          \____\___/ \__,_|\___|_|   \___/ \__\___|_| |_|\__|.com
 *
 * -----------------------------------------------------------------------------
 */

// Declare the namespace.
namespace CodePotent\DisableFLOC;

// Prevent direct access.
if (!defined('ABSPATH')) {
	die();
}

class DisableFLOC {


	public function __construct() {

		$this->init();

	}

	public function init() {

		// Load plugin update class.
		require_once(plugin_dir_path(__FILE__).'classes/UpdateClient.class.php');

		// Add http header to disable FLoC.
		add_filter('wp_headers', [$this, 'append_http_header']);

	}

	public function append_http_header($headers) {

		// No Permissions-Policy header present? Add one and return.
		if (empty($headers['Permissions-Policy'])) {
			$headers['Permissions-Policy'] = 'interest-cohort=()';
			return $headers;
		}

		// Separate Permissions-Policy directives.
		$policies = explode(',', $headers['Permissions-Policy']);

		// Check for existence of interest-cohort directive; set flag.
		foreach ($policies as $n=>$policy) {
			$policies[$n] = $policy = trim($policy);
			if (stripos($policy, 'interest-cohort') === 0) {
				$directive_present = true;
			}
		}

		// If interest-cohort directive not present, add it.
		if (!isset($directive_present)) {
			$policies[] = 'interest-cohort=()';
		}

		// Assign policies to the header.
		$headers['Permissions-Policy'] = implode(', ', $policies);

		// Return headers.
		return $headers;

	}

}

// FLoC you, Google!
new DisableFLOC;