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
class CommentPress_Core_Entry_Document {

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
	 * Entry object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $entry The Entry object.
	 */
	public $entry;

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
	 * Page Title visibility key.
	 *
	 * The key is the "id" and "name" of the form element.
	 * The key is also used to check the POST array for the form element value.
	 * The meta key is the key prefixed with an underscore.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $key_show_title The Page Title visibility key.
	 */
	private $key_show_title = 'cp_title_visibility';

	/**
	 * Page Meta visibility key.
	 *
	 * The key is the "id" and "name" of the form element.
	 * The key is also used to check the POST array for the form element value.
	 * The meta key is the key prefixed with an underscore.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $key_show_meta The Page Meta visibility key.
	 */
	private $key_show_meta = 'cp_page_meta_visibility';

	/**
	 * Paragraph Number key.
	 *
	 * The key is the "id" and "name" of the form element.
	 * The key is also used to check the POST array for the form element value.
	 * The meta key is the key prefixed with an underscore.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $key_para_num The Paragraph Number key.
	 */
	private $key_para_num = 'cp_starting_para_number';

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
	 * Parts template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $parts_path Relative path to the Parts directory.
	 */
	private $parts_path = 'includes/core/assets/templates/wordpress/parts/';

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
	 * @param object $entry Reference to the core entry object.
	 */
	public function __construct( $entry ) {

		// Store references.
		$this->entry = $entry;
		$this->core = $entry->core;

		// Init when the entry object is fully loaded.
		add_action( 'commentpress/core/entry/loaded', [ $this, 'initialise' ] );

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

		// Inject form element into the "CommentPress Settings" metabox on "Edit Entry" screens.
		add_action( 'commentpress/core/entry/metabox/after', [ $this, 'metabox_post_get' ] );

		// Saves the Sidebar value on "Edit Entry" screens.
		add_action( 'commentpress/core/settings/post/saved', [ $this, 'save_for_post' ] );

		// Add our option to the Site Settings "Page Settings" metabox.
		add_action( 'commentpress/core/settings/site/metabox/page/after', [ $this, 'metabox_settings_get' ] );

		// Save data from "Site Settings" screen.
		add_action( 'commentpress/core/settings/site/save/before', [ $this, 'save_for_settings' ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our option to the Site Settings "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function metabox_settings_get() {

		// Get settings.
		$title_visibility = $this->core->db->setting_get( 'cp_title_visibility' );
		$page_meta_visibility = $this->core->db->setting_get( 'cp_page_meta_visibility' );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-entry-document-settings.php';

	}

	/**
	 * Saves the data from "Site Settings" screen.
	 *
	 * Adds the data to the settings array. The settings are actually saved later.
	 *
	 * @see CommentPress_Core_Settings_Site::form_submitted()
	 *
	 * @since 4.0
	 */
	public function save_for_settings() {

		// Get the Entry title visibility value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$show_title = isset( $_POST[ $this->key_show_title ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_show_title ] ) ) : '';

		// Set the Page Meta visibility value.
		$this->core->db->setting_set( $this->key_show_title, $show_title );

		// Get the Page Meta visibility value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$show_meta = isset( $_POST[ $this->key_show_meta ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_show_meta ] ) ) : '';

		// Set the Page Meta visibility value.
		$this->core->db->setting_set( $this->key_show_meta, $show_meta );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our form elements to the "CommentPress Settings" metabox.
	 *
	 * @since 4.0
	 *
	 * @param WP_Post $post The WordPress Post object.
	 */
	public function metabox_post_get( $post ) {

		// Bail if not one of our supported Post Types.
		if ( ! in_array( $post->post_type, $this->post_types ) ) {
			return;
		}

		// Get the Entry title visibility.
		$title_visibility = $this->title_visibility_get( $post );

		// Get the Entry meta visibility.
		$meta_visibility = $this->meta_visibility_get( $post );

		// Get the Page Numbering format.
		$format = $this->page_numbering_get( $post );

		// Get the layout for Welcome Page.
		$layout = $this->title_page_layout_get( $post );

		// Get the starting Paragraph Number.
		$number = $this->paragraph_start_number_get( $post );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-entry-document-entry.php';

	}

	/**
	 * Saves the meta values for a given Entry.
	 *
	 * @since 4.0
	 *
	 * @param object $post The WordPress Post object.
	 */
	public function save_for_post( $post ) {

		// Bail if not one of our supported Post Types.
		if ( ! in_array( $post->post_type, $this->post_types ) ) {
			return;
		}

		// Check edit permissions.
		if ( $post->post_type === 'page' && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// We need to make sure this only runs once.
		if ( $this->saved_post === false ) {
			$this->saved_post = true;
		} else {
			return;
		}

		// Save Entry title visibility.
		$this->title_visibility_save( $post );

		// Save Entry meta visibility.
		$this->meta_visibility_save( $post );

		// Save Page Numbering.
		$this->page_numbering_save( $post );

		// Save layout for Welcome Page.
		$this->title_page_layout_save( $post );

		// Save starting Paragraph Number.
		$this->paragraph_start_number_save( $post );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the Entry title visibility.
	 *
	 * @since 4.0
	 *
	 * @param object $post The Post object.
	 * @return string $value The Entry title visibility.
	 */
	public function title_visibility_get( $post ) {

		// Build meta key.
		$meta_key = '_' . $this->key_show_title;

		// Get the value from Post meta.
		$value = $this->get_for_post_id( $post->ID, $meta_key, $this->key_show_title, $raw = true );

		// --<
		return $value;

	}

	/**
	 * Saves the Entry title visibility.
	 *
	 * @since 3.4
	 *
	 * @param object $post The Post object.
	 */
	private function title_visibility_save( $post ) {

		// Find and save the data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$value = isset( $_POST[ $this->key_show_title ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_show_title ] ) ) : '';

		// Build meta key.
		$meta_key = '_' . $this->key_show_title;

		// Save the meta value.
		$this->set_for_post_id( $post->ID, $value, $meta_key );

	}

	/**
	 * Gets the Entry title visibility.
	 *
	 * @since 4.0
	 *
	 * @param object $post The Post object.
	 * @return string $value The Entry title visibility.
	 */
	public function meta_visibility_get( $post ) {

		// Build meta key.
		$meta_key = '_' . $this->key_show_meta;

		// Get the value from Post meta.
		$value = $this->get_for_post_id( $post->ID, $meta_key, $this->key_show_meta, $raw = true );

		// --<
		return $value;

	}

	/**
	 * Save Page Meta visibility.
	 *
	 * @since 3.4
	 *
	 * @param object $post The Post object.
	 */
	private function meta_visibility_save( $post ) {

		// Find and save the data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$value = isset( $_POST[ $this->key_show_meta ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_show_meta ] ) ) : '';

		// Build meta key.
		$meta_key = '_' . $this->key_show_meta;

		// Save the meta value.
		$this->set_for_post_id( $post->ID, $value, $meta_key );

	}

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
	public function page_numbering_get( $post ) {

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
	private function page_numbering_save( $post ) {

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
			$post->post_parent == '0' &&
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
	public function title_page_layout_get( $post ) {

		// Default to empty.
		$layout = '';

		// Is this the Welcome Page?
		if ( $post->ID == $this->core->db->setting_get( 'cp_welcome_page' ) ) {

			// Default to text.
			$default = 'text';

			// Build meta key.
			$meta_key = '_' . $this->key_layout;

			// Get the value from Post meta.
			$layout = $this->get_for_post_id( $post->ID, $meta_key );
			if ( empty( $layout ) ) {
				$layout = $default;
			}

		}

		// --<
		return $layout;

	}

	/**
	 * Saves the layout for the Welcome Page.
	 *
	 * Note: This allows for the legacy "Book Cover image".
	 *
	 * TODO: Move to "page_legacy" class.
	 *
	 * @since 3.0
	 *
	 * @param object $post The Post object.
	 */
	private function title_page_layout_save( $post ) {

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

	/**
	 * Gets the starting Paragraph Number.
	 *
	 * @since 4.0
	 *
	 * @param object $post The Post object.
	 * @return int $number The starting Paragraph Number.
	 */
	public function paragraph_start_number_get( $post ) {

		// Default to start with Paragraph Number 1.
		$default = 1;

		// Build meta key.
		$meta_key = '_' . $this->key_para_num;

		// Get the value from Post meta.
		$number = $this->get_for_post_id( $post->ID, $meta_key );
		if ( ! is_numeric( $number ) ) {
			$number = $default;
		}

		// --<
		return $number;

	}

	/**
	 * Saves the starting Paragraph Number.
	 *
	 * @since 3.4
	 *
	 * @param object $post The Post object.
	 */
	private function paragraph_start_number_save( $post ) {

		// Get the data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$data = isset( $_POST[ $this->key_para_num ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_para_num ] ) ) : 1;

		// If not numeric, set to default.
		if ( ! is_numeric( $data ) ) {
			$data = 1;
		}

		// Cast as integer.
		$value = (int) $data;

		// Build meta key.
		$meta_key = '_' . $this->key_para_num;

		// Save the meta value.
		$this->set_for_post_id( $post->ID, $value, $meta_key );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the meta value for a given Post ID.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @param string $meta_key The name of the meta key.
	 * @param string $option The name of the site setting. Optional.
	 * @param bool $raw Pass "true" to get the actual meta value.
	 * @return mixed $value The meta value.
	 */
	public function get_for_post_id( $post_id, $meta_key, $option = '', $raw = false ) {

		// Check Post for override.
		$override = get_post_meta( $post_id, $meta_key, true );

		// Return raw value if requested.
		if ( $raw === true ) {
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
	 * @param int $post_id The numeric ID of the Post.
	 * @param mixed $value The meta value.
	 * @param string $meta_key The name of the meta key.
	 */
	public function set_for_post_id( $post_id, $value, $meta_key ) {

		// Delete the meta entry by passing an empty string.
		if ( is_string( $value ) && $value === '' ) {
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
	 * @param int $post_id The numeric ID of the Post.
	 * @param string $meta_key The name of the meta key.
	 */
	public function delete_for_post_id( $post_id, $meta_key ) {

		// Delete the meta value.
		delete_post_meta( $post_id, $meta_key );

	}

	/**
	 * Checks if the Sidebar of a Post is different to the default.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @return bool $overridden True if overridden, false otherwise.
	 */
	public function is_overridden( $post_id ) {

		// Get the current Sidebar.
		$sidebar_blog = $this->core->db->setting_get( $this->option_sidebar );

		// Get the Sidebar for this Post.
		$sidebar_post = $this->get_for_post_id( $post_id );

		// Do override check.
		if ( (string) $sidebar_blog !== (string) $sidebar_post ) {
			return true;
		}

		// Not overridden.
		return false;

	}

}
