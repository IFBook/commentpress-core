<?php
/**
 * CommentPress Multisite Site Settings class.
 *
 * Handles Site Settings page functionality in CommentPress Multisite.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Multisite Site Settings Class.
 *
 * This class handles Site Settings page functionality in CommentPress Multisite.
 *
 * @since 3.3
 */
class CommentPress_Multisite_Settings_Site {

	/**
	 * Multisite plugin object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $ms_loader The multisite plugin object.
	 */
	public $ms_loader;

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
	public $settings_page_slug = 'commentpress_admin';

	/**
	 * Page template directory path.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $page_path Relative path to the Page template directory.
	 */
	public $page_path = 'commentpress-multisite/assets/templates/wordpress/pages/';

	/**
	 * Metabox template directory path.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $metabox_path Relative path to the Metabox directory.
	 */
	public $metabox_path = 'commentpress-multisite/assets/templates/wordpress/metaboxes/';

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param object $ms_loader Reference to the multisite plugin object.
	 */
	public function __construct( $ms_loader ) {

		// Store reference to multisite plugin object.
		$this->ms_loader = $ms_loader;

		// Init when the multisite plugin is fully loaded.
		add_action( 'commentpress/multisite/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this obiject.
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
		if ( COMMENTPRESS_PLUGIN_CONTEXT !== 'mu_sitewide' ) {
			return;
		}

		// Is CommentPress Core active on this blog?
		if ( $this->ms_loader->db->is_commentpress() ) {

			// Modify CommentPress Core settings page.
			add_action( 'commentpress/core/settings/site/metabox/general/before', [ $this, 'form_disable_element' ] );

			// Hook into CommentPress Core settings page result.
			add_action( 'commentpress/core/db/options_update/before', [ $this, 'form_disable_core' ] );

		} else {

			// Add our item to the admin menu.
			add_action( 'admin_menu', [ $this, 'admin_menu' ] );

			// Add our meta boxes.
			add_action( 'add_meta_boxes', [ $this, 'meta_boxes_add' ], 11 );

		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Appends option to admin menu.
	 *
	 * @since 3.3
	 */
	public function admin_menu() {

		// Check user permissions.
		if ( ! $this->page_capability() ) {
			return;
		}

		// Insert item in relevant menu.
		$this->settings_page = add_options_page(
			__( 'CommentPress Core Settings', 'commentpress-core' ),
			__( 'CommentPress Core', 'commentpress-core' ),
			'manage_options',
			$this->settings_page_slug, // Slug name.
			[ $this, 'page_settings' ]
		);

		// Register our form submit hander.
		add_action( 'load-' . $this->settings_page, [ $this, 'form_enable_core' ] );

		// Add WordPress scripts, styles and help text.
		add_action( 'admin_print_styles-' . $this->settings_page, [ $this, 'admin_css' ] );
		add_action( 'admin_print_scripts-' . $this->settings_page, [ $this, 'admin_js' ] );
		add_action( 'admin_head-' . $this->settings_page, [ $this, 'admin_head' ], 50 );

	}

	/**
	 * Enqueue Settings page CSS.
	 *
	 * @since 4.0
	 */
	public function admin_css() {

	}

	/**
	 * Enqueue Settings page Javascript.
	 *
	 * @since 4.0
	 */
	public function admin_js() {

	}

	/**
	 * Performs tasks in Settings page header.
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
	 * Checks the access capability for this page.
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

		// Check user permissions.
		if ( ! current_user_can( $capability ) ) {
			return false;
		}

		// --<
		return true;

	}

	/**
	 * Renders the Site Settings page when core is not enabled.
	 *
	 * @since 4.0
	 */
	public function page_settings() {

		// Check user permissions.
		if ( ! $this->page_capability() ) {
			return;
		}

		// Get current screen.
		$screen = get_current_screen();

		/**
		 * Allow meta boxes to be added to this screen.
		 *
		 * @since 4.0
		 *
		 * @param string $screen_id The ID of the current screen.
		 */
		do_action( 'add_meta_boxes', $screen->id, null );

		// Grab columns.
		$columns = ( 1 == $screen->get_columns() ? '1' : '2' );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->page_path . 'page-site-settings.php';

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

		// Bail if not the Screen ID we want.
		if ( $screen_id !== $this->settings_page ) {
			return;
		}

		// Check user permissions.
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

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-site-settings-general.php';

	}

	/**
	 * Render Save Settings metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_submit_render() {

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-site-settings-submit.php';

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
			$this->ms_loader->db->install_commentpress( 'admin_page' );

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
			$this->ms_loader->db->uninstall_commentpress();

			// Redirect.
			wp_safe_redirect( $url );
			exit();

		}

	}

	/**
	 * Insert the deactivation form element.
	 *
	 * @since 3.3
	 * @since 4.0 Renamed.
	 */
	public function form_disable_element() {

		// Render form element.
		?>
		<tr valign="top">
			<th scope="row"><label for="cp_deactivate_commentpress"><?php esc_html_e( 'Disable CommentPress Core on this site', 'commentpress-core' ); ?></label></th>
			<td><input id="cp_deactivate_commentpress" name="cp_deactivate_commentpress" value="1" type="checkbox" /></td>
		</tr>
		<?php

	}

}
