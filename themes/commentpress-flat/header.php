<?php
/**
 * WordPress Header Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>

	<head>

		<!-- meta -->
		<meta charset="<?php bloginfo( 'charset' ); ?>" />
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
		<meta name="description" content="<?php echo esc_url( commentpress_header_meta_description() ); ?>" />
		<?php if ( is_search() ) : ?>
			<meta name="robots" content="noindex, nofollow" />
		<?php endif; ?>

		<!-- profile -->
		<link rel="profile" href="https://gmpg.org/xfn/11" />

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

		<div id="container">

			<?php commentpress_header_body_template(); ?>
