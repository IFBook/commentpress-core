<?php
/**
 * CommentPress Core class.
 *
 * Handles single site plugin functionality.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Class.
 *
 * A class that encapsulates single site plugin functionality.
 *
 * @since 3.0
 */
class CommentPress_Core {

	/**
	 * Database object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $db The database object.
	 */
	public $db;

	/**
	 * Display handling object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $display The display object.
	 */
	public $display;

	/**
	 * Navigation handling object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $nav The nav object.
	 */
	public $nav;

	/**
	 * Content parser object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $parser The parser object.
	 */
	public $parser;

	/**
	 * Formatter object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $formatter The formatter object.
	 */
	public $formatter;

	/**
	 * Workflow object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $workflow The workflow object.
	 */
	public $workflow;

	/**
	 * BuddyPress present flag.
	 *
	 * @since 3.3
	 * @access public
	 * @var bool $buddypress True if BuddyPress present, false otherwise.
	 */
	public $buddypress = false;

	/**
	 * BuddyPress Groupblog flag.
	 *
	 * @since 3.3
	 * @access public
	 * @var bool $bp_groupblog True if BuddyPress Groupblog present, false otherwise.
	 */
	public $bp_groupblog = false;

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 */
	public function __construct() {

		// Initialise when all plugins have loaded.
		add_action( 'plugins_loaded', [ $this, 'initialise' ] );

		// Use translation.
		add_action( 'plugins_loaded', [ $this, 'translation' ] );

		// Init.
		$this->initialise();

	}

	/**
	 * Initialises this plugin.
	 *
	 * @since 4.0
	 */
	public function initialise() {

		// Only do this once.
		static $done;
		if ( isset( $done ) && $done === true ) {
			return;
		}

		// Bootstrap plugin.
		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

		/**
		 * Broadcast that CommentPress Core has loaded.
		 *
		 * Used internally to bootstrap objects.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/core/loaded' );

		/**
		 * Broadcast that CommentPress Core has loaded.
		 *
		 * @since 3.6.3
		 */
		do_action( 'commentpress_loaded' );

		// We're done.
		$done = true;

	}

	/**
	 * Includes class files.
	 *
	 * @since 4.0
	 */
	public function include_files() {

		// Include class files.
		require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-core/class_commentpress_admin.php';
		require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-core/class_commentpress_db.php';
		require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-core/class_commentpress_display.php';
		require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-core/class_commentpress_nav.php';
		require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-core/class_commentpress_parser.php';
		require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-core/class_commentpress_formatter.php';
		require_once COMMENTPRESS_PLUGIN_PATH . 'commentpress-core/class_commentpress_workflow.php';

		/**
		 * Broadcast that class files have been included.
		 *
		 * @since 3.6.2
		 */
		do_action( 'commentpress_after_includes' );

	}

	/**
	 * Sets up this plugin's objects.
	 *
	 * @since 4.0
	 */
	public function setup_objects() {

		// Initialise objects.
		$this->admin = new CommentPress_Core_Admin( $this );
		$this->db = new CommentPress_Core_Database( $this );
		$this->display = new CommentPress_Core_Display( $this );
		$this->nav = new CommentPress_Core_Navigator( $this );
		$this->parser = new CommentPress_Core_Parser( $this );
		$this->formatter = new CommentPress_Core_Formatter( $this );
		$this->workflow = new CommentPress_Core_Workflow( $this );

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.4
	 * @since 4.0 Renamed.
	 */
	public function register_hooks() {

		// Modify comment posting.
		add_action( 'comment_post', [ $this, 'save_comment' ], 10, 2 );

		// Exclude special pages from listings.
		add_filter( 'wp_list_pages_excludes', [ $this, 'exclude_special_pages' ], 10, 1 );
		add_filter( 'parse_query', [ $this, 'exclude_special_pages_from_admin' ], 10, 1 );

		// Is this the back end?
		if ( is_admin() ) {

			// Modify all.
			add_filter( 'views_edit-page', [ $this, 'update_page_counts_in_admin' ], 10, 1 );

			// Add meta boxes.
			add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );

			// Intercept save.
			add_action( 'save_post', [ $this, 'save_post' ], 10, 2 );

			// Intercept delete.
			add_action( 'before_delete_post', [ $this, 'delete_post' ], 10, 1 );

			// Comment block quicktag.
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );

		} else {

			// Add script libraries.
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

			// Add CSS files.
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );

			// Add template redirect for TOC behaviour.
			add_action( 'template_redirect', [ $this, 'redirect_to_child' ] );

			// Modify the content (after all's done).
			add_filter( 'the_content', [ $this, 'the_content' ], 20 );

		}

		// If we're in a multisite scenario.
		// TODO: Move to WordPress Multisite class.
		if ( is_multisite() ) {

			// Add callback for signup page to include sidebar.
			add_action( 'after_signup_form', [ $this, 'after_signup_form' ], 20 );

			// If subdirectory install.
			if ( ! is_subdomain_install() ) {

				// Add filter for reserved CommentPress Core special page names.
				add_filter( 'subdirectory_reserved_names', [ $this, 'add_reserved_names' ] );

			}

		}

		// If BuddyPress installed, then the following actions will fire.

		// Enable BuddyPress functionality.
		add_action( 'bp_include', [ $this, 'buddypress_init' ] );

		// Add BuddyPress functionality - really late, so group object is set up.
		add_action( 'bp_setup_globals', [ $this, 'buddypress_globals_loaded' ], 1000 );

		// Actions to perform on BuddyPress loaded.
		add_action( 'bp_loaded', [ $this, 'bp_docs_loaded' ], 20 );

		// Actions to perform on BuddyPress Docs load.
		add_action( 'bp_docs_load', [ $this, 'bp_docs_loaded' ], 20 );

		// Override BuddyPress Docs comment template.
		add_filter( 'bp_docs_comment_template_path', [ $this, 'bp_docs_comment_tempate' ], 20, 2 );

		// Amend the behaviour of Featured Comments plugin.
		add_action( 'plugins_loaded', [ $this, 'featured_comments_override' ], 1000 );

		/**
		 * Broadcast that callbacks have been added.
		 *
		 * @since 3.6.2
		 */
		do_action( 'commentpress_after_hooks' );

	}

	// -------------------------------------------------------------------------

	/**
	 * Runs when plugin is activated.
	 *
	 * @since 3.0
	 */
	public function activate() {

		// Initialise display - sets the theme.
		$this->display->activate();

		// Initialise database.
		$this->db->activate();

	}

	/**
	 * Runs when plugin is deactivated.
	 *
	 * @since 3.0
	 */
	public function deactivate() {

		// Call database destroy method.
		$this->db->deactivate();

		// Call display destroy method.
		$this->display->deactivate();

	}

	/**
	 * Loads translation, if present.
	 *
	 * @since 3.4
	 */
	public function translation() {

		// Load translations.
		// phpcs:ignore WordPress.WP.DeprecatedParameters.Load_plugin_textdomainParam2Found
		load_plugin_textdomain(
			'commentpress-core', // Unique name.
			false, // Deprecated argument.
			dirname( plugin_basename( COMMENTPRESS_PLUGIN_FILE ) ) . '/languages/' // Relative path to directory.
		);

	}

	/**
	 * Called when BuddyPress is active.
	 *
	 * @since 3.4
	 */
	public function buddypress_init() {

		// We've got BuddyPress installed.
		$this->buddypress = true;

	}

	/**
	 * Configure when BuddyPress is loaded.
	 *
	 * @since 3.4
	 */
	public function buddypress_globals_loaded() {

		// Test for a bp-groupblog function.
		if ( function_exists( 'get_groupblog_group_id' ) ) {

			// Check if this blog is a group blog.
			$group_id = get_groupblog_group_id( get_current_blog_id() );
			if ( isset( $group_id ) && is_numeric( $group_id ) && $group_id > 0 ) {

				// Okay, we're properly configured.
				$this->bp_groupblog = true;

			}

		}

	}

	/**
	 * Is BuddyPress active?
	 *
	 * @since 3.4
	 *
	 * @return bool $buddypress True when BuddyPress active, false otherwise.
	 */
	public function is_buddypress() {

		// --<
		return $this->buddypress;

	}

	/**
	 * Is this blog a BuddyPress Groupblog?
	 *
	 * @since 3.4
	 *
	 * @return bool $bp_groupblog True when current blog is a BuddyPress Groupblog, false otherwise.
	 */
	public function is_groupblog() {

		// --<
		return $this->bp_groupblog;

	}

	/**
	 * Is a BuddyPress Groupblog theme set?
	 *
	 * @since 3.4
	 *
	 * @return array $theme An array describing the theme.
	 */
	public function get_groupblog_theme() {

		// Kick out if not in a group context.
		if ( ! function_exists( 'bp_is_groups_component' ) ) {
			return false;
		}
		if ( ! bp_is_groups_component() ) {
			return false;
		}

		// Get groupblog options.
		$options = get_site_option( 'bp_groupblog_blog_defaults_options' );

		// Get theme setting.
		if ( ! empty( $options['theme'] ) ) {

			// We have a groupblog theme set.

			// Split the options.
			list( $stylesheet, $template ) = explode( '|', $options['theme'] );

			// Get the registered theme.
			$theme = wp_get_theme( $stylesheet );

			// Test if it's a CommentPress Core theme.
			if ( in_array( 'commentpress', (array) $theme->get( 'Tags' ) ) ) {

				// --<
				return [ $stylesheet, $template ];

			}

		}

		// --<
		return false;

	}

	/**
	 * Is this a BuddyPress "special page" - a component homepage?
	 *
	 * @since 3.4
	 *
	 * @return bool $is_bp True if current page is a BuddyPress page, false otherwise.
	 */
	public function is_buddypress_special_page() {

		// Kick out if not BuddyPress.
		if ( ! $this->is_buddypress() ) {
			return false;
		}

		// Is it a BuddyPress page?
		$is_bp = ! bp_is_blog_page();

		// Let's see.
		return apply_filters( 'cp_is_buddypress_special_page', $is_bp );

	}

	/**
	 * Utility to add a message to admin pages when upgrade required.
	 *
	 * @since 3.4
	 */
	public function admin_upgrade_alert() {

		// Check user permissions.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Show it.
		echo '<div id="message" class="error"><p>' .
			sprintf(
				/* translators: 1: The opening anchor tag, 2: The closing anchor tag. */
				__( 'CommentPress Core has been updated. Please visit the %1$sSettings Page%2$s.', 'commentpress-core' ),
				'<a href="options-general.php?page=commentpress_admin">',
				'</a>'
			) .
		'</p></div>';

	}

	/**
	 * Add scripts needed across all WordPress admin pages.
	 *
	 * @since 3.4
	 *
	 * @param str $hook The requested admin page.
	 */
	public function enqueue_admin_scripts( $hook ) {

		// Don't enqueue on comment edit screen.
		if ( 'comment.php' == $hook ) {
			return;
		}

		// Add quicktag button to page editor.
		$this->display->get_custom_quicktags();

	}

	/**
	 * Adds script libraries.
	 *
	 * @since 3.4
	 */
	public function enqueue_scripts() {

		// Don't include in admin or wp-login.php.
		if ( is_admin() || ( isset( $GLOBALS['pagenow'] ) && 'wp-login.php' == $GLOBALS['pagenow'] ) ) {
			return;
		}

		// Test for mobile user agents.
		$this->db->test_for_mobile();

		// Add jQuery libraries.
		$this->display->get_jquery();

	}

	/**
	 * Adds CSS.
	 *
	 * @since 3.4
	 */
	public function enqueue_styles() {

		// Add plugin styles.
		$this->display->get_frontend_styles();

	}

	/**
	 * Redirect to child page.
	 *
	 * @since 3.4
	 */
	public function redirect_to_child() {

		// Do redirect.
		$this->nav->redirect_to_child();

	}

	/**
	 * Parses page/post content.
	 *
	 * @since 3.0
	 *
	 * @param str $content The content of the page/post.
	 * @return str $content The modified content.
	 */
	public function the_content( $content ) {

		// Reference our post.
		global $post;

		// JetPack 2.7 or greater parses the content in the head to create
		// content summaries so prevent parsing unless this is the main content.
		if ( is_admin() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		// Compat with Subscribe to Comments Reloaded.
		if ( $this->is_subscribe_to_comments_reloaded_page() ) {
			return $content;
		}

		// Compat with Theme My Login.
		if ( $this->is_theme_my_login_page() ) {
			return $content;
		}

		// Compat with Members List plugin.
		if ( $this->is_members_list_page() ) {
			return $content;
		}

		// Test for BuddyPress special page (compat with BuddyPress Docs).
		if ( $this->is_buddypress() ) {

			// Is it a component homepage?
			if ( $this->is_buddypress_special_page() ) {

				// --<
				return $content;

			}

		}

		// Init allowed.
		$allowed = false;

		// Only parse posts or pages.
		if ( ( is_single() || is_page() || is_attachment() ) && ! $this->db->is_special_page() ) {
			$allowed = true;
		}

		// If allowed, parse.
		if ( apply_filters( 'commentpress_force_the_content', $allowed ) ) {

			// Delegate to parser.
			$content = $this->parser->the_content( $content );

		}

		// --<
		return $content;

	}

	/**
	 * Retrieves option for displaying TOC.
	 *
	 * @since 3.4
	 *
	 * @return mixed $result
	 */
	public function get_list_option() {

		// Get list option flag.
		$result = $this->db->option_get( 'cp_show_posts_or_pages_in_toc' );

		// --<
		return $result;

	}

	/**
	 * Retrieves minimise all button.
	 *
	 * @since 3.4
	 *
	 * @param str $sidebar The type of sidebar - either 'comments', 'toc' or 'activity'.
	 * @return str $result The HTML for minimise button.
	 */
	public function get_minimise_all_button( $sidebar = 'comments' ) {

		// Get minimise image.
		$result = $this->display->get_minimise_all_button( $sidebar );

		// --<
		return $result;

	}

	/**
	 * Retrieves header minimise button.
	 *
	 * @since 3.4
	 *
	 * @return str $result The HTML for minimise button.
	 */
	public function get_header_min_link() {

		// Get minimise image.
		$result = $this->display->get_header_min_link();

		// --<
		return $result;

	}

	/**
	 * Retrieves text_signature hidden input.
	 *
	 * @since 3.4
	 *
	 * @return str $result The HTML input.
	 */
	public function get_signature_field() {

		// Init text signature.
		$text_sig = '';

		// Get comment ID to reply to from URL query string.
		$reply_to_comment_id = isset( $_GET['replytocom'] ) ? (int) $_GET['replytocom'] : 0;

		// Did we get a comment ID?
		if ( $reply_to_comment_id != 0 ) {

			// Get paragraph text signature.
			$text_sig = $this->db->get_text_signature_by_comment_id( $reply_to_comment_id );

		} else {

			// Do we have a paragraph number in the query string?
			$reply_to_para_id = isset( $_GET['replytopara'] ) ? (int) $_GET['replytopara'] : 0;

			// Did we get a comment ID?
			if ( $reply_to_para_id != 0 ) {

				// Get paragraph text signature.
				$text_sig = $this->get_text_signature( $reply_to_para_id );

			}

		}

		// Get constructed hidden input for comment form.
		$result = $this->display->get_signature_input( $text_sig );

		// --<
		return $result;

	}

	/**
	 * Add reserved names.
	 *
	 * @since 3.4
	 *
	 * @param array $reserved_names The existing list of illegal names.
	 * @return array $reserved_names The modified list of illegal names.
	 */
	public function add_reserved_names( $reserved_names ) {

		// Get all image attachments to our title page.
		$reserved_names = array_merge(
			$reserved_names,
			[
				'title-page',
				'general-comments',
				'all-comments',
				'comments-by-commenter',
				'table-of-contents',
				'author', // Not currently used.
				'login', // For Theme My Login.
			]
		);

		// --<
		return $reserved_names;

	}

	/**
	 * Add sidebar to signup form.
	 *
	 * @since 3.4
	 */
	public function after_signup_form() {

		// Add sidebar.
		get_sidebar();

	}

	/**
	 * Adds meta boxes to admin screens.
	 *
	 * @since 3.4
	 */
	public function add_meta_boxes() {

		// Add our meta boxes to pages.
		add_meta_box(
			'commentpress_page_options',
			__( 'CommentPress Core Options', 'commentpress-core' ),
			[ $this, 'custom_box_page' ],
			'page',
			'side'
		);

		// Add our meta box to posts.
		add_meta_box(
			'commentpress_post_options',
			__( 'CommentPress Core Options', 'commentpress-core' ),
			[ $this, 'custom_box_post' ],
			'post',
			'side'
		);

		// Get workflow.
		$workflow = $this->db->option_get( 'cp_blog_workflow' );

		// If it's enabled.
		if ( $workflow == '1' ) {

			// Init title.
			$title = __( 'Workflow', 'commentpress-core' );

			// Allow overrides.
			$title = apply_filters( 'cp_workflow_metabox_title', $title );

			// Add our meta box to posts.
			add_meta_box(
				'commentpress_workflow_fields',
				$title,
				[ $this, 'custom_box_workflow' ],
				'post',
				'normal'
			);

			// Add our meta box to pages.
			add_meta_box(
				'commentpress_workflow_fields',
				$title,
				[ $this, 'custom_box_workflow' ],
				'page',
				'normal'
			);

		}

	}

	/**
	 * Adds meta box to page edit screens.
	 *
	 * @since 3.4
	 */
	public function custom_box_page() {

		// Access post.
		global $post;

		// Use nonce for verification.
		wp_nonce_field( 'commentpress_page_settings', 'commentpress_nonce' );

		// ---------------------------------------------------------------------
		// Show or Hide Page Title.
		// ---------------------------------------------------------------------

		// Show a title.
		echo '<div class="cp_title_visibility_wrapper">
		<p><strong><label for="cp_title_visibility">' . __( 'Page Title Visibility', 'commentpress-core' ) . '</label></strong></p>';

		// Set key.
		$key = '_cp_title_visibility';

		// Default to show.
		$viz = $this->db->option_get( 'cp_title_visibility' );

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Get it.
			$viz = get_post_meta( $post->ID, $key, true );

		}

		// Select.
		echo '
		<p>
		<select id="cp_title_visibility" name="cp_title_visibility">
			<option value="show" ' . ( ( $viz == 'show' ) ? ' selected="selected"' : '' ) . '>' . __( 'Show page title', 'commentpress-core' ) . '</option>
			<option value="hide" ' . ( ( $viz == 'hide' ) ? ' selected="selected"' : '' ) . '>' . __( 'Hide page title', 'commentpress-core' ) . '</option>
		</select>
		</p>
		</div>
		';

		// ---------------------------------------------------------------------
		// Show or Hide Page Meta.
		// ---------------------------------------------------------------------

		// Show a label.
		echo '<div class="cp_page_meta_visibility_wrapper">
		<p><strong><label for="cp_page_meta_visibility">' . __( 'Page Meta Visibility', 'commentpress-core' ) . '</label></strong></p>';

		// Set key.
		$key = '_cp_page_meta_visibility';

		// Default to show.
		$viz = $this->db->option_get( 'cp_page_meta_visibility' );

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Get it.
			$viz = get_post_meta( $post->ID, $key, true );

		}

		// Select.
		echo '
		<p>
		<select id="cp_page_meta_visibility" name="cp_page_meta_visibility">
			<option value="show" ' . ( ( $viz == 'show' ) ? ' selected="selected"' : '' ) . '>' . __( 'Show page meta', 'commentpress-core' ) . '</option>
			<option value="hide" ' . ( ( $viz == 'hide' ) ? ' selected="selected"' : '' ) . '>' . __( 'Hide page meta', 'commentpress-core' ) . '</option>
		</select>
		</p>
		</div>
		';

		// ---------------------------------------------------------------------
		// Page Numbering - only shown on first top level page.
		// ---------------------------------------------------------------------

		// If page has no parent and it's not a special page and it's the first.
		if (
			$post->post_parent == '0' &&
			! $this->db->is_special_page() &&
			$post->ID == $this->nav->get_first_page()
		) {

			// Label.
			echo '<div class="cp_number_format_wrapper">
			<p><strong><label for="cp_number_format">' . __( 'Page Number Format', 'commentpress-core' ) . '</label></strong></p>';

			// Set key.
			$key = '_cp_number_format';

			// Default to arabic.
			$format = 'arabic';

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// Get it.
				$format = get_post_meta( $post->ID, $key, true );

			}

			// Select.
			echo '
			<p>
			<select id="cp_number_format" name="cp_number_format">
				<option value="arabic" ' . ( ( $format == 'arabic' ) ? ' selected="selected"' : '' ) . '>' . __( 'Arabic numerals', 'commentpress-core' ) . '</option>
				<option value="roman" ' . ( ( $format == 'roman' ) ? ' selected="selected"' : '' ) . '>' . __( 'Roman numerals', 'commentpress-core' ) . '</option>
			</select>
			</p>
			</div>
			';

		}

		// ---------------------------------------------------------------------
		// Page Layout for Title Page -> to allow for Book Cover image.
		// ---------------------------------------------------------------------

		// Is this the title page?
		if ( $post->ID == $this->db->option_get( 'cp_welcome_page' ) ) {

			// Label.
			echo '<div class="cp_page_layout_wrapper">
			<p><strong><label for="cp_page_layout">' . __( 'Page Layout', 'commentpress-core' ) . '</label></strong></p>';

			// Set key.
			$key = '_cp_page_layout';

			// Default to text.
			$value = 'text';

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// Get it.
				$value = get_post_meta( $post->ID, $key, true );

			}

			// Select.
			echo '
			<p>
			<select id="cp_page_layout" name="cp_page_layout">
				<option value="text" ' . ( ( $value == 'text' ) ? ' selected="selected"' : '' ) . '>' . __( 'Standard', 'commentpress-core' ) . '</option>
				<option value="wide" ' . ( ( $value == 'wide' ) ? ' selected="selected"' : '' ) . '>' . __( 'Wide', 'commentpress-core' ) . '</option>
			</select>
			</p>
			</div>
			';

		}

		// Get post formatter.
		$this->get_post_formatter_metabox( $post );

		// Get default sidebar.
		$this->get_default_sidebar_metabox( $post );

		// Get starting para number.
		$this->get_para_numbering_metabox( $post );

	}

	/**
	 * Adds meta box to post edit screens.
	 *
	 * @since 3.4
	 */
	public function custom_box_post() {

		// Access post.
		global $post;

		// Use nonce for verification.
		wp_nonce_field( 'commentpress_post_settings', 'commentpress_nonce' );

		// Set key.
		$key = '_cp_newer_version';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Get it.
			$new_post_id = get_post_meta( $post->ID, $key, true );

			// -----------------------------------------------------------------
			// Show link to newer post.
			// -----------------------------------------------------------------

			// Define label.
			$label = __( 'This post already has a new version', 'commentpress-core' );

			// Get the edit post link.
			$edit_link = get_edit_post_link( $new_post_id );

			// Define label.
			$link = __( 'Edit new version', 'commentpress-core' );

			// Show link.
			echo '
			<p><a href="' . $edit_link . '">' . $link . '</a></p>' . "\n";

		} else {

			// -----------------------------------------------------------------
			// Create new post with content of current post.
			// -----------------------------------------------------------------

			// Label.
			echo '<p><strong><label for="cp_page_layout">' . __( 'Versioning', 'commentpress-core' ) . '</label></strong></p>';

			// Define label.
			$label = __( 'Create new version', 'commentpress-core' );

			// Show a title.
			echo '
			<div class="checkbox">
				<label for="commentpress_new_post"><input type="checkbox" value="1" id="commentpress_new_post" name="commentpress_new_post" /> ' . $label . '</label>
			</div>' . "\n";

		}

		// Get post formatter.
		$this->get_post_formatter_metabox( $post );

		// Get default sidebar.
		$this->get_default_sidebar_metabox( $post );

	}

	/**
	 * Adds workflow meta box to post edit screens.
	 *
	 * @since 3.4
	 */
	public function custom_box_workflow() {

		// We now need to add any workflow that a plugin might want.
		do_action( 'cp_workflow_metabox' );

	}

	/**
	 * Stores our additional params.
	 *
	 * @since 3.4
	 *
	 * @param int $post_id The numeric ID of the post (or revision).
	 * @param object $post The post object.
	 */
	public function save_post( $post_id, $post ) {

		// We don't use post_id because we're not interested in revisions.

		// Store our meta data.
		$result = $this->db->save_meta( $post );

	}

	/**
	 * Check for data integrity of other posts when one is deleted.
	 *
	 * @since 3.4
	 *
	 * @param int $post_id The numeric ID of the post (or revision).
	 */
	public function delete_post( $post_id ) {

		// Store our meta data.
		$result = $this->db->delete_meta( $post_id );

	}

	/**
	 * Stores our additional param - the text signature.
	 *
	 * @since 3.4
	 *
	 * @param int $comment_ID The numeric ID of the comment.
	 * @param str $comment_status The status of the comment.
	 */
	public function save_comment( $comment_ID, $comment_status ) {

		// Store our comment signature.
		$result = $this->db->save_comment_signature( $comment_ID );

		// Store our comment selection.
		$result = $this->db->save_comment_selection( $comment_ID );

		// In multipage situations, store our comment's page.
		$result = $this->db->save_comment_page( $comment_ID );

		// Has the comment been marked as spam?
		if ( $comment_status === 'spam' ) {

			// Yes - let the commenter know without throwing an AJAX error.
			wp_die( __( 'This comment has been marked as spam. Please contact a site administrator.', 'commentpress-core' ) );

		}

	}

	/**
	 * Get table of contents.
	 *
	 * @since 3.4
	 */
	public function get_toc() {

		// Switch pages or posts.
		if ( $this->get_list_option() == 'post' ) {

			// List posts.
			$this->display->list_posts();

		} else {

			// List pages.
			$this->display->list_pages();

		}

	}

	/**
	 * Get table of contents list.
	 *
	 * @since 3.4
	 *
	 * @param array $exclude_pages The array of pages to exclude.
	 */
	public function get_toc_list( $exclude_pages = [] ) {

		// Switch pages or posts.
		if ( $this->get_list_option() == 'post' ) {

			// List posts.
			$this->display->list_posts();

		} else {

			// List pages.
			$this->display->list_pages( $exclude_pages );

		}

	}

	/**
	 * Exclude special pages from page listings.
	 *
	 * @since 3.4
	 *
	 * @param array $excluded_array The existing list of excluded pages.
	 * @return array $excluded_array The modified list of excluded pages.
	 */
	public function exclude_special_pages( $excluded_array ) {

		// Get special pages array, if it's there.
		$special_pages = $this->db->option_get( 'cp_special_pages' );

		// Do we have an array?
		if ( is_array( $special_pages ) ) {

			// Merge and make unique.
			$excluded_array = array_unique( array_merge( $excluded_array, $special_pages ) );

		}

		// --<
		return $excluded_array;

	}

	/**
	 * Exclude special pages from admin page listings.
	 *
	 * @since 3.4
	 *
	 * @param array $query The existing page query.
	 */
	public function exclude_special_pages_from_admin( $query ) {

		global $pagenow, $post_type;

		// Check admin location.
		if ( is_admin() && $pagenow == 'edit.php' && $post_type == 'page' ) {

			// Get special pages array, if it's there.
			$special_pages = $this->db->option_get( 'cp_special_pages' );

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

			// Get special pages array, if it's there.
			$special_pages = $this->db->option_get( 'cp_special_pages' );

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

					// Subtract special page count.
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

					// Subtract special page count.
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

	/**
	 * Get comments sorted by text signature and paragraph.
	 *
	 * @since 3.4
	 *
	 * @param int $post_ID The numeric ID of the post.
	 * @return array $comments An array of sorted comment data.
	 */
	public function get_sorted_comments( $post_ID ) {

		// --<
		return $this->parser->get_sorted_comments( $post_ID );

	}

	/**
	 * Get paragraph number for a particular text signature.
	 *
	 * @since 3.4
	 *
	 * @param str $text_signature The text signature.
	 * @return int $num The position in text signature array.
	 */
	public function get_para_num( $text_signature ) {

		// Get position in array.
		$num = array_search( $text_signature, $this->db->get_text_sigs() );

		// --<
		return $num + 1;

	}

	/**
	 * Get text signature for a particular paragraph number.
	 *
	 * @since 3.4
	 *
	 * @param int $para_num The paragraph number in a post.
	 * @return str $text_signature The text signature.
	 */
	public function get_text_signature( $para_num ) {

		// Get text sigs.
		$sigs = $this->db->get_text_sigs();

		// Get value at that position in array.
		$text_sig = ( isset( $sigs[ $para_num - 1 ] ) ) ? $sigs[ $para_num - 1 ] : '';

		// --<
		return $text_sig;

	}

	/**
	 * Get a link to a "special" page.
	 *
	 * @since 3.4
	 *
	 * @param str $page_type The CommentPress Core name of a special page.
	 * @return str $link THe HTML link to that page.
	 */
	public function get_page_link( $page_type = 'cp_all_comments_page' ) {

		// Access globals.
		global $post;

		// Init.
		$link = '';

		// Get page ID.
		$page_id = $this->db->option_get( $page_type );

		// Do we have a page?
		if ( $page_id != '' ) {

			// Get page.
			$page = get_post( $page_id );

			// Is it the current page?
			$active = '';
			if ( isset( $post ) && $page->ID == $post->ID ) {
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

			// Let plugins override titles.
			$title = apply_filters( 'commentpress_page_link_title', $link_title, $page_type );

			// Show link.
			$link = '<li' . $active . '><a href="' . $url . '" id="btn_' . $button . '" class="css_btn" title="' . $title . '">' . $title . '</a></li>' . "\n";

		}

		// --<
		return $link;

	}

	/**
	 * Get the URL for a "special" page.
	 *
	 * @since 3.4
	 *
	 * @param str $page_type The CommentPress Core name of a special page.
	 * @return str $url The URL of that page.
	 */
	public function get_page_url( $page_type = 'cp_all_comments_page' ) {

		// Init.
		$url = '';

		// Get page ID.
		$page_id = $this->db->option_get( $page_type );

		// Do we have a page?
		if ( $page_id != '' ) {

			// Get page.
			$page = get_post( $page_id );

			// Get link.
			$url = get_permalink( $page );

		}

		// --<
		return $url;

	}

	/**
	 * Get book cover.
	 *
	 * @since 3.4
	 */
	public function get_book_cover() {

		// Get image SRC.
		$src = $this->db->option_get( 'cp_book_picture' );

		// Get link URL.
		$url = $this->db->option_get( 'cp_book_picture_link' );

		// --<
		return $this->display->get_linked_image( $src, $url );

	}

	/**
	 * Utility to check for presence of Theme My Login.
	 *
	 * @since 3.4
	 *
	 * @return bool $success True if TML page, false otherwise
	 */
	public function is_theme_my_login_page() {

		// Access page.
		global $post;

		// Compat with Theme My Login.
		if (
			is_page() &&
			! $this->db->is_special_page() &&
			$post->post_name == 'login' &&
			$post->post_content == '[theme-my-login]'
		) {

			// --<
			return true;

		}

		// --<
		return false;

	}

	/**
	 * Utility to check for presence of Members List.
	 *
	 * @since 3.4.7
	 *
	 * @return bool $success True if is Members List page, false otherwise.
	 */
	public function is_members_list_page() {

		// Access page.
		global $post;

		// Compat with Members List.
		if (
			is_page() &&
			! $this->db->is_special_page() &&
			( strstr( $post->post_content, '[members-list' ) !== false )
		) {

			// --<
			return true;

		}

		// --<
		return false;

	}

	/**
	 * Utility to check for presence of Subscribe to Comments Reloaded.
	 *
	 * @since 3.5.9
	 *
	 * @return bool $success True if "Subscribe to Comments Reloaded" page, false otherwise.
	 */
	public function is_subscribe_to_comments_reloaded_page() {

		// Access page.
		global $post;

		// Compat with Subscribe to Comments Reloaded.
		if (
			is_page() &&
			! $this->db->is_special_page() &&
			$post->ID == '9999999' &&
			$post->guid == get_bloginfo( 'url' ) . '/?page_id=9999999'
		) {

			// --<
			return true;

		}

		// --<
		return false;

	}

	/**
	 * Override the comment reply script that BuddyPress Docs loads.
	 *
	 * @since 3.5.9
	 */
	public function bp_docs_loaded() {

		// Dequeue offending script (after BuddyPress Docs runs its enqueuing).
		add_action( 'wp_enqueue_scripts', [ $this, 'bp_docs_dequeue_scripts' ], 20 );

	}

	/**
	 * Override the comment reply script that BuddyPress Docs loads.
	 *
	 * @since 3.5.9
	 */
	public function bp_docs_dequeue_scripts() {

		// Dequeue offending script.
		wp_dequeue_script( 'comment-reply' );

	}

	/**
	 * Override the comments tempate for BuddyPress Docs.
	 *
	 * @since 3.4
	 *
	 * @param str $path The existing path to the template.
	 * @param str $original_path The original path to the template.
	 * @return str $path The modified path to the template.
	 */
	public function bp_docs_comment_tempate( $path, $original_path ) {

		// If on BuddyPress root site.
		if ( bp_is_root_blog() ) {

			// Override default link name.
			return $original_path;

		}

		// Pass through.
		return $path;

	}

	/**
	 * Override the Featured Comments behaviour.
	 *
	 * @since 3.4.8
	 */
	public function featured_comments_override() {

		// Is the plugin available?
		if ( ! function_exists( 'wp_featured_comments_load' ) ) {
			return;
		}

		// Get instance.
		$fc = wp_featured_comments_load();

		// Remove comment_text filter.
		remove_filter( 'comment_text', [ $fc, 'comment_text' ], 10 );

		// Get the plugin markup in the comment edit section.
		add_filter( 'cp_comment_edit_link', [ $this, 'featured_comments_markup' ], 100, 2 );

	}

	/**
	 * Get the Featured Comments link markup.
	 *
	 * @since 3.4.8
	 *
	 * @param str $editlink The existing HTML link.
	 * @param array $comment The comment data.
	 * @return str $editlink The modified HTML link.
	 */
	public function featured_comments_markup( $editlink, $comment ) {

		// Is the plugin available?
		if ( ! function_exists( 'wp_featured_comments_load' ) ) {
			return $editlink;
		}

		// Get instance.
		$fc = wp_featured_comments_load();

		// Get markup.
		return $editlink . $fc->comment_text( '' );

	}

	/**
	 * Return the name of the default sidebar.
	 *
	 * @since 3.4
	 *
	 * @return str $return The code for the default sidebar.
	 */
	public function get_default_sidebar() {

		/**
		 * Set sensible default sidebar, but allow overrides.
		 *
		 * @since 3.9.8
		 *
		 * @param str The default sidebar before any contextual modifications.
		 * @return str The modified sidebar before any contextual modifications.
		 */
		$return = apply_filters( 'commentpress_default_sidebar', 'activity' );

		// Is this a commentable page?
		if ( ! $this->is_commentable() ) {

			// No - we must use either 'activity' or 'toc'.
			if ( $this->db->option_exists( 'cp_sidebar_default' ) ) {

				// Get option (we don't need to look at the page meta in this case).
				$default = $this->db->option_get( 'cp_sidebar_default' );

				// Use it unless it's 'comments'.
				if ( $default != 'comments' ) {
					$return = $default;
				}

			}

			// --<
			return $return;

		}

		/*
		// Get CPTs.
		//$types = $this->get_commentable_cpts();

		// Testing what we do with CPTs.
		//if ( is_singular() || is_singular( $types ) ) {
		*/

		// Is it a commentable page?
		if ( is_singular() ) {

			// Some people have reported that db is not an object at this point -
			// though I cannot figure out how this might be occurring - so we
			// avoid the issue by checking if it is.
			if ( is_object( $this->db ) ) {

				// Is it a special page which have comments in page (or are not commentable)?
				if ( ! $this->db->is_special_page() ) {

					// Access page.
					global $post;

					// Either 'comments', 'activity' or 'toc'.
					if ( $this->db->option_exists( 'cp_sidebar_default' ) ) {

						// Get global option.
						$return = $this->db->option_get( 'cp_sidebar_default' );

						// Check if the post/page has a meta value.
						$key = '_cp_sidebar_default';

						// If the custom field already has a value.
						if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

							// Get it.
							$return = get_post_meta( $post->ID, $key, true );

						}

					}

					// --<
					return $return;

				}

			}

		}

		// Not singular - must be either activity or toc.
		if ( $this->db->option_exists( 'cp_sidebar_default' ) ) {

			// Override.
			$default = $this->db->option_get( 'cp_sidebar_default' );

			// Use it unless it's 'comments'.
			if ( $default != 'comments' ) {
				$return = $default;
			}

		}

		// --<
		return $return;

	}

	/**
	 * Get the order of the sidebars.
	 *
	 * @since 3.4
	 *
	 * @return array $order Sidebars in order of display.
	 */
	public function get_sidebar_order() {

		// Set default but allow overrides.
		$order = apply_filters(
			'cp_sidebar_tab_order',
			[ 'contents', 'comments', 'activity' ] // Default order.
		);

		// --<
		return $order;

	}

	/**
	 * Check if a page/post can be commented on.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_commentable True if commentable, false otherwise.
	 */
	public function is_commentable() {

		// Declare access to globals.
		global $post;

		// Not on signup pages.
		$script = isset( $_SERVER['SCRIPT_FILENAME'] ) ? wp_unslash( $_SERVER['SCRIPT_FILENAME'] ) : '';
		if ( is_multisite() && ! empty( $script ) ) {
			if ( 'wp-signup.php' == basename( $script ) ) {
				return false;
			}
			if ( 'wp-activate.php' == basename( $script ) ) {
				return false;
			}
		}

		// Not if we're not on a page/post and especially not if there's no post object.
		if ( ! is_singular() || ! is_object( $post ) ) {
			return false;
		}

		// CommentPress Core Special Pages special pages are not.
		if ( $this->db->is_special_page() ) {
			return false;
		}

		// BuddyPress special pages are not.
		if ( $this->is_buddypress_special_page() ) {
			return false;
		}

		// Theme My Login page is not.
		if ( $this->is_theme_my_login_page() ) {
			return false;
		}

		// Members List page is not.
		if ( $this->is_members_list_page() ) {
			return false;
		}

		// Subscribe to Comments Reloaded page is not.
		if ( $this->is_subscribe_to_comments_reloaded_page() ) {
			return false;
		}

		/**
		 * Filter comment allowed.
		 *
		 * @since 3.4
		 *
		 * @param bool $is_commentable True by default.
		 */
		return apply_filters( 'cp_is_commentable', true );

	}

	/**
	 * Check if user agent is mobile.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_mobile True if mobile OS, false otherwise.
	 */
	public function is_mobile() {

		// --<
		return $this->db->is_mobile();

	}

	/**
	 * Check if user agent is tablet.
	 *
	 * @since 3.4
	 *
	 * @return boolean $is_tablet True if tablet OS, false otherwise.
	 */
	public function is_tablet() {

		// --<
		return $this->db->is_tablet();

	}

	// -------------------------------------------------------------------------

	/**
	 * Utility to check for commentable CPT.
	 *
	 * @since 3.4
	 *
	 * @return str $types Array of post types.
	 */
	public function get_commentable_cpts() {

		// Init.
		$types = false;

		// NOTE: exactly how do we support CPTs?
		$args = [
			//'public'   => true,
			'_builtin' => false,
		];

		$output = 'names'; // Names or objects, note names is the default.
		$operator = 'and'; // 'and' or 'or'.

		// Get post types.
		$post_types = get_post_types( $args, $output, $operator );

		// Did we get any?
		if ( count( $post_types ) > 0 ) {

			// Init as array.
			$types = false;

			// Loop.
			foreach ( $post_types as $post_type ) {

				// Add name to array (is_singular expects this).
				$types[] = $post_type;

			}

		}

		// --<
		return $types;

	}

	/**
	 * Adds the formatter to the page/post metabox.
	 *
	 * @since 3.4
	 *
	 * @param object $post The WordPress post object.
	 */
	public function get_post_formatter_metabox( $post ) {

		// ---------------------------------------------------------------------
		// Override post formatter.
		// ---------------------------------------------------------------------

		// Do we have the option to choose blog type (new in 3.3.1)?
		if ( $this->db->option_exists( 'cp_blog_type' ) ) {

			// Define no types.
			$types = [];

			// Allow overrides.
			$types = apply_filters( 'cp_blog_type_options', $types );

			// If we get some from a plugin, for example.
			if ( ! empty( $types ) ) {

				// Define title.
				$type_title = __( 'Text Formatting', 'commentpress-core' );

				// Allow overrides.
				$type_title = apply_filters( 'cp_post_type_override_label', $type_title );

				// Label.
				echo '<div class="cp_post_type_override_wrapper">
				<p><strong><label for="cp_post_type_override">' . $type_title . '</label></strong></p>';

				// Construct options.
				$type_option_list = [];
				$n = 0;

				// Set key.
				$key = '_cp_post_type_override';

				// Default to current blog type.
				$value = $this->db->option_get( 'cp_blog_type' );

				// But, if the custom field has a value.
				if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

					// Get it.
					$value = get_post_meta( $post->ID, $key, true );

				}

				foreach ( $types as $type ) {
					if ( $n == $value ) {
						$type_option_list[] = '<option value="' . $n . '" selected="selected">' . $type . '</option>';
					} else {
						$type_option_list[] = '<option value="' . $n . '">' . $type . '</option>';
					}
					$n++;
				}
				$type_options = implode( "\n", $type_option_list );

				// Select.
				echo '
				<p>
				<select id="cp_post_type_override" name="cp_post_type_override">
					' . $type_options . '
				</select>
				</p>
				</div>
				';

			}

		}

	}

	/**
	 * Adds the default sidebar preference to the page/post metabox.
	 *
	 * @since 3.4
	 *
	 * @param object $post The WordPress post object.
	 */
	public function get_default_sidebar_metabox( $post ) {

		// Allow this to be disabled.
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) {
			return;
		}

		// ---------------------------------------------------------------------
		// Override post formatter.
		// ---------------------------------------------------------------------

		// Do we have the option to choose the default sidebar (new in 3.3.3)?
		if ( $this->db->option_exists( 'cp_sidebar_default' ) ) {

			// Show a title.
			echo '<div class="cp_sidebar_default_wrapper">
			<p><strong><label for="cp_sidebar_default">' . __( 'Default Sidebar', 'commentpress-core' ) . '</label></strong></p>';

			// Set key.
			$key = '_cp_sidebar_default';

			// Default to show.
			$sidebar = $this->db->option_get( 'cp_sidebar_default' );

			// If the custom field already has a value.
			if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

				// Get it.
				$sidebar = get_post_meta( $post->ID, $key, true );

			}

			// Select.
			echo '
			<p>
			<select id="cp_sidebar_default" name="cp_sidebar_default">
				<option value="toc" ' . ( ( $sidebar == 'toc' ) ? ' selected="selected"' : '' ) . '>' . __( 'Contents', 'commentpress-core' ) . '</option>
				<option value="activity" ' . ( ( $sidebar == 'activity' ) ? ' selected="selected"' : '' ) . '>' . __( 'Activity', 'commentpress-core' ) . '</option>
				<option value="comments" ' . ( ( $sidebar == 'comments' ) ? ' selected="selected"' : '' ) . '>' . __( 'Comments', 'commentpress-core' ) . '</option>
			</select>
			</p>
			</div>
			';

		}

	}

	/**
	 * Adds the paragraph numbering preference to the page/post metabox.
	 *
	 * @since 3.4
	 *
	 * @param object $post The WordPress post object.
	 */
	public function get_para_numbering_metabox( $post ) {

		// Show a title.
		echo '<div class="cp_starting_para_number_wrapper">
		<p><strong><label for="cp_starting_para_number">' . __( 'Starting Paragraph Number', 'commentpress-core' ) . '</label></strong></p>';

		// Set key.
		$key = '_cp_starting_para_number';

		// Default to start with para 1.
		$num = 1;

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) !== '' ) {

			// Get it.
			$num = get_post_meta( $post->ID, $key, true );

		}

		// Select.
		echo '
		<p>
		<input type="text" id="cp_starting_para_number" name="cp_starting_para_number" value="' . $num . '" />
		</p>
		</div>
		';

	}

}
