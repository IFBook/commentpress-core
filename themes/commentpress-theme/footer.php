<!-- footer.php -->

<div id="footer" class="clearfix">	

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
			?>
		
			<p>Website content &copy; <a href="<?php echo home_url(); ?>"><?php bloginfo('name'); ?></a> <?php echo date('Y'); ?>. All rights reserved.</p>
			
			<?php 
			
			/*
			// legacy backlink, leave out for now
			if ( 
			
				"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] == home_url()."/" || 
				"http://www.".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] == home_url()."/" || 
				$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] == home_url()."/" || 
				"www.".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'] == home_url()."/" 
				
			) { 
			
			?>
			
			<p>This site is powered by <a href="http://www.futureofthebook.org/commentpress/">CommentPress</a></p>
			
			<?php 
			
			}
			*/
			
		}
		
	}
	
	?>

</div><!-- /footer_inner -->

</div><!-- /footer -->



</div><!-- /container -->



<?php wp_footer() ?>



</body>



</html>