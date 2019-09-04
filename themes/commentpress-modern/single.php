<?php get_header(); ?>



<!-- single.php -->

<div id="wrapper">



<?php if (have_posts()) : while (have_posts()) : the_post(); ?>



	<div id="main_wrapper" class="clearfix<?php echo commentpress_theme_tabs_class_get(); ?>">

		<?php commentpress_theme_tabs_render(); ?>

		<div id="page_wrapper"<?php echo commentpress_theme_tabs_classes_get(); ?>>

			<?php commentpress_get_feature_image(); ?>

			<?php

			/**
			 * Try to locate template using WP method.
			 *
			 * @since 3.4
			 *
			 * @param str The existing path returned by WordPress.
			 * @return str The modified path.
			 */
			$cp_page_navigation = apply_filters(
				'cp_template_page_navigation',
				locate_template( 'assets/templates/page_navigation.php' )
			);

			// Do we have a featured image?
			if ( ! commentpress_has_feature_image() ) {

				// Load it if we find it.
				if ( $cp_page_navigation != '' ) load_template( $cp_page_navigation, false );

			}

			?>

			<div id="content" class="workflow-wrapper">

				<div class="post<?php echo commentpress_get_post_css_override( get_the_ID() ); ?>" id="post-<?php the_ID(); ?>">

					<?php

					// Do we have a featured image?
					if ( ! commentpress_has_feature_image() ) {

						?><h2 class="post_title"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>

						<div class="search_meta">
							<?php commentpress_echo_post_meta(); ?>
						</div>
						<?php

					}

					?>

					<?php commentpress_get_post_version_info( $post ); ?>

					<?php global $more; $more = true; the_content(''); ?>

					<?php

					// NOTE: Comment permalinks are filtered if the comment is not on the first page
					// in a multipage post... see: commentpress_multipage_comment_link in functions.php
					echo commentpress_multipager();

					?>

					<?php the_tags( '<div class="entry-meta"><p class="postmetadata">' . __( 'Tags: ', 'commentpress-core' ), '<span class="tag-divider">,</span> ', '</p></div>'); ?>

					<div class="entry-category-meta clearfix">

						<p class="category-meta"><?php _e( 'Categories:', 'commentpress-core' ); ?></p>

						<?php echo get_the_category_list( ', ' ); ?>

					</div>

					<?php commentpress_geomashup_map_get(); ?>

					<p class="postmetadata"><?php

						// Define RSS text.
						$rss_text = __( 'RSS 2.0', 'commentpress-core' );

						// Construct RSS link.
						$rss_link = '<a href="' . esc_url( get_post_comments_feed_link() ) . '">' . $rss_text . '</a>';

						// Show text.
						echo sprintf(
							__( 'You can follow any comments on this entry through the %s feed.', 'commentpress-core' ),
							$rss_link
						);

						// Add trailing space.
						echo ' ';

						if (('open' == $post->comment_status) AND ('open' == $post->ping_status)) {

							// Both comments and pings are open.

							// Define trackback text.
							$trackback_text = __( 'trackback', 'commentpress-core' );

							// Construct RSS link.
							$trackback_link = '<a href="' . esc_url( get_trackback_url() ) . '"rel="trackback">' . $trackback_text . '</a>';

							// Write out.
							echo sprintf(
								__( 'You can leave a comment, or %s from your own site.', 'commentpress-core' ),
								$trackback_link
							);

							// Add trailing space.
							echo ' ';

						} elseif (!('open' == $post->comment_status) AND ('open' == $post->ping_status)) {

							// Only pings are open.

							// Define trackback text.
							$trackback_text = __( 'trackback', 'commentpress-core' );

							// Construct RSS link.
							$trackback_link = '<a href="' . esc_url( get_trackback_url() ) . '"rel="trackback">' . $trackback_text . '</a>';

							// Write out.
							echo sprintf(
								__( 'Comments are currently closed, but you can %s from your own site.', 'commentpress-core' ),
								$trackback_link
							);

							// Add trailing space.
							echo ' ';

						} elseif (('open' == $post->comment_status) AND !('open' == $post->ping_status)) {

							// Comments are open, pings are not.
							_e( 'You can leave a comment. Pinging is currently not allowed.', 'commentpress-core' );

							// Add trailing space.
							echo ' ';

						} elseif (!('open' == $post->comment_status) AND !('open' == $post->ping_status)) {

							// Neither comments nor pings are open.
							_e( 'Both comments and pings are currently closed.', 'commentpress-core' );

							// Add trailing space.
							echo ' ';

						}

						// Show edit link.
						edit_post_link( __( 'Edit this entry', 'commentpress-core' ), '', '.' );

					?></p>

				</div><!-- /post -->

			</div><!-- /content -->

			<?php commentpress_theme_tabs_content_render(); ?>

			<div class="page_nav_lower">
			<?php

			// Include page_navigation again.
			if ( $cp_page_navigation != '' ) load_template( $cp_page_navigation, false );

			?>
			</div><!-- /page_nav_lower -->

		</div><!-- /page_wrapper -->

	</div><!-- /main_wrapper -->



<?php endwhile; else: ?>



	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">
			<div id="content">
				<div class="post">

					<h2 class="post_title"><?php _e( 'Post Not Found', 'commentpress-core' ); ?></h2>
					<p><?php _e( 'Sorry, no posts matched your criteria.', 'commentpress-core' ); ?></p>
					<?php get_search_form(); ?>

				</div><!-- /post -->
			</div><!-- /content -->
		</div><!-- /page_wrapper -->
	</div><!-- /main_wrapper -->



<?php endif; ?>



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>
