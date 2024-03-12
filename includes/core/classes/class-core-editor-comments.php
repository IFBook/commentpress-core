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
	 * @var CommentPress_Core_Loader
	 */
	public $core;

	/**
	 * Editor object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_Core_Pages_Editor
	 */
	public $editor;

	/**
	 * Relative path to the Parts directory.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $parts_path = 'includes/core/assets/templates/wordpress/parts/';

	/**
	 * "Comment Editor" settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $key_editor = 'cp_comment_editor';

	/**
	 * "Promote Reading or Commenting" settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $key_promote = 'cp_promote_reading';

	/**
	 * Constructor.
	 *
	 * @since 4.0
	 *
	 * @param object $editor Reference to the core editor object.
	 */
	public function __construct( $editor ) {

		// Store references.
		$this->editor = $editor;
		$this->core   = $editor->core;

		// Init when the editor object is fully loaded.
		add_action( 'commentpress/core/editor/loaded', [ $this, 'initialise' ] );

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

		// Separate callbacks into descriptive methods.
		$this->register_hooks_settings();
		$this->register_hooks_theme();

	}

	/**
	 * Registers "Site Settings" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_settings() {

		// Add our settings to default settings.
		add_filter( 'commentpress/core/settings/defaults', [ $this, 'settings_get_defaults' ] );

		// Inject form element into the "Commenting Settings" metabox on "Site Settings" screen.
		add_action( 'commentpress/core/settings/site/metabox/comment/before', [ $this, 'settings_meta_box_part_get' ] );

		// Save data from Site Settings form submissions.
		add_action( 'commentpress/core/settings/site/save/before', [ $this, 'settings_meta_box_save' ] );

	}

	/**
	 * Registers Theme hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_theme() {

		// Add setting to the Javascript vars.
		add_filter( 'commentpress_get_javascript_vars', [ $this, 'theme_javascript_vars_add' ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Appends our settings to the default core settings.
	 *
	 * @since 4.0
	 *
	 * @param array $settings The existing default core settings.
	 * @return array $settings The modified default core settings.
	 */
	public function settings_get_defaults( $settings ) {

		// Add our defaults.
		$settings[ $this->key_editor ]  = 1; // Default to TinyMCE.
		$settings[ $this->key_promote ] = 0;

		// --<
		return $settings;

	}

	/**
	 * Adds our option to the Site Settings "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_part_get() {

		// Get settings.
		$editor  = $this->setting_editor_get();
		$promote = $this->setting_promote_get();

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-editor-comments-settings.php';

	}

	/**
	 * Saves the data from the Network Settings "BuddyPress Groupblog Settings" metabox.
	 *
	 * Adds the data to the settings array. The settings are actually saved later.
	 *
	 * @see CommentPress_Core_Settings_Site::form_submitted()
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_save() {

		// Get "Comment Editor" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$editor = isset( $_POST[ $this->key_editor ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_editor ] ) ) : '1';

		// Set the setting.
		$this->setting_editor_set( ( $editor ? 1 : 0 ) );

		// Get "Promote Reading or Commenting" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$promote = isset( $_POST[ $this->key_promote ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_promote ] ) ) : '0';

		// Set the setting.
		$this->setting_promote_set( ( $promote ? 1 : 0 ) );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the "Comment Editor" setting.
	 *
	 * @since 4.0
	 *
	 * @return int $editor The setting if found, false otherwise.
	 */
	public function setting_editor_get() {

		// Get the setting.
		$editor = $this->core->db->setting_get( $this->key_editor );

		// Return setting or boolean if empty.
		return ! empty( $editor ) ? $editor : 0;

	}

	/**
	 * Sets the "Comment Editor" setting.
	 *
	 * @since 4.0
	 *
	 * @param int $editor The setting value.
	 */
	public function setting_editor_set( $editor ) {

		// Set the setting.
		$this->core->db->setting_set( $this->key_editor, $editor );

	}

	/**
	 * Gets the "Promote Reading or Commenting" setting.
	 *
	 * @since 4.0
	 *
	 * @return int $promote The setting if found, false otherwise.
	 */
	public function setting_promote_get() {

		// Get the setting.
		$promote = $this->core->db->setting_get( $this->key_promote );

		// Return setting or boolean if empty.
		return ! empty( $promote ) ? $promote : 0;

	}

	/**
	 * Sets the "Promote Reading or Commenting" setting.
	 *
	 * @since 4.0
	 *
	 * @param int $promote The setting value.
	 */
	public function setting_promote_set( $promote ) {

		// Set the setting.
		$this->core->db->setting_set( $this->key_promote, $promote );

	}

	// -------------------------------------------------------------------------

	/**
	 * Filters the Javascript vars.
	 *
	 * @since 4.0
	 *
	 * @param array $vars The default Javascript vars.
	 * @return array $vars The modified Javascript vars.
	 */
	public function theme_javascript_vars_add( $vars ) {

		// Add TinyMCE by default.
		$vars['cp_tinymce'] = $this->setting_editor_get();

		// Don't add TinyMCE if Users must be logged in to comment.
		if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
			$vars['cp_tinymce'] = 0;
		}

		// Don't add TinyMCE if on a public Group Blog and User isn't logged in.
		if ( $this->core->bp->is_groupblog() && ! is_user_logged_in() ) {
			$vars['cp_tinymce'] = 0;
		}

		// Don't add TinyMCE if mobile device.
		$vars['cp_is_mobile'] = 0;
		if ( $this->core->device->is_mobile() ) {
			$vars['cp_is_mobile'] = 1;
			$vars['cp_tinymce']   = 0;
		}

		// Don't add TinyMCE if touch device.
		$vars['cp_is_touch'] = 0;
		if ( $this->core->device->is_touch() ) {
			$vars['cp_is_touch'] = 1;
			$vars['cp_tinymce']  = 0;
		}

		// Don't add TinyMCE if tablet device.
		$vars['cp_is_tablet'] = 0;
		if ( $this->core->device->is_tablet() ) {
			$vars['cp_is_tablet'] = 1;
			$vars['cp_tinymce']   = 0;
		}

		// Support touch device testing if constant is set.
		$vars['cp_touch_testing'] = 0;
		if ( defined( 'COMMENTPRESS_TOUCH_SELECT' ) && COMMENTPRESS_TOUCH_SELECT ) {
			$vars['cp_touch_testing'] = 1;
		}

		/**
		 * Filters the TinyMCE vars.
		 *
		 * Allow plugins to override TinyMCE.
		 *
		 * @since 3.4
		 *
		 * @param bool $cp_tinymce The default TinyMCE vars.
		 */
		$vars['cp_tinymce'] = apply_filters( 'cp_override_tinymce', $vars['cp_tinymce'] );

		// Add TinyMCE behaviour.
		$vars[ $this->key_promote ] = $this->setting_promote_get();

		// --<
		return $vars;

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

		// Basic config.
		$tinymce_config = [
			'theme'     => 'modern',
			'statusbar' => false,
		];

		/**
		 * Filters the TinyMCE 4 config.
		 *
		 * @since 3.4
		 *
		 * @param array $tinymce_config The default TinyMCE 4 config.
		 */
		$tinymce_config = apply_filters( 'commentpress_rte_tinymce', $tinymce_config );

		// No need for editor CSS.
		$editor_css = '';

		$quicktags = [
			'buttons' => 'strong,em,ul,ol,li,link,close',
		];

		/**
		 * Filters the quicktags setting.
		 *
		 * @since 3.4
		 *
		 * @param array $quicktags The default quicktags setting.
		 */
		$quicktags = apply_filters( 'commentpress_rte_quicktags', $quicktags );

		// Our settings.
		$settings = [

			// Configure Comment textarea.
			'media_buttons' => $media_buttons,
			'textarea_name' => 'comment',
			'textarea_rows' => 10,

			// Might as well start with teeny.
			'teeny'         => true,

			// Give the iframe a white background.
			'editor_css'    => $editor_css,

			// Configure TinyMCE.
			'tinymce'       => $tinymce_config,

			// Configure Quicktags.
			'quicktags'     => $quicktags,

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
		$editor = $this->setting_editor_get();

		// Disallow if not "Rich-text Editor".
		if ( empty( $editor ) ) {
			$allowed = false;
		}

		// Disallow for touchscreens - i.e. mobile phones or tablets.
		if ( ! empty( $editor ) && $this->core->device->is_touch() ) {
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
