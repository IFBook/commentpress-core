<?php
/**
 * CommentPress Core Entry Metabox class.
 *
 * Handles the "CommentPress Settings" Metabox for Entries in CommentPress Core.
 * Entries are Posts in commentable Post Types.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Post Settings Class.
 *
 * This class provides a Metabox for Entries in CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Entry_Metabox {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
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
	 * Metabox template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $metabox_path Relative path to the Metabox directory.
	 */
	private $metabox_path = 'includes/core/assets/templates/wordpress/metaboxes/';

	/**
	 * Metabox nonce name.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $nonce_field The name of the metabox nonce element.
	 */
	private $nonce_field = 'commentpress_core_entry_nonce';

	/**
	 * Metabox nonce action.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $nonce_action The name of the metabox nonce action.
	 */
	private $nonce_action = 'commentpress_core_entry_action';

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

		// Add meta boxes to our supported "Edit Entry" screens.
		add_action( 'add_meta_boxes', [ $this, 'metabox_add' ], 20, 2 );

		// Intercept save.
		add_action( 'save_post', [ $this, 'post_saved' ], 10, 2 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds a metabox to our supported "Edit Entry" screens.
	 *
	 * @since 4.0
	 *
	 * @param string  $post_type The WordPress Post Type.
	 * @param WP_Post $post The Post object.
	 */
	public function metabox_add( $post_type, $post ) {

		// Bail if not one of our supported Post Types.
		if ( ! in_array( $post_type, $this->post_types ) ) {
			return;
		}

		// Add our metabox.
		add_meta_box(
			'commentpress_settings_entry',
			__( 'CommentPress Settings', 'commentpress-core' ),
			[ $this, 'metabox_render' ],
			$post_type,
			'side'
		);

	}

	/**
	 * Renders the metabox on "Edit Entry" screens.
	 *
	 * @since 4.0
	 *
	 * @param WP_Post $post The WordPress Post object.
	 */
	public function metabox_render( $post ) {

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-entry.php';

	}

	// -------------------------------------------------------------------------

	/**
	 * Saves CommentPress meta data.
	 *
	 * @since 4.0
	 *
	 * @param int    $post_id The numeric ID of the Post (or revision).
	 * @param object $post The Post object.
	 */
	public function post_saved( $post_id, $post ) {

		// Bail if there's no Post object.
		if ( ! $post ) {
			return;
		}

		// Authenticate.
		$nonce = isset( $_POST[ $this->nonce_field ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->nonce_field ] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, $this->nonce_action ) ) {
			return;
		}

		// Bail if this is an autosave.
		if ( wp_is_post_autosave( $post ) ) {
			return;
		}

		// Check for revision.
		$parent_id = wp_is_post_revision( $post );
		if ( $parent_id ) {
			$post = get_post( $parent_id );
		}

		// Maybe save Page meta.
		if ( 'page' === $post->post_type ) {
			$this->page_meta_save( $post );
		}

		// Maybe save Post meta.
		if ( 'post' === $post->post_type ) {
			$this->post_meta_save( $post );
		}

		/**
		 * Fires when an Entry has been saved.
		 *
		 * * Callbacks do not need to verify the nonce as this has already been done.
		 * * Callbacks should, however, implement their own permissions checks.
		 *
		 * @since 4.0
		 *
		 * @param object $post The WordPress Post object.
		 */
		do_action( 'commentpress/core/settings/post/saved', $post );

	}

	// -------------------------------------------------------------------------

	/**
	 * Saves CommentPress meta data for Posts.
	 *
	 * @since 3.0
	 *
	 * @param object $post The Post object.
	 */
	private function post_meta_save( $post ) {

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return false;
		}

		/**
		 * Fire when Post meta is being saved.
		 *
		 * * Callbacks do not need to verify the nonce as this has already been done.
		 * * Callbacks should, however, implement their own permissions checks.
		 *
		 * @since 4.0
		 *
		 * @param object $post The WordPress Post object.
		 */
		do_action( 'commentpress/core/settings/post/post_meta/saved', $post );

	}

	// -------------------------------------------------------------------------

	/**
	 * Saves CommentPress meta data for Pages.
	 *
	 * @since 3.4
	 *
	 * @param object $post The Post object.
	 */
	private function page_meta_save( $post ) {

		// Check permissions.
		if ( ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		/**
		 * Fire when Page meta is being saved.
		 *
		 * * Callbacks do not need to verify the nonce as this has already been done.
		 * * Callbacks should, however, implement their own permissions checks.
		 *
		 * @since 4.0
		 *
		 * @param object $post The WordPress Post object.
		 */
		do_action( 'commentpress/core/settings/post/page_meta/saved', $post );

	}

}
