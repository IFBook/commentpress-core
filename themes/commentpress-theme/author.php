<?php
/*
Template Name: Author
*/



// get author info
if ( isset( $_GET['author_name'] ) ) {
	$my_author = get_userdatabylogin( $author_name );
} else {
	$my_author = get_userdata(intval($author));
}



// init url (because it can be 'http://' -> doh!)
$authorURL = '';

// do we have an URL for this user?
if ( $my_author->user_url != '' AND $my_author->user_url != 'http://' ) {
	
	// set url
	$authorURL = $my_author->user_url;

}



get_header(); 

?>



<!-- author.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content" class="clearfix">

<div class="post">

<?php if ( $my_author->display_name != '' ) { ?>
<h2 class="post_title">About <?php echo $my_author->display_name; ?></h2>
<?php } else { ?>
<h2 class="post_title">About <?php echo $my_author->nickname; ?></h2>
<?php } ?>

<p><?php echo get_avatar( $my_author->user_email, $size='128' ); ?></p>


<dl>

<?php 

/*

?>

<dt>Gravatar</dt>
<dd><?php echo get_avatar( $my_author->user_email, $size='128' ); ?></dd>

// get full name
$my_name = commentpress_get_full_name( $my_author->first_name, $my_author->last_name );

if ( $my_name != '' ) { ?>
<dt>Name</dt>
<dd><?php echo $my_name; ?></dd>
<?php } ?>

<?php if ( $my_author->display_name != '' ) { ?>
<dt>Display Name</dt>
<dd><?php echo $my_author->display_name; ?></dd>
<?php } ?>

<?php

*/

if ( $my_author->description != '' ) { ?>
<dt>Profile</dt>
<dd><?php echo nl2br( $my_author->description ); ?></dd>
<?php } ?>

<?php if ( $authorURL != '' ) { ?>
<dt>Website</dt>
<dd><a href="<?php echo $my_author->user_url; ?>"><?php echo $my_author->user_url; ?></a></dd>
<?php } ?>

<?php if ( $my_author->user_email != '' ) { ?>
<dt>Email</dt>
<dd><a href="mailto:<?php echo $my_author->user_email; ?>"><?php echo $my_author->user_email; ?></a></dd>
<?php } ?>

<?php if ( $my_author->yim != '' ) { ?>
<dt>Yahoo IM</dt>
<dd><?php echo $my_author->yim; ?></dd>
<?php } ?>

<?php if ( $my_author->aim != '' ) { ?>
<dt>AIM</dt>
<dd><?php echo $my_author->aim; ?></dd>
<?php } ?>

<?php if ( $my_author->jabber != '' ) { ?>
<dt>Jabber / Google Talk</dt>
<dd><?php echo $my_author->jabber; ?></dd>
<?php } ?>

</dl>



<?php if ( $my_author->display_name != '' ) { ?>
<h3>Posts by <?php echo $my_author->display_name; ?></h3>
<?php } else { ?>
<h3>Posts by <?php echo $my_author->nickname; ?></h3>
<?php } ?>


<ul>

<!-- The Loop -->
<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>

<li>
<a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link: <?php the_title(); ?>"><?php the_title(); ?></a> on <?php the_time('l, F jS, Y'); ?>
</li>

<?php endwhile; else: ?>

<p><?php _e( 'No posts by this author.','commentpress-theme' ); ?></p>

<?php endif; ?>
<!-- End Loop -->

</ul>

</div><!-- /post -->

</div><!-- /content -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>