<?php 
/*
Template Name: Directory
*/



get_header(); ?>



<!-- directory.php -->

<div id="wrapper">



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
	
	// init members-list plugin
	$members = new tern_members;
	
	// set options
	$members->members(
		
		array(
		
			'search' => true,
			'alpha' => true,
			'pagination' => true,
			'pagination2' => true,
			'radius' => false,
			'sort' => false
			
		)
		
	);
	
	?>
	


</div><!-- /post -->



<?php endwhile; else: ?>



<div class="post">

	<h2 class="post_title"><?php _e( 'Page Not Found', 'commentpress-core' ); ?></h2>
	
	<p><?php _e( "Sorry, but you are looking for something that isn't here.", 'commentpress-core' ); ?></p>
	
	<?php get_search_form(); ?>

</div><!-- /post -->



<?php endif; ?>



</div><!-- /content -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>