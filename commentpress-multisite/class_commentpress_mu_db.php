<?php
/**
 * CommentPress Core Multisite Database class.
 *
 * Handles the majority of database operations in WordPress Multisite contexts.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Multisite Database Class.
 *
 * This class is a wrapper for the majority of database operations.
 *
 * @since 3.3
 */
class CommentPress_Multisite_Database {

	/**
	 * Multisite plugin object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $ms_loader The multisite plugin object.
	 */
	public $ms_loader;

	/**
	 * Multisite options array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $cpmu_options The multisite options array.
	 */
	public $cpmu_options = [];

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
		add_action( 'commentpress/core/multisite/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this obiject.
	 *
	 * @since 3.3
	 */
	public function initialise() {

		// Load options array.
		$this->cpmu_options = $this->option_wpms_get( 'cpmu_options', $this->cpmu_options );

		/*
		// If we don't have one.
		if ( count( $this->cpmu_options ) == 0 ) {
			// Init upgrade if not in backend.
			if ( ! is_admin() ) {
				die( 'CommentPress Core Multisite upgrade required.' );
			}
		}
		*/

		// Maybe enable CommentPress.
		$this->enable_commentpress();

	}

	// -------------------------------------------------------------------------

	/**
	 * Enables CommentPress Core when active on the current site.
	 *
	 * @since 4.0
	 */
	public function enable_commentpress() {

		// Bail if not network-enabled.
		if ( COMMENTPRESS_PLUGIN_CONTEXT !== 'mu_sitewide' ) {
			return;
		}

		// Bail if CommentPress Core is not active on this blog.
		if ( ! $this->is_commentpress() ) {
			return;
		}

		// Activate core plugin.
		commentpress_activate_core();

		// Activate AJAX commenting.
		commentpress_activate_ajax();

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

		// If we have a passed value and it's not this blog.
		if ( $blog_id !== 0 && (int) $current_blog_id !== (int) $blog_id ) {

			// We need to switch to it.
			switch_to_blog( $blog_id );
			$switched = true;

		}

		// TODO: Checking for special pages seems a fragile way to test for CommentPress Core.

		// Do we have CommentPress Core options?
		if ( get_option( 'commentpress_options', false ) ) {

			// Get them.
			$commentpress_options = get_option( 'commentpress_options' );

			// If we have "special pages", then the plugin must be active on this blog.
			if ( isset( $commentpress_options['cp_special_pages'] ) ) {
				$core_active = true;
			}

		}

		// Do we need to switch back?
		if ( isset( $switched ) && $switched === true ) {
			restore_current_blog();
		}

		// --<
		return $core_active;

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
		$commentpress_core = commentpress_activate_core();

		// Run activation hook.
		$commentpress_core->activate();

		// Activate AJAX.
		commentpress_activate_ajax();

		/*
		------------------------------------------------------------------------
		Configure CommentPress Core based on admin page settings
		------------------------------------------------------------------------
		*/

		// TODO: Create admin page settings.

		/*
		// TOC = posts.
		$commentpress_core->db->option_set( 'cp_show_posts_or_pages_in_toc', 'post' );

		// TOC show extended posts.
		$commentpress_core->db->option_set( 'cp_show_extended_toc', 1 );
		*/

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

		/**
		 * Fires when multisite has "soft installed" core.
		 *
		 * @since 3.3
		 * @since 4.0 Added context param.
		 *
		 * @param str $context The initialisation context.
		 */
		do_action( 'commentpress_core_soft_installed', $context );

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
		$commentpress_core = commentpress_activate_core();

		// Run deactivation hook.
		$commentpress_core->deactivate();

		/**
		 * Fires when multisite has "soft uninstalled" core.
		 *
		 * @since 3.3
		 */
		do_action( 'commentpress_core_soft_uninstalled' );

		/*
		------------------------------------------------------------------------
		Reset WordPress Internal Configuration.
		------------------------------------------------------------------------
		*/

		// Reset any options set in install_commentpress().

	}

	// -------------------------------------------------------------------------

	/**
	 * Check for upgrade.
	 *
	 * @since 3.3
	 *
	 * @return boolean True if upgrade required, false otherwise.
	 */
	public function upgrade_required() {

		// Get installed version.
		$version = $this->option_wpms_get( 'cpmu_version' );

		// Override if we have an install and it's lower than this one.
		if ( $version !== false && version_compare( COMMENTPRESS_MU_PLUGIN_VERSION, $version, '>' ) ) {
			return true;
		}

		// Fallback.
		return false;

	}

	/**
	 * Upgrade plugin from 1.0 options to latest set.
	 *
	 * @since 3.3
	 */
	public function upgrade_options() {

		// If we have a CommentPress Core install - or we're forcing.
		if ( ! $this->upgrade_required() ) {
			return;
		}

		// Store new version.
		$this->option_wpms_set( 'cpmu_version', COMMENTPRESS_MU_PLUGIN_VERSION );

	}

	// -------------------------------------------------------------------------

	/**
	 * Set up all options associated with this object.
	 *
	 * @since 3.3
	 *
	 * @param string $component a component identifier, either 'multisite' or 'buddypress'.
	 */
	public function options_initialise( $component = 'multisite' ) {

		// We always get a multisite request.
		if ( $component == 'multisite' ) {

			// If we don't have our version option.
			if ( ! $this->option_wpms_exists( 'cpmu_version' ) ) {

				// We're activating: add our options.

				// Add options with default values.
				$this->options_create();

			}

		}

		// If BuddyPress is enabled, we'll get a request for that too.
		if ( $component == 'buddypress' ) {

			// If we don't have one of our BuddyPress options.
			if ( ! $this->option_exists( 'cpmu_bp_force_commentpress' ) ) {

				// We're activating: add our options.

				// Use reset method.
				$this->options_reset( $component );

			}

		}

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

		// Bail if our form was not submitted.
		if ( ! isset( $_POST['cpmu_submit'] ) ) {
			return $result;
		}

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

		// Bail if we asked to reset either of the above.
		if ( $cpmu_reset == '1' || $cpmu_bp_reset == '1' ) {
			return true;
		}

		/**
		 * Allow other plugins to hook in here.
		 *
		 * @since 3.3
		 */
		do_action( 'cpmu_db_options_update' );

		// Save.
		$this->options_save();

		// Set flag.
		$result = true;

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

			/**
			 * Allow plugins to add their own options.
			 *
			 * @since 3.3
			 */
			$options = apply_filters( 'cpmu_db_options_get_defaults', $options );

		}

		// Did we get a BuddyPress request?
		if ( $component == 'buddypress' ) {

			/**
			 * Allow plugins to add their own options.
			 *
			 * @since 3.3
			 */
			$options = apply_filters( 'cpmu_db_bp_options_get_defaults', $options );

		}

		// Loop and set.
		foreach ( $options as $option => $value ) {
			$this->option_set( $option, $value );
		}

		// Store it.
		$this->options_save();

	}

	/**
	 * Returns existence of a specified option.
	 *
	 * @since 3.3
	 *
	 * @param str $option_name The name of the option.
	 * @return bool True if the option exists, false otherwise.
	 */
	public function option_exists( $option_name ) {

		// Check if the option exists in the options array.
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
	public function option_get( $option_name, $default = false ) {

		// Get option.
		return array_key_exists( $option_name, $this->cpmu_options ) ? $this->cpmu_options[ $option_name ] : $default;

	}

	/**
	 * Sets a value for a specified option.
	 *
	 * @since 3.3
	 *
	 * @param str $option_name The name of the option.
	 * @param mixed $value The value for the option.
	 */
	public function option_set( $option_name, $value = '' ) {

		// Set option.
		$this->cpmu_options[ $option_name ] = $value;

	}

	/**
	 * Deletes a specified option.
	 *
	 * @since 3.3
	 *
	 * @param str $option_name The name of the option.
	 */
	public function option_delete( $option_name ) {

		// Unset option.
		unset( $this->cpmu_options[ $option_name ] );

	}

	/**
	 * Return existence of a specified site option.
	 *
	 * @since 3.3
	 *
	 * @param str $option_name The name of the option.
	 * @return bool True if option exists, false otherwise.
	 */
	public function option_wpms_exists( $option_name ) {

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
	 * @return mixed The value of the option if it exists, default otherwise.
	 */
	public function option_wpms_get( $option_name, $default = false ) {

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
	 * @return bool True if the value was updated, false otherwise.
	 */
	public function option_wpms_set( $option_name, $value = '' ) {

		// Set option.
		return update_site_option( $option_name, $value );

	}

	// -------------------------------------------------------------------------

	/**
	 * Get workflow form elements.
	 *
	 * @since 3.3
	 *
	 * @return str The form HTML.
	 */
	private function get_workflow() {

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

	// -------------------------------------------------------------------------

	/**
	 * Get blog type form elements.
	 *
	 * @since 3.3
	 *
	 * @return str $type_html The HTML for the form element.
	 */
	private function get_blogtype() {

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
			foreach ( $types as $type ) {
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

}
