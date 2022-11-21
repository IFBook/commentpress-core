<?php
/**
 * Page Settings metabox template.
 *
 * Handles markup for the Page Settings metabox on "Edit Page" screens.
 *
 * @package CommentPress_Core
 */

?>
<!-- includes/core/assets/templates/wordpress/metaboxes/metabox-settings-page.php -->
<?php wp_nonce_field( $this->nonce_value, $this->nonce_name ); ?>

<div class="cp_sidebar_default_wrapper">

	<p><strong><label for="cp_sidebar_default"><?php esc_html_e( 'Default Sidebar', 'commentpress-core' ); ?></label></strong></p>

	<p>
		<select id="cp_sidebar_default" name="cp_sidebar_default">
			<option value="toc" <?php echo ( $sidebar === 'toc' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Contents', 'commentpress-core' ); ?></option>
			<option value="activity" <?php echo ( $sidebar === 'activity' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Activity', 'commentpress-core' ); ?></option>
			<option value="comments" <?php echo ( $sidebar === 'comments' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Comments', 'commentpress-core' ); ?></option>
		</select>
	</p>

</div>
