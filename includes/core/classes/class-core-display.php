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
		 * Broadcast that this class has loaded.
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

		// Enqueue common Javascripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 20 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our Javascripts.
	 *
	 * Enqueue jQuery, jQuery UI and plugins.
	 *
	 * @since 3.4
	 */
	public function enqueue_scripts() {

		// Don't include in admin or wp-login.php.
		if ( is_admin() || ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' == $GLOBALS['pagenow'] ) ) {
			return;
		}

		// Default to minified scripts.
		$min = commentpress_minified();

		// Add our jQuery plugin and dependencies.
		wp_enqueue_script(
			'jquery_commentpress',
			plugins_url( 'includes/core/assets/js/jquery.commentpress' . $min . '.js', COMMENTPRESS_PLUGIN_FILE ),
			[ 'jquery', 'jquery-form', 'jquery-ui-core', 'jquery-ui-resizable', 'jquery-ui-tooltip' ],
			COMMENTPRESS_VERSION, // Version.
			false // In footer.
		);

		// Get vars.
		$vars = $this->get_javascript_vars();

		// Localise the WordPress way.
		wp_localize_script( 'jquery_commentpress', 'CommentpressSettings', $vars );

		// Add jQuery Scroll-To plugin.
		wp_enqueue_script(
			'jquery_scrollto',
			plugins_url( 'includes/core/assets/js/jquery.scrollTo.js', COMMENTPRESS_PLUGIN_FILE ),
			[ 'jquery_commentpress' ],
			COMMENTPRESS_VERSION, // Version.
			false // In footer.
		);

		// Optionally get text highlighter.
		$this->get_text_highlighter();

	}

	/**
	 * Enqueue our text highlighter script.
	 *
	 * @since 3.8
	 */
	public function get_text_highlighter() {

		// Only allow text highlighting on non-touch devices - but allow testing override.
		if ( ! $this->core->device->is_touch() || ( defined( 'COMMENTPRESS_TOUCH_SELECT' ) && COMMENTPRESS_TOUCH_SELECT ) ) {

			// Bail if not a commentable Page/Post.
			if ( ! $this->core->parser->is_commentable() ) {
				return;
			}

			// Default to minified scripts.
			$min = commentpress_minified();

			// Add jQuery wrapSelection plugin.
			wp_enqueue_script(
				'jquery_wrapselection',
				plugins_url( 'includes/core/assets/js/jquery.wrap-selection' . $min . '.js', COMMENTPRESS_PLUGIN_FILE ),
				[ 'jquery_commentpress' ],
				COMMENTPRESS_VERSION, // Version.
				false // In footer.
			);

			// Add jQuery highlighter plugin.
			wp_enqueue_script(
				'jquery_highlighter',
				plugins_url( 'includes/core/assets/js/jquery.highlighter' . $min . '.js', COMMENTPRESS_PLUGIN_FILE ),
				[ 'jquery_wrapselection' ],
				COMMENTPRESS_VERSION, // Version.
				false // In footer.
			);

			// Add jQuery text highlighter plugin.
			wp_enqueue_script(
				'jquery_texthighlighter',
				plugins_url( 'includes/core/assets/js/jquery.texthighlighter' . $min . '.js', COMMENTPRESS_PLUGIN_FILE ),
				[ 'jquery_highlighter' ],
				COMMENTPRESS_VERSION, // Version.
				false // In footer.
			);

			// Define popover for textblocks.
			$popover_textblock = '<span class="popover-holder"><div class="popover-holder-inner"><div class="popover-holder-caret"></div><div class="popover-holder-btn-left"><span class="popover-holder-btn-left-comment">' . esc_html__( 'Comment', 'commentpress-core' ) . '</span><span class="popover-holder-btn-left-quote">' . esc_html__( 'Quote &amp; Comment', 'commentpress-core' ) . '</span></div><div class="popover-holder-btn-right">&times;</div></div></span>';

			// Define popover for Comments.
			$popover_comment = '<span class="comment-popover-holder"><div class="popover-holder-inner"><div class="popover-holder-caret"></div><div class="popover-holder-btn-left"><span class="comment-popover-holder-btn-left-quote">' . esc_html__( 'Quote', 'commentpress-core' ) . '</span></div><div class="popover-holder-btn-right">&times;</div></div></span>';

			// Define localisation array.
			$texthighlighter_vars = [
				'popover_textblock' => $popover_textblock,
				'popover_comment' => $popover_comment,
			];

			// Create translations.
			$texthighlighter_translations = [
				'dialog_title' => esc_html__( 'Are you sure?', 'commentpress-core' ),
				'dialog_content' => esc_html__( 'You have not yet submitted your comment. Are you sure you want to discard it?', 'commentpress-core' ),
				'dialog_yes' => esc_html__( 'Discard', 'commentpress-core' ),
				'dialog_no' => esc_html__( 'Keep', 'commentpress-core' ),
				'backlink_text' => esc_html__( 'Back', 'commentpress-core' ),
			];

			// Add to vars.
			$texthighlighter_vars['localisation'] = $texthighlighter_translations;

			// Localise the WordPress way.
			wp_localize_script( 'jquery_texthighlighter', 'CommentpressTextSelectorSettings', $texthighlighter_vars );

		}

	}

	/**
	 * Get Javascript params for the plugin, context dependent.
	 *
	 * @since 3.4
	 *
	 * @return array $vars The Javascript setup params.
	 */
	public function get_javascript_vars() {

		// Init return.
		$vars = [];

		// Access Post.
		global $post;

		// If we don't have a Post - like on the 404 Page.
		if ( ! ( $post instanceof WP_Post ) ) {

			// Comments must be closed.
			$vars['cp_comments_open'] = 'n';

			// Set empty permalink.
			$vars['cp_permalink'] = '';

		} else {

			// Check for Post "comment_status".
			$vars['cp_comments_open'] = ( $post->comment_status == 'open' ) ? 'y' : 'n';

			// Set Post permalink.
			$vars['cp_permalink'] = get_permalink( $post->ID );

		}

		// Assume no admin bars.
		$vars['cp_wp_adminbar'] = 'n';
		$vars['cp_bp_adminbar'] = 'n';

		// Match WordPress 3.8+ admin bar.
		$vars['cp_wp_adminbar_height'] = '32';
		$vars['cp_wp_adminbar_expanded'] = '0';

		// Are we showing the WordPress admin bar?
		if ( is_admin_bar_showing() ) {

			// We have it.
			$vars['cp_wp_adminbar'] = 'y';

			// Admin bar expands in height below 782px viewport width.
			$vars['cp_wp_adminbar_expanded'] = '46';

		}

		// Are we logged in AND in a BuddyPress scenario?
		if ( is_user_logged_in() && $this->core->bp->is_buddypress() ) {

			// Regardless of version, settings can be made in bp-custom.php.
			if ( defined( 'BP_DISABLE_ADMIN_BAR' ) && BP_DISABLE_ADMIN_BAR ) {

				// We've killed both admin bars.
				$vars['cp_bp_adminbar'] = 'n';
				$vars['cp_wp_adminbar'] = 'n';

			}

			/*
			 * Check for BuddyPress versions prior to 1.6.
			 *
			 * BuddyPress 1.6 uses the WordPress admin bar instead of a custom one.
			 */
			if ( ! function_exists( 'bp_get_version' ) ) {

				// But, this can already be overridden in bp-custom.php.
				if ( defined( 'BP_USE_WP_ADMIN_BAR' ) && BP_USE_WP_ADMIN_BAR ) {

					// Not present.
					$vars['cp_bp_adminbar'] = 'n';
					$vars['cp_wp_adminbar'] = 'y';

				} else {

					// Let our javascript know.
					$vars['cp_bp_adminbar'] = 'y';

					// Recheck 'BP_DISABLE_ADMIN_BAR'.
					if ( defined( 'BP_DISABLE_ADMIN_BAR' ) && BP_DISABLE_ADMIN_BAR ) {

						// We've killed both admin bars.
						$vars['cp_bp_adminbar'] = 'n';
						$vars['cp_wp_adminbar'] = 'n';

					}

				}

			}

		}

		// Add rich text editor by default.
		$vars['cp_tinymce'] = 1;

		// Check if Users must be logged in to comment.
		if ( get_option( 'comment_registration' ) == '1' && ! is_user_logged_in() ) {

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Check CommentPress Core option.
		if (
			$this->core->db->setting_exists( 'cp_comment_editor' ) &&
			$this->core->db->setting_get( 'cp_comment_editor' ) != '1'
		) {

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// If on a public Group Blog and User isn't logged in.
		if ( $this->core->bp->is_groupblog() && ! is_user_logged_in() ) {

			// Don't add rich text editor, because only Members can comment.
			$vars['cp_tinymce'] = 0;

		}

		/**
		 * Filters the TinyMCE vars.
		 *
		 * Allow plugins to override TinyMCE.
		 *
		 * @since 3.4
		 *
		 * @param bool $cp_tinymce The default TinyMCE vars.
		 */
		$vars['cp_tinymce'] = apply_filters( 'cp_override_tinymce', $vars['cp_tinymce'] );

		// Add mobile var.
		$vars['cp_is_mobile'] = 0;

		// Is it a mobile?
		if ( $this->core->device->is_mobile() ) {

			// Is mobile.
			$vars['cp_is_mobile'] = 1;

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Add touch var.
		$vars['cp_is_touch'] = 0;

		// Is it a touch device?
		if ( $this->core->device->is_touch() ) {

			// Is touch.
			$vars['cp_is_touch'] = 1;

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Add touch testing var.
		$vars['cp_touch_testing'] = 0;

		// Have we set our testing constant?
		if ( defined( 'COMMENTPRESS_TOUCH_SELECT' ) && COMMENTPRESS_TOUCH_SELECT ) {

			// Support touch device testing.
			$vars['cp_touch_testing'] = 1;

		}

		// Add tablet var.
		$vars['cp_is_tablet'] = 0;

		// Is it a touch device?
		if ( $this->core->device->is_tablet() ) {

			// Is touch.
			$vars['cp_is_tablet'] = 1;

			// Don't add rich text editor.
			$vars['cp_tinymce'] = 0;

		}

		// Add rich text editor behaviour.
		$vars['cp_promote_reading'] = 1;

		// Check option.
		if (
			$this->core->db->setting_exists( 'cp_promote_reading' ) &&
			$this->core->db->setting_get( 'cp_promote_reading' ) != '1'
		) {

			// Promote commenting.
			$vars['cp_promote_reading'] = 0;

		}

		// Add Special Page var.
		$vars['cp_special_page'] = ( $this->core->pages_legacy->is_special_page() ) ? '1' : '0';

		// Are we in a BuddyPress scenario?
		if ( $this->core->bp->is_buddypress() ) {

			// Is it a component homepage?
			if ( $this->core->bp->is_buddypress_special_page() ) {

				// Treat them the way we do ours.
				$vars['cp_special_page'] = '1';

			}

		}

		// Get path.
		$url_info = wp_parse_url( get_option( 'siteurl' ) );

		// Add path for cookies.
		$vars['cp_cookie_path'] = '/';
		if ( ! empty( $url_info['path'] ) ) {
			$vars['cp_cookie_path'] = trailingslashit( $url_info['path'] );
		}

		// Add Page.
		global $page;
		$vars['cp_multipage_page'] = ( ! empty( $page ) ) ? $page : 0;

		// Are Chapters Pages?
		$vars['cp_toc_chapter_is_page'] = $this->core->db->setting_get( 'cp_toc_chapter_is_page' );

		// Are Sub-pages shown?
		$vars['cp_show_subpages'] = $this->core->db->setting_get( 'cp_show_subpages' );

		// Set default sidebar.
		$vars['cp_default_sidebar'] = $this->core->theme->sidebar->default_get();

		// Set scroll speed.
		$vars['cp_js_scroll_speed'] = $this->core->db->setting_get( 'cp_js_scroll_speed' );

		// Set min Page width.
		$vars['cp_min_page_width'] = $this->core->db->setting_get( 'cp_min_page_width' );

		// Default to showing textblock meta.
		$vars['cp_textblock_meta'] = 1;

		// Check option.
		if (
			$this->core->db->setting_exists( 'cp_textblock_meta' ) &&
			$this->core->db->setting_get( 'cp_textblock_meta' ) == 'n'
		) {

			// Only show textblock meta on rollover.
			$vars['cp_textblock_meta'] = 0;

		}

		// Default to Page navigation enabled.
		$vars['cp_page_nav_enabled'] = 1;

		// Check option.
		if (
			$this->core->db->setting_exists( 'cp_page_nav_enabled' ) &&
			$this->core->db->setting_get( 'cp_page_nav_enabled' ) == 'n'
		) {

			// Disable Page navigation.
			$vars['cp_page_nav_enabled'] = 0;

		}

		// Default to parsing content and Comments.
		$vars['cp_do_not_parse'] = 0;

		// Check option.
		if (
			$this->core->db->setting_exists( 'cp_do_not_parse' ) &&
			$this->core->db->setting_get( 'cp_do_not_parse' ) == 'y'
		) {

			// Do not parse.
			$vars['cp_do_not_parse'] = 1;

		}

		/**
		 * Filters the Javascript vars.
		 *
		 * @since 3.4
		 *
		 * @param array $vars The default Javascript vars.
		 */
		return apply_filters( 'commentpress_get_javascript_vars', $vars );

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
		if ( 'post' === $this->core->db->setting_get( 'cp_show_posts_or_pages_in_toc' ) ) {
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
		$list_style = $this->core->db->setting_get( 'cp_show_extended_toc' );

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
	 * @param int $author_id The numeric ID of the author.
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
			$link = sprintf(
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
				'echo' => true,
				'container' => '',
				'items_wrap' => '%3$s',
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
		$depth = $this->core->db->setting_get( 'cp_show_subpages' );
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
			'depth' => $depth,
			'show_date' => '',
			'date_format' => $this->core->db->setting_get( 'date_format' ),
			'child_of' => 0,
			'exclude' => implode( ',', $exclude ),
			'title_li' => '',
			'echo' => 1,
			'authors' => '',
			'sort_column' => 'menu_order, post_title',
			'link_before' => '',
			'link_after' => '',
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
			$icon = 'comment_add.png';
			$class = ' no_comments';

		} elseif ( $comment_count > 0 ) {

			// Show Comments Present icon.
			$icon = 'comment.png';
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
	 * @param str $sidebar The type of sidebar: "comments", "toc" or "activity".
	 * @return str $tag The tag.
	 */
	public function get_minimise_all_button( $sidebar = 'comments' ) {

		switch ( $sidebar ) {

			case 'comments':
				// Define minimise button.
				$tag = '<span id="cp_minimise_all_comments" title="' . __( 'Minimise all Comment Sections', 'commentpress-core' ) . '"></span>';
				break;

			case 'activity':
				// Define minimise button.
				$tag = '<span id="cp_minimise_all_activity" title="' . __( 'Minimise all Activity Sections', 'commentpress-core' ) . '"></span>';
				break;

			case 'toc':
				// Define minimise button.
				$tag = '<span id="cp_minimise_all_contents" title="' . __( 'Minimise all Contents Sections', 'commentpress-core' ) . '"></span>';
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
			'<a href="#" id="btn_header_min" class="css_btn" title="' . __( 'Minimise Header', 'commentpress-core' ) . '">' .
				__( 'Minimise Header', 'commentpress-core' ) .
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

	// -------------------------------------------------------------------------

	/**
	 * Returns the admin form HTML.
	 *
	 * @since 3.4
	 *
	 * @return str $admin_page The Admin Page HTML.
	 */
	public function get_admin_form() {

		// TODO: Implement upgrades.

		// Sanitise Admin Page URL.
		$url = isset( $_SERVER['REQUEST_URI'] ) ? wp_unslash( $_SERVER['REQUEST_URI'] ) : '';
		if ( ! empty( $url ) ) {
			$url_array = explode( '&', $url );
			if ( ! empty( $url_array ) ) {
				$url = $url_array[0];
			}
		}

		// Init return.
		$admin_page = '';

		// If we need to upgrade.
		if ( $this->core->db->upgrade_required() ) {

			// Get upgrade options.
			$upgrade = $this->get_upgrade();

			// Init text.
			$options_text = '';

			// If there are options.
			if ( $upgrade != '' ) {
				$options_text = __( ' The following options have become available in the new version.', 'commentpress-core' );
			}

			// Define Admin Page.
			$admin_page = '
			<h1>' . __( 'CommentPress Core Upgrade', 'commentpress-core' ) . '</h1>

			<form method="post" action="' . htmlentities( $url . '&updated=true' ) . '">

				' . wp_nonce_field( 'commentpress_admin_action', 'commentpress_nonce', true, false ) . '
				' . wp_referer_field( false ) . '
				<input id="cp_upgrade" name="cp_upgrade" value="1" type="hidden" />

				<h3>' . __( 'Please upgrade CommentPress Core', 'commentpress-core' ) . '</h3>

				<p>' . __( 'It looks like you are running an older version of CommentPress Core.', 'commentpress-core' ) . $options_text . '</p>

				<table class="form-table">

					' . $upgrade . '

				</table>

				<input type="hidden" name="action" value="update" />

				<p class="submit">
					<input type="submit" name="commentpress_submit" value="' . __( 'Upgrade', 'commentpress-core' ) . '" class="button-primary" />
				</p>

			</form>' . "\n\n\n\n";

		}

		// --<
		return $admin_page;

	}

	/**
	 * Returns the upgrade details for the admin form.
	 *
	 * @since 3.4
	 *
	 * @return str $upgrade The upgrade markup.
	 */
	public function get_upgrade() {

		// Init.
		$upgrade = '';

		// Do we have the option to choose which Post Types to skip (new in 3.9)?
		if ( ! $this->core->db->setting_exists( 'cp_post_types_disabled' ) ) {

			// Define labels.
			$description = __( 'Choose the Post Types on which CommentPress Core is enabled. Disabling a post type will mean that paragraph-level commenting will not be enabled on any entries of that post type. Default prior to 3.9 was that all post types were enabled.', 'commentpress-core' );
			$label = __( 'Post Types on which CommentPress Core is enabled.', 'commentpress-core' );

			// Get Post Types that support the editor.
			$capable_post_types = $this->core->db->post_types_get_supported();

			// Init outputs.
			$output = [];
			$options = '';

			// Sanity check.
			if ( count( $capable_post_types ) > 0 ) {

				// Construct checkbox for each Post Type.
				foreach ( $capable_post_types as $post_type => $label ) {

					// Add checked checkbox.
					$output[] = '<input type="checkbox" class="settings-checkbox" name="cp_post_types_enabled[]" value="' . $post_type . '" checked="checked" /> <label class="commentpress_settings_label" for="cp_post_types_enabled">' . $label . '</label><br>';

				}

				// Implode.
				$options = implode( "\n", $output );

			}

			// Define upgrade.
			$upgrade .= '
			<tr valign="top">
				<th scope="row"><label for="cp_post_types_enabled">' . $label . '</label></th>
				<td>
					<p>' . $description . '</p>
					<p>' . $options . '</p>
				</td>
			</tr>
			';

		}

		// Do we have the option to choose to disable parsing (new in 3.8.10)?
		if ( ! $this->core->db->setting_exists( 'cp_do_not_parse' ) ) {

			// Define labels.
			$description = __( 'Note: when comments are closed on an entry and there are no comments on that entry, if this option is set to "Yes" then the content will not be parsed for paragraphs, lines or blocks. Comments will also not be parsed, meaning that the entry behaves the same as content which is not commentable. Default prior to 3.8.10 was "No" - all content was always parsed.', 'commentpress-core' );
			$label = __( 'Disable CommentPress on entries with no comments.', 'commentpress-core' );
			$yes_label = __( 'Yes', 'commentpress-core' );
			$no_label = __( 'No', 'commentpress-core' );

			// Define upgrade.
			$upgrade .= '
			<tr valign="top">
				<th scope="row"><label for="cp_do_not_parse">' . $label . '</label></th>
				<td><select id="cp_do_not_parse" name="cp_do_not_parse">
						<option value="y">' . $yes_label . '</option>
						<option value="n" selected="selected">' . $no_label . '</option>
					</select>
					<p>' . $description . '</p>
				</td>
			</tr>
			';

		}

		// Do we have the option to choose to disable Page Navigation (new in 3.8.10)?
		if ( ! $this->core->db->setting_exists( 'cp_page_nav_enabled' ) ) {

			// Define labels.
			$label = __( 'Enable automatic page navigation (controls appearance of page numbering and navigation arrows on hierarchical pages). Previous default was "Yes".', 'commentpress-core' );
			$yes_label = __( 'Yes', 'commentpress-core' );
			$no_label = __( 'No', 'commentpress-core' );

			// Define upgrade.
			$upgrade .= '
			<tr valign="top">
				<th scope="row"><label for="cp_page_nav_enabled">' . $label . '</label></th>
				<td><select id="cp_page_nav_enabled" name="cp_page_nav_enabled">
						<option value="y" selected="selected">' . $yes_label . '</option>
						<option value="n">' . $no_label . '</option>
					</select>
				</td>
			</tr>
			';

		}

		// Do we have the option to choose to hide textblock meta (new in 3.5.9)?
		if ( ! $this->core->db->setting_exists( 'cp_textblock_meta' ) ) {

			// Define labels.
			$label = __( 'Show paragraph meta (Number and Comment Icon)', 'commentpress-core' );
			$yes_label = __( 'Always', 'commentpress-core' );
			$no_label = __( 'On rollover', 'commentpress-core' );

			// Define upgrade.
			$upgrade .= '
			<tr valign="top">
				<th scope="row"><label for="cp_textblock_meta">' . $label . '</label></th>
				<td><select id="cp_textblock_meta" name="cp_textblock_meta">
						<option value="y" selected="selected">' . $yes_label . '</option>
						<option value="n">' . $no_label . '</option>
					</select>
				</td>
			</tr>
			';

		}

		// Do we have the option to choose featured images (new in 3.5.4)?
		if ( ! $this->core->db->setting_exists( 'cp_featured_images' ) ) {

			// Define labels.
			$label = __( 'Enable Featured Images (Note: if you have already implemented this in a child theme, you should choose "No")', 'commentpress-core' );
			$yes_label = __( 'Yes', 'commentpress-core' );
			$no_label = __( 'No', 'commentpress-core' );

			// Define upgrade.
			$upgrade .= '
			<tr valign="top">
				<th scope="row"><label for="cp_featured_images">' . $label . '</label></th>
				<td><select id="cp_featured_images" name="cp_featured_images">
						<option value="y" selected="selected">' . $yes_label . '</option>
						<option value="n">' . $no_label . '</option>
					</select>
				</td>
			</tr>
			';

		}

		// Do we have the option to choose the default sidebar (new in 3.3.3)?
		if ( ! $this->core->db->setting_exists( 'cp_sidebar_default' ) ) {

			// Define labels.
			$label = __( 'Which sidebar do you want to be active by default? (can be overridden on individual pages)', 'commentpress-core' );
			$contents_label = __( 'Contents', 'commentpress-core' );
			$activity_label = __( 'Activity', 'commentpress-core' );
			$comments_label = __( 'Comments', 'commentpress-core' );

			// Define upgrade.
			$upgrade .= '
			<tr valign="top">
				<th scope="row"><label for="cp_sidebar_default">' . $label . '</label></th>
				<td><select id="cp_sidebar_default" name="cp_sidebar_default">
						<option value="toc">' . $contents_label . '</option>
						<option value="activity">' . $activity_label . '</option>
						<option value="comments" selected="selected">' . $comments_label . '</option>
					</select>
				</td>
			</tr>
			';

		}

		// Do we have the option to show or hide Page meta (new in 3.3.2)?
		if ( ! $this->core->db->setting_exists( 'cp_page_meta_visibility' ) ) {

			$meta_label = __( 'Show or hide page meta by default', 'commentpress-core' );
			$meta_show_label = __( 'Show page meta', 'commentpress-core' );
			$meta_hide_label = __( 'Hide page meta', 'commentpress-core' );

			// Define upgrade.
			$upgrade .= '
			<tr valign="top">
				<th scope="row"><label for="cp_page_meta_visibility">' . $meta_label . '</label></th>
				<td><select id="cp_page_meta_visibility" name="cp_page_meta_visibility">
						<option value="show">' . $meta_show_label . '</option>
						<option value="hide" selected="selected">' . $meta_hide_label . '</option>
					</select>
				</td>
			</tr>
			';

		}

		// Do we have the option to choose Blog Type (new in 3.3.1)?
		if ( ! $this->core->db->setting_exists( 'cp_blog_type' ) ) {

			// Define no types.
			$types = [];

			/**
			 * Build Text Format options.
			 *
			 * @since 3.3.1
			 *
			 * @param array $types Empty by default since others add them.
			 */
			$types = apply_filters( 'cp_blog_type_options', $types );

			// If we get some from a plugin, say.
			if ( ! empty( $types ) ) {

				// Define title.
				$type_title = __( 'Blog Type', 'commentpress-core' );

				/**
				 * Filters the Blog Type label.
				 *
				 * @since 3.3.1
				 *
				 * @param str $type_title The the Blog Type label.
				 */
				$type_title = apply_filters( 'cp_blog_type_label', $type_title );

				// Construct options.
				$type_option_list = [];
				$n = 0;
				foreach ( $types as $type ) {
					$type_option_list[] = '<option value="' . $n . '">' . $type . '</option>';
					$n++;
				}
				$type_options = implode( "\n", $type_option_list );

				// Define upgrade.
				$upgrade .= '
				<tr valign="top">
					<th scope="row"><label for="cp_blog_type">' . $type_title . '</label></th>
					<td><select id="cp_blog_type" name="cp_blog_type">
							' . $type_options . '
						</select>
					</td>
				</tr>
				';

			}

		}

		// Do we have the option to choose the TOC layout (new in 3.3)?
		if ( ! $this->core->db->setting_exists( 'cp_show_extended_toc' ) ) {

			$extended_label = __( 'Appearance of TOC for posts', 'commentpress-core' );
			$extended_info_label = __( 'Extended information', 'commentpress-core' );
			$extended_title_label = __( 'Just the title', 'commentpress-core' );

			// Define upgrade.
			$upgrade .= '
			<tr valign="top">
				<th scope="row"><label for="cp_show_extended_toc">' . $extended_label . '</label></th>
				<td><select id="cp_show_extended_toc" name="cp_show_extended_toc">
						<option value="1">' . $extended_info_label . '</option>
						<option value="0" selected="selected">' . $extended_title_label . '</option>
					</select>
				</td>
			</tr>
			';

		}

		// Do we have the option to set the Comment editor?
		if ( ! $this->core->db->setting_exists( 'cp_comment_editor' ) ) {

			$editor_label = __( 'Comment form editor', 'commentpress-core' );
			$rich_label = __( 'Rich-text Editor', 'commentpress-core' );
			$plain_label = __( 'Plain-text Editor', 'commentpress-core' );

			// Define upgrade.
			$upgrade .= '
			<tr valign="top">
				<th scope="row"><label for="cp_comment_editor">' . $editor_label . '</label></th>
				<td><select id="cp_comment_editor" name="cp_comment_editor">
						<option value="1" selected="selected">' . $rich_label . '</option>
						<option value="0">' . $plain_label . '</option>
					</select>
				</td>
			</tr>
			';

		}

		// Do we have the option to set the default behaviour?
		if ( ! $this->core->db->setting_exists( 'cp_promote_reading' ) ) {

			$behaviour_label = __( 'Default comment form behaviour', 'commentpress-core' );
			$reading_label = __( 'Promote reading', 'commentpress-core' );
			$commenting_label = __( 'Promote commenting', 'commentpress-core' );

			// Define upgrade.
			$upgrade .= '
			<tr valign="top">
				<th scope="row"><label for="cp_promote_reading">' . $behaviour_label . '</label></th>
				<td><select id="cp_promote_reading" name="cp_promote_reading">
						<option value="1">' . $reading_label . '</option>
						<option value="0" selected="selected">' . $commenting_label . '</option>
					</select>
				</td>
			</tr>
			';

		}

		// Do we have the option to show or hide titles?
		if ( ! $this->core->db->setting_exists( 'cp_title_visibility' ) ) {

			// Define labels.
			$titles_label = __( 'Show or hide page titles by default', 'commentpress-core' );
			$titles_select_show = __( 'Show page titles', 'commentpress-core' );
			$titles_select_hide = __( 'Hide page titles', 'commentpress-core' );

			// Define upgrade.
			$upgrade .= '
			<tr valign="top">
				<th scope="row"><label for="cp_title_visibility">' . $titles_label . '</label></th>
				<td><select id="cp_title_visibility" name="cp_title_visibility">
						<option value="show" selected="selected">' . $titles_select_show . '</option>
						<option value="hide">' . $titles_select_hide . '</option>
					</select>
				</td>
			</tr>
			';

		}

		// Do we have the option to set the scroll speed?
		if ( ! $this->core->db->setting_exists( 'cp_js_scroll_speed' ) ) {

			// Define labels.
			$scroll_label = __( 'Scroll speed', 'commentpress-core' );
			$scroll_ms_label = __( 'milliseconds', 'commentpress-core' );

			// Define upgrade.
			$upgrade .= '
			<tr valign="top">
				<th scope="row"><label for="cp_js_scroll_speed">' . $scroll_label . '</label></th>
				<td><input type="text" id="cp_js_scroll_speed" name="cp_js_scroll_speed" value="' . $this->core->db->js_scroll_speed . '" class="small-text" /> ' . $scroll_ms_label . '</td>
			</tr>
			';

		}

		// Do we have the option to set the minimum Page width?
		if ( ! $this->core->db->setting_exists( 'cp_min_page_width' ) ) {

			// Define labels.
			$min_label = __( 'Minimum page width', 'commentpress-core' );
			$min_pix_label = __( 'pixels', 'commentpress-core' );

			// Define upgrade.
			$upgrade .= '
			<tr valign="top">
				<th scope="row"><label for="cp_min_page_width"></label></th>
				<td><input type="text" id="cp_min_page_width" name="cp_min_page_width" value="' . $this->core->db->min_page_width . '" class="small-text" /> ' . $min_pix_label . '</td>
			</tr>
			';

		}

		// --<
		return $upgrade;

	}

}
