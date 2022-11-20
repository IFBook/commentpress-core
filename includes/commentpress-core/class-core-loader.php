<?php
/**
 * CommentPress Core class.
 *
 * Handles single Site plugin functionality.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Class.
 *
 * A class that encapsulates single Site plugin functionality.
 *
 * @since 3.0
 */
class CommentPress_Core {

	/**
	 * Database object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $db The database object.
	 */
	public $db;

	/**
	 * Display handling object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $display The display object.
	 */
	public $display;

	/**
	 * Site Settings object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $site_settings The Site Settings object.
	 */
	public $site_settings;

	/**
	 * Post Settings object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $post_settings The Post Settings object.
	 */
	public $post_settings;

	/**
	 * Navigation handling object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $nav The nav object.
	 */
	public $nav;

	/**
	 * Content parser object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $parser The parser object.
	 */
	public $parser;

	/**
	 * Formatter object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $formatter The Formatter object.
	 */
	public $formatter;

	/**
	 * Comments object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $comments The Comments object.
	 */
	public $comments;

	/**
	 * Revisions object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $revisions The Revisions object.
	 */
	public $revisions;

	/**
	 * BuddyPress compatibility object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $bp The BuddyPress compatibility object.
	 */
	public $bp;

	/**
	 * Legacy Pages object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $pages_legacy The legacy Special Pages object.
	 */
	public $pages_legacy;

	/**
	 * Classes directory path.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $classes_path Relative path to the classes directory.
	 */
	public $classes_path = 'includes/commentpress-core/classes/';

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 */
	public function __construct() {

		// Initialise when all plugins have loaded.
		add_action( 'plugins_loaded', [ $this, 'initialise' ] );

		// Use translation.
		add_action( 'plugins_loaded', [ $this, 'translation' ] );

		// Init.
		$this->initialise();

	}

	/**
	 * Initialises this plugin.
	 *
	 * @since 4.0
	 */
	public function initialise() {

		// Only do this once.
		static $done;
		if ( isset( $done ) && $done === true ) {
			return;
		}

		// Bootstrap plugin.
		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

		/**
		 * Broadcast that CommentPress Core has loaded.
		 *
		 * Used internally to bootstrap objects.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/loaded' );

		/**
		 * Broadcast that CommentPress Core has loaded.
		 *
		 * @since 3.6.3
		 */
		do_action( 'commentpress_loaded' );

		// We're done.
		$done = true;

	}

	/**
	 * Includes class files.
	 *
	 * @since 4.0
	 */
	public function include_files() {

		// Include class files.
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-database.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-display.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-settings-site.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-settings-post.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-navigation.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-parser.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-formatter.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-comments.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-revisions.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-bp-core.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-pages-legacy.php';

		/**
		 * Broadcast that class files have been included.
		 *
		 * @since 3.6.2
		 */
		do_action( 'commentpress_after_includes' );

	}

	/**
	 * Sets up this plugin's objects.
	 *
	 * @since 4.0
	 */
	public function setup_objects() {

		// Initialise objects.
		$this->db = new CommentPress_Core_Database( $this );
		$this->display = new CommentPress_Core_Display( $this );
		$this->site_settings = new CommentPress_Core_Settings_Site( $this );
		$this->post_settings = new CommentPress_Core_Settings_Post( $this );
		$this->nav = new CommentPress_Core_Navigator( $this );
		$this->parser = new CommentPress_Core_Parser( $this );
		$this->formatter = new CommentPress_Core_Formatter( $this );
		$this->comments = new CommentPress_Core_Comments( $this );
		$this->revisions = new CommentPress_Core_Revisions( $this );
		$this->bp = new CommentPress_Core_BuddyPress( $this );
		$this->pages_legacy = new CommentPress_Core_Pages_Legacy( $this );

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed.
	 */
	public function register_hooks() {

		// Is this the back end?
		if ( is_admin() ) {

			// Add meta boxes.
			add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

		} else {

			// Modify the content (after all's done).
			add_filter( 'the_content', [ $this, 'the_content' ], 20 );

		}

		/**
		 * Broadcast that callbacks have been added.
		 *
		 * @since 3.6.2
		 */
		do_action( 'commentpress_after_hooks' );

	}

	/**
	 * Runs when plugin is activated.
	 *
	 * @since 3.0
	 */
	public function activate() {

		// Initialise display - sets the theme.
		$this->display->activate();

		// Initialise database.
		$this->db->activate();

	}

	/**
	 * Runs when plugin is deactivated.
	 *
	 * @since 3.0
	 */
	public function deactivate() {

		// Call database destroy method.
		$this->db->deactivate();

		// Call display destroy method.
		$this->display->deactivate();

	}

	/**
	 * Loads translation, if present.
	 *
	 * @since 3.4
	 */
	public function translation() {

		// Load translations.
		// phpcs:ignore WordPress.WP.DeprecatedParameters.Load_plugin_textdomainParam2Found
		load_plugin_textdomain(
			'commentpress-core', // Unique name.
			false, // Deprecated argument.
			dirname( plugin_basename( COMMENTPRESS_PLUGIN_FILE ) ) . '/languages/' // Relative path to directory.
		);

	}

	// -------------------------------------------------------------------------

	/**
	 * Parses Page/Post content.
	 *
	 * @since 3.0
	 *
	 * @param str $content The content of the Page/Post.
	 * @return str $content The modified content.
	 */
	public function the_content( $content ) {

		// Reference our Post.
		global $post;

		// JetPack 2.7 or greater parses the content in the head to create
		// content summaries so prevent parsing unless this is the main content.
		if ( is_admin() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		// Compat with Subscribe to Comments Reloaded.
		if ( $this->is_subscribe_to_comments_reloaded_page() ) {
			return $content;
		}

		// Compat with Theme My Login.
		if ( $this->is_theme_my_login_page() ) {
			return $content;
		}

		// Compat with Members List plugin.
		if ( $this->is_members_list_page() ) {
			return $content;
		}

		// Test for BuddyPress Special Page (compat with BuddyPress Docs).
		if ( $this->is_buddypress() ) {

			// Is it a component homepage?
			if ( $this->is_buddypress_special_page() ) {

				// --<
				return $content;

			}

		}

		// Init allowed.
		$allowed = false;

		// Only parse Posts or Pages.
		if ( ( is_single() || is_page() || is_attachment() ) && ! $this->db->is_special_page() ) {
			$allowed = true;
		}

		/**
		 * If allowed, parse.
		 *
		 * @since 3.0
		 *
		 * @param bool $allowed True if allowed, false otherwise.
		 */
		if ( apply_filters( 'commentpress_force_the_content', $allowed ) ) {

			// Delegate to parser.
			$content = $this->parser->the_content( $content );

		}

		// --<
		return $content;

	}

	/**
	 * Retrieves option for displaying TOC.
	 *
	 * @since 3.4
	 *
	 * @return mixed $result
	 */
	public function get_list_option() {
		return $this->db->option_get( 'cp_show_posts_or_pages_in_toc' );
	}

	/**
	 * Retrieves minimise all button.
	 *
	 * @since 3.4
	 *
	 * @param str $sidebar The type of sidebar - either 'comments', 'toc' or 'activity'.
	 * @return str $sidebar The HTML for minimise button.
	 */
	public function get_minimise_all_button( $sidebar = 'comments' ) {
		return $this->display->get_minimise_all_button( $sidebar );
	}

	/**
	 * Retrieves header minimise button.
	 *
	 * @since 3.4
	 *
	 * @return str $result The HTML for minimise button.
	 */
	public function get_header_min_link() {
		return $this->display->get_header_min_link();
	}

	/**
	 * Retrieves text_signature hidden input.
	 *
	 * @since 3.4
	 *
	 * @return str $result The HTML input.
	 */
	public function get_signature_field() {

		// Init Text Signature.
		$text_sig = '';

		// Get comment ID to reply to from URL query string.
		$reply_to_comment_id = isset( $_GET['replytocom'] ) ? (int) $_GET['replytocom'] : 0;

		// Did we get a comment ID?
		if ( $reply_to_comment_id != 0 ) {

			// Get Paragraph Text Signature.
			$text_sig = $this->db->get_text_signature_by_comment_id( $reply_to_comment_id );

		} else {

			// Do we have a Paragraph Number in the query string?
			$reply_to_para_id = isset( $_GET['replytopara'] ) ? (int) $_GET['replytopara'] : 0;

			// Did we get a comment ID?
			if ( $reply_to_para_id != 0 ) {

				// Get Paragraph Text Signature.
				$text_sig = $this->get_text_signature( $reply_to_para_id );

			}

		}

		// Get constructed hidden input for comment form.
		$result = $this->display->get_signature_input( $text_sig );

		// --<
		return $result;

	}

	/**
	 * Adds meta boxes to admin screens.
	 *
	 * @since 3.4
	 */
	public function add_meta_boxes() {

		// Add our meta boxes to Pages.
		add_meta_box(
			'commentpress_page_options',
			__( 'CommentPress Core Options', 'commentpress-core' ),
			[ $this, 'custom_box_page' ],
			'page',
			'side'
		);

	}

	/**
	 * Adds meta box to Page edit screens.
	 *
	 * @since 3.4
	 */
	public function custom_box_page() {

		// Access Post.
		global $post;

		// Use nonce for verification.
		wp_nonce_field( 'commentpress_page_settings', 'commentpress_nonce' );

		// ---------------------------------------------------------------------
		// Show or Hide Page Title.
		// ---------------------------------------------------------------------

		// Show a title.
		echo '<div class="cp_title_visibility_wrapper">
		<p><strong><label for="cp_title_visibility">' . __( 'Page Title Visibility', 'commentpress-core' ) . '</label></strong></p>';

		// Set key.
		$key = '_cp_title_visibility';

		// Default to show.
		$viz = $this->db->option_get( 'cp_title_visibility' );

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Get it.
			$viz = get_post_meta( $post->ID, $key, true );

		}

		// Select.
		echo '
		<p>
		<select id="cp_title_visibility" name="cp_title_visibility">
			<option value="show" ' . ( ( $viz == 'show' ) ? ' selected="selected"' : '' ) . '>' . __( 'Show page title', 'commentpress-core' ) . '</option>
			<option value="hide" ' . ( ( $viz == 'hide' ) ? ' selected="selected"' : '' ) . '>' . __( 'Hide page title', 'commentpress-core' ) . '</option>
		</select>
		</p>
		</div>
		';

		// ---------------------------------------------------------------------
		// Show or Hide Page Meta.
		// ---------------------------------------------------------------------

		// Show a label.
		echo '<div class="cp_page_meta_visibility_wrapper">
		<p><strong><label for="cp_page_meta_visibility">' . __( 'Page Meta Visibility', 'commentpress-core' ) . '</label></strong></p>';

		// Set key.
		$key = '_cp_page_meta_visibility';

		// Default to show.
		$viz = $this->db->option_get( 'cp_page_meta_visibility' );

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Get it.
			$viz = get_post_meta( $post->ID, $key, true );

		}

		// Select.
		echo '
		<p>
		<select id="cp_page_meta_visibility" name="cp_page_meta_visibility">
			<option value="show" ' . ( ( $viz == 'show' ) ? ' selected="selected"' : '' ) . '>' . __( 'Show page meta', 'commentpress-core' ) . '</option>
			<option value="hide" ' . ( ( $viz == 'hide' ) ? ' selected="selected"' : '' ) . '>' . __( 'Hide page meta', 'commentpress-core' ) . '</option>
		</select>
		</p>
		</div>
		';

		// ---------------------------------------------------------------------
		// Page Numbering - only shown on first top level Page.
		// ---------------------------------------------------------------------

		// If Page has no parent and it's not a Special Page and it's the first.
		if (
			$post->post_parent == '0' &&
			! $this->db->is_special_page() &&
			$post->ID == $this->nav->get_first_page()
		) {

			// Label.
			echo '<div class="cp_number_format_wrapper">
			<p><strong><label for="cp_number_format">' . __( 'Page Number Format', 'commentpress-core' ) . '</label></strong></p>';

			// Set key.
			$key = '_cp_number_format';

			// Default to arabic.
			$format = 'arabic';

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// Get it.
				$format = get_post_meta( $post->ID, $key, true );

			}

			// Select.
			echo '
			<p>
			<select id="cp_number_format" name="cp_number_format">
				<option value="arabic" ' . ( ( $format == 'arabic' ) ? ' selected="selected"' : '' ) . '>' . __( 'Arabic numerals', 'commentpress-core' ) . '</option>
				<option value="roman" ' . ( ( $format == 'roman' ) ? ' selected="selected"' : '' ) . '>' . __( 'Roman numerals', 'commentpress-core' ) . '</option>
			</select>
			</p>
			</div>
			';

		}

		// ---------------------------------------------------------------------
		// Page Layout for Title Page -> to allow for Book Cover image.
		// ---------------------------------------------------------------------

		// Is this the Title Page?
		if ( $post->ID == $this->db->option_get( 'cp_welcome_page' ) ) {

			// Label.
			echo '<div class="cp_page_layout_wrapper">
			<p><strong><label for="cp_page_layout">' . __( 'Page Layout', 'commentpress-core' ) . '</label></strong></p>';

			// Set key.
			$key = '_cp_page_layout';

			// Default to text.
			$value = 'text';

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// Get it.
				$value = get_post_meta( $post->ID, $key, true );

			}

			// Select.
			echo '
			<p>
			<select id="cp_page_layout" name="cp_page_layout">
				<option value="text" ' . ( ( $value == 'text' ) ? ' selected="selected"' : '' ) . '>' . __( 'Standard', 'commentpress-core' ) . '</option>
				<option value="wide" ' . ( ( $value == 'wide' ) ? ' selected="selected"' : '' ) . '>' . __( 'Wide', 'commentpress-core' ) . '</option>
			</select>
			</p>
			</div>
			';

		}

		// Get default sidebar.
		$this->get_default_sidebar_metabox( $post );

		// Get starting para number.
		$this->get_para_numbering_metabox( $post );

	}

	/**
	 * Get table of contents.
	 *
	 * @since 3.4
	 */
	public function get_toc() {

		// Switch Pages or Posts.
		if ( $this->get_list_option() == 'post' ) {

			// List Posts.
			$this->display->list_posts();

		} else {

			// List Pages.
			$this->display->list_pages();

		}

	}

	/**
	 * Get table of contents list.
	 *
	 * @since 3.4
	 *
	 * @param array $exclude_pages The array of Pages to exclude.
	 */
	public function get_toc_list( $exclude_pages = [] ) {

		// Switch Pages or Posts.
		if ( $this->get_list_option() == 'post' ) {

			// List Posts.
			$this->display->list_posts();

		} else {

			// List Pages.
			$this->display->list_pages( $exclude_pages );

		}

	}

	/**
	 * Get Comments sorted by Text Signature and Paragraph.
	 *
	 * @since 3.4
	 *
	 * @param int $post_ID The numeric ID of the Post.
	 * @return array $comments An array of sorted comment data.
	 */
	public function get_sorted_comments( $post_ID ) {

		// --<
		return $this->parser->get_sorted_comments( $post_ID );

	}

	/**
	 * Get Paragraph Number for a particular Text Signature.
	 *
	 * @since 3.4
	 *
	 * @param str $text_signature The Text Signature.
	 * @return int $num The position in Text Signature array.
	 */
	public function get_para_num( $text_signature ) {

		// Get position in array.
		$num = array_search( $text_signature, $this->db->get_text_sigs() );

		// --<
		return $num + 1;

	}

	/**
	 * Get Text Signature for a particular Paragraph Number.
	 *
	 * @since 3.4
	 *
	 * @param int $para_num The Paragraph Number in a Post.
	 * @return str $text_signature The Text Signature.
	 */
	public function get_text_signature( $para_num ) {

		// Get text sigs.
		$sigs = $this->db->get_text_sigs();

		// Get value at that position in array.
		$text_sig = ( isset( $sigs[ $para_num - 1 ] ) ) ? $sigs[ $para_num - 1 ] : '';

		// --<
		return $text_sig;

	}

	/**
	 * Get a link to a Special Page.
	 *
	 * @since 3.4
	 *
	 * @param str $page_type The CommentPress Core name of a Special Page.
	 * @return str $link THe HTML link to that Page.
	 */
	public function get_page_link( $page_type = 'cp_all_comments_page' ) {

		// Access globals.
		global $post;

		// Init.
		$link = '';

		// Get Page ID.
		$page_id = $this->db->option_get( $page_type );

		// Do we have a Page?
		if ( $page_id != '' ) {

			// Get Page.
			$page = get_post( $page_id );

			// Is it the current Page?
			$active = '';
			if ( isset( $post ) && $page->ID == $post->ID ) {
				$active = ' class="active_page"';
			}

			// Get link.
			$url = get_permalink( $page );

			// Switch title by type.
			switch ( $page_type ) {

				case 'cp_welcome_page':
					$link_title = __( 'Title Page', 'commentpress-core' );
					$button = 'cover';
					break;

				case 'cp_all_comments_page':
					$link_title = __( 'All Comments', 'commentpress-core' );
					$button = 'allcomments';
					break;

				case 'cp_general_comments_page':
					$link_title = __( 'General Comments', 'commentpress-core' );
					$button = 'general';
					break;

				case 'cp_blog_page':
					$link_title = __( 'Blog', 'commentpress-core' );
					if ( is_home() ) {
						$active = ' class="active_page"';
					}
					$button = 'blog';
					break;

				case 'cp_blog_archive_page':
					$link_title = __( 'Blog Archive', 'commentpress-core' );
					$button = 'archive';
					break;

				case 'cp_comments_by_page':
					$link_title = __( 'Comments by Commenter', 'commentpress-core' );
					$button = 'members';
					break;

				default:
					$link_title = __( 'Members', 'commentpress-core' );
					$button = 'members';

			}

			/**
			 * Filters the Special Page title.
			 *
			 * @since 3.4
			 *
			 * @param str $link_title The default Special Page title.
			 * @param str $page_type The CommentPress Core name of a Special Page.
			 */
			$title = apply_filters( 'commentpress_page_link_title', $link_title, $page_type );

			// Show link.
			$link = '<li' . $active . '><a href="' . $url . '" id="btn_' . $button . '" class="css_btn" title="' . $title . '">' . $title . '</a></li>' . "\n";

		}

		// --<
		return $link;

	}

	/**
	 * Get the URL for a Special Page.
	 *
	 * @since 3.4
	 *
	 * @param str $page_type The CommentPress Core name of a Special Page.
	 * @return str $url The URL of that Page.
	 */
	public function get_page_url( $page_type = 'cp_all_comments_page' ) {

		// Init.
		$url = '';

		// Get Page ID.
		$page_id = $this->db->option_get( $page_type );

		// Do we have a Page?
		if ( $page_id != '' ) {

			// Get Page.
			$page = get_post( $page_id );

			// Get link.
			$url = get_permalink( $page );

		}

		// --<
		return $url;

	}

	/**
	 * Utility to check for presence of Theme My Login.
	 *
	 * @since 3.4
	 *
	 * @return bool $success True if Theme My Login Page, false otherwise
	 */
	public function is_theme_my_login_page() {

		// Access Page.
		global $post;

		// Compat with Theme My Login.
		if (
			is_page() &&
			! $this->db->is_special_page() &&
			$post->post_name == 'login' &&
			$post->post_content == '[theme-my-login]'
		) {

			// --<
			return true;

		}

		// --<
		return false;

	}

	/**
	 * Utility to check for presence of Members List.
	 *
	 * @since 3.4.7
	 *
	 * @return bool $success True if is Members List Page, false otherwise.
	 */
	public function is_members_list_page() {

		// Access Page.
		global $post;

		// Compat with Members List.
		if (
			is_page() &&
			! $this->db->is_special_page() &&
			( strstr( $post->post_content, '[members-list' ) !== false )
		) {

			// --<
			return true;

		}

		// --<
		return false;

	}

	/**
	 * Utility to check for presence of Subscribe to Comments Reloaded.
	 *
	 * @since 3.5.9
	 *
	 * @return bool $success True if "Subscribe to Comments Reloaded" Page, false otherwise.
	 */
	public function is_subscribe_to_comments_reloaded_page() {

		// Access Page.
		global $post;

		// Compat with Subscribe to Comments Reloaded.
		if (
			is_page() &&
			! $this->db->is_special_page() &&
			$post->ID == '9999999' &&
			$post->guid == get_bloginfo( 'url' ) . '/?page_id=9999999'
		) {

			// --<
			return true;

		}

		// --<
		return false;

	}

	/**
	 * Return the name of the default sidebar.
	 *
	 * @since 3.4
	 *
	 * @return str $return The code for the default sidebar.
	 */
	public function get_default_sidebar() {

		/**
		 * Set sensible default sidebar, but allow overrides.
		 *
		 * @since 3.9.8
		 *
		 * @param str The default sidebar before any contextual modifications.
		 * @return str The modified sidebar before any contextual modifications.
		 */
		$return = apply_filters( 'commentpress_default_sidebar', 'activity' );

		// Is this a commentable Page?
		if ( ! $this->is_commentable() ) {

			// No - we must use either 'activity' or 'toc'.
			if ( $this->db->option_exists( 'cp_sidebar_default' ) ) {

				// Get option (we don't need to look at the Page meta in this case).
				$default = $this->db->option_get( 'cp_sidebar_default' );

				// Use it unless it's 'comments'.
				if ( $default != 'comments' ) {
					$return = $default;
				}

			}

			// --<
			return $return;

		}

		/*
		// Get CPTs.
		//$types = $this->get_commentable_cpts();

		// Testing what we do with CPTs.
		//if ( is_singular() || is_singular( $types ) ) {
		*/

		// Is it a commentable Page?
		if ( is_singular() ) {

			// Some people have reported that db is not an object at this point -
			// though I cannot figure out how this might be occurring - so we
			// avoid the issue by checking if it is.
			if ( is_object( $this->db ) ) {

				// Is it a Special Page which have Comments-in-Page (or are not commentable)?
				if ( ! $this->db->is_special_page() ) {

					// Access Page.
					global $post;

					// Either 'comments', 'activity' or 'toc'.
					if ( $this->db->option_exists( 'cp_sidebar_default' ) ) {

						// Get global option.
						$return = $this->db->option_get( 'cp_sidebar_default' );

						// Check if the Post/Page has a meta value.
						$key = '_cp_sidebar_default';

						// If the custom field already has a value.
						if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

							// Get it.
							$return = get_post_meta( $post->ID, $key, true );

						}

					}

					// --<
					return $return;

				}

			}

		}

		// Not singular - must be either "activity" or "toc".
		if ( $this->db->option_exists( 'cp_sidebar_default' ) ) {

			// Override.
			$default = $this->db->option_get( 'cp_sidebar_default' );

			// Use it unless it's 'comments'.
			if ( $default != 'comments' ) {
				$return = $default;
			}

		}

		// --<
		return $return;

	}

	/**
	 * Get the order of the sidebars.
	 *
	 * @since 3.4
	 *
	 * @return array $order Sidebars in order of display.
	 */
	public function get_sidebar_order() {

		/**
		 * Filters the default tab order.
		 *
		 * @since 3.4
		 *
		 * @param array $order The default tab order array.
		 */
		$order = apply_filters( 'cp_sidebar_tab_order', [ 'contents', 'comments', 'activity' ] );

		// --<
		return $order;

	}

	/**
	 * Check if a Page/Post can be commented on.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_commentable True if commentable, false otherwise.
	 */
	public function is_commentable() {

		// Declare access to globals.
		global $post;

		// Not on Signup Pages.
		$script = isset( $_SERVER['SCRIPT_FILENAME'] ) ? wp_unslash( $_SERVER['SCRIPT_FILENAME'] ) : '';
		if ( is_multisite() && ! empty( $script ) ) {
			if ( 'wp-signup.php' == basename( $script ) ) {
				return false;
			}
			if ( 'wp-activate.php' == basename( $script ) ) {
				return false;
			}
		}

		// Not if we're not on a Page/Post and especially not if there's no Post object.
		if ( ! is_singular() || ! is_object( $post ) ) {
			return false;
		}

		// CommentPress Core Special Pages Special Pages are not.
		if ( $this->db->is_special_page() ) {
			return false;
		}

		// BuddyPress Special Pages are not.
		if ( $this->is_buddypress_special_page() ) {
			return false;
		}

		// Theme My Login Page is not.
		if ( $this->is_theme_my_login_page() ) {
			return false;
		}

		// Members List Page is not.
		if ( $this->is_members_list_page() ) {
			return false;
		}

		// Subscribe to Comments Reloaded Page is not.
		if ( $this->is_subscribe_to_comments_reloaded_page() ) {
			return false;
		}

		/**
		 * Filter comment allowed.
		 *
		 * @since 3.4
		 *
		 * @param bool $is_commentable True by default.
		 */
		return apply_filters( 'cp_is_commentable', true );

	}

	/**
	 * Check if user agent is mobile.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_mobile True if mobile OS, false otherwise.
	 */
	public function is_mobile() {

		// --<
		return $this->db->is_mobile();

	}

	/**
	 * Check if user agent is tablet.
	 *
	 * @since 3.4
	 *
	 * @return boolean $is_tablet True if tablet OS, false otherwise.
	 */
	public function is_tablet() {

		// --<
		return $this->db->is_tablet();

	}

	// -------------------------------------------------------------------------

	/**
	 * Utility to check for commentable CPT.
	 *
	 * @since 3.4
	 *
	 * @return str $types Array of Post Types.
	 */
	public function get_commentable_cpts() {

		// Init.
		$types = false;

		// NOTE: exactly how do we support CPTs?
		$args = [
			//'public'   => true,
			'_builtin' => false,
		];

		$output = 'names'; // Names or objects, note names is the default.
		$operator = 'and'; // 'and' or 'or'.

		// Get Post Types.
		$post_types = get_post_types( $args, $output, $operator );

		// Did we get any?
		if ( count( $post_types ) > 0 ) {

			// Init as array.
			$types = false;

			// Loop.
			foreach ( $post_types as $post_type ) {

				// Add name to array (is_singular expects this).
				$types[] = $post_type;

			}

		}

		// --<
		return $types;

	}

	/**
	 * Adds the Paragraph numbering preference to the Page/Post metabox.
	 *
	 * @since 3.4
	 *
	 * @param object $post The WordPress Post object.
	 */
	public function get_para_numbering_metabox( $post ) {

		// Show a title.
		echo '<div class="cp_starting_para_number_wrapper">
		<p><strong><label for="cp_starting_para_number">' . __( 'Starting Paragraph Number', 'commentpress-core' ) . '</label></strong></p>';

		// Set key.
		$key = '_cp_starting_para_number';

		// Default to start with para 1.
		$num = 1;

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Get it.
			$num = get_post_meta( $post->ID, $key, true );

		}

		// Select.
		echo '
		<p>
		<input type="text" id="cp_starting_para_number" name="cp_starting_para_number" value="' . $num . '" />
		</p>
		</div>
		';

	}

	// -------------------------------------------------------------------------

	/**
	 * Is BuddyPress active?
	 *
	 * @since 3.4
	 *
	 * @return bool $buddypress True when BuddyPress active, false otherwise.
	 */
	public function is_buddypress() {
		return $this->bp->is_buddypress();
	}

	/**
	 * Is this Blog a BuddyPress Group Blog?
	 *
	 * @since 3.4
	 *
	 * @return bool $bp_groupblog True when current Blog is a BuddyPress Group Blog, false otherwise.
	 */
	public function is_groupblog() {
		return $this->bp->is_groupblog();
	}

	/**
	 * Is this a BuddyPress "Special Page" - a component homepage?
	 *
	 * @since 3.4
	 *
	 * @return bool $is_bp True if current Page is a BuddyPress Page, false otherwise.
	 */
	public function is_buddypress_special_page() {
		return $this->bp->is_buddypress_special_page();
	}

}
