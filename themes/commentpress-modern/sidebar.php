<?php

// Declare access to globals.
global $commentpress_core;



// Init tab order (only relevant for old default theme)
$_tab_order = array( 'comments', 'activity' );



// Init commentable as true by default.
$is_commentable = true;

// If we have the plugin enabled.
if ( is_object( $commentpress_core ) ) {

	// Override.
	$is_commentable = ( $commentpress_core->is_commentable() ) ? true : false;

}



?><!-- sidebar.php -->

<div id="sidebar">

<div id="sidebar_inner">



<ul id="sidebar_tabs">

<?php

// -----------------------------------------------------------------------------
// SIDEBAR HEADERS.
// -----------------------------------------------------------------------------


foreach( $_tab_order AS $_tab ) {

	switch ( $_tab ) {

		// Comments Header.
		case 'comments':



// Add active class.
$active_class = '';
if ( in_array( $commentpress_core->get_default_sidebar(), array( 'comments', 'toc' ) ) ) {
	$active_class = ' class="active-tab"';
}

?><li id="comments_header" class="sidebar_header">
<h2><a href="#comments_sidebar"<?php echo $active_class; ?>><?php

// Set default link name.
$_comments_title = apply_filters(

	// Filter name.
	'cp_tab_title_comments',

	// Default value.
	__( 'Comments', 'commentpress-core' )

);

echo $_comments_title;

?></a></h2>
<?php

// Init.
$_min = '';

// If we have the plugin enabled.
if ( is_object( $commentpress_core ) ) {

	// Show the minimise all button.
	$_min = $commentpress_core->get_minimise_all_button( 'comments' );

}

// Show the minimise all button.
echo $_min;

?>
</li>

<?php

break;



		// Activity Header.
		case 'activity':

// Do we want to show activity tab?
if ( commentpress_show_activity_tab() ) {

	// Add class if not commentable.
	$active_class = '';
	if ( ! $is_commentable OR 'activity' == $commentpress_core->get_default_sidebar() ) {
		$active_class = ' class="active-tab"';
	}

	// Set default link name.
	$_activity_title = apply_filters(

		// Filter name.
		'cp_tab_title_activity',

		// Default value.
		__( 'Activity', 'commentpress-core' )

	);

	?>
	<li id="activity_header" class="sidebar_header">
	<h2><a href="#activity_sidebar"<?php echo $active_class; ?>><?php echo $_activity_title; ?></a></h2>
	<?php

	// If we have the plugin enabled.
	if ( is_object( $commentpress_core ) ) {

		// Show the minimise all button.
		echo $commentpress_core->get_minimise_all_button( 'activity' );

	}

	?>
	</li>
	<?php

} else {

	// Ignore activity.

}

break;



	} // End switch.

} // End foreach.


?>

</ul>



<?php




// -----------------------------------------------------------------------------
// THE SIDEBARS THEMSELVES.
// -----------------------------------------------------------------------------

// Access globals.
global $commentpress_core, $post;

// If we have the plugin enabled.
if ( is_object( $commentpress_core ) ) {



	// Check commentable status.
	$commentable = $commentpress_core->is_commentable();

	// Is it commentable?
	if ( $commentable ) {

		/**
		 * Try to locate template using WP method.
		 *
		 * @since 3.4
		 *
		 * @param str The existing path returned by WordPress.
		 * @return str The modified path.
		 */
		$cp_comments_sidebar = apply_filters(
			'cp_template_comments_sidebar',
			locate_template( 'assets/templates/comments_sidebar.php' )
		);

		// Load it if we find it.
		if ( $cp_comments_sidebar != '' ) load_template( $cp_comments_sidebar );

	}

	// Do we want to show activity tab?
	if ( commentpress_show_activity_tab() ) {

		/**
		 * Try to locate template using WP method.
		 *
		 * @since 3.4
		 *
		 * @param str The existing path returned by WordPress.
		 * @return str The modified path.
		 */
		$cp_activity_sidebar = apply_filters(
			'cp_template_activity_sidebar',
			locate_template( 'assets/templates/activity_sidebar.php' )
		);

		// Load it if we find it.
		if ( $cp_activity_sidebar != '' ) load_template( $cp_activity_sidebar );

	}



} else {





// Default sidebar when plugin not active.
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

} // End check for plugin

?>



</div><!-- /sidebar_inner -->

</div><!-- /sidebar -->



