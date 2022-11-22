<?php
/**
 * Page Navigation Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// Set default link names.
$previous_title = apply_filters( 'cp_nav_previous_link_title', __( 'Older Entries', 'commentpress-core' ) );
$next_title = apply_filters( 'cp_nav_next_link_title', __( 'Newer Entries', 'commentpress-core' ) );

?>
<!-- page_navigation.php -->
<div class="page_navigation">

	<?php

	if ( is_page() ) {

		// Maybe show our custom Page Navigation.
		$cp_page_nav = commentpress_page_navigation();
		if ( $cp_page_nav != '' ) {
			?>
			<ul>
				<?php echo $cp_page_nav; ?>
			</ul>
			<?php
		}

	} elseif ( is_single() ) {

		?>
		<ul class="blog_navigation">
			<?php next_post_link( '<li class="alignright">%link</li>' ); ?>
			<?php previous_post_link( '<li class="alignleft">%link</li>' ); ?>
		</ul>
		<?php

	} elseif ( is_home() || is_post_type_archive() ) {

		$nl = get_next_posts_link( $previous_title );
		$pl = get_previous_posts_link( $next_title );

		// Did we get either?
		if ( ! empty( $nl ) || ! empty( $pl ) ) {
			?>
			<ul class="blog_navigation">
				<?php if ( ! empty( $pl ) ) { ?>
					<li class="alignright"><?php echo $pl; ?></li>
				<?php } ?>
				<?php if ( ! empty( $nl ) ) { ?>
					<li class="alignleft"><?php echo $nl; ?></li>
				<?php } ?>
			</ul>
			<?php
		}

	} elseif ( is_day() || is_month() || is_year() ) {

		$nl = get_next_posts_link( $previous_title );
		$pl = get_previous_posts_link( $next_title );

		// Did we get either?
		if ( ! empty( $nl ) || ! empty( $pl ) ) {
			?>
			<ul class="blog_navigation">
				<?php if ( ! empty( $pl ) ) { ?>
					<li class="alignright"><?php echo $pl; ?></li>
				<?php } ?>
				<?php if ( ! empty( $nl ) ) { ?>
					<li class="alignleft"><?php echo $nl; ?></li>
				<?php } ?>
			</ul>
			<?php
		}

	} elseif ( is_search() ) {

		$nl = get_next_posts_link( __( 'More Results', 'commentpress-core' ) );
		$pl = get_previous_posts_link( __( 'Previous Results', 'commentpress-core' ) );

		// Did we get either?
		if ( ! empty( $nl ) || ! empty( $pl ) ) {
			?>
			<ul class="blog_navigation">
				<?php if ( ! empty( $nl ) ) { ?>
					<li class="alignright"><?php echo $nl; ?></li>
				<?php } ?>
				<?php if ( ! empty( $pl ) ) { ?>
					<li class="alignleft"><?php echo $pl; ?></li>
				<?php } ?>
			</ul>
			<?php
		}

	} elseif ( is_category() ) {

		$nl = get_next_posts_link( __( 'More Results', 'commentpress-core' ) );
		$pl = get_previous_posts_link( __( 'Previous Results', 'commentpress-core' ) );

		// Did we get either?
		if ( ! empty( $nl ) || ! empty( $pl ) ) {
			?>
			<ul class="blog_navigation">
				<?php if ( ! empty( $nl ) ) { ?>
					<li class="alignright"><?php echo $nl; ?></li>
				<?php } ?>
				<?php if ( ! empty( $pl ) ) { ?>
					<li class="alignleft"><?php echo $pl; ?></li>
				<?php } ?>
			</ul>
			<?php

		}

	} elseif ( is_tag() || is_tax() ) {

		$nl = get_next_posts_link( __( 'More Results', 'commentpress-core' ) );
		$pl = get_previous_posts_link( __( 'Previous Results', 'commentpress-core' ) );

		// Did we get either?
		if ( ! empty( $nl ) || ! empty( $pl ) ) {
			?>
			<ul class="blog_navigation">
				<?php if ( ! empty( $nl ) ) { ?>
					<li class="alignright"><?php echo $nl; ?></li>
				<?php } ?>
				<?php if ( ! empty( $pl ) ) { ?>
					<li class="alignleft"><?php echo $pl; ?></li>
				<?php } ?>
			</ul>
			<?php

		}

	}

	?>

</div><!-- /page_navigation -->
