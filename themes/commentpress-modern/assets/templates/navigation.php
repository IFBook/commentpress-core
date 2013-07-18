<?php

global $commentpress_core;

?><!-- navigation.php -->

<div id="document_nav">



<div id="document_nav_wrapper">


	
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
			$site_title = apply_filters( 'cp_nav_network_home_title', __( 'Site Home Page', 'commentpress-core' ) );

			// show home
			?><li><a href="<?php echo network_home_url(); ?>" id="btn_home" class="css_btn" title="<?php echo $site_title; ?>"><?php echo $site_title; ?></a></li><?php
		
			// allow plugins to inject links
			do_action( 'cp_nav_after_network_home_title' );
			
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
					$group_title = apply_filters( 'cp_nav_group_home_title', __( 'Group Home Page', 'commentpress-core' ) );
		
					?><li><a href="<?php echo $group_url; ?>" id="btn_grouphome" class="css_btn" title="<?php echo $group_title; ?>"><?php echo $group_title; ?></a></li><?php
					
				}
				
			}
			
		} else {
			
			// use if blog home is not CP welcome page
			if ( $title_id != get_option('page_on_front') ) {
		
				// set default link name
				$home_title = apply_filters( 'cp_nav_blog_home_title', __( 'Home Page', 'commentpress-core' ) );
	
				// show home
				?><li><a href="<?php echo home_url(); ?>" id="btn_home" class="css_btn" title="<?php echo $home_title; ?>"><?php echo $home_title; ?></a></li><?php
			
			}
			
		}
	
		// do we have a title page url?
		if ( !empty( $title_url ) ) {
	
			// set default link name
			$title_title = apply_filters( 'cp_nav_title_page_title', __( 'Title Page', 'commentpress-core' ) );
	
			?><li><a href="<?php echo $title_url; ?>" id="btn_cover" class="css_btn" title="<?php echo $title_title; ?>"><?php echo $title_title; ?></a></li><?php
		
		}
	
		// show link to general comments page if we have one
		echo $commentpress_core->get_page_link( 'cp_general_comments_page' );
		
		// show link to all comments page if we have one
		echo $commentpress_core->get_page_link( 'cp_all_comments_page' );
		
		// show link to comments-by-user page if we have one
		echo $commentpress_core->get_page_link( 'cp_comments_by_page' );
		
		// show link to document blog page if we have one
		echo $commentpress_core->get_page_link( 'cp_blog_page' );
		
		// show link to document blog archive page if we have one
		echo $commentpress_core->get_page_link( 'cp_blog_archive_page' );
		
	}
		


	// is this multisite?
	if ( is_multisite() ) {
	
		// can users register?
		if ( get_option( 'users_can_register' ) ) {
			
			// this works for get_site_option( 'registration' ) == 'none' and 'user'
			?><li><?php wp_register(' ' , ' '); ?></li>
			<?php 
	
		}
	
		// multisite signup and blog create
		if ( 
		
			( is_user_logged_in() AND get_site_option( 'registration' ) == 'blog' ) OR
			get_site_option( 'registration' ) == 'all'
			
		) {
		
			// test whether we have BuddyPress
			if ( function_exists( 'bp_get_root_domain' ) ) {
			
				// different behaviour when logged in or not
				if ( is_user_logged_in() ) {
			
					// set default link name
					$_new_site_title = apply_filters( 
						'cp_user_links_new_site_title', 
						__( 'Create a new document', 'commentpress-core' )
					);
			
					// BP uses its own signup page
					?><li><a href="<?php echo bp_get_root_domain().'/'.bp_get_blogs_root_slug(); ?>/create/" title="<?php echo $_new_site_title; ?>" id="btn_create"><?php echo $_new_site_title; ?></a></li>
					<?php 
				
				} else {
				
					// not directly allowed - done through signup form
				
				}
	
			} else {
				
				// set default link name
				$_new_site_title = apply_filters( 
					'cp_user_links_new_site_title', 
					__( 'Create a new document', 'commentpress-core' )
				);
		
				// standard WP multisite
				?><li><a href="<?php echo network_site_url(); ?>wp-signup.php" title="<?php echo $_new_site_title; ?>" id="btn_create"><?php echo $_new_site_title; ?></a></li>
				<?php 
			
			}
		
		}
	
	} else {
	
		// if logged in
		if ( is_user_logged_in() ) {
		
			// set default link name
			$_dashboard_title = apply_filters( 
				'cp_user_links_dashboard_title', 
				__( 'Dashboard', 'commentpress-core' )
			);
	
			?>
			<li><a href="<?php echo admin_url(); ?>" title="<?php echo $_dashboard_title; ?>" id="btn_dash"><?php echo $_dashboard_title; ?></a></li>
			<?php
			
		}
		
		/*
		// testing JS
		?>
		<li><a href="#" title="Javascript" id="btn_js">Javascript</a></li>
		<?php
		*/
	
		
	}


	// login/logout
	?><li><?php wp_loginout(); ?></li>
	<?php
	


	?>
</ul>



</div><!-- /document_nav_wrapper -->



</div><!-- /document_nav -->
	
	
	
