/**
 * CommentPress Core "Site Settings" Javascript.
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
	 * Create Settings class.
	 *
	 * @since 4.0
	 */
	function CommentPress_Core_Settings_Site() {

		// Prevent reference collisions.
		var me = this;

		/**
		 * Initialise Settings.
		 *
		 * @since 4.0
		 */
		this.init = function() {
			me.init_localisation();
			me.init_settings();
		};

		// Init localisation array.
		me.localisation = [];

		/**
		 * Init localisation from Settings object.
		 *
		 * @since 4.0
		 */
		this.init_localisation = function() {
			if ( 'undefined' !== typeof CommentPress_Core_Settings_Site_Vars ) {
				me.localisation = CommentPress_Core_Settings_Site_Vars.localisation;
			}
		};

		/**
		 * Getter for localisation.
		 *
		 * @since 4.0
		 *
		 * @param {String} key The key for the desired localisation group.
		 * @param {String} identifier The identifier for the desired localisation string.
		 * @return {String} The localised string.
		 */
		this.get_localisation = function( key, identifier ) {
			return me.localisation[key][identifier];
		};

		// Init settings array.
		me.settings = [];

		/**
		 * Init settings from Settings object.
		 *
		 * @since 4.0
		 */
		this.init_settings = function() {
			if ( 'undefined' !== typeof CommentPress_Core_Settings_Site_Vars ) {
				me.settings = CommentPress_Core_Settings_Site_Vars.settings;
			}
		};

		/**
		 * Getter for retrieving a setting.
		 *
		 * @since 4.0
		 *
		 * @param {String} The identifier for the desired setting.
		 * @return The value of the setting.
		 */
		this.get_setting = function( identifier ) {
			return me.settings[identifier];
		};

	};

	// Init Settings.
	var Site_Settings = new CommentPress_Core_Settings_Site();
	Site_Settings.init();

	/**
	 * Create "Table of Contents" class.
	 *
	 * @since 4.0
	 */
	function CommentPress_Core_Settings_Site_TOC() {

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
			me.posts_or_pages_tr = $('.posts_or_pages');
			me.chapter_is_page_tr = $('.chapter_is_page');
			me.show_subpages_tr = $('.show_subpages');
			me.show_extended_tr = $('.show_extended');

			me.posts_or_pages = $('#cp_show_posts_or_pages_in_toc');
			me.chapter_is_page = $('#cp_toc_chapter_is_page');
			me.show_subpages = $('#cp_show_subpages');
			me.show_extended = $('#cp_show_extended_toc');

		};

		/**
		 * Initialise listeners.
		 *
		 * @since 4.0
		 */
		this.listeners = function() {

			/**
			 * Add a change event listener to the "Table of Contents contains" select.
			 *
			 * @param {Object} event The event object.
			 */
			me.posts_or_pages.on( 'change', function( event ) {

				// When Posts.
				if ( me.posts_or_pages.val() === 'post' ) {
					me.chapter_is_page_tr.hide();
					me.show_subpages_tr.hide();
					me.show_extended_tr.show();
				}

				// When Pages.
				if ( me.posts_or_pages.val() === 'page' ) {
					me.chapter_is_page_tr.show();
					if ( me.chapter_is_page.val() == '1' ) {
						me.show_subpages_tr.hide();
					} else {
						me.show_subpages_tr.show();
					}
					me.show_extended_tr.hide();
				}

			} );

			/**
			 * Add a change event listener to the "Chapters are" select.
			 *
			 * @param {Object} event The event object.
			 */
			me.chapter_is_page.on( 'change', function( event ) {

				// When Pages.
				if ( me.chapter_is_page.val() == '1' ) {
					me.show_subpages_tr.hide();
				}

				// When Headings.
				if ( me.chapter_is_page.val() == '0' ) {
					me.show_subpages_tr.show();
				}

			} );

		};

	};

	// Init Table of Contents.
	var Table_Of_Contents = new CommentPress_Core_Settings_Site_TOC();

	/**
	 * Trigger "dom_ready" methods where necessary.
	 *
	 * @since 4.0
	 */
	$(document).ready( function( $ ) {

		// The DOM is loaded now.
		Table_Of_Contents.dom_ready();

	} );

} )( jQuery );
