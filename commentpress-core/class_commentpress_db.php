<?php

/**
 * CommentPress Core Database Class.
 *
 * This class is a wrapper for the majority of database operations.
 *
 * @since 3.0
 */
class Commentpress_Core_Database {

	/**
	 * Plugin object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $parent_obj The plugin object.
	 */
	public $parent_obj;

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
	 * Table of Contents "chapters are pages" flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $toc_chapter_is_page The Table of Contents "chapters are pages" flag.
	 */
	public $toc_chapter_is_page = 1;

	/**
	 * Extended Table of Contents content for posts lists flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $show_extended_toc The extended TOC content for posts lists flag.
	 */
	public $show_extended_toc = 1;

	/**
	 * Table of Contents show subpages flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $show_subpages The Table of Contents shows subpages by default.
	 */
	public $show_subpages = 1;

	/**
	 * Page title visibility flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $title_visibility Show page titles by default.
	 */
	public $title_visibility = 'show';

	/**
	 * Page meta visibility flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $page_meta_visibility Hide page meta by default.
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
	 * Default type of blog.
	 *
	 * Blog types are built as an array - eg, array('0' => 'Poetry','1' => 'Prose')
	 *
	 * @since 3.3
	 * @access public
	 * @var bool|int $blog_type The default type of blog.
	 */
	public $blog_type = false;

	/**
	 * Default blog workflow.
	 *
	 * Like "translation", for example, off by default.
	 *
	 * @since 3.3
	 * @access public
	 * @var bool $blog_workflow True if blog workflow enabled.
	 */
	public $blog_workflow = 0;

	/**
	 * Default sidebar tab.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $sidebar_default The default sidebar tab ('toc' == Contents tab).
	 */
	public $sidebar_default = 'toc';

	/**
	 * Default minimum page width (px).
	 *
	 * @since 3.0
	 * @access public
	 * @var str $min_page_width The default minimum page width in pixels.
	 */
	public $min_page_width = '447';

	/**
	 * "Live" comment refreshing.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $para_comments_live The "live" comment refreshing setting (off by default).
	 */
	public $para_comments_live = 0;

	/**
	 * Prevent save_post hook firing more than once.
	 *
	 * @since 3.3
	 * @access public
	 * @var str $saved_post True if post already saved.
	 */
	public $saved_post = false;

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
	 * "page" post type. This is what CommentPress was built for in the first
	 * place - to create a "document" from hierarchically-organised pages. This
	 * is not always the desired behaviour.
	 *
	 * @since 3.8.10
	 * @access public
	 * @var str $page_nav_enabled The page navigation flag ('y' or 'n').
	 */
	public $page_nav_enabled = 'y';

	/**
	 * Do Not Parse flag.
	 *
	 * When comments are closed on an entry and there are no comments on that
	 * entry, if this is set then the content will not be parsed for paragraphs,
	 * lines or blocks. Comments will also not be parsed, meaning that the entry
	 * behaves the same as content which is not commentable. This allows, for
	 * example, the rendering of the comment column to be skipped in these
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
	 * By default all post types are parsed by CommentPress. Post Types in this
	 * array will not be parsed. This effectively batch sets $do_not_parse for
	 * the Post Type.
	 *
	 * @since 3.9
	 * @access public
	 * @var str $post_types_disabled The post types not to be parsed.
	 */
	public $post_types_disabled = [];



	/**
	 * Initialises this object.
	 *
	 * @since 3.0
	 *
	 * @param object $parent_obj A reference to the parent object.
	 */
	public function __construct( $parent_obj ) {

		// Store reference to parent.
		$this->parent_obj = $parent_obj;

		// Init.
		$this->initialise();

	}



	/**
	 * Object initialisation.
	 *
	 * @since 3.0
	 */
	public function initialise() {

		// Load options array.
		$this->commentpress_options = $this->option_wp_get( 'commentpress_options', $this->commentpress_options );

		// Do immediate upgrades after the theme has loaded.
		add_action( 'after_setup_theme', [ $this, 'upgrade_immediately' ] );

	}



	/**
	 * Set up all items associated with this object.
	 *
	 * @since 3.0
	 */
	public function activate() {

		// Have we already got a modified database?
		$modified = $this->db_is_modified( 'comment_text_signature' ) ? 'y' : 'n';

		// If  we have an existing comment_text_signature column
		if ( $modified == 'y' ) {

			// Upgrade old CommentPress schema to new.
			if ( ! $this->schema_upgrade() ) {

				// Kill plugin activation.
				_cpdie( 'CommentPress Core Error: could not upgrade the database' );

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

			// Test if we have a existing pre-3.4 CommentPress instance.
			if ( commentpress_is_legacy_plugin_active() ) {

				// Yes: add options with existing values.
				$this->_options_migrate();

			} else {

				// No: add options with default values.
				$this->_options_create();

			}

		}

		// Retrieve data on special pages.
		$special_pages = $this->option_get( 'cp_special_pages', [] );

		// If we haven't created any.
		if ( count( $special_pages ) == 0 ) {

			// Create special pages.
			$this->create_special_pages();

		}

		// Turn comment paging option off.
		$this->_cancel_comment_paging();

		// Override widgets.
		$this->_clear_widgets();

	}



	/**
	 * Reset WordPress to prior state, but retain options.
	 *
	 * @since 3.0
	 */
	public function deactivate() {

		// Reset comment paging option.
		$this->_reset_comment_paging();

		// Restore widgets.
		$this->_reset_widgets();

		// Always remove special pages.
		$this->delete_special_pages();

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Public Methods
	 * -------------------------------------------------------------------------
	 */



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
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

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

		// Init.
		$return = false;

		// Construct query.
		$query = "ALTER TABLE `$wpdb->comments` CHANGE `comment_text_signature` `comment_signature` VARCHAR(255) NULL;";

		// Do the query to rename the column.
		$wpdb->query( $query );

		// Test if we now have the correct column name.
		if ( $this->db_is_modified( 'comment_signature' ) ) {

			// Yes.
			$result = true;

		}

		// --<
		return $result;
	}



	/**
	 * Do we have a column in the comments table?
	 *
	 * @since 3.0
	 *
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
		$cols = $wpdb->get_results( $query );

		// Loop.
		foreach( $cols AS $col ) {

			// Is it our desired column?
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
		if ( $version !== false AND version_compare( COMMENTPRESS_VERSION, $version, '>' ) ) {
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
		if ( ! $this->version_outdated() ) return false;

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

		// Do we have the option to choose which post types are supported (new in 3.9)?
		if ( ! $this->option_exists( 'cp_post_types_disabled' ) ) return true;

		// Do we have the option to choose not to parse content (new in 3.8.10)?
		if ( ! $this->option_exists( 'cp_do_not_parse' ) ) return true;

		// Do we have the option to choose to disable page navigation (new in 3.8.10)?
		if ( ! $this->option_exists( 'cp_page_nav_enabled' ) ) return true;

		// Do we have the option to choose to hide textblock meta (new in 3.5.9)?
		if ( ! $this->option_exists( 'cp_textblock_meta' ) ) return true;

		// Do we have the option to choose featured images (new in 3.5.4)?
		if ( ! $this->option_exists( 'cp_featured_images' ) ) return true;

		// Do we have the option to choose the default sidebar (new in 3.3.3)?
		if ( ! $this->option_exists( 'cp_sidebar_default' ) ) return true;

		// Do we have the option to show or hide page meta (new in 3.3.2)?
		if ( ! $this->option_exists( 'cp_page_meta_visibility' ) ) return true;

		// Do we have the option to choose blog type (new in 3.3.1)?
		if ( ! $this->option_exists( 'cp_blog_type' ) ) return true;

		// Do we have the option to choose blog workflow (new in 3.3.1)?
		if ( ! $this->option_exists( 'cp_blog_workflow' ) ) return true;

		// Do we have the option to choose the TOC layout (new in 3.3)?
		if ( ! $this->option_exists( 'cp_show_extended_toc' ) ) return true;

		// Do we have the option to set the comment editor?
		if ( ! $this->option_exists( 'cp_comment_editor' ) ) return true;

		// Do we have the option to set the default behaviour?
		if ( ! $this->option_exists( 'cp_promote_reading' ) ) return true;

		// Do we have the option to show or hide titles?
		if ( ! $this->option_exists( 'cp_title_visibility' ) ) return true;

		// Do we have the option to set the header bg colour?
		if ( ! $this->option_exists( 'cp_header_bg_colour' ) ) return true;

		// Do we have the option to set the scroll speed?
		if ( ! $this->option_exists( 'cp_js_scroll_speed' ) ) return true;

		// Do we have the option to set the minimum page width?
		if ( ! $this->option_exists( 'cp_min_page_width' ) ) return true;

		// --<
		return false;

	}



	/**
	 * Upgrade CommentPress plugin from 3.1 options to CommentPress Core set.
	 *
	 * @since 3.0
	 *
	 * @return boolean $result
	 */
	public function upgrade_options() {

		// Init return.
		$result = false;

		// If we have a CommentPress install (or we're forcing).
		if ( $this->upgrade_required() ) {

			// Are we missing the commentpress_options option?
			if ( ! $this->option_wp_exists( 'commentpress_options' ) ) {

				// Upgrade to the single array.
				$this->_options_upgrade();

			}

			// Checkboxes send no value if not checked, so use a default.
			$cp_blog_workflow = $this->blog_workflow;

			// We don't receive disabled post types in $_POST, so let's default.
			// To all post types being enabled
			$cp_post_types_enabled = array_keys( $this->get_supported_post_types() );

			// Default blog type.
			$cp_blog_type = $this->blog_type;

			// Get variables.
			extract( $_POST );

			// New in CommentPress Core 3.9 - post types can be excluded.
			if ( ! $this->option_exists( 'cp_post_types_disabled' ) ) {

				// Get selected post types.
				$enabled_types = array_map( 'esc_sql', $cp_post_types_enabled );

				// Exclude the selected post types.
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

			// New in CommentPress Core 3.8.10 - page navigation can be disabled.
			if ( ! $this->option_exists( 'cp_page_nav_enabled' ) ) {

				// Get choice.
				$choice = esc_sql( $cp_page_nav_enabled );

				// Add chosen page navigation option.
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

			// New in CommentPress Core 3.4 - has AJAX "live" comment refreshing been migrated?
			if ( ! $this->option_exists( 'cp_para_comments_live' ) ) {

				// "live" comment refreshing, off by default.
				$this->option_set( 'cp_para_comments_live', $this->para_comments_live );

			}

			// New in CommentPress 3.3.3 - changed the way the welcome page works.
			if ( $this->option_exists( 'cp_special_pages' ) ) {

				// Do we have the cp_welcome_page option?
				if ( $this->option_exists( 'cp_welcome_page' ) ) {

					// Get it.
					$page_id = $this->option_get( 'cp_welcome_page' );

					// Retrieve data on special pages.
					$special_pages = $this->option_get( 'cp_special_pages', [] );

					// Is it in our special pages array?
					if ( in_array( $page_id, $special_pages ) ) {

						// Remove page id from array.
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

			// New in CommentPress 3.3.1 - are we missing the cp_blog_workflow option?
			if ( ! $this->option_exists( 'cp_blog_workflow' ) ) {

				// Get choice.
				$choice = esc_sql( $cp_blog_workflow );

				// Add chosen cp_blog_workflow option.
				$this->option_set( 'cp_blog_workflow', $choice );

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

			// Do we have special pages?
			if ( $this->option_exists( 'cp_special_pages' ) ) {

				// If we don't have the toc page.
				if ( ! $this->option_exists( 'cp_toc_page' ) ) {

					// Get special pages array.
					$special_pages = $this->option_get( 'cp_special_pages', [] );

					// Create TOC page -> a convenience, let's us define a logo as attachment.
					$special_pages[] = $this->_create_toc_page();

					// Store the array of page IDs that were created.
					$this->option_set( 'cp_special_pages', $special_pages );

				}

			}

			// Save new CommentPress Core options.
			$this->options_save();

			// Store new CommentPress Core version.
			$this->option_wp_set( 'commentpress_version', COMMENTPRESS_VERSION );

		}

		// --<
		return $result;

	}



	/**
	 * Perform any plugin upgrades that do not have a setting on page load.
	 *
	 * Unlike `upgrade_options()` (which is only called when someone visits the
	 * CommentPress Core settings page), this method is called on every page
	 * load so that upgrades are performed immediately if required.
	 *
	 * @since 3.0
	 */
	public function upgrade_immediately() {

		// Bail if we do not have an outdated version.
		if ( ! $this->version_outdated() ) return;

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
		if ( 'deprecated' == $this->option_get( 'cp_header_bg_colour' ) ) return;

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
	 *
	 * @return bool $result True if successful, false otherwise.
	 */
	public function options_update() {

		// Init result.
		$result = false;

	 	// Was the form submitted?
		if( isset( $_POST['commentpress_submit'] ) ) {

			// Check that we trust the source of the data.
			check_admin_referer( 'commentpress_admin_action', 'commentpress_nonce' );

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
			$cp_blog_workflow = 0;
			$cp_sidebar_default = 'toc';
			$cp_featured_images = 'n';
			$cp_textblock_meta = 'y';
			$cp_page_nav_enabled = 'y';
			$cp_do_not_parse = 'y';

			// Assume all post types are enabled.
			$cp_post_types_enabled = array_keys( $this->get_supported_post_types() );

			// Get variables.
			extract( $_POST );

			// Hand off to Multisite first, in case we're deactivating.
			do_action( 'cpmu_deactivate_commentpress' );

			// Is Multisite activating CommentPress Core?
			if ( $cp_activate == '1' ) return true;

			// Did we ask to upgrade CommentPress Core?
			if ( $cp_upgrade == '1' ) {

				// Do upgrade.
				$this->upgrade_options();

				// --<
				return true;

			}

			// Did we ask to reset?
			if ( $cp_reset == '1' ) {

				// Reset theme options.
				$this->_options_reset();

				// --<
				return true;

			}

			// Did we ask to auto-create special pages?
			if ( $cp_create_pages == '1' ) {

				// Remove any existing special pages.
				$this->delete_special_pages();

				// Create special pages.
				$this->create_special_pages();

			}

			// Did we ask to delete special pages?
			if ( $cp_delete_pages == '1' ) {

				// Remove special pages.
				$this->delete_special_pages();

			}

			// Let's deal with our params now.

			// Individual special pages.
			//$cp_welcome_page = esc_sql( $cp_welcome_page );
			//$cp_blog_page = esc_sql( $cp_blog_page );
			//$cp_general_comments_page = esc_sql( $cp_general_comments_page );
			//$cp_all_comments_page = esc_sql( $cp_all_comments_page );
			//$cp_comments_by_page = esc_sql( $cp_comments_by_page );
			//$this->option_set( 'cp_welcome_page', $cp_welcome_page );
			//$this->option_set( 'cp_blog_page', $cp_blog_page );
			//$this->option_set( 'cp_general_comments_page', $cp_general_comments_page );
			//$this->option_set( 'cp_all_comments_page', $cp_all_comments_page );
			//$this->option_set( 'cp_comments_by_page', $cp_comments_by_page );

			// TOC content.
			$cp_show_posts_or_pages_in_toc = esc_sql( $cp_show_posts_or_pages_in_toc );
			$this->option_set( 'cp_show_posts_or_pages_in_toc', $cp_show_posts_or_pages_in_toc );

			// If we have pages in TOC and a value for the next param.
			if ( $cp_show_posts_or_pages_in_toc == 'page' AND isset( $cp_toc_chapter_is_page ) ) {

				$cp_toc_chapter_is_page = esc_sql( $cp_toc_chapter_is_page );
				$this->option_set( 'cp_toc_chapter_is_page', $cp_toc_chapter_is_page );

				// If chapters are not pages and we have a value for the next param.
				if ( $cp_toc_chapter_is_page == '0' ) {

					$cp_show_subpages = esc_sql( $cp_show_subpages );
					$this->option_set( 'cp_show_subpages', ( $cp_show_subpages ? 1 : 0 ) );

				} else {

					// Always set to show subpages.
					$this->option_set( 'cp_show_subpages', 1 );

				}

			}

			// Extended or vanilla posts TOC.
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

			// Has AJAX "live" comment refreshing been migrated?
			if ( $this->option_exists( 'cp_para_comments_live' ) ) {

				// "live" comment refreshing.
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

			// Save min page width.
			$cp_min_page_width = esc_sql( $cp_min_page_width );
			$this->option_set( 'cp_min_page_width', $cp_min_page_width );

			// Save workflow.
			$cp_blog_workflow = esc_sql( $cp_blog_workflow );
			$this->option_set( 'cp_blog_workflow', ( $cp_blog_workflow ? 1 : 0 ) );

			// Save blog type.
			$cp_blog_type = esc_sql( $cp_blog_type );
			$this->option_set( 'cp_blog_type', $cp_blog_type );

			// If it's a groupblog.
			if ( $this->parent_obj->is_groupblog() ) {

				// Get the group's id.
				$group_id = get_groupblog_group_id( get_current_blog_id() );
				if ( isset( $group_id ) AND is_numeric( $group_id ) AND $group_id > 0 ) {

					/**
					 * Allow plugins to override the blog type - for example if workflow
					 * is enabled, it might become a new blog type as far as BuddyPress
					 * is concerned.
					 *
					 * @param int $cp_blog_type The numeric blog type.
					 * @param bool $cp_blog_workflow True if workflow enabled, false otherwise.
					 */
					$blog_type = apply_filters( 'cp_get_group_meta_for_blog_type', $cp_blog_type, $cp_blog_workflow );

					// Set the type as group meta info.
					groups_update_groupmeta( $group_id, 'groupblogtype', 'groupblogtype-' . $blog_type );

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

			// Save page navigation enabled flag.
			$cp_page_nav_enabled = esc_sql( $cp_page_nav_enabled );
			$this->option_set( 'cp_page_nav_enabled', $cp_page_nav_enabled );

			// Save do not parse flag.
			$cp_do_not_parse = esc_sql( $cp_do_not_parse );
			$this->option_set( 'cp_do_not_parse', $cp_do_not_parse );

			// Do we have the post types option?
			if ( $this->option_exists( 'cp_post_types_disabled' ) ) {

				// Get selected post types.
				$enabled_types = array_map( 'esc_sql', $cp_post_types_enabled );

				// Exclude the selected post types.
				$disabled_types = array_diff( array_keys( $this->get_supported_post_types() ), $enabled_types );

				// Save skipped post types.
				$this->option_set( 'cp_post_types_disabled', $disabled_types );

			}

			// Save.
			$this->options_save();

			// Set flag.
			$result = true;

			/**
			 * Allow other plugins to hook into the update process.
			 */
			do_action( 'commentpress_admin_page_options_updated' );

		}

		// --<
		return $result;

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
		return ( array_key_exists( $option_name, $this->commentpress_options ) ) ? $this->commentpress_options[$option_name] : $default;

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
		$this->commentpress_options[$option_name] = $value;

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
		unset( $this->commentpress_options[$option_name] );

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
	 * When a page is saved, this also saves the CommentPress Core options.
	 *
	 * @since 3.4
	 *
	 * @param object $post_obj The post object.
	 */
	public function save_meta( $post_obj ) {

		// If no post, kick out.
		if ( ! $post_obj ) return;

		// If page.
		if ( $post_obj->post_type == 'page' ) {
			$this->save_page_meta( $post_obj );
		}

		// If post.
		if ( $post_obj->post_type == 'post' ) {
			$this->save_post_meta( $post_obj );
		}

	}



	/**
	 * When a page is saved, this also saves the CommentPress Core options.
	 *
	 * @since 3.4
	 *
	 * @param object $post_obj The post object.
	 */
	public function save_page_meta( $post_obj ) {

		// Bail if we're not authenticated.
		if ( ! $this->save_page_meta_authenticated( $post_obj ) ) return;

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

		// Save page title visibility.
		$this->save_page_title_visibility( $post );

		// Save page meta visibility.
		$this->save_page_meta_visibility( $post );

		// Save page numbering.
		$this->save_page_numbering( $post );

		// Save page layout for Title Page.
		$this->save_page_layout( $post );

		// Save post formatter (overrides blog_type).
		$this->save_formatter( $post );

		// Save default sidebar.
		$this->save_default_sidebar( $post );

		// Save starting paragraph number.
		$this->save_starting_paragraph( $post );

		// Save workflow meta.
		$this->save_workflow( $post );

	}



	/**
	 * When a page is saved, this authenticates that our options can be saved.
	 *
	 * @since 3.4
	 *
	 * @param object $post_obj The post object.
	 */
	public function save_page_meta_authenticated( $post_obj ) {

		// If no post, kick out.
		if ( ! $post_obj ) return false;

		// If not page, kick out.
		if ( $post_obj->post_type != 'page' ) return false;

		// Authenticate.
		$nonce = isset( $_POST['commentpress_nonce'] ) ? $_POST['commentpress_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'commentpress_page_settings' ) ) return false;

		// Is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) AND DOING_AUTOSAVE ) return false;

		// Check permissions - 'edit_pages' is available to editor+
		if ( ! current_user_can( 'edit_pages' ) ) return false;

		// Good to go.
		return true;

	}



	/**
	 * Save Page Title visibility.
	 *
	 * @since 3.4
	 *
	 * @param object $post The post object.
	 * @return string $data Either 'show' (default) or ''.
	 */
	public function save_page_title_visibility( $post ) {

		// Find and save the data.
		$data = ( isset( $_POST['cp_title_visibility'] ) ) ? $_POST['cp_title_visibility'] : 'show';

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
	 * @param object $post The post object.
	 * @return string $data Either 'hide' (default) or ''.
	 */
	public function save_page_meta_visibility( $post ) {

		// Find and save the data.
		$data = ( isset( $_POST['cp_page_meta_visibility'] ) ) ? $_POST['cp_page_meta_visibility'] : 'hide';

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
	 * Only first top-level page is allowed to save this.
	 *
	 * @param object $post The post object.
	 */
	public function save_page_numbering( $post ) {

		// Was the value sent?
		if ( isset( $_POST['cp_number_format'] ) ) {

			// Set meta key
			$key = '_cp_number_format';

			// Do we need to check this, since only the first top level page
			// can now send this data? doesn't hurt to validate, I guess.
			if (
				$post->post_parent == '0' AND
				! $this->is_special_page() AND
				$post->ID == $this->parent_obj->nav->get_first_page()
			) {

				// Get the data.
				$data = $_POST['cp_number_format'];

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

			// Delete this meta value from all other pages, because we may have altered
			// the relationship between pages, thus causing the page numbering to fail.

			// Get all pages including chapters.
			$all_pages = $this->parent_obj->nav->get_book_pages( 'structural' );

			// If we have any pages.
			if ( count( $all_pages ) > 0 ) {

				// Loop.
				foreach( $all_pages AS $page ) {

					// Exclude first top level page.
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
	 * @param object $post The post object.
	 */
	public function save_page_layout( $post ) {

		// Is this the title page?
		if ( $post->ID == $this->option_get( 'cp_welcome_page' ) ) {

			// Find and save the data.
			$data = ( isset( $_POST['cp_page_layout'] ) ) ? $_POST['cp_page_layout'] : 'text';

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
	 * When a post is saved, this also saves the CommentPress Core options.
	 *
	 * @since 3.0
	 *
	 * @param object $post_obj The post object.
	 */
	public function save_post_meta( $post_obj ) {

		// Bail if we're not authenticated.
		if ( ! $this->save_post_meta_authenticated( $post_obj ) ) return;

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

		// Save post formatter (overrides blog_type).
		$this->save_formatter( $post );

		// Save workflow meta.
		$this->save_workflow( $post );

		// Save default sidebar.
		$this->save_default_sidebar( $post );

		// ---------------------------------------------------------------------
		// Create new post with content of current.
		// ---------------------------------------------------------------------

		// Find and save the data.
		$data = ( isset( $_POST['commentpress_new_post'] ) ) ? $_POST['commentpress_new_post'] : '0';

		// Do we want to create a new revision?
		if ( $data == '0' ) return;



		// We need to make sure this only runs once.
		if ( $this->saved_post === false ) {
			$this->saved_post = true;
		} else {
			return;
		}

		// ---------------------------------------------------------------------

		// We're through: create it.
		$new_post_id = $this->_create_new_post( $post );

		// ---------------------------------------------------------------------
		// Store ID of new version in current version.
		// ---------------------------------------------------------------------

		// Set key.
		$key = '_cp_newer_version';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Delete the meta_key if empty string.
			if ( $data === '' ) {
				delete_post_meta( $post->ID, $key );
			} else {
				update_post_meta( $post->ID, $key, $new_post_id );
			}

		} else {

			// Add the data.
			add_post_meta( $post->ID, $key, $new_post_id );

		}

		// ---------------------------------------------------------------------
		// Store incremental version number in new version
		// ---------------------------------------------------------------------

		// Set key.
		$key = '_cp_version_count';

		// If the custom field of our current post has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Get current value.
			$value = get_post_meta( $post->ID, $key, true );

			// Increment.
			$value++;

		} else {

			// This must be the first new version (Draft 2).
			$value = 2;

		}

		// Add the data.
		add_post_meta( $new_post_id, $key, $value );

		// ---------------------------------------------------------------------
		// Store formatter in new version
		// ---------------------------------------------------------------------

		// Set key.
		$key = '_cp_post_type_override';

		// If we have one set.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Get current value.
			$formatter = get_post_meta( $post->ID, $key, true );

			// Add the data.
			add_post_meta( $new_post_id, $key, esc_sql( $formatter ) );

		}

		// Allow plugins to hook into this.
		do_action( 'cp_workflow_save_copy', $new_post_id );

		// Get the edit post link.
		//$edit_link = get_edit_post_link( $new_post_id );

		// Redirect there?

	}



	/**
	 * When a post is saved, this authenticates that our options can be saved.
	 *
	 * @since 3.4
	 *
	 * @param object $post_obj The post object.
	 */
	public function save_post_meta_authenticated( $post_obj ) {

		// If no post, kick out.
		if ( ! $post_obj ) return false;

		// If not page, kick out.
		if ( $post_obj->post_type != 'post' ) return false;

		// Authenticate.
		$nonce = isset( $_POST['commentpress_nonce'] ) ? $_POST['commentpress_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'commentpress_post_settings' ) ) return false;

		// Is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) AND DOING_AUTOSAVE ) return false;

		// Check permissions - 'edit_posts' is available to contributor+.
		if ( ! current_user_can( 'edit_posts', $post_obj->ID ) ) return false;

		// Good to go.
		return true;

	}



	/**
	 * Override post formatter.
	 *
	 * This overrides the "blog_type" for a post.
	 *
	 * @since 3.4
	 *
	 * @param object $post The post object.
	 */
	public function save_formatter( $post ) {

		// Get the data.
		$data = ( isset( $_POST['cp_post_type_override'] ) ) ? $_POST['cp_post_type_override'] : '';

		// Set key.
		$key = '_cp_post_type_override';

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



	/**
	 * Override default sidebar.
	 *
	 * @since 3.4
	 *
	 * @param object $post The post object.
	 */
	public function save_default_sidebar( $post ) {

		// Allow this to be disabled.
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) return;

		// Do we have the option to choose the default sidebar (new in 3.3.3)?
		if ( $this->option_exists( 'cp_sidebar_default' ) ) {

			// Find and save the data.
			$data = ( isset( $_POST['cp_sidebar_default'] ) ) ?
					 $_POST['cp_sidebar_default'] :
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
	 * @param object $post The post object.
	 */
	public function save_starting_paragraph( $post ) {

		// Get the data.
		$data = ( isset( $_POST['cp_starting_para_number'] ) ) ? $_POST['cp_starting_para_number'] : 1;

		// If not numeric, set to default.
		if ( ! is_numeric( $data ) ) { $data = 1; }

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
	 * Save workflow meta value.
	 *
	 * @since 3.4
	 *
	 * @param object $post The post object.
	 */
	public function save_workflow( $post ) {

		// Do we have the option to set workflow (new in 3.3.1)?
		if ( $this->option_exists( 'cp_blog_workflow' ) ) {

			// Get workflow setting for the blog.
			$workflow = $this->option_get( 'cp_blog_workflow' );

			/*
			// ----------------
			// WORK IN PROGRESS

			// Set key.
			$key = '_cp_blog_workflow_override';

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// Get existing value
				$workflow = get_post_meta( $post->ID, $key, true );

			}
			// ----------------
			*/

			// If it's enabled.
			if ( $workflow == '1' ) {

				// Notify plugins that workflow stuff needs saving.
				do_action( 'cp_workflow_save_' . $post->post_type, $post );

			}

			/*
			// ----------------
			// WORK IN PROGRESS

			// Get the setting for the post (we do this after saving the extra
			// Post data because
			$formatter = ( isset( $_POST['cp_post_type_override'] ) ) ? $_POST['cp_post_type_override'] : '';

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// If empty string
				if ( $data === '' ) {

					// Delete the meta_key
					delete_post_meta( $post->ID, $key );

				} else {

					// Update the data.
					update_post_meta( $post->ID, $key, esc_sql( $data ) );

				}

			} else {

				// Add the data.
				add_post_meta( $post->ID, $key, esc_sql( $data ) );

			}
			// ----------------
			*/

		}

	}



	/**
	 * When a page is deleted, this makes sure that the CommentPress Core options are synced.
	 *
	 * @since 3.4
	 *
	 * @param object $post_id The post ID.
	 */
	public function delete_meta( $post_id ) {

		// If no post, kick out.
		if ( ! $post_id ) return;

		// If it's our welcome page.
		if ( $post_id == $this->option_get( 'cp_welcome_page' ) ) {

			// Delete option.
			$this->option_delete( 'cp_welcome_page' );

			// Save.
			$this->options_save();

		}

		// For posts with versions, we need to delete the version data for the previous version.

		// Define key.
		$key = '_cp_newer_version';

		// Get posts with the about-to-be-deleted post_id (there will be only one, if at all).
		$previous_versions = get_posts( [
			'meta_key' => $key,
			'meta_value' => $post_id,
		] );

		// Did we get one?
		if ( count( $previous_versions ) > 0 ) {

			// Get it.
			$previous_version = $previous_versions[0];

			// If the custom field has a value.
			if ( get_post_meta( $previous_version->ID, $key, true ) !== '' ) {

				// Delete it.
				delete_post_meta( $previous_version->ID, $key );

			}

		}

	}



	/**
	 * Create all "special" pages.
	 *
	 * @since 3.4
	 */
	public function create_special_pages() {

		/*
		 * One of the CommentPress Core themes MUST be active or WordPress will
		 * fail to set the page templates for the pages that require them.
		 *
		 * Also, a user must be logged in for these pages to be associated with them.
		 */

		// Get special pages array, if it's there.
		$special_pages = $this->option_get( 'cp_special_pages', [] );

		// Create welcome/title page, but don't add to special pages.
		$welcome = $this->_create_title_page();

		// Create general comments page.
		$special_pages[] = $this->_create_general_comments_page();

		// Create all comments page.
		$special_pages[] = $this->_create_all_comments_page();

		// Create comments by author page.
		$special_pages[] = $this->_create_comments_by_author_page();

		// Create blog page.
		$special_pages[] = $this->_create_blog_page();

		// Create blog archive page.
		$special_pages[] = $this->_create_blog_archive_page();

		// Create TOC page -> a convenience, let's us define a logo as attachment.
		$special_pages[] = $this->_create_toc_page();

		// Store the array of page IDs that were created.
		$this->option_set( 'cp_special_pages', $special_pages );

		// Save changes.
		$this->options_save();

	}



	/**
	 * Create a particular "special" page.
	 *
	 * @since 3.4
	 *
	 * @param str $page The type of special page.
	 * @return mixed $new_id If successful, the numeric ID of the new page, false on failure.
	 */
	public function create_special_page( $page ) {

		// Init.
		$new_id = false;

		// Get special pages array, if it's there.
		$special_pages = $this->option_get( 'cp_special_pages', [] );

		// Switch by page.
		switch( $page ) {

			case 'title':

				// Create welcome/title page.
				$new_id = $this->_create_title_page();
				break;

			case 'general_comments':

				// Create general comments page.
				$new_id = $this->_create_general_comments_page();
				break;

			case 'all_comments':

				// Create all comments page.
				$new_id = $this->_create_all_comments_page();
				break;

			case 'comments_by_author':

				// Create comments by author page.
				$new_id = $this->_create_comments_by_author_page();
				break;

			case 'blog':

				// Create blog page.
				$new_id = $this->_create_blog_page();
				break;

			case 'blog_archive':

				// Create blog page.
				$new_id = $this->_create_blog_archive_page();
				break;

			case 'toc':

				// Create TOC page.
				$new_id = $this->_create_toc_page();
				break;

		}

		// Add to special pages.
		$special_pages[] = $new_id;

		// Reset option.
		$this->option_set( 'cp_special_pages', $special_pages );

		// Save changes.
		$this->options_save();

		// --<
		return $new_id;

	}



	/**
	 * Delete "special" pages.
	 *
	 * @since 3.4
	 *
	 * @return bool $success True if page deleted successfully, false otherwise.
	 */
	public function delete_special_pages() {

		// Init success flag.
		$success = true;

		/*
		 * Only delete special pages if we have one of the CommentPress Core
		 * themes active because other themes may have a totally different way
		 * of presenting the content of the blog.
		 */

		// Retrieve data on special pages.
		$special_pages = $this->option_get( 'cp_special_pages', [] );

		// If we have created any.
		if ( is_array( $special_pages ) AND count( $special_pages ) > 0 ) {

			// Loop through them.
			foreach( $special_pages AS $special_page ) {

				// Bypass trash.
				$force_delete = true;

				// Try and delete each page.
				if ( ! wp_delete_post( $special_page, $force_delete ) ) {

					// Oops, set success flag to false.
					$success = false;

				}

			}

			// Delete the corresponding options.
			$this->option_delete( 'cp_special_pages' );

			$this->option_delete( 'cp_blog_page' );
			$this->option_delete( 'cp_blog_archive_page' );
			$this->option_delete( 'cp_general_comments_page' );
			$this->option_delete( 'cp_all_comments_page' );
			$this->option_delete( 'cp_comments_by_page' );
			$this->option_delete( 'cp_toc_page' );

			// For now, keep welcome page - delete option when page is deleted.
			//$this->option_delete( 'cp_welcome_page' );

			// Save changes.
			$this->options_save();

			// Reset WordPress internal page references.
			$this->_reset_wordpress_option( 'show_on_front' );
			$this->_reset_wordpress_option( 'page_on_front' );
			$this->_reset_wordpress_option( 'page_for_posts' );

		}

		// --<
		return $success;

	}



	/**
	 * Delete a particular "special" page.
	 *
	 * @since 3.4
	 *
	 * @param str $page The type of special page to delete.
	 * @return boolean $success True if succesfully deleted false otherwise.
	 */
	public function delete_special_page( $page ) {

		// Init success flag.
		$success = true;

		/*
		 * Only delete a special page if we have one of the CommentPress Core
		 * themes active because other themes may have a totally different way
		 * of presenting the content of the blog.
		 */

		// Get id of special page.
		switch( $page ) {

			case 'title':

				// Set flag.
				$flag = 'cp_welcome_page';

				// Reset WordPress internal page references.
				$this->_reset_wordpress_option( 'show_on_front' );
				$this->_reset_wordpress_option( 'page_on_front' );

				break;

			case 'general_comments':

				// Set flag.
				$flag = 'cp_general_comments_page';
				break;

			case 'all_comments':

				// Set flag.
				$flag = 'cp_all_comments_page';
				break;

			case 'comments_by_author':

				// Set flag.
				$flag = 'cp_comments_by_page';
				break;

			case 'blog':

				// Set flag.
				$flag = 'cp_blog_page';

				// Reset WordPress internal page reference.
				$this->_reset_wordpress_option( 'page_for_posts' );

				break;

			case 'blog_archive':

				// Set flag.
				$flag = 'cp_blog_archive_page';
				break;

			case 'toc':

				// Set flag.
				$flag = 'cp_toc_page';
				break;

		}

		// Get page ID.
		$page_id = $this->option_get( $flag );

		// Kick out if it doesn't exist.
		if ( ! $page_id ) return true;

		// Delete option.
		$this->option_delete( $flag );

		// Bypass trash.
		$force_delete = true;

		// Try and delete the page.
		if ( ! wp_delete_post( $page_id, $force_delete ) ) {

			// Oops, set success flag to false.
			$success = false;

		}

		// Retrieve data on special pages.
		$special_pages = $this->option_get( 'cp_special_pages', [] );

		// Is it in our special pages array?
		if ( in_array( $page_id, $special_pages ) ) {

			// Remove page id from array.
			$special_pages = array_diff( $special_pages, [ $page_id ] );

			// Reset option.
			$this->option_set( 'cp_special_pages', $special_pages );

		}

		// Save changes.
		$this->options_save();

		// --<
		return $success;

	}



	/**
	 * Test if a page is a "special" page.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_special_page True if a special page, false otherwise.
	 */
	public function is_special_page() {

		// Init flag.
		$is_special_page = false;

		// Access post object.
		global $post;

		// Do we have one?
		if ( ! is_object( $post ) ) {

			// --<
			return $is_special_page;

		}

		// Get special pages.
		$special_pages = $this->option_get( 'cp_special_pages', [] );

		// Do we have a special page array?
		if ( is_array( $special_pages ) AND count( $special_pages ) > 0 ) {

			// Is the current page one?
			if ( in_array( $post->ID, $special_pages ) ) {

				// It is.
				$is_special_page = true;

			}

		}

		// --<
		return $is_special_page;

	}



	/**
	 * Get WordPress post types that CommentPress supports.
	 *
	 * @since 3.9
	 *
	 * @return array $supported_post_types Array of post types that have an editor.
	 */
	public function get_supported_post_types() {

		// Only parse post types once.
		static $supported_post_types = [];
		if ( ! empty( $supported_post_types ) ) {
			return $supported_post_types;
		}

		// Get only post types with an admin UI.
		$args = [
			'public' => true,
			'show_ui' => true,
		];

		// Get post types.
		$post_types = get_post_types( $args, 'objects' );

		// Include only those which have an editor.
		foreach ( $post_types AS $post_type ) {
			if ( post_type_supports( $post_type->name, 'editor' ) ) {
				$supported_post_types[$post_type->name] = $post_type->label;
			}
		}

		// Built-in media descriptions are also supported.
		$attachment = get_post_type_object( 'attachment' );
		$supported_post_types[$attachment->name] = $attachment->label;

		// --<
		return $supported_post_types;

	}



	/**
	 * Check if a post allows comments to be posted.
	 *
	 * @since 3.4
	 *
	 * @return boolean $allowed True if comments enabled, false otherwise.
	 */
	public function comments_enabled() {

		// Init return.
		$allowed = false;

		// Access post object.
		global $post;

		// Do we have one?
		if ( ! is_object( $post ) ) {

			// --<
			return $allowed;

		}

		// Are comments enabled on this post?
		if ( $post->comment_status == 'open' ) {

			// Set return.
			$allowed = true;

		}

		// --<
		return $allowed;
	}



	/**
	 * Get WordPress approved comments.
	 *
	 * @since 3.4
	 *
	 * @param int $post_id The numeric ID of the post.
	 * @return array $comments The array of comment data.
	 */
	public function get_approved_comments( $post_ID ) {

		// For WordPress, we use the API.
		$comments = get_approved_comments( $post_ID );

		// --<
		return $comments;

	}



	/**
	 * Get all WordPress comments for a post, unless paged.
	 *
	 * @since 3.4
	 *
	 * @param int $post_ID The numeric ID of the post.
	 * @return array $comments The array of comment data.
	 */
	public function get_all_comments( $post_ID ) {

		// Access post.
		global $post;

		// For WordPress, we use the API.
		$comments = get_comments( 'post_id=' . $post_ID . '&order=ASC' );

		// --<
		return $comments;

	}



	/**
	 * Get all comments for a post.
	 *
	 * @since 3.4
	 *
	 * @param int $post_ID The ID of the post.
	 * @return array $comments The array of comment data.
	 */
	public function get_comments( $post_ID ) {

		// Database object.
		global $wpdb;

		// Get comments from db.
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
	 * When a comment is saved, this also saves the text signature.
	 *
	 * @since 3.0
	 *
	 * @param int $comment_id The numeric ID of the comment.
	 * @return boolean $result True if successful, false otherwise.
	 */
	public function save_comment_signature( $comment_ID ) {

		// Database object.
		global $wpdb;

		// Get text signature.
		$text_signature = ( isset( $_POST['text_signature'] ) ) ? $_POST['text_signature'] : '';

		// Did we get one?
		if ( $text_signature != '' ) {

			// Escape it.
			$text_signature = esc_sql( $text_signature );

			// Construct query.
			$query = $wpdb->prepare(
				"UPDATE $wpdb->comments SET comment_signature = %s WHERE comment_ID = %d",
				$text_signature,
				$comment_ID
			);

			// Store comment signature.
			$result = $wpdb->query( $query );

		} else {

			// Set result to true - why not, eh?
			$result = true;

		}

		// --<
		return $result;

	}



	/**
	 * When a comment is saved, this also saves the text selection.
	 *
	 * @since 3.9
	 *
	 * @param int $comment_id The numeric ID of the comment.
	 * @return boolean $result True if successful, false otherwise.
	 */
	public function save_comment_selection( $comment_id ) {

		// Get text selection.
		$text_selection = ( isset( $_POST['text_selection'] ) ) ? $_POST['text_selection'] : '';

		// Bail if we didn't get one.
		if ( $text_selection == '' ) return true;

		// Sanity check: must have a comma.
		if ( stristr( $text_selection, ',' ) === false ) return true;

		// Make into an array.
		$selection = explode( ',', $text_selection );

		// Sanity check: must have only two elements.
		if ( count( $selection ) != 2 ) return true;

		// Sanity check: both elements must be integers.
		$start_end = [];
		foreach( $selection AS $item ) {

			// Not integer - kick out.
			if ( ! is_numeric( $item ) ) return true;

			// Cast as integer and add to array.
			$start_end[] = absint( $item );

		}

		// Okay, we're good to go.
		$selection_data = implode( ',', $start_end );

		// Set key.
		$key = '_cp_comment_selection';

		// Get current.
		$current = get_comment_meta( $comment_id, $key, true );

		// If the comment meta already has a value.
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
	 * When a comment is saved, this also saves the page it was submitted on.
	 *
	 * This allows us to point to the correct page of a multipage post without
	 * parsing the content every time.
	 *
	 * @since 3.4
	 *
	 * @param int $comment_ID The numeric ID of the comment.
	 */
	public function save_comment_page( $comment_ID ) {

		// Is this a paged post?
		if ( isset( $_POST['page'] ) AND is_numeric( $_POST['page'] ) ) {

			// Get text signature.
			$text_signature = ( isset( $_POST['text_signature'] ) ) ? $_POST['text_signature'] : '';

			// Is it a para-level comment?
			if ( $text_signature != '' ) {

				// Get page number.
				$page_number = esc_sql( $_POST['page'] );

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

				// Top level comments are always page 1.
				//$page_number = 1;

			}

		}

	}



	/**
	 * Retrieves text signature by comment ID.
	 *
	 * @since 3.4
	 *
	 * @param int $comment_ID The numeric ID of the comment.
	 * @return str $text_signature The text signature for the comment.
	 */
	public function get_text_signature_by_comment_id( $comment_ID ) {

		// Database object.
		global $wpdb;

		// Query for signature.
		$text_signature = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT comment_signature FROM $wpdb->comments WHERE comment_ID = %s",
				$comment_ID
			)
		);

		// --<
		return $text_signature;

	}



	/**
	 * Store text sigs in a global.
	 *
	 * This is needed because some versions of PHP do not save properties!
	 *
	 * @since 3.4
	 *
	 * @param array $sigs An array of text signatures.
	 */
	public function set_text_sigs( $sigs ) {

		// Store them.
		global $ffffff_sigs;
		$ffffff_sigs = $sigs;

	}



	/**
	 * Retrieve text sigs.
	 *
	 * @since 3.4
	 *
	 * @return array $text_signatures An array of text signatures.
	 */
	public function get_text_sigs() {

		// Get them.
		global $ffffff_sigs;
		return $ffffff_sigs;

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

		// Add comments open.
		global $post;

		// If we don't have a post (like on the 404 page).
		if ( ! is_object( $post ) ) {

			// Comments must be closed.
			$vars['cp_comments_open'] = 'n';

			// Set empty permalink.
			$vars['cp_permalink'] = '';

		} else {

			// Check for post comment_status.
			$vars['cp_comments_open'] = ( $post->comment_status == 'open' ) ? 'y' : 'n';

			// Set post permalink.
			$vars['cp_permalink'] = get_permalink( $post->ID );

		}

		// Assume no admin bars.
		$vars['cp_wp_adminbar'] = 'n';
		$vars['cp_bp_adminbar'] = 'n';

		// Assume pre-3.8 admin bar.
		$vars['cp_wp_adminbar_height'] = '28';
		$vars['cp_wp_adminbar_expanded'] = '0';

		// Are we showing the WP admin bar?
		if ( function_exists( 'is_admin_bar_showing' ) AND is_admin_bar_showing() ) {

			// We have it.
			$vars['cp_wp_adminbar'] = 'y';

			// Check for a WP 3.8+ function.
			if ( function_exists( 'wp_admin_bar_sidebar_toggle' ) ) {

				// The 3.8+ admin bar is taller.
				$vars['cp_wp_adminbar_height'] = '32';

				// It also expands in height below 782px viewport width.
				$vars['cp_wp_adminbar_expanded'] = '46';

			}

		}

		// Are we logged in AND in a BuddyPress scenario?
		if ( is_user_logged_in() AND $this->parent_obj->is_buddypress() ) {

			// Regardless of version, settings can be made in bp-custom.php.
			if ( defined( 'BP_DISABLE_ADMIN_BAR' ) AND BP_DISABLE_ADMIN_BAR ) {

				// We've killed both admin bars.
				$vars['cp_bp_adminbar'] = 'n';
				$vars['cp_wp_adminbar'] = 'n';

			}

			// Check for BuddyPress versions prior to 1.6 (1.6 uses the WP admin bar instead of a custom one).
			if ( ! function_exists( 'bp_get_version' ) ) {

				// But, this can already be overridden in bp-custom.php.
				if ( defined( 'BP_USE_WP_ADMIN_BAR' ) AND BP_USE_WP_ADMIN_BAR ) {

					// Not present.
					$vars['cp_bp_adminbar'] = 'n';
					$vars['cp_wp_adminbar'] = 'y';

				} else {

					// Let our javascript know.
					$vars['cp_bp_adminbar'] = 'y';

					// Recheck 'BP_DISABLE_ADMIN_BAR'.
					if ( defined( 'BP_DISABLE_ADMIN_BAR' ) AND BP_DISABLE_ADMIN_BAR ) {

						// We've killed both admin bars.
						$vars['cp_bp_adminbar'] = 'n';
						$vars['cp_wp_adminbar'] = 'n';

					}

				}

			}

		}

		// Add rich text editor.
		$vars['cp_tinymce'] = 1;

		// Check if users must be logged in to comment.
		if ( get_option( 'comment_registration' ) == '1' AND ! is_user_logged_in() ) {

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Check CommentPress Core option.
		if (
			$this->option_exists( 'cp_comment_editor' ) AND
			$this->option_get( 'cp_comment_editor' ) != '1'
		) {

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// If on a public groupblog and user isn't logged in.
		if ( $this->parent_obj->is_groupblog() AND ! is_user_logged_in() ) {

			// Don't add rich text editor, because only members can comment.
			$vars['cp_tinymce'] = 0;

		}

		// Allow plugins to override TinyMCE.
		$vars['cp_tinymce'] = apply_filters(
			'cp_override_tinymce',
			$vars['cp_tinymce']
		);

		// Add mobile var.
		$vars['cp_is_mobile'] = 0;

		// Is it a mobile?
		if ( isset( $this->is_mobile ) AND $this->is_mobile ) {

			// Is mobile.
			$vars['cp_is_mobile'] = 1;

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Add touch var.
		$vars['cp_is_touch'] = 0;

		// Is it a touch device?
		if ( isset( $this->is_mobile_touch ) AND $this->is_mobile_touch ) {

			// Is touch.
			$vars['cp_is_touch'] = 1;

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Add touch testing var.
		$vars['cp_touch_testing'] = 0;

		// Have we set our testing constant?
		if ( defined( 'COMMENTPRESS_TOUCH_SELECT' ) AND COMMENTPRESS_TOUCH_SELECT ) {

			// Support touch device testing.
			$vars['cp_touch_testing'] = 1;

		}

		// Add tablet var.
		$vars['cp_is_tablet'] = 0;

		// Is it a touch device?
		if ( isset( $this->is_tablet ) AND $this->is_tablet ) {

			// Is touch.
			$vars['cp_is_tablet'] = 1;

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Add TinyMCE version var.
		$vars['cp_tinymce_version'] = 3;

		// Access WP version.
		global $wp_version;

		// If greater than 3.8.
		if ( version_compare( $wp_version, '3.8.9999', '>' ) ) {

			// Add newer TinyMCE version.
			$vars['cp_tinymce_version'] = 4;

		}

		// Add rich text editor behaviour.
		$vars['cp_promote_reading'] = 1;

		// Check option.
		if (
			$this->option_exists( 'cp_promote_reading' ) AND
			$this->option_get( 'cp_promote_reading' ) != '1'
		) {

			// Promote commenting.
			$vars['cp_promote_reading'] = 0;

		}

		// Add special page var.
		$vars['cp_special_page'] = ( $this->is_special_page() ) ? '1' : '0';

		// Are we in a BuddyPress scenario?
		if ( $this->parent_obj->is_buddypress() ) {

			// Is it a component homepage?
			if ( $this->parent_obj->is_buddypress_special_page() ) {

				// Treat them the way we do ours.
				$vars['cp_special_page'] = '1';

			}

		}

		// Get path.
		$url_info = parse_url( get_option('siteurl') );

		// Add path for cookies.
		$vars['cp_cookie_path'] = '/';
		if ( isset( $url_info['path'] ) ) {
			$vars['cp_cookie_path'] = trailingslashit( $url_info['path'] );
		}

		// Add page.
		global $page;
		$vars['cp_multipage_page'] = ( ! empty( $page ) ) ? $page : 0;

		// Are chapters pages?
		$vars['cp_toc_chapter_is_page'] = $this->option_get( 'cp_toc_chapter_is_page' );

		// Are subpages shown?
		$vars['cp_show_subpages'] = $this->option_get( 'cp_show_subpages' );

		// Set default sidebar.
		$vars['cp_default_sidebar'] = $this->parent_obj->get_default_sidebar();

		// Set scroll speed.
		$vars['cp_js_scroll_speed'] = $this->option_get( 'cp_js_scroll_speed' );

		// Set min page width.
		$vars['cp_min_page_width'] = $this->option_get( 'cp_min_page_width' );

		// Default to showing textblock meta.
		$vars['cp_textblock_meta'] = 1;

		// Check option.
		if (
			$this->option_exists( 'cp_textblock_meta' ) AND
			$this->option_get( 'cp_textblock_meta' ) == 'n'
		) {

			// Only show textblock meta on rollover.
			$vars['cp_textblock_meta'] = 0;

		}

		// Default to page navigation enabled.
		$vars['cp_page_nav_enabled'] = 1;

		// Check option.
		if (
			$this->option_exists( 'cp_page_nav_enabled' ) AND
			$this->option_get( 'cp_page_nav_enabled' ) == 'n'
		) {

			// Disable page navigation.
			$vars['cp_page_nav_enabled'] = 0;

		}

		// Default to parsing content and comments.
		$vars['cp_do_not_parse'] = 0;

		// Check option.
		if (
			$this->option_exists( 'cp_do_not_parse' ) AND
			$this->option_get( 'cp_do_not_parse' ) == 'y'
		) {

			// Do not parse.
			$vars['cp_do_not_parse'] = 1;

		}

		// --<
		return apply_filters( 'commentpress_get_javascript_vars', $vars );

	}



	/**
	 * Sets class properties for mobile browsers.
	 *
	 * @since 3.4
	 */
	public function test_for_mobile() {

		// Init mobile flag.
		$this->is_mobile = false;

		// Init tablet flag.
		$this->is_tablet = false;

		// Init touch flag.
		$this->is_mobile_touch = false;

		// Do we have a user agent?
		if ( isset( $_SERVER["HTTP_USER_AGENT"] ) ) {

			// The old CommentPress also includes Mobile_Detect.
			if ( ! class_exists( 'Mobile_Detect' ) ) {

				// Use code from http://code.google.com/p/php-mobile-detect/
				include_once( COMMENTPRESS_PLUGIN_PATH . 'commentpress-core/assets/includes/mobile-detect/Mobile_Detect.php' );

			}

			// Init.
			$detect = new Mobile_Detect();

			// Overwrite flag if mobile.
			if ( $detect->isMobile() ) {
				$this->is_mobile = true;
			}

			// Overwrite flag if tablet.
			if ( $detect->isTablet() ) {
				$this->is_tablet = true;
			}

			// To guess at touch devices, we assume *either* phone *or* tablet..
			if ( $this->is_mobile OR $this->is_tablet ) {
				$this->is_mobile_touch = true;
			}

		}

	}



	/**
	 * Returns class properties for mobile browsers.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_mobile True if mobile device, false otherwise.
	 */
	public function is_mobile() {

		// Do we have the property?
		if ( ! isset( $this->is_mobile ) ) {

			// Get it.
			$this->test_for_mobile();

		}

		// --<
		return $this->is_mobile;

	}



	/**
	 * Returns class properties for tablet browsers.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_tablet True if tablet device, false otherwise.
	 */
	public function is_tablet() {

		// Do we have the property?
		if ( ! isset( $this->is_tablet ) ) {

			// Get it.
			$this->test_for_mobile();

		}

		// --<
		return $this->is_tablet;

	}



	/**
	 * Returns class properties for touch devices.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_touch True if touch device, false otherwise.
	 */
	public function is_touch() {

		// Do we have the property?
		if ( ! isset( $this->is_mobile_touch ) ) {

			// Get it.
			$this->test_for_mobile();

		}

		// --<
		return $this->is_mobile_touch;

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Private Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Create new post with content of existing.
	 *
	 * @since 3.4
	 *
	 * @return int $post The WordPress post object to make a copy of.
	 * @return int $new_post_id The numeric ID of the new post.
	 */
	public function _create_new_post( $post ) {

		// Define basics.
		$new_post = [
			'post_status' => 'draft',
			'post_type' => 'post',
			'comment_status' => 'open',
			'ping_status' => 'open',
			'to_ping' => '', // Quick fix for Windows.
			'pinged' => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt' => '', // Quick fix for Windows.
		];

		// Add post-specific stuff.

		// Default page title.
		$prefix = __( 'Copy of ', 'commentpress-core' );

		// Allow overrides of prefix.
		$prefix = apply_filters( 'commentpress_new_post_title_prefix', $prefix );

		// Set title, but allow overrides.
		$new_post['post_title'] = apply_filters( 'commentpress_new_post_title', $prefix . $post->post_title, $post );

		// Set excerpt, but allow overrides.
		$new_post['post_excerpt'] = apply_filters( 'commentpress_new_post_excerpt', $post->post_excerpt );

		// Set content, but allow overrides.
		$new_post['post_content'] = apply_filters( 'commentpress_new_post_content', $post->post_content );

		// Set post author, but allow overrides.
		$new_post['post_author'] = apply_filters( 'commentpress_new_post_author', $post->post_author );

		// Insert the post into the database.
		$new_post_id = wp_insert_post( $new_post );

		// --<
		return $new_post_id;

	}



	/**
	 * Create "title" page.
	 *
	 * @since 3.4
	 *
	 * @return int $title_id The numeric ID of the Title Page.
	 */
	public function _create_title_page() {

		// Get the option, if it exists.
		$page_exists = $this->option_get( 'cp_welcome_page' );

		// Don't create if we already have the option set.
		if ( $page_exists !== false AND is_numeric( $page_exists ) ) {

			// Get the page (the plugin may have been deactivated, then the page deleted).
			$welcome = get_post( $page_exists );

			// Check that the page exists.
			if ( ! is_null( $welcome ) ) {

				// Got it:

				// We still ought to set WordPress internal page references.
				$this->_store_wordpress_option( 'show_on_front', 'page' );
				$this->_store_wordpress_option( 'page_on_front', $page_exists );

				// --<
				return $page_exists;

			} else {

				// Page does not exist, continue on and create it.

			}

		}

		// Define welcome/title page.
		$title = [
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'open',
			'ping_status' => 'closed',
			'to_ping' => '', // Quick fix for Windows.
			'pinged' => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt' => '', // Quick fix for Windows.
			'menu_order' => 0,
		];

		// Add post-specific stuff.

		// Default page title.
		$default_title = __( 'Title Page', 'commentpress-core' );

		// Set, but allow overrides.
		$title['post_title'] = apply_filters( 'cp_title_page_title', $default_title );

		// Default content.
		$content = __(

		'Welcome to your new CommentPress site, which allows your readers to comment paragraph-by-paragraph or line-by-line in the margins of a text. Annotate, gloss, workshop, debate: with CommentPress you can do all of these things on a finer-grained level, turning a document into a conversation.

This is your title page. Edit it to suit your needs. It has been automatically set as your homepage but if you want another page as your homepage, set it in <em>WordPress</em> &#8594; <em>Settings</em> &#8594; <em>Reading</em>.

You can also set a number of options in <em>WordPress</em> &#8594; <em>Settings</em> &#8594; <em>CommentPress</em> to make the site work the way you want it to. Use the Theme Customizer to change the way your site looks in <em>WordPress</em> &#8594; <em>Appearance</em> &#8594; <em>Customize</em>. For help with structuring, formatting and reading text in CommentPress, please refer to the <a href="http://www.futureofthebook.org/commentpress/">CommentPress website</a>.', 'commentpress-core'

		);

		// Set, but allow overrides.
		$title['post_content'] = apply_filters( 'cp_title_page_content', $content );

		// Set template, but allow overrides.
		$title['page_template'] = apply_filters( 'cp_title_page_template', 'welcome.php' );

		// Insert the post into the database.
		$title_id = wp_insert_post( $title );

		// Make sure it has the default formatter (0 = prose)
		add_post_meta( $title_id, '_cp_post_type_override', '0' );

		// Store the option.
		$this->option_set( 'cp_welcome_page', $title_id );

		// Set WordPress internal page references.
		$this->_store_wordpress_option( 'show_on_front', 'page' );
		$this->_store_wordpress_option( 'page_on_front', $title_id );

		// --<
		return $title_id;

	}



	/**
	 * Create "General Comments" page.
	 *
	 * @since 3.4
	 *
	 * @return int $general_comments_id The numeric ID of the "General Comments" page.
	 */
	public function _create_general_comments_page() {

		// Define general comments page.
		$general_comments = [
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'open',
			'ping_status' => 'open',
			'to_ping' => '', // Quick fix for Windows.
			'pinged' => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt' => '', // Quick fix for Windows.
			'menu_order' => 0,
		];

		// Add post-specific stuff.

		// Default page title.
		$title = __( 'General Comments', 'commentpress-core' );

		// Set, but allow overrides.
		$general_comments['post_title'] = apply_filters( 'cp_general_comments_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		// Set, but allow overrides.
		$general_comments['post_content'] = apply_filters( 'cp_general_comments_content', $content );

		// Set template, but allow overrides.
		$general_comments['page_template'] = apply_filters( 'cp_general_comments_template', 'comments-general.php' );

		// Insert the post into the database.
		$general_comments_id = wp_insert_post( $general_comments );

		// Store the option.
		$this->option_set( 'cp_general_comments_page', $general_comments_id );

		// --<
		return $general_comments_id;

	}



	/**
	 * Create "all comments" page.
	 *
	 * @since 3.4
	 *
	 * @return int $all_comments_id The numeric ID of the "All Comments" page.
	 */
	public function _create_all_comments_page() {

		// Define all comments page.
		$all_comments = [
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'to_ping' => '', // Quick fix for Windows.
			'pinged' => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt' => '', // Quick fix for Windows.
			'menu_order' => 0,
		];

		// Add post-specific stuff.

		// Default page title.
		$title = __( 'All Comments', 'commentpress-core' );

		// Set, but allow overrides.
		$all_comments['post_title'] = apply_filters( 'cp_all_comments_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		// Set, but allow overrides.
		$all_comments['post_content'] = apply_filters( 'cp_all_comments_content', $content );

		// Set template, but allow overrides.
		$all_comments['page_template'] = apply_filters( 'cp_all_comments_template', 'comments-all.php' );

		// Insert the post into the database.
		$all_comments_id = wp_insert_post( $all_comments );

		// Store the option.
		$this->option_set( 'cp_all_comments_page', $all_comments_id );

		// --<
		return $all_comments_id;

	}



	/**
	 * Create "Comments by Author" page.
	 *
	 * @since 3.4
	 *
	 * @return int $group_id The numeric ID of the "Comments by Author" page.
	 */
	public function _create_comments_by_author_page() {

		// Define comments by author page.
		$group = [
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'to_ping' => '', // Quick fix for Windows.
			'pinged' => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt' => '', // Quick fix for Windows.
			'menu_order' => 0,
		];

		// Add post-specific stuff.

		// Default page title.
		$title = __( 'Comments by Commenter', 'commentpress-core' );

		// Set, but allow overrides.
		$group['post_title'] = apply_filters( 'cp_comments_by_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		// Set, but allow overrides.
		$group['post_content'] = apply_filters( 'cp_comments_by_content', $content );

		// Set template, but allow overrides.
		$group['page_template'] = apply_filters( 'cp_comments_by_template', 'comments-by.php' );

		// Insert the post into the database.
		$group_id = wp_insert_post( $group );

		// Store the option.
		$this->option_set( 'cp_comments_by_page', $group_id );

		// --<
		return $group_id;

	}



	/**
	 * Create "blog" page.
	 *
	 * @since 3.4
	 *
	 * @return int $blog_id The numeric ID of the "Blog" page.
	 */
	public function _create_blog_page() {

		// Define blog page.
		$blog = [
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'to_ping' => '', // Quick fix for Windows.
			'pinged' => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt' => '', // Quick fix for Windows.
			'menu_order' => 0,
		];

		// Add post-specific stuff.

		// Default page title
		$title = __( 'Blog', 'commentpress-core' );

		// Set, but allow overrides.
		$blog['post_title'] = apply_filters( 'cp_blog_page_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		// Set, but allow overrides.
		$blog['post_content'] = apply_filters( 'cp_blog_page_content', $content );

		// Set template, but allow overrides.
		$blog['page_template'] = apply_filters( 'cp_blog_page_template', 'blog.php' );

		// Insert the post into the database.
		$blog_id = wp_insert_post( $blog );

		// Store the option.
		$this->option_set( 'cp_blog_page', $blog_id );

		// Set WordPress internal page reference.
		$this->_store_wordpress_option( 'page_for_posts', $blog_id );

		// --<
		return $blog_id;

	}



	/**
	 * Create "Blog Archive" page.
	 *
	 * @since 3.4
	 *
	 * @return int $blog_id The numeric ID of the "Blog Archive" page.
	 */
	public function _create_blog_archive_page() {

		// Define blog archive page.
		$blog = [
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'to_ping' => '', // Quick fix for Windows.
			'pinged' => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt' => '', // Quick fix for Windows.
			'menu_order' => 0,
		];

		// Add post-specific stuff.

		// Default page title.
		$title = __( 'Blog Archive', 'commentpress-core' );

		// Set, but allow overrides.
		$blog['post_title'] = apply_filters( 'cp_blog_archive_page_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		// Set, but allow overrides.
		$blog['post_content'] = apply_filters( 'cp_blog_archive_page_content', $content );

		// Set template, but allow overrides.
		$blog['page_template'] = apply_filters( 'cp_blog_archive_page_template', 'archives.php' );

		// Insert the post into the database.
		$blog_id = wp_insert_post( $blog );

		// Store the option.
		$this->option_set( 'cp_blog_archive_page', $blog_id );

		// --<
		return $blog_id;

	}



	/**
	 * Create "table of contents" page.
	 *
	 * PLease note: this is NOT USED.
	 *
	 * @since 3.4
	 *
	 * @return int $toc_id The numeric ID of the "Table of Contents" page.
	 */
	public function _create_toc_page() {

		// Define TOC page.
		$toc = [
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'to_ping' => '', // Quick fix for Windows.
			'pinged' => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt' => '', // Quick fix for Windows.
			'menu_order' => 0,
		];

		// Default page title.
		$title = __( 'Table of Contents', 'commentpress-core' );

		// Set, but allow overrides.
		$toc['post_title'] = apply_filters( 'cp_toc_page_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		// Set, but allow overrides.
		$toc['post_content'] = apply_filters( 'cp_toc_page_content', $content );

		// Set template, but allow overrides.
		$toc['page_template'] = apply_filters( 'cp_toc_page_template', 'toc.php' );

		// Insert the post into the database.
		$toc_id = wp_insert_post( $toc );

		// Store the option.
		$this->option_set( 'cp_toc_page', $toc_id );

		// --<
		return $toc_id;

	}



	/**
	 * Cancels comment paging because CommentPress Core will not work with comment paging.
	 *
	 * @since 3.4
	 */
	public function _cancel_comment_paging() {

		// Store option.
		$this->_store_wordpress_option( 'page_comments', '' );

	}



	/**
	 * Resets comment paging option when plugin is deactivated.
	 *
	 * @since 3.4
	 */
	public function _reset_comment_paging() {

		// Reset option.
		$this->_reset_wordpress_option( 'page_comments' );

	}



	/**
	 * Clears widgets for a fresh start.
	 *
	 * @since 3.4
	 */
	public function _clear_widgets() {

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
	 * Resets widgets when plugin is deactivated.
	 *
	 * @since 3.4
	 */
	public function _reset_widgets() {

		// Reset option.
		$this->_reset_wordpress_option( 'sidebars_widgets' );

	}



	/**
	 * Store WordPress option.
	 *
	 * @since 3.4
	 *
	 * @param str $name The name of the option.
	 * @param mixed $value The value of the option.
	 */
	public function _store_wordpress_option( $name, $value ) {

		// Set backup option.
		add_option( 'commentpress_' . $name, $this->option_wp_get( $name ) );

		// Set the WordPress option.
		$this->option_wp_set( $name, $value );

	}



	/**
	 * Reset WordPress option.
	 *
	 * @since 3.4
	 *
	 * @param str $name The name of the option.
	 */
	public function _reset_wordpress_option( $name ) {

		// Set the WordPress option.
		$this->option_wp_set( $name, $this->option_wp_get( 'cp_' . $name ) );

		// Remove backup option.
		delete_option( 'commentpress_' . $name );

	}



	/**
	 * Create all basic CommentPress Core options.
	 *
	 * @since 3.4
	 */
	public function _options_create() {

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
			'cp_blog_workflow' => $this->blog_workflow,
			'cp_sidebar_default' => $this->sidebar_default,
			'cp_featured_images' => $this->featured_images,
			'cp_textblock_meta' => $this->textblock_meta,
			'cp_page_nav_enabled' => $this->page_nav_enabled,
			'cp_do_not_parse' => $this->do_not_parse,
			'cp_post_types_disabled' => $this->post_types_disabled,
		];

		// Paragraph-level comments enabled by default.
		add_option( 'commentpress_options', $this->commentpress_options );

	}



	/**
	 * Reset CommentPress Core options.
	 *
	 * @since 3.4
	 */
	public function _options_reset() {

		// TOC: show posts by default.
		$this->option_set( 'cp_show_posts_or_pages_in_toc', $this->toc_content );

		// TOC: are chapters pages.
		$this->option_set( 'cp_toc_chapter_is_page', $this->toc_chapter_is_page );

		// TOC: if pages are shown, show subpages by default.
		$this->option_set( 'cp_show_subpages', $this->show_subpages );

		// TOC: show extended post list.
		$this->option_set( 'cp_show_extended_toc', $this->show_extended_toc );

		// Comment editor.
		$this->option_set( 'cp_comment_editor', $this->comment_editor );

		// Promote reading or commenting.
		$this->option_set( 'cp_promote_reading', $this->promote_reading );

		// Show or hide titles.
		$this->option_set( 'cp_title_visibility', $this->title_visibility );

		// Show or hide page meta.
		$this->option_set( 'cp_page_meta_visibility', $this->page_meta_visibility );

		// Header background colour.
		$this->option_set( 'cp_header_bg_colour', 'deprecated' );

		// Javascript scroll speed.
		$this->option_set( 'cp_js_scroll_speed', $this->js_scroll_speed );

		// Minimum page width.
		$this->option_set( 'cp_min_page_width', $this->min_page_width );

		// "live" comment refeshing.
		$this->option_set( 'cp_para_comments_live', $this->para_comments_live );

		// Blog: excerpt length.
		$this->option_set( 'cp_excerpt_length', $this->excerpt_length );

		// Workflow.
		$this->option_set( 'cp_blog_workflow', $this->blog_workflow );

		// Blog type.
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

		// Skipped post types.
		$this->option_set( 'cp_post_types_disabled', $this->post_types_disabled );

		// Store it.
		$this->options_save();

	}



	/**
	 * Migrate all CommentPress Core options from old plugin.
	 *
	 * @since 3.4
	 */
	public function _options_migrate() {

		// Get existing options.
		$old = get_option( 'cp_options', [] );

		// ---------------------------------------------------------------------
		// Retrieve new ones, if they exist, or use defaults otherwise.
		// ---------------------------------------------------------------------
		$this->toc_content = 			isset( $old['cp_show_posts_or_pages_in_toc'] ) ?
										$old['cp_show_posts_or_pages_in_toc'] :
										$this->toc_content;

		$this->toc_chapter_is_page = 	isset( $old['cp_toc_chapter_is_page'] ) ?
										$old['cp_toc_chapter_is_page'] :
										$this->toc_chapter_is_page;

		$this->show_subpages =		 	isset( $old['cp_show_subpages'] ) ?
										$old['cp_show_subpages'] :
										$this->show_subpages;

		$this->show_extended_toc = 		isset( $old['cp_show_extended_toc'] ) ?
										$old['cp_show_extended_toc'] :
										$this->show_extended_toc;

		$this->title_visibility =	 	isset( $old['cp_title_visibility'] ) ?
										$old['cp_title_visibility'] :
										$this->title_visibility;

		$this->page_meta_visibility = 	isset( $old['cp_page_meta_visibility'] ) ?
										$old['cp_page_meta_visibility'] :
										$this->page_meta_visibility;

		// Header background colour
		$header_bg_colour =	 			isset( $old['cp_header_bg_colour'] ) ?
										$old['cp_header_bg_colour'] :
										$this->header_bg_colour;

		// If it's the old default, upgrade to new default.
		if ( $header_bg_colour == '819565' ) {
			$header_bg_colour = $this->header_bg_colour;
		}

		$this->js_scroll_speed =	 	isset( $old['cp_js_scroll_speed'] ) ?
										$old['cp_js_scroll_speed'] :
										$this->js_scroll_speed;

		$this->min_page_width =		 	isset( $old['cp_min_page_width'] ) ?
										$old['cp_min_page_width'] :
										$this->min_page_width;

		$this->comment_editor =		 	isset( $old['cp_comment_editor'] ) ?
										$old['cp_comment_editor'] :
										$this->comment_editor;

		$this->promote_reading =	 	isset( $old['cp_promote_reading'] ) ?
										$old['cp_promote_reading'] :
										$this->promote_reading;

		$this->excerpt_length =		 	isset( $old['cp_excerpt_length'] ) ?
										$old['cp_excerpt_length'] :
										$this->excerpt_length;

		$this->para_comments_live = 	isset( $old['cp_para_comments_live'] ) ?
										$old['cp_para_comments_live'] :
										$this->para_comments_live;

		$blog_type = 					isset( $old['cp_blog_type'] ) ?
										$old['cp_blog_type'] :
										$this->blog_type;

		$blog_workflow =		 		isset( $old['cp_blog_workflow'] ) ?
										$old['cp_blog_workflow'] :
										$this->blog_workflow;

		$this->sidebar_default =	 	isset( $old['cp_sidebar_default'] ) ?
										$old['cp_sidebar_default'] :
										$this->sidebar_default;

		$this->featured_images =	 	isset( $old['cp_featured_images'] ) ?
										$old['cp_featured_images'] :
										$this->featured_images;

		$this->textblock_meta =		 	isset( $old['cp_textblock_meta'] ) ?
										$old['cp_textblock_meta'] :
										$this->textblock_meta;

		$this->page_nav_enabled =		 	isset( $old['cp_page_nav_enabled'] ) ?
										$old['cp_page_nav_enabled'] :
										$this->page_nav_enabled;

		$this->do_not_parse =		 	isset( $old['cp_do_not_parse'] ) ?
										$old['cp_do_not_parse'] :
										$this->do_not_parse;

		$this->post_types_disabled = 	isset( $old['cp_post_types_disabled'] ) ?
										$old['cp_post_types_disabled'] :
										$this->post_types_disabled;

		// ---------------------------------------------------------------------
		// Special pages.
		// ---------------------------------------------------------------------
		$special_pages =	 		isset( $old['cp_special_pages'] ) ?
									$old['cp_special_pages'] :
									null;

		$blog_page =		 		isset( $old['cp_blog_page'] ) ?
									$old['cp_blog_page'] :
									null;

		$blog_archive_page = 		isset( $old['cp_blog_archive_page'] ) ?
									$old['cp_blog_archive_page'] :
									null;

		$general_comments_page =	isset( $old['cp_general_comments_page'] ) ?
									$old['cp_general_comments_page'] :
									null;

		$all_comments_page = 		isset( $old['cp_all_comments_page'] ) ?
									$old['cp_all_comments_page'] :
									null;

		$comments_by_page = 		isset( $old['cp_comments_by_page'] ) ?
									$old['cp_comments_by_page'] :
									null;

		$toc_page =		 			isset( $old['cp_toc_page'] ) ?
									$old['cp_toc_page'] :
									null;

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
			'cp_blog_type' => $blog_type,
			'cp_blog_workflow' => $blog_workflow,
			'cp_sidebar_default' => $this->sidebar_default,
			'cp_featured_images' => $this->featured_images,
			'cp_textblock_meta' => $this->textblock_meta,
			'cp_page_nav_enabled' => $this->page_nav_enabled,
			'cp_do_not_parse' => $this->do_not_parse,
			'cp_post_types_disabled' => $this->post_types_disabled,
		];

		// If we have special pages.
		if ( ! is_null( $special_pages ) AND is_array( $special_pages ) ) {

			// Let's have them as well.
			$pages = [
				'cp_special_pages' => $special_pages,
				'cp_blog_page' => $blog_page,
				'cp_blog_archive_page' => $blog_archive_page,
				'cp_general_comments_page' => $general_comments_page,
				'cp_all_comments_page' => $all_comments_page,
				'cp_comments_by_page' => $comments_by_page,
				'cp_toc_page' => $toc_page,
			];

			// Merge.
			$this->commentpress_options = array_merge( $this->commentpress_options, $pages );

			// Access old plugin.
			global $commentpress_obj;

			// Did we get it?
			if ( is_object( $commentpress_obj ) ) {

				// Now delete the old CommentPress options.
				$commentpress_obj->db->option_delete( 'cp_special_pages' );
				$commentpress_obj->db->option_delete( 'cp_blog_page' );
				$commentpress_obj->db->option_delete( 'cp_blog_archive_page' );
				$commentpress_obj->db->option_delete( 'cp_general_comments_page' );
				$commentpress_obj->db->option_delete( 'cp_all_comments_page' );
				$commentpress_obj->db->option_delete( 'cp_comments_by_page' );
				$commentpress_obj->db->option_delete( 'cp_toc_page' );

				// Save changes.
				$commentpress_obj->db->options_save();

				// Extra page options: get existing backup, delete it, create new backup.

				// Backup blog page reference.
				$page_for_posts = get_option( 'cp_page_for_posts' );
				delete_option( 'cp_page_for_posts' );
				add_option( 'commentpress_page_for_posts', $page_for_posts );

			}

		}

		// ---------------------------------------------------------------------
		// Welcome page.
		// ---------------------------------------------------------------------

		// Welcome page is a bit of an oddity: deal with it here.
		$welcome_page =	isset( $old['cp_welcome_page'] ) ? $old['cp_welcome_page'] : null;

		// Did we get a welcome page?
		if ( ! is_null( $welcome_page ) ) {

			// If the custom field already has a value.
			if ( get_post_meta( $welcome_page, '_cp_post_type_override', true ) !== '' ) {

				// Leave the selected formatter alone.

			} else {

				// Make sure it has the default formatter (0 = prose).
				add_post_meta( $welcome_page, '_cp_post_type_override', '0' );

			}

			// Add it to our options.
			$this->commentpress_options['cp_welcome_page'] = $welcome_page;

		}

		// Add the options to WordPress.
		add_option( 'commentpress_options', $this->commentpress_options );

		// ---------------------------------------------------------------------
		// Backups.
		// ---------------------------------------------------------------------

		// Backup what to show as homepage.
		$show_on_front = get_option( 'cp_show_on_front' );
		delete_option( 'cp_show_on_front' );
		add_option( 'commentpress_show_on_front', $show_on_front );

		// Backup homepage id.
		$page_on_front = get_option( 'cp_page_on_front' );
		delete_option( 'cp_page_on_front' );
		add_option( 'commentpress_page_on_front', $page_on_front );

		// Backup comment paging.
		$page_comments = get_option( 'cp_page_comments' );
		delete_option( 'cp_page_comments' );
		add_option( 'commentpress_page_comments', $page_comments );

		// ---------------------------------------------------------------------
		// Theme Customizations.
		// ---------------------------------------------------------------------

		// Migrate Theme Customizations.
		$theme_settings = get_option( 'cp_theme_settings', [] );

		// Did we get any?
		if ( ! empty( $theme_settings ) ) {

			// Migrate them.
			add_option( 'commentpress_theme_settings', $theme_settings );

		}

		// Migrate Theme Mods.
		$theme_mods = get_option( 'theme_mods_commentpress', [] );

		// Did we get any?
		if ( is_array( $theme_mods ) AND count( $theme_mods ) > 0 ) {

			// Get header background image.
			if ( isset( $theme_mods['header_image'] ) ) {

				// Is it a CommentPress one?
				if ( strstr( $theme_mods['header_image'], 'style/images/header/caves.jpg' ) !== false ) {

					// Point it at the equivalent new version.
					$theme_mods['header_image'] = COMMENTPRESS_PLUGIN_URL .
												  'themes/commentpress-theme' .
												  '/assets/images/header/caves-green.jpg';

				}

			}

			/*
			// If we wanted to clear widgets.
			if ( isset( $theme_mods['sidebars_widgets'] ) ) {

				// Remove them.
				unset( $theme_mods['sidebars_widgets'] );

			}

			// Override widgets.
			$this->_clear_widgets();
			*/

			// Get current theme.
			$theme = wp_get_theme();

			// Get current theme slug.
			$theme_slug = $theme->get_stylesheet();

			// Update theme mods (will create if it doesn't exist).
			update_option( 'theme_mods_' . $theme_slug, $theme_mods );

		}

		// Update header background colour.
		set_theme_mod( 'commentpress_header_bg_color', '#' . $header_bg_colour );

		// ---------------------------------------------------------------------
		// Deactivate old CommentPress and CommentPress Ajaxified.
		// ---------------------------------------------------------------------

		// Get old CommentPress Ajaxified.
		$cpajax_old = commentpress_find_plugin_by_name( 'Commentpress Ajaxified' );
		if ( $cpajax_old AND is_plugin_active( $cpajax_old ) ) { deactivate_plugins( $cpajax_old ); }

		// Get old CommentPress.
		$cp_old = commentpress_find_plugin_by_name( 'Commentpress' );
		if ( $cp_old AND is_plugin_active( $cp_old ) ) { deactivate_plugins( $cp_old ); }

	}



	/**
	 * Upgrade CommentPress options to array (only for pre-CommentPress 3.2 upgrades).
	 *
	 * @since 3.4
	 */
	public function _options_upgrade() {

		// Populate options array with current values.
		$this->commentpress_options = [

			// Theme settings we want to keep.
			'cp_show_posts_or_pages_in_toc' => $this->option_wp_get( 'cp_show_posts_or_pages_in_toc' ),
			'cp_toc_chapter_is_page' => $this->option_wp_get( 'cp_toc_chapter_is_page'),
			'cp_show_subpages' => $this->option_wp_get( 'cp_show_subpages'),
			'cp_excerpt_length' => $this->option_wp_get( 'cp_excerpt_length'),

			// Migrate special pages.
			'cp_special_pages' => $this->option_wp_get( 'cp_special_pages'),
			'cp_welcome_page' => $this->option_wp_get( 'cp_welcome_page'),
			'cp_general_comments_page' => $this->option_wp_get( 'cp_general_comments_page'),
			'cp_all_comments_page' => $this->option_wp_get( 'cp_all_comments_page'),
			'cp_comments_by_page' => $this->option_wp_get( 'cp_comments_by_page'),
			'cp_blog_page' => $this->option_wp_get( 'cp_blog_page'),

			// Store setting for what was independently set by the ajax commenting plugin, "off" by default.
			'cp_para_comments_live' => $this->para_comments_live,

		];

		// Save options array.
		$this->options_save();

		// Delete all old options.
		$this->_options_delete_legacy();

	}



	/**
	 * Delete all legacy CommentPress options.
	 *
	 * @since 3.4
	 */
	public function _options_delete_legacy() {

		// Delete paragraph-level commenting option.
		delete_option( 'cp_para_comments_enabled' );

		// Delete TOC options.
		delete_option( 'cp_show_posts_or_pages_in_toc' );
		delete_option( 'cp_show_subpages' );
		delete_option( 'cp_toc_chapter_is_page' );

		// Delete comment editor.
		delete_option( 'cp_comment_editor' );

		// Promote reading or commenting.
		delete_option( 'cp_promote_reading' );

		// Show or hide titles.
		delete_option( 'cp_title_visibility' );

		// Header bg colour.
		delete_option( 'cp_header_bg_colour' );

		// Header bg colour.
		delete_option( 'cp_js_scroll_speed' );

		// Header bg colour.
		delete_option( 'cp_min_page_width' );

		// Delete skin.
		delete_option( 'cp_default_skin' );

		// Window appearance options.
		delete_option( 'cp_default_left_position' );
		delete_option( 'cp_default_top_position' );
		delete_option( 'cp_default_width' );
		delete_option( 'cp_default_height' );

		// Window behaviour options.
		delete_option( 'cp_allow_users_to_iconize' );
		delete_option( 'cp_allow_users_to_minimize' );
		delete_option( 'cp_allow_users_to_resize' );
		delete_option( 'cp_allow_users_to_drag' );
		delete_option( 'cp_allow_users_to_save_position' );

		// Blog options.
		delete_option( 'cp_excerpt_length' );

		// "live" comment refreshing.
		delete_option( 'cp_para_comments_live' );

		// Special pages options.
		delete_option( 'cp_special_pages' );
		delete_option( 'cp_welcome_page' );
		delete_option( 'cp_general_comments_page' );
		delete_option( 'cp_all_comments_page' );
		delete_option( 'cp_comments_by_page' );
		delete_option( 'cp_blog_page' );

	}



//##############################################################################



} // Class ends.



