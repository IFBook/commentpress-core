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
	$content_width = 588;
}



if ( ! function_exists( 'commentpress_setup' ) ) :

	/**
	 * Set up theme.
	 *
	 * @since 3.0
	 */
	function commentpress_setup() {

		// Add title support.
		add_theme_support( 'title-tag' );

		// Allow custom backgrounds.
		add_theme_support( 'custom-background' );

		// Allow custom header.
		add_theme_support( 'custom-header', [
			'default-text-color'  => 'eeeeee',
			'width'               => apply_filters( 'cp_header_image_width', 940 ),
			'height'              => apply_filters( 'cp_header_image_height', 67 ),
			'wp-head-callback'    => 'commentpress_header',
			'admin-head-callback' => 'commentpress_admin_header',
		] );

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
				'caves-green'  => [
					'url'           => '%s/assets/images/header/caves-green.jpg',
					'thumbnail_url' => '%s/assets/images/header/caves-green-thumbnail.jpg',
					/* translators: header image description */
					'description'   => __( 'Abstract Green', 'commentpress-core' ),
				],
				'caves-red'    => [
					'url'           => '%s/assets/images/header/caves-red.jpg',
					'thumbnail_url' => '%s/assets/images/header/caves-red-thumbnail.jpg',
					/* translators: header image description */
					'description'   => __( 'Abstract Red', 'commentpress-core' ),
				],
				'caves-blue'   => [
					'url'           => '%s/assets/images/header/caves-blue.jpg',
					'thumbnail_url' => '%s/assets/images/header/caves-blue-thumbnail.jpg',
					/* translators: header image description */
					'description'   => __( 'Abstract Blue', 'commentpress-core' ),
				],
				'caves-violet' => [
					'url'           => '%s/assets/images/header/caves-violet.jpg',
					'thumbnail_url' => '%s/assets/images/header/caves-violet-thumbnail.jpg',
					/* translators: header image description */
					'description'   => __( 'Abstract Violet', 'commentpress-core' ),
				],
			]
		);

		// Auto feed links.
		add_theme_support( 'automatic-feed-links' );

		// Style the visual editor with editor-style.css to match the theme style.
		add_editor_style();

		// Allow the use of wp_nav_menu() - first we need to register them.
		register_nav_menu( 'toc', __( 'Table of Contents', 'commentpress-core' ) );

	}

endif;

// Add after theme setup hook.
add_action( 'after_setup_theme', 'commentpress_setup' );



if ( ! function_exists( 'commentpress_default_theme_customize_register' ) ) :

	/**
	 * Implements CommentPress Default Theme options in the Theme Customizer.
	 *
	 * @since 4.0
	 *
	 * @param object $wp_customize Theme Customizer object.
	 */
	function commentpress_default_theme_customize_register( $wp_customize ) {

		// Add customizer section.
		$wp_customize->add_section(
			'cp_theme_options',
			[
				'title'    => __( 'Theme Settings', 'commentpress-core' ),
				'priority' => 36,
			]
		);

		// Use it if we have a pre-existing default in core settings.
		$core = commentpress_core();
		if ( ! empty( $core ) ) {
			$default = (int) $core->db->setting_get( 'cp_min_page_width' );
		}

		// Otherwise, set default.
		if ( empty( $default ) ) {
			$default = 447;
		}

		// Add setting.
		$wp_customize->add_setting(
			'cp_min_page_width',
			[
				'default' => $default,
			]
		);

		// Add text control.
		$wp_customize->add_control(
			'cp_min_page_width',
			[
				'label'   => __( 'Minimum page width in px', 'commentpress-core' ),
				'section' => 'cp_theme_options',
				'type'    => 'number',
			]
		);

	}

endif;

// Add callback for the above.
add_action( 'customize_register', 'commentpress_default_theme_customize_register' );



if ( ! function_exists( 'commentpress_default_theme_javascript_vars' ) ) :

	/**
	 * Filters the Javascript vars.
	 *
	 * @since 4.0
	 *
	 * @param array $vars The default Javascript vars.
	 * @return array $vars The modified Javascript vars.
	 */
	function commentpress_default_theme_javascript_vars( $vars ) {

		// Do we have a colour set via the Customizer?
		$min_page_width = get_theme_mod( 'cp_min_page_width', false );

		// Find legacy setting if not.
		if ( empty( $min_page_width ) ) {

			// Get core plugin reference.
			$core = commentpress_core();

			// Use it if we have a pre-existing default in core settings.
			if ( empty( $core ) ) {
				$min_page_width = (int) $core->db->setting_get( 'cp_min_page_width' );
			}

		}

		// Otherwise, set default.
		if ( empty( $min_page_width ) ) {
			$min_page_width = 447;
		}

		// Set minimum Page width.
		$vars['cp_min_page_width'] = $min_page_width;

		// --<
		return $vars;

	}

endif;

// Add callback for the above.
add_filter( 'commentpress_get_javascript_vars', 'commentpress_default_theme_javascript_vars' );



if ( ! function_exists( 'commentpress_enqueue_scripts_and_styles' ) ) :

	/**
	 * Add CommentPress Core front-end styles.
	 *
	 * @since 3.0
	 */
	function commentpress_enqueue_scripts_and_styles() {

		// Check for minification.
		$min = commentpress_minified();

		// -------------------------------------------------------------------------
		// Stylesheets.
		// -------------------------------------------------------------------------

		// Register layout styles.
		wp_enqueue_style(
			'cp_layout_css',
			get_template_directory_uri() . '/assets/css/screen-default' . $min . '.css',
			[],
			COMMENTPRESS_VERSION, // Version.
			'all' // Media.
		);

		// Add jQuery UI stylesheet - needed for resizable columns.
		wp_enqueue_style(
			'cp_jquery_ui_base',
			plugins_url( 'includes/core/assets/css/jquery.ui.css', COMMENTPRESS_PLUGIN_FILE ),
			false,
			COMMENTPRESS_VERSION, // Version.
			'all' // Media.
		);

		// -------------------------------------------------------------------------
		// Overrides for styles - for child themes, dequeue these and add you own.
		// -------------------------------------------------------------------------

		// Add Google Webfont "Lato".
		wp_enqueue_style(
			'cp_webfont_css',
			set_url_scheme( 'https://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic' ),
			[ 'cp_layout_css' ],
			COMMENTPRESS_VERSION, // Version.
			'all' // Media.
		);

		// Add colours css.
		wp_enqueue_style(
			'cp_colours_css',
			get_template_directory_uri() . '/assets/css/colours-01' . $min . '.css',
			[ 'cp_webfont_css' ],
			COMMENTPRESS_VERSION, // Version.
			'all' // Media.
		);

		// Use dashicons.
		wp_enqueue_style( 'dashicons' );

		// -------------------------------------------------------------------------
		// Javascripts.
		// -------------------------------------------------------------------------

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return;
		}

		/*
		 * Add jQuery Cookie plugin.
		 *
		 * Renamed to jquery.biscuit.js because some hosts don't like 'cookie' in the filename.
		 */
		wp_enqueue_script(
			'jquery_cookie',
			plugins_url( 'includes/core/assets/js/jquery.biscuit.js', COMMENTPRESS_PLUGIN_FILE ),
			[ 'jquery_commentpress' ],
			COMMENTPRESS_VERSION, // Version.
			false // In footer.
		);

		// Enqueue theme Javascript.
		wp_enqueue_script(
			'cp_common_js',
			get_template_directory_uri() . '/assets/js/screen' . $min . '.js',
			[ 'jquery_cookie' ],
			COMMENTPRESS_VERSION, // Version.
			false
		);

		// Always dequeue WordPress Comment Form script if present.
		wp_dequeue_script( 'comment-reply' );

		// Skip when on a BuddyPress Special Page.
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
					'submit'     => __( 'Update Comment', 'commentpress-core' ),
					'title'      => __( 'Leave a comment', 'commentpress-core' ),
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
				get_template_directory_uri() . '/assets/js/all-comments.js',
				[ 'cp_form' ], // Dependencies.
				COMMENTPRESS_VERSION, // Version.
				false
			);

		}

	}

endif;

// Add a filter for the above, very late so it (hopefully) is last in the queue.
add_action( 'wp_enqueue_scripts', 'commentpress_enqueue_scripts_and_styles', 100 );



if ( ! function_exists( 'commentpress_enqueue_print_styles' ) ) :

	/**
	 * Add CommentPress Core print stylesheet.
	 *
	 * @since 3.0
	 */
	function commentpress_enqueue_print_styles() {

		// Check for minification.
		$min = commentpress_minified();

		// Add print CSS.
		wp_enqueue_style(
			'cp_print_css',
			get_template_directory_uri() . '/assets/css/print' . $min . '.css',
			[ 'cp_layout_css' ],
			COMMENTPRESS_VERSION, // Version.
			'print'
		);

	}

endif;

// Add a filter for the above, very late so it (hopefully) is last in the queue.
add_action( 'wp_enqueue_scripts', 'commentpress_enqueue_print_styles', 101 );



if ( ! function_exists( 'commentpress_buddypress_support' ) ) :

	/**
	 * Enable support for BuddyPress.
	 *
	 * @since 3.3
	 */
	function commentpress_buddypress_support() {

		// Add filter for Activity class.
		add_filter( 'bp_get_activity_css_class', 'commentpress_bp_activity_css_class' );

		// Add filter for Blogs class.
		add_filter( 'bp_get_blog_class', 'commentpress_bp_blog_css_class' );

		// Add filter for Groups class.
		add_filter( 'bp_get_group_class', 'commentpress_bp_group_css_class' );

	}

endif;

// Add an action for the above.
add_action( 'bp_setup_globals', 'commentpress_buddypress_support' );



if ( ! function_exists( 'commentpress_header' ) ) :

	/**
	 * Custom header.
	 *
	 * @since 3.0
	 */
	function commentpress_header() {

		// Init with same colour as theme stylesheets and default in class-core-database.php.
		$bg_colour = '2c2622';

		// Override if we have the plugin enabled.
		$core = commentpress_core();
		if ( ! empty( $core ) ) {
			$bg_colour = $core->theme->header_bg_color_get();
		}

		/**
		 * Filters the default header bgcolor.
		 *
		 * @since 3.0
		 *
		 * @param str $bg_color The default header bgcolor.
		 */
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

		#book_header {
			background-color: #' . $bg_colour . ';
			' . $bg_image . '
			-webkit-background-size: cover;
			-moz-background-size: cover;
			-o-background-size: cover;
			background-size: cover;
			background-repeat: no-repeat;
			background-position: 50%;
		}

		#title h1, #title h1 a {
			' . $css . '
		}

		#book_header #tagline {
			' . $css . '
		}

		</style>
		';

	}

endif;



if ( ! function_exists( 'commentpress_get_all_comments_content' ) ) :

	/**
	 * All-comments Page display function.
	 *
	 * @since 3.0
	 *
	 * @param str $page_or_post Retrieve either 'page' or 'post' Comments.
	 * @return str $html The Comments markup.
	 */
	function commentpress_get_all_comments_content( $page_or_post = 'page' ) {

		// Declare access to globals.
		global $cp_comment_output;

		// Init output.
		$html = '';

		// Get all approved Comments.
		$all_comments = get_comments( [
			'status'    => 'approve',
			'orderby'   => 'comment_post_ID,comment_date',
			'order'     => 'ASC',
			'post_type' => $page_or_post,
		] );

		// Kick out if none.
		if ( count( $all_comments ) == 0 ) {
			return $html;
		}

		// Build list of Posts to which they are attached.
		$posts_with          = [];
		$post_comment_counts = [];
		foreach ( $all_comments as $comment ) {

			// Add to Posts with Comments array.
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
			'orderby'   => 'comment_count',
			'order'     => 'DESC',
			'post_type' => $page_or_post,
			'include'   => $posts_with,
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
				_n(
					'<span class="cp_comment_count">%d</span> comment',
					'<span class="cp_comment_count">%d</span> comments',
					$post_comment_counts[ $post->ID ],
					'commentpress-core'
				),
				$post_comment_counts[ $post->ID ]
			);

			// Show it.
			$html .= '<h3>' .
				esc_html( $post->post_title ) . ' <span>(' . $comment_count_text . ')</span>' .
			'</h3>' . "\n\n";

			// Open Comments div.
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

		// Allow oEmbed in Comments.
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
		$page_or_post = $core->nav->setting_post_type_get();

		/**
		 * Filters the title of the "All Comments" Page when TOC contains Posts.
		 *
		 * @since 3.0
		 *
		 * @param str The default title of the "All Comments" Page.
		 */
		$blog_title = apply_filters( 'cp_page_all_comments_blog_title', __( 'Comments on the Blog', 'commentpress-core' ) );

		/**
		 * Filters the title of the "All Comments" Page when TOC contains Pages.
		 *
		 * @since 3.0
		 *
		 * @param str The default title of the "All Comments" Page.
		 */
		$book_title = apply_filters( 'cp_page_all_comments_book_title', __( 'Comments on the Pages', 'commentpress-core' ) );

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

		// Get data for other Page Type.
		$other_type = ( $page_or_post == 'page' ) ? 'post' : 'page';

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
		$link = str_replace( '<a ', '<a id="' . $id . '" class="button" ', $link );

		// --<
		return $link;

	}

endif;

// Add callbacks for WordPress admin links.
add_filter( 'loginout', 'commentpress_add_link_css' );
add_filter( 'loginout', 'commentpress_add_loginout_id' );
add_filter( 'register', 'commentpress_add_loginout_id' );



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
		'name'          => __( 'CommentPress Footer', 'commentpress-core' ),
		'id'            => 'cp-license-8',
		'description'   => __( 'An optional widget area in the footer of a CommentPress theme', 'commentpress-core' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget'  => '</div>',
		'before_title'  => '<h3 class="widget-title">',
		'after_title'   => '</h3>',
	] );

}

// Add callback for the above.
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

// Add callback for the above.
add_filter( 'commentpress_default_sidebar', 'commentpress_default_theme_default_sidebar' );
