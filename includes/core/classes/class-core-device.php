<?php
/**
 * CommentPress Core Device class.
 *
 * Handles functionality related to Devices in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Device Class.
 *
 * This class provides functionality related to Device in CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Device {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_Core_Loader
	 */
	public $core;

	/**
	 * Device is mobile flag.
	 *
	 * @since 4.0
	 * @access public
	 * @var bool
	 */
	public $is_mobile;

	/**
	 * Device is tablet flag.
	 *
	 * @since 4.0
	 * @access public
	 * @var bool
	 */
	public $is_tablet;

	/**
	 * Device is touchscreen flag.
	 *
	 * @since 4.0
	 * @access public
	 * @var bool
	 */
	public $is_mobile_touch;

	/**
	 * Constructor.
	 *
	 * @since 4.0
	 *
	 * @param object $core Reference to the core plugin object.
	 */
	public function __construct( $core ) {

		// Store reference to core plugin object.
		$this->core = $core;

		// Init when this plugin is fully loaded.
		add_action( 'commentpress/core/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this object.
	 *
	 * @since 4.0
	 */
	public function initialise() {

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

		// Check device before any theme-related code has run.
		add_action( 'init', [ $this, 'detect' ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Checks the device.
	 *
	 * @since 3.4
	 */
	public function detect() {

		// Don't include in admin or wp-login.php.
		if ( is_admin() || ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' == $GLOBALS['pagenow'] ) ) {
			return;
		}

		// Test for mobile user agents.
		$this->test_for_mobile();

	}

	// -------------------------------------------------------------------------

	/**
	 * Sets class properties for mobile browsers.
	 *
	 * @since 3.4
	 */
	public function test_for_mobile() {

		// Init mobile flag.
		$this->is_mobile = false;

		// Init tablet flag.
		$this->is_tablet = false;

		// Init touch flag.
		$this->is_mobile_touch = false;

		// Bail if there is no user agent.
		if ( ! isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			return;
		}

		// The old CommentPress also includes Mobile_Detect.
		if ( ! class_exists( 'Mobile_Detect' ) ) {
			include_once COMMENTPRESS_PLUGIN_PATH . 'includes/core/assets/includes/mobile-detect/Mobile_Detect.php';
		}

		// Init.
		$detect = new Mobile_Detect();

		// Overwrite flag if mobile.
		if ( $detect->isMobile() ) {
			$this->is_mobile = true;
		}

		// Overwrite flag if tablet.
		if ( $detect->isTablet() ) {
			$this->is_tablet = true;
		}

		// To guess at touch devices, we assume *either* phone *or* tablet.
		if ( $this->is_mobile || $this->is_tablet ) {
			$this->is_mobile_touch = true;
		}

	}

	/**
	 * Returns class properties for mobile browsers.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_mobile True if mobile device, false otherwise.
	 */
	public function is_mobile() {

		// Do we have the property?
		if ( ! isset( $this->is_mobile ) ) {
			$this->test_for_mobile();
		}

		// --<
		return $this->is_mobile;

	}

	/**
	 * Returns class properties for tablet browsers.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_tablet True if tablet device, false otherwise.
	 */
	public function is_tablet() {

		// Do we have the property?
		if ( ! isset( $this->is_tablet ) ) {
			$this->test_for_mobile();
		}

		// --<
		return $this->is_tablet;

	}

	/**
	 * Returns class properties for touch devices.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_touch True if touch device, false otherwise.
	 */
	public function is_touch() {

		// Do we have the property?
		if ( ! isset( $this->is_mobile_touch ) ) {
			$this->test_for_mobile();
		}

		// --<
		return $this->is_mobile_touch;

	}

}
