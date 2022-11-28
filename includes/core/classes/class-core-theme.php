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
		add_action( 'commentpress/core/deactivate', [ $this, 'plugin_deactivate' ], 30 );

		// Enable CommentPress themes in Multisite optional scenario.
		add_filter( 'network_allowed_themes', [ $this, 'allowed_themes' ] );

		// Enqueue common Javascripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'scripts_enqueue' ], 20 );

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
	 * Adds our Javascripts.
	 *
	 * Enqueue jQuery, jQuery UI and plugins.
	 *
	 * @since 3.4
	 */
	public function scripts_enqueue() {

		// Don't include in admin or wp-login.php.
		if ( is_admin() || ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' == $GLOBALS['pagenow'] ) ) {
			return;
		}

		// Default to minified scripts.
		$min = commentpress_minified();

		// Add our jQuery plugin and dependencies.
		wp_enqueue_script(
			'jquery_commentpress',
			plugins_url( 'includes/core/assets/js/jquery.commentpress' . $min . '.js', COMMENTPRESS_PLUGIN_FILE ),
			[ 'jquery', 'jquery-form', 'jquery-ui-core', 'jquery-ui-resizable', 'jquery-ui-tooltip' ],
			COMMENTPRESS_VERSION, // Version.
			false // In footer.
		);

		// Get vars.
		$vars = $this->get_javascript_vars();

		// Localise the WordPress way.
		wp_localize_script( 'jquery_commentpress', 'CommentpressSettings', $vars );

		// Add jQuery Scroll-To plugin.
		wp_enqueue_script(
			'jquery_scrollto',
			plugins_url( 'includes/core/assets/js/jquery.scrollTo.js', COMMENTPRESS_PLUGIN_FILE ),
			[ 'jquery_commentpress' ],
			COMMENTPRESS_VERSION, // Version.
			false // In footer.
		);

		// Optionally get text highlighter.
		$this->get_text_highlighter();

	}

	/**
	 * Enqueue our text highlighter script.
	 *
	 * @since 3.8
	 */
	public function get_text_highlighter() {

		// Only allow text highlighting on non-touch devices - but allow testing override.
		if ( ! $this->core->device->is_touch() || ( defined( 'COMMENTPRESS_TOUCH_SELECT' ) && COMMENTPRESS_TOUCH_SELECT ) ) {

			// Bail if not a commentable Page/Post.
			if ( ! $this->core->parser->is_commentable() ) {
				return;
			}

			// Default to minified scripts.
			$min = commentpress_minified();

			// Add jQuery wrapSelection plugin.
			wp_enqueue_script(
				'jquery_wrapselection',
				plugins_url( 'includes/core/assets/js/jquery.wrap-selection' . $min . '.js', COMMENTPRESS_PLUGIN_FILE ),
				[ 'jquery_commentpress' ],
				COMMENTPRESS_VERSION, // Version.
				false // In footer.
			);

			// Add jQuery highlighter plugin.
			wp_enqueue_script(
				'jquery_highlighter',
				plugins_url( 'includes/core/assets/js/jquery.highlighter' . $min . '.js', COMMENTPRESS_PLUGIN_FILE ),
				[ 'jquery_wrapselection' ],
				COMMENTPRESS_VERSION, // Version.
				false // In footer.
			);

			// Add jQuery text highlighter plugin.
			wp_enqueue_script(
				'jquery_texthighlighter',
				plugins_url( 'includes/core/assets/js/jquery.texthighlighter' . $min . '.js', COMMENTPRESS_PLUGIN_FILE ),
				[ 'jquery_highlighter' ],
				COMMENTPRESS_VERSION, // Version.
				false // In footer.
			);

			// Define popover for textblocks.
			$popover_textblock = '<span class="popover-holder"><div class="popover-holder-inner"><div class="popover-holder-caret"></div><div class="popover-holder-btn-left"><span class="popover-holder-btn-left-comment">' . esc_html__( 'Comment', 'commentpress-core' ) . '</span><span class="popover-holder-btn-left-quote">' . esc_html__( 'Quote &amp; Comment', 'commentpress-core' ) . '</span></div><div class="popover-holder-btn-right">&times;</div></div></span>';

			// Define popover for Comments.
			$popover_comment = '<span class="comment-popover-holder"><div class="popover-holder-inner"><div class="popover-holder-caret"></div><div class="popover-holder-btn-left"><span class="comment-popover-holder-btn-left-quote">' . esc_html__( 'Quote', 'commentpress-core' ) . '</span></div><div class="popover-holder-btn-right">&times;</div></div></span>';

			// Define localisation array.
			$texthighlighter_vars = [
				'popover_textblock' => $popover_textblock,
				'popover_comment' => $popover_comment,
			];

			// Create translations.
			$texthighlighter_translations = [
				'dialog_title' => esc_html__( 'Are you sure?', 'commentpress-core' ),
				'dialog_content' => esc_html__( 'You have not yet submitted your comment. Are you sure you want to discard it?', 'commentpress-core' ),
				'dialog_yes' => esc_html__( 'Discard', 'commentpress-core' ),
				'dialog_no' => esc_html__( 'Keep', 'commentpress-core' ),
				'backlink_text' => esc_html__( 'Back', 'commentpress-core' ),
			];

			// Add to vars.
			$texthighlighter_vars['localisation'] = $texthighlighter_translations;

			// Localise the WordPress way.
			wp_localize_script( 'jquery_texthighlighter', 'CommentpressTextSelectorSettings', $texthighlighter_vars );

		}

	}

	/**
	 * Get Javascript params for the plugin, context dependent.
	 *
	 * @since 3.4
	 *
	 * @return array $vars The Javascript setup params.
	 */
	public function get_javascript_vars() {

		// Init return.
		$vars = [];

		// Access Post.
		global $post;

		// If we don't have a Post - like on the 404 Page.
		if ( ! ( $post instanceof WP_Post ) ) {

			// Comments must be closed.
			$vars['cp_comments_open'] = 'n';

			// Set empty permalink.
			$vars['cp_permalink'] = '';

		} else {

			// Check for Post "comment_status".
			$vars['cp_comments_open'] = ( $post->comment_status == 'open' ) ? 'y' : 'n';

			// Set Post permalink.
			$vars['cp_permalink'] = get_permalink( $post->ID );

		}

		// Assume no admin bars.
		$vars['cp_wp_adminbar'] = 'n';
		$vars['cp_bp_adminbar'] = 'n';

		// Match WordPress 3.8+ admin bar.
		$vars['cp_wp_adminbar_height'] = '32';
		$vars['cp_wp_adminbar_expanded'] = '0';

		// Are we showing the WordPress admin bar?
		if ( is_admin_bar_showing() ) {

			// We have it.
			$vars['cp_wp_adminbar'] = 'y';

			// Admin bar expands in height below 782px viewport width.
			$vars['cp_wp_adminbar_expanded'] = '46';

		}

		// Are we logged in AND in a BuddyPress scenario?
		if ( is_user_logged_in() && $this->core->bp->is_buddypress() ) {

			// Regardless of version, settings can be made in bp-custom.php.
			if ( defined( 'BP_DISABLE_ADMIN_BAR' ) && BP_DISABLE_ADMIN_BAR ) {

				// We've killed both admin bars.
				$vars['cp_bp_adminbar'] = 'n';
				$vars['cp_wp_adminbar'] = 'n';

			}

			/*
			 * Check for BuddyPress versions prior to 1.6.
			 *
			 * BuddyPress 1.6 uses the WordPress admin bar instead of a custom one.
			 */
			if ( ! function_exists( 'bp_get_version' ) ) {

				// But, this can already be overridden in bp-custom.php.
				if ( defined( 'BP_USE_WP_ADMIN_BAR' ) && BP_USE_WP_ADMIN_BAR ) {

					// Not present.
					$vars['cp_bp_adminbar'] = 'n';
					$vars['cp_wp_adminbar'] = 'y';

				} else {

					// Let our javascript know.
					$vars['cp_bp_adminbar'] = 'y';

					// Recheck 'BP_DISABLE_ADMIN_BAR'.
					if ( defined( 'BP_DISABLE_ADMIN_BAR' ) && BP_DISABLE_ADMIN_BAR ) {

						// We've killed both admin bars.
						$vars['cp_bp_adminbar'] = 'n';
						$vars['cp_wp_adminbar'] = 'n';

					}

				}

			}

		}

		// Add rich text editor by default.
		$vars['cp_tinymce'] = 1;

		// Check if Users must be logged in to comment.
		if ( get_option( 'comment_registration' ) == '1' && ! is_user_logged_in() ) {

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Check CommentPress Core option.
		if (
			$this->core->db->setting_exists( 'cp_comment_editor' ) &&
			$this->core->db->setting_get( 'cp_comment_editor' ) != '1'
		) {

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// If on a public Group Blog and User isn't logged in.
		if ( $this->core->bp->is_groupblog() && ! is_user_logged_in() ) {

			// Don't add rich text editor, because only Members can comment.
			$vars['cp_tinymce'] = 0;

		}

		/**
		 * Filters the TinyMCE vars.
		 *
		 * Allow plugins to override TinyMCE.
		 *
		 * @since 3.4
		 *
		 * @param bool $cp_tinymce The default TinyMCE vars.
		 */
		$vars['cp_tinymce'] = apply_filters( 'cp_override_tinymce', $vars['cp_tinymce'] );

		// Add mobile var.
		$vars['cp_is_mobile'] = 0;

		// Is it a mobile?
		if ( $this->core->device->is_mobile() ) {

			// Is mobile.
			$vars['cp_is_mobile'] = 1;

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Add touch var.
		$vars['cp_is_touch'] = 0;

		// Is it a touch device?
		if ( $this->core->device->is_touch() ) {

			// Is touch.
			$vars['cp_is_touch'] = 1;

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Add touch testing var.
		$vars['cp_touch_testing'] = 0;

		// Have we set our testing constant?
		if ( defined( 'COMMENTPRESS_TOUCH_SELECT' ) && COMMENTPRESS_TOUCH_SELECT ) {

			// Support touch device testing.
			$vars['cp_touch_testing'] = 1;

		}

		// Add tablet var.
		$vars['cp_is_tablet'] = 0;

		// Is it a touch device?
		if ( $this->core->device->is_tablet() ) {

			// Is touch.
			$vars['cp_is_tablet'] = 1;

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Add rich text editor behaviour.
		$vars['cp_promote_reading'] = 1;

		// Check option.
		if (
			$this->core->db->setting_exists( 'cp_promote_reading' ) &&
			$this->core->db->setting_get( 'cp_promote_reading' ) != '1'
		) {

			// Promote commenting.
			$vars['cp_promote_reading'] = 0;

		}

		// Add Special Page var.
		$vars['cp_special_page'] = ( $this->core->pages_legacy->is_special_page() ) ? '1' : '0';

		// Are we in a BuddyPress scenario?
		if ( $this->core->bp->is_buddypress() ) {

			// Is it a component homepage?
			if ( $this->core->bp->is_buddypress_special_page() ) {

				// Treat them the way we do ours.
				$vars['cp_special_page'] = '1';

			}

		}

		// Get path.
		$url_info = wp_parse_url( get_option( 'siteurl' ) );

		// Add path for cookies.
		$vars['cp_cookie_path'] = '/';
		if ( ! empty( $url_info['path'] ) ) {
			$vars['cp_cookie_path'] = trailingslashit( $url_info['path'] );
		}

		// Add Page.
		global $page;
		$vars['cp_multipage_page'] = ( ! empty( $page ) ) ? $page : 0;

		// Are Chapters Pages?
		$vars['cp_toc_chapter_is_page'] = $this->core->db->setting_get( 'cp_toc_chapter_is_page' );

		// Are Sub-pages shown?
		$vars['cp_show_subpages'] = $this->core->db->setting_get( 'cp_show_subpages' );

		// Set default sidebar.
		$vars['cp_default_sidebar'] = $this->core->theme->sidebar->default_get();

		// Set scroll speed.
		$vars['cp_js_scroll_speed'] = $this->core->db->setting_get( 'cp_js_scroll_speed' );

		// Set min Page width.
		$vars['cp_min_page_width'] = $this->core->db->setting_get( 'cp_min_page_width' );

		// Default to showing textblock meta.
		$vars['cp_textblock_meta'] = 1;

		// Check option.
		if (
			$this->core->db->setting_exists( 'cp_textblock_meta' ) &&
			$this->core->db->setting_get( 'cp_textblock_meta' ) == 'n'
		) {

			// Only show textblock meta on rollover.
			$vars['cp_textblock_meta'] = 0;

		}

		// Default to Page navigation enabled.
		$vars['cp_page_nav_enabled'] = 1;

		// Check option.
		if (
			$this->core->db->setting_exists( 'cp_page_nav_enabled' ) &&
			$this->core->db->setting_get( 'cp_page_nav_enabled' ) == 'n'
		) {

			// Disable Page navigation.
			$vars['cp_page_nav_enabled'] = 0;

		}

		// Default to parsing content and Comments.
		$vars['cp_do_not_parse'] = 0;

		// Check option.
		if (
			$this->core->db->setting_exists( 'cp_do_not_parse' ) &&
			$this->core->db->setting_get( 'cp_do_not_parse' ) == 'y'
		) {

			// Do not parse.
			$vars['cp_do_not_parse'] = 1;

		}

		/**
		 * Filters the Javascript vars.
		 *
		 * @since 3.4
		 *
		 * @param array $vars The default Javascript vars.
		 */
		return apply_filters( 'commentpress_get_javascript_vars', $vars );

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
