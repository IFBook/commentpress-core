<?php
/**
 * Site Settings screen "Table of Contents" metabox template.
 *
 * Handles markup for the Site Settings screen "Table of Contents" metabox.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo esc_html( $this->metabox_path ); ?>metabox-settings-site-nav.php -->
<table class="form-table">

	<tr valign="top">
		<th scope="row">
			<label for="<?php echo esc_attr( $this->key_page_nav_enabled ); ?>"><?php esc_html_e( 'Automatic Page Navigation', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<select id="<?php echo esc_attr( $this->key_page_nav_enabled ); ?>" name="<?php echo esc_attr( $this->key_page_nav_enabled ); ?>">
				<option value="y" <?php selected( $page_nav_enabled, 'y' ); ?>><?php esc_html_e( 'Yes', 'commentpress-core' ); ?></option>
				<option value="n" <?php selected( $page_nav_enabled, 'n' ); ?>><?php esc_html_e( 'No', 'commentpress-core' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'This controls the visibility of page numbering and navigation arrows on hierarchical Pages.', 'commentpress-core' ); ?></p>
			<p class="description"><?php esc_html_e( 'By default, CommentPress creates "book-like" navigation for the built-in "Page" Post Type to create a "Document" from hierarchically-organized Pages. Select "No" if this is not the desired behavior.', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<tr valign="top" class="show_posts_or_pages">
		<th scope="row">
			<label for="<?php echo esc_attr( $this->key_post_type ); ?>"><?php esc_html_e( 'Table of Contents contains', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<select id="<?php echo esc_attr( $this->key_post_type ); ?>" name="<?php echo esc_attr( $this->key_post_type ); ?>">
				<option value="post" <?php selected( $post_type, 'post' ); ?>><?php esc_html_e( 'Posts', 'commentpress-core' ); ?></option>
				<option value="page" <?php selected( $post_type, 'page' ); ?>><?php esc_html_e( 'Pages', 'commentpress-core' ); ?></option>
			</select>
		</td>
	</tr>

	<tr valign="top" class="chapter_is_page"<?php echo ( 'page' === $post_type ? '' : ' style="display: none;"' ); ?>>
		<th scope="row">
			<label for="<?php echo esc_attr( $this->key_chapter_is_page ); ?>"><?php esc_html_e( 'Chapters are', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<select id="<?php echo esc_attr( $this->key_chapter_is_page ); ?>" name="<?php echo esc_attr( $this->key_chapter_is_page ); ?>">
				<option value="1" <?php selected( $chapter_is_page, 1 ); ?>><?php esc_html_e( 'Pages', 'commentpress-core' ); ?></option>
				<option value="0" <?php selected( $chapter_is_page, 0 ); ?>><?php esc_html_e( 'Headings', 'commentpress-core' ); ?></option>
			</select>
			<p class="description"><?php esc_html_e( 'When Chapters are Pages, the Table of Contents will always show Sub-Pages.', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<tr valign="top" class="show_subpages"<?php echo ( 'page' === $post_type && 0 === $chapter_is_page ? '' : ' style="display: none;"' ); ?>>
		<th scope="row">
			<label for="<?php echo esc_attr( $this->key_subpages ); ?>"><?php esc_html_e( 'Show Sub-Pages', 'commentpress-core' ); ?></label>
		</th>
		<td>
			<input id="<?php echo esc_attr( $this->key_subpages ); ?>" name="<?php echo esc_attr( $this->key_subpages ); ?>" value="1"  type="checkbox" <?php checked( 1, $show_subpages ); ?> />
			<p class="description"><?php esc_html_e( 'When Sub-Pages are not shown, the Table of Contents will collapse the Menu when first loaded - only Chapters will be shown. Clicking on Chapters will reveal the Sub-Pages.', 'commentpress-core' ); ?></p>
		</td>
	</tr>

	<tr valign="top" class="show_extended"<?php echo ( 'post' === $post_type ? '' : ' style="display: none;"' ); ?>>
		<th scope="row"><label for="<?php echo esc_attr( $this->key_extended ); ?>"><?php esc_html_e( 'Appearance of TOC for Posts', 'commentpress-core' ); ?></label></th>
		<td><select id="<?php echo esc_attr( $this->key_extended ); ?>" name="<?php echo esc_attr( $this->key_extended ); ?>">
				<option value="1" <?php selected( $extended, '1' ); ?>><?php esc_html_e( 'Extended information', 'commentpress-core' ); ?></option>
				<option value="0" <?php selected( $extended, '0' ); ?>><?php esc_html_e( 'Just the title', 'commentpress-core' ); ?></option>
			</select>
		</td>
	</tr>

</table>
