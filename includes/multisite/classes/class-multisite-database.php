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
	 * Initialises this object.
	 *
	 * @since 3.3
	 */
	public function initialise() {

		// Only do this once.
		static $done;
		if ( isset( $done ) && $done === true ) {
			return;
		}

		// Register hooks.
		$this->register_hooks();

		// We're done.
		$done = true;

	}

	/**
	 * Register WordPress hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

		// Acts when plugin is activated.
		add_action( 'commentpress/activate', [ $this, 'plugin_activate' ], 10 );

		// Act when plugin is deactivated.
		add_action( 'commentpress/deactivate', [ $this, 'plugin_deactivate' ], 100 );

		// Initialise settings when plugins are loaded.
		add_action( 'plugins_loaded', [ $this, 'settings_initialise' ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Runs when the plugin is activated.
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_activate( $network_wide = false ) {

		// Bail if plugin is not network activated.
		if ( ! $network_wide ) {
			return;
		}

		// Initialise settings.
		$network_activation = true;
		$this->settings_initialise( $network_activation );

		// Save settings.
		$this->settings_save();

	}

	/**
	 *  Runs when the plugin is deactivated.
	 *
	 * @since 4.0
	 *
	 * @param bool $network_wide True if network-activated, false otherwise.
	 */
	public function plugin_deactivate( $network_wide = false ) {

		// Bail if plugin is not network activated.
		if ( ! $network_wide ) {
			return;
		}

		// Keep Site Options when deactivating.

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

		// Store new CommentPress Multisite version.
		$this->option_wpms_set( $this->option_version, $version );

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

		// True if no version or we have a multisite install and it's lower than this one.
		if ( empty( $version ) || version_compare( COMMENTPRESS_VERSION, $version, '>' ) ) {
			return true;
		}

		// Fallback.
		return false;

	}

	// -------------------------------------------------------------------------

	/**
	 * Initialises the settings.
	 *
	 * @since 4.0
	 *
	 * @param bool $network_activation True during network activation, false otherwise.
	 */
	public function settings_initialise( $network_activation = false ) {

		/*
		// Bail if plugin is not activated network-wide - unless activating network-wide.
		if ( ! $network_activation && 'mu_sitewide' !== $this->multisite->plugin->plugin_context_get() ) {
			return;
		}
		*/

		// Load installed plugin version.
		$this->plugin_version = $this->version_get();

		// Load settings array.
		$this->settings = $this->settings_get();

		// Store version if there has been a change.
		if ( $this->version_outdated() ) {
			$this->version_set( COMMENTPRESS_VERSION );
			$this->is_upgrade = true;
		}

		// Settings upgrade tasks.
		$this->settings_upgrade();

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
		// Add "Some setting" if it does not exist.
		if ( ! $this->setting_exists( 'some_setting' ) ) {
			$settings = $this->settings_get_defaults();
			$this->setting_set( 'some_setting', $settings['some_setting'] );
			$save = true;
		}

		// Maybe remove "Some setting" if it exists.
		if ( $this->setting_exists( 'some_setting' ) ) {
			$this->setting_delete( 'some_setting' );
			$save = true;
		}

		// Things to always check on upgrade.
		if ( $this->is_upgrade ) {
			// Add them here.
			$save = true;
		}
		*/

		// Maybe remove "Disable translation workflow" setting.
		if ( $this->setting_exists( 'cpmu_disable_translation_workflow' ) ) {
			$this->setting_delete( 'cpmu_disable_translation_workflow' );
			$save = true;
		}

		/**
		 * Filters the "Save settings" flag.
		 *
		 * @since 4.0
		 *
		 * @param bool $save True if settings should be saved, false otherwise.
		 */
		$save = apply_filters( 'commentpress/multisite/settings/upgrade/save', $save );

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
	 * @return array $settings The array of default settings.
	 */
	public function settings_get_defaults() {

		// Init return.
		$settings = [];

		/**
		 * Filters the default settings.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Multisite_Sites::settings_get_defaults() (Priority: 10)
		 *
		 * @since 4.0
		 *
		 * @param array $settings The array of default settings.
		 */
		$settings = apply_filters( 'commentpress/multisite/settings/defaults', $settings );

		// --<
		return $settings;

	}

	/**
	 * Reverts all settings to their defaults.
	 *
	 * @since 4.0
	 */
	public function settings_reset() {

		// Overwrite the settings with the defaults.
		$this->settings = $this->settings_get_defaults();

		// Save settings.
		$this->settings_save();

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
		return wp_parse_args(
			$this->option_wpms_get( $this->option_settings, [] ),
			$this->settings_get_defaults()
		);

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

	// -------------------------------------------------------------------------

	/**
	 * Returns existence of a specified setting.
	 *
	 * @since 3.3
	 *
	 * @param str $name The name of the setting.
	 * @return bool True if the setting exists, false otherwise.
	 */
	public function setting_exists( $name ) {

		// Check if the setting exists in the settings array.
		return array_key_exists( $name, $this->settings );

	}

	/**
	 * Return a value for a specified setting.
	 *
	 * @since 3.3
	 *
	 * @param str   $name The name of the setting.
	 * @param mixed $default The default value for the setting.
	 * @return mixed The value of the setting if it exists, $default otherwise.
	 */
	public function setting_get( $name, $default = false ) {

		// Get setting.
		return array_key_exists( $name, $this->settings ) ? $this->settings[ $name ] : $default;

	}

	/**
	 * Sets a value for a specified setting.
	 *
	 * @since 3.3
	 *
	 * @param str   $name The name of the setting.
	 * @param mixed $value The value for the setting.
	 */
	public function setting_set( $name, $value = '' ) {

		// Set setting.
		$this->settings[ $name ] = $value;

	}

	/**
	 * Deletes a specified setting.
	 *
	 * @since 3.3
	 *
	 * @param str $name The name of the setting.
	 */
	public function setting_delete( $name ) {

		// Unset setting.
		unset( $this->settings[ $name ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Return existence of a specified Site Option.
	 *
	 * @since 3.3
	 *
	 * @param str $name The name of the Site Option.
	 * @return bool True if Site Option exists, false otherwise.
	 */
	public function option_wpms_exists( $name ) {

		// Get Site Option with unlikely default.
		if ( $this->option_wpms_get( $name, 'fenfgehgejgrkj' ) === 'fenfgehgejgrkj' ) {
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
	 * @param str   $name The name of the Site Option.
	 * @param mixed $default The default value for the Site Option.
	 * @return mixed The value of the Site Option if it exists, default otherwise.
	 */
	public function option_wpms_get( $name, $default = false ) {

		// Get Site Option.
		return get_site_option( $name, $default );

	}

	/**
	 * Sets the value for a specified Site Option.
	 *
	 * @since 3.3
	 *
	 * @param str   $name The name of the Site Option.
	 * @param mixed $value The value for the Site Option.
	 * @return bool True if the value was updated, false otherwise.
	 */
	public function option_wpms_set( $name, $value = '' ) {

		// Set Site Option.
		return update_site_option( $name, $value );

	}

	/**
	 * Deletes a specified Site Option.
	 *
	 * @since 4.0
	 *
	 * @param str $name The name of the Site Option.
	 */
	public function option_wpms_delete( $name ) {

		// Delete Site Option.
		delete_site_option( $name );

	}

}
