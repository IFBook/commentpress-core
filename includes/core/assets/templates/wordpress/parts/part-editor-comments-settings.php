<?php
/**
 * Site Settings screen Comments form elements.
 *
 * Handles markup for the Comments form elements on the Site Settings screen.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->parts_path; ?>part-editor-comments-settings.php -->
<tr valign="top">
	<th scope="row">
		<label for="<?php echo esc_attr( $this->key_editor ); ?>"><?php esc_html_e( 'Comment form editor', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<select id="<?php echo esc_attr( $this->key_editor ); ?>" name="<?php echo esc_attr( $this->key_editor ); ?>">
			<option value="1" <?php echo ( ( $editor == '1' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Rich-text Editor', 'commentpress-core' ); ?></option>
			<option value="0" <?php echo ( ( $editor == '0' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Plain-text Editor', 'commentpress-core' ); ?></option>
		</select>
	</td>
</tr>

<tr valign="top">
	<th scope="row"><label for="<?php echo esc_attr( $this->key_promote ); ?>">
		<?php esc_html_e( 'Comment form behaviour', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<select id="<?php echo esc_attr( $this->key_promote ); ?>" name="<?php echo esc_attr( $this->key_promote ); ?>">
			<option value="1" <?php echo ( ( $promote == '1' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Promote reading', 'commentpress-core' ); ?></option>
			<option value="0" <?php echo ( ( $promote == '0' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Promote commenting', 'commentpress-core' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'When promoting commenting, the comment form will be displayed and clicking or tapping a commentable block will scroll directly to the comment form in the comments section for that block.', 'commentpress-core' ); ?></p>
		<p class="description"><?php esc_html_e( 'When promoting reading, the comment form will not be displayed by default and clicking or tapping a commentable block will scroll to the top of the comments section for that block.', 'commentpress-core' ); ?></p>
	</td>
</tr>

