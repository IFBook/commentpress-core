<?php /*
================================================================================
CommentPress Core Common Theme Functions
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

--------------------------------------------------------------------------------
*/



if ( ! function_exists( 'commentpress_admin_header' ) ):
/**
 * Custom admin header.
 *
 * @since 3.0
 */
function commentpress_admin_header() {

	// Access plugin.
	global $commentpress_core;

	// Init with same colour as theme stylesheets and default in class_commentpress_db.php
	$colour = '2c2622';

	// Override if we have the plugin enabled.
	if ( is_object( $commentpress_core ) ) {
		$colour = $commentpress_core->db->option_get_header_bg();
	}

	// Try and recreate the look of the theme header.
	echo '
<style type="text/css">

.appearance_page_custom-header #headimg
{
	min-height: 67px;
}

#headimg
{
	background-color: #' . $colour . ';
}

#headimg #name,
#headimg #desc
{
	margin-left: 20px;
	font-family: Helvetica, Arial, sans-serif;
	font-weight: normal;
	line-height: 1;
	color: #' . get_header_textcolor() . ';
}

#headimg h1
{
	margin: 0;
	padding: 0;
	padding-top: 12px;
}

#headimg #name
{
	font-size: 1em;
	text-decoration: none;
}

#headimg #desc
{
	padding-top: 3px;
	font-size: 1.2em;
	font-style: italic;
}

</style>
';

}
endif; // End commentpress_admin_header



if ( ! function_exists( 'commentpress_customize_register' ) ) :
/**
 * Implements CommentPress Core Theme options in the Theme Customizer.
 *
 * @since 3.0
 *
 * @param $wp_customize Theme Customizer object.
 */
function commentpress_customize_register( $wp_customize ) {

	// Kick out if plugin not active.
	global $commentpress_core;
	if ( ! is_object( $commentpress_core ) ) return;

	// Add "Site Image".
	commentpress_customize_site_image( $wp_customize );

	// Add "Site Logo".
	commentpress_customize_site_logo( $wp_customize );

	// Add "Header Background Colour".
	commentpress_customize_header_bg_color( $wp_customize );

}
endif; // End commentpress_customize_register
add_action( 'customize_register', 'commentpress_customize_register' );



if ( ! function_exists( 'commentpress_customize_site_image' ) ) :
/**
 * Implements CommentPress Core "Site Image" in the Theme Customizer.
 *
 * @since 3.8.5
 *
 * @param $wp_customize Theme Customizer object.
 */
function commentpress_customize_site_image( $wp_customize ) {

	// Kick out if BuddyPress Groupblog.
	global $commentpress_core;
	if ( $commentpress_core->is_groupblog() ) return;

	// Kick out if we do not have the WP 4.2 WP_Customize_Media_Control class.
	if ( ! class_exists( 'WP_Customize_Media_Control' ) ) return;

	/**
	 * Customize Site Image Control class.
	 *
	 * This is incomplete at present, because the labels are not all overridden
	 * the way we would like them, but it does at least allow us to save the
	 * attachment ID of the uploaded image instead of the URL to the full size image.
	 *
	 * @see WP_Customize_Media_Control
	 */
	class WP_Customize_Site_Image_Control extends WP_Customize_Media_Control {

		// Properties.
		public $type = 'media';
		public $mime_type = 'image';
		public $button_labels = [];

		/**
		 * Constructor.
		 *
		 * @param WP_Customize_Manager $manager
		 * @param string $id
		 * @param array  $args
		 */
		public function __construct( $manager, $id, $args = [] ) {

			// Call parent constructor.
			parent::__construct( $manager, $id, $args );

			// Allow label to be filtered.
			$site_image = apply_filters( 'commentpress_customizer_site_image_title', __( 'Site Image', 'commentpress-core' ) );

			// Set labels.
			$this->button_labels = [
				'select'       => sprintf( __( 'Select %s', 'commentpress-core' ), $site_image ),
				'change'       => sprintf( __( 'Change %s', 'commentpress-core' ), $site_image ),
				'remove'       => sprintf( __( 'Remove %s', 'commentpress-core' ), $site_image ),
				'default'      => sprintf( __( 'Default %s', 'commentpress-core' ), $site_image ),
				'placeholder'  => sprintf( __( 'No %s selected', 'commentpress-core' ), $site_image ),
				'frame_title'  => sprintf( __( 'Select %s', 'commentpress-core' ), $site_image ),
				'frame_button' => sprintf( __( 'Choose %s', 'commentpress-core' ), $site_image ),
			];

		}

	}

	// Register control (not needed as yet, but is if we want to fully extend)
	//$wp_customize->register_control_type( 'WP_Customize_Site_Image_Control' );

	// Add customizer section title.
	$wp_customize->add_section( 'cp_site_image', [
		'title' => apply_filters( 'commentpress_customizer_site_image_title', __( 'Site Image', 'commentpress-core' ) ),
		'priority' => 25,
	] );

	// Add image setting.
	$wp_customize->add_setting( 'commentpress_theme_settings[cp_site_image]', [
		 'default' => '',
		 'capability' => 'edit_theme_options',
		 'type' => 'option',
	] );

	// Add image control.
	$wp_customize->add_control( new WP_Customize_Site_Image_Control(
		$wp_customize, 'cp_site_image', [
		'label' => apply_filters( 'commentpress_customizer_site_image_title', __( 'Site Image', 'commentpress-core' ) ),
		'description' => apply_filters( 'commentpress_customizer_site_image_description', __( 'Choose an image to represent this site. Other plugins may use this image to illustrate this site - in multisite directory listings, for example.', 'commentpress-core' ) ),
		'section' => 'cp_site_image',
		'settings' => 'commentpress_theme_settings[cp_site_image]',
		'priority'	=>	1,
	] ) );

}
endif; // End commentpress_customize_site_image



if ( ! function_exists( 'commentpress_customize_site_logo' ) ) :
/**
 * Implements CommentPress Core "Site Logo" in the Theme Customizer.
 *
 * @since 3.8.5
 *
 * @param $wp_customize Theme Customizer object.
 */
function commentpress_customize_site_logo( $wp_customize ) {

	// Kick out if BuddyPress Groupblog.
	global $commentpress_core;
	if ( $commentpress_core->is_groupblog() ) return;

	// Add customizer section title.
	$wp_customize->add_section( 'cp_inline_header_image', [
		'title' => __( 'Site Logo', 'commentpress-core' ),
		'priority' => 35,
	] );

	// Add image setting.
	$wp_customize->add_setting( 'commentpress_theme_settings[cp_inline_header_image]', [
		 'default' => '',
		 'capability' => 'edit_theme_options',
		 'type' => 'option',
	] );

	// Add image control.
	$wp_customize->add_control( new WP_Customize_Image_Control(
		$wp_customize, 'cp_inline_header_image', [
		'label' => __( 'Logo Image', 'commentpress-core' ),
		'description' => apply_filters( 'commentpress_customizer_site_logo_description', __( 'You may prefer to display an image instead of text in the header of your site. The image must be a maximum of 70px tall. If it is less tall, then you can adjust the vertical alignment using the "Top padding in px" setting below.', 'commentpress-core' ) ),
		'section' => 'cp_inline_header_image',
		'settings' => 'commentpress_theme_settings[cp_inline_header_image]',
		'priority'	=>	1,
	] ) );

	// Add padding setting.
	$wp_customize->add_setting( 'commentpress_theme_settings[cp_inline_header_padding]', [
		 'default' => '',
		 'capability' => 'edit_theme_options',
		 'type' => 'option',
	] );

	// Add text control.
	$wp_customize->add_control( 'commentpress_theme_settings[cp_inline_header_padding]', [
		'label' => __( 'Top padding in px', 'commentpress-core' ),
		'section' => 'cp_inline_header_image',
		'type' => 'text',
	] );

}
endif; // End commentpress_customize_site_logo



if ( ! function_exists( 'commentpress_customize_header_bg_color' ) ) :
/**
 * Implements CommentPress Core "Header Background Colour" in the Theme Customizer.
 *
 * @since 3.8.5
 *
 * @param $wp_customize Theme Customizer object.
 */
function commentpress_customize_header_bg_color( $wp_customize ) {

	global $commentpress_core;

	// Add color picker setting.
	$wp_customize->add_setting( 'commentpress_header_bg_color', [
		'default' => '#' . $commentpress_core->db->header_bg_colour,
		 //'capability' => 'edit_theme_options',
		 //'type' => 'option',
	] );

	// Add color picker control.
	$wp_customize->add_control( new WP_Customize_Color_Control(
		$wp_customize, 'commentpress_header_bg_color', [
		'label' => __( 'Header Background Colour', 'commentpress-core' ),
		'section' => 'colors',
		'settings' => 'commentpress_header_bg_color',
	] ) );

}
endif; // End commentpress_customize_header_bg_color



if ( ! function_exists( 'commentpress_admin_menu' ) ) :
/**
 * Adds more prominent menu item.
 *
 * @since 3.0
 */
function commentpress_admin_menu() {

	// Only add for WP3.4+
	global $wp_version;
	if ( version_compare( $wp_version, '3.4', '>=' ) ) {

		// Add the Customize link to the admin menu.
		add_theme_page( __( 'Customize', 'commentpress-core' ), __( 'Customize', 'commentpress-core' ), 'edit_theme_options', 'customize.php' );

	}

}
endif; // End commentpress_admin_menu
add_action( 'admin_menu', 'commentpress_admin_menu' );



if ( ! function_exists( 'commentpress_fix_bp_core_avatar_url' ) ):
/**
 * Filter to fix broken group avatar images in BuddyPress 1.7.
 *
 * @since 3.3
 *
 * @param string $url The existing URL of the avatar.
 * @return string $url The modified URL of the avatar.
 */
function commentpress_fix_bp_core_avatar_url( $url ) {

	// If in multisite and on non-root site.
	if ( is_multisite() && ! bp_is_root_blog() ) {

		// Switch to root site.
		switch_to_blog( bp_get_root_blog_id() );

		// Get upload dir data.
		$upload_dir = wp_upload_dir();

		// Get storage location of avatars.
		$url = $upload_dir['baseurl'];

		// Switch back.
		restore_current_blog();

	}

	// --<
	return $url;

}
endif; // End commentpress_fix_bp_core_avatar_url



if ( ! function_exists( 'commentpress_get_header_image' ) ):
/**
 * Function that sets a header foreground image. (a logo, for example)
 *
 * @since 3.0
 *
 * @todo Inform users that header images are using a different method.
 */
function commentpress_get_header_image() {

	// Access plugin.
	global $commentpress_core;

	// -------------------------------------------------------------------------
	// If this is a groupblog, always show group avatar
	// -------------------------------------------------------------------------

	// Test for groupblog.
	if ( is_object( $commentpress_core ) AND $commentpress_core->is_groupblog() ) {

		// Get group ID.
		$group_id = get_groupblog_group_id( get_current_blog_id() );

		// Get group avatar.
		$avatar_options = array (
			'item_id' => $group_id,
			'object' => 'group',
			'type' => 'full',
			'alt' => __( 'Group avatar', 'commentpress-core' ),
			'class' => 'cp_logo_image',
			'width' => 48,
			'height' => 48,
			'html' => true
		);

		// Add filter for the function above.
		add_filter( 'bp_core_avatar_url', 'commentpress_fix_bp_core_avatar_url', 10, 1 );

		// Show group avatar.
		echo bp_core_fetch_avatar( $avatar_options );

		// Remove filter.
		remove_filter( 'bp_core_avatar_url', 'commentpress_fix_bp_core_avatar_url' );

		// --<
		return;

	}

	// -------------------------------------------------------------------------
	// Allow plugins to hook in before Theme Customizer.
	// -------------------------------------------------------------------------

	// Allow plugins to hook in.
	$custom_avatar_pre = apply_filters( 'commentpress_header_image_pre_customizer', false );

	// Did we get one?
	if ( $custom_avatar_pre !== false ) {

		// Show it.
		echo $custom_avatar_pre;

		// Bail before fallback.
		return;

	}

	// -------------------------------------------------------------------------
	// Implement compatibility with WordPress Theme Customizer.
	// -------------------------------------------------------------------------

	// Get the new options.
	$options = get_option( 'commentpress_theme_settings' );

	// Test for our new theme customizer option
	if ( isset( $options['cp_inline_header_image'] ) AND ! empty( $options['cp_inline_header_image'] ) ) {

		// Init top padding.
		$style = '';

		// Override if there is top padding.
		if ( isset( $options['cp_inline_header_padding'] ) AND ! empty( $options['cp_inline_header_padding'] ) ) {
			$style = ' style="padding-top: ' . $options['cp_inline_header_padding'] . 'px"';
		}

		// Show the uploaded image.
		echo apply_filters(
			'commentpress_header_image',
			'<img src="' . $options['cp_inline_header_image'] . '" class="cp_logo_image"' . $style . ' alt="' . __( 'Logo', 'commentpress-core' ) . '" />'
		);

		// --<
		return;

	}

	// -------------------------------------------------------------------------
	// Allow plugins to hook in after Theme Customizer.
	// -------------------------------------------------------------------------

	// Allow plugins to hook in.
	$custom_avatar_post = apply_filters( 'commentpress_header_image_post_customizer', false );

	// Did we get one?
	if ( $custom_avatar_post !== false ) {

		// Show it.
		echo $custom_avatar_post;

		// Bail before fallback.
		return;

	}

	// -------------------------------------------------------------------------
	// Our fallback is to go with the legacy method that some people might still be using.
	// -------------------------------------------------------------------------

	// If we have the plugin enabled.
	if ( is_object( $commentpress_core ) AND $commentpress_core->db->option_get( 'cp_toc_page' ) ) {

		// Set defaults.
		$args = [
			'post_type' => 'attachment',
			'numberposts' => 1,
			'post_status' => null,
			'post_parent' => $commentpress_core->db->option_get( 'cp_toc_page' ),
		];

		// Get them.
		$attachments = get_posts( $args );

		// We only want the first.
		if ( $attachments ) {
			$attachment = $attachments[0];
		}

		// Show it if we have an image.
		if ( isset( $attachment ) ) {
			echo wp_get_attachment_image( $attachment->ID, 'full' );
		}

	}

}
endif; // End commentpress_get_header_image



if ( ! function_exists( 'commentpress_get_body_id' ) ):
/**
 * Get an ID for the body tag.
 *
 * @since 3.0
 *
 * @return string $body_id The ID attribute for the body tag.
 */
function commentpress_get_body_id() {

	// Init.
	$body_id = '';

	// Is this multisite?
	if ( is_multisite() ) {

		// Is this the main blog?
		if ( is_main_site() ) {

			// Set main blog id.
			$body_id = ' id="main_blog"';

		}

	}

	// --<
	return $body_id;

}
endif; // End commentpress_get_body_id



if ( ! function_exists( 'commentpress_get_body_classes' ) ):
/**
 * Get classes for the body tag.
 *
 * @since 3.3
 *
 * @param boolean $raw If true, returns the class names, if false, returns the attribute.
 * @return string $body_classes The class attribute for the body tag.
 */
function commentpress_get_body_classes( $raw = false ) {

	// Init the array that holds our custom classes.
	$classes = [];

	// Access post and plugin.
	global $post, $commentpress_core;

	// -------------------- default sidebar --------------------

	// Set default sidebar but override if we have the plugin enabled.
	$sidebar_flag = 'toc';
	if ( is_object( $commentpress_core ) ) {
		$sidebar_flag = $commentpress_core->get_default_sidebar();
	}

	// Set class per sidebar.
	$sidebar_class = 'cp_sidebar_' . $sidebar_flag;

	// Add to array.
	$classes[] = $sidebar_class;

	// -------------------- commentable --------------------

	// Init commentable class but override if we have the plugin enabled.
	$commentable = '';
	if ( is_object( $commentpress_core ) ) {
		$commentable = ( $commentpress_core->is_commentable() ) ? 'commentable' : 'not_commentable';
	}

	// Add to array.
	if ( ! empty( $commentable ) ) $classes[] = $commentable;

	// -------------------- layout --------------------

	// Init layout class but if we have the plugin enabled.
	$layout_class = '';
	if ( is_object( $commentpress_core ) ) {

		// Is this the title page?
		if (
			is_object( $post ) AND
			isset( $post->ID ) AND
			$post->ID == $commentpress_core->db->option_get( 'cp_welcome_page' )
		) {

			// Init layout.
			$layout = '';

			// Set key.
			$key = '_cp_page_layout';

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) != '' ) {

				// Get it.
				$layout = get_post_meta( $post->ID, $key, true );

			}

			// If wide layout.
			if ( $layout == 'wide' ) {

				// Set layout class.
				$layout_class = 'full_width';

			}

		}

	}

	// Add to array.
	if ( ! empty( $layout_class ) ) $classes[] = $layout_class;

	// -------------------- page type --------------------

	// Set default page type.
	$page_type = '';

	// If blog post.
	if ( is_single() ) {

		// Add blog post class.
		$page_type = 'blog_post';

	}

	// If we have the plugin enabled.
	if ( is_object( $commentpress_core ) ) {

		// Is it a BuddyPress special page?
		if ( $commentpress_core->is_buddypress_special_page() ) {

			// Add BuddyPress page class.
			$page_type = 'buddypress_page';

		}

		// Is it a CommentPress Core special page?
		if ( $commentpress_core->db->is_special_page() ) {

			// Add BuddyPress page class.
			$page_type = 'commentpress_page';

		}

	}

	// Add to array.
	if ( ! empty( $page_type ) ) $classes[] = $page_type;

	// -------------------- is groupblog --------------------

	// Set default type.
	$is_groupblog = 'not-groupblog';

	// If we have the plugin enabled.
	if ( is_object( $commentpress_core ) ) {

		// If it's a groupblog.
		if ( $commentpress_core->is_groupblog() ) {
			$is_groupblog = 'is-groupblog';
		}

	}

	// Add to array.
	if ( ! empty( $is_groupblog ) ) $classes[] = $is_groupblog;

	// -------------------- blog type --------------------

	// Set default type.
	$blog_type = '';

	// If we have the plugin enabled.
	if ( is_object( $commentpress_core ) ) {

		// Get type.
		$type = $commentpress_core->db->option_get( 'cp_blog_type' );

		// Get workflow.
		$workflow = $commentpress_core->db->option_get( 'cp_blog_workflow' );

		/**
		 * Allow plugins to override the blog type - for example if workflow
		 * is enabled, it might become a new blog type as far as BuddyPress
		 * is concerned.
		 *
		 * @since 3.3
		 *
		 * @param int $type The numeric blog type.
		 * @param bool $workflow True if workflow enabled, false otherwise.
		 */
		$current_blog_type = apply_filters( 'cp_get_group_meta_for_blog_type', $type, $workflow );

		// If it's not the main site, add class.
		if ( is_multisite() AND ! is_main_site() ) {
			$blog_type = 'blogtype-' . intval( $current_blog_type );
		}

	}

	// Add to array.
	if ( ! empty( $blog_type ) ) $classes[] = $blog_type;

	// -------------------- group groupblog type --------------------

	// When viewing a group, set default groupblog type.
	$group_groupblog_type = '';

	// If we have the plugin enabled.
	if ( is_object( $commentpress_core ) ) {

		// Is it a BuddyPress group page?
		if ( function_exists( 'bp_is_groups_component' ) AND bp_is_groups_component() ) {

			// Get current group.
			$current_group = groups_get_current_group();

			// Sanity check.
			if ( $current_group instanceof BP_Groups_Group ) {

				// Get groupblog type.
				$groupblogtype = groups_get_groupmeta( $current_group->id, 'groupblogtype' );

				// Set groupblog type if present.
				if ( ! empty( $groupblogtype ) ) {
					$group_groupblog_type = $groupblogtype;
				}

			}

		}

	}

	// Add to array.
	if ( ! empty( $group_groupblog_type ) ) $classes[] = $group_groupblog_type;

	// -------------------- TinyMCE version --------------------

	// Init TinyMCE class.
	$tinymce_version = 'tinymce-3';

	// Access WP version
	global $wp_version;

	// If greater than 3.8.
	if ( version_compare( $wp_version, '3.8.9999', '>' ) ) {

		// Override TinyMCE class.
		$tinymce_version = 'tinymce-4';

	}

	// Add to array.
	if ( ! empty( $tinymce_version ) ) $classes[] = $tinymce_version;

	// -------------------- process --------------------

	// Construct attribute but allow filtering.
	$body_classes = apply_filters( 'commentpress_body_classes', implode( ' ', $classes ) );

	// If we want them wrapped, do so.
	if ( ! $raw ) {

		// Preserve backwards compat for older child themes.
		$body_classes = ' class="' . $body_classes . '"';

	}

	// --<
	return $body_classes;

}
endif; // End commentpress_get_body_classes



if ( ! function_exists( 'commentpress_document_title_parts' ) ):
/**
 * Add the root network name when the sub-blog is a group blog.
 *
 * @since 3.8
 *
 * @param array $parts The existing title parts.
 * @return array $parts The modified title parts.
 */
function commentpress_document_title_parts( $parts ) {

	global $commentpress_core;

	// If we have the plugin enabled.
	if ( is_object( $commentpress_core ) ) {

		// If it's a groupblog.
		if ( $commentpress_core->is_groupblog() ) {
			if ( ! isset( $parts['site'] ) ) {
				$parts['title'] .= commentpress_site_title( '|', false );
				unset( $parts['tagline'] );
			} else {
				$parts['site'] .= commentpress_site_title( '|', false );
			}
		}

	}

	// Return filtered array.
	return apply_filters( 'commentpress_document_title_parts', $parts );

}
endif; // End commentpress_document_title_parts

// Add a filter for the above.
add_filter( 'document_title_parts', 'commentpress_document_title_parts' );



if ( ! function_exists( 'commentpress_document_title_separator' ) ):
/**
 * Use the separator that CommentPress Core has always used.
 *
 * @since 3.8
 *
 * @param string $sep The existing separator.
 * @return string $sep The modified separator.
 */
function commentpress_document_title_separator( $sep ) {

	// --<
	return '|';

}
endif; // End commentpress_document_title_separator

// Add a filter for the above.
add_filter( 'document_title_separator', 'commentpress_document_title_separator' );



if ( ! function_exists( 'commentpress_site_title' ) ):
/**
 * Amend the site title depending on context of blog.
 *
 * @since 3.8
 *
 * @param string $sep The title separator.
 * @param boolean $echo Echo the result or not.
 * @return string $site_name The title of the site.
 */
function commentpress_site_title( $sep = '', $echo = true ) {

	// Is this multisite?
	if ( is_multisite() ) {

		// If we're on a sub-blog.
		if ( ! is_main_site() ) {

			$current_site = get_current_site();

			// Print?
			if( $echo ) {

				// Add site name.
				echo ' ' . trim( $sep ) . ' ' . $current_site->site_name;

			} else {

				// Add site name.
				return ' ' . trim( $sep ) . ' ' . $current_site->site_name;

			}

		}

	}

}
endif; // End commentpress_site_title



if ( ! function_exists( 'commentpress_header_meta_description' ) ) :
/**
 * Construct the content of the meta description tag.
 *
 * @since 3.9.10
 *
 * @return str $description The content of the meta description tag.
 */
function commentpress_header_meta_description() {

	// Distinguish single items from archives.
	if ( is_singular() ) {

		// Get queried object.
		$queried_post = get_queried_object();

		// Do we have one?
		if ( $queried_post instanceOf WP_Post ) {

			// Maybe use excerpt.
			$excerpt = strip_tags( $queried_post->post_excerpt );
			if ( ! empty( $excerpt ) ) {
				$description = esc_attr( $excerpt );
			} else {

				// Maybe use trimmed content.
				$content = strip_tags( $queried_post->post_content );
				if ( ! empty( $content ) ) {
					$description = esc_attr( wp_trim_words( $content, 35 ) );
				}

			}

		}

		// Fall back to title.
		if ( empty( $description ) ) {
			$description = single_post_title( '', false );
		}

	} else {
		$description = get_bloginfo( 'name' ) . ' - ' . get_bloginfo( 'description' );
	}

	/**
	 * Allow the meta description to be filtered.
	 *
	 * @since 3.9.10
	 *
	 * @param str $description The existing meta description.
	 * @return str $description The modified meta description.
	 */
	$description = apply_filters( 'commentpress_header_meta_description', $description );

	// --<
	return $description;

}
endif; // End commentpress_header_meta_description



if ( ! function_exists( 'commentpress_remove_more_jump_link' ) ):
/**
 * Disable more link jump.
 *
 * @see http://codex.wordpress.org/Customizing_the_Read_More
 *
 * @since 3.0
 *
 * @param string $link The existing more link.
 * @return string $link The modified more link.
 */
function commentpress_remove_more_jump_link( $link ) {

	$offset = strpos( $link, '#more-' );

	if ($offset) {
		$end = strpos( $link, '"',$offset );
	}

	if ($end) {
		$link = substr_replace( $link, '', $offset, $end - $offset );
	}

	// --<
	return $link;

}
endif; // End commentpress_remove_more_jump_link

// Add a filter for the above.
add_filter( 'the_content_more_link', 'commentpress_remove_more_jump_link' );



if ( ! function_exists( 'commentpress_page_title' ) ):
/**
 * Builds a page title, including parent page titles.
 *
 * The CommentPress Core Default theme displays a "cookie trail" style title for
 * pages so we need to build this by inspecting page ancestors.
 *
 * @since 3.0
 *
 * @return string $title The page title.
 */
function commentpress_page_title() {

	// Declare access to globals.
	global $commentpress_core, $post;

	// Init.
	$title = '';
	$sep = ' &#8594; ';

	//$title .= get_bloginfo( 'name' );

	if ( is_page() OR is_single() OR is_category() ) {

		if ( is_page() ) {

			$ancestors = get_post_ancestors( $post );

			if ( $ancestors ) {
				$ancestors = array_reverse( $ancestors );

				$crumbs = [];

				foreach ( $ancestors as $crumb ) {
					$crumbs[] = get_the_title( $crumb );
				}

				$title .= implode( $sep, $crumbs ) . $sep;
			}

		}

		if ( is_single() ) {
			//$category = get_the_category();
			//$title .= $sep . $category[0]->cat_name;
		}

		if ( is_category() ) {
			$category = get_the_category();
			$title .= $category[0]->cat_name . $sep;
		}

		// Current page.
		if ( is_page() OR is_single() ) {
			$title .= get_the_title();
		}

	}

	// --<
	return $title;

}
endif; // End commentpress_page_title



if ( ! function_exists( 'commentpress_has_page_children' ) ):
/**
 * Query whether a given page has children.
 *
 * @since 3.3
 *
 * @param object $page_obj The WordPress page object to query.
 * @return boolean True if page has children, false otherwise.
 */
function commentpress_has_page_children( $page_obj ) {

	// Init to look for published pages.
	$defaults = [
		'post_parent' => $page_obj->ID,
		'post_type' => 'page',
		'numberposts' => -1,
		'post_status' => 'publish',
	];

	// Get page children.
	$kids =& get_children( $defaults );

	// Do we have any?
	return ( empty( $kids ) ) ? false : true;

}
endif; // End commentpress_has_page_children



if ( ! function_exists( 'commentpress_get_children' ) ):
/**
 * Retrieve comment children.
 *
 * @since 3.3
 *
 * @param object $comment The WordPress comment object.
 * @param string $page_or_post The WordPress post type to query.
 * @return array $result The array of child comments.
 */
function commentpress_get_children( $comment, $page_or_post ) {

	// Declare access to globals.
	global $wpdb;

	// Construct query for comment children.
	$query = "
	SELECT $wpdb->comments.*, $wpdb->posts.post_title, $wpdb->posts.post_name
	FROM $wpdb->comments, $wpdb->posts
	WHERE $wpdb->comments.comment_post_ID = $wpdb->posts.ID
	AND $wpdb->posts.post_type = '$page_or_post'
	AND $wpdb->comments.comment_approved = '1'
	AND $wpdb->comments.comment_parent = '$comment->comment_ID'
	ORDER BY $wpdb->comments.comment_date ASC
	";

	// Does it have children?
	return $wpdb->get_results( $query );

}
endif; // End commentpress_get_children



if ( ! function_exists( 'commentpress_get_comments' ) ):
/**
 * Generate comments recursively.
 *
 * This function builds the list into a global called $cp_comment_output for
 * retrieval elsewhere.
 *
 * @since 3.0
 *
 * @param array $comments An array of WordPress comment objects.
 * @param string $page_or_post The WordPress post type to query.
 */
function commentpress_get_comments( $comments, $page_or_post ) {

	// Declare access to globals.
	global $cp_comment_output;

	// Do we have any comments?
	if( count( $comments ) > 0 ) {

		// Open ul.
		$cp_comment_output .= '<ul class="item_ul">' . "\n\n";

		// Produce a checkbox for each.
		foreach( $comments as $comment ) {

			// Open li.
			$cp_comment_output .= '<li class="item_li">' . "\n\n";

			// Format this comment.
			$cp_comment_output .= commentpress_format_comment( $comment );

			// Get comment children.
			$children = commentpress_get_children( $comment, $page_or_post );

			// Do we have any?
			if( count( $children ) > 0 ) {

				// Recurse.
				commentpress_get_comments( $children, $page_or_post );

			}

			// Close li.
			$cp_comment_output .= '</li>' . "\n\n";

		}

		// Close ul.
		$cp_comment_output .= '</ul>' . "\n\n";

	}

}
endif; // End commentpress_get_comments



if ( ! function_exists( 'commentpress_get_user_link' ) ):
/**
 * Get user link in vanilla WordPress scenarios.
 *
 * In default single install mode, just link to their URL, unless they are an
 * author, in which case we link to their author page. In multisite, the same.
 * When BuddyPress is enabled, always link to their profile.
 *
 * @since 3.0
 *
 * @param object $user The WordPress user object.
 * @param object $comment The WordPress comment object.
 * @return string $url The URL for the user.
 */
function commentpress_get_user_link( $user, $comment = null ) {

	// Kick out if not a user.
	if ( ! is_object( $user ) ) return false;

	// We're through: the user is on the system.
	global $commentpress_core;

	// If BuddyPress.
	if ( is_object( $commentpress_core ) AND $commentpress_core->is_buddypress() ) {

		// BuddyPress link ($no_anchor = null, $just_link = true)
		$url = bp_core_get_userlink( $user->ID, null, true );

	} else {

		// Get standard WordPress author URL.

		// Get author URL.
		$url = get_author_posts_url( $user->ID );

		// WP sometimes leaves 'http://' or 'https://' in the field.
		if (  $url == 'http://'  OR $url == 'https://' ) {

			// Clear.
			$url = '';

		}

	}

	// --<
	return apply_filters( 'commentpress_get_user_link', $url, $user, $comment );

}
endif; // End commentpress_get_user_link



if ( ! function_exists( 'commentpress_echo_post_meta' ) ):
/**
 * Show user(s) in the loop.
 *
 * @since 3.0
 */
function commentpress_echo_post_meta() {

	// Bail if this is a BuddyPress page.
	if ( function_exists( 'is_buddypress' ) AND is_buddypress() ) return;

	// Compat with Co-Authors Plus.
	if ( function_exists( 'get_coauthors' ) ) {

		// Get multiple authors.
		$authors = get_coauthors();

		// If we get some.
		if ( ! empty( $authors ) ) {

			// Use the Co-Authors format of "name, name, name & name".
			$author_html = '';

			// Init counter.
			$n = 1;

			// Find out how many author we have.
			$author_count = count( $authors );

			// Loop.
			foreach( $authors AS $author ) {

				// Default to comma.
				$sep = ', ';

				// If we're on the penultimate.
				if ( $n == ( $author_count - 1 ) ) {

					// Use ampersand.
					$sep = __( ' &amp; ', 'commentpress-core' );

				}

				// If we're on the last, don't add.
				if ( $n == $author_count ) { $sep = ''; }

				// Get name.
				$author_html .= commentpress_echo_post_author( $author->ID, false );

				// Add separator.
				$author_html .= $sep;

				// Increment.
				$n++;

				// Yes - are we showing avatars?
				if ( get_option( 'show_avatars' ) ) {

					// Get avatar.
					echo get_avatar( $author->ID, $size='32' );

				}

			}

			?><cite class="fn"><?php echo $author_html; ?></cite>

			<p><a href="<?php the_permalink() ?>"><?php echo esc_html( get_the_date( get_option( 'date_format' ) ) ); ?></a></p>

			<?php

		}

	} else {

		// Get avatar.
		$author_id = get_the_author_meta( 'ID' );
		echo get_avatar( $author_id, $size='32' );

		?>

		<cite class="fn"><?php commentpress_echo_post_author( $author_id ) ?></cite>

		<p><a href="<?php the_permalink() ?>"><?php echo esc_html( get_the_date( get_option( 'date_format' ) ) ); ?></a></p>

		<?php

	}

}
endif; // End commentpress_echo_post_meta



if ( ! function_exists( 'commentpress_show_source_url' ) ):
/**
 * Show source URL for print.
 *
 * @since 3.5
 */
function commentpress_show_source_url() {

	// Add the URL - hidden, but revealed by print stylesheet.
	?><p class="hidden_page_url"><?php

		// Label.
		echo __( 'Source: ', 'commentpress-core' );

		// Path from server array, if set.
		$path = ( isset( $_SERVER['REQUEST_URI'] ) ) ? $_SERVER['REQUEST_URI'] : '';

		// Get server, if set.
		$server = ( isset( $_SERVER['SERVER_NAME'] ) ) ? $_SERVER['SERVER_NAME'] : '';

		// Get protocol, if set.
		$protocol = ( ! empty( $_SERVER['HTTPS'] ) ) ? 'https' : 'http';

		// Construct URL.
		$url = $protocol . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

		// Echo.
		echo $url;

	?></p><?php

}
endif; // End commentpress_show_source_url

// Add action for the above.
add_action( 'wp_footer', 'commentpress_show_source_url' );



if ( ! function_exists( 'commentpress_echo_post_author' ) ):
/**
 * Show username (with link) in the loop.
 *
 * @since 3.0
 *
 * @param int $author_id The numeric ID of the author.
 * @param bool $echo Print or return the linked username.
 * @return str $author The linked username.
 */
function commentpress_echo_post_author( $author_id, $echo = true ) {

	// Get author details.
	$user = get_userdata( $author_id );

	// Kick out if we don't have a user with that ID.
	if ( ! is_object( $user ) ) return;

	// Access plugin.
	global $commentpress_core, $post;

	// If we have the plugin enabled. and it's BuddyPress.
	if ( is_object( $post ) AND is_object( $commentpress_core ) AND $commentpress_core->is_buddypress() ) {

		// Construct user link.
		$author = bp_core_get_userlink( $user->ID );

	} else {

		// Link to theme's author page.
		$link = sprintf(
			'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
			get_author_posts_url( $user->ID, $user->user_nicename ),
			esc_attr( sprintf( __( 'Posts by %s' ), $user->display_name ) ),
			esc_html( $user->display_name )
		);
		$author = apply_filters( 'the_author_posts_link', $link );

	}

	// If we're echoing.
	if ( $echo ) {
		echo $author;
	} else {
		return $author;
	}

}
endif; // End commentpress_echo_post_author



if ( ! function_exists( 'commentpress_format_comment' ) ):
/**
 * Format comment on custom CommentPress Core comments pages.
 *
 * @since 3.0
 *
 * @param object $comment The comment object.
 * @param str $context Either "all" for all-comments or "by" for comments-by-commenter.
 * @return str The formatted comment HTML.
 */
function commentpress_format_comment( $comment, $context = 'all' ) {

	// Declare access to globals.
	global $commentpress_core, $cp_comment_output;

	// TODO enable WordPress API on comment?
	//$GLOBALS['comment'] = $comment;

	// Construct link.
	$comment_link = get_comment_link( $comment->comment_ID );

	// Construct anchor.
	$comment_anchor = '<a href="' . $comment_link . '" title="' . esc_attr( __( 'See comment in context', 'commentpress-core' ) ) . '">' . __( 'Comment', 'commentpress-core' ) . '</a>';

	// Construct date.
	$comment_date = date_i18n( get_option( 'date_format' ), strtotime( $comment->comment_date ) );

	// If context is 'all comments'.
	if ( $context == 'all' ) {

		// Get author.
		if ( $comment->comment_author != '' ) {

			// Was it a registered user?
			if ( $comment->user_id != '0' ) {

				// Get user details.
				$user = get_userdata( $comment->user_id );

				// Get user link.
				$user_link = commentpress_get_user_link( $user, $comment );

				// Did we get one?
				if ( $user_link != '' AND $user_link != 'http://' ) {

					// Construct link to user URL.
					$comment_author = '<a href="' . $user_link . '">' . $comment->comment_author . '</a>';

				} else {

					// Just show author name.
					$comment_author = $comment->comment_author;

				}

			} else {

				// Do we have an author URL?
				if ( $comment->comment_author_url != '' AND $comment->comment_author_url != 'http://' ) {

					// Construct link to user URL.
					$comment_author = '<a href="' . $comment->comment_author_url . '">' . $comment->comment_author . '</a>';

				} else {

					// Define context.
					$comment_author = $comment->comment_author;

				}

			}


		} else {

			// We don't have a name.
			$comment_author = __( 'Anonymous', 'commentpress-core' );

		}

		// Construct comment header content.
		$comment_meta_content = sprintf(
			__( '%1$s by %2$s on %3$s', 'commentpress-core' ),
			$comment_anchor,
			$comment_author,
			$comment_date
		);

		// Wrap comment meta in a div.
		$comment_meta = '<div class="comment_meta">' . $comment_meta_content . '</div>' . "\n";

		// Allow filtering by plugins.
		$comment_meta = apply_filters(
			'commentpress_format_comment_all_meta', // Filter name.
			$comment_meta, // Built meta
			$comment,
			$comment_anchor,
			$comment_author,
			$comment_date
		);

	// If context is 'by commenter'.
	} elseif ( $context == 'by' ) {

		// Construct link.
		$page_link = trailingslashit( get_permalink( $comment->comment_post_ID ) );

		// Construct page anchor.
		$page_anchor = '<a href="' . $page_link . '">' . get_the_title( $comment->comment_post_ID ) . '</a>';

		// Construct comment header content.
		$comment_meta_content = sprintf(
			__( '%1$s on %2$s on %3$s', 'commentpress-core' ),
			$comment_anchor,
			$page_anchor,
			$comment_date
		);

		// Wrap comment meta in a div.
		$comment_meta = '<div class="comment_meta">' . $comment_meta_content . '</div>' . "\n";

		// Allow filtering by plugins.
		$comment_meta = apply_filters(
			'commentpress_format_comment_by_meta', // Filter name.
			$comment_meta, // Built meta.
			$comment,
			$comment_anchor,
			$page_anchor,
			$comment_date
		);

	}

	// Comment content.
	$comment_body = '<div class="comment-content">' . apply_filters( 'comment_text', $comment->comment_content ) . '</div>' . "\n";

	// Construct comment.
	return '<div class="comment_wrapper">' . "\n" . $comment_meta . $comment_body . '</div>' . "\n\n";

}
endif; // End commentpress_format_comment



if ( ! function_exists( 'commentpress_get_comments_by_content' ) ):
/**
 * Comments-by page display function.
 *
 * @todo Do we want trackbacks?
 *
 * @since 3.0
 *
 * @return str $html The HTML for the page.
 */
function commentpress_get_comments_by_content() {

	// Init return.
	$html = '';

	// Get all approved comments.
	$all_comments = get_comments( [
		'status' => 'approve',
		'orderby' => 'comment_author, comment_post_ID, comment_date',
		'order' => 'ASC',
	] );

	// Kick out if none.
	if ( count( $all_comments ) == 0 ) return $html;

	// Build list of authors
	$authors_with = [];
	$author_names = [];
	//$post_comment_counts = [];

	foreach( $all_comments AS $comment ) {

		// Add to authors with comments array.
		if ( ! in_array( $comment->comment_author_email, $authors_with ) ) {
			$authors_with[] = $comment->comment_author_email;
			$name = $comment->comment_author != '' ? $comment->comment_author : __( 'Anonymous', 'commentpress-core' );
			$author_names[$comment->comment_author_email] = $name;
		}

		/*
		// Increment counter.
		if ( ! isset( $post_comment_counts[$comment->comment_author_email] ) ) {
			$post_comment_counts[$comment->comment_author_email] = 1;
		} else {
			$post_comment_counts[$comment->comment_author_email]++;
		}
		*/

	}

	// Kick out if none.
	if ( count( $authors_with ) == 0 ) return $html;

	// Open ul.
	$html .= '<ul class="all_comments_listing">' . "\n\n";

	// Loop through authors.
	foreach( $authors_with AS $author ) {

		// Open li.
		$html .= '<li class="author_li"><!-- author li -->' . "\n\n";

		// Add gravatar.
		$html .= '<h3>' . get_avatar( $author, $size='24' ) . esc_html( $author_names[$author] ) . '</h3>' . "\n\n";

		// Open comments div.
		$html .= '<div class="item_body">' . "\n\n";

		// Open ul.
		$html .= '<ul class="item_ul">' . "\n\n";

		// Loop through comments.
		foreach( $all_comments AS $comment ) {

			// Does it belong to this author?
			if ( $author == $comment->comment_author_email ) {

				// Open li.
				$html .= '<li class="item_li"><!-- item li -->' . "\n\n";

				// Show the comment.
				$html .= commentpress_format_comment( $comment, 'by' );

				// Close li.
				$html .= '</li><!-- /item li -->' . "\n\n";

			}

		}

		// Close ul.
		$html .= '</ul>' . "\n\n";

		// Close item div.
		$html .= '</div><!-- /item_body -->' . "\n\n";

		// Close li.
		$html .= '</li><!-- /.author_li -->' . "\n\n\n\n";

	}

	// Close ul.
	$html .= '</ul><!-- /.all_comments_listing -->' . "\n\n";

	// --<
	return $html;

}
endif; // End commentpress_get_comments_by_content



if ( ! function_exists( 'commentpress_get_comments_by_page_content' ) ):
/**
 * Comments-by page display wrapper function.
 *
 * @since 3.0
 *
 * @return str $page_content The page content.
 */
function commentpress_get_comments_by_page_content() {

	// Allow oEmbed in comments.
	global $wp_embed;
	if ( $wp_embed instanceof WP_Embed ) {
		add_filter( 'comment_text', [ $wp_embed, 'autoembed' ], 1 );
	}

	// Declare access to globals.
	global $commentpress_core;

	// Get data.
	$page_content = commentpress_get_comments_by_content();

	// --<
	return $page_content;

}
endif; // End commentpress_get_comments_by_page_content



if ( ! function_exists( 'commentpress_show_activity_tab' ) ):
/**
 * Decide whether or not to show the Activity Sidebar.
 *
 * @since 3.3
 *
 * @return bool True if we show the activity tab, false otherwise.
 */
function commentpress_show_activity_tab() {

	// Declare access to globals.
	global $commentpress_core, $post;

	/*
	// If we have the plugin enabled.
	if ( is_object( $commentpress_core ) ) {

		// Is this multisite?
		if (
			( is_multisite() AND is_main_site() AND $commentpress_core->is_buddypress_special_page() ) OR
			! is_object( $post )
		) {

			// Ignore activity.
			return false;

		}

	}
	*/

	// --<
	return true;

}
endif; // End commentpress_show_activity_tab



if ( ! function_exists( 'commentpress_is_commentable' ) ):
/**
 * Is a post/page commentable?
 *
 * @since 3.3
 *
 * @return bool $is_commentable True if page can have comments, false otherwise.
 */
function commentpress_is_commentable() {

	// Declare access to plugin
	global $commentpress_core;

	// If we have it.
	if ( is_object( $commentpress_core ) ) {

		// Return what it reports.
		return $commentpress_core->is_commentable();

	}

	// --<
	return false;

}
endif; // End commentpress_is_commentable



if ( ! function_exists( 'commentpress_get_comment_activity' ) ):
/**
 * Activity sidebar display function.
 *
 * @todo Do we want trackbacks?
 *
 * @since 3.3
 *
 * @param str $scope The scope of the activities.
 * @return str $page_content The HTML output for activities.
 */
function commentpress_get_comment_activity( $scope = 'all' ) {

	// Allow oEmbed in comments.
	global $wp_embed;
	if ( $wp_embed instanceof WP_Embed ) {
		add_filter( 'comment_text', [ $wp_embed, 'autoembed' ], 1 );
	}

	// Declare access to globals.
	global $commentpress_core, $post;

	// Init page content
	$page_content = '';

	// Define defaults.
	$args = [
		'number' => 10,
		'status' => 'approve',
		// Exclude trackbacks and pingbacks until we decide what to do with them.
		'type' => '',
	];

	// If we are on a 404, for example.
	if ( $scope == 'post' AND is_object( $post ) ) {

		// Get all comments.
		$args['post_id'] = $post->ID;

	}

	// Get 'em.
	$data = get_comments( $args );

	// Did we get any?
	if ( count( $data ) > 0 ) {

		// Init comments array.
		$comments_array = [];

		// Loop.
		foreach( $data AS $comment ) {

			// Exclude comments from password-protected posts.
			if ( ! post_password_required( $comment->comment_post_ID ) ) {
				$comment_markup = commentpress_get_comment_activity_item( $comment );
				if ( ! empty( $comment_markup ) ) {
					$comments_array[] = $comment_markup;
				}
			}

		}

		// Wrap in list if we get some.
		if ( ! empty( $comments_array ) ) {

			// Open ul.
			$page_content .= '<ol class="comment_activity">' . "\n\n";

			// Add comments.
			$page_content .= implode( '', $comments_array );

			// Close ul.
			$page_content .= '</ol><!-- /comment_activity -->' . "\n\n";

		}

	}

	// --<
	return $page_content;

}
endif; // End commentpress_get_comment_activity



if ( ! function_exists( 'commentpress_get_comment_activity_item' ) ):
/**
 * Get comment formatted for the activity sidebar.
 *
 * @since 3.3
 *
 * @param $comment The comment object.
 * @return $item_html The modified comment HTML.
 */
function commentpress_get_comment_activity_item( $comment ) {

	// Enable WordPress API on comment
	$GLOBALS['comment'] = $comment;

	// Declare access to globals.
	global $commentpress_core, $post;

	// Init markup.
	$item_html = '';

	// Only comments until we decide what to do with pingbacks and trackbacks.
	if ( $comment->comment_type == 'pingback' ) return $item_html;
	if ( $comment->comment_type == 'trackback' ) return $item_html;

	// Test for anonymous comment (usually generated by WP itself in multisite installs)
	if ( empty( $comment->comment_author ) ) {
		$comment->comment_author = __( 'Anonymous', 'commentpress-core' );
	}

	// Was it a registered user?
	if ( $comment->user_id != '0' ) {

		// Get user details.
		$user = get_userdata( $comment->user_id );

		// Get user link.
		$user_link = commentpress_get_user_link( $user, $comment );

		// Construct author citation.
		$author = '<cite class="fn"><a href="' . $user_link . '">' . get_comment_author() . '</a></cite>';

		// Construct link to user URL.
		$author = ( $user_link != '' AND $user_link != 'http://' ) ?
					'<cite class="fn"><a href="' . $user_link . '">' . get_comment_author() . '</a></cite>' :
					 '<cite class="fn">' . get_comment_author() . '</cite>';

	} else {

		// Construct link to commenter URL.
		$author = ( $comment->comment_author_url != '' AND $comment->comment_author_url != 'http://' ) ?
					'<cite class="fn"><a href="' . $comment->comment_author_url . '">' . get_comment_author() . '</a></cite>' :
					 '<cite class="fn">' . get_comment_author() . '</cite>';

	}

	// Approved comment?
	if ( $comment->comment_approved == '0' ) {
		$comment_text = '<p><em>' . __( 'Comment awaiting moderation', 'commentpress-core' ) . '</em></p>';
	} else {
		$comment_text = get_comment_text( $comment->comment_ID );
	}

	// Default to not on post.
	$is_on_current_post = '';

	// On current post?
	if ( is_singular() AND is_object( $post ) AND $comment->comment_post_ID == $post->ID ) {

		// Access paging globals.
		global $multipage, $page;

		// Is it the same page, if paged?
		if ( $multipage ) {

			// If it has a text sig.
			if (
				! is_null( $comment->comment_signature ) AND
				$comment->comment_signature != ''
			) {

				// Set key.
				$key = '_cp_comment_page';

				// If the custom field already has a value.
				if ( get_comment_meta( $comment->comment_ID, $key, true ) != '' ) {

					// Get comment's page from meta.
					$page_num = get_comment_meta( $comment->comment_ID, $key, true );

					// Is it this one?
					if ( $page_num == $page ) {

						// Is the right page.
						$is_on_current_post = ' comment_on_post';

					}

				}

			} else {

				// It's always the right page for page-level comments.
				$is_on_current_post = ' comment_on_post';

			}

		} else {

			// Must be the right page.
			$is_on_current_post = ' comment_on_post';

		}

	}

	// Open li.
	$item_html .= '<li><!-- item li -->' . "\n\n";

	// Show the comment.
	$item_html .= '
<div class="comment-wrapper">

<div class="comment-identifier">
' . get_avatar( $comment, $size='32' ) . '
' . $author . '
<p class="comment_activity_date"><a class="comment_activity_link' . $is_on_current_post . '" href="' . htmlspecialchars( get_comment_link() ) . '">' . sprintf( __( '%1$s at %2$s', 'commentpress-core' ), get_comment_date(), get_comment_time() ) . '</a></p>
</div><!-- /comment-identifier -->



<div class="comment-content">
' . apply_filters( 'comment_text', $comment_text ) . '
</div><!-- /comment-content -->

<div class="reply"><p><a class="comment_activity_link' . $is_on_current_post . '" href="' . htmlspecialchars( get_comment_link() ) . '">' . __( 'See in context', 'commentpress-core' ) . '</a></p></div><!-- /reply -->

</div><!-- /comment-wrapper -->

';

	// Close li.
	$item_html .= '</li><!-- /item li -->' . "\n\n";

	// --<
	return $item_html;

}
endif; // End commentpress_get_comment_activity_item



if ( ! function_exists( 'commentpress_lexia_support_mime' ) ):
/**
 * The "media" post type needs more granular naming support.
 *
 * @since 3.9
 *
 * @param str $post_type_name The existing singular name of the post type.
 * @param str $post_type The post type identifier.
 * @return str $post_type_name The modified singular name of the post type.
 */
function commentpress_lexia_support_mime( $post_type_name, $post_type ) {

	// Only handle media.
	if ( $post_type != 'attachment' ) return $post_type_name;

	// Get mime type.
	$mime_type = get_post_mime_type( get_the_ID() );

	// Use different name for each.
	switch ( $mime_type ) {

		// Image.
		case 'image/jpeg':
		case 'image/png':
		case 'image/gif':
			$mime_type_name = __( 'Image', 'commentpress-core' );
			break;

		// Video.
		case 'video/mpeg':
		case 'video/mp4':
		case 'video/quicktime':
			$mime_type_name = __( 'Video', 'commentpress-core' );
			break;

		// File.
		case 'text/csv':
		case 'text/plain':
		case 'text/xml':
		default:
			$mime_type_name = __( 'File', 'commentpress-core' );
			break;

	}

	/**
	 * Allow this name to be filtered.
	 *
	 * @since 3.9
	 *
	 * @param str $mime_type_name The name for this mime type.
	 * @param str $mime_type The mime type.
	 * @return str $mime_type_name The modified name for this mime type.
	 */
	$mime_type_name = apply_filters( 'commentpress_lexia_mime_type_name', $mime_type_name, $mime_type );

	// --<
	return $mime_type_name;

}
endif; // End commentpress_lexia_support_mime

// Add filter for the above.
add_filter( 'commentpress_lexia_post_type_name', 'commentpress_lexia_support_mime', 10, 2 );



if ( ! function_exists( 'commentpress_lexia_modify_entity_text' ) ):
/**
 * The "media" post type needs more granular naming support.
 *
 * @since 3.9
 *
 * @param str $entity_text The current entity text.
 * @param str $post_type_name The singular name of the post type.
 * @param str $post_type The post type identifier.
 * @return str $entity_text The modified entity text.
 */
function commentpress_lexia_modify_entity_text( $entity_text, $post_type_name, $post_type ) {

	// Only handle media.
	if ( $post_type != 'attachment' ) return $entity_text;

	// Override entity text.
	$entity_text = sprintf(
		__( 'the %s', 'commentpress-core' ),
		$post_type_name
	);

	// --<
	return $entity_text;

}
endif; // End commentpress_lexia_modify_entity_text

// Add filter for the above.
add_filter( 'commentpress_lexia_whole_entity_text', 'commentpress_lexia_modify_entity_text', 10, 3 );



if ( ! function_exists( 'commentpress_comments_by_para_format_whole' ) ):
/**
 * Format the markup for the "whole page" section of comments.
 *
 * @since 3.8.10
 *
 * @param str $post_type_name The singular name of the post type.
 * @param str $post_type The post type identifier.
 * @param int $comment_count The number of comments on the block.
 * @return array $return Data array containing the translated strings.
 */
function commentpress_comments_by_para_format_whole( $post_type_name, $post_type, $comment_count ) {

	// Init return.
	$return = [];

	// Construct entity text.
	$return['entity_text'] = sprintf(
		__( 'the whole %s', 'commentpress-core' ),
		$post_type_name
	);

	/**
	 * Allow "the whole entity" text to be filtered.
	 *
	 * This is primarily for "media", where it makes little sense the comment on
	 * "the whole image", for example.
	 *
	 * @since 3.9
	 *
	 * @param str $entity_text The current entity text.
	 * @param str $post_type_name The singular name of the post type.
	 * @return str $entity_text The modified entity text.
	 */
	$return['entity_text'] = apply_filters(
		'commentpress_lexia_whole_entity_text',
		$return['entity_text'],
		$post_type_name,
		$post_type
	);

	// Construct permalink text.
	$return['permalink_text'] = sprintf(
		__( 'Permalink for comments on %s', 'commentpress-core' ),
		$return['entity_text']
	);

	// Construct comment count.
	$return['comment_text'] = sprintf(
		_n( '<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comment</span>', '<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comments</span>', $comment_count, 'commentpress-core' ),
		$comment_count // Substitution.
	);

	// Construct heading text.
	$return['heading_text'] = sprintf(
		__( '%1$s on <span class="source_block">%2$s</span>', 'commentpress-core' ),
		$return['comment_text'],
		$return['entity_text']
	);

	// --<
	return $return;

}
endif; // End commentpress_comments_by_para_format_whole



if ( ! function_exists( 'commentpress_comments_by_para_format_pings' ) ):
/**
 * Format the markup for the "pingbacks and trackbacks" section of comments.
 *
 * @since 3.8.10
 *
 * @param int $comment_count The number of comments on the block.
 * @return array $return Data array containing the translated strings.
 */
function commentpress_comments_by_para_format_pings( $comment_count ) {

	// Init return.
	$return = [];

	// Construct entity text.
	$return['entity_text'] = __( 'pingback or trackback', 'commentpress-core' );

	// Construct permalink text.
	$return['permalink_text'] = __( 'Permalink for pingbacks and trackbacks', 'commentpress-core' );

	// Construct comment count.
	$return['comment_text'] = sprintf(
		_n( '<span>%d</span> Pingback or trackback', '<span>%d</span> Pingbacks and trackbacks', $comment_count, 'commentpress-core' ),
		$comment_count // Substitution.
	);

	// Construct heading text.
	$return['heading_text'] = sprintf( '<span>%s</span>', $return['comment_text'] );

	// --<
	return $return;

}
endif; // End commentpress_comments_by_para_format_pings



if ( ! function_exists( 'commentpress_comments_by_para_format_block' ) ):
/**
 * Format the markup for "comments by block" section of comments.
 *
 * @since 3.8.10
 *
 * @param int $comment_count The number of comments on the block.
 * @param int $para_num The sequential number of the block.
 * @return array $return Data array containing the translated strings.
 */
function commentpress_comments_by_para_format_block( $comment_count, $para_num ) {

	// Init return.
	$return = [];

	// Access plugin.
	global $commentpress_core;

	// Get block name.
	$block_name = __( 'paragraph', 'commentpress-core' );
	if ( is_object( $commentpress_core ) ) {
		$block_name = $commentpress_core->parser->lexia_get();
	}

	// Construct entity text.
	$return['entity_text'] = sprintf(
		__( '%1$s %2$s', 'commentpress-core' ),
		$block_name,
		$para_num
	);

	// Construct permalink text.
	$return['permalink_text'] = sprintf(
		__( 'Permalink for comments on %1$s %2$s', 'commentpress-core' ),
		$block_name,
		$para_num
	);

	// Construct comment count.
	$return['comment_text'] = sprintf(
		_n( '<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comment</span>', '<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comments</span>', $comment_count, 'commentpress-core' ),
		$comment_count // Substitution.
	);

	// Construct heading text.
	$return['heading_text'] = sprintf(
		__( '%1$s on <span class="source_block">%2$s %3$s</span>', 'commentpress-core' ),
		$return['comment_text'],
		$block_name,
		$para_num
	);

	// --<
	return $return;

}
endif; // End commentpress_comments_by_para_format_block



if ( ! function_exists( 'commentpress_get_comments_by_para' ) ):
/**
 * Get comments delimited by paragraph.
 *
 * @since 3.0
 */
function commentpress_get_comments_by_para() {

	/**
	 * Overwrite the content width for the comments column.
	 *
	 * This is set to an arbitrary width that *sort of* works for the comments
	 * column for all CommentPress themes. It can be overridden by this filter
	 * if a particular theme or child theme wants to do so. The content width
	 * determines the default width for oEmbedded content.
	 *
	 * @since 3.9
	 *
	 * @param int $content_width An general content width for all themes.
	 * @return int $content_width A specific content width for a specific theme.
	 */
	global $content_width;
	$content_width = apply_filters( 'commentpress_comments_content_width', 380 );

	// Allow oEmbed in comments.
	global $wp_embed;
	if ( $wp_embed instanceof WP_Embed ) {
		add_filter( 'comment_text', [ $wp_embed, 'autoembed' ], 1 );
	}

	// Allow plugins to precede comments.
	do_action( 'commentpress_before_scrollable_comments' );

	// Declare access to globals.
	global $post, $commentpress_core;

	// Get approved comments for this post, sorted comments by text signature.
	$comments_sorted = $commentpress_core->get_sorted_comments( $post->ID );

	// Key for starting paragraph number.
	$key = '_cp_starting_para_number';

	// Default starting paragraph number.
	$start_num = 1;

	// Override if the custom field already has a value.
	if ( get_post_meta( $post->ID, $key, true ) != '' ) {
		$start_num = absint( get_post_meta( $post->ID, $key, true ) );
	}

	// If we have any.
	if ( count( $comments_sorted ) > 0 ) {

		// Allow for BuddyPress registration.
		$registration = false;
		if ( function_exists( 'bp_get_signup_allowed' ) AND bp_get_signup_allowed() ) {
			$registration = true;
		}

		// Maybe redirect to BuddyPress sign-up.
		if ( $registration ) {
			$redirect = bp_get_signup_page();
		} else {
			$redirect = wp_login_url( get_permalink() );
		}

		// Init allowed to comment.
		$login_to_comment = false;

		// If we have to log in to comment.
		if ( get_option( 'comment_registration' ) AND ! is_user_logged_in() ) {
			$login_to_comment = true;
		}

		// Default comment type to get.
		$comment_type = 'all';

		// Check for a WP 3.8+ function.
		if ( function_exists( 'wp_admin_bar_sidebar_toggle' ) ) {

			// Walker_Comment has changed to buffered output, so define args without
			// our custom walker. The built in walker works just fine now.
			$args = [
				'style'=> 'ol',
				'type'=> $comment_type,
				'callback' => 'commentpress_comments',
			];

		} else {

			/*
			 * Init new walker, because the original class did not include the
			 * option of using ordered lists <ol> instead of unordered ones <ul>
			 *
			 * @see https://github.com/WordPress/WordPress/blob/5828310157f1805a5f0976d76692c7023e8a895d/wp-includes/comment-template.php#L880
			 */
			$walker = new Walker_Comment_Press();

			// Define args.
			$args = [
				'walker' => $walker,
				'style'=> 'ol',
				'type'=> $comment_type,
				'callback' => 'commentpress_comments',
			];

		}

		// Get singular post type label.
		$current_type = get_post_type();
		$post_type = get_post_type_object( $current_type );

		/**
		 * Assign name of post type.
		 *
		 * @since 3.8.10
		 *
		 * @param str $singular_name The singular label for this post type.
		 * @param str $current_type The post type identifier.
		 * @return str $singular_name The modified label for this post type.
		 */
		$post_type_name = apply_filters( 'commentpress_lexia_post_type_name', $post_type->labels->singular_name, $current_type );

		// Init counter for text_signatures array.
		$sig_counter = 0;

		// Init array for tracking text sigs.
		$used_text_sigs = [];

		// Loop through each paragraph.
		foreach( $comments_sorted AS $text_signature => $comments ) {

			// Count comments.
			$comment_count = count( $comments );

			// Switch, depending on key.
			switch( $text_signature ) {

				// Whole page comments.
				case 'WHOLE_PAGE_OR_POST_COMMENTS':

					// Clear text signature.
					$text_sig = '';

					// Clear the paragraph number.
					$para_num = '';

					// Get the markup we need for this.
					$markup = commentpress_comments_by_para_format_whole( $post_type_name, $current_type, $comment_count );

					break;

				// Pingbacks and trackbacks.
				case 'PINGS_AND_TRACKS':

					// Set "unique-enough" text signature.
					$text_sig = 'pingbacksandtrackbacks';

					// Clear the paragraph number.
					$para_num = '';

					// Get the markup we need for this.
					$markup = commentpress_comments_by_para_format_pings( $comment_count );

					break;

				// Textblock comments.
				default:

					// Get text signature.
					$text_sig = $text_signature;

					// Paragraph number.
					$para_num = $sig_counter + ( $start_num - 1 );

					// Get the markup we need for this.
					$markup = commentpress_comments_by_para_format_block( $comment_count, $para_num );

			} // End switch.

			// Init no comment class.
			$no_comments_class = '';

			// Override if there are no comments (for print stylesheet to hide them).
			if ( $comment_count == 0 ) { $no_comments_class = ' class="no_comments"'; }

			// Exclude pings if there are none.
			if ( $comment_count == 0 AND $text_signature == 'PINGS_AND_TRACKS' ) {

				// Skip.

			} else {

				// Show heading.
				echo '<h3 id="para_heading-' . $text_sig . '"' . $no_comments_class . '><a class="comment_block_permalink" title="' . $markup['permalink_text'] . '" href="#para_heading-' . $text_sig . '">' . $markup['heading_text'] . '</a></h3>' . "\n\n";

				// Override if there are no comments (for print stylesheet to hide them).
				if ( $comment_count == 0 ) {
					$no_comments_class = ' no_comments';
				}

				// Open paragraph wrapper.
				echo '<div id="para_wrapper-' . $text_sig . '" class="paragraph_wrapper' . $no_comments_class . '">' . "\n\n";

				// Have we already used this text signature?
				if( in_array( $text_sig, $used_text_sigs ) ) {

					// Show some kind of message.
					// Should not be necessary now that we ensure unique text sigs.
					echo '<div class="reply_to_para" id="reply_to_para-' . $para_num . '">' . "\n" .
							'<p>' .
								__( 'It appears that this paragraph is a duplicate of a previous one.', 'commentpress-core' ) .
							'</p>' . "\n" .
						 '</div>' . "\n\n";

				} else {

					// If we have comments.
					if ( count( $comments ) > 0 ) {

						// Open commentlist.
						echo '<ol class="commentlist">' . "\n\n";

						// Use WP 2.7+ functionality.
						wp_list_comments( $args, $comments );

						// Close commentlist.
						echo '</ol>' . "\n\n";

					}

					/**
					 * Allow plugins to append to paragraph level comments.
					 *
					 * @param str $text_sig The text signature of the paragraph.
					 */
					do_action( 'commentpress_after_paragraph_comments', $text_sig );

					// Add to used array.
					$used_text_sigs[] = $text_sig;

					// Only add comment-on-para link if comments are open and it's not the pingback section.
					if ( 'open' == $post->comment_status AND $text_signature != 'PINGS_AND_TRACKS' ) {

						// If we have to log in to comment.
						if ( $login_to_comment ) {

							// The link text depending on whether we've got registration.
							if ( $registration ) {
								$prompt = sprintf(
									__( 'Create an account to leave a comment on %s', 'commentpress-core' ),
									$markup['entity_text']
								);
							} else {
								$prompt = sprintf(
									__( 'Login to leave a comment on %s', 'commentpress-core' ),
									$markup['entity_text']
								);
							}

							/**
							 * Filter the prompt text.
							 *
							 * @since 3.9
							 *
							 * @param str $prompt The link text when login is required.
							 * @param bool $registration True if registration is open, false otherwise.
							 */
							$prompt = apply_filters( 'commentpress_reply_to_prompt_text', $prompt, $registration );

							// Leave comment link.
							echo '<div class="reply_to_para" id="reply_to_para-' . $para_num . '">' . "\n" .
									'<p><a class="reply_to_para" rel="nofollow" href="' . $redirect . '">' .
										$prompt .
									'</a></p>' . "\n" .
								 '</div>' . "\n\n";

						} else {

							// Construct onclick content.
							$onclick = "return addComment.moveFormToPara( '$para_num', '$text_sig', '$post->ID' )";

							// Construct onclick attribute.
							$onclick = apply_filters(
								'commentpress_reply_to_para_link_onclick',
								' onclick="' . $onclick . '"'
							);

							// Just show replytopara.
							$query = remove_query_arg( [ 'replytocom' ] );

							// Add param to querystring.
							$query = esc_url(
								add_query_arg(
									[ 'replytopara' => $para_num ],
									$query
								)
							);

							// Construct href attribute.
							$href = apply_filters(
								'commentpress_reply_to_para_link_href',
								$query . '#respond', // Add respond ID
								$text_sig
							);

							// Construct link content.
							$link_content = sprintf(
								__( 'Leave a comment on %s', 'commentpress-core' ),
								$markup['entity_text']
							);

							// Allow overrides.
							$link_content = apply_filters(
								'commentpress_reply_to_para_link_text',
								$link_content,
								$markup['entity_text']
							);

							// Leave comment link.
							echo '<div class="reply_to_para" id="reply_to_para-' . $para_num . '">' . "\n" .
									'<p><a class="reply_to_para" href="' . $href . '"' . $onclick . '>' .
										$link_content .
									'</a></p>' . "\n" .
								 '</div>' . "\n\n";

						}

					}

				}

				/**
				 * Allow plugins to append to paragraph wrappers.
				 *
				 * @param str $text_sig The text signature of the paragraph.
				 */
				do_action( 'commentpress_after_paragraph_wrapper', $text_sig );

				// Close paragraph wrapper.
				echo '</div>' . "\n\n\n\n";

			}

			// Increment signature array counter.
			$sig_counter++;

		} // End comments-per-para loop

	}

	// Allow plugins to follow comments.
	do_action( 'commentpress_after_scrollable_comments' );

}
endif; // End commentpress_get_comments_by_para



/**
 * Modified HTML comment list class.
 *
 * @since 3.0
 *
 * @package WordPress
 * @uses Walker
 * @since unknown
 */
class Walker_Comment_Press extends Walker_Comment {

	/**
	 * @see Walker_Comment::start_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of comment.
	 * @param array $args Uses 'style' argument for type of HTML list.
	 */
	public function start_lvl( &$output, $depth = 0, $args = [] ) {

		// Store depth.
		$GLOBALS['comment_depth'] = $depth + 1;

		// Open children if necessary.
		switch ( $args['style'] ) {

			case 'div':
				break;

			case 'ol':
				echo '<ol class="children">' . "\n";
				break;

			default:
			case 'ul':
				echo '<ul class="children">' . "\n";
				break;
		}

	}

}



if ( ! function_exists( 'commentpress_comment_form_title' ) ):
/**
 * Alternative to the built-in WP function.
 *
 * @since 3.0
 *
 * @param str $no_reply_text
 * @param str $reply_to_comment_text
 * @param str $reply_to_para_text
 * @param str $link_to_parent
 */
function commentpress_comment_form_title( $no_reply_text = '', $reply_to_comment_text = '', $reply_to_para_text = '', $link_to_parent = TRUE ) {

	// Sanity checks.
	if ( $no_reply_text == '' ) { $no_reply_text = __( 'Leave a reply', 'commentpress-core' ); }
	if ( $reply_to_comment_text == '' ) { $reply_to_comment_text = __( 'Leave a reply to %s', 'commentpress-core' ); }
	if ( $reply_to_para_text == '' ) { $reply_to_para_text = __( 'Leave a comment on %s', 'commentpress-core' ); }

	// Declare access to globals.
	global $comment, $commentpress_core;

	// Get comment ID to reply to from URL query string.
	$reply_to_comment_id = isset( $_GET['replytocom'] ) ? (int) $_GET['replytocom'] : 0;

	// Get paragraph number to reply to from URL query string.
	$reply_to_para_id = isset( $_GET['replytopara'] ) ? (int) $_GET['replytopara'] : 0;

	// If we have no comment ID AND no paragraph ID to reply to.
	if ( $reply_to_comment_id == 0 AND $reply_to_para_id === 0 ) {

		// Write default title to page.
		echo $no_reply_text;

	} else {

		// If we have a comment ID AND NO paragraph ID to reply to.
		if ( $reply_to_comment_id !== 0 AND $reply_to_para_id === 0 ) {

			// Get comment.
			$comment = get_comment( $reply_to_comment_id );

			// Get link to comment.
			$author = ( $link_to_parent ) ?
				'<a href="#comment-' . get_comment_ID() . '">' . get_comment_author() . '</a>' :
				get_comment_author();

			// Write to page.
			printf( $reply_to_comment_text, $author );

		} else {

			// Get paragraph text signature.
			$text_sig = $commentpress_core->get_text_signature( $reply_to_para_id );

			// Get link to paragraph.
			if ( $link_to_parent ) {

				// Construct link text.
				$para_text = sprintf(
					_x( '%1$s %2$s', 'The first substitution is the block name e.g. "paragraph". The second is the numeric comment count.', 'commentpress-core' ),
					ucfirst( $commentpress_core->parser->lexia_get() ),
					$reply_to_para_id
				);

				// Construct paragraph.
				$paragraph = '<a href="#para_heading-' . $text_sig . '">' . $para_text . '</a>';

			} else {

				// Construct paragraph without link.
				$paragraph = sprintf(
					_x( '%1$s %2$s', 'The first substitution is the block name e.g. "paragraph". The second is the numeric comment count.', 'commentpress-core' ),
					ucfirst( $commentpress_core->parser->lexia_get() ),
					$para_num
				);

			}

			// Write to page.
			printf( $reply_to_para_text, $paragraph );

		}

	}

}
endif; // End commentpress_comment_form_title



if ( ! function_exists( 'commentpress_comment_reply_link' ) ):
/**
 * Alternative to the built-in WP function.
 *
 * @since 3.0
 *
 * @param array $args The reply links arguments.
 * @param object $comment The comment.
 * @param object $post The post.
 */
function commentpress_comment_reply_link( $args = [], $comment = null, $post = null ) {

	// Set some defaults.
	$defaults = [
		'add_below' => 'comment',
		'respond_id' => 'respond',
		'reply_text' => __( 'Reply', 'commentpress-core' ),
		'login_text' => __( 'Log in to Reply', 'commentpress-core' ),
		'depth' => 0,
		'before' => '',
		'after' => '',
	];

	// Parse them.
	$args = wp_parse_args( $args, $defaults );

	if ( 0 == $args['depth'] || $args['max_depth'] <= $args['depth'] ) {
		return;
	}

	// Convert to vars.
	extract( $args, EXTR_SKIP );

	// Get the obvious.
	$comment = get_comment( $comment );
	$post = get_post( $post );

	// Kick out if comments closed.
	if ( 'open' != $post->comment_status ) return false;

	// Init link.
	$link = '';

	// If we have to log in to comment.
	if ( get_option( 'comment_registration' ) AND ! is_user_logged_in() ) {

		// Construct link.
		$link = '<a rel="nofollow" href="' . site_url( 'wp-login.php?redirect_to=' . get_permalink() ) . '">' . $login_text . '</a>';

	} else {

		// Just show replytocom.
		$query = remove_query_arg( [ 'replytopara' ], get_permalink( $post->ID ) );

		// Define query string.
		$addquery = esc_url(
			add_query_arg(
				[ 'replytocom' => $comment->comment_ID ],
				$query
			)

		);

		// Define link.
		$link = "<a rel='nofollow' class='comment-reply-link' href='" . $addquery . "#" . $respond_id . "' onclick='return addComment.moveForm(\"$add_below-$comment->comment_ID\", \"$comment->comment_ID\", \"$respond_id\", \"$post->ID\", \"$comment->comment_signature\")'>$reply_text</a>";

	}

	// --<
	return apply_filters( 'comment_reply_link', $before . $link . $after, $args, $comment, $post );

}
endif; // End commentpress_comment_reply_link



if ( ! function_exists( 'commentpress_comments' ) ):
/**
 * Custom comments display function.
 *
 * @since 3.0
 *
 * @param object $comment The comment object.
 * @param array $args The comment arguments.
 * @param int $depth The comment depth.
 */
function commentpress_comments( $comment, $args, $depth ) {

	// Build comment as html.
	echo commentpress_get_comment_markup( $comment, $args, $depth );

}
endif; // End commentpress_comments



if ( ! function_exists( 'commentpress_get_comment_markup' ) ):
/**
 * Wrap comment in its markup.
 *
 * @since 3.0
 *
 * @param object $comment The comment object.
 * @param array $args The comment arguments.
 * @param int $depth The comment depth.
 * @return str $html The comment markup.
 */
function commentpress_get_comment_markup( $comment, $args, $depth ) {

	// Enable WordPress API on comment.
	$GLOBALS['comment'] = $comment;

	// Was it a registered user?
	if ( $comment->user_id != '0' ) {

		// Get user details.
		$user = get_userdata( $comment->user_id );

		// Get user link.
		$user_link = commentpress_get_user_link( $user, $comment );

		// Construct author citation.
		$author = ( $user_link != '' AND $user_link != 'http://' ) ?
					'<cite class="fn"><a href="' . $user_link . '">' . get_comment_author() . '</a></cite>' :
					 '<cite class="fn">' . get_comment_author() . '</cite>';

	} else {

		// Construct link to commenter url for unregistered users.
		if (
			$comment->comment_author_url != '' AND
			$comment->comment_author_url != 'http://' AND
			$comment->comment_approved != '0'
		) {
			$author = '<cite class="fn"><a href="' . $comment->comment_author_url . '">' . get_comment_author() . '</a></cite>';
		} else {
			$author = '<cite class="fn">' . get_comment_author() . '</cite>';
		}

	}

	// Check moderation status.
	if ( $comment->comment_approved == '0' ) {
		$comment_text = '<p><em>' . __( 'Comment awaiting moderation', 'commentpress-core' ) . '</em></p>';
	} else {
		$comment_text = get_comment_text();
	}

	// Empty reply div by default.
	$comment_reply = '';

	// Enable access to post.
	global $post;

	// Can we reply?
	if (

		// Not if comments are closed.
		$post->comment_status == 'open' AND

		// We don't want reply to on pingbacks.
		$comment->comment_type != 'pingback' AND

		// We don't want reply to on trackbacks.
		$comment->comment_type != 'trackback' AND

		// Nor on unapproved comments.
		$comment->comment_approved == '1'

	) {

		// Are we threading comments?
		if ( get_option( 'thread_comments', false ) ) {

			// Custom comment_reply_link.
			$comment_reply = commentpress_comment_reply_link( array_merge(
				$args,
				[
					'reply_text' => sprintf( __( 'Reply to %s', 'commentpress-core' ), get_comment_author() ),
					'depth' => $depth,
					'max_depth' => $args['max_depth'],
				]
			) );

			// Wrap in div.
			$comment_reply = '<div class="reply">' . $comment_reply . '</div><!-- /reply -->';

		}

	}

	// Init edit link.
	$editlink = '';

	// If logged in and has capability.
	if (
		is_user_logged_in() AND
		current_user_can( 'edit_comment', $comment->comment_ID )
	) {

		// Set default edit link title text.
		$edit_title_text = apply_filters(
			'cp_comment_edit_link_title_text',
			__( 'Edit this comment', 'commentpress-core' )
		);

		// Set default edit link text.
		$edit_text = apply_filters(
			'cp_comment_edit_link_text',
			__( 'Edit', 'commentpress-core' )
		);

		// Get edit comment link.
		$editlink = '<span class="alignright comment-edit"><a class="comment-edit-link" href="' . get_edit_comment_link() . '" title="' . $edit_title_text . '">' . $edit_text . '</a></span>';

		// Add a filter for plugins.
		$editlink = apply_filters( 'cp_comment_edit_link', $editlink, $comment );

	}

	// Add a nopriv filter for plugins.
	$editlink = apply_filters( 'cp_comment_action_links', $editlink, $comment );

	// Get comment class(es).
	$comment_class = comment_class( null, $comment->comment_ID, $post->ID, false );

	// If orphaned, add class to identify as such.
	$comment_orphan = ( isset( $comment->orphan ) ) ? ' comment-orphan' : '';

	// Construct permalink.
	$comment_permalink = sprintf( __( '%1$s at %2$s', 'commentpress-core' ), get_comment_date(), get_comment_time() );

	// Stripped source.
	$html = '
<li id="li-comment-' . $comment->comment_ID . '" ' . $comment_class . '>
<div class="comment-wrapper">
<div id="comment-' . $comment->comment_ID . '">



<div class="comment-identifier' . $comment_orphan . '">
' . apply_filters( 'commentpress_comment_identifier_prepend', '', $comment ) . '
' . get_avatar( $comment, $size='32' ) . '
' . $editlink . '
' . $author . '
<a class="comment_permalink" href="' . htmlspecialchars( get_comment_link() ) . '" title="' . __( 'Permalink for this comment', 'commentpress-core' ) . '"><span class="comment_permalink_copy"></span>' . $comment_permalink . '</a>
' . apply_filters( 'commentpress_comment_identifier_append', '', $comment ) . '
</div><!-- /comment-identifier -->



<div class="comment-content' . $comment_orphan . '">
' . apply_filters( 'comment_text', $comment_text ) . '
</div><!-- /comment-content -->



' . $comment_reply . '



</div><!-- /comment-' . $comment->comment_ID . ' -->
</div><!-- /comment-wrapper -->
';

	// --<
	return $html;

}
endif; // End commentpress_get_comment_markup



if ( ! function_exists( 'commentpress_get_full_name' ) ):
/**
 * Utility to concatenate names.
 *
 * @since 3.0
 *
 * @param str $forename The WordPress user's first name.
 * @param str $surname The WordPress user's last name.
 * @param str $fullname The WordPress user's full name.
 */
function commentpress_get_full_name( $forename, $surname ) {

	// Init return.
	$fullname = '';

	// Add forename.
	if ( $forename != '' ) { $fullname .= $forename; }

	// Add surname.
	if ( $surname != '' ) { $fullname .= ' ' . $surname; }

	// Strip any whitespace.
	$fullname = trim( $fullname );

	// --<
	return $fullname;

}
endif; // End commentpress_get_full_name



if ( ! function_exists( 'commentpress_excerpt_length' ) ):
/**
 * Utility to define length of excerpt.
 *
 * @since 3.0
 *
 * @return int $length The length of the excerpt.
 */
function commentpress_excerpt_length() {

	// Declare access to globals.
	global $commentpress_core;

	// Is the plugin active?
	if ( ! is_object( $commentpress_core ) ) {

		// --<
		return 55; // WordPress default.

	}

	// Get length of excerpt from option.
	$length = $commentpress_core->db->option_get( 'cp_excerpt_length' );

	// --<
	return $length;

}
endif; // End commentpress_excerpt_length

// Add filter for excerpt length.
add_filter( 'excerpt_length', 'commentpress_excerpt_length' );



if ( ! function_exists( 'commentpress_add_link_css' ) ):
/**
 * Utility to add button css class to blog nav links.
 *
 * @since 3.0
 *
 * @param str $link The existing link.
 * @return str $link The link with the custom class attribute added.
 */
function commentpress_add_link_css( $link ) {

	// Add CSS.
	$link = str_replace( '<a ', '<a class="css_btn" ', $link );

	// --<
	return $link;

}
endif; // End commentpress_add_link_css

// Add filter for next/previous links
add_filter( 'previous_post_link', 'commentpress_add_link_css' );
add_filter( 'next_post_link', 'commentpress_add_link_css' );



if ( ! function_exists( 'commentpress_get_link_css' ) ):
/**
 * Utility to add button css class to blog nav links.
 *
 * @since 3.0
 *
 * @return str $link The custom class attribute.
 */
function commentpress_get_link_css() {

	// Add CSS.
	$link = 'class="css_btn"';

	// --<
	return $link;

}
endif; // End commentpress_get_link_css

// Add filter for next/previous posts links.
add_filter( 'previous_posts_link_attributes', 'commentpress_get_link_css' );
add_filter( 'next_posts_link_attributes', 'commentpress_get_link_css' );



if ( ! function_exists( 'commentpress_multipage_comment_link' ) ):
/**
 * Filter comment permalinks for multipage posts.
 *
 * @since 3.5
 *
 * @param str $link The existing comment link.
 * @param object $comment The comment object.
 * @param array $args An array of extra arguments.
 * @return str $link The modified comment link.
 */
function commentpress_multipage_comment_link( $link, $comment, $args ) {

	// Get multipage and post.
	global $multipage, $post;

	// Are there multiple (sub)pages?
	//if ( is_object( $post ) AND $multipage ) {

		// Exclude page level comments.
		if ( $comment->comment_signature != '' ) {

			// Init page num.
			$page_num = 1;

			// Set key.
			$key = '_cp_comment_page';

			// If the custom field already has a value.
			if ( get_comment_meta( $comment->comment_ID, $key, true ) != '' ) {

				// Get the page number.
				$page_num = get_comment_meta( $comment->comment_ID, $key, true );

			}

			// Get current comment info.
			$comment_path_info = parse_url( $link );

			// Set comment path.
			$link = commentpress_get_post_multipage_url( $page_num, get_post( $comment->comment_post_ID ) ) . '#' . $comment_path_info['fragment'];

		}

	//}

	// --<
	return $link;

}
endif; // End commentpress_multipage_comment_link

// Add filter for the above.
add_filter( 'get_comment_link', 'commentpress_multipage_comment_link', 10, 3 );



if ( ! function_exists( 'commentpress_get_post_multipage_url' ) ):
/**
 * Get the URL fo a page in a multipage context.
 *
 * Copied from wp-includes/post-template.php _wp_link_page()
 *
 * @since 3.5
 *
 * @param int $i The page number.
 * @return str $url The URL to the subpage.
 */
function commentpress_get_post_multipage_url( $i, $post = '' ) {

	// If we have no passed value.
	if ( $post == '' ) {

		// We assume we're in the loop.
		global $post, $wp_rewrite;

		if ( 1 == $i ) {
			$url = get_permalink();
		} else {
			if ( '' == get_option( 'permalink_structure' ) || in_array( $post->post_status, [ 'draft', 'pending' ] ) )
				$url = add_query_arg( 'page', $i, get_permalink() );
			elseif ( 'page' == get_option( 'show_on_front' ) AND get_option( 'page_on_front' ) == $post->ID )
				$url = trailingslashit( get_permalink() ) . user_trailingslashit( "$wp_rewrite->pagination_base/" . $i, 'single_paged' );
			else
				$url = trailingslashit( get_permalink() ) . user_trailingslashit( $i, 'single_paged' );
		}

	} else {

		// Use passed post object.
		if ( 1 == $i ) {
			$url = get_permalink( $post->ID );
		} else {
			if ( '' == get_option( 'permalink_structure' ) || in_array( $post->post_status, [ 'draft', 'pending' ] ) )
				$url = add_query_arg( 'page', $i, get_permalink( $post->ID ) );
			elseif ( 'page' == get_option( 'show_on_front' ) AND get_option( 'page_on_front' ) == $post->ID )
				$url = trailingslashit( get_permalink( $post->ID ) ) . user_trailingslashit( "$wp_rewrite->pagination_base/" . $i, 'single_paged' );
			else
				$url = trailingslashit( get_permalink( $post->ID ) ) . user_trailingslashit( $i, 'single_paged' );
		}

	}

	return esc_url( $url );

}
endif; // End commentpress_get_post_multipage_url



if ( ! function_exists( 'commentpress_multipager' ) ):
/**
 * Create sane links between pages.
 *
 * @since 3.5
 *
 * @return str $page_links The next page and previous page links.
 */
function commentpress_multipager() {

	// Set default behaviour.
	$defaults = [
		'before' => '<div class="multipager">',
		'after' => '</div>',
		'link_before' => '',
		'link_after' => '',
		'next_or_number' => 'next',
		'nextpagelink' => '<span class="alignright">' . __( 'Next page', 'commentpress-core' ) . ' &raquo;</span>',
		'previouspagelink' => '<span class="alignleft">&laquo; ' . __( 'Previous page', 'commentpress-core' ) . '</span>',
		'pagelink' => '%',
		'more_file' => '',
		'echo' => 0,
	];

	// Get page links.
	$page_links = wp_link_pages( $defaults );

	// Add separator when there are two links.
	$page_links = str_replace(
		'a><a',
		'a> <span class="multipager_sep">|</span> <a',
		$page_links
	);

	// Get page links.
	$page_links .= wp_link_pages( [
		'before' => '<div class="multipager multipager_all"><span>' . __( 'Pages: ', 'commentpress-core' ) . '</span>',
		'after' => '</div>',
		'pagelink' => '<span class="multipager_link">%</span>',
		'echo' => 0,
	] );

	// --<
	return $page_links;

}
endif; // End commentpress_multipager



if ( ! function_exists( 'commentpress_add_wp_editor' ) ):
/**
 * Adds our styles to the TinyMCE editor.
 *
 * @since 3.5
 *
 * @param str $mce_css The default TinyMCE stylesheets as set by WordPress.
 * @return str $mce_css The list of stylesheets with ours added.
 */
function commentpress_add_wp_editor() {

	// Init option.
	$rich_text = false;

	// Kick out if wp_editor doesn't exist.
	// TinyMCE will be handled by including the script using the pre- wp_editor() method
	if ( ! function_exists( 'wp_editor' ) ) {
		return false;
	}

	// Kick out if plugin not active.
	global $commentpress_core;
	if ( ! is_object( $commentpress_core ) ) {
		return false;
	}

	// Only allow through if plugin says so.
	if ( ! $commentpress_core->display->is_tinymce_allowed() ) {
		return false;
	}

	// Add our buttons.
	$mce_buttons = apply_filters(

		// Filter for plugins.
		'cp_tinymce_buttons',

		// Basic buttons.
		[
			'bold',
			'italic',
			'underline',
			'|',
			'bullist',
			'numlist',
			'|',
			'link',
			'unlink',
			'|',
			'removeformat',
			'fullscreen',
		]

	);

	// Allow media buttons setting to be overridden.
	$media_buttons = apply_filters( 'commentpress_rte_media_buttons', true );

	// Access WP version.
	global $wp_version;

	// If greater than 3.8.
	if ( version_compare( $wp_version, '3.8.9999', '>' ) ) {

		// TinyMCE 4 - allow tinymce config to be overridden.
		$tinymce_config = apply_filters(
			'commentpress_rte_tinymce',
			[
				'theme' => 'modern',
				'statusbar' => false,
			]
		);

		// No need for editor CSS.
		$editor_css = '';

	} else {

		// TinyMCE 3 - allow tinymce config to be overridden.
		$tinymce_config = apply_filters(
			'commentpress_rte_tinymce',
			[
				'theme' => 'advanced',
				'theme_advanced_buttons1' => implode( ',', $mce_buttons ),
				'theme_advanced_statusbar_location' => 'none',
			]
		);

		// Use legacy editor CSS.
		$editor_css = '
			<style type="text/css">
				.wp_themeSkin iframe
				{
					background: #fff;
				}
			</style>
		';

	}

	// Allow quicktags setting to be overridden.
	$quicktags = apply_filters(
		'commentpress_rte_quicktags',
		[
			'buttons' => 'strong,em,ul,ol,li,link,close',
		]
	);

	// Our settings.
	$settings = [

		// Configure comment textarea.
		'media_buttons' => $media_buttons,
		'textarea_name' => 'comment',
		'textarea_rows' => 10,

		// Might as well start with teeny.
		'teeny' => true,

		// Give the iframe a white background.
		'editor_css' => $editor_css,

		// Configure TinyMCE.
		'tinymce' => $tinymce_config,

		// Configure Quicktags.
		'quicktags' => $quicktags,

	];

	// Create editor.
	wp_editor(
		'', // Initial content.
		'comment', // ID of comment textarea.
		$settings
	);

	// Access WP version.
	global $wp_version;

	// Add styles.
	wp_enqueue_style(
		'commentpress-editor-css',
		wp_admin_css_uri( 'css/edit' ),
		[ 'dashicons', 'open-sans' ],
		$wp_version, // Version.
		'all' // Media.
	);

	// Don't show textarea.
	return true;

}
endif; // End commentpress_add_wp_editor



if ( ! function_exists( 'commentpress_assign_default_editor' ) ):
/**
 * Makes TinyMCE the default editor on the front end.
 *
 * @since 3.0
 *
 * @param str $r The default editor as set by WordPress.
 * @return str 'tinymce' our overridden default editor.
 */
function commentpress_assign_default_editor( $r ) {

	// Only on front-end.
	if ( is_admin() ) return $r;

	// Always return 'tinymce' as the default editor, or else the comment form will not show up!

	// --<
	return 'tinymce';

}
endif; // End commentpress_assign_default_editor

// Add filter for the above.
add_filter( 'wp_default_editor', 'commentpress_assign_default_editor', 10, 1 );



if ( ! function_exists( 'commentpress_add_tinymce_styles' ) ):
/**
 * Adds our styles to the TinyMCE editor.
 *
 * @since 3.0
 *
 * @param str $mce_css The default TinyMCE stylesheets as set by WordPress.
 * @return str $mce_css The list of stylesheets with ours added.
 */
function commentpress_add_tinymce_styles( $mce_css ) {

	// Only on front-end.
	if ( is_admin() ) return $mce_css;

	// Add comma if not empty.
	if ( ! empty( $mce_css ) ) { $mce_css .= ','; }

	// Add our editor styles.
	$mce_css .= get_template_directory_uri() . '/assets/css/comment-form.css';

	// --<
	return $mce_css;

}
endif; // End commentpress_add_tinymce_styles

// Add filter for the above.
add_filter( 'mce_css', 'commentpress_add_tinymce_styles' );



if ( ! function_exists( 'commentpress_add_tinymce_nextpage_button' ) ):
/**
 * Adds the Next Page button to the TinyMCE editor.
 *
 * @since 3.5
 *
 * @param array $buttons The default TinyMCE buttons as set by WordPress.
 * @return array $buttons The buttons with More removed.
 */
function commentpress_add_tinymce_nextpage_button( $buttons ) {

	// Only on back-end.
	if ( ! is_admin() ) return $buttons;

	// Try and place Next Page after More button.
	$pos = array_search( 'wp_more', $buttons, true );

	// Is it there?
	if ( $pos !== false ) {

		// Get array up to that point.
		$tmp_buttons = array_slice( $buttons, 0, $pos + 1 );

		// Add Next Page button.
		$tmp_buttons[] = 'wp_page';

		// Recombine.
		$buttons = array_merge( $tmp_buttons, array_slice( $buttons, $pos + 1 ) );

	}

	// --<
	return $buttons;

}
endif; // End commentpress_add_tinymce_nextpage_button

// Add filter for the above.
add_filter( 'mce_buttons', 'commentpress_add_tinymce_nextpage_button' );



if ( ! function_exists( 'commentpress_assign_editor_buttons' ) ):
/**
 * Assign our buttons to TinyMCE in teeny mode.
 *
 * @since 3.5
 *
 * @param array $buttons The default editor buttons as set by WordPress.
 * @return array The overridden editor buttons.
 */
function commentpress_assign_editor_buttons( $buttons ) {

	// Basic buttons.
	return [
		'bold',
		'italic',
		'underline',
		'|',
		'bullist',
		'numlist',
		'|',
		'link',
		'unlink',
		'|',
		'removeformat',
		'fullscreen',
	];

}
endif; // End commentpress_assign_editor_buttons

// Access WP version.
global $wp_version;

// If greater than 3.8.
if ( version_compare( $wp_version, '3.8.9999', '>' ) ) {
	add_filter( 'teeny_mce_buttons', 'commentpress_assign_editor_buttons' );
}



if ( ! function_exists( 'commentpress_comment_post_redirect' ) ):
/**
 * Filter comment post redirects for multipage posts.
 *
 * @since 3.5
 *
 * @param str $link The link to the comment.
 * @param object $comment The comment object.
 */
function commentpress_comment_post_redirect( $link, $comment ) {

	// Get URL of the page that submitted the comment.
	$page_url = $_SERVER['HTTP_REFERER'];

	// Get anchor position.
	$hash = strpos( $page_url, '#' );

	// Well, do we have an anchor?
	if ( $hash !== false ) {

		// Yup, so strip it.
		$page_url = substr( $page_url, 0, $hash );

	}

	// Assume not AJAX.
	$ajax_token = '';

	// Is this an AJAX comment form submission?
	if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
		if ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {

			// Yes, it's AJAX - some browsers cache POST, so invalidate.
			$ajax_token = '?cachebuster=' . time();

			// But, test for pretty permalinks.
			if ( false !== strpos( $page_url, '?' ) ) {

				// Pretty permalinks are off.
				$ajax_token = '&cachebuster=' . time();

			}

		}

	}

	// Construct cachebusting comment redirect.
	$link = $page_url . $ajax_token . '#comment-' . $comment->comment_ID;

	// --<
	return $link;

}
endif; // End commentpress_comment_post_redirect

// Add filter for the above, making it run early so it can be overridden by AJAX commenting.
add_filter( 'comment_post_redirect', 'commentpress_comment_post_redirect', 4, 2 );



if ( ! function_exists( 'commentpress_image_caption_shortcode' ) ):
/**
 * Rebuild caption shortcode output.
 *
 * @since 3.5
 *
 * @param array $empty WordPress passes '' as the first param!
 * @param array $attr Attributes attributed to the shortcode.
 * @param str $content Optional. Shortcode content.
 * @return str $caption The modified caption
 */
function commentpress_image_caption_shortcode( $empty = null, $attr, $content ) {

	// Get our shortcode vars.
	extract( shortcode_atts( [
		'id'	=> '',
		'align'	=> 'alignnone',
		'width'	=> '',
		'caption' => '',
	], $attr ) );

	if ( 1 > (int) $width || empty( $caption ) ) {
		return $content;
	}

	// Sanitise ID.
	if ( $id ) $id = 'id="' . esc_attr( $id ) . '" ';

	// Add space prior to alignment.
	$alignment = ' ' . esc_attr( $align );

	// Get width.
	$width = (0 + (int) $width);

	// Sanitise caption.
	$caption = wp_kses( $caption,

		// Allow a few tags.
		[
			'em' => [],
			'strong' => [],
			'a' => [ 'href' ],
		]

	);

	// Force balance those tags.
	$caption = balanceTags( $caption, true );

	// Construct.
	$caption = '<!-- cp_caption_start -->' .
				'<span class="captioned_image' . $alignment . '" style="width: ' . $width . 'px">' .
					'<span ' . $id . ' class="wp-caption">' . do_shortcode( $content ) . '</span>' .
					'<small class="wp-caption-text">' . $caption . '</small>' .
				'</span>' .
				'<!-- cp_caption_end -->';

	// --<
	return $caption;

}
endif; // End commentpress_image_caption_shortcode

// Add a filter for the above.
add_filter( 'img_caption_shortcode', 'commentpress_image_caption_shortcode', 10, 3 );



if ( ! function_exists( 'commentpress_add_commentblock_button' ) ):
/**
 * Add filters for adding our custom TinyMCE button.
 *
 * @since 3.3
 */
function commentpress_add_commentblock_button() {

	// Only on back-end.
	if ( ! is_admin() ) return;

	// Don't bother doing this stuff if the current user lacks permissions.
	if ( ! current_user_can( 'edit_posts' ) AND ! current_user_can( 'edit_pages' ) ) {
		return;
	}

	// Add only if user can edit in Rich-text Editor mode.
	if ( get_user_option( 'rich_editing' ) == 'true' ) {
		add_filter( 'mce_external_plugins', 'commentpress_add_commentblock_tinymce_plugin' );
		add_filter( 'mce_buttons', 'commentpress_register_commentblock_button' );
	}

}
endif; // End commentpress_add_commentblock_button

// Init process for button control.
add_action( 'init', 'commentpress_add_commentblock_button' );



if ( ! function_exists( 'commentpress_register_commentblock_button' ) ):
/**
 * Add our custom TinyMCE button.
 *
 * @since 3.3
 *
 * @param array $buttons The existing button array.
 * @return array $buttons The modified button array.
 */
function commentpress_register_commentblock_button( $buttons ) {

	// Add our button to the editor button array.
	array_push( $buttons, '|', 'commentblock' );

	// --<
	return $buttons;

}
endif; // End commentpress_register_commentblock_button



if ( ! function_exists( 'commentpress_add_commentblock_tinymce_plugin' ) ):
/**
 * Load the TinyMCE plugin : cp_editor_plugin.js
 *
 * @since 3.3
 *
 * @param array $plugin_array The existing TinyMCE plugin array.
 * @return array $plugin_array The modified TinyMCE plugin array.
 */
function commentpress_add_commentblock_tinymce_plugin( $plugin_array ) {

	// Add comment block.
	$plugin_array['commentblock'] = get_template_directory_uri() . '/assets/js/tinymce/cp_editor_plugin.js';

	// --<
	return $plugin_array;

}
endif; // End commentpress_add_commentblock_tinymce_plugin



if ( ! function_exists( 'commentpress_trap_empty_search' ) ):
/**
 * Trap empty search queries and redirect.
 *
 * @since 3.3
 *
 * @return str $template The path to the search template.
 */
function commentpress_trap_empty_search() {

	// Take care of empty searches.
	if ( isset( $_GET['s'] ) AND empty( $_GET['s'] ) ) {

		// Send to search page.
		return locate_template( [ 'search.php' ] );

	}

}
endif; // End commentpress_trap_empty_search

// Front_page_template filter is deprecated in WP 3.2+.
if ( version_compare( $wp_version, '3.2', '>=' ) ) {

	// Add filter for the above.
	add_filter( 'home_template', 'commentpress_trap_empty_search' );

} else {

	// Retain old filter for earlier versions.
	add_filter( 'front_page_template', 'commentpress_trap_empty_search' );

}



if ( ! function_exists( 'commentpress_amend_search_query' ) ):
/**
 * Restrict search query to pages only.
 *
 * @since 3.3
 *
 * @param object $query The query object, passed by reference.
 */
function commentpress_amend_search_query( &$query ) {

	// Restrict to search outside admin (BuddyPress does a redirect to the blog page and so $query->is_search is not set).
	if ( ! is_admin() AND isset( $query->query['s'] ) AND ! empty( $query->query['s'] ) ) {

		// Is this a BuddyPress search on the main BuddyPress instance?
		if ( function_exists( 'bp_search_form_type_select' ) AND bp_is_root_blog() ) {

			// Search posts and pages.
			$query->set( 'post_type', apply_filters( 'commentpress_amend_search_query_post_types', [ 'post', 'page' ] ) );

			// Declare access to globals.
			global $commentpress_core;

			// If we have the plugin enabled.
			if ( is_object( $commentpress_core ) ) {

				// Get special pages array, if it's there.
				$special_pages = $commentpress_core->db->option_get( 'cp_special_pages' );

				// Do we have an array?
				if ( is_array( $special_pages ) ) {

					// Exclude them.
					$query->set( 'post__not_in', apply_filters( 'commentpress_amend_search_query_exclusions', $special_pages ) );

				}

			}

		}

	}

}
endif; // End commentpress_amend_search_query

// Add filter for search query modification.
add_filter( 'pre_get_posts', 'commentpress_amend_search_query' );



if ( ! function_exists( 'commentpress_amend_password_form' ) ):
/**
 * Adds some style to the password form by adding a class to it.
 *
 * @since 3.3
 *
 * @param str $output The existing password form.
 * @return str $output The modified password form.
 */
function commentpress_amend_password_form( $output ) {

	// Add css class to form.
	$output = str_replace( '<form ', '<form class="post_password_form" ', $output );

	// Add css class to text field.
	$output = str_replace( '<input name="post_password" ', '<input class="post_password_field" name="post_password" ', $output );

	// Add css class to submit button.
	$output = str_replace( '<input type="submit" ', '<input class="post_password_button" type="submit" ', $output );

	// --<
	return $output;

}
endif; // End commentpress_amend_password_form

// Add filter for the above.
add_filter( 'the_password_form', 'commentpress_amend_password_form' );



if ( ! function_exists( 'commentpress_widgets_init' ) ):
/**
 * Register CommentPress widgets.
 *
 * Widget areas (dynamic sidebars) are defined on a per-theme basis in their
 * functions.php file or similar.
 *
 * @since 3.3
 */
function commentpress_widgets_init() {

	// Load license widget definition.
	require( COMMENTPRESS_PLUGIN_PATH . 'commentpress-core/assets/widgets/widget-license.php' );

	// Register license widget.
	register_widget( 'Commentpress_License_Widget' );

}
endif; // End commentpress_widgets_init

// Add action for the above
add_action( 'widgets_init', 'commentpress_widgets_init' );



if ( ! function_exists( 'commentpress_license_image_css' ) ):
/**
 * Amend display of license plugin image.
 *
 * @since 3.3
 *
 * @return str The CSS for amending the license plugin image.
 */
function commentpress_license_image_css() {

	// Give a bit more room to the image.
	return 'display: block; float: left; margin: 0 6px 3px 0;';

}
endif; // End commentpress_license_image_css

add_action( 'license_img_style', 'commentpress_license_image_css' );



if ( ! function_exists( 'commentpress_license_widget_compat' ) ):
/**
 * Remove license from footer when widget not active.
 *
 * This is because wp_footer() is not inside #footer.
 *
 * @since 3.3
 */
function commentpress_license_widget_compat() {

	// If the widget is not active, (i.e. the plugin is installed but the widget has not been
	// dragged to a sidebar), then DO NOT display the license in the footer as a default.
	if ( ! is_active_widget( false, false, 'license-widget', true ) ) {
		remove_action( 'wp_footer', 'license_print_license_html' );
	}

}
endif; // End commentpress_license_widget_compat

// Do this late, so license ought to be declared by then.
add_action( 'widgets_init', 'commentpress_license_widget_compat', 100 );



if ( ! function_exists( 'commentpress_wplicense_compat' ) ):
/**
 * Remove license from footer.
 *
 * This is because wp_footer() is not inside #footer.
 *
 * @since 3.3
 */
function commentpress_wplicense_compat() {

	// Let's not have the default footer.
	remove_action( 'wp_footer', 'cc_showLicenseHtml' );

}
endif; // End commentpress_wplicense_compat

// Do this late, so license ought to be declared by then.
add_action( 'init', 'commentpress_wplicense_compat', 100 );



if ( ! function_exists( 'commentpress_widget_title' ) ):
/**
 * Ensure that widget title is not empty.
 *
 * Empty widget titles break the layout of the theme at present, because the
 * enclosing markup for the sub-section is split between the 'after_title' and
 * 'after_widget' substitutions in the theme register_sidebar() declarations.
 *
 * Note: #footer widget titles are hidden via CSS. Override this in your child
 * theme to show them collectively or individually.
 *
 * @since 3.8.10
 *
 * @param str $title The possibly-empty widget title.
 * @param str $id_base The widget ID base.
 * @return str $title The non-empty title.
 */
function commentpress_widget_title( $title ) {

	// Set default title if none present
	if ( empty( $title ) ) {
		$title = __( 'Untitled Widget', 'commentpress-core' );
	}

	// --<
	return $title;

}
endif; // End commentpress_widget_title

// Make sure widget title is not empty.
add_filter( 'widget_title', 'commentpress_widget_title', 10, 1 );



if ( ! function_exists( 'commentpress_groupblog_classes' ) ):
/**
 * Add classes to #content in BuddyPress, so that we can distinguish different groupblog types.
 *
 * @since 3.3
 *
 * @return str $groupblog_class The class for the groupblog.
 */
function commentpress_groupblog_classes() {

	// Init empty.
	$groupblog_class = '';

	// Only add classes when bp-groupblog is active.
	if ( function_exists( 'get_groupblog_group_id' ) ) {

		// Init groupblogtype.
		$groupblogtype = 'groupblog';

		// Get group blogtype.
		$groupblog_type = groups_get_groupmeta( bp_get_current_group_id(), 'groupblogtype' );

		// Did we get one?
		if ( $groupblog_type ) {

			// Add to default.
			$groupblogtype .= ' ' . $groupblog_type;

		}

		// Complete.
		$groupblog_class = ' class="' . $groupblogtype . '"';

	}

	// --<
	return $groupblog_class;

}
endif; // End commentpress_groupblog_classes



if ( ! function_exists( 'commentpress_get_post_version_info' ) ):
/**
 * Get links to previous and next versions, should they exist.
 *
 * @since 3.3
 *
 * @param WP_Post $post The WordPress post object.
 */
function commentpress_get_post_version_info( $post ) {

	// Check for newer version.
	$newer_link = '';

	// Assume no newer version.
	$newer_id = '';

	// Set key.
	$key = '_cp_newer_version';

	// If the custom field already has a value.
	if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

		// Get it.
		$newer_id = get_post_meta( $post->ID, $key, true );

	}

	// If we've got one.
	if ( $newer_id !== '' ) {

		// Get post.
		$newer_post = get_post( $newer_id );

		// Is it published?
		if ( $newer_post->post_status == 'publish' ) {

			// Get link.
			$link = get_permalink( $newer_post->ID );

			// Define title.
			$title = __( 'Newer version', 'commentpress-core' );

			// Construct anchor.
			$newer_link = '<a href="' . $link . '" title="' . $title . '">' . $title . ' &rarr;</a>';

		}

	}

	// Check for older version.
	$older_link = '';

	// Get post with this post's ID as their _cp_newer_version meta value.
	$args = [
		'numberposts' => 1,
		'meta_key' => '_cp_newer_version',
		'meta_value' => $post->ID,
	];

	// Get the array
	$previous_posts = get_posts( $args );

	// Did we get one?
	if ( is_array( $previous_posts ) AND count( $previous_posts ) == 1 ) {

		// Get it.
		$older_post = $previous_posts[0];

		// Is it published?
		if ( $older_post->post_status == 'publish' ) {

			// Get link.
			$link = get_permalink( $older_post->ID );

			// Define title.
			$title = __( 'Older version', 'commentpress-core' );

			// Construct anchor.
			$older_link = '<a href="' . $link . '" title="' . $title . '">&larr; ' . $title . '</a>';

		}

	}

	// Did we get either?
	if ( $newer_link != '' OR $older_link != '' ) {

		?>
		<div class="version_info">
			<ul>
				<?php if ( $newer_link != '' ) echo '<li class="newer_version">' . $newer_link . '</li>'; ?>
				<?php if ( $older_link != '' ) echo '<li class="older_version">' . $older_link . '</li>'; ?>
			</ul>
		</div>
		<?php

	}

}
endif; // End commentpress_get_post_version_info



if ( ! function_exists( 'commentpress_get_post_css_override' ) ):
/**
 * Overrride post type by adding a CSS class.
 *
 * @since 3.3
 *
 * @param int $post_id The numeric ID of the post.
 * @return str $type_overridden The CSS class.
 */
function commentpress_get_post_css_override( $post_id ) {

	// Add a class for overridden page types.
	$type_overridden = '';

	// Declare access to globals.
	global $commentpress_core;

	// If we have the plugin enabled.
	if ( is_object( $commentpress_core ) ) {

		// Default to current blog type.
		$type = $commentpress_core->db->option_get( 'cp_blog_type' );

		// Set post meta key.
		$key = '_cp_post_type_override';

		// But, if the custom field has a value.
		if ( get_post_meta( $post_id, $key, true ) !== '' ) {

			// Get it.
			$overridden_type = get_post_meta( $post_id, $key, true );

			// Is it different to the current blog type?
			if ( $overridden_type != $type ) {
				$type_overridden = ' overridden_type-' . $overridden_type;
			}

		}

	}

	// --<
	return $type_overridden;

}
endif; // End commentpress_get_post_css_override



if ( ! function_exists( 'commentpress_get_post_title_visibility' ) ):
/**
 * Do we want to show page/post title?
 *
 * @since 3.3
 *
 * @param int $post_id The numeric ID of the post.
 * @return bool $hide True if title is shown, false if hidden.
 */
function commentpress_get_post_title_visibility( $post_id ) {

	// Init hide (show by default).
	$hide = 'show';

	// Declare access to globals.
	global $commentpress_core;

	// Get global hide if we have the plugin enabled.
	if ( is_object( $commentpress_core ) ) {
		$hide = $commentpress_core->db->option_get( 'cp_title_visibility' );;
	}

	// Set key.
	$key = '_cp_title_visibility';

	// Get value if the custom field already has one.
	if ( get_post_meta( $post_id, $key, true ) != '' ) {
		$hide = get_post_meta( $post_id, $key, true );
	}

	// --<
	return ( $hide == 'show' ) ? true : false;

}
endif; // End commentpress_get_post_title_visibility



if ( ! function_exists( 'commentpress_get_post_meta_visibility' ) ):
/**
 * Do we want to show page/post meta?
 *
 * @since 3.3
 *
 * @param int $post_id The numeric ID of the post.
 * @return bool $hide_meta True if meta is shown, false if hidden.
 */
function commentpress_get_post_meta_visibility( $post_id ) {

	// Init hide (hide by default).
	$hide_meta = 'hide';

	// Declare access to globals.
	global $commentpress_core;

	// If we have the plugin enabled.
	if ( is_object( $commentpress_core ) ) {

		// Get global hide_meta.
		$hide_meta = $commentpress_core->db->option_get( 'cp_page_meta_visibility' );;

		// Set key.
		$key = '_cp_page_meta_visibility';

		// Override with local value if the custom field already has one.
		if ( get_post_meta( $post_id, $key, true ) != '' ) {
			$hide_meta = get_post_meta( $post_id, $key, true );
		}

	}

	// --<
	return ( $hide_meta == 'show' ) ? true : false;

}
endif; // End commentpress_get_post_meta_visibility



if ( ! function_exists( 'commentpress_sidebars_widgets' ) ):
/**
 * Temporary fix for PHP notice in WP 3.9.
 *
 * @since 3.6
 *
 * @return array $array Existing widgets array.
 * @return array $array Modified widgets array.
 */
function commentpress_sidebars_widgets( $array ) {

	// Prevent errors in Theme Customizer.
	if ( ! is_array( $array ) ) {

		// This array is based on the array in wp_install_defaults().
		$array = [
			'wp_inactive_widgets' => [],
			'sidebar-1' => [],
			'sidebar-2' => [],
			'sidebar-3' => [],
			'array_version' => 3,
		];

	}

	// --<
	return $array;

}
endif; // End commentpress_sidebars_widgets

// Add filter for the above.
add_filter( 'sidebars_widgets', 'commentpress_sidebars_widgets', 1000 );



if ( ! function_exists( 'commentpress_add_selection_classes' ) ):
/**
 * Filter the comment class to add selection data.
 *
 * @since 3.8
 *
 * @param array $classes An array of comment classes.
 * @param string $class A comma-separated list of additional classes added to the list.
 * @param int $comment_id The comment id.
 * @param object $comment The comment.
 * @param int|WP_Post $post_id The post ID or WP_Post object.
 */
function commentpress_add_selection_classes( $classes, $class, $comment_id, $comment, $post_id = 0 ) {

	// Define key.
	$key = '_cp_comment_selection';

	// Get current.
	$data = get_comment_meta( $comment_id, $key, true );

	// If the comment meta already has a value.
	if ( ! empty( $data ) ) {

		// Make into an array.
		$selection = explode( ',', $data );

		// Add to classes.
		$classes[] = 'selection-exists';
		$classes[] = 'sel_start-' . $selection[0];
		$classes[] = 'sel_end-' . $selection[1];

	}

	// --<
	return $classes;

}
endif; // End commentpress_add_selection_classes

// Add filter for the above.
add_filter( 'comment_class', 'commentpress_add_selection_classes', 100, 4 );



if ( ! function_exists( 'commentpress_bp_activity_css_class' ) ):
/**
 * Update BuddyPress activity CSS class with groupblog type.
 *
 * @since 3.3
 *
 * @param str $existing_class The existing class.
 * @param str $existing_class The overridden class.
 */
function commentpress_bp_activity_css_class( $existing_class ) {

	// Init group blog type.
	$groupblog_type = '';

	// Get current item.
	global $activities_template;
	$current_activity = $activities_template->activity;

	// For group activity.
	if ( $current_activity->component == 'groups' ) {

		// Get group blogtype.
		$groupblog_type = groups_get_groupmeta( $current_activity->item_id, 'groupblogtype' );

		// Add space before if we have it.
		if ( $groupblog_type ) { $groupblog_type = ' ' . $groupblog_type; }

	}

	// --<
	return $existing_class . $groupblog_type;

}
endif; // End commentpress_bp_activity_css_class



if ( ! function_exists( 'commentpress_bp_blog_css_class' ) ):
/**
 * Update BuddyPress Sites Directory blog item CSS class with groupblog type.
 *
 * @since 3.3
 *
 * @param array $classes The existing classes.
 * @param array $classes The overridden classes.
 */
function commentpress_bp_blog_css_class( $classes ) {

	// Is this a groupblog?
	if ( function_exists( 'get_groupblog_group_id' ) ) {

		// Access BuddyPress object.
		global $blogs_template;

		// Get group ID.
		$group_id = get_groupblog_group_id( $blogs_template->blog->blog_id );
		if ( isset( $group_id ) AND is_numeric( $group_id ) AND $group_id > 0 ) {

			// Get group blogtype.
			$groupblog_type = groups_get_groupmeta( $group_id, 'groupblogtype' );

			// Did we get one?
			if ( $groupblog_type ) {

				// Add classes.
				$classes[] = 'bp-groupblog-blog';
				$classes[] = $groupblog_type;

			}

		}

	}

	// --<
	return $classes;

}
endif; // End commentpress_bp_blog_css_class



if ( ! function_exists( 'commentpress_bp_group_css_class' ) ):
/**
 * Update BuddyPress Groups Directory group item CSS class with groupblog type.
 *
 * @since 3.3
 *
 * @param array $classes The existing classes.
 * @param array $classes The overridden classes.
 */
function commentpress_bp_group_css_class( $classes ) {

	// Only add classes when bp-groupblog is active.
	if ( function_exists( 'get_groupblog_group_id' ) ) {

		// Get group blogtype.
		$groupblog_type = groups_get_groupmeta( bp_get_group_id(), 'groupblogtype' );

		// Did we get one?
		if ( $groupblog_type ) {

			// Add class.
			$classes[] = $groupblog_type;

		}

	}

	// --<
	return $classes;

}
endif; // End commentpress_bp_group_css_class



if ( ! function_exists( 'commentpress_prefix_bp_templates' ) ):
/**
 * Prefixes BuddyPress pages with the div wrappers that CommentPress Core needs.
 *
 * @since 3.3
 */
function commentpress_prefix_bp_templates() {

	// Prefixed wrappers.
	echo '<div id="wrapper">
		  <div id="main_wrapper" class="clearfix">
		  <div id="page_wrapper">';

}
endif; // End commentpress_prefix_bp_templates

// Add action for the above.
add_action( 'bp_before_directory_groupsites_page', 'commentpress_prefix_bp_templates' );



if ( ! function_exists( 'commentpress_suffix_bp_templates' ) ):
/**
 * Suffixes BuddyPress pages with the div wrappers that CommentPress Core needs.
 *
 * @since 3.3
 */
function commentpress_suffix_bp_templates() {

	// Prefixed wrappers.
	echo '</div><!-- /page_wrapper -->
		  </div><!-- /main_wrapper -->
		  </div><!-- /wrapper -->';

}
endif; // End commentpress_suffix_bp_templates

// Add action for the above.
add_action( 'bp_after_directory_groupsites_page', 'commentpress_suffix_bp_templates' );



if ( ! function_exists( 'commentpress_prefix_signup_template' ) ):
/**
 * Prefixes WordPress Signup Page with the div wrappers that CommentPress Core needs.
 *
 * @since 3.8.5
 */
function commentpress_prefix_signup_template() {

	// Prefixed wrappers.
	echo '<div id="wrapper">
		  <div id="main_wrapper" class="clearfix">
		  <div id="page_wrapper">
		  <div id="content">';

}
endif; // End commentpress_prefix_signup_template

// Add action for the above.
add_action( 'before_signup_form', 'commentpress_prefix_signup_template' );



if ( ! function_exists( 'commentpress_suffix_signup_template' ) ):
/**
 * Suffixes WordPress Signup Page with the div wrappers that CommentPress Core needs.
 *
 * @since 3.8.5
 */
function commentpress_suffix_signup_template() {

	// Prefixed wrappers.
	echo '</div><!-- /content -->
		  </div><!-- /page_wrapper -->
		  </div><!-- /main_wrapper -->
		  </div><!-- /wrapper -->';

}
endif; // End commentpress_suffix_signup_template

// Add action for the above.
add_action( 'after_signup_form', 'commentpress_suffix_signup_template' );



if ( ! function_exists( 'commentpress_unwrap_buddypress_button' ) ):
/**
 * Removes the default wrapping of buttons in the directory nav.
 *
 * The BP_Button class was refactored in BuddyPress 2.7 and all buttons are
 * wrapped in <div class="generic-button"></div> by default. This causes the
 * link to pick up the style of a button, which breaks the nav menu layout.
 * This filter unwraps the link so it appears the same as in BuddyPress 2.6.n.
 *
 * @since 3.9.1
 *
 * @param array $button_args The existing params used to define the button.
 * @return array $button_args The modified params used to define the button.
 */
function commentpress_unwrap_buddypress_button( $button_args ) {

	// Bail if not BP 2.7.x.
	if ( ! function_exists( 'bp_core_filter_wp_query' ) ) return $button_args;

	// Remove parent element.
	$button_args['parent_element'] = '';

	// --<
	return $button_args;

}
endif; // End commentpress_unwrap_buddypress_button

// Add filters for the above.
add_filter( 'bp_get_group_create_button', 'commentpress_unwrap_buddypress_button' );
add_filter( 'bp_get_blog_create_button', 'commentpress_unwrap_buddypress_button' );



if ( ! function_exists( 'commentpress_geomashup_map_get' ) ):
/**
 * Show the map for a post.
 *
 * Does not work in non-global loops, such as those made via WP_Query.
 *
 * @since 3.9.9
 */
function commentpress_geomashup_map_get() {

	// Bail if Geo Mashup not active.
	if ( ! class_exists( 'GeoMashup' ) ) return;

	// Bail if post has no location.
	$location = GeoMashup::current_location( null, 'post' );
	if ( empty( $location ) ) return;

	// Show map.
	echo '<div class="geomap">' . GeoMashup::map() . '</div>';

}
endif;



/**
 * Theme Tabs Class.
 *
 * A class that encapsulates functionality of theme-specific Workflow tabs.
 * Does not work in non-global loops, such as those made via WP_Query.
 *
 * @since 3.9.9
 */
class CommentPress_Theme_Tabs {

	/**
	 * Tabs Class.
	 *
	 * @since 3.9.9
	 * @access public
	 * @var str $tabs_class The Tabs Class.
	 */
	public $tabs_class = '';

	/**
	 * Tabs Classes.
	 *
	 * @since 3.9.9
	 * @access public
	 * @var str $tabs_classes The Tabs Classes.
	 */
	public $tabs_classes = '';

	/**
	 * Original Content.
	 *
	 * @since 3.9.9
	 * @access public
	 * @var str $original The Original Content.
	 */
	public $original = '';

	/**
	 * Literal Content.
	 *
	 * @since 3.9.9
	 * @access public
	 * @var str $literal The Literal Content.
	 */
	public $literal = '';



	/**
	 * Constructor.
	 *
	 * @since 3.9.9
	 */
	public function __construct() {

		// Nothing.

	}



	/**
	 * Returns a single instance of this object when called.
	 *
	 * @since 3.9.9
	 *
	 * @return object $instance Comment_Tagger instance.
	 */
	public static function instance() {

		// Store the instance locally to avoid private static replication.
		static $instance = null;

		// Instantiate if need be.
		if ( null === $instance ) {
			$instance = new CommentPress_Theme_Tabs();
		}

		// Always return instance.
		return $instance;

	}



	/**
	 * Initialise required data.
	 *
	 * @since 3.9.9
	 */
	public function initialise() {

		// Bail if already initialised.
		static $initialised = false;
		if ( $initialised ) return;

		// Bail if plugin not present.
		global $commentpress_core;
		if ( ! is_object( $commentpress_core ) ) return;

		// Bail if workflow not enabled.
		if ( '1' != $commentpress_core->db->option_get( 'cp_blog_workflow' ) ) return;

		// Okay, let's get our data.

		// Access post.
		global $post;

		// Set key.
		$key = '_cp_original_text';

		// If the custom field already has a value, get it.
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {
			$this->original = get_post_meta( $post->ID, $key, true );
		}

		// Set key.
		$key = '_cp_literal_translation';

		// If the custom field already has a value, get it.
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {
			$this->literal = get_post_meta( $post->ID, $key, true );
		}

		// Did we get either type of workflow content?
		if ( $this->literal != '' OR $this->original != '' ) {

			// Override tabs class.
			$this->tabs_class = 'with-content-tabs';

			// Override tabs classes.
			$this->tabs_classes = ' class="' . $this->tabs_class . '"';

			// Prefix with space.
			$this->tabs_class = ' ' . $this->tabs_class;

		}

		// Flag as initialised.
		$initialised = true;

	}



	/**
	 * Echo Tabs.
	 *
	 * @since 3.9.9
	 */
	public function tabs() {

		// Bail if we have no tabs.
		if ( empty( $this->tabs_class ) ) return;

		// Bail if we get neither type of workflow content.
		if ( empty( $this->literal ) AND empty( $this->original ) ) return;

		?>
		<ul id="content-tabs">
			<li id="content_header" class="default-content-tab"><h2><a href="#content"><?php
				echo apply_filters(
					'commentpress_content_tab_content',
					__( 'Content', 'commentpress-core' )
				);
			?></a></h2></li>
			<?php if ( $this->literal != '' ) { ?>
			<li id="literal_header"><h2><a href="#literal"><?php
				echo apply_filters(
					'commentpress_content_tab_literal',
					__( 'Literal', 'commentpress-core' )
				);
			?></a></h2></li>
			<?php } ?>
			<?php if ( $this->original != '' ) { ?>
			<li id="original_header"><h2><a href="#original"><?php
				echo apply_filters(
					'commentpress_content_tab_original',
					__( 'Original', 'commentpress-core' )
				);
			?></a></h2></li>
			<?php } ?>
		</ul>
		<?php

	}



	/**
	 * Echo Tabs Content.
	 *
	 * @since 3.9.9
	 */
	public function tabs_content() {

		// Bail if we have no tabs.
		if ( empty( $this->tabs_class ) ) return;

		// Bail if we get neither type of workflow content.
		if ( empty( $this->literal ) AND empty( $this->original ) ) return;

		// Did we get literal?
		if ( $this->literal != '' ) {

			?>
			<div id="literal" class="workflow-wrapper">
				<div class="post">
					<h2 class="post_title"><?php
						echo apply_filters(
							'commentpress_literal_title',
							__( 'Literal Translation', 'commentpress-core' )
						);
					?></h2>
					<?php echo apply_filters( 'cp_workflow_richtext_content', $this->literal ); ?>
				</div><!-- /post -->
			</div><!-- /literal -->
			<?php

		}

		// Did we get original?
		if ( $this->original != '' ) {

			?>
			<div id="original" class="workflow-wrapper">
				<div class="post">
					<h2 class="post_title"><?php
						echo apply_filters(
							'commentpress_original_title',
							__( 'Original Text', 'commentpress-core' )
						);
					?></h2>
					<?php echo apply_filters( 'cp_workflow_richtext_content', $this->original ); ?>
				</div><!-- /post -->
			</div><!-- /original -->
			<?php

		}

	}



} // Class ends.



/**
 * Init Theme Tabs.
 *
 * @since 3.9.9
 *
 * @return object CommentPress_Theme_Tabs The Theme Tabs instance.
 */
function commentpress_theme_tabs() {
	return CommentPress_Theme_Tabs::instance();
}

// Init the above.
commentpress_theme_tabs();



/**
 * Render Theme Tabs.
 *
 * @since 3.9.9
 */
function commentpress_theme_tabs_render() {

	// Get object and maybe init.
	$tabs = commentpress_theme_tabs();
	$tabs->initialise();

	// Print to screen.
	$tabs->tabs();

}



/**
 * Render Theme Tabs Content.
 *
 * @since 3.9.9
 */
function commentpress_theme_tabs_content_render() {

	// Get object and maybe init.
	$tabs = commentpress_theme_tabs();
	$tabs->initialise();

	// Print to screen.
	$tabs->tabs_content();

}



/**
 * Get Theme Tabs Class.
 *
 * @since 3.9.9
 *
 * @return str $tabs_class The tabs class.
 */
function commentpress_theme_tabs_class_get() {

	// Get object and maybe init.
	$tabs = commentpress_theme_tabs();
	$tabs->initialise();

	// --<
	return $tabs->tabs_class;

}



/**
 * Get Theme Tabs Classes.
 *
 * @since 3.9.9
 *
 * @return str $tabs_classes The tabs classes.
 */
function commentpress_theme_tabs_classes_get() {

	// Get object and maybe init.
	$tabs = commentpress_theme_tabs();
	$tabs->initialise();

	// --<
	return $tabs->tabs_classes;

}


