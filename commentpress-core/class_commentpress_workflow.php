<?php /*
================================================================================
Class CommentpressCoreWorkflow
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This class provides "Translation" workflow to CommentPress Core.

--------------------------------------------------------------------------------
*/






/*
================================================================================
Class Name
================================================================================
*/

class CommentpressCoreWorkflow {






	/*
	============================================================================
	Properties
	============================================================================
	*/
	
	// parent object reference
	var $parent_obj;
	
	
	



	/** 
	 * @description: initialises this object
	 * @param object $parent_obj a reference to the parent object
	 * @return object
	 * @todo: 
	 *
	 */
	function __construct( $parent_obj = null ) {
	
		// store reference to "parent" (calling obj, not OOP parent)
		$this->parent_obj = $parent_obj;
	
		// store reference to database wrapper (child of calling obj)
		$this->db = $this->parent_obj->db;
	
		// init
		$this->_init();

		// --<
		return $this;

	}
	
	
	



	/**
	 * PHP 4 constructor
	 */
	function CommentpressCoreWorkflow( $parent_obj = null ) {
		
		// is this php5?
		if ( version_compare( PHP_VERSION, "5.0.0", "<" ) ) {
		
			// call php5 constructor
			$this->__construct( $parent_obj );
			
		}
		
		// --<
		return $this;

	}






	/** 
	 * @description: set up all items associated with this object
	 * @todo: 
	 *
	 */
	function initialise() {
	
	}
	
	
	



	/** 
	 * @description: if needed, destroys all items associated with this object
	 * @todo: 
	 *
	 */
	function destroy() {
	
	}
	
	
	



//##############################################################################
	
	
	



	/*
	============================================================================
	PUBLIC METHODS
	============================================================================
	*/
	
	
	



	/** 
	 * @description: enable workflow
	 * @todo: 
	 *
	 */
	function blog_workflow_exists( $exists ) {
	
		// switch on, but allow overrides
		return apply_filters( 
			
			'cp_class_commentpress_workflow_enabled', 
			true
		
		);
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: override the name of the workflow checkbox label
	 * @todo: 
	 *
	 */
	function blog_workflow_label( $name ) {
	
		// set label, but allow overrides
		return apply_filters( 
			
			'cp_class_commentpress_workflow_label', 
			__( 'Enable Translation Workflow', 'commentpress-plugin' )
		
		);
		
	}
	
	
	
	
	
	
	/** 
	 * @description: amend the group meta if workflow is enabled
	 * @todo: 
	 *
	 */
	function group_meta_set_blog_type( $blog_type, $blog_workflow ) {
	
		// if the blog workflow is enabled, then this is a translation group
		if ( $blog_workflow == '1' ) {
		
			// translation is type 2
			$blog_type = '2';
		
		}
		
		// return, but allow overrides
		return apply_filters( 
			
			'cp_class_commentpress_workflow_group_blogtype', 
			$blog_type
		
		);
		
	}
	
	
	
	
	

	/** 
	 * @description: add our metabox if workflow is enabled
	 * @todo: 
	 *
	 */
	function workflow_metabox() {
	
		global $post;
	
		// Use nonce for verification
		wp_nonce_field( 'commentpress_post_workflow_settings', 'commentpress_workflow_nonce' );
		
		// label
		echo '<h3>' . __( 'Original Text', 'commentpress-plugin' ) . '</h3>';
		
		// set key
		$key = '_cp_original_text';
		
		// get content
		$content = get_post_meta( $post->ID, $key, true );
		
		// set editor ID (sucks that it can't use - and _)
		$editor_id = 'cporiginaltext';
		
		// call the editor
		wp_editor( 
		
			esc_html( stripslashes( $content ) ), 
			$editor_id, 
			$settings = array(
		
				'media_buttons' => false
			
			)
			
		);
		
		// label
		echo '<h3>' . __( 'Literal Translation', 'commentpress-plugin' ) . '</h3>';
		
		// set key
		$key = '_cp_literal_translation';
		
		// get content
		$content = get_post_meta( $post->ID, $key, true );
		
		// set editor ID (sucks that it can't use - and _)
		$editor_id = 'cpliteraltranslation';
		
		// call the editor
		wp_editor( 
		
			esc_html( stripslashes( $content ) ), 
			$editor_id, 
			$settings = array(
		
				'media_buttons' => false
			
			)
			
		);
		
	}
	
	
	
	
	

	/** 
	 * @description: amend the workflow metabox title
	 * @todo: 
	 *
	 */
	function workflow_metabox_title( $title ) {
	
		// set label, but allow overrides
		return apply_filters( 
			
			'cp_class_commentpress_workflow_metabox_title', 
			__( 'Translations', 'commentpress-plugin' )
		
		);
		
	}
	
	
	
	
	

	/** 
	 * @description: amend the workflow metabox title
	 * @todo: 
	 *
	 */
	function workflow_save_post( $post_obj ) {
	
		// how do we get the content of wp_editor()?
	
		// if no post, kick out
		if ( !$post_obj ) { return; }
		
		// if not page, kick out
		if ( $post_obj->post_type != 'post' ) { return; }
		
		
		
		// authenticate
		$_nonce = isset( $_POST['commentpress_workflow_nonce'] ) ? $_POST['commentpress_workflow_nonce'] : '';
		if ( !wp_verify_nonce( $_nonce, 'commentpress_post_workflow_settings' ) ) { return; }
		
		// is this an auto save routine?
		if ( defined('DOING_AUTOSAVE') AND DOING_AUTOSAVE ) { return; }
		
		//print_r( array( 'can' => current_user_can( 'edit_posts' ) ) ); die();
		
		// Check permissions
		if ( !current_user_can( 'edit_posts' ) ) { return; }
		
		
		
		// OK, we're authenticated
		
		
		
		// check for revision
		if ( $post_obj->post_type == 'revision' ) {
		
			// get parent
			if ( $post_obj->post_parent != 0 ) {
				$post = get_post( $post_obj->post_parent );
			} else {
				$post = $post_obj;
			}
	
		} else {
			$post = $post_obj;
		}
		


		// database object
		global $wpdb;
		


		// ---------------------------------------------------------------------
		// Save the content of the two wp_editors
		// ---------------------------------------------------------------------
		
		// get original text
		$original = ( isset( $_POST['cporiginaltext'] ) ) ? $_POST['cporiginaltext'] : '';
		//print_r( $post ); die();
		
		// set key
		$key = '_cp_original_text';
		
		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
		
			// if empty string...
			if ( $original === '' ) {
		
				// delete the meta_key
				delete_post_meta( $post->ID, $key );
			
			} else {
			
				// update the data
				update_post_meta( $post->ID, $key, $original );
				
			}
			
		} else {
		
			// only add meta if we have field data
			if ( $original !== '' ) {
		
				// add the data
				add_post_meta( $post->ID, $key, $wpdb->escape( $original ) );
			
			}
			
		}

		// get literal translation
		$literal = ( isset( $_POST['cpliteraltranslation'] ) ) ? $_POST['cpliteraltranslation'] : '';
		
		// set key
		$key = '_cp_literal_translation';
		
		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {
		
			// if empty string...
			if ( $literal === '' ) {
		
				// delete the meta_key
				delete_post_meta( $post->ID, $key );
			
			} else {
			
				// update the data
				update_post_meta( $post->ID, $key, $literal );
				
			}
			
		} else {
		
			// only add meta if we have field data
			if ( $literal !== '' ) {
		
				// add the data
				add_post_meta( $post->ID, $key, $wpdb->escape( $literal ) );
			
			}
			
		}

	}
	
	
	
	
	

	/** 
	 * @description: add the workflow content to the new version
	 * @todo: 
	 *
	 */
	function workflow_save_copy( $new_post_id ) {
	
		// ---------------------------------------------------------------------
		// If we are making a copy of the current version, also save meta
		// ---------------------------------------------------------------------
		
		// find and save the data
		$_data = ( isset( $_POST['commentpress_new_post'] ) ) ? $_POST['commentpress_new_post'] : '0';
		
		// do we want to create a new revision?
		if ( $_data == '0' ) { return; }



		// database object
		global $wpdb;
		


		// get original text
		$original = ( isset( $_POST['cporiginaltext'] ) ) ? $_POST['cporiginaltext'] : '';
		//print_r( $post ); die();
		
		// set key
		$key = '_cp_original_text';
		
		// if the custom field already has a value...
		if ( get_post_meta( $new_post_id, $key, true ) !== '' ) {
		
			// if empty string...
			if ( $original === '' ) {
		
				// delete the meta_key
				delete_post_meta( $post->ID, $key );
			
			} else {
			
				// update the data
				update_post_meta( $post->ID, $key, $original );
				
			}
			
		} else {
		
			// only add meta if we have field data
			if ( $original != '' ) {
		
				// add the data
				add_post_meta( $new_post_id, $key, $wpdb->escape( $original ) );
			
			}
			
		}



		// get literal translation
		$literal = ( isset( $_POST['cpliteraltranslation'] ) ) ? $_POST['cpliteraltranslation'] : '';
		
		// set key
		$key = '_cp_literal_translation';
		
		// if the custom field already has a value...
		if ( get_post_meta( $new_post_id, $key, true ) !== '' ) {
		
			// if empty string...
			if ( $literal === '' ) {
		
				// delete the meta_key
				delete_post_meta( $post->ID, $key );
			
			} else {
			
				// update the data
				update_post_meta( $post->ID, $key, $literal );
				
			}
			
		} else {
		
			// only add meta if we have field data
			if ( $literal != '' ) {
		
				// add the data
				add_post_meta( $new_post_id, $key, $wpdb->escape( $literal ) );
			
			}
			
		}

	}
	
	
	
	
	

//##############################################################################
	
	
	



	/*
	============================================================================
	PRIVATE METHODS
	============================================================================
	*/
	
	
	



	/** 
	 * @description: object initialisation
	 * @todo:
	 *
	 */
	function _init() {
	
		// register hooks
		$this->_register_hooks();
		
	}
	
	
	



	/** 
	 * @description: register Wordpress hooks
	 * @todo: 
	 *
	 */
	function _register_hooks() {
		
		// enable workflow
		add_filter( 'cp_blog_workflow_exists', array( $this, 'blog_workflow_exists' ), 21 );
		
		// override label
		add_filter( 'cp_blog_workflow_label', array( $this, 'blog_workflow_label' ), 21 );

		// override blog type if workflow is on
		add_filter( 'cp_get_group_meta_for_blog_type', array( $this, 'group_meta_set_blog_type' ), 21, 2 );

		// is this the back end?
		if ( is_admin() ) {
	
			// add meta box for translation workflow
			add_action( 'cp_workflow_metabox', array( $this, 'workflow_metabox' ), 10, 2 );
		
			// override meta box title for translation workflow
			add_filter( 'cp_workflow_metabox_title', array( $this, 'workflow_metabox_title' ), 21, 1 );
		
			// save post with translation workflow
			add_action( 'cp_workflow_save_post', array( $this, 'workflow_save_post' ), 21, 1 );
		
			// save translation workflow for copied posts
			add_action( 'cp_workflow_save_copy', array( $this, 'workflow_save_copy' ), 21, 1 );
			
		}
		
	}
	
	
	



//##############################################################################
	
	
	



} // class ends
	
	
	




