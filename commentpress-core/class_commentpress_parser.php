<?php /*
================================================================================
Class CommentpressCoreParser
================================================================================
AUTHOR: Christian Wach <needle@haystack.co.uk>
--------------------------------------------------------------------------------
NOTES
=====

This class is a wrapper for parsing content and comments. 

The aim is to migrate parsing of content to DOM parsing instead of regex.

When converted to DOM parsing, the class will two other classes, which help with
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
	var $parent_obj;
	
	// init text_signatures
	var $text_signatures = array();
	
	// all comments
	var $comments_all = array();
	
	// approved comments
	var $comments_approved = array();
	
	// sorted comments
	var $comments_sorted = array();
	





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
	 * @description: PHP 4 constructor
	 */
	function CommentpressCoreParser( $parent_obj ) {
		
		// is this php5?
		if ( version_compare( PHP_VERSION, "5.0.0", "<" ) ) {
		
			// call php5 constructor
			$this->__construct( $parent_obj );
			
		}
		
		// --<
		return $this;

	}






	/** 
	 * @description: set up all items associated with this object
	 * @todo: 
	 *
	 */
	function initialise() {
		
	}







	/** 
	 * @description: if needed, destroys all items associated with this object
	 * @todo: 
	 *
	 */
	function destroy() {
	
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
	function the_content( $content ) {
	
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
						
					// generate text signatures array
					$this->text_signatures = $this->_generate_text_signatures( $content, 'p|ul|ol' );
					//print_r( $this->text_signatures ); die();
					
					// only continue parsing if we have an array of sigs
					if ( !empty( $this->text_signatures ) ) {
					
						// filter content by <p>, <ul> and <ol> tags
						$content = $this->_parse_content( $content, 'p|ul|ol' );
						
					}
					
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
	function get_sorted_comments( $post_ID ) {
	
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
	
	}







	/** 
	 * @description: parses the content by tag
	 * @param string $content the post content
	 * @param string $tag the tag to filter by
	 * @return string $content the parsed content
	 * @todo: 
	 *
	 */
	function _parse_content( $content, $tag = 'p|ul|ol' ) {
	

		/*
		print_r( array( 
		
			'c' => $content 
		
		) ); 
		
		die();
		*/
		


		// parse standalone captioned images
		$content = $this->_parse_captions( $content );
				


		// get our paragraphs
		$matches = $this->_get_text_matches( $content, $tag );
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
		
		// run through 'em...
		foreach( $matches AS $paragraph ) {
	  
			// get a signature for the paragraph
			$text_signature = $this->text_signatures[ $sig_key ];
			
			// increment
			$sig_key++;
			
			// get comment count
			$comment_count = count( $this->comments_sorted[ $text_signature ] );
			
			// get comment icon
			$comment_icon = $this->parent_obj->display->get_comment_icon( 
			
				$comment_count, 
				$text_signature, 
				'auto', 
				$sig_key 
				
			);
			
			// get paragraph icon
			$paragraph_icon = $this->parent_obj->display->get_paragraph_icon( 
			
				$comment_count, 
				$text_signature, 
				'auto', 
				$sig_key 
				
			);
			
			// set pattern by first tag
			switch ( substr( $paragraph, 0 , 2 ) ) {
			
				case '<p': $tag = 'p'; break;
				case '<o': $tag = 'ol'; break;
				case '<u': $tag = 'ul'; break;
			
			}

			/*
			--------------------------------------------------------------------
			NOTES
			--------------------------------------------------------------------
			
			There's a temporary fix to exclude <param> and <pre> tags by excluding subsequent 'a' and 
			'r' chars - this regex needs more attention so that only <p> and <p ...> are captured.
			In HTML5 there is also the <progress> tag, but this is excluded along with <pre>
			Also, the WordPress visual editor inserts styles into <p> tags for text justification,
			so we need to feed this regex with enhanced tags to capture the following:
			
			<p style="text-align:left;"> 
			<p style="text-align:right;"> 
			<p style="text-align:center;">
			<p style="text-align:justify;">
			
			AND
			
			<p style="text-align:left"> 
			<p style="text-align:right"> 
			<p style="text-align:center">
			<p style="text-align:justify">
			
			--------------------------------------------------------------------
			*/
			
			// further checks when there's a <p> tag
			if ( $tag == 'p' ) {
				
				// set pattern by TinyMCE tag attribute, if we have one...
				if ( substr( $paragraph, 0 , 17 ) == '<p style="text-al' ) {
				
					// test for left
					if ( substr( $paragraph, 0 , 27 ) == '<p style="text-align:left;"' ) {
						$tag = 'p style="text-align:left;"';
					} elseif ( substr( $paragraph, 0 , 26 ) == '<p style="text-align:left"' ) {
						$tag = 'p style="text-align:left"';
					}
		
					// test for right
					if ( substr( $paragraph, 0 , 28 ) == '<p style="text-align:right;"' ) {
						$tag = 'p style="text-align:right;"';
					} elseif ( substr( $paragraph, 0 , 27 ) == '<p style="text-align:right"' ) {
						$tag = 'p style="text-align:right"';
					}
		
					// test for center
					if ( substr( $paragraph, 0 , 29 ) == '<p style="text-align:center;"' ) {
						$tag = 'p style="text-align:center;"';
					} elseif ( substr( $paragraph, 0 , 28 ) == '<p style="text-align:center"' ) {
						$tag = 'p style="text-align:center"';
					}
		
					// test for justify
					if ( substr( $paragraph, 0 , 30 ) == '<p style="text-align:justify;"' ) {
						$tag = 'p style="text-align:justify;"';
					} elseif ( substr( $paragraph, 0 , 29 ) == '<p style="text-align:justify"' ) {
						$tag = 'p style="text-align:justify"';
					}
				
				} // end check for text-align
	
				// test for Simple Footnotes para "heading"
				if ( substr( $paragraph, 0 , 16 ) == '<p class="notes"' ) {
					$tag = 'p class="notes"';
				}
	
				// if we fall through to here, treat it like it's just a <p> tag above.
				// This will fail if there are custom attributes set in the HTML editor,
				// but I'm not sure how to handle that without migrating to an XML parser
				//print_r( $tag ); //die();
			
			}

			/*
			--------------------------------------------------------------------
			NOTES
			--------------------------------------------------------------------
			
			There are also flaws with parsing nested lists, both ordered and unordered. The WordPress
			Unit Tests XML file reveals these, though the docs are hopefully clear enough that people
			won't use nested lists. However, the severity is such that I'm contemplating migrating to
			a DOM parser such as:
			
			phpQuery <https://github.com/TobiaszCudnik/phpquery>
			Simple HTML DOM <http://sourceforge.net/projects/simplehtmldom/>
			Others <http://stackoverflow.com/questions/3577641/how-to-parse-and-process-html-with-php>
			
			There are so many examples of people saying "don't use regex with HTML" that this probably
			ought to be done when time allows.

			--------------------------------------------------------------------
			*/
			
			// further checks when there's a <ol> tag
			if ( $tag == 'ol' ) {
				
				// set pattern by TinyMCE tag attribute
				switch ( substr( $paragraph, 0 , 21 ) ) {
					
					// compat with WP Footnotes
					case '<ol class="footnotes"': $tag = 'ol class="footnotes"'; break;
					
					// see notes for p tag above
				
				}
	
			}

			// assign icons to paras
			$pattern = array('#<('.$tag.'[^a^r>]*)>#');
			
			$replace = array( 
				
				$this->parent_obj->display->get_para_tag( 
					$text_signature, 
					$paragraph_icon.$comment_icon, 
					$tag 
				) 
				
			);
			
			$block = preg_replace( $pattern, $replace, $paragraph );
			
			// NB: because str_replace() has no limit to the replacements, I am switching to
			// preg_replace() because that does have a limit
			//$content = str_replace( $paragraph, $block, $content );
			
			// prepare paragraph for preg_replace
			$prepared_para = preg_quote( $paragraph );
			
			// because we use / as the delimiter, we need to escape all /s
			$prepared_para = str_replace( '/', '\/', $prepared_para );
			
			// only once please
			$limit = 1;

			// replace the paragraph in the original context, preserving all other content
			$content = preg_replace( 
			
				//array($paragraph), 
				'/'.$prepared_para.'/', 
				$block,
				$content,
				$limit				
				
			);
			
			/*
			print_r( array( 
			
				//'p' => $paragraph,
				'p' => $prepared_para,
				'b' => $block,
				'c' => $content
			
			) ); //die();
			*/
			
		}
		


		/*
		print_r( array( 
		
			'd' => $duplicates,
			't' => $this->text_signatures,
			'c' => $content 
		
		) ); 
		
		die();
		*/
		


		// --<
		return $content;

	}
	






	/** 
	 * @description: splits the content into an array by tag
	 * @param string $content the post content
	 * @param string $tag the tag to filter by
	 * @return array $matches the ordered array of matched items
	 * @todo: 
	 *
	 */
	function _get_text_matches( $content, $tag = 'p|ul|ol' ) {
	
		// filter out embedded tweets
		$content = $this->_filter_twitter_embeds( $content );
		
		// get our paragraphs (needed to split regex into two strings as some IDEs 
		// don't like PHP closing tags, even they are part of a regex and not actually
		// closing tags at all) 
		//preg_match_all( '/<('.$tag.')[^>]*>(.*?)(<\/('.$tag.')>)/', $content, $matches );
		preg_match_all( '#<('.$tag.')[^>]*?'.'>(.*?)</('.$tag.')>#si', $content, $matches );
		//print_r( $matches[0] ); print_r( $matches[1] ); exit();
		
		// kick out if we don't have any
		if( !empty($matches[0]) ) {
		
			// --<
			return $matches[0];
			
		} else {
		
			// --<
			return array();
		
		}
		
	}
	
	
	
		
		
		
	/** 
	 * @description: parses the content by tag and builds text signatures array
	 * @param string $content the post content
	 * @param string $tag the tag to filter by
	 * @return array $text_signatures the ordered array of text signatures
	 * @todo: 
	 *
	 */
	function _generate_text_signatures( $content, $tag = 'p|ul|ol' ) {
	
		// don't filter if a password is required
		if ( post_password_required() ) {
		
			// store text sigs array in global
			$this->parent_obj->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;
			
		}
		
		
		
		// parse standalone captioned images
		$content = $this->_parse_captions( $content );


				
		// get our paragraphs
		$matches = $this->_get_text_matches( $content, $tag );
		
		// kick out if we don't have any
		if( !count( $matches ) ) {
		
			// store text sigs array in global
			$this->parent_obj->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;
			
		}
		
		
		
		// init ( array( 'text_signature' => n ), where n is the number of duplicates )
		$duplicates = array();

		// run through 'em...
		foreach( $matches AS $paragraph ) {
	  
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
		
		
		
		// store text sigs array in global
		$this->parent_obj->db->set_text_sigs( $this->text_signatures );



		// --<
		return $this->text_signatures;

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
	 * @param integer $position paragraph position in a post
	 * @return string $sig the generated text signature
	 * @todo: implement some kind of paragraph identifier to distiguish identical paragraphs?
	 *
	 */
	function _generate_text_signature( $text, $position = null ) {
	
		// get an array of words from the text
		$words = explode( ' ', ereg_replace( '[^A-Za-z]', ' ', html_entity_decode($text) ) );
		
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
		
		
		
		// get sig - think this through (not used, as position always null
		$sig = ($position) ? 
				$position . ':' . $text_signature : 
				$text_signature;
		
		
		
		// --<
		return $sig;
		
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






?>