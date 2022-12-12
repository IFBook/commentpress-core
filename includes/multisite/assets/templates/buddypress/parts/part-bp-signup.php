<?php
/**
 * Form elements for the BuddyPress Signup Form screen.
 *
 * Handles markup for the form elements on the BuddyPress Signup Form screen.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->parts_path; ?>part-bp-signup.php -->
<div id="cp-multisite-options">

	<h3><?php esc_html_e( 'CommentPress Options', 'commentpress-core' ); ?></h3>

	<input type="hidden" id="cp-bp-signup" name="cp-bp-signup" value="1" />

	<?php if ( ! empty( $forced ) ) : ?>

		<p><?php esc_html_e( 'Your new site will be CommentPress-enabled.', 'commentpress-core' ); ?></p>

		<input type="hidden" id="cpbp-new-blog" name="cpbp-new-blog" value="1" />

	<?php else : ?>

		<div class="checkbox">
			<p>
				<label for="cpbp-new-blog">
					<input type="checkbox" id="cpbp-new-blog" name="cpbp-new-blog" value="1" /> <?php esc_html_e( 'Enable CommentPress', 'commentpress-core' ); ?>
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
	do_action( 'commentpress/multisite/bp/signup/after' );

	?>

</div>
