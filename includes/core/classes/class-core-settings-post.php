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
		add_action( 'add_meta_boxes', [ $this, 'metabox_add' ], 20, 2 );

		// Intercept save.
		add_action( 'save_post', [ $this, 'save_post' ], 10, 2 );

		// Intercept delete.
		add_action( 'before_delete_post', [ $this, 'delete_post' ], 10, 1 );

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

		// Bail if not one of our supported Post Types.
		if ( ! in_array( $post_type, $this->post_types ) ) {
			return;
		}

		// Add our metabox.
		add_meta_box(
			'commentpress_post_settings',
			__( 'CommentPress Settings', 'commentpress-core' ),
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

		// Choose internal method.
		if ( $post->post_type === 'page' ) {
			$this->metabox_render_page( $post );
		} elseif ( $post->post_type === 'post' ) {
			$this->metabox_render_post( $post );
		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds meta box to "Edit Page" screens.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed and moved to this class.
	 *
	 * @param WP_Post $post The Post object.
	 */
	public function metabox_render_page( $post ) {

		// Use nonce for verification.
		wp_nonce_field( 'commentpress_page_settings', 'commentpress_nonce' );

		// ---------------------------------------------------------------------
		// Show or Hide Page Title.
		// ---------------------------------------------------------------------

		// Show a title.
		echo '<div class="cp_title_visibility_wrapper">
		<p><strong><label for="cp_title_visibility">' . __( 'Page Title Visibility', 'commentpress-core' ) . '</label></strong></p>';

		// Set key.
		$key = '_cp_title_visibility';

		// Default to show.
		$viz = $this->core->db->option_get( 'cp_title_visibility' );

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Get it.
			$viz = get_post_meta( $post->ID, $key, true );

		}

		// Select.
		echo '
		<p>
		<select id="cp_title_visibility" name="cp_title_visibility">
			<option value="show" ' . ( ( $viz == 'show' ) ? ' selected="selected"' : '' ) . '>' . __( 'Show page title', 'commentpress-core' ) . '</option>
			<option value="hide" ' . ( ( $viz == 'hide' ) ? ' selected="selected"' : '' ) . '>' . __( 'Hide page title', 'commentpress-core' ) . '</option>
		</select>
		</p>
		</div>
		';

		// ---------------------------------------------------------------------
		// Show or Hide Page Meta.
		// ---------------------------------------------------------------------

		// Show a label.
		echo '<div class="cp_page_meta_visibility_wrapper">
		<p><strong><label for="cp_page_meta_visibility">' . __( 'Page Meta Visibility', 'commentpress-core' ) . '</label></strong></p>';

		// Set key.
		$key = '_cp_page_meta_visibility';

		// Default to show.
		$viz = $this->core->db->option_get( 'cp_page_meta_visibility' );

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Get it.
			$viz = get_post_meta( $post->ID, $key, true );

		}

		// Select.
		echo '
		<p>
		<select id="cp_page_meta_visibility" name="cp_page_meta_visibility">
			<option value="show" ' . ( ( $viz == 'show' ) ? ' selected="selected"' : '' ) . '>' . __( 'Show page meta', 'commentpress-core' ) . '</option>
			<option value="hide" ' . ( ( $viz == 'hide' ) ? ' selected="selected"' : '' ) . '>' . __( 'Hide page meta', 'commentpress-core' ) . '</option>
		</select>
		</p>
		</div>
		';

		// ---------------------------------------------------------------------
		// Page Numbering - only shown on first top level Page.
		// ---------------------------------------------------------------------

		// If Page has no parent and it's not a Special Page and it's the first.
		if (
			$post->post_parent == '0' &&
			! $this->core->pages_legacy->is_special_page() &&
			$post->ID == $this->core->nav->get_first_page()
		) {

			// Label.
			echo '<div class="cp_number_format_wrapper">
			<p><strong><label for="cp_number_format">' . __( 'Page Number Format', 'commentpress-core' ) . '</label></strong></p>';

			// Set key.
			$key = '_cp_number_format';

			// Default to arabic.
			$format = 'arabic';

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// Get it.
				$format = get_post_meta( $post->ID, $key, true );

			}

			// Select.
			echo '
			<p>
			<select id="cp_number_format" name="cp_number_format">
				<option value="arabic" ' . ( ( $format == 'arabic' ) ? ' selected="selected"' : '' ) . '>' . __( 'Arabic numerals', 'commentpress-core' ) . '</option>
				<option value="roman" ' . ( ( $format == 'roman' ) ? ' selected="selected"' : '' ) . '>' . __( 'Roman numerals', 'commentpress-core' ) . '</option>
			</select>
			</p>
			</div>
			';

		}

		// ---------------------------------------------------------------------
		// Page Layout for Title Page -> to allow for Book Cover image.
		// ---------------------------------------------------------------------

		// Is this the Title Page?
		if ( $post->ID == $this->core->db->option_get( 'cp_welcome_page' ) ) {

			// Label.
			echo '<div class="cp_page_layout_wrapper">
			<p><strong><label for="cp_page_layout">' . __( 'Page Layout', 'commentpress-core' ) . '</label></strong></p>';

			// Set key.
			$key = '_cp_page_layout';

			// Default to text.
			$value = 'text';

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// Get it.
				$value = get_post_meta( $post->ID, $key, true );

			}

			// Select.
			echo '
			<p>
			<select id="cp_page_layout" name="cp_page_layout">
				<option value="text" ' . ( ( $value == 'text' ) ? ' selected="selected"' : '' ) . '>' . __( 'Standard', 'commentpress-core' ) . '</option>
				<option value="wide" ' . ( ( $value == 'wide' ) ? ' selected="selected"' : '' ) . '>' . __( 'Wide', 'commentpress-core' ) . '</option>
			</select>
			</p>
			</div>
			';

		}

		// Get default sidebar.
		$this->get_default_sidebar_metabox( $post );

		// Get starting para number.
		$this->get_para_numbering_metabox( $post );

	}

	/**
	 * Adds the default sidebar preference to the Page/Post metabox.
	 *
	 * @since 3.4
	 *
	 * @param object $post The WordPress Post object.
	 */
	public function get_default_sidebar_metabox( $post ) {

		// Allow this to be disabled.
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) {
			return;
		}

		// ---------------------------------------------------------------------
		// Override Post Formatter.
		// ---------------------------------------------------------------------

		// Do we have the option to choose the default sidebar (new in 3.3.3)?
		if ( $this->core->db->option_exists( 'cp_sidebar_default' ) ) {

			// Show a title.
			echo '<div class="cp_sidebar_default_wrapper">
			<p><strong><label for="cp_sidebar_default">' . __( 'Default Sidebar', 'commentpress-core' ) . '</label></strong></p>';

			// Set key.
			$key = '_cp_sidebar_default';

			// Default to show.
			$sidebar = $this->core->db->option_get( 'cp_sidebar_default' );

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// Get it.
				$sidebar = get_post_meta( $post->ID, $key, true );

			}

			// Select.
			echo '
			<p>
			<select id="cp_sidebar_default" name="cp_sidebar_default">
				<option value="toc" ' . ( ( $sidebar == 'toc' ) ? ' selected="selected"' : '' ) . '>' . __( 'Contents', 'commentpress-core' ) . '</option>
				<option value="activity" ' . ( ( $sidebar == 'activity' ) ? ' selected="selected"' : '' ) . '>' . __( 'Activity', 'commentpress-core' ) . '</option>
				<option value="comments" ' . ( ( $sidebar == 'comments' ) ? ' selected="selected"' : '' ) . '>' . __( 'Comments', 'commentpress-core' ) . '</option>
			</select>
			</p>
			</div>
			';

		}

	}

	/**
	 * Adds the Paragraph numbering preference to the Page/Post metabox.
	 *
	 * @since 3.4
	 *
	 * @param object $post The WordPress Post object.
	 */
	public function get_para_numbering_metabox( $post ) {

		// Show a title.
		echo '<div class="cp_starting_para_number_wrapper">
		<p><strong><label for="cp_starting_para_number">' . __( 'Starting Paragraph Number', 'commentpress-core' ) . '</label></strong></p>';

		// Set key.
		$key = '_cp_starting_para_number';

		// Default to start with para 1.
		$num = 1;

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Get it.
			$num = get_post_meta( $post->ID, $key, true );

		}

		// Select.
		echo '
		<p>
		<input type="text" id="cp_starting_para_number" name="cp_starting_para_number" value="' . $num . '" />
		</p>
		</div>
		';

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds meta box to "Edit Post" screens.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed and moved to this class.
	 *
	 * @param WP_Post $post The Post object.
	 */
	public function metabox_render_post( $post ) {

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

		// If no Post, kick out.
		if ( ! $post ) {
			return;
		}

		// If Page.
		if ( $post->post_type == 'page' ) {
			$this->save_page_meta( $post );
		}

		// If Post.
		if ( $post->post_type == 'post' ) {
			$this->save_post_meta( $post );
		}

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
		$result = $this->delete_meta( $post_id );

	}

	// -------------------------------------------------------------------------

	/**
	 * When a Post is saved, this authenticates that our options can be saved.
	 *
	 * @since 3.4
	 *
	 * @param object $post_obj The Post object.
	 */
	public function save_post_meta_authenticated( $post_obj ) {

		// If no Post, kick out.
		if ( ! $post_obj ) {
			return false;
		}

		// If not Page, kick out.
		if ( $post_obj->post_type != 'post' ) {
			return false;
		}

		// Authenticate.
		$nonce = isset( $_POST['commentpress_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commentpress_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'commentpress_post_settings' ) ) {
			return false;
		}

		// Is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		// Check permissions - 'edit_posts' is available to contributor+.
		if ( ! current_user_can( 'edit_posts', $post_obj->ID ) ) {
			return false;
		}

		// Good to go.
		return true;

	}

	/**
	 * When a Page is saved, this authenticates that our options can be saved.
	 *
	 * @since 3.4
	 *
	 * @param object $post_obj The Post object.
	 */
	public function save_page_meta_authenticated( $post_obj ) {

		// If no Post, kick out.
		if ( ! $post_obj ) {
			return false;
		}

		// If not Page, kick out.
		if ( $post_obj->post_type != 'page' ) {
			return false;
		}

		// Authenticate.
		$nonce = isset( $_POST['commentpress_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commentpress_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'commentpress_page_settings' ) ) {
			return false;
		}

		// Is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return false;
		}

		// Check permissions - 'edit_pages' is available to editor or greater.
		if ( ! current_user_can( 'edit_pages' ) ) {
			return false;
		}

		// Good to go.
		return true;

	}

	// -------------------------------------------------------------------------

	/**
	 * When a Post is saved, this also saves the CommentPress Core options.
	 *
	 * @since 3.0
	 *
	 * @param object $post_obj The Post object.
	 */
	public function save_post_meta( $post_obj ) {

		// Bail if we're not authenticated.
		if ( ! $this->save_post_meta_authenticated( $post_obj ) ) {
			return;
		}

		// Check for revision.
		if ( $post_obj->post_type == 'revision' ) {

			// Get parent.
			if ( $post_obj->post_parent != 0 ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// Save default sidebar.
		$this->save_default_sidebar( $post );

		/**
		 * Broadcast that Post meta has been saved.
		 *
		 * @since 4.0
		 *
		 * @param object $post The WordPress Post object.
		 */
		do_action( 'commentpress/core/db/post_meta/saved', $post );

	}

	/**
	 * When a Page is saved, this also saves the CommentPress Core options.
	 *
	 * @since 3.4
	 *
	 * @param object $post_obj The Post object.
	 */
	public function save_page_meta( $post_obj ) {

		// Bail if we're not authenticated.
		if ( ! $this->save_page_meta_authenticated( $post_obj ) ) {
			return;
		}

		// Check for revision.
		if ( $post_obj->post_type == 'revision' ) {

			// Get parent.
			if ( $post_obj->post_parent != 0 ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}

		} else {
			$post = $post_obj;
		}

		// Save Page title visibility.
		$this->save_page_title_visibility( $post );

		// Save Page meta visibility.
		$this->save_page_meta_visibility( $post );

		// Save Page numbering.
		$this->save_page_numbering( $post );

		// Save Page layout for Title Page.
		$this->save_page_layout( $post );

		// Save default sidebar.
		$this->save_default_sidebar( $post );

		// Save starting Paragraph Number.
		$this->save_starting_paragraph( $post );

		/**
		 * Broadcast that Page meta has been saved.
		 *
		 * @since 4.0
		 *
		 * @param object $post The WordPress Post object.
		 */
		do_action( 'commentpress/core/db/page_meta/saved', $post );

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
	public function save_page_title_visibility( $post ) {

		// Find and save the data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$data = isset( $_POST['cp_title_visibility'] ) ? sanitize_text_field( wp_unslash( $_POST['cp_title_visibility'] ) ) : 'show';

		// Set key.
		$key = '_cp_title_visibility';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Delete the meta_key if empty string.
			if ( $data === '' ) {
				delete_post_meta( $post->ID, $key );
			} else {
				update_post_meta( $post->ID, $key, esc_sql( $data ) );
			}

		} else {

			// Add the data.
			add_post_meta( $post->ID, $key, esc_sql( $data ) );

		}

		// --<
		return $data;

	}

	/**
	 * Save Page Meta visibility.
	 *
	 * @since 3.4
	 *
	 * @param object $post The Post object.
	 * @return string $data Either 'hide' (default) or ''.
	 */
	public function save_page_meta_visibility( $post ) {

		// Find and save the data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$data = isset( $_POST['cp_page_meta_visibility'] ) ? sanitize_text_field( wp_unslash( $_POST['cp_page_meta_visibility'] ) ) : 'hide';

		// Set key.
		$key = '_cp_page_meta_visibility';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Delete the meta_key if empty string.
			if ( $data === '' ) {
				delete_post_meta( $post->ID, $key );
			} else {
				update_post_meta( $post->ID, $key, esc_sql( $data ) );
			}

		} else {

			// Add the data.
			add_post_meta( $post->ID, $key, esc_sql( $data ) );

		}

		// --<
		return $data;

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
	public function save_page_numbering( $post ) {

		// Bail if no value received.
		if ( ! isset( $_POST['cp_number_format'] ) ) {
			return;
		}

		// Set meta key.
		$key = '_cp_number_format';

		// Do we need to check this, since only the first top level Page
		// can now send this data? doesn't hurt to validate, I guess.
		if (
			$post->post_parent == '0' &&
			! $this->core->pages_legacy->is_special_page() &&
			$post->ID == $this->core->nav->get_first_page()
		) {

			// Get the data.
			$data = sanitize_text_field( wp_unslash( $_POST['cp_number_format'] ) );

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// If empty string.
				if ( $data === '' ) {

					// Delete the meta_key.
					delete_post_meta( $post->ID, $key );

				} else {

					// Update the data.
					update_post_meta( $post->ID, $key, esc_sql( $data ) );

				}

			} else {

				// Add the data.
				add_post_meta( $post->ID, $key, esc_sql( $data ) );

			}

		}

		/*
		 * Delete this meta value from all other Pages, because we may have altered
		 * the relationship between Pages, thus causing the Page numbering to fail.
		 */

		// Get all Pages including Chapters.
		$all_pages = $this->core->nav->get_book_pages( 'structural' );

		// If we have any Pages.
		if ( count( $all_pages ) > 0 ) {

			// Loop.
			foreach ( $all_pages as $page ) {

				// Exclude first top level Page.
				if ( $post->ID != $page->ID ) {

					// Delete the meta value.
					delete_post_meta( $page->ID, $key );

				}

			}

		}

	}

	/**
	 * Save Page Layout for Title Page -> to allow for Book Cover image.
	 *
	 * @since 3.0
	 *
	 * @param object $post The Post object.
	 */
	public function save_page_layout( $post ) {

		// Bail if this is not the Title Page.
		if ( $post->ID !== (int) $this->core->db->option_get( 'cp_welcome_page' ) ) {
			return;
		}

		// Find and save the data.
		$data = isset( $_POST['cp_page_layout'] ) ? sanitize_text_field( wp_unslash( $_POST['cp_page_layout'] ) ) : 'text';

		// Set key.
		$key = '_cp_page_layout';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Delete the meta_key if empty string.
			if ( $data === '' ) {
				delete_post_meta( $post->ID, $key );
			} else {
				update_post_meta( $post->ID, $key, esc_sql( $data ) );
			}

		} else {

			// Add the data.
			add_post_meta( $post->ID, $key, esc_sql( $data ) );

		}

	}

	/**
	 * Override default sidebar.
	 *
	 * @since 3.4
	 *
	 * @param object $post The Post object.
	 */
	public function save_default_sidebar( $post ) {

		// Allow this to be disabled.
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) {
			return;
		}

		// Bail if we do have the option to choose the default sidebar (new in 3.3.3).
		if ( ! $this->core->db->option_exists( 'cp_sidebar_default' ) ) {
			return;
		}

		// Find and save the data.
		$data = ( isset( $_POST['cp_sidebar_default'] ) ) ?
			sanitize_text_field( wp_unslash( $_POST['cp_sidebar_default'] ) ) :
			$this->core->db->option_get( 'cp_sidebar_default' );

		// Set key.
		$key = '_cp_sidebar_default';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Delete the meta_key if empty string.
			if ( $data === '' ) {
				delete_post_meta( $post->ID, $key );
			} else {
				update_post_meta( $post->ID, $key, esc_sql( $data ) );
			}

		} else {

			// Add the data.
			add_post_meta( $post->ID, $key, esc_sql( $data ) );

		}

	}

	/**
	 * Starting Paragraph Number - meta only exists when not default value.
	 *
	 * @since 3.4
	 *
	 * @param object $post The Post object.
	 */
	public function save_starting_paragraph( $post ) {

		// Get the data.
		$data = isset( $_POST['cp_starting_para_number'] ) ? sanitize_text_field( wp_unslash( $_POST['cp_starting_para_number'] ) ) : 1;

		// If not numeric, set to default.
		if ( ! is_numeric( $data ) ) {
			$data = 1;
		}

		// Sanitize it.
		$data = absint( $data );

		// Set key.
		$key = '_cp_starting_para_number';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Delete if default.
			if ( $data === 1 ) {
				delete_post_meta( $post->ID, $key );
			} else {
				update_post_meta( $post->ID, $key, esc_sql( $data ) );
			}

		} else {

			// Add the data if greater than default.
			if ( $data > 1 ) {
				add_post_meta( $post->ID, $key, esc_sql( $data ) );
			}

		}

	}

	/**
	 * When a Page is deleted, this makes sure that the CommentPress Core options are synced.
	 *
	 * @since 3.4
	 *
	 * @param object $post_id The Post ID.
	 */
	public function delete_meta( $post_id ) {

		// If no Post, kick out.
		if ( ! $post_id ) {
			return;
		}

		// If it's our Welcome Page.
		if ( $post_id == $this->core->db->option_get( 'cp_welcome_page' ) ) {

			// Delete option.
			$this->option_delete( 'cp_welcome_page' );

			// Save.
			$this->options_save();

		}

	}

}
