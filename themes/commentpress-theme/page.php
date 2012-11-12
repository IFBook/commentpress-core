<?php get_header(); ?>



<!-- page.php -->

<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content">



<?php if (have_posts()) : while (have_posts()) : the_post(); ?>



<div class="post<?php echo commentpress_get_post_css_override( get_the_ID() ); ?>" id="post-<?php the_ID(); ?>">



	<?php
	
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

	?>
	
	
	
	<?php global $more; $more = true; the_content(''); ?>



	<?php
	
	// NOTE: Comment permalinks are filtered if the comment is not on the first page 
	// in a multipage post... see: commentpress_multipage_comment_link in functions.php
	echo commentpress_multipager();

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



<?php endwhile; else: ?>



<div class="post">

	<h2 class="post_title">Page Not Found</h2>
	
	<p>Sorry, but you are looking for something that isn't here.</p>
	
	<?php get_search_form(); ?>

</div><!-- /post -->



<?php endif; ?>



</div><!-- /content -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>