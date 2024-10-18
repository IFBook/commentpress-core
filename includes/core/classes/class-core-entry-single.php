<?php
/**
 * CommentPress Core Single Entry class.
 *
 * Handles "Single Entry" functionality in CommentPress Core.
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
 * CommentPress Core "Single Entry" Class.
 *
 * This class provides "Single Entry" functionality to CommentPress Core.
 *
 * @since 3.3
 */
class CommentPress_Core_Entry_Single {

	/**
	 * Core loader object.
	 *
	 * @since 3.3
	 * @since 4.0 Renamed.
	 * @access public
	 * @var CommentPress_Core_Loader
	 */
	public $core;

	/**
	 * Entry object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_Core_Entry
	 */
	public $entry;

	/**
	 * Supported Post Types.
	 *
	 * @since 4.0
	 * @access public
	 * @var array
	 */
	public $post_types = [
		'page',
	];

	/**
	 * Relative path to the Metabox directory.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $metabox_path = 'includes/core/assets/templates/wordpress/metaboxes/';

	/**
	 * Relative path to the Parts directory.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $parts_path = 'includes/core/assets/templates/wordpress/parts/';

	/**
	 * Page Title visibility key.
	 *
	 * The key is the "id" and "name" of the form element.
	 * The key is also used to check the POST array for the form element value.
	 * The meta key is the key prefixed with an underscore.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
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
	 * @var string
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
	 * @var string
	 */
	private $key_para_num = 'cp_starting_para_number';

	/**
	 * Prevent "save_post" callback from running more than once.
	 *
	 * True if Post already saved.
	 *
	 * @since 4.0
	 * @access public
	 * @var bool
	 */
	public $saved_post = false;

	/**
	 * Constructor.
	 *
	 * @since 4.0
	 *
	 * @param CommentPress_Core_Entry $entry Reference to the core entry object.
	 */
	public function __construct( $entry ) {

		// Store references.
		$this->entry = $entry;
		$this->core  = $entry->core;

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

		// Separate callbacks into descriptive methods.
		$this->register_hooks_settings();
		$this->register_hooks_entry();

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
		add_filter( 'commentpress/core/settings/site/metaboxes/after', [ $this, 'settings_meta_boxes_append' ], 30 );

		// Save data from Site Settings form submissions.
		add_action( 'commentpress/core/settings/site/save/before', [ $this, 'settings_meta_box_save' ] );

	}

	/**
	 * Registers "Entry" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_entry() {

		// Inject form element at the top of the "CommentPress Settings" metabox on "Edit Entry" screens.
		add_action( 'commentpress/core/entry/metabox/before', [ $this, 'entry_meta_box_part_get' ] );

		// Saves the Sidebar value on "Edit Entry" screens.
		add_action( 'commentpress/core/settings/post/saved', [ $this, 'entry_meta_box_part_save' ] );

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

		// Create "Page Display Settings" metabox.
		add_meta_box(
			'commentpress_page_display',
			__( 'Page Display Settings', 'commentpress-core' ),
			[ $this, 'settings_meta_box_render' ], // Callback.
			$screen_id, // Screen ID.
			'normal', // Column: options are 'normal' and 'side'.
			'core' // Vertical placement: options are 'core', 'high', 'low'.
		);

	}

	/**
	 * Renders the "Commenting Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_render() {

		// Get settings.
		$show_title = $this->setting_show_title_get();
		$show_meta  = $this->setting_show_meta_get();

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-site-entry-single.php';

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
	 * Gets the "Page title visibility" setting.
	 *
	 * @since 4.0
	 *
	 * @return str $show_title The setting if found, default otherwise.
	 */
	public function setting_show_title_get() {

		// Get the setting.
		$show_title = $this->core->db->setting_get( $this->key_show_title );

		// Return setting or default if empty.
		return ! empty( $show_title ) ? $show_title : 'show';

	}

	/**
	 * Sets the "Page title visibility" setting.
	 *
	 * @since 4.0
	 *
	 * @param str $show_title The setting value.
	 */
	public function setting_show_title_set( $show_title ) {

		// Set the setting.
		$this->core->db->setting_set( $this->key_show_title, $show_title );

	}

	/**
	 * Gets the "Page meta visibility" setting.
	 *
	 * @since 4.0
	 *
	 * @return str $show_meta The setting if found, default otherwise.
	 */
	public function setting_show_meta_get() {

		// Get the setting.
		$show_meta = $this->core->db->setting_get( $this->key_show_meta );

		// Return setting or default if empty.
		return ! empty( $show_meta ) ? $show_meta : 'hide';

	}

	/**
	 * Sets the "Page meta visibility" setting.
	 *
	 * @since 4.0
	 *
	 * @param str $show_meta The setting value.
	 */
	public function setting_show_meta_set( $show_meta ) {

		// Set the setting.
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
	public function entry_meta_box_part_get( $post ) {

		// Bail if not one of our supported Post Types.
		if ( ! in_array( $post->post_type, $this->post_types, true ) ) {
			return;
		}

		// We want raw values for the Edit Entry Metabox.
		$raw = true;

		// Get the Entry title visibility.
		$show_title = $this->entry_show_title_get( $post, $raw );

		// Get the Entry meta visibility.
		$show_meta = $this->entry_show_meta_get( $post, $raw );

		// Get the starting Paragraph Number.
		$number = $this->entry_paragraph_start_number_get( $post );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-entry-single-entry.php';

	}

	/**
	 * Saves the meta values for a given Entry.
	 *
	 * @since 4.0
	 *
	 * @param WP_Post $post The WordPress Post object.
	 */
	public function entry_meta_box_part_save( $post ) {

		// Bail if not one of our supported Post Types.
		if ( ! in_array( $post->post_type, $this->post_types, true ) ) {
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

		// Get the "Page Title visibility" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$show_title = isset( $_POST[ $this->key_show_title ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_show_title ] ) ) : '';

		// Save the meta value.
		$this->entry_show_title_set( $show_title, $post );

		// Get the "Page Meta visibility" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$show_meta = isset( $_POST[ $this->key_show_meta ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_show_meta ] ) ) : '';

		// Save the meta value.
		$this->entry_show_meta_set( $show_meta, $post );

		// Get the "starting Paragraph Number" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$para_num = isset( $_POST[ $this->key_para_num ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_para_num ] ) ) : 1;

		// Save starting Paragraph Number.
		$this->entry_paragraph_start_number_set( $para_num, $post );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the "Page title visibility" setting for an Entry.
	 *
	 * @since 4.0
	 *
	 * @param int|WP_Post $post The Post object or ID.
	 * @param bool        $raw Pass "true" to get the actual meta value.
	 * @return str $show_title The setting if found, default otherwise.
	 */
	public function entry_show_title_get( $post, $raw = false ) {

		// If we're not passed a Post object, it should be the Post ID.
		if ( $post instanceof WP_Post ) {
			$post_id = $post->ID;
		} elseif ( is_numeric( $post ) ) {
			$post_id = (int) $post;
		}

		// Build meta key.
		$meta_key = '_' . $this->key_show_title;

		// Get the value from Post meta.
		$show_title = $this->get_for_post_id( $post_id, $meta_key, $this->key_show_title, $raw );

		// --<
		return $show_title;

	}

	/**
	 * Sets the "Page title visibility" setting for an Entry.
	 *
	 * @since 4.0
	 *
	 * @param str         $show_title The setting value.
	 * @param int|WP_Post $post The Post object or ID.
	 */
	public function entry_show_title_set( $show_title, $post ) {

		// If we're not passed a Post object, it should be the Post ID.
		if ( $post instanceof WP_Post ) {
			$post_id = $post->ID;
		} elseif ( is_numeric( $post ) ) {
			$post_id = (int) $post;
		}

		// Build meta key.
		$meta_key = '_' . $this->key_show_title;

		// Save the meta value.
		$this->set_for_post_id( $post_id, $show_title, $meta_key );

	}

	/**
	 * Gets the "Page meta visibility" setting for an Entry.
	 *
	 * @since 4.0
	 *
	 * @param int|WP_Post $post The Post object or ID.
	 * @param bool        $raw Pass "true" to get the actual meta value.
	 * @return str $show_meta The setting if found, default otherwise.
	 */
	public function entry_show_meta_get( $post, $raw = false ) {

		// If we're not passed a Post object, it should be the Post ID.
		if ( $post instanceof WP_Post ) {
			$post_id = $post->ID;
		} elseif ( is_numeric( $post ) ) {
			$post_id = (int) $post;
		}

		// Build meta key.
		$meta_key = '_' . $this->key_show_meta;

		// Get the value from Post meta.
		$show_meta = $this->get_for_post_id( $post_id, $meta_key, $this->key_show_meta, $raw );

		// --<
		return $show_meta;

	}

	/**
	 * Sets the "Page meta visibility" setting for an Entry.
	 *
	 * @since 4.0
	 *
	 * @param str         $show_meta The setting value.
	 * @param int|WP_Post $post The Post object or ID.
	 */
	public function entry_show_meta_set( $show_meta, $post ) {

		// If we're not passed a Post object, it should be the Post ID.
		if ( $post instanceof WP_Post ) {
			$post_id = $post->ID;
		} elseif ( is_numeric( $post ) ) {
			$post_id = (int) $post;
		}

		// Build meta key.
		$meta_key = '_' . $this->key_show_meta;

		// Save the meta value.
		$this->set_for_post_id( $post_id, $show_meta, $meta_key );

	}

	/**
	 * Gets the starting Paragraph Number.
	 *
	 * @since 4.0
	 *
	 * @param int|WP_Post $post The Post object or ID.
	 * @return int $number The starting Paragraph Number.
	 */
	public function entry_paragraph_start_number_get( $post ) {

		// Default to start with Paragraph Number 1.
		$default = 1;

		// If we're not passed a Post object, it should be the Post ID.
		if ( $post instanceof WP_Post ) {
			$post_id = $post->ID;
		} elseif ( is_numeric( $post ) ) {
			$post_id = (int) $post;
		}

		// Build meta key.
		$meta_key = '_' . $this->key_para_num;

		// Get the value from Post meta.
		$number = $this->get_for_post_id( $post_id, $meta_key );
		if ( empty( $number ) || ! is_numeric( $number ) ) {
			$number = $default;
		}

		// --<
		return (int) $number;

	}

	/**
	 * Saves the starting Paragraph Number.
	 *
	 * @since 3.4
	 *
	 * @param int         $number The starting Paragraph Number.
	 * @param int|WP_Post $post The Post object or ID.
	 */
	private function entry_paragraph_start_number_set( $number, $post ) {

		// If not numeric, set to default.
		if ( ! is_numeric( $number ) ) {
			$number = 1;
		}

		// Cast as integer.
		$number = (int) $number;

		// If we're not passed a Post object, it should be the Post ID.
		if ( $post instanceof WP_Post ) {
			$post_id = $post->ID;
		} elseif ( is_numeric( $post ) ) {
			$post_id = (int) $post;
		}

		// Build meta key.
		$meta_key = '_' . $this->key_para_num;

		// Save the meta value.
		$this->set_for_post_id( $post_id, $number, $meta_key );

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
