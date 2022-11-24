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



// Include our Theme filters file.
require_once COMMENTPRESS_PLUGIN_PATH . 'includes/core/assets/includes/theme/theme-filters.php';

// Include our Theme Customizer file.
require_once COMMENTPRESS_PLUGIN_PATH . 'includes/core/assets/includes/theme/theme-customizer.php';

// Include our Comment functions file.
require_once COMMENTPRESS_PLUGIN_PATH . 'includes/core/assets/includes/theme/theme-comments.php';

// Include our BuddyPress compatibility file.
require_once COMMENTPRESS_PLUGIN_PATH . 'includes/core/assets/includes/theme/theme-buddypress.php';

// Include our Navigation file.
require_once COMMENTPRESS_PLUGIN_PATH . 'includes/core/assets/includes/theme/theme-navigation.php';



if ( ! function_exists( 'commentpress_admin_header' ) ) :

	/**
	 * Custom admin header.
	 *
	 * @since 3.0
	 */
	function commentpress_admin_header() {

		// Get core plugin reference.
		$core = commentpress_core();

		// Init with same colour as theme stylesheets and default in "class-core-database.php".
		$colour = '2c2622';

		// Override if we have the plugin enabled.
		if ( ! empty( $core ) ) {
			$colour = $core->theme->header_bg_color_get();
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



if ( ! function_exists( 'commentpress_header_body_template' ) ) :

	/**
	 * Loads the Header Body template.
	 *
	 * @since 4.0
	 */
	function commentpress_header_body_template() {

		// Try to locate template.
		$template = locate_template( 'assets/templates/header_body.php' );

		/**
		 * Filters the located Header Body template.
		 *
		 * @since 3.4
		 *
		 * @param str $template The path to the Header Body template.
		 */
		$template = apply_filters( 'cp_template_header_body', $template );

		// Load it if we find it.
		if ( ! empty( $template ) ) {
			load_template( $template, false );
		}

	}

endif;



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

		// Get core plugin reference.
		$core = commentpress_core();

		// -------------------------------------------------------------------------
		// If this is a Group Blog, always show Group Avatar
		// -------------------------------------------------------------------------
		if ( ! empty( $core ) && $core->bp->is_groupblog() ) {

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

		/**
		 * Allows plugins to short-circuit the Header Image.
		 *
		 * @since 3.4
		 *
		 * @param bool False by default. Return true to short-circuit.
		 */
		$custom_avatar_pre = apply_filters( 'commentpress_header_image_pre_customizer', false );

		// Show it if we get an override.
		if ( $custom_avatar_pre !== false ) {
			echo $custom_avatar_pre;
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

			/**
			 * Filters the uploaded image markup.
			 *
			 * @since 3.4
			 *
			 * @param string The uploaded image markup.
			 */
			echo apply_filters( 'commentpress_header_image', '<img src="' . $options['cp_inline_header_image'] . '" class="cp_logo_image"' . $style . ' alt="' . __( 'Logo', 'commentpress-core' ) . '" />' );

			// --<
			return;

		}

		// -------------------------------------------------------------------------
		// Allow plugins to hook in after Theme Customizer.
		// -------------------------------------------------------------------------

		/**
		 * Allows plugins to short-circuit the Header Image.
		 *
		 * @since 3.4
		 *
		 * @param bool False by default. Return true to short-circuit fallback Header Image.
		 */
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
		if ( ! empty( $core ) && $core->db->option_get( 'cp_toc_page' ) ) {

			// Set defaults.
			$args = [
				'post_type' => 'attachment',
				'numberposts' => 1,
				'post_status' => null,
				'post_parent' => $core->db->option_get( 'cp_toc_page' ),
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

		// Access Post.
		global $post;

		// Get core plugin reference.
		$core = commentpress_core();

		// -------------------- Default Sidebar --------------------------------

		// Set default sidebar but override if we have the plugin enabled.
		$sidebar_flag = 'toc';
		if ( ! empty( $core ) ) {
			$sidebar_flag = $core->theme->get_default_sidebar();
		}

		// Set class per sidebar.
		$sidebar_class = 'cp_sidebar_' . $sidebar_flag;

		// Add to array.
		$classes[] = $sidebar_class;

		// -------------------- Commentable ------------------------------------

		// Add commentable class to array.
		$classes[] = commentpress_is_commentable() ? 'commentable' : 'not_commentable';

		// -------------------- Layout -----------------------------------------

		// Init layout class but if we have the plugin enabled.
		$layout_class = '';
		if ( ! empty( $core ) ) {

			// Is this the Title Page?
			if ( ( $post instanceof WP_Post ) && (int) $post->ID === (int) $core->db->option_get( 'cp_welcome_page' ) ) {

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
		if ( ! empty( $core ) ) {

			// Add BuddyPress Page class on BuddyPress Special Pages.
			if ( $core->bp->is_buddypress_special_page() ) {
				$page_type = 'buddypress_page';
			}

			// Add BuddyPress Page class on CommentPress Core Special Pages.
			if ( $core->pages_legacy->is_special_page() ) {
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

		// If it's a Group Blog.
		if ( ! empty( $core ) && $core->bp->is_groupblog() ) {
			$is_groupblog = 'is-groupblog';
		}

		// Add to array.
		$classes[] = $is_groupblog;

		// -------------------- Blog Type --------------------------------------

		// Set default type.
		$blog_type = '';

		// If we have the plugin enabled.
		if ( ! empty( $core ) ) {

			// Get current Blog Type.
			$current_blog_type = $core->db->option_get( 'cp_blog_type' );

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
		if ( ! empty( $core ) ) {

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

		/**
		 * Filters the body classes attribute.
		 *
		 * @since 3.4
		 *
		 * @param string The body classes attribute.
		 */
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

		// Access globals.
		global $post;

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

		// Bail if we don't have a User.
		if ( ! ( $user instanceof WP_User ) ) {
			return '';
		}

		// Access plugin.
		global $post;

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return '';
		}

		// If we have the plugin enabled and it's BuddyPress.
		if ( ( $post instanceof WP_Post ) && ! empty( $core ) && $core->bp->is_buddypress() ) {

			// Construct User link.
			$author = bp_core_get_userlink( $user->ID );

		} else {

			// Link to theme's Author Page.
			$link = sprintf(
				'<a href="%s" title="%s" rel="author">%s</a>',
				get_author_posts_url( $user->ID, $user->user_nicename ),
				esc_attr( sprintf( __( 'Posts by %s', 'commentpress-core' ), $user->display_name ) ),
				esc_html( $user->display_name )
			);

			/**
			 * Filters the link to theme's Author Page.
			 *
			 * @since 3.4
			 *
			 * @param string The link to theme's Author Page.
			 */
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

		/*
		// Declare access to globals.
		global $post;

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return false;
		}

		// Bail if no Post.
		if ( ! ( $post instanceof WP_Post ) ) {
			return false;
		}

		// Is this multisite?
		if ( is_multisite() && is_main_site() && $core->bp->is_buddypress_special_page() ) {
			// Ignore Activity.
			return false;
		}
		*/

		// --<
		return true;

	}

endif;



if ( ! function_exists( 'commentpress_is_commentable' ) ) :

	/**
	 * Is a Entry commentable?
	 *
	 * @since 3.3
	 *
	 * @return bool $is_commentable True if Entry can have Comments, false otherwise.
	 */
	function commentpress_is_commentable() {

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return false;
		}

		// Return what core reports.
		return $core->parser->is_commentable();

	}

endif;



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



if ( ! function_exists( 'commentpress_add_tinymce_nextpage_button' ) ) :

	/**
	 * Moves the "Next Page" button in the TinyMCE editor.
	 *
	 * @since 3.5
	 *
	 * @param array $buttons The default TinyMCE buttons as set by WordPress.
	 * @return array $buttons The buttons with More removed.
	 */
	function commentpress_add_tinymce_nextpage_button( $buttons ) {

		// Only on back-end.
		if ( ! is_admin() ) {
			return $buttons;
		}

		// Try and place "Next Page" after "More" button.
		$pos = array_search( 'wp_more', $buttons, true );

		// Is it there?
		if ( $pos !== false ) {

			// Get array up to that point.
			$tmp_buttons = array_slice( $buttons, 0, $pos + 1 );

			// Add "Next Page" button.
			$tmp_buttons[] = 'wp_page';

			// Recombine.
			$buttons = array_merge( $tmp_buttons, array_slice( $buttons, $pos + 1 ) );

		}

		// --<
		return $buttons;

	}

endif;

// Add callback for the above.
add_filter( 'mce_buttons', 'commentpress_add_tinymce_nextpage_button' );



if ( ! function_exists( 'commentpress_add_commentblock_button' ) ) :

	/**
	 * Adds our custom TinyMCE button.
	 *
	 * Callback is located here because it's only relevant in CommentPress themes.
	 *
	 * @since 3.3
	 */
	function commentpress_add_commentblock_button() {

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return;
		}

		// Add the TinyMCE button.
		$core->editor_content->button_add();

	}

endif;

// Add callback for the above.
add_action( 'init', 'commentpress_add_commentblock_button' );



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
		require COMMENTPRESS_PLUGIN_PATH . 'includes/core/assets/widgets/widget-license.php';

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



if ( ! function_exists( 'commentpress_post_title_visibility' ) ) :

	/**
	 * Echoes a "style" attribute to show or hide the Page/Post title.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 */
	function commentpress_post_title_visibility( $post_id ) {

		// Override if we've elected to show the title.
		$visibility_attr = ' style="display: none;"';
		if ( commentpress_get_post_title_visibility( $post_id ) ) {
			$visibility_attr = '';
		}

		// Write to screen.
		echo $visibility_attr;

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

		// Show by default.
		$hide = 'show';

		// Get core plugin reference.
		$core = commentpress_core();

		// If we have the plugin enabled.
		if ( ! empty( $core ) ) {

			// Get global hide.
			$hide = $core->db->option_get( 'cp_title_visibility' );

			// Set key.
			$key = '_cp_title_visibility';

			// Get value if the custom field already has one.
			if ( get_post_meta( $post_id, $key, true ) != '' ) {
				$hide = get_post_meta( $post_id, $key, true );
			}

		}

		// --<
		return ( $hide == 'show' ) ? true : false;

	}

endif;



if ( ! function_exists( 'commentpress_post_meta_visibility' ) ) :

	/**
	 * Echoes a "style" attribute to show or hide the Page/Post meta.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 */
	function commentpress_post_meta_visibility( $post_id ) {

		// Override if we've elected to show the meta.
		$visibility_attr = ' style="display: none;"';
		if ( commentpress_get_post_meta_visibility( $post_id ) ) {
			$visibility_attr = '';
		}

		// Write to screen.
		echo $visibility_attr;

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

		// Hide by default.
		$hide_meta = 'hide';

		// Get core plugin reference.
		$core = commentpress_core();

		// If we have the plugin enabled.
		if ( ! empty( $core ) ) {

			// Get global hide_meta.
			$hide_meta = $core->db->option_get( 'cp_page_meta_visibility' );

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
