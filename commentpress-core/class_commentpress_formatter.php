<?php /*
================================================================================
Class CommentpressCoreFormatter
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This class provides "Prose" and "Poetry" formatting to CommentPress Core.

--------------------------------------------------------------------------------
*/






/*
================================================================================
Class Name
================================================================================
*/

class CommentpressCoreFormatter {






	/*
	============================================================================
	Properties
	============================================================================
	*/
	
	// parent object reference
	var $parent_obj;
	
	// database object
	var $db;
	
	
	



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
	function CommentpressCoreFormatter( $parent_obj = null ) {
		
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
	
	
	



	/** 
	 * @description: override the name of the type dropdown label
	 * @todo: 
	 *
	 */
	function blog_type_label( $name ) {
	
		return apply_filters( 
			
			'cp_class_commentpress_formatter_label', 
			__( 'Default Text Format', 'commentpress-plugin' )
		
		);
		
	}
	
	
	
	
	
	
	/** 
	 * @description: define the "types" of groupblog 
	 * @todo: 
	 *
	 */
	function blog_type_options( $existing_options ) {
	
		// define types
		$types = array(
		
			'Prose', // types[0]
			'Poetry', // types[1]
		
		);
		
		// --<
		return apply_filters( 
			
			'cp_class_commentpress_formatter_types', 
			$types
		
		);
		
	}
	
	
	
	
	
	
	/** 
	 * @description: choose content formatter by blog type or post meta value
	 * @todo: 
	 *
	 */
	function content_formatter( $formatter ) {
		
		// access globals
		global $post;

		// set post meta key
		$key = '_cp_post_type_override';
		
		// default to current blog type
		$type = $this->db->option_get( 'cp_blog_type' );
		
		// but, if the custom field has a value...
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {
		
			// get it
			$type = get_post_meta( $post->ID, $key, true );
			
		}
		
		// act on it
		switch ( $type ) {
			
			// prose
			case '0' :
				
				$formatter = 'tag';
				break;
			
			// poetry
			case '1' :
			
				$formatter = 'line';
				break;
			
		}
	
	
	
		// --<
		return apply_filters( 
			
			'cp_class_commentpress_formatter_format', 
			$formatter
		
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
	
		// register hooks
		$this->_register_hooks();
		
	}
	
	
	



	/** 
	 * @description: register Wordpress hooks
	 * @todo: 
	 *
	 */
	function _register_hooks() {
		
		// set blog type options
		add_filter( 'cp_blog_type_options', array( $this, 'blog_type_options' ), 21 );
		
		// set blog type options label
		add_filter( 'cp_blog_type_label', array( $this, 'blog_type_label' ), 21 );
		
		// add filter for CommentPress Core formatter
		add_filter( 'cp_select_content_formatter', array( $this, 'content_formatter' ), 21, 1 );

		// is this the back end?
		if ( is_admin() ) {
	
		}
		
	}
	
	
	



//##############################################################################
	
	
	



} // class ends
	
	
	




