<?php

/**
 * CommentPress Core WordPress Multisite Class.
 *
 * This class encapsulates WordPress Multisite compatibility.
 *
 * @since 3.3
 */
class Commentpress_Multisite_Wordpress {

	/**
	 * Plugin object.
	 *
	 * @since 3.3
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
	//public $cpmu_title_page_content = '';

	/**
	 * Allow translation workflow flag.
	 *
	 * @since 3.3
	 * @access public
	 * @var str $cpmu_disable_translation_workflow The translation workflow allowed flag ('0' or '1').
	 */
	public $cpmu_disable_translation_workflow = '1';



	/**
	 * Initialises this object.
	 *
	 * @since 3.3
	 *
	 * @param object $parent_obj A reference to the parent object.
	 */
	public function __construct( $parent_obj = null ) {

		// Store reference to "parent" (calling obj, not OOP parent).
		$this->parent_obj = $parent_obj;

		// Store reference to database wrapper (child of calling obj).
		$this->db = $this->parent_obj->db;

		// Init.
		$this->_init();

	}



	/**
	 * Set up all items associated with this object.
	 *
	 * @since 3.3
	 */
	public function initialise() {

	}



	/**
	 * If needed, destroys all items associated with this object.
	 *
	 * @since 3.3
	 */
	public function destroy() {

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Public Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Add an admin page for this plugin.
	 *
	 * @since 3.3
	 */
	public function add_admin_menu() {

		// We must be network admin.
		if ( ! is_super_admin() ) return false;

		// Try and update options.
		$saved = $this->db->options_update();

		// Always add the admin page to the Settings menu.
		$page = add_submenu_page(
			'settings.php',
			__( 'CommentPress', 'commentpress-core' ),
			__( 'CommentPress', 'commentpress-core' ),
			'manage_options',
			'cpmu_admin_page',
			[ $this, '_network_admin_form' ]
		);

		// Add styles only on our admin page, see:
		// http://codex.wordpress.org/Function_Reference/wp_enqueue_script#Load_scripts_only_on_plugin_pages
		add_action( 'admin_print_styles-' . $page, [ $this, 'add_admin_styles' ] );

	}



	/**
	 * Enqueue any styles and scripts needed by our admin page.
	 *
	 * @since 3.3
	 */
	public function add_admin_styles() {

		// Add admin CSS.
		wp_enqueue_style(
			'cpmu-admin-style',
			COMMENTPRESS_PLUGIN_URL . 'commentpress-multisite/assets/css/admin.css',
			null,
			COMMENTPRESS_MU_PLUGIN_VERSION, // Version.
			'all' // Media.
		);

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

		// Only apply to wordpress signup form (not the BuddyPress one).
		if ( is_object( $this->parent_obj->bp ) ) return;

		// Get force option.
		$forced = $this->db->option_get( 'cpmu_force_commentpress' );

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
		$workflow_html = $this->_get_workflow();

		// Get blog type element.
		$type_html = $this->_get_blogtype();

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
		if ( isset( $_POST['cpmu-new-blog'] ) AND $_POST['cpmu-new-blog'] == '1' ) {

			// Hand off to private method.
			$this->_create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta );

		}

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
	 * @since 3.3
	 */
	public function _init() {

		// Register hooks.
		$this->_register_hooks();

	}



	/**
	 * Register WordPress hooks.
	 *
	 * @since 3.3
	 */
	public function _register_hooks() {

		// Add form elements to signup form.
		add_action( 'signup_blogform', [ $this, 'signup_blogform' ] );

		// Activate blog-specific CommentPress Core plugin.
		add_action( 'wpmu_new_blog', [ $this, 'wpmu_new_blog' ], 12, 6 );

		// Enable/disable workflow sitewide.
		add_filter( 'cp_class_commentpress_workflow_enabled', [ $this, '_get_workflow_enabled' ] );

		// Is this the back end?
		if ( is_admin() ) {

			// Add menu to Network submenu.
			add_action( 'network_admin_menu', [ $this, 'add_admin_menu' ], 30 );

			// Add options to reset array.
			add_filter( 'cpmu_db_options_get_defaults', [ $this, '_get_default_settings' ], 20, 1 );

			// Hook into Network BuddyPress form update.
			add_action( 'cpmu_db_options_update', [ $this, '_network_admin_update' ], 20 );

		} else {

			// Register any public styles.
			add_action( 'wp_enqueue_scripts', [ $this, 'add_frontend_styles' ], 20 );

		}

		// Override Title Page content.
		//add_filter( 'cp_title_page_content', [ $this, '_get_title_page_content' ] );

	}



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
	public function _create_blog( $blog_id, $user_id, $domain, $path, $site_id, $meta ) {

		// Wpmu_new_blog calls this *after* restore_current_blog, so we need to do it again.
		switch_to_blog( $blog_id );

		// Activate CommentPress Core.
		$this->db->install_commentpress();

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
	public function _get_workflow() {

		// Init.
		$workflow_html = '';

		// Get data.
		$workflow = $this->db->get_workflow_data();

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
	public function _get_blogtype() {

		// Init.
		$type_html = '';

		// Get data.
		$type = $this->db->get_blogtype_data();

		// If we have type data
		if ( ! empty( $type ) ) {

			// Show it.
			$type_html = '

			<div class="dropdown">
				<label for="cp_blog_type">' . $type['label'] . '</label> <select id="cp_blog_type" name="cp_blog_type">

				' . $type['element'] . '

				</select>
			</div>

			';

		}

		// --<
		return $type_html;

	}



	/**
	 * Show our admin page.
	 *
	 * @since 3.3
	 */
	public function _network_admin_form() {

		// Only allow network admins through.
		if( is_super_admin() == false ) {

			// Disallow.
			wp_die( __( 'You do not have permission to access this page.', 'commentpress-core' ) );

		}

		// Show message.
		if ( isset( $_GET['updated'] ) ) {
			echo '<div id="message" class="updated"><p>' . __( 'Options saved.', 'commentpress-core' ) . '</p></div>';
		}

		// Sanitise admin page URL.
		$url = $_SERVER['REQUEST_URI'];
		$url_array = explode( '&', $url );
		if ( is_array( $url_array ) ) { $url = $url_array[0]; }

		// Open admin page.
		echo '
		<div class="wrap" id="cpmu_admin_wrapper">

		<h1>' . __( 'CommentPress Network Settings', 'commentpress-core' ) . '</h1>

		<form method="post" action="' . htmlentities($url . '&updated=true') . '">

		' . wp_nonce_field( 'cpmu_admin_action', 'cpmu_nonce', true, false ) . '
		' . wp_referer_field( false ) . '

		';

		// Show multisite options.
		echo '
<div id="cpmu_admin_options">

<h3>' . __( 'Multisite Settings', 'commentpress-core' ) . '</h3>

<p>' . __( 'Configure how your CommentPress Network behaves. Site-specific options are set on the CommentPress Core Settings page for that site.', 'commentpress-core' ) . '</p>';

		// Add global options.
		echo '
<h4>' . __( 'Global Options', 'commentpress-core' ) . '</h4>

<table class="form-table">

	<tr valign="top">
		<th scope="row"><label for="cpmu_reset">' . __( 'Reset Multisite options', 'commentpress-core' ) . '</label></th>
		<td><input id="cpmu_reset" name="cpmu_reset" value="1" type="checkbox" /></td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cpmu_force_commentpress">' . __( 'Make all new sites CommentPress-enabled', 'commentpress-core' ) . '</label></th>
		<td><input id="cpmu_force_commentpress" name="cpmu_force_commentpress" value="1" type="checkbox"' . ( $this->db->option_get( 'cpmu_force_commentpress' ) == '1' ? ' checked="checked"' : '' ) . ' /></td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cpmu_disable_translation_workflow">' . __( 'Disable Translation Workflow (Recommended because it is still very experimental)', 'commentpress-core' ) . '</label></th>
		<td><input id="cpmu_disable_translation_workflow" name="cpmu_disable_translation_workflow" value="1" type="checkbox"' . ( $this->db->option_get( 'cpmu_disable_translation_workflow' ) == '1' ? ' checked="checked"' : '' ) . ' /></td>
	</tr>

' . $this->_additional_multisite_options() . '

</table>';

		/*
		// Add WordPress overrides.
		echo '
<h4>' . __( 'Override WordPress behaviour', 'commentpress-core' ) . '</h4>

<table class="form-table">

	<tr valign="top">
		<th scope="row"><label for="cpmu_delete_first_page">' . __( 'Delete WordPress-generated Sample Page', 'commentpress-core' ) . '</label></th>
		<td><input id="cpmu_delete_first_page" name="cpmu_delete_first_page" value="1" type="checkbox"' . ( $this->db->option_get( 'cpmu_delete_first_page' ) == '1' ? ' checked="checked"' : '' ) . ' /></td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cpmu_delete_first_post">' . __( 'Delete WordPress-generated Hello World post', 'commentpress-core' ) . '</label></th>
		<td><input id="cpmu_delete_first_post" name="cpmu_delete_first_post" value="1" type="checkbox"' . ( $this->db->option_get( 'cpmu_delete_first_post' ) == '1' ? ' checked="checked"' : '' ) . ' /></td>
	</tr>

	<tr valign="top">
		<th scope="row"><label for="cpmu_delete_first_comment">' . __( 'Delete WordPress-generated First Comment', 'commentpress-core' ) . '</label></th>
		<td><input id="cpmu_delete_first_comment" name="cpmu_delete_first_comment" value="1" type="checkbox"' . ( $this->db->option_get( 'cpmu_delete_first_comment' ) == '1' ? ' checked="checked"' : '' ) . ' /></td>
	</tr>

</table>';
		*/

		// Close form.
		echo '
		</div>';

		/*
		// Title
		echo '<h3>' . __( 'Title Page Content', 'commentpress-core' ) . '</h3>';

		// Explanation
		echo '<p>' . __( 'The following is the content of the Title Page for each new CommentPress site. Edit it if you want to show something else on the Title Page.', 'commentpress-core' ) . '</p>';

		// Get content.
		$content = stripslashes( $this->db->option_get( 'cpmu_title_page_content' ) );
		//_cpdie( $content );

		// Call the editor
		wp_editor(
			$content,
			'cpmu_title_page_content',
			$settings = [
				'media_buttons' => false,
			]
		);
		*/

		// Allow plugins to add stuff.
		echo $this->_additional_form_options();

		// Close admin form.
		echo '
		<p class="submit">
			<input type="submit" name="cpmu_submit" value="' . __( 'Save Changes', 'commentpress-core' ) . '" class="button-primary" />
		</p>

		</form>

		</div>
		' . "\n\n\n\n";

	}



	/**
	 * Allow other plugins to hook into our multisite admin options.
	 *
	 * @since 3.3
	 *
	 * @return str Empty string by default, but may be overridden.
	 */
	public function _additional_multisite_options() {

		// Return whatever plugins send.
		return apply_filters( 'cpmu_network_multisite_options_form', '' );

	}



	/**
	 * Allow other plugins to hook into our admin form.
	 *
	 * @since 3.3
	 *
	 * @return str Empty string by default, but may be overridden.
	 */
	public function _additional_form_options() {

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
	public function _get_default_settings( $existing_options ) {

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
	public function _network_admin_update() {

		// Init.
		$cpmu_force_commentpress = '0';
		//$cpmu_title_page_content = ''; // Replace with content from _get_default_title_page_content()
		$cpmu_disable_translation_workflow = '0';

		// Get variables.
		extract( $_POST );

		// Force all new sites to be CommentPress Core-enabled.
		$cpmu_force_commentpress = esc_sql( $cpmu_force_commentpress );
		$this->db->option_set( 'cpmu_force_commentpress', ( $cpmu_force_commentpress ? 1 : 0 ) );

		/*
		// Default title page content.
		$cpmu_title_page_content = esc_sql( $cpmu_title_page_content );
		$this->db->option_set( 'cpmu_title_page_content', $cpmu_title_page_content );
		*/

		// Allow translation workflow.
		$cpmu_disable_translation_workflow = esc_sql( $cpmu_disable_translation_workflow );
		$this->db->option_set( 'cpmu_disable_translation_workflow', ( $cpmu_disable_translation_workflow ? 1 : 0 ) );

	}



	/**
	 * Get workflow enabled setting.
	 *
	 * @since 3.3
	 *
	 * @return bool $disabled True if disabled, false otherwise.
	 */
	public function _get_workflow_enabled() {

		// Get option.
		$disabled = $this->db->option_get( 'cpmu_disable_translation_workflow' ) == '1' ? false : true;

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
	public function _get_title_page_content( $content ) {

		// Get content.
		$overridden_content = stripslashes( $this->db->option_get( 'cpmu_title_page_content' ) );

		// Is it different to what's been passed?
		if ( $content != $overridden_content ) {

			// Override.
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
	public function _get_default_title_page_content() {

		// --<
		return __(

		'Welcome to your new CommentPress site, which allows your readers to comment paragraph-by-paragraph or line-by-line in the margins of a text. Annotate, gloss, workshop, debate: with CommentPress you can do all of these things on a finer-grained level, turning a document into a conversation.

This is your title page. Edit it to suit your needs. It has been automatically set as your homepage but if you want another page as your homepage, set it in <em>WordPress</em> &#8594; <em>Settings</em> &#8594; <em>Reading</em>.

You can also set a number of options in <em>WordPress</em> &#8594; <em>Settings</em> &#8594; <em>CommentPress</em> to make the site work the way you want it to. Use the Theme Customizer to change the way your site looks in <em>WordPress</em> &#8594; <em>Appearance</em> &#8594; <em>Customize</em>. For help with structuring, formatting and reading text in CommentPress, please refer to the <a href="http://www.futureofthebook.org/commentpress/">CommentPress website</a>.', 'commentpress-core'

		);

	}



//##############################################################################



} // Class ends.



