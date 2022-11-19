<?php
/**
 * Multisite Network Settings Page "BuddyPress & Group Blog Settings" metabox template.
 *
 * Handles markup for the Multisite Network Settings Page "BuddyPress & Group Blog Settings" metabox.
 *
 * @package CommentPress_Core
 */

?>
<!-- includes/commentpress-multisite/assets/templates/wordpress/metaboxes/metabox-network-settings-buddypress.php -->
<table class="form-table">

	<?php

	/**
	 * Fires at the top of the "BuddyPress & Group Blog Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/network/metabox/buddypress/before' );

	?>

	<tr valign="top">
		<th scope="row">
			<label for="cpmu_bp_reset"><?php esc_html_e( 'Reset BuddyPress settings', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="cpmu_bp_reset" name="cpmu_bp_reset" value="1" type="checkbox" />
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="cpmu_bp_force_commentpress"><?php esc_html_e( 'Make all new Group Blogs CommentPress-enabled', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="cpmu_bp_force_commentpress" name="cpmu_bp_force_commentpress" value="1" type="checkbox"<?php echo ( $bp_force_commentpress == '1' ? ' checked="checked"' : '' ); ?> />
		</td>
	</tr>

	<?php echo $this->get_commentpress_themes(); ?>

	<tr valign="top">
		<th scope="row">
			<label for="cpmu_bp_groupblog_privacy"><?php esc_html_e( 'Private Groups must have Private Group Blogs', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="cpmu_bp_groupblog_privacy" name="cpmu_bp_groupblog_privacy" value="1" type="checkbox"<?php echo ( $bp_groupblog_privacy == '1' ? ' checked="checked"' : '' ); ?> />
		</td>
	</tr>

	<tr valign="top">
		<th scope="row">
			<label for="cpmu_bp_require_comment_registration"><?php esc_html_e( 'Require user login to post comments on Group Blogs', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="cpmu_bp_require_comment_registration" name="cpmu_bp_require_comment_registration" value="1" type="checkbox"<?php echo ( $bp_require_comment_registration == '1' ? ' checked="checked"' : '' ); ?> />
		</td>
	</tr>

	<?php echo apply_filters( 'cpmu_network_buddypress_options_form', '' ); ?>

	<?php

	/**
	 * Fires at the bottom of the "BuddyPress & Group Blog Settings" metabox.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/settings/network/metabox/buddypress/after' );

	?>

</table>
