<?php
/*
Template Name: All Comments
*/



// Get page content.
$_page_content = commentpress_get_all_comments_page_content();



get_header(); ?>



<!-- all-comments.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content">



<div class="post">

<h2 class="post_title"><?php

/**
 * Filter the title of the All Comments page.
 *
 * @since 3.4
 *
 * @param str The default title.
 * @return str The modified title.
 */
echo apply_filters( 'cp_page_all_comments_title', __( 'All Comments', 'commentpress-core' ) );

?></h2>

<div id="comments_in_page_wrapper">
<?php echo $_page_content; ?>
</div>

</div><!-- /post -->



</div><!-- /content -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>
