<?php
/**
 * CommentPress Multisite Site Settings class.
 *
 * Handles Site Settings Page functionality in CommentPress Multisite.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Multisite Site Settings Class.
 *
 * This class handles Site Settings Page functionality in CommentPress Multisite.
 *
 * @since 3.3
 */
class CommentPress_Multisite_Settings_Site {

	/**
	 * Multisite loader object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $multisite The multisite loader object.
	 */
	public $multisite;

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
	private $page_path = 'includes/multisite/assets/templates/wordpress/pages/';

	/**
	 * Metabox template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $metabox_path Relative path to the Metabox directory.
	 */
	private $metabox_path = 'includes/multisite/assets/templates/wordpress/metaboxes/';

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param object $multisite Reference to the multisite loader object.
	 */
	public function __construct( $multisite ) {

		// Store reference to multisite loader object.
		$this->multisite = $multisite;

		// Init when the multisite plugin is fully loaded.
		add_action( 'commentpress/multisite/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this object.
	 *
	 * @since 3.3
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

		// Bail if not network-enabled.
		if ( $this->multisite->plugin->plugin_context !== 'mu_sitewide' ) {
			return;
		}

		// Is CommentPress Core active on this Blog?
		if ( $this->multisite->db->is_commentpress() ) {

			// Modify CommentPress Core Settings Page.
			add_action( 'commentpress/core/settings/site/page/settings/metaboxes/after', [ $this, 'meta_boxes_append' ] );

			// Hook into CommentPress Core Settings Page result.
			add_action( 'commentpress/core/db/options_update/before', [ $this, 'form_disable_core' ] );

		} else {

			// Add our item to the admin menu.
			add_action( 'admin_menu', [ $this, 'admin_menu' ] );

			// Add our meta boxes.
			add_action( 'add_meta_boxes', [ $this, 'meta_boxes_add' ], 11 );

			// Add link to Settings Page.
			add_filter( 'plugin_action_links', [ $this, 'action_links' ], 10, 2 );

		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Add our Admin Page(s) to the WordPress admin menu.
	 *
	 * @since 3.3
	 */
	public function admin_menu() {

		// Check User permissions.
		if ( ! $this->page_capability() ) {
			return;
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
		add_action( 'load-' . $this->parent_page, [ $this, 'form_enable_core' ] );

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
		add_action( 'load-' . $this->settings_page, [ $this, 'form_enable_core' ] );

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
	 * @since 4.0
	 */
	public function admin_css() {

	}

	/**
	 * Enqueue Settings Page Javascript.
	 *
	 * @since 4.0
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
	 * Performs tasks in Site Settings screen header.
	 *
	 * @since 4.0
	 */
	public function admin_head() {

		// Enqueue WordPress scripts.
		wp_enqueue_script( 'common' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'dashboard' );

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
		$capability = apply_filters( 'commentpress/multisite/settings/site/page/cap', 'manage_options' );

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
		$this->urls = apply_filters( 'commentpress/multisite/settings/site/page/tab_urls', $this->urls );

		// --<
		return $this->urls;

	}

	/**
	 * Renders the Site Settings screen when core is not enabled.
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
		$show_tabs = apply_filters( 'commentpress/multisite/settings/site/page/show_tabs', false );

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
		return apply_filters( 'commentpress/multisite/settings/site/page/settings/screens', $settings_screens );

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
		$url = apply_filters( 'commentpress/multisite/settings/site/page/settings/url', $url );

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
		$submit_url = apply_filters( 'commentpress/multisite/settings/site/page/settings/submit_url', $submit_url );

		// --<
		return $submit_url;

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

		// Create "Activation" metabox.
		add_meta_box(
			'commentpress_activate',
			__( 'Activation', 'commentpress-core' ),
			[ $this, 'meta_box_activate_render' ], // Callback.
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

		/**
		 * Fires after Site Settings metaboxes have been added.
		 *
		 * @since 4.0
		 *
		 * @param string $screen_id The Admin Page Screen ID.
		 */
		do_action( 'commentpress/multisite/settings/site/page/settings/metaboxes/after', $screen_id );

	}

	/**
	 * Renders the "Activation" metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_activate_render() {

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-site-activate.php';

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
	 * Registers additional meta boxes to the core Site Settings screen.
	 *
	 * @since 4.0
	 *
	 * @param string $screen_id The Admin Page Screen ID.
	 */
	public function meta_boxes_append( $screen_id ) {

		// Check User permissions.
		if ( ! $this->page_capability() ) {
			return;
		}

		// Create "Deactivation" metabox.
		add_meta_box(
			'commentpress_deactivate',
			__( 'Danger Zone', 'commentpress-core' ),
			[ $this, 'meta_box_deactivate_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'low' // Vertical placement: options are 'core', 'high', 'low'.
		);

	}

	/**
	 * Renders the "Deactivation" metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_deactivate_render() {

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-site-deactivate.php';

	}

	// -------------------------------------------------------------------------

	/**
	 * Enable CommentPress Core.
	 *
	 * @since 3.3
	 */
	public function form_enable_core() {

		// Was the form submitted?
		if ( ! isset( $_POST['commentpress_submit'] ) ) {
			return;
		}

		// Check that we trust the source of the data.
		check_admin_referer( 'commentpress_core_settings_action', 'commentpress_core_settings_nonce' );

		// Get the posted variable.
		$cp_activate_commentpress = isset( $_POST['cp_activate_commentpress'] ) ?
			sanitize_text_field( wp_unslash( $_POST['cp_activate_commentpress'] ) ) :
			'0';

		// Did we ask to activate CommentPress Core?
		if ( $cp_activate_commentpress === '1' ) {

			// Install core, but not from wpmu_new_blog.
			$this->multisite->db->install_commentpress( 'admin_page' );

			// Get Settings Page URL.
			$url = $this->page_settings_url_get();

			// Redirect.
			wp_safe_redirect( $url );
			exit();

		}

	}

	/**
	 * Disable CommentPress Core.
	 *
	 * @since 3.3
	 */
	public function form_disable_core() {

		// Was the form submitted?
		if ( ! isset( $_POST['commentpress_submit'] ) ) {
			return;
		}

		// Check that we trust the source of the data.
		check_admin_referer( 'commentpress_core_settings_action', 'commentpress_core_settings_nonce' );

		// Get the posted variable.
		$cp_deactivate_commentpress = isset( $_POST['cp_deactivate_commentpress'] ) ?
			sanitize_text_field( wp_unslash( $_POST['cp_deactivate_commentpress'] ) ) :
			'0';

		// Did we ask to deactivate CommentPress Core?
		if ( $cp_deactivate_commentpress == '1' ) {

			// Get Settings Page URL.
			$url = $this->page_settings_url_get();

			// Uninstall core.
			$this->multisite->db->uninstall_commentpress();

			// Redirect.
			wp_safe_redirect( $url );
			exit();

		}

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
