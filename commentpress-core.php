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

		// Maybe bootstrap core.
		$this->core_bootstrap();

		// Maybe bootstrap multisite.
		$this->multisite_bootstrap();

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
	 * @since 4.0
	 */
	public function plugin_context() {

		// If not multisite, then must be Single Site install.
		if ( ! is_multisite() ) {
			$this->plugin_context = 'standard';
			return;
		}

		// Check if our plugin is one of those activated sitewide.
		$this_plugin = plugin_basename( COMMENTPRESS_PLUGIN_FILE );

		/*
		 * Unfortunately, is_plugin_active_for_network() is not yet available so we
		 * have to do this manually.
		 *
		 * Also note: during network activation, this plugin is not yet present in
		 * the active_sitewide_plugins array.
		 */

		// Get sitewide plugins.
		$active_plugins = (array) get_site_option( 'active_sitewide_plugins' );

		// Is the plugin network activated?
		if ( isset( $active_plugins[ $this_plugin ] ) ) {
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
	 * Gets a reference to the core plugin object.
	 *
	 * @since 4.0
	 *
	 * @return CommentPress_Core_Loader $commentpress_core The plugin reference, or false on failure.
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

		/*
		--------------------------------------------------------------------------------
		Init Standalone.
		--------------------------------------------------------------------------------
		Note: we exclude activation on Network Admin Pages to avoid auto-installation
		on Main Site when the plugin is network activated.
		--------------------------------------------------------------------------------
		*/

		// Include Standalone.
		$this->core_include();

		// Only activate if in "standard" or "mu_optional" context.
		if (
			$this->plugin_context == 'standard' ||
			( $this->plugin_context == 'mu_optional' && ! is_network_admin() )
		) {

			// Activate CommentPress Core.
			$core = $this->core_activate();

			// Activation.
			register_activation_hook( COMMENTPRESS_PLUGIN_FILE, [ $core, 'activate' ] );

			// Deactivation.
			register_deactivation_hook( COMMENTPRESS_PLUGIN_FILE, [ $core, 'deactivate' ] );

			/*
			 * Uninstall uses the 'uninstall.php' method.
			 *
			 * @see https://developer.wordpress.org/reference/functions/register_uninstall_hook/
			 */

		}

	}

	/**
	 * Includes the core plugin loader file.
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
	 * Activates the core plugin.
	 *
	 * @since 4.0
	 *
	 * @return CommentPress_Core $commentpress_core The plugin reference.
	 */
	public function core_activate() {

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
	 * Gets a reference to the multisite plugin object.
	 *
	 * @since 4.0
	 *
	 * @return CommentPress_Multisite_Loader $commentpress_mu The multisite plugin reference.
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
	 * Maybe bootstrap multisite plugin.
	 *
	 * @since 4.0
	 */
	public function multisite_bootstrap() {

		// Bail if we have not activated network-wide.
		if ( $this->plugin_context !== 'mu_sitewide' ) {
			return;
		}

		// Include multisite plugin class file.
		$this->multisite_include();

		// Activate multisite plugin.
		$this->multisite_activate();

	}

	/**
	 * Includes the multisite plugin loader file.
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
	 * Activates the multisite plugin.
	 *
	 * @since 4.0
	 *
	 * @return CommentPress_Multisite_Loader $commentpress_mu The multisite plugin reference.
	 */
	function multisite_activate() {

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
	 * Adds "Donate" link to CommentPress plugin action links.
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
