<?php

global $commentpress_core;

?><!-- navigation.php -->

<div id="document_nav">



<div id="document_nav_wrapper">



<ul id="nav">
	<?php

	// Do we have the plugin?
	if ( is_object( $commentpress_core ) ) {

		// NOTE: we need to account for situations where no CommentPress Core special pages exist.

		// Get title ID and URL.
		$title_id = $commentpress_core->db->option_get( 'cp_welcome_page' );
		$title_url = $commentpress_core->get_page_url( 'cp_welcome_page' );

		// Use as link to main blog in multisite.
		if ( is_multisite() ) {

			// Set default link name.
			$site_title = apply_filters( 'cp_nav_network_home_title', __( 'Site Home Page', 'commentpress-core' ) );

			// Show home.
			?><li><a href="<?php echo network_home_url(); ?>" id="btn_home" class="css_btn" title="<?php echo $site_title; ?>"><?php echo $site_title; ?></a></li><?php

			// Allow plugins to inject links.
			do_action( 'cp_nav_after_network_home_title' );

			// Link to group in multisite groupblog.
			if ( $commentpress_core->is_groupblog() ) {

				// Get current blog ID.
				$blog_id = get_current_blog_id();

				// Check if this blog is a group blog.
				$group_id = get_groupblog_group_id( $blog_id );

				// When this blog is a groupblog.
				if ( isset( $group_id ) AND is_numeric( $group_id ) AND $group_id > 0 ) {

					$group = groups_get_group( [ 'group_id' => $group_id ] );
					$group_url = bp_get_group_permalink( $group );

					// Set default link name.
					$group_title = apply_filters( 'cp_nav_group_home_title', __( 'Group Home Page', 'commentpress-core' ) );

					?><li><a href="<?php echo $group_url; ?>" id="btn_grouphome" class="css_btn" title="<?php echo $group_title; ?>"><?php echo $group_title; ?></a></li><?php

				}

			}

		} else {

			// Use if blog home is not CommentPress Core welcome page.
			if ( $title_id != get_option('page_on_front') ) {

				// Set default link name.
				$home_title = apply_filters( 'cp_nav_blog_home_title', __( 'Home Page', 'commentpress-core' ) );

				// Show home.
				?><li><a href="<?php echo home_url(); ?>" id="btn_home" class="css_btn" title="<?php echo $home_title; ?>"><?php echo $home_title; ?></a></li><?php

			}

		}

		// Do we have a title page URL?
		if ( !empty( $title_url ) ) {

			// Set default link name.
			$title_title = apply_filters( 'cp_nav_title_page_title', __( 'Title Page', 'commentpress-core' ) );

			?><li><a href="<?php echo $title_url; ?>" id="btn_cover" class="css_btn" title="<?php echo $title_title; ?>"><?php echo $title_title; ?></a></li><?php

		}

		// Allow plugins to inject links.
		do_action( 'cp_nav_before_special_pages' );

		// Show link to general comments page if we have one.
		echo $commentpress_core->get_page_link( 'cp_general_comments_page' );

		// Show link to all comments page if we have one.
		echo $commentpress_core->get_page_link( 'cp_all_comments_page' );

		// Show link to comments-by-user page if we have one.
		echo $commentpress_core->get_page_link( 'cp_comments_by_page' );

		// Show link to document blog page if we have one
		echo $commentpress_core->get_page_link( 'cp_blog_page' );

		// Show link to document blog archive page if we have one
		echo $commentpress_core->get_page_link( 'cp_blog_archive_page' );

	}



	// Is this multisite?
	if ( is_multisite() ) {

		// Can users register?
		if ( get_option( 'users_can_register' ) ) {

			// This works for get_site_option( 'registration' ) == 'none' and 'user'
			?><li><?php wp_register(' ' , ' '); ?></li>
			<?php

		}

		// Multisite signup and blog create.
		if (
			( is_user_logged_in() AND get_site_option( 'registration' ) == 'blog' ) OR
			get_site_option( 'registration' ) == 'all'
		) {

			// Test whether we have BuddyPress Site Tracking active.
			if ( function_exists( 'bp_get_blogs_root_slug' ) ) {

				// Different behaviour when logged in or not.
				if ( is_user_logged_in() ) {

					// Set default link name.
					$new_site_title = apply_filters(
						'cp_user_links_new_site_title',
						__( 'Create a new document', 'commentpress-core' )
					);

					// BuddyPress uses its own signup page.
					$item = '<li><a href="' . bp_get_root_domain() . '/' . bp_get_blogs_root_slug() . '/create/" title="' . $new_site_title . '" id="btn_create">' . $new_site_title . '</a></li>';

				} else {

					// Not directly allowed - done through signup form.
					$item = '';

				}

			} else {

				// Set default link name.
				$new_site_title = apply_filters(
					'cp_user_links_new_site_title',
					__( 'Create a new document', 'commentpress-core' )
				);

				// Standard WordPress multisite.
				$item = '<li><a href="' . network_site_url() . 'wp-signup.php" title="' . $new_site_title . '" id="btn_create">' . $new_site_title . '</a></li>';

			}

			// Show it, but allow plugins to override
			echo apply_filters( 'cp_user_links_new_site_link', $item );

		}

	} else {

		// If logged in.
		if ( is_user_logged_in() ) {

			// Set default link name.
			$dashboard_title = apply_filters(
				'cp_user_links_dashboard_title',
				__( 'Dashboard', 'commentpress-core' )
			);

			?>
			<li><a href="<?php echo admin_url(); ?>" title="<?php echo $dashboard_title; ?>" id="btn_dash"><?php echo $dashboard_title; ?></a></li>
			<?php

		}

		/*
		// Testing JS.
		?>
		<li><a href="#" title="Javascript" id="btn_js">Javascript</a></li>
		<?php
		*/


	}


	// Login/logout.
	?><li><?php wp_loginout(); ?></li>
	<?php



	?>
</ul>



</div><!-- /document_nav_wrapper -->



</div><!-- /document_nav -->



