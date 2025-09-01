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
} elseif ( ! empty( get_query_var( 'author' ) ) ) {
	$my_author = get_user_by( 'id', get_query_var( 'author' ) );
} elseif ( ! empty( $author ) ) {
	$my_author = get_userdata( (int) $author );
} else {
	$my_author = get_queried_object();
}

// Do we have an URL for this User?
$my_author_url = '';
if ( ! empty( $my_author->user_url ) && 'http://' !== $my_author->user_url && 'https://' !== $my_author->user_url ) {
	$my_author_url = $my_author->user_url;
}

// Select Author name.
$my_author_name = __( 'Anonymous', 'commentpress-core' );
if ( ! empty( $my_author->display_name ) ) {
	$my_author_name = $my_author->display_name;
} elseif ( ! empty( $my_author->nickname ) ) {
	$my_author_name = $my_author->nickname;
}

// Get avatar.
$my_avatar = '';
if ( ! empty( $my_author->user_email ) ) {
	$my_avatar = get_avatar( $my_author->user_email, $size = '200' );
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

					<?php if ( ! empty( $my_avatar ) ) : ?>
						<p><?php echo $my_avatar; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?></p>
					<?php endif; ?>

					<?php if ( ! empty( $my_author->description ) ) : ?>
						<p><?php echo nl2br( $my_author->description ); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?></p>
					<?php endif; ?>

					<!-- The Loop -->
					<?php if ( have_posts() ) : ?>

						<h3 class="author_pages_heading">
							<?php

							/**
							 * Filter the Posts List title.
							 *
							 * @since 3.4
							 *
							 * @param str The default Posts List title.
							 */
							$posts_list_title = apply_filters( 'cp_author_page_posts_list_title', __( 'Posts written by', 'commentpress-core' ) );

							echo esc_html( $posts_list_title ) . ' ' . esc_html( $my_author_name );

							?>
						</h3>

						<ul class="author_pages">
							<?php while ( have_posts() ) : ?>
								<?php the_post(); ?>
								<li>
									<?php

									$post_title = sprintf(
										/* translators: 1: The Post permalink, 2: The date published. */
										esc_html__( '%1$s on %2$s', 'commentpress-core' ),
										esc_html( get_the_title() ),
										esc_html( get_the_time( get_option( 'date_format' ) ) )
									);

									printf(
										'<a href="%s" title="%s">%s</a>',
										esc_url( get_permalink() ),
										the_title_attribute(
											[
												'before' => __( 'Permanent Link:', 'commentpress-core' ),
												'after'  => '',
												'echo'   => false,
											]
										),
										esc_html( $post_title )
									);

									?>
								</li>
							<?php endwhile; ?>
						</ul>

					<?php endif; ?>

					<?php if ( ! empty( $my_author->ID ) ) : ?>

						<?php

						// Define our args.
						$author_pages_args = [
							'post_status'    => 'publish',
							'post_type'      => 'page',
							'author'         => $my_author->ID,
							'posts_per_page' => 0,
							'no_found_rows'  => true,
						];

						// The Pages query.
						$author_pages = new WP_Query( $author_pages_args );

						?>

						<?php if ( $author_pages->have_posts() ) : ?>
							<h3 class="author_pages_heading">
								<?php

								/**
								 * Filter the Posts List title.
								 *
								 * @since 3.4
								 *
								 * @param str The default Posts List title.
								 */
								$pages_list_title = apply_filters( 'cp_author_page_pages_list_title', __( 'Pages written by', 'commentpress-core' ) );

								echo esc_html( $pages_list_title ) . ' ' . esc_html( $my_author_name );

								?>
							</h3>
							<ul class="author_pages">
								<?php while ( $author_pages->have_posts() ) : ?>
									<?php $author_pages->the_post(); ?>
									<li>
										<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
									</li>
								<?php endwhile; ?>
							</ul>
						<?php endif; ?>

						<?php wp_reset_postdata(); ?>

					<?php endif; ?>

				</div><!-- /post -->
			</div><!-- /content -->

		</div><!-- /page_wrapper -->
	</div><!-- /main_wrapper -->
</div><!-- /wrapper -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
