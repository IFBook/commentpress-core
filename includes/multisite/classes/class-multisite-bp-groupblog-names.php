<?php
/**
 * CommentPress Multisite BuddyPress Groupblog Names class.
 *
 * Handles overriding the naming scheme of Group Blogs. For example, from "Blog"
 * to "Document" or "Workshop".
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Multisite BuddyPress Groupblog Names Class.
 *
 * This class handles overriding the naming scheme of Group Blogs. For example,
 * from "Blog" to "Document" or "Workshop".
 *
 * @since 3.3
 */
class CommentPress_Multisite_BuddyPress_Groupblog_Names {

	/**
	 * Multisite loader object.
	 *
	 * @since 3.0
	 * @access public
	 * @var CommentPress_Multisite_Loader
	 */
	public $multisite;

	/**
	 * BuddyPress object.
	 *
	 * @since 3.3
	 * @access public
	 * @var CommentPress_Multisite_BuddyPress
	 */
	public $bp;

	/**
	 * BuddyPress Groupblog object.
	 *
	 * @since 3.3
	 * @access public
	 * @var CommentPress_Multisite_BuddyPress_Groupblog
	 */
	public $groupblog;

	/**
	 * Relative path to the Parts directory.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $parts_path = 'includes/multisite/assets/templates/buddypress/parts/';

	/**
	 * The Group Blog naming scheme settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $key_scheme = 'cpmu_bp_workshop_scheme';

	/**
	 * The Group Blog renaming enabled settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $key_enabled = 'cpmu_bp_workshop_nomenclature';

	/**
	 * The singular name of a Group Blog settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $key_singular = 'cpmu_bp_workshop_nomenclature_name';

	/**
	 * The plural name of a Group Blog settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $key_plural = 'cpmu_bp_workshop_nomenclature_plural';

	/**
	 * Group Blog slug settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $key_slug = 'cpmu_bp_workshop_nomenclature_slug';

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param CommentPress_Multisite_BuddyPress_Groupblog $groupblog Reference to the BuddyPress Groupblog object.
	 */
	public function __construct( $groupblog ) {

		// Store references.
		$this->multisite = $groupblog->bp->multisite;
		$this->bp        = $groupblog->bp;
		$this->groupblog = $groupblog;

		// Init when the BuddyPress Groupblog is fully loaded.
		add_action( 'commentpress/multisite/bp/groupblog/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this object.
	 *
	 * @since 3.3
	 */
	public function initialise() {

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Registers the callbacks for this object.
	 *
	 * @since 3.3
	 */
	public function register_hooks() {

		// Separate callbacks into descriptive methods.
		$this->register_hooks_settings();
		$this->register_hooks_filters();

		// Maybe register legacy hooks.
		$compatibility = $this->groupblog->compatibility_get();
		if ( 'legacy' === $compatibility ) {
			$this->register_hooks_legacy();
		}
	}

	/**
	 * Registers "Network Settings" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_settings() {

		// Add BuddyPress Groupblog settings to default settings.
		add_filter( 'commentpress/multisite/settings/defaults', [ $this, 'settings_get_defaults' ], 20, 1 );

		// Add our form elements to the Network Settings "BuddyPress Groupblog Settings" metabox.
		add_action( 'commentpress/multisite/settings/network/metabox/bp/groupblog/after', [ $this, 'settings_meta_box_part_get' ] );

		// Add our Javascript to the Network Settings screen.
		add_action( 'commentpress/multisite/settings/network/admin/js', [ $this, 'settings_meta_box_part_js_enqueue' ] );

		// Save data from Network Settings form submissions.
		add_action( 'commentpress/multisite/settings/network/save/before', [ $this, 'settings_meta_box_part_save' ] );

	}

	/**
	 * Registers filter hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_filters() {

		// Filter the "Visit Blog" button in BuddyPress Blog lists.
		add_filter( 'commentpress/multisite/bp/button/visit_blog/label', [ $this, 'blog_visit_blog_button_filter' ], 10, 2 );

		// Filter BuddyPress Groupblog nav item defaults.
		add_filter( 'bp_groupblog_subnav_item_name', [ $this, 'blog_name_filter' ], 20 );
		add_filter( 'bp_groupblog_subnav_item_slug', [ $this, 'blog_slug_filter' ], 20 );

		// Filter the "Welcome Page" title.
		add_filter( 'cp_nav_title_page_title', [ $this, 'nav_title_page_title_filter' ], 25 );

		// Filter the Activity Sidebar headings.
		add_filter( 'cp_activity_tab_recent_title_all_yours', [ $this, 'activity_title_all_yours_filter' ], 25 );
		add_filter( 'cp_activity_tab_recent_title_all_public', [ $this, 'activity_title_all_public_filter' ], 25 );

		// Filter the "All Recent Comments" title.
		add_filter( 'cp_activity_tab_recent_title_blog', [ $this, 'activity_tab_recent_title_blog_filter' ], 25, 1 );

		// Filter the headings on the "All Comments" Page.
		add_filter( 'cp_page_all_comments_book_title', [ $this, 'page_all_comments_book_title' ], 25, 1 );
		add_filter( 'cp_page_all_comments_blog_title', [ $this, 'page_all_comments_blog_title' ], 25, 1 );

	}

	/**
	 * Registers legacy hooks.
	 *
	 * For versions of BuddyPress Groupblog from 1.9.0 onwards, CommentPress no
	 * longer provides replacements for BuddyPress Groupblog Activity Actions.
	 *
	 * @since 4.0
	 */
	private function register_hooks_legacy() {

		// Filters the name of the Activity Post.
		add_filter( 'cp_activity_post_name', [ $this, 'activity_post_name' ], 25 );

		// Override BuddyPress Activity filter labels.
		add_filter( 'cp_groupblog_comment_name', [ $this, 'groupblog_comment_name' ], 25 );
		add_filter( 'cp_groupblog_post_name', [ $this, 'groupblog_post_name' ], 25 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Appends the BuddyPress Groupblog Names settings to the default multisite settings.
	 *
	 * @since 4.0
	 *
	 * @param array $settings The existing default multisite settings.
	 * @return array $settings The modified default multisite settings.
	 */
	public function settings_get_defaults( $settings ) {

		// Add our new BuddyPress Groupblog defaults.
		$settings[ $this->key_scheme ]   = 'groupblog';
		$settings[ $this->key_enabled ]  = 0;
		$settings[ $this->key_singular ] = $this->legacy_singular_get();
		$settings[ $this->key_plural ]   = $this->legacy_plural_get();
		$settings[ $this->key_slug ]     = $this->legacy_slug_get();

		// --<
		return $settings;

	}

	/**
	 * Adds our form elements to the Network Settings "BuddyPress Groupblog Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_part_get() {

		// Get the current naming scheme.
		$current_scheme = $this->setting_scheme_get();

		// Build scheme options.
		$schemes           = $this->schemes_get();
		$groupblog_schemes = [];
		foreach ( $schemes as $scheme => $names ) {
			$groupblog_schemes[ $scheme ] = sprintf( '%1$s / %2$s', $names['singular'], $names['plural'] );
		}

		// Get custom naming scheme settings.
		$enabled  = $this->setting_enabled_get();
		$singular = $this->setting_singular_get();
		$plural   = $this->setting_plural_get();
		$slug     = $this->setting_slug_get();

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-bp-groupblog-names-settings-network.php';

	}

	/**
	 * Adds our Javascript to the Network Settings screen.
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_part_js_enqueue() {

		// Add our Javascript.
		wp_enqueue_script(
			'commentpress_bp_groupblog_names',
			plugins_url( 'includes/multisite/assets/js/cp-bp-groupblog-names-settings-network.js', COMMENTPRESS_PLUGIN_FILE ),
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
	public function settings_meta_box_part_save() {

		// Get "Group Blog naming scheme" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$scheme = isset( $_POST[ $this->key_scheme ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_scheme ] ) ) : '';

		// Set the setting.
		$this->setting_scheme_set( $scheme );

		// Get "Group Blog renaming enabled" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$enabled = isset( $_POST[ $this->key_enabled ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_enabled ] ) ) : '0';

		// Set the setting.
		$this->setting_enabled_set( ( $enabled ? 1 : 0 ) );

		// Get "singular name of a Group Blog" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$singular = isset( $_POST[ $this->key_singular ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_singular ] ) ) : '';

		// Set the setting.
		$this->setting_singular_set( $singular );

		// Get "plural name of a Group Blog" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$plural = isset( $_POST[ $this->key_plural ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_plural ] ) ) : '';

		// Set the setting.
		$this->setting_plural_set( $plural );

		// Get "Group Blog slug" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$slug = isset( $_POST[ $this->key_slug ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_slug ] ) ) : '';

		// Set the setting.
		$this->setting_slug_set( $slug );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the "Group Blog renaming enabled" setting.
	 *
	 * @since 4.0
	 *
	 * @return bool $enabled True if Group Blog renaming is enabled, false otherwise.
	 */
	public function setting_enabled_get() {

		// Get the setting.
		$enabled = $this->multisite->db->setting_get( $this->key_enabled );

		// Return a boolean.
		return ! empty( $enabled ) ? true : false;

	}

	/**
	 * Sets the "Group Blog renaming enabled" setting.
	 *
	 * @since 4.0
	 *
	 * @param int|bool $enabled True if Group Blog renaming is enabled, false otherwise.
	 */
	public function setting_enabled_set( $enabled ) {

		// Set the setting.
		$this->multisite->db->setting_set( $this->key_enabled, ( $enabled ? 1 : 0 ) );

	}

	/**
	 * Gets the "singular name of a Group Blog" setting.
	 *
	 * @since 4.0
	 *
	 * @return string $singular The singular name of a Group Blog if found, false otherwise.
	 */
	public function setting_singular_get() {

		// Get the setting.
		$singular = $this->multisite->db->setting_get( $this->key_singular );

		// Return setting or legacy name if empty.
		return ! empty( $singular ) ? $singular : $this->legacy_singular_get();

	}

	/**
	 * Sets the "singular name of a Group Blog" setting.
	 *
	 * @since 4.0
	 *
	 * @param string $singular The singular name of a Group Blog.
	 */
	public function setting_singular_set( $singular ) {

		// Set the setting.
		$this->multisite->db->setting_set( $this->key_singular, $singular );

	}

	/**
	 * Deletes the "singular name of a Group Blog" setting.
	 *
	 * @since 4.0
	 */
	public function setting_singular_delete() {

		// Delete the setting.
		$this->multisite->db->setting_delete( $this->key_singular );

	}

	/**
	 * Gets the "plural name of a Group Blog" setting.
	 *
	 * @since 4.0
	 *
	 * @return string $plural The plural name of a Group Blog if found, false otherwise.
	 */
	public function setting_plural_get() {

		// Get the setting.
		$plural = $this->multisite->db->setting_get( $this->key_plural );

		// Return setting or legacy plural if empty.
		return ! empty( $plural ) ? $plural : $this->legacy_plural_get();

	}

	/**
	 * Deletes the "plural name of a Group Blog" setting.
	 *
	 * @since 4.0
	 */
	public function setting_plural_delete() {

		// Delete the setting.
		$this->multisite->db->setting_delete( $this->key_plural );

	}

	/**
	 * Sets the "plural name of a Group Blog" setting.
	 *
	 * @since 4.0
	 *
	 * @param string $plural The plural name of a Group Blog.
	 */
	public function setting_plural_set( $plural ) {

		// Set the setting.
		$this->multisite->db->setting_set( $this->key_plural, $plural );

	}

	/**
	 * Gets the "Group Blog slug" setting.
	 *
	 * @since 4.0
	 *
	 * @return string $slug The slug for Group Blogs if found, false otherwise.
	 */
	public function setting_slug_get() {

		// Get the setting.
		$slug = $this->multisite->db->setting_get( $this->key_slug );

		// Return setting or legacy slug if empty.
		return ! empty( $slug ) ? $slug : $this->legacy_slug_get();

	}

	/**
	 * Sets the "Group Blog slug" setting.
	 *
	 * @since 4.0
	 *
	 * @param string $slug The slug for Group Blogs.
	 */
	public function setting_slug_set( $slug ) {

		// Set the setting.
		$this->multisite->db->setting_set( $this->key_slug, $slug );

	}

	/**
	 * Deletes the "Group Blog slug" setting.
	 *
	 * @since 4.0
	 */
	public function setting_slug_delete() {

		// Delete the setting.
		$this->multisite->db->setting_delete( $this->key_slug );

	}

	/**
	 * Gets the "Group Blog naming scheme" setting.
	 *
	 * @since 4.0
	 *
	 * @return string $scheme The naming scheme for Group Blogs if found, false otherwise.
	 */
	public function setting_scheme_get() {

		// Get the setting.
		$scheme = $this->multisite->db->setting_get( $this->key_scheme );

		// Return setting or legacy slug if empty.
		return ! empty( $scheme ) ? $scheme : $this->legacy_slug_get();

	}

	/**
	 * Sets the "Group Blog naming scheme" setting.
	 *
	 * @since 4.0
	 *
	 * @param string $scheme The naming scheme for Group Blogs.
	 */
	public function setting_scheme_set( $scheme ) {

		// Set the setting.
		$this->multisite->db->setting_set( $this->key_scheme, $scheme );

	}

	// -------------------------------------------------------------------------

	/**
	 * Overrides the name of the sub-nav item.
	 *
	 * @since 3.3
	 *
	 * @param string $name The existing name of a "blog".
	 * @return string $name The modified name of a "blog".
	 */
	public function blog_name_filter( $name ) {

		// Bail if we don't have a Group Blog Text Format.
		$groupblog_text_format = $this->groupblog->group_type_get( bp_get_current_group_id() );
		if ( empty( $groupblog_text_format ) ) {
			return $name;
		}

		// Assign scheme singular.
		$name = $this->scheme_singular_get();

		// --<
		return $name;

	}

	/**
	 * Overrides the slug of the sub-nav item.
	 *
	 * @since 3.3
	 *
	 * @param string $slug The existing slug of a "blog".
	 * @return string $slug The modified slug of a "blog".
	 */
	public function blog_slug_filter( $slug ) {

		// Bail if we don't have a Group Blog Text Format.
		$groupblog_text_format = $this->groupblog->group_type_get( bp_get_current_group_id() );
		if ( empty( $groupblog_text_format ) ) {
			return $slug;
		}

		// Assign scheme slug.
		$slug = $this->scheme_slug_get();

		// --<
		return $slug;

	}

	/**
	 * Overrides the name of the button on the BuddyPress "blogs" screen.
	 *
	 * The incoming button array looks like this:
	 *
	 * [id] => visit_blog
	 * [component] => blogs
	 * [must_be_logged_in] =>
	 * [block_self] =>
	 * [wrapper_class] => blog-button visit
	 * [link_href] => https://domain/site-slug/
	 * [link_class] => blog-button visit
	 * [link_text] => Visit Site
	 * [link_title] => Visit Site
	 *
	 * @since 3.3
	 *
	 * @param string $label The existing Blogs button label.
	 * @param string $site_text_format The text format of Blog. Either 'blog' or 'commentpress'.
	 * @return string $label The modified Blogs button label.
	 */
	public function blog_visit_blog_button_filter( $label, $site_text_format ) {

		// Bail if BuddyPress Groupblog is not present.
		if ( ! function_exists( 'get_groupblog_group_id' ) ) {
			return $label;
		}

		// Access global.
		global $blogs_template;

		// Get Group ID.
		$group_id = get_groupblog_group_id( $blogs_template->blog->blog_id );
		if ( empty( $group_id ) || ! is_numeric( $group_id ) ) {
			return $label;
		}

		// Default to standard Group Blog.
		$site_text_format = 'groupblog';

		// Override if the Group has a CommentPress-enabled Group Blog.
		$groupblog_text_format = $this->groupblog->group_type_get( $group_id );
		if ( ! empty( $groupblog_text_format ) ) {
			$site_text_format = 'commentpress-groupblog';
		}

		// Return early for standard Group Blogs.
		if ( 'groupblog' === $site_text_format ) {
			$label = __( 'View Group Blog', 'commentpress-core' );
			return $label;
		}

		// Use the naming scheme singular.
		$label = sprintf(
			/* translators: %s: The singular name for a Group Blog. */
			__( 'View %s', 'commentpress-core' ),
			$this->scheme_singular_get()
		);

		// --<
		return $label;

	}

	// -------------------------------------------------------------------------

	/**
	 * Override the name of the filter item.
	 *
	 * @since 3.3
	 *
	 * @return string The name in the Group Blog Comments label.
	 */
	public function groupblog_comment_name() {

		// Default name.
		return sprintf(
			/* translators: %s: The singular name for a Group Blog. */
			__( '%s Comments', 'commentpress-core' ),
			$this->scheme_singular_get()
		);

	}

	/**
	 * Override the name of the filter item.
	 *
	 * @since 3.3
	 *
	 * @return string The plural name in the Group Blog Posts label.
	 */
	public function groupblog_post_name() {

		// Default name.
		return sprintf(
			/* translators: %s: The singular name for a Group Blog. */
			__( '%s Posts', 'commentpress-core' ),
			$this->scheme_singular_get()
		);

	}

	// -------------------------------------------------------------------------

	/**
	 * Override the name of the filter item.
	 *
	 * @since 3.3
	 *
	 * @return string The singular name of the Group Blog Post.
	 */
	public function activity_post_name() {

		// Default name.
		return sprintf(
			/* translators: %s: The lowercase singular name for a Group Blog. */
			__( '%s post', 'commentpress-core' ),
			strtolower( $this->scheme_singular_get() )
		);

	}

	/**
	 * Override the title of the "Recent Comments in..." link.
	 *
	 * @since 3.3
	 *
	 * @param string $title The title of the Recent Comments heading.
	 * @return string $title The modified title of the Recent Comments heading.
	 */
	public function activity_tab_recent_title_blog_filter( $title ) {

		// Get core plugin reference.
		$core = commentpress_core();

		// Init title.
		$recent_title = '';

		// Build title for Group Blogs.
		if ( ! empty( $core ) && $core->bp->is_groupblog() ) {
			$recent_title = sprintf(
				/* translators: %s: The singular name for a Group Blog. */
				__( 'Recent Comments in this %s', 'commentpress-core' ),
				$this->scheme_singular_get()
			);
		}

		// Build title for Main Site.
		if ( is_multisite() && is_main_site() ) {
			$recent_title = __( 'Recent Comments in Site Blog', 'commentpress-core' );
		}

		// Maybe apply to title.
		if ( ! empty( $recent_title ) ) {

			/**
			 * Filters the Recent Comments title.
			 *
			 * @since 3.4
			 *
			 * @param string $recent_title The default Recent Comments title.
			 */
			$title = apply_filters( 'cpmsextras_user_links_new_site_title', $recent_title );

		}

		// --<
		return $title;

	}

	/**
	 * Override title on Activity tab.
	 *
	 * @since 3.3
	 *
	 * @param string $title The title of the "Recent Activity in..." heading.
	 * @return string $title The modified title of the "Recent Activity in..." heading.
	 */
	public function activity_title_all_yours_filter( $title ) {

		// Override if Group Blog.
		if (
			! bp_is_root_blog() &&
			! $this->groupblog->site->is_commentpress_groupblog()
		) {
			return $title;
		}

		// --<
		return sprintf(
			/* translators: %s: The plural name for a Group Blog. */
			__( 'Recent Activity in your %s', 'commentpress-core' ),
			$this->scheme_plural_get()
		);

	}

	/**
	 * Override title on Activity tab.
	 *
	 * @since 3.3
	 *
	 * @param string $title The title of the "Recent Activity in..." heading.
	 * @return string $title The modified title of the "Recent Activity in..." heading.
	 */
	public function activity_title_all_public_filter( $title ) {

		// Override if Group Blog.
		if (
			! bp_is_root_blog() &&
			! $this->groupblog->site->is_commentpress_groupblog()
		) {
			return $title;
		}

		// --<
		return sprintf(
			/* translators: %s: The plural name for a Group Blog. */
			__( 'Recent Activity in Public %s', 'commentpress-core' ),
			$this->scheme_plural_get()
		);

	}

	// -------------------------------------------------------------------------

	/**
	 * Override title on All Comments Page.
	 *
	 * @since 3.3
	 *
	 * @param string $title The title of the All Comments heading.
	 * @return string $title The modified title of the All Comments heading.
	 */
	public function page_all_comments_blog_title( $title ) {

		// Bail if not a CommentPress-enabled Group Blog.
		if ( ! $this->groupblog->site->is_commentpress_groupblog() ) {
			return $title;
		}

		// --<
		return sprintf(
			/* translators: %s: The singular name for a Group Blog. */
			__( 'Comments on %s Posts', 'commentpress-core' ),
			$this->scheme_singular_get()
		);

	}

	/**
	 * Override title on All Comments Page.
	 *
	 * @since 3.3
	 *
	 * @param string $title The title of the "Comments on..." heading.
	 * @return string $title The modified title of the "Comments on..." heading.
	 */
	public function page_all_comments_book_title( $title ) {

		// Override if Group Blog.
		if ( ! $this->groupblog->site->is_commentpress_groupblog() ) {
			return $title;
		}

		// --<
		return sprintf(
			/* translators: %s: The singular name for a Group Blog. */
			__( 'Comments on %s Pages', 'commentpress-core' ),
			$this->scheme_singular_get()
		);

	}

	// -------------------------------------------------------------------------

	/**
	 * Override CommentPress Core "Welcome Page".
	 *
	 * @since 3.3
	 *
	 * @param string $title The title of the "Group Blog Home Page" heading.
	 * @return string $title The modified title of the "Group Blog Home Page" heading.
	 */
	public function nav_title_page_title_filter( $title ) {

		// Bail if main BuddyPress Site.
		if ( bp_is_root_blog() ) {
			return $title;
		}

		// Bail if not Group Blog.
		if ( ! $this->groupblog->site->is_commentpress_groupblog() ) {
			return $title;
		}

		// --<
		return sprintf(
			/* translators: %s: The singular name for a Group Blog. */
			__( '%s Home Page', 'commentpress-core' ),
			$this->scheme_singular_get()
		);

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the array of Group Blog naming schemes.
	 *
	 * @since 4.0
	 *
	 * @return array $schemes The array of Group Blog naming schemes.
	 */
	public function schemes_get() {

		// Define schemes.
		$schemes = [

			// BuddyPress Groupblog default.
			'groupblog'              => [
				'singular' => __( 'Group Blog', 'commentpress-core' ),
				'plural'   => __( 'Group Blogs', 'commentpress-core' ),
			],

			// CommentPress "document".
			'document'               => [
				'singular' => __( 'Document', 'commentpress-core' ),
				'plural'   => __( 'Documents', 'commentpress-core' ),
			],

			// CommentPress "class".
			'class'                  => [
				'singular' => __( 'Class', 'commentpress-core' ),
				'plural'   => __( 'Classes', 'commentpress-core' ),
			],

			// Legacy "workshop".
			$this->legacy_slug_get() => [
				'singular' => $this->legacy_singular_get(),
				'plural'   => $this->legacy_plural_get(),
			],

		];

		/**
		 * Filters the array of Group Blog naming schemes.
		 *
		 * @since 4.0
		 *
		 * @param array $schemes The array of Group Blog naming schemes.
		 */
		return apply_filters( 'commentpress/multisite/bp/groupblog/schemes', $schemes );

	}

	/**
	 * Gets the singular name for Group Blogs.
	 *
	 * @since 4.0
	 *
	 * @return string $singular The singular name for Group Blogs.
	 */
	public function scheme_singular_get() {

		// Use the custom naming scheme singular if enabled.
		$enabled = $this->setting_enabled_get();
		$single  = $this->setting_singular_get();
		if ( ! empty( $enabled ) && ! empty( $single ) ) {
			return $single;
		}

		// Use the current naming scheme singular if not enabled.
		if ( empty( $enabled ) ) {

			// Get the scheme singular name.
			$current_scheme = $this->setting_scheme_get();
			$schemes        = $this->schemes_get();

			foreach ( $schemes as $scheme => $names ) {
				if ( $current_scheme === $scheme ) {
					return $names['singular'];
				}
			}

		}

		// Fallback to legacy.
		return $this->legacy_singular_get();

	}

	/**
	 * Gets the plural name for Group Blogs.
	 *
	 * @since 4.0
	 *
	 * @return string $plural The plural name for Group Blogs.
	 */
	public function scheme_plural_get() {

		// Use the custom naming scheme plural if enabled.
		$enabled = $this->setting_enabled_get();
		$plural  = $this->setting_plural_get();
		if ( ! empty( $enabled ) && ! empty( $plural ) ) {
			return $plural;
		}

		// Use the current naming scheme plural if not enabled.
		if ( empty( $enabled ) ) {

			// Get the scheme plural name.
			$current_scheme = $this->setting_scheme_get();
			$schemes        = $this->schemes_get();

			foreach ( $schemes as $scheme => $names ) {
				if ( $current_scheme === $scheme ) {
					return $names['plural'];
				}
			}

		}

		// Fallback to legacy.
		return $this->legacy_plural_get();

	}

	/**
	 * Gets the slug for Group Blogs.
	 *
	 * @since 4.0
	 *
	 * @return string $slug The slug for Group Blogs.
	 */
	public function scheme_slug_get() {

		// Use the custom naming scheme slug if enabled.
		$enabled = $this->setting_enabled_get();
		$slug    = $this->setting_slug_get();
		if ( ! empty( $enabled ) && ! empty( $slug ) ) {
			return $slug;
		}

		// Use the current naming scheme slug if not enabled.
		if ( empty( $enabled ) ) {

			// Get the scheme slug.
			$current_scheme = $this->setting_scheme_get();
			$schemes        = $this->schemes_get();

			foreach ( $schemes as $scheme => $names ) {
				if ( $current_scheme === $scheme ) {
					return $scheme;
				}
			}

		}

		// Fallback to legacy.
		return $this->legacy_slug_get();

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the legacy Group Blog naming scheme.
	 *
	 * @since 4.0
	 *
	 * @return array $schemes The array of Group Blog naming schemes.
	 */
	public function legacy_scheme_get() {
		return [
			$this->legacy_slug_get() => [
				'singular' => $this->legacy_singular_get(),
				'plural'   => $this->legacy_plural_get(),
			],
		];
	}

	/**
	 * Get legacy singular name when already set.
	 *
	 * @since 3.3
	 *
	 * @return string $singular The legacy singular name of a Group Blog.
	 */
	public function legacy_singular_get() {
		return __( 'Workshop', 'commentpress-core' );
	}

	/**
	 * Get legacy plural name when already set.
	 *
	 * @since 3.3
	 *
	 * @return string $plural The legacy plural name of a Group Blog.
	 */
	public function legacy_plural_get() {
		return __( 'Workshops', 'commentpress-core' );
	}

	/**
	 * Get legacy slug when already set.
	 *
	 * @since 3.3
	 *
	 * @return string $slug The legacy slug of a Group Blog.
	 */
	public function legacy_slug_get() {
		return 'workshop';
	}

}
