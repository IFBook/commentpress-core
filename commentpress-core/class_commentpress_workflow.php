<?php

/**
 * CommentPress Core Workflow Class.
 *
 * This class provides "Translation" workflow to CommentPress Core.
 *
 * @since 3.0
 */
class Commentpress_Core_Workflow {

	/**
	 * Plugin object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $parent_obj The plugin object.
	 */
	public $parent_obj;



	/**
	 * Initialises this object.
	 *
	 * @since 3.0
	 *
	 * @param object $parent_obj a reference to the parent object.
	 */
	public function __construct( $parent_obj = null ) {

		// Store reference to "parent" (calling obj, not OOP parent).
		$this->parent_obj = $parent_obj;

		// Store reference to database wrapper (child of calling obj).
		$this->db = $this->parent_obj->db;

		// Init.
		$this->_init();

	}



	/**
	 * Set up all items associated with this object.
	 *
	 * @since 3.0
	 */
	public function initialise() {

	}



	/**
	 * If needed, destroys all items associated with this object.
	 *
	 * @since 3.0
	 */
	public function destroy() {

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Public Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Enable workflow.
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
	 * Override the name of the workflow checkbox label.
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
	 * Amend the group meta if workflow is enabled.
	 *
	 * @since 3.0
	 *
	 * @param str $blog_type The existing numerical type of the blog.
	 * @return str $blog_type The modified numerical type of the blog.
	 */
	public function group_meta_set_blog_type( $blog_type, $blog_workflow ) {

		// If the blog workflow is enabled, then this is a translation group.
		if ( $blog_workflow == '1' ) {

			// Translation is type 2
			$blog_type = '2';

		}

		/**
		 * Allow plugins to override the blog type - for example if workflow
		 * is enabled, it might become a new blog type as far as BuddyPress
		 * is concerned.
		 *
		 * @since 3.0
		 *
		 * @param int $blog_type The numeric blog type.
		 * @param bool $blog_workflow True if workflow enabled, false otherwise.
		 */
		return apply_filters( 'cp_class_commentpress_workflow_group_blogtype', $blog_type, $blog_workflow );

	}



	/**
	 * Add our metabox if workflow is enabled.
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
	 * Amend the workflow metabox title.
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
	 * Save workflow data based on the state of the metabox.
	 *
	 * @since 3.0
	 *
	 * @param object $post_obj The WordPress post object.
	 */
	public function workflow_save_post( $post_obj ) {

		// If no post, kick out.
		if ( ! $post_obj ) return;

		// If not post or page, kick out.
		$types = [ 'post', 'page' ];
		if ( ! in_array( $post_obj->post_type, $types ) ) return;

		// Authenticate.
		$nonce = isset( $_POST['commentpress_workflow_nonce'] ) ? $_POST['commentpress_workflow_nonce'] : '';
		if ( ! wp_verify_nonce( $nonce, 'commentpress_post_workflow_settings' ) ) return;

		// Is this an auto save routine?
		if ( defined('DOING_AUTOSAVE') AND DOING_AUTOSAVE ) return;

		// Check permissions.
		if ( $post_obj->post_type == 'post' AND ! current_user_can( 'edit_posts' ) ) return;
		if ( $post_obj->post_type == 'page' AND ! current_user_can( 'edit_pages' ) ) return;

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
		$original = ( isset( $_POST['cporiginaltext'] ) ) ? $_POST['cporiginaltext'] : '';

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
		$literal = ( isset( $_POST['cpliteraltranslation'] ) ) ? $_POST['cpliteraltranslation'] : '';

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
	 * Add the workflow content to the new version.
	 *
	 * @since 3.0
	 *
	 * @param int $new_post_id The numeric ID of the new WordPress post.
	 */
	public function workflow_save_copy( $new_post_id ) {

		// ---------------------------------------------------------------------
		// If we are making a copy of the current version, also save meta
		// ---------------------------------------------------------------------

		// Find and save the data.
		$data = ( isset( $_POST['commentpress_new_post'] ) ) ? $_POST['commentpress_new_post'] : '0';

		// Do we want to create a new revision?
		if ( $data == '0' ) return;

		// Get original text.
		$original = ( isset( $_POST['cporiginaltext'] ) ) ? $_POST['cporiginaltext'] : '';

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
		$literal = ( isset( $_POST['cpliteraltranslation'] ) ) ? $_POST['cpliteraltranslation'] : '';

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



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Private Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Object initialisation.
	 *
	 * @since 3.0
	 */
	public function _init() {

		// Register hooks.
		$this->_register_hooks();

	}



	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.0
	 */
	public function _register_hooks() {

		// Enable workflow.
		add_filter( 'cp_blog_workflow_exists', [ $this, 'blog_workflow_exists' ], 21 );

		// Override label.
		add_filter( 'cp_blog_workflow_label', [ $this, 'blog_workflow_label' ], 21 );

		// Override blog type if workflow is on.
		add_filter( 'cp_get_group_meta_for_blog_type', [ $this, 'group_meta_set_blog_type' ], 21, 2 );

		// Is this the back end?
		if ( is_admin() ) {

			// Add meta box for translation workflow.
			add_action( 'cp_workflow_metabox', [ $this, 'workflow_metabox' ], 10, 2 );

			// Override meta box title for translation workflow.
			add_filter( 'cp_workflow_metabox_title', [ $this, 'workflow_metabox_title' ], 21, 1 );

			// Save post with translation workflow.
			add_action( 'cp_workflow_save_post', [ $this, 'workflow_save_post' ], 21, 1 );

			// Save page with translation workflow.
			add_action( 'cp_workflow_save_page', [ $this, 'workflow_save_post' ], 21, 1 );

			// Save translation workflow for copied posts.
			add_action( 'cp_workflow_save_copy', [ $this, 'workflow_save_copy' ], 21, 1 );

		}

		// Create custom filters that mirror 'the_content'.
		add_filter( 'cp_workflow_richtext_content', 'wptexturize' );
		add_filter( 'cp_workflow_richtext_content', 'convert_smilies' );
		add_filter( 'cp_workflow_richtext_content', 'convert_chars' );
		add_filter( 'cp_workflow_richtext_content', 'wpautop' );
		add_filter( 'cp_workflow_richtext_content', 'shortcode_unautop' );

	}



//##############################################################################



} // Class ends.



