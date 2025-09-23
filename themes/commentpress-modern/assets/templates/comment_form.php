<?php
/**
 * Comment Form Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Access globals.
global $post, $page;

// Get core plugin reference.
$core = commentpress_core();

// Get User data.
$user              = wp_get_current_user();
$user_display_name = $user->exists() ? $user->display_name : '';

/**
 * Force the Comment Form to be displayed.
 *
 * This is used by the (disabled) infinite scroll code.
 *
 * @since 3.6.3
 *
 * @param bool False by default, since the Comment Form is optionally displayed.
 */
$cp_force_form = apply_filters( 'commentpress_force_comment_form', false );

// Init identifying class.
$forced_class = '';

// Optionally override.
if ( $cp_force_form ) {

	// Init classes.
	$forced_classes = [ 'cp_force_displayed' ];
	if ( 'open' !== $post->comment_status ) {
		$forced_classes[] = 'cp_force_closed';
	}

	// Build class attribute.
	$forced_class = ' class="' . implode( ' ', $forced_classes ) . '"';

}

/**
 * Allow plugins to override showing the Comment form.
 *
 * @since 3.8
 *
 * @param bool True by default, since the Comment Form is usually displayed.
 */
$show_comment_form = apply_filters( 'commentpress_show_comment_form', true );

?>
<!-- comment_form.php -->
<?php if ( 'open' === $post->comment_status || $cp_force_form ) : ?>

	<div id="respond_wrapper"<?php echo $forced_class; /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>>
		<div id="respond">

			<div class="cancel-comment-reply">
				<p><?php cancel_comment_reply_link( __( 'Cancel', 'commentpress-core' ) ); ?></p>
			</div>

			<h4 id="respond_title">
				<?php

				commentpress_comment_form_title(
					__( 'Leave a Comment', 'commentpress-core' ),
					/* translators: %s: The name of the comment author. */
					__( 'Leave a Reply to %s', 'commentpress-core' )
				);

				?>
			</h4>

			<?php if ( ( get_option( 'comment_registration' ) && ! is_user_logged_in() ) || ! $show_comment_form ) : ?>

				<p class="commentpress_comment_form_hidden">
					<?php

					$must_log_in = sprintf(
						/* translators: 1: The opening login anchor tag, 2: The closing login anchor tag. */
						esc_html__( 'You must be %1$slogged in%2$s to post a comment.', 'commentpress-core' ),
						'<a href="' . esc_url( wp_login_url( get_permalink() ) ) . '">',
						'</a>'
					);

					/**
					 * Filters the "login required" text.
					 *
					 * @param string $must_log_in The default "login required" text.
					 */
					$must_log_in = apply_filters( 'commentpress_comment_form_hidden', $must_log_in );

					// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					echo $must_log_in;

					?>
				</p>

			<?php else : ?>

				<?php

				// Get required status.
				$req = get_option( 'require_name_email' );

				// Get commenter.
				$commenter = wp_get_current_commenter();

				?>

				<form action="<?php echo esc_url( site_url( '/wp-comments-post.php' ) ); ?>" method="post" id="commentform">

					<fieldset id="author_details">

						<legend class="off-left">
							<?php esc_html_e( 'Your details', 'commentpress-core' ); ?>
						</legend>

						<?php if ( is_user_logged_in() ) : ?>

							<p class="author_is_logged_in">
								<?php esc_html_e( 'Logged in as', 'commentpress-core' ); ?> <a href="<?php echo esc_url( get_edit_profile_url() ); ?>"><?php echo esc_html( $user_display_name ); ?></a> &rarr; <a href="<?php echo esc_url( wp_logout_url( get_permalink() ) ); ?> ?>" title="<?php esc_attr_e( 'Log out of this account', 'commentpress-core' ); ?>"><?php esc_html_e( 'Log out', 'commentpress-core' ); ?></a>
							</p>

						<?php else : ?>

							<p>
								<label for="author">
									<small>
										<?php esc_html_e( 'Name', 'commentpress-core' ); ?> <?php echo ( ! empty( $req ) ? '<span class="req">(' . esc_html__( 'required', 'commentpress-core' ) . ')</span>' : '' ); ?>
									</small>
								</label>
								<br />
								<input type="text" name="author" id="author" value="<?php echo esc_attr( $commenter['comment_author'] ); ?>" size="30"<?php echo ( ! empty( $req ) ? ' aria-required="true"' : '' ); ?> />
							</p>

							<p>
								<label for="email">
									<small>
										<?php esc_html_e( 'Mail (will not be published)', 'commentpress-core' ); ?> <?php echo ( ! empty( $req ) ? '<span class="req">(' . esc_html__( 'required', 'commentpress-core' ) . ')</span>' : '' ); ?>
									</small>
								</label>
								<br />
								<input type="text" name="email" id="email" value="<?php echo esc_attr( $commenter['comment_author_email'] ); ?>" size="30"<?php echo ( ! empty( $req ) ? ' aria-required="true"' : '' ); ?> />
							</p>

							<p class="author_not_logged_in">
								<label for="url">
									<small><?php esc_html_e( 'Website', 'commentpress-core' ); ?></small>
								</label>
								<br />
								<input type="text" name="url" id="url" value="<?php echo esc_attr( $commenter['comment_author_url'] ); ?>" size="30" />
							</p>

						<?php endif; ?>

					</fieldset>

					<fieldset id="comment_details">

						<legend class="off-left">
							<?php esc_html_e( 'Your comment', 'commentpress-core' ); ?>
						</legend>

						<label for="comment" class="off-left">
							<?php esc_html_e( 'Comment', 'commentpress-core' ); ?>
						</label>

						<?php if ( false === commentpress_add_wp_editor() ) : ?>
							<textarea name="comment" class="comment" id="comment" cols="100%" rows="10"></textarea>
						<?php endif; ?>

					</fieldset>

					<?php

					/**
					 * Fires before the default WordPress Comment fields are rendered.
					 *
					 * @since 3.8
					 *
					 * @param int $post_id The numeric ID of the Post.
					 */
					do_action( 'commentpress_comment_form_pre_comment_id_fields', $post->ID );

					?>

					<?php comment_id_fields(); ?>

					<?php if ( ! empty( $core ) ) : ?>
						<?php echo $core->parser->text_signature_field_get(); /* phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped */ ?>
					<?php endif; ?>

					<?php if ( ! empty( $page ) ) : /* Add Page for multipage situations. */ ?>
						<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />
					<?php endif; ?>

					<?php

					/**
					 * Fires after the default WordPress Comment fields are rendered.
					 *
					 * @since 3.8
					 *
					 * @param int $post_id The numeric ID of the Post.
					 */
					do_action( 'commentpress_comment_form_pre_submit', $post->ID );

					?>

					<p id="respond_button">
						<input name="submit" type="submit" id="submit" value="<?php esc_attr_e( 'Submit Comment', 'commentpress-core' ); ?>" />
					</p>

					<?php

					/**
					 * Fires the default WordPress Comment form action.
					 *
					 * @param int $post_id The numeric ID of the Post.
					 */
					do_action( 'comment_form', $post->ID );

					?>

				</form>

			<?php endif; /* If registration required and not logged in. */ ?>

		</div><!-- /respond -->
	</div><!-- /respond_wrapper -->

<?php endif; /* End open Comment status check. */ ?>
