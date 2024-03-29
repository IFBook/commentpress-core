<?php
/**
 * Multisite Network Settings Page "BuddyPress Groupblog Settings" metabox template.
 *
 * Handles markup for the Multisite Network Settings Page "BuddyPress Groupblog Settings" metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo esc_html( $this->metabox_path ); ?>metabox-settings-network-bp-groupblog.php -->
<table class="form-table">

	<?php

	/**
	 * Fires at the top of the "BuddyPress Groupblog Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/network/metabox/bp/groupblog/before' );

	?>

	<tr valign="top">
		<th scope="row">
			<label for="<?php echo esc_attr( $this->key_forced ); ?>"><?php esc_html_e( 'Make all new Group Blogs CommentPress-enabled', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="<?php echo esc_attr( $this->key_forced ); ?>" name="<?php echo esc_attr( $this->key_forced ); ?>" value="1" type="checkbox"<?php checked( true, $force_commentpress ); ?> />
		</td>
	</tr>

	<?php if ( ! empty( $groupblog_themes ) ) : ?>
		<tr valign="top">
			<th scope="row">
				<label for="<?php echo esc_attr( $this->key_theme ); ?>"><?php esc_html_e( 'Default theme for CommentPress Group Blogs', 'commentpress-core' ); ?></label>
			</th>
			<td>
				<select id="<?php echo esc_attr( $this->key_theme ); ?>" name="<?php echo esc_attr( $this->key_theme ); ?>">
					<?php foreach ( $groupblog_themes as $theme_slug => $theme_title ) : ?>
						<option value="<?php echo esc_attr( $theme_slug ); ?>" <?php selected( $current_theme, $theme_slug ); ?>><?php echo esc_html( $theme_title ); ?></option>
					<?php endforeach; ?>
				</select>
				<p class="description">
					<?php

					printf(
						/* translators: 1: The first tag name, 2: The second tag name. */
						esc_html__( 'For themes or child themes to be recognized as eligible, they must be tagged with both the %1$s and %2$s tags.', 'commentpress-core' ),
						'<code>commentpress</code>',
						'<code>groupblog</code>'
					);

					?>
				</p>
			</td>
		</tr>
	<?php endif; ?>

	<tr valign="top">
		<th scope="row">
			<label for="<?php echo esc_attr( $this->key_privacy ); ?>"><?php esc_html_e( 'Private Groups must have Private Group Blogs', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="<?php echo esc_attr( $this->key_privacy ); ?>" name="<?php echo esc_attr( $this->key_privacy ); ?>" value="1" type="checkbox"<?php checked( true, $privacy ); ?> />
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="<?php echo esc_attr( $this->key_comment_login ); ?>"><?php esc_html_e( 'Require user login to post comments on Group Blogs', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="<?php echo esc_attr( $this->key_comment_login ); ?>" name="<?php echo esc_attr( $this->key_comment_login ); ?>" value="1" type="checkbox"<?php checked( true, $comment_login ); ?> />
			<p class="description"><?php esc_html_e( 'The initial privacy setting when Group Blogs are created.', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<?php

	/**
	 * Fires at the bottom of the "BuddyPress Groupblog Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/network/metabox/bp/groupblog/after' );

	?>

</table>
