<?php
/**
 * CommentPress Core Common Theme Functions.
 *
 * Handles theme-specific Workflow tabs.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Theme Tabs Class.
 *
 * A class that encapsulates functionality of theme-specific Workflow tabs.
 *
 * Does not work in non-global loops, such as those made via WP_Query.
 *
 * @since 3.9.9
 */
class CommentPress_Theme_Tabs {

	/**
	 * Tabs Class.
	 *
	 * @since 3.9.9
	 * @access public
	 * @var str $tabs_class The Tabs Class.
	 */
	public $tabs_class = '';

	/**
	 * Tabs Classes.
	 *
	 * @since 3.9.9
	 * @access public
	 * @var str $tabs_classes The Tabs Classes.
	 */
	public $tabs_classes = '';

	/**
	 * Original Content.
	 *
	 * @since 3.9.9
	 * @access public
	 * @var str $original The Original Content.
	 */
	public $original = '';

	/**
	 * Literal Content.
	 *
	 * @since 3.9.9
	 * @access public
	 * @var str $literal The Literal Content.
	 */
	public $literal = '';

	/**
	 * Constructor.
	 *
	 * @since 3.9.9
	 */
	public function __construct() {

		// Nothing.

	}

	/**
	 * Returns a single instance of this object when called.
	 *
	 * @since 3.9.9
	 *
	 * @return object $instance Comment_Tagger instance.
	 */
	public static function instance() {

		// Store the instance locally to avoid private static replication.
		static $instance = null;

		// Instantiate if need be.
		if ( null === $instance ) {
			$instance = new CommentPress_Theme_Tabs();
		}

		// Always return instance.
		return $instance;

	}

	/**
	 * Initialise required data.
	 *
	 * @since 3.9.9
	 */
	public function initialise() {

		// Bail if already initialised.
		static $initialised = false;
		if ( $initialised ) {
			return;
		}

		// Bail if plugin not present.
		global $commentpress_core;
		if ( ! is_object( $commentpress_core ) ) {
			return;
		}

		// Bail if workflow not enabled.
		if ( '1' != $commentpress_core->db->option_get( 'cp_blog_workflow' ) ) {
			return;
		}

		// Okay, let's get our data.

		// Access post.
		global $post;

		// Set key.
		$key = '_cp_original_text';

		// If the custom field already has a value, get it.
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {
			$this->original = get_post_meta( $post->ID, $key, true );
		}

		// Set key.
		$key = '_cp_literal_translation';

		// If the custom field already has a value, get it.
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {
			$this->literal = get_post_meta( $post->ID, $key, true );
		}

		// Did we get either type of workflow content?
		if ( $this->literal != '' || $this->original != '' ) {

			// Override tabs class.
			$this->tabs_class = 'with-content-tabs';

			// Override tabs classes.
			$this->tabs_classes = ' class="' . $this->tabs_class . '"';

			// Prefix with space.
			$this->tabs_class = ' ' . $this->tabs_class;

		}

		// Flag as initialised.
		$initialised = true;

	}

	/**
	 * Echo Tabs.
	 *
	 * @since 3.9.9
	 */
	public function tabs() {

		// Bail if we have no tabs.
		if ( empty( $this->tabs_class ) ) {
			return;
		}

		// Bail if we get neither type of workflow content.
		if ( empty( $this->literal ) && empty( $this->original ) ) {
			return;
		}

		?>
		<ul id="content-tabs">
			<li id="content_header" class="default-content-tab">
				<h2><a href="#content"><?php echo apply_filters( 'commentpress_content_tab_content', __( 'Content', 'commentpress-core' ) ); ?></a></h2>
			</li>
			<?php if ( $this->literal != '' ) { ?>
				<li id="literal_header">
					<h2><a href="#literal"><?php echo apply_filters( 'commentpress_content_tab_literal', __( 'Literal', 'commentpress-core' ) ); ?></a></h2>
				</li>
			<?php } ?>
			<?php if ( $this->original != '' ) { ?>
				<li id="original_header">
					<h2><a href="#original"><?php echo apply_filters( 'commentpress_content_tab_original', __( 'Original', 'commentpress-core' ) ); ?></a></h2>
				</li>
			<?php } ?>
		</ul>
		<?php

	}

	/**
	 * Echo Tabs Content.
	 *
	 * @since 3.9.9
	 */
	public function tabs_content() {

		// Bail if we have no tabs.
		if ( empty( $this->tabs_class ) ) {
			return;
		}

		// Bail if we get neither type of workflow content.
		if ( empty( $this->literal ) && empty( $this->original ) ) {
			return;
		}

		// Did we get literal?
		if ( $this->literal != '' ) {

			?>
			<div id="literal" class="workflow-wrapper">
				<div class="post">
					<h2 class="post_title"><?php echo apply_filters( 'commentpress_literal_title', __( 'Literal Translation', 'commentpress-core' ) ); ?></h2>
					<?php echo apply_filters( 'cp_workflow_richtext_content', $this->literal ); ?>
				</div><!-- /post -->
			</div><!-- /literal -->
			<?php

		}

		// Did we get original?
		if ( $this->original != '' ) {

			?>
			<div id="original" class="workflow-wrapper">
				<div class="post">
					<h2 class="post_title"><?php echo apply_filters( 'commentpress_original_title', __( 'Original Text', 'commentpress-core' ) ); ?></h2>
					<?php echo apply_filters( 'cp_workflow_richtext_content', $this->original ); ?>
				</div><!-- /post -->
			</div><!-- /original -->
			<?php

		}

	}

}

/**
 * Init Theme Tabs.
 *
 * @since 3.9.9
 *
 * @return object CommentPress_Theme_Tabs The Theme Tabs instance.
 */
function commentpress_theme_tabs() {
	return CommentPress_Theme_Tabs::instance();
}

// Init the above.
commentpress_theme_tabs();



/**
 * Render Theme Tabs.
 *
 * @since 3.9.9
 */
function commentpress_theme_tabs_render() {

	// Get object and maybe init.
	$tabs = commentpress_theme_tabs();
	$tabs->initialise();

	// Print to screen.
	$tabs->tabs();

}

/**
 * Render Theme Tabs Content.
 *
 * @since 3.9.9
 */
function commentpress_theme_tabs_content_render() {

	// Get object and maybe init.
	$tabs = commentpress_theme_tabs();
	$tabs->initialise();

	// Print to screen.
	$tabs->tabs_content();

}

/**
 * Get Theme Tabs Class.
 *
 * @since 3.9.9
 *
 * @return str $tabs_class The tabs class.
 */
function commentpress_theme_tabs_class_get() {

	// Get object and maybe init.
	$tabs = commentpress_theme_tabs();
	$tabs->initialise();

	// --<
	return $tabs->tabs_class;

}

/**
 * Get Theme Tabs Classes.
 *
 * @since 3.9.9
 *
 * @return str $tabs_classes The tabs classes.
 */
function commentpress_theme_tabs_classes_get() {

	// Get object and maybe init.
	$tabs = commentpress_theme_tabs();
	$tabs->initialise();

	// --<
	return $tabs->tabs_classes;

}
