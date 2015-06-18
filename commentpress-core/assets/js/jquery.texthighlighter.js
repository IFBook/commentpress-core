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
 * Create texthighlighter sub-namespace
 */
CommentPress.texthighlighter = {};



/**
 * Create CommentPress texthighlighter settings class
 */
CommentPress.texthighlighter.utilities = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();

	/**
	 * Init localisation variable.
	 *
	 * This variable holds all translatable text strings, keyed by a code.
	 * e.g. ['dialog_title': "My title", 'dialog_content': "Hello World" ]
	 */
	this.localisation = new Array;

	// overwrite if we have our localisation object
	if ( 'undefined' !== typeof CommentpressTextSelectorSettings ) {
		this.localisation = CommentpressTextSelectorSettings.localisation;
	}

	/**
	 * Setter for texthighlighter localisation.
	 *
	 * @param array val The new localisation array
	 * @return void
	 */
	this.localisation_set = function( val ) {
		this.localisation = val;
	};

	/**
	 * Getter for texthighlighter localisation.
	 *
	 * @param string key the code/key for the localisation string
	 * @return string localisation The localisation string
	 */
	this.localisation_get = function( key ) {
		if ( key in this.localisation ) {
			return this.localisation[key];
		}
		return '';
	};



	/**
	 * Init scroll target variable.
	 *
	 * This variable holds a reference to the currently active textblock scroll
	 * target for resetting after overriding the current value.
	 */
	this.saved_scroll_target = '';

	/**
	 * Setter for texthighlighter saved scroll target.
	 *
	 * @param string val The new saved_scroll_target
	 * @return void
	 */
	this.saved_scroll_target_set = function( val ) {
		this.saved_scroll_target = val;
	};

	/**
	 * Getter for texthighlighter saved scroll target.
	 *
	 * @return string saved_scroll_target The saved_scroll_target string
	 */
	this.saved_scroll_target_get = function( key ) {
		return this.saved_scroll_target;
	};



	/**
	 * Initialise the jQuery text highlighter.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.init = function() {

		//console.log('CommentPress.texthighlighter.utilities.init');

	};



	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.dom_ready = function() {

		//console.log('CommentPress.texthighlighter.utilities.dom_ready');

	};



	/**
	 * Get current text selection
	 *
	 * @return object selection_obj The selection data
	 */
	this.selection_get = function( element_id ) {

		// get current selection data
		return me.selection_get_current( document.getElementById( element_id ) );

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



	// test browser capability
	if (window.getSelection && document.createRange) {

		/**
		 * Get current texthighlighter selection
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

		}

		/**
		 * Restore texthighlighter selection
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
		}

	// test alternative browser capability
	} else if (document.selection && document.body.createTextRange) {

		/**
		 * Store texthighlighter selection
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

		}

		/**
		 * Restore texthighlighter selection
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
		}

	};



	/**
	 * Clear text highlights
	 *
	 * @return void
	 */
	this.highlights_clear_all = function() {

		// clear all highlights
		$('.inline-highlight').each( function(i) {
			var content = $(this).contents();
			$(this).replaceWith( content );
		});

	};

}; // end CommentPress.texthighlighter.utilities class



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress texthighlighter textblocks class
 */
CommentPress.texthighlighter.textblocks = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();

	// test for our localisation object
	if ( 'undefined' !== typeof CommentpressTextSelectorSettings ) {

		// reference our localisation object vars
		this.popover_textblock = CommentpressTextSelectorSettings.popover_textblock;

	}



	/**
	 * Initialise the jQuery textblocks text highlighter.
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

	};



	/**
	 * Init container variable.
	 *
	 * This variable holds a reference to the currently active textblock and is
	 * set by the click handler on the .textblock elements.
	 */
	this.container = '';

	/**
	 * Setter for texthighlighter container.
	 *
	 * @param string textblock_id The ID of the textblock that was clicked
	 * @return void
	 */
	this.container_set = function( textblock_id ) {
		this.container = textblock_id;
	};

	/**
	 * Getter for texthighlighter container.
	 *
	 * @return string container The ID of the textblock that was clicked
	 */
	this.container_get = function() {
		return this.container;
	};



	/**
	 * Set up the jQuery text highlighter.
	 *
	 * This method should only be called once.
	 *
	 * @return void
	 */
	this.setup = function() {

		// build comment data
		me.selection_build_for_comments();

		// set up textblock popover
		me.setup_popover();

		// set up textblock content
		me.setup_content();

		// set up comment rollovers
		me.setup_comment_rollovers();

		// set up comment form
		CommentPress.texthighlighter.commentform.setup();

		// activate highlighter
		me.highlighter_activate();

	};



	/**
	 * Set up the jQuery text highlighter textblock popover.
	 *
	 * @return void
	 */
	this.setup_popover = function() {

		// append popover to body element
		$(me.popover_textblock).appendTo( 'body' );

		/**
		 * Do not act on mousdowns on holder
		 *
		 * I presume that this prevents event bubbling from the holder to the
		 * document body so that clicking elsewhere deactivates the popover.
		 *
		 * @return void
		 */
		$('.popover-holder').mousedown( function() {
			return false;
		});

		/**
		 * Act on clicks on the holder's "Comment" button
		 *
		 * @return void
		 */
		$('.popover-holder-btn-left-comment').click( function() {

			// define vars
			var textblock_id, selection, wrap;

			// hide popover
			$('.popover-holder').hide();

			// set selection active
			CommentPress.texthighlighter.commentform.focus_activate();

			// send to editor without text
			me.selection_send_to_editor( false );

			// scroll to comment form
			CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

			// get containing textblock
			textblock_id = me.container_get();

			// wrap selection
			wrap = $('#' + textblock_id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight' );

			// save current selection
			//CommentPress.texthighlighter.selection_save_for_textblock( textblock_id );

			// do not bubble
			return false;

		});

		/**
		 * Act on clicks on the holder's "Quote and Comment" button
		 *
		 * @return void
		 */
		$('.popover-holder-btn-left-quote').click( function() {

			// define vars
			var textblock_id, selection, wrap;

			// hide popover
			$('.popover-holder').hide();

			// set selection active
			CommentPress.texthighlighter.commentform.focus_activate();

			// send to editor with text
			me.selection_send_to_editor( true );

			// scroll to comment form
			CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

			// get containing textblock
			textblock_id = me.container_get();

			// wrap selection
			wrap = $('#' + textblock_id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight' );

			// save current selection
			//me.selection_save_for_textblock( textblock_id );

			// do not bubble
			return false;

		});

		/**
		 * Act on clicks on the holder's right button
		 *
		 * @return void
		 */
		$('.popover-holder-btn-right').click( function() {

			// hide popover
			$('.popover-holder').hide();

			// clear container
			var dummy = '';
			me.container_set( dummy );

			// do not bubble
			return false;

		});

	};



	/**
	 * Set up the jQuery text highlighter textblock actions.
	 *
	 * @return void
	 */
	this.setup_content = function() {

		// declare vars
		var touchstart = '', touchend = '';

		// support touch device testing
		if ( cp_is_touch == '1' && cp_touch_testing == '1' ) {
			touchstart = ' touchstart';
			touchend = ' touchend';
		}

		/**
		 * Act on mousedown on textblock
		 *
		 * @return void
		 */
		$('#container').on( 'mousedown' + touchstart, '.textblock', function() {

			// if we have no comment form
			if ( CommentPress.texthighlighter.commentform.has_commentform() == 'n' ) {

				// disable highlighter
				me.highlighter_disable();

				// bail
				return;

			}

			// bail if commentform has focus
			if ( CommentPress.texthighlighter.commentform.focus_is_active() ) { return; }

			// define vars
			var start_id;

			// get the beginning textblock ID
			start_id = $(this).prop('id');

			// store
			me.container_set( start_id );

			// always enable highlighter
			me.highlighter_enable();

		});

		/**
		 * Act on mouseup on textblock
		 *
		 * @return void
		 */
		$('#container').on( 'mouseup' + touchend, '.textblock', function() {

			// bail if we have no comment form
			if ( CommentPress.texthighlighter.commentform.has_commentform() == 'n' ) { return; }

			// bail if commentform has focus
			if ( CommentPress.texthighlighter.commentform.focus_is_active() ) { return; }

			// define vars
			var start_id, end_id;

			// get the beginning textblock ID
			start_id = me.container_get();

			// get the ending textblock ID
			end_id = $(this).prop('id');

			// is it different?
			if ( start_id != end_id ) {

				// overwrite with empty
				me.container_set( '' );

				// disable highlighter
				me.highlighter_disable();

			}

		});

	};



	/**
	 * Set up the jQuery text highlighter comment actions.
	 *
	 * @return void
	 */
	this.setup_comment_rollovers = function() {

		/**
		 * Rolling onto a comment
		 */
		$('#comments_sidebar').on( 'mouseenter', 'li.comment.selection-exists', function( event ) {

			// declare vars
			var item_id, comment_id;

			// kick out if either popover is shown
			if ( $('.popover-holder').css('display') == 'block' ) { return; }
			if ( $('.comment-popover-holder').css('display') == 'block' ) { return; }

			// kick out while there's a selection that has been sent to the editor
			//if ( CommentPress.texthighlighter.commentform.focus_is_active() ) { return; }

			// get the current ID
			item_id = $(this).prop('id');

			// get comment ID
			comment_id = item_id.split('-')[2];

			// show the selection for this comment
			me.selection_recall_for_comment( comment_id );

		});

		/**
		 * Rolling off a comment
		 */
		$('#comments_sidebar').on( 'mouseleave', 'li.comment.selection-exists', function( event ) {

			// kick out if either popover is shown
			if ( $('.popover-holder').css('display') == 'block' ) { return; }
			if ( $('.comment-popover-holder').css('display') == 'block' ) { return; }

			// kick out while there's a selection that has been sent to the editor
			//if ( CommentPress.texthighlighter.commentform.focus_is_active() ) { return; }

			// clear all highlights
			me.highlights_clear_for_comment();

		});

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

		// unbind popover document click handler
		$(document).unbind( 'click', me.highlighter_textblock_handler );

		// get selection
		selection = CommentPress.texthighlighter.utilities.selection_get( me.container_get() );

		// update text_selection hidden input
		CommentPress.texthighlighter.commentform.current_selection_set( selection );

		// if we're sending text
		if ( with_text ) {

			// test for TinyMCE
			if ( cp_tinymce == '1' ) {
				// do we have TinyMCE or QuickTags active?
				if ( $('#wp-comment-wrap').hasClass( 'html-active' ) ) {
					me.selection_add_to_textarea( selection.text, 'replace' );
				} else {
					me.selection_add_to_tinymce( selection.text, 'replace' );
				}
			} else {
				me.selection_add_to_textarea( selection.text, 'replace' );
			}

		} else {

			// test for TinyMCE
			if ( cp_tinymce == '1' ) {
				// do we have TinyMCE or QuickTags active?
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
	 * Add text selection to comment textarea
	 *
	 * @param string text The plain text
	 * @param string mode The mode in which to add (prepend|replace)
	 * @return void
	 */
	this.selection_add_to_textarea = function( text, mode ) {

		// if prepending
		if ( mode == 'prepend' ) {
			// get existing content
			content = $('#comment').val();
		} else {
			content = '';
		}

		// add text and focus
		setTimeout(function () {
			$('#comment').val( '<strong>[' + text + ']</strong>\n\n' + content );
			$('#comment').focus();
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

		// place cursor at the end and focus
		setTimeout(function () {
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
		$('.textblock').highlighter({
			'selector': '.popover-holder',
			'minWords': 1,
			'complete': function( selected_text ) {
				// attach a handler to the document body
				$(document).bind( 'click', me.highlighter_textblock_handler );
			}
		});

	};

	/**
	 * Deactivate the jQuery highlighter
	 *
	 * @return void
	 */
	this.highlighter_deactivate = function() {

		// destroy highlighter
		$('.textblock').highlighter('destroy');

		// unbind document click handler
		$(document).unbind( 'click', me.highlighter_textblock_handler );

	};

	/**
	 * Make the jQuery highlighter modal in behaviour
	 *
	 * @return void
	 */
	this.highlighter_textblock_handler = function( event ) {

		// if the event target is not the popover
		if ( !$(event.target).closest( '.popover-holder' ).length ) {

			// deactivate highlighter
			me.highlighter_deactivate();

			// re-activate highlighter
			me.highlighter_activate();

		}

	};

	/**
	 * Enable the jQuery highlighter plugin
	 *
	 * @return void
	 */
	this.highlighter_enable = function() {

		// enable highlighter
		$('.textblock').highlighter('enable');

	};

	/**
	 * Disable the jQuery highlighter plugin
	 *
	 * @return void
	 */
	this.highlighter_disable = function() {

		// disable highlighter
		$('.textblock').highlighter('disable');

	};

	/**
	 * Clear text highlights in textblocks
	 *
	 * @return void
	 */
	this.highlights_clear_content = function() {

		// clear textblock highlights
		$('.textblock .inline-highlight').each( function(i) {
			var content = $(this).contents();
			$(this).replaceWith( content );
		});

	};

	/**
	 * Clear text highlights from comment rollovers
	 *
	 * @return void
	 */
	this.highlights_clear_for_comment = function() {

		// clear textblock highlights
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
	 */
	this.selections_by_comment = {};

	/**
	 * Build texthighlighter selections for a comments array
	 *
	 * @param string comment_id The numerical comment ID
	 * @return void
	 */
	this.selection_build_for_comments = function() {

		/**
		 * Target only comments that have a special class
		 *
		 * @return void
		 */
		$('#comments_sidebar li.selection-exists').each( function(i) {

			// declare vars
			var item_id, comment_id, comment_key,
				class_list,
				sel_start, sel_end,
				selection_data;

			// get the current item ID
			item_id = $(this).prop('id');

			// get comment ID
			comment_id = item_id.split('-')[2];

			// cast as string
			comment_key = '#comment-' + comment_id

			// get classes
			class_list = $(this).attr('class').split(/\s+/);

			// find our data class names
			$.each( class_list, function(index, item) {

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

	};

	/**
	 * Save texthighlighter selection for a comment
	 *
	 * @param string comment_id The numerical comment ID
	 * @return void
	 */
	this.selection_save_for_comment = function( comment_id ) {

		// declare vars
		var comment_key, selection_data;

		// cast as string
		comment_key = '#comment-' + comment_id;

		// get selection data that was last sent to the editor
		selection_data = CommentPress.texthighlighter.commentform.current_selection_get();

		// add to array, keyed by comment ID
		this.selections_by_comment[comment_key] = selection_data;

		// clear sent selection data
		CommentPress.texthighlighter.commentform.current_selection_clear();

	};

	/**
	 * Save texthighlighter selection for a comment
	 *
	 * @param int comment_id The numerical comment ID
	 * @return void
	 */
	this.selection_recall_for_comment = function( comment_id ) {

		// declare vars
		var item, text_sig, textblock_id, comment_key;

		// cast as string
		comment_key = '#comment-' + comment_id;

		// does the comment ID key exist?
		if ( comment_key in this.selections_by_comment ) {

			// get selection item from array
			item = this.selections_by_comment[comment_key]

			// get text signature for this comment
			text_sig = $.get_text_sig_by_comment_id( comment_key );

			// get textblock
			textblock_id = 'textblock-' + text_sig;

			// restore the selection
			CommentPress.texthighlighter.utilities.selection_restore( document.getElementById( textblock_id ), item );
			$('#' + textblock_id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight-per-comment' );

		}

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
	 * Save texthighlighter selection
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

	};

	/**
	 * Recall all texthighlighter selections
	 *
	 * @param string textblock_id The element ID
	 * @return void
	 */
	this.selection_recall_for_textblock = function( textblock_id ) {

		// does the textblock ID key exist?
		if ( textblock_id in this.selections_by_textblock ) {

			// yes, restore each selection in turn
			for (var i = 0, item; item = this.selections_by_textblock[textblock_id][i++];) {
				CommentPress.texthighlighter.utilities.selection_restore( document.getElementById( textblock_id ), item );
				$('#' + textblock_id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight' );
			}

		}

	};

}; // end CommentPress.texthighlighter.textblocks class



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress texthighlighter commentform class
 */
CommentPress.texthighlighter.commentform = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();



	// init "has commentform" flag
	this.commentform_exists = 'n';

	/**
	 * Setter for "has commentform" flag
	 */
	this.set_commentform_flag = function( val ) {
		this.commentform_exists = val;
	};

	/**
	 * Getter for "has commentform" flag
	 */
	this.has_commentform = function() {
		return this.commentform_exists;
	};



	/**
	 * Initialise the jQuery text highlighter commentform.
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

		// init listeners
		//me.listeners();

	};



	/**
	 * Set up the jQuery text highlighter comment form.
	 *
	 * @return void
	 */
	this.setup = function() {

		// declare vars
		var commentform, input;

		// try and locate comment form
		commentform = $('#commentform');

		// bail if we have no comment form
		if ( commentform.length == 0 ) { return; }

		// set flag
		me.set_commentform_flag( 'y' );

		// append input to comment form
		input = '<input type="hidden" name="text_selection" id="text_selection" value="" />';
		$(input).appendTo( '#commentform' );

	};



	/**
	 * Find out if there is content in the editor
	 *
	 * @return bool Whether or not there is a stored selection object
	 */
	this.comment_content_exists = function() {

		// test for TinyMCE
		if ( cp_tinymce == '1' ) {
			// do we have TinyMCE or QuickTags active?
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
	 * Clear the content in the editor
	 *
	 * @return void
	 */
	this.comment_content_clear = function() {

		// test for TinyMCE
		if ( cp_tinymce == '1' ) {
			// do we have TinyMCE or QuickTags active?
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



	// init the selection that's held in the editor
	this.selection_in_editor = {};

	/**
	 * Setter for the selection that's held in the editor
	 *
	 * @param object selection The selection object to store
	 * @return void
	 */
	this.current_selection_set = function( selection ) {

		// store selection object
		this.selection_in_editor = selection;

		// update text_selection hidden input
		$('#text_selection').val( selection.start + ',' + selection.end );

	};

	/**
	 * Getter for the selection that's held in the editor
	 *
	 * @return object The stored selection object
	 */
	this.current_selection_get = function() {
		return this.selection_in_editor;
	};

	/**
	 * Find out if there is a selection that's held in the editor
	 *
	 * @return bool Whether or not there is a stored selection object
	 */
	this.current_selection_exists = function() {
		return $.isEmptyObject( this.selection_in_editor ) ? false : true;
	};

	/**
	 * Clear the selection that's held in the editor
	 *
	 * @return void
	 */
	this.current_selection_clear = function() {

		// clear selection object
		this.selection_in_editor = {};

		// clear text_selection hidden input
		$('#text_selection').val( '' );

	};



	/**
	 * Init property that flags the selection must not be cleared
	 */
	this.focus_active = false;

	/**
	 * Set selection as active - and therefore not to be cleared
	 *
	 * @return void
	 */
	this.focus_activate = function() {

		// set selection active flag
		me.focus_active = true;

		// attach a handler to the document body
		$(document).bind( 'click', me.focus_active_handler );

	};

	/**
	 * Get selection active state - can it be cleared?
	 *
	 * @return bool Whether or not the selection can be cleared
	 */
	this.focus_is_active = function() {
		return this.focus_active;
	};

	/**
	 * Get selection active state - can it be cleared?
	 *
	 * @return bool Whether or not the selection can be cleared
	 */
	this.focus_clear = function() {

		// set flag
		this.focus_active = false;

		// unbind document click handler
		$(document).unbind( 'click', me.focus_active_handler );

	};

	/**
	 * Selection active handler - test for clicks outside the comment form
	 *
	 * @return void
	 */
	this.focus_active_handler = function( event ) {

		// if the event target is not the comment form container
		if ( !$(event.target).closest( '#respond' ).length ) {

			//console.log( 'the event target is not the comment form' );

			// if the event target is not a comment
			if ( !$(event.target).closest( '.comment-content' ).length ) {

				//console.log( 'the event target is not a comment' );

				// do we have a current selection?
				if ( me.current_selection_exists() ) {

					//console.log( 'we have a selection' );

					// do we have any content?
					if ( me.comment_content_exists() ) {

						//console.log( 'we have comment content' );

						// show modal
						me.modal();

						// unbind document click handler
						$(document).unbind( 'click', me.focus_active_handler );

					} else {

						//console.log( 'we DO NOT have comment content' );

						// do modal yes
						me.modal_yes();

					}

				} else {

					//console.log( 'we DO NOT have a selection' );

					// do modal yes
					me.modal_yes();

				}

			}

		}

	};



	// init modal markup
	this.modal_markup = {};

	/**
	 * Selection active handler - test for clicks outside the comment form
	 *
	 * @return void
	 */
	this.modal = function( event ) {

		// define vars
		var title_text, alert_text, yes_text, no_text, options;

		// get title ("Are you sure?")
		title_text = CommentPress.texthighlighter.utilities.localisation_get( 'dialog_title' );

		// get message ("You have not yet submitted your comment. Are you sure you want to discard it?")
		alert_text = CommentPress.texthighlighter.utilities.localisation_get( 'dialog_content' );

		// create modal dialog markup
		me.modal_markup = $('<div id="dialog" title="' + title_text + '"><p class="cp_alert_text">' + alert_text + '</p></div>');

		// define "Discard" button text
		yes_text = CommentPress.texthighlighter.utilities.localisation_get( 'dialog_yes' );

		// define "Keep" button text
		no_text = CommentPress.texthighlighter.utilities.localisation_get( 'dialog_no' );

		// create options for modal dialog
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

						// let's do it
						//$(this).dialog( "option", "disabled", true );

						// do modal yes
						me.modal_yes();

						// don't close, but destroy instead
						$(this).dialog( 'destroy' );
						$(this).remove();

					}
				},
				{
					text: no_text,
					click : function() {

						// cancel
						$(this).dialog( 'close' );
						$(this).dialog( 'destroy' );
						$(this).remove();

					}
				}

			],
			close: function( event, ui ) {

				// once dialog is closed
				setTimeout(function () {

					// do modal cancel
					me.modal_cancel();

				}, 5 );

			}

		};

		// show dialog
		me.modal_markup.appendTo( 'body' );
		me.modal_markup.dialog( options );

	};



	/**
	 * Callback for clicking "Discard" in the dialog box
	 *
	 * @return void
	 */
	this.modal_yes = function( event ) {

		// clear comment content
		me.comment_content_clear();

		// clear current selection
		me.current_selection_clear();

		// deactivate textblocks highlighter
		CommentPress.texthighlighter.textblocks.highlighter_deactivate();

		// re-activate textblocks highlighter
		CommentPress.texthighlighter.textblocks.highlighter_activate();

		// clear selection active state
		me.focus_clear();

		// clear all highlights
		CommentPress.texthighlighter.utilities.highlights_clear_all();

	};



	/**
	 * Callback for clicking "Keep" in the dialog box
	 *
	 * @return void
	 */
	this.modal_cancel = function( event ) {

		// activate selection active state
		me.focus_activate();

		// clear any existing selection
		CommentPress.texthighlighter.utilities.selection_clear();

		// disable selection
		CommentPress.texthighlighter.textblocks.highlighter_disable();

	};



}; // end CommentPress.texthighlighter.commentform class



/* -------------------------------------------------------------------------- */



/**
 * Create CommentPress texthighlighter comments class
 */
CommentPress.texthighlighter.comments = new function() {

	// store object refs
	var me = this,
		$ = jQuery.noConflict();

	// init popver content
	this.popover_comment = '';

	// overwrite if we have our localisation object
	if ( 'undefined' !== typeof CommentpressTextSelectorSettings ) {
		this.popover_comment = CommentpressTextSelectorSettings.popover_comment;
	}



	/**
	 * Initialise the jQuery comments text highlighter.
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

	};



	/**
	 * Init container variable.
	 *
	 * This variable holds a reference to the currently active comment and is
	 * set by the click handler on the .comment-content elements.
	 */
	this.container = '';

	/**
	 * Setter for texthighlighter container.
	 *
	 * @param string comment_id The ID of the comment that was clicked
	 * @return void
	 */
	this.container_set = function( comment_id ) {
		this.container = comment_id;
	};

	/**
	 * Getter for texthighlighter container.
	 *
	 * @return string container The ID of the comment that was clicked
	 */
	this.container_get = function() {
		return this.container;
	};



	/**
	 * Set up the jQuery text highlighter comment actions.
	 *
	 * @return void
	 */
	this.setup = function() {

		// set up comment popover
		me.setup_popover();

		// set up selection of comment content
		me.setup_content();

		// activate highlighter
		me.highlighter_activate();

	};



	/**
	 * Set up the jQuery text highlighter comment popover.
	 *
	 * @return void
	 */
	this.setup_popover = function() {

		// append popover to body element
		$(me.popover_comment).appendTo( 'body' );

		/**
		 * Do not act on mousdowns on holder
		 *
		 * I presume that this prevents event bubbling from the holder to the
		 * document body so that clicking elsewhere deactivates the popover.
		 *
		 * @return void
		 */
		$('.comment-popover-holder').mousedown( function() {
			return false;
		});

		/**
		 * Act on clicks on the holder's "Quote and Comment" button
		 *
		 * @return void
		 */
		$('.comment-popover-holder-btn-left-quote').click( function() {

			// define vars
			var comment_id, selection, wrap;

			// hide popover
			$('.comment-popover-holder').hide();

			// send to editor with text
			me.selection_send_to_editor( true );

			// scroll to comment form
			CommentPress.common.comments.scroll_comments( $('#respond'), cp_scroll_speed );

			// get containing comment
			comment_id = me.container_get();

			// wrap selection
			wrap = $('#' + comment_id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight' );

			// do not bubble
			return false;

		});

		/**
		 * Act on clicks on the holder's right button
		 *
		 * @return void
		 */
		$('.popover-holder-btn-right').click( function() {

			// hide popover
			$('.comment-popover-holder').hide();

			// clear container
			var dummy = '';
			me.container_set( dummy );

			// do not bubble
			return false;

		});

	};



	/**
	 * Set up the jQuery text highlighter comment actions.
	 *
	 * @return void
	 */
	this.setup_content = function() {

		/**
		 * Act on mousedown on comment
		 *
		 * @return void
		 */
		$('#comments_sidebar').on( 'mousedown', '.comment-content', function() {

			// if we have no comment form
			if ( CommentPress.texthighlighter.commentform.has_commentform() == 'n' ) {

				// disable highlighter
				me.highlighter_disable();

				// bail
				return;

			}

			// define vars
			var start_id;

			// get the beginning comment ID
			start_id = $(this).parent().prop('id');

			// store
			me.container_set( start_id );

			// always enable highlighter
			me.highlighter_enable();

		});

		/**
		 * Act on mouseup on comment content
		 *
		 * @return void
		 */
		$('#comments_sidebar').on( 'mouseup', '.comment-content', function() {

			// bail if we have no comment form
			if ( CommentPress.texthighlighter.commentform.has_commentform() == 'n' ) { return; }

			// define vars
			var start_id, end_id;

			// get the beginning comment ID
			start_id = me.container_get();

			// get the ending comment ID
			end_id = $(this).parent().prop('id');

			// is it different?
			if ( start_id != end_id ) {

				// overwrite with empty
				me.container_set( '' );

				// disable highlighter
				me.highlighter_disable();

			} else {

				// has the quoted comment got the same para heading as the comment form?

				/*
				// get quoted comment para heading
				comment_para_heading = $(this).closest( '.paragraph_wrapper' ).prop('id');

				// get comment form para heading
				form_para_heading = $('#respond').closest( '.paragraph_wrapper' ).prop('id');

				// is it different?
				if ( comment_para_heading != form_para_heading ) {

					// overwrite with empty
					me.container_set( '' );

					// disable highlighter
					me.highlighter_disable();

				}
				*/

			}

		});

		/**
		 * Act on clicks on comment forward-links
		 *
		 * @return void
		 */
		$('#comments_sidebar').on( 'click', '.comment-forwardlink', function( event ) {

			// declare vars
			var parent_comments_array,
				target_comment_id, current_comment, current_comment_id,
				back_text, link,
				this_para_wrapper, target_para_wrapper;

			// override event
			event.preventDefault();

			// get target ID
			target_comment_id = $(this).prop('href').split('#')[1];

			// get array of parent comment divs
			parent_comments_array = $(this).parents('li.comment').map( function() { return this; } );

			// did we get one?
			if ( parent_comments_array.length > 0 ) {

				// get the item
				current_comment = $(parent_comments_array[0]);
				//console.log( current_comment );

				// get current ID
				current_comment_id = current_comment.prop('id').split('-')[2];

				// get link text ("Back")
				back_text = CommentPress.texthighlighter.utilities.localisation_get( 'backlink_text' );

				// construct link
				link = '<a href="#comment-' + current_comment_id + '" class="comment-backlink">' + back_text + '</a>';

				// append backlink to target if it doesn't exist
				if ( !$('#' + target_comment_id + ' .comment-identifier .comment-backlink').length ) {
					$(link).prependTo('#' + target_comment_id + ' .comment-identifier');
				}

				// get this comment's para wrapper
				this_para_wrapper = $(this).closest( '.paragraph_wrapper' ).prop('id');

				// get target comment's para wrapper
				target_para_wrapper = $('#' + target_comment_id).closest( '.paragraph_wrapper' ).prop('id');

				// if different then open
				if ( this_para_wrapper != target_para_wrapper ) {
					$('#' + target_para_wrapper).show();
				}

				// scroll to comment
				CommentPress.common.comments.scroll_comments( $('#' + target_comment_id), 300, 'flash' );

			}

		});

		/**
		 * Act on clicks on comment forward-links
		 *
		 * @return void
		 */
		$('#comments_sidebar').on( 'click', '.comment-backlink', function( event ) {

			// declare vars
			var target_comment_id, this_para_wrapper, target_para_wrapper;

			// override event
			event.preventDefault();

			// get target ID
			target_comment_id = $(this).prop('href').split('#')[1];

			// get this comment's para wrapper
			this_para_wrapper = $(this).closest( '.paragraph_wrapper' ).prop('id');

			// get target comment's para wrapper
			target_para_wrapper = $('#' + target_comment_id).closest( '.paragraph_wrapper' ).prop('id');

			// if different then open
			if ( this_para_wrapper != target_para_wrapper ) {
				$('#' + target_para_wrapper).show();
			}

			// scroll to comment
			CommentPress.common.comments.scroll_comments( $('#' + target_comment_id), 300, 'flash' );

			// remove backlink
			$(this).remove();

		});

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

		// unbind popover document click handler
		$(document).unbind( 'click', me.highlighter_comment_handler );

		// get selection
		selection = CommentPress.texthighlighter.utilities.selection_get( me.container_get() );

		// if we're sending text
		if ( with_text ) {

			// test for TinyMCE
			if ( cp_tinymce == '1' ) {
				// do we have TinyMCE or QuickTags active?
				if ( $('#wp-comment-wrap').hasClass( 'html-active' ) ) {
					me.selection_add_to_textarea( selection.text );
				} else {
					me.selection_add_to_tinymce( selection.text );
				}
			} else {
				me.selection_add_to_textarea( selection.text );
			}

		} else {

			// reference comment somehow
			//comment_id = me.container_get();

			// test for TinyMCE
			if ( cp_tinymce == '1' ) {
				// do we have TinyMCE or QuickTags active?
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
	 * Get a link to the target comment
	 *
	 * @param string text The plain text
	 * @param string link The HTML link
	 */
	this.get_link = function( text ) {

		// declare vars
		var comment_id, link;

		// get comment ID
		comment_id = me.container_get();

		// wrap in link
		link = '<a href="#' + comment_id + '" class="comment-forwardlink">' + text + '</a>';

		// --<
		return link;

	};

	/**
	 * Add text selection to comment textarea
	 *
	 * @param string text The plain text
	 * @return void
	 */
	this.selection_add_to_textarea = function( text ) {

		// insert link
		$('#comment').val( $('#comment').val() + me.get_link( text ) );

		// add link and focus
		setTimeout(function () {
			$('#comment').focus();
			me.highlights_clear_comment();
		}, 200 );

	};

	/**
	 * Add selection to TinyMCE
	 *
	 * @param string text The plain text
	 * @return void
	 */
	this.selection_add_to_tinymce = function( text ) {

		// add link at cursor
		tinymce.activeEditor.execCommand( 'mceInsertContent', false, me.get_link( text ) );

		// place cursor at the end and focus
		setTimeout(function () {
			tinymce.activeEditor.focus();
			me.highlights_clear_comment();
		}, 200 );

	};



	/**
	 * Activate the jQuery highlighter
	 *
	 * @return void
	 */
	this.highlighter_activate = function() {

		// enable highlighter
		$('.comment-content').highlighter({
			'selector': '.comment-popover-holder',
			'minWords': 1,
			'complete': function( selected_text ) {
				// attach a handler to the document body
				$(document).bind( 'click', me.highlighter_comment_handler );
			}
		});

	};

	/**
	 * Deactivate the jQuery highlighter
	 *
	 * @return void
	 */
	this.highlighter_deactivate = function() {

		// destroy highlighter
		$('.comment-content').highlighter('destroy');

		// unbind document click handler
		$(document).unbind( 'click', me.highlighter_comment_handler );

	};

	/**
	 * Make the jQuery highlighter modal in behaviour
	 *
	 * @return void
	 */
	this.highlighter_comment_handler = function( event ) {

		// if the event target is not the popover
		if ( !$(event.target).closest( '.comment-popover-holder' ).length ) {

			// deactivate highlighter
			me.highlighter_deactivate();

			// re-activate highlighter
			me.highlighter_activate();

		}

	};

	/**
	 * Enable the jQuery highlighter plugin
	 *
	 * @return void
	 */
	this.highlighter_enable = function() {

		// enable highlighter
		$('.comment-content').highlighter('enable');

	};

	/**
	 * Disable the jQuery highlighter plugin
	 *
	 * @return void
	 */
	this.highlighter_disable = function() {

		// disable highlighter
		$('.comment-content').highlighter('disable');

	};

	/**
	 * Clear comment highlights
	 *
	 * @return void
	 */
	this.highlights_clear_comment = function() {

		// clear comment highlights
		$('.comment-content .inline-highlight').each( function(i) {
			var content = $(this).contents();
			$(this).replaceWith( content );
		});

	};

}; // end CommentPress.texthighlighter.comments class



/* -------------------------------------------------------------------------- */



// initialise text selector
CommentPress.texthighlighter.utilities.init();



/* -------------------------------------------------------------------------- */



/**
 * Define what happens when the page is ready
 *
 * @return void
 */
jQuery(document).ready(function($) {

	// set up text selector
	//CommentPress.texthighlighter.utilities.dom_ready();

	/**
	 * Receive callback from the theme's Javascript when it's done loading
	 *
	 * @return void
	 */
	$(document).on(
		'commentpress-document-ready',
		function( event ) {

			// set up text selector
			//CommentPress.texthighlighter.utilities.setup();

			// set up textblocks
			CommentPress.texthighlighter.textblocks.setup();

			// set up comments
			CommentPress.texthighlighter.comments.setup();

		} // end function
	);

	/**
	 * Hook into CommentPress AJAX new comment added and animation finished
	 *
	 * @param object event The event (unused)
	 * @param int comment_id The new comment ID
	 * @return void
	 */
	$(document).on(
		'commentpress-ajax-comment-added-scrolled',
		function( event ) {

			// clear highlights
			CommentPress.texthighlighter.utilities.highlights_clear_all();

		} // end function
	);

	/**
	 * Hook into CommentPress AJAX new comment added
	 *
	 * @param object event The event (unused)
	 * @param int comment_id The new comment ID
	 * @return void
	 */
	$(document).on(
		'commentpress-ajax-comment-added',
		function( event, comment_id ) {

			// have we received the full id?
			if ( comment_id.match( '#comment-' ) ) {

				// get numeric comment ID
				comment_id = parseInt( comment_id.split('#comment-')[1] );

			}

			// save selection for comment
			CommentPress.texthighlighter.textblocks.selection_save_for_comment( comment_id );

			// reset comment form
			CommentPress.texthighlighter.commentform.current_selection_clear()

			// clear comment form "modal focus"
			CommentPress.texthighlighter.commentform.focus_clear();

			// reset comment quoting
			CommentPress.texthighlighter.comments.highlighter_deactivate();
			CommentPress.texthighlighter.comments.highlighter_activate();

		} // end function
	);

	/**
	 * Hook into CommentPress clicks on items whose events do not bubble.
	 *
	 * We need to receive callbacks from these clicks to clear the active selection
	 *
	 * @param object event The event (unused)
	 * @return void
	 */
	$(document).on(
		'commentpress-textblock-pre-align ' +
		'commentpress-comment-block-permalink-pre-align ' +
		'commentpress-commenticonbox-pre-align ' +
		'commentpress-link-in-textblock-pre-align',
		function( event ) {

			// if comment form active and populated
			if ( CommentPress.texthighlighter.commentform.focus_is_active() ) {

				//  is it populated?
				if ( CommentPress.texthighlighter.commentform.comment_content_exists() ) {

					// save current target
					CommentPress.texthighlighter.utilities.saved_scroll_target_set( CommentPress.settings.textblock.get_scroll_target() );

					// set target to comment form
					CommentPress.settings.textblock.set_scroll_target( 'none' );

				}

			}

		} // end function
	);

	/**
	 * Hook into CommentPress clicks on items whose events do not bubble.
	 *
	 * We need to receive callbacks from these clicks to clear the active selection
	 *
	 * @param object event The event (unused)
	 * @return void
	 */
	$(document).on(
		'commentpress-textblock-click ' +
		'commentpress-comment-block-permalink-clicked ' +
		'commentpress-commenticonbox-clicked ' +
		'commentpress-link-in-textblock-clicked',
		function( event ) {

			// if comment form active and populated
			if ( CommentPress.texthighlighter.commentform.focus_is_active() ) {

				//  is it populated?
				if ( CommentPress.texthighlighter.commentform.comment_content_exists() ) {

					// reset target to comment form
					CommentPress.settings.textblock.set_scroll_target( CommentPress.texthighlighter.utilities.saved_scroll_target_get() );

					// trigger click on document
					$(document).click();

				}

			}

		} // end function
	);

}); // end document.ready()



