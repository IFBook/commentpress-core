<?php
/**
 * CommentPress Core Theme class.
 *
 * Handles theme functionality in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Theme Class.
 *
 * This class provides theme functionality in CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Theme {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * Constructor.
	 *
	 * @since 4.0
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
	 * Initialises this object.
	 *
	 * @since 4.0
	 */
	public function initialise() {

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

	}

	// -------------------------------------------------------------------------

	/**
	 * Returns the name of the default sidebar.
	 *
	 * @since 3.4
	 *
	 * @return str $return The code for the default sidebar.
	 */
	public function get_default_sidebar() {

		/**
		 * Filters the default sidebar.
		 *
		 * @since 3.9.8
		 *
		 * @param str The default sidebar. Defaults to 'activity'.
		 */
		$return = apply_filters( 'commentpress_default_sidebar', 'activity' );

		// Is this a commentable Page?
		if ( ! $this->core->parser->is_commentable() ) {

			// No - we must use either 'activity' or 'toc'.
			if ( $this->core->db->option_exists( 'cp_sidebar_default' ) ) {

				// Get option (we don't need to look at the Page meta in this case).
				$default = $this->core->db->option_get( 'cp_sidebar_default' );

				// Use it unless it's 'comments'.
				if ( $default != 'comments' ) {
					$return = $default;
				}

			}

			// --<
			return $return;

		}

		/*
		// Get CPTs.
		//$types = $this->get_commentable_cpts();

		// Testing what we do with CPTs.
		//if ( is_singular() || is_singular( $types ) ) {
		*/

		// Is it a commentable Page?
		if ( is_singular() ) {

			/*
			 * Some people have reported that db is not an object at this point,
			 * though I cannot figure out how this might be occurring - so we
			 * avoid the issue by checking if it is.
			 */
			if ( is_object( $this->core->db ) ) {

				// Is it a Special Page which have Comments-in-Page (or are not commentable)?
				if ( ! $this->core->pages_legacy->is_special_page() ) {

					// Access Page.
					global $post;

					// Either 'comments', 'activity' or 'toc'.
					if ( $this->core->db->option_exists( 'cp_sidebar_default' ) ) {

						// Get global option.
						$return = $this->core->db->option_get( 'cp_sidebar_default' );

						// Check if the Post/Page has a meta value.
						$key = '_cp_sidebar_default';

						// If the custom field already has a value.
						if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

							// Get it.
							$return = get_post_meta( $post->ID, $key, true );

						}

					}

					// --<
					return $return;

				}

			}

		}

		// Not singular - must be either "activity" or "toc".
		if ( $this->core->db->option_exists( 'cp_sidebar_default' ) ) {

			// Use default unless it's 'comments'.
			$default = $this->core->db->option_get( 'cp_sidebar_default' );
			if ( $default != 'comments' ) {
				$return = $default;
			}

		}

		// --<
		return $return;

	}

	/**
	 * Get the order of the sidebars.
	 *
	 * @since 3.4
	 *
	 * @return array $order Sidebars in order of display.
	 */
	public function get_sidebar_order() {

		/**
		 * Filters the default tab order.
		 *
		 * @since 3.4
		 *
		 * @param array $order The default tab order array.
		 */
		$order = apply_filters( 'cp_sidebar_tab_order', [ 'contents', 'comments', 'activity' ] );

		// --<
		return $order;

	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieves the commentable Post Types.
	 *
	 * @since 3.4
	 *
	 * @return array $types The array of commentable Post Types.
	 */
	public function get_commentable_cpts() {

		// Init.
		$types = [];

		// TODO: Exactly how do we support Post Types?
		$args = [
			//'public' => true,
			'_builtin' => false,
		];

		$output = 'names'; // Can be "names" or "objects" - "names" is the default.
		$operator = 'and'; // Can be "and" or "or".

		// Get Post Types.
		$post_types = get_post_types( $args, $output, $operator );

		// Did we get any?
		if ( empty( $post_types ) ) {
			return $types;
		}

		// Loop.
		foreach ( $post_types as $post_type ) {

			// Decision goes here.

			// Add name to array (is_singular expects this).
			$types[] = $post_type;

		}

		// --<
		return $types;

	}

}
