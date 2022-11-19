<?php
/**
 * CommentPress Core Workflow class.
 *
 * Handles "Translation" Workflow in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Workflow Class.
 *
 * This class provides "Translation" Workflow to CommentPress Core.
 *
 * @since 3.0
 */
class CommentPress_Core_Workflow {

	/**
	 * Core loader object.
	 *
	 * @since 3.0
	 * @since 4.0 Renamed.
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * Constructor.
	 *
	 * @since 3.0
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
	 * @since 3.0
	 */
	public function initialise() {

		// Register hooks.
		$this->register_hooks();

	}

	// -------------------------------------------------------------------------

	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.0
	 */
	public function register_hooks() {

		// Enable Workflow.
		add_filter( 'cp_blog_workflow_exists', [ $this, 'blog_workflow_exists' ], 21 );

		// Override label.
		add_filter( 'cp_blog_workflow_label', [ $this, 'blog_workflow_label' ], 21 );

		// Override Blog Type if Workflow is on.
		add_filter( 'cp_get_group_meta_for_blog_type', [ $this, 'group_meta_set_blog_type' ], 21, 2 );

		// Is this the back end?
		if ( is_admin() ) {

			// Add meta box for Translation Workflow.
			add_action( 'cp_workflow_metabox', [ $this, 'workflow_metabox' ], 10, 2 );

			// Override meta box title for Translation Workflow.
			add_filter( 'cp_workflow_metabox_title', [ $this, 'workflow_metabox_title' ], 21, 1 );

			// Save Post with Translation Workflow.
			add_action( 'cp_workflow_save_post', [ $this, 'workflow_save_post' ], 21, 1 );

			// Save Page with Translation Workflow.
			add_action( 'cp_workflow_save_page', [ $this, 'workflow_save_post' ], 21, 1 );

			// Save Translation Workflow for copied Posts.
			add_action( 'commentpress/core/revisions/revision/created', [ $this, 'workflow_save_copy' ], 21, 1 );

		}

		// Save Workflow meta.
		add_action( 'commentpress/core/db/page_meta/saved', [ $this, 'save_workflow' ] );
		add_action( 'commentpress/core/db/post_meta/saved', [ $this, 'save_workflow' ] );

		// Create custom filters that mirror 'the_content'.
		add_filter( 'cp_workflow_richtext_content', 'wptexturize' );
		add_filter( 'cp_workflow_richtext_content', 'convert_smilies', 20 );
		add_filter( 'cp_workflow_richtext_content', 'convert_chars' );
		add_filter( 'cp_workflow_richtext_content', 'wpautop' );
		add_filter( 'cp_workflow_richtext_content', 'shortcode_unautop' );
		add_filter( 'cp_workflow_richtext_content', 'prepend_attachment' );

		/*
		// Introduced since WordPress 5.5.
		add_filter( 'cp_workflow_richtext_content', 'wp_filter_content_tags' );
		add_filter( 'cp_workflow_richtext_content', 'wp_replace_insecure_home_url' );
		*/

	}

	/**
	 * Enable Workflow.
	 *
	 * @since 3.0
	 *
	 * @param bool $exists True if "workflow" is enabled, false otherwise.
	 * @return bool $exists True if "workflow" is enabled, false otherwise.
	 */
	public function blog_workflow_exists( $exists ) {

		// Switch on, but allow overrides.
		return apply_filters(
			'cp_class_commentpress_workflow_enabled',
			true
		);

	}

	/**
	 * Override the name of the Workflow checkbox label.
	 *
	 * @since 3.0
	 *
	 * @param str $name The existing singular name of the label.
	 * @return str $name The modified singular name of the label.
	 */
	public function blog_workflow_label( $name ) {

		// Set label, but allow overrides.
		return apply_filters(
			'cp_class_commentpress_workflow_label',
			__( 'Enable Translation Workflow', 'commentpress-core' )
		);

	}

	/**
	 * Amend the Group meta if Workflow is enabled.
	 *
	 * @since 3.0
	 *
	 * @param str $blog_type The existing numerical type of the Blog.
	 * @param int|bool $blog_workflow A positive number if Workflow enabled, false otherwise.
	 * @return str $blog_type The modified numerical type of the Blog.
	 */
	public function group_meta_set_blog_type( $blog_type, $blog_workflow ) {

		// If the Blog Workflow is enabled, then this is a Translation Group.
		if ( $blog_workflow == '1' ) {

			// Translation is type 2.
			$blog_type = '2';

		}

		/**
		 * Allow plugins to override the Blog Type - for example if Workflow
		 * is enabled, it might become a new Blog Type as far as BuddyPress
		 * is concerned.
		 *
		 * @since 3.0
		 *
		 * @param int $blog_type The numeric Blog Type.
		 * @param int|bool $blog_workflow A positive number if Workflow enabled, false otherwise.
		 */
		return apply_filters( 'cp_class_commentpress_workflow_group_blogtype', $blog_type, $blog_workflow );

	}

	/**
	 * Add our metabox if Workflow is enabled.
	 *
	 * @since 3.0
	 */
	public function workflow_metabox() {

		global $post;

		// Use nonce for verification.
		wp_nonce_field( 'commentpress_post_workflow_settings', 'commentpress_workflow_nonce' );

		// Label.
		echo '<h3>' . apply_filters(
			'commentpress_original_title',
			__( 'Original Text', 'commentpress-core' )
		) . '</h3>';

		// Set key.
		$key = '_cp_original_text';

		// Get content.
		$content = get_post_meta( $post->ID, $key, true );

		// Set editor ID (sucks that it can't use - and _).
		$editor_id = 'cporiginaltext';

		// Call the editor.
		wp_editor(
			esc_html( stripslashes( $content ) ),
			$editor_id,
			$settings = [
				'media_buttons' => false,
			]
		);

		// Label.
		echo '<h3>' . apply_filters(
			'commentpress_literal_title',
			__( 'Literal Translation', 'commentpress-core' )
		) . '</h3>';

		// Set key.
		$key = '_cp_literal_translation';

		// Get content.
		$content = get_post_meta( $post->ID, $key, true );

		// Set editor ID (sucks that it can't use - and _).
		$editor_id = 'cpliteraltranslation';

		// Call the editor.
		wp_editor(
			esc_html( stripslashes( $content ) ),
			$editor_id,
			$settings = [
				'media_buttons' => false,
			]
		);

	}

	/**
	 * Amend the Workflow metabox title.
	 *
	 * @since 3.0
	 *
	 * @param str $title The existing title of the metabox.
	 * @return str $title The overridden title of the metabox.
	 */
	public function workflow_metabox_title( $title ) {

		// Set label, but allow overrides.
		return apply_filters(
			'cp_class_commentpress_workflow_metabox_title',
			__( 'Translations', 'commentpress-core' )
		);

	}

	/**
	 * Save Workflow data based on the state of the metabox.
	 *
	 * @since 3.0
	 *
	 * @param object $post_obj The WordPress Post object.
	 */
	public function workflow_save_post( $post_obj ) {

		// If no Post, kick out.
		if ( ! $post_obj ) {
			return;
		}

		// If not Post or Page, kick out.
		$types = [ 'post', 'page' ];
		if ( ! in_array( $post_obj->post_type, $types ) ) {
			return;
		}

		// Authenticate.
		$nonce = isset( $_POST['commentpress_workflow_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['commentpress_workflow_nonce'] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, 'commentpress_post_workflow_settings' ) ) {
			return;
		}

		// Is this an auto save routine?
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// Check permissions.
		if ( $post_obj->post_type == 'post' && ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		if ( $post_obj->post_type == 'page' && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// OK, we're authenticated.

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

		// ---------------------------------------------------------------------
		// Save the content of the two wp_editors.
		// ---------------------------------------------------------------------

		// Get original text.
		$original = isset( $_POST['cporiginaltext'] ) ? trim( wp_unslash( $_POST['cporiginaltext'] ) ) : '';

		// Set key.
		$key = '_cp_original_text';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// If empty string.
			if ( $original === '' ) {

				// Delete the meta_key.
				delete_post_meta( $post->ID, $key );

			} else {

				// Update the data.
				update_post_meta( $post->ID, $key, $original );

			}

		} else {

			// Only add meta if we have field data.
			if ( $original !== '' ) {

				// Add the data.
				add_post_meta( $post->ID, $key, $original, true );

			}

		}

		// Get literal translation.
		$literal = isset( $_POST['cpliteraltranslation'] ) ? trim( wp_unslash( $_POST['cpliteraltranslation'] ) ) : '';

		// Set key.
		$key = '_cp_literal_translation';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// If empty string.
			if ( $literal === '' ) {

				// Delete the meta_key.
				delete_post_meta( $post->ID, $key );

			} else {

				// Update the data.
				update_post_meta( $post->ID, $key, $literal );

			}

		} else {

			// Only add meta if we have field data.
			if ( $literal !== '' ) {

				// Add the data.
				add_post_meta( $post->ID, $key, $literal, true );

			}

		}

	}

	/**
	 * Add the Workflow content to the new version.
	 *
	 * @since 3.0
	 *
	 * @param int $new_post_id The numeric ID of the new WordPress Post.
	 */
	public function workflow_save_copy( $new_post_id ) {

		// ---------------------------------------------------------------------
		// If we are making a copy of the current version, also save meta.
		// ---------------------------------------------------------------------

		// Find and save the data.
		$data = isset( $_POST['commentpress_new_post'] ) ? trim( wp_unslash( $_POST['commentpress_new_post'] ) ) : '0';

		// Do we want to create a new revision?
		if ( $data == '0' ) {
			return;
		}

		// Get original text.
		$original = isset( $_POST['cporiginaltext'] ) ? trim( wp_unslash( $_POST['cporiginaltext'] ) ) : '';

		// Set key.
		$key = '_cp_original_text';

		// If the custom field already has a value.
		if ( get_post_meta( $new_post_id, $key, true ) !== '' ) {

			// If empty string.
			if ( $original === '' ) {

				// Delete the meta_key.
				delete_post_meta( $post->ID, $key );

			} else {

				// Update the data.
				update_post_meta( $post->ID, $key, $original );

			}

		} else {

			// Only add meta if we have field data.
			if ( $original != '' ) {

				// Add the data.
				add_post_meta( $new_post_id, $key, $original, true );

			}

		}

		// Get literal translation.
		$literal = isset( $_POST['cpliteraltranslation'] ) ? trim( wp_unslash( $_POST['cpliteraltranslation'] ) ) : '';

		// Set key.
		$key = '_cp_literal_translation';

		// If the custom field already has a value.
		if ( get_post_meta( $new_post_id, $key, true ) !== '' ) {

			// If empty string.
			if ( $literal === '' ) {

				// Delete the meta_key.
				delete_post_meta( $post->ID, $key );

			} else {

				// Update the data.
				update_post_meta( $post->ID, $key, $literal );

			}

		} else {

			// Only add meta if we have field data.
			if ( $literal != '' ) {

				// Add the data.
				add_post_meta( $new_post_id, $key, $literal, true );

			}

		}

	}

	/**
	 * Save Workflow meta value.
	 *
	 * @since 3.4
	 *
	 * @param object $post The Post object.
	 */
	public function save_workflow( $post ) {

		// Do we have the option to set Workflow (new in 3.3.1)?
		if ( $this->core->db->option_exists( 'cp_blog_workflow' ) ) {

			// Get Workflow setting for the Blog.
			$workflow = $this->core->db->option_get( 'cp_blog_workflow' );

			/*
			// ----------------
			// WORK IN PROGRESS

			// Set key.
			$key = '_cp_blog_workflow_override';

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// Get existing value
				$workflow = get_post_meta( $post->ID, $key, true );

			}
			// ----------------
			*/

			// If it's enabled.
			if ( $workflow == '1' ) {

				/**
				 * Notify plugins that Workflow stuff needs saving.
				 *
				 * @since 3.4
				 *
				 * @param object $post The Post object.
				 */
				do_action( 'cp_workflow_save_' . $post->post_type, $post );

			}

			/*
			// ----------------
			// WORK IN PROGRESS

			// Get the setting for the Post (we do this after saving the extra
			// Post data because
			$formatter = isset( $_POST['cp_post_type_override'] ) ? $_POST['cp_post_type_override'] : '';

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// If empty string
				if ( $data === '' ) {

					// Delete the meta_key
					delete_post_meta( $post->ID, $key );

				} else {

					// Update the data.
					update_post_meta( $post->ID, $key, esc_sql( $data ) );

				}

			} else {

				// Add the data.
				add_post_meta( $post->ID, $key, esc_sql( $data ) );

			}
			// ----------------
			*/

		}

	}

}
