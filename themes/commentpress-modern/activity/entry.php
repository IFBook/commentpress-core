<?php

/**
 * BuddyPress - Activity Stream (Single Item)
 *
 * This template is used by activity-loop.php and AJAX functions to show
 * each activity.
 *
 * @package BuddyPress
 * @subpackage bp-legacy
 */



// init group blog type
$groupblogtype = '';

// get current item
global $activities_template;
$current_activity = $activities_template->activity;
//print_r( array( 'a' => $current_activity ) ); die();

// for group activity...
if ( $current_activity->component == 'groups' ) {

	// get group blogtype
	$groupblogtype = groups_get_groupmeta( $current_activity->item_id, 'groupblogtype' );

	// add space before if we have it
	if ( $groupblogtype ) { $groupblogtype = ' '.$groupblogtype; }

}

?>

<!-- activity/entry.php -->

<?php do_action( 'bp_before_activity_entry' ); ?>

<li class="<?php bp_activity_css_class(); echo $groupblogtype; ?>" id="activity-<?php bp_activity_id(); ?>">

	<div class="activity-wrapper clearfix">

		<div class="activity-avatar">
			<a href="<?php bp_activity_user_link(); ?>">

				<?php bp_activity_avatar(); ?>

			</a>
		</div>

		<div class="activity-content">

			<div class="activity-header">

				<?php bp_activity_action(); ?>

			</div>

			<?php if ( bp_activity_has_content() ) : ?>

				<div class="activity-inner">

					<?php bp_activity_content_body(); ?>

				</div>

			<?php endif; ?>

			<?php do_action( 'bp_activity_entry_content' ); ?>

			<div class="activity-meta">

				<?php if ( bp_get_activity_type() == 'activity_comment' ) : ?>

					<a href="<?php bp_activity_thread_permalink(); ?>" class="button view bp-secondary-action" title="<?php esc_attr_e( 'View Conversation', 'buddypress' ); ?>"><?php _e( 'View Conversation', 'buddypress' ); ?></a>

				<?php endif; ?>

				<?php if ( is_user_logged_in() ) : ?>

					<?php if ( bp_activity_can_comment() ) : ?>

						<?php

						// construct comment link
						$comment_link = '<a href="' . bp_get_activity_comment_link() . '" class="button acomment-reply bp-primary-action" id="acomment-comment-' . bp_get_activity_id() . '">'.sprintf( __( 'Comment <span>%s</span>', 'commentpress-core' ), bp_activity_get_comment_count() ) . '</a>';

						// echo it, but allow plugin overrides first
						echo apply_filters( 'cp_activity_entry_comment_link', $comment_link );

						?>

					<?php endif; ?>

					<?php if ( bp_activity_can_favorite() ) : ?>

						<?php if ( !bp_get_activity_is_favorite() ) : ?>

							<a href="<?php bp_activity_favorite_link(); ?>" class="button fav bp-secondary-action" title="<?php esc_attr_e( 'Mark as Favorite', 'commentpress-core' ); ?>"><?php _e( 'Favorite', 'commentpress-core' ); ?></a>

						<?php else : ?>

							<a href="<?php bp_activity_unfavorite_link(); ?>" class="button unfav bp-secondary-action" title="<?php esc_attr_e( 'Remove Favorite', 'commentpress-core' ); ?>"><?php _e( 'Remove Favorite', 'commentpress-core' ); ?></a>

						<?php endif; ?>

					<?php endif; ?>

					<?php if ( bp_activity_user_can_delete() ) bp_activity_delete_link(); ?>

					<?php do_action( 'bp_activity_entry_meta' ); ?>

				<?php endif; ?>

			</div>

		</div>

		<?php do_action( 'bp_before_activity_entry_comments' ); ?>

		<?php if ( ( bp_activity_get_comment_count() || bp_activity_can_comment() ) || bp_is_single_activity() ) : ?>

			<div class="activity-comments">

				<?php bp_activity_comments(); ?>

				<?php if ( is_user_logged_in() && bp_activity_can_comment() ) : ?>

					<form action="<?php bp_activity_comment_form_action(); ?>" method="post" id="ac-form-<?php bp_activity_id(); ?>" class="ac-form"<?php bp_activity_comment_form_nojs_display(); ?>>
						<div class="ac-reply-avatar"><?php bp_loggedin_user_avatar( 'width=' . BP_AVATAR_THUMB_WIDTH . '&height=' . BP_AVATAR_THUMB_HEIGHT ); ?></div>
						<div class="ac-reply-content">
							<div class="ac-textarea">
								<textarea id="ac-input-<?php bp_activity_id(); ?>" class="ac-input bp-suggestions" name="ac_input_<?php bp_activity_id(); ?>"></textarea>
							</div>
							<input type="submit" name="ac_form_submit" value="<?php _e( 'Post', 'commentpress-core' ); ?>" /> &nbsp; <?php _e( 'or press esc to cancel.', 'commentpress-core' ); ?>
							<input type="hidden" name="comment_form_id" value="<?php bp_activity_id(); ?>" />
						</div>

						<?php do_action( 'bp_activity_entry_comments' ); ?>

						<?php wp_nonce_field( 'new_activity_comment', '_wpnonce_new_activity_comment' ); ?>

					</form>

				<?php endif; ?>

			</div>

		<?php endif; ?>

		<?php do_action( 'bp_after_activity_entry_comments' ); ?>

	</div><!-- /activity-wrapper -->

</li>

<?php do_action( 'bp_after_activity_entry' ); ?>
