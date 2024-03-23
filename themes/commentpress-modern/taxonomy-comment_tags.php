<?php
/**
 * "Comment Tags" Taxonomy Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Get HTML for this template.
$html = '';
$core = commentpress_core();
if ( ! empty( $core ) ) {
	$html = $core->comments->tagging->archive_content_get();
}

get_header();

?>
<!-- taxonomy-comment_tags.php -->
<div id="wrapper">
	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">

			<div id="content" class="clearfix">
				<div class="post">

					<h3 class="post_title">
						<?php

						echo sprintf(
							/* translators: %s: The name of the tag. */
							esc_html__( 'Comments Tagged &#8216;%s&#8217;', 'commentpress-core' ),
							single_cat_title( '', false )
						);

						?>
					</h3>

					<div id="comments_in_page_wrapper">

						<?php if ( ! empty( $html ) ) : ?>

							<?php echo $html; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>

						<?php else : ?>

							<h2 class="post_title"><?php esc_html_e( 'No Comments Found', 'commentpress-core' ); ?></h2>
							<p><?php esc_html_e( 'Sorry, but there are no comments for this tag.', 'commentpress-core' ); ?></p>
							<?php get_search_form(); ?>

						<?php endif; ?>

					</div>

				</div><!-- /post -->
			</div><!-- /content -->

		</div><!-- /page_wrapper -->
	</div><!-- /main_wrapper -->
</div><!-- /wrapper -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
