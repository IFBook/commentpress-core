<?php
/**
 * Template Name: Directory
 *
 * Provides support for The "Members List" plugin.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

?>
<!-- directory.php -->
<div id="wrapper">
	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">

			<div id="content">

				<?php if ( have_posts() ) : ?>

					<?php while ( have_posts() ) : ?>

						<?php the_post(); ?>

						<div class="post clearfix<?php echo commentpress_get_post_css_override( get_the_ID() ); ?>" id="post-<?php the_ID(); ?>">

							<h2 class="post_title"<?php commentpress_post_title_visibility( get_the_ID() ); ?>><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

							<?php

							// If we have the Members List plugin.
							if ( class_exists( 'tern_members' ) ) {

								// Init Members List plugin.
								$members = new tern_members();

								// Show Membership.
								$members->members( [
									'search' => true,
									'alpha' => true,
									'pagination' => true,
									'pagination2' => true,
									'radius' => false,
									'sort' => false,
								] );

							}

							?>

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
