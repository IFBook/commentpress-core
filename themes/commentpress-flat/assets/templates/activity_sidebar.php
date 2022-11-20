<?php

// Access globals.
global $post, $commentpress_core;



// Init output.
$_page_comments_output = '';

// Is it commentable?
$_is_commentable = commentpress_is_commentable();

// If a commentable Post.
if ( $_is_commentable AND ! post_password_required() ) {

	// Get singular Post Type label.
	$current_type = get_post_type();
	$post_type = get_post_type_object( $current_type );

	/**
	 * Assign name of Post Type.
	 *
	 * @since 3.8.10
	 *
	 * @param str $singular_name The singular label for this Post Type.
	 * @param str $current_type The Post Type identifier.
	 * @return str $singular_name The modified label for this Post Type.
	 */
	$post_type_name = apply_filters( 'commentpress_lexia_post_type_name', $post_type->labels->singular_name, $current_type );

	// Construct "Recent Comments" phrase.
	$_paragraph_text = sprintf( __( 'Recent Comments on this %s', 'commentpress-core' ), $post_type_name );

	/**
	 * Allow plugins to set their own title here.
	 *
	 * @since 3.4
	 */
	$page_comments_title = apply_filters(
		'cp_activity_tab_recent_title_page',
		$_paragraph_text
	);

	// Get Page Comments.
	$_page_comments_output = commentpress_get_comment_activity( 'post' );

}



/**
 * Allow plugins to set their own title here.
 *
 * @since 3.4
 */
$_all_comments_title = apply_filters(
	'cp_activity_tab_recent_title_blog',
	__( 'Recent Comments in this Document', 'commentpress-core' )
);

// Get all Comments.
$_all_comments_output = commentpress_get_comment_activity( 'all' );



// Set maximum number to show - put into option?
$_max_members = 10;



?><!-- activity_sidebar.php -->

<div id="activity_sidebar" class="sidebar_container">



<div class="sidebar_header">

<h2><?php _e( 'Activity', 'commentpress-core' ); ?></h2>

</div>



<div class="sidebar_minimiser">

<div class="sidebar_contents_wrapper">

<div class="comments_container">



<?php

// Allow Widgets to be placed above Activity.
dynamic_sidebar( 'cp-activity-top' );

/**
 * Allow plugins to add their own Activity Headings here.
 *
 * @since 3.4.8
 */
do_action( 'commentpress_bp_activity_sidebar_before_activity' );

?>



<?php

// Show Page Comments if we can.
if ( $_is_commentable AND $_page_comments_output != '' ) {

/**
 * Allow plugins to add their own Activity Heading here.
 *
 * @since 3.4.8
 */
do_action( 'commentpress_bp_activity_sidebar_before_page_comments' );

?><h3 class="activity_heading"><?php echo $page_comments_title; ?></h3>

<div class="paragraph_wrapper page_comments_output">

<?php echo $_page_comments_output; ?>

</div>

<?php

/**
 * Allow plugins to add their own Activity Heading here.
 *
 * @since 3.4.8
 */
do_action( 'commentpress_bp_activity_sidebar_after_page_comments' );

} // End commentable Post/Page check.



// Show all Comments from Site if we can.
if ( $_all_comments_output != '' ) {

/**
 * Allow plugins to add their own Activity Heading here.
 *
 * @since 3.4.8
 */
do_action( 'commentpress_bp_activity_sidebar_before_all_comments' );

?><h3 class="activity_heading"><?php echo $_all_comments_title; ?></h3>

<div class="paragraph_wrapper all_comments_output">

<?php echo $_all_comments_output; ?>

</div>

<?php

// Allow plugins to add their own Activity Heading here.
do_action( 'commentpress_bp_activity_sidebar_after_all_comments' );

}



// Access plugin.
global $commentpress_core, $post, $blog_id;

// If we have the plugin enabled and it's Multisite BuddyPress.
if (
	is_multisite() AND
	is_object( $commentpress_core ) AND
	$commentpress_core->bp->is_buddypress()
) {

	// If on either Group Blog or main BuddyPress Blog.
	if ( $commentpress_core->bp->is_groupblog() OR bp_is_root_blog() ) {

		// Define args.
		$recent_groupblog_activity = [
			'scope' => 'groups',
			'action' => 'new_groupblog_comment,new_groupblog_post',
			'primary_id' => false,
		];

		// Get activities.
		if ( function_exists( 'bp_has_activities' ) AND bp_has_activities( $recent_groupblog_activity ) ) :

			// Change header depending on logged in status.
			if ( is_user_logged_in() ) {

				/**
				 * Allow plugins to set their own title here.
				 *
				 * @since 3.4.8
				 */
				$_section_header_text = apply_filters(
					'cp_activity_tab_recent_title_all_yours',
					__( 'Recent Activity in your Documents', 'commentpress-core' )
				);

			} else {

				/**
				 * Allow plugins to set their own title here.
				 *
				 * @since 3.4.8
				 */
				$_section_header_text = apply_filters(
					'cp_activity_tab_recent_title_all_public',
					__( 'Recent Activity in Public Documents', 'commentpress-core' )
				);

			 } ?>

			<h3 class="activity_heading"><?php echo $_section_header_text; ?></h3>

			<div class="paragraph_wrapper workshop_comments_output">

			<ol class="comment_activity">

			<?php while ( bp_activities() ) : bp_the_activity(); ?>

				<?php do_action( 'bp_before_activity_entry' ); ?>

				<li class="<?php bp_activity_css_class(); ?>" id="activity-<?php bp_activity_id(); ?>">

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

			<?php endwhile; ?>

			</ol>

			</div>

		<?php endif; ?>

		<?php

	}



	/**
	 * Allow plugins to add their own Activity Headings here.
	 *
	 * @since 3.4.8
	 */
	do_action( 'commentpress_bp_activity_sidebar_before_members' );



	// Define args.
	$members_recently_active = [
		'user_id' => 0,
		'type' => 'online',
		'per_page' => $_max_members,
		'max' => $_max_members,
		'populate_extras' => 1,
	];

	// Get recently active members.
	if ( bp_has_members( $members_recently_active ) ) : ?>

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

	// Define args.
	$members_online = [
		'user_id' => 0,
		'type' => 'online',
		'per_page' => $_max_members,
		'max' => $_max_members,
		'populate_extras' => 1,
	];

	// Get online members.
	if ( bp_has_members( $members_online ) ) : ?>

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



	/**
	 * Allow plugins to add their own Activity Headings here.
	 *
	 * @since 3.4.8
	 */
	do_action( 'commentpress_bp_activity_sidebar_after_members' );



} // End BuddyPress check.



/**
 * Allow plugins to add their own Activity Headings here.
 *
 * @since 3.4.8
 */
do_action( 'commentpress_bp_activity_sidebar_after_activity' );

// Allow Widgets to be placed below Activity.
dynamic_sidebar( 'cp-activity-bottom' );



?>



<?php

/*
// Prepare for ShareThis integration.
if ( function_exists( 'sharethis_button' ) ) {
	// Wrap in identifier.
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



