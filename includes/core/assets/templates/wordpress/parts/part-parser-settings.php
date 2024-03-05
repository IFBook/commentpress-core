<?php
/**
 * Site Settings screen Parser form element.
 *
 * Handles markup for the Parser form element on the Site Settings screen.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->parts_path; ?>part-parser-settings.php -->
<?php if ( ! empty( $capable_post_types ) ) : ?>
	<tr>
		<th scope="row">
			<label for="<?php echo esc_attr( $this->key_post_types_enabled ); ?>"><?php esc_html_e( 'Post Types on which CommentPress Core is enabled', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<p>
				<?php foreach ( $capable_post_types as $post_type_slug => $label ) : ?>
					<?php if ( ! in_array( $post_type_slug, $selected_types, true ) ) : ?>
						<input type="checkbox" class="settings-checkbox" id="<?php echo esc_attr( $this->key_post_types_enabled ); ?>_<?php echo esc_attr( $post_type_slug ); ?>" name="<?php echo esc_attr( $this->key_post_types_enabled ); ?>[]" value="<?php echo esc_attr( $post_type_slug ); ?>" checked="checked" />
					<?php else : ?>
						<input type="checkbox" class="settings-checkbox" id="<?php echo esc_attr( $this->key_post_types_enabled ); ?>_<?php echo esc_attr( $post_type_slug ); ?>" name="<?php echo esc_attr( $this->key_post_types_enabled ); ?>[]" value="<?php echo esc_attr( $post_type_slug ); ?>" />
					<?php endif; ?>
					<label class="commentpress_settings_label" for="<?php echo esc_attr( $this->key_post_types_enabled ); ?>_<?php echo esc_attr( $post_type_slug ); ?>"><?php echo esc_html( $label ); ?></label><br>
				<?php endforeach; ?>
			</p>
		</td>
	</tr>
<?php endif; ?>

<tr valign="top">
	<th scope="row">
		<label for="<?php echo esc_attr( $this->key_do_not_parse ); ?>"><?php esc_html_e( 'Disable CommentPress on Entries with no Comments', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<select id="<?php echo esc_attr( $this->key_do_not_parse ); ?>" name="<?php echo esc_attr( $this->key_do_not_parse ); ?>">
			<option value="y" <?php echo ( 'y' === $do_not_parse ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Yes', 'commentpress-core' ); ?></option>
			<option value="n" <?php echo ( 'n' === $do_not_parse ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'No', 'commentpress-core' ); ?></option>
		</select>
		<p class="description"><?php esc_html_e( 'When comments are closed on an entry and there are no comments on that entry, if this option is set to "Yes" then the content will not be parsed for paragraphs, lines or blocks. Comments will also not be parsed, meaning that the entry behaves the same as content which is not commentable. Default prior to 3.8.10 was "No" - all content was always parsed.', 'commentpress-core' ); ?></p>
		<p class="description"><?php esc_html_e( 'This setting can be overridden on individual entries.', 'commentpress-core' ); ?></p>
	</td>
</tr>
