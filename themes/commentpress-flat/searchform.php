<?php
/**
 * Search Form Template.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

global $blog_id;

// If this is the main BuddyPress-enabled Blog.
if ( function_exists( 'bp_search_form_type_select' ) && bp_is_root_blog() ) :

	// -----------------------------------------------------------------------------
	// BuddyPress.
	// -----------------------------------------------------------------------------

	?>
	<!-- searchform.php -->
	<form action="<?php echo bp_search_form_action(); ?>" method="post" id="search-form">

		<label for="search-terms" class="accessibly-hidden"><?php esc_html_e( 'Search for:', 'commentpress-core' ); ?></label>
		<input type="text" id="search-terms" name="search-terms" value="<?php echo isset( $_REQUEST['s'] ) ? esc_attr( sanitize_text_field( wp_unslash( $_REQUEST['s'] ) ) ) : ''; ?>" />

		<?php echo bp_search_form_type_select(); ?>

		<input type="submit" name="search-submit" id="search-submit" value="<?php esc_attr_e( 'Search', 'commentpress-core' ); ?>" />

		<?php wp_nonce_field( 'bp_search_form' ); ?>

	</form><!-- #search-form -->

<?php else : ?>

	<?php

	// -----------------------------------------------------------------------------
	// WordPress.
	// -----------------------------------------------------------------------------

	?>
	<!-- searchform.php -->
	<form method="get" id="searchform" action="<?php echo site_url(); ?>/">

		<label for="s"><?php esc_html_e( 'Search for:', 'commentpress-core' ); ?></label>

		<input type="text" value="<?php the_search_query(); ?>" name="s" id="s" />

		<input type="submit" id="searchsubmit" value="<?php esc_attr_e( 'Search', 'commentpress-core' ); ?>" />

	</form>

<?php endif; ?>
