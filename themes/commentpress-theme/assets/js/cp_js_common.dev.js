/* 
================================================================================
Common Javascript
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
*/



// test for our localisation object
if ( 'undefined' !== typeof CommentpressSettings ) {

	// set our vars
	var cp_wp_adminbar = CommentpressSettings.cp_wp_adminbar;
	var cp_bp_adminbar = CommentpressSettings.cp_bp_adminbar;
	var cp_comments_open = CommentpressSettings.cp_comments_open;
	var cp_special_page = CommentpressSettings.cp_special_page;
	var cp_tinymce = CommentpressSettings.cp_tinymce;
	var cp_promote_reading = CommentpressSettings.cp_promote_reading;
	var cp_is_mobile = CommentpressSettings.cp_is_mobile;
	var cp_is_touch = CommentpressSettings.cp_is_touch;
	var cp_is_tablet = CommentpressSettings.cp_is_tablet;
	var cp_cookie_path = CommentpressSettings.cp_cookie_path;
	var cp_multipage_page = CommentpressSettings.cp_multipage_page;
	var cp_template_dir = CommentpressSettings.cp_template_dir;
	var cp_plugin_dir = CommentpressSettings.cp_plugin_dir;
	var cp_toc_chapter_is_page = CommentpressSettings.cp_toc_chapter_is_page;
	var cp_show_subpages = CommentpressSettings.cp_show_subpages;
	var cp_default_sidebar = CommentpressSettings.cp_default_sidebar;
	var cp_is_signup_page = CommentpressSettings.cp_is_signup_page;
	var cp_scroll_speed = CommentpressSettings.cp_js_scroll_speed;
	var cp_min_page_width = CommentpressSettings.cp_min_page_width;

}



// browser detection
var msie = jQuery.browser.msie;
var msie6 = jQuery.browser.msie && jQuery.browser.version == "6.0";

// define utility globals
var cp_wp_adminbar_height = 28;
var cp_book_header_height;
var cp_header_animating = false;

// set toc on top flag
var cp_toc_on_top = 'n';

// page defaults to 'not-highlighted'
var page_highlight = false;

// get state of header
var cp_header_minimised = jQuery.cookie( 'cp_header_minimised' );
if ( cp_header_minimised === undefined || cp_header_minimised === null ) {
	cp_header_minimised = 'n';
}

// get state of sidebar
var cp_sidebar_minimised = jQuery.cookie( 'cp_sidebar_minimised' );
if ( cp_sidebar_minimised === undefined || cp_sidebar_minimised === null ) {
	cp_sidebar_minimised = 'n';
}

// get container original top
var cp_container_top_max = jQuery.cookie( 'cp_container_top_max' );
if ( cp_container_top_max === undefined || cp_container_top_max === null ) {
	cp_container_top_max = 108;
}

// get header offset
var cp_container_top_min = jQuery.cookie( 'cp_container_top_min' );
if ( cp_container_top_min === undefined || cp_container_top_min === null ) {
	cp_container_top_min = 108;
}

// is the buddypress bar shown?
if ( cp_bp_adminbar == 'y' ) {

	// amend to height of buddypress bar
	cp_wp_adminbar_height = 25;
	
	// act as if admin bar were there
	cp_wp_adminbar = 'y';
	
	// from here on, things work as if the WP admin bar were functional...
	
}

// is the admin bar shown?
if ( cp_wp_adminbar == 'y' ) {
	
	// bump them up by the height of the admin bar
	cp_container_top_max = parseInt( cp_container_top_max ) + cp_wp_adminbar_height;
	cp_container_top_min = parseInt( cp_container_top_min ) + cp_wp_adminbar_height;

}



/** 
 * @description: define what happens before the page is ready - avoid flash of content
 * @todo: 
 *
 */
function cp_page_setup() {

	// init styles
	var styles = '';



	// wrap with js test
	if ( document.getElementById ) {
	
		// open style declaration
		styles += '<style type="text/css" media="screen">';

	
	
		// avoid flash of all-comments hidden elements
		styles += 'ul.all_comments_listing div.item_body { display: none; } ';
	


		// is the admin bar shown?
		if ( cp_wp_adminbar == 'y' ) {
		
			// move down
			styles += '#header { top: ' + cp_wp_adminbar_height + 'px; } ';
			styles += '#book_header { top: ' + (cp_wp_adminbar_height + 32) + 'px; } ';
		
		}
		


		// are subpages to be shown?
		if ( cp_show_subpages == '0' ) {
		
			// avoid flash of hidden elements
			styles += '#toc_sidebar .sidebar_contents_wrapper ul li ul { display: none; } ';
		
		}
		


		// has the header been minimised?
		if ( 
			cp_header_minimised === undefined || 
			cp_header_minimised === null || 
			cp_header_minimised == 'n' 
		) {
		
			// no -> skip
		
			// set tops of divs
			
			// adjust for admin bar
			var cp_container_top = cp_container_top_max;
			
			// is the admin bar shown?
			if ( cp_wp_adminbar == 'y' ) {
				var cp_container_top = cp_container_top_max - cp_wp_adminbar_height;
			}
				
			styles += '#container { top: ' + cp_container_top + 'px; } ';
			styles += '#sidebar { top: ' + cp_container_top_max + 'px; } ';

		} else {
		
			// set visibility of comments
			styles += '#book_header { display: none; } ';

			// adjust for admin bar
			var cp_container_top = cp_container_top_min;
			
			// is the admin bar shown?
			if ( cp_wp_adminbar == 'y' ) {
				var cp_container_top = cp_container_top_min - cp_wp_adminbar_height;
			}
				
			// set tops of divs
			styles += '#container { top: ' + cp_container_top + 'px; } ';
			styles += '#sidebar { top: ' + cp_container_top_min + 'px; } ';

		}
	


		// is this the comments sidebar?
		if ( cp_special_page == '0' ) {
		
			// avoid flash of hidden comments
			styles += '.paragraph_wrapper { display: none; } ';
	
			// avoid flash of hidden comment form
			styles += '#respond { display: none; } ';
		
			// has the sidebar window been minimised?
			if ( cp_sidebar_minimised == 'y' ) {
			
				// set visibility of comments
				styles += '#comments_sidebar .sidebar_contents_wrapper { display: none; } ';
	
			}
		
		}
		
		// on global activity sidebar, avoid flash of hidden comments
		styles += '#activity_sidebar .paragraph_wrapper { display: none; } ';

		
		/*
		// TO DO: make into single cookie
		// has the page been changed?
		if ( jQuery.cookie('cp_page_setup') ) {
		
			// get value
			var cp_page_setup = jQuery.cookie('cp_page_setup');
	
		}
		
		*/
		
		
		
		// has the content column changed?
		if ( jQuery.cookie('cp_container_width') ) {

			// get value
			var cp_container_width = jQuery.cookie('cp_container_width');
	
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
		if ( jQuery.cookie('cp_book_nav_width') ) {

			// get book nav width
			var cp_book_nav_width = jQuery.cookie('cp_book_nav_width');

			// set its width
			styles += '#book_nav div#cp_book_nav { width: ' + cp_book_nav_width + '%; } ';

		}
		


		// has the sidebar window changed?
		if ( jQuery.cookie('cp_sidebar_width') ) {
		
			// set width of sidebar
			styles += '#sidebar { width: ' + jQuery.cookie('cp_sidebar_width') + '%; } ';

		}
	
		// has the sidebar window changed?
		if ( jQuery.cookie('cp_sidebar_left') ) {
		
			// set width of sidebar
			styles += '#sidebar { left: ' + jQuery.cookie('cp_sidebar_left') + '%; } ';

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
	
}

// call page setup function
cp_page_setup();






/** 
 * @description: page load prodecure
 * @todo: 
 *
 */
function commentpress_setup_page_layout() {
	
	// is this the signup page?
	if ( cp_is_signup_page == '1' ) {
	
		// target
		var target = jQuery('#content');
	
	} else {
	
		// target
		var target = jQuery('#page_wrapper');
	
	}
	
	
	
	/** 
	 * @description: sets up the main column, if the id exists
	 * @todo: 
	 *
	 */
	target.each( function(i) {
	
		// assign vars
		var me = jQuery(this);
		var content = jQuery('#content');
		var sidebar = jQuery('#sidebar');
		var footer = jQuery('#footer');
		var book_header = jQuery('#book_header');
		var book_nav_wrapper = jQuery('#book_nav_wrapper');
		var book_nav = jQuery('#cp_book_nav');
		var book_info = jQuery('#cp_book_info');
				
		// store original widths
		var original_content_width = me.width();
		var original_sidebar_width = sidebar.width();
		
		// calculate gap to sidebar
		var gap = sidebar.offset().left - original_content_width;
		
		// if Opera...
		if ( jQuery.browser.opera ) {
		
			// set the position of #content to avoid alsoResize bug
			content.css( 'position', 'static' );
		
		}

		// make page wrapper resizable
		me.resizable({ 
		
			handles: 'e',
			minWidth: cp_min_page_width,
			alsoResize: '#footer',
			//grid: 1, // no sub-pixel weirdness please
			


			// on stop... (note: this doesn't fire on the first go in Opera!)
			start: function( event, ui ) {			
				
				// store original widths
				original_content_width = me.width();
				original_sidebar_width = sidebar.width();
				original_nav_width = book_nav.width();
				//console.log(original_sidebar_width);
				
				// calculate sidebar left
				original_sidebar_left = sidebar.css( "left" );
				gap = sidebar.offset().left - original_content_width;
				
			},
				


			// while resizing...
			resize: function( event, ui ) {
			
				me.css( 'height', 'auto' );
				footer.css( 'height', 'auto' );
			
				// have the sidebar follow
				sidebar.css( 'left', ( me.width() + gap ) + 'px' );
				
				// diff
				var my_diff = original_content_width - me.width();

				// have the sidebar right remain static
				sidebar.css( 'width', ( original_sidebar_width + my_diff ) + 'px' );

				// have the book nav follow
				book_nav.css( 'width', ( original_nav_width - my_diff ) + 'px' ); // diff in css

			},
			


			// on stop... (note: this doesn't fire on the first go in Opera!)
			stop: function( event, ui ) {
				
				// viewport width
				var ww = parseFloat(jQuery(window).width() );
				
				
				
				// get element width
				var width = me.width();
				
				// compensate for webkit
				if ( jQuery.browser.webkit ) { width = width + 1; }
				
				// get percent to four decimal places
				var me_w = parseFloat( Math.ceil( ( 1000000 * parseFloat( width ) / ww ) ) / 10000 );
				//console.log(w);
				
				// set element width
				me.css("width" , me_w + '%');

				// set content width to auto so it resizes properly
				if ( cp_is_signup_page == '0' ) {
					content.css( 'width', 'auto' );
				}



				// get element width
				var width = book_nav.width();
				
				// compensate for webkit
				if ( jQuery.browser.webkit ) { width = width + 1; }
				
				// get percent to four decimal places
				var book_nav_w = parseFloat( Math.ceil( ( 1000000 * parseFloat( width ) / ww ) ) / 10000 );
				//console.log(w);
				
				// set element width
				book_nav.css( 'width', book_nav_w + '%' );

				

				
				// get element width
				var width = sidebar.width();
				
				// compensate for webkit
				if ( jQuery.browser.webkit ) { width = width + 1; }
				
				// get percent to four decimal places
				var sidebar_w = parseFloat( Math.ceil( ( 1000000 * parseFloat( width ) / ww ) ) / 10000 );
				//console.log(w);
				
				// set element width
				sidebar.css( 'width', sidebar_w + '%' );

				

				// get element left
				var left = sidebar.position().left;
				
				// compensate for webkit
				if ( jQuery.browser.webkit ) { left = left + 1; }
				
				// get percent to four decimal places
				var sidebar_l = parseFloat( Math.ceil( ( 1000000 * parseFloat( left ) / ww ) ) / 10000 );

				// set element left
				sidebar.css( 'left', sidebar_l + '%' );
				



				// store this width in cookie
				jQuery.cookie( 
				
					'cp_container_width', 
					me_w.toString(), 
					{ expires: 28, path: cp_cookie_path } 
					
				);
				
				// store nav width in cookie
				jQuery.cookie( 
				
					'cp_book_nav_width', 
					book_nav_w.toString(), 
					{ expires: 28, path: cp_cookie_path } 
					
				);
				
				// store location of sidebar in cookie
				jQuery.cookie( 
				
					'cp_sidebar_left', 
					sidebar_l.toString(), 
					{ expires: 28, path: cp_cookie_path } 
					
				);

				// store width of sidebar in cookie
				jQuery.cookie( 
				
					'cp_sidebar_width', 
					sidebar_w.toString(), 
					{ expires: 28, path: cp_cookie_path } 
					
				);


				
			}
			
		});

	});

}






/** 
 * @description: get header offset
 * @todo: decide whether to use border in offset
 *
 */
function commentpress_get_header_offset() {
	
	/*
	// get offset including border
	var offset = 0 - ( 
		jQuery.px_to_num( jQuery('#container').css('top') ) + 
		jQuery.px_to_num( jQuery('#page_wrapper').css( 'borderTopWidth' ) ) 
	);
	*/
	
	// get header offset
	var offset = 0 - ( jQuery.px_to_num( jQuery('#container').css('top') ) );
	
	// is the admin bar shown?
	if ( cp_wp_adminbar == 'y' ) {
	
		// subtract admin bar height
		offset -= cp_wp_adminbar_height;
	
	}
	
	//console.log( offset );
	
	// --<
	return offset;

}






/** 
 * @description: scroll page to target
 * @todo: 
 *
 */
function commentpress_scroll_page( target ) {

	// if IE6, then we have to scroll #wrapper
	if ( msie6 ) {
		
		// 
		jQuery(window).scrollTo( 0, 0 );

		// scroll container to title
		jQuery('#main_wrapper').scrollTo(
			target, 
			{
				duration: (cp_scroll_speed * 1.5), 
				axis:'y', 
				offset: commentpress_get_header_offset()
			}, function () {
				// when done, make sure page is ok
				jQuery(window).scrollTo( 0, 1 );
			}
		);

	} else {
	
		// only scroll if not mobile (but allow tablets)
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {
	
			// scroll page
			jQuery.scrollTo(
				target, 
				{
					duration: (cp_scroll_speed * 1.5), 
					axis:'y', 
					offset: commentpress_get_header_offset()
				}
			);
			
		}
		
	}
	
}




/** 
 * @description: scroll page to target with passed duration param
 * @todo: 
 *
 */
function cp_quick_scroll_page( target, duration ) {

	// if IE6, then we have to scroll #wrapper
	if ( msie6 ) {
		
		// 
		jQuery(window).scrollTo( 0, 0 );

		// scroll container to title
		jQuery('#main_wrapper').scrollTo(
			target, 
			{
				duration: (duration * 1.5), 
				axis:'y', 
				offset: commentpress_get_header_offset()
			}, function () {
				// when done, make sure page is ok
				jQuery(window).scrollTo( 0, 1 );
			}
		);

	} else {
	
		// only scroll if not mobile (but allow tablets)
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {
	
			// scroll page
			jQuery.scrollTo(
				target, 
				{
					duration: (duration * 1.5), 
					axis:'y', 
					offset: commentpress_get_header_offset()
				}
			);
			
		}
		
	}
	
}




/** 
 * @description: scroll page to top
 * @todo: 
 *
 */
function commentpress_scroll_to_top( target, speed ) {

	// if IE6, then we have to scroll #wrapper
	if ( msie6 ) {
	
		// scroll wrapper to title
		jQuery('#main_wrapper').scrollTo( target, {duration: speed} );

	} else {
	
		// only scroll if not mobile (but allow tablets)
		if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {
		
			// scroll
			jQuery.scrollTo( target, speed );
			
		}
		
	}
	
}




/** 
 * @description: scroll comments to target
 * @todo: 
 *
 */
function cp_scroll_comments( target, speed, flash ) {
	
	// preserve compatibility with older calls
    switch(arguments.length) {
        case 2: flash = 'noflash';
        case 3: break;
        default: throw new Error('illegal argument count')
    }

	//console.log( 'scroll: ' + flash );
	
	// only scroll if not mobile (but allow tablets)
	if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {
	
		// either flash at the end or not..
		if ( flash == 'flash' ) {
		
			// scroll to new comment
			jQuery('#comments_sidebar .sidebar_contents_wrapper').scrollTo(
				target, 
				{
					duration: speed, 
					axis: 'y',
					onAfter: function() {
					
						// highlight header
						cp_flash_comment_header( target );
						
					}
				}
			);
						
		} else {
			
			// scroll comment area to para heading
			jQuery('#comments_sidebar .sidebar_contents_wrapper').scrollTo( target, {duration: speed} );
			
		}
		
	}
	
}




/** 
 * @description: set up comment headers
 * @todo: 
 *
 */
function commentpress_setup_comment_headers() {

	// only on normal cp pages
	if ( cp_special_page == '1' ) { return; }

	// unbind first to allow repeated calls to this function
	jQuery('a.comment_block_permalink').unbind( 'click' );

	// set pointer 
	jQuery('a.comment_block_permalink').css( 'cursor', 'pointer' );

	/** 
	 * @description: comment page headings click
	 * @todo: 
	 *
	 */
	jQuery('a.comment_block_permalink').click( function( event ) {
	
		// override event
		event.preventDefault();
	
		// get text_sig
		var text_sig = jQuery(this).parent().attr( 'id' ).split('para_heading-')[1];
		
		// get para wrapper
		var para_wrapper = jQuery(this).parent().next('div.paragraph_wrapper');
		
		// get comment list
		var comment_list = jQuery( '#para_wrapper-' + text_sig ).find('ol.commentlist' );
		


		// init
		var opening = false;
		
		// get visibility
		var visible = para_wrapper.css('display');
		
		// override
		if ( visible == 'none' ) { opening = true; }
		


		// did we get one at all?
		if( typeof( text_sig ) != 'undefined' ) {
		
			//console.log( opening );
			//alert( 'comment_block_permalink click' );
	
			// if not the whole page or pings...
			if( text_sig != '' && text_sig != 'pingbacksandtrackbacks' ) {
	
				// get text block
				var textblock = jQuery('#textblock-' + text_sig);
									
				// only if opening
				if ( opening ) {
				
					// unhighlight paragraphs
					jQuery.unhighlight_para();
					
					// highlight this paragraph
					jQuery.highlight_para( textblock );
					
					// scroll page
					commentpress_scroll_page( textblock );
					
				} else {
				
					// if encouraging commenting
					if ( cp_promote_reading == '0' ) {
					
						// closing with a comment form
						if ( jQuery( '#para_wrapper-' + text_sig ).find('#respond' )[0] ) {
						
							// unhighlight paragraphs
							jQuery.unhighlight_para();
							
						} else {
						
							// if we have no comments, always highlight
							if ( !comment_list[0] ) {
							
								// unhighlight paragraphs
								jQuery.unhighlight_para();
								
								// highlight this paragraph
								jQuery.highlight_para( textblock );
								
								// scroll page
								commentpress_scroll_page( textblock );
								
							}
							
						}
						
					} else {
						
						// if ours is highlighted
						if ( jQuery.is_highlighted( textblock ) ) {
						
							// unhighlight paragraphs
							jQuery.unhighlight_para();

						}
					
					}
					
				}
					
			} else {
			
				// unhighlight paragraphs
				jQuery.unhighlight_para();
				
				// only scroll if not pings
				if ( text_sig != 'pingbacksandtrackbacks' ) {
			
					// scroll to top
					commentpress_scroll_to_top( 0, cp_scroll_speed );
					
					// toggle page highlight flag
					page_highlight = !page_highlight;
				
				}
				
			}
			
		} // end defined check
				

		
		// if encouraging commenting...
		if ( cp_promote_reading == '0' && text_sig != 'pingbacksandtrackbacks' ) {
		
			// are comments open?
			if ( cp_comments_open == 'y' ) {
		
				// get comment post ID
				var post_id = jQuery('#comment_post_ID').attr('value');
				var para_id = jQuery('#para_wrapper-' + text_sig + ' .reply_to_para').attr('id');
				var para_num = para_id.split('-')[1];
				
				// do we have the comment form?
				var has_form = jQuery( '#para_wrapper-' + text_sig ).find( '#respond' )[0];
			
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
				cp_scroll_comments( jQuery('#para_heading-' + text_sig), cp_scroll_speed );
				
			}
			
		});
		
		
		
		// --<
		return false;

	});

}






/** 
 * @description: clicking on the comment permalink
 * @todo: 
 *
 */
function commentpress_enable_comment_permalink_clicks() {

	// unbind first to allow repeated calls to this function
	jQuery('a.comment_permalink').unbind( 'click' );

	jQuery('a.comment_permalink').click( function( event ) {
	
		// override event
		event.preventDefault();
	
		// get comment id
		var comment_id = this.href.split('#')[1];
		
		// if special page
		if ( cp_special_page == '1' ) {
		
			// get offset
			var header_offset = commentpress_get_header_offset();
	
			// scroll to comment
			jQuery.scrollTo(
				jQuery('#'+comment_id), 
				{
					duration: cp_scroll_speed, 
					axis:'y', 
					offset: header_offset
				}
			);
		
		} else {
	
			// clear other highlights
			jQuery.unhighlight_para();
			
			// get text sig
			var text_sig = cp_get_text_sig_by_comment_id( '#'+comment_id );
			
			// if not a pingback...
			if ( text_sig != 'pingbacksandtrackbacks' ) {
			
				// scroll page to it
				commentpress_scroll_page_to_textblock( text_sig );
			
			}
			
			// scroll comments
			cp_scroll_comments( jQuery('#'+comment_id), cp_scroll_speed );
			
		}
		
		// --<
		return false;
		
	});

}






/** 
 * @description: highlight the comment header
 * @todo: 
 *
 */
function cp_flash_comment_header( comment ) {

	//console.log( 'flash' );

	// get header
	var comment_header = comment.children( '.comment-identifier' );
	//console.log( comment_header.css('backgroundColor') );
	
	if ( !comment_header ) { return; }
					
	// store existing color
	var bg = comment_header.css('backgroundColor');
	
	// animate to highlight
	comment_header.animate({ backgroundColor: "#819565" }, 100, function () {
		
		// animate to white
		comment_header.animate({ backgroundColor: bg }, 1000, function () {
			
			// then do something?
			//console.log( 'flashed' );
		
		});
		
	});
	
}






/** 
 * @description: set up context headers for "activity" tab
 * @todo: 
 *
 */
function commentpress_setup_context_headers() {

	// unbind first to allow repeated calls to this function
	jQuery('h3.activity_heading').unbind( 'click' );

	// set pointer 
	jQuery('h3.activity_heading').css( 'cursor', 'pointer' );

	/** 
	 * @description: activity column headings click
	 * @todo: 
	 *
	 */
	jQuery('h3.activity_heading').click( function( event ) {
	
		// override event
		event.preventDefault();
	
		// get para wrapper
		var para_wrapper = jQuery(this).next('div.paragraph_wrapper');
		//console.log( para_wrapper );
		
		// set width to prevent rendering error
		para_wrapper.css( 'width', jQuery(this).parent().css( 'width' ) );
		
		// toggle next paragraph_wrapper
		para_wrapper.slideToggle( 'slow', function() {
		
			// when finished, reset width to auto
			para_wrapper.css( 'width', 'auto' );
		
		} );
		
		// --<
		return false;

	});

}






/** 
 * @description: clicking on the "see in context" link
 * @todo: 
 *
 */
function cp_enable_context_clicks() {

	// allow links to work when not on commentable page
	if ( cp_special_page == '1' ) {
		return;
	}

	// unbind first to allow repeated calls to this function
	jQuery('a.comment_on_post').unbind( 'click' );

	jQuery('a.comment_on_post').click( function( event ) {
		
		// override event
		event.preventDefault();
	
		// show comments sidebar
		cp_activate_sidebar( 'comments' );
	
		// get comment id
		var comment_id = this.href.split('#')[1];
		
		// get comment
		var comment = jQuery('#'+comment_id);
		
		//console.log( comment );

		// get array of parent paragraph_wrapper divs
		var para_wrapper_array = comment
									.parents('div.paragraph_wrapper')
									.map( function () {
										return this;
									});

		// did we get one?
		if ( para_wrapper_array.length > 0 ) {
		
			// get the item
			var item = jQuery(para_wrapper_array[0]);
			
			// show block
			item.show();
			
			// if special page
			if ( cp_special_page == '1' ) {
			
				// get offset
				var header_offset = commentpress_get_header_offset();
		
				// scroll to comment
				jQuery.scrollTo(
					comment, 
					{
						duration: cp_scroll_speed, 
						axis:'y', 
						offset: header_offset
					}
				);
			
			} else {
		
				// clear other highlights
				jQuery.unhighlight_para();
				
				// highlight para
				var text_sig = item.attr('id').split('-')[1];
		
				// scroll page to it
				commentpress_scroll_page_to_textblock( text_sig );
				
				// scroll to new comment
				jQuery('#comments_sidebar .sidebar_contents_wrapper').scrollTo(
					comment, 
					{
						duration: cp_scroll_speed, 
						axis: 'y',
						onAfter: function() {
						
							// highlight header
							cp_flash_comment_header( comment );
							
						}
					}
				);
							
			}
			
		}
		
		// --<
		return false;
		
	});

}






/** 
 * @description: get text sig by comment id
 * @todo: 
 *
 */
function cp_get_text_sig_by_comment_id( cid ) {

	// init
	var text_sig = '';

	// are we passing the full id?
	if ( cid.match('#comment-' ) ) {
	
		// get comment ID
		var comment_id = parseInt( cid.split('#comment-')[1] );
		
	}
		
	// get array of parent paragraph_wrapper divs
	var para_wrapper_array = jQuery('#comment-' + comment_id)
								.parents('div.paragraph_wrapper')
								.map( function () {
									return this;
								});

	// did we get one?
	if ( para_wrapper_array.length > 0 ) {
	
		// get the item
		var item = jQuery(para_wrapper_array[0]);
		
		// move form to para
		text_sig = item.attr('id').split('-')[1];
		
	}
	
	
	
	// --<
	return text_sig; 
	
}





/** 
 * @description: scroll to textblock
 * @todo: 
 *
 */
function commentpress_scroll_page_to_textblock( text_sig ) {

	// if not the whole page...
	if( text_sig != '' ) {

		// get text block
		var textblock = jQuery('#textblock-' + text_sig);
		
		// highlight this paragraph
		jQuery.highlight_para( textblock );
		
		// scroll page
		commentpress_scroll_page( textblock );
		
	} else {
		
		// only scroll if page is not highlighted
		if ( page_highlight === false ) {
		
			// scroll to top
			commentpress_scroll_to_top( 0, cp_scroll_speed );
			
		}
		
		// toggle page highlight flag
		page_highlight = !page_highlight;
		
	}
	
}


			



/** 
 * @description: page load prodecure
 * @todo: 
 *
 */
function cp_scroll_to_anchor_on_load() {

	var text_sig = '';

	// if there is an anchor in the URL (only on non-special pages)
	var url = document.location.toString();
	
	// do we have a comment permalink?
	if ( url.match('#comment-' ) ) {
	
		// activate comments sidebar
		cp_activate_sidebar('comments');

		// open the matching block

		// get comment ID
		var comment_id = url.split('#comment-')[1];
		
		// get array of parent paragraph_wrapper divs
		var para_wrapper_array = jQuery('#comment-' + comment_id)
									.parents('div.paragraph_wrapper')
									.map( function () {
										return this;
									});

		// did we get one?
		if ( para_wrapper_array.length > 0 ) {
		
			// get the item
			var item = jQuery(para_wrapper_array[0]);
			
			// are comments open?
			if ( cp_comments_open == 'y' ) {

				// move form to para
				var text_sig = item.attr('id').split('-')[1];
				var para_id = jQuery('#para_wrapper-'+text_sig+' .reply_to_para').attr('id');
				var para_num = para_id.split('-')[1];
				var post_id = jQuery('#comment_post_ID').attr('value');
				//console.log(post_id);
				
				// seems like TinyMCE isn't yet working and that moving the form
				// prevents it from loading properly
				if ( cp_tinymce == '1' ) { 
					
					// if we have link text, then a comment reply is allowed...
					if ( jQuery( '#comment-' + comment_id + ' > .reply' ).text() != '' ) {
						
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
			cp_scroll_comments( jQuery('#comment-' + comment_id), 0, 'flash' );
			
			// if not the whole page...
			if( text_sig != '' ) {
	
				// get text block
				var textblock = jQuery('#textblock-' + text_sig);
				
				// highlight this paragraph
				jQuery.highlight_para( textblock );
				
				// scroll page
				commentpress_scroll_page( textblock );
				
			} else {
				
				// only scroll if page is not highlighted
				if ( page_highlight === false ) {
				
					// scroll to top
					commentpress_scroll_to_top( 0, cp_scroll_speed );
					
				}
				
				// toggle page highlight flag
				page_highlight = !page_highlight;
				
			}
			
			// if IE6, then we have to scroll the page to the top
			//if ( msie6 ) { jQuery(window).scrollTo( 0, 1 ); }

		}
		
	} else {
		
		/** 
		 * @description: loop through the paragraph permalinks checking for a match
		 * @todo: 
		 *
		 */
		jQuery('a.para_permalink').each( function(i) {
		
			// get text signature
			var text_sig = jQuery(this).attr('id');
			
			// do we have a paragraph or comment block permalink?
			if ( url.match('#' + text_sig ) || url.match('#para_heading-' + text_sig ) ) {
			
				//console.log('yep');
			
				// are comments open?
				if ( cp_comments_open == 'y' ) {

					// move form to para
					var para_id = jQuery('#para_wrapper-' + text_sig + ' .reply_to_para').attr('id');
					var para_num = para_id.split('-')[1];
					var post_id = jQuery('#comment_post_ID').attr('value');
					addComment.moveFormToPara( para_num, text_sig, post_id );

				}
				
				// toggle next item_body
				jQuery('#para_heading-' + text_sig).next('div.paragraph_wrapper').show();
				
				// scroll comments
				cp_scroll_comments( jQuery('#para_heading-' + text_sig), 1 );
				
				// get text block
				var textblock = jQuery('#textblock-' + text_sig);
				
				// highlight this paragraph
				jQuery.highlight_para( textblock );
				
				// if IE6, then we have to scroll the page to the top
				//if ( msie6 ) { jQuery(window).scrollTo( 0, 0 ); }
	
				// scroll page
				commentpress_scroll_page( textblock );
				
			}
			
		});
		
	}

	// do we have a link to the comment form?
	if ( url.match( '#respond' ) ) {
		
		// same as clicking on the "whole page" heading
		jQuery('h3#para_heading- a.comment_block_permalink').click();
	
	}

}






/** 
 * @description: page load prodecure for special pages with comments in content
 * @todo: 
 *
 */
function cp_scroll_to_comment_on_load() {

	// if there is an anchor in the URL...
	var url = document.location.toString();
	
	// do we have a comment permalink?
	if ( url.match( '#comment-' ) ) {
	
		// get comment ID
		var comment_id = url.split('#comment-')[1];

		// if IE6, then we have to scroll #wrapper
		if ( msie6 ) {
		
			// scroll to new comment
			jQuery('#main_wrapper').scrollTo(
				jQuery('#comment-'+comment_id), 
				{
					duration: cp_scroll_speed, 
					axis:'y', 
					offset: commentpress_get_header_offset()
				}
			);
			
		} else {
		
			// only scroll if not mobile (but allow tablets)
			if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {
			
				// scroll to new comment
				jQuery.scrollTo(
					jQuery('#comment-'+comment_id), 
					{
						duration: cp_scroll_speed, 
						axis:'y', 
						offset: commentpress_get_header_offset()
					}
				);
				
			}
			
		}

	}

}






/** 
 * @description: does what a click on a comment icon should do
 * @todo: 
 *
 */
function cp_do_comment_icon_action( text_sig, mode ) {

	// show comments sidebar
	cp_activate_sidebar( 'comments' );



	// get para wrapper
	var para_wrapper = jQuery('#para_heading-' + text_sig).next('div.paragraph_wrapper');
	
	// get comment list
	var comment_list = jQuery( '#para_wrapper-' + text_sig + ' .commentlist' );
	
	// get respond
	var respond = para_wrapper.find('#respond');
	
	// is it a direct child of para wrapper?
	var top_level = addComment.getLevel();



	// init
	var opening = false;
	
	// get visibility
	var visible = para_wrapper.css('display');
	
	// override
	if ( visible == 'none' ) { opening = true; }
	


	// clear other highlights
	jQuery.unhighlight_para();
	
	// did we get a text_sig?
	if ( text_sig != '' ) {
	
		// get text block
		var textblock = jQuery('#textblock-' + text_sig);
		//console.log(text_sig);
		
		// if encouraging reading and closing
		if ( cp_promote_reading == '1' && !opening ) {
		
			// skip the highlight
		
		} else {
		
			// highlight this paragraph
			jQuery.highlight_para( textblock );
			
			// scroll page
			commentpress_scroll_page( textblock );
			
		}
		
	}
	


	// if encouraging commenting
	if ( cp_promote_reading == '0' ) {
	
		// are comments open?
		if ( cp_comments_open == 'y' ) {

			// get comment post ID
			var post_id = jQuery('#comment_post_ID').attr('value');
			var para_id = jQuery('#para_wrapper-'+text_sig+' .reply_to_para').attr('id');
			var para_num = para_id.split('-')[1];
			
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
				
				// if mode is for markers
				if ( mode == 'marker' ) {
				
					// scroll comments to header
					cp_scroll_comments( jQuery('#para_heading-' + text_sig), cp_scroll_speed );
	
				} else {
			
					// scroll comments to comment form
					cp_scroll_comments( jQuery('#respond'), cp_scroll_speed );
				
				}
			
			} else {
			
				// scroll comments to header
				cp_scroll_comments( jQuery('#para_heading-' + text_sig), cp_scroll_speed );
			
			}

			return;
			
		}
			
		// if it doesn't have the commentform but has a comment
		if ( !respond[0] && comment_list[0] && !opening ) {
		
			// are comments open?
			if ( cp_comments_open == 'y' ) {

				// if mode is for markers
				if ( mode == 'marker' ) {
				
					// scroll comments to header
					cp_scroll_comments( jQuery('#para_heading-' + text_sig), cp_scroll_speed );
	
				} else {
			
					// scroll comments to comment form
					cp_scroll_comments( jQuery('#respond'), cp_scroll_speed );
				
				}
			
			} else {
			
				// scroll comments to header
				cp_scroll_comments( jQuery('#para_heading-' + text_sig), cp_scroll_speed );
			
			}

			return;
			
		}

		// if closing with comment list
		if ( !opening && comment_list[0] ) {
		
			// are comments open?
			if ( cp_comments_open == 'y' ) {

				// if mode is for markers
				if ( mode == 'marker' ) {
				
					// scroll comments to header
					cp_scroll_comments( jQuery('#para_heading-' + text_sig), cp_scroll_speed );
	
				} else {
			
					// scroll comments to comment form
					cp_scroll_comments( jQuery('#respond'), cp_scroll_speed );
				
				}
			
			} else {
			
				// scroll comments to header
				cp_scroll_comments( jQuery('#para_heading-' + text_sig), cp_scroll_speed );
			
			}

			return;
		
		}
		
		// if commentform but no comments and closing
		if ( respond[0] && !comment_list[0] && !opening ) {
		
			// are comments open?
			if ( cp_comments_open == 'y' ) {

				// if mode is for markers
				if ( mode == 'marker' ) {
				
					// scroll comments to header
					cp_scroll_comments( jQuery('#para_heading-' + text_sig), cp_scroll_speed );
	
				} else {
			
					// scroll comments to comment form
					cp_scroll_comments( jQuery('#respond'), cp_scroll_speed );
				
				}
			
			} else {
			
				// scroll comments to header
				cp_scroll_comments( jQuery('#para_heading-' + text_sig), cp_scroll_speed );
			
			}

			// --<
			return;
			
		}
		
		// if closing with no comment list
		if ( !opening && !comment_list[0] ) {
		
			//console.log( 'none + closing' );
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
			cp_scroll_comments( jQuery('#para_heading-' + text_sig), cp_scroll_speed );
			
		} else {
		
			// only if opening
			if ( opening ) {
			
				// are comments open?
				if ( cp_comments_open == 'y' ) {

					// if mode is for markers
					if ( mode == 'marker' ) {
					
						// scroll comments to header
						cp_scroll_comments( jQuery('#para_heading-' + text_sig), cp_scroll_speed );
		
					} else {
				
						// scroll comments to comment form
						cp_scroll_comments( jQuery('#respond'), cp_scroll_speed );
					
					}
				
				} else {
				
					// scroll comments to comment form
					cp_scroll_comments( jQuery('#para_heading-' + text_sig), cp_scroll_speed );
				
				}

			}
			
		}
		
		
	});
	
}






/** 
 * @description: set up clicks on comment icons attached to comment-blocks in post/page
 * @todo: 
 *
 */
function commentpress_setup_para_permalink_icons() {

	// unbind first to allow repeated calls to this function
	jQuery('a.para_permalink').unbind( 'click' );

	/** 
	 * @description: clicking on the little comment icon
	 * @todo: 
	 *
	 */
	jQuery('a.para_permalink').click( function( event ) {
	
		// override event
		event.preventDefault();
	
		// get text signature
		var text_sig = jQuery(this).attr('href').substring(1);
		//console.log( text_sig );
		
		// use function
		cp_do_comment_icon_action( text_sig, 'auto' );
		
		// --<
		return false;
		
	});

	// unbind first to allow repeated calls to this function
	jQuery('a.para_permalink').unbind( 'hover' );

	/** 
	 * @description: clicking on the little comment icon
	 * @todo: 
	 *
	 */
	jQuery('a.para_permalink').hover( 
	
		function( event ) {
			
			// get text signature
			var text_sig = jQuery(this).attr('href');
			//console.log( 'span.para_marker a'.text_sig );
			
			jQuery('span.para_marker a' + text_sig).addClass( 'js-hover' );
			
		},
	
		function( event ) {
			
			// get text signature
			var text_sig = jQuery(this).attr('href');
			//console.log( text_sig );
			
			jQuery('span.para_marker a' + text_sig).removeClass( 'js-hover' );
			
		}
	
	);

}





	
/** 
 * @description: set up clicks on comment icons attached to comment-blocks in post/page
 * @todo: 
 *
 */
function commentpress_setup_page_click_actions() {

	// unbind first to allow repeated calls to this function
	jQuery('.post_title a').unbind( 'click' );

	/** 
	 * @description: clicking on the page/post title
	 * @todo: 
	 *
	 */
	jQuery('.post_title a').click( function( event ) {
	
		// override event
		event.preventDefault();
	
		// get text signature
		var text_sig = '';
		//console.log( text_sig );
		
		// use function
		cp_do_comment_icon_action( text_sig, 'marker' );
		
		// --<
		return false;
		
	});

	/** 
	 * @description: clicking on the textblock
	 * @todo: 
	 *
	 */
	jQuery('.textblock').click( function( event ) {
	
		// get text signature
		var text_sig = jQuery(this).attr('id');
		//console.log( text_sig );
		
		// remove leading #
		text_sig = text_sig.split('textblock-')[1];
		
		// use function
		cp_do_comment_icon_action( text_sig, 'marker' );
		
	});

	// unbind first to allow repeated calls to this function
	jQuery('span.para_marker a').unbind( 'click' );

	/** 
	 * @description: clicking on the little comment icon
	 * @todo: 
	 *
	 */
	jQuery('span.para_marker a').click( function( event ) {
	
		// override event
		event.preventDefault();
	
		// get text signature
		var text_sig = jQuery(this).attr('href');
		//console.log( text_sig );
		
		// remove leading #
		text_sig = text_sig.substring(1);
		
		// use function
		cp_do_comment_icon_action( text_sig, 'marker' );
		
		// --<
		return false;
		
	});

	// unbind first to allow repeated calls to this function
	jQuery('span.para_marker a').unbind( 'hover' );

	/** 
	 * @description: clicking on the little comment icon
	 * @todo: 
	 *
	 */
	jQuery('span.para_marker a').hover( 
	
		function( event ) {
			
			// get text signature
			var text_sig = jQuery(this).attr('href');
			//console.log( 'span.para_marker a'.text_sig );
			
			// get target item
			var target = jQuery(this).parent().next().children('.comment_count');
			//console.log( target );
			
			target.addClass( 'js-hover' );
			
		},
	
		function( event ) {
			
			// get text signature
			var text_sig = jQuery(this).attr('href');
			//console.log( text_sig );
			
			// get target item
			var target = jQuery(this).parent().next().children('.comment_count');
			//console.log( target );
			
			target.removeClass( 'js-hover' );
			
		}
	
	);

}





	
/** 
 * @description: open header
 * @todo: 
 *
 */
function cp_open_header() {

	// -------------------------------------------------------------------------
	//console.log( 'open' );
	// -------------------------------------------------------------------------



	// get nav height
	var book_nav_h = jQuery('#book_nav').height();
	
	var target_sidebar = jQuery('#sidebar');
	var target_sidebar_pane = jQuery.get_sidebar_pane();
	var book_header = jQuery('#book_header');
	var container = jQuery('#container');
	
	
	
	// set max height
	var cp_container_top = cp_container_top_max;

	// is the admin bar shown?
	if ( cp_wp_adminbar == 'y' ) {
	
		// deduct height of admin bar
		var cp_container_top = cp_container_top_max - cp_wp_adminbar_height;
	}
	
	// animate container
	container.animate({
	
		top: cp_container_top + 'px',
		duration: 'fast'

	}, function () {
	
		//book_header.show();

		// slide book header
		book_header.fadeIn('fast', function() {
			
			// when done
			cp_header_animating = false;
	
		});
	
	});




	// is the sidebar minimised?
	if ( cp_sidebar_minimised == 'n' ) {
	


		// get sidebar height
		var cp_sidebar_height = target_sidebar.height() - cp_book_header_height;
		
		// animate main wrapper
		target_sidebar.animate({
		
			top: cp_container_top_max + 'px',
			height: cp_sidebar_height + 'px',
			duration: 'fast'
		
		}, function() {
		
			// when done
			target_sidebar.css('height','auto');
	
		});
		
		// animate inner
		target_sidebar_pane.animate({
		
			height: ( target_sidebar_pane.height() - cp_book_header_height ) + 'px',
			duration: 'fast'
		
			}, function() {
			
				// don't set height when mobile device (but allow tablets - needs testing)
				if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {
			
					// fit column
					jQuery.set_sidebar_height();
					
				}
				
				// when done
				cp_header_animating = false;
		
			}
			
		);
		
	} else {
	


		// animate sidebar
		target_sidebar.animate({
		
			top: cp_container_top_max + 'px',
			duration: 'fast'
		
			}, function() {
			
				// when done
				cp_header_animating = false;

				// don't set height when mobile device (but allow tablets)
				if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {
			
					// fit column
					jQuery.set_sidebar_height();
				
				}
				
			}
			
		);
		
	}

}





	
/** 
 * @description: close header
 * @todo: 
 *
 */
function cp_close_header() {

	// -------------------------------------------------------------------------
	//console.log( 'close' );
	// -------------------------------------------------------------------------



	// get nav height
	var book_nav_h = jQuery('#book_nav').height();
	
	var target_sidebar = jQuery('#sidebar');
	var target_sidebar_pane = jQuery.get_sidebar_pane();
	var book_header = jQuery('#book_header');
	var container = jQuery('#container');
	
	
	
	// slide header
	book_header.hide();
	


	// set min height
	var cp_container_top = cp_container_top_min;

	// is the admin bar shown?
	if ( cp_wp_adminbar == 'y' ) {
	
		// deduct height of admin bar
		var cp_container_top = cp_container_top_min - cp_wp_adminbar_height;
	}
	
	container.animate({
	
		top: cp_container_top + 'px',
		duration: 'fast'
	
	});
	


	// is the sidebar minimised?
	if ( cp_sidebar_minimised == 'n' ) {
	
		// get sidebar height
		var cp_sidebar_height = target_sidebar.height() + cp_book_header_height;
		
		//jQuery('#container').css('top','40px');
		target_sidebar.animate({
		
			top: cp_container_top_min + 'px',
			height: cp_sidebar_height + 'px',
			duration: 'fast'
		
			}, function() {
			
				// when done
				target_sidebar.css('height','auto');
		
			}
			
		);
			
		//jQuery('#container').css('top','40px');
		target_sidebar_pane.animate({
		
			height: ( target_sidebar_pane.height() + cp_book_header_height ) + 'px',
			duration: 'fast'
		
		}, function() {
		
			// don't set height when mobile device (but allow tablets)
			if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {
		
				// fit column
				jQuery.set_sidebar_height();
				
			}
			
			// when done
			cp_header_animating = false;
	
		});
		
	} else {
	
		// animate just sidebar
		target_sidebar.animate({
		
			top: cp_container_top_min + 'px',
			duration: 'fast'
	
		}, function() {
		
			// when done
			cp_header_animating = false;
	
			// don't set height when mobile device (but allow tablets)
			if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {
		
				// fit column
				jQuery.set_sidebar_height();
				
			}
			
		});
		
	}
	
}





	
/** 
 * @description: set up header minimiser button
 * @todo: 
 *
 */
function commentpress_setup_header_minimiser() {

	// if animating, kick out
	if ( cp_header_animating === true ) { return false; }
	cp_header_animating = true;
	
	
	// toggle
	if ( 
		cp_header_minimised === undefined || 
		cp_header_minimised === null || 
		cp_header_minimised == 'n' 
	) {
	
		cp_close_header();
	
	} else {
	
		cp_open_header();
	
	}



	// toggle
	cp_header_minimised = ( cp_header_minimised == 'y' ) ? 'n' : 'y';
	
	// store flag in cookie
	jQuery.cookie( 
	
		'cp_header_minimised', 
		cp_header_minimised, 
		{ expires: 28, path: cp_cookie_path } 
		
	);
	
}





	
/** 
 * @description: set up paragraph links: cp_para_link is a class writers can use
 * in their markup to create nicely scrolling links within their pages
 * @todo: 
 *
 */
function commentpress_setup_para_links() {

	// unbind first to allow repeated calls to this function
	jQuery('a.cp_para_link').unbind( 'click' );

	/** 
	 * @description: clicking on links to paragraphs
	 * @todo: 
	 *
	 */
	jQuery('a.cp_para_link').click( function( event ) {
	
		// override event
		event.preventDefault();
	
		// get text signature
		var text_sig = jQuery(this).attr('href').substring(1);
		//console.log(text_sig);
		
		// use function
		cp_do_comment_icon_action( text_sig, 'auto' );
		
		// --<
		return false;
		
	});

}





	
/** 
 * @description: set up footnote links for various plugins
 * @todo: 
 *
 */
function commentpress_setup_footnotes_compatibility() {
	
	// -------------------------------------------------------------------------
	// Back links
	// -------------------------------------------------------------------------

	// unbind first to allow repeated calls to this function
	jQuery('span.footnotereverse a').unbind( 'click' );

	/** 
	 * @description: clicking on reverse links in FD-Footnotes and WP_Footnotes
	 * @todo: 
	 *
	 */
	jQuery('span.footnotereverse a, a.footnote-back-link').click( function( event ) {
	
		// override event
		event.preventDefault();
	
		// get text signature
		var target = jQuery(this).attr('href');
		//console.log(text_sig);
		
		// use function for offset
		cp_quick_scroll_page( target, 100 );
		
		// --<
		return false;
		
	});
	
	// unbind first to allow repeated calls to this function
	jQuery('.simple-footnotes ol li > a').unbind( 'click' );

	/** 
	 * @description: clicking on reverse links in Simple Footnotes plugin
	 * @todo: 
	 *
	 */
	jQuery('.simple-footnotes ol li > a').click( function( event ) {
	
		// get text signature
		var target = jQuery(this).attr('href');
		//console.log(text_sig);
		
		// is it a backlink?
		if ( target.match('#return-note-' ) ) {
		
			// override event
			event.preventDefault();
		
			// use function for offset
			cp_quick_scroll_page( target, 100 );
			
			// --<
			return false;
			
		}
		
	});

	// -------------------------------------------------------------------------
	// Footnote links
	// -------------------------------------------------------------------------

	// unbind first to allow repeated calls to this function
	jQuery('a.simple-footnote, sup.footnote a, sup a.footnote-identifier-link').unbind( 'click' );

	/** 
	 * @description: clicking on footnote links in FD-Footnotes, WP-Footnotes and Simple Footnotes
	 * @todo: 
	 *
	 */
	jQuery('a.simple-footnote, sup.footnote a, sup a.footnote-identifier-link').click( function( event ) {
	
		// override event
		event.preventDefault();
	
		// get text signature
		var target = jQuery(this).attr('href');
		//console.log(text_sig);
		
		// use function for offset
		cp_quick_scroll_page( target, 100 );
		
		// --<
		return false;
		
	});
	
}





	
/** 
 * @description: get top of sidebar
 * @todo: 
 *
 */
function cp_get_sidebar_top() {

	// --<
	return jQuery.px_to_num( jQuery('#toc_sidebar').css('top') );
	
}





	
/** 
 * @description: get border width of sidebar
 * @todo: 
 *
 */
function cp_get_sidebar_top_border() {

	// --<
	return jQuery.px_to_num( jQuery('.sidebar_minimiser').css('borderTopWidth') );
	
}





	
/** 
 * @description: bring sidebar to front
 * @todo: 
 *
 */
function cp_activate_sidebar( sidebar ) {

	// get "visibility" of the requested sidebar
	var ontop = jQuery('#' + sidebar + '_sidebar').css('z-index');
	
	// is it hidden (ie, does it have a lower z-index)
	if ( ontop == '2001' ) {
	
		// hide all
		jQuery('.sidebar_container').css('z-index','2001');

		// show it
		jQuery('#' + sidebar + '_sidebar').css('z-index','2010');
		
		var s_top = cp_get_sidebar_top();
		var s_top_border = cp_get_sidebar_top_border();

		// set all tabs to min height
		jQuery('.sidebar_header').css( 'height', ( s_top - s_top_border ) + 'px' );
		
		// set our tab to max height
		jQuery('#' + sidebar + '_header.sidebar_header').css( 'height', s_top + 'px' );
		
		// set flag
		cp_toc_on_top = 'y';
		
	}
	
	// don't set height when mobile device (but allow tablets)
	if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

		// just to make sure...
		jQuery.set_sidebar_height();
		
	} else {
		
		// hide all
		jQuery('.sidebar_container').css('visibility','hidden');

		// show it
		jQuery('#' + sidebar + '_sidebar').css('visibility','visible');
		
		/*
		// set to height of tallest
		var containers = jQuery('.sidebar_contents_wrapper');
		//console.log( containers );
		
		// did we get any?
		if ( containers.length > 0 ) {
		
			// init
			var tallest = 0;
			
			// find height of each
			containers.each( function(i) {
			
				// get height
				var this_height = jQuery(this).height()
				
				// is it taller?
				if ( this_height > tallest ) {
					tallest = this_height;
				}
			
			});
			//console.log( tallest );
			//alert( tallest );
			
			// set it to that height
			jQuery('.sidebar_contents_wrapper').height( tallest );
				
			// then make it auto
			// BUT, this won't allow it to expand in future...
			//jQuery('#' + sidebar + '_sidebar .sidebar_contents_wrapper').css('height','auto');
			
		}
		*/

	}

}





	
/** 
 * @description: define what happens when the page is ready
 * @todo: 
 *
 */
jQuery(document).ready( function($) {


	
	// get global book_header top
	cp_book_header_height = $('#book_header').height();
	
	// don't set height when mobile device (but allow tablets)
	if ( cp_is_mobile == '0' || cp_is_tablet == '1' ) {

		// set sidebar height
		$.set_sidebar_height();
		
	}



	// if we have a cookie
	if ( jQuery.cookie( 'cp_container_top_min' ) ) {
	
		// skip -> we only set these values once (or when the cookie expires)
	
	} else {
		
		// set global container top max
		cp_container_top_max = $.px_to_num( $('#container').css('top') );
		
		// set global container top min
		cp_container_top_min = cp_container_top_max - cp_book_header_height;
		
		// set cookie for further loads
		$.cookie( 
		
			'cp_container_top_min', 
			cp_container_top_min.toString(), 
			{ expires: 28, path: cp_cookie_path } 
			
		);
	
		// set cookie for further loads
		$.cookie( 
		
			'cp_container_top_max', 
			cp_container_top_max.toString(), 
			{ expires: 28, path: cp_cookie_path } 
			
		);
	
	}







	// set up page layout
	commentpress_setup_page_layout();
	
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




	/** 
	 * @description: clicking on the Contents Header
	 * @todo:
	 *
	 */
	$('#toc_header h2 a').click( function( event ) {
		
		// override event
		event.preventDefault();
	
		// activate it
		cp_activate_sidebar('toc');

		// --<
		return false;
		
	});

	/** 
	 * @description: clicking on the Activity Header
	 * @todo: 
	 *
	 */
	$('#activity_header h2 a').click( function( event ) {
	
		// override event
		event.preventDefault();
	
		// activate it
		cp_activate_sidebar('activity');

		// --<
		return false;
		
	});

	/** 
	 * @description: clicking on the Comments Header
	 * @todo: 
	 *
	 */
	$('#comments_header h2 a').click( function( event ) {
	
		// override event
		event.preventDefault();
	
		// activate it
		cp_activate_sidebar('comments');

		// --<
		return false;
		
	});






	/** 
	 * @description: clicking on the paragraph permalink
	 * @todo: 
	 *
	 */
	$('a.para_permalink').click( function( event ) {

		// override event
		event.preventDefault();
	
		// --<
		return false;
		
	});






	/** 
	 * @description: clicking on the comment block permalink
	 * @todo: 
	 *
	 */
	$('a.comment_block_permalink').click( function( event ) {

		// override event
		event.preventDefault();
	
		// --<
		return false;
		
	});

	/** 
	 * @description: when a comment block permalink comes into focus
	 * @todo: in development for keyboard accessibility
	 *
	 */
	/*
	if ( $().jquery >= 1.4 ) {
		$('a.comment_block_permalink').focusin( function(e) {
	
			// test -> needs refinement
			//jQuery(this).click();
			
		});
	}
	*/

	/** 
	 * @description: when a comment block permalink loses focus
	 * @todo: in development for keyboard accessibility
	 *
	 */
	/*
	$('a.comment_block_permalink').blur( function(e) {

		// test -> needs refinement
		//jQuery(this).click();
		
	});
	*/
	





	/** 
	 * @description: clicking on the contents button
	 * @todo: 
	 *
	 */
	$('#btn_header_min').click( function( event ) {
	
		// override event
		event.preventDefault();
	
		// call function
		commentpress_setup_header_minimiser();
		
		// --<
		return false;
			
	});

	// if IE6, then sod it
	if ( msie6 ) { $('#btn_header_min').hide(); }
	





	/** 
	 * @description: clicking on the minimise comments icon
	 * @todo: 
	 *
	 */
	$('#cp_minimise_all_comments').click( function( event ) {
	
		// override event
		event.preventDefault();
	
		// slide all paragraph comment wrappers up
		$('#comments_sidebar div.paragraph_wrapper').slideUp();
		
		// unhighlight paragraphs
		$.unhighlight_para();

	});

	



	
	/** 
	 * @description: clicking on the minimise activities icon
	 * @todo: 
	 *
	 */
	$('#cp_minimise_all_activity').click( function( event ) {
	
		// override event
		event.preventDefault();
	
		// slide all paragraph comment wrappers up
		$('#activity_sidebar div.paragraph_wrapper').slideUp();
		
	});

	



	
	/** 
	 * @description: chapter page headings click
	 * @todo: 
	 *
	 */
	$("#toc_sidebar .sidebar_contents_wrapper ul#toc_list li a").click( function( event ) {
	
		// are our chapters pages?
		if ( cp_toc_chapter_is_page == '0' ) {
		
			// no, find child lists of the enclosing <li>
			var myArr = $(this).parent().find('ul');
			
			// do we have a child list?
			if( myArr.length > 0 ) {
			
				// are subpages to be shown?
				if ( cp_show_subpages == '0' ) {
				
					// toggle next list
					$(this).next('ul').slideToggle();

				}
			
				// override event
				event.preventDefault();
			
				// --<
				return false;
				
			}
		
		}
		
	});






	/** 
	 * @description: workflow tabs and content logic
	 * @todo: move to plugin?
	 *
	 */

	// store content min-height on load
	var content_min_height = $('#content').css( 'min-height' );
	
	// store content padding-bottom on load
	var content_padding_bottom = $('#content').css( 'padding-bottom' );
	
	// hide workflow content
	$('#literal .post').css( 'display', 'none' );
	$('#original .post').css( 'display', 'none' );
	
	/** 
	 * @description: clicking on the workflow tabs
	 * @todo: 
	 *
	 */
	$('#content-tabs li h2 a').click( function( event ) {
	
		// override event
		event.preventDefault();
	
		// hide others and show corresponding item

		// get href
		var target_id = this.href.split('#')[1];
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
		
		
		
		// --<
		return false;
		
	});

	



	
	// scroll the page on load
	if ( cp_special_page == '1' ) {
		cp_scroll_to_comment_on_load();
	} else {
		cp_scroll_to_anchor_on_load();
	}

});






/** 
 * @description: define what happens when the page is unloaded
 * @todo: 
 *
 */
/*
jQuery(window).unload( function() { 

	// debug
	//console.log('Bye now!'); 
	
});
*/