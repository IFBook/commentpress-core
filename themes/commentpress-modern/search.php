<?php get_header(); ?>



<!-- search.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<?php

/**
 * Try to locate template using WP method.
 *
 * @since 3.4
 *
 * @param str The existing path returned by WordPress.
 * @return str The modified path.
 */
$cp_page_navigation = apply_filters(
	'cp_template_page_navigation',
	locate_template( 'assets/templates/page_navigation.php' )
);

// Load it if we find it.
if ( $cp_page_navigation != '' ) load_template( $cp_page_navigation, false );

?>



<div id="content" class="clearfix">

<?php if ( isset( $_GET['s'] ) AND !empty( $_GET['s'] ) AND have_posts() ) : ?>

	<div class="post">

	<h3 class="post_title"><?php _e( 'Search Results for', 'commentpress-core' ); ?> &#8216;<?php the_search_query(); ?>&#8217;</h3>

	<?php

	global $commentpres_obj;

	// Init.
	$_special_pages = [];

	// If we have the plugin enabled.
	if ( is_object( $commentpress_core ) ) {

		// Get special pages.
		$special_pages = $commentpress_core->db->option_get( 'cp_special_pages' );

		// Do we have a special page array?
		if ( is_array( $special_pages ) AND count( $special_pages ) > 0 ) {

			// Override.
			$_special_pages = $special_pages;

		}

	}

	// Loop.
	while (have_posts()) : the_post();

	// Exclude if CommentPress Core Special Page.
	if ( !in_array( get_the_ID(), $_special_pages ) ) {

	?>

		<div class="search_result">

			<h3 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'commentpress-core' ); ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>

			<?php

			// Default to hidden.
			$cp_meta_visibility = ' style="display: none;"';

			// Override if we've elected to show the meta.
			if ( commentpress_get_post_meta_visibility( get_the_ID() ) ) {
				$cp_meta_visibility = '';
			}

			?>
			<div class="search_meta"<?php echo $cp_meta_visibility; ?>>
				<?php commentpress_echo_post_meta(); ?>
			</div>

			<?php the_excerpt() ?>

			<p class="search_meta"><?php the_tags( __( 'Tags: ', 'commentpress-core' ), ', ', '<br />' ); ?> <?php _e( 'Posted in', 'commentpress-core' ); ?> <?php the_category( ', ' ) ?> | <?php edit_post_link( __( 'Edit', 'commentpress-core' ), '', ' | '); ?>  <?php comments_popup_link( __( 'No Comments &#187;', 'commentpress-core' ), __( '1 Comment &#187;', 'commentpress-core' ), __( '% Comments &#187;', 'commentpress-core' ) ); ?></p>

		</div><!-- /search_result -->

	<?php

	} // End check for special page.

	endwhile; // End loop.

	?>

	</div><!-- /post -->

<?php else : ?>

	<div class="post">

	<h2><?php _e( 'Nothing found for', 'commentpress-core' ); ?> &#8216;<?php the_search_query(); ?>&#8217;</h2>

	<p><?php _e( 'Try a different search?', 'commentpress-core' ); ?></p>

	<?php get_search_form(); ?>

	</div><!-- /post -->

<?php endif; ?>

</div><!-- /content -->



<div class="page_nav_lower">
<?php

// Include page_navigation again.
if ( $cp_page_navigation != '' ) load_template( $cp_page_navigation, false );

?>
</div><!-- /page_nav_lower -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>
