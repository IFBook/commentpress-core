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
	 * Parts template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $metabox_path Relative path to the Parts directory.
	 */
	private $parts_path = 'includes/multisite/assets/templates/wordpress/parts/';

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

		// Add our option to the Site Settings "CommentPress Settings" Activation metabox.
		add_action( 'commentpress/multisite/settings/site/metabox/activate/after', [ $this, 'metabox_settings_get' ] );

		// Save data from multisite Site Settings "CommentPress Settings" screen form submissions.
		add_action( 'commentpress/multisite/settings/site/save/before', [ $this, 'settings_save' ] );

		// Add our option to the Site Settings "CommentPress Settings" Danger Zone metabox.
		add_action( 'commentpress/multisite/settings/site/metabox/danger/after', [ $this, 'metabox_settings_core_get' ] );

		// Save data from multisite Site Settings "CommentPress Settings" screen form submissions.
		add_action( 'commentpress/multisite/settings/site/core/save/before', [ $this, 'settings_core_save' ] );

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
	 * CommentPress Core activation.
	 *
	 * @since 3.3
	 */
	public function core_activate() {

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'context' => $context,
			//'backtrace' => $trace,
		], true ) );
		*/

		// Initialise core.
		$core = $this->multisite->plugin->core_initialise();

		// Get the current Site ID.
		$site_id = get_current_blog_id();

		/**
		 * Fires before multisite has "soft activated" core.
		 *
		 * @since 4.0
		 *
		 * @param int $site_id The current Site ID.
		 */
		do_action( 'commentpress/multisite/site/core/activated/before', $site_id );

		// Run core activation hook.
		do_action( 'commentpress/core/activate', $network_wide = false );

		/**
		 * Fires after multisite has "soft activated" core.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Multisite_Sites::site_id_add() (Priority: 10)
		 *
		 * @since 4.0
		 *
		 * @param int $site_id The current Site ID.
		 */
		do_action( 'commentpress/multisite/site/core/activated/after', $site_id );

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
	public function core_deactivate() {

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			//'backtrace' => $trace,
		], true ) );
		*/

		// Get the current Site ID.
		$site_id = get_current_blog_id();

		/**
		 * Fires before multisite has "soft deactivated" core.
		 *
		 * @since 4.0
		 *
		 * @param int $site_id The current Site ID.
		 */
		do_action( 'commentpress/multisite/site/core/deactivated/before', $site_id );

		// Run core deactivation hook.
		do_action( 'commentpress/core/deactivate', $network_wide = false );

		/**
		 * Fires after multisite has "soft deactivated" core.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Multisite_Sites::site_id_remove() (Priority: 10)
		 *
		 * @since 4.0
		 *
		 * @param int $site_id The current Site ID.
		 */
		do_action( 'commentpress/multisite/site/core/deactivated/after', $site_id );

		/*
		------------------------------------------------------------------------
		Reset WordPress Internal Configuration.
		------------------------------------------------------------------------
		*/

		// Reset any options set in core_activate().

	}

	// -------------------------------------------------------------------------

	/**
	 * Checks if the current Blog is CommentPress Core-enabled.
	 *
	 * @since 3.3
	 *
	 * @param int $blog_id The ID of the Blog to check.
	 * @return bool $core_active True if CommentPress Core-enabled, false otherwise.
	 */
	public function is_commentpress( $blog_id = false ) {

		// Init return.
		$core_active = false;

		// Get current Site ID if the Blog ID isn't passed.
		if ( $blog_id === false ) {
			$blog_id = get_current_blog_id();
		}

		// Bail if we still don't have one.
		if ( empty( $blog_id ) ) {
			return $core_active;
		}

		// Try to get the active Site IDs.
		$site_ids = $this->multisite->sites->core_site_ids_get();
		if ( empty( $site_ids ) ) {
			return $core_active;
		}

		// Is this Site active?
		if ( in_array( $blog_id, $site_ids ) ) {
			$core_active = true;
		}

		/*
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
		*/

		// --<
		return $core_active;

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our settings to the multisite Site Settings "Activation" metabox.
	 *
	 * @since 4.0
	 */
	public function metabox_settings_get() {

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-site-settings-site.php';

	}

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

		// Activate core.
		$this->core_activate();

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our settings to the multisite Site Settings "Activation" metabox.
	 *
	 * @since 4.0
	 */
	public function metabox_settings_core_get() {

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-site-settings-core.php';

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

		// Deactivate core.
		$this->core_deactivate();

		// Do form redirection.
		do_action( 'commentpress/multisite/settings/site/core/redirect' );

	}

}
