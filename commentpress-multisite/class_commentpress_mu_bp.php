<?php /*
================================================================================
Class CommentpressMultisiteBuddypress
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This class encapsulates all BuddyPress compatibility

--------------------------------------------------------------------------------
*/






/*
================================================================================
Class Name
================================================================================
*/

class CommentpressMultisiteBuddypress {






	/*
	============================================================================
	Properties
	============================================================================
	*/
	
	// parent object reference
	var $parent_obj;
	
	// admin object reference
	var $db;
	
	// BP: CommentPress Core enabled on all groupblogs, default is "false"
	var $cpmu_bp_force_commentpress = '0';
	
	// BP: default theme stylesheet for groupblogs (WP3.4+)
	var $cpmu_bp_groupblog_theme = 'commentpress-theme';
	
	// BP: default theme name for groupblogs (pre-WP3.4)
	var $cpmu_bp_groupblog_theme_name = 'CommentPress Default Theme';
	
	// BP: make groupblogs private by default
	var $cpmu_bp_groupblog_privacy = 1;
	
	// anon comments on groupblogs (commenters must be logged in and members)
	var $cpmu_bp_require_comment_registration = 1;
	
	



	/** 
	 * @description: initialises this object
	 * @param object $parent_obj a reference to the parent object
	 * @return object
	 * @todo: 
	 *
	 */
	function __construct( $parent_obj = null ) {
	
		// store reference to "parent" (calling obj, not OOP parent)
		$this->parent_obj = $parent_obj;
	
		// store reference to database wrapper (child of calling obj)
		$this->db = $this->parent_obj->db;
	
		// init
		$this->_init();

		// --<
		return $this;

	}
	
	
	



	/**
	 * PHP 4 constructor
	 */
	function CommentpressMultisiteBuddypress( $parent_obj = null ) {
		
		// is this php5?
		if ( version_compare( PHP_VERSION, "5.0.0", "<" ) ) {
		
			// call php5 constructor
			$this->__construct( $parent_obj );
			
		}
		
		// --<
		return $this;

	}






	/** 
	 * @description: set up all items associated with this object
	 * @todo: 
	 *
	 */
	function initialise() {
	
	}
	
	
	



	/** 
	 * @description: if needed, destroys all items associated with this object
	 * @todo: 
	 *
	 */
	function destroy() {
	
	}
	
	
	



//##############################################################################
	
	
	



	/*
	============================================================================
	PUBLIC METHODS
	============================================================================
	*/
	
	
	



	/*
	----------------------------------------------------------------------------
	BuddyPress Compatibility
	----------------------------------------------------------------------------
	*/
	
	/**
	 * @description: enqueue any styles and scripts needed by our public page
	 * @todo: 
	 *
	 */
	function add_frontend_styles() {
		
		/*
		// EXAMPLES:
		
		// add css
		wp_enqueue_style('cpbp-admin-style', CPBP_PLUGIN_URL . 'assets/css/admin.css');
		
		// add javascripts
		wp_enqueue_script( 'cpbp-admin-js', CPBP_PLUGIN_URL . 'assets/js/admin.js' );
		*/
		
		// dequeue BP Tempate Pack CSS, even if queued
		wp_dequeue_style( 'bp' );
		
	}
	
	
	



	/*
	----------------------------------------------------------------------------
	BP Groupblog Compatibility
	----------------------------------------------------------------------------
	*/
	
	/**
	 * Allow HTML comments and content in Multisite blogs
	 */
	function allow_html_content() {
		
		// using publish_posts for now - means author+
		if ( current_user_can( 'publish_posts' ) ) {

			// remove html filtering on content. Note - this has possible consequences...
			// see: http://wordpress.org/extend/plugins/unfiltered-mu/
			kses_remove_filters();
		
		}
	}
	
	
	



	/**
	 * Allow HTML in Activity items
	 */
	function activity_allowed_tags( $activity_allowedtags ) {
		
		// pretty pointless not to allow p tags when we encourage the use of TinyMCE!
		$activity_allowedtags['p'] = array();
		
		// --<
		return $activity_allowedtags;
		
	}
	
	
	



	/** 
	 * @description: add capability to edit own comments
	 * @todo:
	 * @see: http://scribu.net/wordpress/prevent-blog-authors-from-editing-comments.html
	 */
	function enable_comment_editing( $caps, $cap, $user_id, $args ) {
		
		// only apply this to queries for edit_comment cap
		if ( 'edit_comment' == $cap ) {
		
			// get comment
			$comment = get_comment( $args[0] );
			
			//print_r( array( 'comment' => $comment, 'user' => $user_id ) ); die();
			//print_r( array( 'caps' => $caps, 'cap' => $cap ) ); die();
			
			// is the user the same as the comment author?
			if ( $comment->user_id == $user_id ) {

				//$caps[] = 'moderate_comments';
				$caps = array('edit_posts');

			}
		
		}
		
		
		
		// --<
		return $caps;
		
	}






	/** 
	 * @description: override capability to comment based on group membership.
	 * @todo:
	 *
	 */
	function pre_comment_approved( $approved, $commentdata ) {
	
		//print_r( $commentdata ); die();
	
		global $wpdb;
		$blog_id = (int)$wpdb->blogid;
	
		// do we have groupblogs?
		if ( function_exists( 'get_groupblog_group_id' ) ) {
		
			// check if this blog is a group blog...
			$group_id = get_groupblog_group_id( $blog_id );
			
		}
		
		// when this blog is a groupblog
		if ( isset( $group_id ) AND is_numeric( $group_id ) ) {
		
			// is this user a member?
			if ( groups_is_user_member( $commentdata['user_id'], $group_id ) ) {
				
				// allow un-moderated commenting
				return 1;
				
			}
		
		}
		
		
		
		// pass through
		return $approved;
		
	}
	
	
	
	
	
	
	/*
	// a nicer way?
	add_action( 'preprocess_comment', 'my_check_comment', 1 );
	
	function my_check_comment( $commentdata ) {
	
		// Get the user ID of the comment author.
		$user_id = absint( $commentdata['user_ID'] );
	
		// If comment author is a registered user, approve the comment.
		if ( 0 < $user_id )
			add_filter( 'pre_comment_approved', 'my_approve_comment' );
		else
			add_filter( 'pre_comment_approved', 'my_moderate_comment' );
	
		return $commentdata;
	}
	
	function my_approve_comment( $approved ) {
		$approved = 1;
		return $approved;
	}
	
	function my_moderate_comment( $approved ) {
		if ( 'spam' !== $approved )
			$approved = 0;
		return $approved;
	}
	*/
	
	
	
	



	/** 
	 * @description: override "publicness" of groupblogs so that we can set the hide_sitewide
	 * property of the activity item (post or comment) depending on the group's setting.
	 * @todo: test if they are CP-enabled?
	 *
	 */
	function is_blog_public( $blog_public_option ) {
	
		global $wpdb;
		$blog_id = (int)$wpdb->blogid;
	
		// do we have groupblogs?
		if ( function_exists( 'get_groupblog_group_id' ) ) {
		
			// check if this blog is a group blog...
			$group_id = get_groupblog_group_id( $blog_id );
			
		}
		
		// when this blog is a groupblog
		if ( isset( $group_id ) AND is_numeric( $group_id ) ) {
		
			// always true - so that activities are registered
			return 1;
			
		} else {
		
			return $blog_public_option;
		
		}
		
	}
	
	
	



	/**
	 * groupblog_set_group_to_post_activity ( $activity )
	 *
	 * Record the blog activity for the group - amended from bp_groupblog_set_group_to_post_activity
	 */
	function groupblog_custom_comment_activity( $activity ) {
		
		//print_r( array( 'a1' => $activity ) );// die();
		
		// only deal with comments
		if ( ( $activity->type != 'new_blog_comment' ) ) return;
		
		// only do this on CP-enabled groupblogs
		if ( ( false === $this->_is_commentpress_groupblog() ) ) return;
		


		// get the group
		$blog_id = $activity->item_id;
		$group_id = get_groupblog_group_id( $blog_id );
		if ( !$group_id ) return;
		$group = groups_get_group( array( 'group_id' => $group_id ) );
		//print_r( $group ); die();
	
		// see if we already have the modified activity for this blog post
		$id = bp_activity_get_activity_id( array(
		
			'user_id' => $activity->user_id,
			'type' => 'new_groupblog_comment',
			'item_id' => $group_id,
			'secondary_item_id' => $activity->secondary_item_id
			
		) );
	
		// if we don't find a modified item...
		if ( !$id ) {
		
			// see if we have an unmodified activity item
			$id = bp_activity_get_activity_id( array(
			
				'user_id' => $activity->user_id,
				'type' => $activity->type,
				'item_id' => $activity->item_id,
				'secondary_item_id' => $activity->secondary_item_id
				
			) );
			
		}
	


		// If we found an activity for this blog comment then overwrite that to avoid having 
		// multiple activities for every blog comment edit
		if ( $id ) $activity->id = $id;
		


		// get the comment
		$comment = get_comment( $activity->secondary_item_id );
		//print_r( $comment ); //die();
		
		// get the post
		$post = get_post( $comment->comment_post_ID );
		//print_r( $post ); die();
		
		// was it a registered user?
		if ($comment->user_id != '0') {
		
			// get user details
			$user = get_userdata( $comment->user_id );
			
			// construct user link
			$user_link = bp_core_get_userlink( $activity->user_id );
			
		} else {
		
			// show anonymous user
			$user_link = '<span class="anon-commenter">'.__( 'Anonymous', 'commentpress-plugin' ).'</span>';
	
		}
			
		// allow plugins to override the name of the activity item
		$activity_name = apply_filters(
			'cp_activity_post_name',
			__( 'post', 'commentpress-plugin' )
		);
		
		// set key
		$key = '_cp_comment_page';
		
		// if the custom field has a value, we have a subpage comment...
		if ( get_comment_meta( $comment->comment_ID, $key, true ) != '' ) {
		
			// get comment's page from meta
			$page_num = get_comment_meta( $comment->comment_ID, $key, true );
			
			// get the url for the comment
			$link = commentpress_get_post_multipage_url( $page_num ).'#comment-'.$comment->comment_ID;
			
			// amend the primary link
			$activity->primary_link = $link;
			
			// init target link
			$target_post_link = '<a href="' . commentpress_get_post_multipage_url( $page_num, $post ) .'">' . esc_html( $post->post_title ) . '</a>';
			
		} else {
		
			// init target link
			$target_post_link = '<a href="' . get_permalink( $post->ID ) .'">' . esc_html( $post->post_title ) . '</a>';
			
		}
	
		// Replace the necessary values to display in group activity stream
		$activity->action = sprintf( 
			
			__( '%s left a %s on a %s %s in the group %s:', 'commentpress-plugin' ), 
			
			$user_link, 
			'<a href="' . $activity->primary_link .'">' . __( 'comment', 'commentpress-plugin' ) . '</a>', 
			$activity_name, 
			$target_post_link, 
			'<a href="' . bp_get_group_permalink( $group ) . '">' . esc_html( $group->name ) . '</a>' 
			
		);
		
		// apply group id
		$activity->item_id = (int)$group_id;
		
		// change to groups component
		$activity->component = 'groups';
		
		// having marked all groupblogs as public, we need to hide activity from them if the group is private
		// or hidden, so they don't show up in sitewide activity feeds.
		if ( 'public' != $group->status ) {
			$activity->hide_sitewide = true;
		} else {
			$activity->hide_sitewide = false;
		}
		
		// set unique type
		$activity->type = 'new_groupblog_comment';
		
		
		
		// note: BP seemingly runs content through wp_filter_kses (sad face)
		


		// prevent from firing again
		remove_action( 'bp_activity_before_save', array( $this, 'groupblog_custom_comment_activity' ) );
		
		
		// --<
		return $activity;
	
	}
	
	
	



	/** 
	 * @description: add some meta for the activity item - bp_activity_after_save doesn't seem to fire
	 * @todo: 
	 *
	 */
	function groupblog_custom_comment_meta( $activity ) {

		print_r( array( 'a' => $activity ) );
	
		// only deal with comments
		if ( ( $activity->type != 'new_groupblog_comment' ) ) return;
		
		// only do this on CP-enabled groupblogs
		if ( ( false === $this->_is_commentpress_groupblog() ) ) return;
		
		
		
		// set a meta value for the blog type of the post
		$meta_value = $this->_get_groupblog_type();
		print_r( array( 'a' => $activity ) );
		print_r( array( 'm' => $meta_value ) );
		$result = bp_activity_update_meta( $activity->id, 'groupblogtype', 'groupblogtype-'.$meta_value );
		print_r( array( 'r' => ( ( $result === true ) ? 't' : 'f' ) ) ); die();
		
		
		
		// prevent from firing again
		remove_action( 'bp_activity_after_save', array( $this, 'groupblog_custom_comment_meta' ) );
		
		
		
		// --<
		return $activity;
	
	}
	
	
	
	
	
	
	/**
	 * see: bp_groupblog_set_group_to_post_activity ( $activity )
	 *
	 * Record the blog post activity for the group - by Luiz Armesto
	 */
	function groupblog_custom_post_activity( $activity ) {
	
		// only on new blog posts
		if ( ( $activity->type != 'new_blog_post' ) ) return;
	
		// only on CP-enabled groupblogs
		if ( ( false === $this->_is_commentpress_groupblog() ) ) return;
		
		//print_r( array( 'a1' => $activity ) ); //die();
		
		
		// clarify data
		$blog_id = $activity->item_id;
		$post_id = $activity->secondary_item_id;
		$post = get_post( $post_id );
		
		
		
		// get group id
		$group_id = get_groupblog_group_id( $blog_id );
		if ( !$group_id ) return;
		
		// get group
		$group = groups_get_group( array( 'group_id' => $group_id ) );
		
		
		
		// see if we already have the modified activity for this blog post
		$id = bp_activity_get_activity_id( array(
		
			'user_id' => $activity->user_id,
			'type' => 'new_groupblog_post',
			'item_id' => $group_id,
			'secondary_item_id' => $activity->secondary_item_id
			
		) );
	
		// if we don't find a modified item...
		if ( !$id ) {
		
			// see if we have an unmodified activity item
			$id = bp_activity_get_activity_id( array(
			
				'user_id' => $activity->user_id,
				'type' => $activity->type,
				'item_id' => $activity->item_id,
				'secondary_item_id' => $activity->secondary_item_id
				
			) );
			
		}
	
		// If we found an activity for this blog post then overwrite that to avoid 
		// having multiple activities for every blog post edit
		if ( $id ) {
			$activity->id = $id;
		}
		
		// allow plugins to override the name of the activity item
		$activity_name = apply_filters(
			'cp_activity_post_name',
			__( 'post', 'commentpress-plugin' )
		);
		
		// default to standard BP author
		$activity_author = bp_core_get_userlink( $post->post_author );
	
		// compat with Co-Authors Plus
		if ( function_exists( 'get_coauthors' ) ) {
		
			// get multiple authors
			$authors = get_coauthors();
			//print_r( $authors ); die();
		
			// if we get some
			if ( !empty( $authors ) ) {
			
				// we only want to override if we have more than one...
				if ( count( $authors ) > 1 ) {
					
					// use the Co-Authors format of "name, name, name and name"
					$activity_author = '';
					
					// init counter
					$n = 1;
					
					// find out how many author we have
					$author_count = count( $authors );
				
					// loop
					foreach( $authors AS $author ) {
						
						// default to comma
						$sep = ', ';
						
						// if we're on the penultimate
						if ( $n == ($author_count - 1) ) {
						
							// use ampersand
							$sep = __( ' &amp; ', 'commentpress-plugin' );
							
						}
						
						// if we're on the last, don't add
						if ( $n == $author_count ) { $sep = ''; }
						
						// add name
						$activity_author .= bp_core_get_userlink( $author->ID );
						
						// and separator
						$activity_author .= $sep;
						
						// increment
						$n++;
						
					}
					
				}
				
			}
			
		}
		
		// if we're replacing an item, show different message...
		if ( $id ) {
			
			// replace the necessary values to display in group activity stream
			$activity->action = sprintf( 
			
				__( '%s updated a %s %s in the group %s:', 'commentpress-plugin' ),
				
				$activity_author, 
				$activity_name, 
				'<a href="' . get_permalink( $post->ID ) .'">' . esc_attr( $post->post_title ) . '</a>', 
				'<a href="' . bp_get_group_permalink( $group ) . '">' . esc_attr( $group->name ) . '</a>' 
				
			);
			
		} else {
		
			// replace the necessary values to display in group activity stream
			$activity->action = sprintf( 
			
				__( '%s wrote a new %s %s in the group %s:', 'commentpress-plugin' ),
				
				$activity_author, 
				$activity_name, 
				'<a href="' . get_permalink( $post->ID ) .'">' . esc_attr( $post->post_title ) . '</a>', 
				'<a href="' . bp_get_group_permalink( $group ) . '">' . esc_attr( $group->name ) . '</a>' 
				
			);
			
		}
		
		$activity->item_id = (int)$group_id;
		$activity->component = 'groups';
	
		// having marked all groupblogs as public, we need to hide activity from them if the group is private
		// or hidden, so they don't show up in sitewide activity feeds.
		if ( 'public' != $group->status ) {
			$activity->hide_sitewide = true;
		} else {
			$activity->hide_sitewide = false;
		}
			
		// CMW: assume groupblog_post is intended
		$activity->type = 'new_groupblog_post';
		
		//print_r( array( 'a2' => $activity ) ); die();
		
		
		// prevent from firing again
		remove_action( 'bp_activity_before_save', array( $this, 'groupblog_custom_post_activity' ) );
		
		
		
		// --<
		return $activity;
	
	}
	
	
	



	/** 
	 * @description: add some meta for the activity item
	 * @todo: 
	 *
	 */
	function groupblog_custom_post_meta( $activity ) {
	
		// only on new blog posts
		if ( ( $activity->type != 'new_groupblog_post' ) ) return;
	
		// only on CP-enabled groupblogs
		if ( ( false === $this->_is_commentpress_groupblog() ) ) return;
		
		
		
		// set a meta value for the blog type of the post
		$meta_value = $this->_get_groupblog_type();
		bp_activity_update_meta( $activity->id, 'groupblogtype', 'groupblogtype-'.$meta_value );

		
		
		// --<
		return $activity;
	
	}
	
	
	
	
	
	
	/**
	 * Add a filter option to the filter select box on group activity pages.
	 */
	function groupblog_comments_filter_option() { 
	
		// default name
		$comment_name = __( 'CommentPress Comments', 'commentpress-plugin' );
		
		// allow plugins to override the name of the option
		$comment_name = apply_filters( 'cp_groupblog_comment_name', $comment_name );
		
		// construct option
		$option = '<option value="new_groupblog_comment">'.$comment_name.'</option>'."\n";
	
		// print
		echo $option;
	
	}
	
	
	



	/** 
	 * @description: override the name of the filter item
	 * @todo: 
	 *
	 */
	function groupblog_posts_filter_option( $slug ) {
	
		// default name
		$_name = __( 'CommentPress Posts', 'commentpress-plugin' );
	
		// allow plugins to override the name of the option
		$_name = apply_filters( 'cp_groupblog_post_name', $_name );
		
		// construct option
		$option = '<option value="new_groupblog_post">'.$_name.'</option>'."\n";
		
		// print
		echo $option;
	
	}
	
	
	



	/** 
	 * @description: for group blogs, override the avatar with that of the group
	 * @todo: 
	 *
	 */
	function get_blog_avatar( $avatar, $blog_id = '', $args ){
	
		// did we get anything?
		//print_r( $blog_id ); die();
		
		// do we have groupblogs?
		if ( function_exists( 'get_groupblog_group_id' ) ) {
		
			// get the group id
			$group_id = get_groupblog_group_id( $blog_id );
			
		}
			
		// did we get a group for which this is the group blog?
		if ( isset( $group_id ) ) {
			
			// --<
			return bp_core_fetch_avatar( array( 'item_id' => $group_id, 'object' => 'group' ) );
		
		} else {
			
			// --<
			return $avatar;
	
		}
		
	}
	
	
	
	
	
	
	/** 
	 * @description: override the name of the sub-nav item
	 * @todo: 
	 *
	 */
	function filter_blog_name( $name ) {
	
		// get group blogtype
		$groupblog_type = groups_get_groupmeta( bp_get_current_group_id(), 'groupblogtype' );
		
		// did we get one?
		if ( $groupblog_type ) {
			
			// yes, it's a CommentPress Core-enabled groupblog
			return apply_filters(
				'cpmu_bp_groupblog_subnav_item_name', 
				__( 'Document', 'commentpress-plugin' )
			);
			
		}
		
		// --<
		return $name;
		
	}
	
	
	
	
	
	
	/** 
	 * @description: override the slug of the sub-nav item
	 * @todo: 
	 *
	 */
	function filter_blog_slug( $slug ) {
	
		// get group blogtype
		$groupblog_type = groups_get_groupmeta( bp_get_current_group_id(), 'groupblogtype' );
		
		// did we get one?
		if ( $groupblog_type ) {
			
			// yes, it's a CommentPress Core-enabled groupblog
			return apply_filters(
				'cpmu_bp_groupblog_subnav_item_slug', 
				'document'
			);
			
		}
		
		// --<
		return $slug;
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: override CommentPress "Title Page"
	 * @todo: 
	 *
	 */
	function filter_nav_title_page_title( $title ) {
		
		// --<
		return __( 'Document Home Page', 'cpmsextras' );
	
	}
	
	
	
	
	

	/** 
	 * @description: remove group blogs from blog list
	 * @todo: 
	 *
	 */
	function remove_groupblog_from_loop( $b, $blogs ) {
	
		//print_r( array( 'b' => $b, 'blogs' => $blogs ) ); die();
		
		// loop through them
		foreach ( $blogs->blogs as $key => $blog ) {
			
			// exclude if it's a group blog
			if ( function_exists( 'groupblog_group_id' ) ) {
				
				// get group id
				$group_id = get_groupblog_group_id( $blog->blog_id );
				//print_r( array( 'g' => $group_id ) );
				
				// did we get one?
				if ( is_numeric( $group_id ) ) {
				
					// exclude
					unset( $blogs->blogs[$key] );
					
					// recalculate global values
					$blogs->blog_count = $blogs->blog_count - 1;
					$blogs->total_blog_count = $blogs->total_blog_count - 1;
					$blogs->pag_num = $blogs->pag_num - 1;
		
				}
			
			}
			
		}
		
		//die();
		
		//print_r( array( 'b' => $b, 'blogs' => $blogs ) ); die();
	
		/* Renumber the array keys to account for missing items */
		$blogs_new = array_values( $blogs->blogs );
		$blogs->blogs = $blogs_new;
		
		return $blogs;
	
	}
	
	
	
	
	
	
	/** 
	 * @description: override the name of the button on the BP "blogs" screen
	 * @todo: 
	 *
	 */
	function get_blogs_visit_blog_button( $button ) {
		
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
		//print_r( $button ); die();
		
		// init
		$blogtype = 'blog';
		
		// access global
		global $blogs_template;

		// do we have groupblogs enabled?
		if ( function_exists( 'get_groupblog_group_id' ) ) {
		
			// get group id
			$group_id = get_groupblog_group_id( $blogs_template->blog->blog_id );
			
			// yes, is this blog a groupblog? 
			if ( is_numeric( $group_id ) ) {
			
				// is it CommentPress Core-enabled?
			
				// get group blogtype
				$groupblog_type = groups_get_groupmeta( $group_id, 'groupblogtype' );
				
				// did we get one?
				if ( $groupblog_type ) {
				
					// yes
					$blogtype = 'commentpress-groupblog';
		
				} else {
					
					// standard groupblog
					$blogtype = 'groupblog';

				}
				
			}
			
		} else {
		
			// TODO: is this blog CommentPress Core-enabled?
			// we cannot do this without switch_to_blog at the moment...
			$blogtype = 'blog';
			
		}
		
		
		
		// switch by blogtype
		switch ( $blogtype ) {
			
			// standard sub-site
			case 'blog':
				$label = __( 'View Site', 'commentpress-plugin' );
				$button['link_text'] = $label;
				$button['link_title'] = $label;
				break;
		
			// CommentPress sub-site
			case 'commentpress':
				$label = __( 'View Document', 'commentpress-plugin' );
				$button['link_text'] = apply_filters( 'cp_get_blogs_visit_blog_button', $label );
				$button['link_title'] = apply_filters( 'cp_get_blogs_visit_blog_button', $label );
				break;
		
			// standard groupblog
			case 'groupblog':
				$label = __( 'View Group Blog', 'commentpress-plugin' );
				$button['link_text'] = $label;
				$button['link_title'] = $label;
				break;
		
			// CommentPress sub-site
			case 'commentpress-groupblog':
				$label = __( 'View Document', 'commentpress-plugin' );
				$button['link_text'] = apply_filters( 'cp_get_blogs_visit_groupblog_button', $label );
				$button['link_title'] = apply_filters( 'cp_get_blogs_visit_groupblog_button', $label );
				break;
		
		}
		
		
		
		// --<
		return $button;
	
	}
	
	
	
	
	
	
	/** 
	 * @description: hook into the group blog signup form
	 * @todo: 
	 *
	 */
	function signup_blogform( $errors ) {
	
		// apply to group blog signup form?
		if ( bp_is_groups_component() ) {
		
			// hand off to private method
			$this->_create_groupblog_options();
		
		} else {
		
			// hand off to private method
			$this->_create_blog_options();
		
		}
	
	}
	
	
	
	
	
	
	/** 
	 * @description: hook into wpmu_new_blog and target plugins to be activated
	 * @todo: 
	 *
	 */
	function wpmu_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	
		// test for presence of our checkbox variable in _POST
		if ( isset( $_POST['cpbp-groupblog'] ) AND $_POST['cpbp-groupblog'] == '1' ) {
		
			// hand off to private method
			$this->_create_groupblog( $blog_id, $user_id, $domain, $path, $site_id, $meta );
		
		} else {
		
			// test for presence of our checkbox variable in _POST
			if ( isset( $_POST['cpbp-new-blog'] ) AND $_POST['cpbp-new-blog'] == '1' ) {
				
				// hand off to private method
				$this->_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta );
			
			}
	
		}
		
	}
	

	
	
	
	
	/** 
	 * @description: override the title of the "Create a new document" link
	 * @todo: 
	 *
	 */
	function user_links_new_site_title() {
	
		// override default link name
		return apply_filters(
			'cpmu_bp_create_new_site_title', 
			__( 'Create a New Site', 'commentpress-plugin' )
		);
	
	}
	
	
	
	
	
	
	
	/**
	 * groupblog_privacy_check()
	 *
	 * Check if a non-public group is being accessed by a user who is not a member of the group
	 * Adapted from code in mahype's fork of BP Groupblog plugin, but not accepted because there
	 * may be cases where private groups have public groupblogs. Ours is not such a case.
	 */
	function groupblog_privacy_check() {
	
		// check our site option
		if ( $this->db->option_get( 'cpmu_bp_groupblog_privacy' ) != '1' ) { return; }
		
		
		
		global $blog_id, $current_user;
		
		// if is not the main blog but we do have a blog ID...
		if( !is_main_site() AND isset( $blog_id ) AND is_numeric( $blog_id ) ) {
		
			// do we have groupblog active?
			if ( function_exists( 'get_groupblog_group_id' ) ) {
			
				// get group ID for this blog
				$group_id = get_groupblog_group_id( $blog_id );
				
				// if we get one...
				if( is_numeric( $group_id ) ) {
					
					// get the group object
					$group = new BP_Groups_Group( $group_id );
					
					// if group is not public...
					if( $group->status != 'public' ) {
					
						// is the current user a member of the blog?
						if ( !is_user_member_of_blog( $current_user->ID, $blog_id ) ) {
						
							// no - redirect to network home, but allow overrides
							wp_redirect( apply_filters( 'bp_groupblog_privacy_redirect_url', network_site_url() ) );
							exit;
		
						}
						
					}
				
				}
			
			}
			
		}
		
	}
	





//##############################################################################
	
	
	



	/*
	============================================================================
	PRIVATE METHODS
	============================================================================
	*/
	
	
	



	/** 
	 * @description: object initialisation
	 * @todo:
	 *
	 */
	function _init() {
	
		// register hooks
		$this->_register_hooks();
		
	}
	
	
	



	/** 
	 * @description: register Wordpress hooks
	 * @todo: 
	 *
	 */
	function _register_hooks() {
		
		// enable html comments and content for authors
		add_action( 'init', array( $this, 'allow_html_content' ) );
		
		// check for the privacy of a groupblog
		add_action( 'init', array( $this, 'groupblog_privacy_check' ) );
		
		// add some tags to the allowed tags in activities
		add_filter( 'bp_activity_allowed_tags', array( $this, 'activity_allowed_tags' ), 20 );
		
		// allow comment authors to edit their own comments
		add_filter( 'map_meta_cap', array( $this, 'enable_comment_editing' ), 10, 4 );

		// amend comment activity
		add_filter( 'pre_comment_approved', array( $this, 'pre_comment_approved' ), 99, 2 );
		//add_action( 'preprocess_comment', 'my_check_comment', 1 );
		
		// override "publicness" of groupblogs
		add_filter( 'bp_is_blog_public', array( $this, 'is_blog_public' ), 20, 1 );
	
		// amend activity
		add_action( 'bp_loaded', array( $this, '_groupblog_activity_mods' ), 30 );
	
		// get group avatar when listing groupblogs
		add_filter( 'bp_get_blog_avatar', array( $this, 'get_blog_avatar' ), 20, 3 );
		
		// filter bp-groupblog defaults
		add_filter( 'bp_groupblog_subnav_item_name', array( $this, 'filter_blog_name' ), 20 );
		add_filter( 'bp_groupblog_subnav_item_slug', array( $this, 'filter_blog_slug' ), 20 );
		
		// override CommentPress "Title Page"
		add_filter( 'cp_nav_title_page_title', array( $this, 'filter_nav_title_page_title' ), 20 );
		
		// override the name of the button on the BP "blogs" screen
		// to override this, just add the same filter with a priority of 21 or greater
		add_filter( 'bp_get_blogs_visit_blog_button', array( $this, 'get_blogs_visit_blog_button' ), 20 );
		
		// we can remove groupblogs from the blog list, but cannot update the total_blog_count_for_user
		// that is displayed on the tab *before* the blog list is built - hence filter disabled for now
		//add_filter( 'bp_has_blogs', array( $this, 'remove_groupblog_from_loop' ), 20, 2 );
		
		// add form elements to groupblog form
		add_action( 'signup_blogform', array( $this, 'signup_blogform' ) );
		
		// add form elements to signup form
		add_action( 'cpmu_bp_after_blog_details_fields', array( $this, 'signup_blogform' ) );
		
		// activate blog-specific CommentPress Core plugin
		// added @ priority 20 because BP Groupblog adds its action at the default 10 and 
		// we want it to have done its stuff before we do ours...
		add_action( 'wpmu_new_blog', array( $this, 'wpmu_new_blog' ), 20, 6 );
	
		// register any public styles
		add_action( 'wp_enqueue_scripts', array( $this, 'add_frontend_styles' ), 20 );
	
		// override CommentPress "Create New Document" text
		add_filter( 'cp_user_links_new_site_title', array( $this, 'user_links_new_site_title' ), 21 );
		add_filter( 'cp_site_directory_link_title', array( $this, 'user_links_new_site_title' ), 21 );
		add_filter( 'cp_register_new_site_page_title', array( $this, 'user_links_new_site_title' ), 21 );
		
		// override groupblog theme, if the bp-groupblog default theme is not a CommentPress Core one
		add_filter( 'cp_forced_theme_slug', array( $this, '_get_groupblog_theme' ), 20, 1 );
		add_filter( 'cp_forced_theme_name', array( $this, '_get_groupblog_theme' ), 20, 1 );
		
		// is this the back end?
		if ( is_admin() ) {
		
			// anything specifically for WP Admin
			
			// add options to network settings form
			add_filter( 'cpmu_network_options_form', array( $this, '_network_admin_form' ), 20 );
				
			// add options to reset array
			add_filter( 'cpmu_db_bp_options_get_defaults', array( $this, '_get_default_settings' ), 20, 1 );
				
			// hook into Network BuddyPress form update
			add_action( 'cpmu_db_options_update', array( $this, '_buddypress_admin_update' ), 20 );
			
		} else {
		
			// anything specifically for Front End

			// add filter options for the post and comment activities as late as we can
			// so that bp-groupblog's action can be removed
			add_action( 'bp_setup_globals', array( $this, '_groupblog_filter_options' ) );
			
		}
		
	}
	
	
	



	/**
	 * _groupblog_filter_options()
	 *
	 * Add a filter actions once BuddyPress is loaded.
	 */
	function _groupblog_filter_options() {
		
		// remove bp-groupblog's contradictory option
		remove_action( 'bp_group_activity_filter_options', 'bp_groupblog_posts' );
		
		// add our consistent one
		add_action( 'bp_activity_filter_options', array( $this, 'groupblog_posts_filter_option' ) );
		add_action( 'bp_group_activity_filter_options', array( $this, 'groupblog_posts_filter_option' ) );
		add_action( 'bp_member_activity_filter_options', array( $this, 'groupblog_posts_filter_option' ) );
		
		// add our comments
		add_action( 'bp_activity_filter_options', array( $this, 'groupblog_comments_filter_option' ) );
		add_action( 'bp_group_activity_filter_options', array( $this, 'groupblog_comments_filter_option' ) );
		add_action( 'bp_member_activity_filter_options', array( $this, 'groupblog_comments_filter_option' ) );
		
	}
	
	
	



	/**
	 * _groupblog_activity_mods()
	 *
	 * Amend Activity Methods once BuddyPress is loaded.
	 */
	function _groupblog_activity_mods() {
		
		// allow lists in activity content
		add_action( 'bp_activity_allowed_tags', array( $this, '_activity_allowed_tags' ), 20, 1 );
		
		// ditch bp-groupblog's post activity action
		remove_action( 'bp_activity_before_save', 'bp_groupblog_set_group_to_post_activity' );

		// add custom comment activity to bp-groupblog
		add_action( 'bp_activity_before_save', array( $this, 'groupblog_custom_comment_activity' ), 20, 1 );
		
		// implement our own post activity
		add_action( 'bp_activity_before_save', array( $this, 'groupblog_custom_post_activity' ), 20, 1 );

		// these don't seem to fire to allow us to add our meta values for the items...
		// instead, I'm trying to store the blog_type as group meta data
		//add_action( 'bp_activity_after_save', array( $this, 'groupblog_custom_comment_meta' ), 20, 1 );
		//add_action( 'bp_activity_after_save', array( $this, 'groupblog_custom_post_meta' ), 20, 1 );
		
	}
	
	
	



	/**
	 * @description: allow our TinyMCE comment markup in activity content
	 * @todo:
	 */
	function _activity_allowed_tags( $activity_allowedtags ) {
		
		//print_r( $activity_allowedtags ); die();
		
		// lists
		$activity_allowedtags['ul'] = array();
		$activity_allowedtags['ol'] = array();
		$activity_allowedtags['li'] = array();
		
		// bold
		$activity_allowedtags['strong'] = array();
		
		// italic
		$activity_allowedtags['em'] = array();
		
		// underline
		$activity_allowedtags['span']['style'] = array();
		
		// --<
		return $activity_allowedtags;
		
	}
	
	
	



	/** 
	 * @description: hook into the groupblog create screen
	 * @todo:
	 *
	 */
	function _create_groupblog_options() {
	
		global $bp, $groupblog_create_screen;
		
		$blog_id = get_groupblog_blog_id();
		
		if ( !$groupblog_create_screen && $blog_id != '' ) {
		
			// existing blog and group - do we need to present any options?
		
		} else {
		
			// creating a new group - no groupblog exists yet
			// NOTE: need to check that our context is right
			
			// get force option
			$forced = $this->db->option_get( 'cpmu_bp_force_commentpress' );
			
			// are we force-enabling CommentPress Core?
			if ( $forced ) {
				
				// set hidden element
				$forced_html = '
				<input type="hidden" value="1" id="cpbp-groupblog" name="cpbp-groupblog" />
				';
	
				// define text, but allow overrides
				$text = apply_filters( 
					'cp_groupblog_options_signup_text_forced',
					__( 'Select the options for your new CommentPress-enabled blog. Note: if you choose an existing blog as a group blog, setting these options will have no effect.', 'commentpress-plugin' )
				);
				
			} else {
			
				// set checkbox
				$forced_html = '
				<div class="checkbox">
					<label for="cpbp-groupblog"><input type="checkbox" value="1" id="cpbp-groupblog" name="cpbp-groupblog" /> '.__( 'Enable CommentPress', 'commentpress-plugin' ).'</label>
				</div>
				';
						
				// define text, but allow overrides
				$text = apply_filters( 
					'cp_groupblog_options_signup_text',
					__( 'When you create a group blog, you can choose to enable it as a CommentPress blog. This is a "one time only" option because you cannot disable CommentPress from here once the group blog is created. Note: if you choose an existing blog as a group blog, setting this option will have no effect.', 'commentpress-plugin' )
				);
				
			}
			
			
	
			// off by default
			$has_workflow = false;
		
			// init output
			$workflow_html = '';
		
			// allow overrides
			$has_workflow = apply_filters( 'cp_blog_workflow_exists', $has_workflow );
			
			// if we have workflow enabled, by a plugin, say...
			if ( $has_workflow !== false ) {
			
				// define workflow label
				$workflow_label = __( 'Enable Custom Workflow', 'commentpress-plugin' );
				
				// allow overrides
				$workflow_label = apply_filters( 'cp_blog_workflow_label', $workflow_label );
				
				// show it
				$workflow_html = '
				
				<div class="checkbox">
					<label for="cp_blog_workflow"><input type="checkbox" value="1" id="cp_blog_workflow" name="cp_blog_workflow" /> '.$workflow_label.'</label>
				</div>

				';
			
			}
			
			
			
			// assume no types
			$types = array();
			
			// init output
			$type_html = '';
		
			// but allow overrides for plugins to supply some
			$types = apply_filters( 'cp_blog_type_options', $types );
			
			// if we got any, use them
			if ( !empty( $types ) ) {
			
				// define blog type label
				$type_label = __( 'Document Type', 'commentpress-plugin' );
				
				// allow overrides
				$type_label = apply_filters( 'cp_blog_type_label', $type_label );
				
				// construct options
				$type_option_list = array();
				$n = 0;
				foreach( $types AS $type ) {
					$type_option_list[] = '<option value="'.$n.'">'.$type.'</option>';
					$n++;
				}
				$type_options = implode( "\n", $type_option_list );
				
				// show it
				$type_html = '
				
				<div class="dropdown">
					<label for="cp_blog_type">'.$type_label.'</label> <select id="cp_blog_type" name="cp_blog_type">
					
					'.$type_options.'
					
					</select>
				</div>

				';
			
			}
			
			
			
			// construct form
			$form = '

			<br />
			<div id="cp-multisite-options">

				<h3>'.__( 'CommentPress Options', 'commentpress-plugin' ).'</h3>

				<p>'.$text.'</p>

				'.$forced_html.'

				'.$workflow_html.'

				'.$type_html.'

			</div>

			';
			
			echo $form;
			
		}
		
	}
	
	
	



	/** 
	 * @description: create a blog that is a groupblog
	 * @todo:
	 *
	 */
	function _create_groupblog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	
		// get group id before switch
		$group_id = isset( $_COOKIE['bp_new_group_id'] ) 
					? $_COOKIE['bp_new_group_id'] 
					: bp_get_current_group_id();



		// wpmu_new_blog calls this *after* restore_current_blog, so we need to do it again
		switch_to_blog( $blog_id );
		
		// activate CommentPress Core
		$this->db->install_commentpress();
		
		
		
		// access core
		global $commentpress_core;
			


		// TODO: create admin page settings for WordPress options
		
		
		
		// show posts by default (may be overridden)
		$posts_or_pages = 'post';
	
		// allow plugin overrides
		$posts_or_pages = apply_filters( 'cp_posts_or_pages_in_toc', $posts_or_pages );
	
		// TOC = posts
		$commentpress_core->db->option_set( 'cp_show_posts_or_pages_in_toc', $posts_or_pages );
		
		// if we opted for posts...
		if ( $posts_or_pages == 'post' ) {
	
			// TOC shows extended posts by default (may be overridden)
			$extended_toc = 1;
		
			// allow plugin overrides
			$extended_toc = apply_filters( 'cp_extended_toc', $extended_toc );
		
			// TOC shows extended posts
			$commentpress_core->db->option_set( 'cp_show_extended_toc', $extended_toc );
			
		}
	
	
		
		// get blog type (saved already)
		$cp_blog_type = $commentpress_core->db->option_get( 'cp_blog_type' );
		
		// did we get a group id before we switched blogs?
		if ( isset( $group_id ) ) {

			// allow plugins to override the blog type - for example if workflow is enabled, 
			// it might become a new blog type as far as buddypress is concerned
			$_blog_type = apply_filters( 'cp_get_group_meta_for_blog_type', $cp_blog_type, $cp_blog_workflow );

			// set the type as group meta info
			// we also need to change this when the type is changed from the CP admin page
			groups_update_groupmeta( $group_id, 'groupblogtype', 'groupblogtype-'.$_blog_type );
		
		}
		
	
	
		// save
		$commentpress_core->db->options_save();
		
		
		
		// ---------------------------------------------------------------------
		// WordPress Internal Configuration
		// ---------------------------------------------------------------------


		
		// get commenting option
		$anon_comments = $this->db->option_get( 'cpmu_bp_require_comment_registration' ) == '1' ? 1 : 0;
		
		// anonymous commenting (may be overridden by admin option)
		$anon_comments = apply_filters( 
			'cp_require_comment_registration', 
			$anon_comments
		);
	
		// update wp option
		update_option( 'comment_registration', $anon_comments );
		
		
		
		// get all network-activated plugins
		$active_sitewide_plugins = maybe_unserialize( get_site_option( 'active_sitewide_plugins' ) );
		
		// did we get any?
		if ( is_array( $active_sitewide_plugins ) AND count( $active_sitewide_plugins ) > 0 ) {
		
			// loop through them
			foreach( $active_sitewide_plugins AS $plugin_path => $plugin_data ) {
			
				// if we've got BuddyPress Group Email Subscription network-installed
				if ( false !== strstr( $plugin_path, 'bp-activity-subscription.php' ) ) {
				
					// switch comments_notify off
					update_option( 'comments_notify', 0 );
					
					// no need to carry on
					break;
							
				}
		
			}
		
		}
		
		
		
		// switch back
		restore_current_blog();
		
	}
	
	
	



	/** 
	 * @description: hook into the blog create screen on registration page
	 * @todo:
	 *
	 */
	function _create_blog_options() {
	
		// get force option
		$forced = $this->db->option_get( 'cpmu_force_commentpress' );
		
		// are we force-enabling CommentPress Core?
		if ( $forced ) {
			
			// set hidden element
			$forced_html = '
			<input type="hidden" value="1" id="cpmu-new-blog" name="cpmu-new-blog" />
			';

			// define text
			$text = __( 'Select the options for your new CommentPress document.', 'commentpress-plugin' );
			
		} else {
		
			// set checkbox
			$forced_html = '
			<div class="checkbox">
				<label for="cpmu-new-blog"><input type="checkbox" value="1" id="cpmu-new-blog" name="cpmu-new-blog" /> '.__( 'Enable CommentPress', 'commentpress-plugin' ).'</label>
			</div>
			';
					
			// define text
			$text = __( 'Do you want to make the new site a CommentPress document?', 'commentpress-plugin' );
			
		}
		
		

		// off by default
		$has_workflow = false;
	
		// init output
		$workflow_html = '';
	
		// allow overrides
		$has_workflow = apply_filters( 'cp_blog_workflow_exists', $has_workflow );
		
		// if we have workflow enabled, by a plugin, say...
		if ( $has_workflow !== false ) {
		
			// define workflow label
			$workflow_label = __( 'Enable Custom Workflow', 'commentpress-plugin' );
			
			// allow overrides
			$workflow_label = apply_filters( 'cp_blog_workflow_label', $workflow_label );
			
			// show it
			$workflow_html = '
			
			<div class="checkbox">
				<label for="cp_blog_workflow"><input type="checkbox" value="1" id="cp_blog_workflow" name="cp_blog_workflow" /> '.$workflow_label.'</label>
			</div>

			';
		
		}
		
		
		
		// assume no types
		$types = array();
		
		// init output
		$type_html = '';
	
		// but allow overrides for plugins to supply some
		$types = apply_filters( 'cp_blog_type_options', $types );
		
		// if we got any, use them
		if ( !empty( $types ) ) {
		
			// define blog type label
			$type_label = __( 'Document Type', 'commentpress-plugin' );
			
			// allow overrides
			$type_label = apply_filters( 'cp_blog_type_label', $type_label );
			
			// construct options
			$type_option_list = array();
			$n = 0;
			foreach( $types AS $type ) {
				$type_option_list[] = '<option value="'.$n.'">'.$type.'</option>';
				$n++;
			}
			$type_options = implode( "\n", $type_option_list );
			
			// show it
			$type_html = '
			
			<div class="dropdown cp-workflow-type">
				<label for="cp_blog_type">'.$type_label.'</label> <select id="cp_blog_type" name="cp_blog_type">
				
				'.$type_options.'
				
				</select>
			</div>

			';
		
		}
		
		
		
		// construct form
		$form = '

		<br />
		<div id="cp-multisite-options">

			<h4>'.__( 'CommentPress Options', 'commentpress-plugin' ).'</h4>

			<p>'.$text.'</p>

			'.$forced_html.'

			'.$workflow_html.'

			'.$type_html.'

		</div>

		';
		
		echo $form;
		
	}
	
	
	



	/** 
	 * @description: create a blog that is not a groupblog
	 * @todo:
	 *
	 */
	function _create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {
	
		// wpmu_new_blog calls this *after* restore_current_blog, so we need to do it again
		switch_to_blog( $blog_id );
		
		// activate CommentPress Core
		$this->db->install_commentpress();
		
		// switch back
		restore_current_blog();
		
	}
	
	
	



	/** 
	 * @description: utility to wrap is_groupblog()
	 * @todo: 
	 *
	 */
	function _is_commentpress_groupblog() {
	
		// check if this blog is a CP groupblog
		global $commentpress_core;
		if ( 
		
			!is_null( $commentpress_core ) 
			AND is_object( $commentpress_core ) 
			AND $commentpress_core->is_groupblog() 
			
		) {
		
			return true;
			
		}
		
		return false;
		
	}
	
	
	
	
	
	
	/** 
	 * @description: utility to get blog_type
	 * @todo: 
	 *
	 */
	function _get_groupblog_type() {
	
		global $commentpress_core;
		
		// if we have the plugin
		if ( 
		
			!is_null( $commentpress_core ) 
			AND is_object( $commentpress_core )
			
		) {
			
			// --<
			return $commentpress_core->db->option_get( 'cp_blog_type' ) ;
		}
		
		
		
		// --<
		return false;
		
	}
	
	
	
	
	
	
	/** 
	 * @description: add our options to the network admin form
	 * @todo: 
	 *
	 */
	function _network_admin_form() {
	
		// define admin page
		$admin_page = '
<div id="cpmu_bp_admin_options">

<h3>'.__( 'BuddyPress &amp; Groupblog Settings', 'commentpress-plugin' ).'</h3>

<p>'.__( 'Configure how CommentPress interacts with BuddyPress and BP Groupblog.', 'commentpress-plugin' ).'</p>

<table class="form-table">

	<tr valign="top">
		<th scope="row"><label for="cpmu_bp_reset">'.__( 'Reset BuddyPress settings', 'commentpress-plugin' ).'</label></th>
		<td><input id="cpmu_bp_reset" name="cpmu_bp_reset" value="1" type="checkbox" /></td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cpmu_bp_force_commentpress">'.__( 'Make all new Groupblogs CommentPress-enabled', 'commentpress-plugin' ).'</label></th>
		<td><input id="cpmu_bp_force_commentpress" name="cpmu_bp_force_commentpress" value="1" type="checkbox"'.( $this->db->option_get( 'cpmu_bp_force_commentpress' ) == '1' ? ' checked="checked"' : '' ).' /></td>
	</tr>

	'.$this->_get_commentpress_themes().'

	<tr valign="top">
		<th scope="row"><label for="cpmu_bp_groupblog_privacy">'.__( 'Private Groups must have Private Groupblogs', 'commentpress-plugin' ).'</label></th>
		<td><input id="cpmu_bp_groupblog_privacy" name="cpmu_bp_groupblog_privacy" value="1" type="checkbox"'.( $this->db->option_get( 'cpmu_bp_groupblog_privacy' ) == '1' ? ' checked="checked"' : '' ).' /></td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cpmu_bp_require_comment_registration">'.__( 'Require user login to post comments on Groupblogs', 'commentpress-plugin' ).'</label></th>
		<td><input id="cpmu_bp_require_comment_registration" name="cpmu_bp_require_comment_registration" value="1" type="checkbox"'.( $this->db->option_get( 'cpmu_bp_require_comment_registration' ) == '1' ? ' checked="checked"' : '' ).' /></td>
	</tr>

	'.$this->_additional_buddypress_options().'

</table>

</div>
';
		
		
		
		// --<
		return $admin_page;

	}
	
	
	
	
	
	
	/**
	 * @description: get all CommentPress Core themes
	 * @todo: 
	 *
	 */
	function _get_commentpress_themes() {
	
		// get all themes
		if ( function_exists( 'wp_get_themes' ) ) {
		
			// get theme data the WP3.4 way...
			$themes = wp_get_themes(
				false,     // only error-free themes
				'network', // only network-allowed themes
				0          // use current blog as reference
			);
			
			// get currently selected theme
			$current_theme = $this->db->option_get('cpmu_bp_groupblog_theme');
			
		} else {
			
			// pre WP3.4 functions
			$themes = get_themes();
			
			// get currently selected theme
			$current_theme = $this->db->option_get('cpmu_bp_groupblog_theme_name');
			
		}
		
		// init
		$options = array();
		$element = '';
		
		// we must get *at least* one (the Default), but let's be safe
		if ( !empty( $themes ) ) {
		
			// loop
			foreach( $themes AS $theme ) {
				
				// is it a CommentPress Groupblog theme?
				if ( 
				
					in_array( 'commentpress', (array) $theme['Tags'] ) AND
					in_array( 'groupblog', (array) $theme['Tags'] )
					
				) {
				
					// is this WP3.4+?
					if ( function_exists( 'wp_get_themes' ) ) {
					
						// use stylesheet as theme data
						$theme_data = $theme->get_stylesheet();
						
					} else {
						
						// use name as theme data
						$theme_data = $theme['Title'];
						
					}
					
					// is it the currently selected theme?
					$selected = ( $current_theme == $theme_data ) ? ' selected="selected"' : '';
				
					// add to array
					$options[] = '<option value="'.$theme_data.'" '.$selected.'>'.$theme['Title'].'</option>';
				
				}
			
			}
			
			// did we get any?
			if ( !empty( $options ) ) {
			
				// implode
				$opts = implode( "\n", $options );
				
				// define element
				$element = '
				
	<tr valign="top">
		<th scope="row"><label for="cpmu_bp_groupblog_theme">'.__( 'Select theme for CommentPress Groupblogs', 'commentpress-plugin' ).'</label></th>
		<td><select id="cpmu_bp_groupblog_theme" name="cpmu_bp_groupblog_theme">
			'.$opts.'
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
	 * @description: get Groupblog theme as defined in Network BuddyPress admin
	 * @todo: 
	 *
	 */
	function _get_groupblog_theme( $default_theme ) {
		
		// get the theme we've defined as the default for groupblogs
		$theme = $this->db->option_get( 'cpmu_bp_groupblog_theme' );
		
		// --<
		return $theme;
	
	}
	
	
	
	
	
	
	/**
	 * @description: allow other plugins to hook into our multisite admin options
	 * @todo: 
	 *
	 */
	function _additional_buddypress_options() {
	
		// return whatever plugins send back
		return apply_filters(
			'cpmu_network_buddypress_options_form', 
			''
		);
	
	}
	
	
	



	/**
	 * @description: get default BuddyPress-related settings
	 * @todo: 
	 *
	 */
	function _get_default_settings( $existing_options ) {
	
		// is this WP3.4+?
		if ( function_exists( 'wp_get_themes' ) ) {
		
			// use stylesheet as theme data
			$theme_data = $this->cpmu_bp_groupblog_theme;
			
		} else {
			
			// use name as theme data
			$theme_data = $this->cpmu_bp_groupblog_theme_name;
			
		}
		
		// define defaults
		$defaults = array(
		
			// buddypress/groupblog defaults
			'cpmu_bp_force_commentpress' => $this->cpmu_bp_force_commentpress,
			'cpmu_bp_groupblog_privacy' => $this->cpmu_bp_groupblog_privacy,
			'cpmu_bp_require_comment_registration' => $this->cpmu_bp_require_comment_registration,
			'cpmu_bp_groupblog_theme' => $theme_data
		
		);
		
		// return defaults, but allow overrides and additions
		return apply_filters(
			
			// hook
			'cpmu_buddypress_options_get_defaults',
			$defaults
			
		);

	}
	
	
	



	/** 
	 * @description: hook into Network BuddyPress form update
	 * @todo: 
	 *
	 */
	function _buddypress_admin_update() {
	
		// database object
		global $wpdb;
		
		// init
		$cpmu_bp_force_commentpress = '0';
		$cpmu_bp_groupblog_privacy = '0';
		$cpmu_bp_require_comment_registration = '0';
	
		// get variables
		extract( $_POST );
		
		// force CommentPress Core to be enabled on all groupblogs
		$cpmu_bp_force_commentpress = $wpdb->escape( $cpmu_bp_force_commentpress );
		$this->db->option_set( 'cpmu_bp_force_commentpress', ( $cpmu_bp_force_commentpress ? 1 : 0 ) );
		
		// groupblog privacy synced to group privacy
		$cpmu_bp_groupblog_privacy = $wpdb->escape( $cpmu_bp_groupblog_privacy );
		$this->db->option_set( 'cpmu_bp_groupblog_privacy', ( $cpmu_bp_groupblog_privacy ? 1 : 0 ) );
		
		// default groupblog theme
		$cpmu_bp_groupblog_theme = $wpdb->escape( $cpmu_bp_groupblog_theme );
		$this->db->option_set( 'cpmu_bp_groupblog_theme', $cpmu_bp_groupblog_theme );
		
		// anon comments on groupblogs
		$cpmu_bp_require_comment_registration = $wpdb->escape( $cpmu_bp_require_comment_registration );
		$this->db->option_set( 'cpmu_bp_require_comment_registration', ( $cpmu_bp_require_comment_registration ? 1 : 0 ) );
		
	}
	
	
	
	
	
	
//##############################################################################
	
	
	



} // class ends
	
	
	



?>