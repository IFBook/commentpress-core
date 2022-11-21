<?php
/**
 * Formatter metabox template.
 *
 * Handles markup for the Formatter metabox on "Edit" screens.
 *
 * @package CommentPress_Core
 */

?>
<!-- includes/core/assets/templates/wordpress/metaboxes/metabox-formatter.php -->
<?php wp_nonce_field( $this->nonce_value, $this->nonce_name ); ?>

<div class="cp_post_type_override_wrapper">
	<p>
		<select id="<?php echo $this->element_select; ?>" name="<?php echo $this->element_select; ?>">
			<?php echo $type_options; ?>
		</select>
	</p>
</div>
