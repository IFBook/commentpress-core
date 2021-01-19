<?php /*
================================================================================
CommentPress Default Theme Functions
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

--------------------------------------------------------------------------------
*/



// Always include our common theme functions file.
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
if ( ! isset( $content_width ) ) { $content_width = 588; }



if ( ! function_exists( 'commentpress_setup' ) ):
/**
 * Set up CommentPress Default theme.
 *
 * @since 3.0
 */
function commentpress_setup() {

	// Add title support: wp_title() is deprecated as of WP 4.4.
	add_theme_support( 'title-tag' );

	// Add_custom_background function is deprecated in WP 3.4+
	global $wp_version;
	if ( version_compare( $wp_version, '3.4', '>=' ) ) {

		// Allow custom backgrounds.
		add_theme_support( 'custom-background' );

		// Allow custom header.
		add_theme_support( 'custom-header', [
			'default-text-color' => 'eeeeee',
			'width' => apply_filters( 'cp_header_image_width', 940 ),
			'height' => apply_filters( 'cp_header_image_height', 67 ),
			'wp-head-callback' => 'commentpress_header',
			'admin-head-callback' => 'commentpress_admin_header',
		] );

	} else {

		// Retain old declarations for earlier versions.
		add_custom_background();

		// Header text colour.
		define( 'HEADER_TEXTCOLOR', 'eeeeee' );

		// Set height and width.
		define( 'HEADER_IMAGE_WIDTH', apply_filters( 'cp_header_image_width', 940 ) );
		define( 'HEADER_IMAGE_HEIGHT', apply_filters( 'cp_header_image_height', 67 ) );

		// Allow custom header images.
		add_custom_image_header( 'commentpress_header', 'commentpress_admin_header' );

	}

	/*
	 * Default custom headers packaged with the theme (see Twenty Eleven)
	 *
	 * A nice side-effect of supplying a default header image is that it triggers
	 * the "Header Image" option in the Theme Customizer.
	 *
	 * %s is a placeholder for the theme template directory URI.
	 */
	register_default_headers(
		[
			'caves-green' => [
				'url' => '%s/assets/images/header/caves-green.jpg',
				'thumbnail_url' => '%s/assets/images/header/caves-green-thumbnail.jpg',
				/* translators: header image description */
				'description' => __( 'Abstract Green', 'commentpress-core' ),
			],
			'caves-red' => [
				'url' => '%s/assets/images/header/caves-red.jpg',
				'thumbnail_url' => '%s/assets/images/header/caves-red-thumbnail.jpg',
				/* translators: header image description */
				'description' => __( 'Abstract Red', 'commentpress-core' ),
			],
			'caves-blue' => [
				'url' => '%s/assets/images/header/caves-blue.jpg',
				'thumbnail_url' => '%s/assets/images/header/caves-blue-thumbnail.jpg',
				/* translators: header image description */
				'description' => __( 'Abstract Blue', 'commentpress-core' ),
			],
			'caves-violet' => [
				'url' => '%s/assets/images/header/caves-violet.jpg',
				'thumbnail_url' => '%s/assets/images/header/caves-violet-thumbnail.jpg',
				/* translators: header image description */
				'description' => __( 'Abstract Violet', 'commentpress-core' ),
			],
		]
	);

	// Auto feed links.
	add_theme_support( 'automatic-feed-links' );

	// Style the visual editor with editor-style.css to match the theme style.
	add_editor_style();

	// Testing the use of wp_nav_menu() - first we need to register it.
	register_nav_menu( 'toc', __( 'Table of Contents', 'commentpress-core' ) );

}
endif; // End commentpress_setup

// Add after theme setup hook.
add_action( 'after_setup_theme', 'commentpress_setup' );



if ( ! function_exists( 'commentpress_enqueue_scripts_and_styles' ) ):
/**
 * Add CommentPress Core front-end styles.
 *
 * @since 3.0
 */
function commentpress_enqueue_scripts_and_styles() {

	// Check for dev.
	$dev = commentpress_minified();

	// -------------------------------------------------------------------------
	// Stylesheets.
	// -------------------------------------------------------------------------

	// Add layout css.
	wp_enqueue_style(
		'cp_layout_css',
		get_template_directory_uri() . '/assets/css/screen-default' . $dev . '.css',
		[],
		COMMENTPRESS_VERSION, // Version.
		'all' // Media.
	);

	// -------------------------------------------------------------------------
	// Overrides for styles - for child themes, dequeue these and add you own.
	// -------------------------------------------------------------------------

	// Add Google Webfont "Lato".
	wp_enqueue_style(
		'cp_webfont_css',
		set_url_scheme( 'http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic' ),
		[ 'cp_layout_css' ],
		null, // No version, thanks.
		null // No media, thanks.
	);

	// Add colours css.
	wp_enqueue_style(
		'cp_colours_css',
		get_template_directory_uri() . '/assets/css/colours-01' . $dev . '.css',
		[ 'cp_webfont_css' ],
		COMMENTPRESS_VERSION, // Version.
		'all' // Media.
	);

	// Use dashicons.
	wp_enqueue_style( 'dashicons' );

	// -------------------------------------------------------------------------
	// Javascripts.
	// -------------------------------------------------------------------------

	// Access plugin.
	global $commentpress_core;

	// If we have the plugin enabled.
	if ( is_object( $commentpress_core ) ) {

		// Enqueue common js.
		wp_enqueue_script(
			'cp_common_js',
			get_template_directory_uri() . '/assets/js/cp_js_common' . $dev . '.js',
			[ 'jquery_commentpress' ],
			COMMENTPRESS_VERSION // Version.
		);

		// Test for BuddyPress special page.
		if ( $commentpress_core->is_buddypress() AND $commentpress_core->is_buddypress_special_page() ) {

			// Skip custom addComment.

		} else {

			// Enqueue form js.
			wp_enqueue_script(
				'cp_form',
				plugins_url( 'commentpress-core/assets/js/jquery.commentform' . $dev . '.js', COMMENTPRESS_PLUGIN_FILE ),
				[ 'cp_common_js' ],
				COMMENTPRESS_VERSION // Version.
			);

			// Localisation array.
			$vars = [
				'localisation' => [
					'submit' => __( 'Edit Comment', 'commentpress-core' ),
					'title' => __( 'Leave a comment', 'commentpress-core' ),
					'edit_title' => __( 'Edit comment', 'commentpress-core' ),
				],
			];

			// Localise with wp function.
			wp_localize_script(
				'cp_form',
				'CommentPress_Form',
				$vars
			);

		}

		// Test for CommentPress Core special page.
		if ( $commentpress_core->db->is_special_page() ) {

			// Enqueue accordion-like Javascript.
			wp_enqueue_script(
				'cp_special',
				get_template_directory_uri() . '/assets/js/cp_js_all_comments.js',
				[ 'cp_form' ],
				COMMENTPRESS_VERSION // Version.
			);

		}

	}

}
endif; // End commentpress_enqueue_scripts_and_styles.

// Add a filter for the above, very late so it (hopefully) is last in the queue.
add_action( 'wp_enqueue_scripts', 'commentpress_enqueue_scripts_and_styles', 100 );



if ( ! function_exists( 'commentpress_enqueue_print_styles' ) ):
/**
 * Add CommentPress Core print stylesheet.
 *
 * @since 3.0
 */
function commentpress_enqueue_print_styles() {

	// Check for dev.
	$dev = commentpress_minified();

	// Add print css.
	wp_enqueue_style(
		'cp_print_css',
		get_template_directory_uri() . '/assets/css/print' . $dev . '.css',
		[ 'cp_layout_css' ],
		COMMENTPRESS_VERSION, // Version.
		'print'
	);

}
endif; // End commentpress_enqueue_print_styles.

// Add a filter for the above, very late so it (hopefully) is last in the queue.
add_action( 'wp_enqueue_scripts', 'commentpress_enqueue_print_styles', 101 );



if ( ! function_exists( 'commentpress_buddypress_support' ) ):
/**
 * Enable support for BuddyPress.
 *
 * @since 3.3
 */
function commentpress_buddypress_support() {

	// Add filter for activity class.
	add_filter( 'bp_get_activity_css_class', 'commentpress_bp_activity_css_class' );

	// Add filter for blogs class.
	add_filter( 'bp_get_blog_class', 'commentpress_bp_blog_css_class' );

	// Add filter for groups class.
	add_filter( 'bp_get_group_class', 'commentpress_bp_group_css_class' );

}
endif; // End commentpress_buddypress_support.

// Add an action for the above.
add_action( 'bp_setup_globals', 'commentpress_buddypress_support' );



if ( ! function_exists( 'commentpress_header' ) ):
/**
 * Custom header.
 *
 * @since 3.0
 */
function commentpress_header() {

	// Access plugin.
	global $commentpress_core;

	// Init with same colour as theme stylesheets and default in class_commentpress_db.php.
	$bg_colour = '2c2622';

	// Override if we have the plugin enabled.
	if ( is_object( $commentpress_core ) ) {
		$bg_colour = $commentpress_core->db->option_get_header_bg();
	}

	// Allow overrides.
	$bg_colour = apply_filters( 'cp_default_header_bgcolor', $bg_colour );

	// Init background-image.
	$bg_image = '';

	// Get header image.
	$header_image = get_header_image();

	// Do we have a background-image?
	if ( $header_image ) {
		$bg_image = 'background-image: url("' . $header_image . '");';
	}

	// Get custom text colour.
	// Note: this does NOT retrieve the default if not manually set in the Theme Customizer in WP3.4.
	$text_color = get_header_textcolor();

	// WP3.4 seems to behave differently.
	global $wp_version;
	if ( version_compare( $wp_version, '3.4', '>=' ) ) {

		// If blank, we're hiding the title.
		if ( $text_color == 'blank' ) {
			$css = 'text-indent: -9999px;';
		} else {

			// If empty, we need to use default.
			if ( $text_color == '' ) {
				$css = 'color: #' . HEADER_TEXTCOLOR . ';';
			} else {

				// Use the custom one. I know this amounts to the same thing.
				$css = 'color: #' . $text_color . ';';
			}

		}

	} else {

		// Use previous logic.
		if ( $text_color == 'blank' OR $text_color == '' ) {
			$css = 'text-indent: -9999px;';
		} else {
			$css = 'color: #' . $text_color . ';';
		}

	}

	// Build inline styles.
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
endif; // End commentpress_header.



/*
	// If no custom options for text are set, ignore.
	if ( $text_color == HEADER_TEXTCOLOR ) {

		// Set flag.
		$ignore = true;

	}

	// If blank or empty, we're hiding the title.
	if ( $text_color == 'blank' OR $text_color == '' ) {

	}

	// If we get this far, we have custom styles. Let's do this.
	print_r( ( $text_color ? $text_color : 'nowt<br/>' ) );
	print_r( HEADER_TEXTCOLOR ); die();


*/



if ( ! function_exists( 'commentpress_page_navigation' ) ):
/**
 * Builds a list of previous and next pages, optionally with comments.
 *
 * @param bool $with_comments True returns the next page with comments.
 * @return str $nav_list The unordered list of navigation links.
 */
function commentpress_page_navigation( $with_comments = false ) {

	// Declare access to globals.
	global $commentpress_core;

	// Bail if the plugin is not active.
	if ( !is_object( $commentpress_core ) ) return;

	// Init formatting.
	$before_next = '<li class="alignright">';
	$after_next = ' </li>';
	$before_prev = '<li class="alignleft">';
	$after_prev = '</li>';

	// Init.
	$next_page_html = '';

	// Get next page.
	$next_page = $commentpress_core->nav->get_next_page( $with_comments );

	// Did we get a next page?
	if ( is_object( $next_page ) ) {

		// Init title.
		$img = '';
		$title = __( 'Next page', 'commentpress-core' ); //htmlentities( $next_page->post_title );

		// If we wanted pages with comments.
		if ( $with_comments ) {

			// Set title.
			$title = __( 'Next page with comments', 'commentpress-core' );
			$img = '<img src="' . get_template_directory_uri() . '/assets/images/next.png" />';

		}

		// Define list item.
		$next_page_html = $before_next .
						  $img . '<a href="' . get_permalink( $next_page->ID ) . '" id="next_page" class="css_btn" title="' . esc_attr( $title ) . '">' . $title . '</a>' . $after_next;

	}

	// Init.
	$prev_page_html = '';

	// Get next page.
	$prev_page = $commentpress_core->nav->get_previous_page( $with_comments );

	// Did we get a next page?
	if ( is_object( $prev_page ) ) {

		// Init title.
		$img = '';
		$title = __( 'Previous page', 'commentpress-core' ); //htmlentities( $prev_page->post_title );

		// If we wanted pages with comments.
		if ( $with_comments ) {

			// Set title.
			$title = __( 'Previous page with comments', 'commentpress-core' );
			$img = '<img src="' . get_template_directory_uri() . '/assets/images/prev.png" />';

		}

		// Define list item.
		$prev_page_html = $before_prev .
						  $img . '<a href="' . get_permalink( $prev_page->ID ) . '" id="previous_page" class="css_btn" title="' . esc_attr( $title ) . '">' . $title . '</a>' . $after_prev;

	}

	// Init return.
	$nav_list = '';

	// Did we get either?
	if ( $next_page_html != '' OR $prev_page_html != '' ) {

		// Construct nav list items.
		$nav_list = $prev_page_html . "\n" . $next_page_html . "\n";

	}

	// --<
	return $nav_list;

}
endif; // End commentpress_page_navigation



if ( ! function_exists( 'commentpress_get_all_comments_content' ) ):
/**
 * All-comments page display function.
 *
 * @param str $page_or_post Retrieve either 'page' or 'post' comments.
 * @return str $html The comments.
 */
function commentpress_get_all_comments_content( $page_or_post = 'page' ) {

	// Declare access to globals.
	global $commentpress_core, $cp_comment_output;

	// Init output.
	$html = '';

	// Get all approved comments.
	$all_comments = get_comments( [
		'status' => 'approve',
		'orderby' => 'comment_post_ID,comment_date',
		'order' => 'ASC',
		'post_type' => $page_or_post,
	] );

	// Kick out if none.
	if ( count( $all_comments ) == 0 ) return $html;

	// Build list of posts to which they are attached.
	$posts_with = [];
	$post_comment_counts = [];
	foreach( $all_comments AS $comment ) {

		// Add to posts with comments array.
		if ( !in_array( $comment->comment_post_ID, $posts_with ) ) {
			$posts_with[] = $comment->comment_post_ID;
		}

		// Increment counter.
		if ( !isset( $post_comment_counts[$comment->comment_post_ID] ) ) {
			$post_comment_counts[$comment->comment_post_ID] = 1;
		} else {
			$post_comment_counts[$comment->comment_post_ID]++;
		}

	}

	// Kick out if none.
	if ( count( $posts_with ) == 0 ) return $html;

	// Get those posts.
	$posts = get_posts( [
		'orderby' => 'comment_count',
		'order' => 'DESC',
		'post_type' => $page_or_post,
		'include' => $posts_with,
	] );

	// Kick out if none.
	if ( count( $posts ) == 0 ) return $html;

	// Open ul.
	$html .= '<ul class="all_comments_listing">' . "\n\n";

	foreach( $posts AS $post ) {

		// Open li.
		$html .= '<li class="page_li"><!-- page li -->' . "\n\n";

		// Define comment count.
		$comment_count_text = sprintf(
			_n( '<span class="cp_comment_count">%d</span> comment', '<span class="cp_comment_count">%d</span> comments', $post_comment_counts[$post->ID], 'commentpress-core' ),
			$post_comment_counts[$post->ID]
		);

		// Show it.
		$html .= '<h3>' . esc_html( $post->post_title ) . ' <span>(' . $comment_count_text . ')</span></h3>' . "\n\n";

		// Open comments div.
		$html .= '<div class="item_body">' . "\n\n";

		// Open ul.
		$html .= '<ul class="item_ul">' . "\n\n";

		// Open li.
		$html .= '<li class="item_li"><!-- item li -->' . "\n\n";

		// Check for password-protected.
		if ( post_password_required( $post->ID ) ) {

			// Construct notice.
			$comment_body = '<div class="comment-content">' . __( 'Password protected', 'commentpress-core' ) . '</div>' . "\n";

			// Add notice.
			$html .= '<div class="comment_wrapper">' . "\n" . $comment_body . '</div>' . "\n\n";

		} else {

			foreach( $all_comments AS $comment ) {

				if ( $comment->comment_post_ID == $post->ID ) {

					// Show the comment.
					$html .= commentpress_format_comment( $comment );

					/*
					// Get comment children.
					$children = commentpress_get_children( $comment, $page_or_post );

					// Do we have any?
					if( count( $children ) > 0 ) {

						// Recurse.
						commentpress_get_comments( $children, $page_or_post );

						// Show them.
						$html .= $cp_comment_output;

						// Clear global comment output.
						$cp_comment_output = '';

					}
					*/

				}

			}

		}

		// Close li.
		$html .= '</li><!-- /item li -->' . "\n\n";

		// Close ul.
		$html .= '</ul>' . "\n\n";

		// Close item div.
		$html .= '</div><!-- /item_body -->' . "\n\n";

		// Close li.
		$html .= '</li><!-- /page li -->' . "\n\n\n\n";

	}

	// Close ul.
	$html .= '</ul><!-- /all_comments_listing -->' . "\n\n";

	// --<
	return $html;

}
endif; // Commentpress_get_all_comments_content



if ( ! function_exists( 'commentpress_get_all_comments_page_content' ) ):
/**
 * All-comments page display function.
 *
 * @return str $page_content The page content.
 */
function commentpress_get_all_comments_page_content() {

	// Allow oEmbed in comments.
	global $wp_embed;
	if ( $wp_embed instanceof WP_Embed ) {
		add_filter( 'comment_text', [ $wp_embed, 'autoembed' ], 1 );
	}

	// Declare access to globals.
	global $commentpress_core;

	// Init page content.
	$page_content = '';

	// Get page or post.
	$page_or_post = $commentpress_core->get_list_option();

	// Set default.
	$blog_title = apply_filters(
		'cp_page_all_comments_blog_title',
		__( 'Comments on the Blog', 'commentpress-core' )
	);

	// Set default.
	$book_title = apply_filters(
		'cp_page_all_comments_book_title',
		__( 'Comments on the Pages', 'commentpress-core' )
	);

	// Get title.
	$title = ( $page_or_post == 'page' ) ? $book_title : $blog_title;

	// Get data.
	$data = commentpress_get_all_comments_content( $page_or_post );

	// Did we get any?
	if ( $data != '' ) {

		// Set title.
		$page_content .= '<p class="comments_hl">' . $title . '</p>' . "\n\n";

		// Set data.
		$page_content .= $data . "\n\n";

	}

	// Get data for other page type.
	$other_type = ( $page_or_post == 'page' ) ? 'post': 'page';

	// Get title.
	$title = ( $page_or_post == 'page' ) ? $blog_title : $book_title;

	// Get data.
	$data = commentpress_get_all_comments_content( $other_type );

	// Did we get any?
	if ( $data != '' ) {

		// Set title.
		$page_content .= '<p class="comments_hl">' . $title . '</p>' . "\n\n";

		// Set data.
		$page_content .= $data . "\n\n";

	}

	// --<
	return $page_content;

}
endif; // End commentpress_get_all_comments_page_content.



if ( ! function_exists( 'commentpress_add_loginout_id' ) ):
/**
 * Utility to add button css id to login links.
 *
 * @param str $link The existing link.
 * @return str $link The modified link.
 */
function commentpress_add_loginout_id( $link ) {

	// Site admin link?
	if ( false !== strstr( $link, admin_url() ) ) {

		// Site admin.
		$id = 'btn_site_admin';

	} else {

		// If logged in.
		if ( is_user_logged_in() ) {

			// Logout.
			$id = 'btn_logout';

		} else {

			// Login.
			$id = 'btn_login';

		}

	}

	// Add CSS.
	$link = str_replace( '<a ', '<a id="' . $id . '" class="button" ', $link );

	// --<
	return $link;

}
endif; // End commentpress_add_loginout_id

// Add filters for WordPress admin links.
add_filter( 'loginout', 'commentpress_add_link_css' );
add_filter( 'loginout', 'commentpress_add_loginout_id' );
add_filter( 'register', 'commentpress_add_loginout_id' );



/**
 * Register widget areas for this theme.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 *
 * @since 3.8.10
 */
function commentpress_register_widget_areas() {

	// Define an area where a widget may be placed.
	register_sidebar( [
		'name' => __( 'CommentPress Footer', 'commentpress-core' ),
		'id' => 'cp-license-8',
		'description' => __( 'An optional widget area in the footer of a CommentPress theme', 'commentpress-core' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => "</div>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	] );

}

add_action( 'widgets_init', 'commentpress_register_widget_areas' );



/**
 * Filter the default sidebar before modifications.
 *
 * @since 3.9.8
 *
 * @param str $sidebar The default sidebar before any contextual modifications.
 * @return str $sidebar The modified sidebar before any contextual modifications.
 */
function commentpress_default_theme_default_sidebar( $sidebar ) {

	// This theme has three sidebars and it makes sense for the TOC to be default.
	return 'toc';

}

add_filter( 'commentpress_default_sidebar', 'commentpress_default_theme_default_sidebar' );



