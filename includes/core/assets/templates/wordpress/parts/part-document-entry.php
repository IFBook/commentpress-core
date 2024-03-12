<?php
/**
 * "CommentPress Settings" Document form element.
 *
 * Handles markup for the Document form element in the "CommentPress Settings" Metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo esc_html( $this->parts_path ); ?>part-document-entry.php -->
<?php if ( ! empty( $format ) ) : ?>
	<div class="<?php echo esc_attr( $this->key_number_format ); ?>_wrapper">
		<p><strong><label for="<?php echo esc_attr( $this->key_number_format ); ?>"><?php esc_html_e( 'Page Number Format', 'commentpress-core' ); ?></label></strong></p>
		<p>
			<select id="<?php echo esc_attr( $this->key_number_format ); ?>" name="<?php echo esc_attr( $this->key_number_format ); ?>">
				<option value="arabic" <?php selected( $format, 'arabic' ); ?>><?php esc_html_e( 'Arabic numerals', 'commentpress-core' ); ?></option>
				<option value="roman" <?php selected( $format, 'roman' ); ?>><?php esc_html_e( 'Roman numerals', 'commentpress-core' ); ?></option>
			</select>
		</p>
	</div>
<?php endif; ?>

<?php if ( ! empty( $layout ) ) : ?>
	<div class="<?php echo esc_attr( $this->key_layout ); ?>_wrapper">
		<p><strong><label for="<?php echo esc_attr( $this->key_layout ); ?>"><?php esc_html_e( 'Page Layout', 'commentpress-core' ); ?></label></strong></p>
		<p>
			<select id="<?php echo esc_attr( $this->key_layout ); ?>" name="<?php echo esc_attr( $this->key_layout ); ?>">
				<option value="text" <?php selected( $layout, 'text' ); ?>><?php esc_html_e( 'Standard', 'commentpress-core' ); ?></option>
				<option value="wide" <?php selected( $layout, 'wide' ); ?>><?php esc_html_e( 'Wide', 'commentpress-core' ); ?></option>
			</select>
		</p>
	</div>
<?php endif; ?>
