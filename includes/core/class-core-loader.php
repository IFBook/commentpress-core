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
 * CommentPress Core Loader Class.
 *
 * A class that loads all Single Site functionality.
 *
 * @since 3.0
 */
class CommentPress_Core_Loader {

	/**
	 * Plugin object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $plugin The plugin object.
	 */
	public $plugin;

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
	 * Document object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $document The Document object.
	 */
	public $document;

	/**
	 * Entry object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $entry The Entry object.
	 */
	public $entry;

	/**
	 * Site Settings object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $settings_site The Site Settings object.
	 */
	public $settings_site;

	/**
	 * Navigation object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $nav The Navigation object.
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
	 * Editor object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $editor The Editor object.
	 */
	public $editor;

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
	 * @access private
	 * @var string $classes_path Relative path to the classes directory.
	 */
	private $classes_path = 'includes/core/classes/';

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 *
	 * @param object $plugin The plugin object.
	 */
	public function __construct( $plugin ) {

		// Store reference to plugin.
		$this->plugin = $plugin;

		// Initialise.
		$this->initialise();

		// Use translation.
		add_action( 'plugins_loaded', [ $this, 'translation' ] );

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

		// Bootstrap core.
		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

		/**
		 * Fires when CommentPress Core has loaded.
		 *
		 * @since 3.6.3
		 */
		do_action_deprecated( 'commentpress_loaded', [], '4.0', 'commentpress/core/loaded' );

		/**
		 * Fires when CommentPress Core has fully loaded.
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
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-comments.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-theme.php';

		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-settings-site.php';

		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-display.php';

		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-navigation.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-document.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-entry.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-parser.php';

		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-device.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-editor.php';

		// Include legacy class files.
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-revisions.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-pages-legacy.php';

		// Include ajax class files.
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-ajax-loader.php';

		// Include compatibility class files.
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-bp-core.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-plugins.php';

		/**
		 * Fires when class files have been included.
		 *
		 * @since 3.6.2
		 */
		do_action_deprecated( 'commentpress_after_includes', [], '4.0', 'commentpress/core/loaded' );

	}

	/**
	 * Sets up this plugin's objects.
	 *
	 * @since 4.0
	 */
	public function setup_objects() {

		// Initialise core objects.
		$this->db = new CommentPress_Core_Database( $this );
		$this->comments = new CommentPress_Core_Comments( $this );
		$this->theme = new CommentPress_Core_Theme( $this );

		$this->settings_site = new CommentPress_Core_Settings_Site( $this );

		$this->display = new CommentPress_Core_Display( $this );

		$this->nav = new CommentPress_Core_Navigator( $this );
		$this->document = new CommentPress_Core_Document( $this );
		$this->entry = new CommentPress_Core_Entry( $this );
		$this->parser = new CommentPress_Core_Parser( $this );

		$this->device = new CommentPress_Core_Device( $this );
		$this->editor = new CommentPress_Core_Editor( $this );

		// Initialise legacy objects.
		$this->revisions = new CommentPress_Core_Revisions( $this );
		$this->pages_legacy = new CommentPress_Core_Pages_Legacy( $this );

		// Initialise ajax objects.
		$this->ajax = new CommentPress_AJAX_Loader( $this );

		// Initialise compatibility objects.
		$this->bp = new CommentPress_Core_BuddyPress( $this );
		$this->plugins = new CommentPress_Core_Plugins( $this );

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed.
	 */
	public function register_hooks() {

		// Act when this plugin is activated.
		add_action( 'commentpress/activated', [ $this, 'plugin_activated' ], 20 );

		// Act when this plugin is deactivated.
		add_action( 'commentpress/deactivated', [ $this, 'plugin_deactivated' ], 10 );

		/**
		 * Fires when callbacks have been added.
		 *
		 * @since 3.6.2
		 */
		do_action_deprecated( 'commentpress_after_hooks', [], '4.0', 'commentpress/core/loaded' );

	}

	/**
	 * Runs when the plugin is activated.
	 *
	 * @since 3.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_activated( $network_wide = false ) {

		// Bail if plugin is network activated.
		if ( $network_wide ) {
			return;
		}

		/**
		 * Fires when plugin is activated.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Core_Database::activate() (Priority: 10)
		 * * CommentPress_Core_Comments::activate() (Priority: 20)
		 * * CommentPress_Core_Theme::activate() (Priority: 30)
		 * * CommentPress_Core_Pages_Legacy::activate() (Priority: 40)
		 * * CommentPress_Multisite_Sites::core_site_activated() (Priority: 50)
		 *
		 * @since 4.0
		 *
		 * @param bool $network_wide True if network-activated, false otherwise.
		 */
		do_action( 'commentpress/core/activate', $network_wide );

	}

	/**
	 * Runs when the plugin is deactivated.
	 *
	 * @since 3.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_deactivated( $network_wide = false ) {

		// Bail if plugin is network activated.
		if ( $network_wide ) {
			return;
		}

		/**
		 * Fires when plugin is deactivated.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Core_Database::deactivate() (Priority: 10)
		 * * CommentPress_Core_Pages_Legacy::deactivate() (Priority: 20)
		 * * CommentPress_Core_Theme::deactivate() (Priority: 30)
		 * * CommentPress_Core_Comments::deactivate() (Priority: 40)
		 * * CommentPress_Multisite_Sites::core_site_deactivated() (Priority: 50)
		 *
		 * @since 4.0
		 *
		 * @param bool $network_wide True if network-activated, false otherwise.
		 */
		do_action( 'commentpress/core/deactivate', $network_wide );

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
		return $this->nav->setting_post_type_get();
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
		return $this->parser->comments_sorted_get( $post_ID );
	}

	/**
	 * Retrieves "Minimise All" button.
	 *
	 * @since 3.4
	 *
	 * @param str $sidebar The sidebar identifier - 'comments', 'toc' or 'activity'.
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
		return $this->theme->sidebar->default_get();
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
		return $this->theme->sidebar->order_get();
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
		return $this->parser->text_signature_field_get();
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
		return $this->parser->text_signature_get( $para_num );
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
	 * Is this Blog a Group Blog?
	 *
	 * @since 3.4
	 *
	 * @return bool $bp_groupblog True when current Blog is a Group Blog, false otherwise.
	 */
	public function is_groupblog() {
		_deprecated_function( __METHOD__, '4.0' );
		return $this->bp->is_groupblog();
	}

}
