/*
================================================================================
CommentPress AJAX Comment Submission
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

This script enables AJAX comment posting when the CommentPress theme is active.

Based loosely on the 'Ajax Comment Posting' WordPress plugin (version 2.0)

--------------------------------------------------------------------------------
*/




// define vars
var cpajax_live, cpajax_ajax_url, cpajax_spinner_url, cpajax_post_id, cpajax_submitting;

// test for our localisation object
if ( 'undefined' !== typeof CommentpressAjaxSettings ) {

	// reference our object vars
	cpajax_live = CommentpressAjaxSettings.cpajax_live;
	cpajax_ajax_url = CommentpressAjaxSettings.cpajax_ajax_url;
	cpajax_spinner_url = CommentpressAjaxSettings.cpajax_spinner_url;
	cpajax_post_id = CommentpressAjaxSettings.cpajax_post_id;

}

// init submitting flag
cpajax_submitting = false;






/** 
 * @description: re-enable Featured Comments plugin functionality
 */
function cpajax_reenable_featured_comments() {

	// test for the Featured Comments localisation object
	if ( 'undefined' !== typeof featured_comments ) {
	
		// we've got it, test for function existence
		if ( jQuery.is_function_defined( 'featured_comments_click' ) ) {
			
			// call function
			featured_comments_click();
			
		}
	
	}
	
}





/** 
 * @description: an example ajax callback
 *
 */
function cpajax_ajax_callback( data ) {
	
	// define vars
	var diff, i, comment;
	
	// get diff
	diff = parseInt( data.cpajax_comment_count ) - parseInt( CommentpressAjaxSettings.cpajax_comment_count );
	
	// did we get any new comments?
	if ( diff > 0 ) {
	
		// loop through them
		for( i = 1; i <= diff; i++ ) {
		
			// get comment array (will rejig when I can find a way to pass nested arrays)
			comment = eval( 'data.' + 'cpajax_new_comment_' + i );
			
			// deal with each comment
			cpajax_add_new_comment( jQuery(comment.markup), comment.text_sig, comment.parent, comment.id );
			
			// increment global
			CommentpressAjaxSettings.cpajax_comment_count++;
		
		}
		
	}
	
}






/** 
 * @description: add comment to page
 * @todo: 
 *
 */
function cpajax_add_new_comment( markup, text_sig, comm_parent, comm_id ) {
	
	// define vars
	var comment_container, para_id, head_id, comm_list, parent_id, child_list, 
		head, head_array, comment_num, new_comment_count;
	
	// get container
	comment_container = jQuery('div.comments_container');
	
	// kick out if we have it already
	if ( comment_container.find( '#li-comment-' + comm_id )[0] ) { return; }
	
	
	
	// get useful ids
	para_id = '#para_wrapper-' + text_sig;
	head_id = '#para_heading-' + text_sig;
	
	// find the commentlist we want
	comm_list = jQuery(para_id + ' ol.commentlist:first');

	// if the comment is a reply, append the comment to the children
	if ( comm_parent != '0' ) {
		
		//console.log( comm_parent );
		parent_id = '#li-comment-' + comm_parent;
		
		// find the child list we want
		child_list = jQuery(parent_id + ' > ol.children:first');

		// is there a child list?
		if ( child_list[0] ) {
		
			markup.hide()
				  .css('background', '#c2d8bc')
				  .appendTo( child_list )
				  .slideDown( 'fast', function() { 
				  
						// animate to white
						markup.animate({ backgroundColor: "#ffffff" }, 1000, function () {
							
							// then make transparent
							markup.css('background', 'transparent');
						
						});
						
				  });
			
		} else {
		
			markup.wrap( '<ol class="children" />' )
				  .parent()
				  .css('background', '#c2d8bc')
				  .hide()
				  .appendTo( parent_id )
				  .slideDown( 'fast', function() { 
				  
						// animate to white
						markup.parent().animate({ backgroundColor: "#ffffff" }, 1000, function () {
							
							// then make transparent
							markup.parent().css('background', 'transparent');
						
						});
						
				  });
			
		}
		
	// if not, append the new comment at the bottom
	} else {
		
		// is there a comment list?
		if ( comm_list[0] ) {
		
			markup.hide()
				  .css('background', '#c2d8bc')
				  .appendTo( comm_list )
				  .slideDown( 'fast', function() { 
				  
						// animate to white
						markup.animate({ backgroundColor: "#ffffff" }, 1000, function () {
							
							// then make transparent
							markup.css('background', 'transparent');
						
						});
						
				  });
			
		} else {
		
			markup.wrap( '<ol class="commentlist" />' )
				  .parent()
				  .css('background', '#c2d8bc')
				  .hide()
				  .prependTo( para_id )
				  .slideDown( 'fast', function() { 
				  
						// animate to white
						markup.parent().animate({ backgroundColor: "#ffffff" }, 1000, function () {
							
							// then make transparent
							markup.parent().css('background', 'transparent');
						
						});
						
				  });
			
		}
		
	}
	
	
	
	// get current count
	comment_num = parseInt( jQuery( head_id + ' a span.cp_comment_num' ).text() );
	
	// increment
	new_comment_count = comment_num + 1;
	
	// update heading
	cpajax_update_comments_para_heading( head_id, new_comment_count );
	


	// highlight
	head.css( 'background', '#c2d8bc' );
	
	// animate to existing bg (from css file)
	head.animate( { backgroundColor: '#EFEFEF' }, 1000 );
	
	
	
	// update paragraph icon
	cpajax_update_para_icon( text_sig, new_comment_count );	
	
	
	
	// re-enable clicks
	commentpress_enable_comment_permalink_clicks();
	commentpress_setup_comment_headers();
	cpajax_reassign_comments();
	
	// compatibility with Featured Comments
	cpajax_reenable_featured_comments();
	
}






/** 
 * @description: an example ajax update
 *
 */
function cpajax_ajax_update() {
	
	// kick out if submitting a comment
	if ( cpajax_submitting ) { return; }
	
	/*
	// we can use this to log ajax errors from jQuery.post()
	$(document).ajaxError( function( e, xhr, settings, exception ) { 
		console.log( 'error in: ' + settings.url + ' \n'+'error:\n' + xhr.responseText ); 
	});
	*/
	
	// use post method
	jQuery.post(
		
		// set URL
		cpajax_ajax_url,
		
		// set method to call
		{ action: 'cpajax_get_new_comments',
		
		// send last comment count
		last_count: CommentpressAjaxSettings.cpajax_comment_count,
		
		// send post ID
		post_id: cpajax_post_id
		
		},
		
		// callback
		function( data, textStatus ) { 
		
			//console.log( data );
			//console.log( textStatus );
			
			// if success
			if ( textStatus == 'success' ) {
			
				// call function
				cpajax_ajax_callback( data );
				
			}
			
		},
		
		// expected format
		'json'

	);

}






/** 
 * @description: an example ajax updater
 *
 */
function cpajax_ajax_updater( toggle ) {

	// if set
	if ( toggle == '1' ) {
	
		// NOTE: comment_flood_filter is set to 15000, so that's what we set here. this ain't chat :)
		// if you want to change this to something more 'chat-like'...
		// add this to your theme's functions.php or uncomment it in cp-ajax-comment.php:
		// remove_filter('comment_flood_filter', 'wp_throttle_comment_flood', 10, 3);
		// use at your own risk - it could be very heavy on the database.
		
		// set repeat call
		CommentpressAjaxSettings.interval = window.setInterval( cpajax_ajax_update, 5000 );
		
	} else {
		
		// stop repeat
		window.clearInterval( CommentpressAjaxSettings.interval );
	
	}

}






/** 
 * @description: enable reassignment of comments
 *
 */
function cpajax_reassign_comments() {

	// define vars
	var draggers, droppers, text_sig, options, alert_text, div;

	// get all draggable items
	var draggers = jQuery( '#comments_sidebar .comment-wrapper .comment-assign' );
	
	// show them
	draggers.show();
	
	// make comment reassign button draggable
	draggers.draggable({
		
		// a copy thereof...
		helper: 'clone',
		cursor: 'move'
	
	});
	


	// get all droppable items
	droppers = jQuery( '#content .post .textblock' );
	//console.log( droppers );

	// make textblocks droppable
	droppers.droppable({
		
		// configure droppers
		accept: '.comment-assign',
		hoverClass: 'selected_para selected_dropzone',

		// when the button is dropped
		drop: function( event, ui ) {
			
			// get id of dropped-on item
			text_sig = jQuery( this ).prop('id').split('-')[1];
			
			// create options for modal dialog
			options = {
				
				resizable: false,
				height: 160,
				zIndex: 3999,
				modal: true,
				dialogClass: 'wp-dialog',
				buttons: {
					"Yes": function() {
					
						// let's do it
						jQuery( this ).dialog( "option", "disabled", true );
						
						// clear buttons
						jQuery( '.ui-dialog-buttonset' ).html(
							'<img src="' + cpajax_spinner_url + '" id="loading" alt="' + cpajax_lang[0] + '" />'
						);
						
						// alert title
						jQuery( '.ui-dialog-title' ).html( cpajax_lang[9] );
						
						// show message
						jQuery( '.cp_alert_text' ).html( cpajax_lang[10] );
						
						// call function
						cpajax_reassign( text_sig, ui );
						
					},
					"Cancel": function() {
					
						// cancel
						jQuery( this ).dialog( 'close' );
						jQuery( this ).dialog( 'destroy' );
						jQuery( this ).remove();
						
					}
				}

			};
			
			// define message
			alert_text = cpajax_lang[8];
		
			// create modal dialog
			div = jQuery('<div><p class="cp_alert_text">' + alert_text + '</p></div>');
			div.prop( 'title', cpajax_lang[7] )
			   .appendTo( 'body' )
			   .dialog( options );
			
		}
	
	});
    
}






/** 
 * @description: reassign a comment
 *
 */
function cpajax_reassign( text_sig, ui ) {
	
	// define vars
	var comment_id, comment_item, comment_to_move, other_comments, comment_list;
	
	// get comment id
	comment_id = jQuery( ui.draggable ).prop('id').split('-')[1];

	// let's see what params we've got
	//console.log( 'text_sig: ' + text_sig );
	//console.log( 'comment id: ' + comment_id );
	
	// get comment parent li
	comment_item = jQuery( ui.draggable ).closest( 'li.comment' );
	
	// assign as comment to move
	comment_to_move = comment_item;
		
	// get siblings
	other_comments = comment_item.siblings( 'li.comment' );
	
	// are there any?
	if ( other_comments.length == 0 ) {
	
		// get comment list, because we need to remove the entire list
		comment_list = comment_item.parent( 'ol.commentlist' );
		
		// overwrite comment to move
		comment_to_move = comment_list;
		
	}
	
	// slide our comment up
	jQuery( comment_to_move ).slideUp( 'slow',
	
		// animation complete
		function() {
			
			/*
			// find target paragraph wrapper
			var para_wrapper = jQuery( '#para_wrapper-' + text_sig );
			
			// get nested commentlist
			var target_list = para_wrapper.children( 'ol.commentlist' );
			
			// does it already have a commentlist?
			if ( target_list.length > 0 ) {
				
				// yes, append just the comment item to the list
				comment_item.appendTo( target_list )
							.css( 'display', 'block' )
							.parent()
							.css( 'display', 'block' );
				
			} else {
				
				// no, we must prepend the list, wrapping the item in one if necessary
	
				// do we have the list defined?
				if ( other_comments.length > 0 ) {
					
					// no, wrap item in list, then prepend
					comment_item.wrap( '<ol class="commentlist" />' )
								.css( 'display', 'block' )
								.parent()
								.css( 'display', 'block' )
								.prependTo( para_wrapper );
					
				} else {
				
					//  prepend list
					comment_item.css( 'display', 'block' )
								.parent()
								.css( 'display', 'block' )
								.prependTo( para_wrapper );
				
				}
				
				// check
				//console.log( para_wrapper );

			}
			*/
			
			// use post
			jQuery.post(
				
				// set URL
				cpajax_ajax_url,
				
				// set params
				{ action: 'cpajax_reassign_comment',
				
				// send text sig
				text_signature: text_sig,
				
				// send post ID
				comment_id: comment_id
		
				 },
				
				// callback
				function( data, textStatus ) { 
				
					//console.log( data.msg );
					//console.log( textStatus );
					
					// if success
					if ( textStatus == 'success' ) {
							
						// refresh from server
						document.location.reload( true );
						
					} else {
					
						// show error
						alert( textStatus );
						
					}
					
				},
				
				// expected format
				'json'
		
			);
		
		}
		
	);
	
}






/** 
 * @description: define what happens when the page is ready
 * @todo: 
 *
 */
jQuery(document).ready(function($) {

	/* cpajax_lang[]:
	[0]: 'Loading...'
	[1]: 'Please enter your name.'
	[2]: 'Please enter your email address.'
	[3]: 'Please enter a valid email address.'
	[4]: 'Please enter your comment'
	[5]: 'Your comment has been added.'
	[6]: 'AJAX error!'
	*/
	
	
	
	

	// trigger repeat calls
	cpajax_ajax_updater( cpajax_live );
	
	/** 
	 * @description: ajax comment updating
	 * @todo: 
	 *
	jQuery('#btn_js').toggle( function() {
		
		// trigger repeat calls
		cpajax_ajax_updater( false );
		
		jQuery(this).text('Javascript Off');
		
		return false;
		
	}, function() {
	
		// trigger repeat calls
		cpajax_ajax_updater( true );
		
		jQuery(this).text('Javascript On');
		
		return false;
		
	});
	 */
	
	
	
	
	// enable comment reassignment
	cpajax_reassign_comments();
	
	
	


	/** 
	 * @description: init
	 * @todo: 
	 *
	 */
	var form, err;
	function cpajax_initialise() {

		jQuery('#respond_title').after(
		
			'<div id="cpajax_error_msg"></div>'
			
		);
		jQuery('#submit').after(
			'<img src="' + cpajax_spinner_url + '" id="loading" alt="' + cpajax_lang[0] + '" />'
		);
		jQuery('#loading').hide();
		form = jQuery('#commentform');
		err = jQuery('#cpajax_error_msg');
		err.hide();

	}
	
	// do it
	cpajax_initialise();
	
	
	
	


	/** 
	 * @description: reset
	 * @todo: 
	 *
	 */
	function cpajax_reset() {

		jQuery('#loading').hide();
		jQuery('#submit').removeAttr("disabled");
		jQuery('#submit').show();
		addComment.enableForm();
		cpajax_submitting = false;

	}
	
	
	
	

	
	/** 
	 * @description: add comment to page
	 * @todo: 
	 *
	 */
	function cpajax_add_comment( response ) {
		
		// define vars
		var text_sig, comm_parent, para_id, head_id, parent_id, child_list, 
			comm_list, comment_num, new_comment_count;
	
		// get form data
		text_sig = form.find('#text_signature').val();
		comm_parent = form.find('#comment_parent').val();
		
		// get useful ids
		para_id = '#para_wrapper-' + text_sig;
		head_id = '#para_heading-' + text_sig;
		
		// we no longer have zero comments
		jQuery(para_id).removeClass( 'no_comments' );
		
		// if the comment is a reply, append the comment to the children
		if ( comm_parent != '0' ) {
			
			// get parent
			parent_id = '#li-comment-' + comm_parent;
			
			// find the child list we want
			child_list = jQuery(parent_id + ' > ol.children:first');
	
			// is there a child list?
			if ( child_list[0] ) {
			
				cpajax_nice_append(
					response,
					parent_id + ' > ol.children:first > li:last', 
					child_list, 
					parent_id + ' > ol.children:first > li:last'
				);
				
			} else {
			
				cpajax_nice_append(
					response, 
					parent_id + ' > ol.children:first', 
					parent_id,
					parent_id + ' > ol.children:first > li:last'
				);
				
			}
			
		// if not, append the new comment at the bottom
		} else {
				
			// find the commentlist we want
			comm_list = jQuery(para_id + ' > ol.commentlist:first');
	
			// is there a comment list?
			if ( comm_list[0] ) {
			
				cpajax_nice_append(
					response,
					para_id + ' > ol.commentlist:first > li:last',
					comm_list,
					para_id + ' > ol.commentlist:first > li:last'
				);
				
			} else {
			
				cpajax_nice_prepend(
					response,
					para_id + ' > ol.commentlist:first',
					para_id,
					para_id + ' > ol.commentlist:first > li:last'
				);

			}
			
		}
	

		// slide up comment form
		jQuery('#respond').slideUp( 'fast', function() {  

			// after slide
			addComment.cancelForm();
			
		});
		
		
		
		// get existing current count
		comment_num = parseInt( jQuery( head_id + ' a span.cp_comment_num' ).text() );
		
		// increment
		new_comment_count = comment_num + 1;
		
		// update heading
		cpajax_update_comments_para_heading( head_id, new_comment_count );
		
		// update paragraph icon
		cpajax_update_para_icon( text_sig, new_comment_count );	
		
		
		
		// if not the whole page...
		if( text_sig != '' ) {

			// scroll page to text block
			commentpress_scroll_page( jQuery( '#textblock-' + text_sig ) );
			
		} else {
		
			// scroll to top
			commentpress_scroll_to_top( 0, cp_scroll_speed );
			
		}
		
		
		
		// re-enable clicks
		commentpress_enable_comment_permalink_clicks();
		commentpress_setup_comment_headers();
		cpajax_reassign_comments();
		
		// clear comment form
		form.find('#comment').val( '' );

		//err.html('<span class="success">' + cpajax_lang[5] + '</span>');
		
		// compatibility with Featured Comments
		cpajax_reenable_featured_comments();
		
	}
	
	
	
	
	
	
	/** 
	 * @description: do comment append
	 * @todo: 
	 *
	 */
	function cpajax_nice_append( response, content, target, last ) {
	
		// test for undefined, which may happen on replies to comments
		// which have lost their original context
		if ( response === undefined || response === null ) { return; }
	
		response.find(content)
				.hide()
				.appendTo(target);
		
		// clean up
		cpajax_cleanup( content, last );
		
	}
	
	
	
	

	
	/** 
	 * @description: do comment prepend
	 * @todo: 
	 *
	 */
	function cpajax_nice_prepend( response, content, target, last ) {
	
		// test for undefined, which may happen on replies to comments
		// which have lost their original context
		if ( response === undefined || response === null ) { return; }
	
		response.find(content)
				.hide()
				.prependTo(target);
		
		// clean up
		cpajax_cleanup( content, last );
		
	}
	
	
	
	

	
	/** 
	 * @description: do comment cleanup
	 * @todo: 
	 *
	 */
	function cpajax_cleanup( content, last ) {
		
		// define vars
		var last_id, new_comm_id, comment;
	
		// get the id of the last list item
		last_id = jQuery(last).prop('id');
	
		// construct new comment id
		new_comm_id = '#comment-' + ( last_id.toString().split('-')[2] );
		comment = jQuery(new_comm_id);
		
		// highlight it
		comment.css('background', '#c2d8bc');
		
		jQuery(content).slideDown('slow',
		
			// animation complete
			function() {

				// scroll to new comment
				jQuery('#comments_sidebar .sidebar_contents_wrapper').scrollTo(
					comment, 
					{
						duration: cp_scroll_speed, 
						axis: 'y',
						onAfter: function() {
						
							// animate to white
							comment.animate({ backgroundColor: "#ffffff" }, 1000, function () {
								
								// then make transparent
								comment.css('background', 'transparent');
							
							});
							
						}
					}
				);
							
			}
			
		); // end slide
				
	}
	
	
	
	

	
	/** 
	 * @description: update comments paragraph heading
	 * @todo: 
	 *
	 */
	function cpajax_update_comments_para_heading( head_id, new_comment_count ) {
		
		// increment
		jQuery( head_id + ' a span.cp_comment_num' ).text( new_comment_count.toString() );
		
		// if exactly one comment
		if ( new_comment_count == 1 ) {
		
			// update current word
			jQuery( head_id + ' a span.cp_comment_word' ).text( cpajax_lang[11] );
		
		}
		
		// if greater than one comment
		if ( new_comment_count > 1 ) {
		
			// update current word
			jQuery( head_id + ' a span.cp_comment_word' ).text( cpajax_lang[12] );
		
		}
		
	}
	
	
	
	

	
	/** 
	 * @description: update paragraph comment icon
	 * @todo: 
	 *
	 */
	function cpajax_update_para_icon( text_sig, new_comment_count ) {
		
		// define vars
		var textblock_id;
		
		// construct textblock_id
		textblock_id = '#textblock-' + text_sig;
		
		// set comment icon text
		jQuery( textblock_id + ' span small' ).text( new_comment_count.toString() );
		
		// if we're changing from 0 to 1...
		if ( new_comment_count == 1 ) {
	
			// set comment icon
			jQuery( textblock_id + ' span.commenticonbox a.para_permalink' ).addClass( 'has_comments' );
		
			// show comment icon text
			jQuery( textblock_id + ' span small' ).css( 'visibility', 'visible' );

		}

	}
	
	
	
	

	
	/** 
	 * @description: comment submission method
	 * @todo: 
	 *
	 */
	jQuery('#commentform').on('submit', function( event ) {
	
		// define vars
		var filter;
	
		// set global flag
		cpajax_submitting = true;
		
		// hide errors
		err.hide();
		
		// if not logged in, validate name and email
		if ( form.find( '#author' )[0] ) {
			
			// check for name
			if ( form.find( '#author' ).val() == '' ) {
				err.html('<span class="error">' + cpajax_lang[1] + '</span>');
				err.show();
				cpajax_submitting = false;
				return false;
			}
			
			// check for email
			if ( form.find( '#email' ).val() == '' ) {
				err.html('<span class="error">' + cpajax_lang[2] + '</span>');
				err.show();
				cpajax_submitting = false;
				return false;
			}

			// validate email
			filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
			if( !filter.test( form.find('#email').val() ) ) {
				err.html('<span class="error">' + cpajax_lang[3] + '</span>');
				err.show();
				if (event.preventDefault) {event.preventDefault();}
				cpajax_submitting = false;
				return false;
			}

		} // end if
		


		// test for tinyMCE
		if ( cp_tinymce == '1' ) {
		
			// set value of comment textarea to content
			tinyMCE.triggerSave();
			
			// unload tinyMCE
			addComment.disableForm();
		
		}
		
		// check for comment
		if ( form.find( '#comment' ).val() == '' ) {
			err.html('<span class="error">' + cpajax_lang[4] + '</span>');
			err.show();
			// reload tinyMCE
			addComment.enableForm();
			cpajax_submitting = false;
			return false;
		}
		


		// submit the form
		jQuery(this).ajaxSubmit({
			
			
			
			beforeSubmit: function() {
				
				jQuery('#loading').show();
				jQuery('#submit').prop('disabled','disabled');
				jQuery('#submit').hide();

			}, // end beforeSubmit
			


			error: function(request) {

				// define vars
				var data;
	
				err.empty();
				data = request.responseText.match(/<p>(.*)<\/p>/);
				err.html('<span class="error">' + data[1] + '</span>');
				err.show();

				cpajax_reset();

				return false;

			}, // end error()
			


			success: function( data ) {
				
				// declare vars
				var response;
				
				// trace
				//console.log( data );

				try {
					
					// jQuery 1.9 fails to recognise the response as HTML, so
					// we *must* use parseHTML if it's available...
					if ( jQuery.parseHTML ) {
					
						// if our jQuery version is 1.8+, it'll have parseHTML
						response =  jQuery( jQuery.parseHTML( data ) );
						
					} else {
					
						// get our data as object in the basic way
						response = jQuery( data );
						
					}
					
					//console.log( response );
					
					// add comment
					cpajax_add_comment( response );
					cpajax_reset();
				
				} catch (e) {

					cpajax_reset();
					alert( cpajax_lang[6] + '\n\n' + e );

				} // end try
				
			} // end success()
			


		}); // end ajaxSubmit()
		
		
		
		// --<
		return false; 

	}); // end form.submit()
	
	
	
	
	
	
	
}); // end document.ready()