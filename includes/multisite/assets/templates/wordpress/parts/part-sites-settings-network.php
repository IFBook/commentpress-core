<?php
/**
 * Network Settings screen Sites form elements.
 *
 * Handles markup for the Sites form elements on the "Network Settings" screen.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->parts_path; ?>part-sites-settings-network.php -->
<tr valign="top">
	<th scope="row">
		<label for="<?php echo esc_attr( $this->key_forced ); ?>"><?php esc_html_e( 'Make all new sites CommentPress-enabled', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<input id="<?php echo esc_attr( $this->key_forced ); ?>" name="<?php echo esc_attr( $this->key_forced ); ?>" value="1" type="checkbox"<?php echo ( $forced == '1' ? ' checked="checked"' : '' ); ?> />
	</td>
</tr>
