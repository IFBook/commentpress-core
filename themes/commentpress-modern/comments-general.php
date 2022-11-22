<?php
/**
 * Template Name: General Comments
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

?>
<!-- all-comments.php -->
<div id="wrapper">
	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">

			<div id="content">

				<?php if ( have_posts() ) : ?>

					<?php while ( have_posts() ) : ?>

						<?php the_post(); ?>

							<div class="post general_comments">

								<h2 class="post_title"><?php esc_html_e( 'General Comments', 'commentpress-core' ); ?></h2>

								<?php comments_template(); ?>

							</div><!-- /post -->

					<?php endwhile; ?>

				<?php endif; ?>

			</div><!-- /content -->

		</div><!-- /page_wrapper -->
	</div><!-- /main_wrapper -->
</div><!-- /wrapper -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
