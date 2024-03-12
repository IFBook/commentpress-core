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
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>

	<head profile="http://gmpg.org/xfn/11">

		<!-- meta -->
		<meta http-equiv="content-type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
		<meta name="description" content="<?php echo esc_url( commentpress_header_meta_description() ); ?>" />
		<?php if ( is_search() ) : ?>
			<meta name="robots" content="noindex, nofollow" />
		<?php endif; ?>

		<!-- pingbacks -->
		<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

		<!-- wp_head -->
		<?php wp_head(); ?>

	</head>

	<body<?php commentpress_body_id(); ?> <?php body_class( commentpress_get_body_classes( true ) ); ?>>

		<?php if ( function_exists( 'wp_body_open' ) ) : ?>
			<?php wp_body_open(); ?>
		<?php else : ?>
			<?php do_action( 'wp_body_open' ); ?>
		<?php endif; ?>

		<?php commentpress_header_body_template(); ?>

		<div id="container">
