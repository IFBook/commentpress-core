/**
 * -----------------------------------------------------------------------------
 * CommentPress Core Common Code Library
 * -----------------------------------------------------------------------------
 *
 * This code implements some features of a jQuery Plugin, but is mostly used as
 * a common library for all CommentPress-compatible themes. It allows us to add
 * numerous methods to jQuery without cluttering the global function namespace.
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
 * Create global variables
 *
 * These were being defined in each theme, so have been moved to this library to
 * avoid duplication of code. They are in the porcess of being migrated to class
 * variables to avoid name collisions.
 */

// define global IE var
var msie_detected = false;

// browser detection via conditional comments in <head>
if ( 'undefined' !== typeof cp_msie ) {
	msie_detected = true;
}

// test for our localisation object
if ( 'undefined' !== typeof CommentpressSettings ) {

	// define our vars
	var cp_comments_open, cp_special_page, cp_tinymce, cp_tinymce_version,
		cp_promote_reading, cp_is_mobile, cp_is_touch, cp_is_tablet, cp_cookie_path,
		cp_multipage_page, cp_template_dir, cp_plugin_dir, cp_toc_chapter_is_page, cp_show_subpages,
		cp_default_sidebar, cp_is_signup_page, cp_scroll_speed, cp_min_page_width,
		cp_textblock_meta;

	// set our vars
	cp_comments_open = CommentpressSettings.cp_comments_open;
	cp_special_page = CommentpressSettings.cp_special_page;
	cp_tinymce = CommentpressSettings.cp_tinymce;
	cp_tinymce_version = CommentpressSettings.cp_tinymce_version;
	cp_promote_reading = CommentpressSettings.cp_promote_reading;
	cp_is_mobile = CommentpressSettings.cp_is_mobile;
	cp_is_touch = CommentpressSettings.cp_is_touch;
	cp_is_tablet = CommentpressSettings.cp_is_tablet;
	cp_cookie_path = CommentpressSettings.cp_cookie_path;
	cp_multipage_page = CommentpressSettings.cp_multipage_page;
	cp_template_dir = CommentpressSettings.cp_template_dir;
	cp_plugin_dir = CommentpressSettings.cp_plugin_dir;
	cp_toc_chapter_is_page = CommentpressSettings.cp_toc_chapter_is_page;
	cp_show_subpages = CommentpressSettings.cp_show_subpages;
	cp_default_sidebar = CommentpressSettings.cp_default_sidebar;
	cp_is_signup_page = CommentpressSettings.cp_is_signup_page;
	cp_scroll_speed = CommentpressSettings.cp_js_scroll_speed;
	cp_min_page_width = CommentpressSettings.cp_min_page_width;
	cp_textblock_meta = CommentpressSettings.cp_textblock_meta;

}



/**
 * Create global CommentPress namespace
 */
var CommentPress = CommentPress || {};



/**
 * Create settings sub-namespace
 */
CommentPress.settings = {};



/**
 * Create CommentPress settings DOM class
 */
CommentPress.settings.DOM = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress settings DOM.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

		// init WordPress adminbar
		me.init_wp_adminbar();

		// init BuddyPress adminbar
		me.init_bp_adminbar();

		// init WordPress adminbar height
		me.init_wp_adminbar_height();

	};



	// init BuddyPress adminbar
	this.bp_adminbar = 'n';

	/**
	 * Init for BuddyPress adminbar height
	 */
	this.init_bp_adminbar = function( val ) {

		// get initial value from settings object
		if ( 'undefined' !== typeof CommentpressSettings ) {
			this.bp_adminbar = CommentpressSettings.cp_bp_adminbar;
		}

	};

	/**
	 * Setter for BuddyPress adminbar
	 */
	this.set_bp_adminbar = function( val ) {
		this.bp_adminbar = val;
	};

	/**
	 * Getter for BuddyPress adminbar
	 */
	this.get_bp_adminbar = function() {
		return this.bp_adminbar;
	};



	// init WordPress adminbar
	this.wp_adminbar = 'n';

	/**
	 * Init for WordPress adminbar height
	 */
	this.init_wp_adminbar = function( val ) {

		// get initial value from settings object
		if ( 'undefined' !== typeof CommentpressSettings ) {
			this.wp_adminbar = CommentpressSettings.cp_wp_adminbar;
		}

	};

	/**
	 * Setter for WordPress adminbar
	 */
	this.set_wp_adminbar = function( val ) {
		this.wp_adminbar = val;
	};

	/**
	 * Getter for WordPress adminbar
	 */
	this.get_wp_adminbar = function() {
		return this.wp_adminbar;
	};



	// init WordPress adminbar height
	this.wp_adminbar_height = 0;

	/**
	 * Init for WordPress adminbar height
	 */
	this.init_wp_adminbar_height = function( val ) {

		// get initial value from settings object
		if ( 'undefined' !== typeof CommentpressSettings ) {
			this.wp_adminbar_height = parseInt( CommentpressSettings.cp_wp_adminbar_height );
		}

		// support for legacy BuddyPress bar
		if ( me.get_bp_adminbar() == 'y' ) {

			// amend to height of BuddyPress bar
			this.wp_adminbar_height = 25;

			// act as if admin bar were there
			me.set_wp_adminbar( 'y' );

		}

	};

	/**
	 * Setter for WordPress adminbar height
	 */
	this.set_wp_adminbar_height = function( val ) {
		this.wp_adminbar_height = val;
	};

	/**
	 * Getter for WordPress adminbar height
	 */
	this.get_wp_adminbar_height = function() {
		return this.wp_adminbar_height;
	};



	// init WordPress adminbar height
	this.wp_adminbar_height = 0;

	/**
	 * Init for WordPress adminbar expanded
	 */
	this.init_wp_adminbar_expanded = function( val ) {

		// get initial value from settings object
		if ( 'undefined' !== typeof CommentpressSettings ) {
			this.wp_adminbar_expanded = parseInt( CommentpressSettings.cp_wp_adminbar_expanded );
		}

	};

	/**
	 * Setter for WordPress adminbar expanded
	 */
	this.set_wp_adminbar_expanded = function( val ) {
		this.wp_adminbar_expanded = val;
	};

	/**
	 * Getter for WordPress adminbar expanded
	 */
	this.get_wp_adminbar_expanded = function() {
		return this.wp_adminbar_expanded;
	};



} // end CommentPress settings DOM class



/**
 * Create CommentPress page settings class
 */
CommentPress.settings.page = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress settings page.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {
		// nothing yet
	};



	// init highlight
	this.highlight = false;

	/**
	 * Toggle for page highlight
	 */
	this.toggle_highlight = function() {
		this.highlight = !this.highlight;
	};

	/**
	 * Setter for page highlight
	 */
	this.set_highlight = function( val ) {
		this.highlight = val;
	};

	/**
	 * Getter for page highlight
	 */
	this.get_highlight = function() {
		return this.highlight;
	};

} // end CommentPress page settings class



/**
 * Create CommentPress textblock class
 */
CommentPress.settings.textblock = new function() {

	// init textblock marker mode
	this.marker_mode = 'marker';

	/**
	 * Setter for textblock marker mode
	 */
	this.setMarkerMode = function( mode ) {
		this.marker_mode = mode;
	};

	/**
	 * Getter for textblock marker mode
	 */
	this.getMarkerMode = function() {
		return this.marker_mode;
	};

} // end CommentPress textblock class



/**
 * Create setup sub-namespace
 */
CommentPress.setup = {};



/**
 * Create CommentPress setup navigation column class
 */
CommentPress.setup.navigation = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress setup navigation column.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

		// column headings
		me.headings();

		// menu behaviour
		me.menu();

	};



	/**
	 * Set up "Contents" column headings
	 *
	 * @return false
	 */
	this.headings = function() {

		// set pointer
		$('h3.activity_heading').css( 'cursor', 'pointer' );

		/**
		 * Activity column headings click
		 *
		 * @return void
		 */
		$('#toc_sidebar').on( 'click', 'h3.activity_heading', function( event ) {

			// define vars
			var para_wrapper;

			// override event
			event.preventDefault();

			// get para wrapper
			para_wrapper = $(this).next('div.paragraph_wrapper');
			//console.log( para_wrapper );

			// set width to prevent rendering error
			para_wrapper.css( 'width', $(this).parent().css( 'width' ) );

			// toggle next paragraph_wrapper
			para_wrapper.slideToggle( 'slow', function() {

				// when finished, reset width to auto
				para_wrapper.css( 'width', 'auto' );

			} );

		});

	};



	/**
	 * Set up "Contents" column menu behaviour
	 *
	 * @return false
	 */
	this.menu = function() {

		/**
		 * Chapter page headings click
		 *
		 * @return void
		 */
		$('#toc_sidebar').on( 'click', 'ul#toc_list li a', function( event ) {

			// are our chapters pages?
			if ( cp_toc_chapter_is_page == '0' ) {

				// define vars
				var myArr;

				// no, find child lists of the enclosing <li>
				myArr = $(this).parent().find('ul');

				// do we have a child list?
				if( myArr.length > 0 ) {

					// are subpages to be shown?
					if ( cp_show_subpages == '0' ) {

						// toggle next list
						$(this).next('ul').slideToggle();

					}

					// override event
					event.preventDefault();

				}

			}

		});

	};

} // end CommentPress setup navigation column class



/**
 * Create CommentPress setup content class
 */
CommentPress.setup.content = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress setup content.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

		// title
		me.title_links();

		// textblocks
		me.textblocks();

		// textblock paragraph markers
		me.para_markers();

		// textblock comment icons
		me.comment_icons();

		// internal links
		me.links_in_textblocks();

		// footnotes
		me.footnotes_compatibility();

	};



	/**
	 * Set up actions on the title
	 *
	 * @return void
	 */
	this.title_links = function() {

		/**
		 * Clicking on the page/post title
		 *
		 * @return false
		 */
		$('#container').on( 'click', '.post_title a', function( event ) {

			// override event
			event.preventDefault();

			// get text signature
			var text_sig = '';
			//console.log( text_sig );

			// use function
			CommentPress.theme.viewport.align_content( text_sig, 'marker' );

		});

	};



	/**
	 * Set up actions on the textblocks
	 *
	 * @return void
	 */
	this.textblocks = function() {

		// if mobile, we don't hide textblock meta
		if ( cp_is_mobile == '0' ) {

			// have we explicitly hidden textblock meta?
			if ( cp_textblock_meta == '0' ) {

				/**
				 * Add a class to the textblock when mouse is over it
				 *
				 * @return void
				 */
				$('#container').on( 'mouseover', '.textblock', function( event ) {
					$(this).addClass('textblock-in');
				});

				/**
				 * Remove class from the textblock when mouse moves out of it
				 *
				 * @return void
				 */
				$('#container').on( 'mouseout', '.textblock', function( event ) {
					$(this).removeClass('textblock-in');
				});

			}

		}

		/**
		 * Clicking on the textblock
		 *
		 * @return void
		 */
		$('#container').on( 'click', '.textblock', function( event ) {

			// define vars
			var text_sig;

			// get text signature
			text_sig = $(this).prop('id');
			//console.log( text_sig );

			// remove leading #
			text_sig = text_sig.split('textblock-')[1];

			// use function
			CommentPress.theme.viewport.align_content( text_sig, CommentPress.settings.textblock.getMarkerMode() );

			// broadcast action
			$(document).trigger( 'commentpress-textblock-clicked' );

		});

	};



	/**
	 * Set up actions on the "paragraph" icons to the left of a textblock
	 *
	 * @return void
	 */
	this.para_markers = function() {

		/**
		 * Clicking on the paragraph
		 *
		 * @return false
		 */
		$('#container').on( 'click', 'span.para_marker a', function( event ) {

			// override event
			event.preventDefault();

			// broadcast action
			$(document).trigger( 'commentpress-paramarker-clicked' );

		});

		/**
		 * Rolling onto the paragraph icon
		 *
		 * @return void
		 */
		$('#container').on( 'mouseenter', 'span.para_marker a', function( event ) {

			// define vars
			var target;

			// get target item
			target = $(this).parent().next().children('.comment_count');
			//console.log( target );

			target.addClass( 'js-hover' );

		});

		/**
		 * Rolling off the paragraph icon
		 *
		 * @return void
		 */
		$('#container').on( 'mouseleave', 'span.para_marker a', function( event ) {

			// define vars
			var target;

			// get target item
			target = $(this).parent().next().children('.comment_count');
			//console.log( target );

			target.removeClass( 'js-hover' );

		});

	};



	/**
	 * Set up clicks on comment icons attached to comment-blocks in post/page
	 *
	 * @return void
	 */
	this.comment_icons = function() {

		/**
		 * Clicking on the little comment icon
		 *
		 * @return false
		 */
		$('#container').on( 'click', '.commenticonbox', function( event ) {

			// define vars
			var text_sig;

			// override event
			event.preventDefault();

			// prevent bubbling
			event.stopPropagation();

			// get text signature
			text_sig = $(this).children('a.para_permalink').prop('href').split('#')[1];
			//console.log( text_sig );

			// use function
			CommentPress.theme.viewport.align_content( text_sig, 'auto' );

			// broadcast action
			$(document).trigger( 'commentpress-commenticonbox-clicked' );

		});

		/**
		 * Clicking on the little comment icon
		 *
		 * @return false
		 */
		$('#container').on( 'click', 'a.para_permalink', function( event ) {

			// override event
			event.preventDefault();

		});

		/**
		 * Rolling onto the little comment icon
		 *
		 * @return void
		 */
		$('#container').on( 'mouseenter', 'a.para_permalink', function( event ) {

			// define vars
			var text_sig;

			// get text signature
			text_sig = $(this).prop('href').split('#')[1];
			//console.log( text_sig );

			$('span.para_marker a#' + text_sig).addClass( 'js-hover' );

		});

		/**
		 * Rolling off the little comment icon
		 *
		 * @return void
		 */
		$('#container').on( 'mouseleave', 'a.para_permalink', function( event ) {

			// define vars
			var text_sig;

			// get text signature
			text_sig = $(this).prop('href').split('#')[1];
			//console.log( text_sig );

			$('span.para_marker a#' + text_sig).removeClass( 'js-hover' );

		});

	};



	/**
	 * Set up paragraph links: cp_para_link is a class writers can use
	 * in their markup to create nicely scrolling links within their pages
	 *
	 * @return void
	 */
	this.links_in_textblocks = function() {

		/**
		 * Clicking on links to paragraphs
		 *
		 * @return false
		 */
		$('#container').on( 'click', 'a.cp_para_link', function( event ) {

			// define vars
			var text_sig;

			// override event
			event.preventDefault();

			// get text signature
			text_sig = $(this).prop('href').split('#')[1];
			//console.log(text_sig);

			// use function
			CommentPress.theme.viewport.align_content( text_sig, 'auto' );

		});

	};



	/**
	 * Set up footnote links for various plugins
	 *
	 * @return void
	 */
	this.footnotes_compatibility = function() {

		/**
		 * ---------------------------------------------------------------------
		 * Back links
		 * ---------------------------------------------------------------------
		 */

		/**
		 * Clicking on reverse links in FD-Footnotes and WP_Footnotes
		 *
		 * @return false
		 */
		$('#container').on( 'click', 'span.footnotereverse a, a.footnote-back-link', function( event ) {

			// define vars
			var target;

			// override event
			event.preventDefault();

			// get target
			target = $(this).prop('href').split('#')[1];
			//console.log(target);

			// use function for offset
			$.quick_scroll_page( '#' + target, 100 );

		});

		/**
		 * Clicking on reverse links in Simple Footnotes plugin
		 *
		 * @return false
		 */
		$('#container').on( 'click', '.simple-footnotes ol li > a', function( event ) {

			// define vars
			var target;

			// get target
			target = $(this).prop('href');
			//console.log(target);

			// is it a backlink?
			if ( target.match('#return-note-' ) ) {

				// override event
				event.preventDefault();

				// remove url
				target = target.split('#')[1];

				// use function for offset
				$.quick_scroll_page( '#' + target, 100 );

			}

		});

		/**
		 * ---------------------------------------------------------------------
		 * Footnote links
		 * ---------------------------------------------------------------------
		 */

		/**
		 * Clicking on footnote links in FD-Footnotes, WP-Footnotes, Simple Footnotes and ZotPress
		 *
		 * @return false
		 */
		$('#container').on( 'click', 'a.simple-footnote, sup.footnote a, sup a.footnote-identifier-link, a.zp-ZotpressInText', function( event ) {

			// define vars
			var target;

			// override event
			event.preventDefault();

			// get target
			target = $(this).prop('href').split('#')[1];
			//console.log(target);

			// use function for offset
			$.quick_scroll_page( '#' + target, 100 );

		});

	};



	/**
	 * Workflow tabs
	 *
	 * @param str content_min_height The min-height CSS declaration
	 * @param str content_padding_bottom The content wrapper padding-bottom CSS declaration
	 * @return void
	 */
	this.workflow_tabs = function( content_min_height, content_padding_bottom ) {

		// hide workflow content
		$('#literal .post').css( 'display', 'none' );
		$('#original .post').css( 'display', 'none' );

		/**
		 * Clicking on the workflow tabs
		 *
		 * @return false
		 */
		$('#container').on( 'click', 'content-tabs li h2 a', function( event ) {

			// define vars
			var target_id;

			// override event
			event.preventDefault();

			// hide others and show corresponding item

			// get href
			target_id = this.href.split('#')[1];
			//console.log( target_id );

			// hide all
			$('.post').css( 'display', 'none' );

			// remove content min-height
			$('.workflow-wrapper').css( 'min-height', '0' );

			// remove bottom padding
			$('.workflow-wrapper').css( 'padding-bottom', '0' );

			// set min-height of target
			$('#' + target_id + '.workflow-wrapper').css( 'min-height', content_min_height );

			// set padding-bottom of target
			$('#' + target_id + '.workflow-wrapper').css( 'padding-bottom', content_padding_bottom );

			// show it
			$('#' + target_id + ' .post').css( 'display', 'block' );

			// amend css of list items to mimic tabs
			$('#content-tabs li').removeClass( 'default-content-tab' );
			$(this).parent().parent().addClass( 'default-content-tab' );

		});

	};

} // end CommentPress setup content class



/**
 * Create CommentPress setup comments column class
 */
CommentPress.setup.comments = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress setup comments column.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

		// column header
		me.header();

		// minimiser button
		me.minimiser();

		// comment block permalinks
		me.comment_block_permalinks();

		// comment permalinks
		me.comment_permalinks();

		// comment permalink copy links
		me.comment_permalink_copy_links();

		// comment rollovers (disabled because only the modern theme uses this)
		//me.comment_rollovers();

	};



	/**
	 * Set up "Comments" tab header
	 *
	 * @return false
	 */
	this.header = function() {

		/**
		 * Clicking on the Comments Header
		 *
		 * @return false
		 */
		$('#sidebar').on( 'click', '#comments_header h2 a', function( event ) {

			// override event
			event.preventDefault();

			// activate it (this will become a theme method)
			CommentPress.theme.sidebars.activate_sidebar('comments');

		});

	};



	/**
	 * Set up "Comments" tab minimise button
	 *
	 * @return false
	 */
	this.minimiser = function() {

		/**
		 * Clicking on the minimise comments icon
		 *
		 * @return void
		 */
		$('#sidebar').on( 'click', '#cp_minimise_all_comments', function( event ) {

			// override event
			event.preventDefault();

			// slide all paragraph comment wrappers up
			$('#comments_sidebar div.paragraph_wrapper').slideUp();

			// unhighlight paragraphs
			$.unhighlight_para();

		});

	};



	/**
	 * Set up "Comments" tab "X Comments on Paragraph Y" links
	 *
	 * These links are also permalinks to the "comment block" - i.e. the section
	 * of the Comments column that holds the comments on a particular textblock.
	 *
	 * @return void
	 */
	this.comment_block_permalinks = function() {

		// only on normal cp pages
		if ( cp_special_page == '1' ) { return; }

		// set pointer
		$('a.comment_block_permalink').css( 'cursor', 'pointer' );

		/**
		 * Clicks on "Comments" tab "X Comments on Paragraph Y" links
		 *
		 * @param object event The clicked object
		 * @return false
		 */
		$('#comments_sidebar').on( 'click', 'a.comment_block_permalink', function( event ) {

			// define vars
			var text_sig, para_wrapper, comment_list, opening, visible, textblock,
				post_id, para_id, para_num, has_form;

			// override event
			event.preventDefault();

			// get text_sig
			text_sig = $(this).parent().prop( 'id' ).split('para_heading-')[1];
			//console.log( 'text_sig: ' + text_sig );

			// get para wrapper
			para_wrapper = $(this).parent().next('div.paragraph_wrapper');

			// get comment list
			comment_list = $( '#para_wrapper-' + text_sig ).find('ol.commentlist' );

			// init
			opening = false;

			// get visibility
			visible = para_wrapper.css('display');

			// override
			if ( visible == 'none' ) { opening = true; }

			// did we get one at all?
			if ( 'undefined' !== typeof text_sig ) {

				//console.log( opening );
				//alert( 'comment_block_permalink click' );

				// if not the whole page or pings...
				if( text_sig !== '' && text_sig != 'pingbacksandtrackbacks' ) {

					// get text block
					textblock = $('#textblock-' + text_sig);

					// only if opening
					if ( opening ) {

						// unhighlight paragraphs
						$.unhighlight_para();

						// highlight this paragraph
						$.highlight_para( textblock );

						// scroll page
						$.scroll_page( textblock );

					} else {

						// if encouraging commenting
						if ( cp_promote_reading == '0' ) {

							// closing with a comment form
							if ( $( '#para_wrapper-' + text_sig ).find('#respond' )[0] ) {

								// unhighlight paragraphs
								$.unhighlight_para();

							} else {

								// if we have no comments, always highlight
								if ( !comment_list[0] ) {

									// unhighlight paragraphs
									$.unhighlight_para();

									// highlight this paragraph
									$.highlight_para( textblock );

									// scroll page
									$.scroll_page( textblock );

								}

							}

						} else {

							// if ours is highlighted
							if ( $.is_highlighted( textblock ) ) {

								// unhighlight paragraphs
								$.unhighlight_para();

							}

						}

					}

				} else {

					// unhighlight paragraphs
					$.unhighlight_para();

					// only scroll if not pings
					if ( text_sig != 'pingbacksandtrackbacks' ) {

						// scroll to top
						CommentPress.theme.viewport.scroll_to_top( 0, cp_scroll_speed );

						// toggle page highlight flag
						CommentPress.settings.page.toggle_highlight();

					}

				}

			} // end defined check

			// if encouraging commenting...
			if ( cp_promote_reading == '0' && text_sig != 'pingbacksandtrackbacks' ) {

				// are comments open?
				if ( cp_comments_open == 'y' ) {

					// get comment post ID
					post_id = $('#comment_post_ID').prop('value');
					para_id = $('#para_wrapper-' + text_sig + ' .reply_to_para').prop('id');
					para_num = para_id.split('-')[1];

					// do we have the comment form?
					has_form = $( '#para_wrapper-' + text_sig ).find( '#respond' )[0];

					// if we have a comment list
					if ( comment_list.length > 0 && comment_list[0] ) {

						//console.log( 'has' );

						// are we closing with no reply form?
						if ( !opening && !has_form ) {

							// skip moving form

						} else {

							// move form to para
							addComment.moveFormToPara( para_num, text_sig, post_id );

						}

					} else {

						// if we have no respond
						if ( !has_form ) {

							//console.log( 'none' );
							para_wrapper.css('display','none');
							opening = true;

						}

						// move form to para
						addComment.moveFormToPara( para_num, text_sig, post_id );

					}

				}

			}

			// toggle next paragraph_wrapper
			para_wrapper.slideToggle( 'slow', function() {

				// only scroll if opening
				if ( opening ) {

					// scroll comments
					$.scroll_comments( $('#para_heading-' + text_sig), cp_scroll_speed );

				}

			});

		});

	};



	/**
	 * Set up actions on the Comment permalinks
	 *
	 * @return void
	 */
	this.comment_permalinks = function() {

		/**
		 * Clicking on the comment permalink
		 *
		 * @return void
		 */
		$('#comments_sidebar').on( 'click', '.comment_permalink', function( event ) {

			// define vars
			var comment_id, header_offset, text_sig;

			// override event
			event.preventDefault();

			// get comment id
			comment_id = this.href.split('#')[1];

			// if special page
			if ( cp_special_page == '1' ) {

				// get offset
				header_offset = CommentPress.theme.header.get_offset();

				// scroll to comment
				$.scrollTo(
					$('#'+comment_id),
					{
						duration: cp_scroll_speed,
						axis:'y',
						offset: header_offset
					}
				);

			} else {

				// clear other highlights
				$.unhighlight_para();

				// get text sig
				text_sig = $.get_text_sig_by_comment_id( '#' + comment_id );

				// if not a pingback...
				if ( text_sig != 'pingbacksandtrackbacks' ) {

					// scroll page to it
					$.scroll_page_to_textblock( text_sig );

				}

				// scroll comments
				$.scroll_comments( $('#'+comment_id), cp_scroll_speed );

			}

		});

	};



	/**
	 * Set up actions on the "Link" icons to the left of a comment permalink
	 *
	 * At present, these trigger a Javascript dialog, populated with the comment
	 * permalink for people to copy. Migrate this to showing the permalink in
	 * the browser's location bar.
	 *
	 * @return void
	 */
	this.comment_permalink_copy_links = function() {

		/**
		 * Click on the copy icon
		 *
		 * @return void
		 */
		$('#comments_sidebar').on( 'click', '.comment_permalink_copy', function( event ) {

			// define vars
			var url;

			// get selection
			url = $( this ).parent().attr('href');
			//console.log( url );

			// did we get one?
			if ( url ) {

				// show dialog
				window.prompt( "Copy this link, then paste into where you need it", url );

			}

		});

	};



	/**
	 * Handle comment "rollovers"
	 *
	 * @since 3.7
	 */
	this.comment_rollovers = function() {

		/**
		 * Add a class when rolling onto the comment
		 */
		$('#comments_sidebar').on( 'mouseenter', '.comment-wrapper', function( event ) {
			$(this).addClass( 'background-highlight' );
		});

		/**
		 * Remove the class when rolling off the comment
		 */
		$('#comments_sidebar').on( 'mouseleave', '.comment-wrapper', function( event ) {
			$(this).removeClass( 'background-highlight' );
		});

	};



	/**
	 * Highlight the comment
	 *
	 * @param object comment The $ comment object
	 * @return void
	 */
	this.highlight = function( comment ) {

		// add notransition class
		comment.addClass( 'notransition' );

		// remove existing classes
		if ( comment.hasClass( 'comment-fade' ) ) {
			comment.removeClass( 'comment-fade' );
		}
		if ( comment.hasClass( 'comment-highlighted' ) ) {
			comment.removeClass( 'comment-highlighted' );
		}

		// highlight
		comment.addClass( 'comment-highlighted' );

		// remove notransition class
		comment.removeClass( 'notransition' );

		// trigger reflow
		comment.height();

		// animate to existing bg (from css file)
		comment.addClass( 'comment-fade' );

	}

} // end CommentPress setup comments column class



/**
 * Create CommentPress setup activity column class
 */
CommentPress.setup.activity = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	/**
	 * Initialise CommentPress setup activity column.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

		// column header
		me.header();

		// minimiser button
		me.minimiser();

		// column headings
		me.headings();

		// "see in context" links
		me.see_in_context_links();

	};



	/**
	 * Set up "Activity" tab header
	 *
	 * @return false
	 */
	this.header = function() {

		/**
		 * Clicking on the Activity Header
		 *
		 * @return false
		 */
		$('#sidebar').on( 'click', '#activity_header h2 a', function( event ) {

			// override event
			event.preventDefault();

			// activate it (this will become a theme method)
			CommentPress.theme.sidebars.activate_sidebar( 'activity' );

		});

	};



	/**
	 * Set up "Activity" tab minimise button
	 *
	 * @return false
	 */
	this.minimiser = function() {

		/**
		 * Clicking on the minimise activities icon
		 *
		 * @return void
		 */
		$('#sidebar').on( 'click', '#cp_minimise_all_activity', function( event ) {

			// override event
			event.preventDefault();

			// slide all paragraph comment wrappers up
			$('#activity_sidebar div.paragraph_wrapper').slideUp();

		});

	};



	/**
	 * Set up "Activity" tab headings
	 *
	 * @return false
	 */
	this.headings = function() {

		// set pointer
		$('h3.activity_heading').css( 'cursor', 'pointer' );

		/**
		 * Activity column headings click
		 *
		 * @return void
		 */
		$('#activity_sidebar').on( 'click', 'h3.activity_heading', function( event ) {

			// define vars
			var para_wrapper;

			// override event
			event.preventDefault();

			// get para wrapper
			para_wrapper = $(this).next('div.paragraph_wrapper');
			//console.log( para_wrapper );

			// set width to prevent rendering error
			para_wrapper.css( 'width', $(this).parent().css( 'width' ) );

			// toggle next paragraph_wrapper
			para_wrapper.slideToggle( 'slow', function() {

				// when finished, reset width to auto
				para_wrapper.css( 'width', 'auto' );

			} );

		});

	};



	/**
	 * Set up "Activity" tab "See In Context" links
	 *
	 * @return void
	 */
	this.see_in_context_links = function() {

		// allow links to work when not on commentable page
		// NOTE: is this right?
		if ( cp_special_page == '1' ) { return; }

		/**
		 * Clicking on the "See In Context" links
		 *
		 * @return void
		 */
		$('#activity_sidebar').on( 'click', 'a.comment_on_post', function( event ) {

			// define vars
			var comment_id, comment, para_wrapper_array, item, header_offset, text_sig;

			// override event
			event.preventDefault();

			// show comments sidebar
			CommentPress.theme.sidebars.activate_sidebar( 'comments' );

			// get comment id
			comment_id = this.href.split('#')[1];

			// get comment
			comment = $('#'+comment_id);

			//console.log( comment );

			// get array of parent paragraph_wrapper divs
			para_wrapper_array = comment
										.parents('div.paragraph_wrapper')
										.map( function () {
											return this;
										});

			// did we get one?
			if ( para_wrapper_array.length > 0 ) {

				// get the item
				item = $(para_wrapper_array[0]);

				// show block
				item.show();

				// if special page
				if ( cp_special_page == '1' ) {

					// get offset
					header_offset = CommentPress.theme.header.get_offset();

					// scroll to comment
					$.scrollTo(
						comment,
						{
							duration: cp_scroll_speed,
							axis:'y',
							offset: header_offset
						}
					);

				} else {

					// clear other highlights
					$.unhighlight_para();

					// highlight para
					text_sig = item.prop('id').split('-')[1];

					// scroll page to it
					$.scroll_page_to_textblock( text_sig );

					//console.log( '#li-comment-' + comment_id );

					// add highlight class
					//$( '#li-comment-' + comment_id ).addClass( 'flash-comment' );

					// scroll to new comment
					$('#comments_sidebar .sidebar_contents_wrapper').scrollTo(
						comment,
						{
							duration: cp_scroll_speed,
							axis: 'y',
							onAfter: function() {

								// highlight comment
								CommentPress.setup.comments.highlight( comment );

							}
						}
					);

				}

			}

		});

	};

} // end CommentPress setup activity column class



// do pre-page-rendered stuff
CommentPress.settings.DOM.init();



/**
 * jQuery wrapper
 *
 * This wrapper ensures that jQuery can be addressed using the $ shorthand from
 * anywhere within the script.
 *
 * @since 3.0
 */
;( function( $ ) {



	// our currently highlighted paragraph
	var highlighted_para = '';

	/**
	 * Highlight the current paragraph
	 *
	 * @since 3.0
	 */
	$.highlight_para = function( element ) {

		// bail if we don't have a proper element
		if ( typeof( element ) != 'object' ) { return; }

		// amend p tag css
		element.addClass( 'selected_para' );

	}



	/**
	 * Unhighlight all text
	 *
	 * @since 3.0
	 */
	$.unhighlight_para = function() {

		var highlighted_paras = $('.textblock');

		// remove class from all
		highlighted_paras.removeClass( 'selected_para' );

	}



	/**
	 * Get the element which is currently highlighted
	 *
	 * @since 3.0
	 *
	 * @return string highlighted_para The highlighted paragraph
	 */
	$.get_highlighted_para = function() {

		// --<
		return highlighted_para;

	}



	/**
	 * Test if the element is currently highlighted
	 *
	 * @since 3.0
	 *
	 * @param object element The jQuery element to test
	 * @return boolean True if highlighted, false otherwise
	 */
	$.is_highlighted = function( element ) {

		// bail if we don't have a proper element
		if ( typeof( element ) != 'object' ) { return false; }

		// is our item already highlighted?
		if ( element.hasClass('selected_para') ) {

			// --<
			return true;

		} else {

			// --<
			return false;

		}

	}



	/**
	 * Utility replacement for PHP's in_array
	 *
	 * @since 3.0
	 *
	 * @param mixed needle The item to search for
	 * @param array haystack The array to search
	 * @param boolean argStrict If true, will take variable type into account
	 * @return boolean found True if found, false otherwise
	 */
	$.in_array = function( needle, haystack, argStrict ) {

		// http://kevin.vanzonneveld.net
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
	 * A nifty JS array utility to remove a specified value
	 *
	 * @since 3.0
	 *
	 * @param mixed item The item to remove
	 * @param array sourceArray The array to remove the item from
	 * @return array sourceArray The modified array
	 */
	$.remove_from_array = function( item, sourceArray ) {

		// loop through the array
		for ( var i = 0; i < sourceArray.length; i++ ) {

			// remove our item
			if ( item === sourceArray[i] ) {

				// splice it at that point
				sourceArray.splice(i, 1);

				// kick out
				break;

			}

		}

		// --<
		return sourceArray;

	}



	/**
	 * Utility replacement for PHP's is_object
	 *
	 * @since 3.0
	 *
	 * @param mixed mixed_var The item to test
	 * @return boolean True if item is object, false otherwise
	 */
	$.is_object = function ( mixed_var ) {

		// distiguish between arrays and objects
		if( mixed_var instanceof Array ) {

			// is an array
			return false;

		} else {

			// not null and is object
			return ( mixed_var !== null ) && ( typeof( mixed_var ) == 'object' );
		}

	}



	/**
	 * Test if a function exists without throwing a Reference Error
	 *
	 * @since 3.0
	 *
	 * @param string function_name The name of the function
	 * @return boolean True if the function exists, false otherwise
	 */
	$.is_function_defined = function ( function_name ) {

		// use eval
		if ( eval( 'typeof(' + function_name + ') == typeof(Function)' ) ) {

			// --<
			return true;

		}

		// --<
		return false;

	}



	/**
	 * Utility to strip 'px' off css values
	 *
	 * @since 3.0
	 *
	 * @param string pix The CSS string (eg, '20px')
	 * @return int px The numeric value (eg, 20)
	 */
	$.px_to_num = function( pix ) {

		// --<
		return parseInt( pix.substring( 0, (pix.length - 2) ) );

	};



	/**
	 * Utility to return zero when css values may be NaN in IE
	 *
	 * @since 3.0
	 *
	 * @param mixed strNum A numeric value that we want to modify
	 * @return int The numeric value of strNum
	 */
	$.css_to_num = function( strNum ) {

		// http://mattstark.blogspot.com/2009/05/javascript-jquery-plugin-to-fade.html
		if (strNum && strNum != "") {

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
	 * @param string message The message to show
	 */
	$.frivolous = function( message ) {

		// do a simple alert
		alert( message );

	}



	/**
	 * Get currently highlighted menu item ID
	 *
	 * @since 3.0
	 *
	 * @return string current_menu_item The numeric ID of the menu item
	 */
	$.get_current_menu_item_id = function() {

		// declare vars
		var current_menu_item = 0,
			current_menu_obj, current_item_id,
			current_item_classes, current_item_class;

		// get highlighted menu item object
		current_menu_obj = $('.current_page_item');
		//console.log( 'current_menu_item:' );
		//console.log( current_menu_item );

		// did we get one?
		if ( current_menu_obj.length > 0 ) {

			// get ID, if present
			current_item_id = current_menu_obj.prop('id');
			//console.log( 'current_item_id:' );
			//console.log( current_item_id );

			// if we do have an ID...
			if ( current_item_id.length > 0 ) {

				// it's a WP custom menu
				current_menu_item = current_item_id.split('-')[2];

			} else {

				// it's a WP page menu
				current_item_class = current_menu_obj.prop('class');

				// get classes
				current_item_classes = current_item_class.split(' ');

				// loop to find the one we want
				for (var i = 0, item; item = current_item_classes[i++];) {
					if ( item.match( 'page-item-' ) ) {
						current_menu_item = item.split('-')[2];
						break;
					}
				}

			}

		}

		//console.log( 'cpajax_current_menu_item: ' + cpajax_current_menu_item );

		// --<
		return current_menu_item;

	}



	/**
	 * Get text signature by comment id
	 *
	 * @param object cid The CSS ID of the comment
	 * @return string text_sig The text signature
	 */
	$.get_text_sig_by_comment_id = function( cid ) {

		// define vars
		var comment_id, para_wrapper_array, text_sig, item;

		// init
		text_sig = '';

		// are we passing the full id?
		if ( cid.match( '#comment-' ) ) {

			// get comment ID
			comment_id = parseInt( cid.split('#comment-')[1] );

		}

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

			// move form to para
			text_sig = item.prop('id').split('-')[1];

		}

		// --<
		return text_sig;

	}



	/**
	 * Page load prodecure for special pages with comments in content
	 *
	 * @return void
	 */
	$.on_load_scroll_to_comment = function() {

		// define vars
		var url, comment_id, comment;

		// if there is an anchor in the URL...
		url = document.location.toString();

		// do we have a comment permalink?
		if ( url.match( '#comment-' ) ) {

			// get comment ID
			comment_id = url.split('#comment-')[1];

			// get comment in DOM
			comment = $( '#comment-' + comment_id );

			// did we get one?
			if ( comment.length ) {

				// only scroll if not mobile (but allow tablets)
				if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

					// scroll to new comment
					$.scrollTo(
						comment,
						{
							duration: cp_scroll_speed,
							axis:'y',
							offset: CommentPress.theme.header.get_offset()
						}
					);

				}

			}

		}

	}



	/**
	 * Scroll comments to target
	 *
	 * @param object target The target to scroll to
	 * @param integer speed The duration of the scroll
	 * @param string flash Whether or not to "flash" the comment
	 * @return void
	 */
	$.scroll_comments = function( target, speed, flash ) {

		// preserve compatibility with older calls
		switch(arguments.length) {
			case 2: flash = 'noflash'; break;
			case 3: break;
			default: throw new Error('illegal argument count');
		}

		//console.log( 'scroll: ' + flash );

		// only scroll if not mobile (but allow tablets)
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

			// either flash at the end or not..
			if ( flash == 'flash' ) {

				//console.log( target.prop( 'id' ).split( '-' )[1] );

				// add highlight class
				//$( '#li-comment-' + target.prop( 'id' ).split( '-' )[1] ).addClass( 'flash-comment' );

				// scroll to new comment
				$('#comments_sidebar .sidebar_contents_wrapper').scrollTo(
					target,
					{
						duration: speed,
						axis: 'y',
						onAfter: function() {

							// highlight header
							CommentPress.setup.comments.highlight( target );

							// broadcast
							$(document).trigger( 'commentpress-comments-scrolled' );

						}
					}
				);

			} else {

				// scroll comment area to para heading
				$('#comments_sidebar .sidebar_contents_wrapper').scrollTo(
					target,
					{
						duration: speed,
						onAfter: function() {

							// broadcast
							$(document).trigger( 'commentpress-comments-scrolled' );

						}
					}
				);

			}

		}

	}



	/**
	 * Scroll page to target
	 *
	 * @param object target The object to scroll to
	 */
	$.scroll_page = function( target ) {

		//console.log( target );

		// bail if we didn't get a valid target
		if ( typeof target === 'undefined' ) { return; }

		// only scroll if not mobile (but allow tablets)
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

			// scroll page
			$.scrollTo(
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
	 * Scroll page to target with passed duration param
	 *
	 * @param object target The object to scroll to
	 * @param integer duration The duration of the scroll
	 */
	$.quick_scroll_page = function( target, duration ) {

		// bail if we didn't get a valid target
		if ( typeof target === 'undefined' ) { return; }

		// only scroll if not mobile (but allow tablets)
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

			// scroll page
			$.scrollTo(
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
	 * Scroll to textblock
	 *
	 * @param string text_sig The text signature to scroll to
	 * @return void
	 */
	$.scroll_page_to_textblock = function( text_sig ) {

		// define vars
		var textblock;

		// if not the whole page...
		if( text_sig !== '' ) {

			// get text block
			textblock = $('#textblock-' + text_sig);

			// highlight this paragraph
			$.highlight_para( textblock );

			// scroll page
			$.scroll_page( textblock );

		} else {

			// only scroll if page is not highlighted
			if ( !CommentPress.settings.page.toggle_highlight() ) {

				// scroll to top
				CommentPress.theme.viewport.scroll_to_top( 0, cp_scroll_speed );

			}

			// toggle page highlight flag
			CommentPress.settings.page.toggle_highlight();

		}

	}



})( jQuery );



/**
 * Initialise all setups
 *
 * @return void
 */
function commentpress_setup_init() {

	// setup navigation
	CommentPress.setup.navigation.init();

	// setup content
	CommentPress.setup.content.init();

	// setup comments column
	CommentPress.setup.comments.init();

	// setup activity column
	CommentPress.setup.activity.init();

	// broadcast
	jQuery(document).trigger( 'commentpress-initialised' );

}



/**
 * Define what happens when the page is ready
 *
 * @return void
 */
jQuery(document).ready(function($) {

	// initialise
	commentpress_setup_init()

}); // end document.ready()



