<?php
/**
 * CommentPress Core Editor class.
 *
 * Handles Editor functionality in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Editor Class.
 *
 * This class provides Editor functionality in CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Editor {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * Comments Editor object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $comments The Comments Editor object.
	 */
	public $comments;

	/**
	 * Content Editor object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $content The Content Editor object.
	 */
	public $content;

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
		 * Fires when the Editor object has loaded.
		 *
		 * Used internally to bootstrap objects.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/editor/loaded' );

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
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-editor-comments.php';
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-editor-content.php';

	}

	/**
	 * Sets up the objects in this class.
	 *
	 * @since 4.0
	 */
	public function setup_objects() {

		// Initialise objects.
		$this->comments = new CommentPress_Core_Editor_Comments( $this );
		$this->content  = new CommentPress_Core_Editor_Content( $this );

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

	}

}
