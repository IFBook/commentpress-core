<?php
/**
 * CommentPress Core Theme Sidebar class.
 *
 * Handles Theme Sidebar functionality in CommentPress Core.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Theme Sidebar Class.
 *
 * This class provides Theme Sidebar functionality in CommentPress Core.
 *
 * @since 4.0
 */
class CommentPress_Core_Theme_Sidebar {

	/**
	 * Core loader object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * Theme object.
	 *
	 * @since 4.0
	 * @access public
	 * @var object $theme The theme object.
	 */
	public $theme;

	/**
	 * Parts template directory path.
	 *
	 * @since 4.0
	 * @access private
	 * @var string $parts_path Relative path to the Parts directory.
	 */
	private $parts_path = 'includes/core/assets/templates/wordpress/parts/';

	/**
	 * Sidebar setting key in Site Settings.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $key_sidebar The "Default Sidebar" setting key in Site Settings.
	 */
	public $key_sidebar = 'cp_sidebar_default';

	/**
	 * Sidebar meta key.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $meta_key The "Sidebar" meta key.
	 */
	public $meta_key = '_cp_sidebar_default';

	/**
	 * Constructor.
	 *
	 * @since 4.0
	 *
	 * @param object $theme Reference to the core theme object.
	 */
	public function __construct( $theme ) {

		// Store references.
		$this->theme = $theme;
		$this->core = $theme->core;

		// Init when the theme object is fully loaded.
		add_action( 'commentpress/core/theme/loaded', [ $this, 'initialise' ] );

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

		// Separate callbacks into descriptive methods.
		$this->register_hooks_settings();
		$this->register_hooks_entry();
		$this->register_hooks_theme();

	}

	/**
	 * Registers "Site Settings" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_settings() {

		// Add our settings to default settings.
		add_filter( 'commentpress/core/settings/defaults', [ $this, 'settings_get_defaults' ] );

		// Inject form element into the "Theme Customisation" metabox on "Site Settings" screen.
		add_action( 'commentpress/core/settings/site/metabox/theme/after', [ $this, 'settings_meta_box_part_get' ] );

		// Save Sidebar data from "Site Settings" screen.
		add_action( 'commentpress/core/settings/site/save/before', [ $this, 'settings_meta_box_part_save' ] );

	}

	/**
	 * Registers "Entry Settings" hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_entry() {

		// Inject form element into the "CommentPress Settings" metabox on "Edit Entry" screens.
		add_action( 'commentpress/core/entry/metabox/after', [ $this, 'entry_meta_box_part_get' ], 30 );

		// Saves the Sidebar value on "Edit Entry" screens.
		add_action( 'commentpress/core/settings/post/saved', [ $this, 'entry_meta_box_part_save' ] );

	}

	/**
	 * Registers Theme hooks.
	 *
	 * @since 4.0
	 */
	private function register_hooks_theme() {

		// Add setting to the Javascript vars.
		add_filter( 'commentpress_get_javascript_vars', [ $this, 'theme_javascript_vars_add' ] );

		// Add our class(es) to the body classes.
		add_filter( 'commentpress/core/theme/body/classes', [ $this, 'theme_body_classes_filter' ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Appends our settings to the default core settings.
	 *
	 * @since 4.0
	 *
	 * @param array $settings The existing default core settings.
	 * @return array $settings The modified default core settings.
	 */
	public function settings_get_defaults( $settings ) {

		// Add our defaults.
		$settings[ $this->key_sidebar ] = 'comments';

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'settings' => $settings,
			//'backtrace' => $trace,
		], true ) );
		*/

		// --<
		return $settings;

	}

	/**
	 * Adds our form element to the "Theme Customisation" metabox.
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_part_get() {

		// Allow this to be disabled.
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) {
			return;
		}

		// Get the value of the option.
		$sidebar = $this->core->db->setting_get( $this->key_sidebar, 'comments' );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-theme-sidebar-settings.php';

	}

	/**
	 * Saves the Sidebar with data from "Site Settings" screen.
	 *
	 * Adds the data to the settings array. The settings are actually saved later.
	 *
	 * @see CommentPress_Core_Settings_Site::form_submitted()
	 *
	 * @since 4.0
	 */
	public function settings_meta_box_part_save() {

		// Allow this to be disabled.
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) {
			return;
		}

		// Find the data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$sidebar = isset( $_POST[ $this->key_sidebar ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_sidebar ] ) ) : '';

		// Set default sidebar.
		$this->core->db->setting_set( $this->key_sidebar, $sidebar );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our form element to the "CommentPress Settings" metabox.
	 *
	 * @since 4.0
	 *
	 * @param WP_Post $post The WordPress Post object.
	 */
	public function entry_meta_box_part_get( $post ) {

		// Allow this to be disabled.
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) {
			return;
		}

		// We want raw values for the Edit Entry Metabox.
		$raw = true;

		// Get the Sidebar for this Entry.
		$sidebar = $this->get_for_post_id( $post->ID, $raw );

		// Include template file.
		include COMMENTPRESS_PLUGIN_PATH . $this->parts_path . 'part-theme-sidebar-entry.php';

	}

	/**
	 * Saves the Sidebar for a given Entry.
	 *
	 * @since 4.0
	 *
	 * @param object $post The WordPress Post object.
	 */
	public function entry_meta_box_part_save( $post ) {

		// Allow this to be disabled.
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) {
			return;
		}

		// Find the data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$sidebar = isset( $_POST[ $this->key_sidebar ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->key_sidebar ] ) ) : '';

		// Save Sidebar for this Entry.
		$this->set_for_post_id( $post->ID, $sidebar );

	}

	// -------------------------------------------------------------------------

	/**
	 * Filters the Javascript vars.
	 *
	 * @since 4.0
	 *
	 * @param array $vars The default Javascript vars.
	 * @return array $vars The modified Javascript vars.
	 */
	public function theme_javascript_vars_add( $vars ) {

		// Add our setting.
		$vars['cp_sidebar_default'] = $this->default_get();

		// --<
		return $vars;

	}

	/**
	 * Adds "Sidebar" class to the body classes array.
	 *
	 * @since 4.0
	 *
	 * @param array $classes The existing body classes array.
	 * @return array $classes The modified body classes array.
	 */
	public function theme_body_classes_filter( $classes ) {

		// Get default sidebar.
		$sidebar = $this->default_get();

		// Set class for sidebar.
		$sidebar_class = 'cp_sidebar_' . $sidebar;

		// Add to array.
		$classes[] = $sidebar_class;

		// --<
		return $classes;

	}

	// -------------------------------------------------------------------------

	/**
	 * Returns the "code" of the default Sidebar.
	 *
	 * @since 3.4
	 *
	 * @return str $default The "code" of the default Sidebar.
	 */
	public function default_get() {

		// Access Post object.
		global $post;

		/**
		 * Filters the default sidebar.
		 *
		 * Used internally by:
		 *
		 * * commentpress_default_theme_default_sidebar() (Priority: 10)
		 *
		 * @since 3.9.8
		 *
		 * @param str The default sidebar. Defaults to 'activity'.
		 */
		$default = apply_filters( 'commentpress_default_sidebar', 'activity' );

		// Get setting.
		$setting = $this->core->db->setting_get( $this->key_sidebar );

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'default-INIT' => $default,
			'setting-INIT' => $setting,
			//'backtrace' => $trace,
		], true ) );
		*/

		// If there's no Post object.
		if ( ! ( $post instanceof WP_Post ) ) {

			// Must be either "activity" or "toc".

			// Use setting unless it's "comments".
			// We don't need to look at the Entry meta in this case.
			if ( $setting !== 'comments' ) {
				$default = $setting;
			}

			// --<
			return $default;

		}

		// If this is not a commentable Entry.
		if ( ! $this->core->parser->is_commentable() ) {

			// Must be either "activity" or "toc".

			// Use setting unless it's "comments".
			// We don't need to look at the Entry meta in this case.
			if ( $setting !== 'comments' ) {
				$default = $setting;
			}

			// --<
			return $default;

		}

		// If it's a Special Page which has Comments-in-Page or is not commentable.
		if ( $this->core->pages_legacy->is_special_page() ) {

			// Must be either "activity" or "toc".

			// Use setting unless it's "comments".
			// We don't need to look at the Entry meta in this case.
			if ( $setting !== 'comments' ) {
				$default = $setting;
			}

			// --<
			return $default;

		}

		// If it's not a commentable Entry.
		if ( ! is_singular() ) {

			// Must be either "activity" or "toc".

			// Use setting unless it's "comments".
			// We don't need to look at the Entry meta in this case.
			if ( $setting !== 'comments' ) {
				$default = $setting;
			}

			// --<
			return $default;

		}

		// Get for this Post ID.
		$default = $this->get_for_post_id( $post->ID );

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'default-FINAL' => $default,
			//'backtrace' => $trace,
		], true ) );
		*/

		// --<
		return $default;

	}

	/**
	 * Gets the order of the Sidebars.
	 *
	 * @since 3.4
	 *
	 * @return array $order Sidebars in order of display.
	 */
	public function order_get() {

		/**
		 * Filters the default tab order.
		 *
		 * @since 3.4
		 *
		 * @param array $order The default tab order array.
		 */
		$order = apply_filters( 'cp_sidebar_tab_order', [ 'contents', 'comments', 'activity' ] );

		// --<
		return $order;

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the Sidebar for a given Post ID.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @param bool $raw Pass "true" to get the actual meta value.
	 * @return string $sidebar The Sidebar identifier.
	 */
	public function get_for_post_id( $post_id, $raw = false ) {

		// Check Post for override.
		$override = get_post_meta( $post_id, $this->meta_key, true );

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'override' => $override,
			//'backtrace' => $trace,
		], true ) );
		*/

		// Return raw value if requested.
		if ( $raw === true ) {
			return $override;
		}

		// Default to current Sidebar.
		$sidebar = $this->core->db->setting_get( $this->key_sidebar );

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'sidebar-SETTING' => $sidebar,
			//'backtrace' => $trace,
		], true ) );
		*/

		// Bail if we didn't get one.
		if ( empty( $override ) ) {
			return $sidebar;
		}

		// Override if different to the default Sidebar.
		if ( (string) $override !== (string) $sidebar ) {
			$sidebar = $override;
		}

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'sidebar-FINAL' => $sidebar,
			//'backtrace' => $trace,
		], true ) );
		*/

		// --<
		return (string) $sidebar;

	}

	/**
	 * Sets the Sidebar for a given Post ID.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @param string $sidebar The Sidebar identifier.
	 */
	public function set_for_post_id( $post_id, $sidebar ) {

		/*
		$e = new \Exception();
		$trace = $e->getTraceAsString();
		error_log( print_r( [
			'method' => __METHOD__,
			'post_id' => $post_id,
			'sidebar' => $sidebar,
			'meta_key' => $this->meta_key,
			//'backtrace' => $trace,
		], true ) );
		*/

		// Sanity check.
		$sidebar = (string) $sidebar;
		if ( empty( $sidebar ) ) {
			$this->delete_for_post_id( $post_id );
			return;
		}

		// Cast Sidebar value as string when updating.
		update_post_meta( $post_id, $this->meta_key, $sidebar );

	}

	/**
	 * Deletes the Sidebar for a given Post ID.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 */
	public function delete_for_post_id( $post_id ) {

		// Delete the Sidebar meta value.
		delete_post_meta( $post_id, $this->meta_key );

	}

	/**
	 * Checks if the Sidebar of a Post is different to the default.
	 *
	 * @since 4.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @return bool $overridden True if overridden, false otherwise.
	 */
	public function is_overridden( $post_id ) {

		// Get the Sidebar setting.
		$sidebar_setting = $this->core->db->setting_get( $this->key_sidebar );

		// Get the Sidebar for this Post.
		$sidebar_post = $this->get_for_post_id( $post_id );

		// Do override check.
		if ( (string) $sidebar_setting !== (string) $sidebar_post ) {
			return true;
		}

		// Not overridden.
		return false;

	}

}
