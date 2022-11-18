<?php
/**
 * Site Settings Page "General Settings" metabox template.
 *
 * Handles markup for the Site Settings Page "General Settings" metabox.
 *
 * @package CommentPress_Core
 */

?><!-- includes/commentpress-core/assets/templates/wordpress/metaboxes/metabox-site-settings-general.php -->
<table class="form-table">

	<?php

	/**
	 * Fires at the top of the "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/core/settings/site/metabox/general/before' );

	?>

	<tr>
		<th scope="row">
			<label for="cp_reset"><?php esc_html_e( 'Reset options to plugin defaults', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="cp_reset" name="cp_reset" value="1" type="checkbox" />
		</td>
	</tr>

	<tr>
		<th scope="row">
			<label for="cp_post_types_enabled"><?php esc_html_e( 'Post Types on which CommentPress Core is enabled', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<?php if ( ! empty( $capable_post_types ) ) : ?>
				<p>
					<?php foreach ( $capable_post_types as $post_type => $label ) : ?>
						<?php if ( ! in_array( $post_type, $selected_types ) ) : ?>
							<input type="checkbox" class="settings-checkbox" name="cp_post_types_enabled[]" value="<?php echo esc_attr( $post_type ); ?>" checked="checked" />
						<?php else : ?>
							<input type="checkbox" class="settings-checkbox" name="cp_post_types_enabled[]" value="<?php echo esc_attr( $post_type ); ?>" />
						<?php endif; ?>
						 <label class="commentpress_settings_label" for="cp_post_types_enabled"><?php echo esc_html( $label ); ?></label><br>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
		</td>
	</tr>

	<?php

	// TODO: Make this an action.
	echo $this->core->display->get_optional_options();

	?>

	<tr valign="top">
		<th scope="row">
			<label for="cp_do_not_parse"><?php esc_html_e( 'Disable CommentPress on entries with no comments', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<select id="cp_do_not_parse" name="cp_do_not_parse">
				<option value="y" <?php echo ( $do_not_parse == 'y' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Yes', 'commentpress-core' ); ?></option>
				<option value="n" <?php echo ( $do_not_parse == 'n' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'No', 'commentpress-core' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'This setting can be overridden on individual entries.', 'commentpress-core' ); ?></p>
			<p class="description"><?php esc_html_e( 'Note: when comments are closed on an entry and there are no comments on that entry, if this option is set to "Yes" then the content will not be parsed for paragraphs, lines or blocks. Comments will also not be parsed, meaning that the entry behaves the same as content which is not commentable. Default prior to 3.8.10 was "No" - all content was always parsed.', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<?php

	/**
	 * Fires at the bottom of the "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/core/settings/site/metabox/general/after' );

	?>

</table>
