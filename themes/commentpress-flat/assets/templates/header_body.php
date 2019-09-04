<?php /*
================================================================================
HTML Body Header
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

Separated this out for inclusion in multiple files.

--------------------------------------------------------------------------------
*/



// Start HTML.
?>
<a class="skip" href="#content"><?php _e( 'Skip to Content', 'commentpress-core' ); ?></a>
<span class="off-left"> | </span>
<a class="skip" href="#toc_list"><?php _e( 'Skip to Table of Contents', 'commentpress-core' ); ?></a><!-- /skip_links -->



<div id="header" class="clearfix">

	<?php do_action( 'commentpress_header_before' ); ?>

	<?php commentpress_get_header_image(); ?>

	<div id="switcher">
		<ul>
			<li class="navigation-item"><a class="navigation-button" href="#navigation"><?php _e( 'Navigate', 'commentpress-core' ); ?></a></li>
			<li class="sidebar-item comments-item"><a class="comments-button" href="#comments_sidebar"><?php _e( 'Comments', 'commentpress-core' ); ?></a></li>
			<li class="sidebar-item activity-item"><a class="activity-button" href="#activity_sidebar"><?php _e( 'Activity', 'commentpress-core' ); ?></a></li>
		</ul>
	</div>

	<div id="page_title">
		<div id="title"><h1><a href="<?php echo home_url(); ?>" title="<?php _e( 'Home', 'commentpress-core' ); ?>"><?php bloginfo('title'); ?></a></h1></div>
		<div id="tagline"><?php bloginfo('description'); ?></div>
	</div>

	<?php do_action( 'commentpress_header_after' ); ?>

</div><!-- /header -->



<?php /* closes in footer.php */ ?>
<div id="content_container" class="clearfix">



<?php

/**
 * Try to locate template using WP method.
 *
 * @since 3.4
 *
 * @param str The existing path returned by WordPress.
 * @return str The modified path.
 */
$cp_toc_sidebar = apply_filters(
	'cp_template_toc_sidebar',
	locate_template( 'assets/templates/toc_sidebar.php' )
);

// Load it if we find it.
if ( $cp_toc_sidebar != '' ) load_template( $cp_toc_sidebar );

?>
