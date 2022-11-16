<?php
/**
 * CommentPress Core Comment Walker class.
 *
 * We need this class because the original class did not include the option of
 * using ordered lists <ol> instead of unordered ones <ul>.
 *
 * @see https://github.com/WordPress/WordPress/blob/5828310157f1805a5f0976d76692c7023e8a895d/wp-includes/comment-template.php#L880
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Comment Walker class.
 *
 * @since 3.0
 *
 * @package WordPress
 * @uses Walker
 * @since unknown
 */
class Walker_Comment_Press extends Walker_Comment {

	/**
	 * Oveload the Comment Walker start level method.
	 *
	 * @see Walker_Comment::start_lvl()
	 *
	 * @param string $output Passed by reference. Used to append additional content.
	 * @param int $depth Depth of comment.
	 * @param array $args Uses 'style' argument for type of HTML list.
	 */
	public function start_lvl( &$output, $depth = 0, $args = [] ) {

		// Store depth.
		$GLOBALS['comment_depth'] = $depth + 1;

		// Open children if necessary.
		switch ( $args['style'] ) {

			case 'div':
				break;

			case 'ol':
				echo '<ol class="children">' . "\n";
				break;

			default:
			case 'ul':
				echo '<ul class="children">' . "\n";
				break;

		}

	}

}
