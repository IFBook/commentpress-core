<?php
/**
 * CommentPress Multisite BuddyPress class.
 *
 * Handles BuddyPress compatibility in WordPress Multisite contexts.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Multisite BuddyPress Class.
 *
 * This class encapsulates BuddyPress compatibility.
 *
 * @since 3.3
 */
class CommentPress_Multisite_BuddyPress {

	/**
	 * Multisite loader object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $multisite The multisite loader object.
	 */
	public $multisite;

	/**
	 * BuddyPress Group Blog compatibility object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $workshop The BuddyPress Group Blog object reference.
	 */
	public $workshop;

	/**
	 * CommentPress Core enabled on all Group Blogs flag.
	 *
	 * @since 3.3
	 * @access public
	 * @var str $force_commentpress The CommentPress Core enabled on all Group Blogs flag.
	 */
	public $force_commentpress = '0';

	/**
	 * Default theme stylesheet for Group Blogs.
	 *
	 * @since 3.3
	 * @access public
	 * @var str $groupblog_theme The default theme stylesheet.
	 */
	public $groupblog_theme = 'commentpress-flat';

	/**
	 * Group Blog privacy flag.
	 *
	 * @since 3.3
	 * @access public
	 * @var bool $groupblog_privacy True if private Groups have private Group Blogs.
	 */
	public $groupblog_privacy = 1;

	/**
	 * Require login to leave Comments on Group Blogs flag.
	 *
	 * @since 3.3
	 * @access public
	 * @var bool $require_comment_registration True if login required.
	 */
	public $require_comment_registration = 1;

	/**
	 * Classes directory path.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $classes_path Relative path to the classes directory.
	 */
	public $classes_path = 'includes/multisite/classes/';

	/**
	 * Metabox template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $metabox_path Relative path to the Metabox directory.
	 */
	private $metabox_path = 'includes/multisite/assets/templates/wordpress/metaboxes/';

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param object $multisite Reference to the multisite loader object.
	 */
	public function __construct( $multisite ) {

		// Store reference to multisite loader object.
		$this->multisite = $multisite;

		// Intialise when BuddyPress is loaded.
		add_action( 'bp_include', [ $this, 'initialise' ] );

	}

	/**
	 * Intialises this object.
	 *
	 * @since 3.3
	 */
	public function initialise() {

		// Initialise db for BuddyPress.
		$this->multisite->db->options_initialise( 'buddypress' );

		// Bootstrap this object.
		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

		/**
		 * Broadcast that BuddyPress has loaded.
		 *
		 * Used internally to bootstrap objects.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/multisite/bp/loaded' );

	}

	/**
	 * Includes class files.
	 *
	 * @since 4.0
	 */
	public function include_files() {

		// Include class files.
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-multisite-bp-groupblog.php';

	}

	/**
	 * Sets up the objects in this class.
	 *
	 * @since 4.0
	 */
	public function setup_objects() {

		// Initialise objects.
		$this->workshop = new CommentPress_Multisite_BuddyPress_GroupBlog( $this );

	}

	/**
	 * Register hooks.
	 *
	 * @since 3.3
	 * @since 4.0 Renamed.
	 */
	public function register_hooks() {

		// Enable HTML Comments and content for authors.
		add_action( 'init', [ $this, 'allow_html_content' ] );

		// Check for the privacy of a Group Blog.
		add_action( 'init', [ $this, 'groupblog_privacy_check' ] );

		// Add some tags to the allowed tags in Activities.
		add_filter( 'bp_activity_allowed_tags', [ $this, 'activity_allowed_tags' ], 20 );

		// Allow Comment Authors to edit their own Comments.
		add_filter( 'map_meta_cap', [ $this, 'enable_comment_editing' ], 10, 4 );

		// Amend Comment Activity.
		add_filter( 'pre_comment_approved', [ $this, 'pre_comment_approved' ], 99, 2 );

		/*
		// A nicer way to assess Comment approval?
		add_action( 'preprocess_comment', [ $this, 'my_check_comment' ], 1 );
		*/

		// Register "page" as a Post Type that BuddyPress records Comment Activity for.
		add_action( 'init', [ $this, 'register_comment_tracking_on_pages' ], 100 );

		// Add Pages to the Post Types that BuddyPress records Comment Activity for.
		add_filter( 'bp_blogs_record_comment_post_types', [ $this, 'record_comments_on_pages' ], 10, 1 );

		/*
		// Add Pages to the Post Types that BuddyPress records published Activity for.
		add_filter( 'bp_blogs_record_post_post_types', [ $this, 'record_published_pages' ], 10, 1 );
		*/

		// Make sure "Allow activity stream commenting on Blog and forum posts" is disabled.
		add_action( 'bp_disable_blogforum_comments', [ $this, 'disable_blogforum_comments' ], 20, 1 );

		// Override "publicness" of Group Blogs.
		add_filter( 'bp_is_blog_public', [ $this, 'is_blog_public' ], 20, 1 );

		// Amend BuddyPress Group Activity after Core Loader class does.
		add_action( 'bp_setup_globals', [ $this, 'group_activity_mods' ], 1001 );

		// Get Group Avatar when listing Group Blogs.
		add_filter( 'bp_get_blog_avatar', [ $this, 'get_blog_avatar' ], 20, 3 );

		// Filter bp-groupblog defaults.
		add_filter( 'bp_groupblog_subnav_item_name', [ $this, 'filter_blog_name' ], 20 );
		add_filter( 'bp_groupblog_subnav_item_slug', [ $this, 'filter_blog_slug' ], 20 );

		// Override CommentPress Core "Welcome Page".
		add_filter( 'cp_nav_title_page_title', [ $this, 'filter_nav_title_page_title' ], 20 );

		// Override the name of the button on the BuddyPress "blogs" screen.
		// To override this, just add the same filter with a priority of 21 or greater.
		add_filter( 'bp_get_blogs_visit_blog_button', [ $this, 'get_blogs_visit_blog_button' ], 20 );

		/*
		// We can remove Group Blogs from the Blog list, but cannot update the total_blog_count_for_user
		// that is displayed on the tab *before* the Blog list is built - hence filter disabled for now.
		add_filter( 'bp_has_blogs', [ $this, 'remove_groupblog_from_loop' ], 20, 2 );
		*/

		/*
		 * Duplicated from 'class-core-formatter.php' because CommentPress Core need
		 * not be active on the main Blog, but we still need the options to appear
		 * in the Group Blog Create screen.
		 */

		// TODO: Remove when core installation is more lightweight.

		// Set Blog Type options.
		add_filter( 'cp_blog_type_options', [ $this, 'blog_type_options' ], 21 );

		// Set Blog Type options label.
		add_filter( 'cp_blog_type_label', [ $this, 'blog_type_label' ], 21 );

		// ---------------------------------------------------------------------

		// Add form elements to Group Blog form.
		add_action( 'signup_blogform', [ $this, 'signup_blogform' ] );

		/*
		 * Activate Blog-specific CommentPress Core plugin.
		 *
		 * Added at priority 20 because BuddyPress Group Blog adds its action at
		 * the default 10 and we want it to have done its stuff before we do ours.
		 */
		add_action( 'wpmu_new_blog', [ $this, 'wpmu_new_blog' ], 20, 6 );

		// Register any public styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'add_frontend_styles' ], 20 );

		// Override CommentPress Core "Create New Document" text.
		add_filter( 'cp_user_links_new_site_title', [ $this, 'user_links_new_site_title' ], 21 );
		add_filter( 'cp_site_directory_link_title', [ $this, 'user_links_new_site_title' ], 21 );
		add_filter( 'cp_register_new_site_page_title', [ $this, 'user_links_new_site_title' ], 21 );

		// Override Group Blog theme, if the bp-groupblog default theme is not a CommentPress Core one.
		add_filter( 'cp_forced_theme_slug', [ $this, 'get_groupblog_theme' ], 20, 1 );
		add_filter( 'cp_forced_theme_name', [ $this, 'get_groupblog_theme' ], 20, 1 );

		// Filter the AJAX query string to add "action".
		add_filter( 'bp_ajax_querystring', [ $this, 'groupblog_querystring' ], 20, 2 );

		// Is this the back end?
		if ( is_admin() ) {

			// Anything specifically for WordPress Admin.

			// Add our metaboxes to the Network Settings screen.
			add_filter( 'commentpress/multisite/settings/network/metaboxes/after', [ $this, 'network_admin_metaboxes' ] );

			// Add options to reset array.
			add_filter( 'cpmu_db_bp_options_get_defaults', [ $this, 'get_default_settings' ], 20, 1 );

			// Hook into Network Settings form update.
			add_action( 'commentpress/multisite/settings/network/save/before', [ $this, 'network_admin_update' ], 20 );

		} else {

			// Anything specifically for Front End.

			// Add filter options for the Post and Comment Activities as late as we can
			// so that bp-groupblog's action can be removed.
			add_action( 'bp_setup_globals', [ $this, 'groupblog_filter_options' ] );

		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Enqueue any styles and scripts needed by our public Page.
	 *
	 * @since 3.3
	 */
	public function add_frontend_styles() {

		// Dequeue BP Tempate Pack CSS, even if queued.
		wp_dequeue_style( 'bp' );

	}

	/**
	 * Allow HTML Comments and content in Multisite Blogs.
	 *
	 * @since 3.3
	 */
	public function allow_html_content() {

		// Using publish_posts for now - means author+.
		if ( current_user_can( 'publish_posts' ) ) {

			/*
			 * Remove html filtering on content.
			 *
			 * Note - this has possible consequences.
			 *
			 * @see http://wordpress.org/extend/plugins/unfiltered-mu/
			 */
			kses_remove_filters();

		}

	}

	/**
	 * Allow HTML in Activity items.
	 *
	 * @since 3.3
	 *
	 * @param array $activity_allowedtags The existing array of allowed tags.
	 * @return array $activity_allowedtags The modified array of allowed tags.
	 */
	public function activity_allowed_tags( $activity_allowedtags ) {

		// Pretty pointless not to allow p tags when we encourage the use of TinyMCE!
		$activity_allowedtags['p'] = [];

		// --<
		return $activity_allowedtags;

	}

	/**
	 * Add capability to edit own Comments.
	 *
	 * @see: http://scribu.net/wordpress/prevent-blog-authors-from-editing-comments.html
	 *
	 * @since 3.3
	 *
	 * @param array $caps The existing capabilities array for the WordPress User.
	 * @param str $cap The capability in question.
	 * @param int $user_id The numeric ID of the WordPress User.
	 * @param array $args The additional arguments.
	 * @return array $caps The modified capabilities array for the WordPress User.
	 */
	public function enable_comment_editing( $caps, $cap, $user_id, $args ) {

		// Only apply this to queries for "edit_comment" cap.
		if ( 'edit_comment' == $cap ) {

			// Get Comment.
			$comment = get_comment( $args[0] );

			// Is the User the same as the Comment Author?
			if ( $comment->user_id == $user_id ) {

				//$caps[] = 'moderate_comments';
				$caps = [ 'edit_posts' ];

			}

		}

		// --<
		return $caps;

	}

	/**
	 * Override capability to comment based on Group Membership.
	 *
	 * @since 3.3
	 *
	 * @param bool $approved True if the Comment is approved, false otherwise.
	 * @param array $commentdata The Comment data.
	 * @return bool $approved Modified approval value. True if the Comment is approved, false otherwise.
	 */
	public function pre_comment_approved( $approved, $commentdata ) {

		// Do we have Group Blogs?
		if ( function_exists( 'get_groupblog_group_id' ) ) {

			// Get current Blog ID.
			$blog_id = get_current_blog_id();

			// Check if this Blog is a Group Blog.
			$group_id = get_groupblog_group_id( $blog_id );

			// When this Blog is a Group Blog.
			if ( isset( $group_id ) && is_numeric( $group_id ) && $group_id > 0 ) {

				// Is this User a Member?
				if ( groups_is_user_member( $commentdata['user_id'], $group_id ) ) {

					// Allow un-moderated commenting.
					return 1;

				}

			}

		}

		// Pass through.
		return $approved;

	}

	/*
	// Methods kept for possible inclusion.
	public function my_check_comment( $commentdata ) {

		// Get the User ID of the Comment Author.
		$user_id = absint( $commentdata['user_ID'] );

		// If Comment Author is a registered User, approve the Comment.
		if ( 0 < $user_id ) {
			add_filter( 'pre_comment_approved', 'my_approve_comment' );
		} else {
			add_filter( 'pre_comment_approved', 'my_moderate_comment' );
		}

		return $commentdata;
	}

	public function my_approve_comment( $approved ) {
		$approved = 1;
		return $approved;
	}

	public function my_moderate_comment( $approved ) {
		if ( 'spam' !== $approved ) {
			$approved = 0;
		}
		return $approved;
	}
	*/

	/**
	 * Add "page" to the Post Types that BuddyPress records published Activity for.
	 *
	 * @since 3.3
	 *
	 * @param array $post_types The existing array of Post Types.
	 * @return array $post_types The modified array of Post Types.
	 */
	public function record_published_pages( $post_types ) {

		// If not in the array already.
		if ( ! in_array( 'page', $post_types ) ) {

			// Add "page" Post Type.
			$post_types[] = 'page';

		}

		// --<
		return $post_types;

	}

	/**
	 * Register "page" as a Post Type that BuddyPress records Comment Activity for.
	 *
	 * @since 3.9.3
	 */
	public function register_comment_tracking_on_pages() {

		// Bail if Activity Component is not active.
		if ( ! function_exists( 'bp_activity_set_post_type_tracking_args' ) ) {
			return;
		}

		// Amend "page" Post Type.
		add_post_type_support( 'page', 'buddypress-activity' );

		// Define tracking args.
		bp_activity_set_post_type_tracking_args( 'page', [
			'action_id' => 'new_page',
			'bp_activity_admin_filter' => __( 'Published a new page', 'commentpress-core' ),
			'bp_activity_front_filter' => __( 'Pages', 'commentpress-core' ),
			'bp_activity_new_post' => __( '%1$s posted a new <a href="%2$s">page</a>', 'commentpress-core' ),
			'bp_activity_new_post_ms' => __( '%1$s posted a new <a href="%2$s">page</a>, on the site %3$s', 'commentpress-core' ),
			'contexts' => [ 'activity', 'member' ],
			'comment_action_id' => 'new_blog_comment',
			'bp_activity_comments_admin_filter' => __( 'Commented on a page', 'commentpress-core' ),
			'bp_activity_comments_front_filter' => __( 'Comments', 'commentpress-core' ),
			'bp_activity_new_comment' => __( '%1$s commented on the <a href="%2$s">page</a>', 'commentpress-core' ),
			'bp_activity_new_comment_ms' => __( '%1$s commented on the <a href="%2$s">page</a>, on the site %3$s', 'commentpress-core' ),
			'position' => 100,
		] );

	}

	/**
	 * Add "page" to the Post Types that BuddyPress records Comment Activity for.
	 *
	 * @since 3.3
	 *
	 * @param array $post_types The existing array of Post Types.
	 * @return array $post_types The modified array of Post Types.
	 */
	public function record_comments_on_pages( $post_types ) {

		// If not in the array already.
		if ( ! in_array( 'page', $post_types ) ) {

			// Add "page" Post Type.
			$post_types[] = 'page';

		}

		// --<
		return $post_types;

	}

	/**
	 * Override "publicness" of Group Blogs so that we can set the hide_sitewide
	 * property of the Activity item (post or comment) depending on the Group's
	 * setting.
	 *
	 * Do we want to test if they are CommentPress Core-enabled?
	 *
	 * @since 3.3
	 *
	 * @param bool $blog_public_option True if Blog is public, false otherwise.
	 * @return bool $blog_public_option True if Blog is public, false otherwise.
	 */
	public function is_blog_public( $blog_public_option ) {

		// Do we have Group Blogs?
		if ( function_exists( 'get_groupblog_group_id' ) ) {

			// Get current Blog ID.
			$blog_id = get_current_blog_id();

			// Check if this Blog is a Group Blog.
			$group_id = get_groupblog_group_id( $blog_id );

			// When this Blog is a Group Blog.
			if ( isset( $group_id ) && is_numeric( $group_id ) && $group_id > 0 ) {

				// Always true - so that Activities are registered.
				return 1;

			}

		}

		// Fallback.
		return $blog_public_option;

	}

	/**
	 * Disable Comment sync because parent Activity items may not be in the same
	 * Group as the Comment. Furthermore, CommentPress Core Comments should be
	 * read in context rather than appearing as if globally attached to the Post
	 * or Page.
	 *
	 * @since 3.3
	 *
	 * @param bool $is_disabled The BuddyPress setting that determines blogforum sync.
	 * @return bool $is_disabled The modified value that determines blogforum sync.
	 */
	public function disable_blogforum_comments( $is_disabled ) {

		// Don't mess with admin.
		if ( is_admin() ) {
			return $is_disabled;
		}

		// Get current Blog ID.
		$blog_id = get_current_blog_id();

		// If it's CommentPress Core-enabled, disable sync.
		if ( $this->multisite->site->is_commentpress( $blog_id ) ) {
			return 1;
		}

		// Pass through.
		return $is_disabled;

	}

	/**
	 * Record the Blog Activity for the Group.
	 *
	 * Amended from bp_groupblog_set_group_to_post_activity()
	 *
	 * @since 3.3
	 *
	 * @param object $activity The existing Activity object.
	 * @return object $activity The modified Activity object.
	 */
	public function group_custom_comment_activity( $activity ) {

		// Only deal with Comments.
		if ( ( $activity->type != 'new_blog_comment' ) ) {
			return;
		}

		// Init vars.
		$is_groupblog = false;
		$is_groupsite = false;

		// Get Group Blog status.
		$is_groupblog = $this->is_commentpress_groupblog();

		// If on a CommentPress Core-enabled Group Blog.
		if ( $is_groupblog ) {

			// Which Blog?
			$blog_id = $activity->item_id;

			// Get the Group ID.
			$group_id = get_groupblog_group_id( $blog_id );

			// Kick out if not Group Blog.
			if ( empty( $group_id ) ) {
				return $activity;
			}

			// Set Activity Type.
			$type = 'new_groupblog_comment';

		} else {

			// Get Group Site status.
			$is_groupsite = $this->is_commentpress_groupsite();

			// If on a CommentPress Core-enabled Group Site.
			if ( $is_groupsite ) {

				// Get Group ID from POST.
				global $bp_groupsites;
				$group_id = $bp_groupsites->activity->get_group_id_from_comment_form();

				// Kick out if not a Comment in a Group.
				if ( false === $group_id ) {
					return $activity;
				}

				// Set Activity Type.
				$type = 'new_groupsite_comment';

			}

		}

		// Sanity check.
		if ( ! $is_groupblog && ! $is_groupsite ) {
			return $activity;
		}

		// Okay, let's get the Group object.
		$group = groups_get_group( [ 'group_id' => $group_id ] );

		// See if we already have the modified Activity for this Blog Post.
		$id = bp_activity_get_activity_id( [
			'user_id' => $activity->user_id,
			'type' => $type,
			'item_id' => $group_id,
			'secondary_item_id' => $activity->secondary_item_id,
		] );

		// If we don't find a modified item.
		if ( ! $id ) {

			// See if we have an unmodified Activity item.
			$id = bp_activity_get_activity_id( [
				'user_id' => $activity->user_id,
				'type' => $activity->type,
				'item_id' => $activity->item_id,
				'secondary_item_id' => $activity->secondary_item_id,
			] );

		}

		// If we found an Activity for this Blog Comment then overwrite that to avoid having
		// multiple Activities for every Blog Comment edit.
		if ( $id ) {
			$activity->id = $id;
		}

		// Get the Comment.
		$comment = get_comment( $activity->secondary_item_id );

		// Get the Post.
		$post = get_post( $comment->comment_post_ID );

		// Was it a registered User?
		if ( $comment->user_id != '0' ) {

			// Get User details.
			$user = get_userdata( $comment->user_id );

			// Construct User link.
			$user_link = bp_core_get_userlink( $activity->user_id );

		} else {

			// Show anonymous User.
			$user_link = '<span class="anon-commenter">' . __( 'Anonymous', 'commentpress-core' ) . '</span>';

		}

		// If on a CommentPress Core-enabled Group Blog.
		if ( $is_groupblog ) {

			/**
			 * Filters the name of the Activity item.
			 *
			 * @since 3.3
			 *
			 * @param string The default name of the Activity item.
			 */
			$activity_name = apply_filters( 'cp_activity_post_name', __( 'post', 'commentpress-core' ) );

		}

		// If on a CommentPress Core-enabled Group Site.
		if ( $is_groupsite ) {

			/**
			 * Respect BP Group Sites filter for the name of the Activity item.
			 *
			 * @since 3.3
			 *
			 * @param string The default name of the Activity item.
			 * @param WP_Post The WordPress Post object.
			 */
			$activity_name = apply_filters( 'bpgsites_activity_post_name', __( 'post', 'commentpress-core' ), $post );

		}

		// Set key.
		$key = '_cp_comment_page';

		// If the custom field has a value, we have a Sub-page Comment.
		if ( get_comment_meta( $comment->comment_ID, $key, true ) != '' ) {

			// Get comment's Page from meta.
			$page_num = get_comment_meta( $comment->comment_ID, $key, true );

			// Get the url for the Comment.
			$link = commentpress_get_post_multipage_url( $page_num, $post ) . '#comment-' . $comment->comment_ID;

			// Amend the primary link.
			$activity->primary_link = $link;

			// Init target link.
			$target_post_link = '<a href="' . commentpress_get_post_multipage_url( $page_num, $post ) . '">' .
				esc_html( $post->post_title ) .
			'</a>';

		} else {

			// Init target link.
			$target_post_link = '<a href="' . get_permalink( $post->ID ) . '">' .
				esc_html( $post->post_title ) .
			'</a>';

		}

		// Construct links.
		$comment_link = '<a href="' . $activity->primary_link . '">' . __( 'comment', 'commentpress-core' ) . '</a>';
		$group_link = '<a href="' . bp_get_group_permalink( $group ) . '">' . esc_html( $group->name ) . '</a>';

		// Replace the necessary values to display in Group Activity stream.
		$activity->action = sprintf(
			__( '%1$s left a %2$s on a %3$s %4$s in the group %5$s:', 'commentpress-core' ),
			$user_link,
			$comment_link,
			$activity_name,
			$target_post_link,
			$group_link
		);

		/**
		 * Filters the Activity action.
		 *
		 * @since 3.3
		 *
		 * @param string $action The Activity action.
		 * @param object $activity The Activity object.
		 * @param string $user_link The User link element.
		 * @param string $comment_link The User link element.
		 * @param string $activity_name The name of the Activity.
		 * @param string $target_post_link The Target Post link element.
		 * @param string $group_link The Group link element.
		 */
		$activity->action = apply_filters( 'commentpress_comment_activity_action', $activity->action, $activity, $user_link, $comment_link, $activity_name, $target_post_link, $group_link );

		// Apply Group ID.
		$activity->item_id = (int) $group_id;

		// Change to Groups component.
		$activity->component = 'groups';

		/*
		 * Having marked all Group Blogs as public, we need to hide Activity from
		 * them if the Group is private or hidden, so they don't show up in sitewide
		 * Activity feeds.
		 */
		if ( 'public' != $group->status ) {
			$activity->hide_sitewide = true;
		} else {
			$activity->hide_sitewide = false;
		}

		// Set unique type.
		$activity->type = $type;

		// Note: BuddyPress seemingly runs content through wp_filter_kses. Sad face.

		// Prevent from firing again.
		remove_action( 'bp_activity_before_save', [ $this, 'group_custom_comment_activity' ] );

		// --<
		return $activity;

	}

	/**
	 * Add some meta for the Activity item - bp_activity_after_save doesn't seem to fire.
	 *
	 * @since 3.3
	 *
	 * @param object $activity The existing Activity object.
	 * @return object $activity The modified Activity object.
	 */
	public function groupblog_custom_comment_meta( $activity ) {

		// Only deal with Comments.
		if ( ( $activity->type != 'new_groupblog_comment' ) ) {
			return $activity;
		}

		// Only do this on CommentPress Core-enabled Group Blogs.
		if ( ( false === $this->is_commentpress_groupblog() ) ) {
			return $activity;
		}

		// Set a meta value for the Blog Type of the Post.
		$meta_value = $this->get_groupblog_type();
		$result = bp_activity_update_meta( $activity->id, 'groupblogtype', 'groupblogtype-' . $meta_value );

		// Prevent from firing again.
		remove_action( 'bp_activity_after_save', [ $this, 'groupblog_custom_comment_meta' ] );

		// --<
		return $activity;

	}

	/**
	 * Record the Blog Post Activity for the Group.
	 *
	 * Adapted from code by Luiz Armesto.
	 *
	 * Since the updates to BP Group Blog, a second argument is passed to this
	 * method which, if present, means that we don't need to check for an
	 * existing Activity item. This code needs to be streamlined in the light
	 * of the changes.
	 *
	 * @see bp_groupblog_set_group_to_post_activity( $activity )
	 *
	 * @since 3.3
	 *
	 * @param object $activity The existing Activity object.
	 * @param array $args {
	 *     Optional. Handy if you've already parsed the Blog Post and Group ID.
	 *     @type WP_Post $post The WordPress Post object.
	 *     @type int $group_id The Group ID.
	 * }
	 * @return object $activity The modified Activity object.
	 */
	public function groupblog_custom_post_activity( $activity, $args = [] ) {

		// Sanity check.
		if ( ! bp_is_active( 'groups' ) ) {
			return $activity;
		}

		// Only on new Blog Posts.
		if ( ( $activity->type != 'new_blog_post' ) ) {
			return $activity;
		}

		// Only on CommentPress Core-enabled Group Blogs.
		if ( ( false === $this->is_commentpress_groupblog() ) ) {
			return $activity;
		}

		// Clarify data.
		$blog_id = $activity->item_id;
		$post_id = $activity->secondary_item_id;
		$post = get_post( $post_id );

		// Get Group ID.
		$group_id = get_groupblog_group_id( $blog_id );
		if ( empty( $group_id ) ) {
			return $activity;
		}

		// Get Group.
		$group = groups_get_group( [ 'group_id' => $group_id ] );

		// See if we already have the modified Activity for this Blog Post.
		$id = bp_activity_get_activity_id( [
			'user_id' => $activity->user_id,
			'type' => 'new_groupblog_post',
			'item_id' => $group_id,
			'secondary_item_id' => $activity->secondary_item_id,
		] );

		// If we don't find a modified item.
		if ( ! $id ) {

			// See if we have an unmodified Activity item.
			$id = bp_activity_get_activity_id( [
				'user_id' => $activity->user_id,
				'type' => $activity->type,
				'item_id' => $activity->item_id,
				'secondary_item_id' => $activity->secondary_item_id,
			] );

		}

		// If we found an Activity for this Blog Post then overwrite that to avoid
		// having multiple Activities for every Blog Post edit.
		if ( $id ) {
			$activity->id = $id;
		}

		/**
		 * Filters the name of the Activity item.
		 *
		 * @since 3.3
		 *
		 * @param string The name of the Activity item.
		 */
		$activity_name = apply_filters( 'cp_activity_post_name', __( 'post', 'commentpress-core' ) );

		// Default to standard BuddyPress author.
		$activity_author = bp_core_get_userlink( $post->post_author );

		// Compat with Co-Authors Plus.
		if ( function_exists( 'get_coauthors' ) ) {

			// Get multiple authors.
			$authors = get_coauthors();

			// If we get some.
			if ( ! empty( $authors ) ) {

				// We only want to override if we have more than one.
				if ( count( $authors ) > 1 ) {

					// Use the Co-Authors format of "name, name, name and name".
					$activity_author = '';

					// Init counter.
					$n = 1;

					// Find out how many author we have.
					$author_count = count( $authors );

					// Loop.
					foreach ( $authors as $author ) {

						// Default to comma.
						$sep = ', ';

						// If we're on the penultimate.
						if ( $n == ( $author_count - 1 ) ) {

							// Use ampersand.
							$sep = __( ' &amp; ', 'commentpress-core' );

						}

						// If we're on the last, don't add.
						if ( $n == $author_count ) {
							$sep = '';
						}

						// Add name.
						$activity_author .= bp_core_get_userlink( $author->ID );

						// Add separator.
						$activity_author .= $sep;

						// Increment.
						$n++;

					}

				}

			}

		}

		// If we're replacing an item, show different message.
		if ( $id ) {

			// Replace the necessary values to display in Group Activity stream.
			$activity->action = sprintf(
				__( '%1$s updated a %2$s %3$s in the group %4$s:', 'commentpress-core' ),
				$activity_author,
				$activity_name,
				'<a href="' . get_permalink( $post->ID ) . '">' . esc_attr( $post->post_title ) . '</a>',
				'<a href="' . bp_get_group_permalink( $group ) . '">' . esc_attr( $group->name ) . '</a>'
			);

		} else {

			// Replace the necessary values to display in Group Activity stream.
			$activity->action = sprintf(
				__( '%1$s wrote a new %2$s %3$s in the group %4$s:', 'commentpress-core' ),
				$activity_author,
				$activity_name,
				'<a href="' . get_permalink( $post->ID ) . '">' . esc_attr( $post->post_title ) . '</a>',
				'<a href="' . bp_get_group_permalink( $group ) . '">' . esc_attr( $group->name ) . '</a>'
			);

		}

		$activity->item_id = (int) $group_id;
		$activity->component = 'groups';

		// Having marked all Group Blogs as public, we need to hide Activity from them if the Group is private
		// or hidden, so they don't show up in sitewide Activity feeds.
		if ( 'public' != $group->status ) {
			$activity->hide_sitewide = true;
		} else {
			$activity->hide_sitewide = false;
		}

		// CMW: assume "groupblog_post" is intended.
		$activity->type = 'new_groupblog_post';

		// Prevent from firing again.
		remove_action( 'bp_activity_before_save', [ $this, 'groupblog_custom_post_activity' ] );

		// Using this function outside BP's save routine requires us to manually save.
		if ( ! empty( $args['post'] ) ) {
			$activity->save();
		}

		// --<
		return $activity;

	}

	/**
	 * Detects a Post edit and modifies the Activity entry if found.
	 *
	 * This is needed for BuddyPress 2.2+. Older versions of BuddyPress continue
	 * to use the {@link bp_groupblog_set_group_to_post_activity()} function.
	 *
	 * This is copied from BP Group Blog and amended to suit.
	 *
	 * @see bp_groupblog_catch_transition_post_type_status()
	 *
	 * @since 3.8.5
	 *
	 * @param str $new_status New status for the Post.
	 * @param str $old_status Old status for the Post.
	 * @param object $post The Post data.
	 */
	public function transition_post_type_status( $new_status, $old_status, $post ) {

		// Only needed for >= BP 2.2.
		if ( ! function_exists( 'bp_activity_post_type_update' ) ) {
			return;
		}

		// Bail if not a Blog Post.
		if ( 'post' !== $post->post_type ) {
			return;
		}

		// Is this an edit?
		if ( $new_status === $old_status ) {

			// An edit of an existing Post should update the existing Activity item.
			if ( $new_status == 'publish' ) {

				// Get Group ID.
				$group_id = get_groupblog_group_id( get_current_blog_id() );

				// Get existing Activity ID.
				$id = bp_activity_get_activity_id( [
					'component'         => 'groups',
					'type'              => 'new_groupblog_post',
					'item_id'           => $group_id,
					'secondary_item_id' => $post->ID,
				] );

				// Bail if we don't have one.
				if ( empty( $id ) ) {
					return;
				}

				// Retrieve Activity item and modify some properties.
				$activity = new BP_Activity_Activity( $id );
				$activity->content = $post->post_content;
				$activity->date_recorded = bp_core_current_time();

				// We currently have to fool `$this->groupblog_custom_post_activity()`.
				$activity->type = 'new_blog_post';

				// Pass Activity to our edit function.
				$this->groupblog_custom_post_activity( $activity, [
					'group_id' => $group_id,
					'post'     => $post,
				] );

			}

		}

	}

	/**
	 * Add some meta for the Activity item. (DISABLED)
	 *
	 * @since 3.3
	 *
	 * @param object $activity The existing Activity object.
	 * @return object $activity The modified Activity object.
	 */
	public function groupblog_custom_post_meta( $activity ) {

		// Only on new Blog Posts.
		if ( ( $activity->type != 'new_groupblog_post' ) ) {
			return;
		}

		// Only on CommentPress Core-enabled Group Blogs.
		if ( ( false === $this->is_commentpress_groupblog() ) ) {
			return;
		}

		// Set a meta value for the Blog Type of the Post.
		$meta_value = $this->get_groupblog_type();
		bp_activity_update_meta( $activity->id, 'groupblogtype', 'groupblogtype-' . $meta_value );

		// --<
		return $activity;

	}

	/**
	 * Check if a Group has a CommentPress Core-enabled Group Blog.
	 *
	 * @since 3.3
	 *
	 * @param int $group_id The numeric ID of the BuddyPress Group.
	 * @return boolean True if Group has CommentPress Core Group Blog, false otherwise.
	 */
	public function group_has_commentpress_groupblog( $group_id = null ) {

		// Do we have Group Blogs enabled?
		if ( function_exists( 'get_groupblog_group_id' ) ) {

			// Did we get a specific Group passed in?
			if ( is_null( $group_id ) ) {

				// Use BuddyPress API.
				$group_id = bp_get_current_group_id();

				// Unlikely, but if we don't get one.
				if ( empty( $group_id ) ) {

					// Try and get ID from BuddyPress.
					global $bp;

					if ( isset( $bp->groups->current_group->id ) ) {
						$group_id = $bp->groups->current_group->id;
					}

				}

			}

			// Yes, is this Blog a Group Blog?
			if ( ! empty( $group_id ) && is_numeric( $group_id ) && $group_id > 0 ) {

				// Is it CommentPress Core-enabled?

				// Get Group Blog Type.
				$groupblog_type = groups_get_groupmeta( $group_id, 'groupblogtype' );

				// Did we get one?
				if ( $groupblog_type ) {

					// Yes.
					return true;

				}

			}

		}

		// --<
		return false;

	}

	/**
	 * Add a filter option to the filter select box on Group Activity Pages.
	 *
	 * @since 3.3
	 */
	public function groupblog_comments_filter_option() {

		// Default name.
		$comment_name = __( 'CommentPress Comments', 'commentpress-core' );

		/**
		 * Filters the name of the option.
		 *
		 * @since 3.3
		 *
		 * @param string $comment_name The name of the option.
		 */
		$comment_name = apply_filters( 'cp_groupblog_comment_name', $comment_name );

		// Construct option.
		$option = '<option value="new_groupblog_comment">' . $comment_name . '</option>' . "\n";

		// Print.
		echo $option;

	}

	/**
	 * Override the name of the filter item.
	 *
	 * @since 3.3
	 */
	public function groupblog_posts_filter_option() {

		// Default name.
		$name = __( 'CommentPress Posts', 'commentpress-core' );

		/**
		 * Filters the name of the option.
		 *
		 * @since 3.3
		 *
		 * @param string $comment_name The name of the option.
		 */
		$name = apply_filters( 'cp_groupblog_post_name', $name );

		// Construct option.
		$option = '<option value="new_groupblog_post">' . $name . '</option>' . "\n";

		// Print.
		echo $option;

	}

	/**
	 * For Group Blogs, override the avatar with that of the Group.
	 *
	 * @since 3.3
	 *
	 * @param str $avatar The existing HTML for displaying an avatar.
	 * @param int $blog_id The numeric ID of the WordPress Blog.
	 * @param array $args Additional arguments.
	 * @return str $avatar The modified HTML for displaying an avatar.
	 */
	public function get_blog_avatar( $avatar, $blog_id = '', $args ) {

		// Do we have Group Blogs?
		if ( function_exists( 'get_groupblog_group_id' ) ) {

			// Get the Group ID.
			$group_id = get_groupblog_group_id( $blog_id );

		}

		// Did we get a Group for which this is the Group Blog?
		if ( isset( $group_id ) && is_numeric( $group_id ) && $group_id > 0 ) {

			// --<
			return bp_core_fetch_avatar( [
				'item_id' => $group_id,
				'object' => 'group',
			] );

		} else {

			// --<
			return $avatar;

		}

	}

	/**
	 * Override the name of the sub-nav item.
	 *
	 * @since 3.3
	 *
	 * @param str $name The existing name of a "blog".
	 * @return str $name The modified name of a "blog".
	 */
	public function filter_blog_name( $name ) {

		// Get Group Blog Type.
		$groupblog_type = groups_get_groupmeta( bp_get_current_group_id(), 'groupblogtype' );

		// Did we get one?
		if ( $groupblog_type ) {

			/**
			 * Filters the name of a CommentPress-enabled the Group Blog.
			 *
			 * @since 3.3
			 *
			 * @param string The default name of the Group Blog.
			 */
			return apply_filters( 'cpmu_bp_groupblog_subnav_item_name', __( 'Document', 'commentpress-core' ) );

		}

		// --<
		return $name;

	}

	/**
	 * Override the slug of the sub-nav item.
	 *
	 * @since 3.3
	 *
	 * @param str $slug The existing slug of a "blog".
	 * @return str $slug The modified slug of a "blog".
	 */
	public function filter_blog_slug( $slug ) {

		// Get Group Blog Type.
		$groupblog_type = groups_get_groupmeta( bp_get_current_group_id(), 'groupblogtype' );

		// Did we get one?
		if ( $groupblog_type ) {

			/**
			 * Filters the slug of a CommentPress-enabled the Group Blog.
			 *
			 * @since 3.3
			 *
			 * @param string The default slug of the Group Blog.
			 */
			return apply_filters( 'cpmu_bp_groupblog_subnav_item_slug', 'document' );

		}

		// --<
		return $slug;

	}

	/**
	 * Override CommentPress Core "Welcome Page".
	 *
	 * @since 3.3
	 *
	 * @param str $title The existing title of a "blog".
	 * @return str $title The modified title of a "blog".
	 */
	public function filter_nav_title_page_title( $title ) {

		// Bail if main BuddyPress Site.
		if ( bp_is_root_blog() ) {
			return $title;
		}

		/**
		 * Filters the link name of a CommentPress-enabled the Group Blog.
		 *
		 * @since 3.3
		 *
		 * @param string The default link name of the Group Blog.
		 */
		return apply_filters( 'cpmu_bp_nav_title_page_title', __( 'Document Home Page', 'commentpress-core' ) );

	}

	/**
	 * Remove Group Blogs from Blog list.
	 *
	 * @since 3.3
	 *
	 * @param bool $b True if there are Blogs, false otherwise.
	 * @param object $blogs The existing Blogs object.
	 * @return object $blogs The modified Blogs object.
	 */
	public function remove_groupblog_from_loop( $b, $blogs ) {

		// Loop through them.
		foreach ( $blogs->blogs as $key => $blog ) {

			// Exclude if it's a Group Blog.
			if ( function_exists( 'groupblog_group_id' ) ) {

				// Get Group ID.
				$group_id = get_groupblog_group_id( $blog->blog_id );

				// Did we get one?
				if ( isset( $group_id ) && is_numeric( $group_id ) && $group_id > 0 ) {

					// Exclude.
					unset( $blogs->blogs[ $key ] );

					// Recalculate global values.
					$blogs->blog_count = $blogs->blog_count - 1;
					$blogs->total_blog_count = $blogs->total_blog_count - 1;
					$blogs->pag_num = $blogs->pag_num - 1;

				}

			}

		}

		// Renumber the array keys to account for missing items.
		$blogs_new = array_values( $blogs->blogs );
		$blogs->blogs = $blogs_new;

		// --<
		return $blogs;

	}

	/**
	 * Override the name of the button on the BuddyPress "blogs" screen.
	 *
	 * @since 3.3
	 *
	 * @param array $button The existing Blogs button data.
	 * @return array $button The existing Blogs button data.
	 */
	public function get_blogs_visit_blog_button( $button ) {

		/*
		[id] => visit_blog
		[component] => blogs
		[must_be_logged_in] =>
		[block_self] =>
		[wrapper_class] => blog-button visit
		[link_href] => http://domain/site-slug/
		[link_class] => blog-button visit
		[link_text] => Visit Site
		[link_title] => Visit Site
		*/

		// Init.
		$blogtype = 'blog';

		// Access global.
		global $blogs_template;

		// Do we have Group Blogs enabled?
		if ( function_exists( 'get_groupblog_group_id' ) ) {

			// Get Group ID.
			$group_id = get_groupblog_group_id( $blogs_template->blog->blog_id );

			// Yes, is this Blog a Group Blog?
			if ( isset( $group_id ) && is_numeric( $group_id ) && $group_id > 0 ) {

				// Is it CommentPress Core-enabled?

				// Get Group Blog Type.
				$groupblog_type = groups_get_groupmeta( $group_id, 'groupblogtype' );

				// Did we get one?
				if ( $groupblog_type ) {

					// Yes.
					$blogtype = 'commentpress-groupblog';

				} else {

					// Standard Group Blog.
					$blogtype = 'groupblog';

				}

			}

		} else {

			// TODO: is this Blog CommentPress Core-enabled?
			// We cannot do this without switch_to_blog at the moment.
			$blogtype = 'blog';

		}

		// Switch by Blog Type.
		switch ( $blogtype ) {

			// Standard sub-site.
			case 'blog':
				$label = __( 'View Site', 'commentpress-core' );
				$button['link_text'] = $label;
				$button['link_title'] = $label;
				break;

			// CommentPress Core sub-site.
			case 'commentpress':
				$label = __( 'View Document', 'commentpress-core' );
				$button['link_text'] = apply_filters( 'cp_get_blogs_visit_blog_button', $label );
				$button['link_title'] = apply_filters( 'cp_get_blogs_visit_blog_button', $label );
				break;

			// Standard Group Blog.
			case 'groupblog':
				$label = __( 'View Group Blog', 'commentpress-core' );
				$button['link_text'] = $label;
				$button['link_title'] = $label;
				break;

			// CommentPress Core sub-site.
			case 'commentpress-groupblog':
				$label = __( 'View Document', 'commentpress-core' );
				$button['link_text'] = apply_filters( 'cp_get_blogs_visit_groupblog_button', $label );
				$button['link_title'] = apply_filters( 'cp_get_blogs_visit_groupblog_button', $label );
				break;

		}

		// --<
		return $button;

	}

	/**
	 * Override the name of the Blog Types dropdown label.
	 *
	 * @since 3.3
	 *
	 * @param str $name The existing name of the label.
	 * @return str $name The modified name of the label.
	 */
	public function blog_type_label( $name ) {
		return __( 'Default Text Format', 'commentpress-core' );
	}

	/**
	 * Define the "types" of Blog.
	 *
	 * @since 3.3
	 *
	 * @param array $existing_options The existing types of Blog.
	 * @return array $existing_options The modified types of Blog.
	 */
	public function blog_type_options( $existing_options ) {

		// Define types.
		$types = [
			__( 'Prose', 'commentpress-core' ), // Types[0].
			__( 'Poetry', 'commentpress-core' ), // Types[1].
		];

		/**
		 * Filters the Blog Types.
		 *
		 * @since 3.3.1
		 *
		 * @param array $types The array of Blog Type.
		 */
		return apply_filters( 'cp_class_commentpress_formatter_types', $types );

	}

	/**
	 * Hook into the Group Blog signup form.
	 *
	 * @since 3.3
	 *
	 * @param array $errors The errors generated previously.
	 */
	public function signup_blogform( $errors ) {

		// Apply to Group Blog signup form?
		if ( bp_is_groups_component() ) {

			// Hand off to private method.
			$this->create_groupblog_options();

		} else {

			// Hand off to private method.
			$this->create_blog_options();

		}

	}

	/**
	 * Hook into wpmu_new_blog and target plugins to be activated.
	 *
	 * @since 3.3
	 *
	 * @param int $blog_id The numeric ID of the WordPress Blog.
	 * @param int $user_id The numeric ID of the WordPress User.
	 * @param str $domain The domain of the WordPress Blog.
	 * @param str $path The path of the WordPress Blog.
	 * @param int $site_id The numeric ID of the WordPress parent Site.
	 * @param array $meta The meta data of the WordPress Blog.
	 */
	public function wpmu_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		// Test for presence of our checkbox variable in _POST.
		if ( isset( $_POST['cpbp-groupblog'] ) && $_POST['cpbp-groupblog'] == '1' ) {

			// Hand off to private method.
			$this->create_groupblog( $blog_id, $user_id, $domain, $path, $site_id, $meta );

		} else {

			// Test for presence of our checkbox variable in _POST.
			if ( isset( $_POST['cpbp-new-blog'] ) && $_POST['cpbp-new-blog'] == '1' ) {

				// Hand off to private method.
				$this->create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta );

			}

		}

	}

	/**
	 * Override the title of the "Create a new document" link.
	 *
	 * @since 3.3
	 *
	 * @return str Ther overridden name of the link.
	 */
	public function user_links_new_site_title() {

		/**
		 * Filters the title of the "Create a new document" link.
		 *
		 * @since 3.3
		 *
		 * @param string The default title of the "Create a new document" link.
		 */
		return apply_filters( 'cpmu_bp_create_new_site_title', __( 'Create a New Site', 'commentpress-core' ) );

	}

	/**
	 * Check if a non-public Group is being accessed by a User who is not a
	 * Member of the Group.
	 *
	 * Adapted from code in mahype's fork of BuddyPress Group Blog plugin, but not
	 * accepted because there may be cases where private Groups have public
	 * Group Blogs. Ours is not such a case.
	 *
	 * @see groupblog_privacy_check()
	 *
	 * @since 3.3
	 */
	public function groupblog_privacy_check() {

		// Allow network admins through regardless.
		if ( is_super_admin() ) {
			return;
		}

		// Check our Site option.
		if ( $this->multisite->db->setting_get( 'cpmu_bp_groupblog_privacy' ) != '1' ) {
			return;
		}

		global $blog_id, $current_user;

		// If is not the main Blog but we do have a Blog ID.
		if ( ! is_main_site() && isset( $blog_id ) && is_numeric( $blog_id ) ) {

			// Do we have Group Blog active?
			if ( function_exists( 'get_groupblog_group_id' ) ) {

				// Get Group ID for this Blog.
				$group_id = get_groupblog_group_id( $blog_id );

				// If we get one.
				if ( isset( $group_id ) && is_numeric( $group_id ) && $group_id > 0 ) {

					// Get the Group object.
					$group = new BP_Groups_Group( $group_id );

					// If Group is not public.
					if ( $group->status != 'public' ) {

						// Is the Group Blog CommentPress Core enabled?
						if ( $this->group_has_commentpress_groupblog( $group->id ) ) {

							// Is the current User a Member of the Blog?
							if ( ! is_user_member_of_blog( $current_user->ID, $blog_id ) ) {

								/**
								 * Redirect to network home, but allow overrides.
								 *
								 * @since 3.4
								 *
								 * @param string The default network home URL.
								 */
								wp_safe_redirect( apply_filters( 'bp_groupblog_privacy_redirect_url', network_site_url() ) );
								exit;

							}

						}

					}

				}

			}

		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Add a filter actions once BuddyPress is loaded.
	 *
	 * @since 3.3
	 */
	public function groupblog_filter_options() {

		// Kick out if this Group does not have a CommentPress Core Group Blog.
		if ( ! $this->group_has_commentpress_groupblog() ) {
			return;
		}

		// Remove bp-groupblog's contradictory option.
		remove_action( 'bp_group_activity_filter_options', 'bp_groupblog_posts' );

		// Add our consistent one.
		add_action( 'bp_activity_filter_options', [ $this, 'groupblog_posts_filter_option' ] );
		add_action( 'bp_group_activity_filter_options', [ $this, 'groupblog_posts_filter_option' ] );
		add_action( 'bp_member_activity_filter_options', [ $this, 'groupblog_posts_filter_option' ] );

		// Add our Comments.
		add_action( 'bp_activity_filter_options', [ $this, 'groupblog_comments_filter_option' ] );
		add_action( 'bp_group_activity_filter_options', [ $this, 'groupblog_comments_filter_option' ] );
		add_action( 'bp_member_activity_filter_options', [ $this, 'groupblog_comments_filter_option' ] );

	}

	/**
	 * Amend Activity methods once BuddyPress is loaded.
	 *
	 * @since 3.3
	 */
	public function group_activity_mods() {

		// Don't mess with hooks unless the Blog is CommentPress Core-enabled.
		if ( ( false === $this->is_commentpress_groupblog() ) ) {
			return;
		}

		// Allow lists in Activity content.
		add_action( 'bp_activity_allowed_tags', [ $this, 'group_activity_allowed_tags' ], 20, 1 );

		// Drop the bp-groupblog Post Activity actions.
		remove_action( 'bp_activity_before_save', 'bp_groupblog_set_group_to_post_activity' );
		remove_action( 'transition_post_status', 'bp_groupblog_catch_transition_post_type_status' );

		// Implement our own Post Activity (with Co-Authors compatibility).
		add_action( 'bp_activity_before_save', [ $this, 'groupblog_custom_post_activity' ], 20, 1 );
		add_action( 'transition_post_status', [ $this, 'transition_post_type_status' ], 20, 3 );

		// CommentPress Core needs to know the sub-page for a Comment.

		// Drop the bp-group-sites Comment Activity action, if present.
		global $bp_groupsites;
		if ( ! is_null( $bp_groupsites ) && is_object( $bp_groupsites ) ) {
			remove_action( 'bp_activity_before_save', [ $bp_groupsites->activity, 'custom_comment_activity' ] );
		}

		// Add our own custom Comment Activity.
		add_action( 'bp_activity_before_save', [ $this, 'group_custom_comment_activity' ], 20, 1 );

		/*
		// These don't seem to fire to allow us to add our meta values for the items.
		// Instead, I'm trying to store the Blog Type as Group meta data.
		add_action( 'bp_activity_after_save', [ $this, 'groupblog_custom_comment_meta' ], 20, 1 );
		add_action( 'bp_activity_after_save', [ $this, 'groupblog_custom_post_meta' ], 20, 1 );
		*/

	}

	/**
	 * Modify the AJAX query string.
	 *
	 * @since 3.9.3
	 *
	 * @param string $qs The query string for the BP loop.
	 * @param string $object The current object for the query string.
	 * @return string Modified query string.
	 */
	public function groupblog_querystring( $qs, $object ) {

		// Bail if not an Activity object.
		if ( $object != 'activity' ) {
			return $qs;
		}

		// Parse query string into an array.
		$r = wp_parse_args( $qs );

		// Bail if no type is set.
		if ( empty( $r['type'] ) ) {
			return $qs;
		}

		// Bail if not a type that we're looking for.
		if ( 'new_groupblog_post' !== $r['type'] && 'new_groupblog_comment' !== $r['type'] ) {
			return $qs;
		}

		// Add the 'new_groupblog_post' type if it doesn't exist.
		if ( 'new_groupblog_post' === $r['type'] ) {
			if ( ! isset( $r['action'] ) || false === strpos( $r['action'], 'new_groupblog_post' ) ) {
				// 'action' filters Activity items by the 'type' column.
				$r['action'] = 'new_groupblog_post';
			}
		}

		// Add the 'new_groupblog_comment' type if it doesn't exist.
		if ( 'new_groupblog_comment' === $r['type'] ) {
			if ( ! isset( $r['action'] ) || false === strpos( $r['action'], 'new_groupblog_comment' ) ) {
				// 'action' filters Activity items by the 'type' column.
				$r['action'] = 'new_groupblog_comment';
			}
		}

		// 'type' isn't used anywhere internally.
		unset( $r['type'] );

		// Return a querystring.
		return build_query( $r );

	}

	/**
	 * Allow our TinyMCE Comment markup in Activity content.
	 *
	 * @since 3.3
	 *
	 * @param array $activity_allowedtags The array of tags allowed in an Activity item.
	 * @return array $activity_allowedtags The modified array of tags allowed in an Activity item.
	 */
	public function group_activity_allowed_tags( $activity_allowedtags ) {

		// Lists.
		$activity_allowedtags['ul'] = [];
		$activity_allowedtags['ol'] = [];
		$activity_allowedtags['li'] = [];

		// Bold.
		$activity_allowedtags['strong'] = [];

		// Italic.
		$activity_allowedtags['em'] = [];

		// Underline.
		$activity_allowedtags['span']['style'] = [];

		// --<
		return $activity_allowedtags;

	}

	/**
	 * Hook into the Group Blog create screen.
	 *
	 * @since 3.3
	 */
	public function create_groupblog_options() {

		global $bp, $groupblog_create_screen;

		$blog_id = get_groupblog_blog_id();

		if ( ! $groupblog_create_screen && $blog_id != '' ) {

			// Existing Blog and Group - do we need to present any options?

		} else {

			// Creating a new Group - no Group Blog exists yet
			// NOTE: need to check that our context is right.

			// Get force option.
			$forced = $this->multisite->db->setting_get( 'cpmu_bp_force_commentpress' );

			// Are we force-enabling CommentPress Core?
			if ( $forced ) {

				// Set hidden element.
				$forced_html = '
				<input type="hidden" value="1" id="cpbp-groupblog" name="cpbp-groupblog" />
				';

				/**
				 * Filters the Signup Form text.
				 *
				 * @since 3.4
				 *
				 * @param string The default Signup Form text.
				 */
				$text = apply_filters( 'cp_groupblog_options_signup_text_forced', __( 'Select the options for your new CommentPress-enabled blog. Note: if you choose an existing blog as a group blog, setting these options will have no effect.', 'commentpress-core' ) );

			} else {

				// Set checkbox.
				$forced_html = '
				<div class="checkbox">
					<label for="cpbp-groupblog"><input type="checkbox" value="1" id="cpbp-groupblog" name="cpbp-groupblog" /> ' . __( 'Enable CommentPress', 'commentpress-core' ) . '</label>
				</div>
				';

				/**
				 * Filters the Signup Form Group Blog options text.
				 *
				 * @since 3.4
				 *
				 * @param string The default Signup Form Group Blog options text.
				 */
				$text = apply_filters( 'cp_groupblog_options_signup_text', __( 'When you create a group blog, you can choose to enable it as a CommentPress blog. This is a "one time only" option because you cannot disable CommentPress from here once the group blog is created. Note: if you choose an existing blog as a group blog, setting this option will have no effect.', 'commentpress-core' ) );

			}

			// Assume no types.
			$types = [];

			// Init output.
			$type_html = '';

			/**
			 * Build Text Format options.
			 *
			 * @since 3.3.1
			 *
			 * @param array $types Empty by default since others add them.
			 */
			$types = apply_filters( 'cp_blog_type_options', $types );

			// If we got any, use them.
			if ( ! empty( $types ) ) {

				/**
				 * Filters the Blog Type label.
				 *
				 * @since 3.3.1
				 *
				 * @param str The default Blog Type label.
				 */
				$type_label = apply_filters( 'cp_blog_type_label', __( 'Document Type', 'commentpress-core' ) );

				// Construct options.
				$type_option_list = [];
				$n = 0;
				foreach ( $types as $type ) {
					$type_option_list[] = '<option value="' . $n . '">' . $type . '</option>';
					$n++;
				}
				$type_options = implode( "\n", $type_option_list );

				// Show it.
				$type_html = '

				<div class="dropdown">
					<label for="cp_blog_type">' . $type_label . '</label> <select id="cp_blog_type" name="cp_blog_type">

					' . $type_options . '

					</select>
				</div>

				';

			}

			// Construct form.
			$form = '
			<br />
			<div id="cp-multisite-options">
				<h3>' . __( 'CommentPress Options', 'commentpress-core' ) . '</h3>
				<p>' . $text . '</p>
				' . $forced_html . '
				' . $type_html . '
			</div>

			';

			echo $form;

		}

	}

	/**
	 * Create a Blog that is a Group Blog.
	 *
	 * @since 3.3
	 *
	 * @param int $blog_id The numeric ID of the WordPress Blog.
	 * @param int $user_id The numeric ID of the WordPress User.
	 * @param str $domain The domain of the WordPress Blog.
	 * @param str $path The path of the WordPress Blog.
	 * @param int $site_id The numeric ID of the WordPress parent Site.
	 * @param array $meta The meta data of the WordPress Blog.
	 */
	public function create_groupblog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		// Get Group ID before switch.
		$group_id = isset( $_COOKIE['bp_new_group_id'] )
					? wp_unslash( $_COOKIE['bp_new_group_id'] )
					: bp_get_current_group_id();

		// Wpmu_new_blog calls this *after* restore_current_blog, so we need to do it again.
		switch_to_blog( $blog_id );

		// Activate CommentPress Core.
		$this->multisite->site->core_install();

		// Get core plugin reference.
		$core = commentpress_core();

		// TODO: create Admin Page settings for WordPress options.

		/**
		 * Filters the "Show Posts by default" option.
		 *
		 * @since 3.4
		 *
		 * @param string The default "Show Posts by default" option.
		 */
		$posts_or_pages = apply_filters( 'cp_posts_or_pages_in_toc', 'post' );
		$core->db->setting_set( 'cp_show_posts_or_pages_in_toc', $posts_or_pages );

		// If we opted for Posts.
		if ( $posts_or_pages == 'post' ) {

			/**
			 * Filters the "TOC shows extended Posts" option.
			 *
			 * @since 3.4
			 *
			 * @param bool The default "TOC shows extended Posts" option.
			 */
			$extended_toc = apply_filters( 'cp_extended_toc', 1 );
			$core->db->setting_set( 'cp_show_extended_toc', $extended_toc );

		}

		// Get Blog Type (saved already).
		$cp_blog_type = $core->db->setting_get( 'cp_blog_type' );

		// Did we get a Group ID before we switched Blogs?
		if ( isset( $group_id ) ) {

			// Set the type as Group meta info.
			// We also need to change this when the type is changed from the CommentPress Core Admin Page.
			groups_update_groupmeta( $group_id, 'groupblogtype', 'groupblogtype-' . $cp_blog_type );

		}

		// Save options.
		$core->db->settings_save();

		// ---------------------------------------------------------------------
		// WordPress Internal Configuration.
		// ---------------------------------------------------------------------

		// Get commenting option.
		$anon_comments = $this->multisite->db->setting_get( 'cpmu_bp_require_comment_registration' ) == '1' ? 1 : 0;

		/**
		 * Allow overrides for anonymous commenting.
		 *
		 * This may be overridden by an option in the Network Admin settings screen.
		 *
		 * @param bool $anon_comments Value of 1 requires registration, 0 does not
		 */
		$anon_comments = apply_filters( 'cp_require_comment_registration', $anon_comments );

		// Update WordPress option.
		update_option( 'comment_registration', $anon_comments );

		// Get all network-activated plugins.
		$active_sitewide_plugins = maybe_unserialize( get_site_option( 'active_sitewide_plugins' ) );

		// Did we get any?
		if ( is_array( $active_sitewide_plugins ) && count( $active_sitewide_plugins ) > 0 ) {

			// Loop through them.
			foreach ( $active_sitewide_plugins as $plugin_path => $plugin_data ) {

				// If we've got BuddyPress Group Email Subscription network-installed.
				if ( false !== strstr( $plugin_path, 'bp-activity-subscription.php' ) ) {

					// Switch "comments_notify" off.
					update_option( 'comments_notify', 0 );

					// No need to carry on.
					break;

				}

			}

		}

		/**
		 * Allow plugins to add their own config.
		 *
		 * @since 3.8.5
		 *
		 * @param int $blog_id The numeric ID of the WordPress Blog
		 * @param int $cp_blog_type The numeric Blog Type
		 * @param bool False since this is now deprecated.
		 */
		do_action( 'cp_new_groupblog_created', $blog_id, $cp_blog_type, false );

		// Switch back.
		restore_current_blog();

	}

	/**
	 * Hook into the Blog create screen on Registration Page.
	 *
	 * @since 3.3
	 */
	public function create_blog_options() {

		// Get force option.
		$forced = $this->multisite->db->setting_get( 'cpmu_force_commentpress' );

		// Are we force-enabling CommentPress Core?
		if ( $forced ) {

			// Set hidden element.
			$forced_html = '
			<input type="hidden" value="1" id="cpbp-new-blog" name="cpbp-new-blog" />
			';

			// Define text.
			$text = __( 'Select the options for your new CommentPress document.', 'commentpress-core' );

		} else {

			// Set checkbox.
			$forced_html = '
			<div class="checkbox">
				<label for="cpbp-new-blog"><input type="checkbox" value="1" id="cpbp-new-blog" name="cpbp-new-blog" /> ' . __( 'Enable CommentPress', 'commentpress-core' ) . '</label>
			</div>
			';

			// Define text.
			$text = __( 'Do you want to make the new site a CommentPress document?', 'commentpress-core' );

		}

		// Assume no types.
		$types = [];

		// Init output.
		$type_html = '';

		/**
		 * Build Text Format options.
		 *
		 * @since 3.3.1
		 *
		 * @param array $types Empty by default since others add them.
		 */
		$types = apply_filters( 'cp_blog_type_options', $types );

		// If we got any, use them.
		if ( ! empty( $types ) ) {

			/**
			 * Filters the Blog Type label.
			 *
			 * @since 3.3.1
			 *
			 * @param str The default Blog Type label.
			 */
			$type_label = apply_filters( 'cp_blog_type_label', __( 'Document Type', 'commentpress-core' ) );

			// Construct options.
			$type_option_list = [];
			$n = 0;
			foreach ( $types as $type ) {
				$type_option_list[] = '<option value="' . $n . '">' . $type . '</option>';
				$n++;
			}
			$type_options = implode( "\n", $type_option_list );

			// Show it.
			$type_html = '

			<div class="dropdown cp-blog-type">
				<label for="cp_blog_type">' . $type_label . '</label> <select id="cp_blog_type" name="cp_blog_type">

				' . $type_options . '

				</select>
			</div>

			';

		}

		// Construct form.
		$form = '

		<br />
		<div id="cp-multisite-options">

			<h4>' . __( 'CommentPress Options', 'commentpress-core' ) . '</h4>

			<p>' . $text . '</p>

			' . $forced_html . '

			' . $type_html . '

		</div>

		';

		echo $form;

	}

	/**
	 * Create a Blog that is not a Group Blog.
	 *
	 * @since 3.3
	 *
	 * @param int $blog_id The numeric ID of the WordPress Blog.
	 * @param int $user_id The numeric ID of the WordPress User.
	 * @param str $domain The domain of the WordPress Blog.
	 * @param str $path The path of the WordPress Blog.
	 * @param int $site_id The numeric ID of the WordPress parent Site.
	 * @param array $meta The meta data of the WordPress Blog.
	 */
	public function create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		// Wpmu_new_blog calls this *after* restore_current_blog, so we need to do it again.
		switch_to_blog( $blog_id );

		// Activate CommentPress Core.
		$this->multisite->site->core_install();

		// Switch back.
		restore_current_blog();

	}

	/**
	 * Utility to wrap is_groupblog().
	 *
	 * Note that this only tests the current Blog and cannot be used to discover
	 * if a specific Blog is a CommentPress Core Group Blog.
	 *
	 * Also note that this method only functions after 'bp_setup_globals' has
	 * fired with priority 1000.
	 *
	 * @since 3.3
	 *
	 * @return bool True if current Blog is CommentPress Core-enabled, false otherwise.
	 */
	public function is_commentpress_groupblog() {

		// Get core plugin reference.
		$core = commentpress_core();

		// Check if this Blog is a CommentPress Core Group Blog.
		if ( ! empty( $core ) && $core->bp->is_groupblog() ) {
			return true;
		}

		// --<
		return false;

	}

	/**
	 * Utility to discover if this is a BP Group Site.
	 *
	 * @since 3.8
	 *
	 * @return bool True if current Blog is a BP Group Site, false otherwise.
	 */
	public function is_commentpress_groupsite() {

		// Check if this Blog is a BP Group Site.
		if (
			function_exists( 'bpgsites_is_groupsite' ) &&
			bpgsites_is_groupsite( get_current_blog_id() )
		) {

			return true;

		}

		return false;

	}

	/**
	 * Utility to get Blog Type.
	 *
	 * @since 3.3
	 *
	 * @return mixed String if there is a Blog Type, false otherwise.
	 */
	public function get_groupblog_type() {

		// Get core plugin reference.
		$core = commentpress_core();

		// If we have the plugin.
		if ( ! empty( $core ) ) {
			return $core->db->setting_get( 'cp_blog_type' );
		}

		// --<
		return false;

	}

	// -------------------------------------------------------------------------

	/**
	 * Add our metaboxes to the Network Settings screen.
	 *
	 * @since 4.0
	 *
	 * @param string $screen_id The Network Settings Screen ID.
	 */
	public function network_admin_metaboxes( $screen_id ) {

		// Create "BuddyPress & Group Blog Settings" metabox.
		add_meta_box(
			'commentpress_buddypress',
			__( 'BuddyPress &amp; Group Blog Settings', 'commentpress-core' ),
			[ $this, 'meta_box_buddypress_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

	}

	/**
	 * Renders the "BuddyPress & Group Blog Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function meta_box_buddypress_render() {

		// Get settings.
		$bp_force_commentpress = $this->multisite->db->setting_get( 'cpmu_bp_force_commentpress' );
		$bp_groupblog_privacy = $this->multisite->db->setting_get( 'cpmu_bp_groupblog_privacy' );
		$bp_require_comment_registration = $this->multisite->db->setting_get( 'cpmu_bp_require_comment_registration' );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-network-buddypress.php';

	}

	// -------------------------------------------------------------------------

	/**
	 * Add our options to the network admin form.
	 *
	 * @since 3.3
	 */
	public function network_admin_form() {

		// Define Admin Page.
		$admin_page = '
		<div id="cpmu_bp_admin_options">

		<h3>' . __( 'BuddyPress &amp; Group Blog Settings', 'commentpress-core' ) . '</h3>

		<p>' . __( 'Configure how CommentPress interacts with BuddyPress and BuddyPress Group Blog.', 'commentpress-core' ) . '</p>

		<table class="form-table">

			<tr valign="top">
				<th scope="row"><label for="cpmu_bp_reset">' . __( 'Reset BuddyPress settings', 'commentpress-core' ) . '</label></th>
				<td><input id="cpmu_bp_reset" name="cpmu_bp_reset" value="1" type="checkbox" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="cpmu_bp_force_commentpress">' . __( 'Make all new Group Blogs CommentPress-enabled', 'commentpress-core' ) . '</label></th>
				<td><input id="cpmu_bp_force_commentpress" name="cpmu_bp_force_commentpress" value="1" type="checkbox"' . ( $this->multisite->db->setting_get( 'cpmu_bp_force_commentpress' ) == '1' ? ' checked="checked"' : '' ) . ' /></td>
			</tr>

			' . $this->get_commentpress_themes() . '

			<tr valign="top">
				<th scope="row"><label for="cpmu_bp_groupblog_privacy">' . __( 'Private Groups must have Private Group Blogs', 'commentpress-core' ) . '</label></th>
				<td><input id="cpmu_bp_groupblog_privacy" name="cpmu_bp_groupblog_privacy" value="1" type="checkbox"' . ( $this->multisite->db->setting_get( 'cpmu_bp_groupblog_privacy' ) == '1' ? ' checked="checked"' : '' ) . ' /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="cpmu_bp_require_comment_registration">' . __( 'Require user login to post comments on Group Blogs', 'commentpress-core' ) . '</label></th>
				<td><input id="cpmu_bp_require_comment_registration" name="cpmu_bp_require_comment_registration" value="1" type="checkbox"' . ( $this->multisite->db->setting_get( 'cpmu_bp_require_comment_registration' ) == '1' ? ' checked="checked"' : '' ) . ' /></td>
			</tr>

			' . $this->additional_buddypress_options() . '

		</table>

		</div>
		';

		// --<
		return $admin_page;

	}

	/**
	 * Get all CommentPress Core themes.
	 *
	 * @since 3.3
	 *
	 * @return str $element The HTML form element
	 */
	public function get_commentpress_themes() {

		// Get theme data.
		$themes = wp_get_themes(
			false,     // Only error-free themes.
			'network', // Only network-allowed themes.
			0          // Use current Blog as reference.
		);

		// Get currently selected theme.
		$current_theme = $this->multisite->db->setting_get( 'cpmu_bp_groupblog_theme' );

		// Init.
		$options = [];
		$element = '';

		// We must get *at least* one (the Default), but let's be safe.
		if ( ! empty( $themes ) ) {

			// Loop.
			foreach ( $themes as $theme ) {

				// Is it a CommentPress Core Group Blog theme?
				if (
					in_array( 'commentpress', (array) $theme['Tags'] ) &&
					in_array( 'groupblog', (array) $theme['Tags'] )
				) {

					// Use stylesheet as theme data.
					$theme_data = $theme->get_stylesheet();

					// Is it the currently selected theme?
					$selected = ( $current_theme == $theme_data ) ? ' selected="selected"' : '';

					// Add to array.
					$options[] = '<option value="' . $theme_data . '" ' . $selected . '>' . $theme['Title'] . '</option>';

				}

			}

			// Did we get any?
			if ( ! empty( $options ) ) {

				// Implode.
				$opts = implode( "\n", $options );

				// Define element.
				$element = '

				<tr valign="top">
					<th scope="row"><label for="cpmu_bp_groupblog_theme">' . __( 'Select theme for CommentPress Group Blogs', 'commentpress-core' ) . '</label></th>
					<td><select id="cpmu_bp_groupblog_theme" name="cpmu_bp_groupblog_theme">
						' . $opts . '
						</select>
					</td>
				</tr>

				';

			}

		}

		// --<
		return $element;

	}

	/**
	 * Get Group Blog theme as defined in Network Settings.
	 *
	 * @since 3.3
	 *
	 * @param str $default_theme The existing theme.
	 * @return str $theme The modified theme.
	 */
	public function get_groupblog_theme( $default_theme ) {

		// Get the theme we've defined as the default for Group Blogs.
		$theme = $this->multisite->db->setting_get( 'cpmu_bp_groupblog_theme' );

		// --<
		return $theme;

	}

	/**
	 * Get default BuddyPress-related settings.
	 *
	 * @since 3.3
	 *
	 * @param array $existing_options The existing options data array.
	 * @return array $options The modified options data array.
	 */
	public function get_default_settings( $existing_options ) {

		// Use stylesheet as theme data.
		$theme_data = $this->groupblog_theme;

		// Define BuddyPress and BuddyPress Group Blog defaults.
		$defaults = [
			'cpmu_bp_force_commentpress' => $this->force_commentpress,
			'cpmu_bp_groupblog_privacy' => $this->groupblog_privacy,
			'cpmu_bp_require_comment_registration' => $this->require_comment_registration,
			'cpmu_bp_groupblog_theme' => $theme_data,
		];

		/**
		 * Filters the default BuddyPress options.
		 *
		 * @since 3.4
		 *
		 * @param array $defaults The default BuddyPress options.
		 */
		return apply_filters( 'cpmu_buddypress_options_get_defaults', $defaults );

	}

	/**
	 * Hook into Network Settings form update.
	 *
	 * @since 3.3
	 * @since 4.0 Renamed.
	 */
	public function network_admin_update() {

		// Init.
		$cpmu_bp_force_commentpress = '0';
		$cpmu_bp_groupblog_privacy = '0';
		$cpmu_bp_require_comment_registration = '0';

		// Get variables.
		extract( $_POST );

		// Force CommentPress Core to be enabled on all Group Blogs.
		$cpmu_bp_force_commentpress = esc_sql( $cpmu_bp_force_commentpress );
		$this->multisite->db->setting_set( 'cpmu_bp_force_commentpress', ( $cpmu_bp_force_commentpress ? 1 : 0 ) );

		// Group Blog privacy synced to Group privacy.
		$cpmu_bp_groupblog_privacy = esc_sql( $cpmu_bp_groupblog_privacy );
		$this->multisite->db->setting_set( 'cpmu_bp_groupblog_privacy', ( $cpmu_bp_groupblog_privacy ? 1 : 0 ) );

		// Default Group Blog theme.
		$cpmu_bp_groupblog_theme = esc_sql( $cpmu_bp_groupblog_theme );
		$this->multisite->db->setting_set( 'cpmu_bp_groupblog_theme', $cpmu_bp_groupblog_theme );

		// Anonymous Comments on Group Blogs.
		$cpmu_bp_require_comment_registration = esc_sql( $cpmu_bp_require_comment_registration );
		$this->multisite->db->setting_set( 'cpmu_bp_require_comment_registration', ( $cpmu_bp_require_comment_registration ? 1 : 0 ) );

	}

}
