<?php
/**
 * CommentPress Core Legacy Pages class.
 *
 * Handles functionality related to legacy "Special Pages" in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Legacy Pages Class.
 *
 * This class provides functionality related to legacy "Special Pages" in CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Pages_Legacy {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * Constructor.
	 *
	 * @since 4.0
	 *
	 * @param object $core Reference to the core plugin object.
	 */
	public function __construct( $core ) {

		// Store reference to core plugin object.
		$this->core = $core;

		// Init when this plugin is fully loaded.
		add_action( 'commentpress/core/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this object.
	 *
	 * @since 4.0
	 */
	public function initialise() {

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

		/*
		// TODO: Build new Special Pages functionality.
		$this->register_hooks_settings();
		*/

		// Separate callbacks into descriptive methods.
		$this->register_hooks_activation();
		$this->register_hooks_pages();

	}

	/**
	 * Registers "Site Settings" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_settings() {

		// Add our settings to default settings.
		add_filter( 'commentpress/core/settings/defaults', [ $this, 'settings_get_defaults' ] );

		// Add our metaboxes to the Site Settings screen.
		add_filter( 'commentpress/core/settings/site/metaboxes/after', [ $this, 'settings_meta_boxes_append' ], 40 );

		// Save data from Site Settings form submissions.
		add_action( 'commentpress/core/settings/site/save/before', [ $this, 'settings_meta_box_save' ] );

	}

	/**
	 * Registers activation/deactivation hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_activation() {

		// Acts late when this plugin is activated.
		add_action( 'commentpress/core/activate', [ $this, 'plugin_activate' ], 40 );

		// Act early when this plugin is deactivated.
		add_action( 'commentpress/core/deactivate', [ $this, 'plugin_deactivate' ], 20 );

	}

	/**
	 * Registers Page-related hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_pages() {

		// Intercept Welcome Page delete.
		add_action( 'before_delete_post', [ $this, 'title_page_pre_delete' ], 10, 1 );

		// Exclude Special Pages from listings.
		add_filter( 'wp_list_pages_excludes', [ $this, 'special_pages_exclude' ], 10, 1 );
		add_filter( 'parse_query', [ $this, 'special_pages_exclude_from_admin' ], 10, 1 );

		// Modify Page count in listings.
		add_filter( 'views_edit-page', [ $this, 'update_page_counts_in_admin' ], 10, 1 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Appends our settings to the default core settings.
	 *
	 * @since 4.0
	 *
	 * @param array $settings The existing default core settings.
	 * @return array $settings The modified default core settings.
	 */
	public function settings_get_defaults( $settings ) {

		// --<
		return $settings;

	}

	/**
	 * Appends our metaboxes to the Site Settings screen.
	 *
	 * @since 4.0
	 *
	 * @param string $screen_id The Site Settings Screen ID.
	 */
	public function settings_meta_boxes_append( $screen_id ) {

		// Create "Special Pages" metabox.
		add_meta_box(
			'commentpress_special',
			__( 'Special Pages', 'commentpress-core' ),
			[ $this, 'settings_meta_box_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

	}

	/**
	 * Renders the "Special Pages" metabox.
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_render() {

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-site-special.php';

	}

	/**
	 * Saves the data from the Site Settings "Special Pages" metabox.
	 *
	 * Adds the data to the settings array. The settings are actually saved later.
	 *
	 * @see CommentPress_Core_Settings_Site::form_submitted()
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_save() {

		// Init vars.
		$cp_create_pages = '';
		$cp_delete_pages = '';

		// Did we ask to auto-create Special Pages?
		if ( '1' == $cp_create_pages ) {

			// Remove any existing Special Pages.
			$this->special_pages_delete();

			// Create fresh Special Pages.
			$this->special_pages_create();

		}

		// Did we ask to delete Special Pages?
		if ( '1' == $cp_delete_pages ) {
			$this->special_pages_delete();
		}

		// Let's deal with our params now.

		/*
		// Individual Special Pages.
		$cp_welcome_page = esc_sql( $cp_welcome_page );
		$cp_blog_page = esc_sql( $cp_blog_page );
		$cp_general_comments_page = esc_sql( $cp_general_comments_page );
		$cp_all_comments_page = esc_sql( $cp_all_comments_page );
		$cp_comments_by_page = esc_sql( $cp_comments_by_page );
		$this->core->db->setting_set( 'cp_welcome_page', $cp_welcome_page );
		$this->core->db->setting_set( 'cp_blog_page', $cp_blog_page );
		$this->core->db->setting_set( 'cp_general_comments_page', $cp_general_comments_page );
		$this->core->db->setting_set( 'cp_all_comments_page', $cp_all_comments_page );
		$this->core->db->setting_set( 'cp_comments_by_page', $cp_comments_by_page );
		*/

	}

	// -------------------------------------------------------------------------

	/**
	 * Runs when core is activated.
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_activate( $network_wide = false ) {

		// Bail if plugin is network activated.
		if ( $network_wide ) {
			return;
		}

		// Create the Legacy Pages.
		$this->activate();

	}

	/**
	 * Runs when core is deactivated.
	 *
	 * NOTE: The database schema is only restored in "uninstall.php" when this
	 * plugin is deleted.
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_deactivate( $network_wide = false ) {

		/*
		// Bail if plugin is network activated.
		if ( $network_wide ) {
			return;
		}
		*/

		// Remove the Legacy Pages.
		$this->deactivate();

	}

	// -------------------------------------------------------------------------

	/**
	 * Creates the Legacy Pages.
	 *
	 * @since 3.0
	 */
	public function activate() {

		// Retrieve data on Special Pages.
		$special_pages = $this->core->db->setting_get( 'cp_special_pages', [] );

		// Bail if we have already created them.
		if ( ! empty( $special_pages ) ) {
			return;
		}

		// Create the Welcome Page.
		$this->title_page_create();

		// Create all Special Pages.
		$this->special_pages_create();

	}

	/**
	 * Removes the Legacy Pages.
	 *
	 * @since 3.0
	 */
	public function deactivate() {

		// Remove all Special Pages.
		$this->special_pages_delete();

		// Disable the Welcome Page.
		$this->title_page_disable();

	}

	// -------------------------------------------------------------------------

	/**
	 * Creates the Welcome Page.
	 *
	 * @since 3.4
	 *
	 * @return int $title_id The numeric ID of the Welcome Page.
	 */
	public function title_page_create() {

		// Get the option, if it exists.
		$page_exists = $this->core->db->setting_get( 'cp_welcome_page', false );

		// Don't create if we already have the option set.
		if ( ! empty( $page_exists ) && is_numeric( $page_exists ) ) {

			// Get the Page - the plugin may have been deactivated, then the Page deleted.
			$welcome = get_post( $page_exists );
			if ( $welcome instanceof WP_Post ) {

				// Got it. We still ought to set WordPress internal Page references.
				$this->core->db->option_wp_backup( 'show_on_front', 'page' );
				$this->core->db->option_wp_backup( 'page_on_front', $page_exists );

				// --<
				return $page_exists;

			}

			// Page does not exist, continue on and create it.

		}

		// Define Welcome Page.
		$title = [
			'post_status'           => 'publish',
			'post_type'             => 'page',
			'post_parent'           => 0,
			'comment_status'        => 'open',
			'ping_status'           => 'closed',
			'to_ping'               => '', // Quick fix for Windows.
			'pinged'                => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt'          => '', // Quick fix for Windows.
			'menu_order'            => 0,
		];

		// Add Post-specific stuff.

		// Default Page title.
		$default_title = __( 'Title Page', 'commentpress-core' );

		/**
		 * Filters the Welcome Page title.
		 *
		 * @since 3.4
		 *
		 * @param string $default_title The default title of the Page.
		 */
		$title['post_title'] = apply_filters( 'cp_title_page_title', $default_title );

		// Default content.
		$content = __(
			'Welcome to your new CommentPress site, which allows your readers to comment paragraph-by-paragraph or line-by-line in the margins of a text. Annotate, gloss, workshop, debate: with CommentPress you can do all of these things on a finer-grained level, turning a document into a conversation.

This is your title page. Edit it to suit your needs. It has been automatically set as your homepage but if you want another page as your homepage, set it in <em>WordPress</em> &#8594; <em>Settings</em> &#8594; <em>Reading</em>.

You can also set a number of options in <em>WordPress</em> &#8594; <em>Settings</em> &#8594; <em>CommentPress</em> to make the site work the way you want it to. Use the Theme Customizer to change the way your site looks in <em>WordPress</em> &#8594; <em>Appearance</em> &#8594; <em>Customize</em>. For help with structuring, formatting and reading text in CommentPress, please refer to the <a href="http://www.futureofthebook.org/commentpress/">CommentPress website</a>.',
			'commentpress-core'
		);

		/**
		 * Filters the Welcome Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $content The default content of the Page.
		 */
		$title['post_content'] = apply_filters( 'cp_title_page_content', $content );

		/**
		 * Filters the Welcome Page template.
		 *
		 * @since 3.4
		 *
		 * @param string The default template of the Page.
		 */
		$title['page_template'] = apply_filters( 'cp_title_page_template', 'welcome.php' );

		// Insert the Post into the database.
		$title_id = wp_insert_post( $title );

		// Store the option.
		$this->core->db->setting_set( 'cp_welcome_page', $title_id );

		// Set WordPress internal Page references.
		$this->core->db->option_wp_backup( 'show_on_front', 'page' );
		$this->core->db->option_wp_backup( 'page_on_front', $title_id );

		/**
		 * Fires when the Welcome Page has been created.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Core_Entry_Formatter::default_set_for_post() (Priority: 10)
		 *
		 * @since 4.0
		 *
		 * @param int $title_id The numeric ID of the new Page.
		 */
		do_action( 'commentpress/core/db/page/special/title/created', $title_id );

		// --<
		return $title_id;

	}

	/**
	 * Deletes the Welcome Page setting when the Welcome Page is deleted.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed and moved to this class.
	 *
	 * @param int $post_id The numeric ID of the Post or Revision.
	 */
	public function title_page_pre_delete( $post_id ) {

		// If no Post, kick out.
		if ( ! $post_id ) {
			return;
		}

		// Bail if it's not our Welcome Page.
		if ( $post_id !== (int) $this->core->db->setting_get( 'cp_welcome_page' ) ) {
			return;
		}

		// Delete option.
		$this->core->db->setting_delete( 'cp_welcome_page' );

		// Save changes.
		$this->core->db->settings_save();

	}

	/**
	 * Deletes the Welcome Page.
	 *
	 * @since 4.0
	 *
	 * @return bool $success True if succesfully deleted, false otherwise.
	 */
	public function title_page_delete() {

		// Get the ID if it exists.
		$existing_id = $this->core->db->setting_get( 'cp_welcome_page', false );
		if ( empty( $existing_id ) ) {
			return false;
		}

		// Try and delete the Page, bypassing trash.
		$force_delete = true;
		if ( ! wp_delete_post( $existing_id, $force_delete ) ) {
			return false;
		}

		// Make sure setting is deleted.
		if ( $this->core->db->setting_exists( 'cp_welcome_page' ) ) {
			$this->core->db->setting_delete( 'cp_welcome_page' );
			$this->core->db->settings_save();
		}

		// Reset WordPress internal Page references.
		$this->core->db->option_wp_restore( 'show_on_front' );
		$this->core->db->option_wp_restore( 'page_on_front' );

		// --<
		return true;

	}

	/**
	 * Enables the Welcome Page.
	 *
	 * @since 4.0
	 *
	 * @return int|bool $post_id The numeric ID of the Welcome Page, or false on failure.
	 */
	public function title_page_enable() {

		// Get the ID if it exists.
		$existing_id = $this->core->db->setting_get( 'cp_welcome_page', false );
		if ( empty( $existing_id ) ) {
			return false;
		}

		// Define args to update the Post.
		$args = [
			'ID'          => $existing_id,
			'post_status' => 'publish',
		];

		// Update the Post.
		$post_id = wp_update_post( $args, true );

		// Bail on failure.
		if ( is_wp_error( $post_id ) ) {
			return false;
		}

		// --<
		return $post_id;

	}

	/**
	 * Disables the Welcome Page.
	 *
	 * The Welcome Page is not deleted in case people have modified it.
	 *
	 * The "cp_welcome_page" setting is deleted if the Welcome Page is maunally
	 * deleted.
	 *
	 * @since 4.0
	 *
	 * @return int|bool $post_id The numeric ID of the Welcome Page, or false on failure.
	 */
	public function title_page_disable() {

		// Get the ID if it exists.
		$existing_id = $this->core->db->setting_get( 'cp_welcome_page', false );
		if ( empty( $existing_id ) ) {
			return false;
		}

		// Define args to update the Post.
		$args = [
			'ID'          => $existing_id,
			'post_status' => 'draft',
		];

		// Update the Post.
		$post_id = wp_update_post( $args, true );

		// Bail on failure.
		if ( is_wp_error( $post_id ) ) {
			return false;
		}

		// Reset WordPress internal Page references.
		$this->core->db->option_wp_restore( 'show_on_front' );
		$this->core->db->option_wp_restore( 'page_on_front' );

		// --<
		return $post_id;

	}

	/**
	 * Checks if the CommentPress "Welcome Page" is the Front Page.
	 *
	 * @since 3.0
	 *
	 * @return bool|int $is_home False if not Front Page, the ID of the Welcome Page if true.
	 */
	public function is_title_page_the_homepage() {

		// Only need to parse this once.
		static $is_home;
		if ( isset( $is_home ) ) {
			return $is_home;
		}

		// Get Welcome Page ID.
		$welcome_id = $this->core->db->setting_get( 'cp_welcome_page' );

		// Get Front Page ID.
		$page_on_front = $this->core->db->option_wp_get( 'page_on_front' );

		// If the Welcome Page exists and it's the Front Page.
		if ( false !== $welcome_id && $page_on_front == $welcome_id ) {
			$is_home = $welcome_id;
		} else {
			$is_home = false;
		}

		// --<
		return $is_home;

	}

	// -------------------------------------------------------------------------

	/**
	 * Creates all Special Pages.
	 *
	 * @since 3.4
	 */
	public function special_pages_create() {

		/*
		 * One of the CommentPress Core themes MUST be active or WordPress will
		 * fail to set the Page templates for the Pages that require them.
		 *
		 * Also, a User must be logged in for these Pages to be associated with them.
		 *
		 * TODO: Remove the Page templates and use "the_content" filter instead.
		 */

		// Get Special Pages array, if it's there.
		$special_pages = $this->core->db->setting_get( 'cp_special_pages', [] );

		// Create General Comments Page.
		$general_comments_page_id = $this->general_comments_page_create();
		if ( false !== $general_comments_page_id ) {
			$special_pages[] = $general_comments_page_id;
		}

		// Create All Comments Page.
		$all_comments_page_id = $this->all_comments_page_create();
		if ( false !== $all_comments_page_id ) {
			$special_pages[] = $all_comments_page_id;
		}

		// Create Comments by Author Page.
		$comments_by_author_page_id = $this->comments_by_author_page_create();
		if ( false !== $comments_by_author_page_id ) {
			$special_pages[] = $comments_by_author_page_id;
		}

		// Create Blog Page.
		$blog_page_id = $this->blog_page_create();
		if ( false !== $blog_page_id ) {
			$special_pages[] = $blog_page_id;
		}

		// Create Blog Archive Page.
		$blog_archive_page_id = $this->blog_archive_page_create();
		if ( false !== $blog_archive_page_id ) {
			$special_pages[] = $blog_archive_page_id;
		}

		// Create TOC Page -> a convenience, let's us define a logo as attachment.
		$toc_page_id = $this->toc_page_create();
		if ( false !== $toc_page_id ) {
			$special_pages[] = $toc_page_id;
		}

		// Store the array of Page IDs that were created.
		$this->core->db->setting_set( 'cp_special_pages', $special_pages );

		// Save changes.
		$this->core->db->settings_save();

	}

	/**
	 * Deletes all Special Pages.
	 *
	 * @since 3.4
	 */
	public function special_pages_delete() {

		// Try to retrieve data for Special Pages.
		$special_pages = $this->core->db->setting_get( 'cp_special_pages', [] );
		if ( empty( $special_pages ) ) {
			return;
		}

		// Build an array of the individual Special Page IDs, keyed by "name".
		$special_pages_keyed = [
			'cp_general_comments_page' => $this->core->db->setting_get( 'cp_general_comments_page', false ),
			'cp_all_comments_page'     => $this->core->db->setting_get( 'cp_all_comments_page', false ),
			'cp_comments_by_page'      => $this->core->db->setting_get( 'cp_comments_by_page', false ),
			'cp_blog_page'             => $this->core->db->setting_get( 'cp_blog_page', false ),
			'cp_blog_archive_page'     => $this->core->db->setting_get( 'cp_blog_archive_page', false ),
			'cp_toc_page'              => $this->core->db->setting_get( 'cp_toc_page', false ),
		];

		// Try and delete each Page, bypassing trash.
		foreach ( $special_pages as $special_page_id ) {

			// Skip if this Special Page is somehow missing.
			$name = array_search( $special_page_id, $special_pages_keyed );
			if ( false === $name ) {
				continue;
			}

			// Try to delete the Special Page, bypassing trash.
			$force_delete = true;
			if ( ! wp_delete_post( $special_page_id, $force_delete ) ) {
				continue;
			}

			// Delete the corresponding individual option.
			$this->core->db->setting_delete( $name );

			// For the Blog Page, restore the original WordPress Blog Page.
			if ( 'cp_blog_page' === $name ) {
				$this->core->db->option_wp_restore( 'page_for_posts' );
			}

		}

		// Delete the corresponding options.
		$this->core->db->setting_delete( 'cp_special_pages' );

		// Save changes.
		$this->core->db->settings_save();

	}

	/**
	 * Excludes all Special Pages from Page listings.
	 *
	 * @since 3.4
	 *
	 * @param array $excluded_array The existing list of excluded Pages.
	 * @return array $excluded_array The modified list of excluded Pages.
	 */
	public function special_pages_exclude( $excluded_array ) {

		// Get Special Pages array, if it's there.
		$special_pages = $this->core->db->setting_get( 'cp_special_pages' );

		// Merge and make unique if we have an array.
		if ( ! empty( $special_pages ) ) {
			$excluded_array = array_unique( array_merge( $excluded_array, $special_pages ) );
		}

		// --<
		return $excluded_array;

	}

	/**
	 * Excludes all Special Pages from Admin Page listings.
	 *
	 * @since 3.4
	 *
	 * @param array $query The existing Page query.
	 */
	public function special_pages_exclude_from_admin( $query ) {

		global $pagenow, $post_type;

		// Check admin location.
		if ( is_admin() && 'edit.php' === $pagenow && 'page' === $post_type ) {

			// Get Special Pages array, if it's there.
			$special_pages = $this->core->db->setting_get( 'cp_special_pages' );

			// Modify query if we have an array.
			if ( ! empty( $special_pages ) ) {
				$query->query_vars['post__not_in'] = $special_pages;
			}

		}

	}

	/**
	 * Updates Page counts in Page listings.
	 *
	 * @since 3.4
	 *
	 * @param array $vars The existing variables.
	 * @return array $vars The modified list of variables.
	 */
	public function update_page_counts_in_admin( $vars ) {

		global $pagenow, $post_type;

		// Bail if not in admin.
		if ( ! is_admin() ) {
			return $vars;
		}

		// Bail if not Page listings screen.
		if ( 'edit.php' !== $pagenow || 'page' !== $post_type ) {
			return $vars;
		}

		// Get Special Pages array, if it's there.
		$special_pages = $this->core->db->setting_get( 'cp_special_pages', [] );
		if ( empty( $special_pages ) ) {
			return $vars;
		}

		/**
		 * Data comes in like this:
		 *
		 * [all] => <a href='edit.php?post_type=page' class="current">All <span class="count">(8)</span></a>
		 * [publish] => <a href='edit.php?post_status=publish&amp;post_type=page'>Published <span class="count">(8)</span></a>
		 */

		// Capture existing value enclosed in brackets.
		preg_match( '/\((\d+)\)/', $vars['all'], $matches );

		// Did we get a result?
		if ( isset( $matches[1] ) ) {

			// Subtract Special Page count.
			$new_count = (int) $matches[1] - count( $special_pages );

			// Rebuild 'all' items.
			$vars['all'] = preg_replace( '/\(\d+\)/', '(' . $new_count . ')', $vars['all'] );

		}

		// Capture existing value enclosed in brackets.
		preg_match( '/\((\d+)\)/', $vars['publish'], $matches );

		// Did we get a result?
		if ( isset( $matches[1] ) ) {

			// Subtract Special Page count.
			$new_count = (int) $matches[1] - count( $special_pages );

			// Rebuild 'publish' items.
			$vars['publish'] = preg_replace( '/\(\d+\)/', '(' . $new_count . ')', $vars['publish'] );

		}

		// --<
		return $vars;

	}

	// -------------------------------------------------------------------------

	/**
	 * Creates a given Special Page.
	 *
	 * @since 3.4
	 *
	 * @param str $page The type of Special Page.
	 * @return mixed $new_id The numeric ID of the new Page, or false on failure.
	 */
	public function special_page_create( $page ) {

		// Init.
		$new_id = false;

		// Get Special Pages array, if it's there.
		$special_pages = $this->core->db->setting_get( 'cp_special_pages', [] );

		// Switch by Page.
		switch ( $page ) {

			case 'title':

				// Create Welcome Page.
				$new_id = $this->title_page_create();
				break;

			case 'general_comments':

				// Create General Comments Page.
				$new_id = $this->general_comments_page_create();
				break;

			case 'all_comments':

				// Create All Comments Page.
				$new_id = $this->all_comments_page_create();
				break;

			case 'comments_by_author':

				// Create Comments by Author Page.
				$new_id = $this->comments_by_author_page_create();
				break;

			case 'blog':

				// Create Blog Page.
				$new_id = $this->blog_page_create();
				break;

			case 'blog_archive':

				// Create Blog Page.
				$new_id = $this->blog_archive_page_create();
				break;

			case 'toc':

				// Create TOC Page.
				$new_id = $this->toc_page_create();
				break;

		}

		// Add to Special Pages settings array.
		$special_pages[] = $new_id;

		// Reset option.
		$this->core->db->setting_set( 'cp_special_pages', $special_pages );

		// Save changes.
		$this->core->db->settings_save();

		// --<
		return $new_id;

	}

	/**
	 * Deletes a given Special Page.
	 *
	 * @since 3.4
	 *
	 * @param str $page The type of Special Page to delete.
	 * @return bool True if succesfully deleted, false otherwise.
	 */
	public function special_page_delete( $page ) {

		// Get "name" of Special Page.
		switch ( $page ) {
			case 'general_comments':
				$flag = 'cp_general_comments_page';
				break;
			case 'all_comments':
				$flag = 'cp_all_comments_page';
				break;
			case 'comments_by_author':
				$flag = 'cp_comments_by_page';
				break;
			case 'blog':
				$flag = 'cp_blog_page';
				break;
			case 'blog_archive':
				$flag = 'cp_blog_archive_page';
				break;
			case 'toc':
				$flag = 'cp_toc_page';
				break;
		}

		// Try to get the Page ID.
		$page_id = $this->core->db->setting_get( $flag );
		if ( empty( $page_id ) ) {
			return true;
		}

		// Try to delete the Page, bypassing trash.
		$force_delete = true;
		if ( ! wp_delete_post( $page_id, $force_delete ) ) {
			return false;
		}

		// Delete singular setting.
		$this->core->db->setting_delete( $flag );

		// For the Blog Page, restore the original WordPress Blog Page.
		if ( 'cp_blog_page' === $flag ) {
			$this->core->db->option_wp_restore( 'page_for_posts' );
		}

		// Retrieve data on Special Pages.
		$special_pages = $this->core->db->setting_get( 'cp_special_pages', [] );

		// Is it in our Special Pages array?
		if ( in_array( $page_id, $special_pages ) ) {

			// Remove Page ID from array.
			$special_pages = array_diff( $special_pages, [ $page_id ] );

			// Overwrite setting.
			$this->core->db->setting_set( 'cp_special_pages', $special_pages );

		}

		// Save changes.
		$this->core->db->settings_save();

		// Success.
		return true;

	}

	/**
	 * Tests if the current Page is a Special Page.
	 *
	 * @since 3.4
	 *
	 * @return bool True if Special Page, false otherwise.
	 */
	public function is_special_page() {

		// Access Post object.
		global $post;

		// Bail if we have no Post object.
		if ( ! $post ) {
			return false;
		}

		// Try to get the Special Pages.
		$special_pages = $this->core->db->setting_get( 'cp_special_pages', [] );
		if ( empty( $special_pages ) ) {
			return false;
		}

		// Bail if the current Page is not a Special Page.
		if ( ! in_array( $post->ID, $special_pages ) ) {
			return false;
		}

		// Success.
		return true;

	}

	// -------------------------------------------------------------------------

	/**
	 * Create "General Comments" Page.
	 *
	 * @since 3.4
	 *
	 * @return int|bool $general_comments_id The numeric ID of the "General Comments" Page, or false on failure.
	 */
	public function general_comments_page_create() {

		// Define General Comments Page.
		$general_comments = [
			'post_status'           => 'publish',
			'post_type'             => 'page',
			'post_parent'           => 0,
			'comment_status'        => 'open',
			'ping_status'           => 'open',
			'to_ping'               => '', // Quick fix for Windows.
			'pinged'                => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt'          => '', // Quick fix for Windows.
			'menu_order'            => 0,
		];

		// Add Post-specific stuff.

		// Default Page title.
		$title = __( 'General Comments', 'commentpress-core' );

		/**
		 * Filters the General Comments Page title.
		 *
		 * @since 3.4
		 *
		 * @param string $title The default title of the Page.
		 */
		$general_comments['post_title'] = apply_filters( 'cp_general_comments_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		/**
		 * Filters the General Comments Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $content The default content of the Page.
		 */
		$general_comments['post_content'] = apply_filters( 'cp_general_comments_content', $content );

		/**
		 * Filters the General Comments Page template.
		 *
		 * @since 3.4
		 *
		 * @param string The default template of the Page.
		 */
		$general_comments['page_template'] = apply_filters( 'cp_general_comments_template', 'comments-general.php' );

		// Insert the Post into the database.
		$general_comments_id = wp_insert_post( $general_comments );

		// Bail on error.
		if ( is_wp_error( $general_comments_id ) ) {
			return false;
		}

		// Store the option.
		$this->core->db->setting_set( 'cp_general_comments_page', $general_comments_id );

		// --<
		return $general_comments_id;

	}

	/**
	 * Create "All Comments" Page.
	 *
	 * @since 3.4
	 *
	 * @return int $all_comments_id The numeric ID of the "All Comments" Page.
	 */
	public function all_comments_page_create() {

		// Define All Comments Page.
		$all_comments = [
			'post_status'           => 'publish',
			'post_type'             => 'page',
			'post_parent'           => 0,
			'comment_status'        => 'closed',
			'ping_status'           => 'closed',
			'to_ping'               => '', // Quick fix for Windows.
			'pinged'                => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt'          => '', // Quick fix for Windows.
			'menu_order'            => 0,
		];

		// Add Post-specific stuff.

		// Default Page title.
		$title = __( 'All Comments', 'commentpress-core' );

		/**
		 * Filters the All Comments Page title.
		 *
		 * @since 3.4
		 *
		 * @param string $title The default title of the Page.
		 */
		$all_comments['post_title'] = apply_filters( 'cp_all_comments_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		/**
		 * Filters the All Comments Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $content The default content of the Page.
		 */
		$all_comments['post_content'] = apply_filters( 'cp_all_comments_content', $content );

		/**
		 * Filters the All Comments Page template.
		 *
		 * @since 3.4
		 *
		 * @param string The default template of the Page.
		 */
		$all_comments['page_template'] = apply_filters( 'cp_all_comments_template', 'comments-all.php' );

		// Insert the Post into the database.
		$all_comments_id = wp_insert_post( $all_comments );

		// Bail on error.
		if ( is_wp_error( $all_comments_id ) ) {
			return false;
		}

		// Store the option.
		$this->core->db->setting_set( 'cp_all_comments_page', $all_comments_id );

		// --<
		return $all_comments_id;

	}

	/**
	 * Create "Comments by Author" Page.
	 *
	 * @since 3.4
	 *
	 * @return int $group_id The numeric ID of the "Comments by Author" Page.
	 */
	public function comments_by_author_page_create() {

		// Define Comments by Author Page.
		$group = [
			'post_status'           => 'publish',
			'post_type'             => 'page',
			'post_parent'           => 0,
			'comment_status'        => 'closed',
			'ping_status'           => 'closed',
			'to_ping'               => '', // Quick fix for Windows.
			'pinged'                => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt'          => '', // Quick fix for Windows.
			'menu_order'            => 0,
		];

		// Add Post-specific stuff.

		// Default Page title.
		$title = __( 'Comments by Commenter', 'commentpress-core' );

		/**
		 * Filters the Comments by Commenter Page title.
		 *
		 * @since 3.4
		 *
		 * @param string $title The default title of the Page.
		 */
		$group['post_title'] = apply_filters( 'cp_comments_by_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		/**
		 * Filters the Comments by Commenter Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $content The default content of the Page.
		 */
		$group['post_content'] = apply_filters( 'cp_comments_by_content', $content );

		/**
		 * Filters the Comments by Commenter Page template.
		 *
		 * @since 3.4
		 *
		 * @param string The default template of the Page.
		 */
		$group['page_template'] = apply_filters( 'cp_comments_by_template', 'comments-by.php' );

		// Insert the Post into the database.
		$group_id = wp_insert_post( $group );

		// Bail on error.
		if ( is_wp_error( $group_id ) ) {
			return false;
		}

		// Store the option.
		$this->core->db->setting_set( 'cp_comments_by_page', $group_id );

		// --<
		return $group_id;

	}

	/**
	 * Create "blog" Page.
	 *
	 * @since 3.4
	 *
	 * @return int $blog_id The numeric ID of the "Blog" Page.
	 */
	public function blog_page_create() {

		// Define Blog Page.
		$blog = [
			'post_status'           => 'publish',
			'post_type'             => 'page',
			'post_parent'           => 0,
			'comment_status'        => 'closed',
			'ping_status'           => 'closed',
			'to_ping'               => '', // Quick fix for Windows.
			'pinged'                => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt'          => '', // Quick fix for Windows.
			'menu_order'            => 0,
		];

		// Add Post-specific stuff.

		// Default Page title.
		$title = __( 'Blog', 'commentpress-core' );

		/**
		 * Filters the Blog Page title.
		 *
		 * @since 3.4
		 *
		 * @param string $title The default title of the Page.
		 */
		$blog['post_title'] = apply_filters( 'cp_blog_page_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		/**
		 * Filters the Blog Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $content The default content of the Page.
		 */
		$blog['post_content'] = apply_filters( 'cp_blog_page_content', $content );

		/**
		 * Filters the Blog Page template.
		 *
		 * @since 3.4
		 *
		 * @param string The default template of the Page.
		 */
		$blog['page_template'] = apply_filters( 'cp_blog_page_template', 'blog.php' );

		// Insert the Post into the database.
		$blog_id = wp_insert_post( $blog );

		// Bail on error.
		if ( is_wp_error( $blog_id ) ) {
			return false;
		}

		// Store the option.
		$this->core->db->setting_set( 'cp_blog_page', $blog_id );

		// Set WordPress internal Page reference.
		$this->core->db->option_wp_backup( 'page_for_posts', $blog_id );

		// --<
		return $blog_id;

	}

	/**
	 * Create "Blog Archive" Page.
	 *
	 * @since 3.4
	 *
	 * @return int $blog_id The numeric ID of the "Blog Archive" Page.
	 */
	public function blog_archive_page_create() {

		// Define Blog Archive Page.
		$blog = [
			'post_status'           => 'publish',
			'post_type'             => 'page',
			'post_parent'           => 0,
			'comment_status'        => 'closed',
			'ping_status'           => 'closed',
			'to_ping'               => '', // Quick fix for Windows.
			'pinged'                => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt'          => '', // Quick fix for Windows.
			'menu_order'            => 0,
		];

		// Add Post-specific stuff.

		// Default Page title.
		$title = __( 'Blog Archive', 'commentpress-core' );

		/**
		 * Filters the Blog Archive Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $title The default content of the Page.
		 */
		$blog['post_title'] = apply_filters( 'cp_blog_archive_page_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		/**
		 * Filters the Blog Archive Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $content The default content of the Page.
		 */
		$blog['post_content'] = apply_filters( 'cp_blog_archive_page_content', $content );

		/**
		 * Filters the Blog Archive Page template.
		 *
		 * @since 3.4
		 *
		 * @param string The default template of the Page.
		 */
		$blog['page_template'] = apply_filters( 'cp_blog_archive_page_template', 'archives.php' );

		// Insert the Post into the database.
		$blog_id = wp_insert_post( $blog );

		// Bail on error.
		if ( is_wp_error( $blog_id ) ) {
			return false;
		}

		// Store the option.
		$this->core->db->setting_set( 'cp_blog_archive_page', $blog_id );

		// --<
		return $blog_id;

	}

	/**
	 * Create "Table of Contents" Page.
	 *
	 * PLease note: this is NOT USED.
	 *
	 * @since 3.4
	 *
	 * @return int $toc_id The numeric ID of the "Table of Contents" Page.
	 */
	public function toc_page_create() {

		// Define TOC Page.
		$toc = [
			'post_status'           => 'publish',
			'post_type'             => 'page',
			'post_parent'           => 0,
			'comment_status'        => 'closed',
			'ping_status'           => 'closed',
			'to_ping'               => '', // Quick fix for Windows.
			'pinged'                => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt'          => '', // Quick fix for Windows.
			'menu_order'            => 0,
		];

		// Default Page title.
		$title = __( 'Table of Contents', 'commentpress-core' );

		/**
		 * Filters the TOC Page title.
		 *
		 * @since 3.4
		 *
		 * @param string $title The default title of the Page.
		 */
		$toc['post_title'] = apply_filters( 'cp_toc_page_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		/**
		 * Filters the TOC Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $content The default content of the Page.
		 */
		$toc['post_content'] = apply_filters( 'cp_toc_page_content', $content );

		/**
		 * Filters the TOC Page template.
		 *
		 * @since 3.4
		 *
		 * @param string The default template of the Page.
		 */
		$toc['page_template'] = apply_filters( 'cp_toc_page_template', 'toc.php' );

		// Insert the Post into the database.
		$toc_id = wp_insert_post( $toc );

		// Bail on error.
		if ( is_wp_error( $toc_id ) ) {
			return false;
		}

		// Store the option.
		$this->core->db->setting_set( 'cp_toc_page', $toc_id );

		// --<
		return $toc_id;

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the link to a given Special Page.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @param str $page_type The CommentPress Core "name" of a Special Page.
	 * @return str $link The HTML link to that Page.
	 */
	public function get_page_link( $page_type = 'cp_all_comments_page' ) {

		// Access globals.
		global $post;

		// Init.
		$link = '';

		// Try to get the Page ID.
		$page_id = $this->core->db->setting_get( $page_type );
		if ( empty( $page_id ) ) {
			return $link;
		}

		// Get Page object.
		$page = get_post( $page_id );

		// Is it the current Page?
		$active = '';
		if ( ( $post instanceof WP_Post ) && ( $page instanceof WP_Post ) ) {
			if ( (int) $page->ID === (int) $post->ID ) {
				$active = ' class="active_page"';
			}
		}

		// Get link.
		$url = get_permalink( $page );

		// Switch title by type.
		switch ( $page_type ) {

			case 'cp_welcome_page':
				$link_title = __( 'Title Page', 'commentpress-core' );
				$button     = 'cover';
				break;

			case 'cp_all_comments_page':
				$link_title = __( 'All Comments', 'commentpress-core' );
				$button     = 'allcomments';
				break;

			case 'cp_general_comments_page':
				$link_title = __( 'General Comments', 'commentpress-core' );
				$button     = 'general';
				break;

			case 'cp_blog_page':
				$link_title = __( 'Blog', 'commentpress-core' );
				if ( is_home() ) {
					$active = ' class="active_page"';
				}
				$button = 'blog';
				break;

			case 'cp_blog_archive_page':
				$link_title = __( 'Blog Archive', 'commentpress-core' );
				$button     = 'archive';
				break;

			case 'cp_comments_by_page':
				$link_title = __( 'Comments by Commenter', 'commentpress-core' );
				$button     = 'members';
				break;

			default:
				$link_title = __( 'Members', 'commentpress-core' );
				$button     = 'members';

		}

		/**
		 * Filters the Special Page title.
		 *
		 * @since 3.4
		 *
		 * @param str $link_title The default Special Page title.
		 * @param str $page_type The CommentPress Core "name" of a Special Page.
		 */
		$title = apply_filters( 'commentpress_page_link_title', $link_title, $page_type );

		// Build link.
		$link = '<li' . $active . '>' .
			'<a href="' . $url . '" id="btn_' . $button . '" class="css_btn" title="' . $title . '">' .
				$title .
			'</a>' .
		'</li>' . "\n";

		// --<
		return $link;

	}

	/**
	 * Gets the URL for a given Special Page.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @param str $page_type The CommentPress Core "name" of a Special Page.
	 * @return str $url The URL of that Page.
	 */
	public function get_page_url( $page_type = 'cp_all_comments_page' ) {

		// Init.
		$url = '';

		// Try to get the Page ID.
		$page_id = $this->core->db->setting_get( $page_type );
		if ( empty( $page_id ) ) {
			return $url;
		}

		// Get link.
		$url = get_permalink( $page_id );

		// --<
		return $url;

	}

}
