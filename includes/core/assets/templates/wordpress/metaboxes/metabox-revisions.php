<?php
/**
 * Revisions metabox template.
 *
 * Handles markup for the Revisions metabox on "Edit" screens.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo esc_html( $this->metabox_path ); ?>metabox-revisions.php -->
<?php wp_nonce_field( $this->nonce_action, $this->nonce_field ); ?>

<?php if ( ! empty( $newer_post_id ) ) : ?>

	<h4><?php esc_html_e( 'There is a newer version of this post', 'commentpress-core' ); ?></h4>

	<p><a href="<?php echo esc_url( get_edit_post_link( $newer_post_id ) ); ?>"><?php esc_html_e( 'Edit newer version', 'commentpress-core' ); ?></a></p>

<?php else : ?>

	<div class="checkbox">
		<label for="<?php echo esc_attr( $this->element_checkbox ); ?>">
			<input type="checkbox" value="1" id="<?php echo esc_attr( $this->element_checkbox ); ?>" name="<?php echo esc_attr( $this->element_checkbox ); ?>" /> <?php esc_html_e( 'Create new version', 'commentpress-core' ); ?>
		</label>
	</div>

<?php endif; ?>
