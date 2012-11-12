/*
================================================================================
CommentPress AJAX Comment Submission (in page)
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

This script enables AJAX comment posting when the comment list is in the 
main content area and when the CommentPress theme is active.

Based loosely on the 'Ajax Comment Posting' WordPress plugin (version 2.0)

--------------------------------------------------------------------------------
*/






// test for our localisation object
if ( 'undefined' !== typeof CommentpressAjaxSettings ) {

	// reference our object vars
	var cpajax_live = CommentpressAjaxSettings.cpajax_live;
	var cpajax_ajax_url = CommentpressAjaxSettings.cpajax_ajax_url;
	var cpajax_spinner_url = CommentpressAjaxSettings.cpajax_spinner_url;
	var cpajax_post_id = CommentpressAjaxSettings.cpajax_post_id;

}

// init submitting flag
var cpajax_submitting = false;






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
		jQuery('#commentform').after(
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
	
		// get comment parent
		var comm_parent = form.find('#comment_parent').val();
		
		// find the commentlist we want
		var comm_list = jQuery('ol.commentlist:first');

		// if the comment is a reply, append the comment to the children
		if ( comm_parent != '0' ) {
			
			//alert( comm_parent );
			var parent_id = '#li-comment-' + comm_parent;
			
			// find the child list we want
			var child_list = jQuery(parent_id + ' > .children:first');
	
			// is there a child list?
			if (child_list[0]) {
			
				cpajax_nice_append(
					response,
					parent_id + ' .children:first > li:last', 
					child_list, 
					parent_id + ' .children:first > li:last'
				);
				
			} else {
			
				cpajax_nice_append(
					response, 
					parent_id + ' .children:first', 
					parent_id,
					parent_id + ' .children:first > li:last'
				);
				
			}
			
		// if not, append the new comment at the bottom
		} else {
			
			// is there a comment list?
			if (comm_list[0]) {
			
				cpajax_nice_append(
					response,
					'ol.commentlist:first > li:last',
					comm_list,
					'ol.commentlist:first > li:last'
				);
				
			} else {
			
				cpajax_nice_append(
					response,
					'ol.commentlist:first',
					'div.comments_container',
					'ol.commentlist:first > li:last'
				);

			}
			
		}
	
		// permalink clicks
		commentpress_enable_comment_permalink_clicks();
	
		// get head
		var head = response.find('#comments_in_page_wrapper div.comments_container > h3');
	
		// replace heading
		jQuery('#comments_in_page_wrapper div.comments_container > h3').replaceWith(head);
		
		// clear comment form
		form.find('#comment').val('');

		//err.html('<span class="success">' + cpajax_lang[5] + '</span>');
		
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
	
		// get the id of the last list item
		var last_id = jQuery(last).attr('id');
	
		// construct new comment id
		var new_comm_id = '#comment-' + last_id.split('-')[2];
		var comment = jQuery(new_comm_id);
		
		addComment.cancelForm();

		comment.css('background', '#c2d8bc');
		
		jQuery(content).slideDown('slow',
		
			// animation complete
			function() {

				// scroll to new comment
				jQuery.scrollTo(
					comment, 
					{
						duration: cp_scroll_speed, 
						axis: 'y', 
						offset: commentpress_get_header_offset(),
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
	 * @description: comment submission method
	 * @todo: 
	 *
	 */
	jQuery('#commentform').on('submit', function(evt) {
	
		// set global flag
		cpajax_submitting = true;
		
		// hide errors
		err.hide();
		
		// if not logged in, validate name and email
		if(form.find('#author')[0]) {
			
			// check for name
			if(form.find('#author').val() == '') {
				err.html('<span class="error">' + cpajax_lang[1] + '</span>');
				err.show();
				cpajax_submitting = false;
				return false;
			}
			
			// check for email
			if(form.find('#email').val() == '') {
				err.html('<span class="error">' + cpajax_lang[2] + '</span>');
				err.show();
				cpajax_submitting = false;
				return false;
			}

			// validate email
			var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
				if(!filter.test(form.find('#email').val())) {
				err.html('<span class="error">' + cpajax_lang[3] + '</span>');
				err.show();
				if (evt.preventDefault) {evt.preventDefault();}
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
		if(form.find('#comment').val() == '') {
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
				jQuery('#submit').attr('disabled','disabled');
				jQuery('#submit').hide();

			}, // end beforeSubmit
			


			error: function(request) {

				err.empty();
				var data = request.responseText.match(/<p>(.*)<\/p>/);
				err.html('<span class="error">' + data[1] + '</span>');
				err.show();

				cpajax_reset();

				return false;

			}, // end error()
			


			success: function(data) {

				try {
				
					// get our data as object
					var response = jQuery(data);
					//console.log( data );
					
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