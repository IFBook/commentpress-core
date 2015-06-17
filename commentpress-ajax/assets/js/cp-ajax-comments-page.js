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



/**
 * Create AJAX sub-namespace
 */
CommentPress.ajax = {};



/* -------------------------------------------------------------------------- */



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
	 * Add comment to page
	 *
	 * @param object response The jQuery object containing the result of the AJAX request
	 * @return void
	 */
	this.add_comment = function( response ) {

		// define vars
		var comm_parent, comm_list, parent_id, head;

		// get comment parent
		comm_parent = me.cpajax_form.find('#comment_parent').val();

		// find the commentlist we want
		comm_list = $('ol.commentlist:first');

		// if the comment is a reply, append the comment to the children
		if ( comm_parent != '0' ) {

			// get parent
			parent_id = '#li-comment-' + comm_parent;

			// find the child list we want
			child_list = $(parent_id + ' > ol.children:first');

			// is there a child list?
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

		// if not, append the new comment at the bottom
		} else {

			// is there a comment list?
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

		// get head
		head = response.find('#comments_in_page_wrapper div.comments_container > h3.general_comments_header');

		// replace heading
		$('#comments_in_page_wrapper div.comments_container > h3.general_comments_header').replaceWith(head);

		// clear comment form
		me.cpajax_form.find( '#comment' ).val( '' );

		//me.cpajax_error.html('<span class="success">' + me.cpajax_lang[5] + '</span>');

		// compatibility with Featured Comments
		cpajax_reenable_featured_comments();

		// compatibility with Comment Upvoter
		cpajax_reenable_comment_upvoter();

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

		// test for undefined, which may happen on replies to comments
		// which have lost their original context
		if ( 'undefined' === typeof response || response === null ) { return; }

		response.find(content)
				.clone()
				.hide()
				.appendTo(target);

		// clean up
		me.cleanup( content, last );

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

		// test for undefined, which may happen on replies to comments
		// which have lost their original context
		if ( 'undefined' === typeof response || response === null ) { return; }

		response.find(content)
				.clone()
				.hide()
				.prependTo(target);

		// clean up
		me.cleanup( content, last );

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

		addComment.cancelForm();

		// add a couple of classes
		comment.addClass( 'comment-highlighted' );

		$(content).slideDown( 'slow',

			// animation complete
			function() {

				// scroll to new comment
				$(window).stop(true).scrollTo(
					comment,
					{
						duration: cp_scroll_speed,
						axis: 'y',
						offset: CommentPress.theme.header.get_offset(),
						onAfter: function() {

							// remove highlight class
							comment.addClass( 'comment-fade' );

						}
					}
				);

			}

		); // end slide

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



/* -------------------------------------------------------------------------- */



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

	// initialise plugin
	CommentPress.ajax.comments.dom_ready();

}); // end document.ready()



