/*
================================================================================
CommentPress Modern Screen Javascript
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

		// Add js class so we can do some contextual styling.
		$('html').addClass( 'js' );

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
	 * Perform actions before the page is ready.
	 *
	 * Writes styles into the document <head> to avoid avoid flash of content.
	 *
	 * @since 3.8
	 */
	this.head = function() {

		// Define vars.
		var styles, cp_header_height;

		// Init styles.
		styles = '';

		// Init header height.
		cp_header_height = 70;

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
				styles += 'body.admin-bar #header, #header { top: ' + CommentPress.settings.DOM.get_wp_adminbar_height() + 'px; } ';
				styles += 'body.admin-bar #sidebar, #sidebar, body.admin-bar #navigation, #navigation { top: ' + (CommentPress.settings.DOM.get_wp_adminbar_height() + cp_header_height) + 'px; } ';

				// If we have the responsive admin bar in 3.8+
				if ( CommentPress.settings.DOM.get_wp_adminbar_height() == '32' ) {

					// React to responsive admin bar.
					styles += '@media screen and ( max-width: 782px ) { ' +
								'body.admin-bar #header, #header { top: ' + CommentPress.settings.DOM.get_wp_adminbar_expanded() + 'px; }' +
								'body.admin-bar #sidebar, #sidebar, body.admin-bar #navigation, #navigation { top: ' + (CommentPress.settings.DOM.get_wp_adminbar_expanded() + cp_header_height) + 'px; }' +
							' } ';

				}

			}

			// Are subpages to be shown?
			if ( cp_show_subpages == '0' ) {

				// Avoid flash of hidden elements on collapsed items.
				styles += '#toc_sidebar .sidebar_contents_wrapper ul li ul { display: none; } ';

				// Show current item and ancestors.
				styles += '#toc_sidebar .sidebar_contents_wrapper ul li.current_page_ancestor > ul { display: block; } ';
				//styles += '#toc_sidebar .sidebar_contents_wrapper ul li.current_page_item { display: block; } ';

			}

			// Is this the comments sidebar?
			if ( cp_special_page == '0' ) {

				// Avoid flash of hidden comment form.
				styles += '#respond { display: none; } ';

			}

			// On global activity sidebar, avoid flash of hidden comments.
			styles += '#sidebar .paragraph_wrapper { display: none; } ';
			styles += '#navigation .paragraph_wrapper { display: none; } ';
			styles += '#sidebar .paragraph_wrapper.start_open { display: block; } ';
			styles += '#navigation .paragraph_wrapper.start_open { display: block; } ';
			styles += '.commentpress_page #navigation .paragraph_wrapper.special_pages_wrapper { display: block; } ';
			styles += '.cp_sidebar_activity #comments_sidebar { display: none; } ';

			// Hide original and literal content when JS-enabled.
			styles += '#original .post, #literal .post { display: none; } ';

			// Close style declaration.
			styles += '</style>';

		}

		// Write to page now.
		document.write( styles );

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
		offset = 0 - $('#header').height() - $('#sidebar_tabs').height(); // Add a few pix for comfort

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
	 * Set up "Contents" column "Special Pages" menu behaviour.
	 *
	 * @since 3.8
	 */
	this.menu = function() {

		/**
		 * Clicks on "Special Pages" menu items.
		 *
		 * This is unique to this theme, so is not included in the setup class.
		 *
		 * @since 3.8
		 *
		 * @return false
		 */
		$('#toc_sidebar').on( 'click', 'ul#nav li a', function( event ) {

			// Define vars.
			var myArr;

			// No, find child lists of the enclosing <li>.
			myArr = $(this).parent().find('ul');

			// Do we have a child list?
			if( myArr.length > 0 ) {

				// Toggle next list.
				$(this).next('ul').slideToggle();

				// Override event.
				event.preventDefault();

			}

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
		content_min_height = $('#page_wrapper').css( 'min-height' );

		// Store content padding-bottom on load.
		content_padding_bottom = $('#page_wrapper').css( 'padding-bottom' );

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

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.dom_ready = function() {

		// Enable buttons.
		me.enable_buttons();

		// Set height of sidebars.
		me.set_height();

	};



	/**
	 * Set height of sidebars.
	 *
	 * @since 3.8
	 */
	this.set_height = function() {

		// Define vars.
		var viewport, header_height, switcher_height, sidebar_header_height,
			wpadminbar_height, toc_sidebar_height, switcher_display,
			sidebar_switcher_height, sidebar_height, window_inner;

		// Get viewport height.
		viewport = $(window).height();

		// iOS9 Safari falsely reports the height when the URL bar shrinks.
		if ( cp_is_mobile == '1' || cp_is_tablet == '1' ) {

			// Get window innerHeight.
			window_inner = window.innerHeight;

			// Override if different.
			if ( viewport < window_inner ) {
				viewport = window_inner;
			}

		}

		// Get interface elements.
		header_height = $('#header').height();
		switcher_height = $('#switcher').height();
		sidebar_header_height = $('#toc_sidebar > .sidebar_header').height();

		// Is the admin bar shown?
		if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {
			wpadminbar_height = $('#wpadminbar').height();
		} else {
			wpadminbar_height = 0;
		}

		// Set top of sidebars.
		$('html body #content_container #sidebar,html body #content_container #navigation').css(
			'top', ( header_height + wpadminbar_height ) + 'px'
		);

		// Calculate TOC sidebar height.
		toc_sidebar_height = viewport - (header_height + sidebar_header_height + wpadminbar_height);

		// Allow for switcher visibility.
		switcher_display = $('#switcher').css('display');
		if (switcher_display === 'block') {
			toc_sidebar_height = toc_sidebar_height - switcher_height;
		}

		// Set height.
		$('#toc_sidebar .sidebar_contents_wrapper').css( 'height', toc_sidebar_height + 'px' );

		// Get sidebar tabs header height instead.
		sidebar_switcher_height = $('#sidebar_tabs').height();
		sidebar_height = viewport - (header_height + sidebar_switcher_height + wpadminbar_height);

		// Allow for switcher visibility.
		if (switcher_display === 'block') {
			sidebar_height = sidebar_height - switcher_height;
		}

		// Set height.
		$('#sidebar .sidebar_contents_wrapper').css( 'height', sidebar_height + 'px' );

	};



	/**
	 * Bring sidebar to front.
	 *
	 * @since 3.8
	 *
	 * @param string sidebar The sidebar to bring to the front.
	 */
	this.activate_sidebar = function( sidebar ) {

		if ( sidebar == 'comments' ) {

			// Move to sidebar.
			if ( !$('body').hasClass('active-sidebar') || $('body').hasClass('active-nav') ) {
				CommentPress.theme.sidebars.show_discuss();
			}

		}

		// Define vars.
		var ontop, s_top, s_top_border;

		// Get "visibility" of the requested sidebar.
		ontop = $('#' + sidebar + '_sidebar').css('z-index');

		// Is it hidden - i.e. does it have a lower z-index?
		if ( ontop == '2001' ) {

			// Hide all.
			$('.sidebar_container').css('z-index','2001');
			$('#sidebar_tabs h2 a').removeClass('active-tab');

			// Show it.
			$('#' + sidebar + '_sidebar').css('z-index','2010').css('display','block');
			$('#sidebar_tabs #' + sidebar + '_header h2 a').addClass('active-tab');

		}

	};



	/**
	 * Enable buttons.
	 *
	 * @since 3.8
	 */
	this.enable_buttons = function() {

		// Toggle for navigation.
		$('.navigation-button').on( 'click', function(e) {
			e.preventDefault();
			me.show_nav();
		});

		// Toggle for content.
		$('.content-button').on( 'click', function(e) {
			e.preventDefault();
			me.show_content();
		});

		// Toggle for sidebar.
		$('.sidebar-button').on( 'click', function(e) {
			e.preventDefault();
			me.show_discuss();
		});

	};



	/**
	 * Show navigate column.
	 *
	 * @since 3.8
	 */
	this.show_nav = function() {
		$('body').toggleClass('active-nav').removeClass('active-sidebar');
		$('.sidebar-button,.content-button').removeClass('active-button');
		$('.navigation-button').toggleClass('active-button');
	};

	/**
	 * Show content column.
	 *
	 * @since 3.8
	 */
	this.show_content = function() {
		$('body').removeClass('active-sidebar').removeClass('active-nav');
		$('.navigation-button,.sidebar-button').removeClass('active-button');
		$('.content-button').toggleClass('active-button');
	};

	/**
	 * Show discuss column.
	 *
	 * @since 3.8
	 */
	this.show_discuss = function() {
		$('body').toggleClass('active-sidebar').removeClass('active-nav');
		$('.navigation-button,.content-button').removeClass('active-button');
		$('.sidebar-button').toggleClass('active-button');
	};

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

		// Track viewport changes.
		me.track_resize();

		// Track scrolling.
		me.track_scrolling();

	};



	/**
	 * Track viewport changes.
	 *
	 * @since 3.8
	 */
	this.track_resize = function() {

		/**
		 * Perform actions when the viewport is resized.
		 *
		 * @since 3.8
		 */
		$(window).on( 'resize', function() {

			// Maintain height of sidebars.
			CommentPress.theme.sidebars.set_height();

		});

	};



	/**
	 * Track window scrolling.
	 *
	 * @since 3.8
	 */
	this.track_scrolling = function() {

		/**
		 * Track scrolling.
		 *
		 * @todo Rationalise this code as much of it is duplicated.
		 *
		 * @since 3.8
		 */
		$(window).on( 'scroll', function() {

			// Declare vars.
			var viewport, header_height, switcher_height, sidebar_header_height, wpadminbar_height,
				toc_sidebar_height, switcher_display, sidebar_switcher_height, sidebar_height,
				header, header_position, header_bottom, sidebar_top;

			// Get header.
			header = $('#header');

			// Test for static header.
			position = header.css( 'position' );

			// Only do this if header is absolutely positioned.
			if ( position == 'absolute' ) {

				// Get interface elements.
				header_height = header.height();
				header_position = header.position();
				header_bottom = window.pageYOffset - ( header_position.top + header_height );

				// When the bottom of the header passes out of the viewport.
				if ( parseInt( header_bottom ) > 0 ) {

					// Get top of sidebar.
					sidebar_top = $.px_to_num( $('html body #content_container #sidebar').css( 'top' ) );

					// Bail if already zero.
					if ( sidebar_top == '0' ) { return; }

					// Set top of sidebars.
					$('html body #content_container #sidebar,html body #content_container #navigation').css(
						'top', '0'
					);

					// Get interface elements.
					viewport = $(window).height();
					switcher_height = $('#switcher').height();
					sidebar_header_height = $('#toc_sidebar > .sidebar_header').height();

					// Calculate.
					toc_sidebar_height = viewport - sidebar_header_height;

					// Allow for switcher visibility.
					switcher_display = $('#switcher').css('display');
					if (switcher_display === 'block') {
						toc_sidebar_height = toc_sidebar_height - switcher_height;
					}

					// Set height.
					$('#toc_sidebar .sidebar_contents_wrapper').css( 'height', toc_sidebar_height + 'px' );

					// Get sidebar tabs header height instead.
					sidebar_switcher_height = $('#sidebar_tabs').height();
					sidebar_height = viewport - sidebar_switcher_height;

					// Allow for switcher visibility.
					if (switcher_display === 'block') {
						sidebar_height = sidebar_height - switcher_height;
					}

					// Set height.
					$('#sidebar .sidebar_contents_wrapper').css( 'height', sidebar_height + 'px' );

				} else {

					// Is the admin bar shown?
					if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {
						wpadminbar_height = $('#wpadminbar').height();
					} else {
						wpadminbar_height = 0;
					}

					// Get top of sidebar.
					sidebar_top = $.px_to_num( $('html body #content_container #sidebar').css( 'top' ) );

					// Bail if already zero.
					if ( sidebar_top == header_height + wpadminbar_height ) { return; }

					// Set top of sidebars.
					$('html body #content_container #sidebar,html body #content_container #navigation').css(
						'top', ( header_height + wpadminbar_height ) + 'px'
					);

					// Set sidebar height.
					CommentPress.theme.sidebars.set_height();

				}

			} else {

				// Fixed header.

				// Mobile browsers often reduce the size of the screen chrome on
				// page scroll - so let's try and update the sidebar height.
				if ( cp_is_mobile == '1' || cp_is_tablet == '1' ) {

					// Set sidebar height.
					CommentPress.theme.sidebars.set_height();

				}

			}

		});

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

		// Declare vars.
		var post_id;

		// Only scroll if not mobile - but allow tablets.
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

			// Let's try and scroll to the title.
			if ( target == 0 ) {

				// Parse post ID.
				post_id = $('.comments_container').prop('id');

				// Sanity check.
				if ( 'undefined' !== typeof post_id ) {

					// Get target post ID.
					target_id = post_id.split('-')[1];

					// Contruct target.
					target = $('#post-' + target_id);

				}

			}

			// Scroll.
			$(window).stop(true).scrollTo(
				target,
				{
					duration: (speed * 1.5),
					axis: 'y',
					offset: CommentPress.theme.header.get_offset()
				}
			);

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

			// Locate in DOM.
			anchor = $( '#' + anchor_id );

			// Did we get one?
			if ( anchor.length ) {

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
		CommentPress.theme.sidebars.activate_sidebar('comments');

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

		// Move to sidebar.
		if ( !$('body').hasClass('active-sidebar') || $('body').hasClass('active-nav') ) {
			CommentPress.theme.sidebars.show_discuss();
		}

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

		// Show comments sidebar.
		CommentPress.theme.sidebars.activate_sidebar( 'comments' );

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
 * Create sub-sub-namespace for modern theme.
 *
 * @since 3.8
 */
CommentPress.theme.modern = {};



/* -------------------------------------------------------------------------- */



// Do immediate init.
CommentPress.theme.settings.init();
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



	// Trigger DOM ready methods.
	CommentPress.theme.settings.dom_ready();
	CommentPress.theme.DOM.dom_ready();
	CommentPress.theme.header.dom_ready();
	CommentPress.theme.navigation.dom_ready();
	CommentPress.theme.content.dom_ready();
	CommentPress.theme.sidebars.dom_ready();
	CommentPress.theme.viewport.dom_ready();

	// The modern theme uses a "rollover".
	CommentPress.common.comments.comment_rollovers();



	/**
	 * Hook into CommentPress Form comment highlight trigger.
	 *
	 * @since 3.8
	 *
	 * @param int parent_id The parent comment ID.
	 */
	$( document ).on( 'commentpress-comment-highlight', function( event, parent_id ) {

		// Add highlight class.
		$( '#li-comment-' + parent_id + ' > .comment-wrapper' ).addClass( 'background-highlight' );

	});

	/**
	 * Hook into CommentPress Form comment unhighlight trigger.
	 *
	 * @since 3.8
	 *
	 * @param int parent_id The parent comment ID.
	 */
	$( document ).on( 'commentpress-comment-unhighlight', function( event, parent_id ) {

		// Remove highlight class.
		jQuery( '#li-comment-' + parent_id + ' > .comment-wrapper' ).removeClass( 'background-highlight' );

	});

	/**
	 * Hook into CommentPress Form clear all comment highlights trigger.
	 *
	 * @since 3.8
	 */
	$( document ).on( 'commentpress-comment-highlights-clear', function( event ) {

		// Remove highlight class.
		jQuery( '.comment-wrapper' ).removeClass( 'background-highlight' );

	});



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
