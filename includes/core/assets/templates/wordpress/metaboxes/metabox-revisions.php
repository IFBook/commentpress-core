<?php
/**
 * Revisions metabox template.
 *
 * Handles markup for the Revisions metabox on "Edit" screens.
 *
 * @package CommentPress_Core
 */

?>
<!-- includes/core/assets/templates/wordpress/metaboxes/metabox-revisions.php -->
<?php wp_nonce_field( $this->nonce_value, $this->nonce_name ); ?>

<?php if ( ! empty( $newer_post_id ) ) : ?>

	<h4><?php esc_html_e( 'There is a newer version of this post', 'commentpress-core' ); ?></h4>

	<p><a href="<?php echo get_edit_post_link( $newer_post_id ); ?>"><?php esc_html_e( 'Edit newer version', 'commentpress-core' ); ?></a></p>

<?php else : ?>

	<div class="checkbox">
		<label for="<?php echo $this->element_checkbox; ?>">
			<input type="checkbox" value="1" id="<?php echo $this->element_checkbox; ?>" name="<?php echo $this->element_checkbox; ?>" /> <?php esc_html_e( 'Create new version', 'commentpress-core' ); ?>
		</label>
	</div>

<?php endif; ?>
