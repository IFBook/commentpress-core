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
<!-- <?php echo $this->metabox_path; ?>metabox-settings-site-activate.php -->
<table class="form-table">

	<?php

	/**
	 * Fires at the top of the "Activation" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/site/metabox/activate/before' );

	?>

	<tr valign="top">
		<th scope="row">
			<label for="cp_activate_commentpress"><?php esc_html_e( 'Enable CommentPress on this site', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="cp_activate_commentpress" name="cp_activate_commentpress" value="1" type="checkbox" />
		</td>
	</tr>

	<?php

	/**
	 * Fires at the bottom of the "Activation" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/site/metabox/activate/after' );

	?>

</table>
