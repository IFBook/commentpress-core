<?php
/**
 * CommentPress Core Document class.
 *
 * Handles "Document" functionality in CommentPress Core.
 *
 * Historically, CommentPress only supported the built-in "Page" Post Type for
 * creating "Structured Documents". The plan is to support one or more Custom
 * Post Types for building "Structured Documents".
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core "Document" Class.
 *
 * This class provides "Document" functionality to CommentPress Core.
 *
 * @since 3.3
 */
class CommentPress_Core_Document {

	/**
	 * Core loader object.
	 *
	 * @since 3.3
	 * @since 4.0 Renamed.
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * Supported Post Types.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $post_types The array of supported Post Types.
	 */
	public $post_types = [
		'page',
	];

	/**
	 * Metabox template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $metabox_path Relative path to the Metabox directory.
	 */
	private $metabox_path = 'includes/core/assets/templates/wordpress/metaboxes/';

	/**
	 * Parts template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $parts_path Relative path to the Parts directory.
	 */
	private $parts_path = 'includes/core/assets/templates/wordpress/parts/';

	/**
	 * Page Numbering format key.
	 *
	 * The key is the "id" and "name" of the form element.
	 * The key is also used to check the POST array for the form element value.
	 * The meta key is the key prefixed with an underscore.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $key_number_format The Page Numbering format key.
	 */
	private $key_number_format = 'cp_number_format';

	/**
	 * Page Layout key.
	 *
	 * The key is the "id" and "name" of the form element.
	 * The key is also used to check the POST array for the form element value.
	 * The meta key is the key prefixed with an underscore.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $key_layout The Page Layout key.
	 */
	private $key_layout = 'cp_page_layout';

	/**
	 * Prevent "save_post" callback from running more than once.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $saved_post True if Post already saved.
	 */
	public $saved_post = false;

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
	 * Sets up all items associated with this object.
	 *
	 * @since 3.3
	 */
	public function initialise() {

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.3
	 */
	public function register_hooks() {

		// Separate callbacks into descriptive methods.
		$this->register_hooks_entry();
		$this->register_hooks_theme();

		/*
		// TODO: Build "Document" functionality.
		//$this->register_hooks_settings();
		*/

	}

	/**
	 * Registers "Site Settings" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_settings() {

		// Add our settings to default settings.
		add_filter( 'commentpress/core/settings/defaults', [ $this, 'settings_get_defaults' ] );

		// Add our metaboxes to the Site Settings screen.
		add_filter( 'commentpress/core/settings/site/metaboxes/after', [ $this, 'settings_meta_boxes_append' ], 20 );

		// Save data from Site Settings form submissions.
		add_action( 'commentpress/core/settings/site/save/before', [ $this, 'settings_meta_box_save' ] );

	}

	/**
	 * Registers "Entry" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_entry() {

		// Inject form element into the "CommentPress Settings" metabox on "Edit Entry" screens.
		add_action( 'commentpress/core/entry/metabox/after', [ $this, 'entry_meta_box_part_get' ], 10 );

		// Saves the Sidebar value on "Edit Entry" screens.
		add_action( 'commentpress/core/settings/post/saved', [ $this, 'entry_meta_box_part_save' ] );

	}

	/**
	 * Registers "Theme" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_theme() {

		// Add our class(es) to the body classes.
		add_filter( 'commentpress/core/theme/body/classes', [ $this, 'theme_body_classes_filter' ] );

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
		$settings[ $this->key_show_title ] = 'show';
		$settings[ $this->key_show_meta ]  = 'hide';

		// --<
		return $settings;

	}

	/**
	 * Appends our metaboxes to the Site Settings screen.
	 *
	 * @since 4.0
	 *
	 * @param string $screen_id The Site Settings Screen ID.
	 */
	public function settings_meta_boxes_append( $screen_id ) {

		// Create "Structured Document Settings" metabox.
		add_meta_box(
			'commentpress_document',
			__( 'Structured Document Settings', 'commentpress-core' ),
			[ $this, 'settings_meta_box_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

	}

	/**
	 * Renders the "Structured Document Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_render() {

		// Get settings.
		$show_title = $this->setting_show_title_get();
		$show_meta  = $this->setting_show_meta_get();

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-site-document.php';

	}

	/**
	 * Saves the data from the Site Settings "Structured Document Settings" metabox.
	 *
	 * Adds the data to the settings array. The settings are actually saved later.
	 *
	 * @see CommentPress_Core_Settings_Site::form_submitted()
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_save() {

		// Get the "Page Title visibility" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$show_title = isset( $_POST[ $this->key_show_title ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_show_title ] ) ) : '';

		// Save the meta value.
		$this->setting_show_title_set( $show_title );

		// Get the "Page Meta visibility" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$show_meta = isset( $_POST[ $this->key_show_meta ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_show_meta ] ) ) : '';

		// Save the meta value.
		$this->setting_show_meta_set( $show_meta );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our form elements to the "CommentPress Settings" metabox.
	 *
	 * @since 4.0
	 *
	 * @param WP_Post $post The WordPress Post object.
	 */
	public function entry_meta_box_part_get( $post ) {

		// Bail if not one of our supported Post Types.
		if ( ! in_array( $post->post_type, $this->post_types ) ) {
			return;
		}

		// Get the Page Numbering format.
		$format = $this->entry_page_numbering_get( $post );

		// Get the layout for Welcome Page.
		$layout = $this->entry_title_page_layout_get( $post );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-document-entry.php';

	}

	/**
	 * Saves the meta values for a given Entry.
	 *
	 * @since 4.0
	 *
	 * @param object $post The WordPress Post object.
	 */
	public function entry_meta_box_part_save( $post ) {

		// Bail if not one of our supported Post Types.
		if ( ! in_array( $post->post_type, $this->post_types ) ) {
			return;
		}

		// Check edit permissions.
		if ( 'page' === $post->post_type && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// We need to make sure this only runs once.
		if ( false === $this->saved_post ) {
			$this->saved_post = true;
		} else {
			return;
		}

		// Save Page Numbering.
		$this->entry_page_numbering_set( $post );

		// Save layout for Welcome Page.
		$this->entry_title_page_layout_set( $post );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the Page Numbering format.
	 *
	 * This is only shown on the first Page that is not the Welcome Page.
	 *
	 * @since 4.0
	 *
	 * @param object $post The Post object.
	 * @return string $format The Page Numbering format.
	 */
	public function entry_page_numbering_get( $post ) {

		// Default to empty.
		$format = '';

		// If Entry has no parent and it's not a Special Page and it's the first.
		if (
			empty( $post->post_parent ) &&
			! $this->core->pages_legacy->is_special_page() &&
			$post->ID === (int) $this->core->nav->page_get_first()
		) {

			// Default to arabic.
			$default = 'arabic';

			// Build meta key.
			$meta_key = '_' . $this->key_number_format;

			// Get the value from Post meta.
			$format = $this->get_for_post_id( $post->ID, $meta_key );
			if ( empty( $format ) ) {
				$format = $default;
			}

		}

		// --<
		return $format;

	}

	/**
	 * Save Page Numbering format.
	 *
	 * @since 3.4
	 *
	 * Only first top-level Page that is not the Welcome Page is allowed to save this.
	 *
	 * @param object $post The Post object.
	 */
	private function entry_page_numbering_set( $post ) {

		// Bail if no value received.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST[ $this->key_number_format ] ) ) {
			return;
		}

		// Build meta key.
		$meta_key = '_' . $this->key_number_format;

		// Do we need to check this, since only the first top level Page
		// can now send this data? Doesn't hurt to check, I guess.
		if (
			0 === (int) $post->post_parent &&
			! $this->core->pages_legacy->is_special_page() &&
			$post->ID == $this->core->nav->page_get_first()
		) {

			// Get the value.
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$value = sanitize_text_field( wp_unslash( $_POST[ $this->key_number_format ] ) );

			// Save the meta value.
			$this->set_for_post_id( $post->ID, $value, $meta_key );

		}

		/*
		 * Delete this meta value from all other Pages, because we may have altered
		 * the relationship between Pages, thus causing the Page Numbering to fail.
		 */

		// Get all Pages including Chapters.
		$all_pages = $this->core->nav->document_pages_get_all( 'structural' );
		if ( empty( $all_pages ) ) {
			return;
		}

		// Loop through all Pages.
		foreach ( $all_pages as $page ) {

			// Exclude first top-level Page.
			if ( (int) $post->ID === (int) $page->ID ) {
				continue;
			}

			// Delete the meta value.
			delete_post_meta( $page->ID, $meta_key );

		}

	}

	/**
	 * Gets the layout for the Welcome Page.
	 *
	 * @since 4.0
	 *
	 * @param object $post The Post object.
	 * @return string $layout The layout for the Welcome Page.
	 */
	public function entry_title_page_layout_get( $post ) {

		// TODO: Move to "page_legacy" class.

		// Default to empty.
		$layout = '';

		// Is this the Welcome Page?
		if ( $post->ID !== (int) $this->core->db->setting_get( 'cp_welcome_page' ) ) {
			return $layout;
		}

		// Default to text.
		$default = 'text';

		// Build meta key.
		$meta_key = '_' . $this->key_layout;

		// Get the value from Post meta.
		$layout = $this->get_for_post_id( $post->ID, $meta_key );
		if ( empty( $layout ) ) {
			$layout = $default;
		}

		// --<
		return $layout;

	}

	/**
	 * Saves the layout for the Welcome Page.
	 *
	 * Note: This allows for the legacy "Book Cover image".
	 *
	 * @since 3.0
	 *
	 * @param object $post The Post object.
	 */
	private function entry_title_page_layout_set( $post ) {

		// TODO: Move to "page_legacy" class.

		// Bail if this is not the Welcome Page.
		if ( $post->ID !== (int) $this->core->db->setting_get( 'cp_welcome_page' ) ) {
			return;
		}

		// Find and save the value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$value = isset( $_POST[ $this->key_layout ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_layout ] ) ) : 'text';

		// Build meta key.
		$meta_key = '_' . $this->key_layout;

		// Save the meta value.
		$this->set_for_post_id( $post->ID, $value, $meta_key );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds "Text Format" class to the body classes array.
	 *
	 * @since 4.0
	 *
	 * @param array $classes The existing body classes array.
	 * @return array $classes The modified body classes array.
	 */
	public function theme_body_classes_filter( $classes ) {

		// Access Post.
		global $post;

		// Bail if no Post object.
		if ( ! ( $post instanceof WP_Post ) ) {
			return $classes;
		}

		// Check for "wide" layout.
		$layout = $this->entry_title_page_layout_get( $post );
		if ( 'wide' !== $layout ) {
			return $classes;
		}

		// Add class to array.
		$classes[] = 'full_width';

		// --<
		return $classes;

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the meta value for a given Post ID.
	 *
	 * @since 4.0
	 *
	 * @param int    $post_id The numeric ID of the Post.
	 * @param string $meta_key The name of the meta key.
	 * @param string $option The name of the site setting. Optional.
	 * @param bool   $raw Pass "true" to get the actual meta value.
	 * @return mixed $value The meta value.
	 */
	public function get_for_post_id( $post_id, $meta_key, $option = '', $raw = false ) {

		// Check Post for override.
		$override = get_post_meta( $post_id, $meta_key, true );

		// Return raw value if requested.
		if ( true === $raw ) {
			return $override;
		}

		// Default to site setting when name is passed.
		if ( ! empty( $option ) ) {
			$setting = $this->core->db->setting_get( $option );
		}

		// Bail if we didn't get one.
		if ( ! empty( $option ) && empty( $override ) ) {
			return $setting;
		}

		// Override if different to the default.
		if ( empty( $option ) || (string) $override !== (string) $setting ) {
			$setting = $override;
		}

		// --<
		return $setting;

	}

	/**
	 * Sets the meta value for a given Post ID.
	 *
	 * @since 4.0
	 *
	 * @param int    $post_id The numeric ID of the Post.
	 * @param mixed  $value The meta value.
	 * @param string $meta_key The name of the meta key.
	 */
	public function set_for_post_id( $post_id, $value, $meta_key ) {

		// Delete the meta entry by passing an empty string.
		if ( is_string( $value ) && '' === $value ) {
			$this->delete_for_post_id( $post_id, $meta_key );
			return;
		}

		// Update the Post meta.
		update_post_meta( $post_id, $meta_key, $value );

	}

	/**
	 * Deletes the meta value for a given Post ID.
	 *
	 * @since 4.0
	 *
	 * @param int    $post_id The numeric ID of the Post.
	 * @param string $meta_key The name of the meta key.
	 */
	public function delete_for_post_id( $post_id, $meta_key ) {

		// Delete the meta value.
		delete_post_meta( $post_id, $meta_key );

	}

}
