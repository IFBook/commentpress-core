<?php /*
================================================================================
CommentPress Modern Theme User Links Template
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

User links template for CommentPress Core

--------------------------------------------------------------------------------
*/



// Access plugin global.
global $commentpress_core;



?><!-- user_links.php -->

<div id="user_links">

<ul>
<?php

// Login/logout.
?><li><?php wp_loginout(); ?></li>
<?php

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

		// Test whether we have BuddyPress.
		if ( function_exists( 'bp_get_blogs_root_slug' ) ) {

			// Different behaviour when logged in or not.
			if ( is_user_logged_in() ) {

				// Set default link name.
				$new_site_title = apply_filters(
					'cp_user_links_new_site_title',
					__( 'Create a new document', 'commentpress-core' )
				);

				// BuddyPress uses its own signup page.
				?><li><a href="<?php echo bp_get_root_domain() . '/' . bp_get_blogs_root_slug(); ?>/create/" title="<?php echo $new_site_title; ?>" id="btn_create" class="button"><?php echo $new_site_title; ?></a></li>
				<?php

			} else {

				// Not directly allowed - done through signup form.

			}

		} else {

			// Set default link name.
			$new_site_title = apply_filters(
				'cp_user_links_new_site_title',
				__( 'Create a new document', 'commentpress-core' )
			);

			// Standard WordPress multisite.
			?><li><a href="<?php echo network_site_url(); ?>wp-signup.php" title="<?php echo $new_site_title; ?>" id="btn_create" class="button"><?php echo $new_site_title; ?></a></li>
			<?php

		}

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
		<li><a href="<?php echo admin_url(); ?>" title="<?php echo $dashboard_title; ?>" id="btn_dash" class="button"><?php echo $dashboard_title; ?></a></li>
		<?php

	}

	/*
	// Testing JS.
	?>
	<li><a href="#" title="Javascript" id="btn_js">Javascript</a></li>
	<?php
	*/

}

?></ul>
</div>

<!-- /user_links.php -->



