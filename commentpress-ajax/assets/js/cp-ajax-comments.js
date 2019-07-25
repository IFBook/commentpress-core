/*
================================================================================
CommentPress Core AJAX Comment Submission
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

This script enables AJAX comment posting when a CommentPress Core compatible
theme is active.

Based loosely on the 'Ajax Comment Posting' WordPress plugin (version 2.0)

--------------------------------------------------------------------------------
*/



/**
 * Create AJAX sub-namespace.
 *
 * @since 3.8
 */
CommentPress.ajax = {};



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
		this.cpajax_post_comment_status = CommentpressAjaxSettings.cpajax_post_comment_status;
		this.cpajax_lang = CommentpressAjaxSettings.cpajax_lang;
		this.cpajax_interval = CommentpressAjaxSettings.cpajax_comment_refresh_interval;

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

		// Trigger repeat calls.
		me.updater( me.cpajax_live );

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

		// Enable comment reassignment.
		me.reassign_comments();

		// Enable AJAX comment editing.
		me.edit_comments_setup();

		// Enable listeners.
		me.listeners();

	};



	/**
	 * Initialise the jQuery text highlighter listeners.
	 *
	 * This method should only be called once. To reset the system, call:
	 * CommentPress.textselector.reset();
	 *
	 * @since 3.8
	 */
	this.listeners = function() {

		/**
		 * Hook into the CommentPress Core theme "document ready" trigger.
		 *
		 * @since 3.8
		 */
		$( document ).on( 'commentpress-document-ready', function( event ) {

			// Re-enable AJAX functionality.
			me.reassign_comments();

			// Re-enable AJAX comment editing.
			me.edit_comments_setup();

			// Compatibility with Featured Comments.
			cpajax_reenable_featured_comments();

			// Compatibility with Comment Upvoter.
			cpajax_reenable_comment_upvoter();

		});



		/**
		 * Hook into WordPress Front-end Editor.
		 *
		 * @since 3.8
		 */
		$( document ).on( 'fee-after-save', function( event ) {

			// Re-enable CommentPress Core AJAX clicks.
			me.reassign_comments();

			// Re-enable AJAX comment editing.
			me.edit_comments_setup();

			// Compatibility with Featured Comments.
			cpajax_reenable_featured_comments();

			// Compatibility with Comment Upvoter.
			cpajax_reenable_comment_upvoter();

		});

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
	 * AJAX updater which toggles periodic calls to the server to update comments.
	 *
	 * @see CommentPress.ajax.comments.update()
	 *
	 * @since 3.8
	 *
	 * @param string toggle Whether to switch the interval on or off.
	 */
	this.updater = function( toggle ) {

		// If set.
		if ( toggle == '1' ) {

			/*
			 * NOTE: comment_flood_filter is set to 15000, so that's what we set
			 * here. This ain't chat :) If you want to change this to something
			 * more 'chat-like', add this to your theme's functions.php or
			 * uncomment it in cp-ajax-comment.php:
			 *
			 * remove_filter( 'comment_flood_filter', 'wp_throttle_comment_flood', 10, 3 );
			 *
			 * Use at your own risk - it could be very heavy on the database.
			 */

			// Set repeat call.
			CommentpressAjaxSettings.interval = window.setInterval( me.update, me.cpajax_interval );

		} else {

			// Stop repeat.
			window.clearInterval( CommentpressAjaxSettings.interval );

		}

	};



	/**
	 * Perform an AJAX request to update the comments on a post.
	 *
	 * @since 3.8
	 */
	this.update = function() {

		// Kick out if submitting a comment.
		if ( me.cpajax_submitting ) { return; }

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
				action: 'cpajax_get_new_comments',

				// Send last comment count.
				last_count: CommentpressAjaxSettings.cpajax_comment_count,

				// Send post ID.
				post_id: me.cpajax_post_id

			},

			// Callback.
			function( data, textStatus ) {

				// If success.
				if ( textStatus == 'success' ) {

					// Pass to callback function.
					me.callback( data );

				}

			},

			// Expected format.
			'json'

		);

	};



	/**
	 * AJAX callback method.
	 *
	 * This method gets called when data has been recieved from the server via
	 * an AJAX request.
	 *
	 * @since 3.8
	 *
	 * @param object data The data returned from the AJAX request.
	 */
	this.callback = function( data ) {

		// Define vars.
		var diff, i, comment;

		// Get diff.
		diff = parseInt( data.cpajax_comment_count ) - parseInt( CommentpressAjaxSettings.cpajax_comment_count );

		// Did we get any new comments?
		if ( diff > 0 ) {

			// Loop through them.
			for( i = 1; i <= diff; i++ ) {

				// Get comment array. Wwill rejig when I can find a way to pass nested arrays.
				comment = eval( 'data.' + 'cpajax_new_comment_' + i );

				// Deal with each comment.
				me.add_new_comment( $(comment.markup), comment.text_sig, comment.parent, comment.id );

				// Increment global.
				CommentpressAjaxSettings.cpajax_comment_count++;

			}

		}

	};



	/**
	 * Add comment to page.
	 *
	 * @since 3.8
	 *
	 * @param object markup The jQuery object containing the comment.
	 * @param string text_sig The text signature.
	 * @param string comm_parent The numeric ID of the parent comment.
	 * @param string comm_id The numeric ID of the comment.
	 */
	this.add_new_comment = function( markup, text_sig, comm_parent, comm_id ) {

		// Define vars.
		var comment_container, para_id, head_id, comm_list, parent_id, child_list,
			head, head_array, comment_num, new_comment_count;

		// Get container.
		comment_container = $('div.comments_container');

		// Kick out if we have it already.
		if ( comment_container.find( '#li-comment-' + comm_id )[0] ) { return; }

		// Get useful IDs.
		para_id = '#para_wrapper-' + text_sig;
		head_id = '#para_heading-' + text_sig;

		// Find the commentlist we want.
		comm_list = $(para_id + ' ol.commentlist:first');

		// If the comment is a reply, append the comment to the children.
		if ( comm_parent != '0' ) {

			parent_id = '#li-comment-' + comm_parent;

			// Find the child list we want.
			child_list = $(parent_id + ' > ol.children:first');

			// Is there a child list?
			if ( child_list[0] ) {

				markup.hide()
					  .addClass( 'comment-highlighted' )
					  .appendTo( child_list )
					  .slideDown( 'fast', function() {

							// Remove highlight class.
							markup.addClass( 'comment-fade' );

					  });

			} else {

				markup.wrap( '<ol class="children" />' )
					  .parent()
					  .addClass( 'comment-highlighted' )
					  .hide()
					  .appendTo( parent_id )
					  .slideDown( 'fast', function() {

							// Animate to white.
							markup.parent().addClass( 'comment-fade' );

					  });

			}

		// If not, append the new comment at the bottom.
		} else {

			// Is there a comment list?
			if ( comm_list[0] ) {

				markup.hide()
					  .addClass( 'comment-highlighted' )
					  .appendTo( comm_list )
					  .slideDown( 'fast', function() {

							// Animate to white.
							markup.addClass( 'comment-fade' );

					  });

			} else {

				markup.wrap( '<ol class="commentlist" />' )
					  .parent()
					  .addClass( 'comment-highlighted' )
					  .hide()
					  .prependTo( para_id )
					  .slideDown( 'fast', function() {

							// Animate to white.
							markup.parent().addClass( 'comment-fade' );

					  });

			}

		}

		// Get current count.
		comment_num = parseInt( $(head_id + ' a span.cp_comment_num').text() );

		// Increment.
		new_comment_count = comment_num + 1;

		// Update heading.
		me.update_comments_para_heading( head_id, new_comment_count );

		// Find header and prepare.
		head = $(head_id);

		// Add notransition class.
		head.addClass( 'notransition' );

		// Remove existing classes.
		if ( head.hasClass( 'heading-fade' ) ) {
			head.removeClass( 'heading-fade' );
		}
		if ( head.hasClass( 'heading-highlighted' ) ) {
			head.removeClass( 'heading-highlighted' );
		}

		// Highlight.
		head.addClass( 'heading-highlighted' );

		// Remove notransition class.
		head.removeClass( 'notransition' );

		// Trigger reflow.
		head.height();

		// Animate to existing bg-color from CSS file.
		head.addClass( 'heading-fade' );

		// Update paragraph icon.
		me.update_para_icon( text_sig, new_comment_count );

		// Re-enable clicks.
		me.reassign_comments();

		// Re-enable AJAX comment editing.
		me.edit_comments_setup();

		// Compatibility with Featured Comments.
		cpajax_reenable_featured_comments();

		// Compatibility with Comment Upvoter.
		cpajax_reenable_comment_upvoter();

		// Broadcast that we're done and pass new comment ID.
		$(document).trigger( 'commentpress-ajax-new-comment-added', [ comm_id ] );

	};



	/**
	 * Enable reassignment of comments.
	 *
	 * @since 3.8
	 */
	this.reassign_comments = function() {

		// Define vars.
		var draggers, droppers, text_sig, options, alert_text, div;

		// Get all draggable items.
		var draggers = $('#comments_sidebar .comment-wrapper .comment-assign');

		// Show them.
		draggers.show();

		// Make comment reassign button draggable.
		draggers.draggable({

			// A copy thereof.
			helper: 'clone',
			cursor: 'move'

		});

		// Get all droppable items.
		droppers = $('#content .post .textblock');

		// Make textblocks droppable.
		droppers.droppable({

			// Configure droppers.
			accept: '.comment-assign',
			hoverClass: 'selected_para selected_dropzone',

			// When the button is dropped.
			drop: function( event, ui ) {

				// Get id of dropped-on item.
				text_sig = $(this).prop('id').split('-')[1];

				// Create options for modal dialog.
				options = {

					resizable: false,
					width: 400,
					height: 200,
					zIndex: 3999,
					modal: true,
					dialogClass: 'wp-dialog',
					buttons: {
						"Yes": function() {

							// Let's do it.
							$(this).dialog( "option", "disabled", true );

							// Clear buttons.
							$('.ui-dialog-buttonset').html(
								'<img src="' + me.cpajax_spinner_url + '" id="loading" alt="' + me.cpajax_lang[0] + '" />'
							);

							// Alert title.
							$('.ui-dialog-title').html( me.cpajax_lang[9] );

							// Show message.
							$('.cp_alert_text').html( me.cpajax_lang[10] );

							// Call function.
							me.reassign( text_sig, ui );

						},
						"Cancel": function() {

							// Cancel
							$(this).dialog( 'close' );
							$(this).dialog( 'destroy' );
							$(this).remove();

						}

					}

				};

				// Define message.
				alert_text = me.cpajax_lang[8];

				// Create modal dialog.
				div = $('<div><p class="cp_alert_text">' + alert_text + '</p></div>');
				div.prop( 'title', me.cpajax_lang[7] )
				   .appendTo( 'body' )
				   .dialog( options );

			}

		});

	};



	/**
	 * Reassign a comment.
	 *
	 * @since 3.8
	 *
	 * @param string text_sig The text signature.
	 * @param object ui The UI element.
	 */
	this.reassign = function( text_sig, ui ) {

		// Define vars.
		var comment_id, comment_item, comment_to_move, other_comments, comment_list;

		// Get comment ID.
		comment_id = $(ui.draggable).prop('id').split('-')[1];

		// Get comment parent li.
		comment_item = $(ui.draggable).closest( 'li.comment' );

		// Assign as comment to move.
		comment_to_move = comment_item;

		// Get siblings.
		other_comments = comment_item.siblings( 'li.comment' );

		// Are there any?
		if ( other_comments.length == 0 ) {

			// Get comment list, because we need to remove the entire list.
			comment_list = comment_item.parent( 'ol.commentlist' );

			// Overwrite comment to move.
			comment_to_move = comment_list;

		}

		// Slide our comment up.
		$(comment_to_move).slideUp( 'slow',

			// Animation complete.
			function() {

				/*
				// We could reassign via Javascript, but refreshing the page will clear
				// any possible markup issues, so go with that instead.

				// Find target paragraph wrapper.
				var para_wrapper = $('#para_wrapper-' + text_sig);

				// Get nested commentlist.
				var target_list = para_wrapper.children( 'ol.commentlist' );

				// Does it already have a commentlist?
				if ( target_list.length > 0 ) {

					// Yes, append just the comment item to the list.
					comment_item.appendTo( target_list )
								.css( 'display', 'block' )
								.parent()
								.css( 'display', 'block' );

				} else {

					// No, we must prepend the list, wrapping the item in one if necessary.

					// Do we have the list defined?
					if ( other_comments.length > 0 ) {

						// No, wrap item in list, then prepend.
						comment_item.wrap( '<ol class="commentlist" />' )
									.css( 'display', 'block' )
									.parent()
									.css( 'display', 'block' )
									.prependTo( para_wrapper );

					} else {

						// Prepend list.
						comment_item.css( 'display', 'block' )
									.parent()
									.css( 'display', 'block' )
									.prependTo( para_wrapper );

					}

				}
				*/

				// Use post.
				$.post(

					// Set URL.
					me.cpajax_ajax_url,

					// Set params.
					{

						// Action (function in WordPress)
						action: 'cpajax_reassign_comment',

						// Send text sig.
						text_signature: text_sig,

						// Send post ID.
						comment_id: comment_id

					 },

					// Callback.
					function( data, textStatus ) {

						// If success.
						if ( textStatus == 'success' ) {

							// Refresh from server.
							document.location.reload( true );

						} else {

							// Show error.
							alert( textStatus );

						}

					},

					// Expected format.
					'json'

				);

			}

		);

	};



	/**
	 * Add comment to page in response to the comment form being submitted.
	 *
	 * @since 3.8
	 *
	 * @param object response The jQuery object containing the result of the AJAX request.
	 */
	this.add_comment = function( response ) {

		// Define vars.
		var text_sig, comm_parent, para_id, head_id, parent_id, child_list,
			comm_list, comment_num, new_comment_count, new_comm_id;

		// Get form data.
		text_sig = me.cpajax_form.find( '#text_signature' ).val();
		comm_parent = me.cpajax_form.find( '#comment_parent' ).val();

		// Get useful IDs.
		para_id = '#para_wrapper-' + text_sig;
		head_id = '#para_heading-' + text_sig;

		// We no longer have zero comments.
		$(para_id).removeClass( 'no_comments' );
		$(head_id).removeClass( 'no_comments' );

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

			// Find the commentlist we want.
			comm_list = $(para_id + ' > ol.commentlist:first');

			// Is there a comment list?
			if ( comm_list[0] ) {

				new_comm_id = me.nice_append(
					response,
					para_id + ' > ol.commentlist:first > li:last',
					comm_list,
					para_id + ' > ol.commentlist:first > li:last'
				);

			} else {

				new_comm_id = me.nice_prepend(
					response,
					para_id + ' > ol.commentlist:first',
					para_id,
					para_id + ' > ol.commentlist:first > li:last'
				);

			}

		}

		// Slide up comment form.
		$('#respond').slideUp( 'fast', function() {

			// After slide.
			addComment.cancelForm();

		});

		// Get existing current count.
		comment_num = parseInt( $(head_id + ' a span.cp_comment_num').text() );

		// Increment.
		new_comment_count = comment_num + 1;

		// Update heading.
		me.update_comments_para_heading( head_id, new_comment_count );

		// Update paragraph icon.
		me.update_para_icon( text_sig, new_comment_count );

		// If not the whole page.
		if( text_sig != '' ) {

			// Scroll page to text block.
			CommentPress.common.content.scroll_page( $('#textblock-' + text_sig) );

		} else {

			// Scroll to top.
			CommentPress.theme.viewport.scroll_to_top( 0, cp_scroll_speed );

		}

		// Re-enable clicks.
		me.reassign_comments();

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
		// Which have lost their original context
		if ( 'undefined' === typeof response || response === null ) { return; }

		// Make a copy of the new comment.
		new_comment = response.find(content).clone();

		// Hide and prepend.
		new_comment.prependTo(target).hide();

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

		// Add a couple of classes.
		comment.addClass( 'comment-highlighted' );

		$(content).slideDown( 'slow',

			// Animation complete.
			function() {

				// Scroll to new comment.
				$('#comments_sidebar .sidebar_contents_wrapper').stop(true).scrollTo(
					comment,
					{
						duration: cp_scroll_speed,
						axis: 'y',
						onAfter: function() {

							// Remove highlight class.
							comment.addClass( 'comment-fade' );

							// Broadcast that animation is done.
							$(document).trigger( 'commentpress-ajax-comment-added-scrolled' );

						}
					}
				);

			}

		); // End slide.

		// --<
		return new_comm_id;

	};



	/**
	 * Update comments paragraph heading.
	 *
	 * @since 3.8
	 *
	 * @param string head_id The CSS ID of the header element.
	 * @param int new_comment_count The updated number of comments.
	 */
	this.update_comments_para_heading = function( head_id, new_comment_count ) {

		// Increment.
		$(head_id + ' a span.cp_comment_num').text( new_comment_count.toString() );

		// If exactly one comment.
		if ( new_comment_count == 1 ) {

			// Update current word.
			$(head_id + ' a span.cp_comment_word').text( me.cpajax_lang[11] );

		}

		// If greater than one comment.
		if ( new_comment_count > 1 ) {

			// Update current word.
			$(head_id + ' a span.cp_comment_word').text( me.cpajax_lang[12] );

		}

	};



	/**
	 * Update paragraph comment icon.
	 *
	 * @since 3.8
	 *
	 * @param string text_sig The text signature of the paragraph.
	 * @param int new_comment_count The updated number of comments.
	 */
	this.update_para_icon = function( text_sig, new_comment_count ) {

		// Define vars.
		var textblock_id;

		// Construct textblock_id.
		textblock_id = '#textblock-' + text_sig;

		// Set comment icon text.
		$(textblock_id + ' span small').text( new_comment_count.toString() );

		// If we're changing from 0 to 1.
		if ( new_comment_count == 1 ) {

			// Set comment icon.
			$(textblock_id + ' span.commenticonbox a.para_permalink').removeClass( 'no_comments' );
			$(textblock_id + ' span.commenticonbox a.para_permalink').addClass( 'has_comments' );

			// Show comment icon text.
			$(textblock_id + ' span small').css( 'visibility', 'visible' );

		}

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
		 * @since 3.8
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

		// Bail if comments are closed, since there's no TinyMCE.
		if ( this.cpajax_post_comment_status !== 'open' ) {
			return;
		}

		// Unbind first to allow repeated calls to this function.
		$('#comments_sidebar').off( 'click', '.comment-edit-link' );

		/**
		 * Clicking on comment edit links in the Comments column.
		 *
		 * @since 3.9.12
		 */
		$('#comments_sidebar').on( 'click', '.comment-edit-link', function( event ) {

			// Define vars.
			var comment_id;

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
	 * AJAX callback method.
	 *
	 * This method gets called when data has been received from the server via
	 * the AJAX request in this.get_comment().
	 *
	 * @since 3.9.12
	 *
	 * @param object data The data returned from the AJAX request.
	 */
	this.get_comment_callback = function( data ) {

		// Update text_selection hidden input if there's a selection.
		if ( parseInt( data.sel_start ) !== 0 && parseInt( data.sel_end ) !== 0 ) {
			CommentPress.texthighlighter.commentform.current_selection_set({
				start: parseInt( data.sel_start ),
				end: parseInt( data.sel_end )
			});
		} else {
			CommentPress.texthighlighter.commentform.current_selection_clear();
		}

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

	/**
	 * AJAX comment updating control mechanism?
	 *
	$('#btn_js').toggle( function() {

		// Trigger repeat calls.
		CommentPress.ajax.comments.updater( false );

		$(this).text('Javascript Off');

		return false;

	}, function() {

		// Trigger repeat calls.
		CommentPress.ajax.comments.updater( true );

		$(this).text('Javascript On');

		return false;

	});
	 */

}); // End document.ready()



