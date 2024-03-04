<?php
/**
 * "CommentPress Settings" Single Entry form element.
 *
 * Handles markup for the Single Entry form element in the "CommentPress Settings" Metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->parts_path; ?>part-entry-single-entry.php -->
<div class="<?php echo esc_attr( $this->key_show_title ); ?>_wrapper">
	<p><strong><label for="<?php echo esc_attr( $this->key_show_title ); ?>"><?php esc_html_e( 'Page Title Visibility', 'commentpress-core' ); ?></label></strong></p>
	<p>
		<select id="<?php echo esc_attr( $this->key_show_title ); ?>" name="<?php echo esc_attr( $this->key_show_title ); ?>">
			<option value="" <?php echo ( empty( $show_title ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Use default', 'commentpress-core' ); ?></option>
			<option value="show" <?php echo ( 'show' === $show_title ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Show title', 'commentpress-core' ); ?></option>
			<option value="hide" <?php echo ( 'hide' === $show_title ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Hide title', 'commentpress-core' ); ?></option>
		</select>
	</p>
</div>

<div class="<?php echo esc_attr( $this->key_show_meta ); ?>_wrapper">
	<p><strong><label for="<?php echo esc_attr( $this->key_show_meta ); ?>"><?php esc_html_e( 'Page Meta Visibility', 'commentpress-core' ); ?></label></strong></p>
	<p>
		<select id="<?php echo esc_attr( $this->key_show_meta ); ?>" name="<?php echo esc_attr( $this->key_show_meta ); ?>">
			<option value="" <?php echo ( empty( $show_meta ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Use default', 'commentpress-core' ); ?></option>
			<option value="show" <?php echo ( 'show' === $show_meta ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Show meta', 'commentpress-core' ); ?></option>
			<option value="hide" <?php echo ( 'hide' === $show_meta ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Hide meta', 'commentpress-core' ); ?></option>
	</select>
	</p>
</div>

<div class="<?php echo esc_attr( $this->key_para_num ); ?>_wrapper">
	<p><strong><label for="<?php echo esc_attr( $this->key_para_num ); ?>"><?php esc_html_e( 'Starting Paragraph Number', 'commentpress-core' ); ?></label></strong></p>
	<p>
		<input type="number" id="<?php echo esc_attr( $this->key_para_num ); ?>" name="<?php echo esc_attr( $this->key_para_num ); ?>" value="<?php echo esc_attr( $number ); ?>" />
	</p>
</div>
