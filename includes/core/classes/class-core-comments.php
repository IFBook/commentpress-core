<?php
/**
 * CommentPress Core Comments class.
 *
 * Handles functionality related to Comments in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Comments Class.
 *
 * This class provides functionality related to Comments in CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Comments {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * Metabox nonce name.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $nonce_name The name of the metabox nonce element.
	 */
	private $nonce_name = 'commentpress_nonce';

	/**
	 * Metabox nonce value.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $nonce_value The name of the metabox nonce value.
	 */
	private $nonce_value = 'commentpress_comments';

	/**
	 * Constructor.
	 *
	 * @since 4.0
	 *
	 * @param object $core Reference to the core plugin object.
	 */
	public function __construct( $core ) {

		// Store reference to core plugin object.
		$this->core = $core;

		// Init when this plugin is fully loaded.
		add_action( 'commentpress/core/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this object.
	 *
	 * @since 4.0
	 */
	public function initialise() {

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

		// Act when this plugin is activated.
		add_action( 'commentpress/core/activated', [ $this, 'plugin_activated' ], 20 );

		// Act when this plugin is deactivated.
		add_action( 'commentpress/core/deactivated', [ $this, 'plugin_deactivated' ], 30 );

		// Modify Comment posting.
		add_action( 'comment_post', [ $this, 'save_comment' ], 10, 2 );

		// Amend the behaviour of Featured Comments plugin.
		add_action( 'plugins_loaded', [ $this, 'featured_comments_override' ], 1000 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Runs when core is activated.
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_activated( $network_wide ) {

		// Bail if plugin is network activated.
		if ( $network_wide ) {
			return;
		}

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'network_wide' => $network_wide ? 'y' : 'n',
			//'backtrace' => $trace,
		], true ) );
		*/

		// Install the database schema.
		$this->schema_install();

	}

	/**
	 * Runs when core is deactivated.
	 *
	 * NOTE: The database schema is only restored in "uninstall.php" when this
	 * plugin is deleted.
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_deactivated( $network_wide ) {

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'network_wide' => $network_wide ? 'y' : 'n',
			//'backtrace' => $trace,
		], true ) );
		*/

		// Bail if plugin is network activated.
		if ( $network_wide ) {
			return;
		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Installs the WordPress database schema.
	 *
	 * @since 4.0
	 *
	 * @return bool $result True if successful, false otherwise.
	 */
	public function schema_install() {

		// Database object.
		global $wpdb;

		// Include WordPress install helper script.
		require_once ABSPATH . 'wp-admin/install-helper.php';

		// Add the column, if not already there.
		$result = maybe_add_column(
			$wpdb->comments,
			'comment_signature',
			"ALTER TABLE `$wpdb->comments` ADD `comment_signature` VARCHAR(255) NULL;"
		);

		// --<
		return $result;
	}

	// -------------------------------------------------------------------------

	/**
	 * Stores our additional param - the Text Signature.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @param int $comment_id The numeric ID of the Comment.
	 * @param str $comment_status The status of the Comment.
	 */
	public function save_comment( $comment_id, $comment_status ) {

		// Store our Comment Text Signature.
		$result = $this->save_comment_signature( $comment_id );

		// Store our Comment Selection.
		$result = $this->save_comment_selection( $comment_id );

		// In multipage situations, store our comment's Page.
		$result = $this->save_comment_page( $comment_id );

		// Has the Comment been marked as spam?
		if ( $comment_status === 'spam' ) {

			// TODO: Check for AJAX request.

			// Yes - let the commenter know without throwing an AJAX error.
			wp_die( __( 'This comment has been marked as spam. Please contact a site administrator.', 'commentpress-core' ) );

		}

	}

	/**
	 * When a Comment is saved, this also saves the Text Signature.
	 *
	 * @since 3.0
	 *
	 * @param int $comment_id The numeric ID of the Comment.
	 * @return boolean $result True if successful, false otherwise.
	 */
	public function save_comment_signature( $comment_id ) {

		// Database object.
		global $wpdb;

		// Get Text Signature.
		$text_signature = isset( $_POST['text_signature'] ) ? sanitize_text_field( wp_unslash( $_POST['text_signature'] ) ) : '';

		// Did we get one?
		if ( ! empty( $text_signature ) ) {

			// Escape it.
			$text_signature = esc_sql( $text_signature );

			// Store Comment Text Signature.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$result = $wpdb->query(
				$wpdb->prepare(
					"UPDATE $wpdb->comments SET comment_signature = %s WHERE comment_ID = %d",
					$text_signature,
					$comment_id
				)
			);

		} else {

			// Set result to true - why not, eh?
			$result = true;

		}

		// --<
		return $result;

	}

	/**
	 * When a Comment is saved, this also saves the Page it was submitted on.
	 *
	 * This allows us to point to the correct Page of a multipage Post without
	 * parsing the content every time.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @param int $comment_ID The numeric ID of the Comment.
	 */
	private function save_comment_page( $comment_ID ) {

		// Get the Page number.
		$page_number = isset( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : false;

		// Bail if this is not a paged Post.
		if ( ! is_numeric( $page_number ) ) {
			return;
		}

		// Get Text Signature.
		$text_signature = isset( $_POST['text_signature'] ) ? sanitize_text_field( wp_unslash( $_POST['text_signature'] ) ) : '';

		// Is it a paragraph-level comment?
		if ( ! empty( $text_signature ) ) {

			// Set key.
			$key = '_cp_comment_page';

			// Add or update the data.
			if ( get_comment_meta( $comment_ID, $key, true ) != '' ) {
				update_comment_meta( $comment_ID, $key, $page_number );
			} else {
				add_comment_meta( $comment_ID, $key, $page_number, true );
			}

			// Okay, we're done.
			return;

		}

		/*
		// Top level Comments are always Page 1.
		$page_number = 1;
		*/

	}

	/**
	 * When a Comment is saved, this also saves the text selection.
	 *
	 * @since 3.9
	 *
	 * @param int $comment_id The numeric ID of the Comment.
	 * @return boolean $result True if successful, false otherwise.
	 */
	private function save_comment_selection( $comment_id ) {

		// Get text selection.
		$text_selection = isset( $_POST['text_selection'] ) ? sanitize_text_field( wp_unslash( $_POST['text_selection'] ) ) : '';

		// Bail if we didn't get one.
		if ( empty( $text_selection ) ) {
			return true;
		}

		// Sanity check: must have a comma.
		if ( false === stristr( $text_selection, ',' ) ) {
			return true;
		}

		// Make into an array.
		$selection = explode( ',', $text_selection );

		// Sanity check: must have only two elements.
		if ( count( $selection ) !== 2 ) {
			return true;
		}

		// Sanity check: both elements must be integers.
		$start_end = [];
		foreach ( $selection as $item ) {

			// Not an integer - bail entirely.
			if ( ! is_numeric( $item ) ) {
				return true;
			}

			// Cast as integer and add to array.
			$start_end[] = (int) $item;

		}

		// Okay, we're good to go.
		$selection_data = implode( ',', $start_end );

		// Set key.
		$key = '_cp_comment_selection';

		// Add or update the data.
		$current = get_comment_meta( $comment_id, $key, true );
		if ( ! empty( $current ) ) {
			update_comment_meta( $comment_id, $key, $selection_data );
		} else {
			add_comment_meta( $comment_id, $key, $selection_data, true );
		}

		// --<
		return true;

	}

	// -------------------------------------------------------------------------

	/**
	 * Override the Featured Comments behaviour.
	 *
	 * @since 3.4.8
	 * @since 4.0 Moved to this class.
	 */
	public function featured_comments_override() {

		// Is the plugin available?
		if ( ! function_exists( 'wp_featured_comments_load' ) ) {
			return;
		}

		// Get instance.
		$fc = wp_featured_comments_load();

		// Remove comment_text filter.
		remove_filter( 'comment_text', [ $fc, 'comment_text' ], 10 );

		// Get the plugin markup in the Comment edit section.
		add_filter( 'cp_comment_edit_link', [ $this, 'featured_comments_markup' ], 100, 2 );

	}

	/**
	 * Get the Featured Comments link markup.
	 *
	 * @since 3.4.8
	 * @since 4.0 Moved to this class.
	 *
	 * @param str $editlink The existing HTML link.
	 * @param array $comment The Comment data.
	 * @return str $editlink The modified HTML link.
	 */
	public function featured_comments_markup( $editlink, $comment ) {

		// Is the plugin available?
		if ( ! function_exists( 'wp_featured_comments_load' ) ) {
			return $editlink;
		}

		// Get instance.
		$fc = wp_featured_comments_load();

		// Get markup.
		return $editlink . $fc->comment_text( '' );

	}

}
