<?php
/*
Template Name: All Comments
*/



// get page content --> I prefer to do this before the page is sent
// to the browser: the markup is generated before anything is displayed
$_page_content = commentpress_get_all_comments_page_content();



get_header(); ?>



<!-- all-comments.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content">



<div class="post">



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