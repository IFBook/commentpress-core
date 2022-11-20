<?php
/**
 * Multisite Site Settings Page "General Settings" metabox template.
 *
 * Handles markup for the Multisite Site Settings Page "General Settings" metabox.
 *
 * @package CommentPress_Core
 */

?>
<!-- includes/commentpress-multisite/assets/templates/wordpress/metaboxes/metabox-settings-site-general.php -->
<table class="form-table">

	<?php

	/**
	 * Fires at the top of the "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/site/metabox/general/before' );

	?>

	<tr valign="top">
		<th scope="row">

		<label for="cp_activate_commentpress"><?php esc_html_e( 'Enable CommentPress on this site', 'commentpress-core' ); ?></label></th>
		<td>
			<input id="cp_activate_commentpress" name="cp_activate_commentpress" value="1" type="checkbox" />
		</td>
	</tr>

	<?php

	/**
	 * Fires at the bottom of the "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/site/metabox/general/after' );

	?>

</table>
