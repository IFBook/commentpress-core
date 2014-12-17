<?php
/*
Template Name: Welcome
*/
?>

<?php get_header(); ?>



<!-- welcome.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content">



<?php if (have_posts()) : while (have_posts()) : the_post(); ?>



<div class="post<?php echo commentpress_get_post_css_override( get_the_ID() ); ?>" id="post-<?php the_ID(); ?>">



	<?php

	// default to hidden
	$cp_title_visibility = ' style="display: none;"';

	// override if we've elected to show the title...
	if ( commentpress_get_post_title_visibility( get_the_ID() ) ) {
		$cp_title_visibility = '';
	}

	?>
	<h2 class="post_title"<?php echo $cp_title_visibility; ?>><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>



	<?php

	// default to hidden
	$cp_meta_visibility = ' style="display: none;"';

	// overrideif we've elected to show the meta...
	if ( commentpress_get_post_meta_visibility( get_the_ID() ) ) {
		$cp_meta_visibility = '';
	}

	?>
	<div class="search_meta"<?php echo $cp_meta_visibility; ?>>
		<?php commentpress_echo_post_meta(); ?>
	</div>



	<?php global $more; $more = true; the_content(''); ?>



	<?php

	// NOTE: Comment permalinks are filtered if the comment is not on the first page
	// in a multipage post... see: commentpress_multipage_comment_link in functions.php
	echo commentpress_multipager();

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