<?php /*
================================================================================
CommentPress Modern Theme Comments
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

Comments template for CommentPress Core.

--------------------------------------------------------------------------------
*/



// Do not delete these lines.
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) AND 'comments_by_para.php' == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
	die( 'Please do not load this page directly. Thanks!' );
}



// Add identifier ID.
if ( isset( $post->ID ) ) {
	$comments_post_identifier = ' id="comments_post_identifier-' . $post->ID . '"';
}



?>
<!-- comments_by_para.php -->

<div class="sidebar_contents_wrapper">



<?php do_action( 'commentpress_before_comments_container' ); ?>



<div class="comments_container"<?php echo $comments_post_identifier; ?>>



<?php if ('closed' == $post->comment_status) : ?>

	<!-- comments are closed. -->
	<h3 class="nocomments comments-closed"><span><?php _e( 'Comments are closed', 'commentpress-core' ); ?></span></h3>

<?php endif; ?>

<?php commentpress_get_comments_by_para(); ?>



<?php

/**
 * Allow plugins to precede comment form.
 *
 * @since 3.4
 */
do_action( 'commentpress_before_comment_form' );

// Because AJAX may be routed via admin or front end.
if ( defined( 'DOING_AJAX' ) AND DOING_AJAX ) {

	// Skip.

} else {

	/**
	 * Try to locate template using WP method.
	 *
	 * @since 3.4
	 *
	 * @param str The existing path returned by WordPress.
	 * @return str The modified path.
	 */
	$cp_comment_form = apply_filters(
		'cp_template_comment_form',
		locate_template( 'assets/templates/comment_form.php' )
	);

	// Load it if we find it.
	if ( $cp_comment_form != '' ) load_template( $cp_comment_form );

}

/**
 * Allow plugins to follow comment form.
 *
 * @since 3.4
 */
do_action( 'commentpress_after_comment_form' );

?>



</div><!-- /comments_container -->



<?php do_action( 'commentpress_after_comments_container' ); ?>



</div><!-- /sidebar_contents_wrapper -->



