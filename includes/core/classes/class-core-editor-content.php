<?php
/**
 * CommentPress Core Content Editor class.
 *
 * Handles functionality for the TinyMCE and Quicktags Content Editor.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Content Editor Class.
 *
 * This class provides functionality for the TinyMCE and Quicktags Content Editor.
 *
 * @since 4.0
 */
class CommentPress_Core_Editor_Content {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * The "Comment Block" Quicktag.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $quicktag The "Comment Block" Quicktag.
	 */
	public $quicktag = '<!--commentblock-->';

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

		// Comment Block Quicktag.
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Add scripts needed across all WordPress Admin Pages.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class and renamed.
	 *
	 * @param str $hook The requested Admin Page.
	 */
	public function enqueue_scripts( $hook ) {

		// Don't enqueue on "Edit Comment" screen.
		if ( 'comment.php' == $hook ) {
			return;
		}

		// Bail if the current User lacks permissions.
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// Add our Quicktag Javascript and dependencies.
		wp_enqueue_script(
			'commentpress_custom_quicktags',
			plugins_url( 'includes/core/assets/js/cp_quicktags_3.3.js', COMMENTPRESS_PLUGIN_FILE ),
			[ 'quicktags' ],
			COMMENTPRESS_VERSION, // Version.
			true // In footer.
		);

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our "Comment Block" button to TinyMCE.
	 *
	 * @since 4.0
	 */
	public function button_add() {

		// Only on back-end.
		if ( ! is_admin() ) {
			return;
		}

		// Don't bother doing this stuff if the current User lacks permissions.
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// Add only if User can edit in Rich-text Editor mode.
		if ( get_user_option( 'rich_editing' ) != 'true' ) {
			return;
		}

		add_filter( 'mce_buttons', [ $this, 'button_register' ] );
		add_filter( 'mce_external_plugins', [ $this, 'tinymce_plugin_load' ] );

	}

	/**
	 * Registers our "Comment Block" button with TinyMCE.
	 *
	 * @since 3.3
	 * @since 4.0 Moved to this class.
	 *
	 * @param array $buttons The existing button array.
	 * @return array $buttons The modified button array.
	 */
	public function button_register( $buttons ) {

		// Add our button to the editor button array.
		array_push( $buttons, '|', 'commentblock' );

		// --<
		return $buttons;

	}

	/**
	 * Loads the "Comment Block" TinyMCE plugin.
	 *
	 * @since 3.3
	 * @since 4.0 Moved to this class.
	 *
	 * @param array $plugin_array The existing TinyMCE plugin array.
	 * @return array $plugin_array The modified TinyMCE plugin array.
	 */
	public function tinymce_plugin_load( $plugin_array ) {

		// Add "Comment Block" script.
		$plugin_array['commentblock'] = get_template_directory_uri() . '/assets/js/tinymce/cp_editor_plugin.js';

		// --<
		return $plugin_array;

	}

	// -------------------------------------------------------------------------

	/**
	 * Checks if the content has our custom Quicktag.
	 *
	 * @since 3.0
	 * @since 4.0 Moved to this class.
	 *
	 * @param str $content The Post content.
	 * @return str $content The modified Post content.
	 */
	public function has_quicktag( $content ) {

		// Init.
		$return = false;

		// Override if we find <!--commentblock-->.
		if ( false !== strstr( $content, $this->quicktag ) ) {
			$return = true;
		}

		// --<
		return $return;

	}

	/**
	 * Removes our custom quicktag.
	 *
	 * @since 3.0
	 * @since 4.0 Moved to this class.
	 *
	 * @param str $content The Post content.
	 * @return str $content The modified Post content.
	 */
	public function strip_quicktag( $content ) {

		// Look for Quicktag followed by Line Break.
		if ( preg_match( '/' . $this->quicktag . '<br \/>/', $content, $matches ) ) {

			// Derive list.
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude Quicktag.
			$content = implode( '', $content );

		}

		// Look for Quicktag wrapped in Paragraph.
		if ( preg_match( '/<p>' . $this->quicktag . '<\/p>/', $content, $matches ) ) {

			// Derive list.
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude Quicktag.
			$content = implode( '', $content );

		}

		// --<
		return $content;

	}

}
