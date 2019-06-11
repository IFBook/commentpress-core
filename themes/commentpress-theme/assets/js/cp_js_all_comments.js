/*
================================================================================
Show/Hide All Comments
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES

--------------------------------------------------------------------------------
*/

jQuery(document).ready( function($) {

	// Hide all comment content.
	$('ul.all_comments_listing div.item_body').hide();

	// Set pointer.
	$("ul.all_comments_listing li > h3").css( 'cursor', 'pointer' );

	// All comment page headings toggle slide.
	$("ul.all_comments_listing li > h3").click( function() {

		// Toggle next item_body.
		$(this).next('div.item_body').slideToggle( 'slow' );

	});

});
