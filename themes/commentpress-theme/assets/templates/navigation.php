<?php

global $commentpress_core;

?><!-- navigation.php -->

<div id="book_nav">



<div id="book_nav_wrapper">


	
<div id="cp_book_nav">

<?php

// set default link names
$previous_title = apply_filters( 'cp_nav_previous_link_title', __( 'Older Entries', 'commentpress-theme' ) );
$next_title = apply_filters( 'cp_nav_next_link_title', __( 'Newer Entries', 'commentpress-theme' ) );

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

	?><div id="cp_book_info"><p><?php echo commentpress_page_title(); ?></p></div>
	<?php

}



// is it a post?
elseif ( is_single() ) {

	?><ul id="blog_navigation">
		<?php next_post_link('<li class="alignright">%link</li>'); ?>
		<?php previous_post_link('<li class="alignleft">%link</li>'); ?>
	</ul>
	
	<div id="cp_book_info"><p><?php echo commentpress_page_title(); ?></p></div>
	<?php

}


// is this the blog home?
elseif ( is_home() ) {

	$nl = get_next_posts_link('&laquo; '.$previous_title);
	$pl = get_previous_posts_link($next_title.' &raquo;');
	
	// did we get either?
	if ( $nl != '' OR $pl != '' ) { ?>
	
	<ul id="blog_navigation">
		<?php if ( $nl != '' ) { ?><li class="alignright"><?php echo $nl; ?></li><?php } ?>
		<?php if ( $pl != '' ) { ?><li class="alignleft"><?php echo $pl; ?></li><?php } ?>
	</ul>
	
	<?php } ?>
	
	<div id="cp_book_info"><p><?php echo __( 'Blog', 'commentpress-theme' ); ?></p></div>
	<?php

}



// archives?
elseif ( is_day() || is_month() || is_year() ) {

	$nl = get_next_posts_link('&laquo; '.$previous_title);
	$pl = get_previous_posts_link($next_title.' &raquo;');
	
	// did we get either?
	if ( $nl != '' OR $pl != '' ) { ?>
	
	<ul id="blog_navigation">
		<?php if ( $nl != '' ) { ?><li class="alignright"><?php echo $nl; ?></li><?php } ?>
		<?php if ( $pl != '' ) { ?><li class="alignleft"><?php echo $pl; ?></li><?php } ?>
	</ul>
	
	<?php } ?>
	
	<div id="cp_book_info"><p><?php echo __( 'Blog Archives: ', 'commentpress-theme' ); wp_title(''); ?></p></div>
	<?php

}



// search?
elseif ( is_search() ) {

	$nl = get_next_posts_link('&laquo; '.$previous_title);
	$pl = get_previous_posts_link($next_title.' &raquo;');
	
	// did we get either?
	if ( $nl != '' OR $pl != '' ) { ?>
	
	<ul id="blog_navigation">
		<?php if ( $nl != '' ) { ?><li class="alignright"><?php echo $nl; ?></li><?php } ?>
		<?php if ( $pl != '' ) { ?><li class="alignleft"><?php echo $pl; ?></li><?php } ?>
	</ul>
	
	<?php } ?>
	
	<div id="cp_book_info"><p><?php wp_title(''); ?></p></div>
	<?php

}



else {

	// catchall for other page types	
	?><div id="cp_book_info"><p><?php wp_title(''); ?></p></div>
	<?php

}




?>

</div><!-- /cp_book_nav -->



<ul id="nav">
	<?php
	
	// do we have the plugin?
	if ( is_object( $commentpress_core ) ) {
		
		// NOTE: we need to account for situations where no CommentPress Core special pages exist
		
		// get title id and url
		$title_id = $commentpress_core->db->option_get( 'cp_welcome_page' );
		$title_url = $commentpress_core->get_page_url( 'cp_welcome_page' );
		
		// use as link to main blog in multisite
		if ( is_multisite() ) {
			
			// set default link name
			$site_title = apply_filters( 'cp_nav_network_home_title', __( 'Site Home Page', 'commentpress-theme' ) );

			// show home
			?><li><a href="<?php echo network_home_url(); ?>" id="btn_home" class="css_btn" title="<?php echo $site_title; ?>"><?php echo $site_title; ?></a></li><?php
		
			// link to group in multisite groupblog
			if ( $commentpress_core->is_groupblog() ) {
				
				global $wpdb;
				$blog_id = (int)$wpdb->blogid;
			
				// check if this blog is a group blog...
				$group_id = get_groupblog_group_id( $blog_id );
				
				// when this blog is a groupblog
				if ( !empty( $group_id ) ) {

					$group = groups_get_group( array( 'group_id' => $group_id ) );
					$group_url = bp_get_group_permalink( $group );
					
					// set default link name
					$group_title = apply_filters( 'cp_nav_group_home_title', __( 'Group Home Page', 'commentpress-theme' ) );
		
					?><li><a href="<?php echo $group_url; ?>" id="btn_grouphome" class="css_btn" title="<?php echo $group_title; ?>"><?php echo $group_title; ?></a></li><?php
					
				}
				
			}
			
		} else {
			
			// use if blog home is not CP welcome page
			if ( $title_id != get_option('page_on_front') ) {
		
				// set default link name
				$home_title = apply_filters( 'cp_nav_blog_home_title', __( 'Home Page', 'commentpress-theme' ) );
	
				// show home
				?><li><a href="<?php echo home_url(); ?>" id="btn_home" class="css_btn" title="<?php echo $home_title; ?>"><?php echo $home_title; ?></a></li><?php
			
			}
			
		}
	
		// do we have a title page url?
		if ( !empty( $title_url ) ) {
	
			// set default link name
			$title_title = apply_filters( 'cp_nav_title_page_title', __( 'Title Page', 'commentpress-theme' ) );
	
			?><li><a href="<?php echo $title_url; ?>" id="btn_cover" class="css_btn" title="<?php echo $title_title; ?>"><?php echo $title_title; ?></a></li><?php
		
		}
	
		// show link to general comments page if we have one
		echo $commentpress_core->get_page_link( 'cp_general_comments_page' );
		
		// show link to all comments page if we have one
		echo $commentpress_core->get_page_link( 'cp_all_comments_page' );
		
		// show link to comments-by-user page if we have one
		echo $commentpress_core->get_page_link( 'cp_comments_by_page' );
		
		// show link to book blog page if we have one
		echo $commentpress_core->get_page_link( 'cp_blog_page' );
		
		// show link to book blog archive page if we have one
		echo $commentpress_core->get_page_link( 'cp_blog_archive_page' );
		
	}
		
	?>
</ul>



<ul id="minimiser_trigger">
	<?php
	
	// do we have the plugin?
	if ( is_object( $commentpress_core ) ) {
	
		// show minimise header button
		echo $commentpress_core->get_header_min_link();
		
	}
	
	?>
</ul>



</div><!-- /book_nav_wrapper -->



</div><!-- /book_nav -->
	
	
	
