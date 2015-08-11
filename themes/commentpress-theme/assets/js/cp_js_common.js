/*
================================================================================
CommentPress Default Common Javascript
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

--------------------------------------------------------------------------------
*/



/**
 * Create sub-namespace common to all themes
 */
CommentPress.theme = {};



/**
 * Create settings class
 */
CommentPress.theme.settings = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress theme settings.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

		// init container top max
		me.init_container_top_max();

		// init container top min
		me.init_container_top_min();

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.dom_ready = function() {

		// if we have a cookie
		if ( $.cookie( 'cp_container_top_min' ) ) {

			// skip -> we only set these values once (or when the cookie expires)

		} else {

			// set container top max and save
			me.set_container_top_max( $.px_to_num( $('#container').css( 'top' ) ) );
			me.save_container_top_max();

			// set container top min and save
			me.set_container_top_min( me.get_container_top_max() - CommentPress.theme.original.header.get_height() );
			me.save_container_top_min();

		}

	};



	// init container top max
	this.container_top_max = false;

	/**
	 * Init for container top max
	 */
	this.init_container_top_max = function() {

		// get container original top max
		this.container_top_max = $.cookie( 'cp_container_top_max' );
		if ( 'undefined' === typeof this.container_top_max || this.container_top_max === null ) {
			this.container_top_max = 108;
		}

		// bump up by the height ff the admin bar is shown
		if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {
			this.container_top_max = parseInt( this.container_top_max ) + CommentPress.settings.DOM.get_wp_adminbar_height();
		}

	};

	/**
	 * Setter for container top max
	 */
	this.set_container_top_max = function( val ) {
		this.container_top_max = val;
	};

	/**
	 * Save the container top max value in a cookie
	 */
	this.save_container_top_max = function( val ) {

		// set cookie for further loads
		$.cookie(
			'cp_container_top_max',
			me.get_container_top_max().toString(),
			{ expires: 28, path: cp_cookie_path }
		);

	};

	/**
	 * Getter for container top max
	 */
	this.get_container_top_max = function() {
		return this.container_top_max;
	};



	// init container top min
	this.container_top_min = false;

	/**
	 * Init for container top min
	 */
	this.init_container_top_min = function() {

		// get container original top min
		this.container_top_min = $.cookie( 'cp_container_top_min' );
		if ( 'undefined' === typeof this.container_top_min || this.container_top_min === null ) {
			this.container_top_min = 108;
		}

		// bump up by the height if the admin bar is shown
		if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {
			this.container_top_min = parseInt( this.container_top_min ) + CommentPress.settings.DOM.get_wp_adminbar_height();
		}

	};

	/**
	 * Setter for container top min
	 */
	this.set_container_top_min = function( val ) {
		this.container_top_min = val;
	};

	/**
	 * Save the container top min value in a cookie
	 */
	this.save_container_top_min = function( val ) {

		// set cookie for further loads
		$.cookie(
			'cp_container_top_min',
			me.get_container_top_min().toString(),
			{ expires: 28, path: cp_cookie_path }
		);

	};

	/**
	 * Getter for container top min
	 */
	this.get_container_top_min = function() {
		return this.container_top_min;
	};



	// init toc on top flag
	this.toc_on_top = 'n';

	/**
	 * Setter for Contents tab "on top" flag
	 */
	this.set_toc_on_top = function( val ) {
		this.toc_on_top = val;
	};

	/**
	 * Getter for Contents tab "on top" flag
	 */
	this.get_toc_on_top = function() {
		return this.toc_on_top;
	};



	// init comment border colour
	this.comment_border = '';

	/**
	 * Setter for comment border colour
	 */
	this.set_comment_border = function( val ) {
		this.comment_border = val;
	};

	/**
	 * Getter for comment border colour
	 */
	this.get_comment_border = function() {
		return this.comment_border;
	};

}; // end CommentPress theme settings class



/* -------------------------------------------------------------------------- */



/**
 * Create DOM class
 */
CommentPress.theme.DOM = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress theme DOM.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

		// write styles into <head>
		me.head();

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.dom_ready = function() {

		// setup layout
		me.layout();

	};



	/**
	 * Perform actions before the page is ready
	 *
	 * Writes styles into the document <head> to avoid avoid flash of content
	 *
	 * @return void
	 */
	this.head = function() {

		// define vars
		var styles, cp_container_top, cp_container_width, cp_book_nav_width;

		// init styles
		styles = '';

		// wrap with js test
		if ( document.getElementById ) {

			// open style declaration
			styles += '<style type="text/css" media="screen">';

			// if mobile, don't hide textblock meta
			if ( cp_is_mobile == '0' ) {

				// have we explicitly hidden textblock meta?
				if ( cp_textblock_meta == '0' ) {

					// avoid flash of textblock meta elements
					styles += '#content .textblock span.para_marker, #content .textblock span.commenticonbox { display: none; } ';
					styles += '.content .textblock span.para_marker, .content .textblock span.commenticonbox { display: none; } ';

				}

			}

			// avoid flash of all-comments hidden elements
			styles += 'ul.all_comments_listing div.item_body { display: none; } ';

			// is the admin bar shown?
			if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {

				// move down
				styles += '#header { top: ' + CommentPress.settings.DOM.get_wp_adminbar_height() + 'px; } ';
				styles += '#book_header { top: ' + (CommentPress.settings.DOM.get_wp_adminbar_height() + 32) + 'px; } ';

				// if we have the responsive admin bar in 3.8+
				if ( CommentPress.settings.DOM.get_wp_adminbar_height() == '32' ) {

					// react to responsive admin bar
					styles += '@media screen and ( max-width: 782px ) { ' +
								'#header { top: ' + CommentPress.settings.DOM.get_wp_adminbar_expanded() + 'px; }' +
								'#book_header { top: ' + (CommentPress.settings.DOM.get_wp_adminbar_expanded() + 32) + 'px; }' +
							' } ';

				}

			}

			// are subpages to be shown?
			if ( cp_show_subpages == '0' ) {

				// avoid flash of hidden elements on collapsed items
				styles += '#toc_sidebar .sidebar_contents_wrapper ul li ul { display: none; } ';

			}

			// has the header been minimised?
			if ( CommentPress.theme.original.header.is_minimised() ) {

				// YES, header is minimised

				// do not show header
				styles += '#book_header { display: none; } ';

				// adjust for admin bar
				cp_container_top = CommentPress.theme.settings.get_container_top_min();

				// is the admin bar shown?
				if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {
					cp_container_top = CommentPress.theme.settings.get_container_top_min() - CommentPress.settings.DOM.get_wp_adminbar_height();
				}

				// set tops of divs
				styles += '#container { top: ' + cp_container_top + 'px; } ';
				styles += '#sidebar { top: ' + CommentPress.theme.settings.get_container_top_min() + 'px; } ';

				// is the admin bar shown?
				if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {

					// if we have the responsive admin bar in 3.8+
					if ( CommentPress.settings.DOM.get_wp_adminbar_height() == '32' ) {

						// react to responsive admin bar
						styles += '@media screen and ( max-width: 782px ) { ' +
									'#sidebar { top: ' + (cp_container_top + CommentPress.settings.DOM.get_wp_adminbar_expanded()) + 'px; }' +
								' } ';

					}

				}

			} else {

				// NO, header is NOT minimised

				// adjust for admin bar
				cp_container_top = CommentPress.theme.settings.get_container_top_max();

				// is the admin bar shown?
				if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {
					cp_container_top = CommentPress.theme.settings.get_container_top_max() - CommentPress.settings.DOM.get_wp_adminbar_height();
				}

				styles += '#container { top: ' + cp_container_top + 'px; } ';
				styles += '#sidebar { top: ' + CommentPress.theme.settings.get_container_top_max() + 'px; } ';

				// is the admin bar shown?
				if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {

					// if we have the responsive admin bar in 3.8+
					if ( CommentPress.settings.DOM.get_wp_adminbar_height() == '32' ) {

						// react to responsive admin bar
						styles += '@media screen and ( max-width: 782px ) { ' +
									'#sidebar { top: ' + (cp_container_top + CommentPress.settings.DOM.get_wp_adminbar_expanded()) + 'px; }' +
								' } ';

					}

				}

			}

			// is this the comments sidebar?
			if ( cp_special_page == '0' ) {

				// avoid flash of hidden comments
				styles += '.paragraph_wrapper { display: none; } ';

				// avoid flash of hidden comment form
				styles += '#respond { display: none; } ';

				// has the sidebar window been minimised?
				if ( CommentPress.theme.sidebars.get_minimised() == 'y' ) {

					// set visibility of comments
					styles += '#comments_sidebar .sidebar_contents_wrapper { display: none; } ';

				}

			}

			// on global activity sidebar, avoid flash of hidden comments
			styles += '#activity_sidebar .paragraph_wrapper { display: none; } ';

			/*
			// Note: make into single cookie?
			// has the page been changed?
			if ( $.cookie('cp_page_setup') ) {

				// get value
				cp_page_setup = $.cookie('cp_page_setup');

			}
			*/

			// has the content column changed?
			if ( $.cookie('cp_container_width') ) {

				// get value
				cp_container_width = $.cookie('cp_container_width');

				// set content width
				if ( cp_is_signup_page == '1' ) {
					styles += '#content { width: ' + cp_container_width + '%; } ';
				} else {
					styles += '#page_wrapper { width: ' + cp_container_width + '%; } ';
				}

				// set footer width
				styles += '#footer { width: ' + cp_container_width + '%; } ';

			}

			// has the book nav cookie changed?
			if ( $.cookie('cp_book_nav_width') ) {

				// get book nav width
				cp_book_nav_width = $.cookie('cp_book_nav_width');

				// set its width
				styles += '#book_nav div#cp_book_nav { width: ' + cp_book_nav_width + '%; } ';

			}

			// has the sidebar window changed?
			if ( $.cookie('cp_sidebar_width') ) {

				// set width of sidebar
				styles += '#sidebar { width: ' + $.cookie('cp_sidebar_width') + '%; } ';

			}

			// has the sidebar window changed?
			if ( $.cookie('cp_sidebar_left') ) {

				// set width of sidebar
				styles += '#sidebar { left: ' + $.cookie('cp_sidebar_left') + '%; } ';

			}

			// show tabs when JS enabled
			styles += 'ul#sidebar_tabs, #toc_header.sidebar_header, body.blog_post #activity_header.sidebar_header { display: block; } ';

			// don't set height of sidebar when mobile (but allow tablets)
			if ( cp_is_mobile == '1' && cp_is_tablet == '0' ) {

				// override css
				styles += '.sidebar_contents_wrapper { height: auto; } ';

			}

			// close style declaration
			styles += '</style>';

		}

		// write to page now
		document.write( styles );

	};



	/**
	 * Page load prodecure
	 *
	 * @return void
	 */
	this.layout = function() {

		// define vars
		var target;

		// is this the signup page?
		if ( cp_is_signup_page == '1' ) {

			// target
			target = $('#content');

		} else {

			// target
			target = $('#page_wrapper');

		}

		/**
		 * Sets up the main column, if the id exists
		 *
		 * @param integer i The number of iterations
		 * @return void
		 */
		target.each( function(i) {

			// define vars
			var item, content, sidebar, footer, book_header, book_nav_wrapper, book_nav,
				book_info, original_content_width, original_sidebar_width,
				original_nav_width, original_sidebar_left, gap;

			// assign vars
			item = $(this);
			content = $('#content');
			sidebar = $('#sidebar');
			footer = $('#footer');
			book_header = $('#book_header');
			book_nav_wrapper = $('#book_nav_wrapper');
			book_nav = $('#cp_book_nav');
			book_info = $('#cp_book_info');

			// store original widths
			original_content_width = item.width();
			original_sidebar_width = sidebar.width();

			// calculate gap to sidebar
			gap = sidebar.offset().left - original_content_width;

			// make page wrapper resizable
			item.resizable({

				handles: 'e',
				minWidth: cp_min_page_width,
				alsoResize: '#footer',
				//grid: 1, // no sub-pixel weirdness please

				// on stop... (note: this doesn't fire on the first go in Opera 9!)
				start: function( event, ui ) {

					// store original widths
					original_content_width = item.width();
					original_sidebar_width = sidebar.width();
					original_nav_width = book_nav.width();

					// calculate sidebar left
					original_sidebar_left = sidebar.css( "left" );
					gap = sidebar.offset().left - original_content_width;

				},

				// while resizing...
				resize: function( event, ui ) {

					// define vars
					var my_diff;

					item.css( 'height', 'auto' );
					footer.css( 'height', 'auto' );

					// have the sidebar follow
					sidebar.css( 'left', ( item.width() + gap ) + 'px' );

					// diff
					my_diff = original_content_width - item.width();

					// have the sidebar right remain static
					sidebar.css( 'width', ( original_sidebar_width + my_diff ) + 'px' );

					// have the book nav follow
					book_nav.css( 'width', ( original_nav_width - my_diff ) + 'px' ); // diff in css

				},

				// on stop... (note: this doesn't fire on the first go in Opera 9!)
				stop: function( event, ui ) {

					// define vars
					var ww, width, item_w, book_nav_w, sidebar_w, left, sidebar_l;

					// viewport width
					ww = parseFloat($(window).width() );

					// get element width
					width = item.width();

					// get percent to four decimal places
					item_w = parseFloat( Math.ceil( ( 1000000 * parseFloat( width ) / ww ) ) / 10000 );

					// set element width
					item.css("width" , item_w + '%');

					// set content width to auto so it resizes properly
					if ( cp_is_signup_page == '0' ) {
						content.css( 'width', 'auto' );
					}

					// get element width
					width = book_nav.width();

					// get percent to four decimal places
					book_nav_w = parseFloat( Math.ceil( ( 1000000 * parseFloat( width ) / ww ) ) / 10000 );

					// set element width
					book_nav.css( 'width', book_nav_w + '%' );

					// get element width
					width = sidebar.width();

					// get percent to four decimal places
					sidebar_w = parseFloat( Math.ceil( ( 1000000 * parseFloat( width ) / ww ) ) / 10000 );

					// set element width
					sidebar.css( 'width', sidebar_w + '%' );

					// get element left
					left = sidebar.position().left;

					// get percent to four decimal places
					sidebar_l = parseFloat( Math.ceil( ( 1000000 * parseFloat( left ) / ww ) ) / 10000 );

					// set element left
					sidebar.css( 'left', sidebar_l + '%' );

					// store this width in cookie
					$.cookie(
						'cp_container_width',
						item_w.toString(),
						{ expires: 28, path: cp_cookie_path }
					);

					// store nav width in cookie
					$.cookie(
						'cp_book_nav_width',
						book_nav_w.toString(),
						{ expires: 28, path: cp_cookie_path }
					);

					// store location of sidebar in cookie
					$.cookie(
						'cp_sidebar_left',
						sidebar_l.toString(),
						{ expires: 28, path: cp_cookie_path }
					);

					// store width of sidebar in cookie
					$.cookie(
						'cp_sidebar_width',
						sidebar_w.toString(),
						{ expires: 28, path: cp_cookie_path }
					);

				}

			});

		});

	};

}; // end DOM class



/* -------------------------------------------------------------------------- */



/**
 * Create header class
 */
CommentPress.theme.header = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress theme header.
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
	 * Get header offset
	 *
	 * @return integer offset The target offset in px
	 */
	this.get_offset = function() {

		// define vars
		var offset;

		/*
		// need to decide whether to use border in offset...

		// get offset including border
		offset = 0 - (
			$.px_to_num( $('#container').css('top') ) +
			$.px_to_num( $('#page_wrapper').css( 'borderTopWidth' ) )
		);
		*/

		// get header offset
		offset = 0 - ( $.px_to_num( $('#container').css('top') ) );

		// is the admin bar shown?
		if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {

			// subtract admin bar height
			offset -= CommentPress.settings.DOM.get_wp_adminbar_height();

		}

		// --<
		return offset;

	};

}; // end header class



/* -------------------------------------------------------------------------- */



/**
 * Create navigation class
 */
CommentPress.theme.navigation = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress theme navigation.
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

		// enable "Special Pages" menu behaviour
		me.menu();

	};



	/**
	 * Set up the "Contents" sidebar header
	 *
	 * @return void
	 */
	this.menu = function() {

		/**
		 * Clicking on the "Contents" sidebar header
		 *
		 * @return void
		 */
		$('#sidebar').on( 'click', '#toc_header h2 a', function( event ) {

			// override event
			event.preventDefault();

			// activate it
			CommentPress.theme.sidebars.activate_sidebar( 'toc' );

		});

	};

}; // end navigation class



/* -------------------------------------------------------------------------- */



/**
 * Create content class
 */
CommentPress.theme.content = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress theme content.
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

		// enable "Workflow" tabs
		me.tabs();

	};



	/**
	 * Set up "Workflow" tabs.
	 *
	 * Workflow adds "Literal" and "Original" tabs for use by translators.
	 * Each theme calls the common method with params calculated below.
	 *
	 * @return void
	 */
	this.tabs = function() {

		// define vars
		var content_min_height, content_padding_bottom;

		// store content min-height on load
		content_min_height = $('#content').css( 'min-height' );

		// store content padding-bottom on load
		content_padding_bottom = $('#content').css( 'padding-bottom' );

		// hide workflow content
		$('#literal .post').css( 'display', 'none' );
		$('#original .post').css( 'display', 'none' );

		// setup workflow tabs, if present
		CommentPress.common.content.workflow_tabs( content_min_height, content_padding_bottom );

	};

}; // end content class



/* -------------------------------------------------------------------------- */



/**
 * Create sidebars class
 */
CommentPress.theme.sidebars = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress theme sidebars.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

		// init sidebar minimised flag
		me.init_minimised();

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.dom_ready = function() {

		// don't set height when mobile device (but allow tablets)
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

			// set sidebar height
			me.set_height();

		}

	};



	// init CommentPress sidebar minimised flag
	this.minimised = 'n';

	/**
	 * Init for CommentPress sidebar minimised flag
	 */
	this.init_minimised = function() {

		// get state of sidebar
		this.sidebar_minimised = $.cookie( 'cp_sidebar_minimised' );
		if ( 'undefined' === typeof this.sidebar_minimised || this.sidebar_minimised === null ) {
			this.sidebar_minimised = 'n';
		}

	};

	/**
	 * Setter for CommentPress sidebar minimised flag
	 */
	this.set_minimised = function( val ) {
		this.minimised = val;
	};

	/**
	 * Getter for CommentPress sidebar minimised flag
	 */
	this.get_minimised = function() {
		return this.minimised;
	};

	/**
	 * Getter for CommentPress sidebar minimised flag
	 */
	this.is_minimised = function() {
		if (
			'undefined' === typeof this.minimised ||
			this.minimised === null ||
			this.minimised == 'n'
		) {
			return 'n';
		}
		return this.minimised;
	};

	/**
	 * Toggle for CommentPress sidebar minimised flag
	 */
	this.toggle_minimised = function() {
		if ( this.minimised === 'y' ) {
			this.minimised = 'n';
		} else {
			this.minimised = 'y';
		}
	};



	/**
	 * Bring sidebar to front
	 *
	 * @param string sidebar The sidebar to bring to the front
	 * @return void
	 */
	this.activate_sidebar = function( sidebar ) {

		// define vars
		var ontop, s_top, s_top_border;

		// get "visibility" of the requested sidebar
		ontop = $('#' + sidebar + '_sidebar').css( 'z-index' );

		// is it hidden (ie, does it have a lower z-index)
		if ( ontop == '2001' ) {

			// hide all
			$('.sidebar_container').css('z-index','2001');

			// show it
			$('#' + sidebar + '_sidebar').css('z-index','2010');

			s_top = me.get_top();
			s_top_border = me.get_top_border();

			// set all tabs to min height
			$('.sidebar_header').css( 'height', ( s_top - s_top_border ) + 'px' );

			// set our tab to max height
			$('#' + sidebar + '_header.sidebar_header').css( 'height', s_top + 'px' );

			// set flag
			CommentPress.theme.settings.set_toc_on_top( 'y' );

		}

		// set height if not mobile device (but allow tablets)
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

			// just to make sure...
			me.set_height();

		} else {

			// hide all
			$('.sidebar_container').css( 'visibility', 'hidden' );

			// show it
			$('#' + sidebar + '_sidebar').css( 'visibility', 'visible' );

			/*
			// define vars
			var containers, tallest, this_height;

			// set to height of tallest
			containers = $('.sidebar_contents_wrapper');

			// did we get any?
			if ( containers.length > 0 ) {

				// init
				tallest = 0;

				// find height of each
				containers.each( function(i) {

					// get height
					this_height = $(this).height()

					// is it taller?
					if ( this_height > tallest ) {
						tallest = this_height;
					}

				});

				// set it to that height
				$('.sidebar_contents_wrapper').height( tallest );

				// then make it auto
				// BUT, this won't allow it to expand in future...
				//$('#' + sidebar + '_sidebar .sidebar_contents_wrapper').css('height','auto');

			}
			*/

		}

	};



	/**
	 * Get top of sidebar
	 *
	 * @return integer num The top of the sidebar in pixels
	 */
	this.get_top = function() {
		return $.px_to_num( $('#toc_sidebar').css('top') );
	};



	/**
	 * Get border width of sidebar
	 *
	 * @return integer num The border width of the sidebar in pixels
	 */
	this.get_top_border = function() {
		return $.px_to_num( $('.sidebar_minimiser').css('borderTopWidth') );
	};



	/**
	 * Get visible sidebar minimiser
	 *
	 * @since 3.0
	 *
	 * @return object sidebar_pane The jQuery object for the sidebar pane
	 */
	this.get_sidebar_pane = function() {

		// init
		var name = me.get_sidebar_name();

		// --<
		return $('#' + name + '_sidebar .sidebar_minimiser');

	}



	/**
	 * Get visible sidebar
	 *
	 * @since 3.0
	 *
	 * @return string name The name of the visible sidebar
	 */
	this.get_sidebar_name = function() {

		// init
		var name = 'toc';

		// if toc, must be toc
		//if ( cp_default_sidebar == 'toc' ) { name = 'toc'; }

		// if comments
		if ( cp_default_sidebar == 'comments' ) {
			name = 'comments';
			if ( CommentPress.theme.settings.get_toc_on_top() == 'y' ) {
				name = 'toc';
			}
		}

		// if activity
		if ( cp_default_sidebar == 'activity' ) {
			name = 'activity';
			if ( CommentPress.theme.settings.get_toc_on_top() == 'y' ) {
				name = 'toc';
			}
		}

		// --<
		return name;

	}



	/**
	 * Get height data on element
	 *
	 * @since 3.0
	 *
	 * @param object element The element to adjust
	 * @return int element_adjust The new height of the element in px
	 */
	this.get_element_adjust = function( element ) {

		// declare vars
		var w_bt, w_bb, w_pad_t, w_pad_b, w_mar_t, w_mar_b, element_adjust;

		// get border
		w_bt = $.css_to_num( $.px_to_num( element.css( 'borderTopWidth' ) ) );
		w_bb = $.css_to_num( $.px_to_num( element.css( 'borderBottomWidth' ) ) );

		// get padding
		w_pad_t = $.css_to_num( $.px_to_num( element.css( 'padding-top' ) ) );
		w_pad_b = $.css_to_num( $.px_to_num( element.css( 'padding-bottom' ) ) );

		// get margin
		w_mar_t = $.css_to_num( $.px_to_num( element.css( 'margin-top' ) ) );
		w_mar_b = $.css_to_num( $.px_to_num( element.css( 'margin-bottom' ) ) );

		// add 'em up
		element_adjust = w_bt + w_bb + w_pad_t + w_pad_b + w_mar_t + w_mar_b;

		// --<
		return element_adjust;

	}



	/**
	 * Set height of sidebar minimiser (scrolling element) so that the column fills the viewport
	 *
	 * @since 3.0
	 *
	 * @todo In jQuery 1.9, we get a 143px error, related to sidebar.position().top
	 *
	 * @return int to_bottom The height of the sidebar in px
	 */
	this.set_height = function() {

		var sidebar, sidebar_inner, sidebar_container, header, minimiser,
			s_top, sidebar_inside_h, sidebar_inner_inside_h, sidebar_diff,
			sc_top, sc_inside_h, sc_diff,
			header_diff, minimiser_diff,
			bottom_margin,
			viewport_height, viewport_scrolltop, viewport,
			to_bottom;

		sidebar = $('#sidebar');
		sidebar_inner = $('#sidebar_inner');
		sidebar_container = $('#toc_sidebar');
		header = $('#' + CommentPress.theme.sidebars.get_sidebar_name() + '_sidebar .sidebar_header');
		minimiser = me.get_sidebar_pane();

		// get data on sidebar element
		s_top = sidebar.offset().top;
		sidebar_inside_h = me.get_element_adjust( sidebar );
		sidebar_inner_inside_h = me.get_element_adjust( sidebar_inner );
		sidebar_diff = s_top + sidebar_inside_h + sidebar_inner_inside_h;

		// get data on sidebar_container element
		sc_top = sidebar_container.position().top;
		sc_inside_h = me.get_element_adjust( sidebar_container );
		sc_diff = sc_top + sc_inside_h;

		// init header diff
		header_diff = 0;
		// if internal header element is displayed
		if ( header.css('display') != 'none' ) {
			// get data on header element
			header_diff = header.height() + me.get_element_adjust( header );
		}

		// get data on minimiser element
		minimiser_diff = me.get_element_adjust( minimiser );

		// get bottom margin of main column so sidebar lines up
		// NOTE: this is NOT why they don't line up - it just so happens that the values match
		// It seems the clearfix class adds the margin. Sigh.
		if ( cp_is_signup_page == '1' ) {
			bottom_margin = $.css_to_num( $.px_to_num( $('#content').css( 'margin-bottom' ) ) );
		} else {
			bottom_margin = $.css_to_num( $.px_to_num( $('#page_wrapper').css( 'margin-bottom' ) ) );
		}

		// get viewport data
		viewport_height = $(window).height();
		viewport_scrolltop = $(window).scrollTop();
		viewport = viewport_height + viewport_scrolltop;

		// calculate the necessary height to reach the bottom of the viewport
		to_bottom = viewport - ( sidebar_diff + sc_diff + header_diff + minimiser_diff + bottom_margin );

		// now set it
		$('#sidebar div.sidebar_contents_wrapper').css( 'height', to_bottom + 'px' );

		// --<
		return to_bottom;

	}

}; // end sidebars class



/* -------------------------------------------------------------------------- */



/**
 * Create viewport class
 */
CommentPress.theme.viewport = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress theme viewport.
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
	 * Scroll page to top
	 *
	 * @param object target The object to scroll to
	 * @param integer speed The duration of the scroll
	 * @return void
	 */
	this.scroll_to_top = function( target, speed ) {

		// bail if we didn't get a valid target
		if ( 'undefined' === typeof target ) { return; }

		// only scroll if not mobile (but allow tablets)
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

			// scroll
			$(window).stop(true).scrollTo( target, speed );

		}

	};



	/**
	 * Page load prodecure
	 *
	 * @return void
	 */
	this.on_load_scroll_to_anchor = function() {

		// define vars
		var text_sig, url, comment_id, para_wrapper_array, item, para_id, para_num,
			post_id, textblock, anchor_id, anchor, found;

		// init
		text_sig = '';
		found = false;

		// if there is an anchor in the URL (only on non-special pages)
		url = document.location.toString();

		// do we have a comment permalink?
		if ( url.match( '#comment-' ) ) {

			// get comment ID
			tmp = url.split('#comment-');

			// sanity check
			comment_id = 0;
			if ( tmp.length == 2 ) {
				comment_id = parseInt( tmp[1] );
			}

			// did we get one?
			if ( comment_id !== 0 ) {
				me.on_load_scroll_to_comment( comment_id );
			}

			// set location to page/post permalink
			CommentPress.common.DOM.location_reset();

			// --<
			return;

		} else {

			/**
			 * Loop through the paragraph permalinks checking for a match
			 *
			 * @return void
			 */
			$('span.para_marker > a').each( function(i) {

				// define vars
				var text_sig, para_id, para_num, post_id, textblock;

				// get text signature
				text_sig = $(this).prop( 'id' );

				// do we have a paragraph or comment block permalink?
				if ( url.match( '#' + text_sig ) || url.match( '#para_heading-' + text_sig ) ) {

					// align content
					me.align_content( text_sig, 'para_heading' );

					// set location to page/post permalink
					CommentPress.common.DOM.location_reset();

					// set flag
					found = true;

				}

			});

		}

		// check flag and bail if already found
		if ( found === true ) { return; }

		// do we have a link to the comment form?
		if ( url.match( '#respond' ) ) {

			// is this a "Reply to [...]" link
			if ( url.match( 'replytocom' ) ) {

				// get parent from form
				comment_parent = parseInt( $('#comment_parent').val() );

				// also same as load procedure
				me.on_load_scroll_to_comment( comment_parent );

			} else {

				// is this a "Leave Comment on [...]" link
				if ( url.match( 'replytopara' ) ) {

					// get text sig from form
					text_sig = $('#text_signature').val();

					// align content
					me.align_content( text_sig, 'commentform' );

				} else {

					// same as clicking on the "whole page" heading
					$('h3#para_heading- a.comment_block_permalink').click();

				}

			}

			// set location to page/post permalink
			CommentPress.common.DOM.location_reset();

			// --<
			return;

		}

		// any other anchors in the .post?
		if ( url.match( '#' ) ) {

			// get anchor
			anchor_id = url.split('#')[1];

			// bail if it's WP FEE's custom anchor
			if ( anchor_id == 'edit=true' ) { return; }
			if ( anchor_id == 'fee-edit-link' ) { return; }

			// locate in DOM
			anchor = $( '#' + anchor_id );

			// did we get one?
			if ( anchor.length ) {

				// add class
				anchor.addClass('selected_para');

				// scroll page
				CommentPress.common.content.scroll_page( anchor );

			}

			// set location to page/post permalink
			CommentPress.common.DOM.location_reset();

			// --<
			return;

		}

	};



	/**
	 * Scroll to comment on page load
	 *
	 * @param int comment_id The ID of the comment to scroll to
	 * @return void
	 */
	this.on_load_scroll_to_comment = function( comment_id ) {

		// define vars
		var text_sig, para_wrapper_array, item, para_id, para_num,
			post_id, textblock;

		// activate comments sidebar
		CommentPress.theme.sidebars.activate_sidebar( 'comments' );

		// open the matching block

		// get array of parent paragraph_wrapper divs
		para_wrapper_array = $('#comment-' + comment_id)
									.parents('div.paragraph_wrapper')
									.map( function () {
										return this;
									});

		// did we get one?
		if ( para_wrapper_array.length > 0 ) {

			// get the item
			item = $(para_wrapper_array[0]);

			// are comments open?
			if ( cp_comments_open == 'y' ) {

				// move form to para
				text_sig = item.prop('id').split('-')[1];
				para_id = $('#para_wrapper-'+text_sig+' .reply_to_para').prop('id');
				para_num = para_id.split('-')[1];
				post_id = $('#comment_post_ID').prop('value');

				// seems like TinyMCE isn't yet working and that moving the form
				// prevents it from loading properly
				if ( cp_tinymce == '1' ) {

					// if we have link text, then a comment reply is allowed...
					if ( $( '#comment-' + comment_id + ' > .reply' ).text() !== '' ) {

						// temporarily override global so that TinyMCE is not
						// meddled with in any way...
						cp_tinymce = '0';

						// move the form
						addComment.moveForm(
							'comment-' + comment_id,
							comment_id,
							'respond',
							post_id,
							text_sig
						);

						// restore global
						cp_tinymce = '1';

					}

				} else {

					// move the form
					addComment.moveForm(
						'comment-' + comment_id,
						comment_id,
						'respond',
						post_id,
						text_sig
					);

				}

			}

			// show block
			item.show();

			// scroll comments
			CommentPress.common.comments.scroll_comments( $('#comment-' + comment_id), 1, 'flash' );

			// if not the whole page...
			if( text_sig !== '' ) {

				// get text block
				textblock = $('#textblock-' + text_sig);

				// highlight this paragraph
				$.highlight_para( textblock );

				// scroll page
				CommentPress.common.content.scroll_page( textblock );

			} else {

				// only scroll if page is not highlighted
				if ( !CommentPress.settings.page.get_highlight() ) {

					// scroll to top
					CommentPress.theme.viewport.scroll_to_top( 0, cp_scroll_speed );

				}

				// toggle page highlight flag
				CommentPress.settings.page.toggle_highlight();

			}

		}

	};



	/**
	 * Does what a click on a comment icon should do
	 *
	 * @param string text_sig The text signature to scroll to
	 * @param string scroll_target Where to scroll to ('none', 'para_heading', 'commentform' or 'auto' if empty)
	 * @return void
	 */
	this.align_content = function( text_sig, scroll_target ) {

		// bail if scrool target is 'none'
		if ( scroll_target == 'none' ) { return; }

		// show comments sidebar
		CommentPress.theme.sidebars.activate_sidebar( 'comments' );

		// define vars
		var para_wrapper, comment_list, respond, top_level, opening, visible,
			textblock, post_id, para_id, para_num;

		// get para wrapper
		para_wrapper = $('#para_heading-' + text_sig).next('div.paragraph_wrapper');

		// bail if we don't have the target element
		if ( para_wrapper.length == 0 ) {
			return;
		}

		// get comment list
		comment_list = $( '#para_wrapper-' + text_sig + ' .commentlist' );

		// get respond
		respond = para_wrapper.find('#respond');

		// is it a direct child of para wrapper?
		top_level = addComment.getLevel();

		// init
		opening = false;

		// get visibility
		visible = para_wrapper.css('display');

		// override
		if ( visible == 'none' ) { opening = true; }

		// clear other highlights
		$.unhighlight_para();

		// did we get a text_sig?
		if ( text_sig !== '' ) {

			// get text block
			textblock = $('#textblock-' + text_sig);

			// if encouraging reading and closing
			if ( cp_promote_reading == '1' && !opening ) {

				// skip the highlight

			} else {

				// highlight this paragraph
				$.highlight_para( textblock );

				// scroll page
				CommentPress.common.content.scroll_page( textblock );

			}

		}

		// if encouraging commenting
		if ( cp_promote_reading == '0' ) {

			// are comments open?
			if ( cp_comments_open == 'y' ) {

				// get comment post ID
				post_id = $('#comment_post_ID').prop('value');
				para_id = $('#para_wrapper-'+text_sig+' .reply_to_para').prop('id');
				para_num = para_id.split('-')[1];

			}

			// Choices, choices...

			// if it doesn't have the commentform
			if ( !respond[0] ) {

				// are comments open?
				if ( cp_comments_open == 'y' ) {
					addComment.moveFormToPara( para_num, text_sig, post_id );
				}

			}

			// if it has the commentform but is not top level
			if ( respond[0] && !top_level ) {

				// are comments open?
				if ( cp_comments_open == 'y' ) {

					// move comment form
					addComment.moveFormToPara( para_num, text_sig, post_id );

					// if scroll_target is for para_headings
					if ( scroll_target == 'para_heading' ) {

						// scroll comments to header
						CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

					} else {

						// scroll comments to comment form
						CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

					}

				} else {

					// scroll comments to header
					CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

				}

				return;

			}

			// if it doesn't have the commentform but has a comment
			if ( !respond[0] && comment_list[0] && !opening ) {

				// are comments open?
				if ( cp_comments_open == 'y' ) {

					// if scroll_target is for para_headings
					if ( scroll_target == 'para_heading' ) {

						// scroll comments to header
						CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

					} else {

						// scroll comments to comment form
						CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

					}

				} else {

					// scroll comments to header
					CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

				}

				return;

			}

			// if closing with comment list
			if ( !opening && comment_list[0] ) {

				// are comments open?
				if ( cp_comments_open == 'y' ) {

					// if scroll_target is for para_headings
					if ( scroll_target == 'para_heading' ) {

						// scroll comments to header
						CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

					} else {

						// scroll comments to comment form
						CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

					}

				} else {

					// scroll comments to header
					CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

				}

				return;

			}

			// if commentform but no comments and closing
			if ( respond[0] && !comment_list[0] && !opening ) {

				// are comments open?
				if ( cp_comments_open == 'y' ) {

					// if scroll_target is for para_headings
					if ( scroll_target == 'para_heading' ) {

						// scroll comments to header
						CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

					} else {

						// scroll comments to comment form
						CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

					}

				} else {

					// scroll comments to header
					CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

				}

				// --<
				return;

			}

			// if closing with no comment list
			if ( !opening && !comment_list[0] ) {

				para_wrapper.css( 'display', 'none' );
				opening = true;

			}

		}

		// toggle next item_body
		para_wrapper.slideToggle( 'slow', function () {

			// animation finished

			// are we encouraging reading?
			if ( cp_promote_reading == '1' && opening ) {

				// scroll comments
				CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

			} else {

				// only if opening
				if ( opening ) {

					// are comments open?
					if ( cp_comments_open == 'y' ) {

						// if scroll_target is for para_headings
						if ( scroll_target == 'para_heading' ) {

							// scroll comments to header
							CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

						} else {

							// scroll comments to comment form
							CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

						}

					} else {

						// scroll comments to comment form
						CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

					}

				}

			}

		});

	};

}; // end viewport class



/* -------------------------------------------------------------------------- */



/**
 * Create sub-namespace for default (original) theme
 */
CommentPress.theme.original = {};



/**
 * Create header class
 */
CommentPress.theme.original.header = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict(),
		header_animating = false;



	/**
	 * Initialise CommentPress original header.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

		// init minimised flag
		me.init_minimised();

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.dom_ready = function() {

		// init header height
		me.init_height();

		// enable minimiser button
		me.minimiser();

	};



	// init CommentPress header height
	this.header_height = 0;

	/**
	 * Init for CommentPress header height
	 */
	this.init_height = function() {

		// get global book_header height
		this.header_height = $('#book_header').height();

	};

	/**
	 * Setter for CommentPress header height
	 */
	this.set_height = function( val ) {
		this.header_height = val;
	};

	/**
	 * Getter for CommentPress header height
	 */
	this.get_height = function() {
		return this.header_height;
	};



	// init CommentPress header minimised flag
	this.minimised = 'n';

	/**
	 * Init for CommentPress header minimised flag
	 */
	this.init_minimised = function() {

		// get state of header
		this.minimised = $.cookie( 'cp_header_minimised' );
		if ( 'undefined' === typeof this.minimised || this.minimised === null ) {
			this.minimised = 'n';
		}

	};

	/**
	 * Setter for CommentPress header minimised flag
	 */
	this.set_minimised = function( val ) {
		this.minimised = val;
	};

	/**
	 * Getter for CommentPress header minimised flag
	 */
	this.get_minimised = function() {
		return this.minimised;
	};

	/**
	 * Getter for CommentPress header minimised flag
	 *
	 * @return bool Whether or not the header is minimised
	 */
	this.is_minimised = function() {
		if (
			 'undefined' === typeof this.minimised ||
			this.minimised === null ||
			this.minimised == 'n'
		) {
			return false;
		}
		return true;
	};

	/**
	 * Toggle for CommentPress header minimised flag
	 */
	this.toggle_minimised = function() {
		if ( this.minimised === 'y' ) {
			this.minimised = 'n';
		} else {
			this.minimised = 'y';
		}
	};



	/**
	 * Set up Header minimise button
	 *
	 * @return false
	 */
	this.minimiser = function() {

		/**
		 * Clicking on the Header Minimiser button
		 *
		 * @return false
		 */
		$('#header').on( 'click', '#btn_header_min', function( event ) {

			// override event
			event.preventDefault();

			// call function
			me.toggle();

		});

	};



	/**
	 * Set up header minimiser button
	 *
	 * @return void
	 */
	this.toggle = function() {

		// if animating, kick out
		if ( header_animating === true ) { return false; }
		header_animating = true;

		// toggle
		if ( me.is_minimised() ) {
			me.open();
		} else {
			me.close();
		}

		// toggle
		me.toggle_minimised();

		// store flag in cookie
		$.cookie(
			'cp_header_minimised',
			me.get_minimised(),
			{ expires: 28, path: cp_cookie_path }
		);

	};



	/**
	 * Open header
	 *
	 * @return void
	 */
	this.open = function() {

		// -------------------------------------------------------------------------
		//console.log( 'open' );
		// -------------------------------------------------------------------------

		// define vars
		var book_nav_h, target_sidebar, target_sidebar_pane, book_header, container,
			cp_container_top, cp_sidebar_height;

		// get nav height
		book_nav_h = $('#book_nav').height();

		target_sidebar = $('#sidebar');
		target_sidebar_pane = CommentPress.theme.sidebars.get_sidebar_pane();
		book_header = $('#book_header');
		container = $('#container');

		// set max height
		cp_container_top = CommentPress.theme.settings.get_container_top_max();

		// is the admin bar shown?
		if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {

			// deduct height of admin bar
			cp_container_top = CommentPress.theme.settings.get_container_top_max() - CommentPress.settings.DOM.get_wp_adminbar_height();

		}

		// animate container
		container.animate({

			top: cp_container_top + 'px',
			duration: 'fast'

			}, function () {

				// slide book header
				book_header.fadeIn('fast', function() {

					// when done
					header_animating = false;

				});

			}

		);

		// is the sidebar minimised?
		if ( CommentPress.theme.sidebars.get_minimised() == 'n' ) {

			// get sidebar height
			cp_sidebar_height = target_sidebar.height() - me.get_height();

			// animate main wrapper
			target_sidebar.animate({

				top: CommentPress.theme.settings.get_container_top_max() + 'px',
				height: cp_sidebar_height + 'px',
				duration: 'fast'

				}, function() {

					// when done
					target_sidebar.css( 'height','auto' );

				}

			);

			// animate inner
			target_sidebar_pane.animate({

				height: ( target_sidebar_pane.height() - me.get_height() ) + 'px',
				duration: 'fast'

				}, function() {

					// don't set height when mobile device (but allow tablets - needs testing)
					if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

						// fit column
						CommentPress.theme.sidebars.set_height();

					}

					// when done
					header_animating = false;

				}

			);

		} else {

			// animate sidebar
			target_sidebar.animate({

				top: CommentPress.theme.settings.get_container_top_max() + 'px',
				duration: 'fast'

				}, function() {

					// when done
					header_animating = false;

					// don't set height when mobile device (but allow tablets)
					if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

						// fit column
						CommentPress.theme.sidebars.set_height();

					}

				}

			);

		}

	};



	/**
	 * Close header
	 *
	 * @return void
	 */
	this.close = function() {

		// -------------------------------------------------------------------------
		//console.log( 'close' );
		// -------------------------------------------------------------------------

		// define vars
		var book_nav_h, target_sidebar, target_sidebar_pane, book_header, container;
		var cp_container_top, cp_sidebar_height;

		// get nav height
		book_nav_h = $('#book_nav').height();

		target_sidebar = $('#sidebar');
		target_sidebar_pane = CommentPress.theme.sidebars.get_sidebar_pane();
		book_header = $('#book_header');
		container = $('#container');

		// hide header
		book_header.hide();

		// set min height
		cp_container_top = CommentPress.theme.settings.get_container_top_min();

		// is the admin bar shown?
		if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {

			// deduct height of admin bar
			cp_container_top = CommentPress.theme.settings.get_container_top_min() - CommentPress.settings.DOM.get_wp_adminbar_height();

		}

		container.animate({

			top: cp_container_top + 'px',
			duration: 'fast'

		});

		// is the sidebar minimised?
		if ( CommentPress.theme.sidebars.get_minimised() == 'n' ) {

			// get sidebar height
			cp_sidebar_height = target_sidebar.height() + me.get_height();

			//$('#container').css('top','40px');
			target_sidebar.animate({

				top: CommentPress.theme.settings.get_container_top_min() + 'px',
				height: cp_sidebar_height + 'px',
				duration: 'fast'

				}, function() {

					// when done
					target_sidebar.css( 'height','auto' );

				}

			);

			//$('#container').css('top','40px');
			target_sidebar_pane.animate({

				height: ( target_sidebar_pane.height() + me.get_height() ) + 'px',
				duration: 'fast'

				}, function() {

					// don't set height when mobile device (but allow tablets)
					if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

						// fit column
						CommentPress.theme.sidebars.set_height();

					}

					// when done
					header_animating = false;

				}

			);

		} else {

			// animate just sidebar
			target_sidebar.animate({

				top: CommentPress.theme.settings.get_container_top_min() + 'px',
				duration: 'fast'

				}, function() {

					// when done
					header_animating = false;

					// don't set height when mobile device (but allow tablets)
					if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

						// fit column
						CommentPress.theme.sidebars.set_height();

					}

				}

			);

		}

	};



}; // end original.header class



/* -------------------------------------------------------------------------- */



// do immediate init
CommentPress.theme.settings.init();

// the default theme needs its header inited before DOM
CommentPress.theme.original.header.init();

CommentPress.theme.DOM.init();
CommentPress.theme.header.init();
CommentPress.theme.navigation.init();
CommentPress.theme.content.init();
CommentPress.theme.sidebars.init();
CommentPress.theme.viewport.init();



/* -------------------------------------------------------------------------- */



/**
 * Define what happens when the page is ready
 *
 * @return void
 */
jQuery(document).ready( function($) {



	// the default theme implements custom header actions
	CommentPress.theme.original.header.dom_ready();

	// trigger DOM ready methods
	CommentPress.theme.settings.dom_ready();
	CommentPress.theme.DOM.dom_ready();
	CommentPress.theme.header.dom_ready();
	CommentPress.theme.navigation.dom_ready();
	CommentPress.theme.content.dom_ready();
	CommentPress.theme.sidebars.dom_ready();
	CommentPress.theme.viewport.dom_ready();



	/**
	 * Hook into CommentPress Form comment highlight trigger
	 *
	 * @param int parent_id The parent comment ID
	 * @return void
	 */
	$( document ).on( 'commentpress-comment-highlight', function( event, parent_id ) {

		// declare vars
		var comment_border;

		// set highlight colour
		jQuery('#li-comment-' + parent_id + ' > .comment-wrapper').css( 'background-color', '#CBFFBD' );

		// get existing colour
		comment_border = jQuery('#comment-' + parent_id + ' > .comment-content').css( 'border-bottom' );

		// save it
		CommentPress.theme.settings.set_comment_border( comment_border );

		// set highlight
		jQuery('#comment-' + parent_id + ' > .comment-content').css( 'border-bottom', '1px dashed #b8b8b8' );


	});

	/**
	 * Hook into CommentPress Form comment unhighlight trigger
	 *
	 * @param int parent_id The parent comment ID
	 * @return void
	 */
	$( document ).on( 'commentpress-comment-unhighlight', function( event, parent_id ) {

		// declare vars
		var comment_border;

		// get existing colour
		comment_border = CommentPress.theme.settings.get_comment_border();

		// reset highlight colours
		jQuery('#li-comment-' + parent_id + ' > .comment-wrapper').css( 'background-color', '#fff' );
		jQuery('#comment-' + parent_id + ' > .comment-content').css( 'border-bottom', comment_border );

	});

	/**
	 * Hook into CommentPress Form clear all comment highlights trigger
	 *
	 * @return void
	 */
	$( document ).on( 'commentpress-comment-highlights-clear', function( event ) {

		// declare vars
		var comment_border;

		// get existing colour
		comment_border = CommentPress.theme.settings.get_comment_border();

		// reset highlight colours
		jQuery('.comment-wrapper').css( 'background-color', '#fff');
		jQuery('.comment-content').css( 'border-bottom', comment_border );

	});



	/**
	 * When a comment block permalink comes into focus
	 *
	 * @note in development for keyboard accessibility
	 */
	/*
	if ( $().jquery >= 1.4 ) {
		$('a.comment_block_permalink').focusin( function(e) {

			// test -> needs refinement
			//$(this).click();

		});
	}
	*/

	/**
	 * When a comment block permalink loses focus
	 *
	 * @note: in development for keyboard accessibility
	 */
	/*
	$('a.comment_block_permalink').blur( function(e) {

		// test -> needs refinement
		//$(this).click();

	});
	*/



	// scroll the page on load
	if ( cp_special_page == '1' ) {
		CommentPress.common.content.on_load_scroll_to_comment();
	} else {
		CommentPress.theme.viewport.on_load_scroll_to_anchor();
	}



	// broadcast that we're done
	$( document ).trigger( 'commentpress-document-ready' );

});



/**
 * Define what happens when the page is unloaded
 *
 * @return void
 */
/*
jQuery(window).unload( function() {

	// debug
	//console.log('Bye now!');

});
*/
