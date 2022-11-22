<?php
/**
 * Template Name: Blog
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

?>
<!-- blog.php -->
<div id="wrapper">
	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">

			<div id="content" class="clearfix">
				<div class="post">

					<?php if ( have_posts() ) : ?>

						<?php while ( have_posts() ) : ?>

							<?php the_post(); ?>

							<div class="search_result">
								<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

									<h3><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php esc_attr_e( 'Permanent Link to', 'commentpress-core' ); ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>

									<div class="search_meta">
										<?php commentpress_echo_post_meta(); ?>
									</div>

									<div class="entry">
										<?php the_content( __( 'Read the rest of this entry &raquo;', 'commentpress-core' ) ); ?>
									</div>

									<p class="postmetadata"><?php the_tags( __( 'Tags: ', 'commentpress-core' ), ', ', '<br />' ); ?> <?php esc_html_e( 'Posted in', 'commentpress-core' ); ?> <?php the_category( ', ' ); ?> | <?php edit_post_link( __( 'Edit', 'commentpress-core' ), '', ' | ' ); ?>  <?php comments_popup_link( __( 'No Comments &#187;', 'commentpress-core' ), __( '1 Comment &#187;', 'commentpress-core' ), __( '% Comments &#187;', 'commentpress-core' ) ); ?></p>

								</div><!-- /post -->
							</div><!-- /search_result -->

						<?php endwhile; ?>

					<?php else : ?>

						<h2><?php esc_html_e( 'Not Found', 'commentpress-core' ); ?></h2>
						<p><?php esc_html_e( 'Sorry, but you are looking for something that isn’t here.', 'commentpress-core' ); ?></p>
						<?php get_search_form(); ?>

					<?php endif; ?>

				</div><!-- /post -->
			</div><!-- /content -->

		</div><!-- /page_wrapper -->
	</div><!-- /main_wrapper -->
</div><!-- /wrapper -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
