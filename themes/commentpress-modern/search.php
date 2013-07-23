<?php get_header(); ?>



<!-- search.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<?php

// until WordPress supports a locate_theme_file() function, use filter
$page_navigation = apply_filters( 
	'cp_template_page_navigation',
	get_template_directory() . '/assets/templates/page_navigation.php'
);

// always include
include( $page_navigation );

?>



<div id="content" class="clearfix">

<?php if ( isset( $_GET['s'] ) AND !empty( $_GET['s'] ) AND have_posts() ) : ?>

	<div class="post">

	<h3 class="post_title"><?php _e( 'Search Results for', 'commentpress-core' ); ?> &#8216;<?php the_search_query(); ?>&#8217;</h3>

	<?php
	
	global $commentpres_obj;
	
	// init
	$_special_pages = array();
	
	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {
	
		// get special pages
		$special_pages = $commentpress_core->db->option_get( 'cp_special_pages' );
	
		// do we have a special page array?
		if ( is_array( $special_pages ) AND count( $special_pages ) > 0 ) {
		
			// override
			$_special_pages = $special_pages;
		
		}
		
	}

	// loop
	while (have_posts()) : the_post();
	
	// exclude if CommentPress Core Special Page
	if ( !in_array( get_the_ID(), $_special_pages ) ) {
	
	?>

		<div class="search_result">

			<h3 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'commentpress-core' ); ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
			
			<?php
			
			// if we've elected to show the meta...
			if ( commentpress_get_post_meta_visibility( get_the_ID() ) ) {
		
			?>
			<div class="search_meta">
				
				<?php commentpress_echo_post_meta(); ?>
				
			</div>
			<?php
			
			}
		
			?>
			
			<?php the_excerpt() ?>
		
			<p class="search_meta"><?php the_tags('Tags: ', ', ', '<br />'); ?> <?php _e( 'Posted in', 'commentpress-core' ); ?> <?php the_category(', ') ?> | <?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></p>
		
		</div><!-- /search_result -->

	<?php 
	
	} // end check for special page
	
	endwhile; // end loop
	
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

// include page_navigation again
include( $page_navigation );

?>
</div><!-- /page_nav_lower -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>