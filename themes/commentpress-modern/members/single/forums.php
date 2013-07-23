<?php

/**
 * BuddyPress - Users Forums
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

?>

<div class="item-list-tabs no-ajax" id="subnav" role="navigation">
	<ul>
		<?php bp_get_options_nav() ?>

		<li id="forums-order-select" class="last filter">

			<label for="forums-order-by"><?php _e( 'Order By:', 'commentpress-core' ); ?></label>
			<select id="forums-order-by">
				<option value="active"><?php _e( 'Last Active', 'commentpress-core' ); ?></option>
				<option value="popular"><?php _e( 'Most Posts', 'commentpress-core' ); ?></option>
				<option value="unreplied"><?php _e( 'Unreplied', 'commentpress-core' ); ?></option>

				<?php do_action( 'bp_forums_directory_order_options' ); ?>

			</select>
		</li>
	</ul>
</div><!-- .item-list-tabs -->

<?php

if ( bp_is_current_action( 'favorites' ) ) :
	locate_template( array( 'members/single/forums/topics.php' ), true );

else :
	do_action( 'bp_before_member_forums_content' ); ?>

	<div class="forums myforums">

		<?php locate_template( array( 'forums/forums-loop.php' ), true ); ?>

	</div>

	<?php do_action( 'bp_after_member_forums_content' ); ?>

<?php endif; ?>
