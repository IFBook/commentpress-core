<?php /*
================================================================================
CommentPress AJAX Comments
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

--------------------------------------------------------------------------------
*/





// enable the plugin at the appropriate point
add_action( 'wp', 'cpajax_enable_plugin' );

// always add AJAX functionality
add_action( 'wp_ajax_cpajax_get_new_comments', 'cpajax_get_new_comments' );
add_action( 'wp_ajax_nopriv_cpajax_get_new_comments', 'cpajax_get_new_comments' );

// remove comment flood filter if you want more 'chat-like' functionality
//remove_filter('comment_flood_filter', 'wp_throttle_comment_flood', 10, 3);

// add AJAX reassign functionality
add_action( 'wp_ajax_cpajax_reassign_comment', 'cpajax_reassign_comment' );
add_action( 'wp_ajax_nopriv_cpajax_reassign_comment', 'cpajax_reassign_comment' );

// let's disable infinite scroll unless we set a constant
if ( defined( 'COMMENTPRESS_INFINITE_SCROLL' ) AND COMMENTPRESS_INFINITE_SCROLL ) {

	// add AJAX infinite scroll functionality
	add_action( 'wp_ajax_cpajax_load_next_page', 'cpajax_load_next_page' );
	add_action( 'wp_ajax_nopriv_cpajax_load_next_page', 'cpajax_load_next_page' );

}



/** 
 * @description: load the next page
 * @todo: 
 *
 */
function cpajax_load_next_page() {
	
	// error check
	//if ( ! wp_verify_nonce( $_REQUEST['nonce'], 'cpajax_infinite_nonce' ) ) { die( 'Nonce failure' ); }

	// access globals
	global $commentpress_core;
	
	// die if cp is not enabled
	if ( is_null( $commentpress_core ) OR !is_object( $commentpress_core ) ) { die( 'No CP' ); }
	
	// init data
	$data = '';

	// get incoming data
	$current_post_id = isset( $_POST['current_post_id'] ) ? absint( $_POST['current_post_id'] ) : '';
	
	// sanity check
	if ( $current_post_id === '' ) { die( 'No $current_post_id' ); }
		
	// get all pages
	$all_pages = $commentpress_core->nav->get_book_pages( 'readable' );
	//if ( is_user_logged_in() ) { print_r( $all_pages ); die(); }
	
	// if we have any pages...
	if ( count( $all_pages ) == 0 ) { die( 'No $all_pages' ); }
	
	// init the key we want
	$page_key = false;
	
	// loop
	foreach( $all_pages AS $key => $page_obj ) {
	
		// is it the currently viewed page?
		if ( $page_obj->ID == $current_post_id ) {
		
			// set page key
			$page_key = $key;
		
			// kick out to preserve key
			break;
		
		}
	
	}
	
	// die if we don't get a key
	if ( $page_key === false ) { die( ' No $page_key' ); }
	
	// die if there is no next item
	if ( !isset( $all_pages[$page_key + 1] ) ) { die( 'No key in array' ); }
	
	// get object
	$new_post = $all_pages[$page_key + 1];
	
	global $post;
	
	// get page data
	$post = get_post( $new_post->ID );
	
	setup_postdata( $post );
	
	///*
	// get title using buffer
	ob_start();
	//wp_title( '|', true, 'right' ); 
	bloginfo( 'name' ); commentpress_site_title( '|' );
	$page_title = ob_get_contents();
	ob_end_clean();
	//*/
	
	$page_title = get_the_title( $post->ID ).' | '.$page_title;
	
	// get next page
	//print_r( array( $post, $post->post_title ) ); die();
	
	// because AJAX may be routed via admin or front end
	if ( defined( 'DOING_AJAX' ) AND DOING_AJAX AND is_admin() ) {
	
		// add CP filter to the content when it's on the admin side
		add_filter( 'the_content', array( $commentpress_core->parser, 'the_content' ), 20 );
	
	}
	
	// get feature image
	ob_start();
	commentpress_get_feature_image();
	$feature_image = ob_get_contents();
	ob_end_clean();
	
	// get title
	$title = '<h2 class="post_title"><a href="'.get_permalink( $post->ID ).'">'.get_the_title( $post->ID ).'</a></h2>';
	
	// get content
	$content = apply_filters( 'the_content', $post->post_content );
	
	// generate page numbers
	$commentpress_core->nav->_generate_page_numbers( $all_pages );
	
	// get page number
	$number = $commentpress_core->nav->get_page_number( $post->ID );
	//print_r( $number ); die();
	
	// get menu ID, if we have one
	if ( isset( $new_post->menu_id ) ) {
		$menu_id = 'wpcustom_menuid-'.$new_post->menu_id;
	} else {
		$menu_id = 'wppage_menuid-'.$new_post->ID;
	}
	
	// init page number html
	$page_num = '';

	// if we get one
	if ( $number ) {
		
		// is it arabic?
		if ( is_numeric( $number ) ) {
		
			// add page number
			$page_num = '<div class="running_header_bottom">page '.$number.'</div>';
	
		} else {
		
			// add page number
			$page_num = '<div class="running_header_bottom">page '.strtolower( $number ).'</div>';
	
		}
		
	}
	
	// init nav
	$commentpress_core->nav->initialise();

	// get page navigation
	$navigation = commentpress_page_navigation();
	
	// if we get any...
	if ( $navigation != '' ) { 
		$navigation = '<div class="page_navigation"><ul>'.$navigation.'</ul></div><!-- /page_navigation -->';
	}

	// init upper nav
	$upper_navigation = '';

	// do we have a featured image?
	if ( !commentpress_has_feature_image() ) {

		// assign upper page navigation
		$upper_navigation = $navigation;

	}
	
	// always show lower nav
	$lower_navigation = '<div class="page_nav_lower">'.
							$navigation.
						'</div><!-- /page_nav_lower -->';

	// wrap in div
	$data = '<div class="page_wrapper cp_page_wrapper">'.
				$feature_image.
				$upper_navigation.
				'<div class="content"><div class="post'.commentpress_get_post_css_override( $post->ID ).' '.$menu_id.'" id="post-'.$post->ID.'">'.
					$title.
					$content.
					$page_num.
				'</div></div>'.
				$lower_navigation.
			'</div>';
	
	
	// get comments using buffer
	ob_start();
	$vars = $commentpress_core->db->get_javascript_vars();
	include_once( get_template_directory() . '/assets/templates/comments_by_para.php' );
	$comments = ob_get_contents();
	ob_end_clean();
	
	// wrap in div
	$comments = '<div class="comments-for-'.$post->ID.'">'.$comments.'</div>';

	// construct response
	$response =  array(
	
		'post_id'     => $post->ID,
		'url'     => get_permalink( $post->ID ),
		'title'     => $page_title,
		'content'     => $data,
		'comments'     => $comments,
		
	);

	// set reasonable headers
	header('Content-type: text/plain'); 
	header("Cache-Control: no-cache");
	header("Expires: -1");

	// echo
	echo json_encode( $response );
	
	// die!
	exit();
	
}






/** 
 * @description: get context in which to enable this plugin
 * @todo: 
 *
 */
function cpajax_enable_plugin() {

	// access globals
	global $commentpress_core;
	
	// kick out if...
	
	// cp is not enabled
	if ( is_null( $commentpress_core ) OR !is_object( $commentpress_core ) )  { return; }
	
	// we're in the WP back end
	if ( is_admin() ) { return; }
		
	
	
	// add our javascripts
	add_action( 'wp_enqueue_scripts', 'cpajax_add_javascripts', 120 );
	
	// add a button to the comment meta
	add_filter( 'cp_comment_edit_link', 'cpajax_add_reassign_button', 20, 2 );
	
}






/** 
 * @description: get new comments in response to an ajax request
 * @todo: 
 *
 */
function cpajax_get_new_comments() {

	// init return
	$data = array();

	// get incoming data
	$last_comment_count = isset( $_POST[ 'last_count' ] ) ? $_POST[ 'last_count' ] : NULL;

	// store incoming unless updated later
	$data['cpajax_comment_count'] = $last_comment_count;
	
	// get post ID
	$post_id = isset( $_POST[ 'post_id' ] ) ? $_POST[ 'post_id' ] : NULL;
	
	// make it an integer, just to be sure
	$post_id = (int) $post_id;
	
	// enable Wordpress API on post
	$GLOBALS['post'] = get_post( $post_id );



	// get any comments posted since last update time
	$data['cpajax_new_comments'] = array();
	
	// get current array
	$current_comment_count_array = get_comment_count( $post_id );
	
	// get approved -> do we want others?
	$current_comment_count = $current_comment_count_array['approved'];
	
	// get number of new comments to fetch
	$num_to_get = (int) $current_comment_count - (int) $last_comment_count;
	
	// are there any?
	if ( $num_to_get > 0 ) {
	
		
		
		// update comment count since last request
		$data['cpajax_comment_count'] = (string) $current_comment_count;
		
		
		
		// set get_comments defaults
		$defaults = array(
			'number' => $num_to_get,
			'orderby' => 'comment_date',
			'order' => 'DESC',
			'post_id' => $post_id,
			'status' => 'approve',
			'type' => 'comment'
		);
	
		// get them
		$comments = get_comments( $defaults );
		
		
		
		// if we get some - again, just to be sure
		if ( count( $comments ) > 0 ) {
		
			// init identifier
			$identifier = 1;
		
			// set args
			$args = array();
			$args['max_depth'] = get_option( 'thread_comments_depth' );
			
			// loop
			foreach( $comments AS $_comment ) {
			
				// assume top level
				$depth = 1;

				// if no parent
				if ( $_comment->comment_parent != '0' ) {
				
					// override depth
					$depth = cpajax_get_comment_depth( $_comment, $depth );
					
				}
				
				// get comment markup
				$html = commentpress_get_comment_markup( $_comment, $args, $depth );
				
				// close li (walker would normally do this)
				$html .= '</li>'."\n\n\n\n";
				
				// add comment to array
				$data['cpajax_new_comment_'.$identifier] = array(
				
					'parent' => $_comment->comment_parent,
					'id' => $_comment->comment_ID,
					'text_sig' => $_comment->comment_signature,
					'markup' => $html
					
				);
				
				// increment
				$identifier++;
			
			}
		
		}



	}
	


	// set reasonable headers
	header('Content-type: text/plain'); 
	header("Cache-Control: no-cache");
	header("Expires: -1");

	// echo
	echo json_encode( $data );
	//print_r( $last_comment_count );
	
	// die!
	exit();
	
}






/** 
 * @description: get comment depth
 * @todo: 
 *
 */
function cpajax_get_comment_depth( $comment, $depth ) {
	
	// is parent top level?
	if ( $comment->comment_parent == '0' ) {
	
		// --<
		return $depth;
	
	}
	
	// get parent comment
	$parent = get_comment( $comment->comment_parent );
	
	// increase depth
	$depth++;
	
	// recurse
	return cpajax_get_comment_depth( $parent, $depth );

}






/** 
 * @description: add our plugin javascripts
 * @todo: 
 *
 */
function cpajax_add_javascripts() {
	
	// access globals
	global $post, $commentpress_core;
	
	// can only now see $post
	if ( !cpajax_plugin_can_activate() ) { return; }
	
	
	
	// init vars
	$vars = array();

	// is "live" comment refreshing enabled?
	$vars['cpajax_live'] = ( $commentpress_core->db->option_get( 'cp_para_comments_live' ) == '1' ) ? 1 : 0;
	
	// we need to know the url of the Ajax handler
	$vars['cpajax_ajax_url'] = admin_url( 'admin-ajax.php' );
	
	// add the url of the animated loading bar gif
	$vars['cpajax_spinner_url'] = plugins_url( 'commentpress-ajax/assets/images/loading.gif', COMMENTPRESS_PLUGIN_FILE );
	
	// time formatted thus: 2009-08-09 14:46:14
	$vars['cpajax_current_time'] = date('Y-m-d H:i:s');
	
	// get comment count at the time the page is served
	$_count = get_comment_count( $post->ID );
	
	// adding moderation queue as well, since we do show these
	$vars['cpajax_comment_count'] = $_count['approved']; // + $_count['awaiting_moderation'];
	
	// add post ID
	$vars['cpajax_post_id'] = $post->ID;
	
	// get translations array
	$vars['cpajax_lang'] = cpajax_localise();
	
	
	
	// default to minified scripts
	$debug_state = '';

	// target different scripts when debugging
	if ( defined( 'SCRIPT_DEBUG' ) AND SCRIPT_DEBUG === true ) {
	
		// use uncompressed scripts
		$debug_state = '.dev';
	
	}
	
	
	
	// are we asking for in-page comments?
	if ( $commentpress_core->db->is_special_page() ) {
	
		// add comments in page script
		wp_enqueue_script( 
			
			'cpajax', 
			plugins_url( 'commentpress-ajax/cp-ajax-comments-page'.$debug_state.'.js', COMMENTPRESS_PLUGIN_FILE ),
			null, // no dependencies
			COMMENTPRESS_VERSION // version
			
		);
	
	} else {
	
		// add comments in sidebar script
		wp_enqueue_script( 
			
			'cpajax', 
			plugins_url( 'commentpress-ajax/cp-ajax-comments'.$debug_state.'.js', COMMENTPRESS_PLUGIN_FILE ),
			array( 'jquery-ui-droppable', 'jquery-ui-dialog' ), // load droppable and dialog as dependencies
			COMMENTPRESS_VERSION // version
			
		);
		
		// add WordPress dialog CSS
		wp_enqueue_style( 'wp-jquery-ui-dialog' );
	
	}
	
	// use wp function to localise
	wp_localize_script( 'cpajax', 'CommentpressAjaxSettings', $vars );
	
	
	
	// let's disable infinite scroll unless we set a constant
	if ( defined( 'COMMENTPRESS_INFINITE_SCROLL' ) AND COMMENTPRESS_INFINITE_SCROLL ) {

		// are we asking for in-page comments?
		if ( ! $commentpress_core->db->is_special_page() ) {
	
			// add waypoints script
			wp_enqueue_script( 
			
				'cpajax-waypoints', 
				plugins_url( 'commentpress-ajax/assets/js/waypoints'.$debug_state.'.js', COMMENTPRESS_PLUGIN_FILE ),
				array( 'jquery' ), //dependencies
				COMMENTPRESS_VERSION // version
			
			);
		
			// add infinite scroll script
			wp_enqueue_script( 
			
				'cpajax-infinite', 
				plugins_url( 'commentpress-ajax/assets/js/cp-ajax-infinite'.$debug_state.'.js', COMMENTPRESS_PLUGIN_FILE ),
				array( 'cpajax', 'cpajax-waypoints' ), //dependencies
				COMMENTPRESS_VERSION // version
			
			);
		
			// init vars
			$infinite = array();

			// is "live" comment refreshing enabled?
			$infinite['nonce'] = wp_create_nonce( 'cpajax_infinite_nonce' );
	
			// use wp function to localise
			wp_localize_script( 'cpajax', 'CommentpressAjaxInfiniteSettings', $infinite );
	
		}
	
	}
	
}






/** 
 * @description: translation
 * @todo: 
 *
 */
function cpajax_localise() {
	
	// init array
	$translations = array();
	
	// add translations for comment form
	$translations[] = __( 'Loading...', 'commentpress-core' );
	$translations[] = __( 'Please enter your name.', 'commentpress-core' );
	$translations[] = __( 'Please enter your email address.', 'commentpress-core' );
	$translations[] = __( 'Please enter a valid email address.', 'commentpress-core' );
	$translations[] = __( 'Please enter your comment.', 'commentpress-core' );
	$translations[] = __( 'Your comment has been added.', 'commentpress-core' );
	$translations[] = __( 'AJAX error!', 'commentpress-core' );
	
	// add translations for comment reassignment
	$translations[] = __( 'Are you sure?', 'commentpress-core' );
	$translations[] = __( 'Are you sure you want to assign the comment and its replies to the textblock? This action cannot be undone.', 'commentpress-core' );
	$translations[] = __( 'Submitting...', 'commentpress-core' );
	$translations[] = __( 'Please wait while the comments are reassigned. The page will refresh when this has been done.', 'commentpress-core' );
	
	// add translations for comment word...
	// singular
	$translations[] = __( 'Comment', 'commentpress-core' );
	// plural
	$translations[] = __( 'Comments', 'commentpress-core' );
	
	// --<
	return $translations;

}






/** 
 * @description: validate that the plugin can be activated
 * @todo: 
 *
 */
function cpajax_plugin_can_activate() {

	// access globals
	global $post, $commentpress_core;
	
	// disallow if no post ID (such as 404)
	if ( !is_object( $post ) )  { return false; }
	
	// it's the Theme My Login page
	if ( $commentpress_core->is_theme_my_login_page() ) { return false; }
	
	// init
	$allowed = true;
	
	// disallow generally if page doesn't allow commenting
	if ( !$commentpress_core->is_commentable() )  { $allowed = false; }
	
	// but, allow general comments page
	if ( $commentpress_core->db->option_get( 'cp_general_comments_page' ) == $post->ID ) { $allowed = true; }
	
	// --<
	return $allowed;
	
}





/** 
 * @description: get comment depth
 * @todo: 
 *
 */
function cpajax_add_reassign_button( $edit_button, $comment ) {

	//print_r( $comment ); die();
	
	// pass if not top level
	if ( $comment->comment_parent != '0' ) { return $edit_button; }
	
	// pass if pingback or trackback
	if ( $comment->comment_type == 'trackback' OR $comment->comment_type == 'pingback' ) { return $edit_button; }
	
	// pass if not orphan
	//if ( !isset( $comment->orphan ) ) { return $edit_button; }
	
	// set default edit link title text
	$_title_text = apply_filters( 
		'cpajax_comment_assign_link_title_text', 
		__( 'Drop on to a text-block to reassign this comment (and any replies) to it', 'commentpress-core' )
	);

	// set default edit link text
	$_text = apply_filters( 
		'cp_comment_assign_link_text', 
		__( 'Move', 'commentpress-core' )
	);

	// construct assign button
	$assign_button = '<span class="alignright comment-assign" title="'.$_title_text.'" id="cpajax_assign-'.$comment->comment_ID.'">'.
						$_text.
					 '</span>';
	
	// add our assign button
	$edit_button .= $assign_button;
	
	
	
	// --<
	return $edit_button;

}






/** 
 * @description: change a comment's text-signature
 * @todo: 
 *
 */
function cpajax_reassign_comment() {

	global $data;
	
	// init return
	$data = array();
	$data['msg'] = '';
	
	// init checker
	$comment_ids = array();
	
	// get incoming data
	$text_sig = isset( $_POST[ 'text_signature' ] ) ? $_POST[ 'text_signature' ] : '';
	$comment_id = isset( $_POST[ 'comment_id' ] ) ? $_POST[ 'comment_id' ] : '';
	
	// sanity check
	if ( $text_sig !== '' AND $comment_id !== '' ) {
	
		// access globals
		global $commentpress_core;
		
		// store text signature
		$commentpress_core->db->save_comment_signature( $comment_id );
		
		// trace
		$comment_ids[] = $comment_id;
		
		// recurse for any comment children
		cpajax_reassign_comment_children( $comment_id, $text_sig, $comment_ids );
				
	}
	
	// add message
	$data['msg'] .= 'comments '.implode( ', ', $comment_ids ).' updated'."\n";

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
 * @description: store text signature for all children of a comment
 * @todo: 
 *
 */
function cpajax_reassign_comment_children( $comment_id, $text_sig, &$comment_ids ) {

	// get the children of the comment
	$children = cpajax_get_children( $comment_id );
	
	// did we get any
	if ( count( $children ) > 0 ) {
	
		// loop
		foreach( $children AS $child ) {
	
			// access globals
			global $commentpress_core;
			
			// store text signature
			$commentpress_core->db->save_comment_signature( $child->comment_ID );
			
			// trace
			$comment_ids[] = $child->comment_ID;
			
			// recurse for any comment children
			cpajax_reassign_comment_children( $child->comment_ID, $text_sig, $comment_ids );
			
		}
		
	}

}





/** 
 * @description: retrieve comment children
 * @todo: 
 *
 */
function cpajax_get_children( 

	$comment_id
	
) { //-->

	// declare access to globals
	global $wpdb;

	// construct query for comment children
	$query = "
	SELECT *
	FROM $wpdb->comments
	WHERE comment_parent = '$comment_id' 
	ORDER BY comment_date ASC
	";
	
	// --<
	return $wpdb->get_results( $query );

}





