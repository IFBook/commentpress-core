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

// Init tab order - only relevant for old default theme.
$_tab_order = [ 'comments', 'activity' ];

// Get commentable status.
$is_commentable = commentpress_is_commentable();

?>
<!-- sidebar.php -->

<div id="sidebar">
	<div id="sidebar_inner">

		<ul id="sidebar_tabs">
			<?php

			// -----------------------------------------------------------------
			// SIDEBAR HEADERS.
			// -----------------------------------------------------------------
			foreach ( $_tab_order as $_tab ) {

				switch ( $_tab ) {

					// Comments Header.
					case 'comments':

						// Add active class.
						$active_class = '';
						if ( ! empty( $core ) && in_array( $core->theme->get_default_sidebar(), [ 'comments', 'toc' ] ) ) {
							$active_class = ' class="active-tab"';
						}

						?>

						<li id="comments_header" class="sidebar_header">
							<h2><a href="#comments_sidebar"<?php echo $active_class; ?>>
								<?php

								/**
								 * Filters the Comments tab title.
								 *
								 * @since 3.4
								 *
								 * @param str The default Comments tab title.
								 */
								echo apply_filters( 'cp_tab_title_comments', __( 'Comments', 'commentpress-core' ) );

								?>
							</a></h2>

							<?php

							// Show the minimise all button if we have the plugin enabled.
							if ( ! empty( $core ) ) {
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

							// Add class if not commentable.
							$active_class = '';
							if ( ! $is_commentable || ( ! empty( $core ) && 'activity' === $core->theme->get_default_sidebar() ) ) {
								$active_class = ' class="active-tab"';
							}

							/**
							 * Filters the Activity tab title.
							 *
							 * @since 3.4
							 *
							 * @param str The default Activity tab title.
							 */
							$_activity_title = apply_filters( 'cp_tab_title_activity', __( 'Activity', 'commentpress-core' ) );

							?>
							<li id="activity_header" class="sidebar_header">
								<h2><a href="#activity_sidebar"<?php echo $active_class; ?>><?php echo $_activity_title; ?></a></h2>

								<?php

								// Show the minimise all button if we have the plugin enabled.
								if ( ! empty( $core ) ) {
									echo $core->display->get_minimise_all_button( 'activity' );
								}

								?>
							</li>

							<?php

						}

						break;

				} // End switch.

			} // End foreach.

			?>

		</ul>

		<?php

		// ---------------------------------------------------------------------
		// THE SIDEBARS THEMSELVES.
		// ---------------------------------------------------------------------

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
				 * @param str The existing path returned by WordPress.
				 * @return str The modified path.
				 */
				$cp_comments_sidebar = apply_filters( 'cp_template_comments_sidebar', locate_template( 'assets/templates/comments_sidebar.php' ) );

				// Load it if we find it.
				if ( $cp_comments_sidebar != '' ) {
					load_template( $cp_comments_sidebar );
				}

			}

			// Do we want to show Activity Tab?
			if ( commentpress_show_activity_tab() ) {

				/**
				 * Try to locate template using WordPress method.
				 *
				 * @since 3.4
				 *
				 * @param str The existing path returned by WordPress.
				 * @return str The modified path.
				 */
				$cp_activity_sidebar = apply_filters( 'cp_template_activity_sidebar', locate_template( 'assets/templates/activity_sidebar.php' ) );

				// Load it if we find it.
				if ( $cp_activity_sidebar != '' ) {
					load_template( $cp_activity_sidebar );
				}

			}

		} else {

			// Default sidebar when plugin not active.
			?>
			<div id="toc_sidebar">
				<div class="sidebar_header">
					<h2><?php echo $_toc_title; ?></h2>
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
