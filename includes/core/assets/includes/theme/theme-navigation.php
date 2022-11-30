<?php
/**
 * CommentPress Core Theme Page Navigation.
 *
 * Handles common Page Navigation functionality in CommentPress themes.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;



if ( ! function_exists( 'commentpress_page_navigation_is_login' ) ) :

	/**
	 * Checks if the current Entry is the WordPress Login Page.
	 *
	 * @since 4.0
	 *
	 * @return bool $is_login_page True if the current Page is the WordPress Login Page, false otherwise.
	 */
	function commentpress_page_navigation_is_login() {

		// Assume not.
		$is_login_page = false;

		// Get core plugin reference.
		$core = commentpress_core();

		// Check Entry.
		if ( ! empty( $core ) && $core->entry->is_login_page() ) {
			$is_login_page = true;
		}

		// --<
		return $is_login_page;

	}

endif;



if ( ! function_exists( 'commentpress_page_navigation_is_signup' ) ) :

	/**
	 * Checks if the current Entry is the WordPress Signup Page.
	 *
	 * @since 4.0
	 *
	 * @return bool $is_signup_page True if the current Page is the WordPress Signup Page, false otherwise.
	 */
	function commentpress_page_navigation_is_signup() {

		// Assume not.
		$is_signup_page = false;

		// Get core plugin reference.
		$core = commentpress_core();

		// Check Entry.
		if ( ! empty( $core ) && $core->entry->is_signup_page() ) {
			$is_signup_page = true;
		}

		// --<
		return $is_signup_page;

	}

endif;



if ( ! function_exists( 'commentpress_page_navigation_is_activate' ) ) :

	/**
	 * Checks if the current Entry is the WordPress Activation Page.
	 *
	 * @since 4.0
	 *
	 * @return bool $is_activate_page True if the current Page is the WordPress Activation Page, false otherwise.
	 */
	function commentpress_page_navigation_is_activate() {

		// Assume not.
		$is_activate_page = false;

		// Get core plugin reference.
		$core = commentpress_core();

		// Check Entry.
		if ( ! empty( $core ) && $core->entry->is_activate_page() ) {
			$is_activate_page = true;
		}

		// --<
		return $is_activate_page;

	}

endif;



if ( ! function_exists( 'commentpress_page_navigation_template' ) ) :

	/**
	 * Loads the Page Navigation template.
	 *
	 * @since 4.0
	 */
	function commentpress_page_navigation_template() {

		// Try to locate template.
		$template = locate_template( 'assets/templates/page_navigation.php' );

		/**
		 * Filters the located template.
		 *
		 * @since 3.4
		 *
		 * @param str $template The path to the template.
		 */
		$template = apply_filters( 'cp_template_page_navigation', $template );

		// Load it if we find it.
		if ( ! empty( $template ) ) {
			load_template( $template, false );
		}

	}

endif;



if ( ! function_exists( 'commentpress_page_navigation' ) ) :

	/**
	 * Builds the HTML of list items for Previous Page and Next Page, optionally with Comments.
	 *
	 * @since 3.0
	 *
	 * @param bool $with_comments True returns the Previous Page and Next Page with Comments.
	 * @return str $nav_list The unordered list of navigation links.
	 */
	function commentpress_page_navigation( $with_comments = false ) {

		// Init return.
		$nav_list = '';

		// Get Previous Page link.
		$previous_page_link = commentpress_page_navigation_get_previous_link( $with_comments );

		// Get Next Page link.
		$next_page_link = commentpress_page_navigation_get_next_link( $with_comments );

		// Bail if we get no Page links at all.
		if ( empty( $next_page_link ) && empty( $previous_page_link ) ) {
			return $nav_list;
		}

		// Build navigation list items.
		$previous_list_item = ! empty( $previous_page_link ) ? '<li class="alignleft">' . $previous_page_link . '</li>' : '';
		$next_list_item = ! empty( $next_page_link ) ? '<li class="alignright">' . $next_page_link . '</li>' : '';

		// Merge navigation list items.
		$nav_list = $previous_list_item . "\n" . $next_list_item . "\n";

		// --<
		return $nav_list;

	}

endif;



if ( ! function_exists( 'commentpress_page_navigation_list' ) ) :

	/**
	 * Echoes the list of "Previous Page" and "Next Page" links.
	 *
	 * Next and previous Pages with Comments can be requested.
	 *
	 * @since 3.0
	 *
	 * @param bool $with_comments Pass true to return links to Pages with Comments.
	 */
	function commentpress_page_navigation_list( $with_comments = false ) {

		// Try and get the list items.
		$list_items = commentpress_page_navigation( $with_comments );
		if ( empty( $list_items ) ) {
			return;
		}

		// Write to screen.
		echo '<ul>' . $list_items . '</ul>';

	}

endif;



if ( ! function_exists( 'commentpress_page_navigation_get_next_link' ) ) :

	/**
	 * Gets the markup for the "Next Page" Navigation link.
	 *
	 * @since 4.0
	 *
	 * @param bool $with_comments True returns the Next Page with Comments.
	 * @return str $link The markup for the "Next Page" Navigation link.
	 */
	function commentpress_page_navigation_get_next_link( $with_comments = false ) {

		// Init.
		$link = '';

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return $link;
		}

		// Get Next Page.
		$next_page = $core->nav->page_next_get( $with_comments );
		if ( ! is_object( $next_page ) ) {
			return $link;
		}

		// Override title if asking for Pages with Comments.
		$title = __( 'Next page', 'commentpress-core' );
		if ( $with_comments ) {
			$title = __( 'Next page with comments', 'commentpress-core' );
		}

		// Set an image if asking for Pages with Comments.
		$img = ( $with_comments === true ) ? '<img src="' . get_template_directory_uri() . '/assets/images/next.png" />' : '';

		/**
		 * Filters the "Next Page" Navigation link CSS ID.
		 *
		 * @since 4.0
		 *
		 * @param str The default "Next Page" Navigation link CSS ID.
		 */
		$css_id = apply_filters( 'commentpress/navigation/page/link/next/css_id', 'next_page' );

		// Build ID attribute.
		$id = ! empty( $css_id ) ? ' id="' . $css_id . '"' : '';

		/**
		 * Filters the "Next Page" Navigation link CSS classes.
		 *
		 * @since 4.0
		 *
		 * @param array The default "Next Page" Navigation link CSS classes.
		 */
		$css_classes = apply_filters( 'commentpress/navigation/page/link/next/css_classes', [ 'css_btn' ] );

		// Build class attribute.
		$class = ! empty( $css_classes ) ? ' class="' . implode( ' ', $css_classes ) . '"' : '';

		// Construct link.
		$link = $img .
			'<a href="' . get_permalink( $next_page->ID ) . '"' . $id . $class . ' title="' . esc_attr( $title ) . '">' .
				esc_html( $title ) .
			'</a>';

		// --<
		return $link;

	}

endif;



if ( ! function_exists( 'commentpress_page_navigation_get_previous_link' ) ) :

	/**
	 * Gets the markup for the "Previous Page" Navigation link.
	 *
	 * @since 4.0
	 *
	 * @param bool $with_comments True returns the Previous Page with Comments.
	 * @return str $link The markup for the "Previous Page" Navigation link.
	 */
	function commentpress_page_navigation_get_previous_link( $with_comments = false ) {

		// Init.
		$link = '';

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return $link;
		}

		// Get Previous Page.
		$previous_page = $core->nav->page_previous_get( $with_comments );
		if ( ! is_object( $previous_page ) ) {
			return $link;
		}

		// Override title if asking for Pages with Comments.
		$title = __( 'Previous page', 'commentpress-core' );
		if ( $with_comments ) {
			$title = __( 'Previous page with comments', 'commentpress-core' );
		}

		// Set an image if asking for Pages with Comments.
		$img = ( $with_comments === true ) ? '<img src="' . get_template_directory_uri() . '/assets/images/prev.png" />' : '';

		/**
		 * Filters the "Previous Page" Navigation link CSS ID.
		 *
		 * @since 4.0
		 *
		 * @param str The default "Previous Page" Navigation link CSS ID.
		 */
		$css_id = apply_filters( 'commentpress/navigation/page/link/previous/css_id', 'previous_page' );

		// Build ID attribute.
		$id = ! empty( $css_id ) ? ' id="' . $css_id . '"' : '';

		/**
		 * Filters the "Previous Page" Navigation link CSS classes.
		 *
		 * @since 4.0
		 *
		 * @param array The default "Previous Page" Navigation link CSS classes.
		 */
		$css_classes = apply_filters( 'commentpress/navigation/page/link/previous/css_classes', [ 'css_btn' ] );

		// Build class attribute.
		$class = ! empty( $css_classes ) ? ' class="' . implode( ' ', $css_classes ) . '"' : '';

		// Define list item.
		$link = $img .
			'<a href="' . get_permalink( $previous_page->ID ) . '"' . $id . $class . ' title="' . esc_attr( $title ) . '">' .
				esc_html( $title ) .
			'</a>';

		// --<
		return $link;

	}

endif;



if ( ! function_exists( 'commentpress_navigation_network_home_title' ) ) :

	/**
	 * Gets the "Network Home" title.
	 *
	 * @since 4.0
	 *
	 * @return str $title The default "Network Home" title.
	 */
	function commentpress_navigation_network_home_title() {

		/**
		 * Filters the Network Home title.
		 *
		 * @since 3.4
		 *
		 * @param str The default Network Home title.
		 */
		$title = apply_filters( 'cp_nav_network_home_title', __( 'Site Home Page', 'commentpress-core' ) );

		// --<
		return $title;

	}

endif;



if ( ! function_exists( 'commentpress_navigation_group_home_title' ) ) :

	/**
	 * Gets the "Group Home" title.
	 *
	 * @since 4.0
	 *
	 * @return str $title The default "Group Home" title.
	 */
	function commentpress_navigation_group_home_title() {

		/**
		 * Filters the Group Home Page title.
		 *
		 * @since 3.4
		 *
		 * @param str The default Group Home Page title.
		 */
		$title = apply_filters( 'cp_nav_group_home_title', __( 'Group Home Page', 'commentpress-core' ) );

		// --<
		return $title;

	}

endif;



if ( ! function_exists( 'commentpress_navigation_blog_home_title' ) ) :

	/**
	 * Gets the "Blog Home" title.
	 *
	 * @since 4.0
	 *
	 * @return str $title The default "Blog Home" title.
	 */
	function commentpress_navigation_blog_home_title() {

		/**
		 * Filters the Home Page title.
		 *
		 * Used if Blog home is not CommentPress Core Welcome Page.
		 *
		 * @since 3.4
		 *
		 * @param str The default Home Page title.
		 */
		$title = apply_filters( 'cp_nav_blog_home_title', __( 'Home Page', 'commentpress-core' ) );

		// --<
		return $title;

	}

endif;



if ( ! function_exists( 'commentpress_navigation_title_page_title' ) ) :

	/**
	 * Gets the "Welcome Page" title.
	 *
	 * @since 4.0
	 *
	 * @return str $title The default "Welcome Page" title.
	 */
	function commentpress_navigation_title_page_title() {

		/**
		 * Filters the Welcome Page title.
		 *
		 * @since 3.4
		 *
		 * @param str The default Welcome Page title.
		 */
		$title = apply_filters( 'cp_nav_title_page_title', __( 'Title Page', 'commentpress-core' ) );

		// --<
		return $title;

	}

endif;



if ( ! function_exists( 'commentpress_navigation_new_site_title' ) ) :

	/**
	 * Gets the "New Site" title.
	 *
	 * @since 4.0
	 *
	 * @return str $title The default "New Site" title.
	 */
	function commentpress_navigation_new_site_title() {

		/**
		 * Filters the New Site title.
		 *
		 * This is used for multisite signup and Blog create.
		 *
		 * If BuddyPress Site Tracking is active, BuddyPress uses its
		 * own Signup Page, so Blog create is not directly allowed -
		 * it is done through Signup Page.
		 *
		 * @since 3.4
		 *
		 * @param str The default New Site title.
		 */
		$title = apply_filters( 'cp_user_links_new_site_title', __( 'Create a new document', 'commentpress-core' ) );

		// --<
		return $title;

	}

endif;



if ( ! function_exists( 'commentpress_navigation_dashboard_title' ) ) :

	/**
	 * Gets the "Dashboard" title.
	 *
	 * @since 4.0
	 *
	 * @return str $title The default "Dashboard" title.
	 */
	function commentpress_navigation_dashboard_title() {

		/**
		 * Filters the Dashboard title.
		 *
		 * @since 3.4
		 *
		 * @param str The default Dashboard title.
		 */
		$title = apply_filters( 'cp_user_links_dashboard_title', __( 'Dashboard', 'commentpress-core' ) );

		// --<
		return $title;

	}

endif;



if ( ! function_exists( 'commentpress_get_page_number' ) ) :

	/**
	 * Gets the "Page Number" for a given Post ID.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the WordPress Post.
	 * @return int|bool $number The "Page Number" of the Post, or false on failure.
	 */
	function commentpress_get_page_number( $post_id ) {

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return false;
		}

		// Try and get the "Page Number".
		$number = $core->nav->page_number_get( $post_id );
		if ( empty( $number ) ) {
			return false;
		}

		// --<
		return $number;

	}

endif;



if ( ! function_exists( 'commentpress_page_number' ) ) :

	/**
	 * Echoes the "Page Number" for a given Post ID.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the WordPress Post.
	 */
	function commentpress_page_number( $post_id ) {

		// Try and get the "Page Number".
		$number = commentpress_get_page_number( $post_id );
		if ( empty( $number ) ) {
			return false;
		}

		// Make lowercase if Roman numeral.
		if ( ! is_numeric( $number ) ) {
			$number = strtolower( $number );
		}

		// Wrap number in identifying span.
		$element = '<span class="page_num_bottom">' . $number . '</span>';

		/**
		 * Filter that allows Page number string to be disabled.
		 *
		 * @since 4.0
		 *
		 * @param bool False by default. Return true to skip building string.
		 */
		// Allow this to be disabled.
		if ( ! apply_filters( 'commentpress_hide_page_number_string', false ) ) {

			// Build Page number string.
			$page_number = sprintf(
				/* translators: %s: The span element containing the Page number. */
				__( 'Page %s', 'commentpress-core' ),
				$element
			);

		} else {

			// Skip building string.
			$page_number = $element;

		}

		// Wrap in identifying class.
		echo '<span class="' . ( ! is_numeric( $number ) ? 'roman' : 'arabic' ) . '">' . $page_number . '</span>';

	}

endif;



if ( ! function_exists( 'commentpress_multipager' ) ) :

	/**
	 * Create sane links between Pages.
	 *
	 * Comment permalinks are filtered if the Comment is not on the first Page
	 * in a multipage Post.
	 *
	 * @see commentpress_multipage_comment_link()
	 *
	 * @since 3.5
	 *
	 * @return str $page_links The Next Page and Previous Page links.
	 */
	function commentpress_multipager() {

		// Set default behaviour.
		$defaults = [
			'before' => '<div class="multipager">',
			'after' => '</div>',
			'link_before' => '',
			'link_after' => '',
			'next_or_number' => 'next',
			'nextpagelink' => '<span class="alignright">' . __( 'Next page', 'commentpress-core' ) . ' &raquo;</span>',
			'previouspagelink' => '<span class="alignleft">&laquo; ' . __( 'Previous page', 'commentpress-core' ) . '</span>',
			'pagelink' => '%',
			'more_file' => '',
			'echo' => 0,
		];

		// Get Page links.
		$page_links = wp_link_pages( $defaults );

		// Add separator when there are two links.
		$page_links = str_replace(
			'a><a',
			'a> <span class="multipager_sep">|</span> <a',
			$page_links
		);

		// Get Page links.
		$page_links .= wp_link_pages( [
			'before' => '<div class="multipager multipager_all"><span>' . __( 'Pages: ', 'commentpress-core' ) . '</span>',
			'after' => '</div>',
			'pagelink' => '<span class="multipager_link">%</span>',
			'echo' => 0,
		] );

		// --<
		return $page_links;

	}

endif;
