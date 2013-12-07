<!-- footer.php -->



<?php /* opened in assets/templates/header_body.php */ ?>
</div><!-- /content_container -->



<div id="footer">	

<div id="footer_inner">

	<?php 
	
	// are we using the page footer widget?
	if ( !dynamic_sidebar( 'cp-license-8' ) ) {
		
		// no - make other provision here
	
		// compat with wplicense plugin
		if ( function_exists( 'isLicensed' ) AND isLicensed() ) {
		
			// show the license from wpLicense
			cc_showLicenseHtml();
			
		} else {
			
			// show copyright 
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