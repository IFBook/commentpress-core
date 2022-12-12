<?php
/**
 * Multisite Network Settings Page "Welcome Page Content" metabox template.
 *
 * Handles markup for the Multisite Network Settings Page "Welcome Page Content" metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->metabox_path; ?>metabox-settings-network-title-page.php -->
<p><?php esc_html_e( 'The following is the content of the Title Page for each new CommentPress site. Edit it if you want to show something else on the Title Page by default.', 'commentpress-core' ); ?></p>

<?php

/**
 * Fires at the top of the "Welcome Page Content" metabox.
 *
 * @since 4.0
 */
do_action( 'commentpress/multisite/settings/network/metabox/title_page/before' );

// Call the editor.
wp_editor(
	$content,
	'cpmu_title_page_content',
	$settings = [
		'media_buttons' => false,
	]
);

/**
 * Fires at the bottom of the "Welcome Page Content" metabox.
 *
 * @since 4.0
 */
do_action( 'commentpress/multisite/settings/network/metabox/title_page/after' );

?>
