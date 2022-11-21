<?php
/**
 * Site Settings Page "Theme Customisation" metabox template.
 *
 * Handles markup for the Site Settings Page "Theme Customisation" metabox.
 *
 * @package CommentPress_Core
 */

?>
<!-- includes/core/assets/templates/wordpress/metaboxes/metabox-settings-site-theme.php -->
<p><?php _e( 'You can set a custom background colour in <em>Appearance &#8594; Background</em>.<br />You can also set a custom header image and header text colour in <em>Appearance &#8594; Header</em>.<br />Below are extra options for changing how the theme functions.', 'commentpress-core' ); ?></p>

<table class="form-table">

	<tr valign="top">
		<th scope="row">
			<label for="cp_js_scroll_speed"><?php esc_html_e( 'Scroll speed', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input type="text" id="cp_js_scroll_speed" name="cp_js_scroll_speed" value="<?php echo $scroll_speed; ?>" class="small-text" /> <?php esc_html_e( 'milliseconds', 'commentpress-core' ); ?>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="cp_min_page_width"><?php esc_html_e( 'Minimum page width', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input type="text" id="cp_min_page_width" name="cp_min_page_width" value="<?php echo $min_page_width; ?>" class="small-text" /> <?php esc_html_e( 'pixels', 'commentpress-core' ); ?>
		</td>
	</tr>

	<?php if ( ! apply_filters( 'commentpress_hide_sidebar_option', false ) ) : ?>
		<tr valign="top">
			<th scope="row">
				<label for="cp_sidebar_default"><?php esc_html_e( 'Which sidebar do you want to be active by default?', 'commentpress-core' ); ?></label>
			</th>
			<td>
				<select id="cp_sidebar_default" name="cp_sidebar_default">
					<option value="toc" <?php echo ( ( $sidebar_default == 'contents' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Contents', 'commentpress-core' ); ?></option>
					<option value="activity" <?php echo ( ( $sidebar_default == 'activity' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Activity', 'commentpress-core' ); ?></option>
					<option value="comments" <?php echo ( ( $sidebar_default == 'comments' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Comments', 'commentpress-core' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'This setting can be overridden on individual entries.', 'commentpress-core' ); ?></p>
			</td>
		</tr>
	<?php endif; ?>

	<?php

	/**
	 * Allow others to add to this section.
	 *
	 * @since 3.4
	 *
	 * @param str Empty by default so that it can be populated.
	 */
	echo apply_filters( 'commentpress_theme_customisation_options', '' );

	?>

</table>
