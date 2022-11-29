<?php
/**
 * Site Settings screen "Page Display Options" metabox template.
 *
 * Handles markup for the Site Settings screen "Page Display Options" metabox.
 *
 * @package CommentPress_Core
 */

?>
<!-- <?php echo $this->metabox_path; ?>metabox-settings-site-page.php -->
<table class="form-table">

	<?php

	/**
	 * Fires at the top of the "Page Display Options" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/core/settings/site/metabox/page/before' );

	?>

	<tr valign="top">
		<th scope="row">
			<label for="cp_page_nav_enabled"><?php esc_html_e( 'Automatic page navigation', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<select id="cp_page_nav_enabled" name="cp_page_nav_enabled">
				<option value="y" <?php echo ( $page_nav_enabled == 'y' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Yes', 'commentpress-core' ); ?></option>
				<option value="n" <?php echo ( $page_nav_enabled == 'n' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'No', 'commentpress-core' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'This controls appearance of page numbering and navigation arrows on hierarchical pages.', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="cp_featured_images"><?php esc_html_e( 'Enable Featured Images', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<select id="cp_featured_images" name="cp_featured_images">
				<option value="y" <?php echo ( $featured_images == 'y' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Yes', 'commentpress-core' ); ?></option>
				<option value="n" <?php echo ( $featured_images == 'n' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'No', 'commentpress-core' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'If you have already implemented this in a child theme, you should choose "No".', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<?php

	/**
	 * Fires at the bottom of the "Page Display Options" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/core/settings/site/metabox/page/after' );

	?>

</table>
