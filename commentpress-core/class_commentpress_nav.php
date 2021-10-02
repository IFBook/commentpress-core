<?php

/**
 * CommentPress Core Navigation Class.
 *
 * This class is a wrapper for navigating pages in whatever hierarchy or
 * relationship they have been assigned.
 *
 * @since 3.0
 */
class Commentpress_Core_Navigator {

	/**
	 * Plugin object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $parent_obj The plugin object.
	 */
	public $parent_obj;

	/**
	 * Next pages array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $next_pages The next pages array.
	 */
	public $next_pages = [];

	/**
	 * Previous pages array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $previous_pages The previous pages array.
	 */
	public $previous_pages = [];

	/**
	 * Next posts array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $next_posts The next posts array.
	 */
	public $next_posts = [];

	/**
	 * Previous posts array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $previous_posts The previous posts array.
	 */
	public $previous_posts = [];

	/**
	 * Page numbers array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $page_numbers The page numbers array.
	 */
	public $page_numbers = [];

	/**
	 * Menu objects array, when using custom menu.
	 *
	 * @since 3.3
	 * @access public
	 * @var array $menu_objects The menu objects array.
	 */
	public $menu_objects = [];

	/**
	 * Page navigation enabled flag.
	 *
	 * @since 3.8.10
	 * @access public
	 * @var bool $nav_enabled True if page navigation is enabled, false otherwise.
	 */
	public $nav_enabled = true;



	/**
	 * Initialises this object.
	 *
	 * @since 3.0
	 *
	 * @param object $parent_obj A reference to the parent object.
	 */
	public function __construct( $parent_obj ) {

		// Store reference to parent.
		$this->parent_obj = $parent_obj;

		// Init.
		$this->_init();

	}



	/**
	 * Set up all items associated with this object.
	 *
	 * @since 3.0
	 */
	public function initialise() {

		// If we're navigating pages.
		if ( is_page() ) {

			// Check page navigation flag.
			if ( $this->page_nav_is_disabled() ) {

				// Remove page arrows via filter.
				add_filter( 'cp_template_page_navigation', [ $this, 'page_nav_disable' ], 100, 1 );

				// Save flag.
				$this->nav_enabled = false;

			}

			// Init page lists.
			$this->init_page_lists();

		}

		// If we're navigating posts.
		if( is_single() ) {

			// Init posts lists.
			$this->init_posts_lists();

		}

	}



	/**
	 * If needed, destroys all items associated with this object.
	 *
	 * @since 3.0
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
	 * Disable page navigation when on a "page".
	 *
	 * @since 3.8.10
	 *
	 * @param str $template The existing path to the navigation template.
	 * @return str $template An empty path to disable navigation.
	 */
	public function page_nav_disable( $template ) {

		// Disable for page post type.
		return '';

	}



	/**
	 * Check if page navigation is disabled when on a "page".
	 *
	 * @since 3.9
	 *
	 * @return bool True if navigation is disabled, fasle otherwise.
	 */
	public function page_nav_is_disabled() {

		// Check page navigation option.
		if (
			$this->parent_obj->db->option_exists( 'cp_page_nav_enabled' ) AND
			$this->parent_obj->db->option_get( 'cp_page_nav_enabled', 'y' ) == 'n'
		) {

			// Overwrite flag.
			$this->nav_enabled = false;

		}

		// Return the opposite.
		return $this->nav_enabled ? false : true;

	}



	/**
	 * Get next page link.
	 *
	 * @since 3.0
	 *
	 * @param bool $with_comments The requested page has comments - default false.
	 * @return object $page_data True if successful, boolean false if not.
	 */
	public function get_next_page( $with_comments = false ) {

		// Do we have any next pages?
		if ( count( $this->next_pages ) > 0 ) {

			// Are we asking for comments?
			if ( $with_comments ) {

				// Loop.
				foreach( $this->next_pages AS $next_page ) {

					// Does it have comments?
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

		// Check if the supplied title page is the homepage and this is it.
		$title_id = $this->is_title_page_the_homepage();
		if ( $title_id !== false AND is_front_page() ) {

			// Get the first readable page.
			$first_id = $this->get_first_page();

			// Return the post object.
			return get_post( $first_id );

		}

		// --<
		return false;

	}



	/**
	 * Get previous page link.
	 *
	 * @since 3.0
	 *
	 * @param bool $with_comments The requested page has comments - default false.
	 * @return object $page_data True if successful, boolean false if not.
	 */
	public function get_previous_page( $with_comments = false ) {

		// Do we have any previous pages?
		if ( count( $this->previous_pages ) > 0 ) {

			// Are we asking for comments?
			if ( $with_comments ) {

				// Loop.
				foreach( $this->previous_pages AS $previous_page ) {

					// Does it have comments?
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

		// This must be the first page.

		// We still need to check if the supplied title page is the homepage.
		$title_id = $this->is_title_page_the_homepage();
		if ( $title_id !== false AND ! is_front_page() ) {
			return get_post( $title_id );
		}

		// --<
		return false;

	}



	/**
	 * Get next post link.
	 *
	 * @since 3.0
	 *
	 * @param bool $with_comments The requested post has comments - default false.
	 * @return object $post_data True if successful, boolean false if not.
	 */
	public function get_next_post( $with_comments = false ) {

		// Do we have any next posts?
		if ( count( $this->next_posts ) > 0 ) {

			// Are we asking for comments?
			if ( $with_comments ) {

				// Loop.
				foreach( $this->next_posts AS $next_post ) {

					// Does it have comments?
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
	 * Get previous post link.
	 *
	 * @since 3.0
	 *
	 * @param bool $with_comments The requested post has comments - default false.
	 * @return object $post_data True if successful, boolean false if not.
	 */
	public function get_previous_post( $with_comments = false ) {

		// Do we have any previous posts?
		if ( count( $this->previous_posts ) > 0 ) {

			// Are we asking for comments?
			if ( $with_comments ) {

				// Loop.
				foreach( $this->previous_posts AS $previous_post ) {

					// Does it have comments?
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
	 * Get first viewable child page.
	 *
	 * @since 3.0
	 *
	 * @param int $page_id The page ID.
	 * @return int $first_child The ID of the first child page (or false if not found).
	 */
	public function get_first_child( $page_id ) {

		// Init to look for published pages.
		$defaults = [
			'post_parent' => $page_id,
			'post_type' => 'page',
			'numberposts' => -1,
			'post_status' => 'publish',
			'orderby' => 'menu_order, post_title',
			'order' => 'ASC',
		];

		// Get page children.
		$children = get_children( $defaults );
		$kids =& $children;

		// Do we have any?
		if ( empty( $kids ) ) {

			// No children.
			return false;

		}

		// We got some
		return $this->_get_first_child( $kids );

	}



	/**
	 * Get list of 'book' pages.
	 *
	 * @since 3.0
	 *
	 * @param str $mode Either 'structural' or 'readable'.
	 * @return array $pages All 'book' pages.
	 */
	public function get_book_pages( $mode = 'readable' ) {

		// Init.
		$all_pages = [];

		// Do we have a nav menu enabled?
		if ( has_nav_menu( 'toc' ) ) {

			// Parse menu.
			$all_pages = $this->_parse_menu( $mode );

		} else {

			// Parse page order.
			$all_pages = $this->_parse_pages( $mode );

		} // End check for custom menu.

		// --<
		return $all_pages;

	}



	/**
	 * Get first readable 'book' page.
	 *
	 * @since 3.0
	 *
	 * @return int $id The ID of the first page (or false if not found).
	 */
	public function get_first_page() {

		// Init.
		$id = false;

		// Get all pages including chapters.
		$all_pages = $this->get_book_pages( 'structural' );

		// If we have any pages.
		if ( count( $all_pages ) > 0 ) {

			// Get first ID.
			$id = $all_pages[0]->ID;

		}

		// --<
		return $id;

	}



	/**
	 * Get page number.
	 *
	 * @since 3.0
	 *
	 * @param int $page_id The page ID.
	 * @return int $number The number of the page.
	 */
	public function get_page_number( $page_id ) {

		// Bail if page nav is disabled.
		if ( $this->nav_enabled === false ) return;

		// Init.
		$num = 0;

		// Access post.
		global $post;

		// Are parent pages viewable?
		$viewable = ( $this->parent_obj->db->option_get( 'cp_toc_chapter_is_page' ) == '1' ) ? true : false;

		// If they are.
		if ( $viewable ) {

			// Get page number from array.
			$num = $this->_get_page_number( $page_id );

		} else {

			// Get id of first viewable child.
			$first_child = $this->get_first_child( $post->ID );

			// If this is a childless page.
			if ( ! $first_child ) {

				// Get page number from array.
				$num = $this->_get_page_number( $page_id );

			}

		}

		// Apply a filter.
		$num = apply_filters( 'cp_nav_page_num', $num );

		// --<
		return $num;

	}



	/**
	 * Get page number.
	 *
	 * @since 3.0
	 *
	 * @param int $page_id The page ID.
	 * @return int $number The number of the page.
	 */
	public function _get_page_number( $page_id ) {

		// Init.
		$num = 0;

		// Get from array.
		if ( array_key_exists( $page_id, $this->page_numbers ) ) {

			// Get it.
			$num = $this->page_numbers[$page_id];

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

		// Only on pages.
		if ( ! is_page() ) return;

		// Bail if this is a BuddyPress page.
		if ( $this->parent_obj->is_buddypress_special_page() ) return;

		// Bail if we have a custom menu.
		// TODO: we need to parse the menu to find the viewable child
		if ( has_nav_menu( 'toc' ) ) return;

		// Access post object.
		global $post;

		// Sanity check.
		if ( ! is_object( $post ) ) return;

		// Are parent pages viewable?
		$viewable = ( $this->parent_obj->db->option_get( 'cp_toc_chapter_is_page' ) == '1' ) ? true : false;

		// Get id of first child.
		$first_child = $this->get_first_child( $post->ID );

		// Our conditions.
		if ( $first_child AND ! $viewable ) {

			// Get link.
			$redirect = get_permalink( $first_child );

			// Do the redirect.
			//header( "HTTP/1.1 301 Moved Permanently" );
			header( "Location: $redirect" );

		}

	}



	/**
	 * Set up page list.
	 *
	 * @since 3.3
	 */
	public function init_page_lists() {

		// Get all pages.
		$all_pages = $this->get_book_pages( 'readable' );

		// If we have any pages.
		if ( count( $all_pages ) > 0 ) {

			// Generate page numbers.
			$this->_generate_page_numbers( $all_pages );

			// Access post object.
			global $post;

			// Init the key we want.
			$page_key = false;

			// Loop.
			foreach( $all_pages AS $key => $page_obj ) {

				// Is it the currently viewed page?
				if ( $page_obj->ID == $post->ID ) {

					// Set page key.
					$page_key = $key;

					// Kick out to preserve key.
					break;

				}

			}

			// If we don't get a key.
			if ( $page_key === false ) {

				// The current page is a chapter and is not a page.
				$this->next_pages = [];

				// --<
				return;

			}

			// Will there be a next array?
			if ( isset( $all_pages[$key + 1] ) ) {

				// Get all subsequent pages.
				$this->next_pages = array_slice( $all_pages, $key + 1 );

			}

			// Will there be a previous array?
			if ( isset( $all_pages[$key - 1] ) ) {

				// Get all previous pages.
				$this->previous_pages = array_reverse( array_slice( $all_pages, 0, $key ) );

			}

		} // End have array check.

	}



	/**
	 * Set up posts list.
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

		// If we have any posts.
		if ( count( $all_posts ) > 0 ) {

			// Access post object.
			global $post;

			// Loop.
			foreach( $all_posts AS $key => $post_obj ) {

				// Is it ours?
				if ( $post_obj->ID == $post->ID ) {

					// Kick out to preserve key.
					break;

				}

			}

			// Will there be a next array?
			if ( isset( $all_posts[$key + 1] ) ) {

				// Get all subsequent posts.
				$this->next_posts = array_slice( $all_posts, $key + 1 );

			}

			// Will there be a previous array?
			if ( isset( $all_posts[$key - 1] ) ) {

				// Get all previous posts.
				$this->previous_posts = array_reverse( array_slice( $all_posts, 0, $key ) );

			}

		} // End have array check

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

		/**
		 * is_page() and is_single() are not yet defined, so we init this object when
		 * wp_head() is fired - see initialise() above
		 */

	}



	/**
	 * Strip out all but lowest level pages.
	 *
	 * @todo This only works one level deep?
	 *
	 * @since 3.0
	 *
	 * @param array $pages The array of page objects.
	 * @return array $subpages All subpages.
	 */
	public function _filter_chapters( $pages ) {

		// Init return.
		$subpages = [];

		// If we have any.
		if ( count( $pages ) > 0 ) {

			// Loop.
			foreach( $pages AS $key => $page_obj ) {

				// Init to look for published pages.
				$defaults = [
					'post_parent' => $page_obj->ID,
					'post_type' => 'page',
					'numberposts' => -1,
					'post_status' => 'publish',
				];

				// Get page children.
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
	 *
	 * @param array $pages The array of page objects.
	 * @return array $subpages All subpages.
	 */
	public function _get_first_child( $pages ) {

		// If we have any.
		if ( count( $pages ) > 0 ) {

			// Loop.
			foreach( $pages AS $key => $page_obj ) {

				// Init to look for published pages.
				$defaults = [
					'post_parent' => $page_obj->ID,
					'post_type' => 'page',
					'numberposts' => -1,
					'post_status' => 'publish',
					'orderby' => 'menu_order, post_title',
					'order' => 'ASC',
				];

				// Get page children.
				$children = get_children( $defaults );
				$kids =& $children;

				// Do we have any?
				if ( ! empty( $kids ) ) {

					// Go deeper.
					return $this->_get_first_child( $kids );

				} else {

					// Return first.
					return $page_obj->ID;

				}

			}

		} // End have array check

		// --<
		return false;

	}



	/**
	 * Generates page numbers.
	 *
	 * @todo Refine by section, page meta value etc.
	 *
	 * @since 3.0
	 *
	 * @param array $pages The array of page objects in the 'book'.
	 */
	public function _generate_page_numbers( $pages ) {

		// If we have any.
		if ( count( $pages ) > 0 ) {

			// Init with page 1.
			$num = 1;

			// Assume no menu.
			$has_nav_menu = false;

			// If we have a custom menu.
			if ( has_nav_menu( 'toc' ) ) {

				// Override.
				$has_nav_menu = true;

			}

			// Loop.
			foreach( $pages AS $page_obj ) {

				/**
				 * Get number format - the way this works in publications is that
				 * only prefaces are numbered with Roman numerals. So, we only allow
				 * the first top level page to have the option of Roman numerals.
				 *
				 * If set, all child pages will be set to Roman.
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

					// If we have a custom menu.
					if ( $has_nav_menu ) {

						// Get top level menu item.
						$top_menu_item = $this->_get_top_menu_obj( $page_obj );

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
						$top_page_id = $this->_get_top_parent_id( $page_obj->ID );

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
					$this->page_numbers[$page_obj->ID] = $this->_number_to_roman( $num );

				} else {

					// If flag not set.
					if ( ! isset( $flag ) ) {

						// Reset num.
						$num = 1;

						// Set flag.
						$flag = true;

					}

					// Store roman.
					$this->page_numbers[$page_obj->ID] = $num;

				}

				// Increment.
				$num++;

			}

		}

	}



	/**
	 * Utility to remove the Theme My Login page.
	 *
	 * @since 3.0
	 *
	 * @param array $pages An array of page objects.
	 * @return bool $clean The modified array pf page objects.
	 */
	public function _filter_theme_my_login_page( $pages ) {

		// Init return.
		$clean = [];

		// If we have any.
		if ( count( $pages ) > 0 ) {

			// Loop.
			foreach( $pages AS $page_obj ) {

				// Do we have any?
				if ( ! $this->_detect_login_page( $page_obj ) ) {

					// Add to our return array.
					$clean[] = $page_obj;

				}

			}

		} // End have array check.

		// --<
		return $clean;

	}



	/**
	 * Utility to detect the Theme My Login page.
	 *
	 * @since 3.0
	 *
	 * @param object $page_obj The WordPress page object.
	 * @return boolean $success True if TML page, false otherwise.
	 */
	public function _detect_login_page( $page_obj ) {

		// Compat with Theme My Login.
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
	public function _number_to_roman( $arabic ) {

		$ones = [ "", "I", "II", "III", "IV", "V", "VI", "VII", "VIII", "IX" ];
		$tens = [ "", "X", "XX", "XXX", "XL", "L", "LX", "LXX", "LXXX", "XC" ];
		$hundreds = [ "", "C", "CC", "CCC", "CD", "D", "DC", "DCC", "DCCC", "CM" ];
		$thousands = [ "", "M", "MM", "MMM", "MMMM" ];

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
			return "N";

		} else {

			$roman = $thousands[( $arabic - fmod( $arabic, 1000 ) ) / 1000];
			$arabic = fmod( $arabic, 1000 );
			$roman .= $hundreds[( $arabic - fmod( $arabic, 100 ) ) / 100];
			$arabic = fmod( $arabic, 100 );
			$roman .= $tens[( $arabic - fmod( $arabic, 10 ) ) / 10];
			$arabic = fmod( $arabic, 10 );
			$roman .= $ones[( $arabic - fmod( $arabic, 1 ) ) / 1];
			$arabic = fmod( $arabic, 1 );
			return $roman;

		}

	}



	/**
	 * Get top parent page ID.
	 *
	 * @since 3.0
	 *
	 * @param int $post_id The queried page ID.
	 * @return int $post_id The overridden page ID.
	 */
	public function _get_top_parent_id( $post_id ) {

		// Get page data.
		$page = get_page( $post_id );

		// Is the top page?
		if ( $page->post_parent == 0 ) {

			// Yes -> return the ID.
			return $page->ID;

		} else {

			// No -> recurse upwards.
			return $this->_get_top_parent_id( $page->post_parent );

		}

	}



	/**
	 * Parse a WP page list.
	 *
	 * @since 3.0
	 *
	 * @param str $mode Either 'structural' or 'readable'.
	 * @return array $pages All 'book' pages.
	 */
	public function _parse_pages( $mode ) {

		// Init return.
		$pages = [];

		// -----------------------------------------------------------------
		// Construct "book" navigation based on pages
		// -----------------------------------------------------------------

		// Default to no excludes.
		$excludes = '';

		// Init excluded array with "special pages".
		$excluded_pages = $this->parent_obj->db->option_get( 'cp_special_pages' );

		// If the supplied title page is the homepage.
		$title_id = $this->is_title_page_the_homepage();
		if ( $title_id !== false ) {

			// It will already have been shown at the top of the page list.
			$excluded_pages[] = $title_id;

		}

		// Are we in a BuddyPress scenario?
		if ( $this->parent_obj->is_buddypress() ) {

			/*
			 * BuddyPress creates its own registration page and redirects ordinary
			 * WordPress registration page requests to it. It also seems to exclude
			 * it from wp_list_pages()
			 *
			 * @see Commentpress_Core_Display::list_pages()
			 */

			// Check if registration is allowed.
			if ( '1' == get_option( 'users_can_register' ) AND is_main_site() ) {

				// Find the registration page by its slug.
				$reg_page = get_page_by_path( 'register' );

				// Did we get one?
				if ( is_object( $reg_page ) AND isset( $reg_page->ID ) ) {

					// Yes - exclude it as well.
					$excluded_pages[] = $reg_page->ID;

				}

			}

		}

		// Allow plugins to filter.
		$excluded_pages = apply_filters( 'cp_exclude_pages_from_nav', $excluded_pages );

		// Are there any?
		if ( is_array( $excluded_pages ) AND count( $excluded_pages ) > 0 ) {

			// Format them for the exclude param
			$excludes = implode( ',', $excluded_pages );

		}

		// Set list pages defaults.
		$defaults = [
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
			'exclude_tree' => '',
		];

		// Get them.
		$pages = get_pages( $defaults );

		// If we have any pages.
		if ( count( $pages ) > 0 ) {

			// If chapters are not pages.
			if ( $this->parent_obj->db->option_get( 'cp_toc_chapter_is_page' ) != '1' ) {

				// Do we want all readable pages?
				if ( $mode == 'readable' ) {

					// Filter chapters out.
					$pages = $this->_filter_chapters( $pages );

				}

			}

			// If Theme My Login is present.
			if ( defined( 'TML_ABSPATH' ) ) {

				// Filter its page out.
				$pages = $this->_filter_theme_my_login_page( $pages );

			}

		}

		// --<
		return $pages;

	}



	/**
	 * Check if the CommentPress "Title Page" is the homepage.
	 *
	 * @since 3.0
	 *
	 * @return bool|int $is_home False if not homepage, page ID if true.
	 */
	public function is_title_page_the_homepage() {

		// Only need to parse this once.
		static $is_home = null;
		if ( ! is_null( $is_home ) ) {
			return $is_home;
		}

		// Get welcome page ID.
		$welcome_id = $this->parent_obj->db->option_get( 'cp_welcome_page' );

		// Get front page.
		$page_on_front = $this->parent_obj->db->option_wp_get( 'page_on_front' );

		// If the CommentPress title page exists and it's the front page.
		if ( $welcome_id !== false AND $page_on_front == $welcome_id ) {

			// Set to page ID.
			$is_home = $welcome_id;

		} else {

			// Not home page.
			$is_home = false;

		}

		// --<
		return $is_home;

	}



	/**
	 * Parse a WP menu.
	 *
	 * @since 3.0
	 *
	 * @param str $mode Either 'structural' or 'readable'.
	 * @return array $pages All 'book' pages.
	 */
	public function _parse_menu( $mode ) {

		// Init return.
		$pages = [];

		// Get menu locations.
		$locations = get_nav_menu_locations();

		// Check menu locations.
		if ( isset( $locations['toc'] ) ) {

			// Get the menu object.
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

			// Get the menu objects and store for later.
			$this->menu_objects = wp_get_nav_menu_items( $menu->term_id, $args );

			// If we get some.
			if ( $this->menu_objects ) {

				// If chapters are not pages, filter the menu items.
				if ( $this->parent_obj->db->option_get( 'cp_toc_chapter_is_page' ) != '1' ) {

					// Do we want all readable pages?
					if ( $mode == 'readable' ) {

						// Filter chapters out.
						$menu_items = $this->_filter_menu( $this->menu_objects );

					} else {

						// Structural - use a copy of the raw menu data.
						$menu_items = $this->menu_objects;

					}

				} else {

					// Use a copy of the raw menu data.
					$menu_items = $this->menu_objects;

				}

				// Init.
				$pages_to_get = [];

				// Convert to array of pages.
				foreach ( $menu_items AS $menu_item ) {

					// Is it a WP item?
					if ( isset( $menu_item->object_id ) ) {

						// Init pseudo WP_POST object.
						$pseudo_post = new stdClass();

						// Add post ID.
						$pseudo_post->ID = $menu_item->object_id;

						// Add menu ID (for filtering below).
						$pseudo_post->menu_id = $menu_item->ID;

						// Add menu item parent ID (for finding parent below).
						$pseudo_post->menu_item_parent = $menu_item->menu_item_parent;

						// Add comment count for possible calls for "next with comments".
						$pseudo_post->comment_count = $menu_item->comment_count;

						// Add to array of WP pages in menu.
						$pages[] = $pseudo_post;

					}

				}

			} // End check for menu items.

		} // End check for our menu.

		// --<
		return $pages;

	}



	/**
	 * Strip out all but lowest level menu items.
	 *
	 * @since 3.0
	 *
	 * @param array $menu_items An array of menu item objects.
	 * @return array $sub_items All lowest level items.
	 */
	public function _filter_menu( $menu_items ) {

		// Init return.
		$sub_items = [];

		// If we have any.
		if ( count( $menu_items ) > 0 ) {

			// Loop.
			foreach( $menu_items AS $key => $menu_obj ) {

				// Get item children.
				$kids = $this->_get_menu_item_children( $menu_items, $menu_obj );

				// Do we have any?
				if ( empty( $kids ) ) {

					// Add to our return array.
					$sub_items[] = $menu_obj;

				}

			}

		} // End have array check.

		// --<
		return $sub_items;

	}



	/**
	 * Utility to get children of a menu item.
	 *
	 * @since 3.0
	 *
	 * @param array $menu_items An array of menu item objects.
	 * @param obj $menu_obj The menu item object.
	 * @return array $sub_items The menu item children.
	 */
	public function _get_menu_item_children( $menu_items, $menu_obj ) {

		// Init return.
		$sub_items = [];

		// If we have any.
		if ( count( $menu_items ) > 0 ) {

			// Loop.
			foreach( $menu_items AS $key => $menu_item ) {

				// Is this item a child of the passed in menu object?
				if ( $menu_item->menu_item_parent == $menu_obj->ID ) {

					// Add to our return array.
					$sub_items[] = $menu_item;

				}

			}

		} // End have array check.

		// --<
		return $sub_items;

	}



	/**
	 * Utility to get parent of a menu item.
	 *
	 * @since 3.0
	 *
	 * @param obj $menu_obj The menu item object.
	 * @return int|bool $menu_item The parent menu item - or false if not found.
	 */
	public function _get_menu_item_parent( $menu_obj ) {

		// If we have any.
		if ( count( $this->menu_objects ) > 0 ) {

			// Loop.
			foreach( $this->menu_objects AS $key => $menu_item ) {

				// Is this item the first parent of the passed in menu object?
				if ( $menu_item->ID == $menu_obj->menu_item_parent ) {

					// --<
					return $menu_item;

				}

			}

		} // End have array check.

		// --<
		return false;

	}



	/**
	 * Get top parent menu item.
	 *
	 * @since 3.0
	 *
	 * @param object $menu_obj The queried menu object.
	 * @return object $parent_obj The parent object or false if not found.
	 */
	public function _get_top_menu_obj( $menu_obj ) {

		/*
		 * There is little point walking the menu tree because menu items can
		 * appear more than once in the menu.
		 *
		 * HOWEVER: for instances where people do use the menu sensibly, we
		 * should attempt to walk the tree as best we can.
		 */

		// Is this the top item?
		if ( $menu_obj->menu_item_parent == 0 ) {

			// Yes -> return the object.
			return $menu_obj;

		}

		// Get parent item.
		$parent_obj = $this->_get_menu_item_parent( $menu_obj );

		// Is the top item?
		if ( $parent_obj->menu_item_parent !== 0 ) {

			// No -> recurse upwards.
			return $this->_get_top_menu_obj( $parent_obj );

		}

		// Yes -> return the object.
		return $parent_obj;

	}



//##############################################################################



} // Class ends.



