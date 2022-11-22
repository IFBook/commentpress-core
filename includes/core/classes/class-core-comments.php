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

		// Modify Comment posting.
		add_action( 'comment_post', [ $this, 'save_comment' ], 10, 2 );

		// Amend the behaviour of Featured Comments plugin.
		add_action( 'plugins_loaded', [ $this, 'featured_comments_override' ], 1000 );

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
		$result = $this->core->db->save_comment_signature( $comment_id );

		// Store our Comment Selection.
		$result = $this->core->db->save_comment_selection( $comment_id );

		// In multipage situations, store our comment's Page.
		$result = $this->core->db->save_comment_page( $comment_id );

		// Has the Comment been marked as spam?
		if ( $comment_status === 'spam' ) {

			// TODO: Check for AJAX request.

			// Yes - let the commenter know without throwing an AJAX error.
			wp_die( __( 'This comment has been marked as spam. Please contact a site administrator.', 'commentpress-core' ) );

		}

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
