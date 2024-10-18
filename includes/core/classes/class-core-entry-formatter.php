<?php
/**
 * CommentPress Core Formatter class.
 *
 * Handles "Prose" and "Poetry" formatting in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Formatter Class.
 *
 * This class provides "Prose" and "Poetry" formatting to CommentPress Core.
 *
 * @since 3.3
 */
class CommentPress_Core_Entry_Formatter {

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
		'post',
		'page',
	];

	/**
	 * "Text Format" setting in Site Settings.
	 *
	 * Text Formats are built as an array - e.g.
	 *
	 * array(
	 *   0 => 'Poetry',
	 *   1 => 'Prose',
	 * )
	 *
	 * The stored value is the array key for the corresponding Text Format.
	 *
	 * @since 4.0
	 * @access public
	 * @var string
	 */
	public $key_formatter = 'cp_blog_type';

	/**
	 * The "Text Format" Post meta key.
	 *
	 * The stored value is the array key for the corresponding Text Format as
	 * detailed above.
	 *
	 * @since 4.0
	 * @access public
	 * @var string
	 */
	public $key_post_meta = '_cp_post_type_override';

	/**
	 * Select element name.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $element_select = 'cp_formatter_value';

	/**
	 * Relative path to the Parts directory.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $parts_path = 'includes/core/assets/templates/wordpress/parts/';

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
	 * @param object $entry Reference to the core entry object.
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
		$this->register_hooks_theme();
		$this->register_hooks_content();

		// TODO: Move Site Text Format save handling to this class.

		// TODO: Untangle the following.

		// Set Site Text Format options.
		add_filter( 'cp_blog_type_options', [ $this, 'site_text_format_options' ], 21 );

		// Set Site Text Format options label.
		add_filter( 'cp_blog_type_label', [ $this, 'site_text_format_label' ], 21 );

	}

	/**
	 * Registers "Site Settings" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_settings() {

		// Add our settings to default settings.
		add_filter( 'commentpress/core/settings/defaults', [ $this, 'settings_get_defaults' ] );

		// Add our option to the Site Settings "General Settings" metabox.
		add_action( 'commentpress/core/settings/site/metabox/general/after', [ $this, 'settings_meta_box_part_get' ], 20 );

		// Save data from "Site Settings" screen.
		add_action( 'commentpress/core/settings/site/save/before', [ $this, 'settings_meta_box_part_save' ] );

	}

	/**
	 * Registers "Entry" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_entry() {

		// Inject form element into the "CommentPress Settings" metabox on "Edit Entry" screens.
		add_action( 'commentpress/core/entry/metabox/after', [ $this, 'entry_meta_box_part_get' ], 20 );

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

	/**
	 * Registers "Content" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_content() {

		// Save default Post Formatter on Special Pages.
		add_action( 'commentpress/core/db/page/special/title/created', [ $this, 'default_set_for_post' ] );

		// Save Post Formatter on new Revisions.
		add_action( 'commentpress/core/revisions/revision/meta/added', [ $this, 'revision_formatter_set' ] );

		// Add filter for Content Parser.
		add_filter( 'commentpress/core/parser/content/parser', [ $this, 'content_parser' ], 21, 1 );

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
		$settings[ $this->key_formatter ] = 0;

		// --<
		return $settings;

	}

	/**
	 * Adds our option to the Site Settings "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_part_get() {

		// Get the "Text Format" options array.
		$text_formats = $this->formats_array_get();
		if ( empty( $text_formats ) ) {
			return;
		}

		/**
		 * Filters the Site Text Format label.
		 *
		 * @since 3.3.1
		 *
		 * @param str The the Site Text Format label.
		 */
		$text_format_title = apply_filters( 'cp_blog_type_label', __( 'Text Format', 'commentpress-core' ) );

		// Get existing.
		$site_text_format = $this->setting_formatter_get();

		// Get the "Text Format" options markup without showing default.
		$show_default        = false;
		$text_format_options = $this->formats_select_options_get( $text_formats, $site_text_format, $show_default );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-entry-formatter-settings.php';

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
	public function settings_meta_box_part_save() {

		// Get the value of the metabox select element.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$formatter = isset( $_POST[ $this->key_formatter ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_formatter ] ) ) : '';

		// Set default sidebar.
		$this->setting_formatter_set( $formatter );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the "Text Format" setting.
	 *
	 * @since 4.0
	 *
	 * @return int $formatter The setting if found, default otherwise.
	 */
	public function setting_formatter_get() {

		// Get the setting.
		$formatter = $this->core->db->setting_get( $this->key_formatter );

		// Return setting or default if empty.
		return ! empty( $formatter ) ? $formatter : 0;

	}

	/**
	 * Sets the "Text Format" setting.
	 *
	 * @since 4.0
	 *
	 * @param int $formatter The setting value.
	 */
	public function setting_formatter_set( $formatter ) {

		// Set the setting.
		$this->core->db->setting_set( $this->key_formatter, $formatter );

		/**
		 * Fires when the Text Format has been set for a Site.
		 *
		 * @since 4.0
		 *
		 * @param str $formatter The "Text Format" setting.
		 */
		do_action( 'commentpress/core/formatter/setting/set', $formatter );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our form element to the "CommentPress Settings" metabox.
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

		// Get the "Text Format" options array.
		$text_formats = $this->formats_array_get();
		if ( empty( $text_formats ) ) {
			return;
		}

		// We want raw values for the Edit Entry Metabox.
		$raw = true;

		// Default to current Site Text Format.
		$site_text_format = $this->get_for_post_id( $post->ID, $raw );

		// Get the "Text Format" options markup.
		$text_format_options = $this->formats_select_options_get( $text_formats, $site_text_format );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-entry-formatter-entry.php';

	}

	/**
	 * Saves the Formatter for a given Entry.
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
		if ( 'post' === $post->post_type && ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}
		if ( 'page' === $post->post_type && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// We need to make sure this only runs once.
		if ( false === $this->saved_post ) {
			$this->saved_post = true;
		} else {
			return;
		}

		// Get the value of the metabox select element.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$formatter = isset( $_POST[ $this->element_select ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->element_select ] ) ) : '';

		// Save the Formatter for the Post.
		$this->set_for_post_id( $post->ID, $formatter );

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

		// Bail if on the Main Site of a Multisite install.
		if ( is_multisite() && is_main_site() ) {
			return $classes;
		}

		// Bail if there's no Site Text Format.
		$text_format = $this->setting_formatter_get();
		if ( '' === $text_format || false === $text_format ) {
			return $classes;
		}

		// Add class to array.
		$classes[] = 'blogtype-' . (int) $text_format;

		// --<
		return $classes;

	}

	// -------------------------------------------------------------------------

	/**
	 * Applies the default Formatter to a Post.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 */
	public function default_set_for_post( $post_id ) {

		// Add default Formatter to Post - "Prose" is "0".
		$this->set_for_post_id( $post_id, '0' );

	}

	/**
	 * Adds the Formatter meta data to the new Revision.
	 *
	 * @since 4.0
	 *
	 * @param int     $new_post_id The numeric ID of the new Post.
	 * @param WP_Post $post The WordPress Post object that has been copied.
	 */
	public function revision_formatter_set( $new_post_id, $post ) {

		// Try and get the Formatter in the current Post.
		$formatter = $this->get_for_post_id( $post->ID );
		if ( false === $formatter || '' === $formatter || ! is_numeric( $formatter ) ) {
			return;
		}

		// Add Formatter to new Post.
		$this->set_for_post_id( $new_post_id, $formatter );

	}

	// -------------------------------------------------------------------------

	/**
	 * Override the name of the Site Text Formats dropdown label.
	 *
	 * @since 3.3
	 *
	 * @param str $name The existing name of the label.
	 * @return str $name The modified name of the label.
	 */
	public function site_text_format_label( $name ) {
		return __( 'Text Format', 'commentpress-core' );
	}

	/**
	 * Defines the "Text Formats" for CommentPress Sites.
	 *
	 * @since 3.3
	 *
	 * @param array $text_formats The existing array of Site Text Formats.
	 * @return array $text_formats The modified array of Site Text Formats.
	 */
	public function site_text_format_options( $text_formats ) {

		// Define Text Formats.
		$text_formats = [
			__( 'Prose', 'commentpress-core' ), // Types[0].
			__( 'Poetry', 'commentpress-core' ), // Types[1].
		];

		/**
		 * Filters the Site Text Formats.
		 *
		 * @since 3.3.1
		 *
		 * @param array $text_formats The array of Site Text Formats.
		 */
		return apply_filters( 'cp_class_commentpress_formatter_types', $text_formats );

	}

	// -------------------------------------------------------------------------

	/**
	 * Chooses the Content Parser by Site Text Format or Post meta value.
	 *
	 * @since 3.3
	 *
	 * @param str $parser The existing Content Parser code.
	 * @return str $parser The existing Content Parser code.
	 */
	public function content_parser( $parser ) {

		// Access globals.
		global $post;

		// Try and get the Formatter in the current Post.
		$formatter = $this->get_for_post_id( $post->ID );
		if ( false === $formatter || '' === $formatter || ! is_numeric( $formatter ) ) {
			return $parser;
		}

		// Make the decision.
		switch ( $formatter ) {

			// Prose.
			case '0':
				$parser = 'tag';
				break;

			// Poetry.
			case '1':
				$parser = 'line';
				break;

		}

		// --<
		return $parser;

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the "Text Format" options array.
	 *
	 * @since 4.0
	 *
	 * @return array $text_formats The array of Text Formats.
	 */
	public function formats_array_get() {

		// Start with empty array.
		$text_formats = [];

		/**
		 * Build Text Format options.
		 *
		 * @since 3.3.1
		 *
		 * @param array $text_formats Empty by default since others add them.
		 */
		$text_formats = apply_filters( 'cp_blog_type_options', $text_formats );

		// --<
		return $text_formats;

	}

	/**
	 * Gets the "Text Format" options for a select element.
	 *
	 * @since 4.0
	 *
	 * @param array $text_formats The array of Text Formats.
	 * @param int   $site_text_format The current "Text Format" option.
	 * @param bool  $show_default True includes the "Use default" option, false does not.
	 * @return string $markup The "Text Format" options markup.
	 */
	public function formats_select_options_get( $text_formats, $site_text_format, $show_default = true ) {

		// Init markup.
		$markup = '';

		// Bail if we don't get any.
		if ( empty( $text_formats ) ) {
			return $markup;
		}

		// Init options.
		$options = [];

		// Maybe add "Use Default".
		if ( true === $show_default ) {
			$options = [
				'<option value="" ' . ( ( false === $site_text_format || '' === $site_text_format ) ? ' selected="selected"' : '' ) . '>' .
					esc_html__( 'Use default', 'commentpress-core' ) .
				'</option>',
			];
		}

		// Build options.
		foreach ( $text_formats as $key => $text_format ) {
			if ( (string) $key === (string) $site_text_format ) {
				$options[] = '<option value="' . esc_attr( $key ) . '" selected="selected">' . esc_html( $text_format ) . '</option>';
			} else {
				$options[] = '<option value="' . esc_attr( $key ) . '">' . esc_html( $text_format ) . '</option>';
			}
		}

		// Merge options.
		if ( ! empty( $options ) ) {
			$markup = implode( "\n", $options );
		}

		// --<
		return $markup;

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the Formatter for a given Post ID.
	 *
	 * @since 4.0
	 *
	 * @param int  $post_id The numeric ID of the Post.
	 * @param bool $raw Pass "true" to get the actual meta value.
	 * @return int $formatter The numeric ID of the Formatter.
	 */
	public function get_for_post_id( $post_id, $raw = false ) {

		// Check Post for override.
		$override = get_post_meta( $post_id, $this->key_post_meta, true );

		// Return raw value if requested.
		if ( true === $raw ) {
			return $override;
		}

		// Default to current Site Text Format.
		$formatter = $this->setting_formatter_get();

		// Bail if something went wrong.
		if ( false === $override || '' === $override || ! is_numeric( $override ) ) {
			return $formatter;
		}

		// Override if different to the current Site Text Format.
		if ( (int) $override !== (int) $formatter ) {
			$formatter = $override;
		}

		// --<
		return (int) $formatter;

	}

	/**
	 * Sets the Formatter for a given Post ID.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @param int $formatter The numeric ID of the Formatter.
	 */
	public function set_for_post_id( $post_id, $formatter ) {

		// Clear the Formatter by passing an empty string.
		if ( is_string( $formatter ) && '' === $formatter ) {
			$this->delete_for_post_id( $post_id );
			return;
		}

		// Cast Formatter value as string when updating.
		update_post_meta( $post_id, $this->key_post_meta, (string) $formatter );

	}

	/**
	 * Deletes the Formatter for a given Post.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 */
	public function delete_for_post_id( $post_id ) {

		// Delete the Formatter meta value.
		delete_post_meta( $post_id, $this->key_post_meta );

	}

	// -------------------------------------------------------------------------

	/**
	 * Checks if the Formatter of a Post is different to the Site Text Format.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @return bool $overridden True if overridden, false otherwise.
	 */
	public function is_overridden( $post_id ) {

		// Get the Formatter setting.
		$formatter_setting = $this->setting_formatter_get();

		// Get the Formatter for this Post.
		$formatter_post = $this->get_for_post_id( $post_id );

		// Do override check.
		if ( (int) $formatter_setting !== (int) $formatter_post ) {
			return true;
		}

		// Not overridden.
		return false;

	}

}
