<?php
/**
 * "CommentPress Settings" Formatter form element.
 *
 * Handles markup for the Formatter form element in the "CommentPress Settings" Metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo esc_html( $this->parts_path ); ?>part-entry-formatter-entry.php -->
<div class="<?php echo esc_attr( $this->element_select ); ?>_wrapper">
	<p><strong><label for="<?php echo esc_attr( $this->element_select ); ?>"><?php esc_html_e( 'Text Format', 'commentpress-core' ); ?></label></strong></p>
	<p>
		<select id="<?php echo esc_attr( $this->element_select ); ?>" name="<?php echo esc_attr( $this->element_select ); ?>">
			<?php /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
			<?php echo $text_format_options; ?>
		</select>
	</p>
</div>
