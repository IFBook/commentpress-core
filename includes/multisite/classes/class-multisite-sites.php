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
	 * @var object $multisite The multisite loader object.
	 */
	public $multisite;

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
	 * @access public
	 * @var str $key_sites The settings key for the array of "CommentPress Sites enabled on the Network" setting.
	 */
	public $key_sites = 'sites';

	/**
	 * "CommentPress Core enabled on all Sites" settings key.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $key_forced The settings key for the "CommentPress Core enabled on all Sites" setting.
	 */
	public $key_forced = 'cpmu_force_commentpress';

	/**
	 * "Default Welcome Page content" settings key.
	 *
	 * Not implemented.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $key_title_page_content The settings key for the "Default Welcome Page content" setting.
	 */
	public $key_title_page_content = 'cpmu_title_page_content';

	/**
	 * Partials template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $metabox_path Relative path to the Partials directory.
	 */
	private $partials_path = 'includes/multisite/assets/templates/wordpress/partials/';

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
		add_action( 'commentpress/multisite/site/core/activated/after', [ $this, 'core_site_id_add' ], 10, 2 );

		// Act when plugin is network-active and core is "soft deactivated".
		add_action( 'commentpress/multisite/site/core/deactivated/after', [ $this, 'core_site_id_remove' ], 10 );

		// Act when plugin is not network-active and core is "optionally activated".
		add_action( 'commentpress/core/activate', [ $this, 'core_site_activated' ], 50 );

		// Act when plugin is not network-active and core is "optionally deactivated".
		add_action( 'commentpress/core/deactivate', [ $this, 'core_site_deactivated' ], 50 );

		// Add our option to the Network Settings "General Settings" metabox.
		add_action( 'commentpress/multisite/settings/network/metabox/general/after', [ $this, 'metabox_settings_get' ] );

		// Save data from Network Settings form submissions.
		add_action( 'commentpress/multisite/settings/network/save/before', [ $this, 'settings_save' ] );

		// Filter the default multisite settings.
		add_filter( 'commentpress/multisite/settings/defaults', [ $this, 'settings_get_defaults' ] );

		// ---------------------------------------------------------------------

		// Add form elements to Blog Signup Form.
		add_action( 'signup_blogform', [ $this, 'signup_blogform_elements_add' ] );

		// Add callback for Signup Page to include Sidebar.
		add_action( 'after_signup_form', [ $this, 'signup_blogform_after' ], 20 );

		// Save meta for Blog Signup Form submissions.
		add_filter( 'signup_site_meta', [ $this, 'signup_blogform_meta_add' ], 10, 7 );

		// Activate Blog-specific CommentPress Core plugin.
		if ( version_compare( $GLOBALS['wp_version'], '5.1.0', '>=' ) ) {
			add_action( 'wp_initialize_site', [ $this, 'site_initialise' ], 10, 2 );
		} else {
			add_action( 'wpmu_new_blog', [ $this, 'site_initialise_legacy' ], 20, 6 );
		}

		// ---------------------------------------------------------------------

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

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'network_wide' => $network_wide ? 'y' : 'n',
			//'backtrace' => $trace,
		], true ) );
		*/

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

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'network_wide' => $network_wide ? 'y' : 'n',
			//'backtrace' => $trace,
		], true ) );
		*/

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
	 * @param str $context The activation context.
	 */
	public function core_site_id_add( $site_id, $context = '' ) {

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'site_id' => $site_id,
			//'backtrace' => $trace,
		], true ) );
		*/

		// Get the current Site IDs.
		$site_ids = $this->core_site_ids_get();

		// Bail if already present.
		if ( in_array( $site_id, $site_ids ) ) {
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

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'site_id' => $site_id,
			'backtrace' => $trace,
		], true ) );
		*/

		// Get the current Site IDs.
		$site_ids = $this->core_site_ids_get();

		// Bail if not already present.
		if ( ! in_array( $site_id, $site_ids ) ) {
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

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'site_ids-GET' => $site_ids,
			//'backtrace' => $trace,
		], true ) );
		*/

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

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'site_ids-SET' => $site_ids,
			//'backtrace' => $trace,
		], true ) );
		*/

		// Set the Site IDs setting.
		update_site_option( $this->key_sites, $site_ids );

	}

	// -------------------------------------------------------------------------

	/**
	 * Saves the data from the CommentPress Network "General Settings" screen.
	 *
	 * Adds the data to the options array. The options are actually saved later.
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
		$this->multisite->db->setting_set( $this->key_forced, ( $forced ? 1 : 0 ) );

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
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'default_settings' => $default_settings,
			//'backtrace' => $trace,
		], true ) );
		*/

		/*
		// The default "Default Welcome Page content" value.
		$default_settings[ $this->key_title_page_content ] = $this->title_page_content_default_get();
		*/

		// --<
		return $default_settings;

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our settings to the Network Settings "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function metabox_settings_get() {

		// Get settings.
		$forced = $this->multisite->db->setting_get( $this->key_forced );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->partials_path . 'partial-sites-settings-network.php';

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds the CommentPress form elements to the Blog Signup Form.
	 *
	 * @since 3.3
	 *
	 * @param array $errors The previously encountered errors.
	 */
	public function signup_blogform_elements_add( $errors ) {

		/*
		// Only apply to WordPress signup form (not the BuddyPress one).
		if ( is_object( $this->multisite->bp->workshop ) ) {
			return;
		}
		*/

		// Get force setting.
		$forced = $this->multisite->db->setting_get( $this->key_forced );

		// Are we force-enabling CommentPress Core?
		if ( $forced ) {

			/**
			 * Filters the forced Signup Form text.
			 *
			 * @since 3.3
			 *
			 * @param string The default Signup Form text.
			 */
			$text = apply_filters( 'cp_multisite_options_signup_text_forced', __( 'Your new site will be CommentPress-enabled.', 'commentpress-core' ) );

		} else {

			/**
			 * Filters the un-forced Signup Form text.
			 *
			 * @since 3.3
			 *
			 * @param string The default signup text.
			 */
			$text = apply_filters( 'cp_multisite_options_signup_text', __( 'Do you want to make the new site CommentPress-enabled?', 'commentpress-core' ) );

		}

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->partials_path . 'partial-sites-signup.php';

	}

	/**
	 * Adds the theme's sidebar to Blog Signup Form.
	 *
	 * @since 3.4
	 */
	public function signup_blogform_after() {

		// Add sidebar.
		get_sidebar();

	}

	/**
	 * Saves metadata when Blog Signup Forms are submitted.
	 *
	 * The "signup_site_meta" filter has been available since WordPress 4.8.0.
	 *
	 * @since 3.4
	 *
	 * @param array $meta Signup meta data. Default empty array.
	 * @param string $domain The requested domain.
	 * @param string $path The requested path.
	 * @param string $title The requested site title.
	 * @param string $user The user's requested login name.
	 * @param string $user_email The user's email address.
	 * @param string $key The user's activation key.
	 * @return array $meta The modified signup meta data.
	 */
	public function signup_blogform_meta_add( $meta, $domain, $path, $title, $user, $user_email, $key ) {

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'POST' => $_POST,
			//'backtrace' => $trace,
		], true ) );
		*/

		// Init CommentPress metadata.
		$metadata = [];

		// Get "CommentPress Core enabled on all Sites" setting.
		$forced = $this->multisite->db->setting_get( $this->key_forced );

		// When not forced.
		if ( ! $forced ) {

			// Bail if our checkbox variable is not in POST.
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

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'meta' => $meta,
			//'backtrace' => $trace,
		], true ) );
		*/

		// --<
		return $meta;

	}

	// -------------------------------------------------------------------------

	/**
	 * Initialises a new site.
	 *
	 * The "wp_initialize_site" action has been available since WordPress 5.1.0.
	 *
	 * @since 3.3
	 * @param WP_Site $new_site The new site object.
	 * @param array $args The array of initialization arguments.
	 */
	public function site_initialise( $new_site, $args ) {

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'new_site' => $new_site,
			'args' => $args,
			//'backtrace' => $trace,
		], true ) );
		*/

		// Bail if none of our meta is present.
		if ( empty( $args['options']['commentpress'] ) ) {
			return;
		}

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'one' => 'here',
			//'backtrace' => $trace,
		], true ) );
		*/

		// Get "CommentPress Core enabled on all Sites" setting.
		$forced = $this->multisite->db->setting_get( $this->key_forced );

		// Bail if not forced and "Enable CommentPress" checkbox was not checked.
		if ( ! $forced ) {
			if ( empty( $args['options']['commentpress']['enable'] ) ) {
				return;
			}
			if ( 'y' !== $args['options']['commentpress']['enable'] ) {
				return;
			}
		}

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'two' => 'here',
			//'backtrace' => $trace,
		], true ) );
		*/

		// Switch to the site.
		switch_to_blog( $new_site->blog_id );

		// Activate CommentPress Core.
		$this->multisite->site->core_activate();

		// Switch back.
		restore_current_blog();

	}

	/**
	 * Initialises a new blog.
	 *
	 * The "wpmu_new_blog" action has been deprecated since WordPress 5.1.0.
	 *
	 * @since 3.3
	 *
	 * @param int $blog_id The numeric ID of the WordPress Blog.
	 * @param int $user_id The numeric ID of the WordPress User.
	 * @param str $domain The domain of the WordPress Blog.
	 * @param str $path The path of the WordPress Blog.
	 * @param int $site_id The numeric ID of the WordPress parent Site.
	 * @param array $meta The meta data of the WordPress Blog.
	 */
	public function site_initialise_legacy( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		// Bail if none of our meta is present.
		if ( empty( $meta['commentpress'] ) ) {
			return;
		}

		// Get "CommentPress Core enabled on all Sites" setting.
		$forced = $this->multisite->db->setting_get( $this->key_forced );

		// Bail if not forced and "Enable CommentPress" checkbox was not checked.
		if ( ! $forced ) {
			if ( empty( $meta['commentpress']['enable'] ) ) {
				return;
			}
			if ( 'y' !== $meta['commentpress']['enable'] ) {
				return;
			}
		}

		// Switch to the site.
		switch_to_blog( $blog_id );

		// Activate CommentPress Core.
		$this->multisite->site->core_activate();

		// Switch back.
		restore_current_blog();

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the "Text Format" options array.
	 *
	 * @since 4.0
	 *
	 * @return array $types The array of Text Format options.
	 */
	public function formats_array_get() {

		// Define no types.
		$types = [];

		/**
		 * Build Text Format options.
		 *
		 * @since 3.3.1
		 *
		 * @param array $types Empty by default since others add them.
		 */
		$types = apply_filters( 'cp_blog_type_options', $types );

		// Bail if we don't get any.
		if ( empty( $types ) ) {
			return $types;
		}

		// --<
		return $types;

	}

	/**
	 * Gets the "Text Format" options for a select element.
	 *
	 * @since 4.0
	 *
	 * @param array $types The array of "Text Format" options.
	 * @param int $current The current "Text Format" option.
	 * @param bool $show_default True includes the "Use default" option, false does not.
	 * @return string $markup The "Text Format" options markup.
	 */
	public function formats_select_options_get( $types, $current = false, $show_default = true ) {

		// Init markup.
		$markup = '';

		// Bail if we don't get any.
		if ( empty( $types ) ) {
			return $markup;
		}

		// Init options.
		$options = [];

		// Maybe add "Use Default".
		if ( $show_default === true ) {
			$options = [
				'<option value="" ' . ( ( $current === false || $current === '' ) ? ' selected="selected"' : '' ) . '>' .
					esc_html__( 'Use default', 'commentpress-core' ) .
				'</option>',
			];
		}

		// Build options.
		foreach ( $types as $key => $type ) {
			if ( (string) $key === (string) $current ) {
				$options[] = '<option value="' . esc_attr( $key ) . '" selected="selected">' . esc_html( $type ) . '</option>';
			} else {
				$options[] = '<option value="' . esc_attr( $key ) . '">' . esc_html( $type ) . '</option>';
			}
		}

		// Merge options.
		if ( ! empty( $options ) ) {
			$markup = implode( "\n", $options );
		}

		// --<
		return $markup;

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

		// Add Special Page slugs.
		$reserved_names = array_merge(
			$reserved_names,
			[
				'title-page',
				'general-comments',
				'all-comments',
				'comments-by-commenter',
				'table-of-contents',
				'author', // Not currently used.
				'login', // For Theme My Login.
			]
		);

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
		if ( $content != $overridden_content ) {
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

You can also set a number of options in <em>WordPress</em> &#8594; <em>Settings</em> &#8594; <em>CommentPress</em> to make the site work the way you want it to. Use the Theme Customizer to change the way your site looks in <em>WordPress</em> &#8594; <em>Appearance</em> &#8594; <em>Customize</em>. For help with structuring, formatting and reading text in CommentPress, please refer to the <a href="http://www.futureofthebook.org/commentpress/">CommentPress website</a>.',
			'commentpress-core'
		);

	}

}
