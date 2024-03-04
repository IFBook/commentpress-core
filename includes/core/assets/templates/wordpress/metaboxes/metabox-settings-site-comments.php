<?php
/**
 * Site Settings screen "Commenting Options" metabox template.
 *
 * Handles markup for the Site Settings screen "Commenting Options" metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->metabox_path; ?>metabox-settings-site-comments.php -->
<table class="form-table">

	<?php

	/**
	 * Fires at the top of the "Commenting Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/core/settings/site/metabox/comment/before' );

	?>

	<tr valign="top">
		<th scope="row">
			<label for="<?php echo esc_attr( $this->key_live ); ?>"><?php esc_html_e( '"Live" comment refreshing', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="<?php echo esc_attr( $this->key_live ); ?>" name="<?php echo esc_attr( $this->key_live ); ?>" value="1" type="checkbox" <?php echo ( ( '1' == $live ) ? ' checked="checked"' : '' ); ?> />
			<p class="description"><?php esc_html_e( 'Please note: enabling this may cause heavy load on your server.', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<?php

	/**
	 * Fires at the bottom of the "Commenting Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/core/settings/site/metabox/comment/after' );

	?>

</table>
