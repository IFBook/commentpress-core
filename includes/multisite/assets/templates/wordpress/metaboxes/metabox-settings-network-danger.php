<?php
/**
 * Multisite Network Settings screen "Danger Zone" metabox template.
 *
 * Handles markup for the Multisite Network Settings screen "Danger Zone" metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->metabox_path; ?>metabox-settings-network-danger.php -->
<table class="form-table">

	<style>
		#commentpress_network_danger {
			border-color: #dd1308;
		}
		#commentpress_network_danger > .postbox-header {
			border-bottom-color: #dd1308;
		}
		#commentpress_network_danger > .postbox-header > .hndle {
			color: #dd1308;
		}
	</style>

	<?php

	/**
	 * Fires at the top of the "Danger Zone" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/network/metabox/danger/before' );

	?>

	<tr valign="top">
		<th scope="row">
			<label for="cpmu_reset"><?php esc_html_e( 'Reset Multisite settings', 'commentpress-core' ); ?>
		</label></th>
		<td>
			<input id="cpmu_reset" name="cpmu_reset" value="1" type="checkbox" />
		</td>
	</tr>

	<?php

	/**
	 * Fires at the bottom of the "Danger Zone" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/network/metabox/danger/after' );

	?>

</table>
