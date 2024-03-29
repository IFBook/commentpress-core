<?php
/**
 * Multisite Network Settings Page "WordPress Overrides" metabox template.
 *
 * Handles markup for the Multisite Network Settings Page "WordPress Overrides" metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo esc_html( $this->metabox_path ); ?>metabox-settings-network-wordpress.php -->
<table class="form-table">

	<?php

	/**
	 * Fires at the top of the "WordPress Overrides" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/network/metabox/wordpress/before' );

	?>

	<tr valign="top">
		<th scope="row">
			<label for="cpmu_delete_first_page"><?php esc_html_e( 'Delete WordPress-generated Sample Page', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="cpmu_delete_first_page" name="cpmu_delete_first_page" value="1" type="checkbox"<?php checked( ! empty( $delete_first_page ) ? ' checked="checked"' : '' ); ?> />
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="cpmu_delete_first_post"><?php esc_html_e( 'Delete WordPress-generated Hello World post', 'commentpress-core' ); ?></label></th>
		<td>
			<input id="cpmu_delete_first_post" name="cpmu_delete_first_post" value="1" type="checkbox"<?php echo ( ! empty( $delete_first_post ) ? ' checked="checked"' : '' ); ?> />
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="cpmu_delete_first_comment"><?php esc_html_e( 'Delete WordPress-generated First Comment', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="cpmu_delete_first_comment" name="cpmu_delete_first_comment" value="1" type="checkbox"<?php echo ( ! empty( $delete_first_comment ) ? ' checked="checked"' : '' ); ?> />
		</td>
	</tr>

	<?php

	/**
	 * Fires at the bottom of the "WordPress Overrides" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/network/metabox/wordpress/after' );

	?>

</table>
