<?php /*
================================================================================
CommentPress Modern Theme Comments
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

Comments template for CommentPress

--------------------------------------------------------------------------------
*/



// Do not delete these lines
if (!empty($_SERVER['SCRIPT_FILENAME']) AND 'comments_by_para.php' == basename($_SERVER['SCRIPT_FILENAME'])) {
	die ('Please do not load this page directly. Thanks!');
}



// add identifier ID
if ( isset( $post->ID ) ) {
	$comments_post_identifier = ' id="comments_post_identifier-'.$post->ID.'"';
}



?>
<!-- comments_by_para.php -->

<div class="sidebar_contents_wrapper">



<div class="comments_container"<?php echo $comments_post_identifier; ?>>



<?php if ('closed' == $post->comment_status) : ?>

	<!-- comments are closed. -->
	<h3 class="nocomments comments-closed"><span><?php _e( 'Comments are closed', 'commentpress-core' ); ?></span></h3>

<?php endif; ?>

<?php commentpress_get_comments_by_para(); ?>



<?php

// because AJAX may be routed via admin or front end
if ( defined( 'DOING_AJAX' ) AND DOING_AJAX ) {
	
	// skip
	
} else {

	// until WordPress supports a locate_theme_file() function, use filter
	$include = apply_filters( 
		'cp_template_comment_form',
		get_template_directory() . '/assets/templates/comment_form.php'
	);

	// include comment form
	include( $include );

}

?>



</div><!-- /comments_container -->



</div><!-- /sidebar_contents_wrapper -->



