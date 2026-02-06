<?php
/**
 * Comments in Page Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- comments_in_page.php -->
<div id="comments_in_page_wrapper">
	<div class="comments_container">

		<?php if ( 'open' !== $post->comment_status ) : ?>
			<h3 class="nocomments comments-closed"><span><?php esc_html_e( 'Comments are closed', 'commentpress-core' ); ?></span></h3>
		<?php endif; ?>

		<?php if ( have_comments() ) : ?>

			<h3 class="general_comments_header">
				<?php

				comments_number(
					'<span>0</span> general comments',
					'<span>1</span> general comment',
					'<span>%</span> general comments'
				);

				?>
			</h3>

			<?php

			/**
			 * Fires before the Comments are rendered.
			 *
			 * @since 3.4
			 */
			do_action( 'commentpress_before_scrollable_comments' );

			?>

			<div class="paragraph_wrapper">

				<ol class="commentlist">
					<?php

					// Define our formatting options.
					$comments_args = [
						'type'       => 'comment',
						'reply_text' => __( 'Reply to this comment', 'commentpress-core' ),
						'callback'   => 'commentpress_comments',
						'style'      => 'ol',
					];

					// Get Comments for this Post in ascending order.
					$comment_query = [
						'post_id' => $post->ID,
						'order'   => 'ASC',
					];

					$comments_in_page = get_comments( $comment_query );

					// List Comments.
					wp_list_comments( $comments_args, $comments_in_page );

					?>
				</ol>

			</div><!-- /paragraph_wrapper -->

		<?php else : /* This is displayed if there are no Comments so far. */ ?>

			<?php if ( 'open' === $post->comment_status ) : ?>
				<h3 class="nocomments"><?php esc_html_e( 'No general comments yet.', 'commentpress-core' ); ?></h3>
			<?php endif; ?>

		<?php endif; ?>

	</div><!-- /comments_container -->
</div><!-- /comments_in_page_wrapper -->

<?php

/**
 * Locates the Comment form template.
 *
 * @since 3.7
 *
 * @param string The path to the Comment form template.
 */
$cp_comment_form = apply_filters( 'cp_template_comment_form', locate_template( 'assets/templates/comment_form.php' ) );

// Load it if we find it.
if ( ! empty( $cp_comment_form ) ) {
	load_template( $cp_comment_form );
}

?>
