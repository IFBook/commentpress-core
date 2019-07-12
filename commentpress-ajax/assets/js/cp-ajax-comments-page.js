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

		// Enable AJAX comment editing.
		me.edit_comments_setup();

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
		var comm_parent, comm_list, parent_id, head, new_comm_id;

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

				new_comm_id = me.nice_append(
					response,
					parent_id + ' > ol.children:first > li:last',
					child_list,
					parent_id + ' > ol.children:first > li:last'
				);

			} else {

				new_comm_id = me.nice_append(
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

				new_comm_id = me.nice_append(
					response,
					'ol.commentlist:first > li:last',
					comm_list,
					'ol.commentlist:first > li:last'
				);

			} else {

				new_comm_id = me.nice_append(
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

		// Re-enable AJAX comment editing.
		me.edit_comments_setup();

		// Compatibility with Featured Comments.
		cpajax_reenable_featured_comments();

		// Compatibility with Comment Upvoter.
		cpajax_reenable_comment_upvoter();

		// Broadcast that we're done and pass new comment ID.
		$(document).trigger( 'commentpress-ajax-comment-added', [ new_comm_id ] );

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
		var new_comment, new_comment_id;

		// Test for undefined, which may happen on replies to comments
		// which have lost their original context.
		if ( 'undefined' === typeof response || response === null ) { return; }

		// Make a copy of the new comment.
		new_comment = response.find(content).clone();

		// Hide and append.
		new_comment.appendTo(target).hide();

		// Clean up.
		new_comment_id = me.cleanup( content, last );

		// --<
		return new_comment_id;

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
		var new_comment, new_comment_id;

		// Test for undefined, which may happen on replies to comments
		// which have lost their original context.
		if ( 'undefined' === typeof response || response === null ) { return; }

		// Make a copy of the new comment.
		new_comment = response.find(content).clone();

		// Hide and prepend.
		new_comment.prependTo(target).hide();

		// Clean up.
		me.cleanup( content, last );

		// Clean up.
		new_comment_id = me.cleanup( content, last );

		// --<
		return new_comment_id;

	};



	/**
	 * Do comment cleanup.
	 *
	 * @since 3.8
	 *
	 * @param string content The jQuery selector that targets the comment content.
	 * @param string last The jQuery selector of the last item in the comment list.
	 * @return string new_comm_id The ID of the new comment.
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

		// --<
		return new_comm_id;

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
					if ( event.preventDefault ) { event.preventDefault(); }
					me.cpajax_submitting = false;
					return false;
				}

				// Check for email.
				if ( me.cpajax_form.find( '#email' ).val() == '' ) {
					me.cpajax_error.html('<span class="error">' + me.cpajax_lang[2] + '</span>');
					me.cpajax_error.show();
					if ( event.preventDefault ) { event.preventDefault(); }
					me.cpajax_submitting = false;
					return false;
				}

				// Validate email.
				filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
				if( !filter.test( me.cpajax_form.find('#email').val() ) ) {
					me.cpajax_error.html('<span class="error">' + me.cpajax_lang[3] + '</span>');
					me.cpajax_error.show();
					if ( event.preventDefault ) { event.preventDefault(); }
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
				if ( event.preventDefault ) { event.preventDefault(); }
				// Reload tinyMCE.
				addComment.enableForm();
				me.cpajax_submitting = false;
				return false;
			}

			// Check for comment edit mode.
			if ( me.cpajax_form.find( '#cp_edit_comment' ).val() == 'y' ) {

				// Skip submission and call method.
				if ( event.preventDefault ) { event.preventDefault(); }
				return me.edit_comment( $(this) );

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



	/**
	 * Enable AJAX editing of comments.
	 *
	 * @since 3.9.12
	 */
	this.edit_comments_setup = function() {

		// Unbind first to allow repeated calls to this function.
		$('#wrapper').off( 'click', '.comment-edit-link' );

		/**
		 * Clicking on comment edit links on the General Comments page.
		 *
		 * @since 3.9.12
		 */
		$('#wrapper').on( 'click', '.comment-edit-link', function( event ) {

			// Define vars.
			var comment_id, input;

			// Override event.
			event.preventDefault();

			// Get comment ID.
			comment_id = parseInt( this.href.split('c=')[1] );

			// Get comment data.
			me.get_comment( comment_id );

		});

	};



	/**
	 * Perform an AJAX request to get the data for a comment.
	 *
	 * @since 3.9.12
	 *
	 * @param int comment_id The numeric ID of the comment.
	 */
	this.get_comment = function( comment_id ) {

		// Kick out if submitting a comment.
		if ( me.cpajax_submitting ) {
			return;
		}

		// Set global flag.
		me.cpajax_submitting = true;

		/*
		 * Use the following to log ajax errors from jQuery.post():
		 *
		 * $(document).ajaxError( function( e, xhr, settings, exception ) {
		 *   console.log( 'error in: ' + settings.url + ' \n'+'error:\n' + xhr.responseText );
		 * });
		 */

		// Use post method.
		$.post(

			// Set URL.
			me.cpajax_ajax_url,

			// Add data.
			{

				// Set WordPress method to call.
				action: 'cpajax_get_comment',

				// Send comment ID.
				comment_id: comment_id

			},

			// Callback.
			function( data, textStatus ) {

				// If success.
				if ( textStatus == 'success' ) {

					// Pass to callback function.
					me.get_comment_callback( data );

				} else {

					// Reset global flag.
					me.cpajax_submitting = false;

				}

			},

			// Expected format.
			'json'

		);

	};



	/**
	 * AJAX callback method called when comment data has been received.
	 *
	 * This method gets called when data has been received from the server via
	 * the AJAX request in this.get_comment().
	 *
	 * @since 3.9.12
	 *
	 * @param object data The data returned from the AJAX request.
	 */
	this.get_comment_callback = function( data ) {

		// Move form into place for editing.
		addComment.moveFormToEdit(
			'comment-' + data.id, data.id, 'respond', data.post_id, data.text_sig, data.content, data.nonce
		);

		// Broadcast that we're done and pass data.
		$(document).trigger( 'commentpress-ajax-comment-callback', [ data ] );

		// Reset global flag.
		me.cpajax_submitting = false;

	};



	/**
	 * Perform an AJAX request to update a comment.
	 *
	 * @since 3.9.12
	 *
	 * @param {Object} form The form.
	 */
	this.edit_comment = function( form ) {

		// Set global flag.
		me.cpajax_submitting = true;

		// Submit the form.
		form.ajaxSubmit({

			// Override URL.
			url: me.cpajax_ajax_url,

			// Set WordPress method to call.
			data: { action: 'cpajax_edit_comment' },

			beforeSubmit: function() {

				$('#loading').show();
				$('#submit').prop( 'disabled', 'disabled' );
				$('#submit').hide();

			}, // End beforeSubmit.

			error: function( response ) {

				// Simply bail for now.
				me.reset();
				console.log( 'edit_comment failed', response );
				return false;

			}, // End error()

			success: function( response ) {

				// Declare vars.
				var top, data, comment;

				// Convert response string to JSON.
				data = JSON.parse( response );

				// Slide up comment form.
				$('#respond').slideUp( 'fast', function() {

					// After slide.
					addComment.commentEditResetForm();
					addComment.cancelForm();
					$('#respond').show();

				});

				// Get edited comment.
				comment = $('#comment-' + data.id);

				// Add a couple of classes.
				comment.addClass( 'comment-highlighted' );

				// Replace comment content.
				$('#comment-' + data.id + ' .comment-content').html( data.content );

				/**
				 * Notify plugins that a comment has been edited.
				 *
				 * @since 3.9.12
				 *
				 * @param {Object} data The edited comment data.
				 */
				$(document).trigger( 'commentpress-ajax-comment-edited', [ data ] );

				// Show it.
				$('#comment-' + data.id + ' .comment-content').slideDown( 'fast',

					// Animation complete.
					function() {

						// Scroll to edited comment.
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

				// Reset.
				me.reset();

			} // End success()

		}); // End ajaxSubmit()

		// --<
		return false;

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



