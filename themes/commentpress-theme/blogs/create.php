<?php

/**
 * BuddyPress - Create Blog
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

?>

<?php get_header( 'buddypress' ); ?>

<!-- blogs/create.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



	<?php do_action( 'bp_before_directory_blogs_content' ); ?>

	<div id="content">
		<div class="padder" role="main">

		<?php do_action( 'template_notices' ); ?>

			<h3><?php 
			
			// define title
			$create_title = apply_filters(
				'cp_register_new_site_page_title', 
				__( 'Create a New Document', 'commentpress-core' )
			);
			
			// allow overrides
			echo apply_filters( 'cp_create_site_page_title', $create_title );
			
			?> &nbsp;<a class="button" href="<?php echo trailingslashit( bp_get_root_domain() . '/' . bp_get_blogs_root_slug() ) ?>"><?php 
			
			// define link title
			$link_title = __( 'Directory', 'commentpress-core' );
			
			// allow overrides
			echo apply_filters( 'cp_create_site_page_link_title', $link_title );
			
			?></a></h3>

		<?php do_action( 'bp_before_create_blog_content' ); ?>

		<?php if ( bp_blog_signup_enabled() ) : ?>

			<?php bp_show_blog_signup_form(); ?>

		<?php else: ?>

			<div id="message" class="info">
				<p><?php _e( 'Site registration is currently disabled', 'commentpress-core' ); ?></p>
			</div>

		<?php endif; ?>

		<?php do_action( 'bp_after_create_blog_content' ); ?>

		</div><!-- .padder -->
	</div><!-- #content -->

	<?php do_action( 'bp_after_directory_blogs_content' ); ?>

</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar( 'buddypress' ); ?>
<?php get_footer( 'buddypress' ); ?>

