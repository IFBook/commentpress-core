/**
 * @projectDescription CommentPress Core jQuery Plugin
 *
 * @version 3.0
 * @author Christian Wach/needle@haystack.co.uk
 *
 */
;



/** 
 * @description: our jQuery plugin
 *
 */
;( function( $ ) {

	// our currently highlighted paragraph
	var highlighted_para = '';
	
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
		
		// amend p tag css
		element.addClass( 'selected_para' );

	}
	
	

	/** 
	 * @description: unhighlight all text
	 * @todo: 
	 *
	 */	
	$.unhighlight_para = function() {
	
		var highlighted_paras = $('.textblock');
		
		// remove class from all
		highlighted_paras.removeClass( 'selected_para' );
		
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
	$.is_highlighted = function( element ) {
	
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
	 * @description: set height of sidebar minimiser (scrolling element) so that the column fills the viewport
	 * @todo: in jQuery 1.9, we get a 143px error, related to sidebar.position().top
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
		var s_top = sidebar.offset().top;
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
	 * @description: get currently highlighted menu item ID
	 */
	$.get_current_menu_item_id = function() {
		
		// declare vars
		var current_menu_item = 0, 
			current_menu_obj, current_item_id, 
			current_item_classes, current_item_class;
			
		// get highlighted menu item object
		current_menu_obj = $('.current_page_item');
		//console.log( 'current_menu_item:' );
		//console.log( current_menu_item );
		
		// did we get one?
		if ( current_menu_obj.length > 0 ) {
	
			// get ID, if present
			current_item_id = current_menu_obj.prop('id');
			//console.log( 'current_item_id:' );
			//console.log( current_item_id );
			
			// if we do have an ID...
			if ( current_item_id.length > 0 ) {
		
				// it's a WP custom menu
				current_menu_item = current_item_id.split('-')[2];
			
			} else {
			
				// it's a WP page menu
				current_item_class = current_menu_obj.prop('class');
				
				// get classes
				current_item_classes = current_item_class.split(' ');
				
				// loop to find the one we want
				for (var i = 0, item; item = current_item_classes[i++];) {
					if ( item.match( 'page-item-' ) ) {
						current_menu_item = item.split('-')[2];
						break;
					}
				}
			
			}
		
		}
	
		//console.log( 'cpajax_current_menu_item: ' + cpajax_current_menu_item );
		
		// --<
		return current_menu_item;
		
	}



	/** 
	 * @description: utility replacement for PHP's in_array
	 */	
	$.in_array = function( needle, haystack, argStrict ) {
	
		// http://kevin.vanzonneveld.net
		// +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// *     example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']);
		// *     returns 1: true
	
		var found = false, key, strict = !!argStrict;
		
		for( key in haystack ) {
			if ( (strict && haystack[key] === needle) || (!strict && haystack[key] == needle) ) {
				found = true;
				break;
			}
		}
		
		return found;
		
	}
	
	
	
	/** 
	 * @description: a nifty JS array utility to remove a specified value
	 */
	$.remove_from_array = function( item, sourceArray ) {

		// loop through the array
		for ( var i = 0; i < sourceArray.length; i++ ) {
		
			// remove our item
			if ( item === sourceArray[i] ) {
				
				// splice it at that point
				sourceArray.splice(i, 1);
				
				// kick out
				break;
				
			}
		
		}
		
		// --<
		return sourceArray;

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
	 * @description: test if a function exists without throwing a Reference Error
	 */
	$.is_function_defined = function ( function_name ) {
	
		// use eval
		if ( eval( 'typeof(' + function_name + ') == typeof(Function)' ) ) {
		
			// --<
			return true;
			
		}
		
		// --<
		return false;
		
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