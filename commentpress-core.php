<?php
/**
 * Plugin Name: CommentPress Core
 * Plugin URI: http://www.futureofthebook.org/commentpress/
 * Description: CommentPress allows readers to comment in the margins of a text. You can use it to annotate, gloss, workshop, debate and more!
 * Author: Institute for the Future of the Book
 * Version: 4.0a
 * Author URI: http://www.futureofthebook.org
 * Text Domain: commentpress-core
 * Domain Path: /languages
 * -----------------------------------------------------------------------------
 * Special thanks to:
 * Eddie Tejeda @ http://www.visudo.com for CommentPress 2.0
 * Mark James for the icons: http://www.famfamfam.com/lab/icons/silk/
 * -----------------------------------------------------------------------------
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Set version.
define( 'COMMENTPRESS_VERSION', '4.0a' );

// Store reference to this file.
if ( ! defined( 'COMMENTPRESS_PLUGIN_FILE' ) ) {
	define( 'COMMENTPRESS_PLUGIN_FILE', __FILE__ );
}

// Store URL to this plugin's directory.
if ( ! defined( 'COMMENTPRESS_PLUGIN_URL' ) ) {
	define( 'COMMENTPRESS_PLUGIN_URL', plugin_dir_url( COMMENTPRESS_PLUGIN_FILE ) );
}
// Store PATH to this plugin's directory.
if ( ! defined( 'COMMENTPRESS_PLUGIN_PATH' ) ) {
	define( 'COMMENTPRESS_PLUGIN_PATH', plugin_dir_path( COMMENTPRESS_PLUGIN_FILE ) );
}

/*
 * -----------------------------------------------------------------------------
 * Begin by establishing Plugin Context.
 * -----------------------------------------------------------------------------
 * NOTE: force-activated context is now deprecated.
 * -----------------------------------------------------------------------------
 */

// Test for multisite location.
if ( basename( dirname( COMMENTPRESS_PLUGIN_FILE ) ) == 'mu-plugins' ) {

	// Directory-based forced activation.
	if ( ! defined( 'COMMENTPRESS_PLUGIN_CONTEXT' ) ) {
		define( 'COMMENTPRESS_PLUGIN_CONTEXT', 'mu_forced' );
	}

// Test for multisite.
} elseif ( is_multisite() ) {

	// Check if our plugin is one of those activated sitewide.
	$this_plugin = plugin_basename( COMMENTPRESS_PLUGIN_FILE );

	/*
	 * Unfortunately, is_plugin_active_for_network() is not yet available so we
	 * have to do this manually.
	 *
	 * Also note: during network activation, this plugin is not yet present in
	 * the active_sitewide_plugins array.
	 */

	// Get sitewide plugins.
	$active_plugins = (array) get_site_option( 'active_sitewide_plugins' );

	// Is the plugin network activated?
	if ( isset( $active_plugins[ $this_plugin ] ) ) {

		// Yes, network activated.
		if ( ! defined( 'COMMENTPRESS_PLUGIN_CONTEXT' ) ) {
			define( 'COMMENTPRESS_PLUGIN_CONTEXT', 'mu_sitewide' );
		}

	} else {

		// Optional activation per blog in multisite.
		if ( ! defined( 'COMMENTPRESS_PLUGIN_CONTEXT' ) ) {
			define( 'COMMENTPRESS_PLUGIN_CONTEXT', 'mu_optional' );
		}

	}

} else {

	// Single user install.
	if ( ! defined( 'COMMENTPRESS_PLUGIN_CONTEXT' ) ) {
		define( 'COMMENTPRESS_PLUGIN_CONTEXT', 'standard' );
	}

}

/**
 * Utility to check for presence of vital files.
 *
 * @since 3.0
 *
 * @param string $filename the name of the CommentPress Core Plugin file.
 * @return string $filepath absolute path to file.
 */
function commentpress_file_is_present( $filename ) {

	// Define path to our requested file.
	$filepath = COMMENTPRESS_PLUGIN_PATH . $filename;

	// Die if the file is not present.
	if ( ! is_file( $filepath ) ) {
		wp_die( sprintf( __( 'CommentPress Core Error: file "%s" is missing from the plugin directory.', 'commentpress-core' ), $filepath ) );
	}

	// --<
	return $filepath;

}

/**
 * Utility to include the core plugin.
 *
 * @since 3.4
 */
function commentpress_include_core() {

	// Do we have our class?
	if ( ! class_exists( 'CommentPress_Core' ) ) {

		// Include class definition.
		require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-core/class_commentpress.php';

	}

}

/**
 * Utility to activate the core plugin.
 *
 * @since 3.4
 */
function commentpress_activate_core() {

	// Declare as global.
	global $commentpress_core;

	// Do we have it already?
	if ( is_null( $commentpress_core ) ) {

		// Instantiate it.
		$commentpress_core = new CommentPress_Core();

	}

}

/**
 * Utility to activate the AJAX commenting plugin.
 *
 * @since 3.4
 */
function commentpress_activate_ajax() {

	// Include AJAX file.
	require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-ajax/cp-ajax-comments.php';

}

/**
 * Utility to amend filenames when debugging.
 *
 * @since 3.8.5
 *
 * @return str The debug string to be included in a filename.
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
 * Utility to add link to settings page.
 *
 * @since 3.4
 *
 * @param array $links The existing links array.
 * @param str $file The name of the plugin file.
 * @return array $links The modified links array.
 */
function commentpress_plugin_action_links( $links, $file ) {

	// Add settings link.
	if ( $file == plugin_basename( dirname( __FILE__ ) . '/commentpress-core.php' ) ) {

		// Is this Network Admin?
		if ( is_network_admin() ) {
			$link = add_query_arg( [ 'page' => 'cpmu_admin_page' ], network_admin_url( 'settings.php' ) );
		} else {
			$link = add_query_arg( [ 'page' => 'commentpress_admin' ], admin_url( 'options-general.php' ) );
		}

		// Add settings link.
		$links[] = '<a href="' . esc_url( $link ) . '">' . esc_html__( 'Settings', 'commentpress-core' ) . '</a>';

		// Add Paypal link.
		$paypal = 'https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=PZSKM8T5ZP3SC';
		$links[] = '<a href="' . $paypal . '" target="_blank">' . __( 'Donate!', 'commentpress-core' ) . '</a>';

	}

	// --<
	return $links;

}

// Add filters for the above.
add_filter( 'network_admin_plugin_action_links', 'commentpress_plugin_action_links', 10, 2 );
add_filter( 'plugin_action_links', 'commentpress_plugin_action_links', 10, 2 );

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
	if ( $plugin_name == '' ) {
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

	// Because the key is the path to the plugin file, we have to find the
	// key by iterating over the values (which are arrays) to find the
	// plugin with the name we want. Doh!
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

/*
 * -----------------------------------------------------------------------------
 * NOTE: in multisite, child themes are registered as broken if the plugin
 * is not network-enabled. Make sure child themes have instructions.
 * -----------------------------------------------------------------------------
 * There are further complex issues when in Multisite:
 *
 * First scenario:
 * if the plugin is NOT initially network-enabled
 * but it IS enabled on one or more blogs on the network
 * and the plugin in THEN network-enabled
 *
 * Second scenario:
 * if the plugin IS initially network-enabled
 * and it IS activated on one or more blogs on the network
 * and the plugin in THEN network-disabled
 *
 * If installs stick to one or the other, then all works as expected.
 * -----------------------------------------------------------------------------
 */

// Register our themes directory.
register_theme_directory( plugin_dir_path( COMMENTPRESS_PLUGIN_FILE ) . 'themes' );

/*
--------------------------------------------------------------------------------
Include Standalone.
--------------------------------------------------------------------------------
*/

commentpress_include_core();

/*
--------------------------------------------------------------------------------
Init Standalone.
--------------------------------------------------------------------------------
*/

// Note: we exclude activation on network admin pages to avoid auto-installation
// On main site when the plugin is network activated.

// Only activate if in standard or mu_optional context.
if (
	COMMENTPRESS_PLUGIN_CONTEXT == 'standard' ||
	( COMMENTPRESS_PLUGIN_CONTEXT == 'mu_optional' && ! is_network_admin() )
) {

	// CommentPress Core.
	commentpress_activate_core();

	// Access global.
	global $commentpress_core;

	// Activation.
	register_activation_hook( COMMENTPRESS_PLUGIN_FILE, [ $commentpress_core, 'activate' ] );

	// Deactivation.
	register_deactivation_hook( COMMENTPRESS_PLUGIN_FILE, [ $commentpress_core, 'deactivate' ] );

	/*
	 * Uninstall uses the 'uninstall.php' method.
	 * @see https://developer.wordpress.org/reference/functions/register_uninstall_hook/
	 */

	// AJAX Commenting.
	commentpress_activate_ajax();

}

/*
--------------------------------------------------------------------------------
Init Multisite.
--------------------------------------------------------------------------------
*/

// Have we activated network-wide?
if ( COMMENTPRESS_PLUGIN_CONTEXT == 'mu_sitewide' ) {

	// Activate multisite plugin.

	// Include class definition.
	require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-multisite/class_commentpress_mu_loader.php';

	// Define as global.
	global $commentpress_mu;

	// Instantiate it.
	$commentpress_mu = new CommentPress_Multisite_Loader();

}
