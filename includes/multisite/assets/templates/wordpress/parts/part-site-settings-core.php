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
<!-- <?php echo $this->parts_path; ?>part-sites-settings-core.php -->
<tr valign="top">
	<th scope="row">
		<label for="<?php echo $this->key_disable; ?>"><?php esc_html_e( 'Disable CommentPress on this Site', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<input id="<?php echo $this->key_disable; ?>" name="<?php echo $this->key_disable; ?>" value="1" type="checkbox" />
		<p class="description"><?php esc_html_e( 'You will not lose any data if you disable CommentPress on this site, however this is a drastic action to take. Please be certain.', 'commentpress-core' ); ?></p>
	</td>
</tr>
