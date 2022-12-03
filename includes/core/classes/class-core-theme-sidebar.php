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
	 * Sidebar option in Site Settings.
	 *
	 * @since 4.0
	 * @access public
	 * @var str $option_sidebar The "Default Sidebar" option in Site Settings.
	 */
	public $option_sidebar = 'cp_sidebar_default';

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

		// Inject form element into the "Theme Customisation" metabox on "Site Settings" screen.
		add_action( 'commentpress/core/settings/site/metabox/theme/after', [ $this, 'metabox_settings_get' ] );

		// Save Sidebar data from "Site Settings" screen.
		add_action( 'commentpress/core/settings/site/save/before', [ $this, 'save_for_settings' ] );

		// Inject form element into the "CommentPress Settings" metabox on "Edit Entry" screens.
		add_action( 'commentpress/core/entry/metabox/after', [ $this, 'metabox_post_get' ] );

		// Saves the Sidebar value on "Edit Entry" screens.
		add_action( 'commentpress/core/settings/post/saved', [ $this, 'save_for_post' ] );

	}

	// -------------------------------------------------------------------------

	/**
	 * Returns the "code" of the default Sidebar.
	 *
	 * @since 3.4
	 *
	 * @return str $return The "code" of the default Sidebar.
	 */
	public function default_get() {

		// Access Post object.
		global $post;

		/**
		 * Filters the default sidebar.
		 *
		 * @since 3.9.8
		 *
		 * @param str The default sidebar. Defaults to 'activity'.
		 */
		$return = apply_filters( 'commentpress_default_sidebar', 'activity' );

		// If this is not a commentable Entry.
		if ( ! ( $post instanceof WP_Post ) || ! $this->core->parser->is_commentable() ) {

			// Get option (we don't need to look at the Entry meta in this case).
			$default = $this->core->db->setting_get( $this->option_sidebar );

			// Use it unless it's "comments".
			if ( $default !== 'comments' ) {
				$return = $default;
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

		// Is it a commentable Entry?
		if ( is_singular() ) {

			/*
			 * Some people have reported that db is not an object at this point,
			 * though I cannot figure out how this might be occurring - so we
			 * avoid the issue by checking if it is.
			 */
			if ( is_object( $this->core->db ) ) {

				// Is it a Special Page which have Comments-in-Page (or are not commentable)?
				if ( ! $this->core->pages_legacy->is_special_page() ) {

					// Get for this Post ID.
					$return = $this->get_for_post_id( $post->ID );

					// --<
					return $return;

				}

			}

		}

		// Not singular - must be either "activity" or "toc".

		// Use default unless it's "comments".
		$default = $this->core->db->setting_get( $this->option_sidebar );
		if ( $default !== 'comments' ) {
			$return = $default;
		}

		// --<
		return $return;

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
	 * Adds our form element to the "Theme Customisation" metabox.
	 *
	 * @since 4.0
	 */
	public function metabox_settings_get() {

		// Allow this to be disabled.
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) {
			return;
		}

		// Get the value of the option.
		$sidebar = $this->core->db->setting_get( $this->option_sidebar, 'comments' );

		?>

		<tr valign="top">
			<th scope="row">
				<label for="<?php echo $this->option_sidebar; ?>"><?php esc_html_e( 'Default active sidebar', 'commentpress-core' ); ?></label>
			</th>
			<td>
				<select id="<?php echo $this->option_sidebar; ?>" name="<?php echo $this->option_sidebar; ?>">
					<option value="toc" <?php echo ( ( $sidebar == 'contents' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Contents', 'commentpress-core' ); ?></option>
					<option value="activity" <?php echo ( ( $sidebar == 'activity' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Activity', 'commentpress-core' ); ?></option>
					<option value="comments" <?php echo ( ( $sidebar == 'comments' ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Comments', 'commentpress-core' ); ?></option>
				</select>
				<p class="description"><?php esc_html_e( 'This setting can be overridden on individual entries.', 'commentpress-core' ); ?></p>
			</td>
		</tr>

		<?php

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
	public function save_for_settings() {

		// Allow this to be disabled.
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) {
			return;
		}

		// Find the data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$sidebar = isset( $_POST[ $this->option_sidebar ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->option_sidebar ] ) ) : '';

		// Set default sidebar.
		$this->core->db->setting_set( $this->option_sidebar, $sidebar );

	}

	// -------------------------------------------------------------------------

	/**
	 * Adds our form element to the "CommentPress Settings" metabox.
	 *
	 * @since 4.0
	 *
	 * @param WP_Post $post The WordPress Post object.
	 */
	public function metabox_post_get( $post ) {

		// Allow this to be disabled.
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) {
			return;
		}

		// Get the Sidebar for this Entry.
		$sidebar = $this->get_for_post_id( $post->ID, $raw = true );

		?>

		<div class="<?php echo $this->option_sidebar; ?>_wrapper">

			<p><strong><label for="<?php echo $this->option_sidebar; ?>"><?php esc_html_e( 'Default Sidebar', 'commentpress-core' ); ?></label></strong></p>

			<p>
				<select id="<?php echo $this->option_sidebar; ?>" name="<?php echo $this->option_sidebar; ?>">
					<option value="" <?php echo ( empty( $sidebar ) ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Use default', 'commentpress-core' ); ?></option>
					<option value="toc" <?php echo ( $sidebar === 'toc' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Contents', 'commentpress-core' ); ?></option>
					<option value="activity" <?php echo ( $sidebar === 'activity' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Activity', 'commentpress-core' ); ?></option>
					<option value="comments" <?php echo ( $sidebar === 'comments' ? ' selected="selected"' : '' ); ?>><?php esc_html_e( 'Comments', 'commentpress-core' ); ?></option>
				</select>
			</p>

		</div>

		<?php

	}

	/**
	 * Saves the Sidebar for a given Entry.
	 *
	 * @since 4.0
	 *
	 * @param object $post The WordPress Post object.
	 */
	public function save_for_post( $post ) {

		// Allow this to be disabled.
		if ( apply_filters( 'commentpress_hide_sidebar_option', false ) ) {
			return;
		}

		// Find the data.
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		$sidebar = isset( $_POST[ $this->option_sidebar ] ) ? sanitize_text_field( wp_unslash( $_POST[ $this->option_sidebar ] ) ) : '';

		// Save Sidebar for this Entry.
		$this->set_for_post_id( $post->ID, $sidebar );

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

		// Return raw value if requested.
		if ( $raw === true ) {
			return $override;
		}

		// Default to current Sidebar.
		$sidebar = $this->core->db->setting_get( $this->option_sidebar );

		// Bail if we didn't get one.
		if ( empty( $override ) ) {
			return $sidebar;
		}

		// Override if different to the default Sidebar.
		if ( (string) $override !== (string) $sidebar ) {
			$sidebar = $override;
		}

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

		// Sanity check.
		$sidebar = (string) $sidebar;
		if ( empty( $sidebar ) ) {
			$this->delete_for_post_id( $post_id );
			return;
		}

		// Cast Sidebar value as string when updating.
		update_post_meta( $post_id, $this->meta_key, (string) $sidebar );

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

		// Get the current Sidebar.
		$sidebar_blog = $this->core->db->setting_get( $this->option_sidebar );

		// Get the Sidebar for this Post.
		$sidebar_post = $this->get_for_post_id( $post_id );

		// Do override check.
		if ( (string) $sidebar_blog !== (string) $sidebar_post ) {
			return true;
		}

		// Not overridden.
		return false;

	}

}
