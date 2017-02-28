<?php /*
--------------------------------------------------------------------------------
Plugin Name: CommentPress Core
Plugin URI: http://www.futureofthebook.org/commentpress/
Description: CommentPress allows readers to comment in the margins of a text. You can use it to annotate, gloss, workshop, debate and more!
Author: Institute for the Future of the Book
Version: 3.9.3
Author URI: http://www.futureofthebook.org
Text Domain: commentpress-core
Domain Path: /languages
--------------------------------------------------------------------------------
Special thanks to:
Eddie Tejeda @ http://www.visudo.com for CommentPress 2.0
Mark James for the icons: http://www.famfamfam.com/lab/icons/silk/
--------------------------------------------------------------------------------
*/



// set version
define( 'COMMENTPRESS_VERSION', '3.9.3' );

// store reference to this file
if ( ! defined( 'COMMENTPRESS_PLUGIN_FILE' ) ) {
	define( 'COMMENTPRESS_PLUGIN_FILE', __FILE__ );
}

// store URL to this plugin's directory
if ( ! defined( 'COMMENTPRESS_PLUGIN_URL' ) ) {
	define( 'COMMENTPRESS_PLUGIN_URL', plugin_dir_url( COMMENTPRESS_PLUGIN_FILE ) );
}
// store PATH to this plugin's directory
if ( ! defined( 'COMMENTPRESS_PLUGIN_PATH' ) ) {
	define( 'COMMENTPRESS_PLUGIN_PATH', plugin_dir_path( COMMENTPRESS_PLUGIN_FILE ) );
}



/*
--------------------------------------------------------------------------------
Begin by establishing Plugin Context
--------------------------------------------------------------------------------
NOTE: force-activated context is now deprecated
--------------------------------------------------------------------------------
*/

// test for multisite location
if ( basename( dirname( COMMENTPRESS_PLUGIN_FILE ) ) == 'mu-plugins' ) {

	// directory-based forced activation
	if ( ! defined( 'COMMENTPRESS_PLUGIN_CONTEXT' ) ) {
		define( 'COMMENTPRESS_PLUGIN_CONTEXT', 'mu_forced' );
	}

// test for multisite
} elseif ( is_multisite() ) {

	// check if our plugin is one of those activated sitewide
	$this_plugin = plugin_basename( COMMENTPRESS_PLUGIN_FILE );

	/**
	 * Unfortunately, is_plugin_active_for_network() is not yet available so we
	 * have to do this manually.
	 *
	 * Also note: during network activation, this plugin is not yet present in
	 * the active_sitewide_plugins array.
	 */

	// get sitewide plugins
	$active_plugins = (array) get_site_option( 'active_sitewide_plugins' );

	// is the plugin network activated?
	if ( isset( $active_plugins[$this_plugin] ) ) {

		// yes, network activated
		if ( ! defined( 'COMMENTPRESS_PLUGIN_CONTEXT' ) ) {
			define( 'COMMENTPRESS_PLUGIN_CONTEXT', 'mu_sitewide' );
		}

	} else {

		// optional activation per blog in multisite
		if ( ! defined( 'COMMENTPRESS_PLUGIN_CONTEXT' ) ) {
			define( 'COMMENTPRESS_PLUGIN_CONTEXT', 'mu_optional' );
		}

	}

} else {

	// single user install
	if ( ! defined( 'COMMENTPRESS_PLUGIN_CONTEXT' ) ) {
		define( 'COMMENTPRESS_PLUGIN_CONTEXT', 'standard' );
	}

}



/**
 * Utility to check for presence of vital files.
 *
 * @since 3.0
 *
 * @param string $filename the name of the CommentPress Core Plugin file
 * @return string $filepath absolute path to file
 */
function commentpress_file_is_present( $filename ) {

	// define path to our requested file
	$filepath = COMMENTPRESS_PLUGIN_PATH . $filename;

	// die if the file is not present
	if ( ! is_file( $filepath ) ) {
		wp_die( sprintf(
			__( 'CommentPress Core Error: file "%s" is missing from the plugin directory.', 'commentpress-core' ),
			$filepath )
		);
	}

	// --<
	return $filepath;

}



/**
 * Utility to include the core plugin.
 *
 * @since 3.4
 *
 * @return void
 */
function commentpress_include_core() {

	// do we have our class?
	if ( ! class_exists( 'Commentpress_Core' ) ) {

		// define filename
		$file = 'commentpress-core/class_commentpress.php';

		// get path
		$file_path = commentpress_file_is_present( $file );

		// we're fine, include class definition
		require_once( $file_path );

	}

}



/**
 * Utility to activate the core plugin.
 *
 * @since 3.4
 *
 * @return void
 */
function commentpress_activate_core() {

	// declare as global
	global $commentpress_core;

	// do we have it already?
	if ( is_null( $commentpress_core ) ) {

		// instantiate it
		$commentpress_core = new Commentpress_Core;

	}

}



/**
 * Utility to activate the AJAX commenting plugin.
 *
 * @since 3.4
 *
 * @return void
 */
function commentpress_activate_ajax() {

	// define filename
	$file = 'commentpress-ajax/cp-ajax-comments.php';

	// get path
	$file_path = commentpress_file_is_present( $file );

	// we're fine, include ajax file
	require_once( $file_path );

}



/**
 * Utility to amend filenames when debugging.
 *
 * @since 3.8.5
 *
 * @return str The debug string to be included in a filename
 */
function commentpress_minified() {

	// default to minified scripts
	$minified = '.min';

	// target unminified scripts when debugging
	if ( defined( 'SCRIPT_DEBUG' ) AND SCRIPT_DEBUG === true ) {
		$minified = '';
	}

	// --<
	return $minified;

}



/**
 * Shortcut for debugging.
 *
 * @since 3.0
 *
 * @param str The debug string to be sent the the browser
 */
function _cpdie( $var ) {

	print '<pre>';
	print_r( $var );
	print '</pre>';
	die();

}



/**
 * Utility to add link to settings page.
 *
 * @since 3.4
 *
 * @param array $links The existing links array
 * @param str $file The name of the plugin file
 * @return array $links The modified links array
 */
function commentpress_plugin_action_links( $links, $file ) {

	// add settings link
	if ( $file == plugin_basename( dirname( __FILE__ ) . '/commentpress-core.php' ) ) {

		// is this Network Admin?
		if ( is_network_admin() ) {
			$link = add_query_arg( array( 'page' => 'cpmu_admin_page' ), network_admin_url( 'settings.php' ) );
		} else {
			$link = add_query_arg( array( 'page' => 'commentpress_admin' ), admin_url( 'options-general.php' ) );
		}

		// add settings link
		$links[] = '<a href="' . esc_url( $link ) . '">' . esc_html__( 'Settings', 'commentpress-core' ) . '</a>';

		// add Paypal link
		$paypal = 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=PZSKM8T5ZP3SC';
		$links[] = '<a href="' . $paypal . '" target="_blank">' . __( 'Donate!', 'commentpress-core' ) . '</a>';

	}

	// --<
	return $links;

}

// add filters for the above
add_filter( 'network_admin_plugin_action_links', 'commentpress_plugin_action_links', 10, 2 );
add_filter( 'plugin_action_links', 'commentpress_plugin_action_links', 10, 2 );



/**
 * Get WP plugin reference by name.
 *
 * This is required because we never know for sure what the enclosing directory
 * is called.
 *
 * @since 3.4
 *
 * @param str $plugin_name The name of the plugin
 * @return str $path_to_plugin The path to the plugin
 */
function commentpress_find_plugin_by_name( $plugin_name = '' ) {

	// kick out if no param supplied
	if ( $plugin_name == '' ) return false;

	// init path
	$path_to_plugin = false;

	// ensure function is available
	if ( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	// get plugins
	$plugins = get_plugins();

	// because the key is the path to the plugin file, we have to find the
	// key by iterating over the values (which are arrays) to find the
	// plugin with the name we want. Doh!
	foreach( $plugins AS $key => $plugin ) {

		// is it ours?
		if ( $plugin['Name'] == $plugin_name ) {

			// now get the key, which is our path
			$path_to_plugin = $key;
			break;

		}

	}

	// --<
	return $path_to_plugin;

}



/**
 * Test if the old pre-3.4 CommentPress plugin is active.
 *
 * @since 3.4
 *
 * @return bool $active True if the legacy plugin is active, false otherwise
 */
function commentpress_is_legacy_plugin_active() {

	// assume not
	$active = false;

	// get old options
	$old = get_option( 'cp_options', array() );

	// test if we have a existing pre-3.4 CommentPress instance
	if ( is_array( $old ) AND count( $old ) > 0 ) {

		// if we have "special pages", then the plugin must be active on this blog
		// NB: do we need to check is_plugin_active() as well (or instead)?
		if ( isset( $old['cp_special_pages'] ) ) {

			// set flag
			$active = true;

		}

	}

	// --<
	return $active;

}



/*
--------------------------------------------------------------------------------
NOTE: in multisite, child themes are registered as broken if the plugin is not
network-enabled. Make sure child themes have instructions.
--------------------------------------------------------------------------------
There are further complex issues when in Multisite:

First scenario:
* if the plugin is NOT initially network-enabled
* but it IS enabled on one or more blogs on the network
* and the plugin in THEN network-enabled

Second scenario:
* if the plugin IS initially network-enabled
* and it IS activated on one or more blogs on the network
* and the plugin in THEN network-disabled

If installs stick to one or the other, then all works as expected.
--------------------------------------------------------------------------------
*/

// register our themes directory
register_theme_directory( plugin_dir_path( COMMENTPRESS_PLUGIN_FILE ) . 'themes' );



/*
--------------------------------------------------------------------------------
Include Standalone
--------------------------------------------------------------------------------
*/

commentpress_include_core();



/*
--------------------------------------------------------------------------------
Init Standalone
--------------------------------------------------------------------------------
*/

// note: we exclude activation on network admin pages to avoid auto-installation
// on main site when the plugin is network activated

// only activate if in standard or mu_optional context
if (
	COMMENTPRESS_PLUGIN_CONTEXT == 'standard' OR
	( COMMENTPRESS_PLUGIN_CONTEXT == 'mu_optional' AND ! is_network_admin() )
) {

	// CommentPress Core
	commentpress_activate_core();

	// access global
	global $commentpress_core;

	// activation
	register_activation_hook( COMMENTPRESS_PLUGIN_FILE, array( $commentpress_core, 'activate' ) );

	// deactivation
	register_deactivation_hook( COMMENTPRESS_PLUGIN_FILE, array( $commentpress_core, 'deactivate' ) );

	// uninstall uses the 'uninstall.php' method
	// see: http://codex.wordpress.org/Function_Reference/register_uninstall_hook

	// AJAX Commenting
	commentpress_activate_ajax();

}



/*
--------------------------------------------------------------------------------
Init Multisite
--------------------------------------------------------------------------------
*/

// have we activated network-wide?
if ( COMMENTPRESS_PLUGIN_CONTEXT == 'mu_sitewide' ) {

	// activate multisite plugin

	// define filename
	$file = 'commentpress-multisite/class_commentpress_mu_loader.php';

	// get path
	$file_path = commentpress_file_is_present( $file );

	// we're fine, include class definition
	require_once( $file_path );

}



