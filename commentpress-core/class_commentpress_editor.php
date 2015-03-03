<?php /*
================================================================================
Class CommentpressCoreEditor
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This class provides comatibility between WP Front-end Editor and CommentPress Core.

--------------------------------------------------------------------------------
*/



/*
================================================================================
Class Name
================================================================================
*/

class CommentpressCoreEditor {



	/**
	 * Properties
	 */

	// parent object reference
	public $parent_obj;

	// db object reference
	public $db;

	// toggle state
	public $toggle_state;



	/**
	 * Initialises this object
	 *
	 * @param object $parent_obj a reference to the parent object
	 * @return object
	 */
	function __construct( $parent_obj = null ) {

		// store reference to "parent" (calling obj, not OOP parent)
		$this->parent_obj = $parent_obj;

		// store reference to database wrapper (child of calling obj)
		$this->db = $this->parent_obj->db;

		// intercept toggles
		add_action( 'plugins_loaded', array( $this, 'initialise' ), 999 );

		// --<
		return $this;

	}



	/**
	 * Set up all items associated with this object
	 *
	 * @return void
	 */
	public function initialise() {

		// bail if there's no WP FEE present
		if ( ! class_exists( 'FEE' ) ) return;

		// bail if not logged in
		if ( ! is_user_logged_in() ) return;

		// broadcast
		do_action( 'commentpress_editor_present' );

		// save default toggle state
		$this->editor_toggle_set_default();

		// kill WP FEE
		$this->wp_fee_prevent_tinymce();

		// register hooks
		$this->register_hooks();

	}



	/**
	 * If needed, destroys all items associated with this object
	 *
	 * @return void
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
	 * Register WordPress hooks
	 *
	 * @return void
	 */
	public function register_hooks() {

		// intercept toggles when WP is set up
		add_action( 'wp', array( $this, 'editor_toggle_intercept' ) );

		// enable editor toggle
		add_action( 'cp_content_tab_before_search', array( $this, 'editor_toggle_show' ), 20 );

		// test for flag
		if ( isset( $this->fee ) AND $this->fee == 'killed' ) {

			// enable infinite scroll
			//add_filter( 'cpajax_disable_infinite_scroll', '__return_false', 11 );

			// amend Edit Page button
			global $wordpress_front_end_editor;
			add_action( 'wp_before_admin_bar_render', array( $wordpress_front_end_editor, 'wp_before_admin_bar_render' ) );

			// amend Edit Page link
			add_filter( 'get_edit_post_link', array( $wordpress_front_end_editor, 'get_edit_post_link' ), 10, 3 );
			add_filter( 'get_edit_post_link', array( $this, 'get_edit_post_link' ), 100, 3 );

			// broadcast
			do_action( 'commentpress_editor_wp_fee_disabled' );

			// bail on further hooks
			return;

		}

		/**
		 * The following hooks are enabled when WP FEE is enabled because we need
		 * to suppress TinyMCE for commenting and enable slipstream functionality
		 * that supports WP FEE.
		 */

		// prevent infinite scroll, if enabled
		add_filter( 'cpajax_disable_infinite_scroll', '__return_true' );

		// prevent TinyMCE in comment form
		add_filter( 'cp_override_tinymce', array( $this, 'commentpress_prevent_tinymce' ), 1000, 1 );
		add_filter( 'commentpress_is_tinymce_allowed', array( $this, 'commentpress_disallow_tinymce' ), 1000 );

		// test for AJAX
		if ( defined( 'DOING_AJAX' ) AND DOING_AJAX ) {

			// force filter the content during AJAX
			add_filter( 'commentpress_force_the_content', '__return_true' );

			// filter the content during AJAX
			add_filter( 'the_content', array( $this->parent_obj, 'the_content' ), 20 );

		}

		// add AJAX functionality
		add_action( 'wp_ajax_cp_get_comments_container', array( $this, 'comments_get_container' ) );
		add_action( 'wp_ajax_nopriv_cp_get_comments_container', array( $this, 'comments_get_container' ) );

		// add vars to Javascript
		add_filter( 'commentpress_get_javascript_vars', array( $this, 'javascript_get_vars' ) );

		// add metabox
		add_action( 'commentpress_after_comments_container', array( $this, 'metabox_get_container' ) );

		// add metabox AJAX functionality
		add_action( 'wp_ajax_cp_set_post_title_visibility', array( $this, 'metabox_set_post_title_visibility' ) );
		add_action( 'wp_ajax_nopriv_cp_set_post_title_visibility', array( $this, 'metabox_set_post_title_visibility' ) );
		add_action( 'wp_ajax_cp_set_page_meta_visibility', array( $this, 'metabox_set_page_meta_visibility' ) );
		add_action( 'wp_ajax_nopriv_cp_set_page_meta_visibility', array( $this, 'metabox_set_page_meta_visibility' ) );
		add_action( 'wp_ajax_cp_set_number_format', array( $this, 'metabox_set_number_format' ) );
		add_action( 'wp_ajax_nopriv_cp_set_number_format', array( $this, 'metabox_set_number_format' ) );
		add_action( 'wp_ajax_cp_set_post_type_override', array( $this, 'metabox_set_post_type_override' ) );
		add_action( 'wp_ajax_nopriv_cp_set_post_type_override', array( $this, 'metabox_set_post_type_override' ) );
		add_action( 'wp_ajax_cp_set_starting_para_number', array( $this, 'metabox_set_starting_para_number' ) );
		add_action( 'wp_ajax_nopriv_cp_set_starting_para_number', array( $this, 'metabox_set_starting_para_number' ) );

		// add an action to wp_enqueue_scripts that triggers themes to include their WP FEE compatibility script
		add_action( 'wp_enqueue_scripts', array( $this, 'trigger_script_inclusion' ), 9999 );

		// broadcast
		do_action( 'commentpress_editor_wp_fee_enabled' );

	}



	/**
	 * Set editor toggle state if none exists
	 *
	 * @return void
	 */
	public function trigger_script_inclusion() {
		do_action( 'commentpress_editor_include_javascript' );
	}



	/**
	 * Set editor toggle state if none exists
	 *
	 * @return void
	 */
	public function editor_toggle_set_default() {

		// get existing
		$state = $this->db->option_get( 'cp_editor_toggle', false );

		// well?
		if ( $state === false ) {

			// default state is 'writing'
			$state = 'writing';

			// set default
			$this->db->option_set( 'cp_editor_toggle', $state );

			// save
			$this->db->options_save();

		}

		// set property
		$this->toggle_state = $state;

	}



	/**
	 * Intercept editor toggling once plugins are loaded
	 *
	 * @return void
	 */
	public function editor_toggle_intercept() {

		// access globals
		global $post;

		// don't toggle by default
		$do_toggle = false;

		// check toggle button
		if (
			isset( $_GET['cp_editor_nonce'] ) AND
			wp_verify_nonce( $_GET['cp_editor_nonce'], 'editor_toggle' )
		) {

			// yes, toggle
			$do_toggle = true;

			// plain old permalink
			$url = get_permalink( $post->ID );

		}

		// check "Edit Page" menu link
		if (
			isset( $_GET['cp_editor_edit_nonce'] ) AND
			wp_verify_nonce( $_GET['cp_editor_edit_nonce'], 'editor_edit_toggle' )
		) {

			// yes, toggle
			$do_toggle = true;

			// permalink with "force edit" hash
			$url = get_permalink( $post->ID ) . '#edit=true';

		}

		// bail of not toggling
		if ( $do_toggle === false ) return;

		// get existing state
		$state = $this->db->option_get( 'cp_editor_toggle' );

		// flip the state
		if ( $state === 'writing' ) {
			$state = 'commenting';
		} else {
			$state = 'writing';
		}

		// save the new toggle state
		$this->db->option_set( 'cp_editor_toggle', $state );

		// save
		$this->db->options_save();

		// redirect
		wp_redirect( $url );
		exit();

	}



	/**
	 * Inject editor toggle link before search in Contents column
	 *
	 * @return void
	 */
	public function editor_toggle_show() {

		// bail if not commentable
		if ( ! $this->parent_obj->is_commentable() ) return;

		// access post
		global $post;

		// bail if there isn't one
		if ( ! is_object( $post ) ) return;

		// only show for admins and post authors
		if ( ! current_user_can( 'edit_post', $post->ID ) ) return;

		// change text depending on toggle state
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
	 * Prevent WordPress Front-end Editor from loading
	 *
	 * @return void
	 */
	public function wp_fee_prevent_tinymce() {

		// what's our toggle state?
		if ( isset( $this->toggle_state ) AND $this->toggle_state === 'commenting' ) {

			// set flag
			$this->fee = 'killed';

			global $wordpress_front_end_editor;
			remove_action( 'init', array( $wordpress_front_end_editor, 'init' ) );

		}

	}



	/**
	 * Prevent TinyMCE from loading in the comment form
	 *
	 * @return void
	 */
	public function commentpress_disallow_tinymce() {

		// do not allow
		return false;

	}



	/**
	 * Prevent TinyMCE from loading in the comment form
	 *
	 * @return void
	 */
	public function commentpress_prevent_tinymce( $var ) {

		// do not show
		return 0;

	}



	/**
	 * Get additional javascript vars
	 *
	 * @param array $vars The existing variables to pass to our Javascript
	 * @return array $vars The modified variables to pass to our Javascript
	 */
	public function javascript_get_vars( $vars ) {

		// access globals
		global $post, $multipage, $page;

		// bail if we don't have a post (like on the 404 page)
		if ( ! is_object( $post ) ) return $vars;

		// we need to know the url of the AJAX handler
		$vars['cp_ajax_url'] = admin_url( 'admin-ajax.php' );

		// add the url of the animated loading bar gif
		$vars['cp_spinner_url'] = plugins_url( 'commentpress-ajax/assets/images/loading.gif', COMMENTPRESS_PLUGIN_FILE );

		// add post ID
		$vars['cp_post_id'] = $post->ID;

		// add post multipage var
		$vars['cp_post_multipage'] = $multipage;

		// add post page var
		$vars['cp_post_page'] = $page;

		// add options title
		$vars['cp_options_title'] = __( 'Options', 'commentpress-core' );

		// --<
		return $vars;

	}



	/**
	 * Get new comments container
	 *
	 * @return void
	 */
	public function comments_get_container() {

		// init return
		$data = array();

		// access globals
		global $post, $multipage, $page, $pages;

		// get post ID
		$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : NULL;

		// make it an integer, just to be sure
		$post_id = (int) $post_id;

		// enable WordPress API on post
		$GLOBALS['post'] = get_post( $post_id );
		setup_postdata( $post );

		// default to post content
		$content = $post->post_content;

		// get page number
		$page_num = isset( $_POST['post_page'] ) ? $_POST['post_page'] : 1;
		if ( $page_num == 0 ) $page_num = 1;

		// override if multipage
		if ( $multipage ) $content = $pages[($page_num - 1)];

		// trigger CommentPress comments collation
		$content = apply_filters( 'the_content', $content );

		// add move button to the comment meta
		add_filter( 'cp_comment_edit_link', 'cpajax_add_reassign_button', 20, 2 );

		// get comments using buffer
		ob_start();

		// can't remember why we need this
		$vars = $this->parent_obj->db->get_javascript_vars();

		// first try to locate using WP method
		$cp_comments_by_para = apply_filters(
			'cp_template_comments_by_para',
			locate_template( 'assets/templates/comments_by_para.php' )
		);

		// load it if we find it
		if ( $cp_comments_by_para != '' ) load_template( $cp_comments_by_para );

		// get content
		$comments = ob_get_contents();

		// flush buffer
		ob_end_clean();

		// wrap in div
		$comments = '<div class="comments-for-' . $post->ID . '">' . $comments . '</div>';

		// add comments column
		$data['comments'] = $comments;
		$data['multipage'] = $multipage;
		$data['page'] = $page;
		//$data['pages'] = $pages;

		// send to browser
		$this->_send_json_data( $data );

	}



	/**
	 * Get new post metabox container
	 *
	 * @return void
	 */
	public function metabox_get_container() {

		// open div
		echo '<div class="metabox_container" style="display: none;">';

		// use common method
		$this->parent_obj->custom_box_page();

		// close div
		echo '</div>' . "\n\n";

	}



	/**
	 * AJAX metabox: set post title visibility
	 *
	 * @return void
	 */
	public function metabox_set_post_title_visibility() {

		// access globals
		global $post;

		// init return
		$data = array();

		// set up post
		if ( $this->_setup_post() ) {

			// save data
			$result = $this->db->save_page_title_visibility( $post );

			// construct data to return
			$data['error'] = 'success';
			$data['message'] = __( 'Option saved', 'commentpress-core' );
			$data['toggle'] = ( $result == 'show' ) ? 'show' : 'hide';

		} else {

			// define error
			$data['error'] = __( 'Could not save this option.', 'commentpress-core' );

		}

		// send to browser
		$this->_send_json_data( $data );

	}



	/**
	 * AJAX metabox: set page meta visibility
	 *
	 * @return void
	 */
	public function metabox_set_page_meta_visibility() {

		// access globals
		global $post;

		// init return
		$data = array();

		// set up post
		if ( $this->_setup_post() ) {

			// save data
			$result = $this->db->save_page_meta_visibility( $post );

			// construct data to return
			$data['error'] = 'success';
			$data['message'] = __( 'Option saved', 'commentpress-core' );
			$data['toggle'] = ( $result == 'hide' ) ? 'hide' : 'show';

		} else {

			// define error
			$data['error'] = __( 'Could not save this option.', 'commentpress-core' );

		}

		// send to browser
		$this->_send_json_data( $data );

	}



	/**
	 * AJAX metabox: set number format
	 *
	 * @return void
	 */
	public function metabox_set_number_format() {

		// access globals
		global $post;

		// init return
		$data = array();

		// set up post
		if ( $this->_setup_post() ) {

			// save data
			$this->db->save_page_numbering( $post );

			// init list
			$num = $this->parent_obj->nav->init_page_lists();

			// get page num
			$num = $this->parent_obj->nav->get_page_number( $post->ID );

			// construct data to return
			$data['error'] = 'success';
			$data['message'] = __( 'Option saved', 'commentpress-core' );
			$data['number'] = $num;

		} else {

			// define error
			$data['error'] = __( 'Could not save this option.', 'commentpress-core' );

		}

		// send to browser
		$this->_send_json_data( $data );

	}



	/**
	 * AJAX metabox: set formatter
	 *
	 * @return void
	 */
	public function metabox_set_post_type_override() {

		// access globals
		global $post;

		// init return
		$data = array();

		// set up post
		if ( $this->_setup_post() ) {

			// save data
			$this->db->save_formatter( $post );

			// construct data to return
			$data['error'] = 'success';
			$data['message'] = __( 'Option saved', 'commentpress-core' );

		} else {

			// define error
			$data['error'] = __( 'Could not save this option.', 'commentpress-core' );

		}

		// send to browser
		$this->_send_json_data( $data );

	}



	/**
	 * AJAX metabox: set starting paragraph number
	 *
	 * @return void
	 */
	public function metabox_set_starting_para_number() {

		// access globals
		global $post;

		// init return
		$data = array();

		// set up post
		if ( $this->_setup_post() ) {

			// save data
			$this->db->save_starting_paragraph( $post );

			// construct data to return
			$data['error'] = 'success';
			$data['message'] = __( 'Option saved', 'commentpress-core' );

		} else {

			// define error
			$data['error'] = __( 'Could not save this option.', 'commentpress-core' );

		}

		// send to browser
		$this->_send_json_data( $data );

	}



	/**
	 * Override the "Edit Page" link when WP FEE not active
	 *
	 * @param string $link The existing link
	 * @param int $id The numeric post ID
	 * @param string $context How to write ampersands. Default 'display' encodes as '&amp;'
	 * @return string $link The modified link
	 */
	function get_edit_post_link( $link, $id, $context ) {

		// test for WP FEE hash
		if ( $link == '#fee-edit-link' ) {

			// get url including anchor
			$url = get_permalink( $id ) . $link;

			// get link with nonce attached
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
	 * Set up post object from passed data
	 *
	 * @return bool True if post object set up, false otherwise
	 */
	private function _setup_post() {

		// access globals
		global $post;

		// get post ID
		$post_id = isset( $_POST['post_id'] ) ? (int) $_POST['post_id'] : NULL;

		// bail if we don't get one
		if ( is_null( $post_id ) ) return false;

		// bail if we get a non-numeric value
		if ( ! is_numeric( $post_id ) ) return false;

		// enable WordPress API on post
		$GLOBALS['post'] = get_post( $post_id );
		setup_postdata( $post );

		// success
		return true;

	}



	/**
	 * Send data to browser and exit
	 *
	 * @return void
	 */
	private function _send_json_data( $data ) {

		// set reasonable headers
		header('Content-type: text/plain');
		header("Cache-Control: no-cache");
		header("Expires: -1");

		// echo
		echo json_encode( $data );

		// die!
		exit();

	}



	/**
	 * Inject editor toggle before search in Contents column
	 *
	 * @return void
	 */
	private function _toggle_link() {

		// declare access to globals
		global $post;

		// change text depending on toggle state
		if ( $this->toggle_state == 'writing' ) {

			// link text
			$text = __( 'Switch to Commenting Mode', 'commentpress-core' );

			// link title
			$title = __( 'Switch to Commenting Mode', 'commentpress-core' );

		} else {

			// link text
			$text = __( 'Switch to Writing Mode', 'commentpress-core' );

			// link title
			$title = __( 'Switch to Writing Mode', 'commentpress-core' );

		}

		// link url
		$url = wp_nonce_url( get_permalink( $post->ID ), 'editor_toggle', 'cp_editor_nonce' );

		// link class
		$class = 'button';

		// construct link
		$link = '<a href="' . $url . '" class="' . $class . '" title="' . esc_attr( $title ) . '">' . $text . '</a>';

		// --<
		return apply_filters( 'commentpress_editor_toggle_link', $link, $text, $title, $url, $class );

	}



//##############################################################################



} // class ends



