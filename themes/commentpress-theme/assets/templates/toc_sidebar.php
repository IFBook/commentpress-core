<?php
/**
 * Table of Contents Dropdown Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Get core plugin reference.
$core = commentpress_core();

?>
<!-- toc_sidebar.php -->

<div id="toc_sidebar" class="sidebar_container">

	<div class="sidebar_header">
		<h2><?php esc_html_e( 'Table of Contents', 'commentpress-core' ); ?></h2>
	</div>

	<div class="sidebar_minimiser">
		<div class="sidebar_contents_wrapper">

		<?php if ( ! empty( $core ) ) : ?>
			<ul id="toc_list">
				<?php echo $core->get_toc_list(); ?>
			</ul>
		<?php endif; ?>

		</div><!-- /sidebar_contents_wrapper -->
	</div><!-- /sidebar_minimiser -->

</div><!-- /toc_sidebar -->
