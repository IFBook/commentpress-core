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
<meta http-equiv="content-type" content="<?php bloginfo('html_type') ?>; charset=<?php bloginfo('charset') ?>" />
<meta name="description" content="<?php echo commentpress_header_meta_description(); ?>" />
<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
<?php if(is_search()) { ?><meta name="robots" content="noindex, nofollow" /><?php } ?>

<!-- pingbacks -->
<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>" />

<!--[if IE]>
<script type='text/javascript'>
/* <![CDATA[ */
var cp_msie = 1;
/* ]]> */
</script>
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

<?php

// Add custom css file for user-defined theme mods in child theme directory (legacy).
if( file_exists( get_stylesheet_directory() . '/custom.css' )) {

?>
<!-- legacy custom css -->
<link rel="stylesheet" type="text/css" href="<?php echo get_stylesheet_directory_uri(); ?>/custom.css" media="screen" />
<?php

} ?>

<!-- IE stylesheets so we can override anything -->
<!--[if gte IE 7]>
<link rel="stylesheet" type="text/css" href="<?php echo get_template_directory_uri(); ?>/assets/css/ie7.css" media="screen" />
<![endif]-->

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



<div id="container">


