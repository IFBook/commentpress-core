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
 * Create texthighlighter sub-namespace.
 *
 * @since 3.8
 */
CommentPress.texthighlighter = {};



/**
 * Create CommentPress Core texthighlighter settings class.
 *
 * @since 3.8
 */
CommentPress.texthighlighter.utilities = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();

	/**
	 * Init localisation variable.
	 *
	 * This variable holds all translatable text strings, keyed by a code.
	 * e.g. ['dialog_title': "My title", 'dialog_content': "Hello World" ]
	 *
	 * @since 3.8
	 */
	this.localisation = new Array;

	// Overwrite if we have our localisation object.
	if ( 'undefined' !== typeof CommentpressTextSelectorSettings ) {
		me.localisation = CommentpressTextSelectorSettings.localisation;
	}

	/**
	 * Setter for texthighlighter localisation.
	 *
	 * @since 3.8
	 *
	 * @param array val The new localisation array.
	 */
	this.localisation_set = function( val ) {
		me.localisation = val;
	};

	/**
	 * Getter for texthighlighter localisation.
	 *
	 * @since 3.8
	 *
	 * @param string key the code/key for the localisation string.
	 * @return string localisation The localisation string.
	 */
	this.localisation_get = function( key ) {
		if ( key in me.localisation ) {
			return me.localisation[key];
		}
		return '';
	};



	/**
	 * Init scroll target variable.
	 *
	 * This variable holds a reference to the currently active textblock scroll
	 * target for resetting after overriding the current value.
	 *
	 * @since 3.8
	 */
	this.saved_scroll_target = '';

	/**
	 * Setter for texthighlighter saved scroll target.
	 *
	 * @since 3.8
	 *
	 * @param string val The new saved_scroll_target.
	 */
	this.saved_scroll_target_set = function( val ) {
		me.saved_scroll_target = val;
	};

	/**
	 * Getter for texthighlighter saved scroll target.
	 *
	 * @since 3.8
	 *
	 * @return string saved_scroll_target The saved_scroll_target string.
	 */
	this.saved_scroll_target_get = function( key ) {
		return me.saved_scroll_target;
	};



	/**
	 * Initialise the jQuery text highlighter.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.init = function() {

		//console.log('CommentPress.texthighlighter.utilities.init');

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.dom_ready = function() {

		//console.log('CommentPress.texthighlighter.utilities.dom_ready');

	};



	/**
	 * Get current text selection.
	 *
	 * @since 3.8
	 *
	 * @return object selection_obj The selection data.
	 */
	this.selection_get = function( element_id ) {

		// Get current selection data.
		return me.selection_get_current( document.getElementById( element_id ) );

	};

	/**
	 * Clear text selection.
	 *
	 * @since 3.8
	 */
	this.selection_clear = function() {

		// Clear selection.
		if (window.getSelection) {
			if (window.getSelection().empty) {  // Chrome.
				window.getSelection().empty();
			} else if (window.getSelection().removeAllRanges) {  // Firefox.
				window.getSelection().removeAllRanges();
			}
		} else if (document.selection) {  // IE?
			document.selection.empty();
		}

	};



	// Test browser capability.
	if (window.getSelection && document.createRange) {

		/**
		 * Get current texthighlighter selection.
		 *
		 * @since 3.8
		 *
		 * @param object textblock_el The containing DOM element.
		 * @return object Selection start and end data.
		 */
		this.selection_get_current = function( textblock_el ) {

			// Get selection data.
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
			};

		};

		/**
		 * Restore texthighlighter selection.
		 *
		 * @since 3.8
		 *
		 * @param object textblock_el The containing DOM element.
		 * @param object saved_selection Selection start and end data.
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

	// Test alternative browser capability.
	} else if (document.selection && document.body.createTextRange) {

		/**
		 * Store texthighlighter selection.
		 *
		 * @since 3.8
		 *
		 * @param object textblock_el The DOM element.
		 * @return object Selection start and end data.
		 */
		this.selection_get_current = function( textblock_el ) {

			// Get selection data.
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
			};

		};

		/**
		 * Restore texthighlighter selection.
		 *
		 * @since 3.8
		 *
		 * @param object textblock_el The DOM element.
		 * @param object saved_selection Selection start and end data.
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
	 * Clear text highlights.
	 *
	 * @since 3.8
	 */
	this.highlights_clear_all = function() {

		// Clear all highlights.
		$('.inline-highlight').each( function(i) {
			var content = $(this).contents();
			$(this).replaceWith( content );
		});

	};

}; // End CommentPress.texthighlighter.utilities class.



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress Core texthighlighter textblocks class.
 *
 * @since 3.8
 */
CommentPress.texthighlighter.textblocks = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();

	// Test for our localisation object.
	if ( 'undefined' !== typeof CommentpressTextSelectorSettings ) {

		// Reference our localisation object vars.
		me.popover_textblock = CommentpressTextSelectorSettings.popover_textblock;

	}



	/**
	 * Initialise the jQuery textblocks text highlighter.
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

	};



	/**
	 * Init container variable.
	 *
	 * This variable holds a reference to the currently active textblock and is
	 * set by the click handler on the .textblock elements.
	 *
	 * @since 3.8
	 */
	this.container = '';

	/**
	 * Setter for texthighlighter container.
	 *
	 * @since 3.8
	 *
	 * @param string textblock_id The ID of the textblock that was clicked.
	 */
	this.container_set = function( textblock_id ) {
		me.container = textblock_id;
	};

	/**
	 * Getter for texthighlighter container.
	 *
	 * @since 3.8
	 *
	 * @return string container The ID of the textblock that was clicked.
	 */
	this.container_get = function() {
		return me.container;
	};



	/**
	 * Set up the jQuery text highlighter.
	 *
	 * This method should only be called once.
	 *
	 * @since 3.8
	 */
	this.setup = function() {

		// Build comment data.
		me.selection_build_for_comments();

		// Set up textblock popover.
		me.setup_popover();

		// Set up textblock content.
		me.setup_content();

		// Set up comment rollovers.
		me.setup_comment_rollovers();

		// Set up comment form.
		CommentPress.texthighlighter.commentform.setup();

		// Activate highlighter.
		me.highlighter_activate();

	};



	/**
	 * Set up the jQuery text highlighter textblock popover.
	 *
	 * @since 3.8
	 */
	this.setup_popover = function() {

		// Append popover to body element
		$(me.popover_textblock).appendTo( 'body' );

		/**
		 * Do not act on mousdowns on holder.
		 *
		 * I presume that this prevents event bubbling from the holder to the
		 * document body so that clicking elsewhere deactivates the popover.
		 *
		 * @since 3.8
		 */
		$('.popover-holder').mousedown( function() {
			return false;
		});

		/**
		 * Act on clicks on the holder's "Comment" button.
		 *
		 * @since 3.8
		 */
		$('.popover-holder-btn-left-comment').click( function() {

			// Define vars.
			var textblock_id, selection, wrap;

			// Hide popover.
			$('.popover-holder').hide();

			// Set selection active.
			CommentPress.texthighlighter.commentform.focus_activate();

			// Send to editor without text.
			me.selection_send_to_editor( false );

			// Scroll to comment form.
			CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

			// Get containing textblock.
			textblock_id = me.container_get();

			// Wrap selection.
			wrap = $('#' + textblock_id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight' );

			// Save current selection.
			//CommentPress.texthighlighter.selection_save_for_textblock( textblock_id );

			// Do not bubble.
			return false;

		});

		/**
		 * Act on clicks on the holder's "Quote and Comment" button.
		 *
		 * @since 3.8
		 */
		$('.popover-holder-btn-left-quote').click( function() {

			// Define vars.
			var textblock_id, selection, wrap;

			// Hide popover.
			$('.popover-holder').hide();

			// Set selection active.
			CommentPress.texthighlighter.commentform.focus_activate();

			// Send to editor with text.
			me.selection_send_to_editor( true );

			// Scroll to comment form.
			CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

			// Get containing textblock.
			textblock_id = me.container_get();

			// Wrap selection.
			wrap = $('#' + textblock_id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight' );

			// Save current selection.
			//me.selection_save_for_textblock( textblock_id );

			// Do not bubble.
			return false;

		});

		/**
		 * Act on clicks on the holder's right button.
		 *
		 * @since 3.8
		 */
		$('.popover-holder-btn-right').click( function() {

			// Hide popover.
			$('.popover-holder').hide();

			// Clear container.
			var dummy = '';
			me.container_set( dummy );

			// Do not bubble.
			return false;

		});

	};



	/**
	 * Set up the jQuery text highlighter textblock actions.
	 *
	 * @since 3.8
	 */
	this.setup_content = function() {

		// Declare vars.
		var touchstart = '', touchend = '';

		// Support touch device testing.
		if ( cp_is_touch == '1' && cp_touch_testing == '1' ) {
			touchstart = ' touchstart';
			touchend = ' touchend';
		}

		/**
		 * Act on mousedown on textblock.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'mousedown' + touchstart, '.textblock', function() {

			// If we have no comment form.
			if ( CommentPress.texthighlighter.commentform.has_commentform() == 'n' ) {

				// Disable highlighter.
				me.highlighter_disable();

				// Bail.
				return;

			}

			// Bail if commentform has focus.
			if ( CommentPress.texthighlighter.commentform.focus_is_active() ) { return; }

			// Define vars.
			var start_id;

			// Get the beginning textblock ID.
			start_id = $(this).prop('id');

			// Store.
			me.container_set( start_id );

			// Always enable highlighter.
			me.highlighter_enable();

		});

		/**
		 * Act on mouseup on textblock.
		 *
		 * @since 3.8
		 */
		$('#container').on( 'mouseup' + touchend, '.textblock', function() {

			// Bail if we have no comment form.
			if ( CommentPress.texthighlighter.commentform.has_commentform() == 'n' ) { return; }

			// Bail if commentform has focus.
			if ( CommentPress.texthighlighter.commentform.focus_is_active() ) { return; }

			// Define vars.
			var start_id, end_id;

			// Get the beginning textblock ID.
			start_id = me.container_get();

			// Get the ending textblock ID.
			end_id = $(this).prop('id');

			// Is it different?
			if ( start_id != end_id ) {

				// Overwrite with empty.
				me.container_set( '' );

				// Disable highlighter.
				me.highlighter_disable();

			}

		});

	};



	/**
	 * Set up the jQuery text highlighter comment actions.
	 *
	 * @since 3.8
	 */
	this.setup_comment_rollovers = function() {

		/**
		 * Rolling onto a comment.
		 *
		 * @since 3.8
		 */
		$('#comments_sidebar').on( 'mouseenter', 'li.comment.selection-exists', function( event ) {

			// Declare vars.
			var item_id, comment_id;

			// Kick out if either popover is shown.
			if ( $('.popover-holder').css('display') == 'block' ) { return; }
			if ( $('.comment-popover-holder').css('display') == 'block' ) { return; }

			// Kick out while there's a selection that has been sent to the editor.
			//if ( CommentPress.texthighlighter.commentform.focus_is_active() ) { return; }

			// Get the current ID.
			item_id = $(this).prop('id');

			// Get comment ID.
			comment_id = item_id.split('-')[2];

			// Show the selection for this comment.
			me.selection_recall_for_comment( comment_id );

		});

		/**
		 * Rolling off a comment.
		 *
		 * @since 3.8
		 */
		$('#comments_sidebar').on( 'mouseleave', 'li.comment.selection-exists', function( event ) {

			// Kick out if either popover is shown.
			if ( $('.popover-holder').css('display') == 'block' ) { return; }
			if ( $('.comment-popover-holder').css('display') == 'block' ) { return; }

			// Kick out while there's a selection that has been sent to the editor.
			//if ( CommentPress.texthighlighter.commentform.focus_is_active() ) { return; }

			// Clear all highlights.
			me.highlights_clear_for_comment();

		});

	};



	/**
	 * Send selection data to comment form.
	 *
	 * @since 3.8
	 *
	 * @param bool with_text Whether to send text or not.
	 */
	this.selection_send_to_editor = function( with_text ) {

		// Declare vars.
		var selection, container;

		// Unbind popover document click handler.
		$(document).unbind( 'click', me.highlighter_textblock_handler );

		// Get container.
		container = me.container_get();

		// Get selection.
		selection = CommentPress.texthighlighter.utilities.selection_get( container );

		// Normalise selection bounds.
		selection = me.selection_normalise_for_commentform( container, selection );

		// Update text_selection hidden input.
		CommentPress.texthighlighter.commentform.current_selection_set( selection );

		// If we're sending text.
		if ( with_text ) {

			// Test for TinyMCE.
			if ( cp_tinymce == '1' ) {
				// Do we have TinyMCE or QuickTags active?
				if ( $('#wp-comment-wrap').hasClass( 'html-active' ) ) {
					me.selection_add_to_textarea( selection.text, 'replace' );
				} else {
					me.selection_add_to_tinymce( selection.text, 'replace' );
				}
			} else {
				me.selection_add_to_textarea( selection.text, 'replace' );
			}

		} else {

			// Test for TinyMCE.
			if ( cp_tinymce == '1' ) {
				// Do we have TinyMCE or QuickTags active?
				if ( $('#wp-comment-wrap').hasClass( 'html-active' ) ) {
					setTimeout(function () {
						$('#comment').focus();
					}, 200 );
				} else {
					setTimeout(function () {
						tinymce.activeEditor.focus();
					}, 200 );
				}
			} else {
				setTimeout(function () {
					$('#comment').focus();
				}, 200 );
			}

		}

	};

	/**
	 * Normalise the selection for a comment before it is sent to the form.
	 *
	 * We need to do this because we have to store the selection range based on
	 * a single digit in the comment count span to preserve the validity of
	 * existing range data for comments.
	 *
	 * @see this.selection_normalise_for_comment()
	 *
	 * @since 3.8
	 *
	 * @param str textblock_id The ID of the textblock.
	 * @param object item The object containing the selection start and end values.
	 * @return object item The normalised selection start and end values.
	 */
	this.selection_normalise_for_commentform = function( textblock_id, item ) {

		var count;

		// Find the number of characters in the comment number.
		count = $('#' + textblock_id + ' .comment_count').html().length;

		// Adjust if the count is greater than 1.
		if ( count > 1 ) {
			return {
				text: item.text,
				start: item.start - ( count - 1 ),
				end: item.end - ( count - 1 )
			};
		}

		// --<
		return item;

	};

	/**
	 * Add text selection to comment textarea.
	 *
	 * @since 3.8
	 *
	 * @param string text The plain text.
	 * @param string mode The mode in which to add (prepend|replace).
	 */
	this.selection_add_to_textarea = function( text, mode ) {

		// If prepending.
		if ( mode == 'prepend' ) {
			// Get existing content.
			content = $('#comment').val();
		} else {
			content = '';
		}

		// Add text and focus.
		setTimeout(function () {
			$('#comment').val( '<strong>[' + text + ']</strong>\n\n' + content );
			$('#comment').focus();
		}, 200 );

	};

	/**
	 * Add selection to TinyMCE.
	 *
	 * @since 3.8
	 *
	 * @param string text The plain text.
	 * @param string mode The mode in which to add (prepend|replace).
	 */
	this.selection_add_to_tinymce = function( text, mode ) {

		// If prepending.
		if ( mode == 'prepend' ) {
			// Get existing content.
			content = tinymce.activeEditor.getContent();
		} else {
			content = '';
		}

		// Prepend selection.
		tinymce.activeEditor.setContent( '<p><strong>[' + text + ']</strong></p><p></p>' + content, {format : 'html'} );

		// Place cursor at the end and focus.
		setTimeout(function () {
			tinymce.activeEditor.selection.select(tinymce.activeEditor.getBody(), true);
			tinymce.activeEditor.selection.collapse(false);
			tinymce.activeEditor.focus();
		}, 200 );

	};



	/**
	 * Activate the jQuery highlighter.
	 *
	 * @since 3.8
	 */
	this.highlighter_activate = function() {

		// Enable highlighter.
		$('.textblock').highlighter({
			'selector': '.popover-holder',
			'minWords': 1,
			'complete': function( selected_text ) {
				// Attach a handler to the document body.
				$(document).bind( 'click', me.highlighter_textblock_handler );
			}
		});

	};

	/**
	 * Deactivate the jQuery highlighter.
	 *
	 * @since 3.8
	 */
	this.highlighter_deactivate = function() {

		// Destroy highlighter.
		$('.textblock').highlighter('destroy');

		// Unbind document click handler.
		$(document).unbind( 'click', me.highlighter_textblock_handler );

	};

	/**
	 * Make the jQuery highlighter modal in behaviour.
	 *
	 * @since 3.8
	 */
	this.highlighter_textblock_handler = function( event ) {

		// If the event target is not the popover
		if ( !$(event.target).closest( '.popover-holder' ).length ) {

			// Deactivate highlighter.
			me.highlighter_deactivate();

			// Re-activate highlighter.
			me.highlighter_activate();

		}

	};

	/**
	 * Enable the jQuery highlighter plugin.
	 *
	 * @since 3.8
	 */
	this.highlighter_enable = function() {

		// Enable highlighter.
		$('.textblock').highlighter('enable');

	};

	/**
	 * Disable the jQuery highlighter plugin.
	 *
	 * @since 3.8
	 */
	this.highlighter_disable = function() {

		// Disable highlighter.
		$('.textblock').highlighter('disable');

	};

	/**
	 * Clear text highlights in textblocks.
	 *
	 * @since 3.8
	 */
	this.highlights_clear_content = function() {

		// Clear textblock highlights.
		$('.textblock .inline-highlight').each( function(i) {
			var content = $(this).contents();
			$(this).replaceWith( content );
		});

	};

	/**
	 * Clear text highlights from comment rollovers.
	 *
	 * @since 3.8
	 */
	this.highlights_clear_for_comment = function() {

		// Clear textblock highlights.
		$('.textblock .inline-highlight-per-comment').each( function(i) {
			var content = $(this).contents();
			$(this).replaceWith( content );
		});

	};



	/**
	 * Init array that stores the selection data for comments that have them.
	 *
	 * There is key in the master array for each comment ID, whose value is a
	 * selection object from which we can read the start and end values.
	 *
	 * @since 3.8
	 */
	this.selections_by_comment = {};

	/**
	 * Build texthighlighter selections for a comments array.
	 *
	 * @since 3.8
	 *
	 * @param string comment_id The numerical comment ID.
	 */
	this.selection_build_for_comments = function() {

		/**
		 * Target only comments that have a special class.
		 *
		 * @since 3.8
		 */
		$('#comments_sidebar li.selection-exists').each( function(i) {

			// Declare vars.
			var item_id, comment_id, comment_key,
				class_list,
				sel_start, sel_end,
				selection_data;

			// Get the current item ID.
			item_id = $(this).prop('id');

			// Get comment ID.
			comment_id = item_id.split('-')[2];

			// Cast as string.
			comment_key = '#comment-' + comment_id;

			// Get classes.
			class_list = $(this).attr('class').split(/\s+/);

			// Find our data class names.
			$.each( class_list, function(index, item) {

				// Find our start.
				if ( item.match( 'sel_start-' ) ) {
					sel_start = parseInt( item.split('sel_start-')[1] );
				}

				// Find our end.
				if ( item.match( 'sel_end-' ) ) {
					sel_end = parseInt( item.split('sel_end-')[1] );
				}

				// Create selection data.
				selection_data = { start: sel_start, end: sel_end };

				// Add to array, keyed by comment ID.
				me.selections_by_comment[comment_key] = selection_data;

			});

		});

	};

	/**
	 * Save texthighlighter selection for a comment.
	 *
	 * @since 3.8
	 *
	 * @param string comment_id The numerical comment ID.
	 */
	this.selection_save_for_comment = function( comment_id ) {

		// Declare vars.
		var comment_key, selection_data;

		// Cast as string.
		comment_key = '#comment-' + comment_id;

		// Get selection data that was last sent to the editor.
		selection_data = CommentPress.texthighlighter.commentform.current_selection_get();

		// Add to array, keyed by comment ID.
		me.selections_by_comment[comment_key] = selection_data;

		// Clear sent selection data.
		CommentPress.texthighlighter.commentform.current_selection_clear();

	};

	/**
	 * Save texthighlighter selection for a comment.
	 *
	 * @since 3.8
	 *
	 * @param int comment_id The numerical comment ID.
	 */
	this.selection_recall_for_comment = function( comment_id ) {

		// Declare vars.
		var item, text_sig, textblock_id, comment_key;

		// Cast as string.
		comment_key = '#comment-' + comment_id;

		// Does the comment ID key exist?
		if ( comment_key in me.selections_by_comment ) {

			// Get selection item from array.
			item = me.selections_by_comment[comment_key];

			// Get text signature for this comment.
			text_sig = $.get_text_sig_by_comment_id( comment_key );

			// Get textblock.
			textblock_id = 'textblock-' + text_sig;

			// Normalise selection bounds.
			item = me.selection_normalise_for_comment( textblock_id, item );

			// Restore the selection.
			CommentPress.texthighlighter.utilities.selection_restore( document.getElementById( textblock_id ), item );
			$('#' + textblock_id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight-per-comment' );

		}

	};

	/**
	 * Normalise the selection for a comment.
	 *
	 * We need to do this because we have stored the selection range based on a
	 * single digit in the comment count span. But when the number of comments
	 * reaches 10, the selection is off by one. In the unlikely event that it
	 * should reach 100, it will be off by two.
	 *
	 * @since 3.8
	 *
	 * @param str textblock_id The ID of the textblock.
	 * @param object item The object containing the selection start and end values.
	 * @return object item The normalised selection start and end values.
	 */
	this.selection_normalise_for_comment = function( textblock_id, item ) {

		var count;

		// Find the number of characters in the comment number.
		count = $('#' + textblock_id + ' .comment_count').html().length;

		// Adjust if the count is greater than 1.
		if ( count > 1 ) {
			return {
				start: item.start + ( count - 1 ),
				end: item.end + ( count - 1 )
			};
		}

		// --<
		return item;

	};



	/**
	 * Init array that stores selections for each textblock element.
	 *
	 * There is key in the master array for each textblock ID, whose value is an
	 * array of selection objects.
	 *
	 * This code is based loosely on:
	 * http://stackoverflow.com/questions/13949059/persisting-the-changes-of-range-objects-after-selection-in-html/13950376#13950376
	 *
	 * @since 3.8
	 */
	this.selections_by_textblock = {};

	/**
	 * Save texthighlighter selection.
	 *
	 * @since 3.8
	 *
	 * @param string textblock_id The element ID.
	 */
	this.selection_save_for_textblock = function( textblock_id ) {

		// Get selection data.
		var selection_data = me.selection_get_current( document.getElementById( textblock_id ) );

		// Create the array, keyed by textblock ID, if it doesn't exist.
		if ( !(textblock_id in me.selections_by_textblock) ) { me.selections_by_textblock[textblock_id] = [] }

		// Add selection data to the array.
		me.selections_by_textblock[textblock_id].push( selection_data );

	};

	/**
	 * Recall all texthighlighter selections.
	 *
	 * @since 3.8
	 *
	 * @param string textblock_id The element ID.
	 */
	this.selection_recall_for_textblock = function( textblock_id ) {

		// Does the textblock ID key exist?
		if ( textblock_id in me.selections_by_textblock ) {

			// Yes, restore each selection in turn.
			for (var i = 0, item; item = me.selections_by_textblock[textblock_id][i++];) {
				CommentPress.texthighlighter.utilities.selection_restore( document.getElementById( textblock_id ), item );
				$('#' + textblock_id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight' );
			}

		}

	};

}; // End CommentPress.texthighlighter.textblocks class.



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress Core texthighlighter commentform class.
 *
 * @since 3.8
 */
CommentPress.texthighlighter.commentform = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();



	// Init "has commentform" flag.
	this.commentform_exists = 'n';

	/**
	 * Setter for "has commentform" flag.
	 *
	 * @since 3.8
	 */
	this.set_commentform_flag = function( val ) {
		me.commentform_exists = val;
	};

	/**
	 * Getter for "has commentform" flag.
	 *
	 * @since 3.8
	 */
	this.has_commentform = function() {
		return me.commentform_exists;
	};



	/**
	 * Initialise the jQuery text highlighter commentform.
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

		// Init listeners.
		//me.listeners();

	};



	/**
	 * Set up the jQuery text highlighter comment form.
	 *
	 * @since 3.8
	 */
	this.setup = function() {

		// Declare vars.
		var commentform, input;

		// Try and locate comment form.
		commentform = $('#commentform');

		// Bail if we have no comment form.
		if ( commentform.length == 0 ) { return; }

		// Set flag.
		me.set_commentform_flag( 'y' );

		// Append input to comment form.
		input = '<input type="hidden" name="text_selection" id="text_selection" value="" />';
		$(input).appendTo( '#commentform' );

	};



	/**
	 * Find out if there is content in the editor.
	 *
	 * @since 3.8
	 *
	 * @return bool Whether or not there is a stored selection object.
	 */
	this.comment_content_exists = function() {

		// Test for TinyMCE.
		if ( cp_tinymce == '1' ) {
			// Do we have TinyMCE or QuickTags active?
			if ( $('#wp-comment-wrap').hasClass( 'html-active' ) ) {
				content = $('#comment').val();
			} else {
				if ( 'undefined' !== typeof tinymce.activeEditor ) {
					content = tinymce.activeEditor.getContent();
				} else {
					content = $('#comment').val();
				}
			}
		} else {
			content = $('#comment').val();
		}

		// --<
		return ( content == '' ) ? false : true;

	};

	/**
	 * Clear the content in the editor.
	 *
	 * @since 3.8
	 */
	this.comment_content_clear = function() {

		// Test for TinyMCE.
		if ( cp_tinymce == '1' ) {
			// Do we have TinyMCE or QuickTags active?
			if ( $('#wp-comment-wrap').hasClass( 'html-active' ) ) {
				$('#comment').val( '' );
			} else {
				if ( 'undefined' !== typeof tinymce.activeEditor ) {
					tinymce.activeEditor.setContent( '', {format : 'html'} );
				} else {
					$('#comment').val( '' );
				}
			}
		} else {
			$('#comment').val( '' );
		}

	};



	// Init the selection that's held in the editor
	this.selection_in_editor = {};

	/**
	 * Setter for the selection that's held in the editor.
	 *
	 * @since 3.8
	 *
	 * @param object selection The selection object to store.
	 */
	this.current_selection_set = function( selection ) {

		// Store selection object.
		me.selection_in_editor = selection;

		// Update text_selection hidden input.
		$('#text_selection').val( selection.start + ',' + selection.end );

	};

	/**
	 * Getter for the selection that's held in the editor.
	 *
	 * @since 3.8
	 *
	 * @return object The stored selection object.
	 */
	this.current_selection_get = function() {
		return me.selection_in_editor;
	};

	/**
	 * Find out if there is a selection that's held in the editor.
	 *
	 * @since 3.8
	 *
	 * @return bool Whether or not there is a stored selection object.
	 */
	this.current_selection_exists = function() {
		return $.isEmptyObject( me.selection_in_editor ) ? false : true;
	};

	/**
	 * Clear the selection that's held in the editor.
	 *
	 * @since 3.8
	 */
	this.current_selection_clear = function() {

		// Clear selection object.
		me.selection_in_editor = {};

		// Clear text_selection hidden input.
		$('#text_selection').val( '' );

	};



	/**
	 * Init property that flags the selection must not be cleared.
	 *
	 * @since 3.8
	 */
	this.focus_active = false;

	/**
	 * Set selection as active - and therefore not to be cleared.
	 *
	 * @since 3.8
	 */
	this.focus_activate = function() {

		// Set selection active flag.
		me.focus_active = true;

		// Attach a handler to the document body.
		$(document).bind( 'click', me.focus_active_handler );

	};

	/**
	 * Get selection active state - can it be cleared?
	 *
	 * @since 3.8
	 *
	 * @return bool Whether or not the selection can be cleared.
	 */
	this.focus_is_active = function() {
		return me.focus_active;
	};

	/**
	 * Get selection active state - can it be cleared?
	 *
	 * @since 3.8
	 *
	 * @return bool Whether or not the selection can be cleared.
	 */
	this.focus_clear = function() {

		// Set flag.
		me.focus_active = false;

		// Unbind document click handler.
		$(document).unbind( 'click', me.focus_active_handler );

	};

	/**
	 * Selection active handler - test for clicks outside the comment form.
	 *
	 * @since 3.8
	 */
	this.focus_active_handler = function( event ) {

		// If the event target is not the comment form container.
		if ( !$(event.target).closest( '#respond' ).length ) {

			// If the event target is not a comment.
			if ( !$(event.target).closest( '.comment-content' ).length ) {

				// Do we have a current selection?
				if ( me.current_selection_exists() ) {

					// Do we have any content?
					if ( me.comment_content_exists() ) {

						// Show modal.
						me.modal();

						// Unbind document click handler.
						$(document).unbind( 'click', me.focus_active_handler );

					} else {

						// Do modal yes.
						me.modal_yes();

					}

				} else {

					// Do modal yes.
					me.modal_yes();

				}

			}

		}

	};



	// Init modal markup.
	this.modal_markup = {};

	/**
	 * Selection active handler - test for clicks outside the comment form.
	 *
	 * @since 3.8
	 */
	this.modal = function( event ) {

		// Define vars.
		var title_text, alert_text, yes_text, no_text, options;

		// Get title ("Are you sure?")
		title_text = CommentPress.texthighlighter.utilities.localisation_get( 'dialog_title' );

		// Get message ("You have not yet submitted your comment. Are you sure you want to discard it?")
		alert_text = CommentPress.texthighlighter.utilities.localisation_get( 'dialog_content' );

		// Create modal dialog markup.
		me.modal_markup = $('<div id="dialog" title="' + title_text + '"><p class="cp_alert_text">' + alert_text + '</p></div>');

		// Define "Discard" button text.
		yes_text = CommentPress.texthighlighter.utilities.localisation_get( 'dialog_yes' );

		// Define "Keep" button text.
		no_text = CommentPress.texthighlighter.utilities.localisation_get( 'dialog_no' );

		// Create options for modal dialog.
		options = {

			resizable: false,
			width: 400,
			height: 200,
			zIndex: 3999,
			modal: true,
			dialogClass: 'wp-dialog',
			buttons: [
				{
					text: yes_text,
					click : function() {

						// Let's do it.
						//$(this).dialog( "option", "disabled", true );

						// Do modal yes.
						me.modal_yes();

						// Don't close, but destroy instead.
						$(this).dialog( 'destroy' );
						$(this).remove();

					}
				},
				{
					text: no_text,
					click : function() {

						// Cancel
						$(this).dialog( 'close' );
						$(this).dialog( 'destroy' );
						$(this).remove();

					}
				}

			],
			close: function( event, ui ) {

				// Once dialog is closed.
				setTimeout(function () {

					// Do modal cancel.
					me.modal_cancel();

				}, 5 );

			}

		};

		// Show dialog.
		me.modal_markup.appendTo( 'body' );
		me.modal_markup.dialog( options );

	};



	/**
	 * Callback for clicking "Discard" in the dialog box.
	 *
	 * @since 3.8
	 */
	this.modal_yes = function( event ) {

		// Clear comment content.
		me.comment_content_clear();

		// Clear current selection.
		me.current_selection_clear();

		// Deactivate textblocks highlighter.
		CommentPress.texthighlighter.textblocks.highlighter_deactivate();

		// Re-activate textblocks highlighter.
		CommentPress.texthighlighter.textblocks.highlighter_activate();

		// Clear selection active state.
		me.focus_clear();

		// Clear all highlights.
		CommentPress.texthighlighter.utilities.highlights_clear_all();

	};



	/**
	 * Callback for clicking "Keep" in the dialog box.
	 *
	 * @since 3.8
	 */
	this.modal_cancel = function( event ) {

		// Activate selection active state.
		me.focus_activate();

		// Clear any existing selection.
		CommentPress.texthighlighter.utilities.selection_clear();

		// Disable selection.
		CommentPress.texthighlighter.textblocks.highlighter_disable();

	};



}; // End CommentPress.texthighlighter.commentform class



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress Core texthighlighter comments class.
 *
 * @since 3.8
 */
CommentPress.texthighlighter.comments = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();

	// Init popover content.
	this.popover_comment = '';

	// Overwrite if we have our localisation object.
	if ( 'undefined' !== typeof CommentpressTextSelectorSettings ) {
		me.popover_comment = CommentpressTextSelectorSettings.popover_comment;
	}



	/**
	 * Initialise the jQuery comments text highlighter.
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

	};



	/**
	 * Init container variable.
	 *
	 * This variable holds a reference to the currently active comment and is
	 * set by the click handler on the .comment-content elements.
	 *
	 * @since 3.8
	 */
	this.container = '';

	/**
	 * Setter for texthighlighter container.
	 *
	 * @since 3.8
	 *
	 * @param string comment_id The ID of the comment that was clicked.
	 */
	this.container_set = function( comment_id ) {
		me.container = comment_id;
	};

	/**
	 * Getter for texthighlighter container.
	 *
	 * @since 3.8
	 *
	 * @return string container The ID of the comment that was clicked.
	 */
	this.container_get = function() {
		return me.container;
	};



	/**
	 * Set up the jQuery text highlighter comment actions.
	 *
	 * @since 3.8
	 */
	this.setup = function() {

		// Set up comment popover.
		me.setup_popover();

		// Set up selection of comment content.
		me.setup_content();

		// Activate highlighter.
		me.highlighter_activate();

	};



	/**
	 * Set up the jQuery text highlighter comment popover.
	 *
	 * @since 3.8
	 */
	this.setup_popover = function() {

		// Append popover to body element.
		$(me.popover_comment).appendTo( 'body' );

		/**
		 * Do not act on mousdowns on holder.
		 *
		 * I presume that this prevents event bubbling from the holder to the
		 * document body so that clicking elsewhere deactivates the popover.
		 *
		 * @since 3.8
		 */
		$('.comment-popover-holder').mousedown( function() {
			return false;
		});

		/**
		 * Act on clicks on the holder's "Quote and Comment" button.
		 *
		 * @since 3.8
		 */
		$('.comment-popover-holder-btn-left-quote').click( function() {

			// Define vars.
			var comment_id, selection, wrap;

			// Hide popover.
			$('.comment-popover-holder').hide();

			// Send to editor with text.
			me.selection_send_to_editor( true );

			// Scroll to comment form.
			CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

			// Get containing comment.
			comment_id = me.container_get();

			// Wrap selection.
			wrap = $('#' + comment_id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight' );

			// Do not bubble.
			return false;

		});

		/**
		 * Act on clicks on the holder's right button.
		 *
		 * @since 3.8
		 */
		$('.popover-holder-btn-right').click( function() {

			// Hide popover.
			$('.comment-popover-holder').hide();

			// Clear container.
			var dummy = '';
			me.container_set( dummy );

			// Do not bubble.
			return false;

		});

	};



	/**
	 * Set up the jQuery text highlighter comment actions.
	 *
	 * @since 3.8
	 */
	this.setup_content = function() {

		/**
		 * Act on mousedown on comment.
		 *
		 * @since 3.8
		 */
		$('#comments_sidebar').on( 'mousedown', '.comment-content', function() {

			// If we have no comment form.
			if ( CommentPress.texthighlighter.commentform.has_commentform() == 'n' ) {

				// Disable highlighter.
				me.highlighter_disable();

				// Bail.
				return;

			}

			// Define vars.
			var start_id;

			// Get the beginning comment ID.
			start_id = $(this).parent().prop('id');

			// Store.
			me.container_set( start_id );

			// Always enable highlighter.
			me.highlighter_enable();

		});

		/**
		 * Act on mouseup on comment content.
		 *
		 * @since 3.8
		 */
		$('#comments_sidebar').on( 'mouseup', '.comment-content', function() {

			// Bail if we have no comment form.
			if ( CommentPress.texthighlighter.commentform.has_commentform() == 'n' ) { return; }

			// Define vars.
			var start_id, end_id;

			// Get the beginning comment ID.
			start_id = me.container_get();

			// Get the ending comment ID.
			end_id = $(this).parent().prop('id');

			// Is it different?
			if ( start_id != end_id ) {

				// Overwrite with empty.
				me.container_set( '' );

				// Disable highlighter.
				me.highlighter_disable();

			} else {

				// Has the quoted comment got the same para heading as the comment form?

				/*
				// Get quoted comment para heading.
				comment_para_heading = $(this).closest( '.paragraph_wrapper' ).prop('id');

				// Get comment form para heading.
				form_para_heading = $('#respond').closest( '.paragraph_wrapper' ).prop('id');

				// Is it different?
				if ( comment_para_heading != form_para_heading ) {

					// Overwrite with empty.
					me.container_set( '' );

					// Disable highlighter.
					me.highlighter_disable();

				}
				*/

			}

		});

		/**
		 * Act on clicks on comment forward-links.
		 *
		 * @since 3.8
		 */
		$('#comments_sidebar').on( 'click', '.comment-forwardlink', function( event ) {

			// Declare vars.
			var parent_comments_array,
				target_comment_id, current_comment, current_comment_id,
				back_text, link,
				this_para_wrapper, target_para_wrapper;

			// Override event.
			event.preventDefault();

			// Get target ID.
			target_comment_id = $(this).prop('href').split('#')[1];

			// Get array of parent comment divs.
			parent_comments_array = $(this).parents('li.comment').map( function() { return this; } );

			// Did we get one?
			if ( parent_comments_array.length > 0 ) {

				// Get the item.
				current_comment = $(parent_comments_array[0]);

				// Get current ID.
				current_comment_id = current_comment.prop('id').split('-')[2];

				// Get link text ("Back")
				back_text = CommentPress.texthighlighter.utilities.localisation_get( 'backlink_text' );

				// Construct link.
				link = '<a href="#comment-' + current_comment_id + '" class="comment-backlink">' + back_text + '</a>';

				// Append backlink to target if it doesn't exist.
				if ( !$('#' + target_comment_id + ' .comment-identifier .comment-backlink').length ) {
					$(link).prependTo('#' + target_comment_id + ' .comment-identifier');
				}

				// Get this comment's para wrapper.
				this_para_wrapper = $(this).closest( '.paragraph_wrapper' ).prop('id');

				// Get target comment's para wrapper.
				target_para_wrapper = $('#' + target_comment_id).closest( '.paragraph_wrapper' ).prop('id');

				// If different then open.
				if ( this_para_wrapper != target_para_wrapper ) {
					$('#' + target_para_wrapper).show();
				}

				// Scroll to comment.
				CommentPress.common.comments.scroll_comments( $('#' + target_comment_id), 300, 'flash' );

			}

		});

		/**
		 * Act on clicks on comment back-links.
		 *
		 * @since 3.8
		 */
		$('#comments_sidebar').on( 'click', '.comment-backlink', function( event ) {

			// Declare vars.
			var target_comment_id, this_para_wrapper, target_para_wrapper;

			// Override event.
			event.preventDefault();

			// Get target ID.
			target_comment_id = $(this).prop('href').split('#')[1];

			// Get this comment's para wrapper.
			this_para_wrapper = $(this).closest( '.paragraph_wrapper' ).prop('id');

			// Get target comment's para wrapper.
			target_para_wrapper = $('#' + target_comment_id).closest( '.paragraph_wrapper' ).prop('id');

			// If different then open.
			if ( this_para_wrapper != target_para_wrapper ) {
				$('#' + target_para_wrapper).show();
			}

			// Scroll to comment.
			CommentPress.common.comments.scroll_comments( $('#' + target_comment_id), 300, 'flash' );

			// Remove backlink.
			$(this).remove();

		});

	};



	/**
	 * Send selection data to comment form.
	 *
	 * @since 3.8
	 *
	 * @param bool with_text Whether to send text or not.
	 */
	this.selection_send_to_editor = function( with_text ) {

		// Declare vars.
		var selection;

		// Unbind popover document click handler.
		$(document).unbind( 'click', me.highlighter_comment_handler );

		// Get selection.
		selection = CommentPress.texthighlighter.utilities.selection_get( me.container_get() );

		// If we're sending text.
		if ( with_text ) {

			// Test for TinyMCE.
			if ( cp_tinymce == '1' ) {
				// Do we have TinyMCE or QuickTags active?
				if ( $('#wp-comment-wrap').hasClass( 'html-active' ) ) {
					me.selection_add_to_textarea( selection.text );
				} else {
					me.selection_add_to_tinymce( selection.text );
				}
			} else {
				me.selection_add_to_textarea( selection.text );
			}

		} else {

			// Reference comment somehow.
			//comment_id = me.container_get();

			// Test for TinyMCE.
			if ( cp_tinymce == '1' ) {
				// Do we have TinyMCE or QuickTags active?
				if ( $('#wp-comment-wrap').hasClass( 'html-active' ) ) {
					setTimeout(function () {
						$('#comment').focus();
					}, 200 );
				} else {
					setTimeout(function () {
						if ( 'undefined' !== typeof tinymce.activeEditor ) {
							tinymce.activeEditor.focus();
						} else {
							$('#comment').focus();
						}
					}, 200 );
				}
			} else {
				setTimeout(function () {
					$('#comment').focus();
				}, 200 );
			}

		}

	};

	/**
	 * Get a link to the target comment.
	 *
	 * @since 3.8
	 *
	 * @param string text The plain text.
	 * @param string link The HTML link.
	 */
	this.get_link = function( text ) {

		// Declare vars.
		var comment_id, link;

		// Get comment ID.
		comment_id = me.container_get();

		// Wrap in link.
		link = '<a href="#' + comment_id + '" class="comment-forwardlink">' + text + '</a>';

		// --<
		return link;

	};

	/**
	 * Add text selection to comment textarea.
	 *
	 * @since 3.8
	 *
	 * @param string text The plain text.
	 */
	this.selection_add_to_textarea = function( text ) {

		// Insert link.
		$('#comment').val( $('#comment').val() + me.get_link( text ) );

		// Add link and focus.
		setTimeout(function () {
			$('#comment').focus();
			me.highlights_clear_comment();
		}, 200 );

	};

	/**
	 * Add selection to TinyMCE.
	 *
	 * @since 3.8
	 *
	 * @param string text The plain text.
	 */
	this.selection_add_to_tinymce = function( text ) {

		// Add link at cursor.
		tinymce.activeEditor.execCommand( 'mceInsertContent', false, me.get_link( text ) );

		// Place cursor at the end and focus.
		setTimeout(function () {
			tinymce.activeEditor.focus();
			me.highlights_clear_comment();
		}, 200 );

	};



	/**
	 * Activate the jQuery highlighter.
	 *
	 * @since 3.8
	 */
	this.highlighter_activate = function() {

		// Enable highlighter.
		$('#comments_sidebar .comment-content').highlighter({
			'selector': '.comment-popover-holder',
			'minWords': 1,
			'complete': function( selected_text ) {
				// Attach a handler to the document body.
				$(document).bind( 'click', me.highlighter_comment_handler );
			}
		});

	};

	/**
	 * Deactivate the jQuery highlighter.
	 *
	 * @since 3.8
	 */
	this.highlighter_deactivate = function() {

		// Destroy highlighter.
		$('#comments_sidebar .comment-content').highlighter('destroy');

		// Unbind document click handler.
		$(document).unbind( 'click', me.highlighter_comment_handler );

	};

	/**
	 * Make the jQuery highlighter modal in behaviour.
	 *
	 * @since 3.8
	 */
	this.highlighter_comment_handler = function( event ) {

		// If the event target is not the popover.
		if ( !$(event.target).closest( '.comment-popover-holder' ).length ) {

			// Deactivate highlighter.
			me.highlighter_deactivate();

			// Re-activate highlighter.
			me.highlighter_activate();

		}

	};

	/**
	 * Enable the jQuery highlighter plugin.
	 *
	 * @since 3.8
	 */
	this.highlighter_enable = function() {

		// Enable highlighter.
		$('#comments_sidebar .comment-content').highlighter('enable');

	};

	/**
	 * Disable the jQuery highlighter plugin.
	 *
	 * @since 3.8
	 */
	this.highlighter_disable = function() {

		// Disable highlighter.
		$('#comments_sidebar .comment-content').highlighter('disable');

	};

	/**
	 * Clear comment highlights.
	 *
	 * @since 3.8
	 */
	this.highlights_clear_comment = function() {

		// Clear comment highlights.
		$('#comments_sidebar .comment-content .inline-highlight').each( function(i) {
			var content = $(this).contents();
			$(this).replaceWith( content );
		});

	};

}; // End CommentPress.texthighlighter.comments class.



/* -------------------------------------------------------------------------- */



// Initialise text selector.
CommentPress.texthighlighter.utilities.init();



/* -------------------------------------------------------------------------- */



/**
 * Define what happens when the page is ready.
 *
 * @since 3.8
 */
jQuery(document).ready(function($) {

	// Set up text selector.
	//CommentPress.texthighlighter.utilities.dom_ready();

	/**
	 * Receive callback from the theme's Javascript when it's done loading.
	 *
	 * @since 3.8
	 */
	$(document).on(
		'commentpress-document-ready',
		function( event ) {

			// Set up text selector.
			//CommentPress.texthighlighter.utilities.setup();

			// Set up textblocks.
			CommentPress.texthighlighter.textblocks.setup();

			// Set up comments.
			CommentPress.texthighlighter.comments.setup();

		} // End function.
	);

	/**
	 * Hook into CommentPress Core AJAX new comment added and animation finished.
	 *
	 * @since 3.8
	 *
	 * @param object event The event. (unused)
	 * @param int comment_id The new comment ID.
	 */
	$(document).on(
		'commentpress-ajax-comment-added-scrolled',
		function( event ) {

			// Clear highlights.
			CommentPress.texthighlighter.utilities.highlights_clear_all();

		} // End function.
	);

	/**
	 * Hook into CommentPress Core AJAX new comment added.
	 *
	 * @since 3.8
	 *
	 * @param object event The event. (unused)
	 * @param int comment_id The new comment ID.
	 */
	$(document).on(
		'commentpress-ajax-comment-added',
		function( event, comment_id ) {

			// Have we received the full id?
			if ( comment_id.match( '#comment-' ) ) {

				// Get numeric comment ID.
				comment_id = parseInt( comment_id.split('#comment-')[1] );

			}

			// Save selection for comment.
			CommentPress.texthighlighter.textblocks.selection_save_for_comment( comment_id );

			// Reset comment form.
			CommentPress.texthighlighter.commentform.current_selection_clear()

			// Clear comment form "modal focus".
			CommentPress.texthighlighter.commentform.focus_clear();

			// Reset comment quoting.
			CommentPress.texthighlighter.comments.highlighter_deactivate();
			CommentPress.texthighlighter.comments.highlighter_activate();

		} // End function.
	);

	/**
	 * Hook into CommentPress Core clicks on items whose events do not bubble.
	 *
	 * We need to receive callbacks from these clicks to clear the active selection.
	 *
	 * @since 3.8
	 *
	 * @param object event The event. (unused)
	 */
	$(document).on(
		'commentpress-textblock-pre-align ' +
		'commentpress-comment-block-permalink-pre-align ' +
		'commentpress-commenticonbox-pre-align ' +
		'commentpress-link-in-textblock-pre-align',
		function( event ) {

			// If comment form active and populated.
			if ( CommentPress.texthighlighter.commentform.focus_is_active() ) {

				//  is it populated?
				if ( CommentPress.texthighlighter.commentform.comment_content_exists() ) {

					// Save current target.
					CommentPress.texthighlighter.utilities.saved_scroll_target_set( CommentPress.settings.textblock.get_scroll_target() );

					// Set target to comment form.
					CommentPress.settings.textblock.set_scroll_target( 'none' );

				}

			}

		} // End function.
	);

	/**
	 * Hook into CommentPress Core clicks on items whose events do not bubble.
	 *
	 * We need to receive callbacks from these clicks to clear the active selection.
	 *
	 * @since 3.8
	 *
	 * @param object event The event. (unused)
	 */
	$(document).on(
		'commentpress-textblock-click ' +
		'commentpress-comment-block-permalink-clicked ' +
		'commentpress-commenticonbox-clicked ' +
		'commentpress-link-in-textblock-clicked',
		function( event ) {

			// If comment form active and populated.
			if ( CommentPress.texthighlighter.commentform.focus_is_active() ) {

				//  is it populated?
				if ( CommentPress.texthighlighter.commentform.comment_content_exists() ) {

					// Reset target to comment form.
					CommentPress.settings.textblock.set_scroll_target( CommentPress.texthighlighter.utilities.saved_scroll_target_get() );

					// Trigger click on document.
					$(document).click();

				}

			}

		} // End function.
	);

}); // End document.ready()



