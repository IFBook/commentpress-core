<?php
/**
 * "CommentPress Settings" Document form element.
 *
 * Handles markup for the Document form element in the "CommentPress Settings" Metabox.
 *
 * @package CommentPress_Core
 */

?>
<!-- <?php echo $this->parts_path; ?>part-entry-document-entry.php -->
<div class="cp_title_visibility_wrapper">
	<p><strong><label for="cp_title_visibility"><?php esc_html_e( 'Page Title Visibility', 'commentpress-core' ); ?></label></strong></p>
	<p>
		<select id="cp_title_visibility" name="cp_title_visibility">
			<option value="" <?php echo ( empty( $title_visibility ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Use default', 'commentpress-core' ); ?></option>
			<option value="show" <?php echo ( $title_visibility == 'show' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Show page title', 'commentpress-core' ); ?></option>
			<option value="hide" <?php echo ( $title_visibility == 'hide' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Hide page title', 'commentpress-core' ); ?></option>
		</select>
	</p>
</div>

<div class="cp_page_meta_visibility_wrapper">
	<p><strong><label for="cp_page_meta_visibility"><?php esc_html_e( 'Page Meta Visibility', 'commentpress-core' ); ?></label></strong></p>
	<p>
		<select id="cp_page_meta_visibility" name="cp_page_meta_visibility">
			<option value="" <?php echo ( empty( $meta_visibility ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Use default', 'commentpress-core' ); ?></option>
			<option value="show" <?php echo ( $meta_visibility == 'show' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Show page meta', 'commentpress-core' ); ?></option>
			<option value="hide" <?php echo ( $meta_visibility == 'hide' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Hide page meta', 'commentpress-core' ); ?></option>
	</select>
	</p>
</div>

<div class="cp_starting_para_number_wrapper">
	<p><strong><label for="cp_starting_para_number"><?php esc_html_e( 'Starting Paragraph Number', 'commentpress-core' ); ?></label></strong></p>
	<p>
		<input type="number" id="cp_starting_para_number" name="cp_starting_para_number" value="<?php echo esc_attr( $number ); ?>" />
	</p>
</div>

<?php if ( ! empty( $format ) ) : ?>
	<div class="cp_number_format_wrapper">
		<p><strong><label for="cp_number_format"><?php esc_html_e( 'Page Number Format', 'commentpress-core' ); ?></label></strong></p>
		<p>
			<select id="cp_number_format" name="cp_number_format">
				<option value="arabic" <?php echo ( $format == 'arabic' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Arabic numerals', 'commentpress-core' ); ?></option>
				<option value="roman" <?php echo ( $format == 'roman' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Roman numerals', 'commentpress-core' ); ?></option>
			</select>
		</p>
	</div>
<?php endif; ?>

<?php if ( ! empty( $layout ) ) : ?>
	<div class="cp_page_layout_wrapper">
		<p><strong><label for="cp_page_layout"><?php esc_html_e( 'Page Layout', 'commentpress-core' ); ?></label></strong></p>
		<p>
			<select id="cp_page_layout" name="cp_page_layout">
				<option value="text" <?php echo ( $layout == 'text' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Standard', 'commentpress-core' ); ?></option>
				<option value="wide" <?php echo ( $layout == 'wide' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Wide', 'commentpress-core' ); ?></option>
			</select>
		</p>
	</div>
<?php endif; ?>
