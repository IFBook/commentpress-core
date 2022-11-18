<?php
/**
 * CommentPress Core Multisite Admin class.
 *
 * Handles admin settings page functionality in CommentPress Multisite.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Multisite Admin Class.
 *
 * This class handles admin settings page functionality in CommentPress Multisite.
 *
 * @since 3.3
 */
class CommentPress_Multisite_Admin {

	/**
	 * Multisite plugin object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $ms_loader The multisite plugin object.
	 */
	public $ms_loader;

	/**
	 * Options page reference.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $options_page The options page reference.
	 */
	public $options_page;

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
			add_action( 'commentpress/core/admin/settings/general/before', [ $this, 'form_disable_element' ] );

			// Hook into CommentPress Core settings page result.
			add_action( 'commentpress/core/db/options_update/before', [ $this, 'form_disable_core' ] );

		} else {

			// Add our item to the admin menu.
			add_action( 'admin_menu', [ $this, 'admin_menu' ] );

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
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Insert item in relevant menu.
		$this->options_page = add_options_page(
			__( 'CommentPress Core Settings', 'commentpress-core' ),
			__( 'CommentPress Core', 'commentpress-core' ),
			'manage_options',
			'commentpress_admin',
			[ $this, 'page_settings_site' ]
		);

		// Register our form submit hander.
		add_action( 'load-' . $this->options_page, [ $this, 'form_enable_core' ] );

		/*
		// Add scripts and styles.
		add_action( 'admin_print_scripts-' . $this->options_page, [ $this, 'admin_js' ] );
		add_action( 'admin_print_styles-' . $this->options_page, [ $this, 'admin_css' ] );
		add_action( 'admin_head-' . $this->options_page, [ $this, 'admin_head' ], 50 );
		*/

	}

	/**
	 * Renders the core Settings page.
	 *
	 * @since 3.3
	 * @since 4.0 Renamed.
	 */
	public function page_settings_site() {

		// Check user permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Get our admin options page.
		echo $this->page_settings_site_get();

	}

	/**
	 * Gets the WordPress admin page markup.
	 *
	 * @since 3.3
	 * @since 4.0 Renamed.
	 *
	 * @return string $admin_page The HTML for the admin page.
	 */
	private function page_settings_site_get() {

		// Open div.
		$admin_page = '<div class="wrap" id="cpmu_admin_wrapper">' . "\n\n";

		// Get our form.
		$admin_page .= $this->page_settings_site_form_get();

		// Close div.
		$admin_page .= '</div>' . "\n\n";

		// --<
		return $admin_page;

	}

	/**
	 * Returns the admin form HTML.
	 *
	 * @since 3.3
	 *
	 * @return string $admin_page The HTML for the admin page.
	 */
	private function page_settings_site_form_get() {

		// Sanitise admin page URL.
		$url = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		$url_array = explode( '&', $url );
		if ( $url_array ) {
			$url = $url_array[0];
		}

		// Init vars.
		$label = __( 'Activate CommentPress', 'commentpress-core' );
		$submit = __( 'Save Changes', 'commentpress-core' );

		// Define admin page.
		$admin_page = '
		<h1>' . __( 'CommentPress Core Settings', 'commentpress-core' ) . '</h1>

		<form method="post" action="' . htmlentities( $url . '&updated=true' ) . '">

		' . wp_nonce_field( 'commentpress_core_settings_action', 'commentpress_core_settings_nonce', true, false ) . '
		' . wp_referer_field( false ) . '
		<input id="cp_activate" name="cp_activate" value="1" type="hidden" />

		<h4>' . __( 'Activation', 'commentpress-core' ) . '</h4>

		<table class="form-table">

			<tr valign="top">
				<th scope="row"><label for="cp_activate_commentpress">' . $label . '</label></th>
				<td><input id="cp_activate_commentpress" name="cp_activate_commentpress" value="1" type="checkbox" /></td>
			</tr>

		</table>

		<input type="hidden" name="action" value="update" />

		<p class="submit">
			<input type="submit" name="commentpress_submit" value="' . $submit . '" class="button-primary" />
		</p>

		</form>' . "\n\n\n\n";

		// --<
		return $admin_page;

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

		// Init var.
		$cp_activate_commentpress = 0;

		// Get vars.
		extract( $_POST );

		// Did we ask to activate CommentPress Core?
		if ( $cp_activate_commentpress == '1' ) {

			// Install core, but not from wpmu_new_blog.
			$this->ms_loader->db->install_commentpress( 'admin_page' );

			// Get core reference.
			$core = commentpres_core();

			// Get Settings Page URL.
			$url = $core->admin->page_settings_url_get();

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

		// Init var.
		$cp_deactivate_commentpress = 0;

		// Get vars.
		extract( $_POST );

		// Did we ask to deactivate CommentPress Core?
		if ( $cp_deactivate_commentpress == '1' ) {

			// Get core reference.
			$core = commentpres_core();

			// Get Settings Page URL.
			$url = $core->admin->page_settings_url_get();

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

		// Define HTML.
		echo '
		<tr valign="top">
			<th scope="row"><label for="cp_deactivate_commentpress">' . esc_html__( 'Disable CommentPress Core on this site', 'commentpress-core' ) . '</label></th>
			<td><input id="cp_deactivate_commentpress" name="cp_deactivate_commentpress" value="1" type="checkbox" /></td>
		</tr>
		';

	}

}
