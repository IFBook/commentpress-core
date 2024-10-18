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

/**
 * CommentPress Multisite Loader Class.
 *
 * This class loads all Multisite functionality.
 *
 * @since 3.3
 */
class CommentPress_Multisite_Loader {

	/**
	 * Plugin object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_Plugin
	 */
	public $plugin;

	/**
	 * Database object.
	 *
	 * @since 3.3
	 * @access public
	 * @var CommentPress_Core_Database
	 */
	public $db;

	/**
	 * Sites object.
	 *
	 * @since 3.3
	 * @since 4.0 Renamed.
	 * @access public
	 * @var CommentPress_Multisite_Sites
	 */
	public $sites;

	/**
	 * Single Site object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_Multisite_Site
	 */
	public $site;

	/**
	 * Network Settings object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_Multisite_Settings_Network
	 */
	public $settings_network;

	/**
	 * Site Settings object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_Core_Settings_Site
	 */
	public $settings_site;

	/**
	 * Revisions object.
	 *
	 * @since 3.3
	 * @access public
	 * @var CommentPress_Multisite_Revisions
	 */
	public $revisions;

	/**
	 * BuddyPress compatibility object.
	 *
	 * @since 3.3
	 * @access public
	 * @var CommentPress_Multisite_BuddyPress
	 */
	public $bp;

	/**
	 * Relative path to the classes directory.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $classes_path = 'includes/multisite/classes/';

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param CommentPress_Plugin $plugin The plugin object.
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
		if ( isset( $done ) && true === $done ) {
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
		$this->db               = new CommentPress_Multisite_Database( $this );
		$this->sites            = new CommentPress_Multisite_Sites( $this );
		$this->site             = new CommentPress_Multisite_Site( $this );
		$this->settings_network = new CommentPress_Multisite_Settings_Network( $this );
		$this->settings_site    = new CommentPress_Multisite_Settings_Site( $this );
		$this->revisions        = new CommentPress_Multisite_Revisions( $this );
		$this->bp               = new CommentPress_Multisite_BuddyPress( $this );

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

		// Act when this plugin is activated.
		add_action( 'commentpress/activated', [ $this, 'plugin_activated' ], 10 );

		// Act when this plugin is deactivated.
		add_action( 'commentpress/deactivated', [ $this, 'plugin_deactivated' ], 20 );

	}

	/**
	 * Runs when the plugin is activated.
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_activated( $network_wide = false ) {

		/**
		 * Fires when plugin is activated.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Multisite_Database::plugin_activated() (Priority: 10)
		 * * CommentPress_Multisite_Sites::plugin_activated() (Priority: 20)
		 *
		 * @since 4.0
		 *
		 * @param bool $network_wide True if network-activated, false otherwise.
		 */
		do_action( 'commentpress/multisite/activate', $network_wide );

	}

	/**
	 * Runs when the plugin is deactivated.
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_deactivated( $network_wide = false ) {

		/**
		 * Fires when plugin is activated.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Multisite_Sites::plugin_deactivated() (Priority: 10)
		 * * CommentPress_Multisite_Database::plugin_deactivated() (Priority: 20)
		 *
		 * @since 4.0
		 *
		 * @param bool $network_wide True if network-activated, false otherwise.
		 */
		do_action( 'commentpress/multisite/deactivate', $network_wide );

	}

}
