<?php
/**
 * Multisite Network Settings page "Title Page Content" metabox template.
 *
 * Handles markup for the Multisite Network Settings page "Title Page Content" metabox.
 *
 * @package CommentPress_Core
 */

?><!-- includes/commentpress-multisite/assets/templates/wordpress/metaboxes/metabox-network-settings-title-page.php -->
<p><?php esc_html_e( 'The following is the content of the Title Page for each new CommentPress site. Edit it if you want to show something else on the Title Page by default.', 'commentpress-core' ); ?></p>

<?php

/**
 * Fires at the top of the "Title Page Content" metabox.
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
 * Fires at the bottom of the "Title Page Content" metabox.
 *
 * @since 4.0
 */
do_action( 'commentpress/multisite/settings/network/metabox/title_page/after' );

?>
