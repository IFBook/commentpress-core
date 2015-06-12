<?php /*
================================================================================
CommentPress Default Theme Functions
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
 * This seems to be a WordPress requirement - though rather dumb in the
 * context of our theme, which has a percentage-based default width.
 * I have arbitrarily set it to the default content-width when viewing
 * on a 1280px-wide screen.
 */
if ( !isset( $content_width ) ) { $content_width = 588; }



if ( ! function_exists( 'commentpress_setup' ) ):
/**
 * Set up CommentPress Default theme
 *
 * @return void
 */
function commentpress_setup() {

	// add_custom_background function is deprecated in WP 3.4+
	global $wp_version;
	if ( version_compare( $wp_version, '3.4', '>=' ) ) {

		// -------------------------
		// TO DO: test 3.4 features
		// -------------------------

		// allow custom backgrounds
		add_theme_support( 'custom-background' );

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

	// Default custom headers packaged with the theme (see Twenty Eleven)
	// A nice side-effect of supplying a default header image is that it triggers the
	// "Header Image" option in the Theme Customizer
	// %s is a placeholder for the theme template directory URI
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


	// ignore BP 1.7 auto-compatibility - see commentpress_enqueue_theme_styles()
	//add_theme_support( 'buddypress' );

}
endif; // commentpress_setup

// add after theme setup hook
add_action( 'after_setup_theme', 'commentpress_setup' );



if ( ! function_exists( 'commentpress_enqueue_theme_styles' ) ):
/**
 * Add BuddyPress front-end styles
 *
 * @return void
 */
function commentpress_enqueue_theme_styles() {

	// kick out on admin
	if ( is_admin() ) { return; }

	// init
	$dev = '.min';

	// check for dev
	if ( defined( 'SCRIPT_DEBUG' ) AND SCRIPT_DEBUG === true ) {
		$dev = '';
	}

	// add our own BuddyPress css
	wp_enqueue_style(

		'cp_buddypress_css',
		get_template_directory_uri() . '/assets/css/bp-overrides' . $dev . '.css',
		array( 'cp_layout_css' ),
		COMMENTPRESS_VERSION, // version
		'all' // media

	);

	/*
	----------------------------------------------------------------------------
	Some notes on BuddyPress 1.7 theme compatibility
	----------------------------------------------------------------------------

	(a) see commentpress_enqueue_scripts_and_styles() for dequeuing bp-legacy-css

	(b) CommentPress Core and themes based on it require the inclusion and setup
	    of the BuddyPress Template Pack plugin, which should have only Javascript
	    enabled for the main BuddyPress site

	----------------------------------------------------------------------------
	*/

	/*
	// enqueue a copy of the legacy buddypress js
	wp_enqueue_script(

		'cp_buddypress_js',
		get_template_directory_uri() . '/assets/js/buddypress.js',
		array( 'cp_common_js' )

	);
	*/

}
endif; // commentpress_enqueue_theme_styles

if ( ! function_exists( 'commentpress_enqueue_bp_theme_styles' ) ):
/**
 * Enqueue BuddyPress front-end styles
 *
 * @return void
 */
function commentpress_enqueue_bp_theme_styles() {

	// add an action to include bp-overrides when buddypress is active
	add_action( 'wp_enqueue_scripts', 'commentpress_enqueue_theme_styles', 101 );

}
endif; // commentpress_enqueue_bp_theme_styles

// add an action for the above
add_action( 'bp_setup_globals', 'commentpress_enqueue_theme_styles' );



if ( ! function_exists( 'commentpress_enqueue_scripts_and_styles' ) ):
/**
 * Add front-end print styles
 *
 * @return void
 */
function commentpress_enqueue_scripts_and_styles() {

	// -------------------------------------------------------------------------
	// Stylesheets
	// -------------------------------------------------------------------------

	// register reset
	wp_register_style(
		'cp_reset_css', // unique id
		get_template_directory_uri() . '/assets/css/reset.css', // src
		array(), // dependencies
		COMMENTPRESS_VERSION, // version
		'all' // media
	);

	// init
	$dev = '.min';

	// check for dev
	if ( defined( 'SCRIPT_DEBUG' ) AND SCRIPT_DEBUG === true ) {
		$dev = '';
	}

	// add typography css
	wp_enqueue_style(
		'cp_typography_css',
		get_template_directory_uri() . '/assets/css/typography' . $dev . '.css',
		array( 'cp_reset_css' ),
		COMMENTPRESS_VERSION, // version
		'all' // media
	);

	// add layout css
	wp_enqueue_style(
		'cp_layout_css',
		get_template_directory_uri() . '/assets/css/layout' . $dev . '.css',
		array( 'cp_typography_css' ),
		COMMENTPRESS_VERSION, // version
		'all' // media
	);

	// -------------------------------------------------------------------------
	// Overrides for styles - for child themes, dequeue these and add you own
	// -------------------------------------------------------------------------

	// add Google Webfont "Lato"
	wp_enqueue_style(
		'cp_webfont_css',
		'http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic',
		array( 'cp_layout_css' ),
		null, // no version, thanks
		null // no media, thanks
	);

	// add colours css
	wp_enqueue_style(
		'cp_colours_css',
		get_template_directory_uri() . '/assets/css/colours-01' . $dev . '.css',
		array( 'cp_webfont_css' ),
		COMMENTPRESS_VERSION, // version
		'all' // media
	);

	// use dashicons
	wp_enqueue_style( 'dashicons' );

	// -------------------------------------------------------------------------
	// Javascripts
	// -------------------------------------------------------------------------

	// access plugin
	global $commentpress_core;

	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {

		// enqueue common js
		wp_enqueue_script(
			'cp_common_js',
			get_template_directory_uri() . '/assets/js/cp_js_common' . $dev . '.js',
			array( 'jquery_commentpress' ),
			COMMENTPRESS_VERSION // version
		);

		// test for buddypress special page
		if ( $commentpress_core->is_buddypress() AND $commentpress_core->is_buddypress_special_page() ) {

			// skip custom addComment

		} else {

			// enqueue form js
			wp_enqueue_script(
				'cp_form',
				plugins_url( 'commentpress-core/assets/js/jquery.commentform' . $dev . '.js', COMMENTPRESS_PLUGIN_FILE ),
				array( 'cp_common_js' ),
				COMMENTPRESS_VERSION // version
			);

		}

		// test for CommentPress Core special page
		if ( $commentpress_core->db->is_special_page() ) {

			// enqueue accordion-like js
			wp_enqueue_script(
				'cp_special',
				get_template_directory_uri() . '/assets/js/cp_js_all_comments.js',
				array( 'cp_form' ),
				COMMENTPRESS_VERSION // version
			);

		}

	}

}
endif; // commentpress_enqueue_scripts_and_styles

// add a filter for the above, very late so it (hopefully) is last in the queue
add_action( 'wp_enqueue_scripts', 'commentpress_enqueue_scripts_and_styles', 100 );



if ( ! function_exists( 'commentpress_enqueue_print_styles' ) ):
/**
 * Add CommentPress print stylesheet
 *
 * @return void
 */
function commentpress_enqueue_print_styles() {

	// init
	$dev = '.min';

	// check for dev
	if ( defined( 'SCRIPT_DEBUG' ) AND SCRIPT_DEBUG === true ) {
		$dev = '';
	}

	// -------------------------------------------------------------------------
	// Print stylesheet included last
	// -------------------------------------------------------------------------

	// add print css
	wp_enqueue_style(

		'cp_print_css',
		get_template_directory_uri() . '/assets/css/print' . $dev . '.css',
		array( 'cp_layout_css' ),
		COMMENTPRESS_VERSION, // version
		'print'

	);

}
endif; // commentpress_enqueue_print_styles

// add a filter for the above, very late so it (hopefully) is last in the queue
add_action( 'wp_enqueue_scripts', 'commentpress_enqueue_print_styles', 101 );



if ( ! function_exists( 'commentpress_header' ) ):
/**
 * Custom header
 *
 * @return void
 */
function commentpress_header() {

	// init (same as bg in layout.css and default in class_commentpress_db.php)
	$bg_colour = '2c2622';

	// access plugin
	global $commentpress_core;

	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {

		// override
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

#book_header
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

#book_header #tagline
{
	' . $css . '
}

</style>
	';

}
endif; // commentpress_header



/*
	// if no custom options for text are set, ignore
	if ( $text_color == HEADER_TEXTCOLOR ) {

		// set flag
		$ignore = true;

	}

	// if blank or empty, we're hiding the title
	if ( $text_color == 'blank' OR $text_color == '' ) {

	}

	// If we get this far, we have custom styles. Let's do this.
	print_r( ( $text_color ? $text_color : 'nowt<br/>' ) );
	print_r( HEADER_TEXTCOLOR ); die();


*/



if ( ! function_exists( 'commentpress_page_navigation' ) ):
/**
 * Builds a list of previous and next pages, optionally with comments
 *
 * @param bool $with_comments True returns the next page with comments
 * @return str $nav_list The unordered list of navigation links
 */
function commentpress_page_navigation( $with_comments = false ) {

	// declare access to globals
	global $commentpress_core;

	// is the plugin active?
	if ( !is_object( $commentpress_core ) ) {

		// --<
		return;

	}

	// init formatting
	$before_next = '<li class="alignright">';
	$after_next = ' </li>';
	$before_prev = '<li class="alignleft">';
	$after_prev = '</li>';

	// init
	$next_page_html = '';

	// get next page
	$next_page = $commentpress_core->nav->get_next_page( $with_comments );

	//var_dump( $next_page );

	// did we get a next page?
	if ( is_object( $next_page ) ) {

		// init title
		$img = '';
		$title = __( 'Next page', 'commentpress-core' ); //htmlentities( $next_page->post_title );

		// if we wanted pages with comments...
		if ( $with_comments ) {

			// set title
			$title = __( 'Next page with comments', 'commentpress-core' );
			$img = '<img src="' . get_template_directory_uri() . '/assets/images/next.png" />';

		}

		// define list item
		$next_page_html = $before_next .
						  $img . '<a href="' . get_permalink( $next_page->ID ) . '" id="next_page" class="css_btn" title="' . esc_attr( $title ) . '">' . $title . '</a>' . $after_next;

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

		// if we wanted pages with comments...
		if ( $with_comments ) {

			// set title
			$title = __( 'Previous page with comments', 'commentpress-core' );
			$img = '<img src="' . get_template_directory_uri() . '/assets/images/prev.png" />';

		}

		// define list item
		$prev_page_html = $before_prev .
						  $img . '<a href="' . get_permalink( $prev_page->ID ) . '" id="previous_page" class="css_btn" title="' . esc_attr( $title ) . '">' . $title . '</a>' . $after_prev;

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
 * All-comments page display function
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
	//print_r( $all_comments ); //die();

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
	/*
	if ( $page_or_post == 'post' ) {
		print_r( $post_comment_counts ); die();
		print_r( $posts_with ); die();
	}
	*/

	// kick out if none
	if ( count( $posts_with ) == 0 ) return $html;

	// get those posts
	$posts = get_posts( array(
		'orderby' => 'comment_count',
		'order' => 'DESC',
		'post_type' => $page_or_post,
		'include' => $posts_with,
	) );
	//print_r( $posts ); die();

	// kick out if none
	if ( count( $posts ) == 0 ) return $html;

	// open ul
	$html .= '<ul class="all_comments_listing">' . "\n\n";

	foreach( $posts AS $_post ) {

		// open li
		$html .= '<li class="page_li"><!-- page li -->' . "\n\n";

		// define comment count
		$comment_count_text = sprintf( _n(

			// singular
			'<span class="cp_comment_count">%d</span> comment',

			// plural
			'<span class="cp_comment_count">%d</span> comments',

			// number
			$post_comment_counts[$_post->ID],

			// domain
			'commentpress-core'

		// substitution
		), $post_comment_counts[$_post->ID] );

		// show it
		$html .= '<h3>' . esc_html( $_post->post_title ) . ' <span>(' . $comment_count_text . ')</span></h3>' . "\n\n";

		// open comments div
		$html .= '<div class="item_body">' . "\n\n";

		// open ul
		$html .= '<ul class="item_ul">' . "\n\n";

		// open li
		$html .= '<li class="item_li"><!-- item li -->' . "\n\n";

		// check for password-protected
		if ( post_password_required( $_post->ID ) ) {

			// construct notice
			$_comment_body = '<div class="comment-content">' . __( 'Password protected', 'commentpress-core' ) . '</div>' . "\n";

			// add notice
			$html .= '<div class="comment_wrapper">' . "\n" . $_comment_body . '</div>' . "\n\n";

		} else {

			foreach( $all_comments AS $comment ) {

				if ( $comment->comment_post_ID == $_post->ID ) {

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
 * All-comments page display function
 *
 * @return str $_page_content The page content
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
	$pagetitle = apply_filters(
		'cp_page_all_comments_title',
		__( 'All Comments', 'commentpress-core' )
	);

	// set title
	$_page_content = '<h2 class="post_title">' . $pagetitle . '</h2>' . "\n\n";

	// get page or post
	$page_or_post = $commentpress_core->get_list_option();

	// set default
	$blogtitle = apply_filters(
		'cp_page_all_comments_blog_title',
		__( 'Comments on the Blog', 'commentpress-core' )
	);

	// set default
	$booktitle = apply_filters(
		'cp_page_all_comments_book_title',
		__( 'Comments on the Pages', 'commentpress-core' )
	);

	// get title
	$title = ( $page_or_post == 'page' ) ? $booktitle : $blogtitle;

	// get data
	$_data = commentpress_get_all_comments_content( $page_or_post );

	// did we get any?
	if ( $_data != '' ) {

		// set title
		$_page_content .= '<p class="comments_hl">' . $title . '</p>' . "\n\n";

		// set data
		$_page_content .= $_data . "\n\n";

	}

	// get data for other page type
	$other_type = ( $page_or_post == 'page' ) ? 'post': 'page';

	// get title
	$title = ( $page_or_post == 'page' ) ? $blogtitle : $booktitle;

	// get data
	$_data = commentpress_get_all_comments_content( $other_type );

	// did we get any?
	if ( $_data != '' ) {

		// set title
		$_page_content .= '<p class="comments_hl">' . $title . '</p>' . "\n\n";

		// set data
		$_page_content .= $_data . "\n\n";

	}

	// --<
	return $_page_content;

}
endif; // commentpress_get_all_comments_page_content



if ( ! function_exists( 'commentpress_add_loginout_id' ) ):
/**
 * Utility to add button css id to login links
 *
 * @param str $link The existing link
 * @return str $link The modified link
 */
function commentpress_add_loginout_id( $link ) {

	// site admin link?
	if ( false !== strstr( $link, admin_url() ) ) {

		// site admin
		$_id = 'btn_site_admin';

	} else {

		// if logged in
		if ( is_user_logged_in() ) {

			// logout
			$_id = 'btn_logout';

		} else {

			// login
			$_id = 'btn_login';

		}

	}

	// add css
	$link = str_replace( '<a ', '<a id="' . $_id . '" class="button" ', $link );

	// --<
	return $link;

}
endif; // commentpress_add_loginout_id

// add filters for WordPress admin links
add_filter( 'loginout', 'commentpress_add_link_css' );
add_filter( 'loginout', 'commentpress_add_loginout_id' );
add_filter( 'register', 'commentpress_add_loginout_id' );



