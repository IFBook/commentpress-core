<?php /*
================================================================================
CommentPress Modern Theme Functions
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

--------------------------------------------------------------------------------
*/





/**
 * Set the content width based on the theme's design and stylesheet.
 * This seems to be a Wordpress requirement - though rather dumb in the
 * context of our theme, which has a percentage-based default width.
 * I have arbitrarily set it to the default content-width when viewing
 * on a 1280px-wide screen.
 */
if ( !isset( $content_width ) ) { $content_width = 525; }





if ( ! function_exists( 'commentpress_setup' ) ):
/** 
 * @description: get an ID for the body tag
 * @todo: 
 *
 */
function commentpress_setup( 
	
) { //-->

	/** 
	 * Make CommentPress Modern Theme available for translation.
	 * Translations can be added to the /assets/languages/ directory.
	 */
	
	/*
	// we no longer use this: instead, the plugin's textdomain is used
	load_theme_textdomain( 
	
		'commentpress-modern', 
		get_template_directory() . '/assets/languages' 
		
	);
	*/

	// add_custom_background function is deprecated in WP 3.4+
	global $wp_version;
	if ( version_compare( $wp_version, '3.4', '>=' ) ) {
		
		// -------------------------
		// TO DO: test 3.4 features
		// -------------------------
	
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

	
	
	// if we have the plugin enabled...
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

}
endif; // commentpress_setup

// add after theme setup hook
add_action( 'after_setup_theme', 'commentpress_setup' );






if ( ! function_exists( 'commentpress_bp_enqueue_styles' ) ):
/** 
 * @description: add buddypress front-end styles
 * @todo:
 *
 */
function commentpress_bp_enqueue_styles() {

	// kick out on admin
	if ( is_admin() ) { return; }

	// init
	$dev = '';
	
	// check for dev
	if ( defined( 'SCRIPT_DEBUG' ) AND SCRIPT_DEBUG === true ) {
		$dev = '.dev';
	}
	
	// add our own BuddyPress css
	wp_enqueue_style( 
		
		'cp_buddypress_css', 
		get_template_directory_uri() . '/assets/css/bp-overrides'.$dev.'.css',
		array( 'cp_screen_css' ),
		COMMENTPRESS_VERSION, // version
		'all' // media
		
	);
	
}
endif; // commentpress_bp_enqueue_styles






if ( ! function_exists( 'commentpress_bp_enqueue_scripts' ) ):
/** 
 * @description: add buddypress front-end scripts
 * @todo:
 *
 */
function commentpress_bp_enqueue_scripts() {

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
	
	// kick out on admin
	if ( is_admin() ) { return; }

	// enqueue buddypress js
	wp_enqueue_script( 
	
		'cp_buddypress_js', 
		//BP_PLUGIN_URL . '/bp-templates/bp-legacy/js/buddypress.js',
		BP_PLUGIN_URL . '/bp-themes/bp-default/_inc/global.js',
		array( 'jquery' ),
		COMMENTPRESS_VERSION // version

	);
	
	// add translation: this needs to be checked against BP_Legacy::enqueue_scripts
	// from time to time to make sure it's up-to-date
	$params = array(
		'my_favs'           => __( 'My Favorites', 'commentpress-core' ),
		'accepted'          => __( 'Accepted', 'commentpress-core' ),
		'rejected'          => __( 'Rejected', 'commentpress-core' ),
		'show_all_comments' => __( 'Show all comments for this thread', 'commentpress-core' ),
		'show_x_comments'   => __( 'Show all %d comments', 'commentpress-core' ),
		'show_all'          => __( 'Show all', 'commentpress-core' ),
		'comments'          => __( 'comments', 'commentpress-core' ),
		'close'             => __( 'Close', 'commentpress-core' ),
		'view'              => __( 'View', 'commentpress-core' ),
		'mark_as_fav'	    => __( 'Favorite', 'commentpress-core' ),
		'remove_fav'	    => __( 'Remove Favorite', 'commentpress-core' ),
		'unsaved_changes'   => __( 'Your profile has unsaved changes. If you leave the page, the changes will be lost.', 'commentpress-core' ),
	);
	
	// localise
	wp_localize_script( 'cp_buddypress_js', 'BP_DTheme', $params );

}
endif; // commentpress_bp_enqueue_scripts






/** 
 * @description: enable compatibility with BuddyPress
 *
 */
function commentpress_bp_theme_compatibility() {

	// BP 1.7 auto-compatibility - see commentpress_enqueue_theme_styles()
	
	// if we're using BP Template Pack
	if ( function_exists( 'bp_tpack_theme_setup' ) ) {
	
		// assume that BP Template Pack has been activated and set up correctly
	
	} else {
	
		// use function cloned from BP Template Pack
		commentpress_bp_theme_support();
		
	}
	
}






/**
 * @description: sets up WordPress theme for BuddyPress support - cloned from BP Template Pack
 *
 */
function commentpress_bp_theme_support() {

	// load the default BuddyPress AJAX functions if it isn't already included
	if ( ! function_exists( 'bp_dtheme_register_actions' ) ) {
		require_once( BP_PLUGIN_DIR . '/bp-themes/bp-default/_inc/ajax.php' );
	}
	
	// call after_setup_theme function directly otherwise it doesn't run: this is 
	// because we're hooking into bp_after_setup_theme which runs with priority 100
	bp_dtheme_register_actions();

	// tell BP that we support it
	add_theme_support( 'buddypress' );
	
	// bail if admin
	if ( is_admin() ) { return; }
	
	// register buttons for the relevant component templates
	
	// friends button
	if ( bp_is_active( 'friends' ) )
		add_action( 'bp_member_header_actions',    'bp_add_friend_button' );

	// activity button
	if ( bp_is_active( 'activity' ) )
		add_action( 'bp_member_header_actions',    'bp_send_public_message_button' );

	// messages button
	if ( bp_is_active( 'messages' ) )
		add_action( 'bp_member_header_actions',    'bp_send_private_message_button' );

	// group buttons
	if ( bp_is_active( 'groups' ) ) {
		add_action( 'bp_group_header_actions',     'bp_group_join_button' );
		add_action( 'bp_group_header_actions',     'bp_group_new_topic_button' );
		add_action( 'bp_directory_groups_actions', 'bp_group_join_button' );
	}

	// blog button
	if ( bp_is_active( 'blogs' ) ) {
		add_action( 'bp_directory_blogs_actions',  'bp_blogs_visit_blog_button' );
	}
	
} // commentpress_bp_theme_support






/** 
 * @description: update BuddyPress activity CSS class with groupblog type
 *
 */
function commentpress_bp_activity_css_class( $existing_class ) {
	
	// $activities_template->activity->component . ' ' . $activities_template->activity->type . $class
	//print_r( array( 'existing_class' => $existing_class ) ); die();
	
	// init group blog type
	$groupblogtype = '';

	// get current item
	global $activities_template;
	$current_activity = $activities_template->activity;
	//print_r( array( 'a' => $current_activity ) ); die();

	// for group activity...
	if ( $current_activity->component == 'groups' ) {

		// get group blogtype
		$groupblogtype = groups_get_groupmeta( $current_activity->item_id, 'groupblogtype' );
	
		// add space before if we have it
		if ( $groupblogtype ) { $groupblogtype = ' '.$groupblogtype; }
	
	}
	
	// --<
	return $existing_class . $groupblogtype;

}






/** 
 * @description: enable support for BuddyPress
 * @todo:
 *
 */
function commentpress_buddypress_support() {
	
	//print_r( 'commentpress_buddypress_support' );
	
	// add an action to enable compatibility with BuddyPress
	add_action( 'after_setup_theme', 'commentpress_bp_theme_compatibility', 101 );
	
	// include bp-overrides when buddypress is active
	add_action( 'wp_enqueue_scripts', 'commentpress_bp_enqueue_styles', 994 );
	add_action( 'wp_enqueue_scripts', 'commentpress_bp_enqueue_scripts', 994 );
	
	// add filter for activity class
	add_filter( 'bp_get_activity_css_class', 'commentpress_bp_activity_css_class' );
	
}

// add an action for the above (BP hooks this to after_setup_theme with priority 100)
add_action( 'bp_after_setup_theme', 'commentpress_buddypress_support' );






if ( ! function_exists( 'commentpress_enqueue_scripts_and_styles' ) ):
/** 
 * @description: add front-end print styles
 * @todo:
 *
 */
function commentpress_enqueue_scripts_and_styles() {

	// init
	$dev = '';
	
	// check for dev
	if ( defined( 'SCRIPT_DEBUG' ) AND SCRIPT_DEBUG === true ) {
		$dev = '.dev';
	}
	
	// -------------------------------------------------------------------------
	// Stylesheets
	// -------------------------------------------------------------------------
	
	// register screen styles
	wp_register_style(
	
		'cp_screen_css', // unique id
		get_template_directory_uri() . '/assets/css/screen'.$dev.'.css', // src
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
		'http://fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic',
		array( 'cp_screen_css' ),
		COMMENTPRESS_VERSION, // version
		'all' // media
		
	);
	
	// add colours css
	wp_enqueue_style( 
		
		'cp_colours_css', 
		get_template_directory_uri() . '/assets/css/colours-01'.$dev.'.css',
		array( 'cp_webfont_lato_css' ),
		COMMENTPRESS_VERSION, // version
		'all' // media
		
	);
	
	// test for a function in BP 1.7+
	if ( function_exists( 'bp_is_network_activated' ) ) {
	
		// try to remove the legacy stylesheet that BP 1.7 tries to insert...
		// do it here because, it seems, doing so on bp_setup_globals is too early
		wp_dequeue_style( 'bp-legacy-css' );
		
	}
	
	// -------------------------------------------------------------------------
	// Javascripts
	// -------------------------------------------------------------------------
	
	// enqueue common js
	wp_enqueue_script(
	
		'cp_common_js', 
		get_template_directory_uri() . '/assets/js/screen'.$dev.'.js', 
		array( 'jquery_commentpress' ), // deps
		COMMENTPRESS_VERSION // version
	
	);
	
	// access plugin
	global $commentpress_core;

	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {
		
		// test for buddypress special page
		if ( $commentpress_core->is_buddypress() AND $commentpress_core->is_buddypress_special_page() ) {
		
			// skip custom addComment
		
		} else {
			
			// enqueue form js
			wp_enqueue_script(
			
				'cp_form', 
				get_template_directory_uri() . '/assets/js/cp_js_form'.$dev.'.js', 
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
		
		// get vars
		$vars = $commentpress_core->db->get_javascript_vars();
		
		// localise with wp function
		wp_localize_script( 'cp_common_js', 'CommentpressSettings', $vars );
		
	}
	
}
endif; // commentpress_enqueue_scripts_and_styles

// add a filter for the above, very late so it (hopefully) is last in the queue
add_action( 'wp_enqueue_scripts', 'commentpress_enqueue_scripts_and_styles', 995 );






if ( ! function_exists( 'commentpress_enqueue_print_styles' ) ):
/** 
 * @description: add front-end print styles
 * @todo:
 *
 */
function commentpress_enqueue_print_styles() {

	// init
	$dev = '';
	
	// check for dev
	if ( defined( 'SCRIPT_DEBUG' ) AND SCRIPT_DEBUG === true ) {
		$dev = '.dev';
	}
	
	// -------------------------------------------------------------------------
	// Print stylesheet included last
	// -------------------------------------------------------------------------
	
	// add print css
	wp_enqueue_style( 
		
		'cp_print_css', 
		get_template_directory_uri() . '/assets/css/print'.$dev.'.css',
		array( 'cp_screen_css' ),
		COMMENTPRESS_VERSION, // version
		'print'
		
	);
	
}
endif; // commentpress_enqueue_print_styles

// add a filter for the above, very late so it (hopefully) is last in the queue
add_action( 'wp_enqueue_scripts', 'commentpress_enqueue_print_styles', 999 );






if ( ! function_exists( 'commentpress_header' ) ):
/** 
 * @description: custom background colour
 * @see: _custom_background_cb() 
 *
 */
function commentpress_background( 
	
) { //-->

	// $color is the saved custom color.
	// A default has to be specified in style.css. It will not be printed here.
	$color = get_theme_mod( 'background_color' );
	
	// bail if we don't have one
	if ( ! $color ) return;

	$style = $color ? "background-color: #$color; !important" : '';

	echo '
<style type="text/css" id="custom-background-css">

	html,
	body.custom-background,
	#toc_sidebar .sidebar_minimiser ul#toc_list,
	.sidebar_contents_wrapper,
	#footer_inner
	{ 
		'.trim( $style ).'
	}
	
</style>
	';
	
}
endif; // commentpress_background



if ( ! function_exists( 'commentpress_header' ) ):
/** 
 * @description: custom header
 * @todo: 
 *
 */
function commentpress_header( 
	
) { //-->

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
	
		$bg_image = 'background-image: url("'.$header_image.'");';
	
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
				$css = 'color: #'.HEADER_TEXTCOLOR.';';
			} else {
				
				// use the custom one. I know this amounts to the same thing.
				$css = 'color: #'.$text_color.';';
			}
			
		}

	} else {
		
		// use previous logic
		if ( $text_color == 'blank' OR $text_color == '' ) {
			$css = 'text-indent: -9999px;';
		} else {
			$css = 'color: #'.$text_color.';';
		}
		
	}
	
	// build inline styles
	echo '
<style type="text/css">

#header
{
	background-color: #'.$bg_colour.';
	'.$bg_image.'
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
	'.$css.'
}

#header #tagline
{
	'.$css.'
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



if ( ! function_exists( 'commentpress_admin_header' ) ):
/** 
 * @description: custom admin header
 * @todo: 
 *
 */
function commentpress_admin_header( 
	
) { //-->

	// init (same as bg in layout.css and default in class_commentpress_db.php)
	$colour = '2c2622';

	// access plugin
	global $commentpress_core;

	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {
	
		// override
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
	background-color: #'.$colour.';
}

#headimg #name,
#headimg #desc
{
	margin-left: 20px; 
	font-family: Helvetica, Arial, sans-serif;
	font-weight: normal;
	line-height: 1;
	color: #'.get_header_textcolor().';
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






if ( ! function_exists( 'commentpress_customize_register' ) ) {
/**
 * Implements CommentPress Default Theme options into Theme Customizer
 *
 * @param $wp_customize Theme Customizer object
 * @return void
 *
 */
function commentpress_customize_register( 

	$wp_customize 

) { //-->

	// access plugin
	global $commentpress_core;
	
	// kick out if buddypress groupblog...
	if ( is_object( $commentpress_core ) AND $commentpress_core->is_groupblog() ) return;
	
	
	
	// add customizer section title
	$wp_customize->add_section( 'cp_inline_header_image', array(
		'title' => __( 'Site Logo', 'commentpress-core' ),
		'priority' => 35,
	) );
	
	
	
	// add image
	$wp_customize->add_setting( 'commentpress_theme_settings[cp_inline_header_image]', array(
		 'default' => '',
		 'capability' => 'edit_theme_options',
		 'type' => 'option'
	));
	 
	$wp_customize->add_control( new WP_Customize_Image_Control(
		$wp_customize, 'cp_inline_header_image', array(
		'label' => __( 'Logo Image', 'commentpress-core' ),
		'section' => 'cp_inline_header_image',
		'settings' => 'commentpress_theme_settings[cp_inline_header_image]',
		'priority'	=>	1
	)));
	
	
	
	// add padding
	$wp_customize->add_setting( 'commentpress_theme_settings[cp_inline_header_padding]', array(
		 'default' => '',
		 'capability' => 'edit_theme_options',
		 'type' => 'option'
	));
	 
	$wp_customize->add_control( 'commentpress_theme_settings[cp_inline_header_padding]', array(
		'label' => __( 'Top padding in px', 'commentpress-core' ),
		'section' => 'cp_inline_header_image',
		'type' => 'text'
	) );

}
}
add_action( 'customize_register', 'commentpress_customize_register' );





if ( ! function_exists( 'commentpress_admin_menu' ) ) {
/** 
 * @description: adds more prominent menu item
 * @todo:
 *
 */
function commentpress_admin_menu() {

	// Only add for WP3.4+
	global $wp_version;
	if ( version_compare( $wp_version, '3.4', '>=' ) ) {

		// add the Customize link to the admin menu
		add_theme_page( 'Customize', 'Customize', 'edit_theme_options', 'customize.php' );
		
	}
	
}
}
add_action( 'admin_menu', 'commentpress_admin_menu' );






if ( ! function_exists( 'commentpress_fix_bp_core_avatar_url' ) ):
/** 
 * @description: filter to fix broken group avatar images in BP 1.7
 * @todo: 
 *
 */
function commentpress_fix_bp_core_avatar_url( 

	$url

) { //-->
	
	// if in multisite and on non-root site
	if ( is_multisite() && !bp_is_root_blog() ) {
		
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
 * @description: function that sets a header foreground image (a logo, for example)
 * @todo: inform users that header images are using a different method
 *
 */
function commentpress_get_header_image( 
	
) { //-->

	// access plugin
	global $commentpress_core;

	// test for groupblog
	if ( is_object( $commentpress_core ) AND $commentpress_core->is_groupblog() ) {
	
		// get group ID
		$group_id = get_groupblog_group_id( get_current_blog_id() );
	
		// get group avatar
		$avatar_options = array ( 
			'item_id' => $group_id, 
			'object' => 'group', 
			'type' => 'full', 
			'alt' => 'Group avatar', 
			'class' => 'cp_logo_image cp_group_avatar', 
			'width' => 48, 
			'height' => 48, 
			'html' => true 
		);
		
		//print_r( $avatar_options ); die();
        
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
	// implement compatibility with WordPress Theme Customizer
	// -------------------------------------------------------------------------

	// get the new options
	$options = get_option( 'commentpress_theme_settings' );
	//print_r( $options ); die();
	
	// test for our new theme customizer option
	if ( isset( $options['cp_inline_header_image'] ) AND !empty( $options['cp_inline_header_image'] ) ) {
	
		// init top padding
		$style = '';
		
		// test for top padding	
		if ( isset( $options['cp_inline_header_padding'] ) AND !empty( $options['cp_inline_header_padding'] ) ) {
		
			// override
			$style = ' style="padding-top: '.$options['cp_inline_header_padding'].'px"';
			
		}		
		
		// show the uploaded image
		echo apply_filters(
			'commentpress_header_image',
			'<img src="'.$options['cp_inline_header_image'].'" class="cp_logo_image"'.$style.' alt="'.__( 'Logo', 'commentpress-core' ).'" />'
		);
		
		// --<
		return;
	
	}
	
	
	
	// -------------------------------------------------------------------------
	// our fallback is to go with the legacy method that some people might still be using
	// -------------------------------------------------------------------------

	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) AND $commentpress_core->db->option_get( 'cp_toc_page' ) ) {
	
		// set defaults
		$args = array(
		
			'post_type' => 'attachment',
			'numberposts' => 1,
			'post_status' => null,
			'post_parent' => $commentpress_core->db->option_get( 'cp_toc_page' )
			
		);
		
		// get them...
		$attachments = get_posts( $args );
		
		// well?
		if ( $attachments ) {
		
			// we only want the first
			$attachment = $attachments[0];
		
		}
		
		// if we have an image
		if ( isset( $attachment ) ) { 
			
			// show it
			echo wp_get_attachment_image( $attachment->ID, 'full' );
						
		}
		
	}
	
}
endif; // commentpress_get_header_image






if ( ! function_exists( 'commentpress_get_body_id' ) ):
/** 
 * @description: get an ID for the body tag
 * @todo: 
 *
 */
function commentpress_get_body_id( 
	
) { //-->

	// init
	$_body_id = '';
	
	// is this multisite?
	if ( is_multisite() ) {
	
		// is this the main blog?
		if ( is_main_site() ) {
		
			// set main blog id
			$_body_id = ' id="main_blog"';
		
		}
		
	}
	
	// --<
	return $_body_id;
	
}
endif; // commentpress_get_body_id






if ( ! function_exists( 'commentpress_get_body_classes' ) ):
/** 
 * @description: get classes for the body tag
 * @todo: 
 *
 */
function commentpress_get_body_classes(

	$raw = false
	
) { //-->

	// init
	$_body_classes = '';
	
	// access post and plugin
	global $post, $commentpress_core;



	// set default sidebar
	$sidebar_flag = 'toc';
	
	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {
	
		// get sidebar
		$sidebar_flag = $commentpress_core->get_default_sidebar();
		
	}
	
	// set class by sidebar
	$sidebar_class = 'cp_sidebar_'.$sidebar_flag;
	


	// init commentable class
	$commentable = '';
	
	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {
		
		// set class
		$commentable = ( $commentpress_core->is_commentable() ) ? ' commentable' : ' not_commentable';
		
	}
	
	
	
	// init layout class
	$layout_class = '';
	
	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {
	
		// is this the title page?
		if ( 
			
			// be more defensive
			is_object( $post )
			AND isset( $post->ID )
			AND $post->ID == $commentpress_core->db->option_get( 'cp_welcome_page' ) 
			
		) {
		
			// init layout
			$layout = '';
			
			// set key
			$key = '_cp_page_layout';
			
			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) != '' ) {
			
				// get it
				$layout = get_post_meta( $post->ID, $key, true );
				
			}
			
			// if wide layout...
			if ( $layout == 'wide' ) {
			
				// set layout class
				$layout_class = ' full_width';
				
			}
			
		}
		
	}


	
	// set default page type
	$page_type = '';
	
	// if blog post...
	if ( is_single() ) {
	
		// add blog post class
		$page_type = ' blog_post';
		
	}
	
	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {
	
		// is it a BP special page?
		if ( $commentpress_core->is_buddypress_special_page() ) {
	
			// add buddypress page class
			$page_type = ' buddypress_page';
		
		}
		
		// is it a CP special page?
		if ( $commentpress_core->db->is_special_page() ) {
	
			// add buddypress page class
			$page_type = ' commentpress_page';
		
		}
		
	}
	

	
	// set default type
	$groupblog_type = ' not-groupblog';
	
	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {
	
		// if it's a groupblog
		if ( $commentpress_core->is_groupblog() ) {
			$groupblog_type = ' is-groupblog';
		}
		
	}
	


	// set default type
	$blog_type = '';
	
	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {
	
		// get type
		$_type = $commentpress_core->db->option_get( 'cp_blog_type' );
		//print_r( $_type ); die();
		
		// get workflow
		$_workflow = $commentpress_core->db->option_get( 'cp_blog_workflow' );
		
		// allow plugins to override the blog type - for example if workflow is enabled, 
		// it might be a new blog type as far as buddypress is concerned
		$_blog_type = apply_filters( 'cp_get_group_meta_for_blog_type', $_type, $_workflow );

		// if it's not the main site, add class
		if ( is_multisite() AND !is_main_site() ) {
			$blog_type = ' blogtype-'.intval( $_blog_type );
		}
		
	}
	


	// init TinyMCE class
	$tinymce_version = ' tinymce-3';
	
	// access WP version
	global $wp_version;

	// if greater than 3.8
	if ( version_compare( $wp_version, '3.8.9999', '>' ) ) {
	
		// override TinyMCE class
		$tinymce_version = ' tinymce-4';
		
	}
	
	
	
	// construct attribute
	$_body_classes = $sidebar_class.$commentable.$layout_class.$page_type.$groupblog_type.$blog_type.$tinymce_version;

	// if we want them wrapped, do so
	if ( !$raw ) {
		
		// preserve backwards compat for older child themes
		$_body_classes = ' class="'.$_body_classes.'"';

	}


	// --<
	return $_body_classes;
	
}
endif; // commentpress_get_body_classes






if ( ! function_exists( 'commentpress_site_title' ) ):
/** 
 * @description: disable more link jump - from: http://codex.wordpress.org/Customizing_the_Read_More
 * @todo:
 *
 */
function commentpress_site_title( $sep = '', $echo = true ){

	// is this multisite?
	if ( is_multisite() ) {
	
		// if we're on a sub-blog
		if ( !is_main_site() ) {
			
			global $current_site;
			
			// print?
			if( $echo ) {
			
				// add site name
				echo ' '.trim($sep).' '.$current_site->site_name;
				
			} else {
			
				// add site name
				return ' '.trim($sep).' '.$current_site->site_name;
				
			}
			
		}
		
	}
	
}
endif; // commentpress_site_title






if ( ! function_exists( 'commentpress_remove_more_jump_link' ) ):
/** 
 * @description: disable more link jump - from: http://codex.wordpress.org/Customizing_the_Read_More
 * @todo:
 *
 */
function commentpress_remove_more_jump_link( $link ) { 

	$offset = strpos($link, '#more-');
	
	if ($offset) {
		$end = strpos($link, '"',$offset);
	}
	
	if ($end) {
		$link = substr_replace($link, '', $offset, $end-$offset);
	}
	
	// --<
	return $link;
	
}
endif; // commentpress_remove_more_jump_link

// add a filter for the above
add_filter( 'the_content_more_link', 'commentpress_remove_more_jump_link' );






if ( ! function_exists( 'commentpress_page_navigation' ) ):
/** 
 * @description: builds a list of previous and next pages, optionally with comments
 * @todo: 
 *
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
			$img = '<img src="'.get_template_directory_uri().'/assets/images/next.png" />';	

		}
		
		// define list item 
		$next_page_html = $before_next.
						  $img.
						  '<a href="'.get_permalink( $next_page->ID ).'" class="next_page" title="'.$title.'">'.$title.'</a>'.
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
	
		// if we wanted pages with comments...
		if ( $with_comments ) {
		
			// set title
			$title = __( 'Previous page with comments', 'commentpress-core' );
			$img = '<img src="'.get_template_directory_uri().'/assets/images/prev.png" />';
		
		}
		
		// define list item 
		$prev_page_html = $before_prev.
						  $img.
						  '<a href="'.get_permalink( $prev_page->ID ).'" class="previous_page" title="'.$title.'">'.$title.'</a>'.
						  $after_prev;
		
	}
	
	
	
	// init return
	$nav_list = '';
	
	// did we get either?
	if ( $next_page_html != '' OR $prev_page_html != '' ) {
	
		// construct nav list items
		$nav_list = $prev_page_html."\n".$next_page_html."\n";

	}
	
	
	
	// --<
	return $nav_list;

}
endif; // commentpress_page_navigation






if ( ! function_exists( 'commentpress_page_title' ) ):
/** 
 * @description: builds a list of previous and next pages, optionally with comments
 * @todo: 
 *
 */
function commentpress_page_title( $with_comments = false ) {

	// declare access to globals
	global $commentpress_core, $post;
	
	
	
	// init
	$_title = '';
	$_sep = ' &#8594; ';
	
	
	//$_title .= get_bloginfo('name');
 
	if ( is_page() OR is_single() OR is_category() ) {

		if (is_page()) {

			$ancestors = get_post_ancestors($post);
 
			if ($ancestors) {
				$ancestors = array_reverse($ancestors);
 				
 				$_crumb = array();
 				
				foreach ($ancestors as $crumb) {
					$_crumb[] = get_the_title($crumb);
				}
				
				$_title .= implode( $_sep, $_crumb ).$_sep;
			}

		}
 
		if (is_single()) {
			//$category = get_the_category();
			//$_title .= $_sep.$category[0]->cat_name;
		}
 
		if (is_category()) {
			$category = get_the_category();
			$_title .= $category[0]->cat_name.$_sep;
		}
 
		// Current page
		if (is_page() OR is_single()) {
			$_title .= get_the_title();
		}

	}

	

	// --<
	return $_title;

}
endif; // commentpress_page_title





if ( ! function_exists( 'commentpress_has_page_children' ) ):
/** 
 * @description: query whether a given page has children
 * @todo: 
 *
 */
function commentpress_has_page_children( 

	$page_obj
	
) { //-->

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
 * @description: retrieve comment children
 * @todo: 
 *
 */
function commentpress_get_children( 

	$comment,
	$page_or_post
	
) { //-->

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
 * @description: generate comments recursively
 * @todo: 
 *
 */
function commentpress_get_comments( 

	$comments,
	$page_or_post
	
) { //-->

	// declare access to globals
	global $cp_comment_output;



	// do we have any comments?
	if( count( $comments ) > 0 ) {
	
		// open ul
		$cp_comment_output .= '<ul class="item_ul">'."\n\n";

		// produce a checkbox for each
		foreach( $comments as $comment ) {
		
			// open li
			$cp_comment_output .= '<li class="item_li">'."\n\n";
	
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
			$cp_comment_output .= '</li>'."\n\n";

		}

		// close ul
		$cp_comment_output .= '</ul>'."\n\n";

	}

}
endif; // commentpress_get_comments







if ( ! function_exists( 'commentpress_get_user_link' ) ):
/** 
 * @description: get user link in vanilla WP scenarios
 * @todo: 
 *
 */
function commentpress_get_user_link( 

	&$user
	
) { //-->

	/**
	 * In default single install mode, just link to their URL, unless 
	 * they are	an author, in which case we link to their author page.
	 *
	 * In multisite, the same.
	 *
	 * When BuddyPress is enabled, always link to their profile
	 */
	
	// kick out if not a user
	if ( !is_object( $user ) ) { return false; }
	
	// we're through: the user is on the system
	global $commentpress_core;
	
	// if buddypress...
	if ( is_object( $commentpress_core ) AND $commentpress_core->is_buddypress() ) {
	
		// buddypress link ($no_anchor = null, $just_link = true)
		$url = bp_core_get_userlink( $user->ID, null, true );
		
	} else {
	
		// get standard WP author url
	
		// get author url
		$url = get_author_posts_url( $user->ID );
		//print_r( $url ); die();
		
		// WP sometimes leaves 'http://' or 'https://' in the field
		if (  $url == 'http://'  OR $url == 'https://' ) {
		
			// clear
			$url = '';
		
		}
		
	}
	
	
	
	// --<
	return $url;
	 
}
endif; // commentpress_get_user_link







if ( ! function_exists( 'commentpress_echo_post_meta' ) ):
/** 
 * @description: show user(s) in the loop
 * @todo: 
 *
 */
function commentpress_echo_post_meta() {

	// compat with Co-Authors Plus
	if ( function_exists( 'get_coauthors' ) ) {
	
		// get multiple authors
		$authors = get_coauthors();
		//print_r( $authors ); die();
		
		// if we get some
		if ( !empty( $authors ) ) {
		
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
				if ( $n == ($author_count - 1) ) {
				
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
				if ( get_option('show_avatars') ) {
				
					// get avatar
					echo get_avatar( $author->ID, $size='32' );
					
				}
					
			}
			
			?><cite class="fn"><?php echo $author_html; ?></cite>
			
			<p><a href="<?php the_permalink() ?>"><?php the_time('l, F jS, Y') ?></a></p>
			
			<?php
				
		}
	
	} else {
	
		// get avatar
		$author_id = get_the_author_meta( 'ID' );
		echo get_avatar( $author_id, $size='32' );
		
		?>
		
		<cite class="fn"><?php commentpress_echo_post_author( $author_id ) ?></cite>
		
		<p><a href="<?php the_permalink() ?>"><?php the_time('l, F jS, Y') ?></a></p>
		
		<?php 
	
	}
		
}
endif; // commentpress_echo_post_meta





if ( ! function_exists( 'commentpress_show_source_url' ) ):
/** 
 * @description: show source URL for print
 * @todo: 
 *
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
		$protocol = ( !empty( $_SERVER['HTTPS'] ) ) ? 'https' : 'http';
		
		// construct URL
		$url = $protocol.'://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		
		// echo
		echo $url;
		
	?></p><?php 

}
endif; // commentpress_show_source_url

// add after theme setup hook
add_action( 'wp_footer', 'commentpress_show_source_url' );





if ( ! function_exists( 'commentpress_echo_post_author' ) ):
/** 
 * @description: show username (with link) in the loop
 * @todo: 
 *
 */
function commentpress_echo_post_author( $author_id, $echo = true ) {

	// get author details
	$user = get_userdata( $author_id );
	
	// kick out if we don't have a user with that ID
	if ( !is_object( $user ) ) { return; }
	
	
	
	// access plugin
	global $commentpress_core, $post;

	// if we have the plugin enabled and it's BP
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
 * @description: format comment on custom CommentPress comments pages
 */
function commentpress_format_comment( $comment, $context = 'all' ) {

	// declare access to globals
	global $commentpress_core, $cp_comment_output;

	// enable Wordpress API on comment
	//$GLOBALS['comment'] = $comment;
	
	
	
	// construct link
	$_comment_link = get_comment_link( $comment->comment_ID );
	
	// construct anchor
	$_comment_anchor = '<a href="'.$_comment_link.'" title="'.esc_attr( __( 'See comment in context', 'commentpress-core' ) ).'">'.__( 'Comment', 'commentpress-core' ).'</a>';
	
	// construct date
	$_comment_date = date( 'F jS, Y', strtotime( $comment->comment_date ) );
	
	
	
	// if context is 'all comments'...
	if ( $context == 'all' ) {
	
		// get author
		if ( $comment->comment_author != '' ) {
		
			// was it a registered user?
			if ( $comment->user_id != '0' ) {
			
				// get user details
				$user = get_userdata( $comment->user_id );
				//print_r( $user->display_name ); die();
				
				// get user link
				$user_link = commentpress_get_user_link( $user );
				
				// did we get one?
				if ( $user_link != '' AND $user_link != 'http://' ) {
				
					// construct link to user url
					$_comment_author = '<a href="'.$user_link.'">'.$comment->comment_author.'</a>';
					
				} else {
				
					// just show author name
					$_comment_author = $comment->comment_author;
				
				}
				
			} else {
			
				// do we have an author URL?
				if ( $comment->comment_author_url != '' AND $comment->comment_author_url != 'http://' ) {
				
					// construct link to user url
					$_comment_author = '<a href="'.$comment->comment_author_url.'">'.$comment->comment_author.'</a>';
					
				} else {
				
					// define context
					$_comment_author = $comment->comment_author;
				
				}
				
			}
			
			
		} else { 
		
			// we don't have a name
			$_comment_author = __( 'Anonymous', 'commentpress-core' );
			
		}
	
		// construct comment header content
		$_comment_meta_content = sprintf(
			__( '%1$s by %2$s on %3$s', 'commentpress-core' ),
			$_comment_anchor,
			$_comment_author,
			$_comment_date
		);
		
		// wrap comment meta in a div
		$_comment_meta = '<div class="comment_meta">'.$_comment_meta_content.'</div>'."\n";
	
		// allow filtering by plugins
		$_comment_meta = apply_filters( 
			'commentpress_format_comment_all_meta', // filter name
			$_comment_meta, // built meta
			$comment,
			$_comment_anchor,
			$_comment_author,
			$_comment_date
		);
		
		
		
	// if context is 'by commenter'
	} elseif ( $context == 'by' ) {
	
		// construct link
		$_page_link = trailingslashit( get_permalink( $comment->comment_post_ID ) );
		
		// construct page anchor
		$_page_anchor = '<a href="'.$_page_link.'">'.get_the_title( $comment->comment_post_ID ).'</a>';
	
		// construct comment header content
		$_comment_meta_content = sprintf(
			__( '%1$s on %2$s on %3$s', 'commentpress-core' ),
			$_comment_anchor,
			$_page_anchor,
			$_comment_date
		);

		// wrap comment meta in a div
		$_comment_meta = '<div class="comment_meta">'.$_comment_meta_content.'</div>'."\n";
	
		// allow filtering by plugins
		$_comment_meta = apply_filters( 
			'commentpress_format_comment_by_meta', // filter name
			$_comment_meta, // built meta
			$comment,
			$_comment_anchor,
			$_page_anchor,
			$_comment_date
		);
	
	}
	
	
	
	// comment content
	$_comment_body = '<div class="comment-content">'.apply_filters( 'comment_text', $comment->comment_content ).'</div>'."\n";
	
	// construct comment
	return '<div class="comment_wrapper">'."\n".$_comment_meta.$_comment_body.'</div>'."\n\n";
	
}
endif; // commentpress_format_comment






if ( ! function_exists( 'commentpress_get_all_comments_content' ) ):
/** 
 * @description: all-comments page display function
 * @todo: 
 *
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
	$html .= '<ul class="all_comments_listing">'."\n\n";

	foreach( $posts AS $_post ) {

		// open li
		$html .= '<li class="page_li"><!-- page li -->'."\n\n";
		
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
		$html .= '<h4>'.esc_html( $_post->post_title ).' <span>('.$comment_count_text.')</span></h4>'."\n\n";

		// open comments div
		$html .= '<div class="item_body">'."\n\n";
		
		// open ul
		$html .= '<ul class="item_ul">'."\n\n";

		// open li
		$html .= '<li class="item_li"><!-- item li -->'."\n\n";
		
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
		
		// close li
		$html .= '</li><!-- /item li -->'."\n\n";

		// close ul
		$html .= '</ul>'."\n\n";
	
		// close item div
		$html .= '</div><!-- /item_body -->'."\n\n";
	
		// close li
		$html .= '</li><!-- /page li -->'."\n\n\n\n";
	
	}

	// close ul
	$html .= '</ul><!-- /all_comments_listing -->'."\n\n";
	
	
	
	// --<
	return $html;

}
endif; // commentpress_get_all_comments_content
	
	
	



if ( ! function_exists( 'commentpress_get_all_comments_page_content' ) ):
/** 
 * @description: all-comments page display function
 * @todo: 
 *
 */
function commentpress_get_all_comments_page_content() {

	// allow oEmbed in comments
	global $wp_embed;
	if ( is_a( $wp_embed, 'WP_Embed' ) ) {
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
	$_page_content = '<h2 class="post_title">'.$pagetitle.'</h2>'."\n\n";
	


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
		$_page_content .= '<h3 class="comments_hl">'.$title.'</h3>'."\n\n";
	
		// set data
		$_page_content .= $_data."\n\n";
		
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
		$_page_content .= '<h3 class="comments_hl">'.$title.'</h3>'."\n\n";
		
		// set data
		$_page_content .= $_data."\n\n";
		
	}
	
	
	
	// --<
	return $_page_content;
	
}
endif; // commentpress_get_all_comments_page_content

	
	



if ( ! function_exists( 'commentpress_get_comments_by_content' ) ):
/** 
 * @description: comments-by page display function
 * @todo: do we want trackbacks?
 *
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
	//print_r( $all_comments ); //die();
	
	// kick out if none
	if ( count( $all_comments ) == 0 ) return $html;
	
	
	
	// build list of authors
	$authors_with = array();
	$author_names = array();
	//$post_comment_counts = array();

	foreach( $all_comments AS $comment ) {
		
		// add to authors with comments array
		if ( !in_array( $comment->comment_author_email, $authors_with ) ) {
			$authors_with[] = $comment->comment_author_email;
			$name = $comment->comment_author != '' ? $comment->comment_author : __( 'Anonymous', 'commentpress-core' );
			$author_names[$comment->comment_author_email] = $name;
		}
		
		/*
		// increment counter
		if ( !isset( $post_comment_counts[$comment->comment_author_email] ) ) {
			$post_comment_counts[$comment->comment_author_email] = 1;
		} else {
			$post_comment_counts[$comment->comment_author_email]++;
		}
		*/
		
	}
	//print_r( $post_comment_counts ); //die();
	//print_r( $authors_with ); die();
	
	// kick out if none
	if ( count( $authors_with ) == 0 ) return $html;
	
	
	
	// open ul
	$html .= '<ul class="all_comments_listing">'."\n\n";
	
	// loop through authors
	foreach( $authors_with AS $author ) {
		
		// open li
		$html .= '<li class="author_li"><!-- author li -->'."\n\n";
		
		// add gravatar
		$html .= '<h3>'.get_avatar( $author, $size='24' ). esc_html( $author_names[$author] ).'</h3>'."\n\n";

		// open comments div
		$html .= '<div class="item_body">'."\n\n";
		
		// open ul
		$html .= '<ul class="item_ul">'."\n\n";

		// loop through comments
		foreach( $all_comments AS $comment ) {
			
			// does it belong to this author?
			if ( $author == $comment->comment_author_email ) {
		
				// open li
				$html .= '<li class="item_li"><!-- item li -->'."\n\n";
	
				// show the comment
				$html .= commentpress_format_comment( $comment, 'by' );
		
				// close li
				$html .= '</li><!-- /item li -->'."\n\n";

			}

		}
	
		// close ul
		$html .= '</ul>'."\n\n";
		
		// close item div
		$html .= '</div><!-- /item_body -->'."\n\n";
		
		// close li
		$html .= '</li><!-- /.author_li -->'."\n\n\n\n";
		
	}
	
	// close ul
	$html .= '</ul><!-- /.all_comments_listing -->'."\n\n";

	
	
	// --<
	return $html;
	
}
endif; // commentpress_get_comments_by_content

	
	



if ( ! function_exists( 'commentpress_get_comments_by_page_content' ) ):
/** 
 * @description: comments-by page display function
 * @todo: 
 *
 */
function commentpress_get_comments_by_page_content() {

	// allow oEmbed in comments
	global $wp_embed;
	if ( is_a( $wp_embed, 'WP_Embed' ) ) {
		add_filter( 'comment_text', array( $wp_embed, 'autoembed' ), 1 );
	}
	
	
	
	// declare access to globals
	global $commentpress_core;

	
	
	// set title
	$_page_content = '<h2 class="post_title">'.__( 'Comments by Commenter', 'commentpress-core' ).'</h2>'."\n\n";

	// get data
	$_page_content .= commentpress_get_comments_by_content();
	

	
	// --<
	return $_page_content;
	
}
endif; // commentpress_get_comments_by_page_content

	
	




if ( ! function_exists( 'commentpress_show_activity_tab' ) ):
/** 
 * @description: decide whether or not to show the Activity Sidebar
 * @todo: 
 *
 */
function commentpress_show_activity_tab() {

	// declare access to globals
	global $commentpress_core, $post;

	
	
	/*
	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {
	
		// is this multisite?
		if ( 
		
			( is_multisite() 
			AND is_main_site() 
			AND $commentpress_core->is_buddypress_special_page() )
			OR !is_object( $post )
			
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
 * @description: decide whether or not to show the Activity Sidebar
 * @todo: 
 *
 */
function commentpress_is_commentable() {

	// declare access to plugin
	global $commentpress_core;
	
	// if we have it...
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
 * @description: activity sidebar display function
 * @todo: do we want trackbacks?
 *
 */
function commentpress_get_comment_activity( $scope = 'all' ) {

	// allow oEmbed in comments
	global $wp_embed;
	if ( is_a( $wp_embed, 'WP_Embed' ) ) {
		add_filter( 'comment_text', array( $wp_embed, 'autoembed' ), 1 );
	}
	
	
	
	// declare access to globals
	global $commentpress_core, $post;

	// init page content
	$_page_content = '';
	
	
	
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
	$_data = get_comments( $args );
	//print_r( $_data ); exit();
	
	
	
	// did we get any?
	if ( count( $_data ) > 0 ) {
	
	
	
		// open ul
		$_page_content .= '<ol class="comment_activity">'."\n\n";
		
		// init title
		$_title = '';
		
		// loop
		foreach ($_data as $comment) {
		
			$_page_content .= commentpress_get_comment_activity_item( $comment );
		
		}
	
		// close ul
		$_page_content .= '</ol><!-- /comment_activity -->'."\n\n";
	
	}
	
	
	
	// --<
	return $_page_content;
	
}
endif; // commentpress_get_comment_activity

	
	



if ( ! function_exists( 'commentpress_get_comment_activity_item' ) ):
/** 
 * @description: get comment formatted for the activity sidebar
 */
function commentpress_get_comment_activity_item( $comment ) {
	
	// enable Wordpress API on comment
	$GLOBALS['comment'] = $comment;
	
	// declare access to globals
	global $commentpress_core, $post;

	// init markup
	$item_html = '';
	
	
	
	// only comments until we decide what to do with pingbacks
	if ( $comment->comment_type == 'pingback' ) { return $item_html; }



	// test for anonymous comment (usually generated by WP itself in multisite installs)
	if ( empty( $comment->comment_author ) ) {
	
		$comment->comment_author = __( 'Anonymous', 'commentpress-core' );
	
	}
	
	

	// was it a registered user?
	if ( $comment->user_id != '0' ) {
	
		// get user details
		$user = get_userdata( $comment->user_id );
		//print_r( $user->display_name ); die();
		
		// get user link
		$user_link = commentpress_get_user_link( $user );
		
		// construct author citation
		$author = '<cite class="fn"><a href="'.$user_link.'">'.esc_html( $comment->comment_author ).'</a></cite>';
		
		// construct link to user url
		$author = ( $user_link != '' AND $user_link != 'http://' ) ? 
					'<cite class="fn"><a href="'.$user_link.'">'.esc_html( $comment->comment_author ).'</a></cite>' : 
					 '<cite class="fn">'.esc_html( $comment->comment_author ).'</cite>';
		
	} else {
	
		// construct link to commenter url
		$author = ( $comment->comment_author_url != '' AND $comment->comment_author_url != 'http://' ) ? 
					'<cite class="fn"><a href="'.$comment->comment_author_url.'">'.esc_html( $comment->comment_author ).'</a></cite>' : 
					 '<cite class="fn">'.esc_html( $comment->comment_author ).'</cite>';
	
	}
		
	
	
	// approved comment?
	if ($comment->comment_approved == '0') {
		$comment_text = '<p><em>'.__( 'Comment awaiting moderation', 'commentpress-core' ).'</em></p>';
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
			
			/*
			print_r( array( 
				'multipage' => $multipage, 
				'page' => $page 
			) ); die();
			*/
			
			// if it has a text sig
			if ( 
			
				!is_null( $comment->comment_signature ) 
				AND $comment->comment_signature != '' 
				
			) {

				// set key
				$key = '_cp_comment_page';
				
				// if the custom field already has a value...
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
	$item_html .= '<li><!-- item li -->'."\n\n";

	// show the comment
	$item_html .= '
<div class="comment-wrapper">

<div class="comment-identifier">
'.get_avatar( $comment, $size='32' ).'
'.$author.'		
<p class="comment_activity_date"><a class="comment_activity_link'.$is_on_current_post.'" href="'.htmlspecialchars( get_comment_link() ).'">'.get_comment_date().' at '.get_comment_time().'</a></p>
</div><!-- /comment-identifier -->



<div class="comment-content">
'.apply_filters( 'comment_text', $comment_text ).'
</div><!-- /comment-content -->

<div class="reply"><p><a class="comment_activity_link'.$is_on_current_post.'" href="'.htmlspecialchars( get_comment_link() ).'">'.__( 'See in context', 'commentpress-core' ).'</a></p></div><!-- /reply -->

</div><!-- /comment-wrapper -->

';

	// close li
	$item_html .= '</li><!-- /item li -->'."\n\n";
	
	// --<
	return $item_html;
	
}
endif; // commentpress_get_comment_activity_item






if ( ! function_exists( 'commentpress_get_comments_by_para' ) ):
/** 
 * @description: get comments delimited by paragraph
 * @todo: translation
 *
 */
function commentpress_get_comments_by_para() {
	
	// allow oEmbed in comments
	global $wp_embed;
	if ( is_a( $wp_embed, 'WP_Embed' ) ) {
		add_filter( 'comment_text', array( $wp_embed, 'autoembed' ), 1 );
	}
	
	
	
	// allow plugins to precede comments
	do_action( 'commentpress_before_scrollable_comments' );
	
	
	
	// declare access to globals
	global $post, $commentpress_core;
	


	// get approved comments for this post, sorted comments by text signature
	$comments_sorted = $commentpress_core->get_sorted_comments( $post->ID );
	//print_r( $comments_sorted ); die();
	
	// get text signatures
	//$text_sigs = $commentpress_core->db->get_text_sigs();

	// init starting paragraph number
	$start_num = 1;
	
	// set key
	$key = '_cp_starting_para_number';
	
	// if the custom field already has a value...
	if ( get_post_meta( $post->ID, $key, true ) != '' ) {
	
		// get it
		$start_num = absint( get_post_meta( $post->ID, $key, true ) );
		
	}
	


	// if we have any...
	if ( count( $comments_sorted ) > 0 ) {
	
		// construct redirect link
		$redirect = site_url( 'wp-login.php?redirect_to='.get_permalink() );
		
		// init allowed to comment
		$login_to_comment = false;
		
		// if we have to log in to comment...
		if ( get_option('comment_registration') AND !is_user_logged_in() ) {
			$login_to_comment = true;
		}
		
		
		
		// default comment type to get
		$comment_type = 'all';

		// if we don't allow pingbacks...
		if ( !('open' == $post->ping_status) ) {
		
			// just get comments
			$comment_type = 'comment';
	
		}
		
		

		// check for a WP 3.8+ function
		if ( function_exists( 'wp_admin_bar_sidebar_toggle' ) ) {
		
			// Walker_Comment has changed to buffered output, so define args without
			// our custom walker. The built in walker works just fine now.
			$args = array(
			
				// list comments params
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
			
				// list comments params
				'walker' => $walker,
				'style'=> 'ol', 
				'type'=> $comment_type, 
				'callback' => 'commentpress_comments'
			
			);
			
		}

		
		
		// init counter for text_signatures array
		$sig_counter = 0;
		
		// init array for tracking text sigs
		$used_text_sigs = array();
		
		

		// loop through each paragraph
		foreach( $comments_sorted AS $text_signature => $_comments ) {
		
			// count comments
			$comment_count = count( $_comments );
			
			// switch, depending on key
			switch( $text_signature ) {
				
				// whole page comments
				case 'WHOLE_PAGE_OR_POST_COMMENTS':

					// clear text signature
					$text_sig = '';
					
					// clear the paragraph number
					$para_num = '';
					
					// define default phrase
					$paragraph_text = __( 'the whole page', 'commentpress-core' );
					
					$current_type = get_post_type();
					//print_r( $current_type ); die();
					
					switch( $current_type ) {
						
						// we can add more of these if needed
						case 'post': $paragraph_text = __( 'the whole post', 'commentpress-core' ); break;
						case 'page': $paragraph_text = __( 'the whole page', 'commentpress-core' ); break;
						
					}
				
					// set permalink text
					$permalink_text = sprintf(
						__('Permalink for comments on %s', 'commentpress-core' ),
						$paragraph_text
					);
					
					// define heading text
					$heading_text = sprintf( _n(
						
						// singular
						'<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comment</span> on ', 
						
						// plural
						'<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comments</span> on ', 
						
						// number
						$comment_count, 
						
						// domain
						'commentpress-core'
					
					// substitution
					), $comment_count );
					
					// append para text
					$heading_text .= '<span class="source_block">'.$paragraph_text.'</span>';
					
					break;
				
				// pingbacks etc
				case 'PINGS_AND_TRACKS':

					// set "unique-enough" text signature
					$text_sig = 'pingbacksandtrackbacks';
					
					// clear the paragraph number
					$para_num = '';
					
					// define heading text
					$heading_text = sprintf( _n(
						
						// singular
						'<span>%d</span> Pingback or trackback', 
						
						// plural
						'<span>%d</span> Pingbacks and trackbacks', 
						
						// number
						$comment_count, 
						
						// domain
						'commentpress-core'
					
					// substitution
					), $comment_count );
					
					// set permalink text
					$permalink_text = __( 'Permalink for pingbacks and trackbacks', 'commentpress-core' );
					
					// wrap in span
					$heading_text = '<span>'.$heading_text.'</span>';

					break;
					
				// textblock comments
				default:

					// get text signature
					$text_sig = $text_signature;
				
					// paragraph number
					$para_num = $sig_counter + ( $start_num - 1 );
					
					// which parsing method?
					if ( defined( 'COMMENTPRESS_BLOCK' ) ) {
					
						switch ( COMMENTPRESS_BLOCK ) {
						
							case 'tag' :
								
								// set block identifier
								$block_name = __( 'paragraph', 'commentpress-core' );
							
								break;
								
							case 'block' :
								
								// set block identifier
								$block_name = __( 'block', 'commentpress-core' );
							
								break;
								
							case 'line' :
								
								// set block identifier
								$block_name = __( 'line', 'commentpress-core' );
							
								break;
								
						}
					
					} else {
					
						// set block identifier
						$block_name = __( 'paragraph', 'commentpress-core' );
					
					}
					
					// set paragraph text
					$paragraph_text = $block_name.' '.$para_num;
					
					// set permalink text
					$permalink_text = sprintf(
						__('Permalink for comments on %s', 'commentpress-core' ),
						$paragraph_text
					);
					
					// define heading text
					$heading_text = sprintf( _n(
						
						// singular
						'<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comment</span> on ', 
						
						// plural
						'<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comments</span> on ', 
						
						// number
						$comment_count, 
						
						// domain
						'commentpress-core'
					
					// substitution
					), $comment_count );
					
					// append para text
					$heading_text .= '<span class="source_block">'.$paragraph_text.'</span>';
					
			} // end switch
		


			// init no comment class
			$no_comments_class = '';
			
			// override if there are no comments (for print stylesheet to hide them)
			if ( $comment_count == 0 ) { $no_comments_class = ' class="no_comments"'; }
			
			// eclude pings if there are none
			if ( $comment_count == 0 AND $text_signature == 'PINGS_AND_TRACKS' ) {
			
				// skip
				
			} else {
			
				// show heading
				echo '<h3 id="para_heading-'.$text_sig.'"'.$no_comments_class.'><a class="comment_block_permalink" title="'.$permalink_text.'" href="#para_heading-'.$text_sig.'">'.$heading_text.'</a></h3>'."\n\n";
	
				// override if there are no comments (for print stylesheet to hide them)
				if ( $comment_count == 0 ) { $no_comments_class = ' no_comments'; }
				
				// open paragraph wrapper
				echo '<div id="para_wrapper-'.$text_sig.'" class="paragraph_wrapper'.$no_comments_class.'">'."\n\n";
	
				// have we already used this text signature?
				if( in_array( $text_sig, $used_text_sigs ) ) {
				
					// show some kind of message
					// should not be necessary now that we ensure unique text sigs
					echo '<div class="reply_to_para" id="reply_to_para-'.$para_num.'">'."\n".
							'<p>'.
								__( 'It appears that this paragraph is a duplicate of a previous one.', 'commentpress-core' ).
							'</p>'."\n".
						 '</div>'."\n\n";
	
				} else {
			
					// if we have comments...
					if ( count( $_comments ) > 0 ) {
					
						// open commentlist
						echo '<ol class="commentlist">'."\n\n";
				
						// use WP 2.7+ functionality
						wp_list_comments( $args, $_comments ); 
						
						// close commentlist
						echo '</ol>'."\n\n";
							
					}
					
					// add to used array
					$used_text_sigs[] = $text_sig;
				
					// only add comment-on-para link if comments are open and it's not the pingback section
					if ( 'open' == $post->comment_status AND $text_signature != 'PINGS_AND_TRACKS' ) {
					
						// if we have to log in to comment...
						if ( $login_to_comment ) {
						
							// leave comment link
							echo '<div class="reply_to_para" id="reply_to_para-'.$para_num.'">'."\n".
									'<p><a class="reply_to_para" rel="nofollow" href="'.$redirect.'">'.
										__( 'Login to leave a comment on ', 'commentpress-core' ).$paragraph_text.
									'</a></p>'."\n".
								 '</div>'."\n\n";
							
						} else {
						
							// construct onclick content
							$onclick = "return addComment.moveFormToPara( '$para_num', '$text_sig', '$post->ID' )";
							
							// construct onclick attribute
							$onclick = apply_filters( 
								'commentpress_reply_to_para_link_onclick',
								' onclick="'.$onclick.'"'
							);
						
							// just show replytopara
							$query = remove_query_arg( array( 'replytocom' ) ); 
			
							// add param to querystring
							$query = esc_attr( 
								add_query_arg( 
									array( 'replytopara' => $para_num ),
									$query
								)
							);
							
							// construct href attribute
							$href = apply_filters( 
								'commentpress_reply_to_para_link_href',
								$query.'#respond' // add respond ID
							);
						
							// construct link content
							$link_content = sprintf(
								__( 'Leave a comment on %s', 'commentpress-core' ),
								$paragraph_text
							);
						
							// allow overrides
							$link_content = apply_filters( 
								'commentpress_reply_to_para_link_text',
								$link_content,
								$paragraph_text
							);
							
							// leave comment link
							echo '<div class="reply_to_para" id="reply_to_para-'.$para_num.'">'."\n".
									'<p><a class="reply_to_para" href="'.$href.'"'.$onclick.'>'.
										$link_content.
									'</a></p>'."\n".
								 '</div>'."\n\n";
							
						}
							 
					}
						 
				}
	
				// close paragraph wrapper
				echo '</div>'."\n\n\n\n";
				
			}
			
			// increment signature array counter
			$sig_counter++;
			
		}
		
	}
	
}
endif; // commentpress_get_comments_by_para






/**
 * HTML comment list class.
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
	function start_lvl( &$output, $depth, $args ) {
	
		// if on top level
		if( $depth === 0 ) {
			//echo '<h3>New Top Level</h3>'."\n";
		}
		
		// store depth
		$GLOBALS['comment_depth'] = $depth + 1;
		
		// open children if necessary
		switch ( $args['style'] ) {
		
			case 'div':
				break;
				
			case 'ol':
				echo '<ol class="children">'."\n";
				break;
				
			default:
			case 'ul':
				echo '<ul class="children">'."\n";
				break;
		}
		
	}

}






if ( ! function_exists( 'commentpress_comment_form_title' ) ):
/** 
 * @description: alternative to the built-in WP function
 * @todo: 
 *
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
	$reply_to_comment_id = isset($_GET['replytocom']) ? (int) $_GET['replytocom'] : 0;

	// get paragraph number to reply to from URL query string
	$reply_to_para_id = isset($_GET['replytopara']) ? (int) $_GET['replytopara'] : 0;



	// if we have no comment ID AND no paragraph ID to reply to
	if ( $reply_to_comment_id == 0 AND $reply_to_para_id == 0 ) {
		
		// write default title to page
		echo $no_reply_text;
		
	} else {
	
		// if we have a comment ID AND NO paragraph ID to reply to
		if ( $reply_to_comment_id != 0 AND $reply_to_para_id == 0 ) {
	
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
					__( 'Paragraph %s', 'commentpress-core' ),
					$reply_to_para_id
				);
				
				// construct paragraph
				$paragraph = '<a href="#para_heading-' . $text_sig . '">'.$para_text.'</a>';
				
			} else {
			
				// construct paragraph without link
				$paragraph = sprintf(
					__( 'Paragraph %s', 'commentpress-core' ),
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
 * @description: alternative to the built-in WP function
 * @todo: 
 *
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
	$args = wp_parse_args($args, $defaults);

	if ( 0 == $args['depth'] || $args['max_depth'] <= $args['depth'] ) {
		return;
	}
	
	// convert to vars
	extract($args, EXTR_SKIP);
	
	// get the obvious
	$comment = get_comment($comment);
	$post = get_post($post);

	// kick out if comments closed
	if ( 'open' != $post->comment_status ) { return false; }
	
	// init link
	$link = '';
	
	// if we have to log in to comment...
	if ( get_option( 'comment_registration' ) AND !is_user_logged_in() ) {
		
		// construct link
		$link = '<a rel="nofollow" href="' . site_url('wp-login.php?redirect_to=' . get_permalink()) . '">' . $login_text . '</a>';
		
	} else {
	
		// just show replytocom
		$query = remove_query_arg( array( 'replytopara' ), get_permalink( $post->ID ) ); 

		// define query string
		$addquery = esc_html( 
		
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
 * @description: custom comments display function
 * @todo: 
 *
 */
function commentpress_comments( $comment, $args, $depth ) {

	// build comment as html
	echo commentpress_get_comment_markup( $comment, $args, $depth );
	
}
endif; // commentpress_comments






if ( ! function_exists( 'commentpress_get_comment_markup' ) ):
/** 
 * @description: wrap comment in its markup
 * @todo: 
 *
 */
function commentpress_get_comment_markup( $comment, $args, $depth ) {

	//print_r( $comment );
	//print_r( $args );

	// enable Wordpress API on comment
	$GLOBALS['comment'] = $comment;



	// was it a registered user?
	if ( $comment->user_id != '0' ) {
	
		// get user details
		$user = get_userdata( $comment->user_id );
		//print_r( $user->display_name ); die();
		
		// get user link
		$user_link = commentpress_get_user_link( $user );
		//print_r( array( 'u' => $user_link ) ); die();
		
		// construct author citation
		$author = ( $user_link != '' AND $user_link != 'http://' ) ? 
					'<cite class="fn"><a href="'.$user_link.'">'.get_comment_author().'</a></cite>' : 
					 '<cite class="fn">'.get_comment_author().'</cite>';
		
		//print_r( array( 'a' => $author ) ); die();

	} else {
	
		// construct link to commenter url
		$author = ( $comment->comment_author_url != '' AND $comment->comment_author_url != 'http://' AND $comment->comment_approved != '0' ) ? 
					'<cite class="fn"><a href="'.$comment->comment_author_url.'">'.get_comment_author().'</a></cite>' : 
					 '<cite class="fn">'.get_comment_author().'</cite>';
	
	}
		
	
	
	/*
	if ($comment->comment_approved == '0') {
		$author = '<cite class="fn">'.get_comment_author().'</cite>';
	} else {
		$author = '<cite class="fn">'.get_comment_author_link().'</cite>';
	}
	*/
	
	
	
	if ( $comment->comment_approved == '0' ) {
		$comment_text = '<p><em>'.__( 'Comment awaiting moderation', 'commentpress-core' ).'</em></p>';
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
		$post->comment_status == 'open' 
		
		// we don't want reply to on pingbacks
		AND $comment->comment_type != 'pingback' 
		
		// nor on unapproved comments
		AND $comment->comment_approved == '1' 
		
	) {
	
		// are we threading comments?
		if ( get_option( 'thread_comments', false ) ) {
		
			// custom comment_reply_link
			$comment_reply = commentpress_comment_reply_link(
			
				array_merge(
				
					$args, 
					array(
						'reply_text' => sprintf( __( 'Reply to %s', 'commentpress-core' ), get_comment_author() ),
						'depth' => $depth, 
						'max_depth' => $args['max_depth']
					)
				)
				
			);
			
			// wrap in div
			$comment_reply = '<div class="reply">'.$comment_reply.'</div><!-- /reply -->';
			
		}
		
	}
	
	
	
	// init edit link
	$editlink = '';
	
	// if logged in and has capability
	if ( 
		is_user_logged_in() 
	AND 
		current_user_can('edit_posts') 
	AND 
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
		$editlink = '<span class="alignright comment-edit"><a class="comment-edit-link" href="'.get_edit_comment_link().'" title="'.$edit_title_text.'">'.$edit_text.'</a></span>';
		
		// add a filter for plugins
		$editlink = apply_filters( 'cp_comment_edit_link', $editlink, $comment );
		
	}
	
	// add a nopriv filter for plugins
	$editlink = apply_filters( 'cp_comment_action_links', $editlink, $comment );
	
	
	
	// get comment class(es)
	$_comment_class = comment_class( null, $comment->comment_ID, $post->ID, false );
	
	// if orphaned, add class to identify as such
	$_comment_orphan = ( isset( $comment->orphan ) ) ? ' comment-orphan' : '';
	
	
	
	// stripped source
	$html = '	
<li id="li-comment-'.$comment->comment_ID.'" '.$_comment_class.'>
<div class="comment-wrapper">
<div id="comment-'.$comment->comment_ID.'">



<div class="comment-identifier'.$_comment_orphan.'">
'.get_avatar( $comment, $size='32' ).'
'.$editlink.'
'.$author.'		
<a class="comment_permalink" href="'.htmlspecialchars( get_comment_link() ).'">'.get_comment_date().' at '.get_comment_time().'</a>
</div><!-- /comment-identifier -->



<div class="comment-content'.$_comment_orphan.'">
'.apply_filters( 'comment_text', $comment_text ).'
</div><!-- /comment-content -->



'.$comment_reply.'



</div><!-- /comment-'.$comment->comment_ID.' -->
</div><!-- /comment-wrapper -->
';



	// --<
	return $html;
	
}
endif; // commentpress_get_comment_markup






if ( ! function_exists( 'commentpress_get_full_name' ) ):
/** 
 * @description: utility to concatenate names
 * @todo: 
 *
 */
function commentpress_get_full_name( $forename, $surname ) {

	// init return
	$fullname = '';
	
	

	// add forename
	if ($forename != '' ) { $fullname .= $forename; } 
	
	// add surname
	if ($surname != '' ) { $fullname .= ' '.$surname; }
	
	// strip any whitespace
	$fullname = trim( $fullname );
	
	
	
	// --<
	return $fullname;
	
}
endif; // commentpress_get_full_name






if ( ! function_exists( 'commentpress_excerpt_length' ) ):
/** 
 * @description: utility to define length of excerpt
 * @todo: 
 *
 */
function commentpress_excerpt_length() {

	// declare access to globals
	global $commentpress_core;
	
	
	
	// is the plugin active?
	if ( !is_object( $commentpress_core ) ) {
	
		// --<
		return 55; // Wordpress default
		
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
 * @description: utility to add button class to BP 1.9 notification links
 * @todo: 
 *
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






if ( ! function_exists( 'commentpress_add_link_css' ) ):
/** 
 * @description: utility to add button css class to blog nav links
 * @todo: 
 *
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
 * @description: utility to add button css class to blog nav links
 * @todo: 
 *
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






if ( ! function_exists( 'commentpress_add_loginout_id' ) ):
/** 
 * @description: utility to add button css id to login links
 * @todo: 
 *
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
	$link = str_replace( '<a ', '<a id="'.$_id.'" ', $link );

	// --<
	return $link;
	
}
endif; // commentpress_add_loginout_id

// add filters for WordPress admin links
add_filter( 'loginout', 'commentpress_add_link_css' );
add_filter( 'loginout', 'commentpress_add_loginout_id' );
add_filter( 'register', 'commentpress_add_loginout_id' );






if ( ! function_exists( 'commentpress_multipage_comment_link' ) ):
/** 
 * @description: filter comment permalinks for multipage posts
 * @todo: should this go in the plugin?
 *
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
			
			// if the custom field already has a value...
			if ( get_comment_meta( $comment->comment_ID, $key, true ) != '' ) {
			
				// get the page number
				$page_num = get_comment_meta( $comment->comment_ID, $key, true );
				
			}
			
			// get current comment info
			$comment_path_info = parse_url( $link );
			
			// set comment path
			return commentpress_get_post_multipage_url( $page_num, get_post( $comment->comment_post_ID ) ).'#'.$comment_path_info['fragment'];

		}
		
	//}

	// --<
	return $link;

}
endif; // commentpress_multipage_comment_link

// add filter for the above
add_filter( 'get_comment_link', 'commentpress_multipage_comment_link', 10, 3 );






/**
 * Copied from wp-includes/post-template.php _wp_link_page()
 * @param int $i Page number.
 * @return string url.
 */
function commentpress_get_post_multipage_url( $i, $post = '' ) {

	// if we have no passed value
	if ( $post == '' ) {
		
		// we assume we're in the loop
		global $post, $wp_rewrite;
	
		if ( 1 == $i ) {
			$url = get_permalink();
		} else {
			if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) )
				$url = add_query_arg( 'page', $i, get_permalink() );
			elseif ( 'page' == get_option('show_on_front') AND get_option('page_on_front') == $post->ID )
				$url = trailingslashit(get_permalink()) . user_trailingslashit("$wp_rewrite->pagination_base/" . $i, 'single_paged');
			else
				$url = trailingslashit(get_permalink()) . user_trailingslashit($i, 'single_paged');
		}
		
	} else {
		
		// use passed post object
		if ( 1 == $i ) {
			$url = get_permalink( $post->ID );
		} else {
			if ( '' == get_option('permalink_structure') || in_array($post->post_status, array('draft', 'pending')) )
				$url = add_query_arg( 'page', $i, get_permalink( $post->ID ) );
			elseif ( 'page' == get_option('show_on_front') AND get_option('page_on_front') == $post->ID )
				$url = trailingslashit(get_permalink( $post->ID )) . user_trailingslashit("$wp_rewrite->pagination_base/" . $i, 'single_paged');
			else
				$url = trailingslashit(get_permalink( $post->ID )) . user_trailingslashit($i, 'single_paged');
		}
		
	}
	
	return esc_url( $url );
}







if ( ! function_exists( 'commentpress_multipager' ) ):
/** 
 * @description: adds some style
 * @todo: 
 *
 */
function commentpress_multipager() {

	// set default behaviour
	$defaults = array(
		
		'before' => '<div class="multipager">',
		'after' => '</div>',
		'link_before' => '', 
		'link_after' => '',
		'next_or_number' => 'next', 
		'nextpagelink' => '<span class="alignright">'.__( 'Next page', 'commentpress-core' ).' &raquo;</span>',
		'previouspagelink' => '<span class="alignleft">&laquo; '.__( 'Previous page', 'commentpress-core' ).'</span>',
		'pagelink' => '%',
		'more_file' => '', 
		'echo' => 0
		
	);
	
	// get page links
	$page_links = wp_link_pages( $defaults );
	//print_r( $page_links ); die();
	
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






/**
 * @description; adds our styles to the TinyMCE editor
 * @param string $mce_css The default TinyMCE stylesheets as set by WordPress
 * @return string $mce_css The list of stylesheets with ours added
 */
function commentpress_add_wp_editor() {
	
	// init option
	$rich_text = false;
	
	global $commentpress_core;
	
	// kick out if wp_editor doesn't exist
	// TinyMCE will be handled by including the script using the pre- wp_editor() method
	if ( !function_exists( 'wp_editor' ) ) {
	
		// --<
		return false;
	
	}

	// kick out if plugin not active
	if ( !is_object( $commentpress_core ) ) {
	
		// --<
		return false;
	
	}

	// only allow through if plugin says so
	if ( !$commentpress_core->display->is_tinymce_allowed() ) {
	
		// --<
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
	
	
	
	// don't show textarea
	return true;

}






/**
 * @description; makes TinyMCE the default editor on the front end
 * @param string $r The default editor as set by WordPress
 * @return string 'tinymce' our overridden default editor
 */
function commentpress_assign_default_editor( $r ) {

	// only on front-end
	if ( is_admin() ) { return $r; }
	
	
	
	// always return 'tinymce' as the default editor, or else the comment form will not show up!
	
	// --<
	return 'tinymce';
	
}

add_filter( 'wp_default_editor', 'commentpress_assign_default_editor', 10, 1 );






/**
 * @description; adds our styles to the TinyMCE editor
 * @param string $mce_css The default TinyMCE stylesheets as set by WordPress
 * @return string $mce_css The list of stylesheets with ours added
 */
function commentpress_add_tinymce_styles( $mce_css ) {

	// only on front-end
	if ( is_admin() ) { return $mce_css; }
	
	// add comma if not empty
	if ( !empty( $mce_css ) ) { $mce_css .= ','; }
	
	// add our editor styles
	$mce_css .= get_template_directory_uri().'/assets/css/comment-form.css';
	
	return $mce_css;
	
}

// add filter for the above
add_filter( 'mce_css', 'commentpress_add_tinymce_styles' );





/**
 * @description; adds the Next Page button to the TinyMCE editor
 * @param array $buttons The default TinyMCE buttons as set by WordPress
 * @return array $buttons The buttons with More removed
 */
function commentpress_add_tinymce_nextpage_button( $buttons ) {
	
	// only on back-end
	if ( !is_admin() ) { return $buttons; }
	
	// try and place Next Page after More button
	$pos = array_search( 'wp_more', $buttons, true );
	
	// is it there?
	if ($pos !== false) {
	
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

// add filter for the above
add_filter( 'mce_buttons', 'commentpress_add_tinymce_nextpage_button' );






/**
 * @description; assign our buttons to TinyMCE in teeny mode
 * @param array $buttons The default editor buttons as set by WordPress
 * @return string 'tinymce' our overridden default editor
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

// access WP version
global $wp_version;

// if greater than 3.8
if ( version_compare( $wp_version, '3.8.9999', '>' ) ) {
	add_filter( 'teeny_mce_buttons', 'commentpress_assign_editor_buttons' );
}





if ( ! function_exists( 'commentpress_comment_post_redirect' ) ):
/** 
 * @description: filter comment post redirects for multipage posts
 * @todo: should this go in the plugin?
 *
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
			$ajax_token = '?cachebuster='.time();
			
			// but, test for pretty permalinks
			if ( false !== strpos( $page_url, '?' ) ) {
		
				// pretty permalinks are off...
				$ajax_token = '&cachebuster='.time();
		
			}
	
		}
		
	}

	// construct cachebusting comment redirect
	$link = $page_url.$ajax_token.'#comment-'.$comment->comment_ID;
	
	// --<
	return $link;

}
endif; // commentpress_comment_post_redirect

// add filter for the above, making it run early so it can be overridden by AJAX commenting
add_filter( 'comment_post_redirect', 'commentpress_comment_post_redirect', 4, 2 );






if ( ! function_exists( 'commentpress_image_caption_shortcode' ) ):
/** 
 * @description: rebuild caption shortcode output
 * @param array $empty WordPress passes '' as the first param!
 * @param array $attr Attributes attributed to the shortcode.
 * @param string $content Optional. Shortcode content.
 * @return string
 * @todo:
 *
 */
function commentpress_image_caption_shortcode( $empty=null, $attr, $content ) {
	
	// get our shortcode vars
	extract(shortcode_atts(array(
		'id'	=> '',
		'align'	=> 'alignnone',
		'width'	=> '',
		'caption' => ''
	), $attr));
	
	/*
	print_r( array(
		'content' => $content,
		'caption' => $caption,
	) ); die();
	*/
	
	if ( 1 > (int) $width || empty($caption) )
		return $content;
	
	// sanitise id
	if ( $id ) $id = 'id="' . esc_attr($id) . '" ';
	
	// add space prior to alignment
	$_alignment = ' '.esc_attr($align);
	
	// get width
	$_width = (0 + (int) $width);
	
	// sanitise caption
	$caption = wp_kses( $caption,
		
		// allow a few tags
		array(
			'em' => array(),  
			'strong' => array(),  
			'a' => array('href')
		)
		
	);
	
	// force balance those tags
	$caption = balanceTags( $caption, true );
	
	// construct
	$_caption = '<!-- cp_caption_start -->'.
				'<span class="captioned_image'.$_alignment.'" style="width: '.$_width.'px">'.
					'<span '.$id.' class="wp-caption">'.do_shortcode( $content ).'</span>'.
					'<small class="wp-caption-text">'.$caption.'</small>'.
				'</span>'.
				'<!-- cp_caption_end -->';
	
	// --<
	return $_caption;
	
}
endif; // commentpress_image_caption_shortcode

// add a filter for the above
add_filter( 'img_caption_shortcode', 'commentpress_image_caption_shortcode', 10, 3 );







if ( ! function_exists( 'commentpress_audio' ) ):
/** 
 * @description: enable audio shortcode
 * @param array $attr Attributes attributed to the shortcode.
 * @param string $content Optional. Shortcode content.
 * @return string
 * @todo:
 *
 */
function commentpress_audio( $atts, $content = null ) {

    extract(shortcode_atts(array(
        "src" => '',
        "autoplay" => '',
        "preload"=> 'true',
        "loop" => '',
        "controls"=> ''
    ), $atts));
    
    return '<audio src="'.$src.'" autoplay="'.$autoplay.'" preload="'.$preload.'" loop="'.$loop.'" controls="'.$controls.'" autobuffer />';

}
endif; // commentpress_audio

// add a shortcode for the above
//add_shortcode( 'audio', 'commentpress_audio' );






if ( ! function_exists( 'commentpress_add_commentblock_button' ) ):
/** 
 * @description: add filters for adding our custom TinyMCE button
 * @todo:
 *
 */
function commentpress_add_commentblock_button() {

	// only on back-end
	if ( ! is_admin() ) { return; }
	
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






if ( ! function_exists( 'commentpress_register_commentblock_button' ) ):
/** 
 * @description: add filters for adding our custom TinyMCE button
 * @todo:
 *
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
 * @description: load the TinyMCE plugin : cp_editor_plugin.js
 * @todo:
 *
 */
function commentpress_add_commentblock_tinymce_plugin( $plugin_array ) {

	// add comment block
	$plugin_array['commentblock'] = get_template_directory_uri().'/assets/js/tinymce/cp_editor_plugin.js';
	
	// --<
	return $plugin_array;

}
endif; // commentpress_add_commentblock_tinymce_plugin






if ( ! function_exists( 'commentpress_refresh_mce' ) ):
/** 
 * @description: load the TinyMCE plugin : cp_editor_plugin.js
 * @todo: can this be removed? doesn't seem to affect things...
 *
 */
function commentpress_refresh_mce($ver) {

	$ver += 3;
	return $ver;

}
endif; // commentpress_refresh_mce

// init process for button control
//add_filter( 'tiny_mce_version', 'commentpress_refresh_mce');
add_action( 'init', 'commentpress_add_commentblock_button' );






if ( ! function_exists( 'commentpress_trap_empty_search' ) ):
/** 
 * @description: trap empty search queries and redirect
 * @todo: this isn't ideal, but works - awaiting core updates
 *
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




if ( ! function_exists( 'commentpress_amend_password_form' ) ):
/** 
 * @description: adds some style
 * @todo: 
 *
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
 * Register our widgets
 */
function commentpress_widgets_init() {

	// define an area where a widget may be placed
	register_sidebar( array(
		'name' => __( 'CommentPress Footer', 'commentpress-core' ),
		'id' => 'cp-license-8',
		'description' => __( 'An optional widget area in the page footer of the CommentPress theme', 'commentpress-core' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => "</div>",
		'before_title' => '<h3 class="widget-title">',
		'after_title' => '</h3>',
	) );
	
	// widget definitions
	require( get_template_directory() . '/assets/widgets/widgets.php' );

	// and the widget
	register_widget( 'Commentpress_License_Widget' );

}
endif; // commentpress_widgets_init

add_action( 'widgets_init', 'commentpress_widgets_init' );






if ( ! function_exists( 'commentpress_license_image_css' ) ):
/**
 * Amend display of license plugin image
 */
function commentpress_license_image_css() {

	// give a bit more room to the image
	return 'display: block; float: left; margin: 0 6px 3px 0;';

}
endif; // commentpress_license_image_css

add_action( 'license_img_style', 'commentpress_license_image_css' );






if ( ! function_exists( 'commentpress_license_widget_compat' ) ):
/**
 * Remove license from footer when widget not active - wp_footer() is not inside #footer
 */
function commentpress_license_widget_compat() {

	// if the widget is not active, (i.e. the plugin is installed but the widget has not been 
	// dragged to a sidebar), then DO NOT display the license in the footer as a default
	if (!is_active_widget(false, false, 'license-widget', true) ) {
		remove_action( 'wp_footer', 'license_print_license_html' );			
	}

}
endif; // commentpress_license_widget_compat

// do this late, so license ought to be declared by then
add_action( 'widgets_init', 'commentpress_license_widget_compat', 100 );






if ( ! function_exists( 'commentpress_wplicense_compat' ) ):
/**
 * Remove license from footer - wp_footer() is not inside #footer
 */
function commentpress_wplicense_compat() {
	
	// let's not have the default footer
	remove_action( 'wp_footer', 'cc_showLicenseHtml' );

}
endif; // commentpress_wplicense_compat

// do this late, so license ought to be declared by then
add_action( 'init', 'commentpress_wplicense_compat', 100 );






if ( ! function_exists( 'commentpress_groupblog_classes' ) ):
/**
 * Add classes to #content in BuddyPress, so that we can distinguish different groupblog types
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
			$groupblogtype .= ' '.$groupblog_type;
		
		}
		
		// complete
		$groupblog_class = ' class="'.$groupblogtype.'"';
		
	}
	
	
	
	// --<
	return $groupblog_class;
	
}
endif; // commentpress_groupblog_classes






if ( ! function_exists( 'commentpress_get_post_version_info' ) ):
/**
 * Get links to previous and next versions, should they exist
 */
function commentpress_get_post_version_info( $post ) {
	
	// check for newer version
	$newer_link = '';
	
	// assume no newer version
	$newer_id = '';
	
	// set key
	$key = '_cp_newer_version';
	
	// if the custom field already has a value...
	if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
	
		// get it
		$newer_id = get_post_meta( $post->ID, $key, true );
		
	}
	
	
	
	// if we've got one...
	if ( $newer_id !== '' ) {
	
		// get post
		$newer_post = get_post( $newer_id );
		
		// is it published?
		if ( $newer_post->post_status == 'publish' ) {
	
			// get link
			$_link = get_permalink( $newer_post->ID );
			
			// define title
			$_title = __( 'Newer version', 'commentpress-core' );
			
			// construct anchor
			$newer_link = '<a href="'.$_link.'" title="'.$_title.'">'.$_title.' &rarr;</a>';
		
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
			$_link = get_permalink( $older_post->ID );
			
			// define title
			$_title = __( 'Older version', 'commentpress-core' );
			
			// construct anchor
			$older_link = '<a href="'.$_link.'" title="'.$_title.'">&larr; '.$_title.'</a>';
		
		}
	
	}
	
	
	
	// did we get either?
	if ( $newer_link != '' OR $older_link != '' ) {
	
		?>
		<div class="version_info">
			<ul>
				<?php if ( $newer_link != '' ) echo '<li class="newer_version">'.$newer_link.'</li>'; ?>
				<?php if ( $older_link != '' ) echo '<li class="older_version">'.$older_link.'</li>'; ?>
			</ul>
		</div>
		<?php
			
	}
	
}
endif; // commentpress_get_post_version_info






if ( ! function_exists( 'commentpress_get_post_css_override' ) ):
/**
 * Get links to previous and next versions, should they exist
 */
function commentpress_get_post_css_override( $post_id ) {
	
	// add a class for overridden page types
	$type_overridden = '';
	
	// declare access to globals
	global $commentpress_core;
	//print_r(array( 'here' )); die();
	
	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {
	
		// default to current blog type
		$type = $commentpress_core->db->option_get( 'cp_blog_type' );
		//print_r($type); die();
		
		// set post meta key
		$key = '_cp_post_type_override';
		
		// but, if the custom field has a value...
		if ( get_post_meta( $post_id, $key, true ) !== '' ) {
		
			// get it
			$overridden_type = get_post_meta( $post_id, $key, true );
			
			// is it different to the current blog type?
			if ( $overridden_type != $type ) {
			
				$type_overridden = ' overridden_type-'.$overridden_type;
			
			}
		
		}
		
	}
	
	// --<
	return $type_overridden;

}
endif; // commentpress_get_post_css_override





if ( ! function_exists( 'commentpress_get_post_title_visibility' ) ):
/**
 * Get links to previous and next versions, should they exist
 */
function commentpress_get_post_title_visibility( $post_id ) {
	
	// init hide (show by default)
	$hide = 'show';
	
	// declare access to globals
	global $commentpress_core;
	
	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {
	
		// get global hide
		$hide = $commentpress_core->db->option_get( 'cp_title_visibility' );;
		
	}
	
	// set key
	$key = '_cp_title_visibility';
	
	//if the custom field already has a value...
	if ( get_post_meta( $post_id, $key, true ) != '' ) {
	
		// get it
		$hide = get_post_meta( $post_id, $key, true );
		
	}
	
	// --<
	return ( $hide == 'show' ) ? true : false;

}
endif; // commentpress_get_post_title_visibility





if ( ! function_exists( 'commentpress_get_post_meta_visibility' ) ):
/**
 * Get links to previous and next versions, should they exist
 */
function commentpress_get_post_meta_visibility( $post_id ) {
	
	// init hide (hide by default)
	$hide_meta = 'hide';
	
	// declare access to globals
	global $commentpress_core;
	
	// if we have the plugin enabled...
	if ( is_object( $commentpress_core ) ) {
	
		// get global hide_meta
		$hide_meta = $commentpress_core->db->option_get( 'cp_page_meta_visibility' );;
		
		// set key
		$key = '_cp_page_meta_visibility';
		
		// if the custom field already has a value...
		if ( get_post_meta( $post_id, $key, true ) != '' ) {
		
			// override with local value
			$hide_meta = get_post_meta( $post_id, $key, true );
			
		}
		
	}
	
	// --<
	return ( $hide_meta == 'show' ) ? true : false;

}
endif; // commentpress_get_post_meta_visibility





if ( ! function_exists( 'commentpress_get_feature_image' ) ):
/** 
 * @description: show feature image
 */
function commentpress_get_feature_image() {
	
	// do we have a featured image?
	if ( commentpress_has_feature_image() ) {
	
		// show it
		echo '<div class="cp_feature_image">';
		
		echo get_the_post_thumbnail( get_the_ID(), 'commentpress-feature' );
		
		?>
		<div class="cp_featured_title">
			<div class="cp_featured_title_inner">
				
				<?php if ( is_page() ) { ?>
				
					<?php if ( commentpress_get_post_title_visibility( get_the_ID() ) ) { ?>
					<h2 class="post_title"><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>
					<?php } ?>

					<?php if ( commentpress_get_post_meta_visibility( get_the_ID() ) ) { ?>
					<div class="search_meta">
						<?php commentpress_echo_post_meta(); ?>
					</div>
					<?php } ?>
				
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
 * @description: utility to test for feature image, because has_post_thumbnail() fails sometimes
 * @see http://codex.wordpress.org/Function_Reference/has_post_thumbnail
 */
function commentpress_has_feature_image() {
	
	// replacement check
	if ( '' != get_the_post_thumbnail() ) { 
		return true;
	}
	
	// --<
	return false;
	
}





