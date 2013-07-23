<?php /*
================================================================================
CommentPress Modern Theme User Links Template
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

User links template for CommentPress

--------------------------------------------------------------------------------
*/



// acces plugin global
global $commentpress_core;



?><!-- user_links.php -->

<div id="user_links">

<ul>
<?php

// login/logout
?><li><?php wp_loginout(); ?></li>
<?php

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
				?><li><a href="<?php echo bp_get_root_domain().'/'.bp_get_blogs_root_slug(); ?>/create/" title="<?php echo $_new_site_title; ?>" id="btn_create" class="button"><?php echo $_new_site_title; ?></a></li>
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
			?><li><a href="<?php echo network_site_url(); ?>wp-signup.php" title="<?php echo $_new_site_title; ?>" id="btn_create" class="button"><?php echo $_new_site_title; ?></a></li>
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
		<li><a href="<?php echo admin_url(); ?>" title="<?php echo $_dashboard_title; ?>" id="btn_dash" class="button"><?php echo $_dashboard_title; ?></a></li>
		<?php
		
	}
	
	/*
	// testing JS
	?>
	<li><a href="#" title="Javascript" id="btn_js">Javascript</a></li>
	<?php
	*/
	
}
	
?></ul>
</div>

<!-- /user_links.php -->



