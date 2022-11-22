<?php
/**
 * Archive Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

?>
<!-- archive.php -->
<div id="wrapper">
	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">

			<?php

			/**
			 * Try to locate template using WordPress method but allow overrides.
			 *
			 * @since 3.4
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

						<?php /* If this is a category archive. */ if ( is_category() ) : ?>
							<h3 class="post_title"><?php echo sprintf( __( 'Archive for the &#8216;%s&#8217; Category', 'commentpress-core' ), single_cat_title( '', false ) ); ?></h3>
						<?php /* If this is a tag archive. */ elseif ( is_tag() ) : ?>
							<h3 class="post_title"><?php esc_html_e( 'Posts Tagged', 'commentpress-core' ); ?> &#8216;<?php single_tag_title(); ?>&#8217;</h3>
						<?php /* If this is a daily archive. */ elseif ( is_day() ) : ?>
						<h3 class="post_title"><?php esc_html_e( 'Archive for', 'commentpress-core' ); ?> <?php the_time( __( 'F jS, Y', 'commentpress-core' ) ); ?></h3>
						<?php /* If this is a monthly archive. */ elseif ( is_month() ) : ?>
							<h3 class="post_title"><?php esc_html_e( 'Archive for', 'commentpress-core' ); ?> <?php the_time( __( 'F, Y', 'commentpress-core' ) ); ?></h3>
						<?php /* If this is a yearly archive. */ elseif ( is_year() ) : ?>
						<h3 class="post_title"><?php esc_html_e( 'Archive for', 'commentpress-core' ); ?> <?php the_time( __( 'Y', 'commentpress-core' ) ); ?></h3>
						<?php /* If this is an author archive. */ elseif ( is_author() ) : ?>
							<h3 class="post_title"><?php esc_html_e( 'Author Archive', 'commentpress-core' ); ?></h3>
						<?php /* If this is a paged archive. */ elseif ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) : ?>
							<h3 class="post_title"><?php esc_html_e( 'Archives', 'commentpress-core' ); ?></h3>
						<?php endif; ?>

						<?php while ( have_posts() ) : ?>

							<?php the_post(); ?>

							<div class="search_result">

								<h3 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php esc_attr_e( 'Permanent Link to', 'commentpress-core' ); ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>

								<?php

								// Override if we've elected to show the meta.
								$cp_meta_visibility = ' style="display: none;"';
								if ( commentpress_get_post_meta_visibility( get_the_ID() ) ) {
									$cp_meta_visibility = '';
								}

								?>
								<div class="search_meta"<?php echo $cp_meta_visibility; ?>>
									<?php commentpress_echo_post_meta(); ?>
								</div>

								<?php the_excerpt(); ?>

								<p class="search_meta">
									<?php the_tags( __( 'Tags: ', 'commentpress-core' ), ', ', '<br />' ); ?> <?php esc_html_e( 'Posted in', 'commentpress-core' ); ?> <?php the_category( ', ' ); ?> | <?php edit_post_link( __( 'Edit', 'commentpress-core' ), '', ' | ' ); ?>  <?php comments_popup_link( __( 'No Comments &#187;', 'commentpress-core' ), __( '1 Comment &#187;', 'commentpress-core' ), __( '% Comments &#187;', 'commentpress-core' ) ); ?>
								</p>

							</div><!-- /search_result -->

						<?php endwhile; ?>

					<?php else : ?>

						<h2 class="post_title"><?php esc_html_e( 'Not Found', 'commentpress-core' ); ?></h2>

						<p><?php esc_html_e( 'Sorry, but you are looking for something that isn’t here.', 'commentpress-core' ); ?></p>

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
