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
<!-- toc_dropdown.php -->
<div id="toc_dropdown">

	<div id="toc_dd_header">
		<h2><?php esc_html_e( 'Table of Contents', 'commentpress-core' ); ?></h2>
	</div>

	<div id="toc_dd_wrapper">
		<?php if ( ! empty( $core ) ) : ?>
			<ul id="toc_dd_list">
				<?php echo $core->get_toc_list(); ?></ul>
		<?php endif; ?>
	</div><!-- /toc_dd_wrapper -->

</div><!-- /toc_dropdown -->
