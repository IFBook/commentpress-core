<?php
/*
Template Name: Comments by Commenter
*/



// Get page content.
$_page_content = commentpress_get_comments_by_page_content();



get_header(); ?>



<!-- all-comments.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content">



<div class="post">

<h2 class="post_title"><?php _e( 'Comments by Commenter', 'commentpress-core' ); ?></h2>

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
