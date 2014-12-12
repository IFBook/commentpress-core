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

		// kill WP FEE
		//$this->kill_wp_fee();

		// intercept toggles
		add_action( 'plugins_loaded', array( $this, 'initialise' ) );

		// --<
		return $this;

	}



	/**
	 * Set up all items associated with this object
	 *
	 * @return void
	 */
	public function initialise() {

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
	 * Prevent WordPress Front-end Editor from loading
	 *
	 * @return void
	 */
	public function kill_wp_fee() {

		// set flag
		$this->fee = 'killed';

		// define filename
		$class_file = 'commentpress-core/class_commentpress_fee.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// we're fine, include class definition
		require_once( $class_file_path );

	}



	/**
	 * Register WordPress hooks
	 *
	 * @return void
	 */
	public function register_hooks() {

		// bail if there's no WP FEE present
		if ( ! class_exists( 'FEE' ) ) return;

		// test for flag
		if ( isset( $this->fee ) AND $this->fee == 'killed' ) return;

		// intercept toggles
		add_action( 'init', array( $this, 'intercept_toggle' ) );

		// enable editor toggle
		add_action( 'cp_content_tab_before_search', array( $this, 'show_editor_toggle' ) );

		// prevent TinyMCE in comment form
		add_filter( 'cp_override_tinymce', array( $this, 'kill_commentpress_fee' ), 1000, 1 );
		add_filter( 'commentpress_is_tinymce_allowed', array( $this, 'disallow_commentpress_fee' ), 1000 );

		// test for AJAX
		if ( defined( 'DOING_AJAX' ) AND DOING_AJAX ) {

			// force filter the content during AJAX
			add_filter( 'commentpress_force_the_content', '__return_true' );

			// filter the content during AJAX
			add_filter( 'the_content', array( $this->parent_obj, 'the_content' ), 20 );

			/*
			if ( isset( $_POST['post_id'] ) ) {
				trigger_error( print_r( array(
					'method' => 'register_hooks',
				), true ), E_USER_ERROR ); die();
			}
			*/

		}

		// add AJAX functionality
		add_action( 'wp_ajax_cp_get_comments_container', array( $this, 'get_comments_container' ) );
		add_action( 'wp_ajax_nopriv_cp_get_comments_container', array( $this, 'get_comments_container' ) );

		// add vars to Javascript
		add_filter( 'commentpress_get_javascript_vars', array( $this, 'get_javascript_vars' ) );

	}



	/**
	 * Get new comments container
	 *
	 * @return void
	 */
	public function get_comments_container() {

		// init return
		$data = array();

		// access globals
		global $post, $multipage, $page, $pages;

		// get post ID
		$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : NULL;

		// make it an integer, just to be sure
		$post_id = (int) $post_id;

		// enable Wordpress API on post
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

		// get comments using buffer
		ob_start();

		// can't remember why we need this
		$vars = $this->parent_obj->db->get_javascript_vars();

		// include template
		include_once( get_template_directory() . '/assets/templates/comments_by_para.php' );

		// get content
		$comments = ob_get_contents();

		// flush buffer
		ob_end_clean();

		// wrap in div
		$comments = '<div class="comments-for-' . $post->ID . '">' . $comments . '</div>';

		/*
		if ( isset( $_POST['post_id'] ) ) {
			trigger_error( print_r( array(
				'post_id' => $post_id,
				//'GLOBALS[post]' => $GLOBALS['post'],
				'post' => $post,
				'content' => $content,
				//'comments' => $comments,
				'multipage' => $multipage,
				'page_num' => $page_num,
				'page' => $page,
				'pages' => $pages,
			), true ), E_USER_ERROR ); die();
		}
		*/

		// add comments column
		$data['comments'] = $comments;
		$data['multipage'] = $multipage;
		$data['page'] = $page;
		//$data['pages'] = $pages;

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
	 * Get additional javascript vars
	 *
	 * @param array $vars The existing variables to pass to our Javascript
	 * @return array $vars The modified variables to pass to our Javascript
	 */
	public function get_javascript_vars( $vars ) {

		// access globals
		global $post, $multipage, $page;

		// bail if we don't have a post (like on the 404 page)
		if ( ! is_object( $post ) ) return $vars;

		// we need to know the url of the Ajax handler
		$vars['cp_ajax_url'] = admin_url( 'admin-ajax.php' );

		// add the url of the animated loading bar gif
		$vars['cp_spinner_url'] = plugins_url( 'commentpress-ajax/assets/images/loading.gif', COMMENTPRESS_PLUGIN_FILE );

		// add post ID
		$vars['cp_post_id'] = $post->ID;

		// add post multipage var
		$vars['cp_post_multipage'] = $multipage;

		// add post page var
		$vars['cp_post_page'] = $page;

		// --<
		return $vars;

	}



	/**
	 * Prevent TinyMCE from loading in the comment form
	 *
	 * @return void
	 */
	public function disallow_commentpress_fee() {

		// do not allow
		return false;

	}



	/**
	 * Prevent TinyMCE from loading in the comment form
	 *
	 * @return void
	 */
	public function kill_commentpress_fee( $var ) {

		//var_dump( $var ); die();

		// do not show
		return 0;

	}



	/**
	 * Intercept editor toggling once plugins are loaded
	 *
	 * @return void
	 */
	public function intercept_toggle() {

		if (
			! isset( $_GET['cp_editor_nonce'] ) OR
			! wp_verify_nonce( $_GET['cp_editor_nonce'], 'editor_toggle' )
		) {

			// --<
			return;

		}

		//

	}



	/**
	 * Inject editor toggle link before search in Contents column
	 *
	 * @return void
	 */
	public function show_editor_toggle() {

		// bail if not commentable
		if ( ! $this->parent_obj->is_commentable() ) return;

		// define heading title
		$heading = apply_filters( 'cp_content_tab_editor_toggle_title', __( 'Mode', 'commentpress-core' ) );

		?>

		<h3 class="activity_heading"><?php echo $heading; ?></h3>

		<div class="paragraph_wrapper editor_toggle_wrapper">

		<div class="editor_toggle">
			<?php echo $this->toggle_link(); ?>
		</div><!-- /book_search -->

		</div>

		<?php

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Private Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Inject editor toggle before search in Contents column
	 *
	 * @return void
	 */
	private function toggle_link() {

		// declare access to globals
		global $post;

		// link text
		$link_text = __( 'Stop writing', 'commentpress-core' );

		// link text
		$link_title = __( 'Stop writing', 'commentpress-core' );

		// link url
		$link_url = wp_nonce_url( get_permalink( $post->ID ), 'editor_toggle', 'cp_editor_nonce' );

		// construct link
		$link = '<a href="' . $link_url . '" title="' . esc_attr( $link_title ) . '">' . $link_text . '</a>';

		// --<
		return apply_filters( 'commentpress_editor_toggle_link', $link, $link_text, $link_title, $link_url );

	}



//##############################################################################



} // class ends



