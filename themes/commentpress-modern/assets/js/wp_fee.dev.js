/*
================================================================================
CommentPress WP FEE Compatibility Javascript
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

The following code relates specifically to WordPress Front-end Editor and
provides  a measure of compatibility with it. WordPress Front-end Editor is
still in development, so much of this is liable to change.

--------------------------------------------------------------------------------
*/



// define global scope vars
var cp_ajax_url, cp_spinner_url, cp_post_id, cp_post_multipage, cp_post_page,
	cp_options_title, cp_column_title_original;

// test for our localisation object
if ( 'undefined' !== typeof CommentpressSettings ) {

	cp_ajax_url = CommentpressSettings.cp_ajax_url;
	cp_spinner_url = CommentpressSettings.cp_spinner_url;
	cp_post_id = CommentpressSettings.cp_post_id;
	cp_post_multipage = CommentpressSettings.cp_post_multipage;
	cp_post_page = CommentpressSettings.cp_post_page;
	cp_options_title = CommentpressSettings.cp_options_title;

}



/**
 * Send single data item to server
 *
 * @param method The WordPress function to call
 * @param value The single data item to send
 * @return boolean success Whether successful or not
 */
function cp_send_to_server( method, key, value, callback ) {

	var data;

	// create data array
	data = {

		// WordPress method to call
		action: method,

		// send data
		post_id: cp_post_id

	};

	// add key/value
	data[key] = value;

	// send to server
	jQuery.post(

		// set URL
		cp_ajax_url,

		// add data
		data,

		// callback
		function( data, textStatus ) {

			// if success
			if ( textStatus == 'success' ) {
				window[callback]( data );
			}

		},

		// expected format
		'json'

	);

}



/**
 * Callback for AJAX request for Page Title Visibility change
 *
 * @param data The data returned by the AJAX call in cp_send_to_server()
 */
function cp_title_visibility_changed( data ) {

	// if all went well, update element
	if ( data.error == 'success' ) {
		if ( data.toggle == 'show' ) {
			jQuery( 'h2.post_title' ).show();
		} else {
			jQuery( 'h2.post_title' ).hide();
		}
	}

}



/**
 * Callback for AJAX request for Page Meta Visibility change
 *
 * @param data The data returned by the AJAX call in cp_send_to_server()
 */
function cp_meta_visibility_changed( data ) {

	// if all went well, update element
	if ( data.error == 'success' ) {
		if ( data.toggle == 'show' ) {
			jQuery( '.search_meta' ).show();
		} else {
			jQuery( '.search_meta' ).hide();
		}
	}

}



/**
 * Callback for AJAX request for Text Formatting change
 *
 * @param data The data returned by the AJAX call in cp_send_to_server()
 */
function cp_text_parser_changed( data ) {

	// if all went well, update element
	if ( data.error == 'success' ) {
		jQuery( '.page_num_bottom' ).html( data.number );
	}

}



/**
 * Callback for AJAX request for number format change
 *
 * @param data The data returned by the AJAX call in cp_send_to_server()
 */
function cp_number_format_changed( data ) {

	// if all went well, update element
	if ( data.error == 'success' ) {
		jQuery( '.page_num_bottom' ).html( data.number );
	}

}



/**
 * Define what happens when the page is ready
 *
 * @return void
 */
jQuery(document).ready( function($) {



	// are comments open?
	if ( cp_comments_open == 'y' ) {

		// put some vars into global scope
		var cp_comment_form;

		// disable the comment form
		addComment.disableForm();

		// save comment form
		cp_comment_form = $('#respond_wrapper').clone();

		// change the form ID so we don't get double submissions
		$( 'form', cp_comment_form ).attr( 'id', 'commentform-clone' );

		// save original comments column heading
		cp_column_title_original = $( '#comments_header h2 a' ).text();

	}



	/**
	 * Hook into WordPress Front-end Editor after save
	 *
	 * @return void
	 */
	$( document ).on( 'fee-after-save', function( event ) {

	});



	/**
	 * Hook into WordPress Front-end Editor activation
	 *
	 * @return void
	 */
	$( document ).on( 'fee-on', function( event ) {

		//alert( 'fee-on' );

		// hide comments
		$( '#comments_sidebar .comments_container' ).fadeOut(

			function() {

				// replace column title
				$( '#comments_header h2 a' ).html( cp_options_title );

				// show metabox
				$( '#comments_sidebar .metabox_container' ).fadeIn();

			}

		);

	});



	/**
	 * Hook into WordPress Front-end Editor deactivation
	 *
	 * @return void
	 */
	$( document ).on( 'fee-off', function( event ) {

		//alert( 'fee-off' );

		// hide metabox
		$( '#comments_sidebar .metabox_container' ).fadeOut(

			function() {

				// replace column title
				$( '#comments_header h2 a' ).html( cp_column_title_original );

				/*
				// we can use this to log ajax errors from jQuery.post()
				$(document).ajaxError( function( e, xhr, settings, exception ) {
					console.log( 'error in: ' + settings.url + ' \n'+'error:\n' + xhr.responseText );
				});
				*/

				// show comments
				$.post(

					// set URL
					cp_ajax_url,

					// add data
					{

						// set WordPress method to call
						action: 'cp_get_comments_container',

						// send post data
						post_id: cp_post_id,
						post_multipage: cp_post_multipage,
						post_page: cp_post_page,

					},

					// callback
					function( data, textStatus ) {

						var comments;

						//console.log( textStatus );
						//console.log( data );

						// if success
						if ( textStatus == 'success' ) {

							// find comments
							comments = $( '.comments_container', $(data.comments) );

							// get a copy of the comment form for this post
							post_comment_form = cp_comment_form.clone();

							// change the form ID so we don't get double submissions
							$( 'form', post_comment_form ).attr( 'id', 'commentform' );

							// add it to the comments
							comments.append( post_comment_form );

							// disable the comment form
							addComment.disableForm();

							// replace comments
							$( '#comments_sidebar .comments_container' ).replaceWith( comments );

							// re-enable the comment form
							addComment.enableForm();

							// keep comments hidden
							$( '#comments_sidebar .comments_container' ).hide();

							// show comments
							$( '#comments_sidebar .comments_container' ).fadeIn();

						}

					},

					// expected format
					'json'

				);
			}

		);

	});



	/**
	 * Hook into WordPress Front-end Editor before save and add items to be saved
	 * along with the post data.
	 *
	 * @return void
	 */
	$( document ).on( 'fee-before-save', function( event ) {

		// add nonce
		wp.fee.post.commentpress_nonce = function() {
			return $( '#commentpress_nonce' ).val();
		};

		// add text parser
		wp.fee.post.cp_post_type_override = function() {
			return $( '#cp_post_type_override' ).val();
		};

		// add starting paragraph number
		wp.fee.post.cp_starting_para_number = function() {
			return $( '#cp_starting_para_number' ).val();
		};

	});



	/**
	 * Metabox element changed: Page Title Visibility dropdown
	 *
	 * @return void
	 */
	$( '#cp_title_visibility' ).on( 'change', function( event ) {

		// local vars
		var method, key, callback;

		method = 'cp_set_post_title_visibility';
		key = 'cp_title_visibility';
		callback = 'cp_title_visibility_changed';

		// send
		cp_send_to_server( method, key, this.value, callback );

	});



	/**
	 * Metabox element changed: Page Meta Visibility dropdown
	 *
	 * @return void
	 */
	$( '#cp_page_meta_visibility' ).on( 'change', function( event ) {

		// local vars
		var method, key, callback;

		method = 'cp_set_page_meta_visibility';
		key = 'cp_page_meta_visibility';
		callback = 'cp_meta_visibility_changed';

		// send
		cp_send_to_server( method, key, this.value, callback );

	});



	/**
	 * Metabox element changed: Page Number Format dropdown
	 *
	 * @return void
	 */
	$( '#cp_number_format' ).on( 'change', function( event ) {

		// local vars
		var method, key, callback;

		method = 'cp_set_number_format';
		key = 'cp_number_format';
		callback = 'cp_number_format_changed';

		// send
		cp_send_to_server( method, key, this.value, callback );

	});



	/**
	 * Metabox element changed: Text Formatting dropdown
	 *
	 * @return void
	 */
	$( '#cp_post_type_override' ).on( 'change', function( event ) {

		// local vars
		var method, key, callback;

		method = 'cp_set_post_type_override';
		key = 'cp_post_type_override';
		callback = 'cp_text_parser_changed';

		// send
		cp_send_to_server( method, key, this.value, callback );

	});



	/**
	 * Metabox element changed: Starting Paragraph Number
	 *
	 * @return void
	 */
	$( '#cp_starting_para_number' ).on( 'change', function( event ) {

		// local vars
		var method, key, callback;

		method = 'cp_set_starting_para_number';
		key = 'cp_starting_para_number';
		callback = 'cp_my_callback';

		// send
		cp_send_to_server( method, key, this.value, callback );

	});



});



