<?php /*
================================================================================
Class CommentpressCore
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====



--------------------------------------------------------------------------------
*/



/*
================================================================================
Class Name
================================================================================
*/

class CommentpressCore {



	/**
	 * Properties
	 */

	// database object
	public $db;

	// display object
	public $display;

	// nav object
	public $nav;

	// parser object
	public $parser;

	// formatter object
	public $formatter;

	// workflow object
	public $workflow;

	// front-end editor object
	public $editor;

	// options page
	public $options_page;

	// buddypress present
	public $buddypress = false;

	// bp-groupblog present
	public $bp_groupblog = false;



	/**
	 * Initialises this object
	 *
	 * @return object
	 */
	function __construct() {

		// init
		$this->_init();

		//$this->groupblog_theme_is_set();

		// --<
		return $this;

	}



	/**
	 * If needed, destroys this object
	 *
	 * @return void
	 */
	public function destroy() {

		// nothing

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Public Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Runs when plugin is activated
	 *
	 * @return void
	 */
	public function activate() {

		// initialise display - sets the theme
		$this->display->activate();

		// initialise database
		$this->db->activate();

	}



	/**
	 * Runs when plugin is deactivated
	 *
	 * @return void
	 */
	public function deactivate() {

		// call database destroy method
		$this->db->deactivate();

		// call display destroy method
		$this->display->deactivate();

	}



	/**
	 * Utility that fires an action when CommentPress has loaded
	 *
	 * @return void
	 */
	public function broadcast() {

		// broadcast
		do_action( 'commentpress_loaded' );

	}



	/**
	 * Loads translation, if present
	 *
	 * @return void
	 */
	public function translation() {

		// only use, if we have it...
		if( function_exists('load_plugin_textdomain') ) {

			// not used, as there are no translations as yet
			load_plugin_textdomain(

				// unique name
				'commentpress-core',

				// deprecated argument
				false,

				// relative path to directory containing translation files
				dirname( plugin_basename( COMMENTPRESS_PLUGIN_FILE ) ) . '/languages/'

			);

		}

	}



	/**
	 * Called when BuddyPress is active
	 *
	 * @return void
	 */
	public function buddypress_init() {

		// we've got BuddyPress installed
		$this->buddypress = true;

	}



	/**
	 * Configure when BuddyPress is loaded
	 *
	 * @return void
	 */
	public function buddypress_globals_loaded() {

		// for bp-groupblog integration...
		if (

			// require multisite
			is_multisite()

			// and groups
			AND bp_is_active( 'groups' )

			// and bp-groupblog
			AND defined( 'BP_GROUPBLOG_IS_INSTALLED' )

		) {

			// check if this blog is a group blog...
			$group_id = get_groupblog_group_id( get_current_blog_id() );
			if ( is_numeric( $group_id ) ) {

				// okay, we're properly configured
				$this->bp_groupblog = true;

			}

		}

	}



	/**
	 * Is BuddyPress active?
	 *
	 * @return bool $buddypress True when BuddyPress active, false otherwise
	 */
	public function is_buddypress() {

		// --<
		return $this->buddypress;

	}



	/**
	 * Is this blog a BuddyPress Group Blog?
	 *
	 * @return bool $bp_groupblog True when current blog is a BP Groupblog, false otherwise
	 */
	public function is_groupblog() {

		// --<
		return $this->bp_groupblog;

	}



	/**
	 * Is a BP Group Blog theme set?
	 *
	 * @return array $theme An array describing the theme
	 */
	public function get_groupblog_theme() {

		// kick out if not in a group context
		if ( ! function_exists( 'bp_is_groups_component' ) ) { return false; }
		if ( ! bp_is_groups_component() ) { return false; }

		// get groupblog options
		$options = get_site_option( 'bp_groupblog_blog_defaults_options' );

		// get theme setting
		if ( ! empty( $options['theme'] ) ) {

			// we have a groupblog theme set

			// split the options
			list( $stylesheet, $template ) = explode( "|", $options['theme'] );

			// test for WP3.4...
			if ( function_exists( 'wp_get_theme' ) ) {

				// get the registered theme
				$theme = wp_get_theme( $stylesheet );

				// test if it's a CommentPress Core theme
				if ( in_array( 'commentpress', (array) $theme->Tags ) ) {

					// --<
					return array( $stylesheet, $template );

				}

			} else {

				// get the registered theme
				$theme = get_theme( $stylesheet );

				// test if it's a CommentPress Core theme
				if ( in_array( 'commentpress', (array) $theme['Tags'] ) ) {

					// --<
					return array( $stylesheet, $template );

				}

			}

		}

		// --<
		return false;

	}



	/**
	 * Is this a BuddyPress "special page" - a component homepage?
	 *
	 * @return bool $is_bp True if current page is a BuddyPress page, false otherwise
	 */
	public function is_buddypress_special_page() {

		// kick out if not BP
		if ( ! $this->is_buddypress() ) {

			return false;

		}

		// is it a BP page?
		$is_bp = ! bp_is_blog_page();

		// let's see...
		return apply_filters( 'cp_is_buddypress_special_page', $is_bp );

	}



	/**
	 * Utility to add a message to admin pages when upgrade required
	 *
	 * @return void
	 */
	public function admin_upgrade_alert() {

		// sanity check function exists
		if ( function_exists('current_user_can') ) {

			// check user permissions
			if ( current_user_can('manage_options') ) {

				// show it
				echo '<div id="message" class="error"><p>' . __( 'CommentPress Core has been updated. Please visit the ' ) . '<a href="options-general.php?page=commentpress_admin">' . __( 'Settings Page', 'commentpress-core' ) . '</a>.</p></div>';

			}

		}

	}



	/**
	 * Appends option to admin menu
	 *
	 * @return void
	 */
	public function admin_menu() {

		// sanity check function exists
		if ( function_exists('current_user_can') ) {

			// check user permissions
			if ( current_user_can('manage_options') ) {

				// try and update options
				$saved = $this->db->options_update();

				// if upgrade required...
				if ( $this->db->check_upgrade() ) {

					// access globals
					global $pagenow;

					// show on pages other than the CP admin page
					if (

						$pagenow == 'options-general.php'
						AND ! empty( $_GET['page'] )
						AND 'commentpress_admin' == $_GET['page']

					) {

						// we're on our admin page

					} else {

						// show message
						add_action( 'admin_notices', array( $this, 'admin_upgrade_alert' ) );

					}

				}

				// insert item in relevant menu
				$this->options_page = add_options_page(

					__( 'CommentPress Core Settings', 'commentpress-core' ),
					__( 'CommentPress Core', 'commentpress-core' ),
					'manage_options',
					'commentpress_admin',
					array( $this, 'options_page' )

				);

				//print_r( $this->options_page );die();

				// add scripts and styles
				add_action( 'admin_print_scripts-' . $this->options_page, array( $this, 'admin_js' ) );
				add_action( 'admin_print_styles-' . $this->options_page, array( $this, 'admin_css' ) );
				add_action( 'admin_head-' . $this->options_page, array( $this, 'admin_head' ), 50 );

			}

		}

	}



	/**
	 * Prints plugin options page header
	 *
	 * @return void
	 */
	public function admin_head() {

		// get admin javascript
		echo $this->display->get_admin_js();

		// there's a new screen object for help in 3.3
		global $wp_version;
		if ( version_compare( $wp_version, '3.2.99999', '>=' ) ) {

			$screen = get_current_screen();
			//print_r( $screen ); die();

			// use method in this class
			$this->options_help( $screen );

		}

		// do we have a custom header bg colour?
		if ( $this->db->option_get_header_bg() != $this->db->header_bg_colour ) {

			// echo inline style
			echo '

			<style type="text/css">

				#book_header {
					background: #' . $this->db->option_get_header_bg() . ';
				}

			</style>

			';

		}

	}



	/**
	 * Enqueue plugin options page css
	 *
	 * @return void
	 */
	public function admin_css() {

		// enqueue farbtastic
		wp_enqueue_style( 'farbtastic' );

		// add admin stylesheet
		wp_enqueue_style(

			'commentpress_admin_css',
			plugins_url( 'commentpress-core/assets/css/admin.css', COMMENTPRESS_PLUGIN_FILE ),
			false,
			COMMENTPRESS_VERSION, // version
			'all' // media

		);

	}



	/**
	 * Enqueue plugin options page javascript
	 *
	 * @return void
	 */
	public function admin_js() {

		// enqueue farbtastic
		wp_enqueue_script( 'farbtastic' );

	}



	/**
	 * Prints plugin options page
	 *
	 * @return void
	 */
	public function options_page() {

		// sanity check function exists
		if ( function_exists( 'current_user_can' ) ) {

			// check user permissions
			if ( current_user_can( 'manage_options' ) ) {

				// get our admin options page
				echo $this->display->get_admin_page();

			}

		}

	}



	/**
	 * Add scripts needed across all WP admin pages
	 *
	 * @return void
	 */
	public function enqueue_admin_scripts() {

		// add quicktag button to page editor
		$this->display->get_custom_quicktags();

	}



	/**
	 * Adds script libraries
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		// don't include in admin or wp-login.php
		if ( is_admin() OR ( isset( $GLOBALS['pagenow'] ) AND 'wp-login.php' == $GLOBALS['pagenow'] ) ) {

			return;

		}

		// test for mobile user agents
		$this->db->test_for_mobile();

		// add jQuery libraries
		$this->display->get_jquery();

		// if comments are enabled on this post/page
		if ( $this->db->comments_enabled() ) {

			// add tinyMCE scripts
			$this->display->get_tinymce();

		}

	}



	/**
	 * Adds CSS
	 *
	 * @return void
	 */
	public function enqueue_styles() {

		// add plugin styles
		$this->display->get_frontend_styles();

	}



	/**
	 * Redirect to child page
	 *
	 * @return void
	 */
	public function redirect_to_child() {

		// do redirect
		$this->nav->redirect_to_child();

	}



	/**
	 * Inserts plugin-specific header items
	 *
	 * @param str $headers
	 * @return void
	 */
	public function head( $headers ) {

		// do we have navigation?
		if ( is_single() OR is_page() OR is_attachment() ) {

			// initialise nav
			$this->nav->initialise();

		}

	}



	/**
	 * Parses page/post content
	 *
	 * @param str $content The content of the page/post
	 * @return str $content The modified content
	 */
	public function the_content( $content ) {

		// reference our post
		global $post;

		// compat with Subscribe to Comments Reloaded
		if( $this->is_subscribe_to_comments_reloaded_page() ) return $content;

		// compat with Theme My Login
		if( $this->is_theme_my_login_page() ) return $content;

		// compat with Members List plugin
		if( $this->is_members_list_page() ) return $content;

		// test for buddypress special page (compat with BP Docs)
		if ( $this->is_buddypress() ) {

			// is it a component homepage?
			if ( $this->is_buddypress_special_page() ) {

				// --<
				return $content;

			}

		}

		// init allowed
		$allowed = false;

		// only parse posts or pages...
		if( ( is_single() OR is_page() OR is_attachment() ) AND ! $this->db->is_special_page() ) {
			$allowed = true;
		}

		// if allowed, parse
		if ( apply_filters( 'commentpress_force_the_content', $allowed ) ) {

			// delegate to parser
			$content = $this->parser->the_content( $content );

		}

		/**
		 * The following fails with JetPack 2.7, which parses content in the head
		 * to create content summaries. I now can't remember why I was being so
		 * cautious about not parsing twice, but since JetPack is so useful and
		 * common, I'm commenting this out until I get reports that something
		 * odd is happening
		 */

		// only parse content once
		//remove_filter( 'the_content', array( $this, 'the_content' ), 20 );



		// --<
		return $content;

	}



	/**
	 * Retrieves option for displaying TOC
	 *
	 * @return mixed $result
	 */
	public function get_list_option() {

		// get list option flag
		$result = $this->db->option_get( 'cp_show_posts_or_pages_in_toc' );

		// --<
		return $result;
	}



	/**
	 * Retrieves minimise all button
	 *
	 * @param str $sidebar The type of sidebar (comments, toc, activity)
	 * @return str $result The HTML for minimise button
	 */
	public function get_minimise_all_button( $sidebar = 'comments' ) {

		// get minimise image
		$result = $this->display->get_minimise_all_button( $sidebar );

		// --<
		return $result;
	}



	/**
	 * Retrieves header minimise button
	 *
	 * @return str $result The HTML for minimise button
	 */
	public function get_header_min_link() {

		// get minimise image
		$result = $this->display->get_header_min_link();

		// --<
		return $result;

	}



	/**
	 * Retrieves text_signature hidden input
	 *
	 * @return str $result The HTML input
	 */
	public function get_signature_field() {

		// init text signature
		$text_sig = '';

		// get comment ID to reply to from URL query string
		$reply_to_comment_id = isset($_GET['replytocom']) ? (int) $_GET['replytocom'] : 0;

		// did we get a comment ID?
		if ( $reply_to_comment_id != 0 ) {

			// get paragraph text signature
			$text_sig = $this->db->get_text_signature_by_comment_id( $reply_to_comment_id );

		} else {

			// do we have a paragraph number in the query string?
			$reply_to_para_id = isset($_GET['replytopara']) ? (int) $_GET['replytopara'] : 0;

			// did we get a comment ID?
			if ( $reply_to_para_id != 0 ) {

				// get paragraph text signature
				$text_sig = $this->get_text_signature( $reply_to_para_id );

			}

		}

		// get constructed hidden input for comment form
		$result = $this->display->get_signature_input( $text_sig );

		// --<
		return $result;
	}



	/**
	 * Add reserved names
	 *
	 * @param array $reserved_names The existing list of illegal names
	 * @return array $reserved_names The modified list of illegal names
	 */
	public function add_reserved_names( $reserved_names ) {

		// get all image attachments to our title page
		$reserved_names = array_merge(

			$reserved_names,

			array(

				'title-page',
				'general-comments',
				'all-comments',
				'comments-by-commenter',
				'table-of-contents',
				'author', // not currently used
				'login', // for Theme My Login

			)

		);

		// --<
		return $reserved_names;

	}



	/**
	 * Add sidebar to signup form
	 *
	 * @return void
	 */
	public function after_signup_form() {

		// add sidebar
		get_sidebar();

	}



	/**
	 * Adds meta boxes to admin screens
	 *
	 * @return void
	 */
	public function add_meta_boxes() {

		// add our meta boxes to pages
		add_meta_box(

			'commentpress_page_options',
			__( 'CommentPress Core Options', 'commentpress-core' ),
			array( $this, 'custom_box_page' ),
			'page',
			'side'

		);

		// add our meta box to posts
		add_meta_box(

			'commentpress_post_options',
			__( 'CommentPress Core Options', 'commentpress-core' ),
			array( $this, 'custom_box_post' ),
			'post',
			'side'

		);

		// get workflow
		$_workflow = $this->db->option_get( 'cp_blog_workflow' );

		// if it's enabled...
		if ( $_workflow == '1' ) {

			// init title
			$title = __( 'Workflow', 'commentpress-core' );

			// allow overrides
			$title = apply_filters( 'cp_workflow_metabox_title', $title );

			// add our meta box to posts
			add_meta_box(

				'commentpress_workflow_fields',
				$title,
				array( $this, 'custom_box_workflow' ),
				'post',
				'normal'

			);

			// add our meta box to pages
			add_meta_box(

				'commentpress_workflow_fields',
				$title,
				array( $this, 'custom_box_workflow' ),
				'page',
				'normal'

			);

		}

	}



	/**
	 * Adds meta box to page edit screens
	 *
	 * @return void
	 */
	public function custom_box_page() {

		// access post
		global $post;

		// Use nonce for verification
		wp_nonce_field( 'commentpress_page_settings', 'commentpress_nonce' );

		// ---------------------------------------------------------------------
		// Show or Hide Page Title
		// ---------------------------------------------------------------------

		// show a title
		echo '<div class="cp_title_visibility_wrapper">
		<p><strong><label for="cp_title_visibility">' . __( 'Page Title Visibility' , 'commentpress-core' ) . '</label></strong></p>';

		// set key
		$key = '_cp_title_visibility';

		// default to show
		$viz = $this->db->option_get( 'cp_title_visibility' );

		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// get it
			$viz = get_post_meta( $post->ID, $key, true );

		}

		// select
		echo '
		<p>
		<select id="cp_title_visibility" name="cp_title_visibility">
			<option value="show" ' . (($viz == 'show') ? ' selected="selected"' : '') . '>' . __('Show page title', 'commentpress-core') . '</option>
			<option value="hide" ' . (($viz == 'hide') ? ' selected="selected"' : '') . '>' . __('Hide page title', 'commentpress-core') . '</option>
		</select>
		</p>
		</div>
		';

		// ---------------------------------------------------------------------
		// Show or Hide Page Meta
		// ---------------------------------------------------------------------

		// show a label
		echo '<div class="cp_page_meta_visibility_wrapper">
		<p><strong><label for="cp_page_meta_visibility">' . __( 'Page Meta Visibility' , 'commentpress-core' ) . '</label></strong></p>';

		// set key
		$key = '_cp_page_meta_visibility';

		// default to show
		$viz = $this->db->option_get( 'cp_page_meta_visibility' );

		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// get it
			$viz = get_post_meta( $post->ID, $key, true );

		}

		// select
		echo '
		<p>
		<select id="cp_page_meta_visibility" name="cp_page_meta_visibility">
			<option value="show" ' . (($viz == 'show') ? ' selected="selected"' : '') . '>' . __('Show page meta', 'commentpress-core') . '</option>
			<option value="hide" ' . (($viz == 'hide') ? ' selected="selected"' : '') . '>' . __('Hide page meta', 'commentpress-core') . '</option>
		</select>
		</p>
		</div>
		';

		// ---------------------------------------------------------------------
		// Page Numbering - only shown on first top level page
		// ---------------------------------------------------------------------
		//print_r( $this->nav->get_first_page() ); die();

		// if page has no parent and it's not a special page and it's the first...
		if (

			$post->post_parent == '0' AND
			! $this->db->is_special_page() AND
			$post->ID == $this->nav->get_first_page()

		) { // -->

			// label
			echo '<div class="cp_number_format_wrapper">
			<p><strong><label for="cp_number_format">' . __('Page Number Format', 'commentpress-core' ) . '</label></strong></p>';

			// set key
			$key = '_cp_number_format';

			// default to arabic
			$format = 'arabic';

			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// get it
				$format = get_post_meta( $post->ID, $key, true );

			}

			//print_r( $format ); die();

			// select
			echo '
			<p>
			<select id="cp_number_format" name="cp_number_format">
				<option value="arabic" ' . (($format == 'arabic') ? ' selected="selected"' : '') . '>' . __('Arabic numerals', 'commentpress-core' ) . '</option>
				<option value="roman" ' . (($format == 'roman') ? ' selected="selected"' : '') . '>' . __('Roman numerals', 'commentpress-core' ) . '</option>
			</select>
			</p>
			</div>
			';

		}

		// ---------------------------------------------------------------------
		// Page Layout for Title Page -> to allow for Book Cover image
		// ---------------------------------------------------------------------

		// is this the title page?
		if ( $post->ID == $this->db->option_get( 'cp_welcome_page' ) ) {

			// label
			echo '<div class="cp_page_layout_wrapper">
			<p><strong><label for="cp_page_layout">' . __('Page Layout', 'commentpress-core' ) . '</label></strong></p>';

			// set key
			$key = '_cp_page_layout';

			// default to text
			$value = 'text';

			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// get it
				$value = get_post_meta( $post->ID, $key, true );

			}

			// select
			echo '
			<p>
			<select id="cp_page_layout" name="cp_page_layout">
				<option value="text" ' . (($value == 'text') ? ' selected="selected"' : '') . '>' . __('Standard', 'commentpress-core' ) . '</option>
				<option value="wide" ' . (($value == 'wide') ? ' selected="selected"' : '') . '>' . __('Wide', 'commentpress-core' ) . '</option>
			</select>
			</p>
			</div>
			';

		}

		// get post formatter
		$this->_get_post_formatter_metabox( $post );

		// get default sidebar
		$this->_get_default_sidebar_metabox( $post );

		// get starting para number
		$this->_get_para_numbering_metabox( $post );

	}



	/**
	 * Adds meta box to post edit screens
	 *
	 * @return void
	 */
	public function custom_box_post() {

		// access post
		global $post;

		// Use nonce for verification
		wp_nonce_field( 'commentpress_post_settings', 'commentpress_nonce' );

		// set key
		$key = '_cp_newer_version';

		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// get it
			$new_post_id = get_post_meta( $post->ID, $key, true );

			// -----------------------------------------------------------------
			// Show link to newer post
			// -----------------------------------------------------------------

			// define label
			$label = __( 'This post already has a new version', 'commentpress-core' );

			// get the edit post link
			$edit_link = get_edit_post_link( $new_post_id );

			// define label
			$link = __( 'Edit new version', 'commentpress-core' );

			// show link
			echo '
			<p><a href="' . $edit_link . '">' . $link . '</a></p>' . "\n";

		} else {

			// -----------------------------------------------------------------
			// Create new post with content of current post
			// -----------------------------------------------------------------

			// label
			echo '<p><strong><label for="cp_page_layout">' . __('Versioning', 'commentpress-core' ) . '</label></strong></p>';

			// define label
			$label = __( 'Create new version', 'commentpress-core' );

			// show a title
			echo '
			<div class="checkbox">
				<label for="commentpress_new_post"><input type="checkbox" value="1" id="commentpress_new_post" name="commentpress_new_post" /> ' . $label . '</label>
			</div>' . "\n";

		}

		// get post formatter
		$this->_get_post_formatter_metabox( $post );

		// get default sidebar
		$this->_get_default_sidebar_metabox( $post );

	}



	/**
	 * Adds workflow meta box to post edit screens
	 *
	 * @return void
	 */
	public function custom_box_workflow() {

		// we now need to add any workflow that a plugin might want
		do_action( 'cp_workflow_metabox' );

	}



	/**
	 * Adds help copy to admin page in WP <= 3.2
	 *
	 * @param str $text The existing help text
	 * @return str $text The modified help text
	 */
	public function contextual_help( $text ) {

		$text = '';
		$screen = isset( $_GET['page'] ) ? $_GET['page'] : '';
		if ($screen == 'commentpress_admin') {

			// get help text
			$text = '<h5>' . __('CommentPress Core Help', 'commentpress-core' ) . '</h5>';
			$text .= $this->display->get_help();

		}

		// --<
		return $text;

	}



	/**
	 * Adds help copy to admin page in WP3.3+
	 *
	 * @param object $screen The existing screen object
	 * @return object $screen The modified screen object
	 */
	public function options_help( $screen ) {

		//print_r( $screen ); die();

		// is this our screen?
		if ( $screen->id != $this->options_page ) {

			// no, kick out
			return;

		}

		// add a tab
		$screen->add_help_tab( array(

			'id'      => 'commentpress-base',
			'title'   => __('CommentPress Core Help', 'commentpress-core'),
			'content' => $this->display->get_help(),

		));

		// --<
		return $screen;

	}



	/**
	 * Stores our additional params
	 *
	 * @param int $post_id The numeric ID of the post (or revision)
	 * @param object $post The post object
	 * @return void
	 */
	public function save_post( $post_id, $post ) {

		// we don't use post_id because we're not interested in revisions

		// store our meta data
		$result = $this->db->save_meta( $post );

	}



	/**
	 * Check for data integrity of other posts when one is deleted
	 *
	 * @param int $post_id The numeric ID of the post (or revision)
	 */
	public function delete_post( $post_id ) {

		// store our meta data
		$result = $this->db->delete_meta( $post_id );

	}



	/**
	 * Stores our additional param - the text signature
	 *
	 * @param int $comment_ID The numeric ID of the comment
	 * @param str $comment_status The status of the comment
	 * @return void
	 */
	public function save_comment( $comment_ID, $comment_status ) {

		// store our comment signature
		$result = $this->db->save_comment_signature( $comment_ID );

		// store our comment selection
		$result = $this->db->save_comment_selection( $comment_ID );

		// in multipage situations, store our comment's page
		$result = $this->db->save_comment_page( $comment_ID );

		// has the comment been marked as spam?
		if ( $comment_status === 'spam' ) {

			// yes - let the commenter know without throwing an AJAX error
			wp_die( __( 'This comment has been marked as spam. Please contact a site administrator.',  'commentpress-core' ) );

		}

	}



	/**
	 * Get table of contents
	 *
	 * @return void
	 */
	public function get_toc() {

		// switch pages or posts
		if( $this->get_list_option() == 'post' ) {

			// list posts
			$this->display->list_posts();

		} else {

			// list pages
			$this->display->list_pages();

		}

	}



	/**
	 * Get table of contents list
	 *
	 * @return void
	 */
	public function get_toc_list( $exclude_pages = array() ) {

		// switch pages or posts
		if( $this->get_list_option() == 'post' ) {

			// list posts
			$this->display->list_posts();

		} else {

			// list pages
			$this->display->list_pages( $exclude_pages );

		}

	}



	/**
	 * Exclude special pages from page listings
	 *
	 * @param array $excluded_array The existing list of excluded pages
	 * @return array $excluded_array The modified list of excluded pages
	 */
	public function exclude_special_pages( $excluded_array ) {

		//print_r( $excluded_array ); die();

		// get special pages array, if it's there
		$special_pages = $this->db->option_get( 'cp_special_pages' );

		// do we have an array?
		if ( is_array( $special_pages ) ) {

			// merge and make unique
			$excluded_array = array_unique( array_merge( $excluded_array, $special_pages ) );

		}

		// --<
		return $excluded_array;

	}



	/**
	 * Exclude special pages from admin page listings
	 *
	 * @param array $query The existing page query
	 * @return void
	 */
	public function exclude_special_pages_from_admin( $query ) {

		//print_r( $query ); die();

		global $pagenow, $post_type;

		// check admin location
		if ( is_admin() AND $pagenow=='edit.php' AND $post_type =='page' ) {

			// get special pages array, if it's there
			$special_pages = $this->db->option_get( 'cp_special_pages' );

			// do we have an array?
			if ( is_array( $special_pages ) AND count( $special_pages ) > 0 ) {

				// modify query
				$query->query_vars['post__not_in'] = $special_pages;

			}

		}

	}



	/**
	 * Page counts still need amending
	 *
	 * @param array $vars The existing variables
	 * @return array $vars The modified list of variables
	 */
	public function update_page_counts_in_admin( $vars ) {

		//print_r( $vars ); die();

		global $pagenow, $post_type;

		// check admin location
		if (is_admin() AND $pagenow=='edit.php' AND $post_type =='page') {

			// get special pages array, if it's there
			$special_pages = $this->db->option_get( 'cp_special_pages' );

			// do we have an array?
			if ( is_array( $special_pages ) ) {

				/**
				 * Data comes in like this:
				 * [all] => <a href='edit.php?post_type=page' class="current">All <span class="count">(8)</span></a>
				 * [publish] => <a href='edit.php?post_status=publish&amp;post_type=page'>Published <span class="count">(8)</span></a>
				 */

				// capture existing value enclosed in brackets
				preg_match( '/\((\d+)\)/', $vars['all'], $matches );
				//print_r( $matches ); die();

				// did we get a result?
				if ( isset( $matches[1] ) ) {

					// subtract special page count
					$new_count = $matches[1] - count( $special_pages );

					// rebuild 'all' and 'publish' items
					$vars['all'] = preg_replace(

						'/\(\d+\)/',
						'(' . $new_count . ')',
						$vars['all']

					);

				}

				// capture existing value enclosed in brackets
				preg_match( '/\((\d+)\)/', $vars['publish'], $matches );
				//print_r( $matches ); die();

				// did we get a result?
				if ( isset( $matches[1] ) ) {

					// subtract special page count
					$new_count = $matches[1] - count( $special_pages );

					// rebuild 'all' and 'publish' items
					$vars['publish'] = preg_replace(

						'/\(\d+\)/',
						'(' . $new_count . ')',
						$vars['publish']

					);

				}

			}

		}

		// --<
		return $vars;

	}



	/**
	 * Get comments sorted by text signature and paragraph
	 *
	 * @param int $post_ID The numeric ID of the post
	 * @return array $_comments An array of sorted comment data
	 */
	public function get_sorted_comments( $post_ID ) {

		// --<
		return $this->parser->get_sorted_comments( $post_ID );

	}



	/**
	 * Get paragraph number for a particular text signature
	 *
	 * @param str $text_signature The text signature
	 * @return int $num The position in text signature array
	 */
	public function get_para_num( $text_signature ) {

		// get position in array
		$num = array_search( $text_signature, $this->db->get_text_sigs() );

		// --<
		return $num + 1;

	}



	/**
	 * Get text signature for a particular paragraph number
	 *
	 * @param int $para_num The paragraph number in a post
	 * @return str $text_signature The text signature
	 */
	public function get_text_signature( $para_num ) {

		// get text sigs
		$_sigs = $this->db->get_text_sigs();

		// get value at that position in array
		$text_sig = ( isset( $_sigs[$para_num-1] ) ) ? $_sigs[$para_num-1] : '';

		// --<
		return $text_sig;

	}



	/**
	 * Get a link to a "special" page
	 *
	 * @param str $page_type The CommentPress Core name of a special page
	 * @return str $link THe HTML link to that page
	 */
	public function get_page_link( $page_type = 'cp_all_comments_page' ) {

		// access globals
		global $post;

		// init
		$link = '';

		// get page ID
		$_page_id = $this->db->option_get( $page_type );

		// do we have a page?
		if ( $_page_id != '' ) {

			// get page
			$_page = get_post( $_page_id );

			$_active = '';

			// is it the current page?
			if ( isset( $post ) AND $_page->ID == $post->ID ) {

				$_active = ' class="active_page"';

			}

			// get link
			$_url = get_permalink( $_page );

			// switch title by type
			switch( $page_type ) {

				case 'cp_welcome_page':
					$_link_title = __( 'Title Page', 'commentpress-core' );
					$_button = 'cover';
					break;

				case 'cp_all_comments_page':
					$_link_title = __( 'All Comments', 'commentpress-core' );
					$_button = 'allcomments'; break;

				case 'cp_general_comments_page':
					$_link_title = __( 'General Comments', 'commentpress-core' );
					$_button = 'general'; break;

				case 'cp_blog_page':
					$_link_title = __( 'Blog', 'commentpress-core' );
					if ( is_home() ) { $_active = ' class="active_page"'; }
					$_button = 'blog'; break;

				case 'cp_blog_archive_page':
					$_link_title = __( 'Blog Archive', 'commentpress-core' );
					$_button = 'archive'; break;

				case 'cp_comments_by_page':
					$_link_title = __( 'Comments by Commenter', 'commentpress-core' );
					$_button = 'members'; break;

				default:
					$_link_title = __( 'Members', 'commentpress-core' );
					$_button = 'members';

			}

			// let plugins override titles
			$_title = apply_filters( 'commentpress_page_link_title', $_link_title );

			// show link
			$link = '<li' . $_active . '><a href="' . $_url . '" id="btn_' . $_button . '" class="css_btn" title="' . $_title . '">' . $_title . '</a></li>' . "\n";

		}

		// --<
		return $link;

	}



	/**
	 * Get the URL for a "special" page
	 *
	 * @param str $page_type The CommentPress Core name of a special page
	 * @return str $_url The URL of that page
	 */
	public function get_page_url( $page_type = 'cp_all_comments_page' ) {

		// init
		$_url = '';

		// get page ID
		$_page_id = $this->db->option_get( $page_type );

		// do we have a page?
		if ( $_page_id != '' ) {

			// get page
			$_page = get_post( $_page_id );

			// get link
			$_url = get_permalink( $_page );

		}

		// --<
		return $_url;

	}



	/**
	 * Get book cover
	 *
	 * @param str The markup for the "cover"
	 */
	public function get_book_cover() {

		// get image SRC
		$src = $this->db->option_get( 'cp_book_picture' );

		// get link URL
		$url = $this->db->option_get( 'cp_book_picture_link' );

		// --<
		return $this->display->get_linked_image( $src, $url );

	}



	/**
	 * Check if we are on the signup page
	 *
	 * @return boolean $is_signup True if signup page, false otherwise
	 * @todo:
	 *
	 */
	public function is_signup_page() {

		// init
		$is_signup = false;

		// if multisite
		if ( is_multisite() ) {

			// test script filename
			if ( 'wp-signup.php' == basename($_SERVER['SCRIPT_FILENAME']) ) {

				// override
				$is_signup = true;

			}

		}

		// --<
		return $is_signup;

	}



	/**
	 * Utility to check for presence of Theme My Login
	 *
	 * @return bool $success True if TML page, false otherwise
	 * @todo:
	 *
	 */
	public function is_theme_my_login_page() {

		// access page
		global $post;

		// compat with Theme My Login
		if(

			is_page() AND
			! $this->db->is_special_page() AND
			$post->post_name == 'login' AND
			$post->post_content == '[theme-my-login]'

		) {

			// --<
			return true;

		}

		// --<
		return false;

	}



	/**
	 * Utility to check for presence of Members List
	 *
	 * @return bool $success True if is Members List page, false otherwise
	 */
	public function is_members_list_page() {

		// access page
		global $post;

		// compat with Members List
		if(

			is_page() AND
			! $this->db->is_special_page() AND
			( strstr( $post->post_content, '[members-list' ) !== false )

		) {

			// --<
			return true;

		}

		// --<
		return false;

	}



	/**
	 * Utility to check for presence of Subscribe to Comments Reloaded
	 *
	 * @return bool $success True if "Subscribe to Comments Reloaded" page, false otherwise
	 */
	public function is_subscribe_to_comments_reloaded_page() {

		// access page
		global $post;

		// compat with Subscribe to Comments Reloaded
		if(

			is_page() AND
			! $this->db->is_special_page() AND
			$post->ID == '9999999' AND
			$post->guid == get_bloginfo('url') . '/?page_id=9999999'

		) {

			// --<
			return true;

		}

		// --<
		return false;

	}



	/**
	 * Override the comment reply script that BP Docs loads
	 *
	 * @return void
	 */
	public function bp_docs_loaded() {

		// dequeue offending script (after BP Docs runs its enqueuing)
		add_action( 'wp_enqueue_scripts', array( $this, 'bp_docs_dequeue_scripts' ), 20 );

	}



	/**
	 * Override the comment reply script that BP Docs loads
	 *
	 * @return void
	 */
	public function bp_docs_dequeue_scripts() {

		// dequeue offending script
		wp_dequeue_script( 'comment-reply' );

	}



	/**
	 * Override the comments tempate for BP Docs
	 *
	 * @param str $path The existing path to the template
	 * @param str $original_path The original path to the template
	 * @return str $path The modified path to the template
	 */
	public function bp_docs_comment_tempate( $path, $original_path ) {

		// if on BP root site
		if ( bp_is_root_blog() ) {

			// override default link name
			return $original_path;

		}

		// pass through
		return $path;

	}



	/**
	 * Override the Featured Comments behaviour
	 *
	 * @return void
	 */
	public function featured_comments_override() {

		// is the plugin available?
		if ( function_exists( 'wp_featured_comments_load' ) ) {

			// get instance
			$fc = wp_featured_comments_load();

			// remove comment_text filter
			remove_filter( 'comment_text', array( $fc, 'comment_text' ), 10 );

			// get the plugin markup in the comment edit section
			add_filter( 'cp_comment_edit_link', array( $this, 'featured_comments_markup' ), 100, 2 );

		}

	}



	/**
	 * Get the Featured Comments link markup
	 *
	 * @param str $editlink The existing HTML link
	 * @param array $comment The comment data
	 * @return str $editlink The modified HTML link
	 */
	public function featured_comments_markup( $editlink, $comment ) {

		// is the plugin available?
		if ( function_exists( 'wp_featured_comments_load' ) ) {

			// get instance
			$fc = wp_featured_comments_load();

			// get markup
			return $editlink . $fc->comment_text( '' );

		}

		// --<
		return $editlink;

	}



	/**
	 * Return the name of the default sidebar
	 *
	 * @return str $return The code for the default sidebar
	 */
	public function get_default_sidebar() {

		// set sensible default
		$return = 'toc';

		// is this a commentable page?
		if ( ! $this->is_commentable() ) {

			// no - we must use either 'activity' or 'toc'
			if ( $this->db->option_exists( 'cp_sidebar_default' ) ) {

				// get option (we don't need to look at the page meta in this case)
				$default = $this->db->option_get( 'cp_sidebar_default' );

				// use it unless it's 'comments'
				if ( $default != 'comments' ) { $return = $default; }

			}

			// --<
			return $return;

		}

		// get CPTs
		//$_types = $this->_get_commentable_cpts();

		// testing what we do with CPTs...
		//if ( is_singular() OR is_singular( $_types ) ) {

		// is it a commentable page?
		if ( is_singular() ) {

			// some people have reported that db is not an object at this point -
			// though I cannot figure out how this might be occurring - so we
			// avoid the issue by checking if it is
			if ( is_object( $this->db ) ) {

				// is it a special page which have comments in page (or are not commentable)?
				if ( ! $this->db->is_special_page() ) {

					// access page
					global $post;

					// is it our title page?
					if ( $post->ID == $this->db->option_get( 'cp_welcome_page' ) ) {

						// use 'toc', but should this be a special case?
						return 'toc';

					} else {

						// either 'comments', 'activity' or 'toc'
						if ( $this->db->option_exists( 'cp_sidebar_default' ) ) {

							// get global option
							$return = $this->db->option_get( 'cp_sidebar_default' );

							// check if the post/page has a meta value
							$key = '_cp_sidebar_default';

							// if the custom field already has a value...
							if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

								// get it
								$return = get_post_meta( $post->ID, $key, true );

							}


						}

						// --<
						return $return;

					}

				}

			}

		}

		// not singular... must be either activity or toc
		if ( $this->db->option_exists( 'cp_sidebar_default' ) ) {

			// override
			$default = $this->db->option_get( 'cp_sidebar_default' );

			// use it unless it's 'comments'
			if ( $default != 'comments' ) { $return = $default; }

		}

		// --<
		return $return;

	}



	/**
	 * Get the order of the sidebars
	 *
	 * @return array $order Sidebars in order of display
	 */
	public function get_sidebar_order() {

		// set default but allow overrides
		$order = apply_filters(

			// hook name
			'cp_sidebar_tab_order',

			// default order
			array( 'contents', 'comments', 'activity' )

		);

		// --<
		return $order;

	}



	/**
	 * Check if a page/post can be commented on
	 *
	 * @return bool $is_commentable True if commentable, false otherwise
	 */
	public function is_commentable() {

		// declare access to globals
		global $post;

		// not if we're not on a page/post and especially not if there's no post object
		if ( ! is_singular() OR ! is_object( $post ) ) { return false; }

		// CP Special Pages special pages are not
		if ( $this->db->is_special_page() ) { return false; }

		// BuddyPress special pages are not
		if ( $this->is_buddypress_special_page() ) { return false; }

		// Theme My Login page is not
		if ( $this->is_theme_my_login_page() ) { return false; }

		// Members List page is not
		if ( $this->is_members_list_page() ) { return false; }

		// Subscribe to Comments Reloaded page is not
		if ( $this->is_subscribe_to_comments_reloaded_page() ) { return false; }

		// --<
		return apply_filters( 'cp_is_commentable', true );

	}



	/**
	 * Check if user agent is mobile
	 *
	 * @return bool $is_mobile True if mobile OS, false otherwise
	 */
	public function is_mobile() {

		// --<
		return $this->db->is_mobile();

	}



	/**
	 * Check if user agent is tablet
	 *
	 * @return boolean $is_tablet True if tablet OS, false otherwise
	 */
	public function is_tablet() {

		// --<
		return $this->db->is_tablet();

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Private Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Object initialisation
	 *
	 * @return void
	 */
	function _init() {

		// ---------------------------------------------------------------------
		// Database Object
		// ---------------------------------------------------------------------

		// define filename
		$class_file = 'commentpress-core/class_commentpress_db.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// we're fine, include class definition
		require_once( $class_file_path );

		// init autoload database object
		$this->db = new CommentpressCoreDatabase( $this );

		// ---------------------------------------------------------------------
		// Display Object
		// ---------------------------------------------------------------------

		// define filename
		$class_file = 'commentpress-core/class_commentpress_display.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// we're fine, include class definition
		require_once( $class_file_path );

		// init display object
		$this->display = new CommentpressCoreDisplay( $this );

		// ---------------------------------------------------------------------
		// Navigation Object
		// ---------------------------------------------------------------------

		// define filename
		$class_file = 'commentpress-core/class_commentpress_nav.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// we're fine, include class definition
		require_once( $class_file_path );

		// init display object
		$this->nav = new CommentpressCoreNavigator( $this );

		// ---------------------------------------------------------------------
		// Parser Object
		// ---------------------------------------------------------------------

		// define filename
		$class_file = 'commentpress-core/class_commentpress_parser.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// we're fine, include class definition
		require_once( $class_file_path );

		// init parser object
		$this->parser = new CommentpressCoreParser( $this );

		// ---------------------------------------------------------------------
		// Formatter Object
		// ---------------------------------------------------------------------

		// define filename
		$class_file = 'commentpress-core/class_commentpress_formatter.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// allow plugins to override this and supply their own
		$class_file_path = apply_filters(

			'cp_class_commentpress_formatter',
			$class_file_path

		);

		// we're fine, include class definition
		require_once( $class_file_path );

		// init formatter object
		$this->formatter = new CommentpressCoreFormatter( $this );

		// ---------------------------------------------------------------------
		// Workflow Object
		// ---------------------------------------------------------------------

		// define filename
		$class_file = 'commentpress-core/class_commentpress_workflow.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// allow plugins to override this and supply their own
		$class_file_path = apply_filters(

			'cp_class_commentpress_workflow',
			$class_file_path

		);

		// we're fine, include class definition
		require_once( $class_file_path );

		// init workflow object
		$this->workflow = new CommentpressCoreWorkflow( $this );

		// ---------------------------------------------------------------------
		// Front-end Editor Object
		// ---------------------------------------------------------------------

		// define filename
		$class_file = 'commentpress-core/class_commentpress_editor.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// allow plugins to override this and supply their own
		$class_file_path = apply_filters(

			'cp_class_commentpress_editor',
			$class_file_path

		);

		// we're fine, include class definition
		require_once( $class_file_path );

		// init workflow object
		$this->editor = new CommentpressCoreEditor( $this );

		// broadcast
		do_action( 'commentpress_after_includes' );

		// ---------------------------------------------------------------------
		// Finally, register hooks
		// ---------------------------------------------------------------------

		// register hooks
		$this->_register_hooks();

	}



	/**
	 * Register WordPress hooks
	 *
	 * @return void
	 */
	function _register_hooks() {

		// access version
		global $wp_version;

		// broadcast that CommentPress is active
		add_action( 'plugins_loaded', array( $this, 'broadcast' ) );

		// use translation
		add_action( 'plugins_loaded', array( $this, 'translation' ) );

		// check for plugin deactivation
		add_action( 'deactivated_plugin',  array( $this, '_plugin_deactivated' ), 10, 2 );

		// modify comment posting
		add_action( 'comment_post', array( $this, 'save_comment' ), 10, 2 );

		// exclude special pages from listings
		add_filter( 'wp_list_pages_excludes', array( $this, 'exclude_special_pages' ), 10, 1 );
		add_filter( 'parse_query', array( $this, 'exclude_special_pages_from_admin' ), 10, 1 );

		// is this the back end?
		if ( is_admin() ) {

			// modify all
			add_filter( 'views_edit-page', array( $this, 'update_page_counts_in_admin' ), 10, 1 );

			// modify admin menu
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );

			// add meta boxes
			add_action( 'add_meta_boxes' , array( $this, 'add_meta_boxes' ) );

			// intercept save
			add_action( 'save_post', array( $this, 'save_post' ), 10, 2 );

			// intercept delete
			add_action( 'before_delete_post', array( $this, 'delete_post' ), 10, 1 );

			// there's a new screen object in 3.3
			if ( version_compare( $wp_version, '3.2.99999', '>=' ) ) {

				// use new help functionality
				//add_action('add_screen_help_and_options', array( $this, 'options_help' ) );

				// NOTE: help is actually called in $this->admin_head() because the
				// 'add_screen_help_and_options' action does not seem to be working in 3.3-beta1

			} else {

				// previous help method
				add_action( 'contextual_help', array( $this, 'contextual_help' ) );

			}

			// comment block quicktag
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		} else {

			// modify the document head
			add_filter( 'wp_head', array( $this, 'head' ) );

			// add script libraries
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// add CSS files
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

			// add template redirect for TOC behaviour
			add_action( 'template_redirect', array( $this, 'redirect_to_child' ) );

			// modify the content (after all's done)
			add_filter( 'the_content', array( $this, 'the_content' ), 20 );

		}

		// if we're in a multisite scenario
		if ( is_multisite() ) {

			// add filter for signup page to include sidebar
			add_filter( 'after_signup_form', array( $this, 'after_signup_form' ) );

			// if subdirectory install
			if ( ! is_subdomain_install() ) {

				// add filter for reserved commentpress special page names
				add_filter( 'subdirectory_reserved_names', array( $this, 'add_reserved_names' ) );

			}

		}

		// if BP installed, then the following actions will fire...

		// enable BuddyPress functionality
		add_action( 'bp_include', array( $this, 'buddypress_init' ) );

		// add BuddyPress functionality (really late, so group object is set up)
		add_action( 'bp_setup_globals', array( $this, 'buddypress_globals_loaded' ), 1000 );

		// actions to perform on BP Docs load
		add_action( 'bp_docs_load', array( $this, 'bp_docs_loaded' ), 20 );

		// override BP Docs comment template
		add_filter( 'bp_docs_comment_template_path', array( $this, 'bp_docs_comment_tempate' ), 20, 2 );

		// amend the behaviour of Featured Comments plugin
		add_action( 'plugins_loaded', array( $this, 'featured_comments_override' ), 1000 );

		// broadcast
		do_action( 'commentpress_after_hooks' );

	}



	/**
	 * Utility to check for commentable CPT
	 *
	 * @todo: in development
	 *
	 * @return str $types Array of post types
	 */
	function _get_commentable_cpts() {

		// init
		$_types = false;

		// NOTE: exactly how do we support CPTs?
		$args = array(
			//'public'   => true,
			'_builtin' => false
		);

		$output = 'names'; // names or objects, note names is the default
		$operator = 'and'; // 'and' or 'or'

		// get post types
		$post_types = get_post_types( $args, $output, $operator );

		// trace
		//print_r( $post_types ); die();



		// did we get any?
		if ( count( $post_types ) > 0 ) {

			// init as array
			$_types = false;

			// loop
			foreach ($post_types AS $post_type ) {

				// add name to array (is_singular expects this)
				$_types[] = $post_type;

			}

		}

		// trace
		//print_r( $_types ); die();

		// --<
		return $_types;

	}



	/**
	 * Adds the formatter to the page/post metabox
	 *
	 * @param object $post The WordPress post object
	 * @return void
	 */
	function _get_post_formatter_metabox( $post ) {

		// ---------------------------------------------------------------------
		// Override post formatter
		// ---------------------------------------------------------------------

		// do we have the option to choose blog type (new in 3.3.1)?
		if ( $this->db->option_exists( 'cp_blog_type' ) ) {

			// define no types
			$types = array();

			// allow overrides
			$types = apply_filters( 'cp_blog_type_options', $types );

			// if we get some from a plugin, say...
			if ( ! empty( $types ) ) {

				// define title
				$type_title = __( 'Text Formatting', 'commentpress-core' );

				// allow overrides
				$type_title = apply_filters( 'cp_post_type_override_label', $type_title );

				// label
				echo '<div class="cp_post_type_override_wrapper">
				<p><strong><label for="cp_post_type_override">' . $type_title . '</label></strong></p>';

				// construct options
				$type_option_list = array();
				$n = 0;

				// set key
				$key = '_cp_post_type_override';

				// default to current blog type
				$value = $this->db->option_get( 'cp_blog_type' );

				// but, if the custom field has a value...
				if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

					// get it
					$value = get_post_meta( $post->ID, $key, true );

				}

				foreach( $types AS $type ) {
					if ( $n == $value ) {
						$type_option_list[] = '<option value="' . $n . '" selected="selected">' . $type . '</option>';
					} else {
						$type_option_list[] = '<option value="' . $n . '">' . $type . '</option>';
					}
					$n++;
				}
				$type_options = implode( "\n", $type_option_list );



				// select
				echo '
				<p>
				<select id="cp_post_type_override" name="cp_post_type_override">
					' . $type_options . '
				</select>
				</p>
				</div>
				';

			}

		}

	}



	/**
	 * Adds the default sidebar preference to the page/post metabox
	 *
	 * @param object $post The WordPress post object
	 * @return void
	 */
	function _get_default_sidebar_metabox( $post ) {

		// allow this to be disabled
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) return;

		// ---------------------------------------------------------------------
		// Override post formatter
		// ---------------------------------------------------------------------

		// do we have the option to choose the default sidebar (new in 3.3.3)?
		if ( $this->db->option_exists( 'cp_sidebar_default' ) ) {

			// show a title
			echo '<div class="cp_sidebar_default_wrapper">
			<p><strong><label for="cp_sidebar_default">' . __( 'Default Sidebar' , 'commentpress-core' ) . '</label></strong></p>';

			// set key
			$key = '_cp_sidebar_default';

			// default to show
			$_sidebar = $this->db->option_get( 'cp_sidebar_default' );

			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// get it
				$_sidebar = get_post_meta( $post->ID, $key, true );

			}

			// select
			echo '
			<p>
			<select id="cp_sidebar_default" name="cp_sidebar_default">
				<option value="toc" ' . (($_sidebar == 'toc') ? ' selected="selected"' : '') . '>' . __('Contents', 'commentpress-core') . '</option>
				<option value="activity" ' . (($_sidebar == 'activity') ? ' selected="selected"' : '') . '>' . __('Activity', 'commentpress-core') . '</option>
				<option value="comments" ' . (($_sidebar == 'comments') ? ' selected="selected"' : '') . '>' . __('Comments', 'commentpress-core') . '</option>
			</select>
			</p>
			</div>
			';

		}

	}



	/**
	 * Adds the paragraph numbering preference to the page/post metabox
	 *
	 * @param object $post The WordPress post object
	 * @return void
	 */
	function _get_para_numbering_metabox( $post ) {

		// show a title
		echo '<div class="cp_starting_para_number_wrapper">
		<p><strong><label for="cp_starting_para_number">' . __( 'Starting Paragraph Number' , 'commentpress-core' ) . '</label></strong></p>';

		// set key
		$key = '_cp_starting_para_number';

		// default to start with para 1
		$_num = 1;

		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// get it
			$_num = get_post_meta( $post->ID, $key, true );

		}

		// select
		echo '
		<p>
		<input type="text" id="cp_starting_para_number" name="cp_starting_para_number" value="' . $_num . '" />
		</p>
		</div>
		';

	}



	/**
	 * Deactivate this plugin
	 *
	 * @param str $plugin The name of the plugin
	 * @param bool $network_wide True if the plugin is network-activated, false otherwise
	 * @return void
	 */
	function _plugin_deactivated( $plugin, $network_wide = null ) {

		// is it the old Commentpress plugin still active?
		if ( defined( 'CP_PLUGIN_FILE' ) ) {

			// is it the old Commentpress plugin being deactivated?
			if ( $plugin == plugin_basename( CP_PLUGIN_FILE ) ) {

				//print_r( array( $plugin, $network_wide ) ); die();

				// only trigger this when not network-wide
				if ( is_null( $network_wide ) OR $network_wide == false ) {

					// restore theme
					$this->display->activate();

					// override widgets?
					//$this->db->_clear_widgets();

				}

			}

		}

	}



//##############################################################################



} // class ends



