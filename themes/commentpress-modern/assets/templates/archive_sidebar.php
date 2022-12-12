<?php
/**
 * Archive Dropdown Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- archive_sidebar.php -->
<div id="archive_sidebar" class="sidebar_container">

	<div class="sidebar_header">
		<h2><?php esc_html_e( 'Archives', 'commentpress-core' ); ?></h2>
	</div>

	<div class="sidebar_minimiser">
		<div class="sidebar_contents_wrapper">

			<ul>
				<?php wp_get_archives( 'type=monthly' ); ?>
			</ul>

		</div><!-- /sidebar_contents_wrapper -->
	</div><!-- /sidebar_minimiser -->

</div><!-- /archive_sidebar -->
