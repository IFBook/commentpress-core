<?php
/**
 * User Links Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- user_links.php -->
<div id="user_links">

	<ul>
		<li><?php wp_loginout(); ?></li>

		<?php if ( is_multisite() ) : ?>

			<?php if ( get_option( 'users_can_register' ) ) : ?>
				<?php /* This works for get_site_option( 'registration' ) === 'none' and 'user' */ ?>
				<li><?php wp_register( ' ', ' ' ); ?></li>
			<?php endif; ?>

			<?php

			// Multisite signup and Blog create.
			if (
				( is_user_logged_in() && get_site_option( 'registration' ) === 'blog' )
				|| get_site_option( 'registration' ) === 'all'
			) {

				/**
				 * Filters the default Create a Site link name.
				 *
				 * @since 3.4
				 *
				 * @param str The default Create a Site link name.
				 */
				$new_site_title = apply_filters( 'cp_user_links_new_site_title', __( 'Create a new document', 'commentpress-core' ) );

				// Test whether we have BuddyPress.
				if ( function_exists( 'bp_get_blogs_root_slug' ) ) {

					// New Sites for logged-out Users are not directly allowed - done through Signup Page.
					if ( is_user_logged_in() ) {

						// BuddyPress uses its own Signup Page.
						?>
						<li><a href="<?php echo bp_get_root_domain() . '/' . bp_get_blogs_root_slug(); ?>/create/" title="<?php echo esc_attr( $new_site_title ); ?>" id="btn_create" class="button"><?php echo esc_html( $new_site_title ); ?></a></li>
						<?php

					}

				} else {

					// Standard WordPress multisite.
					?>
					<li><a href="<?php echo network_site_url(); ?>wp-signup.php" title="<?php echo esc_attr( $new_site_title ); ?>" id="btn_create" class="button"><?php echo esc_html( $new_site_title ); ?></a></li>
					<?php

				}

			}

			?>

		<?php else : ?>

			<?php if ( is_user_logged_in() ) : ?>
				<?php

				/**
				 * Filters the default Dashboard link name.
				 *
				 * @since 3.4
				 *
				 * @param str The default Dashboard link name.
				 */
				$dashboard_title = apply_filters( 'cp_user_links_dashboard_title', __( 'Dashboard', 'commentpress-core' ) );

				?>

				<li><a href="<?php echo admin_url(); ?>" title="<?php echo $dashboard_title; ?>" id="btn_dash" class="button"><?php echo $dashboard_title; ?></a></li>
			<?php endif; ?>

		<?php endif; ?>
	</ul>

</div><!-- /user_links.php -->
