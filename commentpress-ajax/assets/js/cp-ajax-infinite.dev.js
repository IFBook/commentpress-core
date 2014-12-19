/*
================================================================================
CommentPress AJAX Infinite Scroll
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

This script enables infinite scroll when the CommentPress theme is active.

--------------------------------------------------------------------------------
*/



// init vars
var cpajax_nonce,
	cpajax_post_url,
	cpajax_post_title,
	cpajax_infinite_posts,
	cpajax_infinite_comments,
	cpajax_comment_form,
	cpajax_comments_open,
	cpajax_history_supported;

// test for our localisation object
if ( 'undefined' !== typeof CommentpressAjaxInfiniteSettings ) {

	// reference our object vars
	cpajax_nonce = CommentpressAjaxInfiniteSettings.nonce;

}

// set defaults
cpajax_infinite_posts = new Array;

// latest comments
cpajax_infinite_comments = '';

// comment form
cpajax_comment_form = 0;

// page attributes
cpajax_post_url = document.location.href;
cpajax_post_title = document.title;

// do we support history?
cpajax_history_supported = false;

/*
// do we have pushstate?
if ( window.history ) {
	console.log( 'ONLOAD' );
	console.log( window.history );
}
*/



/**
 * jQuery wrapper
 *
 * This wrapper ensures that jQuery can be addressed using the $ shorthand from
 * anywhere within the script.
 */
;( function( $ ) {



	/**
	 * Define what happens when the page is ready
	 *
	 * @return void
	 */
	$( document ).ready( function( $ ) {

		// store comment form
		cpajax_store_comment_form();

		// enable waypoints
		cpajax_enable_wrapper_waypoints();

		// enable history
		cpajax_enable_state_navigation();

	}); // end document.ready()



	/**
	 * Intercept page navigation links
	 *
	 * @return void
	 */
	function cpajax_enable_state_navigation() {

		// unbind first to allow repeated calls to this function
		jQuery( '.previous_page, .next_page' ).unbind( 'click' );

		// only apply when there is a history
		if ( window.history.length > 0 ) {

			// previous page button
			$( '.previous_page' ).click( function( e ) {
				alert( 'back' );
				if ( event.preventDefault ) {event.preventDefault();}
				window.history.back();
				return false;
			});

		}

		// next page button
		$( '.next_page' ).click( function( e ) {
			alert( 'forward' );
			if ( event.preventDefault ) {event.preventDefault();}
			cpajax_load_next_page( 'link' );
			return false;
		});

	}



	/**
	 * Copy and store the comment form
	 *
	 * @return void
	 */
	function cpajax_store_comment_form() {

		// disable the comment form
		addComment.disableForm();

		// store a copy of the comment form
		cpajax_comment_form = $( '#respond_wrapper' ).clone();

		// change the form ID so we don't get double submissions
		$( 'form', cpajax_comment_form ).attr( 'id', 'commentform-disabled' );

		// remove the extraneous classes
		if ( $( '#respond_wrapper', cpajax_comment_form ).hasClass( 'cp_force_displayed' ) ) {
			$( '#respond_wrapper', cpajax_comment_form ).removeClass( 'cp_force_displayed' );
		}
		if ( $( '#respond_wrapper', cpajax_comment_form ).hasClass( 'cp_force_closed' ) ) {
			$( '#respond_wrapper', cpajax_comment_form ).removeClass( 'cp_force_closed' );
		}

		// optionally remove from DOM if comments disabled
		if ( $( '#respond_wrapper' ).hasClass( 'cp_force_closed' ) ) {
			$( '#respond_wrapper' ).remove();
		}

		// enable the comment form (not necessary, it seems)
		//addComment.enableForm();

	}



	/**
	 * Store comments (not used)
	 *
	 * @return void
	 */
	function cpajax_store_comments() {

		// declare vars
		var post_id;

		// store ID of current post
		post_id = $( '#wrapper .post' ).prop( 'id' ).split( '-' )[1];
		//cpajax_infinite_posts.push( post_id );

		// add new comments data to our array
		cpajax_infinite_comments[post_id] = $( '#comments_sidebar .comments_container' );
		//console.log( 'INIT WITH cpajax_infinite_comments:' );
		//console.log( cpajax_infinite_comments );

	}



	/**
	 * Update the classes on the menu for a given menu_id
	 *
	 * @param string item_id The numerical ID of the menu item
	 * @return void
	 */
	function cpajax_update_custom_menu( item_id ) {

		// update item
		$( '#toc_list .menu-item' ).removeClass( 'current_page_item' );
		$( '#menu-item-' + item_id ).addClass( 'current_page_item' );

		// update ancestors
		$( '#toc_list .menu-item' ).removeClass( 'current_page_ancestor' );
		$( '#menu-item-' + item_id ).parents( 'li' ).addClass( 'current_page_ancestor' );

	}



	/**
	 * Update the classes on the WP pages menu for a given page_id
	 *
	 * @param string item_id The numerical ID of the menu item
	 * @return void
	 */
	function cpajax_update_pages_menu( item_id ) {

		// update item
		$( '#toc_list .page_item' ).removeClass( 'current_page_item' );
		$( '.page-item-' + item_id ).addClass( 'current_page_item' );

		// update ancestors
		$( '#toc_list .page_item' ).removeClass( 'current_page_ancestor' );
		$( '.page-item-' + item_id ).parents( 'li' ).addClass( 'current_page_ancestor' );

	}



	/**
	 * Refresh all CommentPress listeners and methods
	 *
	 * @return void
	 */
	function cpajax_refresh_commentpress() {

		// COMMENTPRESS CORE

		// set up comment headers
		commentpress_setup_comment_headers();

		// set up comment headers
		commentpress_setup_comment_headers();

		// enable animations on clicking comment permalinks
		commentpress_enable_comment_permalink_clicks();

		// set up comment icons (these used to be paragraph permalinks - now 'add comment')
		commentpress_setup_para_permalink_icons();

		// set up clicks in the page content:
		// title
		// paragraph content
		// paragraph icons (newly assigned as paragraph permalinks - also 'read comments')
		commentpress_setup_page_click_actions();

		// set up user-defined links to paragraphs
		commentpress_setup_para_links();

		// set up activity links
		cp_enable_context_clicks();

		// set up activity headers
		commentpress_setup_context_headers();

		// set up footnote plugin compatibility
		commentpress_setup_footnotes_compatibility();

		// AJAX COMMENTS

		// enable comment reassignment
		cpajax_reassign_comments();

		// initialise plugin
		cpajax_initialise();

		// initialise comment form
		cpajax_initialise_form();

		// re-enable page nav buttons
		cpajax_enable_state_navigation();

		// BP GROUP SITES

		// refresh group sites, if present
		if ( $.is_function_defined( 'bpgsites_init_elements' ) ) {
			bpgsites_init_elements();
		}

	}



	/**
	 * Enable waypoints
	 *
	 * @return void
	 */
	function cpajax_enable_wrapper_waypoints() {

		/**
		 * Define what happens when the bottom of the page is in view
		 *
		 * @return void
		 */
		$( '#wrapper' ).waypoint(

			/**
			 * Waypoint callback
			 *
			 * @return void
			 */
			function( direction ) {

				// only look for downward motion
				if ( direction === 'down' ) {

					// direction: UP
					//console.log( 'up: ' + post_id );

					// load next page
					cpajax_load_next_page( 'waypoint' );

				} else {

					// direction: UP
					//console.log( 'up: ' + post_id );

				}

			},

			// config options
			{
				offset: 'bottom-in-view'
				//offset: function() { return -$( this ).height / 2; }
				//offset: 100
			}

		);

	}



	/**
	 * Make an AJAX call to retrieve next post/page
	 *
	 * @param string mode Triggered by either 'waypoint' or 'link'
	 * @return void
	 */
	function cpajax_load_next_page( mode ) {

		// declare vars
		var post_id;

		// get ID of last post
		post_id = $( '#main_wrapper .post:last-child' ).prop( 'id' ).split( '-' )[1];
		//console.log( 'wrapper current_post_id: ' + post_id );

		//console.log( 'WRAPPER GOING DOWN' );

		// kick out if we've already got this one
		if ( $.in_array( post_id, cpajax_infinite_posts ) ) { return; }

		// direction: DOWN
		//console.log( 'down: ' + post_id );

		// show loading
		//$( '#loading' ).show();

		// init AJAX spinner
		$( '#main_wrapper' ).after(
			'<div class="cp_next_page_loading_wrapper" style="text-align: center">' +
				'<img src="' + cpajax_spinner_url + '" id="cp_next_page_loading" alt="' + cpajax_lang[0] + '" />' +
			'</div>'
		);

		// call WordPress
		$.ajax({

			// wp ajax script
			url: cpajax_ajax_url,

			// method
			type: 'POST',

			// data expected
			dataType: 'json',

			// data to send
			data: {
				action: 'cpajax_load_next_page',
				current_post_id: post_id,
				nonce: cpajax_nonce
			},

			// ajax callback
			success: function( data ) {

				// handle incoming...
				cpajax_handle_data( data, mode );

				// remove spinner
				$( '.cp_next_page_loading_wrapper' ).remove();

			}

		});

	}



	/**
	 * Handle data sent back from an AJAX call
	 *
	 * @param object response The data from the AJAX request as jQuery object
	 * @param string mode The type of data handling to perform
	 * @return void
	 */
	function cpajax_handle_data( response, mode ) {

		// declare vars
		var new_post_obj, new_post_prop, new_post_id,
			existing_menu_item, existing_menu_item_id,
			new_comments_obj, post_comment_form,
			post_id;

		// get ID of last post
		post_id = $( '#main_wrapper .post:last-child' ).prop( 'id' ).split( '-' )[1];
		//console.log( 'wrapper current_post_id: ' + post_id );

		//console.log( response );
		//return;

		// store page data
		cpajax_post_url = response.url;
		cpajax_post_title = response.title;

		/*
		// $ 1.9 fails to recognise the response as HTML, so
		// we *must* use parseHTML if it's available...
		if ( $.parseHTML ) {

			// if our $ version is 1.8+, it'll have parseHTML
			response =  $( $.parseHTML( data ) );

		} else {

			// get our data as object in the basic way
			response = $( data );

		}
		*/

		// get existing menu item before we append
		existing_menu_item = $( '#main_wrapper .post:last-child' ).prop( 'class' );
		//console.log( 'LOAD: existing_menu_item: ' + existing_menu_item );

		// find post object
		new_post_obj = $( '.post', $( response.content ) );
		//console.log( 'NEW post obj: ' + new_post_obj );

		// add new post to the end
		$( '#main_wrapper' ).append( new_post_obj.parents( '.page_wrapper' ) );

		// find post ID property
		new_post_prop = new_post_obj.prop( 'id' );
		//console.log( 'NEW post ID: ' + new_post_prop );

		// if we get one...
		if ( typeof new_post_prop !== 'undefined' ) {

			// add calling post ID to our array
			cpajax_infinite_posts.push( post_id );

			// get new post ID
			new_post_id = new_post_prop.split( '-' )[1];

			// find comments
			new_comments_obj = $( '.comments_container', $( response.comments ) );
			//console.log( 'NEW comments obj: ' );
			//console.log( new_comments_obj );

			// do we have a comment form?
			if ( cpajax_comment_form !== 0 ) {

				// get a copy of the comment form for this post
				post_comment_form = cpajax_comment_form.clone();

				// change the form ID so we don't get double submissions
				$( 'form', post_comment_form ).attr( 'id', 'commentform' );

				// update its comment_post_ID
				$( '#comment_post_ID', post_comment_form ).val( new_post_id );

				// replace stored comments data
				cpajax_infinite_comments = new_comments_obj.append( post_comment_form );
				//console.log( 'REPLACE cpajax_infinite_comments:' );
				//console.log( cpajax_infinite_comments );

				// set global depending on status
				cpajax_comments_open = 'n';
				if ( response.comment_status == 'open' ) {
					cpajax_comments_open = 'y';
				}

			}

			// does it have a menu item ID reference?
			if ( existing_menu_item != 'post' ) {

				// get item ID
				existing_menu_item_id = existing_menu_item.split( '-' )[1];

				// is it a custom menu?
				if ( existing_menu_item.match( 'wpcustom_menuid-' ) ) {

					// update menu: existing_menu_item_id
					cpajax_update_custom_menu( existing_menu_item_id );

				} else {

					// cpajax_update_pages_menu
					cpajax_update_pages_menu( existing_menu_item_id );

				}

			}

			// add waypoints to new post
			cpajax_enable_post_comments_waypoint( new_post_id );

			// broadcast
			$( document ).trigger( 'commentpress-new-post-loaded' );

			// enable CommentPress
			cpajax_refresh_commentpress();

		}

		// get new waypoint
		$.waypoints( 'refresh' );

		// if waypoint
		if ( mode == 'link' ) {

			// scroll to the page
			commentpress_scroll_page( new_post_obj );

		}

	}



	/**
	 * Enable waypoints on posts on a per-post basis
	 *
	 * @param int post_id The numeric ID of the WordPress post
	 * @return int offset The waypoint offset
	 */
	function cpajax_enable_post_comments_waypoint( post_id ) {

		// The top of a post has reached the top of the viewport scrolling downwards
		$( '#post-' + post_id + '.post' ).parent().parent().waypoint(

			/**
			 * Waypoint callback
			 *
			 * @param string direction The direction of scroll
			 * @return void
			 */
			function( direction ) {

				// only look for downward motion
				if ( direction === 'down' ) {

					// trigger page change
					cpajax_trigger_page_change( $( this ) );

				}

			},

			// config options
			{

				// only trigger once
				triggerOnce: true,

				// get offset
				offset: function() {

					// define vars
					var offset;

					// get header offset
					offset = $( '#header' ).height();

					// is the admin bar shown?
					if ( cp_wp_adminbar == 'y' ) {

						// add admin bar height
						offset += cp_wp_adminbar_height;

					}

					// --<
					return offset;

				}

			}

		);

	}



	/**
	 * Change content and comments
	 *
	 * @param object context The jQuery context object
	 * @return void
	 */
	function cpajax_trigger_page_change( context ) {

		// declare vars
		var post_obj, post_id, menu_item, new_comments, container, item_above,
			html_to_save, comments;

		// trace
		//console.log( 'TOP GOING DOWN (this)' );
		//console.log( context );
		//console.log( 'TOP GOING DOWN ' + $( '.post', context ).prop( 'id' ) );

		// get post object
		post_obj = $( '.post', context );

		// ID of current post that's come into view
		post_id = post_obj.prop( 'id' ).split( '-' )[1];
		//console.log( 'DOWN: post_id: ' + post_id );

		// get menu item from class
		menu_item = post_obj.prop( 'class' );
		//console.log( 'DOWN: menu_item: ' + menu_item );

		// did we get one of our target ones?
		if ( menu_item != 'post' ) {

			// got one
			menu_item_id = menu_item.split( '-' )[1];

			// direction: DOWN
			//console.log( 'DOWN: post_id ' + post_id + ' menu_item_id ' + menu_item_id );

			// is it a custom menu?
			if ( menu_item.match( 'wpcustom_menuid-' ) ) {

				// update menu: menu_item_id
				cpajax_update_custom_menu( menu_item_id );

			} else {

				// cpajax_update_pages_menu
				cpajax_update_pages_menu( menu_item_id );

			}

			// disable container waypoint to prevent accidental triggering
			$( '#wrapper' ).waypoint( 'disable' );

			// remove item above
			container = $( '#main_wrapper' );
			item_above = context.prev();

			// trash waypoints
			item_above.waypoint( 'destroy' );
			context.waypoint( 'destroy' );

			// set as fixed (remove from document flow)
			container.css( 'position', 'fixed' );

			// remove item
			//item_above.css( 'visibility', 'hidden' );
			//item_above.css( 'display', 'none' );
			item_above.remove();

			// set document title
			document.title = cpajax_post_title;

			// do we have pushstate?
			if ( window.history ) {

				//console.log( 'PUSHSTATE' );
				//console.log( 'page_title: ' + cpajax_post_title );

				// store items as HTML
				html_to_save = $.trim( context.html() );

				// create new state object to push to history
				window.history.pushState(

					{
						'post_id': post_id,
						'post_permalink': cpajax_post_url,
						'page_title': cpajax_post_title,
						'html': html_to_save,
						'comment_status': cp_comments_open
					},
					'',
					cpajax_post_url

				);

			}

			// scroll to top of page
			$( document ).scrollTop( 0 );

			// set back to relative (add back to document flow)
			container.css( 'position', 'relative' );

			// re-renable container waypoint
			$( '#wrapper' ).waypoint( 'enable' );



			// get comments
			comments = cpajax_infinite_comments;
			//console.log( 'COMMENTS:' );
			//console.log( comments );

			// do we have a comment form?
			if ( cpajax_comment_form !== 0 ) {

				// disable the comment form
				addComment.disableForm();

			}

			// add new comments to the end
			$( '#comments_sidebar .comments_container' ).replaceWith( comments );

			// set global
			cp_comments_open = cpajax_comments_open;

			// do we have a comment form?
			if ( cpajax_comment_form !== 0 ) {

				// re-enable the comment form
				addComment.enableForm();

			}



			// enable CommentPress
			cpajax_refresh_commentpress();

			// get new waypoint
			$.waypoints( 'refresh' );

			// broadcast
			$( document ).trigger( 'commentpress-post-changed' );

		}

	}



	/**
	 * Change content and comments
	 *
	 * @param object e The state object
	 * @return void
	 */
	window.onpopstate = function( e ) {

		// did we get a state object?
		if ( e.state ) {

			//console.log( 'ONPOPSTATE' );
			//console.log( e );

			/*
			// test if history supported
			if ( cpajax_history_supported === false ) {

				alert( 'gah' );

				// refresh from server
				document.location.reload( true );
				return;

			}
			*/

			// set title
			document.title = e.state.page_title;

			// set html
			$( '#main_wrapper' ).prepend( '<div class="page_wrapper cp_page_wrapper">' + e.state.html + '</div>' );

			// add comments back in
			//$( '#comments_sidebar .comments_container' ).replaceWith( $( e.state.comments ) );

		}

	};



})( jQuery );



