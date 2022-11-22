<?php
/**
 * Template Name: Links
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

get_header();

?>
<!-- links.php -->
<div id="wrapper">
	<div id="main_wrapper" class="clearfix">
		<div id="page_wrapper">

			<div id="content" class="clearfix">
				<div class="post">

					<h2 class="post_title"><?php esc_html_e( 'Links:', 'commentpress-core' ); ?></h2>

					<ul>
						<?php wp_list_bookmarks(); ?>
					</ul>

				</div><!-- /post -->
			</div><!-- /content -->

		</div><!-- /page_wrapper -->
	</div><!-- /main_wrapper -->
</div><!-- /wrapper -->

<?php get_sidebar(); ?>

<?php get_footer(); ?>
