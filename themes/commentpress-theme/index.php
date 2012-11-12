<?php get_header(); ?>



<!-- index.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content" class="clearfix">

<div class="post">



<?php if (have_posts()) : ?>

	<?php while (have_posts()) : the_post(); ?>

	<div class="search_result">

		<h3 id="post-<?php the_ID(); ?>"><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>
		
		<div class="search_meta">
			
			<?php commentpress_echo_post_meta(); ?>
			
		</div>

		<?php the_excerpt() ?>
	
		<p class="search_meta"><?php the_tags('Tags: ', ', ', '<br />'); ?> Posted in <?php the_category(', ') ?> | <?php edit_post_link('Edit', '', ' | '); ?>  <?php comments_popup_link('No Comments &#187;', '1 Comment &#187;', '% Comments &#187;'); ?></p>
	
	</div><!-- /search_result -->

	<?php endwhile; ?>

<?php else : ?>

		<h2 class="post_title">No blog posts found</h2>
		
		<p>There are no blog posts yet. <?php
		
		// if logged in
		if ( is_user_logged_in() ) {
			
			// add a suggestion
			?> <a href="<?php admin_url(); ?>">Go to your dashboard to add one.</a><?php
			
		}
			
		?></p>
		
		<p>If you were looking for something that hasn't been found, try using the search form below.</p>

		<?php get_search_form(); ?>

<?php endif; ?>



</div><!-- /post -->

</div><!-- /content -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>