<?php
/**
 * CommentPress Core Theme Customizer.
 *
 * Handles Customizer functionality in CommentPress themes.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;



if ( ! function_exists( 'commentpress_admin_menu' ) ) :

	/**
	 * Adds more prominent menu item.
	 *
	 * @since 3.0
	 */
	function commentpress_admin_menu() {

		// Add the Customize link to the admin menu.
		add_theme_page(
			__( 'Customize', 'commentpress-core' ),
			__( 'Customize', 'commentpress-core' ),
			'edit_theme_options',
			'customize.php'
		);

	}

endif;

// Add callback for the above.
// TODO: Is this necessary?
add_action( 'admin_menu', 'commentpress_admin_menu' );



if ( ! function_exists( 'commentpress_customizer_get_site_image_title' ) ) :

	/**
	 * Gets the "Site Image" label.
	 *
	 * @since 4.0
	 *
	 * @return str The default "Site Image" label.
	 */
	function commentpress_customizer_get_site_image_title() {

		/**
		 * Filters the "Site Image" label.
		 *
		 * @since 3.8.6
		 *
		 * @param str The default "Site Image" label.
		 */
		return apply_filters( 'commentpress_customizer_site_image_title', __( 'Site Image', 'commentpress-core' ) );

	}

endif;



if ( ! function_exists( 'commentpress_customizer_get_site_image_description' ) ) :

	/**
	 * Gets the "Site Image" description.
	 *
	 * @since 4.0
	 *
	 * @return str The default "Site Image" label.
	 */
	function commentpress_customizer_get_site_image_description() {

		/**
		 * Filters the "Site Image" description.
		 *
		 * @since 3.8.6
		 *
		 * @param str The default "Site Image" description.
		 */
		return apply_filters( 'commentpress_customizer_site_image_description', __( 'Choose an image to represent this site. Other plugins may use this image to illustrate this site - in multisite directory listings, for example.', 'commentpress-core' ) );

	}

endif;



if ( ! function_exists( 'commentpress_customizer_get_site_logo_description' ) ) :

	/**
	 * Gets the "Site Logo" description.
	 *
	 * @since 4.0
	 *
	 * @return str The default "Site Logo" label.
	 */
	function commentpress_customizer_get_site_logo_description() {

		/**
		 * Filters the "Site Logo" description.
		 *
		 * @since 3.8.6
		 *
		 * @param str The default "Site Logo" description.
		 */
		return apply_filters( 'commentpress_customizer_site_logo_description', __( 'You may prefer to display an image instead of text in the header of your site. The image must be a maximum of 70px tall. If it is less tall, then you can adjust the vertical alignment using the "Top padding in px" setting below.', 'commentpress-core' ) );

	}

endif;



if ( ! function_exists( 'commentpress_customize_register' ) ) :

	/**
	 * Implements CommentPress Core Theme options in the Theme Customizer.
	 *
	 * @since 3.0
	 *
	 * @param object $wp_customize Theme Customizer object.
	 */
	function commentpress_customize_register( $wp_customize ) {

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return;
		}

		// Add "Site Image".
		commentpress_customize_site_image( $wp_customize );

		// Add "Site Logo".
		commentpress_customize_site_logo( $wp_customize );

		// Add "Header Background Colour".
		commentpress_customize_header_bg_color( $wp_customize );

	}

endif;

// Add callback for the above.
add_action( 'customize_register', 'commentpress_customize_register' );



if ( ! function_exists( 'commentpress_customize_site_image' ) ) :

	/**
	 * Implements CommentPress Core "Site Image" in the Theme Customizer.
	 *
	 * @since 3.8.5
	 *
	 * @param object $wp_customize Theme Customizer object.
	 */
	function commentpress_customize_site_image( $wp_customize ) {

		// Get core plugin reference.
		$core = commentpress_core();

		// Bail if BuddyPress Group Blog.
		if ( ! empty( $core ) && $core->bp->is_groupblog() ) {
			return;
		}

		// Include our class file.
		include_once COMMENTPRESS_PLUGIN_PATH . 'includes/core/assets/includes/theme/class-customizer-site-image.php';

		/*
		// Register control - not needed as yet, but is if we want to fully extend.
		$wp_customize->register_control_type( 'WP_Customize_Site_Image_Control' );
		*/

		// Add customizer section title.
		$wp_customize->add_section(
			'cp_site_image',
			[
				'title' => commentpress_customizer_get_site_image_title(),
				'priority' => 25,
			]
		);

		// Add image setting.
		$wp_customize->add_setting(
			'commentpress_theme_settings[cp_site_image]',
			[
				'default' => '',
				'capability' => 'edit_theme_options',
				'type' => 'option',
			]
		);

		// Add image control.
		$wp_customize->add_control(
			new WP_Customize_Site_Image_Control(
				$wp_customize,
				'cp_site_image',
				[
					'label' => commentpress_customizer_get_site_image_title(),
					'description' => commentpress_customizer_get_site_image_description(),
					'section' => 'cp_site_image',
					'settings' => 'commentpress_theme_settings[cp_site_image]',
					'priority' => 1,
				]
			)
		);

	}

endif;



if ( ! function_exists( 'commentpress_customize_site_logo' ) ) :

	/**
	 * Implements CommentPress Core "Site Logo" in the Theme Customizer.
	 *
	 * @since 3.8.5
	 *
	 * @param object $wp_customize Theme Customizer object.
	 */
	function commentpress_customize_site_logo( $wp_customize ) {

		// Get core plugin reference.
		$core = commentpress_core();

		// Bail if BuddyPress Group Blog.
		if ( empty( $core ) && $core->bp->is_groupblog() ) {
			return;
		}

		// Add customizer section title.
		$wp_customize->add_section(
			'cp_inline_header_image',
			[
				'title' => __( 'Site Logo', 'commentpress-core' ),
				'priority' => 35,
			]
		);

		// Add image setting.
		$wp_customize->add_setting(
			'commentpress_theme_settings[cp_inline_header_image]',
			[
				'default' => '',
				'capability' => 'edit_theme_options',
				'type' => 'option',
			]
		);

		// Add image control.
		$wp_customize->add_control(
			new WP_Customize_Image_Control(
				$wp_customize,
				'cp_inline_header_image',
				[
					'label' => __( 'Logo Image', 'commentpress-core' ),
					'description' => commentpress_customizer_get_site_logo_description(),
					'section' => 'cp_inline_header_image',
					'settings' => 'commentpress_theme_settings[cp_inline_header_image]',
					'priority' => 1,
				]
			)
		);

		// Add padding setting.
		$wp_customize->add_setting(
			'commentpress_theme_settings[cp_inline_header_padding]',
			[
				'default' => '',
				'capability' => 'edit_theme_options',
				'type' => 'option',
			]
		);

		// Add text control.
		$wp_customize->add_control(
			'commentpress_theme_settings[cp_inline_header_padding]',
			[
				'label' => __( 'Top padding in px', 'commentpress-core' ),
				'section' => 'cp_inline_header_image',
				'type' => 'text',
			]
		);

	}

endif;



if ( ! function_exists( 'commentpress_customize_header_bg_color' ) ) :

	/**
	 * Implements CommentPress Core "Header Background Colour" in the Theme Customizer.
	 *
	 * @since 3.8.5
	 *
	 * @param object $wp_customize Theme Customizer object.
	 */
	function commentpress_customize_header_bg_color( $wp_customize ) {

		// Get core plugin reference.
		$core = commentpress_core();
		if ( empty( $core ) ) {
			return;
		}

		// Add color picker setting.
		$wp_customize->add_setting(
			'commentpress_header_bg_color',
			[
				'default' => '#' . $core->db->header_bg_colour,
				//'capability' => 'edit_theme_options',
				//'type' => 'option',
			]
		);

		// Add color picker control.
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				'commentpress_header_bg_color',
				[
					'label' => __( 'Header Background Colour', 'commentpress-core' ),
					'section' => 'colors',
					'settings' => 'commentpress_header_bg_color',
				]
			)
		);

	}

endif;
