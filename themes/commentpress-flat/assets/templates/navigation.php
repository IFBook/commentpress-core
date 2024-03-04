<?php
/**
 * Navigation Template.
 *
 * TODO: We need to account for situations where no CommentPress Core Special Pages exist.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Get core plugin reference.
$core = commentpress_core();

// Get the ID and URL for the "Welcome Page".
if ( ! empty( $core ) ) {
	$title_id  = $core->db->setting_get( 'cp_welcome_page' );
	$title_url = $core->pages_legacy->get_page_url( 'cp_welcome_page' );
}

?>
<!-- themes/commentpress-flat/assets/templates/navigation.php -->
<div id="document_nav">
	<div id="document_nav_wrapper">

		<ul id="nav">

			<?php if ( ! empty( $core ) ) : ?>

				<?php if ( is_multisite() ) : ?>

					<?php $site_title = commentpress_navigation_network_home_title(); ?>

					<li>
						<a href="<?php echo network_home_url(); ?>" id="btn_home" class="css_btn" title="<?php echo esc_attr( $site_title ); ?>"><?php echo esc_html( $site_title ); ?></a>
					</li>

					<?php

					/**
					 * Fires after the Network Home title.
					 *
					 * @since 3.4
					 */
					do_action( 'cp_nav_after_network_home_title' );

					?>

					<?php if ( $core->bp->is_groupblog() ) : ?>

						<?php

						// Link to Group in multisite Group Blog.

						// Check if this Blog is a Group Blog.
						$group_id = get_groupblog_group_id( get_current_blog_id() );

						?>

						<?php if ( ! empty( $group_id ) && is_numeric( $group_id ) ) : ?>

							<?php

							// When this Blog is a Group Blog.
							$group       = groups_get_group( [ 'group_id' => $group_id ] );
							$group_url   = bp_get_group_permalink( $group );
							$group_title = commentpress_navigation_group_home_title();

							?>

							<li>
								<a href="<?php echo esc_url( $group_url ); ?>" id="btn_grouphome" class="css_btn" title="<?php echo esc_attr( $group_title ); ?>"><?php echo esc_html( $group_title ); ?></a>
							</li>

						<?php endif; ?>

					<?php endif; ?>

				<?php else : ?>

					<?php if ( (int) get_option( 'page_on_front' ) !== (int) $title_id ) : ?>

						<?php $home_title = commentpress_navigation_blog_home_title(); ?>

						<li>
							<a href="<?php echo home_url(); ?>" id="btn_home" class="css_btn" title="<?php echo esc_attr( $home_title ); ?>"><?php echo esc_html( $home_title ); ?></a>
						</li>

					<?php endif; ?>

				<?php endif; ?>

				<?php if ( ! empty( $title_url ) ) : ?>

					<?php $title_title = commentpress_navigation_title_page_title(); ?>

					<li>
						<a href="<?php echo $title_url; ?>" id="btn_cover" class="css_btn" title="<?php echo esc_attr( $title_title ); ?>"><?php echo esc_html( $title_title ); ?></a>
					</li>

				<?php endif; ?>

				<?php

				/**
				 * Fires before Special Page links are rendered.
				 *
				 * @since 3.9
				 */
				do_action( 'cp_nav_before_special_pages' );

				// Show link to General Comments Page if we have one.
				echo $core->pages_legacy->get_page_link( 'cp_general_comments_page' );

				// Show link to All Comments Page if we have one.
				echo $core->pages_legacy->get_page_link( 'cp_all_comments_page' );

				// Show link to Comments-by-User Page if we have one.
				echo $core->pages_legacy->get_page_link( 'cp_comments_by_page' );

				// Show link to document Blog Page if we have one.
				echo $core->pages_legacy->get_page_link( 'cp_blog_page' );

				// Show link to document Blog Archive Page if we have one.
				echo $core->pages_legacy->get_page_link( 'cp_blog_archive_page' );

				?>

			<?php endif; /* End of core list items. */ ?>

			<?php if ( is_multisite() ) : ?>

				<?php if ( get_option( 'users_can_register' ) ) : ?>
					<?php /* This works for get_site_option( 'registration' ) == 'none' and 'user'. */ ?>
					<li>
						<?php wp_register( ' ', ' ' ); ?>
					</li>
				<?php endif; /* End of Users can register check. */ ?>

				<?php if ( ( is_user_logged_in() && get_site_option( 'registration' ) == 'blog' ) || get_site_option( 'registration' ) == 'all' ) : ?>

					<?php $new_site_title = commentpress_navigation_new_site_title(); ?>

					<?php if ( function_exists( 'bp_get_blogs_root_slug' ) ) : /* BuddyPress Site Tracking is active. */ ?>

						<?php if ( is_user_logged_in() ) : ?>

							<li>
								<a href="<?php echo trailingslashit( bp_get_blogs_directory_permalink() . 'create' ); ?>" title="<?php echo esc_attr( $new_site_title ); ?>" id="btn_create"><?php echo esc_html( $new_site_title ); ?></a>
							</li>

						<?php endif; ?>

					<?php else : /* Standard WordPress multisite. */ ?>

						<li<?php echo ( commentpress_page_navigation_is_signup() ? ' class="active_page"' : '' ); ?>>
							<a href="<?php echo network_site_url(); ?>wp-signup.php" title="<?php esc_attr( $new_site_title ); ?>" id="btn_create"><?php echo esc_html( $new_site_title ); ?></a>
						</li>

					<?php endif; ?>

				<?php endif; ?>

			<?php else : /* End of multisite. */ ?>

				<?php if ( is_user_logged_in() ) : ?>

					<?php $dashboard_title = commentpress_navigation_dashboard_title(); ?>

					<li>
						<a href="<?php echo admin_url(); ?>" title="<?php echo esc_attr( $dashboard_title ); ?>" id="btn_dash"><?php echo esc_html( $dashboard_title ); ?></a>
					</li>

				<?php endif; ?>

			<?php endif; ?>

			<li<?php echo ( commentpress_page_navigation_is_login() ? ' class="active_page"' : '' ); ?>>
				<?php wp_loginout(); ?>
			</li>

		</ul>

	</div><!-- /document_nav_wrapper -->
</div><!-- /document_nav -->
