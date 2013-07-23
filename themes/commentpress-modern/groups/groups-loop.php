<?php

/**
 * BuddyPress - Groups Loop
 *
 * Querystring is set via AJAX in _inc/ajax.php - bp_dtheme_object_filter()
 *
 * @package BuddyPress
 * @subpackage bp-default
 */

?>

<!-- groups/groups-loop.php -->

<?php do_action( 'bp_before_groups_loop' ); ?>

<?php if ( bp_has_groups( bp_ajax_querystring( 'groups' ) ) ) : ?>

	<div id="pag-top" class="pagination">

		<div class="pag-count" id="group-dir-count-top">

			<?php bp_groups_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="group-dir-pag-top">

			<?php bp_groups_pagination_links(); ?>

		</div>

	</div>

	<?php do_action( 'bp_before_directory_groups_list' ); ?>

	<ul id="groups-list" class="item-list" role="main">

	<?php while ( bp_groups() ) : bp_the_group(); ?>
	
		<?php
		
		// init groupblogtype
		$groupblogtype = '';
		
		// only add classes when bp-groupblog is active
		if ( function_exists( 'get_groupblog_group_id' ) ) {
		
			// get group blogtype
			$groupblog_type = groups_get_groupmeta( bp_get_group_id(), 'groupblogtype' );
			
			// did we get one?
			if ( $groupblog_type ) {
			
				// add to default
				$groupblogtype = ' class="'.$groupblog_type.'"';
			
			}
			
		}
		
		?>

		<li<?php echo $groupblogtype; ?>>
		
			<div class="group-wrapper clearfix">

				<div class="item-avatar">
					<a href="<?php bp_group_permalink(); ?>"><?php bp_group_avatar( 'type=thumb&width=50&height=50' ); ?></a>
				</div>

				<div class="item">
					<div class="item-title"><a href="<?php bp_group_permalink(); ?>"><?php bp_group_name(); ?></a></div>
					<div class="item-meta"><span class="activity"><?php printf( __( 'active %s', 'commentpress-core' ), bp_get_group_last_active() ); ?></span></div>

					<div class="item-desc"><?php bp_group_description_excerpt(); ?></div>

					<?php do_action( 'bp_directory_groups_item' ); ?>

				</div>

				<div class="action">

					<?php do_action( 'bp_directory_groups_actions' ); ?>

					<div class="meta">

						<?php bp_group_type(); ?> / <?php bp_group_member_count(); ?>

					</div>

				</div>

			</div>

			<div class="clear"></div>
		</li>

	<?php endwhile; ?>

	</ul>

	<?php do_action( 'bp_after_directory_groups_list' ); ?>

	<div id="pag-bottom" class="pagination">

		<div class="pag-count" id="group-dir-count-bottom">

			<?php bp_groups_pagination_count(); ?>

		</div>

		<div class="pagination-links" id="group-dir-pag-bottom">

			<?php bp_groups_pagination_links(); ?>

		</div>

	</div>

<?php else: ?>

	<div id="message" class="info">
		<p><?php _e( 'There were no groups found.', 'commentpress-core' ); ?></p>
	</div>

<?php endif; ?>

<?php do_action( 'bp_after_groups_loop' ); ?>
