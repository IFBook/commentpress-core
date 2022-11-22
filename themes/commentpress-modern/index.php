<?php
/**
 * Default Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

?>
<!-- index.php -->
<div id="wrapper">
	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">

			<?php

			/**
			 * Try to locate template using WordPress method.
			 *
			 * @since 3.4
			 *
			 * @param str The existing path returned by WordPress.
			 * @return str The modified path.
			 */
			$cp_page_navigation = apply_filters( 'cp_template_page_navigation', locate_template( 'assets/templates/page_navigation.php' ) );

			// Load it if we find it.
			if ( $cp_page_navigation != '' ) {
				load_template( $cp_page_navigation, false );
			}

			?>

			<div id="content" class="clearfix">
				<div class="post">

					<?php if ( have_posts() ) : ?>

						<?php while ( have_posts() ) : ?>

							<?php the_post(); ?>

							<div class="search_result">

								<h3 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php esc_attr_e( 'Permanent Link to', 'commentpress-core' ); ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>

								<div class="search_meta">
									<?php commentpress_echo_post_meta(); ?>
								</div>

								<?php the_excerpt(); ?>

								<p class="search_meta"><?php the_tags( __( 'Tags: ', 'commentpress-core' ), ', ', '<br />' ); ?> <?php esc_html_e( 'Posted in', 'commentpress-core' ); ?> <?php the_category( ', ' ); ?> | <?php edit_post_link( __( 'Edit', 'commentpress-core' ), '', ' | ' ); ?>  <?php comments_popup_link( __( 'No Comments &#187;', 'commentpress-core' ), __( '1 Comment &#187;', 'commentpress-core' ), __( '% Comments &#187;', 'commentpress-core' ) ); ?></p>

							</div><!-- /search_result -->

						<?php endwhile; ?>

					<?php else : ?>

						<h2 class="post_title"><?php esc_html_e( 'No blog posts found', 'commentpress-core' ); ?></h2>

						<p>
							<?php esc_html_e( 'There are no blog posts yet.', 'commentpress-core' ); ?>
							<?php if ( is_user_logged_in() ) : ?>
								<a href="<?php admin_url(); ?>"><?php esc_html_e( 'Go to your dashboard to add one.', 'commentpress-core' ); ?></a>
							<?php endif; ?>
						</p>

						<p><?php esc_html_e( 'If you were looking for something that hasn’t been found, try using the search form below.', 'commentpress-core' ); ?></p>

						<?php get_search_form(); ?>

					<?php endif; ?>

				</div><!-- /post -->
			</div><!-- /content -->

			<div class="page_nav_lower">
				<?php

				// Include Page Navigation again.
				if ( $cp_page_navigation != '' ) {
					load_template( $cp_page_navigation, false );
				}

				?>
			</div><!-- /page_nav_lower -->

		</div><!-- /page_wrapper -->
	</div><!-- /main_wrapper -->
</div><!-- /wrapper -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
