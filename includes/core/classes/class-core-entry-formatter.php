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
		'post',
		'page',
	];

	/**
	 * Formatter option in Site Settings.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $option_formatter The "Default Text Format" option in Site Settings.
	 */
	public $option_formatter = 'cp_blog_type';

	/**
	 * Formatter meta key.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $meta_key The "Formatter" meta key.
	 */
	public $meta_key = '_cp_post_type_override';

	/**
	 * Partials template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $metabox_path Relative path to the Partials directory.
	 */
	private $partials_path = 'includes/core/assets/templates/wordpress/partials/';

	/**
	 * Metabox select element name.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $element_select The name of the metabox select element.
	 */
	private $element_select = 'cp_formatter_value';

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

		// Add our option to the Site Settings "General Settings" metabox.
		add_action( 'commentpress/core/settings/site/metabox/general/after', [ $this, 'metabox_settings_get' ] );

		// Save data from "Site Settings" screen.
		add_action( 'commentpress/core/settings/site/saved', [ $this, 'save_for_settings' ] );



		// Save default Post Formatter on Special Pages.
		add_action( 'commentpress/core/db/page/special/title/created', [ $this, 'default_set_for_post' ] );

		// Save Post Formatter on new Revisions.
		add_action( 'commentpress/core/revisions/revision/meta/added', [ $this, 'revision_formatter_set' ] );

		// Add filter for Content Parser.
		add_filter( 'commentpress/core/parser/content/parser', [ $this, 'content_parser' ], 21, 1 );

		// TODO: Move Blog Type save handling to this class.

		// TODO: Untangle the following.

		// Set Blog Type options.
		add_filter( 'cp_blog_type_options', [ $this, 'blog_type_options' ], 21 );

		// Set Blog Type options label.
		add_filter( 'cp_blog_type_label', [ $this, 'blog_type_label' ], 21 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our option to the Site Settings "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function metabox_settings_get() {

		// Get the "Text Format" options array.
		$types = $this->options_array_get();
		if ( empty( $types ) ) {
			return;
		}

		/**
		 * Filters the Blog Type label.
		 *
		 * @since 3.3.1
		 *
		 * @param str The the Blog Type label.
		 */
		$type_title = apply_filters( 'cp_blog_type_label', __( 'Default Text Format', 'commentpress-core' ) );

		// Get existing.
		$blog_type = $this->core->db->option_get( $this->option_formatter );

		// Get the "Text Format" options markup.
		$type_options = $this->options_markup_get( $types, $blog_type );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->partials_path . 'partial-entry-formatter-settings.php';

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

		// Get the "Text Format" options array.
		$types = $this->options_array_get();
		if ( empty( $types ) ) {
			return;
		}

		// Default to current Blog Type.
		$value = $this->get_for_post_id( $post->ID );

		// Get the "Text Format" options markup.
		$type_options = $this->options_markup_get( $types, $value );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->partials_path . 'partial-entry-formatter-entry.php';

	}

	/**
	 * Saves the Formatter for a given Entry.
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
		if ( $post->post_type === 'post' && ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}
		if ( $post->post_type === 'page' && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// We need to make sure this only runs once.
		if ( $this->saved_post === false ) {
			$this->saved_post = true;
		} else {
			return;
		}

		// Get the value of the metabox select element.
		$formatter = isset( $_POST[ $this->element_select ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->element_select ] ) ) : '';

		// Save the Formatter for the Post.
		$this->set_for_post_id( $post->ID, $formatter );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the Formatter for a given Post ID.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @return int $formatter The numeric ID of the Formatter.
	 */
	public function get_for_post_id( $post_id ) {

		// Default to current Blog Type.
		$formatter = $this->core->db->option_get( $this->option_formatter );

		// Check Post for override.
		$override = get_post_meta( $post_id, $this->meta_key, true );

		// Bail if something went wrong.
		if ( $override === false || $override === '' || ! is_numeric( $override ) ) {
			return $formatter;
		}

		// Override if different to the current Blog Type.
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
		if ( is_string( $formatter ) && $formatter === '' ) {
			$this->delete_for_post_id( $post_id );
			return;
		}

		// Cast Formatter value as string when updating.
		update_post_meta( $post_id, $this->meta_key, (string) $formatter );

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
		delete_post_meta( $post_id, $this->meta_key );

	}

	// -------------------------------------------------------------------------

	/**
	 * Checks if the Formatter of a Post is different to the Blog Type.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @return bool $overridden True if overridden, false otherwise.
	 */
	public function is_overridden( $post_id ) {

		// Get the current Blog Type.
		$formatter_blog = $this->core->db->option_get( $this->option_formatter );

		// Get the Formatter for this Post.
		$formatter_post = $this->get_for_post_id( $post_id );

		// Do override check.
		if ( (int) $formatter_blog !== (int) $formatter_post ) {
			return true;
		}

		// Not overridden.
		return false;

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
	 * @param int $new_post_id The numeric ID of the new Post.
	 * @param WP_Post $post The WordPress Post object that has been copied.
	 */
	public function revision_formatter_set( $new_post_id, $post ) {

		// Try and get the Formatter in the current Post.
		$formatter = $this->get_for_post_id( $post->ID );
		if ( $formatter === false || $formatter === '' || ! is_numeric( $formatter ) ) {
			return;
		}

		// Add Formatter to new Post.
		$this->set_for_post_id( $new_post_id, $formatter );

	}

	// -------------------------------------------------------------------------

	/**
	 * Override the name of the Blog Types dropdown label.
	 *
	 * @since 3.3
	 *
	 * @param str $name The existing name of the label.
	 * @return str $name The modified name of the label.
	 */
	public function blog_type_label( $name ) {
		return __( 'Default Text Format', 'commentpress-core' );
	}

	/**
	 * Define the "types" of Blog.
	 *
	 * @since 3.3
	 *
	 * @param array $existing_options The existing types of Blog.
	 * @return array $existing_options The modified types of Blog.
	 */
	public function blog_type_options( $existing_options ) {

		// Define types.
		$types = [
			__( 'Prose', 'commentpress-core' ), // Types[0].
			__( 'Poetry', 'commentpress-core' ), // Types[1].
		];

		/**
		 * Filters the Blog Types.
		 *
		 * @since 3.3.1
		 *
		 * @param array $types The array of Blog Type.
		 */
		return apply_filters( 'cp_class_commentpress_formatter_types', $types );

	}

	// -------------------------------------------------------------------------

	/**
	 * Chooses the Content Parser by Blog Type or Post meta value.
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
		if ( $formatter === false || $formatter === '' || ! is_numeric( $formatter ) ) {
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
	 * @return array $types The array of Text Format options.
	 */
	public function options_array_get() {

		// Define no types.
		$types = [];

		/**
		 * Build Text Format options.
		 *
		 * @since 3.3.1
		 *
		 * @param array $types Empty by default since others add them.
		 */
		$types = apply_filters( 'cp_blog_type_options', $types );

		// Bail if we don't get any.
		if ( empty( $types ) ) {
			return $types;
		}

		// --<
		return $types;

	}

	/**
	 * Gets the "Text Format" options for a select element.
	 *
	 * @since 4.0
	 *
	 * @param array $types The array of "Text Format" options.
	 * @param int $current The current "Text Format" option.
	 * @return string $markup The "Text Format" options markup.
	 */
	public function options_markup_get( $types, $current ) {

		// Init markup.
		$markup = '';

		// Bail if we don't get any.
		if ( empty( $types ) ) {
			return $markup;
		}

		// Build options.
		$options = [];
		foreach ( $types as $key => $type ) {
			if ( (int) $key === (int) $current ) {
				$options[] = '<option value="' . esc_attr( $key ) . '" selected="selected">' . esc_html( $type ) . '</option>';
			} else {
				$options[] = '<option value="' . esc_attr( $key ) . '">' . esc_html( $type ) . '</option>';
			}
		}

		// Merge options.
		if ( ! empty( $options ) ) {
			$markup = implode( "\n", $options );
		}

		// --<
		return $markup;

	}

}
