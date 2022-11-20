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
class CommentPress_Core_Formatter {

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
	 * @var str $post_types The supported Post Types meta key.
	 */
	public $post_types = [
		'post',
		'page',
	];

	/**
	 * Formatter meta key.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $meta_key_formatter The "Formatter" meta key.
	 */
	public $meta_key_formatter = '_cp_post_type_override';

	/**
	 * Metabox template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $metabox_path Relative path to the Metabox directory.
	 */
	private $metabox_path = 'includes/commentpress-core/assets/templates/wordpress/metaboxes/';

	/**
	 * Metabox nonce name.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $nonce_name The name of the metabox nonce element.
	 */
	private $nonce_name = 'commentpress_formatter_nonce';

	/**
	 * Metabox nonce value.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $nonce_value The name of the metabox nonce value.
	 */
	private $nonce_value = 'commentpress_formatter_value';

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
	 * @since 3.3
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

		// Add meta boxes.
		add_action( 'add_meta_boxes', [ $this, 'metabox_add' ], 10, 2 );

		// Add Formatter meta value to Post.
		add_action( 'save_post', [ $this, 'formatter_save' ], 10, 2 );

		// Save default Post Formatter on Special Pages.
		add_action( 'commentpress/core/db/page/special/title/created', [ $this, 'formatter_default_apply' ] );

		// Save Post Formatter on new Revisions.
		add_action( 'commentpress/core/revisions/revision/meta/added', [ $this, 'revision_formatter_set' ] );

		// Add filter for Content Parser.
		add_filter( 'commentpress/core/parser/content/parser', [ $this, 'content_parser' ], 21, 1 );

		// TODO: Move Blog Type save handling to this class.

		// Add our option to the Site Settings "General Settings" metabox.
		add_action( 'commentpress/core/settings/site/metabox/general/after', [ $this, 'form_element_render' ] );

		// TODO: Untangle the following.

		// Set Blog Type options.
		add_filter( 'cp_blog_type_options', [ $this, 'blog_type_options' ], 21 );

		// Set Blog Type options label.
		add_filter( 'cp_blog_type_label', [ $this, 'blog_type_label' ], 21 );



	}

	// -------------------------------------------------------------------------

	/**
	 * Adds metabox to our supported "Edit" screens.
	 *
	 * @since 4.0
	 *
	 * @param string $post_type The WordPress Post Type.
	 * @param WP_Post $post The Post object.
	 */
	public function metabox_add( $post_type, $post ) {

		// Bail if not one of our supported Post Types.
		if ( ! in_array( $post_type, $this->post_types ) ) {
			return;
		}

		// Add our metabox.
		add_meta_box(
			'commentpress_formatter',
			__( 'CommentPress Text Format', 'commentpress-core' ),
			[ $this, 'metabox_render' ],
			$post_type,
			'side'
		);

	}

	/**
	 * Adds metabox to "Edit" screens.
	 *
	 * @since 4.0
	 *
	 * @param WP_Post $post The Post object.
	 */
	public function metabox_render( $post ) {

		// Bail if we do not have the option to choose Blog Type (new in 3.3.1).
		if ( ! $this->core->db->option_exists( 'cp_blog_type' ) ) {
			return;
		}

		/**
		 * Build Text Format options.
		 *
		 * @since 3.3.1
		 *
		 * @param array $types Empty by default since others add them.
		 */
		$types = apply_filters( 'cp_blog_type_options', [] );

		// Bail if we don't get any.
		if ( empty( $types ) ) {
			return;
		}

		// Default to current Blog Type.
		$value = $this->core->db->option_get( 'cp_blog_type' );

		// Override if the custom field has a value.
		if ( get_post_meta( $post->ID, $this->meta_key_formatter, true ) !== '' ) {
			$value = get_post_meta( $post->ID, $this->meta_key_formatter, true );
		}

		// Construct options.
		$type_option_list = [];
		$n = 0;
		foreach ( $types as $type ) {
			if ( $n === (int) $value ) {
				$type_option_list[] = '<option value="' . $n . '" selected="selected">' . $type . '</option>';
			} else {
				$type_option_list[] = '<option value="' . $n . '">' . $type . '</option>';
			}
			$n++;
		}
		$type_options = implode( "\n", $type_option_list );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-formatter.php';

	}

	// -------------------------------------------------------------------------

	/**
	 * Handles authentication and adds Formatter meta value.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The ID of the saved WordPress Post or Revision ID.
	 * @param object $post The saved WordPress Post object.
	 */
	public function formatter_save( $post_id, $post ) {

		// Bail if no Post object.
		if ( ! ( $post instanceof WP_Post ) ) {
			return false;
		}

		// Bail if not one of our supported Post Types.
		if ( ! in_array( $post->post_type, $this->post_types ) ) {
			return;
		}

		// Authenticate.
		$nonce = isset( $_POST[ $this->nonce_name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->nonce_name ] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, $this->nonce_value ) ) {
			return;
		}

		// Bail if this is an autosave.
		if ( wp_is_post_autosave( $post ) ) {
			return;
		}

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
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
		$this->formatter_set( $post_id, $formatter );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the Formatter for a given Post.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @return int $formatter The numeric ID of the Formatter.
	 */
	public function formatter_get( $post_id ) {

		// Default to current Blog Type.
		$formatter = $this->core->db->option_get( 'cp_blog_type' );

		// Check Post for override.
		$override = get_post_meta( $post_id, $this->meta_key_formatter, true );

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
	 * Sets the Formatter for a given Post.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @param int $formatter The numeric ID of the Formatter.
	 */
	public function formatter_set( $post_id, $formatter ) {

		// Sanity check.
		$formatter = (int) $formatter;
		if ( ! is_int( $formatter ) ) {
			$this->formatter_delete( $post_id );
			return;
		}

		// Cast Formatter value as string when updating.
		update_post_meta( $post_id, $this->meta_key_formatter, (string) $formatter );

	}

	/**
	 * Deletes the Formatter for a given Post.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 */
	public function formatter_delete( $post_id ) {

		// Delete the Formatter meta value.
		delete_post_meta( $post_id, $this->meta_key_formatter );

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
	public function formatter_is_overridden( $post_id ) {

		// Get the current Blog Type.
		$formatter_blog = $this->core->db->option_get( 'cp_blog_type' );

		// Get the Formatter for this Post.
		$formatter_post = $this->formatter_get( $post_id );

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
	public function formatter_default_apply( $post_id ) {

		// Add default Formatter to Post.
		$this->formatter_set( $post_id, '0' );

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
		$formatter = $this->formatter_get( $post->ID );
		if ( $formatter === false || $formatter === '' || ! is_numeric( $formatter ) ) {
			return;
		}

		// Add Formatter to new Post.
		$this->formatter_set( $new_post_id, $formatter );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our option to the Site Settings "General Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function form_element_render() {

		// Do we have the option to choose Blog Type (new in 3.3.1)?
		if ( ! $this->core->db->option_exists( 'cp_blog_type' ) ) {
			return;
		}

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

		// If we get some from a plugin, say.
		if ( empty( $types ) ) {
			return;
		}

		// Define title.
		$type_title = __( 'Default Text Format', 'commentpress-core' );

		// Allow overrides.
		$type_title = apply_filters( 'cp_blog_type_label', $type_title );

		// Add extra message.
		$type_title .= __( ' (can be overridden on individual pages)', 'commentpress-core' );

		// Construct options.
		$type_option_list = [];
		$n = 0;

		// Get existing.
		$blog_type = $this->core->db->option_get( 'cp_blog_type' );

		foreach ( $types as $type ) {
			if ( $n == $blog_type ) {
				$type_option_list[] = '<option value="' . $n . '" selected="selected">' . $type . '</option>';
			} else {
				$type_option_list[] = '<option value="' . $n . '">' . $type . '</option>';
			}
			$n++;
		}
		$type_options = implode( "\n", $type_option_list );

		// Render element.
		?>
		<tr valign="top">
			<th scope="row">
				<label for="cp_blog_type">' . $type_title . '</label>
			</th>
			<td>
				<select id="cp_blog_type" name="cp_blog_type">
					' . $type_options . '
				</select>
			</td>
		</tr>
		<?php

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
		$formatter = $this->formatter_get( $post->ID );
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

}
