<?php

global $commentpress_core;

?><!-- page_navigation.php -->



<div class="page_navigation">

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

	?><ul class="blog_navigation">
		<?php next_post_link('<li class="alignright">%link</li>'); ?>
		<?php previous_post_link('<li class="alignleft">%link</li>'); ?>
	</ul>
	
	<?php

}



// is this the blog home?
elseif ( is_home() ) {

	$nl = get_next_posts_link($previous_title);
	$pl = get_previous_posts_link($next_title);
	
	// did we get either?
	if ( $nl != '' OR $pl != '' ) { ?>
	
	<ul class="blog_navigation">
		<?php if ( $pl != '' ) { ?><li class="alignright"><?php echo $pl; ?></li><?php } ?>
		<?php if ( $nl != '' ) { ?><li class="alignleft"><?php echo $nl; ?></li><?php } ?>
	</ul>
	
	<?php } ?>
	
	<?php

}



// archives?
elseif ( is_day() || is_month() || is_year() ) {

	$nl = get_next_posts_link( $previous_title );
	$pl = get_previous_posts_link( $next_title );
	
	// did we get either?
	if ( $nl != '' OR $pl != '' ) { ?>
	
	<ul class="blog_navigation">
		<?php if ( $pl != '' ) { ?><li class="alignright"><?php echo $pl; ?></li><?php } ?>
		<?php if ( $nl != '' ) { ?><li class="alignleft"><?php echo $nl; ?></li><?php } ?>
	</ul>
	
	<?php } ?>
	
	<?php

}



// search?
elseif ( is_search() ) {

	$nl = get_next_posts_link( __( 'More Results', 'commentpress-core' ) );
	$pl = get_previous_posts_link( __( 'Previous Results', 'commentpress-core' ) );
	
	// did we get either?
	if ( $nl != '' OR $pl != '' ) { ?>
	
	<ul class="blog_navigation">
		<?php if ( $nl != '' ) { ?><li class="alignright"><?php echo $nl; ?></li><?php } ?>
		<?php if ( $pl != '' ) { ?><li class="alignleft"><?php echo $pl; ?></li><?php } ?>
	</ul>
	
	<?php } ?>
	
	<?php

}



// category archives, including qmt
elseif ( is_category() ) {

	$nl = get_next_posts_link( __( 'More Results', 'commentpress-core' ) );
	$pl = get_previous_posts_link( __( 'Previous Results', 'commentpress-core' ) );
	
	// did we get either?
	if ( $nl != '' OR $pl != '' ) { ?>
	
	<ul class="blog_navigation">
		<?php if ( $nl != '' ) { ?><li class="alignright"><?php echo $nl; ?></li><?php } ?>
		<?php if ( $pl != '' ) { ?><li class="alignleft"><?php echo $pl; ?></li><?php } ?>
	</ul>
	
	<?php } ?>
	
	<?php

}



// tag archives
elseif ( is_tag() ) {

	$nl = get_next_posts_link( __( 'More Results', 'commentpress-core' ) );
	$pl = get_previous_posts_link( __( 'Previous Results', 'commentpress-core' ) );
	
	// did we get either?
	if ( $nl != '' OR $pl != '' ) { ?>
	
	<ul class="blog_navigation">
		<?php if ( $nl != '' ) { ?><li class="alignright"><?php echo $nl; ?></li><?php } ?>
		<?php if ( $pl != '' ) { ?><li class="alignleft"><?php echo $pl; ?></li><?php } ?>
	</ul>
	
	<?php } ?>
	
	<?php

}



?>

</div><!-- /page_navigation -->



