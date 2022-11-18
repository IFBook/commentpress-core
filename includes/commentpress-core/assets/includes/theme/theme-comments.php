<?php
/**
 * CommentPress Core Theme Comment functions.
 *
 * Handles common comment functionality in CommentPress themes.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;



if ( ! function_exists( 'commentpress_get_children' ) ) :

	/**
	 * Retrieve comment children.
	 *
	 * @since 3.3
	 *
	 * @param object $comment The WordPress comment object.
	 * @param string $page_or_post The WordPress post type to query.
	 * @return array $result The array of child comments.
	 */
	function commentpress_get_children( $comment, $page_or_post ) {

		// Declare access to globals.
		global $wpdb;

		// Does the comment have children?
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		return $wpdb->get_results(
			$wpdb->prepare(
				"
				SELECT {$wpdb->comments}.*, {$wpdb->posts}.post_title, {$wpdb->posts}.post_name
				FROM {$wpdb->comments}, {$wpdb->posts}
				WHERE {$wpdb->comments}.comment_post_ID = {$wpdb->posts}.ID
				AND {$wpdb->posts}.post_type = %s
				AND {$wpdb->comments}.comment_approved = '1'
				AND {$wpdb->comments}.comment_parent = %d
				ORDER BY {$wpdb->comments}.comment_date ASC
				",
				$page_or_post,
				$comment->comment_ID
			)
		);

	}

endif;



if ( ! function_exists( 'commentpress_get_comments' ) ) :

	/**
	 * Generate comments recursively.
	 *
	 * This function builds the list into a global called $cp_comment_output for
	 * retrieval elsewhere.
	 *
	 * @since 3.0
	 *
	 * @param array $comments An array of WordPress comment objects.
	 * @param string $page_or_post The WordPress post type to query.
	 */
	function commentpress_get_comments( $comments, $page_or_post ) {

		// Declare access to globals.
		global $cp_comment_output;

		// Do we have any comments?
		if ( count( $comments ) > 0 ) {

			// Open ul.
			$cp_comment_output .= '<ul class="item_ul">' . "\n\n";

			// Produce a checkbox for each.
			foreach ( $comments as $comment ) {

				// Open li.
				$cp_comment_output .= '<li class="item_li">' . "\n\n";

				// Format this comment.
				$cp_comment_output .= commentpress_format_comment( $comment );

				// Get comment children.
				$children = commentpress_get_children( $comment, $page_or_post );

				// Do we have any?
				if ( count( $children ) > 0 ) {

					// Recurse.
					commentpress_get_comments( $children, $page_or_post );

				}

				// Close li.
				$cp_comment_output .= '</li>' . "\n\n";

			}

			// Close ul.
			$cp_comment_output .= '</ul>' . "\n\n";

		}

	}

endif;



if ( ! function_exists( 'commentpress_get_user_link' ) ) :

	/**
	 * Get user link in vanilla WordPress scenarios.
	 *
	 * In default single install mode, just link to their URL, unless they are an
	 * author, in which case we link to their author page. In multisite, the same.
	 * When BuddyPress is enabled, always link to their profile.
	 *
	 * @since 3.0
	 *
	 * @param object $user The WordPress user object.
	 * @param object $comment The WordPress comment object.
	 * @return string $url The URL for the user.
	 */
	function commentpress_get_user_link( $user, $comment = null ) {

		// Kick out if not a user.
		if ( ! is_object( $user ) ) {
			return false;
		}

		// We're through: the user is on the system.
		global $commentpress_core;

		// If BuddyPress.
		if ( is_object( $commentpress_core ) && $commentpress_core->is_buddypress() ) {

			// BuddyPress link - $no_anchor = null, $just_link = true.
			$url = bp_core_get_userlink( $user->ID, null, true );

		} else {

			// Get standard WordPress author URL.

			// Get author URL.
			$url = get_author_posts_url( $user->ID );

			// WordPress sometimes leaves 'http://' or 'https://' in the field.
			if ( $url == 'http://' || $url == 'https://' ) {
				$url = '';
			}

		}

		// --<
		return apply_filters( 'commentpress_get_user_link', $url, $user, $comment );

	}

endif;



if ( ! function_exists( 'commentpress_format_comment' ) ) :

	/**
	 * Format comment on custom CommentPress Core comments pages.
	 *
	 * @since 3.0
	 *
	 * @param object $comment The comment object.
	 * @param str $context Either "all" for all-comments or "by" for comments-by-commenter.
	 * @return str The formatted comment HTML.
	 */
	function commentpress_format_comment( $comment, $context = 'all' ) {

		// Declare access to globals.
		global $commentpress_core, $cp_comment_output;

		/*
		// TODO: Enable WordPress API on comment?
		$GLOBALS['comment'] = $comment;
		*/

		// Construct link.
		$comment_link = get_comment_link( $comment->comment_ID );

		// Construct anchor.
		$comment_anchor = '<a href="' . $comment_link . '" title="' . esc_attr( __( 'See comment in context', 'commentpress-core' ) ) . '">' . __( 'Comment', 'commentpress-core' ) . '</a>';

		// Construct date.
		$comment_date = date_i18n( get_option( 'date_format' ), strtotime( $comment->comment_date ) );

		// If context is 'all comments'.
		if ( $context == 'all' ) {

			// Get author.
			if ( $comment->comment_author != '' ) {

				// Was it a registered user?
				if ( $comment->user_id != '0' ) {

					// Get user details.
					$user = get_userdata( $comment->user_id );

					// Get user link.
					$user_link = commentpress_get_user_link( $user, $comment );

					// Did we get one?
					if ( $user_link != '' && $user_link != 'http://' ) {

						// Construct link to user URL.
						$comment_author = '<a href="' . $user_link . '">' . $comment->comment_author . '</a>';

					} else {

						// Just show author name.
						$comment_author = $comment->comment_author;

					}

				} else {

					// Do we have an author URL?
					if ( $comment->comment_author_url != '' && $comment->comment_author_url != 'http://' ) {

						// Construct link to user URL.
						$comment_author = '<a href="' . $comment->comment_author_url . '">' . $comment->comment_author . '</a>';

					} else {

						// Define context.
						$comment_author = $comment->comment_author;

					}

				}

			} else {

				// We don't have a name.
				$comment_author = __( 'Anonymous', 'commentpress-core' );

			}

			// Construct comment header content.
			$comment_meta_content = sprintf(
				__( '%1$s by %2$s on %3$s', 'commentpress-core' ),
				$comment_anchor,
				$comment_author,
				$comment_date
			);

			// Wrap comment meta in a div.
			$comment_meta = '<div class="comment_meta">' . $comment_meta_content . '</div>' . "\n";

			// Allow filtering by plugins.
			$comment_meta = apply_filters(
				'commentpress_format_comment_all_meta', // Filter name.
				$comment_meta, // Built meta.
				$comment,
				$comment_anchor,
				$comment_author,
				$comment_date
			);

		// If context is 'by commenter'.
		} elseif ( $context == 'by' ) {

			// Construct link.
			$page_link = trailingslashit( get_permalink( $comment->comment_post_ID ) );

			// Construct page anchor.
			$page_anchor = '<a href="' . $page_link . '">' . get_the_title( $comment->comment_post_ID ) . '</a>';

			// Construct comment header content.
			$comment_meta_content = sprintf(
				__( '%1$s on %2$s on %3$s', 'commentpress-core' ),
				$comment_anchor,
				$page_anchor,
				$comment_date
			);

			// Wrap comment meta in a div.
			$comment_meta = '<div class="comment_meta">' . $comment_meta_content . '</div>' . "\n";

			// Allow filtering by plugins.
			$comment_meta = apply_filters(
				'commentpress_format_comment_by_meta', // Filter name.
				$comment_meta, // Built meta.
				$comment,
				$comment_anchor,
				$page_anchor,
				$comment_date
			);

		}

		// Comment content.
		$comment_body = '<div class="comment-content">' . apply_filters( 'comment_text', $comment->comment_content ) . '</div>' . "\n";

		// Construct comment.
		return '<div class="comment_wrapper">' . "\n" . $comment_meta . $comment_body . '</div>' . "\n\n";

	}

endif;



if ( ! function_exists( 'commentpress_get_comments_by_content' ) ) :

	/**
	 * Comments-by page display function.
	 *
	 * @todo Do we want trackbacks?
	 *
	 * @since 3.0
	 *
	 * @return str $html The HTML for the page.
	 */
	function commentpress_get_comments_by_content() {

		// Init return.
		$html = '';

		// Get all approved comments.
		$all_comments = get_comments( [
			'status' => 'approve',
			'orderby' => 'comment_author, comment_post_ID, comment_date',
			'order' => 'ASC',
		] );

		// Kick out if none.
		if ( count( $all_comments ) == 0 ) {
			return $html;
		}

		// Build list of authors.
		$authors_with = [];
		$author_names = [];
		/* $post_comment_counts = []; */

		foreach ( $all_comments as $comment ) {

			// Add to authors with comments array.
			if ( ! in_array( $comment->comment_author_email, $authors_with ) ) {
				$authors_with[] = $comment->comment_author_email;
				$name = $comment->comment_author != '' ? $comment->comment_author : __( 'Anonymous', 'commentpress-core' );
				$author_names[ $comment->comment_author_email ] = $name;
			}

			/*
			// Increment counter.
			if ( ! isset( $post_comment_counts[$comment->comment_author_email] ) ) {
				$post_comment_counts[$comment->comment_author_email] = 1;
			} else {
				$post_comment_counts[$comment->comment_author_email]++;
			}
			*/

		}

		// Kick out if none.
		if ( count( $authors_with ) == 0 ) {
			return $html;
		}

		// Open ul.
		$html .= '<ul class="all_comments_listing">' . "\n\n";

		// Loop through authors.
		foreach ( $authors_with as $author ) {

			// Open li.
			$html .= '<li class="author_li"><!-- author li -->' . "\n\n";

			// Add gravatar.
			$html .= '<h3>' . get_avatar( $author, $size = '24' ) . esc_html( $author_names[ $author ] ) . '</h3>' . "\n\n";

			// Open comments div.
			$html .= '<div class="item_body">' . "\n\n";

			// Open ul.
			$html .= '<ul class="item_ul">' . "\n\n";

			// Loop through comments.
			foreach ( $all_comments as $comment ) {

				// Does it belong to this author?
				if ( $author == $comment->comment_author_email ) {

					// Open li.
					$html .= '<li class="item_li"><!-- item li -->' . "\n\n";

					// Show the comment.
					$html .= commentpress_format_comment( $comment, 'by' );

					// Close li.
					$html .= '</li><!-- /item li -->' . "\n\n";

				}

			}

			// Close ul.
			$html .= '</ul>' . "\n\n";

			// Close item div.
			$html .= '</div><!-- /item_body -->' . "\n\n";

			// Close li.
			$html .= '</li><!-- /.author_li -->' . "\n\n\n\n";

		}

		// Close ul.
		$html .= '</ul><!-- /.all_comments_listing -->' . "\n\n";

		// --<
		return $html;

	}

endif;



if ( ! function_exists( 'commentpress_get_comments_by_page_content' ) ) :

	/**
	 * Comments-by page display wrapper function.
	 *
	 * @since 3.0
	 *
	 * @return str $page_content The page content.
	 */
	function commentpress_get_comments_by_page_content() {

		// Allow oEmbed in comments.
		global $wp_embed;
		if ( $wp_embed instanceof WP_Embed ) {
			add_filter( 'comment_text', [ $wp_embed, 'autoembed' ], 1 );
		}

		// Declare access to globals.
		global $commentpress_core;

		// Get data.
		$page_content = commentpress_get_comments_by_content();

		// --<
		return $page_content;

	}

endif;



if ( ! function_exists( 'commentpress_get_comment_activity' ) ) :

	/**
	 * Activity sidebar display function.
	 *
	 * @todo Do we want trackbacks?
	 *
	 * @since 3.3
	 *
	 * @param str $scope The scope of the activities.
	 * @return str $page_content The HTML output for activities.
	 */
	function commentpress_get_comment_activity( $scope = 'all' ) {

		// Allow oEmbed in comments.
		global $wp_embed;
		if ( $wp_embed instanceof WP_Embed ) {
			add_filter( 'comment_text', [ $wp_embed, 'autoembed' ], 1 );
		}

		// Declare access to globals.
		global $commentpress_core, $post;

		// Init page content.
		$page_content = '';

		// Define defaults.
		$args = [
			'number' => 10,
			'status' => 'approve',
			// Exclude trackbacks and pingbacks until we decide what to do with them.
			'type' => '',
		];

		// If we are on a 404, for example.
		if ( $scope == 'post' && is_object( $post ) ) {

			// Get all comments.
			$args['post_id'] = $post->ID;

		}

		// Get 'em.
		$data = get_comments( $args );

		// Did we get any?
		if ( count( $data ) > 0 ) {

			// Init comments array.
			$comments_array = [];

			// Loop.
			foreach ( $data as $comment ) {

				// Exclude comments from password-protected posts.
				if ( ! post_password_required( $comment->comment_post_ID ) ) {
					$comment_markup = commentpress_get_comment_activity_item( $comment );
					if ( ! empty( $comment_markup ) ) {
						$comments_array[] = $comment_markup;
					}
				}

			}

			// Wrap in list if we get some.
			if ( ! empty( $comments_array ) ) {

				// Open ul.
				$page_content .= '<ol class="comment_activity">' . "\n\n";

				// Add comments.
				$page_content .= implode( '', $comments_array );

				// Close ul.
				$page_content .= '</ol><!-- /comment_activity -->' . "\n\n";

			}

		}

		// --<
		return $page_content;

	}

endif;



if ( ! function_exists( 'commentpress_get_comment_activity_item' ) ) :

	/**
	 * Get comment formatted for the activity sidebar.
	 *
	 * @since 3.3
	 *
	 * @param object $comment The comment object.
	 * @return string $item_html The modified comment HTML.
	 */
	function commentpress_get_comment_activity_item( $comment ) {

		// Enable WordPress API on comment.
		$GLOBALS['comment'] = $comment;

		// Declare access to globals.
		global $commentpress_core, $post;

		// Init markup.
		$item_html = '';

		// Only comments until we decide what to do with pingbacks and trackbacks.
		if ( $comment->comment_type == 'pingback' ) {
			return $item_html;
		}
		if ( $comment->comment_type == 'trackback' ) {
			return $item_html;
		}

		// Test for anonymous comment - usually generated by WordPress itself in multisite installs.
		if ( empty( $comment->comment_author ) ) {
			$comment->comment_author = __( 'Anonymous', 'commentpress-core' );
		}

		// Was it a registered user?
		if ( $comment->user_id != '0' ) {

			// Get user details.
			$user = get_userdata( $comment->user_id );

			// Get user link.
			$user_link = commentpress_get_user_link( $user, $comment );

			// Construct author citation.
			$author = '<cite class="fn"><a href="' . $user_link . '">' . get_comment_author() . '</a></cite>';

			// Construct link to user URL.
			$author = ( $user_link != '' && $user_link != 'http://' ) ?
				'<cite class="fn"><a href="' . $user_link . '">' . get_comment_author() . '</a></cite>' :
				'<cite class="fn">' . get_comment_author() . '</cite>';

		} else {

			// Construct link to commenter URL.
			$author = ( $comment->comment_author_url != '' && $comment->comment_author_url != 'http://' ) ?
				'<cite class="fn"><a href="' . $comment->comment_author_url . '">' . get_comment_author() . '</a></cite>' :
				'<cite class="fn">' . get_comment_author() . '</cite>';

		}

		// Approved comment?
		if ( $comment->comment_approved == '0' ) {
			$comment_text = '<p><em>' . __( 'Comment awaiting moderation', 'commentpress-core' ) . '</em></p>';
		} else {
			$comment_text = get_comment_text( $comment->comment_ID );
		}

		// Default to not on post.
		$is_on_current_post = '';

		// On current post?
		if ( is_singular() && is_object( $post ) && $comment->comment_post_ID == $post->ID ) {

			// Access paging globals.
			global $multipage, $page;

			// Is it the same page, if paged?
			if ( $multipage ) {

				// If it has a text sig.
				if (
					! is_null( $comment->comment_signature ) &&
					$comment->comment_signature != ''
				) {

					// Set key.
					$key = '_cp_comment_page';

					// If the custom field already has a value.
					if ( get_comment_meta( $comment->comment_ID, $key, true ) != '' ) {

						// Get comment's page from meta.
						$page_num = get_comment_meta( $comment->comment_ID, $key, true );

						// Is it this one?
						if ( $page_num == $page ) {

							// Is the right page.
							$is_on_current_post = ' comment_on_post';

						}

					}

				} else {

					// It's always the right page for page-level comments.
					$is_on_current_post = ' comment_on_post';

				}

			} else {

				// Must be the right page.
				$is_on_current_post = ' comment_on_post';

			}

		}

		// Open li.
		$item_html .= '<li><!-- item li -->' . "\n\n";

		// Show the comment.
		$item_html .= '
	<div class="comment-wrapper">

	<div class="comment-identifier">
	' . get_avatar( $comment, $size = '32' ) . '
	' . $author . '
	<p class="comment_activity_date"><a class="comment_activity_link' . $is_on_current_post . '" href="' . htmlspecialchars( get_comment_link() ) . '">' . sprintf( __( '%1$s at %2$s', 'commentpress-core' ), get_comment_date(), get_comment_time() ) . '</a></p>
	</div><!-- /comment-identifier -->



	<div class="comment-content">
	' . apply_filters( 'comment_text', $comment_text ) . '
	</div><!-- /comment-content -->

	<div class="reply"><p><a class="comment_activity_link' . $is_on_current_post . '" href="' . htmlspecialchars( get_comment_link() ) . '">' . __( 'See in context', 'commentpress-core' ) . '</a></p></div><!-- /reply -->

	</div><!-- /comment-wrapper -->

	';

		// Close li.
		$item_html .= '</li><!-- /item li -->' . "\n\n";

		// --<
		return $item_html;

	}

endif;



if ( ! function_exists( 'commentpress_comments_by_para_format_pings' ) ) :

	/**
	 * Format the markup for the "pingbacks and trackbacks" section of comments.
	 *
	 * @since 3.8.10
	 *
	 * @param int $comment_count The number of comments on the block.
	 * @return array $return Data array containing the translated strings.
	 */
	function commentpress_comments_by_para_format_pings( $comment_count ) {

		// Init return.
		$return = [];

		// Construct entity text.
		$return['entity_text'] = __( 'pingback or trackback', 'commentpress-core' );

		// Construct permalink text.
		$return['permalink_text'] = __( 'Permalink for pingbacks and trackbacks', 'commentpress-core' );

		// Construct comment count.
		$return['comment_text'] = sprintf(
			_n( '<span>%d</span> Pingback or trackback', '<span>%d</span> Pingbacks and trackbacks', $comment_count, 'commentpress-core' ),
			$comment_count // Substitution.
		);

		// Construct heading text.
		$return['heading_text'] = sprintf( '<span>%s</span>', $return['comment_text'] );

		// --<
		return $return;

	}

endif;



if ( ! function_exists( 'commentpress_comments_by_para_format_block' ) ) :

	/**
	 * Format the markup for "comments by block" section of comments.
	 *
	 * @since 3.8.10
	 *
	 * @param int $comment_count The number of comments on the block.
	 * @param int $para_num The sequential number of the block.
	 * @return array $return Data array containing the translated strings.
	 */
	function commentpress_comments_by_para_format_block( $comment_count, $para_num ) {

		// Init return.
		$return = [];

		// Access plugin.
		global $commentpress_core;

		// Get block name.
		$block_name = __( 'paragraph', 'commentpress-core' );
		if ( is_object( $commentpress_core ) ) {
			$block_name = $commentpress_core->parser->lexia_get();
		}

		// Construct entity text.
		$return['entity_text'] = sprintf(
			__( '%1$s %2$s', 'commentpress-core' ),
			$block_name,
			$para_num
		);

		// Construct permalink text.
		$return['permalink_text'] = sprintf(
			__( 'Permalink for comments on %1$s %2$s', 'commentpress-core' ),
			$block_name,
			$para_num
		);

		// Construct comment count.
		$return['comment_text'] = sprintf(
			_n( '<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comment</span>', '<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comments</span>', $comment_count, 'commentpress-core' ),
			$comment_count // Substitution.
		);

		// Construct heading text.
		$return['heading_text'] = sprintf(
			__( '%1$s on <span class="source_block">%2$s %3$s</span>', 'commentpress-core' ),
			$return['comment_text'],
			$block_name,
			$para_num
		);

		// --<
		return $return;

	}

endif;



if ( ! function_exists( 'commentpress_get_comments_by_para' ) ) :

	/**
	 * Get comments delimited by paragraph.
	 *
	 * @since 3.0
	 */
	function commentpress_get_comments_by_para() {

		/**
		 * Overwrite the content width for the comments column.
		 *
		 * This is set to an arbitrary width that *sort of* works for the comments
		 * column for all CommentPress themes. It can be overridden by this filter
		 * if a particular theme or child theme wants to do so. The content width
		 * determines the default width for oEmbedded content.
		 *
		 * @since 3.9
		 *
		 * @param int $content_width An general content width for all themes.
		 * @return int $content_width A specific content width for a specific theme.
		 */
		global $content_width;
		$content_width = apply_filters( 'commentpress_comments_content_width', 380 );

		// Allow oEmbed in comments.
		global $wp_embed;
		if ( $wp_embed instanceof WP_Embed ) {
			add_filter( 'comment_text', [ $wp_embed, 'autoembed' ], 1 );
		}

		// Allow plugins to precede comments.
		do_action( 'commentpress_before_scrollable_comments' );

		// Declare access to globals.
		global $post, $commentpress_core;

		// Get approved comments for this post, sorted comments by text signature.
		$comments_sorted = $commentpress_core->get_sorted_comments( $post->ID );

		// Key for starting paragraph number.
		$key = '_cp_starting_para_number';

		// Default starting paragraph number.
		$start_num = 1;

		// Override if the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {
			$start_num = absint( get_post_meta( $post->ID, $key, true ) );
		}

		// If we have any.
		if ( count( $comments_sorted ) > 0 ) {

			// Allow for BuddyPress registration.
			$registration = false;
			if ( function_exists( 'bp_get_signup_allowed' ) && bp_get_signup_allowed() ) {
				$registration = true;
			}

			// Maybe redirect to BuddyPress sign-up.
			if ( $registration ) {
				$redirect = bp_get_signup_page();
			} else {
				$redirect = wp_login_url( get_permalink() );
			}

			// Init allowed to comment.
			$login_to_comment = false;

			// If we have to log in to comment.
			if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {
				$login_to_comment = true;
			}

			// Default comment type to get.
			$comment_type = 'all';

			// The built in walker works just fine since WordPress 3.8.
			$args = [
				'style' => 'ol',
				'type' => $comment_type,
				'callback' => 'commentpress_comments',
			];

			// Get singular post type label.
			$current_type = get_post_type();
			$post_type = get_post_type_object( $current_type );

			/**
			 * Assign name of post type.
			 *
			 * @since 3.8.10
			 *
			 * @param str $singular_name The singular label for this post type.
			 * @param str $current_type The post type identifier.
			 * @return str $singular_name The modified label for this post type.
			 */
			$post_type_name = apply_filters( 'commentpress_lexia_post_type_name', $post_type->labels->singular_name, $current_type );

			// Init counter for text_signatures array.
			$sig_counter = 0;

			// Init array for tracking text sigs.
			$used_text_sigs = [];

			// Loop through each paragraph.
			foreach ( $comments_sorted as $text_signature => $comments ) {

				// Count comments.
				$comment_count = count( $comments );

				// Switch, depending on key.
				switch ( $text_signature ) {

					// Whole page comments.
					case 'WHOLE_PAGE_OR_POST_COMMENTS':
						// Clear text signature.
						$text_sig = '';

						// Clear the paragraph number.
						$para_num = '';

						// Get the markup we need for this.
						$markup = commentpress_comments_by_para_format_whole( $post_type_name, $current_type, $comment_count );

						break;

					// Pingbacks and trackbacks.
					case 'PINGS_AND_TRACKS':
						// Set "unique-enough" text signature.
						$text_sig = 'pingbacksandtrackbacks';

						// Clear the paragraph number.
						$para_num = '';

						// Get the markup we need for this.
						$markup = commentpress_comments_by_para_format_pings( $comment_count );

						break;

					// Textblock comments.
					default:
						// Get text signature.
						$text_sig = $text_signature;

						// Paragraph number.
						$para_num = $sig_counter + ( $start_num - 1 );

						// Get the markup we need for this.
						$markup = commentpress_comments_by_para_format_block( $comment_count, $para_num );

				}

				// Init no comment class.
				$no_comments_class = '';

				// Override if there are no comments (for print stylesheet to hide them).
				if ( $comment_count == 0 ) {
					$no_comments_class = ' class="no_comments"';
				}

				// Exclude pings if there are none.
				if ( $comment_count == 0 && $text_signature == 'PINGS_AND_TRACKS' ) {

					// Skip.

				} else {

					// Show heading.
					echo '<h3 id="para_heading-' . $text_sig . '"' . $no_comments_class . '><a class="comment_block_permalink" title="' . $markup['permalink_text'] . '" href="#para_heading-' . $text_sig . '">' . $markup['heading_text'] . '</a></h3>' . "\n\n";

					// Override if there are no comments (for print stylesheet to hide them).
					if ( $comment_count == 0 ) {
						$no_comments_class = ' no_comments';
					}

					// Open paragraph wrapper.
					echo '<div id="para_wrapper-' . $text_sig . '" class="paragraph_wrapper' . $no_comments_class . '">' . "\n\n";

					// Have we already used this text signature?
					if ( in_array( $text_sig, $used_text_sigs ) ) {

						// Show some kind of message.
						// Should not be necessary now that we ensure unique text sigs.
						echo '<div class="reply_to_para" id="reply_to_para-' . $para_num . '">' . "\n" .
							'<p>' .
								__( 'It appears that this paragraph is a duplicate of a previous one.', 'commentpress-core' ) .
							'</p>' . "\n" .
						'</div>' . "\n\n";

					} else {

						// If we have comments.
						if ( count( $comments ) > 0 ) {

							// Open commentlist.
							echo '<ol class="commentlist">' . "\n\n";

							// Use WordPress 2.7+ functionality.
							wp_list_comments( $args, $comments );

							// Close commentlist.
							echo '</ol>' . "\n\n";

						}

						/**
						 * Allow plugins to append to paragraph level comments.
						 *
						 * @param str $text_sig The text signature of the paragraph.
						 */
						do_action( 'commentpress_after_paragraph_comments', $text_sig );

						// Add to used array.
						$used_text_sigs[] = $text_sig;

						// Only add comment-on-para link if comments are open and it's not the pingback section.
						if ( 'open' == $post->comment_status && $text_signature != 'PINGS_AND_TRACKS' ) {

							// If we have to log in to comment.
							if ( $login_to_comment ) {

								// The link text depending on whether we've got registration.
								if ( $registration ) {
									$prompt = sprintf(
										__( 'Create an account to leave a comment on %s', 'commentpress-core' ),
										$markup['entity_text']
									);
								} else {
									$prompt = sprintf(
										__( 'Login to leave a comment on %s', 'commentpress-core' ),
										$markup['entity_text']
									);
								}

								/**
								 * Filter the prompt text.
								 *
								 * @since 3.9
								 *
								 * @param str $prompt The link text when login is required.
								 * @param bool $registration True if registration is open, false otherwise.
								 */
								$prompt = apply_filters( 'commentpress_reply_to_prompt_text', $prompt, $registration );

								// Leave comment link.
								echo '<div class="reply_to_para" id="reply_to_para-' . $para_num . '">' . "\n" .
									'<p><a class="reply_to_para" rel="nofollow" href="' . $redirect . '">' .
										$prompt .
									'</a></p>' . "\n" .
								'</div>' . "\n\n";

							} else {

								// Construct onclick content.
								$onclick = "return addComment.moveFormToPara( '$para_num', '$text_sig', '$post->ID' )";

								// Construct onclick attribute.
								$onclick = apply_filters(
									'commentpress_reply_to_para_link_onclick',
									' onclick="' . $onclick . '"'
								);

								// Just show replytopara.
								$query = remove_query_arg( [ 'replytocom' ] );

								// Add param to querystring.
								$query = esc_url(
									add_query_arg(
										[ 'replytopara' => $para_num ],
										$query
									)
								);

								// Construct href attribute.
								$href = apply_filters(
									'commentpress_reply_to_para_link_href',
									$query . '#respond', // Add respond ID.
									$text_sig
								);

								// Construct link content.
								$link_content = sprintf(
									__( 'Leave a comment on %s', 'commentpress-core' ),
									$markup['entity_text']
								);

								// Allow overrides.
								$link_content = apply_filters(
									'commentpress_reply_to_para_link_text',
									$link_content,
									$markup['entity_text']
								);

								// Leave comment link.
								echo '<div class="reply_to_para" id="reply_to_para-' . $para_num . '">' . "\n" .
									'<p><a class="reply_to_para" href="' . $href . '"' . $onclick . '>' .
										$link_content .
									'</a></p>' . "\n" .
								'</div>' . "\n\n";

							}

						}

					}

					/**
					 * Allow plugins to append to paragraph wrappers.
					 *
					 * @param str $text_sig The text signature of the paragraph.
					 */
					do_action( 'commentpress_after_paragraph_wrapper', $text_sig );

					// Close paragraph wrapper.
					echo '</div>' . "\n\n\n\n";

				}

				// Increment signature array counter.
				$sig_counter++;

			} // End comments-per-para loop

		}

		// Allow plugins to follow comments.
		do_action( 'commentpress_after_scrollable_comments' );

	}

endif;



if ( ! function_exists( 'commentpress_comment_form_title' ) ) :

	/**
	 * Alternative to the built-in WordPress function.
	 *
	 * @since 3.0
	 *
	 * @param str $no_reply_text The text to show when there are no comments.
	 * @param str $reply_to_comment_text The text to show when there are comments.
	 * @param str $reply_to_para_text The text to show on paragraphs when there are comments.
	 * @param str $link_to_parent The link to the parent comment.
	 */
	function commentpress_comment_form_title( $no_reply_text = '', $reply_to_comment_text = '', $reply_to_para_text = '', $link_to_parent = true ) {

		// Sanity checks.
		if ( $no_reply_text == '' ) {
			$no_reply_text = __( 'Leave a reply', 'commentpress-core' );
		}
		if ( $reply_to_comment_text == '' ) {
			$reply_to_comment_text = __( 'Leave a reply to %s', 'commentpress-core' );
		}
		if ( $reply_to_para_text == '' ) {
			$reply_to_para_text = __( 'Leave a comment on %s', 'commentpress-core' );
		}

		// Declare access to globals.
		global $comment, $commentpress_core;

		// Get comment ID to reply to from URL query string.
		$reply_to_comment_id = isset( $_GET['replytocom'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['replytocom'] ) ) : 0;

		// Get paragraph number to reply to from URL query string.
		$reply_to_para_id = isset( $_GET['replytopara'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['replytopara'] ) ) : 0;

		// If we have no comment ID and no paragraph ID to reply to.
		if ( $reply_to_comment_id == 0 && $reply_to_para_id === 0 ) {

			// Write default title to page.
			echo $no_reply_text;

		} else {

			// If we have a comment ID and NO paragraph ID to reply to.
			if ( $reply_to_comment_id !== 0 && $reply_to_para_id === 0 ) {

				// Get comment.
				$comment = get_comment( $reply_to_comment_id );

				// Get link to comment.
				$author = ( $link_to_parent ) ?
					'<a href="#comment-' . get_comment_ID() . '">' . get_comment_author() . '</a>' :
					get_comment_author();

				// Write to page.
				printf( $reply_to_comment_text, $author );

			} else {

				// Get paragraph text signature.
				$text_sig = $commentpress_core->get_text_signature( $reply_to_para_id );

				// Get link to paragraph.
				if ( $link_to_parent ) {

					// Construct link text.
					$para_text = sprintf(
						_x( '%1$s %2$s', 'The first substitution is the block name e.g. "paragraph". The second is the numeric comment count.', 'commentpress-core' ),
						ucfirst( $commentpress_core->parser->lexia_get() ),
						$reply_to_para_id
					);

					// Construct paragraph.
					$paragraph = '<a href="#para_heading-' . $text_sig . '">' . $para_text . '</a>';

				} else {

					// Construct paragraph without link.
					$paragraph = sprintf(
						_x( '%1$s %2$s', 'The first substitution is the block name e.g. "paragraph". The second is the numeric comment count.', 'commentpress-core' ),
						ucfirst( $commentpress_core->parser->lexia_get() ),
						$para_num
					);

				}

				// Write to page.
				printf( $reply_to_para_text, $paragraph );

			}

		}

	}

endif;



if ( ! function_exists( 'commentpress_comment_reply_link' ) ) :

	/**
	 * Alternative to the built-in WordPress function.
	 *
	 * @since 3.0
	 *
	 * @param array $args The reply links arguments.
	 * @param object $comment The comment.
	 * @param object $post The post.
	 */
	function commentpress_comment_reply_link( $args = [], $comment = null, $post = null ) {

		// Set some defaults.
		$defaults = [
			'add_below' => 'comment',
			'respond_id' => 'respond',
			'reply_text' => __( 'Reply', 'commentpress-core' ),
			'login_text' => __( 'Log in to Reply', 'commentpress-core' ),
			'depth' => 0,
			'before' => '',
			'after' => '',
		];

		// Parse them.
		$args = wp_parse_args( $args, $defaults );

		if ( 0 == $args['depth'] || $args['max_depth'] <= $args['depth'] ) {
			return;
		}

		// Convert to vars.
		extract( $args, EXTR_SKIP );

		// Get the obvious.
		$comment = get_comment( $comment );
		$post = get_post( $post );

		// Kick out if comments closed.
		if ( 'open' != $post->comment_status ) {
			return false;
		}

		// Init link.
		$link = '';

		// If we have to log in to comment.
		if ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) {

			// Construct link.
			$link = '<a rel="nofollow" href="' . site_url( 'wp-login.php?redirect_to=' . get_permalink() ) . '">' . $login_text . '</a>';

		} else {

			// Just show replytocom.
			$query = remove_query_arg( [ 'replytopara' ], get_permalink( $post->ID ) );

			// Define query string.
			$addquery = esc_url(
				add_query_arg(
					[ 'replytocom' => $comment->comment_ID ],
					$query
				)
			);

			// Define link.
			$link = "<a rel='nofollow' class='comment-reply-link' href='" . $addquery . '#' . $respond_id . "' onclick='return addComment.moveForm(\"$add_below-$comment->comment_ID\", \"$comment->comment_ID\", \"$respond_id\", \"$post->ID\", \"$comment->comment_signature\")'>$reply_text</a>";

		}

		// --<
		return apply_filters( 'comment_reply_link', $before . $link . $after, $args, $comment, $post );

	}

endif;



if ( ! function_exists( 'commentpress_comments' ) ) :

	/**
	 * Custom comments display function.
	 *
	 * @since 3.0
	 *
	 * @param object $comment The comment object.
	 * @param array $args The comment arguments.
	 * @param int $depth The comment depth.
	 */
	function commentpress_comments( $comment, $args, $depth ) {

		// Build comment as html.
		echo commentpress_get_comment_markup( $comment, $args, $depth );

	}

endif;



if ( ! function_exists( 'commentpress_get_comment_markup' ) ) :

	/**
	 * Wrap comment in its markup.
	 *
	 * @since 3.0
	 *
	 * @param object $comment The comment object.
	 * @param array $args The comment arguments.
	 * @param int $depth The comment depth.
	 * @return str $html The comment markup.
	 */
	function commentpress_get_comment_markup( $comment, $args, $depth ) {

		// Enable WordPress API on comment.
		$GLOBALS['comment'] = $comment;

		// Was it a registered user?
		if ( $comment->user_id != '0' ) {

			// Get user details.
			$user = get_userdata( $comment->user_id );

			// Get user link.
			$user_link = commentpress_get_user_link( $user, $comment );

			// Construct author citation.
			$author = ( $user_link != '' && $user_link != 'http://' ) ?
				'<cite class="fn"><a href="' . $user_link . '">' . get_comment_author() . '</a></cite>' :
				'<cite class="fn">' . get_comment_author() . '</cite>';

		} else {

			// Construct link to commenter url for unregistered users.
			if (
				$comment->comment_author_url != '' &&
				$comment->comment_author_url != 'http://' &&
				$comment->comment_approved != '0'
			) {
				$author = '<cite class="fn"><a href="' . $comment->comment_author_url . '">' . get_comment_author() . '</a></cite>';
			} else {
				$author = '<cite class="fn">' . get_comment_author() . '</cite>';
			}

		}

		// Check moderation status.
		if ( $comment->comment_approved == '0' ) {
			$comment_text = '<p><em>' . __( 'Comment awaiting moderation', 'commentpress-core' ) . '</em></p>';
		} else {
			$comment_text = get_comment_text();
		}

		// Empty reply div by default.
		$comment_reply = '';

		// Enable access to post.
		global $post;

		// Can we reply?
		if (

			// Not if comments are closed.
			$post->comment_status == 'open' &&

			// We don't want reply to on pingbacks.
			$comment->comment_type != 'pingback' &&

			// We don't want reply to on trackbacks.
			$comment->comment_type != 'trackback' &&

			// Nor on unapproved comments.
			$comment->comment_approved == '1'

		) {

			// Are we threading comments?
			if ( get_option( 'thread_comments', false ) ) {

				// Custom comment_reply_link.
				$comment_reply = commentpress_comment_reply_link( array_merge(
					$args,
					[
						'reply_text' => sprintf( __( 'Reply to %s', 'commentpress-core' ), get_comment_author() ),
						'depth' => $depth,
						'max_depth' => $args['max_depth'],
					]
				) );

				// Wrap in div.
				$comment_reply = '<div class="reply">' . $comment_reply . '</div><!-- /reply -->';

			}

		}

		// Init edit link.
		$editlink = '';

		// If logged in and has capability.
		if (
			is_user_logged_in() &&
			current_user_can( 'edit_comment', $comment->comment_ID )
		) {

			// Set default edit link title text.
			$edit_title_text = apply_filters(
				'cp_comment_edit_link_title_text',
				__( 'Edit this comment', 'commentpress-core' )
			);

			// Set default edit link text.
			$edit_text = apply_filters(
				'cp_comment_edit_link_text',
				__( 'Edit', 'commentpress-core' )
			);

			// Get edit comment link.
			$editlink = '<span class="alignright comment-edit"><a class="comment-edit-link" href="' . get_edit_comment_link() . '" title="' . $edit_title_text . '">' . $edit_text . '</a></span>';

			// Add a filter for plugins.
			$editlink = apply_filters( 'cp_comment_edit_link', $editlink, $comment );

		}

		// Add a nopriv filter for plugins.
		$editlink = apply_filters( 'cp_comment_action_links', $editlink, $comment );

		// Get comment class(es).
		$comment_class = comment_class( null, $comment->comment_ID, $post->ID, false );

		// If orphaned, add class to identify as such.
		$comment_orphan = ( isset( $comment->orphan ) ) ? ' comment-orphan' : '';

		// Construct permalink.
		$comment_permalink = sprintf( __( '%1$s at %2$s', 'commentpress-core' ), get_comment_date(), get_comment_time() );

		// Stripped source.
		$html = '
			<li id="li-comment-' . $comment->comment_ID . '" ' . $comment_class . '>
			<div class="comment-wrapper">
			<div id="comment-' . $comment->comment_ID . '">

			<div class="comment-identifier' . $comment_orphan . '">
			' . apply_filters( 'commentpress_comment_identifier_prepend', '', $comment ) . '
			' . get_avatar( $comment, $size = '32' ) . '
			' . $editlink . '
			' . $author . '
			<a class="comment_permalink" href="' . htmlspecialchars( get_comment_link() ) . '" title="' . __( 'Permalink for this comment', 'commentpress-core' ) . '"><span class="comment_permalink_copy"></span>' . $comment_permalink . '</a>
			' . apply_filters( 'commentpress_comment_identifier_append', '', $comment ) . '
			</div><!-- /comment-identifier -->

			<div class="comment-content' . $comment_orphan . '">
			' . apply_filters( 'comment_text', $comment_text ) . '
			</div><!-- /comment-content -->

			' . $comment_reply . '

			</div><!-- /comment-' . $comment->comment_ID . ' -->
			</div><!-- /comment-wrapper -->
			';

		// --<
		return $html;

	}

endif;



if ( ! function_exists( 'commentpress_comments_by_para_format_whole' ) ) :

	/**
	 * Format the markup for the "whole page" section of comments.
	 *
	 * @since 3.8.10
	 *
	 * @param str $post_type_name The singular name of the post type.
	 * @param str $post_type The post type identifier.
	 * @param int $comment_count The number of comments on the block.
	 * @return array $return Data array containing the translated strings.
	 */
	function commentpress_comments_by_para_format_whole( $post_type_name, $post_type, $comment_count ) {

		// Init return.
		$return = [];

		// Construct entity text.
		$return['entity_text'] = sprintf(
			__( 'the whole %s', 'commentpress-core' ),
			$post_type_name
		);

		/**
		 * Allow "the whole entity" text to be filtered.
		 *
		 * This is primarily for "media", where it makes little sense the comment on
		 * "the whole image", for example.
		 *
		 * @since 3.9
		 *
		 * @param str $entity_text The current entity text.
		 * @param str $post_type_name The singular name of the post type.
		 * @return str $entity_text The modified entity text.
		 */
		$return['entity_text'] = apply_filters(
			'commentpress_lexia_whole_entity_text',
			$return['entity_text'],
			$post_type_name,
			$post_type
		);

		// Construct permalink text.
		$return['permalink_text'] = sprintf(
			__( 'Permalink for comments on %s', 'commentpress-core' ),
			$return['entity_text']
		);

		// Construct comment count.
		$return['comment_text'] = sprintf(
			_n( '<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comment</span>', '<span class="cp_comment_num">%d</span> <span class="cp_comment_word">Comments</span>', $comment_count, 'commentpress-core' ),
			$comment_count // Substitution.
		);

		// Construct heading text.
		$return['heading_text'] = sprintf(
			__( '%1$s on <span class="source_block">%2$s</span>', 'commentpress-core' ),
			$return['comment_text'],
			$return['entity_text']
		);

		// --<
		return $return;

	}

endif;



if ( ! function_exists( 'commentpress_add_wp_editor' ) ) :

	/**
	 * Adds our styles to the TinyMCE editor.
	 *
	 * @since 3.5
	 *
	 * @return bool True if the editor has been added, false otherwise.
	 */
	function commentpress_add_wp_editor() {

		// Init option.
		$rich_text = false;

		// Kick out if wp_editor doesn't exist.
		// TinyMCE will be handled by including the script using the pre- wp_editor() method.
		if ( ! function_exists( 'wp_editor' ) ) {
			return false;
		}

		// Kick out if plugin not active.
		global $commentpress_core;
		if ( ! is_object( $commentpress_core ) ) {
			return false;
		}

		// Only allow through if plugin says so.
		if ( ! $commentpress_core->display->is_tinymce_allowed() ) {
			return false;
		}

		// Basic buttons.
		$basic_buttons = [
			'bold',
			'italic',
			'underline',
			'|',
			'bullist',
			'numlist',
			'|',
			'link',
			'unlink',
			'|',
			'removeformat',
			'fullscreen',
		];

		/**
		 * Add our buttons but allow filtering.
		 *
		 * @since 3.5
		 *
		 * @param array $basic_buttons The default buttons.
		 */
		$mce_buttons = apply_filters( 'cp_tinymce_buttons', $basic_buttons );

		/**
		 * Allow media buttons setting to be overridden.
		 *
		 * @since 3.5
		 *
		 * @param bool True by default - buttons are allowed.
		 */
		$media_buttons = apply_filters( 'commentpress_rte_media_buttons', true );

		// TinyMCE 4 - allow tinymce config to be overridden.
		$tinymce_config = apply_filters(
			'commentpress_rte_tinymce',
			[
				'theme' => 'modern',
				'statusbar' => false,
			]
		);

		// No need for editor CSS.
		$editor_css = '';

		// Allow quicktags setting to be overridden.
		$quicktags = apply_filters(
			'commentpress_rte_quicktags',
			[
				'buttons' => 'strong,em,ul,ol,li,link,close',
			]
		);

		// Our settings.
		$settings = [

			// Configure comment textarea.
			'media_buttons' => $media_buttons,
			'textarea_name' => 'comment',
			'textarea_rows' => 10,

			// Might as well start with teeny.
			'teeny' => true,

			// Give the iframe a white background.
			'editor_css' => $editor_css,

			// Configure TinyMCE.
			'tinymce' => $tinymce_config,

			// Configure Quicktags.
			'quicktags' => $quicktags,

		];

		// Create editor.
		wp_editor(
			'', // Initial content.
			'comment', // ID of comment textarea.
			$settings
		);

		// Access WordPress version.
		global $wp_version;

		// Add styles.
		wp_enqueue_style(
			'commentpress-editor-css',
			wp_admin_css_uri( 'css/edit' ),
			[ 'dashicons', 'open-sans' ],
			$wp_version, // Version.
			'all' // Media.
		);

		// Don't show textarea.
		return true;

	}

endif;



if ( ! function_exists( 'commentpress_assign_default_editor' ) ) :

	/**
	 * Makes TinyMCE the default editor on the front end.
	 *
	 * @since 3.0
	 *
	 * @param str $r The default editor as set by WordPress.
	 * @return str 'tinymce' our overridden default editor.
	 */
	function commentpress_assign_default_editor( $r ) {

		// Only on front-end.
		if ( is_admin() ) {
			return $r;
		}

		// Always return 'tinymce' as the default editor, or else the comment form will not show up.

		// --<
		return 'tinymce';

	}

endif;

// Add callback for the above.
add_filter( 'wp_default_editor', 'commentpress_assign_default_editor', 10, 1 );



if ( ! function_exists( 'commentpress_add_tinymce_styles' ) ) :

	/**
	 * Adds our styles to the TinyMCE editor.
	 *
	 * @since 3.0
	 *
	 * @param str $mce_css The default TinyMCE stylesheets as set by WordPress.
	 * @return str $mce_css The list of stylesheets with ours added.
	 */
	function commentpress_add_tinymce_styles( $mce_css ) {

		// Only on front-end.
		if ( is_admin() ) {
			return $mce_css;
		}

		// Add comma if not empty.
		if ( ! empty( $mce_css ) ) {
			$mce_css .= ',';
		}

		// Add our editor styles.
		$mce_css .= get_template_directory_uri() . '/assets/css/comment-form.css';

		// --<
		return $mce_css;

	}

endif;

// Add callback for the above.
add_filter( 'mce_css', 'commentpress_add_tinymce_styles' );



if ( ! function_exists( 'commentpress_add_tinymce_nextpage_button' ) ) :

	/**
	 * Adds the Next Page button to the TinyMCE editor.
	 *
	 * @since 3.5
	 *
	 * @param array $buttons The default TinyMCE buttons as set by WordPress.
	 * @return array $buttons The buttons with More removed.
	 */
	function commentpress_add_tinymce_nextpage_button( $buttons ) {

		// Only on back-end.
		if ( ! is_admin() ) {
			return $buttons;
		}

		// Try and place Next Page after More button.
		$pos = array_search( 'wp_more', $buttons, true );

		// Is it there?
		if ( $pos !== false ) {

			// Get array up to that point.
			$tmp_buttons = array_slice( $buttons, 0, $pos + 1 );

			// Add Next Page button.
			$tmp_buttons[] = 'wp_page';

			// Recombine.
			$buttons = array_merge( $tmp_buttons, array_slice( $buttons, $pos + 1 ) );

		}

		// --<
		return $buttons;

	}

endif;

// Add callback for the above.
add_filter( 'mce_buttons', 'commentpress_add_tinymce_nextpage_button' );



if ( ! function_exists( 'commentpress_assign_editor_buttons' ) ) :

	/**
	 * Assign our buttons to TinyMCE in teeny mode.
	 *
	 * @since 3.5
	 *
	 * @param array $buttons The default editor buttons as set by WordPress.
	 * @return array The overridden editor buttons.
	 */
	function commentpress_assign_editor_buttons( $buttons ) {

		// Basic buttons.
		return [
			'bold',
			'italic',
			'underline',
			'|',
			'bullist',
			'numlist',
			'|',
			'link',
			'unlink',
			'|',
			'removeformat',
			'fullscreen',
		];

	}

endif;

// Add callback for the above.
add_filter( 'teeny_mce_buttons', 'commentpress_assign_editor_buttons' );



if ( ! function_exists( 'commentpress_comment_post_redirect' ) ) :

	/**
	 * Filter comment post redirects for multipage posts.
	 *
	 * @since 3.5
	 *
	 * @param str $link The link to the comment.
	 * @param object $comment The comment object.
	 */
	function commentpress_comment_post_redirect( $link, $comment ) {

		// Get URL of the page that submitted the comment.
		$page_url = isset( $_SERVER['HTTP_REFERER'] ) ? wp_unslash( $_SERVER['HTTP_REFERER'] ) : '';

		// Get anchor position.
		$hash = strpos( $page_url, '#' );

		// Well, do we have an anchor?
		if ( $hash !== false ) {

			// Yup, so strip it.
			$page_url = substr( $page_url, 0, $hash );

		}

		// Assume not AJAX.
		$ajax_token = '';

		// Is this an AJAX comment form submission?
		if ( isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) {
			if ( $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest' ) {

				// Yes, it's AJAX - some browsers cache POST, so invalidate.
				$ajax_token = '?cachebuster=' . time();

				// But, test for pretty permalinks.
				if ( false !== strpos( $page_url, '?' ) ) {

					// Pretty permalinks are off.
					$ajax_token = '&cachebuster=' . time();

				}

			}

		}

		// Construct cachebusting comment redirect.
		$link = $page_url . $ajax_token . '#comment-' . $comment->comment_ID;

		// --<
		return $link;

	}

endif;

// Add callback for the above, making it run early so it can be overridden by AJAX commenting.
add_filter( 'comment_post_redirect', 'commentpress_comment_post_redirect', 4, 2 );



if ( ! function_exists( 'commentpress_image_caption_shortcode' ) ) :

	/**
	 * Rebuild caption shortcode output.
	 *
	 * @since 3.5
	 *
	 * @param array $empty WordPress passes '' as the first param.
	 * @param array $attr Attributes attributed to the shortcode.
	 * @param str $content Optional. Shortcode content.
	 * @return str $caption The modified caption
	 */
	function commentpress_image_caption_shortcode( $empty, $attr, $content ) {

		// Get our shortcode vars.
		extract( shortcode_atts( [
			'id' => '',
			'align' => 'alignnone',
			'width' => '',
			'caption' => '',
		], $attr ) );

		if ( 1 > (int) $width || empty( $caption ) ) {
			return $content;
		}

		// Sanitise ID.
		if ( $id ) {
			$id = 'id="' . esc_attr( $id ) . '" ';
		}

		// Add space prior to alignment.
		$alignment = ' ' . esc_attr( $align );

		// Get width.
		$width = ( 0 + (int) $width );

		// Allow a few tags.
		$tags_to_allow = [
			'em' => [],
			'strong' => [],
			'a' => [ 'href' ],
		];

		// Sanitise caption.
		$caption = wp_kses( $caption, $tags_to_allow );

		// Force balance those tags.
		$caption = balanceTags( $caption, true );

		// Construct.
		$caption = '<!-- cp_caption_start -->' .
					'<span class="captioned_image' . $alignment . '" style="width: ' . $width . 'px">' .
						'<span ' . $id . ' class="wp-caption">' . do_shortcode( $content ) . '</span>' .
						'<small class="wp-caption-text">' . $caption . '</small>' .
					'</span>' .
					'<!-- cp_caption_end -->';

		// --<
		return $caption;

	}

endif;

// Add callback for the above.
add_filter( 'img_caption_shortcode', 'commentpress_image_caption_shortcode', 10, 3 );



if ( ! function_exists( 'commentpress_add_commentblock_button' ) ) :

	/**
	 * Add filters for adding our custom TinyMCE button.
	 *
	 * @since 3.3
	 */
	function commentpress_add_commentblock_button() {

		// Only on back-end.
		if ( ! is_admin() ) {
			return;
		}

		// Don't bother doing this stuff if the current user lacks permissions.
		if ( ! current_user_can( 'edit_posts' ) && ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// Add only if user can edit in Rich-text Editor mode.
		if ( get_user_option( 'rich_editing' ) == 'true' ) {
			add_filter( 'mce_external_plugins', 'commentpress_add_commentblock_tinymce_plugin' );
			add_filter( 'mce_buttons', 'commentpress_register_commentblock_button' );
		}

	}

endif;

// Init process for button control.
add_action( 'init', 'commentpress_add_commentblock_button' );



if ( ! function_exists( 'commentpress_register_commentblock_button' ) ) :

	/**
	 * Add our custom TinyMCE button.
	 *
	 * @since 3.3
	 *
	 * @param array $buttons The existing button array.
	 * @return array $buttons The modified button array.
	 */
	function commentpress_register_commentblock_button( $buttons ) {

		// Add our button to the editor button array.
		array_push( $buttons, '|', 'commentblock' );

		// --<
		return $buttons;

	}

endif;



if ( ! function_exists( 'commentpress_add_commentblock_tinymce_plugin' ) ) :

	/**
	 * Load the TinyMCE plugin : cp_editor_plugin.js
	 *
	 * @since 3.3
	 *
	 * @param array $plugin_array The existing TinyMCE plugin array.
	 * @return array $plugin_array The modified TinyMCE plugin array.
	 */
	function commentpress_add_commentblock_tinymce_plugin( $plugin_array ) {

		// Add comment block.
		$plugin_array['commentblock'] = get_template_directory_uri() . '/assets/js/tinymce/cp_editor_plugin.js';

		// --<
		return $plugin_array;

	}

endif;



if ( ! function_exists( 'commentpress_multipage_comment_link' ) ) :

	/**
	 * Filter comment permalinks for multipage posts.
	 *
	 * @since 3.5
	 *
	 * @param str $link The existing comment link.
	 * @param object $comment The comment object.
	 * @param array $args An array of extra arguments.
	 * @return str $link The modified comment link.
	 */
	function commentpress_multipage_comment_link( $link, $comment, $args ) {

		// Get multipage and post.
		global $multipage, $post;

		/*
		// Are there multiple (sub)pages?
		if ( is_object( $post ) && $multipage ) {
		*/

		// Exclude page level comments.
		if ( $comment->comment_signature != '' ) {

			// Init page num.
			$page_num = 1;

			// Set key.
			$key = '_cp_comment_page';

			// Get the page number if the custom field already has a value.
			if ( get_comment_meta( $comment->comment_ID, $key, true ) != '' ) {
				$page_num = get_comment_meta( $comment->comment_ID, $key, true );
			}

			// Get current comment info.
			$comment_path_info = wp_parse_url( $link );

			// Set comment path.
			$link = commentpress_get_post_multipage_url( $page_num, get_post( $comment->comment_post_ID ) ) . '#' . $comment_path_info['fragment'];

		}

		/*
		// Close multiple (sub)pages test.
		}
		*/

		// --<
		return $link;

	}

endif;

// Add callback for the above.
add_filter( 'get_comment_link', 'commentpress_multipage_comment_link', 10, 3 );



if ( ! function_exists( 'commentpress_get_post_multipage_url' ) ) :

	/**
	 * Get the URL fo a page in a multipage context.
	 *
	 * Copied from wp-includes/post-template.php _wp_link_page()
	 *
	 * @since 3.5
	 *
	 * @param int $i The page number.
	 * @param WP_Post $post The WordPress post object.
	 * @return str $url The URL to the subpage.
	 */
	function commentpress_get_post_multipage_url( $i, $post = '' ) {

		// If we have no passed value.
		if ( $post == '' ) {

			// We assume we're in the loop.
			global $post, $wp_rewrite;

			if ( 1 == $i ) {
				$url = get_permalink();
			} else {
				if ( '' == get_option( 'permalink_structure' ) || in_array( $post->post_status, [ 'draft', 'pending' ] ) ) {
					$url = add_query_arg( 'page', $i, get_permalink() );
				} elseif ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_on_front' ) == $post->ID ) {
					$url = trailingslashit( get_permalink() ) . user_trailingslashit( "$wp_rewrite->pagination_base/" . $i, 'single_paged' );
				} else {
					$url = trailingslashit( get_permalink() ) . user_trailingslashit( $i, 'single_paged' );
				}
			}

		} else {

			// Use passed post object.
			if ( 1 == $i ) {
				$url = get_permalink( $post->ID );
			} else {
				if ( '' == get_option( 'permalink_structure' ) || in_array( $post->post_status, [ 'draft', 'pending' ] ) ) {
					$url = add_query_arg( 'page', $i, get_permalink( $post->ID ) );
				} elseif ( 'page' == get_option( 'show_on_front' ) && get_option( 'page_on_front' ) == $post->ID ) {
					$url = trailingslashit( get_permalink( $post->ID ) ) . user_trailingslashit( "$wp_rewrite->pagination_base/" . $i, 'single_paged' );
				} else {
					$url = trailingslashit( get_permalink( $post->ID ) ) . user_trailingslashit( $i, 'single_paged' );
				}
			}

		}

		return esc_url( $url );

	}

endif;



