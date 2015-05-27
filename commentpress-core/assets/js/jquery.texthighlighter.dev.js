/*
================================================================================
CommentPress Core Text Selector Javascript
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

Implements commenting on text highlights

--------------------------------------------------------------------------------
*/



/**
 * Create CommentPress textselector class
 */
CommentPress.textselector = new function() {

	// store object ref
	var me = this;

	// test for our localisation object
	if ( 'undefined' !== typeof CommentpressTextSelectorSettings ) {

		// reference our localisation object vars
		this.popover = CommentpressTextSelectorSettings.popover;

	}



	/**
	 * Init container variable.
	 *
	 * This variable holds a reference to the currently active textblock and is
	 * set by the click handler on the .textblock elements as defined below.
	 *
	 * @see CommentPress.textselector.init()
	 */
	this.container = '';

	/**
	 * Setter for textselector container.
	 *
	 * @param string textblock_id The ID of the textblock that was clicked
	 * @return void
	 */
	this.container_set = function( textblock_id ) {
		this.container = textblock_id;
	};

	/**
	 * Getter for textselector container.
	 *
	 * @return string container The ID of the textblock that was clicked
	 */
	this.container_get = function() {
		return this.container;
	};



	/**
	 * Init array that stores selections for each textblock element.
	 *
	 * There is key in the master array for each textblock ID, whose value is an
	 * array of selection objects.
	 *
	 * This code is based loosely on:
	 * http://stackoverflow.com/questions/13949059/persisting-the-changes-of-range-objects-after-selection-in-html/13950376#13950376
	 */
	this.selections_by_textblock = {};

	/**
	 * Init array that stores the selection data for comments that have them.
	 *
	 * There is key in the master array for each comment ID, whose value is a
	 * selection object from which we can read the start and end values.
	 */
	this.selections_by_comment = {};

	/**
	 * Init property that stores the selection that was last sent to the editor
	 */
	this.selection_sent = {};

	/**
	 * Get current text selection
	 *
	 * @return object selection_obj The selection data
	 */
	this.selection_get = function() {

		// get current selection data
		return this.selection_get_current( document.getElementById( me.container ) );

	};

	/**
	 * Clear text selection
	 *
	 * @return void
	 */
	this.selection_clear = function() {

		// clear selection
		if (window.getSelection) {
			if (window.getSelection().empty) {  // Chrome
				window.getSelection().empty();
			} else if (window.getSelection().removeAllRanges) {  // Firefox
				window.getSelection().removeAllRanges();
			}
		} else if (document.selection) {  // IE?
			document.selection.empty();
		}

	};

	/**
	 * Build textselector selections for a comments array
	 *
	 * @param string comment_id The numerical comment ID
	 * @return void
	 */
	this.selection_build_for_comments = function() {

		/**
		 * Target only comments that have a marker class
		 *
		 * @return void
		 */
		jQuery('#comments_sidebar li.selection-exists').each( function(i) {

			// declare vars
			var item_id, comment_id, comment_key,
				class_list,
				sel_start, sel_end,
				selection_data;

			// get the current item ID
			item_id = jQuery(this).prop('id');

			// get comment ID
			comment_id = item_id.split('-')[2];

			// cast as string
			comment_key = '#comment-' + comment_id

			// get classes
			class_list = jQuery(this).attr('class').split(/\s+/);

			// find our data class names
			jQuery.each( class_list, function(index, item) {

				// find our start
				if ( item.match( 'sel_start-' ) ) {
					sel_start = parseInt( item.split('sel_start-')[1] );
				}

				// find our end
				if ( item.match( 'sel_end-' ) ) {
					sel_end = parseInt( item.split('sel_end-')[1] );
				}

				// create selection data
				selection_data = { start: sel_start, end: sel_end }

				// add to array, keyed by comment ID
				me.selections_by_comment[comment_key] = selection_data;

			});

		});

		//console.log( 'built this.selections_by_comment' );
		//console.log( this.selections_by_comment );

	}

	/**
	 * Save textselector selection for a comment
	 *
	 * @param string comment_id The numerical comment ID
	 * @return void
	 */
	this.selection_save_for_comment = function( comment_id ) {

		//console.log( 'this.selection_save_for_comment' );

		// cast as string
		var comment_key = '#comment-' + comment_id;
		//console.log( 'comment_key' );
		//console.log( comment_key );

		// get text signature
		//var text_sig = jQuery.get_text_sig_by_comment_id( comment_id );

		// get selection data that was last sent to the editor
		var selection_data = me.selection_sent;
		//console.log( 'selection_data' );
		//console.log( selection_data );

		// add to array, keyed by comment ID
		this.selections_by_comment[comment_key] = selection_data;
		//console.log( this.selections_by_comment );

	};

	/**
	 * Save textselector selection for a comment
	 *
	 * @param int comment_id The numerical comment ID
	 * @return void
	 */
	this.selection_recall_for_comment = function( comment_id ) {

		//console.log( 'recall this.selections_by_comment' );

		// declare vars
		var item, text_sig, textblock_id, comment_key;

		// cast as string
		comment_key = '#comment-' + comment_id;
		//console.log( comment_id );
		//console.log( comment_key );

		//console.log( this.selections_by_comment );

		// does the comment ID key exist?
		if ( comment_key in this.selections_by_comment ) {

			//console.log( 'recalling this.selections_by_comment[comment_key]' );
			item = this.selections_by_comment[comment_key]
			//console.log( 'this.selections_by_comment[comment_key]' );
			//console.log( item );

			// get text signature for this comment
			text_sig = jQuery.get_text_sig_by_comment_id( comment_key );
			//console.log( 'text_sig' );
			//console.log( text_sig );

			// get textblock
			textblock_id = 'textblock-' + text_sig;
			//console.log( 'textblock_id' );
			//console.log( textblock_id );

			// restore the selection
			me.selection_restore( document.getElementById( textblock_id ), item );
			jQuery('#' + textblock_id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight' );

		}

	};

	/**
	 * Save textselector selection
	 *
	 * @param string textblock_id The element ID
	 * @return void
	 */
	this.selection_save_for_textblock = function( textblock_id ) {

		// get selection data
		var selection_data = me.selection_get_current( document.getElementById( textblock_id ) );

		// create the array, keyed by textblock ID, if it doesn't exist
		if ( !(textblock_id in this.selections_by_textblock) ) { this.selections_by_textblock[textblock_id] = [] }

		// add selection data to the array
		this.selections_by_textblock[textblock_id].push( selection_data );

		/*
		console.log( 'save this.selections_by_textblock' );
		console.log( this.selections_by_textblock );
		*/

	};

	/**
	 * Recall all textselector selections
	 *
	 * @param string textblock_id The element ID
	 * @return void
	 */
	this.selection_recall_for_textblock = function( textblock_id ) {

		//console.log( 'recall this.selections_by_textblock' );
		//console.log( this.selections_by_textblock );

		// does the textblock ID key exist?
		if ( textblock_id in this.selections_by_textblock ) {

			// yes, restore each selection in turn
			for (var i = 0, item; item = this.selections_by_textblock[textblock_id][i++];) {
				//console.log( 'recalling this.selections_by_textblock[textblock_id][i]' );
				//console.log( item );
				me.selection_restore( document.getElementById( textblock_id ), item );
				jQuery('#' + textblock_id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight' );
			}

		}

	};

	// test browser capability
	if (window.getSelection && document.createRange) {

		/**
		 * Get current textselector selection
		 *
		 * @param object textblock_el The containing DOM element
		 * @return object Selection start and end data
		 */
		this.selection_get_current = function( textblock_el ) {

			// get selection data
			var range, preSelectionRange, start;
			range = window.getSelection().getRangeAt(0);
			preSelectionRange = range.cloneRange();
			preSelectionRange.selectNodeContents( textblock_el );
			preSelectionRange.setEnd(range.startContainer, range.startOffset);
			start = preSelectionRange.toString().length;
			return {
				text: range.toString(),
				start: start,
				end: start + range.toString().length
			}

		};

		/**
		 * Restore textselector selection
		 *
		 * @param object textblock_el The containing DOM element
		 * @param object saved_selection Selection start and end data
		 * @return void
		 */
		this.selection_restore = function( textblock_el, saved_selection ) {
			var charIndex = 0,
				range = document.createRange(),
				nodeStack = [textblock_el],
				node,
				foundStart = false,
				stop = false,
				sel;
			range.setStart(textblock_el, 0);
			range.collapse(true);
			while (!stop && (node = nodeStack.pop())) {
				if (node.nodeType == 3) {
					var nextCharIndex = charIndex + node.length;
					if (!foundStart && saved_selection.start >= charIndex && saved_selection.start <= nextCharIndex) {
						range.setStart(node, saved_selection.start - charIndex);
						foundStart = true;
					}
					if (foundStart && saved_selection.end >= charIndex && saved_selection.end <= nextCharIndex) {
						range.setEnd(node, saved_selection.end - charIndex);
						stop = true;
					}
					charIndex = nextCharIndex;
				} else {
					var i = node.childNodes.length;
					while (i--) {
						nodeStack.push(node.childNodes[i]);
					}
				}
			}
			sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange(range);
		};

	// test alternative browser capability
	} else if (document.selection && document.body.createTextRange) {

		/**
		 * Store textselector selection
		 *
		 * @param object textblock_el The DOM element
		 * @return object Selection start and end data
		 */
		this.selection_get_current = function( textblock_el ) {

			// get selection data
			var selectedTextRange, preSelectionTextRange, start;
			selectedTextRange = document.selection.createRange();
			preSelectionTextRange = document.body.createTextRange();
			preSelectionTextRange.moveToElementText(textblock_el);
			preSelectionTextRange.setEndPoint("EndToStart", selectedTextRange);
			start = preSelectionTextRange.text.length;
			return {
				text: selectedTextRange.text,
				start: start,
				end: start + selectedTextRange.text.length
			}

		};

		/**
		 * Restore textselector selection
		 *
		 * @param object textblock_el The DOM element
		 * @param object saved_selection Selection start and end data
		 * @return void
		 */
		this.selection_restore = function( textblock_el, saved_selection ) {
			var textRange;
			textRange = document.body.createTextRange();
			textRange.moveToElementText(textblock_el);
			textRange.collapse(true);
			textRange.moveEnd("character", saved_selection.end);
			textRange.moveStart("character", saved_selection.start);
			textRange.select();
		};

	};



	/**
	 * Send selection data to comment form
	 *
	 * @param bool with_text Whether to send text or not
	 * @return void
	 */
	this.selection_send_to_editor = function( with_text ) {

		// declare vars
		var selection;

		// get selection
		selection = me.selection_get();

		// always update text_selection hidden input
		jQuery( '#text_selection' ).val( selection.start + ',' + selection.end );

		// store the data
		me.selection_sent = selection;

		// bail if we're not sending text
		if ( !with_text ) { return; }

		// test for TinyMCE
		if ( cp_tinymce == '1' ) {
			// do we have TinyMCE or QuickTags active?
			if ( jQuery( '#wp-comment-wrap' ).hasClass( 'html-active' ) ) {
				me.selection_add_to_textarea( selection.text );
			} else {
				me.selection_add_to_tinymce( selection.text );
			}
		} else {
			me.selection_add_to_textarea( selection.text );
		}

	};

	/**
	 * Add text selection to comment textarea
	 *
	 * @param string text The plain text
	 * @param string mode The mode in which to add (prepend|replace)
	 * @return void
	 */
	this.selection_add_to_textarea = function( text, mode ) {

		// either the quicktags editor is active, or we have a simple textarea

		// if prepending
		if ( mode == 'prepend' ) {
			// get existing content
			content = jQuery( '#comment' ).val();
		} else {
			content = '';
		}

		setTimeout(function () {
			jQuery( '#comment' ).val( '<strong>[' + text + ']</strong>\n\n' + content );
		}, 200 );

	};

	/**
	 * Add selection to TinyMCE
	 *
	 * @param string text The plain text
	 * @param string mode The mode in which to add (prepend|replace)
	 * @return void
	 */
	this.selection_add_to_tinymce = function( text, mode ) {

		// if prepending
		if ( mode == 'prepend' ) {
			// get existing content
			content = tinymce.activeEditor.getContent();
		} else {
			content = '';
		}

		// prepend selection
		tinymce.activeEditor.setContent( '<p><strong>[' + text + ']</strong></p>' + content, {format : 'html'} );

		setTimeout(function () {

			// place cursor at the end and focus
			tinymce.activeEditor.selection.select(tinymce.activeEditor.getBody(), true);
			tinymce.activeEditor.selection.collapse(false);
			tinymce.activeEditor.focus();

		}, 200 );

	};



	/**
	 * Activate the jQuery highlighter
	 *
	 * @return void
	 */
	this.highlighter_activate = function() {

		// enable highlighter
		jQuery('.textblock').highlighter({
			'selector': '.holder',
			'minWords': 1,
			'complete': function( selected_text ) {
				//console.log( 'selected_text' );
			}
		});

	};

	/**
	 * Deactivate the jQuery highlighter
	 *
	 * @return void
	 */
	this.highlighter_deactivate = function() {
		jQuery('.textblock').highlighter('destroy');
	};



	/**
	 * Initialise the jQuery text highlighter.
	 *
	 * This method should only be called once. To reset the system, call:
	 * CommentPress.textselector.reset();
	 *
	 * @return void
	 */
	this.init = function() {

		// reset first
		me.reset();

		// build comment data
		me.selection_build_for_comments();

		// declare vars
		var input;

		// append input to comment form
		input = '<input type="hidden" name="text_selection" id="text_selection" value="" />';
		jQuery(input).appendTo( '#commentform' );

		// append popover to body element
		jQuery(me.popover).appendTo( 'body' );

		// activate highlighter
		me.highlighter_activate();

		/**
		 * Do not act on mousdowns on holder
		 *
		 * @return void
		 */
		jQuery('.holder').mousedown( function() {
			return false;
		});

		/**
		 * Act on clicks on the holder's "Comment" button
		 *
		 * @return void
		 */
		jQuery('.btn-left-comment').click( function() {

			// define vars
			var textblock_id, selection, container, wrap;

			// hide popover
			jQuery('.holder').hide();

			// get containing textblock
			textblock_id = me.container_get();

			// save current selection
			me.selection_save_for_textblock( textblock_id );

			// send to editor without text
			me.selection_send_to_editor( false );

			// scroll to comment form
			cp_scroll_comments( jQuery('#respond'), cp_scroll_speed );

			// wrap selection
			wrap = jQuery('#' + textblock_id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight' );

			return false;

		});

		/**
		 * Act on clicks on the holder's "Quote and Comment" button
		 *
		 * @return void
		 */
		jQuery('.btn-left-quote').click( function() {

			// define vars
			var textblock_id, selection, container, wrap;

			// hide popover
			jQuery('.holder').hide();

			// get containing textblock
			textblock_id = me.container_get();

			// save current selection
			me.selection_save_for_textblock( textblock_id );

			// send to editor with text
			me.selection_send_to_editor( true );

			// scroll to comment form
			cp_scroll_comments( jQuery('#respond'), cp_scroll_speed );

			// wrap selection
			wrap = jQuery('#' + textblock_id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight' );

			return false;

		});

		/**
		 * Act on clicks on the holder's right button
		 *
		 * @return void
		 */
		jQuery('.btn-right').click( function() {
			jQuery('.holder').hide();
			var dummy = '';
			me.container_set( dummy );
			return false;
		});

		/**
		 * Store last textblock clicked
		 *
		 * @return void
		 */
		jQuery( '#container' ).on( 'click', '.textblock', function() {

			// define vars
			var clicked_id, stored_id;

			// get existing ID, if there is one
			stored_id = me.container_get();

			// store the clicked on textblock ID
			clicked_id = jQuery(this).prop('id');

			/*
			console.log( 'textblock clicked' );
			console.log( stored_id );
			console.log( clicked_id );
			console.log( jQuery(this) );
			*/

			// is it different?
			if ( stored_id != clicked_id ) {

				// yes, store it
				me.container_set( clicked_id );

				// clear all highlights
				jQuery('.inline-highlight').each( function(i) {
					var content = jQuery(this).contents();
					jQuery(this).replaceWith( content );
				});

				/*
				// deactivate highlighter
				me.highlighter_deactivate();

				// reenable current selection
				me.selection_recall_for_textblock( clicked_id );

				// re-activate highlighter
				me.highlighter_activate();
				*/

			}

		});

		/**
		 * Rolling onto a comment
		 */
		jQuery( '#comments_sidebar' ).on( 'mouseenter', 'li.comment', function( event ) {

			// declare vars
			var item_id, comment_id;

			// we probably need to disable this while there's a selection that
			// has been sent to the editor

			// get the current ID
			item_id = jQuery(this).prop('id');
			//console.log( 'comment rolled into' );
			//console.log( item_id );

			// get comment ID
			comment_id = item_id.split('-')[2];
			//console.log( comment_id );

			me.selection_recall_for_comment( comment_id );

		});

		/**
		 * Rolling off a comment
		 */
		jQuery( '#comments_sidebar' ).on( 'mouseleave', 'li.comment', function( event ) {

			//console.log( 'comment rolled out of' );

			// we probably need to disable this while there's a selection that
			// has been sent to the editor

			// clear all highlights
			jQuery('.inline-highlight').each( function(i) {
				var content = jQuery(this).contents();
				jQuery(this).replaceWith( content );
			});

		});

	};

	/**
	 * Reset the jQuery text highlighter
	 *
	 * @return void
	 */
	this.reset = function() {

	};

} // end CommentPress textselector class



/**
 * Define what happens when the page is ready
 *
 * @return void
 */
jQuery(document).ready(function($) {

	/**
	 * Receive callback from the theme's Javascript when it's done loading
	 *
	 * @return void
	 */
	$(document).on( 'commentpress-document-ready', function( event ) {

		// initialise text selector
		CommentPress.textselector.init();

	});

	/**
	 * Hook into CommentPress reset
	 *
	 * @return void
	 */
	$(document).on( 'commentpress-reset-actions', function( event ) {

		// reset text selector
		CommentPress.textselector.reset();

	});

	/**
	 * Hook into CommentPress AJAX new comment added
	 *
	 * @param object event The event (unused)
	 * @param int comment_id The new comment ID
	 * @return void
	 */
	$(document).on( 'commentpress-ajax-comment-added', function( event, comment_id ) {

		// are we passing the full id?
		if ( comment_id.match( '#comment-' ) ) {

			// get numeric comment ID
			comment_id = parseInt( comment_id.split('#comment-')[1] );

		}

		//console.log( 'comment_id received: ' + comment_id );

		// reset text selector
		CommentPress.textselector.selection_save_for_comment( comment_id );

	});

}); // end document.ready()



