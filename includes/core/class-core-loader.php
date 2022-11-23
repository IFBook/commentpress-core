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
	 * Activates core plugin.
	 *
	 * @since 3.0
	 */
	public function activate() {

		/**
		 * Fires when plugin is activated.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/activated' );

	}

	/**
	 * Deactivates core plugin.
	 *
	 * @since 3.0
	 */
	public function deactivate() {

		/**
		 * Fires when plugin is activated.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/deactivated' );

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

	/*
	 * -------------------------------------------------------------------------
	 * Methods below are legacy methods and should no longer be used.
	 * -------------------------------------------------------------------------
	 *
	 * They are retained here for the time-being but will be removed in future
	 * versions. You should check your log files to identify any calls to these
	 * methods and update your code accordingly.
	 *
	 * -------------------------------------------------------------------------
	 */

	/**
	 * Retrieves option for displaying TOC.
	 *
	 * @since 3.4
	 *
	 * @return mixed $result
	 */
	public function get_list_option() {
		_deprecated_function( __METHOD__, '4.0' );
		return $this->db->option_get( 'cp_show_posts_or_pages_in_toc' );
	}

	/**
	 * Get "Table of Contents" list.
	 *
	 * @since 3.4
	 *
	 * @param array $exclude_pages The array of Pages to exclude.
	 */
	public function get_toc_list( $exclude_pages = [] ) {
		_deprecated_function( __METHOD__, '4.0' );
		$this->display->get_toc_list( $exclude_pages );
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
		_deprecated_function( __METHOD__, '4.0' );
		return $this->parser->get_sorted_comments( $post_ID );
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
		_deprecated_function( __METHOD__, '4.0' );
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
		_deprecated_function( __METHOD__, '4.0' );
		return $this->display->get_header_min_link();
	}

	// -------------------------------------------------------------------------

	/**
	 * Get a link to a Special Page.
	 *
	 * @since 3.4
	 *
	 * @param str $page_type The CommentPress Core name of a Special Page.
	 * @return str $link The HTML link to that Page.
	 */
	public function get_page_link( $page_type = 'cp_all_comments_page' ) {
		_deprecated_function( __METHOD__, '4.0' );
		return $this->pages_legacy->get_page_link( $page_type );
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
		_deprecated_function( __METHOD__, '4.0' );
		return $this->pages_legacy->get_page_url( $page_type );
	}

	// -------------------------------------------------------------------------

	/**
	 * Return the name of the default sidebar.
	 *
	 * @since 3.4
	 *
	 * @return str $return The code for the default sidebar.
	 */
	public function get_default_sidebar() {
		_deprecated_function( __METHOD__, '4.0' );
		return $this->theme->get_default_sidebar();
	}

	/**
	 * Get the order of the sidebars.
	 *
	 * @since 3.4
	 *
	 * @return array $order Sidebars in order of display.
	 */
	public function get_sidebar_order() {
		_deprecated_function( __METHOD__, '4.0' );
		return $this->theme->get_sidebar_order();
	}

	// -------------------------------------------------------------------------

	/**
	 * Retrieves th current Text Signature hidden input.
	 *
	 * @since 3.4
	 *
	 * @return str $result The HTML input.
	 */
	public function get_signature_field() {
		_deprecated_function( __METHOD__, '4.0' );
		return $this->display->get_signature_field();
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
		_deprecated_function( __METHOD__, '4.0' );
		return $this->parser->get_text_signature( $para_num );
	}

	// -------------------------------------------------------------------------

	/**
	 * Check if a Page/Post can be commented on.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_commentable True if commentable, false otherwise.
	 */
	public function is_commentable() {
		_deprecated_function( __METHOD__, '4.0' );
		return $this->parser->is_commentable();
	}

	/**
	 * Check if user agent is mobile.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_mobile True if mobile OS, false otherwise.
	 */
	public function is_mobile() {
		_deprecated_function( __METHOD__, '4.0' );
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
		_deprecated_function( __METHOD__, '4.0' );
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
		_deprecated_function( __METHOD__, '4.0' );
		return $this->bp->is_buddypress();
	}

	/**
	 * Is this a BuddyPress "Special Page" - a component homepage?
	 *
	 * @since 3.4
	 *
	 * @return bool $is_bp True if current Page is a BuddyPress Page, false otherwise.
	 */
	public function is_buddypress_special_page() {
		_deprecated_function( __METHOD__, '4.0' );
		return $this->bp->is_buddypress_special_page();
	}

	/**
	 * Is this Blog a BuddyPress Group Blog?
	 *
	 * @since 3.4
	 *
	 * @return bool $bp_groupblog True when current Blog is a BuddyPress Group Blog, false otherwise.
	 */
	public function is_groupblog() {
		_deprecated_function( __METHOD__, '4.0' );
		return $this->bp->is_groupblog();
	}

}
