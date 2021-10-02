<?php /*
================================================================================
CommentPress Flat Theme Comment Form
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

Comment form template for CommentPress Core.

--------------------------------------------------------------------------------
*/



// Do not delete these lines.
if ( ! empty( $_SERVER['SCRIPT_FILENAME'] ) AND 'comment_form.php' == basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
	die( 'Please do not load this page directly. Thanks!' );
}



// Access globals.
global $post;

// Get user data.
$user = wp_get_current_user();
$user_identity = $user->exists() ? $user->display_name : '';



// Check force state (this is for infinite scroll).
$cp_force_form = apply_filters( 'commentpress_force_comment_form', false );

// Init identifying class.
$forced_class = '';

// Optionally override.
if ( $cp_force_form ) {

	// Init classes.
	$forced_classes = [ 'cp_force_displayed' ];
	if ( 'open' != $post->comment_status ) $forced_classes[] = 'cp_force_closed';

	// Build class attribute.
	$forced_class = ' class="' . implode( ' ', $forced_classes ) . '"';

}



/**
 * Allow plugins to override showing the comment form.
 *
 * @since 3.8
 */
$show_comment_form = apply_filters( 'commentpress_show_comment_form', true );

?>



<!-- comment_form.php -->

<?php if ( 'open' == $post->comment_status OR $cp_force_form ) : ?>



<div id="respond_wrapper"<?php echo $forced_class; ?>>



<div id="respond">



<div class="cancel-comment-reply">
	<p><?php cancel_comment_reply_link( 'Cancel' ); ?></p>
</div>



<h4 id="respond_title"><?php commentpress_comment_form_title(
	__( 'Leave a Comment', 'commentpress-core' ),
	__( 'Leave a Reply to %s', 'commentpress-core' )
); ?></h4>

<?php if ( get_option('comment_registration') AND ! is_user_logged_in() ) : ?>

	<p><?php

	echo sprintf(
		__( 'You must be <a href="%s">logged in</a> to post a comment.', 'commentpress-core' ),
		get_option('siteurl') . '/wp-login.php?redirect_to=' . urlencode( get_permalink() )
	);

	?></p>

<?php else : ?>

	<?php

	// Are we showing the comment form?
	if ( $show_comment_form ) {

		// Get required status.
		$req = get_option( 'require_name_email' );

		// Get commenter.
		$commenter = wp_get_current_commenter();

		?>

		<form action="<?php echo site_url( '/wp-comments-post.php' ); ?>" method="post" id="commentform">

		<fieldset id="author_details">

			<legend class="off-left"><?php _e( 'Your details', 'commentpress-core' ); ?></legend>

			<?php if ( is_user_logged_in() ) : ?>

				<p class="author_is_logged_in"><?php _e( 'Logged in as', 'commentpress-core' ); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a> &rarr; <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="<?php _e( 'Log out of this account', 'commentpress-core' ); ?>"><?php _e( 'Log out', 'commentpress-core' ); ?></a></p>

			<?php else : ?>

				<p><label for="author"><small><?php _e( 'Name', 'commentpress-core' ); ?><?php if ($req) echo ' <span class="req">(' . __( 'required', 'commentpress-core' ) . ')</span>'; ?></small></label><br />
				<input type="text" name="author" id="author" value="<?php echo esc_attr( $commenter['comment_author'] ); ?>" size="30"<?php if ($req) echo ' aria-required="true"'; ?> /></p>

				<p><label for="email"><small><?php _e( 'Mail (will not be published)', 'commentpress-core' ); ?><?php if ($req) echo ' <span class="req">(' . __( 'required', 'commentpress-core' ) . ')</span>'; ?></small></label><br />
				<input type="text" name="email" id="email" value="<?php echo esc_attr(  $commenter['comment_author_email'] ); ?>" size="30"<?php if ($req) { echo ' aria-required="true"'; } ?> /></p>

				<p class="author_not_logged_in"><label for="url"><small><?php _e( 'Website', 'commentpress-core' ); ?></small></label><br />
				<input type="text" name="url" id="url" value="<?php echo esc_attr( $commenter['comment_author_url'] ); ?>" size="30" /></p>

			<?php endif; ?>

		</fieldset>

		<fieldset id="comment_details">

			<legend class="off-left"><?php _e( 'Your comment', 'commentpress-core' ); ?></legend>

			<label for="comment" class="off-left"><?php _e( 'Comment', 'commentpress-core' ); ?></label>
			<?php

			// In theme-functions.php
			if ( false === commentpress_add_wp_editor() ) {

				?>
				<textarea name="comment" class="comment" id="comment" cols="100%" rows="10"></textarea>
				<?php

			}

			?>

		</fieldset>

		<?php do_action('commentpress_comment_form_pre_comment_id_fields', $post->ID); ?>

		<?php

		// Add default wp fields.
		comment_id_fields();

		// Is CommentPress Core active?
		global $commentpress_core;
		if ( is_object( $commentpress_core ) ) {

			// Get text sig input.
			echo $commentpress_core->get_signature_field();

		}

		// Add page for multipage situations.
		global $page;
		if ( !empty( $page ) ) {
			echo "\n" . '<input type="hidden" name="page" value="' . $page . '" />' . "\n";
		}

		// Compatibility with Subscribe to Comments Reloaded.
		if ( function_exists( 'subscribe_reloaded_show' ) ) { ?>
			<div class="subscribe_reloaded_insert">
			<?php subscribe_reloaded_show(); ?>
			</div>
		<?php }

		?>

		<?php do_action('commentpress_comment_form_pre_submit', $post->ID); ?>

		<p id="respond_button"><input name="submit" type="submit" id="submit" value="<?php _e( 'Submit Comment', 'commentpress-core' ); ?>" /></p>

		<?php do_action('comment_form', $post->ID); ?>

		</form>

		<?php

	} else { // End check for plugin overrides.

		?>

		<p class="commentpress_comment_form_hidden"><?php
			echo apply_filters(
				'commentpress_comment_form_hidden',
				sprintf(
					__( 'You must be <a href="%s">logged in</a> to post a comment.', 'commentpress-core' ),
					get_option('siteurl') . '/wp-login.php?redirect_to=' . urlencode( get_permalink() )
				)
			);
		?></p>

		<?php

	}

	?>

<?php endif; // If registration required and not logged in. ?>



</div><!-- /respond -->



</div><!-- /respond_wrapper -->



<?php endif; // End open comment status check. ?>



