<?php

/**
 * CommentPress Core Parser Class.
 *
 * This class is a wrapper for parsing content and comments.
 *
 * The aim is to migrate parsing of content to DOM parsing instead of regex.
 * When converted to DOM parsing, this class will include two other classes,
 * which help with oddities in DOMDocument. These can be found in
 * `inc/dom-helpers` in the dom-parser branch.
 *
 * @since 3.0
 */
class Commentpress_Core_Parser {

	/**
	 * Plugin object.
	 *
	 * @since 3.0
	 * @access public
	 * @var object $parent_obj The plugin object
	 */
	public $parent_obj;

	/**
	 * Text signatures array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $text_signatures The text signatures array
	 */
	public $text_signatures = array();

	/**
	 * All comments array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $comments_all The all comments array
	 */
	public $comments_all = array();

	/**
	 * Approved comments array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $comments_approved The approved comments array
	 */
	public $comments_approved = array();

	/**
	 * Sorted comments array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $comments_sorted The sorted comments array
	 */
	public $comments_sorted = array();

	/**
	 * Do Not Parse flag.
	 *
	 * @see Commentpress_Core_Database->do_not_parse
	 * @since 3.8.10
	 * @access public
	 * @var bool $do_not_parse False if content is parsed, true disables parsing
	 */
	public $do_not_parse = false;

	/**
	 * Formatter flag.
	 *
	 * @since 3.8.10
	 * @access public
	 * @var str $formatter The type of formatter ('tag', 'line' or 'block')
	 */
	public $formatter = 'tag';

	/**
	 * Block name.
	 *
	 * @since 3.8.10
	 * @access public
	 * @var str $block_name The name of the block (e.g. "paragraph", "line" etc)
	 */
	public $block_name = '';



	/**
	 * Initialises this object.
	 *
	 * @since 3.0
	 *
	 * @param object $parent_obj a reference to the parent object
	 */
	function __construct( $parent_obj ) {

		// store reference to parent
		$this->parent_obj = $parent_obj;

		// initialise via 'wp' hook
		add_action( 'wp', array( $this, 'initialise' ) );

	}



	/**
	 * Set up all items associated with this object.
	 *
	 * @return void
	 */
	public function initialise() {

		global $post;

		// are we skipping parsing?
		if (

			// no need to parse 404s etc
			! is_object( $post ) OR (

				// post types can be skipped:
				$this->parent_obj->db->option_exists( 'cp_post_types_disabled' ) AND
				in_array( $post->post_type, $this->parent_obj->db->option_get( 'cp_post_types_disabled' ) )

			) OR (

				// individual entries can have parsing skipped when:
				$this->parent_obj->db->option_exists( 'cp_do_not_parse' ) AND
				$this->parent_obj->db->option_get( 'cp_do_not_parse' ) == 'y' AND
				$post->comment_status == 'closed' AND
				empty( $post->comment_count )

			)

		) {

			// store for later reference
			$this->do_not_parse = true;

			// filter commentable status
			add_filter( 'cp_is_commentable', '__return_false' );

		} else {

			// filter shortcodes at source
			add_filter( 'wp_audio_shortcode', array( $this, '_parse_audio_shortcode' ), 10, 5 );
			add_filter( 'wp_video_shortcode', array( $this, '_parse_video_shortcode' ), 10, 5 );

		}

	}



	/**
	 * If needed, destroys all items associated with this object.
	 *
	 * @return void
	 */
	public function destroy() {

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Public Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Intercept content and modify based on paragraphs, blocks or lines.
	 *
	 * @param str $content The existing content
	 * @return str $content The modified content
	 */
	public function the_content( $content ) {

		// reference our post
		global $post;

		// retrieve all comments and store
		// we need this data multiple times and only need to get it once
		$this->comments_all = $this->parent_obj->db->get_all_comments( $post->ID );

		// are we skipping parsing?
		if ( $this->do_not_parse ) {

			// return content unparsed
			return $content;

		}

		// strip out <!--shortcode--> tags
		$content = $this->_strip_shortcodes( $content );

		// check for our quicktag
		$has_quicktag = $this->_has_comment_block_quicktag( $content );

		// determine formatter
		if ( ! $has_quicktag ) {

			// auto-format content

			// get action to take (defaults to 'tag')
			$this->formatter = apply_filters( 'cp_select_content_formatter', 'tag' );

			// set constant
			if ( ! defined( 'COMMENTPRESS_BLOCK' ) ) define( 'COMMENTPRESS_BLOCK', $this->formatter );

		} else {

			// set action to take
			$this->formatter = 'block';

			// set constant
			if ( ! defined( 'COMMENTPRESS_BLOCK' ) ) define( 'COMMENTPRESS_BLOCK', 'block' );

		}

		// determine "lexia" names
		$this->lexia_set( $this->formatter );

		// act on formatter
		switch( $this->formatter ) {

			// for poetry
			case 'line' :

				// generate text signatures array
				$this->text_signatures = $this->_generate_line_signatures( $content );

				// only continue parsing if we have an array of sigs
				if ( ! empty( $this->text_signatures ) ) {

					// filter content by <br> and <br /> tags
					$content = $this->_parse_lines( $content );

				}

				break;

			// for general prose
			case 'tag' :

				// generate text signatures array
				$this->text_signatures = $this->_generate_text_signatures( $content, 'p|ul|ol' );

				// only continue parsing if we have an array of sigs
				if ( ! empty( $this->text_signatures ) ) {

					// filter content by <p>, <ul> and <ol> tags
					$content = $this->_parse_content( $content, 'p|ul|ol' );

				}

				break;

			// for blocks
			case 'block' :

				// generate text signatures array
				$this->text_signatures = $this->_generate_block_signatures( $content );

				// only parse content if we have an array of sigs
				if ( ! empty( $this->text_signatures ) ) {

					// filter content by <!--commentblock--> quicktags
					$content = $this->_parse_blocks( $content );

				}

				break;

		}

		// store text sigs
		$this->parent_obj->db->set_text_sigs( $this->text_signatures );

		// --<
		return $content;

	}



	/**
	 * Get comments sorted by text signature and paragraph.
	 *
	 * @param int $post_ID The numeric ID of the post
	 * @return array $comments
	 */
	public function get_sorted_comments( $post_ID ) {

		// have we already sorted the comments?
		if ( ! empty( $this->comments_sorted ) ) {

			// --<
			return $this->comments_sorted;

		}

		// --<
		return $this->_get_sorted_comments( $post_ID );

	}



	/**
	 * Store the name of the "block" for paragraphs, blocks or lines.
	 *
	 * @since 3.8.10
	 *
	 * @param str $formatter The formatter
	 */
	public function lexia_set( $formatter ) {

		// set block identifier
		switch ( $formatter ) {

			case 'block' :
				$block_name = __( 'block', 'commentpress-core' );
				break;

			case 'line' :
				$block_name = __( 'line', 'commentpress-core' );
				break;

			case 'tag' :
			default:
				$block_name = __( 'paragraph', 'commentpress-core' );
				break;

		}

		/**
		 * Allow filtering of block name by formatter.
		 *
		 * @since 3.8.10
		 *
		 * @param str $block_name The existing name of the block
		 * @param str $block_name The type of block
		 * @return str $block_name The modified name of the block
		 */
		$this->block_name = apply_filters( 'commentpress_lexia_block_name', $block_name, $formatter );

	}



	/**
	 * Get the name of the "block" for paragraphs, blocks or lines.
	 *
	 * @since 3.8.10
	 *
	 * @return str $block_name The name of the block
	 */
	public function lexia_get() {

		// return existing property
		return $this->block_name;

	}



//##############################################################################



	/**
	 * -------------------------------------------------------------------------
	 * Private Methods
	 * -------------------------------------------------------------------------
	 */



	/**
	 * Object initialisation.
	 *
	 * @return void
	 */
	function _init() {

	}



	/**
	 * Parses the content by tag.
	 *
	 * @param str $content The post content
	 * @param str $tag The tag to filter by
	 * @return str $content the parsed content
	 */
	function _parse_content( $content, $tag = 'p|ul|ol' ) {

		// parse standalone captioned images
		$content = $this->_parse_captions( $content );

		// parse embedded quotes
		$content = $this->_parse_blockquotes_in_paras( $content );

		// get our paragraphs
		$matches = $this->_get_text_matches( $content, $tag );

		// kick out if we don't have any
		if( ! count( $matches ) ) {
			return $content;
		}

		// reference our post
		global $post;

		// get sorted comments and store
		$this->comments_sorted = $this->_get_sorted_comments( $post->ID );

		// init starting paragraph number
		$start_num = 1;

		// set key
		$key = '_cp_starting_para_number';

		// if the custom field already has a value
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {

			// get it
			$start_num = absint( get_post_meta( $post->ID, $key, true ) );

		}

		// we already have our text signatures, so set flag
		$sig_key = 0;

		// run through 'em
		foreach( $matches AS $paragraph ) {

			// get a signature for the paragraph
			$text_signature = $this->text_signatures[$sig_key];

			// construct paragraph number
			$para_num = $sig_key + $start_num;

			// increment
			$sig_key++;

			// get comment count
			$comment_count = count( $this->comments_sorted[$text_signature] );

			// get comment icon
			$comment_icon = $this->parent_obj->display->get_comment_icon(
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

				// set pattern by TinyMCE tag attribute, if we have one
				if ( substr( $paragraph, 0 , 17 ) == '<p style="text-al' ) {

					// test for left
					if ( substr( $paragraph, 0 , 27 ) == '<p style="text-align:left;"' ) {
						$tag = 'p style="text-align:left;"';
					} elseif ( substr( $paragraph, 0 , 26 ) == '<p style="text-align:left"' ) {
						$tag = 'p style="text-align:left"';
					} elseif ( substr( $paragraph, 0 , 28 ) == '<p style="text-align: left;"' ) {
						$tag = 'p style="text-align: left;"';
					} elseif ( substr( $paragraph, 0 , 27 ) == '<p style="text-align: left"' ) {
						$tag = 'p style="text-align: left"';
					}

					// test for right
					if ( substr( $paragraph, 0 , 28 ) == '<p style="text-align:right;"' ) {
						$tag = 'p style="text-align:right;"';
					} elseif ( substr( $paragraph, 0 , 27 ) == '<p style="text-align:right"' ) {
						$tag = 'p style="text-align:right"';
					} elseif ( substr( $paragraph, 0 , 29 ) == '<p style="text-align: right;"' ) {
						$tag = 'p style="text-align: right;"';
					} elseif ( substr( $paragraph, 0 , 28 ) == '<p style="text-align: right"' ) {
						$tag = 'p style="text-align: right"';
					}

					// test for center
					if ( substr( $paragraph, 0 , 29 ) == '<p style="text-align:center;"' ) {
						$tag = 'p style="text-align:center;"';
					} elseif ( substr( $paragraph, 0 , 28 ) == '<p style="text-align:center"' ) {
						$tag = 'p style="text-align:center"';
					} elseif ( substr( $paragraph, 0 , 30 ) == '<p style="text-align: center;"' ) {
						$tag = 'p style="text-align: center;"';
					} elseif ( substr( $paragraph, 0 , 29 ) == '<p style="text-align: center"' ) {
						$tag = 'p style="text-align: center"';
					}

					// test for justify
					if ( substr( $paragraph, 0 , 30 ) == '<p style="text-align:justify;"' ) {
						$tag = 'p style="text-align:justify;"';
					} elseif ( substr( $paragraph, 0 , 29 ) == '<p style="text-align:justify"' ) {
						$tag = 'p style="text-align:justify"';
					} elseif ( substr( $paragraph, 0 , 31 ) == '<p style="text-align: justify;"' ) {
						$tag = 'p style="text-align: justify;"';
					} elseif ( substr( $paragraph, 0 , 30 ) == '<p style="text-align: justify"' ) {
						$tag = 'p style="text-align: justify"';
					}

				} // end check for text-align

				// test for Simple Footnotes para "heading"
				if ( substr( $paragraph, 0 , 16 ) == '<p class="notes"' ) {
					$tag = 'p class="notes"';
				}

				// if we fall through to here, treat it like it's just a <p> tag above.
				// This will fail if there are custom attributes set in the HTML editor,
				// but I'm not sure how to handle that without migrating to an XML parser

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

			// init start (for ol attribute)
			$start = 0;

			// further checks when there's a <ol> tag
			if ( $tag == 'ol' ) {

				// compat with WP Footnotes
				if ( substr( $paragraph, 0 , 21 ) == '<ol class="footnotes"' ) {

					// construct tag
					$tag = 'ol class="footnotes"';

				// add support for <ol start="n">
				} elseif ( substr( $paragraph, 0 , 11 ) == '<ol start="' ) {

					// parse tag
					preg_match( '/start="([^"]*)"/i', $paragraph, $matches );

					// construct new tag
					$tag = 'ol ' . $matches[0];

					// set start
					$start = $matches[1];

				}

			}

			// assign icons to paras
			$pattern = array('#<(' . $tag . '[^a^r>]*)>#');

			$replace = array(

				$this->parent_obj->display->get_para_tag(
					$text_signature,
					$paragraph_icon . $comment_icon,
					$tag,
					$start
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

			// protect all dollar numbers
			$block = str_replace( "$", "\\\$", $block );

			// only once please
			$limit = 1;

			// replace the paragraph in the original context, preserving all other content
			$content = preg_replace(
				//array($paragraph),
				'/' . $prepared_para . '/',
				$block,
				$content,
				$limit
			);

		}

		// --<
		return $content;

	}



	/**
	 * Splits the content into an array by tag.
	 *
	 * @param str $content The post content
	 * @param str $tag The tag to filter by
	 * @return array $matches The ordered array of matched items
	 */
	function _get_text_matches( $content, $tag = 'p|ul|ol' ) {

		// filter out embedded tweets
		$content = $this->_filter_twitter_embeds( $content );

		/**
		 * Get our paragraphs.
		 *
		 * This is needed to split regex into two strings since some IDEs don't
		 * like PHP closing tags, even they are part of a regex and not actually
		 * closing tags at all.
		 */
		//preg_match_all( '/<(' . $tag . ')[^>]*>(.*?)(<\/(' . $tag . ')>)/', $content, $matches );
		preg_match_all( '#<(' . $tag . ')[^>]*?' . '>(.*?)</(' . $tag . ')>#si', $content, $matches );

		// kick out if we don't have any
		if( ! empty($matches[0]) ) {

			// --<
			return $matches[0];

		} else {

			// --<
			return array();

		}

	}



	/**
	 * Parses the content by tag and builds text signatures array.
	 *
	 * @param str $content The post content
	 * @param str $tag The tag to filter by
	 * @return array $text_signatures The ordered array of text signatures
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
		if( ! count( $matches ) ) {

			// store text sigs array in global
			$this->parent_obj->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;

		}

		// init ( array( 'text_signature' => n ), where n is the number of duplicates )
		$duplicates = array();

		// run through 'em
		foreach( $matches AS $paragraph ) {

			// get a signature for the paragraph
			$text_signature = $this->_generate_text_signature( $paragraph );

			// do we have one already?
			if ( in_array( $text_signature, $this->text_signatures ) ) {

				// is it in the duplicates array?
				if ( array_key_exists( $text_signature, $duplicates ) ) {

					// add one
					$duplicates[$text_signature]++;

				} else {

					// add it
					$duplicates[$text_signature] = 1;

				}

				// add number to end of text sig
				$text_signature .= '_' . $duplicates[$text_signature];

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
	 * Parse the content by line (<br />).
	 *
	 * @param str $content The post content
	 * @return str $content The parsed content
	 */
	function _parse_lines( $content ) {

		// parse standalone captioned images
		$content = $this->_parse_captions( $content );

		// get our lines
		$matches = $this->_get_line_matches( $content );

		// kick out if we don't have any
		if( ! count( $matches ) ) {
			return $content;
		}

		// reference our post
		global $post;

		// get sorted comments and store
		$this->comments_sorted = $this->_get_sorted_comments( $post->ID );

		// init starting paragraph number
		$start_num = 1;

		// set key
		$key = '_cp_starting_para_number';

		// if the custom field already has a value
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {

			// get it
			$start_num = absint( get_post_meta( $post->ID, $key, true ) );

		}

		// we already have our text signatures, so set flag
		$sig_key = 0;

		// init our content array
		$content_array = array();

		// run through 'em
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
					$text_signature = $this->text_signatures[$sig_key];

					// construct paragraph number
					$para_num = $sig_key + $start_num;

					// increment
					$sig_key++;

					// get comment count
					// NB: the sorted array contains whole page as key 0, so we use the incremented value
					$comment_count = count( $this->comments_sorted[$text_signature] );

					// get paragraph icon
					$paragraph_icon = $this->parent_obj->display->get_paragraph_icon(
						$comment_count,
						$text_signature,
						'line',
						$para_num
					);

					// get opening tag markup for this line
					$opening_tag = $this->parent_obj->display->get_para_tag(
						$text_signature,
						$paragraph_icon,
						'span'
					);

					// assign opening tag markup to line
					$line = $opening_tag . $line;

					// get comment icon
					$comment_icon = $this->parent_obj->display->get_comment_icon(
						$comment_count,
						$text_signature,
						'line',
						$para_num
					);

					// replace inline html comment with comment_icon
					$line = str_replace( '<!-- line-end -->', ' ' . $comment_icon, $line );

					// add to content array
					$content_array[] = $line;

				}

			}

		}

		// rejoin and exclude quicktag
		$content = implode( '', $content_array );

		// --<
		return $content;

	}



	/**
	 * Splits the content into an array by line.
	 *
	 * @param str $content The post content
	 * @return array $output_array The ordered array of matched items
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
			'<br>' . "\n" . '<span class="cp-line">',
			'<br/>' . "\n" . '<span class="cp-line">',
			'<br />' . "\n" . '<span class="cp-line">',
			'<p><span class="cp-line">',
			'<!-- line-end --></span></p>'
		);

		// do replacement
		$content = preg_replace( $pattern, $replace, $content );

		// explode by <span>
		$output_array = explode( '<span class="cp-line">', $content );

		// kick out if we have an empty array
		if ( empty( $output_array ) ) {
			return array();
		}

		// --<
		return $output_array;

	}



	/**
	 * Parses the content by line (<br />) and builds text signatures array.
	 *
	 * @param str $content The post content
	 * @return array $text_signatures The ordered array of text signatures
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

		// run through 'em
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
							$duplicates[$text_signature]++;

						} else {

							// add it
							$duplicates[$text_signature] = 1;

						}

						// add number to end of text sig
						$text_signature .= '_' . $duplicates[$text_signature];

					}

					// add to signatures array
					$this->text_signatures[] = $text_signature;

				}

			}

		}

		// store text sigs array in global
		$this->parent_obj->db->set_text_sigs( $this->text_signatures );

		// --<
		return $this->text_signatures;

	}



	/**
	 * Parses the content by comment block.
	 *
	 * @param str $content The post content
	 * @return str $content The parsed content
	 */
	function _parse_blocks( $content ) {

		// parse standalone captioned images
		$content = $this->_parse_captions( $content );

		// get our lines
		$matches = $this->_get_block_matches( $content );

		// kick out if we don't have any
		if( ! count( $matches ) ) {
			return $content;
		}

		// reference our post
		global $post;

		// get sorted comments and store
		$this->comments_sorted = $this->_get_sorted_comments( $post->ID );

		// init starting paragraph number
		$start_num = 1;

		// set key
		$key = '_cp_starting_para_number';

		// if the custom field already has a value
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {

			// get it
			$start_num = absint( get_post_meta( $post->ID, $key, true ) );

		}

		// we already have our text signatures, so set flag
		$sig_key = 0;

		// init content array
		$content_array = array();

		// run through 'em
		foreach( $matches AS $paragraph ) {

			// is there any content?
			if ( $paragraph != '' ) {

				// get a signature for the paragraph
				$text_signature = $this->text_signatures[$sig_key];

				// construct paragraph number
				$para_num = $sig_key + $start_num;

				// increment
				$sig_key++;

				// get comment count
				// NB: the sorted array contains whole page as key 0, so we use the incremented value
				$comment_count = count( $this->comments_sorted[$text_signature] );

				// get comment icon
				$comment_icon = $this->parent_obj->display->get_comment_icon(
					$comment_count,
					$text_signature,
					'block',
					$para_num
				);

				// get paragraph icon
				$paragraph_icon = $this->parent_obj->display->get_paragraph_icon(
					$comment_count,
					$text_signature,
					'block',
					$para_num
				);

				// get comment icon markup
				$icon_html = $this->parent_obj->display->get_para_tag(
					$text_signature,
					$paragraph_icon . $comment_icon,
					'div'
				);

				// assign icons to blocks
				$paragraph = $icon_html . $paragraph . '</div>' . "\n\n\n\n";

				// add to content array
				$content_array[] = $paragraph;

			}

		}

		// rejoin and exclude quicktag
		$content = implode( '', $content_array );

		// --<
		return $content;

	}



	/**
	 * Splits the content into an array by block.
	 *
	 * @param str $content The post content
	 * @return array $output_array The ordered array of matched items
	 */
	function _get_block_matches( $content ) {

		// filter out embedded tweets
		$content = $this->_filter_twitter_embeds( $content );

		// wp_texturize() does an okay job with creating paragraphs, but comments tend
		// to screw things up. let's try and fix:

		// first, replace all instances of '   <!--commentblock-->   ' with
		// '<p><!--commentblock--></p>\n'
		$content = preg_replace(
			'/\s+<!--commentblock-->\s+/',
			'<p><!--commentblock--></p>' . "\n",
			$content
		);

		// next, replace all instances of '<p><!--commentblock-->fengfnefe' with
		// '<p><!--commentblock--></p>\n<p>fengfnefe'
		$content = preg_replace(
			'/<p><!--commentblock-->/',
			'<p><!--commentblock--></p>' . "\n" . '<p>',
			$content
		);

		// next, replace all instances of 'fengfnefe<!--commentblock--></p>' with
		// 'fengfnefe</p>\n<p><!--commentblock--></p>'
		$content = preg_replace(
			'/<!--commentblock--><\/p>/',
			'</p>' . "\n" . '<p><!--commentblock--></p>' . "\n",
			$content
		);

		// replace all instances of '<br />\n<!--commentblock--><br />\n' with
		// '</p>\n<p><!--commentblock--></p>\n<p>'
		$content = preg_replace(
			'/<br \/>\s+<!--commentblock--><br \/>\s+/',
			'</p>' . "\n" . '<p><!--commentblock--></p>' . "\n" . '<p>',
			$content
		);

		// next, replace all instances of '<br />\n<!--commentblock--></p>\n' with
		// '</p>\n<p><!--commentblock--></p>\n<p>'
		$content = preg_replace(
			'/<br \/>\s+<!--commentblock--><\/p>\s+/',
			'</p>' . "\n" . '<p><!--commentblock--></p>' . "\n",
			$content
		);

		// next, replace all instances of '<p><!--commentblock--><br />\n' with
		// '<p><!--commentblock--></p>\n<p>'
		$content = preg_replace(
			'/<p><!--commentblock--><br \/>\s+/',
			'<p><!--commentblock--></p>' . "\n" . '<p>',
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

		// explode by <p> version to temp array
		$output_array = explode( '<p><' . '!--commentblock--></p>', $content );

		// kick out if we have an empty array
		if ( empty( $output_array ) ) {
			return array();
		}

		// --<
		return $output_array;

	}



	/**
	 * Parses the content by comment block and generates text signature array.
	 *
	 * @param str $content The post content
	 * @return array $text_signatures The ordered array of text signatures
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

		// run through 'em
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
						$duplicates[$text_signature]++;

					} else {

						// add it
						$duplicates[$text_signature] = 1;

					}

					// add number to end of text sig
					$text_signature .= '_' . $duplicates[$text_signature];

				}

				// add to signatures array
				$this->text_signatures[] = $text_signature;

			}

		}

		// store text sigs array in global
		$this->parent_obj->db->set_text_sigs( $this->text_signatures );

		// --<
		return $this->text_signatures;

	}



	/**
	 * Utility to check if the content has our custom quicktag.
	 *
	 * @param str $content The post content
	 * @return str $content The modified post content
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
	 * Utility to remove our custom quicktag.
	 *
	 * @param str $content The post content
	 * @return str $content The modified post content
	 */
	function _strip_comment_block_quicktag( $content ) {

		// look for < !--commentblock--> comment
		if ( preg_match('/<' . '!--commentblock--><br \/>/', $content, $matches) ) {

			// derive list
			$content = explode( $matches[0], $content, 2 );

			// rejoin to exclude shortcode
			$content = implode( '', $content );

		}

		// look for < !--commentblock--> comment
		if ( preg_match('/<p><' . '!--commentblock--><\/p>/', $content, $matches) ) {

			// derive list
			$content = explode( $matches[0], $content, 2 );

			// rejoin to exclude shortcode
			$content = implode( '', $content );

		}

		// --<
		return $content;

	}



	/**
	 * Utility to strip out shortcodes from content otherwise they get formatting.
	 *
	 * @param str $content The post content
	 * @return str $content The modified post content
	 */
	function _strip_shortcodes( $content ) {

		/*
		------------------------------------------------------------------------
		Notes added: 08 Mar 2012
		------------------------------------------------------------------------

		Here's how these quicktags work:
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

		// look for inline <!--more--> span
		if ( preg_match('/<span id="more-(.*?)?' . '><\/span><br \/>/', $content, $matches) ) {

			// derive list
			$content = explode( $matches[0], $content, 2 );

			// rejoin to exclude shortcode
			$content = implode( '', $content );

		}

		// look for separated <!--more--> span
		if ( preg_match('/<p><span id="more-(.*?)?' . '><\/span><\/p>/', $content, $matches) ) {

			// derive list
			$content = explode( $matches[0], $content, 2 );

			// rejoin to exclude shortcode
			$content = implode( '', $content );

		}

		// look for inline <!--more--> span correctly followed by <!--noteaser-->
		if ( preg_match('/<span id="more-(.*?)?' . '><\/span><!--noteaser--><br \/>/', $content, $matches) ) {

			// derive list
			$content = explode( $matches[0], $content, 2 );

			// rejoin to exclude shortcode
			$content = implode( '', $content );

		}

		// look for separated <!--more--> span correctly followed by <!--noteaser-->
		if ( preg_match('/<p><span id="more-(.*?)?' . '><\/span><!--noteaser--><\/p>/', $content, $matches) ) {

			// derive list
			$content = explode( $matches[0], $content, 2 );

			// rejoin to exclude shortcode
			$content = implode( '', $content );

		}

		// look for incorrectly placed inline <!--noteaser--> comment
		if ( preg_match('/<' . '!--noteaser--><br \/>/', $content, $matches) ) {

			// derive list
			$content = explode( $matches[0], $content, 2 );

			// rejoin to exclude shortcode
			$content = implode( '', $content );

		}

		// look for incorrectly placed separated <!--noteaser--> comment
		if ( preg_match('/<p><' . '!--noteaser--><\/p>/', $content, $matches) ) {

			// derive list
			$content = explode( $matches[0], $content, 2 );

			// rejoin to exclude shortcode
			$content = implode( '', $content );

		}

		// this gets the additional text (not used)
		if ( ! empty($matches[1]) ) {
			//$more_link_text = strip_tags(wp_kses_no_null(trim($matches[1])));
		}

		// --<
		return $content;

	}



	/**
	 * Generates a text signature based on the content of a paragraph.
	 *
	 * @param str $text The text of a paragraph
	 * @return str $text_signature The generated text signature
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
	 * Removes embedded tweets (new in WP 3.4).
	 *
	 * @todo Make these commentable
	 *
	 * @param str $content The post content
	 * @return str $content The filtered post content
	 */
	function _filter_twitter_embeds( $content ) {

		// test for a WP 3.4 function
		if ( function_exists( 'wp_get_themes' ) ) {

			// look for Embedded Tweet <blockquote>
			if ( preg_match('#<(blockquote class="twitter-tweet)[^>]*?' . '>(.*?)</(blockquote)>#si', $content, $matches) ) {

				// derive list
				$content = explode( $matches[0], $content, 2 );

				// rejoin to exclude from content to be parsed
				$content = implode( '', $content );

				// remove old twitter script
				$content = str_replace(
					'<p><script src="//platform.twitter.com/widgets.js" charset="utf-8"></script></p>',
					'',
					$content
				);

				// remove new twitter script
				$content = str_replace(
					'<p><script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script></p>',
					'',
					$content
				);

			}

		}

		// --<
		return $content;

	}



	/**
	 * Wraps standalone captions (ie, not inside <p> tags) in <p>.
	 *
	 * Previously, there was a weakness in the means by which standalone captions
	 * were detected: it was assumed that standalones were always preceded by a
	 * newline, but this is not always true. When they are the first element of
	 * the post content, they can be standalone too. Props: Ralph Bloch.
	 *
	 * @param str $content The post content
	 * @return str $content The filtered post content
	 */
	function _parse_captions( $content ) {

		// filter captioned images that are *not* inside other tags
		$pattern = array(
			'/\n<!-- cp_caption_start -->/',
			'/<!-- cp_caption_end -->\n/'
		);

		// define replacements
		$replace = array(
			"\n" . '<p><!-- cp_caption_start -->',
			'<!-- cp_caption_end --></p>' . "\n"
		);

		// do replacement
		$content = preg_replace( $pattern, $replace, $content );

		// check for captions at the very beginning of content
		if ( substr( $content, 0, 25 ) == '<!-- cp_caption_start -->' ) {
			$content = '<p>' . $content;
		}

		// --<
		return $content;

	}



	/**
	 * Wraps processed audio shortcodes in a paragraph tag.
	 *
	 * @since 3.9.3
	 *
     * @param string $html Shortcode HTML output.
     * @param array $atts Array of shortcode attributes.
     * @param string $file Media file.
     * @param int $post_id Post ID.
     * @param string $library Media library used for the shortcode.
	 */
	function _parse_audio_shortcode( $html, $atts, $file, $post_id, $library ) {

		// wrap
		return '<p><span class="cp-audio-shortcode">' . $html . '</span></p>';

	}



	/**
	 * Wraps processed video shortcodes in a paragraph tag.
	 *
	 * @since 3.9.3
	 *
     * @param string $html Shortcode HTML output.
     * @param array $atts Array of shortcode attributes.
     * @param string $file Media file.
     * @param int $post_id Post ID.
     * @param string $library Media library used for the shortcode.
	 */
	function _parse_video_shortcode( $html, $atts, $file, $post_id, $library ) {

		// replace enclosing div with span
		$html = str_replace( '<div', '<span', $html );
		$html = str_replace( '</div', '</span', $html );

		// wrap
		return '<p><span class="cp-video-shortcode"><span></span>' . $html . '</span></p>';

	}



	/**
	 * Removes leading and trailing <br /> tags from embedded quotes.
	 *
	 * @param string $content The post content
	 * @return string $content The filtered post content
	 */
	function _parse_blockquotes_in_paras( $content ) {

		// make sure we strip leading br
		$content = str_replace(
			'<br />' . "\n" . '<span class="blockquote-in-para">',
			"\n" . '<span class="blockquote-in-para">',
			$content
		);

		// analyse
		preg_match_all( '#(<span class="blockquote-in-para">(.*?)</span>)<br />#si', $content, $matches );

		// did we get any?
		if ( isset( $matches[0] ) AND ! empty( $matches[0] ) ) {

			$content = str_replace(
				$matches[0],
				$matches[1],
				$content
			);

		}

		// --<
		return $content;

	}



	/**
	 * Get comments sorted by text signature and paragraph.
	 *
	 * @param int $post_ID The numeric ID of the post
	 * @return array $sorted_comments The array of comment data
	 */
	function _get_sorted_comments( $post_ID ) {

		// init return
		$sorted_comments = array();

		// get all comments
		$comments = $this->comments_all;

		// filter out any multipage comments not on this page
		$comments = $this->_multipage_comment_filter( $comments );

		// get our signatures
		$sigs = $this->parent_obj->db->get_text_sigs();

		// assign comments to text signatures
		$assigned = $this->_assign_comments( $comments, $sigs );

		// NB: $assigned is an array with sigs as keys and array of comments as value
		// it may be empty:

		// if we have any comments on the whole page
		if ( isset( $assigned['WHOLE_PAGE_OR_POST_COMMENTS'] ) ) {

			// add them first
			$sorted_comments['WHOLE_PAGE_OR_POST_COMMENTS'] = $assigned['WHOLE_PAGE_OR_POST_COMMENTS'];

		} else {

			// append empty array
			$sorted_comments['WHOLE_PAGE_OR_POST_COMMENTS'] = array();

		}

		// we must have text signatures
		if ( ! empty( $sigs ) ) {

			// then add  in the order of our text signatures
			foreach( $sigs AS $text_signature ) {

				// if we have any assigned
				if ( isset( $assigned[$text_signature] ) ) {

					// append assigned comments
					$sorted_comments[$text_signature] = $assigned[$text_signature];

				} else {

					// append empty array
					$sorted_comments[$text_signature] = array();

				}

			}

		}

		// if we have any pingbacks or trackbacks
		if ( isset( $assigned['PINGS_AND_TRACKS'] ) ) {

			// add them last
			$sorted_comments['PINGS_AND_TRACKS'] = $assigned['PINGS_AND_TRACKS'];

		} else {

			// append empty array
			$sorted_comments['PINGS_AND_TRACKS'] = array();

		}

		// --<
		return $sorted_comments;

	}



	/**
	 * Filter comments to find comments for the current page of a multipage post.
	 *
	 * @param array $comments The array of comment objects
	 * @return array $filtered The array of comments for the current page
	 */
	function _multipage_comment_filter( $comments ) {

		// access globals
		global $post, $page, $multipage;

	  	// init return
		$filtered = array();

		// kick out if no comments
		if( ! is_array( $comments ) OR empty( $comments ) ) {
			return $filtered;
		}

		// kick out if not multipage
		if( ! isset( $multipage ) OR ! $multipage ) {
			return $comments;
		}

		// now add only comments that are on this page or are page-level
		foreach ( $comments AS $comment ) {

			// if it has a text sig
			if ( ! is_null( $comment->comment_signature ) AND $comment->comment_signature != '' ) {

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
	 * Filter comments by text signature.
	 *
	 * @param array $comments The array of comment objects
	 * @param array $text_signatures The array of text signatures
	 * @param integer $confidence The confidence level of paragraph identity - default 90%
	 * @return array $assigned The array with text signatures as keys and array of comments as values
	 */
	function _assign_comments( $comments, $text_signatures, $confidence = 90 ) {

	  	// init returned array
	  	// NB: we use a very unlikely key for page-level comments: WHOLE_PAGE_OR_POST_COMMENTS
		$assigned = array();

		// kick out if no comments
		if( ! is_array( $comments ) OR empty( $comments ) ) {
			return $assigned;
		}

		// run through our comments
		foreach( $comments AS $comment ) {

			// test for empty comment text signature
			if ( ! is_null( $comment->comment_signature ) AND $comment->comment_signature != '' ) {

				// do we have an exact match in the text sigs array?
				// NB: this will work, because we're already ensuring identical sigs are made unique
				if ( in_array( $comment->comment_signature, $text_signatures ) ) {

					// yes, assign to that key
					$assigned[$comment->comment_signature][] = $comment;

				} else {

					// init possibles array
					$possibles = array();

					// find the nearest matching text signature
					foreach( $text_signatures AS $text_signature ) {

						// compare strings
						similar_text( $comment->comment_signature, $text_signature, $score );

						// add to possibles array if it passes
						if( $score >= $confidence ) { $possibles[$text_signature] = $score; }

					}

					// did we get any?
					if ( ! empty( $possibles ) ) {

						// sort them by score
						arsort( $possibles );

						// get keys
						$keys = array_keys( $possibles );

						// let's use the sig with the highest score
						$highest = array_pop( $keys );

						// assign comment to that key
						$assigned[$highest][] = $comment;

					} else {

						// set property in case we need it
						$comment->orphan = true;

						// clear text signature
						$comment->comment_signature = '';

						// is it a pingback or trackback?
						if ( $comment->comment_type == 'trackback' OR $comment->comment_type == 'pingback' ) {

							// we have one - assign to pings
							$assigned['PINGS_AND_TRACKS'][] = $comment;

						} else {

							// we have comment with no text sig - assign to page
							$assigned['WHOLE_PAGE_OR_POST_COMMENTS'][] = $comment;

						}

					}

				}

			} else {

				// is it a pingback or trackback?
				if ( $comment->comment_type == 'trackback' OR $comment->comment_type == 'pingback' ) {

					// we have one - assign to pings
					$assigned['PINGS_AND_TRACKS'][] = $comment;

				} else {

					// we have comment with no text sig - assign to page
					$assigned['WHOLE_PAGE_OR_POST_COMMENTS'][] = $comment;

				}

			}

		}

		// --<
		return $assigned;

	}



//##############################################################################



} // class ends



