<?php /*
================================================================================
CommentPress for Multisite
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This used to be the CommentPress for Multisite plugin, but is now merged into
a unified plugin that covers all situations.

--------------------------------------------------------------------------------
*/





// define version
define( 'COMMENTPRESS_MU_PLUGIN_VERSION', '1.0' );






/*
--------------------------------------------------------------------------------
Init Multisite plugin
--------------------------------------------------------------------------------
*/

// do we have our class?
if ( !class_exists( 'CommentpressMultisiteLoader' ) ) {

	// define filename
	$class_file = 'commentpress-multisite/class_commentpress_mu_loader.php';

	// get path
	$class_file_path = commentpress_file_is_present( $class_file );
	
	// we're fine, include class definition
	require_once( $class_file_path );

	// define as global
	global $commentpress_mu;

	// instantiate it
	$commentpress_mu = new CommentpressMultisiteLoader;
	
}





/*
--------------------------------------------------------------------------------
Misc Utility Functions
--------------------------------------------------------------------------------
*/

/** 
 * @description: get WP plugin reference by name (since we never know for sure what the enclosing
 * directory is called)
 * @todo: 
 *
 */
function commentpress_mu_find_plugin_by_name( $plugin_name = '' ) {

	// kick out if no param supplied
	if ( $plugin_name == '' ) { return false; }



	// init path
	$path_to_plugin = false;
	
	// get plugins
	$plugins = get_plugins();
	//print_r( $plugins ); die();
	
	// because the key is the path to the plugin file, we have to find the
	// key by iterating over the values (which are arrays) to find the
	// plugin with the desired name. Doh!
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





/*
--------------------------------------------------------------------------------
Force a plugin to activate: adapted from https://gist.github.com/1966425
Audited with reference to activate_plugin() with extra commenting inline
--------------------------------------------------------------------------------
*/

/** 
 * @description: Helper to activate a plugin on another site without causing a 
 * fatal error by including the plugin file a second time
 * Based on activate_plugin() in wp-admin/includes/plugin.php
 * $buffer option is used for plugins which send output
 * @todo: 
 *
 */
function commentpress_mu_activate_plugin( $plugin, $buffer = false ) {
	
	// find our already active plugins
	$current = get_option( 'active_plugins', array() );
	
	// no need to validate it...
	
	// check that the plugin isn't already active
	if ( !in_array( $plugin, $current ) ) {
	
		// no need to redirect...
	
		// open buffer if required
		if ( $buffer ) { ob_start(); }
		
		// safe include
		// Note: this a valid use of WP_PLUGIN_DIR since there is no plugins_dir()
		include_once( WP_PLUGIN_DIR . '/' . $plugin );
		
		// no need to check silent activation, just go ahead...
		do_action( 'activate_plugin', $plugin );
		do_action( 'activate_' . $plugin );
		
		// housekeeping
		$current[] = $plugin;
		sort( $current );
		update_option( 'active_plugins', $current );
		do_action( 'activated_plugin', $plugin );
		
		// close buffer if required
		if ( $buffer ) { ob_end_clean(); }

	}

}






/** 
 * @description: utility to show theme environment
 * @todo: 
 *
 */
function _commentpress_mu_environment() {
	
	// don't show in admin
	if ( !is_admin() ) {
		
		// dump our environment
		echo '<strong>TEMPLATEPATH</strong><br />'.TEMPLATEPATH.'<br /><br />';
		echo '<strong>STYLESHEETPATH</strong><br />'.STYLESHEETPATH.'<br /><br />';
		echo '<strong>template_directory</strong><br />'.get_bloginfo('template_directory').'<br /><br />';	
		echo '<strong>stylesheet_directory</strong><br />'.get_bloginfo('stylesheet_directory').'<br /><br />';
		echo '<strong>template_url</strong><br />'.get_bloginfo('template_url').'<br /><br />';	
		echo '<strong>stylesheet_url</strong><br />'.get_bloginfo('stylesheet_url').'<br /><br />';
		echo '<strong>get_template_directory</strong><br />'.get_template_directory().'<br /><br />';
		echo '<strong>get_stylesheet_directory</strong><br />'.get_stylesheet_directory().'<br /><br />';
		echo '<strong>get_stylesheet_directory_uri</strong><br />'.get_stylesheet_directory_uri().'<br /><br />';
		echo '<strong>get_template_directory_uri</strong><br />'.get_template_directory_uri().'<br /><br />';
		echo '<strong>locate_template</strong><br />'.locate_template( array( 'style/js/cp_js_common.js' ), false ).'<br /><br />';
		die();
	
	}
	
}

//add_action( 'template_redirect', '_commentpress_mu_environment' );






/** 
 * @description: utility to show tests
 * @todo: 
 *
 */
function _commentpress_mu_test() {

	global $commentpress_core;
	//print_r( $commentpress_core ); die();
	
}

//add_action( 'wp_head', '_commentpress_mu_test' );





