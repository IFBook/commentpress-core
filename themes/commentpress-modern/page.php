<?php get_header(); ?>



<!-- page.php -->

<div id="wrapper">



<?php if (have_posts()) : while (have_posts()) : the_post();



	// access post
	global $post;



	// init class values
	$tabs_class = '';
	$tabs_classes = '';

	// init workflow items
	$original = '';
	$literal = '';

	// do we have workflow?
	if ( is_object( $commentpress_core ) ) {

		// get workflow
		$_workflow = $commentpress_core->db->option_get( 'cp_blog_workflow' );

		// is it enabled?
		if ( $_workflow == '1' ) {

			// okay, let's add our tabs

			// set key
			$key = '_cp_original_text';

			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) != '' ) {

				// get it
				$original = get_post_meta( $post->ID, $key, true );

			}

			// set key
			$key = '_cp_literal_translation';

			// if the custom field already has a value...
			if ( get_post_meta( $post->ID, $key, true ) != '' ) {

				// get it
				$literal = get_post_meta( $post->ID, $key, true );

			}

			// did we get either type of workflow content?
			if ( $literal != '' OR $original != '' ) {

				// override tabs class
				$tabs_class = 'with-content-tabs';

				// override tabs classes
				$tabs_classes = ' class="'.$tabs_class.'"';

				// prefix with space
				$tabs_class = ' '.$tabs_class;

			}

		}

	}

	?>



	<div id="main_wrapper" class="clearfix<?php echo $tabs_class; ?>">



	<?php

	// did we get tabs?
	if ( $tabs_class != '' ) {

		// did we get either type of workflow content?
		if ( $literal != '' OR $original != '' ) {

		?>
		<ul id="content-tabs">
			<li id="content_header" class="default-content-tab"><h2><a href="#content"><?php
				echo apply_filters(
					'commentpress_content_tab_content',
					__( 'Content', 'commentpress-core' )
				);
			?></a></h2></li>
			<?php if ( $literal != '' ) { ?>
			<li id="literal_header"><h2><a href="#literal"><?php
				echo apply_filters(
					'commentpress_content_tab_literal',
					__( 'Literal', 'commentpress-core' )
				);
			?></a></h2></li>
			<?php } ?>
			<?php if ( $original != '' ) { ?>
			<li id="original_header"><h2><a href="#original"><?php
				echo apply_filters(
					'commentpress_content_tab_original',
					__( 'Original', 'commentpress-core' )
				);
			?></a></h2></li>
			<?php } ?>
		</ul>
		<?php

		}

	}

	?>



	<div id="page_wrapper" class="page_wrapper<?php echo $tabs_class; ?>">



	<?php

	// show feature image
	commentpress_get_feature_image();

	?>



	<?php

	// first try to locate using WP method
	$cp_page_navigation = apply_filters(
		'cp_template_page_navigation',
		locate_template( 'assets/templates/page_navigation.php' )
	);

	// do we have a featured image?
	if ( ! commentpress_has_feature_image() ) {

		// load it if we find it
		if ( $cp_page_navigation != '' ) load_template( $cp_page_navigation, false );

	}

	?>



	<div id="content" class="content workflow-wrapper">



	<div class="post<?php echo commentpress_get_post_css_override( get_the_ID() ); ?>" id="post-<?php the_ID(); ?>">



		<?php

		// do we have a featured image?
		if ( ! commentpress_has_feature_image() ) {

			// default to hidden
			$cp_title_visibility = ' style="display: none;"';

			// override if we've elected to show the title...
			if ( commentpress_get_post_title_visibility( get_the_ID() ) ) {
				$cp_title_visibility = '';
			}

			?>
			<h2 class="post_title"<?php echo $cp_title_visibility; ?>><a href="<?php the_permalink() ?>"><?php the_title(); ?></a></h2>



			<?php

			// default to hidden
			$cp_meta_visibility = ' style="display: none;"';

			// overrideif we've elected to show the meta...
			if ( commentpress_get_post_meta_visibility( get_the_ID() ) ) {
				$cp_meta_visibility = '';
			}

			?>
			<div class="search_meta"<?php echo $cp_meta_visibility; ?>>
				<?php commentpress_echo_post_meta(); ?>
			</div>

			<?php

		}

		?>



		<?php global $more; $more = true; the_content(''); ?>



		<?php

		// NOTE: Comment permalinks are filtered if the comment is not on the first page
		// in a multipage post... see: commentpress_multipage_comment_link in functions.php
		echo commentpress_multipager();

		?>



		<?php

		// test for "Post Tags and Categories for Pages" plugin
		if ( class_exists( 'PTCFP' ) ) {

		?>
		<p class="search_meta"><?php the_tags( __( 'Tags: ', 'commentpress-core' ), ', ', '<br />' ); ?> <?php _e( 'Posted in', 'commentpress-core' ); ?> <?php the_category( ', ' ) ?></p>
		<?php

		}

		?>



		<?php

		// if we have the plugin enabled...
		if ( is_object( $commentpress_core ) ) {

			// get page num
			$num = $commentpress_core->nav->get_page_number( get_the_ID() );

			//print_r( $num ); die();

			// if we get one
			if ( $num ) {

				// make lowercase if Roman
				if ( ! is_numeric( $num ) ) {
					$num = strtolower( $num );
				}

				// wrap number
				$element = '<span class="page_num_bottom">' . $num . '</span>';

				// add page number
				?><div class="running_header_bottom"><?php
					echo sprintf( __( 'Page %s', 'commentpress-core' ), $element );
				?></div><?php

			}

		}

		?>



	</div><!-- /post -->



	</div><!-- /content -->



	<div class="page_nav_lower">
	<?php

	// include page_navigation again
	if ( $cp_page_navigation != '' ) load_template( $cp_page_navigation, false );

	?>
	</div><!-- /page_nav_lower -->



	<?php

	// did we get tabs?
	if ( $tabs_class != '' ) {

		// did we get either type of workflow content?
		if ( $literal != '' OR $original != '' ) {

		// did we get literal?
		if ( $literal != '' ) {

		?>
		<div id="literal" class="workflow-wrapper">

		<div class="post">

		<h2 class="post_title"><?php
			echo apply_filters(
				'commentpress_literal_title',
				__( 'Literal Translation', 'commentpress-core' )
			);
		?></h2>

		<?php echo apply_filters( 'cp_workflow_richtext_content', $literal ); ?>

		</div><!-- /post -->

		</div><!-- /literal -->

		<?php } ?>


		<?php

		// did we get original?
		if ( $original != '' ) {

		?>

		<div id="original" class="workflow-wrapper">

		<div class="post">

		<h2 class="post_title"><?php
			echo apply_filters(
				'commentpress_original_title',
				__( 'Original Text', 'commentpress-core' )
			);
		?></h2>

		<?php echo apply_filters( 'cp_workflow_richtext_content', $original ); ?>

		</div><!-- /post -->

		</div><!-- /original -->

		<?php } ?>



		<?php

		}

	}

	?>



	</div><!-- /page_wrapper -->



	</div><!-- /main_wrapper -->



<?php endwhile; else: ?>



	<div id="main_wrapper" class="clearfix">

	<div id="page_wrapper" class="page_wrapper">

	<div id="content" class="content">

	<div class="post">

		<h2 class="post_title"><?php _e( 'Page Not Found', 'commentpress-core' ); ?></h2>

		<p><?php _e( "Sorry, but you are looking for something that isn't here.", 'commentpress-core' ); ?></p>

		<?php get_search_form(); ?>

	</div><!-- /post -->

	</div><!-- /content -->

	</div><!-- /page_wrapper -->

	</div><!-- /main_wrapper -->



<?php endif; ?>



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>