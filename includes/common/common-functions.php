<?php
/**
 * CommentPress Core Functions.
 *
 * Functions that are required globally are collected here.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Gets a reference to the core plugin object.
 *
 * @since 4.0
 *
 * @return CommentPress_Core_Loader $commentpress_core The core plugin reference, or false on failure.
 */
function commentpress_core() {
	return commentpress()->core();
}

/**
 * Gets a reference to the multisite plugin object.
 *
 * @since 4.0
 *
 * @return CommentPress_Multisite_Loader $commentpress_mu The multisite plugin reference, or false on failure.
 */
function commentpress_multisite() {
	return commentpress()->multisite();
}

/**
 * Gets the string to be included in a filename when in script debugging mode.
 *
 * @since 3.8.5
 *
 * @return str $minified The string to be included in a filename.
 */
function commentpress_minified() {

	// Default to minified scripts.
	$minified = '.min';

	// Target unminified scripts when debugging.
	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG === true ) {
		$minified = '';
	}

	// --<
	return $minified;

}

/**
 * Gets a plugin reference by name.
 *
 * This is required because we never know for sure what the enclosing directory
 * is called.
 *
 * @since 3.4
 *
 * @param str $plugin_name The name of the plugin.
 * @return str $path_to_plugin The path to the plugin.
 */
function commentpress_find_plugin_by_name( $plugin_name = '' ) {

	// Kick out if no param supplied.
	if ( empty( $plugin_name ) ) {
		return false;
	}

	// Init path.
	$path_to_plugin = false;

	// Ensure function is available.
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	// Get plugins.
	$plugins = get_plugins();

	/*
	 * Because the key is the path to the plugin file, we have to find the
	 * key by iterating over the values (which are arrays) to find the
	 * plugin with the name we want.
	 */
	foreach ( $plugins as $key => $plugin ) {

		// Is it ours?
		if ( $plugin['Name'] == $plugin_name ) {

			// Now get the key, which is our path.
			$path_to_plugin = $key;
			break;

		}

	}

	// --<
	return $path_to_plugin;

}
