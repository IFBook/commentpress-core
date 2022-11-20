<?php
/**
 * CommentPress Core Common Theme Functions.
 *
 * Functions that all CommentPress themes can use are collected here.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;


// Include our Comment functions file.
require_once COMMENTPRESS_PLUGIN_PATH . 'includes/commentpress-core/assets/includes/theme/theme-comments.php';

// Include our BuddyPress compatibility file.
require_once COMMENTPRESS_PLUGIN_PATH . 'includes/commentpress-core/assets/includes/theme/theme-buddypress.php';

// Include our "Theme Tabs" class file.
require_once COMMENTPRESS_PLUGIN_PATH . 'includes/commentpress-core/assets/includes/theme/theme-tabs.php';



if ( ! function_exists( 'commentpress_admin_header' ) ) :

	/**
	 * Custom admin header.
	 *
	 * @since 3.0
	 */
	function commentpress_admin_header() {

		// Access plugin.
		global $commentpress_core;

		// Init with same colour as theme stylesheets and default in "class-core-database.php".
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

endif;

if ( ! function_exists( 'commentpress_customize_register' ) ) :

	/**
	 * Implements CommentPress Core Theme options in the Theme Customizer.
	 *
	 * @since 3.0
	 *
	 * @param object $wp_customize Theme Customizer object.
	 */
	function commentpress_customize_register( $wp_customize ) {

		// Kick out if plugin not active.
		global $commentpress_core;
		if ( ! is_object( $commentpress_core ) ) {
			return;
		}

		// Add "Site Image".
		commentpress_customize_site_image( $wp_customize );

		// Add "Site Logo".
		commentpress_customize_site_logo( $wp_customize );

		// Add "Header Background Colour".
		commentpress_customize_header_bg_color( $wp_customize );

	}

endif;

// Add callback for the above.
add_action( 'customize_register', 'commentpress_customize_register' );



if ( ! function_exists( 'commentpress_customize_site_image' ) ) :

	/**
	 * Implements CommentPress Core "Site Image" in the Theme Customizer.
	 *
	 * @since 3.8.5
	 *
	 * @param object $wp_customize Theme Customizer object.
	 */
	function commentpress_customize_site_image( $wp_customize ) {

		// Kick out if BuddyPress Group Blog.
		global $commentpress_core;
		if ( $commentpress_core->is_groupblog() ) {
			return;
		}

		// Include our class file.
		include_once COMMENTPRESS_PLUGIN_PATH . 'includes/commentpress-core/assets/includes/theme/class-customizer-site-image.php';

		/*
		// Register control - not needed as yet, but is if we want to fully extend.
		$wp_customize->register_control_type( 'WP_Customize_Site_Image_Control' );
		*/

		// Add customizer section title.
		$wp_customize->add_section(
			'cp_site_image',
			[
				'title' => apply_filters( 'commentpress_customizer_site_image_title', __( 'Site Image', 'commentpress-core' ) ),
				'priority' => 25,
			]
		);

		// Add image setting.
		$wp_customize->add_setting(
			'commentpress_theme_settings[cp_site_image]',
			[
				'default' => '',
				'capability' => 'edit_theme_options',
				'type' => 'option',
			]
		);

		// Add image control.
		$wp_customize->add_control(
			new WP_Customize_Site_Image_Control(
				$wp_customize,
				'cp_site_image',
				[
					'label' => apply_filters( 'commentpress_customizer_site_image_title', __( 'Site Image', 'commentpress-core' ) ),
					'description' => apply_filters( 'commentpress_customizer_site_image_description', __( 'Choose an image to represent this site. Other plugins may use this image to illustrate this site - in multisite directory listings, for example.', 'commentpress-core' ) ),
					'section' => 'cp_site_image',
					'settings' => 'commentpress_theme_settings[cp_site_image]',
					'priority' => 1,
				]
			)
		);

	}

endif;



if ( ! function_exists( 'commentpress_customize_site_logo' ) ) :

	/**
	 * Implements CommentPress Core "Site Logo" in the Theme Customizer.
	 *
	 * @since 3.8.5
	 *
	 * @param object $wp_customize Theme Customizer object.
	 */
	function commentpress_customize_site_logo( $wp_customize ) {

		// Kick out if BuddyPress Group Blog.
		global $commentpress_core;
		if ( $commentpress_core->is_groupblog() ) {
			return;
		}

		// Add customizer section title.
		$wp_customize->add_section(
			'cp_inline_header_image',
			[
				'title' => __( 'Site Logo', 'commentpress-core' ),
				'priority' => 35,
			]
		);

		// Add image setting.
		$wp_customize->add_setting(
			'commentpress_theme_settings[cp_inline_header_image]',
			[
				'default' => '',
				'capability' => 'edit_theme_options',
				'type' => 'option',
			]
		);

		// Add image control.
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				'cp_inline_header_image',
				[
					'label' => __( 'Logo Image', 'commentpress-core' ),
					'description' => apply_filters( 'commentpress_customizer_site_logo_description', __( 'You may prefer to display an image instead of text in the header of your site. The image must be a maximum of 70px tall. If it is less tall, then you can adjust the vertical alignment using the "Top padding in px" setting below.', 'commentpress-core' ) ),
					'section' => 'cp_inline_header_image',
					'settings' => 'commentpress_theme_settings[cp_inline_header_image]',
					'priority' => 1,
				]
			)
		);

		// Add padding setting.
		$wp_customize->add_setting(
			'commentpress_theme_settings[cp_inline_header_padding]',
			[
				'default' => '',
				'capability' => 'edit_theme_options',
				'type' => 'option',
			]
		);

		// Add text control.
		$wp_customize->add_control(
			'commentpress_theme_settings[cp_inline_header_padding]',
			[
				'label' => __( 'Top padding in px', 'commentpress-core' ),
				'section' => 'cp_inline_header_image',
				'type' => 'text',
			]
		);

	}

endif;



if ( ! function_exists( 'commentpress_customize_header_bg_color' ) ) :

	/**
	 * Implements CommentPress Core "Header Background Colour" in the Theme Customizer.
	 *
	 * @since 3.8.5
	 *
	 * @param object $wp_customize Theme Customizer object.
	 */
	function commentpress_customize_header_bg_color( $wp_customize ) {

		global $commentpress_core;

		// Add color picker setting.
		$wp_customize->add_setting(
			'commentpress_header_bg_color',
			[
				'default' => '#' . $commentpress_core->db->header_bg_colour,
				//'capability' => 'edit_theme_options',
				//'type' => 'option',
			]
		);

		// Add color picker control.
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'commentpress_header_bg_color',
				[
					'label' => __( 'Header Background Colour', 'commentpress-core' ),
					'section' => 'colors',
					'settings' => 'commentpress_header_bg_color',
				]
			)
		);

	}

endif;



if ( ! function_exists( 'commentpress_admin_menu' ) ) :

	/**
	 * Adds more prominent menu item.
	 *
	 * @since 3.0
	 */
	function commentpress_admin_menu() {

		// Add the Customize link to the admin menu.
		add_theme_page( __( 'Customize', 'commentpress-core' ), __( 'Customize', 'commentpress-core' ), 'edit_theme_options', 'customize.php' );

	}

endif;

// Add callback for the above.
// TODO: Is this necessary?
add_action( 'admin_menu', 'commentpress_admin_menu' );



if ( ! function_exists( 'commentpress_get_header_image' ) ) :

	/**
	 * Function that sets a header foreground image.
	 *
	 * This might be a logo, for example.
	 *
	 * @since 3.0
	 *
	 * @todo Inform Users that header images are using a different method.
	 */
	function commentpress_get_header_image() {

		// Access plugin.
		global $commentpress_core;

		// -------------------------------------------------------------------------
		// If this is a Group Blog, always show Group Avatar
		// -------------------------------------------------------------------------

		// Test for Group Blog.
		if ( is_object( $commentpress_core ) && $commentpress_core->is_groupblog() ) {

			// Get Group ID.
			$group_id = get_groupblog_group_id( get_current_blog_id() );

			// Get Group Avatar.
			$avatar_options = [
				'item_id' => $group_id,
				'object' => 'group',
				'type' => 'full',
				'alt' => __( 'Group avatar', 'commentpress-core' ),
				'class' => 'cp_logo_image',
				'width' => 48,
				'height' => 48,
				'html' => true,
			];

			// Add filter for the function above.
			add_filter( 'bp_core_avatar_url', 'commentpress_fix_bp_core_avatar_url', 10, 1 );

			// Show Group Avatar.
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

		// Test for our new theme customizer option.
		if ( isset( $options['cp_inline_header_image'] ) && ! empty( $options['cp_inline_header_image'] ) ) {

			// Init top padding.
			$style = '';

			// Override if there is top padding.
			if ( isset( $options['cp_inline_header_padding'] ) && ! empty( $options['cp_inline_header_padding'] ) ) {
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
		if ( is_object( $commentpress_core ) && $commentpress_core->db->option_get( 'cp_toc_page' ) ) {

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

endif;



if ( ! function_exists( 'commentpress_get_body_id' ) ) :

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

			// Is this the main Blog?
			if ( is_main_site() ) {

				// Set main Blog ID.
				$body_id = ' id="main_blog"';

			}

		}

		// --<
		return $body_id;

	}

endif;



if ( ! function_exists( 'commentpress_get_body_classes' ) ) :

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

		// Access Post and plugin.
		global $post, $commentpress_core;

		// -------------------- Default Sidebar --------------------------------

		// Set default sidebar but override if we have the plugin enabled.
		$sidebar_flag = 'toc';
		if ( is_object( $commentpress_core ) ) {
			$sidebar_flag = $commentpress_core->get_default_sidebar();
		}

		// Set class per sidebar.
		$sidebar_class = 'cp_sidebar_' . $sidebar_flag;

		// Add to array.
		$classes[] = $sidebar_class;

		// -------------------- Commentable ------------------------------------

		// Init commentable class but override if we have the plugin enabled.
		$commentable = '';
		if ( is_object( $commentpress_core ) ) {
			$commentable = ( $commentpress_core->is_commentable() ) ? 'commentable' : 'not_commentable';
		}

		// Add to array.
		if ( ! empty( $commentable ) ) {
			$classes[] = $commentable;
		}

		// -------------------- Layout -----------------------------------------

		// Init layout class but if we have the plugin enabled.
		$layout_class = '';
		if ( is_object( $commentpress_core ) ) {

			// Is this the Title Page?
			if (
				is_object( $post ) &&
				isset( $post->ID ) &&
				$post->ID == $commentpress_core->db->option_get( 'cp_welcome_page' )
			) {

				// Init layout.
				$layout = '';

				// Set key.
				$key = '_cp_page_layout';

				// Get it if the custom field already has a value.
				if ( get_post_meta( $post->ID, $key, true ) != '' ) {
					$layout = get_post_meta( $post->ID, $key, true );
				}

				// Set layout class if wide layout.
				if ( $layout == 'wide' ) {
					$layout_class = 'full_width';
				}

			}

		}

		// Add to array.
		if ( ! empty( $layout_class ) ) {
			$classes[] = $layout_class;
		}

		// -------------------- Page Type --------------------------------------

		// Set default Page Type.
		$page_type = '';

		// Add Blog Post class if Blog Post.
		if ( is_single() ) {
			$page_type = 'blog_post';
		}

		// If we have the plugin enabled.
		if ( is_object( $commentpress_core ) ) {

			// Add BuddyPress Page class on BuddyPress Special Pages.
			if ( $commentpress_core->is_buddypress_special_page() ) {
				$page_type = 'buddypress_page';
			}

			// Add BuddyPress Page class on CommentPress Core Special Pages.
			if ( $commentpress_core->db->is_special_page() ) {
				$page_type = 'commentpress_page';
			}

		}

		// Add to array.
		if ( ! empty( $page_type ) ) {
			$classes[] = $page_type;
		}

		// -------------------- Is Group Blog ----------------------------------

		// Set default type.
		$is_groupblog = 'not-groupblog';

		// If we have the plugin enabled.
		if ( is_object( $commentpress_core ) ) {

			// If it's a Group Blog.
			if ( $commentpress_core->is_groupblog() ) {
				$is_groupblog = 'is-groupblog';
			}

		}

		// Add to array.
		if ( ! empty( $is_groupblog ) ) {
			$classes[] = $is_groupblog;
		}

		// -------------------- Blog Type --------------------------------------

		// Set default type.
		$blog_type = '';

		// If we have the plugin enabled.
		if ( is_object( $commentpress_core ) ) {

			// Get type.
			$type = $commentpress_core->db->option_get( 'cp_blog_type' );

			// Get Workflow.
			$workflow = $commentpress_core->db->option_get( 'cp_blog_workflow' );

			/**
			 * Allow plugins to override the Blog Type - for example if Workflow
			 * is enabled, it might become a new Blog Type as far as BuddyPress
			 * is concerned.
			 *
			 * @since 3.3
			 *
			 * @param int $type The numeric Blog Type.
			 * @param bool $workflow True if Workflow enabled, false otherwise.
			 */
			$current_blog_type = apply_filters( 'cp_get_group_meta_for_blog_type', $type, $workflow );

			// If it's not the Main Site, add class.
			if ( is_multisite() && ! is_main_site() ) {
				$blog_type = 'blogtype-' . intval( $current_blog_type );
			}

		}

		// Add to array.
		if ( ! empty( $blog_type ) ) {
			$classes[] = $blog_type;
		}

		// -------------------- Group Blog Type ---------------------------------

		// When viewing a Group, set default Group Blog Type.
		$group_groupblog_type = '';

		// If we have the plugin enabled.
		if ( is_object( $commentpress_core ) ) {

			// Is it a BuddyPress Group Page?
			if ( function_exists( 'bp_is_groups_component' ) && bp_is_groups_component() ) {

				// Get current Group.
				$current_group = groups_get_current_group();

				// Sanity check.
				if ( $current_group instanceof BP_Groups_Group ) {

					// Get Group Blog Type.
					$groupblogtype = groups_get_groupmeta( $current_group->id, 'groupblogtype' );

					// Set Group Blog Type if present.
					if ( ! empty( $groupblogtype ) ) {
						$group_groupblog_type = $groupblogtype;
					}

				}

			}

		}

		// Add to array.
		if ( ! empty( $group_groupblog_type ) ) {
			$classes[] = $group_groupblog_type;
		}

		// -------------------- TinyMCE version --------------------------------

		// TinyMCE is v4 since WordPress 3.9.
		$classes[] = 'tinymce-4';

		// -------------------- Process ----------------------------------------

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

endif;



if ( ! function_exists( 'commentpress_document_title_parts' ) ) :

	/**
	 * Add the root network name when the sub-blog is a Group Blog.
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

			// If it's a Group Blog.
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

endif;

// Add callback for the above.
add_filter( 'document_title_parts', 'commentpress_document_title_parts' );



if ( ! function_exists( 'commentpress_document_title_separator' ) ) :

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

endif;

// Add callback for the above.
add_filter( 'document_title_separator', 'commentpress_document_title_separator' );



if ( ! function_exists( 'commentpress_site_title' ) ) :

	/**
	 * Amend the Site title depending on context of Blog.
	 *
	 * @since 3.8
	 *
	 * @param string $sep The title separator.
	 * @param boolean $echo Echo the result or not.
	 * @return string $site_name The title of the Site.
	 */
	function commentpress_site_title( $sep = '', $echo = true ) {

		// Is this multisite?
		if ( is_multisite() ) {

			// If we're on a sub-blog.
			if ( ! is_main_site() ) {

				$current_site = get_current_site();

				// Print?
				if ( $echo ) {

					// Add Site name.
					echo ' ' . trim( $sep ) . ' ' . $current_site->site_name;

				} else {

					// Add Site name.
					return ' ' . trim( $sep ) . ' ' . $current_site->site_name;

				}

			}

		}

	}

endif;



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
			if ( $queried_post instanceof WP_Post ) {

				// Maybe use excerpt.
				$excerpt = wp_strip_all_tags( $queried_post->post_excerpt );
				if ( ! empty( $excerpt ) ) {
					$description = esc_attr( $excerpt );
				} else {

					// Maybe use trimmed content.
					$content = wp_strip_all_tags( $queried_post->post_content );
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

endif;



if ( ! function_exists( 'commentpress_remove_more_jump_link' ) ) :

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

		if ( $offset ) {
			$end = strpos( $link, '"', $offset );
			if ( $end ) {
				$link = substr_replace( $link, '', $offset, $end - $offset );
			}
		}

		// --<
		return $link;

	}

endif;

// Add callback for the above.
add_filter( 'the_content_more_link', 'commentpress_remove_more_jump_link' );



if ( ! function_exists( 'commentpress_page_title' ) ) :

	/**
	 * Builds a Page title, including parent Page titles.
	 *
	 * The CommentPress Core Default theme displays a "cookie trail" style title for
	 * Pages so we need to build this by inspecting Page ancestors.
	 *
	 * @since 3.0
	 *
	 * @return string $title The Page title.
	 */
	function commentpress_page_title() {

		// Declare access to globals.
		global $commentpress_core, $post;

		// Init.
		$title = '';
		$sep = ' &#8594; ';

		/*
		// Maybe use Blog title.
		$title .= get_bloginfo( 'name' );
		*/

		if ( is_page() || is_single() || is_category() ) {

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

			/*
			// Maybe handle single
			if ( is_single() ) {
				$category = get_the_category();
				$title .= $sep . $category[0]->cat_name;
			}
			*/

			if ( is_category() ) {
				$category = get_the_category();
				$title .= $category[0]->cat_name . $sep;
			}

			// Current Page.
			if ( is_page() || is_single() ) {
				$title .= get_the_title();
			}

		}

		// --<
		return $title;

	}

endif;



if ( ! function_exists( 'commentpress_has_page_children' ) ) :

	/**
	 * Query whether a given Page has children.
	 *
	 * @since 3.3
	 *
	 * @param object $page_obj The WordPress Page object to query.
	 * @return boolean True if Page has children, false otherwise.
	 */
	function commentpress_has_page_children( $page_obj ) {

		// Init to look for published Pages.
		$defaults = [
			'post_parent' => $page_obj->ID,
			'post_type' => 'page',
			'numberposts' => -1,
			'post_status' => 'publish',
		];

		// Get Page children.
		$kids =& get_children( $defaults );

		// Do we have any?
		return ( empty( $kids ) ) ? false : true;

	}

endif;



if ( ! function_exists( 'commentpress_echo_post_meta' ) ) :

	/**
	 * Show User(s) in the loop.
	 *
	 * @since 3.0
	 */
	function commentpress_echo_post_meta() {

		// Bail if this is a BuddyPress Page.
		if ( function_exists( 'is_buddypress' ) && is_buddypress() ) {
			return;
		}

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
				foreach ( $authors as $author ) {

					// Default to comma.
					$sep = ', ';

					// If we're on the penultimate.
					if ( $n == ( $author_count - 1 ) ) {

						// Use ampersand.
						$sep = __( ' &amp; ', 'commentpress-core' );

					}

					// If we're on the last, don't add.
					if ( $n == $author_count ) {
						$sep = '';
					}

					// Get name.
					$author_html .= commentpress_echo_post_author( $author->ID, false );

					// Add separator.
					$author_html .= $sep;

					// Increment.
					$n++;

					// Yes - are we showing avatars?
					if ( get_option( 'show_avatars' ) ) {

						// Get avatar.
						echo get_avatar( $author->ID, $size = '32' );

					}

				}

				?><cite class="fn"><?php echo $author_html; ?></cite>

				<p><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_date( get_option( 'date_format' ) ) ); ?></a></p>

				<?php

			}

		} else {

			// Get avatar.
			$author_id = get_the_author_meta( 'ID' );
			echo get_avatar( $author_id, $size = '32' );

			?>

			<cite class="fn"><?php commentpress_echo_post_author( $author_id ); ?></cite>

			<p><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_date( get_option( 'date_format' ) ) ); ?></a></p>

			<?php

		}

	}

endif;



if ( ! function_exists( 'commentpress_show_source_url' ) ) :

	/**
	 * Show source URL for print.
	 *
	 * @since 3.5
	 */
	function commentpress_show_source_url() {

		// Add the URL - hidden, but revealed by print stylesheet.
		?>
		<p class="hidden_page_url">
			<?php

			// Label.
			echo __( 'Source: ', 'commentpress-core' );

			// Path from server array, if set.
			$path = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';

			// Get server, if set.
			$server = isset( $_SERVER['SERVER_NAME'] ) ? wp_unslash( $_SERVER['SERVER_NAME'] ) : '';

			// Get protocol, if set.
			$protocol = ! empty( $_SERVER['HTTPS'] ) ? 'https' : 'http';

			// Construct URL.
			$url = $protocol . '://' . $server . $path;

			// Echo.
			echo $url;

			?>
		</p>
		<?php

	}

endif;

// Add callback for the above.
add_action( 'wp_footer', 'commentpress_show_source_url' );



if ( ! function_exists( 'commentpress_echo_post_author' ) ) :

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

		// Kick out if we don't have a User with that ID.
		if ( ! is_object( $user ) ) {
			return;
		}

		// Access plugin.
		global $commentpress_core, $post;

		// If we have the plugin enabled. and it's BuddyPress.
		if ( is_object( $post ) && is_object( $commentpress_core ) && $commentpress_core->is_buddypress() ) {

			// Construct User link.
			$author = bp_core_get_userlink( $user->ID );

		} else {

			// Link to theme's Author Page.
			$link = sprintf(
				'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
				get_author_posts_url( $user->ID, $user->user_nicename ),
				esc_attr( sprintf( __( 'Posts by %s', 'commentpress-core' ), $user->display_name ) ),
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

endif;



if ( ! function_exists( 'commentpress_show_activity_tab' ) ) :

	/**
	 * Decide whether or not to show the Activity Sidebar.
	 *
	 * @since 3.3
	 *
	 * @return bool True if we show the Activity Tab, false otherwise.
	 */
	function commentpress_show_activity_tab() {

		// Declare access to globals.
		global $commentpress_core, $post;

		/*
		// If we have the plugin enabled.
		if ( is_object( $commentpress_core ) ) {

			// Is this multisite?
			if (
				( is_multisite() && is_main_site() && $commentpress_core->is_buddypress_special_page() ) ||
				! is_object( $post )
			) {

				// Ignore Activity.
				return false;

			}

		}
		*/

		// --<
		return true;

	}

endif;



if ( ! function_exists( 'commentpress_is_commentable' ) ) :

	/**
	 * Is a Post/Page commentable?
	 *
	 * @since 3.3
	 *
	 * @return bool $is_commentable True if Page can have Comments, false otherwise.
	 */
	function commentpress_is_commentable() {

		// Declare access to plugin.
		global $commentpress_core;

		// If we have it.
		if ( is_object( $commentpress_core ) ) {

			// Return what it reports.
			return $commentpress_core->is_commentable();

		}

		// --<
		return false;

	}

endif;



if ( ! function_exists( 'commentpress_lexia_support_mime' ) ) :

	/**
	 * The "media" Post Type needs more granular naming support.
	 *
	 * @since 3.9
	 *
	 * @param str $post_type_name The existing singular name of the Post Type.
	 * @param str $post_type The Post Type identifier.
	 * @return str $post_type_name The modified singular name of the Post Type.
	 */
	function commentpress_lexia_support_mime( $post_type_name, $post_type ) {

		// Only handle media.
		if ( $post_type != 'attachment' ) {
			return $post_type_name;
		}

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

endif;

// Add callback for the above.
add_filter( 'commentpress_lexia_post_type_name', 'commentpress_lexia_support_mime', 10, 2 );



if ( ! function_exists( 'commentpress_lexia_modify_entity_text' ) ) :

	/**
	 * The "media" Post Type needs more granular naming support.
	 *
	 * @since 3.9
	 *
	 * @param str $entity_text The current entity text.
	 * @param str $post_type_name The singular name of the Post Type.
	 * @param str $post_type The Post Type identifier.
	 * @return str $entity_text The modified entity text.
	 */
	function commentpress_lexia_modify_entity_text( $entity_text, $post_type_name, $post_type ) {

		// Only handle media.
		if ( $post_type != 'attachment' ) {
			return $entity_text;
		}

		// Override entity text.
		$entity_text = sprintf(
			__( 'the %s', 'commentpress-core' ),
			$post_type_name
		);

		// --<
		return $entity_text;

	}

endif;

// Add callback for the above.
add_filter( 'commentpress_lexia_whole_entity_text', 'commentpress_lexia_modify_entity_text', 10, 3 );



if ( ! function_exists( 'commentpress_get_full_name' ) ) :

	/**
	 * Utility to concatenate names.
	 *
	 * @since 3.0
	 *
	 * @param str $forename The WordPress User's first name.
	 * @param str $surname The WordPress User's last name.
	 * @return str $fullname The WordPress User's full name.
	 */
	function commentpress_get_full_name( $forename, $surname ) {

		// Init return.
		$fullname = '';

		// Add forename.
		if ( $forename != '' ) {
			$fullname .= $forename;
		}

		// Add surname.
		if ( $surname != '' ) {
			$fullname .= ' ' . $surname;
		}

		// Strip any whitespace.
		$fullname = trim( $fullname );

		// --<
		return $fullname;

	}

endif;



if ( ! function_exists( 'commentpress_excerpt_length' ) ) :

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

endif;

// Add callback for excerpt length.
add_filter( 'excerpt_length', 'commentpress_excerpt_length' );



if ( ! function_exists( 'commentpress_add_link_css' ) ) :

	/**
	 * Utility to add button css class to Blog nav links.
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

endif;

// Add callback for next/previous links.
add_filter( 'previous_post_link', 'commentpress_add_link_css' );
add_filter( 'next_post_link', 'commentpress_add_link_css' );



if ( ! function_exists( 'commentpress_get_link_css' ) ) :

	/**
	 * Utility to add button css class to Blog nav links.
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

endif;

// Add callback for next/previous Posts links.
add_filter( 'previous_posts_link_attributes', 'commentpress_get_link_css' );
add_filter( 'next_posts_link_attributes', 'commentpress_get_link_css' );



if ( ! function_exists( 'commentpress_multipager' ) ) :

	/**
	 * Create sane links between Pages.
	 *
	 * @since 3.5
	 *
	 * @return str $page_links The Next Page and Previous Page links.
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

		// Get Page links.
		$page_links = wp_link_pages( $defaults );

		// Add separator when there are two links.
		$page_links = str_replace(
			'a><a',
			'a> <span class="multipager_sep">|</span> <a',
			$page_links
		);

		// Get Page links.
		$page_links .= wp_link_pages( [
			'before' => '<div class="multipager multipager_all"><span>' . __( 'Pages: ', 'commentpress-core' ) . '</span>',
			'after' => '</div>',
			'pagelink' => '<span class="multipager_link">%</span>',
			'echo' => 0,
		] );

		// --<
		return $page_links;

	}

endif;



if ( ! function_exists( 'commentpress_trap_empty_search' ) ) :

	/**
	 * Trap empty search queries and redirect.
	 *
	 * @since 3.3
	 *
	 * @return str $template The path to the search template.
	 */
	function commentpress_trap_empty_search() {

		// Send to Search Page when there is an empty search.
		if ( isset( $_GET['s'] ) && empty( $_GET['s'] ) ) {
			return locate_template( [ 'search.php' ] );
		}

	}

endif;

// Add callback for the above.
add_filter( 'home_template', 'commentpress_trap_empty_search' );



if ( ! function_exists( 'commentpress_amend_password_form' ) ) :

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

endif;

// Add callback for the above.
add_filter( 'the_password_form', 'commentpress_amend_password_form' );



if ( ! function_exists( 'commentpress_widgets_init' ) ) :

	/**
	 * Register CommentPress Widgets.
	 *
	 * Widget areas (dynamic sidebars) are defined on a per-theme basis in their
	 * functions.php file or similar.
	 *
	 * @since 3.3
	 */
	function commentpress_widgets_init() {

		// Load License Widget definition.
		require COMMENTPRESS_PLUGIN_PATH . 'includes/commentpress-core/assets/widgets/widget-license.php';

		// Register License Widget.
		register_widget( 'CommentPress_License_Widget' );

	}

endif;

// Add callback for the above.
add_action( 'widgets_init', 'commentpress_widgets_init' );



if ( ! function_exists( 'commentpress_license_image_css' ) ) :

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

endif;

// Add callback for the above.
add_action( 'license_img_style', 'commentpress_license_image_css' );



if ( ! function_exists( 'commentpress_license_widget_compat' ) ) :

	/**
	 * Remove license from footer when Widget not active.
	 *
	 * This is because wp_footer() is not inside #footer.
	 *
	 * @since 3.3
	 */
	function commentpress_license_widget_compat() {

		// If the Widget is not active, (i.e. the plugin is installed but the Widget has not been
		// dragged to a sidebar), then DO NOT display the license in the footer as a default.
		if ( ! is_active_widget( false, false, 'license-widget', true ) ) {
			remove_action( 'wp_footer', 'license_print_license_html' );
		}

	}

endif;

// Do this late, so license ought to be declared by then.
add_action( 'widgets_init', 'commentpress_license_widget_compat', 100 );



if ( ! function_exists( 'commentpress_wplicense_compat' ) ) :

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

endif;

// Do this late, so license ought to be declared by then.
add_action( 'init', 'commentpress_wplicense_compat', 100 );



if ( ! function_exists( 'commentpress_widget_title' ) ) :

	/**
	 * Ensure that Widget title is not empty.
	 *
	 * Empty Widget titles break the layout of the theme at present, because the
	 * enclosing markup for the sub-section is split between the 'after_title' and
	 * 'after_widget' substitutions in the theme register_sidebar() declarations.
	 *
	 * Note: #footer Widget titles are hidden via CSS. Override this in your child
	 * theme to show them collectively or individually.
	 *
	 * @since 3.8.10
	 *
	 * @param str $title The possibly-empty Widget title.
	 * @return str $title The non-empty title.
	 */
	function commentpress_widget_title( $title = '' ) {

		// Set default title if none present.
		if ( empty( $title ) ) {
			$title = __( 'Untitled Widget', 'commentpress-core' );
		}

		// --<
		return $title;

	}

endif;

// Make sure Widget title is not empty.
add_filter( 'widget_title', 'commentpress_widget_title', 10, 1 );



if ( ! function_exists( 'commentpress_get_post_version_info' ) ) :

	/**
	 * Echoes links to previous and next versions, should they exist.
	 *
	 * @since 3.3
	 *
	 * @param WP_Post $post The WordPress Post object.
	 */
	function commentpress_get_post_version_info( $post ) {

		// Sanity check.
		if ( ! ( $post instanceof WP_Post ) ) {
			return;
		}

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return;
		}

		// Get newer link for this Post.
		$newer_link = $core->revisions->link_next_get( $post->ID );

		// Get older link for this Post.
		$older_link = $core->revisions->link_previous_get( $post->ID );

		// Bail if we didn't either of them.
		if ( empty( $newer_link ) && empty( $older_link ) ) {
			return;
		}

		?>
		<div class="version_info clear">
			<ul>
				<?php if ( ! empty( $newer_link ) ) : ?>
					<li class="newer_version"><?php echo $newer_link; ?></li>
				<?php endif; ?>
				<?php if ( ! empty( $older_link ) ) : ?>
					<li class="older_version"><?php echo $older_link; ?></li>
				<?php endif; ?>
			</ul>
		</div>
		<?php

	}

endif;



if ( ! function_exists( 'commentpress_get_post_css_override' ) ) :

	/**
	 * Override Post Type by adding a CSS class.
	 *
	 * @since 3.3
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @return str $class_name The CSS class.
	 */
	function commentpress_get_post_css_override( $post_id ) {

		// A class for overridden Page Types.
		$class_name = '';

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return $class_name;
		}

		// Check if the Formatter for this Post is overridden.
		$overridden = $core->formatter->formatter_is_overridden( $post_id );

		// Bail if not overridden.
		if ( $overridden === false ) {
			return $class_name;
		}

		// Get the Formatter for this Post.
		$formatter = $core->formatter->formatter_get( $post_id );

		// Build class.
		$class_name = ' overridden_type-' . $formatter;

		// --<
		return $class_name;

	}

endif;



if ( ! function_exists( 'commentpress_get_post_title_visibility' ) ) :

	/**
	 * Do we want to show Page/Post title?
	 *
	 * @since 3.3
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @return bool $hide True if title is shown, false if hidden.
	 */
	function commentpress_get_post_title_visibility( $post_id ) {

		// Init hide (show by default).
		$hide = 'show';

		// Declare access to globals.
		global $commentpress_core;

		// Get global hide if we have the plugin enabled.
		if ( is_object( $commentpress_core ) ) {
			$hide = $commentpress_core->db->option_get( 'cp_title_visibility' );
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

endif;



if ( ! function_exists( 'commentpress_get_post_meta_visibility' ) ) :

	/**
	 * Do we want to show Page/Post meta?
	 *
	 * @since 3.3
	 *
	 * @param int $post_id The numeric ID of the Post.
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
			$hide_meta = $commentpress_core->db->option_get( 'cp_page_meta_visibility' );

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

endif;



if ( ! function_exists( 'commentpress_add_selection_classes' ) ) :

	/**
	 * Filter the comment class to add selection data.
	 *
	 * @since 3.8
	 *
	 * @param array $classes An array of comment classes.
	 * @param string $class A comma-separated list of additional classes added to the list.
	 * @param int $comment_id The comment id.
	 * @param object $comment The comment.
	 * @param int|WP_Post $post_id The Post ID or WP_Post object.
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

endif;

// Add callback for the above.
add_filter( 'comment_class', 'commentpress_add_selection_classes', 100, 4 );



if ( ! function_exists( 'commentpress_prefix_signup_template' ) ) :

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

endif;

// Add callback for the above.
add_action( 'before_signup_form', 'commentpress_prefix_signup_template' );



if ( ! function_exists( 'commentpress_suffix_signup_template' ) ) :

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

endif;

// Add callback for the above.
add_action( 'after_signup_form', 'commentpress_suffix_signup_template' );



if ( ! function_exists( 'commentpress_geomashup_map_get' ) ) :

	/**
	 * Show the map for a Post.
	 *
	 * Does not work in non-global loops, such as those made via WP_Query.
	 *
	 * @since 3.9.9
	 */
	function commentpress_geomashup_map_get() {

		// Bail if Geo Mashup not active.
		if ( ! class_exists( 'GeoMashup' ) ) {
			return;
		}

		// Bail if Post has no location.
		$location = GeoMashup::current_location( null, 'post' );
		if ( empty( $location ) ) {
			return;
		}

		// Show map.
		echo '<div class="geomap">' . GeoMashup::map() . '</div>';

	}

endif;
