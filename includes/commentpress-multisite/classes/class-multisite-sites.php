<?php
/**
 * CommentPress Multisite Sites class.
 *
 * Handles functionality related to Sites in WordPress Multisite.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Multisite Sites Class.
 *
 * This class functionality related to Sites in WordPress Multisite.
 *
 * @since 3.3
 */
class CommentPress_Multisite_Sites {

	/**
	 * Multisite loader object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $multisite The multisite loader object.
	 */
	public $multisite;

	/**
	 * CommentPress Core enabled on all Sites flag.
	 *
	 * @since 3.3
	 * @access public
	 * @var str $cpmu_bp_force_commentpress The enabled on all Sites flag ('0' or '1').
	 */
	public $cpmu_force_commentpress = '0';

	/**
	 * Default Title Page content on new Sites (not yet used).
	 *
	 * @since 3.3
	 * @access public
	 * @var str $cpmu_title_page_content The default Title Page content.
	 */
	public $cpmu_title_page_content = '';

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

		// Init when the multisite plugin is fully loaded.
		add_action( 'commentpress/multisite/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Set up all items associated with this object.
	 *
	 * @since 3.3
	 */
	public function initialise() {

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.3
	 */
	public function register_hooks() {

		// Add form elements to signup form.
		add_action( 'signup_blogform', [ $this, 'signup_blogform' ] );

		// Add callback for Signup Page to include sidebar.
		add_action( 'after_signup_form', [ $this, 'after_signup_form' ], 20 );

		// If subdirectory install.
		if ( ! is_subdomain_install() ) {

			// Add filter for reserved CommentPress Core Special Page names.
			add_filter( 'subdirectory_reserved_names', [ $this, 'add_reserved_names' ] );

		}

		// Activate Blog-specific CommentPress Core plugin.
		add_action( 'wpmu_new_blog', [ $this, 'wpmu_new_blog' ], 12, 6 );

		// Add options to reset array.
		add_filter( 'cpmu_db_options_get_defaults', [ $this, 'get_default_settings' ], 20, 1 );

		// Hook into Network Settings form update.
		add_action( 'commentpress/multisite/settings/network/form_submitted/pre', [ $this, 'network_admin_update' ] );

		/*
		// Override Title Page content.
		add_filter( 'cp_title_page_content', [ $this, 'get_title_page_content' ] );
		*/

	}

	// -------------------------------------------------------------------------

	/**
	 * Hook into the Blog signup form.
	 *
	 * @since 3.3
	 *
	 * @param array $errors The previously encountered errors.
	 */
	public function signup_blogform( $errors ) {

		// Only apply to WordPress signup form (not the BuddyPress one).
		if ( is_object( $this->multisite->bp ) ) {
			return;
		}

		// Get force option.
		$forced = $this->multisite->db->option_get( 'cpmu_force_commentpress' );

		// Are we force-enabling CommentPress Core?
		if ( $forced ) {

			// Set hidden element.
			$forced_html = '
			<input type="hidden" value="1" id="cpmu-new-blog" name="cpmu-new-blog" />
			';

			// Define text, but allow overrides.
			$text = apply_filters(
				'cp_multisite_options_signup_text_forced',
				__( 'Select the options for your new CommentPress document.', 'commentpress-core' )
			);

		} else {

			// Set checkbox.
			$forced_html = '
			<div class="checkbox">
				<label for="cpmu-new-blog"><input type="checkbox" value="1" id="cpmu-new-blog" name="cpmu-new-blog" /> ' . __( 'Enable CommentPress', 'commentpress-core' ) . '</label>
			</div>
			';

			// Define text, but allow overrides.
			$text = apply_filters(
				'cp_multisite_options_signup_text',
				__( 'Do you want to make the new site a CommentPress document?', 'commentpress-core' )
			);

		}

		// Get Blog Type element.
		$type_html = $this->get_blogtype();

		// Construct form.
		$form = '

		<br />
		<div id="cp-multisite-options">

			<h3>' . __( 'CommentPress:', 'commentpress-core' ) . '</h3>

			<p>' . $text . '</p>

			' . $forced_html . '

			' . $type_html . '

		</div>

		';

		echo $form;

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

	// -------------------------------------------------------------------------

	/**
	 * Add reserved names.
	 *
	 * @since 3.4
	 *
	 * @param array $reserved_names The existing list of illegal names.
	 * @return array $reserved_names The modified list of illegal names.
	 */
	public function add_reserved_names( $reserved_names ) {

		// Add Special Page slugs.
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
	 * Hook into wpmu_new_blog and target plugins to be activated.
	 *
	 * @since 3.3
	 *
	 * @param int $blog_id The numeric ID of the WordPress Blog.
	 * @param int $user_id The numeric ID of the WordPress User.
	 * @param str $domain The domain of the WordPress Blog.
	 * @param str $path The path of the WordPress Blog.
	 * @param int $site_id The numeric ID of the WordPress parent Site.
	 * @param array $meta The meta data of the WordPress Blog.
	 */
	public function wpmu_new_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		// Test for presence of our checkbox variable in _POST.
		$cpmu_new_blog = isset( $_POST['cpmu-new-blog'] ) ? sanitize_text_field( wp_unslash( $_POST['cpmu-new-blog'] ) ) : '';
		if ( $cpmu_new_blog == '1' ) {

			// Hand off to private method.
			$this->create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta );

		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Create a Blog.
	 *
	 * @since 3.3
	 *
	 * @param int $blog_id The numeric ID of the WordPress Blog.
	 * @param int $user_id The numeric ID of the WordPress User.
	 * @param str $domain The domain of the WordPress Blog.
	 * @param str $path The path of the WordPress Blog.
	 * @param int $site_id The numeric ID of the WordPress parent Site.
	 * @param array $meta The meta data of the WordPress Blog.
	 */
	private function create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		// Wpmu_new_blog calls this *after* restore_current_blog, so we need to do it again.
		switch_to_blog( $blog_id );

		// Activate CommentPress Core.
		$this->multisite->db->install_commentpress();

		// Switch back.
		restore_current_blog();

	}

	/**
	 * Get Blog Type form elements.
	 *
	 * @since 3.3
	 *
	 * @return str $type_html The HTML form element.
	 */
	public function get_blogtype() {

		// Init.
		$type_html = '';

		// Get data.
		$type = $this->multisite->db->get_blogtype_data();

		// Show if we have type data.
		if ( ! empty( $type ) ) {
			$type_html = '<div class="dropdown">
				<label for="cp_blog_type">' . $type['label'] . '</label> <select id="cp_blog_type" name="cp_blog_type">
					' . $type['element'] . '
				</select>
			</div>';
		}

		// --<
		return $type_html;

	}

	/**
	 * Get default Multisite-related settings.
	 *
	 * @since 3.3
	 *
	 * @param array $existing_options The existing options.
	 * @return array $existing_options The modified options.
	 */
	public function get_default_settings( $existing_options ) {

		// Default Multisite options.
		$defaults = [
			'cpmu_force_commentpress' => $this->cpmu_force_commentpress,
			//'cpmu_title_page_content' => $this->cpmu_title_page_content,
		];

		/**
		 * Allow overrides and additions.
		 *
		 * @since 3.3
		 *
		 * @param array $defaults The existing array of defaults.
		 * @return array $defaults The modified array of defaults.
		 */
		$defaults = apply_filters( 'cpmu_multisite_options_get_defaults', $defaults );

		// Return options array.
		return array_merge( $existing_options, $defaults );

	}

	/**
	 * Hook into Network form update.
	 *
	 * @since 3.3
	 */
	public function network_admin_update() {

		// Check that we trust the source of the data.
		check_admin_referer( 'cpmu_admin_action', 'cpmu_nonce' );

		// Get "force CommentPress" value.
		$cpmu_force_commentpress = isset( $_POST['cpmu_force_commentpress'] ) ?
			sanitize_text_field( wp_unslash( $_POST['cpmu_force_commentpress'] ) ) :
			'0';

		// Set "force all new Sites to be CommentPress Core-enabled" option.
		$this->multisite->db->option_set( 'cpmu_force_commentpress', ( $cpmu_force_commentpress ? 1 : 0 ) );

		/*
		// Get "Default Title Page content" value.
		$cpmu_title_page_content = isset( $_POST['cpmu_title_page_content'] ) ?
			sanitize_text_field( wp_unslash( $_POST['cpmu_title_page_content'] ) ) :
			$this->get_default_title_page_content();

		// Set "Default Title Page content" option.
		$this->multisite->db->option_set( 'cpmu_title_page_content', $cpmu_title_page_content );
		*/

	}

	/**
	 * Get default Title Page content, if set.
	 *
	 * Do we want to enable this when we enable the Admin Page editor?
	 *
	 * @since 3.3
	 *
	 * @param str $content The existing content.
	 * @return str $content The modified content.
	 */
	public function get_title_page_content( $content ) {

		// Get content.
		$overridden_content = stripslashes( $this->multisite->db->option_get( 'cpmu_title_page_content' ) );

		// Override if different to what's been passed.
		if ( $content != $overridden_content ) {
			$content = $overridden_content;
		}

		// --<
		return $content;

	}

	/**
	 * Get default Title Page content.
	 *
	 * @since 3.3
	 *
	 * @return str $content The default Title Page content.
	 */
	public function get_default_title_page_content() {

		// --<
		return __(
			'Welcome to your new CommentPress site, which allows your readers to comment paragraph-by-paragraph or line-by-line in the margins of a text. Annotate, gloss, workshop, debate: with CommentPress you can do all of these things on a finer-grained level, turning a document into a conversation.

This is your title page. Edit it to suit your needs. It has been automatically set as your homepage but if you want another page as your homepage, set it in <em>WordPress</em> &#8594; <em>Settings</em> &#8594; <em>Reading</em>.

You can also set a number of options in <em>WordPress</em> &#8594; <em>Settings</em> &#8594; <em>CommentPress</em> to make the site work the way you want it to. Use the Theme Customizer to change the way your site looks in <em>WordPress</em> &#8594; <em>Appearance</em> &#8594; <em>Customize</em>. For help with structuring, formatting and reading text in CommentPress, please refer to the <a href="http://www.futureofthebook.org/commentpress/">CommentPress website</a>.',
			'commentpress-core'
		);

	}

}
