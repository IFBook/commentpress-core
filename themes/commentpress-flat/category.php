<?php
/**
 * Category Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

?>
<!-- category.php -->
<div id="wrapper">
	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">

			<div id="content" class="clearfix">
				<div class="post clearfix">

				<?php if ( have_posts() ) : ?>

					<?php /* If this is a category archive */ if ( is_category() ) : ?>
						<?php /* translators: %s: The name of the category. */ ?>
						<h3 class="post_title"><?php echo sprintf( __( 'Archive for the &#8216;%s&#8217; Category', 'commentpress-core' ), single_cat_title( '', false ) ); ?></h3>
					<?php /* If this is a tag archive */ elseif ( is_tag() ) : ?>
						<?php /* translators: %s: The name of the tag. */ ?>
						<h3 class="post_title"><?php echo sprintf( __( 'Posts Tagged &#8216;%s&#8217;', 'commentpress-core' ), single_cat_title( '', false ) ); ?></h3>
					<?php /* If this is a paged archive */ elseif ( isset( $_GET['paged'] ) && ! empty( $_GET['paged'] ) ) : ?>
						<h3 class="post_title"><?php esc_html_e( 'Archives', 'commentpress-core' ); ?></h3>
					<?php endif; ?>

					<?php while ( have_posts() ) : ?>

						<?php the_post(); ?>

						<div class="search_result">

							<h3 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php esc_attr_e( 'Permanent Link to', 'commentpress-core' ); ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>

							<div class="search_meta"<?php commentpress_post_meta_visibility( get_the_ID() ); ?>>
								<?php commentpress_echo_post_meta(); ?>
							</div>

							<?php the_excerpt(); ?>

							<p class="search_meta"><?php the_tags( __( 'Tags: ', 'commentpress-core' ), ', ', '<br />' ); ?> <?php esc_html_e( 'Posted in', 'commentpress-core' ); ?> <?php the_category( ', ' ); ?> | <?php edit_post_link( __( 'Edit', 'commentpress-core' ), '', ' | ' ); ?>  <?php comments_popup_link( __( 'No Comments &#187;', 'commentpress-core' ), __( '1 Comment &#187;', 'commentpress-core' ), __( '% Comments &#187;', 'commentpress-core' ) ); ?></p>

						</div><!-- /search_result -->

					<?php endwhile; ?>

				<?php else : ?>

					<h2 class="post_title"><?php esc_html_e( 'Not Found', 'commentpress-core' ); ?></h2>

					<p><?php esc_html_e( 'Sorry, but you are looking for something that isn’t here.', 'commentpress-core' ); ?></p>

					<?php get_search_form(); ?>

				<?php endif; ?>

				</div><!-- /post -->
			</div><!-- /content -->

		</div><!-- /page_wrapper -->
	</div><!-- /main_wrapper -->
</div><!-- /wrapper -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
