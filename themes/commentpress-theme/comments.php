<?php
/**
 * Comments Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// First, test for password protection.
if ( post_password_required() ) :

	?>
	<div class="sidebar_contents_wrapper">
		<div class="comments_container">
			<h3 class="nocomments"><span><?php esc_html_e( 'Enter the password to view comments', 'commentpress-core' ); ?></span></h3>
		</div><!-- /comments_container -->
	</div><!-- /sidebar_contents_wrapper -->
	<?php

	return;

endif;

// Declare access to globals.
global $post;

// Get core plugin reference.
$core = commentpress_core();

// If we have the plugin enabled.
if ( ! empty( $core ) ) {

	// Are we asking for Comments-in-Page?
	if ( $core->pages_legacy->is_special_page() ) {

		// Include 'Comments-in-Page' template.

		/**
		 * Try to locate template using WordPress method.
		 *
		 * @since 3.4
		 *
		 * @param str The existing path returned by WordPress.
		 * @return str The modified path.
		 */
		$cp_comments_in_page = apply_filters( 'cp_template_comments_in_page', locate_template( 'assets/templates/comments_in_page.php' ) );

		// Load it if we find it.
		if ( '' !== $cp_comments_in_page ) {
			load_template( $cp_comments_in_page );
		}

		// --<
		return;

	} else {

		// Include Comments split by Paragraph template.

		/**
		 * Try to locate template using WordPress method.
		 *
		 * @since 3.4
		 *
		 * @param str The existing path returned by WordPress.
		 * @return str The modified path.
		 */
		$cp_comments_by_para = apply_filters( 'cp_template_comments_by_para', locate_template( 'assets/templates/comments_by_para.php' ) );

		// Load it if we find it.
		if ( '' !== $cp_comments_by_para ) {
			load_template( $cp_comments_by_para );
		}

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

			<h3 id="para-heading-"><span class="heading-padder">
				<?php

				// TODO: Needs sprintf() here.
				comments_number(
					'<span>0</span> comments',
					'<span>1</span> comment',
					'<span>%</span> comments'
				);

				?>
				<?php esc_html_e( 'on the whole page', 'commentpress-core' ); ?>
			</span></h3>

			<div class="paragraph_wrapper">

				<ol class="commentlist">
					<?php

					$args = [
						'type'       => 'comment',
						'reply_text' => __( 'Reply to this comment', 'commentpress-core' ),
						'callback'   => 'commentpress_comments',
					];

					wp_list_comments( $args );

					?>
				</ol>

				<div class="reply_to_para" id="reply_to_para-">
					<p><a class="reply_to_para" href="<?php the_permalink(); ?>?replytopara#respond" onclick="return addComment.moveFormToPara( '', '', '1' )"><?php esc_html_e( 'Leave a comment on the whole page', 'commentpress-core' ); ?></a></p>
				</div>

			</div><!-- /paragraph_wrapper -->

		<?php else : /* This is displayed if there are no Comments so far. */ ?>

			<?php if ( 'open' === $post->comment_status ) : ?>

				<!-- Comments are open, but there are no Comments. -->
				<h3 class="nocomments"><span><?php esc_html_e( 'No comments on the whole page', 'commentpress-core' ); ?></span></h3>

				<div class="paragraph_wrapper">

					<div class="reply_to_para" id="reply_to_para-">
						<p><a class="reply_to_para" href="<?php the_permalink(); ?>?replytopara#respond" onclick="return addComment.moveFormToPara( '', '', '1' )"><?php esc_html_e( 'Leave a comment on the whole page', 'commentpress-core' ); ?></a></p>
					</div>

				</div><!-- /paragraph_wrapper -->

			<?php else : ?>

				<!-- comments are closed. -->
				<h3 class="nocomments comments-closed"><span><?php esc_html_e( 'Comments are closed.', 'commentpress-core' ); ?></span></h3>

			<?php endif; ?>

		<?php endif; ?>

	</div><!-- /comments_container -->
</div><!-- /sidebar_contents_wrapper -->

<?php

/**
 * Try to locate template using WordPress method.
 *
 * @since 3.4
 *
 * @param str The existing path returned by WordPress.
 * @return str The modified path.
 */
$cp_comment_form = apply_filters( 'cp_template_comment_form', locate_template( 'assets/templates/comment_form.php' ) );

// Load it if we find it.
if ( ! empty( $cp_comment_form ) ) {
	load_template( $cp_comment_form );
}
