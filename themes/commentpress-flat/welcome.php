<?php
/*
Template Name: Welcome
*/



global $post;

// "Title Page" always points to the first readable page, unless it is itself.
$next_page_id = $commentpress_core->nav->get_first_page();

// Init.
$next_page_html = '';

// If the link does not point to this page and we're allowing page nav.
if ( $next_page_id != $post->ID AND false === $commentpress_core->nav->page_nav_is_disabled() ) {

	// Get page attributes.
	$title = get_the_title( $next_page_id );
	$target = get_permalink( $next_page_id );

	// Set the link.
	$next_page_html = '<a href="' . $target . '" id="next_page" class="css_btn" title="' . esc_attr( $title ) . '">' . $title . '</a>';

}


get_header(); ?>



<!-- welcome.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<?php if (have_posts()) : while (have_posts()) : the_post(); ?>



<?php

// Show feature image.
commentpress_get_feature_image();

?>



<?php

// Do we have a featured image?
if ( ! commentpress_has_feature_image() ) :

	if ( $next_page_html != '' ) : ?>
		<div class="page_navigation">
			<ul>
				<li class="alignright">
					<?php echo $next_page_html; ?>
				</li>
			</ul>
		</div><!-- /page_navigation -->
	<?php endif;

endif; ?>



<div id="content">



<div class="post<?php echo commentpress_get_post_css_override( get_the_ID() ); ?>" id="post-<?php the_ID(); ?>">



	<?php

	// Do we have a featured image?
	if ( ! commentpress_has_feature_image() ) {

		// Default to hidden.
		$cp_title_visibility = ' style="display: none;"';

		// Override if we've elected to show the title.
		if ( commentpress_get_post_title_visibility( get_the_ID() ) ) {
			$cp_title_visibility = '';
		}

		?>
		<h2 class="post_title"<?php echo $cp_title_visibility; ?>><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>



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

		<?php

	}

	?>



	<?php global $more; $more = true; the_content(''); ?>



	<?php

	// NOTE: Comment permalinks are filtered if the comment is not on the first page
	// in a multipage post... see: commentpress_multipage_comment_link in functions.php
	echo commentpress_multipager();

	?>



</div><!-- /post -->



</div><!-- /content -->



<?php endwhile; else: ?>



<div id="content">

<div class="post">

	<h2 class="post_title"><?php _e( 'Page Not Found', 'commentpress-core' ); ?></h2>

	<p><?php _e( "Sorry, but you are looking for something that isn't here.", 'commentpress-core' ); ?></p>

	<?php get_search_form(); ?>

</div><!-- /post -->

</div><!-- /content -->



<?php endif; ?>



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



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>
