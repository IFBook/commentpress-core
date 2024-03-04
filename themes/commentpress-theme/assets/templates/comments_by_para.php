<?php
/**
 * Comments by Paragraph Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Add identifier ID.
if ( isset( $post->ID ) ) {
	$comments_post_identifier = ' id="comments_post_identifier-' . $post->ID . '"';
}

?>
<!-- comments_by_para.php -->
<div class="sidebar_contents_wrapper">

	<?php

	/**
	 * Fires before the Comments container is rendered.
	 *
	 * @since 3.9
	 */
	do_action( 'commentpress_before_comments_container' );

	?>

	<div class="comments_container"<?php echo $comments_post_identifier; ?>>

		<?php if ( 'closed' == $post->comment_status ) : ?>
			<h3 class="nocomments comments-closed"><span><?php esc_html_e( 'Comments are closed', 'commentpress-core' ); ?></span></h3>
		<?php endif; ?>

		<?php commentpress_get_comments_by_para(); ?>

		<?php

		/**
		 * Fires before the Comments form is rendered.
		 *
		 * @since 3.4
		 */
		do_action( 'commentpress_before_comment_form' );

		// Because AJAX may be routed via admin or front end.
		// TODO: Need to understand this.
		if ( ! wp_doing_ajax() ) {

			/**
			 * Locates the Comment form template.
			 *
			 * @since 3.7
			 *
			 * @param str The path to the Comment form template.
			 */
			$cp_comment_form = apply_filters( 'cp_template_comment_form', locate_template( 'assets/templates/comment_form.php' ) );

			// Load it if we find it.
			if ( '' != $cp_comment_form ) {
				load_template( $cp_comment_form );
			}

		}

		/**
		 * Fires after the Comments form is rendered.
		 *
		 * @since 3.4
		 */
		do_action( 'commentpress_after_comment_form' );

		?>

	</div><!-- /comments_container -->

	<?php

	/**
	 * Fires after the Comments container is rendered.
	 *
	 * @since 3.9
	 */
	do_action( 'commentpress_after_comments_container' );

	?>

</div><!-- /sidebar_contents_wrapper -->
