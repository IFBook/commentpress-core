// Updated Quicktags script for WP 3.3+

//console.log(QTags);

// add Page Break quicktag, see: http://core.trac.wordpress.org/ticket/1345
QTags.addButton( 'wp_page', 'p-break', "\n<!--nextpage-->\n" );

// add Comment Block quicktag
QTags.addButton( 'commentblock', 'c-block', "\n<!--commentblock-->\n" );
