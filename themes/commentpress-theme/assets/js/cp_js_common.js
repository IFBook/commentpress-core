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
 * Create sub-namespace common to all themes.
 *
 * @since 3.8
 */
CommentPress.theme = {};



/**
 * Create settings class.
 *
 * @since 3.8
 */
CommentPress.theme.settings = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress theme settings.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.init = function() {

		// Init container top max.
		me.init_container_top_max();

		// Init container top min.
		me.init_container_top_min();

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.dom_ready = function() {

		// If we have a cookie.
		if ( $.cookie( 'cp_container_top_min' ) ) {

			// Skip -> we only set these values once - or when the cookie expires.

		} else {

			// Set container top max and save.
			me.set_container_top_max( $.px_to_num( $('#container').css( 'top' ) ) );
			me.save_container_top_max();

			// Set container top min and save.
			me.set_container_top_min( me.get_container_top_max() - CommentPress.theme.original.header.get_height() );
			me.save_container_top_min();

		}

	};



	// Init container top max.
	this.container_top_max = false;

	/**
	 * Init for container top max.
	 *
	 * @since 3.8
	 */
	this.init_container_top_max = function() {

		// Get container original top max.
		me.container_top_max = $.cookie( 'cp_container_top_max' );
		if ( 'undefined' === typeof me.container_top_max || me.container_top_max === null ) {
			me.container_top_max = 108;
		}

		// Bump up by the height ff the admin bar is shown.
		if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {
			me.container_top_max = parseInt( me.container_top_max ) + CommentPress.settings.DOM.get_wp_adminbar_height();
		}

	};

	/**
	 * Setter for container top max.
	 *
	 * @since 3.8
	 */
	this.set_container_top_max = function( val ) {
		me.container_top_max = val;
	};

	/**
	 * Save the container top max value in a cookie.
	 *
	 * @since 3.8
	 */
	this.save_container_top_max = function( val ) {

		// Set cookie for further loads.
		$.cookie(
			'cp_container_top_max',
			me.get_container_top_max().toString(),
			{ expires: 28, path: cp_cookie_path }
		);

	};

	/**
	 * Getter for container top max.
	 *
	 * @since 3.8
	 */
	this.get_container_top_max = function() {
		return me.container_top_max;
	};



	// Init container top min
	this.container_top_min = false;

	/**
	 * Init for container top min.
	 *
	 * @since 3.8
	 */
	this.init_container_top_min = function() {

		// Get container original top min.
		me.container_top_min = $.cookie( 'cp_container_top_min' );
		if ( 'undefined' === typeof me.container_top_min || me.container_top_min === null ) {
			me.container_top_min = 108;
		}

		// Bump up by the height if the admin bar is shown.
		if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {
			me.container_top_min = parseInt( me.container_top_min ) + CommentPress.settings.DOM.get_wp_adminbar_height();
		}

	};

	/**
	 * Setter for container top min.
	 *
	 * @since 3.8
	 */
	this.set_container_top_min = function( val ) {
		me.container_top_min = val;
	};

	/**
	 * Save the container top min value in a cookie.
	 *
	 * @since 3.8
	 */
	this.save_container_top_min = function( val ) {

		// Set cookie for further loads.
		$.cookie(
			'cp_container_top_min',
			me.get_container_top_min().toString(),
			{ expires: 28, path: cp_cookie_path }
		);

	};

	/**
	 * Getter for container top min.
	 *
	 * @since 3.8
	 */
	this.get_container_top_min = function() {
		return me.container_top_min;
	};



	// Init toc on top flag
	this.toc_on_top = 'n';

	/**
	 * Setter for Contents tab "on top" flag.
	 *
	 * @since 3.8
	 */
	this.set_toc_on_top = function( val ) {
		me.toc_on_top = val;
	};

	/**
	 * Getter for Contents tab "on top" flag.
	 *
	 * @since 3.8
	 */
	this.get_toc_on_top = function() {
		return me.toc_on_top;
	};



	// Init comment border colour
	this.comment_border = '';

	/**
	 * Setter for comment border colour.
	 *
	 * @since 3.8
	 */
	this.set_comment_border = function( val ) {
		me.comment_border = val;
	};

	/**
	 * Getter for comment border colour.
	 *
	 * @since 3.8
	 */
	this.get_comment_border = function() {
		return me.comment_border;
	};

}; // End CommentPress theme settings class.



/* -------------------------------------------------------------------------- */



/**
 * Create DOM class.
 *
 * @since 3.8
 */
CommentPress.theme.DOM = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress theme DOM.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.init = function() {

		// Write styles into <head>.
		me.head();

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.dom_ready = function() {

		// Setup layout.
		me.layout();

	};



	/**
	 * Perform actions before the page is ready.
	 *
	 * Writes styles into the document <head> to avoid avoid flash of content.
	 *
	 * @since 3.8
	 */
	this.head = function() {

		// Define vars.
		var styles, cp_container_top, cp_container_width, cp_book_nav_width;

		// Init styles.
		styles = '';

		// Wrap with js test.
		if ( document.getElementById ) {

			// Open style declaration.
			styles += '<style type="text/css" media="screen">';

			// If mobile, don't hide textblock meta.
			if ( cp_is_mobile == '0' ) {

				// Have we explicitly hidden textblock meta?
				if ( cp_textblock_meta == '0' ) {

					// Avoid flash of textblock meta elements.
					styles += '#content .textblock span.para_marker, #content .textblock span.commenticonbox { display: none; } ';
					styles += '.content .textblock span.para_marker, .content .textblock span.commenticonbox { display: none; } ';

				}

			}

			// Avoid flash of all-comments hidden elements.
			styles += 'ul.all_comments_listing div.item_body { display: none; } ';

			// Is the admin bar shown?
			if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {

				// Move down.
				styles += '#header { top: ' + CommentPress.settings.DOM.get_wp_adminbar_height() + 'px; } ';
				styles += '#book_header { top: ' + (CommentPress.settings.DOM.get_wp_adminbar_height() + 32) + 'px; } ';

				// If we have the responsive admin bar in 3.8+
				if ( CommentPress.settings.DOM.get_wp_adminbar_height() == '32' ) {

					// React to responsive admin bar.
					styles += '@media screen and ( max-width: 782px ) { ' +
								'#header { top: ' + CommentPress.settings.DOM.get_wp_adminbar_expanded() + 'px; }' +
								'#book_header { top: ' + (CommentPress.settings.DOM.get_wp_adminbar_expanded() + 32) + 'px; }' +
							' } ';

				}

			}

			// Are subpages to be shown?
			if ( cp_show_subpages == '0' ) {

				// Avoid flash of hidden elements on collapsed items.
				styles += '#toc_sidebar .sidebar_contents_wrapper ul li ul { display: none; } ';

			}

			// Has the header been minimised?
			if ( CommentPress.theme.original.header.is_minimised() ) {

				// YES, header is minimised.

				// Do not show header.
				styles += '#book_header { display: none; } ';

				// Adjust for admin bar.
				cp_container_top = CommentPress.theme.settings.get_container_top_min();

				// Is the admin bar shown?
				if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {
					cp_container_top = CommentPress.theme.settings.get_container_top_min() - CommentPress.settings.DOM.get_wp_adminbar_height();
				}

				// Set tops of divs.
				styles += '#container { top: ' + cp_container_top + 'px; } ';
				styles += '#sidebar { top: ' + CommentPress.theme.settings.get_container_top_min() + 'px; } ';

				// Is the admin bar shown?
				if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {

					// If we have the responsive admin bar in 3.8+
					if ( CommentPress.settings.DOM.get_wp_adminbar_height() == '32' ) {

						// React to responsive admin bar.
						styles += '@media screen and ( max-width: 782px ) { ' +
									'#sidebar { top: ' + (cp_container_top + CommentPress.settings.DOM.get_wp_adminbar_expanded()) + 'px; }' +
								' } ';

					}

				}

			} else {

				// NO, header is NOT minimised.

				// Adjust for admin bar.
				cp_container_top = CommentPress.theme.settings.get_container_top_max();

				// Is the admin bar shown?
				if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {
					cp_container_top = CommentPress.theme.settings.get_container_top_max() - CommentPress.settings.DOM.get_wp_adminbar_height();
				}

				styles += '#container { top: ' + cp_container_top + 'px; } ';
				styles += '#sidebar { top: ' + CommentPress.theme.settings.get_container_top_max() + 'px; } ';

				// Is the admin bar shown?
				if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {

					// If we have the responsive admin bar in 3.8+
					if ( CommentPress.settings.DOM.get_wp_adminbar_height() == '32' ) {

						// React to responsive admin bar.
						styles += '@media screen and ( max-width: 782px ) { ' +
									'#sidebar { top: ' + (cp_container_top + CommentPress.settings.DOM.get_wp_adminbar_expanded()) + 'px; }' +
								' } ';

					}

				}

			}

			// Is this the comments sidebar?
			if ( cp_special_page == '0' ) {

				// Avoid flash of hidden comments.
				styles += '.paragraph_wrapper { display: none; } ';

				// Avoid flash of hidden comment form.
				styles += '#respond { display: none; } ';

				// Has the sidebar window been minimised?
				if ( CommentPress.theme.sidebars.get_minimised() == 'y' ) {

					// Set visibility of comments.
					styles += '#comments_sidebar .sidebar_contents_wrapper { display: none; } ';

				}

			}

			// On global activity sidebar, avoid flash of hidden comments.
			styles += '#activity_sidebar .paragraph_wrapper { display: none; } ';

			/*
			// Note: make into single cookie?
			// Has the page been changed?
			if ( $.cookie('cp_page_setup') ) {

				// Get value.
				cp_page_setup = $.cookie('cp_page_setup');

			}
			*/

			// Has the content column changed?
			if ( $.cookie('cp_container_width') ) {

				// Get value.
				cp_container_width = $.cookie('cp_container_width');

				// Set content width.
				styles += '#page_wrapper { width: ' + cp_container_width + '%; } ';

				// Set footer width.
				styles += '#footer { width: ' + cp_container_width + '%; } ';

			}

			// Has the book nav cookie changed?
			if ( $.cookie('cp_book_nav_width') ) {

				// Get book nav width.
				cp_book_nav_width = $.cookie('cp_book_nav_width');

				// Set its width.
				styles += '#book_nav div#cp_book_nav { width: ' + cp_book_nav_width + '%; } ';

			}

			// Has the sidebar window changed?
			if ( $.cookie('cp_sidebar_width') ) {

				// Set width of sidebar.
				styles += '#sidebar { width: ' + $.cookie('cp_sidebar_width') + '%; } ';

			}

			// Has the sidebar window changed?
			if ( $.cookie('cp_sidebar_left') ) {

				// Set width of sidebar.
				styles += '#sidebar { left: ' + $.cookie('cp_sidebar_left') + '%; } ';

			}

			// Show tabs when JS enabled.
			styles += 'ul#sidebar_tabs, #toc_header.sidebar_header, body.blog_post #activity_header.sidebar_header { display: block; } ';

			// Don't set height of sidebar when mobile - but allow tablets.
			if ( cp_is_mobile == '1' && cp_is_tablet == '0' ) {

				// Override CSS.
				styles += '.sidebar_contents_wrapper { height: auto; } ';

			}

			// Close style declaration.
			styles += '</style>';

		}

		// Write to page now.
		document.write( styles );

	};



	/**
	 * Page load prodecure.
	 *
	 * @since 3.8
	 */
	this.layout = function() {

		// Define vars.
		var target;

		// Target.
		target = $('#page_wrapper');

		/**
		 * Sets up the main column, if the id exists.
		 *
		 * @since 3.8
		 *
		 * @param integer i The number of iterations.
		 */
		target.each( function(i) {

			// Define vars.
			var item, content, sidebar, footer, book_header, book_nav_wrapper, book_nav,
				book_info, original_content_width, original_sidebar_width,
				original_nav_width, original_sidebar_left, gap;

			// Assign vars.
			item = $(this);
			content = $('#content');
			sidebar = $('#sidebar');
			footer = $('#footer');
			book_header = $('#book_header');
			book_nav_wrapper = $('#book_nav_wrapper');
			book_nav = $('#cp_book_nav');
			book_info = $('#cp_book_info');

			// Store original widths.
			original_content_width = item.width();
			original_sidebar_width = sidebar.width();

			// Calculate gap to sidebar.
			gap = sidebar.offset().left - original_content_width;

			// Make page wrapper resizable.
			item.resizable({

				handles: 'e',
				minWidth: cp_min_page_width,
				alsoResize: '#footer',
				//grid: 1, // No sub-pixel weirdness please.

				// On stop (note: this doesn't fire on the first go in Opera 9!)
				start: function( event, ui ) {

					// Store original widths.
					original_content_width = item.width();
					original_sidebar_width = sidebar.width();
					original_nav_width = book_nav.width();

					// Calculate sidebar left.
					original_sidebar_left = sidebar.css( "left" );
					gap = sidebar.offset().left - original_content_width;

				},

				// While resizing.
				resize: function( event, ui ) {

					// Define vars.
					var my_diff;

					item.css( 'height', 'auto' );
					footer.css( 'height', 'auto' );

					// Have the sidebar follow.
					sidebar.css( 'left', ( item.width() + gap ) + 'px' );

					// Diff.
					my_diff = original_content_width - item.width();

					// Have the sidebar right remain static.
					sidebar.css( 'width', ( original_sidebar_width + my_diff ) + 'px' );

					// Have the book nav follow.
					book_nav.css( 'width', ( original_nav_width - my_diff ) + 'px' ); // Diff in css

				},

				// On stop (note: this doesn't fire on the first go in Opera 9!)
				stop: function( event, ui ) {

					// Define vars.
					var ww, width, item_w, book_nav_w, sidebar_w, left, sidebar_l;

					// Viewport width.
					ww = parseFloat($(window).width() );

					// Get element width.
					width = item.width();

					// Get percent to four decimal places.
					item_w = parseFloat( Math.ceil( ( 1000000 * parseFloat( width ) / ww ) ) / 10000 );

					// Set element width.
					item.css("width" , item_w + '%');

					// Set content width to auto so it resizes properly.
					content.css( 'width', 'auto' );

					// Get element width.
					width = book_nav.width();

					// Get percent to four decimal places.
					book_nav_w = parseFloat( Math.ceil( ( 1000000 * parseFloat( width ) / ww ) ) / 10000 );

					// Set element width.
					book_nav.css( 'width', book_nav_w + '%' );

					// Get element width.
					width = sidebar.width();

					// Get percent to four decimal places.
					sidebar_w = parseFloat( Math.ceil( ( 1000000 * parseFloat( width ) / ww ) ) / 10000 );

					// Set element width.
					sidebar.css( 'width', sidebar_w + '%' );

					// Get element left.
					left = sidebar.position().left;

					// Get percent to four decimal places.
					sidebar_l = parseFloat( Math.ceil( ( 1000000 * parseFloat( left ) / ww ) ) / 10000 );

					// Set element left.
					sidebar.css( 'left', sidebar_l + '%' );

					// Store this width in cookie.
					$.cookie(
						'cp_container_width',
						item_w.toString(),
						{ expires: 28, path: cp_cookie_path }
					);

					// Store nav width in cookie.
					$.cookie(
						'cp_book_nav_width',
						book_nav_w.toString(),
						{ expires: 28, path: cp_cookie_path }
					);

					// Store location of sidebar in cookie.
					$.cookie(
						'cp_sidebar_left',
						sidebar_l.toString(),
						{ expires: 28, path: cp_cookie_path }
					);

					// Store width of sidebar in cookie.
					$.cookie(
						'cp_sidebar_width',
						sidebar_w.toString(),
						{ expires: 28, path: cp_cookie_path }
					);

				}

			});

		});

	};

}; // End DOM class.



/* -------------------------------------------------------------------------- */



/**
 * Create header class.
 *
 * @since 3.8
 */
CommentPress.theme.header = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress theme header.
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
	 * Get header offset.
	 *
	 * @since 3.8
	 *
	 * @return integer offset The target offset in px.
	 */
	this.get_offset = function() {

		// Define vars.
		var offset;

		/*
		// Need to decide whether to use border in offset.

		// Get offset including border.
		offset = 0 - (
			$.px_to_num( $('#container').css('top') ) +
			$.px_to_num( $('#page_wrapper').css( 'borderTopWidth' ) )
		);
		*/

		// Get header offset.
		offset = 0 - ( $.px_to_num( $('#container').css('top') ) );

		// Is the admin bar shown?
		if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {

			// Subtract admin bar height.
			offset -= CommentPress.settings.DOM.get_wp_adminbar_height();

		}

		// --<
		return offset;

	};

}; // End header class.



/* -------------------------------------------------------------------------- */



/**
 * Create navigation class.
 *
 * @since 3.8
 */
CommentPress.theme.navigation = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress theme navigation.
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

		// Enable "Special Pages" menu behaviour.
		me.menu();

	};



	/**
	 * Set up the "Contents" sidebar header.
	 *
	 * @since 3.8
	 */
	this.menu = function() {

		/**
		 * Clicking on the "Contents" sidebar header.
		 *
		 * @since 3.8
		 */
		$('#sidebar').on( 'click', '#toc_header h2 a', function( event ) {

			// Override event.
			event.preventDefault();

			// Activate it.
			CommentPress.theme.sidebars.activate_sidebar( 'toc' );

		});

	};

}; // End navigation class.



/* -------------------------------------------------------------------------- */



/**
 * Create content class.
 *
 * @since 3.8
 */
CommentPress.theme.content = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress theme content.
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

		// Enable "Workflow" tabs.
		me.tabs();

	};



	/**
	 * Set up "Workflow" tabs.
	 *
	 * Workflow adds "Literal" and "Original" tabs for use by translators.
	 * Each theme calls the common method with params calculated below.
	 *
	 * @since 3.8
	 */
	this.tabs = function() {

		// Define vars.
		var content_min_height, content_padding_bottom;

		// Store content min-height on load.
		content_min_height = $('#content').css( 'min-height' );

		// Store content padding-bottom on load.
		content_padding_bottom = $('#content').css( 'padding-bottom' );

		// Hide workflow content.
		$('#literal .post').css( 'display', 'none' );
		$('#original .post').css( 'display', 'none' );

		// Setup workflow tabs, if present.
		CommentPress.common.content.workflow_tabs( content_min_height, content_padding_bottom );

	};

}; // End content class.



/* -------------------------------------------------------------------------- */



/**
 * Create sidebars class.
 *
 * @since 3.8
 */
CommentPress.theme.sidebars = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress theme sidebars.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.init = function() {

		// Init sidebar minimised flag.
		me.init_minimised();

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.dom_ready = function() {

		// Don't set height when mobile device - but allow tablets.
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

			// Set sidebar height.
			me.set_height();

		}

	};



	// Init CommentPress sidebar minimised flag.
	this.minimised = 'n';

	/**
	 * Init for CommentPress sidebar minimised flag.
	 *
	 * @since 3.8
	 */
	this.init_minimised = function() {

		// Get state of sidebar.
		me.sidebar_minimised = $.cookie( 'cp_sidebar_minimised' );
		if ( 'undefined' === typeof me.sidebar_minimised || me.sidebar_minimised === null ) {
			me.sidebar_minimised = 'n';
		}

	};

	/**
	 * Setter for CommentPress sidebar minimised flag.
	 *
	 * @since 3.8
	 */
	this.set_minimised = function( val ) {
		me.minimised = val;
	};

	/**
	 * Getter for CommentPress sidebar minimised flag.
	 *
	 * @since 3.8
	 */
	this.get_minimised = function() {
		return me.minimised;
	};

	/**
	 * Getter for CommentPress sidebar minimised flag.
	 *
	 * @since 3.8
	 */
	this.is_minimised = function() {
		if (
			'undefined' === typeof me.minimised ||
			me.minimised === null ||
			me.minimised == 'n'
		) {
			return 'n';
		}
		return me.minimised;
	};

	/**
	 * Toggle for CommentPress sidebar minimised flag.
	 *
	 * @since 3.8
	 */
	this.toggle_minimised = function() {
		if ( me.minimised === 'y' ) {
			me.minimised = 'n';
		} else {
			me.minimised = 'y';
		}
	};



	/**
	 * Bring sidebar to front.
	 *
	 * @since 3.8
	 *
	 * @param string sidebar The sidebar to bring to the front.
	 */
	this.activate_sidebar = function( sidebar ) {

		// Define vars.
		var ontop, s_top, s_top_border;

		// Get "visibility" of the requested sidebar.
		ontop = $('#' + sidebar + '_sidebar').css( 'z-index' );

		// Is it hidden - ie, does it have a lower z-index?
		if ( ontop == '2001' ) {

			// Hide all.
			$('.sidebar_container').css('z-index','2001');

			// Show it.
			$('#' + sidebar + '_sidebar').css('z-index','2010');

			s_top = me.get_top();
			s_top_border = me.get_top_border();

			// Set all tabs to min height.
			$('.sidebar_header').css( 'height', ( s_top - s_top_border ) + 'px' );

			// Set our tab to max height.
			$('#' + sidebar + '_header.sidebar_header').css( 'height', s_top + 'px' );

			// Set flag.
			CommentPress.theme.settings.set_toc_on_top( 'y' );

		}

		// Set height if not mobile device - but allow tablets.
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

			// Just to make sure.
			me.set_height();

		} else {

			// Hide all.
			$('.sidebar_container').css( 'visibility', 'hidden' );

			// Show it.
			$('#' + sidebar + '_sidebar').css( 'visibility', 'visible' );

			/*
			// Define vars.
			var containers, tallest, this_height;

			// Set to height of tallest.
			containers = $('.sidebar_contents_wrapper');

			// Did we get any?
			if ( containers.length > 0 ) {

				// Init.
				tallest = 0;

				// Find height of each.
				containers.each( function(i) {

					// Get height.
					this_height = $(this).height()

					// Is it taller?
					if ( this_height > tallest ) {
						tallest = this_height;
					}

				});

				// Set it to that height.
				$('.sidebar_contents_wrapper').height( tallest );

				// Then make it auto.
				// BUT, this won't allow it to expand in future.
				//$('#' + sidebar + '_sidebar .sidebar_contents_wrapper').css('height','auto');

			}
			*/

		}

	};



	/**
	 * Get top of sidebar.
	 *
	 * @since 3.8
	 *
	 * @return integer num The top of the sidebar in pixels.
	 */
	this.get_top = function() {
		return $.px_to_num( $('#toc_sidebar').css('top') );
	};



	/**
	 * Get border width of sidebar.
	 *
	 * @since 3.8
	 *
	 * @return integer num The border width of the sidebar in pixels.
	 */
	this.get_top_border = function() {
		return $.px_to_num( $('.sidebar_minimiser').css('borderTopWidth') );
	};



	/**
	 * Get visible sidebar minimiser.
	 *
	 * @since 3.0
	 *
	 * @return object sidebar_pane The jQuery object for the sidebar pane.
	 */
	this.get_sidebar_pane = function() {

		// Init.
		var name = me.get_sidebar_name();

		// --<
		return $('#' + name + '_sidebar .sidebar_minimiser');

	}



	/**
	 * Get visible sidebar.
	 *
	 * @since 3.0
	 *
	 * @return string name The name of the visible sidebar.
	 */
	this.get_sidebar_name = function() {

		// Init.
		var name = 'toc';

		// If toc, must be toc.
		//if ( cp_default_sidebar == 'toc' ) { name = 'toc'; }

		// If comments.
		if ( cp_default_sidebar == 'comments' ) {
			name = 'comments';
			if ( CommentPress.theme.settings.get_toc_on_top() == 'y' ) {
				name = 'toc';
			}
		}

		// If activity.
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
	 * Get height data on element.
	 *
	 * @since 3.0
	 *
	 * @param object element The element to adjust.
	 * @return int element_adjust The new height of the element in px.
	 */
	this.get_element_adjust = function( element ) {

		// Declare vars.
		var w_bt, w_bb, w_pad_t, w_pad_b, w_mar_t, w_mar_b, element_adjust;

		// Get border.
		w_bt = $.css_to_num( $.px_to_num( element.css( 'borderTopWidth' ) ) );
		w_bb = $.css_to_num( $.px_to_num( element.css( 'borderBottomWidth' ) ) );

		// Get padding.
		w_pad_t = $.css_to_num( $.px_to_num( element.css( 'padding-top' ) ) );
		w_pad_b = $.css_to_num( $.px_to_num( element.css( 'padding-bottom' ) ) );

		// Get margin.
		w_mar_t = $.css_to_num( $.px_to_num( element.css( 'margin-top' ) ) );
		w_mar_b = $.css_to_num( $.px_to_num( element.css( 'margin-bottom' ) ) );

		// Add 'em up.
		element_adjust = w_bt + w_bb + w_pad_t + w_pad_b + w_mar_t + w_mar_b;

		// --<
		return element_adjust;

	}



	/**
	 * Set height of sidebar minimiser (scrolling element) so that the column fills the viewport.
	 *
	 * @since 3.0
	 *
	 * @todo In jQuery 1.9, we get a 143px error, related to sidebar.position().top.
	 *
	 * @return int to_bottom The height of the sidebar in px.
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

		// Get data on sidebar element.
		s_top = sidebar.offset().top;
		sidebar_inside_h = me.get_element_adjust( sidebar );
		sidebar_inner_inside_h = me.get_element_adjust( sidebar_inner );
		sidebar_diff = s_top + sidebar_inside_h + sidebar_inner_inside_h;

		// Get data on sidebar_container element.
		sc_top = sidebar_container.position().top;
		sc_inside_h = me.get_element_adjust( sidebar_container );
		sc_diff = sc_top + sc_inside_h;

		// Init header diff.
		header_diff = 0;
		// If internal header element is displayed.
		if ( header.css('display') != 'none' ) {
			// Get data on header element.
			header_diff = header.height() + me.get_element_adjust( header );
		}

		// Get data on minimiser element.
		minimiser_diff = me.get_element_adjust( minimiser );

		// Get bottom margin of main column so sidebar lines up.
		// NOTE: this is NOT why they don't line up - it just so happens that the values match.
		// It seems the clearfix class adds the margin. Sigh.
		bottom_margin = $.css_to_num( $.px_to_num( $('#page_wrapper').css( 'margin-bottom' ) ) );

		// Get viewport data.
		viewport_height = $(window).height();
		viewport_scrolltop = $(window).scrollTop();
		viewport = viewport_height + viewport_scrolltop;

		// Calculate the necessary height to reach the bottom of the viewport.
		to_bottom = viewport - ( sidebar_diff + sc_diff + header_diff + minimiser_diff + bottom_margin );

		// Now set it.
		$('#sidebar div.sidebar_contents_wrapper').css( 'height', to_bottom + 'px' );

		// --<
		return to_bottom;

	}

}; // End sidebars class.



/* -------------------------------------------------------------------------- */



/**
 * Create viewport class.
 *
 * @since 3.8
 */
CommentPress.theme.viewport = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress theme viewport.
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
	 * Scroll page to top.
	 *
	 * @since 3.8
	 *
	 * @param object target The object to scroll to.
	 * @param integer speed The duration of the scroll.
	 */
	this.scroll_to_top = function( target, speed ) {

		// Bail if we didn't get a valid target.
		if ( 'undefined' === typeof target ) { return; }

		// Only scroll if not mobile - but allow tablets.
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

			// Scroll.
			$(window).stop(true).scrollTo( target, speed );

		}

	};



	/**
	 * Page load prodecure.
	 *
	 * @since 3.8
	 */
	this.on_load_scroll_to_anchor = function() {

		// Define vars.
		var text_sig, url, comment_id, para_wrapper_array, item, para_id, para_num,
			post_id, textblock, anchor_id, anchor, found;

		// Init.
		text_sig = '';
		found = false;

		// If there is an anchor in the URL - only on non-special pages.
		url = document.location.toString();

		// Do we have a comment permalink?
		if ( url.match( '#comment-' ) ) {

			// Get comment ID.
			tmp = url.split('#comment-');

			// Sanity check.
			comment_id = 0;
			if ( tmp.length == 2 ) {
				comment_id = parseInt( tmp[1] );
			}

			// Did we get one?
			if ( comment_id !== 0 ) {
				me.on_load_scroll_to_comment( comment_id );
			}

			// Set location to page/post permalink.
			CommentPress.common.DOM.location_reset();

			// --<
			return;

		} else {

			/**
			 * Loop through the paragraph permalinks checking for a match.
			 *
			 * @since 3.8
			 */
			$('span.para_marker > a').each( function(i) {

				// Define vars.
				var text_sig, para_id, para_num, post_id, textblock;

				// Get text signature.
				text_sig = $(this).prop( 'id' );

				// Do we have a paragraph or comment block permalink?
				if ( url.match( '#' + text_sig ) || url.match( '#para_heading-' + text_sig ) ) {

					// Align content.
					me.align_content( text_sig, 'para_heading' );

					// Set location to page/post permalink.
					CommentPress.common.DOM.location_reset();

					// Set flag.
					found = true;

				}

			});

		}

		// Check flag and bail if already found.
		if ( found === true ) { return; }

		// Do we have a link to the comment form?
		if ( url.match( '#respond' ) ) {

			// Is this a "Reply to [...]" link?
			if ( url.match( 'replytocom' ) ) {

				// Get parent from form.
				comment_parent = parseInt( $('#comment_parent').val() );

				// Also same as load procedure.
				me.on_load_scroll_to_comment( comment_parent );

			} else {

				// Is this a "Leave Comment on [...]" link.
				if ( url.match( 'replytopara' ) ) {

					// Get text sig from form.
					text_sig = $('#text_signature').val();

					// Align content.
					me.align_content( text_sig, 'commentform' );

				} else {

					// Same as clicking on the "whole page" heading.
					$('h3#para_heading- a.comment_block_permalink').click();

				}

			}

			// Set location to page/post permalink.
			CommentPress.common.DOM.location_reset();

			// --<
			return;

		}

		// Any other anchors in the .post?
		if ( url.match( '#' ) ) {

			// Get anchor.
			anchor_id = url.split('#')[1];

			// Bail if it's WP FEE's custom anchor.
			if ( anchor_id == 'edit=true' ) { return; }
			if ( anchor_id == 'fee-edit-link' ) { return; }

			// Locate in DOM.
			anchor = $( '#' + anchor_id );

			// Did we get one?
			if ( anchor.length ) {

				// Add class.
				anchor.addClass('selected_para');

				// Scroll page.
				CommentPress.common.content.scroll_page( anchor );

			}

			// Set location to page/post permalink.
			CommentPress.common.DOM.location_reset();

			// --<
			return;

		}

	};



	/**
	 * Scroll to comment on page load.
	 *
	 * @since 3.8
	 *
	 * @param int comment_id The ID of the comment to scroll to.
	 */
	this.on_load_scroll_to_comment = function( comment_id ) {

		// Define vars.
		var text_sig, para_wrapper_array, item, para_id, para_num,
			post_id, textblock;

		// Activate comments sidebar.
		CommentPress.theme.sidebars.activate_sidebar( 'comments' );

		// Open the matching block.

		// Get array of parent paragraph_wrapper divs.
		para_wrapper_array = $('#comment-' + comment_id)
									.parents('div.paragraph_wrapper')
									.map( function () {
										return this;
									});

		// Did we get one?
		if ( para_wrapper_array.length > 0 ) {

			// Get the item.
			item = $(para_wrapper_array[0]);

			// Get the text sig.
			text_sig = item.prop('id').split('-')[1];

			// Are comments open?
			if ( cp_comments_open == 'y' ) {

				// Move form to para.
				para_id = $('#para_wrapper-'+text_sig+' .reply_to_para').prop('id');
				para_num = para_id.split('-')[1];
				post_id = $('#comment_post_ID').prop('value');

				// Seems like TinyMCE isn't yet working and that moving the form
				// prevents it from loading properly.
				if ( cp_tinymce == '1' ) {

					// If we have link text, then a comment reply is allowed.
					if ( $( '#comment-' + comment_id + ' > .reply' ).text() !== '' ) {

						// Temporarily override global so that TinyMCE is not
						// meddled with in any way.
						cp_tinymce = '0';

						// Move the form.
						addComment.moveForm(
							'comment-' + comment_id,
							comment_id,
							'respond',
							post_id,
							text_sig
						);

						// Restore global.
						cp_tinymce = '1';

					}

				} else {

					// Move the form.
					addComment.moveForm(
						'comment-' + comment_id,
						comment_id,
						'respond',
						post_id,
						text_sig
					);

				}

			}

			// Show block.
			item.show();

			// Scroll comments.
			CommentPress.common.comments.scroll_comments( $('#comment-' + comment_id), 1, 'flash' );

			// If not the whole page.
			if( text_sig !== '' ) {

				// Get text block.
				textblock = $('#textblock-' + text_sig);

				// Highlight this paragraph.
				$.highlight_para( textblock );

				// Scroll page.
				CommentPress.common.content.scroll_page( textblock );

			} else {

				// Only scroll if page is not highlighted.
				if ( !CommentPress.settings.page.get_highlight() ) {

					// Scroll to top.
					CommentPress.theme.viewport.scroll_to_top( 0, cp_scroll_speed );

				}

				// Toggle page highlight flag.
				CommentPress.settings.page.toggle_highlight();

			}

		}

	};



	/**
	 * Does what a click on a comment icon should do.
	 *
	 * @since 3.8
	 *
	 * @param string text_sig The text signature to scroll to.
	 * @param string scroll_target Where to scroll to ('none', 'para_heading', 'commentform' or 'auto' if empty).
	 */
	this.align_content = function( text_sig, scroll_target ) {

		// Bail if scroll target is 'none'.
		if ( scroll_target == 'none' ) { return; }

		// Show comments sidebar.
		CommentPress.theme.sidebars.activate_sidebar( 'comments' );

		// Define vars.
		var para_wrapper, comment_list, respond, top_level, opening, visible,
			textblock, post_id, para_id, para_num;

		// Get para wrapper.
		para_wrapper = $('#para_heading-' + text_sig).next('div.paragraph_wrapper');

		// Bail if we don't have the target element.
		if ( para_wrapper.length == 0 ) {
			return;
		}

		// Get comment list.
		comment_list = $( '#para_wrapper-' + text_sig + ' .commentlist' );

		// Get respond.
		respond = para_wrapper.find('#respond');

		// Is it a direct child of para wrapper?
		top_level = addComment.getLevel();

		// Init.
		opening = false;

		// Get visibility.
		visible = para_wrapper.css('display');

		// Override.
		if ( visible == 'none' ) { opening = true; }

		// Clear other highlights.
		$.unhighlight_para();

		// Did we get a text_sig?
		if ( text_sig !== '' ) {

			// Get text block.
			textblock = $('#textblock-' + text_sig);

			// If encouraging reading and closing.
			if ( cp_promote_reading == '1' && !opening ) {

				// Skip the highlight.

			} else {

				// Highlight this paragraph.
				$.highlight_para( textblock );

				// Scroll page.
				CommentPress.common.content.scroll_page( textblock );

			}

		}

		// If encouraging commenting.
		if ( cp_promote_reading == '0' ) {

			// Are comments open?
			if ( cp_comments_open == 'y' ) {

				// Get comment post ID.
				post_id = $('#comment_post_ID').prop('value');
				para_id = $('#para_wrapper-'+text_sig+' .reply_to_para').prop('id');
				para_num = para_id.split('-')[1];

			}

			// Choices, choices:

			// If it doesn't have the commentform.
			if ( !respond[0] ) {

				// Are comments open?
				if ( cp_comments_open == 'y' ) {
					addComment.moveFormToPara( para_num, text_sig, post_id );
				}

			}

			// If it has the commentform but is not top level.
			if ( respond[0] && !top_level ) {

				// Are comments open?
				if ( cp_comments_open == 'y' ) {

					// Move comment form.
					addComment.moveFormToPara( para_num, text_sig, post_id );

					// If scroll_target is for para_headings.
					if ( scroll_target == 'para_heading' ) {

						// Scroll comments to header.
						CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

					} else {

						// Scroll comments to comment form.
						CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

					}

				} else {

					// Scroll comments to header.
					CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

				}

				return;

			}

			// If it doesn't have the commentform but has a comment.
			if ( !respond[0] && comment_list[0] && !opening ) {

				// Are comments open?
				if ( cp_comments_open == 'y' ) {

					// If scroll_target is for para_headings.
					if ( scroll_target == 'para_heading' ) {

						// Scroll comments to header.
						CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

					} else {

						// Scroll comments to comment form.
						CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

					}

				} else {

					// Scroll comments to header.
					CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

				}

				return;

			}

			// If closing with comment list.
			if ( !opening && comment_list[0] ) {

				// Are comments open?
				if ( cp_comments_open == 'y' ) {

					// If scroll_target is for para_headings.
					if ( scroll_target == 'para_heading' ) {

						// Scroll comments to header.
						CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

					} else {

						// Scroll comments to comment form.
						CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

					}

				} else {

					// Scroll comments to header.
					CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

				}

				return;

			}

			// If commentform but no comments and closing.
			if ( respond[0] && !comment_list[0] && !opening ) {

				// Are comments open?
				if ( cp_comments_open == 'y' ) {

					// If scroll_target is for para_headings.
					if ( scroll_target == 'para_heading' ) {

						// Scroll comments to header.
						CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

					} else {

						// Scroll comments to comment form.
						CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

					}

				} else {

					// Scroll comments to header.
					CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

				}

				// --<
				return;

			}

			// If closing with no comment list.
			if ( !opening && !comment_list[0] ) {

				para_wrapper.css( 'display', 'none' );
				opening = true;

			}

		}

		// Toggle next item_body.
		para_wrapper.slideToggle( 'slow', function () {

			// Animation finished.

			// Are we encouraging reading?
			if ( cp_promote_reading == '1' && opening ) {

				// Scroll comments.
				CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

			} else {

				// Only if opening.
				if ( opening ) {

					// Are comments open?
					if ( cp_comments_open == 'y' ) {

						// If scroll_target is for para_headings.
						if ( scroll_target == 'para_heading' ) {

							// Scroll comments to header.
							CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

						} else {

							// Scroll comments to comment form.
							CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

						}

					} else {

						// Scroll comments to comment form.
						CommentPress.common.comments.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

					}

				}

			}

		});

	};

}; // End viewport class.



/* -------------------------------------------------------------------------- */



/**
 * Create sub-namespace for default (original) theme.
 *
 * @since 3.8
 */
CommentPress.theme.original = {};



/**
 * Create header class.
 *
 * @since 3.8
 */
CommentPress.theme.original.header = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict(),
		header_animating = false;



	/**
	 * Initialise CommentPress original header.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.init = function() {

		// Init minimised flag.
		me.init_minimised();

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.dom_ready = function() {

		// Init header height.
		me.init_height();

		// Enable minimiser button.
		me.minimiser();

	};



	// Init CommentPress header height
	this.header_height = 0;

	/**
	 * Init for CommentPress header height.
	 *
	 * @since 3.8
	 */
	this.init_height = function() {

		// Get global book_header height.
		me.header_height = $('#book_header').height();

	};

	/**
	 * Setter for CommentPress header height.
	 *
	 * @since 3.8
	 */
	this.set_height = function( val ) {
		me.header_height = val;
	};

	/**
	 * Getter for CommentPress header height.
	 *
	 * @since 3.8
	 */
	this.get_height = function() {
		return me.header_height;
	};



	// Init CommentPress header minimised flag.
	this.minimised = 'n';

	/**
	 * Init for CommentPress header minimised flag.
	 *
	 * @since 3.8
	 */
	this.init_minimised = function() {

		// Get state of header.
		me.minimised = $.cookie( 'cp_header_minimised' );
		if ( 'undefined' === typeof me.minimised || me.minimised === null ) {
			me.minimised = 'n';
		}

	};

	/**
	 * Setter for CommentPress header minimised flag.
	 *
	 * @since 3.8
	 */
	this.set_minimised = function( val ) {
		me.minimised = val;
	};

	/**
	 * Getter for CommentPress header minimised flag.
	 *
	 * @since 3.8
	 */
	this.get_minimised = function() {
		return me.minimised;
	};

	/**
	 * Getter for CommentPress header minimised flag.
	 *
	 * @since 3.8
	 *
	 * @return bool Whether or not the header is minimised.
	 */
	this.is_minimised = function() {
		if (
			 'undefined' === typeof me.minimised ||
			me.minimised === null ||
			me.minimised == 'n'
		) {
			return false;
		}
		return true;
	};

	/**
	 * Toggle for CommentPress header minimised flag.
	 *
	 * @since 3.8
	 */
	this.toggle_minimised = function() {
		if ( me.minimised === 'y' ) {
			me.minimised = 'n';
		} else {
			me.minimised = 'y';
		}
	};



	/**
	 * Set up Header minimise button.
	 *
	 * @since 3.8
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

			// Override event.
			event.preventDefault();

			// Call function.
			me.toggle();

		});

	};



	/**
	 * Set up header minimiser button.
	 *
	 * @since 3.8
	 */
	this.toggle = function() {

		// If animating, kick out
		if ( header_animating === true ) { return false; }
		header_animating = true;

		// Toggle.
		if ( me.is_minimised() ) {
			me.open();
		} else {
			me.close();
		}

		// Toggle.
		me.toggle_minimised();

		// Store flag in cookie.
		$.cookie(
			'cp_header_minimised',
			me.get_minimised(),
			{ expires: 28, path: cp_cookie_path }
		);

	};



	/**
	 * Open header.
	 *
	 * @since 3.8
	 */
	this.open = function() {

		// Define vars.
		var book_nav_h, target_sidebar, target_sidebar_pane, book_header, container,
			cp_container_top, cp_sidebar_height;

		// Get nav height.
		book_nav_h = $('#book_nav').height();

		target_sidebar = $('#sidebar');
		target_sidebar_pane = CommentPress.theme.sidebars.get_sidebar_pane();
		book_header = $('#book_header');
		container = $('#container');

		// Set max height.
		cp_container_top = CommentPress.theme.settings.get_container_top_max();

		// Is the admin bar shown?
		if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {

			// Deduct height of admin bar.
			cp_container_top = CommentPress.theme.settings.get_container_top_max() - CommentPress.settings.DOM.get_wp_adminbar_height();

		}

		// Animate container.
		container.animate({

			top: cp_container_top + 'px',
			duration: 'fast'

			}, function () {

				// Slide book header.
				book_header.fadeIn('fast', function() {

					// When done.
					header_animating = false;

				});

			}

		);

		// Is the sidebar minimised?
		if ( CommentPress.theme.sidebars.get_minimised() == 'n' ) {

			// Get sidebar height.
			cp_sidebar_height = target_sidebar.height() - me.get_height();

			// Animate main wrapper.
			target_sidebar.animate({

				top: CommentPress.theme.settings.get_container_top_max() + 'px',
				height: cp_sidebar_height + 'px',
				duration: 'fast'

				}, function() {

					// When done.
					target_sidebar.css( 'height','auto' );

				}

			);

			// Animate inner.
			target_sidebar_pane.animate({

				height: ( target_sidebar_pane.height() - me.get_height() ) + 'px',
				duration: 'fast'

				}, function() {

					// Don't set height when mobile device - but allow tablets. Needs testing.
					if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

						// Fit column.
						CommentPress.theme.sidebars.set_height();

					}

					// When done.
					header_animating = false;

				}

			);

		} else {

			// Animate sidebar.
			target_sidebar.animate({

				top: CommentPress.theme.settings.get_container_top_max() + 'px',
				duration: 'fast'

				}, function() {

					// When done.
					header_animating = false;

					// Don't set height when mobile device - but allow tablets.
					if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

						// Fit column.
						CommentPress.theme.sidebars.set_height();

					}

				}

			);

		}

	};



	/**
	 * Close header.
	 *
	 * @since 3.8
	 */
	this.close = function() {

		// Define vars.
		var book_nav_h, target_sidebar, target_sidebar_pane, book_header, container;
		var cp_container_top, cp_sidebar_height;

		// Get nav height.
		book_nav_h = $('#book_nav').height();

		target_sidebar = $('#sidebar');
		target_sidebar_pane = CommentPress.theme.sidebars.get_sidebar_pane();
		book_header = $('#book_header');
		container = $('#container');

		// Hide header.
		book_header.hide();

		// Set min height.
		cp_container_top = CommentPress.theme.settings.get_container_top_min();

		// Is the admin bar shown?
		if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {

			// Deduct height of admin bar.
			cp_container_top = CommentPress.theme.settings.get_container_top_min() - CommentPress.settings.DOM.get_wp_adminbar_height();

		}

		container.animate({

			top: cp_container_top + 'px',
			duration: 'fast'

		});

		// Is the sidebar minimised?
		if ( CommentPress.theme.sidebars.get_minimised() == 'n' ) {

			// Get sidebar height.
			cp_sidebar_height = target_sidebar.height() + me.get_height();

			//$('#container').css('top','40px');
			target_sidebar.animate({

				top: CommentPress.theme.settings.get_container_top_min() + 'px',
				height: cp_sidebar_height + 'px',
				duration: 'fast'

				}, function() {

					// When done.
					target_sidebar.css( 'height','auto' );

				}

			);

			//$('#container').css('top','40px');
			target_sidebar_pane.animate({

				height: ( target_sidebar_pane.height() + me.get_height() ) + 'px',
				duration: 'fast'

				}, function() {

					// Don't set height when mobile device (but allow tablets)
					if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

						// Fit column.
						CommentPress.theme.sidebars.set_height();

					}

					// When done.
					header_animating = false;

				}

			);

		} else {

			// Animate just sidebar.
			target_sidebar.animate({

				top: CommentPress.theme.settings.get_container_top_min() + 'px',
				duration: 'fast'

				}, function() {

					// When done.
					header_animating = false;

					// Don't set height when mobile device - but allow tablets.
					if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

						// Fit column.
						CommentPress.theme.sidebars.set_height();

					}

				}

			);

		}

	};



}; // End original.header class.



/* -------------------------------------------------------------------------- */



// Do immediate init.
CommentPress.theme.settings.init();

// The default theme needs its header inited before DOM.
CommentPress.theme.original.header.init();

CommentPress.theme.DOM.init();
CommentPress.theme.header.init();
CommentPress.theme.navigation.init();
CommentPress.theme.content.init();
CommentPress.theme.sidebars.init();
CommentPress.theme.viewport.init();



/* -------------------------------------------------------------------------- */



/**
 * Define what happens when the page is ready.
 *
 * @since 3.8
 */
jQuery(document).ready( function($) {



	// The default theme implements custom header actions.
	CommentPress.theme.original.header.dom_ready();

	// Trigger DOM ready methods.
	CommentPress.theme.settings.dom_ready();
	CommentPress.theme.DOM.dom_ready();
	CommentPress.theme.header.dom_ready();
	CommentPress.theme.navigation.dom_ready();
	CommentPress.theme.content.dom_ready();
	CommentPress.theme.sidebars.dom_ready();
	CommentPress.theme.viewport.dom_ready();



	/**
	 * Hook into CommentPress Form comment highlight trigger.
	 *
	 * @since 3.8
	 *
	 * @param int parent_id The parent comment ID.
	 */
	$( document ).on( 'commentpress-comment-highlight', function( event, parent_id ) {

		// Declare vars.
		var comment_border;

		// Set highlight colour.
		jQuery('#li-comment-' + parent_id + ' > .comment-wrapper').css( 'background-color', '#CBFFBD' );

		// Get existing colour.
		comment_border = jQuery('#comment-' + parent_id + ' > .comment-content').css( 'border-bottom' );

		// Save it.
		CommentPress.theme.settings.set_comment_border( comment_border );

		// Set highlight.
		jQuery('#comment-' + parent_id + ' > .comment-content').css( 'border-bottom', '1px dashed #b8b8b8' );


	});

	/**
	 * Hook into CommentPress Form comment unhighlight trigger.
	 *
	 * @since 3.8
	 *
	 * @param int parent_id The parent comment ID.
	 */
	$( document ).on( 'commentpress-comment-unhighlight', function( event, parent_id ) {

		// Declare vars.
		var comment_border;

		// Get existing colour.
		comment_border = CommentPress.theme.settings.get_comment_border();

		// Reset highlight colours.
		jQuery('#li-comment-' + parent_id + ' > .comment-wrapper').css( 'background-color', '#fff' );
		jQuery('#comment-' + parent_id + ' > .comment-content').css( 'border-bottom', comment_border );

	});

	/**
	 * Hook into CommentPress Form clear all comment highlights trigger.
	 *
	 * @since 3.8
	 */
	$( document ).on( 'commentpress-comment-highlights-clear', function( event ) {

		// Declare vars.
		var comment_border;

		// Get existing colour.
		comment_border = CommentPress.theme.settings.get_comment_border();

		// Reset highlight colours.
		jQuery('.comment-wrapper').css( 'background-color', '#fff');
		jQuery('.comment-content').css( 'border-bottom', comment_border );

	});



	/**
	 * When a comment block permalink comes into focus.
	 *
	 * @note in development for keyboard accessibility.
	 */
	/*
	if ( $().jquery >= 1.4 ) {
		$('a.comment_block_permalink').focusin( function(e) {

			// Test -> needs refinement.
			//$(this).click();

		});
	}
	*/

	/**
	 * When a comment block permalink loses focus.
	 *
	 * @note: in development for keyboard accessibility.
	 */
	/*
	$('a.comment_block_permalink').blur( function(e) {

		// Test -> needs refinement.
		//$(this).click();

	});
	*/



	// Scroll the page on load.
	if ( cp_special_page == '1' ) {
		CommentPress.common.content.on_load_scroll_to_comment();
	} else {
		CommentPress.theme.viewport.on_load_scroll_to_anchor();
	}



	// Broadcast that we're done.
	$( document ).trigger( 'commentpress-document-ready' );

});



/**
 * Define what happens when the page is unloaded.
 *
 * @since 3.8
 */
/*
jQuery(window).unload( function() {

	// Debug.
	//console.log('Bye now!');

});
*/
