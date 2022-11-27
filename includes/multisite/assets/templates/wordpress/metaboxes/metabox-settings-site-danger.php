<?php
/**
 * Multisite Site Settings screen "Danger Zone" metabox template.
 *
 * Handles markup for the Multisite Site Settings screen "Danger Zone" metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->metabox_path; ?>metabox-settings-site-danger.php -->
<table class="form-table">

	<style>
		#commentpress_danger {
			border-color: #dd1308;
		}
		#commentpress_danger > .postbox-header {
			border-bottom-color: #dd1308;
		}
		#commentpress_danger > .postbox-header > .hndle {
			color: #dd1308;
		}
	</style>

	<?php

	/**
	 * Fires at the top of the "Danger Zone" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/site/metabox/danger/before' );

	?>

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
	 * Fires at the bottom of the "Danger Zone" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/site/metabox/danger/after' );

	?>

</table>
