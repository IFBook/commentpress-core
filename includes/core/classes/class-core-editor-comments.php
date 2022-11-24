<?php
/**
 * CommentPress Core Comment Editor class.
 *
 * Handles functionality for the TinyMCE and Quicktags Comment Editor.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Comment Editor Class.
 *
 * This class provides functionality for the TinyMCE and Quicktags Comment Editor.
 *
 * @since 4.0
 */
class CommentPress_Core_Editor_Comments {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

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

	}

	// -------------------------------------------------------------------------

	/**
	 * Renders the WordPress Editor.
	 *
	 * @since 4.0
	 *
	 * @return bool True if WordPress Editor is rendered, false otherwise.
	 */
	public function editor_render() {

		// Bail if TinyMCE is not allowed.
		if ( ! $this->is_tinymce_allowed() ) {
			return false;
		}

		// Basic buttons.
		$basic_buttons = [
			'bold',
			'italic',
			'underline',
			'|',
			'bullist',
			'numlist',
			'|',
			'link',
			'unlink',
			'|',
			'removeformat',
			'fullscreen',
		];

		/**
		 * Add our buttons but allow filtering.
		 *
		 * @since 3.5
		 *
		 * @param array $basic_buttons The default buttons.
		 */
		$mce_buttons = apply_filters( 'cp_tinymce_buttons', $basic_buttons );

		/**
		 * Allow media buttons setting to be overridden.
		 *
		 * @since 3.5
		 *
		 * @param bool True by default - buttons are allowed.
		 */
		$media_buttons = apply_filters( 'commentpress_rte_media_buttons', true );

		/**
		 * Filters the TinyMCE 4 config.
		 *
		 * @since 3.4
		 *
		 * @param array The default TinyMCE 4 config.
		 */
		$tinymce_config = apply_filters( 'commentpress_rte_tinymce', [
			'theme' => 'modern',
			'statusbar' => false,
		] );

		// No need for editor CSS.
		$editor_css = '';

		/**
		 * Filters the quicktags setting.
		 *
		 * @since 3.4
		 *
		 * @param array The default quicktags setting.
		 */
		$quicktags = apply_filters( 'commentpress_rte_quicktags', [
			'buttons' => 'strong,em,ul,ol,li,link,close',
		] );

		// Our settings.
		$settings = [

			// Configure Comment textarea.
			'media_buttons' => $media_buttons,
			'textarea_name' => 'comment',
			'textarea_rows' => 10,

			// Might as well start with teeny.
			'teeny' => true,

			// Give the iframe a white background.
			'editor_css' => $editor_css,

			// Configure TinyMCE.
			'tinymce' => $tinymce_config,

			// Configure Quicktags.
			'quicktags' => $quicktags,

		];

		// Create the editor.
		wp_editor(
			'', // Initial content.
			'comment', // ID of Comment textarea.
			$settings
		);

		// Access WordPress version.
		global $wp_version;

		// Add styles.
		wp_enqueue_style(
			'commentpress-editor-css',
			wp_admin_css_uri( 'css/edit' ),
			[ 'dashicons', 'open-sans' ],
			$wp_version, // Version.
			'all' // Media.
		);

		// Don't show standard textarea.
		return true;

	}

	/**
	 * Test if TinyMCE is allowed.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @return bool $allowed True if TinyMCE is allowed, false otherwise.
	 */
	public function is_tinymce_allowed() {

		// Default to allowed.
		$allowed = true;

		// Get "Comment form editor" option.
		$comment_editor = $this->core->db->option_get( 'cp_comment_editor' );

		// Disallow if not "Rich-text Editor".
		if ( $comment_editor != '1' ) {
			$allowed = false;
		}

		// Disallow for touchscreens - i.e. mobile phones or tablets.
		if ( $comment_editor == '1' && $this->core->device->is_touch() ) {
			$allowed = false;
		}

		/**
		 * Filters if TinyMCE is allowed.
		 *
		 * @since 3.4
		 *
		 * @param bool $allowed True if TinyMCE is allowed, false otherwise.
		 */
		return apply_filters( 'commentpress_is_tinymce_allowed', $allowed );

	}

}
