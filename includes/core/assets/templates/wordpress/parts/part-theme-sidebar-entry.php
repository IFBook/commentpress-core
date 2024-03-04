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
<!-- <?php echo $this->parts_path; ?>part-theme-sidebar-entry.php -->
<div class="<?php echo $this->key_sidebar; ?>_wrapper">

	<p><strong><label for="<?php echo esc_attr( $this->key_sidebar ); ?>"><?php esc_html_e( 'Default Sidebar', 'commentpress-core' ); ?></label></strong></p>

	<p>
		<select id="<?php echo esc_attr( $this->key_sidebar ); ?>" name="<?php echo esc_attr( $this->key_sidebar ); ?>">
			<option value="" <?php echo ( empty( $sidebar ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Use default', 'commentpress-core' ); ?></option>
			<option value="toc" <?php echo ( 'toc' === $sidebar ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Contents', 'commentpress-core' ); ?></option>
			<option value="activity" <?php echo ( 'activity' === $sidebar ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Activity', 'commentpress-core' ); ?></option>
			<option value="comments" <?php echo ( 'comments' === $sidebar ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Comments', 'commentpress-core' ); ?></option>
		</select>
	</p>

</div>
