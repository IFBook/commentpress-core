<?php
/**
 * CommentPress Core Document class.
 *
 * Handles "Document" functionality in CommentPress Core.
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
	 * Partials template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $metabox_path Relative path to the Partials directory.
	 */
	private $partials_path = 'includes/core/assets/templates/wordpress/partials/';

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
		add_action( 'commentpress/core/settings/site/saved', [ $this, 'save_for_settings' ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our option to the Site Settings "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function metabox_settings_get() {

		// Get settings.
		$title_visibility = $this->core->db->option_get( 'cp_title_visibility' );
		$page_meta_visibility = $this->core->db->option_get( 'cp_page_meta_visibility' );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->partials_path . 'partial-entry-document-settings.php';

	}

	/**
	 * Saves the data from "Site Settings" screen.
	 *
	 * @since 4.0
	 */
	public function save_for_settings() {

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our form element to the "CommentPress Settings" metabox.
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

		// ---------------------------------------------------------------------
		// Show or Hide Page Title.
		// ---------------------------------------------------------------------

		// Build meta key.
		$meta_key = '_' . $this->key_show_title;

		// Get the value from Post meta.
		$title_visibility = $this->get_for_post_id( $post->ID, $meta_key, $this->key_show_title );

		// ---------------------------------------------------------------------
		// Show or Hide Page Meta.
		// ---------------------------------------------------------------------

		// Build meta key.
		$meta_key = '_' . $this->key_show_meta;

		// Get the value from Post meta.
		$meta_visibility = $this->get_for_post_id( $post->ID, $meta_key, $this->key_show_meta );

		// ---------------------------------------------------------------------
		// Page Numbering - only shown on first top level Page.
		// ---------------------------------------------------------------------

		// Default to empty.
		$format = '';

		// If Page has no parent and it's not a Special Page and it's the first.
		if (
			$post->post_parent == '0' &&
			! $this->core->pages_legacy->is_special_page() &&
			$post->ID == $this->core->nav->get_first_page()
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

		// ---------------------------------------------------------------------
		// Page Layout for Title Page to allow for Book Cover image.
		// ---------------------------------------------------------------------

		// Default to empty.
		$layout = '';

		// Is this the Title Page?
		if ( $post->ID == $this->core->db->option_get( 'cp_welcome_page' ) ) {

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

		// ---------------------------------------------------------------------
		// Paragraph numbering.
		// ---------------------------------------------------------------------

		// Default to start with para 1.
		$default = 1;

		// Build meta key.
		$meta_key = '_' . $this->key_para_num;

		// Get the value from Post meta.
		$num = $this->get_for_post_id( $post->ID, $meta_key );
		if ( ! is_numeric( $num ) ) {
			$num = $default;
		}

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->partials_path . 'partial-entry-document-entry.php';

	}

	/**
	 * Saves the Document for a given Entry.
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

		// Save Page title visibility.
		$this->page_save_title_visibility( $post );

		// Save Page meta visibility.
		$this->page_save_meta_visibility( $post );

		// Save Page numbering.
		$this->page_save_numbering( $post );

		// Save Page layout for Title Page.
		$this->page_save_layout( $post );

		// Save starting Paragraph Number.
		$this->page_save_starting_paragraph( $post );

	}

	// -------------------------------------------------------------------------

	/**
	 * Save Page Title visibility.
	 *
	 * @since 3.4
	 *
	 * @param object $post The Post object.
	 * @return string $data Either 'show' (default) or ''.
	 */
	public function page_save_title_visibility( $post ) {

		// Find and save the data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$value = isset( $_POST[ $this->key_show_title ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_show_title ] ) ) : 'show';

		// Build meta key.
		$meta_key = '_' . $this->key_show_title;

		// Save the meta value.
		$this->set_for_post_id( $post->ID, $value, $meta_key );


	}

	/**
	 * Save Page Meta visibility.
	 *
	 * @since 3.4
	 *
	 * @param object $post The Post object.
	 * @return string $data Either 'hide' (default) or ''.
	 */
	public function page_save_meta_visibility( $post ) {

		// Find and save the data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$value = isset( $_POST[ $this->key_show_meta ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_show_meta ] ) ) : 'hide';

		// Build meta key.
		$meta_key = '_' . $this->key_show_meta;

		// Save the meta value.
		$this->set_for_post_id( $post->ID, $value, $meta_key );

	}

	/**
	 * Save Page Numbering format.
	 *
	 * @since 3.4
	 *
	 * Only first top-level Page is allowed to save this.
	 *
	 * @param object $post The Post object.
	 */
	public function page_save_numbering( $post ) {

		// Bail if no value received.
		if ( ! isset( $_POST[ $this->key_number_format ] ) ) {
			return;
		}

		// Build meta key.
		$meta_key = '_' . $this->key_number_format;

		// Do we need to check this, since only the first top level Page
		// can now send this data? doesn't hurt to validate, I guess.
		if (
			$post->post_parent == '0' &&
			! $this->core->pages_legacy->is_special_page() &&
			$post->ID == $this->core->nav->get_first_page()
		) {

			// Get the value.
			$value = sanitize_text_field( wp_unslash( $_POST[ $this->key_number_format ] ) );

			// Save the meta value.
			$this->set_for_post_id( $post->ID, $value, $meta_key );

		}

		/*
		 * Delete this meta value from all other Pages, because we may have altered
		 * the relationship between Pages, thus causing the Page numbering to fail.
		 */

		// Get all Pages including Chapters.
		$all_pages = $this->core->nav->get_book_pages( 'structural' );
		if ( empty( $all_pages  ) ) {
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
	 * Save Page Layout for Title Page.
	 *
	 * Note: This allows for the legacy "Book Cover image".
	 *
	 * TODO: Move to "page_legacy" class.
	 *
	 * @since 3.0
	 *
	 * @param object $post The Post object.
	 */
	public function page_save_layout( $post ) {

		// Bail if this is not the Title Page.
		if ( $post->ID !== (int) $this->core->db->option_get( 'cp_welcome_page' ) ) {
			return;
		}

		// Find and save the value.
		$value = isset( $_POST[ $this->key_layout ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_layout ] ) ) : 'text';

		// Build meta key.
		$meta_key = '_' . $this->key_layout;

		// Save the meta value.
		$this->set_for_post_id( $post->ID, $value, $meta_key );

	}

	/**
	 * Starting Paragraph Number - meta only exists when not default value.
	 *
	 * @since 3.4
	 *
	 * @param object $post The Post object.
	 */
	public function page_save_starting_paragraph( $post ) {

		// Get the data.
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
	 * @return mixed $value The meta value.
	 */
	public function get_for_post_id( $post_id, $meta_key, $option = '' ) {

		// Default to site setting when name is passed.
		if ( ! empty( $option ) ) {
			$setting = $this->core->db->option_get( $option );
		}

		// Check Post for override.
		$override = get_post_meta( $post_id, $meta_key, true );

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
		$sidebar_blog = $this->core->db->option_get( $this->option_sidebar );

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
