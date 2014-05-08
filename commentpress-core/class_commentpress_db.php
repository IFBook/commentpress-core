<?php /*
================================================================================
Class CommentpressCoreDatabase
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This class is a wrapper for the majority of database operations.

--------------------------------------------------------------------------------
*/






/*
================================================================================
Class Name
================================================================================
*/

class CommentpressCoreDatabase {






	/*
	============================================================================
	Properties
	============================================================================
	*/
	
	// parent object reference
	public $parent_obj;
	
	// ---------- options ----------
	public $commentpress_options = array();
	
	// TOC content ('post' or 'page')
	public $toc_content = 'page';
	
	// TOC chapters are pages by default
	public $toc_chapter_is_page = 1;

	// Show extended TOC content for posts lists
	public $show_extended_toc = 1;
		
	// TOC shows subpages by default
	public $show_subpages = 1;
	
	// show page titles by default
	public $title_visibility = 'show';
	
	// hide page meta by default
	public $page_meta_visibility = 'hide';
	
	// default editor (tinyMCE)
	public $comment_editor = 1;
	
	// promote reading (1) or commenting (0)
	public $promote_reading = 0;
	
	// default excerpt length
	public $excerpt_length = 55;
	
	// default header background colour (hex, same as in layout.css)
	public $header_bg_colour = '2c2622';
	
	// default scroll speed (ms)
	public $js_scroll_speed = '800';
	
	// default type of blog - eg, array('0' => 'Poetry','1' => 'Prose')
	public $blog_type = false;
		
	// default blog workflow (like translation, for example), off by default
	public $blog_workflow = 0;
	
	// default sidebar ('toc' => Contents tab)
	public $sidebar_default = 'toc';
		
	// default minimum page width (px)
	public $min_page_width = '447';
		
	// "live" comment refreshing off by default
	public $para_comments_live = 0;
		
	// prevent save_post hook firing more than once
	public $saved_post = false;
	
	// featured images off by default
	public $featured_images = 'n';
	
	// show textblock meta by default
	public $textblock_meta = 'y';
	






	/** 
	 * @description: initialises this object
	 * @param object $parent_obj a reference to the parent object
	 * @return object
	 * @todo: 
	 *
	 */
	function __construct( $parent_obj ) {
	
		// store reference to parent
		$this->parent_obj = $parent_obj;
	
		// init
		$this->_init();

		// --<
		return $this;

	}






	/** 
	 * @description: set up all items associated with this object
	 * @todo: 
	 *
	 */
	public function activate() {
		
		// have we already got a modified database?
		$modified = $this->db_is_modified( 'comment_text_signature' ) ? 'y' : 'n';
		
		// if  we have an existing comment_text_signature column...
		if ( $modified == 'y' ) {
		
			// upgrade old Commentpress schema to new
			if ( !$this->schema_upgrade() ) {
			
				// kill plugin activation
				_cpdie( 'CommentPress Core Error: could not upgrade the database' );
				
			}
			
		} else {
			
			// update db schema
			$this->schema_update();
			
		}
		
		
		
		// test if we have our version
		if ( !$this->option_wp_get( 'commentpress_version' ) ) {
		
			// store CommentPress Core version
			$this->option_wp_set( 'commentpress_version', COMMENTPRESS_VERSION );
		
		}
		
		
		
		// test that we aren't reactivating
		if ( !$this->option_wp_get( 'commentpress_options' ) ) {
			
			// test if we have a existing pre-3.4 Commentpress instance
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

		// if we haven't created any...
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
	 * @description: upgrade Commentpress plugin from 3.1 options to latest set
	 * @return boolean $result
	 * @todo: 
	 *
	 */
	public function upgrade() {
		
		// init return
		$result = false;



		// if we have a commentpress install (or we're forcing)
		if ( $this->check_upgrade() ) {
		


			// are we missing the commentpress_options option?
			if ( !$this->option_wp_exists( 'commentpress_options' ) ) {
			
				// upgrade to the single array
				$this->_options_upgrade();
			
			}
			
			
			
			// checkboxes send no value if not checked, so use a default
			$cp_blog_workflow = $this->blog_workflow;
			
			// default blog type
			$cp_blog_type = $this->blog_type;
			
			
			
			// get variables
			extract( $_POST );
			
			
			
			// New in CP 3.5.9 - textblock meta can be hidden
			if ( !$this->option_exists( 'cp_textblock_meta' ) ) {
	
				// get choice
				$_choice = esc_sql( $cp_textblock_meta );
			
				// add chosen featured images option
				$this->option_set( 'cp_textblock_meta', $_choice );
				
			}
			
			
			
			// New in CP 3.5.4 - featured image capabilities
			if ( !$this->option_exists( 'cp_featured_images' ) ) {
	
				// get choice
				$_choice = esc_sql( $cp_featured_images );
			
				// add chosen featured images option
				$this->option_set( 'cp_featured_images', $_choice );
				
			}
			
			
			
			// Removed in CP 3.4 - do we still have the legacy cp_para_comments_enabled option?
			if ( $this->option_exists( 'cp_para_comments_enabled' ) ) {
			
				// delete old cp_para_comments_enabled option
				$this->option_delete( 'cp_para_comments_enabled' );
				
			}
			


			// Removed in CP 3.4 - do we still have the legacy cp_minimise_sidebar option?
			if ( $this->option_exists( 'cp_minimise_sidebar' ) ) {
			
				// delete old cp_minimise_sidebar option
				$this->option_delete( 'cp_minimise_sidebar' );
				
			}
			


			// New in CP 3.4 - has AJAX "live" comment refreshing been migrated?
			if ( !$this->option_exists( 'cp_para_comments_live' ) ) {
	
				// "live" comment refreshing, off by default
				$this->option_set( 'cp_para_comments_live', $this->para_comments_live );
				
			}
			
			
			
			// New in CP 3.3.3 - changed the way the welcome page works
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
			


			// New in CP 3.3.3 - are we missing the cp_sidebar_default option?
			if ( !$this->option_exists( 'cp_sidebar_default' ) ) {
			
				// get choice
				$_choice = esc_sql( $cp_sidebar_default );
			
				// add chosen cp_page_meta_visibility option
				$this->option_set( 'cp_sidebar_default', $_choice );
				
			}
			


			// New in CP 3.3.2 - are we missing the cp_page_meta_visibility option?
			if ( !$this->option_exists( 'cp_page_meta_visibility' ) ) {
			
				// get choice
				$_choice = esc_sql( $cp_page_meta_visibility );
			
				// add chosen cp_page_meta_visibility option
				$this->option_set( 'cp_page_meta_visibility', $_choice );
				
			}
			


			// New in CP 3.3.1 - are we missing the cp_blog_workflow option?
			if ( !$this->option_exists( 'cp_blog_workflow' ) ) {
			
				// get choice
				$_choice = esc_sql( $cp_blog_workflow );
			
				// add chosen cp_blog_workflow option
				$this->option_set( 'cp_blog_workflow', $_choice );
				
			}
			


			// New in CP 3.3.1 - are we missing the cp_blog_type option?
			if ( !$this->option_exists( 'cp_blog_type' ) ) {
			
				// get choice
				$_choice = esc_sql( $cp_blog_type );
			
				// add chosen cp_blog_type option
				$this->option_set( 'cp_blog_type', $_choice );
				
			}
			


			// New in CP 3.3 - are we missing the cp_show_extended_toc option?
			if ( !$this->option_exists( 'cp_show_extended_toc' ) ) {
			
				// get choice
				$_choice = esc_sql( $cp_show_extended_toc );
			
				// add chosen cp_show_extended_toc option
				$this->option_set( 'cp_show_extended_toc', $_choice );
				
			}
			


			// are we missing the cp_comment_editor option?
			if ( !$this->option_exists( 'cp_comment_editor' ) ) {
			
				// get choice
				$_choice = esc_sql( $cp_comment_editor );
			
				// add chosen cp_comment_editor option
				$this->option_set( 'cp_comment_editor', $_choice );
				
			}
			


			// are we missing the cp_promote_reading option?
			if ( !$this->option_exists( 'cp_promote_reading' ) ) {
			
				// get choice
				$_choice = esc_sql( $cp_promote_reading );
			
				// add chosen cp_promote_reading option
				$this->option_set( 'cp_promote_reading', $_choice );
				
			}
			


			// are we missing the cp_title_visibility option?
			if ( !$this->option_exists( 'cp_title_visibility' ) ) {
			
				// get choice
				$_choice = esc_sql( $cp_title_visibility );
			
				// add chosen cp_title_visibility option
				$this->option_set( 'cp_title_visibility', $_choice );
				
			}
			


			// are we missing the cp_header_bg_colour option?
			if ( !$this->option_exists( 'cp_header_bg_colour' ) ) {
			
				// get choice
				$_choice = esc_sql( $cp_header_bg_colour );
			
				// strip our rgb #
				if ( stristr( $_choice, '#' ) ) {
					$_choice = substr( $_choice, 1 );
				}
				
				// reset to default if blank
				if ( $_choice == '' ) {
					$_choice = $this->header_bg_colour;
				}
				
				// add chosen cp_header_bg_colour option
				$this->option_set( 'cp_header_bg_colour', $_choice );
				
			}
			


			// are we missing the cp_js_scroll_speed option?
			if ( !$this->option_exists( 'cp_js_scroll_speed' ) ) {
			
				// get choice
				$_choice = esc_sql( $cp_js_scroll_speed );
			
				// add chosen cp_js_scroll_speed option
				$this->option_set( 'cp_js_scroll_speed', $_choice );
				
			}
			


			// are we missing the cp_min_page_width option?
			if ( !$this->option_exists( 'cp_min_page_width' ) ) {
			
				// get choice
				$_choice = esc_sql( $cp_min_page_width );
			
				// add chosen cp_min_page_width option
				$this->option_set( 'cp_min_page_width', $_choice );
				
			}
			


			// do we still have the legacy cp_allow_users_to_minimize option?
			if ( $this->option_exists( 'cp_allow_users_to_minimize' ) ) {
			
				// delete old cp_allow_users_to_minimize option
				$this->option_delete( 'cp_allow_users_to_minimize' );
				
			}
			


			// do we have special pages?
			if ( $this->option_exists( 'cp_special_pages' ) ) {
			
				// if we don't have the toc page...
				if ( !$this->option_exists( 'cp_toc_page' ) ) {
				
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
	 * @description: reset Wordpress to prior state, but retain options
	 * @todo: 
	 *
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







	/*
	============================================================================
	PUBLIC METHODS
	============================================================================
	*/
	




	/** 
	 * @description: update Wordpress database schema
	 * @return boolean $result
	 * @todo: 
	 *
	 */
	public function schema_update() {
		
		// database object
		global $wpdb;
		


		// include Wordpress upgrade script
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
	 * @description: upgrade Wordpress database schema
	 * @return boolean $result
	 * @todo: 
	 *
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
		
		// test if we now have the correct column name...
		if ( $this->db_is_modified( 'comment_signature' ) ) {
			
			// yes
			$result = true;
		
		}
		
		// --<
		return $result;
	}
	
	
	
	
	


	/** 
	 * @description: do we have a column in the comments table?
	 * @return boolean $result
	 * @todo: 
	 *
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
	 * @description: check for plugin upgrade
	 * @return boolean $result
	 * @todo: 
	 *
	 */
	public function check_upgrade() {
	
		// init
		$result = false;
		
		// get installed version cast as string
		$_version = (string) $this->option_wp_get( 'commentpress_version' );
		
		// if we have a commentpress install and it's lower than this one
		if ( $_version !== false AND version_compare( COMMENTPRESS_VERSION, $_version, '>' ) ) {
			
			// check whether any options need to be shown
			if ( $this->check_upgrade_options() ) {
			
				// override
				$result = true;
			
			}

		}
		


		// --<
		return $result;

	}
	
	
	
	
	


	/** 
	 * @description: check for options added in this plugin upgrade
	 * @return boolean $result
	 * @todo: 
	 *
	 */
	public function check_upgrade_options() {
	
		// do we have the option to choose to hide textblock meta (new in 3.5.9)?
		if ( !$this->option_exists( 'cp_textblock_meta' ) ) { return true; }
		
		// do we have the option to choose featured images (new in 3.5.4)?
		if ( !$this->option_exists( 'cp_featured_images' ) ) { return true; }
		
		// do we have the option to choose the default sidebar (new in 3.3.3)?
		if ( !$this->option_exists( 'cp_sidebar_default' ) ) { return true; }
		
		// do we have the option to show or hide page meta (new in 3.3.2)?
		if ( !$this->option_exists( 'cp_page_meta_visibility' ) ) { return true; }
		
		// do we have the option to choose blog type (new in 3.3.1)?
		if ( !$this->option_exists( 'cp_blog_type' ) ) { return true; }
		
		// do we have the option to choose blog workflow (new in 3.3.1)?
		if ( !$this->option_exists( 'cp_blog_workflow' ) ) { return true; }
		
		// do we have the option to choose the TOC layout (new in 3.3)?
		if ( !$this->option_exists( 'cp_show_extended_toc' ) ) { return true; }
		
		// do we have the option to set the comment editor?
		if ( !$this->option_exists( 'cp_comment_editor' ) ) { return true; }
		
		// do we have the option to set the default behaviour?
		if ( !$this->option_exists( 'cp_promote_reading' ) ) { return true; }
		
		// do we have the option to show or hide titles?
		if ( !$this->option_exists( 'cp_title_visibility' ) ) { return true; }
		
		// do we have the option to set the header bg colour?
		if ( !$this->option_exists( 'cp_header_bg_colour' ) ) { return true; }
		
		// do we have the option to set the scroll speed?
		if ( !$this->option_exists( 'cp_js_scroll_speed' ) ) { return true; }
		
		// do we have the option to set the minimum page width?
		if ( !$this->option_exists( 'cp_min_page_width' ) ) { return true; }
		


		// --<
		return false;

	}
	
	
	
	
	


	/** 
	 * @description: save the settings set by the administrator
	 * @return boolean success or failure
	 * @todo: do more error checking?
	 *
	 */
	public function options_update() {
	
		// init result
		$result = false;
		


	 	// was the form submitted?
		if( isset( $_POST[ 'commentpress_submit' ] ) ) {
			


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
			$cp_sidebar_default = 'comments';
			$cp_featured_images = 'n';
			$cp_textblock_meta = 'y';
			
			
			
			// get variables
			extract( $_POST );
			
			
			
			// hand off to Multisite first, in case we're deactivating
			do_action( 'cpmu_deactivate_commentpress' );
			
			// is Multisite activating CommentPress Core?
			if ( $cp_activate == '1' ) { return true; }
			
			
			
			// did we ask to upgrade CommentPress Core?
			if ( $cp_upgrade == '1' ) {
			
				// do upgrade
				$this->upgrade();
				
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
			
			// if we have pages in TOC and a value for the next param...
			if ( $cp_show_posts_or_pages_in_toc == 'page' AND isset( $cp_toc_chapter_is_page ) ) {
				
				$cp_toc_chapter_is_page = esc_sql( $cp_toc_chapter_is_page );
				$this->option_set( 'cp_toc_chapter_is_page', $cp_toc_chapter_is_page );
				
				// if chapters are not pages and we have a value for the next param...
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
			
			// header background colour
			
			// strip our rgb #
			if ( stristr( $cp_header_bg_colour, '#' ) ) {
				$cp_header_bg_colour = substr( $cp_header_bg_colour, 1 );
			}
			
			// reset to default if blank
			if ( $cp_header_bg_colour == '' ) {
				$cp_header_bg_colour = $this->header_bg_colour;
			}
			
			// save it
			$cp_header_bg_colour = esc_sql( $cp_header_bg_colour );
			$this->option_set( 'cp_header_bg_colour', $cp_header_bg_colour );
			
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
				
					// allow plugins to override the blog type - for example if workflow is enabled, 
					// it might become a new blog type as far as buddypress is concerned
					$_blog_type = apply_filters( 'cp_get_group_meta_for_blog_type', $cp_blog_type, $cp_blog_workflow );
	
					// set the type as group meta info
					groups_update_groupmeta( $group_id, 'groupblogtype', 'groupblogtype-'.$_blog_type );
					
				}
			
			}

			// save default sidebar
			$cp_sidebar_default = esc_sql( $cp_sidebar_default );
			$this->option_set( 'cp_sidebar_default', $cp_sidebar_default );
			
			// save featured images
			$cp_featured_images = esc_sql( $cp_featured_images );
			$this->option_set( 'cp_featured_images', $cp_featured_images );
			
			// save textblock meta
			$cp_textblock_meta = esc_sql( $cp_textblock_meta );
			$this->option_set( 'cp_textblock_meta', $cp_textblock_meta );
			


			// save
			$this->options_save();
			
			

			// set flag
			$result = true;
	
		}
		
		
		
		// --<
		return $result;
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: upgrade CommentPress Core options to array
	 * @todo: 
	 *
	 */
	public function options_save() {
		
		// set option
		return $this->option_wp_set( 'commentpress_options', $this->commentpress_options );
		
	}
	
	
	
	
	


	/** 
	 * @description: return a value for a specified option
	 * @todo: 
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
	 * @description: return a value for a specified option
	 * @todo: 
	 */
	public function option_get( $option_name = '', $default = false ) {
	
		// test for null
		if ( $option_name == '' ) {
		
			// oops
			die( __( 'You must supply an option to option_get()', 'commentpress-core' ) );
		
		}
	
		// get option
		return ( array_key_exists( $option_name, $this->commentpress_options ) ) ? $this->commentpress_options[ $option_name ] : $default;
		
	}
	
	
	
	
	
	
	/** 
	 * @description: sets a value for a specified option
	 * @todo: 
	 */
	public function option_set( $option_name = '', $value = '' ) {
	
		// test for null
		if ( $option_name == '' ) {
		
			// oops
			die( __( 'You must supply an option to option_set()', 'commentpress-core' ) );
		
		}
	
		// test for other than string
		if ( !is_string( $option_name ) ) {
		
			// oops
			die( __( 'You must supply the option as a string to option_set()', 'commentpress-core' ) );
		
		}
	
		// set option
		$this->commentpress_options[ $option_name ] = $value;
		
	}
	
	
	
	
	
	
	/** 
	 * @description: deletes a specified option
	 * @todo: 
	 */
	public function option_delete( $option_name = '' ) {
	
		// test for null
		if ( $option_name == '' ) {
		
			// oops
			die( __( 'You must supply an option to option_delete()', 'commentpress-core' ) );
		
		}
	
		// unset option
		unset( $this->commentpress_options[ $option_name ] );
		
	}
	
	
	
	
	
	
	/** 
	 * @description: return a value for a specified option
	 * @todo: 
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
	 * @description: return a value for a specified option
	 * @todo: 
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
	 * @description: sets a value for a specified option
	 * @todo: 
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
	 * @description: get default header bg colour
	 * @todo: 
	 */
	public function option_get_header_bg() {
	
		// test for option
		if ( $this->option_exists( 'cp_header_bg_colour' ) ) {
		
			// --<
			return $this->option_get( 'cp_header_bg_colour' );
			
		} else {
		
			// --<
			return $this->header_bg_colour;
			
		}
	
	}
	
	
	
	
	
	
	/** 
	 * @description: when a page is saved, this also saves the CP options
	 * @param object $post_obj the post object
	 * @return boolean $result
	 * @todo: 
	 *
	 */
	public function save_meta( $post_obj ) {
	
		// if no post, kick out
		if ( !$post_obj ) { return; }
		
		// if page...
		if ( $post_obj->post_type == 'page' ) {
		
			$this->save_page_meta( $post_obj );
		
		}
		
		// if post...
		if ( $post_obj->post_type == 'post' ) {
		
			$this->save_post_meta( $post_obj );
		
		}
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: when a page is saved, this also saves the CP options
	 * @param object $post_obj the post object
	 * @return boolean $result
	 * @todo: 
	 *
	 */
	public function save_page_meta( $post_obj ) {
		
		//print_r( 'data: '.$_data ); die();
		//print_r( '$post_obj->post_type: '.$post_obj->post_type ); die();
		//print_r( '$post_obj->ID: '.$post_obj->ID ); die();
		
		// if no post, kick out
		if ( !$post_obj ) { return; }
		
		// if not page, kick out
		if ( $post_obj->post_type != 'page' ) { return; }
		
		
		
		// authenticate
		$_nonce = isset( $_POST['commentpress_nonce'] ) ? $_POST['commentpress_nonce'] : '';
		if ( !wp_verify_nonce( $_nonce, 'commentpress_page_settings' ) ) { return; }
		
		// is this an auto save routine?
		if ( defined('DOING_AUTOSAVE') AND DOING_AUTOSAVE ) { return; }
		
		// check permissions - 'edit_pages' is available to editor+
		if ( !current_user_can( 'edit_pages' ) ) { return; }
		

		
		// OK, we're authenticated
		
		
		
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
		


		// ---------------------------------------------------------------------
		// Show or Hide Page Meta
		// ---------------------------------------------------------------------
		
		// find and save the data
		$_data = ( isset( $_POST['cp_page_meta_visibility'] ) ) ? $_POST['cp_page_meta_visibility'] : 'hide';

		//print_r( '$_data: '.$_data ); die();
		//print_r( $post ); die();

		// set key
		$key = '_cp_page_meta_visibility';
		
		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
		
			// if empty string...
			if ( $_data === '' ) {
		
				// delete the meta_key
				delete_post_meta( $post->ID, $key );
			
			} else {
			
				// update the data
				update_post_meta( $post->ID, $key, esc_sql( $_data ) );
				
			}
			
		} else {
		
			// add the data
			add_post_meta( $post->ID, $key, esc_sql( $_data ) );
			
		}



		// ---------------------------------------------------------------------
		// Show or Hide Page Title
		// ---------------------------------------------------------------------
		
		// find and save the data
		$_data = ( isset( $_POST['cp_title_visibility'] ) ) ? $_POST['cp_title_visibility'] : 'show';

		//print_r( '$_data: '.$_data ); die();
		//print_r( $post ); die();

		// set key
		$key = '_cp_title_visibility';
		
		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
		
			// if empty string...
			if ( $_data === '' ) {
		
				// delete the meta_key
				delete_post_meta( $post->ID, $key );
			
			} else {
			
				// update the data
				update_post_meta( $post->ID, $key, esc_sql( $_data ) );
				
			}
			
		} else {
		
			// add the data
			add_post_meta( $post->ID, $key, esc_sql( $_data ) );
			
		}



		// ---------------------------------------------------------------------
		// Page Numbering - only first top level page is allowed to send this
		// ---------------------------------------------------------------------
		
		// was the value sent?
		if ( isset( $_POST['cp_number_format'] ) ) {
		
			// set meta key
			$key = '_cp_number_format';
			
			if ( 
				
				// do we need to check this, since only the first top level page
				// can now send this data? doesn't hurt to validate, I guess.
				$post->post_parent == '0' AND 
				!$this->is_special_page() AND 
				$post->ID == $this->parent_obj->nav->get_first_page() 
				
			) { // -->
	
				// get the data
				$_data = $_POST['cp_number_format'];
		
				//print_r( $post->ID ); die();
				
				// if the custom field already has a value...
				if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
				
					// if empty string...
					if ( $_data === '' ) {
				
						// delete the meta_key
						delete_post_meta( $post->ID, $key );
					
					} else {
					
						// update the data
						update_post_meta( $post->ID, $key, esc_sql( $_data ) );
						
					}
					
				} else {
				
					// add the data
					add_post_meta( $post->ID, $key, esc_sql( $_data ) );
					
				}

			}
			
			// delete this meta value from all other pages, because we may have altered
			// the relationship between pages, thus causing the page numbering to fail
			
			// get all pages including chapters
			$all_pages = $this->parent_obj->nav->get_book_pages( 'structural' );
			
			// if we have any pages...
			if ( count( $all_pages ) > 0 ) {
			
				// loop
				foreach( $all_pages AS $_page ) {
				
					// exclude first top level page
					if ( $post->ID != $_page->ID ) {
				
						// delete the meta value
						delete_post_meta( $_page->ID, $key );
					
					}
				
				}
			
			}
			
		}
		
		
		
		// ---------------------------------------------------------------------
		// Page Layout for Title Page -> to allow for Book Cover image
		// ---------------------------------------------------------------------
		
		// is this the title page?
		if ( $post->ID == $this->option_get( 'cp_welcome_page' ) ) {
		
			// find and save the data
			$_data = ( isset( $_POST['cp_page_layout'] ) ) ? $_POST['cp_page_layout'] : 'text';
	
			// set key
			$key = '_cp_page_layout';
			
			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
			
				// if empty string...
				if ( $_data === '' ) {
			
					// delete the meta_key
					delete_post_meta( $post->ID, $key );
				
				} else {
				
					// update the data
					update_post_meta( $post->ID, $key, esc_sql( $_data ) );
					
				}
				
			} else {
			
				// add the data
				add_post_meta( $post->ID, $key, esc_sql( $_data ) );
				
			}
			
		}



		// ---------------------------------------------------------------------
		// Override post formatter (override blog_type)
		// ---------------------------------------------------------------------
		
		// get the data
		$_data = ( isset( $_POST['cp_post_type_override'] ) ) ? $_POST['cp_post_type_override'] : '';

		//print_r( '$_data: '.$_data ); die();
		//print_r( $post ); die();

		// set key
		$key = '_cp_post_type_override';
		
		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
		
			// if empty string...
			if ( $_data === '' ) {
		
				// delete the meta_key
				delete_post_meta( $post->ID, $key );
			
			} else {
			
				// update the data
				update_post_meta( $post->ID, $key, esc_sql( $_data ) );
				
			}
			
		} else {
		
			// add the data
			add_post_meta( $post->ID, $key, esc_sql( $_data ) );
			
		}



		// ---------------------------------------------------------------------
		// Default Sidebar
		// ---------------------------------------------------------------------
		
		// do we have the option to choose the default sidebar (new in 3.3.3)?
		if ( $this->option_exists( 'cp_sidebar_default' ) ) {
		
			// find and save the data
			$_data = ( isset( $_POST['cp_sidebar_default'] ) ) ? 
					 $_POST['cp_sidebar_default'] : 
					 $this->db->option_get( 'cp_sidebar_default' );
	
			//print_r( '$_data: '.$_data ); die();
			//print_r( $post ); die();
	
			// set key
			$key = '_cp_sidebar_default';
			
			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
			
				// if empty string...
				if ( $_data === '' ) {
			
					// delete the meta_key
					delete_post_meta( $post->ID, $key );
				
				} else {
				
					// update the data
					update_post_meta( $post->ID, $key, esc_sql( $_data ) );
					
				}
				
			} else {
			
				// add the data
				add_post_meta( $post->ID, $key, esc_sql( $_data ) );
				
			}
			
		}



		// ---------------------------------------------------------------------
		// Starting Paragraph Number - meta only exists when not default value
		// ---------------------------------------------------------------------
		
		// get the data
		$_data = ( isset( $_POST['cp_starting_para_number'] ) ) ? $_POST['cp_starting_para_number'] : 1;
		
		// if not numeric, set to default
		if ( ! is_numeric( $_data ) ) { $_data = 1; }
		
		// sanitize it
		$_data = absint( $_data );

		// set key
		$key = '_cp_starting_para_number';
		
		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
		
			// if default...
			if ( $_data === 1 ) {
		
				// delete the meta_key
				delete_post_meta( $post->ID, $key );
			
			} else {
			
				// update the data
				update_post_meta( $post->ID, $key, esc_sql( $_data ) );
				
			}
			
		} else {
		
			// if greater than default...
			if ( $_data > 1 ) {
		
				// add the data
				add_post_meta( $post->ID, $key, esc_sql( $_data ) );
			
			}
			
		}
		


		// ---------------------------------------------------------------------
		// Workflow
		// ---------------------------------------------------------------------
		
		// do we have the option to set workflow (new in 3.3.1)?
		if ( $this->option_exists( 'cp_blog_workflow' ) ) {
		
			// get workflow setting for the blog
			$_workflow = $this->option_get( 'cp_blog_workflow' );
			
			/*
			// ----------------
			// WORK IN PROGRESS
			
			// set key
			$key = '_cp_blog_workflow_override';
			
			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
			
				// get existing value
				$_workflow = get_post_meta( $post->ID, $key, true );
				
			}
			// ----------------
			*/
			
			// if it's enabled...
			if ( $_workflow == '1' ) {
			
				// notify plugins that workflow stuff needs saving
				do_action( 'cp_workflow_save_page', $post );
			
			}
			
			/*
			// ----------------
			// WORK IN PROGRESS
			
			// get the setting for the post (we do this after saving the extra
			// post data because 
			$_formatter = ( isset( $_POST['cp_post_type_override'] ) ) ? $_POST['cp_post_type_override'] : '';
	
			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
			
				// if empty string...
				if ( $_data === '' ) {
			
					// delete the meta_key
					delete_post_meta( $post->ID, $key );
				
				} else {
				
					// update the data
					update_post_meta( $post->ID, $key, esc_sql( $_data ) );
					
				}
				
			} else {
			
				// add the data
				add_post_meta( $post->ID, $key, esc_sql( $_data ) );
				
			}
			// ----------------
			*/
			
		}
		


	}
	
	
	
	
	


	/** 
	 * @description: when a post is saved, this also saves the CP options
	 * @param object $post_obj the post object
	 * @return boolean $result
	 * @todo: 
	 *
	 */
	public function save_post_meta( $post_obj ) {
		
		//print_r( 'data: '.$_data ); die();
		//print_r( '$post_obj->post_type: '.$post_obj->post_type ); die();
		//print_r( '$post_obj->ID: '.$post_obj->ID ); die();
		
		// if no post, kick out
		if ( !$post_obj ) { return; }
		
		// if not page, kick out
		if ( $post_obj->post_type != 'post' ) { return; }
		
		
		
		// authenticate
		$_nonce = isset( $_POST['commentpress_nonce'] ) ? $_POST['commentpress_nonce'] : '';
		if ( !wp_verify_nonce( $_nonce, 'commentpress_post_settings' ) ) { return; }
		
		// is this an auto save routine?
		if ( defined('DOING_AUTOSAVE') AND DOING_AUTOSAVE ) { return; }
		
		// check permissions - 'edit_posts' is available to contributor+
		if ( !current_user_can( 'edit_posts', $post_obj->ID ) ) { return; }
		

		
		// OK, we're authenticated
		
		
		
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
		


		// ---------------------------------------------------------------------
		// Override post formatter (override blog_type)
		// ---------------------------------------------------------------------
		
		// get the data
		$_formatter = ( isset( $_POST['cp_post_type_override'] ) ) ? $_POST['cp_post_type_override'] : '';

		//print_r( '$_data: '.$_data ); die();
		//print_r( $post ); die();

		// set key
		$key = '_cp_post_type_override';
		
		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
		
			// if empty string...
			if ( $_formatter === '' ) {
		
				// delete the meta_key
				delete_post_meta( $post->ID, $key );
			
			} else {
			
				// update the data
				update_post_meta( $post->ID, $key, esc_sql( $_formatter ) );
				
			}
			
		} else {
		
			// add the data
			add_post_meta( $post->ID, $key, esc_sql( $_formatter ) );
			
		}



		// ---------------------------------------------------------------------
		// Workflow
		// ---------------------------------------------------------------------
		
		// do we have the option to set workflow (new in 3.3.1)?
		if ( $this->option_exists( 'cp_blog_workflow' ) ) {
		
			// get workflow setting for the blog
			$_workflow = $this->option_get( 'cp_blog_workflow' );
			
			/*
			// ----------------
			// WORK IN PROGRESS
			
			// set key
			$key = '_cp_blog_workflow_override';
			
			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
			
				// get existing value
				$_workflow = get_post_meta( $post->ID, $key, true );
				
			}
			// ----------------
			*/
			
			// if it's enabled for the blog or the post...
			if ( $_workflow == '1' ) {
			
				// notify plugins that workflow stuff needs saving
				do_action( 'cp_workflow_save_post', $post );
			
			}
			
			/*
			// ----------------
			// WORK IN PROGRESS
			
			// get the setting for the post (we do this after saving the extra
			// post data because 
			$_formatter = ( isset( $_POST['cp_post_type_override'] ) ) ? $_POST['cp_post_type_override'] : '';
	
			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
			
				// if empty string...
				if ( $_data === '' ) {
			
					// delete the meta_key
					delete_post_meta( $post->ID, $key );
				
				} else {
				
					// update the data
					update_post_meta( $post->ID, $key, esc_sql( $_data ) );
					
				}
				
			} else {
			
				// add the data
				add_post_meta( $post->ID, $key, esc_sql( $_data ) );
				
			}
			// ----------------
			*/
			
		}
		


		// ---------------------------------------------------------------------
		// Default Sidebar
		// ---------------------------------------------------------------------
		
		// do we have the option to choose the default sidebar (new in 3.3.3)?
		if ( $this->option_exists( 'cp_sidebar_default' ) ) {
		
			// find and save the data
			$_data = ( isset( $_POST['cp_sidebar_default'] ) ) ? 
					 $_POST['cp_sidebar_default'] : 
					 $this->db->option_get( 'cp_sidebar_default' );
	
			//print_r( '$_data: '.$_data ); die();
			//print_r( $post ); die();
	
			// set key
			$key = '_cp_sidebar_default';
			
			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
			
				// if empty string...
				if ( $_data === '' ) {
			
					// delete the meta_key
					delete_post_meta( $post->ID, $key );
				
				} else {
				
					// update the data
					update_post_meta( $post->ID, $key, esc_sql( $_data ) );
					
				}
				
			} else {
			
				// add the data
				add_post_meta( $post->ID, $key, esc_sql( $_data ) );
				
			}
			
		}



		// ---------------------------------------------------------------------
		// Create new post with content of current
		// ---------------------------------------------------------------------
		
		// find and save the data
		$_data = ( isset( $_POST['commentpress_new_post'] ) ) ? $_POST['commentpress_new_post'] : '0';
		
		/*
		print_r( array( 
		
			'$_data' => $_data,
			'$post ' => $post 
			
		) ); die();
		*/
		
		// do we want to create a new revision?
		if ( $_data == '0' ) { return; }
		
		

		// we need to make sure this only runs once
		//print_r( $this->saved_post === false ? 'f' : 't' ); die();
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
		
		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
		
			// if empty string...
			if ( $_data === '' ) {
		
				// delete the meta_key
				delete_post_meta( $post->ID, $key );
			
			} else {
			
				// update the data
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
		
		// if the custom field of our current post has a value...
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
		
		// if we have one set...
		if ( $_formatter != '' ) {
		
			// add the data
			add_post_meta( $new_post_id, $key, esc_sql( $_formatter ) );
			
		}
		
		
		
		// allow plugins to hook into this
		do_action( 'cp_workflow_save_copy', $new_post_id );
			


		// get the edit post link
		//$edit_link = get_edit_post_link( $new_post_id );
		
		// redirect there?

	}
	
	
	
	
	


	/** 
	 * @description: when a page is deleted, this makes sure that the CP options are synced
	 * @param object $post_id the post ID
	 * @return none
	 * @todo: 
	 *
	 */
	public function delete_meta( $post_id ) {
	
		// if no post, kick out
		if ( !$post_id ) { return; }
		
		
		
		// if it's our welcome page...
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
		
			// if the custom field has a value...
			if ( get_post_meta( $previous_version->ID, $key, true ) !== '' ) {
			
				// delete it
				delete_post_meta( $previous_version->ID, $key );
			
			}
			
		}
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: create all "special" pages
	 * @todo: 
	 *
	 */
	public function create_special_pages() {
		
		// one of the CommentPress Core themes MUST be active...
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
	 * @description: create a particular "special" page
	 * @todo: 
	 *
	 */
	public function create_special_page( $_page ) {
	
		// init
		$new_id = false;
		
		
	
		// get special pages array, if it's there
		$special_pages = $this->option_get( 'cp_special_pages', array() );
	


		// switch by page
		switch( $_page ) {
		
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
	 * @description: delete "special" pages
	 * @return boolean $success
	 * @todo: 
	 *
	 */
	public function delete_special_pages() {
	
		// init success flag
		$success = true;
		


		// only delete special pages if we have one of the CommentPress Core themes active
		// because other themes may have a totally different way of presenting the
		// content of the blog

		// retrieve data on special pages
		$special_pages = $this->option_get( 'cp_special_pages', array() );
		
		// if we have created any...
		if ( is_array( $special_pages ) AND count( $special_pages ) > 0 ) {
		
			// loop through them
			foreach( $special_pages AS $special_page ) {
			
				// bypass trash
				$force_delete = true;
			
				// try and delete each page...
				if ( !wp_delete_post( $special_page, $force_delete ) ) {
				
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
			
			// reset Wordpress internal page references
			$this->_reset_wordpress_option( 'show_on_front' );
			$this->_reset_wordpress_option( 'page_on_front' );
			$this->_reset_wordpress_option( 'page_for_posts' );
	
		}
	


		// --<
		return $success;

	}
	
	
	
	
	


	/** 
	 * @description: delete a particular "special" page
	 * @return boolean $success
	 * @todo: 
	 *
	 */
	public function delete_special_page( $_page ) {
	
		// init success flag
		$success = true;
		


		// only delete a special page if we have one of the CommentPress Core themes active
		// because other themes may have a totally different way of presenting the
		// content of the blog
		


		// get id of special page
		switch( $_page ) {
		
			case 'title':
			
				// set flag
				$flag = 'cp_welcome_page';

				// reset Wordpress internal page references
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

				// reset Wordpress internal page reference
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
		if ( !$page_id ) { return true; }



		// delete option
		$this->option_delete( $flag );



		// bypass trash
		$force_delete = true;
	
		// try and delete the page...
		if ( !wp_delete_post( $page_id, $force_delete ) ) {
		
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
	 * @description: test if a page is a "special" page
	 * @return boolean $is_special_page
	 * @todo: 
	 *
	 */
	public function is_special_page() {
	
		// init flag
		$is_special_page = false;
		


		// access post object
		global $post;
		
		// do we have one?
		if ( !is_object( $post ) ) {
		
			// --<
			return $is_special_page;
			
		}
	


		// get special pages
		$special_pages = $this->option_get( 'cp_special_pages', array() );
	
		// do we have a special page array?
		if ( is_array( $special_pages ) AND count( $special_pages ) > 0 ) {
		
			// is the current page one?
			if ( in_array( $post->ID, $special_pages ) ) {
			
				// it is...
				$is_special_page = true;
			
			}
		
		}



		// --<
		return $is_special_page;

	}
	
	
	
	
	


	/** 
	 * @description: check if a post allows comments to be posted
	 * @return boolean $allowed
	 * @todo: 
	 *
	 */
	public function comments_enabled() {
	
		// init return
		$allowed = false;
		


		// access post object
		global $post;
		
		// do we have one?
		if ( !is_object( $post ) ) {
		
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
	 * @description: get Wordpress approved comments
	 * @param integer $post_id the ID of the post
	 * @return array $comments
	 * @todo: 
	 *
	 */
	public function get_approved_comments( $post_ID ) {
		
		// for Wordpress, we use the API
		$comments = get_approved_comments( $post_ID );



		// --<
		return $comments;
	}
	
	
	
	
	


	/** 
	 * @description: get all Wordpress comments for a post, unless paged
	 * @param integer $post_id the ID of the post
	 * @return array $comments
	 * @todo: 
	 *
	 */
	public function get_all_comments( $post_ID ) {
	
		// access post
		global $post;

		// get all by default
		$pings = '';
		
		// check what we're allowing
		if ( is_object( $post ) AND $post->ping_status != 'open' ) {
		
			$pings = '&type=comment';

		}
		
		// for Wordpress, we use the API
		$comments = get_comments( 'post_id='.$post_ID.'&order=ASC'.$pings );
		
		
		
		// --<
		return $comments;
	}
	
	
	
	
	


	/** 
	 * @description: get all comments for a post
	 * @param integer $post_id the ID of the post
	 * @return array $comments
	 * @todo: 
	 *
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
	 * @description: when a comment is saved, this also saves the text signature
	 * @param integer $comment_id the ID of the comment
	 * @return boolean $result
	 * @todo: 
	 *
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
	
			//var_dump( $query );
	
	
	
			// store comment signature
			$result = $wpdb->query( $query );
		
		} else {
		
			// set result to true... why not, eh?
			$result = true;
		
		}
		
		
		
		// --<
		return $result;
		
	}
	
	
	
	
	


	/** 
	 * @description: when a comment is saved, this also saves the page it was submitted on. this allows
	 * us to point to the correct page of a multipage post without parsing the content every time
	 * @param integer $comment_id the ID of the comment
	 * @todo: 
	 *
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
				
				// if the custom field already has a value...
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
	 * @description: retrieves text signature by comment ID
	 * @param integer $comment_id the ID of the comment
	 * @return string $text_signature
	 * @todo: 
	 *
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
	 * @description: store text sigs in a global - because some versions of PHP do not save properties!
	 * @param: array $sigs array of text signatures
	 * @todo: 
	 *
	 */
	public function set_text_sigs( $sigs ) {
	
		global $ffffff_sigs;
	
		// store them
		$ffffff_sigs = $sigs;
		
		//var_dump( $ffffff_sigs );
		
	}
	
	
	
	
	




	/** 
	 * @description: retrieve text sigs
	 * @return array $text_signatures
	 * @todo: 
	 *
	 */
	public function get_text_sigs() {
	
		global $ffffff_sigs;
	
		//var_dump( $ffffff_sigs );

		// get them
		return $ffffff_sigs;
		
	}
	
	
	
	
	



	/** 
	 * @description: get javascript for the plugin, context dependent
	 * @return string $script
	 * @todo: 
	 *
	 */
	public function get_javascript_vars() {
	
		// init return
		$vars = array();
		
		
	
		// add comments open
		global $post;
		
		// if we don't have a post (like on the 404 page)
		if ( !is_object( $post ) ) {
		
			// comments must be closed
			$vars['cp_comments_open'] = 'n';

		} else {
			
			// check for post comment_status
			$vars['cp_comments_open'] = ( $post->comment_status == 'open' ) ? 'y' : 'n';
			
		}
		
		
		
		// assume no admin bars
		$vars['cp_wp_adminbar'] = 'n';
		$vars['cp_bp_adminbar'] = 'n';

		// assume pre-3.8 admin bar
		$vars['cp_wp_adminbar_height'] = '28';
		$vars['cp_wp_adminbar_expanded'] = '0';

		// are we showing the WP admin bar?
		if ( function_exists( 'is_admin_bar_showing' ) AND is_admin_bar_showing() ) {
			
			// we have it...
			$vars['cp_wp_adminbar'] = 'y';
			
			// check for a WP 3.8+ function
			if ( function_exists( 'wp_admin_bar_sidebar_toggle' ) ) {
				
				//die('here');
			
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
			
			// check for BP versions prior to 1.6 (1.6 uses the WP admin bar instead of a custom one)
			if ( !function_exists( 'bp_get_version' ) ) {
				
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
		if ( 
		
			get_option( 'comment_registration' ) == '1' AND
			! is_user_logged_in()
			
		) {
		
			// don't add rich text editor
			$vars['cp_tinymce'] = 0;
			
		}
		
		// check CP option
		if ( 
		
			$this->option_exists( 'cp_comment_editor' ) AND
			$this->option_get( 'cp_comment_editor' ) != '1'
			
		) {
		
			// don't add rich text editor
			$vars['cp_tinymce'] = 0;
			
		}
		
		// if on a public groupblog and user isn't logged in
		if (
			
			$this->parent_obj->is_groupblog() AND
			! is_user_logged_in()
			
		) {
			
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
		$vars['cp_multipage_page'] = ( !empty( $page ) ) ? $page : 0;
		
		// add path to template directory
		$vars['cp_template_dir'] = get_template_directory_uri();
		
		// add path to plugin directory
		$vars['cp_plugin_dir'] = WP_PLUGIN_URL.'/'.str_replace(basename( __FILE__),"",plugin_basename(__FILE__));
		
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
		
		// set signup flag
		$vars['cp_is_signup_page'] = '0';
		
		// test for signup
		if ( $this->parent_obj->is_signup_page() ) {
		
			// set flag
			$vars['cp_is_signup_page'] = '1';
			
		}
		
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
		
		
		
		// --<
		return $vars;
			
	}
	
	
	
	
	
	

	/** 
	 * @description: sets class properties for mobile browsers
	 * @todo: 
	 *
	 */
	public function test_for_mobile() {
	
		// do we have a user agent?
		if ( isset( $_SERVER["HTTP_USER_AGENT"] ) ) {
		
			// NOTE: keep an eye on touchphone agents
		
			// get agent
			$agent = $_SERVER["HTTP_USER_AGENT"];
			
			// init touchphone array
			$touchphones = array(
				'iPhone',
				'iPod',
				'Android',
				'BlackBerry9530',
				'LG-TU915 Obigo', // LG touch browser
				'LGE VX',
				'webOS', // Palm Pre, etc.
			);
			
			// loop through them
			foreach( $touchphones AS $phone ) {

				// test for its name in the agent string
				if ( strpos( $agent, $phone ) !== false ) {
				
					// set flag
					$this->is_mobile_touch = true;
				
				}
			
			}
			
			// the old Commentpress also includes Mobile_Detect
			if ( !class_exists( 'Mobile_Detect' ) ) {
			
				// use code from http://code.google.com/p/php-mobile-detect/
				include_once( COMMENTPRESS_PLUGIN_PATH . 'commentpress-core/assets/includes/mobile-detect/Mobile_Detect.php' );
			
			}
			
			// init
			$detect = new Mobile_Detect();
			
			// is it mobile?
			if ( $detect->isMobile() ) {
			
				// set flag
				$this->is_mobile = true;

			}
			
			// is it a tablet?
			if ( $detect->isTablet() ) {
			
				// set flag
				$this->is_tablet = true;

			}
			
		}

	}
	
	





	/** 
	 * @description: returns class properties for mobile browsers
	 * @todo: 
	 *
	 */
	public function is_mobile() {
	
		// do we have the property?
		if ( !isset( $this->is_mobile ) ) { 
		
			// get it
			$this->test_for_mobile();
		
		}

		// --<
		return $this->is_mobile;
			
	}
	
	





	/** 
	 * @description: returns class properties for tablet browsers
	 * @todo: 
	 *
	 */
	public function is_tablet() {
	
		// do we have the property?
		if ( !isset( $this->is_tablet ) ) { 
		
			// get it
			$this->test_for_mobile();
		
		}

		// --<
		return $this->is_tablet;
			
	}
	
	





//##############################################################################







	/*
	============================================================================
	PRIVATE METHODS
	============================================================================
	*/
	
	
	



	/*
	---------------------------------------------------------------
	Object Initialisation
	---------------------------------------------------------------
	*/
	
	/** 
	 * @description: object initialisation
	 * @todo:
	 *
	 */
	function _init() {
		
		// load options array
		$this->commentpress_options = $this->option_wp_get( 'commentpress_options', $this->commentpress_options );
		
		// if we don't have one
		if ( count( $this->commentpress_options ) == 0 ) {
		
			// if not in backend
			if ( !is_admin() ) {
		
				// init upgrade
				//die( 'CommentPress Core upgrade required.' );
				
			}
		
		}
		
	}







	/** 
	 * @description: create new post with content of existing
	 * @todo: 
	 *
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
		//print_r( $post ); die();
		
		// default page title
		$prefix = __( 'Copy of ', 'commentpress-core' );

		// allow overrides of prefix
		$prefix = apply_filters( 'commentpress_new_post_title_prefix', $prefix );
		
		// set title, but allow overrides
		$new_post['post_title'] = apply_filters( 'commentpress_new_post_title', $prefix.$post->post_title, $post );
		
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
	 * @description: create "title" page
	 * @todo: 
	 *
	 */
	function _create_title_page() {
	
		// get the option, if it exists
		$page_exists = $this->option_get( 'cp_welcome_page' );
		
		// don't create if we already have the option set
		if ( $page_exists !== false AND is_numeric( $page_exists ) ) {
			
			// get the page (the plugin may have been deactivated, then the page deleted)
			$welcome = get_post( $page_exists );
			
			// check that the page exists 
			if ( !is_null( $welcome ) ) {
			
				// got it...
		
				// we still ought to set Wordpress internal page references
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
		$_title = __( 'Title Page', 'commentpress-core' );

		// set, but allow overrides
		$title['post_title'] = apply_filters( 'cp_title_page_title', $_title );
		
		// default content
		$content = __( 
		
		'Welcome to your new CommentPress site, which allows your readers to comment paragraph-by-paragraph or line-by-line in the margins of a text. Annotate, gloss, workshop, debate: with CommentPress you can do all of these things on a finer-grained level, turning a document into a conversation.

This is your title page. Edit it to suit your needs. It has been automatically set as your homepage but if you want another page as your homepage, set it in <em>Wordpress</em> &#8594; <em>Settings</em> &#8594; <em>Reading</em>.

You can also set a number of options in <em>Wordpress</em> &#8594; <em>Settings</em> &#8594; <em>CommentPress</em> to make the site work the way you want it to. Use the Theme Customizer to change the way your site looks in <em>Wordpress</em> &#8594; <em>Appearance</em> &#8594; <em>Customize</em>. For help with structuring, formatting and reading text in CommentPress, please refer to the <a href="http://www.futureofthebook.org/commentpress/">CommentPress website</a>.', 'commentpress-core' 
			
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
		
		// set Wordpress internal page references
		$this->_store_wordpress_option( 'show_on_front', 'page' );
		$this->_store_wordpress_option( 'page_on_front', $title_id );

		// --<
		return $title_id;

	}
	
	
	
	
	


	/** 
	 * @description: create "general comments" page
	 * @todo: 
	 *
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
	 * @description: create "all comments" page
	 * @todo: 
	 *
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
	 * @description: create "comments by author" page
	 * @todo: 
	 *
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
	 * @description: create "blog" page
	 * @todo: 
	 *
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

		// set Wordpress internal page reference
		$this->_store_wordpress_option( 'page_for_posts', $blog_id );

		// --<
		return $blog_id;

	}
	
	
	
	
	


	/** 
	 * @description: create "blog archive" page
	 * @todo: 
	 *
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
	 * @description: create "table of contents" page
	 * @todo: NOT USED
	 *
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
	 * @description: cancels comment paging because CP will not work with comment paging
	 * @todo: 
	 */
	function _cancel_comment_paging() {
	
		// store option
		$this->_store_wordpress_option( 'page_comments', '' );
	
	}
	
	
	
	
	
	
	/** 
	 * @description: resets comment paging option when plugin is deactivated
	 * @todo: 
	 */
	function _reset_comment_paging() {
	
		// reset option
		$this->_reset_wordpress_option( 'page_comments' );
	
	}
	
	
	
	
	
	
	/** 
	 * @description: clears widgets for a fresh start
	 * @todo: 
	 */
	function _clear_widgets() {
	
		// set backup option
		add_option( 'commentpress_sidebars_widgets', $this->option_wp_get( 'sidebars_widgets' ) );

		// clear them
		update_option( 'sidebars_widgets', array() );

	}
	
	
	
	
	
	
	/** 
	 * @description: resets widgets when plugin is deactivated
	 * @todo: 
	 */
	function _reset_widgets() {
	
		// reset option
		$this->_reset_wordpress_option( 'sidebars_widgets' );
	
	}
	
	
	
	
	
	
	/** 
	 * @description: store Wordpress option
	 * @param string $name the name of the option
	 * @param mixed $value the value of the option
	 * @todo: 
	 */
	function _store_wordpress_option( $name, $value ) {
	
		// set backup option
		add_option( 'commentpress_'.$name, $this->option_wp_get( $name ) );

		// set the Wordpress option
		$this->option_wp_set( $name, $value );
	
	}
	
	
	
	
	
	
	/** 
	 * @description: reset Wordpress option
	 * @param string $name the name of the option
	 * @todo: 
	 */
	function _reset_wordpress_option( $name ) {
	
		// set the Wordpress option
		$this->option_wp_set( $name, $this->option_wp_get( 'cp_'.$name ) );
	
		// remove backup option
		delete_option( 'commentpress_'.$name );

	}
	
	
	
	
	
	
	/** 
	 * @description: create all basic CommentPress Core options
	 * @todo:
	 *
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
			'cp_header_bg_colour' => $this->header_bg_colour,
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
		
		);

		// Paragraph-level comments enabled by default
		add_option( 'commentpress_options', $this->commentpress_options );
		
	}
	
	
	
	
	


	/** 
	 * @description: reset CommentPress Core options
	 * @todo: 
	 *
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
		$this->option_set( 'cp_header_bg_colour', $this->header_bg_colour );

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
		
		// store it
		$this->options_save();
		
	}
	
	
	
	
	


	/** 
	 * @description: migrate all CommentPress Core options from old plugin
	 * @todo:
	 *
	 */
	function _options_migrate() {
	
		// get existing options
		$old = get_option( 'cp_options', array() );
		
		
		
		// ---------------------------------------------------------------------
		// retrieve new ones, if they exist, or use defaults otherwise
		// ---------------------------------------------------------------------
		$this->toc_content = 			isset( $old[ 'cp_show_posts_or_pages_in_toc' ] ) ?
										$old[ 'cp_show_posts_or_pages_in_toc' ] :
										$this->toc_content;
	
		$this->toc_chapter_is_page = 	isset( $old[ 'cp_toc_chapter_is_page' ] ) ?
										$old[ 'cp_toc_chapter_is_page' ] :
										$this->toc_chapter_is_page;
	
		$this->show_subpages =		 	isset( $old[ 'cp_show_subpages' ] ) ?
										$old[ 'cp_show_subpages' ] :
										$this->show_subpages;
		
		$this->show_extended_toc = 		isset( $old[ 'cp_show_extended_toc' ] ) ?
										$old[ 'cp_show_extended_toc' ] :
										$this->show_extended_toc;
	
		$this->title_visibility =	 	isset( $old[ 'cp_title_visibility' ] ) ?
										$old[ 'cp_title_visibility' ] :
										$this->title_visibility;
	
		$this->page_meta_visibility = 	isset( $old[ 'cp_page_meta_visibility' ] ) ?
										$old[ 'cp_page_meta_visibility' ] :
										$this->page_meta_visibility;
		
		// header background colour
		$header_bg_colour =	 			isset( $old[ 'cp_header_bg_colour' ] ) ?
										$old[ 'cp_header_bg_colour' ] :
										$this->header_bg_colour;
		
		// if it's the old default, upgrade to new default
		if ( $header_bg_colour == '819565' ) {
			$header_bg_colour = $this->header_bg_colour;
		}
		
		$this->js_scroll_speed =	 	isset( $old[ 'cp_js_scroll_speed' ] ) ?
										$old[ 'cp_js_scroll_speed' ] :
										$this->js_scroll_speed;
	
		$this->min_page_width =		 	isset( $old[ 'cp_min_page_width' ] ) ?
										$old[ 'cp_min_page_width' ] :
										$this->min_page_width;
	
		$this->comment_editor =		 	isset( $old[ 'cp_comment_editor' ] ) ?
										$old[ 'cp_comment_editor' ] :
										$this->comment_editor;
	
		$this->promote_reading =	 	isset( $old[ 'cp_promote_reading' ] ) ?
										$old[ 'cp_promote_reading' ] :
										$this->promote_reading;
	
		$this->excerpt_length =		 	isset( $old[ 'cp_excerpt_length' ] ) ?
										$old[ 'cp_excerpt_length' ] :
										$this->excerpt_length;
	
		$this->para_comments_live = 	isset( $old[ 'cp_para_comments_live' ] ) ?
										$old[ 'cp_para_comments_live' ] :
										$this->para_comments_live;
	
		$blog_type = 					isset( $old[ 'cp_blog_type' ] ) ?
										$old[ 'cp_blog_type' ] :
										$this->blog_type;
	
		$blog_workflow =		 		isset( $old[ 'cp_blog_workflow' ] ) ?
										$old[ 'cp_blog_workflow' ] :
										$this->blog_workflow;
	
		$this->sidebar_default =	 	isset( $old[ 'cp_sidebar_default' ] ) ?
										$old[ 'cp_sidebar_default' ] :
										$this->sidebar_default;
		
		$this->featured_images =	 	isset( $old[ 'cp_featured_images' ] ) ?
										$old[ 'cp_featured_images' ] :
										$this->featured_images;
		
		$this->textblock_meta =		 	isset( $old[ 'cp_textblock_meta' ] ) ?
										$old[ 'cp_textblock_meta' ] :
										$this->textblock_meta;
		


		// ---------------------------------------------------------------------
		// special pages
		// ---------------------------------------------------------------------
		$special_pages =	 		isset( $old[ 'cp_special_pages' ] ) ?
									$old[ 'cp_special_pages' ] :
									null;

		$blog_page =		 		isset( $old[ 'cp_blog_page' ] ) ?
									$old[ 'cp_blog_page' ] :
									null;

		$blog_archive_page = 		isset( $old[ 'cp_blog_archive_page' ] ) ?
									$old[ 'cp_blog_archive_page' ] :
									null;

		$general_comments_page =	isset( $old[ 'cp_general_comments_page' ] ) ?
									$old[ 'cp_general_comments_page' ] :
									null;

		$all_comments_page = 		isset( $old[ 'cp_all_comments_page' ] ) ?
									$old[ 'cp_all_comments_page' ] :
									null;

		$comments_by_page = 		isset( $old[ 'cp_comments_by_page' ] ) ?
									$old[ 'cp_comments_by_page' ] :
									null;

		$toc_page =		 			isset( $old[ 'cp_toc_page' ] ) ?
									$old[ 'cp_toc_page' ] :
									null;
		


		// init options array
		$this->commentpress_options = array(
			
			// basic options
			'cp_show_posts_or_pages_in_toc' => $this->toc_content,
			'cp_toc_chapter_is_page' => $this->toc_chapter_is_page,
			'cp_show_subpages' => $this->show_subpages,
			'cp_show_extended_toc' => $this->show_extended_toc,
			'cp_title_visibility' => $this->title_visibility,
			'cp_page_meta_visibility' => $this->page_meta_visibility,
			'cp_header_bg_colour' => $header_bg_colour,
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
			
		);
			
		
		
		// if we have special pages
		if ( !is_null( $special_pages ) AND is_array( $special_pages ) ) {
		
			// let's have them as well
			$pages = array( 
			
				// special pages
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
			
				// now delete the old Commentpress options
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
		
		// welcome page is a bit of an oddity: deal with it here...
		$welcome_page =	isset( $old[ 'cp_welcome_page' ] ) ? $old[ 'cp_welcome_page' ] : null;
		
		// did we get a welcome page?
		if ( !is_null( $welcome_page ) ) {
		
			// if the custom field already has a value...
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
		if ( !empty( $theme_settings ) ) {
		
			// migrate them
			add_option( 'commentpress_theme_settings', $theme_settings );
			
		}

		// migrate Theme Mods
		$theme_mods = get_option( 'theme_mods_commentpress', array() );

		// did we get any?
		if ( is_array( $theme_mods ) AND count( $theme_mods ) > 0 ) {
		
			// get header background image...
			if ( isset( $theme_mods['header_image'] ) ) {
				
				//print_r( 'here' ); die();

				// is it a Commentpress one?
				if ( strstr( $theme_mods['header_image'], 'style/images/header/caves.jpg' ) !== false ) {
				
					// point it at the equivalent new version
					$theme_mods['header_image'] = COMMENTPRESS_PLUGIN_URL.
												  'themes/commentpress-theme'.
												  '/assets/images/header/caves-green.jpg';
					
				}
				
			}
			
			/*
			// if we wanted to clear widgets widgets...
			if ( isset( $theme_mods['sidebars_widgets'] ) ) {
				
				// remove them
				unset( $theme_mods['sidebars_widgets'] );
			
			}
	
			// override widgets
			$this->_clear_widgets();
			*/
			
			// update theme mods (will create if it doesn't exist)
			update_option( 'theme_mods_commentpress-theme', $theme_mods );
						
		}
		
		
		

		// ---------------------------------------------------------------------
		// deactivate old Commentpress and Commentpress Ajaxified
		// ---------------------------------------------------------------------
		
		// get old Commentpress Ajaxified
		$cpajax_old = commentpress_find_plugin_by_name( 'Commentpress Ajaxified' );
		if ( $cpajax_old AND is_plugin_active( $cpajax_old ) ) { deactivate_plugins( $cpajax_old ); }
		
		// get old Commentpress
		$cp_old = commentpress_find_plugin_by_name( 'Commentpress' );
		if ( $cp_old AND is_plugin_active( $cp_old ) ) { deactivate_plugins( $cp_old ); }
		
	}
	
	
	
	
	

	/** 
	 * @description: upgrade Commentpress options to array (only for pre-CP3.2 upgrades)
	 * @todo: 
	 *
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
	 * @description: delete all legacy Commentpress options
	 * @todo: 
	 *
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






