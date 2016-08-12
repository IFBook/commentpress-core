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
	 * @var object $parent_obj The plugin object
	 */
	public $parent_obj;

	/**
	 * Plugin options array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $commentpress_options The plugin options array
	 */
	public $commentpress_options = array();

	/**
	 * Table of Contents content flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $toc_content The TOC content (either 'post' or 'page')
	 */
	public $toc_content = 'page';

	/**
	 * Table of Contents "chapters are pages" flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $toc_chapter_is_page The Table of Contents "chapters are pages" flag
	 */
	public $toc_chapter_is_page = 1;

	/**
	 * Extended Table of Contents content for posts lists flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $show_extended_toc The extended TOC content for posts lists flag
	 */
	public $show_extended_toc = 1;

	/**
	 * Table of Contents show subpages flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $show_subpages The Table of Contents shows subpages by default
	 */
	public $show_subpages = 1;

	/**
	 * Page title visibility flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $title_visibility Show page titles by default
	 */
	public $title_visibility = 'show';

	/**
	 * Page meta visibility flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $page_meta_visibility Hide page meta by default
	 */
	public $page_meta_visibility = 'hide';

	/**
	 * Default editor flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $comment_editor Default to rich text editor (TinyMCE)
	 */
	public $comment_editor = 1;

	/**
	 * Promote reading flag.
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $promote_reading Either promote reading (1) or commenting (0)
	 */
	public $promote_reading = 0;

	/**
	 * Excerpt length.
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $excerpt_length The default excerpt length
	 */
	public $excerpt_length = 55;

	/**
	 * Default header background colour (hex, same as in theme stylesheet).
	 *
	 * @since 3.0
	 * @access public
	 * @var bool $header_bg_colour The default header background colour
	 */
	public $header_bg_colour = '2c2622';

	/**
	 * Default scroll speed.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $js_scroll_speed The scroll speed (in millisecs)
	 */
	public $js_scroll_speed = '800';

	/**
	 * Default type of blog.
	 *
	 * Blog types are built as an array - eg, array('0' => 'Poetry','1' => 'Prose')
	 *
	 * @since 3.3
	 * @access public
	 * @var bool|int $blog_type The default type of blog
	 */
	public $blog_type = false;

	/**
	 * Default blog workflow.
	 *
	 * Like "translation", for example, off by default
	 *
	 * @since 3.3
	 * @access public
	 * @var bool $blog_workflow True if blog workflow enabled
	 */
	public $blog_workflow = 0;

	/**
	 * Default sidebar tab.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $sidebar_default The default sidebar tab ('toc' == Contents tab)
	 */
	public $sidebar_default = 'toc';

	/**
	 * Default minimum page width (px).
	 *
	 * @since 3.0
	 * @access public
	 * @var str $min_page_width The default minimum page width in pixels
	 */
	public $min_page_width = '447';

	/**
	 * "Live" comment refreshing.
	 *
	 * @since 3.0
	 * @access public
	 * @var str $para_comments_live The "live" comment refreshing setting (off by default)
	 */
	public $para_comments_live = 0;

	/**
	 * Prevent save_post hook firing more than once.
	 *
	 * @since 3.3
	 * @access public
	 * @var str $saved_post True if post already saved
	 */
	public $saved_post = false;

	/**
	 * Featured images flag.
	 *
	 * @since 3.5
	 * @access public
	 * @var str $featured_images The featured images flag ('y' or 'n')
	 */
	public $featured_images = 'n';

	/**
	 * Textblock meta flag.
	 *
	 * @since 3.5
	 * @access public
	 * @var str $textblock_meta The textblock meta flag ('y' or 'n')
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
	 * @var str $page_nav_enabled The page navigation flag ('y' or 'n')
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
	 * @var str $do_not_parse The flag indicating if content is to parsed ('y' or 'n')
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
	 * @var str $post_types_disabled The post types not to be parsed
	 */
	public $post_types_disabled = array();



	/**
	 * Initialises this object.
	 *
	 * @since 3.0
	 *
	 * @param object $parent_obj A reference to the parent object
	 */
	function __construct( $parent_obj ) {

		// store reference to parent
		$this->parent_obj = $parent_obj;

		// init
		$this->initialise();

	}



	/**
	 * Object initialisation.
	 *
	 * @return void
	 */
	function initialise() {

		// load options array
		$this->commentpress_options = $this->option_wp_get( 'commentpress_options', $this->commentpress_options );

		// do immediate upgrades after the theme has loaded
		add_action( 'after_setup_theme', array( $this, 'upgrade_immediately' ) );

	}



	/**
	 * Set up all items associated with this object.
	 *
	 * @return void
	 */
	public function activate() {

		// have we already got a modified database?
		$modified = $this->db_is_modified( 'comment_text_signature' ) ? 'y' : 'n';

		// if  we have an existing comment_text_signature column
		if ( $modified == 'y' ) {

			// upgrade old CommentPress schema to new
			if ( ! $this->schema_upgrade() ) {

				// kill plugin activation
				_cpdie( 'CommentPress Core Error: could not upgrade the database' );

			}

		} else {

			// update db schema
			$this->schema_update();

		}

		// test if we have our version
		if ( ! $this->option_wp_get( 'commentpress_version' ) ) {

			// store CommentPress Core version
			$this->option_wp_set( 'commentpress_version', COMMENTPRESS_VERSION );

		}

		// test that we aren't reactivating
		if ( ! $this->option_wp_get( 'commentpress_options' ) ) {

			// test if we have a existing pre-3.4 CommentPress instance
			if ( commentpress_is_legacy_plugin_active() ) {

				// yes: add options with existing values
				$this->_options_migrate();

			} else {

				// no: add options with default values
				$this->_options_create();

			}

		}

		// retrieve data on special pages
		$special_pages = $this->option_get( 'cp_special_pages', array() );

		// if we haven't created any
		if ( count( $special_pages ) == 0 ) {

			// create special pages
			$this->create_special_pages();

		}

		// turn comment paging option off
		$this->_cancel_comment_paging();

		// override widgets
		$this->_clear_widgets();

	}



	/**
	 * Reset WordPress to prior state, but retain options.
	 *
	 * @return void
	 */
	public function deactivate() {

		// reset comment paging option
		$this->_reset_comment_paging();

		// restore widgets
		$this->_reset_widgets();

		// always remove special pages
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
	 * @return bool $result True if successful, false otherwise
	 */
	public function schema_update() {

		// database object
		global $wpdb;

		// include WordPress upgrade script
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		// add the column, if not already there
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
	 * @return bool $result True if successful, false otherwise
	 */
	public function schema_upgrade() {

		// database object
		global $wpdb;

		// init
		$return = false;

		// construct query
		$query = "ALTER TABLE `$wpdb->comments` CHANGE `comment_text_signature` `comment_signature` VARCHAR(255) NULL;";

		// do the query to rename the column
		$wpdb->query( $query );

		// test if we now have the correct column name
		if ( $this->db_is_modified( 'comment_signature' ) ) {

			// yes
			$result = true;

		}

		// --<
		return $result;
	}



	/**
	 * Do we have a column in the comments table?
	 *
	 * @return bool $result True if modified, false otherwise
	 */
	public function db_is_modified( $column_name ) {

		// database object
		global $wpdb;

		// init
		$result = false;

		// define query
		$query = "DESCRIBE $wpdb->comments";

		// get columns
		$cols = $wpdb->get_results( $query );

		// loop
		foreach( $cols AS $col ) {

			// is it our desired column?
			if ( $col->Field == $column_name ) {

				// we got it
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
	 * @return bool $result True if outdated, false otherwise
	 */
	public function version_outdated() {

		// get installed version cast as string
		$version = (string) $this->option_wp_get( 'commentpress_version' );

		// override if we have a CommentPress Core install and it's lower than this one
		if ( $version !== false AND version_compare( COMMENTPRESS_VERSION, $version, '>' ) ) {
			return true;
		}

		// fallback
		return false;

	}



	/**
	 * Check for plugin upgrade.
	 *
	 * @return bool $result True if required, false otherwise
	 */
	public function upgrade_required() {

		// bail if we do not have an outdated version
		if ( ! $this->version_outdated() ) return false;

		// override if any options need to be shown
		if ( $this->upgrade_options_check() ) {
			return true;
		}

		// fallback
		return false;

	}



	/**
	 * Check for options added in this plugin upgrade.
	 *
	 * @return bool $result True if upgrade needed, false otherwise
	 */
	public function upgrade_options_check() {

		// do we have the option to choose which post types are supported (new in 3.9)?
		if ( ! $this->option_exists( 'cp_post_types_disabled' ) ) return true;

		// do we have the option to choose not to parse content (new in 3.8.10)?
		if ( ! $this->option_exists( 'cp_do_not_parse' ) ) return true;

		// do we have the option to choose to disable page navigation (new in 3.8.10)?
		if ( ! $this->option_exists( 'cp_page_nav_enabled' ) ) return true;

		// do we have the option to choose to hide textblock meta (new in 3.5.9)?
		if ( ! $this->option_exists( 'cp_textblock_meta' ) ) return true;

		// do we have the option to choose featured images (new in 3.5.4)?
		if ( ! $this->option_exists( 'cp_featured_images' ) ) return true;

		// do we have the option to choose the default sidebar (new in 3.3.3)?
		if ( ! $this->option_exists( 'cp_sidebar_default' ) ) return true;

		// do we have the option to show or hide page meta (new in 3.3.2)?
		if ( ! $this->option_exists( 'cp_page_meta_visibility' ) ) return true;

		// do we have the option to choose blog type (new in 3.3.1)?
		if ( ! $this->option_exists( 'cp_blog_type' ) ) return true;

		// do we have the option to choose blog workflow (new in 3.3.1)?
		if ( ! $this->option_exists( 'cp_blog_workflow' ) ) return true;

		// do we have the option to choose the TOC layout (new in 3.3)?
		if ( ! $this->option_exists( 'cp_show_extended_toc' ) ) return true;

		// do we have the option to set the comment editor?
		if ( ! $this->option_exists( 'cp_comment_editor' ) ) return true;

		// do we have the option to set the default behaviour?
		if ( ! $this->option_exists( 'cp_promote_reading' ) ) return true;

		// do we have the option to show or hide titles?
		if ( ! $this->option_exists( 'cp_title_visibility' ) ) return true;

		// do we have the option to set the header bg colour?
		if ( ! $this->option_exists( 'cp_header_bg_colour' ) ) return true;

		// do we have the option to set the scroll speed?
		if ( ! $this->option_exists( 'cp_js_scroll_speed' ) ) return true;

		// do we have the option to set the minimum page width?
		if ( ! $this->option_exists( 'cp_min_page_width' ) ) return true;

		// --<
		return false;

	}



	/**
	 * Upgrade CommentPress plugin from 3.1 options to CommentPress Core set.
	 *
	 * @return boolean $result
	 */
	public function upgrade_options() {

		// init return
		$result = false;

		// if we have a CommentPress install (or we're forcing)
		if ( $this->upgrade_required() ) {

			// are we missing the commentpress_options option?
			if ( ! $this->option_wp_exists( 'commentpress_options' ) ) {

				// upgrade to the single array
				$this->_options_upgrade();

			}

			// checkboxes send no value if not checked, so use a default
			$cp_blog_workflow = $this->blog_workflow;

			// we don't receive disabled post types in $_POST, so let's default
			// to all post types being enabled
			$cp_post_types_enabled = $this->get_supported_post_types();

			// default blog type
			$cp_blog_type = $this->blog_type;

			// get variables
			extract( $_POST );

			// New in CommentPress Core 3.9 - post types can be excluded
			if ( ! $this->option_exists( 'cp_post_types_disabled' ) ) {

				// get selected post types
				$enabled_types = array_map( 'esc_sql', $cp_post_types_enabled );

				// exclude the selected post types
				$disabled_types = array_diff( $this->get_supported_post_types(), $enabled_types );

				// add option
				$this->option_set( 'cp_post_types_disabled', $disabled_types );

			}

			// New in CommentPress Core 3.8.10 - parsing can be prevented
			if ( ! $this->option_exists( 'cp_do_not_parse' ) ) {

				// get choice
				$choice = esc_sql( $cp_do_not_parse );

				// add chosen parsing option
				$this->option_set( 'cp_do_not_parse', $choice );

			}

			// New in CommentPress Core 3.8.10 - page navigation can be disabled
			if ( ! $this->option_exists( 'cp_page_nav_enabled' ) ) {

				// get choice
				$choice = esc_sql( $cp_page_nav_enabled );

				// add chosen page navigation option
				$this->option_set( 'cp_page_nav_enabled', $choice );

			}

			// New in CommentPress Core 3.5.9 - textblock meta can be hidden
			if ( ! $this->option_exists( 'cp_textblock_meta' ) ) {

				// get choice
				$choice = esc_sql( $cp_textblock_meta );

				// add chosen textblock meta option
				$this->option_set( 'cp_textblock_meta', $choice );

			}

			// New in CommentPress Core 3.5.4 - featured image capabilities
			if ( ! $this->option_exists( 'cp_featured_images' ) ) {

				// get choice
				$choice = esc_sql( $cp_featured_images );

				// add chosen featured images option
				$this->option_set( 'cp_featured_images', $choice );

			}

			// Removed in CommentPress Core 3.4 - do we still have the legacy cp_para_comments_enabled option?
			if ( $this->option_exists( 'cp_para_comments_enabled' ) ) {

				// delete old cp_para_comments_enabled option
				$this->option_delete( 'cp_para_comments_enabled' );

			}

			// Removed in CommentPress Core 3.4 - do we still have the legacy cp_minimise_sidebar option?
			if ( $this->option_exists( 'cp_minimise_sidebar' ) ) {

				// delete old cp_minimise_sidebar option
				$this->option_delete( 'cp_minimise_sidebar' );

			}

			// New in CommentPress Core 3.4 - has AJAX "live" comment refreshing been migrated?
			if ( ! $this->option_exists( 'cp_para_comments_live' ) ) {

				// "live" comment refreshing, off by default
				$this->option_set( 'cp_para_comments_live', $this->para_comments_live );

			}

			// New in CommentPress 3.3.3 - changed the way the welcome page works
			if ( $this->option_exists( 'cp_special_pages' ) ) {

				// do we have the cp_welcome_page option?
				if ( $this->option_exists( 'cp_welcome_page' ) ) {

					// get it
					$page_id = $this->option_get( 'cp_welcome_page' );

					// retrieve data on special pages
					$special_pages = $this->option_get( 'cp_special_pages', array() );

					// is it in our special pages array?
					if ( in_array( $page_id, $special_pages ) ) {

						// remove page id from array
						$special_pages = array_diff( $special_pages, array( $page_id ) );

						// reset option
						$this->option_set( 'cp_special_pages', $special_pages );

					}

				}

			}

			// New in CommentPress 3.3.3 - are we missing the cp_sidebar_default option?
			if ( ! $this->option_exists( 'cp_sidebar_default' ) ) {

				// does the current theme need this option?
				if ( ! apply_filters( 'commentpress_hide_sidebar_option', false ) ) {

					// yes, get choice
					$choice = esc_sql( $cp_sidebar_default );

					// add chosen cp_sidebar_default option
					$this->option_set( 'cp_sidebar_default', $choice );

				} else {

					// add default cp_sidebar_default option
					$this->option_set( 'cp_sidebar_default', $this->sidebar_default );

				}

			}

			// New in CommentPress 3.3.2 - are we missing the cp_page_meta_visibility option?
			if ( ! $this->option_exists( 'cp_page_meta_visibility' ) ) {

				// get choice
				$choice = esc_sql( $cp_page_meta_visibility );

				// add chosen cp_page_meta_visibility option
				$this->option_set( 'cp_page_meta_visibility', $choice );

			}

			// New in CommentPress 3.3.1 - are we missing the cp_blog_workflow option?
			if ( ! $this->option_exists( 'cp_blog_workflow' ) ) {

				// get choice
				$choice = esc_sql( $cp_blog_workflow );

				// add chosen cp_blog_workflow option
				$this->option_set( 'cp_blog_workflow', $choice );

			}

			// New in CommentPress 3.3.1 - are we missing the cp_blog_type option?
			if ( ! $this->option_exists( 'cp_blog_type' ) ) {

				// get choice
				$choice = esc_sql( $cp_blog_type );

				// add chosen cp_blog_type option
				$this->option_set( 'cp_blog_type', $choice );

			}

			// New in CommentPress 3.3 - are we missing the cp_show_extended_toc option?
			if ( ! $this->option_exists( 'cp_show_extended_toc' ) ) {

				// get choice
				$choice = esc_sql( $cp_show_extended_toc );

				// add chosen cp_show_extended_toc option
				$this->option_set( 'cp_show_extended_toc', $choice );

			}

			// are we missing the cp_comment_editor option?
			if ( ! $this->option_exists( 'cp_comment_editor' ) ) {

				// get choice
				$choice = esc_sql( $cp_comment_editor );

				// add chosen cp_comment_editor option
				$this->option_set( 'cp_comment_editor', $choice );

			}

			// are we missing the cp_promote_reading option?
			if ( ! $this->option_exists( 'cp_promote_reading' ) ) {

				// get choice
				$choice = esc_sql( $cp_promote_reading );

				// add chosen cp_promote_reading option
				$this->option_set( 'cp_promote_reading', $choice );

			}

			// are we missing the cp_title_visibility option?
			if ( ! $this->option_exists( 'cp_title_visibility' ) ) {

				// get choice
				$choice = esc_sql( $cp_title_visibility );

				// add chosen cp_title_visibility option
				$this->option_set( 'cp_title_visibility', $choice );

			}

			// are we missing the cp_header_bg_colour option?
			if ( ! $this->option_exists( 'cp_header_bg_colour' ) ) {

				// get choice
				$choice = esc_sql( $cp_header_bg_colour );

				// strip our hex # char
				if ( stristr( $choice, '#' ) ) {
					$choice = substr( $choice, 1 );
				}

				// reset to default if blank
				if ( $choice == '' ) {
					$choice = $this->header_bg_colour;
				}

				// add chosen cp_header_bg_colour option
				$this->option_set( 'cp_header_bg_colour', $choice );

			}

			// are we missing the cp_js_scroll_speed option?
			if ( ! $this->option_exists( 'cp_js_scroll_speed' ) ) {

				// get choice
				$choice = esc_sql( $cp_js_scroll_speed );

				// add chosen cp_js_scroll_speed option
				$this->option_set( 'cp_js_scroll_speed', $choice );

			}

			// are we missing the cp_min_page_width option?
			if ( ! $this->option_exists( 'cp_min_page_width' ) ) {

				// get choice
				$choice = esc_sql( $cp_min_page_width );

				// add chosen cp_min_page_width option
				$this->option_set( 'cp_min_page_width', $choice );

			}

			// do we still have the legacy cp_allow_users_to_minimize option?
			if ( $this->option_exists( 'cp_allow_users_to_minimize' ) ) {

				// delete old cp_allow_users_to_minimize option
				$this->option_delete( 'cp_allow_users_to_minimize' );

			}

			// do we have special pages?
			if ( $this->option_exists( 'cp_special_pages' ) ) {

				// if we don't have the toc page
				if ( ! $this->option_exists( 'cp_toc_page' ) ) {

					// get special pages array
					$special_pages = $this->option_get( 'cp_special_pages', array() );

					// create TOC page -> a convenience, let's us define a logo as attachment
					$special_pages[] = $this->_create_toc_page();

					// store the array of page IDs that were created
					$this->option_set( 'cp_special_pages', $special_pages );

				}

			}

			// save new CommentPress Core options
			$this->options_save();

			// store new CommentPress Core version
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
	 * @return void
	 */
	public function upgrade_immediately() {

		// bail if we do not have an outdated version
		if ( ! $this->version_outdated() ) return;

		// maybe upgrade theme mods
		$this->upgrade_theme_mods();

	}



	/**
	 * Check for theme mods added in this plugin upgrade.
	 *
	 * @return bool $result True if upgraded, false otherwise
	 */
	public function upgrade_theme_mods() {

		// bail if option is already deprecated
		if ( 'deprecated' == $this->option_get( 'cp_header_bg_colour' ) ) return;

		// get header background colour set via customizer (new in 3.8.5)
		$colour = get_theme_mod( 'commentpress_header_bg_color', false );

		// if we have no existing one
		if ( $colour === false ) {

			// set to default
			$colour = $this->header_bg_colour;

			// check for existing option
			if ( $this->option_exists( 'cp_header_bg_colour' ) ) {

				// get current value
				$value = $this->option_get( 'cp_header_bg_colour' );

				// override colour if not yet deprecated
				if ( $value !== 'deprecated' ) {
					$colour = $value;
				}

			}

			// apply theme mod setting
			set_theme_mod( 'commentpress_header_bg_color', '#' . $colour );

			// set option to deprecated
			$this->option_set( 'cp_header_bg_colour', 'deprecated' );
			$this->options_save();

		}

	}



	/**
	 * Save the settings set by the administrator.
	 *
	 * @return bool $result True if successful, false otherwise
	 */
	public function options_update() {

		// init result
		$result = false;

	 	// was the form submitted?
		if( isset( $_POST['commentpress_submit'] ) ) {

			// check that we trust the source of the data
			check_admin_referer( 'commentpress_admin_action', 'commentpress_nonce' );

			// init vars
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

			// assume all post types are enabled
			$cp_post_types_enabled = $this->get_supported_post_types();

			// get variables
			extract( $_POST );

			// hand off to Multisite first, in case we're deactivating
			do_action( 'cpmu_deactivate_commentpress' );

			// is Multisite activating CommentPress Core?
			if ( $cp_activate == '1' ) return true;

			// did we ask to upgrade CommentPress Core?
			if ( $cp_upgrade == '1' ) {

				// do upgrade
				$this->upgrade_options();

				// --<
				return true;

			}

			// did we ask to reset?
			if ( $cp_reset == '1' ) {

				// reset theme options
				$this->_options_reset();

				// --<
				return true;

			}

			// did we ask to auto-create special pages?
			if ( $cp_create_pages == '1' ) {

				// remove any existing special pages
				$this->delete_special_pages();

				// create special pages
				$this->create_special_pages();

			}

			// did we ask to delete special pages?
			if ( $cp_delete_pages == '1' ) {

				// remove special pages
				$this->delete_special_pages();

			}

			// let's deal with our params now

			// individual special pages
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

			// TOC content
			$cp_show_posts_or_pages_in_toc = esc_sql( $cp_show_posts_or_pages_in_toc );
			$this->option_set( 'cp_show_posts_or_pages_in_toc', $cp_show_posts_or_pages_in_toc );

			// if we have pages in TOC and a value for the next param
			if ( $cp_show_posts_or_pages_in_toc == 'page' AND isset( $cp_toc_chapter_is_page ) ) {

				$cp_toc_chapter_is_page = esc_sql( $cp_toc_chapter_is_page );
				$this->option_set( 'cp_toc_chapter_is_page', $cp_toc_chapter_is_page );

				// if chapters are not pages and we have a value for the next param
				if ( $cp_toc_chapter_is_page == '0' ) {

					$cp_show_subpages = esc_sql( $cp_show_subpages );
					$this->option_set( 'cp_show_subpages', ( $cp_show_subpages ? 1 : 0 ) );

				} else {

					// always set to show subpages
					$this->option_set( 'cp_show_subpages', 1 );

				}

			}

			// extended or vanilla posts TOC
			if ( $cp_show_posts_or_pages_in_toc == 'post' ) {

				$cp_show_extended_toc = esc_sql( $cp_show_extended_toc );
				$this->option_set( 'cp_show_extended_toc', ( $cp_show_extended_toc ? 1 : 0 ) );

			}

			// excerpt length
			$cp_excerpt_length = esc_sql( $cp_excerpt_length );
			$this->option_set( 'cp_excerpt_length', intval( $cp_excerpt_length ) );

			// comment editor
			$cp_comment_editor = esc_sql( $cp_comment_editor );
			$this->option_set( 'cp_comment_editor', ( $cp_comment_editor ? 1 : 0 ) );

			// has AJAX "live" comment refreshing been migrated?
			if ( $this->option_exists( 'cp_para_comments_live' ) ) {

				// "live" comment refreshing
				$cp_para_comments_live = esc_sql( $cp_para_comments_live );
				$this->option_set( 'cp_para_comments_live', ( $cp_para_comments_live ? 1 : 0 ) );

			}

			// behaviour
			$cp_promote_reading = esc_sql( $cp_promote_reading );
			$this->option_set( 'cp_promote_reading', ( $cp_promote_reading ? 1 : 0 ) );

			// title visibility
			$cp_title_visibility = esc_sql( $cp_title_visibility );
			$this->option_set( 'cp_title_visibility', $cp_title_visibility );

			// page meta visibility
			$cp_page_meta_visibility = esc_sql( $cp_page_meta_visibility );
			$this->option_set( 'cp_page_meta_visibility', $cp_page_meta_visibility );

			// save scroll speed
			$cp_js_scroll_speed = esc_sql( $cp_js_scroll_speed );
			$this->option_set( 'cp_js_scroll_speed', $cp_js_scroll_speed );

			// save min page width
			$cp_min_page_width = esc_sql( $cp_min_page_width );
			$this->option_set( 'cp_min_page_width', $cp_min_page_width );

			// save workflow
			$cp_blog_workflow = esc_sql( $cp_blog_workflow );
			$this->option_set( 'cp_blog_workflow', ( $cp_blog_workflow ? 1 : 0 ) );

			// save blog type
			$cp_blog_type = esc_sql( $cp_blog_type );
			$this->option_set( 'cp_blog_type', $cp_blog_type );

			// if it's a groupblog
			if ( $this->parent_obj->is_groupblog() ) {

				// get the group's id
				$group_id = get_groupblog_group_id( get_current_blog_id() );
				if ( is_numeric( $group_id ) ) {

					/**
					 * Allow plugins to override the blog type - for example if workflow
					 * is enabled, it might become a new blog type as far as BuddyPress
					 * is concerned.
					 *
					 * @param int $cp_blog_type The numeric blog type
					 * @param bool $cp_blog_workflow True if workflow enabled, false otherwise
					 */
					$blog_type = apply_filters( 'cp_get_group_meta_for_blog_type', $cp_blog_type, $cp_blog_workflow );

					// set the type as group meta info
					groups_update_groupmeta( $group_id, 'groupblogtype', 'groupblogtype-' . $blog_type );

				}

			}

			// save default sidebar
			if ( ! apply_filters( 'commentpress_hide_sidebar_option', false ) ) {
				$cp_sidebar_default = esc_sql( $cp_sidebar_default );
				$this->option_set( 'cp_sidebar_default', $cp_sidebar_default );
			}

			// save featured images
			$cp_featured_images = esc_sql( $cp_featured_images );
			$this->option_set( 'cp_featured_images', $cp_featured_images );

			// save textblock meta
			$cp_textblock_meta = esc_sql( $cp_textblock_meta );
			$this->option_set( 'cp_textblock_meta', $cp_textblock_meta );

			// save page navigation enabled flag
			$cp_page_nav_enabled = esc_sql( $cp_page_nav_enabled );
			$this->option_set( 'cp_page_nav_enabled', $cp_page_nav_enabled );

			// save do not parse flag
			$cp_do_not_parse = esc_sql( $cp_do_not_parse );
			$this->option_set( 'cp_do_not_parse', $cp_do_not_parse );

			// do we have the post types option?
			if ( $this->option_exists( 'cp_post_types_disabled' ) ) {

				// get selected post types
				$enabled_types = array_map( 'esc_sql', $cp_post_types_enabled );

				// exclude the selected post types
				$disabled_types = array_diff( $this->get_supported_post_types(), $enabled_types );

				// save skipped post types
				$this->option_set( 'cp_post_types_disabled', $disabled_types );

			}

			// save
			$this->options_save();

			// set flag
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
	 * @return array $commentpress_options The plugin options
	 */
	public function options_save() {

		// set option
		return $this->option_wp_set( 'commentpress_options', $this->commentpress_options );

	}



	/**
	 * Return existence of a specified option.
	 *
	 * @param str $option_name The name of the option
	 * @return bool True if the option exists, false otherwise
	 */
	public function option_exists( $option_name = '' ) {

		// test for null
		if ( $option_name == '' ) {

			// oops
			die( __( 'You must supply an option to option_exists()', 'commentpress-core' ) );

		}

		// get option with unlikely default
		return array_key_exists( $option_name, $this->commentpress_options );

	}



	/**
	 * Return a value for a specified option.
	 *
	 * @param str $option_name The name of the option
	 * @param mixed $default The default value for the option
	 * @return mixed The value of the option if it exists, $default otherwise
	 */
	public function option_get( $option_name = '', $default = false ) {

		// test for null
		if ( $option_name == '' ) {

			// oops
			die( __( 'You must supply an option to option_get()', 'commentpress-core' ) );

		}

		// get option
		return ( array_key_exists( $option_name, $this->commentpress_options ) ) ? $this->commentpress_options[$option_name] : $default;

	}



	/**
	 * Sets a value for a specified option.
	 *
	 * @param str $option_name The name of the option
	 * @param mixed $value The value for the option
	 * @return void
	 */
	public function option_set( $option_name = '', $value = '' ) {

		// test for null
		if ( $option_name == '' ) {

			// oops
			die( __( 'You must supply an option to option_set()', 'commentpress-core' ) );

		}

		// test for other than string
		if ( ! is_string( $option_name ) ) {

			// oops
			die( __( 'You must supply the option as a string to option_set()', 'commentpress-core' ) );

		}

		// set option
		$this->commentpress_options[$option_name] = $value;

	}



	/**
	 * Deletes a specified option.
	 *
	 * @param str $option_name The name of the option
	 * @return void
	 */
	public function option_delete( $option_name = '' ) {

		// test for null
		if ( $option_name == '' ) {

			// oops
			die( __( 'You must supply an option to option_delete()', 'commentpress-core' ) );

		}

		// unset option
		unset( $this->commentpress_options[$option_name] );

	}



	/**
	 * Return existence of a specified WordPress option.
	 *
	 * @param str $option_name The name of the option
	 * @return bool True if option exists, false otherwise
	 */
	public function option_wp_exists( $option_name = '' ) {

		// test for null
		if ( $option_name == '' ) {

			// oops
			die( __( 'You must supply an option to option_wp_exists()', 'commentpress-core' ) );

		}

		// get option with unlikely default
		if ( $this->option_wp_get( $option_name, 'fenfgehgejgrkj' ) == 'fenfgehgejgrkj' ) {

			// no
			return false;

		} else {

			// yes
			return true;

		}

	}



	/**
	 * Return a value for a specified WordPress option.
	 *
	 * @param str $option_name The name of the option
	 * @param mixed $default The default value for the option
	 * @return mixed The value of the option if it exists, $default otherwise
	 */
	public function option_wp_get( $option_name = '', $default = false ) {

		// test for null
		if ( $option_name == '' ) {

			// oops
			die( __( 'You must supply an option to option_wp_get()', 'commentpress-core' ) );

		}

		// get option
		return get_option( $option_name, $default );

	}



	/**
	 * Sets a value for a specified WordPress option.
	 *
	 * @param str $option_name The name of the option
	 * @param mixed $value The value for the option
	 * @return void
	 */
	public function option_wp_set( $option_name = '', $value = null ) {

		// test for null
		if ( $option_name == '' ) {

			// oops
			die( __( 'You must supply an option to option_wp_set()', 'commentpress-core' ) );

		}

		// set option
		return update_option( $option_name, $value );

	}



	/**
	 * Get current header background colour.
	 *
	 * @return str $header_bg_colour The hex value of the header
	 */
	public function option_get_header_bg() {

		// do we have one set via the Customizer?
		$colour = get_theme_mod( 'commentpress_header_bg_color', false );

		// return it if we do
		if ( ! empty( $colour ) ) {
			return substr( $colour, 1 );
		}

		// check if legacy option exists
		if ( $this->option_exists( 'cp_header_bg_colour' ) ) {

			// get the option
			$colour = $this->option_get( 'cp_header_bg_colour' );

			// return it if it is not yet deprecated
			if ( $colour !== 'deprecated' ) {
				return $colour;
			}

		}

		// fallback to default
		return $this->header_bg_colour;

	}



	/**
	 * When a page is saved, this also saves the CommentPress Core options.
	 *
	 * @param object $post_obj The post object
	 * @return void
	 */
	public function save_meta( $post_obj ) {

		// if no post, kick out
		if ( ! $post_obj ) return;

		// if page
		if ( $post_obj->post_type == 'page' ) {
			$this->save_page_meta( $post_obj );
		}

		// if post
		if ( $post_obj->post_type == 'post' ) {
			$this->save_post_meta( $post_obj );
		}

	}



	/**
	 * When a page is saved, this also saves the CommentPress Core options.
	 *
	 * @param object $post_obj The post object
	 * @return void
	 */
	public function save_page_meta( $post_obj ) {

		// bail if we're not authenticated
		if ( ! $this->save_page_meta_authenticated( $post_obj ) ) return;

		// check for revision
		if ( $post_obj->post_type == 'revision' ) {

			// get parent
			if ( $post_obj->post_parent != 0 ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// save page title visibility
		$this->save_page_title_visibility( $post );

		// save page meta visibility
		$this->save_page_meta_visibility( $post );

		// save page numbering
		$this->save_page_numbering( $post );

		// save page layout for Title Page
		$this->save_page_layout( $post );

		// save post formatter (overrides blog_type)
		$this->save_formatter( $post );

		// save default sidebar
		$this->save_default_sidebar( $post );

		// save starting paragraph number
		$this->save_starting_paragraph( $post );

		// save workflow meta
		$this->save_workflow( $post );

	}



	/**
	 * When a page is saved, this authenticates that our options can be saved.
	 *
	 * @param object $post_obj The post object
	 * @return void
	 */
	public function save_page_meta_authenticated( $post_obj ) {

		// if no post, kick out
		if ( ! $post_obj ) return false;

		// if not page, kick out
		if ( $post_obj->post_type != 'page' ) return false;

		// authenticate
		$nonce = isset( $_POST['commentpress_nonce'] ) ? $_POST['commentpress_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'commentpress_page_settings' ) ) return false;

		// is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) AND DOING_AUTOSAVE ) return false;

		// check permissions - 'edit_pages' is available to editor+
		if ( ! current_user_can( 'edit_pages' ) ) return false;

		// good to go
		return true;

	}



	/**
	 * Save Page Title visibility.
	 *
	 * @param object $post The post object
	 * @return string $data Either 'show' (default) or ''
	 */
	public function save_page_title_visibility( $post ) {

		// find and save the data
		$data = ( isset( $_POST['cp_title_visibility'] ) ) ? $_POST['cp_title_visibility'] : 'show';

		// set key
		$key = '_cp_title_visibility';

		// if the custom field already has a value
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// delete the meta_key if empty string
			if ( $data === '' ) {
				delete_post_meta( $post->ID, $key );
			} else {
				update_post_meta( $post->ID, $key, esc_sql( $data ) );
			}

		} else {

			// add the data
			add_post_meta( $post->ID, $key, esc_sql( $data ) );

		}

		// --<
		return $data;

	}



	/**
	 * Save Page Meta visibility.
	 *
	 * @param object $post The post object
	 * @return string $data Either 'hide' (default) or ''
	 */
	public function save_page_meta_visibility( $post ) {

		// find and save the data
		$data = ( isset( $_POST['cp_page_meta_visibility'] ) ) ? $_POST['cp_page_meta_visibility'] : 'hide';

		// set key
		$key = '_cp_page_meta_visibility';

		// if the custom field already has a value
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// delete the meta_key if empty string
			if ( $data === '' ) {
				delete_post_meta( $post->ID, $key );
			} else {
				update_post_meta( $post->ID, $key, esc_sql( $data ) );
			}

		} else {

			// add the data
			add_post_meta( $post->ID, $key, esc_sql( $data ) );

		}

		// --<
		return $data;

	}



	/**
	 * Save Page Numbering format.
	 *
	 * Only first top-level page is allowed to save this
	 *
	 * @param object $post The post object
	 * @return void
	 */
	public function save_page_numbering( $post ) {

		// was the value sent?
		if ( isset( $_POST['cp_number_format'] ) ) {

			// set meta key
			$key = '_cp_number_format';

			// do we need to check this, since only the first top level page
			// can now send this data? doesn't hurt to validate, I guess.
			if (
				$post->post_parent == '0' AND
				! $this->is_special_page() AND
				$post->ID == $this->parent_obj->nav->get_first_page()
			) {

				// get the data
				$data = $_POST['cp_number_format'];

				// if the custom field already has a value
				if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

					// if empty string
					if ( $data === '' ) {

						// delete the meta_key
						delete_post_meta( $post->ID, $key );

					} else {

						// update the data
						update_post_meta( $post->ID, $key, esc_sql( $data ) );

					}

				} else {

					// add the data
					add_post_meta( $post->ID, $key, esc_sql( $data ) );

				}

			}

			// delete this meta value from all other pages, because we may have altered
			// the relationship between pages, thus causing the page numbering to fail

			// get all pages including chapters
			$all_pages = $this->parent_obj->nav->get_book_pages( 'structural' );

			// if we have any pages
			if ( count( $all_pages ) > 0 ) {

				// loop
				foreach( $all_pages AS $page ) {

					// exclude first top level page
					if ( $post->ID != $page->ID ) {

						// delete the meta value
						delete_post_meta( $page->ID, $key );

					}

				}

			}

		}

	}



	/**
	 * Save Page Layout for Title Page -> to allow for Book Cover image.
	 *
	 * @param object $post The post object
	 * @return void
	 */
	public function save_page_layout( $post ) {

		// is this the title page?
		if ( $post->ID == $this->option_get( 'cp_welcome_page' ) ) {

			// find and save the data
			$data = ( isset( $_POST['cp_page_layout'] ) ) ? $_POST['cp_page_layout'] : 'text';

			// set key
			$key = '_cp_page_layout';

			// if the custom field already has a value
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// delete the meta_key if empty string
				if ( $data === '' ) {
					delete_post_meta( $post->ID, $key );
				} else {
					update_post_meta( $post->ID, $key, esc_sql( $data ) );
				}

			} else {

				// add the data
				add_post_meta( $post->ID, $key, esc_sql( $data ) );

			}

		}

	}



	/**
	 * When a post is saved, this also saves the CommentPress Core options.
	 *
	 * @param object $post_obj The post object
	 * @return void
	 */
	public function save_post_meta( $post_obj ) {

		// bail if we're not authenticated
		if ( ! $this->save_post_meta_authenticated( $post_obj ) ) return;

		// check for revision
		if ( $post_obj->post_type == 'revision' ) {

			// get parent
			if ( $post_obj->post_parent != 0 ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// save post formatter (overrides blog_type)
		$this->save_formatter( $post );

		// save workflow meta
		$this->save_workflow( $post );

		// save default sidebar
		$this->save_default_sidebar( $post );

		// ---------------------------------------------------------------------
		// Create new post with content of current
		// ---------------------------------------------------------------------

		// find and save the data
		$data = ( isset( $_POST['commentpress_new_post'] ) ) ? $_POST['commentpress_new_post'] : '0';

		// do we want to create a new revision?
		if ( $data == '0' ) return;



		// we need to make sure this only runs once
		if ( $this->saved_post === false ) {
			$this->saved_post = true;
		} else {
			return;
		}

		// ---------------------------------------------------------------------

		// we're through: create it
		$new_post_id = $this->_create_new_post( $post );

		// ---------------------------------------------------------------------
		// Store ID of new version in current version
		// ---------------------------------------------------------------------

		// set key
		$key = '_cp_newer_version';

		// if the custom field already has a value
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// delete the meta_key if empty string
			if ( $data === '' ) {
				delete_post_meta( $post->ID, $key );
			} else {
				update_post_meta( $post->ID, $key, $new_post_id );
			}

		} else {

			// add the data
			add_post_meta( $post->ID, $key, $new_post_id );

		}

		// ---------------------------------------------------------------------
		// Store incremental version number in new version
		// ---------------------------------------------------------------------

		// set key
		$key = '_cp_version_count';

		// if the custom field of our current post has a value
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// get current value
			$value = get_post_meta( $post->ID, $key, true );

			// increment
			$value++;

		} else {

			// this must be the first new version (Draft 2)
			$value = 2;

		}

		// add the data
		add_post_meta( $new_post_id, $key, $value );

		// ---------------------------------------------------------------------
		// Store formatter in new version
		// ---------------------------------------------------------------------

		// set key
		$key = '_cp_post_type_override';

		// if we have one set
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// get current value
			$formatter = get_post_meta( $post->ID, $key, true );

			// add the data
			add_post_meta( $new_post_id, $key, esc_sql( $formatter ) );

		}

		// allow plugins to hook into this
		do_action( 'cp_workflow_save_copy', $new_post_id );

		// get the edit post link
		//$edit_link = get_edit_post_link( $new_post_id );

		// redirect there?

	}



	/**
	 * When a post is saved, this authenticates that our options can be saved.
	 *
	 * @param object $post_obj The post object
	 * @return void
	 */
	public function save_post_meta_authenticated( $post_obj ) {

		// if no post, kick out
		if ( ! $post_obj ) return false;

		// if not page, kick out
		if ( $post_obj->post_type != 'post' ) return false;

		// authenticate
		$nonce = isset( $_POST['commentpress_nonce'] ) ? $_POST['commentpress_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'commentpress_post_settings' ) ) return false;

		// is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) AND DOING_AUTOSAVE ) return false;

		// check permissions - 'edit_posts' is available to contributor+
		if ( ! current_user_can( 'edit_posts', $post_obj->ID ) ) return false;

		// good to go
		return true;

	}



	/**
	 * Override post formatter.
	 *
	 * This overrides the "blog_type" for a post.
	 *
	 * @param object $post The post object
	 * @return void
	 */
	public function save_formatter( $post ) {

		// get the data
		$data = ( isset( $_POST['cp_post_type_override'] ) ) ? $_POST['cp_post_type_override'] : '';

		// set key
		$key = '_cp_post_type_override';

		// if the custom field already has a value
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// delete the meta_key if empty string
			if ( $data === '' ) {
				delete_post_meta( $post->ID, $key );
			} else {
				update_post_meta( $post->ID, $key, esc_sql( $data ) );
			}

		} else {

			// add the data
			add_post_meta( $post->ID, $key, esc_sql( $data ) );

		}

	}



	/**
	 * Override default sidebar.
	 *
	 * @param object $post The post object
	 * @return void
	 */
	public function save_default_sidebar( $post ) {

		// allow this to be disabled
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) return;

		// do we have the option to choose the default sidebar (new in 3.3.3)?
		if ( $this->option_exists( 'cp_sidebar_default' ) ) {

			// find and save the data
			$data = ( isset( $_POST['cp_sidebar_default'] ) ) ?
					 $_POST['cp_sidebar_default'] :
					 $this->option_get( 'cp_sidebar_default' );

			// set key
			$key = '_cp_sidebar_default';

			// if the custom field already has a value
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// delete the meta_key if empty string
				if ( $data === '' ) {
					delete_post_meta( $post->ID, $key );
				} else {
					update_post_meta( $post->ID, $key, esc_sql( $data ) );
				}

			} else {

				// add the data
				add_post_meta( $post->ID, $key, esc_sql( $data ) );

			}

		}

	}



	/**
	 * Starting Paragraph Number - meta only exists when not default value.
	 *
	 * @param object $post The post object
	 * @return void
	 */
	public function save_starting_paragraph( $post ) {

		// get the data
		$data = ( isset( $_POST['cp_starting_para_number'] ) ) ? $_POST['cp_starting_para_number'] : 1;

		// if not numeric, set to default
		if ( ! is_numeric( $data ) ) { $data = 1; }

		// sanitize it
		$data = absint( $data );

		// set key
		$key = '_cp_starting_para_number';

		// if the custom field already has a value
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// delete if default
			if ( $data === 1 ) {
				delete_post_meta( $post->ID, $key );
			} else {
				update_post_meta( $post->ID, $key, esc_sql( $data ) );
			}

		} else {

			// add the data if greater than default
			if ( $data > 1 ) {
				add_post_meta( $post->ID, $key, esc_sql( $data ) );
			}

		}

	}



	/**
	 * Save workflow meta value.
	 *
	 * @param object $post The post object
	 * @return void
	 */
	public function save_workflow( $post ) {

		// do we have the option to set workflow (new in 3.3.1)?
		if ( $this->option_exists( 'cp_blog_workflow' ) ) {

			// get workflow setting for the blog
			$workflow = $this->option_get( 'cp_blog_workflow' );

			/*
			// ----------------
			// WORK IN PROGRESS

			// set key
			$key = '_cp_blog_workflow_override';

			// if the custom field already has a value
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// get existing value
				$workflow = get_post_meta( $post->ID, $key, true );

			}
			// ----------------
			*/

			// if it's enabled
			if ( $workflow == '1' ) {

				// notify plugins that workflow stuff needs saving
				do_action( 'cp_workflow_save_' . $post->post_type, $post );

			}

			/*
			// ----------------
			// WORK IN PROGRESS

			// get the setting for the post (we do this after saving the extra
			// post data because
			$formatter = ( isset( $_POST['cp_post_type_override'] ) ) ? $_POST['cp_post_type_override'] : '';

			// if the custom field already has a value
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// if empty string
				if ( $data === '' ) {

					// delete the meta_key
					delete_post_meta( $post->ID, $key );

				} else {

					// update the data
					update_post_meta( $post->ID, $key, esc_sql( $data ) );

				}

			} else {

				// add the data
				add_post_meta( $post->ID, $key, esc_sql( $data ) );

			}
			// ----------------
			*/

		}

	}



	/**
	 * When a page is deleted, this makes sure that the CommentPress Core options are synced.
	 *
	 * @param object $post_id The post ID
	 * @return void
	 */
	public function delete_meta( $post_id ) {

		// if no post, kick out
		if ( ! $post_id ) return;

		// if it's our welcome page
		if ( $post_id == $this->option_get( 'cp_welcome_page' ) ) {

			// delete option
			$this->option_delete( 'cp_welcome_page' );

			// save
			$this->options_save();

		}

		// for posts with versions, we need to delete the version data for the previous version

		// define key
		$key = '_cp_newer_version';

		// get posts with the about-to-be-deleted post_id (there will be only one, if at all)
		$previous_versions = get_posts( array(

			'meta_key' => $key,
			'meta_value' => $post_id

		) );

		// did we get one?
		if ( count( $previous_versions ) > 0 ) {

			// get it
			$previous_version = $previous_versions[0];

			// if the custom field has a value
			if ( get_post_meta( $previous_version->ID, $key, true ) !== '' ) {

				// delete it
				delete_post_meta( $previous_version->ID, $key );

			}

		}

	}



	/**
	 * Create all "special" pages.
	 *
	 * @return void
	 */
	public function create_special_pages() {

		// one of the CommentPress Core themes MUST be active
		// or WordPress will fail to set the page templates for the pages that require them.
		// Also, a user must be logged in for these pages to be associated with them.

		// get special pages array, if it's there
		$special_pages = $this->option_get( 'cp_special_pages', array() );

		// create welcome/title page, but don't add to special pages
		$welcome = $this->_create_title_page();

		// create general comments page
		$special_pages[] = $this->_create_general_comments_page();

		// create all comments page
		$special_pages[] = $this->_create_all_comments_page();

		// create comments by author page
		$special_pages[] = $this->_create_comments_by_author_page();

		// create blog page
		$special_pages[] = $this->_create_blog_page();

		// create blog archive page
		$special_pages[] = $this->_create_blog_archive_page();

		// create TOC page -> a convenience, let's us define a logo as attachment
		$special_pages[] = $this->_create_toc_page();

		// store the array of page IDs that were created
		$this->option_set( 'cp_special_pages', $special_pages );

		// save changes
		$this->options_save();

	}



	/**
	 * Create a particular "special" page.
	 *
	 * @param str $page The type of special page
	 * @return mixed $new_id If successful, the numeric ID of the new page, false on failure
	 */
	public function create_special_page( $page ) {

		// init
		$new_id = false;

		// get special pages array, if it's there
		$special_pages = $this->option_get( 'cp_special_pages', array() );

		// switch by page
		switch( $page ) {

			case 'title':

				// create welcome/title page
				$new_id = $this->_create_title_page();
				break;

			case 'general_comments':

				// create general comments page
				$new_id = $this->_create_general_comments_page();
				break;

			case 'all_comments':

				// create all comments page
				$new_id = $this->_create_all_comments_page();
				break;

			case 'comments_by_author':

				// create comments by author page
				$new_id = $this->_create_comments_by_author_page();
				break;

			case 'blog':

				// create blog page
				$new_id = $this->_create_blog_page();
				break;

			case 'blog_archive':

				// create blog page
				$new_id = $this->_create_blog_archive_page();
				break;

			case 'toc':

				// create TOC page
				$new_id = $this->_create_toc_page();
				break;

		}

		// add to special pages
		$special_pages[] = $new_id;

		// reset option
		$this->option_set( 'cp_special_pages', $special_pages );

		// save changes
		$this->options_save();

		// --<
		return $new_id;

	}



	/**
	 * Delete "special" pages.
	 *
	 * @return bool $success True if page deleted successfully, false otherwise
	 */
	public function delete_special_pages() {

		// init success flag
		$success = true;

		/**
		 * Only delete special pages if we have one of the CommentPress Core
		 * themes active because other themes may have a totally different way
		 * of presenting the content of the blog.
		 */

		// retrieve data on special pages
		$special_pages = $this->option_get( 'cp_special_pages', array() );

		// if we have created any
		if ( is_array( $special_pages ) AND count( $special_pages ) > 0 ) {

			// loop through them
			foreach( $special_pages AS $special_page ) {

				// bypass trash
				$force_delete = true;

				// try and delete each page
				if ( ! wp_delete_post( $special_page, $force_delete ) ) {

					// oops, set success flag to false
					$success = false;

				}

			}

			// delete the corresponding options
			$this->option_delete( 'cp_special_pages' );

			$this->option_delete( 'cp_blog_page' );
			$this->option_delete( 'cp_blog_archive_page' );
			$this->option_delete( 'cp_general_comments_page' );
			$this->option_delete( 'cp_all_comments_page' );
			$this->option_delete( 'cp_comments_by_page' );
			$this->option_delete( 'cp_toc_page' );

			// for now, keep welcome page - delete option when page is deleted
			//$this->option_delete( 'cp_welcome_page' );

			// save changes
			$this->options_save();

			// reset WordPress internal page references
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
	 * @param str $page The type of special page to delete
	 * @return boolean $success True if succesfully deleted false otherwise
	 */
	public function delete_special_page( $page ) {

		// init success flag
		$success = true;

		/**
		 * Only delete a special page if we have one of the CommentPress Core
		 * themes active because other themes may have a totally different way
		 * of presenting the content of the blog.
		 */

		// get id of special page
		switch( $page ) {

			case 'title':

				// set flag
				$flag = 'cp_welcome_page';

				// reset WordPress internal page references
				$this->_reset_wordpress_option( 'show_on_front' );
				$this->_reset_wordpress_option( 'page_on_front' );

				break;

			case 'general_comments':

				// set flag
				$flag = 'cp_general_comments_page';
				break;

			case 'all_comments':

				// set flag
				$flag = 'cp_all_comments_page';
				break;

			case 'comments_by_author':

				// set flag
				$flag = 'cp_comments_by_page';
				break;

			case 'blog':

				// set flag
				$flag = 'cp_blog_page';

				// reset WordPress internal page reference
				$this->_reset_wordpress_option( 'page_for_posts' );

				break;

			case 'blog_archive':

				// set flag
				$flag = 'cp_blog_archive_page';
				break;

			case 'toc':

				// set flag
				$flag = 'cp_toc_page';
				break;

		}

		// get page id
		$page_id = $this->option_get( $flag );

		// kick out if it doesn't exist
		if ( ! $page_id ) return true;

		// delete option
		$this->option_delete( $flag );

		// bypass trash
		$force_delete = true;

		// try and delete the page
		if ( ! wp_delete_post( $page_id, $force_delete ) ) {

			// oops, set success flag to false
			$success = false;

		}

		// retrieve data on special pages
		$special_pages = $this->option_get( 'cp_special_pages', array() );

		// is it in our special pages array?
		if ( in_array( $page_id, $special_pages ) ) {

			// remove page id from array
			$special_pages = array_diff( $special_pages, array( $page_id ) );

			// reset option
			$this->option_set( 'cp_special_pages', $special_pages );

		}

		// save changes
		$this->options_save();

		// --<
		return $success;

	}



	/**
	 * Test if a page is a "special" page.
	 *
	 * @return bool $is_special_page True if a special page, false otherwise
	 */
	public function is_special_page() {

		// init flag
		$is_special_page = false;

		// access post object
		global $post;

		// do we have one?
		if ( ! is_object( $post ) ) {

			// --<
			return $is_special_page;

		}

		// get special pages
		$special_pages = $this->option_get( 'cp_special_pages', array() );

		// do we have a special page array?
		if ( is_array( $special_pages ) AND count( $special_pages ) > 0 ) {

			// is the current page one?
			if ( in_array( $post->ID, $special_pages ) ) {

				// it is
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
	 * @return array $supported_post_types Array of post types that have an editor
	 */
	public function get_supported_post_types() {

		// only parse post types once
		static $supported_post_types = array();
		if ( ! empty( $supported_post_types ) ) {
			return $supported_post_types;
		}

		// get only post types with an admin UI
		$args = array(
			'public' => true,
			'show_ui' => true,
		);

		// get post types
		$post_types = get_post_types( $args );

		// include only those which have an editor
		foreach ( $post_types AS $post_type ) {
			if ( post_type_supports( $post_type, 'editor' ) ) {
				$supported_post_types[] = $post_type;
			}
		}

		// built-in media descriptions are also supported
		$supported_post_types[] = 'attachment';

		// --<
		return $supported_post_types;

	}

	/**
	 * Check if a post allows comments to be posted.
	 *
	 * @return boolean $allowed True if comments enabled, false otherwise
	 */
	public function comments_enabled() {

		// init return
		$allowed = false;

		// access post object
		global $post;

		// do we have one?
		if ( ! is_object( $post ) ) {

			// --<
			return $allowed;

		}

		// are comments enabled on this post?
		if ( $post->comment_status == 'open' ) {

			// set return
			$allowed = true;

		}

		// --<
		return $allowed;
	}



	/**
	 * Get WordPress approved comments.
	 *
	 * @param int $post_id The numeric ID of the post
	 * @return array $comments The array of comment data
	 */
	public function get_approved_comments( $post_ID ) {

		// for WordPress, we use the API
		$comments = get_approved_comments( $post_ID );

		// --<
		return $comments;

	}



	/**
	 * Get all WordPress comments for a post, unless paged.
	 *
	 * @param int $post_ID The numeric ID of the post
	 * @return array $comments The array of comment data
	 */
	public function get_all_comments( $post_ID ) {

		// access post
		global $post;

		// for WordPress, we use the API
		$comments = get_comments( 'post_id=' . $post_ID . '&order=ASC' );

		// --<
		return $comments;

	}



	/**
	 * Get all comments for a post.
	 *
	 * @param int $post_ID The ID of the post
	 * @return array $comments The array of comment data
	 */
	public function get_comments( $post_ID ) {

		// database object
		global $wpdb;

		// get comments from db
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
	 * @param int $comment_id The numeric ID of the comment
	 * @return boolean $result True if successful, false otherwise
	 */
	public function save_comment_signature( $comment_ID ) {

		// database object
		global $wpdb;

		// get text signature
		$text_signature = ( isset( $_POST['text_signature'] ) ) ? $_POST['text_signature'] : '';

		// did we get one?
		if ( $text_signature != '' ) {

			// escape it
			$text_signature = esc_sql( $text_signature );

			// construct query
			$query = $wpdb->prepare(
				"UPDATE $wpdb->comments SET comment_signature = %s WHERE comment_ID = %d",
				$text_signature,
				$comment_ID
			);

			// store comment signature
			$result = $wpdb->query( $query );

		} else {

			// set result to true - why not, eh?
			$result = true;

		}

		// --<
		return $result;

	}



	/**
	 * When a comment is saved, this also saves the text selection.
	 *
	 * @param int $comment_id The numeric ID of the comment
	 * @return boolean $result True if successful, false otherwise
	 */
	public function save_comment_selection( $comment_id ) {

		// get text selection
		$text_selection = ( isset( $_POST['text_selection'] ) ) ? $_POST['text_selection'] : '';

		// bail if we didn't get one
		if ( $text_selection == '' ) return true;

		// sanity check: must have a comma
		if ( stristr( $text_selection, ',' ) === false ) return true;

		// make into an array
		$selection = explode( ',', $text_selection );

		// sanity check: must have only two elements
		if ( count( $selection ) != 2 ) return true;

		// sanity check: both elements must be integers
		$start_end = array();
		foreach( $selection AS $item ) {

			// not integer - kick out
			if ( ! is_numeric( $item ) ) return true;

			// cast as integer and add to array
			$start_end[] = absint( $item );

		}

		// okay, we're good to go
		$selection_data = implode( ',', $start_end );

		// set key
		$key = '_cp_comment_selection';

		// get current
		$current = get_comment_meta( $comment_id, $key, true );

		// if the comment meta already has a value
		if ( ! empty( $current ) ) {

			// update the data
			update_comment_meta( $comment_id, $key, $selection_data );

		} else {

			// add the data
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
	 * @param int $comment_ID The numeric ID of the comment
	 * @return void
	 */
	public function save_comment_page( $comment_ID ) {

		// is this a paged post?
		if ( isset( $_POST['page'] ) AND is_numeric( $_POST['page'] ) ) {

			// get text signature
			$text_signature = ( isset( $_POST['text_signature'] ) ) ? $_POST['text_signature'] : '';

			// is it a para-level comment?
			if ( $text_signature != '' ) {

				// get page number
				$page_number = esc_sql( $_POST['page'] );

				// set key
				$key = '_cp_comment_page';

				// if the custom field already has a value
				if ( get_comment_meta( $comment_ID, $key, true ) != '' ) {

					// update the data
					update_comment_meta( $comment_ID, $key, $page_number );

				} else {

					// add the data
					add_comment_meta( $comment_ID, $key, $page_number, true );

				}

			} else {

				// top level comments are always page 1
				//$page_number = 1;

			}

		}

	}



	/**
	 * Retrieves text signature by comment ID.
	 *
	 * @param int $comment_ID The numeric ID of the comment
	 * @return str $text_signature The text signature for the comment
	 */
	public function get_text_signature_by_comment_id( $comment_ID ) {

		// database object
		global $wpdb;

		// query for signature
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
	 * @param array $sigs An array of text signatures
	 * @return void
	 */
	public function set_text_sigs( $sigs ) {

		// store them
		global $ffffff_sigs;
		$ffffff_sigs = $sigs;

	}



	/**
	 * Retrieve text sigs.
	 *
	 * @return array $text_signatures An array of text signatures
	 */
	public function get_text_sigs() {

		// get them
		global $ffffff_sigs;
		return $ffffff_sigs;

	}



	/**
	 * Get javascript for the plugin, context dependent.
	 *
	 * @return str $script The Javascript
	 */
	public function get_javascript_vars() {

		// init return
		$vars = array();

		// add comments open
		global $post;

		// if we don't have a post (like on the 404 page)
		if ( ! is_object( $post ) ) {

			// comments must be closed
			$vars['cp_comments_open'] = 'n';

			// set empty permalink
			$vars['cp_permalink'] = '';

		} else {

			// check for post comment_status
			$vars['cp_comments_open'] = ( $post->comment_status == 'open' ) ? 'y' : 'n';

			// set post permalink
			$vars['cp_permalink'] = get_permalink( $post->ID );

		}

		// assume no admin bars
		$vars['cp_wp_adminbar'] = 'n';
		$vars['cp_bp_adminbar'] = 'n';

		// assume pre-3.8 admin bar
		$vars['cp_wp_adminbar_height'] = '28';
		$vars['cp_wp_adminbar_expanded'] = '0';

		// are we showing the WP admin bar?
		if ( function_exists( 'is_admin_bar_showing' ) AND is_admin_bar_showing() ) {

			// we have it
			$vars['cp_wp_adminbar'] = 'y';

			// check for a WP 3.8+ function
			if ( function_exists( 'wp_admin_bar_sidebar_toggle' ) ) {

				// the 3.8+ admin bar is taller
				$vars['cp_wp_adminbar_height'] = '32';

				// it also expands in height below 782px viewport width
				$vars['cp_wp_adminbar_expanded'] = '46';

			}

		}

		// are we logged in AND in a BuddyPress scenario?
		if ( is_user_logged_in() AND $this->parent_obj->is_buddypress() ) {

			// regardless of version, settings can be made in bp-custom.php
			if ( defined( 'BP_DISABLE_ADMIN_BAR' ) AND BP_DISABLE_ADMIN_BAR ) {

				// we've killed both admin bars
				$vars['cp_bp_adminbar'] = 'n';
				$vars['cp_wp_adminbar'] = 'n';

			}

			// check for BuddyPress versions prior to 1.6 (1.6 uses the WP admin bar instead of a custom one)
			if ( ! function_exists( 'bp_get_version' ) ) {

				// but, this can already be overridden in bp-custom.php
				if ( defined( 'BP_USE_WP_ADMIN_BAR' ) AND BP_USE_WP_ADMIN_BAR ) {

					// not present
					$vars['cp_bp_adminbar'] = 'n';
					$vars['cp_wp_adminbar'] = 'y';

				} else {

					// let our javascript know
					$vars['cp_bp_adminbar'] = 'y';

					// recheck 'BP_DISABLE_ADMIN_BAR'
					if ( defined( 'BP_DISABLE_ADMIN_BAR' ) AND BP_DISABLE_ADMIN_BAR ) {

						// we've killed both admin bars
						$vars['cp_bp_adminbar'] = 'n';
						$vars['cp_wp_adminbar'] = 'n';

					}

				}

			}

		}

		// add rich text editor
		$vars['cp_tinymce'] = 1;

		// check if users must be logged in to comment
		if ( get_option( 'comment_registration' ) == '1' AND ! is_user_logged_in() ) {

			// don't add rich text editor
			$vars['cp_tinymce'] = 0;

		}

		// check CommentPress Core option
		if (
			$this->option_exists( 'cp_comment_editor' ) AND
			$this->option_get( 'cp_comment_editor' ) != '1'
		) {

			// don't add rich text editor
			$vars['cp_tinymce'] = 0;

		}

		// if on a public groupblog and user isn't logged in
		if ( $this->parent_obj->is_groupblog() AND ! is_user_logged_in() ) {

			// don't add rich text editor, because only members can comment
			$vars['cp_tinymce'] = 0;

		}

		// allow plugins to override TinyMCE
		$vars['cp_tinymce'] = apply_filters(
			'cp_override_tinymce',
			$vars['cp_tinymce']
		);

		// add mobile var
		$vars['cp_is_mobile'] = 0;

		// is it a mobile?
		if ( isset( $this->is_mobile ) AND $this->is_mobile ) {

			// is mobile
			$vars['cp_is_mobile'] = 1;

			// don't add rich text editor
			$vars['cp_tinymce'] = 0;

		}

		// add touch var
		$vars['cp_is_touch'] = 0;

		// is it a touch device?
		if ( isset( $this->is_mobile_touch ) AND $this->is_mobile_touch ) {

			// is touch
			$vars['cp_is_touch'] = 1;

			// don't add rich text editor
			$vars['cp_tinymce'] = 0;

		}

		// add touch testing var
		$vars['cp_touch_testing'] = 0;

		// have we set our testing constant?
		if ( defined( 'COMMENTPRESS_TOUCH_SELECT' ) AND COMMENTPRESS_TOUCH_SELECT ) {

			// support touch device testing
			$vars['cp_touch_testing'] = 1;

		}

		// add tablet var
		$vars['cp_is_tablet'] = 0;

		// is it a touch device?
		if ( isset( $this->is_tablet ) AND $this->is_tablet ) {

			// is touch
			$vars['cp_is_tablet'] = 1;

			// don't add rich text editor
			$vars['cp_tinymce'] = 0;

		}

		// add TinyMCE version var
		$vars['cp_tinymce_version'] = 3;

		// access WP version
		global $wp_version;

		// if greater than 3.8
		if ( version_compare( $wp_version, '3.8.9999', '>' ) ) {

			// add newer TinyMCE version
			$vars['cp_tinymce_version'] = 4;

		}

		// add rich text editor behaviour
		$vars['cp_promote_reading'] = 1;

		// check option
		if (
			$this->option_exists( 'cp_promote_reading' ) AND
			$this->option_get( 'cp_promote_reading' ) != '1'
		) {

			// promote commenting
			$vars['cp_promote_reading'] = 0;

		}

		// add special page var
		$vars['cp_special_page'] = ( $this->is_special_page() ) ? '1' : '0';

		// are we in a BuddyPress scenario?
		if ( $this->parent_obj->is_buddypress() ) {

			// is it a component homepage?
			if ( $this->parent_obj->is_buddypress_special_page() ) {

				// treat them the way we do ours
				$vars['cp_special_page'] = '1';

			}

		}

		// get path
		$url_info = parse_url( get_option('siteurl') );

		// add path for cookies
		$vars['cp_cookie_path'] = '/';
		if ( isset( $url_info['path'] ) ) {
			$vars['cp_cookie_path'] = trailingslashit( $url_info['path'] );
		}

		// add page
		global $page;
		$vars['cp_multipage_page'] = ( ! empty( $page ) ) ? $page : 0;

		// are chapters pages?
		$vars['cp_toc_chapter_is_page'] = $this->option_get( 'cp_toc_chapter_is_page' );

		// are subpages shown?
		$vars['cp_show_subpages'] = $this->option_get( 'cp_show_subpages' );

		// set default sidebar
		$vars['cp_default_sidebar'] = $this->parent_obj->get_default_sidebar();

		// set scroll speed
		$vars['cp_js_scroll_speed'] = $this->option_get( 'cp_js_scroll_speed' );

		// set min page width
		$vars['cp_min_page_width'] = $this->option_get( 'cp_min_page_width' );

		// default to showing textblock meta
		$vars['cp_textblock_meta'] = 1;

		// check option
		if (
			$this->option_exists( 'cp_textblock_meta' ) AND
			$this->option_get( 'cp_textblock_meta' ) == 'n'
		) {

			// only show textblock meta on rollover
			$vars['cp_textblock_meta'] = 0;

		}

		// default to page navigation enabled
		$vars['cp_page_nav_enabled'] = 1;

		// check option
		if (
			$this->option_exists( 'cp_page_nav_enabled' ) AND
			$this->option_get( 'cp_page_nav_enabled' ) == 'n'
		) {

			// disable page navigation
			$vars['cp_page_nav_enabled'] = 0;

		}

		// default to parsing content and comments
		$vars['cp_do_not_parse'] = 0;

		// check option
		if (
			$this->option_exists( 'cp_do_not_parse' ) AND
			$this->option_get( 'cp_do_not_parse' ) == 'y'
		) {

			// do not parse
			$vars['cp_do_not_parse'] = 1;

		}

		// --<
		return apply_filters( 'commentpress_get_javascript_vars', $vars );

	}



	/**
	 * Sets class properties for mobile browsers.
	 *
	 * @return void
	 */
	public function test_for_mobile() {

		// init mobile flag
		$this->is_mobile = false;

		// init tablet flag
		$this->is_tablet = false;

		// init touch flag
		$this->is_mobile_touch = false;

		// do we have a user agent?
		if ( isset( $_SERVER["HTTP_USER_AGENT"] ) ) {

			// the old CommentPress also includes Mobile_Detect
			if ( ! class_exists( 'Mobile_Detect' ) ) {

				// use code from http://code.google.com/p/php-mobile-detect/
				include_once( COMMENTPRESS_PLUGIN_PATH . 'commentpress-core/assets/includes/mobile-detect/Mobile_Detect.php' );

			}

			// init
			$detect = new Mobile_Detect();

			// overwrite flag if mobile
			if ( $detect->isMobile() ) {
				$this->is_mobile = true;
			}

			// overwrite flag if tablet
			if ( $detect->isTablet() ) {
				$this->is_tablet = true;
			}

			// to guess at touch devices, we assume *either* phone *or* tablet
			if ( $this->is_mobile OR $this->is_tablet ) {
				$this->is_mobile_touch = true;
			}

		}

	}



	/**
	 * Returns class properties for mobile browsers.
	 *
	 * @return bool $is_mobile True if mobile device, false otherwise
	 */
	public function is_mobile() {

		// do we have the property?
		if ( ! isset( $this->is_mobile ) ) {

			// get it
			$this->test_for_mobile();

		}

		// --<
		return $this->is_mobile;

	}



	/**
	 * Returns class properties for tablet browsers.
	 *
	 * @return bool $is_tablet True if tablet device, false otherwise
	 */
	public function is_tablet() {

		// do we have the property?
		if ( ! isset( $this->is_tablet ) ) {

			// get it
			$this->test_for_mobile();

		}

		// --<
		return $this->is_tablet;

	}



	/**
	 * Returns class properties for touch devices.
	 *
	 * @return bool $is_touch True if touch device, false otherwise
	 */
	public function is_touch() {

		// do we have the property?
		if ( ! isset( $this->is_mobile_touch ) ) {

			// get it
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
	 * @return int $post The WordPress post object to make a copy of
	 * @return int $new_post_id The numeric ID of the new post
	 */
	function _create_new_post( $post ) {

		// define basics
		$new_post = array(
			'post_status' => 'draft',
			'post_type' => 'post',
			'comment_status' => 'open',
			'ping_status' => 'open',
			'to_ping' => '', // quick fix for Windows
			'pinged' => '', // quick fix for Windows
			'post_content_filtered' => '', // quick fix for Windows
			'post_excerpt' => '' // quick fix for Windows
		);

		// add post-specific stuff

		// default page title
		$prefix = __( 'Copy of ', 'commentpress-core' );

		// allow overrides of prefix
		$prefix = apply_filters( 'commentpress_new_post_title_prefix', $prefix );

		// set title, but allow overrides
		$new_post['post_title'] = apply_filters( 'commentpress_new_post_title', $prefix . $post->post_title, $post );

		// set excerpt, but allow overrides
		$new_post['post_excerpt'] = apply_filters( 'commentpress_new_post_excerpt', $post->post_excerpt );

		// set content, but allow overrides
		$new_post['post_content'] = apply_filters( 'commentpress_new_post_content', $post->post_content );

		// set post author, but allow overrides
		$new_post['post_author'] = apply_filters( 'commentpress_new_post_author', $post->post_author );

		// Insert the post into the database
		$new_post_id = wp_insert_post( $new_post );

		// --<
		return $new_post_id;

	}



	/**
	 * Create "title" page.
	 *
	 * @return int $title_id The numeric ID of the Title Page
	 */
	function _create_title_page() {

		// get the option, if it exists
		$page_exists = $this->option_get( 'cp_welcome_page' );

		// don't create if we already have the option set
		if ( $page_exists !== false AND is_numeric( $page_exists ) ) {

			// get the page (the plugin may have been deactivated, then the page deleted)
			$welcome = get_post( $page_exists );

			// check that the page exists
			if ( ! is_null( $welcome ) ) {

				// got it:

				// we still ought to set WordPress internal page references
				$this->_store_wordpress_option( 'show_on_front', 'page' );
				$this->_store_wordpress_option( 'page_on_front', $page_exists );

				// --<
				return $page_exists;

			} else {

				// page does not exist, continue on and create it

			}

		}

		// define welcome/title page
		$title = array(
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'open',
			'ping_status' => 'closed',
			'to_ping' => '', // quick fix for Windows
			'pinged' => '', // quick fix for Windows
			'post_content_filtered' => '', // quick fix for Windows
			'post_excerpt' => '', // quick fix for Windows
			'menu_order' => 0
		);

		// add post-specific stuff

		// default page title
		$default_title = __( 'Title Page', 'commentpress-core' );

		// set, but allow overrides
		$title['post_title'] = apply_filters( 'cp_title_page_title', $default_title );

		// default content
		$content = __(

		'Welcome to your new CommentPress site, which allows your readers to comment paragraph-by-paragraph or line-by-line in the margins of a text. Annotate, gloss, workshop, debate: with CommentPress you can do all of these things on a finer-grained level, turning a document into a conversation.

This is your title page. Edit it to suit your needs. It has been automatically set as your homepage but if you want another page as your homepage, set it in <em>WordPress</em> &#8594; <em>Settings</em> &#8594; <em>Reading</em>.

You can also set a number of options in <em>WordPress</em> &#8594; <em>Settings</em> &#8594; <em>CommentPress</em> to make the site work the way you want it to. Use the Theme Customizer to change the way your site looks in <em>WordPress</em> &#8594; <em>Appearance</em> &#8594; <em>Customize</em>. For help with structuring, formatting and reading text in CommentPress, please refer to the <a href="http://www.futureofthebook.org/commentpress/">CommentPress website</a>.', 'commentpress-core'

		);

		// set, but allow overrides
		$title['post_content'] = apply_filters( 'cp_title_page_content', $content );

		// set template, but allow overrides
		$title['page_template'] = apply_filters( 'cp_title_page_template', 'welcome.php' );

		// Insert the post into the database
		$title_id = wp_insert_post( $title );

		// make sure it has the default formatter (0 = prose)
		add_post_meta( $title_id, '_cp_post_type_override', '0' );

		// store the option
		$this->option_set( 'cp_welcome_page', $title_id );

		// set WordPress internal page references
		$this->_store_wordpress_option( 'show_on_front', 'page' );
		$this->_store_wordpress_option( 'page_on_front', $title_id );

		// --<
		return $title_id;

	}



	/**
	 * Create "General Comments" page.
	 *
	 * @return int $general_comments_id The numeric ID of the "General Comments" page
	 */
	function _create_general_comments_page() {

		// define general comments page
		$general_comments = array(
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'open',
			'ping_status' => 'open',
			'to_ping' => '', // quick fix for Windows
			'pinged' => '', // quick fix for Windows
			'post_content_filtered' => '', // quick fix for Windows
			'post_excerpt' => '', // quick fix for Windows
			'menu_order' => 0
		);

		// add post-specific stuff

		// default page title
		$title = __( 'General Comments', 'commentpress-core' );

		// set, but allow overrides
		$general_comments['post_title'] = apply_filters( 'cp_general_comments_title', $title );

		// default content
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		// set, but allow overrides
		$general_comments['post_content'] = apply_filters( 'cp_general_comments_content', $content );

		// set template, but allow overrides
		$general_comments['page_template'] = apply_filters( 'cp_general_comments_template', 'comments-general.php' );

		// Insert the post into the database
		$general_comments_id = wp_insert_post( $general_comments );

		// store the option
		$this->option_set( 'cp_general_comments_page', $general_comments_id );

		// --<
		return $general_comments_id;

	}



	/**
	 * Create "all comments" page.
	 *
	 * @return int $all_comments_id The numeric ID of the "All Comments" page
	 */
	function _create_all_comments_page() {

		// define all comments page
		$all_comments = array(
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'to_ping' => '', // quick fix for Windows
			'pinged' => '', // quick fix for Windows
			'post_content_filtered' => '', // quick fix for Windows
			'post_excerpt' => '', // quick fix for Windows
			'menu_order' => 0
		);

		// add post-specific stuff

		// default page title
		$title = __( 'All Comments', 'commentpress-core' );

		// set, but allow overrides
		$all_comments['post_title'] = apply_filters( 'cp_all_comments_title', $title );

		// default content
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		// set, but allow overrides
		$all_comments['post_content'] = apply_filters( 'cp_all_comments_content', $content );

		// set template, but allow overrides
		$all_comments['page_template'] = apply_filters( 'cp_all_comments_template', 'comments-all.php' );

		// Insert the post into the database
		$all_comments_id = wp_insert_post( $all_comments );

		// store the option
		$this->option_set( 'cp_all_comments_page', $all_comments_id );

		// --<
		return $all_comments_id;

	}



	/**
	 * Create "Comments by Author" page.
	 *
	 * @return int $group_id The numeric ID of the "Comments by Author" page
	 */
	function _create_comments_by_author_page() {

		// define comments by author page
		$group = array(
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'to_ping' => '', // quick fix for Windows
			'pinged' => '', // quick fix for Windows
			'post_content_filtered' => '', // quick fix for Windows
			'post_excerpt' => '', // quick fix for Windows
			'menu_order' => 0
		);

		// add post-specific stuff

		// default page title
		$title = __( 'Comments by Commenter', 'commentpress-core' );

		// set, but allow overrides
		$group['post_title'] = apply_filters( 'cp_comments_by_title', $title );

		// default content
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		// set, but allow overrides
		$group['post_content'] = apply_filters( 'cp_comments_by_content', $content );

		// set template, but allow overrides
		$group['page_template'] = apply_filters( 'cp_comments_by_template', 'comments-by.php' );

		// Insert the post into the database
		$group_id = wp_insert_post( $group );

		// store the option
		$this->option_set( 'cp_comments_by_page', $group_id );

		// --<
		return $group_id;

	}



	/**
	 * Create "blog" page.
	 *
	 * @return int $blog_id The numeric ID of the "Blog" page
	 */
	function _create_blog_page() {

		// define blog page
		$blog = array(
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'to_ping' => '', // quick fix for Windows
			'pinged' => '', // quick fix for Windows
			'post_content_filtered' => '', // quick fix for Windows
			'post_excerpt' => '', // quick fix for Windows
			'menu_order' => 0
		);

		// add post-specific stuff

		// default page title
		$title = __( 'Blog', 'commentpress-core' );

		// set, but allow overrides
		$blog['post_title'] = apply_filters( 'cp_blog_page_title', $title );

		// default content
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		// set, but allow overrides
		$blog['post_content'] = apply_filters( 'cp_blog_page_content', $content );

		// set template, but allow overrides
		$blog['page_template'] = apply_filters( 'cp_blog_page_template', 'blog.php' );

		// Insert the post into the database
		$blog_id = wp_insert_post( $blog );

		// store the option
		$this->option_set( 'cp_blog_page', $blog_id );

		// set WordPress internal page reference
		$this->_store_wordpress_option( 'page_for_posts', $blog_id );

		// --<
		return $blog_id;

	}



	/**
	 * Create "Blog Archive" page.
	 *
	 * @return int $blog_id The numeric ID of the "Blog Archive" page
	 */
	function _create_blog_archive_page() {

		// define blog page
		$blog = array(
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'to_ping' => '', // quick fix for Windows
			'pinged' => '', // quick fix for Windows
			'post_content_filtered' => '', // quick fix for Windows
			'post_excerpt' => '', // quick fix for Windows
			'menu_order' => 0
		);

		// add post-specific stuff

		// default page title
		$title = __( 'Blog Archive', 'commentpress-core' );

		// set, but allow overrides
		$blog['post_title'] = apply_filters( 'cp_blog_archive_page_title', $title );

		// default content
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		// set, but allow overrides
		$blog['post_content'] = apply_filters( 'cp_blog_archive_page_content', $content );

		// set template, but allow overrides
		$blog['page_template'] = apply_filters( 'cp_blog_archive_page_template', 'archives.php' );

		// Insert the post into the database
		$blog_id = wp_insert_post( $blog );

		// store the option
		$this->option_set( 'cp_blog_archive_page', $blog_id );

		// --<
		return $blog_id;

	}



	/**
	 * Create "table of contents" page.
	 *
	 * @todo NOT USED
	 *
	 * @return int $toc_id The numeric ID of the "Table of Contents" page
	 */
	function _create_toc_page() {

		// define TOC page
		$toc = array(
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'to_ping' => '', // quick fix for Windows
			'pinged' => '', // quick fix for Windows
			'post_content_filtered' => '', // quick fix for Windows
			'post_excerpt' => '', // quick fix for Windows
			'menu_order' => 0
		);

		// default page title
		$title = __( 'Table of Contents', 'commentpress-core' );

		// set, but allow overrides
		$toc['post_title'] = apply_filters( 'cp_toc_page_title', $title );

		// default content
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		// set, but allow overrides
		$toc['post_content'] = apply_filters( 'cp_toc_page_content', $content );

		// set template, but allow overrides
		$toc['page_template'] = apply_filters( 'cp_toc_page_template', 'toc.php' );

		// Insert the post into the database
		$toc_id = wp_insert_post( $toc );

		// store the option
		$this->option_set( 'cp_toc_page', $toc_id );

		// --<
		return $toc_id;

	}



	/**
	 * Cancels comment paging because CommentPress Core will not work with comment paging.
	 *
	 * @return void
	 */
	function _cancel_comment_paging() {

		// store option
		$this->_store_wordpress_option( 'page_comments', '' );

	}



	/**
	 * Resets comment paging option when plugin is deactivated.
	 *
	 * @return void
	 */
	function _reset_comment_paging() {

		// reset option
		$this->_reset_wordpress_option( 'page_comments' );

	}



	/**
	 * Clears widgets for a fresh start.
	 *
	 * @return void
	 */
	function _clear_widgets() {

		// set backup option
		add_option( 'commentpress_sidebars_widgets', $this->option_wp_get( 'sidebars_widgets' ) );

		// clear them - this array is based on the array in wp_install_defaults()
		update_option( 'sidebars_widgets', array(
			'wp_inactive_widgets' => array(),
			'sidebar-1' => array(),
			'sidebar-2' => array(),
			'sidebar-3' => array(),
			'array_version' => 3
		) );

	}



	/**
	 * Resets widgets when plugin is deactivated.
	 *
	 * @return void
	 */
	function _reset_widgets() {

		// reset option
		$this->_reset_wordpress_option( 'sidebars_widgets' );

	}



	/**
	 * Store WordPress option.
	 *
	 * @param str $name The name of the option
	 * @param mixed $value The value of the option
	 * @return void
	 */
	function _store_wordpress_option( $name, $value ) {

		// set backup option
		add_option( 'commentpress_' . $name, $this->option_wp_get( $name ) );

		// set the WordPress option
		$this->option_wp_set( $name, $value );

	}



	/**
	 * Reset WordPress option.
	 *
	 * @param str $name The name of the option
	 * @return void
	 */
	function _reset_wordpress_option( $name ) {

		// set the WordPress option
		$this->option_wp_set( $name, $this->option_wp_get( 'cp_' . $name ) );

		// remove backup option
		delete_option( 'commentpress_' . $name );

	}



	/**
	 * Create all basic CommentPress Core options.
	 *
	 * @return void
	 */
	function _options_create() {

		// init options array
		$this->commentpress_options = array(
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
		);

		// Paragraph-level comments enabled by default
		add_option( 'commentpress_options', $this->commentpress_options );

	}



	/**
	 * Reset CommentPress Core options.
	 *
	 * @return void
	 */
	function _options_reset() {

		// TOC: show posts by default
		$this->option_set( 'cp_show_posts_or_pages_in_toc', $this->toc_content );

		// TOC: are chapters pages
		$this->option_set( 'cp_toc_chapter_is_page', $this->toc_chapter_is_page );

		// TOC: if pages are shown, show subpages by default
		$this->option_set( 'cp_show_subpages', $this->show_subpages );

		// TOC: show extended post list
		$this->option_set( 'cp_show_extended_toc', $this->show_extended_toc );

		// comment editor
		$this->option_set( 'cp_comment_editor', $this->comment_editor );

		// promote reading or commenting
		$this->option_set( 'cp_promote_reading', $this->promote_reading );

		// show or hide titles
		$this->option_set( 'cp_title_visibility', $this->title_visibility );

		// show or hide page meta
		$this->option_set( 'cp_page_meta_visibility', $this->page_meta_visibility );

		// header background colour
		$this->option_set( 'cp_header_bg_colour', 'deprecated' );

		// js scroll speed
		$this->option_set( 'cp_js_scroll_speed', $this->js_scroll_speed );

		// minimum page width
		$this->option_set( 'cp_min_page_width', $this->min_page_width );

		// "live" comment refeshing
		$this->option_set( 'cp_para_comments_live', $this->para_comments_live );

		// Blog: excerpt length
		$this->option_set( 'cp_excerpt_length', $this->excerpt_length );

		// workflow
		$this->option_set( 'cp_blog_workflow', $this->blog_workflow );

		// blog type
		$this->option_set( 'cp_blog_type', $this->blog_type );

		// default sidebar
		$this->option_set( 'cp_sidebar_default', $this->sidebar_default );

		// featured images
		$this->option_set( 'cp_featured_images', $this->featured_images );

		// textblock meta
		$this->option_set( 'cp_textblock_meta', $this->textblock_meta );

		// page navigation enabled
		$this->option_set( 'cp_page_nav_enabled', $this->page_nav_enabled );

		// do not parse flag
		$this->option_set( 'cp_do_not_parse', $this->do_not_parse );

		// skipped post types
		$this->option_set( 'cp_post_types_disabled', $this->post_types_disabled );

		// store it
		$this->options_save();

	}



	/**
	 * Migrate all CommentPress Core options from old plugin.
	 *
	 * @return void
	 */
	function _options_migrate() {

		// get existing options
		$old = get_option( 'cp_options', array() );

		// ---------------------------------------------------------------------
		// retrieve new ones, if they exist, or use defaults otherwise
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

		// header background colour
		$header_bg_colour =	 			isset( $old['cp_header_bg_colour'] ) ?
										$old['cp_header_bg_colour'] :
										$this->header_bg_colour;

		// if it's the old default, upgrade to new default
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
		// special pages
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

		// init options array
		$this->commentpress_options = array(
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
		);

		// if we have special pages
		if ( ! is_null( $special_pages ) AND is_array( $special_pages ) ) {

			// let's have them as well
			$pages = array(
				'cp_special_pages' => $special_pages,
				'cp_blog_page' => $blog_page,
				'cp_blog_archive_page' => $blog_archive_page,
				'cp_general_comments_page' => $general_comments_page,
				'cp_all_comments_page' => $all_comments_page,
				'cp_comments_by_page' => $comments_by_page,
				'cp_toc_page' => $toc_page
			);

			// merge
			$this->commentpress_options = array_merge( $this->commentpress_options, $pages );

			// access old plugin
			global $commentpress_obj;

			// did we get it?
			if ( is_object( $commentpress_obj ) ) {

				// now delete the old CommentPress options
				$commentpress_obj->db->option_delete( 'cp_special_pages' );
				$commentpress_obj->db->option_delete( 'cp_blog_page' );
				$commentpress_obj->db->option_delete( 'cp_blog_archive_page' );
				$commentpress_obj->db->option_delete( 'cp_general_comments_page' );
				$commentpress_obj->db->option_delete( 'cp_all_comments_page' );
				$commentpress_obj->db->option_delete( 'cp_comments_by_page' );
				$commentpress_obj->db->option_delete( 'cp_toc_page' );

				// save changes
				$commentpress_obj->db->options_save();

				// extra page options: get existing backup, delete it, create new backup

				// backup blog page reference
				$page_for_posts = get_option( 'cp_page_for_posts' );
				delete_option( 'cp_page_for_posts' );
				add_option( 'commentpress_page_for_posts', $page_for_posts );

			}

		}

		// ---------------------------------------------------------------------
		// welcome page
		// ---------------------------------------------------------------------

		// welcome page is a bit of an oddity: deal with it here
		$welcome_page =	isset( $old['cp_welcome_page'] ) ? $old['cp_welcome_page'] : null;

		// did we get a welcome page?
		if ( ! is_null( $welcome_page ) ) {

			// if the custom field already has a value
			if ( get_post_meta( $welcome_page, '_cp_post_type_override', true ) !== '' ) {

				// leave the selected formatter alone

			} else {

				// make sure it has the default formatter (0 = prose)
				add_post_meta( $welcome_page, '_cp_post_type_override', '0' );

			}

			// add it to our options
			$this->commentpress_options['cp_welcome_page'] = $welcome_page;

		}

		// add the options to WordPress
		add_option( 'commentpress_options', $this->commentpress_options );

		// ---------------------------------------------------------------------
		// backups
		// ---------------------------------------------------------------------

		// backup what to show as homepage
		$show_on_front = get_option( 'cp_show_on_front' );
		delete_option( 'cp_show_on_front' );
		add_option( 'commentpress_show_on_front', $show_on_front );

		// backup homepage id
		$page_on_front = get_option( 'cp_page_on_front' );
		delete_option( 'cp_page_on_front' );
		add_option( 'commentpress_page_on_front', $page_on_front );

		// backup comment paging
		$page_comments = get_option( 'cp_page_comments' );
		delete_option( 'cp_page_comments' );
		add_option( 'commentpress_page_comments', $page_comments );

		// ---------------------------------------------------------------------
		// Theme Customizations
		// ---------------------------------------------------------------------

		// migrate Theme Customizations
		$theme_settings = get_option( 'cp_theme_settings', array() );

		// did we get any?
		if ( ! empty( $theme_settings ) ) {

			// migrate them
			add_option( 'commentpress_theme_settings', $theme_settings );

		}

		// migrate Theme Mods
		$theme_mods = get_option( 'theme_mods_commentpress', array() );

		// did we get any?
		if ( is_array( $theme_mods ) AND count( $theme_mods ) > 0 ) {

			// get header background image
			if ( isset( $theme_mods['header_image'] ) ) {

				// is it a CommentPress one?
				if ( strstr( $theme_mods['header_image'], 'style/images/header/caves.jpg' ) !== false ) {

					// point it at the equivalent new version
					$theme_mods['header_image'] = COMMENTPRESS_PLUGIN_URL .
												  'themes/commentpress-theme' .
												  '/assets/images/header/caves-green.jpg';

				}

			}

			/*
			// if we wanted to clear widgets
			if ( isset( $theme_mods['sidebars_widgets'] ) ) {

				// remove them
				unset( $theme_mods['sidebars_widgets'] );

			}

			// override widgets
			$this->_clear_widgets();
			*/

			// get current theme
			$theme = wp_get_theme();

			// get current theme slug
			$theme_slug = $theme->get_stylesheet();

			// update theme mods (will create if it doesn't exist)
			update_option( 'theme_mods_' . $theme_slug, $theme_mods );

		}

		// update header background colour
		set_theme_mod( 'commentpress_header_bg_color', '#' . $header_bg_colour );

		// ---------------------------------------------------------------------
		// deactivate old CommentPress and CommentPress Ajaxified
		// ---------------------------------------------------------------------

		// get old CommentPress Ajaxified
		$cpajax_old = commentpress_find_plugin_by_name( 'Commentpress Ajaxified' );
		if ( $cpajax_old AND is_plugin_active( $cpajax_old ) ) { deactivate_plugins( $cpajax_old ); }

		// get old CommentPress
		$cp_old = commentpress_find_plugin_by_name( 'Commentpress' );
		if ( $cp_old AND is_plugin_active( $cp_old ) ) { deactivate_plugins( $cp_old ); }

	}



	/**
	 * Upgrade CommentPress options to array (only for pre-CommentPress 3.2 upgrades).
	 *
	 * @return void
	 */
	function _options_upgrade() {

		// populate options array with current values
		$this->commentpress_options = array(

			// theme settings we want to keep
			'cp_show_posts_or_pages_in_toc' => $this->option_wp_get( 'cp_show_posts_or_pages_in_toc' ),
			'cp_toc_chapter_is_page' => $this->option_wp_get( 'cp_toc_chapter_is_page'),
			'cp_show_subpages' => $this->option_wp_get( 'cp_show_subpages'),
			'cp_excerpt_length' => $this->option_wp_get( 'cp_excerpt_length'),

			// migrate special pages
			'cp_special_pages' => $this->option_wp_get( 'cp_special_pages'),
			'cp_welcome_page' => $this->option_wp_get( 'cp_welcome_page'),
			'cp_general_comments_page' => $this->option_wp_get( 'cp_general_comments_page'),
			'cp_all_comments_page' => $this->option_wp_get( 'cp_all_comments_page'),
			'cp_comments_by_page' => $this->option_wp_get( 'cp_comments_by_page'),
			'cp_blog_page' => $this->option_wp_get( 'cp_blog_page'),

			// store setting for what was independently set by the ajax commenting plugin, "off" by default
			'cp_para_comments_live' => $this->para_comments_live

		);

		// save options array
		$this->options_save();

		// delete all old options
		$this->_options_delete_legacy();

	}



	/**
	 * Delete all legacy CommentPress options.
	 *
	 * @return void
	 */
	function _options_delete_legacy() {

		// delete paragraph-level commenting option
		delete_option( 'cp_para_comments_enabled' );

		// delete TOC options
		delete_option( 'cp_show_posts_or_pages_in_toc' );
		delete_option( 'cp_show_subpages' );
		delete_option( 'cp_toc_chapter_is_page' );

		// delete comment editor
		delete_option( 'cp_comment_editor' );

		// promote reading or commenting
		delete_option( 'cp_promote_reading' );

		// show or hide titles
		delete_option( 'cp_title_visibility' );

		// header bg colour
		delete_option( 'cp_header_bg_colour' );

		// header bg colour
		delete_option( 'cp_js_scroll_speed' );

		// header bg colour
		delete_option( 'cp_min_page_width' );

		// delete skin
		delete_option( 'cp_default_skin' );

		// window appearance options
		delete_option( 'cp_default_left_position' );
		delete_option( 'cp_default_top_position' );
		delete_option( 'cp_default_width' );
		delete_option( 'cp_default_height' );

		// window behaviour options
		delete_option( 'cp_allow_users_to_iconize' );
		delete_option( 'cp_allow_users_to_minimize' );
		delete_option( 'cp_allow_users_to_resize' );
		delete_option( 'cp_allow_users_to_drag' );
		delete_option( 'cp_allow_users_to_save_position' );

		// blog options
		delete_option( 'cp_excerpt_length' );

		// "live" comment refreshing
		delete_option( 'cp_para_comments_live' );

		// special pages options
		delete_option( 'cp_special_pages' );
		delete_option( 'cp_welcome_page' );
		delete_option( 'cp_general_comments_page' );
		delete_option( 'cp_all_comments_page' );
		delete_option( 'cp_comments_by_page' );
		delete_option( 'cp_blog_page' );

	}



//##############################################################################



} // class ends



