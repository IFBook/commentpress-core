<?php


/**
 * Enqueue accordion script.
 */
function my_js_needed() {
	// Enqueue accordion-like Javascript.
	wp_enqueue_script(
		'cp_special',
		get_template_directory_uri() . '/assets/js/cp_js_all_comments.js',
		null, // Dependencies.
		COMMENTPRESS_VERSION // Version.
	);
}

add_action( 'wp_enqueue_scripts', 'my_js_needed' );



// Get HTML for this template.
$html = commentpress_get_tagged_comments_content();



get_header(); ?>



<!-- taxonomy-comment_tags.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content" class="clearfix">

<div class="post">



<h3 class="post_title"><?php
	echo sprintf( __( 'Comments Tagged &#8216;%s&#8217;', 'commentpress-core' ), single_cat_title( '', false ) )
?></h3>

<div id="comments_in_page_wrapper">

<?php if ( ! empty( $html ) ) : ?>

	<?php echo $html; ?>

<?php else : ?>

	<h2 class="post_title"><?php _e( 'No Comments Found', 'commentpress-core' ); ?></h2>

	<p><?php _e( "Sorry, but there are no comments for this tag.", 'commentpress-core' ); ?></p>

	<?php get_search_form(); ?>

<?php endif; ?>

</div>



</div><!-- /post -->

</div><!-- /content -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>
