<?php
/**
 * Multisite Network Settings template.
 *
 * Handles markup for the Multisite Network Settings screen.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->page_path; ?>page-settings-network.php -->
<div class="wrap">

	<h1><?php esc_html_e( 'CommentPress Network', 'commentpress-core' ); ?></h1>

	<hr />

	<form method="post" id="<?php echo esc_attr( $this->form_id ); ?>" action="<?php echo $this->page_settings_submit_url_get(); ?>">

		<?php wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false ); ?>
		<?php wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false ); ?>
		<?php wp_nonce_field( $this->nonce_value, $this->nonce_name ); ?>

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
