<?php

/**
 * BuddyPress - Users Friends
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>
		<?php if ( bp_is_my_profile() ) bp_get_options_nav(); ?>

		<?php if ( !bp_is_current_action( 'requests' ) ) : ?>

			<li id="members-order-select" class="last filter">

				<label for="members-all"><?php _e( 'Order By:', 'commentpress-core' ) ?></label>
				<select id="members-all">
					<option value="active"><?php _e( 'Last Active', 'commentpress-core' ) ?></option>
					<option value="newest"><?php _e( 'Newest Registered', 'commentpress-core' ) ?></option>
					<option value="alphabetical"><?php _e( 'Alphabetical', 'commentpress-core' ) ?></option>

					<?php do_action( 'bp_member_blog_order_options' ) ?>

				</select>
			</li>

		<?php endif; ?>

	</ul>
</div>

<?php

if ( bp_is_current_action( 'requests' ) ) :
	 locate_template( array( 'members/single/friends/requests.php' ), true );

else :
	do_action( 'bp_before_member_friends_content' ); ?>

	<div class="members friends">

		<?php locate_template( array( 'members/members-loop.php' ), true ); ?>

	</div><!-- .members.friends -->

	<?php do_action( 'bp_after_member_friends_content' ); ?>

<?php endif; ?>
