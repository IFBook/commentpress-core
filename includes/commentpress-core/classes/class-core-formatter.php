<?php
/**
 * CommentPress Core Formatter class.
 *
 * Handles "Prose" and "Poetry" formatting in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Formatter Class.
 *
 * This class provides "Prose" and "Poetry" formatting to CommentPress Core.
 *
 * @since 3.3
 */
class CommentPress_Core_Formatter {

	/**
	 * Core loader object.
	 *
	 * @since 3.3
	 * @since 4.0 Renamed.
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param object $core Reference to the core plugin object.
	 */
	public function __construct( $core ) {

		// Store reference to core plugin object.
		$this->core = $core;

		// Init when this plugin is fully loaded.
		add_action( 'commentpress/core/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Sets up all items associated with this object.
	 *
	 * @since 3.3
	 */
	public function initialise() {

		// Register hooks.
		$this->register_hooks();

	}

	// -------------------------------------------------------------------------

	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.3
	 */
	public function register_hooks() {

		// Set blog type options.
		add_filter( 'cp_blog_type_options', [ $this, 'blog_type_options' ], 21 );

		// Set blog type options label.
		add_filter( 'cp_blog_type_label', [ $this, 'blog_type_label' ], 21 );

		// Add filter for CommentPress Core formatter.
		add_filter( 'cp_select_content_formatter', [ $this, 'content_formatter' ], 21, 1 );

		// Save post formatter - this overrides "blog_type".
		add_action( 'commentpress/core/db/page_meta/saved', [ $this, 'save_formatter' ] );
		add_action( 'commentpress/core/db/post_meta/saved', [ $this, 'save_formatter' ] );

	}

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
			__( 'Prose', 'commentpress-core' ), // Types[0].
			__( 'Poetry', 'commentpress-core' ), // Types[1].
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
		$type = $this->core->db->option_get( 'cp_blog_type' );

		// But, if the custom field has a value.
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {

			// Get it.
			$type = get_post_meta( $post->ID, $key, true );

		}

		// Act on it.
		switch ( $type ) {

			// Prose.
			case '0':
				$formatter = 'tag';
				break;

			// Poetry.
			case '1':
				$formatter = 'line';
				break;

		}

		// --<
		return apply_filters(
			'cp_class_commentpress_formatter_format',
			$formatter
		);

	}

	/**
	 * Override post formatter.
	 *
	 * This overrides the "blog_type" for a post.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @param object $post The post object.
	 */
	public function save_formatter( $post ) {

		// Get the data.
		$data = isset( $_POST['cp_post_type_override'] ) ? sanitize_text_field( wp_unslash( $_POST['cp_post_type_override'] ) ) : '';

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

}
