<?php
/**
 * CommentPress Core Theme class.
 *
 * Handles theme functionality in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Theme Class.
 *
 * This class provides theme functionality in CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Theme {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * Sidebar object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $sidebar The Sidebar object.
	 */
	public $sidebar;

	/**
	 * Classes directory path.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $classes_path Relative path to the classes directory.
	 */
	public $classes_path = 'includes/core/classes/';

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

		// Only do this once.
		static $done;
		if ( isset( $done ) && $done === true ) {
			return;
		}

		// Bootstrap object.
		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

		/**
		 * Fires when the Theme object has loaded.
		 *
		 * Used internally to bootstrap objects.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/theme/loaded' );

		// We're done.
		$done = true;

	}

	/**
	 * Includes class files.
	 *
	 * @since 4.0
	 */
	public function include_files() {

		// Include theme class files.
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-theme-sidebar.php';

	}

	/**
	 * Sets up the objects in this class.
	 *
	 * @since 4.0
	 */
	public function setup_objects() {

		// Initialise theme objects.
		$this->sidebar = new CommentPress_Core_Theme_Sidebar( $this );

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

		// Acts when this plugin is activated.
		add_action( 'commentpress/core/activate', [ $this, 'plugin_activate' ], 30 );

		// Acts when this plugin is deactivated.
		add_action( 'commentpress/core/deactivate', [ $this, 'plugin_deactivate' ], 20 );

		// Enable CommentPress themes in Multisite optional scenario.
		add_filter( 'network_allowed_themes', [ $this, 'allowed_themes' ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Runs when core is activated.
	 *
	 * @since 3.0
	 * @since 4.0 Moved to this class.
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_activate( $network_wide = false ) {

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

		// Activate the default CommentPress theme.
		$this->activate();

		// Turn Comment paging option off.
		$this->comment_paging_cancel();

		// Override Widgets.
		$this->widgets_clear();

	}

	/**
	 * Runs when core is deactivated.
	 *
	 * @since 3.0
	 * @since 4.0 Moved to this class.
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_deactivate( $network_wide = false ) {

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'network_wide' => $network_wide ? 'y' : 'n',
			//'backtrace' => $trace,
		], true ) );
		*/

		/*
		// Bail if plugin is network activated.
		if ( $network_wide ) {
			return;
		}
		*/

		// Deactivate the default CommentPress theme.
		$this->deactivate();

		// Reset Comment paging option.
		$this->comment_paging_restore();

		// Restore Widgets.
		$this->widgets_restore();

	}

	// -------------------------------------------------------------------------

	/**
	 * Activates the default CommentPress theme.
	 *
	 * @since 3.0
	 * @since 4.0 Moved to this class.
	 */
	public function activate() {

		// Force WordPress to regenerate theme directories.
		search_theme_directories( true );

		/**
		 * Get Group Blog and set theme, if we have one.
		 *
		 * Allow filtering here because plugins may want to override a correctly-set
		 * CommentPress Core theme for a particular Group Blog (or type of Group Blog).
		 *
		 * If that is the case, then the filter callback must return boolean 'false'
		 * to prevent the theme being applied and also implement a filter on
		 * 'cp_forced_theme_slug' below that returns the desired theme slug.
		 *
		 * @since 3.4
		 * @since 4.0 Moved to this class.
		 *
		 * @param array The existing array containing the stylesheet and template paths.
		 */
		$theme = apply_filters( 'commentpress_get_groupblog_theme', $this->core->bp->get_groupblog_theme() );

		// Did we get a CommentPress Core one?
		if ( $theme !== false ) {

			// We're in a Group Blog context: BuddyPress Group Blog will already have set
			// the theme because we're adding our wpmu_new_blog action after it.

			// --<
			return;

		}

		/**
		 * Filters the default CommentPress theme.
		 *
		 * @since 3.4
		 *
		 * @param str The default slug of the theme.
		 */
		$target_theme = apply_filters( 'cp_forced_theme_slug', 'commentpress-flat' );

		// Get the theme we want.
		$theme = wp_get_theme( $target_theme );

		// If we get it.
		if ( $theme->exists() ) {

			/*
			// Ignore if not allowed.
			if ( is_multisite() && ! $theme->is_allowed() ) {
				return;
			}
			*/

			// Activate it.
			switch_theme(
				$theme->get_template(),
				$theme->get_stylesheet()
			);

		}

	}

	/**
	 * Deactivates the default CommentPress theme.
	 *
	 * @since 3.0
	 * @since 4.0 Moved to this class.
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function deactivate() {

		/**
		 * Get WordPress default theme, but allow overrides.
		 *
		 * @since 3.4
		 *
		 * @param str The slug of the default theme to switch to.
		 * @return str The modified slug of the default theme to switch to.
		 */
		$target_theme = apply_filters( 'cp_restore_theme_slug', WP_DEFAULT_THEME );

		// Get the theme we want.
		$theme = wp_get_theme( $target_theme );

		// If we get it.
		if ( $theme->exists() ) {

			/*
			// Ignore if not allowed.
			if ( is_multisite() && ! $theme->is_allowed() ) {
				return;
			}
			*/

			// Activate it.
			switch_theme(
				$theme->get_template(),
				$theme->get_stylesheet()
			);

		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Cancels Comment Paging because CommentPress Core does not work with Comment Paging.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class and renamed.
	 */
	public function comment_paging_cancel() {

		// Set backup option.
		$this->core->db->option_wp_backup( 'page_comments', '' );

	}

	/**
	 * Resets Comment Paging option when plugin is deactivated.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class and renamed.
	 */
	public function comment_paging_restore() {

		// Reset option.
		$this->core->db->option_wp_restore( 'page_comments' );

	}

	// -------------------------------------------------------------------------

	/**
	 * Clears Widgets for a fresh start.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class and renamed.
	 */
	public function widgets_clear() {

		/*
		 * Clear the Widget array.
		 *
		 * This array is based on the default WordPress array.
		 *
		 * @see wp_install_defaults()
		 */
		$this->core->db->option_wp_backup( 'sidebars_widgets', [
			'wp_inactive_widgets' => [],
			'sidebar-1' => [],
			'sidebar-2' => [],
			'sidebar-3' => [],
			'array_version' => 3,
		] );

	}

	/**
	 * Restores Widgets when plugin is deactivated.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class and renamed.
	 */
	public function widgets_restore() {

		// Reset option.
		$this->core->db->option_wp_restore( 'sidebars_widgets' );

	}

	// -------------------------------------------------------------------------

	/**
	 * Allow all CommentPress parent themes in Multisite optional scenario.
	 *
	 * @since 3.9.14
	 * @since 4.0 Moved to this class.
	 *
	 * @param array $retval The existing array of allowed themes.
	 * @return array $retval The modified array of allowed themes.
	 */
	public function allowed_themes( $retval ) {

		// Allow all parent themes.
		$retval['commentpress-flat'] = 1;
		$retval['commentpress-modern'] = 1;
		$retval['commentpress-theme'] = 1;

		// --<
		return $retval;

	}

	// -------------------------------------------------------------------------

	/**
	 * Get current header background colour.
	 *
	 * @since 3.0
	 *
	 * @return str $header_bg_color The hex value of the header.
	 */
	public function header_bg_color_get() {

		// TODO: Remove this method.

		// Do we have one set via the Customizer?
		$header_bg_color = get_theme_mod( 'commentpress_header_bg_color', false );

		// Return it if we do.
		if ( ! empty( $header_bg_color ) ) {
			return substr( $header_bg_color, 1 );
		}

		// Fallback to default.
		return $this->core->db->header_bg_color;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieves the commentable Post Types.
	 *
	 * @since 3.4
	 *
	 * @return array $types The array of commentable Post Types.
	 */
	public function get_commentable_cpts() {

		// Init.
		$types = [];

		// TODO: Exactly how do we support Post Types?
		$args = [
			//'public' => true,
			'_builtin' => false,
		];

		$output = 'names'; // Can be "names" or "objects" - "names" is the default.
		$operator = 'and'; // Can be "and" or "or".

		// Get Post Types.
		$post_types = get_post_types( $args, $output, $operator );

		// Did we get any?
		if ( empty( $post_types ) ) {
			return $types;
		}

		// Loop.
		foreach ( $post_types as $post_type ) {

			// Decision goes here.

			// Add name to array (is_singular expects this).
			$types[] = $post_type;

		}

		// --<
		return $types;

	}

}
