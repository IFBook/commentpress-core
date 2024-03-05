<?php
/**
 * CommentPress Core Comment Tagging class.
 *
 * Handles functionality for tagging Comments in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Comment Tagging class.
 *
 * A class that handles functionality for tagging Comments in CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Comments_Tagging {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * Comments object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $comments The Comments object.
	 */
	public $comments;

	/**
	 * Parts template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $parts_path Relative path to the Parts directory.
	 */
	private $parts_path = 'includes/core/assets/templates/wordpress/parts/';

	/**
	 * Comment Tagging Enabled setting key in Site Settings.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $key_sidebar The "Comment Tagging Enabled" setting key in Site Settings.
	 */
	public $key_tagging = 'cp_tagging_enabled';

	/**
	 * Taxonomy name.
	 *
	 * @since 4.0
	 * @access private
	 * @var str $tax_name The name of the Comment Tags Taxonomy.
	 */
	private $tax_name = 'comment_tags';

	/**
	 * Taxonomy prefix for Select2.
	 *
	 * @since 4.0
	 * @access private
	 * @var str $tax_prefix The Taxonomy prefix for Select2.
	 */
	private $tax_prefix = 'cmmnt_tggr';

	/**
	 * Constructor.
	 *
	 * @since 4.0
	 *
	 * @param object $comments Reference to the core Comments object.
	 */
	public function __construct( $comments ) {

		// Store references.
		$this->comments = $comments;
		$this->core     = $comments->core;

		// Init when the Comments object is fully loaded.
		add_action( 'commentpress/core/comments/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this object.
	 *
	 * @since 4.0
	 */
	public function initialise() {

		// Defer to Comment Tagger if present.
		if ( defined( 'COMMENT_TAGGER_VERSION' ) ) {
			return;
		}

		// Register hooks.
		$this->register_hooks_settings();

		// Init after the Database object has loaded settings.
		add_action( 'plugins_loaded', [ $this, 'register_hooks_tagging' ], 20 );

		/**
		 * Fires when the Comments Tagging object has loaded.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/comments/tagging/loaded' );

	}

	/**
	 * Registers "Site Settings" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_settings() {

		// Add our settings to default settings.
		add_filter( 'commentpress/core/settings/defaults', [ $this, 'settings_get_defaults' ] );

		// Inject form element into the "Commenting Settings" metabox on "Site Settings" screen.
		add_action( 'commentpress/core/settings/site/metabox/comment/before', [ $this, 'settings_meta_box_part_get' ], 20 );

		// Save Sidebar data from "Site Settings" screen.
		add_action( 'commentpress/core/settings/site/save/before', [ $this, 'settings_meta_box_part_save' ] );

	}

	/**
	 * Registers all other hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks_tagging() {

		// Separated for clarity.
		$this->register_hooks_activation();
		$this->register_hooks_taxonomy();
		$this->register_hooks_admin();
		$this->register_hooks_comments();
		$this->register_hooks_front_end();

	}

	/**
	 * Registers activation/deactivation hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_activation() {

		// Act when this plugin is activated.
		add_action( 'commentpress/core/activate', [ $this, 'plugin_activate' ], 21 );

		// Act when this plugin is deactivated.
		add_action( 'commentpress/core/deactivate', [ $this, 'plugin_deactivate' ], 41 );

	}

	/**
	 * Registers "Comment Taxonomy" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_taxonomy() {

		// Bail if not enabled.
		if ( 'y' !== $this->setting_tagging_get() ) {
			return;
		}

		// Create Taxonomy.
		add_action( 'init', [ $this, 'taxonomy_create' ], 0 );

		// Manage Taxonomy table columns.
		add_filter( 'manage_edit-' . $this->tax_name . '_columns', [ $this, 'taxonomy_comment_column_set' ] );
		add_action( 'manage_' . $this->tax_name . '_custom_column', [ $this, 'taxonomy_comment_column_values' ], 10, 3 );

		// Allow Comment Authors to assign Terms.
		add_filter( 'map_meta_cap', [ $this, 'comment_terms_enable' ], 10, 4 );

	}

	/**
	 * Registers "Comment Taxonomy Admin" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_admin() {

		// Bail if not enabled.
		if ( 'y' !== $this->setting_tagging_get() ) {
			return;
		}

		// Add admin menu item.
		add_action( 'admin_menu', [ $this, 'admin_page_register' ] );

		// Hack the menu parent.
		add_filter( 'parent_file', [ $this, 'admin_parent_menu' ] );

		// Add admin styles.
		add_action( 'admin_enqueue_scripts', [ $this, 'admin_styles_enqueue' ] );

		// Register a meta box for the Edit Comment screen.
		add_action( 'add_meta_boxes', [ $this, 'admin_meta_box_add' ] );

	}

	/**
	 * Registers "Comment Modification" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_comments() {

		// Bail if not enabled.
		if ( 'y' !== $this->setting_tagging_get() ) {
			return;
		}

		// Intercept Comment save process.
		add_action( 'comment_post', [ $this, 'comment_saved' ], 20, 2 );

		// Intercept Edit Comment process in WordPress admin.
		add_action( 'edit_comment', [ $this, 'comment_terms_update' ] );

		// Intercept Edit Comment process in CommentPress front-end.
		add_action( 'edit_comment', [ $this, 'comment_terms_edit' ] );

		// Intercept Delete Comment process.
		add_action( 'delete_comment', [ $this, 'comment_terms_delete' ] );

	}

	/**
	 * Registers "Front End" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_front_end() {

		// Bail if not enabled.
		if ( 'y' !== $this->setting_tagging_get() ) {
			return;
		}

		// Register any public styles.
		add_action( 'wp_enqueue_scripts', [ $this, 'front_end_enqueue_styles' ], 20 );

		// Register any public scripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'front_end_enqueue_scripts' ], 20 );

		// Add tags to Comment content.
		add_filter( 'commentpress_comment_identifier_append', [ $this, 'front_end_tags' ], 10, 2 );

		// Add UI to CommentPress Comments.
		add_action( 'commentpress_comment_form_pre_comment_id_fields', [ $this, 'front_end_markup' ] );

		// Add tag data to AJAX-edit Comment data.
		add_filter( 'commentpress_ajax_get_comment', [ $this, 'front_end_ajax_get_comment_filter' ], 10, 1 );

		// Add tag data to AJAX-edited Comment data.
		add_filter( 'commentpress_ajax_edited_comment', [ $this, 'front_end_ajax_edited_comment_filter' ], 10, 1 );

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
		$settings[ $this->key_tagging ] = 'n';

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
		$tagging = $this->setting_tagging_get();

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-comments-tagging-settings.php';

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
		$tagging = isset( $_POST[ $this->key_tagging ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_tagging ] ) ) : 'n';

		// Set default sidebar.
		$this->setting_tagging_set( $tagging );

		// Try and flush rewrite rules.
		add_action( 'init', 'flush_rewrite_rules' );

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the "Comment Tagging Enabled" setting.
	 *
	 * @since 4.0
	 *
	 * @return str $tagging The setting if found, default otherwise.
	 */
	public function setting_tagging_get() {

		// Get the setting.
		$tagging = $this->core->db->setting_get( $this->key_tagging );

		// Return setting or default if empty.
		return ! empty( $tagging ) ? $tagging : 'n';

	}

	/**
	 * Sets the "Comment Tagging Enabled" setting.
	 *
	 * @since 4.0
	 *
	 * @param str $tagging The setting value.
	 */
	public function setting_tagging_set( $tagging ) {

		// Set the setting.
		$this->core->db->setting_set( $this->key_tagging, $tagging );

	}

	// -------------------------------------------------------------------------

	/**
	 * Runs when core is activated.
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_activate( $network_wide = false ) {

		// Bail if plugin is network activated.
		if ( $network_wide ) {
			return;
		}

		// Flush rules.
		flush_rewrite_rules();

	}

	/**
	 * Runs when core is deactivated.
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_deactivate( $network_wide ) {

		// Bail if plugin is network deactivated.
		if ( $network_wide ) {
			return;
		}

		// Flush rules.
		flush_rewrite_rules();

	}

	// -------------------------------------------------------------------------

	/**
	 * Creates a free-tagging Taxonomy for Comments.
	 *
	 * @since 4.0
	 */
	public function taxonomy_create() {

		// Labels.
		$labels = [
			'name'                       => __( 'Comment Tags', 'commentpress-core' ),
			'singular_name'              => __( 'Comment Tag', 'commentpress-core' ),
			'menu_name'                  => __( 'Comment Tags', 'commentpress-core' ),
			'search_items'               => __( 'Search Comment Tags', 'commentpress-core' ),
			'popular_items'              => __( 'Popular Comment Tags', 'commentpress-core' ),
			'all_items'                  => __( 'All Comment Tags', 'commentpress-core' ),
			'edit_item'                  => __( 'Edit Comment Tag', 'commentpress-core' ),
			'update_item'                => __( 'Update Comment Tag', 'commentpress-core' ),
			'add_new_item'               => __( 'Add New Comment Tag', 'commentpress-core' ),
			'new_item_name'              => __( 'New Comment Tag Name', 'commentpress-core' ),
			'separate_items_with_commas' => __( 'Separate Comment Tags with commas', 'commentpress-core' ),
			'add_or_remove_items'        => __( 'Add or remove Comment Tag', 'commentpress-core' ),
			'choose_from_most_used'      => __( 'Choose from the most popular Comment Tags', 'commentpress-core' ),
		];

		/*
		 * Rewrite Rules.
		 *
		 * Use `'with_front' => true,` to create a tag archive.
		 */
		$rewrite = [
			'slug' => apply_filters( 'comment_tagger_tax_slug', 'comments/tags' ),
		];

		// Capabilities.
		$capabilities = [
			'manage_terms' => 'manage_categories',
			'edit_terms'   => 'manage_categories',
			'delete_terms' => 'manage_categories',
			'assign_terms' => 'assign_' . $this->tax_name,
		];

		// Define Taxonomy arguments.
		$args = [
			'public'                => true,
			'hierarchical'          => false,
			'labels'                => $labels,
			'rewrite'               => $rewrite,
			'capabilities'          => $capabilities,
			// Custom function to update the count.
			'update_count_callback' => [ $this, 'taxonomy_tag_count_update' ],
		];

		// Go ahead and register the Taxonomy now.
		register_taxonomy( $this->tax_name, 'comment', $args );

	}

	/**
	 * Force update the number of Comments for a Taxonomy Term.
	 *
	 * @since 4.0
	 */
	public function taxonomy_tag_count_refresh() {

		// Find all the Term IDs.
		$terms = get_terms( $this->tax_name, [ 'hide_empty' => false ] );
		$tids  = [];
		foreach ( $terms as $term ) {
			$tids[] = $term->term_taxonomy_id;
		}

		// Do update.
		wp_update_term_count_now( $tids, $this->tax_name );

	}

	/**
	 * Manually update the number of Comments for a Taxonomy Term.
	 *
	 * @see _update_post_term_count()
	 *
	 * @since 4.0
	 *
	 * @param array  $terms List of Term Taxonomy IDs.
	 * @param object $taxonomy Current Taxonomy object of Terms.
	 */
	public function taxonomy_tag_count_update( $terms, $taxonomy ) {

		// Access DB wrapper.
		global $wpdb;

		// Loop through each Term.
		foreach ( (array) $terms as $term ) {

			// Get count.
			// phpcs:ignore: WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM $wpdb->term_relationships, $wpdb->comments " .
					"WHERE $wpdb->term_relationships.object_id = $wpdb->comments.comment_ID " .
					"AND $wpdb->term_relationships.term_taxonomy_id = %d",
					$term
				)
			);

			// Fire WordPress action.
			do_action( 'edit_term_taxonomy', $term, $taxonomy );

			// Update database directly.
			// phpcs:ignore: WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->update( $wpdb->term_taxonomy, [ 'count' => $count ], [ 'term_taxonomy_id' => $term ] );

			// Fire WordPress action.
			do_action( 'edited_term_taxonomy', $term, $taxonomy );

		}

	}

	/**
	 * Correct the column name for Comment Taxonomies - replace "Posts" with "Comments".
	 *
	 * @since 4.0
	 *
	 * @param array $columns An array of columns to be shown in the manage Terms table.
	 * @return array $columns Modified array of columns to be shown in the manage Terms table.
	 */
	public function taxonomy_comment_column_set( $columns ) {

		// Replace column.
		unset( $columns['posts'] );
		$columns['comments'] = __( 'Comments', 'commentpress-core' );

		// --<
		return $columns;

	}

	/**
	 * Set values for custom columns in Comment Taxonomies.
	 *
	 * @since 4.0
	 *
	 * @param string $display WP just passes an empty string here.
	 * @param string $column The name of the custom column.
	 * @param int    $term_id The ID of the Term being displayed in the table.
	 */
	public function taxonomy_comment_column_values( $display, $column, $term_id ) {

		if ( 'comments' !== $column ) {
			return;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$taxonomy = isset( $_GET['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) : '';
		if ( empty( $taxonomy ) ) {
			return;
		}

		$term = get_term( $term_id, $taxonomy );
		echo $term->count;

	}

	// -------------------------------------------------------------------------

	/**
	 * Creates the admin menu item for the Taxonomy under the 'Comments' menu.
	 *
	 * @since 4.0
	 */
	public function admin_page_register() {

		// Get Taxonomy object.
		$tax = get_taxonomy( $this->tax_name );

		// Add as subpage of 'Comments' menu item.
		add_comments_page(
			esc_attr( $tax->labels->menu_name ),
			esc_attr( $tax->labels->menu_name ),
			$tax->cap->manage_terms,
			'edit-tags.php?taxonomy=' . $tax->name
		);

	}

	/**
	 * Enqueue CSS in WP admin to tweak the appearance of various elements.
	 *
	 * @since 4.0
	 */
	public function admin_styles_enqueue() {

		global $pagenow;

		// Add basic stylesheet if we're on our Taxonomy Page.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$taxonomy_page = isset( $_GET['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) : '';
		if ( ! empty( $taxonomy_page ) && $taxonomy_page === $this->tax_name && 'edit-tags.php' === $pagenow ) {

			wp_enqueue_style(
				'cp_tagging_css',
				plugins_url( 'includes/core/assets/css/admin-comment-tagging.css', COMMENTPRESS_PLUGIN_FILE ),
				false,
				COMMENTPRESS_VERSION, // Version.
				'all' // Media.
			);

		}

		// The tags meta box requires this script if we're on the "Edit Comment" Page.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
		if ( 'comment.php' === $pagenow && ! empty( $action ) && 'editcomment' === $action ) {
			wp_enqueue_script( 'post' );
		}

	}

	/**
	 * Fix a bug with highlighting the parent menu item.
	 *
	 * By default, when on the edit Taxonomy Page for a user Taxonomy, the "Posts" tab
	 * is highlighted. This will correct that bug.
	 *
	 * @since 4.0
	 *
	 * @param string $parent The existing parent menu item.
	 * @return string $parent The modified parent menu item.
	 */
	public function admin_parent_menu( $parent = '' ) {

		global $pagenow;

		// If we're editing our Comment Taxonomy highlight the Comments menu.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['taxonomy'] ) && $_GET['taxonomy'] == $this->tax_name && 'edit-tags.php' === $pagenow ) {
			$parent = 'edit-comments.php';
		}

		// --<
		return $parent;

	}

	/**
	 * Register a meta box for the Edit Comment screen.
	 *
	 * @since 4.0
	 */
	public function admin_meta_box_add() {

		// Let's use the built-in tags metabox.
		add_meta_box(
			'tagsdiv-post_tag',
			__( 'Comment Tags', 'commentpress-core' ), // Custom name.
			[ $this, 'post_tags_meta_box' ], // Custom callback.
			'comment',
			'normal',
			'default',
			[ 'taxonomy' => $this->tax_name ]
		);

	}

	// -------------------------------------------------------------------------

	/**
	 * Intercept the Save Comment process and maybe update Terms.
	 *
	 * @since 4.0
	 *
	 * @param int $comment_id The numeric ID of the Comment.
	 * @param str $comment_status The status of the Comment.
	 */
	public function comment_saved( $comment_id, $comment_status ) {

		// Bail if we didn't receive any Terms.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['comment_tagger_tags'] ) ) {
			return;
		}

		// Retrieve Terms array.
		$comment_tags = filter_input( INPUT_POST, 'comment_tagger_tags', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( empty( $comment_tags ) ) {
			return;
		}

		// Sanitise Terms array.
		array_walk(
			$comment_tags,
			function( &$item ) {
				$item = sanitize_text_field( $item );
			}
		);

		// Init "existing" and "new" arrays.
		$existing_term_ids = [];
		$new_term_ids      = [];
		$new_terms         = [];

		// Parse the received Terms.
		foreach ( $comment_tags as $term ) {

			// Does the Term contain our prefix?
			if ( strstr( $term, $this->tax_prefix ) ) {

				// It's an existing Term.
				$tmp = explode( '-', $term );

				// Get Term ID.
				$term_id = isset( $tmp[1] ) ? intval( $tmp[1] ) : 0;

				// Add to existing.
				if ( 0 !== $term_id ) {
					$existing_term_ids[] = $term_id;
				}

			} else {

				// Add Term to new.
				$new_terms[] = $term;

			}

		}

		// Get sanitised Term IDs for any *new* Terms.
		if ( count( $new_terms ) > 0 ) {
			$new_term_ids = $this->comment_terms_sanitise( $new_terms );
		}

		// Combine arrays.
		$term_ids = array_unique( array_merge( $existing_term_ids, $new_term_ids ) );

		// Overwrite with new Terms if there are some.
		if ( ! empty( $term_ids ) ) {
			wp_set_object_terms( $comment_id, $term_ids, $this->tax_name, false );
		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Add capability to assign tags.
	 *
	 * @since 4.0
	 *
	 * @param array $caps The existing capabilities array for the WordPress user.
	 * @param str   $cap The capability in question.
	 * @param int   $user_id The numeric ID of the WordPress user.
	 * @param array $args The additional arguments.
	 * @return array $caps The modified capabilities array for the WordPress user.
	 */
	public function comment_terms_enable( $caps, $cap, $user_id, $args ) {

		// Only apply caps to queries for edit_comment cap.
		if ( 'assign_' . $this->tax_name != $cap ) {
			return $caps;
		}

		// Always allow.
		$caps = [ 'exist' ];

		// --<
		return $caps;

	}

	/**
	 * Save data returned by our Comment metabox in WordPress admin.
	 *
	 * @since 4.0
	 *
	 * @param int $comment_id The ID of the Comment being saved.
	 */
	public function comment_terms_update( $comment_id ) {

		// If there's no nonce then there's no Comment meta data.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['_wpnonce'] ) ) {
			return;
		}

		// Get our Taxonomy.
		$tax = get_taxonomy( $this->tax_name );

		// Make sure the user can assign Terms.
		if ( ! current_user_can( $tax->cap->assign_terms ) ) {
			return;
		}

		// Init "existing" and "new" arrays.
		$existing_term_ids = [];
		$new_term_ids      = [];

		// Get sanitised Term IDs for any *existing* Terms.
		if ( isset( $_POST['tax_input'][ $this->tax_name ] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$existing_term_ids = $this->comment_terms_sanitise( wp_unslash( $_POST['tax_input'][ $this->tax_name ] ) );
		}

		// Get sanitised Term IDs for any *new* Terms.
		if ( isset( $_POST['newtag'][ $this->tax_name ] ) ) {
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			$new_term_ids = $this->comment_terms_sanitise( wp_unslash( $_POST['newtag'][ $this->tax_name ] ) );
		}

		// phpcs:enable WordPress.Security.NonceVerification.Missing

		// Combine arrays.
		$term_ids = array_unique( array_merge( $existing_term_ids, $new_term_ids ) );

		// Overwrite with new Terms if there are any.
		if ( ! empty( $term_ids ) ) {
			wp_set_object_terms( $comment_id, $term_ids, $this->tax_name, false );
			clean_object_term_cache( $comment_id, $this->tax_name );
		} else {
			$this->comment_terms_delete( $comment_id );
		}

	}

	/**
	 * Save data returned by our tags select in CommentPress front-end.
	 *
	 * @since 4.0
	 *
	 * @param int $comment_id The ID of the Comment being saved.
	 */
	public function comment_terms_edit( $comment_id ) {

		// If there's no nonce then there's no Comment meta data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['cpajax_comment_nonce'] ) ) {
			return;
		}

		// Get our Taxonomy.
		$tax = get_taxonomy( $this->tax_name );

		// Make sure the user can assign Terms.
		if ( ! current_user_can( $tax->cap->assign_terms ) ) {
			return;
		}

		// Init "existing" and "new" arrays.
		$existing_term_ids = [];
		$new_term_ids      = [];
		$new_terms         = [];

		// Check and sanitise Terms array.
		$comment_tags = filter_input( INPUT_POST, 'comment_tagger_tags', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( ! empty( $comment_tags ) ) {
			array_walk(
				$comment_tags,
				function( &$item ) {
					$item = sanitize_text_field( $item );
				}
			);
		}

		// Sanity check.
		if ( ! empty( $comment_tags ) ) {

			// Parse the received Terms.
			foreach ( $comment_tags as $term ) {

				// Does the Term contain our prefix?
				if ( strstr( $term, $this->tax_prefix ) ) {

					// It's an existing Term.
					$tmp = explode( '-', $term );

					// Get Term ID.
					$term_id = isset( $tmp[1] ) ? intval( $tmp[1] ) : 0;

					// Add to existing.
					if ( 0 !== $term_id ) {
						$existing_term_ids[] = $term_id;
					}

				} else {

					// Add Term to new.
					$new_terms[] = $term;

				}

			}

		}

		// Get sanitised Term IDs for any *new* Terms.
		if ( ! empty( $new_terms ) ) {
			$new_term_ids = $this->comment_terms_sanitise( $new_terms );
		}

		// Combine arrays.
		$term_ids = array_unique( array_merge( $existing_term_ids, $new_term_ids ) );

		// Overwrite with new Terms if there are any.
		if ( ! empty( $term_ids ) ) {
			wp_set_object_terms( $comment_id, $term_ids, $this->tax_name, false );
			clean_object_term_cache( $comment_id, $this->tax_name );
		} else {
			$this->comment_terms_delete( $comment_id );
		}

	}

	/**
	 * Sanitise Comment Terms.
	 *
	 * @since 4.0
	 *
	 * @param mixed $raw_terms The Term names as retrieved from $_POST.
	 * @return array $term_ids The array of numeric Term IDs.
	 */
	private function comment_terms_sanitise( $raw_terms ) {

		// Is this a multi-term Taxonomy?
		if ( is_array( $raw_terms ) ) {

			// Yes, get Terms and validate.
			$terms = array_map( 'esc_attr', $raw_terms );

		} else {

			// We should receive a comma-delimited array of Term names.
			$terms = array_map( 'esc_attr', explode( ',', $raw_terms ) );

		}

		// Init Term IDs.
		$term_ids = [];

		// Loop through them.
		foreach ( $terms as $term ) {

			// Does the Term exist?
			$exists = term_exists( $term, $this->tax_name );

			// If it does.
			if ( 0 !== $exists && null !== $exists ) {

				/*
				 * Should be array e.g. array( 'term_id' => 12, 'term_taxonomy_id' => 34 )
				 * since we specify the Taxonomy.
				 */

				// Add Term ID to array.
				$term_ids[] = $exists['term_id'];

			} else {

				/*
				 * Let's add the Term - but note: return value is either:
				 * WP_Error or array e.g. array( 'term_id' => 12, 'term_taxonomy_id' => 34 )
				 */
				$new_term = wp_insert_term( $term, $this->tax_name );

				/*
				 * Add Term ID to array if there's no error.
				 *
				 * If there was an error somewhere and the Terms couldn't be set
				 * then we should let people know at some point.
				 */
				if ( ! is_wp_error( $new_term ) ) {
					$term_ids[] = $new_term['term_id'];
				}

			}

		}

		// Sanity checks if we have Term IDs.
		if ( ! empty( $term_ids ) ) {
			$term_ids = array_map( 'intval', $term_ids );
			$term_ids = array_unique( $term_ids );
		}

		// --<
		return $term_ids;

	}

	/**
	 * Delete Comment Terms when a Comment is deleted.
	 *
	 * @since 4.0
	 *
	 * @param int $comment_id The ID of the Comment being saved.
	 */
	public function comment_terms_delete( $comment_id ) {

		wp_delete_object_term_relationships( $comment_id, $this->tax_name );
		clean_object_term_cache( $comment_id, $this->tax_name );

	}

	// -------------------------------------------------------------------------

	/**
	 * Show tags on front-end, appended to Comment text.
	 *
	 * @since 4.0
	 *
	 * @param str    $text The content to prepend to the Comment identifer.
	 * @param object $comment The WordPress Comment object.
	 * @return str $text The markup showing the tags for a Comment.
	 */
	public function front_end_tags( $text = '', $comment = null ) {

		// Sanity check.
		if ( ! isset( $comment->comment_ID ) ) {
			return $text;
		}

		// Get Terms for this Comment.
		$terms = wp_get_object_terms( $comment->comment_ID, $this->tax_name );

		// Did we get any?
		if ( count( $terms ) > 0 ) {

			// Init tag list.
			$tag_list = [];

			// Create markup for each.
			foreach ( $terms as $term ) {

				// Get URL.
				$term_href = get_term_link( $term, $this->tax_name );

				// Construct link.
				$term_link = '<a class="comment_tagger_tag_link" href="' . $term_href . '">' . esc_html( $term->name ) . '</a>';

				// Wrap and add to list.
				$tag_list[] = '<span class="comment_tagger_tag">' . $term_link . '</span>';

			}

			// Wrap in identifying div.
			$tags = '<div class="comment_tagger_tags">' .
				'<p>' . __( 'Tagged: ', 'commentpress-core' ) . implode( ' ', $tag_list ) . '</p>' .
			'</div>' . "\n\n";

		} else {

			// Add placeholder div.
			$tags = '<div class="comment_tagger_tags"></div>' . "\n\n";

		}

		// Prepend to text.
		$text = $tags . $text;

		// --<
		return $text;

	}

	/**
	 * Show front-end version of tags metabox.
	 *
	 * @since 4.0
	 */
	public function front_end_markup() {

		// Get content and echo.
		echo $this->front_end_markup_get();

	}

	/**
	 * Show front-end version of tags metabox.
	 *
	 * @since 4.0
	 *
	 * @param str $content The existing content.
	 * @return str $html The markup for the tags metabox.
	 */
	public function front_end_markup_get( $content = '' ) {

		// Only our Taxonomy.
		$taxonomies = [ $this->tax_name ];

		// Config.
		$args = [
			'orderby' => 'count',
			'order'   => 'DESC',
			'number'  => 5,
		];

		// Get top 5 most used tags.
		$tags = get_terms( $taxonomies, $args );

		// Construct default options for Select2.
		$most_used_tags_array = [];
		foreach ( $tags as $tag ) {
			$most_used_tags_array[] = '<option value="' . $this->tax_prefix . '-' . $tag->term_id . '">' .
				esc_html( $tag->name ) .
			'</option>';
		}
		$most_used_tags = implode( "\n", $most_used_tags_array );

		// Use Select2 in "tag" mode.
		$html = '<div class="comment_tagger_select2_container">
			<h5 class="comment_tagger_select2_heading">' . __( 'Tag this comment', 'commentpress-core' ) . '</h5>
			<p class="comment_tagger_select2_description">' .
				__( 'Select from existing tags or add your own.', 'commentpress-core' ) .
				'<br />' .
				__( 'Separate new tags with a comma.', 'commentpress-core' ) .
			'</p>
			<select class="comment_tagger_select2" name="comment_tagger_tags[]" id="comment_tagger_tags" multiple="multiple" style="width: 100%;">
				' . $most_used_tags . '
			</select>
		 </div>';

		// --<
		return $content . $html;

	}

	/**
	 * Add our front-end stylesheets.
	 *
	 * @see https://github.com/select2/select2/tags
	 *
	 * @since 4.0
	 */
	public function front_end_enqueue_styles() {

		// Define our handle.
		$handle = 'cp_tagging_select2_css';

		// Register Select2 styles.
		wp_register_style(
			$handle,
			set_url_scheme( 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css' ),
			null,
			COMMENTPRESS_VERSION // Version.
		);

		// Enqueue styles.
		wp_enqueue_style( $handle );

	}

	/**
	 * Adds our front-end Javascripts.
	 *
	 * @see https://github.com/select2/select2/tags
	 *
	 * @since 4.0
	 */
	public function front_end_enqueue_scripts() {

		// Default to minified scripts.
		$min = commentpress_minified();

		// Define our handle.
		$handle = 'cp_tagging_select2_js';

		// Register Select2.
		wp_register_script(
			$handle,
			set_url_scheme( 'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js' ),
			[ 'jquery' ],
			COMMENTPRESS_VERSION, // Version.
			true // In footer.
		);

		// Enqueue script.
		wp_enqueue_script( $handle );

		// Enqueue our custom Javascript.
		wp_enqueue_script(
			'cp_tagging_js',
			plugins_url( 'includes/core/assets/js/cp-comment-tagging' . $min . '.js', COMMENTPRESS_PLUGIN_FILE ),
			[ $handle ],
			COMMENTPRESS_VERSION,
			false
		);

		// Localisation array.
		$vars = [
			'localisation' => [],
			'data'         => [],
		];

		// Localise with WordPress function.
		wp_localize_script(
			'cp_tagging_js',
			'CommentPress_Comments_Tagging_Settings',
			$vars
		);

	}

	/**
	 * Filters the Comment data returned via AJAX when editing a Comment.
	 *
	 * @since 4.0
	 *
	 * @param array $data The existing array of Comment data.
	 * @return array $data The modified array of Comment data.
	 */
	public function front_end_ajax_get_comment_filter( $data ) {

		// Sanity check.
		if ( ! isset( $data['id'] ) ) {
			return $data;
		}

		// Get Terms for this Comment.
		$terms = wp_get_object_terms( $data['id'], $this->tax_name );

		// Bail if empty.
		if ( count( $terms ) === 0 ) {
			return $data;
		}

		// Build array of simple Term objects.
		$term_ids = [];
		foreach ( $terms as $term ) {
			$obj        = new stdClass();
			$obj->id    = $this->tax_prefix . '-' . $term->term_id;
			$obj->name  = $term->name;
			$term_ids[] = $obj;
		}

		// Add to array.
		$data['comment_tagger_tags'] = $term_ids;

		// --<
		return $data;

	}

	/**
	 * Filters the Comment data returned via AJAX when a Comment has been edited.
	 *
	 * @since 4.0
	 *
	 * @param array $data The existing array of Comment data.
	 * @return array $data The modified array of Comment data.
	 */
	public function front_end_ajax_edited_comment_filter( $data ) {

		// Sanity check.
		if ( ! isset( $data['id'] ) ) {
			return $data;
		}

		// Add tag data.
		$data = $this->front_end_ajax_get_comment_filter( $data );

		// Get Comment.
		$comment = get_comment( $data['id'] );

		// Get markup.
		$markup = $this->front_end_tags( '', $comment );

		// Add to array.
		$data['comment_tagger_markup'] = $markup;

		// --<
		return $data;

	}

	/**
	 * Renders the Tagged Comments for the Comment Tags archive template.
	 *
	 * @since 4.0
	 *
	 * @return str $html The Comments.
	 */
	public function archive_content_get() {

		// Init output.
		$html = '';

		// Get all Comments for this Archive.
		$all_comments = $this->archive_comments_get();
		if ( empty( $all_comments ) ) {
			return $html;
		}

		// Build list of Posts to which they are attached.
		$posts_with          = [];
		$post_comment_counts = [];
		foreach ( $all_comments as $comment ) {

			// Add to Posts with Comments array.
			if ( ! in_array( $comment->comment_post_ID, $posts_with, true ) ) {
				$posts_with[] = $comment->comment_post_ID;
			}

			// Increment counter.
			if ( ! isset( $post_comment_counts[ $comment->comment_post_ID ] ) ) {
				$post_comment_counts[ $comment->comment_post_ID ] = 1;
			} else {
				$post_comment_counts[ $comment->comment_post_ID ]++;
			}

		}

		// Bail if none.
		if ( empty( $posts_with ) ) {
			return $html;
		}

		// Create args.
		$args = [
			'orderby'             => 'comment_count',
			'order'               => 'DESC',
			'post_type'           => 'any',
			'post__in'            => $posts_with,
			'posts_per_page'      => -1,
			'ignore_sticky_posts' => 1,
			'post_status'         => [
				'publish',
				'inherit',
			],
		];

		// Create query.
		$query = new WP_Query( $args );

		// Did we get any?
		if ( $query->have_posts() ) {

			// Open ul.
			$html .= '<ul class="all_comments_listing">' . "\n\n";

			while ( $query->have_posts() ) {

				$query->the_post();

				// Open li.
				$html .= '<li class="page_li"><!-- page li -->' . "\n\n";

				// Define Comment count.
				$comment_count_text = sprintf(
					/* translators: %d: Number of comments. */
					_n(
						// Singular.
						'<span class="cp_comment_count">%d</span> comment',
						// Plural.
						'<span class="cp_comment_count">%d</span> comments',
						// Number.
						$post_comment_counts[ get_the_ID() ],
						// Domain.
						'commentpress-core'
					),
					// Substitution.
					$post_comment_counts[ get_the_ID() ]
				);

				// Show it.
				$html .= '<h4>' . get_the_title() . ' <span>(' . $comment_count_text . ')</span></h4>' . "\n\n";

				// Open Comments div.
				$html .= '<div class="item_body">' . "\n\n";

				// Open ul.
				$html .= '<ul class="item_ul">' . "\n\n";

				// Open li.
				$html .= '<li class="item_li"><!-- item li -->' . "\n\n";

				// Check for password-protected.
				if ( post_password_required( get_the_ID() ) ) {

					// Construct notice.
					$comment_body = '<div class="comment-content">' . __( 'Password protected', 'commentpress-core' ) . '</div>' . "\n";

					// Add notice.
					$html .= '<div class="comment_wrapper">' . "\n" . $comment_body . '</div>' . "\n\n";

				} else {

					foreach ( $all_comments as $comment ) {

						// Maybe show the Comment.
						if ( (int) get_the_ID() === (int) $comment->comment_post_ID ) {
							$html .= commentpress_format_comment( $comment );
						}

					}

				}

				// Close li.
				$html .= '</li><!-- /item li -->' . "\n\n";

				// Close ul.
				$html .= '</ul>' . "\n\n";

				// Close Comments div.
				$html .= '</div><!-- /item_body -->' . "\n\n";

				// Close li.
				$html .= '</li><!-- /page li -->' . "\n\n\n\n";

			}

			// Close ul.
			$html .= '</ul><!-- /all_comments_listing -->' . "\n\n";

			// Reset.
			wp_reset_postdata();

		}

		// --<
		return $html;

	}

	/**
	 * Gets all Comments with the queried Tag for the Comment Tags archive.
	 *
	 * @since 4.0
	 *
	 * @return array $comments The Comments.
	 */
	private function archive_comments_get() {

		// Init return.
		$comments = [];

		// Get queried data.
		$comment_term_id = get_queried_object_id();
		$comment_term    = get_queried_object();

		// Get Comment IDs.
		$tagged_comments = get_objects_in_term( $comment_term_id, $comment_term->taxonomy );
		if ( empty( $tagged_comments ) ) {
			return $comments;
		}

		// Create custom query.
		$comments_query = new WP_Comment_Query();

		// Define args.
		$args = [
			'comment__in' => $tagged_comments,
			'status'      => 'approve',
			'orderby'     => 'comment_post_ID,comment_date',
			'order'       => 'ASC',
		];

		/**
		 * Filters the Comment Query arguments.
		 *
		 * @since 4.0
		 *
		 * @param array $args The Comment Query arguments.
		 */
		$args = apply_filters( 'commentpress/comments/tagging/archive/query/args', $args );

		// Do the query.
		$comments = $comments_query->query( $args );

		// Also enqueue "All Comments" accordion Javascript.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_accordion' ] );

		// --<
		return $comments;

	}

	/**
	 * Enqueue accordion script.
	 *
	 * @since 4.0
	 */
	public function enqueue_accordion() {

		// Enqueue accordion-like Javascript.
		wp_enqueue_script(
			'cp_special',
			get_template_directory_uri() . '/assets/js/all-comments.js',
			[ 'jquery' ], // Dependencies.
			COMMENTPRESS_VERSION, // Version.
			false
		);

	}

	/**
	 * Renders the Comment Tags meta box.
	 *
	 * This is a clone of `post_tags_meta_box` which is usually used to display post
	 * tags form fields. It has been modified so that the Terms are assigned to the
	 * Comment not the Post. The capability check has also been changed to see if a
	 * user can edit the Comment - this may be changed to assign custom capabilities
	 * to the Taxonomy itself and then use the 'map_meta_caps' filter to make the
	 * decision.
	 *
	 * NB: there's a to-do note on the original that suggests that it should be made
	 * more compatible with general Taxonomies...
	 *
	 * @todo Create taxonomy-agnostic wrapper for this.
	 *
	 * @see post_tags_meta_box
	 *
	 * @since 4.0
	 *
	 * @param WP_Post $post The Post object.
	 * @param array   $box {
	 *     Tags meta box arguments.
	 *
	 *     @type string   $id       Meta box ID.
	 *     @type string   $title    Meta box title.
	 *     @type callback $callback Meta box display callback.
	 *     @type array    $args {
	 *         Extra meta box arguments.
	 *
	 *         @type string $taxonomy Taxonomy. Default 'post_tag'.
	 *     }
	 * }
	 */
	public function post_tags_meta_box( $post, $box ) {

		// Access Comment.
		global $comment;

		// Parse the passed in arguments.
		$defaults = [ 'taxonomy' => 'post_tag' ];
		if ( ! isset( $box['args'] ) || ! is_array( $box['args'] ) ) {
			$args = [];
		} else {
			$args = $box['args'];
		}
		$parsed_args = wp_parse_args( $args, $defaults );

		// Get Taxonomy data.
		$tax_name              = esc_attr( $parsed_args['taxonomy'] );
		$taxonomy              = get_taxonomy( $parsed_args['taxonomy'] );
		$user_can_assign_terms = current_user_can( $taxonomy->cap->assign_terms );
		$comma                 = _x( ',', 'tag delimiter', 'commentpress-core' );
		$terms_to_edit         = get_terms_to_edit( $post->ID, $tax_name );
		if ( ! is_string( $terms_to_edit ) ) {
			$terms_to_edit = '';
		}

		?>
		<div class="tagsdiv" id="<?php echo $tax_name; ?>">
			<div class="jaxtag">
			<div class="nojs-tags hide-if-js">
				<label for="tax-input-<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->add_or_remove_items; ?></label>
				<p><textarea name="<?php echo "tax_input[$tax_name]"; ?>" rows="3" cols="20" class="the-tags" id="tax-input-<?php echo $tax_name; ?>" <?php disabled( ! $user_can_assign_terms ); ?> aria-describedby="new-tag-<?php echo $tax_name; ?>-desc"><?php echo str_replace( ',', $comma . ' ', get_terms_to_edit( $comment->comment_ID, $tax_name ) ); /* textarea_escaped by esc_attr() */ ?></textarea></p>
			</div>
			<?php if ( $user_can_assign_terms ) : ?>
				<div class="ajaxtag hide-if-no-js">
					<label class="screen-reader-text" for="new-tag-<?php echo $tax_name; ?>"><?php echo $taxonomy->labels->add_new_item; ?></label>
					<input data-wp-taxonomy="<?php echo $tax_name; ?>" type="text" id="new-tag-<?php echo $tax_name; ?>" name="newtag[<?php echo $tax_name; ?>]" class="newtag form-input-tip" size="16" autocomplete="off" aria-describedby="new-tag-<?php echo $tax_name; ?>-desc" value="" />
					<input type="button" class="button tagadd" value="<?php esc_attr_e( 'Add', 'commentpress-core' ); ?>" />
				</div>
				<p class="howto" id="new-tag-<?php echo $tax_name; ?>-desc"><?php echo $taxonomy->labels->separate_items_with_commas; ?></p>
			<?php elseif ( empty( $terms_to_edit ) ) : ?>
				<p><?php echo $taxonomy->labels->no_terms; ?></p>
			<?php endif; ?>
			</div>
			<ul class="tagchecklist" role="list"></ul>
		</div>

		<?php if ( $user_can_assign_terms ) : ?>
			<p class="hide-if-no-js"><button type="button" class="button-link tagcloud-link" id="link-<?php echo $tax_name; ?>" aria-expanded="false"><?php echo $taxonomy->labels->choose_from_most_used; ?></button></p>
		<?php endif; ?>

		<?php

	}

}
