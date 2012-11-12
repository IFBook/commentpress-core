// NOTE: in WP 3.3, quicktags calls will look like this:
//QTags.addButton( 'commentblock', 'c-block', '\n<!--commentblock-->\n' );

// add Page Break quicktag, see: http://core.trac.wordpress.org/ticket/1345
edButtons[edButtons.length] =
new edButton('wp_page'
,'p-break'
,'\n<!--nextpage-->\n'
,''
,'-1'
);

// add CommentPress commentblock custom quicktag
edButtons[edButtons.length] =
new edButton('commentblock'
,'c-block'
,'\n<!--commentblock-->\n'
,''
,'-1'
);
