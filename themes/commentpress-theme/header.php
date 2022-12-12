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

		<?php commentpress_header_body_template(); ?>

		<div id="container">
