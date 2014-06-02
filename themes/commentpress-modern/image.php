<?php get_header(); ?>



<!-- image.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content" class="clearfix">

<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

	<div class="post image_attachment" id="post-<?php the_ID(); ?>">
	
		<h2 class="post_title"><a href="<?php echo get_permalink( $post->post_parent ); ?>" rev="attachment" title="<?php _e( 'Back to gallery', 'commentpress-core' ) ?>"><?php echo get_the_title( $post->post_parent ); ?></a> <span>&raquo; <a href="<?php the_permalink(); ?>" class="attachment_permalink" title="<?php _e( 'Permalink for this image', 'commentpress-core' ) ?>"><?php the_title(); ?></a></span></h2>
		
		<p class="back_button"><a class="button" href="<?php echo get_permalink( $post->post_parent ); ?>" rev="attachment" title="<?php _e( 'Back to gallery', 'commentpress-core' ) ?>"><?php _e( 'Back to gallery', 'commentpress-core' ); ?></a></p>
	
		<div class="the_image_attachment">

			<p class="image_attachment_wrap"><a class="image_attachment_link" href="<?php echo wp_get_attachment_url( $post->ID ); ?>"><?php echo wp_get_attachment_image( $post->ID, 'medium' ); ?></a></p>
		
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
	
		<?php the_content( '<p>'.__( 'Read the rest of the text &raquo;', 'commentpress-core').'</p>' ); ?>

		<p class="postmetadata" style="clear: left;"><?php 
			
			echo sprintf(
				__( 'This image was posted on %1$s at %2$s and is filed under %3$s.', 'commentpress-core' ),
				esc_html( get_the_date( __( 'l, F jS, Y', 'commentpress-core' ) ) ),
				get_the_time(),
				get_the_category_list( ', ' )
			);
			
			?> <?php the_taxonomies(); ?> <?php
			
			// define RSS text
			$rss_text = __( 'RSS 2.0', 'commentpress-core' );
			
			// construct RSS link
			$rss_link = '<a href="'.esc_url( get_post_comments_feed_link() ).'">'.$rss_text.'</a>';
			
			echo sprintf(
				__( 'You can follow any comments on this image through the %s feed.', 'commentpress-core' ),
				$rss_link
			);
			
			if (('open' == $post-> comment_status) AND ('open' == $post->ping_status)) {
			
				// both comments and pings are open

				// define trackback text
				$trackback_text = __( 'trackback', 'commentpress-core' );
	
				// construct RSS link
				$trackback_link = '<a href="'.esc_url( get_trackback_url() ).'"rel="trackback">'.$trackback_text.'</a>';
		
				// write out
				echo sprintf(
					__( 'You are welcome to leave a comment, or %s from your own site.' ),
					$trackback_link
				);
		
				// add trailing space
				echo ' ';
	
			} elseif (!('open' == $post-> comment_status) AND ('open' == $post->ping_status)) {
			
				// only pings are open 

				// define trackback text
				$trackback_text = __( 'trackback', 'commentpress-core' );
	
				// construct RSS link
				$trackback_link = '<a href="'.esc_url( get_trackback_url() ).'"rel="trackback">'.$trackback_text.'</a>';
	
				// write out
				echo sprintf(
					__( 'Comments are currently closed, but you can %s from your own site.', 'commentpress-core' ),
					$trackback_link
				);
		
				// add trailing space
				echo ' ';
	
			} elseif (('open' == $post-> comment_status) AND !('open' == $post->ping_status)) {
			
				// comments are open, pings are not 
				_e( 'You can leave a comment. Pinging is currently not allowed.', 'commentpress-core' ); 
		
				// add trailing space
				echo ' ';
	
			} elseif (!('open' == $post-> comment_status) AND !('open' == $post->ping_status)) {
			
				// neither comments nor pings are open 
				_e( 'Both comments and pings are currently closed.', 'commentpress-core' ); 
		
				// add trailing space
				echo ' ';
	
			} 
			
			// show edit link
			edit_post_link( __( 'Edit this entry', 'commentpress-core' ), '', '.' ); 
			
		?></p>
		
		
		
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