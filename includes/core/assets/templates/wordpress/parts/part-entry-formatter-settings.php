<?php
/**
 * Site Settings screen Formatter form element.
 *
 * Handles markup for the Formatter form element on the Site Settings screen.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo esc_html( $this->parts_path ); ?>part-entry-formatter-settings.php -->
<tr valign="top">
	<th scope="row">
		<label for="<?php echo esc_attr( $this->key_formatter ); ?>"><?php echo esc_html( $text_format_title ); ?></label>
	</th>
	<td>
		<select id="<?php echo esc_attr( $this->key_formatter ); ?>" name="<?php echo esc_attr( $this->key_formatter ); ?>">
			<?php /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
			<?php echo $text_format_options; ?>
		</select>
		<p class="description"><?php esc_html_e( 'Choose "Prose" if you want content to be parsed by paragraphs and lists. Choose "Poetry" if you want content to be parsed by lines. If you insert a Comment Block into the content, then it will be parsed by block, regardless of this setting.', 'commentpress-core' ); ?></p>
		<p class="description"><?php esc_html_e( 'This setting can be overridden on individual entries.', 'commentpress-core' ); ?></p>
	</td>
</tr>
