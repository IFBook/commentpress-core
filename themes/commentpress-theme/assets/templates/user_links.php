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
				<li>
					<?php wp_register( ' ', ' ' ); ?>
				</li>
			<?php endif; ?>

			<?php if ( ( is_user_logged_in() && get_site_option( 'registration' ) === 'blog' ) || get_site_option( 'registration' ) === 'all' ) : ?>

				<?php $new_site_title = commentpress_navigation_new_site_title(); ?>

				<?php if ( function_exists( 'bp_get_blogs_root_slug' ) ) : /* BuddyPress Site Tracking is active. */ ?>

					<?php if ( is_user_logged_in() ) : ?>

						<li>
							<a href="<?php echo trailingslashit( bp_get_blogs_directory_permalink() . 'create' ); ?>" title="<?php echo esc_attr( $new_site_title ); ?>" id="btn_create"><?php echo esc_html( $new_site_title ); ?></a>
						</li>

					<?php endif; ?>

				<?php else : /* Standard WordPress multisite. */ ?>

					<li>
						<a href="<?php echo network_site_url(); ?>wp-signup.php" title="<?php echo esc_attr( $new_site_title ); ?>" id="btn_create" class="button"><?php echo esc_html( $new_site_title ); ?></a>
					</li>

				<?php endif; ?>

			<?php endif; ?>

		<?php else : ?>

			<?php if ( is_user_logged_in() ) : ?>

				<?php $dashboard_title = commentpress_navigation_dashboard_title(); ?>

				<li><a href="<?php echo admin_url(); ?>" title="<?php echo esc_attr( $dashboard_title ); ?>" id="btn_dash" class="button"><?php echo esc_html( $dashboard_title ); ?></a></li>
			<?php endif; ?>

		<?php endif; ?>
	</ul>

</div><!-- /user_links.php -->
