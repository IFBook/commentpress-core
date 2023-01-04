<?php
/**
 * CommentPress Core BuddyPress class.
 *
 * Handles compatibility with BuddyPress.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core BuddyPress Class.
 *
 * This class provides compatibility with BuddyPress.
 *
 * @since 4.0
 */
class CommentPress_Core_BuddyPress {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * BuddyPress present flag.
	 *
	 * @since 3.3
	 * @access public
	 * @var bool $buddypress True if BuddyPress present, false otherwise.
	 */
	public $buddypress = false;

	/**
	 * BuddyPress Groupblog flag.
	 *
	 * @since 3.3
	 * @access public
	 * @var bool $bp_groupblog True if BuddyPress Groupblog present, false otherwise.
	 */
	public $bp_groupblog = false;

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

		// Enable BuddyPress functionality.
		add_action( 'bp_include', [ $this, 'buddypress_present' ] );

		// Add BuddyPress functionality - really late, so Group object is set up.
		add_action( 'bp_setup_globals', [ $this, 'buddypress_groupblog_present' ], 100 );

		// Add our class(es) to the body classes.
		add_filter( 'commentpress/core/theme/body/classes', [ $this, 'groupblog_body_class_add' ] );

		// Actions to perform on BuddyPress Docs load.
		add_action( 'bp_docs_load', [ $this, 'bp_docs_loaded' ], 20 );

		// Override BuddyPress Docs Comment template.
		add_filter( 'bp_docs_comment_template_path', [ $this, 'bp_docs_comment_tempate' ], 20, 2 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Sets a property when BuddyPress is active.
	 *
	 * @since 3.4
	 */
	public function buddypress_present() {
		$this->buddypress = true;
	}

	/**
	 * Getter method for checking if BuddyPress is active.
	 *
	 * @since 3.4
	 *
	 * @return bool $buddypress True when BuddyPress active, false otherwise.
	 */
	public function is_buddypress() {
		return $this->buddypress;
	}

	/**
	 * Checks if the current Page is a BuddyPress "Special Page".
	 *
	 * Example Page is a Component Homepage.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_bp True if current Page is a BuddyPress Page, false otherwise.
	 */
	public function is_buddypress_special_page() {

		// Kick out if not BuddyPress.
		if ( ! $this->is_buddypress() ) {
			return false;
		}

		// We want the opposite of what is defined as a "non-BuddyPress Page".
		$is_bp = ! bp_is_blog_page();

		/**
		 * Filters the BuddyPress "Special Page" result.
		 *
		 * This is not necessary because BuddyPress has its own filter.
		 *
		 * @since 3.4
		 *
		 * @param bool $is_bp The default BuddyPress "Special Page" result.
		 */
		return apply_filters_deprecated( 'cp_is_buddypress_special_page', [ $is_bp ], 'bp_is_blog_page' );

	}

	// -------------------------------------------------------------------------

	/**
	 * Sets a property when the current Site is a BuddyPress Groupblog.
	 *
	 * @since 3.4
	 */
	public function buddypress_groupblog_present() {

		// Bail if BuddyPress Groupblog is not present.
		if ( ! defined( 'BP_GROUPBLOG_VERSION' ) ) {
			return;
		}

		// Check if this Blog is a Group Blog.
		$group_id = get_groupblog_group_id( get_current_blog_id() );
		if ( empty( $group_id ) || ! is_numeric( $group_id ) ) {
			return;
		}

		// BuddyPress Groupblog is present.
		$this->bp_groupblog = true;

	}

	/**
	 * Getter method for checking if the current Blog a Group Blog.
	 *
	 * @since 3.4
	 *
	 * @return bool $bp_groupblog True when current Blog is a Group Blog, false otherwise.
	 */
	public function is_groupblog() {
		return $this->bp_groupblog;
	}

	/**
	 * Checks if a Group Blog theme has been set.
	 *
	 * @since 3.4
	 *
	 * @return array|bool $theme An array describing the theme, or false otherwise.
	 */
	public function groupblog_theme_get() {

		// Bail if not GroupBlog context.
		if ( ! $this->is_groupblog() ) {
			return false;
		}

		// Bail if Groups Component is not active.
		if ( ! bp_is_active( 'groups' ) ) {
			return false;
		}

		// Bail if the current page is not part of the Groups component.
		if ( ! function_exists( 'bp_is_groups_component' ) || ! bp_is_groups_component() ) {
			return false;
		}

		// Try to get BuddyPress Groupblog options.
		$options = get_site_option( 'bp_groupblog_blog_defaults_options' );
		if ( empty( $options['theme'] ) ) {
			return false;
		}

		// We have a Group Blog theme set.
		list( $stylesheet, $template ) = explode( '|', $options['theme'] );

		// Get the registered theme.
		$theme = wp_get_theme( $stylesheet );

		// Bail if it's not a CommentPress Core theme.
		if ( ! in_array( 'commentpress', (array) $theme->get( 'Tags' ) ) ) {
			return false;
		}

		// We're good.
		return [ $stylesheet, $template ];

	}

	/**
	 * Adds "Group Blog" class to the body classes array.
	 *
	 * @since 4.0
	 *
	 * @param array $classes The existing body classes array.
	 * @return array $classes The modified body classes array.
	 */
	public function groupblog_body_class_add( $classes ) {

		// Set default class unless it's a Group Blog.
		$is_groupblog_class = 'not-groupblog';
		if ( $this->is_groupblog() ) {
			$is_groupblog_class = 'is-groupblog';
		}

		// Add to array.
		$classes[] = $is_groupblog_class;

		// --<
		return $classes;

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds callback to remove the Comment Reply script that BuddyPress Docs loads.
	 *
	 * @since 3.5.9
	 */
	public function bp_docs_loaded() {

		// Dequeue offending script (after BuddyPress Docs runs its enqueuing).
		add_action( 'wp_enqueue_scripts', [ $this, 'bp_docs_dequeue_scripts' ], 20 );

	}

	/**
	 * Removes the Comment Reply script that BuddyPress Docs loads.
	 *
	 * @since 3.5.9
	 */
	public function bp_docs_dequeue_scripts() {

		// Dequeue offending script.
		wp_dequeue_script( 'comment-reply' );

	}

	/**
	 * Override the Comments Template for BuddyPress Docs.
	 *
	 * @since 3.4
	 *
	 * @param str $path The existing path to the template.
	 * @param str $original_path The original path to the template.
	 * @return str $path The modified path to the template.
	 */
	public function bp_docs_comment_tempate( $path, $original_path ) {

		// If on BuddyPress root Site.
		if ( bp_is_root_blog() ) {

			// Override default link name.
			return $original_path;

		}

		// Pass through.
		return $path;

	}

}
