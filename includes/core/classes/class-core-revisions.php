<?php
/**
 * CommentPress Core Revisions class.
 *
 * Handles "Revisions" in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Revisions Class.
 *
 * This class provides "Revisions" to CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Revisions {

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
	 * Post Revisions Enabled setting key in Site Settings.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $key_sidebar The "Post Revisions Enabled" setting key in Site Settings.
	 */
	public $key_revisions = 'cp_revisions_enabled';

	/**
	 * Newer Version meta key.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $meta_key_newer_id The "Newer Version" meta key.
	 */
	public $meta_key_newer_id = '_cp_newer_version';

	/**
	 * Version Count meta key.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $meta_key The "Version Count" meta key.
	 */
	public $meta_key_version_count = '_cp_version_count';

	/**
	 * Metabox nonce name.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $nonce_field The name of the metabox nonce element.
	 */
	private $nonce_field = 'commentpress_core_revisions_nonce';

	/**
	 * Metabox nonce value.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $nonce_action The name of the metabox nonce action.
	 */
	private $nonce_action = 'commentpress_core_revisions_action';

	/**
	 * Metabox checkbox element name.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $element_checkbox The name of the metabox checkbox element.
	 */
	private $element_checkbox = 'cp_revision_create';

	/**
	 * Prevent "save_post" callback from running more than once.
	 *
	 * @since 3.3
	 * @access private
	 * @var str $saved_post True if Post already saved.
	 */
	private $saved_post = false;

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

		// Register hooks.
		$this->register_hooks_settings();

		// Init after the Database object has loaded settings.
		add_action( 'plugins_loaded', [ $this, 'register_hooks_revisions' ], 20 );

	}

	/**
	 * Registers "Site Settings" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_settings() {

		// Add our settings to default settings.
		add_filter( 'commentpress/core/settings/defaults', [ $this, 'settings_get_defaults' ] );

		// Inject form element into the "General Settings" metabox on "Site Settings" screen.
		add_action( 'commentpress/core/settings/site/metabox/general/after', [ $this, 'settings_meta_box_part_get' ], 20 );

		// Save Sidebar data from "Site Settings" screen.
		add_action( 'commentpress/core/settings/site/save/before', [ $this, 'settings_meta_box_part_save' ] );

	}

	/**
	 * Registers all other hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks_revisions() {

		// Bail if not enabled.
		if ( 'y' !== $this->setting_revisions_get() ) {
			return;
		}

		// Add meta boxes to our supported "Edit" screens.
		add_action( 'add_meta_boxes', [ $this, 'metabox_add' ], 40, 2 );

		// Maybe create a new Revision.
		add_action( 'save_post', [ $this, 'revision_create' ], 10, 2 );

		// Maybe delete Newer Post pointer in Older Post meta.
		add_action( 'before_delete_post', [ $this, 'revision_meta_delete' ] );

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
		$settings[ $this->key_revisions ] = 'n';

		// --<
		return $settings;

	}

	/**
	 * Adds our form element to the "Commenting Settings" metabox.
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_part_get() {

		// Get the value of the option.
		$revisions = $this->setting_revisions_get();

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-revisions-settings.php';

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

		// Find the data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$revisions = isset( $_POST[ $this->key_revisions ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_revisions ] ) ) : 'n';

		// Set default sidebar.
		$this->setting_revisions_set( $revisions );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the "Post Revisions Enabled" setting.
	 *
	 * @since 4.0
	 *
	 * @return str $revisions The setting if found, default otherwise.
	 */
	public function setting_revisions_get() {

		// Get the setting.
		$revisions = $this->core->db->setting_get( $this->key_revisions );

		// Return setting or default if empty.
		return ! empty( $revisions ) ? $revisions : 'n';

	}

	/**
	 * Sets the "Post Revisions Enabled" setting.
	 *
	 * @since 4.0
	 *
	 * @param str $revisions The setting value.
	 */
	public function setting_revisions_set( $revisions ) {

		// Set the setting.
		$this->core->db->setting_set( $this->key_revisions, $revisions );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds metabox to our supported "Edit" screens.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed and moved to this class.
	 *
	 * @param string  $post_type The WordPress Post Type.
	 * @param WP_Post $post The Post object.
	 */
	public function metabox_add( $post_type, $post ) {

		// Bail if not "post" Post Type.
		if ( 'post' !== $post_type ) {
			return;
		}

		// Add our metabox.
		add_meta_box(
			'commentpress_revisions',
			__( 'CommentPress Revisions', 'commentpress-core' ),
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

		// Try and get Newer Version.
		$newer_post_id = get_post_meta( $post->ID, $this->meta_key_newer_id, true );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-revisions.php';

	}

	// -------------------------------------------------------------------------

	/**
	 * Handles authentication and creates Revision.
	 *
	 * @since 4.0
	 *
	 * @param int    $post_id The ID of the saved WordPress Post or Revision ID.
	 * @param object $post The saved WordPress Post object.
	 */
	public function revision_create( $post_id, $post ) {

		// Bail if no Post object.
		if ( ! ( $post instanceof WP_Post ) ) {
			return false;
		}

		// Bail if not "post" Post Type.
		if ( 'post' !== $post->post_type ) {
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

		// Check permissions.
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		// Do we want to create a new Revision?
		$new_post = isset( $_POST[ $this->element_checkbox ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->element_checkbox ] ) ) : '';
		if ( empty( $new_post ) ) {
			return;
		}

		// We need to make sure this only runs once.
		if ( false === $this->saved_post ) {
			$this->saved_post = true;
		} else {
			return;
		}

		// Create new Post with content of current Post.
		$new_post_id = $this->revision_post_create( $post );
		if ( is_wp_error( $new_post_id ) ) {
			return;
		}

		// Add meta to new Post with meta from current Post.
		$this->revision_meta_add( $new_post_id, $post );

		/**
		 * Fires when a Revision has been created.
		 *
		 * Used internally by:
		 *
		 * @since 3.3
		 * @since 4.0 Renamed since it's only used internally.
		 *
		 * @param int $new_post_id The numeric ID of the new Post.
		 * @param WP_Post $post The copied WordPress Post object.
		 */
		do_action( 'commentpress/core/revisions/revision/created', $new_post_id, $post );

		/*
		// Redirect to new Post?

		// Get the edit Post link.
		$edit_link = get_edit_post_link( $new_post_id );
		*/

	}

	/**
	 * Create new Post with content of existing Post.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @param WP_Post $post The WordPress Post object to make a copy of.
	 * @return int|WP_Error $new_post_id The numeric ID of the new Post.
	 */
	private function revision_post_create( $post ) {

		// Define basics.
		$new_post = [
			'post_status'           => 'draft',
			'post_type'             => 'post',
			'comment_status'        => 'open',
			'ping_status'           => 'open',
			'to_ping'               => '', // Quick fix for Windows.
			'pinged'                => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt'          => '', // Quick fix for Windows.
		];

		// Default Page title.
		$prefix = __( 'Copy of ', 'commentpress-core' );

		/**
		 * Allow overrides of prefix.
		 *
		 * @since 3.3
		 *
		 * @param str $prefix The existing prefix.
		 */
		$prefix = apply_filters( 'commentpress_new_post_title_prefix', $prefix );

		/**
		 * Set title, but allow overrides.
		 *
		 * @since 3.3
		 *
		 * @param str $post_title The prefixed Post title.
		 * @param WP_Post $post The WordPress Post object to make a copy of.
		 */
		$new_post['post_title'] = apply_filters( 'commentpress_new_post_title', $prefix . $post->post_title, $post );

		/**
		 * Set excerpt, but allow overrides.
		 *
		 * @since 3.3
		 *
		 * @param str $post_excerpt The Post excerpt.
		 */
		$new_post['post_excerpt'] = apply_filters( 'commentpress_new_post_excerpt', $post->post_excerpt );

		/**
		 * Set content, but allow overrides.
		 *
		 * @since 3.3
		 *
		 * @param str $post_content The Post content.
		 */
		$new_post['post_content'] = apply_filters( 'commentpress_new_post_content', $post->post_content );

		/**
		 * Set author, but allow overrides.
		 *
		 * @since 3.3
		 *
		 * @param str $post_author The Post author.
		 */
		$new_post['post_author'] = apply_filters( 'commentpress_new_post_author', $post->post_author );

		// Insert the Post.
		$new_post_id = wp_insert_post( $new_post );

		// --<
		return $new_post_id;

	}

	/**
	 * Adds the meta data to the new Revision.
	 *
	 * @since 4.0
	 *
	 * @param int     $new_post_id The numeric ID of the new Post.
	 * @param WP_Post $post The WordPress Post object that has been copied.
	 */
	private function revision_meta_add( $new_post_id, $post ) {

		// ---------------------------------------------------------------------
		// Store ID of newer Post in current Post.
		// ---------------------------------------------------------------------

		// Save new Post ID in current Post.
		update_post_meta( $post->ID, $this->meta_key_newer_id, $new_post_id );

		// ---------------------------------------------------------------------
		// Store incremental version number in new Post.
		// ---------------------------------------------------------------------

		// Get count in current Post.
		$version_count = get_post_meta( $post->ID, $this->meta_key_version_count, true );

		// Increment if the count in current Post has a value.
		if ( ! empty( $version_count ) && is_numeric( $version_count ) ) {
			$version_count++;
		} else {
			// This must be the first new Revision (Draft 2).
			$version_count = 2;
		}

		// Add the updated count to the new Post.
		add_post_meta( $new_post_id, $this->meta_key_version_count, $version_count );

		/**
		 * Fires when Revision meta has been added.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Core_Entry_Formatter::revision_formatter_set() (Priority: 10)
		 *
		 * @since 4.0
		 *
		 * @param int $new_post_id The numeric ID of the new Post.
		 * @param WP_Post $post The copied WordPress Post object.
		 */
		do_action( 'commentpress/core/revisions/revision/meta/added', $new_post_id, $post );

	}

	/**
	 * Handles Revision deletion.
	 *
	 * For Posts with versions, we need to delete the version data in the previous
	 * Post's meta.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The ID of the deleted WordPress Post.
	 */
	public function revision_meta_delete( $post_id ) {

		// Build query.
		$query = [
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_key'   => $this->meta_key_newer_id,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_value' => $post_id,
		];

		// Get Posts with the about-to-be-deleted Post ID in meta.
		$older_posts = get_posts( $query );

		// Bail if we didn't get any.
		if ( empty( $older_posts ) ) {
			return;
		}

		// There will be only one, but let's use a loop anyway.
		foreach ( $older_posts as $older_post ) {

			// Delete it if the custom field has a value.
			if ( get_post_meta( $older_post->ID, $key, true ) !== '' ) {
				delete_post_meta( $older_post->ID, $key );
			}

		}

		// TODO: If there is a Newer Post, link Older Post to it.

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the link to the newer Revision.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The ID of the WordPress Post.
	 * @return str $newer_link The HTML link to the newer WordPress Post.
	 */
	public function link_next_get( $post_id ) {

		// Init return.
		$newer_link = '';

		// Try and get the ID of the Newer Post.
		$newer_post_id = get_post_meta( $post_id, $this->meta_key_newer_id, true );
		if ( empty( $newer_post_id ) ) {
			return $newer_link;
		}

		// Get Post.
		$newer_post = get_post( $newer_post_id );
		if ( empty( $newer_post ) ) {
			return $newer_link;
		}

		// Bail if it is not published.
		if ( 'publish' !== $newer_post->post_status ) {
			return $newer_link;
		}

		// Construct anchor.
		$title      = __( 'Newer version', 'commentpress-core' );
		$newer_link = '<a href="' . get_permalink( $newer_post->ID ) . '" title="' . esc_attr( $title ) . '">' .
			esc_html( $title ) . ' &rarr;' .
		'</a>';

		// --<
		return $newer_link;

	}

	/**
	 * Gets the link to the older Revision.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The ID of the WordPress Post.
	 * @return str $older_link The HTML link to the older WordPress Post.
	 */
	public function link_previous_get( $post_id ) {

		// Init return.
		$older_link = '';

		// Get the Post with this Post's ID in its meta.
		$args = [
			'numberposts' => 1,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_key'    => $this->meta_key_newer_id,
			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'meta_value'  => $post_id,
		];

		// Get the array.
		$older_posts = get_posts( $args );

		// Bail if we didn't find exactly one.
		if ( ! is_array( $older_posts ) || count( $older_posts ) !== 1 ) {
			return $older_link;
		}

		// Get the older Post.
		$older_post = $older_posts[0];

		// Bail if it is not published.
		if ( 'publish' !== $older_post->post_status ) {
			return $older_link;
		}

		// Construct anchor.
		$title      = __( 'Older version', 'commentpress-core' );
		$older_link = '<a href="' . get_permalink( $older_post->ID ) . '" title="' . esc_attr( $title ) . '">' .
			'&larr; ' . esc_html( $title ) .
		'</a>';

		// --<
		return $older_link;
	}

}
