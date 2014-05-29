<?php get_header(); ?>



<!-- single.php -->

<div id="wrapper">



<?php if (have_posts()) : while (have_posts()) : the_post(); 

// access post
global $post;



// init class values
$tabs_class = '';
$tabs_classes = '';

// init workflow items
$original = '';
$literal = '';

// do we have workflow?
if ( is_object( $commentpress_core ) ) {

	// get workflow
	$_workflow = $commentpress_core->db->option_get( 'cp_blog_workflow' );
	
	// is it enabled?
	if ( $_workflow == '1' ) {
	
		// okay, let's add our tabs
		
		// set key
		$key = '_cp_original_text';
		
		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {
		
			// get it
			$original = get_post_meta( $post->ID, $key, true );
			
		}

		// set key
		$key = '_cp_literal_translation';
		
		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {
		
			// get it
			$literal = get_post_meta( $post->ID, $key, true );
			
		}
		
		// did we get either type of workflow content?
		if ( $literal != '' OR $original != '' ) {
		
			// override tabs class
			$tabs_class = 'with-content-tabs';
	
			// override tabs classes
			$tabs_classes = ' class="'.$tabs_class.'"';
			
			// prefix with space
			$tabs_class = ' '.$tabs_class;
			
		}
		
	}
	
}


?>



<div id="main_wrapper" class="clearfix<?php echo $tabs_class; ?>">



<?php 

// did we get tabs?
if ( $tabs_class != '' ) {
	
	// did we get either type of workflow content?
	if ( $literal != '' OR $original != '' ) {
	
	?>
	<ul id="content-tabs">
		<li id="content_header" class="default-content-tab"><h2><a href="#content"><?php 
			echo apply_filters( 
				'commentpress_content_tab_content', 
				__( 'Content', 'commentpress-core' )
			); 
		?></a></h2></li>
		<?php if ( $literal != '' ) { ?>
		<li id="literal_header"><h2><a href="#literal"><?php 
			echo apply_filters( 
				'commentpress_content_tab_literal', 
				__( 'Literal', 'commentpress-core' )
			); 
		?></a></h2></li>
		<?php } ?>
		<?php if ( $original != '' ) { ?>
		<li id="original_header"><h2><a href="#original"><?php 
			echo apply_filters( 
				'commentpress_content_tab_original', 
				__( 'Original', 'commentpress-core' )
			);
		?></a></h2></li>
		<?php } ?>
	</ul>
	<?php
	
	}
	
}
		
?>

<div id="page_wrapper"<?php echo $tabs_classes; ?>>



<div id="content" class="workflow-wrapper">



<div class="post<?php echo commentpress_get_post_css_override( get_the_ID() ); ?>" id="post-<?php the_ID(); ?>">



<h2 class="post_title"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>

<div class="search_meta">
	
	<?php commentpress_echo_post_meta(); ?>
	
</div>



<?php commentpress_get_post_version_info( $post ); ?>



<?php global $more; $more = true; the_content(''); ?>



<?php

// NOTE: Comment permalinks are filtered if the comment is not on the first page 
// in a multipage post... see: commentpress_multipage_comment_link in functions.php
echo commentpress_multipager();

?>



<?php the_tags( '<p class="postmetadata">' . __( 'Tags: ', 'commentpress-core' ), ', ', '</p>'); ?>



<p class="postmetadata"><?php
	
	// define RSS text
	$rss_text = __( 'RSS 2.0', 'commentpress-core' );
	
	// construct RSS link
	$rss_link = '<a href="'.esc_url( get_post_comments_feed_link() ).'">'.$rss_text.'</a>';
	
	// show text
	echo sprintf( 
		__( 'This entry is filed under %1$s. You can follow any comments on this entry through the %2$s feed.', 'commentpress-core' ), 
		get_the_category_list( ', ' ),
		$rss_link
	);
	
	// add trailing space
	echo ' ';
	
	if (('open' == $post->comment_status) AND ('open' == $post->ping_status)) {
		
		// both comments and pings are open

		// define trackback text
		$trackback_text = __( 'trackback', 'commentpress-core' );
	
		// construct RSS link
		$trackback_link = '<a href="'.esc_url( get_trackback_url() ).'"rel="trackback">'.$trackback_text.'</a>';
		
		// write out
		echo sprintf(
			__( 'You can leave a comment, or %s from your own site.' ),
			$trackback_link
		);
		
		// add trailing space
		echo ' ';
	
	} elseif (!('open' == $post->comment_status) AND ('open' == $post->ping_status)) {
	
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
	
	} elseif (('open' == $post->comment_status) AND !('open' == $post->ping_status)) {
	
		// comments are open, pings are not 
		_e( 'You can leave a comment. Pinging is currently not allowed.', 'commentpress-core' ); 
		
		// add trailing space
		echo ' ';
	
	} elseif (!('open' == $post->comment_status) AND !('open' == $post->ping_status)) {
		
		// neither comments nor pings are open 
		_e( 'Both comments and pings are currently closed.', 'commentpress-core' ); 
		
		// add trailing space
		echo ' ';
	
	}
	
	// show edit link
	edit_post_link( __( 'Edit this entry', 'commentpress-core' ), '', '.' ); 
	
?></p>



</div><!-- /post -->



</div><!-- /content -->



<?php 

// did we get tabs?
if ( $tabs_class != '' ) {

	// did we get either type of workflow content?
	if ( $literal != '' OR $original != '' ) {
	
	// did we get literal?
	if ( $literal != '' ) {
	
	?>
	<div id="literal" class="workflow-wrapper">
	
	<div class="post">
	
	<h2 class="post_title"><?php 
		echo apply_filters( 
			'commentpress_literal_title', 
			__( 'Literal Translation', 'commentpress-core' )
		); 
	?></h2>
	
	<?php echo wpautop(convert_chars(wptexturize( stripslashes( $literal ) ))); ?>
	
	</div><!-- /post -->
	
	</div><!-- /literal -->
	
	<?php } ?>
	
	
	<?php
	
	// did we get original?
	if ( $original != '' ) {
	
	?>
	
	<div id="original" class="workflow-wrapper">
	
	<div class="post">
	
	<h2 class="post_title"><?php 
		echo apply_filters( 
			'commentpress_original_title', 
			__( 'Original Text', 'commentpress-core' )
		); 
	?></h2>
	
	<?php echo wpautop(convert_chars(wptexturize( stripslashes( $original ) ))); ?>
	
	</div><!-- /post -->
	
	</div><!-- /original -->
	
	<?php } ?>
	
	
	
	<?php
	
	}
	
}
		
?>



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



<?php endwhile; else: ?>



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content">



<div class="post">

<h2 class="post_title"><?php _e( 'Post Not Found', 'commentpress-core' ); ?></h2>

<p><?php _e( 'Sorry, no posts matched your criteria.', 'commentpress-core' ); ?></p>

<?php get_search_form(); ?>

</div><!-- /post -->



</div><!-- /content -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



<?php endif; ?>



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>