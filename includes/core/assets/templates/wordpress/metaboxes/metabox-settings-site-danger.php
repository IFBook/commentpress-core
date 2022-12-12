<?php
/**
 * Site Settings screen "Danger Zone" metabox template.
 *
 * Handles markup for the Site Settings screen "Danger Zone" metabox.
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
			background: #fff5f5;
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
	do_action( 'commentpress/core/settings/site/metabox/danger/before' );

	?>

	<tr>
		<th scope="row">
			<label for="cp_reset"><?php esc_html_e( 'Reset Site settings', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="cp_reset" name="cp_reset" value="1" type="checkbox" />
			<p class="description"><?php esc_html_e( 'Resets settings for this Site to the plugin defaults.', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<?php

	/**
	 * Fires at the bottom of the "Danger Zone" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/core/settings/site/metabox/danger/after' );

	?>

</table>
