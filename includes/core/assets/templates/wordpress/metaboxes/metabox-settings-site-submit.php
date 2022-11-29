<?php
/**
 * Site Settings screen "Submit" metabox template.
 *
 * Handles markup for the Site Settings screen "Submit" metabox.
 *
 * @package CommentPress_Core
 */

?>
<!-- <?php echo $this->metabox_path; ?>metabox-settings-site-submit.php -->
<div class="submitbox">
	<div id="minor-publishing">
		<div id="misc-publishing-actions">
			<div class="misc-pub-section">
				<span><?php esc_html_e( 'Save your settings here.', 'commentpress-core' ); ?></span>
			</div>
		</div>
		<div class="clear"></div>
	</div>

	<div id="major-publishing-actions">
		<div id="publishing-action">
			<?php submit_button( esc_html__( 'Update', 'commentpress-core' ), 'primary', 'commentpress_submit', false ); ?>
			<input type="hidden" name="action" value="update" />
		</div>
		<div class="clear"></div>
	</div>
</div>
