<!-- toc_sidebar.php -->

<div id="navigation">

<div id="toc_sidebar" class="sidebar_container">



<?php do_action( 'cp_content_tab_before' ); ?>



<div class="sidebar_header">

<h2><?php _e( 'Contents', 'commentpress-core' ); ?></h2>

</div>



<div class="sidebar_minimiser">

<div class="sidebar_contents_wrapper">



<?php

// Allow widgets to be placed above navigation.
dynamic_sidebar( 'cp-nav-top' );

?>

<?php do_action( 'cp_content_tab_before_search' ); ?>



<h3 class="activity_heading search_heading"><?php
echo apply_filters( 'cp_content_tab_search_title', __( 'Search', 'commentpress-core' ) );
?></h3>

<div class="paragraph_wrapper search_wrapper">

<div id="document_search">
	<?php get_search_form(); ?>
</div><!-- /document_search -->

</div>



<?php if ( apply_filters( 'cp_content_tab_special_pages_visible', true ) ) : ?>
	<h3 class="activity_heading special_pages_heading"><?php
	echo apply_filters( 'cp_content_tab_special_pages_title', __( 'Special Pages', 'commentpress-core' ) );
	?></h3>

	<div class="paragraph_wrapper special_pages_wrapper">

	<?php

	/**
	 * Try to locate template using WP method.
	 *
	 * @since 3.4
	 *
	 * @param str The existing path returned by WordPress.
	 * @return str The modified path.
	 */
	$cp_navigation = apply_filters(
		'cp_template_navigation',
		locate_template( 'assets/templates/navigation.php' )
	);

	// Load it if we find it.
	if ( $cp_navigation != '' ) load_template( $cp_navigation );

	?>

	</div>
<?php endif; ?>



<h3 class="activity_heading toc_heading"><?php
echo apply_filters( 'cp_content_tab_toc_title', __( 'Table of Contents', 'commentpress-core' ) );
?></h3>

<div class="paragraph_wrapper start_open">

<?php

// Declare access to globals.
global $commentpress_core;

// If we have the plugin enabled.
if ( is_object( $commentpress_core ) ) {

	?><ul id="toc_list">
	<?php

	// Show the TOC.
	echo $commentpress_core->get_toc_list();

	?></ul>
	<?php

}

?>

</div>



<?php

// Allow widgets to be placed below navigation.
dynamic_sidebar( 'cp-nav-bottom' );

?>



<?php do_action( 'cp_content_tab_after' ); ?>



</div><!-- /sidebar_contents_wrapper -->

</div><!-- /sidebar_minimiser -->



</div><!-- /toc_sidebar -->

</div><!-- /navigation -->


