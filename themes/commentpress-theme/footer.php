<?php
/**
 * Footer Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- footer.php -->
			<div id="footer" class="clearfix">
				<div id="footer_inner">

					<?php if ( ! dynamic_sidebar( 'cp-license-8' ) ) : ?>

						<?php /* Compat with wpLicense plugin. */ ?>
						<?php if ( function_exists( 'isLicensed' ) && isLicensed() ) : ?>

							<?php /* Show the license from wpLicense. */ ?>
							<?php cc_showLicenseHtml(); ?>

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

					<?php endif; ?>

				</div><!-- /footer_inner -->
			</div><!-- /footer -->

		</div><!-- /container -->

		<?php wp_footer(); ?>

	</body>

</html>
