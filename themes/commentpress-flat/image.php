<?php
/**
 * Image Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

?>
<!-- image.php -->
<div id="wrapper">
	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">

			<div id="content" class="clearfix">

				<?php if ( have_posts() ) : ?>

					<?php while ( have_posts() ) : ?>

						<?php the_post(); ?>

						<div id="post-<?php the_ID(); ?>" class="post image_attachment">

							<h2 class="post_title"><a href="<?php echo get_permalink( $post->post_parent ); ?>" rev="attachment" title="<?php esc_attr_e( 'Back to gallery', 'commentpress-core' ); ?>"><?php echo get_the_title( $post->post_parent ); ?></a> <span><a href="<?php the_permalink(); ?>" class="attachment_permalink" title="<?php esc_attr_e( 'Permalink for this image', 'commentpress-core' ); ?>"><?php the_title(); ?></a></span></h2>

							<p class="back_button"><a class="button" href="<?php echo get_permalink( $post->post_parent ); ?>" rev="attachment" title="<?php esc_attr_e( 'Back to gallery', 'commentpress-core' ); ?>"><?php esc_html_e( 'Back to gallery', 'commentpress-core' ); ?></a></p>

							<div class="the_image_attachment">

								<p class="image_attachment_wrap"><a class="image_attachment_link" href="<?php echo wp_get_attachment_url( $post->ID ); ?>"><?php echo wp_get_attachment_image( $post->ID, 'large' ); ?></a></p>

								<div class="image_attachment_caption">
									<?php /* Show "caption" if present. */ ?>
									<?php if ( ! empty( $post->post_excerpt ) ) : ?>
										<?php the_excerpt(); ?>
									<?php else : ?>
										<p><?php esc_html_e( 'Untitled', 'commentpress-core' ); ?></p>
									<?php endif; ?>
								</div>

							</div>

							<?php the_content( '<p>' . __( 'Read the rest of the text &raquo;', 'commentpress-core' ) . '</p>' ); ?>

							<p class="postmetadata" style="clear: left;">
								<?php

								echo sprintf(
									/* translators: 1: The post date, 2: The post time, 3: The list of categories. */
									__( 'This image was posted on %1$s at %2$s and is filed under %3$s.', 'commentpress-core' ),
									esc_html( get_the_date( get_option( 'date_format' ) ) ),
									get_the_time(),
									get_the_category_list( ', ' )
								);

								?>

								<?php the_taxonomies(); ?>

								<?php

								// Define RSS text.
								$rss_text = __( 'RSS 2.0', 'commentpress-core' );

								// Construct RSS link.
								$rss_link = '<a href="' . esc_url( get_post_comments_feed_link() ) . '">' . $rss_text . '</a>';

								echo sprintf(
									/* translators: %s: The RSS feed link. */
									__( 'You can follow any comments on this image through the %s feed.', 'commentpress-core' ),
									$rss_link
								);

								if ( ( 'open' == $post->comment_status ) && ( 'open' == $post->ping_status ) ) {

									// Both Comments and pings are open.

									// Define trackback text.
									$trackback_text = __( 'trackback', 'commentpress-core' );

									// Construct RSS link.
									$trackback_link = '<a href="' . esc_url( get_trackback_url() ) . '"rel="trackback">' . $trackback_text . '</a>';

									// Write out.
									echo sprintf(
										/* translators: %s: The trackback link. */
										__( 'You are welcome to leave a comment, or %s from your own site.', 'commentpress-core' ),
										$trackback_link
									);

									// Add trailing space.
									echo ' ';

								} elseif ( ! ( 'open' == $post->comment_status ) && ( 'open' == $post->ping_status ) ) {

									// Only pings are open.

									// Define trackback text.
									$trackback_text = __( 'trackback', 'commentpress-core' );

									// Construct RSS link.
									$trackback_link = '<a href="' . esc_url( get_trackback_url() ) . '"rel="trackback">' . $trackback_text . '</a>';

									// Write out.
									echo sprintf(
										/* translators: %s: The trackback link. */
										__( 'Comments are currently closed, but you can %s from your own site.', 'commentpress-core' ),
										$trackback_link
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

							<ul class="image_link">
								<li class="alignright">
									<h4><?php esc_html_e( 'Next Image &raquo;', 'commentpress-core' ); ?></h4>
									<?php next_image_link(); ?>
								</li>
								<li class="alignleft">
									<h4><?php esc_html_e( '&laquo; Previous Image', 'commentpress-core' ); ?></h4>
									<?php previous_image_link(); ?>
								</li>
							</ul>

						</div><!-- /post -->

					<?php endwhile; ?>

				<?php else : ?>

					<div class="post">

						<h2><?php esc_html_e( 'Not Found', 'commentpress-core' ); ?></h2>

						<p><?php esc_html_e( 'Sorry, no attachments matched your criteria.', 'commentpress-core' ); ?></p>

					</div><!-- /post -->

				<?php endif; ?>

			</div><!-- /content -->

		</div><!-- /page_wrapper -->
	</div><!-- /main_wrapper -->
</div><!-- /wrapper -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
