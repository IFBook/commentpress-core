<!-- footer.php -->

<div id="footer" class="clearfix">

<div id="footer_inner">

	<?php if ( is_active_sidebar( 'cp-license-8' ) ) : ?>
		<div class="footer_widgets">
			<?php dynamic_sidebar( 'cp-license-8' ); ?>
		</div>
	<?php else : ?>
		<p><?php echo sprintf(
			__( 'Website content &copy; %1$s %2$s. All rights reserved.', 'commentpress-core' ),
			'<a href="' . home_url() . '">' . get_bloginfo( 'name' ) . '</a>',
			date('Y')
		); ?></p>
	<?php endif; ?>

</div><!-- /footer_inner -->

</div><!-- /footer -->



</div><!-- /container -->



<?php wp_footer() ?>



</body>



</html>
