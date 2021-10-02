<?php /*
================================================================================
CommentPress Modern Theme Comments
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

--------------------------------------------------------------------------------
*/



// Do not delete these lines.
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) AND 'comments.php' == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
	die( 'Please do not load this page directly. Thanks!' );
}



// First, test for password protection
if ( post_password_required() ) { ?>

<div class="sidebar_contents_wrapper">
<div class="comments_container">
	<h3 class="nocomments"><span><?php _e( 'Enter the password to view comments', 'commentpress-core' ); ?></span></h3>
</div><!-- /comments_container -->
</div><!-- /sidebar_contents_wrapper -->

<?php
	return;
}



// Declare access to globals.
global $commentpress_core, $post;

// If we have the plugin enabled.
if ( is_object( $commentpress_core ) ) {

	// Are we asking for in-page comments?
	if ( $commentpress_core->db->is_special_page() ) {

		// Include 'comments in page' template

		/**
		 * Try to locate template using WP method.
		 *
		 * @since 3.4
		 *
		 * @param str The existing path returned by WordPress.
		 * @return str The modified path.
		 */
		$cp_comments_in_page = apply_filters(
			'cp_template_comments_in_page',
			locate_template( 'assets/templates/comments_in_page.php' )
		);

		// Load it if we find it.
		if ( $cp_comments_in_page != '' ) load_template( $cp_comments_in_page );

		// --<
		return;

	} else {

		// Include comments split by paragraph template

		/**
		 * Try to locate template using WP method.
		 *
		 * @since 3.4
		 *
		 * @param str The existing path returned by WordPress.
		 * @return str The modified path.
		 */
		$cp_comments_by_para = apply_filters(
			'cp_template_comments_by_para',
			locate_template( 'assets/templates/comments_by_para.php' )
		);

		// Load it if we find it.
		if ( $cp_comments_by_para != '' ) load_template( $cp_comments_by_para );

		// --<
		return;

	}

}



// Fallback.
?>
<!-- comments.php -->

<div id="sidebar_contents_wrapper">



<div class="comments_container">



<?php if ( have_comments() ) : ?>



	<h3 id="para-heading-"><span class="heading-padder"><?php

	comments_number(
		'<span>0</span> comments',
		'<span>1</span> comment',
		'<span>%</span> comments'
	);

	?> <?php _e( 'on the whole page', 'commentpress-core' ); ?></span></h3>



	<div class="paragraph_wrapper">

		<ol class="commentlist">

		<?php wp_list_comments( [
			'type'=> 'comment',
			'reply_text' => __( 'Reply to this comment', 'commentpress-core' ),
			'callback' => 'commentpress_comments',
		] ); ?>

		</ol>

		<div class="reply_to_para" id="reply_to_para-">
		<p><a class="reply_to_para" href="<?php the_permalink() ?>?replytopara#respond" onclick="return addComment.moveFormToPara( '', '', '1' )"><?php _e( 'Leave a comment on the whole page', 'commentpress-core' ); ?></a></p>
		</div>

	</div><!-- /paragraph_wrapper -->



<?php else : // This is displayed if there are no comments so far. ?>



	<?php if ( 'open' == $post->comment_status ) : ?>

		<!-- comments are open, but there are no comments. -->
		<h3 class="nocomments"><span><?php _e( 'No comments on the whole page', 'commentpress-core' ); ?></span></h3>

		<div class="paragraph_wrapper">

			<div class="reply_to_para" id="reply_to_para-">
			<p><a class="reply_to_para" href="<?php the_permalink() ?>?replytopara#respond" onclick="return addComment.moveFormToPara( '', '', '1' )"><?php _e( 'Leave a comment on the whole page', 'commentpress-core' ); ?></a></p>
			</div>

		</div><!-- /paragraph_wrapper -->

	<?php else : // Comments are closed. ?>

		<!-- comments are closed. -->
		<h3 class="nocomments comments-closed"><span><?php _e( 'Comments are closed.', 'commentpress-core' ); ?></span></h3>

	<?php endif; ?>



<?php endif; ?>



</div><!-- /comments_container -->



</div><!-- /sidebar_contents_wrapper -->



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
