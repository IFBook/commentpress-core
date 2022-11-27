<?php
/**
 * Plugin Name: CommentPress Core
 * Plugin URI: http://www.futureofthebook.org/commentpress/
 * Description: CommentPress allows readers to comment in the margins of a text. You can use it to annotate, gloss, workshop, debate and more!
 * Author: Institute for the Future of the Book
 * Version: 4.0a
 * Author URI: http://www.futureofthebook.org
 * Text Domain: commentpress-core
 * Domain Path: /languages
 *
 * Special thanks to:
 *
 * Eddie Tejeda for CommentPress 2.0: https://www.visudo.com
 * Mark James for the icons: http://www.famfamfam.com/lab/icons/silk/
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Set version.
define( 'COMMENTPRESS_VERSION', '4.0a' );

// Store reference to this file.
if ( ! defined( 'COMMENTPRESS_PLUGIN_FILE' ) ) {
	define( 'COMMENTPRESS_PLUGIN_FILE', __FILE__ );
}

// Store URL to this plugin's directory.
if ( ! defined( 'COMMENTPRESS_PLUGIN_URL' ) ) {
	define( 'COMMENTPRESS_PLUGIN_URL', plugin_dir_url( COMMENTPRESS_PLUGIN_FILE ) );
}
// Store PATH to this plugin's directory.
if ( ! defined( 'COMMENTPRESS_PLUGIN_PATH' ) ) {
	define( 'COMMENTPRESS_PLUGIN_PATH', plugin_dir_path( COMMENTPRESS_PLUGIN_FILE ) );
}

/**
 * CommentPress Core Class.
 *
 * A class that handles plugin functionality.
 *
 * @since 4.0
 */
class CommentPress_Core {

	/**
	 * Plugin context flag.
	 *
	 * Replaces the legacy "COMMENTPRESS_PLUGIN_CONTEXT" constant.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $plugin_context The plugin context flag.
	 */
	public $plugin_context;

	/**
	 * Common directory path.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $common_path Relative path to the common directory.
	 */
	public $common_path = 'includes/common/';

	/**
	 * Core directory path.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $core_path Relative path to the core directory.
	 */
	public $core_path = 'includes/core/';

	/**
	 * Multisite directory path.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $multisite_path Relative path to the multisite directory.
	 */
	public $multisite_path = 'includes/multisite/';

	/**
	 * Constructor.
	 *
	 * @since 4.0
	 */
	public function __construct() {

		// Initialise plugin.
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

		// Include files.
		$this->include_files();

		// Establish context.
		$this->plugin_context();

		// Register theme directory.
		$this->theme_directory_register();

		// Maybe bootstrap multisite.
		$this->multisite_bootstrap();

		// Maybe bootstrap core.
		$this->core_bootstrap();

		// Register hooks.
		$this->register_hooks();

		/**
		 * Fires when CommentPress has loaded.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/loaded' );

		// We're done.
		$done = true;

	}

	/**
	 * Includes files.
	 *
	 * @since 4.0
	 */
	public function include_files() {

		// Include common files.
		require_once COMMENTPRESS_PLUGIN_PATH . $this->common_path . 'common-functions.php';

	}

	/**
	 * Determines plugin context.
	 *
	 * Worth noting that during network activation, this plugin is not present
	 * in the "active_sitewide_plugins" array.
	 *
	 * @since 4.0
	 */
	public function plugin_context() {

		// If not Multisite, then must be Single Site install.
		if ( ! is_multisite() ) {
			$this->plugin_context = 'standard';
			return;
		}

		// Is the plugin network activated?
		if ( $this->is_network_activated() ) {
			$this->plugin_context = 'mu_sitewide';
			return;
		}

		// Optional activation per Site in Multisite.
		$this->plugin_context = 'mu_optional';

	}

	/**
	 * Registers the theme directory with WordPress.
	 *
	 * NOTE: in multisite, child themes are registered as broken if the plugin
	 * is not network-enabled. Make sure child themes have instructions.
	 *
	 * There are further complex issues when in Multisite:
	 *
	 * First scenario:
	 *
	 * * If the plugin is NOT initially network-enabled
	 * * But it IS enabled on one or more Blogs on the network
	 * * And the plugin in THEN network-enabled
	 *
	 * Second scenario:
	 *
	 * * If the plugin IS initially network-enabled
	 * * And it IS activated on one or more Blogs on the network
	 * * And the plugin in THEN network-disabled
	 *
	 * If installs stick to one or the other, then all works as expected.
	 *
	 * @since 4.0
	 */
	public function theme_directory_register() {

		// Register our themes directory.
		register_theme_directory( plugin_dir_path( COMMENTPRESS_PLUGIN_FILE ) . 'themes' );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets a reference to the multisite loader object.
	 *
	 * @since 4.0
	 *
	 * @return CommentPress_Multisite_Loader $commentpress_mu The multisite loader reference.
	 */
	public function multisite() {

		// Declare as global to retain backwards compatibility.
		global $commentpress_mu;

		// Maybe return reference.
		if ( isset( $commentpress_mu ) ) {
			if ( $commentpress_mu instanceof CommentPress_Multisite_Loader ) {
				return $commentpress_mu;
			}
		}

		// Not present.
		return false;

	}

	/**
	 * Maybe bootstrap multisite.
	 *
	 * @since 4.0
	 */
	public function multisite_bootstrap() {

		// Bail if we have not activated network-wide.
		if ( $this->plugin_context !== 'mu_sitewide' ) {
			return;
		}

		// Include multisite loader class file.
		$this->multisite_include();

		// Initialise multisite.
		$this->multisite_initialise();

	}

	/**
	 * Includes the multisite loader file.
	 *
	 * @since 4.0
	 */
	public function multisite_include() {

		// Include multisite loader class file.
		if ( ! class_exists( 'CommentPress_Multisite_Loader' ) ) {
			require_once COMMENTPRESS_PLUGIN_PATH . $this->multisite_path . 'class-multisite-loader.php';
		}

	}

	/**
	 * Initialises multisite.
	 *
	 * @since 4.0
	 *
	 * @return CommentPress_Multisite_Loader $commentpress_mu The multisite loader reference.
	 */
	function multisite_initialise() {

		// Declare as global to retain backwards compatibility.
		global $commentpress_mu;

		// Instantiate if not yet instantiated.
		if ( ! isset( $commentpress_mu ) ) {
			$commentpress_mu = new CommentPress_Multisite_Loader( $this );
		}

		// --<
		return $commentpress_mu;

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets a reference to the core loader object.
	 *
	 * @since 4.0
	 *
	 * @return CommentPress_Core_Loader $commentpress_core The core loader reference, or false on failure.
	 */
	public function core() {

		// Declare as global to retain backwards compatibility.
		global $commentpress_core;

		// Maybe return reference.
		if ( isset( $commentpress_core ) ) {
			if ( $commentpress_core instanceof CommentPress_Core_Loader ) {
				return $commentpress_core;
			}
		}

		// Not present.
		return false;

	}

	/**
	 * Maybe bootstrap core.
	 *
	 * @since 4.0
	 */
	public function core_bootstrap() {

		// Bail if plugin is activated network-wide.
		if ( $this->plugin_context !== 'standard' ) {
			return;
		}

		// Include core loader class file.
		$this->core_include();

		// Initialise core.
		$this->core_initialise();

	}

	/**
	 * Includes the core loader file.
	 *
	 * @since 4.0
	 */
	public function core_include() {

		// Include core loader class file.
		if ( ! class_exists( 'CommentPress_Core_Loader' ) ) {
			require_once COMMENTPRESS_PLUGIN_PATH . $this->core_path . 'class-core-loader.php';
		}

	}

	/**
	 * Initialises core.
	 *
	 * @since 4.0
	 *
	 * @return CommentPress_Core_Loader $commentpress_core The core loader reference.
	 */
	public function core_initialise() {

		// Declare as global to retain backwards compatibility.
		global $commentpress_core;

		// Instantiate if not already instantiated.
		if ( ! isset( $commentpress_core ) ) {
			$commentpress_core = new CommentPress_Core_Loader( $this );
		}

		// Return reference.
		return $commentpress_core;

	}

	// -------------------------------------------------------------------------

	/**
	 * Register WordPress hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

		// Add links to Settings Page.
		add_filter( 'network_admin_plugin_action_links', [ $this, 'action_links' ], 20, 2 );
		add_filter( 'plugin_action_links', [ $this, 'action_links' ], 20, 2 );

	}

	/**
	 * Adds "Donate" link to all CommentPress action links.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @param array $links The existing links array.
	 * @param str $file The name of the plugin file.
	 * @return array $links The modified links array.
	 */
	public function action_links( $links, $file ) {

		// Bail if not this plugin.
		if ( $file !== plugin_basename( dirname( COMMENTPRESS_PLUGIN_FILE ) . '/commentpress-core.php' ) ) {
			return $links;
		}

		// Add PayPal link.
		$paypal = 'https://www.paypal.com/donate/?cmd=_s-xclick&hosted_button_id=PZSKM8T5ZP3SC';
		$links[] = '<a href="' . esc_url( $paypal ) . '" target="_blank">' . __( 'Donate!', 'commentpress-core' ) . '</a>';

		// --<
		return $links;

	}

	// -------------------------------------------------------------------------

	/**
	 * Checks if this plugin is network activated.
	 *
	 * @since 4.0
	 *
	 * @return bool $is_network_active True if network activated, false otherwise.
	 */
	public function is_network_activated() {

		// Only need to test once.
		static $is_network_active;

		// Have we done this already?
		if ( isset( $is_network_active ) ) {
			return $is_network_active;
		}

		// If not multisite, it cannot be.
		if ( ! is_multisite() ) {
			$is_network_active = false;
			return $is_network_active;
		}

		// Make sure plugin file is included when outside admin.
		if ( ! function_exists( 'is_plugin_active_for_network' ) ) {
			require_once ABSPATH . '/wp-admin/includes/plugin.php';
		}

		// Get path from "plugins" directory to this plugin.
		$this_plugin = plugin_basename( COMMENTPRESS_PLUGIN_FILE );

		// Test if network activated.
		$is_network_active = is_plugin_active_for_network( $this_plugin );

		// --<
		return $is_network_active;

	}

}

/**
 * Bootstrap plugin if not yet loaded and returns reference.
 *
 * @since 4.0
 *
 * @return CommentPress_Core $plugin The plugin reference.
 */
function commentpress() {

	// Maybe bootstrap plugin.
	static $plugin;
	if ( ! isset( $plugin ) ) {
		$plugin = new CommentPress_Core();
	}

	// Return reference.
	return $plugin;

}

// Bootstrap immediately.
commentpress();

/**
 * Performs plugin activation tasks.
 *
 * @since 4.0
 */
function commentpress_activated() {

	/**
	 * Fires when this plugin has been activated.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/plugin/activated' );

}

// Activation.
register_activation_hook( __FILE__, 'commentpress_activated' );

/**
 * Performs plugin deactivation tasks.
 *
 * @since 4.0
 */
function commentpress_deactivated() {

	/**
	 * Fires when this plugin has been deactivated.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/plugin/deactivated' );

}

// Deactivation.
register_deactivation_hook( __FILE__, 'commentpress_deactivated' );

/*
 * Uninstall uses the 'uninstall.php' method.
 *
 * @see https://developer.wordpress.org/reference/functions/register_uninstall_hook/
 */
