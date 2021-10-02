<?php

/**
 * CommentPress Core Display Class.
 *
 * A class that is intended to encapsulate display handling.
 *
 * @since 3.0
 */
class Commentpress_Core_Display {

	/**
	 * Plugin object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $parent_obj The plugin object.
	 */
	public $parent_obj;

	/**
	 * Database interaction object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $db The database object.
	 */
	public $db;



	/**
	 * Initialises this object.
	 *
	 * @since 3.0
	 *
	 * @param object $parent_obj A reference to the parent object.
	 */
	public function __construct( $parent_obj ) {

		// Store reference to parent.
		$this->parent_obj = $parent_obj;

		// Store reference to database wrapper (child of calling obj).
		$this->db = $this->parent_obj->db;

		// Init.
		$this->_init();

	}



	/**
	 * If needed, sets up this object.
	 *
	 * @since 3.0
	 */
	public function activate() {

		// Force WordPress to regenerate theme directories.
		search_theme_directories( true );

		/**
		 * Get groupblog-set theme, if we have one.
		 *
		 * Allow filtering here because plugins may want to override a correctly-set
		 * CommentPress Core theme for a particular groupblog (or type of groupblog).
		 *
		 * If that is the case, then the filter callback must return boolean 'false'
		 * to prevent the theme being applied and also implement a filter on
		 * 'cp_forced_theme_slug' below that returns the desired theme slug.
		 *
		 * @since 3.4
		 *
		 * @param array The existing array containing the stylesheet and template paths.
		 * @return array The modified array containing the stylesheet and template paths.
		 */
		$theme = apply_filters( 'commentpress_get_groupblog_theme', $this->parent_obj->get_groupblog_theme() );

		// Did we get a CommentPress Core one?
		if ( $theme !== false ) {

			// We're in a groupblog context: BuddyPress Groupblog will already have set
			// the theme because we're adding our wpmu_new_blog action after it.

			// --<
			return;

		}

		// Test for WP3.4.
		if ( function_exists( 'wp_get_themes' ) ) {

			/**
			 * Get CommentPress Core theme by default, but allow overrides.
			 *
			 * @since 3.4
			 *
			 * @param str The default slug of the theme.
			 * @return str The modified slug of the theme.
			 */
			$target_theme = apply_filters( 'cp_forced_theme_slug', 'commentpress-modern' );

			// Get the theme we want.
			$theme = wp_get_theme( $target_theme );

			// If we get it.
			if ( $theme->exists() ) {

				// Ignore if not allowed.
				//if ( is_multisite() AND ! $theme->is_allowed() ) return;

				// Activate it.
				switch_theme(
					$theme->get_template(),
					$theme->get_stylesheet()
				);

			}

		} else {

			// Use pre-3.4 logic.
			$themes = get_themes();

			/**
			 * Get CommentPress Core theme by default, but allow overrides.
			 *
			 * NB, the key prior to WP 3.4 is the theme's *name*.
			 *
			 * @since 3.4
			 */
			$target_theme = apply_filters( 'cp_forced_theme_name', 'CommentPress Default Theme' );

			// The key is the theme name.
			if ( isset( $themes[$target_theme] ) ) {

				// Activate it.
				switch_theme(
					$themes[$target_theme]['Template'],
					$themes[$target_theme]['Stylesheet']
				);

			}

		}

	}



	/**
	 * If needed, destroys this object.
	 *
	 * @since 3.0
	 */
	public function deactivate() {

		// Test for WP3.4.
		if ( function_exists( 'wp_get_theme' ) ) {

			/**
			 * Get WordPress default theme, but allow overrides.
			 *
			 * @since 3.4
			 *
			 * @param str The slug of the default theme to switch to.
			 * @return str The modified slug of the default theme to switch to.
			 */
			$target_theme = apply_filters( 'cp_restore_theme_slug', WP_DEFAULT_THEME );

			// Get the theme we want.
			$theme = wp_get_theme( $target_theme );

			// If we get it.
			if ( $theme->exists() ) {

				// Ignore if not allowed.
				//if ( is_multisite() AND ! $theme->is_allowed() ) return;

				// Activate it.
				switch_theme(
					$theme->get_template(),
					$theme->get_stylesheet()
				);

			}

		} else {

			// Use pre-3.4 logic.
			$themes = get_themes();

			/**
			 * Get default theme by default, but allow overrides.
			 *
			 * NB, the key prior to WP 3.4 is the theme's *name*.
			 *
			 * @since 3.4
			 *
			 * @param str The key of the default theme to switch to.
			 * @return str The modified key of the default theme to switch to.
			 */
			$target_theme = apply_filters( 'cp_restore_theme_name', WP_DEFAULT_THEME );

			// The key is the theme name.
			if ( isset( $themes[$target_theme] ) ) {

				// Activate it.
				switch_theme(
					$themes[$target_theme]['Template'],
					$themes[$target_theme]['Stylesheet']
				);

			}

		}

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Public Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Enqueue jQuery, jQuery UI and plugins.
	 *
	 * @since 3.4
	 */
	public function get_jquery() {

		// Default to minified scripts.
		$debug_state = commentpress_minified();

		// Add our javascript plugin and dependencies.
		wp_enqueue_script(
			'jquery_commentpress',
			plugins_url( 'commentpress-core/assets/js/jquery.commentpress' . $debug_state . '.js', COMMENTPRESS_PLUGIN_FILE ),
			[ 'jquery', 'jquery-form', 'jquery-ui-core', 'jquery-ui-resizable', 'jquery-ui-tooltip' ],
			COMMENTPRESS_VERSION // Version.
		);

		// Get vars.
		$vars = $this->db->get_javascript_vars();

		// Localise the WordPress way.
		wp_localize_script( 'jquery_commentpress', 'CommentpressSettings', $vars );

		// Add jQuery Scroll-To plugin.
		wp_enqueue_script(
			'jquery_scrollto',
			plugins_url( 'commentpress-core/assets/js/jquery.scrollTo.js', COMMENTPRESS_PLUGIN_FILE ),
			[ 'jquery_commentpress' ],
			COMMENTPRESS_VERSION // Version.
		);

		// Add jQuery Cookie plugin (renamed to jquery.biscuit.js because some hosts don't like 'cookie' in the filename).
		wp_enqueue_script(
			'jquery_cookie',
			plugins_url( 'commentpress-core/assets/js/jquery.biscuit.js', COMMENTPRESS_PLUGIN_FILE ),
			[ 'jquery_commentpress' ],
			COMMENTPRESS_VERSION // Version.
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

		// Only allow text highlighting on non-touch devices (allow testing override).
		if ( ! $this->db->is_touch() OR ( defined( 'COMMENTPRESS_TOUCH_SELECT' ) AND COMMENTPRESS_TOUCH_SELECT ) ) {

			// Bail if not a commentable page/post.
			if ( ! $this->parent_obj->is_commentable() ) {
				return;
			}

			// Default to minified scripts.
			$debug_state = commentpress_minified();

			// Add jQuery wrapSelection plugin.
			wp_enqueue_script(
				'jquery_wrapselection',
				plugins_url( 'commentpress-core/assets/js/jquery.wrap-selection' . $debug_state . '.js', COMMENTPRESS_PLUGIN_FILE ),
				[ 'jquery_commentpress' ],
				COMMENTPRESS_VERSION // Version.
			);

			// Add jQuery highlighter plugin.
			wp_enqueue_script(
				'jquery_highlighter',
				plugins_url( 'commentpress-core/assets/js/jquery.highlighter' . $debug_state . '.js', COMMENTPRESS_PLUGIN_FILE ),
				[ 'jquery_wrapselection' ],
				COMMENTPRESS_VERSION // Version.
			);

			// Add jQuery text highlighter plugin.
			wp_enqueue_script(
				'jquery_texthighlighter',
				plugins_url( 'commentpress-core/assets/js/jquery.texthighlighter' . $debug_state . '.js', COMMENTPRESS_PLUGIN_FILE ),
				[ 'jquery_highlighter' ],
				COMMENTPRESS_VERSION // Version.
			);

			// Define popover for textblocks.
			$popover_textblock = '<span class="popover-holder"><div class="popover-holder-inner"><div class="popover-holder-caret"></div><div class="popover-holder-btn-left"><span class="popover-holder-btn-left-comment">' . __( 'Comment', 'commentpress-core' ) . '</span><span class="popover-holder-btn-left-quote">' . __( 'Quote &amp; Comment', 'commentpress-core' ) . '</span></div><div class="popover-holder-btn-right">&times;</div></div></span>';

			// Define popover for comments.
			$popover_comment = '<span class="comment-popover-holder"><div class="popover-holder-inner"><div class="popover-holder-caret"></div><div class="popover-holder-btn-left"><span class="comment-popover-holder-btn-left-quote">' . __( 'Quote', 'commentpress-core' ) . '</span></div><div class="popover-holder-btn-right">&times;</div></div></span>';

			// Define localisation array.
			$texthighlighter_vars = [
				'popover_textblock' => $popover_textblock,
				'popover_comment' => $popover_comment,
			];

			// Create translations.
			$texthighlighter_translations = [
				'dialog_title' => __( 'Are you sure?', 'commentpress-core' ),
				'dialog_content' => __( 'You have not yet submitted your comment. Are you sure you want to discard it?', 'commentpress-core' ),
				'dialog_yes' => __( 'Discard', 'commentpress-core' ),
				'dialog_no' => __( 'Keep', 'commentpress-core' ),
				'backlink_text' => __( 'Back', 'commentpress-core' ),
			];

			// Add to vars.
			$texthighlighter_vars['localisation'] = $texthighlighter_translations;

			// Localise the WordPress way.
			wp_localize_script( 'jquery_texthighlighter', 'CommentpressTextSelectorSettings', $texthighlighter_vars );

		}

	}



	/**
	 * Enqueue our quicktags script.
	 *
	 * @since 3.4
	 */
	public function get_custom_quicktags() {

		// Don't bother if the current user lacks permissions.
		if ( ! current_user_can( 'edit_posts' ) AND ! current_user_can( 'edit_pages' ) ) {
			return;
		}

		// Need access to WP version.
		global $wp_version;

		// There's a new quicktags script in 3.3.
		if ( version_compare( $wp_version, '3.2.99999', '>=' ) ) {

			// Add our javascript script and dependencies.
			wp_enqueue_script(
				'commentpress_custom_quicktags',
				plugin_dir_url( COMMENTPRESS_PLUGIN_FILE ) . 'commentpress-core/assets/js/cp_quicktags_3.3.js',
				[ 'quicktags' ],
				COMMENTPRESS_VERSION, // Version.
				true // In footer
			);

		} else {

			// Add our javascript script and dependencies.
			wp_enqueue_script(
				'commentpress_custom_quicktags',
				plugin_dir_url( COMMENTPRESS_PLUGIN_FILE ) . 'commentpress-core/assets/js/cp_quicktags.js',
				[ 'quicktags' ],
				COMMENTPRESS_VERSION, // Version.
				false // Not in footer (but may need to be in WP 3.3)
			);

		}

	}



	/**
	 * Get plugin stylesheets.
	 *
	 * @since 3.0
	 */
	public function get_frontend_styles() {

		// Add jQuery UI stylesheet -> needed for resizable columns.
		wp_enqueue_style(
			'cp_jquery_ui_base',
			plugins_url( 'commentpress-core/assets/css/jquery.ui.css', COMMENTPRESS_PLUGIN_FILE ),
			false,
			COMMENTPRESS_VERSION, // Version.
			'all' // Media.
		);

	}



	/**
	 * Test if TinyMCE is allowed.
	 *
	 * @since 3.4
	 *
	 * @return bool $allowed True if TinyMCE is allowed, false otherwise.
	 */
	public function is_tinymce_allowed() {

		// Default to allowed.
		$allowed = true;

		// Check option.
		if (
			$this->db->option_exists( 'cp_comment_editor' ) AND
			$this->db->option_get( 'cp_comment_editor' ) != '1'
		) {

			// Disallow.
			$allowed = false;

		} else {

			// Don't return TinyMCE for touchscreens, mobile phones or tablets
			if (
				( isset( $this->db->is_mobile_touch ) AND $this->db->is_mobile_touch ) OR
				( isset( $this->db->is_mobile ) AND $this->db->is_mobile ) OR
				( isset( $this->db->is_tablet ) AND $this->db->is_tablet )
			) {

				// Disallow.
				$allowed = false;

			}

		}

		// --<
		return apply_filters( 'commentpress_is_tinymce_allowed', $allowed );

	}



	/**
	 * Get help text.
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



	/**
	 * Show the posts and their comment count in a list format.
	 *
	 * @since 3.4
	 *
	 * @param str $params The parameters to list posts by.
	 */
	public function list_posts( $params = 'numberposts=-1&order=DESC' ) {

		// Get all posts.
		$posts = get_posts( $params );

		// Have we set the option?
		$list_style = $this->db->option_get( 'cp_show_extended_toc' );

		// If not set or set to 'off'.
		if ( $list_style === false OR $list_style == '0' ) {

			// --------------------------
			// Old-style undecorated list.
			// --------------------------

			// Run through them
			foreach( $posts AS $item ) {

				// Get comment count for that post.
				$count = count( $this->db->get_approved_comments( $item->ID ) );

				// Write list item.
				echo '<li class="title"><a href="' . get_permalink( $item->ID ) . '">' . get_the_title( $item->ID ) . ' (' . $count . ')</a></li>' . "\n";

			}

		} else {

			// ------------------------
			// New-style decorated list.
			// ------------------------

			// Access current post.
			global $post;

			// Run through them.
			foreach( $posts AS $item ) {

				// Init output.
				$html = '';

				// Get comment count for that post.
				$count = count( $this->db->get_approved_comments( $item->ID ) );

				// Compat with Co-Authors Plus.
				if ( function_exists( 'get_coauthors' ) ) {

					// Get multiple authors.
					$authors = get_coauthors( $item->ID );

					// If we get some.
					if ( ! empty( $authors ) ) {

						// Use the Co-Authors format of "name, name, name & name".
						$author_html = '';

						// Init counter.
						$n = 1;

						// Find out how many author we have.
						$author_count = count( $authors );

						// Loop.
						foreach( $authors AS $author ) {

							// Default to comma.
							$sep = ', ';

							// If we're on the penultimate.
							if ( $n == ($author_count - 1) ) {

								// Use ampersand.
								$sep = __( ' &amp; ', 'commentpress-core' );

							}

							// If we're on the last, don't add.
							if ( $n == $author_count ) { $sep = ''; }

							// Get name.
							$author_html .= $this->echo_post_author( $author->ID, false );

							// Add separator.
							$author_html .= $sep;

							// Increment.
							$n++;

							// Are we showing avatars?
							if ( get_option( 'show_avatars' ) ) {

								// Get avatar.
								$html .= get_avatar( $author->ID, $size='32' );

							}

						}

						// Add citation
						$html .= '<cite class="fn">' . $author_html . '</cite>' . "\n";

						// Add permalink
						$html .= '<p class="post_activity_date">' . esc_html( get_the_time( get_option( 'date_format' ), $item->ID ) ) . '</p>' . "\n";

					}

				} else {

					// Get avatar.
					$author_id = $item->post_author;

					// Are we showing avatars?
					if ( get_option( 'show_avatars' ) ) {

						$html .= get_avatar( $author_id, $size='32' );

					}

					// Add citation.
					$html .= '<cite class="fn">' . $this->echo_post_author( $author_id, false ) . '</cite>';

					// Add permalink.
					$html .= '<p class="post_activity_date">' . esc_html( get_the_time( get_option( 'date_format' ), $item->ID ) ) . '</p>';

				}

				// Init current post class as empty.
				$current_post = '';

				// If we're on the current post and it's this item.
				if ( is_singular() AND isset( $post ) AND $post->ID == $item->ID ) {
					$current_post = ' current_page_item';
				}

				// Write list item.
				echo '<li class="title' . $current_post . '">
				<div class="post-identifier">
				' . $html . '
				</div>
				<a href="' . get_permalink( $item->ID ) . '" class="post_activity_link">' . get_the_title( $item->ID ) . ' (' . $count . ')</a>
				</li>' . "\n";

			}

		}

	}



	/**
	 * Show username (with link).
	 *
	 * @since 3.4
	 *
	 * @param int $author_id The numeric ID of the author.
	 * @param bool $echo True if link is to be echoed, false if returned.
	 */
	public function echo_post_author( $author_id, $echo = true ) {

		// Get author details.
		$user = get_userdata( $author_id );

		// Kick out if we don't have a user with that ID.
		if ( ! is_object( $user ) ) return;

		// Access plugin.
		global $commentpress_core, $post;

		// If we have the plugin enabled. and it's BuddyPress.
		if ( is_object( $post ) AND is_object( $commentpress_core ) AND $commentpress_core->is_buddypress() ) {

			// Construct user link.
			$author = bp_core_get_userlink( $user->ID );

		} else {

			// Link to theme's author page.
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



	/**
	 * Print the posts and their comment count in a list format.
	 *
	 * @since 3.4
	 */
	public function list_pages( $exclude_pages = [] ) {

		// Test for custom menu.
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

		// Get welcome page ID.
		$welcome_id = $this->db->option_get( 'cp_welcome_page' );

		// Get front page.
		$page_on_front = $this->db->option_wp_get( 'page_on_front' );

		// Print link to title page, if we have one and it's the front page.
		if ( $welcome_id !== false AND $page_on_front == $welcome_id ) {

			// Define title page.
			$title_page_title = get_the_title( $welcome_id );

			// Allow overrides.
			$title_page_title = apply_filters( 'cp_title_page_title', $title_page_title );

			// Set current item class if viewing front page.
			$is_active = '';
			if ( is_front_page() ) {
				$is_active = ' current_page_item';
			}

			// Echo list item.
			echo '<li class="page_item page-item-' . $welcome_id . $is_active .'"><a href="' . get_permalink( $welcome_id ) . '">' . $title_page_title . '</a></li>';

		}

		// Get page display option.
		//$depth = $this->db->option_get( 'cp_show_subpages' );

		// ALWAYS write subpages into page, even if they aren't displayed.
		$depth = 0;

		// Get pages to exclude.
		$exclude = $this->db->option_get( 'cp_special_pages' );

		// Do we have any?
		if ( ! $exclude ) { $exclude = []; }

		// Exclude title page, if we have one.
		if ( $welcome_id !== false ) { $exclude[] = $welcome_id; }

		// Did we get any passed to us?
		if ( ! empty( $exclude_pages ) ) {

			// Merge arrays.
			$exclude = array_merge( $exclude, $exclude_pages );

		}

		// Set list pages defaults.
		$defaults = [
			'depth' => $depth,
			'show_date' => '',
			'date_format' => $this->db->option_get( 'date_format' ),
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
	 * Get the block comment icon.
	 *
	 * @since 3.4
	 *
	 * @param int $comment_count The number of comments.
	 * @param str $text_signature The comment text signature.
	 * @param str $block_type Either 'auto', 'line' or 'block'.
	 * @param int $para_num Sequential commentable block number.
	 * @return str $comment_icon The comment icon formatted as HTML.
	 */
	public function get_comment_icon( $comment_count, $text_signature, $block_type = 'auto', $para_num = 1 ) {

		// Reset icon.
		$icon = null;

		// If we have no comments.
		if( $comment_count == 0 ) {

			// Show add comment icon.
			$icon = 'comment_add.png';
			$class = ' no_comments';

		} elseif( $comment_count > 0 ) {

			// Show comments present icon.
			$icon = 'comment.png';
			$class = ' has_comments';

		}

		// Define block title by block type.
		switch ( $block_type ) {

			// ----------------------------
			// Auto-formatted.
			// ----------------------------
			case 'auto':
			default:

				// Define title text.
				$title_text = sprintf(
					_n( 'There is %d comment written for this %s', 'There are %d comments written for this %s', $comment_count, 'commentpress-core' ),
					$comment_count,
					$this->parent_obj->parser->lexia_get()
				);

				// Define add comment text.
				$add_text = sprintf(
					__( 'Leave a comment on %s %d', 'commentpress-core' ),
					$this->parent_obj->parser->lexia_get(),
					$para_num
				);

				break;

			// ----------------------------
			// Line-by-line, eg poetry.
			// ----------------------------
			case 'line':

				// Define title text.
				$title_text = sprintf(
					_n( 'There is %d comment written for this %s', 'There are %d comments written for this %s', $comment_count, 'commentpress-core' ),
					$comment_count,
					$this->parent_obj->parser->lexia_get()
				);

				// Define add comment text.
				$add_text = sprintf(
					__( 'Leave a comment on %s %d', 'commentpress-core' ),
					$this->parent_obj->parser->lexia_get(),
					$para_num
				);

				break;


			// ----------------------------
			// Comment-blocks.
			// ----------------------------
			case 'block':

				// Define title text.
				$title_text = sprintf(
					_n( 'There is %d comment written for this %s', 'There are %d comments written for this %s', $comment_count, 'commentpress-core' ),
					$comment_count,
					$this->parent_obj->parser->lexia_get()
				);

				// Define add comment text.
				$add_text = sprintf(
					__( 'Leave a comment on %s %d', 'commentpress-core' ),
					$this->parent_obj->parser->lexia_get(),
					$para_num
				);

				break;

		}

		// Define small.
		$small = '<small class="comment_count" title="' . $title_text . '">' . (string) $comment_count . '</small>';

		// Define HTML for comment icon.
		$comment_icon = '<span class="commenticonbox"><a class="para_permalink' . $class . '" href="#' . $text_signature . '" title="' . $add_text . '">' . $add_text . '</a> ' . $small . '</span>' . "\n";

		// --<
		return $comment_icon;

	}



	/**
	 * Get the block paragraph icon.
	 *
	 * @since 3.4
	 *
	 * @param int $comment_count The number of comments.
	 * @param str $text_signature The comment text signature.
	 * @param str $block_type Either 'auto', 'line' or 'block'.
	 * @param int $para_num The sequential commentable block number.
	 * @return str $paragraph_icon The paragraph icon formatted as HTML.
	 */
	public function get_paragraph_icon( $comment_count, $text_signature, $block_type = 'auto', $para_num = 1 ) {

		// Define block title by block type.
		switch ( $block_type ) {

			// ----------------------------
			// Auto-formatted.
			// ----------------------------
			case 'auto':
			default:

				// Define permalink text.
				$permalink_text = sprintf(
					__( 'Permalink for %s %d', 'commentpress-core' ),
					$this->parent_obj->parser->lexia_get(),
					$para_num
				);

				// Define paragraph marker.
				$para_marker = '<span class="para_marker"><a class="textblock_permalink" id="' . $text_signature . '" href="#' . $text_signature . '" title="' . $permalink_text . '">&para; <span>' . (string) $para_num . '</span></a></span>';

				break;

			// ----------------------------
			// Line-by-line, eg poetry.
			// ----------------------------
			case 'line':

				// Define permalink text.
				$permalink_text = sprintf(
					__( 'Permalink for %s %d', 'commentpress-core' ),
					$this->parent_obj->parser->lexia_get(),
					$para_num
				);

				// Define paragraph marker.
				$para_marker = '<span class="para_marker"><a class="textblock_permalink" id="' . $text_signature . '" href="#' . $text_signature . '" title="' . $permalink_text . '">&para; <span>' . (string) $para_num . '</span></a></span>';

				break;


			// ----------------------------
			// Comment-blocks.
			// ----------------------------
			case 'block':

				// Define permalink text.
				$permalink_text = sprintf(
					__( 'Permalink for %s %d', 'commentpress-core' ),
					$this->parent_obj->parser->lexia_get(),
					$para_num
				);

				// Define paragraph marker.
				$para_marker = '<span class="para_marker"><a class="textblock_permalink" id="' . $text_signature . '" href="#' . $text_signature . '" title="' . $permalink_text . '">&para; <span>' . (string) $para_num . '</span></a></span>';

				break;

		}

		// Define HTML for paragraph icon.
		$paragraph_icon = $para_marker . "\n";

		// --<
		return $paragraph_icon;

	}



	/**
	 * Get the content comment icon tag.
	 *
	 * @since 3.4
	 *
	 * @param str $text_signature The comment text signature.
	 * @param str $commenticon The comment icon.
	 * @param str $tag The tag.
	 * @param str $start The ordered list start value.
	 * @return str $para_tag The tag formatted as HTML.
	 */
	public function get_para_tag( $text_signature, $commenticon, $tag = 'p', $start = 0 ) {

		// Return different stuff for different tags.
		switch( $tag ) {

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

			// Compat with WP Footnotes.
			case 'ol class="footnotes"':

				// Define list tag
				$para_tag = '<ol class="footnotes textblock" id="textblock-' . $text_signature . '" start="0">' .
							'<li class="list_commenticon">' . $commenticon . '</li>';
				break;

			// Compat with WP Footnotes.
			case ( substr( $tag, 0 , 10 ) == 'ol start="' ):

				// Define list tag.
				$para_tag = '<ol class="textblock" id="textblock-' . $text_signature . '" start="' . ($start - 1) . '">' .
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
	 * Get the text signature input for the comment form.
	 *
	 * @since 3.4
	 *
	 * @param str $text_sig The comment text signature.
	 * @return str $input The HTML input element.
	 */
	public function get_signature_input( $text_sig = '' ) {

		// Define input tag.
		$input = '<input type="hidden" name="text_signature" value="' . $text_sig . '" id="text_signature" />';

		// --<
		return $input;

	}



	/**
	 * Get the minimise all button.
	 *
	 * @since 3.4
	 *
	 * @param str $sidebar The type of sidebar (comments, toc, activity).
	 * @return str $tag The tag.
	 */
	public function get_minimise_all_button( $sidebar = 'comments' ) {

		switch( $sidebar ) {

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
		$link = '<li><a href="#" id="btn_header_min" class="css_btn" title="' . __( 'Minimise Header', 'commentpress-core' ) . '">' . __( 'Minimise Header', 'commentpress-core' ) . '</a></li>' . "\n";

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

		// Init html
		$html = '';

		// Do we have an image?
		if ( $src != '' ) {

			// Construct link.
			$html .= '<img src="' . $src . '" />';

		}

		// Do we have one?
		if ( $url != '' ) {

			// Construct link around image.
			$html .= '<a href="' . $url . '">' . $html . '</a>';

		}

		// --<
		return $html;

	}



	/**
	 * Got the WordPress admin page.
	 *
	 * @since 3.4
	 *
	 * @return str $admin_page The HTML for the admin page.
	 */
	public function get_admin_page() {

		// Init.
		$admin_page = '';

		// Open div.
		$admin_page .= '<div class="wrap" id="commentpress_admin_wrapper">' . "\n\n";

		// Get our form.
		$admin_page .= $this->_get_admin_form();

		// Close div.
		$admin_page .= '</div>' . "\n\n";

		// --<
		return $admin_page;

	}



	/**
	 * Allow all CommentPress parent themes in Multisite optional scenario.
	 *
	 * @since 3.9.14
	 *
	 * @param array $retval The existing array of allowed themes.
	 * @return array $retval The modified array of allowed themes.
	 */
	public function allowed_themes( $retval ) {

		// Allow all parent themes.
		$retval['commentpress-flat'] = 1;
		$retval['commentpress-modern'] = 1;
		$retval['commentpress-theme'] = 1;

		// --<
		return $retval;

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Private Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Object initialisation.
	 *
	 * @since 3.0
	 */
	public function _init() {

		/**
		 * Moved mobile checks to class_commentpress_db.php so it only loads as
		 * needed and so that it loads *after* the old CommentPress loads it.
		 */

		// Register hooks after parent class has done its thing.
		add_action( 'commentpress_after_hooks', [ $this, '_register_hooks' ] );

	}



	/**
	 * Register hooks.
	 *
	 * @since 3.9.14
	 */
	public function _register_hooks() {

		// Enable CommentPress themes in Multisite optional scenario.
		add_filter( 'network_allowed_themes', [ $this, 'allowed_themes' ] );

	}



	/**
	 * Returns the admin form HTML.
	 *
	 * @since 3.4
	 *
	 * @return str $admin_page The admin page HTML.
	 */
	public function _get_admin_form() {

		// Sanitise admin page URL.
		$url = $_SERVER['REQUEST_URI'];
		$url_array = explode( '&', $url );
		if ( $url_array ) { $url = $url_array[0]; }

		// If we need to upgrade.
		if ( $this->db->upgrade_required() ) {

			// Get upgrade options.
			$upgrade = $this->_get_upgrade();

			// Init text.
			$options_text = '';

			// If there are options.
			if ( $upgrade != '' ) {
				$options_text = __( ' The following options have become available in the new version.', 'commentpress-core' );
			}

			// Define admin page.
			$admin_page = '
			<h1>' . __( 'CommentPress Core Upgrade', 'commentpress-core' ) . '</h1>



			<form method="post" action="' . htmlentities($url . '&updated=true') . '">

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

		} else {

			// Define admin page.
			$admin_page = '
			<h1>' . __( 'CommentPress Core Settings', 'commentpress-core' ) . '</h1>

			<form method="post" action="' . htmlentities($url . '&updated=true') . '">

			' . wp_nonce_field( 'commentpress_admin_action', 'commentpress_nonce', true, false ) . '
			' . wp_referer_field( false ) . '

			' .

			$this->_get_options() .

			'<input type="hidden" name="action" value="update" />

			' . $this->_get_submit() . '

			</form>' . "\n\n\n\n";

		}

		// --<
		return $admin_page;

	}



	/**
	 * Returns the CommentPress Core options for the admin form.
	 *
	 * @since 3.4
	 *
	 * @return str $options The options markup.
	 */
	public function _get_options() {

		// Define CommentPress Core theme options.
		$options = '
		<p>' . __( 'When a supplied CommentPress theme (or a valid CommentPress child theme) is active, the following options modify its behaviour.', 'commentpress-core' ) . '</p>



		<hr />

		<h3>' . __( 'Global Options', 'commentpress-core' ) . '</h3>

		<table class="form-table">

		' . $this->_get_deactivate() . '

		' . $this->_get_reset() . '

		' . $this->_get_post_type_options() . '

		' . $this->_get_optional_options() . '

		' . $this->_get_do_not_parse() . '

		</table>



		<hr />

		<h3>' . __( 'Table of Contents', 'commentpress-core' ) . '</h3>

		<p>' . __( 'Choose how you want your Table of Contents to appear and function.<br />
		<strong style="color: red;">NOTE!</strong> When Chapters are Pages, the TOC will always show Sub-Pages, since collapsing the TOC makes no sense in that situation.', 'commentpress-core' ) . '</p>

		<table class="form-table">

		' . $this->_get_toc() . '

		</table>



		<hr />

		<h3>' . __( 'Page Display Options', 'commentpress-core' ) . '</h3>

		<table class="form-table">

			<tr valign="top">
				<th scope="row"><label for="cp_featured_images">' . __( 'Enable Featured Images (Note: if you have already implemented this in a child theme, you should choose "No")', 'commentpress-core' ) . '</label></th>
				<td><select id="cp_featured_images" name="cp_featured_images">
						<option value="y" ' . (($this->db->option_get('cp_featured_images', 'n') == 'y') ? ' selected="selected"' : '') . '>' . __( 'Yes', 'commentpress-core' ) . '</option>
						<option value="n" ' . (($this->db->option_get('cp_featured_images', 'n') == 'n') ? ' selected="selected"' : '') . '>' . __( 'No', 'commentpress-core' ) . '</option>
					</select>
				</td>
			</tr>

		' . $this->_get_page_nav_enabled() . '

			<tr valign="top">
				<th scope="row"><label for="cp_title_visibility">' . __( 'Default page title visibility (can be overridden on individual pages)', 'commentpress-core' ) . '</label></th>
				<td><select id="cp_title_visibility" name="cp_title_visibility">
						<option value="show" ' . (($this->db->option_get('cp_title_visibility') == 'show') ? ' selected="selected"' : '') . '>' . __( 'Show page titles', 'commentpress-core' ) . '</option>
						<option value="hide" ' . (($this->db->option_get('cp_title_visibility') == 'hide') ? ' selected="selected"' : '') . '>' . __( 'Hide page titles', 'commentpress-core' ) . '</option>
					</select>
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="cp_page_meta_visibility">' . __( 'Default page meta visibility (can be overridden on individual pages)', 'commentpress-core' ) . '</label></th>
				<td><select id="cp_page_meta_visibility" name="cp_page_meta_visibility">
						<option value="show" ' . (($this->db->option_get('cp_page_meta_visibility') == 'show') ? ' selected="selected"' : '') . '>' . __( 'Show page meta', 'commentpress-core' ) . '</option>
						<option value="hide" ' . (($this->db->option_get('cp_page_meta_visibility') == 'hide') ? ' selected="selected"' : '') . '>' . __( 'Hide page meta', 'commentpress-core' ) . '</option>
					</select>
				</td>
			</tr>

		' . $this->_get_textblock_meta() . '

			<tr valign="top">
				<th scope="row"><label for="cp_excerpt_length">' . __( 'Blog excerpt length', 'commentpress-core' ) . '</label></th>
				<td><input type="text" id="cp_excerpt_length" name="cp_excerpt_length" value="' . $this->db->option_get('cp_excerpt_length') . '" class="small-text" /> ' . __( 'words', 'commentpress-core' ) . '</td>
			</tr>

		</table>



		<hr />

		<h3>' . __( 'Commenting Options', 'commentpress-core' ) . '</h3>

		<table class="form-table">

		' . $this->_get_editor() . '

		' . $this->_get_override() . '

		</table>



		<hr />

		<h3>' . __( 'Theme Customisation', 'commentpress-core' ) . '</h3>

		<p>' . __( 'You can set a custom background colour in <em>Appearance &#8594; Background</em>.<br />
		You can also set a custom header image and header text colour in <em>Appearance &#8594; Header</em>.<br />
		Below are extra options for changing how the theme functions.', 'commentpress-core' ) . '</p>

		<table class="form-table">

			<tr valign="top">
				<th scope="row"><label for="cp_js_scroll_speed">' . __( 'Scroll speed', 'commentpress-core' ) . '</label></th>
				<td><input type="text" id="cp_js_scroll_speed" name="cp_js_scroll_speed" value="' . $this->db->option_get('cp_js_scroll_speed') . '" class="small-text" /> ' . __( 'milliseconds', 'commentpress-core' ) . '</td>
			</tr>

			<tr valign="top">
				<th scope="row"><label for="cp_min_page_width">' . __( 'Minimum page width', 'commentpress-core' ) . '</label></th>
				<td><input type="text" id="cp_min_page_width" name="cp_min_page_width" value="' . $this->db->option_get('cp_min_page_width') . '" class="small-text" /> ' . __( 'pixels', 'commentpress-core' ) . '</td>
			</tr>

		' . $this->_get_sidebar() . '

		' . apply_filters( 'commentpress_theme_customisation_options', '' ) . '

		</table>



		';

		// Allow plugins to append form elements.
		return apply_filters( 'commentpress_admin_page_options', $options );

	}



	/**
	 * Returns optional options, if defined.
	 *
	 * @since 3.4
	 *
	 * @return str $html The markup.
	 */
	public function _get_optional_options() {

		// Init.
		$html = '';

		// Do we have the option to choose blog type (new in 3.3.1)?
		if ( $this->db->option_exists( 'cp_blog_type' ) ) {

			// Define no types.
			$types = [];

			// Allow overrides.
			$types = apply_filters( 'cp_blog_type_options', $types );

			// If we get some from a plugin, say.
			if ( ! empty( $types ) ) {

				// Define title.
				$type_title = __( 'Default Text Format', 'commentpress-core' );

				// Allow overrides.
				$type_title = apply_filters( 'cp_blog_type_label', $type_title );

				// Add extra message.
				$type_title .= __( ' (can be overridden on individual pages)', 'commentpress-core' );

				// Construct options.
				$type_option_list = [];
				$n = 0;

				// Get existing.
				$blog_type = $this->db->option_get( 'cp_blog_type' );

				foreach( $types AS $type ) {
					if ( $n == $blog_type ) {
						$type_option_list[] = '<option value="' . $n . '" selected="selected">' . $type . '</option>';
					} else {
						$type_option_list[] = '<option value="' . $n . '">' . $type . '</option>';
					}
					$n++;
				}
				$type_options = implode( "\n", $type_option_list );

				// Define upgrade.
				$html .= '
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

		// Do we have the option to choose blog workflow (new in 3.3.1)?
		if ( $this->db->option_exists( 'cp_blog_workflow' ) ) {

			// Off by default.
			$has_workflow = false;

			// Allow overrides.
			$has_workflow = apply_filters( 'cp_blog_workflow_exists', $has_workflow );

			// If we have workflow enabled, by a plugin, say.
			if ( $has_workflow !== false ) {

				// Define label.
				$workflow_label = __( 'Enable Custom Workflow', 'commentpress-core' );

				// Define label.
				$workflow_label = apply_filters( 'cp_blog_workflow_label', $workflow_label );

				// Add extra message.
				$workflow_label .= ' (Not recommended because it is still very experimental)';

				// Define upgrade.
				$html .= '
				<tr valign="top">
					<th scope="row"><label for="cp_blog_workflow">' . $workflow_label . '</label></th>
					<td><input id="cp_blog_workflow" name="cp_blog_workflow" value="1" type="checkbox" ' . ( $this->db->option_get('cp_blog_workflow') ? ' checked="checked"' : ''  ) . ' /></td>

				</tr>

				';

			}

		}



		// TODO add infinite scroll switch when ready.



		// --<
		return $html;

	}



	/**
	 * Returns the upgrade details for the admin form.
	 *
	 * @since 3.4
	 *
	 * @return str $upgrade The upgrade markup.
	 */
	public function _get_upgrade() {

		// Init.
		$upgrade = '';

		// Do we have the option to choose which post types to skip (new in 3.9)?
		if ( ! $this->db->option_exists( 'cp_post_types_disabled' ) ) {

			// Define labels.
			$description = __( 'Choose the Post Types on which CommentPress Core is enabled. Disabling a post type will mean that paragraph-level commenting will not be enabled on any entries of that post type. Default prior to 3.9 was that all post types were enabled.', 'commentpress-core' );
			$label = __( 'Post Types on which CommentPress Core is enabled.', 'commentpress-core' );

			// Get post types that support the editor.
			$capable_post_types = $this->db->get_supported_post_types();

			// Init outputs.
			$output = [];
			$options = '';

			// Sanity check.
			if ( count( $capable_post_types ) > 0 ) {

				// Construct checkbox for each post type.
				foreach( $capable_post_types AS $post_type => $label ) {

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
		if ( ! $this->db->option_exists( 'cp_do_not_parse' ) ) {

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

		// Do we have the option to choose to disable page navigation (new in 3.8.10)?
		if ( ! $this->db->option_exists( 'cp_page_nav_enabled' ) ) {

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
		if ( ! $this->db->option_exists( 'cp_textblock_meta' ) ) {

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
		if ( ! $this->db->option_exists( 'cp_featured_images' ) ) {

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
		if ( ! $this->db->option_exists( 'cp_sidebar_default' ) ) {

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

		// Do we have the option to show or hide page meta (new in 3.3.2)?
		if ( ! $this->db->option_exists( 'cp_page_meta_visibility' ) ) {

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

		// Do we have the option to choose blog type (new in 3.3.1)?
		if ( ! $this->db->option_exists( 'cp_blog_type' ) ) {

			// Define no types.
			$types = [];

			// Allow overrides.
			$types = apply_filters( 'cp_blog_type_options', $types );

			// If we get some from a plugin, say.
			if ( ! empty( $types ) ) {

				// Define title.
				$type_title = __( 'Blog Type', 'commentpress-core' );

				// Allow overrides.
				$type_title = apply_filters( 'cp_blog_type_label', $type_title );

				// Construct options.
				$type_option_list = [];
				$n = 0;
				foreach( $types AS $type ) {
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

		// Do we have the option to choose blog workflow (new in 3.3.1)?
		if ( ! $this->db->option_exists( 'cp_blog_workflow' ) ) {

			// Off by default.
			$has_workflow = false;

			// Allow overrides.
			$has_workflow = apply_filters( 'cp_blog_workflow_exists', $has_workflow );

			// If we have workflow enabled, by a plugin, say.
			if ( $has_workflow !== false ) {

				// Define label.
				$workflow_label = __( 'Enable Custom Workflow', 'commentpress-core' );

				// Define label.
				$workflow_label = apply_filters( 'cp_blog_workflow_label', $workflow_label );

				// Define upgrade.
				$upgrade .= '
				<tr valign="top">
					<th scope="row"><label for="cp_blog_workflow">' . $workflow_label . '</label></th>
					<td><input id="cp_blog_workflow" name="cp_blog_workflow" value="1" type="checkbox" /></td>
				</tr>

				';

			}

		}

		// Do we have the option to choose the TOC layout (new in 3.3)?
		if ( ! $this->db->option_exists( 'cp_show_extended_toc' ) ) {

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

		// Do we have the option to set the comment editor?
		if ( ! $this->db->option_exists( 'cp_comment_editor' ) ) {

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
		if ( ! $this->db->option_exists( 'cp_promote_reading' ) ) {

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
		if ( ! $this->db->option_exists( 'cp_title_visibility' ) ) {

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
		if ( ! $this->db->option_exists( 'cp_js_scroll_speed' ) ) {

			// Define labels.
			$scroll_label = __( 'Scroll speed', 'commentpress-core' );
			$scroll_ms_label = __( 'milliseconds', 'commentpress-core' );

			// Define upgrade.
			$upgrade .= '
			<tr valign="top">
				<th scope="row"><label for="cp_js_scroll_speed">' . $scroll_label . '</label></th>
				<td><input type="text" id="cp_js_scroll_speed" name="cp_js_scroll_speed" value="' . $this->db->js_scroll_speed . '" class="small-text" /> ' . $scroll_ms_label . '</td>
			</tr>

			';

		}

		// Do we have the option to set the minimum page width?
		if ( ! $this->db->option_exists( 'cp_min_page_width' ) ) {

			// Define labels.
			$min_label = __( 'Minimum page width', 'commentpress-core' );
			$min_pix_label = __( 'pixels', 'commentpress-core' );

			// Define upgrade.
			$upgrade .= '
			<tr valign="top">
				<th scope="row"><label for="cp_min_page_width"></label></th>
				<td><input type="text" id="cp_min_page_width" name="cp_min_page_width" value="' . $this->db->min_page_width . '" class="small-text" /> ' . $min_pix_label . '</td>
			</tr>

			';

		}

		// --<
		return $upgrade;

	}



	/**
	 * Returns the multisite deactivate button for the admin form.
	 *
	 * @since 3.4
	 *
	 * @return str $html The multisite markup, default is empty.
	 */
	public function _get_deactivate() {

		// Do this via a filter, so only the Multisite object returns anything.
		return apply_filters( 'cpmu_deactivate_commentpress_element', '' );

	}



	/**
	 * Returns the reset button for the admin form.
	 *
	 * @since 3.4
	 *
	 * @return str $reset The reset button markup.
	 */
	public function _get_reset() {

		// Define label.
		$label = __( 'Reset options to plugin defaults', 'commentpress-core' );

		// Define reset.
		$reset = '
		<tr valign="top">
			<th scope="row"><label for="cp_reset">' . $label . '</label></th>
			<td><input id="cp_reset" name="cp_reset" value="1" type="checkbox" /></td>
		</tr>
		';

		// --<
		return $reset;

	}



	/**
	 * Returns the Rich Text Editor button for the admin form.
	 *
	 * @since 3.4
	 *
	 * @return str $editor The editor option markup.
	 */
	public function _get_editor() {

		// Define labels.
		$editor_label = __( 'Comment form editor', 'commentpress-core' );
		$rich_label = __( 'Rich-text Editor', 'commentpress-core' );
		$plain_label = __( 'Plain-text Editor', 'commentpress-core' );

		$behaviour_label = __( 'Default comment form behaviour', 'commentpress-core' );
		$reading_label = __( 'Promote reading', 'commentpress-core' );
		$commenting_label = __( 'Promote commenting', 'commentpress-core' );

		// Define editor.
		$editor = '
		<tr valign="top">
			<th scope="row"><label for="cp_comment_editor">' . $editor_label . '</label></th>
			<td><select id="cp_comment_editor" name="cp_comment_editor">
					<option value="1" ' . (($this->db->option_get('cp_comment_editor') == '1') ? ' selected="selected"' : '') . '>' . $rich_label . '</option>
					<option value="0" ' . (($this->db->option_get('cp_comment_editor') == '0') ? ' selected="selected"' : '') . '>' . $plain_label . '</option>
				</select>
			</td>
		</tr>

		<tr valign="top">
			<th scope="row"><label for="cp_promote_reading">' . $behaviour_label . '</label></th>
			<td><select id="cp_promote_reading" name="cp_promote_reading">
					<option value="1" ' . (($this->db->option_get('cp_promote_reading') == '1') ? ' selected="selected"' : '') . '>' . $reading_label . '</option>
					<option value="0" ' . (($this->db->option_get('cp_promote_reading') == '0') ? ' selected="selected"' : '') . '>' . $commenting_label . '</option>
				</select>
			</td>
		</tr>
		';

		// --<
		return $editor;

	}



	/**
	 * Returns the TOC options for the admin form.
	 *
	 * @since 3.4
	 *
	 * @return str $editor The markup.
	 */
	public function _get_toc() {

		// Define labels.
		$toc_label = __( 'Table of Contents contains', 'commentpress-core' );
		$posts_label = __( 'Posts', 'commentpress-core' );
		$pages_label = __( 'Pages', 'commentpress-core' );

		$chapter_label = __( 'Chapters are', 'commentpress-core' );
		$chapter_pages_label = __( 'Pages', 'commentpress-core' );
		$chapter_headings_label = __( 'Headings', 'commentpress-core' );

		$sub_pages_label = __( 'Show Sub-Pages', 'commentpress-core' );

		$extended_label = __( 'Appearance of TOC for posts', 'commentpress-core' );
		$extended_info_label = __( 'Extended information', 'commentpress-core' );
		$extended_title_label = __( 'Just the title', 'commentpress-core' );

		// Define table of contents options.
		$toc = '
		<tr valign="top">
			<th scope="row"><label for="cp_show_posts_or_pages_in_toc">' . $toc_label . '</label></th>
			<td><select id="cp_show_posts_or_pages_in_toc" name="cp_show_posts_or_pages_in_toc">
					<option value="post" ' . (($this->db->option_get('cp_show_posts_or_pages_in_toc') == 'post') ? ' selected="selected"' : '') . '>' . $posts_label . '</option>
					<option value="page" ' . (($this->db->option_get('cp_show_posts_or_pages_in_toc') == 'page') ? ' selected="selected"' : '') . '>' . $pages_label . '</option>
				</select>
			</td>
		</tr>

		' . (($this->db->option_get('cp_show_posts_or_pages_in_toc') == 'page') ? '
		<tr valign="top">
			<th scope="row"><label for="cp_toc_chapter_is_page">' . $chapter_label . '</label></th>
			<td><select id="cp_toc_chapter_is_page" name="cp_toc_chapter_is_page">
					<option value="1" ' . (($this->db->option_get('cp_toc_chapter_is_page') == '1') ? ' selected="selected"' : '') . '>' . $chapter_pages_label . '</option>
					<option value="0" ' . (($this->db->option_get('cp_toc_chapter_is_page') == '0') ? ' selected="selected"' : '') . '>' . $chapter_headings_label . '</option>
				</select>
			</td>
		</tr>' : '' ) . '

		' . (($this->db->option_get('cp_show_posts_or_pages_in_toc') == 'page' AND $this->db->option_get('cp_toc_chapter_is_page') == '0') ? '
		<tr valign="top">
			<th scope="row"><label for="cp_show_subpages">' . $sub_pages_label . '</label></th>
			<td><input id="cp_show_subpages" name="cp_show_subpages" value="1"  type="checkbox" ' . ( $this->db->option_get('cp_show_subpages') ? ' checked="checked"' : ''  ) . ' /></td>
		</tr>' : '' ) . '


		<tr valign="top">
			<th scope="row"><label for="cp_show_extended_toc">' . $extended_label . '</label></th>
			<td><select id="cp_show_extended_toc" name="cp_show_extended_toc">
					<option value="1" ' . (($this->db->option_get('cp_show_extended_toc') == '1') ? ' selected="selected"' : '') . '>' . $extended_info_label . '</option>
					<option value="0" ' . (($this->db->option_get('cp_show_extended_toc') == '0') ? ' selected="selected"' : '') . '>' . $extended_title_label . '</option>
				</select>
			</td>
		</tr>
		';

		// --<
		return $toc;

	}



	/**
	 * Returns the Sidebar options for the admin form.
	 *
	 * @since 3.4
	 *
	 * @return str $toc The markup.
	 */
	public function _get_sidebar() {

		// Allow this to be disabled.
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) return;

		// Define labels.
		$label = __( 'Which sidebar do you want to be active by default? (can be overridden on individual pages)', 'commentpress-core' );
		$contents_label = __( 'Contents', 'commentpress-core' );
		$activity_label = __( 'Activity', 'commentpress-core' );
		$comments_label = __( 'Comments', 'commentpress-core' );

		// Get option (but if we haven't got a value, use comments)
		$default = $this->db->option_get( 'cp_sidebar_default', 'comments' );

		// Define table of contents options.
		$toc = '
		<tr valign="top">
			<th scope="row"><label for="cp_sidebar_default">' . $label . '</label></th>
			<td><select id="cp_sidebar_default" name="cp_sidebar_default">
					<option value="toc" ' . (($default == 'contents') ? ' selected="selected"' : '') . '>' . $contents_label . '</option>
					<option value="activity" ' . (($default == 'activity') ? ' selected="selected"' : '') . '>' . $activity_label . '</option>
					<option value="comments" ' . (($default == 'comments') ? ' selected="selected"' : '') . '>' . $comments_label . '</option>
				</select>
			</td>
		</tr>

		';

		// --<
		return $toc;

	}



	/**
	 * Returns the override paragraph commenting button for the admin form.
	 *
	 * @since 3.4
	 *
	 * @return str $override The markup.
	 */
	public function _get_override() {

		// Define label.
		$label = __( 'Enable "live" comment refreshing (Please note: may cause heavy load on your server)', 'commentpress-core' );

		// Define override.
		$override = '
		<tr valign="top">
			<th scope="row"><label for="cp_para_comments_live">' . $label . '</label></th>
			<td><input id="cp_para_comments_live" name="cp_para_comments_live" value="1" type="checkbox" ' . ( ($this->db->option_get('cp_para_comments_live') == '1') ? ' checked="checked"' : ''  ) . ' /></td>
		</tr>
		';

		// --<
		return $override;

	}



	/**
	 * Returns the disable parsing section for the admin form.
	 *
	 * @since 3.8.10
	 *
	 * @return str $html The markup for the button.
	 */
	public function _get_do_not_parse() {

			$description = __( 'Note: when comments are closed on an entry and there are no comments on that entry, if this option is set to "Yes" then the content will not be parsed for paragraphs, lines or blocks. Comments will also not be parsed, meaning that the entry behaves the same as content which is not commentable. Default prior to 3.8.10 was "No" - all content was always parsed.', 'commentpress-core' );

		// Define override.
		$html = '
		<tr valign="top">
			<th scope="row"><label for="cp_do_not_parse">' . __( 'Disable CommentPress on entries with no comments. (can be overridden on individual entries)', 'commentpress-core' ) . '</label></th>
			<td><select id="cp_do_not_parse" name="cp_do_not_parse">
					<option value="y" ' . (($this->db->option_get('cp_do_not_parse', 'n') == 'y') ? ' selected="selected"' : '') . '>' . __( 'Yes', 'commentpress-core' ) . '</option>
					<option value="n" ' . (($this->db->option_get('cp_do_not_parse', 'n') == 'n') ? ' selected="selected"' : '') . '>' . __( 'No', 'commentpress-core' ) . '</option>
				</select>
				<p>' . $description . '</p>
			</td>
		</tr>

		';

		// --<
		return $html;

	}



	/**
	 * Returns the page navigation enabled button for the admin form.
	 *
	 * @since 3.8.10
	 *
	 * @return str $html The markup for the button.
	 */
	public function _get_page_nav_enabled() {

		// Define override.
		$html = '
		<tr valign="top">
			<th scope="row"><label for="cp_page_nav_enabled">' . __( 'Enable automatic page navigation (controls appearance of page numbering and navigation arrows on hierarchical pages)', 'commentpress-core' ) . '</label></th>
			<td><select id="cp_page_nav_enabled" name="cp_page_nav_enabled">
					<option value="y" ' . (($this->db->option_get('cp_page_nav_enabled', 'y') == 'y') ? ' selected="selected"' : '') . '>' . __( 'Yes', 'commentpress-core' ) . '</option>
					<option value="n" ' . (($this->db->option_get('cp_page_nav_enabled', 'y') == 'n') ? ' selected="selected"' : '') . '>' . __( 'No', 'commentpress-core' ) . '</option>
				</select>
			</td>
		</tr>

		';

		// --<
		return $html;

	}



	/**
	 * Get post type options.
	 *
	 * @since 3.9
	 *
	 * @return str $html The markup for the post type options.
	 */
	public function _get_post_type_options() {

		// Get post types that support the editor
		$capable_post_types = $this->db->get_supported_post_types();

		// Init outputs.
		$output = [];
		$options = '';

		// Get chosen post types.
		$selected_types = $this->db->option_get( 'cp_post_types_disabled', [] );

		// Sanity check.
		if ( count( $capable_post_types ) > 0 ) {

			// Construct checkbox for each post type.
			foreach( $capable_post_types AS $post_type => $label ) {

				$checked = '';
				if ( ! in_array( $post_type, $selected_types ) ) $checked = ' checked="checked"';

				// Add checkbox.
				$output[] = '<input type="checkbox" class="settings-checkbox" name="cp_post_types_enabled[]" value="' . $post_type . '"' . $checked . ' /> <label class="commentpress_settings_label" for="cp_post_types_enabled">' . $label . '</label><br>';

			}

			// Implode.
			$options = implode( "\n", $output );

		}

		// Construct option.
		$html = '
		<tr valign="top">
			<th scope="row"><label for="cp_post_types_enabled">' . __( 'Post Types on which CommentPress Core is enabled', 'commentpress-core' ) . '</label></th>
			<td>
				<p>' . $options . '</p>
			</td>
		</tr>

		';

		// --<
		return $html;

	}



	/**
	 * Returns the textblock meta button for the admin form.
	 *
	 * @since 3.4
	 *
	 * @return str $override The markup.
	 */
	public function _get_textblock_meta() {

		// Define override.
		$override = '
		<tr valign="top">
			<th scope="row"><label for="cp_textblock_meta">' . __( 'Show paragraph meta (Number and Comment Icon)', 'commentpress-core' ) . '</label></th>
			<td><select id="cp_textblock_meta" name="cp_textblock_meta">
					<option value="y" ' . (($this->db->option_get('cp_textblock_meta', 'y') == 'y') ? ' selected="selected"' : '') . '>' . __( 'Always', 'commentpress-core' ) . '</option>
					<option value="n" ' . (($this->db->option_get('cp_textblock_meta', 'y') == 'n') ? ' selected="selected"' : '') . '>' . __( 'On rollover', 'commentpress-core' ) . '</option>
				</select>
			</td>
		</tr>

		';

		// --<
		return $override;

	}



	/**
	 * Returns the submit button.
	 *
	 * @since 3.4
	 *
	 * @return str $submit The submit button HTML.
	 */
	public function _get_submit() {

		// Define label.
		$label = __( 'Save Changes', 'commentpress-core' );

		// Define editor.
		$submit = '
		<p class="submit">
			<input type="submit" name="commentpress_submit" value="' . $label . '" class="button-primary" />
		</p>
		';

		// --<
		return $submit;

	}



//##############################################################################



} // Class ends.



