<?php get_header(); ?>



<!-- page.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<?php if (have_posts()) : while (have_posts()) : the_post(); ?>



<?php

// show feature image
commentpress_get_feature_image();

?>



<?php

// until WordPress supports a locate_theme_file() function, use filter
$page_navigation = apply_filters( 
	'cp_template_page_navigation',
	get_template_directory() . '/assets/templates/page_navigation.php'
);

// do we have a featured image?
if ( !commentpress_has_feature_image() ) {

	// always include
	include( $page_navigation );

}

?>



<div id="content">



<div class="post<?php echo commentpress_get_post_css_override( get_the_ID() ); ?>" id="post-<?php the_ID(); ?>">



	<?php

	// do we have a featured image?
	if ( !commentpress_has_feature_image() ) {

		// if we've elected to show the title...
		if ( commentpress_get_post_title_visibility( get_the_ID() ) ) {

		?>
		<h2 class="post_title"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>
		<?php
	
		}

		?>
	


		<?php
	
		// if we've elected to show the meta...
		if ( commentpress_get_post_meta_visibility( get_the_ID() ) ) {

		?>
		<div class="search_meta">
		
			<?php commentpress_echo_post_meta(); ?>
		
		</div>
		<?php
	
		}

	}

	?>
	
	
	
	<?php global $more; $more = true; the_content(''); ?>



	<?php
	
	// NOTE: Comment permalinks are filtered if the comment is not on the first page 
	// in a multipage post... see: commentpress_multipage_comment_link in functions.php
	echo commentpress_multipager();

	?>



	<?php
	
	// test for "Post Tags and Categories for Pages" plugin
	if ( class_exists( 'PTCFP' ) ) {
	
	?>
	<p class="search_meta"><?php the_tags('Tags: ', ', ', '<br />'); ?> <?php _e( 'Posted in', 'commentpress-core' ); ?> <?php the_category(', ') ?></p>
	<?php
	
	}
	
	?>
	
	
	
	<?php 

	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {
	
		// get page num
		$num = $commentpress_core->nav->get_page_number( get_the_ID() );
		
		//print_r( $num ); die();
	
		// if we get one
		if ( $num ) {
			
			// is it arabic?
			if ( is_numeric( $num ) ) {
			
				// add page number
				?><div class="running_header_bottom">page <?php echo $num; ?></div><?php 
		
			} else {
			
				// add page number
				?><div class="running_header_bottom">page <?php echo strtolower( $num ); ?></div><?php 
		
			}
			
		}
		
	} 
	
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



<div class="page_nav_lower">
<?php

// include page_navigation again
include( $page_navigation );

?>
</div><!-- /page_nav_lower -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>