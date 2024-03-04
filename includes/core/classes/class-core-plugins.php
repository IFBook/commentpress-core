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

		// Check plugin pages when checking "is commentable".
		add_filter( 'cp_is_commentable', [ $this, 'is_commentable' ] );

		// Check plugin pages when parsing content.
		add_filter( 'commentpress/core/parser/the_content/skip', [ $this, 'is_theme_my_login_page' ] );
		add_filter( 'commentpress/core/parser/the_content/skip', [ $this, 'is_subscribe_to_comments_reloaded_page' ] );

		// Removes any "Theme My Login" Pages.
		add_filter( 'commentpress/core/nav/page_list', [ $this, 'filter_theme_my_login_page' ] );

		// Show the "Subscribe to Comments Reloaded" Subscription Checkbox.
		add_action( 'commentpress_comment_form_pre_submit', [ $this, 'subscribe_to_comments_reloaded_show' ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Filters "is commentable" to check if a Page is a plugin Page.
	 *
	 * @since 3.4
	 *
	 * @param bool $is_commentable True if commentable, false otherwise.
	 * @return bool $is_commentable True if commentable, false otherwise.
	 */
	public function is_commentable( $is_commentable ) {

		// Theme My Login Page is not.
		if ( $this->core->plugins->is_theme_my_login_page() ) {
			return false;
		}

		// Subscribe to Comments Reloaded Page is not.
		if ( $this->core->plugins->is_subscribe_to_comments_reloaded_page() ) {
			return false;
		}

		// --<
		return $is_commentable;

	}

	// -------------------------------------------------------------------------

	/**
	 * Checks for presence of "Theme My Login" Shortcode in the current Post.
	 *
	 * @since 3.4
	 *
	 * @return bool $success True if "Theme My Login" Page, false otherwise.
	 */
	public function is_theme_my_login_page() {

		// Access Post.
		global $post;

		// Bail if not a Post.
		if ( ! ( $post instanceof WP_Post ) ) {
			return false;
		}

		// It is if it has the "Theme My Login" Shortcode.
		if ( has_shortcode( $post->post_content, 'theme-my-login' ) ) {
			return true;
		}

		// --<
		return false;

	}

	/**
	 * Removes any "Theme My Login" Pages from the Navigation Page List.
	 *
	 * @since 3.0
	 *
	 * @param array $pages The existing array of Page objects.
	 * @return array $clean The modified array of Page objects.
	 */
	public function filter_theme_my_login_page( $pages ) {

		// Bail if Theme My Login is not present.
		if ( ! defined( 'TML_ABSPATH' ) ) {
			return $pages;
		}

		// Bail if there are none.
		if ( empty( $pages ) ) {
			return $pages;
		}

		// Init return.
		$clean = [];

		// Rebuild array without Pages that have the "Theme My Login" Shortcode.
		foreach ( $pages as $page ) {
			if ( ! has_shortcode( $page->post_content, 'theme-my-login' ) ) {
				$clean[] = $page;
			}
		}

		// --<
		return $clean;

	}

	// -------------------------------------------------------------------------

	/**
	 * Checks for the "Subscribe to Comments Reloaded" Subscriptions Page.
	 *
	 * @since 3.5.9
	 *
	 * @return bool True if "Subscribe to Comments Reloaded" Subscriptions Page, false otherwise.
	 */
	public function is_subscribe_to_comments_reloaded_page() {

		// Access Page.
		global $post;

		// Bail if not a Post.
		if ( ! ( $post instanceof WP_Post ) ) {
			return false;
		}

		// It is if it's the unique "Subscribe to Comments Reloaded" Post.
		if ( '-999' == $post->ID ) {
			return true;
		}

		// --<
		return false;

	}

	/**
	 * Renders the "Subscribe to Comments Reloaded" Subscription markup.
	 *
	 * @since 4.0
	 */
	public function subscribe_to_comments_reloaded_show() {

		// Access plugin.
		global $wp_subscribe_reloaded;

		// Bail if not present.
		if ( ! isset( $wp_subscribe_reloaded ) ) {
			return;
		}

		// Show the Subscription markup.
		echo '<div class="stcr-wrapper">' .
			$wp_subscribe_reloaded->stcr->subscribe_reloaded_show() .
		'</div>';

	}

}
