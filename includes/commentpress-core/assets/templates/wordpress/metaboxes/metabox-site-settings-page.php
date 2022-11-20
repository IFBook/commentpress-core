<?php
/**
 * Site Settings Page "Page Display Options" metabox template.
 *
 * Handles markup for the Site Settings Page "Page Display Options" metabox.
 *
 * @package CommentPress_Core
 */

?>
<!-- includes/commentpress-core/assets/templates/wordpress/metaboxes/metabox-site-settings-page.php -->
<table class="form-table">

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

	<tr valign="top">
		<th scope="row">
			<label for="cp_page_nav_enabled"><?php esc_html_e( 'Enable automatic page navigation', 'commentpress-core' ); ?></label>
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
			<label for="cp_title_visibility"><?php esc_html_e( 'Default page title visibility', 'commentpress-core' ); ?></label>
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
			<label for="cp_page_meta_visibility"><?php esc_html_e( 'Default page meta visibility', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<select id="cp_page_meta_visibility" name="cp_page_meta_visibility">
				<option value="show" <?php echo ( ( $page_meta_visibility == 'show' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Show page meta', 'commentpress-core' ); ?></option>
				<option value="hide" <?php echo ( ( $page_meta_visibility == 'hide' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Hide page meta', 'commentpress-core' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'This setting can be overridden on individual entries.', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="cp_textblock_meta"><?php esc_html_e( 'Show paragraph meta', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<select id="cp_textblock_meta" name="cp_textblock_meta">
				<option value="y" <?php echo ( ( $textblock_meta == 'y' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Always', 'commentpress-core' ); ?></option>
				<option value="n" <?php echo ( ( $textblock_meta == 'n' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'On rollover', 'commentpress-core' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'This refers to the Number before the paragraph and the Comment Icon after the paragraph.', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="cp_excerpt_length"><?php esc_html_e( 'Blog excerpt length', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input type="text" id="cp_excerpt_length" name="cp_excerpt_length" value="<?php echo esc_attr( $excerpt_length ); ?>" class="small-text" /> <?php esc_html_e( 'words', 'commentpress-core' ); ?>
		</td>
	</tr>

</table>
