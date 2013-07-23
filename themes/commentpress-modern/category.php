<?php get_header(); ?>



<!-- category.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content" class="clearfix">

<div class="post">



<?php if (have_posts()) : ?>

	<?php //$post = $posts[0]; // Hack. Set $post so that the_date() works. ?>
	<?php /* If this is a category archive */ if (is_category()) { ?>
	<h3 class="post_title"><?php echo sprintf( __( 'Archive for the &#8216;%s&#8217; Category', 'commentpress-core' ), single_cat_title( '', false ) ) ?></h3>
	<?php /* If this is a tag archive */ } elseif( is_tag() ) { ?>
	<h3 class="post_title"><?php echo sprintf( __( 'Posts Tagged &#8216;%s&#8217;', 'commentpress-core' ), single_cat_title( '', false ) ) ?></h3>
	<?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
	<h3 class="post_title"><?php _e( 'Archives', 'commentpress-core' ); ?></h3>
	<?php } ?>
	
	<?php while (have_posts()) : the_post(); ?>

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
		
		</div><!-- /archive_item -->
	
	<?php endwhile; ?>


	
<?php else : ?>

	<h2 class="post_title"><?php _e( 'Not Found', 'commentpress-core' ); ?></h2>

	<p><?php _e( "Sorry, but you are looking for something that isn't here.", 'commentpress-core' ); ?></p>
	
	<?php get_search_form(); ?>

<?php endif; ?>



</div><!-- /post -->

</div><!-- /content -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>