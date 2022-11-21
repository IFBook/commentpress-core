<?php

// Get core plugin reference.
$core = commentpress_core();

?><!-- navigation.php -->

<div id="book_nav">



<div id="book_nav_wrapper">



<div id="cp_book_nav">

<?php

// Set default link names.
$previous_title = apply_filters( 'cp_nav_previous_link_title', __( 'Older Entries', 'commentpress-core' ) );
$next_title = apply_filters( 'cp_nav_next_link_title', __( 'Newer Entries', 'commentpress-core' ) );

// Is it a Page?
if ( is_page() ) {

	// Get our custom Page Navigation.
	$cp_page_nav = apply_filters( 'cp_template_page_navigation', commentpress_page_navigation() );

	// If we get any.
	if ( $cp_page_nav != '' ) {

		?><ul>
			<?php echo $cp_page_nav; ?>
		</ul>
		<?php

	}

	?><div id="cp_book_info"><p><?php echo commentpress_page_title(); ?></p></div>
	<?php

}



// Is it a Post?
elseif ( is_single() ) {

	?><ul id="blog_navigation">
		<?php next_post_link( '<li class="alignright">%link</li>' ); ?>
		<?php previous_post_link( '<li class="alignleft">%link</li>' ); ?>
	</ul>

	<div id="cp_book_info"><p><?php echo commentpress_page_title(); ?></p></div>
	<?php

}



// Is this the Posts archive or a CPT archive?
elseif ( is_home() OR is_post_type_archive() ) {

	$nl = get_next_posts_link( '&laquo; ' . $previous_title );
	$pl = get_previous_posts_link( $next_title . ' &raquo;' );

	// Did we get either?
	if ( $nl != '' OR $pl != '' ) { ?>

	<ul id="blog_navigation">
		<?php if ( $nl != '' ) { ?><li class="alignright"><?php echo $nl; ?></li><?php } ?>
		<?php if ( $pl != '' ) { ?><li class="alignleft"><?php echo $pl; ?></li><?php } ?>
	</ul>

	<?php } ?>

	<div id="cp_book_info"><p><?php echo __( 'Blog', 'commentpress-core' ); ?></p></div>
	<?php

}



// Archives?
elseif ( is_day() || is_month() || is_year() ) {

	$nl = get_next_posts_link( '&laquo; ' . $previous_title );
	$pl = get_previous_posts_link( $next_title . ' &raquo;' );

	// Did we get either?
	if ( $nl != '' OR $pl != '' ) { ?>

	<ul id="blog_navigation">
		<?php if ( $nl != '' ) { ?><li class="alignright"><?php echo $nl; ?></li><?php } ?>
		<?php if ( $pl != '' ) { ?><li class="alignleft"><?php echo $pl; ?></li><?php } ?>
	</ul>

	<?php } ?>

	<div id="cp_book_info"><p><?php echo __( 'Blog Archives: ', 'commentpress-core' ); wp_title(''); ?></p></div>
	<?php

}



// Search?
elseif ( is_search() ) {

	$nl = get_next_posts_link( '&laquo; ' .  __( 'More Results', 'commentpress-core' ) );
	$pl = get_previous_posts_link( __( 'Previous Results', 'commentpress-core' ) . ' &raquo;' );

	// Did we get either?
	if ( $nl != '' OR $pl != '' ) { ?>

	<ul id="blog_navigation">
		<?php if ( $nl != '' ) { ?><li class="alignright"><?php echo $nl; ?></li><?php } ?>
		<?php if ( $pl != '' ) { ?><li class="alignleft"><?php echo $pl; ?></li><?php } ?>
	</ul>

	<?php } ?>

	<div id="cp_book_info"><p><?php wp_title(''); ?></p></div>
	<?php

}



// Category, tag & custom taxonomy archives, including qmt.
elseif ( is_category() OR is_tag() OR is_tax() ) {

	$nl = get_next_posts_link( '&laquo; ' .  __( 'More Results', 'commentpress-core' ) );
	$pl = get_previous_posts_link( __( 'Previous Results', 'commentpress-core' ) . ' &raquo;' );

	// Did we get either?
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

	// Catch-all for other Page Types.
	?><div id="cp_book_info"><p><?php wp_title( '' ); ?></p></div>
	<?php

}




?>

</div><!-- /cp_book_nav -->



<ul id="nav">
	<?php

	// Do we have the plugin?
	if ( ! empty( $core ) ) {

		// NOTE: we need to account for situations where no CommentPress Core Special Pages exist.

		// Get title ID and URL.
		$title_id = $core->db->option_get( 'cp_welcome_page' );
		$title_url = $core->get_page_url( 'cp_welcome_page' );

		// Use as link to main Blog in multisite.
		if ( is_multisite() ) {

			// Set default link name.
			$site_title = apply_filters( 'cp_nav_network_home_title', __( 'Site Home Page', 'commentpress-core' ) );

			// Show home.
			?><li><a href="<?php echo network_home_url(); ?>" id="btn_home" class="css_btn" title="<?php echo $site_title; ?>"><?php echo $site_title; ?></a></li><?php

			// Allow plugins to inject links.
			//do_action( 'cp_nav_after_network_home_title' );

			// Link to Group in multisite Group Blog.
			if ( $core->bp->is_groupblog() ) {

				// Get current Blog ID.
				$blog_id = get_current_blog_id();

				// Check if this Blog is a Group Blog.
				$group_id = get_groupblog_group_id( $blog_id );

				// When this Blog is a Group Blog.
				if ( isset( $group_id ) AND is_numeric( $group_id ) AND $group_id > 0 ) {

					$group = groups_get_group( [ 'group_id' => $group_id ] );
					$group_url = bp_get_group_permalink( $group );

					// Set default link name.
					$group_title = apply_filters( 'cp_nav_group_home_title', __( 'Group Home Page', 'commentpress-core' ) );

					?><li><a href="<?php echo $group_url; ?>" id="btn_grouphome" class="css_btn" title="<?php echo $group_title; ?>"><?php echo $group_title; ?></a></li><?php

				}

			}

		} else {

			// Use if Blog home is not CommentPress Core Welcome Page.
			if ( $title_id != get_option('page_on_front') ) {

				// Set default link name.
				$home_title = apply_filters( 'cp_nav_blog_home_title', __( 'Home Page', 'commentpress-core' ) );

				// Show home.
				?><li><a href="<?php echo home_url(); ?>" id="btn_home" class="css_btn" title="<?php echo $home_title; ?>"><?php echo $home_title; ?></a></li><?php

			}

		}

		// Do we have a Title Page URL?
		if ( !empty( $title_url ) ) {

			// Set default link name.
			$title_title = apply_filters( 'cp_nav_title_page_title', __( 'Title Page', 'commentpress-core' ) );

			?><li><a href="<?php echo $title_url; ?>" id="btn_cover" class="css_btn" title="<?php echo $title_title; ?>"><?php echo $title_title; ?></a></li><?php

		}

		// Show link to General Comments Page if we have one.
		echo $core->get_page_link( 'cp_general_comments_page' );

		// Show link to All Comments Page if we have one.
		echo $core->get_page_link( 'cp_all_comments_page' );

		// Show link to Comments-by-User Page if we have one.
		echo $core->get_page_link( 'cp_comments_by_page' );

		// Show link to book Blog Page if we have one.
		echo $core->get_page_link( 'cp_blog_page' );

		// Show link to book Blog Archive Page if we have one.
		echo $core->get_page_link( 'cp_blog_archive_page' );

	}

	?>
</ul>



<ul id="minimiser_trigger">
	<?php

	// Do we have the plugin?
	if ( ! empty( $core ) ) {

		// Show minimise header button.
		echo $core->get_header_min_link();

	}

	?>
</ul>



</div><!-- /book_nav_wrapper -->



</div><!-- /book_nav -->



