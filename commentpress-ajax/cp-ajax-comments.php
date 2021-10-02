<?php /*
================================================================================
CommentPress Core AJAX Comments
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

--------------------------------------------------------------------------------
*/



// Enable the plugin at the appropriate point.
add_action( 'wp', 'cpajax_enable_plugin' );

// Always add AJAX functionality.
add_action( 'wp_ajax_cpajax_get_new_comments', 'cpajax_get_new_comments' );
add_action( 'wp_ajax_nopriv_cpajax_get_new_comments', 'cpajax_get_new_comments' );

// Add AJAX edit comment functionality.
add_action( 'wp_ajax_cpajax_get_comment', 'cpajax_get_comment' );
add_action( 'wp_ajax_cpajax_edit_comment', 'cpajax_edit_comment' );

// Remove comment flood filter if you want more 'chat-like' functionality.
//remove_filter('comment_flood_filter', 'wp_throttle_comment_flood', 10, 3);

// Add AJAX reassign functionality.
add_action( 'wp_ajax_cpajax_reassign_comment', 'cpajax_reassign_comment' );
add_action( 'wp_ajax_nopriv_cpajax_reassign_comment', 'cpajax_reassign_comment' );

// Prevent infinite scroll by default.
add_filter( 'cpajax_disable_infinite_scroll', '__return_true' );

// Add AJAX infinite scroll functionality.
add_action( 'wp_ajax_cpajax_load_next_page', 'cpajax_infinite_scroll_load_next_page' );
add_action( 'wp_ajax_nopriv_cpajax_load_next_page', 'cpajax_infinite_scroll_load_next_page' );



/**
 * Get context in which to enable this plugin.
 *
 * @since 3.4
 */
function cpajax_enable_plugin() {

	// Access globals.
	global $commentpress_core;

	// Kick out if cp is not enabled.
	if ( is_null( $commentpress_core ) OR ! is_object( $commentpress_core ) ) return;

	// Kick out if we're in the WP back end.
	if ( is_admin() ) return;

	// Add our javascripts.
	add_action( 'wp_enqueue_scripts', 'cpajax_add_javascripts', 120 );

	// Add a button to the comment meta.
	add_filter( 'cp_comment_edit_link', 'cpajax_add_reassign_button', 20, 2 );

}



/**
 * Add our plugin javascripts.
 *
 * @since 3.4
 */
function cpajax_add_javascripts() {

	// Access globals.
	global $post, $commentpress_core;

	// Can only now see $post.
	if ( ! cpajax_plugin_can_activate() ) return;

	// Init vars.
	$vars = [];

	// Is "live" comment refreshing enabled?
	$vars['cpajax_live'] = ( $commentpress_core->db->option_get( 'cp_para_comments_live' ) == '1' ) ? 1 : 0;

	// We need to know the url of the Ajax handler.
	$vars['cpajax_ajax_url'] = admin_url( 'admin-ajax.php' );

	// Add the url of the animated loading bar gif.
	$vars['cpajax_spinner_url'] = plugins_url( 'commentpress-ajax/assets/images/loading.gif', COMMENTPRESS_PLUGIN_FILE );

	// Time formatted thus: 2009-08-09 14:46:14
	$vars['cpajax_current_time'] = date('Y-m-d H:i:s');

	// Get comment count at the time the page is served.
	$count = get_comment_count( $post->ID );

	// Adding moderation queue as well, since we do show these.
	$vars['cpajax_comment_count'] = $count['approved']; // + $count['awaiting_moderation'];

	// Add post ID.
	$vars['cpajax_post_id'] = $post->ID;

	// Add post comment status.
	$vars['cpajax_post_comment_status'] = $post->comment_status;

	// Get translations array.
	$vars['cpajax_lang'] = cpajax_localise();

	// Comment refresh interval, in milliseconds.
	$vars['cpajax_comment_refresh_interval'] = 5000;

	/**
	 * Allow Javascript vars to be filtered.
	 *
	 * @since 3.9.6
	 *
	 * @param array $vars The existing localisation array.
	 * @return array $vars The modified localisation array.
	 */
	$vars = apply_filters( 'cpajax_javascript_vars', $vars );

	// Default to minified scripts.
	$debug_state = commentpress_minified();

	// Are we asking for in-page comments?
	if ( $commentpress_core->db->is_special_page() ) {

		// Add comments in page script.
		wp_enqueue_script(
			'cpajax',
			plugins_url( 'commentpress-ajax/assets/js/cp-ajax-comments-page' . $debug_state . '.js', COMMENTPRESS_PLUGIN_FILE ),
			null, // No dependencies.
			COMMENTPRESS_VERSION // Version.
		);

	} else {

		// Add comments in sidebar script.
		wp_enqueue_script(
			'cpajax',
			plugins_url( 'commentpress-ajax/assets/js/cp-ajax-comments' . $debug_state . '.js', COMMENTPRESS_PLUGIN_FILE ),
			[ 'jquery-ui-droppable', 'jquery-ui-dialog' ], // Load droppable and dialog as dependencies.
			COMMENTPRESS_VERSION // Version.
		);

		// Add WordPress dialog CSS.
		wp_enqueue_style( 'wp-jquery-ui-dialog' );

	}

	// Use wp function to localise.
	wp_localize_script( 'cpajax', 'CommentpressAjaxSettings', $vars );

	// Include infinite scroll scripts.
	cpajax_infinite_scroll_scripts();

}



/**
 * Enable translation in the Javascript.
 *
 * @since 3.4
 *
 * @return array $translations The array of translations to pass to the script.
 */
function cpajax_localise() {

	// Init array.
	$translations = [];

	// Add translations for comment form.
	$translations[] = __( 'Loading...', 'commentpress-core' );
	$translations[] = __( 'Please enter your name.', 'commentpress-core' );
	$translations[] = __( 'Please enter your email address.', 'commentpress-core' );
	$translations[] = __( 'Please enter a valid email address.', 'commentpress-core' );
	$translations[] = __( 'Please enter your comment.', 'commentpress-core' );
	$translations[] = __( 'Your comment has been added.', 'commentpress-core' );
	$translations[] = __( 'AJAX error!', 'commentpress-core' );

	// Add translations for comment reassignment.
	$translations[] = __( 'Are you sure?', 'commentpress-core' );
	$translations[] = __( 'Are you sure you want to assign the comment and its replies to the textblock? This action cannot be undone.', 'commentpress-core' );
	$translations[] = __( 'Submitting...', 'commentpress-core' );
	$translations[] = __( 'Please wait while the comments are reassigned. The page will refresh when this has been done.', 'commentpress-core' );

	// Add translations for comment word.
	// Singular.
	$translations[] = __( 'Comment', 'commentpress-core' );
	// Plural.
	$translations[] = __( 'Comments', 'commentpress-core' );

	// --<
	return $translations;

}



/**
 * Validate that the plugin can be activated.
 *
 * @return bool $allowed True if the plugin can activate, false otherwise.
 */
function cpajax_plugin_can_activate() {

	// Access globals.
	global $post, $commentpress_core;

	// Disallow if no post ID (such as 404).
	if ( ! is_object( $post ) ) return false;

	// It's the Theme My Login page.
	if ( $commentpress_core->is_theme_my_login_page() ) return false;

	// Init.
	$allowed = true;

	// Disallow generally if page doesn't allow commenting.
	if ( ! $commentpress_core->is_commentable() )  { $allowed = false; }

	// But, allow general comments page.
	if ( $commentpress_core->db->option_get( 'cp_general_comments_page' ) == $post->ID ) { $allowed = true; }

	// --<
	return $allowed;

}



//##############################################################################



/**
 * Get a comment in response to an AJAX request.
 *
 * @since 3.9.12
 */
function cpajax_get_comment() {

	// Init return.
	$data = [];

	// Get incoming data.
	$comment_id = isset( $_POST['comment_id'] ) ? absint( $_POST['comment_id'] ) : NULL;

	// Sanity check.
	if ( ! is_null( $comment_id ) ) {

		// Get comment.
		$comment = get_comment( $comment_id );

		// Add comment data to array.
		$data = [
			'id' => $comment->comment_ID,
			'parent' => $comment->comment_parent,
			'text_sig' => $comment->comment_signature,
			'post_id' => $comment->comment_post_ID,
			'content' => $comment->comment_content,
		];

		// Get selection data.
		$selection_data = get_comment_meta( $comment_id, '_cp_comment_selection', true );

		// If there is selection data.
		if ( ! empty( $selection_data ) ) {

			// Make into an array.
			$selection = explode( ',', $selection_data );

			// Add to data.
			$data['sel_start'] = $selection[0];
			$data['sel_end'] = $selection[1];

		} else {

			// Add default data.
			$data['sel_start'] = 0;
			$data['sel_end'] = 0;

		}

		// Add nonce or verification.
		$data['nonce'] = wp_create_nonce( 'cpajax_comment_nonce' );

	}

	/**
	 * Filter the data returned to the calling script.
	 *
	 * @since 3.9.12
	 *
	 * @param array $data The array of comment data.
	 * @return array $data The modified array of comment data.
	 */
	$data = apply_filters( 'commentpress_ajax_get_comment', $data );

	// Set reasonable headers.
	header('Content-type: text/plain');
	header("Cache-Control: no-cache");
	header("Expires: -1");

	// Echo.
	echo json_encode( $data );

	// Die.
	exit();

}



/**
 * Edit a comment in response to an AJAX request.
 *
 * @since 3.9.12
 */
function cpajax_edit_comment() {

	// Init return.
	$data = [];

	// Authenticate.
	$nonce = isset( $_POST['cpajax_comment_nonce'] ) ? $_POST['cpajax_comment_nonce'] : '';
	if ( ! wp_verify_nonce( $nonce, 'cpajax_comment_nonce' ) ) {

		// Skip.

	} else {

		// Get incoming comment ID.
		$comment_id = isset( $_POST['comment_ID'] ) ? absint( $_POST['comment_ID'] ) : NULL;

		// Sanity check.
		if ( ! is_null( $comment_id ) ) {

			// Construct comment data.
			$comment_data = [
				'comment_ID' => $comment_id,
				'comment_content' => isset( $_POST['comment'] ) ? trim( $_POST['comment'] ) : '',
				'comment_post_ID' => isset( $_POST['comment_post_ID'] ) ? absint( $_POST['comment_post_ID'] ) : '',
			];

			// Update the comment.
			wp_update_comment( $comment_data );

			// Get the fresh comment data.
			$comment = get_comment( $comment_data['comment_ID'] );

			// Access plugin.
			global $commentpress_core;

			// Get text signature.
			$comment_signature = $commentpress_core->db->get_text_signature_by_comment_id( $comment->comment_ID );

			// Add comment data to array.
			$data = [
				'id' => $comment->comment_ID,
				'parent' => $comment->comment_parent,
				'text_sig' => $comment_signature,
				'post_id' => $comment->comment_post_ID,
				'content' => apply_filters( 'comment_text', get_comment_text( $comment->comment_ID ) ),
			];

			// Get selection data.
			$selection_data = get_comment_meta( $comment_id, '_cp_comment_selection', true );

			// If there is selection data.
			if ( ! empty( $selection_data ) ) {

				// Make into an array.
				$selection = explode( ',', $selection_data );

				// Add to data.
				$data['sel_start'] = $selection[0];
				$data['sel_end'] = $selection[1];

			} else {

				// Add default data.
				$data['sel_start'] = 0;
				$data['sel_end'] = 0;

			}

		} // End check for comment ID.

		/**
		 * Filter the data returned to the calling script.
		 *
		 * @since 3.9.12
		 *
		 * @param array $data The array of comment data.
		 * @return array $data The modified array of comment data.
		 */
		$data = apply_filters( 'commentpress_ajax_edited_comment', $data );

	} // End nonce check.

	// Set reasonable headers.
	header('Content-type: text/plain');
	header("Cache-Control: no-cache");
	header("Expires: -1");

	// Echo.
	echo json_encode( $data );

	// Die.
	exit();

}



//##############################################################################



/**
 * Get new comments in response to an AJAX request.
 *
 * @since 3.4
 */
function cpajax_get_new_comments() {

	// Init return.
	$data = [];

	// Get incoming data.
	$last_comment_count = isset( $_POST['last_count'] ) ? $_POST['last_count'] : NULL;

	// Store incoming unless updated later.
	$data['cpajax_comment_count'] = $last_comment_count;

	// Get post ID.
	$post_id = isset( $_POST['post_id'] ) ? $_POST['post_id'] : NULL;

	// Make it an integer, just to be sure.
	$post_id = (int) $post_id;

	// Enable WordPress API on post.
	$GLOBALS['post'] = get_post( $post_id );

	// Get any comments posted since last update time.
	$data['cpajax_new_comments'] = [];

	// Get current array.
	$current_comment_count_array = get_comment_count( $post_id );

	// Get approved -> do we want others?
	$current_comment_count = $current_comment_count_array['approved'];

	// Get number of new comments to fetch.
	$num_to_get = (int) $current_comment_count - (int) $last_comment_count;

	// Are there any?
	if ( $num_to_get > 0 ) {

		// Update comment count since last request.
		$data['cpajax_comment_count'] = (string) $current_comment_count;

		// Set get_comments defaults.
		$defaults = [
			'number' => $num_to_get,
			'orderby' => 'comment_date',
			'order' => 'DESC',
			'post_id' => $post_id,
			'status' => 'approve',
			'type' => 'comment',
		];

		// Get them.
		$comments = get_comments( $defaults );

		// If we get some - again, just to be sure.
		if ( count( $comments ) > 0 ) {

			// Init identifier.
			$identifier = 1;

			// Set args.
			$args = [];
			$args['max_depth'] = get_option( 'thread_comments_depth' );

			// Loop.
			foreach( $comments AS $comment ) {

				// Assume top level.
				$depth = 1;

				// If no parent.
				if ( $comment->comment_parent != '0' ) {

					// Override depth.
					$depth = cpajax_get_comment_depth( $comment, $depth );

				}

				// Get comment markup.
				$html = commentpress_get_comment_markup( $comment, $args, $depth );

				// Close li (walker would normally do this).
				$html .= '</li>' . "\n\n\n\n";

				// Add comment to array.
				$data['cpajax_new_comment_' . $identifier] = [
					'parent' => $comment->comment_parent,
					'id' => $comment->comment_ID,
					'text_sig' => $comment->comment_signature,
					'markup' => $html,
				];

				// Increment.
				$identifier++;

			}

		}

	}

	// Set reasonable headers.
	header('Content-type: text/plain');
	header("Cache-Control: no-cache");
	header("Expires: -1");

	// Echo.
	echo json_encode( $data );

	// Die.
	exit();

}



/**
 * Get comment depth.
 *
 * @since 3.4
 *
 * @return int $depth The depth of the comment in a thread.
 */
function cpajax_get_comment_depth( $comment, $depth ) {

	// Is parent top level?
	if ( $comment->comment_parent == '0' ) {

		// --<
		return $depth;

	}

	// Get parent comment.
	$parent = get_comment( $comment->comment_parent );

	// Increase depth.
	$depth++;

	// Recurse.
	return cpajax_get_comment_depth( $parent, $depth );

}



//##############################################################################



/**
 * Add "reassign" button to comment utilities.
 *
 * @since 3.4
 *
 * @param str $edit_button The existing edit button HTML.
 * @param array $comment The comment  this edit button applies to.
 * @return str $edit_button The modified edit button HTML.
 */
function cpajax_add_reassign_button( $edit_button, $comment ) {

	// Pass if not top level.
	if ( $comment->comment_parent != '0' ) return $edit_button;

	// Pass if pingback or trackback.
	if ( $comment->comment_type == 'trackback' OR $comment->comment_type == 'pingback' ) return $edit_button;

	// Pass if not orphan.
	//if ( ! isset( $comment->orphan ) ) return $edit_button;

	// Set default edit link title text.
	$title_text = apply_filters(
		'cpajax_comment_assign_link_title_text',
		__( 'Drop on to a text-block to reassign this comment (and any replies) to it', 'commentpress-core' )
	);

	// Set default edit link text.
	$text = apply_filters(
		'cp_comment_assign_link_text',
		__( 'Move', 'commentpress-core' )
	);

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
function cpajax_reassign_comment() {

	global $data;

	// Init return.
	$data = [];
	$data['msg'] = '';

	// Init checker.
	$comment_ids = [];

	// Get incoming data.
	$text_sig = isset( $_POST['text_signature'] ) ? $_POST['text_signature'] : '';
	$comment_id = isset( $_POST['comment_id'] ) ? $_POST['comment_id'] : '';

	// Sanity check.
	if ( $text_sig !== '' AND $comment_id !== '' ) {

		// Access globals.
		global $commentpress_core;

		// Store text signature.
		$commentpress_core->db->save_comment_signature( $comment_id );

		// Trace.
		$comment_ids[] = $comment_id;

		// Recurse for any comment children.
		cpajax_reassign_comment_children( $comment_id, $text_sig, $comment_ids );

	}

	// Add message.
	$data['msg'] .= 'comments ' . implode( ', ', $comment_ids ) . ' updated' . "\n";

	// Set reasonable headers.
	header('Content-type: text/plain');
	header("Cache-Control: no-cache");
	header("Expires: -1");

	// Echo.
	echo json_encode( $data );

	// Die.
	exit();

}



/**
 * Store text signature for all children of a comment.
 *
 * @since 3.4
 */
function cpajax_reassign_comment_children( $comment_id, $text_sig, &$comment_ids ) {

	// Get the children of the comment.
	$children = cpajax_get_children( $comment_id );

	// Did we get any?
	if ( count( $children ) > 0 ) {

		// Loop.
		foreach( $children AS $child ) {

			// Access globals.
			global $commentpress_core;

			// Store text signature.
			$commentpress_core->db->save_comment_signature( $child->comment_ID );

			// Trace.
			$comment_ids[] = $child->comment_ID;

			// Recurse for any comment children.
			cpajax_reassign_comment_children( $child->comment_ID, $text_sig, $comment_ids );

		}

	}

}



/**
 * Retrieve comment children.
 *
 * @since 3.4
 *
 * @param int $comment_id The numeric ID of the comment.
 * @return array $children The array of child comments.
 */
function cpajax_get_children( $comment_id ) {

	// Declare access to globals.
	global $wpdb;

	// Construct query for comment children.
	$query = "
	SELECT *
	FROM $wpdb->comments
	WHERE comment_parent = '$comment_id'
	ORDER BY comment_date ASC
	";

	// --<
	return $wpdb->get_results( $query );

}



//##############################################################################



/**
 * Add Infinite Scroll Javascript.
 *
 * @since 3.4
 */
function cpajax_infinite_scroll_scripts() {

	// Allow this to be disabled.
	if ( apply_filters( 'cpajax_disable_infinite_scroll', false ) ) return;

	// Always load the comment form, even if comments are disabled.
	add_filter( 'commentpress_force_comment_form', '__return_true' );

	// Access globals.
	global $post, $commentpress_core;

	// Default to minified scripts.
	$debug_state = commentpress_minified();

	// Bail if we are we asking for in-page comments.
	if ( $commentpress_core->db->is_special_page() ) return;

	// Add waypoints script.
	wp_enqueue_script(
		'cpajax-waypoints',
		plugins_url( 'commentpress-ajax/assets/js/waypoints' . $debug_state . '.js', COMMENTPRESS_PLUGIN_FILE ),
		[ 'jquery' ], //dependencies
		COMMENTPRESS_VERSION // Version.
	);

	// Add infinite scroll script.
	wp_enqueue_script(
		'cpajax-infinite',
		plugins_url( 'commentpress-ajax/assets/js/cp-ajax-infinite' . $debug_state . '.js', COMMENTPRESS_PLUGIN_FILE ),
		[ 'cpajax', 'cpajax-waypoints' ], //dependencies
		COMMENTPRESS_VERSION // Version.
	);

	// Init vars.
	$infinite = [];

	// Is "live" comment refreshing enabled?
	$infinite['nonce'] = wp_create_nonce( 'cpajax_infinite_nonce' );

	// Use wp function to localise.
	wp_localize_script( 'cpajax', 'CommentpressAjaxInfiniteSettings', $infinite );

}



/**
 * Load the next page.
 *
 * @since 3.4
 */
function cpajax_infinite_scroll_load_next_page() {

	// Error check.
	//if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'cpajax_infinite_nonce' ) ) { die( 'Nonce failure' ); }

	// Access globals.
	global $commentpress_core;

	// Die if CommentPress is not enabled.
	if ( is_null( $commentpress_core ) OR ! is_object( $commentpress_core ) ) {
		$message = __( 'No CommentPress Core', 'commentpress-core' );
		die( $message );
	}

	// Init data.
	$data = '';

	// Get incoming data.
	$current_post_id = isset( $_POST['current_post_id'] ) ? absint( $_POST['current_post_id'] ) : '';

	// Sanity check.
	if ( $current_post_id === '' ) { die( 'No $current_post_id' ); }

	// Get all pages.
	$all_pages = $commentpress_core->nav->get_book_pages( 'readable' );

	// If we have any pages.
	if ( count( $all_pages ) == 0 ) { die( 'No $all_pages' ); }

	// Init the key we want.
	$page_key = false;

	// Loop.
	foreach( $all_pages AS $key => $page_obj ) {

		// Is it the currently viewed page?
		if ( $page_obj->ID == $current_post_id ) {

			// Set page key.
			$page_key = $key;

			// Kick out to preserve key.
			break;

		}

	}

	// Die if we don't get a key.
	if ( $page_key === false ) { die( ' No $page_key' ); }

	// Die if there is no next item.
	if ( ! isset( $all_pages[$page_key + 1] ) ) { die( 'No key in array' ); }

	// Get object.
	$new_post = $all_pages[$page_key + 1];

	// Access post.
	global $post;

	// Get page data.
	$post = get_post( $new_post->ID );

	// Enable API.
	setup_postdata( $post );



	// Get title using buffer.
	ob_start();
	//wp_title( '|', true, 'right' );
	bloginfo( 'name' ); commentpress_site_title( '|' );
	$page_title = ob_get_contents();
	ob_end_clean();

	// Format title.
	$page_title = get_the_title( $post->ID ) . ' | ' . $page_title;

	// Get next page.

	// Get feature image.
	ob_start();
	commentpress_get_feature_image();
	$feature_image = ob_get_contents();
	ob_end_clean();

	// Get title.
	$title = '<h2 class="post_title"><a href="' . get_permalink( $post->ID ) . '">' . get_the_title( $post->ID ) . '</a></h2>';



	// Because AJAX may be routed via admin or front end.
	if ( defined( 'DOING_AJAX' ) AND DOING_AJAX AND is_admin() ) {

		// Add CommentPress Core filter to the content when it's on the admin side.
		add_filter( 'the_content', [ $commentpress_core->parser, 'the_content' ], 20 );

	}

	// Get content.
	$content = apply_filters( 'the_content', $post->post_content );



	// Generate page numbers.
	$commentpress_core->nav->_generate_page_numbers( $all_pages );

	// Get page number.
	$number = $commentpress_core->nav->get_page_number( $post->ID );

	// Get menu ID, if we have one.
	if ( isset( $new_post->menu_id ) ) {
		$menu_id = 'wpcustom_menuid-' . $new_post->menu_id;
	} else {
		$menu_id = 'wppage_menuid-' . $new_post->ID;
	}

	// Init page number html.
	$page_num = '';

	// If we get one.
	if ( $number ) {

		// Is it arabic?
		if ( is_numeric( $number ) ) {

			// Add page number.
			$page_num = '<div class="running_header_bottom">page ' . $number . '</div>';

		} else {

			// Add page number.
			$page_num = '<div class="running_header_bottom">page ' . strtolower( $number ) . '</div>';

		}

	}



	// Init nav.
	$commentpress_core->nav->initialise();

	// Get page navigation.
	$navigation = commentpress_page_navigation();

	// If we get any.
	if ( $navigation != '' ) {
		$navigation = '<div class="page_navigation"><ul>' . $navigation . '</ul></div><!-- /page_navigation -->';
	}

	// Init upper nav.
	$upper_navigation = '';

	// Do we have a featured image?
	if ( ! commentpress_has_feature_image() ) {

		// Assign upper page navigation.
		$upper_navigation = $navigation;

	} else {

		// We have a feature image - clear title in main body of content.
		$title = '';

	}

	// Always show lower nav.
	$lower_navigation = '<div class="page_nav_lower">' .
							$navigation .
						'</div><!-- /page_nav_lower -->';

	// Wrap in div.
	$data = '<div class="page_wrapper cp_page_wrapper">' .
				$feature_image .
				$upper_navigation .
				'<div class="content"><div class="post' . commentpress_get_post_css_override( $post->ID ) . ' ' . $menu_id . '" id="post-' . $post->ID . '">' .
					$title .
					$content .
					$page_num .
				'</div></div>' .
				$lower_navigation .
			'</div>';



	// Get comments using buffer.
	ob_start();
	$vars = $commentpress_core->db->get_javascript_vars();

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

	$comments = ob_get_contents();
	ob_end_clean();

	// Wrap in div.
	$comments = '<div class="comments-for-' . $post->ID . '">' . $comments . '</div>';



	// Construct response.
	$response =  [
		'post_id' => $post->ID,
		'url' => get_permalink( $post->ID ),
		'title' => $page_title,
		'content' => $data,
		'comments' => $comments,
		'comment_status' => $post->comment_status,
	];

	// Set reasonable headers.
	header('Content-type: text/plain');
	header("Cache-Control: no-cache");
	header("Expires: -1");

	// Echo.
	echo json_encode( $response );

	// Die.
	exit();

}



