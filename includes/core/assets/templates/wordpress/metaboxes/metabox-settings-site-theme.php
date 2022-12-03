<?php
/**
 * Site Settings screen "Theme Customisation" metabox template.
 *
 * Handles markup for the Site Settings screen "Theme Customisation" metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->metabox_path; ?>metabox-settings-site-theme.php -->
<p><?php _e( 'You can set a custom background colour in <em>Appearance &#8594; Background</em>.<br />You can also set a custom header image and header text colour in <em>Appearance &#8594; Header</em>.<br />Below are extra options for changing how the theme functions.', 'commentpress-core' ); ?></p>

<table class="form-table">

	<?php

	/**
	 * Fires at the top of the "Theme Customisation" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/core/settings/site/metabox/theme/before' );

	?>

	<tr valign="top">
		<th scope="row">
			<label for="cp_excerpt_length"><?php esc_html_e( 'Blog excerpt length', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input type="text" id="cp_excerpt_length" name="cp_excerpt_length" value="<?php echo esc_attr( $excerpt_length ); ?>" class="small-text" /> <?php esc_html_e( 'words', 'commentpress-core' ); ?>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="cp_js_scroll_speed"><?php esc_html_e( 'Scroll speed', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input type="text" id="cp_js_scroll_speed" name="cp_js_scroll_speed" value="<?php echo $scroll_speed; ?>" class="small-text" /> <?php esc_html_e( 'milliseconds', 'commentpress-core' ); ?>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="cp_min_page_width"><?php esc_html_e( 'Minimum page width', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input type="text" id="cp_min_page_width" name="cp_min_page_width" value="<?php echo $min_page_width; ?>" class="small-text" /> <?php esc_html_e( 'pixels', 'commentpress-core' ); ?>
		</td>
	</tr>

	<?php

	/**
	 * Allow others to add to the "Theme Customisation" metabox.
	 *
	 * @since 3.4
	 *
	 * @param str Empty by default so that it can be populated.
	 */
	echo apply_filters_deprecated( 'commentpress_theme_customisation_options', [ '' ], '4.0' );

	?>

	<?php

	/**
	 * Fires at the bottom of the "Theme Customisation" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/core/settings/site/metabox/theme/after' );

	?>

</table>
