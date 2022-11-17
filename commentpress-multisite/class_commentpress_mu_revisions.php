<?php
/**
 * CommentPress Core Multisite Revisions class.
 *
 * Overrides the way that new post revisions are named.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Multisite Revisions Class.
 *
 * This class overrides the way that new post revisions are named.
 *
 * @since 3.3
 */
class CommentPress_Multisite_Revisions {

	/**
	 * Multisite plugin object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $ms_loader The multisite plugin object.
	 */
	public $ms_loader;

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param object $ms_loader Reference to the multisite plugin object.
	 */
	public function __construct( $ms_loader ) {

		// Store reference to multisite plugin object.
		$this->ms_loader = $ms_loader;

		// Init when the multisite plugin is fully loaded.
		add_action( 'commentpress/core/multisite/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Set up all items associated with this object.
	 *
	 * @since 3.3
	 */
	public function initialise() {

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.3
	 */
	public function register_hooks() {

		// Add filter for new post title prefix.
		add_filter( 'commentpress_new_post_title_prefix', [ $this, 'new_post_title_prefix' ], 21, 1 );

		// Add filter for new post title.
		add_filter( 'commentpress_new_post_title', [ $this, 'new_post_title' ], 21, 2 );

	}

	// -------------------------------------------------------------------------

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

}
