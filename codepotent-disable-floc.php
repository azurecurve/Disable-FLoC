<?php

/**
 * -----------------------------------------------------------------------------
 * Plugin Name:		Disable FloC
 * Description:		Disable Google's next-gen tracking technology.
 * Version:			1.1.2
 * Requires CP:		1.0
 * Author:			azurecurve
 * Author URI:		https://dev.azrcrv.co.uk/classicpress-plugins
 * Plugin URI:		https://dev.azrcrv.co.uk/classicpress-plugins
 * Donate link:		https://development.azurecurve.co.uk/support-development/
 * Text Domain:		codepotent-disable-floc
 * Domain Path:		/languages
 * License:			GPLv2 or later
 * License URI:		http://www.gnu.org/licenses/gpl-2.0.html
 * -----------------------------------------------------------------------------
 * This is free software released under the terms of the General Public License,
 * version 2, or later. It is distributed WITHOUT ANY WARRANTY; without even the
 * implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. Full
 * text of the license is available at https://www.gnu.org/licenses/gpl-2.0.txt.
 * -----------------------------------------------------------------------------
 * Copyright 2021, John Alarcon (Code Potent)
 * -----------------------------------------------------------------------------
 * Adopted by azurecurve, 06/01/2021
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

		// Load constants.
		require_once plugin_dir_path(__FILE__).'includes/constants.php';

		// Load update client.
		require_once(PATH_CLASSES.'/UpdateClient.class.php');

		// Add http header to disable FLoC.
		add_filter('wp_headers', [$this, 'append_http_header']);

		// POST-ADOPTION: Remove these actions before pushing your next update.
		add_action('upgrader_process_complete', [$this, 'enable_adoption_notice'], 10, 2);
		add_action('admin_notices', [$this, 'display_adoption_notice']);

	}

	// POST-ADOPTION: Remove this method before pushing your next update.
	public function enable_adoption_notice($upgrader_object, $options) {
		if ($options['action'] === 'update') {
			if ($options['type'] === 'plugin') {
				if (!empty($options['plugins'])) {
					if (in_array(plugin_basename(__FILE__), $options['plugins'])) {
						set_transient(PLUGIN_PREFIX.'_adoption_complete', 1);
					}
				}
			}
		}
	}

	// POST-ADOPTION: Remove this method before pushing your next update.
	public function display_adoption_notice() {
		if (get_transient(PLUGIN_PREFIX.'_adoption_complete')) {
			delete_transient(PLUGIN_PREFIX.'_adoption_complete');
			echo '<div class="notice notice-success is-dismissible">';
			echo '<h3 style="margin:25px 0 15px;padding:0;color:#e53935;">IMPORTANT <span style="color:#aaa;">information about the <strong style="color:#333;">'.PLUGIN_NAME.'</strong> plugin</h3>';
			echo '<p style="margin:0 0 15px;padding:0;font-size:14px;">The <strong>'.PLUGIN_NAME.'</strong> plugin has been officially adopted and is now managed by <a href="'.PLUGIN_AUTHOR_URL.'" rel="noopener" target="_blank" style="text-decoration:none;">'.PLUGIN_AUTHOR.'<span class="dashicons dashicons-external" style="display:inline;font-size:98%;"></span></a>, a longstanding and trusted ClassicPress developer and community member. While it has been wonderful to serve the ClassicPress community with free plugins, tutorials, and resources for nearly 3 years, it\'s time that I move on to other endeavors. This notice is to inform you of the change, and to assure you that the plugin remains in good hands. I\'d like to extend my heartfelt thanks to you for making my plugins a staple within the community, and wish you great success with ClassicPress!</p>';
			echo '<p style="margin:0 0 15px;padding:0;font-size:14px;font-weight:600;">All the best!</p>';
			echo '<p style="margin:0 0 15px;padding:0;font-size:14px;">~ John Alarcon <span style="color:#aaa;">(Code Potent)</span></p>';
			echo '</div>';
		}
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