<?php
/**
 * CommentPress Multisite Network Settings class.
 *
 * Handles Network Settings Page functionality in CommentPress Multisite.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Multisite Network Settings Class.
 *
 * This class handles Network Settings Page functionality in CommentPress Multisite.
 *
 * @since 3.3
 */
class CommentPress_Multisite_Settings_Network {

	/**
	 * Multisite loader object.
	 *
	 * @since 3.0
	 * @access public
	 * @var CommentPress_Multisite_Loader
	 */
	public $multisite;

	/**
	 * Settings Page reference.
	 *
	 * @since 4.0
	 * @access public
	 * @var string
	 */
	public $settings_page;

	/**
	 * Settings Page slug.
	 *
	 * @since 4.0
	 * @access public
	 * @var string
	 */
	public $settings_page_slug = 'cpmu_admin_page';

	/**
	 * Relative path to the Page template directory.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $page_path = 'includes/multisite/assets/templates/wordpress/pages/';

	/**
	 * Relative path to the Metabox directory.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $metabox_path = 'includes/multisite/assets/templates/wordpress/metaboxes/';

	/**
	 * Form nonce name.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $nonce_field = 'cpms_settings_network_nonce';

	/**
	 * Form nonce action.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $nonce_action = 'cpms_settings_network_action';

	/**
	 * Form "name" and "id".
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $form_id = 'cpms_settings_network_form';

	/**
	 * Form submit input element "name" and "id".
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $submit_id = 'cpms_settings_network_submit';

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

		// Bail if plugin is not activated network-wide.
		if ( 'mu_sitewide' !== $this->multisite->plugin->plugin_context_get() ) {
			return;
		}

		// Add our item to the network admin menu.
		add_action( 'network_admin_menu', [ $this, 'admin_menu' ] );

		// Add our meta boxes.
		add_action( 'commentpress/multisite/settings/network/page/add_meta_boxes', [ $this, 'meta_boxes_add' ], 11 );

		// Add link to Settings Page.
		add_filter( 'network_admin_plugin_action_links', [ $this, 'action_links' ], 10, 2 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Appends option to WordPress Network Settings menu.
	 *
	 * @since 3.3
	 */
	public function admin_menu() {

		// Check User permissions.
		if ( ! $this->page_capability() ) {
			return;
		}

		// Insert item in relevant menu.
		$this->settings_page = add_submenu_page(
			'settings.php',
			__( 'Network Settings for CommentPress', 'commentpress-core' ),
			__( 'CommentPress Network', 'commentpress-core' ),
			'manage_options',
			$this->settings_page_slug, // Slug name.
			[ $this, 'page_settings' ]
		);

		// Register our form submit hander.
		add_action( 'load-' . $this->settings_page, [ $this, 'form_submitted' ] );

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

		/**
		 * Fires when the Network Settings screen Javascript has been enqueued.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Multisite_BuddyPress_Groupblog::admin_js()
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/multisite/settings/network/admin/js' );

	}

	/**
	 * Performs tasks in Settings Page header.
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

		// Always allow network admins.
		if ( is_super_admin() ) {
			return true;
		}

		/**
		 * Set access capability but allow overrides.
		 *
		 * @since 4.0
		 *
		 * @param string The default capability for access to Settings Page.
		 */
		$capability = apply_filters( 'commentpress/multisite/settings/network/page/cap', 'manage_options' );

		// Check User permissions.
		if ( ! current_user_can( $capability ) ) {
			return false;
		}

		// --<
		return true;

	}

	/**
	 * Renders the Network Settings Page when core is not enabled.
	 *
	 * @since 4.0
	 */
	public function page_settings() {

		// Check User permissions.
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
		do_action( 'commentpress/multisite/settings/network/page/add_meta_boxes', $screen->id );

		// Grab columns.
		$columns = ( 1 === (int) $screen->get_columns() ? '1' : '2' );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->page_path . 'page-settings-network.php';

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
		$url = $this->network_menu_page_url( $this->settings_page_slug, false );

		/**
		 * Filter the Settings Page URL.
		 *
		 * @since 4.0
		 *
		 * @param array $url The default Settings Page URL.
		 */
		$url = apply_filters( 'commentpress/multisite/settings/network/page/settings/url', $url );

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
		$submit_url = $this->network_menu_page_url( $this->settings_page_slug, false );

		/**
		 * Filter the Settings Page submit URL.
		 *
		 * @since 4.0
		 *
		 * @param array $submit_url The Settings Page submit URL.
		 */
		$submit_url = apply_filters( 'commentpress/multisite/settings/network/page/settings/submit_url', $submit_url );

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
		if ( $screen_id !== $this->settings_page . '-network' ) {
			return;
		}

		// Check User permissions.
		if ( ! $this->page_capability() ) {
			return;
		}

		// Create "General Settings" metabox.
		add_meta_box(
			'commentpress_network_general',
			__( 'General Settings', 'commentpress-core' ),
			[ $this, 'meta_box_general_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

		/*
		// Create "WordPress Overrides" metabox.
		add_meta_box(
			'commentpress_network_wordpress',
			__( 'WordPress Overrides', 'commentpress-core' ),
			[ $this, 'meta_box_wordpress_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);
		*/

		/*
		// Create "Welcome Page Content" metabox.
		add_meta_box(
			'commentpress_network_title_page',
			__( 'Title Page Content', 'commentpress-core' ),
			[ $this, 'meta_box_title_page_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);
		*/

		// Create "Danger Zone" metabox.
		add_meta_box(
			'commentpress_network_danger',
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
		 * Used internally by:
		 *
		 * * CommentPress_Multisite_BuddyPress::network_admin_metaboxes()
		 *
		 * @since 4.0
		 *
		 * @param string $screen_id The Network Settings Screen ID.
		 */
		do_action( 'commentpress/multisite/settings/network/metaboxes/after', $screen_id );

	}

	/**
	 * Renders the "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_general_render() {

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-network-general.php';

	}

	/**
	 * Renders the "WordPress Overrides" metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_wordpress_render() {

		// Get settings.
		$delete_first_page    = $this->multisite->db->setting_get( 'cpmu_delete_first_page' );
		$delete_first_post    = $this->multisite->db->setting_get( 'cpmu_delete_first_post' );
		$delete_first_comment = $this->multisite->db->setting_get( 'cpmu_delete_first_comment' );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-network-wordpress.php';

	}

	/**
	 * Renders the "Welcome Page Content" metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_title_page_render() {

		// Get settings.
		$content = stripslashes( $this->multisite->db->setting_get( 'cpmu_title_page_content' ) );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-network-title-page.php';

	}

	/**
	 * Renders the "Danger Zone" metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_danger_render() {

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-network-danger.php';

	}

	/**
	 * Render Save Settings metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_submit_render() {

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-network-submit.php';

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
		 * Fires before network settings have been updated.
		 *
		 * * Callbacks do not need to verify the nonce as this has already been done.
		 * * Callbacks should, however, implement their own data validation checks.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Multisite_Sites::settings_save() (Priority: 10)
		 * * CommentPress_Multisite_BuddyPress::network_admin_update() (Priority: 20)
		 * * CommentPress_Multisite_BuddyPress_Groupblog::network_admin_update() (Priority: 30)
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/multisite/settings/network/save/before' );

		// Get "Reset to defaults" value.
		$reset = isset( $_POST['cpmu_reset'] ) ? sanitize_text_field( wp_unslash( $_POST['cpmu_reset'] ) ) : '0';

		// Maybe reset the settings to the defaults.
		if ( ! empty( $reset ) ) {
			$this->multisite->db->settings_reset();
		}

		// Save the settings to the database.
		$this->multisite->db->settings_save();

		/**
		 * Fires when the Network Settings have been saved.
		 *
		 * * Callbacks do not need to verify the nonce as this has already been done.
		 * * Callbacks should, however, implement their own data validation checks.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/multisite/settings/network/form_submitted/post' );

		// Now redirect.
		$this->form_redirect();

	}

	/**
	 * Form redirection handler.
	 *
	 * @since 4.0
	 */
	public function form_redirect() {

		// Get the Network Settings Page URL.
		$url = $this->page_settings_url_get();

		// Our array of arguments.
		$args = [ 'updated' => 'true' ];

		// Do the redirect.
		wp_safe_redirect( $url );
		exit();

	}

	// -------------------------------------------------------------------------

	/**
	 * Get the URL to access a particular menu Page.
	 *
	 * The URL based on the slug it was registered with. If the slug hasn't been
	 * registered properly no url will be returned.
	 *
	 * @since 4.0
	 *
	 * @param string $menu_slug The slug name to refer to this menu by (should be unique for this menu).
	 * @param bool   $echo Whether or not to echo the url - default is true.
	 * @return string $url The URL.
	 */
	public function network_menu_page_url( $menu_slug, $echo = true ) {

		global $_parent_pages;

		if ( isset( $_parent_pages[ $menu_slug ] ) ) {
			$parent_slug = $_parent_pages[ $menu_slug ];
			if ( $parent_slug && ! isset( $_parent_pages[ $parent_slug ] ) ) {
				$url = network_admin_url( add_query_arg( 'page', $menu_slug, $parent_slug ) );
			} else {
				$url = network_admin_url( 'admin.php?page=' . $menu_slug );
			}
		} else {
			$url = '';
		}

		$url = esc_url( $url );

		if ( $echo ) {
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo $url;
		}

		// --<
		return $url;

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds a link to "Network Settings" screen on Network Plugins screen.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @param array $links The existing links array.
	 * @param str   $file The name of the plugin file.
	 * @return array $links The modified links array.
	 */
	public function action_links( $links, $file ) {

		// Bail if not this plugin.
		if ( plugin_basename( dirname( COMMENTPRESS_PLUGIN_FILE ) . '/commentpress-core.php' ) !== $file ) {
			return $links;
		}

		// Get the "Network Settings" link.
		$link = $this->page_settings_url_get();

		// Add settings link.
		$links[] = '<a href="' . esc_url( $link ) . '">' . esc_html__( 'Network Settings', 'commentpress-core' ) . '</a>';

		// --<
		return $links;

	}

}
