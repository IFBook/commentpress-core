<?php
/**
 * Site Settings screen Formatter form element.
 *
 * Handles markup for the Formatter form element on the Site Settings screen.
 *
 * @package CommentPress_Core
 */

?>
<!-- includes/core/assets/templates/wordpress/partials/partial-entry-formatter-settings.php -->
<tr valign="top">
	<th scope="row">
		<label for="<?php echo $this->option_formatter; ?>"><?php echo esc_html( $type_title ); ?></label>
	</th>
	<td>
		<select id="<?php echo $this->option_formatter; ?>" name="<?php echo $this->option_formatter; ?>">
			<?php echo $type_options; ?>
		</select>
		<p class="description"><?php esc_html_e( 'This setting can be overridden on individual entries.', 'commentpress-core' ); ?></p>
		<p class="description"><?php esc_html_e( 'Choose "Prose" if you want content to be parsed by paragraphs and lists. Choose "Poetry" if you want content to be parsed by lines. If you insert a Comment Block into the content, then it will be parsed by block, regardless of this setting.', 'commentpress-core' ); ?></p>
	</td>
</tr>
