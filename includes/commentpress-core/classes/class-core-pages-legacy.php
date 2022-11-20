<?php
/**
 * CommentPress Core Legacy Pages class.
 *
 * Handles functionality related to legacy "Special Pages" in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Legacy Pages Class.
 *
 * This class provides functionality related to legacy "Special Pages" in CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Pages_Legacy {

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

		// Exclude Special Pages from listings.
		add_filter( 'wp_list_pages_excludes', [ $this, 'exclude_special_pages' ], 10, 1 );
		add_filter( 'parse_query', [ $this, 'exclude_special_pages_from_admin' ], 10, 1 );

		// Modify all.
		add_filter( 'views_edit-page', [ $this, 'update_page_counts_in_admin' ], 10, 1 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Exclude Special Pages from Page listings.
	 *
	 * @since 3.4
	 *
	 * @param array $excluded_array The existing list of excluded Pages.
	 * @return array $excluded_array The modified list of excluded Pages.
	 */
	public function exclude_special_pages( $excluded_array ) {

		// Get Special Pages array, if it's there.
		$special_pages = $this->db->option_get( 'cp_special_pages' );

		// Do we have an array?
		if ( is_array( $special_pages ) ) {

			// Merge and make unique.
			$excluded_array = array_unique( array_merge( $excluded_array, $special_pages ) );

		}

		// --<
		return $excluded_array;

	}

	/**
	 * Exclude Special Pages from Admin Page listings.
	 *
	 * @since 3.4
	 *
	 * @param array $query The existing Page query.
	 */
	public function exclude_special_pages_from_admin( $query ) {

		global $pagenow, $post_type;

		// Check admin location.
		if ( is_admin() && $pagenow == 'edit.php' && $post_type == 'page' ) {

			// Get Special Pages array, if it's there.
			$special_pages = $this->db->option_get( 'cp_special_pages' );

			// Do we have an array?
			if ( is_array( $special_pages ) && count( $special_pages ) > 0 ) {

				// Modify query.
				$query->query_vars['post__not_in'] = $special_pages;

			}

		}

	}

	/**
	 * Page counts still need amending.
	 *
	 * @since 3.4
	 *
	 * @param array $vars The existing variables.
	 * @return array $vars The modified list of variables.
	 */
	public function update_page_counts_in_admin( $vars ) {

		global $pagenow, $post_type;

		// Check admin location.
		if ( is_admin() && $pagenow == 'edit.php' && $post_type == 'page' ) {

			// Get Special Pages array, if it's there.
			$special_pages = $this->db->option_get( 'cp_special_pages' );

			// Do we have an array?
			if ( is_array( $special_pages ) ) {

				/**
				 * Data comes in like this:
				 *
				 * [all] => <a href='edit.php?post_type=page' class="current">All <span class="count">(8)</span></a>
				 * [publish] => <a href='edit.php?post_status=publish&amp;post_type=page'>Published <span class="count">(8)</span></a>
				 */

				// Capture existing value enclosed in brackets.
				preg_match( '/\((\d+)\)/', $vars['all'], $matches );

				// Did we get a result?
				if ( isset( $matches[1] ) ) {

					// Subtract Special Page count.
					$new_count = $matches[1] - count( $special_pages );

					// Rebuild 'all' and 'publish' items.
					$vars['all'] = preg_replace(
						'/\(\d+\)/',
						'(' . $new_count . ')',
						$vars['all']
					);

				}

				// Capture existing value enclosed in brackets.
				preg_match( '/\((\d+)\)/', $vars['publish'], $matches );

				// Did we get a result?
				if ( isset( $matches[1] ) ) {

					// Subtract Special Page count.
					$new_count = $matches[1] - count( $special_pages );

					// Rebuild 'all' and 'publish' items.
					$vars['publish'] = preg_replace(
						'/\(\d+\)/',
						'(' . $new_count . ')',
						$vars['publish']
					);

				}

			}

		}

		// --<
		return $vars;

	}

}
