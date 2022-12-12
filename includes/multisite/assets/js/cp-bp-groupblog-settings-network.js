/**
 * CommentPress Multisite BuddyPress Groupblog "Network Settings" Javascript.
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
	 * Create "BuddyPress Groupblog" class.
	 *
	 * @since 4.0
	 */
	function CPMS_Network_BuddyPress_Groupblog() {

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
			me.cpmu_force_commentpress = $('#cpmu_force_commentpress');
			me.cpmu_bp_force_commentpress = $('#cpmu_bp_force_commentpress');

		};

		/**
		 * Initialise listeners.
		 *
		 * @since 4.0
		 */
		this.listeners = function() {

			/**
			 * Add a change event listener to the "Make all new sites CommentPress-enabled" checkbox.
			 *
			 * @param {Object} event The event object.
			 */
			me.cpmu_force_commentpress.on( 'change', function( event ) {

				// Checks "Make all new Group Blogs CommentPress-enabled" as well.
				if ( this.checked ) {
					me.cpmu_bp_force_commentpress.prop( 'checked', '1' );
				}

			} );

		};

	};

	// Init BuddyPress Groupblog.
	var BuddyPress_Groupblog = new CPMS_Network_BuddyPress_Groupblog();

	/**
	 * Trigger "dom_ready" methods where necessary.
	 *
	 * @since 4.0
	 */
	$(document).ready( function( $ ) {
		BuddyPress_Groupblog.dom_ready();
	} );

} )( jQuery );
