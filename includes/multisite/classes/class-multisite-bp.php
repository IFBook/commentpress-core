<?php
/**
 * CommentPress Multisite BuddyPress class.
 *
 * Handles BuddyPress compatibility in WordPress Multisite contexts.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Multisite BuddyPress Class.
 *
 * This class encapsulates BuddyPress compatibility.
 *
 * @since 3.3
 */
class CommentPress_Multisite_BuddyPress {

	/**
	 * Multisite loader object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $multisite The multisite loader object.
	 */
	public $multisite;

	/**
	 * BuddyPress Group Blog compatibility object.
	 *
	 * @since 3.3
	 * @access public
	 * @var object $groupblog The BuddyPress Group Blog object reference.
	 */
	public $groupblog;

	/**
	 * Classes directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $classes_path Relative path to the classes directory.
	 */
	private $classes_path = 'includes/multisite/classes/';

	/**
	 * Parts template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $parts_path Relative path to the Parts directory.
	 */
	private $parts_path = 'includes/multisite/assets/templates/buddypress/parts/';

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param object $multisite Reference to the multisite loader object.
	 */
	public function __construct( $multisite ) {

		// Store reference to multisite loader object.
		$this->multisite = $multisite;

		// Initialise after BuddyPress has set up its components.
		add_action( 'bp_include', [ $this, 'initialise' ], 50 );

	}

	/**
	 * Intialises this object.
	 *
	 * Runs just after BuddyPress has set up its components.
	 *
	 * @since 3.3
	 */
	public function initialise() {

		// Only do this once.
		static $done;
		if ( isset( $done ) && true === $done ) {
			return;
		}

		// Bootstrap this object.
		$this->include_files();
		$this->setup_objects();
		$this->register_hooks();

		/**
		 * Fires when BuddyPress has loaded.
		 *
		 * Used internally to bootstrap objects.
		 *
		 * @since 4.0
		 */
		do_action( 'commentpress/multisite/bp/loaded' );

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
		require_once COMMENTPRESS_PLUGIN_PATH . $this->classes_path . 'class-multisite-bp-groupblog.php';

	}

	/**
	 * Sets up the objects in this class.
	 *
	 * @since 4.0
	 */
	public function setup_objects() {

		// Initialise objects.
		$this->groupblog = new CommentPress_Multisite_BuddyPress_Groupblog( $this );

	}

	/**
	 * Register hooks.
	 *
	 * @since 3.3
	 * @since 4.0 Renamed.
	 */
	public function register_hooks() {

		// Separate callbacks into descriptive methods.
		$this->register_hooks_registration();
		$this->register_hooks_activity();
		$this->register_hooks_text();

		/*
		// Register any public styles and scripts.
		add_action( 'wp_enqueue_scripts', [ $this, 'scripts_enqueue' ], 20 );
		*/

	}

	/**
	 * Registers BuddyPress Site Registration hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_registration() {

		// Add form elements to the BuddyPress Blog Signup Form.
		add_action( 'bp_blog_details_fields', [ $this, 'site_signup_form_elements_add' ] );

		// Save meta for Blog Signup Form submissions.
		add_filter( 'signup_site_meta', [ $this, 'site_signup_form_meta_add' ], 10, 1 );

		// Activate Blog-specific CommentPress Core.
		if ( version_compare( $GLOBALS['wp_version'], '5.1.0', '>=' ) ) {
			add_action( 'wp_initialize_site', [ $this, 'site_initialise' ], 20, 2 );
		} else {
			add_action( 'wpmu_new_blog', [ $this, 'site_initialise_legacy' ], 20, 6 );
		}

	}

	/**
	 * Registers BuddyPress Activity hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_activity() {

		// Bail if the Activity Component is not active.
		if ( ! bp_is_active( 'activity' ) ) {
			return;
		}

		// Register "page" as a Post Type that BuddyPress records Comment Activity for.
		add_action( 'bp_init', [ $this, 'activity_register_comment_tracking_on_pages' ], 100 );

		// Add "page" to the Post Types that BuddyPress records Comment Activity for.
		add_filter( 'bp_blogs_record_comment_post_types', [ $this, 'activity_record_comments_on_pages' ], 10, 1 );

		// Add some tags to the allowed tags in Activities.
		add_filter( 'bp_activity_allowed_tags', [ $this, 'activity_allowed_tags' ], 20 );

		// Make sure "Allow activity stream commenting on Blog and forum posts" is disabled.
		add_action( 'bp_disable_blogforum_comments', [ $this, 'activity_blogforum_comments_disable' ], 20, 1 );

	}


	/**
	 * Registers text filtering hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_text() {

		// Override CommentPress Core "Create New Document" text.
		add_filter( 'cp_user_links_new_site_title', [ $this, 'text_title_new_site_filter' ], 21 );
		add_filter( 'cp_site_directory_link_title', [ $this, 'text_title_new_site_filter' ], 21 );
		add_filter( 'cp_register_new_site_page_title', [ $this, 'text_title_new_site_filter' ], 21 );

		// Override CommentPress Core "Welcome Page".
		add_filter( 'cp_nav_title_page_title', [ $this, 'text_title_title_page_filter' ], 20 );

		// Override the name of the button on the BuddyPress "blogs" screen.
		// To override this, just add the same filter with a priority of 21 or greater.
		add_filter( 'bp_get_blogs_visit_blog_button', [ $this, 'text_visit_blog_button_filter' ], 20 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds the CommentPress form elements to the BuddyPress Blog Signup Form.
	 *
	 * @since 3.3
	 *
	 * @param array $errors The errors generated previously.
	 */
	public function site_signup_form_elements_add( $errors ) {

		// Skip if it's the BuddyPress Groupblog signup form.
		if ( bp_is_groups_component() ) {
			return;
		}

		// Get force option.
		$forced = $this->multisite->db->setting_get( 'cpmu_force_commentpress' );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-bp-signup.php';

	}

	/**
	 * Saves metadata when the BuddyPress Blog Signup Form is submitted.
	 *
	 * The "signup_site_meta" filter has been available since WordPress 4.8.0.
	 *
	 * @since 4.0
	 *
	 * @param array $meta Signup meta data. Default empty array.
	 * @return array $meta The modified signup meta data.
	 */
	public function site_signup_form_meta_add( $meta ) {

		// Bail early if in a BuddyPress Groups context.
		if ( bp_is_groups_component() ) {
			return $meta;
		}

		// Init CommentPress metadata.
		$metadata = [];

		// Get "CommentPress enabled on all Sites" setting.
		$forced = $this->multisite->sites->setting_forced_get();

		// When not forced.
		if ( ! $forced ) {

			// Bail if our checkbox variable is not in POST.
			// phpcs:ignore WordPress.Security.NonceVerification.Missing
			$cpmu_new_blog = isset( $_POST['cpbp-new-blog'] ) ? sanitize_text_field( wp_unslash( $_POST['cpbp-new-blog'] ) ) : '';
			if ( empty( $cpmu_new_blog ) ) {
				return $meta;
			}

		}

		// Add flag to our meta.
		$metadata['enable'] = 'y';

		// Maybe add our meta.
		if ( ! empty( $metadata ) ) {
			$meta['commentpress-bp'] = $metadata;
		}

		// --<
		return $meta;

	}

	/**
	 * Initialises a new site.
	 *
	 * The "wp_initialize_site" action has been available since WordPress 5.1.0.
	 *
	 * @since 3.3
	 * @param WP_Site $new_site The new site object.
	 * @param array   $args The array of initialization arguments.
	 */
	public function site_initialise( $new_site, $args ) {

		// Bail early if in a BuddyPress Groups context.
		if ( bp_is_groups_component() ) {
			return;
		}

		// Bail if none of our meta is present.
		if ( empty( $args['options']['commentpress-bp'] ) ) {
			return;
		}

		// Get "CommentPress enabled on all Sites" setting.
		$forced = $this->multisite->sites->setting_forced_get();

		// Bail if not forced and "Enable CommentPress" checkbox was not checked.
		if ( ! $forced ) {
			if ( empty( $args['options']['commentpress-bp']['enable'] ) ) {
				return;
			}
			if ( 'y' !== $args['options']['commentpress-bp']['enable'] ) {
				return;
			}
		}

		// Switch to the site.
		switch_to_blog( $new_site->blog_id );

		// Activate CommentPress Core.
		$this->multisite->site->core_activate();

		// Switch back.
		restore_current_blog();

	}

	/**
	 * Initialises a new blog.
	 *
	 * The "wpmu_new_blog" action has been deprecated since WordPress 5.1.0.
	 *
	 * @since 3.3
	 *
	 * @param int   $blog_id The numeric ID of the WordPress Blog.
	 * @param int   $user_id The numeric ID of the WordPress User.
	 * @param str   $domain The domain of the WordPress Blog.
	 * @param str   $path The path of the WordPress Blog.
	 * @param int   $site_id The numeric ID of the WordPress parent Site.
	 * @param array $meta The meta data of the WordPress Blog.
	 */
	public function site_initialise_legacy( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		// Bail early if in a BuddyPress Groups context.
		if ( bp_is_groups_component() ) {
			return;
		}

		// Bail if none of our meta is present.
		if ( empty( $meta['commentpress'] ) ) {
			return;
		}

		// Get "CommentPress enabled on all Sites" setting.
		$forced = $this->multisite->sites->setting_forced_get();

		// Bail if not forced and "Enable CommentPress" checkbox was not checked.
		if ( ! $forced ) {
			if ( empty( $meta['commentpress']['enable'] ) ) {
				return;
			}
			if ( 'y' !== $meta['commentpress']['enable'] ) {
				return;
			}
		}

		// Switch to the site.
		switch_to_blog( $blog_id );

		// Activate CommentPress Core.
		$this->multisite->site->core_activate();

		// Switch back.
		restore_current_blog();

	}

	// -------------------------------------------------------------------------

	/**
	 * Registers "page" as a Post Type that BuddyPress records Comment Activity for.
	 *
	 * @since 3.9.3
	 */
	public function activity_register_comment_tracking_on_pages() {

		// Add support for "page" Post Type.
		add_post_type_support( 'page', 'buddypress-activity' );

		// Define tracking args.
		$args = [
			'action_id'                         => 'new_page',
			'bp_activity_admin_filter'          => __( 'Published a new page', 'commentpress-core' ),
			'bp_activity_front_filter'          => __( 'Pages', 'commentpress-core' ),
			'bp_activity_new_post'              => __( '%1$s posted a new <a href="%2$s">page</a>', 'commentpress-core' ),
			'bp_activity_new_post_ms'           => __( '%1$s posted a new <a href="%2$s">page</a>, on the site %3$s', 'commentpress-core' ),
			'contexts'                          => [ 'activity', 'member' ],
			'comment_action_id'                 => 'new_blog_comment',
			'bp_activity_comments_admin_filter' => __( 'Commented on a page', 'commentpress-core' ),
			'bp_activity_comments_front_filter' => __( 'Comments', 'commentpress-core' ),
			'bp_activity_new_comment'           => __( '%1$s commented on the <a href="%2$s">page</a>', 'commentpress-core' ),
			'bp_activity_new_comment_ms'        => __( '%1$s commented on the <a href="%2$s">page</a>, on the site %3$s', 'commentpress-core' ),
			'position'                          => 100,
		];

		// Apply tracking args.
		bp_activity_set_post_type_tracking_args( 'page', $args );

	}

	/**
	 * Adds "page" to the Post Types that BuddyPress records Comment Activity for.
	 *
	 * @since 3.3
	 *
	 * @param array $post_types The existing array of Post Types.
	 * @return array $post_types The modified array of Post Types.
	 */
	public function activity_record_comments_on_pages( $post_types ) {

		// Bail if in the array already.
		if ( in_array( 'page', $post_types ) ) {
			return $post_types;
		}

		// Add the "page" Post Type.
		$post_types[] = 'page';

		// --<
		return $post_types;

	}

	/**
	 * Allow our TinyMCE Comment markup in Activity content.
	 *
	 * @since 3.3
	 *
	 * @param array $activity_allowedtags The array of tags allowed in an Activity item.
	 * @return array $activity_allowedtags The modified array of tags allowed in an Activity item.
	 */
	public function activity_allowed_tags( $activity_allowedtags ) {

		// Lists.
		$activity_allowedtags['ul'] = [];
		$activity_allowedtags['ol'] = [];
		$activity_allowedtags['li'] = [];

		// Bold.
		$activity_allowedtags['strong'] = [];

		// Italic.
		$activity_allowedtags['em'] = [];

		// Underline.
		$activity_allowedtags['span']['style'] = [];

		// --<
		return $activity_allowedtags;

	}

	/**
	 * Disable Activity Stream commenting on Blog and Forum Posts.
	 *
	 * Comment sync is disabled because parent Activity items may not be in the
	 * same Group as the Comment.
	 *
	 * Furthermore, CommentPress Core Comments should be read in context rather
	 * than appearing as if globally attached to the Post or Page.
	 *
	 * @since 3.3
	 *
	 * @param bool $is_disabled The BuddyPress setting that determines blogforum sync.
	 * @return bool $is_disabled The modified value that determines blogforum sync.
	 */
	public function activity_blogforum_comments_disable( $is_disabled ) {

		// Don't mess with admin.
		if ( is_admin() ) {
			return $is_disabled;
		}

		// Get current Blog ID.
		$blog_id = get_current_blog_id();

		// If it's CommentPress-enabled, disable sync.
		$legacy_check = false;
		if ( $this->multisite->site->is_commentpress( $blog_id, $legacy_check ) ) {
			return true;
		}

		// Pass through.
		return $is_disabled;

	}

	// -------------------------------------------------------------------------

	/**
	 * Overrides the title of the "Create a new document" link.
	 *
	 * @since 3.3
	 *
	 * @param str $title The existing title of the "Create a new document" link.
	 * @return str $title The overridden title of the "Create a new document" link.
	 */
	public function text_title_new_site_filter( $title ) {

		/**
		 * Filters the title of the "Create a new document" link.
		 *
		 * @since 3.3
		 *
		 * @param string The default title of the "Create a new document" link.
		 */
		return apply_filters( 'cpmu_bp_create_new_site_title', __( 'Create a New Site', 'commentpress-core' ) );

	}

	/**
	 * Overrides the title of the CommentPress Core "Welcome Page".
	 *
	 * @since 3.3
	 *
	 * @param str $title The existing title of the "Welcome Page".
	 * @return str $title The modified title of  the "Welcome Page".
	 */
	public function text_title_title_page_filter( $title ) {

		// Bail if main BuddyPress Site.
		if ( bp_is_root_blog() ) {
			return $title;
		}

		/**
		 * Filters the link name of a CommentPress-enabled the Group Blog.
		 *
		 * @since 3.3
		 *
		 * @param string The default link name of the Group Blog.
		 */
		return apply_filters( 'cpmu_bp_nav_title_page_title', __( 'Document Home Page', 'commentpress-core' ) );

	}

	/**
	 * Overrides the name of the button on the BuddyPress "blogs" screen.
	 *
	 * The incoming button array looks like this:
	 *
	 * [id] => visit_blog
	 * [component] => blogs
	 * [must_be_logged_in] =>
	 * [block_self] =>
	 * [wrapper_class] => blog-button visit
	 * [link_href] => http://domain/site-slug/
	 * [link_class] => blog-button visit
	 * [link_text] => Visit Site
	 * [link_title] => Visit Site
	 *
	 * @since 3.3
	 *
	 * @param array $button The existing Blogs button data.
	 * @return array $button The existing Blogs button data.
	 */
	public function text_visit_blog_button_filter( $button ) {

		// Set default.
		$site_type = 'blog';

		// Access BuddyPress Blogs global.
		global $blogs_template;

		// Check if this Blog is CommentPress-enabled.
		if ( ! empty( $blogs_template->blog->blog_id ) ) {
			$legacy_check = false;
			if ( $this->multisite->site->is_commentpress( $blogs_template->blog->blog_id, $legacy_check ) ) {
				$site_type = 'commentpress';
			}
		}

		// Switch by Site Type.
		switch ( $site_type ) {

			// Standard sub-site.
			case 'blog':
				$label = __( 'View Site', 'commentpress-core' );
				break;

			// CommentPress Core sub-site.
			case 'commentpress':
				$label = __( 'View Document', 'commentpress-core' );
				break;

		}

		/**
		 * Filters the "Visit Blog" button label.
		 *
		 * @since 3.3
		 *
		 * @param str $label The text of the "Visit Blog" button label.
		 */
		$label = apply_filters_deprecated( 'cp_get_blogs_visit_blog_button', [ $label ], 'commentpress/multisite/bp/button/visit_blog/label' );

		/**
		 * Filters the "Visit Blog" button label.
		 *
		 * @since 4.0
		 *
		 * @param str $label The text of the "Visit Blog" button label.
		 * @param str $site_type The type of Blog.
		 */
		$label = apply_filters( 'commentpress/multisite/bp/button/visit_blog/label', $label, $site_type );

		// Apply label.
		$button['link_text']  = $label;
		$button['link_title'] = $label;

		// --<
		return $button;

	}

	// -------------------------------------------------------------------------
	// Unused methods.
	// -------------------------------------------------------------------------

	/**
	 * Enqueues any styles and scripts needed by a CommentPress theme.
	 *
	 * Disabled since this doesn't seem to do anything any more.
	 *
	 * @since 3.3
	 */
	public function scripts_enqueue() {

		// Dequeue BP Template Pack CSS, even if queued.
		wp_dequeue_style( 'bp' );

	}

}
