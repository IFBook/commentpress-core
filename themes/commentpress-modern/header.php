<!DOCTYPE html>
<!--[if IE 7]>
<html class="ie ie7" <?php language_attributes(); ?>>
<![endif]-->
<!--[if IE 8]>
<html class="ie ie8" <?php language_attributes(); ?>>
<![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->
<head>

<?php
if ( ! function_exists( '_wp_render_title_tag' ) ) :
	function commentpress_theme_slug_render_title() {
	?>
	<!-- title -->
	<title><?php wp_title( '|', true, 'right' ); bloginfo( 'name' ); commentpress_site_title( '|' ); ?></title>
	<?php
	}
	add_action( 'wp_head', 'commentpress_theme_slug_render_title' );
endif;
?>

<!-- meta -->
<meta charset="<?php bloginfo( 'charset' ); ?>" />
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0, user-scalable=no" />
<meta name="description" content="<?php echo commentpress_header_meta_description(); ?>" />
<?php if(is_search()) { ?><meta name="robots" content="noindex, nofollow" /><?php } ?>

<!-- profile -->
<link rel="profile" href="http://gmpg.org/xfn/11" />

<!-- pingbacks -->
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<!--[if IE]>
<script type='text/javascript'>
/* <![CDATA[ */
var cp_msie = 1;
/* ]]> */
</script>
<![endif]-->

<!--[if lt IE 9]>
<script src="<?php echo get_template_directory_uri(); ?>/assets/js/html5.js" type="text/javascript"></script>
<![endif]-->

<!-- wp_head -->
<?php wp_head(); ?>

<?php if ( is_multisite() ) { if ( 'wp-signup.php' == basename($_SERVER['SCRIPT_FILENAME']) ) { ?>
<!-- signup css -->
<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/assets/css/signup.css" media="screen" />
<?php }} ?>
<?php if ( is_multisite() ) { if ( 'wp-activate.php' == basename($_SERVER['SCRIPT_FILENAME']) ) { ?>
<!-- activate css -->
<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/assets/css/activate.css" media="screen" />
<?php }} ?>

</head>



<?php

// Get body ID.
$_body_id = commentpress_get_body_id();

// Get body classes.
$_body_classes = commentpress_get_body_classes( true );

// BODY starts here.
?><body<?php echo $_body_id; ?> <?php body_class( $_body_classes ); ?>>
<?php
if ( function_exists( 'wp_body_open' ) ) {
    wp_body_open();
} else {
    do_action( 'wp_body_open' );
}
?>



<div id="container">



<?php

/**
 * Try to locate template using WP method.
 *
 * @since 3.4
 *
 * @param str The existing path returned by WordPress.
 * @return str The modified path.
 */
$cp_header_body = apply_filters(
	'cp_template_header_body',
	locate_template( 'assets/templates/header_body.php' )
);

// Load it if we find it.
if ( $cp_header_body != '' ) load_template( $cp_header_body );

?>



