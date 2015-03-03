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



<div id="book_header">

	<div id="titlewrap">
		<?php

		// get header image
		commentpress_get_header_image();

		?>
		<div id="page_title">
		<div id="title"><h1><a href="<?php echo home_url(); ?>" title="<?php _e( 'Home', 'commentpress-core' ); ?>"><?php bloginfo('title'); ?></a></h1></div>
		<div id="tagline"><?php bloginfo('description'); ?></div>
		</div>
	</div>

	<div id="book_search">
		<?php get_search_form(); ?>
	</div><!-- /book_search -->

	<?php

	// first try to locate using WP method
	$cp_user_links = apply_filters(
		'cp_template_user_links',
		locate_template( 'assets/templates/user_links.php' )
	);

	// load it if we find it
	if ( $cp_user_links != '' ) load_template( $cp_user_links );

	?>

</div><!-- /book_header -->



<div id="header">

	<?php

	// first try to locate using WP method
	$cp_navigation = apply_filters(
		'cp_template_navigation',
		locate_template( 'assets/templates/navigation.php' )
	);

	// load it if we find it
	if ( $cp_navigation != '' ) load_template( $cp_navigation );

	?>

</div><!-- /header -->



