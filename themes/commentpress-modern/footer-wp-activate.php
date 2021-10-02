<!-- footer.php -->



<!-- Activate page wrappers -->
</div><!-- /content -->
</div><!-- /page_wrapper -->
</div><!-- /main_wrapper -->
</div><!-- /wrapper -->



<?php /* opened in assets/templates/header_body.php */ ?>
</div><!-- /content_container -->



<?php get_sidebar(); ?>



<div id="footer">

<div id="footer_inner">

	<?php

	// Show footer menu if assigned.
	if ( has_nav_menu( 'footer' ) ) {
		wp_nav_menu( [
			'theme_location' => 'footer',
			'container_class' => 'commentpress-footer-nav-menu',
		] );
	}

	// Are we using the page footer widget?
	if ( ! dynamic_sidebar( 'cp-license-8' ) ) {

		// No - make other provision here.

		// Compat with wplicense plugin.
		if ( function_exists( 'isLicensed' ) AND isLicensed() ) {

			// Show the license from wpLicense.
			cc_showLicenseHtml();

		} else {

			// Show copyright.
			?><p><?php _e( 'Website content', 'commentpress-core' ); ?> &copy; <a href="<?php echo home_url(); ?>"><?php bloginfo('name'); ?></a> <?php echo date('Y'); ?>. <?php _e( 'All rights reserved.', 'commentpress-core' ); ?></p><?php

		}

	}

	?>

</div><!-- /footer_inner -->

</div><!-- /footer -->



</div><!-- /container -->



<?php wp_footer() ?>



</body>



</html>
