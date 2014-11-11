<?php /*
================================================================================
Class CommentpressMultisiteRevisions Version 1.0
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This class overrides the way that new post revisions are named

--------------------------------------------------------------------------------
*/



/*
================================================================================
Class Name
================================================================================
*/

class CommentpressMultisiteRevisions {



	/**
	 * Properties
	 */

	// parent object reference
	public $parent_obj;

	// admin object reference
	public $db;



	/**
	 * Initialises this object
	 *
	 * @param object $parent_obj a reference to the parent object
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
	 * Amend the post title prefix
	 *
	 * @return str An empty string
	 */
	public function new_post_title_prefix( $prefix ) {

		// don't use a prefix
		return '';

	}



	/**
	 * Add suffix " - Draft N", where N is the latest version number
	 *
	 * @param str $title The existing title of the post
	 * @param object $post The WordPress post object
	 * @return str $title The modified title of the post
	 */
	public function new_post_title( $title, $post ) {

		// get incremental version number of source post
		$key = '_cp_version_count';

		// if the custom field of our current post has a value...
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {

			// get current value
			$value = get_post_meta( $post->ID, $key, true );

			// increment
			$value++;

		} else {

			// this must be the first new version (Draft 2)
			$value = 2;

		}

		// do we already have our suffix in the title?
		if ( stristr( $title, ' - Draft ' ) === false ) {

			// no, append " - Draft N"
			$title = $title.' - Draft '.$value;

		} else {

			// yes, split
			$title_array = explode( ' - Draft ', $title );

			// append to first part
			$title = $title_array[0].' - Draft '.$value;

		}

		// --<
		return $title;

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

		// add filter for new post title prefix
		add_filter( 'commentpress_new_post_title_prefix', array( $this, 'new_post_title_prefix' ), 21, 1 );

		// add filter for new post title
		add_filter( 'commentpress_new_post_title', array( $this, 'new_post_title' ), 21, 2 );

	}



//##############################################################################



} // class ends



