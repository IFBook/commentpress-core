<?php
/**
 * Template Name: Welcome
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

?>
<!-- welcome.php -->
<div id="wrapper">
	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">
			<div id="content">
				<?php if ( have_posts() ) : ?>

					<?php while ( have_posts() ) : ?>

						<?php the_post(); ?>

						<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>">

							<h2 class="post_title"<?php commentpress_post_title_visibility( get_the_ID() ); ?>><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

							<div class="search_meta"<?php commentpress_post_meta_visibility( get_the_ID() ); ?>>
								<?php commentpress_echo_post_meta(); ?>
							</div>

							<?php the_content(); ?>

							<?php echo commentpress_multipager(); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>

						</div><!-- /post -->

					<?php endwhile; ?>

				<?php else : ?>

					<div class="post">
						<h2 class="post_title"><?php esc_html_e( 'Page Not Found', 'commentpress-core' ); ?></h2>
						<p><?php esc_html_e( 'Sorry, but you are looking for something that isn’t here.', 'commentpress-core' ); ?></p>
						<?php get_search_form(); ?>
					</div><!-- /post -->

				<?php endif; ?>

			</div><!-- /content -->
		</div><!-- /page_wrapper -->
	</div><!-- /main_wrapper -->
</div><!-- /wrapper -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
