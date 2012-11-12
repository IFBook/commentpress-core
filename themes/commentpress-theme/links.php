<?php
/*
Template Name: Links
*/
?>

<?php get_header(); ?>



<!-- links.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content" class="clearfix">

<div class="post">

<h2 class="post_title">Links:</h2>

<ul>
<?php wp_list_bookmarks(); ?>
</ul>

</div><!-- /post -->

</div><!-- /content -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>