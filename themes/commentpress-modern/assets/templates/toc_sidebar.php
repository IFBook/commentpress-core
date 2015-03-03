<!-- toc_sidebar.php -->

<div id="navigation">

<div id="toc_sidebar" class="sidebar_container">



<?php do_action( 'cp_content_tab_before' ); ?>



<div class="sidebar_header">

<h2><?php _e( 'Contents', 'commentpress-core' ); ?></h2>

</div>



<div class="sidebar_minimiser">

<div class="sidebar_contents_wrapper">



<?php do_action( 'cp_content_tab_before_search' ); ?>



<h3 class="activity_heading"><?php
echo apply_filters( 'cp_content_tab_search_title', __( 'Search', 'commentpress-core' ) );
?></h3>

<div class="paragraph_wrapper search_wrapper">

<div id="document_search">
	<?php get_search_form(); ?>
</div><!-- /book_search -->

</div>



<h3 class="activity_heading"><?php
echo apply_filters( 'cp_content_tab_special_pages_title', __( 'Special Pages', 'commentpress-core' ) );
?></h3>

<div class="paragraph_wrapper special_pages_wrapper">

<?php

// first try to locate using WP method
$cp_navigation = apply_filters(
	'cp_template_navigation',
	locate_template( 'assets/templates/navigation.php' )
);

// load it if we find it
if ( $cp_navigation != '' ) load_template( $cp_navigation );

?>

</div>


<h3 class="activity_heading"><?php
echo apply_filters( 'cp_content_tab_toc_title', __( 'Table of Contents', 'commentpress-core' ) );
?></h3>

<div class="paragraph_wrapper start_open">

<?php

// declare access to globals
global $commentpress_core;

// if we have the plugin enabled...
if ( is_object( $commentpress_core ) ) {

	?><ul id="toc_list">
	<?php

	// show the TOC
	echo $commentpress_core->get_toc_list();

	?></ul>
	<?php

}

?>

</div>



<?php do_action( 'cp_content_tab_after' ); ?>



</div><!-- /sidebar_contents_wrapper -->

</div><!-- /sidebar_minimiser -->



</div><!-- /toc_sidebar -->

</div><!-- /navigation -->


