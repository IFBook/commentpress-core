<?php
/**
 * Template Name: Author
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Get the User object for the Author.
if ( ! empty( get_query_var( 'author_name' ) ) ) {
	$my_author = get_user_by( 'login', get_query_var( 'author_name' ) );
} elseif ( ! empty( $author ) ) {
	$my_author = get_userdata( (int) $author );
} else {
	$my_author = get_queried_object();
}

// Do we have an URL for this User?
$my_author_URL = '';
if ( ! empty( $my_author->user_url ) && 'http://' !== $my_author->user_url && 'https://' !== $my_author->user_url ) {
	$my_author_URL = $my_author->user_url;
}

// Select Author name.
$my_author_name = __( 'Anonymous', 'commentpress-core' );
if ( ! empty( $my_author->display_name ) ) {
	$my_author_name = $my_author->display_name;
} elseif ( ! empty( $my_author->nickname ) ) {
	$my_author_name = $my_author->nickname;
}

get_header();

?>
<!-- author.php -->
<div id="wrapper">
	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">

			<div id="content" class="clearfix">
				<div class="post">

					<h2 class="post_title"><?php echo esc_html( $my_author_name ); ?></h2>

					<p><?php echo get_avatar( $my_author->user_email, $size = '128' ); ?></p>

					<dl>

						<?php if ( ! empty( $my_author->description ) ) : ?>
							<dt><?php esc_html_e( 'Profile', 'commentpress-core' ); ?></dt>
							<dd><?php echo nl2br( esc_html( $my_author->description ) ); ?></dd>
						<?php endif; ?>

						<?php if ( ! empty( $my_author_URL ) ) : ?>
							<dt><?php esc_html_e( 'Website', 'commentpress-core' ); ?></dt>
							<dd><a href="<?php echo $my_author_URL; ?>"><?php echo esc_html( $my_author_URL ); ?></a></dd>
						<?php endif; ?>

						<?php if ( ! empty( $my_author->user_email ) ) : ?>
							<dt><?php esc_html_e( 'Email', 'commentpress-core' ); ?></dt>
							<dd><a href="mailto:<?php echo esc_attr( $my_author->user_email ); ?>"><?php echo esc_html( $my_author->user_email ); ?></a></dd>
						<?php endif; ?>

						<?php if ( ! empty( $my_author->yim ) ) : ?>
							<dt><?php esc_html_e( 'Yahoo IM', 'commentpress-core' ); ?></dt>
							<dd><?php echo esc_html( $my_author->yim ); ?></dd>
						<?php endif; ?>

						<?php if ( ! empty( $my_author->aim ) ) : ?>
							<dt><?php esc_html_e( 'AIM', 'commentpress-core' ); ?></dt>
							<dd><?php echo esc_html( $my_author->aim ); ?></dd>
						<?php endif; ?>

						<?php if ( ! empty( $my_author->jabber ) ) : ?>
							<dt><?php esc_html_e( 'Jabber / Google Talk', 'commentpress-core' ); ?></dt>
							<dd><?php echo esc_html( $my_author->jabber ); ?></dd>
						<?php endif; ?>

					</dl>

					<h3><?php esc_html_e( 'Posts by', 'commentpress-core' ); ?> <?php echo esc_html( $my_author_name ); ?></h3>

					<?php if ( have_posts() ) : ?>

						<ul>
							<?php while ( have_posts() ) : ?>
								<?php the_post(); ?>
								<li>
									<?php

									$post_title = sprintf(
										/* translators: 1: The Post permalink, 2: The date published. */
										__( '%1$s (on %2$s)', 'commentpress-core' ),
										get_the_title(),
										get_the_time( get_option( 'date_format' ) )
									);

									printf(
										'<a href="%s" title="%s">%s</a>',
										get_permalink(),
										the_title_attribute( [
											'before' => __( 'Permanent Link:', 'commentpress-core' ),
											'after'  => '',
											'echo'   => false,
										] ),
										$post_title
									);

									?>
								</li>
							<?php endwhile; ?>
						</ul>

					<?php else : ?>

						<p><?php esc_html_e( 'No posts by this author.', 'commentpress-core' ); ?></p>

					<?php endif; ?>

				</div><!-- /post -->
			</div><!-- /content -->

		</div><!-- /page_wrapper -->
	</div><!-- /main_wrapper -->
</div><!-- /wrapper -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
