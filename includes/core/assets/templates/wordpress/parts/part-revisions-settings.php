<?php
/**
 * Site Settings screen Post Revisions form element.
 *
 * Handles markup for the Post Revisions form element on the Site Settings screen.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->parts_path; ?>part-revisions-settings.php -->
<tr valign="top">
	<th scope="row">
		<label for="<?php echo esc_attr( $this->key_revisions ); ?>"><?php esc_html_e( 'Post Revisions', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<select id="<?php echo esc_attr( $this->key_revisions ); ?>" name="<?php echo esc_attr( $this->key_revisions ); ?>">
			<option value="y" <?php echo ( $revisions == 'y' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Enabled', 'commentpress-core' ); ?></option>
			<option value="n" <?php echo ( $revisions == 'n' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Disabled', 'commentpress-core' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'CommentPress Post Revisions can be created from the "Edit Post" screen. The newly created Post Revision is a copy of the original Post ready to be edited. Auto-generated links between Revisions are shown at the top of each Post Revision for easy navigation between them.', 'commentpress-core' ); ?></p>
	</td>
</tr>
