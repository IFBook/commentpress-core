<?php
/**
 * Site Settings screen "Commenting Options" metabox template.
 *
 * Handles markup for the Site Settings screen "Commenting Options" metabox.
 *
 * @package CommentPress_Core
 */

?>
<!-- <?php echo $this->metabox_path; ?>metabox-settings-site-comment.php -->
<table class="form-table">

	<tr valign="top">
		<th scope="row">
			<label for="cp_comment_editor"><?php esc_html_e( 'Comment form editor', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<select id="cp_comment_editor" name="cp_comment_editor">
				<option value="1" <?php echo ( ( $comment_editor == '1' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Rich-text Editor', 'commentpress-core' ); ?></option>
				<option value="0" <?php echo ( ( $comment_editor == '0' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Plain-text Editor', 'commentpress-core' ); ?></option>
			</select>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cp_promote_reading">
			<?php esc_html_e( 'Comment form behaviour', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<select id="cp_promote_reading" name="cp_promote_reading">
				<option value="1" <?php echo ( ( $promote_reading == '1' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Promote reading', 'commentpress-core' ); ?></option>
				<option value="0" <?php echo ( ( $promote_reading == '0' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Promote commenting', 'commentpress-core' ); ?></option>
			</select>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="cp_para_comments_live"><?php esc_html_e( '"Live" comment refreshing', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="cp_para_comments_live" name="cp_para_comments_live" value="1" type="checkbox" <?php echo ( ( $comments_live == '1' ) ? ' checked="checked"' : '' ); ?> />
			<p class="description"><?php esc_html_e( 'Please note: enabling this may cause heavy load on your server.', 'commentpress-core' ); ?></p>
		</td>
	</tr>

</table>
