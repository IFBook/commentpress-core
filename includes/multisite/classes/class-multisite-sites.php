<?php
/**
 * CommentPress Multisite Sites class.
 *
 * Handles functionality related to Sites in WordPress Multisite.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Multisite Sites Class.
 *
 * This class provides functionality related to Sites in WordPress Multisite.
 *
 * @since 3.3
 */
class CommentPress_Multisite_Sites {

	/**
	 * Multisite loader object.
	 *
	 * @since 3.0
	 * @access public
	 * @var CommentPress_Multisite_Loader
	 */
	public $multisite;

	/**
	 * Relative path to the Parts directory.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $parts_path = 'includes/multisite/assets/templates/wordpress/parts/';

	/**
	 * "CommentPress Sites on the Network" settings key.
	 *
	 * This setting allows us to track the Sites on the Network that have CommentPress Core
	 * enabled on them.
	 *
	 * Each Site where CommentPress Core is active will register its Site ID in this array
	 * whether the plugin is network activated or optionally activated.
	 *
	 * This allows us to retrieve that list of IDs in "uninstall.php" when the plugin is
	 * being deleted. This is, of course, much more efficient than iterating through all
	 * sites on the Network.
	 *
	 * TODO: Upgrade this functionality for WordPress Multi-Network installs once it is
	 * working on vanilla WordPress Multisite installs.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $key_sites = 'commentpress_sites';

	/**
	 * "CommentPress enabled on all Sites" settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $key_forced = 'cpmu_force_commentpress';

	/**
	 * "Default Welcome Page content" settings key.
	 *
	 * Not implemented.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $key_title_page_content = 'cpmu_title_page_content';

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param CommentPress_Multisite_Loader $multisite Reference to the multisite loader object.
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

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.3
	 */
	public function register_hooks() {

		// Acts when multisite is activated.
		add_action( 'commentpress/multisite/activate', [ $this, 'plugin_activate' ], 20 );

		// Act when multisite is deactivated.
		add_action( 'commentpress/multisite/deactivate', [ $this, 'plugin_deactivate' ], 10 );

		// Act when plugin is network-active and core is "soft activated".
		add_action( 'commentpress/multisite/site/core/activated/after', [ $this, 'core_site_id_add' ], 10 );

		// Act when plugin is network-active and core is "soft deactivated".
		add_action( 'commentpress/multisite/site/core/deactivated/after', [ $this, 'core_site_id_remove' ], 10 );

		// Act when plugin is not network-active and core is "optionally activated".
		add_action( 'commentpress/core/activate', [ $this, 'core_site_activated' ], 50 );

		// Act when plugin is not network-active and core is "optionally deactivated".
		add_action( 'commentpress/core/deactivate', [ $this, 'core_site_deactivated' ], 50 );

		// ---------------------------------------------------------------------

		// Add our option to the Network Settings "General Settings" metabox.
		add_action( 'commentpress/multisite/settings/network/metabox/general/after', [ $this, 'metabox_get' ] );

		// Save data from Network Settings form submissions.
		add_action( 'commentpress/multisite/settings/network/save/before', [ $this, 'settings_save' ] );

		// Filter the default multisite settings.
		add_filter( 'commentpress/multisite/settings/defaults', [ $this, 'settings_get_defaults' ] );

		// ---------------------------------------------------------------------

		// Add form elements to Blog Signup Form. Hook late so others can unhook.
		add_action( 'signup_blogform', [ $this, 'site_signup_form_elements_add' ], 50 );

		// Add callback for Signup Page to include Sidebar.
		add_action( 'after_signup_form', [ $this, 'site_signup_form_after' ], 20 );

		// Save meta for Blog Signup Form submissions.
		add_filter( 'signup_site_meta', [ $this, 'site_signup_form_meta_add' ], 10, 1 );

		// Save meta for other Blog Signup Form submissions.
		add_filter( 'add_signup_meta', [ $this, 'site_signup_form_meta_add' ], 10 );

		// Activate Blog-specific CommentPress plugin.
		if ( version_compare( $GLOBALS['wp_version'], '5.1.0', '>=' ) ) {
			add_action( 'wp_initialize_site', [ $this, 'site_initialise' ], 20, 2 );
		} else {
			add_action( 'wpmu_new_blog', [ $this, 'site_initialise_legacy' ], 20, 6 );
		}

		// ---------------------------------------------------------------------

		// Enable CommentPress themes in Multisite optional scenario.
		add_filter( 'site_allowed_themes', [ $this, 'themes_allowed_add' ], 10, 2 );

		// Add filter for reserved core "Special Page" names on subdirectory installs.
		if ( ! is_subdomain_install() ) {
			add_filter( 'subdirectory_reserved_names', [ $this, 'reserved_names_add' ] );
		}

		/*
		// Override Welcome Page content.
		add_filter( 'cp_title_page_content', [ $this, 'title_page_content_get' ] );
		*/

	}

	// -------------------------------------------------------------------------

	/**
	 * Runs when the plugin is activated.
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_activate( $network_wide = false ) {

		// Bail if plugin is not network activated.
		if ( ! $network_wide ) {
			return;
		}

		// Activate all CommentPress-enabled Sites when deactivating.
		$this->core_sites_activate();

	}

	/**
	 *  Runs when the plugin is deactivated.
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_deactivate( $network_wide = false ) {

		// Bail if plugin is not network activated.
		if ( ! $network_wide ) {
			return;
		}

		// Deactivate all CommentPress-enabled Sites when deactivating.
		$this->core_sites_deactivate();

	}

	// -------------------------------------------------------------------------

	/**
	 * Activates all CommentPress-enabled Sites.
	 *
	 * @since 4.0
	 */
	public function core_sites_activate() {

		// Try to get the CommentPress-enabled Site IDs.
		$site_ids = $this->core_site_ids_get();
		if ( empty( $site_ids ) ) {
			return;
		}

		// Remove both "activated" callbacks because we want to keep the setting as is.
		remove_action( 'commentpress/multisite/site/core/activated/after', [ $this, 'core_site_id_add' ], 10 );
		remove_action( 'commentpress/core/activate', [ $this, 'core_site_activated' ], 50 );

		// Handle each Site in turn.
		foreach ( $site_ids as $site_id ) {

			// Switch and run core activation hook.
			switch_to_blog( $site_id );
			do_action( 'commentpress/core/activate', $network_wide = false );

		}

		// Restore.
		restore_current_blog();

		// Restore both "activated" callbacks.
		add_action( 'commentpress/multisite/site/core/activated/after', [ $this, 'core_site_id_add' ], 10 );
		add_action( 'commentpress/core/activate', [ $this, 'core_site_activated' ], 50 );

	}

	/**
	 * Deactivates all CommentPress-enabled Sites.
	 *
	 * @since 4.0
	 */
	public function core_sites_deactivate() {

		// Try to get the CommentPress-enabled Site IDs.
		$site_ids = $this->core_site_ids_get();
		if ( empty( $site_ids ) ) {
			return;
		}

		// Initialise core.
		$this->multisite->plugin->core_initialise();

		// Remove both "deactivated" callbacks because we want to keep the setting as is.
		remove_action( 'commentpress/multisite/site/core/deactivated/after', [ $this, 'core_site_id_remove' ], 10 );
		remove_action( 'commentpress/core/deactivate', [ $this, 'core_site_deactivated' ], 50 );

		// Handle each Site in turn.
		foreach ( $site_ids as $site_id ) {

			// Switch and run core deactivation hook.
			switch_to_blog( $site_id );
			do_action( 'commentpress/core/deactivate', $network_wide = false );

		}

		// Restore.
		restore_current_blog();

		// Restore both "deactivated" callbacks.
		add_action( 'commentpress/multisite/site/core/deactivated/after', [ $this, 'core_site_id_remove' ], 10 );
		add_action( 'commentpress/core/deactivate', [ $this, 'core_site_deactivated' ], 50 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Stores the Site ID to the array of Site IDs where CommentPress Core "optionally deactivated".
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function core_site_activated( $network_wide = false ) {

		// Get the current Site ID.
		$site_id = get_current_blog_id();

		// Add Site ID.
		$this->core_site_id_add( $site_id );

	}

	/**
	 * Removes the Site ID from the array of Site IDs where CommentPress Core "optionally deactivated".
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function core_site_deactivated( $network_wide = false ) {

		// Get the current Site ID.
		$site_id = get_current_blog_id();

		// Remove it from the setting.
		$this->core_site_id_remove( $site_id );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds a Site ID to the "CommentPress Sites on the Network" array.
	 *
	 * @since 4.0
	 *
	 * @param int $site_id The numeric ID of the Site.
	 */
	public function core_site_id_add( $site_id ) {

		// Get the current Site IDs.
		$site_ids = $this->core_site_ids_get();

		// Bail if already present.
		if ( in_array( $site_id, $site_ids, true ) ) {
			return;
		}

		// Add the Site ID and save setting.
		$site_ids[] = $site_id;
		$this->core_site_ids_set( $site_ids );

	}

	/**
	 * Removes a Site ID from the "CommentPress Sites on the Network" array.
	 *
	 * @since 4.0
	 *
	 * @param int $site_id The numeric ID of the Site.
	 */
	public function core_site_id_remove( $site_id ) {

		// Get the current Site IDs.
		$site_ids = $this->core_site_ids_get();

		// Bail if not already present.
		if ( ! in_array( $site_id, $site_ids, true ) ) {
			return;
		}

		// Remove Site ID from array and save setting.
		$site_ids = array_diff( $site_ids, [ $site_id ] );
		$this->core_site_ids_set( $site_ids );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the array of Site IDs where CommentPress Core is active.
	 *
	 * @since 4.0
	 *
	 * @return array $site_ids The array of numeric Site IDs.
	 */
	public function core_site_ids_get() {

		// Get the current Site IDs setting.
		$site_ids = get_site_option( $this->key_sites, [] );

		// --<
		return $site_ids;

	}

	/**
	 * Sets the array of Site IDs where CommentPress Core is active.
	 *
	 * @since 4.0
	 *
	 * @param array $site_ids The array of numeric Site IDs.
	 */
	public function core_site_ids_set( $site_ids ) {

		// Set the Site IDs setting.
		update_site_option( $this->key_sites, $site_ids );

	}

	// -------------------------------------------------------------------------

	/**
	 * Appends the Sites settings to the default multisite settings.
	 *
	 * @since 4.0
	 *
	 * @param array $default_settings The existing default multisite settings.
	 * @return array $default_settings The modified default multisite settings.
	 */
	public function settings_get_defaults( $default_settings ) {

		// CommentPress Core not enabled on all Sites by default.
		$default_settings[ $this->key_forced ] = '0';

		/*
		// The default "Default Welcome Page content" value.
		$default_settings[ $this->key_title_page_content ] = $this->title_page_content_default_get();
		*/

		// --<
		return $default_settings;

	}

	/**
	 * Saves the data from the CommentPress Network "General Settings" screen.
	 *
	 * Adds the data to the settings array. The settings are actually saved later.
	 *
	 * @see CommentPress_Multisite_Settings_Network::form_submitted()
	 *
	 * @since 4.0
	 */
	public function settings_save() {

		// Get "Make all new sites CommentPress-enabled" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$forced = isset( $_POST[ $this->key_forced ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_forced ] ) ) : '0';

		// Set "Make all new sites CommentPress-enabled" option.
		$this->setting_forced_set( ( $forced ? 1 : 0 ) );

		/*
		// Get "Default Welcome Page content" value.
		$title_page_content = isset( $_POST[ $this->key_title_page_content ] ) ?
			sanitize_textarea_field( wp_unslash( $_POST[ $this->key_title_page_content ] ) ) :
			$this->title_page_content_default_get();

		// Set "Default Welcome Page content" setting.
		$this->multisite->db->setting_set( $this->key_title_page_content, $title_page_content );
		*/

	}

	/**
	 * Gets the "Make all new sites CommentPress-enabled" setting.
	 *
	 * @since 4.0
	 *
	 * @return bool $forced True if CommentPress is enabled on all Sites, false otherwise.
	 */
	public function setting_forced_get() {

		// Get the setting.
		$forced = $this->multisite->db->setting_get( $this->key_forced );

		// Return a boolean.
		return ! empty( $forced ) ? true : false;

	}

	/**
	 * Sets the "Make all new sites CommentPress-enabled" setting.
	 *
	 * @since 4.0
	 *
	 * @param int|bool $forced True if CommentPress is enabled on all Sites, false otherwise.
	 */
	public function setting_forced_set( $forced ) {

		// Set the setting.
		$this->multisite->db->setting_set( $this->key_forced, ( $forced ? 1 : 0 ) );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our form elements to the Network Settings "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function metabox_get() {

		// Get settings.
		$forced = $this->setting_forced_get();

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-sites-settings-network.php';

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds the CommentPress form elements to the WordPress Blog Signup Form.
	 *
	 * @since 3.3
	 *
	 * @param array $errors The previously encountered errors.
	 */
	public function site_signup_form_elements_add( $errors ) {

		// Only apply to WordPress signup form - not the BuddyPress ones.
		// TODO: Skip this!
		if ( doing_action( 'bp_groupblog_create_screen_markup' ) ) {
			return;
		}

		// Get force setting.
		$forced = $this->setting_forced_get();

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-sites-signup.php';

	}

	/**
	 * Adds the theme's sidebar to Blog Signup Form.
	 *
	 * @since 3.4
	 */
	public function site_signup_form_after() {

		// Add sidebar.
		get_sidebar();

	}

	/**
	 * Saves metadata when Blog Signup Forms are submitted.
	 *
	 * The "signup_site_meta" filter has been available since WordPress 4.8.0.
	 *
	 * @since 4.0
	 *
	 * @param array $meta Signup meta data. Default empty array.
	 * @return array $meta The modified signup meta data.
	 */
	public function site_signup_form_meta_add( $meta ) {

		// Bail early if we already have our meta.
		if ( ! empty( $meta['commentpress'] ) ) {
			return $meta;
		}

		// Init CommentPress metadata.
		$metadata = [];

		// Get "CommentPress Core enabled on all Sites" setting.
		$forced = $this->setting_forced_get();

		// When not forced.
		if ( ! $forced ) {

			// Bail if our checkbox variable is not in POST.
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$cpmu_new_blog = isset( $_POST['cpmu-new-blog'] ) ? sanitize_text_field( wp_unslash( $_POST['cpmu-new-blog'] ) ) : '';
			if ( empty( $cpmu_new_blog ) ) {
				return $meta;
			}

		}

		// Add flag to our meta.
		$metadata['enable'] = 'y';

		// Maybe add our meta.
		if ( ! empty( $metadata ) ) {
			$meta['commentpress'] = $metadata;
		}

		// --<
		return $meta;

	}

	// -------------------------------------------------------------------------

	/**
	 * Initialises a new CommentPress-enabled Site.
	 *
	 * The "wp_initialize_site" action has been available since WordPress 5.1.0.
	 *
	 * @since 4.0
	 *
	 * @param WP_Site $new_site The new site object.
	 * @param array   $args The array of initialization arguments.
	 */
	public function site_initialise( $new_site, $args ) {

		// If none of our Site initialisation meta is present.
		if ( empty( $args['options']['commentpress'] ) ) {

			// This might be an *additional* Site signup, which skips "signup_site_meta".
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$stage = isset( $_POST['stage'] ) ? sanitize_text_field( wp_unslash( $_POST['stage'] ) ) : '';
			if ( 'gimmeanotherblog' !== $stage ) {
				return;
			}

			// Bail if POST does not contain our hidden variable.
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$signup = isset( $_POST['cp-sites-signup'] ) ? sanitize_text_field( wp_unslash( $_POST['cp-sites-signup'] ) ) : '';
			if ( empty( $signup ) ) {
				return;
			}

			// Bail if our checkbox variable is not in POST.
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$new_blog = isset( $_POST['cpmu-new-blog'] ) ? sanitize_text_field( wp_unslash( $_POST['cpmu-new-blog'] ) ) : '';
			if ( empty( $new_blog ) ) {
				return;
			}

			// Okay, let's add the options.
			$args['options']['commentpress']['enable'] = 'y';

		}

		// Get "CommentPress Core enabled on all Sites" setting.
		$forced = $this->setting_forced_get();

		// Bail if not forced and "Enable CommentPress" checkbox was not checked.
		if ( ! $forced ) {
			if ( empty( $args['options']['commentpress']['enable'] ) ) {
				return;
			}
			if ( 'y' !== $args['options']['commentpress']['enable'] ) {
				return;
			}
		}

		// Switch to the new Site.
		switch_to_blog( $new_site->blog_id );

		// Activate CommentPress Core.
		$this->multisite->site->core_activate();

		/**
		 * Fires when a new CommentPress-enabled Site has been initialised.
		 *
		 * @since 4.0
		 *
		 * @param int $blog_id The numeric ID of the new WordPress Site.
		 * @param array $args The array of initialization arguments.
		 */
		do_action( 'commentpress/multisite/sites/site/initialised', $new_site->blog_id, $args );

		// Switch back.
		restore_current_blog();

	}

	/**
	 * Legacy initialisation of a new CommentPress-enabled Site.
	 *
	 * The "wpmu_new_blog" action has been deprecated since WordPress 5.1.0.
	 *
	 * @since 3.3
	 *
	 * @param int   $blog_id The numeric ID of the WordPress Blog.
	 * @param int   $user_id The numeric ID of the WordPress User.
	 * @param str   $domain The domain of the WordPress Blog.
	 * @param str   $path The path of the WordPress Blog.
	 * @param int   $site_id The numeric ID of the WordPress parent Site.
	 * @param array $meta The meta data of the WordPress Blog.
	 */
	public function site_initialise_legacy( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		// Bail if none of our meta is present.
		if ( empty( $meta['commentpress'] ) ) {
			return;
		}

		// Get "CommentPress enabled on all Sites" setting.
		$forced = $this->setting_forced_get();

		// Bail if not forced and "Enable CommentPress" checkbox was not checked.
		if ( ! $forced ) {
			if ( empty( $meta['commentpress']['enable'] ) ) {
				return;
			}
			if ( 'y' !== $meta['commentpress']['enable'] ) {
				return;
			}
		}

		// Switch to the new Site.
		switch_to_blog( $blog_id );

		// Activate CommentPress Core.
		$this->multisite->site->core_activate();

		// Get the current Site.
		$site = get_current_site();

		// Build args for compatibility with action.
		$args = [
			'title'   => ! empty( $site->site_name ) ? $site->site_name : '',
			'user_id' => $user_id,
			'options' => $meta,
		];

		/**
		 * Fires when a new CommentPress-enabled Site has been initialised.
		 *
		 * @since 4.0
		 *
		 * @param int $blog_id The numeric ID of the new WordPress Site.
		 * @param array $args The array of initialization arguments.
		 */
		do_action( 'commentpress/multisite/sites/site/initialised', $blog_id, $args );

		// Switch back.
		restore_current_blog();

	}

	// -------------------------------------------------------------------------

	/**
	 * Allows all CommentPress parent themes on CommentPress-enabled Sites.
	 *
	 * @since 4.0
	 *
	 * @param array $allowed_themes The existing array of allowed themes.
	 * @param int   $blog_id The numeric ID of the Site.
	 * @return array $allowed_themes The modified array of allowed themes.
	 */
	public function themes_allowed_add( $allowed_themes, $blog_id ) {

		// Bail if this Blog is not CommentPress-enabled.
		if ( ! $this->multisite->site->is_commentpress( $blog_id ) ) {
			return $allowed_themes;
		}

		// Allow all parent themes.
		$allowed_themes['commentpress-flat']   = 1;
		$allowed_themes['commentpress-modern'] = 1;
		$allowed_themes['commentpress-theme']  = 1;

		/**
		 * Filters the allowed themes on a CommentPress-enabled Site.
		 *
		 * @since 4.0
		 *
		 * @param array $allowed_themes The array of allowed themes on a CommentPress-enabled Site.
		 * @param int $blog_id The numeric ID of the Site.
		 */
		$allowed_themes = apply_filters( 'commentpress/multisite/sites/allowed_themes', $allowed_themes, $blog_id );

		// --<
		return $allowed_themes;

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds the "Special Page" slugs to reserved names array.
	 *
	 * @since 3.4
	 *
	 * @param array $reserved_names The existing list of reserved names.
	 * @return array $reserved_names The modified list of reserved names.
	 */
	public function reserved_names_add( $reserved_names ) {

		// Build our array of Special Page slugs.
		$reserved = [
			'title-page',
			'general-comments',
			'all-comments',
			'comments-by-commenter',
			'table-of-contents',
			'author', // Not currently used.
			'login', // For Theme My Login.
		];

		// Add to the reserved names array.
		$reserved_names = array_merge( $reserved_names, $reserved );

		// --<
		return $reserved_names;

	}

	// -------------------------------------------------------------------------

	/**
	 * Get default Welcome Page content, if set.
	 *
	 * Do we want to enable this when we enable the Admin Page editor?
	 *
	 * @since 3.3
	 *
	 * @param str $content The existing content.
	 * @return str $content The modified content.
	 */
	public function title_page_content_get( $content ) {

		// Get content.
		$overridden_content = stripslashes( $this->multisite->db->setting_get( $this->key_title_page_content ) );

		// Override if different to what's been passed.
		if ( $content !== $overridden_content ) {
			$content = $overridden_content;
		}

		// --<
		return $content;

	}

	/**
	 * Get default Welcome Page content.
	 *
	 * @since 3.3
	 *
	 * @return str $content The default Welcome Page content.
	 */
	public function title_page_content_default_get() {

		// --<
		return __(
			'Welcome to your new CommentPress site, which allows your readers to comment paragraph-by-paragraph or line-by-line in the margins of a text. Annotate, gloss, workshop, debate: with CommentPress you can do all of these things on a finer-grained level, turning a document into a conversation.

This is your title page. Edit it to suit your needs. It has been automatically set as your homepage but if you want another page as your homepage, set it in <em>WordPress</em> &#8594; <em>Settings</em> &#8594; <em>Reading</em>.

You can also set a number of options in <em>WordPress</em> &#8594; <em>Settings</em> &#8594; <em>CommentPress</em> to make the site work the way you want it to. Use the Theme Customizer to change the way your site looks in <em>WordPress</em> &#8594; <em>Appearance</em> &#8594; <em>Customize</em>. For help with structuring, formatting and reading text in CommentPress, please refer to the <a href="https://www.futureofthebook.org/commentpress/">CommentPress website</a>.',
			'commentpress-core'
		);

	}

}
