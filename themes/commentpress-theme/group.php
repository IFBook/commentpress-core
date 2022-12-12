<?php
/**
 * Template Name: Group
 *
 * Appears to be a list of all Users. Perhaps redundant.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Set args.
$args = [
	'orderby' => 'nicename',
];

// Get Users of this Blog (blog_id is provided by default).
$group_users = get_users( $args );

get_header();

?>
<!-- group.php -->
<div id="wrapper">
	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">

			<div id="content">
				<div class="post">

					<h2 class="post_title"><?php esc_html_e( 'Group Members', 'commentpress-core' ); ?></h2>

					<?php if ( ! empty( $group_users ) ) : ?>

						<ul id="group_list">

						<?php foreach ( $group_users as $group_user ) : ?>
							<?php if ( $group_user->user_id != '1' ) : ?>
								<li>
									<a href="<?php echo home_url(); ?>/author/<?php echo $group_user->user_login; ?>/"><?php echo esc_html( $group_user->display_name ); ?></a>
								</li>
							<?php endif; ?>
						<?php endforeach; ?>

						</ul>

					<?php endif; ?>

				</div><!-- /post -->
			</div><!-- /content -->

		</div><!-- /page_wrapper -->
	</div><!-- /main_wrapper -->
</div><!-- /wrapper -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
