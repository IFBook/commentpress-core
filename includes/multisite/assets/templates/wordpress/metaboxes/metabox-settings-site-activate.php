<?php
/**
 * Multisite Site Settings screen "Activation" metabox template.
 *
 * Handles markup for the Multisite Site Settings screen "Activation" metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo esc_html( $this->metabox_path ); ?>metabox-settings-site-activate.php -->
<table class="form-table">

	<?php

	/**
	 * Fires at the top of the "Activation" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/site/metabox/activate/before' );

	?>

	<?php

	/**
	 * Fires at the bottom of the "Activation" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/site/metabox/activate/after' );

	?>

</table>
