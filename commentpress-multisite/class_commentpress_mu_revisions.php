<?php

/**
 * CommentPress Core Multisite Revisions Class.
 *
 * This class overrides the way that new post revisions are named.
 *
 * @since 3.3
 */
class Commentpress_Multisite_Revisions {

	/**
	 * Plugin object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $parent_obj The plugin object.
	 */
	public $parent_obj;

	/**
	 * Database interaction object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $db The database object.
	 */
	public $db;



	/**
	 * Initialises this object.
	 *
	 * @since 3.3
	 *
	 * @param object $parent_obj The reference to the parent object.
	 */
	public function __construct( $parent_obj = null ) {

		// Store reference to "parent" (calling obj, not OOP parent).
		$this->parent_obj = $parent_obj;

		// Store reference to database wrapper (child of calling obj).
		$this->db = $this->parent_obj->db;

		// Init.
		$this->_init();

	}



	/**
	 * Set up all items associated with this object.
	 *
	 * @since 3.3
	 */
	public function initialise() {

	}



	/**
	 * If needed, destroys all items associated with this object.
	 *
	 * @since 3.3
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
	 * Amend the post title prefix.
	 *
	 * @since 3.3
	 *
	 * @param str $prefix The existing post title prefix.
	 * @return str An empty string.
	 */
	public function new_post_title_prefix( $prefix ) {

		// Don't use a prefix.
		return '';

	}



	/**
	 * Add suffix " - Draft N", where N is the latest version number.
	 *
	 * @since 3.3
	 *
	 * @param str $title The existing title of the post.
	 * @param object $post The WordPress post object.
	 * @return str $title The modified title of the post.
	 */
	public function new_post_title( $title, $post ) {

		// Get incremental version number of source post.
		$key = '_cp_version_count';

		// If the custom field of our current post has a value.
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {

			// Get current value.
			$value = get_post_meta( $post->ID, $key, true );

			// Increment.
			$value++;

		} else {

			// This must be the first new version (Draft 2).
			$value = 2;

		}

		// Do we already have our suffix in the title?
		if ( stristr( $title, ' - Draft ' ) === false ) {

			// No, append " - Draft N".
			$title = $title . ' - Draft ' . $value;

		} else {

			// Yes, split.
			$title_array = explode( ' - Draft ', $title );

			// Append to first part.
			$title = $title_array[0] . ' - Draft ' . $value;

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
	 * Object initialisation.
	 *
	 * @since 3.3
	 */
	public function _init() {

		// Register hooks.
		$this->_register_hooks();

	}



	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.3
	 */
	public function _register_hooks() {

		// Add filter for new post title prefix.
		add_filter( 'commentpress_new_post_title_prefix', [ $this, 'new_post_title_prefix' ], 21, 1 );

		// Add filter for new post title.
		add_filter( 'commentpress_new_post_title', [ $this, 'new_post_title' ], 21, 2 );

	}



//##############################################################################



} // Class ends.



