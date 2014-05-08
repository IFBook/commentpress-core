<?php get_header( 'buddypress' ) ?>

<!-- members/single/activity/permalink.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">

	<div id="content">
		<div class="padder">


			<?php do_action( 'template_notices' ); ?>

			<div class="activity no-ajax" role="main">
				<?php if ( bp_has_activities( 'display_comments=threaded&show_hidden=true&include=' . bp_current_action() ) ) : ?>

					<ul id="activity-stream" class="activity-list item-list">
					<?php while ( bp_activities() ) : bp_the_activity(); ?>

						<?php locate_template( array( 'activity/entry.php' ), true ) ?>

					<?php endwhile; ?>
					</ul>

				<?php endif; ?>
			</div>



		</div><!-- .padder -->
	</div><!-- #content -->

</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar( 'buddypress' ); ?>
<?php get_footer( 'buddypress' ) ?>