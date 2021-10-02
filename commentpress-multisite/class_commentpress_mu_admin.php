<?php

/**
 * CommentPress Core Multisite Admin Class.
 *
 * This class is a wrapper for the majority of database operations.
 *
 * @since 3.3
 */
class Commentpress_Multisite_Admin {

	/**
	 * Plugin object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $parent_obj The plugin object.
	 */
	public $parent_obj;

	/**
	 * Options page reference.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $options_page The options page reference.
	 */
	public $options_page;

	/**
	 * Multisite options array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $cpmu_options The multisite options array.
	 */
	public $cpmu_options = [];



	/**
	 * Initialises this object.
	 *
	 * @since 3.3
	 *
	 * @param object $parent_obj a reference to the parent object.
	 */
	public function __construct( $parent_obj = null ) {

		// Store reference to "parent" (calling obj, not OOP parent).
		$this->parent_obj = $parent_obj;

		// Init.
		$this->_init();

	}



	/**
	 * Set up all options associated with this object.
	 *
	 * @since 3.3
	 *
	 * @param string $component a component identifier, either 'multisite' or 'buddypress'.
	 */
	public function initialise( $component = 'multisite' ) {

		// We always get a multisite request.
		if ( $component == 'multisite' ) {

			// If we don't have our version option.
			if ( ! $this->option_wpms_exists( 'cpmu_version' ) ) {

				// We're activating: add our options:

				// Add options with default values.
				$this->options_create();

			}

		}

		// If BuddyPress is enabled, we'll get a request for that too.
		if ( $component == 'buddypress' ) {

			// If we don't have one of our BuddyPress options.
			if ( ! $this->option_exists( 'cpmu_bp_force_commentpress' ) ) {

				// We're activating: add our options:

				// Use reset method.
				$this->options_reset( $component );

			}

		}

	}



	/**
	 * Upgrade plugin from 1.0 options to latest set.
	 *
	 * @since 3.3
	 *
	 * @return boolean $result
	 */
	public function upgrade_options() {

		// Init return.
		$result = false;

		// If we have a CommentPress Core install (or we're forcing)
		if ( $this->upgrade_required() ) {

			// Store new version.
			$this->option_wpms_set( 'cpmu_version', COMMENTPRESS_MU_PLUGIN_VERSION );

		}

		// --<
		return $result;
	}



	/**
	 * If needed, destroys all items associated with this object.
	 *
	 * @since 3.3
	 */
	public function destroy() {

		// Delete options.
		$this->options_delete();

	}



	/**
	 * Uninstalls database modifications.
	 *
	 * @since 3.3
	 */
	public function uninstall() {

		// Nothing.

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Public Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Check for plugin upgrade.
	 *
	 * @since 3.3
	 *
	 * @return boolean True if upgrade required, false otherwise.
	 */
	public function upgrade_required() {

		// Get installed version.
		$version = $this->option_wpms_get( 'cpmu_version' );

		// Override if we have an install and it's lower than this one.
		if ( $version !== false AND version_compare( COMMENTPRESS_MU_PLUGIN_VERSION, $version, '>' ) ) {
			return true;
		}

		// Fallback.
		return false;
	}



	/**
	 * Create all plugin options.
	 *
	 * @since 3.3
	 */
	public function options_create() {

		// Init default options.
		$this->cpmu_options = [];

		// Allow plugins to add their own options (we always get options from commentpress_mu).
		$this->cpmu_options = apply_filters( 'cpmu_db_options_get_defaults', $this->cpmu_options );

		// Store options array.
		add_site_option( 'cpmu_options', $this->cpmu_options );

		// Store CommentPress Core Multisite version.
		add_site_option( 'cpmu_version', COMMENTPRESS_MU_PLUGIN_VERSION );

	}



	/**
	 * Delete all plugin options.
	 *
	 * @since 3.3
	 */
	public function options_delete() {

		// Delete CommentPress Core Multisite version.
		delete_site_option( 'cpmu_version' );

		// Delete CommentPress Core Multisite options.
		delete_site_option( 'cpmu_options' );

	}



	/**
	 * Save the settings set by the administrator.
	 *
	 * @since 3.3
	 *
	 * @return boolean True on success, false on failure.
	 */
	public function options_update() {

		// Init result.
		$result = false;

	 	// Was the form submitted?
		if( isset( $_POST['cpmu_submit'] ) ) {

			// Check that we trust the source of the data.
			check_admin_referer( 'cpmu_admin_action', 'cpmu_nonce' );

			// Init vars.
			$cpmu_upgrade = '0';
			$cpmu_reset = '0';
			$cpmu_bp_reset = '0';

			// Get variables.
			extract( $_POST );

			// Did we ask to upgrade CommentPress Core Multisite?
			if ( $cpmu_upgrade == '1' ) {

				// Do upgrade.
				$this->upgrade_options();

				// --<
				return true;

			}

			// Did we ask to reset Multisite?
			if ( $cpmu_reset == '1' ) {

				// Reset Multisite options.
				$this->options_reset( 'multisite' );

			}

			// Did we ask to reset BuddyPress?
			if ( $cpmu_bp_reset == '1' ) {

				// Reset BuddyPress options.
				$this->options_reset( 'buddypress' );

			}

			// Did we ask to reset either?
			if ( $cpmu_reset == '1' OR $cpmu_bp_reset == '1' ) {

				// Kick out.
				return true;

			}

			// Allow other plugins to hook into here.
			do_action( 'cpmu_db_options_update' );

			// Save.
			$this->options_save();

			// Set flag.
			$result = true;

		}

		// --<
		return $result;

	}



	/**
	 * Save options array as WordPress site option.
	 *
	 * @since 3.3
	 *
	 * @return boolean $success True if successful, false otherwise.
	 */
	public function options_save() {

		// Set option.
		return $this->option_wpms_set( 'cpmu_options', $this->cpmu_options );

	}



	/**
	 * Reset options.
	 *
	 * @since 3.3
	 *
	 * @param string $component a component identifier, either 'multisite' or 'buddypress'.
	 */
	public function options_reset( $component = 'multisite' ) {

		// Init default options.
		$options = [];

		// Did we get a multisite request?
		if ( $component == 'multisite' ) {

			// Allow plugins to add their own options.
			$options = apply_filters( 'cpmu_db_options_get_defaults', $options );

		}

		// Did we get a BuddyPress request?
		if ( $component == 'buddypress' ) {

			// Allow plugins to add their own options.
			$options = apply_filters( 'cpmu_db_bp_options_get_defaults', $options );

		}

		// Loop and set.
		foreach( $options AS $option => $value ) {
			$this->option_set( $option, $value );
		}

		// Store it.
		$this->options_save();

	}



	/**
	 * Return existence of a specified option.
	 *
	 * @since 3.3
	 *
	 * @param str $option_name The name of the option.
	 * @return bool True if the option exists, false otherwise.
	 */
	public function option_exists( $option_name = '' ) {

		// Test for null.
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_exists()', 'commentpress-core' ) );
		}

		// Get option with unlikely default.
		return array_key_exists( $option_name, $this->cpmu_options );

	}



	/**
	 * Return a value for a specified option.
	 *
	 * @since 3.3
	 *
	 * @param str $option_name The name of the option.
	 * @param mixed $default The default value for the option.
	 * @return mixed The value of the option if it exists, $default otherwise.
	 */
	public function option_get( $option_name = '', $default = false ) {

		// Test for null.
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_get()', 'commentpress-core' ) );
		}

		// Get option.
		return ( array_key_exists( $option_name, $this->cpmu_options ) ) ? $this->cpmu_options[$option_name] : $default;

	}



	/**
	 * Sets a value for a specified option.
	 *
	 * @since 3.3
	 *
	 * @param str $option_name The name of the option.
	 * @param mixed $value The value for the option.
	 */
	public function option_set( $option_name = '', $value = '' ) {

		// Test for null.
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_set()', 'commentpress-core' ) );
		}

		// Test for other than string.
		if ( ! is_string( $option_name ) ) {
			die( __( 'You must supply the option as a string to option_set()', 'commentpress-core' ) );
		}

		// Set option.
		$this->cpmu_options[$option_name] = $value;

	}



	/**
	 * Deletes a specified option.
	 *
	 * @since 3.3
	 *
	 * @param str $option_name The name of the option.
	 */
	public function option_delete( $option_name = '' ) {

		// Test for null.
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_delete()', 'commentpress-core' ) );
		}

		// Unset option.
		unset( $this->cpmu_options[$option_name] );

	}



	/**
	 * Return existence of a specified site option.
	 *
	 * @since 3.3
	 *
	 * @param str $option_name The name of the option.
	 * @return bool True if option exists, false otherwise.
	 */
	public function option_wpms_exists( $option_name = '' ) {

		// Test for null.
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_wpms_exists()', 'commentpress-core' ) );
		}

		// Get option with unlikely default.
		if ( $this->option_wpms_get( $option_name, 'fenfgehgejgrkj' ) == 'fenfgehgejgrkj' ) {
			return false;
		} else {
			return true;
		}

	}



	/**
	 * Return a value for a specified site option.
	 *
	 * @since 3.3
	 *
	 * @param str $option_name The name of the option.
	 * @param mixed $default The default value for the option.
	 * @return mixed The value of the option if it exists, $default otherwise.
	 */
	public function option_wpms_get( $option_name = '', $default = false ) {

		// Test for null.
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_wpms_get()', 'commentpress-core' ) );
		}

		// Get option.
		return get_site_option( $option_name, $default );

	}



	/**
	 * Sets a value for a specified site option.
	 *
	 * @since 3.3
	 *
	 * @param str $option_name The name of the option.
	 * @param mixed $value The value for the option.
	 */
	public function option_wpms_set( $option_name = '', $value = '' ) {

		// Test for null.
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_wpms_set()', 'commentpress-core' ) );
		}

		// Set option.
		return update_site_option( $option_name, $value );

	}



	/**
	 * CommentPress Core initialisation.
	 *
	 * @since 3.3
	 *
	 * @param str $context The initialisation context.
	 */
	public function install_commentpress( $context = 'new_blog' ) {

		// Activate core.
		commentpress_activate_core();

		// Access globals.
		global $commentpress_core;

		// Run activation hook.
		$commentpress_core->activate();

		// Activate AJAX.
		commentpress_activate_ajax();

		/*
		------------------------------------------------------------------------
		Configure CommentPress Core based on admin page settings
		------------------------------------------------------------------------
		*/

		// TODO: create admin page settings.

		// TOC = posts
		//$commentpress_core->db->option_set( 'cp_show_posts_or_pages_in_toc', 'post' );

		// TOC show extended posts
		//$commentpress_core->db->option_set( 'cp_show_extended_toc', 1 );

		/*
		------------------------------------------------------------------------
		Further CommentPress plugins may define Blog Workflows and Type and
		enable them to be set in the blog signup form.
		------------------------------------------------------------------------
		*/

		// If we're installing from the wpmu_new_blog filter, then we need to grab
		// the extra options below - but if we're installing any other way, we need
		// to ignore these, as they override actual values.

		// Use passed value.
		if ( $context == 'new_blog' ) {

			// Check for (translation) workflow (checkbox).
			if ( isset( $_POST['cp_blog_workflow'] ) ) {

				// Ensure boolean.
				$cp_blog_workflow = ( $_POST['cp_blog_workflow'] == '1' ) ? 1 : 0;

				// Set workflow.
				$commentpress_core->db->option_set( 'cp_blog_workflow', $cp_blog_workflow );

			}

			// Check for blog type (dropdown).
			if ( isset( $_POST['cp_blog_type'] ) ) {

				// Ensure boolean.
				$cp_blog_type = intval( $_POST['cp_blog_type'] );

				// Set blog type.
				$commentpress_core->db->option_set( 'cp_blog_type', $cp_blog_type );

			}

			// Save.
			$commentpress_core->db->options_save();

		}

		// Broadcast.
		do_action( 'commentpress_core_soft_installed' );

		/*
		------------------------------------------------------------------------
		Set WordPress Internal Configuration.
		------------------------------------------------------------------------
		*/

		/*
		// Allow anonymous commenting (may be overridden).
		$anon_comments = 0;

		// Allow plugin overrides.
		$anon_comments = apply_filters( 'cp_require_comment_registration', $anon_comments );

		// Update wp option.
		update_option( 'comment_registration', $anon_comments );

		// Add Lorem Ipsum to "Sample Page" if the Network setting is empty?
		$first_page = get_site_option( 'first_page' );

		// Is it empty?
		if ( $first_page == '' ) {

			// Get it & update content, or perhaps delete?

		}
		*/

	}



	/**
	 * CommentPress Core deactivation.
	 *
	 * @since 3.3
	 */
	public function uninstall_commentpress() {

		// Activate core.
		commentpress_activate_core();

		// Access globals.
		global $commentpress_core;

		// Run deactivation hook.
		$commentpress_core->deactivate();

		// Broadcast.
		do_action( 'commentpress_core_soft_uninstalled' );

		/*
		------------------------------------------------------------------------
		Reset WordPress Internal Configuration.
		------------------------------------------------------------------------
		*/

		// Reset any options set in install_commentpress().

	}



	/**
	 * Get workflow form data.
	 *
	 * @since 3.3
	 *
	 * @return array $return Keyed array of form data.
	 */
	public function get_workflow_data() {

		// Init.
		$return = [];

		// Off by default.
		$has_workflow = false;

		// Init output.
		$workflow_html = '';

		// Allow overrides.
		$has_workflow = apply_filters( 'cp_blog_workflow_exists', $has_workflow );

		// If we have workflow enabled, by a plugin, say.
		if ( $has_workflow !== false ) {

			// Define workflow label.
			$workflow_label = __( 'Enable Custom Workflow', 'commentpress-core' );

			// Allow overrides.
			$workflow_label = apply_filters( 'cp_blog_workflow_label', $workflow_label );

			// Add to return.
			$return['label'] = $workflow_label;

			// Define form element.
			$workflow_element = '<input type="checkbox" value="1" id="cp_blog_workflow" name="cp_blog_workflow" />';

			// Add to return.
			$return['element'] = $workflow_element;

		}

		// --<
		return $return;

	}



	/**
	 * Get blog type form elements.
	 *
	 * @since 3.3
	 *
	 * @return array $return Keyed array of form data.
	 */
	public function get_blogtype_data() {

		// Init.
		$return = [];

		// Assume no types.
		$types = [];

		// But allow overrides for plugins to supply some.
		$types = apply_filters( 'cp_blog_type_options', $types );

		// If we got any, use them.
		if ( ! empty( $types ) ) {

			// Define blog type label.
			$type_label = __( 'Document Type', 'commentpress-core' );

			// Allow overrides.
			$type_label = apply_filters( 'cp_blog_type_label', $type_label );

			// Add to return.
			$return['label'] = $type_label;

			// Construct options.
			$type_option_list = [];
			$n = 0;
			foreach( $types AS $type ) {
				$type_option_list[] = '<option value="' . $n . '">' . $type . '</option>';
				$n++;
			}
			$type_options = implode( "\n", $type_option_list );

			// Add to return.
			$return['element'] = $type_options;

		}

		// --<
		return $return;

	}



	/**
	 * Check if blog is CommentPress Core-enabled.
	 *
	 * @since 3.3
	 *
	 * @param int $blog_id The ID of the blog to check.
	 * @return bool $core_active True if CommentPress Core-enabled, false otherwise.
	 */
	public function is_commentpress( $blog_id = 0 ) {

		// Init return.
		$core_active = false;

		// Get current blog ID.
		$current_blog_id = get_current_blog_id();

		// If we have a passed value and it's not this blog
		if ( $blog_id !== 0  AND $current_blog_id != $blog_id ) {

			// We need to switch to it.
			switch_to_blog( $blog_id );

			// Flag.
			$switched = true;

		}

		// TODO: checking for special pages seems a fragile way to test for CommentPress Core.

		// Do we have CommentPress Core options?
		if ( get_option( 'commentpress_options', false ) ) {

			// Get them.
			$commentpress_options = get_option( 'commentpress_options' );

			// If we have "special pages", then the plugin must be active on this blog.
			if ( isset( $commentpress_options['cp_special_pages'] ) ) {

				// Set flag.
				$core_active = true;

			}

		}

		// Do we need to switch back?
		if ( isset( $switched ) AND $switched === true ) {

			// Yes, restore.
			restore_current_blog();

		}

		// --<
		return $core_active;

	}



	/**
	 * Appends option to admin menu.
	 *
	 * @since 3.3
	 */
	public function admin_menu() {

		// Check user permissions.
		if ( ! current_user_can('manage_options') ) return;

		// Enable CommentPress Core, if applicable.
		$this->enable_core();

		// Insert item in relevant menu.
		$this->options_page = add_options_page(
			__( 'CommentPress Core Settings', 'commentpress-core' ),
			__( 'CommentPress Core', 'commentpress-core' ),
			'manage_options',
			'commentpress_admin',
			[ $this, 'options_page' ]
		);

		// Add scripts and styles.
		//add_action( 'admin_print_scripts-' . $this->options_page, [ $this, 'admin_js' ] );
		//add_action( 'admin_print_styles-' . $this->options_page, [ $this, 'admin_css' ] );
		//add_action( 'admin_head-' . $this->options_page, [ $this, 'admin_head' ], 50 );

		// Test if we have a existing pre-3.4 CommentPress instance
		if ( commentpress_is_legacy_plugin_active() ) {

			// Access globals.
			global $pagenow;

			// Show on pages other than the CommentPress Core admin page.
			if (
				$pagenow == 'options-general.php' AND
				! empty( $_GET['page'] ) AND
				'commentpress_admin' == $_GET['page']
			) {

				// We're on our admin page.

			} else {

				// Show message.
				add_action( 'admin_notices', [ $this, 'migrate_alert' ] );

			}

		}

	}



	/**
	 * Prints plugin options page.
	 *
	 * @since 3.3
	 */
	public function options_page() {

		// Check user permissions.
		if ( ! current_user_can( 'manage_options' ) ) return;

		// Get our admin options page.
		echo $this->_get_admin_page();

	}



	/**
	 * Utility to add a message to admin pages when migration is required.
	 *
	 * @since 3.3
	 */
	public function migrate_alert() {

		// Check user permissions.
		if ( ! current_user_can( 'manage_options' ) ) return;

		// Show it.
		echo '<div id="message" class="error"><p>' . __( 'CommentPress Core has detected that a previous version of CommentPress is active on this site. Please visit the <a href="options-general.php?page=commentpress_admin">Settings Page</a> to upgrade.', 'commentpress-core' ) . '</p></div>';

	}



	/**
	 * Get deactivation form element.
	 *
	 * @since 3.3
	 *
	 * @return str The HTML for the form element
	 */
	public function get_deactivate_element() {

		// Define HTML.
		return '
		<tr valign="top">
			<th scope="row"><label for="cp_deactivate_commentpress">' . __( 'Deactivate CommentPress Core', 'commentpress-core' ) . '</label></th>
			<td><input id="cp_deactivate_commentpress" name="cp_deactivate_commentpress" value="1" type="checkbox" /></td>
		</tr>
		';

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Private Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Object initialisation.
	 *
	 * @since 3.3
	 */
	private function _init() {

		// Load options array.
		$this->cpmu_options = $this->option_wpms_get( 'cpmu_options', $this->cpmu_options );

		// If we don't have one.
		if ( count( $this->cpmu_options ) == 0 ) {

			// If not in backend.
			if ( ! is_admin() ) {

				// Init upgrade.
				//die( 'CommentPress Core Multisite upgrade required.' );

			}

		}

		/*
		 * Optionally load CommentPress Core.
		 */

		// If we're network-enabled.
		if ( COMMENTPRESS_PLUGIN_CONTEXT == 'mu_sitewide' ) {

			// Is CommentPress Core active on this blog?
			if ( $this->is_commentpress() ) {

				// Activate core.
				commentpress_activate_core();

				// Activate AJAX.
				commentpress_activate_ajax();

				// Modify CommentPress Core settings page.
				add_filter( 'cpmu_deactivate_commentpress_element', [ $this, 'get_deactivate_element' ] );

				// Hook into CommentPress Core settings page result.
				add_action( 'cpmu_deactivate_commentpress', [ $this, 'disable_core' ] );

			} else {

				// Modify admin menu.
				add_action( 'admin_menu', [ $this, 'admin_menu' ] );

			}

		}

	}



	/**
	 * Get the WordPress admin page.
	 *
	 * @since 3.3
	 *
	 * @return string $admin_page The HTML for the admin page.
	 */
	private function _get_admin_page() {

		// Init.
		$admin_page = '';

		// Open div.
		$admin_page .= '<div class="wrap" id="cpmu_admin_wrapper">' . "\n\n";

		// Get our form.
		$admin_page .= $this->_get_admin_form();

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
	private function _get_admin_form() {

		// Sanitise admin page URL.
		$url = $_SERVER['REQUEST_URI'];
		$url_array = explode( '&', $url );
		if ( $url_array ) { $url = $url_array[0]; }

		// Init vars.
		$label = __( 'Activate CommentPress', 'commentpress-core' );
		$submit = __( 'Save Changes', 'commentpress-core' );

		// Test if we have a existing pre-3.4 CommentPress instance.
		if ( commentpress_is_legacy_plugin_active() ) {

			// Override vars.
			$label = __( 'Upgrade to CommentPress Core', 'commentpress-core' );
			$submit = __( 'Upgrade', 'commentpress-core' );

		}

		// Define admin page.
		$admin_page = '
		<h1>' . __( 'CommentPress Core Settings', 'commentpress-core' ) . '</h1>

		<form method="post" action="' . htmlentities( $url . '&updated=true' ) . '">

		' . wp_nonce_field( 'commentpress_admin_action', 'commentpress_nonce', true, false ) . '
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



	/**
	 * Get workflow form elements.
	 *
	 * @since 3.3
	 *
	 * @return str The form HTML.
	 */
	private function _get_workflow() {

		// Init.
		$workflow_html = '';

		// Get data.
		$workflow = $this->get_workflow_data();

		// If we have workflow data.
		if ( ! empty( $workflow ) ) {

			// Show it.
			$workflow_html = '

			<tr valign="top">
				<th scope="row"><label for="cp_blog_workflow">' . $workflow['label'] . '</label></th>
				<td>' . $workflow['element'] . '</td>
			</tr>

			';

		}

		// --<
		return $workflow_html;

	}



	/**
	 * Get blog type form elements.
	 *
	 * @since 3.3
	 *
	 * @return str $type_html The HTML for the form element.
	 */
	private function _get_blogtype() {

		// Init.
		$type_html = '';

		// Get data.
		$type = $this->get_blogtype_data();

		// If we have type data.
		if ( ! empty( $type ) ) {

			// Show it.
			$type_html = '

			<tr valign="top">
				<th scope="row"><label for="cp_blog_type">' . $type['label'] . '</label></th>
				<td><select id="cp_blog_type" name="cp_blog_type">

				' . $type['element'] . '

				</select></td>
			</tr>

			';

		}

		// --<
		return $type_html;

	}



	/**
	 * Enable CommentPress Core.
	 *
	 * @since 3.3
	 */
	public function enable_core() {

	 	// Was the form submitted?
		if( ! isset( $_POST['commentpress_submit'] ) ) return;

		// Check that we trust the source of the data.
		check_admin_referer( 'commentpress_admin_action', 'commentpress_nonce' );

		// Init var.
		$cp_activate_commentpress = 0;

		// Get vars.
		extract( $_POST );

		// Did we ask to activate CommentPress Core?
		if ( $cp_activate_commentpress == '1' ) {

			// Install core, but not from wpmu_new_blog.
			$this->install_commentpress( 'admin_page' );

			// Redirect.
			wp_redirect( $_SERVER['REQUEST_URI'] );

			// --<
			exit();

		}

	}



	/**
	 * Disable CommentPress Core.
	 *
	 * @since 3.3
	 */
	public function disable_core() {

	 	// Was the form submitted?
		if( ! isset( $_POST['commentpress_submit'] ) ) return;

		// Check that we trust the source of the data.
		check_admin_referer( 'commentpress_admin_action', 'commentpress_nonce' );

		// Init var.
		$cp_deactivate_commentpress = 0;

		// Get vars.
		extract( $_POST );

		// Did we ask to deactivate CommentPress Core?
		if ( $cp_deactivate_commentpress == '1' ) {

			// Uninstall core.
			$this->uninstall_commentpress();

			// Redirect.
			wp_redirect( $_SERVER['REQUEST_URI'] );

			// --<
			exit();

		}

		// --<
		return;

	}



} // Class ends.
