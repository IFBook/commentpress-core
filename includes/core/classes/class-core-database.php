<?php
/**
 * CommentPress Core Database class.
 *
 * Handles the majority of database operations.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Database Class.
 *
 * This class is a wrapper for the majority of database operations.
 *
 * @since 3.0
 */
class CommentPress_Core_Database {

	/**
	 * Core loader object.
	 *
	 * @since 3.0
	 * @since 4.0 Renamed.
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * The installed version of the plugin.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $plugin_version The plugin version.
	 */
	public $plugin_version;

	/**
	 * Upgrade flag.
	 *
	 * @since 4.0
	 * @access public
	 * @var bool $is_upgrade The upgrade flag. False by default.
	 */
	public $is_upgrade = false;


	/**
	 * Core version option name.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $option_version The name of the core version option.
	 */
	public $option_version = 'commentpress_version';

	/**
	 * Core settings option name.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $option_settings The name of the core settings option.
	 */
	public $option_settings = 'commentpress_options';

	/**
	 * Core settings array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $settings The core settings array.
	 */
	public $settings = [];

	// -------------------------------------------------------------------------

	/**
	 * Table of Contents content flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $toc_content The TOC content - either 'post' or 'page'.
	 */
	public $toc_content = 'page';

	/**
	 * Table of Contents "Chapters are Pages" flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $toc_chapter_is_page The Table of Contents "Chapters are Pages" flag.
	 */
	public $toc_chapter_is_page = 1;

	/**
	 * Extended Table of Contents content for Posts lists flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $show_extended_toc The extended TOC content for Posts lists flag.
	 */
	public $show_extended_toc = 1;

	/**
	 * Table of Contents show Sub-pages flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $show_subpages The Table of Contents shows Sub-pages by default.
	 */
	public $show_subpages = 1;

	/**
	 * Page title visibility flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $title_visibility Show Page titles by default.
	 */
	public $title_visibility = 'show';

	/**
	 * Page meta visibility flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $page_meta_visibility Hide Page meta by default.
	 */
	public $page_meta_visibility = 'hide';

	/**
	 * Default editor flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $comment_editor Default to rich text editor (TinyMCE).
	 */
	public $comment_editor = 1;

	/**
	 * Promote reading flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $promote_reading Either promote reading (1) or commenting (0).
	 */
	public $promote_reading = 0;

	/**
	 * Excerpt length.
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $excerpt_length The default excerpt length.
	 */
	public $excerpt_length = 55;

	/**
	 * Default header background colour (hex, same as in theme stylesheet).
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $header_bg_color The default header background colour.
	 */
	public $header_bg_color = '2c2622';

	/**
	 * Default scroll speed.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $js_scroll_speed The scroll speed (in millisecs).
	 */
	public $js_scroll_speed = '800';

	/**
	 * Default type of Blog.
	 *
	 * Blog Types are built as an array - eg, array( '0' => 'Poetry', '1' => 'Prose' )
	 *
	 * @since 3.3
	 * @access public
	 * @var bool|int $blog_type The default type of Blog.
	 */
	public $blog_type = 0;

	/**
	 * Default sidebar tab.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $sidebar_default The default sidebar tab ('toc' == Contents tab).
	 */
	public $sidebar_default = 'toc';

	/**
	 * Default minimum Page width (px).
	 *
	 * @since 3.0
	 * @access public
	 * @var str $min_page_width The default minimum Page width in pixels.
	 */
	public $min_page_width = '447';

	/**
	 * "Live" Comment refreshing.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $para_comments_live The "live" Comment refreshing setting (off by default).
	 */
	public $para_comments_live = 0;

	/**
	 * Featured images flag.
	 *
	 * @since 3.5
	 * @access public
	 * @var str $featured_images The featured images flag ('y' or 'n').
	 */
	public $featured_images = 'n';

	/**
	 * Textblock meta flag.
	 *
	 * @since 3.5
	 * @access public
	 * @var str $textblock_meta The textblock meta flag ('y' or 'n').
	 */
	public $textblock_meta = 'y';

	/**
	 * Page navigation enabled flag.
	 *
	 * By default, CommentPress creates "book-like" navigation for the built-in
	 * "page" Post Type. This is what CommentPress was built for in the first
	 * place - to create a "document" from hierarchically-organised Pages. This
	 * is not always the desired behaviour.
	 *
	 * @since 3.8.10
	 * @access public
	 * @var str $page_nav_enabled The Page navigation flag ('y' or 'n').
	 */
	public $page_nav_enabled = 'y';

	/**
	 * Do Not Parse flag.
	 *
	 * When Comments are closed on an entry and there are no Comments on that
	 * entry, if this is set then the content will not be parsed for Paragraphs,
	 * Lines or Blocks. Comments will also not be parsed, meaning that the entry
	 * behaves the same as content which is not commentable. This allows, for
	 * example, the rendering of the Comment column to be skipped in these
	 * circumstances.
	 *
	 * @since 3.8.10
	 * @access public
	 * @var str $do_not_parse The flag indicating if content is to parsed ('y' or 'n').
	 */
	public $do_not_parse = 'n';

	/**
	 * Skipped Post Types.
	 *
	 * By default all Post Types are parsed by CommentPress. Post Types in this
	 * array will not be parsed. This effectively batch sets $do_not_parse for
	 * the Post Type.
	 *
	 * @since 3.9
	 * @access public
	 * @var str $post_types_disabled The Post Types not to be parsed.
	 */
	public $post_types_disabled = [];

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 *
	 * @param object $core Reference to the core plugin object.
	 */
	public function __construct( $core ) {

		// Store reference to parent.
		$this->core = $core;

		// Init when this plugin is fully loaded.
		add_action( 'commentpress/core/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Object initialisation.
	 *
	 * @since 3.0
	 */
	public function initialise() {

		// Only do this once.
		static $done;
		if ( isset( $done ) && $done === true ) {
			return;
		}

		// Register hooks.
		$this->register_hooks();

		// We're done.
		$done = true;

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

		// Act early when this plugin is activated.
		add_action( 'commentpress/core/activate', [ $this, 'plugin_activate' ], 10 );

		// Act late when this plugin is deactivated.
		add_action( 'commentpress/core/deactivate', [ $this, 'plugin_deactivate' ], 10 );

		// Initialise settings when plugins are loaded.
		add_action( 'plugins_loaded', [ $this, 'settings_initialise' ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Runs when core is activated.
	 *
	 * @since 3.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_activate( $network_wide = false ) {

		// Bail if plugin is network activated.
		if ( $network_wide ) {
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

		// Init settings.
		$this->settings_initialise();

		// Save settings.
		$this->settings_save();

	}

	/**
	 * Runs when core is deactivated.
	 *
	 * @since 3.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_deactivate( $network_wide = false ) {

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'network_wide' => $network_wide ? 'y' : 'n',
			//'backtrace' => $trace,
		], true ) );
		*/

		// Init settings.
		$this->settings_initialise();

		// Keep options when deactivating.

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the installed plugin version.
	 *
	 * @since 4.0
	 *
	 * @return string|bool $version The installed version, or false if none found.
	 */
	public function version_get() {

		// Get installed version cast as string.
		$version = (string) $this->option_wp_get( $this->option_version );

		// Cast as boolean if not found.
		if ( empty( $version ) ) {
			$version = false;
		}

		// --<
		return $version;

	}

	/**
	 * Sets the plugin version.
	 *
	 * @since 4.0
	 *
	 * @param string $version The version to save.
	 */
	public function version_set( $version ) {

		// Store new CommentPress Core version.
		$this->option_wp_set( $this->option_version, $version );

	}

	/**
	 * Deletes the plugin version option.
	 *
	 * @since 4.0
	 */
	public function version_delete() {

		// Delete CommentPress Core version option.
		$this->option_wp_delete( $this->option_version );

	}

	/**
	 * Checks for an outdated plugin version.
	 *
	 * @since 4.0
	 *
	 * @return bool True if outdated, false otherwise.
	 */
	public function version_outdated() {

		// Get installed version.
		$version = $this->version_get();

		// True if no version or we have a core install and it's lower than this one.
		if ( empty( $version ) || version_compare( COMMENTPRESS_VERSION, $version, '>' ) ) {
			return true;
		}

		// Fallback.
		return false;

	}

	// -------------------------------------------------------------------------

	/**
	 * Initialise settings.
	 *
	 * @since 4.0
	 */
	public function settings_initialise() {

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'plugin_context' => $this->core->plugin->plugin_context_get(),
			//'backtrace' => $trace,
		], true ) );
		*/

		// Load installed plugin version.
		$this->plugin_version = $this->version_get();

		// Load settings array.
		$this->settings = $this->settings_get();

		// Store version if there has been a change.
		if ( $this->version_outdated() ) {
			$this->version_set( COMMENTPRESS_VERSION );
			$this->is_upgrade = true;
		}

		// Settings upgrade tasks.
		$this->settings_upgrade();

	}

	/**
	 * Upgrades the settings when required.
	 *
	 * @since 4.0
	 */
	public function settings_upgrade() {

		// Don't save by default.
		$save = false;

		/*
		// Some setting may not exist.
		if ( ! $this->setting_exists( 'some_setting' ) ) {
			$settings = $this->settings_get_defaults();
			$this->setting_set( 'some_setting', $settings['some_setting'] );
			$save = true;
		}
		*/

		// The "registered in multisite" setting may not exist.
		if ( is_multisite() && ! $this->setting_exists( 'network_registered' ) ) {

			// Make sure that this site is registered.
			$this->register_on_network();

			// Save a setting locally.
			$this->setting_set( 'network_registered', 'y' );
			$save = true;

		}

		/*
		// Things to always check on upgrade.
		if ( $this->is_upgrade ) {
			// Maybe save settings.
			//$save = true;
		}
		*/

		/**
		 * Filters the "Save settings" flag.
		 *
		 * @since 4.0
		 *
		 * @param bool $save True if settings should be saved, false otherwise.
		 */
		$save = apply_filters( 'commentpress/core/settings/upgrade/save', $save );

		// Save settings if need be.
		if ( $save === true ) {
			$this->settings_save();
		}

	}

	/**
	 * Gets the default settings.
	 *
	 * @since 4.0
	 *
	 * @return array $settings  The array of default settings.
	 */
	public function settings_get_defaults() {

		// Init return.
		$settings = [
			'cp_show_posts_or_pages_in_toc' => $this->toc_content,
			'cp_toc_chapter_is_page' => $this->toc_chapter_is_page,
			'cp_show_subpages' => $this->show_subpages,
			'cp_show_extended_toc' => $this->show_extended_toc,
			'cp_title_visibility' => $this->title_visibility,
			'cp_page_meta_visibility' => $this->page_meta_visibility,
			'cp_js_scroll_speed' => $this->js_scroll_speed,
			'cp_min_page_width' => $this->min_page_width,
			'cp_comment_editor' => $this->comment_editor,
			'cp_promote_reading' => $this->promote_reading,
			'cp_excerpt_length' => $this->excerpt_length,
			'cp_para_comments_live' => $this->para_comments_live,
			'cp_blog_type' => $this->blog_type,
			'cp_sidebar_default' => $this->sidebar_default,
			'cp_featured_images' => $this->featured_images,
			'cp_textblock_meta' => $this->textblock_meta,
			'cp_page_nav_enabled' => $this->page_nav_enabled,
			'cp_do_not_parse' => $this->do_not_parse,
			'cp_post_types_disabled' => $this->post_types_disabled,
		];

		/**
		 * Filters the default settings.
		 *
		 * @since 4.0
		 *
		 * @param array $settings The array of default settings.
		 */
		$settings = apply_filters( 'commentpress/core/settings/defaults', $settings );

		// --<
		return $settings;

	}

	/**
	 * Updates the settings from form submissions.
	 *
	 * @since 4.0
	 */
	public function settings_update() {

		// Init vars.
		$cp_activate = '0';
		$cp_upgrade = '';
		$cp_reset = '';
		$cp_create_pages = '';
		$cp_delete_pages = '';
		$cp_para_comments_live = 0;
		$cp_show_subpages = 0;
		$cp_show_extended_toc = 0;
		$cp_featured_images = 'n';
		$cp_textblock_meta = 'y';
		$cp_page_nav_enabled = 'y';
		$cp_do_not_parse = 'y';

		// Assume all Post Types are enabled.
		$cp_post_types_enabled = array_keys( $this->post_types_get_supported() );

		// Get variables.
		extract( $_POST );

		/**
		 * Fires before the options have been updated.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/db/settings_update/before' );

		// Is Multisite activating CommentPress Core?
		if ( $cp_activate == '1' ) {
			return;
		}

		// Did we ask to upgrade CommentPress Core?
		if ( $cp_upgrade == '1' ) {
			$this->upgrade_options();
			return;
		}

		// Did we ask to reset?
		if ( $cp_reset == '1' ) {
			$defaults = $this->settings_get_defaults();
			$this->settings_save();
			return;
		}

		// Did we ask to auto-create Special Pages?
		if ( $cp_create_pages == '1' ) {

			// Remove any existing Special Pages.
			$this->core->pages_legacy->special_pages_delete();

			// Create Special Pages.
			$this->core->pages_legacy->special_pages_create();

		}

		// Did we ask to delete Special Pages?
		if ( $cp_delete_pages == '1' ) {

			// Remove Special Pages.
			$this->core->pages_legacy->special_pages_delete();

		}

		// Let's deal with our params now.

		/*
		// Individual Special Pages.
		$cp_welcome_page = esc_sql( $cp_welcome_page );
		$cp_blog_page = esc_sql( $cp_blog_page );
		$cp_general_comments_page = esc_sql( $cp_general_comments_page );
		$cp_all_comments_page = esc_sql( $cp_all_comments_page );
		$cp_comments_by_page = esc_sql( $cp_comments_by_page );
		$this->setting_set( 'cp_welcome_page', $cp_welcome_page );
		$this->setting_set( 'cp_blog_page', $cp_blog_page );
		$this->setting_set( 'cp_general_comments_page', $cp_general_comments_page );
		$this->setting_set( 'cp_all_comments_page', $cp_all_comments_page );
		$this->setting_set( 'cp_comments_by_page', $cp_comments_by_page );
		*/

		// TOC content.
		$cp_show_posts_or_pages_in_toc = esc_sql( $cp_show_posts_or_pages_in_toc );
		$this->setting_set( 'cp_show_posts_or_pages_in_toc', $cp_show_posts_or_pages_in_toc );

		// If we have Pages in TOC and a value for the next param.
		if ( $cp_show_posts_or_pages_in_toc == 'page' && isset( $cp_toc_chapter_is_page ) ) {

			$cp_toc_chapter_is_page = esc_sql( $cp_toc_chapter_is_page );
			$this->setting_set( 'cp_toc_chapter_is_page', $cp_toc_chapter_is_page );

			// If Chapters are not Pages and we have a value for the next param.
			if ( $cp_toc_chapter_is_page == '0' ) {

				$cp_show_subpages = esc_sql( $cp_show_subpages );
				$this->setting_set( 'cp_show_subpages', ( $cp_show_subpages ? 1 : 0 ) );

			} else {

				// Always set to show Sub-pages.
				$this->setting_set( 'cp_show_subpages', 1 );

			}

		}

		// Extended or vanilla Posts TOC.
		if ( $cp_show_posts_or_pages_in_toc == 'post' ) {

			$cp_show_extended_toc = esc_sql( $cp_show_extended_toc );
			$this->setting_set( 'cp_show_extended_toc', ( $cp_show_extended_toc ? 1 : 0 ) );

		}

		// Excerpt length.
		$cp_excerpt_length = esc_sql( $cp_excerpt_length );
		$this->setting_set( 'cp_excerpt_length', intval( $cp_excerpt_length ) );

		// Comment editor.
		$cp_comment_editor = esc_sql( $cp_comment_editor );
		$this->setting_set( 'cp_comment_editor', ( $cp_comment_editor ? 1 : 0 ) );

		// Has AJAX "live" Comment refreshing been migrated?
		if ( $this->setting_exists( 'cp_para_comments_live' ) ) {

			// "live" Comment refreshing.
			$cp_para_comments_live = esc_sql( $cp_para_comments_live );
			$this->setting_set( 'cp_para_comments_live', ( $cp_para_comments_live ? 1 : 0 ) );

		}

		// Behaviour.
		$cp_promote_reading = esc_sql( $cp_promote_reading );
		$this->setting_set( 'cp_promote_reading', ( $cp_promote_reading ? 1 : 0 ) );

		// Save scroll speed.
		$cp_js_scroll_speed = esc_sql( $cp_js_scroll_speed );
		$this->setting_set( 'cp_js_scroll_speed', $cp_js_scroll_speed );

		// Save min Page width.
		$cp_min_page_width = esc_sql( $cp_min_page_width );
		$this->setting_set( 'cp_min_page_width', $cp_min_page_width );

		// If it's a Group Blog.
		if ( $this->core->bp->is_groupblog() ) {

			// Get the Group ID.
			$group_id = get_groupblog_group_id( get_current_blog_id() );
			if ( isset( $group_id ) && is_numeric( $group_id ) && $group_id > 0 ) {

				// Store the Blog Type in Group meta.
				groups_update_groupmeta( $group_id, 'groupblogtype', 'groupblogtype-' . $cp_blog_type );

			}

		}

		// Save featured images.
		$cp_featured_images = esc_sql( $cp_featured_images );
		$this->setting_set( 'cp_featured_images', $cp_featured_images );

		// Save textblock meta.
		$cp_textblock_meta = esc_sql( $cp_textblock_meta );
		$this->setting_set( 'cp_textblock_meta', $cp_textblock_meta );

		// Save Page navigation enabled flag.
		$cp_page_nav_enabled = esc_sql( $cp_page_nav_enabled );
		$this->setting_set( 'cp_page_nav_enabled', $cp_page_nav_enabled );

		// Save do not parse flag.
		$cp_do_not_parse = esc_sql( $cp_do_not_parse );
		$this->setting_set( 'cp_do_not_parse', $cp_do_not_parse );

		// Do we have the Post Types option?
		if ( $this->setting_exists( 'cp_post_types_disabled' ) ) {

			// Get selected Post Types.
			$enabled_types = array_map( 'esc_sql', $cp_post_types_enabled );

			// Exclude the selected Post Types.
			$disabled_types = array_diff( array_keys( $this->post_types_get_supported() ), $enabled_types );

			// Save skipped Post Types.
			$this->setting_set( 'cp_post_types_disabled', $disabled_types );

		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the settings array from a WordPress option.
	 *
	 * @since 4.0
	 *
	 * @return array $settings The array of settings if successful, or empty array otherwise.
	 */
	public function settings_get() {

		// Get the option.
		return $this->option_wp_get( $this->option_settings, $this->settings_get_defaults() );

	}

	/**
	 * Saves the settings array in a WordPress option.
	 *
	 * @since 4.0
	 *
	 * @return boolean $success True if successful, or false otherwise.
	 */
	public function settings_save() {

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'plugin_context' => $this->core->plugin->plugin_context_get(),
			'settings' => $this->settings,
			//'backtrace' => $trace,
		], true ) );
		*/

		// Set the option.
		return $this->option_wp_set( $this->option_settings, $this->settings );

	}

	/**
	 * Deletes the settings WordPress option.
	 *
	 * @since 4.0
	 */
	public function settings_delete() {

		// Delete the option.
		$this->option_wp_delete( $this->option_settings );

	}

	// -------------------------------------------------------------------------

	/**
	 * Returns existence of a specified setting.
	 *
	 * @since 4.0
	 *
	 * @param str $name The name of the setting.
	 * @return bool True if the setting exists, false otherwise.
	 */
	public function setting_exists( $name ) {

		// Check if the setting exists in the settings array.
		return array_key_exists( $name, $this->settings );

	}

	/**
	 * Return a value for a specified setting.
	 *
	 * @since 4.0
	 *
	 * @param str $name The name of the setting.
	 * @param mixed $default The default value for the setting.
	 * @return mixed The value of the setting if it exists, $default otherwise.
	 */
	public function setting_get( $name, $default = false ) {

		// Get setting.
		return array_key_exists( $name, $this->settings ) ? $this->settings[ $name ] : $default;

	}

	/**
	 * Sets a value for a specified setting.
	 *
	 * @since 4.0
	 *
	 * @param str $name The name of the setting.
	 * @param mixed $value The value for the setting.
	 */
	public function setting_set( $name, $value = '' ) {

		// Set setting.
		$this->settings[ $name ] = $value;

	}

	/**
	 * Deletes a specified setting.
	 *
	 * @since 4.0
	 *
	 * @param str $name The name of the setting.
	 */
	public function setting_delete( $name ) {

		// Unset setting.
		unset( $this->settings[ $name ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Return existence of a specified option.
	 *
	 * @since 3.0
	 *
	 * @param str $name The name of the option.
	 * @return bool True if option exists, false otherwise.
	 */
	public function option_wp_exists( $name ) {

		// Get option with unlikely default.
		if ( $this->option_wp_get( $name, 'fenfgehgejgrkj' ) === 'fenfgehgejgrkj' ) {
			return false;
		} else {
			return true;
		}

	}

	/**
	 * Return a value for a specified option.
	 *
	 * @since 3.0
	 *
	 * @param str $name The name of the option.
	 * @param mixed $default The default value for the option.
	 * @return mixed The value of the option if it exists, default otherwise.
	 */
	public function option_wp_get( $name, $default = false ) {

		// Get option.
		return get_option( $name, $default );

	}

	/**
	 * Sets the value for a specified option.
	 *
	 * @since 3.0
	 *
	 * @param str $name The name of the option.
	 * @param mixed $value The value for the option.
	 * @return bool True if the value was updated, false otherwise.
	 */
	public function option_wp_set( $name, $value = '' ) {

		// Set option.
		return update_option( $name, $value );

	}

	/**
	 * Deletes a specified option.
	 *
	 * @since 4.0
	 *
	 * @param str $name The name of the option.
	 */
	public function option_wp_delete( $name ) {

		// Delete option.
		delete_option( $name );

	}

	/**
	 * Backs up a current WordPress option.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed.
	 *
	 * @param str $name The name of the option to back up.
	 * @param mixed $value The value of the option.
	 */
	public function option_wp_backup( $name, $value ) {

		// Save backup option.
		$this->option_wp_set( 'commentpress_' . $name, $this->option_wp_get( $name ) );

		// Overwrite the WordPress option.
		$this->option_wp_set( $name, $value );

	}

	/**
	 * Restores a WordPress option to the backed-up value.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed.
	 *
	 * @param str $name The name of the option.
	 */
	public function option_wp_restore( $name ) {

		// Restore the WordPress option.
		$this->option_wp_set( $name, $this->option_wp_get( 'commentpress_' . $name ) );

		// Remove backup option.
		$this->option_wp_delete( 'commentpress_' . $name );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the WordPress Post Types that CommentPress supports.
	 *
	 * @since 3.9
	 *
	 * @return array $supported_post_types The array of Post Types that have an editor.
	 */
	public function post_types_get_supported() {

		// Only parse Post Types once.
		static $supported_post_types = [];
		if ( ! empty( $supported_post_types ) ) {
			return $supported_post_types;
		}

		// Get only Post Types with an admin UI.
		$args = [
			'public' => true,
			'show_ui' => true,
		];

		// Get Post Types.
		$post_types = get_post_types( $args, 'objects' );

		// Include only those which have an editor.
		foreach ( $post_types as $post_type ) {
			if ( post_type_supports( $post_type->name, 'editor' ) ) {
				$supported_post_types[ $post_type->name ] = $post_type->label;
			}
		}

		// Built-in media descriptions are also supported.
		$attachment = get_post_type_object( 'attachment' );
		$supported_post_types[ $attachment->name ] = $attachment->label;

		// --<
		return $supported_post_types;

	}

	/**
	 * Ensures that this site is registered in multisite.
	 *
	 * This is needed because this is new functionality in 4.0 and there are
	 * going to be existing Sites on the network if this is not a fresh install.
	 *
	 * @see CommentPress_Multisite_Sites
	 *
	 * @since 4.0
	 */
	public function register_on_network() {

		// Get multisite reference.
		$multisite = commentpress_multisite();
		if ( empty( $multisite ) ) {
			return;
		}

		// Get the current Site ID.
		$site_id = get_current_blog_id();

		// Register this site on the network.
		$multisite->sites->core_site_id_add( $site_id );

	}

	// -------------------------------------------------------------------------

	/*
	 * -------------------------------------------------------------------------
	 * Methods below are legacy methods and should no longer be used.
	 * -------------------------------------------------------------------------
	 *
	 * They are retained here for the time-being but will be removed in future
	 * versions. You should check your log files to identify any calls to these
	 * methods and update your code accordingly.
	 *
	 * -------------------------------------------------------------------------
	 */

	/**
	 * Saves CommentPress Core options array in a WordPress option.
	 *
	 * @since 3.0
	 *
	 * @return boolean $success True if successful, or false otherwise.
	 */
	public function options_save() {
		_deprecated_function( __METHOD__, '4.0' );
		return $this->settings_save();
	}

	/**
	 * Save the settings set by the administrator.
	 *
	 * @since 3.0
	 */
	public function options_update() {
		_deprecated_function( __METHOD__, '4.0' );
		$this->settings_update();
	}

	/**
	 * Return existence of a specified option.
	 *
	 * @since 3.0
	 *
	 * @param str $name The name of the option.
	 * @return bool True if the option exists, false otherwise.
	 */
	public function option_exists( $name ) {
		_deprecated_function( __METHOD__, '4.0' );
		return $this->setting_exists( $name );
	}

	/**
	 * Return a value for a specified option.
	 *
	 * @since 3.0
	 *
	 * @param str $name The name of the option.
	 * @param mixed $default The default value for the option.
	 * @return mixed The value of the option if it exists, $default otherwise.
	 */
	public function option_get( $name, $default = false ) {
		_deprecated_function( __METHOD__, '4.0' );
		return $this->setting_get( $name, $default );
	}

	/**
	 * Sets a value for a specified option.
	 *
	 * @since 3.0
	 *
	 * @param str $name The name of the option.
	 * @param mixed $value The value for the option.
	 */
	public function option_set( $name, $value = '' ) {
		_deprecated_function( __METHOD__, '4.0' );
		$this->setting_set( $name, $value );
	}

	/**
	 * Deletes a specified option.
	 *
	 * @since 3.0
	 *
	 * @param str $name The name of the option.
	 */
	public function option_delete( $name ) {
		_deprecated_function( __METHOD__, '4.0' );
		$this->setting_delete( $name );
	}

	/**
	 * Gets the WordPress Post Types that CommentPress supports.
	 *
	 * @since 3.9
	 *
	 * @return array $supported_post_types The array of Post Types that have an editor.
	 */
	public function get_supported_post_types() {
		_deprecated_function( __METHOD__, '4.0' );
		return $this->post_types_get_supported();
	}

}
