<?php get_header(); ?>



<!-- index.php -->

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

<div class="post">



<?php if (have_posts()) : ?>

	<?php while (have_posts()) : the_post(); ?>

	<div class="search_result">

		<h3 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', 'commentpress-core' ); ?> <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
		
		<div class="search_meta">
			
			<?php commentpress_echo_post_meta(); ?>
			
		</div>

		<?php the_excerpt() ?>
	
		<p class="search_meta"><?php the_tags('Tags: ', ', ', '<br />'); ?> <?php _e( 'Posted in', 'commentpress-core' ); ?> <?php the_category(', ') ?> | <?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></p>
	
	</div><!-- /search_result -->

	<?php endwhile; ?>

<?php else : ?>

		<h2 class="post_title"><?php _e( 'No blog posts found', 'commentpress-core' ); ?></h2>
		
		<p><?php _e( 'There are no blog posts yet.', 'commentpress-core' ); ?> <?php
		
		// if logged in
		if ( is_user_logged_in() ) {
			
			// add a suggestion
			?> <a href="<?php admin_url(); ?>"><?php _e( 'Go to your dashboard to add one.', 'commentpress-core' ); ?></a><?php
			
		}
			
		?></p>
		
		<p><?php _e( "If you were looking for something that hasn't been found, try using the search form below.", 'commentpress-core' ); ?></p>

		<?php get_search_form(); ?>

<?php endif; ?>



</div><!-- /post -->

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