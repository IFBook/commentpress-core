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
<a class="skip" href="#content"><?php _e( 'Skip to Content', 'commentpress-core' ); ?></a>
<span class="off-left"> | </span>
<a class="skip" href="#toc_list"><?php _e( 'Skip to Table of Contents', 'commentpress-core' ); ?></a><!-- /skip_links -->



<div id="header">
	
	<?php
	
	// get header image
	commentpress_get_header_image();
	
	?>
	<div id="page_title">
		<div id="title"><h1><a href="<?php echo home_url(); ?>" title="<?php _e( 'Home', 'commentpress-core' ); ?>"><?php bloginfo('title'); ?></a></h1></div>
		<div id="tagline"><?php bloginfo('description'); ?></div>
	</div>
	


</div><!-- /header -->



<div id="switcher">
	<ul>
		<li class="navigation-item"><a class="navigation-button" href="#navigation"><?php _e( 'Navigate', 'commentpress-core' ); ?></a></li>			
		<li class="content-item"><a class="content-button" href="#content"><?php _e( 'Content', 'commentpress-core' ); ?></a></li>			
		<li class="sidebar-item"><a class="sidebar-button" href="#sidebar"><?php _e( 'Discuss', 'commentpress-core' ); ?></a></li>
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