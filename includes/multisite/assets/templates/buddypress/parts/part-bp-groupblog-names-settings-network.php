<?php
/**
 * Form elements for the BuddyPress Groupblog Settings metabox.
 *
 * Handles markup for the form elements on the BuddyPress Groupblog Settings metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->parts_path; ?>part-bp-groupblog-names-settings-network.php -->
<tr valign="top">
	<th scope="row">
		<label for="<?php echo esc_attr( $this->key_scheme ); ?>"><?php echo esc_html_e( 'Default naming scheme for Group Blogs', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<select id="<?php echo esc_attr( $this->key_scheme ); ?>" name="<?php echo esc_attr( $this->key_scheme ); ?>">
			<?php foreach ( $groupblog_schemes as $scheme_slug => $scheme_title ) : ?>
				<option value="<?php echo esc_attr( $scheme_slug ); ?>" <?php selected( $current_scheme, $scheme_slug ); ?>><?php echo esc_html( $scheme_title ); ?></option>
			<?php endforeach; ?>
		</select>
		<p class="description"><?php printf( __( 'You can add additional translatable naming schemes for Group Blogs using the %s filter.', 'commentpress-core' ), '<code>commentpress/multisite/bp/groupblog/schemes</code>' ); ?></p>
	</td>
</tr>

<tr valign="top">
	<th scope="row">
		<label for="<?php echo esc_attr( $this->key_enabled ); ?>"><?php echo esc_html_e( 'Set a custom naming scheme for Group Blogs', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<input id="<?php echo esc_attr( $this->key_enabled ); ?>" name="<?php echo esc_attr( $this->key_enabled ); ?>" value="1" type="checkbox"<?php echo ( $enabled == 1 ? ' checked="checked"' : '' ); ?> />
		<p class="description"><?php echo esc_html_e( 'Please note: if you set a custom name for Group Blogs, it will not be translatable.', 'commentpress-core' ); ?></p>
	</td>
</tr>

<tr valign="top" class="nomenclature_name"<?php echo ( $enabled == 1 ? '' : ' style="display: none;"' ); ?>>
	<th scope="row">
		<label for="<?php echo esc_attr( $this->key_singular ); ?>"><?php echo esc_html_e( 'Singular name for Group Blogs', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<input id="<?php echo esc_attr( $this->key_singular ); ?>" name="<?php echo esc_attr( $this->key_singular ); ?>" value="<?php echo esc_attr( $singular ); ?>" type="text" />
	</td>
</tr>

<tr valign="top" class="nomenclature_plural"<?php echo ( $enabled == 1 ? '' : ' style="display: none;"' ); ?>>
	<th scope="row">
		<label for="<?php echo esc_attr( $this->key_plural ); ?>"><?php echo esc_html_e( 'Plural name for Group Blogs', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<input id="<?php echo esc_attr( $this->key_plural ); ?>" name="<?php echo esc_attr( $this->key_plural ); ?>" value="<?php echo esc_attr( $plural ); ?>" type="text" />
	</td>
</tr>

<tr valign="top" class="nomenclature_slug"<?php echo ( $enabled == 1 ? '' : ' style="display: none;"' ); ?>>
	<th scope="row">
		<label for="<?php echo esc_attr( $this->key_slug ); ?>"><?php echo esc_html_e( 'Slug for Group Blogs', 'commentpress-core' ); ?></label>
	</th>
	<td>
		<input id="<?php echo esc_attr( $this->key_slug ); ?>" name="<?php echo esc_attr( $this->key_slug ); ?>" value="<?php echo esc_attr( $slug ); ?>" type="text" />
	</td>
</tr>
