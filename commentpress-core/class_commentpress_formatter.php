<?php

/**
 * CommentPress Core Formatter Class.
 *
 * This class provides "Prose" and "Poetry" formatting to CommentPress Core.
 *
 * @since 3.3
 */
class Commentpress_Core_Formatter {

	/**
	 * Plugin object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $parent_obj The plugin object.
	 */
	public $parent_obj;

	/**
	 * Database interaction object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $db The database object.
	 */
	public $db;



	/**
	 * Initialises this object.
	 *
	 * @since 3.3
	 *
	 * @param object $parent_obj A reference to the parent object.
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
	 * Override the name of the type dropdown label.
	 *
	 * @since 3.3
	 *
	 * @param str $name The existing name of the label.
	 * @return str $name The modified name of the label.
	 */
	public function blog_type_label( $name ) {

		return apply_filters(
			'cp_class_commentpress_formatter_label',
			__( 'Default Text Format', 'commentpress-core' )
		);

	}



	/**
	 * Define the "types" of groupblog.
	 *
	 * @since 3.3
	 *
	 * @param array $existing_options The existing types of groupblog.
	 * @return array $existing_options The modified types of groupblog.
	 */
	public function blog_type_options( $existing_options ) {

		// Define types.
		$types = [
			__( 'Prose', 'commentpress-core' ), // Types[0]
			__( 'Poetry', 'commentpress-core' ), // Types[1]
		];

		// --<
		return apply_filters(
			'cp_class_commentpress_formatter_types',
			$types
		);

	}



	/**
	 * Choose content formatter by blog type or post meta value.
	 *
	 * @since 3.3
	 *
	 * @param str $formatter The existing formatter code.
	 * @return str $formatter The existing formatter code.
	 */
	public function content_formatter( $formatter ) {

		// Access globals.
		global $post;

		// Set post meta key.
		$key = '_cp_post_type_override';

		// Default to current blog type.
		$type = $this->db->option_get( 'cp_blog_type' );

		// But, if the custom field has a value.
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {

			// Get it.
			$type = get_post_meta( $post->ID, $key, true );

		}

		// Act on it.
		switch ( $type ) {

			// Prose.
			case '0' :

				$formatter = 'tag';
				break;

			// Poetry.
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

		// Set blog type options.
		add_filter( 'cp_blog_type_options', [ $this, 'blog_type_options' ], 21 );

		// Set blog type options label.
		add_filter( 'cp_blog_type_label', [ $this, 'blog_type_label' ], 21 );

		// Add filter for CommentPress Core formatter.
		add_filter( 'cp_select_content_formatter', [ $this, 'content_formatter' ], 21, 1 );

		// Is this the back end?
		if ( is_admin() ) {

		}

	}



//##############################################################################



} // Class ends.



