<?php

// declare access to globals
global $commentpress_core;



// init tab order (only relevant for old default theme)
$_tab_order = array( 'comments', 'activity' );
//print_r( $_tab_order ); die();



	
// init commentable as true by default
$is_commentable = true;

// if we have the plugin enabled...
if ( is_object( $commentpress_core ) ) {
	
	// override
	$is_commentable = ( $commentpress_core->is_commentable() ) ? true : false;
	
}




?><!-- sidebar.php -->

<div id="sidebar">

<div id="sidebar_inner">



<ul id="sidebar_tabs">

<?php

// -----------------------------------------------------------------------------
// SIDEBAR HEADERS
// -----------------------------------------------------------------------------


foreach( $_tab_order AS $_tab ) {

	switch ( $_tab ) {
		
		// Comments Header
		case 'comments':



// add class if commentable?
$active_class = '';
if ( $is_commentable ) {
	$active_class = ' class="active-tab"';
}

?><li id="comments_header" class="sidebar_header">
<h2><a href="#comments_sidebar"<?php echo $active_class; ?>><?php 

// set default link name
$_comments_title = apply_filters(

	// filter name
	'cp_tab_title_comments', 
	
	// default value
	__( 'Comments', 'commentpress-core' )
	
);

echo $_comments_title; 

?></a></h2>
<?php

// init
$_min = '';

// if we have the plugin enabled...
if ( is_object( $commentpress_core ) ) {

	// show the minimise all button
	$_min = $commentpress_core->get_minimise_all_button( 'comments' );

}

// show the minimise all button
echo $_min;

?>
</li>

<?php 

break;



		// Activity Header
		case 'activity':

// do we want to show activity tab?
if ( commentpress_show_activity_tab() ) {
	
	// add class if commentable?
	$active_class = '';
	if ( !$is_commentable ) {
		$active_class = ' class="active-tab"';
	}
	
	// set default link name
	$_activity_title = apply_filters(
	
		// filter name
		'cp_tab_title_activity', 
		
		// default value
		__( 'Activity', 'commentpress-core' )
		
	);
	
	?>
	<li id="activity_header" class="sidebar_header">
	<h2><a href="#activity_sidebar"<?php echo $active_class; ?>><?php echo $_activity_title; ?></a></h2>
	<?php
	
	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {
	
		// show the minimise all button
		echo $commentpress_core->get_minimise_all_button( 'activity' );
	
	}
	
	?>
	</li>
	<?php

} else {

	// ignore activity
		
}

break;



	} // end switch
	
} // end foreach


?>

</ul>



<?php




// -----------------------------------------------------------------------------
// THE SIDEBARS THEMSELVES
// -----------------------------------------------------------------------------

// plugin global
global $commentpress_core, $post;

// if we have the plugin enabled...
if ( is_object( $commentpress_core ) ) {



	// check commentable status
	$commentable = $commentpress_core->is_commentable();

	// is it commentable?
	if ( $commentable ) {
	
		// until WordPress supports a locate_theme_file() function, use filter
		$include = apply_filters( 
			'cp_template_comments_sidebar',
			get_template_directory() . '/assets/templates/comments_sidebar.php'
		);
		
		// get comments sidebar
		include( $include );
		
	}
	
	// do we want to show activity tab?
	if ( commentpress_show_activity_tab() ) {
		
		// until WordPress supports a locate_theme_file() function, use filter
		$include = apply_filters( 
			'cp_template_activity_sidebar',
			get_template_directory() . '/assets/templates/activity_sidebar.php'
		);
		
		// get activity sidebar
		include( $include );
		
	}
	


} else {





// default sidebar when plugin not active...
?><div id="toc_sidebar">



<div class="sidebar_header">

<h2><?php echo $_toc_title; ?></h2>

</div>



<div class="sidebar_minimiser">

<div class="sidebar_contents_wrapper">

<ul>
	<?php wp_list_pages('sort_column=menu_order&title_li='); ?>
</ul>

</div><!-- /sidebar_contents_wrapper -->

</div><!-- /sidebar_minimiser -->



</div><!-- /toc_sidebar -->



<?php 

} // end check for plugin

?>



</div><!-- /sidebar_inner -->

</div><!-- /sidebar -->



