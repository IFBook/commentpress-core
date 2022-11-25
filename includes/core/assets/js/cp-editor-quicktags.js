/**
 * Quicktags script for WordPress 3.3+.
 *
 * @see https://core.trac.wordpress.org/ticket/1345
 *
 * @since 3.0
 *
 * @package CommentPress_Core
 */

// Add Page Break quicktag.
QTags.addButton( 'wp_page', 'p-break', "\n<!--nextpage-->\n" );

// Add Comment Block quicktag.
QTags.addButton( 'commentblock', 'c-block', "\n<!--commentblock-->\n" );
