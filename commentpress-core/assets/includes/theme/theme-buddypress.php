<?php
/**
 * CommentPress Core BuddyPress Theme compatibility.
 *
 * Handles Theme compatibility with BuddyPress.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;



if ( ! function_exists( 'commentpress_fix_bp_core_avatar_url' ) ) :

	/**
	 * Filter to fix broken group avatar images in BuddyPress 1.7.
	 *
	 * @since 3.3
	 *
	 * @param string $url The existing URL of the avatar.
	 * @return string $url The modified URL of the avatar.
	 */
	function commentpress_fix_bp_core_avatar_url( $url ) {

		// If in multisite and on non-root site.
		if ( is_multisite() && ! bp_is_root_blog() ) {

			// Switch to root site.
			switch_to_blog( bp_get_root_blog_id() );

			// Get upload dir data.
			$upload_dir = wp_upload_dir();

			// Get storage location of avatars.
			$url = $upload_dir['baseurl'];

			// Switch back.
			restore_current_blog();

		}

		// --<
		return $url;

	}

endif;



if ( ! function_exists( 'commentpress_amend_search_query' ) ) :

	/**
	 * Restrict search query to pages only.
	 *
	 * @since 3.3
	 *
	 * @param object $query The query object, passed by reference.
	 */
	function commentpress_amend_search_query( &$query ) {

		/*
		 * Restrict to search outside admin.
		 *
		 * Note: BuddyPress does a redirect to the blog page and so $query->is_search is not set.
		 */
		if ( ! is_admin() && ! empty( $query->query['s'] ) ) {

			// Is this a BuddyPress search on the main BuddyPress instance?
			if ( function_exists( 'bp_search_form_type_select' ) && bp_is_root_blog() ) {

				// Search posts and pages.
				$query->set( 'post_type', apply_filters( 'commentpress_amend_search_query_post_types', [ 'post', 'page' ] ) );

				// Declare access to globals.
				global $commentpress_core;

				// If we have the plugin enabled.
				if ( is_object( $commentpress_core ) ) {

					// Get special pages array, if it's there.
					$special_pages = $commentpress_core->db->option_get( 'cp_special_pages' );

					// Exclude special pages if we have them.
					if ( is_array( $special_pages ) ) {
						$query->set( 'post__not_in', apply_filters( 'commentpress_amend_search_query_exclusions', $special_pages ) );
					}

				}

			}

		}

	}

endif;

// Add callback for search query modification.
add_filter( 'pre_get_posts', 'commentpress_amend_search_query' );



if ( ! function_exists( 'commentpress_groupblog_classes' ) ) :

	/**
	 * Add classes to #content in BuddyPress, so that we can distinguish different GroupBlog types.
	 *
	 * @since 3.3
	 *
	 * @return str $groupblog_class The class for the groupblog.
	 */
	function commentpress_groupblog_classes() {

		// Init empty.
		$groupblog_class = '';

		// Only add classes when bp-groupblog is active.
		if ( function_exists( 'get_groupblog_group_id' ) ) {

			// Init groupblogtype.
			$groupblogtype = 'groupblog';

			// Get group blogtype.
			$groupblog_type = groups_get_groupmeta( bp_get_current_group_id(), 'groupblogtype' );

			// Add to default if we get one.
			if ( $groupblog_type ) {
				$groupblogtype .= ' ' . $groupblog_type;
			}

			// Complete.
			$groupblog_class = ' class="' . $groupblogtype . '"';

		}

		// --<
		return $groupblog_class;

	}

endif;



if ( ! function_exists( 'commentpress_bp_activity_css_class' ) ) :

	/**
	 * Update BuddyPress activity CSS class with GroupBlog type.
	 *
	 * @since 3.3
	 *
	 * @param str $existing_class The existing class.
	 * @return str $existing_class The overridden class.
	 */
	function commentpress_bp_activity_css_class( $existing_class ) {

		// Init group blog type.
		$groupblog_type = '';

		// Get current item.
		global $activities_template;
		$current_activity = $activities_template->activity;

		// For group activity.
		if ( $current_activity->component == 'groups' ) {

			// Get group blogtype.
			$groupblog_type = groups_get_groupmeta( $current_activity->item_id, 'groupblogtype' );

			// Add space before if we have it.
			if ( $groupblog_type ) {
				$groupblog_type = ' ' . $groupblog_type;
			}

		}

		// --<
		return $existing_class . $groupblog_type;

	}

endif;



if ( ! function_exists( 'commentpress_bp_blog_css_class' ) ) :

	/**
	 * Update BuddyPress Sites Directory blog item CSS class with GroupBlog type.
	 *
	 * @since 3.3
	 *
	 * @param array $classes The existing classes.
	 * @return array $classes The overridden classes.
	 */
	function commentpress_bp_blog_css_class( $classes ) {

		// Bail if not a groupblog.
		if ( ! function_exists( 'get_groupblog_group_id' ) ) {
			return $classes;
		}

		// Access BuddyPress object.
		global $blogs_template;

		// Get group ID.
		$group_id = get_groupblog_group_id( $blogs_template->blog->blog_id );
		if ( isset( $group_id ) && is_numeric( $group_id ) && $group_id > 0 ) {

			// Get group blogtype.
			$groupblog_type = groups_get_groupmeta( $group_id, 'groupblogtype' );

			// Add classes if we get one.
			if ( $groupblog_type ) {
				$classes[] = 'bp-groupblog-blog';
				$classes[] = $groupblog_type;
			}

		}

		// --<
		return $classes;

	}

endif;



if ( ! function_exists( 'commentpress_bp_group_css_class' ) ) :

	/**
	 * Update BuddyPress Groups Directory group item CSS class with GroupBlog type.
	 *
	 * @since 3.3
	 *
	 * @param array $classes The existing classes.
	 * @return array $classes The overridden classes.
	 */
	function commentpress_bp_group_css_class( $classes ) {

		// Only add classes when bp-groupblog is active.
		if ( ! function_exists( 'get_groupblog_group_id' ) ) {
			return $classes;
		}

		// Get group blogtype.
		$groupblog_type = groups_get_groupmeta( bp_get_group_id(), 'groupblogtype' );

		// Add class if we get one.
		if ( $groupblog_type ) {
			$classes[] = $groupblog_type;
		}

		// --<
		return $classes;

	}

endif;




if ( ! function_exists( 'commentpress_prefix_bp_templates' ) ) :

	/**
	 * Prefixes BuddyPress pages with the div wrappers that CommentPress Core needs.
	 *
	 * @since 3.3
	 */
	function commentpress_prefix_bp_templates() {

		// Prefixed wrappers.
		echo '<div id="wrapper">
			  <div id="main_wrapper" class="clearfix">
			  <div id="page_wrapper">';

	}

endif;

// Add callback for the above.
add_action( 'bp_before_directory_groupsites_page', 'commentpress_prefix_bp_templates' );



if ( ! function_exists( 'commentpress_suffix_bp_templates' ) ) :

	/**
	 * Suffixes BuddyPress pages with the div wrappers that CommentPress Core needs.
	 *
	 * @since 3.3
	 */
	function commentpress_suffix_bp_templates() {

		// Prefixed wrappers.
		echo '</div><!-- /page_wrapper -->
			  </div><!-- /main_wrapper -->
			  </div><!-- /wrapper -->';

	}

endif;

// Add callback for the above.
add_action( 'bp_after_directory_groupsites_page', 'commentpress_suffix_bp_templates' );



if ( ! function_exists( 'commentpress_unwrap_buddypress_button' ) ) :

	/**
	 * Removes the default wrapping of buttons in the directory nav.
	 *
	 * The BP_Button class was refactored in BuddyPress 2.7 and all buttons are
	 * wrapped in <div class="generic-button"></div> by default. This causes the
	 * link to pick up the style of a button, which breaks the nav menu layout.
	 * This filter unwraps the link so it appears the same as in BuddyPress 2.6.n.
	 *
	 * @since 3.9.1
	 *
	 * @param array $button_args The existing params used to define the button.
	 * @return array $button_args The modified params used to define the button.
	 */
	function commentpress_unwrap_buddypress_button( $button_args ) {

		// Bail if not BP 2.7.x.
		if ( ! function_exists( 'bp_core_filter_wp_query' ) ) {
			return $button_args;
		}

		// Remove parent element.
		$button_args['parent_element'] = '';

		// --<
		return $button_args;

	}

endif;

// Add callbacks for the above.
add_filter( 'bp_get_group_create_button', 'commentpress_unwrap_buddypress_button' );
add_filter( 'bp_get_blog_create_button', 'commentpress_unwrap_buddypress_button' );
