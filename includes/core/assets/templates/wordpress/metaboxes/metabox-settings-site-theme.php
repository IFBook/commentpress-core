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
<!-- <?php echo esc_html( $this->metabox_path ); ?>metabox-settings-site-theme.php -->
<p>
	<?php
	echo sprintf(
		/* translators: %s: The trail to the background screen. */
		esc_html__( 'You can set a custom background color in %s.', 'commentpress-core' ),
		'<em>' . esc_html__( 'Appearance &#8594; Background', 'commentpress-core' ) . '</em>'
	);
	?>
	<br>
	<?php
	echo sprintf(
		/* translators: %s: The trail to the header screen. */
		esc_html__( 'You can also set a custom header image and header text color in %s.', 'commentpress-core' ),
		'<em>' . esc_html__( 'Appearance &#8594; Header', 'commentpress-core' ) . '</em>'
	);
	?>
	<br>
	<?php esc_html_e( 'Below are additional options for changing how the theme functions.', 'commentpress-core' ); ?><br>
</p>

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
			<label for="<?php echo esc_attr( $this->key_featured_images ); ?>"><?php esc_html_e( 'Featured Images', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<select id="<?php echo esc_attr( $this->key_featured_images ); ?>" name="<?php echo esc_attr( $this->key_featured_images ); ?>">
				<option value="y" <?php selected( $featured_images, 'y' ); ?>><?php esc_html_e( 'Enabled', 'commentpress-core' ); ?></option>
				<option value="n" <?php selected( $featured_images, 'n' ); ?>><?php esc_html_e( 'Disabled', 'commentpress-core' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'CommentPress is most commonly used for text-based content, however some sites benefit from additional graphics and illustration. Enable Feature Images if this Site would benefit from them.', 'commentpress-core' ); ?></p>
			<p class="description"><?php esc_html_e( 'If you have already implemented this in a child theme, you should choose "Disabled".', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="<?php echo esc_attr( $this->key_textblock_meta ); ?>"><?php esc_html_e( 'Show textblock meta', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<select id="<?php echo esc_attr( $this->key_textblock_meta ); ?>" name="<?php echo esc_attr( $this->key_textblock_meta ); ?>">
				<option value="y" <?php selected( $textblock_meta, 'y' ); ?>><?php esc_html_e( 'Always', 'commentpress-core' ); ?></option>
				<option value="n" <?php selected( $textblock_meta, 'n' ); ?>><?php esc_html_e( 'On rollover', 'commentpress-core' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'This controls the display of the number to the left and the comment icon to the right of each paragraph, line or block that can be commented on.', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="<?php echo esc_attr( $this->key_excerpt_length ); ?>"><?php esc_html_e( 'Blog excerpt length', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input type="text" id="<?php echo esc_attr( $this->key_excerpt_length ); ?>" name="<?php echo esc_attr( $this->key_excerpt_length ); ?>" value="<?php echo esc_attr( $excerpt_length ); ?>" class="small-text" /> <?php esc_html_e( 'words', 'commentpress-core' ); ?>
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="<?php echo esc_attr( $this->key_scroll_speed ); ?>"><?php esc_html_e( 'Scroll speed', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input type="text" id="<?php echo esc_attr( $this->key_scroll_speed ); ?>" name="<?php echo esc_attr( $this->key_scroll_speed ); ?>" value="<?php echo esc_attr( $scroll_speed ); ?>" class="small-text" /> <?php esc_html_e( 'milliseconds', 'commentpress-core' ); ?>
			<p class="description"><?php esc_html_e( 'Modifies the speed of scrolling when actions like clicking or tapping on a paragraph are performed.', 'commentpress-core' ); ?></p>
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
	echo apply_filters_deprecated( 'commentpress_theme_customisation_options', [ '' ], '4.0' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

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
