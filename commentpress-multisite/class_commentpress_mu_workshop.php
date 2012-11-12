<?php /*
================================================================================
Class CommentpressGroupblogWorkshop
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This class overrides the name of Groupblogs from "Blog" (or "Document") to "Workshop"

--------------------------------------------------------------------------------
*/






/*
================================================================================
Class Name
================================================================================
*/

class CommentpressGroupblogWorkshop {






	/*
	============================================================================
	Properties
	============================================================================
	*/
	
	// parent object reference
	var $parent_obj;
	
	// admin object reference
	var $db;
	
	// default to "off"
	var $cpmu_bp_workshop_nomenclature = 0;
	
	
	



	/** 
	 * @description: initialises this object
	 * @param object $parent_obj a reference to the parent object
	 * @return object
	 * @todo: 
	 *
	 */
	function __construct( $parent_obj = null ) {
	
		// store reference to "parent" (calling obj, not OOP parent)
		$this->parent_obj = $parent_obj;
	
		// store reference to database wrapper (child of calling obj)
		$this->db = $this->parent_obj->db;
	
		// init
		$this->_init();

		// --<
		return $this;

	}
	
	
	



	/**
	 * PHP 4 constructor
	 */
	function CommentpressGroupblogWorkshop( $parent_obj = null ) {
		
		// is this php5?
		if ( version_compare( PHP_VERSION, "5.0.0", "<" ) ) {
		
			// call php5 constructor
			$this->__construct( $parent_obj );
			
		}
		
		// --<
		return $this;

	}






	/** 
	 * @description: set up all items associated with this object
	 * @todo: 
	 *
	 */
	function initialise() {
	
	}
	
	
	



	/** 
	 * @description: if needed, destroys all items associated with this object
	 * @todo: 
	 *
	 */
	function destroy() {
	
	}
	
	
	



//##############################################################################
	
	
	



	/*
	============================================================================
	PUBLIC METHODS
	============================================================================
	*/
	
	
	



	/*
	----------------------------------------------------------------------------
	BuddyPress Groupblog Text Overrides
	----------------------------------------------------------------------------
	*/
	
	/**
	 * @description: override the name of the filter item
	 * @todo: 
	 *
	 */
	function groupblog_comment_name() { 
	
		// default name
		return __( 'Workshop Comments', 'commentpress-plugin' );
		
	}
	
	
	



	/** 
	 * @description: override the name of the filter item
	 * @todo: 
	 *
	 */
	function groupblog_post_name() {
	
		// default name
		return __( 'Workshop Posts', 'commentpress-plugin' );
	
	}
	
	
	



	/** 
	 * @description: override the name of the filter item
	 * @todo: 
	 *
	 */
	function activity_post_name() {
	
		// default name
		return __( 'workshop post', 'commentpress-plugin' );
	
	}
	
	
	



	/** 
	 * @description: override the name of the sub-nav item
	 * @todo: 
	 *
	 */
	function filter_blog_name( $name ) {
	
		return __( 'Workshop', 'commentpress-plugin' );
		
	}
	
	
	
	
	
	
	/** 
	 * @description: override the slug of the sub-nav item
	 * @todo: 
	 *
	 */
	function filter_blog_slug( $slug ) {
	
		return 'workshop';
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: override the title of the "Recent Comments in..." link
	 * @todo: 
	 *
	 */
	function activity_tab_recent_title_blog( $title ) {
	
		// if groupblog...
		global $commentpress_core;
		if ( 
		
			!is_null( $commentpress_core ) 
			AND is_object( $commentpress_core ) 
			AND $commentpress_core->is_groupblog() 
			
		) { 
		
			// override default link name
			return apply_filters(
				'cpmsextras_user_links_new_site_title', 
				__( 'Recent Comments in this Workshop', 'commentpress-plugin' )
			);
			
		}
		
		// if main site...
		if ( is_multisite() AND is_main_site() ) { 
		
			// override default link name
			return apply_filters(
				'cpmsextras_user_links_new_site_title', 
				__( 'Recent Comments in Site Blog', 'commentpress-plugin' )
			);
			
		}
		
		return $title;
	
	}
	
	
	
	
	
	
	
	/** 
	 * @description: override title on All Comments page
	 * @todo: 
	 *
	 */
	function page_all_comments_blog_title( $title ) {
	
		// --<
		return __( 'Comments on Workshop Posts', 'commentpress-plugin' );
	
	}
	
	
	
	
	

	/** 
	 * @description: override title on All Comments page
	 * @todo: 
	 *
	 */
	function page_all_comments_book_title( $title ) {
	
		// --<
		return __( 'Comments on Workshop Pages', 'commentpress-plugin' );
	
	}
	
	
	
	
	

	/** 
	 * @description: override title on Activity tab
	 * @todo: 
	 *
	 */
	function filter_activity_title_all_yours( $title ) {
	
		// --<
		return __( 'Recent Activity in your Workshops', 'commentpress-plugin' );
	
	}
	
	
	
	
	

	/** 
	 * @description: override title on Activity tab
	 * @todo: 
	 *
	 */
	function filter_activity_title_all_public( $title ) {
	
		// --<
		return __( 'Recent Activity in Public Workshops', 'commentpress-plugin' );
	
	}
	
	
	
	
	

	/** 
	 * @description: override CommentPress "Title Page"
	 * @todo: 
	 *
	 */
	function filter_nav_title_page_title( $title ) {
		
		// access globals
		global $commentpress_core;

		// if plugin active...
		if ( 
		
			!is_null( $commentpress_core ) 
			AND is_object( $commentpress_core )
			AND $commentpress_core->is_groupblog()
			
		) {
		
			// --<
			return __( 'Workshop Home Page', 'commentpress-plugin' );
			
		}
		
		// --<
		return $title;
	
	}
	
	
	
	
	

	/** 
	 * @description: override the BP Sites Directory "visit" button
	 * @todo: 
	 *
	 */
	function get_blogs_visit_blog_button( $button ) {
		
		// update link for groupblogs
		return __( 'Visit Workshop', 'commentpress-plugin' );
	
	}
	
	
	
	
	
	
//##############################################################################
	
	
	



	/*
	============================================================================
	PRIVATE METHODS
	============================================================================
	*/
	
	
	



	/** 
	 * @description: object initialisation
	 * @todo:
	 *
	 */
	function _init() {
	
		// is this the back end?
		if ( is_admin() ) {
	
			// add element to Network BuddyPress form
			add_filter( 'cpmu_network_buddypress_options_form', array( $this, '_buddypress_admin_form' ) );
			
			// hook into Network BuddyPress form update
			add_action( 'cpmu_db_options_update', array( $this, '_buddypress_admin_update' ), 21 );
			
			// hook into Network BuddyPress options reset
			add_filter( 'cpmu_buddypress_options_get_defaults', array( $this, '_get_default_settings' ), 10, 1 );
			
		}
		
		// do we have our option set?
		if ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature' ) == '1' ) {
		
			// register hooks
			$this->_register_hooks();
			
		}
		
	}
	
	
	



	/** 
	 * @description: register Wordpress hooks
	 * @todo: 
	 *
	 */
	function _register_hooks() {
		
		// override CommentPress "Title Page"
		add_filter( 'cp_nav_title_page_title', array( $this, 'filter_nav_title_page_title' ), 25 );
		
		// override CP title of "view document" button in blog lists
		add_filter( 'cp_get_blogs_visit_groupblog_button', array( $this, 'get_blogs_visit_blog_button' ), 25, 1 );

		// filter bp-groupblog defaults
		add_filter( 'cpmu_bp_groupblog_subnav_item_name', array( $this, 'filter_blog_name' ), 25 );
		add_filter( 'cpmu_bp_groupblog_subnav_item_slug', array( $this, 'filter_blog_slug' ), 25 );
		
		// change name of activity sidebar headings
		add_filter( 'cp_activity_tab_recent_title_all_yours', array( $this, 'filter_activity_title_all_yours' ), 25 );
		add_filter( 'cp_activity_tab_recent_title_all_public', array( $this, 'filter_activity_title_all_public' ), 25 );
		
		// override with 'workshop'
		add_filter( 'cp_activity_tab_recent_title_blog', array( $this, 'activity_tab_recent_title_blog' ), 25, 1 );
		
		// override titles of BP activity filters
		add_filter( 'cp_groupblog_comment_name', array( $this, 'groupblog_comment_name' ), 25 );
		add_filter( 'cp_groupblog_post_name', array( $this, 'groupblog_post_name' ), 25 );
		
		// cp_activity_post_name_filter
		add_filter( 'cp_activity_post_name', array( $this, 'activity_post_name' ), 25 );
		
		// override label on All Comments page
		add_filter( 'cp_page_all_comments_book_title', array( $this, 'page_all_comments_book_title' ), 25, 1 );
		add_filter( 'cp_page_all_comments_blog_title', array( $this, 'page_all_comments_blog_title' ), 25, 1 );
		
	}
	
	
	



	/** 
	 * @description: add our options to the BuddyPress admin form
	 * @todo: 
	 *
	 */
	function _buddypress_admin_form() {
	
		// define form element
		$element = '
	<tr valign="top">
		<th scope="row"><label for="cpmu_bp_workshop_nomenclature">'.__( 'Change the name of a Group "Blog" to "Workshop"', 'commentpress-plugin' ).'</label></th>
		<td><input id="cpmu_bp_workshop_nomenclature" name="cpmu_bp_workshop_nomenclature" value="1" type="checkbox"'.( $this->db->option_get( 'cpmu_bp_workshop_nomenclature' ) == '1' ? ' checked="checked"' : '' ).' /></td>
	</tr>

';
		
		// --<
		return $element;

	}
	
	
	
	
	
	
	/** 
	 * @description: hook into Network BuddyPress form update
	 * @todo: 
	 *
	 */
	function _buddypress_admin_update() {
	
		// database object
		global $wpdb;
		
		// init
		$cpmu_bp_workshop_nomenclature = 0;
	
		// get variables
		extract( $_POST );
		
		// set option
		$cpmu_bp_workshop_nomenclature = $wpdb->escape( $cpmu_bp_workshop_nomenclature );
		$this->db->option_set( 'cpmu_bp_workshop_nomenclature', ( $cpmu_bp_workshop_nomenclature ? 1 : 0 ) );
		
	}
	
	
	
	
	
	
	/**
	 * @description: add our default BuddyPress-related settings
	 * @todo: 
	 *
	 */
	function _get_default_settings( $settings ) {
	
		// add our option
		$settings['cpmu_bp_workshop_nomenclature'] = $this->cpmu_bp_workshop_nomenclature;
		
		// --<
		return $settings;
		
	}
	
	
	



//##############################################################################
	
	
	



} // class ends
	
	
	




