<?php
/**
 * WordPress Header Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--[if IE 7]>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?> class="ie7">
<![endif]-->
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?> class="ie8">
<![endif]-->
<!--[if !(IE 6) | !(IE 7) | !(IE 8)  ]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<!--<![endif]-->

	<head profile="http://gmpg.org/xfn/11">

		<?php if ( ! function_exists( '_wp_render_title_tag' ) ) : ?>
			<?php

			/**
			 * Adds theme support for built-in title tags.
			 *
			 * @since 3.8.3
			 */
			function commentpress_theme_slug_render_title() {
				?>
				<!-- title -->
				<title><?php wp_title( '|', true, 'right' ); ?> <?php bloginfo( 'name' ); ?> <?php commentpress_site_title( '|' ); ?></title>
				<?php
			}

			add_action( 'wp_head', 'commentpress_theme_slug_render_title' );

			?>
		<?php endif; ?>

		<!-- meta -->
		<meta http-equiv="content-type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
		<meta name="description" content="<?php echo commentpress_header_meta_description(); ?>" />
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
		<?php if ( is_search() ) : ?>
			<meta name="robots" content="noindex, nofollow" />
		<?php endif; ?>

		<!-- pingbacks -->
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

		<!--[if IE]>
		<script type='text/javascript'>
		/* <![CDATA[ */
		var cp_msie = 1;
		/* ]]> */
		</script>
		<![endif]-->

		<!-- wp_head -->
		<?php wp_head(); ?>

		<?php if ( is_multisite() ) : ?>
			<?php $current_script = isset( $_SERVER['SCRIPT_FILENAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_FILENAME'] ) ) : ''; ?>
			<?php if ( 'wp-signup.php' == basename( $current_script ) ) : ?>
				<!-- signup css -->
				<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/assets/css/signup.css" media="screen" />
			<?php endif; ?>
			<?php if ( 'wp-activate.php' == basename( $current_script ) ) : ?>
				<!-- activate css -->
				<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/assets/css/activate.css" media="screen" />
			<?php endif; ?>
		<?php endif; ?>

		<?php /* Add legacy custom CSS file for user-defined theme mods in child theme directory. */ ?>
		<?php if ( file_exists( get_stylesheet_directory() . '/custom.css' ) ) : ?>
			<!-- legacy custom css -->
			<link rel="stylesheet" type="text/css" href="<?php echo get_stylesheet_directory_uri(); ?>/custom.css" media="screen" />
		<?php endif; ?>

		<!-- IE stylesheets so we can override anything -->
		<!--[if gte IE 7]>
		<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/assets/css/ie7.css" media="screen" />
		<![endif]-->

	</head>

	<body<?php echo commentpress_get_body_id(); ?> <?php body_class( commentpress_get_body_classes( true ) ); ?>>

		<?php if ( function_exists( 'wp_body_open' ) ) : ?>
			<?php wp_body_open(); ?>
		<?php else : ?>
			<?php do_action( 'wp_body_open' ); ?>
		<?php endif; ?>

		<?php

		/**
		 * Try to locate template using WordPress method.
		 *
		 * @since 3.4
		 *
		 * @param str The existing path returned by WordPress.
		 * @return str The modified path.
		 */
		$cp_header_body = apply_filters( 'cp_template_header_body', locate_template( 'assets/templates/header_body.php' ) );

		// Load it if we find it.
		if ( $cp_header_body != '' ) {
			load_template( $cp_header_body );
		}

		?>

		<div id="container">
