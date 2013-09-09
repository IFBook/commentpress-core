<?php

// access globals
global $post, $commentpress_core;



// init output
$_page_comments_output = '';

// is it commentable?
$_is_commentable = commentpress_is_commentable();

// if a commentable post...
if ( $_is_commentable ) {

	// set default phrase
	$_paragraph_text = __( 'Recent Comments on this Page', 'commentpress-core' );

	$_current_type = get_post_type();
	//print_r( $_current_type ); die();
	
	switch( $_current_type ) {
		
		// we can add more of these if needed
		case 'post': $_paragraph_text = __( 'Recent Comments on this Post', 'commentpress-core' ); break;
		case 'page': $_paragraph_text = __( 'Recent Comments on this Page', 'commentpress-core' ); break;
		
	}
	
	// set default
	$page_comments_title = apply_filters(
		'cp_activity_tab_recent_title_page', 
		$_paragraph_text
	);
	
	// get page comments
	$_page_comments_output = commentpress_get_comment_activity( 'post' );
	
}





// set default
$_all_comments_title = apply_filters(
	'cp_activity_tab_recent_title_blog', 
	__( 'Recent Comments in this Document', 'commentpress-core' )
);

// get all comments
$_all_comments_output = commentpress_get_comment_activity( 'all' );





// set maximum number to show - put into option?
$_max_members = 10;




?><!-- activity_sidebar.php -->

<div id="activity_sidebar" class="sidebar_container">



<div class="sidebar_header">

<h2>Activity</h2>

</div>



<div class="sidebar_minimiser">

<div class="sidebar_contents_wrapper">

<div class="comments_container">





<?php /* ?>
<div class="paragraph_wrappers">

<?php 

// until WordPress supports a locate_theme_file() function, use filter
$include = apply_filters( 
	'cp_template_user_links',
	get_template_directory() . '/assets/templates/user_links.php'
);

include( $include );

?>

</div>
<?php */ ?>



<?php 

// allow plugins to add their own activity headings here
do_action( 'commentpress_bp_activity_sidebar_before_activity' );

?>



<?php

// show page comments if we can
if ( $_is_commentable AND $_page_comments_output != '' ) {

// allow plugins to add their own activity heading here
do_action( 'commentpress_bp_activity_sidebar_before_page_comments' );

?><h3 class="activity_heading"><?php echo $page_comments_title; ?></h3>

<div class="paragraph_wrapper page_comments_output">

<?php echo $_page_comments_output; ?>

</div>

<?php

// allow plugins to add their own activity heading here
do_action( 'commentpress_bp_activity_sidebar_after_page_comments' );

} // end commentable post/page check






// show all comments from site if we can
if ( $_all_comments_output != '' ) {

// allow plugins to add their own activity heading here
do_action( 'commentpress_bp_activity_sidebar_before_all_comments' );

?><h3 class="activity_heading"><?php echo $_all_comments_title; ?></h3>

<div class="paragraph_wrapper all_comments_output">

<?php echo $_all_comments_output; ?>

</div>

<?php

// allow plugins to add their own activity heading here
do_action( 'commentpress_bp_activity_sidebar_after_all_comments' );

} // end comments from site check






/*
--------------------------------------------------------------------------------
This seems not to work because BP returns no values for the combination we want
--------------------------------------------------------------------------------
NOTE: raise a ticket on BP
--------------------------------------------------------------------------------
Also, need to make this kind of include file properly child-theme adaptable
--------------------------------------------------------------------------------


// access plugin
global $commentpress_core, $post;

// if we have the plugin enabled and it's BP
if ( 
	
	is_multisite() 
	AND is_object( $commentpress_core ) 
	AND $commentpress_core->is_buddypress() 
	AND $commentpress_core->is_groupblog() 
	
) {
	
	// check if this blog is a group blog...
	$group_id = get_groupblog_group_id( get_current_blog_id() );
	//print_r( $group_id ); die();
	
	// when this blog is a groupblog
	if ( !empty( $group_id ) ) {
	
		// get activities for our activities
		if ( bp_has_activities( array(
			
			// NO RESULTS!
			'object' => 'groups',
			'action' => 'new_groupblog_comment,new_groupblog_post',
			'primary_id' => $group_id
			'secondary_id' => $post_id
			
		) ) ) : ?>
			
			<h3 class="activity_heading">Recent Activity in this Workshop</h3>
	
			<div class="paragraph_wrapper">
			
			<ol class="comment_activity">
		
			<?php while ( bp_activities() ) : bp_the_activity(); ?>
		 
				<?php locate_template( array( 'activity/groupblog.php' ), true, false ); ?>
				
			<?php endwhile; ?>
			
			</ol>
			
			</div>
		 
		<?php
		
		endif; 

	}




} // end BP check
*/


?>

<?php



// access plugin
global $commentpress_core, $post, $blog_id;

// if we have the plugin enabled and it's Multisite BP
if (

	// test for multisite buddypress
	is_multisite() AND 
	is_object( $commentpress_core ) AND 
	$commentpress_core->is_buddypress()
	
) {



	
	// if on either groupblog or main BP blog
	if ( $commentpress_core->is_groupblog() OR bp_is_root_blog() ) {
	
		// get activities	
		if ( bp_has_activities( array(
	
			'scope' => 'groups',
			'action' => 'new_groupblog_comment,new_groupblog_post',
		
		) ) ) :
	
			// change header depending on logged in status
			if ( is_user_logged_in() ) {
		
				// set default
				$_section_header_text = apply_filters(
					'cp_activity_tab_recent_title_all_yours', 
					__( 'Recent Activity in your Documents', 'commentpress-core' )
				);
			
			} else { 
		
				// set default
				$_section_header_text = apply_filters(
					'cp_activity_tab_recent_title_all_public', 
					__( 'Recent Activity in Public Documents', 'commentpress-core' )
				);
		
			 } ?>

			<h3 class="activity_heading"><?php echo $_section_header_text; ?></h3>
		
			<div class="paragraph_wrapper workshop_comments_output">
		
			<ol class="comment_activity">
	
			<?php while ( bp_activities() ) : bp_the_activity(); ?>
	 
				<?php locate_template( array( 'activity/groupblog.php' ), true, false ); ?>
			
			<?php endwhile; ?>
		
			</ol>
		
			</div>
	 
		<?php endif; ?>

		<?php 
	
	} // end groupblog check
	
	
	
	// allow plugins to add their own activity headings here
	do_action( 'commentpress_bp_activity_sidebar_before_members' );
	
	
	
	// get recently active members
	if ( bp_has_members( 
	
		'user_id=0'.
		'&type=active'.
		'&per_page='.$_max_members.
		'&max='.$_max_members.
		'&populate_extras=1' 
		
	) ) : ?>
	
		<h3 class="activity_heading"><?php _e( 'Recently Active Members', 'commentpress-core' ); ?></h3>
	
		<div class="paragraph_wrapper active_members_output">
	
		<ul class="item-list cp-recently-active">
	
		<?php while ( bp_members() ) : bp_the_member(); ?>
	
			<li>
	
				<div class="item-avatar">
					<a href="<?php bp_member_permalink(); ?>"><?php bp_member_avatar(); ?></a>
				</div>
	
				<div class="item">

					<div class="item-title">
						<a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a>
					</div>
	
					<div class="item-meta"><span class="activity"><?php bp_member_last_active(); ?></span></div>
					
				</div>
	
				<div class="clear"></div>
	
			</li>
	
		<?php endwhile; ?>
	
		</ul>
	
		</div>
	
	<?php endif; ?>
	
	
	
	<?php 
	
	// get online members
	if ( bp_has_members( 
	
		'user_id=0'.
		'&type=online'.
		'&per_page='.$_max_members.
		'&max='.$_max_members.
		'&populate_extras=1' 
		
	) ) : ?>
	
		<h3 class="activity_heading"><?php _e( "Who's Online", 'commentpress-core' ); ?></h3>
	
		<div class="paragraph_wrapper online_members_output">
	
		<ul class="item-list cp-online-members">
	
		<?php while ( bp_members() ) : bp_the_member(); ?>
	
			<li>
	
				<div class="item-avatar">
					<a href="<?php bp_member_permalink(); ?>"><?php bp_member_avatar(); ?></a>
				</div>
	
				<div class="item">

					<div class="item-title">
						<a href="<?php bp_member_permalink(); ?>"><?php bp_member_name(); ?></a>
					</div>
	
					<div class="item-meta"><span class="activity"><?php bp_member_last_active(); ?></span></div>
	
				</div>
	
				<div class="clear"></div>
	
			</li>
	
		<?php endwhile; ?>
	
		</ul>
	
		</div>
	
	<?php endif; ?>
	
	
	<?php 



	// allow plugins to add their own activity headings here
	do_action( 'commentpress_bp_activity_sidebar_after_members' );
	
	
	
} // end BP check



// allow plugins to add their own activity headings here
do_action( 'commentpress_bp_activity_sidebar_after_activity' );



?>



<?php

/*
// prepare for ShareThis integration
if ( function_exists( 'sharethis_button' ) ) {
	// wrap in identifier
	echo '<h3 class="activity_heading">Share with ShareThis</h3>';
	echo '<div class="paragraph_wrapper">';
	echo '<p class="cp_share_this_buttons" style="padding: 10px 18px;">';
	sharethis_button();
	echo '</p>';
	echo '</div>';
}
*/

?>




</div><!-- /comments_container -->

</div><!-- /sidebar_contents_wrapper -->

</div><!-- /sidebar_minimiser -->



</div><!-- /activity_sidebar -->



