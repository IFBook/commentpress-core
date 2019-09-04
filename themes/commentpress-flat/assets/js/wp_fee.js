/*
================================================================================
CommentPress Flat WP FEE Compatibility Javascript
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

The following code relates specifically to WordPress Front-end Editor and
provides  a measure of compatibility with it. WordPress Front-end Editor is
still in development, so much of this is liable to change.

--------------------------------------------------------------------------------
*/



// Define global scope vars.
var cp_ajax_url, cp_spinner_url, cp_post_id, cp_post_multipage, cp_post_page,
	cp_options_title, cp_column_title_original;

// Test for our localisation object.
if ( 'undefined' !== typeof CommentpressSettings ) {

	cp_ajax_url = CommentpressSettings.cp_ajax_url;
	cp_spinner_url = CommentpressSettings.cp_spinner_url;
	cp_post_id = CommentpressSettings.cp_post_id;
	cp_post_multipage = CommentpressSettings.cp_post_multipage;
	cp_post_page = CommentpressSettings.cp_post_page;
	cp_options_title = CommentpressSettings.cp_options_title;

}



/**
 * Send single data item to server.
 *
 * @since 3.8
 *
 * @param method The WordPress function to call.
 * @param value The single data item to send.
 * @return boolean success Whether successful or not.
 */
function cp_send_to_server( method, key, value, callback ) {

	var data;

	// Create data array.
	data = {

		// WordPress method to call.
		action: method,

		// Send data.
		post_id: cp_post_id

	};

	// Add key/value.
	data[key] = value;

	// Send to server.
	jQuery.post(

		// Set URL.
		cp_ajax_url,

		// Add data.
		data,

		// Callback.
		function( data, textStatus ) {

			// If success.
			if ( textStatus == 'success' ) {
				window[callback]( data );
			}

		},

		// Expected format.
		'json'

	);

}



/**
 * Callback for AJAX request for Page Title Visibility change.
 *
 * @since 3.8
 *
 * @param data The data returned by the AJAX call in cp_send_to_server().
 */
function cp_title_visibility_changed( data ) {

	// If all went well, update element.
	if ( data.error == 'success' ) {
		if ( data.toggle == 'show' ) {
			jQuery( 'h2.post_title' ).show();
		} else {
			jQuery( 'h2.post_title' ).hide();
		}
	}

}



/**
 * Callback for AJAX request for Page Meta Visibility change.
 *
 * @since 3.8
 *
 * @param data The data returned by the AJAX call in cp_send_to_server().
 */
function cp_meta_visibility_changed( data ) {

	// If all went well, update element.
	if ( data.error == 'success' ) {
		if ( data.toggle == 'show' ) {
			jQuery( '.search_meta' ).show();
		} else {
			jQuery( '.search_meta' ).hide();
		}
	}

}



/**
 * Callback for AJAX request for Text Formatting change.
 *
 * @since 3.8
 *
 * @param data The data returned by the AJAX call in cp_send_to_server().
 */
function cp_text_parser_changed( data ) {

	// If all went well, update element.
	if ( data.error == 'success' ) {
		jQuery( '.page_num_bottom' ).html( data.number );
	}

}



/**
 * Callback for AJAX request for number format change.
 *
 * @since 3.8
 *
 * @param data The data returned by the AJAX call in cp_send_to_server().
 */
function cp_number_format_changed( data ) {

	// If all went well, update element.
	if ( data.error == 'success' ) {
		jQuery( '.page_num_bottom' ).html( data.number );
	}

}



/**
 * Define what happens when the page is ready.
 *
 * @since 3.8
 */
jQuery(document).ready( function($) {



	// Are comments open?
	if ( cp_comments_open == 'y' ) {

		// Put some vars into global scope.
		var cp_comment_form;

		// Disable the comment form.
		addComment.disableForm();

		// Save comment form.
		cp_comment_form = $('#respond_wrapper').clone();

		// Change the form ID so we don't get double submissions.
		$( 'form', cp_comment_form ).attr( 'id', 'commentform-clone' );

		// Save original comments column heading.
		cp_column_title_original = $( '#comments_header h2 a' ).text();

	}



	/**
	 * Hook into WordPress Front-end Editor after save.
	 *
	 * @since 3.8
	 */
	$( document ).on( 'fee-after-save', function( event ) {

	});



	/**
	 * Hook into WordPress Front-end Editor activation.
	 *
	 * @since 3.8
	 */
	$( document ).on( 'fee-on', function( event ) {

		//alert( 'fee-on' );

		// Hide comments.
		$( '#comments_sidebar .comments_container' ).fadeOut(

			function() {

				// Replace column title.
				$( '#comments_header h2 a' ).html( cp_options_title );

				// Show metabox.
				$( '#comments_sidebar .metabox_container' ).fadeIn();

			}

		);

	});



	/**
	 * Hook into WordPress Front-end Editor deactivation.
	 *
	 * @since 3.8
	 */
	$( document ).on( 'fee-off', function( event ) {

		//alert( 'fee-off' );

		// Hide metabox.
		$( '#comments_sidebar .metabox_container' ).fadeOut(

			function() {

				// Replace column title.
				$( '#comments_header h2 a' ).html( cp_column_title_original );

				/*
				// We can use this to log ajax errors from jQuery.post()
				$(document).ajaxError( function( e, xhr, settings, exception ) {
					console.log( 'error in: ' + settings.url + ' \n'+'error:\n' + xhr.responseText );
				});
				*/

				// Show comments.
				$.post(

					// Set URL.
					cp_ajax_url,

					// Add data.
					{

						// Set WordPress method to call.
						action: 'cp_get_comments_container',

						// Send post data.
						post_id: cp_post_id,
						post_multipage: cp_post_multipage,
						post_page: cp_post_page,

					},

					// Callback.
					function( data, textStatus ) {

						var comments;

						// If success.
						if ( textStatus == 'success' ) {

							// Find comments.
							comments = $( '.comments_container', $(data.comments) );

							// Get a copy of the comment form for this post
							post_comment_form = cp_comment_form.clone();

							// Change the form ID so we don't get double submissions.
							$( 'form', post_comment_form ).attr( 'id', 'commentform' );

							// Add it to the comments.
							comments.append( post_comment_form );

							// Disable the comment form.
							addComment.disableForm();

							// Replace comments.
							$( '#comments_sidebar .comments_container' ).replaceWith( comments );

							// Re-enable the comment form.
							addComment.enableForm();

							// Keep comments hidden.
							$( '#comments_sidebar .comments_container' ).hide();

							// Show comments.
							$( '#comments_sidebar .comments_container' ).fadeIn();

						}

					},

					// Expected format.
					'json'

				);
			}

		);

	});



	/**
	 * Hook into WordPress Front-end Editor before save and add items to be saved
	 * along with the post data.
	 *
	 * @since 3.8
	 */
	$( document ).on( 'fee-before-save', function( event ) {

		// Add nonce.
		wp.fee.post.commentpress_nonce = function() {
			return $( '#commentpress_nonce' ).val();
		};

		// Add text parser.
		wp.fee.post.cp_post_type_override = function() {
			return $( '#cp_post_type_override' ).val();
		};

		// Add starting paragraph number.
		wp.fee.post.cp_starting_para_number = function() {
			return $( '#cp_starting_para_number' ).val();
		};

	});



	/**
	 * Metabox element changed: Page Title Visibility dropdown.
	 *
	 * @since 3.8
	 */
	$( '#cp_title_visibility' ).on( 'change', function( event ) {

		// Local vars.
		var method, key, callback;

		method = 'cp_set_post_title_visibility';
		key = 'cp_title_visibility';
		callback = 'cp_title_visibility_changed';

		// Send.
		cp_send_to_server( method, key, this.value, callback );

	});



	/**
	 * Metabox element changed: Page Meta Visibility dropdown.
	 *
	 * @since 3.8
	 */
	$( '#cp_page_meta_visibility' ).on( 'change', function( event ) {

		// Local vars.
		var method, key, callback;

		method = 'cp_set_page_meta_visibility';
		key = 'cp_page_meta_visibility';
		callback = 'cp_meta_visibility_changed';

		// Send.
		cp_send_to_server( method, key, this.value, callback );

	});



	/**
	 * Metabox element changed: Page Number Format dropdown.
	 *
	 * @since 3.8
	 */
	$( '#cp_number_format' ).on( 'change', function( event ) {

		// Local vars.
		var method, key, callback;

		method = 'cp_set_number_format';
		key = 'cp_number_format';
		callback = 'cp_number_format_changed';

		// Send.
		cp_send_to_server( method, key, this.value, callback );

	});



	/**
	 * Metabox element changed: Text Formatting dropdown.
	 *
	 * @since 3.8
	 */
	$( '#cp_post_type_override' ).on( 'change', function( event ) {

		// Local vars.
		var method, key, callback;

		method = 'cp_set_post_type_override';
		key = 'cp_post_type_override';
		callback = 'cp_text_parser_changed';

		// Send.
		cp_send_to_server( method, key, this.value, callback );

	});



	/**
	 * Metabox element changed: Starting Paragraph Number.
	 *
	 * @since 3.8
	 */
	$( '#cp_starting_para_number' ).on( 'change', function( event ) {

		// Local vars.
		var method, key, callback;

		method = 'cp_set_starting_para_number';
		key = 'cp_starting_para_number';
		callback = 'cp_my_callback';

		// Send.
		cp_send_to_server( method, key, this.value, callback );

	});



});



