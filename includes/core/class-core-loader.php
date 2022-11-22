<?php
/**
 * CommentPress Core Loader class.
 *
 * Handles Single Site plugin functionality.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Class.
 *
 * A class that encapsulates Single Site plugin functionality.
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
	 * Theme object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $theme The theme object.
	 */
	public $theme;

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
	 * Plugin compatibility object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $plugins The plugin compatibility object.
	 */
	public $plugins;

	/**
	 * Device detection object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $device The Device detection object.
	 */
	public $device;

	/**
	 * Legacy Pages object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $pages_legacy The legacy Special Pages object.
	 */
	public $pages_legacy;

	/**
	 * AJAX loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $ajax The AJAX loader object.
	 */
	public $ajax;

	/**
	 * Classes directory path.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $classes_path Relative path to the classes directory.
	 */
	public $classes_path = 'includes/core/classes/';

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
		 * @since 3.6.3
		 */
		do_action_deprecated( 'commentpress_loaded', '4.0', 'commentpress/core/loaded' );

		/**
		 * Broadcast that CommentPress Core has loaded.
		 *
		 * Used internally to bootstrap objects.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/loaded' );

		// We're done.
		$done = true;

	}

	/**
	 * Includes class files.
	 *
	 * @since 4.0
	 */
	public function include_files() {

		// Include core class files.
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-database.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-display.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-theme.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-settings-site.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-settings-post.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-navigation.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-parser.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-formatter.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-comments.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-revisions.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-bp-core.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-plugins.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-device.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-pages-legacy.php';

		// Include ajax class files.
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-ajax-loader.php';

		/**
		 * Broadcast that class files have been included.
		 *
		 * @since 3.6.2
		 */
		do_action_deprecated( 'commentpress_after_includes', '4.0', 'commentpress/core/loaded' );

	}

	/**
	 * Sets up this plugin's objects.
	 *
	 * @since 4.0
	 */
	public function setup_objects() {

		// Initialise core objects.
		$this->db = new CommentPress_Core_Database( $this );
		$this->display = new CommentPress_Core_Display( $this );
		$this->theme = new CommentPress_Core_Theme( $this );
		$this->site_settings = new CommentPress_Core_Settings_Site( $this );
		$this->post_settings = new CommentPress_Core_Settings_Post( $this );
		$this->nav = new CommentPress_Core_Navigator( $this );
		$this->parser = new CommentPress_Core_Parser( $this );
		$this->formatter = new CommentPress_Core_Formatter( $this );
		$this->comments = new CommentPress_Core_Comments( $this );
		$this->revisions = new CommentPress_Core_Revisions( $this );
		$this->bp = new CommentPress_Core_BuddyPress( $this );
		$this->plugins = new CommentPress_Core_Plugins( $this );
		$this->device = new CommentPress_Core_Device( $this );
		$this->pages_legacy = new CommentPress_Core_Pages_Legacy( $this );

		// Initialise ajax objects.
		$this->ajax = new CommentPress_AJAX_Loader( $this );

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed.
	 */
	public function register_hooks() {

		/**
		 * Broadcast that callbacks have been added.
		 *
		 * @since 3.6.2
		 */
		do_action_deprecated( 'commentpress_after_hooks', '4.0', 'commentpress/core/loaded' );

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

		// Get Comment ID to reply to from URL query string.
		$reply_to_comment_id = isset( $_GET['replytocom'] ) ? (int) $_GET['replytocom'] : 0;

		// Did we get a Comment ID?
		if ( $reply_to_comment_id != 0 ) {

			// Get Paragraph Text Signature.
			$text_sig = $this->db->get_text_signature_by_comment_id( $reply_to_comment_id );

		} else {

			// Do we have a Paragraph Number in the query string?
			$reply_to_para_id = isset( $_GET['replytopara'] ) ? (int) $_GET['replytopara'] : 0;

			// Did we get a Comment ID?
			if ( $reply_to_para_id != 0 ) {

				// Get Paragraph Text Signature.
				$text_sig = $this->get_text_signature( $reply_to_para_id );

			}

		}

		// Get constructed hidden input for Comment form.
		$result = $this->display->get_signature_input( $text_sig );

		// --<
		return $result;

	}

	/**
	 * Get "Table of Contents" list.
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
	 * @return array $comments An array of sorted Comment data.
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

		// Get Text Signatures.
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
				if ( ! $this->pages_legacy->is_special_page() ) {

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
		if ( $this->pages_legacy->is_special_page() ) {
			return false;
		}

		// BuddyPress Special Pages are not.
		if ( $this->bp->is_buddypress_special_page() ) {
			return false;
		}

		// Theme My Login Page is not.
		if ( $this->plugins->is_theme_my_login_page() ) {
			return false;
		}

		// Members List Page is not.
		if ( $this->plugins->is_members_list_page() ) {
			return false;
		}

		// Subscribe to Comments Reloaded Page is not.
		if ( $this->plugins->is_subscribe_to_comments_reloaded_page() ) {
			return false;
		}

		/**
		 * Filters "commenting allowed" status.
		 *
		 * @since 3.4
		 *
		 * @param bool $is_commentable True by default.
		 */
		return apply_filters( 'cp_is_commentable', true );

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

	// -------------------------------------------------------------------------

	/**
	 * Check if user agent is mobile.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_mobile True if mobile OS, false otherwise.
	 */
	public function is_mobile() {
		return $this->device->is_mobile();
	}

	/**
	 * Check if user agent is tablet.
	 *
	 * @since 3.4
	 *
	 * @return boolean $is_tablet True if tablet OS, false otherwise.
	 */
	public function is_tablet() {
		return $this->device->is_tablet();
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
