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
	public $key_disable = 'cpmu_deactivate_commentpress';

	/**
	 * Parts template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $parts_path Relative path to the Parts directory.
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

		// Inject form element into the "Danger Zone" metabox on core "Site Settings" screen.
		add_action( 'commentpress/core/settings/site/metabox/danger/after', [ $this, 'settings_meta_box_part_get' ] );

		// Act early on core Site Settings "CommentPress Settings" screen form submissions.
		add_action( 'commentpress/core/settings/site/save/before', [ $this, 'settings_meta_box_part_save' ], 1 );

		// Enable HTML Comments and Content for Authors.
		add_action( 'init', [ $this, 'html_content_allow' ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Initialises CommentPress Core when it is enabled on the current Site.
	 *
	 * @since 4.0
	 */
	public function core_initialise() {

		// Bail if not network-enabled.
		if ( 'mu_sitewide' !== $this->multisite->plugin->plugin_context ) {
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
		Configure CommentPress Core based on "Site Settings" screen.
		------------------------------------------------------------------------
		*/

		// TODO: Create Admin Page settings.

		/*
		// TOC = Posts.
		$core->nav->setting_post_type_set( 'post' );

		// TOC show extended Posts.
		$core->nav->setting_subpages_set( 1 );
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
	 * Checks if the current Blog is registered as CommentPress-enabled.
	 *
	 * @since 3.3
	 *
	 * @param int  $blog_id The ID of the Blog to check.
	 * @param bool $legacy_check Whether to switch to the Blog to perform legacy check. Default true.
	 * @return bool True if CommentPress-enabled, false otherwise.
	 */
	public function is_commentpress( $blog_id = false, $legacy_check = true ) {

		// Get current Site ID if the Blog ID isn't passed.
		if ( false === $blog_id ) {
			$blog_id = get_current_blog_id();
		}

		// Bail if we still don't have one.
		if ( empty( $blog_id ) ) {
			return false;
		}

		// Try to get the active Site IDs.
		$site_ids = $this->multisite->sites->core_site_ids_get();

		// Is this Site active?
		if ( ! empty( $site_ids ) && in_array( $blog_id, $site_ids ) ) {
			return true;
		}

		// Bail if legacy check has been skipped.
		if ( true !== $legacy_check ) {
			return false;
		}

		// If we fail to find one, then this might be an upgrade.
		// Let's use our legacy method for checking.
		$core_active = $this->has_commentpress( $blog_id );

		// --<
		return $core_active;

	}

	/**
	 * Checks if the current Blog has CommentPress Core data.
	 *
	 * @since 4.0
	 *
	 * @param int $blog_id The ID of the Blog to check.
	 * @return bool $core_active True if CommentPress-enabled, false otherwise.
	 */
	public function has_commentpress( $blog_id = false ) {

		// Assume not active.
		$core_active = false;

		// Get current Site ID if no Blog ID is passed in.
		if ( false === $blog_id ) {
			$blog_id = get_current_blog_id();
		}

		// Get current Blog ID.
		$current_blog_id = get_current_blog_id();

		// If we have a passed value and it's not this Blog.
		if ( ! empty( $blog_id ) && (int) $current_blog_id !== (int) $blog_id ) {

			// We need to switch to it.
			switch_to_blog( $blog_id );
			$switched = true;

		}

		// TODO: Checking for Special Pages seems a fragile way to test for CommentPress Core.

		// Try to get the CommentPress Core options.
		$commentpress_options = get_option( 'commentpress_options', false );

		// If we have "Special Pages", then the plugin must be active on this Blog.
		if ( ! empty( $commentpress_options['cp_special_pages'] ) ) {
			$core_active = true;
		}

		// Do we need to switch back?
		if ( isset( $switched ) && true === $switched ) {
			restore_current_blog();
		}

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
		if ( '1' !== $activate ) {
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
	public function settings_meta_box_part_get() {

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-site-settings-core.php';

	}

	/**
	 * Saves the data from the core "CommentPress Settings" screen.
	 *
	 * @see CommentPress_Core_Settings_Site::form_submitted()
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_part_save() {

		// Get the posted setting.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$deactivate = isset( $_POST[ $this->key_disable ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_disable ] ) ) : '0';

		// Bail if we did not ask to deactivate CommentPress Core.
		if ( '1' !== $deactivate ) {
			return;
		}

		// Deactivate core.
		$this->core_deactivate();

		// Do core "CommentPress Settings" form redirection.
		do_action( 'commentpress/core/settings/site/form/redirect' );

	}

	// -------------------------------------------------------------------------

	/**
	 * Allows HTML Comments and Content on CommentPress-enabled Sites.
	 *
	 * @since 3.3
	 */
	public function html_content_allow() {

		// Bail if CommentPress Core is not active on this Site.
		if ( ! $this->is_commentpress() ) {
			return;
		}

		// Using "publish_posts" for now - means author+.
		if ( ! current_user_can( 'publish_posts' ) ) {
			return;
		}

		/*
		 * Remove html filtering on content.
		 *
		 * NOTE: This has possible consequences.
		 *
		 * @see https://wordpress.org/plugins/unfiltered-mu/
		 */
		kses_remove_filters();

	}

}
