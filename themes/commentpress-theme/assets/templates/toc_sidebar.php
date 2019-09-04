<!-- toc_sidebar.php -->

<div id="toc_sidebar" class="sidebar_container">



<div class="sidebar_header">

<h2><?php _e( 'Table of Contents', 'commentpress-core' ); ?></h2>

</div>



<div class="sidebar_minimiser">

<div class="sidebar_contents_wrapper">

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

</div><!-- /sidebar_contents_wrapper -->

</div><!-- /sidebar_minimiser -->



</div><!-- /toc_sidebar -->



