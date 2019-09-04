<?php /*
================================================================================
CommentPress Default Theme Comments
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



?>



<!-- comments_by_para.php -->

<div class="sidebar_contents_wrapper">



<div class="comments_container">



<?php if ('closed' == $post->comment_status) : ?>

	<!-- comments are closed. -->
	<h3 class="nocomments comments-closed"><span><?php _e( 'Comments are closed', 'commentpress-core' ); ?></span></h3>

<?php endif; ?>

<?php commentpress_get_comments_by_para(); ?>



<?php

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

?>



</div><!-- /comments_container -->



</div><!-- /sidebar_contents_wrapper -->



