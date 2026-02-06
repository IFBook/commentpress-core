<?php
/**
 * CommentPress AJAX Comments class.
 *
 * Handles AJAX commenting functionality.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress AJAX Comments Class.
 *
 * This class provides AJAX commenting functionality.
 *
 * @since 4.0
 */
class CommentPress_AJAX_Comments {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_Core_Loader
	 */
	public $core;

	/**
	 * AJAX loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var CommentPress_AJAX_Loader
	 */
	public $ajax;

	/**
	 * Relative path to the assets directory.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $assets_path = 'includes/core/assets/';

	/**
	 * Form nonce action.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $nonce_action_form = 'cpajax_comment_nonce';

	/**
	 * "Edit Comment" nonce action.
	 *
	 * @since 4.0
	 * @access private
	 * @var string
	 */
	private $nonce_action_edit = 'cpajax_comment_edit_action';

	/**
	 * Constructor.
	 *
	 * @since 4.0
	 *
	 * @param CommentPress_AJAX_Loader $ajax Reference to the AJAX loader object.
	 */
	public function __construct( $ajax ) {

		// Store references to loader objects.
		$this->ajax = $ajax;
		$this->core = $this->ajax->core;

		// Init when this plugin is fully loaded.
		add_action( 'commentpress/ajax/loaded', [ $this, 'initialise' ] );

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

		// Add our Javascripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'scripts_enqueue' ], 120 );

		// Add a button to the Comment meta.
		add_filter( 'cp_comment_edit_link', [ $this, 'reassign_button_add' ], 20, 2 );

		// Add AJAX functionality.
		add_action( 'wp_ajax_cpajax_get_new_comments', [ $this, 'new_comments_get' ] );
		add_action( 'wp_ajax_nopriv_cpajax_get_new_comments', [ $this, 'new_comments_get' ] );

		// Add AJAX "Edit Comment" functionality.
		add_action( 'wp_ajax_cpajax_get_comment', [ $this, 'comment_get' ] );
		add_action( 'wp_ajax_cpajax_edit_comment', [ $this, 'comment_edit' ] );

		// Add AJAX reassign functionality.
		add_action( 'wp_ajax_cpajax_reassign_comment', [ $this, 'comment_reassign' ] );
		add_action( 'wp_ajax_nopriv_cpajax_reassign_comment', [ $this, 'comment_reassign' ] );

		/*
		// Remove Comment Flood filter if you want more 'chat-like' functionality.
		remove_filter('comment_flood_filter', 'wp_throttle_comment_flood', 10, 3);
		*/

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our scripts.
	 *
	 * @since 3.4
	 */
	public function scripts_enqueue() {

		// Access globals.
		global $post;

		// Can only now see $post.
		if ( ! $this->ajax->can_activate() ) {
			return;
		}

		// Init vars.
		$vars = [];

		// Add a nonce.
		$vars['cpajax_nonce'] = wp_create_nonce( $this->nonce_action_form );

		// Is "live" Comment refreshing enabled?
		$vars['cpajax_live'] = $this->core->comments->setting_live_get();

		// We need to know the url of the Ajax handler.
		$vars['cpajax_ajax_url'] = admin_url( 'admin-ajax.php' );

		// Add the url of the animated loading bar gif.
		$vars['cpajax_spinner_url'] = plugins_url( $this->assets_path . 'images/loading.gif', COMMENTPRESS_PLUGIN_FILE );

		// Time formatted thus: 2009-08-09 14:46:14.
		$vars['cpajax_current_time'] = gmdate( 'Y-m-d H:i:s' );

		// Get Comment count at the time the Page is served.
		$count = get_comment_count( $post->ID );

		// Add approved Comment count.
		$vars['cpajax_comment_count'] = (int) $count['approved'];

		/*
		// Add moderation queue as well, since we do show these.
		//$vars['cpajax_comment_count'] += (int) $count['awaiting_moderation'];
		*/

		// Add Post ID.
		$vars['cpajax_post_id'] = $post->ID;

		// Add Post Comment status.
		$vars['cpajax_post_comment_status'] = $post->comment_status;

		// Get translations array.
		$vars['cpajax_lang'] = $this->scripts_localise();

		// Comment refresh interval, in milliseconds.
		$vars['cpajax_comment_refresh_interval'] = 5000;

		/**
		 * Filter the Javascript vars.
		 *
		 * @since 3.9.6
		 *
		 * @param array $vars The array of Javascript vars to be passed to the script.
		 */
		$vars = apply_filters( 'cpajax_javascript_vars', $vars );

		// Default to minified scripts.
		$min = commentpress_minified();

		// Are we asking for Comments-in-Page?
		if ( $this->core->pages_legacy->is_special_page() ) {

			// Add Comments-in-Page script.
			wp_enqueue_script(
				'cpajax',
				plugins_url( $this->assets_path . 'js/cp-ajax-comments-page' . $min . '.js', COMMENTPRESS_PLUGIN_FILE ),
				null, // No dependencies.
				COMMENTPRESS_VERSION, // Version.
				true
			);

		} else {

			// Add Comments-in-Sidebar script.
			wp_enqueue_script(
				'cpajax',
				plugins_url( $this->assets_path . 'js/cp-ajax-comments' . $min . '.js', COMMENTPRESS_PLUGIN_FILE ),
				[ 'jquery-ui-droppable', 'jquery-ui-dialog' ], // Load droppable and dialog as dependencies.
				COMMENTPRESS_VERSION, // Version.
				true
			);

			// Add WordPress dialog CSS.
			wp_enqueue_style( 'wp-jquery-ui-dialog' );

		}

		// Use wp function to localise.
		wp_localize_script( 'cpajax', 'CommentPress_AJAX_Settings', $vars );

	}

	/**
	 * Enable translation in the Javascript.
	 *
	 * @since 3.4
	 *
	 * @return array $translations The array of translations to pass to the script.
	 */
	private function scripts_localise() {

		// Init array.
		$translations = [];

		// Add translations for Comment form.
		$translations[] = __( 'Loading...', 'commentpress-core' );
		$translations[] = __( 'Please enter your name.', 'commentpress-core' );
		$translations[] = __( 'Please enter your email address.', 'commentpress-core' );
		$translations[] = __( 'Please enter a valid email address.', 'commentpress-core' );
		$translations[] = __( 'Please enter your comment.', 'commentpress-core' );
		$translations[] = __( 'Your comment has been added.', 'commentpress-core' );
		$translations[] = __( 'AJAX error!', 'commentpress-core' );

		// Add translations for Comment reassignment.
		$translations[] = __( 'Are you sure?', 'commentpress-core' );
		$translations[] = __( 'Are you sure you want to assign the comment and its replies to the textblock? This action cannot be undone.', 'commentpress-core' );
		$translations[] = __( 'Submitting...', 'commentpress-core' );
		$translations[] = __( 'Please wait while the comments are reassigned. The page will refresh when this has been done.', 'commentpress-core' );

		// Add translations for Comment word.
		// Singular.
		$translations[] = __( 'Comment', 'commentpress-core' );
		// Plural.
		$translations[] = __( 'Comments', 'commentpress-core' );

		// --<
		return $translations;

	}

	// -------------------------------------------------------------------------

	/**
	 * Get a Comment in response to an AJAX request.
	 *
	 * @since 3.9.12
	 */
	public function comment_get() {

		// Init return.
		$data = [
			'id' => false,
		];

		// Since this is an AJAX request, check security.
		$result = check_ajax_referer( $this->nonce_action_form, false, false );
		if ( false === $result ) {
			wp_send_json( $data );
		}

		// Get incoming data.
		$comment_id = isset( $_POST['comment_id'] ) ? sanitize_text_field( wp_unslash( $_POST['comment_id'] ) ) : false;

		// Sanity check.
		if ( ! empty( $comment_id ) && is_numeric( $comment_id ) ) {

			// Get Comment.
			$comment = get_comment( (int) $comment_id );

			// Add Comment data to array.
			$data = [
				'id'       => $comment->comment_ID,
				'parent'   => $comment->comment_parent,
				'text_sig' => $comment->comment_signature,
				'post_id'  => $comment->comment_post_ID,
				'content'  => $comment->comment_content,
			];

			// Get selection data.
			$selection_data = get_comment_meta( $comment_id, '_cp_comment_selection', true );

			// If there is selection data.
			if ( ! empty( $selection_data ) ) {

				// Make into an array.
				$selection = explode( ',', $selection_data );

				// Add to data.
				$data['sel_start'] = $selection[0];
				$data['sel_end']   = $selection[1];

			} else {

				// Add default data.
				$data['sel_start'] = 0;
				$data['sel_end']   = 0;

			}

			// Add nonce or verification.
			$data['nonce'] = wp_create_nonce( $this->nonce_action_form );

		}

		/**
		 * Filter the data returned to the calling script.
		 *
		 * @since 3.9.12
		 *
		 * @param array $data The array of Comment data.
		 */
		$data = apply_filters( 'commentpress_ajax_get_comment', $data );

		// Send data to browser.
		wp_send_json( $data );

	}

	/**
	 * Edit a Comment in response to an AJAX request.
	 *
	 * @see CommentPress_Multisite_BuddyPress::enable_comment_editing()
	 *
	 * @since 3.9.12
	 */
	public function comment_edit() {

		// TODO: Check permissions.

		// Init return.
		$data = [
			'id' => false,
		];

		// Bail if authentication fails.
		$nonce = isset( $_POST[ $this->nonce_action_form ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->nonce_action_form ] ) ) : '';
		if ( ! wp_verify_nonce( $nonce, $this->nonce_action_form ) ) {
			wp_send_json( $data );
		}

		// Get incoming Comment ID.
		$comment_id = isset( $_POST['comment_ID'] ) ? sanitize_text_field( wp_unslash( $_POST['comment_ID'] ) ) : null;
		if ( empty( $comment_id ) || ! is_numeric( $comment_id ) ) {
			wp_send_json( $data );
		}

		// Construct Comment data. Comment content is run through the same filtering as WordPress core.
		$comment_data = [
			'comment_ID'      => (int) $comment_id,
			// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			'comment_content' => isset( $_POST['comment'] ) ? trim( wp_unslash( $_POST['comment'] ) ) : '',
			'comment_post_ID' => isset( $_POST['comment_post_ID'] ) ? sanitize_text_field( wp_unslash( $_POST['comment_post_ID'] ) ) : '',
		];

		// Update the Comment.
		wp_update_comment( $comment_data );

		// Get the fresh Comment data.
		$comment = get_comment( $comment_data['comment_ID'] );

		// Add Comment data to array.
		$data = [
			'id'       => $comment->comment_ID,
			'parent'   => $comment->comment_parent,
			'text_sig' => $comment->comment_signature,
			'post_id'  => $comment->comment_post_ID,
			'content'  => apply_filters( 'comment_text', get_comment_text( $comment->comment_ID ) ),
		];

		// Get selection data.
		$selection_data = get_comment_meta( $comment_id, '_cp_comment_selection', true );

		// If there is selection data.
		if ( ! empty( $selection_data ) ) {

			// Make into an array.
			$selection = explode( ',', $selection_data );

			// Add to data.
			$data['sel_start'] = $selection[0];
			$data['sel_end']   = $selection[1];

		} else {

			// Add default data.
			$data['sel_start'] = 0;
			$data['sel_end']   = 0;

		}

		/**
		 * Filter the data returned to the calling script.
		 *
		 * @since 3.9.12
		 *
		 * @param array $data The array of Comment data.
		 */
		$data = apply_filters( 'commentpress_ajax_edited_comment', $data );

		// Send data to browser.
		wp_send_json( $data );

	}

	/**
	 * Get new Comments in response to an AJAX request.
	 *
	 * @since 3.4
	 */
	public function new_comments_get() {

		// Init return.
		$data = [];

		// Since this is an AJAX request, check security.
		$result = check_ajax_referer( $this->nonce_action_form, false, false );
		if ( false === $result ) {
			wp_send_json( $data );
		}

		// Get incoming data.
		$last_comment_count = isset( $_POST['last_count'] ) ? sanitize_text_field( wp_unslash( $_POST['last_count'] ) ) : null;

		// Store incoming unless updated later.
		$data['cpajax_comment_count'] = (int) $last_comment_count;

		// Get Post ID.
		$post_id = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : null;

		// Make it an integer, just to be sure.
		$post_id = (int) $post_id;

		// Get any Comments posted since last update time.
		$data['cpajax_new_comments'] = [];

		// Get current array.
		$current_comment_count_array = get_comment_count( $post_id );

		// Get approved -> do we want others?
		$current_comment_count = $current_comment_count_array['approved'];

		// Get number of new Comments to fetch.
		$num_to_get = (int) $current_comment_count - (int) $last_comment_count;

		// Are there any?
		if ( $num_to_get <= 0 ) {
			wp_send_json( $data );
		}

		// Update Comment count since last request.
		$data['cpajax_comment_count'] = (string) $current_comment_count;

		// Build arguments.
		$args = [
			'number'  => $num_to_get,
			'orderby' => 'comment_date',
			'order'   => 'DESC',
			'post_id' => $post_id,
			'status'  => 'approve',
			'type'    => 'comment',
		];

		// Get the Comments.
		$comments = get_comments( $args );

		// Bail if we don't get any.
		if ( empty( $comments ) ) {
			wp_send_json( $data );
		}

		// Init identifier.
		$identifier = 1;

		// Set args.
		$args              = [];
		$args['max_depth'] = get_option( 'thread_comments_depth' );

		// Loop.
		foreach ( $comments as $comment ) {

			// Assume top level.
			$depth = 1;

			// Override depth if no parent.
			if ( ! empty( $comment->comment_parent ) ) {
				$depth = $this->comment_depth_get( $comment, $depth );
			}

			// Get Comment markup.
			$html = commentpress_get_comment_markup( $comment, $args, $depth );

			// Close li (walker would normally do this).
			$html .= '</li>' . "\n\n\n\n";

			// Add Comment to array.
			$data[ 'cpajax_new_comment_' . $identifier ] = [
				'parent'   => $comment->comment_parent,
				'id'       => $comment->comment_ID,
				'text_sig' => $comment->comment_signature,
				'markup'   => $html,
			];

			// Increment.
			$identifier++;

		}

		// Send data to browser.
		wp_send_json( $data );

	}

	/**
	 * Gets the Comment depth.
	 *
	 * @since 3.4
	 *
	 * @param WP_Comment $comment The WordPress Comment object.
	 * @param int        $depth The depth of the Comment in a thread.
	 * @return int $depth The depth of the Comment in a thread.
	 */
	public function comment_depth_get( $comment, $depth ) {

		// Is parent top level?
		if ( 0 === (int) $comment->comment_parent ) {
			return $depth;
		}

		// Get parent Comment.
		$parent = get_comment( $comment->comment_parent );

		// Increase depth.
		$depth++;

		// Recurse.
		return $this->comment_depth_get( $parent, $depth );

	}

	/**
	 * Add "reassign" button to Comment utilities.
	 *
	 * @since 3.4
	 *
	 * @param string $edit_button The existing edit button HTML.
	 * @param array  $comment The Comment this edit button applies to.
	 * @return string $edit_button The modified edit button HTML.
	 */
	public function reassign_button_add( $edit_button, $comment ) {

		// Pass if not top level.
		if ( ! empty( $comment->comment_parent ) ) {
			return $edit_button;
		}

		// Pass if pingback or trackback.
		if ( 'trackback' === $comment->comment_type || 'pingback' === $comment->comment_type ) {
			return $edit_button;
		}

		/*
		// Pass if not orphan.
		if ( ! isset( $comment->orphan ) ) {
			return $edit_button;
		}
		*/

		/**
		 * Filters default "Move Comment" link title text.
		 *
		 * @since 3.4
		 *
		 * @param string The default "Move Comment" link title text.
		 */
		$title_text = apply_filters( 'cpajax_comment_assign_link_title_text', __( 'Drop on to a text-block to reassign this comment (and any replies) to it', 'commentpress-core' ) );

		/**
		 * Filters default "Move Comment" link text.
		 *
		 * @since 3.4
		 *
		 * @param string The default "Move Comment" link text.
		 */
		$text = apply_filters( 'cp_comment_assign_link_text', __( 'Move', 'commentpress-core' ) );

		// Construct assign button.
		$assign_button = '<span class="alignright comment-assign" title="' . $title_text . '" id="cpajax_assign-' . $comment->comment_ID . '">' .
			$text .
		'</span>';

		// Add our assign button.
		$edit_button .= $assign_button;

		// --<
		return $edit_button;

	}

	/**
	 * Change a comment's text-signature.
	 *
	 * @since 3.4
	 */
	public function comment_reassign() {

		// Init return.
		$data        = [];
		$data['msg'] = '';

		// Since this is an AJAX request, check security.
		$result = check_ajax_referer( $this->nonce_action_form, false, false );
		if ( false === $result ) {
			wp_send_json( $data );
		}

		// Init checker.
		$comment_ids = [];

		// Get incoming data.
		$text_sig   = isset( $_POST['text_signature'] ) ? sanitize_text_field( wp_unslash( $_POST['text_signature'] ) ) : '';
		$comment_id = isset( $_POST['comment_id'] ) ? sanitize_text_field( wp_unslash( $_POST['comment_id'] ) ) : '';

		// Sanity check.
		if ( ! empty( $text_sig ) && ! empty( $comment_id ) ) {

			// Store Text Signature.
			$this->core->comments->save_comment_signature( $comment_id, $text_sig );

			// Trace.
			$comment_ids[] = (int) $comment_id;

			// Recurse for any Comment children.
			$this->comment_children_reassign( (int) $comment_id, $text_sig, $comment_ids );

		}

		// Add message.
		$data['msg'] .= 'comments ' . implode( ', ', $comment_ids ) . ' updated' . "\n";

		// Send data to browser.
		wp_send_json( $data );

	}

	/**
	 * Store Text Signature for all children of a Comment.
	 *
	 * @since 3.4
	 *
	 * @param int    $comment_id The numeric ID of the Comment.
	 * @param string $text_sig The Text Signature.
	 * @param array $comment_ids The array of Comment IDs.
	 */
	public function comment_children_reassign( $comment_id, $text_sig, &$comment_ids ) {

		// Get the children of the Comment.
		$children = $this->comment_children_get( $comment_id );

		// Did we get any?
		if ( ! empty( $children ) ) {

			// Loop.
			foreach ( $children as $child ) {

				// Store Text Signature.
				$this->core->comments->save_comment_signature( $child->comment_ID, $text_sig );

				// Trace.
				$comment_ids[] = $child->comment_ID;

				// Recurse for any Comment children.
				$this->comment_children_reassign( $child->comment_ID, $text_sig, $comment_ids );

			}

		}

	}

	/**
	 * Retrieve Comment children.
	 *
	 * @since 3.4
	 *
	 * @param int $comment_id The numeric ID of the Comment.
	 * @return array $children The array of child Comments.
	 */
	public function comment_children_get( $comment_id ) {

		// Build query args.
		$args = [
			'parent'  => $comment_id,
			'orderby' => 'comment_date',
			'order'   => 'ASC',
		];

		// Get the child Comments.
		$children = get_comments( $args );

		// --<
		return $children;

	}

}
