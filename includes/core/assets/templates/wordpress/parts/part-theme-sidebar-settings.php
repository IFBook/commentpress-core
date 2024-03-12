<?php
/**
 * Site Settings screen Theme Sidebar form element.
 *
 * Handles markup for the Theme Sidebar form element on the Site Settings screen.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo esc_html( $this->parts_path ); ?>part-theme-sidebar-settings.php -->
<tr valign="top">
	<th scope="row">
		<label for="<?php echo esc_attr( $this->key_sidebar ); ?>"><?php esc_html_e( 'Default active sidebar', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<select id="<?php echo esc_attr( $this->key_sidebar ); ?>" name="<?php echo esc_attr( $this->key_sidebar ); ?>">
			<option value="toc" <?php selected( $sidebar, 'contents' ); ?>><?php esc_html_e( 'Contents', 'commentpress-core' ); ?></option>
			<option value="activity" <?php selected( $sidebar, 'activity' ); ?>><?php esc_html_e( 'Activity', 'commentpress-core' ); ?></option>
			<option value="comments" <?php selected( $sidebar, 'comments' ); ?>><?php esc_html_e( 'Comments', 'commentpress-core' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'This setting can be overridden on individual entries.', 'commentpress-core' ); ?></p>
	</td>
</tr>
