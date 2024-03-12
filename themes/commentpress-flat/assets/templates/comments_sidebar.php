<?php
/**
 * Comments Sidebar Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Declare access to globals.
global $post;

// Override if there are no Comments (for print stylesheet to hide title).
$no_comments_class = ( 0 === $post->comment_count ) ? ' no_comments' : '';

?>
<!-- comments_sidebar.php -->
<div id="comments_sidebar" class="sidebar_container<?php echo esc_attr( $no_comments_class ); ?>">

	<div class="sidebar_header">
		<h2><?php esc_html_e( 'Comments', 'commentpress-core' ); ?></h2>
	</div>

	<div class="sidebar_minimiser">
		<?php comments_template(); ?>
	</div><!-- /sidebar_minimiser -->

</div><!-- /comments_sidebar -->
