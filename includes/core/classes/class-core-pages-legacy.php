<?php
/**
 * CommentPress Core Legacy Pages class.
 *
 * Handles functionality related to legacy "Special Pages" in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Legacy Pages Class.
 *
 * This class provides functionality related to legacy "Special Pages" in CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Pages_Legacy {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * Constructor.
	 *
	 * @since 4.0
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

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

		// Exclude Special Pages from listings.
		add_filter( 'wp_list_pages_excludes', [ $this, 'exclude_special_pages' ], 10, 1 );
		add_filter( 'parse_query', [ $this, 'exclude_special_pages_from_admin' ], 10, 1 );

		// Modify all.
		add_filter( 'views_edit-page', [ $this, 'update_page_counts_in_admin' ], 10, 1 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Test if a Page is a Special Page.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_special_page True if a Special Page, false otherwise.
	 */
	public function is_special_page() {

		// Init flag.
		$is_special_page = false;

		// Access Post object.
		global $post;

		// Do we have one?
		if ( ! is_object( $post ) ) {

			// --<
			return $is_special_page;

		}

		// Get Special Pages.
		$special_pages = $this->core->db->option_get( 'cp_special_pages', [] );

		// Do we have a Special Page array?
		if ( is_array( $special_pages ) && count( $special_pages ) > 0 ) {

			// Is the current Page one?
			if ( in_array( $post->ID, $special_pages ) ) {

				// It is.
				$is_special_page = true;

			}

		}

		// --<
		return $is_special_page;

	}

	// -------------------------------------------------------------------------

	/**
	 * Exclude Special Pages from Page listings.
	 *
	 * @since 3.4
	 *
	 * @param array $excluded_array The existing list of excluded Pages.
	 * @return array $excluded_array The modified list of excluded Pages.
	 */
	public function exclude_special_pages( $excluded_array ) {

		// Get Special Pages array, if it's there.
		$special_pages = $this->core->db->option_get( 'cp_special_pages' );

		// Do we have an array?
		if ( is_array( $special_pages ) ) {

			// Merge and make unique.
			$excluded_array = array_unique( array_merge( $excluded_array, $special_pages ) );

		}

		// --<
		return $excluded_array;

	}

	/**
	 * Exclude Special Pages from Admin Page listings.
	 *
	 * @since 3.4
	 *
	 * @param array $query The existing Page query.
	 */
	public function exclude_special_pages_from_admin( $query ) {

		global $pagenow, $post_type;

		// Check admin location.
		if ( is_admin() && $pagenow == 'edit.php' && $post_type == 'page' ) {

			// Get Special Pages array, if it's there.
			$special_pages = $this->core->db->option_get( 'cp_special_pages' );

			// Do we have an array?
			if ( is_array( $special_pages ) && count( $special_pages ) > 0 ) {

				// Modify query.
				$query->query_vars['post__not_in'] = $special_pages;

			}

		}

	}

	/**
	 * Page counts still need amending.
	 *
	 * @since 3.4
	 *
	 * @param array $vars The existing variables.
	 * @return array $vars The modified list of variables.
	 */
	public function update_page_counts_in_admin( $vars ) {

		global $pagenow, $post_type;

		// Check admin location.
		if ( is_admin() && $pagenow == 'edit.php' && $post_type == 'page' ) {

			// Get Special Pages array, if it's there.
			$special_pages = $this->core->db->option_get( 'cp_special_pages' );

			// Do we have an array?
			if ( is_array( $special_pages ) ) {

				/**
				 * Data comes in like this:
				 *
				 * [all] => <a href='edit.php?post_type=page' class="current">All <span class="count">(8)</span></a>
				 * [publish] => <a href='edit.php?post_status=publish&amp;post_type=page'>Published <span class="count">(8)</span></a>
				 */

				// Capture existing value enclosed in brackets.
				preg_match( '/\((\d+)\)/', $vars['all'], $matches );

				// Did we get a result?
				if ( isset( $matches[1] ) ) {

					// Subtract Special Page count.
					$new_count = $matches[1] - count( $special_pages );

					// Rebuild 'all' and 'publish' items.
					$vars['all'] = preg_replace(
						'/\(\d+\)/',
						'(' . $new_count . ')',
						$vars['all']
					);

				}

				// Capture existing value enclosed in brackets.
				preg_match( '/\((\d+)\)/', $vars['publish'], $matches );

				// Did we get a result?
				if ( isset( $matches[1] ) ) {

					// Subtract Special Page count.
					$new_count = $matches[1] - count( $special_pages );

					// Rebuild 'all' and 'publish' items.
					$vars['publish'] = preg_replace(
						'/\(\d+\)/',
						'(' . $new_count . ')',
						$vars['publish']
					);

				}

			}

		}

		// --<
		return $vars;

	}

	// -------------------------------------------------------------------------

	/**
	 * Create all Special Pages.
	 *
	 * @since 3.4
	 */
	public function create_special_pages() {

		/*
		 * One of the CommentPress Core themes MUST be active or WordPress will
		 * fail to set the Page templates for the Pages that require them.
		 *
		 * Also, a User must be logged in for these Pages to be associated with them.
		 */

		// Get Special Pages array, if it's there.
		$special_pages = $this->core->db->option_get( 'cp_special_pages', [] );

		// Create Welcome/Title Page, but don't add to Special Pages.
		$welcome = $this->create_title_page();

		// Create General Comments Page.
		$special_pages[] = $this->create_general_comments_page();

		// Create All Comments Page.
		$special_pages[] = $this->create_all_comments_page();

		// Create Comments by Author Page.
		$special_pages[] = $this->create_comments_by_author_page();

		// Create Blog Page.
		$special_pages[] = $this->create_blog_page();

		// Create Blog Archive Page.
		$special_pages[] = $this->create_blog_archive_page();

		// Create TOC Page -> a convenience, let's us define a logo as attachment.
		$special_pages[] = $this->create_toc_page();

		// Store the array of Page IDs that were created.
		$this->core->db->option_set( 'cp_special_pages', $special_pages );

		// Save changes.
		$this->core->db->options_save();

	}

	/**
	 * Create a particular Special Page.
	 *
	 * @since 3.4
	 *
	 * @param str $page The type of Special Page.
	 * @return mixed $new_id If successful, the numeric ID of the new Page, false on failure.
	 */
	public function create_special_page( $page ) {

		// Init.
		$new_id = false;

		// Get Special Pages array, if it's there.
		$special_pages = $this->core->db->option_get( 'cp_special_pages', [] );

		// Switch by Page.
		switch ( $page ) {

			case 'title':

				// Create Welcome/Title Page.
				$new_id = $this->create_title_page();
				break;

			case 'general_comments':

				// Create General Comments Page.
				$new_id = $this->create_general_comments_page();
				break;

			case 'all_comments':

				// Create All Comments Page.
				$new_id = $this->create_all_comments_page();
				break;

			case 'comments_by_author':

				// Create Comments by Author Page.
				$new_id = $this->create_comments_by_author_page();
				break;

			case 'blog':

				// Create Blog Page.
				$new_id = $this->create_blog_page();
				break;

			case 'blog_archive':

				// Create Blog Page.
				$new_id = $this->create_blog_archive_page();
				break;

			case 'toc':

				// Create TOC Page.
				$new_id = $this->create_toc_page();
				break;

		}

		// Add to Special Pages.
		$special_pages[] = $new_id;

		// Reset option.
		$this->core->db->option_set( 'cp_special_pages', $special_pages );

		// Save changes.
		$this->core->db->options_save();

		// --<
		return $new_id;

	}

	/**
	 * Delete Special Pages.
	 *
	 * @since 3.4
	 *
	 * @return bool $success True if Page deleted successfully, false otherwise.
	 */
	public function delete_special_pages() {

		// Init success flag.
		$success = true;

		/*
		 * Only delete Special Pages if we have one of the CommentPress Core
		 * themes active because other themes may have a totally different way
		 * of presenting the content of the Blog.
		 */

		// Retrieve data on Special Pages.
		$special_pages = $this->core->db->option_get( 'cp_special_pages', [] );

		// If we have created any.
		if ( is_array( $special_pages ) && count( $special_pages ) > 0 ) {

			// Loop through them.
			foreach ( $special_pages as $special_page ) {

				// Bypass trash.
				$force_delete = true;

				// Try and delete each Page.
				if ( ! wp_delete_post( $special_page, $force_delete ) ) {

					// Oops, set success flag to false.
					$success = false;

				}

			}

			// Delete the corresponding options.
			$this->core->db->option_delete( 'cp_special_pages' );

			$this->core->db->option_delete( 'cp_blog_page' );
			$this->core->db->option_delete( 'cp_blog_archive_page' );
			$this->core->db->option_delete( 'cp_general_comments_page' );
			$this->core->db->option_delete( 'cp_all_comments_page' );
			$this->core->db->option_delete( 'cp_comments_by_page' );
			$this->core->db->option_delete( 'cp_toc_page' );

			/*
			// For now, keep Welcome Page - delete option when Page is deleted.
			$this->core->db->option_delete( 'cp_welcome_page' );
			*/

			// Save changes.
			$this->core->db->options_save();

			// Reset WordPress internal Page references.
			$this->wordpress_option_restore( 'show_on_front' );
			$this->wordpress_option_restore( 'page_on_front' );
			$this->wordpress_option_restore( 'page_for_posts' );

		}

		// --<
		return $success;

	}

	/**
	 * Delete a particular Special Page.
	 *
	 * @since 3.4
	 *
	 * @param str $page The type of Special Page to delete.
	 * @return boolean $success True if succesfully deleted false otherwise.
	 */
	public function delete_special_page( $page ) {

		// Init success flag.
		$success = true;

		/*
		 * Only delete a Special Page if we have one of the CommentPress Core
		 * themes active because other themes may have a totally different way
		 * of presenting the content of the Blog.
		 */

		// Get id of Special Page.
		switch ( $page ) {

			case 'title':

				// Set flag.
				$flag = 'cp_welcome_page';

				// Reset WordPress internal Page references.
				$this->wordpress_option_restore( 'show_on_front' );
				$this->wordpress_option_restore( 'page_on_front' );

				break;

			case 'general_comments':

				// Set flag.
				$flag = 'cp_general_comments_page';
				break;

			case 'all_comments':

				// Set flag.
				$flag = 'cp_all_comments_page';
				break;

			case 'comments_by_author':

				// Set flag.
				$flag = 'cp_comments_by_page';
				break;

			case 'blog':

				// Set flag.
				$flag = 'cp_blog_page';

				// Reset WordPress internal Page reference.
				$this->wordpress_option_restore( 'page_for_posts' );

				break;

			case 'blog_archive':

				// Set flag.
				$flag = 'cp_blog_archive_page';
				break;

			case 'toc':

				// Set flag.
				$flag = 'cp_toc_page';
				break;

		}

		// Get Page ID.
		$page_id = $this->core->db->option_get( $flag );

		// Kick out if it doesn't exist.
		if ( ! $page_id ) {
			return true;
		}

		// Delete option.
		$this->core->db->option_delete( $flag );

		// Bypass trash.
		$force_delete = true;

		// Try and delete the Page.
		if ( ! wp_delete_post( $page_id, $force_delete ) ) {

			// Oops, set success flag to false.
			$success = false;

		}

		// Retrieve data on Special Pages.
		$special_pages = $this->core->db->option_get( 'cp_special_pages', [] );

		// Is it in our Special Pages array?
		if ( in_array( $page_id, $special_pages ) ) {

			// Remove Page ID from array.
			$special_pages = array_diff( $special_pages, [ $page_id ] );

			// Reset option.
			$this->core->db->option_set( 'cp_special_pages', $special_pages );

		}

		// Save changes.
		$this->core->db->options_save();

		// --<
		return $success;

	}

	// -------------------------------------------------------------------------

	/**
	 * Create "title" Page.
	 *
	 * @since 3.4
	 *
	 * @return int $title_id The numeric ID of the Title Page.
	 */
	public function create_title_page() {

		// Get the option, if it exists.
		$page_exists = $this->core->db->option_get( 'cp_welcome_page' );

		// Don't create if we already have the option set.
		if ( $page_exists !== false && is_numeric( $page_exists ) ) {

			// Get the Page (the plugin may have been deactivated, then the Page deleted).
			$welcome = get_post( $page_exists );

			// Check that the Page exists.
			if ( ! is_null( $welcome ) ) {

				// Got it.

				// We still ought to set WordPress internal Page references.
				$this->wordpress_option_backup( 'show_on_front', 'page' );
				$this->wordpress_option_backup( 'page_on_front', $page_exists );

				// --<
				return $page_exists;

			} else {

				// Page does not exist, continue on and create it.

			}

		}

		// Define Welcome/Title Page.
		$title = [
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'open',
			'ping_status' => 'closed',
			'to_ping' => '', // Quick fix for Windows.
			'pinged' => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt' => '', // Quick fix for Windows.
			'menu_order' => 0,
		];

		// Add Post-specific stuff.

		// Default Page title.
		$default_title = __( 'Title Page', 'commentpress-core' );

		/**
		 * Filters the Title Page title.
		 *
		 * @since 3.4
		 *
		 * @param string $default_title The default title of the Page.
		 */
		$title['post_title'] = apply_filters( 'cp_title_page_title', $default_title );

		// Default content.
		$content = __(
			'Welcome to your new CommentPress site, which allows your readers to comment paragraph-by-paragraph or line-by-line in the margins of a text. Annotate, gloss, workshop, debate: with CommentPress you can do all of these things on a finer-grained level, turning a document into a conversation.

This is your title page. Edit it to suit your needs. It has been automatically set as your homepage but if you want another page as your homepage, set it in <em>WordPress</em> &#8594; <em>Settings</em> &#8594; <em>Reading</em>.

You can also set a number of options in <em>WordPress</em> &#8594; <em>Settings</em> &#8594; <em>CommentPress</em> to make the site work the way you want it to. Use the Theme Customizer to change the way your site looks in <em>WordPress</em> &#8594; <em>Appearance</em> &#8594; <em>Customize</em>. For help with structuring, formatting and reading text in CommentPress, please refer to the <a href="http://www.futureofthebook.org/commentpress/">CommentPress website</a>.', 'commentpress-core'
		);

		/**
		 * Filters the Title Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $content The default content of the Page.
		 */
		$title['post_content'] = apply_filters( 'cp_title_page_content', $content );

		/**
		 * Filters the Title Page template.
		 *
		 * @since 3.4
		 *
		 * @param string The default template of the Page.
		 */
		$title['page_template'] = apply_filters( 'cp_title_page_template', 'welcome.php' );

		// Insert the Post into the database.
		$title_id = wp_insert_post( $title );

		// Store the option.
		$this->core->db->option_set( 'cp_welcome_page', $title_id );

		// Set WordPress internal Page references.
		$this->wordpress_option_backup( 'show_on_front', 'page' );
		$this->wordpress_option_backup( 'page_on_front', $title_id );

		/**
		 * Fires when the Title Page has been created.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Core_Formatter::formatter_default_apply() (Priority: 10)
		 *
		 * @since 4.0
		 *
		 * @param int $title_id The numeric ID of the new Page.
		 */
		do_action( 'commentpress/core/db/page/special/title/created', $title_id );

		// --<
		return $title_id;

	}

	/**
	 * Create "General Comments" Page.
	 *
	 * @since 3.4
	 *
	 * @return int $general_comments_id The numeric ID of the "General Comments" Page.
	 */
	public function create_general_comments_page() {

		// Define General Comments Page.
		$general_comments = [
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'open',
			'ping_status' => 'open',
			'to_ping' => '', // Quick fix for Windows.
			'pinged' => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt' => '', // Quick fix for Windows.
			'menu_order' => 0,
		];

		// Add Post-specific stuff.

		// Default Page title.
		$title = __( 'General Comments', 'commentpress-core' );

		/**
		 * Filters the General Comments Page title.
		 *
		 * @since 3.4
		 *
		 * @param string $title The default title of the Page.
		 */
		$general_comments['post_title'] = apply_filters( 'cp_general_comments_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		/**
		 * Filters the General Comments Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $content The default content of the Page.
		 */
		$general_comments['post_content'] = apply_filters( 'cp_general_comments_content', $content );

		/**
		 * Filters the General Comments Page template.
		 *
		 * @since 3.4
		 *
		 * @param string The default template of the Page.
		 */
		$general_comments['page_template'] = apply_filters( 'cp_general_comments_template', 'comments-general.php' );

		// Insert the Post into the database.
		$general_comments_id = wp_insert_post( $general_comments );

		// Store the option.
		$this->core->db->option_set( 'cp_general_comments_page', $general_comments_id );

		// --<
		return $general_comments_id;

	}

	/**
	 * Create "All Comments" Page.
	 *
	 * @since 3.4
	 *
	 * @return int $all_comments_id The numeric ID of the "All Comments" Page.
	 */
	public function create_all_comments_page() {

		// Define All Comments Page.
		$all_comments = [
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'to_ping' => '', // Quick fix for Windows.
			'pinged' => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt' => '', // Quick fix for Windows.
			'menu_order' => 0,
		];

		// Add Post-specific stuff.

		// Default Page title.
		$title = __( 'All Comments', 'commentpress-core' );

		/**
		 * Filters the All Comments Page title.
		 *
		 * @since 3.4
		 *
		 * @param string $title The default title of the Page.
		 */
		$all_comments['post_title'] = apply_filters( 'cp_all_comments_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		/**
		 * Filters the All Comments Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $content The default content of the Page.
		 */
		$all_comments['post_content'] = apply_filters( 'cp_all_comments_content', $content );

		/**
		 * Filters the All Comments Page template.
		 *
		 * @since 3.4
		 *
		 * @param string The default template of the Page.
		 */
		$all_comments['page_template'] = apply_filters( 'cp_all_comments_template', 'comments-all.php' );

		// Insert the Post into the database.
		$all_comments_id = wp_insert_post( $all_comments );

		// Store the option.
		$this->core->db->option_set( 'cp_all_comments_page', $all_comments_id );

		// --<
		return $all_comments_id;

	}

	/**
	 * Create "Comments by Author" Page.
	 *
	 * @since 3.4
	 *
	 * @return int $group_id The numeric ID of the "Comments by Author" Page.
	 */
	public function create_comments_by_author_page() {

		// Define Comments by Author Page.
		$group = [
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'to_ping' => '', // Quick fix for Windows.
			'pinged' => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt' => '', // Quick fix for Windows.
			'menu_order' => 0,
		];

		// Add Post-specific stuff.

		// Default Page title.
		$title = __( 'Comments by Commenter', 'commentpress-core' );

		/**
		 * Filters the Comments by Commenter Page title.
		 *
		 * @since 3.4
		 *
		 * @param string $title The default title of the Page.
		 */
		$group['post_title'] = apply_filters( 'cp_comments_by_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		/**
		 * Filters the Comments by Commenter Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $content The default content of the Page.
		 */
		$group['post_content'] = apply_filters( 'cp_comments_by_content', $content );

		/**
		 * Filters the Comments by Commenter Page template.
		 *
		 * @since 3.4
		 *
		 * @param string The default template of the Page.
		 */
		$group['page_template'] = apply_filters( 'cp_comments_by_template', 'comments-by.php' );

		// Insert the Post into the database.
		$group_id = wp_insert_post( $group );

		// Store the option.
		$this->core->db->option_set( 'cp_comments_by_page', $group_id );

		// --<
		return $group_id;

	}

	/**
	 * Create "blog" Page.
	 *
	 * @since 3.4
	 *
	 * @return int $blog_id The numeric ID of the "Blog" Page.
	 */
	public function create_blog_page() {

		// Define Blog Page.
		$blog = [
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'to_ping' => '', // Quick fix for Windows.
			'pinged' => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt' => '', // Quick fix for Windows.
			'menu_order' => 0,
		];

		// Add Post-specific stuff.

		// Default Page title.
		$title = __( 'Blog', 'commentpress-core' );

		/**
		 * Filters the Blog Page title.
		 *
		 * @since 3.4
		 *
		 * @param string $title The default title of the Page.
		 */
		$blog['post_title'] = apply_filters( 'cp_blog_page_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		/**
		 * Filters the Blog Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $content The default content of the Page.
		 */
		$blog['post_content'] = apply_filters( 'cp_blog_page_content', $content );

		/**
		 * Filters the Blog Page template.
		 *
		 * @since 3.4
		 *
		 * @param string The default template of the Page.
		 */
		$blog['page_template'] = apply_filters( 'cp_blog_page_template', 'blog.php' );

		// Insert the Post into the database.
		$blog_id = wp_insert_post( $blog );

		// Store the option.
		$this->core->db->option_set( 'cp_blog_page', $blog_id );

		// Set WordPress internal Page reference.
		$this->wordpress_option_backup( 'page_for_posts', $blog_id );

		// --<
		return $blog_id;

	}

	/**
	 * Create "Blog Archive" Page.
	 *
	 * @since 3.4
	 *
	 * @return int $blog_id The numeric ID of the "Blog Archive" Page.
	 */
	public function create_blog_archive_page() {

		// Define Blog Archive Page.
		$blog = [
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'to_ping' => '', // Quick fix for Windows.
			'pinged' => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt' => '', // Quick fix for Windows.
			'menu_order' => 0,
		];

		// Add Post-specific stuff.

		// Default Page title.
		$title = __( 'Blog Archive', 'commentpress-core' );

		/**
		 * Filters the Blog Archive Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $title The default content of the Page.
		 */
		$blog['post_title'] = apply_filters( 'cp_blog_archive_page_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		/**
		 * Filters the Blog Archive Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $content The default content of the Page.
		 */
		$blog['post_content'] = apply_filters( 'cp_blog_archive_page_content', $content );

		/**
		 * Filters the Blog Archive Page template.
		 *
		 * @since 3.4
		 *
		 * @param string The default template of the Page.
		 */
		$blog['page_template'] = apply_filters( 'cp_blog_archive_page_template', 'archives.php' );

		// Insert the Post into the database.
		$blog_id = wp_insert_post( $blog );

		// Store the option.
		$this->core->db->option_set( 'cp_blog_archive_page', $blog_id );

		// --<
		return $blog_id;

	}

	/**
	 * Create "Table of Contents" Page.
	 *
	 * PLease note: this is NOT USED.
	 *
	 * @since 3.4
	 *
	 * @return int $toc_id The numeric ID of the "Table of Contents" Page.
	 */
	public function create_toc_page() {

		// Define TOC Page.
		$toc = [
			'post_status' => 'publish',
			'post_type' => 'page',
			'post_parent' => 0,
			'comment_status' => 'closed',
			'ping_status' => 'closed',
			'to_ping' => '', // Quick fix for Windows.
			'pinged' => '', // Quick fix for Windows.
			'post_content_filtered' => '', // Quick fix for Windows.
			'post_excerpt' => '', // Quick fix for Windows.
			'menu_order' => 0,
		];

		// Default Page title.
		$title = __( 'Table of Contents', 'commentpress-core' );

		/**
		 * Filters the TOC Page title.
		 *
		 * @since 3.4
		 *
		 * @param string $title The default title of the Page.
		 */
		$toc['post_title'] = apply_filters( 'cp_toc_page_title', $title );

		// Default content.
		$content = __( 'Do not delete this page. Page content is generated with a custom template.', 'commentpress-core' );

		/**
		 * Filters the TOC Page content.
		 *
		 * @since 3.4
		 *
		 * @param string $content The default content of the Page.
		 */
		$toc['post_content'] = apply_filters( 'cp_toc_page_content', $content );

		/**
		 * Filters the TOC Page template.
		 *
		 * @since 3.4
		 *
		 * @param string The default template of the Page.
		 */
		$toc['page_template'] = apply_filters( 'cp_toc_page_template', 'toc.php' );

		// Insert the Post into the database.
		$toc_id = wp_insert_post( $toc );

		// Store the option.
		$this->core->db->option_set( 'cp_toc_page', $toc_id );

		// --<
		return $toc_id;

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets a link to a Special Page.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @param str $page_type The CommentPress Core "name" of a Special Page.
	 * @return str $link The HTML link to that Page.
	 */
	public function get_page_link( $page_type = 'cp_all_comments_page' ) {

		// Access globals.
		global $post;

		// Init.
		$link = '';

		// Get Page ID.
		$page_id = $this->core->db->option_get( $page_type );

		// Bail if we have no Page.
		if ( empty( $page_id ) ) {
			return $link;
		}

		// Get Page.
		$page = get_post( $page_id );

		// Is it the current Page?
		$active = '';
		if ( ( $post instanceof WP_Post ) && (int) $page->ID === (int) $post->ID ) {
			$active = ' class="active_page"';
		}

		// Get link.
		$url = get_permalink( $page );

		// Switch title by type.
		switch ( $page_type ) {

			case 'cp_welcome_page':
				$link_title = __( 'Title Page', 'commentpress-core' );
				$button = 'cover';
				break;

			case 'cp_all_comments_page':
				$link_title = __( 'All Comments', 'commentpress-core' );
				$button = 'allcomments';
				break;

			case 'cp_general_comments_page':
				$link_title = __( 'General Comments', 'commentpress-core' );
				$button = 'general';
				break;

			case 'cp_blog_page':
				$link_title = __( 'Blog', 'commentpress-core' );
				if ( is_home() ) {
					$active = ' class="active_page"';
				}
				$button = 'blog';
				break;

			case 'cp_blog_archive_page':
				$link_title = __( 'Blog Archive', 'commentpress-core' );
				$button = 'archive';
				break;

			case 'cp_comments_by_page':
				$link_title = __( 'Comments by Commenter', 'commentpress-core' );
				$button = 'members';
				break;

			default:
				$link_title = __( 'Members', 'commentpress-core' );
				$button = 'members';

		}

		/**
		 * Filters the Special Page title.
		 *
		 * @since 3.4
		 *
		 * @param str $link_title The default Special Page title.
		 * @param str $page_type The CommentPress Core "name" of a Special Page.
		 */
		$title = apply_filters( 'commentpress_page_link_title', $link_title, $page_type );

		// Build link.
		$link = '<li' . $active . '>' .
			'<a href="' . $url . '" id="btn_' . $button . '" class="css_btn" title="' . $title . '">' .
				$title .
			'</a>' .
		'</li>' . "\n";

		// --<
		return $link;

	}

	/**
	 * Gets the URL for a Special Page.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @param str $page_type The CommentPress Core "name" of a Special Page.
	 * @return str $url The URL of that Page.
	 */
	public function get_page_url( $page_type = 'cp_all_comments_page' ) {

		// Init.
		$url = '';

		// Get Page ID.
		$page_id = $this->core->db->option_get( $page_type );

		// Bail if we have no Page.
		if ( empty( $page_id ) ) {
			return $url;
		}

		// Get Page.
		$page = get_post( $page_id );

		// Get link.
		$url = get_permalink( $page );

		// --<
		return $url;

	}

}
