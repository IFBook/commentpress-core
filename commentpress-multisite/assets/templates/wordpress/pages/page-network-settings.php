<?php
/**
 * Multisite Network Settings template.
 *
 * Handles markup for the Multisite Network Settings page.
 *
 * @package CommentPress_Core
 */

?><!-- commentpress-multisite/assets/templates/wordpress/pages/page-network-settings.php -->
<div class="wrap">

	<h1><?php esc_html_e( 'CommentPress Network', 'commentpress-core' ); ?></h1>

	<hr />

	<form method="post" id="commentpress_core_settings_form" action="<?php echo $this->page_settings_submit_url_get(); ?>">

		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field( 'cpmu_admin_action', 'cpmu_nonce' ); ?>

		<div id="poststuff">

			<div id="post-body" class="metabox-holder columns-<?php echo $columns; ?>">

				<!--<div id="post-body-content">
				</div>--><!-- #post-body-content -->

				<div id="postbox-container-1" class="postbox-container">
					<?php do_meta_boxes( $screen->id, 'side', null ); ?>
				</div>

				<div id="postbox-container-2" class="postbox-container">
					<?php do_meta_boxes( $screen->id, 'normal', null ); ?>
					<?php do_meta_boxes( $screen->id, 'advanced', null ); ?>
				</div>

			</div><!-- #post-body -->
			<br class="clear">

		</div><!-- #poststuff -->

	</form>

</div><!-- /.wrap -->
