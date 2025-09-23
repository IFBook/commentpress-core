<?php
/**
 * Sidebar Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Get core plugin reference.
$core = commentpress_core();

// If we have the plugin enabled, get order from plugin options.
$_tab_order = [ 'comments', 'activity', 'contents' ];
if ( ! empty( $core ) ) {
	$_tab_order = $core->theme->sidebar->order_get();
}

// Get commentable status.
$is_commentable = commentpress_is_commentable();

?>
<!-- sidebar.php -->
<div id="sidebar">
	<div id="sidebar_inner">

		<ul id="sidebar_tabs">
			<?php

			// -----------------------------------------------------------------------------
			// SIDEBAR HEADERS.
			// -----------------------------------------------------------------------------
			foreach ( $_tab_order as $_tab ) {

				switch ( $_tab ) {

					// Comments Header.
					case 'comments':
						?>

						<li id="comments_header" class="sidebar_header">
							<h2><a href="#comments_sidebar">
								<?php

								/**
								 * Filters the Comments tab title.
								 *
								 * @since 3.4
								 *
								 * @param string The default Comments tab title.
								 */
								$cp_tab_title_comments = apply_filters( 'cp_tab_title_comments', __( 'Comments', 'commentpress-core' ) );

								echo esc_html( $cp_tab_title_comments );

								?>
							</a></h2>

							<?php

							// Show the minimise all button if we have the plugin enabled.
							if ( ! empty( $core ) ) {
								// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
								echo $core->display->get_minimise_all_button( 'comments' );
							}

							?>
						</li>

						<?php

						break;

					// Activity Header.
					case 'activity':
						// Do we want to show Activity Tab?
						if ( commentpress_show_activity_tab() ) {

							/**
							 * Filters the Activity tab title.
							 *
							 * @since 3.4
							 *
							 * @param string The default Activity tab title.
							 */
							$_activity_title = apply_filters( 'cp_tab_title_activity', __( 'Activity', 'commentpress-core' ) );

							?>
							<li id="activity_header" class="sidebar_header">
								<h2><a href="#activity_sidebar"><?php echo esc_html( $_activity_title ); ?></a></h2>
								<?php

								// Show the minimise all button if we have the plugin enabled.
								if ( ! empty( $core ) ) {
									// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
									echo $core->display->get_minimise_all_button( 'activity' );
								}

								?>
							</li>
							<?php

						}

						break;

					// Contents Header.
					case 'contents':
						?>

						<li id="toc_header" class="sidebar_header">
							<h2><a href="#toc_sidebar">
								<?php

								/**
								 * Filters the Contents tab title.
								 *
								 * @since 3.4
								 *
								 * @param string The default Contents tab title.
								 */
								$cp_tab_title_toc = apply_filters( 'cp_tab_title_toc', __( 'Contents', 'commentpress-core' ) );

								echo esc_html( $cp_tab_title_toc );

								?>
							</a></h2>
						</li>

						<?php

						break;

				} // End switch.

			} // End foreach.

			?>

		</ul>

		<?php

		// -----------------------------------------------------------------------------
		// THE SIDEBARS THEMSELVES.
		// -----------------------------------------------------------------------------

		// Access globals.
		global $post;

		// If we have the plugin enabled.
		if ( ! empty( $core ) ) {

			// Is it commentable?
			if ( $is_commentable ) {

				/**
				 * Try to locate template using WordPress method.
				 *
				 * @since 3.4
				 *
				 * @param string The existing path returned by WordPress.
				 */
				$cp_comments_sidebar = apply_filters( 'cp_template_comments_sidebar', locate_template( 'assets/templates/comments_sidebar.php' ) );

				// Load it if we find it.
				if ( ! empty( $cp_comments_sidebar ) ) {
					load_template( $cp_comments_sidebar );
				}

			}

			/**
			 * Try to locate template using WordPress method.
			 *
			 * @since 3.4
			 *
			 * @param string The existing path returned by WordPress.
			 */
			$cp_toc_sidebar = apply_filters( 'cp_template_toc_sidebar', locate_template( 'assets/templates/toc_sidebar.php' ) );

			// Load it if we find it.
			if ( ! empty( $cp_toc_sidebar ) ) {
				load_template( $cp_toc_sidebar );
			}

			// Do we want to show Activity Tab?
			if ( commentpress_show_activity_tab() ) {

				/**
				 * Try to locate template using WordPress method.
				 *
				 * @since 3.4
				 *
				 * @param string The existing path returned by WordPress.
				 */
				$cp_activity_sidebar = apply_filters( 'cp_template_activity_sidebar', locate_template( 'assets/templates/activity_sidebar.php' ) );

				// Load it if we find it.
				if ( ! empty( $cp_activity_sidebar ) ) {
					load_template( $cp_activity_sidebar );
				}

			}

		} else {

			// Default sidebar when plugin not active.
			?>
			<div id="toc_sidebar">

				<div class="sidebar_header">
					<h2>
						<?php

						/**
						 * Filters the Contents tab title.
						 *
						 * @since 3.4
						 *
						 * @param string The default Contents tab title.
						 */
						$cp_tab_title_toc = apply_filters( 'cp_tab_title_toc', __( 'Contents', 'commentpress-core' ) );

						echo esc_html( $cp_tab_title_toc );

						?>
					</h2>
				</div>

				<div class="sidebar_minimiser">

					<div class="sidebar_contents_wrapper">
						<ul>
							<?php wp_list_pages( 'sort_column=menu_order&title_li=' ); ?>
						</ul>
					</div><!-- /sidebar_contents_wrapper -->

				</div><!-- /sidebar_minimiser -->

			</div><!-- /toc_sidebar -->

		<?php } ?>

	</div><!-- /sidebar_inner -->
</div><!-- /sidebar -->
