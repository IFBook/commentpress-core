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






	/*
	============================================================================
	Properties
	============================================================================
	*/
	
	// parent object reference
	var $parent_obj;
	
	// next pages array
	var $next_pages = array();
	
	// previous pages array
	var $previous_pages = array();
	
	// next posts array
	var $next_posts = array();
	
	// previous posts array
	var $previous_posts = array();
	
	// page numbers array
	var $page_numbers = array();
	
	
	






	/** 
	 * @description: initialises this object
	 * @param object $parent_obj a reference to the parent object
	 * @return object
	 * @todo: 
	 *
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
	 * @description: PHP 4 constructor
	 */
	function CommentpressCoreNavigator( $parent_obj ) {
		
		// is this php5?
		if ( version_compare( PHP_VERSION, "5.0.0", "<" ) ) {
		
			// call php5 constructor
			$this->__construct( $parent_obj );
			
		}
		
		// --<
		return $this;

	}






	/** 
	 * @description: set up all items associated with this object
	 * @todo: 
	 *
	 */
	function initialise() {
	
		// init page lists
		$this->_init_page_lists();
		
		// init posts lists
		$this->_init_posts_lists();
		
	}







	/** 
	 * @description: if needed, destroys all items associated with this object
	 * @todo: 
	 *
	 */
	function destroy() {
	
	}







//##############################################################################







	/*
	============================================================================
	PUBLIC METHODS
	============================================================================
	*/
	





	/** 
	 * @description: get next page link
	 * @param boolean $with_comments requested page has comments - default false
	 * @return object $page_data if successful, boolean false if not
	 * @todo: 
	 *
	 */
	function get_next_page( $with_comments = false ) {
	
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
	 * @description: get previous page link
	 * @param boolean $with_comments requested page has comments - default false
	 * @return object $page_data if successful, boolean false if not
	 * @todo: 
	 *
	 */
	function get_previous_page( $with_comments = false ) {
	
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
	 * @description: get next post link
	 * @param boolean $with_comments requested post has comments - default false
	 * @return object $post_data if successful, boolean false if not
	 * @todo: 
	 *
	 */
	function get_next_post( $with_comments = false ) {
	
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
	 * @description: get previous post link
	 * @param boolean $with_comments requested post has comments - default false
	 * @return object $post_data if successful, boolean false if not
	 * @todo: 
	 *
	 */
	function get_previous_post( $with_comments = false ) {
	
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
	 * @description: get first viewable child page
	 * @param integer $page_id the page ID
	 * @return integer $first_child ID of the first child page (or false if not found)
	 * @todo:
	 *
	 */
	function get_first_child( $page_id ) {
	
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
		$kids =& get_children( $defaults );
		
		// do we have any?
		if ( empty( $kids ) ) {
		
			// no children
			return false;
		
		}
		


		// we got some...
		return $this->_get_first_child( $kids );

	}







	/** 
	 * @description: get list of 'book' pages
	 * @param string $mode either 'structural' or 'readable'
	 * @return array $pages all 'book' pages
	 * @todo:
	 *
	 */
	function get_book_pages( $mode = 'readable' ) {
	
		// init
		$all_pages = array();
		
		
		
		// do we have a nav menu enabled?
		if ( has_nav_menu( 'toc' ) ) {
			
			// YES - a custom menu disables "book" navigation
			
			// --<
			return $all_pages;
			
			
			
			/*
			
			// -----------------------------------------------------------------
			// For posterity: here's what I considered when trying to tease out
			// page lists from menu items. However, menus can contain anything,
			// including external links, so the effort to parse the menu doesn't
			// seem to be worthwhile.
			// -----------------------------------------------------------------
			
			// check menu locations
			if ( ( $locations = get_nav_menu_locations() ) && isset( $locations[ $menu_name ] ) ) {
				
				// get the menu object
				$menu = wp_get_nav_menu_object( $locations[ $menu_name ] );
			
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
	
				// get the page references
				$menu_items = wp_get_nav_menu_items( $menu->term_id, $args );
				
				// init
				$pages_to_get = array();
				
				// if we get some
				if ( $menu_items ) {
					
					// convert to array of pages
					foreach ( $menu_items AS $menu_item ) {
					
						// is it a WP item?
						if ( isset( $menu_item->object_id ) ) {
							
							// construct array of WP pages in menu
							$pages_to_get[] = $menu_item->object_id;
							
						}
					
					}
				
					print_r( array( 'menu_items' => $menu_items, 'pages_to_get' => $pages_to_get ) ); die();

					// set list pages defaults
					$defaults = array(
						'child_of' => 0, 
						'sort_order' => 'ASC',
						'sort_column' => 'menu_order, post_title', 
						'hierarchical' => 1,
						'exclude' => '', 
						'include' => implode( ',', $pages_to_get ),
						'meta_key' => '', 
						'meta_value' => '',
						'authors' => '', 
						'parent' => -1, 
						'exclude_tree' => ''
					);
					
					// get them
					$all_pages = get_pages( $defaults );
					
				}
				
			}
		
			*/



		} else {
		
		
	
			// -----------------------------------------------------------------
			// construct "book" navigation based on pages
			// -----------------------------------------------------------------
				
			// default to no excludes
			$excludes = '';
			
			// get special pages
			$special_pages = $this->parent_obj->db->option_get( 'cp_special_pages' );
			
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
						$special_pages[] = $reg_page->ID;
					
					}
				
				}
			
			}
			
			// are there any?
			if ( is_array( $special_pages ) AND count( $special_pages ) > 0 ) {
	
				// format them for the exclude param
				$excludes = implode( ',', $special_pages );
				
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
			$all_pages = get_pages( $defaults );
			
			
			
			// if we have any pages...
			if ( count( $all_pages ) > 0 ) {
	
				// if chapters are not pages...
				if ( $this->parent_obj->db->option_get( 'cp_toc_chapter_is_page' ) != '1' ) {
				
					// do we want all readable pages?
					if ( $mode == 'readable' ) {
					
						// filter chapters out
						$all_pages = $this->_filter_chapters( $all_pages );
					
					}
					
				}
				
				// if Theme My Login is present...
				if ( defined( 'TML_ABSPATH' ) ) {
				
					// filter its page out
					$all_pages = $this->_filter_theme_my_login_page( $all_pages );
					
				}
				
			}
				
				
			
		} // end check for custom menu
		
		
		
		//print_r( $all_pages ); die();
		


		// --<
		return $all_pages;

	}







	/** 
	 * @description: get first readable 'book' page
	 * @return integer $id ID of the first page (or false if not found)
	 * @todo:
	 *
	 */
	function get_first_page() {
	
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
	 * @description: get page number
	 * @param integer $page_id the page ID
	 * @return integer $number number of the page
	 * @todo:
	 *
	 */
	function get_page_number( $page_id ) {
	
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
			if ( !$first_child ) {
				
				// get page number from array
				$num = $this->_get_page_number( $page_id );
				
			}
			
		}
	
	
	
		// --<
		return $num;

	}







	/** 
	 * @description: get page number
	 * @param integer $page_id the page ID
	 * @return integer $number number of the page
	 * @todo:
	 *
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
	 * @description: redirect to child
	 * @todo: 
	 *
	 */
	function redirect_to_child() {
	
		// only on pages
		if ( !is_page() ) { return; }
		
		
		
		// access post object
		global $post;
		
		// do we have one?
		if ( !is_object( $post ) ) {
		
			// --<
			die( 'no post object' );
			
		}
		


		// are parent pages viewable?
		$viewable = ( $this->parent_obj->db->option_get( 'cp_toc_chapter_is_page' ) == '1' ) ? true : false;
		
		// get id of first child
		$first_child = $this->get_first_child( $post->ID );
		
		// our conditions
		if ( $first_child AND !$viewable ) {
			
			// get link
			$redirect = get_permalink( $first_child );

			// do the redirect
			//header( "HTTP/1.1 301 Moved Permanently" ); 
			header( "Location: $redirect" );
		
		}

	}
	
	
	
	
	



//##############################################################################







	/*
	============================================================================
	PRIVATE METHODS
	============================================================================
	*/
	
	
	



	/** 
	 * @description: object initialisation
	 * @todo:
	 *
	 */
	function _init() {
	
		// is_page() and is_single() are not yet defined, so we init this object when
		// wp_head() is fired - see initialise() above
	
	}







	/** 
	 * @description: set up page list
	 * @todo: 
	 *
	 */
	function _init_page_lists() {
	
		// if we're navigating pages
		if( is_page() ) {
			
			
			
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
			
			
			
		} // end is_page() check
	
	}







	/** 
	 * @description: set up posts list
	 * @todo: 
	 *
	 */
	function _init_posts_lists() {
	
		// if we're navigating posts
		if( is_single() ) {
			
			
			
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
			
			
			
		} // end is_single() check
	
	}







	/** 
	 * @description: strip out all but lowest level pages
	 * @param array $pages array of page objects
	 * @return array $subpages all subpages
	 * @todo: this only works one level deep?
	 *
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
				$kids =& get_children( $defaults );
				
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
	 * @description: get first published child, however deep
	 * @param array $pages array of page objects
	 * @return array $subpages all subpages
	 * @todo: 
	 *
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
				if ( !empty( $kids ) ) {
				
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
	 * @description: generates page numbers
	 * @param array $pages array of page objects in the 'book'
	 * @todo: refine by section, page meta value etc
	 *
	 */
	function _generate_page_numbers( $pages ) {
	
		// if we have any...
		if ( count( $pages ) > 0 ) {
		
			// init with page 1
			$num = 1;
		
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
				
					// get top level parent
					$top_page_id = $this->_get_top_parent_id( $page_obj->ID );
				
					// if the custom field has a value...
					if ( get_post_meta( $top_page_id, $key, true ) !== '' ) {
					
						// get it
						$format = get_post_meta( $top_page_id, $key, true );
				
					}
					
				}
				
				// if it's roman
				if ( $format == 'roman' ) {
				
					// convert arabic to roman
					$this->page_numbers[ $page_obj->ID ] = $this->_number_to_roman( $num );
				
				} else {
				
					// if flag not set
					if ( !isset( $flag ) ) {
					
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
	 * @description: utility to remove the Theme My Login page
	 * @return boolean $success
	 * @todo: pass the array
	 *
	 */
	function _filter_theme_my_login_page( $pages ) {
		
		// init return
		$clean = array();
		
		
	
		// if we have any...
		if ( count( $pages ) > 0 ) {
		
			// loop
			foreach( $pages AS $page_obj ) {
			
				// do we have any?
				if ( !$this->_detect_login_page( $page_obj ) ) {
				
					// add to our return array
					$clean[] = $page_obj;
				
				}
			
			}

		} // end have array check



		// --<
		return $clean;
	
	}
	
	
	
	
	
	
	

	/** 
	 * @description: utility to detect the Theme My Login page
	 * @return boolean $success
	 * @todo: 
	 *
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
	 * @description: utility to convert arabic to roman numerals
	 * @return boolean $result the roman equivalent
	 * @todo: 
	 *
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
	 * @description: get top parent page id
	 * @param integer $post_id the queried page id
	 * @return integer $post_id
	 * @todo: 
	 *
	 */
	function _get_top_parent_id( $post_id ){
	
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
	
	
	
	
	
	
//##############################################################################







} // class ends






?>