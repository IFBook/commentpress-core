<?php
/**
 * Header Body Template.
 *
 * Separated out for inclusion in multiple files.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<a class="skip" href="#content"><?php esc_html_e( 'Skip to Content', 'commentpress-core' ); ?></a>
<span class="off-left"> | </span>
<a class="skip" href="#toc_list"><?php esc_html_e( 'Skip to Table of Contents', 'commentpress-core' ); ?></a>

<div id="header" class="clearfix">

	<?php

	/**
	 * Fires before the Page header.
	 *
	 * @since 3.8.5
	 */
	do_action( 'commentpress_header_before' );

	?>

	<?php commentpress_get_header_image(); ?>

	<div id="switcher">
		<ul>
			<li class="navigation-item"><a class="navigation-button" href="#navigation"><?php esc_html_e( 'Navigate', 'commentpress-core' ); ?></a></li>
			<li class="sidebar-item comments-item"><a class="comments-button" href="#comments_sidebar"><?php esc_html_e( 'Comments', 'commentpress-core' ); ?></a></li>
			<li class="sidebar-item activity-item"><a class="activity-button" href="#activity_sidebar"><?php esc_html_e( 'Activity', 'commentpress-core' ); ?></a></li>
		</ul>
	</div>

	<div id="page_title">
		<div id="title">
			<h1><a href="<?php echo esc_url( home_url() ); ?>" title="<?php esc_attr_e( 'Home', 'commentpress-core' ); ?>"><?php bloginfo( 'title' ); ?></a></h1>
		</div>
		<div id="tagline">
			<?php bloginfo( 'description' ); ?>
		</div>
	</div>

	<?php

	/**
	 * Fires after the Page header.
	 *
	 * @since 3.8.5
	 */
	do_action( 'commentpress_header_after' );

	?>

</div><!-- /header -->

<?php /* This element closes in "footer.php" */ ?>
<div id="content_container" class="clearfix">

	<?php

	/**
	 * Try to locate template using WordPress method.
	 *
	 * @since 3.4
	 *
	 * @param str The existing path returned by WordPress.
	 * @return str The modified path.
	 */
	$cp_toc_sidebar = apply_filters( 'cp_template_toc_sidebar', locate_template( 'assets/templates/toc_sidebar.php' ) );

	// Load it if we find it.
	if ( ! empty( $cp_toc_sidebar ) ) {
		load_template( $cp_toc_sidebar );
	}

	?>
