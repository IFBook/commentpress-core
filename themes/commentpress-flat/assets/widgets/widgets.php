<?php /*
================================================================================
CommentPress Core Widgets
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

--------------------------------------------------------------------------------
*/



/**
 * Makes a custom Widget for displaying License Information with CommentPress Core.
 *
 * @since 3.4
 */
class Commentpress_License_Widget extends WP_Widget {



	/**
	 * Constructor registers widget with WordPress.
	 *
	 * @since 3.4
	 */
	public function __construct() {

		// Widget settings.
		$widget_options = [
			'classname' => 'commentpress_widget',
			'description' => __( 'This widget is supplied by CommentPress Core for placing HTML in the page footer - for example, copyright or licensing information.', 'commentpress-core' ),
		];

		// Instantiate parent.
		parent::__construct(
			'commentpress_text', // Base ID.
			__( 'CommentPress Footer Text', 'commentpress-core' ), // Name.
			$widget_options // Options.
		);

	}


	/**
	 * Outputs the HTML for this widget.
	 *
	 * @since 3.4
	 *
	 * @param array $args An array of standard parameters for widgets in this theme.
	 * @param array $instance An array of settings for this widget instance.
	 */
	public function widget( $args, $instance ) {

		extract( $args );

		// Get data.
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$text = apply_filters( 'commentpress_widget', empty( $instance['text'] ) ? '' : $instance['text'], $instance );

		echo $before_widget;

		// Show title.
		if ( ! empty( $title ) ) {
			echo $before_title . $title . $after_title;
		}

		?>
			<div class="textwidget"><?php echo ! empty( $instance['filter'] ) ? wpautop( $text ) : $text; ?></div>
		<?php

		echo $after_widget;

	}



	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @since 3.4
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 * @return array $instance Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {

		// Store old instance.
		$instance = $old_instance;

		// Sanitise title.
		$instance['title'] = strip_tags( $new_instance['title'] );

		// Maybe allow HTML.
		if ( current_user_can( 'unfiltered_html' ) ) {
			$instance['text'] =  $new_instance['text'];
		} else {
			// Wp_filter_post_kses() expects slashed.
			$instance['text'] = stripslashes( wp_filter_post_kses( addslashes( $new_instance['text'] ) ) );
		}

		$instance['filter'] = isset( $new_instance['filter'] );

		// --<
		return $instance;

	}



	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @since 3.4
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, [ 'title' => '', 'text' => '' ] );
		$title = strip_tags( $instance['title'] );
		$text = esc_textarea( $instance['text'] );

		?>
		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'commentpress-core' ); ?></label>

		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>

		<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id( 'text' ); ?>" name="<?php echo $this->get_field_name( 'text' ); ?>"><?php echo $text; ?></textarea>

		<p><input id="<?php echo $this->get_field_id( 'filter' ); ?>" name="<?php echo $this->get_field_name( 'filter' ); ?>" type="checkbox" <?php checked( isset( $instance['filter'] ) ? $instance['filter'] : 0 ); ?> />&nbsp;<label for="<?php echo $this->get_field_id( 'filter' ); ?>"><?php _e( 'Automatically add paragraphs', 'commentpress-core' ); ?></label></p>
		<?php

	}



} // Ends class Commentpress_License_Widget



