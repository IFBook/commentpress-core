<?php

/**
 * CommentPress Core Multisite BuddyPress Class.
 *
 * This class encapsulates BuddyPress compatibility.
 *
 * @since 3.3
 */
class Commentpress_Multisite_Buddypress {

	/**
	 * Plugin object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $parent_obj The plugin object.
	 */
	public $parent_obj;

	/**
	 * Database interaction object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $db The database object.
	 */
	public $db;

	/**
	 * CommentPress Core enabled on all groupblogs flag.
	 *
	 * @since 3.3
	 * @access public
	 * @var str $force_commentpress The CommentPress Core enabled on all groupblogs flag.
	 */
	public $force_commentpress = '0';

	/**
	 * Default theme stylesheet for groupblogs (WP3.4+).
	 *
	 * @since 3.3
	 * @access public
	 * @var str $groupblog_theme The default theme stylesheet.
	 */
	public $groupblog_theme = 'commentpress-modern';

	/**
	 * Default theme stylesheet for groupblogs (pre-WP3.4).
	 *
	 * @since 3.3
	 * @access public
	 * @var str $groupblog_theme_name The default theme stylesheet.
	 */
	public $groupblog_theme_name = 'CommentPress Default Theme';

	/**
	 * Groupblog privacy flag.
	 *
	 * @since 3.3
	 * @access public
	 * @var bool $groupblog_privacy True if private groups have private groupblogs.
	 */
	public $groupblog_privacy = 1;

	/**
	 * Require login to leave comments on groupblogs flag.
	 *
	 * @since 3.3
	 * @access public
	 * @var bool $require_comment_registration True if login required.
	 */
	public $require_comment_registration = 1;



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
	 * Enqueue any styles and scripts needed by our public page.
	 *
	 * @since 3.3
	 */
	public function add_frontend_styles() {

		// Dequeue BP Tempate Pack CSS, even if queued.
		wp_dequeue_style( 'bp' );

	}



	/**
	 * Allow HTML comments and content in Multisite blogs.
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
	 * Add capability to edit own comments.
	 *
	 * @see: http://scribu.net/wordpress/prevent-blog-authors-from-editing-comments.html
	 *
	 * @since 3.3
	 *
	 * @param array $caps The existing capabilities array for the WordPress user.
	 * @param str $cap The capability in question.
	 * @param int $user_id The numerical ID of the WordPress user.
	 * @param array $args The additional arguments.
	 * @return array $caps The modified capabilities array for the WordPress user.
	 */
	public function enable_comment_editing( $caps, $cap, $user_id, $args ) {

		// Only apply this to queries for edit_comment cap.
		if ( 'edit_comment' == $cap ) {

			// Get comment.
			$comment = get_comment( $args[0] );

			// Is the user the same as the comment author?
			if ( $comment->user_id == $user_id ) {

				//$caps[] = 'moderate_comments';
				$caps = [ 'edit_posts' ];

			}

		}

		// --<
		return $caps;

	}



	/**
	 * Override capability to comment based on group membership.
	 *
	 * @since 3.3
	 *
	 * @param bool $approved True if the comment is approved, false otherwise.
	 * @param array $commentdata The comment data.
	 * @return bool $approved Modified approval value. True if the comment is approved, false otherwise.
	 */
	public function pre_comment_approved( $approved, $commentdata ) {

		// Do we have groupblogs?
		if ( function_exists( 'get_groupblog_group_id' ) ) {

			// Get current blog ID.
			$blog_id = get_current_blog_id();

			// Check if this blog is a group blog.
			$group_id = get_groupblog_group_id( $blog_id );

			// When this blog is a groupblog.
			if ( isset( $group_id ) AND is_numeric( $group_id ) AND $group_id > 0 ) {

				// Is this user a member?
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
	// A nicer way?
	add_action( 'preprocess_comment', 'my_check_comment', 1 );

	public function my_check_comment( $commentdata ) {

		// Get the user ID of the comment author.
		$user_id = absint( $commentdata['user_ID'] );

		// If comment author is a registered user, approve the comment.
		if ( 0 < $user_id )
			add_filter( 'pre_comment_approved', 'my_approve_comment' );
		else
			add_filter( 'pre_comment_approved', 'my_moderate_comment' );

		return $commentdata;
	}

	public function my_approve_comment( $approved ) {
		$approved = 1;
		return $approved;
	}

	public function my_moderate_comment( $approved ) {
		if ( 'spam' !== $approved )
			$approved = 0;
		return $approved;
	}
	*/



	/**
	 * Add pages to the post_types that BuddyPress records published activity for.
	 *
	 * @since 3.3
	 *
	 * @param array $post_types The existing array of post types.
	 * @return array $post_types The modified array of post types.
	 */
	public function record_published_pages( $post_types ) {

		// If not in the array already.
		if ( ! in_array( 'page', $post_types ) ) {

			// Add page post_type.
			$post_types[] = 'page';

		}

		// --<
		return $post_types;

	}



	/**
	 * Register "page" as a post_type that BuddyPress records comment activity for.
	 *
	 * @since 3.9.3
	 */
	public function register_comment_tracking_on_pages() {

		// Bail if Activity Component is not active.
		if ( ! function_exists( 'bp_activity_set_post_type_tracking_args' ) ) return;

		// Amend "page" post type.
		add_post_type_support( 'page', 'buddypress-activity' );

		// Define tracking args.
		bp_activity_set_post_type_tracking_args( 'page', [
			'action_id' => 'new_page',
			'bp_activity_admin_filter' => __( 'Published a new page', 'commentpress-core' ),
			'bp_activity_front_filter' => __( 'Pages', 'commentpress-core' ),
			'bp_activity_new_post' => __( '%1$s posted a new <a href="%2$s">page</a>', 'commentpress-core' ),
			'bp_activity_new_post_ms' => __( '%1$s posted a new <a href="%2$s">page</a>, on the site %3$s', 'commentpress-core' ),
			'contexts' => array( 'activity', 'member' ),
			'comment_action_id' => 'new_blog_comment',
			'bp_activity_comments_admin_filter' => __( 'Commented on a page', 'commentpress-core' ),
			'bp_activity_comments_front_filter' => __( 'Comments', 'commentpress-core' ),
			'bp_activity_new_comment' => __( '%1$s commented on the <a href="%2$s">page</a>', 'commentpress-core' ),
			'bp_activity_new_comment_ms' => __( '%1$s commented on the <a href="%2$s">page</a>, on the site %3$s', 'commentpress-core' ),
			'position' => 100,
		] );

	}



	/**
	 * Add pages to the post_types that BuddyPress records comment activity for.
	 *
	 * @since 3.3
	 *
	 * @param array $post_types The existing array of post types.
	 * @return array $post_types The modified array of post types.
	 */
	public function record_comments_on_pages( $post_types ) {

		// If not in the array already.
		if ( ! in_array( 'page', $post_types ) ) {

			// Add page post_type.
			$post_types[] = 'page';

		}

		// --<
		return $post_types;

	}



	/**
	 * Override "publicness" of groupblogs so that we can set the hide_sitewide
	 * property of the activity item (post or comment) depending on the group's
	 * setting.
	 *
	 * Do we want to test if they are CommentPress Core-enabled?
	 *
	 * @since 3.3
	 *
	 * @param bool $blog_public_option True if blog is public, false otherwise.
	 * @return bool $blog_public_option True if blog is public, false otherwise.
	 */
	public function is_blog_public( $blog_public_option ) {

		// Do we have groupblogs?
		if ( function_exists( 'get_groupblog_group_id' ) ) {

			// Get current blog ID.
			$blog_id = get_current_blog_id();

			// Check if this blog is a group blog.
			$group_id = get_groupblog_group_id( $blog_id );

			// When this blog is a groupblog.
			if ( isset( $group_id ) AND is_numeric( $group_id ) AND $group_id > 0 ) {

				// Always true - so that activities are registered.
				return 1;

			}

		}

		// Fallback.
		return $blog_public_option;

	}



	/**
	 * Disable comment sync because parent activity items may not be in the same
	 * group as the comment. Furthermore, CommentPress Core comments should be
	 * read in context rather than appearing as if globally attached to the post
	 * or page.
	 *
	 * @since 3.3
	 *
	 * @param bool $is_disabled The BuddyPress setting that determines blogforum sync.
	 * @return bool $is_disabled The modified value that determines blogforum sync.
	 */
	public function disable_blogforum_comments( $is_disabled ) {

		// Don't mess with admin.
		if ( is_admin() ) return $is_disabled;

		// Get current blog ID.
		$blog_id = get_current_blog_id();

		// If it's CommentPress Core-enabled, disable sync.
		if ( $this->db->is_commentpress( $blog_id ) ) return 1;

		// Pass through.
		return $is_disabled;

	}



	/**
	 * Record the blog activity for the group.
	 *
	 * Amended from bp_groupblog_set_group_to_post_activity()
	 *
	 * @since 3.3
	 *
	 * @param object $activity The existing activity object.
	 * @return object $activity The modified activity object.
	 */
	public function group_custom_comment_activity( $activity ) {

		// Only deal with comments.
		if ( ( $activity->type != 'new_blog_comment' ) ) return;

		// Init vars.
		$is_groupblog = false;
		$is_groupsite = false;
		$is_working_paper = false;

		// Get groupblog status.
		$is_groupblog = $this->_is_commentpress_groupblog();

		// If on a CommentPress Core-enabled groupblog.
		if ( $is_groupblog ) {

			// Which blog?
			$blog_id = $activity->item_id;

			// Get the group ID.
			$group_id = get_groupblog_group_id( $blog_id );

			// Kick out if not groupblog
			if ( empty( $group_id ) ) return $activity;

			// Set activity type.
			$type = 'new_groupblog_comment';

		} else {

			// Get group site status.
			$is_groupsite = $this->_is_commentpress_groupsite();

			// If on a CommentPress Core-enabled group site.
			if ( $is_groupsite ) {

				// Get group ID from POST.
				global $bp_groupsites;
				$group_id = $bp_groupsites->activity->get_group_id_from_comment_form();

				// Kick out if not a comment in a group.
				if ( false === $group_id ) return $activity;

				// Set activity type.
				$type = 'new_groupsite_comment';

			} else {

				// Do we have the function we need to call?
				if ( function_exists( 'bpwpapers_is_working_paper' ) ) {

					// Which blog?
					$blog_id = $activity->item_id;

					// Only on working papers.
					if ( ! bpwpapers_is_working_paper( $blog_id ) ) return $activity;

					// Get the group ID for this blog.
					$group_id = bpwpapers_get_group_by_blog_id( $blog_id );

					// Sanity check.
					if ( $group_id === false ) return $activity;

					// Set activity type.
					$type = 'new_working_paper_comment';

					// Working paper is active.
					$is_working_paper = true;

				}

			}

		}

		// Sanity check.
		if ( ! $is_groupblog AND ! $is_groupsite AND ! $is_working_paper ) return $activity;

		// Okay, let's get the group object.
		$group = groups_get_group( [ 'group_id' => $group_id ] );

		// See if we already have the modified activity for this blog post.
		$id = bp_activity_get_activity_id( [
			'user_id' => $activity->user_id,
			'type' => $type,
			'item_id' => $group_id,
			'secondary_item_id' => $activity->secondary_item_id,
		] );

		// If we don't find a modified item.
		if ( ! $id ) {

			// See if we have an unmodified activity item.
			$id = bp_activity_get_activity_id( [
				'user_id' => $activity->user_id,
				'type' => $activity->type,
				'item_id' => $activity->item_id,
				'secondary_item_id' => $activity->secondary_item_id,
			] );

		}

		// If we found an activity for this blog comment then overwrite that to avoid having
		// multiple activities for every blog comment edit.
		if ( $id ) $activity->id = $id;

		// Get the comment.
		$comment = get_comment( $activity->secondary_item_id );

		// Get the post.
		$post = get_post( $comment->comment_post_ID );

		// Was it a registered user?
		if ($comment->user_id != '0') {

			// Get user details.
			$user = get_userdata( $comment->user_id );

			// Construct user link.
			$user_link = bp_core_get_userlink( $activity->user_id );

		} else {

			// Show anonymous user.
			$user_link = '<span class="anon-commenter">' . __( 'Anonymous', 'commentpress-core' ) . '</span>';

		}

		// If on a CommentPress Core-enabled groupblog.
		if ( $is_groupblog ) {

			// Allow plugins to override the name of the activity item.
			$activity_name = apply_filters(
				'cp_activity_post_name',
				__( 'post', 'commentpress-core' )
			);

		}

		// If on a CommentPress Core-enabled group site.
		if ( $is_groupsite ) {

			// Respect BP Group Sites filter for the name of the activity item.
			$activity_name = apply_filters(
				'bpgsites_activity_post_name',
				__( 'post', 'commentpress-core' ),
				$post
			);

		}

		// If on a CommentPress Core-enabled working paper.
		if ( $is_working_paper ) {

			// Respect BP Working Papers filter for the name of the activity item.
			$activity_name = apply_filters(
				'bpwpapers_activity_post_name',
				__( 'post', 'commentpress-core' ),
				$post
			);

		}

		// Set key.
		$key = '_cp_comment_page';

		// If the custom field has a value, we have a subpage comment.
		if ( get_comment_meta( $comment->comment_ID, $key, true ) != '' ) {

			// Get comment's page from meta.
			$page_num = get_comment_meta( $comment->comment_ID, $key, true );

			// Get the url for the comment.
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

		// Replace the necessary values to display in group activity stream.
		$activity->action = sprintf(
			__( '%1$s left a %2$s on a %3$s %4$s in the group %5$s:', 'commentpress-core' ),
			$user_link,
			$comment_link,
			$activity_name,
			$target_post_link,
			$group_link
		);

		// Allow plugins to override this.
		$activity->action = apply_filters(
			'commentpress_comment_activity_action', // Hook.
			$activity->action, // Default.
			$activity,
			$user_link,
			$comment_link,
			$activity_name,
			$target_post_link,
			$group_link
		);

		// Apply group id.
		$activity->item_id = (int)$group_id;

		// Change to groups component.
		$activity->component = 'groups';

		// Having marked all groupblogs as public, we need to hide activity from them if the group is private
		// or hidden, so they don't show up in sitewide activity feeds.
		if ( 'public' != $group->status ) {
			$activity->hide_sitewide = true;
		} else {
			$activity->hide_sitewide = false;
		}

		// Set unique type
		$activity->type = $type;

		// Note: BuddyPress seemingly runs content through wp_filter_kses. (sad face)

		// Prevent from firing again.
		remove_action( 'bp_activity_before_save', [ $this, 'group_custom_comment_activity' ] );

		// --<
		return $activity;

	}



	/**
	 * Add some meta for the activity item - bp_activity_after_save doesn't seem to fire.
	 *
	 * @since 3.3
	 *
	 * @param object $activity The existing activity object.
	 * @return object $activity The modified activity object.
	 */
	public function groupblog_custom_comment_meta( $activity ) {

		// Only deal with comments.
		if ( ( $activity->type != 'new_groupblog_comment' ) ) return $activity;

		// Only do this on CommentPress Core-enabled groupblogs.
		if ( ( false === $this->_is_commentpress_groupblog() ) ) return $activity;

		// Set a meta value for the blog type of the post.
		$meta_value = $this->_get_groupblog_type();
		$result = bp_activity_update_meta( $activity->id, 'groupblogtype', 'groupblogtype-' . $meta_value );

		// Prevent from firing again.
		remove_action( 'bp_activity_after_save', [ $this, 'groupblog_custom_comment_meta' ] );

		// --<
		return $activity;

	}



	/**
	 * Record the blog post activity for the group.
	 *
	 * Adapted from code by Luiz Armesto.
	 *
	 * Since the updates to BP Groupblog, a second argument is passed to this
	 * method which, if present, means that we don't need to check for an
	 * existing activity item. This code needs to be streamlined in the light
	 * of the changes.
	 *
	 * @see bp_groupblog_set_group_to_post_activity( $activity )
	 *
	 * @since 3.3
	 *
	 * @param object $activity The existing activity object.
	 * @param array $args {
	 *     Optional. Handy if you've already parsed the blog post and group ID.
	 *     @type WP_Post $post The WP post object.
	 *     @type int $group_id The group ID.
	 * }
	 * @return object $activity The modified activity object.
	 */
	public function groupblog_custom_post_activity( $activity, $args = [] ) {

		// Sanity check.
		if ( ! bp_is_active( 'groups' ) ) return $activity;

		// Only on new blog posts.
		if ( ( $activity->type != 'new_blog_post' ) ) return $activity;

		// Only on CommentPress Core-enabled groupblogs.
		if ( ( false === $this->_is_commentpress_groupblog() ) ) return $activity;

		// Clarify data.
		$blog_id = $activity->item_id;
		$post_id = $activity->secondary_item_id;
		$post = get_post( $post_id );

		// Get group ID.
		$group_id = get_groupblog_group_id( $blog_id );
		if ( empty( $group_id ) ) return $activity;

		// Get group.
		$group = groups_get_group( [ 'group_id' => $group_id ] );

		// See if we already have the modified activity for this blog post.
		$id = bp_activity_get_activity_id( [
			'user_id' => $activity->user_id,
			'type' => 'new_groupblog_post',
			'item_id' => $group_id,
			'secondary_item_id' => $activity->secondary_item_id,
		] );

		// If we don't find a modified item.
		if ( ! $id ) {

			// See if we have an unmodified activity item.
			$id = bp_activity_get_activity_id( [
				'user_id' => $activity->user_id,
				'type' => $activity->type,
				'item_id' => $activity->item_id,
				'secondary_item_id' => $activity->secondary_item_id,
			] );

		}

		// If we found an activity for this blog post then overwrite that to avoid
		// having multiple activities for every blog post edit.
		if ( $id ) {
			$activity->id = $id;
		}

		// Allow plugins to override the name of the activity item.
		$activity_name = apply_filters(
			'cp_activity_post_name',
			__( 'post', 'commentpress-core' )
		);

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
					foreach( $authors AS $author ) {

						// Default to comma.
						$sep = ', ';

						// If we're on the penultimate.
						if ( $n == ($author_count - 1) ) {

							// Use ampersand.
							$sep = __( ' &amp; ', 'commentpress-core' );

						}

						// If we're on the last, don't add.
						if ( $n == $author_count ) { $sep = ''; }

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

			// Replace the necessary values to display in group activity stream.
			$activity->action = sprintf(
				__( '%1$s updated a %2$s %3$s in the group %4$s:', 'commentpress-core' ),
				$activity_author,
				$activity_name,
				'<a href="' . get_permalink( $post->ID ) . '">' . esc_attr( $post->post_title ) . '</a>',
				'<a href="' . bp_get_group_permalink( $group ) . '">' . esc_attr( $group->name ) . '</a>'
			);

		} else {

			// Replace the necessary values to display in group activity stream.
			$activity->action = sprintf(
				__( '%1$s wrote a new %2$s %3$s in the group %4$s:', 'commentpress-core' ),
				$activity_author,
				$activity_name,
				'<a href="' . get_permalink( $post->ID ) . '">' . esc_attr( $post->post_title ) . '</a>',
				'<a href="' . bp_get_group_permalink( $group ) . '">' . esc_attr( $group->name ) . '</a>'
			);

		}

		$activity->item_id = (int)$group_id;
		$activity->component = 'groups';

		// Having marked all groupblogs as public, we need to hide activity from them if the group is private
		// or hidden, so they don't show up in sitewide activity feeds.
		if ( 'public' != $group->status ) {
			$activity->hide_sitewide = true;
		} else {
			$activity->hide_sitewide = false;
		}

		// CMW: assume groupblog_post is intended.
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
	 * Detects a post edit and modifies the activity entry if found.
	 *
	 * This is needed for BuddyPress 2.2+. Older versions of BuddyPress continue
	 * to use the {@link bp_groupblog_set_group_to_post_activity()} function.
	 *
	 * This is copied from BP Groupblog and amended to suit.
	 *
	 * @see bp_groupblog_catch_transition_post_type_status()
	 *
	 * @since 3.8.5
	 *
	 * @param str $new_status New status for the post.
	 * @param str $old_status Old status for the post.
	 * @param object $post The post data.
	 */
	public function transition_post_type_status( $new_status, $old_status, $post ) {

		// Only needed for >= BP 2.2.
		if ( ! function_exists( 'bp_activity_post_type_update' ) ) return;

		// Bail if not a blog post.
		if ( 'post' !== $post->post_type ) return;

		// Is this an edit?
		if ( $new_status === $old_status ) {

			// An edit of an existing post should update the existing activity item.
			if ( $new_status == 'publish' ) {

				// Get group ID.
				$group_id = get_groupblog_group_id( get_current_blog_id() );

				// Get existing activity ID.
				$id = bp_activity_get_activity_id( [
					'component'         => 'groups',
					'type'              => 'new_groupblog_post',
					'item_id'           => $group_id,
					'secondary_item_id' => $post->ID,
				] );

				// Bail if we don't have one.
				if ( empty( $id ) ) return;

				// Retrieve activity item and modify some properties.
				$activity = new BP_Activity_Activity( $id );
				$activity->content = $post->post_content;
				$activity->date_recorded = bp_core_current_time();

				// We currently have to fool `$this->groupblog_custom_post_activity()`.
				$activity->type = 'new_blog_post';

				// Pass activity to our edit function.
				$this->groupblog_custom_post_activity( $activity, [
					'group_id' => $group_id,
					'post'     => $post,
				] );

			}

		}

	}



	/**
	 * Add some meta for the activity item. (DISABLED)
	 *
	 * @since 3.3
	 *
	 * @param object $activity The existing activity object.
	 * @return object $activity The modified activity object.
	 */
	public function groupblog_custom_post_meta( $activity ) {

		// Only on new blog posts.
		if ( ( $activity->type != 'new_groupblog_post' ) ) return;

		// Only on CommentPress Core-enabled groupblogs.
		if ( ( false === $this->_is_commentpress_groupblog() ) ) return;

		// Set a meta value for the blog type of the post.
		$meta_value = $this->_get_groupblog_type();
		bp_activity_update_meta( $activity->id, 'groupblogtype', 'groupblogtype-' . $meta_value );

		// --<
		return $activity;

	}



	/**
	 * Check if a group has a CommentPress Core-enabled groupblog.
	 *
	 * @since 3.3
	 *
	 * @param int $group_id The numeric ID of the BuddyPress group.
	 * @return boolean True if group has CommentPress Core groupblog, false otherwise.
	 */
	public function group_has_commentpress_groupblog( $group_id = null ) {

		// Do we have groupblogs enabled?
		if ( function_exists( 'get_groupblog_group_id' ) ) {

			// Did we get a specific group passed in?
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

			// Yes, is this blog a groupblog?
			if ( ! empty( $group_id ) AND is_numeric( $group_id ) AND $group_id > 0 ) {

				// Is it CommentPress Core-enabled?

				// Get group blogtype.
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
	 * Add a filter option to the filter select box on group activity pages.
	 *
	 * @since 3.3
	 */
	public function groupblog_comments_filter_option() {

		// Default name.
		$comment_name = __( 'CommentPress Comments', 'commentpress-core' );

		// Allow plugins to override the name of the option.
		$comment_name = apply_filters( 'cp_groupblog_comment_name', $comment_name );

		// Construct option.
		$option = '<option value="new_groupblog_comment">' . $comment_name . '</option>' . "\n";

		// Print
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

		// Allow plugins to override the name of the option.
		$name = apply_filters( 'cp_groupblog_post_name', $name );

		// Construct option.
		$option = '<option value="new_groupblog_post">' . $name . '</option>' . "\n";

		// Print
		echo $option;

	}



	/**
	 * For group blogs, override the avatar with that of the group.
	 *
	 * @since 3.3
	 *
	 * @param str $avatar The existing HTML for displaying an avatar.
	 * @param int $blog_id The numeric ID of the WordPress blog.
	 * @param array $args Additional arguments.
	 * @return str $avatar The modified HTML for displaying an avatar.
	 */
	public function get_blog_avatar( $avatar, $blog_id = '', $args ){

		// Do we have groupblogs?
		if ( function_exists( 'get_groupblog_group_id' ) ) {

			// Get the group ID.
			$group_id = get_groupblog_group_id( $blog_id );

		}

		// Did we get a group for which this is the group blog?
		if ( isset( $group_id ) AND is_numeric( $group_id ) AND $group_id > 0 ) {

			// --<
			return bp_core_fetch_avatar( [ 'item_id' => $group_id, 'object' => 'group' ] );

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

		// Get group blogtype.
		$groupblog_type = groups_get_groupmeta( bp_get_current_group_id(), 'groupblogtype' );

		// Did we get one?
		if ( $groupblog_type ) {

			// Yes, it's a CommentPress Core-enabled groupblog
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

		// Get group blogtype.
		$groupblog_type = groups_get_groupmeta( bp_get_current_group_id(), 'groupblogtype' );

		// Did we get one?
		if ( $groupblog_type ) {

			// Yes, it's a CommentPress Core-enabled groupblog
			return apply_filters( 'cpmu_bp_groupblog_subnav_item_slug', 'document' );

		}

		// --<
		return $slug;

	}



	/**
	 * Override CommentPress Core "Title Page".
	 *
	 * @since 3.3
	 *
	 * @param str $title The existing title of a "blog".
	 * @return str $title The modified title of a "blog".
	 */
	public function filter_nav_title_page_title( $title ) {

		// Bail if main BuddyPress site.
		if ( bp_is_root_blog() ) return $title;

		// Override default link name.
		return apply_filters( 'cpmu_bp_nav_title_page_title', __( 'Document Home Page', 'commentpress-core' ) );

	}



	/**
	 * Remove group blogs from blog list.
	 *
	 * @since 3.3
	 *
	 * @param bool $b True if there are blogs, false otherwise.
	 * @param object $blogs The existing blogs object.
	 * @return object $blogs The modified blogs object.
	 */
	public function remove_groupblog_from_loop( $b, $blogs ) {

		// Loop through them.
		foreach ( $blogs->blogs as $key => $blog ) {

			// Exclude if it's a group blog
			if ( function_exists( 'groupblog_group_id' ) ) {

				// Get group ID.
				$group_id = get_groupblog_group_id( $blog->blog_id );

				// Did we get one?
				if ( isset( $group_id ) AND is_numeric( $group_id ) AND $group_id > 0 ) {

					// Exclude.
					unset( $blogs->blogs[$key] );

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
	 * @param array $button The existing blogs button data.
	 * @return array $button The existing blogs button data.
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

		// Do we have groupblogs enabled?
		if ( function_exists( 'get_groupblog_group_id' ) ) {

			// Get group ID.
			$group_id = get_groupblog_group_id( $blogs_template->blog->blog_id );

			// Yes, is this blog a groupblog?
			if ( isset( $group_id ) AND is_numeric( $group_id ) AND $group_id > 0 ) {

				// Is it CommentPress Core-enabled?

				// Get group blogtype.
				$groupblog_type = groups_get_groupmeta( $group_id, 'groupblogtype' );

				// Did we get one?
				if ( $groupblog_type ) {

					// Yes.
					$blogtype = 'commentpress-groupblog';

				} else {

					// Standard groupblog.
					$blogtype = 'groupblog';

				}

			}

		} else {

			// TODO: is this blog CommentPress Core-enabled?
			// We cannot do this without switch_to_blog at the moment.
			$blogtype = 'blog';

		}

		// Switch by blogtype.
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

			// Standard groupblog.
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
	 * Override the name of the type dropdown label.
	 *
	 * @since 3.3
	 *
	 * @param str $name The existing name of the label.
	 * @return str $name The modified name of the label.
	 */
	public function blog_type_label( $name ) {

		return apply_filters( 'cp_class_commentpress_formatter_label', __( 'Default Text Format', 'commentpress-core' ) );

	}



	/**
	 * Define the "types" of groupblog.
	 *
	 * @since 3.3
	 *
	 * @param array $existing_options The existing types of groupblog.
	 * @return array $existing_options The modified types of groupblog.
	 */
	public function blog_type_options( $existing_options ) {

		// Define types.
		$types = [
			__( 'Prose', 'commentpress-core' ), // Types[0]
			__( 'Poetry', 'commentpress-core' ), // Types[1]
		];

		// --<
		return apply_filters( 'cp_class_commentpress_formatter_types', $types );

	}



	/**
	 * Enable workflow.
	 *
	 * @since 3.3
	 *
	 * @param bool $exists True if "workflow" is enabled, false otherwise.
	 * @return bool $exists True if "workflow" is enabled, false otherwise.
	 */
	public function blog_workflow_exists( $exists ) {

		// Switch on, but allow overrides.
		return apply_filters( 'cp_class_commentpress_workflow_enabled', true );

	}



	/**
	 * Override the name of the workflow checkbox label.
	 *
	 * @since 3.3
	 *
	 * @param str $name The existing singular name of the label.
	 * @return str $name The modified singular name of the label.
	 */
	public function blog_workflow_label( $name ) {

		// Set label, but allow overrides.
		return apply_filters( 'cp_class_commentpress_workflow_label', __( 'Enable Translation Workflow', 'commentpress-core' ) );

	}



	/**
	 * Amend the group meta if workflow is enabled.
	 *
	 * @since 3.3
	 *
	 * @param str $blog_type The existing numerical type of the blog.
	 * @return str $blog_type The modified numerical type of the blog.
	 */
	public function group_meta_set_blog_type( $blog_type, $blog_workflow ) {

		// If the blog workflow is enabled, then this is a translation group.
		if ( $blog_workflow == '1' ) {

			// Translation is type 2.
			$blog_type = '2';

		}

		/**
		 * Allow plugins to override the blog type - for example if workflow
		 * is enabled, it might become a new blog type as far as BuddyPress
		 * is concerned.
		 *
		 * @since 3.3
		 *
		 * @param int $blog_type The numeric blog type.
		 * @param bool $blog_workflow True if workflow enabled, false otherwise.
		 */
		return apply_filters( 'cp_class_commentpress_workflow_group_blogtype', $blog_type, $blog_workflow );

	}



	/**
	 * Hook into the group blog signup form.
	 *
	 * @since 3.3
	 *
	 * @param array $errors The errors generated previously.
	 */
	public function signup_blogform( $errors ) {

		// Apply to group blog signup form?
		if ( bp_is_groups_component() ) {

			// Hand off to private method.
			$this->_create_groupblog_options();

		} else {

			// Hand off to private method.
			$this->_create_blog_options();

		}

	}



	/**
	 * Hook into wpmu_new_blog and target plugins to be activated.
	 *
	 * @since 3.3
	 *
	 * @param int $blog_id The numeric ID of the WordPress blog.
	 * @param int $user_id The numeric ID of the WordPress user.
	 * @param str $domain The domain of the WordPress blog.
	 * @param str $path The path of the WordPress blog.
	 * @param int $site_id The numeric ID of the WordPress parent site.
	 * @param array $meta The meta data of the WordPress blog.
	 */
	public function wpmu_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		// Test for presence of our checkbox variable in _POST.
		if ( isset( $_POST['cpbp-groupblog'] ) AND $_POST['cpbp-groupblog'] == '1' ) {

			// Hand off to private method.
			$this->_create_groupblog( $blog_id, $user_id, $domain, $path, $site_id, $meta );

		} else {

			// Test for presence of our checkbox variable in _POST.
			if ( isset( $_POST['cpbp-new-blog'] ) AND $_POST['cpbp-new-blog'] == '1' ) {

				// Hand off to private method.
				$this->_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta );

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

		// Override default link name.
		return apply_filters( 'cpmu_bp_create_new_site_title', __( 'Create a New Site', 'commentpress-core' ) );

	}



	/**
	 * Check if a non-public group is being accessed by a user who is not a
	 * member of the group.
	 *
	 * Adapted from code in mahype's fork of BuddyPress Groupblog plugin, but not
	 * accepted because there may be cases where private groups have public
	 * groupblogs. Ours is not such a case.
	 *
	 * @see groupblog_privacy_check()
	 *
	 * @since 3.3
	 */
	public function groupblog_privacy_check() {

		// Allow network admins through regardless.
		if ( is_super_admin() ) return;

		// Check our site option.
		if ( $this->db->option_get( 'cpmu_bp_groupblog_privacy' ) != '1' ) return;

		global $blog_id, $current_user;

		// If is not the main blog but we do have a blog ID.
		if( ! is_main_site() AND isset( $blog_id ) AND is_numeric( $blog_id ) ) {

			// Do we have groupblog active?
			if ( function_exists( 'get_groupblog_group_id' ) ) {

				// Get group ID for this blog.
				$group_id = get_groupblog_group_id( $blog_id );

				// If we get one.
				if ( isset( $group_id ) AND is_numeric( $group_id ) AND $group_id > 0 ) {

					// Get the group object.
					$group = new BP_Groups_Group( $group_id );

					// If group is not public.
					if( $group->status != 'public' ) {

						// Is the groupblog CommentPress Core enabled?
						if ( $this->group_has_commentpress_groupblog( $group->id ) ) {

							// Is the current user a member of the blog?
							if ( ! is_user_member_of_blog( $current_user->ID, $blog_id ) ) {

								// No - redirect to network home, but allow overrides.
								wp_redirect( apply_filters( 'bp_groupblog_privacy_redirect_url', network_site_url() ) );
								exit;

							}

						}

					}

				}

			}

		}

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

		// Register hooks.
		$this->_register_hooks();

	}



	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.3
	 */
	public function _register_hooks() {

		// Enable html comments and content for authors.
		add_action( 'init', [ $this, 'allow_html_content' ] );

		// Check for the privacy of a groupblog.
		add_action( 'init', [ $this, 'groupblog_privacy_check' ] );

		// Add some tags to the allowed tags in activities.
		add_filter( 'bp_activity_allowed_tags', [ $this, 'activity_allowed_tags' ], 20 );

		// Allow comment authors to edit their own comments.
		add_filter( 'map_meta_cap', [ $this, 'enable_comment_editing' ], 10, 4 );

		// Amend comment activity.
		add_filter( 'pre_comment_approved', [ $this, 'pre_comment_approved' ], 99, 2 );
		//add_action( 'preprocess_comment', 'my_check_comment', 1 );

		// Register "page" as a post_type that BuddyPress records comment activity for.
		add_action( 'init', [ $this, 'register_comment_tracking_on_pages' ], 100 );

		// Add pages to the post_types that BuddyPress records comment activity for.
		add_filter( 'bp_blogs_record_comment_post_types', [ $this, 'record_comments_on_pages' ], 10, 1 );

		// Add pages to the post_types that BuddyPress records published activity for.
		//add_filter( 'bp_blogs_record_post_post_types', [ $this, 'record_published_pages' ], 10, 1 );

		// Make sure "Allow activity stream commenting on blog and forum posts" is disabled.
		add_action( 'bp_disable_blogforum_comments', [ $this, 'disable_blogforum_comments' ], 20, 1 );

		// Override "publicness" of groupblogs.
		add_filter( 'bp_is_blog_public', [ $this, 'is_blog_public' ], 20, 1 );

		// Amend BuddyPress group activity (after class Commentpress_Core does).
		add_action( 'bp_setup_globals', [ $this, '_group_activity_mods' ], 1001 );

		// Get group avatar when listing groupblogs.
		add_filter( 'bp_get_blog_avatar', [ $this, 'get_blog_avatar' ], 20, 3 );

		// Filter bp-groupblog defaults.
		add_filter( 'bp_groupblog_subnav_item_name', [ $this, 'filter_blog_name' ], 20 );
		add_filter( 'bp_groupblog_subnav_item_slug', [ $this, 'filter_blog_slug' ], 20 );

		// Override CommentPress Core "Title Page".
		add_filter( 'cp_nav_title_page_title', [ $this, 'filter_nav_title_page_title' ], 20 );

		// Override the name of the button on the BuddyPress "blogs" screen.
		// To override this, just add the same filter with a priority of 21 or greater.
		add_filter( 'bp_get_blogs_visit_blog_button', [ $this, 'get_blogs_visit_blog_button' ], 20 );

		// We can remove groupblogs from the blog list, but cannot update the total_blog_count_for_user
		// that is displayed on the tab *before* the blog list is built - hence filter disabled for now.
		//add_filter( 'bp_has_blogs', [ $this, 'remove_groupblog_from_loop' ], 20, 2 );

		/*
		 * Duplicated from 'class_commentpress_formatter.php' because CommentPress Core need
		 * not be active on the main blog, but we still need the options to appear
		 * in the Groupblog Create screen.
		 */

		// Set blog type options.
		add_filter( 'cp_blog_type_options', [ $this, 'blog_type_options' ], 21 );

		// Set blog type options label.
		add_filter( 'cp_blog_type_label', [ $this, 'blog_type_label' ], 21 );

		// ---------------------------------------------------------------------

		/*
		 * Duplicated from 'class_commentpress_workflow.php' because CommentPress Core need
		 * not be active on the main blog, but we still need the options to appear
		 * in the Groupblog Create screen.
		 */

		// Enable workflow.
		add_filter( 'cp_blog_workflow_exists', [ $this, 'blog_workflow_exists' ], 21 );

		// Override label.
		add_filter( 'cp_blog_workflow_label', [ $this, 'blog_workflow_label' ], 21 );

		// Override blog type if workflow is on.
		add_filter( 'cp_get_group_meta_for_blog_type', [ $this, 'group_meta_set_blog_type' ], 21, 2 );

		// ---------------------------------------------------------------------

		// Add form elements to groupblog form.
		add_action( 'signup_blogform', [ $this, 'signup_blogform' ] );

		// Activate blog-specific CommentPress Core plugin.
		// Added @ priority 20 because BuddyPress Groupblog adds its action at the default 10 and
		// we want it to have done its stuff before we do ours.
		add_action( 'wpmu_new_blog', [ $this, 'wpmu_new_blog' ], 20, 6 );

		// Register any public styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'add_frontend_styles' ], 20 );

		// Override CommentPress Core "Create New Document" text.
		add_filter( 'cp_user_links_new_site_title', [ $this, 'user_links_new_site_title' ], 21 );
		add_filter( 'cp_site_directory_link_title', [ $this, 'user_links_new_site_title' ], 21 );
		add_filter( 'cp_register_new_site_page_title', [ $this, 'user_links_new_site_title' ], 21 );

		// Override groupblog theme, if the bp-groupblog default theme is not a CommentPress Core one.
		add_filter( 'cp_forced_theme_slug', [ $this, '_get_groupblog_theme' ], 20, 1 );
		add_filter( 'cp_forced_theme_name', [ $this, '_get_groupblog_theme' ], 20, 1 );

		// Filter the AJAX query string to add "action".
		add_filter( 'bp_ajax_querystring', [ $this, '_groupblog_querystring' ], 20, 2 );

		// Is this the back end?
		if ( is_admin() ) {

			// Anything specifically for WP Admin.

			// Add options to network settings form.
			add_filter( 'cpmu_network_options_form', [ $this, '_network_admin_form' ], 20 );

			// Add options to reset array.
			add_filter( 'cpmu_db_bp_options_get_defaults', [ $this, '_get_default_settings' ], 20, 1 );

			// Hook into Network BuddyPress form update.
			add_action( 'cpmu_db_options_update', [ $this, '_buddypress_admin_update' ], 20 );

		} else {

			// Anything specifically for Front End.

			// Add filter options for the post and comment activities as late as we can
			// so that bp-groupblog's action can be removed.
			add_action( 'bp_setup_globals', [ $this, '_groupblog_filter_options' ] );

		}

	}



	/**
	 * Add a filter actions once BuddyPress is loaded.
	 *
	 * @since 3.3
	 */
	public function _groupblog_filter_options() {

		// Kick out if this group does not have a CommentPress Core groupblog.
		if ( ! $this->group_has_commentpress_groupblog() ) return;

		// Remove bp-groupblog's contradictory option.
		remove_action( 'bp_group_activity_filter_options', 'bp_groupblog_posts' );

		// Add our consistent one.
		add_action( 'bp_activity_filter_options', [ $this, 'groupblog_posts_filter_option' ] );
		add_action( 'bp_group_activity_filter_options', [ $this, 'groupblog_posts_filter_option' ] );
		add_action( 'bp_member_activity_filter_options', [ $this, 'groupblog_posts_filter_option' ] );

		// Add our comments.
		add_action( 'bp_activity_filter_options', [ $this, 'groupblog_comments_filter_option' ] );
		add_action( 'bp_group_activity_filter_options', [ $this, 'groupblog_comments_filter_option' ] );
		add_action( 'bp_member_activity_filter_options', [ $this, 'groupblog_comments_filter_option' ] );

	}



	/**
	 * Amend Activity methods once BuddyPress is loaded.
	 *
	 * @since 3.3
	 */
	public function _group_activity_mods() {

		// Don't mess with hooks unless the blog is CommentPress Core-enabled.
		if ( ( false === $this->_is_commentpress_groupblog() ) ) return;

		// Allow lists in activity content.
		add_action( 'bp_activity_allowed_tags', [ $this, '_activity_allowed_tags' ], 20, 1 );

		// Drop the bp-groupblog post activity actions.
		remove_action( 'bp_activity_before_save', 'bp_groupblog_set_group_to_post_activity' );
		remove_action( 'transition_post_status', 'bp_groupblog_catch_transition_post_type_status' );

		// Implement our own post activity (with Co-Authors compatibility).
		add_action( 'bp_activity_before_save', [ $this, 'groupblog_custom_post_activity' ], 20, 1 );
		add_action( 'transition_post_status', [ $this, 'transition_post_type_status' ], 20, 3 );

		// CommentPress Core needs to know the sub-page for a comment, therefore:

		// Drop the bp-group-sites comment activity action, if present.
		global $bp_groupsites;
		if ( ! is_null( $bp_groupsites ) AND is_object( $bp_groupsites ) ) {
			remove_action( 'bp_activity_before_save', [ $bp_groupsites->activity, 'custom_comment_activity' ] );
		}

		// Drop the bp-working-papers comment activity action, if present.
		global $bp_working_papers;
		if ( ! is_null( $bp_working_papers ) AND is_object( $bp_working_papers ) ) {
			remove_action( 'bp_activity_before_save', [ $bp_working_papers->activity, 'custom_comment_activity' ] );
		}

		// Add our own custom comment activity.
		add_action( 'bp_activity_before_save', [ $this, 'group_custom_comment_activity' ], 20, 1 );

		// These don't seem to fire to allow us to add our meta values for the items.
		// Instead, I'm trying to store the blog_type as group meta data.
		//add_action( 'bp_activity_after_save', [ $this, 'groupblog_custom_comment_meta' ], 20, 1 );
		//add_action( 'bp_activity_after_save', [ $this, 'groupblog_custom_post_meta' ], 20, 1 );

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
	public function _groupblog_querystring( $qs, $object ) {

		// Bail if not an activity object.
		if ( $object != 'activity' ) return $qs;

		// Parse query string into an array.
		$r = wp_parse_args( $qs );

		// Bail if no type is set.
		if ( empty( $r['type'] ) ) return $qs;

		// Bail if not a type that we're looking for.
		if ( 'new_groupblog_post' !== $r['type'] AND 'new_groupblog_comment' !== $r['type'] ) {
			return $qs;
		}

		// Add the 'new_groupblog_post' type if it doesn't exist.
		if ( 'new_groupblog_post' === $r['type'] ) {
			if ( ! isset( $r['action'] ) OR false === strpos( $r['action'], 'new_groupblog_post' ) ) {
				// 'action' filters activity items by the 'type' column.
				$r['action'] = 'new_groupblog_post';
			}
		}

		// Add the 'new_groupblog_comment' type if it doesn't exist.
		if ( 'new_groupblog_comment' === $r['type'] ) {
			if ( ! isset( $r['action'] ) OR false === strpos( $r['action'], 'new_groupblog_comment' ) ) {
				// 'action' filters activity items by the 'type' column.
				$r['action'] = 'new_groupblog_comment';
			}
		}

		// 'type' isn't used anywhere internally.
		unset( $r['type'] );

		// Return a querystring.
		return build_query( $r );

	}



	/**
	 * Allow our TinyMCE comment markup in activity content.
	 *
	 * @since 3.3
	 *
	 * @param array $activity_allowedtags The array of tags allowed in an activity item.
	 * @param array $activity_allowedtags The modified array of tags allowed in an activity item.
	 */
	public function _activity_allowed_tags( $activity_allowedtags ) {

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
	 * Hook into the groupblog create screen.
	 *
	 * @since 3.3
	 */
	public function _create_groupblog_options() {

		global $bp, $groupblog_create_screen;

		$blog_id = get_groupblog_blog_id();

		if ( ! $groupblog_create_screen && $blog_id != '' ) {

			// Existing blog and group - do we need to present any options?

		} else {

			// Creating a new group - no groupblog exists yet
			// NOTE: need to check that our context is right.

			// Get force option.
			$forced = $this->db->option_get( 'cpmu_bp_force_commentpress' );

			// Are we force-enabling CommentPress Core?
			if ( $forced ) {

				// Set hidden element.
				$forced_html = '
				<input type="hidden" value="1" id="cpbp-groupblog" name="cpbp-groupblog" />
				';

				// Define text, but allow overrides.
				$text = apply_filters(
					'cp_groupblog_options_signup_text_forced',
					__( 'Select the options for your new CommentPress-enabled blog. Note: if you choose an existing blog as a group blog, setting these options will have no effect.', 'commentpress-core' )
				);

			} else {

				// Set checkbox.
				$forced_html = '
				<div class="checkbox">
					<label for="cpbp-groupblog"><input type="checkbox" value="1" id="cpbp-groupblog" name="cpbp-groupblog" /> ' . __( 'Enable CommentPress', 'commentpress-core' ) . '</label>
				</div>
				';

				// Define text, but allow overrides.
				$text = apply_filters(
					'cp_groupblog_options_signup_text',
					__( 'When you create a group blog, you can choose to enable it as a CommentPress blog. This is a "one time only" option because you cannot disable CommentPress from here once the group blog is created. Note: if you choose an existing blog as a group blog, setting this option will have no effect.', 'commentpress-core' )
				);

			}

			// Off by default.
			$has_workflow = false;

			// Init output.
			$workflow_html = '';

			// Allow overrides.
			$has_workflow = apply_filters( 'cp_blog_workflow_exists', $has_workflow );

			// If we have workflow enabled, by a plugin, say.
			if ( $has_workflow !== false ) {

				// Define workflow label.
				$workflow_label = __( 'Enable Custom Workflow', 'commentpress-core' );

				// Allow overrides.
				$workflow_label = apply_filters( 'cp_blog_workflow_label', $workflow_label );

				// Show it.
				$workflow_html = '

				<div class="checkbox">
					<label for="cp_blog_workflow"><input type="checkbox" value="1" id="cp_blog_workflow" name="cp_blog_workflow" /> ' . $workflow_label . '</label>
				</div>

				';

			}

			// Assume no types.
			$types = [];

			// Init output.
			$type_html = '';

			// But allow overrides for plugins to supply some.
			$types = apply_filters( 'cp_blog_type_options', $types );

			// If we got any, use them.
			if ( ! empty( $types ) ) {

				// Define blog type label.
				$type_label = __( 'Document Type', 'commentpress-core' );

				// Allow overrides.
				$type_label = apply_filters( 'cp_blog_type_label', $type_label );

				// Construct options.
				$type_option_list = [];
				$n = 0;
				foreach( $types AS $type ) {
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
				' . $workflow_html . '
				' . $type_html . '
			</div>

			';

			echo $form;

		}

	}



	/**
	 * Create a blog that is a groupblog.
	 *
	 * @since 3.3
	 *
	 * @param int $blog_id The numeric ID of the WordPress blog.
	 * @param int $user_id The numeric ID of the WordPress user.
	 * @param str $domain The domain of the WordPress blog.
	 * @param str $path The path of the WordPress blog.
	 * @param int $site_id The numeric ID of the WordPress parent site.
	 * @param array $meta The meta data of the WordPress blog.
	 */
	public function _create_groupblog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		// Get group id before switch.
		$group_id = isset( $_COOKIE['bp_new_group_id'] )
					? $_COOKIE['bp_new_group_id']
					: bp_get_current_group_id();

		// Wpmu_new_blog calls this *after* restore_current_blog, so we need to do it again.
		switch_to_blog( $blog_id );

		// Activate CommentPress Core.
		$this->db->install_commentpress();

		// Access core.
		global $commentpress_core;

		// TODO: create admin page settings for WordPress options.

		// Show posts by default (allow plugin overrides).
		$posts_or_pages = apply_filters( 'cp_posts_or_pages_in_toc', 'post' );
		$commentpress_core->db->option_set( 'cp_show_posts_or_pages_in_toc', $posts_or_pages );

		// If we opted for posts.
		if ( $posts_or_pages == 'post' ) {

			// TOC shows extended posts by default (allow plugin overrides).
			$extended_toc = apply_filters( 'cp_extended_toc', 1 );
			$commentpress_core->db->option_set( 'cp_show_extended_toc', $extended_toc );

		}

		// Get blog type (saved already).
		$cp_blog_type = $commentpress_core->db->option_get( 'cp_blog_type' );

		// Get workflow (saved already).
		$cp_blog_workflow = $commentpress_core->db->option_get( 'cp_blog_workflow' );

		// Did we get a group id before we switched blogs?
		if ( isset( $group_id ) ) {

			/**
			 * Allow plugins to override the blog type - for example if workflow
			 * is enabled, it might become a new blog type as far as BuddyPress
			 * is concerned.
			 *
			 * @param int $cp_blog_type The numeric blog type
			 * @param bool $cp_blog_workflow True if workflow enabled, false otherwise
			 */
			$blog_type = apply_filters( 'cp_get_group_meta_for_blog_type', $cp_blog_type, $cp_blog_workflow );

			// Set the type as group meta info.
			// We also need to change this when the type is changed from the CommentPress Core admin page.
			groups_update_groupmeta( $group_id, 'groupblogtype', 'groupblogtype-' . $blog_type );

		}

		// Save.
		$commentpress_core->db->options_save();

		// ---------------------------------------------------------------------
		// WordPress Internal Configuration.
		// ---------------------------------------------------------------------

		// Get commenting option.
		$anon_comments = $this->db->option_get( 'cpmu_bp_require_comment_registration' ) == '1' ? 1 : 0;

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
		if ( is_array( $active_sitewide_plugins ) AND count( $active_sitewide_plugins ) > 0 ) {

			// Loop through them.
			foreach( $active_sitewide_plugins AS $plugin_path => $plugin_data ) {

				// If we've got BuddyPress Group Email Subscription network-installed.
				if ( false !== strstr( $plugin_path, 'bp-activity-subscription.php' ) ) {

					// Switch comments_notify off.
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
		 * @param int $blog_id The numeric ID of the WordPress blog
		 * @param int $cp_blog_type The numeric blog type
		 * @param bool $cp_blog_workflow True if workflow enabled, false otherwise
		 */
		do_action( 'cp_new_groupblog_created', $blog_id, $cp_blog_type, $cp_blog_workflow );

		// Switch back.
		restore_current_blog();

	}



	/**
	 * Hook into the blog create screen on registration page.
	 *
	 * @since 3.3
	 */
	public function _create_blog_options() {

		// Get force option.
		$forced = $this->db->option_get( 'cpmu_force_commentpress' );

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

		// Off by default.
		$has_workflow = false;

		// Init output.
		$workflow_html = '';

		// Allow overrides.
		$has_workflow = apply_filters( 'cp_blog_workflow_exists', $has_workflow );

		// If we have workflow enabled, by a plugin, say.
		if ( $has_workflow !== false ) {

			// Define workflow label.
			$workflow_label = __( 'Enable Custom Workflow', 'commentpress-core' );

			// Allow overrides.
			$workflow_label = apply_filters( 'cp_blog_workflow_label', $workflow_label );

			// Show it.
			$workflow_html = '

			<div class="checkbox">
				<label for="cp_blog_workflow"><input type="checkbox" value="1" id="cp_blog_workflow" name="cp_blog_workflow" /> ' . $workflow_label . '</label>
			</div>

			';

		}

		// Assume no types.
		$types = [];

		// Init output.
		$type_html = '';

		// But allow overrides for plugins to supply some.
		$types = apply_filters( 'cp_blog_type_options', $types );

		// If we got any, use them.
		if ( ! empty( $types ) ) {

			// Define blog type label.
			$type_label = __( 'Document Type', 'commentpress-core' );

			// Allow overrides.
			$type_label = apply_filters( 'cp_blog_type_label', $type_label );

			// Construct options.
			$type_option_list = [];
			$n = 0;
			foreach( $types AS $type ) {
				$type_option_list[] = '<option value="' . $n . '">' . $type . '</option>';
				$n++;
			}
			$type_options = implode( "\n", $type_option_list );

			// Show it.
			$type_html = '

			<div class="dropdown cp-workflow-type">
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

			' . $workflow_html . '

			' . $type_html . '

		</div>

		';

		echo $form;

	}



	/**
	 * Create a blog that is not a groupblog.
	 *
	 * @since 3.3
	 *
	 * @param int $blog_id The numeric ID of the WordPress blog.
	 * @param int $user_id The numeric ID of the WordPress user.
	 * @param str $domain The domain of the WordPress blog.
	 * @param str $path The path of the WordPress blog.
	 * @param int $site_id The numeric ID of the WordPress parent site.
	 * @param array $meta The meta data of the WordPress blog.
	 */
	public function _create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		// Wpmu_new_blog calls this *after* restore_current_blog, so we need to do it again.
		switch_to_blog( $blog_id );

		// Activate CommentPress Core.
		$this->db->install_commentpress();

		// Switch back.
		restore_current_blog();

	}



	/**
	 * Utility to wrap is_groupblog().
	 *
	 * Note that this only tests the current blog and cannot be used to discover
	 * if a specific blog is a CommentPress Core groupblog.
	 *
	 * Also note that this method only functions after 'bp_setup_globals' has
	 * fired with priority 1000.
	 *
	 * @since 3.3
	 *
	 * @return bool True if current blog is CommentPress Core-enabled, false otherwise.
	 */
	public function _is_commentpress_groupblog() {

		// Check if this blog is a CommentPress Core groupblog.
		global $commentpress_core;
		if (
			! is_null( $commentpress_core ) AND
			is_object( $commentpress_core ) AND
			$commentpress_core->is_groupblog()
		) {

			return true;

		}

		return false;

	}



	/**
	 * Utility to discover if this is a BP Group Site.
	 *
	 * @since 3.8
	 *
	 * @return bool True if current blog is a BP Group Site, false otherwise.
	 */
	public function _is_commentpress_groupsite() {

		// Check if this blog is a CommentPress Core groupsite.
		if (
			function_exists( 'bpgsites_is_groupsite' ) AND
			bpgsites_is_groupsite( get_current_blog_id() )
		) {

			return true;

		}

		return false;

	}



	/**
	 * Utility to get blog_type.
	 *
	 * @since 3.3
	 *
	 * @return mixed String if there is a blog type, false otherwise.
	 */
	public function _get_groupblog_type() {

		global $commentpress_core;

		// If we have the plugin.
		if ( ! is_null( $commentpress_core ) AND is_object( $commentpress_core ) ) {

			// --<
			return $commentpress_core->db->option_get( 'cp_blog_type' ) ;
		}

		// --<
		return false;

	}



	/**
	 * Add our options to the network admin form.
	 *
	 * @since 3.3
	 */
	public function _network_admin_form() {

		// Define admin page.
		$admin_page = '
		<div id="cpmu_bp_admin_options">

		<h3>' . __( 'BuddyPress &amp; Groupblog Settings', 'commentpress-core' ) . '</h3>

		<p>' . __( 'Configure how CommentPress interacts with BuddyPress and BuddyPress Groupblog.', 'commentpress-core' ) . '</p>

		<table class="form-table">

			<tr valign="top">
				<th scope="row"><label for="cpmu_bp_reset">' . __( 'Reset BuddyPress settings', 'commentpress-core' ) . '</label></th>
				<td><input id="cpmu_bp_reset" name="cpmu_bp_reset" value="1" type="checkbox" /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="cpmu_bp_force_commentpress">' . __( 'Make all new Groupblogs CommentPress-enabled', 'commentpress-core' ) . '</label></th>
				<td><input id="cpmu_bp_force_commentpress" name="cpmu_bp_force_commentpress" value="1" type="checkbox"' . ( $this->db->option_get( 'cpmu_bp_force_commentpress' ) == '1' ? ' checked="checked"' : '' ) . ' /></td>
			</tr>

			' . $this->_get_commentpress_themes() . '

			<tr valign="top">
				<th scope="row"><label for="cpmu_bp_groupblog_privacy">' . __( 'Private Groups must have Private Groupblogs', 'commentpress-core' ) . '</label></th>
				<td><input id="cpmu_bp_groupblog_privacy" name="cpmu_bp_groupblog_privacy" value="1" type="checkbox"' . ( $this->db->option_get( 'cpmu_bp_groupblog_privacy' ) == '1' ? ' checked="checked"' : '' ) . ' /></td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="cpmu_bp_require_comment_registration">' . __( 'Require user login to post comments on Groupblogs', 'commentpress-core' ) . '</label></th>
				<td><input id="cpmu_bp_require_comment_registration" name="cpmu_bp_require_comment_registration" value="1" type="checkbox"' . ( $this->db->option_get( 'cpmu_bp_require_comment_registration' ) == '1' ? ' checked="checked"' : '' ) . ' /></td>
			</tr>

			' . $this->_additional_buddypress_options() . '

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
	public function _get_commentpress_themes() {

		// Get all themes.
		if ( function_exists( 'wp_get_themes' ) ) {

			// Get theme data the WP3.4 way.
			$themes = wp_get_themes(
				false,     // Only error-free themes.
				'network', // Only network-allowed themes.
				0          // Use current blog as reference.
			);

			// Get currently selected theme.
			$current_theme = $this->db->option_get('cpmu_bp_groupblog_theme');

		} else {

			// Pre WP3.4 functions.
			$themes = get_themes();

			// Get currently selected theme.
			$current_theme = $this->db->option_get('cpmu_bp_groupblog_theme_name');

		}

		// Init.
		$options = [];
		$element = '';

		// We must get *at least* one (the Default), but let's be safe.
		if ( ! empty( $themes ) ) {

			// Loop.
			foreach( $themes AS $theme ) {

				// Is it a CommentPress Core Groupblog theme?
				if (
					in_array( 'commentpress', (array) $theme['Tags'] ) AND
					in_array( 'groupblog', (array) $theme['Tags'] )
				) {

					// Is this WP3.4+?
					if ( function_exists( 'wp_get_themes' ) ) {

						// Use stylesheet as theme data.
						$theme_data = $theme->get_stylesheet();

					} else {

						// Use name as theme data.
						$theme_data = $theme['Title'];

					}

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
					<th scope="row"><label for="cpmu_bp_groupblog_theme">' . __( 'Select theme for CommentPress Groupblogs', 'commentpress-core' ) . '</label></th>
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
	 * Get Groupblog theme as defined in Network BuddyPress admin.
	 *
	 * @since 3.3
	 *
	 * @param str $default_theme The existing theme.
	 * @return str $theme The modified theme.
	 */
	public function _get_groupblog_theme( $default_theme ) {

		// Get the theme we've defined as the default for groupblogs.
		$theme = $this->db->option_get( 'cpmu_bp_groupblog_theme' );

		// --<
		return $theme;

	}



	/**
	 * Allow other plugins to hook into our multisite admin options.
	 *
	 * @since 3.3
	 *
	 * @return str Empty string, but plugins may send content back.
	 */
	public function _additional_buddypress_options() {

		// Return whatever plugins send back.
		return apply_filters( 'cpmu_network_buddypress_options_form', '' );

	}



	/**
	 * Get default BuddyPress-related settings.
	 *
	 * @since 3.3
	 *
	 * @param array $existing_options The existing options data array.
	 * @return array $options The modified options data array.
	 */
	public function _get_default_settings( $existing_options ) {

		// Is this WP3.4+?
		if ( function_exists( 'wp_get_themes' ) ) {

			// Use stylesheet as theme data.
			$theme_data = $this->groupblog_theme;

		} else {

			// Use name as theme data.
			$theme_data = $this->groupblog_theme_name;

		}

		// Define BuddyPress and BuddyPress Groupblog defaults.
		$defaults = [
			'cpmu_bp_force_commentpress' => $this->force_commentpress,
			'cpmu_bp_groupblog_privacy' => $this->groupblog_privacy,
			'cpmu_bp_require_comment_registration' => $this->require_comment_registration,
			'cpmu_bp_groupblog_theme' => $theme_data,
		];

		// Return defaults, but allow overrides and additions.
		return apply_filters( 'cpmu_buddypress_options_get_defaults', $defaults );

	}



	/**
	 * Hook into Network BuddyPress form update.
	 *
	 * @since 3.3
	 */
	public function _buddypress_admin_update() {

		// Init.
		$cpmu_bp_force_commentpress = '0';
		$cpmu_bp_groupblog_privacy = '0';
		$cpmu_bp_require_comment_registration = '0';

		// Get variables.
		extract( $_POST );

		// Force CommentPress Core to be enabled on all groupblogs.
		$cpmu_bp_force_commentpress = esc_sql( $cpmu_bp_force_commentpress );
		$this->db->option_set( 'cpmu_bp_force_commentpress', ( $cpmu_bp_force_commentpress ? 1 : 0 ) );

		// Groupblog privacy synced to group privacy.
		$cpmu_bp_groupblog_privacy = esc_sql( $cpmu_bp_groupblog_privacy );
		$this->db->option_set( 'cpmu_bp_groupblog_privacy', ( $cpmu_bp_groupblog_privacy ? 1 : 0 ) );

		// Default groupblog theme.
		$cpmu_bp_groupblog_theme = esc_sql( $cpmu_bp_groupblog_theme );
		$this->db->option_set( 'cpmu_bp_groupblog_theme', $cpmu_bp_groupblog_theme );

		// Anon comments on groupblogs.
		$cpmu_bp_require_comment_registration = esc_sql( $cpmu_bp_require_comment_registration );
		$this->db->option_set( 'cpmu_bp_require_comment_registration', ( $cpmu_bp_require_comment_registration ? 1 : 0 ) );

	}



//##############################################################################



} // Class ends.



