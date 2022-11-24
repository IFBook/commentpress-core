<?php
/**
 * CommentPress Core Admin class.
 *
 * Handles Settings Page functionality in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Workflow Class.
 *
 * This class handles Settings Page functionality in CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Settings_Site {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * Parent Page reference.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $parent_page The reference to the parent Page.
	 */
	public $parent_page;

	/**
	 * Parent Page slug.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $parent_page_slug The slug of the parent Page.
	 */
	public $parent_page_slug = 'commentpress_admin';

	/**
	 * Settings Page reference.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $settings_page The reference to the Settings Page.
	 */
	public $settings_page;

	/**
	 * Settings Page slug.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $settings_page_slug The slug of the Settings Page.
	 */
	public $settings_page_slug = 'commentpress_settings';

	/**
	 * Settings Page Tab URLs.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $urls The array of Settings Page Tab URLs.
	 */
	public $urls = [];

	/**
	 * Page template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $page_path Relative path to the Page template directory.
	 */
	private $page_path = 'includes/core/assets/templates/wordpress/pages/';

	/**
	 * Metabox template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $metabox_path Relative path to the Metabox directory.
	 */
	private $metabox_path = 'includes/core/assets/templates/wordpress/metaboxes/';

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

		/*
		// Maybe show a warning if Settings need updating.
		add_action( 'admin_notices', [ $this, 'upgrade_warning' ] );
		*/

		// Add our item to the admin menu.
		add_action( 'admin_menu', [ $this, 'admin_menu' ] );

		// Add our meta boxes.
		add_action( 'add_meta_boxes', [ $this, 'meta_boxes_add' ], 11 );

		// Add link to Settings Page.
		add_filter( 'plugin_action_links', [ $this, 'action_links' ], 10, 2 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Utility to add a message to Admin Pages when upgrade required.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 */
	public function admin_upgrade_alert() {

		// Check User permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Show it.
		echo '<div id="message" class="error"><p>' .
			sprintf(
				/* translators: 1: The opening anchor tag, 2: The closing anchor tag. */
				__( 'CommentPress Core has been updated. Please visit the %1$sSettings Page%2$s.', 'commentpress-core' ),
				'<a href="options-general.php?page=commentpress_admin">',
				'</a>'
			) .
		'</p></div>';

	}

	/**
	 * Add our Admin Page(s) to the WordPress admin menu.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 */
	public function admin_menu() {

		// Check User permissions.
		if ( ! $this->page_capability() ) {
			return;
		}

		// If upgrade required.
		if ( $this->core->db->upgrade_required() ) {

			// Access globals.
			global $pagenow;

			// Show on Pages other than the CommentPress Core Admin Page.
			if (
				$pagenow == 'options-general.php' &&
				! empty( $_GET['page'] ) &&
				'commentpress_admin' == $_GET['page']
			) {

				// We're on our Admin Page.

			} else {

				// Show message.
				add_action( 'admin_notices', [ $this, 'admin_upgrade_alert' ] );

			}

		}

		// Add parent Page to Settings menu.
		$this->parent_page = add_options_page(
			__( 'CommentPress Core Settings', 'commentpress-core' ),
			__( 'CommentPress Core', 'commentpress-core' ),
			'manage_options', // Required caps.
			$this->parent_page_slug, // Slug name.
			[ $this, 'page_settings' ] // Callback.
		);

		// Register our form submit hander.
		add_action( 'load-' . $this->parent_page, [ $this, 'form_submitted' ] );

		// Add WordPress scripts, styles and help text.
		add_action( 'admin_print_styles-' . $this->parent_page, [ $this, 'admin_css' ] );
		add_action( 'admin_print_scripts-' . $this->parent_page, [ $this, 'admin_js' ] );
		add_action( 'admin_head-' . $this->parent_page, [ $this, 'admin_head' ], 50 );

		// Insert item in relevant menu.
		$this->settings_page = add_submenu_page(
			$this->parent_page_slug, // Parent slug.
			__( 'CommentPress Core Settings', 'commentpress-core' ),
			__( 'CommentPress Core', 'commentpress-core' ),
			'manage_options', // Required caps.
			$this->settings_page_slug, // Slug name.
			[ $this, 'page_settings' ]
		);

		// Register our form submit hander.
		add_action( 'load-' . $this->settings_page, [ $this, 'form_submitted' ] );

		// Ensure correct menu item is highlighted.
		add_action( 'admin_head-' . $this->settings_page, [ $this, 'admin_menu_highlight' ], 50 );

		// Add WordPress scripts, styles and help text.
		add_action( 'admin_print_styles-' . $this->settings_page, [ $this, 'admin_css' ] );
		add_action( 'admin_print_scripts-' . $this->settings_page, [ $this, 'admin_js' ] );
		add_action( 'admin_head-' . $this->settings_page, [ $this, 'admin_head' ], 50 );

	}

	/**
	 * Enqueue Settings Page CSS.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 */
	public function admin_css() {

		// Add admin stylesheet.
		wp_enqueue_style(
			'commentpress_admin_css',
			plugins_url( 'includes/core/assets/css/admin.css', COMMENTPRESS_PLUGIN_FILE ),
			false,
			COMMENTPRESS_VERSION, // Version.
			'all' // Media.
		);

	}

	/**
	 * Enqueue Settings Page Javascript.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 */
	public function admin_js() {

	}

	/**
	 * Highlight the plugin's parent menu item.
	 *
	 * Regardless of the actual admin screen we are on, we need the parent menu
	 * item to be highlighted so that the appropriate menu is open by default
	 * when the Sub-page is viewed.
	 *
	 * @since 4.0
	 *
	 * @global string $plugin_page The current plugin Page.
	 * @global string $submenu_file The current submenu.
	 */
	public function admin_menu_highlight() {

		global $plugin_page, $submenu_file;

		// Define Sub-pages.
		$subpages = [
			$this->settings_page_slug,
		];

		/**
		 * Filter the list of Sub-pages.
		 *
		 * @since 4.0
		 *
		 * @param array $subpages The existing list of Sub-pages.
		 */
		$subpages = apply_filters( 'commentpress/core/settings/site/page/subpages', $subpages );

		// This tweaks the Settings subnav menu to show only one menu item.
		if ( in_array( $plugin_page, $subpages ) ) {
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$plugin_page = $this->parent_page_slug;
			// phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			$submenu_file = $this->parent_page_slug;
		}

	}

	/**
	 * Performs tasks in Options Page header.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 */
	public function admin_head() {

		// Enqueue WordPress scripts.
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'dashboard' );

		// Add help.
		$this->admin_help();

	}

	/**
	 * Adds help copy to Admin Page.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class and renamed.
	 */
	public function admin_help() {

		// Get screen object.
		$screen = get_current_screen();

		// Bail if not our screen.
		if ( $screen->id != $this->settings_page ) {
			return;
		}

		// Add a help tab.
		$screen->add_help_tab( [
			'id'      => 'commentpress-base',
			'title'   => __( 'CommentPress Core Help', 'commentpress-core' ),
			'content' => $this->core->display->get_help(),
		] );

		// --<
		return $screen;

	}

	// -------------------------------------------------------------------------

	/**
	 * Checks the access capability for this Page.
	 *
	 * @since 4.0
	 *
	 * @return bool True if the current User has the capability, false otherwise.
	 */
	public function page_capability() {

		/**
		 * Set access capability but allow overrides.
		 *
		 * @since 4.0
		 *
		 * @param string The default capability for access to Settings Page.
		 */
		$capability = apply_filters( 'commentpress/core/settings/site/page/cap', 'manage_options' );

		// Check User permissions.
		if ( ! current_user_can( $capability ) ) {
			return false;
		}

		// --<
		return true;

	}

	/**
	 * Get Settings Page Tab URLs.
	 *
	 * @since 4.0
	 *
	 * @return array $urls The array of Settings Page Tab URLs.
	 */
	public function page_tab_urls_get() {

		// Only build once.
		if ( ! empty( $this->urls ) ) {
			return $this->urls;
		}

		// Get Settings Page URL.
		$this->urls['settings'] = menu_page_url( $this->settings_page_slug, false );

		/**
		 * Filter the list of URLs.
		 *
		 * @since 4.0
		 *
		 * @param array $urls The existing list of URLs.
		 */
		$this->urls = apply_filters( 'commentpress/core/settings/site/page/tab_urls', $this->urls );

		// --<
		return $this->urls;

	}

	/**
	 * Show our Settings Page.
	 *
	 * @since 4.0
	 */
	public function page_settings() {

		// Check User permissions.
		if ( ! $this->page_capability() ) {
			return;
		}

		// Get Settings Page Tab URLs.
		$urls = $this->page_tab_urls_get();

		/**
		 * Do not show tabs by default but allow overrides.
		 *
		 * @since 4.0
		 *
		 * @param bool False by default - do not show tabs.
		 */
		$show_tabs = apply_filters( 'commentpress/core/settings/site/page/show_tabs', false );

		// Get current screen.
		$screen = get_current_screen();

		/**
		 * Allow meta boxes to be added to this screen.
		 *
		 * The Screen IDs to use are:
		 *
		 * * "settings_page_commentpress_admin"
		 * * "admin_page_commentpress_settings"
		 *
		 * @since 4.0
		 *
		 * @param string $screen_id The ID of the current screen.
		 */
		do_action( 'add_meta_boxes', $screen->id, null );

		// Grab columns.
		$columns = ( 1 == $screen->get_columns() ? '1' : '2' );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->page_path . 'page-settings-site.php';

	}

	/**
	 * Get our Settings Page screens.
	 *
	 * @since 4.0
	 *
	 * @return array $settings_screens The array of Settings Page screens.
	 */
	public function page_settings_screens_get() {

		// Define this plugin's Settings Page screen IDs.
		$settings_screens = [
			'settings_page_' . $this->parent_page_slug,
			'admin_page_' . $this->settings_page_slug,
		];

		/**
		 * Filter the Settings Page screens.
		 *
		 * @since 4.0
		 *
		 * @param array $settings_screens The default array of Settings Page screens.
		 */
		return apply_filters( 'commentpress/core/admin/page/settings/screens', $settings_screens );

	}

	/**
	 * Get the URL of the Settings Page.
	 *
	 * @since 4.0
	 *
	 * @return string $url The URL of the Settings Page.
	 */
	public function page_settings_url_get() {

		// Get Settings Page URL.
		$url = menu_page_url( $this->settings_page_slug, false );

		/**
		 * Filter the Settings Page URL.
		 *
		 * @since 4.0
		 *
		 * @param array $url The default Settings Page URL.
		 */
		$url = apply_filters( 'commentpress/core/admin/page/settings/url', $url );

		// --<
		return $url;

	}

	/**
	 * Get the URL for the Settings Page form action attribute.
	 *
	 * This happens to be the same as the Settings Page URL, but need not be.
	 *
	 * @since 4.0
	 *
	 * @return string $submit_url The URL for the Settings Page form action.
	 */
	public function page_settings_submit_url_get() {

		// Get Settings Page submit URL.
		$submit_url = menu_page_url( $this->settings_page_slug, false );

		/**
		 * Filter the Settings Page submit URL.
		 *
		 * @since 4.0
		 *
		 * @param array $submit_url The Settings Page submit URL.
		 */
		$submit_url = apply_filters( 'commentpress/core/admin/page/settings/submit_url', $submit_url );

		// --<
		return $submit_url;

	}

	/**
	 * Get the URL to the Settings Page in our Admin Notices.
	 *
	 * @since 4.0
	 *
	 * @return string $notice_url The URL to the Settings Page in our Admin Notices.
	 */
	public function page_settings_warning_url_get() {

		// Use default Settings Page URL.
		$notice_url = menu_page_url( $this->settings_page_slug, false );

		/**
		 * Filter the Settings Page URL in Admin Notices.
		 *
		 * @since 4.0
		 *
		 * @param array $notice_url The default Settings Page URL in Admin Notices.
		 */
		$notice_url = apply_filters( 'commentpress/core/admin/notice/url', $notice_url );

		// --<
		return $notice_url;

	}

	// -------------------------------------------------------------------------

	/**
	 * Register meta boxes.
	 *
	 * @since 4.0
	 *
	 * @param string $screen_id The Admin Page Screen ID.
	 */
	public function meta_boxes_add( $screen_id ) {

		// Get our Settings Page screens.
		$settings_screens = $this->page_settings_screens_get();

		// Bail if not the Screen ID we want.
		if ( ! in_array( $screen_id, $settings_screens ) ) {
			return;
		}

		// Check User permissions.
		if ( ! $this->page_capability() ) {
			return;
		}

		// Create "General Settings" metabox.
		add_meta_box(
			'commentpress_general',
			__( 'General Settings', 'commentpress-core' ),
			[ $this, 'meta_box_general_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

		// Create "Table of Contents" metabox.
		add_meta_box(
			'commentpress_toc',
			__( 'Table of Contents', 'commentpress-core' ),
			[ $this, 'meta_box_toc_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

		// Create "Page Display Options" metabox.
		add_meta_box(
			'commentpress_page_display',
			__( 'Page Display Options', 'commentpress-core' ),
			[ $this, 'meta_box_page_display_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

		// Create "Commenting Options" metabox.
		add_meta_box(
			'commentpress_commenting',
			__( 'Commenting Options', 'commentpress-core' ),
			[ $this, 'meta_box_commenting_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

		// Create "Theme Customisation" metabox.
		add_meta_box(
			'commentpress_theme',
			__( 'Theme Customisation', 'commentpress-core' ),
			[ $this, 'meta_box_theme_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

		// Create "Submit" metabox.
		add_meta_box(
			'submitdiv',
			__( 'Settings', 'commentpress-core' ),
			[ $this, 'meta_box_submit_render' ], // Callback.
			$screen_id, // Screen ID.
			'side', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

	}

	/**
	 * Renders the "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_general_render() {

		// Get Post Types that support the editor.
		$capable_post_types = $this->core->db->get_supported_post_types();

		// Get chosen Post Types.
		$selected_types = $this->core->db->option_get( 'cp_post_types_disabled', [] );

		// Get settings.
		$do_not_parse = $this->core->db->option_get( 'cp_do_not_parse', 'n' );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-site-general.php';

	}

	/**
	 * Renders the "Table of Contents" metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_toc_render() {

		// Get settings.
		$show_posts_or_pages_in_toc = $this->core->db->option_get( 'cp_show_posts_or_pages_in_toc' );
		$toc_chapter_is_page = $this->core->db->option_get( 'cp_toc_chapter_is_page' );
		$show_subpages = $this->core->db->option_get( 'cp_show_subpages' );
		$show_extended_toc = $this->core->db->option_get( 'cp_show_extended_toc' );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-site-toc.php';

	}

	/**
	 * Renders the "Page Display Options" metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_page_display_render() {

		// Get settings.
		$featured_images = $this->core->db->option_get( 'cp_featured_images', 'n' );
		$page_nav_enabled = $this->core->db->option_get( 'cp_page_nav_enabled', 'y' );
		$title_visibility = $this->core->db->option_get( 'cp_title_visibility' );
		$page_meta_visibility = $this->core->db->option_get( 'cp_page_meta_visibility' );
		$textblock_meta = $this->core->db->option_get( 'cp_textblock_meta', 'y' );
		$excerpt_length = $this->core->db->option_get( 'cp_excerpt_length' );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-site-page.php';

	}

	/**
	 * Renders the "Commenting Options" metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_commenting_render() {

		// Get settings.
		$comment_editor = $this->core->db->option_get( 'cp_comment_editor' );
		$promote_reading = $this->core->db->option_get( 'cp_promote_reading' );
		$comments_live = $this->core->db->option_get( 'cp_para_comments_live' );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-site-comment.php';

	}

	/**
	 * Renders the "Theme Customisation" metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_theme_render() {

		// Get settings.
		$scroll_speed = $this->core->db->option_get( 'cp_js_scroll_speed' );
		$min_page_width = $this->core->db->option_get( 'cp_min_page_width' );
		$sidebar_default = $this->core->db->option_get( 'cp_sidebar_default', 'comments' );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-site-theme.php';

	}

	/**
	 * Render Save Settings metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_submit_render() {

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-site-submit.php';

	}

	// -------------------------------------------------------------------------

	/**
	 * Routes settings updates to relevant methods.
	 *
	 * @since 4.0
	 */
	public function form_submitted() {

		// Was the form submitted?
		if ( ! isset( $_POST['commentpress_submit'] ) ) {
			return;
		}

		// Check that we trust the source of the data.
		check_admin_referer( 'commentpress_core_settings_action', 'commentpress_core_settings_nonce' );

		// Update the settings.
		$this->core->db->options_update();

		// Get the Settings Page URL.
		$url = $this->page_settings_url_get();

		// Our array of arguments.
		$args = [ 'updated' => 'true' ];

		// Redirect to our Settings Page.
		wp_safe_redirect( add_query_arg( $args, $url ) );
		exit;

	}

	// -------------------------------------------------------------------------

	/**
	 * Utility to add link to "Site Settings" screen on Site Plugins screen.
	 *
	 * @since 4.0
	 *
	 * @param array $links The existing links array.
	 * @param str $file The name of the plugin file.
	 * @return array $links The modified links array.
	 */
	public function action_links( $links, $file ) {

		// Bail if not this plugin.
		if ( $file !== plugin_basename( dirname( COMMENTPRESS_PLUGIN_FILE ) . '/commentpress-core.php' ) ) {
			return $links;
		}

		// Get the "Site Settings" link.
		$link = $this->page_settings_url_get();

		// Add settings link.
		$links[] = '<a href="' . esc_url( $link ) . '">' . esc_html__( 'Site Settings', 'commentpress-core' ) . '</a>';

		// --<
		return $links;

	}

}
