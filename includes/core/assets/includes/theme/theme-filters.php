<?php
/**
 * CommentPress Core Theme Filters.
 *
 * Filters that alter functionality in CommentPress themes live here.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;



if ( ! function_exists( 'commentpress_prefix_signup_template' ) ) :

	/**
	 * Prefixes WordPress Signup Page with the div wrappers that CommentPress Core needs.
	 *
	 * @since 3.8.5
	 */
	function commentpress_prefix_signup_template() {

		// Prefixed wrappers.
		echo '<div id="wrapper">
			  <div id="main_wrapper" class="clearfix">
			  <div id="page_wrapper">
			  <div id="content">';

	}

endif;

// Add callback for the above.
add_action( 'before_signup_form', 'commentpress_prefix_signup_template' );



if ( ! function_exists( 'commentpress_suffix_signup_template' ) ) :

	/**
	 * Suffixes WordPress Signup Page with the div wrappers that CommentPress Core needs.
	 *
	 * @since 3.8.5
	 */
	function commentpress_suffix_signup_template() {

		// Prefixed wrappers.
		echo '</div><!-- /content -->
			  </div><!-- /page_wrapper -->
			  </div><!-- /main_wrapper -->
			  </div><!-- /wrapper -->';

	}

endif;

// Add callback for the above.
add_action( 'after_signup_form', 'commentpress_suffix_signup_template' );



if ( ! function_exists( 'commentpress_document_title_parts' ) ) :

	/**
	 * Add the root network name when the sub-blog is a Group Blog.
	 *
	 * @since 3.8
	 *
	 * @param array $parts The existing title parts.
	 * @return array $parts The modified title parts.
	 */
	function commentpress_document_title_parts( $parts ) {

		// Get core plugin reference.
		$core = commentpress_core();

		// If it's a Group Blog.
		if ( ! empty( $core ) && $core->bp->is_groupblog() ) {
			if ( ! isset( $parts['site'] ) ) {
				$parts['title'] .= commentpress_site_title( '|', false );
				unset( $parts['tagline'] );
			} else {
				$parts['site'] .= commentpress_site_title( '|', false );
			}
		}

		/**
		 * Filters array of title parts.
		 *
		 * @since 3.8.3
		 *
		 * @param array $parts The array of title parts.
		 */
		return apply_filters( 'commentpress_document_title_parts', $parts );

	}

endif;

// Add callback for the above.
add_filter( 'document_title_parts', 'commentpress_document_title_parts' );



if ( ! function_exists( 'commentpress_document_title_separator' ) ) :

	/**
	 * Use the separator that CommentPress Core has always used.
	 *
	 * @since 3.8
	 *
	 * @param string $sep The existing separator.
	 * @return string $sep The modified separator.
	 */
	function commentpress_document_title_separator( $sep ) {

		// --<
		return '|';

	}

endif;

// Add callback for the above.
add_filter( 'document_title_separator', 'commentpress_document_title_separator' );



if ( ! function_exists( 'commentpress_site_title' ) ) :

	/**
	 * Amend the Site title depending on context of Blog.
	 *
	 * @since 3.8
	 *
	 * @param string $sep The title separator.
	 * @param bool   $echo Echo the result or not.
	 * @return string $site_name The title of the Site.
	 */
	function commentpress_site_title( $sep = '', $echo = true ) {

		// Is this multisite?
		if ( is_multisite() ) {

			// If we're on a sub-blog.
			if ( ! is_main_site() ) {

				$current_site = get_current_site();

				// Print?
				if ( $echo ) {

					// Add Site name.
					echo esc_html( ' ' . trim( $sep ) . ' ' . $current_site->site_name );

				} else {

					// Add Site name.
					return ' ' . trim( $sep ) . ' ' . $current_site->site_name;

				}

			}

		}

	}

endif;



if ( ! function_exists( 'commentpress_post_classes' ) ) :

	/**
	 * Filters the Post classes.
	 *
	 * @since 4.0
	 *
	 * @param array $classes The array of classes assigned to the Post.
	 * @param array $class The additional classes assigned to the Post.
	 * @param int   $post_id The numeric ID of the Post.
	 * @return array $classes The modified array of classes assigned to the Post.
	 */
	function commentpress_post_classes( $classes, $class, $post_id ) {

		// Always add "post" to Post classes.
		$classes[] = 'post';

		// Always add "clearfix" to Post classes.
		$classes[] = 'clearfix';

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return $classes;
		}

		// Check if the Formatter for this Post is overridden.
		$overridden = $core->entry->formatter->is_overridden( $post_id );
		if ( false === $overridden ) {
			return $classes;
		}

		// Get the Formatter for this Post.
		$formatter = $core->entry->formatter->get_for_post_id( $post_id );

		// Build class name and add to classes.
		$classes[] = 'overridden_type-' . $formatter;

		// --<
		return $classes;

	}

endif;

// Add callback for the above.
add_filter( 'post_class', 'commentpress_post_classes', 20, 3 );



if ( ! function_exists( 'commentpress_remove_more_jump_link' ) ) :

	/**
	 * Disable more link jump.
	 *
	 * @see https://codex.wordpress.org/Customizing_the_Read_More
	 *
	 * @since 3.0
	 *
	 * @param string $link The existing more link.
	 * @return string $link The modified more link.
	 */
	function commentpress_remove_more_jump_link( $link ) {

		$offset = strpos( $link, '#more-' );

		if ( $offset ) {
			$end = strpos( $link, '"', $offset );
			if ( $end ) {
				$link = substr_replace( $link, '', $offset, $end - $offset );
			}
		}

		// --<
		return $link;

	}

endif;

// Add callback for the above.
add_filter( 'the_content_more_link', 'commentpress_remove_more_jump_link' );



if ( ! function_exists( 'commentpress_lexia_support_mime' ) ) :

	/**
	 * The "media" Post Type needs more granular naming support.
	 *
	 * @since 3.9
	 *
	 * @param string $post_type_name The existing singular name of the Post Type.
	 * @param string $post_type The Post Type identifier.
	 * @return string $post_type_name The modified singular name of the Post Type.
	 */
	function commentpress_lexia_support_mime( $post_type_name, $post_type ) {

		// Only handle media.
		if ( 'attachment' !== $post_type ) {
			return $post_type_name;
		}

		// Get mime type.
		$mime_type = get_post_mime_type( get_the_ID() );

		// Use different name for each.
		switch ( $mime_type ) {

			// Image.
			case 'image/jpeg':
			case 'image/png':
			case 'image/gif':
				$mime_type_name = __( 'Image', 'commentpress-core' );
				break;

			// Video.
			case 'video/mpeg':
			case 'video/mp4':
			case 'video/quicktime':
				$mime_type_name = __( 'Video', 'commentpress-core' );
				break;

			// File.
			case 'text/csv':
			case 'text/plain':
			case 'text/xml':
			default:
				$mime_type_name = __( 'File', 'commentpress-core' );
				break;

		}

		/**
		 * Allow this name to be filtered.
		 *
		 * @since 3.9
		 *
		 * @param string $mime_type_name The name for this mime type.
		 * @param string $mime_type The mime type.
		 */
		$mime_type_name = apply_filters( 'commentpress_lexia_mime_type_name', $mime_type_name, $mime_type );

		// --<
		return $mime_type_name;

	}

endif;

// Add callback for the above.
add_filter( 'commentpress_lexia_post_type_name', 'commentpress_lexia_support_mime', 10, 2 );



if ( ! function_exists( 'commentpress_lexia_modify_entity_text' ) ) :

	/**
	 * The "media" Post Type needs more granular naming support.
	 *
	 * @since 3.9
	 *
	 * @param string $entity_text The current entity text.
	 * @param string $post_type_name The singular name of the Post Type.
	 * @param string $post_type The Post Type identifier.
	 * @return string $entity_text The modified entity text.
	 */
	function commentpress_lexia_modify_entity_text( $entity_text, $post_type_name, $post_type ) {

		// Only handle media.
		if ( 'attachment' !== $post_type ) {
			return $entity_text;
		}

		// Override entity text.
		$entity_text = sprintf(
			/* translators: %s: Name of the Post Type. */
			__( 'the %s', 'commentpress-core' ),
			$post_type_name
		);

		// --<
		return $entity_text;

	}

endif;

// Add callback for the above.
add_filter( 'commentpress_lexia_whole_entity_text', 'commentpress_lexia_modify_entity_text', 10, 3 );



if ( ! function_exists( 'commentpress_excerpt_length' ) ) :

	/**
	 * Gets the length of excerpt.
	 *
	 * @since 3.0
	 *
	 * @return int $length The length of the excerpt.
	 */
	function commentpress_excerpt_length() {

		// Init return with WordPress default value.
		$length = 55;

		// Get core plugin reference.
		$core = commentpress_core();

		// Get length of excerpt from option.
		if ( ! empty( $core ) ) {
			$setting = $core->theme->setting_excerpt_length_get();
			if ( ! empty( $setting ) ) {
				$length = (int) $setting;
			}
		}

		// --<
		return $length;

	}

endif;

// Add callback for excerpt length.
add_filter( 'excerpt_length', 'commentpress_excerpt_length' );



if ( ! function_exists( 'commentpress_add_link_css' ) ) :

	/**
	 * Utility to add button css class to Blog nav links.
	 *
	 * @since 3.0
	 *
	 * @param string $link The existing link.
	 * @return string $link The link with the custom class attribute added.
	 */
	function commentpress_add_link_css( $link ) {

		// Add CSS.
		$link = str_replace( '<a ', '<a class="css_btn" ', $link );

		// --<
		return $link;

	}

endif;

// Add callback for next/previous links.
add_filter( 'previous_post_link', 'commentpress_add_link_css' );
add_filter( 'next_post_link', 'commentpress_add_link_css' );



if ( ! function_exists( 'commentpress_get_link_css' ) ) :

	/**
	 * Utility to add button css class to Blog nav links.
	 *
	 * @since 3.0
	 *
	 * @return string $link The custom class attribute.
	 */
	function commentpress_get_link_css() {

		// Add CSS.
		$link = 'class="css_btn"';

		// --<
		return $link;

	}

endif;

// Add callback for next/previous Posts links.
add_filter( 'previous_posts_link_attributes', 'commentpress_get_link_css' );
add_filter( 'next_posts_link_attributes', 'commentpress_get_link_css' );



if ( ! function_exists( 'commentpress_trap_empty_search' ) ) :

	/**
	 * Trap empty search queries and use Search template.
	 *
	 * @since 3.3
	 *
	 * @return string $template The path to the search template.
	 */
	function commentpress_trap_empty_search() {

		// Use Search template when there is an empty search.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET['s'] ) && empty( $_GET['s'] ) ) {
			return locate_template( [ 'search.php' ] );
		}

	}

endif;

// Add callback for the above.
add_filter( 'home_template', 'commentpress_trap_empty_search' );



if ( ! function_exists( 'commentpress_amend_password_form' ) ) :

	/**
	 * Adds some style to the password form by adding a class to it.
	 *
	 * @since 3.3
	 *
	 * @param string $output The existing password form.
	 * @return string $output The modified password form.
	 */
	function commentpress_amend_password_form( $output ) {

		// Add css class to form.
		$output = str_replace( '<form ', '<form class="post_password_form" ', $output );

		// Add css class to text field.
		$output = str_replace( '<input name="post_password" ', '<input class="post_password_field" name="post_password" ', $output );

		// Add css class to submit button.
		$output = str_replace( '<input type="submit" ', '<input class="post_password_button" type="submit" ', $output );

		// --<
		return $output;

	}

endif;

// Add callback for the above.
add_filter( 'the_password_form', 'commentpress_amend_password_form' );



if ( ! function_exists( 'commentpress_show_source_url' ) ) :

	/**
	 * Show source URL for print.
	 *
	 * @since 3.5
	 */
	function commentpress_show_source_url() {

		// Path from server array, if set.
		$path = isset( $_SERVER['REQUEST_URI'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		// Get server, if set.
		$server = isset( $_SERVER['SERVER_NAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ) : '';

		// Get protocol, if set.
		$protocol = ! empty( $_SERVER['HTTPS'] ) ? 'https' : 'http';

		// Construct URL.
		$url = $protocol . '://' . $server . $path;

		// Construct source text.
		$source = sprintf(
			/* translators: %s: The source URL of the Page being printed. */
			__( 'Source: %s', 'commentpress-core' ),
			$url
		);

		// Add the URL - hidden, but revealed by print stylesheet.
		echo '<p class="hidden_page_url">' . esc_html( $source ) . '</p>';

	}

endif;

// Add callback for the above.
add_action( 'wp_footer', 'commentpress_show_source_url' );
