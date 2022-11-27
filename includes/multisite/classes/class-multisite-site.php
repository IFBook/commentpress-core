<?php
/**
 * CommentPress Multisite Single Site class.
 *
 * Handles functionality related to Single Sites in WordPress Multisite.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Multisite Single Site Class.
 *
 * This class provides functionality related to Single Sites in WordPress Multisite.
 *
 * @since 3.3
 */
class CommentPress_Multisite_Site {

	/**
	 * Multisite loader object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $multisite The multisite loader object.
	 */
	public $multisite;

	/**
	 * "Enable CommentPress" settings key.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $key_enable The settings key for the "Enable CommentPress" setting.
	 */
	public $key_enable = 'cpmu_activate_commentpress';

	/**
	 * "Disable CommentPress" settings key.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $key_enable The settings key for the "Disable CommentPress" setting.
	 */
	public $key_disable = 'cpmu_activate_commentpress';

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
	 * Set up all items associated with this object.
	 *
	 * @since 3.3
	 */
	public function initialise() {

		// Maybe initialise CommentPress.
		$this->core_initialise();

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.3
	 */
	public function register_hooks() {

		// Save data from Network Settings form submissions.
		add_action( 'commentpress/multisite/settings/site/save/before', [ $this, 'settings_save' ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Saves the data from the "CommentPress Settings" screen.
	 *
	 * @see CommentPress_Multisite_Settings_Site::form_submitted()
	 *
	 * @since 4.0
	 */
	public function settings_save() {

		// Get the posted setting.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$activate = isset( $_POST[ $this->key_enable ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_enable ] ) ) : '0';

		// Bail if we did not ask to activate CommentPress Core.
		if ( $activate !== '1' ) {
			return;
		}

		// Install core, but not from "wpmu_new_blog".
		$this->core_install( 'admin_page' );

	}

	/**
	 * Saves the data from the core "CommentPress Settings" screen.
	 *
	 * @see CommentPress_Multisite_Settings_Site::form_core_submitted()
	 *
	 * @since 4.0
	 */
	public function settings_core_save() {

		// Get the posted setting.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$deactivate = isset( $_POST[ $this->key_disable ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_disable ] ) ) : '0';

		// Bail if we did not ask to deactivate CommentPress Core.
		if ( $deactivate !== '1' ) {
			return;
		}

		// Uninstall core.
		$this->core_uninstall();

		// Do form redirection.
		do_action( 'commentpress/multisite/settings/site/core/redirect' );

	}

	// -------------------------------------------------------------------------

	/**
	 * Initialises CommentPress Core when it is enabled on the current Site.
	 *
	 * @since 4.0
	 */
	public function core_initialise() {

		// Bail if not network-enabled.
		if ( $this->multisite->plugin->plugin_context !== 'mu_sitewide' ) {
			return;
		}

		// Bail if CommentPress Core is not active on this Site.
		if ( ! $this->is_commentpress() ) {
			return;
		}

		// Initialise core.
		$this->multisite->plugin->core_initialise();

	}

	/**
	 * CommentPress Core installation.
	 *
	 * @since 3.3
	 *
	 * @param str $context The installation context.
	 */
	public function core_install( $context = 'new_blog' ) {

		// Initialise core plugin.
		$core = $this->multisite->plugin->core_initialise();

		// Run activation hook.
		$core->activate();

		/*
		------------------------------------------------------------------------
		Configure CommentPress Core based on Admin Page settings
		------------------------------------------------------------------------
		*/

		// TODO: Create Admin Page settings.

		/*
		// TOC = Posts.
		$core->db->setting_set( 'cp_show_posts_or_pages_in_toc', 'post' );

		// TOC show extended Posts.
		$core->db->setting_set( 'cp_show_extended_toc', 1 );
		*/

		/*
		------------------------------------------------------------------------
		Further CommentPress plugins may define Blog Workflows and Type and
		enable them to be set in the Blog signup form.
		------------------------------------------------------------------------
		*/

		// If we're installing from the "wpmu_new_blog" action, then we need to grab
		// the extra options below - but if we're installing any other way, we need
		// to ignore these, as they override actual values.

		// TODO: Move to bp class and use action below.

		// Use passed value.
		if ( $context == 'new_blog' ) {

			// Check for Blog Type (dropdown).
			if ( isset( $_POST['cp_blog_type'] ) ) {

				// Ensure boolean.
				$cp_blog_type = intval( $_POST['cp_blog_type'] );

				// Set Blog Type.
				$core->db->setting_set( 'cp_blog_type', $cp_blog_type );

			}

			// Save.
			$core->db->settings_save();

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
	public function core_uninstall() {

		// Activate core plugin.
		$core = $this->multisite->plugin->core_initialise();

		// Run deactivation hook.
		$core->deactivate();

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

		// Reset any options set in core_install().

	}

	// -------------------------------------------------------------------------

	/**
	 * Check if Blog is CommentPress Core-enabled.
	 *
	 * @since 3.3
	 *
	 * @param int $blog_id The ID of the Blog to check.
	 * @return bool $core_active True if CommentPress Core-enabled, false otherwise.
	 */
	public function is_commentpress( $blog_id = 0 ) {

		// Init return.
		$core_active = false;

		// Get current Blog ID.
		$current_blog_id = get_current_blog_id();

		// If we have a passed value and it's not this Blog.
		if ( $blog_id !== 0 && (int) $current_blog_id !== (int) $blog_id ) {

			// We need to switch to it.
			switch_to_blog( $blog_id );
			$switched = true;

		}

		// TODO: Checking for Special Pages seems a fragile way to test for CommentPress Core.

		// Do we have CommentPress Core options?
		if ( get_option( 'commentpress_options', false ) ) {

			// Get them.
			$commentpress_options = get_option( 'commentpress_options' );

			// If we have "Special Pages", then the plugin must be active on this Blog.
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

}
