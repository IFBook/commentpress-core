<?php
/**
 * CommentPress Multisite BuddyPress Groupblog Groups class.
 *
 * Handles the Group modifications required for BuddyPress Groupblog compatibility.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Multisite BuddyPress Groupblog Groups class.
 *
 * This class provides the Group modifications required for BuddyPress Groupblog
 * compatibility.
 *
 * @since 3.3
 */
class CommentPress_Multisite_BuddyPress_Groupblog_Groups {

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

		// Register hooks based on compatibility.
		$compatibility = $this->groupblog->compatibility_get();
		if ( 'legacy' === $compatibility ) {
			$this->register_hooks_legacy();
		} else {
			$this->register_hooks_latest();
		}

	}

	/**
	 * Register latest hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks_latest() {
		// Future compatibility goes here.
	}

	/**
	 * Register legacy hooks.
	 *
	 * For versions of BuddyPress Groupblog from 1.9.0 onwards, CommentPress no
	 * longer provides replacements for BuddyPress Groupblog Activity Actions.
	 *
	 * @since 4.0
	 */
	public function register_hooks_legacy() {

		/*
		 * Add filters for Group Blog Post and Comment Activities to a late-ish
		 * hook so that BuddyPress Groupblog's action(s) can be removed.
		 */
		add_action( 'bp_setup_globals', [ $this, 'register_hooks_legacy_filters' ], 20 );

		// Amend Group Activity after core checks for BuddyPress Groupblog.
		add_action( 'bp_setup_globals', [ $this, 'register_hooks_legacy_activity' ], 101 );

	}

	/**
	 * Registers the Group Activity Filter hooks needed for BuddyPress Groupblog.
	 *
	 * @since 3.3
	 */
	public function register_hooks_legacy_filters() {

		// Bail if this Group does not have a CommentPress-enabled Group Blog.
		if ( ! $this->has_commentpress_groupblog() ) {
			return;
		}

		// Remove BuddyPress Groupblog's contradictory option.
		remove_action( 'bp_group_activity_filter_options', 'bp_groupblog_posts' );

		// Add our consistent one.
		add_action( 'bp_activity_filter_options', [ $this, 'filter_option_posts' ] );
		add_action( 'bp_group_activity_filter_options', [ $this, 'filter_option_posts' ] );
		add_action( 'bp_member_activity_filter_options', [ $this, 'filter_option_posts' ] );

		// Add our Comments.
		add_action( 'bp_activity_filter_options', [ $this, 'filter_option_comments' ] );
		add_action( 'bp_group_activity_filter_options', [ $this, 'filter_option_comments' ] );
		add_action( 'bp_member_activity_filter_options', [ $this, 'filter_option_comments' ] );

	}

	/**
	 * Registers the Group Activity hooks needed for BuddyPress Groupblog.
	 *
	 * @since 4.0
	 */
	public function register_hooks_legacy_activity() {

		// Don't mess with hooks unless the Group Blog is CommentPress-enabled.
		if ( ( false === $this->groupblog->site->is_commentpress_groupblog() ) ) {
			return;
		}

		// Drop the BuddyPress Groupblog Post Activity actions.
		remove_action( 'bp_activity_before_save', 'bp_groupblog_set_group_to_post_activity' );
		remove_action( 'transition_post_status', 'bp_groupblog_catch_transition_post_type_status' );

		// Drop the "BP Group Sites" Comment Activity action, if present.
		global $bp_groupsites;
		if ( ! empty( $bp_groupsites ) && is_object( $bp_groupsites ) ) {
			remove_action( 'bp_activity_before_save', [ $bp_groupsites->activity, 'custom_comment_activity' ] );
		}

		// Implement our own Post Activity (with Co-Authors compatibility).
		add_action( 'bp_activity_before_save', [ $this, 'activity_post_custom' ], 20, 1 );
		add_action( 'transition_post_status', [ $this, 'activity_post_status_transitioned' ], 20, 3 );

		// Add our own custom Comment Activity.
		add_action( 'bp_activity_before_save', [ $this, 'activity_comment_custom' ], 20, 1 );

		// TODO: CommentPress needs to know the sub-page for a Comment.

		/*
		 * These don't seem to fire to allow us to add our meta values for Activity items.
		 * Instead, we store the Site Text Format as Group meta data.
		add_action( 'bp_activity_after_save', [ $this, 'activity_post_meta' ], 20, 1 );
		add_action( 'bp_activity_after_save', [ $this, 'activity_comment_meta' ], 20, 1 );
		*/

	}

	// -------------------------------------------------------------------------

	/**
	 * Checks if a Group has a CommentPress-enabled Group Blog.
	 *
	 * @since 3.3
	 *
	 * @param int $group_id The numeric ID of the BuddyPress Group.
	 * @return bool True if the Group has CommentPress-enabled Group Blog, false otherwise.
	 */
	public function has_commentpress_groupblog( $group_id = null ) {

		// Look for a Group ID if we didn't get one passed in.
		if ( is_null( $group_id ) ) {

			// Use BuddyPress API.
			$group_id = bp_get_current_group_id();

			// Unlikely, but if we don't get one try and get ID from BuddyPress.
			if ( empty( $group_id ) ) {
				$bp = buddypress();
				if ( ! empty( $bp->groups->current_group->id ) ) {
					$group_id = $bp->groups->current_group->id;
				}
			}

		}

		// Bail if we didn't get a Group ID.
		if ( empty( $group_id ) || ! is_numeric( $group_id ) ) {
			return false;
		}

		// Bail if it is not CommentPress-enabled.
		$groupblog_text_format = $this->groupblog->group_type_get( $group_id );
		if ( empty( $groupblog_text_format ) ) {
			return false;
		}

		// We're good.
		return true;

	}

	// -------------------------------------------------------------------------

	/**
	 * Override the name of the "Posts" filter option.
	 *
	 * @since 3.3
	 */
	public function filter_option_posts() {

		/**
		 * Filters the name of the option.
		 *
		 * @since 3.3
		 *
		 * @param string The name of the "Posts" filter option.
		 */
		$name = apply_filters( 'cp_groupblog_post_name', __( 'CommentPress Posts', 'commentpress-core' ) );

		// Construct option.
		$option = '<option value="new_groupblog_post">' . esc_html( $name ) . '</option>' . "\n";

		// Print.
		echo $option;

	}

	/**
	 * Adds a filter option to the filter select box on Group Activity screens.
	 *
	 * @since 3.3
	 */
	public function filter_option_comments() {

		/**
		 * Filters thename of the "Comments" filter option.
		 *
		 * @since 3.3
		 *
		 * @param string $comment_name The name of the "Comments" filter option.
		 */
		$comment_name = apply_filters( 'cp_groupblog_comment_name', __( 'CommentPress Comments', 'commentpress-core' ) );

		// Construct option.
		$option = '<option value="new_groupblog_comment">' . esc_html( $comment_name ) . '</option>' . "\n";

		// Print.
		echo $option;

	}

	// -------------------------------------------------------------------------

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
	 * @param array  $args {
	 *      Optional. Handy if you've already parsed the Blog Post and Group ID.
	 *
	 *     @type WP_Post $post The WordPress Post object.
	 *     @type int $group_id The Group ID.
	 * }
	 * @return object $activity The modified Activity object.
	 */
	public function activity_post_custom( $activity, $args = [] ) {

		// Sanity check.
		if ( ! bp_is_active( 'groups' ) ) {
			return $activity;
		}

		// Only on new Blog Posts.
		if ( 'new_blog_post' !== $activity->type ) {
			return $activity;
		}

		// Only on CommentPress-enabled Group Blogs.
		if ( false === $this->groupblog->site->is_commentpress_groupblog() ) {
			return $activity;
		}

		// Clarify data.
		$blog_id = $activity->item_id;
		$post_id = $activity->secondary_item_id;
		$post    = get_post( $post_id );

		// Get Group ID.
		$group_id = get_groupblog_group_id( $blog_id );
		if ( empty( $group_id ) ) {
			return $activity;
		}

		// Get Group.
		$group = groups_get_group( [ 'group_id' => $group_id ] );

		// See if we already have the modified Activity for this Blog Post.
		$params = [
			'user_id'           => $activity->user_id,
			'type'              => 'new_groupblog_post',
			'item_id'           => $group_id,
			'secondary_item_id' => $activity->secondary_item_id,
		];

		$id = bp_activity_get_activity_id( $params );

		// If we don't find a modified item.
		if ( ! $id ) {

			// See if we have an unmodified Activity item.
			$params = [
				'user_id'           => $activity->user_id,
				'type'              => $activity->type,
				'item_id'           => $activity->item_id,
				'secondary_item_id' => $activity->secondary_item_id,
			];

			$id = bp_activity_get_activity_id( $params );

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
						if ( ( $author_count - 1 ) === $n ) {

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
				/* translators: 1: The author, 2: The activity name, 3: The name of the Post, 4: The name of the group. */
				__( '%1$s updated a %2$s %3$s in the group %4$s:', 'commentpress-core' ),
				$activity_author,
				$activity_name,
				'<a href="' . get_permalink( $post->ID ) . '">' . esc_attr( $post->post_title ) . '</a>',
				'<a href="' . bp_get_group_permalink( $group ) . '">' . esc_attr( $group->name ) . '</a>'
			);

		} else {

			// Replace the necessary values to display in Group Activity stream.
			$activity->action = sprintf(
				/* translators: 1: The author, 2: The activity name, 3: The name of the Post, 4: The name of the group. */
				__( '%1$s wrote a new %2$s %3$s in the group %4$s:', 'commentpress-core' ),
				$activity_author,
				$activity_name,
				'<a href="' . get_permalink( $post->ID ) . '">' . esc_attr( $post->post_title ) . '</a>',
				'<a href="' . bp_get_group_permalink( $group ) . '">' . esc_attr( $group->name ) . '</a>'
			);

		}

		$activity->item_id   = (int) $group_id;
		$activity->component = 'groups';

		// Having marked all Group Blogs as public, we need to hide Activity from them if the Group is private
		// or hidden, so they don't show up in sitewide Activity feeds.
		if ( 'public' != $group->status ) {
			$activity->hide_sitewide = true;
		} else {
			$activity->hide_sitewide = false;
		}

		// CMW: assume "new_groupblog_post" is intended.
		$activity->type = 'new_groupblog_post';

		// Prevent from firing again.
		remove_action( 'bp_activity_before_save', [ $this, 'activity_post_custom' ] );

		// Using this function outside BP's save routine requires us to manually save.
		if ( ! empty( $args['post'] ) ) {
			$activity->save();
		}

		// --<
		return $activity;

	}

	/**
	 * Add some meta for the Activity item.
	 *
	 * Note: this is not used.
	 *
	 * @since 3.3
	 *
	 * @param object $activity The existing Activity object.
	 * @return object $activity The modified Activity object.
	 */
	public function activity_post_meta( $activity ) {

		// Only on new Blog Posts.
		if ( 'new_groupblog_post' !== $activity->type ) {
			return;
		}

		// Only on CommentPress-enabled Group Blogs.
		if ( false === $this->groupblog->site->is_commentpress_groupblog() ) {
			return;
		}

		// Set a meta value for the Site Text Format of the Post.
		$meta_value = $this->groupblog->site->text_format_get();
		bp_activity_update_meta( $activity->id, 'groupblogtype', 'groupblogtype-' . $meta_value );

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
	 * @param str    $new_status New status for the Post.
	 * @param str    $old_status Old status for the Post.
	 * @param object $post The Post data.
	 */
	public function activity_post_status_transitioned( $new_status, $old_status, $post ) {

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
			if ( 'publish' === $new_status ) {

				// Get Group ID.
				$group_id = get_groupblog_group_id( get_current_blog_id() );

				// Activity ID args.
				$args = [
					'component'         => 'groups',
					'type'              => 'new_groupblog_post',
					'item_id'           => $group_id,
					'secondary_item_id' => $post->ID,
				];

				// Get existing Activity ID.
				$id = bp_activity_get_activity_id( $args );

				// Bail if we don't have one.
				if ( empty( $id ) ) {
					return;
				}

				// Retrieve Activity item and modify some properties.
				$activity                = new BP_Activity_Activity( $id );
				$activity->content       = $post->post_content;
				$activity->date_recorded = bp_core_current_time();

				// We currently have to fool `$this->activity_post_custom()`.
				$activity->type = 'new_blog_post';

				// Build params.
				$params = [
					'group_id' => $group_id,
					'post'     => $post,
				];

				// Pass Activity to our method.
				$this->activity_post_custom( $activity, $params );

			}

		}

	}

	// -------------------------------------------------------------------------

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
	public function activity_comment_custom( $activity ) {

		// Only deal with Comments.
		if ( 'new_blog_comment' !== $activity->type ) {
			return;
		}

		// Init vars.
		$is_groupblog = false;
		$is_groupsite = false;

		// Get Group Blog status.
		$is_groupblog = $this->groupblog->site->is_commentpress_groupblog();

		// If on a CommentPress-enabled Group Blog.
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
			$activity_type = 'new_groupblog_comment';

		} else {

			// Get Group Site status.
			$is_groupsite = $this->groupblog->site->is_commentpress_groupsite();

			// If on a CommentPress-enabled Group Site.
			if ( $is_groupsite ) {

				// Get Group ID from POST.
				global $bp_groupsites;
				$group_id = $bp_groupsites->activity->get_group_id_from_comment_form();

				// Kick out if not a Comment in a Group.
				if ( false === $group_id ) {
					return $activity;
				}

				// Set Activity Type.
				$activity_type = 'new_groupsite_comment';

			}

		}

		// Sanity check.
		if ( ! $is_groupblog && ! $is_groupsite ) {
			return $activity;
		}

		// Okay, let's get the Group object.
		$group = groups_get_group( [ 'group_id' => $group_id ] );

		// See if we already have the modified Activity for this Blog Comment.
		$params = [
			'user_id'           => $activity->user_id,
			'type'              => $activity_type,
			'item_id'           => $group_id,
			'secondary_item_id' => $activity->secondary_item_id,
		];

		$id = bp_activity_get_activity_id( $params );

		// If we don't find a modified item.
		if ( ! $id ) {

			// See if we have an unmodified Activity item.
			$params = [
				'user_id'           => $activity->user_id,
				'type'              => $activity->type,
				'item_id'           => $activity->item_id,
				'secondary_item_id' => $activity->secondary_item_id,
			];

			$id = bp_activity_get_activity_id( $params );

		}

		// If we found an Activity for this Blog Comment then overwrite the ID
		// to avoid having multiple Activities for every Blog Comment edit.
		if ( $id ) {
			$activity->id = $id;
		}

		// Get the Comment.
		$comment = get_comment( $activity->secondary_item_id );

		// Get the Post.
		$post = get_post( $comment->comment_post_ID );

		// Was it a registered User?
		if ( 0 !== (int) $comment->user_id ) {

			// Get User details.
			$user = get_userdata( $comment->user_id );

			// Construct User link.
			$user_link = bp_core_get_userlink( $activity->user_id );

		} else {

			// Show anonymous User.
			$user_link = '<span class="anon-commenter">' . __( 'Anonymous', 'commentpress-core' ) . '</span>';

		}

		// If on a CommentPress-enabled Group Blog.
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

		// If on a CommentPress-enabled Group Site.
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
		$group_link   = '<a href="' . bp_get_group_permalink( $group ) . '">' . esc_html( $group->name ) . '</a>';

		// Replace the necessary values to display in Group Activity stream.
		$activity->action = sprintf(
			/* translators: 1: The author, 2: The link to the comment, 3: The activity name, 4: The name of the Post, 5: The name of the group. */
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
		$activity->type = $activity_type;

		// Note: BuddyPress seemingly runs content through "wp_filter_kses". Sad face.

		// Prevent from firing again.
		remove_action( 'bp_activity_before_save', [ $this, 'activity_comment_custom' ] );

		// --<
		return $activity;

	}

	/**
	 * Add some meta for the Activity item.
	 *
	 * Done here because "bp_activity_after_save" doesn't seem to fire.
	 *
	 * @since 3.3
	 *
	 * @param object $activity The existing Activity object.
	 * @return object $activity The modified Activity object.
	 */
	public function activity_comment_meta( $activity ) {

		// Only deal with Comments.
		if ( 'new_groupblog_comment' !== $activity->type ) {
			return $activity;
		}

		// Only do this on CommentPress-enabled Group Blogs.
		if ( false === $this->groupblog->site->is_commentpress_groupblog() ) {
			return $activity;
		}

		// Set a meta value for the Site Text Format of the Post.
		$meta_value = $this->groupblog->site->text_format_get();
		$result     = bp_activity_update_meta( $activity->id, 'groupblogtype', 'groupblogtype-' . $meta_value );

		// Prevent from firing again.
		remove_action( 'bp_activity_after_save', [ $this, 'activity_comment_meta' ] );

		// --<
		return $activity;

	}

}
