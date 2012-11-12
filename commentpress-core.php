<?php /*
--------------------------------------------------------------------------------
Plugin Name: CommentPress Core
Plugin URI: http://www.futureofthebook.org/commentpress/
Description: CommentPress allows readers to comment paragraph by paragraph in the margins of a text. You can use it to annotate, gloss, workshop, debate and more!
Author: Institute for the Future of the Book
Version: 3.4
Author URI: http://www.futureofthebook.org
--------------------------------------------------------------------------------
Special thanks to:
Eddie Tejeda @ http://www.visudo.com for Commentpress 2.0
Mark James for the icons: http://www.famfamfam.com/lab/icons/silk/
--------------------------------------------------------------------------------
*/





// -----------------------------------------------------------------------------
// No need to edit below this line
// -----------------------------------------------------------------------------

// set version
define( 'COMMENTPRESS_VERSION', '3.4' );

// store reference to this file
if ( !defined( 'COMMENTPRESS_PLUGIN_FILE' ) ) {
	define( 'COMMENTPRESS_PLUGIN_FILE', __FILE__ );
}

// store URL to this plugin's directory
if ( !defined( 'COMMENTPRESS_PLUGIN_URL' ) ) {
	define( 'COMMENTPRESS_PLUGIN_URL', plugin_dir_url( COMMENTPRESS_PLUGIN_FILE ) );
}
// store PATH to this plugin's directory
if ( !defined( 'COMMENTPRESS_PLUGIN_PATH' ) ) {
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
	if ( !defined( 'COMMENTPRESS_PLUGIN_CONTEXT' ) ) {
		define( 'COMMENTPRESS_PLUGIN_CONTEXT', 'mu_forced' );
	}
	
// test for multisite
} elseif ( is_multisite() ) {

	// check if our plugin is one of those activated sitewide
	$this_plugin = plugin_basename( COMMENTPRESS_PLUGIN_FILE );
	
	// unfortunately, is_plugin_active_for_network() is not yet available so
	// we have to do this manually...
	
	// get sitewide plugins
	$active_plugins = (array) get_site_option( 'active_sitewide_plugins' );
	
	// is the plugin network activated?
	if ( isset( $active_plugins[ $this_plugin ] ) ) {
	
		// yes, network activated
		if ( !defined( 'COMMENTPRESS_PLUGIN_CONTEXT' ) ) {
			define( 'COMMENTPRESS_PLUGIN_CONTEXT', 'mu_sitewide' );
		}
		
	} else {

		// optional activation per blog in multisite
		if ( !defined( 'COMMENTPRESS_PLUGIN_CONTEXT' ) ) {
			define( 'COMMENTPRESS_PLUGIN_CONTEXT', 'mu_optional' );
		}
		
	}

} else {

	// single user install
	if ( !defined( 'COMMENTPRESS_PLUGIN_CONTEXT' ) ) {
		define( 'COMMENTPRESS_PLUGIN_CONTEXT', 'standard' );
	}
	
}

//print_r( COMMENTPRESS_PLUGIN_CONTEXT ); die();






/*
--------------------------------------------------------------------------------
Misc Utility Functions
--------------------------------------------------------------------------------
*/

/** 
 * @description: utility to check for presence of vital files
 * @param string $filename the name of the CommentPress Core Plugin file
 * @return string $filepath absolute path to file
 * @todo: 
 *
 */
function commentpress_file_is_present( $filename ) {

	// define path to our requested file
	$filepath = COMMENTPRESS_PLUGIN_PATH . $filename;

	// is our class definition present?
	if ( !is_file( $filepath ) ) {
	
		// oh no!
		die( 'CommentPress Core Error: file "'.$filepath.'" is missing from the plugin directory.' );
	
	}
	
	
	
	// --<
	return $filepath;

}






/** 
 * @description: utility to include the core plugin
 * @todo: 
 *
 */
function commentpress_include_core() {
	
	// do we have our class?
	if ( !class_exists( 'CommentpressCore' ) ) {
		
		// define filename
		$_file = 'commentpress-core/class_commentpress.php';
		
		// get path
		$_file_path = commentpress_file_is_present( $_file );
		
		// we're fine, include class definition
		require_once( $_file_path );
		
	}
	
}






/** 
 * @description: utility to activate the core plugin
 * @todo: 
 *
 */
function commentpress_activate_core() {
	
	// declare as global
	global $commentpress_core;
	
	// do we have it already?
	if ( is_null( $commentpress_core ) ) {
	
		// instantiate it
		$commentpress_core = new CommentpressCore;
	
	}

}






/** 
 * @description: utility to activate the ajax plugin
 * @todo: 
 *
 */
function commentpress_activate_ajax() {
	
	// define filename
	$_file = 'commentpress-ajax/cp-ajax-comments.php';

	// get path
	$_file_path = commentpress_file_is_present( $_file );
	
	// we're fine, include ajax file
	require_once( $_file_path );
		
}






/**
 * shortcut for debugging
 */
function _cpdie( $var ) {

	print '<pre>';
	print_r( $var ); 
	print '</pre>';
	die();
	
}






/** 
 * @description: utility to add link to settings page
 * @todo: 
 *
 */
function commentpress_plugin_action_links( $links, $file ) {
	
	// add settings link
	if ( $file == plugin_basename( dirname( __FILE__ ).'/commentpress-core.php' ) ) {
		$links[] = '<a href="options-general.php?page=commentpress_admin">'.__( 'Settings', 'commentpress-plugin' ).'</a>';
	}
	
	// --<
	return $links;

}

// add filter for the above
add_filter( 'plugin_action_links', 'commentpress_plugin_action_links', 10, 2 );






/** 
 * @description: get WP plugin reference by name (since we never know for sure what the enclosing
 * directory is called)
 * @todo: 
 *
 */
function commentpress_find_plugin_by_name( $plugin_name = '' ) {

	// kick out if no param supplied
	if ( $plugin_name == '' ) { return false; }



	// init path
	$path_to_plugin = false;
	
	// get plugins
	$plugins = get_plugins();
	//print_r( $plugins ); die();
	
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
 * @description: test if the old pre-3.4 Commentpress plugin is active
 * @todo: 
 *
 */
function commentpress_is_legacy_plugin_active() {

	// assume not
	$active = false;

	// get old options
	$old = get_option( 'cp_options', array() );
	
	// test if we have a existing pre-3.4 Commentpress instance
	if ( is_array( $old ) AND count( $old ) > 0 ) {
	
		// if we have "special pages", then the plugin must be active on this blog
		// NB: do we need to check is_plugin_active() as well (or instead)?
		if ( isset( $old[ 'cp_special_pages' ] ) ) {
		
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

// only activate if in standard or mu_optional context
if ( COMMENTPRESS_PLUGIN_CONTEXT == 'standard' OR COMMENTPRESS_PLUGIN_CONTEXT == 'mu_optional' ) {

	// CommentPress Core
	commentpress_activate_core();
	
	// access global
	global $commentpress_core;
	//print_r( $commentpress_core ); die();
	
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
	$_file = 'commentpress-multisite/commentpress-mu.php';

	// get path
	$_file_path = commentpress_file_is_present( $_file );
	
	// we're fine, include class definition
	require_once( $_file_path );

}





