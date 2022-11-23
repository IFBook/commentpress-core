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
	 * Plugin options array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $commentpress_options The plugin options array.
	 */
	public $commentpress_options = [];

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
	 * @var bool $header_bg_colour The default header background colour.
	 */
	public $header_bg_colour = '2c2622';

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
	public $blog_type = false;

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

		/*
		 * Act when this plugin is activated/deactivated.
		 *
		 * Hooked in after Display class.
		 */
		add_action( 'commentpress/core/activated', [ $this, 'activate' ], 20 );
		add_action( 'commentpress/core/deactivated', [ $this, 'deactivate' ],20 );

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

		// Load options array.
		$this->commentpress_options = $this->option_wp_get( 'commentpress_options', $this->commentpress_options );

		// Do immediate upgrades after the theme has loaded.
		add_action( 'after_setup_theme', [ $this, 'upgrade_immediately' ] );

		// We're done.
		$done = true;

	}

	/**
	 * Set up all items associated with this object.
	 *
	 * @since 3.0
	 */
	public function activate() {

		// Have we already got a modified database?
		$modified = $this->db_is_modified( 'comment_text_signature' ) ? 'y' : 'n';

		// If  we have an existing comment_text_signature column.
		if ( $modified == 'y' ) {

			// Upgrade old CommentPress schema to new.
			if ( ! $this->schema_upgrade() ) {

				// Kill plugin activation.
				wp_die( 'CommentPress Core Error: could not upgrade the database' );

			}

		} else {

			// Update db schema.
			$this->schema_update();

		}

		// Test if we have our version.
		if ( ! $this->option_wp_get( 'commentpress_version' ) ) {

			// Store CommentPress Core version.
			$this->option_wp_set( 'commentpress_version', COMMENTPRESS_VERSION );

		}

		// Test that we aren't reactivating.
		if ( ! $this->option_wp_get( 'commentpress_options' ) ) {

			// Add options with default values.
			$this->options_create();

		}

		// Retrieve data on Special Pages.
		$special_pages = $this->option_get( 'cp_special_pages', [] );

		// If we haven't created any.
		if ( count( $special_pages ) == 0 ) {

			// Create Special Pages.
			$this->core->pages_legacy->create_special_pages();

		}

		// Turn Comment paging option off.
		$this->comment_paging_cancel();

		// Override Widgets.
		$this->widgets_clear();

	}

	/**
	 * Reset WordPress to prior state, but retain options.
	 *
	 * @since 3.0
	 */
	public function deactivate() {

		// Reset Comment paging option.
		$this->comment_paging_restore();

		// Restore Widgets.
		$this->widgets_restore();

		// Always remove Special Pages.
		$this->core->pages_legacy->delete_special_pages();

	}

	// -------------------------------------------------------------------------

	/**
	 * Update WordPress database schema.
	 *
	 * @since 3.0
	 *
	 * @return bool $result True if successful, false otherwise.
	 */
	public function schema_update() {

		// Database object.
		global $wpdb;

		// Include WordPress upgrade script.
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		// Add the column, if not already there.
		$result = maybe_add_column(
			$wpdb->comments,
			'comment_signature',
			"ALTER TABLE `$wpdb->comments` ADD `comment_signature` VARCHAR(255) NULL;"
		);

		// --<
		return $result;
	}

	/**
	 * Upgrade WordPress database schema.
	 *
	 * @since 3.0
	 *
	 * @return bool $result True if successful, false otherwise.
	 */
	public function schema_upgrade() {

		// Database object.
		global $wpdb;

		// Init return.
		$result = false;

		// Construct query.
		$query = "ALTER TABLE `$wpdb->comments` CHANGE `comment_text_signature` `comment_signature` VARCHAR(255) NULL;";

		// Do the query to rename the column.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->query( $query );

		// Test if we now have the correct column name.
		if ( $this->db_is_modified( 'comment_signature' ) ) {
			$result = true;
		}

		// --<
		return $result;
	}

	/**
	 * Do we have a column in the Comments table?
	 *
	 * @since 3.0
	 *
	 * @param string $column_name The name of the column.
	 * @return bool $result True if modified, false otherwise.
	 */
	public function db_is_modified( $column_name ) {

		// Database object.
		global $wpdb;

		// Init.
		$result = false;

		// Define query.
		$query = "DESCRIBE $wpdb->comments";

		// Get columns.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$cols = $wpdb->get_results( $query );

		// Loop.
		foreach ( $cols as $col ) {

			// Is it our desired column?
			// phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
			if ( $col->Field == $column_name ) {

				// We got it.
				$result = true;
				break;

			}

		}

		// --<
		return $result;
	}

	/**
	 * Check for an outdated plugin version.
	 *
	 * @since 3.0
	 *
	 * @return bool $result True if outdated, false otherwise.
	 */
	public function version_outdated() {

		// Get installed version cast as string.
		$version = (string) $this->option_wp_get( 'commentpress_version' );

		// Override if we have a CommentPress Core install and it's lower than this one.
		if ( ! empty( $version ) && version_compare( COMMENTPRESS_VERSION, $version, '>' ) ) {
			return true;
		}

		// Fallback.
		return false;

	}

	/**
	 * Check for plugin upgrade.
	 *
	 * @since 3.0
	 *
	 * @return bool $result True if required, false otherwise.
	 */
	public function upgrade_required() {

		// Bail if we do not have an outdated version.
		if ( ! $this->version_outdated() ) {
			return false;
		}

		// Override if any options need to be shown.
		if ( $this->upgrade_options_check() ) {
			return true;
		}

		// Fallback.
		return false;

	}

	/**
	 * Check for options added in this plugin upgrade.
	 *
	 * @since 3.0
	 *
	 * @return bool $result True if upgrade needed, false otherwise.
	 */
	public function upgrade_options_check() {

		// Do we have the option to choose which Post Types are supported (new in 3.9)?
		if ( ! $this->option_exists( 'cp_post_types_disabled' ) ) {
			return true;
		}

		// Do we have the option to choose not to parse content (new in 3.8.10)?
		if ( ! $this->option_exists( 'cp_do_not_parse' ) ) {
			return true;
		}

		// Do we have the option to choose to disable Page navigation (new in 3.8.10)?
		if ( ! $this->option_exists( 'cp_page_nav_enabled' ) ) {
			return true;
		}

		// Do we have the option to choose to hide textblock meta (new in 3.5.9)?
		if ( ! $this->option_exists( 'cp_textblock_meta' ) ) {
			return true;
		}

		// Do we have the option to choose featured images (new in 3.5.4)?
		if ( ! $this->option_exists( 'cp_featured_images' ) ) {
			return true;
		}

		// Do we have the option to choose the default sidebar (new in 3.3.3)?
		if ( ! $this->option_exists( 'cp_sidebar_default' ) ) {
			return true;
		}

		// Do we have the option to show or hide Page meta (new in 3.3.2)?
		if ( ! $this->option_exists( 'cp_page_meta_visibility' ) ) {
			return true;
		}

		// Do we have the option to choose Blog Type (new in 3.3.1)?
		if ( ! $this->option_exists( 'cp_blog_type' ) ) {
			return true;
		}

		// Do we have the option to choose the TOC layout (new in 3.3)?
		if ( ! $this->option_exists( 'cp_show_extended_toc' ) ) {
			return true;
		}

		// Do we have the option to set the Comment editor?
		if ( ! $this->option_exists( 'cp_comment_editor' ) ) {
			return true;
		}

		// Do we have the option to set the default behaviour?
		if ( ! $this->option_exists( 'cp_promote_reading' ) ) {
			return true;
		}

		// Do we have the option to show or hide titles?
		if ( ! $this->option_exists( 'cp_title_visibility' ) ) {
			return true;
		}

		// Do we have the option to set the header bg colour?
		if ( ! $this->option_exists( 'cp_header_bg_colour' ) ) {
			return true;
		}

		// Do we have the option to set the scroll speed?
		if ( ! $this->option_exists( 'cp_js_scroll_speed' ) ) {
			return true;
		}

		// Do we have the option to set the minimum Page width?
		if ( ! $this->option_exists( 'cp_min_page_width' ) ) {
			return true;
		}

		// --<
		return false;

	}

	/**
	 * Upgrade CommentPress options.
	 *
	 * @since 3.0
	 */
	public function upgrade_options() {

		// Bail if no upgrade required.
		if ( ! $this->upgrade_required() ) {
			return;
		}

		/*
		 * We don't receive disabled Post Types in $_POST, so let's default
		 * to all Post Types being enabled.
		 */
		$cp_post_types_enabled = array_keys( $this->get_supported_post_types() );

		// Default Blog Type.
		$cp_blog_type = $this->blog_type;

		// Get variables.
		extract( $_POST );

		// New in CommentPress Core 3.9 - Post Types can be excluded.
		if ( ! $this->option_exists( 'cp_post_types_disabled' ) ) {

			// Get selected Post Types.
			$enabled_types = array_map( 'esc_sql', $cp_post_types_enabled );

			// Exclude the selected Post Types.
			$disabled_types = array_diff( array_keys( $this->get_supported_post_types() ), $enabled_types );

			// Add option.
			$this->option_set( 'cp_post_types_disabled', $disabled_types );

		}

		// New in CommentPress Core 3.8.10 - parsing can be prevented.
		if ( ! $this->option_exists( 'cp_do_not_parse' ) ) {

			// Get choice.
			$choice = esc_sql( $cp_do_not_parse );

			// Add chosen parsing option.
			$this->option_set( 'cp_do_not_parse', $choice );

		}

		// New in CommentPress Core 3.8.10 - Page navigation can be disabled.
		if ( ! $this->option_exists( 'cp_page_nav_enabled' ) ) {

			// Get choice.
			$choice = esc_sql( $cp_page_nav_enabled );

			// Add chosen Page navigation option.
			$this->option_set( 'cp_page_nav_enabled', $choice );

		}

		// New in CommentPress Core 3.5.9 - textblock meta can be hidden.
		if ( ! $this->option_exists( 'cp_textblock_meta' ) ) {

			// Get choice.
			$choice = esc_sql( $cp_textblock_meta );

			// Add chosen textblock meta option.
			$this->option_set( 'cp_textblock_meta', $choice );

		}

		// New in CommentPress Core 3.5.4 - featured image capabilities.
		if ( ! $this->option_exists( 'cp_featured_images' ) ) {

			// Get choice.
			$choice = esc_sql( $cp_featured_images );

			// Add chosen featured images option.
			$this->option_set( 'cp_featured_images', $choice );

		}

		// Removed in CommentPress Core 3.4 - do we still have the legacy cp_para_comments_enabled option?
		if ( $this->option_exists( 'cp_para_comments_enabled' ) ) {

			// Delete old cp_para_comments_enabled option.
			$this->option_delete( 'cp_para_comments_enabled' );

		}

		// Removed in CommentPress Core 3.4 - do we still have the legacy cp_minimise_sidebar option?
		if ( $this->option_exists( 'cp_minimise_sidebar' ) ) {

			// Delete old cp_minimise_sidebar option.
			$this->option_delete( 'cp_minimise_sidebar' );

		}

		// New in CommentPress Core 3.4 - has AJAX "live" Comment refreshing been migrated?
		if ( ! $this->option_exists( 'cp_para_comments_live' ) ) {

			// "live" Comment refreshing, off by default.
			$this->option_set( 'cp_para_comments_live', $this->para_comments_live );

		}

		// New in CommentPress 3.3.3 - changed the way the Welcome Page works.
		if ( $this->option_exists( 'cp_special_pages' ) ) {

			// Do we have the cp_welcome_page option?
			if ( $this->option_exists( 'cp_welcome_page' ) ) {

				// Get it.
				$page_id = $this->option_get( 'cp_welcome_page' );

				// Retrieve data on Special Pages.
				$special_pages = $this->option_get( 'cp_special_pages', [] );

				// Is it in our Special Pages array?
				if ( in_array( $page_id, $special_pages ) ) {

					// Remove Page ID from array.
					$special_pages = array_diff( $special_pages, [ $page_id ] );

					// Reset option.
					$this->option_set( 'cp_special_pages', $special_pages );

				}

			}

		}

		// New in CommentPress 3.3.3 - are we missing the cp_sidebar_default option?
		if ( ! $this->option_exists( 'cp_sidebar_default' ) ) {

			// Does the current theme need this option?
			if ( ! apply_filters( 'commentpress_hide_sidebar_option', false ) ) {

				// Yes, get choice.
				$choice = esc_sql( $cp_sidebar_default );

				// Add chosen cp_sidebar_default option.
				$this->option_set( 'cp_sidebar_default', $choice );

			} else {

				// Add default cp_sidebar_default option.
				$this->option_set( 'cp_sidebar_default', $this->sidebar_default );

			}

		}

		// New in CommentPress 3.3.2 - are we missing the cp_page_meta_visibility option?
		if ( ! $this->option_exists( 'cp_page_meta_visibility' ) ) {

			// Get choice.
			$choice = esc_sql( $cp_page_meta_visibility );

			// Add chosen cp_page_meta_visibility option.
			$this->option_set( 'cp_page_meta_visibility', $choice );

		}

		// New in CommentPress 3.3.1 - are we missing the cp_blog_type option?
		if ( ! $this->option_exists( 'cp_blog_type' ) ) {

			// Get choice.
			$choice = esc_sql( $cp_blog_type );

			// Add chosen cp_blog_type option.
			$this->option_set( 'cp_blog_type', $choice );

		}

		// New in CommentPress 3.3 - are we missing the cp_show_extended_toc option?
		if ( ! $this->option_exists( 'cp_show_extended_toc' ) ) {

			// Get choice.
			$choice = esc_sql( $cp_show_extended_toc );

			// Add chosen cp_show_extended_toc option.
			$this->option_set( 'cp_show_extended_toc', $choice );

		}

		// Are we missing the cp_comment_editor option?
		if ( ! $this->option_exists( 'cp_comment_editor' ) ) {

			// Get choice.
			$choice = esc_sql( $cp_comment_editor );

			// Add chosen cp_comment_editor option.
			$this->option_set( 'cp_comment_editor', $choice );

		}

		// Are we missing the cp_promote_reading option?
		if ( ! $this->option_exists( 'cp_promote_reading' ) ) {

			// Get choice.
			$choice = esc_sql( $cp_promote_reading );

			// Add chosen cp_promote_reading option.
			$this->option_set( 'cp_promote_reading', $choice );

		}

		// Are we missing the cp_title_visibility option?
		if ( ! $this->option_exists( 'cp_title_visibility' ) ) {

			// Get choice.
			$choice = esc_sql( $cp_title_visibility );

			// Add chosen cp_title_visibility option.
			$this->option_set( 'cp_title_visibility', $choice );

		}

		// Are we missing the cp_header_bg_colour option?
		if ( ! $this->option_exists( 'cp_header_bg_colour' ) ) {

			// Get choice.
			$choice = esc_sql( $cp_header_bg_colour );

			// Strip our hex # char.
			if ( stristr( $choice, '#' ) ) {
				$choice = substr( $choice, 1 );
			}

			// Reset to default if blank.
			if ( $choice == '' ) {
				$choice = $this->header_bg_colour;
			}

			// Add chosen cp_header_bg_colour option.
			$this->option_set( 'cp_header_bg_colour', $choice );

		}

		// Are we missing the cp_js_scroll_speed option?
		if ( ! $this->option_exists( 'cp_js_scroll_speed' ) ) {

			// Get choice.
			$choice = esc_sql( $cp_js_scroll_speed );

			// Add chosen cp_js_scroll_speed option.
			$this->option_set( 'cp_js_scroll_speed', $choice );

		}

		// Are we missing the cp_min_page_width option?
		if ( ! $this->option_exists( 'cp_min_page_width' ) ) {

			// Get choice.
			$choice = esc_sql( $cp_min_page_width );

			// Add chosen cp_min_page_width option.
			$this->option_set( 'cp_min_page_width', $choice );

		}

		// Do we still have the legacy cp_allow_users_to_minimize option?
		if ( $this->option_exists( 'cp_allow_users_to_minimize' ) ) {

			// Delete old cp_allow_users_to_minimize option.
			$this->option_delete( 'cp_allow_users_to_minimize' );

		}

		// Do we have Special Pages?
		if ( $this->option_exists( 'cp_special_pages' ) ) {

			// If we don't have the TOC Page.
			if ( ! $this->option_exists( 'cp_toc_page' ) ) {

				// Get Special Pages array.
				$special_pages = $this->option_get( 'cp_special_pages', [] );

				// Create TOC Page -> a convenience, let's us define a logo as attachment.
				$special_pages[] = $this->create_toc_page();

				// Store the array of Page IDs that were created.
				$this->option_set( 'cp_special_pages', $special_pages );

			}

		}

		// Save new CommentPress Core options.
		$this->options_save();

		// Store new CommentPress Core version.
		$this->option_wp_set( 'commentpress_version', COMMENTPRESS_VERSION );

	}

	/**
	 * Perform any plugin upgrades that do not have a setting on Page load.
	 *
	 * Unlike `upgrade_options()` (which is only called when someone visits the
	 * CommentPress Core Settings Page), this method is called on every Page
	 * load so that upgrades are performed immediately if required.
	 *
	 * @since 3.0
	 */
	public function upgrade_immediately() {

		// Bail if we do not have an outdated version.
		if ( ! $this->version_outdated() ) {
			return;
		}

		// Maybe upgrade theme mods.
		$this->upgrade_theme_mods();

	}

	/**
	 * Check for theme mods added in this plugin upgrade.
	 *
	 * @since 3.4
	 *
	 * @return bool $result True if upgraded, false otherwise.
	 */
	public function upgrade_theme_mods() {

		// Bail if option is already deprecated.
		if ( 'deprecated' == $this->option_get( 'cp_header_bg_colour' ) ) {
			return;
		}

		// Get header background colour set via customizer (new in 3.8.5).
		$colour = get_theme_mod( 'commentpress_header_bg_color', false );

		// If we have no existing one.
		if ( $colour === false ) {

			// Set to default.
			$colour = $this->header_bg_colour;

			// Check for existing option.
			if ( $this->option_exists( 'cp_header_bg_colour' ) ) {

				// Get current value.
				$value = $this->option_get( 'cp_header_bg_colour' );

				// Override colour if not yet deprecated.
				if ( $value !== 'deprecated' ) {
					$colour = $value;
				}

			}

			// Apply theme mod setting.
			set_theme_mod( 'commentpress_header_bg_color', '#' . $colour );

			// Set option to deprecated.
			$this->option_set( 'cp_header_bg_colour', 'deprecated' );
			$this->options_save();

		}

	}

	/**
	 * Save the settings set by the administrator.
	 *
	 * @since 3.0
	 */
	public function options_update() {

		// Init vars.
		$cp_activate = '0';
		$cp_upgrade = '';
		$cp_reset = '';
		$cp_create_pages = '';
		$cp_delete_pages = '';
		$cp_para_comments_live = 0;
		$cp_show_subpages = 0;
		$cp_show_extended_toc = 0;
		$cp_blog_type = 0;
		$cp_sidebar_default = 'toc';
		$cp_featured_images = 'n';
		$cp_textblock_meta = 'y';
		$cp_page_nav_enabled = 'y';
		$cp_do_not_parse = 'y';

		// Assume all Post Types are enabled.
		$cp_post_types_enabled = array_keys( $this->get_supported_post_types() );

		// Get variables.
		extract( $_POST );

		/**
		 * Fires before the options have been updated.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Multisite_Admin::disable_core()
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/db/options_update/before' );

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
			$this->options_reset();
			return;
		}

		// Did we ask to auto-create Special Pages?
		if ( $cp_create_pages == '1' ) {

			// Remove any existing Special Pages.
			$this->core->pages_legacy->delete_special_pages();

			// Create Special Pages.
			$this->core->pages_legacy->create_special_pages();

		}

		// Did we ask to delete Special Pages?
		if ( $cp_delete_pages == '1' ) {

			// Remove Special Pages.
			$this->core->pages_legacy->delete_special_pages();

		}

		// Let's deal with our params now.

		/*
		// Individual Special Pages.
		$cp_welcome_page = esc_sql( $cp_welcome_page );
		$cp_blog_page = esc_sql( $cp_blog_page );
		$cp_general_comments_page = esc_sql( $cp_general_comments_page );
		$cp_all_comments_page = esc_sql( $cp_all_comments_page );
		$cp_comments_by_page = esc_sql( $cp_comments_by_page );
		$this->option_set( 'cp_welcome_page', $cp_welcome_page );
		$this->option_set( 'cp_blog_page', $cp_blog_page );
		$this->option_set( 'cp_general_comments_page', $cp_general_comments_page );
		$this->option_set( 'cp_all_comments_page', $cp_all_comments_page );
		$this->option_set( 'cp_comments_by_page', $cp_comments_by_page );
		*/

		// TOC content.
		$cp_show_posts_or_pages_in_toc = esc_sql( $cp_show_posts_or_pages_in_toc );
		$this->option_set( 'cp_show_posts_or_pages_in_toc', $cp_show_posts_or_pages_in_toc );

		// If we have Pages in TOC and a value for the next param.
		if ( $cp_show_posts_or_pages_in_toc == 'page' && isset( $cp_toc_chapter_is_page ) ) {

			$cp_toc_chapter_is_page = esc_sql( $cp_toc_chapter_is_page );
			$this->option_set( 'cp_toc_chapter_is_page', $cp_toc_chapter_is_page );

			// If Chapters are not Pages and we have a value for the next param.
			if ( $cp_toc_chapter_is_page == '0' ) {

				$cp_show_subpages = esc_sql( $cp_show_subpages );
				$this->option_set( 'cp_show_subpages', ( $cp_show_subpages ? 1 : 0 ) );

			} else {

				// Always set to show Sub-pages.
				$this->option_set( 'cp_show_subpages', 1 );

			}

		}

		// Extended or vanilla Posts TOC.
		if ( $cp_show_posts_or_pages_in_toc == 'post' ) {

			$cp_show_extended_toc = esc_sql( $cp_show_extended_toc );
			$this->option_set( 'cp_show_extended_toc', ( $cp_show_extended_toc ? 1 : 0 ) );

		}

		// Excerpt length.
		$cp_excerpt_length = esc_sql( $cp_excerpt_length );
		$this->option_set( 'cp_excerpt_length', intval( $cp_excerpt_length ) );

		// Comment editor.
		$cp_comment_editor = esc_sql( $cp_comment_editor );
		$this->option_set( 'cp_comment_editor', ( $cp_comment_editor ? 1 : 0 ) );

		// Has AJAX "live" Comment refreshing been migrated?
		if ( $this->option_exists( 'cp_para_comments_live' ) ) {

			// "live" Comment refreshing.
			$cp_para_comments_live = esc_sql( $cp_para_comments_live );
			$this->option_set( 'cp_para_comments_live', ( $cp_para_comments_live ? 1 : 0 ) );

		}

		// Behaviour.
		$cp_promote_reading = esc_sql( $cp_promote_reading );
		$this->option_set( 'cp_promote_reading', ( $cp_promote_reading ? 1 : 0 ) );

		// Title visibility.
		$cp_title_visibility = esc_sql( $cp_title_visibility );
		$this->option_set( 'cp_title_visibility', $cp_title_visibility );

		// Page meta visibility.
		$cp_page_meta_visibility = esc_sql( $cp_page_meta_visibility );
		$this->option_set( 'cp_page_meta_visibility', $cp_page_meta_visibility );

		// Save scroll speed.
		$cp_js_scroll_speed = esc_sql( $cp_js_scroll_speed );
		$this->option_set( 'cp_js_scroll_speed', $cp_js_scroll_speed );

		// Save min Page width.
		$cp_min_page_width = esc_sql( $cp_min_page_width );
		$this->option_set( 'cp_min_page_width', $cp_min_page_width );

		// Save Blog Type.
		$cp_blog_type = esc_sql( $cp_blog_type );
		$this->option_set( 'cp_blog_type', $cp_blog_type );

		// If it's a Group Blog.
		if ( $this->core->bp->is_groupblog() ) {

			// Get the Group ID.
			$group_id = get_groupblog_group_id( get_current_blog_id() );
			if ( isset( $group_id ) && is_numeric( $group_id ) && $group_id > 0 ) {

				// Store the Blog Type in Group meta.
				groups_update_groupmeta( $group_id, 'groupblogtype', 'groupblogtype-' . $cp_blog_type );

			}

		}

		// Save default sidebar.
		if ( ! apply_filters( 'commentpress_hide_sidebar_option', false ) ) {
			$cp_sidebar_default = esc_sql( $cp_sidebar_default );
			$this->option_set( 'cp_sidebar_default', $cp_sidebar_default );
		}

		// Save featured images.
		$cp_featured_images = esc_sql( $cp_featured_images );
		$this->option_set( 'cp_featured_images', $cp_featured_images );

		// Save textblock meta.
		$cp_textblock_meta = esc_sql( $cp_textblock_meta );
		$this->option_set( 'cp_textblock_meta', $cp_textblock_meta );

		// Save Page navigation enabled flag.
		$cp_page_nav_enabled = esc_sql( $cp_page_nav_enabled );
		$this->option_set( 'cp_page_nav_enabled', $cp_page_nav_enabled );

		// Save do not parse flag.
		$cp_do_not_parse = esc_sql( $cp_do_not_parse );
		$this->option_set( 'cp_do_not_parse', $cp_do_not_parse );

		// Do we have the Post Types option?
		if ( $this->option_exists( 'cp_post_types_disabled' ) ) {

			// Get selected Post Types.
			$enabled_types = array_map( 'esc_sql', $cp_post_types_enabled );

			// Exclude the selected Post Types.
			$disabled_types = array_diff( array_keys( $this->get_supported_post_types() ), $enabled_types );

			// Save skipped Post Types.
			$this->option_set( 'cp_post_types_disabled', $disabled_types );

		}

		/**
		 * Fires before the options have been saved.
		 *
		 * Used internally by:
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/db/options_update/pre' );

		// Save.
		$this->options_save();

		/**
		 * Fires after the options have been saved.
		 *
		 * Used internally by:
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/db/options_update/post' );

	}

	/**
	 * Upgrade CommentPress Core options to array.
	 *
	 * @since 3.0
	 *
	 * @return array $commentpress_options The plugin options.
	 */
	public function options_save() {

		// Set option.
		return $this->option_wp_set( 'commentpress_options', $this->commentpress_options );

	}

	/**
	 * Return existence of a specified option.
	 *
	 * @since 3.0
	 *
	 * @param str $option_name The name of the option.
	 * @return bool True if the option exists, false otherwise.
	 */
	public function option_exists( $option_name = '' ) {

		// Test for null.
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_exists()', 'commentpress-core' ) );
		}

		// Get option with unlikely default.
		return array_key_exists( $option_name, $this->commentpress_options );

	}

	/**
	 * Return a value for a specified option.
	 *
	 * @since 3.0
	 *
	 * @param str $option_name The name of the option.
	 * @param mixed $default The default value for the option.
	 * @return mixed The value of the option if it exists, $default otherwise.
	 */
	public function option_get( $option_name = '', $default = false ) {

		// Test for null.
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_get()', 'commentpress-core' ) );
		}

		// Get option.
		return ( array_key_exists( $option_name, $this->commentpress_options ) ) ? $this->commentpress_options[ $option_name ] : $default;

	}

	/**
	 * Sets a value for a specified option.
	 *
	 * @since 3.0
	 *
	 * @param str $option_name The name of the option.
	 * @param mixed $value The value for the option.
	 */
	public function option_set( $option_name = '', $value = '' ) {

		// Test for null.
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_set()', 'commentpress-core' ) );
		}

		// Test for other than string.
		if ( ! is_string( $option_name ) ) {
			die( __( 'You must supply the option as a string to option_set()', 'commentpress-core' ) );
		}

		// Set option.
		$this->commentpress_options[ $option_name ] = $value;

	}

	/**
	 * Deletes a specified option.
	 *
	 * @since 3.0
	 *
	 * @param str $option_name The name of the option.
	 */
	public function option_delete( $option_name = '' ) {

		// Test for null.
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_delete()', 'commentpress-core' ) );
		}

		// Unset option.
		unset( $this->commentpress_options[ $option_name ] );

	}

	/**
	 * Return existence of a specified WordPress option.
	 *
	 * @since 3.0
	 *
	 * @param str $option_name The name of the option.
	 * @return bool True if option exists, false otherwise.
	 */
	public function option_wp_exists( $option_name = '' ) {

		// Test for null.
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_wp_exists()', 'commentpress-core' ) );
		}

		// Get option with unlikely default.
		if ( $this->option_wp_get( $option_name, 'fenfgehgejgrkj' ) == 'fenfgehgejgrkj' ) {
			return false;
		} else {
			return true;
		}

	}

	/**
	 * Return a value for a specified WordPress option.
	 *
	 * @since 3.0
	 *
	 * @param str $option_name The name of the option.
	 * @param mixed $default The default value for the option.
	 * @return mixed The value of the option if it exists, $default otherwise.
	 */
	public function option_wp_get( $option_name = '', $default = false ) {

		// Test for null.
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_wp_get()', 'commentpress-core' ) );
		}

		// Get option.
		return get_option( $option_name, $default );

	}

	/**
	 * Sets a value for a specified WordPress option.
	 *
	 * @since 3.0
	 *
	 * @param str $option_name The name of the option.
	 * @param mixed $value The value for the option.
	 */
	public function option_wp_set( $option_name = '', $value = null ) {

		// Test for null.
		if ( $option_name == '' ) {
			die( __( 'You must supply an option to option_wp_set()', 'commentpress-core' ) );
		}

		// Set option.
		return update_option( $option_name, $value );

	}

	/**
	 * Get current header background colour.
	 *
	 * @since 3.0
	 *
	 * @return str $header_bg_colour The hex value of the header.
	 */
	public function option_get_header_bg() {

		// Do we have one set via the Customizer?
		$colour = get_theme_mod( 'commentpress_header_bg_color', false );

		// Return it if we do.
		if ( ! empty( $colour ) ) {
			return substr( $colour, 1 );
		}

		// Check if legacy option exists.
		if ( $this->option_exists( 'cp_header_bg_colour' ) ) {

			// Get the option.
			$colour = $this->option_get( 'cp_header_bg_colour' );

			// Return it if it is not yet deprecated.
			if ( $colour !== 'deprecated' ) {
				return $colour;
			}

		}

		// Fallback to default.
		return $this->header_bg_colour;

	}

	/**
	 * When a Page is saved, this also saves the CommentPress Core options.
	 *
	 * @since 3.4
	 *
	 * @param object $post_obj The Post object.
	 */
	public function save_meta( $post_obj ) {

		// If no Post, kick out.
		if ( ! $post_obj ) {
			return;
		}

		// If Page.
		if ( $post_obj->post_type == 'page' ) {
			$this->save_page_meta( $post_obj );
		}

		// If Post.
		if ( $post_obj->post_type == 'post' ) {
			$this->save_post_meta( $post_obj );
		}

	}

	/**
	 * When a Page is saved, this also saves the CommentPress Core options.
	 *
	 * @since 3.4
	 *
	 * @param object $post_obj The Post object.
	 */
	public function save_page_meta( $post_obj ) {

		// Bail if we're not authenticated.
		if ( ! $this->save_page_meta_authenticated( $post_obj ) ) {
			return;
		}

		// Check for revision.
		if ( $post_obj->post_type == 'revision' ) {

			// Get parent.
			if ( $post_obj->post_parent != 0 ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// Save Page title visibility.
		$this->save_page_title_visibility( $post );

		// Save Page meta visibility.
		$this->save_page_meta_visibility( $post );

		// Save Page numbering.
		$this->save_page_numbering( $post );

		// Save Page layout for Title Page.
		$this->save_page_layout( $post );

		// Save default sidebar.
		$this->save_default_sidebar( $post );

		// Save starting Paragraph Number.
		$this->save_starting_paragraph( $post );

		/**
		 * Broadcast that Page meta has been saved.
		 *
		 * @since 4.0
		 *
		 * @param object $post The WordPress Post object.
		 */
		do_action( 'commentpress/core/db/page_meta/saved', $post );

	}

	/**
	 * When a Page is saved, this authenticates that our options can be saved.
	 *
	 * @since 3.4
	 *
	 * @param object $post_obj The Post object.
	 */
	public function save_page_meta_authenticated( $post_obj ) {

		// If no Post, kick out.
		if ( ! $post_obj ) {
			return false;
		}

		// If not Page, kick out.
		if ( $post_obj->post_type != 'page' ) {
			return false;
		}

		// Authenticate.
		$nonce = isset( $_POST['commentpress_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commentpress_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'commentpress_page_settings' ) ) {
			return false;
		}

		// Is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		// Check permissions - 'edit_pages' is available to editor or greater.
		if ( ! current_user_can( 'edit_pages' ) ) {
			return false;
		}

		// Good to go.
		return true;

	}

	/**
	 * Save Page Title visibility.
	 *
	 * @since 3.4
	 *
	 * @param object $post The Post object.
	 * @return string $data Either 'show' (default) or ''.
	 */
	public function save_page_title_visibility( $post ) {

		// Find and save the data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$data = isset( $_POST['cp_title_visibility'] ) ? sanitize_text_field( wp_unslash( $_POST['cp_title_visibility'] ) ) : 'show';

		// Set key.
		$key = '_cp_title_visibility';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Delete the meta_key if empty string.
			if ( $data === '' ) {
				delete_post_meta( $post->ID, $key );
			} else {
				update_post_meta( $post->ID, $key, esc_sql( $data ) );
			}

		} else {

			// Add the data.
			add_post_meta( $post->ID, $key, esc_sql( $data ) );

		}

		// --<
		return $data;

	}

	/**
	 * Save Page Meta visibility.
	 *
	 * @since 3.4
	 *
	 * @param object $post The Post object.
	 * @return string $data Either 'hide' (default) or ''.
	 */
	public function save_page_meta_visibility( $post ) {

		// Find and save the data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$data = isset( $_POST['cp_page_meta_visibility'] ) ? sanitize_text_field( wp_unslash( $_POST['cp_page_meta_visibility'] ) ) : 'hide';

		// Set key.
		$key = '_cp_page_meta_visibility';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Delete the meta_key if empty string.
			if ( $data === '' ) {
				delete_post_meta( $post->ID, $key );
			} else {
				update_post_meta( $post->ID, $key, esc_sql( $data ) );
			}

		} else {

			// Add the data.
			add_post_meta( $post->ID, $key, esc_sql( $data ) );

		}

		// --<
		return $data;

	}

	/**
	 * Save Page Numbering format.
	 *
	 * @since 3.4
	 *
	 * Only first top-level Page is allowed to save this.
	 *
	 * @param object $post The Post object.
	 */
	public function save_page_numbering( $post ) {

		// Was the value sent?
		if ( isset( $_POST['cp_number_format'] ) ) {

			// Set meta key.
			$key = '_cp_number_format';

			// Do we need to check this, since only the first top level Page
			// can now send this data? doesn't hurt to validate, I guess.
			if (
				$post->post_parent == '0' &&
				! $this->core->pages_legacy->is_special_page() &&
				$post->ID == $this->core->nav->get_first_page()
			) {

				// Get the data.
				$data = sanitize_text_field( wp_unslash( $_POST['cp_number_format'] ) );

				// If the custom field already has a value.
				if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

					// If empty string.
					if ( $data === '' ) {

						// Delete the meta_key.
						delete_post_meta( $post->ID, $key );

					} else {

						// Update the data.
						update_post_meta( $post->ID, $key, esc_sql( $data ) );

					}

				} else {

					// Add the data.
					add_post_meta( $post->ID, $key, esc_sql( $data ) );

				}

			}

			// Delete this meta value from all other Pages, because we may have altered
			// the relationship between Pages, thus causing the Page numbering to fail.

			// Get all Pages including Chapters.
			$all_pages = $this->core->nav->get_book_pages( 'structural' );

			// If we have any Pages.
			if ( count( $all_pages ) > 0 ) {

				// Loop.
				foreach ( $all_pages as $page ) {

					// Exclude first top level Page.
					if ( $post->ID != $page->ID ) {

						// Delete the meta value.
						delete_post_meta( $page->ID, $key );

					}

				}

			}

		}

	}

	/**
	 * Save Page Layout for Title Page -> to allow for Book Cover image.
	 *
	 * @since 3.0
	 *
	 * @param object $post The Post object.
	 */
	public function save_page_layout( $post ) {

		// Is this the Title Page?
		if ( $post->ID == $this->option_get( 'cp_welcome_page' ) ) {

			// Find and save the data.
			$data = isset( $_POST['cp_page_layout'] ) ? sanitize_text_field( wp_unslash( $_POST['cp_page_layout'] ) ) : 'text';

			// Set key.
			$key = '_cp_page_layout';

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// Delete the meta_key if empty string.
				if ( $data === '' ) {
					delete_post_meta( $post->ID, $key );
				} else {
					update_post_meta( $post->ID, $key, esc_sql( $data ) );
				}

			} else {

				// Add the data.
				add_post_meta( $post->ID, $key, esc_sql( $data ) );

			}

		}

	}

	/**
	 * When a Post is saved, this also saves the CommentPress Core options.
	 *
	 * @since 3.0
	 *
	 * @param object $post_obj The Post object.
	 */
	public function save_post_meta( $post_obj ) {

		// Bail if we're not authenticated.
		if ( ! $this->save_post_meta_authenticated( $post_obj ) ) {
			return;
		}

		// Check for revision.
		if ( $post_obj->post_type == 'revision' ) {

			// Get parent.
			if ( $post_obj->post_parent != 0 ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// Save default sidebar.
		$this->save_default_sidebar( $post );

		/**
		 * Broadcast that Post meta has been saved.
		 *
		 * @since 4.0
		 *
		 * @param object $post The WordPress Post object.
		 */
		do_action( 'commentpress/core/db/post_meta/saved', $post );

	}

	/**
	 * When a Post is saved, this authenticates that our options can be saved.
	 *
	 * @since 3.4
	 *
	 * @param object $post_obj The Post object.
	 */
	public function save_post_meta_authenticated( $post_obj ) {

		// If no Post, kick out.
		if ( ! $post_obj ) {
			return false;
		}

		// If not Page, kick out.
		if ( $post_obj->post_type != 'post' ) {
			return false;
		}

		// Authenticate.
		$nonce = isset( $_POST['commentpress_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commentpress_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'commentpress_post_settings' ) ) {
			return false;
		}

		// Is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		// Check permissions - 'edit_posts' is available to contributor+.
		if ( ! current_user_can( 'edit_posts', $post_obj->ID ) ) {
			return false;
		}

		// Good to go.
		return true;

	}

	/**
	 * Override default sidebar.
	 *
	 * @since 3.4
	 *
	 * @param object $post The Post object.
	 */
	public function save_default_sidebar( $post ) {

		// Allow this to be disabled.
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) {
			return;
		}

		// Do we have the option to choose the default sidebar (new in 3.3.3)?
		if ( $this->option_exists( 'cp_sidebar_default' ) ) {

			// Find and save the data.
			$data = ( isset( $_POST['cp_sidebar_default'] ) ) ?
				sanitize_text_field( wp_unslash( $_POST['cp_sidebar_default'] ) ) :
				$this->option_get( 'cp_sidebar_default' );

			// Set key.
			$key = '_cp_sidebar_default';

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// Delete the meta_key if empty string.
				if ( $data === '' ) {
					delete_post_meta( $post->ID, $key );
				} else {
					update_post_meta( $post->ID, $key, esc_sql( $data ) );
				}

			} else {

				// Add the data.
				add_post_meta( $post->ID, $key, esc_sql( $data ) );

			}

		}

	}

	/**
	 * Starting Paragraph Number - meta only exists when not default value.
	 *
	 * @since 3.4
	 *
	 * @param object $post The Post object.
	 */
	public function save_starting_paragraph( $post ) {

		// Get the data.
		$data = isset( $_POST['cp_starting_para_number'] ) ? sanitize_text_field( wp_unslash( $_POST['cp_starting_para_number'] ) ) : 1;

		// If not numeric, set to default.
		if ( ! is_numeric( $data ) ) {
			$data = 1;
		}

		// Sanitize it.
		$data = absint( $data );

		// Set key.
		$key = '_cp_starting_para_number';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Delete if default.
			if ( $data === 1 ) {
				delete_post_meta( $post->ID, $key );
			} else {
				update_post_meta( $post->ID, $key, esc_sql( $data ) );
			}

		} else {

			// Add the data if greater than default.
			if ( $data > 1 ) {
				add_post_meta( $post->ID, $key, esc_sql( $data ) );
			}

		}

	}

	/**
	 * When a Page is deleted, this makes sure that the CommentPress Core options are synced.
	 *
	 * @since 3.4
	 *
	 * @param object $post_id The Post ID.
	 */
	public function delete_meta( $post_id ) {

		// If no Post, kick out.
		if ( ! $post_id ) {
			return;
		}

		// If it's our Welcome Page.
		if ( $post_id == $this->option_get( 'cp_welcome_page' ) ) {

			// Delete option.
			$this->option_delete( 'cp_welcome_page' );

			// Save.
			$this->options_save();

		}

	}

	/**
	 * Get WordPress Post Types that CommentPress supports.
	 *
	 * @since 3.9
	 *
	 * @return array $supported_post_types Array of Post Types that have an editor.
	 */
	public function get_supported_post_types() {

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
	 * Get all WordPress Comments for a Post, unless Paged.
	 *
	 * @since 3.4
	 *
	 * @param int $post_ID The numeric ID of the Post.
	 * @return array $comments The array of Comment data.
	 */
	public function get_all_comments( $post_ID ) {

		// Access Post.
		global $post;

		// For WordPress, we use the API.
		$comments = get_comments( 'post_id=' . $post_ID . '&order=ASC' );

		// --<
		return $comments;

	}

	/**
	 * Get all Comments for a Post.
	 *
	 * @since 3.4
	 *
	 * @param int $post_ID The ID of the Post.
	 * @return array $comments The array of Comment data.
	 */
	public function get_comments( $post_ID ) {

		// Database object.
		global $wpdb;

		// Get Comments from db.
		// TODO: convert to WordPress method.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$comments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM $wpdb->comments WHERE comment_post_ID = %d",
				$post_ID
			)
		);

		// --<
		return $comments;

	}

	/**
	 * When a Comment is saved, this also saves the Text Signature.
	 *
	 * @since 3.0
	 *
	 * @param int $comment_ID The numeric ID of the Comment.
	 * @return boolean $result True if successful, false otherwise.
	 */
	public function save_comment_signature( $comment_ID ) {

		// Database object.
		global $wpdb;

		// Get Text Signature.
		$text_signature = isset( $_POST['text_signature'] ) ? sanitize_text_field( wp_unslash( $_POST['text_signature'] ) ) : '';

		// Did we get one?
		if ( $text_signature != '' ) {

			// Escape it.
			$text_signature = esc_sql( $text_signature );

			// Store Comment Text Signature.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			$result = $wpdb->query(
				$wpdb->prepare(
					"UPDATE $wpdb->comments SET comment_signature = %s WHERE comment_ID = %d",
					$text_signature,
					$comment_ID
				)
			);

		} else {

			// Set result to true - why not, eh?
			$result = true;

		}

		// --<
		return $result;

	}

	/**
	 * When a Comment is saved, this also saves the text selection.
	 *
	 * @since 3.9
	 *
	 * @param int $comment_id The numeric ID of the Comment.
	 * @return boolean $result True if successful, false otherwise.
	 */
	public function save_comment_selection( $comment_id ) {

		// Get text selection.
		$text_selection = isset( $_POST['text_selection'] ) ? sanitize_text_field( wp_unslash( $_POST['text_selection'] ) ) : '';

		// Bail if we didn't get one.
		if ( $text_selection == '' ) {
			return true;
		}

		// Sanity check: must have a comma.
		if ( stristr( $text_selection, ',' ) === false ) {
			return true;
		}

		// Make into an array.
		$selection = explode( ',', $text_selection );

		// Sanity check: must have only two elements.
		if ( count( $selection ) != 2 ) {
			return true;
		}

		// Sanity check: both elements must be integers.
		$start_end = [];
		foreach ( $selection as $item ) {

			// Not integer - kick out.
			if ( ! is_numeric( $item ) ) {
				return true;
			}

			// Cast as integer and add to array.
			$start_end[] = absint( $item );

		}

		// Okay, we're good to go.
		$selection_data = implode( ',', $start_end );

		// Set key.
		$key = '_cp_comment_selection';

		// Get current.
		$current = get_comment_meta( $comment_id, $key, true );

		// If the Comment meta already has a value.
		if ( ! empty( $current ) ) {

			// Update the data.
			update_comment_meta( $comment_id, $key, $selection_data );

		} else {

			// Add the data.
			add_comment_meta( $comment_id, $key, $selection_data, true );

		}

		// --<
		return true;

	}

	/**
	 * When a Comment is saved, this also saves the Page it was submitted on.
	 *
	 * This allows us to point to the correct Page of a multipage Post without
	 * parsing the content every time.
	 *
	 * @since 3.4
	 *
	 * @param int $comment_ID The numeric ID of the Comment.
	 */
	public function save_comment_page( $comment_ID ) {

		// Get the Page number.
		$page_number = isset( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : false;

		// Is this a paged Post?
		if ( is_numeric( $page_number ) ) {

			// Get Text Signature.
			$text_signature = isset( $_POST['text_signature'] ) ? sanitize_text_field( wp_unslash( $_POST['text_signature'] ) ) : '';

			// Is it a para-level comment?
			if ( $text_signature != '' ) {

				// Set key.
				$key = '_cp_comment_page';

				// If the custom field already has a value.
				if ( get_comment_meta( $comment_ID, $key, true ) != '' ) {

					// Update the data.
					update_comment_meta( $comment_ID, $key, $page_number );

				} else {

					// Add the data.
					add_comment_meta( $comment_ID, $key, $page_number, true );

				}

			} else {

				/*
				// Top level Comments are always Page 1.
				$page_number = 1;
				*/

			}

		}

	}

	/**
	 * Get Javascript params for the plugin, context dependent.
	 *
	 * @since 3.4
	 *
	 * @return array $vars The Javascript setup params.
	 */
	public function get_javascript_vars() {

		// Init return.
		$vars = [];

		// Add Comments open.
		global $post;

		// If we don't have a Post (like on the 404 Page).
		if ( ! is_object( $post ) ) {

			// Comments must be closed.
			$vars['cp_comments_open'] = 'n';

			// Set empty permalink.
			$vars['cp_permalink'] = '';

		} else {

			// Check for Post "comment_status".
			$vars['cp_comments_open'] = ( $post->comment_status == 'open' ) ? 'y' : 'n';

			// Set Post permalink.
			$vars['cp_permalink'] = get_permalink( $post->ID );

		}

		// Assume no admin bars.
		$vars['cp_wp_adminbar'] = 'n';
		$vars['cp_bp_adminbar'] = 'n';

		// Match WordPress 3.8+ admin bar.
		$vars['cp_wp_adminbar_height'] = '32';
		$vars['cp_wp_adminbar_expanded'] = '0';

		// Are we showing the WordPress admin bar?
		if ( is_admin_bar_showing() ) {

			// We have it.
			$vars['cp_wp_adminbar'] = 'y';

			// Admin bar expands in height below 782px viewport width.
			$vars['cp_wp_adminbar_expanded'] = '46';

		}

		// Are we logged in AND in a BuddyPress scenario?
		if ( is_user_logged_in() && $this->core->bp->is_buddypress() ) {

			// Regardless of version, settings can be made in bp-custom.php.
			if ( defined( 'BP_DISABLE_ADMIN_BAR' ) && BP_DISABLE_ADMIN_BAR ) {

				// We've killed both admin bars.
				$vars['cp_bp_adminbar'] = 'n';
				$vars['cp_wp_adminbar'] = 'n';

			}

			// Check for BuddyPress versions prior to 1.6 (1.6 uses the WordPress admin bar instead of a custom one).
			if ( ! function_exists( 'bp_get_version' ) ) {

				// But, this can already be overridden in bp-custom.php.
				if ( defined( 'BP_USE_WP_ADMIN_BAR' ) && BP_USE_WP_ADMIN_BAR ) {

					// Not present.
					$vars['cp_bp_adminbar'] = 'n';
					$vars['cp_wp_adminbar'] = 'y';

				} else {

					// Let our javascript know.
					$vars['cp_bp_adminbar'] = 'y';

					// Recheck 'BP_DISABLE_ADMIN_BAR'.
					if ( defined( 'BP_DISABLE_ADMIN_BAR' ) && BP_DISABLE_ADMIN_BAR ) {

						// We've killed both admin bars.
						$vars['cp_bp_adminbar'] = 'n';
						$vars['cp_wp_adminbar'] = 'n';

					}

				}

			}

		}

		// Add rich text editor.
		$vars['cp_tinymce'] = 1;

		// Check if Users must be logged in to comment.
		if ( get_option( 'comment_registration' ) == '1' && ! is_user_logged_in() ) {

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Check CommentPress Core option.
		if (
			$this->option_exists( 'cp_comment_editor' ) &&
			$this->option_get( 'cp_comment_editor' ) != '1'
		) {

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// If on a public Group Blog and User isn't logged in.
		if ( $this->core->bp->is_groupblog() && ! is_user_logged_in() ) {

			// Don't add rich text editor, because only Members can comment.
			$vars['cp_tinymce'] = 0;

		}

		/**
		 * Filters the TinyMCE vars.
		 *
		 * Allow plugins to override TinyMCE.
		 *
		 * @since 3.4
		 *
		 * @param bool $cp_tinymce The default TinyMCE vars.
		 */
		$vars['cp_tinymce'] = apply_filters( 'cp_override_tinymce', $vars['cp_tinymce'] );

		// Add mobile var.
		$vars['cp_is_mobile'] = 0;

		// Is it a mobile?
		if ( $this->core->device->is_mobile() ) {

			// Is mobile.
			$vars['cp_is_mobile'] = 1;

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Add touch var.
		$vars['cp_is_touch'] = 0;

		// Is it a touch device?
		if ( $this->core->device->is_touch() ) {

			// Is touch.
			$vars['cp_is_touch'] = 1;

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Add touch testing var.
		$vars['cp_touch_testing'] = 0;

		// Have we set our testing constant?
		if ( defined( 'COMMENTPRESS_TOUCH_SELECT' ) && COMMENTPRESS_TOUCH_SELECT ) {

			// Support touch device testing.
			$vars['cp_touch_testing'] = 1;

		}

		// Add tablet var.
		$vars['cp_is_tablet'] = 0;

		// Is it a touch device?
		if ( $this->core->device->is_tablet() ) {

			// Is touch.
			$vars['cp_is_tablet'] = 1;

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Add rich text editor behaviour.
		$vars['cp_promote_reading'] = 1;

		// Check option.
		if (
			$this->option_exists( 'cp_promote_reading' ) &&
			$this->option_get( 'cp_promote_reading' ) != '1'
		) {

			// Promote commenting.
			$vars['cp_promote_reading'] = 0;

		}

		// Add Special Page var.
		$vars['cp_special_page'] = ( $this->core->pages_legacy->is_special_page() ) ? '1' : '0';

		// Are we in a BuddyPress scenario?
		if ( $this->core->bp->is_buddypress() ) {

			// Is it a component homepage?
			if ( $this->core->bp->is_buddypress_special_page() ) {

				// Treat them the way we do ours.
				$vars['cp_special_page'] = '1';

			}

		}

		// Get path.
		$url_info = wp_parse_url( get_option( 'siteurl' ) );

		// Add path for cookies.
		$vars['cp_cookie_path'] = '/';
		if ( ! empty( $url_info['path'] ) ) {
			$vars['cp_cookie_path'] = trailingslashit( $url_info['path'] );
		}

		// Add Page.
		global $page;
		$vars['cp_multipage_page'] = ( ! empty( $page ) ) ? $page : 0;

		// Are Chapters Pages?
		$vars['cp_toc_chapter_is_page'] = $this->option_get( 'cp_toc_chapter_is_page' );

		// Are Sub-pages shown?
		$vars['cp_show_subpages'] = $this->option_get( 'cp_show_subpages' );

		// Set default sidebar.
		$vars['cp_default_sidebar'] = $this->core->theme->get_default_sidebar();

		// Set scroll speed.
		$vars['cp_js_scroll_speed'] = $this->option_get( 'cp_js_scroll_speed' );

		// Set min Page width.
		$vars['cp_min_page_width'] = $this->option_get( 'cp_min_page_width' );

		// Default to showing textblock meta.
		$vars['cp_textblock_meta'] = 1;

		// Check option.
		if (
			$this->option_exists( 'cp_textblock_meta' ) &&
			$this->option_get( 'cp_textblock_meta' ) == 'n'
		) {

			// Only show textblock meta on rollover.
			$vars['cp_textblock_meta'] = 0;

		}

		// Default to Page navigation enabled.
		$vars['cp_page_nav_enabled'] = 1;

		// Check option.
		if (
			$this->option_exists( 'cp_page_nav_enabled' ) &&
			$this->option_get( 'cp_page_nav_enabled' ) == 'n'
		) {

			// Disable Page navigation.
			$vars['cp_page_nav_enabled'] = 0;

		}

		// Default to parsing content and Comments.
		$vars['cp_do_not_parse'] = 0;

		// Check option.
		if (
			$this->option_exists( 'cp_do_not_parse' ) &&
			$this->option_get( 'cp_do_not_parse' ) == 'y'
		) {

			// Do not parse.
			$vars['cp_do_not_parse'] = 1;

		}

		/**
		 * Filters the Javascript vars.
		 *
		 * @since 3.4
		 *
		 * @param array $vars The default Javascript vars.
		 */
		return apply_filters( 'commentpress_get_javascript_vars', $vars );

	}

	// -------------------------------------------------------------------------

	/**
	 * Cancels Comment paging because CommentPress Core will not work with Comment paging.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed.
	 */
	public function comment_paging_cancel() {

		// Store option.
		$this->wordpress_option_backup( 'page_comments', '' );

	}

	/**
	 * Resets Comment paging option when plugin is deactivated.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed.
	 */
	public function comment_paging_restore() {

		// Reset option.
		$this->wordpress_option_restore( 'page_comments' );

	}

	// -------------------------------------------------------------------------

	/**
	 * Clears Widgets for a fresh start.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed.
	 */
	public function widgets_clear() {

		// Set backup option.
		add_option( 'commentpress_sidebars_widgets', $this->option_wp_get( 'sidebars_widgets' ) );

		// Clear them - this array is based on the array in wp_install_defaults().
		update_option( 'sidebars_widgets', [
			'wp_inactive_widgets' => [],
			'sidebar-1' => [],
			'sidebar-2' => [],
			'sidebar-3' => [],
			'array_version' => 3,
		] );

	}

	/**
	 * Restores Widgets when plugin is deactivated.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed.
	 */
	public function widgets_restore() {

		// Reset option.
		$this->wordpress_option_restore( 'sidebars_widgets' );

	}

	// -------------------------------------------------------------------------

	/**
	 * Backs up a current WordPress option.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed.
	 *
	 * @param str $name The name of the option to back up.
	 * @param mixed $value The value of the option.
	 */
	public function wordpress_option_backup( $name, $value ) {

		// Save backup option.
		add_option( 'commentpress_' . $name, $this->option_wp_get( $name ) );

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
	public function wordpress_option_restore( $name ) {

		// Restore the WordPress option.
		$this->option_wp_set( $name, $this->option_wp_get( 'commentpress_' . $name ) );

		// Remove backup option.
		delete_option( 'commentpress_' . $name );

	}

	// -------------------------------------------------------------------------

	/**
	 * Create all basic CommentPress Core options.
	 *
	 * @since 3.4
	 */
	public function options_create() {

		// Init options array.
		$this->commentpress_options = [
			'cp_show_posts_or_pages_in_toc' => $this->toc_content,
			'cp_toc_chapter_is_page' => $this->toc_chapter_is_page,
			'cp_show_subpages' => $this->show_subpages,
			'cp_show_extended_toc' => $this->show_extended_toc,
			'cp_title_visibility' => $this->title_visibility,
			'cp_page_meta_visibility' => $this->page_meta_visibility,
			'cp_header_bg_colour' => 'deprecated',
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

		// Paragraph-level Comments enabled by default.
		add_option( 'commentpress_options', $this->commentpress_options );

	}

	/**
	 * Reset CommentPress Core options.
	 *
	 * @since 3.4
	 */
	public function options_reset() {

		// TOC: show Posts by default.
		$this->option_set( 'cp_show_posts_or_pages_in_toc', $this->toc_content );

		// TOC: are Chapters Pages.
		$this->option_set( 'cp_toc_chapter_is_page', $this->toc_chapter_is_page );

		// TOC: if Pages are shown, show Sub-pages by default.
		$this->option_set( 'cp_show_subpages', $this->show_subpages );

		// TOC: show extended Post list.
		$this->option_set( 'cp_show_extended_toc', $this->show_extended_toc );

		// Comment editor.
		$this->option_set( 'cp_comment_editor', $this->comment_editor );

		// Promote reading or commenting.
		$this->option_set( 'cp_promote_reading', $this->promote_reading );

		// Show or hide titles.
		$this->option_set( 'cp_title_visibility', $this->title_visibility );

		// Show or hide Page meta.
		$this->option_set( 'cp_page_meta_visibility', $this->page_meta_visibility );

		// Header background colour.
		$this->option_set( 'cp_header_bg_colour', 'deprecated' );

		// Javascript scroll speed.
		$this->option_set( 'cp_js_scroll_speed', $this->js_scroll_speed );

		// Minimum Page width.
		$this->option_set( 'cp_min_page_width', $this->min_page_width );

		// "live" Comment refeshing.
		$this->option_set( 'cp_para_comments_live', $this->para_comments_live );

		// Blog: excerpt length.
		$this->option_set( 'cp_excerpt_length', $this->excerpt_length );

		// Blog Type.
		$this->option_set( 'cp_blog_type', $this->blog_type );

		// Default sidebar.
		$this->option_set( 'cp_sidebar_default', $this->sidebar_default );

		// Featured images.
		$this->option_set( 'cp_featured_images', $this->featured_images );

		// Textblock meta.
		$this->option_set( 'cp_textblock_meta', $this->textblock_meta );

		// Page navigation enabled.
		$this->option_set( 'cp_page_nav_enabled', $this->page_nav_enabled );

		// Do not parse flag.
		$this->option_set( 'cp_do_not_parse', $this->do_not_parse );

		// Skipped Post Types.
		$this->option_set( 'cp_post_types_disabled', $this->post_types_disabled );

		// Store it.
		$this->options_save();

	}

}
