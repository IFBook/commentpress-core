<!-- searchform.php -->
<?php

global $blog_id;

// if this is the main BuddyPress-enabled blog...
if ( 
	
	function_exists( 'bp_search_form_type_select' ) AND 
	BP_ROOT_BLOG == $blog_id
	
) {



// -----------------------------------------------------------------------------
// BuddyPress
// -----------------------------------------------------------------------------

?><form action="<?php echo bp_search_form_action() ?>" method="post" id="search-form">
<label for="search-terms" class="accessibly-hidden"><?php _e( 'Search for:', 'commentpress-core' ); ?></label>
<input type="text" id="search-terms" name="search-terms" value="<?php echo isset( $_REQUEST['s'] ) ? esc_attr( $_REQUEST['s'] ) : ''; ?>" />

<?php echo bp_search_form_type_select() ?>

<input type="submit" name="search-submit" id="search-submit" value="<?php _e( 'Search', 'commentpress-core' ) ?>" />

<?php wp_nonce_field( 'bp_search_form' ) ?>

</form><!-- #search-form -->

<?php



} else {



// -----------------------------------------------------------------------------
// WordPress
// -----------------------------------------------------------------------------

?><form method="get" id="searchform" action="<?php echo site_url(); ?>/">

<label for="s"><?php _e('Search for:', 'commentpress-core'); ?></label>

<input type="text" value="<?php the_search_query(); ?>" name="s" id="s" />

<input type="submit" id="searchsubmit" value="<?php _e( 'Search', 'commentpress-core' ); ?>" />

</form>

<?php

}

?>