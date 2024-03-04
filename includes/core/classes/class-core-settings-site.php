<?php
/**
 * CommentPress Core Site Settings class.
 *
 * Handles Site Settings screen functionality in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Site Settings Class.
 *
 * This class handles Site Settings screen functionality in CommentPress Core.
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
	 * Form nonce name.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $nonce_field The name of the form nonce element.
	 */
	private $nonce_field = 'commentpress_core_settings_site_nonce';

	/**
	 * Form nonce value.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $nonce_action The name of the form nonce value.
	 */
	private $nonce_action = 'commentpress_core_settings_site_action';

	/**
	 * Form "name" and "id".
	 *
	 * @since 4.0
	 * @access private
	 * @var string $input_submit The "name" and "id" of the form.
	 */
	private $form_id = 'commentpress_core_settings_site_form';

	/**
	 * Form submit input element "name" and "id".
	 *
	 * @since 4.0
	 * @access private
	 * @var string $input_submit The "name" and "id" of the form's submit input element.
	 */
	private $submit_id = 'commentpress_core_settings_site_submit';

	/**
	 * Constructor.
	 *
	 * @since 4.0
	 *
	 * @param object $core Reference to the core loader object.
	 */
	public function __construct( $core ) {

		// Store reference to core loader object.
		$this->core = $core;

		// Init when the core plugin is fully loaded.
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
		add_action( 'commentpress/core/settings/site/page/add_meta_boxes', [ $this, 'meta_boxes_add' ], 11 );

		// Add link to Settings Page.
		add_filter( 'plugin_action_links', [ $this, 'action_links' ], 10, 2 );

		// Listen for form redirection requests.
		add_action( 'commentpress/core/settings/site/form/redirect', [ $this, 'form_redirect' ] );

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

	// -------------------------------------------------------------------------

	/**
	 * Appends option to WordPress Settings menu.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 */
	public function admin_menu() {

		// Check User permissions.
		$capability = $this->page_capability();
		if ( false === $capability ) {
			return;
		}

		/*
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
		*/

		// Add parent Page to Settings menu.
		$this->parent_page = add_options_page(
			__( 'Site Settings for CommentPress', 'commentpress-core' ),
			__( 'CommentPress', 'commentpress-core' ),
			$capability, // Required caps.
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
			__( 'Site Settings for CommentPress', 'commentpress-core' ),
			__( 'CommentPress', 'commentpress-core' ),
			$capability, // Required caps.
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

		/*
		// Add admin stylesheet.
		wp_enqueue_style(
			'commentpress_admin_css',
			plugins_url( 'includes/core/assets/css/admin.css', COMMENTPRESS_PLUGIN_FILE ),
			false,
			COMMENTPRESS_VERSION, // Version.
			'all' // Media.
		);
		*/

	}

	/**
	 * Enqueue Settings Page Javascript.
	 *
	 * @since 4.0
	 */
	public function admin_js() {

		/**
		 * Fires when the Site Settings screen Javascript has been enqueued.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/settings/site/admin/js' );

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
	 * @return string|bool The capability if the current User has it, false otherwise.
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
		return $capability;

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
	 * Renders the Site Settings screen.
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
		do_action( 'commentpress/core/settings/site/page/add_meta_boxes', $screen->id );

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
		return apply_filters( 'commentpress/core/settings/site/page/settings/screens', $settings_screens );

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
		$url = apply_filters( 'commentpress/core/settings/site/page/settings/url', $url );

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
		$submit_url = apply_filters( 'commentpress/core/settings/site/page/settings/submit_url', $submit_url );

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
		$notice_url = apply_filters( 'commentpress/core/settings/site/notice/url', $notice_url );

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

		// Create "Danger Zone" metabox.
		add_meta_box(
			'commentpress_danger',
			__( 'Danger Zone', 'commentpress-core' ),
			[ $this, 'meta_box_danger_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'low' // Vertical placement: options are 'core', 'high', 'low'.
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
		 * Fires when all metaboxes have been added.
		 *
		 * @since 4.0
		 *
		 * @param string $screen_id The Settings Page Screen ID.
		 */
		do_action( 'commentpress/core/settings/site/metaboxes/after', $screen_id );

	}

	/**
	 * Renders the "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_general_render() {

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-site-general.php';

	}

	/**
	 * Renders the "Danger Zone" metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_danger_render() {

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-site-danger.php';

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
	 * Form submission handler.
	 *
	 * @since 4.0
	 */
	public function form_submitted() {

		// Was the form submitted?
		if ( ! isset( $_POST[ $this->submit_id ] ) ) {
			return;
		}

		// Check that we trust the source of the data.
		check_admin_referer( $this->nonce_action, $this->nonce_field );

		/**
		 * Fires before the options have been saved.
		 *
		 * * Callbacks do not need to verify the nonce as this has already been done.
		 * * Callbacks should, however, implement their own data validation checks.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Core_Entry_Document::settings_meta_box_part_save() (Priority: 10)
		 * * CommentPress_Core_Entry_Formatter::settings_meta_box_part_save() (Priority: 10)
		 * * CommentPress_Core_Theme_Sidebar::settings_meta_box_part_save() (Priority: 10)
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/settings/site/save/before' );

		// Get "Reset to defaults" value.
		$reset = isset( $_POST['cp_reset'] ) ? sanitize_text_field( wp_unslash( $_POST['cp_reset'] ) ) : '0';

		// Maybe reset the settings to the defaults.
		if ( ! empty( $reset ) ) {
			$this->core->db->settings_reset();
		}

		// Save the settings.
		$this->core->db->settings_save();

		/**
		 * Fires when the Site Settings have been saved.
		 *
		 * * Callbacks do not need to verify the nonce as this has already been done.
		 * * Callbacks should, however, implement their own data validation checks.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/settings/site/save/after' );

		// Now redirect.
		$this->form_redirect();

	}

	/**
	 * Form redirection handler.
	 *
	 * Also responds to redirection requests made by calling:
	 *
	 * do_action( 'commentpress/core/settings/site/form/redirect' );
	 *
	 * @see CommentPress_Multisite_Site:settings_meta_box_part_save()
	 *
	 * @since 4.0
	 */
	public function form_redirect() {

		// Get the Site Settings Page URL.
		$url = $this->page_settings_url_get();

		// Our array of arguments.
		$args = [ 'updated' => 'true' ];

		// Redirect to our Settings Page.
		wp_safe_redirect( add_query_arg( $args, $url ) );
		exit;

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds a link to "Site Settings" screen on Site Plugins screen.
	 *
	 * @since 4.0
	 *
	 * @param array $links The existing links array.
	 * @param str   $file The name of the plugin file.
	 * @return array $links The modified links array.
	 */
	public function action_links( $links, $file ) {

		// Bail if not this plugin.
		if ( plugin_basename( dirname( COMMENTPRESS_PLUGIN_FILE ) . '/commentpress-core.php' !== $file ) ) {
			return $links;
		}

		// Get the "Site Settings" link.
		$link = $this->page_settings_url_get();

		// Use "Site Settings" when network active.
		if ( 'mu_sitewide' === commentpress()->plugin_context_get() ) {
			$text = __( 'Site Settings', 'commentpress-core' );
		} else {
			$text = __( 'Settings', 'commentpress-core' );
		}

		// Add settings link.
		$links[] = '<a href="' . esc_url( $link ) . '">' . esc_html( $text ) . '</a>';

		// --<
		return $links;

	}

}
