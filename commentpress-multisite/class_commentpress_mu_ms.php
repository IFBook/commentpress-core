<?php
/**
 * CommentPress Core for Multisite class.
 *
 * Handles WordPress Multisite compatibility.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core WordPress Multisite Class.
 *
 * This class encapsulates WordPress Multisite compatibility.
 *
 * @since 3.3
 */
class CommentPress_Multisite_WordPress {

	/**
	 * Multisite plugin object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $ms_loader The multisite plugin object.
	 */
	public $ms_loader;

	/**
	 * CommentPress Core enabled on all sites flag.
	 *
	 * @since 3.3
	 * @access public
	 * @var str $cpmu_bp_force_commentpress The enabled on all sites flag ('0' or '1').
	 */
	public $cpmu_force_commentpress = '0';

	/**
	 * Default title page content on new sites (not yet used).
	 *
	 * @since 3.3
	 * @access public
	 * @var str $cpmu_title_page_content The default title page content.
	 */
	public $cpmu_title_page_content = '';

	/**
	 * Allow translation workflow flag.
	 *
	 * @since 3.3
	 * @access public
	 * @var str $cpmu_disable_translation_workflow The translation workflow allowed flag ('0' or '1').
	 */
	public $cpmu_disable_translation_workflow = '1';

	/**
	 * Constructor.
	 *
	 * @since 3.3
	 *
	 * @param object $ms_loader Reference to the multisite plugin object.
	 */
	public function __construct( $ms_loader ) {

		// Store reference to multisite plugin object.
		$this->ms_loader = $ms_loader;

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

		// Activate blog-specific CommentPress Core plugin.
		add_action( 'wpmu_new_blog', [ $this, 'wpmu_new_blog' ], 12, 6 );

		// Enable/disable workflow sitewide.
		add_filter( 'cp_class_commentpress_workflow_enabled', [ $this, 'get_workflow_enabled' ] );

		// Is this the back end?
		if ( is_admin() ) {

			// Add options to reset array.
			add_filter( 'cpmu_db_options_get_defaults', [ $this, 'get_default_settings' ], 20, 1 );

			// Hook into Network Settings form update.
			add_action( 'commentpress/multisite/settings/network/form_submitted/pre', [ $this, 'network_admin_update' ] );

		} else {

			// Register any public styles.
			add_action( 'wp_enqueue_scripts', [ $this, 'add_frontend_styles' ], 20 );

		}

		/*
		// Override Title Page content.
		add_filter( 'cp_title_page_content', [ $this, 'get_title_page_content' ] );
		*/

	}

	/**
	 * Enqueue any styles and scripts needed by our public pages.
	 *
	 * @since 3.3
	 */
	public function add_frontend_styles() {

	}

	/**
	 * Hook into the blog signup form.
	 *
	 * @since 3.3
	 *
	 * @param array $errors The previously encountered errors.
	 */
	public function signup_blogform( $errors ) {

		// Only apply to WordPress signup form (not the BuddyPress one).
		if ( is_object( $this->ms_loader->bp ) ) {
			return;
		}

		// Get force option.
		$forced = $this->ms_loader->db->option_get( 'cpmu_force_commentpress' );

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

		// Get workflow element.
		$workflow_html = $this->get_workflow();

		// Get blog type element.
		$type_html = $this->get_blogtype();

		// Construct form.
		$form = '

		<br />
		<div id="cp-multisite-options">

			<h3>' . __( 'CommentPress:', 'commentpress-core' ) . '</h3>

			<p>' . $text . '</p>

			' . $forced_html . '

			' . $workflow_html . '

			' . $type_html . '

		</div>

		';

		echo $form;

	}

	/**
	 * Hook into wpmu_new_blog and target plugins to be activated.
	 *
	 * @since 3.3
	 *
	 * @param int $blog_id The numeric ID of the WordPress blog.
	 * @param int $user_id The numeric ID of the WordPress user.
	 * @param str $domain The domain of the WordPress blog.
	 * @param str $path The path of the WordPress blog.
	 * @param int $site_id The numeric ID of the WordPress parent site.
	 * @param array $meta The meta data of the WordPress blog.
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
	 * Create a blog.
	 *
	 * @since 3.3
	 *
	 * @param int $blog_id The numeric ID of the WordPress blog.
	 * @param int $user_id The numeric ID of the WordPress user.
	 * @param str $domain The domain of the WordPress blog.
	 * @param str $path The path of the WordPress blog.
	 * @param int $site_id The numeric ID of the WordPress parent site.
	 * @param array $meta The meta data of the WordPress blog.
	 */
	private function create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		// Wpmu_new_blog calls this *after* restore_current_blog, so we need to do it again.
		switch_to_blog( $blog_id );

		// Activate CommentPress Core.
		$this->ms_loader->db->install_commentpress();

		// Switch back.
		restore_current_blog();

	}

	/**
	 * Get workflow form elements.
	 *
	 * @since 3.3
	 *
	 * @return str $workflow_html The HTML form element.
	 */
	public function get_workflow() {

		// Init.
		$workflow_html = '';

		// Get data.
		$workflow = $this->ms_loader->db->get_workflow_data();

		// If we have workflow data.
		if ( ! empty( $workflow ) ) {

			// Show it.
			$workflow_html = '

			<div class="checkbox">
				<label for="cp_blog_workflow">' . $workflow['element'] . ' ' . $workflow['label'] . '</label>
			</div>

			';

		}

		// --<
		return $workflow_html;

	}

	/**
	 * Get blog type form elements.
	 *
	 * @since 3.3
	 *
	 * @return str $type_html The HTML form element.
	 */
	public function get_blogtype() {

		// Init.
		$type_html = '';

		// Get data.
		$type = $this->ms_loader->db->get_blogtype_data();

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
	 * Allow other plugins to hook into our admin form.
	 *
	 * @since 3.3
	 *
	 * @return str Empty string by default, but may be overridden.
	 */
	public function additional_form_options() {

		// Return whatever plugins send.
		return apply_filters( 'cpmu_network_options_form', '' );

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
			'cpmu_disable_translation_workflow' => $this->cpmu_disable_translation_workflow,
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

		// Set "force all new sites to be CommentPress Core-enabled" option.
		$this->ms_loader->db->option_set( 'cpmu_force_commentpress', ( $cpmu_force_commentpress ? 1 : 0 ) );

		/*
		// Get "Default title page content" value.
		$cpmu_title_page_content = isset( $_POST['cpmu_title_page_content'] ) ?
			sanitize_text_field( wp_unslash( $_POST['cpmu_title_page_content'] ) ) :
			$this->get_default_title_page_content();

		// Set "Default title page content" option.
		$this->ms_loader->db->option_set( 'cpmu_title_page_content', $cpmu_title_page_content );
		*/

		// Get "Disable translation workflow" value.
		$cpmu_disable_translation_workflow = isset( $_POST['cpmu_disable_translation_workflow'] ) ?
			sanitize_text_field( wp_unslash( $_POST['cpmu_disable_translation_workflow'] ) ) :
			'0';

		// Set "Disable translation workflow" option.
		$this->ms_loader->db->option_set( 'cpmu_disable_translation_workflow', ( $cpmu_disable_translation_workflow ? 1 : 0 ) );

	}

	/**
	 * Get workflow enabled setting.
	 *
	 * @since 3.3
	 *
	 * @return bool $disabled True if disabled, false otherwise.
	 */
	public function get_workflow_enabled() {

		// Get option.
		$disabled = $this->ms_loader->db->option_get( 'cpmu_disable_translation_workflow' ) == '1' ? false : true;

		// Return whatever option is set.
		return $disabled;

	}

	/**
	 * Get default Title Page content, if set.
	 *
	 * Do we want to enable this when we enable the admin page editor?
	 *
	 * @since 3.3
	 *
	 * @param str $content The existing content.
	 * @return str $content The modified content.
	 */
	public function get_title_page_content( $content ) {

		// Get content.
		$overridden_content = stripslashes( $this->ms_loader->db->option_get( 'cpmu_title_page_content' ) );

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
