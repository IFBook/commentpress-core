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
	 * @access private
	 * @var string $classes_path Relative path to the classes directory.
	 */
	private $classes_path = 'includes/core/classes/';

	/**
	 * Metabox template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $metabox_path Relative path to the Metabox directory.
	 */
	private $metabox_path = 'includes/core/assets/templates/wordpress/metaboxes/';

	/**
	 * "Featured Images" settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var str $key_featured_images The settings key for the "Featured Images" setting.
	 */
	private $key_featured_images = 'cp_featured_images';

	/**
	 * "Textblock meta" settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var str $key_excerpt_length The settings key for the "Textblock meta" setting.
	 */
	private $key_textblock_meta = 'cp_textblock_meta';

	/**
	 * "Excerpt length" settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var str $key_excerpt_length The settings key for the "Excerpt length" setting.
	 */
	private $key_excerpt_length = 'cp_excerpt_length';

	/**
	 * "Scroll speed" settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var str $key_scroll_speed The settings key for the "Scroll speed" setting.
	 */
	private $key_scroll_speed = 'cp_js_scroll_speed';

	/**
	 * Default header background colour (hex, same as in theme stylesheet).
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $header_bg_color The default header background colour.
	 */
	public $header_bg_color = '2c2622';

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
		if ( isset( $done ) && true === $done ) {
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

		// Separate callbacks into descriptive methods.
		$this->register_hooks_activation();
		$this->register_hooks_settings();

		// Enqueue common Javascripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'scripts_enqueue' ], 20 );

	}

	/**
	 * Registers activation/deactivation hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_activation() {

		// Acts when this plugin is activated.
		add_action( 'commentpress/core/activate', [ $this, 'plugin_activate' ], 30 );

		// Acts when this plugin is deactivated.
		add_action( 'commentpress/core/deactivate', [ $this, 'plugin_deactivate' ], 30 );

	}

	/**
	 * Registers "Site Settings" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_settings() {

		// Add our settings to default settings.
		add_filter( 'commentpress/core/settings/defaults', [ $this, 'settings_get_defaults' ] );

		// Add our metaboxes to the Site Settings screen.
		add_filter( 'commentpress/core/settings/site/metaboxes/after', [ $this, 'settings_meta_boxes_append' ], 50 );

		// Save data from Site Settings form submissions.
		add_action( 'commentpress/core/settings/site/save/before', [ $this, 'settings_meta_box_save' ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Appends our settings to the default core settings.
	 *
	 * @since 4.0
	 *
	 * @param array $settings The existing default core settings.
	 * @return array $settings The modified default core settings.
	 */
	public function settings_get_defaults( $settings ) {

		// Add our defaults.
		$settings[ $this->key_featured_images ] = 'n';
		$settings[ $this->key_textblock_meta ]  = 'y';
		$settings[ $this->key_excerpt_length ]  = 55;
		$settings[ $this->key_scroll_speed ]    = 800;

		// --<
		return $settings;

	}

	/**
	 * Appends our metaboxes to the Site Settings screen.
	 *
	 * @since 4.0
	 *
	 * @param string $screen_id The Site Settings Screen ID.
	 */
	public function settings_meta_boxes_append( $screen_id ) {

		// Create "Theme Customisation" metabox.
		add_meta_box(
			'commentpress_core_theme',
			__( 'Theme Customization', 'commentpress-core' ),
			[ $this, 'settings_meta_box_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

	}

	/**
	 * Renders the "Theme Customisation" metabox.
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_render() {

		// Get settings.
		$featured_images = $this->setting_featured_images_get();
		$textblock_meta  = $this->setting_textblock_meta_get();
		$excerpt_length  = $this->setting_excerpt_length_get();
		$scroll_speed    = $this->setting_scroll_speed_get();

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-site-theme.php';

	}

	/**
	 * Saves the data from the Network Settings "BuddyPress Groupblog Settings" metabox.
	 *
	 * Adds the data to the settings array. The settings are actually saved later.
	 *
	 * @see CommentPress_Core_Settings_Site::form_submitted()
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_save() {

		// Get "Featured Images" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$featured_images = isset( $_POST[ $this->key_featured_images ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_featured_images ] ) ) : '0';

		// Set the setting.
		$this->setting_featured_images_set( $featured_images );

		// Get "Textblock meta" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$textblock_meta = isset( $_POST[ $this->key_textblock_meta ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_textblock_meta ] ) ) : '0';

		// Set the setting.
		$this->setting_textblock_meta_set( $textblock_meta );

		// Get "Excerpt length" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$excerpt_length = isset( $_POST[ $this->key_excerpt_length ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_excerpt_length ] ) ) : '0';

		// Set the setting.
		$this->setting_excerpt_length_set( $excerpt_length );

		// Get "Scroll speed" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$scroll_speed = isset( $_POST[ $this->key_scroll_speed ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_scroll_speed ] ) ) : '0';

		// Set the setting.
		$this->setting_scroll_speed_set( $scroll_speed );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the "Featured Images" setting.
	 *
	 * @since 4.0
	 *
	 * @return str $featured_images The setting if found, default otherwise.
	 */
	public function setting_featured_images_get() {

		// Get the setting.
		$featured_images = $this->core->db->setting_get( $this->key_featured_images );

		// Return setting or default if empty.
		return ! empty( $featured_images ) ? $featured_images : 'n';

	}

	/**
	 * Sets the "Featured Images" setting.
	 *
	 * @since 4.0
	 *
	 * @param str $featured_images The setting value.
	 */
	public function setting_featured_images_set( $featured_images ) {

		// Set the setting.
		$this->core->db->setting_set( $this->key_featured_images, $featured_images );

	}

	/**
	 * Gets the "Textblock meta" setting.
	 *
	 * @since 4.0
	 *
	 * @return str $textblock_meta The setting if found, default otherwise.
	 */
	public function setting_textblock_meta_get() {

		// Get the setting.
		$textblock_meta = $this->core->db->setting_get( $this->key_textblock_meta );

		// Return setting or default if empty.
		return ! empty( $textblock_meta ) ? $textblock_meta : 'y';

	}

	/**
	 * Sets the "Textblock meta" setting.
	 *
	 * @since 4.0
	 *
	 * @param str $textblock_meta The setting value.
	 */
	public function setting_textblock_meta_set( $textblock_meta ) {

		// Set the setting.
		$this->core->db->setting_set( $this->key_textblock_meta, $textblock_meta );

	}

	/**
	 * Gets the "Excerpt length" setting.
	 *
	 * @since 4.0
	 *
	 * @return int|bool $excerpt_length The setting if found, false otherwise.
	 */
	public function setting_excerpt_length_get() {

		// Get the setting.
		$excerpt_length = $this->core->db->setting_get( $this->key_excerpt_length );

		// Return setting or boolean if empty.
		return ! empty( $excerpt_length ) ? $excerpt_length : false;

	}

	/**
	 * Sets the "Excerpt length" setting.
	 *
	 * @since 4.0
	 *
	 * @param int $excerpt_length The setting value.
	 */
	public function setting_excerpt_length_set( $excerpt_length ) {

		// Set the setting.
		$this->core->db->setting_set( $this->key_excerpt_length, $excerpt_length );

	}

	/**
	 * Gets the "Scroll speed" setting.
	 *
	 * @since 4.0
	 *
	 * @return int|bool $scroll_speed The setting if found, false otherwise.
	 */
	public function setting_scroll_speed_get() {

		// Get the setting.
		$scroll_speed = $this->core->db->setting_get( $this->key_scroll_speed );

		// Return setting or boolean if empty.
		return ! empty( $scroll_speed ) ? $scroll_speed : false;

	}

	/**
	 * Sets the "Scroll speed" setting.
	 *
	 * @since 4.0
	 *
	 * @param int $scroll_speed The setting value.
	 */
	public function setting_scroll_speed_set( $scroll_speed ) {

		// Set the setting.
		$this->core->db->setting_set( $this->key_scroll_speed, $scroll_speed );

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
		 * CommentPress Core theme for a particular Group Blog or type of Group Blog.
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
		$theme = apply_filters( 'commentpress_get_groupblog_theme', $this->core->bp->groupblog_theme_get() );

		// Did we get a CommentPress Core one?
		if ( false !== $theme ) {

			/*
			 * We're in a Group Blog context.
			 *
			 * BuddyPress Groupblog will already have set the theme because we're
			 * adding our "wpmu_new_blog" action after it.
			 */
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
			'sidebar-1'           => [],
			'sidebar-2'           => [],
			'sidebar-3'           => [],
			'array_version'       => 3,
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

		// Add FitVids script.
		wp_enqueue_script(
			'jquery_fitvids',
			plugins_url( 'includes/core/assets/js/jquery.fitvids.js', COMMENTPRESS_PLUGIN_FILE ),
			[ 'jquery' ],
			COMMENTPRESS_VERSION, // Version.
			true
		);

		// Add our jQuery plugin and dependencies.
		wp_enqueue_script(
			'jquery_commentpress',
			plugins_url( 'includes/core/assets/js/jquery.commentpress' . $min . '.js', COMMENTPRESS_PLUGIN_FILE ),
			[ 'jquery', 'jquery-form', 'jquery-ui-core', 'jquery-ui-resizable', 'jquery-ui-tooltip', 'jquery_fitvids' ],
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
				'popover_comment'   => $popover_comment,
			];

			// Create translations.
			$texthighlighter_translations = [
				'dialog_title'   => esc_html__( 'Are you sure?', 'commentpress-core' ),
				'dialog_content' => esc_html__( 'You have not yet submitted your comment. Are you sure you want to discard it?', 'commentpress-core' ),
				'dialog_yes'     => esc_html__( 'Discard', 'commentpress-core' ),
				'dialog_no'      => esc_html__( 'Keep', 'commentpress-core' ),
				'backlink_text'  => esc_html__( 'Back', 'commentpress-core' ),
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
			$vars['cp_comments_open'] = ( 'open' === $post->comment_status ) ? 'y' : 'n';

			// Set Post permalink.
			$vars['cp_permalink'] = get_permalink( $post->ID );

		}

		// Assume no admin bars.
		$vars['cp_wp_adminbar'] = 'n';
		$vars['cp_bp_adminbar'] = 'n';

		// Match WordPress 3.8+ admin bar.
		$vars['cp_wp_adminbar_height']   = '32';
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

		// Set scroll speed.
		$vars['cp_js_scroll_speed'] = $this->setting_scroll_speed_get();

		// Show textblock meta unless setting is set to on rollover.
		$vars['cp_textblock_meta'] = 1;
		if ( $this->setting_textblock_meta_get() == 'n' ) {
			$vars['cp_textblock_meta'] = 0;
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
		return $this->header_bg_color;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieves the commentable Post Types.
	 *
	 * @since 3.4
	 *
	 * @return array $commentable_post_types The array of commentable Post Types.
	 */
	public function get_commentable_cpts() {

		// Init.
		$commentable_post_types = [];

		// TODO: Exactly how do we support Post Types?
		$args = [
			//'public' => true,
			'_builtin' => false,
		];

		$output   = 'names'; // Can be "names" or "objects" - "names" is the default.
		$operator = 'and'; // Can be "and" or "or".

		// Get Post Types.
		$post_types = get_post_types( $args, $output, $operator );

		// Did we get any?
		if ( empty( $post_types ) ) {
			return $commentable_post_types;
		}

		// Loop.
		foreach ( $post_types as $post_type ) {

			// Decision goes here.

			// Add name to array - "is_singular" expects this.
			$commentable_post_types[] = $post_type;

		}

		// --<
		return $commentable_post_types;

	}

}
