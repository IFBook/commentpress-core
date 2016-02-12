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
if ( ! class_exists( 'CommentpressMultisiteLoader' ) ) {

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



