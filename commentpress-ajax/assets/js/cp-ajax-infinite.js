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



/**
 * Create CommentPress Infinite sub-namespace
 */
CommentPress.infinite = {};



/* -------------------------------------------------------------------------- */



/**
 * Create settings class
 */
CommentPress.infinite.settings = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress infinite settings.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

		/*
		// do we have pushstate?
		if ( window.history ) {
			console.log( 'ONLOAD' );
			console.log( window.history );
		}
		*/

		// init post url
		me.init_post_url();

		// init post title
		me.init_post_title();

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.dom_ready = function() {

	};



	// init posts array
	this.posts = new Array;

	/**
	 * Setter for posts array
	 */
	this.set_posts = function( val ) {
		this.posts = val;
	};

	/**
	 * Getter for posts array
	 */
	this.get_posts = function() {
		return this.posts;
	};

	/**
	 * Test if post is in posts array
	 */
	this.in_posts = function( post_id ) {
		return $.in_array( post_id, this.posts );
	};

	/**
	 * Add to posts array
	 */
	this.add_to_posts = function( post_id ) {
		this.posts.push( post_id );
	};



	// init post url
	this.post_url = '';

	/**
	 * Init for post url
	 */
	this.init_post_url = function() {

		// get initial value
		this.post_url = document.location.href;

	};

	/**
	 * Setter for post url
	 */
	this.set_post_url = function( val ) {
		this.post_url = val;
	};

	/**
	 * Getter for post url
	 */
	this.get_post_url = function() {
		return this.post_url;
	};



	// init post title
	this.post_title = '';

	/**
	 * Init for post title
	 */
	this.init_post_title = function() {

		// get initial value
		this.post_title = document.title;

	};

	/**
	 * Setter for post title
	 */
	this.set_post_title = function( val ) {
		this.post_title = val;
	};

	/**
	 * Getter for post title
	 */
	this.get_post_title = function() {
		return this.post_title;
	};



	// init post "comments open" setting
	this.post_comments_open = 'n';

	/**
	 * Setter for post "comments open" setting
	 */
	this.set_post_comments_open = function( val ) {
		this.post_comments_open = val;
	};

	/**
	 * Getter for post "comments open" setting
	 */
	this.get_post_comments_open = function() {
		return this.post_comments_open;
	};



	// init comments array
	this.comments = new Array;

	/**
	 * Setter for comments array
	 */
	this.set_comments = function( val ) {
		this.comments = val;
	};

	/**
	 * Getter for comments array
	 */
	this.get_comments = function() {
		return this.comments;
	};

	/**
	 * Add to comments array
	 */
	this.add_to_comments = function( post_id, val ) {
		this.comments[post_id] = val;
		this.comments.push( post_id );
	};

}; // end CommentPress infinite settings class



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress infinite DOM class
 */
CommentPress.infinite.DOM = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress infinite DOM.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.dom_ready = function() {

		// init waypoints
		me.init_waypoints();

		// init popstate
		me.init_popstate();

	};



	/**
	 * Enable popstate
	 *
	 * @return void
	 */
	this.init_popstate = function() {

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
				var cpajax_history_supported = ???;

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

	};



	/**
	 * Enable waypoints
	 *
	 * @return void
	 */
	this.init_waypoints = function() {

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
					CommentPress.infinite.page.load_next( 'waypoint' );

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
	 * Enable waypoints on posts on a per-post basis
	 *
	 * @param int post_id The numeric ID of the WordPress post
	 * @return int offset The waypoint offset
	 */
	this.init_post_waypoint = function( post_id ) {

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
					me.update_dom( $( this ) );

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
					if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {

						// add admin bar height
						offset += CommentPress.settings.DOM.get_wp_adminbar_height();

					}

					// --<
					return offset;

				}

			}

		);

	}



	/**
	 * Handle data sent back from an AJAX call
	 *
	 * @param object response The data from the AJAX request as jQuery object
	 * @param string mode The type of data handling to perform
	 * @return void
	 */
	this.handle_ajax_data = function( response, mode ) {

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
		CommentPress.infinite.settings.set_post_url( response.url );
		CommentPress.infinite.settings.set_post_title( response.title );

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
			CommentPress.infinite.settings.add_to_posts( post_id );

			// get new post ID
			new_post_id = new_post_prop.split( '-' )[1];

			// find comments
			new_comments_obj = $( '.comments_container', $( response.comments ) );
			//console.log( 'NEW comments obj: ' );
			//console.log( new_comments_obj );

			// do we have a comment form?
			if ( CommentPress.infinite.commentform.get_comments_form() !== 0 ) {

				// get a copy of the comment form for this post
				post_comment_form = CommentPress.infinite.commentform.get_comments_form().clone();

				// change the form ID so we don't get double submissions
				$( 'form', post_comment_form ).attr( 'id', 'commentform' );

				// update its comment_post_ID
				$( '#comment_post_ID', post_comment_form ).val( new_post_id );

				// replace stored comments data
				CommentPress.infinite.settings.set_comments( new_comments_obj.append( post_comment_form ) );
				//console.log( 'REPLACE CommentPress.infinite.settings.comments:' );
				//console.log( CommentPress.infinite.settings.get_comments() );

				// set global depending on status
				CommentPress.infinite.settings.set_post_comments_open( 'n' );
				if ( response.comment_status == 'open' ) {
					CommentPress.infinite.settings.set_post_comments_open( 'y' );
				}

			}

			// does it have a menu item ID reference?
			if ( existing_menu_item != 'post' ) {

				// get item ID
				existing_menu_item_id = existing_menu_item.split( '-' )[1];

				// is it a custom menu?
				if ( existing_menu_item.match( 'wpcustom_menuid-' ) ) {

					// update menu: existing_menu_item_id
					CommentPress.infinite.menu.update_custom( existing_menu_item_id );

				} else {

					// update pages menu
					CommentPress.infinite.menu.update_pages( existing_menu_item_id );

				}

			}

			// add waypoints to new post
			me.init_post_waypoint( new_post_id );

			// broadcast
			$( document ).trigger( 'commentpress-new-post-loaded' );

			// enable CommentPress
			me.refresh();

		}

		// get new waypoint
		$.waypoints( 'refresh' );

		// if waypoint
		if ( mode == 'link' ) {

			// scroll to the page
			CommentPress.common.content.scroll_page( new_post_obj );

		}

	}



	/**
	 * Refresh all CommentPress listeners and methods
	 *
	 * @return void
	 */
	this.refresh = function() {

		// COMMENTPRESS AJAX COMMENTS

		// enable comment reassignment
		CommentPress.ajax.comments.reassign_comments();

		// initialise plugin
		CommentPress.ajax.comments.initialise();

		// initialise comment form
		CommentPress.ajax.comments.initialise_form();

		// BP GROUP SITES

		// refresh group sites, if present
		if ( $.is_function_defined( 'bpgsites_init_elements' ) ) {
			bpgsites_init_elements();
		}

	};



	/**
	 * Change content and comments
	 *
	 * @param object context The jQuery context object
	 * @return void
	 */
	this.update_dom = function( context ) {

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
				CommentPress.infinite.menu.update_custom( menu_item_id );

			} else {

				// update pages menu
				CommentPress.infinite.menu.update_pages( menu_item_id );

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
			document.title = CommentPress.infinite.settings.get_post_title();

			// do we have pushstate?
			if ( window.history ) {

				//console.log( 'PUSHSTATE' );
				//console.log( 'page_title: ' + CommentPress.infinite.settings.get_post_title() );

				// store items as HTML
				html_to_save = $.trim( context.html() );

				// create new state object to push to history
				window.history.pushState(

					{
						'post_id': post_id,
						'post_permalink': CommentPress.infinite.settings.get_post_url(),
						'page_title': CommentPress.infinite.settings.get_post_title(),
						'html': html_to_save,
						'comment_status': cp_comments_open
					},
					'',
					CommentPress.infinite.settings.get_post_url()

				);

			}

			// scroll to top of page
			$( document ).scrollTop( 0 );

			// set back to relative (add back to document flow)
			container.css( 'position', 'relative' );

			// re-renable container waypoint
			$( '#wrapper' ).waypoint( 'enable' );



			// get comments
			comments = CommentPress.infinite.settings.get_comments();
			//console.log( 'COMMENTS:' );
			//console.log( comments );

			// do we have a comment form?
			if ( CommentPress.infinite.commentform.get_comments_form() !== 0 ) {

				// disable the comment form
				addComment.disableForm();

			}

			// add new comments to the end
			$( '#comments_sidebar .comments_container' ).replaceWith( comments );

			// set global
			cp_comments_open = CommentPress.infinite.settings.get_post_comments_open();

			// do we have a comment form?
			if ( CommentPress.infinite.commentform.get_comments_form() !== 0 ) {

				// re-enable the comment form
				addComment.enableForm();

			}



			// enable CommentPress
			CommentPress.infinite.DOM.refresh();

			// get new waypoint
			$.waypoints( 'refresh' );

			// broadcast
			$( document ).trigger( 'commentpress-post-changed' );

		}

	};

}; // end CommentPress infinite DOM class



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress infinite page class
 */
CommentPress.infinite.page = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress infinite page.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

		// init nonce
		me.init_nonce();

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.dom_ready = function() {

		// init previous and next buttons
		me.state_navigation();

	};



	// init nonce
	this.cpajax_nonce = '';

	/**
	 * Init for comment form nonce
	 */
	this.init_nonce = function() {

		// get initial value from settings object
		if ( 'undefined' !== typeof CommentpressAjaxInfiniteSettings ) {
			this.cpajax_nonce = CommentpressAjaxInfiniteSettings.nonce;
		}

	};

	/**
	 * Setter for comment form nonce
	 */
	this.set_nonce = function( val ) {
		this.cpajax_nonce = val;
	};

	/**
	 * Getter for comment form nonce
	 */
	this.get_nonce = function() {
		return this.cpajax_nonce;
	};




	/**
	 * Intercept page navigation links
	 *
	 * @return void
	 */
	this.state_navigation = function() {

		// previous page button
		$('#container').on( 'click', '.previous_page', function( event ) {
			alert( 'back' );
			if ( event.preventDefault ) {event.preventDefault();}
			window.history.back();
		});

		// next page button
		$('#container').on( 'click', '.next_page', function( event ) {
			alert( 'forward' );
			if ( event.preventDefault ) {event.preventDefault();}
			me.load_next( 'link' );
		});

	}



	/**
	 * Make an AJAX call to retrieve next post/page
	 *
	 * @param string mode Triggered by either 'waypoint' or 'link'
	 * @return void
	 */
	this.load_next = function( mode ) {

		// declare vars
		var post_id;

		// get ID of last post
		post_id = $( '#main_wrapper .post:last-child' ).prop( 'id' ).split( '-' )[1];
		//console.log( 'wrapper current_post_id: ' + post_id );

		//console.log( 'WRAPPER GOING DOWN' );

		// kick out if we've already got this one
		if ( CommentPress.infinite.settings.in_posts( post_id ) ) { return; }

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
				nonce: me.get_nonce()
			},

			// ajax callback
			success: function( data ) {

				// handle incoming...
				CommentPress.infinite.DOM.handle_ajax_data( data, mode );

				// remove spinner
				$( '.cp_next_page_loading_wrapper' ).remove();

			}

		});

	};

}; // end CommentPress infinite page class



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress infinite menu class
 */
CommentPress.infinite.menu = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress infinite menu.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.dom_ready = function() {

	};



	/**
	 * Update the classes on the menu for a given menu_id
	 *
	 * @param string item_id The numerical ID of the menu item
	 * @return void
	 */
	this.update_custom = function( item_id ) {

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
	this.CommentPress.infinite.menu.update_pages = function( item_id ) {

		// update item
		$( '#toc_list .page_item' ).removeClass( 'current_page_item' );
		$( '.page-item-' + item_id ).addClass( 'current_page_item' );

		// update ancestors
		$( '#toc_list .page_item' ).removeClass( 'current_page_ancestor' );
		$( '.page-item-' + item_id ).parents( 'li' ).addClass( 'current_page_ancestor' );

	};

}; // end CommentPress infinite menu class



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress infinite comments class
 */
CommentPress.infinite.comments = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress infinite page.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.dom_ready = function() {

	};



	/**
	 * Store comments (not used)
	 *
	 * @return void
	 */
	this.store = function() {

		// declare vars
		var post_id;

		// store ID of current post
		post_id = $( '#wrapper .post' ).prop( 'id' ).split( '-' )[1];
		//CommentPress.infinite.settings.add_to_posts( post_id );

		// add new comments data to our array
		CommentPress.infinite.settings_add_to_comments( post_id, $( '#comments_sidebar .comments_container' ) );
		//console.log( 'INIT WITH CommentPress.infinite.settings.comments:' );
		//console.log( CommentPress.infinite.settings.get_comments() );

	};

}; // end CommentPress infinite comments class




/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress infinite commentform class
 */
CommentPress.infinite.commentform = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress infinite page.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.dom_ready = function() {

		//store comment form
		me.store();

	};



	// init comments form
	this.comments_form = 0;

	/**
	 * Setter for comments form
	 */
	this.set_comments_form = function( val ) {
		this.comments_form = val;
	};

	/**
	 * Getter for comments form
	 */
	this.get_comments_form = function() {
		return this.comments_form;
	};



	/**
	 * Copy and store the comment form
	 *
	 * @return void
	 */
	this.store = function() {

		// declare vars
		var new_form;

		// disable the comment form
		addComment.disableForm();

		// store a copy of the comment form
		me.set_comments_form( $( '#respond_wrapper' ).clone() );

		// get new form
		new_form = me.get_comments_form();

		// change the form ID so we don't get double submissions
		$( 'form', new_form ).attr( 'id', 'commentform-disabled' );

		// remove the extraneous classes
		if ( $( '#respond_wrapper', new_form ).hasClass( 'cp_force_displayed' ) ) {
			$( '#respond_wrapper', new_form ).removeClass( 'cp_force_displayed' );
		}
		if ( $( '#respond_wrapper', new_form ).hasClass( 'cp_force_closed' ) ) {
			$( '#respond_wrapper', new_form ).removeClass( 'cp_force_closed' );
		}

		// optionally remove from DOM if comments disabled
		if ( $( '#respond_wrapper' ).hasClass( 'cp_force_closed' ) ) {
			$( '#respond_wrapper' ).remove();
		}

		// enable the comment form (not necessary, it seems)
		//addComment.enableForm();

	};

}; // end CommentPress infinite comment form class



/* -------------------------------------------------------------------------- */



// do immediate init
CommentPress.infinite.settings.init();
CommentPress.infinite.DOM.init();
CommentPress.infinite.page.init();
CommentPress.infinite.menu.init();
CommentPress.infinite.commentform.init();
CommentPress.infinite.comments.init();



/* -------------------------------------------------------------------------- */



/**
 * Define what happens when the page is ready
 *
 * @return void
 */
jQuery(document).ready(function($) {

	// trigger DOM ready methods
	CommentPress.infinite.settings.dom_ready();
	CommentPress.infinite.DOM.dom_ready();
	CommentPress.infinite.page.dom_ready();
	CommentPress.infinite.menu.dom_ready();
	CommentPress.infinite.commentform.dom_ready();
	CommentPress.infinite.comments.dom_ready();

}); // end document.ready()



