<?php

/**
 * CommentPress Core Editor Class.
 *
 * A class that handles compatibility with WordPress Front End Editor.
 *
 * @since 3.7
 */
class Commentpress_Core_Editor {

	/**
	 * Plugin object.
	 *
	 * @since 3.7
	 * @access public
	 * @var object $parent_obj The plugin object.
	 */
	public $parent_obj;

	/**
	 * Database interaction object.
	 *
	 * @since 3.7
	 * @access public
	 * @var object $db The database object.
	 */
	public $db;

	/**
	 * Editor toggle state.
	 *
	 * @since 3.7
	 * @access public
	 * @var str $toggle_state The toggle state.
	 */
	public $toggle_state;



	/**
	 * Initialises this object.
	 *
	 * @since 3.7
	 *
	 * @param object $parent_obj a reference to the parent object.
	 */
	public function __construct( $parent_obj = null ) {

		// Store reference to "parent" (calling obj, not OOP parent).
		$this->parent_obj = $parent_obj;

		// Store reference to database wrapper (child of calling obj).
		$this->db = $this->parent_obj->db;

		// Init this class.
		add_action( 'init', [ $this, 'initialise' ], 999 );

	}



	/**
	 * Set up all items associated with this object.
	 *
	 * @since 3.7
	 */
	public function initialise() {

		// Bail if not logged in.
		if ( ! is_user_logged_in() ) return;

		// Bail if there's no WP FEE present.
		if ( ! class_exists( 'FEE' ) ) return;

		// Try and find the global.
		if ( ! isset( $GLOBALS['wp_front_end_editor'] ) ) return;

		// Broadcast.
		do_action( 'commentpress_editor_present' );

		// Save default toggle state.
		$this->editor_toggle_set_default();

		// Kill WP FEE.
		$this->wp_fee_prevent_tinymce();

		// Register hooks.
		$this->register_hooks();

	}



	/**
	 * If needed, destroys all items associated with this object.
	 *
	 * @since 3.7
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
	 * Register WordPress hooks.
	 *
	 * @since 3.7
	 */
	public function register_hooks() {

		// Intercept toggles when WP is set up.
		add_action( 'wp', [ $this, 'editor_toggle_intercept' ] );

		// Enable editor toggle.
		add_action( 'cp_content_tab_before_search', [ $this, 'editor_toggle_show' ], 20 );

		// Test for flag.
		if ( isset( $this->fee ) AND $this->fee == 'killed' ) {

			// Enable infinite scroll.
			//add_filter( 'cpajax_disable_infinite_scroll', '__return_false', 11 );

			// Amend Edit Page button.
			add_action( 'wp_before_admin_bar_render', [ $GLOBALS['wp_front_end_editor'], 'wp_before_admin_bar_render' ] );

			// Amend Edit Page link.
			add_filter( 'get_edit_post_link', [ $GLOBALS['wp_front_end_editor'], 'get_edit_post_link' ], 10, 3 );
			add_filter( 'get_edit_post_link', [ $this, 'get_edit_post_link' ], 100, 3 );

			// Broadcast.
			do_action( 'commentpress_editor_wp_fee_disabled' );

			// Bail on further hooks.
			return;

		}

		/*
		 * The following hooks are enabled when WP FEE is enabled because we need
		 * to suppress TinyMCE for commenting and enable slipstream functionality
		 * that supports WP FEE.
		 */

		// Prevent infinite scroll, if enabled.
		add_filter( 'cpajax_disable_infinite_scroll', '__return_true' );

		// Prevent TinyMCE in comment form.
		add_filter( 'cp_override_tinymce', [ $this, 'commentpress_prevent_tinymce' ], 1000, 1 );
		add_filter( 'commentpress_is_tinymce_allowed', [ $this, 'commentpress_disallow_tinymce' ], 1000 );

		// Test for AJAX.
		if ( defined( 'DOING_AJAX' ) AND DOING_AJAX ) {

			// Force filter the content during AJAX.
			add_filter( 'commentpress_force_the_content', '__return_true' );

			// Filter the content during AJAX.
			add_filter( 'the_content', [ $this->parent_obj, 'the_content' ], 20 );

		}

		// Add AJAX functionality.
		add_action( 'wp_ajax_cp_get_comments_container', [ $this, 'comments_get_container' ] );
		add_action( 'wp_ajax_nopriv_cp_get_comments_container', [ $this, 'comments_get_container' ] );

		// Add vars to Javascript.
		add_filter( 'commentpress_get_javascript_vars', [ $this, 'javascript_get_vars' ] );

		// Add metabox.
		add_action( 'commentpress_after_comments_container', [ $this, 'metabox_get_container' ] );

		// Add metabox AJAX functionality.
		add_action( 'wp_ajax_cp_set_post_title_visibility', [ $this, 'metabox_set_post_title_visibility' ] );
		add_action( 'wp_ajax_nopriv_cp_set_post_title_visibility', [ $this, 'metabox_set_post_title_visibility' ] );
		add_action( 'wp_ajax_cp_set_page_meta_visibility', [ $this, 'metabox_set_page_meta_visibility' ] );
		add_action( 'wp_ajax_nopriv_cp_set_page_meta_visibility', [ $this, 'metabox_set_page_meta_visibility' ] );
		add_action( 'wp_ajax_cp_set_number_format', [ $this, 'metabox_set_number_format' ] );
		add_action( 'wp_ajax_nopriv_cp_set_number_format', [ $this, 'metabox_set_number_format' ] );
		add_action( 'wp_ajax_cp_set_post_type_override', [ $this, 'metabox_set_post_type_override' ] );
		add_action( 'wp_ajax_nopriv_cp_set_post_type_override', [ $this, 'metabox_set_post_type_override' ] );
		add_action( 'wp_ajax_cp_set_starting_para_number', [ $this, 'metabox_set_starting_para_number' ] );
		add_action( 'wp_ajax_nopriv_cp_set_starting_para_number', [ $this, 'metabox_set_starting_para_number' ] );

		// Add an action to wp_enqueue_scripts that triggers themes to include their WP FEE compatibility script.
		add_action( 'wp_enqueue_scripts', [ $this, 'trigger_script_inclusion' ], 9999 );

		// Broadcast.
		do_action( 'commentpress_editor_wp_fee_enabled' );

	}



	/**
	 * Set editor toggle state if none exists.
	 *
	 * @since 3.7
	 */
	public function trigger_script_inclusion() {
		do_action( 'commentpress_editor_include_javascript' );
	}



	/**
	 * Set editor toggle state if none exists.
	 *
	 * @since 3.7
	 */
	public function editor_toggle_set_default() {

		// Get existing.
		$state = $this->db->option_get( 'cp_editor_toggle', false );

		// Well?
		if ( $state === false ) {

			// Default state is 'writing'.
			$state = 'writing';

			// Set default.
			$this->db->option_set( 'cp_editor_toggle', $state );

			// Save.
			$this->db->options_save();

		}

		// Set property.
		$this->toggle_state = $state;

	}



	/**
	 * Intercept editor toggling once plugins are loaded.
	 *
	 * @since 3.7
	 */
	public function editor_toggle_intercept() {

		// Access globals.
		global $post;

		// Don't toggle by default.
		$do_toggle = false;

		// Check toggle button.
		if (
			isset( $_GET['cp_editor_nonce'] ) AND
			wp_verify_nonce( $_GET['cp_editor_nonce'], 'editor_toggle' )
		) {

			// Yes, toggle.
			$do_toggle = true;

			// Plain old permalink.
			$url = get_permalink( $post->ID );

		}

		// Check "Edit Page" menu link.
		if (
			isset( $_GET['cp_editor_edit_nonce'] ) AND
			wp_verify_nonce( $_GET['cp_editor_edit_nonce'], 'editor_edit_toggle' )
		) {

			// Yes, toggle.
			$do_toggle = true;

			// Permalink with "force edit" hash.
			$url = get_permalink( $post->ID ) . '#edit=true';

		}

		// Bail of not toggling.
		if ( $do_toggle === false ) return;

		// Get existing state.
		$state = $this->db->option_get( 'cp_editor_toggle' );

		// Flip the state.
		if ( $state === 'writing' ) {
			$state = 'commenting';
		} else {
			$state = 'writing';
		}

		// Save the new toggle state.
		$this->db->option_set( 'cp_editor_toggle', $state );

		// Save.
		$this->db->options_save();

		// Redirect.
		wp_redirect( $url );
		exit();

	}



	/**
	 * Inject editor toggle link before search in Contents column.
	 *
	 * @since 3.7
	 */
	public function editor_toggle_show() {

		// Bail if not commentable.
		if ( ! $this->parent_obj->is_commentable() ) return;

		// Access post.
		global $post;

		// Bail if there isn't one.
		if ( ! is_object( $post ) ) return;

		// Only show for admins and post authors.
		if ( ! current_user_can( 'edit_post', $post->ID ) ) return;

		// Change text depending on toggle state.
		if ( $this->toggle_state == 'writing' ) {
			$heading = __( 'Author Mode: Write', 'commentpress-core' );
		} else {
			$heading = __( 'Author Mode: Comment', 'commentpress-core' );
		}

		echo '
		<h3 class="activity_heading">' .
			apply_filters( 'cp_content_tab_editor_toggle_title', $heading, $this->toggle_state ) .
		'</h3>

		<div class="paragraph_wrapper editor_toggle_wrapper">

		';

		do_action( 'cp_content_tab_editor_toggle_before' );

		echo '

		<div class="editor_toggle">
			' . $this->_toggle_link() . '
		</div><!-- /editor_toggle -->

		';

		do_action( 'cp_content_tab_editor_toggle_after' );

		echo '</div>

		';

	}



	/**
	 * Prevent WordPress Front-end Editor from loading.
	 *
	 * @since 3.7
	 */
	public function wp_fee_prevent_tinymce() {

		// What's our toggle state?
		if ( isset( $this->toggle_state ) AND $this->toggle_state === 'commenting' ) {

			// Set flag.
			$this->fee = 'killed';

			// Do not allow FEE to init.
			remove_action( 'init', [ $GLOBALS['wp_front_end_editor'], 'init' ] );

		}

	}



	/**
	 * Disallow TinyMCE from loading in the comment form.
	 *
	 * @since 3.7
	 *
	 * @return bool False to disallow loading.
	 */
	public function commentpress_disallow_tinymce() {

		// Do not allow.
		return false;

	}



	/**
	 * Prevent TinyMCE from loading in the comment form.
	 *
	 * @since 3.7
	 *
	 * @return int Zero prevents loading.
	 */
	public function commentpress_prevent_tinymce( $var ) {

		// Do not show.
		return 0;

	}



	/**
	 * Get additional javascript vars.
	 *
	 * @since 3.7
	 *
	 * @param array $vars The existing variables to pass to our Javascript.
	 * @return array $vars The modified variables to pass to our Javascript.
	 */
	public function javascript_get_vars( $vars ) {

		// Access globals.
		global $post, $multipage, $page;

		// Bail if we don't have a post (like on the 404 page).
		if ( ! is_object( $post ) ) return $vars;

		// We need to know the url of the AJAX handler.
		$vars['cp_ajax_url'] = admin_url( 'admin-ajax.php' );

		// Add the url of the animated loading bar gif.
		$vars['cp_spinner_url'] = plugins_url( 'commentpress-ajax/assets/images/loading.gif', COMMENTPRESS_PLUGIN_FILE );

		// Add post ID.
		$vars['cp_post_id'] = $post->ID;

		// Add post multipage var.
		$vars['cp_post_multipage'] = $multipage;

		// Add post page var.
		$vars['cp_post_page'] = $page;

		// Add options title.
		$vars['cp_options_title'] = __( 'Options', 'commentpress-core' );

		// --<
		return $vars;

	}



	/**
	 * Get new comments container.
	 *
	 * @since 3.7
	 */
	public function comments_get_container() {

		// Init return.
		$data = [];

		// Access globals.
		global $post, $multipage, $page, $pages;

		// Get post ID.
		$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : NULL;

		// Make it an integer, just to be sure.
		$post_id = (int) $post_id;

		// Enable WordPress API on post.
		$GLOBALS['post'] = get_post( $post_id );
		setup_postdata( $post );

		// Default to post content.
		$content = $post->post_content;

		// Get page number.
		$page_num = isset( $_POST['post_page'] ) ? $_POST['post_page'] : 1;
		if ( $page_num == 0 ) $page_num = 1;

		// Override if multipage.
		if ( $multipage ) $content = $pages[($page_num - 1)];

		// Trigger CommentPress Core comments collation.
		$content = apply_filters( 'the_content', $content );

		// Add move button to the comment meta.
		add_filter( 'cp_comment_edit_link', 'cpajax_add_reassign_button', 20, 2 );

		// Get comments using buffer.
		ob_start();

		// Can't remember why we need this.
		$vars = $this->parent_obj->db->get_javascript_vars();

		/**
		 * Try to locate template using WP method.
		 *
		 * @since 3.4
		 *
		 * @param str The existing path returned by WordPress.
		 * @return str The modified path.
		 */
		$cp_comments_by_para = apply_filters(
			'cp_template_comments_by_para',
			locate_template( 'assets/templates/comments_by_para.php' )
		);

		// Load it if we find it.
		if ( $cp_comments_by_para != '' ) load_template( $cp_comments_by_para );

		// Get content.
		$comments = ob_get_contents();

		// Flush buffer.
		ob_end_clean();

		// Wrap in div.
		$comments = '<div class="comments-for-' . $post->ID . '">' . $comments . '</div>';

		// Add comments column.
		$data['comments'] = $comments;
		$data['multipage'] = $multipage;
		$data['page'] = $page;
		//$data['pages'] = $pages;

		// Send to browser.
		$this->_send_json_data( $data );

	}



	/**
	 * Get new post metabox container.
	 *
	 * @since 3.7
	 */
	public function metabox_get_container() {

		// Open div.
		echo '<div class="metabox_container" style="display: none;">';

		// Use common method.
		$this->parent_obj->custom_box_page();

		// Close div.
		echo '</div>' . "\n\n";

	}



	/**
	 * AJAX metabox: set post title visibility.
	 *
	 * @since 3.7
	 */
	public function metabox_set_post_title_visibility() {

		// Access globals.
		global $post;

		// Init return.
		$data = [];

		// Set up post.
		if ( $this->_setup_post() ) {

			// Save data.
			$result = $this->db->save_page_title_visibility( $post );

			// Construct data to return.
			$data['error'] = 'success';
			$data['message'] = __( 'Option saved', 'commentpress-core' );
			$data['toggle'] = ( $result == 'show' ) ? 'show' : 'hide';

		} else {

			// Define error.
			$data['error'] = __( 'Could not save this option.', 'commentpress-core' );

		}

		// Send to browser.
		$this->_send_json_data( $data );

	}



	/**
	 * AJAX metabox: set page meta visibility.
	 *
	 * @since 3.7
	 */
	public function metabox_set_page_meta_visibility() {

		// Access globals.
		global $post;

		// Init return.
		$data = [];

		// Set up post.
		if ( $this->_setup_post() ) {

			// Save data.
			$result = $this->db->save_page_meta_visibility( $post );

			// Construct data to return.
			$data['error'] = 'success';
			$data['message'] = __( 'Option saved', 'commentpress-core' );
			$data['toggle'] = ( $result == 'hide' ) ? 'hide' : 'show';

		} else {

			// Define error.
			$data['error'] = __( 'Could not save this option.', 'commentpress-core' );

		}

		// Send to browser.
		$this->_send_json_data( $data );

	}



	/**
	 * AJAX metabox: set number format.
	 *
	 * @since 3.7
	 */
	public function metabox_set_number_format() {

		// Access globals.
		global $post;

		// Init return.
		$data = [];

		// Set up post.
		if ( $this->_setup_post() ) {

			// Save data.
			$this->db->save_page_numbering( $post );

			// Init list.
			$num = $this->parent_obj->nav->init_page_lists();

			// Get page num.
			$num = $this->parent_obj->nav->get_page_number( $post->ID );

			// Construct data to return.
			$data['error'] = 'success';
			$data['message'] = __( 'Option saved', 'commentpress-core' );
			$data['number'] = $num;

		} else {

			// Define error.
			$data['error'] = __( 'Could not save this option.', 'commentpress-core' );

		}

		// Send to browser.
		$this->_send_json_data( $data );

	}



	/**
	 * AJAX metabox: set formatter.
	 *
	 * @since 3.7
	 */
	public function metabox_set_post_type_override() {

		// Access globals.
		global $post;

		// Init return.
		$data = [];

		// Set up post.
		if ( $this->_setup_post() ) {

			// Save data.
			$this->db->save_formatter( $post );

			// Construct data to return.
			$data['error'] = 'success';
			$data['message'] = __( 'Option saved', 'commentpress-core' );

		} else {

			// Define error.
			$data['error'] = __( 'Could not save this option.', 'commentpress-core' );

		}

		// Send to browser.
		$this->_send_json_data( $data );

	}



	/**
	 * AJAX metabox: set starting paragraph number.
	 *
	 * @since 3.7
	 */
	public function metabox_set_starting_para_number() {

		// Access globals.
		global $post;

		// Init return.
		$data = [];

		// Set up post.
		if ( $this->_setup_post() ) {

			// Save data.
			$this->db->save_starting_paragraph( $post );

			// Construct data to return.
			$data['error'] = 'success';
			$data['message'] = __( 'Option saved', 'commentpress-core' );

		} else {

			// Define error.
			$data['error'] = __( 'Could not save this option.', 'commentpress-core' );

		}

		// Send to browser.
		$this->_send_json_data( $data );

	}



	/**
	 * Override the "Edit Page" link when WP FEE not active.
	 *
	 * @since 3.7
	 *
	 * @param string $link The existing link.
	 * @param int $id The numeric post ID.
	 * @param string $context How to write ampersands. Default 'display' encodes as '&amp;'.
	 * @return string $link The modified link.
	 */
	public function get_edit_post_link( $link, $id, $context ) {

		// Test for WP FEE hash.
		if ( $link == '#fee-edit-link' ) {

			// Get url including anchor.
			$url = get_permalink( $id ) . $link;

			// Get link with nonce attached.
			$link = wp_nonce_url( $url, 'editor_edit_toggle', 'cp_editor_edit_nonce' );

		}

		// --<
		return $link;

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Private Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Set up post object from passed data.
	 *
	 * @since 3.7
	 *
	 * @return bool True if post object set up, false otherwise
	 */
	private function _setup_post() {

		// Access globals.
		global $post;

		// Get post ID.
		$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : NULL;

		// Bail if we don't get one.
		if ( is_null( $post_id ) ) return false;

		// Bail if we get a non-numeric value.
		if ( ! is_numeric( $post_id ) ) return false;

		// Enable WordPress API on post.
		$GLOBALS['post'] = get_post( $post_id );
		setup_postdata( $post );

		// Success.
		return true;

	}



	/**
	 * Send data to browser and exit.
	 *
	 * @since 3.7
	 */
	private function _send_json_data( $data ) {

		// Set reasonable headers.
		header('Content-type: text/plain');
		header("Cache-Control: no-cache");
		header("Expires: -1");

		// Echo.
		echo json_encode( $data );

		// Die!
		exit();

	}



	/**
	 * Inject editor toggle before search in Contents column.
	 *
	 * @since 3.7
	 *
	 * @return str $link The link markup.
	 */
	private function _toggle_link() {

		// Declare access to globals.
		global $post;

		// Change text depending on toggle state
		if ( $this->toggle_state == 'writing' ) {

			// Link text.
			$text = __( 'Switch to Commenting Mode', 'commentpress-core' );

			// Link title.
			$title = __( 'Switch to Commenting Mode', 'commentpress-core' );

		} else {

			// Link text.
			$text = __( 'Switch to Writing Mode', 'commentpress-core' );

			// Link title.
			$title = __( 'Switch to Writing Mode', 'commentpress-core' );

		}

		// Link URL.
		$url = wp_nonce_url( get_permalink( $post->ID ), 'editor_toggle', 'cp_editor_nonce' );

		// Link class
		$class = 'button';

		// Construct link.
		$link = '<a href="' . $url . '" class="' . $class . '" title="' . esc_attr( $title ) . '">' . $text . '</a>';

		// --<
		return apply_filters( 'commentpress_editor_toggle_link', $link, $text, $title, $url, $class );

	}



//##############################################################################



} // Class ends.



