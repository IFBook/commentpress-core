<?php /*
================================================================================
CommentPress Modern Theme Comments in Page
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

--------------------------------------------------------------------------------
*/

?>



<!-- comments_in_page.php -->

<div id="comments_in_page_wrapper">



<div class="comments_container">



<?php if ('open' != $post->comment_status) : ?>

	<!-- comments are closed. -->
	<h3 class="nocomments comments-closed"><span><?php _e( 'Comments are closed', 'commentpress-core' ); ?></span></h3>

<?php endif; ?>



<?php if ( have_comments() ) : ?>



	<h3 class="general_comments_header"><?php

	comments_number(
		'<span>0</span> general comments',
		'<span>1</span> general comment',
		'<span>%</span> general comments'
	);

	?></h3>



	<?php do_action( 'commentpress_before_scrollable_comments' ); ?>



	<div class="paragraph_wrapper">

		<ol class="commentlist">

		<?php

		// Get Comments for this Post in ascending order.
		$comments = get_comments( [
			'post_id' => $post->ID,
			'order' => 'ASC',
		] );

		// List Comments.
		wp_list_comments(
			[
				'type'=> 'comment',
				'reply_text' => __( 'Reply to this comment', 'commentpress-core' ),
				'callback' => 'commentpress_comments',
				'style'=> 'ol',
			],
			$comments
		); ?>

		</ol>

	</div><!-- /paragraph_wrapper -->



<?php else : // This is displayed if there are no Comments so far. ?>



	<?php if ('open' == $post->comment_status) : ?>

		<!-- Comments are open, but there are no Comments. -->
		<h3 class="nocomments"><?php esc_html_e( 'No general comments yet', 'commentpress-core' ); ?></h3>

	<?php endif; ?>



<?php endif; ?>



</div><!-- /comments_container -->



</div><!-- /comments_in_page_wrapper -->



<?php

/**
 * Try to locate template using WordPress method.
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
