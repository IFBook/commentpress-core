<?php
/**
 * Form elements for the BuddyPress Groupblog Signup Form screen.
 *
 * Handles markup for the form elements on the BuddyPress Groupblog Signup Form screen.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo esc_html( $this->parts_path ); ?>part-bp-groupblog-signup.php -->
<br />
<div id="cp-multisite-options">

	<h3><?php esc_html_e( 'CommentPress Options', 'commentpress-core' ); ?></h3>

	<input type="hidden" id="cp-groupblog-signup" name="cp-groupblog-signup" value="1" />

	<?php if ( ! empty( $forced ) ) : ?>

		<p><?php esc_html_e( 'Your new site will be CommentPress-enabled.', 'commentpress-core' ); ?></p>

		<input type="hidden" id="cpbp-groupblog" name="cpbp-groupblog" value="1" />

	<?php else : ?>

		<div class="checkbox">
			<p>
				<label for="cpbp-groupblog">
					<input type="checkbox" id="cpbp-groupblog" name="cpbp-groupblog" value="1" /> <?php esc_html_e( 'Enable CommentPress', 'commentpress-core' ); ?>
				</label>
			</p>

			<p><?php esc_html_e( 'When you create a group blog, you can choose to enable it as a CommentPress blog. This is a "one time only" option because you cannot disable CommentPress from here once the group blog is created. Note: if you choose an existing blog as a group blog, setting this option will have no effect.', 'commentpress-core' ); ?></p>
		</div>

	<?php endif; ?>

	<?php

	/**
	 * Fires at the bottom of the "CommentPress" section of the Signup Form.
	 *
	 * @since 4.0
	 */
	do_action( 'commentpress/multisite/bp/groupblog/signup/after' );

	?>

</div>
