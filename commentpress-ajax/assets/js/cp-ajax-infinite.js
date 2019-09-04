/*
================================================================================
CommentPress Core AJAX Infinite Scroll
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

This script enables infinite scroll when a CommentPress Core compatible theme is
active. Still in development.

--------------------------------------------------------------------------------
*/



/**
 * Create CommentPress Infinite sub-namespace.
 *
 * @since 3.8
 */
CommentPress.infinite = {};



/* -------------------------------------------------------------------------- */



/**
 * Create settings class.
 *
 * @since 3.8
 */
CommentPress.infinite.settings = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress infinite settings.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.init = function() {

		/*
		// Do we have pushstate?
		if ( window.history ) {
			console.log( 'ONLOAD' );
			console.log( window.history );
		}
		*/

		// Init post URL.
		me.init_post_url();

		// Init post title.
		me.init_post_title();

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.dom_ready = function() {

	};



	// Init posts array
	this.posts = new Array;

	/**
	 * Setter for posts array.
	 *
	 * @since 3.8
	 */
	this.set_posts = function( val ) {
		this.posts = val;
	};

	/**
	 * Getter for posts array.
	 *
	 * @since 3.8
	 */
	this.get_posts = function() {
		return this.posts;
	};

	/**
	 * Test if post is in posts array.
	 *
	 * @since 3.8
	 */
	this.in_posts = function( post_id ) {
		return $.in_array( post_id, this.posts );
	};

	/**
	 * Add to posts array.
	 *
	 * @since 3.8
	 */
	this.add_to_posts = function( post_id ) {
		this.posts.push( post_id );
	};



	// Init post URL.
	this.post_url = '';

	/**
	 * Init for post url.
	 *
	 * @since 3.8
	 */
	this.init_post_url = function() {

		// Get initial value.
		this.post_url = document.location.href;

	};

	/**
	 * Setter for post url.
	 *
	 * @since 3.8
	 */
	this.set_post_url = function( val ) {
		this.post_url = val;
	};

	/**
	 * Getter for post url.
	 *
	 * @since 3.8
	 */
	this.get_post_url = function() {
		return this.post_url;
	};



	// Init post title.
	this.post_title = '';

	/**
	 * Init for post title.
	 *
	 * @since 3.8
	 */
	this.init_post_title = function() {

		// Get initial value.
		this.post_title = document.title;

	};

	/**
	 * Setter for post title.
	 *
	 * @since 3.8
	 */
	this.set_post_title = function( val ) {
		this.post_title = val;
	};

	/**
	 * Getter for post title.
	 *
	 * @since 3.8
	 */
	this.get_post_title = function() {
		return this.post_title;
	};



	// Init post "comments open" setting
	this.post_comments_open = 'n';

	/**
	 * Setter for post "comments open" setting.
	 *
	 * @since 3.8
	 */
	this.set_post_comments_open = function( val ) {
		this.post_comments_open = val;
	};

	/**
	 * Getter for post "comments open" setting.
	 *
	 * @since 3.8
	 */
	this.get_post_comments_open = function() {
		return this.post_comments_open;
	};



	// Init comments array
	this.comments = new Array;

	/**
	 * Setter for comments array.
	 *
	 * @since 3.8
	 */
	this.set_comments = function( val ) {
		this.comments = val;
	};

	/**
	 * Getter for comments array.
	 *
	 * @since 3.8
	 */
	this.get_comments = function() {
		return this.comments;
	};

	/**
	 * Add to comments array.
	 *
	 * @since 3.8
	 */
	this.add_to_comments = function( post_id, val ) {
		this.comments[post_id] = val;
		this.comments.push( post_id );
	};

}; // End CommentPress infinite settings class.



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress infinite DOM class.
 *
 * @since 3.8
 */
CommentPress.infinite.DOM = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress infinite DOM.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.init = function() {

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.dom_ready = function() {

		// Init waypoints.
		me.init_waypoints();

		// Init popstate.
		me.init_popstate();

	};



	/**
	 * Enable popstate.
	 *
	 * @since 3.8
	 */
	this.init_popstate = function() {

		/**
		 * Change content and comments.
		 *
		 * @since 3.8
		 *
		 * @param object e The state object.
		 */
		window.onpopstate = function( e ) {

			// Did we get a state object?
			if ( e.state ) {

				//console.log( 'ONPOPSTATE' );
				//console.log( e );

				/*
				var cpajax_history_supported = ???;

				// Test if history supported.
				if ( cpajax_history_supported === false ) {

					alert( 'gah' );

					// Refresh from server.
					document.location.reload( true );
					return;

				}
				*/

				// Set title.
				document.title = e.state.page_title;

				// Set HTML.
				$( '#main_wrapper' ).prepend( '<div class="page_wrapper cp_page_wrapper">' + e.state.html + '</div>' );

				// Add comments back in.
				//$( '#comments_sidebar .comments_container' ).replaceWith( $( e.state.comments ) );

			}

		};

	};



	/**
	 * Enable waypoints.
	 *
	 * @since 3.8
	 */
	this.init_waypoints = function() {

		/**
		 * Define what happens when the bottom of the page is in view.
		 *
		 * @since 3.8
		 */
		$( '#wrapper' ).waypoint(

			/**
			 * Waypoint callback.
			 *
			 * @since 3.8
			 */
			function( direction ) {

				// Only look for downward motion.
				if ( direction === 'down' ) {

					// Direction: UP.
					//console.log( 'up: ' + post_id );

					// Load next page.
					CommentPress.infinite.page.load_next( 'waypoint' );

				} else {

					// Direction: UP.
					//console.log( 'up: ' + post_id );

				}

			},

			// Config options
			{
				offset: 'bottom-in-view'
				//offset: function() { return -$( this ).height / 2; }
				//offset: 100
			}

		);

	}



	/**
	 * Enable waypoints on posts on a per-post basis.
	 *
	 * @since 3.8
	 *
	 * @param int post_id The numeric ID of the WordPress post.
	 * @return int offset The waypoint offset.
	 */
	this.init_post_waypoint = function( post_id ) {

		// The top of a post has reached the top of the viewport scrolling downwards.
		$( '#post-' + post_id + '.post' ).parent().parent().waypoint(

			/**
			 * Waypoint callback.
			 *
			 * @since 3.8
			 *
			 * @param string direction The direction of scroll.
			 */
			function( direction ) {

				// Only look for downward motion.
				if ( direction === 'down' ) {

					// Trigger page change.
					me.update_dom( $( this ) );

				}

			},

			// Config options.
			{

				// Only trigger once.
				triggerOnce: true,

				// Get offset.
				offset: function() {

					// Define vars.
					var offset;

					// Get header offset.
					offset = $( '#header' ).height();

					// Is the admin bar shown?
					if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {

						// Add admin bar height.
						offset += CommentPress.settings.DOM.get_wp_adminbar_height();

					}

					// --<
					return offset;

				}

			}

		);

	}



	/**
	 * Handle data sent back from an AJAX call.
	 *
	 * @since 3.8
	 *
	 * @param object response The data from the AJAX request as jQuery object.
	 * @param string mode The type of data handling to perform.
	 */
	this.handle_ajax_data = function( response, mode ) {

		// Declare vars.
		var new_post_obj, new_post_prop, new_post_id,
			existing_menu_item, existing_menu_item_id,
			new_comments_obj, post_comment_form,
			post_id;

		// Get ID of last post.
		post_id = $( '#main_wrapper .post:last-child' ).prop( 'id' ).split( '-' )[1];
		//console.log( 'wrapper current_post_id: ' + post_id );

		//console.log( response );
		//return;

		// Store page data.
		CommentPress.infinite.settings.set_post_url( response.url );
		CommentPress.infinite.settings.set_post_title( response.title );

		/*
		// $ 1.9 fails to recognise the response as HTML, so
		// se *must* use parseHTML if it's available.
		if ( $.parseHTML ) {

			// If our $ version is 1.8+, it'll have parseHTML.
			response =  $( $.parseHTML( data ) );

		} else {

			// Get our data as object in the basic way.
			response = $( data );

		}
		*/

		// Get existing menu item before we append.
		existing_menu_item = $( '#main_wrapper .post:last-child' ).prop( 'class' );
		//console.log( 'LOAD: existing_menu_item: ' + existing_menu_item );

		// Find post object.
		new_post_obj = $( '.post', $( response.content ) );
		//console.log( 'NEW post obj: ' + new_post_obj );

		// Add new post to the end.
		$( '#main_wrapper' ).append( new_post_obj.parents( '.page_wrapper' ) );

		// Find post ID property.
		new_post_prop = new_post_obj.prop( 'id' );
		//console.log( 'NEW post ID: ' + new_post_prop );

		// If we get one.
		if ( typeof new_post_prop !== 'undefined' ) {

			// Add calling post ID to our array.
			CommentPress.infinite.settings.add_to_posts( post_id );

			// Get new post ID.
			new_post_id = new_post_prop.split( '-' )[1];

			// Find comments.
			new_comments_obj = $( '.comments_container', $( response.comments ) );
			//console.log( 'NEW comments obj: ' );
			//console.log( new_comments_obj );

			// Do we have a comment form?
			if ( CommentPress.infinite.commentform.get_comments_form() !== 0 ) {

				// Get a copy of the comment form for this post.
				post_comment_form = CommentPress.infinite.commentform.get_comments_form().clone();

				// Change the form ID so we don't get double submissions.
				$( 'form', post_comment_form ).attr( 'id', 'commentform' );

				// Update its comment_post_ID.
				$( '#comment_post_ID', post_comment_form ).val( new_post_id );

				// Replace stored comments data.
				CommentPress.infinite.settings.set_comments( new_comments_obj.append( post_comment_form ) );
				//console.log( 'REPLACE CommentPress.infinite.settings.comments:' );
				//console.log( CommentPress.infinite.settings.get_comments() );

				// Set global depending on status.
				CommentPress.infinite.settings.set_post_comments_open( 'n' );
				if ( response.comment_status == 'open' ) {
					CommentPress.infinite.settings.set_post_comments_open( 'y' );
				}

			}

			// Does it have a menu item ID reference?
			if ( existing_menu_item != 'post' ) {

				// Get item ID.
				existing_menu_item_id = existing_menu_item.split( '-' )[1];

				// Is it a custom menu?
				if ( existing_menu_item.match( 'wpcustom_menuid-' ) ) {

					// Update menu: existing_menu_item_id.
					CommentPress.infinite.menu.update_custom( existing_menu_item_id );

				} else {

					// Update pages menu.
					CommentPress.infinite.menu.update_pages( existing_menu_item_id );

				}

			}

			// Add waypoints to new post.
			me.init_post_waypoint( new_post_id );

			// Broadcast.
			$( document ).trigger( 'commentpress-new-post-loaded' );

			// Enable CommentPress.
			me.refresh();

		}

		// Get new waypoint.
		$.waypoints( 'refresh' );

		// If waypoint.
		if ( mode == 'link' ) {

			// Scroll to the page.
			CommentPress.common.content.scroll_page( new_post_obj );

		}

	}



	/**
	 * Refresh all CommentPress listeners and methods.
	 *
	 * @since 3.8
	 */
	this.refresh = function() {

		// COMMENTPRESS AJAX COMMENTS

		// Enable comment reassignment.
		CommentPress.ajax.comments.reassign_comments();

		// Initialise plugin.
		CommentPress.ajax.comments.initialise();

		// Initialise comment form.
		CommentPress.ajax.comments.initialise_form();

		// BP GROUP SITES

		// Refresh group sites, if present.
		if ( $.is_function_defined( 'bpgsites_init_elements' ) ) {
			bpgsites_init_elements();
		}

	};



	/**
	 * Change content and comments.
	 *
	 * @since 3.8
	 *
	 * @param object context The jQuery context object.
	 */
	this.update_dom = function( context ) {

		// Declare vars.
		var post_obj, post_id, menu_item, new_comments, container, item_above,
			html_to_save, comments;

		// Trace.
		//console.log( 'TOP GOING DOWN (this)' );
		//console.log( context );
		//console.log( 'TOP GOING DOWN ' + $( '.post', context ).prop( 'id' ) );

		// Get post object.
		post_obj = $( '.post', context );

		// ID of current post that's come into view.
		post_id = post_obj.prop( 'id' ).split( '-' )[1];
		//console.log( 'DOWN: post_id: ' + post_id );

		// Get menu item from class.
		menu_item = post_obj.prop( 'class' );
		//console.log( 'DOWN: menu_item: ' + menu_item );

		// Did we get one of our target ones?
		if ( menu_item != 'post' ) {

			// Got one.
			menu_item_id = menu_item.split( '-' )[1];

			// Direction: DOWN.
			//console.log( 'DOWN: post_id ' + post_id + ' menu_item_id ' + menu_item_id );

			// Is it a custom menu?
			if ( menu_item.match( 'wpcustom_menuid-' ) ) {

				// Update menu: menu_item_id.
				CommentPress.infinite.menu.update_custom( menu_item_id );

			} else {

				// Update pages menu.
				CommentPress.infinite.menu.update_pages( menu_item_id );

			}

			// Disable container waypoint to prevent accidental triggering.
			$( '#wrapper' ).waypoint( 'disable' );

			// Remove item above.
			container = $( '#main_wrapper' );
			item_above = context.prev();

			// Trash waypoints.
			item_above.waypoint( 'destroy' );
			context.waypoint( 'destroy' );

			// Set as fixed - remove from document flow.
			container.css( 'position', 'fixed' );

			// Remove item.
			//item_above.css( 'visibility', 'hidden' );
			//item_above.css( 'display', 'none' );
			item_above.remove();

			// Set document title.
			document.title = CommentPress.infinite.settings.get_post_title();

			// Do we have pushstate?
			if ( window.history ) {

				//console.log( 'PUSHSTATE' );
				//console.log( 'page_title: ' + CommentPress.infinite.settings.get_post_title() );

				// Store items as HTML.
				html_to_save = $.trim( context.html() );

				// Create new state object to push to history.
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

			// Scroll to top of page.
			$( document ).scrollTop( 0 );

			// Set back to relative - add back to document flow.
			container.css( 'position', 'relative' );

			// Re-renable container waypoint.
			$( '#wrapper' ).waypoint( 'enable' );



			// Get comments.
			comments = CommentPress.infinite.settings.get_comments();
			//console.log( 'COMMENTS:' );
			//console.log( comments );

			// Do we have a comment form?
			if ( CommentPress.infinite.commentform.get_comments_form() !== 0 ) {

				// Disable the comment form.
				addComment.disableForm();

			}

			// Add new comments to the end.
			$( '#comments_sidebar .comments_container' ).replaceWith( comments );

			// Set global.
			cp_comments_open = CommentPress.infinite.settings.get_post_comments_open();

			// Do we have a comment form?
			if ( CommentPress.infinite.commentform.get_comments_form() !== 0 ) {

				// Re-enable the comment form.
				addComment.enableForm();

			}



			// Enable CommentPress.
			CommentPress.infinite.DOM.refresh();

			// Get new waypoint.
			$.waypoints( 'refresh' );

			// Broadcast.
			$( document ).trigger( 'commentpress-post-changed' );

		}

	};

}; // End CommentPress infinite DOM class



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress infinite page class.
 *
 * @since 3.8
 */
CommentPress.infinite.page = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress infinite page.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.init = function() {

		// Init nonce.
		me.init_nonce();

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.dom_ready = function() {

		// Init previous and next buttons.
		me.state_navigation();

	};



	// Init nonce.
	this.cpajax_nonce = '';

	/**
	 * Init for comment form nonce.
	 *
	 * @since 3.8
	 */
	this.init_nonce = function() {

		// Get initial value from settings object.
		if ( 'undefined' !== typeof CommentpressAjaxInfiniteSettings ) {
			this.cpajax_nonce = CommentpressAjaxInfiniteSettings.nonce;
		}

	};

	/**
	 * Setter for comment form nonce.
	 *
	 * @since 3.8
	 */
	this.set_nonce = function( val ) {
		this.cpajax_nonce = val;
	};

	/**
	 * Getter for comment form nonce.
	 *
	 * @since 3.8
	 */
	this.get_nonce = function() {
		return this.cpajax_nonce;
	};




	/**
	 * Intercept page navigation links.
	 *
	 * @since 3.8
	 */
	this.state_navigation = function() {

		// Previous page button.
		$('#container').on( 'click', '.previous_page', function( event ) {
			alert( 'back' );
			if ( event.preventDefault ) {event.preventDefault();}
			window.history.back();
		});

		// Next page button.
		$('#container').on( 'click', '.next_page', function( event ) {
			alert( 'forward' );
			if ( event.preventDefault ) {event.preventDefault();}
			me.load_next( 'link' );
		});

	}



	/**
	 * Make an AJAX call to retrieve next post/page.
	 *
	 * @since 3.8
	 *
	 * @param string mode Triggered by either 'waypoint' or 'link'.
	 */
	this.load_next = function( mode ) {

		// Declare vars.
		var post_id;

		// Get ID of last post.
		post_id = $( '#main_wrapper .post:last-child' ).prop( 'id' ).split( '-' )[1];
		//console.log( 'wrapper current_post_id: ' + post_id );

		//console.log( 'WRAPPER GOING DOWN' );

		// Kick out if we've already got this one.
		if ( CommentPress.infinite.settings.in_posts( post_id ) ) { return; }

		// Direction: DOWN.
		//console.log( 'down: ' + post_id );

		// Show loading.
		//$( '#loading' ).show();

		// Init AJAX spinner.
		$( '#main_wrapper' ).after(
			'<div class="cp_next_page_loading_wrapper" style="text-align: center">' +
				'<img src="' + cpajax_spinner_url + '" id="cp_next_page_loading" alt="' + cpajax_lang[0] + '" />' +
			'</div>'
		);

		// Call WordPress.
		$.ajax({

			// WordPress AJAX script.
			url: cpajax_ajax_url,

			// Method.
			type: 'POST',

			// Data expected.
			dataType: 'json',

			// Data to send.
			data: {
				action: 'cpajax_load_next_page',
				current_post_id: post_id,
				nonce: me.get_nonce()
			},

			// AJAX callback.
			success: function( data ) {

				// Handle incoming.
				CommentPress.infinite.DOM.handle_ajax_data( data, mode );

				// Remove spinner.
				$( '.cp_next_page_loading_wrapper' ).remove();

			}

		});

	};

}; // End CommentPress infinite page class.



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress infinite menu class.
 *
 * @since 3.8
 */
CommentPress.infinite.menu = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress infinite menu.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.init = function() {

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.dom_ready = function() {

	};



	/**
	 * Update the classes on the menu for a given menu_id.
	 *
	 * @since 3.8
	 *
	 * @param string item_id The numerical ID of the menu item.
	 */
	this.update_custom = function( item_id ) {

		// Update item.
		$( '#toc_list .menu-item' ).removeClass( 'current_page_item' );
		$( '#menu-item-' + item_id ).addClass( 'current_page_item' );

		// Update ancestors.
		$( '#toc_list .menu-item' ).removeClass( 'current_page_ancestor' );
		$( '#menu-item-' + item_id ).parents( 'li' ).addClass( 'current_page_ancestor' );

	}



	/**
	 * Update the classes on the WP pages menu for a given page_id.
	 *
	 * @since 3.8
	 *
	 * @param string item_id The numerical ID of the menu item.
	 */
	this.CommentPress.infinite.menu.update_pages = function( item_id ) {

		// Update item.
		$( '#toc_list .page_item' ).removeClass( 'current_page_item' );
		$( '.page-item-' + item_id ).addClass( 'current_page_item' );

		// Update ancestors.
		$( '#toc_list .page_item' ).removeClass( 'current_page_ancestor' );
		$( '.page-item-' + item_id ).parents( 'li' ).addClass( 'current_page_ancestor' );

	};

}; // End CommentPress infinite menu class.



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress infinite comments class.
 *
 * @since 3.8
 */
CommentPress.infinite.comments = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress infinite page.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.init = function() {

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.dom_ready = function() {

	};



	/**
	 * Store comments. (not used)
	 *
	 * @since 3.8
	 */
	this.store = function() {

		// Declare vars.
		var post_id;

		// Store ID of current post.
		post_id = $( '#wrapper .post' ).prop( 'id' ).split( '-' )[1];
		//CommentPress.infinite.settings.add_to_posts( post_id );

		// Add new comments data to our array.
		CommentPress.infinite.settings_add_to_comments( post_id, $( '#comments_sidebar .comments_container' ) );
		//console.log( 'INIT WITH CommentPress.infinite.settings.comments:' );
		//console.log( CommentPress.infinite.settings.get_comments() );

	};

}; // End CommentPress infinite comments class.




/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress infinite commentform class.
 *
 * @since 3.8
 */
CommentPress.infinite.commentform = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress infinite page.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.init = function() {

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.dom_ready = function() {

		// Store comment form.
		me.store();

	};



	// Init comments form
	this.comments_form = 0;

	/**
	 * Setter for comments form.
	 *
	 * @since 3.8
	 */
	this.set_comments_form = function( val ) {
		this.comments_form = val;
	};

	/**
	 * Getter for comments form.
	 *
	 * @since 3.8
	 */
	this.get_comments_form = function() {
		return this.comments_form;
	};



	/**
	 * Copy and store the comment form.
	 *
	 * @since 3.8
	 */
	this.store = function() {

		// Declare vars.
		var new_form;

		// Disable the comment form.
		addComment.disableForm();

		// Store a copy of the comment form.
		me.set_comments_form( $( '#respond_wrapper' ).clone() );

		// Get new form.
		new_form = me.get_comments_form();

		// Change the form ID so we don't get double submissions.
		$( 'form', new_form ).attr( 'id', 'commentform-disabled' );

		// Remove the extraneous classes.
		if ( $( '#respond_wrapper', new_form ).hasClass( 'cp_force_displayed' ) ) {
			$( '#respond_wrapper', new_form ).removeClass( 'cp_force_displayed' );
		}
		if ( $( '#respond_wrapper', new_form ).hasClass( 'cp_force_closed' ) ) {
			$( '#respond_wrapper', new_form ).removeClass( 'cp_force_closed' );
		}

		// Optionally remove from DOM if comments disabled.
		if ( $( '#respond_wrapper' ).hasClass( 'cp_force_closed' ) ) {
			$( '#respond_wrapper' ).remove();
		}

		// Enable the comment form - not necessary, it seems.
		//addComment.enableForm();

	};

}; // End CommentPress infinite comment form class.



/* -------------------------------------------------------------------------- */



// Do immediate init.
CommentPress.infinite.settings.init();
CommentPress.infinite.DOM.init();
CommentPress.infinite.page.init();
CommentPress.infinite.menu.init();
CommentPress.infinite.commentform.init();
CommentPress.infinite.comments.init();



/* -------------------------------------------------------------------------- */



/**
 * Define what happens when the page is ready.
 *
 * @since 3.8
 */
jQuery(document).ready(function($) {

	// Trigger DOM ready methods.
	CommentPress.infinite.settings.dom_ready();
	CommentPress.infinite.DOM.dom_ready();
	CommentPress.infinite.page.dom_ready();
	CommentPress.infinite.menu.dom_ready();
	CommentPress.infinite.commentform.dom_ready();
	CommentPress.infinite.comments.dom_ready();

}); // End document.ready()



