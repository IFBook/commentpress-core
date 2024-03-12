<?php
/**
 * Default Single Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Get core plugin reference.
$core = commentpress_core();

get_header();

?>
<!-- single.php -->
<div id="wrapper">

	<?php if ( have_posts() ) : ?>

		<?php while ( have_posts() ) : ?>

			<?php the_post(); ?>

			<div id="main_wrapper" class="clearfix">
				<div id="page_wrapper">

					<?php commentpress_get_feature_image(); ?>

					<?php if ( ! commentpress_has_feature_image() ) : ?>
						<?php commentpress_page_navigation_template(); ?>
					<?php endif; ?>

					<div id="content" class="content-wrapper">
						<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>">

							<?php if ( ! commentpress_has_feature_image() ) : ?>
								<h2 class="post_title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

								<div class="search_meta">
									<?php commentpress_echo_post_meta(); ?>
								</div>
							<?php endif; ?>

							<?php commentpress_get_post_version_info( $post ); ?>

							<?php the_content(); ?>

							<?php echo commentpress_multipager(); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>

							<?php the_tags( '<div class="entry-meta"><p class="postmetadata">' . __( 'Tags: ', 'commentpress-core' ), '<span class="tag-divider">,</span> ', '</p></div>' ); ?>

							<div class="entry-category-meta clearfix">
								<p class="category-meta"><?php esc_html_e( 'Categories:', 'commentpress-core' ); ?></p>
								<?php echo get_the_category_list( ', ' ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
							</div>

							<?php commentpress_geomashup_map_get(); ?>

							<p class="postmetadata">
								<?php

								// Define RSS text.
								$rss_text = __( 'RSS 2.0', 'commentpress-core' );

								// Construct RSS link.
								$rss_link = '<a href="' . esc_url( get_post_comments_feed_link() ) . '">' . esc_html( $rss_text ) . '</a>';

								// Show text.
								echo sprintf(
									/* translators: %s: The RSS feed link. */
									esc_html__( 'You can follow any comments on this entry through the %s feed.', 'commentpress-core' ),
									$rss_link // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								);

								// Add trailing space.
								echo ' ';

								if ( ( 'open' == $post->comment_status ) && ( 'open' == $post->ping_status ) ) {

									// Both Comments and pings are open.

									// Define trackback text.
									$trackback_text = __( 'trackback', 'commentpress-core' );

									// Construct RSS link.
									$trackback_link = '<a href="' . esc_url( get_trackback_url() ) . '"rel="trackback">' . esc_html( $trackback_text ) . '</a>';

									// Write out.
									echo sprintf(
										/* translators: %s: The trackback link. */
										esc_html__( 'You can leave a comment, or %s from your own site.', 'commentpress-core' ),
										$trackback_link // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									);

									// Add trailing space.
									echo ' ';

								} elseif ( ! ( 'open' == $post->comment_status ) && ( 'open' == $post->ping_status ) ) {

									// Only pings are open.

									// Define trackback text.
									$trackback_text = __( 'trackback', 'commentpress-core' );

									// Construct RSS link.
									$trackback_link = '<a href="' . esc_url( get_trackback_url() ) . '"rel="trackback">' . esc_html( $trackback_text ) . '</a>';

									// Write out.
									echo sprintf(
										/* translators: %s: The trackback link. */
										esc_html__( 'Comments are currently closed, but you can %s from your own site.', 'commentpress-core' ),
										$trackback_link // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									);

									// Add trailing space.
									echo ' ';

								} elseif ( ( 'open' == $post->comment_status ) && ! ( 'open' == $post->ping_status ) ) {

									// Comments are open, pings are not.
									esc_html_e( 'You can leave a comment. Pinging is currently not allowed.', 'commentpress-core' );

									// Add trailing space.
									echo ' ';

								} elseif ( ! ( 'open' == $post->comment_status ) && ! ( 'open' == $post->ping_status ) ) {

									// Neither Comments nor pings are open.
									esc_html_e( 'Both comments and pings are currently closed.', 'commentpress-core' );

									// Add trailing space.
									echo ' ';

								}

								// Show edit link.
								edit_post_link( __( 'Edit this entry', 'commentpress-core' ), '', '.' );

								?>
							</p>

						</div><!-- /post -->
					</div><!-- /content -->

					<div class="page_nav_lower">
						<?php commentpress_page_navigation_template(); ?>
					</div><!-- /page_nav_lower -->

				</div><!-- /page_wrapper -->
			</div><!-- /main_wrapper -->

		<?php endwhile; ?>

	<?php else : ?>

		<div id="main_wrapper" class="clearfix">
			<div id="page_wrapper">
				<div id="content">
					<div class="post">

						<h2 class="post_title"><?php esc_html_e( 'Post Not Found', 'commentpress-core' ); ?></h2>
						<p><?php esc_html_e( 'Sorry, no posts matched your criteria.', 'commentpress-core' ); ?></p>
						<?php get_search_form(); ?>

					</div><!-- /post -->
				</div><!-- /content -->
			</div><!-- /page_wrapper -->
		</div><!-- /main_wrapper -->

	<?php endif; ?>

</div><!-- /wrapper -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
