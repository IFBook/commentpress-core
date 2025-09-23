<?php
/**
 * CommentPress Core Comments class.
 *
 * Handles functionality related to Comments in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Comments Class.
 *
 * This class provides functionality related to Comments in CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Comments {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_Core_Loader
	 */
	public $core;

	/**
	 * Comment Tagging object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_Core_Comments_Tagging
	 */
	public $tagging;

	/**
	 * Relative path to the classes directory.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $classes_path = 'includes/core/classes/';

	/**
	 * Relative path to the Metabox directory.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $metabox_path = 'includes/core/assets/templates/wordpress/metaboxes/';

	/**
	 * "Live comment refreshing" settings key.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $key_live = 'cp_para_comments_live';

	/**
	 * Constructor.
	 *
	 * @since 4.0
	 *
	 * @param CommentPress_Core_Loader $core Reference to the core loader object.
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

		// Only do this once.
		static $done;
		if ( isset( $done ) && true === $done ) {
			return;
		}

		// Bootstrap object.
		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

		/**
		 * Fires when the Comments object has loaded.
		 *
		 * Used internally to bootstrap objects.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/comments/loaded' );

		// We're done.
		$done = true;

	}

	/**
	 * Includes class files.
	 *
	 * @since 4.0
	 */
	public function include_files() {

		// Include theme class files.
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-core-comments-tagging.php';

	}

	/**
	 * Sets up the objects in this class.
	 *
	 * @since 4.0
	 */
	public function setup_objects() {

		// Initialise theme objects.
		$this->tagging = new CommentPress_Core_Comments_Tagging( $this );

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

		// Separate callbacks into descriptive methods.
		$this->register_hooks_settings();
		$this->register_hooks_activation();
		$this->register_hooks_comments();

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
		add_action( 'commentpress/core/settings/site/metaboxes/after', [ $this, 'settings_meta_boxes_append' ], 40 );

		// Save data from Site Settings form submissions.
		add_action( 'commentpress/core/settings/site/save/before', [ $this, 'settings_meta_box_save' ] );

	}

	/**
	 * Registers activation/deactivation hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_activation() {

		// Act when this plugin is activated.
		add_action( 'commentpress/core/activate', [ $this, 'plugin_activate' ], 20 );

		// Act when this plugin is deactivated.
		add_action( 'commentpress/core/deactivate', [ $this, 'plugin_deactivate' ], 40 );

	}

	/**
	 * Registers Comment-related hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_comments() {

		// Modify Comment posting.
		add_action( 'comment_post', [ $this, 'save_comment' ], 10, 2 );

		// Allow Comment Authors to edit their own Comments.
		add_filter( 'map_meta_cap', [ $this, 'enable_comment_editing' ], 10, 4 );

		/*
		// Auto-approve Comments for registered Users.
		add_action( 'preprocess_comment', [ $this, 'allow_comment_editing' ], 1 );
		*/

		// Amend the behaviour of Featured Comments plugin.
		add_action( 'plugins_loaded', [ $this, 'featured_comments_override' ], 1000 );

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
		$settings[ $this->key_live ] = 0;

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

		// Create "Commenting Settings" metabox.
		add_meta_box(
			'commentpress_commenting',
			__( 'Commenting Settings', 'commentpress-core' ),
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
		$live = $this->setting_live_get();

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->metabox_path . 'metabox-settings-site-comments.php';

	}

	/**
	 * Saves the data from the Site Settings "Commenting Settings" metabox.
	 *
	 * Adds the data to the settings array. The settings are actually saved later.
	 *
	 * @see CommentPress_Core_Settings_Site::form_submitted()
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_save() {

		// Get "Live comment refreshing" value.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$live = isset( $_POST[ $this->key_live ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_live ] ) ) : '0';

		// Set the setting.
		$this->setting_live_set( ( $live ? 1 : 0 ) );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the "Live comment refreshing" setting.
	 *
	 * @since 4.0
	 *
	 * @return int $live The setting if found, zero otherwise.
	 */
	public function setting_live_get() {

		// Get the setting.
		$live = $this->core->db->setting_get( $this->key_live );

		// Return setting or boolean if empty.
		return ! empty( $live ) ? (int) $live : 0;

	}

	/**
	 * Sets the "Live comment refreshing" setting.
	 *
	 * @since 4.0
	 *
	 * @param int $live The setting value.
	 */
	public function setting_live_set( $live ) {

		// Set the setting.
		$this->core->db->setting_set( $this->key_live, $live );

	}

	// -------------------------------------------------------------------------

	/**
	 * Runs when core is activated.
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_activate( $network_wide ) {

		// Bail if plugin is network activated.
		if ( $network_wide ) {
			return;
		}

		// Install the database schema.
		$this->schema_install();

	}

	/**
	 * Runs when core is deactivated.
	 *
	 * NOTE: The database schema is only restored in "uninstall.php" when this
	 * plugin is deleted.
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_deactivate( $network_wide ) {
		// Keep schema when deactivating.
	}

	// -------------------------------------------------------------------------

	/**
	 * Installs the WordPress database schema.
	 *
	 * @since 4.0
	 *
	 * @return bool $result True if successful, false otherwise.
	 */
	public function schema_install() {

		// Database object.
		global $wpdb;

		// Include WordPress install helper script.
		require_once ABSPATH . 'wp-admin/install-helper.php';

		// Add the column, if not already there.
		$result = maybe_add_column(
			$wpdb->comments,
			'comment_signature',
			"ALTER TABLE `$wpdb->comments` ADD `comment_signature` VARCHAR(255) NULL;"
		);

		// --<
		return $result;
	}

	// -------------------------------------------------------------------------

	/**
	 * Stores our additional Comment data.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @param int    $comment_id The numeric ID of the Comment.
	 * @param string $comment_status The status of the Comment.
	 */
	public function save_comment( $comment_id, $comment_status ) {

		// Store our Comment Text Signature.
		$result = $this->save_comment_signature( $comment_id );

		// Store our Comment Selection.
		$result = $this->save_comment_selection( $comment_id );

		// In multipage situations, store our comment's Page.
		$result = $this->save_comment_page( $comment_id );

		// Has the Comment been marked as spam?
		if ( 'spam' === $comment_status ) {

			// TODO: Check for AJAX request.

			// Yes - let the commenter know without throwing an AJAX error.
			wp_die( esc_html__( 'This comment has been marked as spam. Please contact a site administrator.', 'commentpress-core' ) );

		}

	}

	/**
	 * When a Comment is saved, this also saves the Text Signature.
	 *
	 * @since 3.0
	 *
	 * @param int    $comment_id The numeric ID of the Comment.
	 * @param string $text_signature The Text Signature of the Comment.
	 * @return bool $result True if successful, false otherwise.
	 */
	public function save_comment_signature( $comment_id, $text_signature = '' ) {

		// Database object.
		global $wpdb;

		// If no Text Signature is passed, look in POST.
		if ( empty( $text_signature ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$text_signature = isset( $_POST['text_signature'] ) ? sanitize_text_field( wp_unslash( $_POST['text_signature'] ) ) : '';
		}

		// Did we get one?
		if ( ! empty( $text_signature ) ) {

			// Escape it.
			$text_signature = esc_sql( $text_signature );

			// Store Comment Text Signature.
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$result = $wpdb->query(
				$wpdb->prepare(
					"UPDATE $wpdb->comments SET comment_signature = %s WHERE comment_ID = %d",
					$text_signature,
					$comment_id
				)
			);

		} else {

			// Set result to true - why not, eh?
			$result = true;

		}

		// --<
		return $result;

	}

	/**
	 * When a Comment is saved, this also saves the Page it was submitted on.
	 *
	 * This allows us to point to the correct Page of a multipage Post without
	 * parsing the content every time.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @param int    $comment_id The numeric ID of the Comment.
	 * @param int    $page_number The number of the Page.
	 * @param string $text_signature The Text Signature of the Comment.
	 */
	private function save_comment_page( $comment_id, $page_number = false, $text_signature = '' ) {

		// If no Page number is passed, look in POST.
		if ( empty( $page_number ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$page_number = isset( $_POST['page'] ) ? sanitize_text_field( wp_unslash( $_POST['page'] ) ) : false;
		}

		// Bail if this is not a paged Post.
		if ( ! is_numeric( $page_number ) ) {
			return;
		}

		// If no Text Signature is passed, look in POST.
		if ( empty( $text_signature ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$text_signature = isset( $_POST['text_signature'] ) ? sanitize_text_field( wp_unslash( $_POST['text_signature'] ) ) : '';
		}

		// Is it a paragraph-level comment?
		if ( ! empty( $text_signature ) ) {

			// Set key.
			$key = '_cp_comment_page';

			// Add or update the data.
			if ( get_comment_meta( $comment_id, $key, true ) !== '' ) {
				update_comment_meta( $comment_id, $key, $page_number );
			} else {
				add_comment_meta( $comment_id, $key, $page_number, true );
			}

			// Okay, we're done.
			return;

		}

		/*
		// Top level Comments are always Page 1.
		$page_number = 1;
		*/

	}

	/**
	 * When a Comment is saved, this also saves the Text Selection.
	 *
	 * @since 3.9
	 *
	 * @param int    $comment_id The numeric ID of the Comment.
	 * @param string $text_selection The Text Selection of the Comment.
	 * @return bool $result True if successful, false otherwise.
	 */
	private function save_comment_selection( $comment_id, $text_selection = '' ) {

		// If no Text Selection is passed, look in POST.
		if ( empty( $text_selection ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$text_selection = isset( $_POST['text_selection'] ) ? sanitize_text_field( wp_unslash( $_POST['text_selection'] ) ) : '';
		}

		// Bail if we didn't get one.
		if ( empty( $text_selection ) ) {
			return true;
		}

		// Sanity check: must have a comma.
		if ( false === stristr( $text_selection, ',' ) ) {
			return true;
		}

		// Make into an array.
		$selection = explode( ',', $text_selection );

		// Sanity check: must have only two elements.
		if ( count( $selection ) !== 2 ) {
			return true;
		}

		// Sanity check: both elements must be integers.
		$start_end = [];
		foreach ( $selection as $item ) {

			// Not an integer - bail entirely.
			if ( ! is_numeric( $item ) ) {
				return true;
			}

			// Cast as integer and add to array.
			$start_end[] = (int) $item;

		}

		// Okay, we're good to go.
		$selection_data = implode( ',', $start_end );

		// Set key.
		$key = '_cp_comment_selection';

		// Add or update the data.
		$current = get_comment_meta( $comment_id, $key, true );
		if ( ! empty( $current ) ) {
			update_comment_meta( $comment_id, $key, $selection_data );
		} else {
			add_comment_meta( $comment_id, $key, $selection_data, true );
		}

		// --<
		return true;

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds the capability for Users to edit their own Comments.
	 *
	 * @see http://scribu.net/wordpress/prevent-blog-authors-from-editing-comments.html
	 *
	 * @since 3.3
	 *
	 * @param array  $caps The existing capabilities array for the WordPress User.
	 * @param string $cap The capability in question.
	 * @param int    $user_id The numeric ID of the WordPress User.
	 * @param array  $args The additional arguments.
	 * @return array $caps The modified capabilities array for the WordPress User.
	 */
	public function enable_comment_editing( $caps, $cap, $user_id, $args ) {

		// Only apply this to queries for "edit_comment" cap.
		if ( 'edit_comment' !== $cap ) {
			return $caps;
		}

		// Get the Comment.
		$comment = get_comment( $args[0] );

		// Is the User the same as the Comment Author?
		if ( (int) $comment->user_id === (int) $user_id ) {
			$caps = [ 'edit_posts' ];
		}

		// --<
		return $caps;

	}

	/**
	 * Allows registered Users to edit their own Comments.
	 *
	 * Not used at present.
	 *
	 * @since 3.3
	 *
	 * @param array $commentdata The array of Comment data.
	 * @return array $commentdata The modified array of Comment data.
	 */
	public function allow_comment_editing( $commentdata ) {

		// Get the User ID of the Comment Author.
		$user_id = (int) $commentdata['user_ID'];

		// If Comment Author is a registered User, approve the Comment.
		if ( ! empty( $user_id ) ) {
			add_filter( 'pre_comment_approved', [ $this, 'approve_comment' ] );
		} else {
			add_filter( 'pre_comment_approved', [ $this, 'moderate_comment' ] );
		}

		// --<
		return $commentdata;

	}

	/**
	 * Approves Comments for registered Users.
	 *
	 * Not used at present.
	 *
	 * @since 3.3
	 *
	 * @param bool $approved True if the Comment is approved, false otherwise.
	 * @return bool $approved True if the Comment is approved, false otherwise.
	 */
	public function approve_comment( $approved ) {
		$approved = 1;
		return $approved;
	}

	/**
	 * Moderates Comments for unregistered Users.
	 *
	 * Not used at present.
	 *
	 * @since 3.3
	 *
	 * @param bool $approved True if the Comment is approved, false otherwise.
	 * @return bool $approved True if the Comment is approved, false otherwise.
	 */
	public function moderate_comment( $approved ) {
		if ( 'spam' !== $approved ) {
			$approved = 0;
		}
		return $approved;
	}

	// -------------------------------------------------------------------------

	/**
	 * Override the Featured Comments behaviour.
	 *
	 * @since 3.4.8
	 * @since 4.0 Moved to this class.
	 */
	public function featured_comments_override() {

		// Is the plugin available?
		if ( ! function_exists( 'wp_featured_comments_load' ) ) {
			return;
		}

		// Get instance.
		$fc = wp_featured_comments_load();

		// Remove comment_text filter.
		remove_filter( 'comment_text', [ $fc, 'comment_text' ], 10 );

		// Get the plugin markup in the Comment edit section.
		add_filter( 'cp_comment_edit_link', [ $this, 'featured_comments_markup' ], 100, 2 );

	}

	/**
	 * Get the Featured Comments link markup.
	 *
	 * @since 3.4.8
	 * @since 4.0 Moved to this class.
	 *
	 * @param string $editlink The existing HTML link.
	 * @param array  $comment The Comment data.
	 * @return string $editlink The modified HTML link.
	 */
	public function featured_comments_markup( $editlink, $comment ) {

		// Is the plugin available?
		if ( ! function_exists( 'wp_featured_comments_load' ) ) {
			return $editlink;
		}

		// Get instance.
		$fc = wp_featured_comments_load();

		// Get markup.
		return $editlink . $fc->comment_text( '', $comment );

	}

}
