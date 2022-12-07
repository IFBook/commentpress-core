<?php
/**
 * CommentPress Multisite BuddyPress Groupblog class.
 *
 * Handles compatibility with the BuddyPress Groupblog plugin.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Multisite BuddyPress Groupblog Class.
 *
 * This class handles compatibility with the BuddyPress Groupblog plugin.
 *
 * DEVELOPER NOTES:
 *
 * Things have changed since BuddyPress Groupblog 1.9.0:
 *
 * * The "new_groupblog_comment" Action has been fixed.
 * * The way that the "new_groupblog_post" Action is declared has been changed.
 *
 * For versions of BuddyPress Groupblog prior to 1.9.0, this plugin provided
 * replacement Actions for "new_groupblog_post" and "new_groupblog_comment" that
 * fixed the bugs in those BuddyPress Groupblog Actions.
 *
 * The replacement "new_groupblog_post" Action also provided compatiblity with
 * the "Co-Authors" plugin.
 *
 * For versions of BuddyPress Groupblog from 1.9.0 to 1.9.2, compatibility is
 * sort of broken. Things still work, but the naming scheme and Activity filters
 * do not work as expected.
 *
 * The updates to BuddyPress Groupblog that are in the pipeline for 1.9.3 may
 * resolve the situation but that's not certain at this stage.
 *
 * In summary, either this plugin has to support all versions of BuddyPress
 * Groupblog or it has to require the latest version. Tempting to go for the
 * latter option.
 *
 * @since 3.3
 */
class CommentPress_Multisite_BuddyPress_Groupblog {

	/**
	 * Multisite loader object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $multisite The multisite loader object.
	 */
	public $multisite;

	/**
	 * BuddyPress object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $bp The BuddyPress object reference.
	 */
	public $bp;

	/**
	 * BuddyPress Groupblog Groups object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $groups The BuddyPress Groupblog Groups object.
	 */
	public $groups;

	/**
	 * BuddyPress Groupblog Names object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $names The BuddyPress Groupblog Names object.
	 */
	public $names;

	/**
	 * BuddyPress Groupblog Site object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $site The BuddyPress Groupblog Site object.
	 */
	public $site;

	/**
	 * Classes directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $classes_path Relative path to the classes directory.
	 */
	private $classes_path = 'includes/multisite/classes/';

	/**
	 * Metabox template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $metabox_path Relative path to the Metabox directory.
	 */
	private $metabox_path = 'includes/multisite/assets/templates/buddypress/metaboxes/';

	/**
	 * Parts template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $parts_path Relative path to the Parts directory.
	 */
	private $parts_path = 'includes/multisite/assets/templates/buddypress/parts/';

	/**
	 * Plugin compatibility flag.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $compatibility Plugin compatibility flag.
	 */
	public $compatibility = 'none';

	/**
	 * "CommentPress enabled on all Group Blogs" settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var str $key_forced The settings key for the "CommentPress enabled on all Group Blogs" setting.
	 */
	private $key_forced = 'cpmu_bp_force_commentpress';

	/**
	 * "Group Blog privacy" settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var str $key_privacy The settings key for the "Group Blog privacy" setting.
	 */
	private $key_privacy = 'cpmu_bp_groupblog_privacy';

	/**
	 * "Require login to leave Comments on Group Blogs" settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var str $key_comment_login The settings key for the "Require login to leave Comments on Group Blogs" setting.
	 */
	private $key_comment_login = 'cpmu_bp_require_comment_registration';

	/**
	 * "Default theme for Group Blogs" settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var str $key_theme The settings key for the "Default theme for Group Blogs" setting.
	 */
	private $key_theme = 'cpmu_bp_groupblog_theme';

	/**
	 * "Group Type" Group meta key.
	 *
	 * @since 4.0
	 * @access private
	 * @var str $key_theme The Group meta key for the "Group Type" setting.
	 */
	private $key_group_meta = 'groupblogtype';

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param object $bp Reference to the BuddyPress object.
	 */
	public function __construct( $bp ) {

		// Bail if BuddyPress Groupblog plugin is not present.
		if ( ! defined( 'BP_GROUPBLOG_VERSION' ) ) {
			return;
		}

		// Store references.
		$this->multisite = $bp->multisite;
		$this->bp = $bp;

		// Check compatibility before proceeding.
		$this->compatibility_check();

		// Init when the BuddyPress classes are fully loaded.
		add_action( 'commentpress/multisite/bp/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Checks compatibility with the BuddyPress Groupblog plugin.
	 *
	 * @since 4.0
	 *
	 * @return str|bool $compatibility False if BuddyPress Groupblog plugin not present, compatibility flag otherwise.
	 */
	public function compatibility_check() {

		// Check BuddyPress Groupblog version before proceeding.
		if ( version_compare( BP_GROUPBLOG_VERSION, '1.9.0', '>=' ) ) {
			if ( version_compare( BP_GROUPBLOG_VERSION, '1.9.3', '>=' ) ) {
				$this->compatibility = 'latest';
			}
		} else {
			$this->compatibility = 'legacy';
		}

		// --<
		return $this->compatibility;

	}

	/**
	 * Gets the status of compatibility with the BuddyPress Groupblog plugin.
	 *
	 * @since 4.0
	 *
	 * @return str $compatibility The compatibility status flag.
	 */
	public function compatibility_get() {
		return $this->compatibility;
	}

	/**
	 * Initialises this object.
	 *
	 * @since 3.3
	 */
	public function initialise() {

		// Only do this once.
		static $done;
		if ( isset( $done ) && $done === true ) {
			return;
		}

		// Bootstrap this object.
		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

		/**
		 * Fires when BuddyPress Groupblog has loaded.
		 *
		 * Used internally to bootstrap objects.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/multisite/bp/groupblog/loaded' );

		// We're done.
		$done = true;

	}

	/**
	 * Includes class files.
	 *
	 * @since 4.0
	 */
	public function include_files() {

		// Include class files.
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-multisite-bp-groupblog-groups.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-multisite-bp-groupblog-names.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-multisite-bp-groupblog-site.php';

	}

	/**
	 * Sets up the objects in this class.
	 *
	 * @since 4.0
	 */
	public function setup_objects() {

		// Initialise objects.
		$this->groups = new CommentPress_Multisite_BuddyPress_Groupblog_Groups( $this );
		$this->names = new CommentPress_Multisite_BuddyPress_Groupblog_Names( $this );
		$this->site = new CommentPress_Multisite_BuddyPress_Groupblog_Site( $this );

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.3
	 */
	public function register_hooks() {

		// Separate callbacks into descriptive methods.
		$this->register_hooks_settings();
		$this->register_hooks_signup();

		// Update the "Text Format" for a Group Blog when it's changed on this Site.
		add_action( 'commentpress/core/formatter/setting/set', [ $this, 'group_type_set_by_text_format' ] );

	}

	/**
	 * Registers BuddyPress Groupblog "Network Settings" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_settings() {

		// Add BuddyPress Groupblog settings to default settings.
		add_filter( 'commentpress/multisite/settings/defaults', [ $this, 'settings_get_defaults' ], 20, 1 );

		// Add our metaboxes to the Network Settings screen.
		add_filter( 'commentpress/multisite/settings/network/metaboxes/after', [ $this, 'settings_meta_boxes_append' ] );

		// Add our Javascript to the Network Settings screen.
		add_action( 'commentpress/multisite/settings/network/admin/js', [ $this, 'settings_meta_box_js_enqueue' ] );

		// Save data from Network Settings form submissions.
		add_action( 'commentpress/multisite/settings/network/save/before', [ $this, 'settings_meta_box_save' ] );

	}

	/**
	 * Registers BuddyPress Groupblog Signup Form hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_signup() {

		// Add form elements to the BuddyPress Groupblog Signup Form.
		add_action( 'signup_blogform', [ $this, 'form_signup_elements_add' ] );

		// Save meta for BuddyPress Groupblog Blog Signup Form submissions.
		add_filter( 'add_signup_meta', [ $this, 'form_signup_submitted_meta_add' ] );

		// Act when the BuddyPress Groupblog Signup Form is submitted.
		add_action( 'commentpress/multisite/sites/site/initialised', [ $this, 'form_signup_site_initialised' ], 10, 2 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Appends the BuddyPress Groupblog settings to the default multisite settings.
	 *
	 * @since 3.3
	 *
	 * @param array $settings The existing default multisite settings.
	 * @return array $settings The modified default multisite settings.
	 */
	public function settings_get_defaults( $settings ) {

		// Add our BuddyPress Groupblog defaults.
		$settings[ $this->key_forced ] = 0;
		$settings[ $this->key_privacy ] = 1;
		$settings[ $this->key_comment_login ] = 1;
		$settings[ $this->key_theme ] = 'commentpress-flat';

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'settings' => $settings,
			//'backtrace' => $trace,
		], true ) );
		*/

		// --<
		return $settings;

	}

	/**
	 * Appends our metaboxes to the Network Settings screen.
	 *
	 * @since 4.0
	 *
	 * @param string $screen_id The Network Settings Screen ID.
	 */
	public function settings_meta_boxes_append( $screen_id ) {

		// Create "BuddyPress Groupblog Settings" metabox.
		add_meta_box(
			'commentpress_bp_groupblog',
			__( 'BuddyPress Groupblog Settings', 'commentpress-core' ),
			[ $this, 'settings_meta_box_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

	}

	/**
	 * Renders the "BuddyPress Groupblog Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_render() {

		// Get settings.
		$force_commentpress = $this->setting_forced_get();
		$privacy = $this->setting_privacy_get();
		$comment_login = $this->setting_comment_login_get();

		// Get the valid Theme stylesheets and titles.
		$groupblog_themes = $this->site->themes_get();

		// Get currently selected theme.
		$current_theme = $this->setting_theme_get();

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'force_commentpress' => $force_commentpress ? 'y' : 'n',
			'privacy' => $privacy ? 'y' : 'n',
			'comment_login' => $comment_login ? 'y' : 'n',
			'groupblog_themes' => $groupblog_themes,
			'current_theme' => $current_theme,
			//'backtrace' => $trace,
		], true ) );
		*/

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-network-bp-groupblog.php';

	}

	/**
	 * Adds our Javascript to the Network Settings screen.
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_js_enqueue() {

		// Add our Javascript.
		wp_enqueue_script(
			'commentpress_bp_groupblog',
			plugins_url( 'includes/multisite/assets/js/cp-bp-groupblog-settings-network.js', COMMENTPRESS_PLUGIN_FILE ),
			[ 'jquery' ],
			COMMENTPRESS_VERSION, // Version.
			true
		);

	}

	/**
	 * Saves the data from the Network Settings "BuddyPress Groupblog Settings" metabox.
	 *
	 * Adds the data to the settings array. The settings are actually saved later.
	 *
	 * @see CommentPress_Multisite_Settings_Network::form_submitted()
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_save() {

		// Get "Make all new Group Blogs CommentPress-enabled" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$forced = isset( $_POST[ $this->key_forced ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_forced ] ) ) : '0';

		// Set the setting.
		$this->setting_forced_set( ( $forced ? 1 : 0 ) );

		// Get "Private Groups must have Private Group Blogs" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$privacy = isset( $_POST[ $this->key_privacy ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_privacy ] ) ) : '0';

		// Set the setting.
		$this->setting_privacy_set( ( $privacy ? 1 : 0 ) );

		// Get "Require user login to post comments on Group Blogs" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$comment_login = isset( $_POST[ $this->key_comment_login ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_comment_login ] ) ) : '0';

		// Set the setting.
		$this->setting_comment_login_set( ( $comment_login ? 1 : 0 ) );

		// Get "CommentPress-enabled Group Blog theme" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$theme = isset( $_POST[ $this->key_theme ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_theme ] ) ) : '';

		// Set the setting.
		$this->setting_theme_set( $theme );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the "Make all new Group Blogs CommentPress-enabled" setting.
	 *
	 * @since 4.0
	 *
	 * @return bool $forced True if CommentPress is enabled on all Group Blogs, false otherwise.
	 */
	public function setting_forced_get() {

		// Get the setting.
		$forced = $this->multisite->db->setting_get( $this->key_forced );

		// Return a boolean.
		return ! empty( $forced ) ? true : false;

	}

	/**
	 * Sets the "Make all new Group Blogs CommentPress-enabled" setting.
	 *
	 * @since 4.0
	 *
	 * @param int|bool $forced True if CommentPress is enabled on all Group Blogs, false otherwise.
	 */
	public function setting_forced_set( $forced ) {

		// Set the setting.
		$this->multisite->db->setting_set( $this->key_forced, ( $forced ? 1 : 0 ) );

	}

	/**
	 * Gets the "Private Groups must have Private Group Blogs" setting.
	 *
	 * @since 4.0
	 *
	 * @return bool $privacy True if Private Groups must have Private Group Blogs, false otherwise.
	 */
	public function setting_privacy_get() {

		// Get the setting.
		$privacy = $this->multisite->db->setting_get( $this->key_privacy );

		// Return a boolean.
		return ! empty( $privacy ) ? true : false;

	}

	/**
	 * Sets the "Private Groups must have Private Group Blogs" setting.
	 *
	 * @since 4.0
	 *
	 * @param int|bool $privacy True if Private Groups must have Private Group Blogs, false otherwise.
	 */
	public function setting_privacy_set( $privacy ) {

		// Set the setting.
		$this->multisite->db->setting_set( $this->key_privacy, ( $privacy ? 1 : 0 ) );

	}

	/**
	 * Gets the "Require user login to post comments on Group Blogs" setting.
	 *
	 * @since 4.0
	 *
	 * @return bool $comment_login True if user login is required to post comments on Group Blogs, false otherwise.
	 */
	public function setting_comment_login_get() {

		// Get the setting.
		$comment_login = $this->multisite->db->setting_get( $this->key_comment_login );

		// Return a boolean.
		return ! empty( $comment_login ) ? true : false;

	}

	/**
	 * Sets the "Require user login to post comments on Group Blogs" setting.
	 *
	 * @since 4.0
	 *
	 * @param int|bool $comment_login True if user login is required to post comments on Group Blogs, false otherwise.
	 */
	public function setting_comment_login_set( $comment_login ) {

		// Set the setting.
		$this->multisite->db->setting_set( $this->key_comment_login, ( $comment_login ? 1 : 0 ) );

	}

	/**
	 * Gets the "CommentPress-enabled Group Blog theme" setting.
	 *
	 * @since 4.0
	 *
	 * @return str|bool $theme The theme "stylesheet" if found, false otherwise.
	 */
	public function setting_theme_get() {

		// Get the setting.
		$theme = $this->multisite->db->setting_get( $this->key_theme );

		// Return theme "stylesheet" or boolean if empty.
		return ! empty( $theme ) ? $theme : false;

	}

	/**
	 * Sets the "CommentPress-enabled Group Blog theme" setting.
	 *
	 * @since 4.0
	 *
	 * @param str $theme The theme "stylesheet".
	 */
	public function setting_theme_set( $theme ) {

		// Set the setting.
		$this->multisite->db->setting_set( $this->key_theme, $theme );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds the CommentPress form elements to the BuddyPress Blog Signup Form.
	 *
	 * @since 3.3
	 *
	 * @param array $errors The errors generated previously.
	 */
	public function form_signup_elements_add( $errors ) {

		// Skip if it's not the BuddyPress GroupBlog Blog Signup Form.
		if ( ! bp_is_groups_component() ) {
			return;
		}

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'message' => 'ADDING GROUP BLOG',
			//'backtrace' => $trace,
		], true ) );
		*/

		// Unhook Multisite Sites callback.
		remove_action( 'signup_blogform', [ $this->multisite->sites, 'site_signup_form_elements_add' ], 50 );

		// Access the BuddyPress Groupblog global.
		global $groupblog_create_screen;

		// Get the current Group Blog ID.
		$blog_id = get_groupblog_blog_id();

		// Bail if there is an existing Blog and Group.
		if ( ! $groupblog_create_screen && ! empty( $blog_id ) ) {
			// Do we need to present any options?
			return;
		}

		// Get forced option.
		$forced = $this->setting_forced_get();

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-bp-groupblog-signup.php';

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
	public function form_signup_submitted_meta_add( $meta ) {

		// Bail early if not in a BuddyPress Groups context.
		if ( ! bp_is_groups_component() ) {
			return;
		}

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'POST' => $_POST,
			'meta' => $meta,
			'skip' => ! empty( $meta['commentpress'] ) ? 'y' : 'n',
			//'backtrace' => $trace,
		], true ) );
		*/

		// Bail early if we already have our meta.
		if ( ! empty( $meta['commentpress'] ) ) {
			return $meta;
		}

		// Init CommentPress metadata.
		$metadata = [];

		// Get "CommentPress enabled on all Sites" setting.
		$forced = $this->setting_forced_get();

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'forced' => $forced ? 'y' : 'n',
			//'backtrace' => $trace,
		], true ) );
		*/

		// When not forced.
		if ( ! $forced ) {

			// Bail if our checkbox variable is not in POST.
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$checkbox = isset( $_POST['cpbp-groupblog'] ) ? sanitize_text_field( wp_unslash( $_POST['cpbp-groupblog'] ) ) : '';
			if ( empty( $checkbox ) ) {
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

	/**
	 * Initialises a new CommentPress-enabled Group Blog.
	 *
	 * @since 4.0
	 *
	 * @param int $blog_id The numeric ID of the new WordPress Site.
	 * @param array $args The array of initialization arguments.
	 */
	public function form_signup_site_initialised( $blog_id, $args ) {

		// Bail early if not in a BuddyPress Groups context.
		if ( ! bp_is_groups_component() ) {
			return;
		}

		// Get Group ID before switch.
		$group_id = isset( $_COOKIE['bp_new_group_id'] ) ? sanitize_text_field( wp_unslash( $_COOKIE['bp_new_group_id'] ) ) : bp_get_current_group_id();

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'group_id' => $group_id,
			//'backtrace' => $trace,
		], true ) );
		*/

		// Bail if we don't get one.
		if ( empty( $group_id ) ) {
			return;
		}

		// We are already switched to the new Site so get core reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return;
		}

		// TODO: Create settings in "BuddyPress Groupblog Settings" metabox for WordPress options.

		/**
		 * Filters the "Show Posts by default" option.
		 *
		 * @since 3.4
		 *
		 * @param string The default "Show Posts by default" option.
		 */
		$posts_or_pages = apply_filters( 'cp_posts_or_pages_in_toc', 'post' );
		$core->nav->setting_post_type_set( $posts_or_pages );

		// If we opted for Posts.
		if ( $posts_or_pages == 'post' ) {

			/**
			 * Filters the "TOC shows extended Posts" option.
			 *
			 * @since 3.4
			 *
			 * @param bool The default "TOC shows extended Posts" option.
			 */
			$extended_toc = apply_filters( 'cp_extended_toc', 1 );
			$core->nav->setting_subpages_set( $extended_toc );

		}

		// Get Site Text Format.
		$site_text_format = $core->formatter->setting_formatter_get();

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'site_text_format' => $site_text_format,
			//'backtrace' => $trace,
		], true ) );
		*/

		// Set the type as Group meta info.
		// TODO: Check that the type is changed from the CommentPress Core "Site Settings" screen.
		$this->group_type_set( $group_id, (string) $site_text_format );

		// Save options.
		$core->db->settings_save();

		// ---------------------------------------------------------------------
		// WordPress Internal Configuration.
		// ---------------------------------------------------------------------

		// Get commenting option.
		$anon_comments = $this->setting_comment_login_get() ? 1 : 0;

		/**
		 * Filters the anonymous commenting setting.
		 *
		 * @since 3.3
		 *
		 * @param bool $anon_comments A value of 1 requires registration, 0 does not.
		 */
		$anon_comments = apply_filters( 'cp_require_comment_registration', $anon_comments );

		// Update WordPress option.
		update_option( 'comment_registration', $anon_comments );

		// Get all network-activated plugins.
		$sitewide_plugins = maybe_unserialize( get_site_option( 'active_sitewide_plugins', [] ) );
		if ( ! empty( $sitewide_plugins ) ) {

			// Loop through them.
			foreach ( $sitewide_plugins as $plugin_path => $plugin_data ) {

				// Switch "comments_notify" off if we've got "BuddyPress Group Email Subscription" network-activated.
				if ( false !== strstr( $plugin_path, 'bp-activity-subscription.php' ) ) {
					update_option( 'comments_notify', 0 );
					continue;
				}

				// Handle other network-activated plugins here.

			}

		}

		/**
		 * Allow plugins to add their own config.
		 *
		 * @since 3.8.5
		 *
		 * @param int $blog_id The numeric ID of the WordPress Blog.
		 * @param int $site_text_format The numeric Site Text Format.
		 * @param bool False since this is now deprecated.
		 */
		do_action_deprecated(
			'cp_new_groupblog_created',
			[ $blog_id, $site_text_format, false ],
			'4.0',
			'commentpress/multisite/bp/groupblog/site/initialised'
		);

		/**
		 * Fires when a new CommentPress-enabled Group Blog has been initialised.
		 *
		 * @since 4.0
		 *
		 * @param int $blog_id The numeric ID of the WordPress Blog.
		 * @param int $site_text_format The numeric Site Text Format.
		 * @param int $group_id The numeric ID of the BuddyPress Group.
		 */
		do_action_deprecated( 'commentpress/multisite/bp/groupblog/site/initialised', $blog_id, $site_text_format, $group_id );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the Group Blog Type for a given Group ID.
	 *
	 * @since 4.0
	 *
	 * @param int $group_id The numeric ID of the Group.
	 * @return str $group_type The Group Type identifier, or false on failure.
	 */
	public function group_type_get( $group_id ) {

		// Get the value from Group meta.
		$group_type = groups_get_groupmeta( $group_id, $this->key_group_meta );

		// --<
		return $group_type;

	}

	/**
	 * Sets the Group Blog Type for a given Group ID.
	 *
	 * @since 4.0
	 *
	 * @param int $group_id The numeric ID of the Group.
	 * @param str $group_type The Group Type identifier.
	 */
	public function group_type_set( $group_id, $group_type ) {

		// Set the value in Group meta.
		groups_update_groupmeta( $group_id, $this->key_group_meta, $this->key_group_meta . '-' . $group_type );

	}

	/**
	 * Sets the Group Blog Type when the "Text Format" of a Site changes.
	 *
	 * @since 4.0
	 *
	 * @param str $formatter The "Text Format" setting.
	 */
	public function group_type_set_by_text_format( $formatter ) {

		// Bail if it's not a Group Blog.
		if ( ! $this->site->is_commentpress_groupblog() ) {
			return;
		}

		// Bail if there's no Group ID.
		$group_id = get_groupblog_group_id( get_current_blog_id() );
		if ( empty( $group_id ) || ! is_numeric( $group_id ) ) {
			return;
		}

		// Store the Site Text Format in Group meta.
		$groupblog_text_format = $this->group_type_set( $group_id, $formatter );

	}

}
