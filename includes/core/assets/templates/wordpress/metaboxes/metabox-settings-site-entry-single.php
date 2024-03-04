<?php
/**
 * Site Settings screen "Page Display Settings" metabox template.
 *
 * Handles markup for the Site Settings screen "Page Display Settings" metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->metabox_path; ?>metabox-settings-site-entry-single.php -->
<table class="form-table">

	<?php

	/**
	 * Fires at the top of the "Page Display Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/core/settings/site/metabox/entry/before' );

	?>

	<tr valign="top">
		<th scope="row">
			<label for="<?php echo esc_attr( $this->key_show_title ); ?>"><?php esc_html_e( 'Show Page Titles', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<select id="<?php echo esc_attr( $this->key_show_title ); ?>" name="<?php echo esc_attr( $this->key_show_title ); ?>">
				<option value="show" <?php echo ( ( 'show' === $show_title ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Yes', 'commentpress-core' ); ?></option>
				<option value="hide" <?php echo ( ( 'hide' === $show_title ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'No', 'commentpress-core' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'This setting can be overridden on individual entries.', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="<?php echo esc_attr( $this->key_show_meta ); ?>"><?php esc_html_e( 'Show Page Meta', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<select id="<?php echo esc_attr( $this->key_show_meta ); ?>" name="<?php echo esc_attr( $this->key_show_meta ); ?>">
				<option value="show" <?php echo ( ( 'show' === $show_meta ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Yes', 'commentpress-core' ); ?></option>
				<option value="hide" <?php echo ( ( 'hide' === $show_meta ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'No', 'commentpress-core' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'Page "meta" contains the avatar and name of the author as well as the date published.', 'commentpress-core' ); ?></p>
			<p class="description"><?php esc_html_e( 'This setting can be overridden on individual entries.', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<?php

	/**
	 * Fires at the bottom of the "Page Display Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/core/settings/site/metabox/entry/after' );

	?>

</table>
