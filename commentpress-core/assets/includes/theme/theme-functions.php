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
 *
 * @return void
 */
function commentpress_admin_header() {

	// access plugin
	global $commentpress_core;

	// init with same colour as theme stylesheets and default in class_commentpress_db.php
	$colour = '2c2622';

	// override if we have the plugin enabled
	if ( is_object( $commentpress_core ) ) {
		$colour = $commentpress_core->db->option_get_header_bg();
	}

	// try and recreate the look of the theme header
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
endif; // commentpress_admin_header



if ( ! function_exists( 'commentpress_customize_register' ) ) :
/**
 * Implements CommentPress Core Theme options in the Theme Customizer.
 *
 * @since 3.0
 *
 * @param $wp_customize Theme Customizer object
 * @return void
 */
function commentpress_customize_register( $wp_customize ) {

	// kick out if plugin not active
	global $commentpress_core;
	if ( ! is_object( $commentpress_core ) ) return;

	// add "Site Image"
	commentpress_customize_site_image( $wp_customize );

	// add "Site Logo"
	commentpress_customize_site_logo( $wp_customize );

	// add "Header Background Colour"
	commentpress_customize_header_bg_color( $wp_customize );

}
endif; // commentpress_customize_register
add_action( 'customize_register', 'commentpress_customize_register' );



if ( ! function_exists( 'commentpress_customize_site_image' ) ) :
/**
 * Implements CommentPress Core "Site Image" in the Theme Customizer.
 *
 * @since 3.8.5
 *
 * @param $wp_customize Theme Customizer object
 * @return void
 */
function commentpress_customize_site_image( $wp_customize ) {

	// kick out if BuddyPress Groupblog
	global $commentpress_core;
	if ( $commentpress_core->is_groupblog() ) return;

	// kick out if we do not have the WP 4.2 WP_Customize_Media_Control class
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

		// properties
		public $type = 'media';
		public $mime_type = 'image';
		public $button_labels = array();

		/**
		 * Constructor.
		 *
		 * @param WP_Customize_Manager $manager
		 * @param string $id
		 * @param array  $args
		 */
		public function __construct( $manager, $id, $args = array() ) {

			// call parent constructor
			parent::__construct( $manager, $id, $args );

			// allow label to be filtered
			$site_image = apply_filters( 'commentpress_customizer_site_image_title', __( 'Site Image', 'commentpress-core' ) );

			// set labels
			$this->button_labels = array(
				'select'       => sprintf( __( 'Select %s', 'commentpress-core' ), $site_image ),
				'change'       => sprintf( __( 'Change %s', 'commentpress-core' ), $site_image ),
				'remove'       => sprintf( __( 'Remove %s', 'commentpress-core' ), $site_image ),
				'default'      => sprintf( __( 'Default %s', 'commentpress-core' ), $site_image ),
				'placeholder'  => sprintf( __( 'No %s selected', 'commentpress-core' ), $site_image ),
				'frame_title'  => sprintf( __( 'Select %s', 'commentpress-core' ), $site_image ),
				'frame_button' => sprintf( __( 'Choose %s', 'commentpress-core' ), $site_image ),
			);

		}

	}

	// register control (not needed as yet, but is if we want to fully extend)
	//$wp_customize->register_control_type( 'WP_Customize_Site_Image_Control' );

	// add customizer section title
	$wp_customize->add_section( 'cp_site_image', array(
		'title' => apply_filters( 'commentpress_customizer_site_image_title', __( 'Site Image', 'commentpress-core' ) ),
		'priority' => 25,
	) );

	// add image setting
	$wp_customize->add_setting( 'commentpress_theme_settings[cp_site_image]', array(
		 'default' => '',
		 'capability' => 'edit_theme_options',
		 'type' => 'option'
	));

	// add image control
	$wp_customize->add_control( new WP_Customize_Site_Image_Control(
		$wp_customize, 'cp_site_image', array(
		'label' => apply_filters( 'commentpress_customizer_site_image_title', __( 'Site Image', 'commentpress-core' ) ),
		'description' => apply_filters( 'commentpress_customizer_site_image_description', __( 'Choose an image to represent this site. Other plugins may use this image to illustrate this site - in multisite directory listings, for example.', 'commentpress-core' ) ),
		'section' => 'cp_site_image',
		'settings' => 'commentpress_theme_settings[cp_site_image]',
		'priority'	=>	1
	)));

}
endif; // commentpress_customize_site_image



if ( ! function_exists( 'commentpress_customize_site_logo' ) ) :
/**
 * Implements CommentPress Core "Site Logo" in the Theme Customizer.
 *
 * @since 3.8.5
 *
 * @param $wp_customize Theme Customizer object
 * @return void
 */
function commentpress_customize_site_logo( $wp_customize ) {

	// kick out if BuddyPress Groupblog
	global $commentpress_core;
	if ( $commentpress_core->is_groupblog() ) return;

	// add customizer section title
	$wp_customize->add_section( 'cp_inline_header_image', array(
		'title' => __( 'Site Logo', 'commentpress-core' ),
		'priority' => 35,
	) );

	// add image setting
	$wp_customize->add_setting( 'commentpress_theme_settings[cp_inline_header_image]', array(
		 'default' => '',
		 'capability' => 'edit_theme_options',
		 'type' => 'option'
	));

	// add image control
	$wp_customize->add_control( new WP_Customize_Image_Control(
		$wp_customize, 'cp_inline_header_image', array(
		'label' => __( 'Logo Image', 'commentpress-core' ),
	    'description' => apply_filters( 'commentpress_customizer_site_logo_description', __( 'You may prefer to display an image instead of text in the header of your site. The image must be a maximum of 70px tall. If it is less tall, then you can adjust the vertical alignment using the "Top padding in px" setting below.', 'commentpress-core' ) ),
		'section' => 'cp_inline_header_image',
		'settings' => 'commentpress_theme_settings[cp_inline_header_image]',
		'priority'	=>	1
	)));

	// add padding setting
	$wp_customize->add_setting( 'commentpress_theme_settings[cp_inline_header_padding]', array(
		 'default' => '',
		 'capability' => 'edit_theme_options',
		 'type' => 'option'
	));

	// add text control
	$wp_customize->add_control( 'commentpress_theme_settings[cp_inline_header_padding]', array(
		'label' => __( 'Top padding in px', 'commentpress-core' ),
		'section' => 'cp_inline_header_image',
		'type' => 'text'
	) );

}
endif; // commentpress_customize_site_logo



if ( ! function_exists( 'commentpress_customize_header_bg_color' ) ) :
/**
 * Implements CommentPress Core "Header Background Colour" in the Theme Customizer.
 *
 * @since 3.8.5
 *
 * @param $wp_customize Theme Customizer object
 * @return void
 */
function commentpress_customize_header_bg_color( $wp_customize ) {

	global $commentpress_core;

	// add color picker setting
	$wp_customize->add_setting( 'commentpress_header_bg_color', array(
		'default' => '#' . $commentpress_core->db->header_bg_colour,
		 //'capability' => 'edit_theme_options',
		 //'type' => 'option',
	) );

	// add color picker control
	$wp_customize->add_control( new WP_Customize_Color_Control(
		$wp_customize, 'commentpress_header_bg_color', array(
		'label' => __( 'Header Background Colour', 'commentpress-core' ),
		'section' => 'colors',
		'settings' => 'commentpress_header_bg_color',
	) ) );

}
endif; // commentpress_customize_header_bg_color



if ( ! function_exists( 'commentpress_admin_menu' ) ) :
/**
 * Adds more prominent menu item.
 *
 * @since 3.0
 *
 * @return void
 */
function commentpress_admin_menu() {

	// Only add for WP3.4+
	global $wp_version;
	if ( version_compare( $wp_version, '3.4', '>=' ) ) {

		// add the Customize link to the admin menu
		add_theme_page( __( 'Customize', 'commentpress-core' ), __( 'Customize', 'commentpress-core' ), 'edit_theme_options', 'customize.php' );

	}

}
endif; // commentpress_admin_menu
add_action( 'admin_menu', 'commentpress_admin_menu' );



if ( ! function_exists( 'commentpress_fix_bp_core_avatar_url' ) ):
/**
 * Filter to fix broken group avatar images in BuddyPress 1.7.
 *
 * @since 3.3
 *
 * @param string $url The existing URL of the avatar
 * @return string $url The modified URL of the avatar
 */
function commentpress_fix_bp_core_avatar_url( $url ) {

	// if in multisite and on non-root site
	if ( is_multisite() && ! bp_is_root_blog() ) {

		// switch to root site
		switch_to_blog( bp_get_root_blog_id() );

		// get upload dir data
		$upload_dir = wp_upload_dir();

		// get storage location of avatars
		$url = $upload_dir['baseurl'];

		// switch back
		restore_current_blog();

	}

	// --<
	return $url;

}
endif; // commentpress_fix_bp_core_avatar_url



if ( ! function_exists( 'commentpress_get_header_image' ) ):
/**
 * Function that sets a header foreground image. (a logo, for example)
 *
 * @since 3.0
 *
 * @todo Inform users that header images are using a different method
 *
 * @return void
 */
function commentpress_get_header_image() {

	// access plugin
	global $commentpress_core;

	// -------------------------------------------------------------------------
	// if this is a groupblog, always show group avatar
	// -------------------------------------------------------------------------

	// test for groupblog
	if ( is_object( $commentpress_core ) AND $commentpress_core->is_groupblog() ) {

		// get group ID
		$group_id = get_groupblog_group_id( get_current_blog_id() );

		// get group avatar
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

		// add filter for the function above
		add_filter( 'bp_core_avatar_url', 'commentpress_fix_bp_core_avatar_url', 10, 1 );

        // show group avatar
        echo bp_core_fetch_avatar( $avatar_options );

        // remove filter
        remove_filter( 'bp_core_avatar_url', 'commentpress_fix_bp_core_avatar_url' );

		// --<
		return;

	}

	// -------------------------------------------------------------------------
	// allow plugins to hook in before Theme Customizer
	// -------------------------------------------------------------------------

	// allow plugins to hook in
	$custom_avatar_pre = apply_filters( 'commentpress_header_image_pre_customizer', false );

	// did we get one?
	if ( $custom_avatar_pre !== false ) {

		// show it
		echo $custom_avatar_pre;

		// bail before fallback
		return;

	}

	// -------------------------------------------------------------------------
	// implement compatibility with WordPress Theme Customizer
	// -------------------------------------------------------------------------

	// get the new options
	$options = get_option( 'commentpress_theme_settings' );

	// test for our new theme customizer option
	if ( isset( $options['cp_inline_header_image'] ) AND ! empty( $options['cp_inline_header_image'] ) ) {

		// init top padding
		$style = '';

		// override if there is top padding
		if ( isset( $options['cp_inline_header_padding'] ) AND ! empty( $options['cp_inline_header_padding'] ) ) {
			$style = ' style="padding-top: ' . $options['cp_inline_header_padding'] . 'px"';
		}

		// show the uploaded image
		echo apply_filters(
			'commentpress_header_image',
			'<img src="' . $options['cp_inline_header_image'] . '" class="cp_logo_image"' . $style . ' alt="' . __( 'Logo', 'commentpress-core' ) . '" />'
		);

		// --<
		return;

	}

	// -------------------------------------------------------------------------
	// allow plugins to hook in after Theme Customizer
	// -------------------------------------------------------------------------

	// allow plugins to hook in
	$custom_avatar_post = apply_filters( 'commentpress_header_image_post_customizer', false );

	// did we get one?
	if ( $custom_avatar_post !== false ) {

		// show it
		echo $custom_avatar_post;

		// bail before fallback
		return;

	}

	// -------------------------------------------------------------------------
	// our fallback is to go with the legacy method that some people might still be using
	// -------------------------------------------------------------------------

	// if we have the plugin enabled
	if ( is_object( $commentpress_core ) AND $commentpress_core->db->option_get( 'cp_toc_page' ) ) {

		// set defaults
		$args = array(
			'post_type' => 'attachment',
			'numberposts' => 1,
			'post_status' => null,
			'post_parent' => $commentpress_core->db->option_get( 'cp_toc_page' )
		);

		// get them
		$attachments = get_posts( $args );

		// we only want the first
		if ( $attachments ) {
			$attachment = $attachments[0];
		}

		// show it if we have an image
		if ( isset( $attachment ) ) {
			echo wp_get_attachment_image( $attachment->ID, 'full' );
		}

	}

}
endif; // commentpress_get_header_image



if ( ! function_exists( 'commentpress_get_body_id' ) ):
/**
 * Get an ID for the body tag.
 *
 * @since 3.0
 *
 * @return string $body_id The ID attribute for the body tag
 */
function commentpress_get_body_id() {

	// init
	$body_id = '';

	// is this multisite?
	if ( is_multisite() ) {

		// is this the main blog?
		if ( is_main_site() ) {

			// set main blog id
			$body_id = ' id="main_blog"';

		}

	}

	// --<
	return $body_id;

}
endif; // commentpress_get_body_id



if ( ! function_exists( 'commentpress_get_body_classes' ) ):
/**
 * Get classes for the body tag.
 *
 * @since 3.3
 *
 * @param boolean $raw If true, returns the class names, if false, returns the attribute
 * @return string $body_classes The class attribute for the body tag
 */
function commentpress_get_body_classes( $raw = false ) {

	// init the array that holds our custom classes
	$classes = array();

	// access post and plugin
	global $post, $commentpress_core;

	// -------------------- default sidebar --------------------

	// set default sidebar but override if we have the plugin enabled
	$sidebar_flag = 'toc';
	if ( is_object( $commentpress_core ) ) {
		$sidebar_flag = $commentpress_core->get_default_sidebar();
	}

	// set class per sidebar
	$sidebar_class = 'cp_sidebar_' . $sidebar_flag;

	// add to array
	$classes[] = $sidebar_class;

	// -------------------- commentable --------------------

	// init commentable class but override if we have the plugin enabled
	$commentable = '';
	if ( is_object( $commentpress_core ) ) {
		$commentable = ( $commentpress_core->is_commentable() ) ? 'commentable' : 'not_commentable';
	}

	// add to array
	if ( ! empty( $commentable ) ) $classes[] = $commentable;

	// -------------------- layout --------------------

	// init layout class but if we have the plugin enabled
	$layout_class = '';
	if ( is_object( $commentpress_core ) ) {

		// is this the title page?
		if (
			is_object( $post ) AND
			isset( $post->ID ) AND
			$post->ID == $commentpress_core->db->option_get( 'cp_welcome_page' )
		) {

			// init layout
			$layout = '';

			// set key
			$key = '_cp_page_layout';

			// if the custom field already has a value
			if ( get_post_meta( $post->ID, $key, true ) != '' ) {

				// get it
				$layout = get_post_meta( $post->ID, $key, true );

			}

			// if wide layout
			if ( $layout == 'wide' ) {

				// set layout class
				$layout_class = 'full_width';

			}

		}

	}

	// add to array
	if ( ! empty( $layout_class ) ) $classes[] = $layout_class;

	// -------------------- page type --------------------

	// set default page type
	$page_type = '';

	// if blog post
	if ( is_single() ) {

		// add blog post class
		$page_type = 'blog_post';

	}

	// if we have the plugin enabled
	if ( is_object( $commentpress_core ) ) {

		// is it a BuddyPress special page?
		if ( $commentpress_core->is_buddypress_special_page() ) {

			// add BuddyPress page class
			$page_type = 'buddypress_page';

		}

		// is it a CommentPress Core special page?
		if ( $commentpress_core->db->is_special_page() ) {

			// add BuddyPress page class
			$page_type = 'commentpress_page';

		}

	}

	// add to array
	if ( ! empty( $page_type ) ) $classes[] = $page_type;

	// -------------------- is groupblog --------------------

	// set default type
	$is_groupblog = 'not-groupblog';

	// if we have the plugin enabled
	if ( is_object( $commentpress_core ) ) {

		// if it's a groupblog
		if ( $commentpress_core->is_groupblog() ) {
			$is_groupblog = 'is-groupblog';
		}

	}

	// add to array
	if ( ! empty( $is_groupblog ) ) $classes[] = $is_groupblog;

	// -------------------- blog type --------------------

	// set default type
	$blog_type = '';

	// if we have the plugin enabled
	if ( is_object( $commentpress_core ) ) {

		// get type
		$type = $commentpress_core->db->option_get( 'cp_blog_type' );

		// get workflow
		$workflow = $commentpress_core->db->option_get( 'cp_blog_workflow' );

		/**
		 * Allow plugins to override the blog type - for example if workflow
		 * is enabled, it might become a new blog type as far as BuddyPress
		 * is concerned.
		 *
		 * @param int $type The numeric blog type
		 * @param bool $workflow True if workflow enabled, false otherwise
		 */
		$current_blog_type = apply_filters( 'cp_get_group_meta_for_blog_type', $type, $workflow );

		// if it's not the main site, add class
		if ( is_multisite() AND ! is_main_site() ) {
			$blog_type = 'blogtype-' . intval( $current_blog_type );
		}

	}

	// add to array
	if ( ! empty( $blog_type ) ) $classes[] = $blog_type;

	// -------------------- group groupblog type --------------------

	// when viewing a group, set default groupblog type
	$group_groupblog_type = '';

	// if we have the plugin enabled
	if ( is_object( $commentpress_core ) ) {

		// is it a BuddyPress group page?
		if ( function_exists( 'bp_is_groups_component' ) AND bp_is_groups_component() ) {

			// get current group
			$current_group = groups_get_current_group();

			// sanity check
			if ( $current_group instanceof BP_Groups_Group ) {

				// get groupblog type
				$groupblogtype = groups_get_groupmeta( $current_group->id, 'groupblogtype' );

				// set groupblog type if present
				if ( ! empty( $groupblogtype ) ) {
					$group_groupblog_type = $groupblogtype;
				}

			}

		}

	}

	// add to array
	if ( ! empty( $group_groupblog_type ) ) $classes[] = $group_groupblog_type;

	// -------------------- TinyMCE version --------------------

	// init TinyMCE class
	$tinymce_version = 'tinymce-3';

	// access WP version
	global $wp_version;

	// if greater than 3.8
	if ( version_compare( $wp_version, '3.8.9999', '>' ) ) {

		// override TinyMCE class
		$tinymce_version = 'tinymce-4';

	}

	// add to array
	if ( ! empty( $tinymce_version ) ) $classes[] = $tinymce_version;

	// -------------------- process --------------------

	// construct attribute but allow filtering
	$body_classes = apply_filters( 'commentpress_body_classes', implode( ' ', $classes ) );

	// if we want them wrapped, do so
	if ( ! $raw ) {

		// preserve backwards compat for older child themes
		$body_classes = ' class="' . $body_classes . '"';

	}

	// --<
	return $body_classes;

}
endif; // commentpress_get_body_classes



if ( ! function_exists( 'commentpress_document_title_parts' ) ):
/**
 * Add the root network name when the sub-blog is a group blog.
 *
 * @since 3.8
 *
 * @param array $parts The existing title parts
 * @return array $parts The modified title parts
 */
function commentpress_document_title_parts( $parts ) {

	global $commentpress_core;

	// if we have the plugin enabled
	if ( is_object( $commentpress_core ) ) {

		// if it's a groupblog
		if ( $commentpress_core->is_groupblog() ) {
			if ( ! isset( $parts['site'] ) ) {
				$parts['title'] .= commentpress_site_title( '|', false );
				unset( $parts['tagline'] );
			} else {
				$parts['site'] .= commentpress_site_title( '|', false );
			}
		}

	}

	// return filtered array
	return apply_filters( 'commentpress_document_title_parts', $parts );

}
endif; // commentpress_document_title_parts

// add a filter for the above
add_filter( 'document_title_parts', 'commentpress_document_title_parts' );



if ( ! function_exists( 'commentpress_document_title_separator' ) ):
/**
 * Use the separator that CommentPress Core has always used.
 *
 * @since 3.8
 *
 * @param string $sep The existing separator
 * @return string $sep The modified separator
 */
function commentpress_document_title_separator( $sep ) {

	// --<
	return '|';

}
endif; // commentpress_document_title_separator

// add a filter for the above
add_filter( 'document_title_separator', 'commentpress_document_title_separator' );



if ( ! function_exists( 'commentpress_site_title' ) ):
/**
 * Amend the site title depending on context of blog.
 *
 * @since 3.8
 *
 * @param string $sep The title separator
 * @param boolean $echo Echo the result or not
 * @return string $site_name The title of the site
 */
function commentpress_site_title( $sep = '', $echo = true ) {

	// is this multisite?
	if ( is_multisite() ) {

		// if we're on a sub-blog
		if ( ! is_main_site() ) {

			$current_site = get_current_site();

			// print?
			if( $echo ) {

				// add site name
				echo ' ' . trim( $sep ) . ' ' . $current_site->site_name;

			} else {

				// add site name
				return ' ' . trim( $sep ) . ' ' . $current_site->site_name;

			}

		}

	}

}
endif; // commentpress_site_title



if ( ! function_exists( 'commentpress_remove_more_jump_link' ) ):
/**
 * Disable more link jump.
 *
 * @see http://codex.wordpress.org/Customizing_the_Read_More
 *
 * @since 3.0
 *
 * @param string $link The existing more link
 * @return string $link The modified more link
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
endif; // commentpress_remove_more_jump_link

// add a filter for the above
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
 * @return string $title The page title
 */
function commentpress_page_title() {

	// declare access to globals
	global $commentpress_core, $post;

	// init
	$title = '';
	$sep = ' &#8594; ';

	//$title .= get_bloginfo( 'name' );

	if ( is_page() OR is_single() OR is_category() ) {

		if ( is_page() ) {

			$ancestors = get_post_ancestors( $post );

			if ( $ancestors ) {
				$ancestors = array_reverse( $ancestors );

				$crumbs = array();

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

		// Current page
		if ( is_page() OR is_single() ) {
			$title .= get_the_title();
		}

	}

	// --<
	return $title;

}
endif; // commentpress_page_title



if ( ! function_exists( 'commentpress_has_page_children' ) ):
/**
 * Query whether a given page has children.
 *
 * @since 3.3
 *
 * @param object $page_obj The WordPress page object to query
 * @return boolean True if page has children, false otherwise
 */
function commentpress_has_page_children( $page_obj ) {

	// init to look for published pages
	$defaults = array(
		'post_parent' => $page_obj->ID,
		'post_type' => 'page',
		'numberposts' => -1,
		'post_status' => 'publish'
	);

	// get page children
	$kids =& get_children( $defaults );

	// do we have any?
	return ( empty( $kids ) ) ? false : true;

}
endif; // commentpress_has_page_children



if ( ! function_exists( 'commentpress_get_children' ) ):
/**
 * Retrieve comment children.
 *
 * @since 3.3
 *
 * @param object $comment The WordPress comment object
 * @param string $page_or_post The WordPress post type to query
 * @return array $result The array of child comments
 */
function commentpress_get_children( $comment, $page_or_post ) {

	// declare access to globals
	global $wpdb;

	// construct query for comment children
	$query = "
	SELECT $wpdb->comments.*, $wpdb->posts.post_title, $wpdb->posts.post_name
	FROM $wpdb->comments, $wpdb->posts
	WHERE $wpdb->comments.comment_post_ID = $wpdb->posts.ID
	AND $wpdb->posts.post_type = '$page_or_post'
	AND $wpdb->comments.comment_approved = '1'
	AND $wpdb->comments.comment_parent = '$comment->comment_ID'
	ORDER BY $wpdb->comments.comment_date ASC
	";

	// does it have children?
	return $wpdb->get_results( $query );

}
endif; // commentpress_get_children



if ( ! function_exists( 'commentpress_get_comments' ) ):
/**
 * Generate comments recursively.
 *
 * This function builds the list into a global called $cp_comment_output for
 * retrieval elsewhere.
 *
 * @since 3.0
 *
 * @param array $comments An array of WordPress comment objects
 * @param string $page_or_post The WordPress post type to query
 * @return void
 */
function commentpress_get_comments( $comments, $page_or_post ) {

	// declare access to globals
	global $cp_comment_output;

	// do we have any comments?
	if( count( $comments ) > 0 ) {

		// open ul
		$cp_comment_output .= '<ul class="item_ul">' . "\n\n";

		// produce a checkbox for each
		foreach( $comments as $comment ) {

			// open li
			$cp_comment_output .= '<li class="item_li">' . "\n\n";

			// format this comment
			$cp_comment_output .= commentpress_format_comment( $comment );

			// get comment children
			$children = commentpress_get_children( $comment, $page_or_post );

			// do we have any?
			if( count( $children ) > 0 ) {

				// recurse
				commentpress_get_comments( $children, $page_or_post );

			}

			// close li
			$cp_comment_output .= '</li>' . "\n\n";

		}

		// close ul
		$cp_comment_output .= '</ul>' . "\n\n";

	}

}
endif; // commentpress_get_comments



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
 * @param object $user The WordPress user object
 * @param object $comment The WordPress comment object
 * @return string $url The URL for the user
 */
function commentpress_get_user_link( $user, $comment = null ) {

	// kick out if not a user
	if ( ! is_object( $user ) ) return false;

	// we're through: the user is on the system
	global $commentpress_core;

	// if BuddyPress
	if ( is_object( $commentpress_core ) AND $commentpress_core->is_buddypress() ) {

		// BuddyPress link ($no_anchor = null, $just_link = true)
		$url = bp_core_get_userlink( $user->ID, null, true );

	} else {

		// get standard WP author url

		// get author url
		$url = get_author_posts_url( $user->ID );

		// WP sometimes leaves 'http://' or 'https://' in the field
		if (  $url == 'http://'  OR $url == 'https://' ) {

			// clear
			$url = '';

		}

	}

	// --<
	return apply_filters( 'commentpress_get_user_link', $url, $user, $comment );

}
endif; // commentpress_get_user_link



if ( ! function_exists( 'commentpress_echo_post_meta' ) ):
/**
 * Show user(s) in the loop.
 *
 * @since 3.0
 *
 * @return void
 */
function commentpress_echo_post_meta() {

	// bail if this is a BuddyPress page
	if ( function_exists( 'is_buddypress' ) AND is_buddypress() ) return;

	// compat with Co-Authors Plus
	if ( function_exists( 'get_coauthors' ) ) {

		// get multiple authors
		$authors = get_coauthors();

		// if we get some
		if ( ! empty( $authors ) ) {

			// use the Co-Authors format of "name, name, name & name"
			$author_html = '';

			// init counter
			$n = 1;

			// find out how many author we have
			$author_count = count( $authors );

			// loop
			foreach( $authors AS $author ) {

				// default to comma
				$sep = ', ';

				// if we're on the penultimate
				if ( $n == ( $author_count - 1 ) ) {

					// use ampersand
					$sep = __( ' &amp; ', 'commentpress-core' );

				}

				// if we're on the last, don't add
				if ( $n == $author_count ) { $sep = ''; }

				// get name
				$author_html .= commentpress_echo_post_author( $author->ID, false );

				// and separator
				$author_html .= $sep;

				// increment
				$n++;

				// yes - are we showing avatars?
				if ( get_option( 'show_avatars' ) ) {

					// get avatar
					echo get_avatar( $author->ID, $size='32' );

				}

			}

			?><cite class="fn"><?php echo $author_html; ?></cite>

			<p><a href="<?php the_permalink() ?>"><?php echo esc_html( get_the_date( __( 'l, F jS, Y', 'commentpress-core' ) ) ); ?></a></p>

			<?php

		}

	} else {

		// get avatar
		$author_id = get_the_author_meta( 'ID' );
		echo get_avatar( $author_id, $size='32' );

		?>

		<cite class="fn"><?php commentpress_echo_post_author( $author_id ) ?></cite>

		<p><a href="<?php the_permalink() ?>"><?php echo esc_html( get_the_date( __( 'l, F jS, Y', 'commentpress-core' ) ) ); ?></a></p>

		<?php

	}

}
endif; // commentpress_echo_post_meta



if ( ! function_exists( 'commentpress_show_source_url' ) ):
/**
 * Show source URL for print.
 *
 * @since 3.5
 *
 * @return void
 */
function commentpress_show_source_url() {

	// add the URL - hidden, but revealed by print stylesheet
	?><p class="hidden_page_url"><?php

		// label
		echo __( 'Source: ', 'commentpress-core' );

		// path from server array, if set
		$path = ( isset( $_SERVER['REQUEST_URI'] ) ) ? $_SERVER['REQUEST_URI'] : '';

		// get server, if set
		$server = ( isset( $_SERVER['SERVER_NAME'] ) ) ? $_SERVER['SERVER_NAME'] : '';

		// get protocol, if set
		$protocol = ( ! empty( $_SERVER['HTTPS'] ) ) ? 'https' : 'http';

		// construct URL
		$url = $protocol . '://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

		// echo
		echo $url;

	?></p><?php

}
endif; // commentpress_show_source_url

// add after theme setup hook
add_action( 'wp_footer', 'commentpress_show_source_url' );



if ( ! function_exists( 'commentpress_echo_post_author' ) ):
/**
 * Show username (with link) in the loop.
 *
 * @since 3.0
 *
 * @param int $author_id The numeric ID of the author
 * @param bool $echo Print or return the linked username
 * @return str $author The linked username
 */
function commentpress_echo_post_author( $author_id, $echo = true ) {

	// get author details
	$user = get_userdata( $author_id );

	// kick out if we don't have a user with that ID
	if ( ! is_object( $user ) ) return;

	// access plugin
	global $commentpress_core, $post;

	// if we have the plugin enabled and it's BuddyPress
	if ( is_object( $post ) AND is_object( $commentpress_core ) AND $commentpress_core->is_buddypress() ) {

		// construct user link
		$author = bp_core_get_userlink( $user->ID );

	} else {

		// link to theme's author page
		$link = sprintf(
			'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
			get_author_posts_url( $user->ID, $user->user_nicename ),
			esc_attr( sprintf( __( 'Posts by %s' ), $user->display_name ) ),
			esc_html( $user->display_name )
		);
		$author = apply_filters( 'the_author_posts_link', $link );

	}

	// if we're echoing
	if ( $echo ) {
		echo $author;
	} else {
		return $author;
	}

}
endif; // commentpress_echo_post_author



if ( ! function_exists( 'commentpress_format_comment' ) ):
/**
 * Format comment on custom CommentPress Core comments pages.
 *
 * @since 3.0
 *
 * @param object $comment The comment object
 * @param str $context Either "all" for all-comments or "by" for comments-by-commenter
 * @return str The formatted comment HTML
 */
function commentpress_format_comment( $comment, $context = 'all' ) {

	// declare access to globals
	global $commentpress_core, $cp_comment_output;

	// TODO enable WordPress API on comment?
	//$GLOBALS['comment'] = $comment;

	// construct link
	$comment_link = get_comment_link( $comment->comment_ID );

	// construct anchor
	$comment_anchor = '<a href="' . $comment_link . '" title="' . esc_attr( __( 'See comment in context', 'commentpress-core' ) ) . '">' . __( 'Comment', 'commentpress-core' ) . '</a>';

	// construct date
	$comment_date = date( __( 'F jS, Y', 'commentpress-core' ), strtotime( $comment->comment_date ) );

	// if context is 'all comments'
	if ( $context == 'all' ) {

		// get author
		if ( $comment->comment_author != '' ) {

			// was it a registered user?
			if ( $comment->user_id != '0' ) {

				// get user details
				$user = get_userdata( $comment->user_id );

				// get user link
				$user_link = commentpress_get_user_link( $user, $comment );

				// did we get one?
				if ( $user_link != '' AND $user_link != 'http://' ) {

					// construct link to user url
					$comment_author = '<a href="' . $user_link . '">' . $comment->comment_author . '</a>';

				} else {

					// just show author name
					$comment_author = $comment->comment_author;

				}

			} else {

				// do we have an author URL?
				if ( $comment->comment_author_url != '' AND $comment->comment_author_url != 'http://' ) {

					// construct link to user url
					$comment_author = '<a href="' . $comment->comment_author_url . '">' . $comment->comment_author . '</a>';

				} else {

					// define context
					$comment_author = $comment->comment_author;

				}

			}


		} else {

			// we don't have a name
			$comment_author = __( 'Anonymous', 'commentpress-core' );

		}

		// construct comment header content
		$comment_meta_content = sprintf(
			__( '%1$s by %2$s on %3$s', 'commentpress-core' ),
			$comment_anchor,
			$comment_author,
			$comment_date
		);

		// wrap comment meta in a div
		$comment_meta = '<div class="comment_meta">' . $comment_meta_content . '</div>' . "\n";

		// allow filtering by plugins
		$comment_meta = apply_filters(
			'commentpress_format_comment_all_meta', // filter name
			$comment_meta, // built meta
			$comment,
			$comment_anchor,
			$comment_author,
			$comment_date
		);

	// if context is 'by commenter'
	} elseif ( $context == 'by' ) {

		// construct link
		$page_link = trailingslashit( get_permalink( $comment->comment_post_ID ) );

		// construct page anchor
		$page_anchor = '<a href="' . $page_link . '">' . get_the_title( $comment->comment_post_ID ) . '</a>';

		// construct comment header content
		$comment_meta_content = sprintf(
			__( '%1$s on %2$s on %3$s', 'commentpress-core' ),
			$comment_anchor,
			$page_anchor,
			$comment_date
		);

		// wrap comment meta in a div
		$comment_meta = '<div class="comment_meta">' . $comment_meta_content . '</div>' . "\n";

		// allow filtering by plugins
		$comment_meta = apply_filters(
			'commentpress_format_comment_by_meta', // filter name
			$comment_meta, // built meta
			$comment,
			$comment_anchor,
			$page_anchor,
			$comment_date
		);

	}

	// comment content
	$comment_body = '<div class="comment-content">' . apply_filters( 'comment_text', $comment->comment_content ) . '</div>' . "\n";

	// construct comment
	return '<div class="comment_wrapper">' . "\n" . $comment_meta . $comment_body . '</div>' . "\n\n";

}
endif; // commentpress_format_comment



if ( ! function_exists( 'commentpress_get_comments_by_content' ) ):
/**
 * Comments-by page display function.
 *
 * @todo Do we want trackbacks?
 *
 * @since 3.0
 *
 * @return str $html The HTML for the page
 */
function commentpress_get_comments_by_content() {

	// init return
	$html = '';

	// get all approved comments
	$all_comments = get_comments( array(
		'status' => 'approve',
		'orderby' => 'comment_author, comment_post_ID, comment_date',
		'order' => 'ASC',
	) );

	// kick out if none
	if ( count( $all_comments ) == 0 ) return $html;

	// build list of authors
	$authors_with = array();
	$author_names = array();
	//$post_comment_counts = array();

	foreach( $all_comments AS $comment ) {

		// add to authors with comments array
		if ( ! in_array( $comment->comment_author_email, $authors_with ) ) {
			$authors_with[] = $comment->comment_author_email;
			$name = $comment->comment_author != '' ? $comment->comment_author : __( 'Anonymous', 'commentpress-core' );
			$author_names[$comment->comment_author_email] = $name;
		}

		/*
		// increment counter
		if ( ! isset( $post_comment_counts[$comment->comment_author_email] ) ) {
			$post_comment_counts[$comment->comment_author_email] = 1;
		} else {
			$post_comment_counts[$comment->comment_author_email]++;
		}
		*/

	}

	// kick out if none
	if ( count( $authors_with ) == 0 ) return $html;

	// open ul
	$html .= '<ul class="all_comments_listing">' . "\n\n";

	// loop through authors
	foreach( $authors_with AS $author ) {

		// open li
		$html .= '<li class="author_li"><!-- author li -->' . "\n\n";

		// add gravatar
		$html .= '<h3>' . get_avatar( $author, $size='24' ) . esc_html( $author_names[$author] ) . '</h3>' . "\n\n";

		// open comments div
		$html .= '<div class="item_body">' . "\n\n";

		// open ul
		$html .= '<ul class="item_ul">' . "\n\n";

		// loop through comments
		foreach( $all_comments AS $comment ) {

			// does it belong to this author?
			if ( $author == $comment->comment_author_email ) {

				// open li
				$html .= '<li class="item_li"><!-- item li -->' . "\n\n";

				// show the comment
				$html .= commentpress_format_comment( $comment, 'by' );

				// close li
				$html .= '</li><!-- /item li -->' . "\n\n";

			}

		}

		// close ul
		$html .= '</ul>' . "\n\n";

		// close item div
		$html .= '</div><!-- /item_body -->' . "\n\n";

		// close li
		$html .= '</li><!-- /.author_li -->' . "\n\n\n\n";

	}

	// close ul
	$html .= '</ul><!-- /.all_comments_listing -->' . "\n\n";

	// --<
	return $html;

}
endif; // commentpress_get_comments_by_content



if ( ! function_exists( 'commentpress_get_comments_by_page_content' ) ):
/**
 * Comments-by page display wrapper function.
 *
 * @since 3.0
 *
 * @return str $page_content The page content
 */
function commentpress_get_comments_by_page_content() {

	// allow oEmbed in comments
	global $wp_embed;
	if ( $wp_embed instanceof WP_Embed ) {
		add_filter( 'comment_text', array( $wp_embed, 'autoembed' ), 1 );
	}

	// declare access to globals
	global $commentpress_core;

	// set title
	$page_content = '<h2 class="post_title">' . __( 'Comments by Commenter', 'commentpress-core' ) . '</h2>' . "\n\n";

	// get data
	$page_content .= commentpress_get_comments_by_content();

	// --<
	return $page_content;

}
endif; // commentpress_get_comments_by_page_content



if ( ! function_exists( 'commentpress_show_activity_tab' ) ):
/**
 * Decide whether or not to show the Activity Sidebar.
 *
 * @since 3.3
 *
 * @return bool True if we show the activity tab, false otherwise
 */
function commentpress_show_activity_tab() {

	// declare access to globals
	global $commentpress_core, $post;

	/*
	// if we have the plugin enabled
	if ( is_object( $commentpress_core ) ) {

		// is this multisite?
		if (
			( is_multisite() AND is_main_site() AND $commentpress_core->is_buddypress_special_page() ) OR
			! is_object( $post )
		) {

			// ignore activity
			return false;

		}

	}
	*/

	// --<
	return true;

}
endif; // commentpress_show_activity_tab



if ( ! function_exists( 'commentpress_is_commentable' ) ):
/**
 * Is a post/page commentable?
 *
 * @since 3.3
 *
 * @return bool $is_commentable True if page can have comments, false otherwise
 */
function commentpress_is_commentable() {

	// declare access to plugin
	global $commentpress_core;

	// if we have it
	if ( is_object( $commentpress_core ) ) {

		// return what it reports
		return $commentpress_core->is_commentable();

	}

	// --<
	return false;

}
endif; // commentpress_is_commentable



if ( ! function_exists( 'commentpress_get_comment_activity' ) ):
/**
 * Activity sidebar display function.
 *
 * @todo Do we want trackbacks?
 *
 * @since 3.3
 *
 * @param str $scope The scope of the activities
 * @return str $page_content The HTML output for activities
 */
function commentpress_get_comment_activity( $scope = 'all' ) {

	// allow oEmbed in comments
	global $wp_embed;
	if ( $wp_embed instanceof WP_Embed ) {
		add_filter( 'comment_text', array( $wp_embed, 'autoembed' ), 1 );
	}

	// declare access to globals
	global $commentpress_core, $post;

	// init page content
	$page_content = '';

	// define defaults
	$args = array(

		'number' => 10,
		'status' => 'approve',

		// exclude trackbacks and pingbacks until we decide what to do with them
		'type' => ''

	);

	// if we are on a 404, for example
	if ( $scope == 'post' AND is_object( $post ) ) {

		// get all comments
		$args['post_id'] = $post->ID;

	}

	// get 'em
	$data = get_comments( $args );

	// did we get any?
	if ( count( $data ) > 0 ) {

		// open ul
		$page_content .= '<ol class="comment_activity">' . "\n\n";

		// init title
		$title = '';

		// loop
		foreach( $data AS $comment ) {

			// exclude comments from password-protected posts
			if ( ! post_password_required( $comment->comment_post_ID ) ) {
				$page_content .= commentpress_get_comment_activity_item( $comment );
			}

		}

		// close ul
		$page_content .= '</ol><!-- /comment_activity -->' . "\n\n";

	}

	// --<
	return $page_content;

}
endif; // commentpress_get_comment_activity



if ( ! function_exists( 'commentpress_get_comment_activity_item' ) ):
/**
 * Get comment formatted for the activity sidebar.
 *
 * @since 3.3
 *
 * @param $comment The comment object
 * @return $item_html The modified comment HTML
 */
function commentpress_get_comment_activity_item( $comment ) {

	// enable WordPress API on comment
	$GLOBALS['comment'] = $comment;

	// declare access to globals
	global $commentpress_core, $post;

	// init markup
	$item_html = '';

	// only comments until we decide what to do with pingbacks and trackbacks
	if ( $comment->comment_type == 'pingback' ) return $item_html;
	if ( $comment->comment_type == 'trackback' ) return $item_html;

	// test for anonymous comment (usually generated by WP itself in multisite installs)
	if ( empty( $comment->comment_author ) ) {

		$comment->comment_author = __( 'Anonymous', 'commentpress-core' );

	}

	// was it a registered user?
	if ( $comment->user_id != '0' ) {

		// get user details
		$user = get_userdata( $comment->user_id );

		// get user link
		$user_link = commentpress_get_user_link( $user, $comment );

		// construct author citation
		$author = '<cite class="fn"><a href="' . $user_link . '">' . get_comment_author() . '</a></cite>';

		// construct link to user url
		$author = ( $user_link != '' AND $user_link != 'http://' ) ?
					'<cite class="fn"><a href="' . $user_link . '">' . get_comment_author() . '</a></cite>' :
					 '<cite class="fn">' . get_comment_author() . '</cite>';

	} else {

		// construct link to commenter url
		$author = ( $comment->comment_author_url != '' AND $comment->comment_author_url != 'http://' ) ?
					'<cite class="fn"><a href="' . $comment->comment_author_url . '">' . get_comment_author() . '</a></cite>' :
					 '<cite class="fn">' . get_comment_author() . '</cite>';

	}

	// approved comment?
	if ( $comment->comment_approved == '0' ) {
		$comment_text = '<p><em>' . __( 'Comment awaiting moderation', 'commentpress-core' ) . '</em></p>';
	} else {
		$comment_text = get_comment_text( $comment->comment_ID );
	}

	// default to not on post
	$is_on_current_post = '';

	// on current post?
	if ( is_singular() AND is_object( $post ) AND $comment->comment_post_ID == $post->ID ) {

		// access paging globals
		global $multipage, $page;

		// is it the same page, if paged?
		if ( $multipage ) {

			// if it has a text sig
			if (
				! is_null( $comment->comment_signature ) AND
				$comment->comment_signature != ''
			) {

				// set key
				$key = '_cp_comment_page';

				// if the custom field already has a value
				if ( get_comment_meta( $comment->comment_ID, $key, true ) != '' ) {

					// get comment's page from meta
					$page_num = get_comment_meta( $comment->comment_ID, $key, true );

					// is it this one?
					if ( $page_num == $page ) {

						// is the right page
						$is_on_current_post = ' comment_on_post';

					}

				}

			} else {

				// it's always the right page for page-level comments
				$is_on_current_post = ' comment_on_post';

			}

		} else {

			// must be the right page
			$is_on_current_post = ' comment_on_post';

		}

	}

	// open li
	$item_html .= '<li><!-- item li -->' . "\n\n";

	// show the comment
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

	// close li
	$item_html .= '</li><!-- /item li -->' . "\n\n";

	// --<
	return $item_html;

}
endif; // commentpress_get_comment_activity_item



if ( ! function_exists( 'commentpress_lexia_support_mime' ) ):
/**
 * The "media" post type needs more granular naming support.
 *
 * @since 3.9
 *
 * @param str $post_type_name The existing singular name of the post type
 * @param str $post_type The post type identifier
 * @return str $post_type_name The modified singular name of the post type
 */
function commentpress_lexia_support_mime( $post_type_name, $post_type ) {

	// only handle media
	if ( $post_type != 'attachment' ) return $post_type_name;

	// get mime type
	$mime_type = get_post_mime_type( get_the_ID() );

	// use different name for each
	switch ( $mime_type ) {

		// image
		case 'image/jpeg':
		case 'image/png':
		case 'image/gif':
			$mime_type_name = __( 'Image', 'commentpress-core' );
			break;

		// video
		case 'video/mpeg':
		case 'video/mp4':
		case 'video/quicktime':
			$mime_type_name = __( 'Video', 'commentpress-core' );
			break;

		// file
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
	 * @param str $mime_type_name The name for this mime type
	 * @param str $mime_type The mime type
	 * @return str $mime_type_name The modified name for this mime type
	 */
	$mime_type_name = apply_filters( 'commentpress_lexia_mime_type_name', $mime_type_name, $mime_type );

	// --<
	return $mime_type_name;

}
endif; // commentpress_lexia_support_mime

// add filter for the above
add_filter( 'commentpress_lexia_post_type_name', 'commentpress_lexia_support_mime', 10, 2 );



if ( ! function_exists( 'commentpress_lexia_modify_entity_text' ) ):
/**
 * The "media" post type needs more granular naming support.
 *
 * @since 3.9
 *
 * @param str $entity_text The current entity text
 * @param str $post_type_name The singular name of the post type
 * @param str $post_type The post type identifier
 * @return str $entity_text The modified entity text
 */
function commentpress_lexia_modify_entity_text( $entity_text, $post_type_name, $post_type ) {

	// only handle media
	if ( $post_type != 'attachment' ) return $entity_text;

	// override entity text
	$entity_text = sprintf(
		__( 'the %s', 'commentpress-core' ),
		$post_type_name
	);

	// --<
	return $entity_text;

}
endif; // commentpress_lexia_modify_entity_text

// add filter for the above
add_filter( 'commentpress_lexia_whole_entity_text', 'commentpress_lexia_modify_entity_text', 10, 3 );



if ( ! function_exists( 'commentpress_comments_by_para_format_whole' ) ):
/**
 * Format the markup for the "whole page" section of comments.
 *
 * @since 3.8.10
 *
 * @param str $post_type_name The singular name of the post type
 * @param str $post_type The post type identifier
 * @param int $comment_count The number of comments on the block
 * @return array $return Data array containing the translated strings
 */
function commentpress_comments_by_para_format_whole( $post_type_name, $post_type, $comment_count ) {

	// init return
	$return = array();

	// construct entity text
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
	 * @param str $entity_text The current entity text
	 * @param str $post_type_name The singular name of the post type
	 * @return str $entity_text The modified entity text
	 */
	$return['entity_text'] = apply_filters(
		'commentpress_lexia_whole_entity_text',
		$return['entity_text'],
		$post_type_name,
		$post_type
	);

	// construct permalink text
	$return['permalink_text'] = sprintf(
		__( 'Permalink for comments on %s', 'commentpress-core' ),
		$return['entity_text']
	);

	// construct comment count
	$return['comment_text'] = sprintf(
		_n(
			'<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comment</span>', // singular
			'<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comments</span>', // plural
			$comment_count, // number
			'commentpress-core' // domain
		),
		$comment_count // substitution
	);

	// construct heading text
	$return['heading_text'] = sprintf(
		__( '%1$s on <span class="source_block">%2$s</span>', 'commentpress-core' ),
		$return['comment_text'],
		$return['entity_text']
	);

	// --<
	return $return;

}
endif; // commentpress_comments_by_para_format_whole



if ( ! function_exists( 'commentpress_comments_by_para_format_pings' ) ):
/**
 * Format the markup for the "pingbacks and trackbacks" section of comments.
 *
 * @since 3.8.10
 *
 * @param int $comment_count The number of comments on the block
 * @return array $return Data array containing the translated strings
 */
function commentpress_comments_by_para_format_pings( $comment_count ) {

	// init return
	$return = array();

	// construct entity text
	$return['entity_text'] = __( 'pingback or trackback', 'commentpress-core' );

	// construct permalink text
	$return['permalink_text'] = __( 'Permalink for pingbacks and trackbacks', 'commentpress-core' );

	// construct comment count
	$return['comment_text'] = sprintf(
		_n(
			'<span>%d</span> Pingback or trackback', // singular
			'<span>%d</span> Pingbacks and trackbacks', // plural
			$comment_count, // number
			'commentpress-core' // domain
		),
		$comment_count // substitution
	);

	// construct heading text
	$return['heading_text'] = sprintf( '<span>%s</span>', $return['comment_text'] );

	// --<
	return $return;

}
endif; // commentpress_comments_by_para_format_pings



if ( ! function_exists( 'commentpress_comments_by_para_format_block' ) ):
/**
 * Format the markup for "comments by block" section of comments.
 *
 * @since 3.8.10
 *
 * @param int $comment_count The number of comments on the block
 * @param int $para_num The sequential number of the block
 * @return array $return Data array containing the translated strings
 */
function commentpress_comments_by_para_format_block( $comment_count, $para_num ) {

	// init return
	$return = array();

	// access plugin
	global $commentpress_core;

	// get block name
	$block_name = __( 'paragraph', 'commentpress-core' );
	if ( is_object( $commentpress_core ) ) {
		$block_name = $commentpress_core->parser->lexia_get();
	}

	// construct entity text
	$return['entity_text'] = sprintf(
		__( '%1$s %2$s', 'commentpress-core' ),
		$block_name,
		$para_num
	);

	// construct permalink text
	$return['permalink_text'] = sprintf(
		__( 'Permalink for comments on %1$s %2$s', 'commentpress-core' ),
		$block_name,
		$para_num
	);

	// construct comment count
	$return['comment_text'] = sprintf(
		_n(
			'<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comment</span>', // singular
			'<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comments</span>', // plural
			$comment_count, // number
			'commentpress-core' // domain
		),
		$comment_count // substitution
	);

	// construct heading text
	$return['heading_text'] = sprintf(
		__( '%1$s on <span class="source_block">%2$s %3$s</span>', 'commentpress-core' ),
		$return['comment_text'],
		$block_name,
		$para_num
	);

	// --<
	return $return;

}
endif; // commentpress_comments_by_para_format_block



if ( ! function_exists( 'commentpress_get_comments_by_para' ) ):
/**
 * Get comments delimited by paragraph.
 *
 * @since 3.0
 *
 * @return void
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
	 * @param int $content_width An general content width for all themes
	 * @return int $content_width A specific content width for a specific theme
	 */
	global $content_width;
	$content_width = apply_filters( 'commentpress_comments_content_width', 380 );

	// allow oEmbed in comments
	global $wp_embed;
	if ( $wp_embed instanceof WP_Embed ) {
		add_filter( 'comment_text', array( $wp_embed, 'autoembed' ), 1 );
	}

	// allow plugins to precede comments
	do_action( 'commentpress_before_scrollable_comments' );

	// declare access to globals
	global $post, $commentpress_core;

	// get approved comments for this post, sorted comments by text signature
	$comments_sorted = $commentpress_core->get_sorted_comments( $post->ID );

	// key for starting paragraph number
	$key = '_cp_starting_para_number';

	// default starting paragraph number
	$start_num = 1;

	// override if the custom field already has a value
	if ( get_post_meta( $post->ID, $key, true ) != '' ) {
		$start_num = absint( get_post_meta( $post->ID, $key, true ) );
	}

	// if we have any
	if ( count( $comments_sorted ) > 0 ) {

		// allow for BuddyPress registration
		$registration = false;
		if ( function_exists( 'bp_get_signup_allowed' ) AND bp_get_signup_allowed() ) {
			$registration = true;
		}

		// maybe redirect to BuddyPress sign-up
		if ( $registration ) {
			$redirect = bp_get_signup_page();
		} else {
			$redirect = wp_login_url( get_permalink() );
		}

		// init allowed to comment
		$login_to_comment = false;

		// if we have to log in to comment
		if ( get_option( 'comment_registration' ) AND ! is_user_logged_in() ) {
			$login_to_comment = true;
		}

		// default comment type to get
		$comment_type = 'all';

		// check for a WP 3.8+ function
		if ( function_exists( 'wp_admin_bar_sidebar_toggle' ) ) {

			// Walker_Comment has changed to buffered output, so define args without
			// our custom walker. The built in walker works just fine now.
			$args = array(
				'style'=> 'ol',
				'type'=> $comment_type,
				'callback' => 'commentpress_comments'
			);

		} else {

			// init new walker, because the original class did not include the option
			// of using ordered lists <ol> instead of unordered ones <ul>
			// @see https://github.com/WordPress/WordPress/blob/5828310157f1805a5f0976d76692c7023e8a895d/wp-includes/comment-template.php#L880
			$walker = new Walker_Comment_Press;

			// define args
			$args = array(
				'walker' => $walker,
				'style'=> 'ol',
				'type'=> $comment_type,
				'callback' => 'commentpress_comments'
			);

		}

		// get singular post type label
		$current_type = get_post_type();
		$post_type = get_post_type_object( $current_type );

		/**
		 * Assign name of post type.
		 *
		 * @since 3.8.10
		 *
		 * @param str $singular_name The singular label for this post type
		 * @param str $current_type The post type identifier
		 * @return str $singular_name The modified label for this post type
		 */
		$post_type_name = apply_filters( 'commentpress_lexia_post_type_name', $post_type->labels->singular_name, $current_type );

		// init counter for text_signatures array
		$sig_counter = 0;

		// init array for tracking text sigs
		$used_text_sigs = array();

		// loop through each paragraph
		foreach( $comments_sorted AS $text_signature => $comments ) {

			// count comments
			$comment_count = count( $comments );

			// switch, depending on key
			switch( $text_signature ) {

				// whole page comments
				case 'WHOLE_PAGE_OR_POST_COMMENTS':

					// clear text signature
					$text_sig = '';

					// clear the paragraph number
					$para_num = '';

					// get the markup we need for this
					$markup = commentpress_comments_by_para_format_whole( $post_type_name, $current_type, $comment_count );

					break;

				// pingbacks and trackbacks
				case 'PINGS_AND_TRACKS':

					// set "unique-enough" text signature
					$text_sig = 'pingbacksandtrackbacks';

					// clear the paragraph number
					$para_num = '';

					// get the markup we need for this
					$markup = commentpress_comments_by_para_format_pings( $comment_count );

					break;

				// textblock comments
				default:

					// get text signature
					$text_sig = $text_signature;

					// paragraph number
					$para_num = $sig_counter + ( $start_num - 1 );

					// get the markup we need for this
					$markup = commentpress_comments_by_para_format_block( $comment_count, $para_num );

			} // end switch

			// init no comment class
			$no_comments_class = '';

			// override if there are no comments (for print stylesheet to hide them)
			if ( $comment_count == 0 ) { $no_comments_class = ' class="no_comments"'; }

			// exclude pings if there are none
			if ( $comment_count == 0 AND $text_signature == 'PINGS_AND_TRACKS' ) {

				// skip

			} else {

				// show heading
				echo '<h3 id="para_heading-' . $text_sig . '"' . $no_comments_class . '><a class="comment_block_permalink" title="' . $markup['permalink_text'] . '" href="#para_heading-' . $text_sig . '">' . $markup['heading_text'] . '</a></h3>' . "\n\n";

				// override if there are no comments (for print stylesheet to hide them)
				if ( $comment_count == 0 ) {
					$no_comments_class = ' no_comments';
				}

				// open paragraph wrapper
				echo '<div id="para_wrapper-' . $text_sig . '" class="paragraph_wrapper' . $no_comments_class . '">' . "\n\n";

				// have we already used this text signature?
				if( in_array( $text_sig, $used_text_sigs ) ) {

					// show some kind of message
					// should not be necessary now that we ensure unique text sigs
					echo '<div class="reply_to_para" id="reply_to_para-' . $para_num . '">' . "\n" .
							'<p>' .
								__( 'It appears that this paragraph is a duplicate of a previous one.', 'commentpress-core' ) .
							'</p>' . "\n" .
						 '</div>' . "\n\n";

				} else {

					// if we have comments
					if ( count( $comments ) > 0 ) {

						// open commentlist
						echo '<ol class="commentlist">' . "\n\n";

						// use WP 2.7+ functionality
						wp_list_comments( $args, $comments );

						// close commentlist
						echo '</ol>' . "\n\n";

					}

					/**
					 * Allow plugins to append to paragraph level comments.
					 *
					 * @param str $text_sig The text signature of the paragraph
					 */
					do_action( 'commentpress_after_paragraph_comments', $text_sig );

					// add to used array
					$used_text_sigs[] = $text_sig;

					// only add comment-on-para link if comments are open and it's not the pingback section
					if ( 'open' == $post->comment_status AND $text_signature != 'PINGS_AND_TRACKS' ) {

						// if we have to log in to comment
						if ( $login_to_comment ) {

							// the link text depending on whether we've got registration
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
							 * @param str $prompt The link text when login is required
							 * @param bool $registration True if registration is open, false otherwise
							 */
							$prompt = apply_filters( 'commentpress_reply_to_prompt_text', $prompt, $registration );

							// leave comment link
							echo '<div class="reply_to_para" id="reply_to_para-' . $para_num . '">' . "\n" .
									'<p><a class="reply_to_para" rel="nofollow" href="' . $redirect . '">' .
										$prompt .
									'</a></p>' . "\n" .
								 '</div>' . "\n\n";

						} else {

							// construct onclick content
							$onclick = "return addComment.moveFormToPara( '$para_num', '$text_sig', '$post->ID' )";

							// construct onclick attribute
							$onclick = apply_filters(
								'commentpress_reply_to_para_link_onclick',
								' onclick="' . $onclick . '"'
							);

							// just show replytopara
							$query = remove_query_arg( array( 'replytocom' ) );

							// add param to querystring
							$query = esc_url(
								add_query_arg(
									array( 'replytopara' => $para_num ),
									$query
								)
							);

							// construct href attribute
							$href = apply_filters(
								'commentpress_reply_to_para_link_href',
								$query . '#respond', // add respond ID
								$text_sig
							);

							// construct link content
							$link_content = sprintf(
								__( 'Leave a comment on %s', 'commentpress-core' ),
								$markup['entity_text']
							);

							// allow overrides
							$link_content = apply_filters(
								'commentpress_reply_to_para_link_text',
								$link_content,
								$markup['entity_text']
							);

							// leave comment link
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
				 * @param str $text_sig The text signature of the paragraph
				 */
				do_action( 'commentpress_after_paragraph_wrapper', $text_sig );

				// close paragraph wrapper
				echo '</div>' . "\n\n\n\n";

			}

			// increment signature array counter
			$sig_counter++;

		} // end comments-per-para loop

	}

	// allow plugins to follow comments
	do_action( 'commentpress_after_scrollable_comments' );

}
endif; // commentpress_get_comments_by_para



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
	function start_lvl( &$output, $depth = 0, $args = array() ) {

		// store depth
		$GLOBALS['comment_depth'] = $depth + 1;

		// open children if necessary
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
 * @return void
 */
function commentpress_comment_form_title(

	$no_reply_text = '',
	$reply_to_comment_text = '',
	$reply_to_para_text = '',
	$link_to_parent = TRUE

) {

	// sanity checks
	if ( $no_reply_text == '' ) { $no_reply_text = __( 'Leave a Reply', 'commentpress-core' ); }
	if ( $reply_to_comment_text == '' ) { $reply_to_comment_text = __( 'Leave a Reply to %s', 'commentpress-core' ); }
	if ( $reply_to_para_text == '' ) { $reply_to_para_text = __( 'Leave a Comment on %s', 'commentpress-core' ); }

	// declare access to globals
	global $comment, $commentpress_core;

	// get comment ID to reply to from URL query string
	$reply_to_comment_id = isset( $_GET['replytocom'] ) ? (int) $_GET['replytocom'] : 0;

	// get paragraph number to reply to from URL query string
	$reply_to_para_id = isset( $_GET['replytopara'] ) ? (int) $_GET['replytopara'] : 0;

	// if we have no comment ID AND no paragraph ID to reply to
	if ( $reply_to_comment_id == 0 AND $reply_to_para_id === 0 ) {

		// write default title to page
		echo $no_reply_text;

	} else {

		// if we have a comment ID AND NO paragraph ID to reply to
		if ( $reply_to_comment_id !== 0 AND $reply_to_para_id === 0 ) {

			// get comment
			$comment = get_comment( $reply_to_comment_id );

			// get link to comment
			$author = ( $link_to_parent ) ?
				'<a href="#comment-' . get_comment_ID() . '">' . get_comment_author() . '</a>' :
				get_comment_author();

			// write to page
			printf( $reply_to_comment_text, $author );

		} else {

			// get paragraph text signature
			$text_sig = $commentpress_core->get_text_signature( $reply_to_para_id );

			// get link to paragraph
			if ( $link_to_parent ) {

				// construct link text
				$para_text = sprintf(
					_x( '%s %s', 'The first substitution is the block name e.g. "paragraph". The second is the numeric comment count.', 'commentpress-core' ),
					ucfirst( $commentpress_core->parser->lexia_get() ),
					$reply_to_para_id
				);

				// construct paragraph
				$paragraph = '<a href="#para_heading-' . $text_sig . '">' . $para_text . '</a>';

			} else {

				// construct paragraph without link
				$paragraph = sprintf(
					_x( '%s %s', 'The first substitution is the block name e.g. "paragraph". The second is the numeric comment count.', 'commentpress-core' ),
					ucfirst( $commentpress_core->parser->lexia_get() ),
					$para_num
				);

			}

			// write to page
			printf( $reply_to_para_text, $paragraph );

		}

	}

}
endif; // commentpress_comment_form_title



if ( ! function_exists( 'commentpress_comment_reply_link' ) ):
/**
 * Alternative to the built-in WP function.
 *
 * @since 3.0
 *
 * @param array $args The reply links arguments
 * @param object $comment The comment
 * @param object $post The post
 */
function commentpress_comment_reply_link( $args = array(), $comment = null, $post = null ) {

	// set some defaults
	$defaults = array(
		'add_below' => 'comment',
		'respond_id' => 'respond',
		'reply_text' => __( 'Reply', 'commentpress-core' ),
		'login_text' => __( 'Log in to Reply', 'commentpress-core' ),
		'depth' => 0,
		'before' => '',
		'after' => ''
	);

	// parse them
	$args = wp_parse_args( $args, $defaults );

	if ( 0 == $args['depth'] || $args['max_depth'] <= $args['depth'] ) {
		return;
	}

	// convert to vars
	extract( $args, EXTR_SKIP );

	// get the obvious
	$comment = get_comment( $comment );
	$post = get_post( $post );

	// kick out if comments closed
	if ( 'open' != $post->comment_status ) return false;

	// init link
	$link = '';

	// if we have to log in to comment
	if ( get_option( 'comment_registration' ) AND ! is_user_logged_in() ) {

		// construct link
		$link = '<a rel="nofollow" href="' . site_url( 'wp-login.php?redirect_to=' . get_permalink() ) . '">' . $login_text . '</a>';

	} else {

		// just show replytocom
		$query = remove_query_arg( array( 'replytopara' ), get_permalink( $post->ID ) );

		// define query string
		$addquery = esc_url(
			add_query_arg(
				array( 'replytocom' => $comment->comment_ID ),
				$query
			)

		);

		// define link
		$link = "<a rel='nofollow' class='comment-reply-link' href='" . $addquery . "#" . $respond_id . "' onclick='return addComment.moveForm(\"$add_below-$comment->comment_ID\", \"$comment->comment_ID\", \"$respond_id\", \"$post->ID\", \"$comment->comment_signature\")'>$reply_text</a>";

	}

	// --<
	return apply_filters( 'comment_reply_link', $before . $link . $after, $args, $comment, $post );

}
endif; // commentpress_comment_reply_link



if ( ! function_exists( 'commentpress_comments' ) ):
/**
 * Custom comments display function.
 *
 * @since 3.0
 *
 * @param object $comment The comment object
 * @param array $args The comment arguments
 * @param int $depth The comment depth
 * @return void
 */
function commentpress_comments( $comment, $args, $depth ) {

	// build comment as html
	echo commentpress_get_comment_markup( $comment, $args, $depth );

}
endif; // commentpress_comments



if ( ! function_exists( 'commentpress_get_comment_markup' ) ):
/**
 * Wrap comment in its markup.
 *
 * @since 3.0
 *
 * @param object $comment The comment object
 * @param array $args The comment arguments
 * @param int $depth The comment depth
 * @return str $html The comment markup
 */
function commentpress_get_comment_markup( $comment, $args, $depth ) {

	// enable WordPress API on comment
	$GLOBALS['comment'] = $comment;

	// was it a registered user?
	if ( $comment->user_id != '0' ) {

		// get user details
		$user = get_userdata( $comment->user_id );

		// get user link
		$user_link = commentpress_get_user_link( $user, $comment );

		// construct author citation
		$author = ( $user_link != '' AND $user_link != 'http://' ) ?
					'<cite class="fn"><a href="' . $user_link . '">' . get_comment_author() . '</a></cite>' :
					 '<cite class="fn">' . get_comment_author() . '</cite>';

	} else {

		// construct link to commenter url for unregistered users
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

	// check moderation status
	if ( $comment->comment_approved == '0' ) {
		$comment_text = '<p><em>' . __( 'Comment awaiting moderation', 'commentpress-core' ) . '</em></p>';
	} else {
		$comment_text = get_comment_text();
	}

	// empty reply div by default
	$comment_reply = '';

	// enable access to post
	global $post;

	// can we reply?
	if (

		// not if comments are closed
		$post->comment_status == 'open' AND

		// we don't want reply to on pingbacks
		$comment->comment_type != 'pingback' AND

		// we don't want reply to on trackbacks
		$comment->comment_type != 'trackback' AND

		// nor on unapproved comments
		$comment->comment_approved == '1'

	) {

		// are we threading comments?
		if ( get_option( 'thread_comments', false ) ) {

			// custom comment_reply_link
			$comment_reply = commentpress_comment_reply_link( array_merge(
				$args,
				array(
					'reply_text' => sprintf( __( 'Reply to %s', 'commentpress-core' ), get_comment_author() ),
					'depth' => $depth,
					'max_depth' => $args['max_depth']
				)
			) );

			// wrap in div
			$comment_reply = '<div class="reply">' . $comment_reply . '</div><!-- /reply -->';

		}

	}

	// init edit link
	$editlink = '';

	// if logged in and has capability
	if (
		is_user_logged_in() AND
		current_user_can( 'edit_comment', $comment->comment_ID )
	) {

		// set default edit link title text
		$edit_title_text = apply_filters(
			'cp_comment_edit_link_title_text',
			__( 'Edit this comment', 'commentpress-core' )
		);

		// set default edit link text
		$edit_text = apply_filters(
			'cp_comment_edit_link_text',
			__( 'Edit', 'commentpress-core' )
		);

		// get edit comment link
		$editlink = '<span class="alignright comment-edit"><a class="comment-edit-link" href="' . get_edit_comment_link() . '" title="' . $edit_title_text . '">' . $edit_text . '</a></span>';

		// add a filter for plugins
		$editlink = apply_filters( 'cp_comment_edit_link', $editlink, $comment );

	}

	// add a nopriv filter for plugins
	$editlink = apply_filters( 'cp_comment_action_links', $editlink, $comment );

	// get comment class(es)
	$comment_class = comment_class( null, $comment->comment_ID, $post->ID, false );

	// if orphaned, add class to identify as such
	$comment_orphan = ( isset( $comment->orphan ) ) ? ' comment-orphan' : '';

	// construct permalink
	$comment_permalink = sprintf( __( '%1$s at %2$s', 'commentpress-core' ), get_comment_date(), get_comment_time() );

	// stripped source
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
endif; // commentpress_get_comment_markup



if ( ! function_exists( 'commentpress_get_full_name' ) ):
/**
 * Utility to concatenate names.
 *
 * @since 3.0
 *
 * @param str $forename The WordPress user's first name
 * @param str $surname The WordPress user's last name
 * @param str $fullname The WordPress user's full name
 */
function commentpress_get_full_name( $forename, $surname ) {

	// init return
	$fullname = '';

	// add forename
	if ( $forename != '' ) { $fullname .= $forename; }

	// add surname
	if ( $surname != '' ) { $fullname .= ' ' . $surname; }

	// strip any whitespace
	$fullname = trim( $fullname );

	// --<
	return $fullname;

}
endif; // commentpress_get_full_name



if ( ! function_exists( 'commentpress_excerpt_length' ) ):
/**
 * Utility to define length of excerpt.
 *
 * @since 3.0
 *
 * @return int $length The length of the excerpt
 */
function commentpress_excerpt_length() {

	// declare access to globals
	global $commentpress_core;

	// is the plugin active?
	if ( ! is_object( $commentpress_core ) ) {

		// --<
		return 55; // WordPress default

	}

	// get length of excerpt from option
	$length = $commentpress_core->db->option_get( 'cp_excerpt_length' );

	// --<
	return $length;

}
endif; // commentpress_excerpt_length

// add filter for excerpt length
add_filter( 'excerpt_length', 'commentpress_excerpt_length' );



if ( ! function_exists( 'commentpress_add_link_css' ) ):
/**
 * Utility to add button css class to blog nav links.
 *
 * @since 3.0
 *
 * @param str $link The existing link
 * @return str $link The link with the custom class attribute added
 */
function commentpress_add_link_css( $link ) {

	// add css
	$link = str_replace( '<a ', '<a class="css_btn" ', $link );

	// --<
	return $link;

}
endif; // commentpress_add_link_css

// add filter for next/previous links
add_filter( 'previous_post_link', 'commentpress_add_link_css' );
add_filter( 'next_post_link', 'commentpress_add_link_css' );



if ( ! function_exists( 'commentpress_get_link_css' ) ):
/**
 * Utility to add button css class to blog nav links.
 *
 * @since 3.0
 *
 * @return str $link The custom class attribute
 */
function commentpress_get_link_css() {

	// add css
	$link = 'class="css_btn"';

	// --<
	return $link;

}
endif; // commentpress_get_link_css

// add filter for next/previous posts links
add_filter( 'previous_posts_link_attributes', 'commentpress_get_link_css' );
add_filter( 'next_posts_link_attributes', 'commentpress_get_link_css' );



if ( ! function_exists( 'commentpress_multipage_comment_link' ) ):
/**
 * Filter comment permalinks for multipage posts.
 *
 * @since 3.5
 *
 * @param str $link The existing comment link
 * @param object $comment The comment object
 * @param array $args An array of extra arguments
 * @return str $link The modified comment link
 */
function commentpress_multipage_comment_link( $link, $comment, $args ) {

	// get multipage and post
	global $multipage, $post;

	// are there multiple (sub)pages?
	//if ( is_object( $post ) AND $multipage ) {

		// exclude page level comments
		if ( $comment->comment_signature != '' ) {

			// init page num
			$page_num = 1;

			// set key
			$key = '_cp_comment_page';

			// if the custom field already has a value
			if ( get_comment_meta( $comment->comment_ID, $key, true ) != '' ) {

				// get the page number
				$page_num = get_comment_meta( $comment->comment_ID, $key, true );

			}

			// get current comment info
			$comment_path_info = parse_url( $link );

			// set comment path
			return commentpress_get_post_multipage_url( $page_num, get_post( $comment->comment_post_ID ) ) . '#' . $comment_path_info['fragment'];

		}

	//}

	// --<
	return $link;

}
endif; // commentpress_multipage_comment_link

// add filter for the above
add_filter( 'get_comment_link', 'commentpress_multipage_comment_link', 10, 3 );



if ( ! function_exists( 'commentpress_get_post_multipage_url' ) ):
/**
 * Get the URL fo a page in a multipage context.
 *
 * Copied from wp-includes/post-template.php _wp_link_page()
 *
 * @since 3.5
 *
 * @param int $i The page number
 * @return str $url The URL to the subpage
 */
function commentpress_get_post_multipage_url( $i, $post = '' ) {

	// if we have no passed value
	if ( $post == '' ) {

		// we assume we're in the loop
		global $post, $wp_rewrite;

		if ( 1 == $i ) {
			$url = get_permalink();
		} else {
			if ( '' == get_option( 'permalink_structure' ) || in_array( $post->post_status, array( 'draft', 'pending' ) ) )
				$url = add_query_arg( 'page', $i, get_permalink() );
			elseif ( 'page' == get_option( 'show_on_front' ) AND get_option( 'page_on_front' ) == $post->ID )
				$url = trailingslashit( get_permalink() ) . user_trailingslashit( "$wp_rewrite->pagination_base/" . $i, 'single_paged' );
			else
				$url = trailingslashit( get_permalink() ) . user_trailingslashit( $i, 'single_paged' );
		}

	} else {

		// use passed post object
		if ( 1 == $i ) {
			$url = get_permalink( $post->ID );
		} else {
			if ( '' == get_option( 'permalink_structure' ) || in_array( $post->post_status, array( 'draft', 'pending' ) ) )
				$url = add_query_arg( 'page', $i, get_permalink( $post->ID ) );
			elseif ( 'page' == get_option( 'show_on_front' ) AND get_option( 'page_on_front' ) == $post->ID )
				$url = trailingslashit( get_permalink( $post->ID ) ) . user_trailingslashit( "$wp_rewrite->pagination_base/" . $i, 'single_paged' );
			else
				$url = trailingslashit( get_permalink( $post->ID ) ) . user_trailingslashit( $i, 'single_paged' );
		}

	}

	return esc_url( $url );

}
endif; // commentpress_get_post_multipage_url



if ( ! function_exists( 'commentpress_multipager' ) ):
/**
 * Create sane links between pages.
 *
 * @since 3.5
 *
 * @return str $page_links The next page and previous page links
 */
function commentpress_multipager() {

	// set default behaviour
	$defaults = array(
		'before' => '<div class="multipager">',
		'after' => '</div>',
		'link_before' => '',
		'link_after' => '',
		'next_or_number' => 'next',
		'nextpagelink' => '<span class="alignright">' . __( 'Next page', 'commentpress-core' ) . ' &raquo;</span>',
		'previouspagelink' => '<span class="alignleft">&laquo; ' . __( 'Previous page', 'commentpress-core' ) . '</span>',
		'pagelink' => '%',
		'more_file' => '',
		'echo' => 0
	);

	// get page links
	$page_links = wp_link_pages( $defaults );

	// add separator when there are two links
	$page_links = str_replace(
		'a><a',
		'a> <span class="multipager_sep">|</span> <a',
		$page_links
	);

	// get page links
	$page_links .= wp_link_pages( array(
		'before' => '<div class="multipager multipager_all"><span>' . __( 'Pages: ', 'commentpress-core' ) . '</span>',
		'after' => '</div>',
		'pagelink' => '<span class="multipager_link">%</span>',
		'echo' => 0
	) );

	// --<
	return $page_links;

}
endif; // commentpress_multipager



if ( ! function_exists( 'commentpress_add_wp_editor' ) ):
/**
 * Adds our styles to the TinyMCE editor.
 *
 * @since 3.5
 *
 * @param str $mce_css The default TinyMCE stylesheets as set by WordPress
 * @return str $mce_css The list of stylesheets with ours added
 */
function commentpress_add_wp_editor() {

	// init option
	$rich_text = false;

	// kick out if wp_editor doesn't exist
	// TinyMCE will be handled by including the script using the pre- wp_editor() method
	if ( ! function_exists( 'wp_editor' ) ) {
		return false;
	}

	// kick out if plugin not active
	global $commentpress_core;
	if ( ! is_object( $commentpress_core ) ) {
		return false;
	}

	// only allow through if plugin says so
	if ( ! $commentpress_core->display->is_tinymce_allowed() ) {
		return false;
	}

	// add our buttons
	$mce_buttons = apply_filters(

		// filter for plugins
		'cp_tinymce_buttons',

		// basic buttons
		array(
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
			'fullscreen'
		)

	);

	// allow media buttons setting to be overridden
	$media_buttons = apply_filters( 'commentpress_rte_media_buttons', true );

	// access WP version
	global $wp_version;

	// if greater than 3.8
	if ( version_compare( $wp_version, '3.8.9999', '>' ) ) {

		// TinyMCE 4 - allow tinymce config to be overridden
		$tinymce_config = apply_filters(
			'commentpress_rte_tinymce',
			array(
				'theme' => 'modern',
				'statusbar' => false,
			)
		);

		// no need for editor css
		$editor_css = '';

	} else {

		// TinyMCE 3 - allow tinymce config to be overridden
		$tinymce_config = apply_filters(
			'commentpress_rte_tinymce',
			array(
				'theme' => 'advanced',
				'theme_advanced_buttons1' => implode( ',', $mce_buttons ),
				'theme_advanced_statusbar_location' => 'none',
			)
		);

		// use legacy editor css
		$editor_css = '
			<style type="text/css">
				.wp_themeSkin iframe
				{
					background: #fff;
				}
			</style>
		';

	}

	// allow quicktags setting to be overridden
	$quicktags = apply_filters(
		'commentpress_rte_quicktags',
		array(
			'buttons' => 'strong,em,ul,ol,li,link,close'
		)
	);

	// our settings
	$settings = array(

		// configure comment textarea
		'media_buttons' => $media_buttons,
		'textarea_name' => 'comment',
		'textarea_rows' => 10,

		// might as well start with teeny
		'teeny' => true,

		// give the iframe a white background
		'editor_css' => $editor_css,

		// configure TinyMCE
		'tinymce' => $tinymce_config,

		// configure quicktags
		'quicktags' => $quicktags

	);

	// create editor
	wp_editor(
		'', // initial content
		'comment', // id of comment textarea
		$settings
	);

	// access WP version
	global $wp_version;

	// add styles
	wp_enqueue_style(
		'commentpress-editor-css',
		wp_admin_css_uri( 'css/edit' ),
		array( 'dashicons', 'open-sans' ),
		$wp_version, // version
		'all' // media
	);

	// don't show textarea
	return true;

}
endif; // commentpress_add_wp_editor



if ( ! function_exists( 'commentpress_assign_default_editor' ) ):
/**
 * Makes TinyMCE the default editor on the front end.
 *
 * @since 3.0
 *
 * @param str $r The default editor as set by WordPress
 * @return str 'tinymce' our overridden default editor
 */
function commentpress_assign_default_editor( $r ) {

	// only on front-end
	if ( is_admin() ) return $r;

	// always return 'tinymce' as the default editor, or else the comment form will not show up!

	// --<
	return 'tinymce';

}
endif; // commentpress_assign_default_editor

add_filter( 'wp_default_editor', 'commentpress_assign_default_editor', 10, 1 );



if ( ! function_exists( 'commentpress_add_tinymce_styles' ) ):
/**
 * Adds our styles to the TinyMCE editor.
 *
 * @since 3.0
 *
 * @param str $mce_css The default TinyMCE stylesheets as set by WordPress
 * @return str $mce_css The list of stylesheets with ours added
 */
function commentpress_add_tinymce_styles( $mce_css ) {

	// only on front-end
	if ( is_admin() ) return $mce_css;

	// add comma if not empty
	if ( ! empty( $mce_css ) ) { $mce_css .= ','; }

	// add our editor styles
	$mce_css .= get_template_directory_uri() . '/assets/css/comment-form.css';

	// --<
	return $mce_css;

}
endif; // commentpress_add_tinymce_styles

// add filter for the above
add_filter( 'mce_css', 'commentpress_add_tinymce_styles' );



if ( ! function_exists( 'commentpress_add_tinymce_nextpage_button' ) ):
/**
 * Adds the Next Page button to the TinyMCE editor.
 *
 * @since 3.5
 *
 * @param array $buttons The default TinyMCE buttons as set by WordPress
 * @return array $buttons The buttons with More removed
 */
function commentpress_add_tinymce_nextpage_button( $buttons ) {

	// only on back-end
	if ( ! is_admin() ) return $buttons;

	// try and place Next Page after More button
	$pos = array_search( 'wp_more', $buttons, true );

	// is it there?
	if ( $pos !== false ) {

		// get array up to that point
		$tmp_buttons = array_slice( $buttons, 0, $pos + 1 );

		// add Next Page button
		$tmp_buttons[] = 'wp_page';

		// recombine
		$buttons = array_merge( $tmp_buttons, array_slice( $buttons, $pos + 1 ) );

	}

	// --<
	return $buttons;

}
endif; // commentpress_add_tinymce_nextpage_button

// add filter for the above
add_filter( 'mce_buttons', 'commentpress_add_tinymce_nextpage_button' );



if ( ! function_exists( 'commentpress_assign_editor_buttons' ) ):
/**
 * Assign our buttons to TinyMCE in teeny mode.
 *
 * @since 3.5
 *
 * @param array $buttons The default editor buttons as set by WordPress
 * @return array The overridden editor buttons
 */
function commentpress_assign_editor_buttons( $buttons ) {

	// basic buttons
	return array(
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
		'fullscreen'
	);

}
endif; // commentpress_assign_editor_buttons

// access WP version
global $wp_version;

// if greater than 3.8
if ( version_compare( $wp_version, '3.8.9999', '>' ) ) {
	add_filter( 'teeny_mce_buttons', 'commentpress_assign_editor_buttons' );
}



if ( ! function_exists( 'commentpress_comment_post_redirect' ) ):
/**
 * Filter comment post redirects for multipage posts.
 *
 * @since 3.5
 *
 * @param str $link The link to the comment
 * @param object $comment The comment object
 */
function commentpress_comment_post_redirect( $link, $comment ) {

	// get URL of the page that submitted the comment
	$page_url = $_SERVER['HTTP_REFERER'];

	// get anchor position
	$hash = strpos( $page_url, '#' );

	// well, do we have an anchor?
	if ( $hash !== false ) {

		// yup, so strip it
		$page_url = substr( $page_url, 0, $hash );

	}

	// assume not AJAX
	$ajax_token = '';

	// is this an AJAX comment form submission?
	if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
		if ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {

			// yes, it's AJAX - some browsers cache POST, so invalidate
			$ajax_token = '?cachebuster=' . time();

			// but, test for pretty permalinks
			if ( false !== strpos( $page_url, '?' ) ) {

				// pretty permalinks are off
				$ajax_token = '&cachebuster=' . time();

			}

		}

	}

	// construct cachebusting comment redirect
	$link = $page_url . $ajax_token . '#comment-' . $comment->comment_ID;

	// --<
	return $link;

}
endif; // commentpress_comment_post_redirect

// add filter for the above, making it run early so it can be overridden by AJAX commenting
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

	// get our shortcode vars
	extract( shortcode_atts( array(
		'id'	=> '',
		'align'	=> 'alignnone',
		'width'	=> '',
		'caption' => ''
	), $attr ) );

	if ( 1 > (int) $width || empty( $caption ) )
		return $content;

	// sanitise id
	if ( $id ) $id = 'id="' . esc_attr( $id ) . '" ';

	// add space prior to alignment
	$alignment = ' ' . esc_attr( $align );

	// get width
	$width = (0 + (int) $width);

	// sanitise caption
	$caption = wp_kses( $caption,

		// allow a few tags
		array(
			'em' => array(),
			'strong' => array(),
			'a' => array( 'href' )
		)

	);

	// force balance those tags
	$caption = balanceTags( $caption, true );

	// construct
	$caption = '<!-- cp_caption_start -->' .
				'<span class="captioned_image' . $alignment . '" style="width: ' . $width . 'px">' .
					'<span ' . $id . ' class="wp-caption">' . do_shortcode( $content ) . '</span>' .
					'<small class="wp-caption-text">' . $caption . '</small>' .
				'</span>' .
				'<!-- cp_caption_end -->';

	// --<
	return $caption;

}
endif; // commentpress_image_caption_shortcode

// add a filter for the above
add_filter( 'img_caption_shortcode', 'commentpress_image_caption_shortcode', 10, 3 );



if ( ! function_exists( 'commentpress_add_commentblock_button' ) ):
/**
 * Add filters for adding our custom TinyMCE button.
 *
 * @since 3.3
 *
 * @return void
 */
function commentpress_add_commentblock_button() {

	// only on back-end
	if ( ! is_admin() ) return;

	// don't bother doing this stuff if the current user lacks permissions
	if ( ! current_user_can( 'edit_posts' ) AND ! current_user_can( 'edit_pages' ) ) {
		return;
	}

	// add only if user can edit in Rich-text Editor mode
	if ( get_user_option( 'rich_editing' ) == 'true' ) {

		add_filter( 'mce_external_plugins', 'commentpress_add_commentblock_tinymce_plugin' );
		add_filter( 'mce_buttons', 'commentpress_register_commentblock_button' );

	}

}
endif; // commentpress_add_commentblock_button

// init process for button control
add_action( 'init', 'commentpress_add_commentblock_button' );



if ( ! function_exists( 'commentpress_register_commentblock_button' ) ):
/**
 * Add our custom TinyMCE button.
 *
 * @since 3.3
 *
 * @param array $buttons The existing button array
 * @return array $buttons The modified button array
 */
function commentpress_register_commentblock_button( $buttons ) {

	// add our button to the editor button array
	array_push( $buttons, '|', 'commentblock' );

	// --<
	return $buttons;

}
endif; // commentpress_register_commentblock_button



if ( ! function_exists( 'commentpress_add_commentblock_tinymce_plugin' ) ):
/**
 * Load the TinyMCE plugin : cp_editor_plugin.js
 *
 * @since 3.3
 *
 * @param array $plugin_array The existing TinyMCE plugin array
 * @return array $plugin_array The modified TinyMCE plugin array
 */
function commentpress_add_commentblock_tinymce_plugin( $plugin_array ) {

	// add comment block
	$plugin_array['commentblock'] = get_template_directory_uri() . '/assets/js/tinymce/cp_editor_plugin.js';

	// --<
	return $plugin_array;

}
endif; // commentpress_add_commentblock_tinymce_plugin



if ( ! function_exists( 'commentpress_trap_empty_search' ) ):
/**
 * Trap empty search queries and redirect.
 *
 * @since 3.3
 *
 * @return str $template The path to the search template
 */
function commentpress_trap_empty_search() {

	// take care of empty searches
	if ( isset( $_GET['s'] ) AND empty( $_GET['s'] ) ) {

		// send to search page
		return locate_template( array( 'search.php' ) );

	}

}
endif; // commentpress_trap_empty_search

// front_page_template filter is deprecated in WP 3.2+
if ( version_compare( $wp_version, '3.2', '>=' ) ) {

	// add filter for the above
	add_filter( 'home_template', 'commentpress_trap_empty_search' );

} else {

	// retain old filter for earlier versions
	add_filter( 'front_page_template', 'commentpress_trap_empty_search' );

}



if ( ! function_exists( 'commentpress_amend_search_query' ) ):
/**
 * Restrict search query to pages only.
 *
 * @since 3.3
 *
 * @param object $query The query object, passed by reference
 * @return void
 */
function commentpress_amend_search_query( &$query ) {

	// restrict to search outside admin (BuddyPress does a redirect to the blog page and so $query->is_search is not set)
	if ( ! is_admin() AND isset( $query->query['s'] ) AND ! empty( $query->query['s'] ) ) {

		// is this a BuddyPress search on the main BuddyPress instance?
		if ( function_exists( 'bp_search_form_type_select' ) AND bp_is_root_blog() ) {

			// search posts and pages
			$query->set( 'post_type', apply_filters( 'commentpress_amend_search_query_post_types', array( 'post', 'page' ) ) );

			// declare access to globals
			global $commentpress_core;

			// if we have the plugin enabled
			if ( is_object( $commentpress_core ) ) {

				// get special pages array, if it's there
				$special_pages = $commentpress_core->db->option_get( 'cp_special_pages' );

				// do we have an array?
				if ( is_array( $special_pages ) ) {

					// exclude them
					$query->set( 'post__not_in', apply_filters( 'commentpress_amend_search_query_exclusions', $special_pages ) );

				}

			}

		}

	}

}
endif; // commentpress_amend_search_query

// add filter for search query modification
add_filter( 'pre_get_posts', 'commentpress_amend_search_query' );



if ( ! function_exists( 'commentpress_amend_password_form' ) ):
/**
 * Adds some style to the password form by adding a class to it.
 *
 * @since 3.3
 *
 * @param str $output The existing password form
 * @return str $output The modified password form
 */
function commentpress_amend_password_form( $output ) {

	// add css class to form
	$output = str_replace( '<form ', '<form class="post_password_form" ', $output );

	// add css class to text field
	$output = str_replace( '<input name="post_password" ', '<input class="post_password_field" name="post_password" ', $output );

	// add css class to submit button
	$output = str_replace( '<input type="submit" ', '<input class="post_password_button" type="submit" ', $output );

	// --<
	return $output;

}
endif; // commentpress_amend_password_form

// add filter for the above
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

	// load license widget definition
	require( COMMENTPRESS_PLUGIN_PATH . 'commentpress-core/assets/widgets/widget-license.php' );

	// register license widget
	register_widget( 'Commentpress_License_Widget' );

}
endif; // commentpress_widgets_init

// add action for the above
add_action( 'widgets_init', 'commentpress_widgets_init' );



if ( ! function_exists( 'commentpress_license_image_css' ) ):
/**
 * Amend display of license plugin image.
 *
 * @since 3.3
 *
 * @return str The CSS for amending the license plugin image
 */
function commentpress_license_image_css() {

	// give a bit more room to the image
	return 'display: block; float: left; margin: 0 6px 3px 0;';

}
endif; // commentpress_license_image_css

add_action( 'license_img_style', 'commentpress_license_image_css' );



if ( ! function_exists( 'commentpress_license_widget_compat' ) ):
/**
 * Remove license from footer when widget not active.
 *
 * This is because wp_footer() is not inside #footer
 *
 * @since 3.3
 *
 * @return void
 */
function commentpress_license_widget_compat() {

	// if the widget is not active, (i.e. the plugin is installed but the widget has not been
	// dragged to a sidebar), then DO NOT display the license in the footer as a default
	if ( ! is_active_widget( false, false, 'license-widget', true ) ) {
		remove_action( 'wp_footer', 'license_print_license_html' );
	}

}
endif; // commentpress_license_widget_compat

// do this late, so license ought to be declared by then
add_action( 'widgets_init', 'commentpress_license_widget_compat', 100 );



if ( ! function_exists( 'commentpress_wplicense_compat' ) ):
/**
 * Remove license from footer.
 *
 * This is because wp_footer() is not inside #footer
 *
 * @since 3.3
 *
 * @return void
 */
function commentpress_wplicense_compat() {

	// let's not have the default footer
	remove_action( 'wp_footer', 'cc_showLicenseHtml' );

}
endif; // commentpress_wplicense_compat

// do this late, so license ought to be declared by then
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
 * @param str $title The possibly-empty widget title
 * @param str $id_base The widget ID base
 * @return str $title The non-empty title
 */
function commentpress_widget_title( $title ) {

	// set default title if none present
	if ( empty( $title ) ) {
		$title = __( 'Untitled Widget', 'commentpress-core' );
	}

	// --<
	return $title;

}
endif; // commentpress_widget_title

// make sure widget title is not empty
add_filter( 'widget_title', 'commentpress_widget_title', 10, 1 );



if ( ! function_exists( 'commentpress_groupblog_classes' ) ):
/**
 * Add classes to #content in BuddyPress, so that we can distinguish different groupblog types.
 *
 * @since 3.3
 *
 * @return str $groupblog_class The class for the groupblog
 */
function commentpress_groupblog_classes() {

	// init empty
	$groupblog_class = '';

	// only add classes when bp-groupblog is active
	if ( function_exists( 'get_groupblog_group_id' ) ) {

		// init groupblogtype
		$groupblogtype = 'groupblog';

		// get group blogtype
		$groupblog_type = groups_get_groupmeta( bp_get_current_group_id(), 'groupblogtype' );

		// did we get one?
		if ( $groupblog_type ) {

			// add to default
			$groupblogtype .= ' ' . $groupblog_type;

		}

		// complete
		$groupblog_class = ' class="' . $groupblogtype . '"';

	}

	// --<
	return $groupblog_class;

}
endif; // commentpress_groupblog_classes



if ( ! function_exists( 'commentpress_get_post_version_info' ) ):
/**
 * Get links to previous and next versions, should they exist.
 *
 * @since 3.3
 *
 * @return void
 */
function commentpress_get_post_version_info( $post ) {

	// check for newer version
	$newer_link = '';

	// assume no newer version
	$newer_id = '';

	// set key
	$key = '_cp_newer_version';

	// if the custom field already has a value
	if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

		// get it
		$newer_id = get_post_meta( $post->ID, $key, true );

	}

	// if we've got one
	if ( $newer_id !== '' ) {

		// get post
		$newer_post = get_post( $newer_id );

		// is it published?
		if ( $newer_post->post_status == 'publish' ) {

			// get link
			$link = get_permalink( $newer_post->ID );

			// define title
			$title = __( 'Newer version', 'commentpress-core' );

			// construct anchor
			$newer_link = '<a href="' . $link . '" title="' . $title . '">' . $title . ' &rarr;</a>';

		}

	}

	// check for older version
	$older_link = '';

	// get post with this post's ID as their _cp_newer_version meta value
	$args = array(
		'numberposts' => 1,
		'meta_key' => '_cp_newer_version',
		'meta_value' => $post->ID
	);

	// get the array
	$previous_posts = get_posts( $args );

	// did we get one?
	if ( is_array( $previous_posts ) AND count( $previous_posts ) == 1 ) {

		// get it
		$older_post = $previous_posts[0];

		// is it published?
		if ( $older_post->post_status == 'publish' ) {

			// get link
			$link = get_permalink( $older_post->ID );

			// define title
			$title = __( 'Older version', 'commentpress-core' );

			// construct anchor
			$older_link = '<a href="' . $link . '" title="' . $title . '">&larr; ' . $title . '</a>';

		}

	}

	// did we get either?
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
endif; // commentpress_get_post_version_info



if ( ! function_exists( 'commentpress_get_post_css_override' ) ):
/**
 * Overrride post type by adding a CSS class.
 *
 * @since 3.3
 *
 * @param int $post_id The numeric ID of the post
 * @return str $type_overridden The CSS class
 */
function commentpress_get_post_css_override( $post_id ) {

	// add a class for overridden page types
	$type_overridden = '';

	// declare access to globals
	global $commentpress_core;

	// if we have the plugin enabled
	if ( is_object( $commentpress_core ) ) {

		// default to current blog type
		$type = $commentpress_core->db->option_get( 'cp_blog_type' );

		// set post meta key
		$key = '_cp_post_type_override';

		// but, if the custom field has a value
		if ( get_post_meta( $post_id, $key, true ) !== '' ) {

			// get it
			$overridden_type = get_post_meta( $post_id, $key, true );

			// is it different to the current blog type?
			if ( $overridden_type != $type ) {
				$type_overridden = ' overridden_type-' . $overridden_type;
			}

		}

	}

	// --<
	return $type_overridden;

}
endif; // commentpress_get_post_css_override



if ( ! function_exists( 'commentpress_get_post_title_visibility' ) ):
/**
 * Do we want to show page/post title?
 *
 * @since 3.3
 *
 * @param int $post_id The numeric ID of the post
 * @return bool $hide True if title is shown, false if hidden
 */
function commentpress_get_post_title_visibility( $post_id ) {

	// init hide (show by default)
	$hide = 'show';

	// declare access to globals
	global $commentpress_core;

	// get global hide if we have the plugin enabled
	if ( is_object( $commentpress_core ) ) {
		$hide = $commentpress_core->db->option_get( 'cp_title_visibility' );;
	}

	// set key
	$key = '_cp_title_visibility';

	// get value if the custom field already has one
	if ( get_post_meta( $post_id, $key, true ) != '' ) {
		$hide = get_post_meta( $post_id, $key, true );
	}

	// --<
	return ( $hide == 'show' ) ? true : false;

}
endif; // commentpress_get_post_title_visibility



if ( ! function_exists( 'commentpress_get_post_meta_visibility' ) ):
/**
 * Do we want to show page/post meta?
 *
 * @since 3.3
 *
 * @param int $post_id The numeric ID of the post
 * @return bool $hide_meta True if meta is shown, false if hidden
 */
function commentpress_get_post_meta_visibility( $post_id ) {

	// init hide (hide by default)
	$hide_meta = 'hide';

	// declare access to globals
	global $commentpress_core;

	// if we have the plugin enabled
	if ( is_object( $commentpress_core ) ) {

		// get global hide_meta
		$hide_meta = $commentpress_core->db->option_get( 'cp_page_meta_visibility' );;

		// set key
		$key = '_cp_page_meta_visibility';

		// override with local value if the custom field already has one
		if ( get_post_meta( $post_id, $key, true ) != '' ) {
			$hide_meta = get_post_meta( $post_id, $key, true );
		}

	}

	// --<
	return ( $hide_meta == 'show' ) ? true : false;

}
endif; // commentpress_get_post_meta_visibility



if ( ! function_exists( 'commentpress_sidebars_widgets' ) ):
/**
 * Temporary fix for PHP notice in WP 3.9.
 *
 * @since 3.6
 *
 * @return array $array Existing widgets array
 * @return array $array Modified widgets array
 */
function commentpress_sidebars_widgets( $array ) {

	// prevent errors in Theme Customizer
	if ( ! is_array( $array ) ) {

		// this array is based on the array in wp_install_defaults()
		$array = array(
			'wp_inactive_widgets' => array(),
			'sidebar-1' => array(),
			'sidebar-2' => array(),
			'sidebar-3' => array(),
			'array_version' => 3
		);

	}

	// --<
	return $array;

}
endif; // commentpress_sidebars_widgets

// add filter for above
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
 * @param object $comment The comment
 * @param int|WP_Post $post_id The post ID or WP_Post object.
 */
function commentpress_add_selection_classes( $classes, $class, $comment_id, $comment, $post_id = 0 ) {

	// define key
	$key = '_cp_comment_selection';

	// get current
	$data = get_comment_meta( $comment_id, $key, true );

	// if the comment meta already has a value
	if ( ! empty( $data ) ) {

		// make into an array
		$selection = explode( ',', $data );

		// add to classes
		$classes[] = 'selection-exists';
		$classes[] = 'sel_start-' . $selection[0];
		$classes[] = 'sel_end-' . $selection[1];

	}

	// --<
	return $classes;

}
endif; // commentpress_add_selection_classes

// add filter for above
add_filter( 'comment_class', 'commentpress_add_selection_classes', 100, 4 );



if ( ! function_exists( 'commentpress_bp_activity_css_class' ) ):
/**
 * Update BuddyPress activity CSS class with groupblog type.
 *
 * @since 3.3
 *
 * @param str $existing_class The existing class
 * @param str $existing_class The overridden class
 */
function commentpress_bp_activity_css_class( $existing_class ) {

	// init group blog type
	$groupblog_type = '';

	// get current item
	global $activities_template;
	$current_activity = $activities_template->activity;

	// for group activity
	if ( $current_activity->component == 'groups' ) {

		// get group blogtype
		$groupblog_type = groups_get_groupmeta( $current_activity->item_id, 'groupblogtype' );

		// add space before if we have it
		if ( $groupblog_type ) { $groupblog_type = ' ' . $groupblog_type; }

	}

	// --<
	return $existing_class . $groupblog_type;

}
endif; // commentpress_bp_activity_css_class



if ( ! function_exists( 'commentpress_bp_blog_css_class' ) ):
/**
 * Update BuddyPress Sites Directory blog item CSS class with groupblog type.
 *
 * @since 3.3
 *
 * @param array $classes The existing classes
 * @param array $classes The overridden classes
 */
function commentpress_bp_blog_css_class( $classes ) {

	// is this a groupblog?
	if ( function_exists( 'get_groupblog_group_id' ) ) {

		// access BuddyPress object
		global $blogs_template;

		// get group ID
		$group_id = get_groupblog_group_id( $blogs_template->blog->blog_id );
		if ( is_numeric( $group_id ) ) {

			// get group blogtype
			$groupblog_type = groups_get_groupmeta( $group_id, 'groupblogtype' );

			// did we get one?
			if ( $groupblog_type ) {

				// add classes
				$classes[] = 'bp-groupblog-blog';
				$classes[] = $groupblog_type;

			}

		}

	}

	// --<
	return $classes;

}
endif; // commentpress_bp_blog_css_class



if ( ! function_exists( 'commentpress_bp_group_css_class' ) ):
/**
 * Update BuddyPress Groups Directory group item CSS class with groupblog type.
 *
 * @since 3.3
 *
 * @param array $classes The existing classes
 * @param array $classes The overridden classes
 */
function commentpress_bp_group_css_class( $classes ) {

	// only add classes when bp-groupblog is active
	if ( function_exists( 'get_groupblog_group_id' ) ) {

		// get group blogtype
		$groupblog_type = groups_get_groupmeta( bp_get_group_id(), 'groupblogtype' );

		// did we get one?
		if ( $groupblog_type ) {

			// add class
			$classes[] = $groupblog_type;

		}

	}

	// --<
	return $classes;

}
endif; // commentpress_bp_group_css_class



if ( ! function_exists( 'commentpress_prefix_bp_templates' ) ):
/**
 * Prefixes BuddyPress pages with the div wrappers that CommentPress Core needs.
 *
 * @since 3.3
 *
 * @return void
 */
function commentpress_prefix_bp_templates() {

	// prefixed wrappers
	echo '<div id="wrapper">
		  <div id="main_wrapper" class="clearfix">
		  <div id="page_wrapper">';

}
endif; // commentpress_prefix_bp_templates

// add action for above
add_action( 'bp_before_directory_groupsites_page', 'commentpress_prefix_bp_templates' );



if ( ! function_exists( 'commentpress_suffix_bp_templates' ) ):
/**
 * Suffixes BuddyPress pages with the div wrappers that CommentPress Core needs.
 *
 * @since 3.3
 *
 * @return void
 */
function commentpress_suffix_bp_templates() {

	// prefixed wrappers
	echo '</div><!-- /page_wrapper -->
		  </div><!-- /main_wrapper -->
		  </div><!-- /wrapper -->';

}
endif; // commentpress_suffix_bp_templates

// add action for above
add_action( 'bp_after_directory_groupsites_page', 'commentpress_suffix_bp_templates' );



if ( ! function_exists( 'commentpress_prefix_signup_template' ) ):
/**
 * Prefixes WordPress Signup Page with the div wrappers that CommentPress Core needs.
 *
 * @since 3.8.5
 *
 * @return void
 */
function commentpress_prefix_signup_template() {

	// prefixed wrappers
	echo '<div id="wrapper">
		  <div id="main_wrapper" class="clearfix">
		  <div id="page_wrapper">
		  <div id="content">';

}
endif; // commentpress_prefix_signup_template

// add action for above
add_action( 'before_signup_form', 'commentpress_prefix_signup_template' );



if ( ! function_exists( 'commentpress_suffix_signup_template' ) ):
/**
 * Suffixes WordPress Signup Page with the div wrappers that CommentPress Core needs.
 *
 * @since 3.8.5
 *
 * @return void
 */
function commentpress_suffix_signup_template() {

	// prefixed wrappers
	echo '</div><!-- /content -->
		  </div><!-- /page_wrapper -->
		  </div><!-- /main_wrapper -->
		  </div><!-- /wrapper -->';

}
endif; // commentpress_suffix_signup_template

// add action for above
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
 * @param array $button_args The existing params used to define the button
 * @return array $button_args The modified params used to define the button
 */
function commentpress_unwrap_buddypress_button( $button_args ) {

	// bail if not BP 2.7.x
	if ( ! function_exists( 'bp_core_filter_wp_query' ) ) return $button_args;

	// remove parent element
	$button_args['parent_element'] = '';

	// --<
	return $button_args;

}
endif; // commentpress_unwrap_buddypress_button

// add filters for the above
add_filter( 'bp_get_group_create_button', 'commentpress_unwrap_buddypress_button' );
add_filter( 'bp_get_blog_create_button', 'commentpress_unwrap_buddypress_button' );



