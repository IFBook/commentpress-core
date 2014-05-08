<?php /*
================================================================================
Class CommentpressCoreParser
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This class is a wrapper for parsing content and the comments associated with
each "block". 

With DOM parsing, this class includes two other classes, which help with some
oddities in DOMDocument. These can be found in `inc/dom-helpers`.

--------------------------------------------------------------------------------
*/






/*
================================================================================
Class Name
================================================================================
*/

class CommentpressCoreParser {






	/*
	============================================================================
	Properties
	============================================================================
	*/
	
	// parent object reference
	public $parent_obj;
	
	// DOM helper
	public $dom;
	
	// XHTML helper
	public $xhtml;
	
	// init text_signatures
	public $text_signatures = array();
	
	// all comments
	public $comments_all = array();
	
	// approved comments
	public $comments_approved = array();
	
	// sorted comments
	public $comments_sorted = array();
	





	/** 
	 * @description: initialises this object
	 * @param object $parent_obj a reference to the parent object
	 * @return object
	 * @todo: 
	 *
	 */
	function __construct( $parent_obj ) {
	
		// store reference to parent
		$this->parent_obj = $parent_obj;
	
		// init
		$this->_init();

		// --<
		return $this;

	}






	/** 
	 * @description: set up all items associated with this object
	 * @todo: 
	 *
	 */
	public function initialise() {
		
	}







	/** 
	 * @description: if needed, destroys all items associated with this object
	 * @todo: 
	 *
	 */
	public function destroy() {
	
	}







//##############################################################################







	/*
	============================================================================
	PUBLIC METHODS
	============================================================================
	*/
	





	/** 
	 * @description: call
	 * @todo:
	 *
	 */
	public function the_content( $content ) {
	
		// reference our post
		global $post;
		


		// retrieve all comments and store...
		// we need this data multiple times and only need to get it once
		$this->comments_all = $this->parent_obj->db->get_all_comments( $post->ID );
		


		// strip out <!--shortcode--> tags
		$content = $this->_strip_shortcodes( $content );
		
		
		
		// check for our quicktag
		$has_quicktag = $this->_has_comment_block_quicktag( $content );

		// if it hasn't...
		if ( !$has_quicktag ) {
		
			// auto-format content accordingly
			
			// get action to take
			$action = apply_filters(
				
				// hook
				'cp_select_content_formatter',
				
				// default
				'tag'
				
			);
			
			// act on it
			switch( $action ) {
				
				// for poetry, for example, line by line commenting formatter is better
				case 'line' :

					// set constant - okay, since we never return here
					if ( !defined( 'COMMENTPRESS_BLOCK' ) ) 
						define( 'COMMENTPRESS_BLOCK', 'line' );
				
					// generate text signatures array
					$this->text_signatures = $this->_generate_line_signatures( $content );
					//print_r( $this->text_signatures ); die();
					
					// only continue parsing if we have an array of sigs
					if ( !empty( $this->text_signatures ) ) {
					
						// filter content by <br> and <br /> tags
						$content = $this->_parse_lines( $content );
						
					}
					
					break;
				
				// for general prose, existing formatter is fine
				case 'tag' :

					// set constant
					if ( !defined( 'COMMENTPRESS_BLOCK' ) ) 
						define( 'COMMENTPRESS_BLOCK', 'tag' );
						
					// filter content by lexia
					$content = $this->_parse_with_xml( $content );
					
					break;
			
			}
			
			
		} else {
		
			// set constant
			if ( !defined( 'COMMENTPRESS_BLOCK' ) ) 
				define( 'COMMENTPRESS_BLOCK', 'block' );
		
			// generate text signatures array
			$this->text_signatures = $this->_generate_block_signatures( $content );
			//print_r( $this->text_signatures ); die();
			
			// only parse content if we have an array of sigs
			if ( !empty( $this->text_signatures ) ) {
			
				// filter content by <!--commentblock--> quicktags
				$content = $this->_parse_blocks( $content );
				
			}
			
		}



		// store text sigs
		$this->parent_obj->db->set_text_sigs( $this->text_signatures );



		// --<
		return $content;
	
	}







	/** 
	 * @description: get comments sorted by text signature and paragraph
	 * @param integer $post_ID the ID of the post
	 * @return array $_comments
	 * @todo: 
	 *
	 */
	public function get_sorted_comments( $post_ID ) {
	
		// have we already sorted the comments?
		if ( !empty( $this->comments_sorted ) ) {
			
			// --<
			return $this->comments_sorted;
		
		}
	
		// --<
		return $this->_get_sorted_comments( $post_ID );
		
	}
	
	
	
	
	
	
	
//##############################################################################







	/*
	============================================================================
	PRIVATE METHODS
	============================================================================
	*/
	
	
	



	/** 
	 * @description: object initialisation
	 * @todo:
	 *
	 */
	function _init() {
	
		// define filename
		$class_file = 'commentpress-core/assets/includes/dom-helpers/class.custom.domdocument.php';

		// get path
		$class_file_path = commentpress_file_is_present( $class_file );

		// we're fine, include class definition
		require_once( $class_file_path );
	
		// init DOM object
		$this->dom = new CommentPress_DOMDocument();
	
	}







	/** 
	 * @description: parse with DOMDocument
	 */
	function _parse_with_xml( $content ) {
	
		// reference our post
		global $post;

		// get sorted comments and store
		$this->comments_sorted = $this->_get_sorted_comments( $post->ID );
		//print_r( $this->comments_sorted ); die();
		
		
		
		// parse standalone captioned images
		$content = $this->_parse_captions( $content );
		
		
		
		// suppress error bubbling
		libxml_use_internal_errors( true );
		
		// load the markup
		$this->dom->loadHTML( $content );



		// init debugger
		$blah = array();
		
		// init lexia tags
		$lexia = array( 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'ul',  'ol', 'dl', 'pre' );



		// init starting paragraph number
		$start_num = 1;
		
		// set key
		$key = '_cp_starting_para_number';
		
		// if the custom field already has a value...
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {
		
			// get it
			$start_num = absint( get_post_meta( $post->ID, $key, true ) );
			
		}
		
		// we already have our text signatures, so set flag
		$sig_key = 0;
		
		
		
		// init ( array( 'text_signature' => n ), where n is the number of duplicates )
		$duplicates = array();
		
		// get all elements
		$elements = $this->dom->getElementsByTagName( '*' );

		// deal with them in turn...
		foreach ( $elements as $element ) {
			
			// extract node name 
			$node_name = strtolower($element->nodeName);
	
			// deal with tags in array
			if ( in_array( $node_name, $lexia ) ) {
				
				/*
				print_r( "\n\n".'-----------------------------'."\n\n" );
				print_r( $element );
				print_r( "\n\n".'-----------------------------'."\n\n" );
				//die();
				*/
				
				// first get element as raw HTML - we need this to maintain 
				// compatibility with the previous parsing method
				$value = $this->dom->saveXHTML( $element );
				
				/*
				print_r( "\n\n".'-----------------------------'."\n\n" );
				print_r( $value );
				print_r( "\n\n".'-----------------------------'."\n\n" );
				*/
				
				
				
				// now get equivalent of innerHTML
				//$original_content = $element->childNodes;
				
				/*
				print_r( "\n\n".'-----------------------------'."\n\n" );
				print_r( $value );
				print_r( "\n\n".'-----------------------------'."\n\n" );
				*/
				
				/*
				// remove them so we can rebuild
				foreach( $original_content AS $node ) {
					$element->removeChild( $node );
				}
				*/
				
				
				
				// our class
				$our_class = 'textblock';
		
				// get any existing
				$existing_classes = $element->getAttribute( 'class' );
		
				// if we get any...
				if ( $existing_classes != '' ) {
			
					// append ours
					$our_class = $existing_classes.' '.$our_class;
			
				}
		
				// set class
				$element->setAttribute( 'class', $our_class );
				
				
				
				// get first character of tag to maintain compatibility with regex parser
				$text_sig_prefix = substr( $node_name, 0, 1 );
				
				// get text signature
				$text_signature = $text_sig_prefix . $this->_generate_text_signature( $value );
				
				// do we have one already?
				if ( in_array( $text_signature, $this->text_signatures ) ) {
			
					// is it in the duplicates array?
					if ( array_key_exists( $text_signature, $duplicates ) ) {
				
						// add one
						$duplicates[ $text_signature ]++;
				
					} else {
				
						// add it
						$duplicates[ $text_signature ] = 1;
				
					}
				
					// add number to end of text sig
					$text_signature .= '_'.$duplicates[ $text_signature ];
				
				}
				
				// construct ID
				$text_sig_id = 'textblock-' . $text_signature;
				
				// set class
				$element->setAttribute( 'id', $text_sig_id );
				
				// add to signatures array
				$this->text_signatures[] = $text_signature;
			
				
				
				// construct paragraph number
				$para_num = $sig_key + $start_num;
			
				// increment
				$sig_key++;
			
				// get comment count
				$comment_count = count( $this->comments_sorted[ $text_signature ] );
			
				// get comment icon
				$comment_icon_markup = $this->parent_obj->display->get_comment_icon( 
			
					$comment_count, 
					$text_signature, 
					'auto', 
					$para_num 
				
				);
			
				// get paragraph icon
				$paragraph_icon = $this->parent_obj->display->get_paragraph_icon( 
			
					$comment_count, 
					$text_signature, 
					'auto', 
					$para_num 
				
				);
				
				/*
				print_r( "\n\n".'-----------------------------'."\n\n" );
				print_r( $paragraph_icon );
				print_r( "\n" );
				print_r( $comment_icon );
				print_r( "\n\n".'-----------------------------'."\n\n" );
				*/
				
				$para_icon = new CommentPress_DOMDocument;
				$para_icon->loadHTML( $paragraph_icon );
				$para_icon_body = $para_icon->getElementsByTagName( 'body' )->item(0);

				/*
				print_r( "\n\n".'-----------------------------'."\n\n" );
				print_r( $para_icon_body );
				print_r( "\n\n".'-----------------------------'."\n\n" );
				*/
				
				$para_icon_fragment = $para_icon_body->firstChild;
				
				// create fragment from icons
				//$fragment = $this->dom->createDocumentFragment();
				//$fragment->loadHTML( $paragraph_icon.$comment_icon );
				
				$para_icon_node = $this->dom->importNode( $para_icon_fragment, true );
				
				/*
				print_r( "\n\n".'-----------------------------'."\n\n" );
				print_r( $node->childNodes );
				print_r( "\n\n".'-----------------------------'."\n\n" );
				*/
				
				// add icon to lexia
				$element->appendChild( $para_icon_node );
				
				
				
				
				$comment_icon = new CommentPress_DOMDocument;
				$comment_icon->loadHTML( $comment_icon_markup );
				$comment_icon_body = $comment_icon->getElementsByTagName( 'body' )->item(0);

				/*
				print_r( "\n\n".'-----------------------------'."\n\n" );
				print_r( $comment_icon_body );
				print_r( "\n\n".'-----------------------------'."\n\n" );
				*/
				
				$comment_icon_fragment = $comment_icon_body->firstChild;
				
				// create fragment from icons
				//$fragment = $this->dom->createDocumentFragment();
				//$fragment->loadHTML( $paragraph_icon.$comment_icon );
				
				$comment_icon_node = $this->dom->importNode( $comment_icon_fragment, true );
				
				/*
				print_r( "\n\n".'-----------------------------'."\n\n" );
				print_r( $node->childNodes );
				print_r( "\n\n".'-----------------------------'."\n\n" );
				*/
				
				// add icon to lexia
				$element->appendChild( $comment_icon_node );
				
				
				
				/*
				// add original content back to lexia
				foreach( $original_content AS $node ) {
					$element->appendChild( $node );
				}
				
				//$element->appendChild( $original_text );
				*/
				
				
				
			} // end check for lexia
			
			
			
			// check for inline blockquotes
			if ( $node_name == 'span' ) {
			
				//print_r( $element );
				$this->_remove_inline_blockquote_breaks( $element );
				
			}
			
		}
		
		//die();
		


		// store text sigs array in global
		$this->parent_obj->db->set_text_sigs( $this->text_signatures );
		
		
		
		// output correctly formatted (X)HTML
		$content = preg_replace(
			array( "/<html><body>/si", "!</body></html>$!si" ),
			'',
			$this->dom->saveXHTML()
		);
		
		
		
		// --<
		return $content;

	}






	/** 
	 * @description: removes <br /> tags that precede and follow span
	 * @param DOMDocument object $element the element
	 * @return string $content the parsed content
	 */
	function _remove_inline_blockquote_breaks( $element ) {
	
		// get existing classes
		$existing_classes = $element->getAttribute( 'class' );
		
		//print_r( $existing_classes."\n\n" );

		// are there any?
		if ( $existing_classes != '' ) {
			
			// split by space char
			$existing_array = explode( ' ', $existing_classes );
			
			// sanity check
			if ( count( $existing_array ) > 0 ) {
				
				// let's run through them
				foreach( $existing_array AS $existing_class ) {
				
					// is it our inline blockquote class?
					if ( $existing_class == 'blockquote-in-para' ) {
						
						// get previous br
						$br_before = $element->previousSibling;
					
						//print_r( $br_before );
						
						// delete it
						$element->parentNode->removeChild( $br_before );

						// get next br
						$br_after = $element->nextSibling;
					
						//print_r( $br_after );

						// delete it
						$element->parentNode->removeChild( $br_after );

					}
				
				}
			
			}
			
		}

	}
	
	
	
	
	
	
	
	/** 
	 * @description: parse the content by line (<br />)
	 * @param string $content the post content
	 * @return string $content the parsed content
	 * @todo: 
	 *
	 */
	function _parse_lines( $content ) {
	
		// parse standalone captioned images
		$content = $this->_parse_captions( $content );
				
		// get our lines
		$matches = $this->_get_line_matches( $content );
		//print_r( $matches ); die();
		
		// kick out if we don't have any
		if( !count( $matches ) ) {
		
			// --<
			return $content;
			
		}
		
		
		
		// reference our post
		global $post;



		// get sorted comments and store
		$this->comments_sorted = $this->_get_sorted_comments( $post->ID );
		//print_r( $this->comments_sorted ); die();
	


		// we already have our text signatures, so set flag
		$sig_key = 0;
		
		// init our content array
		$content_array = array();
	


		// run through 'em...
		foreach( $matches AS $line ) {

			// is there any content?
			if ( $line != '' ) {
				
				// check for paras
				if ( $line == '<p>' OR $line == '</p>' ) {
				
					// do we want to allow commenting on verses?
				
					// add to content array
					$content_array[] = $line;
	
				} else {
				
					// line commenting
				
					// get a signature for the line
					$text_signature = $this->text_signatures[ $sig_key ];
					
					// increment
					$sig_key++;
					
					// get comment count
					// NB: the sorted array contains whole page as key 0, so we use the incremented value
					$comment_count = count( $this->comments_sorted[ $text_signature ] );
					
					// get paragraph icon
					$paragraph_icon = $this->parent_obj->display->get_paragraph_icon( 
						
						$comment_count, 
						$text_signature, 
						'line', 
						$sig_key 
					
					);
					
					// get opening tag markup for this line
					$opening_tag = $this->parent_obj->display->get_para_tag( 
					
						$text_signature, 
						$paragraph_icon, 
						'span' 
						
					);
					
					// assign opening tag markup to line
					$line = $opening_tag.$line;
					
					// get comment icon
					$comment_icon = $this->parent_obj->display->get_comment_icon( 
					
						$comment_count, 
						$text_signature, 
						'line', 
						$sig_key 
					
					);
					//_cpdie( $commenticon );
					
					// replace inline html comment with comment_icon
					$line = str_replace( '<!-- line-end -->', ' '.$comment_icon, $line );
					
					// add to content array
					$content_array[] = $line;
	
				}
				
			}
			
		}

		//print_r( $this->text_signatures ); //die();
		//print_r( $duplicates ); die();
		//print_r( $content_array ); die();
		//die();
	

		
		// rejoin and exclude quicktag
		$content = implode( '', $content_array );
	


		// --<
		return $content;

	}
	






	/** 
	 * @description: splits the content into an array by line
	 * @param string $content the post content
	 * @param string $tag the tag to filter by
	 * @return array $matches the ordered array of matched items
	 * @todo: 
	 *
	 */
	function _get_line_matches( $content ) {
		
		// filter out embedded tweets
		$content = $this->_filter_twitter_embeds( $content );
				
		// wrap all lines with spans
		
		// get all instances
		$pattern = array(
		
			'/<br>/', 
			'/<br\/>/', 
			'/<br \/>/', 
			'/<br>\n/', 
			'/<br\/>\n/', 
			'/<br \/>\n/', 
			'/<p>/', 
			'/<\/p>/'
			
		);
		
		// define replacements
		$replace = array( 
		
			'<!-- line-end --></span><br>', 
			'<!-- line-end --></span><br/>', 
			'<!-- line-end --></span><br />', 
			'<br>'."\n".'<span class="cp-line">', 
			'<br/>'."\n".'<span class="cp-line">', 
			'<br />'."\n".'<span class="cp-line">', 
			'<p><span class="cp-line">', 
			'<!-- line-end --></span></p>' 
			
		);
		
		// do replacement
		$content = preg_replace( $pattern, $replace, $content );
		
		/*
		print_r( array(
		
			'content' => $content,
		
		) ); die();
		*/
		


		// explode by <span>
		$output_array = explode( '<span class="cp-line">', $content );
		//print_r( $output_array ); die();
		
		// kick out if we have an empty array
		if ( empty( $output_array ) ) {
		
			// --<
			return array();
		
		}
		
		
		
		// --<
		return $output_array;
		
	}
	
	
	
		
		
	/** 
	 * @description: parses the content by line (<br />) and builds text signatures array
	 * @param string $content the post content
	 * @return array $text_signatures the ordered array of text signatures
	 * @todo: 
	 *
	 */
	function _generate_line_signatures( $content ) {
	
		// don't filter if a password is required
		if ( post_password_required() ) {
		
			// store text sigs array in global
			$this->parent_obj->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;
			
		}
		
		
		
		// wrap all lines with spans
		


		// parse standalone captioned images
		$content = $this->_parse_captions( $content );
				


		// explode by <span>
		$output_array = $this->_get_line_matches( $content );
		//print_r( $output_array ); die();
		
		// kick out if we have an empty array
		if ( empty( $output_array ) ) {
		
			// store text sigs array in global
			$this->parent_obj->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;
			
		}
		
		
		
		// reference our post
		global $post;

		// init our content array
		$content_array = array();

		// init ( array( 'text_signature' => n ), where n is the number of duplicates )
		$duplicates = array();



		// run through 'em...
		foreach( $output_array AS $paragraph ) {
		
			// is there any content?
			if ( $paragraph != '' ) {
				
				// check for paras
				if ( $paragraph == '<p>' OR $paragraph == '</p>' ) {
				
					// do we want to allow commenting on verses?
				
				} else {
				
					// line commenting
				
					// get a signature for the paragraph
					$text_signature = $this->_generate_text_signature( $paragraph );
					
					// do we have one already?
					if ( in_array( $text_signature, $this->text_signatures ) ) {
					
						// is it in the duplicates array?
						if ( array_key_exists( $text_signature, $duplicates ) ) {
						
							// add one
							$duplicates[ $text_signature ]++;
						
						} else {
						
							// add it
							$duplicates[ $text_signature ] = 1;
						
						}
						
						// add number to end of text sig
						$text_signature .= '_'.$duplicates[ $text_signature ];
						
					}
					
					// add to signatures array
					$this->text_signatures[] = $text_signature;
					
				}
				
			}
			
		}

		//print_r( $this->text_signatures ); //die();
		//print_r( $duplicates ); die();
		//die();
	


		// store text sigs array in global
		$this->parent_obj->db->set_text_sigs( $this->text_signatures );



		// --<
		return $this->text_signatures;

	}
	






	/** 
	 * @description: parses the content by comment block
	 * @param string $content the post content
	 * @return string $content the parsed content
	 * @todo: this is probably mighty slow - review preg_replace patterns
	 *
	 */
	function _parse_blocks( $content ) {
	
		// parse standalone captioned images
		$content = $this->_parse_captions( $content );
				
		// get our lines
		$matches = $this->_get_block_matches( $content );
		//print_r( $matches ); die();
		
		// kick out if we don't have any
		if( !count( $matches ) ) {
		
			// --<
			return $content;
			
		}
		
		
		
		// reference our post
		global $post;

		

		// get sorted comments and store
		$this->comments_sorted = $this->_get_sorted_comments( $post->ID );
		//print_r( $this->comments_sorted ); die();
	


		// we already have our text signatures, so set flag
		$sig_key = 0;
		
		// init content array
		$content_array = array();
		
		
		
		// run through 'em...
		foreach( $matches AS $paragraph ) {
		
			// is there any content?
			if ( $paragraph != '' ) {
	  
				// get a signature for the paragraph
				$text_signature = $this->text_signatures[ $sig_key ];
				
				// increment
				$sig_key++;
				
				// get comment count
				// NB: the sorted array contains whole page as key 0, so we use the incremented value
				$comment_count = count( $this->comments_sorted[ $text_signature ] );
				
				// get comment icon
				$comment_icon = $this->parent_obj->display->get_comment_icon( 
				
					$comment_count, 
					$text_signature, 
					'block', 
					$sig_key 
					
				);
				
				// get paragraph icon
				$paragraph_icon = $this->parent_obj->display->get_paragraph_icon( 
				
					$comment_count, 
					$text_signature, 
					'block', 
					$sig_key 
					
				);
				
				// get comment icon markup
				$icon_html = $this->parent_obj->display->get_para_tag( 
				
					$text_signature, 
					$paragraph_icon.$comment_icon, 
					'div' 
					
				);
				
				// assign icons to blocks
				$paragraph = $icon_html.$paragraph.'</div>'."\n\n\n\n";
				
				// add to content array
				$content_array[] = $paragraph;
				
			}
			
		}

		//print_r( $this->text_signatures ); //die();
		//print_r( $duplicates ); die();
	

		
		// rejoin and exclude quicktag
		$content = implode( '', $content_array );
	


		// --<
		return $content;

	}
	






	/** 
	 * @description: splits the content into an array by block
	 * @param string $content the post content
	 * @param string $tag the tag to filter by
	 * @return array $matches the ordered array of matched items
	 * @todo: 
	 *
	 */
	function _get_block_matches( $content ) {
		
		// filter out embedded tweets
		$content = $this->_filter_twitter_embeds( $content );
				
		// wp_texturize() does an okay job with creating paragraphs, but comments tend
		// to screw things up. let's try and fix...

		//print_r( array( 'before' => $content ) );

		// first, replace all instances of '   <!--commentblock-->   ' with
		// '<p><!--commentblock--></p>\n'
		$content = preg_replace( 
		
			'/\s+<!--commentblock-->\s+/', 
			'<p><!--commentblock--></p>'."\n", 
			$content 
			
		);

		// next, replace all instances of '<p><!--commentblock-->fengfnefe' with
		// '<p><!--commentblock--></p>\n<p>fengfnefe'
		$content = preg_replace( 
		
			'/<p><!--commentblock-->/', 
			'<p><!--commentblock--></p>'."\n".'<p>', 
			$content 
			
		);

		// next, replace all instances of 'fengfnefe<!--commentblock--></p>' with
		// 'fengfnefe</p>\n<p><!--commentblock--></p>'
		$content = preg_replace( 
		
			'/<!--commentblock--><\/p>/', 
			'</p>'."\n".'<p><!--commentblock--></p>'."\n", 
			$content 
			
		);

		// replace all instances of '<br />\n<!--commentblock--><br />\n' with
		// '</p>\n<p><!--commentblock--></p>\n<p>'
		$content = preg_replace( 
		
			'/<br \/>\s+<!--commentblock--><br \/>\s+/', 
			'</p>'."\n".'<p><!--commentblock--></p>'."\n".'<p>', 
			$content 
			
		);

		// next, replace all instances of '<br />\n<!--commentblock--></p>\n' with
		// '</p>\n<p><!--commentblock--></p>\n<p>'
		$content = preg_replace( 
		
			'/<br \/>\s+<!--commentblock--><\/p>\s+/', 
			'</p>'."\n".'<p><!--commentblock--></p>'."\n", 
			$content 
			
		);

		// next, replace all instances of '<p><!--commentblock--><br />\n' with
		// '<p><!--commentblock--></p>\n<p>'
		$content = preg_replace( 
		
			'/<p><!--commentblock--><br \/>\s+/', 
			'<p><!--commentblock--></p>'."\n".'<p>', 
			$content 
			
		);
		
		// repair some oddities: empty newlines with whitespace after:
		$content = preg_replace( 
		
			'/<p><br \/>\s+/', 
			'<p>', 
			$content 
			
		);
		
		// repair some oddities: empty newlines without whitespace after:
		$content = preg_replace( 
		
			'/<p><br \/>/', 
			'<p>', 
			$content 
			
		);
		
		// repair some oddities: empty paragraphs with whitespace inside:
		$content = preg_replace( 
		
			'/<p>\s+<\/p>\s+/', 
			'', 
			$content 
			
		);
		
		// repair some oddities: empty paragraphs without whitespace inside:
		$content = preg_replace( 
		
			'/<p><\/p>\s+/', 
			'', 
			$content 
			
		);

		// repair some oddities: any remaining empty paragraphs:
		$content = preg_replace( 
		
			'/<p><\/p>/', 
			'', 
			$content 
			
		);

		//print_r( array( 'after' => $content ) ); die();
		
		
		
		// explode by <p> version to temp array
		$output_array = explode( '<p><'.'!--commentblock--></p>', $content );
		
		// kick out if we have an empty array
		if ( empty( $output_array ) ) {
		
			// --<
			return array();
		
		}
		
		
		
		// --<
		return $output_array;
		
	}
	
	
	
		
		
	/** 
	 * @description: parses the content by comment block and generates text signature array
	 * @param string $content the post content
	 * @return array $text_signatures the ordered array of text signatures
	 * @todo: this is probably mighty slow - review preg_replace patterns
	 *
	 */
	function _generate_block_signatures( $content ) {
	
		// don't filter if a password is required
		if ( post_password_required() ) {
		
			// store text sigs array in global
			$this->parent_obj->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;
			
		}
		
		
		
		// parse standalone captioned images
		$content = $this->_parse_captions( $content );
				


		// get blocks array
		$matches = $this->_get_block_matches( $content );
		
		// init ( array( 'text_signature' => n ), where n is the number of duplicates )
		$duplicates = array();

		// run through 'em...
		foreach( $matches AS $paragraph ) {
		
			// is there any content?
			if ( $paragraph != '' ) {
	  
				// get a signature for the paragraph
				$text_signature = $this->_generate_text_signature( $paragraph );
				
				// do we have one already?
				if ( in_array( $text_signature, $this->text_signatures ) ) {
				
					// is it in the duplicates array?
					if ( array_key_exists( $text_signature, $duplicates ) ) {
					
						// add one
						$duplicates[ $text_signature ]++;
					
					} else {
					
						// add it
						$duplicates[ $text_signature ] = 1;
					
					}
					
					// add number to end of text sig
					$text_signature .= '_'.$duplicates[ $text_signature ];
					
				}
				
				// add to signatures array
				$this->text_signatures[] = $text_signature;
				
			}
			
		}

		//print_r( $this->text_signatures ); die();
		//print_r( $duplicates ); die();
	


		// store text sigs array in global
		$this->parent_obj->db->set_text_sigs( $this->text_signatures );



		// --<
		return $this->text_signatures;

	}
	






	/** 
	 * @description: utility to check if the content has our custom quicktag
	 * @param string $content the post content
	 * @return string $content modified post content
	 * @todo: 
	 *
	 */
	function _has_comment_block_quicktag( $content ) {
	
		// init
		$return = false;
		
		
	
		// look for < !--commentblock--> comment
		if ( strstr( $content, '<!--commentblock-->' ) !== false ) {
		
			// yep
			$return = true;
		
		}
		
		
		
		// --<
		return $return;

	}
	
	
	
	
	
	
	
	/** 
	 * @description: utility to remove our custom quicktag
	 * @param string $content the post content
	 * @return string $content modified post content
	 * @todo: 
	 *
	 */
	function _strip_comment_block_quicktag( $content ) {
	
		// look for < !--commentblock--> comment
		if ( preg_match('/<'.'!--commentblock--><br \/>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
		
		
		
		// look for < !--commentblock--> comment
		if ( preg_match('/<p><'.'!--commentblock--><\/p>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
		
		
		
		// --<
		return $content;

	}
	
	
	
	
	
	
	
	/** 
	 * @description: utility to strip out shortcodes from content otherwise they get formatting
	 * @param string $content the post content
	 * @return string $content modified post content
	 * @todo: 
	 *
	 */
	function _strip_shortcodes( $content ) {
	
		/*
		------------------------------------------------------------------------
		Notes added: 08 Mar 2012
		------------------------------------------------------------------------
		
		Here's how these quicktags work...
		http://codex.wordpress.org/Customizing_the_Read_More
		
		
		-------------
		More Quicktag
		-------------
		
		However, we cannot be sure of how the quicktags has been inserted. For example (1):
		
		<p>Here&#8217;s the teaser<br />
		<span id="more-689"></span><br />
		Here&#8217;s the rest of the post</p>
		
		Is the intention here that the teaser is a paragraph? I'd say so.
		
		What about (2):
		
		<p>Here&#8217;s the teaser</p>
		<p><span id="more-689"></span></p>
		<p>Here&#8217;s the rest of the post</p>
		
		I'd say the same as above.
		
		And then these two possibilities (3) & (4):
		
		<p>Here&#8217;s the teaser<span id="more-689"></span><br />
		Here&#8217;s the rest of the post</p>
		
		<p>Here&#8217;s the teaser<br />
		<span id="more-689"></span>Here&#8217;s the rest of the post</p>
		
		Now, for our purposes, since we currently use the excerpt in the blog archives, only
		(1) and (2) are truly problematic - because they cause visible formatting. (3) & (4)
		do not currently get filtered out because the spans are inline - but they do imply
		that the content before and after should be self-contained. As a result, I think it
		is probably better to add a statement about correct usage in to the help text so that
		we can reliably parse the content.
		
		
		-----------------
		NoTeaser Quicktag
		-----------------
		
		The Codex says "Include <!--noteaser--> in the post text, immediately after the <!--more-->"
		which really means *on the same line*. When this is done, our content looks like this (1):
		
		<p><span id="more-691"></span><!--noteaser--></p>
		<p>And this is the rest of the post blah</p>
		
		Or (2):
		
		<p><span id="more-691"></span><!--noteaser--><br />
		And this is the rest of the post blah</p>
		
		------------------------------------------------------------------------
		*/
	
		//print_r( $content ); die();
		
		// look for inline <!--more--> span
		if ( preg_match('/<span id="more-(.*?)?'.'><\/span><br \/>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
		
		// look for separated <!--more--> span
		if ( preg_match('/<p><span id="more-(.*?)?'.'><\/span><\/p>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
				
		// look for inline <!--more--> span correctly followed by <!--noteaser-->
		if ( preg_match('/<span id="more-(.*?)?'.'><\/span><!--noteaser--><br \/>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
				
		// look for separated <!--more--> span correctly followed by <!--noteaser-->
		if ( preg_match('/<p><span id="more-(.*?)?'.'><\/span><!--noteaser--><\/p>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
				
		// look for incorrectly placed inline <!--noteaser--> comment
		if ( preg_match('/<'.'!--noteaser--><br \/>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
		
		
		
		// look for incorrectly placed separated <!--noteaser--> comment
		if ( preg_match('/<p><'.'!--noteaser--><\/p>/', $content, $matches) ) {
		
			// derive list
			$content = explode( $matches[0], $content, 2 );
			
			// rejoin to exclude shortcode
			$content = implode( '', $content );
		
		}
		
		
		
		// this gets the additional text... (not used)
		if ( !empty($matches[1]) ) {
			//$more_link_text = strip_tags(wp_kses_no_null(trim($matches[1])));
		}
		
		//print_r( $content ); die();


		// --<
		return $content;

	}
	
	
	
	
	
	
	
	/** 
	 * @description: generates a text signature based on the content of a paragraph
	 * @param string $text the text of a paragraph
	 * @return string $text_signature the generated text signature
	 *
	 */
	function _generate_text_signature( $text ) {
	
		// get an array of words from the text
		$words = explode( ' ', preg_replace( '/[^A-Za-z]/', ' ', html_entity_decode($text) ) );
		
		// store unique words
		// NB: this may be a mistake for poetry, which can use any words in any order
		$unique_words = array_unique( $words );
		
		
		
		// init sig
		$text_signature = null;
	
		// run through our unique words
		foreach( $unique_words AS $key => $word ) {
			
			// add first letter
			$text_signature .= substr( $word, 0, 1 );
			
			// limit to 250 chars
			// NB: this is because we have changed the format of text signatures by adding numerals
			// when there are duplicates. Duplicates add at least 2 characters, so there is the 
			// (admittedly remote) possibility of exceeding the varchar(255) character limit.
			if( $key > 250 ) { break; }
			
		}
		
		
		
		// --<
		return $text_signature;
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: removes embedded tweets (new in WP 3.4)
	 * @param string $content the post content
	 * @return string $content the filtered post content
	 * @todo: make these commentable
	 *
	 */
	function _filter_twitter_embeds( $content ) {
	
		// test for a WP 3.4 function
		if ( function_exists( 'wp_get_themes' ) ) {
	
			// look for Embedded Tweet <blockquote>
			if ( preg_match('#<(blockquote class="twitter-tweet)[^>]*?'.'>(.*?)</(blockquote)>#si', $content, $matches) ) {
			
				// derive list
				$content = explode( $matches[0], $content, 2 );
				
				// rejoin to exclude from content to be parsed
				$content = implode( '', $content );
				
				// also remove twitter script
				$content = str_replace(
				
					'<p><script src="//platform.twitter.com/widgets.js" charset="utf-8"></script></p>', 
					'', 
					$content 
					
				);
				
			}
			
		}
		
		
		
		// --<
		return $content;
				
	}
	
	
	
		
		
		
	/** 
	 * @description: wraps standalone captions (ie, not inside <p> tags) in <p>
	 * @param string $content the post content
	 * @return string $content the filtered post content
	 * @todo:
	 *
	 */
	function _parse_captions( $content ) {
	
		// filter captioned images that are *not* inside other tags
		$pattern = array(
		
			'/\n<!-- cp_caption_start -->/',
			'/<!-- cp_caption_end -->\n/'
			
		);
		
		// define replacements
		$replace = array( 
		
			"\n".'<p><!-- cp_caption_start -->', 
			'<!-- cp_caption_end --></p>'."\n"
			
		);
		
		// do replacement
		$content = preg_replace( $pattern, $replace, $content );

		/*
		print_r( array( 
		
			'c' => $content 
		
		) ); 
		
		die();
		*/

		// --<
		return $content;
				
	}
	
	
	
		
		
		

	/** 
	 * @description: removes leading and trailing <br /> tags from embedded quotes
	 * @param string $content the post content
	 * @return string $content the filtered post content
	 * @todo:
	 *
	 */
	function _parse_blockquotes_in_paras( $content ) {
		
		// make sure we strip leading br
		$content = str_replace( 
			'<br />'."\n".'<span class="blockquote-in-para">',
			"\n".'<span class="blockquote-in-para">',
			$content
		);
		
		// analyse
		preg_match_all( '#(<span class="blockquote-in-para">(.*?)</span>)<br />#si', $content, $matches );
		
		// did we get any?
		if ( isset( $matches[0] ) AND !empty( $matches[0] ) ) {
		
			$content = str_replace( 
				$matches[0],
				$matches[1],
				$content
			);
		
		}
		
		/*
		print_r( array(
			'c' => $content,
			//'new' => $_content,
			'm' => $matches,
		) ); die();
		*/
		
		// --<
		return $content;
				
	}
	
	
	
		
		
		

	/** 
	 * @description: get comments sorted by text signature and paragraph
	 * @param integer $post_ID the ID of the post
	 * @return array $_comments
	 * @todo: 
	 *
	 */
	function _get_sorted_comments( $post_ID ) {
	
		// init return
		$_comments = array();
		
		
	
		// get all comments
		$comments = $this->comments_all;
		
		
		
		// filter out any multipage comments not on this page
		$comments = $this->_multipage_comment_filter( $comments );
		//print_r( $comments ); die();
		
		
		
		// get our signatures
		$_sigs = $this->parent_obj->db->get_text_sigs();
		//print_r( $_sigs ); die();
		
		// assign comments to text signatures
		$_assigned = $this->_assign_comments( $comments, $_sigs );
		
		// NB: $_assigned is an array with sigs as keys and array of comments as value
		// it may be empty...
		
		// we must have text signatures...
		if ( !empty( $_sigs ) ) {
		


			// if we have any comments on the whole page...
			if ( isset( $_assigned[ 'WHOLE_PAGE_OR_POST_COMMENTS' ] ) ) {
		
				// add them first
				$_comments[ 'WHOLE_PAGE_OR_POST_COMMENTS' ] = $_assigned[ 'WHOLE_PAGE_OR_POST_COMMENTS' ];
				
			} else {
			
				// append empty array
				$_comments[ 'WHOLE_PAGE_OR_POST_COMMENTS' ] = array();
			
			}
			
		

			// then add  in the order of our text signatures
			foreach( $_sigs AS $text_signature ) {
			
				// if we have any assigned...
				if ( isset( $_assigned[ $text_signature ] ) ) {
			
					// append assigned comments
					$_comments[ $text_signature ] = $_assigned[ $text_signature ];
					
				} else {
				
					// append empty array
					$_comments[ $text_signature ] = array();
				
				}
				
			}
			


			// if we have any pingbacks or trackbacks...
			if ( isset( $_assigned[ 'PINGS_AND_TRACKS' ] ) ) {
		
				// add them last
				$_comments[ 'PINGS_AND_TRACKS' ] = $_assigned[ 'PINGS_AND_TRACKS' ];
				
			} else {
			
				// append empty array
				$_comments[ 'PINGS_AND_TRACKS' ] = array();
			
			}
			
		

		}
		
		
		
		//print_r( $_comments ); die();

		// --<
		return $_comments;
		
	}
	
	
	
	
	
	
	
	/** 
	 * @description: filter comments to find comments for the current page of a multipage post
	 * @param array $comments array of comment objects
	 * @return array $filtered array of comments for the current page
	 * @todo: 
	 *
	 */
	function _multipage_comment_filter( $comments ) {
	  
		// access globals
		global $post, $page, $multipage;
		//print_r( $comments ); die();
		


	  	// init return
		$filtered = array();

		// kick out if no comments
		if( !is_array( $comments ) OR empty( $comments ) ) {
		
			// --<
			return $filtered;
		}
		
		
		
		// kick out if not multipage
		if( !isset( $multipage ) OR !$multipage ) {
		
			// --<
			return $comments;
			
		}
		
		
		
		// now add only comments that are on this page or are page-level
		foreach ( $comments AS $comment ) {
		
			// if it has a text sig
			if ( !is_null( $comment->comment_signature ) AND $comment->comment_signature != '' ) {
			
				// set key
				$key = '_cp_comment_page';
				
				// does it have a comment meta value?
				if ( get_comment_meta( $comment->comment_ID, $key, true ) != '' ) {
				
					// get the page number
					$page_num = get_comment_meta( $comment->comment_ID, $key, true );
					
					// is it the current one?
					if ( $page_num == $page ) {
					
						// add it
						$filtered[] = $comment;
						
					}
					
				}
				
			} else {
			
				// page-level comment: add it
				$filtered[] = $comment;
				
			}
		
		}
		
		
		
		// --<
		return $filtered;
		
	}
	






	/** 
	 * @description: filter comments by text signature
	 * @param array $comments array of comment objects
	 * @param array $text_signatures array of text signatures
	 * @param integer $confidence the confidence level of paragraph identity - default 90%
	 * @return array $assigned array with text signatures as keys and array of comments as values
	 * @todo: 
	 *
	 */
	function _assign_comments( $comments, $text_signatures, $confidence = 90 ) {
	  
	  	// init returned array
	  	// NB: we use a very unlikely key for page-level comments: WHOLE_PAGE_OR_POST_COMMENTS
		$assigned = array();

		// kick out if no comments
		if( !is_array( $comments ) OR empty( $comments ) ) {
		
			// --<
			return $assigned;
		}
		
		
		
		// kick out if no text_signatures
		if( !is_array( $text_signatures ) OR empty( $text_signatures ) ) {
		
			// --<
			return $assigned;
		}
		
		
		
		/*
		print_r( array( 
		
			'comments' => $comments,
			'sigs' => $text_signatures 
		
		) ); die();
		*/
		
		// run through our comments...
		foreach( $comments AS $comment ) {
		
			// test for empty comment text signature
			if ( !is_null( $comment->comment_signature ) AND $comment->comment_signature != '' ) {
			
				// do we have an exact match in the text sigs array?
				// NB: this will work, because we're already ensuring identical sigs are made unique
				if ( in_array( $comment->comment_signature, $text_signatures ) ) {
					
					// yes, assign to that key
					$assigned[ $comment->comment_signature ][] = $comment;
				
				} else {
				
					// init possibles array
					$possibles = array();
				
					// find the nearest matching text signature
					foreach( $text_signatures AS $text_signature ) {
					
						// compare strings...
						similar_text( $comment->comment_signature, $text_signature, $score );
						
						//print_r( $score.'<br>' ); 
						
						// add to possibles array if it passes
						if( $score >= $confidence ) { $possibles[ $text_signature ] = $score; }
					
					}
					//die();
					
					// did we get any?
					if ( !empty( $possibles ) ) {
						
						// sort them by score
						arsort( $possibles );
						//print_r( array_keys( $possibles ) ); die();
						
						// let's use the sig with the highest score
						$highest = array_pop( array_keys( $possibles ) );
					
						// assign comment to that key
						$assigned[ $highest ][] = $comment;
					
					} else {
					
						// set property in case we need it
						$comment->orphan = true;
					
						// clear text signature
						$comment->comment_signature = '';
							
						// is it a pingback or trackback?
						if ( $comment->comment_type == 'trackback' OR $comment->comment_type == 'pingback' ) {
		
							// we have one - assign to pings
							$assigned[ 'PINGS_AND_TRACKS' ][] = $comment;
						
						} else {
					
							// we have comment with no text sig - assign to page
							$assigned[ 'WHOLE_PAGE_OR_POST_COMMENTS' ][] = $comment;
							
						}
					
					}
				
				}
				
			} else {
			
				// is it a pingback or trackback?
				if ( $comment->comment_type == 'trackback' OR $comment->comment_type == 'pingback' ) {

					// we have one - assign to pings
					$assigned[ 'PINGS_AND_TRACKS' ][] = $comment;
				
				} else {
			
					// we have comment with no text sig - assign to page
					$assigned[ 'WHOLE_PAGE_OR_POST_COMMENTS' ][] = $comment;
					
				}
			
			}
			
		}
		
		// let's have a look
		//print_r( $assigned ); die();
		
		
		
		// --<
		return $assigned;
		
	}
	






//##############################################################################







} // class ends






