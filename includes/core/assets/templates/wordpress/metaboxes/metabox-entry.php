<?php
/**
 * "CommentPress Settings" Metabox template.
 *
 * Handles markup for the "CommentPress Settings" Metabox on "Edit Entry" screens.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

?>
<!-- <?php echo $this->metabox_path; ?>metabox-entry.php -->
<?php wp_nonce_field( $this->nonce_value, $this->nonce_name ); ?>

<?php

/**
 * Fires at the top of the "CommentPress Settings" metabox.
 *
 * @since 4.0
 *
 * @param WP_Post $post The WordPress Post object.
 */
do_action( 'commentpress/core/entry/metabox/before', $post );

?>

<?php

/**
 * Fires at the bottom of the "CommentPress Settings" metabox.
 *
 * @since 4.0
 *
 * @param WP_Post $post The WordPress Post object.
 */
do_action( 'commentpress/core/entry/metabox/after', $post );

?>
