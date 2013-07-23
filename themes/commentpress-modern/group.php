<?php
/*
Template Name: Group
*/

// get_users_of_blog() is deprecated in WP 3.1+
if ( version_compare( $wp_version, '3.1', '>=' ) ) {
	
	// set args
	$args = array(
		
		'orderby' => 'nicename'
		
	);
	
	// get users of this blog (blog_id is provided by default)
	$_users = get_users( $args );
	
} else {

	// get the users of this blog
	$_users = get_users_of_blog();
	
}



get_header(); ?>



<!-- group.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content">



<div class="post">



<h2 class="post_title"><?php _e( 'Group Members', 'commentpress-core' ); ?></h2>



<?php 

// did we get any?
if ( count( $_users ) > 0 ) {

	// open list
	echo '<ul id="group_list">'."\n";

	// loop
	foreach( $_users AS $_user ) {
	
		// exclude admin
		if( $_user->user_id != '1' ) {
		
			// open item
			echo '<li>'."\n";
		
			// show display name
			echo  '<a href="'.home_url().'/author/'.$_user->user_login.'/">'.$_user->display_name.'</a>';
			
			// close item
			echo '</li>'."\n\n";
		
		}
	
	}

	// close list
	echo '</ul>'."\n\n";

} ?>


</div><!-- /post -->



</div><!-- /content -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>