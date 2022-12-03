/**
 * CommentPress Multisite BuddyPress Groupblog Names "Network Settings" Javascript.
 *
 * @since 4.0
 *
 * @package CommentPress_Core
 */

/**
 * Pass the jQuery shortcut in.
 *
 * @since 4.0
 *
 * @param {Object} $ The jQuery object.
 */
( function( $ ) {

	/**
	 * Create "BuddyPress Groupblog Names" class.
	 *
	 * @since 4.0
	 */
	function CPMS_Network_BuddyPress_Groupblog_Names() {

		// Prevent reference collisions.
		var me = this;

		/**
		 * Do setup when jQuery reports that the DOM is ready.
		 *
		 * @since 4.0
		 */
		this.dom_ready = function() {
			me.setup();
			me.listeners();
		};

		/**
		 * Do initial setup.
		 *
		 * @since 4.0
		 */
		this.setup = function() {

			// Assign properties.
			me.nomenclature_enabled = $('#cpmu_bp_workshop_nomenclature');

			me.nomenclature_name_tr = $('.nomenclature_name');
			me.nomenclature_plural_tr = $('.nomenclature_plural');
			me.nomenclature_slug_tr = $('.nomenclature_slug');

		};

		/**
		 * Initialise listeners.
		 *
		 * @since 4.0
		 */
		this.listeners = function() {

			/**
			 * Add a change event listener to the "Change the name of a Group Document" checkbox.
			 *
			 * @param {Object} event The event object.
			 */
			me.nomenclature_enabled.on( 'change', function( event ) {

				// Toggle other <tr> elements.
				if ( this.checked ) {
					me.nomenclature_name_tr.show();
					me.nomenclature_plural_tr.show();
					me.nomenclature_slug_tr.show();
				} else {
					me.nomenclature_name_tr.hide();
					me.nomenclature_plural_tr.hide();
					me.nomenclature_slug_tr.hide();
				}

			} );

		};

	};

	// Init BuddyPress Groupblog Names.
	var BuddyPress_Groupblog_Names = new CPMS_Network_BuddyPress_Groupblog_Names();

	/**
	 * Trigger "dom_ready" methods where necessary.
	 *
	 * @since 4.0
	 */
	$(document).ready( function( $ ) {
		BuddyPress_Groupblog_Names.dom_ready();
	} );

} )( jQuery );
