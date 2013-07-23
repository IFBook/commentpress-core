<!-- toc_dropdown.php -->

<div id="toc_dropdown">



<div id="toc_dd_header">

<h2><?php _e( 'Table of Contents', 'commentpress-core' ); ?></h2>

</div>



<div id="toc_dd_wrapper">

<?php 

// if we have the plugin enabled...
if ( is_object( $commentpress_core ) ) {

	?><ul id="toc_dd_list">
	<?php

	// show the TOC
	echo $commentpress_core->get_toc_list();

	?></ul>
	<?php

}

?>
	
</div><!-- /toc_dd_wrapper -->



</div><!-- /toc_dropdown -->



