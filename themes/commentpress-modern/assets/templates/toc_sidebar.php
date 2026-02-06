<?php
/**
 * Table of Contents Dropdown Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Get core plugin reference.
$core = commentpress_core();

?>
<!-- toc_sidebar.php -->

<div id="navigation">
	<div id="toc_sidebar" class="sidebar_container">

		<?php

		/**
		 * Fires before the Contents Tab.
		 *
		 * @since 3.4
		 */
		do_action( 'cp_content_tab_before' );

		?>

		<div class="sidebar_header">
			<h2><?php esc_html_e( 'Contents', 'commentpress-core' ); ?></h2>
		</div>

		<div class="sidebar_minimiser">
			<div class="sidebar_contents_wrapper">

				<?php

				// Allow Widgets to be placed above navigation.
				dynamic_sidebar( 'cp-nav-top' );

				?>

				<?php

				/**
				 * Fires before the Search accordion.
				 *
				 * @since 3.4
				 */
				do_action( 'cp_content_tab_before_search' );

				?>

				<h3 class="activity_heading search_heading">
					<?php

					/**
					 * Filters the Search accordion title.
					 *
					 * @since 3.4
					 *
					 * @param string The default Search accordion title.
					 */
					$cp_search_title = apply_filters( 'cp_content_tab_search_title', __( 'Search', 'commentpress-core' ) );

					echo esc_html( $cp_search_title );

					?>
				</h3>

				<div class="paragraph_wrapper search_wrapper">
					<div id="document_search">
						<?php get_search_form(); ?>
					</div><!-- /document_search -->
				</div>

				<?php if ( apply_filters( 'cp_content_tab_special_pages_visible', true ) ) : ?>
					<h3 class="activity_heading special_pages_heading">
						<?php

						/**
						 * Filters the Special Pages accordion title.
						 *
						 * @since 3.4
						 *
						 * @param string The default Special Pages accordion title.
						 */
						$cp_special_pages_title = apply_filters( 'cp_content_tab_special_pages_title', __( 'Special Pages', 'commentpress-core' ) );

						echo esc_html( $cp_special_pages_title );

						?>
					</h3>

					<div class="paragraph_wrapper special_pages_wrapper">
						<?php

						/**
						 * Try to locate template using WordPress method.
						 *
						 * @since 3.4
						 *
						 * @param string The existing path returned by WordPress.
						 */
						$cp_navigation = apply_filters( 'cp_template_navigation', locate_template( 'assets/templates/navigation.php' ) );

						// Load it if we find it.
						if ( ! empty( $cp_navigation ) ) {
							load_template( $cp_navigation );
						}

						?>
					</div>
				<?php endif; ?>

				<h3 class="activity_heading toc_heading">
					<?php

					/**
					 * Filters the Table of Contents accordion title.
					 *
					 * @since 3.4
					 *
					 * @param string The default Table of Contents accordion title.
					 */
					$cp_toc_title = apply_filters( 'cp_content_tab_toc_title', __( 'Table of Contents', 'commentpress-core' ) );

					echo esc_html( $cp_toc_title );

					?>
				</h3>

				<div class="paragraph_wrapper start_open">
					<ul id="toc_list">
						<?php if ( ! empty( $core ) ) : ?>
							<?php echo $core->display->get_toc_list(); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
						<?php else : ?>
							<?php wp_list_pages( 'sort_column=menu_order&title_li=' ); ?>
						<?php endif; ?>
					</ul>
				</div>

				<?php

				// Allow Widgets to be placed below navigation.
				dynamic_sidebar( 'cp-nav-bottom' );

				?>

				<?php

				/**
				 * Fires after the Contents Tab.
				 *
				 * @since 3.4
				 */
				do_action( 'cp_content_tab_after' );

				?>

			</div><!-- /sidebar_contents_wrapper -->
		</div><!-- /sidebar_minimiser -->

	</div><!-- /toc_sidebar -->
</div><!-- /navigation -->
