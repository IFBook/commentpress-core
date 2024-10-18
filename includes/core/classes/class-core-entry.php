<?php
/**
 * CommentPress Core Entry class.
 *
 * Handles Entry functionality in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Entry Class.
 *
 * This class provides Entry functionality in CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Entry {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_Core_Loader
	 */
	public $core;

	/**
	 * Metabox object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_Core_Entry_Metabox
	 */
	public $metabox;

	/**
	 * Formatter object.
	 *
	 * @since 3.3
	 * @access public
	 * @var CommentPress_Core_Entry_Formatter
	 */
	public $formatter;

	/**
	 * Single Entry object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_Core_Entry_Single
	 */
	public $single;

	/**
	 * Relative path to the classes directory.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $classes_path = 'includes/core/classes/';

	/**
	 * Constructor.
	 *
	 * @since 4.0
	 *
	 * @param CommentPress_Core_Loader $core Reference to the core loader object.
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

		// Only do this once.
		static $done;
		if ( isset( $done ) && true === $done ) {
			return;
		}

		// Bootstrap object.
		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

		/**
		 * Fires when the Entry object has loaded.
		 *
		 * Used internally to bootstrap objects.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/entry/loaded' );

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
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-entry-metabox.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-entry-formatter.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-entry-single.php';

	}

	/**
	 * Sets up the objects in this class.
	 *
	 * @since 4.0
	 */
	public function setup_objects() {

		// Initialise objects.
		$this->metabox   = new CommentPress_Core_Entry_Metabox( $this );
		$this->formatter = new CommentPress_Core_Entry_Formatter( $this );
		$this->single    = new CommentPress_Core_Entry_Single( $this );

	}

	/**
	 * Registers hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

	}

	// -------------------------------------------------------------------------

	/**
	 * Checks if the current Entry is the WordPress Login Page.
	 *
	 * @since 4.0
	 *
	 * @return bool $is_login_page True if the current Page is the WordPress Login Page, false otherwise.
	 */
	public function is_login_page() {

		// Assume not.
		$is_login_page = false;

		// Check via SERVER vars.
		$script = isset( $_SERVER['SCRIPT_FILENAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_FILENAME'] ) ) : '';
		if ( ! empty( $script ) && 'wp-login.php' === basename( $script ) ) {
			$is_login_page = true;
		}

		// --<
		return $is_login_page;

	}

	/**
	 * Checks if the current Entry is the WordPress Signup Page.
	 *
	 * @since 4.0
	 *
	 * @return bool $is_signup_page True if the current Page is the WordPress Signup Page, false otherwise.
	 */
	public function is_signup_page() {

		// Assume not.
		$is_signup_page = false;

		// Bail if not multisite.
		if ( ! is_multisite() ) {
			return $is_signup_page;
		}

		// Check via SERVER vars.
		$script = isset( $_SERVER['SCRIPT_FILENAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_FILENAME'] ) ) : '';
		if ( ! empty( $script ) && 'wp-signup.php' === basename( $script ) ) {
			$is_signup_page = true;
		}

		// --<
		return $is_signup_page;

	}

	/**
	 * Checks if the current Entry is the WordPress Activation Page.
	 *
	 * @since 4.0
	 *
	 * @return bool $is_activate_page True if the current Page is the WordPress Activation Page, false otherwise.
	 */
	public function is_activate_page() {

		// Assume not.
		$is_activate_page = false;

		// Bail if not multisite.
		if ( ! is_multisite() ) {
			return $is_activate_page;
		}

		// Check via SERVER vars.
		$script = isset( $_SERVER['SCRIPT_FILENAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_FILENAME'] ) ) : '';
		if ( ! empty( $script ) && 'wp-activate.php' === basename( $script ) ) {
			$is_activate_page = true;
		}

		// --<
		return $is_activate_page;

	}

}
