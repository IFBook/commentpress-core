<?php
/**
 * Multisite Site Settings screen "Deactivation" metabox template.
 *
 * Handles markup for the Multisite Site Settings screen "Deactivation" metabox.
 *
 * @package CommentPress_Core
 */

?>
<!-- includes/multisite/assets/templates/wordpress/metaboxes/metabox-settings-site-deactivate.php -->
<table class="form-table">

	<?php

	/**
	 * Fires at the top of the "Deactivation" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/site/metabox/deactivate/before' );

	?>

	<style>
		#commentpress_deactivate {
			border-color: #dd1308;
		}
		#commentpress_deactivate > .postbox-header {
			border-bottom-color: #dd1308;
		}
		#commentpress_deactivate > .postbox-header > .hndle {
			color: #dd1308;
		}
	</style>

	<tr valign="top">
		<th scope="row">
			<label for="cp_deactivate_commentpress"><?php esc_html_e( 'Disable CommentPress on this site', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="cp_deactivate_commentpress" name="cp_deactivate_commentpress" value="1" type="checkbox" />
			<p class="description"><?php esc_html_e( 'You will not lose any data if you disable CommentPress on this site, however this is a drastic action to take. Please be certain.', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<?php

	/**
	 * Fires at the bottom of the "Deactivation" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/site/metabox/deactivate/after' );

	?>

</table>
