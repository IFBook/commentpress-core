<?php
/**
 * WordPress Activation Page Footer Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- footer-wp-activate.php -->
							</div><!-- /content --><?php /* Activate Page wrappers. */ ?>
						</div><!-- /page_wrapper -->
					</div><!-- /main_wrapper -->
				</div><!-- /wrapper -->

			</div><!-- /content_container --><?php /* Opened in "assets/templates/header_body.php" */ ?>

			<?php get_sidebar(); ?>

			<div id="footer">
				<div id="footer_inner">

					<?php if ( has_nav_menu( 'footer' ) ) : ?>

						<?php

						// Show footer menu if assigned.
						$footer_menu = [
							'theme_location'  => 'footer',
							'container_class' => 'commentpress-footer-nav-menu',
						];

						wp_nav_menu( $footer_menu );

						?>

					<?php endif; ?>

					<?php if ( is_active_sidebar( 'cp-license-8' ) ) : ?>

						<div class="footer_widgets">
							<?php dynamic_sidebar( 'cp-license-8' ); ?>
						</div>

					<?php else : ?>

						<p>
							<?php

							echo sprintf(
								/* translators: 1: Hame page link, 2: The current year. */
								esc_html__( 'Website content &copy; %1$s %2$s. All rights reserved.', 'commentpress-core' ),
								'<a href="' . esc_url( home_url() ) . '">' . esc_html( get_bloginfo( 'name' ) ) . '</a>',
								esc_html( gmdate( 'Y' ) )
							);

							?>
						</p>

					<?php endif; ?>

				</div><!-- /footer_inner -->
			</div><!-- /footer -->

		</div><!-- /container -->

		<?php wp_footer(); ?>

	</body>

</html>
