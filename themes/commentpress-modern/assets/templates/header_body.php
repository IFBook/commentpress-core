<?php /*
================================================================================
HTML Body Header
================================================================================
AUTHOR			: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

Separated this out for inclusion in multiple files.

--------------------------------------------------------------------------------
*/



// Start HTML
?>
<a class="skip" href="#content">Skip to Content</a>
<span class="off-left"> | </span>
<a class="skip" href="#toc_list">Skip to Table of Contents</a><!-- /skip_links -->



<div id="header">
	
	<?php
	
	// get header image
	commentpress_get_header_image();
	
	?>
	<div id="page_title">
		<div id="title"><h1><a href="<?php echo home_url(); ?>" title="Home"><?php bloginfo('title'); ?></a></h1></div>
		<div id="tagline"><?php bloginfo('description'); ?></div>
	</div>
	


</div><!-- /header -->



<?php /* ?>
<div id="cp_book_nav">

<?php

// set default link names
$previous_title = apply_filters( 'cp_nav_previous_link_title', __( 'Older Entries', 'commentpress-core' ) );
$next_title = apply_filters( 'cp_nav_next_link_title', __( 'Newer Entries', 'commentpress-core' ) );

// is it a page?
if ( is_page() ) {

	// get our custom page navigation
	$cp_page_nav = commentpress_page_navigation();
	
	// if we get any...
	if ( $cp_page_nav != '' ) { 

		?><ul>
			<?php echo $cp_page_nav; ?>
		</ul>
		<?php
	
	}

}



// is it a post?
elseif ( is_single() ) {

	?><ul id="blog_navigation">
		<?php next_post_link('<li class="alignright">%link</li>'); ?>
		<?php previous_post_link('<li class="alignleft">%link</li>'); ?>
	</ul>
	
	<?php

}


// is this the blog home?
elseif ( is_home() ) {

	$nl = get_next_posts_link('&laquo; '.$previous_title);
	$pl = get_previous_posts_link($next_title.' &raquo;');
	
	// did we get either?
	if ( $nl != '' OR $pl != '' ) { ?>
	
	<ul id="blog_navigation">
		<?php if ( $nl != '' ) { ?><li class="alignright"><?php echo $nl; ?></li><?php } ?>
		<?php if ( $pl != '' ) { ?><li class="alignleft"><?php echo $pl; ?></li><?php } ?>
	</ul>
	
	<?php } ?>
	
	<?php

}



// archives?
elseif ( is_day() || is_month() || is_year() ) {

	$nl = get_next_posts_link('&laquo; '.$previous_title);
	$pl = get_previous_posts_link($next_title.' &raquo;');
	
	// did we get either?
	if ( $nl != '' OR $pl != '' ) { ?>
	
	<ul id="blog_navigation">
		<?php if ( $nl != '' ) { ?><li class="alignright"><?php echo $nl; ?></li><?php } ?>
		<?php if ( $pl != '' ) { ?><li class="alignleft"><?php echo $pl; ?></li><?php } ?>
	</ul>
	
	<?php } ?>
	
	<?php

}



// search?
elseif ( is_search() ) {

	$nl = get_next_posts_link('&laquo; '.$previous_title);
	$pl = get_previous_posts_link($next_title.' &raquo;');
	
	// did we get either?
	if ( $nl != '' OR $pl != '' ) { ?>
	
	<ul id="blog_navigation">
		<?php if ( $nl != '' ) { ?><li class="alignright"><?php echo $nl; ?></li><?php } ?>
		<?php if ( $pl != '' ) { ?><li class="alignleft"><?php echo $pl; ?></li><?php } ?>
	</ul>
	
	<?php } ?>
	
	<?php

}



?>

</div><!-- /cp_book_nav -->
<?php */ ?>


<div id="switcher">
	<ul>
		<li class="navigation-item"><a class="navigation-button" href="#navigation">Navigate</a></li>			
		<li class="content-item"><a class="content-button" href="#content">Content</a></li>			
		<li class="sidebar-item"><a class="sidebar-button" href="#sidebar">Discuss</a></li>
	</ul>
</div>



<?php /* closes in footer.php */ ?>
<div id="content_container" class="clearfix">



<?php

// until WordPress supports a locate_theme_file() function, use filter
$include = apply_filters( 
	'cp_template_toc_sidebar',
	get_template_directory() . '/assets/templates/toc_sidebar.php'
);

// always include TOC
include( $include );

?>