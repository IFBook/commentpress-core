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

		// add js class so we can do some contextual styling
		$('html').addClass( 'js' );

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
	 * Perform actions before the page is ready
	 *
	 * Writes styles into the document <head> to avoid avoid flash of content
	 *
	 * @return void
	 */
	this.head = function() {

		// define vars
		var styles, cp_header_height;

		// init styles
		styles = '';

		// init header height
		cp_header_height = 70;

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
				styles += 'body.admin-bar #header, #header { top: ' + CommentPress.settings.DOM.get_wp_adminbar_height() + 'px; } ';
				styles += 'body.admin-bar #sidebar, #sidebar, body.admin-bar #navigation, #navigation { top: ' + (CommentPress.settings.DOM.get_wp_adminbar_height() + cp_header_height) + 'px; } ';

				// if we have the responsive admin bar in 3.8+
				if ( CommentPress.settings.DOM.get_wp_adminbar_height() == '32' ) {

					// react to responsive admin bar
					styles += '@media screen and ( max-width: 782px ) { ' +
								'body.admin-bar #header, #header { top: ' + CommentPress.settings.DOM.get_wp_adminbar_expanded() + 'px; }' +
								'body.admin-bar #sidebar, #sidebar, body.admin-bar #navigation, #navigation { top: ' + (CommentPress.settings.DOM.get_wp_adminbar_expanded() + cp_header_height) + 'px; }' +
							' } ';

				}

			}

			// are subpages to be shown?
			if ( cp_show_subpages == '0' ) {

				// avoid flash of hidden elements on collapsed items
				styles += '#toc_sidebar .sidebar_contents_wrapper ul li ul { display: none; } ';

				// show current item and ancestors
				styles += '#toc_sidebar .sidebar_contents_wrapper ul li.current_page_ancestor > ul { display: block; } ';
				//styles += '#toc_sidebar .sidebar_contents_wrapper ul li.current_page_item { display: block; } ';

			}

			// is this the comments sidebar?
			if ( cp_special_page == '0' ) {

				// avoid flash of hidden comment form
				styles += '#respond { display: none; } ';

			}

			// on global activity sidebar, avoid flash of hidden comments
			styles += '#sidebar .paragraph_wrapper { display: none; } ';
			styles += '#navigation .paragraph_wrapper { display: none; } ';
			styles += '#sidebar .paragraph_wrapper.start_open { display: block; } ';
			styles += '#navigation .paragraph_wrapper.start_open { display: block; } ';
			styles += '.commentpress_page #navigation .paragraph_wrapper.special_pages_wrapper { display: block; } ';
			//styles += '#sidebar .paragraph_wrapper { display: none; } ';

			// hide original and literal content when JS-enabled
			styles += '#original .post, #literal .post { display: none; } ';

			// close style declaration
			styles += '</style>';

		}

		// write to page now
		document.write( styles );

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
		offset = 0 - $('#header').height() - $('#sidebar_tabs').height(); // add a few pix for comfort

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
	 * Set up "Contents" column "Special Pages" menu behaviour
	 *
	 * @return void
	 */
	this.menu = function() {

		/**
		 * Clicks on "Special Pages" menu items
		 *
		 * This is unique to this theme, so is not included in the setup class
		 *
		 * @return false
		 */
		$('#toc_sidebar').on( 'click', 'ul#nav li a', function( event ) {

			// define vars
			var myArr;

			// no, find child lists of the enclosing <li>
			myArr = $(this).parent().find('ul');

			// do we have a child list?
			if( myArr.length > 0 ) {

				// toggle next list
				$(this).next('ul').slideToggle();

				// override event
				event.preventDefault();

			}

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
		content_min_height = $('#page_wrapper').css( 'min-height' );

		// store content padding-bottom on load
		content_padding_bottom = $('#page_wrapper').css( 'padding-bottom' );

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

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.dom_ready = function() {

		// enable buttons
		me.enable_buttons();

		// set height of sidebars
		me.set_height();

	};



	/**
	 * Set height of sidebars
	 *
	 * @return void
	 */
	this.set_height = function() {

		// define vars
		var viewport, header_height, switcher_height, sidebar_header_height, wpadminbar_height,
			toc_sidebar_height, switcher_display, sidebar_switcher_height, sidebar_height;

		// get window
		viewport = $(window).height();

		// get interface elements
		header_height = $('#header').height();
		switcher_height = $('#switcher').height();
		sidebar_header_height = $('#toc_sidebar > .sidebar_header').height();

		// is the admin bar shown?
		if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {
			wpadminbar_height = $('#wpadminbar').height();
		} else {
			wpadminbar_height = 0;
		}

		// calculate
		toc_sidebar_height = viewport - (header_height + sidebar_header_height + wpadminbar_height);

		// allow for switcher visibility
		switcher_display = $('#switcher').css('display');
		if (switcher_display === 'block') {
			toc_sidebar_height = toc_sidebar_height - switcher_height;
		}

		// set height
		$('#toc_sidebar .sidebar_contents_wrapper').css( 'height', toc_sidebar_height + 'px' );

		// get sidebar tabs header height instead
		sidebar_switcher_height = $('#sidebar_tabs').height();
		sidebar_height = viewport - (header_height + sidebar_switcher_height + wpadminbar_height);

		// allow for switcher visibility
		if (switcher_display === 'block') {
			sidebar_height = sidebar_height - switcher_height;
		}

		// set height
		$('#sidebar .sidebar_contents_wrapper').css( 'height', sidebar_height + 'px' );

	};



	/**
	 * Bring sidebar to front
	 *
	 * @param string sidebar The sidebar to bring to the front
	 * @return void
	 */
	this.activate_sidebar = function( sidebar ) {

		if ( sidebar == 'comments' ) {

			// move to sidebar
			if ( !$('body').hasClass('active-sidebar') || $('body').hasClass('active-nav') ) {
				CommentPress.theme.sidebars.show_discuss();
			}

		}

		// define vars
		var ontop, s_top, s_top_border;

		// get "visibility" of the requested sidebar
		ontop = $('#' + sidebar + '_sidebar').css('z-index');

		// is it hidden (ie, does it have a lower z-index)
		if ( ontop == '2001' ) {

			// hide all
			$('.sidebar_container').css('z-index','2001');
			$('#sidebar_tabs h2 a').removeClass('active-tab');

			// show it
			$('#' + sidebar + '_sidebar').css('z-index','2010');
			$('#sidebar_tabs #' + sidebar + '_header h2 a').addClass('active-tab');

		}

	};



	/**
	 * Enable buttons
	 *
	 * @return void
	 */
	this.enable_buttons = function() {

		// Toggle for navigation
		$('.navigation-button').click(function(e) {
			e.preventDefault();
			me.show_nav();
		});

		// Toggle for content
		$('.content-button').click(function(e) {
			e.preventDefault();
			me.show_content();
		});

		// Toggle for sidebar
		$('.sidebar-button').click(function(e) {
			e.preventDefault();
			me.show_discuss();
		});

	};



	/**
	 * Show navigate column
	 *
	 * @return void
	 */
	this.show_nav = function() {
		$('body').toggleClass('active-nav').removeClass('active-sidebar');
		$('.sidebar-button,.content-button').removeClass('active-button');
		$('.navigation-button').toggleClass('active-button');
	};

	/**
	 * Show content column
	 *
	 * @return void
	 */
	this.show_content = function() {
		$('body').removeClass('active-sidebar').removeClass('active-nav');
		$('.navigation-button,.sidebar-button').removeClass('active-button');
		$('.content-button').toggleClass('active-button');
	};

	/**
	 * Show discuss column
	 *
	 * @return void
	 */
	this.show_discuss = function() {
		$('body').toggleClass('active-sidebar').removeClass('active-nav');
		$('.navigation-button,.content-button').removeClass('active-button');
		$('.sidebar-button').toggleClass('active-button');
	};

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

		// track viewport changes
		me.track_resize();

		// track scrolling
		me.track_scrolling();

	};



	/**
	 * Track viewport changes
	 *
	 * @return void
	 */
	this.track_resize = function() {

		/**
		 * Perform actions when the viewport is resized
		 *
		 * @return void
		 */
		$(window).resize( function() {

			// maintain height of sidebars
			CommentPress.theme.sidebars.set_height();

		});

	};



	/**
	 * Track window scrolling
	 *
	 * @return void
	 */
	this.track_scrolling = function() {

		/**
		 * Track scrolling
		 *
		 * @todo Rationalise this code as much of it is duplicated
		 *
		 * @return void
		 */
		$(window).scroll( function() {

			// declare vars
			var viewport, header_height, switcher_height, sidebar_header_height, wpadminbar_height,
				toc_sidebar_height, switcher_display, sidebar_switcher_height, sidebar_height,
				header, header_position, header_bottom, sidebar_top;

			// get header
			header = $('#header');

			// test for static header
			position = header.css( 'position' );

			// only do this if header is absolutely positioned
			if ( position == 'absolute' ) {

				// get interface elements
				header_height = header.height();
				header_position = header.position();
				header_bottom = window.pageYOffset - ( header_position.top + header_height );

				// when the bottom of the header passes out of the viewport...
				if ( parseInt( header_bottom ) > 0 ) {

					// get top of sidebar
					sidebar_top = $.px_to_num( $('html body #content_container #sidebar').css( 'top' ) );

					// bail if already zero
					if ( sidebar_top == '0' ) { return; }

					// set top of sidebars
					$('html body #content_container #sidebar,html body #content_container #navigation').css(
						'top', '0'
					);

					// get interface elements
					viewport = $(window).height();
					switcher_height = $('#switcher').height();
					sidebar_header_height = $('#toc_sidebar > .sidebar_header').height();

					// calculate
					toc_sidebar_height = viewport - sidebar_header_height;

					// allow for switcher visibility
					switcher_display = $('#switcher').css('display');
					if (switcher_display === 'block') {
						toc_sidebar_height = toc_sidebar_height - switcher_height;
					}

					// set height
					$('#toc_sidebar .sidebar_contents_wrapper').css( 'height', toc_sidebar_height + 'px' );

					// get sidebar tabs header height instead
					sidebar_switcher_height = $('#sidebar_tabs').height();
					sidebar_height = viewport - sidebar_switcher_height;

					// allow for switcher visibility
					if (switcher_display === 'block') {
						sidebar_height = sidebar_height - switcher_height;
					}

					// set height
					$('#sidebar .sidebar_contents_wrapper').css( 'height', sidebar_height + 'px' );

				} else {

					// is the admin bar shown?
					if ( CommentPress.settings.DOM.get_wp_adminbar() == 'y' ) {
						wpadminbar_height = $('#wpadminbar').height();
					} else {
						wpadminbar_height = 0;
					}

					// get top of sidebar
					sidebar_top = $.px_to_num( $('html body #content_container #sidebar').css( 'top' ) );

					// bail if already zero
					if ( sidebar_top == header_height + wpadminbar_height ) { return; }

					// set top of sidebars
					$('html body #content_container #sidebar,html body #content_container #navigation').css(
						'top', ( header_height + wpadminbar_height ) + 'px'
					);

					// set sidebar height
					CommentPress.theme.sidebars.set_height();

				}



			}
			//*/

		});

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

		// declare vars
		var post_id;

		// only scroll if not mobile (but allow tablets)
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

			// let's try and scroll to the title
			if ( target == 0 ) {

				// parse post ID
				post_id = $('.comments_container').prop('id');

				// sanity check
				if ( 'undefined' !== typeof post_id ) {

					// get target post ID
					target_id = post_id.split('-')[1];

					// contruct target
					target = $('#post-' + target_id);

				}

			}

			// scroll
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
		CommentPress.theme.sidebars.activate_sidebar('comments');

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

		// move to sidebar
		if ( !$('body').hasClass('active-sidebar') || $('body').hasClass('active-nav') ) {
			CommentPress.theme.sidebars.show_discuss();
		}

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

		// show comments sidebar
		CommentPress.theme.sidebars.activate_sidebar( 'comments' );

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
 * Create sub-sub-namespace for modern theme
 */
CommentPress.theme.modern = {};



/* -------------------------------------------------------------------------- */



// do immediate init
CommentPress.theme.settings.init();
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



	// trigger DOM ready methods
	CommentPress.theme.settings.dom_ready();
	CommentPress.theme.DOM.dom_ready();
	CommentPress.theme.header.dom_ready();
	CommentPress.theme.navigation.dom_ready();
	CommentPress.theme.content.dom_ready();
	CommentPress.theme.sidebars.dom_ready();
	CommentPress.theme.viewport.dom_ready();

	// the modern theme uses a "rollover"
	CommentPress.common.comments.comment_rollovers();



	/**
	 * Hook into CommentPress Form comment highlight trigger
	 *
	 * @param int parent_id The parent comment ID
	 * @return void
	 */
	$( document ).on( 'commentpress-comment-highlight', function( event, parent_id ) {

		// add highlight class
		$( '#li-comment-' + parent_id + ' > .comment-wrapper' ).addClass( 'background-highlight' );

	});

	/**
	 * Hook into CommentPress Form comment unhighlight trigger
	 *
	 * @param int parent_id The parent comment ID
	 * @return void
	 */
	$( document ).on( 'commentpress-comment-unhighlight', function( event, parent_id ) {

		// remove highlight class
		jQuery( '#li-comment-' + parent_id + ' > .comment-wrapper' ).removeClass( 'background-highlight' );

	});

	/**
	 * Hook into CommentPress Form clear all comment highlights trigger
	 *
	 * @return void
	 */
	$( document ).on( 'commentpress-comment-highlights-clear', function( event ) {

		// remove highlight class
		jQuery( '.comment-wrapper' ).removeClass( 'background-highlight' );


	});



	/**
	 * Hook into CommentPress AJAX Infinite Scroll page changed
	 *
	 * This hook is present in this file because the WP FEE JS is not loaded
	 * when WP FEE is active, but we still want to change the URL of the toggle
	 * button to reflect the page URL change.
	 *
	 * @return void
	 */
	$( document ).on( 'commentpress-post-changed', function( event ) {

		// declare local vars
		var toggler, new_url, toggle_url;

		// find new URL
		new_url = document.location.href;

		// get toggle URL
		toggler = $( '.editor_toggle a' );

		// bail if not found
		if ( toggler.length == 0 ) { return; }

		// get toggle URL
		toggle_url = toggler.attr( 'href' );

		// split on query string
		nonce = toggle_url.split( '?' )[1];

		// add to new URL
		new_url += '?' + nonce;

		// update toggle
		toggler.attr( 'href', new_url );

	});



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
