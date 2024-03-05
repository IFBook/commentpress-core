<?php
/**
 * CommentPress Multisite BuddyPress Groupblog Site class.
 *
 * Handles the Site functionality required for BuddyPress Groupblog compatibility.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Multisite BuddyPress Groupblog Site Class.
 *
 * This class provides the Site functionality required for BuddyPress Groupblog compatibility.
 *
 * @since 3.3
 */
class CommentPress_Multisite_BuddyPress_Groupblog_Site {

	/**
	 * Multisite loader object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $multisite The multisite loader object.
	 */
	public $multisite;

	/**
	 * BuddyPress object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $bp The BuddyPress object reference.
	 */
	public $bp;

	/**
	 * BuddyPress Groupblog object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $groupblog The BuddyPress Groupblog object reference.
	 */
	public $groupblog;

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param object $groupblog Reference to the BuddyPress Groupblog object.
	 */
	public function __construct( $groupblog ) {

		// Store references.
		$this->multisite = $groupblog->bp->multisite;
		$this->bp        = $groupblog->bp;
		$this->groupblog = $groupblog;

		// Init when the BuddyPress Groupblog is fully loaded.
		add_action( 'commentpress/multisite/bp/groupblog/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this object.
	 *
	 * @since 3.3
	 */
	public function initialise() {

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.3
	 */
	public function register_hooks() {

		// Override theme if the BuddyPress Groupblog default theme is not a CommentPress theme.
		add_filter( 'cp_forced_theme_slug', [ $this, 'theme_get' ], 20 );
		add_filter( 'cp_forced_theme_name', [ $this, 'theme_get' ], 20 );

		// Add our class(es) to the body classes.
		add_filter( 'commentpress/core/theme/body/classes', [ $this, 'theme_body_classes_filter' ] );

		// Check for the privacy of a Group Blog.
		add_action( 'init', [ $this, 'privacy_check' ] );

		// Maybe override the "publicness" of Group Blogs.
		add_filter( 'bp_is_blog_public', [ $this, 'is_public_filter' ], 20, 1 );

		// Get Group Avatar when listing Group Blogs.
		add_filter( 'bp_get_blog_avatar', [ $this, 'avatar_get' ], 20, 3 );

		// Filter the AJAX query string to add "action".
		add_filter( 'bp_ajax_querystring', [ $this, 'ajax_querystring' ], 20, 2 );

		// Amend Comment Activity.
		add_filter( 'pre_comment_approved', [ $this, 'user_can_comment' ], 99, 2 );

		/*
		// We can remove Group Blogs from the Blog list, but cannot update the
		// "total_blog_count_for_user" that is displayed on the tab *before* the
		// Blog list is built - hence filter disabled for now.
		add_filter( 'bp_has_blogs', [ $this, 'remove_from_loop' ], 20, 2 );
		*/

	}

	// -------------------------------------------------------------------------

	/**
	 * Checks if the current Site is a CommentPress-enabled Group Blog.
	 *
	 * Note that this only tests the current Blog and cannot be used to discover
	 * if a specific Blog is a CommentPress-enabled Group Blog.
	 *
	 * Also note that this method only functions after 'bp_setup_globals' has
	 * fired with priority 100.
	 *
	 * @since 3.3
	 *
	 * @return bool True if current Blog is a CommentPress-enabled Group Blog, false otherwise.
	 */
	public function is_commentpress_groupblog() {

		// Try to get core reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return false;
		}

		// Bail if this Blog is not a CommentPress-enabled Group Blog.
		if ( ! $core->bp->is_groupblog() ) {
			return false;
		}

		// It is a CommentPress-enabled Group Blog.
		return true;

	}

	/**
	 * Checks if the current Site is a BP Group Site.
	 *
	 * @since 3.8
	 *
	 * @return bool True if current Site is a BP Group Site, false otherwise.
	 */
	public function is_commentpress_groupsite() {

		// Bail if BP Group Sites is not present.
		if ( ! function_exists( 'bpgsites_is_groupsite' ) ) {
			return false;
		}

		// Bail if this Blog is not a BP Group Site.
		if ( ! bpgsites_is_groupsite( get_current_blog_id() ) ) {
			return false;
		}

		// It is a BP Group Site.
		return true;

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the Group Blog theme "stylesheet" slug as defined in Network Settings.
	 *
	 * @since 3.3
	 *
	 * @param str $default_theme The existing theme "stylesheet" slug.
	 * @return str $default_theme The modified theme "stylesheet" slug.
	 */
	public function theme_get( $default_theme ) {

		// Get the theme we've defined as the default for Group Blogs.
		$theme = $this->groupblog->setting_theme_get();
		if ( ! empty( $theme ) ) {
			$default_theme = $theme;
		}

		// --<
		return $default_theme;

	}

	/**
	 * Adds "Text Format" class to the body classes array.
	 *
	 * @since 4.0
	 *
	 * @param array $classes The existing body classes array.
	 * @return array $classes The modified body classes array.
	 */
	public function theme_body_classes_filter( $classes ) {

		// Bail if BuddyPress Groupblog is not present.
		if ( ! function_exists( 'get_groupblog_group_id' ) ) {
			return $classes;
		}

		// Bail if not a BuddyPress Group Page.
		if ( ! bp_is_groups_component() ) {
			return $classes;
		}

		// Get current Group.
		$current_group = groups_get_current_group();
		if ( ! ( $current_group instanceof BP_Groups_Group ) ) {
			return $classes;
		}

		// Bail if Group Blog Text Format not present.
		$text_format = $this->groupblog->group_type_get( $current_group->id );
		if ( empty( $text_format ) ) {
			return $classes;
		}

		// Add class to array.
		$classes[] = $text_format;

		// --<
		return $classes;

	}

	/**
	 * Checks if a non-public Group is being accessed by a User who is not a
	 * Member of the Group.
	 *
	 * Adapted from code in mahype's fork of BuddyPress Groupblog plugin, but not
	 * accepted because there may be cases where private Groups have public
	 * Group Blogs. Ours is not such a case.
	 *
	 * @see groupblog_privacy_check()
	 *
	 * @since 3.3
	 */
	public function privacy_check() {

		// Bail if BuddyPress Groupblog is not present.
		if ( ! function_exists( 'get_groupblog_group_id' ) ) {
			return;
		}

		// Allow network admins through regardless.
		if ( is_super_admin() ) {
			return;
		}

		// Allow if main Site.
		if ( is_main_site() ) {
			return;
		}

		// Allow if our privacy setting is not enabled.
		if ( ! $this->groupblog->setting_privacy_get() ) {
			return;
		}

		// Get current Blog ID.
		$blog_id = get_current_blog_id();
		if ( empty( $blog_id ) ) {
			return;
		}

		// Get Group ID for this Blog.
		$group_id = get_groupblog_group_id( $blog_id );
		if ( empty( $group_id ) ) {
			return;
		}

		// Get the Group object.
		$group = new BP_Groups_Group( $group_id );

		// Allow if the Group is public.
		if ( 'public' === $group->status ) {
			return;
		}

		// Allow if this Group Blog is not CommentPress-enabled.
		if ( ! $this->groupblog->groups->has_commentpress_groupblog( $group->id ) ) {
			return;
		}

		// Get current User ID.
		$current_user = wp_get_current_user();

		// Allow if the current User is a Member of the Blog.
		if ( is_user_member_of_blog( $current_user->ID, $blog_id ) ) {
			return;
		}

		/**
		 * Filters the default network home URL.
		 *
		 * @since 3.4
		 *
		 * @param string The default network home URL.
		 */
		wp_safe_redirect( apply_filters( 'bp_groupblog_privacy_redirect_url', network_site_url() ) );
		exit;

	}

	/**
	 * Overrides the "publicness" of Group Blogs.
	 *
	 * This is done so that we can set the "hide_sitewide" property of the
	 * Activity item (post or comment) depending on the Group's setting.
	 *
	 * TODO: Do we want to test if they are CommentPress-enabled?
	 *
	 * @since 3.3
	 *
	 * @param bool $blog_public_option True if Blog is public, false otherwise.
	 * @return bool $blog_public_option True if Blog is public, false otherwise.
	 */
	public function is_public_filter( $blog_public_option ) {

		// Bail if BuddyPress Groupblog is not present.
		if ( ! function_exists( 'get_groupblog_group_id' ) ) {
			return $blog_public_option;
		}

		// Get current Blog ID.
		$blog_id = get_current_blog_id();

		// Check if this Blog is a Group Blog.
		$group_id = get_groupblog_group_id( $blog_id );

		// Always true when this Blog is a Group Blog - Activities are registered.
		if ( ! empty( $group_id ) ) {
			$blog_public_option = 1;
		}

		// --<
		return $blog_public_option;

	}

	/**
	 * For Group Blogs, override the avatar with that of the Group.
	 *
	 * @since 3.3
	 *
	 * @param str   $avatar The existing HTML for displaying an avatar.
	 * @param int   $blog_id The numeric ID of the WordPress Blog.
	 * @param array $args Additional arguments.
	 * @return str $avatar The modified HTML for displaying an avatar.
	 */
	public function avatar_get( $avatar, $blog_id = 0, $args = [] ) {

		// Bail if BuddyPress Groupblog is not present.
		if ( ! function_exists( 'get_groupblog_group_id' ) ) {
			return $avatar;
		}

		// Get the Group ID.
		$group_id = get_groupblog_group_id( $blog_id );

		// Bail if this is not a Group Blog.
		if ( empty( $group_id ) ) {
			return $avatar;
		}

		// Define args.
		$args = [
			'item_id' => $group_id,
			'object'  => 'group',
		];

		// Get the Group avatar.
		$avatar = bp_core_fetch_avatar( $args );

		// --<
		return $avatar;

	}

	/**
	 * Modify the AJAX query string.
	 *
	 * @since 3.9.3
	 *
	 * @param string $query_string The query string for the BuddyPress loop.
	 * @param string $object The current object for the query string.
	 * @return string The modified query string.
	 */
	public function ajax_querystring( $query_string, $object ) {

		// Bail if not an Activity object.
		if ( 'activity' !== $object ) {
			return $query_string;
		}

		// Parse query string into an array.
		$query = wp_parse_args( $query_string );

		// Bail if no type is set.
		if ( empty( $query['type'] ) ) {
			return $query_string;
		}

		// Bail if not a type that we're looking for.
		if ( 'new_groupblog_post' !== $query['type'] && 'new_groupblog_comment' !== $query['type'] ) {
			return $query_string;
		}

		// Add the 'new_groupblog_post' type if it doesn't exist.
		if ( 'new_groupblog_post' === $query['type'] ) {
			if ( ! isset( $query['action'] ) || false === strpos( $query['action'], 'new_groupblog_post' ) ) {
				// The 'action' filters Activity items by the 'type' column.
				$query['action'] = 'new_groupblog_post';
			}
		}

		// Add the 'new_groupblog_comment' type if it doesn't exist.
		if ( 'new_groupblog_comment' === $query['type'] ) {
			if ( ! isset( $query['action'] ) || false === strpos( $query['action'], 'new_groupblog_comment' ) ) {
				// The 'action' filters Activity items by the 'type' column.
				$query['action'] = 'new_groupblog_comment';
			}
		}

		// The 'type' isn't used anywhere internally.
		unset( $query['type'] );

		// Return a query string.
		return build_query( $query );

	}

	/**
	 * Checks the capability to comment on a Site based on Group Membership.
	 *
	 * @since 3.3
	 *
	 * @param bool  $approved True if the Comment is approved, false otherwise.
	 * @param array $commentdata The Comment data.
	 * @return bool $approved Modified approval value. True if the Comment is approved, false otherwise.
	 */
	public function user_can_comment( $approved, $commentdata ) {

		// Bail if BuddyPress Groupblog is not present.
		if ( ! function_exists( 'get_groupblog_group_id' ) ) {
			return $approved;
		}

		// Get current Blog ID.
		$blog_id = get_current_blog_id();

		// Check if this Blog is a Group Blog.
		$group_id = get_groupblog_group_id( $blog_id );

		// Bail if this Blog is not a Group Blog.
		if ( empty( $group_id ) || ! is_numeric( $group_id ) ) {
			return $approved;
		}

		// Allow un-moderated commenting if this User is a Member.
		if ( groups_is_user_member( $commentdata['user_id'], $group_id ) ) {
			return 1;
		}

		// Pass through.
		return $approved;

	}

	/**
	 * Remove Group Blogs from Blog list.
	 *
	 * @since 3.3
	 *
	 * @param bool   $b True if there are Blogs, false otherwise.
	 * @param object $blogs The existing Blogs object.
	 * @return object $blogs The modified Blogs object.
	 */
	public function remove_from_loop( $b, $blogs ) {

		// Bail if BuddyPress Groupblog is not present.
		if ( ! function_exists( 'get_groupblog_group_id' ) ) {
			return $blogs;
		}

		// Loop through them.
		foreach ( $blogs->blogs as $key => $blog ) {

			// Get Group ID.
			$group_id = get_groupblog_group_id( $blog->blog_id );

			// Did we get one?
			if ( empty( $group_id ) || ! is_numeric( $group_id ) ) {
				continue;
			}

			// Exclude.
			unset( $blogs->blogs[ $key ] );

			// Recalculate global values.
			$blogs->blog_count       = $blogs->blog_count - 1;
			$blogs->total_blog_count = $blogs->total_blog_count - 1;
			$blogs->pag_num          = $blogs->pag_num - 1;

		}

		// Renumber the array keys to account for missing items.
		$blogs_new    = array_values( $blogs->blogs );
		$blogs->blogs = $blogs_new;

		// --<
		return $blogs;

	}

	/**
	 * Checks the Site Text Format of the current Site.
	 *
	 * @since 3.3
	 *
	 * @return int The Site Text Format if there is one, default otherwise.
	 */
	public function text_format_get() {

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return 0;
		}

		// Return the setting.
		return $core->formatter->setting_formatter_get();

	}

	/**
	 * Gets the array of compatible themes.
	 *
	 * Themes must be tagged for Group Blog and CommentPress Core.
	 *
	 * @since 4.0
	 *
	 * @param bool $stylesheets True if only stylesheet "slugs" are required.
	 * @return array $groupblog_themes The array of compatible themes.
	 */
	public function themes_get( $stylesheets = true ) {

		// Init return.
		$groupblog_themes = [];

		// Define theme search args.
		$args = [

			// Only error-free themes.
			'errors'  => false,

			// All themes.
			'allowed' => null,

			// Use current Blog as reference.
			'blog_id' => 0,

		];

		// Get the theme data.
		$themes = wp_get_themes( $args );

		// Init options array.
		$options = [];

		// We must get *at least* one (the Default), but let's be safe.
		if ( empty( $themes ) ) {
			return $element;
		}

		// Find the appropriately tagged themes.
		foreach ( $themes as $theme ) {

			// Get the Theme tags.
			$tags = $theme->get( 'Tags' );

			// Is it a CommentPress Core and Group Blog theme?
			if ( in_array( 'commentpress', $tags, true ) && in_array( 'groupblog', $tags, true ) ) {

				// Maybe use stylesheet as theme data.
				if ( true === $stylesheets ) {
					$groupblog_themes[ $theme->get_stylesheet() ] = $theme->get( 'Name' );
				} else {
					$groupblog_themes[ $theme->get_stylesheet() ] = $theme;
				}

			}

		}

		// --<
		return $groupblog_themes;

	}

}
