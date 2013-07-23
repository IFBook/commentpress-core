<?php get_header(); ?>



<!-- image.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content" class="clearfix">

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

	<div class="post image_attachment" id="post-<?php the_ID(); ?>">
	
		<h2 class="post_title"><a href="<?php echo get_permalink($post->post_parent); ?>" rev="attachment" title="<?php _e( 'Back to gallery', 'commentpress-core' ) ?>"><?php echo get_the_title($post->post_parent); ?></a> <span>&raquo; <a href="<?php the_permalink(); ?>" class="attachment_permalink" title="<?php _e( 'Permalink for this image', 'commentpress-core' ) ?>"><?php the_title(); ?></a></span></h2>
		
		<p class="back_button"><a class="button" href="<?php echo get_permalink($post->post_parent); ?>" rev="attachment" title="<?php _e( 'Back to gallery', 'commentpress-core' ) ?>"><?php _e( 'Back to gallery', 'commentpress-core' ); ?></a></p>
	
		<div class="the_image_attachment">

			<p class="image_attachment_wrap"><a class="image_attachment_link" href="<?php echo wp_get_attachment_url($post->ID); ?>"><?php echo wp_get_attachment_image( $post->ID, 'medium' ); ?></a></p>
		
			<div class="image_attachment_caption">
			<?php 
			// show "caption" if present
			if ( !empty($post->post_excerpt) ) {
				the_excerpt();
			} else {
				?>
				<p><?php _e( 'Untitled', 'commentpress-core' ) ?></p>
				<?php	
			}
			?>
			</div>
			
		</div>
	
		<?php the_content('<p>Read the rest of the text &raquo;</p>'); ?>

		<p class="postmetadata" style="clear: left;">
			This image was posted on <?php the_time('l, F jS, Y') ?> at <?php the_time() ?>
			and is filed under <?php the_category(', ') ?>.
			<?php the_taxonomies(); ?>
			You can follow any comments on this image through the <?php post_comments_feed_link('RSS 2.0'); ?> feed.

			<?php if (('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
				// Both Comments and Pings are open ?>
				You are welcome to <a href="#respond">leave a comment</a>, or <a href="<?php trackback_url(); ?>" rel="trackback">trackback</a> from your own site.

			<?php } elseif (!('open' == $post-> comment_status) && ('open' == $post->ping_status)) {
				// Only Pings are Open ?>
				Comments are currently closed, but you can <a href="<?php trackback_url(); ?> " rel="trackback">trackback</a> from your own site.

			<?php } elseif (('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
				// Comments are open, Pings are not ?>
				You are welcome to <a href="#respond">leave a comment</a>. Pinging is currently not allowed.

			<?php } elseif (!('open' == $post-> comment_status) && !('open' == $post->ping_status)) {
				// Neither Comments, nor Pings are open ?>
				Both comments and pings are currently closed.

			<?php } ?>
		</p>



		<ul class="image_link">
			<li class="alignright">
				<h4><?php _e( 'Next Image &raquo;', 'commentpress-core' ); ?></h4>
				<?php next_image_link() ?>
			</li>
			<li class="alignleft">
				<h4><?php _e( '&laquo; Previous Image', 'commentpress-core' ); ?></h4>
				<?php previous_image_link() ?>
			</li>
		</ul>
		
		
		
	</div><!-- /post -->



<?php endwhile; else: ?>

	<div class="post">

		<h2><?php _e( 'Not Found', 'commentpress-core' ); ?></h2>
	
		<p><?php _e( 'Sorry, no attachments matched your criteria.', 'commentpress-core' ); ?></p>

	</div><!-- /post -->

<?php endif; ?>



</div><!-- /content -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>