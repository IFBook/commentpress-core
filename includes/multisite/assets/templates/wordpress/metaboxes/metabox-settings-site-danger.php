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

	<?php

	/**
	 * Fires at the bottom of the "Danger Zone" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/site/metabox/danger/after' );

	?>

</table>
