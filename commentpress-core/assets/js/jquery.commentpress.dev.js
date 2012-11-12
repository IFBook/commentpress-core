/**
 * @projectDescription CommentPress Core jQuery Plugin
 *
 * @version 3.0
 * @author Christian Wach/needle@haystack.co.uk
 *
 */
;



/** 
 * @description: a nifty JS array utility to remove a specified value
 *
 */
Array.prototype.remove_item = function( item ) {

	// loop through the array
	for (var i = 0; i < this.length; i++){
		
		// remove our item
		if (item === this[i]) { this.splice(i, 1); }
		
	}

}




/** 
 * @description: our jQuery plugin
 *
 */
;( function( $ ) {

	// plugin context (external or internal)
	var plugin_context = 'internal';

	/** 
	 * @description: set the context of this plugin
	 * @todo: remove
	 *
	 */	
	$.set_context = function( context ) {
	
		// store global
		plugin_context = context;
	
	};
	
	
	
	// define open accordion parts array
	var open_parts = new Array();
	
	/** 
	 * @description: reset the accordion
	 * @todo: remove
	 *
	 */	
	$.accordion_reset = function() {
	
		// reset open_parts
		open_parts = new Array();
	
	};
	
	
	
	/** 
	 * @description: open a section of the accordion
	 * @todo: remove
	 *
	 */	
	$.accordion_open = function( part ) {
	
		// add part to open array
		open_parts.push( part );
	
	};
	
	
	
	/** 
	 * @description: close a section of the accordion
	 * @todo: remove
	 *
	 */	
	$.accordion_close = function( part ) {
	
		// remove this part from the open array
		open_parts.remove_item( part );
	
	};
	
	
	
	/** 
	 * @description: query the accordion for the open/closed status of a part
	 * @todo: remove
	 *
	 */	
	$.accordion_query = function( part ) {

		// is our part in the open array?
		return $.in_array( part, open_parts )

	};
	
	
	
	// our currently highlighted paragraph
	var highlighted_para = '';
	
	// our widening constant (must match selected_para: top in CSS)
	var selected_para_widen = 7;

	/**
	 * @description: highlight the current paragraph
	 * @todo: 
	 *
	 */	
	$.highlight_para = function( element ) {
	
		// test that we have a proper element
		if ( typeof( element ) != 'object' ) {
		
			// --<
			return;
		
		}

		
		
		// unhighlight
		//$.unhighlight_para();
		

		
		/*
		// only store highlight if in our CommentPress context
		if ( plugin_context == 'internal' ) {
	
			// is our item already highlighted?
			if ( $.accordion_query( element.attr('id') ) ) {
			
				// clear current element in global
				highlighted_para = '';
				
				// close this part of the accordion
				$.accordion_close( element.attr('id') );
			
				// --<
				return;
			
			} else {
			
				// open this part of the accordion
				$.accordion_open( element.attr('id') );
			
				// store current element in global
				highlighted_para = element;
				
			}
			
		}
		
		
		
		// widen
		//var width = parseInt( element.width() );
		//element.css( 'width', (width + selected_para_widen) + 'px' );

		// get padding
		var padding_top = parseInt( element.css( 'padding-top' ).split('px')[0] );
		var padding_right = parseInt( element.css( 'padding-right' ).split('px')[0] );
		var padding_bottom = parseInt( element.css( 'padding-bottom' ).split('px')[0] );
		var padding_left = parseInt( element.css( 'padding-left' ).split('px')[0] );

		element.css( 'padding-top', padding_top + selected_para_widen );
		element.css( 'padding-right', padding_right + selected_para_widen );
		element.css( 'padding-bottom', padding_bottom + selected_para_widen );
		element.css( 'padding-left', padding_left + selected_para_widen );
		*/
		
		// amend p tag css
		element.addClass( 'selected_para' );

	}
	
	

	/** 
	 * @description: unhighlight all text
	 * @todo: 
	 *
	 */	
	$.unhighlight_para = function() {
	
		// if we have a highlight
		//if ( highlighted_para != '' ) {
			
			var highlighted_paras = $('.textblock');
			
			// remove class from all
			highlighted_paras.removeClass( 'selected_para' );
			
			/*
			// get padding
			var padding_top = parseInt( highlighted_paras.css( 'padding-top' ).split('px')[0] );
			var padding_right = parseInt( highlighted_paras.css( 'padding-right' ).split('px')[0] );
			var padding_bottom = parseInt( highlighted_paras.css( 'padding-bottom' ).split('px')[0] );
			var padding_left = parseInt( highlighted_paras.css( 'padding-left' ).split('px')[0] );
			
			// remove visible highlight
			highlighted_paras.css( 'padding-top', padding_top - selected_para_widen );
			highlighted_paras.css( 'padding-right', padding_right - selected_para_widen );
			highlighted_paras.css( 'padding-bottom', padding_bottom - selected_para_widen );
			highlighted_paras.css( 'padding-left', padding_left - selected_para_widen );
			
			// narrow
			//var width = parseInt( highlighted_para.width() );
			//highlighted_para.css( 'width', (width - selected_para_widen) + 'px' );
			*/
			
		//}
		
		/*
		// only clear highlight if in our CommentPress context
		if ( plugin_context == 'internal' ) {
	
			// clear global
			highlighted_para = '';
		
		}
		*/
		
	}

	

	/** 
	 * @description: get the element which is currently highlighted
	 * @todo: 
	 *
	 */	
	$.get_highlighted_para = function() {
	
		// --<
		return highlighted_para;
		
	}

	

	/** 
	 * @description: test if the element is currently highlighted
	 * @todo: 
	 *
	 */	
	$.is_highlighted_para = function( element ) {
	
		// only return highlight status if in our CommentPress context
		if ( plugin_context != 'internal' ) {
		
			// --<
			return false;
		
		}
	
		// test that we have a proper element
		if ( typeof( element ) != 'object' ) {
		
			// --<
			return false;
		
		}
	
		// is our item already highlighted?
		if ( $.accordion_query( element.attr('id') ) ) {
		
			// --<
			return true;
		
		} else {
		
			// --<
			return false;
		
		}
		
	}

	

	/** 
	 * @description: test if the element is currently highlighted
	 * @todo: 
	 *
	 */	
	$.is_highlighted = function( element ) {
	
		// only return highlight status if in our CommentPress context
		if ( plugin_context != 'internal' ) {
		
			// --<
			return false;
		
		}
	
		// test that we have a proper element
		if ( typeof( element ) != 'object' ) {
		
			// --<
			return false;
		
		}
	
		// is our item already highlighted?
		if ( element.hasClass('selected_para') ) {
		
			// --<
			return true;
		
		} else {
		
			// --<
			return false;
		
		}
		
	}

	

	/**
	 * @description: highlight the current text
	 * @todo: 
	 *
	 */	
	$.highlight_text = function( element ) {
	
		// get padding
		var padding_top = parseInt( element.css( 'padding-top' ).split('px')[0] );
		var padding_right = parseInt( element.css( 'padding-right' ).split('px')[0] );
		var padding_bottom = parseInt( element.css( 'padding-bottom' ).split('px')[0] );
		var padding_left = parseInt( element.css( 'padding-left' ).split('px')[0] );

		/*
		console.log( 'padding_bottom: ' + padding_bottom );
		console.log( 'padding_top: ' + padding_top );
		console.log( 'padding_right: ' + padding_right );
		console.log( 'padding_left: ' + padding_left );
		*/
		
		// get margin
		var margin_top = parseInt( element.css( 'margin-top' ).split('px')[0] );
		var margin_right = parseInt( element.css( 'margin-right' ).split('px')[0] );
		var margin_bottom = parseInt( element.css( 'margin-bottom' ).split('px')[0] );
		var margin_left = parseInt( element.css( 'margin-left' ).split('px')[0] );
		
		/*
		console.log( 'margin_top: ' + margin_top );
		console.log( 'margin_bottom: ' + margin_bottom );
		console.log( 'margin_right: ' + margin_right );
		console.log( 'margin_left: ' + margin_left );
		*/
		
		// gap between paragraphs
		var gap = margin_top + margin_bottom + padding_top + padding_bottom;
		//console.log( 'element gap: ' + gap );

		// so, halve it
		var half_gap = parseInt( gap / 2 );

		// get params
		var top = parseInt( element.position().top ) + margin_top + padding_top - selected_para_widen;
		var left = parseInt( element.position().left ) + margin_left + padding_left - selected_para_widen;
		var width = parseInt( element.width() ) + ( selected_para_widen * 2 );
		var height = parseInt( element.height() ) + ( selected_para_widen * 2 );

		/*
		console.log( 'element top: ' + top );
		console.log( 'element left: ' + left );
		console.log( 'element width: ' + width );
		console.log( 'element height: ' + height );
		
		// init adjustor value
		var adjust = 0;
		
		// if we have no padding and margin, adjust by 10px
		if ( margin_left + margin_right + padding_left + padding_right == 0 ) {
		
			// set adjust to our desired padding in px
			adjust = 10;
		
		}
		
		// set dimensions for highlighted element
		top = top + padding_top + margin_top - half_gap;
		height = height + gap - 40;
		left = left + padding_left + margin_left - adjust;
		width = width + padding_left + padding_right + adjust + adjust - 18; // + margin_left + margin_right;
		*/

		/*
		console.log( 'final top: ' + top );
		console.log( 'final left: ' + left );
		console.log( 'final width: ' + width );
		console.log( 'final height: ' + height );
		*/
		
		// unhighlight_text();		
	
		// create a highlighted element
		var highlite = $.create(
	
			'div', 
	
			{
			'id':'selected_text', 
			'class':'selected_text',
			'style':'top: ' + top + 'px; left: ' + left + 'px; width: ' + width + 'px; height: ' + height + 'px;'
			//'style':'top: ' + (top + 10) + 'px; left: ' + (left - 5) + 'px; width: ' + width + 'px; height: ' + (height - 20) + 'px;'
			}, ''
	
		);
		
		// show it
		$('#content').append(highlite);
	
	}
	
	

	/** 
	 * @description: unhighlight all text
	 * @todo: 
	 *
	 */	
	$.unhighlight_text = function() {
	
		// remove visible highlight
		$('.selected_text').remove();
		
	}

	

	/** 
	 * @description: scroll to page title
	 * @todo: implement
	 *
	 */	
	$.scroll_to_title = function() {
	
	};
	
	
	
	/** 
	 * @description: set height of sidebar minimiser (scrolling element) so that the column fills the viewport
	 * @todo: 
	 *
	 */
	$.set_sidebar_height = function() {
		
		var sidebar = $('#sidebar');
		var sidebar_inner = $('#sidebar_inner');
		var sidebar_container = $('#toc_sidebar');
		var header = $('#' + $.get_sidebar_name() + '_sidebar .sidebar_header');
		var minimiser = $.get_sidebar_pane();
	
		// get data on sidebar element
		//var s_top = $.css_to_num( $.px_to_num( sidebar.css('top') ) );
		var s_top = sidebar.position().top;
		//console.log( 's_top: ' + s_top );
		var sidebar_inside_h = $.get_element_adjust( sidebar );
		var sidebar_inner_inside_h = $.get_element_adjust( sidebar_inner );
		var sidebar_diff = s_top + sidebar_inside_h + sidebar_inner_inside_h;
		//console.log( 'sidebar_diff: ' + sidebar_diff );

		// get data on sidebar_container element
		var sc_top = sidebar_container.position().top;
		//console.log( 'sc_top: ' + sc_top );
		var sc_inside_h = $.get_element_adjust( sidebar_container );
		//console.log( 'sc_inside_h: ' + sc_inside_h );
		var sc_diff = sc_top + sc_inside_h;
		//console.log( 'sc_diff: ' + sc_diff );
		
		// init header diff
		var header_diff = 0;
		// if internal header element is displayed
		if ( header.css('display') != 'none' ) {
			// get data on header element
			header_diff = header.height() + $.get_element_adjust( header );
		}
		//console.log( 'header_diff: ' + header_diff );

		// get data on minimiser element
		var minimiser_diff = $.get_element_adjust( minimiser );
		//console.log( 'minimiser_diff: ' + minimiser_diff );
		
		// get bottom margin of main column so sidebar lines up
		// NOTE: this is NOT why they don't line up - it just so happens that the values match
		// It seems the clearfix class adds the margin. Sigh.
		if ( cp_is_signup_page == '1' ) {
			var bottom_margin = $.css_to_num( $.px_to_num( $('#content').css( 'margin-bottom' ) ) );
		} else {
			var bottom_margin = $.css_to_num( $.px_to_num( $('#page_wrapper').css( 'margin-bottom' ) ) );
		}
		//console.log( 'bottom_margin: ' + bottom_margin );
		
		// get viewport data
		var viewport_height = $(window).height();
		var viewport_scrolltop = $(window).scrollTop();
		var viewport = viewport_height + viewport_scrolltop;
		//console.log( 'viewport: ' + viewport );
		
		// calculate the necessary height to reach the bottom of the viewport
		var to_bottom = viewport - ( sidebar_diff + sc_diff + header_diff + minimiser_diff + bottom_margin );
		//console.log( 'to_bottom: ' + to_bottom );
		
		$('#sidebar div.sidebar_contents_wrapper').css( 'height', to_bottom + 'px' );
		
		// --<
		return to_bottom;
		
	}
	
	
	
	
	
	
	/** 
	 * @description: get height data on element
	 * @todo: 
	 *
	 */
	$.get_element_adjust = function( element ) {
	
		// get border
		var w_bt = $.css_to_num( $.px_to_num( element.css( 'borderTopWidth' ) ) );
		var w_bb = $.css_to_num( $.px_to_num( element.css( 'borderBottomWidth' ) ) );

		// get padding
		var w_pad_t = $.css_to_num( $.px_to_num( element.css( 'padding-top' ) ) );
		var w_pad_b = $.css_to_num( $.px_to_num( element.css( 'padding-bottom' ) ) );

		// get margin
		var w_mar_t = $.css_to_num( $.px_to_num( element.css( 'margin-top' ) ) );
		var w_mar_b = $.css_to_num( $.px_to_num( element.css( 'margin-bottom' ) ) );

		// add 'em up
		var element_adjust = w_bt + w_bb + w_pad_t + w_pad_b + w_mar_t + w_mar_b;

		//console.log( 'element_adjust: ' + element_adjust );

		// --<
		return element_adjust;
		
	}
	
	
	
	
	
		
	/** 
	 * @description: get visible sidebar minimiser
	 * @todo: 
	 *
	 */
	$.get_sidebar_pane = function() {
	
		var name = $.get_sidebar_name();
	
		// --<
		return $('#' + name + '_sidebar .sidebar_minimiser');
		
	}
	
	
	
	
	
		
	/** 
	 * @description: get visible sidebar
	 * @todo: 
	 *
	 */
	$.get_sidebar_name = function() {
	
		// init
		var name = 'toc';
		
		// if toc, must be toc
		//if ( cp_default_sidebar == 'toc' ) { name = 'toc'; }
		
		// if comments
		if ( cp_default_sidebar == 'comments' ) {
			name = 'comments';
			if ( cp_toc_on_top == 'y' ) {
				//console.log( 'toc on comments_sidebar' );
				name = 'toc';
			}
		}
	
		// if activity
		if ( cp_default_sidebar == 'activity' ) {
			name = 'activity';
			if ( cp_toc_on_top == 'y' ) {
				//console.log( 'toc on activity_sidebar' );
				name = 'toc';
			}
		}
		
		// --<
		return name;
		
	}
	
	
	
	
	
		
	/** 
	 * @description: compare strings and returns a measure of similarity (replicates PHP's similar_text)
	 * @todo: 
	 * 
	 *
	 */	
	$.similar_string = function( str1, str2 ) {
	
		// use levenshtein
		var distance = $.cp_levenshtein( str1, str2 );
		
		// get percent
		var percent = ( 1 - distance / Math.max( str1.length, str2.length ) ) * 100;
		
		// debug
		//console.log( 'TESTING: ' + str1 + ' AND ' + str2 + ' PERCENT: ' + percent );



		// --<
		return percent;
		
	}
	
	
	
	/** 
	 * @description: Javascript implementation of PHP levenshtein
	 * @todo: 
	 * 
	 *
	 */	
	$.cp_levenshtein = function(a, b) {
	
		// Calculate Levenshtein distance between two strings
		// 
		// +    discuss at: http://kevin.vanzonneveld.net/techblog/article/javascript_equivalent_for_phps_levenshtein/
		// +       version: 903.421
		// +      original by: Carlos R. L. Rodrigues (http://www.jsfromhell.com)
		// +      bugfixed by: Onno Marsman
		// +       revised by: Andrea Giammarchi (http://webreflection.blogspot.com)
		// + reimplemented by: Brett Zamir
		// *        example 1: levenshtein('Kevin van Zonneveld', 'Kevin van Sommeveld');
		// *        returns 1: 3
		
		var split=false, min=Math.min, len1=0, len2=0, I=0, i=0, d=[], c='', j=0, J=0;
		try{
			('0')[0];
		} catch(i){
			split=true;
		}
		if (a == b) {
			return 0;
		}
		if (!a.length || !b.length) {
			return b.length || a.length;
		}
		if (split){
			a = a.split('');b = b.split('');
		}
		len1 = a.length + 1;
		len2 = b.length + 1;
		d = [[0]];
		while (++i < len2) {
			d[0][i] = i;
		}
		i = 0;
		while (++i < len1) {
			J = j = 0;
			c = a[I];
			d[i] = [i];
			while (++j < len2) {
				d[i][j] = min(d[I][j] + 1, d[i][J] + 1, d[I][J] + (c != b[J]));
				++J;
			}
			++I;
		}
		
		// --<
		return d[len1 - 1][len2 - 1];
		
	}



	/** 
	 * @description: move comment area to an accordion section
	 * @todo: 
	 *
	 */	
	$.move_comment_form = function( text_signature ) {
	
		// move the respond div
		$('#respond').appendTo('#comment-group-' + text_signature);

		// make it visible
		$('#respond').css('display', 'block');

		// set the text signature value
		$('#text_signature').val( text_signature );

		// clear the reply to value
		$('#comment_parent').val( '' );
		
		// hide cancel reply link
		$('#cancel-comment-reply-link').css('display','none');
		
	}
	
	
	
	/** 
	 * @description: utility replacement for PHP's in_array
	 * @todo: 
	 *
	 */	
	$.in_array = function( needle, haystack, argStrict ) {
	
		// http://kevin.vanzonneveld.net
		// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// *     example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']);
		// *     returns 1: true
	
		var found = false, key, strict = !!argStrict;
		
		for( key in haystack ) {
			if ((strict && haystack[key] === needle) || (!strict && haystack[key] == needle)) {
				found = true;
				break;
			}
		}
		
		return found;
		
	}
	
	
	
	/** 
	 * @description: utility replacement for PHP's is_object
	 * @todo: 
	 *
	 */	
	$.is_object = function ( mixed_var ) {
	
		// distiguish between arrays and objects
		if( mixed_var instanceof Array ) {
		
			// is an array
			return false;
			
		} else {
		
			// not null and is object
			return ( mixed_var !== null ) && ( typeof( mixed_var ) == 'object' );
		}
		
	}



	/** 
	 * @description: utility to strip 'px' off css values
	 * @todo: 
	 *
	 */	
	$.px_to_num = function( pix ) {
	
		// --<
		return parseInt( pix.substring( 0, (pix.length - 2) ) );
	
	};
	
	
	
	/** 
	 * @description: utility to return zero when css values may be NaN in IE
	 * @todo: 
	 *
	 */	
	$.css_to_num = function( strNum ) {
	
		// http://mattstark.blogspot.com/2009/05/javascript-jquery-plugin-to-fade.html
		if (strNum && strNum != "") {
		
			var i = parseFloat(strNum);
			if (i.toString() == "NaN") {

				// --<
				return 0;

			} else {

				// --<
				return i;

			}
			
		}
		
		// --<
		return 0;
	
	};
	
	
	
	/** 
	 * @description: a test
	 * @todo: remove
	 *
	 */	
	$.frivolous = function( message ) {
	
		// do a simple alert
		alert( message );
	
	};
	
	
	
})( jQuery );