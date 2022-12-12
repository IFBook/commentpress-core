/**
 * Custom Javascript functionality for Comment Tagger.
 *
 * @since 4.0
 *
 * @package Comment_Tagger
 */

/**
 * Create global namespace.
 *
 * @since 4.0
 */
var CommentPress_Comments_Tagging = CommentPress_Comments_Tagging || {};

/**
 * Create settings class.
 *
 * Unused at present, but kept as a useful template.
 *
 * @since 4.0
 */
CommentPress_Comments_Tagging.settings = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();

	// Init group ID.
	//this.group_id = false;

	// Override if we have our localisation object.
	if ( 'undefined' !== typeof CommentPress_Comments_Tagging_Settings ) {
		//this.group_id = CommentPress_Comments_Tagging_Settings.data.group_id;
	}

	/**
	 * Setter for group ID.
	 *
	 * @since 4.0
	 *
	 * @param {Integer} val The group ID.
	 */
	this.set_group_id = function( val ) {
		this.group_id = val;
	};

	/**
	 * Getter for group ID.
	 *
	 * @since 4.0
	 *
	 * @return {Integer} The group ID.
	 */
	this.get_group_id = function() {
		return this.group_id;
	};

};

/**
 * Create comments class.
 *
 * @since 4.0
 */
CommentPress_Comments_Tagging.comments = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();

	/**
	 * Initialise "Read With".
	 *
	 * This method should only be called once.
	 *
	 * @since 4.0
	 */
	this.init = function() {

	};

	/**
	 * Do setup when jQuery reports that the DOM is ready.
	 *
	 * This method should only be called once.
	 *
	 * @since 4.0
	 */
	this.dom_ready = function() {

		// Init Select2.
		me.select2.init();
		me.select2.listeners();

	};

};

/**
 * Create comments Select2 class.
 *
 * @since 4.0
 */
CommentPress_Comments_Tagging.comments.select2 = new function() {

	// Store object refs.
	var me = this,
		$ = jQuery.noConflict();

	/**
	 * Select2 init.
	 *
	 * @since 4.0
	 */
	this.init = function() {

		// Select2 init.
		$('.comment_tagger_select2').select2({
			tags: true,
			tokenSeparators: [','],
			multiple: true
		});

	};

	/**
	 * Select2 listeners.
	 *
	 * @since 4.0
	 */
	this.listeners = function() {

		/**
		 * Hook into CommentPress AJAX new comment added.
		 *
		 * @since 4.0
		 *
		 * @param {Object} event The event. (unused)
		 * @param {Integer} comment_id The new comment ID.
		 */
		$(document).on( 'commentpress-ajax-comment-added', function( event, comment_id ) {

			// Reset Select2.
			me.reset();

		});

		/**
		 * Hook into CommentPress comment edit trigger.
		 *
		 * @since 4.0
		 *
		 * @param {Object} data The event.
		 * @param {Array} data The array of comment data.
		 */
		$( document ).on( 'commentpress-ajax-comment-callback', function( event, data ) {

			// Sanity check.
			if ( ! data.id ) {
				return;
			}

			// Configure Select2 with terms.
			if ( data.comment_tagger_tags ) {
				me.configure( data.comment_tagger_tags );
			} else {
				me.reset();
			}

			// Hide tags for this comment.
			$('#comment-' + data.id + ' .comment_tagger_tags').hide();

		});

		/**
		 * Hook into CommentPress comment edited trigger.
		 *
		 * @since 4.0
		 *
		 * @param {Object} data The event.
		 * @param {Array} data The array of comment data.
		 */
		$( document ).on( 'commentpress-ajax-comment-edited', function( event, data ) {

			// Sanity check.
			if ( ! data.id ) {
				return;
			}

			// Replace markup with new terms.
			if ( data.comment_tagger_markup ) {
				$('#comment-' + data.id + ' .comment_tagger_tags').replaceWith( data.comment_tagger_markup );
			}

		});

		/**
		 * Hook into CommentPress comment form moved trigger.
		 *
		 * @since 4.0
		 *
		 * @param {Object} data The event.
		 * @param {String} mode The comment form mode ('add' or 'edit').
		 * @param {Array} data The array of params.
		 */
		$( document ).on( 'commentpress-commentform-moved', function( event, mode, data ) {

			// Always reset Select2.
			me.reset();

		});

	};

	/**
	 * Reset Select2 to clean slate.
	 *
	 * @since 4.0
	 */
	this.reset = function() {

		// Reset Select2.
		$('.comment_tagger_select2').val( null ).trigger( "change" );

		// Show tags for all comments.
		$('.comment_tagger_tags').show();

	};

	/**
	 * Configure Select2 options.
	 *
	 * @since 4.0
	 *
	 * @param {Array} term_ids The array of term IDs to highlight.
	 */
	this.configure = function( terms ) {

		var term_ids = [],
			select = $('.comment_tagger_select2'),
			new_option;

		// Construct array of term IDs.
		$.each( terms, function( index, value ) {
			term_ids.push( value.id );
		});

		// Make sure all options exist.
		$.each( terms, function( index, value ) {
			if ( select.find( "option[value='" + value.id + "']" ).length ) {
				// Found, so skip.
			} else {
				new_option = new Option( value.name, value.id, true, true );
				select.append( new_option );
			}
		});

		// Now set Select2 options.
		select.val( term_ids ).trigger( "change" );

	};

};

// Do immediate actions.
CommentPress_Comments_Tagging.comments.init();

/**
 * Define what happens when the page is ready.
 *
 * @since 4.0
 */
jQuery(document).ready( function($) {

	// Document ready!
	CommentPress_Comments_Tagging.comments.dom_ready();

});
