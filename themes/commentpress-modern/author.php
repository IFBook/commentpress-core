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



get_header(); ?>



<!-- author.php -->

<div id="wrapper">



<div id="main_wrapper" class="clearfix">



<div id="page_wrapper">



<div id="content" class="clearfix">

<div class="post">

<?php if ( $my_author->display_name != '' ) { ?>
<h2 class="post_title"><?php echo $my_author->display_name; ?></h2>
<?php } else { ?>
<h2 class="post_title"><?php echo $my_author->nickname; ?></h2>
<?php } ?>

<?php 

// get avatar
$_avatar = get_avatar( $my_author->user_email, $size='200' ); 

// did we get one?
if ( $_avatar != '' ) {

	// show it
	echo '<p>'.$_avatar.'</p>';

}

?>



<?php if ( $my_author->description != '' ) { ?>
<p><?php echo nl2br( $my_author->description ); ?></p>
<?php } ?>



<?php 

/*

?>

<dl>

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

<?php

*/

?>



<!-- The Loop -->
<?php if ( have_posts() ) {

	// init name
	$_author_name = $my_author->nickname;
	
	// override if there's a display name
	if ( $my_author->display_name != '' ) { 
		$_author_name = $my_author->display_name;
	}
	
	?>
	<h3 class="author_pages_heading"><?php 
		
		// show title
		echo apply_filters( 'cp_author_page_posts_list_title', __( 'Posts written by', 'commentpress-core' ) ); 
		
	?> <?php echo $_author_name; ?></h3>
	
	

	<ul class="author_pages">
	
	<?php while ( have_posts() ) : the_post(); ?>

		<li>
		<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?> (<?php the_time('F jS, Y'); ?>)</a>
		</li>

	<?php endwhile; ?>

	</ul>

<?php } ?>



<?php

// define our args
$page_args = array(
	'post_status' => 'publish',
	'post_type' => 'page',
	'author' => $my_author->ID,
	'posts_per_page' => 0,
	'no_found_rows' => true,
);

// the pages query
$_pages = new WP_Query( $page_args );

// proceed only if published pages exist
if ( $_pages->have_posts() ) {

	//print_r( $pages ); die();
	
	// init name
	$_author_name = $my_author->nickname;
	
	// override if there's a display name
	if ( $my_author->display_name != '' ) { 
		$_author_name = $my_author->display_name;
	}
	
	?>
	<h3 class="author_pages_heading"><?php 
		
		// show title
		echo apply_filters( 'cp_author_page_pages_list_title', __( 'Pages written by', 'commentpress-core' ) ); 
		
	?> <?php echo $_author_name; ?></h3>
	
	
	<ul class="author_pages">
	<?php
	
	// let's roll
	while ( $_pages->have_posts() ) : $_pages->the_post(); ?>
	
		<li>
		<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a>
		</li>

	<?php

	endwhile;

	?>
	</ul>
	<?php

}

?>



</div><!-- /post -->

</div><!-- /content -->



</div><!-- /page_wrapper -->



</div><!-- /main_wrapper -->



</div><!-- /wrapper -->



<?php get_sidebar(); ?>



<?php get_footer(); ?>