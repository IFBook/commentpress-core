<?php /*
================================================================================
CommentPress Modern Theme Functions
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

--------------------------------------------------------------------------------
*/



// always include our common theme functions file
require_once( COMMENTPRESS_PLUGIN_PATH . 'commentpress-core/assets/includes/theme/theme-functions.php' );



/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * This seems to be a WordPress requirement - though rather dumb in the context
 * of our theme, which has a percentage-based default width.
 *
 * I have arbitrarily set it to the default content-width when viewing on a
 * 1280px-wide screen.
 */
if ( !isset( $content_width ) ) { $content_width = 1024; }



if ( ! function_exists( 'commentpress_setup' ) ):
/**
 * Set up CommentPress Modern theme.
 *
 * @since 3.0
 *
 * @return void
 */
function commentpress_setup() {

	// add title support: wp_title() is deprecated as of WP 4.4
	add_theme_support( 'title-tag' );

	// add_custom_background function is deprecated in WP 3.4+
	global $wp_version;
	if ( version_compare( $wp_version, '3.4', '>=' ) ) {

		// allow custom backgrounds
		add_theme_support( 'custom-background', array(
			'default-color'          => 'ccc',
			'default-image'          => '',
			'wp-head-callback'       => 'commentpress_background',
			'admin-head-callback'    => '',
			'admin-preview-callback' => ''
		) );

		// allow custom header
		add_theme_support( 'custom-header', array(
			'default-text-color' => 'eeeeee',
			'width' => apply_filters( 'cp_header_image_width', 940 ),
			'height' => apply_filters( 'cp_header_image_height', 67 ),
			'wp-head-callback' => 'commentpress_header',
			'admin-head-callback' => 'commentpress_admin_header'
		) );

	} else {

		// retain old declarations for earlier versions
		add_custom_background();

		// header text colour
		define( 'HEADER_TEXTCOLOR', 'eeeeee' );

		// set height and width
		define( 'HEADER_IMAGE_WIDTH', apply_filters( 'cp_header_image_width', 940 ) );
		define( 'HEADER_IMAGE_HEIGHT', apply_filters( 'cp_header_image_height', 67 ) );

		// allow custom header images
		add_custom_image_header( 'commentpress_header', 'commentpress_admin_header' );

	}

	/**
	 * Default custom headers packaged with the theme (see Twenty Eleven)
	 * A nice side-effect of supplying a default header image is that it triggers the
	 * "Header Image" option in the Theme Customizer
	 * %s is a placeholder for the theme template directory URI
	 */
	register_default_headers(
		array(
			'caves-green' => array(
				'url' => '%s/assets/images/header/caves-green.jpg',
				'thumbnail_url' => '%s/assets/images/header/caves-green-thumbnail.jpg',
				/* translators: header image description */
				'description' => __( 'Abstract Green', 'commentpress-core' )
			),
			'caves-red' => array(
				'url' => '%s/assets/images/header/caves-red.jpg',
				'thumbnail_url' => '%s/assets/images/header/caves-red-thumbnail.jpg',
				/* translators: header image description */
				'description' => __( 'Abstract Red', 'commentpress-core' )
			),
			'caves-blue' => array(
				'url' => '%s/assets/images/header/caves-blue.jpg',
				'thumbnail_url' => '%s/assets/images/header/caves-blue-thumbnail.jpg',
				/* translators: header image description */
				'description' => __( 'Abstract Blue', 'commentpress-core' )
			),
			'caves-violet' => array(
				'url' => '%s/assets/images/header/caves-violet.jpg',
				'thumbnail_url' => '%s/assets/images/header/caves-violet-thumbnail.jpg',
				/* translators: header image description */
				'description' => __( 'Abstract Violet', 'commentpress-core' )
			)
		)
	);

	// auto feed links
	add_theme_support( 'automatic-feed-links' );

	// style the visual editor with editor-style.css to match the theme style
	add_editor_style();

	// testing the use of wp_nav_menu() - first we need to register it
	register_nav_menu( 'toc', __( 'Table of Contents', 'commentpress-core' ) );



	// if we have the plugin enabled
	global $commentpress_core;
	if ( is_object( $commentpress_core ) ) {

		// get the option
		$featured_images = $commentpress_core->db->option_get( 'cp_featured_images', 'n' );

		// do we have the featured imaegs option enabled?
		if ( $featured_images == 'y' ) {

			// use Featured Images (also known as post thumbnails)
			add_theme_support( 'post-thumbnails' );

			// define a custom image size, cropped to fit
			add_image_size(
				'commentpress-feature',
				apply_filters( 'cp_feature_image_width', 1200 ),
				apply_filters( 'cp_feature_image_height', 600 ),
				true // crop
			);

		}

	}

	// no need for default sidebar in this theme
	//add_filter( 'commentpress_hide_sidebar_option', '__return_true' );

}
endif; // commentpress_setup

// add after theme setup hook
add_action( 'after_setup_theme', 'commentpress_setup' );



if ( ! function_exists( 'commentpress_enqueue_scripts_and_styles' ) ):
/**
 * Add CommentPress Core front-end styles.
 *
 * @since 3.0
 *
 * @return void
 */
function commentpress_enqueue_scripts_and_styles() {

	// check for dev
	$dev = commentpress_minified();

	// -------------------------------------------------------------------------
	// Stylesheets
	// -------------------------------------------------------------------------

	// register screen styles
	wp_register_style(
		'cp_screen_css', // unique id
		get_template_directory_uri() . '/assets/css/screen' . $dev . '.css', // src
		array(), // dependencies
		COMMENTPRESS_VERSION, // version
		'all' // media
	);

	// -------------------------------------------------------------------------
	// Overrides for styles - for child themes, dequeue these and add you own
	// -------------------------------------------------------------------------

	// add Google Webfont "Lato"
	wp_enqueue_style(
		'cp_webfont_lato_css',
		set_url_scheme( 'http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic' ),
		array( 'cp_screen_css' ),
		COMMENTPRESS_VERSION, // version
		'all' // media
	);

	// add colours css
	wp_enqueue_style(
		'cp_colours_css',
		get_template_directory_uri() . '/assets/css/colours-01' . $dev . '.css',
		array( 'cp_webfont_lato_css' ),
		COMMENTPRESS_VERSION, // version
		'all' // media
	);

	// use dashicons
	wp_enqueue_style( 'dashicons' );

	// -------------------------------------------------------------------------
	// Javascripts
	// -------------------------------------------------------------------------

	// enqueue common js
	wp_enqueue_script(
		'cp_common_js',
		get_template_directory_uri() . '/assets/js/screen' . $dev . '.js',
		array( 'jquery_commentpress' ), // deps
		COMMENTPRESS_VERSION // version
	);

	// access plugin
	global $commentpress_core;

	// if we have the plugin enabled
	if ( is_object( $commentpress_core ) ) {

		// test for BuddyPress special page
		if ( $commentpress_core->is_buddypress() AND $commentpress_core->is_buddypress_special_page() ) {

			// skip custom addComment

		} else {

			// enqueue form js
			wp_enqueue_script(
				'cp_form',
				plugins_url( 'commentpress-core/assets/js/jquery.commentform' . $dev . '.js', COMMENTPRESS_PLUGIN_FILE ),
				array( 'cp_common_js' ), // deps
				COMMENTPRESS_VERSION // version
			);

		}

		// test for CommentPress Core special page
		if ( $commentpress_core->db->is_special_page() ) {

			// enqueue accordion-like js
			wp_enqueue_script(
				'cp_special',
				get_template_directory_uri() . '/assets/js/cp_js_all_comments.js',
				array( 'cp_form' ), // deps
				COMMENTPRESS_VERSION // version
			);

		}

	}

}
endif; // commentpress_enqueue_scripts_and_styles

// add a filter for the above, very late so it (hopefully) is last in the queue
add_action( 'wp_enqueue_scripts', 'commentpress_enqueue_scripts_and_styles', 995 );



if ( ! function_exists( 'commentpress_enqueue_print_styles' ) ):
/**
 * Add CommentPress Core print stylesheet.
 *
 * @since 3.0
 *
 * @return void
 */
function commentpress_enqueue_print_styles() {

	// check for dev
	$dev = commentpress_minified();

	// add print css
	wp_enqueue_style(
		'cp_print_css',
		get_template_directory_uri() . '/assets/css/print' . $dev . '.css',
		array( 'cp_screen_css' ),
		COMMENTPRESS_VERSION, // version
		'print'
	);

}
endif; // commentpress_enqueue_print_styles

// add a filter for the above, very late so it (hopefully) is last in the queue
add_action( 'wp_enqueue_scripts', 'commentpress_enqueue_print_styles', 999 );



if ( ! function_exists( 'commentpress_buddypress_support' ) ):
/**
 * Enable support for BuddyPress.
 *
 * @since 3.3
 *
 * @return void
 */
function commentpress_buddypress_support() {

	// include bp-overrides when BuddyPress is active
	add_action( 'wp_enqueue_scripts', 'commentpress_bp_enqueue_styles', 996 );

	// add filter for activity class
	add_filter( 'bp_get_activity_css_class', 'commentpress_bp_activity_css_class' );

	// add filter for blogs class
	add_filter( 'bp_get_blog_class', 'commentpress_bp_blog_css_class' );

	// add filter for groups class
	add_filter( 'bp_get_group_class', 'commentpress_bp_group_css_class' );

}
endif; // commentpress_buddypress_support

// add an action for the above (BuddyPress hooks this to after_setup_theme with priority 100)
add_action( 'bp_after_setup_theme', 'commentpress_buddypress_support' );



if ( ! function_exists( 'commentpress_bp_enqueue_styles' ) ):
/**
 * Add BuddyPress front-end styles.
 *
 * @since 3.3
 *
 * @return void
 */
function commentpress_bp_enqueue_styles() {

	// kick out on admin
	if ( is_admin() ) return;

	// check for dev
	$dev = commentpress_minified();

	// add our own BuddyPress css
	wp_enqueue_style(
		'cp_buddypress_css',
		get_template_directory_uri() . '/assets/css/bp-overrides' . $dev . '.css',
		array( 'cp_screen_css' ),
		COMMENTPRESS_VERSION, // version
		'all' // media
	);

}
endif; // commentpress_bp_enqueue_styles



if ( ! function_exists( 'commentpress_enqueue_wp_fee_js' ) ):
/**
 * Add CommentPress Modern WP FEE Javascript.
 *
 * @since 3.7
 *
 * @return void
 */
function commentpress_enqueue_wp_fee_js() {

	// check for dev
	$dev = commentpress_minified();

	// enqueue support for WP FEE
	wp_enqueue_script(
		'cp_wp_fee_js',
		get_template_directory_uri() . '/assets/js/wp_fee' . $dev . '.js',
		array( 'cp_common_js' ), // deps
		COMMENTPRESS_VERSION // version
	);

}
endif; // commentpress_enqueue_wp_fee_js

// add an action to include WP FEE script if detected
add_action( 'commentpress_editor_include_javascript', 'commentpress_enqueue_wp_fee_js' );



if ( ! function_exists( 'commentpress_background' ) ):
/**
 * Custom background colour.
 *
 * @since 3.0
 *
 * @see _custom_background_cb()
 *
 * @return void
 */
function commentpress_background() {

	// $color is the saved custom color.
	// A default has to be specified in style.css. It will not be printed here.
	$color = get_theme_mod( 'background_color' );

	// bail if we don't have one
	if ( ! $color ) return;

	$style = $color ? "background-color: #$color;" : '';

	echo '
<style type="text/css" id="custom-background-css">

	html,
	body.custom-background,
	#toc_sidebar .sidebar_minimiser ul#toc_list,
	.sidebar_contents_wrapper,
	#footer_inner
	{
		' . trim( $style ) . '
	}

</style>
	';

}
endif; // commentpress_background



if ( ! function_exists( 'commentpress_header' ) ):
/**
 * Custom header.
 *
 * @since 3.0
 *
 * @return void
 */
function commentpress_header() {

	// access plugin
	global $commentpress_core;

	// init with same colour as theme stylesheets and default in class_commentpress_db.php
	$bg_colour = '2c2622';

	// override if we have the plugin enabled
	if ( is_object( $commentpress_core ) ) {
		$bg_colour = $commentpress_core->db->option_get_header_bg();
	}

	// allow overrides
	$bg_colour = apply_filters( 'cp_default_header_bgcolor', $bg_colour );

	// init background-image
	$bg_image = '';

	// get header image
	$header_image = get_header_image();

	// do we have a background-image?
	if ( $header_image ) {
		$bg_image = 'background-image: url("' . $header_image . '");';
	}

	// get custom text colour
	// note: this does NOT retrieve the default if not manually set in the Theme Customizer in WP3.4
	$text_color = get_header_textcolor();

	// WP3.4 seems to behave differently.
	global $wp_version;
	if ( version_compare( $wp_version, '3.4', '>=' ) ) {

		// if blank, we're hiding the title
		if ( $text_color == 'blank' ) {
			$css = 'text-indent: -9999px;';
		} else {

			// if empty, we need to use default
			if ( $text_color == '' ) {
				$css = 'color: #' . HEADER_TEXTCOLOR . ';';
			} else {

				// use the custom one. I know this amounts to the same thing.
				$css = 'color: #' . $text_color . ';';
			}

		}

	} else {

		// use previous logic
		if ( $text_color == 'blank' OR $text_color == '' ) {
			$css = 'text-indent: -9999px;';
		} else {
			$css = 'color: #' . $text_color . ';';
		}

	}

	// build inline styles
	echo '
<style type="text/css">

#header
{
	background-color: #' . $bg_colour . ';
	' . $bg_image . '
	-webkit-background-size: cover;
	-moz-background-size: cover;
	-o-background-size: cover;
	background-size: cover;
	background-repeat: no-repeat;
	background-position: 50%;
}

#title h1,
#title h1 a
{
	' . $css . '
}

#header #tagline
{
	' . $css . '
}

</style>
	';

}
endif; // commentpress_header



if ( ! function_exists( 'commentpress_page_navigation' ) ):
/**
 * Builds a list of previous and next pages, optionally with comments.
 *
 * @since 3.0
 *
 * @param bool $with_comments True returns the next page with comments
 * @return str $nav_list The unordered list of navigation links
 */
function commentpress_page_navigation( $with_comments = false ) {

	// declare access to globals
	global $commentpress_core;

	// bail if the plugin is not active
	if ( ! is_object( $commentpress_core ) ) return;

	// init formatting
	$before_next = '<li class="alignright">';
	$after_next = ' </li>';
	$before_prev = '<li class="alignleft">';
	$after_prev = '</li>';

	// init
	$next_page_html = '';

	// get next page
	$next_page = $commentpress_core->nav->get_next_page( $with_comments );

	// did we get a next page?
	if ( is_object( $next_page ) ) {

		// init title
		$img = '';
		$title = __( 'Next page', 'commentpress-core' ); //htmlentities( $next_page->post_title );

		// if we wanted pages with comments
		if ( $with_comments ) {

			// set title
			$title = __( 'Next page with comments', 'commentpress-core' );
			$img = '<img src="' . get_template_directory_uri() . '/assets/images/next.png" />';

		}

		// define list item
		$next_page_html = $before_next .
						  $img .
						  '<a href="' . get_permalink( $next_page->ID ) . '" class="next_page" title="' . esc_attr( $title ) . '">' . $title . '</a>' .
						  $after_next;

	}

	// init
	$prev_page_html = '';

	// get next page
	$prev_page = $commentpress_core->nav->get_previous_page( $with_comments );

	// did we get a next page?
	if ( is_object( $prev_page ) ) {

		// init title
		$img = '';
		$title = __( 'Previous page', 'commentpress-core' ); //htmlentities( $prev_page->post_title );

		// if we wanted pages with comments
		if ( $with_comments ) {

			// set title
			$title = __( 'Previous page with comments', 'commentpress-core' );
			$img = '<img src="' . get_template_directory_uri() . '/assets/images/prev.png" />';

		}

		// define list item
		$prev_page_html = $before_prev .
						  $img .
						  '<a href="' . get_permalink( $prev_page->ID ) . '" class="previous_page" title="' . esc_attr( $title ) . '">' . $title . '</a>' .
						  $after_prev;

	}

	// init return
	$nav_list = '';

	// did we get either?
	if ( $next_page_html != '' OR $prev_page_html != '' ) {

		// construct nav list items
		$nav_list = $prev_page_html . "\n" . $next_page_html . "\n";

	}

	// --<
	return $nav_list;

}
endif; // commentpress_page_navigation



if ( ! function_exists( 'commentpress_get_all_comments_content' ) ):
/**
 * All-comments page display function.
 *
 * @since 3.0
 *
 * @param str $page_or_post Retrieve either 'page' or 'post' comments
 * @return str $html The comments
 */
function commentpress_get_all_comments_content( $page_or_post = 'page' ) {

	// declare access to globals
	global $commentpress_core, $cp_comment_output;

	// init output
	$html = '';

	// get all approved comments
	$all_comments = get_comments( array(
		'status' => 'approve',
		'orderby' => 'comment_post_ID,comment_date',
		'order' => 'ASC',
		'post_type' => $page_or_post,
	) );

	// kick out if none
	if ( count( $all_comments ) == 0 ) return $html;

	// build list of posts to which they are attached
	$posts_with = array();
	$post_comment_counts = array();
	foreach( $all_comments AS $comment ) {

		// add to posts with comments array
		if ( !in_array( $comment->comment_post_ID, $posts_with ) ) {
			$posts_with[] = $comment->comment_post_ID;
		}

		// increment counter
		if ( !isset( $post_comment_counts[$comment->comment_post_ID] ) ) {
			$post_comment_counts[$comment->comment_post_ID] = 1;
		} else {
			$post_comment_counts[$comment->comment_post_ID]++;
		}

	}

	// kick out if none
	if ( count( $posts_with ) == 0 ) return $html;

	// get those posts
	$posts = get_posts( array(
		'orderby' => 'comment_count',
		'order' => 'DESC',
		'post_type' => $page_or_post,
		'include' => $posts_with,
	) );

	// kick out if none
	if ( count( $posts ) == 0 ) return $html;

	// open ul
	$html .= '<ul class="all_comments_listing">' . "\n\n";

	foreach( $posts AS $post ) {

		// open li
		$html .= '<li class="page_li"><!-- page li -->' . "\n\n";

		// define comment count
		$comment_count_text = sprintf( _n(

			// singular
			'<span class="cp_comment_count">%d</span> comment',

			// plural
			'<span class="cp_comment_count">%d</span> comments',

			// number
			$post_comment_counts[$post->ID],

			// domain
			'commentpress-core'

		// substitution
		), $post_comment_counts[$post->ID] );

		// show it
		$html .= '<h4>' . esc_html( $post->post_title ) . ' <span>(' . $comment_count_text . ')</span></h4>' . "\n\n";

		// open comments div
		$html .= '<div class="item_body">' . "\n\n";

		// open ul
		$html .= '<ul class="item_ul">' . "\n\n";

		// open li
		$html .= '<li class="item_li"><!-- item li -->' . "\n\n";

		// check for password-protected
		if ( post_password_required( $post->ID ) ) {

			// construct notice
			$comment_body = '<div class="comment-content">' . __( 'Password protected', 'commentpress-core' ) . '</div>' . "\n";

			// add notice
			$html .= '<div class="comment_wrapper">' . "\n" . $comment_body . '</div>' . "\n\n";

		} else {

			foreach( $all_comments AS $comment ) {

				if ( $comment->comment_post_ID == $post->ID ) {

					// show the comment
					$html .= commentpress_format_comment( $comment );

					/*
					// get comment children
					$children = commentpress_get_children( $comment, $page_or_post );

					// do we have any?
					if( count( $children ) > 0 ) {

						// recurse
						commentpress_get_comments( $children, $page_or_post );

						// show them
						$html .= $cp_comment_output;

						// clear global comment output
						$cp_comment_output = '';

					}
					*/

				}

			}

		}

		// close li
		$html .= '</li><!-- /item li -->' . "\n\n";

		// close ul
		$html .= '</ul>' . "\n\n";

		// close item div
		$html .= '</div><!-- /item_body -->' . "\n\n";

		// close li
		$html .= '</li><!-- /page li -->' . "\n\n\n\n";

	}

	// close ul
	$html .= '</ul><!-- /all_comments_listing -->' . "\n\n";

	// --<
	return $html;

}
endif; // commentpress_get_all_comments_content



if ( ! function_exists( 'commentpress_get_all_comments_page_content' ) ):
/**
 * All-comments page display function.
 *
 * @since 3.0
 *
 * @return str $page_content The page content
 */
function commentpress_get_all_comments_page_content() {

	// allow oEmbed in comments
	global $wp_embed;
	if ( $wp_embed instanceof WP_Embed ) {
		add_filter( 'comment_text', array( $wp_embed, 'autoembed' ), 1 );
	}

	// declare access to globals
	global $commentpress_core;

	// set default
	$page_title = apply_filters(
		'cp_page_all_comments_title',
		__( 'All Comments', 'commentpress-core' )
	);

	// set title
	$page_content = '<h2 class="post_title">' . $page_title . '</h2>' . "\n\n";

	// get page or post
	$page_or_post = $commentpress_core->get_list_option();

	// set default
	$blog_title = apply_filters(
		'cp_page_all_comments_blog_title',
		__( 'Comments on the Blog', 'commentpress-core' )
	);

	// set default
	$book_title = apply_filters(
		'cp_page_all_comments_book_title',
		__( 'Comments on the Pages', 'commentpress-core' )
	);

	// get title
	$title = ( $page_or_post == 'page' ) ? $book_title : $blog_title;

	// get data
	$data = commentpress_get_all_comments_content( $page_or_post );

	// did we get any?
	if ( $data != '' ) {

		// set title
		$page_content .= '<h3 class="comments_hl">' . $title . '</h3>' . "\n\n";

		// set data
		$page_content .= $data . "\n\n";

	}

	// get data for other page type
	$other_type = ( $page_or_post == 'page' ) ? 'post': 'page';

	// get title
	$title = ( $page_or_post == 'page' ) ? $blog_title : $book_title;

	// get data
	$data = commentpress_get_all_comments_content( $other_type );

	// did we get any?
	if ( $data != '' ) {

		// set title
		$page_content .= '<h3 class="comments_hl">' . $title . '</h3>' . "\n\n";

		// set data
		$page_content .= $data . "\n\n";

	}

	// --<
	return $page_content;

}
endif; // commentpress_get_all_comments_page_content



if ( ! function_exists( 'commentpress_add_loginout_id' ) ):
/**
 * Utility to add button css id to login links.
 *
 * @since 3.0
 *
 * @param str $link The existing link
 * @return str $link The modified link
 */
function commentpress_add_loginout_id( $link ) {

	// site admin link?
	if ( false !== strstr( $link, admin_url() ) ) {

		// site admin
		$id = 'btn_site_admin';

	} else {

		// if logged in
		if ( is_user_logged_in() ) {

			// logout
			$id = 'btn_logout';

		} else {

			// login
			$id = 'btn_login';

		}

	}

	// add css
	$link = str_replace( '<a ', '<a id="' . $id . '" ', $link );

	// --<
	return $link;

}
endif; // commentpress_add_loginout_id

// add filters for WordPress admin links
add_filter( 'loginout', 'commentpress_add_link_css' );
add_filter( 'loginout', 'commentpress_add_loginout_id' );
add_filter( 'register', 'commentpress_add_loginout_id' );



if ( ! function_exists( 'commentpress_convert_link_to_button' ) ):
/**
 * Utility to add button class to BuddyPress 1.9 notification links.
 *
 * @since 3.5
 *
 * @param str $link The existing link
 * @return str $link The modified link
 */
function commentpress_convert_link_to_button( $link ) {

	// add css
	$link = str_replace( 'class="mark-unread', 'class="button mark-unread', $link );
	$link = str_replace( 'class="mark-read', 'class="button mark-read', $link );
	$link = str_replace( 'class="delete', 'class="button delete', $link );

	// --<
	return $link;

}
endif; // commentpress_convert_link_to_button

// add filters for the above
add_filter( 'bp_get_the_notification_mark_unread_link', 'commentpress_convert_link_to_button' );
add_filter( 'bp_get_the_notification_mark_read_link', 'commentpress_convert_link_to_button' );
add_filter( 'bp_get_the_notification_delete_link', 'commentpress_convert_link_to_button' );



if ( ! function_exists( 'commentpress_get_feature_image' ) ):
/**
 * Show feature image.
 *
 * @since 3.5
 *
 * @return void
 */
function commentpress_get_feature_image() {

	// access post
	global $post;

	// do we have a featured image?
	if ( commentpress_has_feature_image() ) {

		// show it
		echo '<div class="cp_feature_image">';

		/**
		 * Filter the feature image.
		 *
		 * @since 3.9
		 *
		 * @param str The HTML for showing the image
		 * @param WP_Post The current WordPress post object
		 */
		echo apply_filters(
			'commentpress_get_feature_image',
			get_the_post_thumbnail( get_the_ID(), 'commentpress-feature' ),
			$post
		);

		?>
		<div class="cp_featured_title">
			<div class="cp_featured_title_inner">

				<?php

				// when pulling post in via AJAX, is_page() isn't available, so
				// inspect the post type as well
				if ( is_page() OR $post->post_type == 'page' ) {

				?>

					<?php

					// default to hidden
					$cp_title_visibility = ' style="display: none;"';

					// override if we've elected to show the title
					if ( commentpress_get_post_title_visibility( get_the_ID() ) ) {
						$cp_title_visibility = '';
					}

					?>
					<h2 class="post_title page_title"<?php echo $cp_title_visibility; ?>><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>

					<?php

					// default to hidden
					$cp_meta_visibility = ' style="display: none;"';

					// override if we've elected to show the meta
					if ( commentpress_get_post_meta_visibility( get_the_ID() ) ) {
						$cp_meta_visibility = '';
					}

					?>
					<div class="search_meta page_search_meta"<?php echo $cp_meta_visibility; ?>>
						<?php commentpress_echo_post_meta(); ?>
					</div>

				<?php } else { ?>

					<h2 class="post_title"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>

					<div class="search_meta">
						<?php commentpress_echo_post_meta(); ?>
					</div>

				<?php } ?>

			</div>
		</div>
		<?php

		echo '</div>';

	}

}
endif; // commentpress_get_feature_image



/**
 * Utility to test for feature image, because has_post_thumbnail() fails sometimes.
 *
 * @see http://codex.wordpress.org/Function_Reference/has_post_thumbnail
 *
 * @since 3.5
 *
 * @return bool True if post has thumbnail, false otherwise
 */
function commentpress_has_feature_image() {

	// init return
	$has_feature_image = false;

	// replacement check
	if ( '' != get_the_post_thumbnail() ) {
		$has_feature_image = true;
	}

	/**
	 * Allow this test to be overridden.
	 *
	 * @since 3.9
	 *
	 * @param bool $has_feature_image True if the post has a feature image, false otherwise
	 */
	return apply_filters( 'commentpress_has_feature_image', $has_feature_image );

}



/**
 * Register widget areas for this theme.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 *
 * @since 3.8.10
 */
function commentpress_register_widget_areas() {

	// define an area where a widget may be placed
	register_sidebar( array(
		'name' => __( 'CommentPress Footer', 'commentpress-core' ),
		'id' => 'cp-license-8',
		'description' => __( 'An optional widget area in the footer of a CommentPress theme', 'commentpress-core' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => "</div>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );

	// define an area where a widget may be placed
	register_sidebar( array(
		'name' => __( 'Navigation Top', 'commentpress-core' ),
		'id' => 'cp-nav-top',
		'description' => __( 'An optional widget area at the top of the Navigation Column', 'commentpress-core' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => "</div></div></div>",
		'before_title' => '<h3 class="widget-title activity_heading">',
		'after_title' => '</h3><div class="paragraph_wrapper"><div class="widget_wrapper clearfix">',
	) );

	// define an area where a widget may be placed
	register_sidebar( array(
		'name' => __( 'Navigation Bottom', 'commentpress-core' ),
		'id' => 'cp-nav-bottom',
		'description' => __( 'An optional widget area at the bottom of the Navigation Column', 'commentpress-core' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => "</div></div></div>",
		'before_title' => '<h3 class="widget-title activity_heading">',
		'after_title' => '</h3><div class="paragraph_wrapper"><div class="widget_wrapper clearfix">',
	) );

	// define an area where a widget may be placed
	register_sidebar( array(
		'name' => __( 'Activity Top', 'commentpress-core' ),
		'id' => 'cp-activity-top',
		'description' => __( 'An optional widget area at the top of the Activity Column', 'commentpress-core' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => "</div></div></div>",
		'before_title' => '<h3 class="widget-title activity_heading">',
		'after_title' => '</h3><div class="paragraph_wrapper"><div class="widget_wrapper clearfix">',
	) );

	// define an area where a widget may be placed
	register_sidebar( array(
		'name' => __( 'Activity Bottom', 'commentpress-core' ),
		'id' => 'cp-activity-bottom',
		'description' => __( 'An optional widget area at the bottom of the Activity Column', 'commentpress-core' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => "</div></div></div>",
		'before_title' => '<h3 class="widget-title activity_heading">',
		'after_title' => '</h3><div class="paragraph_wrapper"><div class="widget_wrapper clearfix">',
	) );

}

add_action( 'widgets_init', 'commentpress_register_widget_areas' );



