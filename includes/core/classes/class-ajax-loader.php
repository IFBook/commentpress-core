<?php
/**
 * CommentPress AJAX Loader class.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress AJAX Loader Class.
 *
 * This class loads all AJAX compatibility.
 *
 * @since 3.3
 */
class CommentPress_AJAX_Loader {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_Core_Loader
	 */
	public $core;

	/**
	 * AJAX Comments object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_AJAX_Comments
	 */
	public $comments;

	/**
	 * Infinite Scroll object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_AJAX_Infinite_Scroll
	 */
	public $infinite;

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
	 * @param object $core Reference to the core plugin object.
	 */
	public function __construct( $core ) {

		// Store reference to core plugin object.
		$this->core = $core;

		// Init when this plugin is fully loaded.
		add_action( 'commentpress/core/loaded', [ $this, 'initialise' ] );

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

		// Bootstrap plugin.
		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

		/**
		 * Fires when CommentPress AJAX has loaded.
		 *
		 * Used internally to bootstrap objects.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/ajax/loaded' );

		// We're done.
		$done = true;

	}

	/**
	 * Includes class files.
	 *
	 * @since 4.0
	 */
	public function include_files() {

		// Include AJAX Comments class file.
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-ajax-comments.php';

		/*
		// Include Infinite Scroll class file.
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-ajax-infinite.php';
		*/

	}

	/**
	 * Sets up this plugin's objects.
	 *
	 * @since 4.0
	 */
	public function setup_objects() {

		// Initialise AJAX Comments object.
		$this->comments = new CommentPress_AJAX_Comments( $this );

		/*
		// Initialise Infinite Scroll object.
		$this->infinite = new CommentPress_AJAX_Infinite_Scroll( $this );
		*/

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

		/*
		// Check for activation.
		add_action( 'activated_plugin',  [ $this, 'activated' ], 10, 2 );

		// Check for deactivation.
		add_action( 'deactivated_plugin', [ $this, 'deactivated' ], 10, 2 );
		*/

	}

	// -------------------------------------------------------------------------

	/**
	 * Checks whether AJAX functionality can be activated.
	 *
	 * @since 4.0
	 *
	 * @return bool $allowed True if AJAX functionality can activate, false otherwise.
	 */
	public function can_activate() {

		// Access global.
		global $post;

		// Disallow if no Post, such as a 404.
		if ( ! ( $post instanceof WP_Post ) ) {
			return false;
		}

		// Disallow if it's the Theme My Login Page.
		if ( $this->core->plugins->is_theme_my_login_page() ) {
			return false;
		}

		// Init.
		$allowed = true;

		// Disallow generally if Page doesn't allow commenting.
		if ( ! $this->core->parser->is_commentable() ) {
			$allowed = false;
		}

		// But, allow General Comments Page.
		if ( (int) $post->ID === (int) $this->core->db->setting_get( 'cp_general_comments_page' ) ) {
			$allowed = true;
		}

		// --<
		return $allowed;

	}

}
