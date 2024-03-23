<?php
/**
 * Signup Form screen Sites form elements.
 *
 * Handles markup for the Sites form elements on the "Signup Form" screen.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo esc_html( $this->parts_path ); ?>part-sites-signup.php -->
<div id="cp-multisite-options">

	<h3><?php esc_html_e( 'CommentPress:', 'commentpress-core' ); ?></h3>

	<input type="hidden" id="cp-sites-signup" name="cp-sites-signup" value="1" />

	<?php if ( ! empty( $forced ) ) : ?>

		<p><?php esc_html_e( 'Your new site will be CommentPress-enabled.', 'commentpress-core' ); ?></p>

		<input type="hidden" id="cpmu-new-blog" name="cpmu-new-blog" value="1" />

	<?php else : ?>

		<div class="checkbox">
			<p>
				<label for="cpmu-new-blog">
					<input type="checkbox" id="cpmu-new-blog" name="cpmu-new-blog" value="1" /> <?php esc_html_e( 'Enable CommentPress', 'commentpress-core' ); ?>
				</label>
			</p>
		</div>

	<?php endif; ?>

	<?php

	/**
	 * Fires at the bottom of the "CommentPress" section of the Signup Form.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/sites/signup/after' );

	?>

</div>
