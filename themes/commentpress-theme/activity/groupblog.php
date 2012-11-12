<?php

/**
 * CommentPress - Activity Stream for Group Blogs
 *
 * This template is used by activity_sidebar.php to show each activity.
 */

// init group blog type
$groupblogtype = '';

// get activity type
$type = bp_get_activity_type();

// init same post
$same_post = '';

// for our types of activity...
if ( $type == 'new_groupblog_post' OR $type == 'new_groupblog_comment' ) {

	// get group id first
	$group_id = bp_get_activity_item_id();

	// get group blogtype
	$groupblogtype = groups_get_groupmeta( $group_id, 'groupblogtype' );
	
	// add space before if we have it
	if ( $groupblogtype ) { $groupblogtype = ' '.$groupblogtype; }
	
	/*
	// if it's a groupblog comment
	if ( $type == 'new_groupblog_comment' ) {
	
		// get post id of the comment
		$post_id = bp_get_activity_secondary_item_id();
		
		// get blog id
		$blog_id = groups_get_groupmeta( $group_id, 'groupblogtype' );
		
		// is it the current blog...
		if ( $blog_id = get_current_blog_id() ) {
		
			// is it the current post...
			global $post;
			if ( is_object( $post ) AND $post_id = $post->ID ) {
			
				// lastly, check if the comment is on a subpage
				// OH DEAR, BP doesn't store the ID of the comment :(
				
				// get comment
				$comment = get_comment(  );
				
				// init same post
				$same_post = ' comment_on_post';
				
			}
		
		}
	
	}
	*/
	
}

?>

<?php do_action( 'bp_before_activity_entry' ); ?>

<li class="<?php bp_activity_css_class(); echo $groupblogtype.$same_post; ?>" id="activity-<?php bp_activity_id(); ?>">

	<div class="comment-wrapper">
	
		<div class="comment-identifier">
		
			<a href="<?php bp_activity_user_link(); ?>"><?php bp_activity_avatar( 'width=32&height=32' ); ?></a>
			<?php bp_activity_action(); ?>
	
		</div>
	
		<div class="comment-content">
	
			<?php if ( bp_activity_has_content() ) : ?>
	
				<?php bp_activity_content_body(); ?>
	
			<?php endif; ?>
	
			<?php do_action( 'bp_activity_entry_content' ); ?>
	
		</div>
	
	</div>

</li>

<?php do_action( 'bp_after_activity_entry' ); ?>
