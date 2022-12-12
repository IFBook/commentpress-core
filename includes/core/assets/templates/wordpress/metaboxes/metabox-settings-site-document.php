<?php
/**
 * Site Settings screen "Structured Document Settings" metabox template.
 *
 * Handles markup for the Site Settings screen "Structured Document Settings" metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->metabox_path; ?>metabox-settings-site-document.php -->
<table class="form-table">

	<?php

	/**
	 * Fires at the top of the "Structured Document Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/core/settings/site/metabox/document/before' );

	?>

	<?php

	/**
	 * Fires at the bottom of the "Structured Document Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/core/settings/site/metabox/document/after' );

	?>

</table>
