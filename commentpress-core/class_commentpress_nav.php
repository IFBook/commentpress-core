<?php /*
================================================================================
Class CommentpressCoreNavigator
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This class is a wrapper for navigating pages in whatever hierarchy
or relationship they have been assigned

--------------------------------------------------------------------------------
*/



/*
================================================================================
Class Name
================================================================================
*/

class CommentpressCoreNavigator {



	/**
	 * Properties
	 */

	// parent object reference
	public $parent_obj;

	// next pages array
	public $next_pages = array();

	// previous pages array
	public $previous_pages = array();

	// next posts array
	public $next_posts = array();

	// previous posts array
	public $previous_posts = array();

	// page numbers array
	public $page_numbers = array();

	// menu objects array, when using custom menu
	public $menu_objects = array();



	/**
	 * Initialises this object
	 *
	 * @param object $parent_obj A reference to the parent object
	 * @return object
	 */
	function __construct( $parent_obj ) {

		// store reference to parent
		$this->parent_obj = $parent_obj;

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

		// if we're navigating pages
		if ( is_page() ) {

			// init page lists
			$this->init_page_lists();

		}

		// if we're navigating posts
		if( is_single() ) {

			// init posts lists
			$this->init_posts_lists();

		}

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
	 * Get next page link
	 *
	 * @param bool $with_comments The requested page has comments - default false
	 * @return object $page_data True if successful, boolean false if not
	 */
	public function get_next_page( $with_comments = false ) {

		// do we have any next pages?
		if ( count( $this->next_pages ) > 0 ) {

			// are we asking for comments?
			if ( $with_comments ) {

				// loop
				foreach( $this->next_pages AS $next_page ) {

					// does it have comments?
					if ( $next_page->comment_count > 0 ) {

						// --<
						return $next_page;

					}

				}

			} else {

				// --<
				return $this->next_pages[0];

			}

		}

		// --<
		return false;

	}



	/**
	 * Get previous page link
	 *
	 * @param bool $with_comments The requested page has comments - default false
	 * @return object $page_data True if successful, boolean false if not
	 */
	public function get_previous_page( $with_comments = false ) {

		// do we have any previous pages?
		if ( count( $this->previous_pages ) > 0 ) {

			// are we asking for comments?
			if ( $with_comments ) {

				// loop
				foreach( $this->previous_pages AS $previous_page ) {

					// does it have comments?
					if ( $previous_page->comment_count > 0 ) {

						// --<
						return $previous_page;

					}

				}

			} else {

				// --<
				return $this->previous_pages[0];

			}

		}

		// --<
		return false;

	}



	/**
	 * Get next post link
	 *
	 * @param bool $with_comments The requested post has comments - default false
	 * @return object $post_data True if successful, boolean false if not
	 */
	public function get_next_post( $with_comments = false ) {

		// do we have any next posts?
		if ( count( $this->next_posts ) > 0 ) {

			// are we asking for comments?
			if ( $with_comments ) {

				// loop
				foreach( $this->next_posts AS $next_post ) {

					// does it have comments?
					if ( $next_post->comment_count > 0 ) {

						// --<
						return $next_post;

					}

				}

			} else {

				// --<
				return $this->next_posts[0];

			}

		}

		// --<
		return false;

	}



	/**
	 * Get previous post link
	 *
	 * @param bool $with_comments The requested post has comments - default false
	 * @return object $post_data True if successful, boolean false if not
	 */
	public function get_previous_post( $with_comments = false ) {

		// do we have any previous posts?
		if ( count( $this->previous_posts ) > 0 ) {

			// are we asking for comments?
			if ( $with_comments ) {

				// loop
				foreach( $this->previous_posts AS $previous_post ) {

					// does it have comments?
					if ( $previous_post->comment_count > 0 ) {

						// --<
						return $previous_post;

					}

				}

			} else {

				// --<
				return $this->previous_posts[0];

			}

		}

		// --<
		return false;

	}



	/**
	 * Get first viewable child page
	 *
	 * @param int $page_id The page ID
	 * @return int $first_child The ID of the first child page (or false if not found)
	 */
	public function get_first_child( $page_id ) {

		// init to look for published pages
		$defaults = array(

			'post_parent' => $page_id,
			'post_type' => 'page',
			'numberposts' => -1,
			'post_status' => 'publish',
			'orderby' => 'menu_order',
			'order' => 'ASC'

		);

		// get page children
		$children = get_children( $defaults );
		$kids =& $children;

		// do we have any?
		if ( empty( $kids ) ) {

			// no children
			return false;

		}

		// we got some...
		return $this->_get_first_child( $kids );

	}



	/**
	 * Get list of 'book' pages
	 *
	 * @param str $mode Either 'structural' or 'readable'
	 * @return array $pages All 'book' pages
	 */
	public function get_book_pages( $mode = 'readable' ) {

		// init
		$all_pages = array();

		// do we have a nav menu enabled?
		if ( has_nav_menu( 'toc' ) ) {

			// parse menu
			$all_pages = $this->_parse_menu( $mode );

		} else {

			// parse page order
			$all_pages = $this->_parse_pages( $mode );

		} // end check for custom menu

		//print_r( $all_pages ); die();

		// --<
		return $all_pages;

	}



	/**
	 * Get first readable 'book' page
	 *
	 * @return int $id The ID of the first page (or false if not found)
	 */
	public function get_first_page() {

		// init
		$id = false;

		// get all pages including chapters
		$all_pages = $this->get_book_pages( 'structural' );

		// if we have any pages...
		if ( count( $all_pages ) > 0 ) {

			// get first id
			$id = $all_pages[0]->ID;

		}

		// --<
		return $id;

	}



	/**
	 * Get page number
	 *
	 * @param int $page_id The page ID
	 * @return int $number The number of the page
	 */
	public function get_page_number( $page_id ) {

		// init
		$num = 0;

		// access post
		global $post;

		// are parent pages viewable?
		$viewable = ( $this->parent_obj->db->option_get( 'cp_toc_chapter_is_page' ) == '1' ) ? true : false;

		// if they are...
		if ( $viewable ) {

			// get page number from array
			$num = $this->_get_page_number( $page_id );

		} else {

			// get id of first viewable child
			$first_child = $this->get_first_child( $post->ID );

			// if this is a childless page
			if ( ! $first_child ) {

				// get page number from array
				$num = $this->_get_page_number( $page_id );

			}

		}

		// apply a filter
		$num = apply_filters( 'cp_nav_page_num', $num );

		// --<
		return $num;

	}



	/**
	 * Get page number
	 *
	 * @param int $page_id The page ID
	 * @return int $number The number of the page
	 */
	function _get_page_number( $page_id ) {

		// init
		$num = 0;

		// get from array
		if ( array_key_exists( $page_id, $this->page_numbers ) ) {

			// get it
			$num = $this->page_numbers[ $page_id ];

		}

		// --<
		return $num;

	}



	/**
	 * Redirect to child
	 *
	 * @return void
	 */
	function redirect_to_child() {

		// only on pages
		if ( ! is_page() ) { return; }

		// access post object
		global $post;

		// do we have one?
		if ( ! is_object( $post ) ) {

			// --<
			die( 'no post object' );

		}

		// are parent pages viewable?
		$viewable = ( $this->parent_obj->db->option_get( 'cp_toc_chapter_is_page' ) == '1' ) ? true : false;

		// get id of first child
		$first_child = $this->get_first_child( $post->ID );

		// our conditions
		if ( $first_child AND ! $viewable ) {

			// get link
			$redirect = get_permalink( $first_child );

			// do the redirect
			//header( "HTTP/1.1 301 Moved Permanently" );
			header( "Location: $redirect" );

		}

	}



	/**
	 * Set up page list
	 *
	 * @return void
	 */
	public function init_page_lists() {

		// get all pages
		$all_pages = $this->get_book_pages( 'readable' );

		// if we have any pages...
		if ( count( $all_pages ) > 0 ) {

			// generate page numbers
			$this->_generate_page_numbers( $all_pages );

			// access post object
			global $post;

			// init the key we want
			$page_key = false;

			// loop
			foreach( $all_pages AS $key => $page_obj ) {

				// is it the currently viewed page?
				if ( $page_obj->ID == $post->ID ) {

					// set page key
					$page_key = $key;

					// kick out to preserve key
					break;

				}

			}

			// if we don't get a key...
			if ( $page_key === false ) {

				// the current page is a chapter and is not a page
				$this->next_pages = array();

				// --<
				return;

			}

			// will there be a next array?
			if ( isset( $all_pages[$key + 1] ) ) {

				// get all subsequent pages
				$this->next_pages = array_slice( $all_pages, $key + 1 );

			}

			// will there be a previous array?
			if ( isset( $all_pages[$key - 1] ) ) {

				// get all previous pages
				$this->previous_pages = array_reverse( array_slice( $all_pages, 0, $key ) );

			}

		} // end have array check

	}



	/**
	 * Set up posts list
	 *
	 * @return void
	 */
	public function init_posts_lists() {

		// set defaults
		$defaults = array(

			'numberposts' => -1,
			'orderby' => 'date'

		);

		// get them
		$all_posts = get_posts( $defaults );

		// if we have any posts...
		if ( count( $all_posts ) > 0 ) {

			// access post object
			global $post;

			// loop
			foreach( $all_posts AS $key => $post_obj ) {

				// is it ours?
				if ( $post_obj->ID == $post->ID ) {

					// kick out to preserve key
					break;

				}

			}

			// will there be a next array?
			if ( isset( $all_posts[$key + 1] ) ) {

				// get all subsequent posts
				$this->next_posts = array_slice( $all_posts, $key + 1 );

			}

			// will there be a previous array?
			if ( isset( $all_posts[$key - 1] ) ) {

				// get all previous posts
				$this->previous_posts = array_reverse( array_slice( $all_posts, 0, $key ) );

			}

		} // end have array check

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

		/**
		 * is_page() and is_single() are not yet defined, so we init this object when
		 * wp_head() is fired - see initialise() above
		 */

	}



	/**
	 * Strip out all but lowest level pages
	 *
	 * @todo This only works one level deep?
	 *
	 * @param array $pages The array of page objects
	 * @return array $subpages All subpages
	 */
	function _filter_chapters( $pages ) {

		// init return
		$subpages = array();

		// if we have any...
		if ( count( $pages ) > 0 ) {

			// loop
			foreach( $pages AS $key => $page_obj ) {

				// init to look for published pages
				$defaults = array(

					'post_parent' => $page_obj->ID,
					'post_type' => 'page',
					'numberposts' => -1,
					'post_status' => 'publish'

				);

				// get page children
				$children = get_children( $defaults );
				$kids =& $children;

				// do we have any?
				if ( empty( $kids ) ) {

					// add to our return array
					$subpages[] = $page_obj;

				}

			}

		} // end have array check

		// --<
		return $subpages;

	}



	/**
	 * Get first published child, however deep
	 *
	 * @param array $pages The array of page objects
	 * @return array $subpages All subpages
	 */
	function _get_first_child( $pages ) {

		// if we have any...
		if ( count( $pages ) > 0 ) {

			// loop
			foreach( $pages AS $key => $page_obj ) {

				// init to look for published pages
				$defaults = array(

					'post_parent' => $page_obj->ID,
					'post_type' => 'page',
					'numberposts' => -1,
					'post_status' => 'publish',
					'orderby' => 'menu_order',
					'order' => 'ASC'
					//sort_column=menu_order,post_title

				);

				// get page children
				$kids =& get_children( $defaults );

				// do we have any?
				if ( ! empty( $kids ) ) {

					// go deeper
					return $this->_get_first_child( $kids );

				} else {

					// return first
					return $page_obj->ID;

				}

			}

		} // end have array check

		// --<
		return false;

	}



	/**
	 * Generates page numbers
	 *
	 * @todo Refine by section, page meta value etc
	 *
	 * @param array $pages The array of page objects in the 'book'
	 * @return void
	 */
	function _generate_page_numbers( $pages ) {

		// if we have any...
		if ( count( $pages ) > 0 ) {

			// init with page 1
			$num = 1;

			// assume no menu
			$has_nav_menu = false;

			// if we have a custom menu...
			if ( has_nav_menu( 'toc' ) ) {

				// override
				$has_nav_menu = true;

			}

			// loop
			foreach( $pages AS $page_obj ) {

				// get number format... the way this works in publications is that
				// only prefaces are numbered with roman numerals. So, we only allow
				// the first top level page to have the option of roman numerals.
				// if set, all child pages will be set to roman.

				// once we run out of roman numerals, $num is reset to 1

				// default to arabic
				$format = 'arabic';

				// set key
				$key = '_cp_number_format';

				// if the custom field already has a value...
				if ( get_post_meta( $page_obj->ID, $key, true ) !== '' ) {

					// get it
					$format = get_post_meta( $page_obj->ID, $key, true );

				} else {

					// if we have a custom menu...
					if ( $has_nav_menu ) {

						//print_r( $page_obj ); die();

						// get top level menu item
						$top_menu_item = $this->_get_top_menu_obj( $page_obj );
						//print_r( $top_menu_item ); //die();

						// since this might not be a WP_POST object...
						if ( isset( $top_menu_item->object_id ) ) {

							// get ID of top level parent
							$top_page_id = $top_menu_item->object_id;

							// if the custom field has a value...
							if ( get_post_meta( $top_page_id, $key, true ) !== '' ) {

								// get it
								$format = get_post_meta( $top_page_id, $key, true );

							}

						}

					} else {

						// get top level parent
						$top_page_id = $this->_get_top_parent_id( $page_obj->ID );

						// if the custom field has a value...
						if ( get_post_meta( $top_page_id, $key, true ) !== '' ) {

							// get it
							$format = get_post_meta( $top_page_id, $key, true );

						}

					}

				}

				// if it's roman
				if ( $format == 'roman' ) {

					// convert arabic to roman
					$this->page_numbers[ $page_obj->ID ] = $this->_number_to_roman( $num );

				} else {

					// if flag not set
					if ( ! isset( $flag ) ) {

						// reset num
						$num = 1;

						// set flag
						$flag = true;

					}

					// store roman
					$this->page_numbers[ $page_obj->ID ] = $num;

				}

				// increment
				$num++;

			}

			//print_r( $this->page_numbers ); die();

		}

	}



	/**
	 * Utility to remove the Theme My Login page
	 *
	 * @todo Pass the array?
	 *
	 * @param array $pages An array of page objects
	 * @return bool $success
	 */
	function _filter_theme_my_login_page( $pages ) {

		// init return
		$clean = array();

		// if we have any...
		if ( count( $pages ) > 0 ) {

			// loop
			foreach( $pages AS $page_obj ) {

				// do we have any?
				if ( ! $this->_detect_login_page( $page_obj ) ) {

					// add to our return array
					$clean[] = $page_obj;

				}

			}

		} // end have array check

		// --<
		return $clean;

	}



	/**
	 * Utility to detect the Theme My Login page
	 *
	 * @param object $page_obj The WordPress page object
	 * @return boolean $success True if TML page, false otherwise
	 */
	function _detect_login_page( $page_obj ) {

		// compat with Theme My Login
		if(

			$page_obj->post_name == 'login' AND
			$page_obj->post_content == '[theme-my-login]'

		) {

			// --<
			return true;

		}

		// --<
		return false;

	}



	/**
	 * PHP Roman Numeral Library
	 *
	 * Copyright (c) 2008, reusablecode.blogspot.com; some rights reserved.
	 *
	 * This work is licensed under the Creative Commons Attribution License. To view
	 * a copy of this license, visit http://creativecommons.org/licenses/by/3.0/ or
	 * send a letter to Creative Commons, 559 Nathan Abbott Way, Stanford, California
	 * 94305, USA.
	 *
	 * Utility to convert arabic to roman numerals
	 *
	 * @return boolean $result the roman equivalent
	 */
	function _number_to_roman( $arabic ) {

		$ones = array("", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX");
		$tens = array("", "X", "XX", "XXX", "XL", "L", "LX", "LXX", "LXXX", "XC");
		$hundreds = array("", "C", "CC", "CCC", "CD", "D", "DC", "DCC", "DCCC", "CM");
		$thousands = array("", "M", "MM", "MMM", "MMMM");

		if ( $arabic > 4999 ) {

			// For large numbers (five thousand and above), a bar is placed above a base numeral to indicate multiplication by 1000.
			// Since it is not possible to illustrate this in plain ASCII, this function will refuse to convert numbers above 4999.
			die("Cannot represent numbers larger than 4999 in plain ASCII.");

		} elseif ( $arabic == 0 ) {

			// About 725, Bede or one of his colleagues used the letter N, the initial of nullae,
			// in a table of epacts, all written in Roman numerals, to indicate zero.
			return "N";

		} else {

			$roman = $thousands[($arabic - fmod($arabic, 1000)) / 1000];
			$arabic = fmod($arabic, 1000);
			$roman .= $hundreds[($arabic - fmod($arabic, 100)) / 100];
			$arabic = fmod($arabic, 100);
			$roman .= $tens[($arabic - fmod($arabic, 10)) / 10];
			$arabic = fmod($arabic, 10);
			$roman .= $ones[($arabic - fmod($arabic, 1)) / 1];
			$arabic = fmod($arabic, 1);
			return $roman;

		}

	}



	/**
	 * Get top parent page id
	 *
	 * @param int $post_id The queried page ID
	 * @return int $post_id The overridden page ID
	 */
	function _get_top_parent_id( $post_id ) {

		// get page data
		$_page = get_page( $post_id );

		// is the top page?
		if ( $_page->post_parent == 0 ) {

			// yes -> return the id
			return $_page->ID;

		} else {

			// no -> recurse upwards
			return $this->_get_top_parent_id( $_page->post_parent );

		}

	}



	/**
	 * Parse a WP page list
	 *
	 * @param str $mode Either 'structural' or 'readable'
	 * @return array $pages All 'book' pages
	 */
	function _parse_pages( $mode ) {

		// init return
		$pages = array();

		// -----------------------------------------------------------------
		// construct "book" navigation based on pages
		// -----------------------------------------------------------------

		// default to no excludes
		$excludes = '';

		// init excluded array with "special pages"
		$excluded_pages = $this->parent_obj->db->option_get( 'cp_special_pages' );

		// are we in a BuddyPress scenario?
		if ( $this->parent_obj->is_buddypress() ) {

			// BuddyPress creates its own registration page at /register and
			// redirects ordinary WP registration page requests to it. It also
			// seems to exclude it from wp_list_pages(), see: $cp->display->list_pages()

			// check if registration is allowed
			if ( '1' == get_option('users_can_register') AND is_main_site() ) {

				// find the registration page by its slug
				$reg_page = get_page_by_path( 'register' );

				// did we get one?
				if ( is_object( $reg_page ) AND isset( $reg_page->ID ) ) {

					// yes - exclude it as well
					$excluded_pages[] = $reg_page->ID;

				}

			}

		}

		// allow plugins to filter
		$excluded_pages = apply_filters( 'cp_exclude_pages_from_nav', $excluded_pages );

		// are there any?
		if ( is_array( $excluded_pages ) AND count( $excluded_pages ) > 0 ) {

			// format them for the exclude param
			$excludes = implode( ',', $excluded_pages );

		}

		// set list pages defaults
		$defaults = array(
			'child_of' => 0,
			'sort_order' => 'ASC',
			'sort_column' => 'menu_order, post_title',
			'hierarchical' => 1,
			'exclude' => $excludes,
			'include' => '',
			'meta_key' => '',
			'meta_value' => '',
			'authors' => '',
			'parent' => -1,
			'exclude_tree' => ''
		);

		// get them
		$pages = get_pages( $defaults );

		// if we have any pages...
		if ( count( $pages ) > 0 ) {

			// if chapters are not pages...
			if ( $this->parent_obj->db->option_get( 'cp_toc_chapter_is_page' ) != '1' ) {

				// do we want all readable pages?
				if ( $mode == 'readable' ) {

					// filter chapters out
					$pages = $this->_filter_chapters( $pages );

				}

			}

			// if Theme My Login is present...
			if ( defined( 'TML_ABSPATH' ) ) {

				// filter its page out
				$pages = $this->_filter_theme_my_login_page( $pages );

			}

		}

		// --<
		return $pages;

	}



	/**
	 * Parse a WP menu
	 *
	 * @param str $mode Either 'structural' or 'readable'
	 * @return array $pages All 'book' pages
	 */
	function _parse_menu( $mode ) {

		// init return
		$pages = array();

		// get menu locations
		$locations = get_nav_menu_locations();

		// check menu locations
		if ( isset( $locations[ 'toc' ] ) ) {

			// get the menu object
			$menu = wp_get_nav_menu_object( $locations[ 'toc' ] );

			// default args for reference
			$args = array(

				'order' => 'ASC',
				'orderby' => 'menu_order',
				'post_type' => 'nav_menu_item',
				'post_status' => 'publish',
				'output' => ARRAY_A,
				'output_key' => 'menu_order',
				'nopaging' => true,
				'update_post_term_cache' => false

			);

			// get the menu objects and store for later
			$this->menu_objects = wp_get_nav_menu_items( $menu->term_id, $args );
			//print_r( $this->menu_objects ); die();

			// if we get some
			if ( $this->menu_objects ) {

				// if chapters are not pages, filter the menu items...
				if ( $this->parent_obj->db->option_get( 'cp_toc_chapter_is_page' ) != '1' ) {

					// do we want all readable pages?
					if ( $mode == 'readable' ) {

						// filter chapters out
						$menu_items = $this->_filter_menu( $this->menu_objects );

					} else {

						// structural - use a copy of the raw menu data
						$menu_items = $this->menu_objects;

					}

				} else {

					// use a copy of the raw menu data
					$menu_items = $this->menu_objects;

				}

				// init
				$pages_to_get = array();

				// convert to array of pages
				foreach ( $menu_items AS $menu_item ) {

					// is it a WP item?
					if ( isset( $menu_item->object_id ) ) {

						// init pseudo WP_POST object
						$pseudo_post = new stdClass;

						// add post ID
						$pseudo_post->ID = $menu_item->object_id;

						// add menu ID (for filtering below)
						$pseudo_post->menu_id = $menu_item->ID;

						// add menu item parent ID (for finding parent below)
						$pseudo_post->menu_item_parent = $menu_item->menu_item_parent;

						// add comment count for possible calls for "next with comments"
						$pseudo_post->comment_count = $menu_item->comment_count;

						// add to array of WP pages in menu
						$pages[] = $pseudo_post;

					}

				}

				/*
				print_r( array(
					'menu_items' => $menu_items,
					'pages' => $pages,
				) ); die();
				*/

			} // end check for menu items

		} // end check for our menu

		// --<
		return $pages;

	}



	/**
	 * Strip out all but lowest level menu items
	 *
	 * @param array $menu_items An array of menu item objects
	 * @return array $sub_items All lowest level items
	 */
	function _filter_menu( $menu_items ) {

		// init return
		$sub_items = array();

		// if we have any...
		if ( count( $menu_items ) > 0 ) {

			// loop
			foreach( $menu_items AS $key => $menu_obj ) {

				// get item children
				$kids = $this->_get_menu_item_children( $menu_items, $menu_obj );

				// do we have any?
				if ( empty( $kids ) ) {

					// add to our return array
					$sub_items[] = $menu_obj;

				}

			}

		} // end have array check

		// --<
		return $sub_items;

	}



	/**
	 * Utility to get children of a menu item
	 *
	 * @param array $menu_items An array of menu item objects
	 * @param obj $menu_obj The menu item object
	 * @return array $sub_items The menu item children
	 */
	function _get_menu_item_children( $menu_items, $menu_obj ) {

		// init return
		$sub_items = array();

		// if we have any...
		if ( count( $menu_items ) > 0 ) {

			// loop
			foreach( $menu_items AS $key => $menu_item ) {

				// is this item a child of the passed in menu object?
				if ( $menu_item->menu_item_parent == $menu_obj->ID ) {

					// add to our return array
					$sub_items[] = $menu_item;

				}

			}

		} // end have array check

		// --<
		return $sub_items;

	}



	/**
	 * Utility to get parent of a menu item
	 *
	 * @param obj $menu_obj The menu item object
	 * @return int $menu_obj The parent menu item
	 */
	function _get_menu_item_parent( $menu_obj ) {

		// if we have any...
		if ( count( $this->menu_objects ) > 0 ) {

			// loop
			foreach( $this->menu_objects AS $key => $menu_item ) {

				// is this item the first parent of the passed in menu object?
				if ( $menu_item->ID == $menu_obj->menu_item_parent ) {

					// --<
					return $menu_item;

				}

			}

		} // end have array check

		// --<
		return false;

	}



	/**
	 * Get top parent menu item
	 *
	 * @param object $menu_obj The queried menu object
	 * @return object $parent_obj The parent object or false if
	 */
	function _get_top_menu_obj( $menu_obj ) {

		// there is little point walking the menu tree because menu items can appear
		// more than once in the menu...

		// HOWEVER: for instances where people do use the menu sensibly, we should
		// attempt to walk the tree as best we can

		// is this the top item?
		if ( $menu_obj->menu_item_parent == 0 ) {

			// yes -> return the object
			return $menu_obj;

		}

		//print_r( $menu_obj ); //die();

		// get parent item
		$parent_obj = $this->_get_menu_item_parent( $menu_obj );

		//print_r( $parent_obj ); //die();

		// is the top item?
		if ( $parent_obj->menu_item_parent !== 0 ) {

			// no -> recurse upwards
			return $this->_get_top_menu_obj( $parent_obj );

		}

		//print_r( $parent_obj ); die();

		// yes -> return the object
		return $parent_obj;

	}



//##############################################################################



} // class ends



