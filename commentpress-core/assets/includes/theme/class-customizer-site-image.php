<?php
/**
 * CommentPress Core Customize Site Image Control class.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Customize Site Image Control class.
 *
 * This is incomplete at present, because the labels are not all overridden
 * the way we would like them, but it does at least allow us to save the
 * attachment ID of the uploaded image instead of the URL to the full size image.
 *
 * @see WP_Customize_Media_Control
 */
class WP_Customize_Site_Image_Control extends WP_Customize_Media_Control {

	/**
	 * Media type.
	 *
	 * @since 3.8.5
	 * @access public
	 * @var string $type The media type.
	 */
	public $type = 'media';

	/**
	 * Mime type.
	 *
	 * @since 3.8.5
	 * @access public
	 * @var string $mime_type The mime type.
	 */
	public $mime_type = 'image';

	/**
	 * Button labels.
	 *
	 * @since 3.8.5
	 * @access public
	 * @var array $button_labels The button labels.
	 */
	public $button_labels = [];

	/**
	 * Constructor.
	 *
	 * @param WP_Customize_Manager $manager The manager object.
	 * @param string $id The ID.
	 * @param array $args Extra arguments.
	 */
	public function __construct( $manager, $id, $args = [] ) {

		// Call parent constructor.
		parent::__construct( $manager, $id, $args );

		// Allow label to be filtered.
		$site_image = apply_filters( 'commentpress_customizer_site_image_title', __( 'Site Image', 'commentpress-core' ) );

		// Set labels.
		$this->button_labels = [
			'select'       => sprintf( __( 'Select %s', 'commentpress-core' ), $site_image ),
			'change'       => sprintf( __( 'Change %s', 'commentpress-core' ), $site_image ),
			'remove'       => sprintf( __( 'Remove %s', 'commentpress-core' ), $site_image ),
			'default'      => sprintf( __( 'Default %s', 'commentpress-core' ), $site_image ),
			'placeholder'  => sprintf( __( 'No %s selected', 'commentpress-core' ), $site_image ),
			'frame_title'  => sprintf( __( 'Select %s', 'commentpress-core' ), $site_image ),
			'frame_button' => sprintf( __( 'Choose %s', 'commentpress-core' ), $site_image ),
		];

	}

}
