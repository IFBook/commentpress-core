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
									__( 'Website content &copy; %1$s %2$s. All rights reserved.', 'commentpress-core' ),
									'<a href="' . home_url() . '">' . get_bloginfo( 'name' ) . '</a>',
									gmdate( 'Y' )
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
