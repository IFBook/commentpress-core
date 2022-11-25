<?php
/**
 * "CommentPress Settings" Formatter form element.
 *
 * Handles markup for the Formatter form element in the "CommentPress Settings" Metabox.
 *
 * @package CommentPress_Core
 */

?>
<!-- includes/core/assets/templates/wordpress/partials/partial-entry-formatter-entry.php -->
<div class="cp_post_type_override_wrapper">
	<p><strong><label for="cp_title_visibility"><?php esc_html_e( 'Text Format', 'commentpress-core' ); ?></label></strong></p>
	<p>
		<select id="<?php echo $this->element_select; ?>" name="<?php echo $this->element_select; ?>">
			<?php echo $type_options; ?>
		</select>
	</p>
</div>
