<?php
/**
 * Template Name: Welcome
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $post;

// Get core plugin reference.
$core = commentpress_core();

// Init.
$next_page_html = '';

// If we have the plugin.
if ( ! empty( $core ) ) {

	// "Welcome Page" always points to the first readable Page, unless it is itself.
	$next_page_id = $core->nav->page_get_first();

	// If the link does not point to this Page and we're allowing Page nav.
	if ( $next_page_id != $post->ID && false === $core->nav->page_nav_is_disabled() ) {

		// Get Page attributes.
		$page_title = get_the_title( $next_page_id );
		$target = get_permalink( $next_page_id );

		// Set the link.
		$next_page_html = '<a href="' . $target . '" id="next_page" class="css_btn" title="' . esc_attr( $page_title ) . '">' .
			$page_title .
		'</a>';

	}

}

get_header();

?>
<!-- welcome.php -->
<div id="wrapper">
	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">

			<?php if ( have_posts() ) : ?>

				<?php while ( have_posts() ) : ?>

					<?php the_post(); ?>

					<?php commentpress_get_feature_image(); ?>

					<?php if ( ! commentpress_has_feature_image() ) : ?>
						<?php if ( $next_page_html != '' ) : ?>
							<div class="page_navigation">
								<ul>
									<li class="alignright">
										<?php echo $next_page_html; ?>
									</li>
								</ul>
							</div><!-- /page_navigation -->
						<?php endif; ?>
					<?php endif; ?>

					<div id="content">
						<div id="post-<?php the_ID(); ?>" <?php post_class(); ?>">

							<?php if ( ! commentpress_has_feature_image() ) : ?>

								<h2 class="post_title"<?php commentpress_post_title_visibility( get_the_ID() ); ?>><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>

								<div class="search_meta"<?php commentpress_post_meta_visibility( get_the_ID() ); ?>>
									<?php commentpress_echo_post_meta(); ?>
								</div>

							<?php endif; ?>

							<?php the_content(); ?>

							<?php echo commentpress_multipager(); ?>

						</div><!-- /post -->
					</div><!-- /content -->

				<?php endwhile; ?>

				<?php if ( $next_page_html != '' ) : ?>
					<div class="page_nav_lower">
						<div class="page_navigation">
							<ul>
								<li class="alignright">
									<?php echo $next_page_html; ?>
								</li>
							</ul>
						</div><!-- /page_navigation -->
					</div><!-- /page_nav_lower -->
				<?php endif; ?>

			<?php else : ?>

				<div id="content">
					<div class="post">
						<h2 class="post_title"><?php esc_html_e( 'Page Not Found', 'commentpress-core' ); ?></h2>
						<p><?php esc_html_e( 'Sorry, but you are looking for something that isn’t here.', 'commentpress-core' ); ?></p>
						<?php get_search_form(); ?>
					</div><!-- /post -->
				</div><!-- /content -->

			<?php endif; ?>

		</div><!-- /page_wrapper -->
	</div><!-- /main_wrapper -->
</div><!-- /wrapper -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
