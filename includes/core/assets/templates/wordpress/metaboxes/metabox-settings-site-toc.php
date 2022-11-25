<?php
/**
 * Site Settings Page "Table of Contents" metabox template.
 *
 * Handles markup for the Site Settings Page "Table of Contents" metabox.
 *
 * @package CommentPress_Core
 */

?>
<!-- includes/core/assets/templates/wordpress/metaboxes/metabox-settings-site-toc.php -->
<p>
	<?php esc_html_e( 'Choose how you want your Table of Contents to appear and function.', 'commentpress-core' ); ?><br />
	<?php

	printf(
		/* translators: 1: The opening strong tag, 2: The closing strong tag. */
		__( '%1$sNOTE!%2$s When Chapters are Pages, the TOC will always show Sub-Pages, since collapsing the TOC makes no sense in that situation.', 'commentpress-core' ),
		'<strong style="color: red;">',
		'</strong>'
	);

	?>
</p>

<table class="form-table">

	<tr valign="top" class="show_posts_or_pages">
		<th scope="row">
			<label for="cp_show_posts_or_pages_in_toc"><?php esc_html_e( 'Table of Contents contains', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<select id="cp_show_posts_or_pages_in_toc" name="cp_show_posts_or_pages_in_toc">
				<option value="post" <?php echo ( ( $show_posts_or_pages_in_toc == 'post' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Posts', 'commentpress-core' ); ?></option>
				<option value="page" <?php echo ( ( $show_posts_or_pages_in_toc == 'page' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Pages', 'commentpress-core' ); ?></option>
			</select>
		</td>
	</tr>

	<?php if ( $show_posts_or_pages_in_toc == 'page' ) : ?>
		<tr valign="top" class="chapter_is_page">
			<th scope="row">
				<label for="cp_toc_chapter_is_page"><?php esc_html_e( 'Chapters are', 'commentpress-core' ); ?></label>
			</th>
			<td>
				<select id="cp_toc_chapter_is_page" name="cp_toc_chapter_is_page">
					<option value="1" <?php echo ( ( $toc_chapter_is_page == '1' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Pages', 'commentpress-core' ); ?></option>
					<option value="0" <?php echo ( ( $toc_chapter_is_page == '0' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Headings', 'commentpress-core' ); ?></option>
				</select>
			</td>
		</tr>
	<?php endif; ?>

	<?php if ( $show_posts_or_pages_in_toc == 'page' && $toc_chapter_is_page == '0' ) : ?>
		<tr valign="top" class="show_subpages">
			<th scope="row">
				<label for="cp_show_subpages"><?php esc_html_e( 'Show Sub-Pages', 'commentpress-core' ); ?></label>
			</th>
			<td>
				<input id="cp_show_subpages" name="cp_show_subpages" value="1"  type="checkbox" <?php echo ( $show_subpages ? ' checked="checked"' : '' ); ?> />
			</td>
		</tr>
	<?php endif; ?>

	<tr valign="top" class="show_extended">
		<th scope="row"><label for="cp_show_extended_toc"><?php esc_html_e( 'Appearance of TOC for posts', 'commentpress-core' ); ?></label></th>
		<td><select id="cp_show_extended_toc" name="cp_show_extended_toc">
				<option value="1" <?php echo ( ( $show_extended_toc == '1' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Extended information', 'commentpress-core' ); ?></option>
				<option value="0" <?php echo ( ( $show_extended_toc == '0' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Just the title', 'commentpress-core' ); ?></option>
			</select>
		</td>
	</tr>

</table>
