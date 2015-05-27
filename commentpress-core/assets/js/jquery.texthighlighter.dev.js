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



	// init container
	this.container = '';

	/**
	 * Setter for textselector container
	 *
	 * @param string textblock_id The ID of the textblock that was clicked
	 * @return void
	 */
	this.set_container = function( textblock_id ) {
		this.container = textblock_id;
	};

	/**
	 * Getter for textselector container
	 *
	 * @return string container The ID of the textblock that was clicked
	 */
	this.get_container = function() {
		return this.container;
	};



	/**
	 * Get selected text
	 *
	 * This is a modified version of the _getCaretInfo method in jQuery.selection:
	 * https://github.com/madapaja/jquery.selection
	 *
	 * @return object selection_obj The selection data
	 */
	this.get_selection = function() {

		// declare vars
		var selection_obj;

		// init return
		selection_obj = {
			text: '',
			start: 0,
			end: 0
		};

		// if there's a selection
		if ( window.getSelection ) {
			selection_obj.text = window.getSelection().toString();
		} else if ( document.selection && document.selection.type != "Control" ) {
			selection_obj.text = document.selection.createRange().text;
		}

		// --<
		return selection_obj;

	};



	/**
	 * Clear text selection
	 *
	 * @return void
	 */
	this.clear_selection = function() {

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
	 * Add text selection to comment textarea
	 *
	 * @param string text The plain text
	 * @param string mode The mode in which to add (prepend|replace)
	 * @return void
	 */
	this.add_text_selection = function( text, mode ) {

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
	this.add_tinymce_selection = function( text, mode ) {

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
	 * Init selection. For each .textblock element, there is an array of
	 * selection objects, eventually to be keyed by comment ID. For now, they
	 * simply have numeric keys, so we can test this out.
	 *
	 * This code is based on:
	 * http://stackoverflow.com/questions/13949059/persisting-the-changes-of-range-objects-after-selection-in-html/13950376#13950376
	 */
	this.selection = {};

	/**
	 * Save textselector selection
	 *
	 * @param string id The element ID
	 * @return void
	 */
	this.save_selection = function( el ) {

		// get selection data
		var sel = me.store_selection( document.getElementById( el ) );

		// create the array, keyed by textblock ID, if it doesn't exist
		if ( !(el in this.selection) ) { this.selection[el] = [] }

		// add selection data to the array
		this.selection[el].push( sel );

		//console.log( 'save this.selection' );
		//console.log( this.selection );

	};

	/**
	 * Recall all textselector selections
	 *
	 * @param string id The element ID
	 * @return void
	 */
	this.recall_selection = function( el ) {

		//console.log( 'recall this.selection' );
		//console.log( this.selection );

		// does the textblock ID key exist?
		if ( el in this.selection ) {

			// yes, restore each selection in turn
			for (var i = 0, item; item = this.selection[el][i++];) {
				//console.log( 'recalling this.selection[el][i]' );
				//console.log( item );
				me.restore_selection( document.getElementById( el ), item );
				jQuery('#' + el).wrapSelection({fitToWord: false}).addClass( 'inline-highlight' );
			}

		}

	};

	// test browser capability
	if (window.getSelection && document.createRange) {

		/**
		 * Store textselector selection
		 *
		 * @param object id The DOM element
		 * @return object Selection start and end data
		 */
		this.store_selection = function( containerEl ) {
			var range = window.getSelection().getRangeAt(0);
			var preSelectionRange = range.cloneRange();
			preSelectionRange.selectNodeContents(containerEl);
			preSelectionRange.setEnd(range.startContainer, range.startOffset);
			var start = preSelectionRange.toString().length;
			return {
				start: start,
				end: start + range.toString().length
			}
		};

		/**
		 * Restore textselector selection
		 *
		 * @param object id The DOM element
		 * @param object Selection start and end data
		 * @return void
		 */
		this.restore_selection = function(containerEl, savedSel) {
			var charIndex = 0, range = document.createRange();
			range.setStart(containerEl, 0);
			range.collapse(true);
			var nodeStack = [containerEl], node, foundStart = false, stop = false;
			while (!stop && (node = nodeStack.pop())) {
				if (node.nodeType == 3) {
					var nextCharIndex = charIndex + node.length;
					if (!foundStart && savedSel.start >= charIndex && savedSel.start <= nextCharIndex) {
						range.setStart(node, savedSel.start - charIndex);
						foundStart = true;
					}
					if (foundStart && savedSel.end >= charIndex && savedSel.end <= nextCharIndex) {
						range.setEnd(node, savedSel.end - charIndex);
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
			var sel = window.getSelection();
			sel.removeAllRanges();
			sel.addRange(range);
		};

	// test alternative browser capability
	} else if (document.selection && document.body.createTextRange) {

		/**
		 * Store textselector selection
		 *
		 * @param object id The DOM element
		 * @return object Selection start and end data
		 */
		this.store_selection = function(containerEl) {
			var selectedTextRange = document.selection.createRange();
			var preSelectionTextRange = document.body.createTextRange();
			preSelectionTextRange.moveToElementText(containerEl);
			preSelectionTextRange.setEndPoint("EndToStart", selectedTextRange);
			var start = preSelectionTextRange.text.length;
			return {
				start: start,
				end: start + selectedTextRange.text.length
			}
		};

		/**
		 * Restore textselector selection
		 *
		 * @param object id The DOM element
		 * @param object Selection start and end data
		 * @return void
		 */
		this.restore_selection = function(containerEl, savedSel) {
			var textRange = document.body.createTextRange();
			textRange.moveToElementText(containerEl);
			textRange.collapse(true);
			textRange.moveEnd("character", savedSel.end);
			textRange.moveStart("character", savedSel.start);
			textRange.select();
		};

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
	 * Deactivate the jQuery highlighter
	 *
	 * @return void
	 */
	this.highlighter_deactivate = function() {
		jQuery('.textblock').highlighter('destroy');
	};



	/**
	 * Initialise the jQuery text highlighter
	 *
	 * @return void
	 */
	this.init = function() {

		// activate highlighter
		me.highlighter_activate();

		/**
		 * Store last textblock clicked
		 *
		 * @return void
		 */
		jQuery('.textblock').click( function() {

			var clicked_id, stored_id;

			// get existing ID, if there is one
			stored_id = me.get_container();

			// store the clicked on textblock ID
			clicked_id = jQuery(this).prop('id');

			console.log( 'textblock clicked' );
			console.log( stored_id );
			console.log( clicked_id );
			console.log( jQuery(this) );

			// is it different?
			if ( stored_id != clicked_id ) {

				// yes, store it
				me.set_container( clicked_id );

				// clear all highlights
				jQuery('.inline-highlight').each( function(i) {
					var content = jQuery(this).contents();
					jQuery(this).replaceWith( content );
				});

				// deactivate highlighter
				me.highlighter_deactivate();

				// reenable current selection
				me.recall_selection( clicked_id );

				// re-activate highlighter
				me.highlighter_activate();

			}

		});

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
			var id, selection, container, wrap;

			// hide popover
			jQuery('.holder').hide();

			// get containing textblock
			id = me.get_container();

			// save current selection
			me.save_selection( id );

			// get selection
			selection = me.get_selection();

			// scroll to comment form
			cp_scroll_comments( jQuery('#respond'), cp_scroll_speed );

			// wrap selection
			wrap = jQuery('#' + id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight' );

			return false;

		});

		/**
		 * Act on clicks on the holder's "Quote and Comment" button
		 *
		 * @return void
		 */
		jQuery('.btn-left-quote').click( function() {

			// define vars
			var id, selection, container, wrap;

			// hide popover
			jQuery('.holder').hide();

			// get containing textblock
			id = me.get_container();

			// save current selection
			me.save_selection( id );

			// get selection
			selection = me.get_selection();

			// test for TinyMCE
			if ( cp_tinymce == '1' ) {
				// do we have TinyMCE or QuickTags active?
				if ( jQuery( '#wp-comment-wrap' ).hasClass( 'html-active' ) ) {
					me.add_text_selection( selection.text );
				} else {
					me.add_tinymce_selection( selection.text );
				}
			} else {
				me.add_text_selection( selection.text );
			}

			// scroll to comment form
			cp_scroll_comments( jQuery('#respond'), cp_scroll_speed );

			// wrap selection
			wrap = jQuery('#' + id).wrapSelection({fitToWord: false}).addClass( 'inline-highlight' );

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
			me.set_container( dummy );
			return false;
		});

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

		// re-enable selection
		//commentpress_enable_selection();

	});

}); // end document.ready()



