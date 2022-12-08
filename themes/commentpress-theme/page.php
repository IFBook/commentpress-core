<?php
/**
 * Default Page Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

?>
<!-- page.php -->
<div id="wrapper">

	<?php if ( have_posts() ) : ?>

		<?php while ( have_posts() ) : ?>

			<?php the_post(); ?>

			<div id="main_wrapper" class="clearfix">
				<div id="page_wrapper">

					<div id="content" class="content-wrapper">
						<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>">

							<h2 class="post_title"<?php commentpress_post_title_visibility( get_the_ID() ); ?>><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

							<div class="search_meta"<?php commentpress_post_meta_visibility( get_the_ID() ); ?>>
								<?php commentpress_echo_post_meta(); ?>
							</div>

							<?php the_content(); ?>

							<?php /* Test for "Post Tags and Categories for Pages" plugin. */ ?>
							<?php if ( class_exists( 'PTCFP' ) ) : ?>
								<p class="search_meta">
									<?php the_tags( __( 'Tags: ', 'commentpress-core' ), ', ', '<br />' ); ?> <?php esc_html_e( 'Posted in', 'commentpress-core' ); ?> <?php the_category( ', ' ); ?>
								</p>
							<?php endif; ?>

							<?php echo commentpress_multipager(); ?>

							<?php if ( ! empty( commentpress_get_page_number( get_the_ID() ) ) ) : ?>
								<div class="running_header_bottom">
									<?php commentpress_page_number( get_the_ID() ); ?>
								</div>
							<?php endif; ?>

						</div><!-- /post -->
					</div><!-- /content -->

				</div><!-- /page_wrapper -->
			</div><!-- /main_wrapper -->

		<?php endwhile; ?>

	<?php else : ?>

		<div id="main_wrapper" class="clearfix">
			<div id="page_wrapper">
				<div id="content">
					<div class="post">

						<h2 class="post_title"><?php esc_html_e( 'Page Not Found', 'commentpress-core' ); ?></h2>
						<p><?php esc_html_e( 'Sorry, but you are looking for something that isn’t here.', 'commentpress-core' ); ?></p>
						<?php get_search_form(); ?>

					</div><!-- /post -->
				</div><!-- /content -->
			</div><!-- /page_wrapper -->
		</div><!-- /main_wrapper -->

	<?php endif; ?>

</div><!-- /wrapper -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
