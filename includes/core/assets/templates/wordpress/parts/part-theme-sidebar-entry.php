<?php
/**
 * "CommentPress Settings" Sidebar form element.
 *
 * Handles markup for the Sidebar form element in the "CommentPress Settings" Metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo esc_html( $this->parts_path ); ?>part-theme-sidebar-entry.php -->
<div class="<?php echo esc_attr( $this->key_sidebar ); ?>_wrapper">

	<p><strong><label for="<?php echo esc_attr( $this->key_sidebar ); ?>"><?php esc_html_e( 'Default Sidebar', 'commentpress-core' ); ?></label></strong></p>

	<p>
		<select id="<?php echo esc_attr( $this->key_sidebar ); ?>" name="<?php echo esc_attr( $this->key_sidebar ); ?>">
			<option value="" <?php selected( $sidebar, '' ); ?>><?php esc_html_e( 'Use default', 'commentpress-core' ); ?></option>
			<option value="toc" <?php selected( $sidebar, 'toc' ); ?>><?php esc_html_e( 'Contents', 'commentpress-core' ); ?></option>
			<option value="activity" <?php selected( $sidebar, 'activity' ); ?>><?php esc_html_e( 'Activity', 'commentpress-core' ); ?></option>
			<option value="comments" <?php selected( $sidebar, 'comments' ); ?>><?php esc_html_e( 'Comments', 'commentpress-core' ); ?></option>
		</select>
	</p>

</div>
