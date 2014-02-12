<?php /*
================================================================================
CommentPress Modern Theme Comment Form
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

Comment form template for CommentPress

--------------------------------------------------------------------------------
*/



// Do not delete these lines
if (!empty($_SERVER['SCRIPT_FILENAME']) AND 'comment_form.php' == basename($_SERVER['SCRIPT_FILENAME'])) {
	die ('Please do not load this page directly. Thanks!');
}



// access post
global $post;

?>



<!-- comment_form.php -->

<?php if ('open' == $post->comment_status) : ?>



<div id="respond_wrapper">



<div id="respond">



<div class="cancel-comment-reply">
	<p><?php cancel_comment_reply_link( 'Cancel' ); ?></p>
</div>



<h4 id="respond_title"><?php commentpress_comment_form_title( 
	__( 'Leave a Comment', 'commentpress-core' ), 
	__( 'Leave a Reply to %s', 'commentpress-core' ) 
); ?></h4>

<?php if ( get_option('comment_registration') AND !$user_ID ) : ?>

	<p>You must be <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php echo urlencode(get_permalink()); ?>">logged in</a> to post a comment.</p>

<?php else : ?>

	<?php
	
	// allow plugins to override showing the comment form
	$show_comment_form = apply_filters( 'commentpress_show_comment_form', true );
	
	// how did we do?
	if ( $show_comment_form ) { ?>

		<form action="<?php echo site_url( '/wp-comments-post.php' ); ?>" method="post" id="commentform">
	
		<fieldset id="author_details">
	
			<legend class="off-left"><?php _e( 'Your details', 'commentpress-core' ); ?></legend>
		
			<?php if ( $user_ID ) : ?>
		
				<p class="author_is_logged_in"><?php _e( 'Logged in as', 'commentpress-core' ); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a> &rarr; <a href="<?php echo wp_logout_url(get_permalink()); ?>" title="<?php _e( 'Log out of this account', 'commentpress-core' ); ?>"><?php _e( 'Log out', 'commentpress-core' ); ?></a></p>
		
			<?php else : ?>
		
				<p><label for="author"><small><?php _e( 'Name', 'commentpress-core' ); ?><?php if ($req) echo ' <span class="req">('.__( 'required', 'commentpress-core' ).')</span>'; ?></small></label><br />
				<input type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="30"<?php if ($req) echo ' aria-required="true"'; ?> /></p>
			
				<p><label for="email"><small><?php _e( 'Mail (will not be published)', 'commentpress-core' ); ?><?php if ($req) echo ' <span class="req">('.__( 'required', 'commentpress-core' ).')</span>'; ?></small></label><br />
				<input type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="30"<?php if ($req) { echo ' aria-required="true"'; } ?> /></p>
			
				<p class="author_not_logged_in"><label for="url"><small><?php _e( 'Website', 'commentpress-core' ); ?></small></label><br />
				<input type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="30" /></p>
		
			<?php endif; ?>
		
		</fieldset>
	
		<fieldset id="comment_details">
	
			<legend class="off-left"><?php _e( 'Your comment', 'commentpress-core' ); ?></legend>
		
			<label for="comment" class="off-left"><?php _e( 'Comment', 'commentpress-core' ); ?></label>
			<?php
		
			// in functions.php
			if ( false === commentpress_add_wp_editor() ) {
			
				?>
				<textarea name="comment" class="comment" id="comment" cols="100%" rows="10"></textarea>
				<?php
		
			}
		
			?>		
	
		</fieldset>
	
		<?php 
	
		// add default wp fields
		comment_id_fields();
	
		// get text sig input
		global $commentpress_core;
		if ( is_object( $commentpress_core ) ) {
			echo $commentpress_core->get_signature_field();
		}
	
		// add page for multipage situations
		global $page;
		if ( !empty( $page ) ) {
			echo "\n".'<input type="hidden" name="page" value="'.$page.'" />'."\n";
		}
	
		// compatibility with Subscribe to Comments Reloaded
		if ( function_exists( 'subscribe_reloaded_show' ) ) { ?>
			<div class="subscribe_reloaded_insert">
			<?php subscribe_reloaded_show(); ?>
			</div>
		<?php }
	
		?>

		<p id="respond_button"><input name="submit" type="submit" id="submit" value="<?php _e( 'Submit Comment', 'commentpress-core' ); ?>" /></p>

		<?php do_action('comment_form', $post->ID); ?>

		</form>
	
		<?php
	
	} // end check for plugin overrides
	
	?>

<?php endif; // If registration required and not logged in ?>



</div><!-- /respond -->



</div><!-- /respond_wrapper -->



<?php endif; // end open comment status check ?>



