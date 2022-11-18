<?php
/**
 * CommentPress Core for Multisite Loader class.
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
 * CommentPress Core Multisite Loader Class.
 *
 * This class loads all Multisite compatibility.
 *
 * @since 3.3
 */
class CommentPress_Multisite_Loader {

	/**
	 * Database object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $db The database object.
	 */
	public $db;

	/**
	 * Admin object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $db The admin object.
	 */
	public $admin;

	/**
	 * Multisite object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $mu The multisite object reference.
	 */
	public $multisite;

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
	 * BuddyPress Groupblog compatibility object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $workshop The workshop object reference.
	 */
	public $workshop;

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 */
	public function __construct() {

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

		// Bootstrap plugin.
		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

		// Initialise db for multisite.
		$this->db->options_initialise( 'multisite' );

		/**
		 * Broadcast that CommentPress Core Multisite has loaded.
		 *
		 * Used internally to bootstrap objects.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/multisite/loaded' );

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
		require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-multisite/class_commentpress_mu_db.php';
		require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-multisite/class_commentpress_mu_admin.php';
		require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-multisite/class_commentpress_mu_ms.php';
		require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-multisite/class_commentpress_mu_revisions.php';

	}

	/**
	 * Sets up this plugin's objects.
	 *
	 * @since 4.0
	 */
	public function setup_objects() {

		// Initialise objects.
		$this->db = new CommentPress_Multisite_Database( $this );
		$this->admin = new CommentPress_Multisite_Admin( $this );
		$this->multisite = new CommentPress_Multisite_WordPress( $this );
		$this->revisions = new CommentPress_Multisite_Revisions( $this );

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

		// Load when BuddyPress is loaded.
		add_action( 'bp_include', [ $this, 'buddypress_init' ] );

	}

	/**
	 * BuddyPress initialisation.
	 *
	 * @since 3.3
	 */
	public function buddypress_init() {

		// Bootstrap plugin.
		$this->buddypress_include_files();
		$this->buddypress_setup_objects();

		// Initialise db for BuddyPress.
		$this->db->options_initialise( 'buddypress' );

		/**
		 * Broadcast that BuddyPress has loaded.
		 *
		 * Used internally to bootstrap objects.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/multisite/bp/loaded' );

	}

	/**
	 * Includes BuddyPress class files.
	 *
	 * @since 4.0
	 */
	public function buddypress_include_files() {

		// Include class files.
		require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-multisite/class_commentpress_mu_bp.php';
		require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-multisite/class_commentpress_mu_workshop.php';

	}

	/**
	 * Sets up this plugin's BuddyPress objects.
	 *
	 * @since 4.0
	 */
	public function buddypress_setup_objects() {

		// Initialise objects.
		$this->bp = new CommentPress_Multisite_Buddypress( $this );
		$this->workshop = new CommentPress_Multisite_Buddypress_Groupblog( $this );

	}

	/**
	 * This plugin has been network-activated. (does not fire!)!
	 *
	 * @since 3.3
	 *
	 * @param str $plugin The plugin file.
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function network_activated( $plugin, $network_wide = null ) {

		/*
		// If it's our plugin.
		if ( $plugin == plugin_basename( COMMENTPRESS_PLUGIN_FILE ) ) {

			// Was it network deactivated?
			if ( $network_wide == true ) {

				// If upgrading, we need to migrate each existing instance into a CommentPress Core blog.

			}

		}
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
		// If it's our plugin.
		if ( $plugin == plugin_basename( COMMENTPRESS_PLUGIN_FILE ) ) {

			// Was it network deactivated?
			if ( $network_wide == true ) {

				// Do we want to trigger deactivation_hook for all sub-blogs?
				// Or do we want to convert each instance into a self-contained
				// CommentPress Core blog?

			}

		}
		*/

	}

}
