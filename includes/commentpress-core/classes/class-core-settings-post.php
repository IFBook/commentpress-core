<?php
/**
 * CommentPress Core Post Settings class.
 *
 * Handles "Post Settings" in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Post Settings Class.
 *
 * This class provides "Post Settings" to CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Settings_Post {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

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
	private $nonce_name = 'commentpress_nonce';

	/**
	 * Metabox nonce value.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $nonce_value The name of the metabox nonce value.
	 */
	private $nonce_value = 'commentpress_post_settings';

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

		// Add meta boxes.
		add_action( 'add_meta_boxes', [ $this, 'metabox_add' ], 10, 2 );

		// Intercept save.
		add_action( 'save_post', [ $this, 'save_post' ], 10, 2 );

		// Intercept delete.
		add_action( 'before_delete_post', [ $this, 'delete_post' ], 10, 1 );

		/*
		// Maybe create a new Revision.
		add_action( 'save_post', [ $this, 'revision_create' ], 10, 2 );

		// Maybe delete Newer Post pointer in Older Post meta.
		add_action( 'before_delete_post', [ $this, 'delete_post' ] );
		*/

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds metabox to our supported "Edit" screens.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed and moved to this class.
	 *
	 * @param string $post_type The WordPress Post Type.
	 * @param WP_Post $post The Post object.
	 */
	public function metabox_add( $post_type, $post ) {

		// Bail if not "post" Post Type.
		if ( 'post' !== $post_type ) {
			return;
		}

		// Add our metabox.
		add_meta_box(
			'commentpress_post_settings',
			__( 'CommentPress Post Settings', 'commentpress-core' ),
			[ $this, 'metabox_render' ],
			$post_type,
			'side'
		);

	}

	/**
	 * Adds meta box to "Edit" screens.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed and moved to this class.
	 *
	 * @param WP_Post $post The Post object.
	 */
	public function metabox_render( $post ) {

		/**
		 * Allow metabox to be hidden.
		 *
		 * @since 3.4
		 *
		 * @param bool False (shown) by default.
		 */
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) {
			return;
		}

		// Bail if we do not have the option to choose the default sidebar (new in 3.3.3).
		if ( ! $this->core->db->option_exists( 'cp_sidebar_default' ) ) {
			return;
		}

		// Set key.
		$key = '_cp_sidebar_default';

		// Default to show.
		$sidebar = $this->core->db->option_get( 'cp_sidebar_default' );

		// Override if the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
			$sidebar = get_post_meta( $post->ID, $key, true );
		}

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-post.php';

	}

	// -------------------------------------------------------------------------

	/**
	 * Stores our additional params.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed and moved to this class.
	 *
	 * @param int $post_id The numeric ID of the Post (or revision).
	 * @param object $post The Post object.
	 */
	public function save_post( $post_id, $post ) {

		// We don't use "post_id" because we're not interested in revisions.

		// Store our meta data.
		$result = $this->core->db->save_meta( $post );

	}

	/**
	 * Check for data integrity of other Posts when one is deleted.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed and moved to this class.
	 *
	 * @param int $post_id The numeric ID of the Post (or revision).
	 */
	public function delete_post( $post_id ) {

		// Store our meta data.
		$result = $this->core->db->delete_meta( $post_id );

	}

}
