<?php
/**
 * CommentPress Multisite Loader class.
 *
 * This used to be the CommentPress for Multisite plugin, but is now merged into
 * a unified plugin that covers all situations.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Define version.
define( 'COMMENTPRESS_MU_PLUGIN_VERSION', '1.0' );

/**
 * CommentPress Multisite Loader Class.
 *
 * This class loads all Multisite compatibility.
 *
 * @since 3.3
 */
class CommentPress_Multisite_Loader {

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
	 * @since 3.3
	 * @access public
	 * @var object $db The database object.
	 */
	public $db;

	/**
	 * Sites object.
	 *
	 * @since 3.3
	 * @since 4.0 Renamed.
	 * @access public
	 * @var object $sites The Sites object reference.
	 */
	public $sites;

	/**
	 * Single Site object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $site The Single Site object reference.
	 */
	public $site;

	/**
	 * Network Settings object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $settings_network The Network Settings object.
	 */
	public $settings_network;

	/**
	 * Site Settings object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $settings_site The Site Settings object.
	 */
	public $settings_site;

	/**
	 * Revisions object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $revisions The revisions object reference.
	 */
	public $revisions;

	/**
	 * BuddyPress compatibility object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $bp The BuddyPress object reference.
	 */
	public $bp;

	/**
	 * Classes directory path.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $classes_path Relative path to the classes directory.
	 */
	public $classes_path = 'includes/multisite/classes/';

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param object $plugin The plugin object.
	 */
	public function __construct( $plugin ) {

		// Store reference to plugin.
		$this->plugin = $plugin;

		// Initialise.
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

		// Bootstrap multisite.
		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

		/**
		 * Fire when CommentPress Multisite has loaded.
		 *
		 * Used internally to bootstrap objects.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/multisite/loaded' );

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
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-multisite-database.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-multisite-sites.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-multisite-site.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-multisite-settings-network.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-multisite-settings-site.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-multisite-revisions.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-multisite-bp.php';

	}

	/**
	 * Sets up this plugin's objects.
	 *
	 * @since 4.0
	 */
	public function setup_objects() {

		// Initialise objects.
		$this->db = new CommentPress_Multisite_Database( $this );
		$this->sites = new CommentPress_Multisite_Sites( $this );
		$this->site = new CommentPress_Multisite_Site( $this );
		$this->settings_network = new CommentPress_Multisite_Settings_Network( $this );
		$this->settings_site = new CommentPress_Multisite_Settings_Site( $this );
		$this->revisions = new CommentPress_Multisite_Revisions( $this );
		$this->bp = new CommentPress_Multisite_BuddyPress( $this );

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

		/*
		// Check for network activation.
		add_action( 'activated_plugin',  [ $this, 'network_activated' ], 10, 2 );

		// Check for network deactivation.
		add_action( 'deactivated_plugin', [ $this, 'network_deactivated' ], 10, 2 );
		*/

	}

	/**
	 * This plugin has been network-activated.
	 *
	 * @since 3.3
	 *
	 * @param str $plugin The plugin file.
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function network_activated( $plugin, $network_wide = null ) {

		/*
		// Bail if it's not our plugin.
		if ( $plugin !== plugin_basename( COMMENTPRESS_PLUGIN_FILE ) ) {
			return;
		}

		// Bail if plugin not network activated.
		if ( ! $network_wide ) {
			return;
		}

		// If upgrading, we might want to migrate each existing instance into a
		// CommentPress Core Blog.
		*/

	}

	/**
	 * This plugin has been network-deactivated.
	 *
	 * @since 3.3
	 *
	 * @param str $plugin The plugin file.
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function network_deactivated( $plugin, $network_wide = null ) {

		/*
		// Bail if it's not our plugin.
		if ( $plugin !== plugin_basename( COMMENTPRESS_PLUGIN_FILE ) ) {
			return;
		}

		// Bail if plugin not network activated.
		if ( ! $network_wide ) {
			return;
		}

		// Do we want to trigger deactivation_hook for all sub-blogs?
		// Or do we want to convert each instance into a self-contained
		// CommentPress Core Blog?
		*/

	}

}
