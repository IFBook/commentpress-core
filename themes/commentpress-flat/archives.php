<?php
/**
 * Template Name: Archive
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

?>
<!-- archives.php -->
<div id="wrapper">
	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">

			<div id="content" class="clearfix">
				<div class="post">

				<?php if ( have_posts() ) : ?>

					<?php while ( have_posts() ) : ?>

						<?php the_post(); ?>

						<h2 class="post_title"><?php the_title(); ?></h2>

						<div class="archives_search_form">
							<?php get_search_form(); ?>
						</div>

						<div class="archives_by_month">

							<h3><?php esc_html_e( 'Archives by Month', 'commentpress-core' ); ?></h3>

							<ul>
								<?php wp_get_archives( 'type=monthly' ); ?>
							</ul>

						</div>

						<div class="categories">

							<h3><?php esc_html_e( 'Categories', 'commentpress-core' ); ?></h3>

							<ul>
								<?php

								// Configure.
								$defaults = [
									'show_option_all' => '',
									'orderby' => 'name',
									'order' => 'ASC',
									'show_last_update' => 0,
									'style' => 'list',
									'show_count' => 0,
									'hide_empty' => 0,
									'use_desc_for_title' => 1,
									'child_of' => 0,
									'feed' => '',
									'feed_type' => '',
									'feed_image' => '',
									'exclude' => '',
									'exclude_tree' => '',
									'current_category' => 0,
									'hierarchical' => true,
									'title_li' => '',
									'echo' => 1,
									'depth' => 0,
								];

								// Show them.
								wp_list_categories( $defaults );

								?>
							</ul>

						</div>

						<div class="tags">

							<h3><?php esc_html_e( 'Tags', 'commentpress-core' ); ?></h3>

							<?php

							/*
							// Testing tag list.
							echo get_the_tag_list( '<ul><li>', '</li><li>', '</li></ul>' );
							*/

							// Configure.
							$args = [
								'smallest' => 1,
								'largest' => 1,
								'unit' => 'em',
								'number' => 0,
								'format' => 'list',
								'separator' => '\n',
								'orderby' => 'name',
								'order' => 'ASC',
								'link' => 'view',
								'taxonomy' => 'post_tag',
								'echo' => false,
							];

							// Get them.
							$tags = wp_tag_cloud( $args );

							?>

							<?php if ( $tags != '' ) : ?>
								<?php echo $tags; ?>
							<?php else : ?>
								<ul>
									<li class="no_tags"><?php esc_html_e( 'No tags yet', 'commentpress-core' ); ?></li>
								</ul>
							<?php endif; ?>

						</div>

					<?php endwhile; ?>

				<?php else : ?>

					<h2 class="post_title"><?php esc_html_e( 'Page Not Found', 'commentpress-core' ); ?></h2>
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
