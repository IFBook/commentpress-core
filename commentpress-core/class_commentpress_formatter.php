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



	/**
	 * Properties
	 */

	// parent object reference
	public $parent_obj;

	// database object
	public $db;



	/**
	 * Initialises this object
	 *
	 * @param object $parent_obj A reference to the parent object
	 * @return object
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
	 * Set up all items associated with this object
	 *
	 * @return void
	 */
	public function initialise() {

	}



	/**
	 * If needed, destroys all items associated with this object
	 *
	 * @return void
	 */
	public function destroy() {

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Public Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Override the name of the type dropdown label
	 *
	 * @param str $name The existing name of the label
	 * @return str $name The modified name of the label
	 */
	public function blog_type_label( $name ) {

		return apply_filters(

			'cp_class_commentpress_formatter_label',
			__( 'Default Text Format', 'commentpress-core' )

		);

	}



	/**
	 * Define the "types" of groupblog
	 *
	 * @param array $existing_options The existing types of groupblog
	 * @return array $existing_options The modified types of groupblog
	 */
	public function blog_type_options( $existing_options ) {

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
	 * Choose content formatter by blog type or post meta value
	 *
	 * @param str $formatter The existing formatter code
	 * @return str $formatter The existing formatter code
	 */
	public function content_formatter( $formatter ) {

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



	/**
	 * -------------------------------------------------------------------------
	 * Private Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Object initialisation
	 *
	 * @return void
	 */
	function _init() {

		// register hooks
		$this->_register_hooks();

	}



	/**
	 * Register Wordpress hooks
	 *
	 * @return void
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



