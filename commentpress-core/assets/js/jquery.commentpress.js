/**
 * -----------------------------------------------------------------------------
 * CommentPress Core Common Code Library
 * -----------------------------------------------------------------------------
 *
 * This code implements some features of a jQuery Plugin, but is mostly used as
 * a common library for all CommentPress Core-compatible themes. It allows us to
 * add numerous methods to jQuery without cluttering the global function
 * namespace.
 *
 * The code has been present since 3.0 but was completely refactored in 3.8, so
 * most methods are marked as being since 3.8.
 *
 * -----------------------------------------------------------------------------
 * @package CommentPress Core
 * @author Christian Wach <needle@haystack.co.uk>
 *
 * @since 3.0
 * -----------------------------------------------------------------------------
 */
;



/**
 * Create global variables.
 *
 * These were being defined in each theme, so have been moved to this library to
 * avoid duplication of code. They are in the porcess of being migrated to class
 * variables to avoid name collisions.
 *
 * @since 3.0
 */

// Define global IE var.
var msie_detected = false;

// Browser detection via conditional comments in <head>.
if ( 'undefined' !== typeof cp_msie ) {
	msie_detected = true;
}

// Test for our localisation object.
if ( 'undefined' !== typeof CommentpressSettings ) {

	// Define our vars.
	var cp_comments_open, cp_special_page, cp_tinymce, cp_tinymce_version,
		cp_promote_reading, cp_is_mobile, cp_is_touch, cp_is_tablet, cp_cookie_path,
		cp_multipage_page, cp_toc_chapter_is_page, cp_show_subpages,
		cp_default_sidebar, cp_scroll_speed, cp_min_page_width,
		cp_textblock_meta, cp_touch_testing;

	// Set our vars.
	cp_comments_open = CommentpressSettings.cp_comments_open;
	cp_special_page = CommentpressSettings.cp_special_page;
	cp_tinymce = CommentpressSettings.cp_tinymce;
	cp_tinymce_version = CommentpressSettings.cp_tinymce_version;
	cp_promote_reading = CommentpressSettings.cp_promote_reading;
	cp_is_mobile = CommentpressSettings.cp_is_mobile;
	cp_is_touch = CommentpressSettings.cp_is_touch;
	cp_is_tablet = CommentpressSettings.cp_is_tablet;
	cp_touch_testing = CommentpressSettings.cp_touch_testing;
	cp_cookie_path = CommentpressSettings.cp_cookie_path;
	cp_multipage_page = CommentpressSettings.cp_multipage_page;
	cp_toc_chapter_is_page = CommentpressSettings.cp_toc_chapter_is_page;
	cp_show_subpages = CommentpressSettings.cp_show_subpages;
	cp_default_sidebar = CommentpressSettings.cp_default_sidebar;
	cp_scroll_speed = CommentpressSettings.cp_js_scroll_speed;
	cp_min_page_width = CommentpressSettings.cp_min_page_width;
	cp_textblock_meta = CommentpressSettings.cp_textblock_meta;

}



/* -------------------------------------------------------------------------- */



/**
 * Create global CommentPress Core namespace.
 *
 * @since 3.8
 */
var CommentPress = CommentPress || {};



/* -------------------------------------------------------------------------- */



/**
 * Create settings sub-namespace.
 *
 * @since 3.8
 */
CommentPress.settings = {};



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress Core settings DOM class.
 *
 * @since 3.8
 */
CommentPress.settings.DOM = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress Core settings DOM.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.init = function() {

		// Init WordPress adminbar.
		me.init_wp_adminbar();

		// Init BuddyPress adminbar.
		me.init_bp_adminbar();

		// Init WordPress adminbar height.
		me.init_wp_adminbar_height();

		// Init WordPress adminbar expanded height.
		me.init_wp_adminbar_expanded();

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



	// Init original permalink.
	this.original_permalink = document.location.toString();

	// Override with the value from our localisation object, if present.
	if ( 'undefined' !== typeof CommentpressSettings ) {
		if ( CommentpressSettings.cp_permalink != '' ) {
			me.original_permalink = CommentpressSettings.cp_permalink;
		}
	}

	/**
	 * Setter for original permalink.
	 *
	 * @since 3.8
	 */
	this.set_permalink = function( val ) {
		me.original_permalink = val;
	};

	/**
	 * Getter for original permalink.
	 *
	 * @since 3.8
	 */
	this.get_permalink = function() {
		return me.original_permalink;
	};



	// Init BuddyPress adminbar.
	this.bp_adminbar = 'n';

	/**
	 * Init for BuddyPress adminbar height.
	 *
	 * @since 3.8
	 */
	this.init_bp_adminbar = function( val ) {

		// Get initial value from settings object.
		if ( 'undefined' !== typeof CommentpressSettings ) {
			me.bp_adminbar = CommentpressSettings.cp_bp_adminbar;
		}

	};

	/**
	 * Setter for BuddyPress adminbar.
	 *
	 * @since 3.8
	 */
	this.set_bp_adminbar = function( val ) {
		me.bp_adminbar = val;
	};

	/**
	 * Getter for BuddyPress adminbar.
	 *
	 * @since 3.8
	 */
	this.get_bp_adminbar = function() {
		return me.bp_adminbar;
	};



	// Init WordPress adminbar.
	this.wp_adminbar = 'n';

	/**
	 * Init for WordPress adminbar height.
	 *
	 * @since 3.8
	 */
	this.init_wp_adminbar = function( val ) {

		// Get initial value from settings object.
		if ( 'undefined' !== typeof CommentpressSettings ) {
			me.wp_adminbar = CommentpressSettings.cp_wp_adminbar;
		}

	};

	/**
	 * Setter for WordPress adminbar.
	 *
	 * @since 3.8
	 */
	this.set_wp_adminbar = function( val ) {
		me.wp_adminbar = val;
	};

	/**
	 * Getter for WordPress adminbar.
	 *
	 * @since 3.8
	 */
	this.get_wp_adminbar = function() {
		return me.wp_adminbar;
	};



	// Init WordPress adminbar height.
	this.wp_adminbar_height = 0;

	/**
	 * Init for WordPress adminbar height.
	 *
	 * @since 3.8
	 */
	this.init_wp_adminbar_height = function( val ) {

		// Get initial value from settings object.
		if ( 'undefined' !== typeof CommentpressSettings ) {
			me.wp_adminbar_height = parseInt( CommentpressSettings.cp_wp_adminbar_height );
		}

		// Support for legacy BuddyPress bar.
		if ( me.get_bp_adminbar() == 'y' ) {

			// Amend to height of BuddyPress bar.
			me.wp_adminbar_height = 25;

			// Act as if admin bar were there.
			me.set_wp_adminbar( 'y' );

		}

	};

	/**
	 * Setter for WordPress adminbar height.
	 *
	 * @since 3.8
	 */
	this.set_wp_adminbar_height = function( val ) {
		me.wp_adminbar_height = val;
	};

	/**
	 * Getter for WordPress adminbar height.
	 *
	 * @since 3.8
	 */
	this.get_wp_adminbar_height = function() {
		return me.wp_adminbar_height;
	};



	// Init WordPress adminbar expanded height
	this.wp_adminbar_expanded = 0;

	/**
	 * Init for WordPress adminbar expanded.
	 *
	 * @since 3.8
	 */
	this.init_wp_adminbar_expanded = function( val ) {

		// Get initial value from settings object.
		if ( 'undefined' !== typeof CommentpressSettings ) {
			me.wp_adminbar_expanded = parseInt( CommentpressSettings.cp_wp_adminbar_expanded );
		}

	};

	/**
	 * Setter for WordPress adminbar expanded.
	 *
	 * @since 3.8
	 */
	this.set_wp_adminbar_expanded = function( val ) {
		me.wp_adminbar_expanded = val;
	};

	/**
	 * Getter for WordPress adminbar expanded.
	 *
	 * @since 3.8
	 */
	this.get_wp_adminbar_expanded = function() {
		return me.wp_adminbar_expanded;
	};



}; // End CommentPress Core settings DOM class.



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress Core page settings class.
 *
 * @since 3.8
 */
CommentPress.settings.page = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress Core settings page.
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



	// Init highlight
	this.highlight = false;

	/**
	 * Toggle for page highlight.
	 *
	 * @since 3.8
	 */
	this.toggle_highlight = function() {
		me.highlight = !me.highlight;
	};

	/**
	 * Setter for page highlight.
	 *
	 * @since 3.8
	 */
	this.set_highlight = function( val ) {
		me.highlight = val;
	};

	/**
	 * Getter for page highlight.
	 *
	 * @since 3.8
	 */
	this.get_highlight = function() {
		return me.highlight;
	};

}; // End CommentPress Core page settings class.



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress Core textblock class.
 *
 * @since 3.8
 */
CommentPress.settings.textblock = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress Core settings page.
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



	// Init textblock scroll target.
	this.scroll_target = 'para_heading';

	/**
	 * Setter for textblock scroll target.
	 *
	 * @since 3.8
	 */
	this.set_scroll_target = function( scroll_target ) {
		me.scroll_target = scroll_target;
	};

	/**
	 * Getter for textblock scroll target.
	 *
	 * @since 3.8
	 */
	this.get_scroll_target = function() {
		return me.scroll_target;
	};



	// Init textblock "permalink shown in location bar" flag.
	this.permalink_shown = false;

	/**
	 * Setter for textblock "permalink shown in location bar" flag.
	 *
	 * @since 3.8
	 */
	this.set_permalink_shown = function( permalink_shown ) {
		me.permalink_shown = permalink_shown;
	};

	/**
	 * Getter for textblock "permalink shown in location bar" flag.
	 *
	 * @since 3.8
	 */
	this.get_permalink_shown = function() {
		return me.permalink_shown;
	};

}; // End CommentPress Core textblock class.



/* -------------------------------------------------------------------------- */



/**
 * Create common sub-namespace.
 *
 * @since 3.8
 */
CommentPress.common = {};



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress Core common DOM class.
 *
 * @since 3.8
 */
CommentPress.common.DOM = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress Core settings DOM.
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
	 * Set the URL to a given link.
	 *
	 * @since 3.8
	 *
	 * @param str url The URL to show in the location bar.
	 */
	this.location_set = function( url ) {

		// Do we have replaceState?
		if ( window.history && window.history.replaceState ) {

			// Replace window state.
			window.history.replaceState( {} , '', url );

		}

	};



	/**
	 * Reset the URL to the page permalink.
	 *
	 * @since 3.8
	 */
	this.location_reset = function() {

		// Do we have replaceState?
		if ( window.history && window.history.replaceState ) {

			// Replace window state with original.
			window.history.replaceState( {} , '', CommentPress.settings.DOM.get_permalink() );

		}

	};



}; // End CommentPress Core common DOM class.



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress Core setup navigation column class.
 *
 * @since 3.8
 */
CommentPress.common.navigation = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress Core setup navigation column.
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

		// Column headings.
		me.headings();

		// Menu behaviour.
		me.menu();

	};



	/**
	 * Set up "Contents" column headings.
	 *
	 * @since 3.8
	 *
	 * @return false
	 */
	this.headings = function() {

		// Set pointer.
		$('h3.activity_heading').css( 'cursor', 'pointer' );

		/**
		 * Activity column headings click.
		 *
		 * @since 3.8
		 */
		$('#toc_sidebar').on( 'click', 'h3.activity_heading', function( event ) {

			// Define vars.
			var para_wrapper;

			// Override event.
			event.preventDefault();

			// Get para wrapper.
			para_wrapper = $(this).next('div.paragraph_wrapper');

			// Set width to prevent rendering error.
			para_wrapper.css( 'width', $(this).parent().css( 'width' ) );

			// Toggle next paragraph_wrapper.
			para_wrapper.slideToggle( 'slow', function() {

				// When finished, reset width to auto.
				para_wrapper.css( 'width', 'auto' );

			} );

		});

	};



	/**
	 * Set up "Contents" column menu behaviour.
	 *
	 * @since 3.8
	 */
	this.menu = function() {

		/**
		 * Chapter page headings click.
		 *
		 * @since 3.8
		 */
		$('#toc_sidebar').on( 'click', 'ul#toc_list li a', function( event ) {

			// Are our chapters pages?
			if ( cp_toc_chapter_is_page == '0' ) {

				// Define vars.
				var myArr;

				// No, find child lists of the enclosing <li>.
				myArr = $(this).parent().find('ul');

				// Do we have a child list?
				if( myArr.length > 0 ) {

					// Are subpages to be shown?
					if ( cp_show_subpages == '0' ) {

						// Toggle next list.
						$(this).next('ul').slideToggle();

					}

					// Override event.
					event.preventDefault();

				}

			}

		});

	};

}; // End CommentPress Core setup navigation column class.



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress Core setup content class.
 *
 * @since 3.8
 */
CommentPress.common.content = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress Core setup content.
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

		// Generic links.
		me.generic_links();

		// Title.
		me.title_links();

		// Textblocks.
		me.textblocks();

		// Textblock paragraph markers.
		me.para_markers();

		// Show textblock permalink in location bar.
		me.textblock_permalink_show();

		// Textblock comment icons.
		me.comment_icons();

		// Internal links.
		me.links_in_textblocks();

		// Footnotes.
		me.footnotes_compatibility();

	};



	/**
	 * Set up actions on generic linkss in textblocks.
	 *
	 * @since 3.8
	 */
	this.generic_links = function() {

		/**
		 * Clicking on generic links in textblocks.
		 *
		 * We don't want the event to bubble on links that are not CommentPress-
		 * specific, causing the columns to animate. Most generic links point to
		 * external pages and there's a class available for internal links.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'click', '.textblock a:not([class])', function( event ) {

			// Prevent bubbling.
			event.stopPropagation();

		});

	};



	/**
	 * Set up actions on the title.
	 *
	 * @since 3.8
	 */
	this.title_links = function() {

		/**
		 * Clicking on the page/post title.
		 *
		 * @since 3.8
		 */
		$('.single #container, .page #container').on( 'click', '.post_title a', function( event ) {

			// Override event.
			event.preventDefault();

			// Get text signature.
			var text_sig = '';

			// Set target to para heading.
			CommentPress.settings.textblock.set_scroll_target( 'para_heading' );

			// Broadcast action - allows scroll target to be overridden.
			$(document).trigger( 'commentpress-post-title-pre-align' );

			// Pass scroll target to function.
			CommentPress.theme.viewport.align_content( text_sig, CommentPress.settings.textblock.get_scroll_target() );

			// Broadcast action - allows scroll target to be reset.
			$(document).trigger( 'commentpress-post-title-clicked' );

		});

	};



	/**
	 * Set up actions on the textblocks.
	 *
	 * @since 3.8
	 */
	this.textblocks = function() {

		// If mobile, we don't hide textblock meta.
		if ( cp_is_mobile == '0' ) {

			// Have we explicitly hidden textblock meta?
			if ( cp_textblock_meta == '0' ) {

				/**
				 * Add a class to the textblock when mouse is over it.
				 *
				 * @since 3.8
				 */
				$('#container').on( 'mouseover', '.textblock', function( event ) {
					$(this).addClass('textblock-in');
				});

				/**
				 * Remove class from the textblock when mouse moves out of it.
				 *
				 * @since 3.8
				 */
				$('#container').on( 'mouseout', '.textblock', function( event ) {
					$(this).removeClass('textblock-in');
				});

			}

		}

		/**
		 * Clicking on the textblock.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'click', '.textblock', function( event ) {

			// Define vars.
			var text_sig;

			// Get text signature.
			text_sig = $(this).prop('id');

			// Remove leading #
			text_sig = text_sig.split('textblock-')[1];

			// Set target to para heading.
			CommentPress.settings.textblock.set_scroll_target( 'para_heading' );

			// Broadcast action - allows scroll target to be reset.
			$(document).trigger( 'commentpress-post-title-clicked' );

			// Broadcast action - allows scroll target to be overridden.
			$(document).trigger( 'commentpress-textblock-pre-align' );

			// Pass scroll target to function.
			CommentPress.theme.viewport.align_content( text_sig, CommentPress.settings.textblock.get_scroll_target() );

			// Broadcast action - allows scroll target to be reset.
			$(document).trigger( 'commentpress-textblock-clicked' );

		});

	};



	/**
	 * Set up actions on the "paragraph" icons to the left of a textblock.
	 *
	 * @since 3.8
	 */
	this.para_markers = function() {

		/**
		 * Clicking on the paragraph.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'click', 'span.para_marker a', function( event ) {

			// Override event.
			event.preventDefault();

			// Broadcast action.
			$(document).trigger( 'commentpress-paramarker-clicked' );

		});

		/**
		 * Rolling onto the paragraph icon.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'mouseenter', 'span.para_marker a', function( event ) {

			// Define vars.
			var target;

			// Get target item.
			target = $(this).parent().next().children('.comment_count');

			target.addClass( 'js-hover' );

		});

		/**
		 * Rolling off the paragraph icon.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'mouseleave', 'span.para_marker a', function( event ) {

			// Define vars.
			var target;

			// Get target item.
			target = $(this).parent().next().children('.comment_count');

			target.removeClass( 'js-hover' );

		});

	};



	/**
	 * Show the paragraph permalink in the browser's location bar.
	 *
	 * @since 3.8
	 */
	this.textblock_permalink_show = function() {

		/**
		 * Copy icon tooltip.
		 *
		 * @since 3.8
		 */
		$('.textblock_permalink').tooltip({

			// Positional behaviour.
			position: {

				// Basics.
				my: "left-30 bottom-20",
				at: "left top",

				// Configure arrow.
				using: function( position, feedback ) {
					$(this).css( position );
					$('<div>')
					.addClass( "arrow" )
					.addClass( feedback.vertical )
					.addClass( feedback.horizontal )
					.appendTo( this );
				}

			}
		});

		/**
		 * Click on paragraph permalink to reveal it in the location bar.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'click', '.textblock_permalink', function( event ) {

			// Define vars.
			var url;

			// Get selection.
			url = $(this).attr( 'href' );

			// Did we get one?
			if ( url ) {

				// Replace window state with para permalink.
				CommentPress.common.DOM.location_set( url );

				// Set flag.
				CommentPress.settings.textblock.set_permalink_shown( true );

				// Attach a reset handler to the document body.
				$(document).bind( 'click', me.textblock_permalink_handler );

			}

		});

		/**
		 * Hook into CommentPress Core clicks on items whose events do not bubble.
		 *
		 * We need to receive callbacks from these clicks to clear the location bar.
		 *
		 * @since 3.8
		 *
		 * @param object event The event (unused).
		 */
		$(document).on(
			'commentpress-textblock-click ' +
			'commentpress-comment-block-permalink-clicked ' +
			'commentpress-commenticonbox-clicked ' +
			'commentpress-link-in-textblock-clicked',
			function( event ) {

				// Test flag.
				if ( CommentPress.settings.textblock.get_permalink_shown() ) {

					// Unbind document click handler.
					$(document).unbind( 'click', me.textblock_permalink_handler );

					// Set flag.
					CommentPress.settings.textblock.set_permalink_shown( false );

					// Replace window state with original.
					CommentPress.common.DOM.location_reset();

				}

			} // End function.
		);

	};



	/**
	 * Reset the URL to the page permalink.
	 *
	 * @since 3.8
	 */
	this.textblock_permalink_handler = function( event ) {

		// If the event target is not a para permalink
		if ( !$(event.target).closest( '.textblock_permalink' ).length ) {

			// Unbind document click handler.
			$(document).unbind( 'click', me.textblock_permalink_handler );

			// Set flag.
			CommentPress.settings.textblock.set_permalink_shown( false );

			// Replace window state with original.
			CommentPress.common.DOM.location_reset();

		}

	};



	/**
	 * Set up clicks on comment icons attached to textblocks in post/page.
	 *
	 * @since 3.8
	 */
	this.comment_icons = function() {

		/**
		 * Clicking on the little comment icon.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'click', '.commenticonbox', function( event ) {

			// Define vars.
			var text_sig;

			// Override event.
			event.preventDefault();

			// Prevent bubbling.
			event.stopPropagation();

			// Get text signature.
			text_sig = $(this).children('a.para_permalink').prop('href').split('#')[1];

			// Set target to comment form.
			CommentPress.settings.textblock.set_scroll_target( 'commentform' );

			// Broadcast action - allows scroll target to be overridden.
			$(document).trigger( 'commentpress-commenticonbox-pre-align' );

			// Pass scroll target to function.
			CommentPress.theme.viewport.align_content( text_sig, CommentPress.settings.textblock.get_scroll_target() );

			// Broadcast action - allows scroll target to be reset.
			$(document).trigger( 'commentpress-commenticonbox-clicked' );

		});

		/**
		 * Clicking on the little comment icon.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'click', 'a.para_permalink', function( event ) {

			// Override event.
			event.preventDefault();

		});

		/**
		 * Rolling onto the little comment icon.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'mouseenter', 'a.para_permalink', function( event ) {

			// Define vars.
			var text_sig;

			// Get text signature.
			text_sig = $(this).prop('href').split('#')[1];

			$('span.para_marker a#' + text_sig).addClass( 'js-hover' );

		});

		/**
		 * Rolling off the little comment icon.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'mouseleave', 'a.para_permalink', function( event ) {

			// Define vars.
			var text_sig;

			// Get text signature.
			text_sig = $(this).prop('href').split('#')[1];

			$('span.para_marker a#' + text_sig).removeClass( 'js-hover' );

		});

	};



	/**
	 * Set up paragraph links: cp_para_link is a class writers can use
	 * in their markup to create nicely scrolling links within their pages.
	 *
	 * @since 3.8
	 */
	this.links_in_textblocks = function() {

		/**
		 * Clicking on links to paragraphs.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'click', 'a.cp_para_link', function( event ) {

			// Define vars.
			var text_sig;

			// Override event.
			event.preventDefault();

			// Prevent bubbling.
			event.stopPropagation();

			// Get text signature.
			text_sig = $(this).prop('href').split('#')[1];

			// Set target to comment form.
			CommentPress.settings.textblock.set_scroll_target( 'para_heading' );

			// Broadcast action - allows scroll target to be overridden.
			$(document).trigger( 'commentpress-link-in-textblock-pre-align' );

			// Pass scroll target to function.
			CommentPress.theme.viewport.align_content( text_sig, CommentPress.settings.textblock.get_scroll_target() );

			// Broadcast action - allows scroll target to be reset.
			$(document).trigger( 'commentpress-link-in-textblock-clicked' );

		});

	};



	/**
	 * Set up footnote links for various plugins.
	 *
	 * @since 3.8
	 */
	this.footnotes_compatibility = function() {

		/**
		 * ---------------------------------------------------------------------
		 * Back links
		 * ---------------------------------------------------------------------
		 */

		/**
		 * Clicking on reverse links in FD-Footnotes and WP_Footnotes.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'click', 'span.footnotereverse a, a.footnote-back-link', function( event ) {

			// Define vars.
			var target;

			// Override event.
			event.preventDefault();

			// Prevent bubbling.
			event.stopPropagation();

			// Get target.
			target = $(this).prop('href').split('#')[1];

			// Use function for offset.
			me.quick_scroll_page( '#' + target, 100 );

		});

		/**
		 * Clicking on reverse links in Simple Footnotes plugin.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'click', '.simple-footnotes ol li > a', function( event ) {

			// Define vars.
			var target;

			// Get target.
			target = $(this).prop('href');

			// Is it a backlink?
			if ( target.match('#return-note-' ) ) {

				// Override event.
				event.preventDefault();

				// Prevent bubbling.
				event.stopPropagation();

				// Remove URL.
				target = target.split('#')[1];

				// Use function for offset.
				me.quick_scroll_page( '#' + target, 100 );

			}

		});

		/**
		 * ---------------------------------------------------------------------
		 * Footnote links
		 * ---------------------------------------------------------------------
		 */

		/**
		 * Clicking on footnote links in FD-Footnotes, WP-Footnotes, Simple
		 * Footnotes and ZotPress.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'click', 'a.simple-footnote, sup.footnote a, sup a.footnote-identifier-link, a.zp-ZotpressInText', function( event ) {

			// Define vars.
			var target;

			// Override event.
			event.preventDefault();

			// Prevent bubbling.
			event.stopPropagation();

			// Get target.
			target = $(this).prop('href').split('#')[1];

			// Use function for offset.
			me.quick_scroll_page( '#' + target, 100 );

		});

	};



	/**
	 * Scroll page to target.
	 *
	 * @since 3.8
	 *
	 * @param object target The object to scroll to.
	 */
	this.scroll_page = function( target ) {

		// Bail if we didn't get a valid target.
		if ( 'undefined' === typeof target ) { return; }

		// Only scroll if not mobile - but allow tablets.
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

			// Scroll page.
			$(window).stop(true).scrollTo(
				target,
				{
					duration: (cp_scroll_speed * 1.5),
					axis: 'y',
					offset: CommentPress.theme.header.get_offset()
				}
			);

		}

	}



	/**
	 * Scroll page to target with passed duration param.
	 *
	 * @since 3.8
	 *
	 * @param object target The object to scroll to.
	 * @param integer duration The duration of the scroll.
	 */
	this.quick_scroll_page = function( target, duration ) {

		// Bail if we didn't get a valid target.
		if ( 'undefined' === typeof target ) { return; }

		// Only scroll if not mobile - but allow tablets.
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

			// Scroll page.
			$(window).stop(true).scrollTo(
				target,
				{
					duration: (duration * 1.5),
					axis: 'y',
					offset: CommentPress.theme.header.get_offset()
				}
			);

		}

	}



	/**
	 * Scroll to textblock.
	 *
	 * @since 3.8
	 *
	 * @param string text_sig The text signature to scroll to.
	 */
	this.scroll_page_to_textblock = function( text_sig ) {

		// Define vars.
		var textblock;

		// If not the whole page.
		if( text_sig !== '' ) {

			// Get text block.
			textblock = $('#textblock-' + text_sig);

			// Highlight this paragraph.
			$.highlight_para( textblock );

			// Scroll page.
			me.scroll_page( textblock );

		} else {

			// Only scroll if page is not highlighted.
			if ( !CommentPress.settings.page.toggle_highlight() ) {

				// Scroll to top.
				CommentPress.theme.viewport.scroll_to_top( 0, cp_scroll_speed );

			}

			// Toggle page highlight flag.
			CommentPress.settings.page.toggle_highlight();

		}

	}



	/**
	 * Page load prodecure for special pages with comments in content.
	 *
	 * @since 3.8
	 */
	this.on_load_scroll_to_comment = function() {

		// Define vars.
		var url, comment_id, comment;

		// If there is an anchor in the URL
		url = document.location.toString();

		// Do we have a comment permalink?
		if ( url.match( '#comment-' ) ) {

			// Get comment ID.
			comment_id = url.split('#comment-')[1];

			// Get comment in DOM.
			comment = $( '#comment-' + comment_id );

			// Did we get one?
			if ( comment.length ) {

				// Only scroll if not mobile - but allow tablets.
				if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

					// Scroll to new comment.
					$(window).stop(true).scrollTo(
						comment,
						{
							duration: cp_scroll_speed,
							axis:'y',
							offset: CommentPress.theme.header.get_offset()
						}
					);

				}

			}

			// Set location to page/post permalink.
			CommentPress.common.DOM.location_reset();

			// --<
			return;

		}

		// Do we have a link to the comment form?
		if ( url.match( '#respond' ) ) {

			// Get comment form in DOM.
			comment_form = $('#respond');

			// Did we get it?
			if ( comment_form.length ) {

				// Only scroll if not mobile - but allow tablets.
				if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

					// Scroll to new comment.
					$(window).stop(true).scrollTo(
						comment_form,
						{
							duration: cp_scroll_speed,
							axis:'y',
							offset: CommentPress.theme.header.get_offset()
						}
					);

				}

			}

			// Set location to page/post permalink.
			CommentPress.common.DOM.location_reset();

		}

	}



	/**
	 * Workflow tabs.
	 *
	 * @since 3.8
	 *
	 * @param str content_min_height The min-height CSS declaration.
	 * @param str content_padding_bottom The content wrapper padding-bottom CSS declaration.
	 */
	this.workflow_tabs = function( content_min_height, content_padding_bottom ) {

		// Hide workflow content.
		$('#literal .post').css( 'display', 'none' );
		$('#original .post').css( 'display', 'none' );

		/**
		 * Clicking on the workflow tabs.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'click', '#content-tabs li h2 a', function( event ) {

			// Define vars.
			var target_id;

			// Override event.
			event.preventDefault();

			// Hide others and show corresponding item.

			// Get href.
			target_id = this.href.split('#')[1];

			// Hide all.
			$('.post').css( 'display', 'none' );

			// Remove content min-height.
			$('.workflow-wrapper').css( 'min-height', '0' );

			// Remove bottom padding.
			$('.workflow-wrapper').css( 'padding-bottom', '0' );

			// Set min-height of target.
			$('#' + target_id + '.workflow-wrapper').css( 'min-height', content_min_height );

			// Set padding-bottom of target.
			$('#' + target_id + '.workflow-wrapper').css( 'padding-bottom', content_padding_bottom );

			// Show it.
			$('#' + target_id + ' .post').css( 'display', 'block' );

			// Amend CSS of list items to mimic tabs.
			$('#content-tabs li').removeClass( 'default-content-tab' );
			$(this).parent().parent().addClass( 'default-content-tab' );

		});

	};

}; // End CommentPress Core setup content class.



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress Core setup comments column class.
 *
 * @since 3.8
 */
CommentPress.common.comments = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress Core setup comments column.
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

		// Column header.
		me.header();

		// Minimiser button.
		me.minimiser();

		// Comment block permalinks.
		me.comment_block_permalinks();

		// Comment permalinks.
		me.comment_permalinks();

		// Show comment permalink in location bar.
		me.comment_permalink_show();

		// Comment rollovers (disabled because only the modern theme uses this)
		//me.comment_rollovers();

	};



	/**
	 * Set up "Comments" tab header.
	 *
	 * @since 3.8
	 */
	this.header = function() {

		/**
		 * Clicking on the Comments Header.
		 *
		 * @since 3.8
		 */
		$('#sidebar').on( 'click', '#comments_header h2 a', function( event ) {

			// Override event.
			event.preventDefault();

			// Activate it - this will become a theme method.
			CommentPress.theme.sidebars.activate_sidebar( 'comments' );

		});

	};



	/**
	 * Set up "Comments" tab minimise button.
	 *
	 * @since 3.8
	 */
	this.minimiser = function() {

		/**
		 * Clicking on the minimise comments icon.
		 *
		 * @since 3.8
		 */
		$('#sidebar').on( 'click', '#cp_minimise_all_comments', function( event ) {

			// Override event.
			event.preventDefault();

			// Slide all paragraph comment wrappers up.
			$('#comments_sidebar div.paragraph_wrapper').slideUp();

			// Unhighlight paragraphs.
			$.unhighlight_para();

		});

	};



	/**
	 * Set up "Comments" tab "X Comments on Paragraph Y" links.
	 *
	 * These links are also permalinks to the "comment block" - i.e. the section
	 * of the Comments column that holds the comments on a particular textblock.
	 *
	 * @since 3.8
	 */
	this.comment_block_permalinks = function() {

		// Only on normal CommentPress pages.
		if ( cp_special_page == '1' ) { return; }

		// Set pointer.
		$('a.comment_block_permalink').css( 'cursor', 'pointer' );

		/**
		 * Clicks on "Comments" tab "X Comments on Paragraph Y" links.
		 *
		 * @since 3.8
		 *
		 * @param object event The clicked object.
		 */
		$('#comments_sidebar').on( 'click', 'a.comment_block_permalink', function( event ) {

			// Define vars.
			var text_sig, para_wrapper, comment_list, opening, visible, textblock,
				post_id, para_id, para_num, has_form;

			// Override event.
			event.preventDefault();

			// Set target to comment form.
			CommentPress.settings.textblock.set_scroll_target( 'para_heading' );

			// Broadcast action - allows scroll target to be overridden.
			$(document).trigger( 'commentpress-comment-block-permalink-pre-align' );

			// Did we get an override?
			if ( 'none' == CommentPress.settings.textblock.get_scroll_target() ) {

				// Broadcast action - allows scroll target to be reset.
				$(document).trigger( 'commentpress-comment-block-permalink-clicked' );

				// Bail.
				return;

			}

			// Get text_sig.
			text_sig = $(this).parent().prop( 'id' ).split('para_heading-')[1];

			// Get para wrapper.
			para_wrapper = $(this).parent().next('div.paragraph_wrapper');

			// Get comment list.
			comment_list = $( '#para_wrapper-' + text_sig ).find('ol.commentlist' );

			// Init.
			opening = false;

			// Get visibility.
			visible = para_wrapper.css('display');

			// Override.
			if ( visible == 'none' ) { opening = true; }

			// Did we get one at all?
			if ( 'undefined' !== typeof text_sig ) {

				// If not the whole page or pings.
				if( text_sig !== '' && text_sig != 'pingbacksandtrackbacks' ) {

					// Get text block.
					textblock = $('#textblock-' + text_sig);

					// Only if opening.
					if ( opening ) {

						// Unhighlight paragraphs.
						$.unhighlight_para();

						// Highlight this paragraph.
						$.highlight_para( textblock );

						// Scroll page.
						CommentPress.common.content.scroll_page( textblock );

					} else {

						// If encouraging commenting.
						if ( cp_promote_reading == '0' ) {

							// Closing with a comment form.
							if ( $( '#para_wrapper-' + text_sig ).find('#respond' )[0] ) {

								// Unhighlight paragraphs.
								$.unhighlight_para();

							} else {

								// If we have no comments, always highlight.
								if ( !comment_list[0] ) {

									// Unhighlight paragraphs.
									$.unhighlight_para();

									// Highlight this paragraph.
									$.highlight_para( textblock );

									// Scroll page.
									CommentPress.common.content.scroll_page( textblock );

								}

							}

						} else {

							// If ours is highlighted.
							if ( $.is_highlighted( textblock ) ) {

								// Unhighlight paragraphs.
								$.unhighlight_para();

							}

						}

					}

				} else {

					// Unhighlight paragraphs.
					$.unhighlight_para();

					// Only scroll if not pings.
					if ( text_sig != 'pingbacksandtrackbacks' ) {

						// Scroll to top.
						CommentPress.theme.viewport.scroll_to_top( 0, cp_scroll_speed );

						// Toggle page highlight flag.
						CommentPress.settings.page.toggle_highlight();

					}

				}

			} // End defined check.

			// If encouraging commenting.
			if ( cp_promote_reading == '0' && text_sig != 'pingbacksandtrackbacks' ) {

				// Are comments open?
				if ( cp_comments_open == 'y' ) {

					// Get comment post ID.
					post_id = $('#comment_post_ID').prop('value');
					para_id = $('#para_wrapper-' + text_sig + ' .reply_to_para').prop('id');
					para_num = para_id.split('-')[1];

					// Do we have the comment form?
					has_form = $( '#para_wrapper-' + text_sig ).find( '#respond' )[0];

					// If we have a comment list.
					if ( comment_list.length > 0 && comment_list[0] ) {

						// Are we closing with no reply form?
						if ( !opening && !has_form ) {

							// Skip moving form.

						} else {

							// Move form to para.
							addComment.moveFormToPara( para_num, text_sig, post_id );

						}

					} else {

						// If we have no respond.
						if ( !has_form ) {

							para_wrapper.css('display','none');
							opening = true;

						}

						// Move form to para.
						addComment.moveFormToPara( para_num, text_sig, post_id );

					}

				}

			}

			// Toggle next paragraph_wrapper.
			para_wrapper.slideToggle( 'slow', function() {

				// Only scroll if opening.
				if ( opening ) {

					// Scroll comments.
					me.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

				}

			});

			// Broadcast action.
			$(document).trigger( 'commentpress-comment-block-permalink-clicked' );

		});

	};



	/**
	 * Set up actions on the Comment permalinks.
	 *
	 * @since 3.8
	 */
	this.comment_permalinks = function() {

		/**
		 * Clicking on comment permalinks on the General Comments page.
		 *
		 * @since 3.8
		 */
		$('#wrapper').on( 'click', '.comment_permalink', function( event ) {

			// Bail if not special page.
			if ( cp_special_page != '1' ) { return; }

			// Define vars.
			var comment_id, header_offset, text_sig;

			// Override event.
			event.preventDefault();

			// Get comment ID.
			comment_id = this.href.split('#')[1];

			// Get offset.
			header_offset = CommentPress.theme.header.get_offset();

			// Scroll to comment.
			$(window).stop(true).scrollTo(
				$('#'+comment_id),
				{
					duration: cp_scroll_speed,
					axis:'y',
					offset: header_offset,
					onAfter: function() {

						// Broadcast.
						$(document).trigger( 'commentpress-comments-in-page-scrolled' );

					}
				}
			);

		});

		/**
		 * Clicking on comment permalinks in the Comments column.
		 *
		 * @since 3.8
		 */
		$('#comments_sidebar').on( 'click', '.comment_permalink', function( event ) {

			// Define vars.
			var comment_id, header_offset, text_sig;

			// Override event.
			event.preventDefault();

			// Get comment ID.
			comment_id = this.href.split('#')[1];

			// Clear other highlights.
			$.unhighlight_para();

			// Get text sig.
			text_sig = $.get_text_sig_by_comment_id( '#' + comment_id );

			// If not a pingback.
			if ( text_sig != 'pingbacksandtrackbacks' ) {

				// Scroll page to it.
				CommentPress.common.content.scroll_page_to_textblock( text_sig );

			}

			// Scroll comments.
			me.scroll_comments( $('#'+comment_id), cp_scroll_speed );

		});

	};



	/**
	 * Show the comment permalink in the browser's location bar.
	 *
	 * @since 3.8
	 */
	this.comment_permalink_show = function() {

		/**
		 * Copy icon tooltip.
		 *
		 * @since 3.8
		 */
		$('.comment_permalink').tooltip({

			// Positional behaviour.
			position: {

				// Basics.
				my: "left bottom-20",
				at: "left top",

				// Configure arrow.
				using: function( position, feedback ) {
					$( this ).css( position );
					$( "<div>" )
					.addClass( "arrow" )
					.addClass( feedback.vertical )
					.addClass( feedback.horizontal )
					.appendTo( this );
				}

			}
		});

		/**
		 * Click on comment permalink to reveal it in the location bar.
		 *
		 * @since 3.8
		 */
		$('#comments_sidebar, #wrapper').on( 'click', '.comment_permalink', function( event ) {

			// Define vars.
			var url, hide;

			// Get selection.
			url = $(this).attr( 'href' );

			// Did we get one?
			if ( url ) {

				// Close tooltip immediately.
				hide = $('.comment_permalink').tooltip( 'option', 'hide' );
				$('.comment_permalink').tooltip( 'option', 'hide', false );
				$('.comment_permalink').tooltip( 'close' );
				$('.comment_permalink').tooltip( 'option', 'hide', hide );

				// Replace window state with comment permalink.
				CommentPress.common.DOM.location_set( url );

				// Attach a reset handler to the document body.
				$(document).bind( 'click', me.comment_permalink_handler );

			}

		});

	};



	/**
	 * Reset the URL to the page permalink.
	 *
	 * @since 3.8
	 */
	this.comment_permalink_handler = function( event ) {

		// If the event target is not a comment permalink.
		if ( !$(event.target).closest( '.comment_permalink' ).length ) {

			// Unbind document click handler.
			$(document).unbind( 'click', me.comment_permalink_handler );

			// Replace window state with original.
			CommentPress.common.DOM.location_reset();

		}

	};



	/**
	 * Handle comment "rollovers".
	 *
	 * @since 3.8
	 */
	this.comment_rollovers = function() {

		/**
		 * Add a class when rolling onto the comment.
		 */
		$('#comments_sidebar').on( 'mouseenter', '.comment-wrapper', function( event ) {
			$(this).addClass( 'background-highlight' );
		});

		/**
		 * Remove the class when rolling off the comment.
		 */
		$('#comments_sidebar').on( 'mouseleave', '.comment-wrapper', function( event ) {
			$(this).removeClass( 'background-highlight' );
		});

	};



	/**
	 * Highlight the comment.
	 *
	 * @since 3.8
	 *
	 * @param object comment The $ comment object.
	 */
	this.highlight = function( comment ) {

		// Add notransition class.
		comment.addClass( 'notransition' );

		// Remove existing classes.
		if ( comment.hasClass( 'comment-fade' ) ) {
			comment.removeClass( 'comment-fade' );
		}
		if ( comment.hasClass( 'comment-highlighted' ) ) {
			comment.removeClass( 'comment-highlighted' );
		}

		// Highlight.
		comment.addClass( 'comment-highlighted' );

		// Remove notransition class.
		comment.removeClass( 'notransition' );

		// Trigger reflow.
		comment.height();

		// Animate to existing bg-color from CSS file.
		comment.addClass( 'comment-fade' );

	}



	/**
	 * Scroll comments to target.
	 *
	 * @since 3.8
	 *
	 * @param object target The target to scroll to.
	 * @param integer speed The duration of the scroll.
	 * @param string flash Whether or not to "flash" the comment.
	 */
	this.scroll_comments = function( target, speed, flash ) {

		// Preserve compatibility with older calls.
		switch(arguments.length) {
			case 2: flash = 'noflash'; break;
			case 3: break;
			default: throw new Error('illegal argument count');
		}

		// Only scroll if not mobile - but allow tablets.
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

			// Either flash at the end or not..
			if ( flash == 'flash' ) {

				// Add highlight class.
				//$( '#li-comment-' + target.prop( 'id' ).split( '-' )[1] ).addClass( 'flash-comment' );

				// Scroll to new comment.
				$('#comments_sidebar .sidebar_contents_wrapper').stop(true).scrollTo(
					target,
					{
						duration: speed,
						axis: 'y',
						onAfter: function() {

							// Highlight header.
							me.highlight( target );

							// Broadcast.
							$(document).trigger( 'commentpress-comments-scrolled' );

						}
					}
				);

			} else {

				// Scroll comment area to para heading.
				$('#comments_sidebar .sidebar_contents_wrapper').stop(true).scrollTo(
					target,
					{
						duration: speed,
						onAfter: function() {

							// Broadcast.
							$(document).trigger( 'commentpress-comments-scrolled' );

						}
					}
				);

			}

		}

	};

}; // End CommentPress Core setup comments column class.



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress Core setup activity column class.
 *
 * @since 3.8
 */
CommentPress.common.activity = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress Core setup activity column.
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

		// Column header.
		me.header();

		// Minimiser button.
		me.minimiser();

		// Column headings.
		me.headings();

		// "see in context" links.
		me.see_in_context_links();

	};



	/**
	 * Set up "Activity" tab header.
	 *
	 * @since 3.8
	 */
	this.header = function() {

		/**
		 * Clicking on the Activity Header.
		 *
		 * @since 3.8
		 *
		 * @return false
		 */
		$('#sidebar').on( 'click', '#activity_header h2 a', function( event ) {

			// Override event.
			event.preventDefault();

			// Activate it - this will become a theme method.
			CommentPress.theme.sidebars.activate_sidebar( 'activity' );

		});

	};



	/**
	 * Set up "Activity" tab minimise button.
	 *
	 * @since 3.8
	 */
	this.minimiser = function() {

		/**
		 * Clicking on the minimise activities icon.
		 *
		 * @since 3.8
		 */
		$('#sidebar').on( 'click', '#cp_minimise_all_activity', function( event ) {

			// Override event.
			event.preventDefault();

			// Slide all paragraph comment wrappers up.
			$('#activity_sidebar div.paragraph_wrapper').slideUp();

		});

	};



	/**
	 * Set up "Activity" tab headings.
	 *
	 * @since 3.8
	 */
	this.headings = function() {

		// Set pointer.
		$('h3.activity_heading').css( 'cursor', 'pointer' );

		/**
		 * Activity column headings click.
		 *
		 * @since 3.8
		 */
		$('#activity_sidebar').on( 'click', 'h3.activity_heading', function( event ) {

			// Define vars.
			var para_wrapper;

			// Override event.
			event.preventDefault();

			// Get para wrapper.
			para_wrapper = $(this).next('div.paragraph_wrapper');

			// Set width to prevent rendering error.
			para_wrapper.css( 'width', $(this).parent().css( 'width' ) );

			// Toggle next paragraph_wrapper.
			para_wrapper.slideToggle( 'slow', function() {

				// When finished, reset width to auto.
				para_wrapper.css( 'width', 'auto' );

			} );

		});

	};



	/**
	 * Set up "Activity" tab "See In Context" links.
	 *
	 * @since 3.8
	 */
	this.see_in_context_links = function() {

		// Allow links to work when not on commentable page.
		// NOTE: is this right?
		if ( cp_special_page == '1' ) { return; }

		/**
		 * Clicking on the "See In Context" links.
		 *
		 * @since 3.8
		 */
		$('#activity_sidebar').on( 'click', 'a.comment_on_post', function( event ) {

			// Define vars.
			var comment_id, comment, para_wrapper_array, item, header_offset, text_sig;

			// Override event.
			event.preventDefault();

			// Show comments sidebar.
			CommentPress.theme.sidebars.activate_sidebar( 'comments' );

			// Get comment ID.
			comment_id = this.href.split('#')[1];

			// Get comment.
			comment = $('#'+comment_id);

			// Get array of parent paragraph_wrapper divs.
			para_wrapper_array = comment
										.parents('div.paragraph_wrapper')
										.map( function () {
											return this;
										});

			// Did we get one?
			if ( para_wrapper_array.length > 0 ) {

				// Get the item.
				item = $(para_wrapper_array[0]);

				// Show block.
				item.show();

				// If special page.
				if ( cp_special_page == '1' ) {

					// Get offset.
					header_offset = CommentPress.theme.header.get_offset();

					// Scroll to comment.
					$(window).stop(true).scrollTo(
						comment,
						{
							duration: cp_scroll_speed,
							axis:'y',
							offset: header_offset
						}
					);

				} else {

					// Clear other highlights.
					$.unhighlight_para();

					// Highlight para.
					text_sig = item.prop('id').split('-')[1];

					// Scroll page to it.
					CommentPress.common.content.scroll_page_to_textblock( text_sig );

					// Add highlight class.
					//$( '#li-comment-' + comment_id ).addClass( 'flash-comment' );

					// Scroll to new comment.
					$('#comments_sidebar .sidebar_contents_wrapper').stop(true).scrollTo(
						comment,
						{
							duration: cp_scroll_speed,
							axis: 'y',
							onAfter: function() {

								// Highlight comment.
								CommentPress.common.comments.highlight( comment );

							}
						}
					);

				}

			}

		});

	};

}; // End CommentPress Core setup activity column class.



/* -------------------------------------------------------------------------- */



/**
 * Create viewport class.
 *
 * @since 3.8
 */
CommentPress.common.viewport = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress Core theme viewport.
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

}; // End CommentPress Core setup viewport class.



/* -------------------------------------------------------------------------- */



/**
 * jQuery wrapper.
 *
 * This wrapper ensures that jQuery can be addressed using the $ shorthand from
 * anywhere within the script.
 *
 * @since 3.0
 */
;( function( $ ) {



	// Our currently highlighted paragraph.
	var highlighted_para = '';

	/**
	 * Highlight the current paragraph.
	 *
	 * @since 3.0
	 */
	$.highlight_para = function( element ) {

		// Bail if we don't have a proper element.
		if ( typeof( element ) != 'object' ) { return; }

		// Amend <p> tag CSS.
		element.addClass( 'selected_para' );

	}



	/**
	 * Unhighlight all text.
	 *
	 * @since 3.0
	 */
	$.unhighlight_para = function() {

		var highlighted_paras = $('.textblock');

		// Remove class from all.
		highlighted_paras.removeClass( 'selected_para' );

	}



	/**
	 * Get the element which is currently highlighted.
	 *
	 * @since 3.0
	 *
	 * @return string highlighted_para The highlighted paragraph.
	 */
	$.get_highlighted_para = function() {

		// --<
		return highlighted_para;

	}



	/**
	 * Test if the element is currently highlighted.
	 *
	 * @since 3.0
	 *
	 * @param object element The jQuery element to test.
	 * @return boolean True if highlighted, false otherwise.
	 */
	$.is_highlighted = function( element ) {

		// Bail if we don't have a proper element.
		if ( typeof( element ) != 'object' ) { return false; }

		// Is our item already highlighted?
		if ( element.hasClass('selected_para') ) {

			// --<
			return true;

		} else {

			// --<
			return false;

		}

	}



	/**
	 * Utility replacement for PHP's in_array.
	 *
	 * @since 3.0
	 *
	 * @param mixed needle The item to search for.
	 * @param array haystack The array to search.
	 * @param boolean argStrict If true, will take variable type into account.
	 * @return boolean found True if found, false otherwise.
	 */
	$.in_array = function( needle, haystack, argStrict ) {

		// @see http://kevin.vanzonneveld.net
		// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// *     example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']);
		// *     returns 1: true

		var found = false, key, strict = !!argStrict;

		for( key in haystack ) {
			if ( (strict && haystack[key] === needle) || (!strict && haystack[key] == needle) ) {
				found = true;
				break;
			}
		}

		return found;

	}



	/**
	 * A nifty JS array utility to remove a specified value.
	 *
	 * @since 3.0
	 *
	 * @param mixed item The item to remove.
	 * @param array sourceArray The array to remove the item from.
	 * @return array sourceArray The modified array.
	 */
	$.remove_from_array = function( item, sourceArray ) {

		// Loop through the array.
		for ( var i = 0; i < sourceArray.length; i++ ) {

			// Remove our item.
			if ( item === sourceArray[i] ) {

				// Splice it at that point.
				sourceArray.splice(i, 1);

				// Kick out.
				break;

			}

		}

		// --<
		return sourceArray;

	}



	/**
	 * Utility replacement for PHP's is_object.
	 *
	 * @since 3.0
	 *
	 * @param mixed mixed_var The item to test.
	 * @return boolean True if item is object, false otherwise.
	 */
	$.is_object = function( mixed_var ) {

		// Distiguish between arrays and objects.
		if( mixed_var instanceof Array ) {

			// Is an array.
			return false;

		} else {

			// Not null and is object.
			return ( mixed_var !== null ) && ( typeof( mixed_var ) == 'object' );
		}

	}



	/**
	 * Test if a function exists without throwing a Reference Error.
	 *
	 * @since 3.0
	 *
	 * @param string function_name The name of the function.
	 * @return boolean True if the function exists, false otherwise.
	 */
	$.is_function_defined = function( function_name ) {

		// Use eval.
		if ( eval( 'typeof(' + function_name + ') == typeof(Function)' ) ) {

			// --<
			return true;

		}

		// --<
		return false;

	}



	/**
	 * Utility to strip 'px' off css values.
	 *
	 * @since 3.0
	 *
	 * @param string pix The CSS string (eg, '20px').
	 * @return int px The numeric value (eg, 20).
	 */
	$.px_to_num = function( pix ) {

		// --<
		return parseInt( pix.substring( 0, (pix.length - 2) ) );

	};



	/**
	 * Utility to return zero when css values may be NaN in IE.
	 *
	 * @since 3.0
	 *
	 * @param mixed strNum A numeric value that we want to modify.
	 * @return int The numeric value of strNum.
	 */
	$.css_to_num = function( strNum ) {

		// @see http://mattstark.blogspot.com/2009/05/javascript-jquery-plugin-to-fade.html
		if ( strNum && strNum != "" ) {

			var i = parseFloat(strNum);
			if (i.toString() == "NaN") {

				// --<
				return 0;

			} else {

				// --<
				return i;

			}

		}

		// --<
		return 0;

	}



	/**
	 * A test!
	 *
	 * @since 3.0
	 *
	 * @todo Remove
	 *
	 * @param string message The message to show.
	 */
	$.frivolous = function( message ) {

		// Do a simple alert.
		alert( message );

	}



	/**
	 * Get currently highlighted menu item ID.
	 *
	 * @since 3.0
	 *
	 * @return string current_menu_item The numeric ID of the menu item.
	 */
	$.get_current_menu_item_id = function() {

		// Declare vars.
		var current_menu_item = 0,
			current_menu_obj, current_item_id,
			current_item_classes, current_item_class;

		// Get highlighted menu item object.
		current_menu_obj = $('.current_page_item');

		// Did we get one?
		if ( current_menu_obj.length > 0 ) {

			// Get ID, if present.
			current_item_id = current_menu_obj.prop('id');

			// If we do have an ID.
			if ( current_item_id.length > 0 ) {

				// It's a WP custom menu.
				current_menu_item = current_item_id.split('-')[2];

			} else {

				// It's a WP page menu.
				current_item_class = current_menu_obj.prop('class');

				// Get classes.
				current_item_classes = current_item_class.split(' ');

				// Loop to find the one we want.
				for (var i = 0, item; item = current_item_classes[i++];) {
					if ( item.match( 'page-item-' ) ) {
						current_menu_item = item.split('-')[2];
						break;
					}
				}

			}

		}

		// --<
		return current_menu_item;

	}



	/**
	 * Get text signature by comment id.
	 *
	 * @since 3.0
	 *
	 * @param object cid The CSS ID of the comment.
	 * @return string text_sig The text signature.
	 */
	$.get_text_sig_by_comment_id = function( cid ) {

		// Define vars.
		var comment_id, para_wrapper_array, text_sig, item;

		// Init.
		text_sig = '';

		// Are we passing the full ID?
		if ( cid.match( '#comment-' ) ) {

			// Get comment ID.
			comment_id = parseInt( cid.split('#comment-')[1] );

		}

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

			// Move form to para.
			text_sig = item.prop('id').split('-')[1];

		}

		// --<
		return text_sig;

	}



})( jQuery );



/* -------------------------------------------------------------------------- */



// Do immediate init.
CommentPress.settings.DOM.init();
CommentPress.settings.page.init();
CommentPress.settings.textblock.init();

CommentPress.common.DOM.init();
CommentPress.common.navigation.init();
CommentPress.common.content.init();
CommentPress.common.comments.init();
CommentPress.common.activity.init();



/* -------------------------------------------------------------------------- */



/**
 * Define what happens when the page is ready.
 *
 * @since 3.0
 */
jQuery(document).ready(function($) {

	// Setup dom loaded actions.
	CommentPress.settings.DOM.dom_ready();
	CommentPress.settings.page.dom_ready();
	CommentPress.settings.textblock.dom_ready();

	CommentPress.common.DOM.dom_ready();
	CommentPress.common.navigation.dom_ready();
	CommentPress.common.content.dom_ready();
	CommentPress.common.comments.dom_ready();
	CommentPress.common.activity.dom_ready();

	// Broadcast.
	jQuery(document).trigger( 'commentpress-initialised' );

}); // End document.ready()



