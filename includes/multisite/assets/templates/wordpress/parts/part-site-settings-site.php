<?php
/**
 * Site Settings screen Site form elements.
 *
 * Handles markup for the Site form elements on the "CommentPress Settings" screen.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo esc_html( $this->parts_path ); ?>part-sites-settings-site.php -->
<tr valign="top">
	<th scope="row">
		<label for="<?php echo esc_attr( $this->key_enable ); ?>"><?php esc_html_e( 'Enable CommentPress on this Site', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<input id="<?php echo esc_attr( $this->key_enable ); ?>" name="<?php echo esc_attr( $this->key_enable ); ?>" value="1" type="checkbox" />
	</td>
</tr>
