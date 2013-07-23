<?php

/**
 * BuddyPress - Blogs Directory
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

?>

<?php get_header( 'buddypress' ); ?>

<!-- blogs/index.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



	<?php do_action( 'bp_before_directory_blogs_page' ); ?>

	<div id="content">
		<div class="padder">

		<?php do_action( 'bp_before_directory_blogs' ); ?>

		<form action="" method="post" id="blogs-directory-form" class="dir-form">

			<h3><?php 
			
			// define title
			$page_title = __( 'Sites', 'commentpress-core' );
			
			// allow overrides
			echo apply_filters( 'cp_site_directory_page_title', $page_title );
			
			?><?php if ( is_user_logged_in() && bp_blog_signup_enabled() ) : ?> &nbsp;<a class="button" href="<?php echo bp_get_root_domain() . '/' . bp_get_blogs_root_slug() . '/create/' ?>"><?php 
			
			// define title
			$create_title = __( 'Create a New Document', 'commentpress-core' );
			
			// allow overrides
			echo apply_filters( 'cp_site_directory_link_title', $create_title );
			
			?></a><?php endif; ?></h3>

			<?php do_action( 'bp_before_directory_blogs_content' ); ?>

			<div id="blog-dir-search" class="dir-search" role="search">

				<?php bp_directory_blogs_search_form(); ?>

			</div><!-- #blog-dir-search -->

			<div class="item-list-tabs" role="navigation">
				<ul>
					<li class="selected" id="blogs-all"><a href="<?php bp_root_domain(); ?>/<?php bp_blogs_root_slug() ?>"><?php printf( __( 'All Sites <span>%s</span>', 'commentpress-core' ), bp_get_total_blog_count() ); ?></a></li>

					<?php if ( is_user_logged_in() && bp_get_total_blog_count_for_user( bp_loggedin_user_id() ) ) : ?>

						<li id="blogs-personal"><a href="<?php echo bp_loggedin_user_domain() . bp_get_blogs_slug() ?>"><?php printf( __( 'My Sites <span>%s</span>', 'commentpress-core' ), bp_get_total_blog_count_for_user( bp_loggedin_user_id() ) ); ?></a></li>

					<?php endif; ?>

					<?php do_action( 'bp_blogs_directory_blog_types' ); ?>

				</ul>
			</div><!-- .item-list-tabs -->

			<div class="item-list-tabs" id="subnav" role="navigation">
				<ul>

					<?php do_action( 'bp_blogs_directory_blog_sub_types' ); ?>

					<li id="blogs-order-select" class="last filter">

						<label for="blogs-order-by"><?php _e( 'Order By:', 'commentpress-core' ); ?></label>
						<select id="blogs-order-by">
							<option value="active"><?php _e( 'Last Active', 'commentpress-core' ); ?></option>
							<option value="newest"><?php _e( 'Newest', 'commentpress-core' ); ?></option>
							<option value="alphabetical"><?php _e( 'Alphabetical', 'commentpress-core' ); ?></option>

							<?php do_action( 'bp_blogs_directory_order_options' ); ?>

						</select>
					</li>
				</ul>
			</div>

			<div id="blogs-dir-list" class="blogs dir-list">

				<?php locate_template( array( 'blogs/blogs-loop.php' ), true ); ?>

			</div><!-- #blogs-dir-list -->

			<?php do_action( 'bp_directory_blogs_content' ); ?>

			<?php wp_nonce_field( 'directory_blogs', '_wpnonce-blogs-filter' ); ?>

			<?php do_action( 'bp_after_directory_blogs_content' ); ?>

		</form><!-- #blogs-directory-form -->

		<?php do_action( 'bp_after_directory_blogs' ); ?>

		</div><!-- .padder -->
	</div><!-- #content -->

	<?php do_action( 'bp_after_directory_blogs_page' ); ?>

</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar( 'buddypress' ); ?>
<?php get_footer( 'buddypress' ); ?>
