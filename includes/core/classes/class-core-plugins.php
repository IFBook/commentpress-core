<?php
/**
 * CommentPress Core Plugin Compatibility class.
 *
 * Handles compatibility with other plugins in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Plugin Compatibility Class.
 *
 * This class provides compatibility with other plugins in CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Plugins {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

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

	}

	// -------------------------------------------------------------------------

	/**
	 * Utility to check for presence of Theme My Login.
	 *
	 * @since 3.4
	 *
	 * @return bool $success True if Theme My Login Page, false otherwise.
	 */
	public function is_theme_my_login_page() {

		// Access Page.
		global $post;

		// Compat with Theme My Login.
		if (
			is_page() &&
			! $this->core->pages_legacy->is_special_page() &&
			$post->post_name == 'login' &&
			$post->post_content == '[theme-my-login]'
		) {

			// --<
			return true;

		}

		// --<
		return false;

	}

	// -------------------------------------------------------------------------

	/**
	 * Utility to check for presence of Members List.
	 *
	 * @since 3.4.7
	 *
	 * @return bool $success True if is Members List Page, false otherwise.
	 */
	public function is_members_list_page() {

		// Access Page.
		global $post;

		// Compat with Members List.
		if (
			is_page() &&
			! $this->core->pages_legacy->is_special_page() &&
			( strstr( $post->post_content, '[members-list' ) !== false )
		) {

			// --<
			return true;

		}

		// --<
		return false;

	}

	// -------------------------------------------------------------------------

	/**
	 * Utility to check for presence of Subscribe to Comments Reloaded.
	 *
	 * @since 3.5.9
	 *
	 * @return bool $success True if "Subscribe to Comments Reloaded" Page, false otherwise.
	 */
	public function is_subscribe_to_comments_reloaded_page() {

		// Access Page.
		global $post;

		// Compat with Subscribe to Comments Reloaded.
		if (
			is_page() &&
			! $this->core->pages_legacy->is_special_page() &&
			$post->ID == '9999999' &&
			$post->guid == get_bloginfo( 'url' ) . '/?page_id=9999999'
		) {

			// --<
			return true;

		}

		// --<
		return false;

	}

}
