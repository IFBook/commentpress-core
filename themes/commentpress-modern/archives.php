<?php 
/*
Template Name: Archive
*/



get_header(); ?>



<!-- archives.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content" class="clearfix">

<div class="post">



<?php the_post(); ?>

<h2 class="post_title"><?php the_title(); ?></h2>



<div class="archives_search_form">
<?php get_search_form(); ?>
</div>



<div class="archives_by_month">

<h3><?php _e( 'Archives by Month', 'commentpress-core' ); ?></h3>

<ul>
	<?php wp_get_archives('type=monthly'); ?>
</ul>

</div>



<div class="categories">

<h3><?php _e( 'Categories', 'commentpress-core' ); ?></h3>

<ul>
<?php 

// configure
$defaults = array(

	'show_option_all' => '', 
	'orderby' => 'name',
	'order' => 'ASC', 
	'show_last_update' => 0,
	'style' => 'list', 
	'show_count' => 0,
	'hide_empty' => 0, 
	'use_desc_for_title' => 1,
	'child_of' => 0, 
	'feed' => '', 
	'feed_type' => '',
	'feed_image' => '', 
	'exclude' => '', 
	'exclude_tree' => '', 
	'current_category' => 0,
	'hierarchical' => true, 
	'title_li' => '',
	'echo' => 1, 
	'depth' => 0

);

// show them
wp_list_categories( $defaults ); 

?>
</ul>

</div>



<div class="tags">

<h3><?php _e( 'Tags', 'commentpress-core' ); ?></h3>

<?php 

//echo get_the_tag_list('<ul><li>','</li><li>','</li></ul>');

$args = array(

	'smallest' => 1, 
	'largest' => 1,
	'unit' => 'em', 
	'number' => 0,  
	'format' => 'list',
	'separator' => '\n',
	'orderby' => 'name', 
	'order' => 'ASC',
	'link' => 'view', 
	'taxonomy' => 'post_tag', 
	'echo' => false

);

// get them
$tags = wp_tag_cloud( $args );

// did we get any?
if ( $tags != '' ) {

	echo $tags;

} else {

	echo '<ul><li class="no_tags">'.__( 'No tags yet', 'commentpress-core' ).'</li></ul>';

}

?>

</div>



</div><!-- /post -->

</div><!-- /content -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>