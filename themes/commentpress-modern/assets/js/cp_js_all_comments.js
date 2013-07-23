/* 
================================================================================
CommentPress Modern Show/Hide All Comments
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

--------------------------------------------------------------------------------
*/

jQuery(document).ready( function($) {

	// hide all comment content
	$('ul.all_comments_listing div.item_body').hide();
	
	// get our headers
	var cp_headers = $("ul.all_comments_listing li > h4, ul.all_comments_listing li.author_li > h3");
	
	// set pointer 
	cp_headers.addClass( 'pointer' );

	// all comment page headings toggle slide
	cp_headers.click( function() {
	
		// toggle next item_body
		$(this).next('div.item_body').slideToggle( 'slow' );
		
	});
	
});
