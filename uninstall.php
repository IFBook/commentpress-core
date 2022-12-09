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
 * Restores all CommentPress-enabled Sites.
 *
 * NOTE: For a large Network, this could be a very lengthy process. There will
 * be an AJAX-driven UI for this task in future.
 *
 * @since 4.0
 */
function commentpress_sites_restore() {

	// Only restore current Site if not multisite.
	if ( ! is_multisite() ) {
		commentpress_schema_restore();
		commentpress_options_delete();
		return;
	}

	// Get the Site IDs.
	$site_ids = get_site_option( 'commentpress_sites', [] );
	if ( empty( $site_ids ) ) {
		return;
	}

	// Restore each Site.
	foreach ( $site_ids as $site_id ) {

		// We have to switch to the Blog.
		switch_to_blog( $site_id );

		// Restore WordPress database schema.
		commentpress_schema_restore();

		// Remove our custom Comment Taxonomy.
		commentpress_taxonomy_restore( 'comment_tags' );

		// Delete options.
		commentpress_options_delete();

	}

	// Restore.
	restore_current_blog();

	// Delete multisite options.
	commentpress_site_options_delete();

}

/**
 * Restores the WordPress database schema.
 *
 * @since 3.0
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

	// Write something to the logs on failure.
	if ( ! $result ) {

		// Build message.
		$message = sprintf(
			'Could not drop "comment_signature" column in table "%s".',
			$wpdb->comments
		);

		// Write to log.
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'CommentPress Uninstall Error' => $message,
		], true ) );

	}

	// --<
	return $result;

}

/**
 * Deletes a custom Taxonomy and all its data.
 *
 * @see https://gist.github.com/wpsmith/9285391#file-uninstall-terms-taxonomy-2-php
 *
 * @since 4.0
 *
 * @param str $taxonomy The name of the Taxonomy to delete.
 */
function commentpress_taxonomy_restore( $taxonomy ) {

	// Bail if we have CommentPress 4.0.x.
	if ( defined( 'COMMENTPRESS_VERSION' ) ) {
		if ( version_compare( COMMENTPRESS_VERSION, '3.9.20', '>' ) ) {
			return;
		}
	}

	// Access DB object.
	global $wpdb;

	// Get Terms.
	// phpcs:ignore: WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$terms = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT t.*, tt.* FROM {$wpdb->terms} AS t " .
			"INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id " .
			'WHERE tt.taxonomy IN (%s) ' .
			'ORDER BY t.name ASC',
			$taxonomy
		)
	);

	// Delete each one in turn - if we get any.
	if ( ! empty( $terms ) ) {
		foreach ( $terms as $term ) {
			// phpcs:ignore: WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->delete( $wpdb->term_taxonomy, [ 'term_taxonomy_id' => $term->term_taxonomy_id ] );
			// phpcs:ignore: WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->delete( $wpdb->term_relationships, [ 'term_taxonomy_id' => $term->term_taxonomy_id ] );
			// phpcs:ignore: WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->delete( $wpdb->terms, [ 'term_id' => $term->term_id ] );
		}
	}

	// Delete the Taxonomy itself.
	// phpcs:ignore: WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$wpdb->delete( $wpdb->term_taxonomy, [ 'taxonomy' => $taxonomy ], [ '%s' ] );

	// Lastly, flush rules.
	flush_rewrite_rules();

}

/**
 * Deletes the core options.
 *
 * Make sure these match those declared in CommentPress_Core_Database.
 *
 * @since 4.0
 */
function commentpress_options_delete() {
	delete_option( 'commentpress_version' );
	delete_option( 'commentpress_options' );
}

/**
 * Deletes the multisite options.
 *
 * Make sure these match those declared in CommentPress_Multisite_Database.
 *
 * @since 4.0
 */
function commentpress_site_options_delete() {
	delete_site_option( 'cpmu_options' );
	delete_site_option( 'cpmu_version' );
	delete_site_option( 'commentpress_sites' );
}

// Restore all Sites to pre-CommentPress state.
commentpress_sites_restore();
