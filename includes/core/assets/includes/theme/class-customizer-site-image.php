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
 * Attachment ID of the uploaded image instead of the URL to the full size image.
 *
 * @see WP_Customize_Media_Control
 *
 * @since 3.8.5
 */
class WP_Customize_Site_Image_Control extends WP_Customize_Media_Control {

	/**
	 * Media type.
	 *
	 * @since 3.8.5
	 * @access public
	 * @var string
	 */
	public $type = 'media';

	/**
	 * Mime type.
	 *
	 * @since 3.8.5
	 * @access public
	 * @var string
	 */
	public $mime_type = 'image';

	/**
	 * Button labels.
	 *
	 * @since 3.8.5
	 * @access public
	 * @var array
	 */
	public $button_labels = [];

	/**
	 * Constructor.
	 *
	 * @since 3.8.5
	 *
	 * @param WP_Customize_Manager $manager The manager object.
	 * @param string               $id The ID.
	 * @param array                $args Extra arguments.
	 */
	public function __construct( $manager, $id, $args = [] ) {

		// Call parent constructor.
		parent::__construct( $manager, $id, $args );

		// Get the "Site Image" label.
		$site_image = commentpress_customizer_get_site_image_title();

		// Set labels.
		$this->button_labels = [
			/* translators: %s: the "Site Image" label. */
			'select'       => sprintf( __( 'Select %s', 'commentpress-core' ), $site_image ),
			/* translators: %s: the "Site Image" label. */
			'change'       => sprintf( __( 'Change %s', 'commentpress-core' ), $site_image ),
			/* translators: %s: the "Site Image" label. */
			'remove'       => sprintf( __( 'Remove %s', 'commentpress-core' ), $site_image ),
			/* translators: %s: the "Site Image" label. */
			'default'      => sprintf( __( 'Default %s', 'commentpress-core' ), $site_image ),
			/* translators: %s: the "Site Image" label. */
			'placeholder'  => sprintf( __( 'No %s selected', 'commentpress-core' ), $site_image ),
			/* translators: %s: the "Site Image" label. */
			'frame_title'  => sprintf( __( 'Select %s', 'commentpress-core' ), $site_image ),
			/* translators: %s: the "Site Image" label. */
			'frame_button' => sprintf( __( 'Choose %s', 'commentpress-core' ), $site_image ),
		];

	}

}
