<?php
/**
 * CommentPress Multisite Database class.
 *
 * Handles the majority of database operations in WordPress Multisite contexts.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Multisite Database Class.
 *
 * This class is a wrapper for the majority of database operations.
 *
 * @since 3.3
 */
class CommentPress_Multisite_Database {

	/**
	 * Multisite loader object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $multisite The multisite loader object.
	 */
	public $multisite;

	/**
	 * The installed version of the plugin.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $plugin_version The plugin version.
	 */
	public $plugin_version;

	/**
	 * Upgrade flag.
	 *
	 * @since 4.0
	 * @access public
	 * @var bool $is_upgrade The upgrade flag. False by default.
	 */
	public $is_upgrade = false;

	/**
	 * Multisite version site option name.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $option_version The name of the multisite version site option.
	 */
	public $option_version = 'cpmu_version';

	/**
	 * Multisite settings site option name.
	 *
	 * @since 4.0
	 * @access public
	 * @var string $option_settings The name of the multisite settings site option.
	 */
	public $option_settings = 'cpmu_options';

	/**
	 * Multisite settings array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $settings The multisite settings array.
	 */
	public $settings = [];

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
	 * Initialises this obiject.
	 *
	 * @since 3.3
	 */
	public function initialise() {

		// Only do this once.
		static $done;
		if ( isset( $done ) && $done === true ) {
			return;
		}

		// Init settings.
		$this->settings_initialise();

		// Register hooks.
		$this->register_hooks();

		// We're done.
		$done = true;

	}

	/**
	 * Initialises the settings.
	 *
	 * @since 4.0
	 */
	public function settings_initialise() {

		// Load installed plugin version.
		$this->plugin_version = $this->version_get();

		// Load settings array.
		$this->settings = $this->settings_get();

		// Store version if there has been a change.
		if ( $this->version_outdated() ) {
			$this->version_set( COMMENTPRESS_MU_PLUGIN_VERSION );
			$this->is_upgrade = true;
		}

		// Settings upgrade tasks.
		$this->settings_upgrade();

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

		// Acts early when this plugin is activated.
		add_action( 'commentpress/activated', [ $this, 'activate' ], 10 );

		// Act late when this plugin is deactivated.
		add_action( 'commentpress/deactivated', [ $this, 'deactivate' ], 50 );

	}

	// -------------------------------------------------------------------------

	/**
	 * Runs when the plugin is activated.
	 *
	 * @since 4.0
	 */
	public function activate() {

	}

	/**
	 *  Runs when the plugin is deactivated.
	 *
	 * @since 4.0
	 */
	public function deactivate() {

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the installed plugin version.
	 *
	 * @since 4.0
	 *
	 * @return string|bool $version The installed version, or false if none found.
	 */
	public function version_get() {

		// Get installed version cast as string.
		$version = (string) $this->option_wpms_get( $this->option_version );

		// Cast as boolean if not found.
		if ( empty( $version ) ) {
			$version = false;
		}

		// --<
		return $version;

	}

	/**
	 * Sets the plugin version.
	 *
	 * @since 4.0
	 *
	 * @param string $version The version to save.
	 */
	public function version_set( $version ) {

		// Store new CommentPress Core version.
		$this->option_wp_set( $this->option_version, $version );

	}

	/**
	 * Deletes the plugin version Site Option.
	 *
	 * @since 4.0
	 */
	public function version_delete() {

		// Delete CommentPress Multisite version option.
		$this->option_wpms_delete( $this->option_version );

	}

	/**
	 * Checks for an outdated plugin version.
	 *
	 * @since 4.0
	 *
	 * @return bool True if outdated, false otherwise.
	 */
	public function version_outdated() {

		// Get installed version.
		$version = $this->version_get();

		// True if we have a CommentPress Multisite install and it's lower than this one.
		if ( ! empty( $version ) && version_compare( COMMENTPRESS_MU_PLUGIN_VERSION, $version, '>' ) ) {
			return true;
		}

		// Fallback.
		return false;

	}

	// -------------------------------------------------------------------------

	/**
	 * Gets the settings array from a WordPress Site Option.
	 *
	 * @since 4.0
	 *
	 * @return array $settings The array of settings if successful, or empty array otherwise.
	 */
	public function settings_get() {

		// Get the Site Option.
		return $this->option_wpms_get( $this->option_settings, $this->settings_get_defaults() );

	}

	/**
	 * Saves the settings array in a WordPress Site Option.
	 *
	 * @since 3.3
	 *
	 * @return boolean $success True if successful, or false otherwise.
	 */
	public function settings_save() {

		// Set the Site Option.
		return $this->option_wpms_set( $this->option_settings, $this->settings );

	}

	/**
	 * Deletes the settings WordPress Site Option.
	 *
	 * @since 4.0
	 */
	public function settings_delete() {

		// Delete the Site Option.
		$this->option_wpms_delete( $this->option_settings );

	}

	/**
	 * Upgrades the settings when required.
	 *
	 * @since 4.0
	 */
	public function settings_upgrade() {

		// Don't save by default.
		$save = false;

		/*
		// Some setting may not exist.
		if ( ! $this->setting_exists( 'some_setting' ) ) {
			$settings = $this->settings_get_defaults();
			$this->setting_set( 'some_setting', $settings['some_setting'] );
			$save = true;
		}
		*/

		/*
		// Things to always check on upgrade.
		if ( $this->is_upgrade ) {
			// Add them here.
			//$save = true;
		}
		*/

		// Save settings if need be.
		if ( $save === true ) {
			$this->settings_save();
		}

	}

	/**
	 * Gets the default settings.
	 *
	 * @since 4.0
	 *
	 * @return array $settings  The array of default settings.
	 */
	public function settings_get_defaults() {

		// Init return.
		$settings = [];

		/**
		 * Filters the default settings.
		 *
		 * @since 4.0
		 *
		 * @param array $settings The array of default settings.
		 */
		$settings = apply_filters( 'commentpress/multisite/settings/defaults', $settings );

		// --<
		return $settings;

	}

	// -------------------------------------------------------------------------

	/**
	 * Returns existence of a specified setting.
	 *
	 * @since 3.3
	 *
	 * @param str $setting_name The name of the setting.
	 * @return bool True if the setting exists, false otherwise.
	 */
	public function setting_exists( $setting_name ) {

		// Check if the setting exists in the settings array.
		return array_key_exists( $setting_name, $this->settings );

	}

	/**
	 * Return a value for a specified setting.
	 *
	 * @since 3.3
	 *
	 * @param str $setting_name The name of the setting.
	 * @param mixed $default The default value for the setting.
	 * @return mixed The value of the setting if it exists, $default otherwise.
	 */
	public function setting_get( $setting_name, $default = false ) {

		// Get setting.
		return array_key_exists( $setting_name, $this->settings ) ? $this->settings[ $setting_name ] : $default;

	}

	/**
	 * Sets a value for a specified setting.
	 *
	 * @since 3.3
	 *
	 * @param str $setting_name The name of the setting.
	 * @param mixed $value The value for the setting.
	 */
	public function setting_set( $setting_name, $value = '' ) {

		// Set setting.
		$this->settings[ $setting_name ] = $value;

	}

	/**
	 * Deletes a specified setting.
	 *
	 * @since 3.3
	 *
	 * @param str $setting_name The name of the setting.
	 */
	public function setting_delete( $setting_name ) {

		// Unset setting.
		unset( $this->settings[ $setting_name ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Return existence of a specified Site Option.
	 *
	 * @since 3.3
	 *
	 * @param str $option_name The name of the option.
	 * @return bool True if option exists, false otherwise.
	 */
	public function option_wpms_exists( $option_name ) {

		// Get option with unlikely default.
		if ( $this->option_wpms_get( $option_name, 'fenfgehgejgrkj' ) === 'fenfgehgejgrkj' ) {
			return false;
		} else {
			return true;
		}

	}

	/**
	 * Return a value for a specified Site Option.
	 *
	 * @since 3.3
	 *
	 * @param str $option_name The name of the option.
	 * @param mixed $default The default value for the option.
	 * @return mixed The value of the option if it exists, default otherwise.
	 */
	public function option_wpms_get( $option_name, $default = false ) {

		// Get option.
		return get_site_option( $option_name, $default );

	}

	/**
	 * Sets the value for a specified Site Option.
	 *
	 * @since 3.3
	 *
	 * @param str $option_name The name of the option.
	 * @param mixed $value The value for the option.
	 * @return bool True if the value was updated, false otherwise.
	 */
	public function option_wpms_set( $option_name, $value = '' ) {

		// Set option.
		return update_site_option( $option_name, $value );

	}

	/**
	 * Deletes a specified Site Option.
	 *
	 * @since 4.0
	 *
	 * @param str $option_name The name of the option.
	 */
	public function option_wpms_delete( $option_name ) {

		// Set option.
		delete_site_option( $option_name );

	}

	// -------------------------------------------------------------------------

	/**
	 * Get Blog Type form elements.
	 *
	 * @since 3.3
	 *
	 * @return str $type_html The HTML for the form element.
	 */
	private function get_blogtype() {

		// TODO: Move to Formatter class when install has a lighter touch.

		// Init.
		$type_html = '';

		// Get data.
		$type = $this->get_blogtype_data();

		// If we have type data.
		if ( ! empty( $type ) ) {

			// Show it.
			$type_html = '
			<tr valign="top">
				<th scope="row"><label for="cp_blog_type">' . $type['label'] . '</label></th>
				<td><select id="cp_blog_type" name="cp_blog_type">
					' . $type['element'] . '
				</select></td>
			</tr>
			';

		}

		// --<
		return $type_html;

	}

	/**
	 * Get Blog Type form elements.
	 *
	 * @since 3.3
	 *
	 * @return array $return Keyed array of form data.
	 */
	public function get_blogtype_data() {

		// TODO: Move to Formatter class when install has a lighter touch.

		// Init.
		$return = [];

		// Assume no types.
		$types = [];

		/**
		 * Build Text Format options.
		 *
		 * @since 3.3.1
		 *
		 * @param array $types Empty by default since others add them.
		 */
		$types = apply_filters( 'cp_blog_type_options', $types );

		// If we got any, use them.
		if ( ! empty( $types ) ) {

			// Define Blog Type label.
			$type_label = __( 'Document Type', 'commentpress-core' );

			/**
			 * Filters the Blog Type label.
			 *
			 * @since 3.3.1
			 *
			 * @param str $type_title The the Blog Type label.
			 */
			$type_label = apply_filters( 'cp_blog_type_label', $type_label );

			// Add to return.
			$return['label'] = $type_label;

			// Construct options.
			$type_option_list = [];
			$n = 0;
			foreach ( $types as $type ) {
				$type_option_list[] = '<option value="' . $n . '">' . $type . '</option>';
				$n++;
			}
			$type_options = implode( "\n", $type_option_list );

			// Add to return.
			$return['element'] = $type_options;

		}

		// --<
		return $return;

	}

}
