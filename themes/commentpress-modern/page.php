<?php
/**
 * Default Page Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Get core plugin reference.
$core = commentpress_core();

get_header();

?>
<!-- page.php -->

<div id="wrapper">

	<?php if ( have_posts() ) : ?>

		<?php while ( have_posts() ) : ?>

			<?php the_post(); ?>

			<div id="main_wrapper" class="clearfix">
				<div id="page_wrapper">

					<?php commentpress_get_feature_image(); ?>

					<?php

					/**
					 * Try to locate template using WordPress method.
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

					?>

					<?php if ( ! commentpress_has_feature_image() ) : ?>
						<?php

						// Load it if we find it.
						if ( $cp_page_navigation != '' ) {
							load_template( $cp_page_navigation, false );
						}

						?>
					<?php endif; ?>

					<div id="content" class="content">
						<div class="post clearfix<?php echo commentpress_get_post_css_override( get_the_ID() ); ?>" id="post-<?php the_ID(); ?>">

							<?php if ( ! commentpress_has_feature_image() ) : ?>
								<?php

								// Override if we've elected to show the title.
								$cp_title_visibility = ' style="display: none;"';
								if ( commentpress_get_post_title_visibility( get_the_ID() ) ) {
									$cp_title_visibility = '';
								}

								?>
								<h2 class="post_title"<?php echo $cp_title_visibility; ?>><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

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

							<?php endif; ?>

							<?php the_content(); ?>

							<?php echo commentpress_multipager(); ?>

							<?php /* Test for "Post Tags and Categories for Pages" plugin. */ ?>
							<?php if ( class_exists( 'PTCFP' ) ) : ?>
								<p class="search_meta">
									<?php the_tags( __( 'Tags: ', 'commentpress-core' ), ', ', '<br />' ); ?> <?php esc_html_e( 'Posted in', 'commentpress-core' ); ?> <?php the_category( ', ' ); ?>
								</p>
							<?php endif; ?>

							<?php if ( ! empty( $core ) ) : ?>
								<?php $num = $core->nav->get_page_number( get_the_ID() ); ?>
								<?php if ( $num ) : ?>
									<div class="running_header_bottom">
										<?php

										// Make lowercase if Roman.
										if ( ! is_numeric( $num ) ) {
											$num = strtolower( $num );
										}

										// Wrap number.
										$element = '<span class="page_num_bottom">' . $num . '</span>';

										echo sprintf(
											__( 'Page %s', 'commentpress-core' ),
											$element
										);

										?>
									</div>
								<?php endif; ?>
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

		<?php endwhile; ?>

	<?php else : ?>

		<div id="main_wrapper" class="clearfix">
			<div id="page_wrapper" class="page_wrapper">
				<div id="content" class="content">
					<div class="post clearfix">

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
