<?php
/*
Template Name: General Comments
*/
?>

<?php get_header(); ?>



<!-- all-comments.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content">



<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<div class="post general_comments">

<h2 class="post_title">General Comments</h2>

<?php comments_template(); ?>

</div><!-- /post -->

<?php endwhile; endif; ?>



</div><!-- /content -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>


<?php get_footer(); ?>