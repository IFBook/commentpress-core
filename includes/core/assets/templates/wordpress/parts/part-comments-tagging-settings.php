<?php
/**
 * Site Settings screen Comments Tagging form element.
 *
 * Handles markup for the Comments Tagging form element on the Site Settings screen.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo esc_html( $this->parts_path ); ?>part-comments-tagging-settings.php -->
<tr valign="top">
	<th scope="row">
		<label for="<?php echo esc_attr( $this->key_tagging ); ?>"><?php esc_html_e( 'Comment Tagging', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<select id="<?php echo esc_attr( $this->key_tagging ); ?>" name="<?php echo esc_attr( $this->key_tagging ); ?>">
			<option value="y" <?php selected( $tagging, 'y' ); ?>><?php esc_html_e( 'Enabled', 'commentpress-core' ); ?></option>
			<option value="n" <?php selected( $tagging, 'n' ); ?>><?php esc_html_e( 'Disabled', 'commentpress-core' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'You may need to visit your Permalinks settings page to refresh your Rewrite Rules after enabling or disabling this setting.', 'commentpress-core' ); ?></p>
	</td>
</tr>
