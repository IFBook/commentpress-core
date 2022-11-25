<?php
/**
 * Site Settings screen Document form elements.
 *
 * Handles markup for the Document form elements on the "Site Settings" screen.
 *
 * @package CommentPress_Core
 */

?>
<!-- includes/core/assets/templates/wordpress/partials/partial-entry-document-settings.php -->
<tr valign="top">
	<th scope="row">
		<label for="cp_title_visibility"><?php esc_html_e( 'Page title visibility', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<select id="cp_title_visibility" name="cp_title_visibility">
			<option value="show" <?php echo ( ( $title_visibility == 'show' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Show page titles', 'commentpress-core' ); ?></option>
			<option value="hide" <?php echo ( ( $title_visibility == 'hide' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Hide page titles', 'commentpress-core' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'This setting can be overridden on individual entries.', 'commentpress-core' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="cp_page_meta_visibility"><?php esc_html_e( 'Page meta visibility', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<select id="cp_page_meta_visibility" name="cp_page_meta_visibility">
			<option value="show" <?php echo ( ( $page_meta_visibility == 'show' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Show page meta', 'commentpress-core' ); ?></option>
			<option value="hide" <?php echo ( ( $page_meta_visibility == 'hide' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Hide page meta', 'commentpress-core' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'This setting can be overridden on individual entries.', 'commentpress-core' ); ?></p>
	</td>
</tr>
