<?php
/**
 * CommentPress Core Uninstaller.
 *
 * @package CommentPress_Core
 */

// Kick out if uninstall not called from WordPress.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

/**
 * Restore WordPress database schema.
 *
 * In Multisite, this needs to target the Sites on which CommentPress Core was active.
 *
 * @return boolean $result The result of the database operation.
 */
function commentpress_schema_restore() {

	// Database object.
	global $wpdb;

	// Include WordPress install helper script.
	require_once ABSPATH . 'wp-admin/install-helper.php';

	// Drop the column, if already there.
	$result = maybe_drop_column(
		$wpdb->comments,
		'comment_signature',
		"ALTER TABLE `$wpdb->comments` DROP `comment_signature`;"
	);

	// --<
	return $result;

}

// Restore database schema.
$success = commentpress_schema_restore();

// Write something to the logs on failure.
if ( ! $success ) {
	$e = new \Exception();
	$trace = $e->getTraceAsString();
	error_log( print_r( [
		'commentpress-uninstall-error' => __( 'Could not drop comment signature column.', 'commentpress-core' ),
	], true ) );
}

/*
 * Delete core options.
 *
 * Make sure these match those declared in CommentPress_Core_Database.
 */
delete_option( 'commentpress_version' );
delete_option( 'commentpress_options' );

// Are we deleting in multisite?
if ( is_multisite() ) {

	/*
	 * Delete multisite options.
	 *
	 * Make sure these match those declared in CommentPress_Multisite_Database.
	 */
	delete_site_option( 'cpmu_options' );
	delete_site_option( 'cpmu_version' );

}
