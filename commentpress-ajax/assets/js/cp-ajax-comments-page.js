/*
================================================================================
CommentPress Core AJAX Comment Submission (in page)
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

This script enables AJAX comment posting when the comment list is in the
main content area and when a CommentPress Core compatible theme is active.

Based loosely on the 'Ajax Comment Posting' WordPress plugin (version 2.0)

--------------------------------------------------------------------------------
*/



/**
 * Create AJAX sub-namespace.
 *
 * @since 3.8
 */
CommentPress.ajax = {};



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress Core AJAX comments class.
 *
 * @since 3.8
 */
CommentPress.ajax.comments = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();

	// Init form submitting flag.
	this.cpajax_submitting = false;

	// Comment form.
	this.cpajax_form = {};

	// Error display element.
	this.cpajax_error = {};

	// Test for our localisation object.
	if ( 'undefined' !== typeof CommentpressAjaxSettings ) {

		// Reference our localisation object vars.
		this.cpajax_live = CommentpressAjaxSettings.cpajax_live;
		this.cpajax_ajax_url = CommentpressAjaxSettings.cpajax_ajax_url;
		this.cpajax_spinner_url = CommentpressAjaxSettings.cpajax_spinner_url;
		this.cpajax_post_id = CommentpressAjaxSettings.cpajax_post_id;
		this.cpajax_lang = CommentpressAjaxSettings.cpajax_lang;

	}



	/**
	 * Initialise CommentPress Core AJAX.
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

		// Create error container.
		$('#respond_title').after(
			'<div id="cpajax_error_msg"></div>'
		);

		// Init AJAX spinner.
		$('#submit').after(
			'<img src="' + me.cpajax_spinner_url + '" id="loading" alt="' + me.cpajax_lang[0] + '" />'
		);

		// Hide spinner.
		$('#loading').hide();

		// Store reference to the comment form.
		me.cpajax_form = $('#commentform');

		// Store reference to the error div.
		me.cpajax_error = $('#cpajax_error_msg');

		// Hide error div.
		me.cpajax_error.hide();

		// Initialise comment form.
		me.initialise_form();

	};



	/**
	 * Reset CommentPress Core AJAX.
	 *
	 * @since 3.8
	 */
	this.reset = function() {

		// Hide the spinner.
		$('#loading').hide();

		// Enable submit button.
		$('#submit').removeAttr( 'disabled' );

		// Make it visible.
		$('#submit').show();

		// Enable the comment form.
		addComment.enableForm();

		// Set flag to say we're done.
		me.cpajax_submitting = false;

	};



	/**
	 * Add comment to page.
	 *
	 * @since 3.8
	 *
	 * @param object response The jQuery object containing the result of the AJAX request.
	 */
	this.add_comment = function( response ) {

		// Define vars.
		var comm_parent, comm_list, parent_id, head;

		// Get comment parent.
		comm_parent = me.cpajax_form.find('#comment_parent').val();

		// Find the commentlist we want.
		comm_list = $('ol.commentlist:first');

		// If the comment is a reply, append the comment to the children.
		if ( comm_parent != '0' ) {

			// Get parent.
			parent_id = '#li-comment-' + comm_parent;

			// Find the child list we want.
			child_list = $(parent_id + ' > ol.children:first');

			// Is there a child list?
			if ( child_list[0] ) {

				me.nice_append(
					response,
					parent_id + ' > ol.children:first > li:last',
					child_list,
					parent_id + ' > ol.children:first > li:last'
				);

			} else {

				me.nice_append(
					response,
					parent_id + ' > ol.children:first',
					parent_id,
					parent_id + ' > ol.children:first > li:last'
				);

			}

		// If not, append the new comment at the bottom.
		} else {

			// Is there a comment list?
			if ( comm_list[0] ) {

				me.nice_append(
					response,
					'ol.commentlist:first > li:last',
					comm_list,
					'ol.commentlist:first > li:last'
				);

			} else {

				me.nice_append(
					response,
					'ol.commentlist:first',
					'div.comments_container',
					'ol.commentlist:first > li:last'
				);

			}

		}

		// Get head.
		head = response.find('#comments_in_page_wrapper div.comments_container > h3.general_comments_header');

		// Replace heading.
		$('#comments_in_page_wrapper div.comments_container > h3.general_comments_header').replaceWith(head);

		// Clear comment form.
		me.cpajax_form.find( '#comment' ).val( '' );

		//me.cpajax_error.html('<span class="success">' + me.cpajax_lang[5] + '</span>');

		// Compatibility with Featured Comments.
		cpajax_reenable_featured_comments();

		// Compatibility with Comment Upvoter.
		cpajax_reenable_comment_upvoter();

	};



	/**
	 * Do comment append.
	 *
	 * @since 3.8
	 *
	 * @param object response The jQuery object from the AJAX request.
	 * @param string content The jQuery selector that targets the comment content.
	 * @param object target The jQuery object in which the comment should be placed.
	 * @param string last The jQuery selector of the last item in the comment list.
	 * @return string new_comment_id The ID of the new comment.
	 */
	this.nice_append = function( response, content, target, last ) {

		// Define vars.
		var new_comment;

		// Test for undefined, which may happen on replies to comments
		// which have lost their original context.
		if ( 'undefined' === typeof response || response === null ) { return; }

		// Make a copy of the new comment.
		new_comment = response.find(content).clone();

		// Hide and append.
		new_comment.appendTo(target).hide();

		// Clean up.
		me.cleanup( content, last );

	};



	/**
	 * Do comment prepend.
	 *
	 * @since 3.8
	 *
	 * @param object response The jQuery object from the AJAX request.
	 * @param string content The jQuery selector that targets the comment content.
	 * @param object target The jQuery object in which the comment should be placed.
	 * @param string last The jQuery selector of the last item in the comment list.
	 * @return string new_comment_id The ID of the new comment.
	 */
	this.nice_prepend = function( response, content, target, last ) {

		// Define vars.
		var new_comment;

		// Test for undefined, which may happen on replies to comments
		// which have lost their original context.
		if ( 'undefined' === typeof response || response === null ) { return; }

		// Make a copy of the new comment.
		new_comment = response.find(content).clone();

		// Hide and prepend.
		new_comment.prependTo(target).hide();

		// Clean up.
		me.cleanup( content, last );

	};



	/**
	 * Do comment cleanup.
	 *
	 * @since 3.8
	 *
	 * @param string content The jQuery selector that targets the comment content.
	 * @param string last The jQuery selector of the last item in the comment list.
	 */
	this.cleanup = function( content, last ) {

		// Define vars.
		var last_id, new_comm_id, comment;

		// Get the id of the last list item.
		last_id = $(last).prop('id');

		/*
		// IE seems to grab the result from cache despite nocache_headers()
		// The following is an action of last resort - the cache is being busted in
		// commentpress_comment_post_redirect() instead.
		if ( typeof last_id == 'undefined' || last_id === null ) {
			document.location.reload( true );
			return;
		}
		*/

		// Construct new comment id.
		new_comm_id = '#comment-' + ( last_id.toString().split('-')[2] );
		comment = $(new_comm_id);

		addComment.cancelForm();

		// Add a couple of classes.
		comment.addClass( 'comment-highlighted' );

		$(content).slideDown( 'slow',

			// Animation complete.
			function() {

				// Scroll to new comment.
				$(window).stop(true).scrollTo(
					comment,
					{
						duration: cp_scroll_speed,
						axis: 'y',
						offset: CommentPress.theme.header.get_offset(),
						onAfter: function() {

							// Remove highlight class.
							comment.addClass( 'comment-fade' );

						}
					}
				);

			}

		); // End slide.

	};




	/**
	 * Init comment form.
	 *
	 * @since 3.8
	 */
	this.initialise_form = function() {

		// Unbind first to allow repeated calls to this function.
		$('#commentform').off( 'submit' );

		/**
		 * Comment submission method.
		 *
		 * @return false
		 */
		$('#commentform').on( 'submit', function( event ) {

			// Define vars.
			var filter;

			// Set global flag.
			me.cpajax_submitting = true;

			// Hide errors.
			me.cpajax_error.hide();

			// If not logged in, validate name and email.
			if ( me.cpajax_form.find( '#author' )[0] ) {

				// Check for name.
				if ( me.cpajax_form.find( '#author' ).val() == '' ) {
					me.cpajax_error.html('<span class="error">' + me.cpajax_lang[1] + '</span>');
					me.cpajax_error.show();
					me.cpajax_submitting = false;
					return false;
				}

				// Check for email.
				if ( me.cpajax_form.find( '#email' ).val() == '' ) {
					me.cpajax_error.html('<span class="error">' + me.cpajax_lang[2] + '</span>');
					me.cpajax_error.show();
					me.cpajax_submitting = false;
					return false;
				}

				// Validate email.
				filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
				if( !filter.test( me.cpajax_form.find('#email').val() ) ) {
					me.cpajax_error.html('<span class="error">' + me.cpajax_lang[3] + '</span>');
					me.cpajax_error.show();
					if (event.preventDefault) {event.preventDefault();}
					me.cpajax_submitting = false;
					return false;
				}

			} // End if.

			// Test for tinyMCE.
			if ( cp_tinymce == '1' ) {

				// Set value of comment textarea to content.
				tinyMCE.triggerSave();

				// Unload tinyMCE.
				addComment.disableForm();

			}

			// Check for comment.
			if ( me.cpajax_form.find( '#comment' ).val() == '' ) {
				me.cpajax_error.html('<span class="error">' + me.cpajax_lang[4] + '</span>');
				me.cpajax_error.show();
				// Reload tinyMCE.
				addComment.enableForm();
				me.cpajax_submitting = false;
				return false;
			}

			// Submit the form.
			$(this).ajaxSubmit({

				beforeSubmit: function() {

					$('#loading').show();
					$('#submit').prop('disabled','disabled');
					$('#submit').hide();

				}, // End beforeSubmit.

				error: function(request) {

					// Define vars.
					var data;

					me.cpajax_error.empty();
					data = request.responseText.match(/<p>(.*)<\/p>/);
					me.cpajax_error.html('<span class="error">' + data[1] + '</span>');
					me.cpajax_error.show();

					me.reset();

					return false;

				}, // End error()

				success: function( data ) {

					// Declare vars.
					var response;

					// jQuery 1.9 fails to recognise the response as HTML, so
					// we *must* use parseHTML if it's available.
					if ( $.parseHTML ) {

						// If our jQuery version is 1.8+, it'll have parseHTML.
						response =  $( $.parseHTML( data ) );

					} else {

						// Get our data as object in the basic way.
						response = $(data);

					}

					// Avoid errors if we can.
					try {

						// Add comment.
						me.add_comment( response );
						me.reset();

					// Oh well
					} catch (e) {

						me.reset();
						alert( me.cpajax_lang[6] + '\n\n' + e );

					} // End try.

				} // End success()

			}); // End ajaxSubmit()

			// --<
			return false;

		}); // End form.submit()

	};



}; // End CommentPress Core AJAX comments class.



/* -------------------------------------------------------------------------- */



/**
 * Re-enable Featured Comments plugin functionality.
 *
 * @since 3.8
 */
function cpajax_reenable_featured_comments() {

	// Test for the Featured Comments localisation object.
	if ( 'undefined' !== typeof featured_comments ) {

		// We've got it, test for function existence.
		if ( jQuery.is_function_defined( 'featured_comments_click' ) ) {

			// Call function.
			featured_comments_click();

		}

	}

}



/**
 * Re-enable Comment Upvoter plugin functionality.
 *
 * @since 3.8
 */
function cpajax_reenable_comment_upvoter() {

	// Test for the Comment Upvoter localisation object.
	if ( 'undefined' !== typeof comment_upvoter ) {

		// We've got it, test for function existence.
		if ( jQuery.is_function_defined( 'comment_upvoter_click' ) ) {

			// Call function.
			comment_upvoter_click();

		}

	}

}



/* -------------------------------------------------------------------------- */



// Do immediate init.
CommentPress.ajax.comments.init();



/* -------------------------------------------------------------------------- */



/**
 * Define what happens when the page is ready.
 *
 * @since 3.8
 */
jQuery(document).ready(function($) {

	// Trigger DOM ready methods.
	CommentPress.ajax.comments.dom_ready();

}); // End document.ready()



