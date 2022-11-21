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
	 * BuddyPress Group Blog flag.
	 *
	 * @since 3.3
	 * @access public
	 * @var bool $bp_groupblog True if BuddyPress Group Blog present, false otherwise.
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
		add_action( 'bp_include', [ $this, 'buddypress_init' ] );

		// Add BuddyPress functionality - really late, so Group object is set up.
		add_action( 'bp_setup_globals', [ $this, 'buddypress_globals_loaded' ], 1000 );

		// Actions to perform on BuddyPress loaded.
		add_action( 'bp_loaded', [ $this, 'bp_docs_loaded' ], 20 );

		// Actions to perform on BuddyPress Docs load.
		add_action( 'bp_docs_load', [ $this, 'bp_docs_loaded' ], 20 );

		// Override BuddyPress Docs comment template.
		add_filter( 'bp_docs_comment_template_path', [ $this, 'bp_docs_comment_tempate' ], 20, 2 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Called when BuddyPress is active.
	 *
	 * @since 3.4
	 */
	public function buddypress_init() {

		// We've got BuddyPress installed.
		$this->buddypress = true;

	}

	/**
	 * Configure when BuddyPress is loaded.
	 *
	 * @since 3.4
	 */
	public function buddypress_globals_loaded() {

		// Test for a bp-groupblog function.
		if ( function_exists( 'get_groupblog_group_id' ) ) {

			// Check if this Blog is a Group Blog.
			$group_id = get_groupblog_group_id( get_current_blog_id() );
			if ( isset( $group_id ) && is_numeric( $group_id ) && $group_id > 0 ) {

				// Okay, we're properly configured.
				$this->bp_groupblog = true;

			}

		}

	}

	/**
	 * Is BuddyPress active?
	 *
	 * @since 3.4
	 *
	 * @return bool $buddypress True when BuddyPress active, false otherwise.
	 */
	public function is_buddypress() {

		// --<
		return $this->buddypress;

	}

	/**
	 * Is this Blog a BuddyPress Group Blog?
	 *
	 * @since 3.4
	 *
	 * @return bool $bp_groupblog True when current Blog is a BuddyPress Group Blog, false otherwise.
	 */
	public function is_groupblog() {

		// --<
		return $this->bp_groupblog;

	}

	/**
	 * Is this a BuddyPress "Special Page" - a component homepage?
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

		// Is it a BuddyPress Page?
		$is_bp = ! bp_is_blog_page();

		// Let's see.
		return apply_filters( 'cp_is_buddypress_special_page', $is_bp );

	}

	// -------------------------------------------------------------------------

	/**
	 * Is a BuddyPress Group Blog theme set?
	 *
	 * @since 3.4
	 *
	 * @return array $theme An array describing the theme.
	 */
	public function get_groupblog_theme() {

		// Kick out if not in a Group context.
		if ( ! function_exists( 'bp_is_groups_component' ) ) {
			return false;
		}
		if ( ! bp_is_groups_component() ) {
			return false;
		}

		// Get Group Blog options.
		$options = get_site_option( 'bp_groupblog_blog_defaults_options' );

		// Get theme setting.
		if ( ! empty( $options['theme'] ) ) {

			// We have a Group Blog theme set.

			// Split the options.
			list( $stylesheet, $template ) = explode( '|', $options['theme'] );

			// Get the registered theme.
			$theme = wp_get_theme( $stylesheet );

			// Test if it's a CommentPress Core theme.
			if ( in_array( 'commentpress', (array) $theme->get( 'Tags' ) ) ) {

				// --<
				return [ $stylesheet, $template ];

			}

		}

		// --<
		return false;

	}

	// -------------------------------------------------------------------------

	/**
	 * Override the comment reply script that BuddyPress Docs loads.
	 *
	 * @since 3.5.9
	 */
	public function bp_docs_loaded() {

		// Dequeue offending script (after BuddyPress Docs runs its enqueuing).
		add_action( 'wp_enqueue_scripts', [ $this, 'bp_docs_dequeue_scripts' ], 20 );

	}

	/**
	 * Override the comment reply script that BuddyPress Docs loads.
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
