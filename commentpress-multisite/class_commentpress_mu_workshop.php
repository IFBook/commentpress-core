<?php

/**
 * CommentPress Core BuddyPress Groupblog Class.
 *
 * This class overrides the name of Groupblogs from "Blog" (or "Document") to "Workshop".
 *
 * @since 3.3
 */
class Commentpress_Multisite_Buddypress_Groupblog {

	/**
	 * Plugin object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $parent_obj The plugin object.
	 */
	public $parent_obj;

	/**
	 * Database interaction object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $db The database object.
	 */
	public $db;

	/**
	 * Flag whether or not to rename a groupblog.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $groupblog_nomenclature Flag whether or not to rename a groupblog - default to "off".
	 */
	public $groupblog_nomenclature = 0;

	/**
	 * Default singular name of a groupblog.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $groupblog_nomenclature_name Default name of a groupblog.
	 */
	public $groupblog_nomenclature_name = '';

	/**
	 * Default plural name of a groupblog.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $groupblog_nomenclature_plural Default plural name of a groupblog.
	 */
	public $groupblog_nomenclature_plural = '';

	/**
	 * Default slug of a groupblog.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $groupblog_nomenclature_slug Default slug of a groupblog.
	 */
	public $groupblog_nomenclature_slug = '';



	/**
	 * Initialises this object.
	 *
	 * @since 3.3
	 *
	 * @param object $parent_obj a reference to the parent object.
	 */
	public function __construct( $parent_obj = null ) {

		// Store reference to "parent" (calling obj, not OOP parent).
		$this->parent_obj = $parent_obj;

		// Store reference to database wrapper (child of calling obj).
		$this->db = $this->parent_obj->db;

		// Make properties translatable.
		$this->groupblog_nomenclature_name = __( 'Document', 'commentpress-core' );
		$this->groupblog_nomenclature_plural = __( 'Documents', 'commentpress-core' );
		$this->groupblog_nomenclature_slug = __( 'document', 'commentpress-core' );

		// Init.
		$this->_init();

	}



	/**
	 * Set up all items associated with this object.
	 *
	 * @since 3.3
	 */
	public function initialise() {

	}



	/**
	 * If needed, destroys all items associated with this object.
	 *
	 * @since 3.3
	 */
	public function destroy() {

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Public Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Override the name of the filter item.
	 *
	 * @since 3.3
	 *
	 * @return str The name in the groupblog comments label.
	 */
	public function groupblog_comment_name() {

		// Default name.
		return sprintf(
			__( '%s Comments', 'commentpress-core' ),
			$this->groupblog_nomenclature_name
		);

	}



	/**
	 * Override the name of the filter item.
	 *
	 * @since 3.3
	 *
	 * @return str The plural name in the groupblog posts label.
	 */
	public function groupblog_post_name() {

		// Default name.
		return sprintf(
			__( '%s Posts', 'commentpress-core' ),
			$this->groupblog_nomenclature_name
		);

	}



	/**
	 * Override the name of the filter item.
	 *
	 * @since 3.3
	 *
	 * @return str The singular name of the groupblog post.
	 */
	public function activity_post_name() {

		// Default name.
		return sprintf(
			__( '%s post', 'commentpress-core' ),
			strtolower( $this->groupblog_nomenclature_name )
		);

	}



	/**
	 * Override the name of the sub-nav item.
	 *
	 * @since 3.3
	 *
	 * @return str The singular name of the groupblog post.
	 */
	public function filter_blog_name( $name ) {

		// --<
		return $this->groupblog_nomenclature_name;

	}



	/**
	 * Override the slug of the sub-nav item.
	 *
	 * @since 3.3
	 *
	 * @return The slug of the sub-nav item.
	 */
	public function filter_blog_slug( $slug ) {

		// --<
		return $this->groupblog_nomenclature_slug;

	}



	/**
	 * Override the title of the "Recent Comments in..." link.
	 *
	 * @since 3.3
	 *
	 * @param str $title The title of the Recent Comments heading.
	 * @return str $title The modified title of the Recent Comments heading.
	 */
	public function activity_tab_recent_title_blog( $title ) {

		// If groupblog.
		global $commentpress_core;
		if (
			! is_null( $commentpress_core ) AND
			is_object( $commentpress_core ) AND
			$commentpress_core->is_groupblog()
		) {

			// Override default link name.
			return apply_filters(
				'cpmsextras_user_links_new_site_title',
				sprintf(
					__( 'Recent Comments in this %s', 'commentpress-core' ),
					$this->groupblog_nomenclature_name
				)
			);

		}

		// If main site
		if ( is_multisite() AND is_main_site() ) {

			// Override default link name.
			return apply_filters(
				'cpmsextras_user_links_new_site_title',
				__( 'Recent Comments in Site Blog', 'commentpress-core' )
			);

		}

		// --<
		return $title;

	}



	/**
	 * Override title on All Comments page.
	 *
	 * @since 3.3
	 *
	 * @param str $title The title of the All Comments heading.
	 * @return str $title The modified title of the All Comments heading.
	 */
	public function page_all_comments_blog_title( $title ) {

		// Override if groupblog.
		if ( ! $this->parent_obj->bp->_is_commentpress_groupblog() ) {
			return $title;
		}

		// --<
		return sprintf(
			__( 'Comments on %s Posts', 'commentpress-core' ),
			$this->groupblog_nomenclature_name
		);

	}



	/**
	 * Override title on All Comments page.
	 *
	 * @since 3.3
	 *
	 * @param str $title The title of the "Comments on..." heading.
	 * @return str $title The modified title of the "Comments on..." heading.
	 */
	public function page_all_comments_book_title( $title ) {

		// Override if groupblog.
		if ( ! $this->parent_obj->bp->_is_commentpress_groupblog() ) {
			return $title;
		}

		// --<
		return sprintf(
			__( 'Comments on %s Pages', 'commentpress-core' ),
			$this->groupblog_nomenclature_name
		);

	}



	/**
	 * Override title on Activity tab.
	 *
	 * @since 3.3
	 *
	 * @param str $title The title of the "Recent Activity in..." heading.
	 * @return str $title The modified title of the "Recent Activity in..." heading.
	 */
	public function filter_activity_title_all_yours( $title ) {

		// Override if groupblog.
		if (
			! bp_is_root_blog() AND
			! $this->parent_obj->bp->_is_commentpress_groupblog() )
		{
			return $title;
		}

		// --<
		return sprintf(
			__( 'Recent Activity in your %s', 'commentpress-core' ),
			$this->groupblog_nomenclature_plural
		);

	}



	/**
	 * Override title on Activity tab.
	 *
	 * @since 3.3
	 *
	 * @param str $title The title of the "Recent Activity in..." heading.
	 * @return str $title The modified title of the "Recent Activity in..." heading.
	 */
	public function filter_activity_title_all_public( $title ) {

		// Override if groupblog.
		if (
			! bp_is_root_blog() AND
			! $this->parent_obj->bp->_is_commentpress_groupblog() )
		{
			return $title;
		}

		// --<
		return sprintf(
			__( 'Recent Activity in Public %s', 'commentpress-core' ),
			$this->groupblog_nomenclature_plural
		);

	}



	/**
	 * Override CommentPress Core "Title Page".
	 *
	 * @since 3.3
	 *
	 * @param str $title The title of the "Groupblog Home Page" heading.
	 * @return str $title The modified title of the "Groupblog Home Page" heading.
	 */
	public function filter_nav_title_page_title( $title ) {

		// Bail if main BuddyPress site.
		if ( bp_is_root_blog() ) return $title;

		// Bail if not groupblog.
		if ( ! $this->parent_obj->bp->_is_commentpress_groupblog() ) {
			return $title;
		}

		// --<
		return sprintf(
			__( '%s Home Page', 'commentpress-core' ),
			$this->groupblog_nomenclature_name
		);

	}



	/**
	 * Override the BuddyPress Sites Directory "visit" button.
	 *
	 * @since 3.3
	 *
	 * @param str $button The title of the "Visit Site" heading.
	 * @return str $title The modified title of the "Visit Site" heading.
	 */
	public function get_blogs_visit_blog_button( $button ) {

		// Update link for groupblogs.
		return sprintf(
			__( 'Visit %s', 'commentpress-core' ),
			$this->groupblog_nomenclature_name
		);

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Private Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Object initialisation.
	 *
	 * @since 3.3
	 */
	public function _init() {

		// Is this the back end?
		if ( is_admin() ) {

			// Add element to Network BuddyPress form.
			add_filter( 'cpmu_network_buddypress_options_form', [ $this, '_buddypress_admin_form' ] );

			// Hook into Network BuddyPress form update.
			add_action( 'cpmu_db_options_update', [ $this, '_buddypress_admin_update' ], 21 );

			// Hook into Network BuddyPress options reset.
			add_filter( 'cpmu_buddypress_options_get_defaults', [ $this, '_get_default_settings' ], 10, 1 );

		}

		// Do we have our option set?
		if ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature' ) == '1' ) {

			// Store the setting locally.
			$this->groupblog_nomenclature = '1';

			// Do we have the name option already defined?
			if ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature_name' ) == '' ) {

				// No, so we must have switched to the legacy "Workshop" setting.
				$this->groupblog_nomenclature_name = $this->_get_legacy_name();

			} else {

				// Store the setting locally.
				$this->groupblog_nomenclature_name = $this->db->option_get( 'cpmu_bp_workshop_nomenclature_name' );

			}

			// Do we have the plural option already defined?
			if ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature_plural' ) == '' ) {

				// No, likewise we must have switched to the legacy "Workshop" setting.
				$this->groupblog_nomenclature_plural = $this->_get_legacy_plural();

			} else {

				// Store the setting locally.
				$this->groupblog_nomenclature_plural = $this->db->option_get( 'cpmu_bp_workshop_nomenclature_plural' );

			}

			// Do we have the slug option already defined?
			if ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature_slug' ) == '' ) {

				// No, likewise we must have switched to the legacy "Workshop" setting.
				$this->groupblog_nomenclature_slug = $this->_get_legacy_slug();

			} else {

				// Store the setting locally.
				$this->groupblog_nomenclature_slug = $this->db->option_get( 'cpmu_bp_workshop_nomenclature_slug' );

			}

			// Register hooks.
			$this->_register_hooks();

		}

	}



	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.3
	 */
	public function _register_hooks() {

		// Override CommentPress Core "Title Page".
		add_filter( 'cp_nav_title_page_title', [ $this, 'filter_nav_title_page_title' ], 25 );

		// Override CommentPress Core title of "view document" button in blog lists.
		add_filter( 'cp_get_blogs_visit_groupblog_button', [ $this, 'get_blogs_visit_blog_button' ], 25, 1 );

		// Filter bp-groupblog defaults.
		add_filter( 'cpmu_bp_groupblog_subnav_item_name', [ $this, 'filter_blog_name' ], 25 );
		add_filter( 'cpmu_bp_groupblog_subnav_item_slug', [ $this, 'filter_blog_slug' ], 25 );

		// Change name of activity sidebar headings.
		add_filter( 'cp_activity_tab_recent_title_all_yours', [ $this, 'filter_activity_title_all_yours' ], 25 );
		add_filter( 'cp_activity_tab_recent_title_all_public', [ $this, 'filter_activity_title_all_public' ], 25 );

		// Override with 'workshop'.
		add_filter( 'cp_activity_tab_recent_title_blog', [ $this, 'activity_tab_recent_title_blog' ], 25, 1 );

		// Override titles of BuddyPress activity filters.
		add_filter( 'cp_groupblog_comment_name', [ $this, 'groupblog_comment_name' ], 25 );
		add_filter( 'cp_groupblog_post_name', [ $this, 'groupblog_post_name' ], 25 );

		// Cp_activity_post_name_filter.
		add_filter( 'cp_activity_post_name', [ $this, 'activity_post_name' ], 25 );

		// Override label on All Comments page.
		add_filter( 'cp_page_all_comments_book_title', [ $this, 'page_all_comments_book_title' ], 25, 1 );
		add_filter( 'cp_page_all_comments_blog_title', [ $this, 'page_all_comments_blog_title' ], 25, 1 );

	}



	/**
	 * Add our options to the BuddyPress admin form.
	 *
	 * @since 3.3
	 *
	 * @return str $element The admin form element.
	 */
	public function _buddypress_admin_form() {

		// Check if we already have it switched on.
		if ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature' ) == '1' ) {

			// Do we have the name option already defined?
			if ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature_name' ) == '' ) {

				// No, so we must have switched to the legacy "Workshop" setting.
				$this->groupblog_nomenclature_name = $this->_get_legacy_name();

			}

			// Do we have the plural option already defined?
			if ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature_plural' ) == '' ) {

				// No, likewise we must have switched to the legacy "Workshop" setting.
				$this->groupblog_nomenclature_plural = $this->_get_legacy_plural();

			}

		}

		// Define form element.
		$element = '
		<tr valign="top">
			<th scope="row"><label for="cpmu_bp_groupblog_nomenclature">' . __( 'Change the name of a Group "Document"?', 'commentpress-core' ) . '</label></th>
			<td><input id="cpmu_bp_groupblog_nomenclature" name="cpmu_bp_groupblog_nomenclature" value="1" type="checkbox"' . ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature' ) == '1' ? ' checked="checked"' : '' ) . ' /></td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="cpmu_bp_groupblog_nomenclature_name">' . __( 'Singular name for a Group "Document"', 'commentpress-core' ) . '</label></th>
			<td><input id="cpmu_bp_groupblog_nomenclature_name" name="cpmu_bp_groupblog_nomenclature_name" value="' . ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature_name' ) == '' ? $this->groupblog_nomenclature_name : $this->db->option_get( 'cpmu_bp_workshop_nomenclature_name' ) ) . '" type="text" /></td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="cpmu_bp_groupblog_nomenclature_plural">' . __( 'Plural name for Group "Documents"', 'commentpress-core' ) . '</label></th>
			<td><input id="cpmu_bp_groupblog_nomenclature_plural" name="cpmu_bp_groupblog_nomenclature_plural" value="' . ( $this->db->option_get( 'cpmu_bp_workshop_nomenclature_plural' ) == '' ? $this->groupblog_nomenclature_plural : $this->db->option_get( 'cpmu_bp_workshop_nomenclature_plural' ) ) . '" type="text" /></td>
		</tr>

		';

		// --<
		return $element;

	}



	/**
	 * Hook into Network BuddyPress form update.
	 *
	 * @since 3.3
	 */
	public function _buddypress_admin_update() {

		// Init.
		$cpmu_bp_groupblog_nomenclature = 0;

		// Get variables.
		extract( $_POST );

		// Set on/off option.
		$cpmu_bp_groupblog_nomenclature = esc_sql( $cpmu_bp_groupblog_nomenclature );
		$this->db->option_set( 'cpmu_bp_workshop_nomenclature', ( $cpmu_bp_groupblog_nomenclature ? 1 : 0 ) );

		// Get name option.
		$cpmu_bp_groupblog_nomenclature_name = esc_sql( $cpmu_bp_groupblog_nomenclature_name );

		// Revert to default if we didn't get one.
		if ( $cpmu_bp_groupblog_nomenclature_name == '' ) {
			$cpmu_bp_groupblog_nomenclature_name = $this->groupblog_nomenclature_name;
		}

		// Set name option.
		$this->db->option_set( 'cpmu_bp_workshop_nomenclature_name', $cpmu_bp_groupblog_nomenclature_name );

		// Get plural option.
		$cpmu_bp_groupblog_nomenclature_plural = esc_sql( $cpmu_bp_groupblog_nomenclature_plural );

		// Revert to default if we didn't get one.
		if ( $cpmu_bp_groupblog_nomenclature_plural == '' ) {
			$cpmu_bp_groupblog_nomenclature_plural = $this->groupblog_nomenclature_plural;
		}

		// Set plural option.
		$this->db->option_set( 'cpmu_bp_workshop_nomenclature_plural', $cpmu_bp_groupblog_nomenclature_plural );

		// Set slug option.
		$cpmu_bp_groupblog_nomenclature_slug = sanitize_title( $cpmu_bp_groupblog_nomenclature_name );
		$this->db->option_set( 'cpmu_bp_workshop_nomenclature_slug', $cpmu_bp_groupblog_nomenclature_slug );

	}



	/**
	 * Add our default BuddyPress-related settings.
	 *
	 * @since 3.3
	 *
	 * @return array $settings The default settings.
	 */
	public function _get_default_settings( $settings ) {

		// Add our options.
		$settings['cpmu_bp_workshop_nomenclature'] = $this->groupblog_nomenclature;
		$settings['cpmu_bp_workshop_nomenclature_name'] = $this->groupblog_nomenclature_name;
		$settings['cpmu_bp_workshop_nomenclature_plural'] = $this->groupblog_nomenclature_plural;
		$settings['cpmu_bp_workshop_nomenclature_slug'] = $this->groupblog_nomenclature_slug;

		// --<
		return $settings;

	}



	/**
	 * Get legacy name when already set.
	 *
	 * @since 3.3
	 *
	 * @return str $name The legacy singular name of a groupblog.
	 */
	public function _get_legacy_name() {

		// --<
		return __( 'Workshop', 'commentpress-core' );

	}



	/**
	 * Get legacy plural name when already set.
	 *
	 * @since 3.3
	 *
	 * @return str $name The legacy plural name of a groupblog.
	 */
	public function _get_legacy_plural() {

		// --<
		return __( 'Workshops', 'commentpress-core' );

	}



	/**
	 * Get legacy slug when already set.
	 *
	 * @since 3.3
	 *
	 * @return str $name The legacy slug of a groupblog.
	 */
	public function _get_legacy_slug() {

		// --<
		return 'workshop';

	}



//##############################################################################



} // Class ends.



