<?php get_header(); ?>



<!-- page.php -->

<div id="wrapper">



<?php if (have_posts()) : while (have_posts()) : the_post(); ?>



	<div id="main_wrapper" class="clearfix<?php echo commentpress_theme_tabs_class_get(); ?>">

		<?php commentpress_theme_tabs_render(); ?>

		<div id="page_wrapper"<?php echo commentpress_theme_tabs_classes_get(); ?>>

			<div id="content" class="workflow-wrapper">

				<div class="post<?php echo commentpress_get_post_css_override( get_the_ID() ); ?>" id="post-<?php the_ID(); ?>">

					<?php

					// Default to hidden.
					$cp_title_visibility = ' style="display: none;"';

					// Override if we've elected to show the title.
					if ( commentpress_get_post_title_visibility( get_the_ID() ) ) {
						$cp_title_visibility = '';
					}

					?>
					<h2 class="post_title"<?php echo $cp_title_visibility; ?>><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>

					<?php

					// Default to hidden.
					$cp_meta_visibility = ' style="display: none;"';

					// Override if we've elected to show the meta.
					if ( commentpress_get_post_meta_visibility( get_the_ID() ) ) {
						$cp_meta_visibility = '';
					}

					?>
					<div class="search_meta"<?php echo $cp_meta_visibility; ?>>
						<?php commentpress_echo_post_meta(); ?>
					</div>

					<?php global $more; $more = true; the_content(''); ?>

					<?php

					// Test for "Post Tags and Categories for Pages" plugin.
					if ( class_exists( 'PTCFP' ) ) {

					?>
					<p class="search_meta"><?php the_tags( __( 'Tags: ', 'commentpress-core' ), ', ', '<br />' ); ?> <?php _e( 'Posted in', 'commentpress-core' ); ?> <?php the_category( ', ' ); ?></p>
					<?php

					}

					?>

					<?php

					// NOTE: Comment permalinks are filtered if the comment is not on the first page
					// in a multipage post... see: commentpress_multipage_comment_link in functions.php
					echo commentpress_multipager();

					?>

					<?php

					// If we have the plugin enabled.
					if ( is_object( $commentpress_core ) ) {

						// Get page num.
						$num = $commentpress_core->nav->get_page_number( get_the_ID() );

						// If we get one.
						if ( $num ) {

							// Make lowercase if Roman.
							if ( ! is_numeric( $num ) ) {
								$num = strtolower( $num );
							}

							// Wrap number.
							$element = '<span class="page_num_bottom">' . $num . '</span>';

							// Add page number.
							?><div class="running_header_bottom"><?php
								echo sprintf( __( 'Page %s', 'commentpress-core' ), $element );
							?></div><?php

						}

					}

					?>

				</div><!-- /post -->

			</div><!-- /content -->

			<?php commentpress_theme_tabs_content_render(); ?>

		</div><!-- /page_wrapper -->

	</div><!-- /main_wrapper -->



<?php endwhile; else: ?>



	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">
			<div id="content">
				<div class="post">

					<h2 class="post_title"><?php _e( 'Page Not Found', 'commentpress-core' ); ?></h2>
					<p><?php _e( "Sorry, but you are looking for something that isn't here.", 'commentpress-core' ); ?></p>
					<?php get_search_form(); ?>

				</div><!-- /post -->
			</div><!-- /content -->
		</div><!-- /page_wrapper -->
	</div><!-- /main_wrapper -->



<?php endif; ?>



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>
