<?php
/**
 * CommentPress Core Display class.
 *
 * Handles display functionality in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Display Class.
 *
 * A class that is intended to encapsulate display handling.
 *
 * @since 3.0
 */
class CommentPress_Core_Display {

	/**
	 * Core loader object.
	 *
	 * @since 3.0
	 * @since 4.0 Renamed.
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 *
	 * @param object $core Reference to the core plugin object.
	 */
	public function __construct( $core ) {

		// Store reference to core plugin object.
		$this->core = $core;

		// Init when this plugin is fully loaded.
		add_action( 'commentpress/core/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this object.
	 *
	 * @since 4.0
	 */
	public function initialise() {

		// Only do this once.
		static $done;
		if ( isset( $done ) && $done === true ) {
			return;
		}

		// Register hooks.
		$this->register_hooks();

		/**
		 * Fires when this class has loaded.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/display/loaded' );

		// We're done.
		$done = true;

	}

	/**
	 * Register hooks.
	 *
	 * @since 3.9.14
	 */
	public function register_hooks() {

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the help text.
	 *
	 * @since 3.4
	 *
	 * @return str $help The help text formatted as HTML.
	 */
	public function get_help() {

		$help = <<<HELPTEXT
<p>For further information about using CommentPress, please refer to the <a href="http://www.futureofthebook.org/commentpress/support/">CommentPress support pages</a> or use one of the links below:</p>

<ul>
<li><a href="http://www.futureofthebook.org/commentpress/support/structuring-your-document/">Structuring your Document</a></li>
<li><a href="http://www.futureofthebook.org/commentpress/support/formatting-your-document/">Formatting Your Document</a></li>
<li><a href="http://www.futureofthebook.org/commentpress/support/using-commentpress/">How to read a CommentPress document</a></li>
</ul>
HELPTEXT;

		// --<
		return $help;

	}

	// -------------------------------------------------------------------------

	/**
	 * Get "Table of Contents" list.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @param array $exclude_pages The array of Pages to exclude.
	 */
	public function get_toc_list( $exclude_pages = [] ) {

		// Switch Pages or Posts.
		if ( 'post' === $this->core->nav->setting_post_type_get() ) {
			$this->list_posts();
		} else {
			$this->list_pages( $exclude_pages );
		}

	}

	/**
	 * Show the Posts and their Comment count in a list format.
	 *
	 * @since 3.4
	 *
	 * @param str $params The parameters to list Posts by.
	 */
	public function list_posts( $params = 'numberposts=-1&order=DESC' ) {

		// Get all Posts.
		$posts = get_posts( $params );

		// Have we set the option?
		$list_style = $this->core->nav->setting_subpages_get();

		// If not set or set to 'off'.
		if ( $list_style === false || $list_style == '0' ) {

			// -----------------------------------------------------------------
			// Old-style undecorated list.
			// -----------------------------------------------------------------
			// Run through them.
			foreach ( $posts as $item ) {

				// Get Comment count for that Post.
				$count = count( get_approved_comments( $item->ID ) );

				// Write list item.
				echo '<li class="title">' .
					'<a href="' . get_permalink( $item->ID ) . '">' . get_the_title( $item->ID ) . ' (' . $count . ')</a>' .
				'</li>' . "\n";

			}

			// Bail early.
			return;

		}

		// -----------------------------------------------------------------
		// New-style decorated list.
		// -----------------------------------------------------------------

		// Access current Post.
		global $post;

		// Run through them.
		foreach ( $posts as $item ) {

			// Init output.
			$html = '';

			// Compat with Co-Authors Plus.
			if ( function_exists( 'get_coauthors' ) ) {

				// Add permalink.
				$html .= $this->list_posts_coauthors( $item );

			} else {

				// Get avatar.
				$author_id = $item->post_author;

				// Are we showing avatars?
				if ( get_option( 'show_avatars' ) ) {
					$html .= get_avatar( $author_id, $size = '32' );
				}

				// Add citation.
				$html .= '<cite class="fn">' . $this->echo_post_author( $author_id, false ) . '</cite>';

				// Add permalink.
				$html .= '<p class="post_activity_date">' . esc_html( get_the_time( get_option( 'date_format' ), $item->ID ) ) . '</p>';

			}

			// Init current Post class as empty.
			$current_post = '';

			// If we're on the current Post and it's this item.
			if ( is_singular() && ( $post instanceof WP_Post ) && $post->ID == $item->ID ) {
				$current_post = ' current_page_item';
			}

			// Get Comment count for this item.
			$count = count( get_approved_comments( $item->ID ) );

			// Write list item.
			echo '<li class="title' . $current_post . '">
				<div class="post-identifier">
					' . $html . '
				</div>
				<a href="' . get_permalink( $item->ID ) . '" class="post_activity_link">' .
					get_the_title( $item->ID ) . ' (' . $count . ')' .
				'</a>
			</li>' . "\n";

		}

	}

	/**
	 * Build Authors when Co-Authors Plus is present.
	 *
	 * @since 4.0
	 *
	 * @param WP_Post $item The WordPress Post object.
	 */
	private function list_posts_coauthors( $item ) {

		// Init return.
		$html = '';

		// Get multiple authors.
		$authors = get_coauthors( $item->ID );

		// Bail if we don't get any.
		if ( empty( $authors ) ) {
			return $html;
		}

		// Use the Co-Authors format of "name, name, name & name".
		$author_html = '';

		// Init counter.
		$n = 1;

		// Find out how many author we have.
		$author_count = count( $authors );

		// Loop.
		foreach ( $authors as $author ) {

			// Default to comma.
			$sep = ', ';

			// Use ampersand if we're on the penultimate.
			if ( $n == ( $author_count - 1 ) ) {
				$sep = __( ' &amp; ', 'commentpress-core' );
			}

			// If we're on the last, don't add.
			if ( $n == $author_count ) {
				$sep = '';
			}

			// Get name.
			$author_html .= $this->echo_post_author( $author->ID, false );

			// Add separator.
			$author_html .= $sep;

			// Increment.
			$n++;

			// Maybe get avatar.
			if ( get_option( 'show_avatars' ) ) {
				$html .= get_avatar( $author->ID, $size = '32' );
			}

		}

		// Add citation.
		$html .= '<cite class="fn">' . $author_html . '</cite>' . "\n";

		// Add permalink.
		$html .= '<p class="post_activity_date">' .
			esc_html( get_the_time( get_option( 'date_format' ), $item->ID ) ) .
		'</p>' . "\n";

		// --<
		return $html;

	}

	/**
	 * Show username (with link).
	 *
	 * @since 3.4
	 *
	 * @param int  $author_id The numeric ID of the author.
	 * @param bool $echo True if link is to be echoed, false if returned.
	 */
	private function echo_post_author( $author_id, $echo = true ) {

		// Get author details.
		$user = get_userdata( $author_id );

		// Kick out if we don't have a User with that ID.
		if ( ! is_object( $user ) ) {
			return;
		}

		// Access plugin.
		global $post;

		// If we have a Post and it's BuddyPress.
		if ( is_object( $post ) && $this->core->bp->is_buddypress() ) {

			// Construct User link.
			$author = bp_core_get_userlink( $user->ID );

		} else {

			// Link to theme's Author Page.
			$link   = sprintf(
				'<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
				get_author_posts_url( $user->ID, $user->user_nicename ),
				esc_attr( sprintf( __( 'Posts by %s', 'commentpress-core' ), $user->display_name ) ),
				esc_html( $user->display_name )
			);
			$author = apply_filters( 'the_author_posts_link', $link );

		}

		// If we're echoing.
		if ( $echo ) {
			echo $author;
		} else {
			return $author;
		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Print the Pages and their Comment count in a list format.
	 *
	 * @since 3.4
	 *
	 * @param array $exclude_pages The array of Pages to exclude.
	 */
	public function list_pages( $exclude_pages = [] ) {

		// Bail if there is a custom menu.
		if ( has_nav_menu( 'toc' ) ) {

			// Display menu.
			wp_nav_menu( [
				'theme_location' => 'toc',
				'echo'           => true,
				'container'      => '',
				'items_wrap'     => '%3$s',
			] );

			// --<
			return;

		}

		// Get Welcome Page ID.
		$welcome_id = $this->core->db->setting_get( 'cp_welcome_page' );

		// Get Front Page.
		$page_on_front = $this->core->db->option_wp_get( 'page_on_front' );

		// Print link to Welcome Page, if we have one and it's the Front Page.
		if ( $welcome_id !== false && $page_on_front == $welcome_id ) {

			// Define Welcome Page.
			$title_page_title = get_the_title( $welcome_id );

			/**
			 * Filters the Welcome Page title.
			 *
			 * @since 3.4
			 *
			 * @param string $title_page_title The default Welcome Page title.
			 */
			$title_page_title = apply_filters( 'cp_title_page_title', $title_page_title );

			// Set current item class if viewing Front Page.
			$is_active = '';
			if ( is_front_page() ) {
				$is_active = ' current_page_item';
			}

			// Echo list item.
			echo '<li class="page_item page-item-' . $welcome_id . $is_active . '">' .
				'<a href="' . get_permalink( $welcome_id ) . '">' . $title_page_title . '</a>' .
			'</li>';

		}

		/*
		// Get Page display option.
		$depth = $this->core->nav->setting_subpages_get();
		*/

		// ALWAYS write Sub-pages into Page, even if they aren't displayed.
		$depth = 0;

		// Get Pages to exclude.
		$exclude = $this->core->db->setting_get( 'cp_special_pages' );

		// Do we have any?
		if ( ! $exclude ) {
			$exclude = [];
		}

		// Exclude Welcome Page, if we have one.
		if ( $welcome_id !== false ) {
			$exclude[] = $welcome_id;
		}

		// Did we get any passed to us?
		if ( ! empty( $exclude_pages ) ) {

			// Merge arrays.
			$exclude = array_merge( $exclude, $exclude_pages );

		}

		// Set list Pages defaults.
		$defaults = [
			'depth'        => $depth,
			'show_date'    => '',
			'date_format'  => $this->core->db->setting_get( 'date_format' ),
			'child_of'     => 0,
			'exclude'      => implode( ',', $exclude ),
			'title_li'     => '',
			'echo'         => 1,
			'authors'      => '',
			'sort_column'  => 'menu_order, post_title',
			'link_before'  => '',
			'link_after'   => '',
			'exclude_tree' => '',
		];

		// Use WordPress function to echo.
		wp_list_pages( $defaults );

	}

	/**
	 * Get the Block Comment icon.
	 *
	 * @since 3.4
	 *
	 * @param int $comment_count The number of Comments.
	 * @param str $text_signature The Comment Text Signature.
	 * @param str $block_type Either 'auto', 'line' or 'block'.
	 * @param int $para_num Sequential commentable block number.
	 * @return str $comment_icon The Comment icon formatted as HTML.
	 */
	public function get_comment_icon( $comment_count, $text_signature, $block_type = 'auto', $para_num = 1 ) {

		// Reset icon.
		$icon = null;

		// If we have no Comments.
		if ( $comment_count == 0 ) {

			// Show add Comment icon.
			$icon  = 'comment-add.png';
			$class = ' no_comments';

		} elseif ( $comment_count > 0 ) {

			// Show Comments Present icon.
			$icon  = 'comment.png';
			$class = ' has_comments';

		}

		// Define Block title by Block type.
		switch ( $block_type ) {

			// -----------------------------------------------------------------
			// Auto-formatted.
			// -----------------------------------------------------------------
			case 'auto':
			default:
				// Define title text.
				$title_text = sprintf(
					_n( 'There is %1$d comment written for this %2$s', 'There are %1$d comments written for this %2$s', $comment_count, 'commentpress-core' ),
					$comment_count,
					$this->core->parser->lexia_get()
				);

				// Define add Comment text.
				$add_text = sprintf(
					__( 'Leave a comment on %1$s %2$d', 'commentpress-core' ),
					$this->core->parser->lexia_get(),
					$para_num
				);

				break;

			// -----------------------------------------------------------------
			// Line-by-line, eg poetry.
			// -----------------------------------------------------------------
			case 'line':

				// Define title text.
				$title_text = sprintf(
					_n( 'There is %1$d comment written for this %2$s', 'There are %1$d comments written for this %2$s', $comment_count, 'commentpress-core' ),
					$comment_count,
					$this->core->parser->lexia_get()
				);

				// Define add Comment text.
				$add_text = sprintf(
					__( 'Leave a comment on %1$s %2$d', 'commentpress-core' ),
					$this->core->parser->lexia_get(),
					$para_num
				);

				break;

			// -----------------------------------------------------------------
			// Comment-blocks.
			// -----------------------------------------------------------------
			case 'block':

				// Define title text.
				$title_text = sprintf(
					_n( 'There is %1$d comment written for this %2$s', 'There are %1$d comments written for this %2$s', $comment_count, 'commentpress-core' ),
					$comment_count,
					$this->core->parser->lexia_get()
				);

				// Define add Comment text.
				$add_text = sprintf(
					__( 'Leave a comment on %1$s %2$d', 'commentpress-core' ),
					$this->core->parser->lexia_get(),
					$para_num
				);

				break;

		}

		// Define small.
		$small = '<small class="comment_count" title="' . $title_text . '">' . (string) $comment_count . '</small>';

		// Define HTML for Comment icon.
		$comment_icon = '<span class="commenticonbox">' .
			'<a class="para_permalink' . $class . '" href="#' . $text_signature . '" title="' . $add_text . '">' .
				$add_text .
			'</a> ' .
			$small .
		'</span>' . "\n";

		// --<
		return $comment_icon;

	}

	/**
	 * Get the Block Paragraph icon.
	 *
	 * @since 3.4
	 *
	 * @param int $comment_count The number of Comments.
	 * @param str $text_signature The Comment Text Signature.
	 * @param str $block_type Either 'auto', 'line' or 'block'.
	 * @param int $para_num The sequential commentable Block number.
	 * @return str $paragraph_icon The Paragraph icon formatted as HTML.
	 */
	public function get_paragraph_icon( $comment_count, $text_signature, $block_type = 'auto', $para_num = 1 ) {

		// Define Block title by Block type.
		switch ( $block_type ) {

			// -----------------------------------------------------------------
			// Auto-formatted.
			// -----------------------------------------------------------------
			case 'auto':
			default:
				// Define permalink text.
				$permalink_text = sprintf(
					__( 'Permalink for %1$s %2$d', 'commentpress-core' ),
					$this->core->parser->lexia_get(),
					$para_num
				);

				// Define Paragraph marker.
				$para_marker = '<span class="para_marker">' .
					'<a class="textblock_permalink" id="' . $text_signature . '" href="#' . $text_signature . '" title="' . $permalink_text . '">' .
						'&para; <span>' . (string) $para_num . '</span>' .
					'</a>' .
				'</span>';

				break;

			// -----------------------------------------------------------------
			// Line-by-line, eg poetry.
			// -----------------------------------------------------------------
			case 'line':

				// Define permalink text.
				$permalink_text = sprintf(
					__( 'Permalink for %1$s %2$d', 'commentpress-core' ),
					$this->core->parser->lexia_get(),
					$para_num
				);

				// Define Paragraph marker.
				$para_marker = '<span class="para_marker">' .
					'<a class="textblock_permalink" id="' . $text_signature . '" href="#' . $text_signature . '" title="' . $permalink_text . '">' .
						'&para; <span>' . (string) $para_num . '</span>' .
					'</a>' .
				'</span>';

				break;

			// -----------------------------------------------------------------
			// Comment-blocks.
			// -----------------------------------------------------------------
			case 'block':

				// Define permalink text.
				$permalink_text = sprintf(
					__( 'Permalink for %1$s %2$d', 'commentpress-core' ),
					$this->core->parser->lexia_get(),
					$para_num
				);

				// Define Paragraph marker.
				$para_marker = '<span class="para_marker">' .
					'<a class="textblock_permalink" id="' . $text_signature . '" href="#' . $text_signature . '" title="' . $permalink_text . '">' .
						'&para; <span>' . (string) $para_num . '</span>' .
					'</a>' .
				'</span>';

				break;

		}

		// Define HTML for Paragraph icon.
		$paragraph_icon = $para_marker . "\n";

		// --<
		return $paragraph_icon;

	}

	/**
	 * Get the content Comment icon tag.
	 *
	 * @since 3.4
	 *
	 * @param str $text_signature The Comment Text Signature.
	 * @param str $commenticon The Comment icon.
	 * @param str $tag The tag.
	 * @param str $start The ordered list start value.
	 * @return str $para_tag The tag formatted as HTML.
	 */
	public function get_para_tag( $text_signature, $commenticon, $tag = 'p', $start = 0 ) {

		// Return different stuff for different tags.
		switch ( $tag ) {

			case 'ul':

				// Define list tag.
				$para_tag = '<' . $tag . ' class="textblock" id="textblock-' . $text_signature . '">' .
					'<li class="list_commenticon">' . $commenticon . '</li>';
				break;

			case 'ol':

				// Define list tag.
				$para_tag = '<' . $tag . ' class="textblock" id="textblock-' . $text_signature . '" start="0">' .
					'<li class="list_commenticon">' . $commenticon . '</li>';
				break;

			// Compat with "WP Footnotes".
			case 'ol class="footnotes"':

				// Define list tag.
				$para_tag = '<ol class="footnotes textblock" id="textblock-' . $text_signature . '" start="0">' .
					'<li class="list_commenticon">' . $commenticon . '</li>';
				break;

			// Compat with "WP Footnotes".
			case ( substr( $tag, 0, 10 ) == 'ol start="' ):

				// Define list tag.
				$para_tag = '<ol class="textblock" id="textblock-' . $text_signature . '" start="' . ( $start - 1 ) . '">' .
					'<li class="list_commenticon">' . $commenticon . '</li>';
				break;

			case 'p':
			case 'p style="text-align:left"':
			case 'p style="text-align:left;"':
			case 'p style="text-align: left"':
			case 'p style="text-align: left;"':

				// Define para tag.
				$para_tag = '<' . $tag . ' class="textblock" id="textblock-' . $text_signature . '">' . $commenticon;
				break;

			case 'p style="text-align:right"':
			case 'p style="text-align:right;"':
			case 'p style="text-align: right"':
			case 'p style="text-align: right;"':

				// Define para tag.
				$para_tag = '<' . $tag . ' class="textblock textblock-right" id="textblock-' . $text_signature . '">' . $commenticon;
				break;

			case 'p style="text-align:center"':
			case 'p style="text-align:center;"':
			case 'p style="text-align: center"':
			case 'p style="text-align: center;"':

				// Define para tag.
				$para_tag = '<' . $tag . ' class="textblock textblock-center" id="textblock-' . $text_signature . '">' . $commenticon;
				break;

			case 'p style="text-align:justify"':
			case 'p style="text-align:justify;"':
			case 'p style="text-align: justify"':
			case 'p style="text-align: justify;"':

				// Define para tag.
				$para_tag = '<' . $tag . ' class="textblock textblock-justify" id="textblock-' . $text_signature . '">' . $commenticon;
				break;

			case 'p class="notes"':

				// Define para tag.
				$para_tag = '<p class="notes textblock" id="textblock-' . $text_signature . '">' . $commenticon;
				break;

			case 'div':

				// Define opening tag (we'll close it later).
				$para_tag = '<div class="textblock" id="textblock-' . $text_signature . '">' . $commenticon;
				break;

			case 'span':

				// Define opening tag (we'll close it later).
				$para_tag = '<span class="textblock" id="textblock-' . $text_signature . '">' . $commenticon;
				break;

		}

		// --<
		return $para_tag;

	}

	/**
	 * Get the minimise all button.
	 *
	 * @since 3.4
	 *
	 * @param str $sidebar The sidebar identifier - "comments", "toc" or "activity".
	 * @return str $tag The tag.
	 */
	public function get_minimise_all_button( $sidebar = 'comments' ) {

		switch ( $sidebar ) {

			case 'comments':
				// Define minimise button.
				$tag = '<span id="cp_minimise_all_comments" title="' . __( 'Minimize all Comment Sections', 'commentpress-core' ) . '"></span>';
				break;

			case 'activity':
				// Define minimise button.
				$tag = '<span id="cp_minimise_all_activity" title="' . __( 'Minimize all Activity Sections', 'commentpress-core' ) . '"></span>';
				break;

			case 'toc':
				// Define minimise button.
				$tag = '<span id="cp_minimise_all_contents" title="' . __( 'Minimize all Contents Sections', 'commentpress-core' ) . '"></span>';
				break;

		}

		// --<
		return $tag;

	}

	/**
	 * Get the header minimise button.
	 *
	 * @since 3.4
	 *
	 * @return str $link The markup of the link.
	 */
	public function get_header_min_link() {

		// Define minimise button.
		$link = '<li>' .
			'<a href="#" id="btn_header_min" class="css_btn" title="' . __( 'Minimize Header', 'commentpress-core' ) . '">' .
				__( 'Minimize Header', 'commentpress-core' ) .
			'</a>' .
		'</li>' . "\n";

		// --<
		return $link;

	}

	/**
	 * Get an image wrapped in a link.
	 *
	 * @since 3.4
	 *
	 * @param str $src The location of image file.
	 * @param str $url The link target.
	 * @return string $tag The markup.
	 */
	public function get_linked_image( $src = '', $url = '' ) {

		// Init html.
		$html = '';

		// Maybe construct image tag.
		if ( ! empty( $src ) ) {
			$html .= '<img src="' . $src . '" />';
		}

		// Maybe construct link around image.
		if ( ! empty( $url ) ) {
			$html .= '<a href="' . $url . '">' . $html . '</a>';
		}

		// --<
		return $html;

	}

}
