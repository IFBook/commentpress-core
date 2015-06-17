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



/**
 * Create AJAX sub-namespace
 */
CommentPress.ajax = {};



/**
 * Create CommentPress AJAX comments class
 */
CommentPress.ajax.comments = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();

	// init form submitting flag
	this.cpajax_submitting = false;

	// comment form
	this.cpajax_form = {};

	// error display element
	this.cpajax_error = {};

	// test for our localisation object
	if ( 'undefined' !== typeof CommentpressAjaxSettings ) {

		// reference our localisation object vars
		this.cpajax_live = CommentpressAjaxSettings.cpajax_live;
		this.cpajax_ajax_url = CommentpressAjaxSettings.cpajax_ajax_url;
		this.cpajax_spinner_url = CommentpressAjaxSettings.cpajax_spinner_url;
		this.cpajax_post_id = CommentpressAjaxSettings.cpajax_post_id;
		this.cpajax_lang = CommentpressAjaxSettings.cpajax_lang;

	}



	/**
	 * Initialise CommentPress AJAX.
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

		// trigger repeat calls
		me.updater( me.cpajax_live );

		// create error container
		$('#respond_title').after(
			'<div id="cpajax_error_msg"></div>'
		);

		// init AJAX spinner
		$('#submit').after(
			'<img src="' + me.cpajax_spinner_url + '" id="loading" alt="' + me.cpajax_lang[0] + '" />'
		);

		// hide spinner
		$('#loading').hide();

		// store reference to the comment form
		me.cpajax_form = $('#commentform');

		// store reference to the error div
		me.cpajax_error = $('#cpajax_error_msg');

		// hide error div
		me.cpajax_error.hide();

		// initialise comment form
		me.initialise_form();

		// enable comment reassignment
		me.reassign_comments();

		// enable listeners
		me.listeners();

	};



	/**
	 * Initialise the jQuery text highlighter listeners.
	 *
	 * This method should only be called once. To reset the system, call:
	 * CommentPress.textselector.reset();
	 *
	 * @return void
	 */
	this.listeners = function() {

		/**
		 * Hook into the CommentPress theme "document ready" trigger
		 *
		 * @return void
		 */
		$( document ).on( 'commentpress-document-ready', function( event ) {

			// re-enable AJAX functionality
			me.reassign_comments();

			// compatibility with Featured Comments
			cpajax_reenable_featured_comments();

			// compatibility with Comment Upvoter
			cpajax_reenable_comment_upvoter();

		});



		/**
		 * Hook into WordPress Front-end Editor
		 *
		 * @return void
		 */
		$( document ).on( 'fee-after-save', function( event ) {

			// re-enable CommentPress AJAX clicks
			me.reassign_comments();

			// compatibility with Featured Comments
			cpajax_reenable_featured_comments();

			// compatibility with Comment Upvoter
			cpajax_reenable_comment_upvoter();

		});

	};



	/**
	 * Reset CommentPress AJAX
	 *
	 * @return void
	 */
	this.reset = function() {

		// hide the spinner
		$('#loading').hide();

		// enable submit button
		$('#submit').removeAttr( 'disabled' );

		// make it visible
		$('#submit').show();

		// enable the comment form
		addComment.enableForm();

		// set flag to say we're done
		me.cpajax_submitting = false;

	};



	/**
	 * AJAX updater which toggles periodic calls to the server to update comments
	 *
	 * @see CommentPress.ajax.comments.update()
	 *
	 * @param string toggle Whether to switch the interval on or off
	 * @return void
	 */
	this.updater = function( toggle ) {

		// if set
		if ( toggle == '1' ) {

			/**
			 * NOTE: comment_flood_filter is set to 15000, so that's what we set
			 * here. This ain't chat :) If you want to change this to something
			 * more 'chat-like', add this to your theme's functions.php or
			 * uncomment it in cp-ajax-comment.php:
			 *
			 * remove_filter( 'comment_flood_filter', 'wp_throttle_comment_flood', 10, 3 );
			 *
			 * Use at your own risk - it could be very heavy on the database.
			 */

			// set repeat call
			CommentpressAjaxSettings.interval = window.setInterval( me.update, 5000 );

		} else {

			// stop repeat
			window.clearInterval( CommentpressAjaxSettings.interval );

		}

	};



	/**
	 * Perform an AJAX request to update the comments on a post
	 *
	 * @return void
	 */
	this.update = function() {

		// kick out if submitting a comment
		if ( me.cpajax_submitting ) { return; }

		/**
		 * Use the following to log ajax errors from jQuery.post():
		 *
		 * $(document).ajaxError( function( e, xhr, settings, exception ) {
		 *   console.log( 'error in: ' + settings.url + ' \n'+'error:\n' + xhr.responseText );
		 * });
		 *
		 */

		// use post method
		$.post(

			// set URL
			me.cpajax_ajax_url,

			// add data
			{

				// set WordPress method to call
				action: 'cpajax_get_new_comments',

				// send last comment count
				last_count: CommentpressAjaxSettings.cpajax_comment_count,

				// send post ID
				post_id: me.cpajax_post_id

			},

			// callback
			function( data, textStatus ) {

				//console.log( data );
				//console.log( textStatus );

				// if success
				if ( textStatus == 'success' ) {

					// pass to callback function
					me.callback( data );

				}

			},

			// expected format
			'json'

		);

	};



	/**
	 * AJAX callback method
	 *
	 * This method gets called when data has been recieved from the server via
	 * an AJAX request.
	 *
	 * @param object data The data returned from the AJAX request
	 * @return void
	 */
	this.callback = function( data ) {

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
				me.add_new_comment( $(comment.markup), comment.text_sig, comment.parent, comment.id );

				// increment global
				CommentpressAjaxSettings.cpajax_comment_count++;

			}

		}

	};



	/**
	 * Add comment to page
	 *
	 * @param object markup The jQuery object containing the comment
	 * @param string text_sig The text signature
	 * @param string comm_parent The numeric ID of the parent comment
	 * @param string comm_id The numeric ID of the comment
	 * @return void
	 */
	this.add_new_comment = function( markup, text_sig, comm_parent, comm_id ) {

		// define vars
		var comment_container, para_id, head_id, comm_list, parent_id, child_list,
			head, head_array, comment_num, new_comment_count;

		// get container
		comment_container = $('div.comments_container');

		// kick out if we have it already
		if ( comment_container.find( '#li-comment-' + comm_id )[0] ) { return; }

		// get useful ids
		para_id = '#para_wrapper-' + text_sig;
		head_id = '#para_heading-' + text_sig;

		// find the commentlist we want
		comm_list = $(para_id + ' ol.commentlist:first');

		// if the comment is a reply, append the comment to the children
		if ( comm_parent != '0' ) {

			//console.log( comm_parent );
			parent_id = '#li-comment-' + comm_parent;

			// find the child list we want
			child_list = $(parent_id + ' > ol.children:first');

			// is there a child list?
			if ( child_list[0] ) {

				markup.hide()
					  .addClass( 'comment-highlighted' )
					  .appendTo( child_list )
					  .slideDown( 'fast', function() {

							// remove highlight class
							markup.addClass( 'comment-fade' );

					  });

			} else {

				markup.wrap( '<ol class="children" />' )
					  .parent()
					  .addClass( 'comment-highlighted' )
					  .hide()
					  .appendTo( parent_id )
					  .slideDown( 'fast', function() {

							// animate to white
							markup.parent().addClass( 'comment-fade' );

					  });

			}

		// if not, append the new comment at the bottom
		} else {

			// is there a comment list?
			if ( comm_list[0] ) {

				markup.hide()
					  .addClass( 'comment-highlighted' )
					  .appendTo( comm_list )
					  .slideDown( 'fast', function() {

							// animate to white
							markup.addClass( 'comment-fade' );

					  });

			} else {

				markup.wrap( '<ol class="commentlist" />' )
					  .parent()
					  .addClass( 'comment-highlighted' )
					  .hide()
					  .prependTo( para_id )
					  .slideDown( 'fast', function() {

							// animate to white
							markup.parent().addClass( 'comment-fade' );

					  });

			}

		}

		// get current count
		comment_num = parseInt( $(head_id + ' a span.cp_comment_num').text() );

		// increment
		new_comment_count = comment_num + 1;

		// update heading
		me.update_comments_para_heading( head_id, new_comment_count );

		// find header and prepare
		head = $(head_id);

		// add notransition class
		head.addClass( 'notransition' );

		// remove existing classes
		if ( head.hasClass( 'heading-fade' ) ) {
			head.removeClass( 'heading-fade' );
		}
		if ( head.hasClass( 'heading-highlighted' ) ) {
			head.removeClass( 'heading-highlighted' );
		}

		// highlight
		head.addClass( 'heading-highlighted' );

		// remove notransition class
		head.removeClass( 'notransition' );

		// trigger reflow
		head.height();

		// animate to existing bg (from css file)
		head.addClass( 'heading-fade' );

		// update paragraph icon
		me.update_para_icon( text_sig, new_comment_count );

		// re-enable clicks
		me.reassign_comments();

		// compatibility with Featured Comments
		cpajax_reenable_featured_comments();

		// compatibility with Comment Upvoter
		cpajax_reenable_comment_upvoter();

		// broadcast that we're done and pass new comment ID
		$(document).trigger( 'commentpress-ajax-new-comment-added', [ comm_id ] );

	};



	/**
	 * Enable reassignment of comments
	 *
	 * @return void
	 */
	this.reassign_comments = function() {

		// define vars
		var draggers, droppers, text_sig, options, alert_text, div;

		// get all draggable items
		var draggers = $('#comments_sidebar .comment-wrapper .comment-assign');

		// show them
		draggers.show();

		// make comment reassign button draggable
		draggers.draggable({

			// a copy thereof...
			helper: 'clone',
			cursor: 'move'

		});

		// get all droppable items
		droppers = $('#content .post .textblock');
		//console.log( droppers );

		// make textblocks droppable
		droppers.droppable({

			// configure droppers
			accept: '.comment-assign',
			hoverClass: 'selected_para selected_dropzone',

			// when the button is dropped
			drop: function( event, ui ) {

				// get id of dropped-on item
				text_sig = $(this).prop('id').split('-')[1];

				// create options for modal dialog
				options = {

					resizable: false,
					width: 400,
					height: 200,
					zIndex: 3999,
					modal: true,
					dialogClass: 'wp-dialog',
					buttons: {
						"Yes": function() {

							// let's do it
							$(this).dialog( "option", "disabled", true );

							// clear buttons
							$('.ui-dialog-buttonset').html(
								'<img src="' + me.cpajax_spinner_url + '" id="loading" alt="' + me.cpajax_lang[0] + '" />'
							);

							// alert title
							$('.ui-dialog-title').html( me.cpajax_lang[9] );

							// show message
							$('.cp_alert_text').html( me.cpajax_lang[10] );

							// call function
							me.reassign( text_sig, ui );

						},
						"Cancel": function() {

							// cancel
							$(this).dialog( 'close' );
							$(this).dialog( 'destroy' );
							$(this).remove();

						}

					}

				};

				// define message
				alert_text = me.cpajax_lang[8];

				// create modal dialog
				div = $('<div><p class="cp_alert_text">' + alert_text + '</p></div>');
				div.prop( 'title', me.cpajax_lang[7] )
				   .appendTo( 'body' )
				   .dialog( options );

			}

		});

	};



	/**
	 * Reassign a comment
	 *
	 * @param string text_sig The text signature
	 * @param object ui The UI element
	 * @return void
	 */
	this.reassign = function( text_sig, ui ) {

		// define vars
		var comment_id, comment_item, comment_to_move, other_comments, comment_list;

		// get comment id
		comment_id = $(ui.draggable).prop('id').split('-')[1];

		// let's see what params we've got
		//console.log( 'text_sig: ' + text_sig );
		//console.log( 'comment id: ' + comment_id );

		// get comment parent li
		comment_item = $(ui.draggable).closest( 'li.comment' );

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
		$(comment_to_move).slideUp( 'slow',

			// animation complete
			function() {

				/*
				// We could reassign via Javascript, but refreshing the page will clear
				// any possible markup issues, so go with that instead...

				// find target paragraph wrapper
				var para_wrapper = $('#para_wrapper-' + text_sig);

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
				$.post(

					// set URL
					me.cpajax_ajax_url,

					// set params
					{

						// action (function in WordPress)
						action: 'cpajax_reassign_comment',

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

	};



	/**
	 * Add comment to page in response to the comment form being submitted
	 *
	 * @param object response The jQuery object containing the result of the AJAX request
	 * @return void
	 */
	this.add_comment = function( response ) {

		// define vars
		var text_sig, comm_parent, para_id, head_id, parent_id, child_list,
			comm_list, comment_num, new_comment_count, new_comm_id;

		// get form data
		text_sig = me.cpajax_form.find( '#text_signature' ).val();
		comm_parent = me.cpajax_form.find( '#comment_parent' ).val();

		// get useful ids
		para_id = '#para_wrapper-' + text_sig;
		head_id = '#para_heading-' + text_sig;

		/*
		console.log( 'response' );
		console.log( response );
		console.log( 'text_sig' );
		console.log( text_sig );
		console.log( 'comm_parent' );
		console.log( comm_parent );
		console.log( 'para_id' );
		console.log( para_id );
		console.log( 'head_id' );
		console.log( head_id );
		*/

		// we no longer have zero comments
		$(para_id).removeClass( 'no_comments' );

		// if the comment is a reply, append the comment to the children
		if ( comm_parent != '0' ) {

			// get parent
			parent_id = '#li-comment-' + comm_parent;

			// find the child list we want
			child_list = $(parent_id + ' > ol.children:first');

			// is there a child list?
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

		// if not, append the new comment at the bottom
		} else {

			// find the commentlist we want
			comm_list = $(para_id + ' > ol.commentlist:first');

			// is there a comment list?
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

		// slide up comment form
		$('#respond').slideUp( 'fast', function() {

			// after slide
			addComment.cancelForm();

		});

		// get existing current count
		comment_num = parseInt( $(head_id + ' a span.cp_comment_num').text() );

		// increment
		new_comment_count = comment_num + 1;

		// update heading
		me.update_comments_para_heading( head_id, new_comment_count );

		// update paragraph icon
		me.update_para_icon( text_sig, new_comment_count );

		// if not the whole page...
		if( text_sig != '' ) {

			// scroll page to text block
			CommentPress.common.content.scroll_page( $('#textblock-' + text_sig) );

		} else {

			// scroll to top
			CommentPress.theme.viewport.scroll_to_top( 0, cp_scroll_speed );

		}

		// re-enable clicks
		me.reassign_comments();

		// clear comment form
		me.cpajax_form.find( '#comment' ).val( '' );

		//me.cpajax_error.html('<span class="success">' + me.cpajax_lang[5] + '</span>');

		// compatibility with Featured Comments
		cpajax_reenable_featured_comments();

		// compatibility with Comment Upvoter
		cpajax_reenable_comment_upvoter();

		// broadcast that we're done and pass new comment ID
		$(document).trigger( 'commentpress-ajax-comment-added', [ new_comm_id ] );

	};



	/**
	 * Do comment append
	 *
	 * @param object response The jQuery object from the AJAX request
	 * @param object content The jQuery object containing the content
	 * @param object target The jQuery object in which the content should be placed
	 * @param object last The jQuery object of the last item in the comment list
	 * @return void
	 */
	this.nice_append = function( response, content, target, last ) {

		// define vars
		var new_comm_id;

		// test for undefined, which may happen on replies to comments
		// which have lost their original context
		if ( 'undefined' === typeof response || response === null ) { return; }

		/*
		console.log( 'content' );
		console.log( content );
		console.log( 'target' );
		console.log( target );
		console.log( 'comment' );
		console.log( response.find(content) );
		*/

		response.find(content)
				.clone()
				.hide()
				.appendTo(target);

		// clean up
		new_comm_id = me.cleanup( content, last );

		// --<
		return new_comm_id;

	};



	/**
	 * Do comment prepend
	 *
	 * @param object response The jQuery object from the AJAX request
	 * @param object content The jQuery object containing the content
	 * @param object target The jQuery object in which the content should be placed
	 * @param object last The jQuery object of the last item in the comment list
	 * @return void
	 */
	this.nice_prepend = function( response, content, target, last ) {

		// define vars
		var new_comm_id;

		// test for undefined, which may happen on replies to comments
		// which have lost their original context
		if ( 'undefined' === typeof response || response === null ) { return; }

		/*
		console.log( 'content' );
		console.log( content );
		console.log( 'target' );
		console.log( target );
		console.log( 'comment' );
		console.log( response.find(content) );
		*/

		response.find(content)
				.clone()
				.hide()
				.prependTo(target);

		// clean up
		new_comm_id = me.cleanup( content, last );

		// --<
		return new_comm_id;

	};



	/**
	 * Do comment cleanup
	 *
	 * @param object content The jQuery object containing the content
	 * @param object last The jQuery object of the last item in the comment list
	 * @return void
	 */
	this.cleanup = function( content, last ) {

		// define vars
		var last_id, new_comm_id, comment;

		// get the id of the last list item
		last_id = $(last).prop('id');

		/*
		// IE seems to grab the result from cache despite nocache_headers()
		// the following is an action of last resort - the cache is being busted in
		// commentpress_comment_post_redirect() instead
		if ( typeof last_id == 'undefined' || last_id === null ) {
			document.location.reload( true );
			return;
		}
		*/

		// construct new comment id
		new_comm_id = '#comment-' + ( last_id.toString().split('-')[2] );
		comment = $(new_comm_id);

		// add a couple of classes
		comment.addClass( 'comment-highlighted' );

		$(content).slideDown( 'slow',

			// animation complete
			function() {

				// scroll to new comment
				$('#comments_sidebar .sidebar_contents_wrapper').stop(true).scrollTo(
					comment,
					{
						duration: cp_scroll_speed,
						axis: 'y',
						onAfter: function() {

							// remove highlight class
							comment.addClass( 'comment-fade' );

							// broadcast that animation is done
							$(document).trigger( 'commentpress-ajax-comment-added-scrolled' );

						}
					}
				);

			}

		); // end slide

		// --<
		return new_comm_id;

	};



	/**
	 * Update comments paragraph heading
	 *
	 * @param string head_id The CSS ID of the header element
	 * @param int new_comment_count The updated number of comments
	 * @return void
	 */
	this.update_comments_para_heading = function( head_id, new_comment_count ) {

		// increment
		$(head_id + ' a span.cp_comment_num').text( new_comment_count.toString() );

		// if exactly one comment
		if ( new_comment_count == 1 ) {

			// update current word
			$(head_id + ' a span.cp_comment_word').text( me.cpajax_lang[11] );

		}

		// if greater than one comment
		if ( new_comment_count > 1 ) {

			// update current word
			$(head_id + ' a span.cp_comment_word').text( me.cpajax_lang[12] );

		}

	};



	/**
	 * Update paragraph comment icon
	 *
	 * @param string text_sig The text signature of the paragraph
	 * @param int new_comment_count The updated number of comments
	 * @return void
	 */
	this.update_para_icon = function( text_sig, new_comment_count ) {

		// define vars
		var textblock_id;

		// construct textblock_id
		textblock_id = '#textblock-' + text_sig;

		// set comment icon text
		$(textblock_id + ' span small').text( new_comment_count.toString() );

		// if we're changing from 0 to 1...
		if ( new_comment_count == 1 ) {

			// set comment icon
			$(textblock_id + ' span.commenticonbox a.para_permalink').addClass( 'has_comments' );

			// show comment icon text
			$(textblock_id + ' span small').css( 'visibility', 'visible' );

		}

	};



	/**
	 * Init comment form
	 *
	 * @return void
	 */
	this.initialise_form = function() {

		// unbind first to allow repeated calls to this function
		$('#commentform').off( 'submit' );

		/**
		 * Comment submission method
		 *
		 * @return false
		 */
		$('#commentform').on( 'submit', function( event ) {

			// define vars
			var filter;

			// set global flag
			me.cpajax_submitting = true;

			// hide errors
			me.cpajax_error.hide();

			// if not logged in, validate name and email
			if ( me.cpajax_form.find( '#author' )[0] ) {

				// check for name
				if ( me.cpajax_form.find( '#author' ).val() == '' ) {
					me.cpajax_error.html('<span class="error">' + me.cpajax_lang[1] + '</span>');
					me.cpajax_error.show();
					me.cpajax_submitting = false;
					return false;
				}

				// check for email
				if ( me.cpajax_form.find( '#email' ).val() == '' ) {
					me.cpajax_error.html('<span class="error">' + me.cpajax_lang[2] + '</span>');
					me.cpajax_error.show();
					me.cpajax_submitting = false;
					return false;
				}

				// validate email
				filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
				if( !filter.test( me.cpajax_form.find('#email').val() ) ) {
					me.cpajax_error.html('<span class="error">' + me.cpajax_lang[3] + '</span>');
					me.cpajax_error.show();
					if (event.preventDefault) {event.preventDefault();}
					me.cpajax_submitting = false;
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
			if ( me.cpajax_form.find( '#comment' ).val() == '' ) {
				me.cpajax_error.html('<span class="error">' + me.cpajax_lang[4] + '</span>');
				me.cpajax_error.show();
				// reload tinyMCE
				addComment.enableForm();
				me.cpajax_submitting = false;
				return false;
			}

			// submit the form
			$(this).ajaxSubmit({

				beforeSubmit: function() {

					$('#loading').show();
					$('#submit').prop('disabled','disabled');
					$('#submit').hide();

				}, // end beforeSubmit

				error: function(request) {

					// define vars
					var data;

					me.cpajax_error.empty();
					data = request.responseText.match(/<p>(.*)<\/p>/);
					me.cpajax_error.html('<span class="error">' + data[1] + '</span>');
					me.cpajax_error.show();

					me.reset();

					return false;

				}, // end error()

				success: function( data ) {

					// declare vars
					var response;

					// trace
					//console.log( data );

					// jQuery 1.9 fails to recognise the response as HTML, so
					// we *must* use parseHTML if it's available...
					if ( $.parseHTML ) {

						// if our jQuery version is 1.8+, it'll have parseHTML
						response =  $( $.parseHTML( data ) );

					} else {

						// get our data as object in the basic way
						response = $(data);

					}

					//console.log( response );

					// avoid errors if we can
					try {

						// add comment
						me.add_comment( response );
						me.reset();

					// oh well...
					} catch (e) {

						me.reset();
						alert( me.cpajax_lang[6] + '\n\n' + e );
						//console.log( data );

					} // end try

				} // end success()

			}); // end ajaxSubmit()

			// --<
			return false;

		}); // end form.submit()

	};



}; // end CommentPress AJAX comments class



/**
 * Re-enable Featured Comments plugin functionality
 *
 * @return void
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
 * Re-enable Comment Upvoter plugin functionality
 *
 * @return void
 */
function cpajax_reenable_comment_upvoter() {

	// test for the Comment Upvoter localisation object
	if ( 'undefined' !== typeof comment_upvoter ) {

		// we've got it, test for function existence
		if ( jQuery.is_function_defined( 'comment_upvoter_click' ) ) {

			// call function
			comment_upvoter_click();

		}

	}

}



/* -------------------------------------------------------------------------- */



// do immediate init
CommentPress.ajax.comments.init();



/* -------------------------------------------------------------------------- */



/**
 * Define what happens when the page is ready
 *
 * @return void
 */
jQuery(document).ready(function($) {

	// trigger DOM ready methods
	CommentPress.ajax.comments.dom_ready();

	/**
	 * AJAX comment updating control mechanism?
	 *
	$('#btn_js').toggle( function() {

		// trigger repeat calls
		CommentPress.ajax.comments.updater( false );

		$(this).text('Javascript Off');

		return false;

	}, function() {

		// trigger repeat calls
		CommentPress.ajax.comments.updater( true );

		$(this).text('Javascript On');

		return false;

	});
	 */

}); // end document.ready()



