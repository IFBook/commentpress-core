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
	 * @var object $parent_obj The plugin object
	 */
	public $parent_obj;

	/**
	 * Options page reference.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $options_page The options page reference
	 */
	public $options_page;

	/**
	 * Multisite options array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $cpmu_options The multisite options array
	 */
	public $cpmu_options = array();



	/**
	 * Initialises this object.
	 *
	 * @since 3.3
	 *
	 * @param object $parent_obj a reference to the parent object
	 */
	function __construct( $parent_obj = null ) {

		// store reference to "parent" (calling obj, not OOP parent)
		$this->parent_obj = $parent_obj;

		// init
		$this->_init();

	}



	/**
	 * Set up all options associated with this object.
	 *
	 * @param string $component a component identifier, either 'multisite' or 'buddypress'
	 * @return void
	 */
	public function initialise( $component = 'multisite' ) {

		// we always get a multisite request
		if ( $component == 'multisite' ) {

			// if we don't have our version option
			if ( ! $this->option_wpms_exists( 'cpmu_version' ) ) {

				// we're activating: add our options:

				// add options with default values
				$this->options_create();

			}

		}

		// if BuddyPress is enabled, we'll get a request for that too
		if ( $component == 'buddypress' ) {

			// if we don't have one of our BuddyPress options
			if ( ! $this->option_exists( 'cpmu_bp_force_commentpress' ) ) {

				// we're activating: add our options:

				// use reset method
				$this->options_reset( $component );

			}

		}

	}



	/**
	 * Upgrade plugin from 1.0 options to latest set.
	 *
	 * @return boolean $result
	 */
	public function upgrade_options() {

		// init return
		$result = false;

		// if we have a CommentPress Core install (or we're forcing)
		if ( $this->upgrade_required() ) {

			// store new version
			$this->option_wpms_set( 'cpmu_version', COMMENTPRESS_MU_PLUGIN_VERSION );

		}

		// --<
		return $result;
	}



	/**
	 * If needed, destroys all items associated with this object.
	 *
	 * @return void
	 */
	public function destroy() {

		// delete options
		$this->options_delete();

	}



	/**
	 * Uninstalls database modifications.
	 *
	 * @return void
	 */
	public function uninstall() {

		// nothing

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
	 * @return boolean True if upgrade required, false otherwise
	 */
	public function upgrade_required() {

		// get installed version
		$version = $this->option_wpms_get( 'cpmu_version' );

		// override if we have an install and it's lower than this one
		if ( $version !== false AND version_compare( COMMENTPRESS_MU_PLUGIN_VERSION, $version, '>' ) ) {
			return true;
		}

		// fallback
		return false;
	}



	/**
	 * Create all plugin options.
	 *
	 * @return void
	 */
	public function options_create() {

		// init default options
		$this->cpmu_options = array();

		// allow plugins to add their own options (we always get options from commentpress_mu)
		$this->cpmu_options = apply_filters( 'cpmu_db_options_get_defaults', $this->cpmu_options );

		// store options array
		add_site_option( 'cpmu_options', $this->cpmu_options );

		// store CommentPress Core Multisite version
		add_site_option( 'cpmu_version', COMMENTPRESS_MU_PLUGIN_VERSION );

	}



	/**
	 * Delete all plugin options.
	 *
	 * @return void
	 */
	public function options_delete() {

		// delete CommentPress Core Multisite version
		delete_site_option( 'cpmu_version' );

		// delete CommentPress Core Multisite options
		delete_site_option( 'cpmu_options' );

	}



	/**
	 * Save the settings set by the administrator.
	 *
	 * @return boolean True on success, false on failure
	 */
	public function options_update() {

		// init result
		$result = false;

	 	// was the form submitted?
		if( isset( $_POST['cpmu_submit'] ) ) {

			// check that we trust the source of the data
			check_admin_referer( 'cpmu_admin_action', 'cpmu_nonce' );

			// init vars
			$cpmu_upgrade = '0';
			$cpmu_reset = '0';
			$cpmu_bp_reset = '0';

			// get variables
			extract( $_POST );

			// did we ask to upgrade CommentPress Core Multisite?
			if ( $cpmu_upgrade == '1' ) {

				// do upgrade
				$this->upgrade_options();

				// --<
				return true;

			}

			// did we ask to reset Multisite?
			if ( $cpmu_reset == '1' ) {

				// reset Multisite options
				$this->options_reset( 'multisite' );

			}

			// did we ask to reset BuddyPress?
			if ( $cpmu_bp_reset == '1' ) {

				// reset BuddyPress options
				$this->options_reset( 'buddypress' );

			}

			// did we ask to reset either?
			if ( $cpmu_reset == '1' OR $cpmu_bp_reset == '1' ) {

				// kick out
				return true;

			}

			// allow other plugins to hook into here
			do_action( 'cpmu_db_options_update' );

			// save
			$this->options_save();

			// set flag
			$result = true;

		}

		// --<
		return $result;

	}



	/**
	 * Save options array as WordPress site option.
	 *
	 * @return boolean $success True if successful, false otherwise
	 */
	public function options_save() {

		// set option
		return $this->option_wpms_set( 'cpmu_options', $this->cpmu_options );

	}



	/**
	 * Reset options.
	 *
	 * @param string $component a component identifier, either 'multisite' or 'buddypress'
	 * @return void
	 */
	public function options_reset( $component = 'multisite' ) {

		// init default options
		$options = array();

		// did we get a multisite request?
		if ( $component == 'multisite' ) {

			// allow plugins to add their own options
			$options = apply_filters( 'cpmu_db_options_get_defaults', $options );

		}

		// did we get a BuddyPress request?
		if ( $component == 'buddypress' ) {

			// allow plugins to add their own options
			$options = apply_filters( 'cpmu_db_bp_options_get_defaults', $options );

		}

		// loop and set
		foreach( $options AS $option => $value ) {
			$this->option_set( $option, $value );
		}

		// store it
		$this->options_save();

	}



	/**
	 * Return existence of a specified option.
	 *
	 * @param str $option_name The name of the option
	 * @return bool True if the option exists, false otherwise
	 */
	public function option_exists( $option_name = '' ) {

		// test for null
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_exists()', 'commentpress-core' ) );
		}

		// get option with unlikely default
		return array_key_exists( $option_name, $this->cpmu_options );

	}



	/**
	 * Return a value for a specified option.
	 *
	 * @param str $option_name The name of the option
	 * @param mixed $default The default value for the option
	 * @return mixed The value of the option if it exists, $default otherwise
	 */
	public function option_get( $option_name = '', $default = false ) {

		// test for null
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_get()', 'commentpress-core' ) );
		}

		// get option
		return ( array_key_exists( $option_name, $this->cpmu_options ) ) ? $this->cpmu_options[$option_name] : $default;

	}



	/**
	 * Sets a value for a specified option.
	 *
	 * @param str $option_name The name of the option
	 * @param mixed $value The value for the option
	 * @return void
	 */
	public function option_set( $option_name = '', $value = '' ) {

		// test for null
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_set()', 'commentpress-core' ) );
		}

		// test for other than string
		if ( ! is_string( $option_name ) ) {
			die( __( 'You must supply the option as a string to option_set()', 'commentpress-core' ) );
		}

		// set option
		$this->cpmu_options[$option_name] = $value;

	}



	/**
	 * Deletes a specified option.
	 *
	 * @param str $option_name The name of the option
	 * @return void
	 */
	public function option_delete( $option_name = '' ) {

		// test for null
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_delete()', 'commentpress-core' ) );
		}

		// unset option
		unset( $this->cpmu_options[$option_name] );

	}



	/**
	 * Return existence of a specified site option.
	 *
	 * @param str $option_name The name of the option
	 * @return bool True if option exists, false otherwise
	 */
	public function option_wpms_exists( $option_name = '' ) {

		// test for null
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_wpms_exists()', 'commentpress-core' ) );
		}

		// get option with unlikely default
		if ( $this->option_wpms_get( $option_name, 'fenfgehgejgrkj' ) == 'fenfgehgejgrkj' ) {
			return false;
		} else {
			return true;
		}

	}



	/**
	 * Return a value for a specified site option.
	 *
	 * @param str $option_name The name of the option
	 * @param mixed $default The default value for the option
	 * @return mixed The value of the option if it exists, $default otherwise
	 */
	public function option_wpms_get( $option_name = '', $default = false ) {

		// test for null
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_wpms_get()', 'commentpress-core' ) );
		}

		// get option
		return get_site_option( $option_name, $default );

	}



	/**
	 * Sets a value for a specified site option.
	 *
	 * @param str $option_name The name of the option
	 * @param mixed $value The value for the option
	 * @return void
	 */
	public function option_wpms_set( $option_name = '', $value = '' ) {

		// test for null
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_wpms_set()', 'commentpress-core' ) );
		}

		// set option
		return update_site_option( $option_name, $value );

	}



	/**
	 * CommentPress Core initialisation.
	 *
	 * @param str $context The initialisation context
	 * @return void
	 */
	public function install_commentpress( $context = 'new_blog' ) {

		// activate core
		commentpress_activate_core();

		// access globals
		global $commentpress_core;

		// run activation hook
		$commentpress_core->activate();

		// activate ajax
		commentpress_activate_ajax();

		/*
		------------------------------------------------------------------------
		Configure CommentPress Core based on admin page settings
		------------------------------------------------------------------------
		*/

		// TODO: create admin page settings

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

		// if we're installing from the wpmu_new_blog filter, then we need to grab
		// the extra options below - but if we're installing any other way, we need
		// to ignore these, as they override actual values

		// use passed value
		if ( $context == 'new_blog' ) {

			// check for (translation) workflow (checkbox)
			if ( isset( $_POST['cp_blog_workflow'] ) ) {

				// ensure boolean
				$cp_blog_workflow = ( $_POST['cp_blog_workflow'] == '1' ) ? 1 : 0;

				// set workflow
				$commentpress_core->db->option_set( 'cp_blog_workflow', $cp_blog_workflow );

			}

			// check for blog type (dropdown)
			if ( isset( $_POST['cp_blog_type'] ) ) {

				// ensure boolean
				$cp_blog_type = intval( $_POST['cp_blog_type'] );

				// set blog type
				$commentpress_core->db->option_set( 'cp_blog_type', $cp_blog_type );

			}

			// save
			$commentpress_core->db->options_save();

		}

		// broadcast
		do_action( 'commentpress_core_soft_installed' );

		/*
		------------------------------------------------------------------------
		Set WordPress Internal Configuration
		------------------------------------------------------------------------
		*/

		/*
		// allow anonymous commenting (may be overridden)
		$anon_comments = 0;

		// allow plugin overrides
		$anon_comments = apply_filters( 'cp_require_comment_registration', $anon_comments );

		// update wp option
		update_option( 'comment_registration', $anon_comments );

		// add Lorem Ipsum to "Sample Page" if the Network setting is empty?
		$first_page = get_site_option( 'first_page' );

		// is it empty?
		if ( $first_page == '' ) {

			// get it & update content, or perhaps delete?

		}
		*/

	}



	/**
	 * CommentPress Core deactivation.
	 *
	 * @return void
	 */
	public function uninstall_commentpress() {

		// activate core
		commentpress_activate_core();

		// access globals
		global $commentpress_core;

		// run deactivation hook
		$commentpress_core->deactivate();

		// broadcast
		do_action( 'commentpress_core_soft_uninstalled' );

		/*
		------------------------------------------------------------------------
		Reset WordPress Internal Configuration
		------------------------------------------------------------------------
		*/

		// reset any options set in install_commentpress()

	}



	/**
	 * Get workflow form data.
	 *
	 * @return array $return Keyed array of form data
	 */
	public function get_workflow_data() {

		// init
		$return = array();

		// off by default
		$has_workflow = false;

		// init output
		$workflow_html = '';

		// allow overrides
		$has_workflow = apply_filters( 'cp_blog_workflow_exists', $has_workflow );

		// if we have workflow enabled, by a plugin, say
		if ( $has_workflow !== false ) {

			// define workflow label
			$workflow_label = __( 'Enable Custom Workflow', 'commentpress-core' );

			// allow overrides
			$workflow_label = apply_filters( 'cp_blog_workflow_label', $workflow_label );

			// add to return
			$return['label'] = $workflow_label;

			// define form element
			$workflow_element = '<input type="checkbox" value="1" id="cp_blog_workflow" name="cp_blog_workflow" />';

			// add to return
			$return['element'] = $workflow_element;

		}

		// --<
		return $return;

	}



	/**
	 * Get blog type form elements.
	 *
	 * @return array $return Keyed array of form data
	 */
	public function get_blogtype_data() {

		// init
		$return = array();

		// assume no types
		$types = array();

		// but allow overrides for plugins to supply some
		$types = apply_filters( 'cp_blog_type_options', $types );

		// if we got any, use them
		if ( ! empty( $types ) ) {

			// define blog type label
			$type_label = __( 'Document Type', 'commentpress-core' );

			// allow overrides
			$type_label = apply_filters( 'cp_blog_type_label', $type_label );

			// add to return
			$return['label'] = $type_label;

			// construct options
			$type_option_list = array();
			$n = 0;
			foreach( $types AS $type ) {
				$type_option_list[] = '<option value="' . $n . '">' . $type . '</option>';
				$n++;
			}
			$type_options = implode( "\n", $type_option_list );

			// add to return
			$return['element'] = $type_options;

		}

		// --<
		return $return;

	}



	/**
	 * Check if blog is CommentPress Core-enabled.
	 *
	 * @param int $blog_id The ID of the blog to check
	 * @return bool $core_active True if CommentPress Core-enabled, false otherwise
	 */
	public function is_commentpress( $blog_id = 0 ) {

		// init return
		$core_active = false;

		// get current blog ID
		$current_blog_id = get_current_blog_id();

		// if we have a passed value and it's not this blog
		if ( $blog_id !== 0  AND $current_blog_id != $blog_id ) {

			// we need to switch to it
			switch_to_blog( $blog_id );

			// flag
			$switched = true;

		}

		// TODO: checking for special pages seems a fragile way to test for CommentPress Core

		// do we have CommentPress Core options?
		if ( get_option( 'commentpress_options', false ) ) {

			// get them
			$commentpress_options = get_option( 'commentpress_options' );

			// if we have "special pages", then the plugin must be active on this blog
			if ( isset( $commentpress_options['cp_special_pages'] ) ) {

				// set flag
				$core_active = true;

			}

		}

		// do we need to switch back?
		if ( isset( $switched ) AND $switched === true ) {

			// yes, restore
			restore_current_blog();

		}

		// --<
		return $core_active;

	}



	/**
	 * Appends option to admin menu.
	 *
	 * @return void
	 */
	public function admin_menu() {

		// check user permissions
		if ( ! current_user_can('manage_options') ) return;

		// enable CommentPress Core, if applicable
		$this->enable_core();

		// insert item in relevant menu
		$this->options_page = add_options_page(
			__( 'CommentPress Core Settings', 'commentpress-core' ),
			__( 'CommentPress Core', 'commentpress-core' ),
			'manage_options',
			'commentpress_admin',
			array( $this, 'options_page' )
		);

		// add scripts and styles
		//add_action( 'admin_print_scripts-' . $this->options_page, array( $this, 'admin_js' ) );
		//add_action( 'admin_print_styles-' . $this->options_page, array( $this, 'admin_css' ) );
		//add_action( 'admin_head-' . $this->options_page, array( $this, 'admin_head' ), 50 );

		// test if we have a existing pre-3.4 CommentPress instance
		if ( commentpress_is_legacy_plugin_active() ) {

			// access globals
			global $pagenow;

			// show on pages other than the CommentPress Core admin page
			if (
				$pagenow == 'options-general.php' AND
				! empty( $_GET['page'] ) AND
				'commentpress_admin' == $_GET['page']
			) {

				// we're on our admin page

			} else {

				// show message
				add_action( 'admin_notices', array( $this, 'migrate_alert' ) );

			}

		}

	}



	/**
	 * Prints plugin options page.
	 *
	 * @return void
	 */
	public function options_page() {

		// check user permissions
		if ( ! current_user_can( 'manage_options' ) ) return;

		// get our admin options page
		echo $this->_get_admin_page();

	}



	/**
	 * Utility to add a message to admin pages when migration is required.
	 *
	 * @return void
	 */
	public function migrate_alert() {

		// check user permissions
		if ( ! current_user_can( 'manage_options' ) ) return;

		// show it
		echo '<div id="message" class="error"><p>' . __( 'CommentPress Core has detected that a previous version of CommentPress is active on this site. Please visit the <a href="options-general.php?page=commentpress_admin">Settings Page</a> to upgrade.', 'commentpress-core' ) . '</p></div>';

	}



	/**
	 * Get deactivation form element.
	 *
	 * @return str The HTML for the form element
	 */
	public function get_deactivate_element() {

		// define html
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
	 * @return void
	 */
	private function _init() {

		// load options array
		$this->cpmu_options = $this->option_wpms_get( 'cpmu_options', $this->cpmu_options );

		// if we don't have one
		if ( count( $this->cpmu_options ) == 0 ) {

			// if not in backend
			if ( ! is_admin() ) {

				// init upgrade
				//die( 'CommentPress Core Multisite upgrade required.' );

			}

		}

		/**
		 * Optionally load CommentPress Core
		 */

		// if we're network-enabled
		if ( COMMENTPRESS_PLUGIN_CONTEXT == 'mu_sitewide' ) {

			// is CommentPress Core active on this blog?
			if ( $this->is_commentpress() ) {

				// activate core
				commentpress_activate_core();

				// activate ajax
				commentpress_activate_ajax();

				// modify CommentPress Core settings page
				add_filter( 'cpmu_deactivate_commentpress_element', array( $this, 'get_deactivate_element' ) );

				// hook into CommentPress Core settings page result
				add_action( 'cpmu_deactivate_commentpress', array( $this, 'disable_core' ) );

			} else {

				// modify admin menu
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			}

		}

	}



	/**
	 * Get the WordPress admin page.
	 *
	 * @return string $admin_page The HTML for the admin page
	 */
	private function _get_admin_page() {

		// init
		$admin_page = '';

		// open div
		$admin_page .= '<div class="wrap" id="cpmu_admin_wrapper">' . "\n\n";

		// get our form
		$admin_page .= $this->_get_admin_form();

		// close div
		$admin_page .= '</div>' . "\n\n";

		// --<
		return $admin_page;

	}



	/**
	 * Returns the admin form HTML.
	 *
	 * @return string $admin_page The HTML for the admin page
	 */
	private function _get_admin_form() {

		// sanitise admin page url
		$url = $_SERVER['REQUEST_URI'];
		$url_array = explode( '&', $url );
		if ( $url_array ) { $url = $url_array[0]; }

		// init vars
		$label = __( 'Activate CommentPress', 'commentpress-core' );
		$submit = __( 'Save Changes', 'commentpress-core' );

		// test if we have a existing pre-3.4 CommentPress instance
		if ( commentpress_is_legacy_plugin_active() ) {

			// override vars
			$label = __( 'Upgrade to CommentPress Core', 'commentpress-core' );
			$submit = __( 'Upgrade', 'commentpress-core' );

		}

		// define admin page
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
	 * @return str The form HTML
	 */
	private function _get_workflow() {

		// init
		$workflow_html = '';

		// get data
		$workflow = $this->get_workflow_data();

		// if we have workflow data
		if ( ! empty( $workflow ) ) {

			// show it
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
	 * @return str $type_html The HTML for the form element
	 */
	private function _get_blogtype() {

		// init
		$type_html = '';

		// get data
		$type = $this->get_blogtype_data();

		// if we have type data
		if ( ! empty( $type ) ) {

			// show it
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
	 * @return void
	 */
	public function enable_core() {

	 	// was the form submitted?
		if( ! isset( $_POST['commentpress_submit'] ) ) return;

		// check that we trust the source of the data
		check_admin_referer( 'commentpress_admin_action', 'commentpress_nonce' );

		// init var
		$cp_activate_commentpress = 0;

		// get vars
		extract( $_POST );

		// did we ask to activate CommentPress Core?
		if ( $cp_activate_commentpress == '1' ) {

			// install core, but not from wpmu_new_blog
			$this->install_commentpress( 'admin_page' );

			// redirect
			wp_redirect( $_SERVER['REQUEST_URI'] );

			// --<
			exit();

		}

	}



	/**
	 * Disable CommentPress Core.
	 *
	 * @return void
	 */
	public function disable_core() {

	 	// was the form submitted?
		if( ! isset( $_POST['commentpress_submit'] ) ) return;

		// check that we trust the source of the data
		check_admin_referer( 'commentpress_admin_action', 'commentpress_nonce' );

		// init var
		$cp_deactivate_commentpress = 0;

		// get vars
		extract( $_POST );

		// did we ask to deactivate CommentPress Core?
		if ( $cp_deactivate_commentpress == '1' ) {

			// uninstall core
			$this->uninstall_commentpress();

			// redirect
			wp_redirect( $_SERVER['REQUEST_URI'] );

			// --<
			exit();

		}

		// --<
		return;

	}



} // class ends
