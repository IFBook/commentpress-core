/* 
================================================================================
Show/Hide All Comments
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
*/

jQuery(document).ready( function($) {

	// hide all comment content
	$('ul.all_comments_listing div.item_body').hide();
	
	// set pointer 
	$("ul.all_comments_listing li > h3").css( 'cursor', 'pointer' );

	// all comment page headings toggle slide
	$("ul.all_comments_listing li > h3").click( function() {
	
		// toggle next item_body
		$(this).next('div.item_body').slideToggle( 'slow' );
		
	});
	
});
