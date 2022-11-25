<?php
/**
 * CommentPress Core Navigation class.
 *
 * Handles navigating Pages in whatever hierarchy or relationship they have been assigned.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Navigation Class.
 *
 * This class is a wrapper for navigating Pages in whatever hierarchy or
 * relationship they have been assigned.
 *
 * @since 3.0
 */
class CommentPress_Core_Navigator {

	/**
	 * Core loader object.
	 *
	 * @since 3.0
	 * @since 4.0 Renamed.
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * Next Pages array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $next_pages The "Next Pages" array.
	 */
	public $next_pages = [];

	/**
	 * Previous Pages array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $previous_pages The "Previous Pages" array.
	 */
	public $previous_pages = [];

	/**
	 * Next Posts array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $next_posts The "Next Posts" array.
	 */
	public $next_posts = [];

	/**
	 * Previous Posts array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $previous_posts The "Previous Posts" array.
	 */
	public $previous_posts = [];

	/**
	 * Page numbers array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $page_numbers The Page numbers array.
	 */
	public $page_numbers = [];

	/**
	 * Menu objects array, when using custom Menu.
	 *
	 * @since 3.3
	 * @access public
	 * @var array $menu_objects The Menu objects array.
	 */
	public $menu_objects = [];

	/**
	 * Page navigation enabled flag.
	 *
	 * @since 3.8.10
	 * @access public
	 * @var bool $nav_enabled True if Page Navigation is enabled, false otherwise.
	 */
	public $nav_enabled = true;

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 *
	 * @param object $core Reference to the core plugin object.
	 */
	public function __construct( $core ) {

		// Store reference to parent.
		$this->core = $core;

		// Init when this plugin is fully loaded.
		add_action( 'commentpress/core/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this object.
	 *
	 * @since 3.0
	 */
	public function initialise() {

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Register hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

		/*
		 * We need template functions - e.g. is_page() and is_single() - to be
		 * defined, so we set up this object when the "wp_head" action is fired.
		 */
		add_action( 'wp_head', [ $this, 'setup_items' ] );

		// Add template redirect for TOC behaviour.
		add_action( 'template_redirect', [ $this, 'redirect_to_child' ] );

	}

	/**
	 * Set up all items associated with this object.
	 *
	 * @since 4.0
	 */
	public function setup_items() {

		// If we're navigating Pages.
		if ( is_page() ) {

			// Check Page Navigation flag.
			if ( $this->page_nav_is_disabled() ) {

				// Remove Page Navigation via filter.
				add_filter( 'cp_template_page_navigation', [ $this, 'page_nav_disable' ], 100, 1 );

				// Save flag.
				$this->nav_enabled = false;

			}

			// Init Page lists.
			$this->init_page_lists();

		}

		// If we're navigating Posts or attachments.
		if ( is_single() || is_attachment() ) {

			// Init Posts lists.
			$this->init_posts_lists();

		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Disable Page Navigation when on a "page".
	 *
	 * @since 3.8.10
	 *
	 * @param str $template The existing path to the navigation template.
	 * @return str $template An empty path to disable navigation.
	 */
	public function page_nav_disable( $template ) {

		// Disable for "Page" Post Type.
		return '';

	}

	/**
	 * Check if Page Navigation is disabled when on a "Page".
	 *
	 * @since 3.9
	 *
	 * @return bool True if navigation is disabled, fasle otherwise.
	 */
	public function page_nav_is_disabled() {

		// Check Page Navigation option.
		if (
			$this->core->db->option_exists( 'cp_page_nav_enabled' ) &&
			$this->core->db->option_get( 'cp_page_nav_enabled', 'y' ) == 'n'
		) {

			// Overwrite flag.
			$this->nav_enabled = false;

		}

		// Return the opposite.
		return $this->nav_enabled ? false : true;

	}

	/**
	 * Get "Next Page" object.
	 *
	 * @since 3.0
	 *
	 * @param bool $with_comments The requested Page has Comments. Default false.
	 * @return object|bool $next_page The WordPress Post object, or false on failure.
	 */
	public function get_next_page( $with_comments = false ) {

		// Do we have any subsequent Pages?
		if ( count( $this->next_pages ) > 0 ) {

			// Are we asking for Comments?
			if ( $with_comments ) {

				// Find the first with Comments.
				foreach ( $this->next_pages as $next_page ) {
					if ( $next_page->comment_count > 0 ) {
						return $next_page;
					}
				}

			} else {

				// Return the first on the stack.
				return $this->next_pages[0];

			}

		}

		// Check if the supplied Title Page is the homepage and this is it.
		$title_id = $this->is_title_page_the_homepage();
		if ( $title_id !== false && is_front_page() ) {

			// Get the first readable Page.
			$first_id = $this->get_first_page();

			// Return the Post object.
			return get_post( $first_id );

		}

		// --<
		return false;

	}

	/**
	 * Get "Previous Page" object.
	 *
	 * @since 3.0
	 *
	 * @param bool $with_comments The requested Page has Comments. Default false.
	 * @return object|bool $previous_page The WordPress Post object, or false on failure.
	 */
	public function get_previous_page( $with_comments = false ) {

		// Do we have any previous Pages?
		if ( count( $this->previous_pages ) > 0 ) {

			// Are we asking for Comments?
			if ( $with_comments ) {

				// Find the first with Comments.
				foreach ( $this->previous_pages as $previous_page ) {
					if ( $previous_page->comment_count > 0 ) {
						return $previous_page;
					}
				}

			} else {

				// Return the first on the stack.
				return $this->previous_pages[0];

			}

		}

		// This must be the first Page.

		// We still need to check if the supplied Title Page is the homepage.
		$title_id = $this->is_title_page_the_homepage();
		if ( $title_id !== false && ! is_front_page() ) {
			return get_post( $title_id );
		}

		// --<
		return false;

	}

	/**
	 * Get "Next Post" object.
	 *
	 * @since 3.0
	 *
	 * @param bool $with_comments The requested Post has Comments. Default false.
	 * @return object|bool $next_post The WordPress Post object, or false on failure.
	 */
	public function get_next_post( $with_comments = false ) {

		// Do we have any subsequent Posts?
		if ( count( $this->next_posts ) > 0 ) {

			// Are we asking for Comments?
			if ( $with_comments ) {

				// Find the first with Comments.
				foreach ( $this->next_posts as $next_post ) {
					if ( $next_post->comment_count > 0 ) {
						return $next_post;
					}
				}

			} else {

				// Return the first on the stack.
				return $this->next_posts[0];

			}

		}

		// --<
		return false;

	}

	/**
	 * Get "Previous Post" link.
	 *
	 * @since 3.0
	 *
	 * @param bool $with_comments The requested Post has Comments. Default false.
	 * @return object|bool $previous_post The WordPress Post object, or false on failure.
	 */
	public function get_previous_post( $with_comments = false ) {

		// Do we have any previous Posts?
		if ( count( $this->previous_posts ) > 0 ) {

			// Are we asking for Comments?
			if ( $with_comments ) {

				// Find the first with Comments.
				foreach ( $this->previous_posts as $previous_post ) {
					if ( $previous_post->comment_count > 0 ) {
						return $previous_post;
					}
				}

			} else {

				// Return the first on the stack.
				return $this->previous_posts[0];

			}

		}

		// --<
		return false;

	}

	/**
	 * Get first viewable child Page.
	 *
	 * @since 3.0
	 *
	 * @param int $page_id The Page ID.
	 * @return int|bool $first_child The ID of the first child Page, or false if not found.
	 */
	public function get_first_child( $page_id ) {

		// Init to look for published Pages.
		$defaults = [
			'post_parent' => $page_id,
			'post_type' => 'page',
			'numberposts' => -1,
			'post_status' => 'publish',
			'orderby' => 'menu_order, post_title',
			'order' => 'ASC',
		];

		// Get Page children.
		$children = get_children( $defaults );
		$kids =& $children;

		// Do we have any?
		if ( empty( $kids ) ) {

			// No children.
			return false;

		}

		// We got some.
		return $this->get_first_child_recursive( $kids );

	}

	/**
	 * Get list of "Document" Pages.
	 *
	 * @since 3.0
	 *
	 * @param str $mode Either 'structural' or 'readable'.
	 * @return array $pages All "Document" Pages.
	 */
	public function get_book_pages( $mode = 'readable' ) {

		// Init.
		$all_pages = [];

		// Parse Menu if we have one.
		if ( has_nav_menu( 'toc' ) ) {
			$all_pages = $this->parse_menu( $mode );
			return $all_pages;
		}

		// Fall back to parsing the Page order.
		$all_pages = $this->parse_pages( $mode );

		// --<
		return $all_pages;

	}

	/**
	 * Get first readable "Document" Page.
	 *
	 * @since 3.0
	 *
	 * @return int $id The ID of the first Page (or false if not found).
	 */
	public function get_first_page() {

		// Init.
		$id = false;

		// Get all Pages including Chapters.
		$all_pages = $this->get_book_pages( 'structural' );

		// If we have any Pages.
		if ( count( $all_pages ) > 0 ) {

			// Get first ID.
			$id = $all_pages[0]->ID;

		}

		// --<
		return $id;

	}

	/**
	 * Get Page number.
	 *
	 * @since 3.0
	 *
	 * @param int $page_id The Page ID.
	 * @return int|bool $number The number of the Page, or false on failure.
	 */
	public function get_page_number( $page_id ) {

		// Bail if Page nav is disabled.
		if ( $this->nav_enabled === false ) {
			return false;
		}

		// Init.
		$num = 0;

		// Access Post.
		global $post;

		// Are parent Pages viewable?
		$viewable = ( $this->core->db->option_get( 'cp_toc_chapter_is_page' ) == '1' ) ? true : false;

		// If they are.
		if ( $viewable ) {

			// Get Page number from array.
			$num = $this->get_page_num( $page_id );

		} else {

			// Get the ID of first viewable child.
			$first_child = $this->get_first_child( $post->ID );

			// If this is a childless Page.
			if ( ! $first_child ) {

				// Get Page number from array.
				$num = $this->get_page_num( $page_id );

			}

		}

		/**
		 * Filters the Page Number.
		 *
		 * @since 3.0
		 *
		 * @param int|bool $number The number of the Page, or false if not found.
		 */
		$num = apply_filters( 'cp_nav_page_num', $num );

		// --<
		return $num;

	}

	/**
	 * Get Page number.
	 *
	 * @since 3.0
	 *
	 * @param int $page_id The Page ID.
	 * @return int $number The number of the Page.
	 */
	public function get_page_num( $page_id ) {

		// Init.
		$num = 0;

		// Get from array.
		if ( array_key_exists( $page_id, $this->page_numbers ) ) {

			// Get it.
			$num = $this->page_numbers[ $page_id ];

		}

		// --<
		return $num;

	}

	/**
	 * Redirect to child.
	 *
	 * @since 3.3
	 */
	public function redirect_to_child() {

		// Only on Pages.
		if ( ! is_page() ) {
			return;
		}

		// Bail if this is a BuddyPress Page.
		if ( $this->core->bp->is_buddypress_special_page() ) {
			return;
		}

		// Bail if we have a custom Menu.
		// TODO: we need to parse the Menu to find the viewable child.
		if ( has_nav_menu( 'toc' ) ) {
			return;
		}

		// Access Post object.
		global $post;

		// Bail if not a WordPress Post.
		if ( ! ( $post instanceof WP_Post ) ) {
			return;
		}

		// Are parent Pages viewable?
		$viewable = ( $this->core->db->option_get( 'cp_toc_chapter_is_page' ) == '1' ) ? true : false;

		// Get the ID of the first child.
		$first_child = $this->get_first_child( $post->ID );

		// Our conditions.
		if ( $first_child && ! $viewable ) {

			// Get link.
			$redirect = get_permalink( $first_child );

			// Do the redirect.
			wp_safe_redirect( $redirect );
			exit();

		}

	}

	/**
	 * Builds the next and previous Page lists.
	 *
	 * @since 3.3
	 */
	public function init_page_lists() {

		// Get all Pages.
		$all_pages = $this->get_book_pages( 'readable' );

		// Bail if we have no Pages.
		if ( empty( $all_pages ) ) {
			return;
		}

		// Generate Page numbers.
		$this->generate_page_numbers( $all_pages );

		// Access Post object.
		global $post;

		// Init the key we want.
		$page_key = false;

		// Loop.
		foreach ( $all_pages as $key => $page_obj ) {

			// Is it the currently viewed Page?
			if ( $page_obj->ID == $post->ID ) {

				// Set Page key.
				$page_key = $key;

				// Break to preserve key.
				break;

			}

		}

		// If we don't get a key, the current Page is a Chapter and not a Page.
		if ( $page_key === false ) {
			$this->next_pages = [];
			return;
		}

		// Will there be a next array?
		if ( isset( $all_pages[ $key + 1 ] ) ) {
			// Get all subsequent Pages.
			$this->next_pages = array_slice( $all_pages, $key + 1 );
		}

		// Will there be a previous array?
		if ( isset( $all_pages[ $key - 1 ] ) ) {
			// Get all previous Pages.
			$this->previous_pages = array_reverse( array_slice( $all_pages, 0, $key ) );
		}

	}

	/**
	 * Builds the "Next Posts" and "Previous Posts" list.
	 *
	 * @since 3.3
	 */
	public function init_posts_lists() {

		// Set defaults.
		$defaults = [
			'numberposts' => -1,
			'orderby' => 'date',
		];

		// Get them.
		$all_posts = get_posts( $defaults );

		// Bail of we have no Posts.
		if ( empty( $all_posts ) ) {
			return;
		}

		// Access Post object.
		global $post;

		// Loop.
		foreach ( $all_posts as $key => $post_obj ) {

			// Is it ours?
			if ( $post_obj->ID == $post->ID ) {

				// Break to preserve key.
				break;

			}

		}

		// Will there be a next array?
		if ( isset( $all_posts[ $key + 1 ] ) ) {
			// Get all subsequent Posts.
			$this->next_posts = array_slice( $all_posts, $key + 1 );
		}

		// Will there be a previous array?
		if ( isset( $all_posts[ $key - 1 ] ) ) {
			// Get all previous Posts.
			$this->previous_posts = array_reverse( array_slice( $all_posts, 0, $key ) );
		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Strip out all but lowest level Pages.
	 *
	 * @todo This only works one level deep?
	 *
	 * @since 3.0
	 *
	 * @param array $pages The array of Page objects.
	 * @return array $subpages All Sub-pages.
	 */
	public function filter_chapters( $pages ) {

		// Init return.
		$subpages = [];

		// If we have any.
		if ( count( $pages ) > 0 ) {

			// Loop.
			foreach ( $pages as $key => $page_obj ) {

				// Init to look for published Pages.
				$defaults = [
					'post_parent' => $page_obj->ID,
					'post_type' => 'page',
					'numberposts' => -1,
					'post_status' => 'publish',
				];

				// Get Page children.
				$children = get_children( $defaults );
				$kids =& $children;

				// Do we have any?
				if ( empty( $kids ) ) {
					// Add to our return array.
					$subpages[] = $page_obj;
				}

			}

		} // End have array check.

		// --<
		return $subpages;

	}

	/**
	 * Get first published child, however deep.
	 *
	 * @since 3.0
	 * @since 4.0 Renamed.
	 *
	 * @param array $pages The array of Page objects.
	 * @return array $subpages All Sub-pages.
	 */
	public function get_first_child_recursive( $pages ) {

		// If we have any.
		if ( count( $pages ) > 0 ) {

			// Loop.
			foreach ( $pages as $key => $page_obj ) {

				// Init to look for published Pages.
				$defaults = [
					'post_parent' => $page_obj->ID,
					'post_type' => 'page',
					'numberposts' => -1,
					'post_status' => 'publish',
					'orderby' => 'menu_order, post_title',
					'order' => 'ASC',
				];

				// Get Page children.
				$children = get_children( $defaults );
				$kids =& $children;

				// Do we have any?
				if ( ! empty( $kids ) ) {

					// Go deeper.
					return $this->get_first_child_recursive( $kids );

				} else {

					// Return first.
					return $page_obj->ID;

				}

			}

		}

		// --<
		return false;

	}

	/**
	 * Generates Page numbers.
	 *
	 * @todo Refine by section, Page meta value etc.
	 *
	 * @since 3.0
	 *
	 * @param array $pages The array of Page objects in the "Document".
	 */
	public function generate_page_numbers( $pages ) {

		// Bail if we have no Pages.
		if ( empty( $pages ) ) {
			return;
		}

		// Init with Page 1.
		$num = 1;

		// Check if we have a custom Menu.
		$has_nav_menu = false;
		if ( has_nav_menu( 'toc' ) ) {
			$has_nav_menu = true;
		}

		// Loop.
		foreach ( $pages as $page_obj ) {

			/**
			 * Get number format - the way this works in publications is that
			 * only prefaces are numbered with Roman numerals. So, we only allow
			 * the first top level Page to have the option of Roman numerals.
			 *
			 * If set, all child Pages will be set to Roman.
			 */

			// Once we run out of Roman numerals, $num is reset to 1.

			// Default to arabic.
			$format = 'arabic';

			// Set key.
			$key = '_cp_number_format';

			// If the custom field already has a value.
			if ( get_post_meta( $page_obj->ID, $key, true ) !== '' ) {

				// Get it.
				$format = get_post_meta( $page_obj->ID, $key, true );

			} else {

				// If we have a custom Menu.
				if ( $has_nav_menu ) {

					// Get top level Menu Item.
					$top_menu_item = $this->get_top_menu_obj( $page_obj );

					// Since this might not be a WP_POST object.
					if ( isset( $top_menu_item->object_id ) ) {

						// Get ID of top level parent.
						$top_page_id = $top_menu_item->object_id;

						// If the custom field has a value.
						if ( get_post_meta( $top_page_id, $key, true ) !== '' ) {

							// Get it.
							$format = get_post_meta( $top_page_id, $key, true );

						}

					}

				} else {

					// Get top level parent.
					$top_page_id = $this->get_top_parent_id( $page_obj->ID );

					// If the custom field has a value.
					if ( get_post_meta( $top_page_id, $key, true ) !== '' ) {

						// Get it.
						$format = get_post_meta( $top_page_id, $key, true );

					}

				}

			}

			// If it's roman.
			if ( $format == 'roman' ) {

				// Convert arabic to roman.
				$this->page_numbers[ $page_obj->ID ] = $this->number_to_roman( $num );

			} else {

				// If flag not set.
				if ( ! isset( $flag ) ) {

					// Reset num.
					$num = 1;

					// Set flag.
					$flag = true;

				}

				// Store roman.
				$this->page_numbers[ $page_obj->ID ] = $num;

			}

			// Increment.
			$num++;

		}

	}

	/**
	 * Utility to remove the Theme My Login Page.
	 *
	 * @since 3.0
	 *
	 * @param array $pages An array of Page objects.
	 * @return bool $clean The modified array pf Page objects.
	 */
	public function filter_theme_my_login_page( $pages ) {

		// Init return.
		$clean = [];

		// If we have any.
		if ( count( $pages ) > 0 ) {

			// Loop.
			foreach ( $pages as $page_obj ) {

				// Do we have any?
				if ( ! $this->detect_login_page( $page_obj ) ) {

					// Add to our return array.
					$clean[] = $page_obj;

				}

			}

		} // End have array check.

		// --<
		return $clean;

	}

	/**
	 * Utility to detect the Theme My Login Page.
	 *
	 * @since 3.0
	 *
	 * @param object $page_obj The WordPress Page object.
	 * @return boolean $success True if Theme My Login Page, false otherwise.
	 */
	public function detect_login_page( $page_obj ) {

		// Compat with Theme My Login.
		if (
			$page_obj->post_name == 'login' &&
			$page_obj->post_content == '[theme-my-login]'
		) {

			// --<
			return true;

		}

		// --<
		return false;

	}

	/**
	 * PHP Roman Numeral Library.
	 *
	 * Copyright (c) 2008, reusablecode.blogspot.com; some rights reserved.
	 *
	 * This work is licensed under the Creative Commons Attribution License. To view
	 * a copy of this license, visit http://creativecommons.org/licenses/by/3.0/ or
	 * send a letter to Creative Commons, 559 Nathan Abbott Way, Stanford, California
	 * 94305, USA.
	 *
	 * Utility to convert arabic to roman numerals.
	 *
	 * @since 3.0
	 *
	 * @param int $arabic The numeric Arabic value.
	 * @return str $roman The Roman equivalent.
	 */
	public function number_to_roman( $arabic ) {

		$ones = [ '', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX' ];
		$tens = [ '', 'X', 'XX', 'XXX', 'XL', 'L', 'LX', 'LXX', 'LXXX', 'XC' ];
		$hundreds = [ '', 'C', 'CC', 'CCC', 'CD', 'D', 'DC', 'DCC', 'DCCC', 'CM' ];
		$thousands = [ '', 'M', 'MM', 'MMM', 'MMMM' ];

		if ( $arabic > 4999 ) {

			/*
			 * For large numbers (five thousand and above), a bar is placed above
			 * a base numeral to indicate multiplication by 1000.
			 *
			 * Since it is not possible to illustrate this in plain ASCII, this
			 * function will refuse to convert numbers above 4999.
			 */
			wp_die( __( 'Cannot represent numbers larger than 4999 in plain ASCII.', 'commentpress-core' ) );

		} elseif ( $arabic == 0 ) {

			/*
			 * In about 725, Bede or one of his colleagues used the letter N, the
			 * initial of nullae, in a table of epacts, all written in Roman
			 * numerals, to indicate zero.
			 */
			return 'N';

		} else {

			$roman = $thousands[ ( $arabic - fmod( $arabic, 1000 ) ) / 1000 ];
			$arabic = fmod( $arabic, 1000 );
			$roman .= $hundreds[ ( $arabic - fmod( $arabic, 100 ) ) / 100 ];
			$arabic = fmod( $arabic, 100 );
			$roman .= $tens[ ( $arabic - fmod( $arabic, 10 ) ) / 10 ];
			$arabic = fmod( $arabic, 10 );
			$roman .= $ones[ ( $arabic - fmod( $arabic, 1 ) ) / 1 ];
			$arabic = fmod( $arabic, 1 );
			return $roman;

		}

	}

	/**
	 * Get top parent Page ID.
	 *
	 * @since 3.0
	 *
	 * @param int $post_id The queried Page ID.
	 * @return int $post_id The overridden Page ID.
	 */
	public function get_top_parent_id( $post_id ) {

		// Get Page data.
		$page = get_page( $post_id );

		// Is the top Page?
		if ( $page->post_parent == 0 ) {

			// Yes -> return the ID.
			return $page->ID;

		} else {

			// No -> recurse upwards.
			return $this->get_top_parent_id( $page->post_parent );

		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Parse a WordPress Page list.
	 *
	 * @since 3.0
	 *
	 * @param str $mode Either 'structural' or 'readable'.
	 * @return array $pages All "Document" Pages.
	 */
	public function parse_pages( $mode ) {

		// Init return.
		$pages = [];

		// -----------------------------------------------------------------
		// Construct "Document" navigation based on Pages
		// -----------------------------------------------------------------

		// Default to no excludes.
		$excludes = '';

		// Init excluded array with "Special Pages".
		$excluded_pages = $this->core->db->option_get( 'cp_special_pages' );

		// If the supplied Title Page is the homepage.
		$title_id = $this->is_title_page_the_homepage();
		if ( $title_id !== false ) {

			// It will already be shown at the top of the Page list.
			$excluded_pages[] = $title_id;

		}

		// Are we in a BuddyPress scenario?
		if ( $this->core->bp->is_buddypress() ) {

			/*
			 * BuddyPress creates its own Registration Page and redirects ordinary
			 * WordPress Registration Page requests to it. It also seems to exclude
			 * it from wp_list_pages()
			 *
			 * @see CommentPress_Core_Display::list_pages()
			 */

			// Check if registration is allowed.
			if ( '1' == get_option( 'users_can_register' ) && is_main_site() ) {

				// Find the Registration Page by its slug.
				$reg_page = get_page_by_path( 'register' );

				// Did we get one?
				if ( is_object( $reg_page ) && isset( $reg_page->ID ) ) {

					// Yes - exclude it as well.
					$excluded_pages[] = $reg_page->ID;

				}

			}

		}

		/**
		 * Filters the Pages excluded from navigation.
		 *
		 * @since 3.4
		 *
		 * @param array $excluded_pages The default Pages excluded from navigation.
		 */
		$excluded_pages = apply_filters( 'cp_exclude_pages_from_nav', $excluded_pages );

		// Maybe comma-delimit them for the "exclude" argument.
		if ( is_array( $excluded_pages ) && ! empty( $excluded_pages ) ) {
			$excludes = implode( ',', $excluded_pages );
		}

		// Build Page query defaults.
		$defaults = [
			'child_of' => 0,
			'sort_order' => 'ASC',
			'sort_column' => 'menu_order, post_title',
			'hierarchical' => 1,
			'exclude' => $excludes,
			'include' => '',
			'authors' => '',
			'parent' => -1,
			'exclude_tree' => '',
		];

		// Get the Pages.
		$pages = get_pages( $defaults );
		if ( empty( $pages ) ) {
			return $pages;
		}

		// If Chapters are not Pages.
		if ( $this->core->db->option_get( 'cp_toc_chapter_is_page' ) != '1' ) {

			// Filter Chapters out if we want all readable Pages.
			if ( $mode == 'readable' ) {
				$pages = $this->filter_chapters( $pages );
			}

		}

		// Filter out Theme My Login Page if present.
		if ( defined( 'TML_ABSPATH' ) ) {
			$pages = $this->filter_theme_my_login_page( $pages );
		}

		// --<
		return $pages;

	}

	/**
	 * Check if the CommentPress "Title Page" is the homepage.
	 *
	 * @since 3.0
	 *
	 * @return bool|int $is_home False if not homepage, Page ID if true.
	 */
	public function is_title_page_the_homepage() {

		// Only need to parse this once.
		static $is_home;
		if ( isset( $is_home ) ) {
			return $is_home;
		}

		// Get Welcome Page ID.
		$welcome_id = $this->core->db->option_get( 'cp_welcome_page' );

		// Get Front Page ID.
		$page_on_front = $this->core->db->option_wp_get( 'page_on_front' );

		// If the CommentPress Title Page exists and it's the Front Page.
		if ( $welcome_id !== false && $page_on_front == $welcome_id ) {
			$is_home = $welcome_id;
		} else {
			$is_home = false;
		}

		// --<
		return $is_home;

	}

	// -------------------------------------------------------------------------

	/**
	 * Parse a WordPress Menu.
	 *
	 * @since 3.0
	 *
	 * @param str $mode Either 'structural' or 'readable'.
	 * @return array $pages All "Document" Pages.
	 */
	public function parse_menu( $mode ) {

		// Init return.
		$pages = [];

		// Get Menu locations.
		$locations = get_nav_menu_locations();

		// Check Menu locations.
		if ( isset( $locations['toc'] ) ) {

			// Get the Menu object.
			$menu = wp_get_nav_menu_object( $locations['toc'] );

			// Default args for reference.
			$args = [
				'order' => 'ASC',
				'orderby' => 'menu_order',
				'post_type' => 'nav_menu_item',
				'post_status' => 'publish',
				'output' => ARRAY_A,
				'output_key' => 'menu_order',
				'nopaging' => true,
				'update_post_term_cache' => false,
			];

			// Get the Menu objects and store for later.
			$this->menu_objects = wp_get_nav_menu_items( $menu->term_id, $args );

			// If we get some.
			if ( $this->menu_objects ) {

				// If Chapters are not Pages, filter the Menu Items.
				if ( $this->core->db->option_get( 'cp_toc_chapter_is_page' ) != '1' ) {

					// Do we want all readable Pages?
					if ( $mode == 'readable' ) {

						// Filter Chapters out.
						$menu_items = $this->filter_menu( $this->menu_objects );

					} else {

						// Structural - use a copy of the raw Menu data.
						$menu_items = $this->menu_objects;

					}

				} else {

					// Use a copy of the raw Menu data.
					$menu_items = $this->menu_objects;

				}

				// Init.
				$pages_to_get = [];

				// Convert to array of Pages.
				foreach ( $menu_items as $menu_item ) {

					// Is it a WordPress Menu Item?
					if ( isset( $menu_item->object_id ) ) {

						// Init pseudo WP_Post object.
						$pseudo_post = new stdClass();

						// Add Post ID.
						$pseudo_post->ID = $menu_item->object_id;

						// Add Menu ID (for filtering below).
						$pseudo_post->menu_id = $menu_item->ID;

						// Add Menu Item parent ID (for finding parent below).
						$pseudo_post->menu_item_parent = $menu_item->menu_item_parent;

						// Add Comment count for possible calls for "Next with Comments".
						$pseudo_post->comment_count = $menu_item->comment_count;

						// Add to array of WordPress Pages in Menu.
						$pages[] = $pseudo_post;

					}

				}

			}

		}

		// --<
		return $pages;

	}

	/**
	 * Strip out all but lowest level Menu Items.
	 *
	 * @since 3.0
	 *
	 * @param array $menu_items An array of Menu Item objects.
	 * @return array $sub_items All lowest level Menu Items.
	 */
	public function filter_menu( $menu_items ) {

		// Init return.
		$sub_items = [];

		// If we have any.
		if ( count( $menu_items ) > 0 ) {

			// Loop.
			foreach ( $menu_items as $key => $menu_obj ) {

				// Get Menu Item children.
				$kids = $this->get_menu_item_children( $menu_items, $menu_obj );

				// Do we have any?
				if ( empty( $kids ) ) {

					// Add to our return array.
					$sub_items[] = $menu_obj;

				}

			}

		}

		// --<
		return $sub_items;

	}

	/**
	 * Utility to get children of a Menu Item.
	 *
	 * @since 3.0
	 *
	 * @param array $menu_items An array of Menu Item objects.
	 * @param obj $menu_obj The Menu Item object.
	 * @return array $sub_items The Menu Item children.
	 */
	public function get_menu_item_children( $menu_items, $menu_obj ) {

		// Init return.
		$sub_items = [];

		// Bail if we have none.
		if ( empty( $menu_items ) ) {
			return $sub_items;
		}

		// Loop.
		foreach ( $menu_items as $key => $menu_item ) {

			// Is this Menu Item a child of the passed in Menu object?
			if ( $menu_item->menu_item_parent == $menu_obj->ID ) {

				// Add to our return array.
				$sub_items[] = $menu_item;

			}

		}

		// --<
		return $sub_items;

	}

	/**
	 * Utility to get parent of a Menu Item.
	 *
	 * @since 3.0
	 *
	 * @param obj $menu_obj The Menu Item object.
	 * @return int|bool $menu_item The parent Menu Item - or false if not found.
	 */
	public function get_menu_item_parent( $menu_obj ) {

		// If we have any.
		if ( count( $this->menu_objects ) > 0 ) {

			// Loop.
			foreach ( $this->menu_objects as $key => $menu_item ) {

				// Is this Menu Item the first parent of the passed in Menu object?
				if ( $menu_item->ID == $menu_obj->menu_item_parent ) {

					// --<
					return $menu_item;

				}

			}

		}

		// --<
		return false;

	}

	/**
	 * Get top parent Menu Item.
	 *
	 * @since 3.0
	 *
	 * @param object $menu_obj The queried Menu object.
	 * @return object $parent_obj The parent object or false if not found.
	 */
	public function get_top_menu_obj( $menu_obj ) {

		/*
		 * There is little point walking the Menu tree because Menu Items can
		 * appear more than once in the Menu.
		 *
		 * HOWEVER: for instances where people do use the Menu sensibly, we
		 * should attempt to walk the tree as best we can.
		 */

		// Is this the top Menu Item?
		if ( $menu_obj->menu_item_parent == 0 ) {

			// Yes -> return the object.
			return $menu_obj;

		}

		// Get parent Menu Item.
		$parent_obj = $this->get_menu_item_parent( $menu_obj );

		// Is the top Menu Item?
		if ( $parent_obj->menu_item_parent !== 0 ) {

			// No -> recurse upwards.
			return $this->get_top_menu_obj( $parent_obj );

		}

		// Yes -> return the object.
		return $parent_obj;

	}

}
