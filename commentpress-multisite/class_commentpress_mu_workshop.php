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
	public $parent_obj;
	
	// admin object reference
	public $db;
	
	// default to "off"
	public $cpmu_bp_workshop_nomenclature = 0;
	
	// default name to "Document"
	public $cpmu_bp_workshop_nomenclature_name = 'Document';
	
	// default plural to "Documents"
	public $cpmu_bp_workshop_nomenclature_plural = 'Documents';
	
	// default slug to "document"
	public $cpmu_bp_workshop_nomenclature_slug = 'document';
	
	
	



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
	 * @description: set up all items associated with this object
	 * @todo: 
	 *
	 */
	public function initialise() {
	
	}
	
	
	



	/** 
	 * @description: if needed, destroys all items associated with this object
	 * @todo: 
	 *
	 */
	public function destroy() {
	
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
	public function groupblog_comment_name() { 
	
		// default name
		return sprintf(
			__( '%s Comments', 'commentpress-core' ),
			$this->cpmu_bp_workshop_nomenclature_name
		);
		
	}
	
	
	



	/** 
	 * @description: override the name of the filter item
	 * @todo: 
	 *
	 */
	public function groupblog_post_name() {
	
		// default name
		return sprintf(
			__( '%s Posts', 'commentpress-core' ),
			$this->cpmu_bp_workshop_nomenclature_name
		);
	
	}
	
	
	



	/** 
	 * @description: override the name of the filter item
	 * @todo: 
	 *
	 */
	public function activity_post_name() {
	
		// default name
		return sprintf( 
			__( '%s post', 'commentpress-core' ), 
			strtolower( $this->cpmu_bp_workshop_nomenclature_name )
		);
	
	}
	
	
	



	/** 
	 * @description: override the name of the sub-nav item
	 * @todo: 
	 *
	 */
	public function filter_blog_name( $name ) {
		
		// --<
		return $this->cpmu_bp_workshop_nomenclature_name;
		
	}
	
	
	
	
	
	
	/** 
	 * @description: override the slug of the sub-nav item
	 * @todo: 
	 *
	 */
	public function filter_blog_slug( $slug ) {
	
		// --<
		return $this->cpmu_bp_workshop_nomenclature_slug;
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: override the title of the "Recent Comments in..." link
	 * @todo: 
	 *
	 */
	public function activity_tab_recent_title_blog( $title ) {
	
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
				sprintf(
					__( 'Recent Comments in this %s', 'commentpress-core' ),
					$this->cpmu_bp_workshop_nomenclature_name
				)
			);
			
		}
		
		// if main site...
		if ( is_multisite() AND is_main_site() ) { 
		
			// override default link name
			return apply_filters(
				'cpmsextras_user_links_new_site_title', 
				__( 'Recent Comments in Site Blog', 'commentpress-core' )
			);
			
		}
		
		return $title;
	
	}
	
	
	
	
	
	
	
	/** 
	 * @description: override title on All Comments page
	 * @todo: 
	 *
	 */
	public function page_all_comments_blog_title( $title ) {
	
		// override if groupblog
		if ( !$this->parent_obj->bp->_is_commentpress_groupblog() ) {
			return $title;
		}
		
		// --<
		return sprintf(
			__( 'Comments on %s Posts', 'commentpress-core' ),
			$this->cpmu_bp_workshop_nomenclature_name
		);
	
	}
	
	
	
	
	

	/** 
	 * @description: override title on All Comments page
	 * @todo: 
	 *
	 */
	public function page_all_comments_book_title( $title ) {
	
		// override if groupblog
		if ( !$this->parent_obj->bp->_is_commentpress_groupblog() ) {
			return $title;
		}
		
		// --<
		return sprintf(
			__( 'Comments on %s Pages', 'commentpress-core' ),
			$this->cpmu_bp_workshop_nomenclature_name
		);
	
	}
	
	
	
	
	

	/** 
	 * @description: override title on Activity tab
	 * @todo: 
	 *
	 */
	public function filter_activity_title_all_yours( $title ) {
	
		// override if groupblog
		if ( 
			! bp_is_root_blog() AND 
			! $this->parent_obj->bp->_is_commentpress_groupblog() ) 
		{
			return $title;
		}
		
		// --<
		return sprintf(
			__( 'Recent Activity in your %s', 'commentpress-core' ),
			$this->cpmu_bp_workshop_nomenclature_plural			
		);
	
	}
	
	
	
	
	

	/** 
	 * @description: override title on Activity tab
	 * @todo: 
	 *
	 */
	public function filter_activity_title_all_public( $title ) {
	
		// override if groupblog
		if ( 
			! bp_is_root_blog() AND 
			! $this->parent_obj->bp->_is_commentpress_groupblog() ) 
		{
			return $title;
		}
		
		// --<
		return sprintf(
			__( 'Recent Activity in Public %s', 'commentpress-core' ),
			$this->cpmu_bp_workshop_nomenclature_plural
		);
	
	}
	
	
	
	
	

	/** 
	 * @description: override CommentPress "Title Page"
	 * @todo: 
	 *
	 */
	public function filter_nav_title_page_title( $title ) {
	
		// bail if main BP site
		if ( bp_is_root_blog() ) return $title;
		
		// bail if not groupblog
		if ( ! $this->parent_obj->bp->_is_commentpress_groupblog() ) {
			return $title;
		}
		
		// --<
		return sprintf(
			__( '%s Home Page', 'commentpress-core' ),
			$this->cpmu_bp_workshop_nomenclature_name
		);
		
	}
	
	
	
	
	

	/** 
	 * @description: override the BP Sites Directory "visit" button
	 * @todo: 
	 *
	 */
	public function get_blogs_visit_blog_button( $button ) {
		
		// update link for groupblogs
		return sprintf(
			__( 'Visit %s', 'commentpress-core' ),
			$this->cpmu_bp_workshop_nomenclature_name
		);
	
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
		
			// store the setting locally
			$this->cpmu_bp_workshop_nomenclature = '1';
		
			// do we have the name option already defined?
			if ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature_name' ) == '' ) {
			
				// no, so we must have switched to the legacy "Workshop" setting
				$this->cpmu_bp_workshop_nomenclature_name = $this->_get_legacy_name();
				
			} else {
			
				// store the setting locally
				$this->cpmu_bp_workshop_nomenclature_name = $this->db->option_get( 'cpmu_bp_workshop_nomenclature_name' );

			}
			
			// do we have the plural option already defined?
			if ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature_plural' ) == '' ) {
			
				// no, likewise we must have switched to the legacy "Workshop" setting
				$this->cpmu_bp_workshop_nomenclature_plural = $this->_get_legacy_plural();
			
			} else {
			
				// store the setting locally
				$this->cpmu_bp_workshop_nomenclature_plural = $this->db->option_get( 'cpmu_bp_workshop_nomenclature_plural' );

			}
			
			// do we have the slug option already defined?
			if ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature_slug' ) == '' ) {
			
				// no, likewise we must have switched to the legacy "Workshop" setting
				$this->cpmu_bp_workshop_nomenclature_slug = $this->_get_legacy_slug();
			
			} else {
			
				// store the setting locally
				$this->cpmu_bp_workshop_nomenclature_slug = $this->db->option_get( 'cpmu_bp_workshop_nomenclature_slug' );

			}
			
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
	
		// check if we already have it switched on
		if ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature' ) == '1' ) {
			
			// do we have the name option already defined?
			if ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature_name' ) == '' ) {
			
				// no, so we must have switched to the legacy "Workshop" setting
				$this->cpmu_bp_workshop_nomenclature_name = $this->_get_legacy_name();
			
			}
			
			// do we have the plural option already defined?
			if ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature_plural' ) == '' ) {
			
				// no, likewise we must have switched to the legacy "Workshop" setting
				$this->cpmu_bp_workshop_nomenclature_plural = $this->_get_legacy_plural();
			
			}
			
		}
	
		// define form element
		$element = '
	<tr valign="top">
		<th scope="row"><label for="cpmu_bp_workshop_nomenclature">'.__( 'Change the name of a Group "Document"?', 'commentpress-core' ).'</label></th>
		<td><input id="cpmu_bp_workshop_nomenclature" name="cpmu_bp_workshop_nomenclature" value="1" type="checkbox"'.( $this->db->option_get( 'cpmu_bp_workshop_nomenclature' ) == '1' ? ' checked="checked"' : '' ).' /></td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cpmu_bp_workshop_nomenclature_name">'.__( 'Singular name for a Group "Document"', 'commentpress-core' ).'</label></th>
		<td><input id="cpmu_bp_workshop_nomenclature_name" name="cpmu_bp_workshop_nomenclature_name" value="'.( $this->db->option_get( 'cpmu_bp_workshop_nomenclature_name' ) == '' ? $this->cpmu_bp_workshop_nomenclature_name : $this->db->option_get( 'cpmu_bp_workshop_nomenclature_name' ) ).'" type="text" /></td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cpmu_bp_workshop_nomenclature_plural">'.__( 'Plural name for Group "Documents"', 'commentpress-core' ).'</label></th>
		<td><input id="cpmu_bp_workshop_nomenclature_plural" name="cpmu_bp_workshop_nomenclature_plural" value="'.( $this->db->option_get( 'cpmu_bp_workshop_nomenclature_plural' ) == '' ? $this->cpmu_bp_workshop_nomenclature_plural : $this->db->option_get( 'cpmu_bp_workshop_nomenclature_plural' ) ).'" type="text" /></td>
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
	
		// init
		$cpmu_bp_workshop_nomenclature = 0;
	
		// get variables
		extract( $_POST );
		
		
		
		// set on/off option
		$cpmu_bp_workshop_nomenclature = esc_sql( $cpmu_bp_workshop_nomenclature );
		$this->db->option_set( 'cpmu_bp_workshop_nomenclature', ( $cpmu_bp_workshop_nomenclature ? 1 : 0 ) );
		
		
		
		// get name option
		$cpmu_bp_workshop_nomenclature_name = esc_sql( $cpmu_bp_workshop_nomenclature_name );
		
		// revert to default if we didn't get one...
		if ( $cpmu_bp_workshop_nomenclature_name == '' ) {
			$cpmu_bp_workshop_nomenclature_name = $this->cpmu_bp_workshop_nomenclature_name;
		}
		
		// set name option
		$this->db->option_set( 'cpmu_bp_workshop_nomenclature_name', $cpmu_bp_workshop_nomenclature_name );
		
		
		
		// get plural option
		$cpmu_bp_workshop_nomenclature_plural = esc_sql( $cpmu_bp_workshop_nomenclature_plural );
		
		// revert to default if we didn't get one...
		if ( $cpmu_bp_workshop_nomenclature_plural == '' ) {
			$cpmu_bp_workshop_nomenclature_plural = $this->cpmu_bp_workshop_nomenclature_plural;
		}

		// set plural option
		$this->db->option_set( 'cpmu_bp_workshop_nomenclature_plural', $cpmu_bp_workshop_nomenclature_plural );
		
		
		
		// set slug option
		$cpmu_bp_workshop_nomenclature_slug = sanitize_title( $cpmu_bp_workshop_nomenclature_name );
		$this->db->option_set( 'cpmu_bp_workshop_nomenclature_slug', $cpmu_bp_workshop_nomenclature_slug );
		
	}
	
	
	
	
	
	
	/**
	 * @description: add our default BuddyPress-related settings
	 * @todo: 
	 *
	 */
	function _get_default_settings( $settings ) {
	
		// add our options
		$settings['cpmu_bp_workshop_nomenclature'] = $this->cpmu_bp_workshop_nomenclature;
		$settings['cpmu_bp_workshop_nomenclature_name'] = $this->cpmu_bp_workshop_nomenclature_name;
		$settings['cpmu_bp_workshop_nomenclature_plural'] = $this->cpmu_bp_workshop_nomenclature_plural;
		$settings['cpmu_bp_workshop_nomenclature_slug'] = $this->cpmu_bp_workshop_nomenclature_slug;
		
		// --<
		return $settings;
		
	}
	
	
	



	/**
	 * @description: get legacy name when already set
	 */
	function _get_legacy_name() {
	
		// --<
		return __( 'Workshop', 'commentpress-core' );
		
	}
	
	
	



	/**
	 * @description: get legacy plural name when already set
	 */
	function _get_legacy_plural() {
	
		// --<
		return __( 'Workshops', 'commentpress-core' );
		
	}
	
	
	



	/**
	 * @description: get legacy slug when already set
	 */
	function _get_legacy_slug() {
	
		// --<
		return 'workshop';
		
	}
	
	
	



//##############################################################################
	
	
	



} // class ends
	
	
	




