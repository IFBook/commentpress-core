<?php
/**
 * Multisite Network Settings Page "General Settings" metabox template.
 *
 * Handles markup for the Multisite Network Settings Page "General Settings" metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo esc_html( $this->metabox_path ); ?>metabox-settings-network-general.php -->
<table class="form-table">

	<?php

	/**
	 * Fires at the top of the "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/network/metabox/general/before' );

	?>

	<?php

	/**
	 * Fires at the bottom of the "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/network/metabox/general/after' );

	?>

</table>
