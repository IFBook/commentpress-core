<?php /*
================================================================================
CommentPress Core Uninstaller Version 1.0
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====


--------------------------------------------------------------------------------
*/



// kick out if uninstall not called from WordPress
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) { exit(); }





/**
 * Restore WordPress database schema
 *
 * @return boolean $result The result of the database operation
 */
function commentpress_schema_restore() {

	// database object
	global $wpdb;

	// include WordPress install helper script
	require_once( ABSPATH . 'wp-admin/install-helper.php' );

	// drop the column, if already there
	$result = maybe_drop_column(

		$wpdb->comments,
		'comment_signature',
		"ALTER TABLE `$wpdb->comments` DROP `comment_signature`;"

	);

	// --<
	return $result;
}





// delete standalone options
delete_option( 'commentpress_version' );
delete_option( 'commentpress_options' );

// restore database schema
$success = commentpress_schema_restore();
// do we care about the result?


// are we deleting in multisite?
if ( is_multisite() ) {

	// delete multisite options
	delete_site_option( 'cpmu_options' );
	delete_site_option( 'cpmu_version' );

}





