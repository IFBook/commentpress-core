<?php
/**
 * Theme Functions
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Always include our common theme functions file.
require_once COMMENTPRESS_PLUGIN_PATH . 'includes/core/assets/includes/theme/theme-functions.php';

/**
 * Set the content width based on the theme's design and stylesheet.
 *
 * This seems to be a WordPress requirement - though rather dumb in the context
 * of our theme, which has a percentage-based default width.
 *
 * I have arbitrarily set it to the default content-width when viewing on a
 * 1280px-wide screen.
 */
if ( ! isset( $content_width ) ) {
	$content_width = 1024;
}

if ( ! function_exists( 'commentpress_setup' ) ) :

	/**
	 * Set up CommentPress Modern theme.
	 *
	 * @since 3.0
	 */
	function commentpress_setup() {

		// Add title support.
		add_theme_support( 'title-tag' );

		// Allow custom backgrounds.
		add_theme_support( 'custom-background', [
			'default-color'          => 'ccc',
			'default-image'          => '',
			'wp-head-callback'       => 'commentpress_background',
			'admin-head-callback'    => '',
			'admin-preview-callback' => '',
		] );

		// Allow custom header.
		add_theme_support( 'custom-header', [
			'default-text-color' => 'eeeeee',
			'width' => apply_filters( 'cp_header_image_width', 940 ),
			'height' => apply_filters( 'cp_header_image_height', 67 ),
			'wp-head-callback' => 'commentpress_header',
			'admin-head-callback' => 'commentpress_admin_header',
		] );

		/**
		 * Default custom headers packaged with the theme (see Twenty Eleven)
		 *
		 * A nice side-effect of supplying a default header image is that it triggers the
		 * "Header Image" option in the Theme Customizer.
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

		// Allow the use of wp_nav_menu() - first we need to register them.
		register_nav_menu( 'toc', __( 'Table of Contents', 'commentpress-core' ) );
		register_nav_menu( 'footer', __( 'Footer', 'commentpress-core' ) );

		// Get core plugin reference.
		$core = commentpress_core();

		// If we have the plugin enabled.
		if ( ! empty( $core ) ) {

			// Get the option.
			$featured_images = $core->db->option_get( 'cp_featured_images', 'n' );

			// Do we have the featured images option enabled?
			if ( $featured_images == 'y' ) {

				// Use Featured Images - also known as Post Thumbnails.
				add_theme_support( 'post-thumbnails' );

				// Define a custom image size, cropped to fit.
				add_image_size(
					'commentpress-feature',
					apply_filters( 'cp_feature_image_width', 1200 ),
					apply_filters( 'cp_feature_image_height', 600 ),
					true // Crop.
				);

			}

		}

		/*
		// No need for default sidebar in this theme.
		add_filter( 'commentpress_hide_sidebar_option', '__return_true' );
		*/

	}

endif;

// Add after theme setup hook.
add_action( 'after_setup_theme', 'commentpress_setup' );

if ( ! function_exists( 'commentpress_enqueue_scripts_and_styles' ) ) :

	/**
	 * Add CommentPress Core front-end styles.
	 *
	 * @since 3.0
	 */
	function commentpress_enqueue_scripts_and_styles() {

		// Check for dev.
		$min = commentpress_minified();

		// -------------------------------------------------------------------------
		// Stylesheets
		// -------------------------------------------------------------------------

		// Register screen styles.
		wp_register_style(
			'cp_screen_css', // Unique ID.
			get_template_directory_uri() . '/assets/css/screen' . $min . '.css',
			[], // Dependencies.
			COMMENTPRESS_VERSION, // Version.
			'all' // Media.
		);

		// -------------------------------------------------------------------------
		// Overrides for styles - for child themes, dequeue these and add you own
		// -------------------------------------------------------------------------

		// Add Google Webfont "Lato".
		wp_enqueue_style(
			'cp_webfont_lato_css',
			set_url_scheme( 'http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic' ),
			[ 'cp_screen_css' ],
			COMMENTPRESS_VERSION, // Version.
			'all' // Media.
		);

		// Add colours CSS.
		wp_enqueue_style(
			'cp_colours_css',
			get_template_directory_uri() . '/assets/css/colours-01' . $min . '.css',
			[ 'cp_webfont_lato_css' ],
			COMMENTPRESS_VERSION, // Version.
			'all' // Media.
		);

		// Use dashicons.
		wp_enqueue_style( 'dashicons' );

		// -------------------------------------------------------------------------
		// Javascripts.
		// -------------------------------------------------------------------------

		// Enqueue common Javascript.
		wp_enqueue_script(
			'cp_common_js',
			get_template_directory_uri() . '/assets/js/screen' . $min . '.js',
			[ 'jquery_commentpress' ], // Dependencies.
			COMMENTPRESS_VERSION, // Version.
			false
		);

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return;
		}

		// Skip for BuddyPress Special Pages.
		if ( ! $core->bp->is_buddypress_special_page() ) {

			// Enqueue form Javascript.
			wp_enqueue_script(
				'cp_form',
				plugins_url( 'includes/core/assets/js/jquery.commentform' . $min . '.js', COMMENTPRESS_PLUGIN_FILE ),
				[ 'cp_common_js' ], // Dependencies.
				COMMENTPRESS_VERSION, // Version.
				false
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

		// Test for CommentPress Core Special Page.
		if ( $core->pages_legacy->is_special_page() ) {

			// Enqueue accordion-like Javascript.
			wp_enqueue_script(
				'cp_special',
				get_template_directory_uri() . '/assets/js/cp_js_all_comments.js',
				[ 'cp_form' ], // Dependencies.
				COMMENTPRESS_VERSION, // Version.
				false
			);

		}

	}

endif;

// Add a filter for the above, very late so it (hopefully) is last in the queue.
add_action( 'wp_enqueue_scripts', 'commentpress_enqueue_scripts_and_styles', 995 );

if ( ! function_exists( 'commentpress_enqueue_print_styles' ) ) :

	/**
	 * Add CommentPress Core print stylesheet.
	 *
	 * @since 3.0
	 */
	function commentpress_enqueue_print_styles() {

		// Check for dev.
		$min = commentpress_minified();

		// Add print CSS.
		wp_enqueue_style(
			'cp_print_css',
			get_template_directory_uri() . '/assets/css/print' . $min . '.css',
			[ 'cp_screen_css' ],
			COMMENTPRESS_VERSION, // Version.
			'print'
		);

	}

endif;

// Add a filter for the above, very late so it (hopefully) is last in the queue.
add_action( 'wp_enqueue_scripts', 'commentpress_enqueue_print_styles', 999 );

if ( ! function_exists( 'commentpress_buddypress_support' ) ) :

	/**
	 * Enable support for BuddyPress.
	 *
	 * @since 3.3
	 */
	function commentpress_buddypress_support() {

		// Include bp-overrides when BuddyPress is active.
		add_action( 'wp_enqueue_scripts', 'commentpress_bp_enqueue_styles', 996 );

		// Add filter for Activity class.
		add_filter( 'bp_get_activity_css_class', 'commentpress_bp_activity_css_class' );

		// Add filter for Blogs class.
		add_filter( 'bp_get_blog_class', 'commentpress_bp_blog_css_class' );

		// Add filter for Groups class.
		add_filter( 'bp_get_group_class', 'commentpress_bp_group_css_class' );

		// Add wrapper element to Member Settings section.
		add_action( 'bp_before_member_settings_template', 'commentpress_bp_wrapper_open' );
		add_action( 'bp_after_member_settings_template', 'commentpress_bp_wrapper_close' );

	}

endif;

// Add an action for the above (BuddyPress hooks this to after_setup_theme with priority 100).
add_action( 'bp_after_setup_theme', 'commentpress_buddypress_support' );

if ( ! function_exists( 'commentpress_bp_wrapper_open' ) ) :

	/**
	 * Open wrapper element for BuddyPress.
	 *
	 * @since 3.9.15
	 */
	function commentpress_bp_wrapper_open() {
		echo '<div class="cp-member-settings-template">';
	}

endif;

if ( ! function_exists( 'commentpress_bp_wrapper_close' ) ) :

	/**
	 * Close BuddyPress wrapper element.
	 *
	 * @since 3.9.15
	 */
	function commentpress_bp_wrapper_close() {
		echo '</div>';
	}

endif;

if ( ! function_exists( 'commentpress_bp_enqueue_styles' ) ) :

	/**
	 * Add BuddyPress front-end styles.
	 *
	 * @since 3.3
	 */
	function commentpress_bp_enqueue_styles() {

		// Kick out on admin.
		if ( is_admin() ) {
			return;
		}

		// Check for dev.
		$min = commentpress_minified();

		// Add our own BuddyPress CSS.
		wp_enqueue_style(
			'cp_buddypress_css',
			get_template_directory_uri() . '/assets/css/bp-overrides' . $min . '.css',
			[ 'cp_screen_css' ],
			COMMENTPRESS_VERSION, // Version.
			'all' // Media.
		);

	}

endif;

if ( ! function_exists( 'commentpress_background' ) ) :

	/**
	 * Custom background colour.
	 *
	 * @since 3.0
	 *
	 * @see _custom_background_cb()
	 */
	function commentpress_background() {

		// $color is the saved custom color.
		// A default has to be specified in style.css. It will not be printed here.
		$color = get_theme_mod( 'background_color' );

		// Bail if we don't have one.
		if ( ! $color ) {
			return;
		}

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

endif;

if ( ! function_exists( 'commentpress_header' ) ) :

	/**
	 * Custom header.
	 *
	 * @since 3.0
	 */
	function commentpress_header() {

		// Get core plugin reference.
		$core = commentpress_core();

		// Init with same colour as theme stylesheets and default in class-core-database.php.
		$bg_colour = '2c2622';

		// Override if we have the plugin enabled.
		if ( ! empty( $core ) ) {
			$bg_colour = $core->db->option_get_header_bg();
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
		// TODO: Check this.
		$text_color = get_header_textcolor();

		// If blank, we're hiding the title.
		if ( $text_color == 'blank' ) {
			$css = 'text-indent: -9999px;';
		} else {

			/*
			// If empty, we need to use default.
			$css = 'color: #' . HEADER_TEXTCOLOR . ';';
			*/

			// Use the custom one. I know this amounts to the same thing.
			if ( ! empty( $text_color ) ) {
				$css = 'color: #' . $text_color . ';';
			}

		}

		// Build inline styles.
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

endif;

if ( ! function_exists( 'commentpress_page_navigation' ) ) :

	/**
	 * Builds a list of previous and Next Pages, optionally with Comments.
	 *
	 * @since 3.0
	 *
	 * @param bool $with_comments True returns the Next Page with Comments.
	 * @return str $nav_list The unordered list of navigation links.
	 */
	function commentpress_page_navigation( $with_comments = false ) {

		// Init return.
		$nav_list = '';

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return $nav_list;
		}

		// Init formatting.
		$before_next = '<li class="alignright">';
		$after_next = ' </li>';
		$before_prev = '<li class="alignleft">';
		$after_prev = '</li>';

		// Init.
		$next_page_html = '';

		// Get Next Page.
		$next_page = $core->nav->get_next_page( $with_comments );

		// Did we get a Next Page?
		if ( is_object( $next_page ) ) {

			// Init title.
			$img = '';
			$title = __( 'Next page', 'commentpress-core' );

			// If we wanted Pages with Comments.
			if ( $with_comments ) {

				// Set title.
				$title = __( 'Next page with comments', 'commentpress-core' );
				$img = '<img src="' . get_template_directory_uri() . '/assets/images/next.png" />';

			}

			// Define list item.
			$next_page_html = $before_next .
				$img .
				'<a href="' . get_permalink( $next_page->ID ) . '" class="next_page" title="' . esc_attr( $title ) . '">' . esc_html( $title ) . '</a>' .
				$after_next;

		}

		// Init.
		$prev_page_html = '';

		// Get Next Page.
		$prev_page = $core->nav->get_previous_page( $with_comments );

		// Did we get a Next Page?
		if ( is_object( $prev_page ) ) {

			// Init title.
			$img = '';
			$title = __( 'Previous page', 'commentpress-core' );

			// If we wanted Pages with Comments.
			if ( $with_comments ) {

				// Set title.
				$title = __( 'Previous page with comments', 'commentpress-core' );
				$img = '<img src="' . get_template_directory_uri() . '/assets/images/prev.png" />';

			}

			// Define list item.
			$prev_page_html = $before_prev .
				$img .
				'<a href="' . get_permalink( $prev_page->ID ) . '" class="previous_page" title="' . esc_attr( $title ) . '">' . esc_html( $title ) . '</a>' .
				$after_prev;

		}

		// Did we get either?
		if ( $next_page_html != '' || $prev_page_html != '' ) {

			// Construct nav list items.
			$nav_list = $prev_page_html . "\n" . $next_page_html . "\n";

		}

		// --<
		return $nav_list;

	}

endif;

if ( ! function_exists( 'commentpress_get_all_comments_content' ) ) :

	/**
	 * All-comments Page display function.
	 *
	 * @since 3.0
	 *
	 * @param str $page_or_post Retrieve either 'page' or 'post' comments.
	 * @return str $html The comments.
	 */
	function commentpress_get_all_comments_content( $page_or_post = 'page' ) {

		// Declare access to globals.
		global $cp_comment_output;

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
		if ( count( $all_comments ) == 0 ) {
return $html;
		}

		// Build list of Posts to which they are attached.
		$posts_with = [];
		$post_comment_counts = [];
		foreach ( $all_comments as $comment ) {

			// Add to Posts with comments array.
			if ( ! in_array( $comment->comment_post_ID, $posts_with ) ) {
				$posts_with[] = $comment->comment_post_ID;
			}

			// Increment counter.
			if ( ! isset( $post_comment_counts[ $comment->comment_post_ID ] ) ) {
				$post_comment_counts[ $comment->comment_post_ID ] = 1;
			} else {
				$post_comment_counts[ $comment->comment_post_ID ]++;
			}

		}

		// Kick out if none.
		if ( count( $posts_with ) == 0 ) {
			return $html;
		}

		// Get those Posts.
		$posts = get_posts( [
			'orderby' => 'comment_count',
			'order' => 'DESC',
			'post_type' => $page_or_post,
			'include' => $posts_with,
		] );

		// Kick out if none.
		if ( count( $posts ) == 0 ) {
			return $html;
		}

		// Open ul.
		$html .= '<ul class="all_comments_listing">' . "\n\n";

		foreach ( $posts as $post ) {

			// Open li.
			$html .= '<li class="page_li"><!-- page li -->' . "\n\n";

			// Define Comment count.
			$comment_count_text = sprintf(
				_n( '<span class="cp_comment_count">%d</span> comment', '<span class="cp_comment_count">%d</span> comments', $post_comment_counts[ $post->ID ], 'commentpress-core' ),
				$post_comment_counts[ $post->ID ]
			);

			// Show it.
			$html .= '<h4>' . esc_html( $post->post_title ) . ' <span>(' . $comment_count_text . ')</span></h4>' . "\n\n";

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

				foreach ( $all_comments as $comment ) {

					if ( $comment->comment_post_ID == $post->ID ) {

						// Show the Comment.
						$html .= commentpress_format_comment( $comment );

						/*
						// Get Comment children.
						$children = commentpress_get_children( $comment, $page_or_post );

						// Do we have any?
						if( count( $children ) > 0 ) {

							// Recurse.
							commentpress_get_comments( $children, $page_or_post );

							// Show them.
							$html .= $cp_comment_output;

							// Clear global Comment output.
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

endif;

if ( ! function_exists( 'commentpress_get_all_comments_page_content' ) ) :

	/**
	 * All-comments Page display function.
	 *
	 * @since 3.0
	 *
	 * @return str $page_content The Page content.
	 */
	function commentpress_get_all_comments_page_content() {

		// Allow oEmbed in comments.
		global $wp_embed;
		if ( $wp_embed instanceof WP_Embed ) {
			add_filter( 'comment_text', [ $wp_embed, 'autoembed' ], 1 );
		}

		// Init Page content.
		$page_content = '';

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return $page_content;
		}

		// Get Page or Post.
		$page_or_post = $core->get_list_option();

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
			$page_content .= '<h3 class="comments_hl">' . $title . '</h3>' . "\n\n";

			// Set data.
			$page_content .= $data . "\n\n";

		}

		// Get data for other Page Type.
		$other_type = ( $page_or_post == 'page' ) ? 'post' : 'page';

		// Get title.
		$title = ( $page_or_post == 'page' ) ? $blog_title : $book_title;

		// Get data.
		$data = commentpress_get_all_comments_content( $other_type );

		// Did we get any?
		if ( $data != '' ) {

			// Set title.
			$page_content .= '<h3 class="comments_hl">' . $title . '</h3>' . "\n\n";

			// Set data.
			$page_content .= $data . "\n\n";

		}

		// --<
		return $page_content;

	}

endif;

if ( ! function_exists( 'commentpress_add_loginout_id' ) ) :

	/**
	 * Utility to add button css id to login links.
	 *
	 * @since 3.0
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
		$link = str_replace( '<a ', '<a id="' . $id . '" ', $link );

		// --<
		return $link;

	}

endif;

// Add filters for WordPress admin links.
add_filter( 'loginout', 'commentpress_add_link_css' );
add_filter( 'loginout', 'commentpress_add_loginout_id' );
add_filter( 'register', 'commentpress_add_loginout_id' );

if ( ! function_exists( 'commentpress_convert_link_to_button' ) ) :

	/**
	 * Utility to add button class to BuddyPress 1.9 notification links.
	 *
	 * @since 3.5
	 *
	 * @param str $link The existing link.
	 * @return str $link The modified link.
	 */
	function commentpress_convert_link_to_button( $link ) {

		// Add CSS.
		$link = str_replace( 'class="mark-unread', 'class="button mark-unread', $link );
		$link = str_replace( 'class="mark-read', 'class="button mark-read', $link );
		$link = str_replace( 'class="delete', 'class="button delete', $link );

		// --<
		return $link;

	}

endif;

// Add filters for the above.
add_filter( 'bp_get_the_notification_mark_unread_link', 'commentpress_convert_link_to_button' );
add_filter( 'bp_get_the_notification_mark_read_link', 'commentpress_convert_link_to_button' );
add_filter( 'bp_get_the_notification_delete_link', 'commentpress_convert_link_to_button' );

if ( ! function_exists( 'commentpress_get_feature_image' ) ) :

	/**
	 * Show Feature Image.
	 *
	 * @since 3.5
	 */
	function commentpress_get_feature_image() {

		// Access Post.
		global $post;

		// Do we have a featured image?
		if ( commentpress_has_feature_image() ) {

			// Show it.
			echo '<div class="cp_feature_image">';

			/**
			 * Filter the Feature Image.
			 *
			 * @since 3.9
			 *
			 * @param str The HTML for showing the image.
			 * @param WP_Post The current WordPress Post object.
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

					// When pulling Post in via AJAX, is_page() isn't available, so
					// inspect the Post Type as well.
					if ( is_page() || $post->post_type == 'page' ) {

						?>

						<?php

						// Default to hidden.
						$cp_title_visibility = ' style="display: none;"';

						// Override if we've elected to show the title.
						if ( commentpress_get_post_title_visibility( get_the_ID() ) ) {
							$cp_title_visibility = '';
						}

						// Construct title.
						$title = '<h2 class="post_title page_title"' . $cp_title_visibility . '>' .
							'<a href="' . get_permalink() . '">' . get_the_title() . '</a>' .
						'</h2>';

						/**
						 * Filter the Page/Post title when there is a Feature Image.
						 *
						 * @since 3.9.10
						 *
						 * @param str The HTML for showing the image.
						 * @param WP_Post The current WordPress Post object.
						 */
						echo apply_filters( 'commentpress_get_feature_image_title', $title, $post );

						// Default to hidden.
						$cp_meta_visibility = ' style="display: none;"';

						// Override if we've elected to show the meta.
						if ( commentpress_get_post_meta_visibility( get_the_ID() ) ) {
							$cp_meta_visibility = '';
						}

						?>
						<div class="search_meta page_search_meta"<?php echo $cp_meta_visibility; ?>>
							<?php commentpress_echo_post_meta(); ?>
						</div>

					<?php
					} else {

						// Construct title.
						$title = '<h2 class="post_title">' .
							'<a href="' . get_permalink() . '">' . get_the_title() . '</a>' .
						'</h2>';

						/**
						 * Filter the Page/Post title when there is a Feature Image.
						 *
						 * @since 3.9.10
						 *
						 * @param str The HTML for showing the image.
						 * @param WP_Post The current WordPress Post object.
						 */
						echo apply_filters( 'commentpress_get_feature_image_title', $title, $post );

						?>

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

endif;

/**
 * Utility to test for Feature Image, because has_post_thumbnail() fails sometimes.
 *
 * @see http://codex.wordpress.org/Function_Reference/has_post_thumbnail
 *
 * @since 3.5
 *
 * @return bool True if Post has thumbnail, false otherwise.
 */
function commentpress_has_feature_image() {

	// Init return.
	$has_feature_image = false;

	// Replacement check.
	if ( '' != get_the_post_thumbnail() ) {
		$has_feature_image = true;
	}

	/**
	 * Allow this test to be overridden.
	 *
	 * @since 3.9
	 *
	 * @param bool $has_feature_image True if the Post has a Feature Image, false otherwise.
	 */
	return apply_filters( 'commentpress_has_feature_image', $has_feature_image );

}

/**
 * Register Widget areas for this theme.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 *
 * @since 3.8.10
 */
function commentpress_register_widget_areas() {

	// Define an area where a Widget may be placed.
	register_sidebar( [
		'name' => __( 'CommentPress Footer', 'commentpress-core' ),
		'id' => 'cp-license-8',
		'description' => __( 'An optional widget area in the footer of a CommentPress theme', 'commentpress-core' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	] );

	// Define an area where a Widget may be placed.
	register_sidebar( [
		'name' => __( 'Navigation Top', 'commentpress-core' ),
		'id' => 'cp-nav-top',
		'description' => __( 'An optional widget area at the top of the Navigation Column', 'commentpress-core' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div></div></div>',
		'before_title' => '<h3 class="widget-title activity_heading">',
		'after_title' => '</h3><div class="paragraph_wrapper"><div class="widget_wrapper clearfix">',
	] );

	// Define an area where a Widget may be placed.
	register_sidebar( [
		'name' => __( 'Navigation Bottom', 'commentpress-core' ),
		'id' => 'cp-nav-bottom',
		'description' => __( 'An optional widget area at the bottom of the Navigation Column', 'commentpress-core' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div></div></div>',
		'before_title' => '<h3 class="widget-title activity_heading">',
		'after_title' => '</h3><div class="paragraph_wrapper"><div class="widget_wrapper clearfix">',
	] );

	// Define an area where a Widget may be placed.
	register_sidebar( [
		'name' => __( 'Activity Top', 'commentpress-core' ),
		'id' => 'cp-activity-top',
		'description' => __( 'An optional widget area at the top of the Activity Column', 'commentpress-core' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div></div></div>',
		'before_title' => '<h3 class="widget-title activity_heading">',
		'after_title' => '</h3><div class="paragraph_wrapper"><div class="widget_wrapper clearfix">',
	] );

	// Define an area where a Widget may be placed.
	register_sidebar( [
		'name' => __( 'Activity Bottom', 'commentpress-core' ),
		'id' => 'cp-activity-bottom',
		'description' => __( 'An optional widget area at the bottom of the Activity Column', 'commentpress-core' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div></div></div>',
		'before_title' => '<h3 class="widget-title activity_heading">',
		'after_title' => '</h3><div class="paragraph_wrapper"><div class="widget_wrapper clearfix">',
	] );

}

add_action( 'widgets_init', 'commentpress_register_widget_areas' );
