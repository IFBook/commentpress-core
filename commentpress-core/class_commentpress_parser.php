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
	 * @var object $parent_obj The plugin object.
	 */
	public $parent_obj;

	/**
	 * Text signatures array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $text_signatures The text signatures array.
	 */
	public $text_signatures = [];

	/**
	 * All comments array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $comments_all The all comments array.
	 */
	public $comments_all = [];

	/**
	 * Approved comments array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $comments_approved The approved comments array.
	 */
	public $comments_approved = [];

	/**
	 * Sorted comments array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $comments_sorted The sorted comments array.
	 */
	public $comments_sorted = [];

	/**
	 * Do Not Parse flag.
	 *
	 * @see Commentpress_Core_Database->do_not_parse
	 * @since 3.8.10
	 * @access public
	 * @var bool $do_not_parse False if content is parsed, true disables parsing.
	 */
	public $do_not_parse = false;

	/**
	 * Formatter flag.
	 *
	 * @since 3.8.10
	 * @access public
	 * @var str $formatter The type of formatter ('tag', 'line' or 'block').
	 */
	public $formatter = 'tag';

	/**
	 * Block name.
	 *
	 * @since 3.8.10
	 * @access public
	 * @var str $block_name The name of the block (e.g. "paragraph", "line" etc).
	 */
	public $block_name = '';



	/**
	 * Initialises this object.
	 *
	 * @since 3.0
	 *
	 * @param object $parent_obj a reference to the parent object.
	 */
	public function __construct( $parent_obj ) {

		// Store reference to parent.
		$this->parent_obj = $parent_obj;

		// Initialise via 'wp' hook.
		add_action( 'wp', [ $this, 'initialise' ] );

	}



	/**
	 * Set up all items associated with this object.
	 *
	 * @since 3.0
	 */
	public function initialise() {

		global $post;

		// Are we skipping parsing?
		if (

			// No need to parse 404s etc.
			! is_object( $post ) OR (

				// Post types can be skipped:
				$this->parent_obj->db->option_exists( 'cp_post_types_disabled' ) AND
				in_array( $post->post_type, $this->parent_obj->db->option_get( 'cp_post_types_disabled' ) )

			) OR (

				// Individual entries can have parsing skipped when:
				$this->parent_obj->db->option_exists( 'cp_do_not_parse' ) AND
				$this->parent_obj->db->option_get( 'cp_do_not_parse' ) == 'y' AND
				$post->comment_status == 'closed' AND
				empty( $post->comment_count )

			)

		) {

			// Store for later reference.
			$this->do_not_parse = true;

			// Filter commentable status.
			add_filter( 'cp_is_commentable', '__return_false' );

		} else {

			// Filter shortcodes at source.
			add_filter( 'wp_audio_shortcode', [ $this, '_parse_audio_shortcode' ], 10, 5 );
			add_filter( 'wp_video_shortcode', [ $this, '_parse_video_shortcode' ], 10, 5 );

		}

	}



	/**
	 * If needed, destroys all items associated with this object.
	 *
	 * @since 3.0
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
	 * @since 3.0
	 *
	 * @param str $content The existing content.
	 * @return str $content The modified content.
	 */
	public function the_content( $content ) {

		// Reference our post.
		global $post;

		// Retrieve all comments and store.
		// We need this data multiple times and only need to get it once.
		$this->comments_all = $this->parent_obj->db->get_all_comments( $post->ID );

		// Are we skipping parsing?
		if ( $this->do_not_parse ) {

			// Return content unparsed.
			return $content;

		}

		// Strip out <!--shortcode--> tags.
		$content = $this->_strip_shortcodes( $content );

		// Check for our quicktag.
		$has_quicktag = $this->_has_comment_block_quicktag( $content );

		// Determine formatter.
		if ( ! $has_quicktag ) {

			// Auto-format content.

			// Get action to take (defaults to 'tag').
			$this->formatter = apply_filters( 'cp_select_content_formatter', 'tag' );

			// Set constant.
			if ( ! defined( 'COMMENTPRESS_BLOCK' ) ) define( 'COMMENTPRESS_BLOCK', $this->formatter );

		} else {

			// Set action to take.
			$this->formatter = 'block';

			// Set constant.
			if ( ! defined( 'COMMENTPRESS_BLOCK' ) ) define( 'COMMENTPRESS_BLOCK', 'block' );

		}

		// Determine "lexia" names.
		$this->lexia_set( $this->formatter );

		// Act on formatter.
		switch( $this->formatter ) {

			// For poetry.
			case 'line' :

				// Generate text signatures array.
				$this->text_signatures = $this->_generate_line_signatures( $content );

				// Only continue parsing if we have an array of sigs.
				if ( ! empty( $this->text_signatures ) ) {

					// Filter content by <br> and <br /> tags.
					$content = $this->_parse_lines( $content );

				}

				break;

			// For general prose.
			case 'tag' :

				// Generate text signatures array.
				$this->text_signatures = $this->_generate_text_signatures( $content, 'p|ul|ol' );

				// Only continue parsing if we have an array of sigs.
				if ( ! empty( $this->text_signatures ) ) {

					// Filter content by <p>, <ul> and <ol> tags.
					$content = $this->_parse_content( $content, 'p|ul|ol' );

				}

				break;

			// For blocks
			case 'block' :

				// Generate text signatures array.
				$this->text_signatures = $this->_generate_block_signatures( $content );

				// Only parse content if we have an array of sigs.
				if ( ! empty( $this->text_signatures ) ) {

					// Filter content by <!--commentblock--> quicktags.
					$content = $this->_parse_blocks( $content );

				}

				break;

		}

		// Store text sigs.
		$this->parent_obj->db->set_text_sigs( $this->text_signatures );

		// --<
		return $content;

	}



	/**
	 * Get comments sorted by text signature and paragraph.
	 *
	 * @since 3.0
	 *
	 * @param int $post_ID The numeric ID of the post.
	 * @return array $comments The sorted comments array.
	 */
	public function get_sorted_comments( $post_ID ) {

		// Have we already sorted the comments?
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
	 * @param str $formatter The formatter.
	 */
	public function lexia_set( $formatter ) {

		// Set block identifier.
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
		 * @param str $block_name The existing name of the block.
		 * @param str $block_name The type of block.
		 * @return str $block_name The modified name of the block.
		 */
		$this->block_name = apply_filters( 'commentpress_lexia_block_name', $block_name, $formatter );

	}



	/**
	 * Get the name of the "block" for paragraphs, blocks or lines.
	 *
	 * @since 3.8.10
	 *
	 * @return str $block_name The name of the block.
	 */
	public function lexia_get() {

		// Return existing property.
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
	 * @since 3.0
	 */
	public function _init() {

	}



	/**
	 * Parses the content by tag.
	 *
	 * @since 3.0
	 *
	 * @param str $content The post content.
	 * @param str $tag The tag to filter by.
	 * @return str $content the parsed content.
	 */
	public function _parse_content( $content, $tag = 'p|ul|ol' ) {

		// Parse standalone captioned images.
		$content = $this->_parse_captions( $content );

		// Parse embedded quotes.
		$content = $this->_parse_blockquotes_in_paras( $content );

		// Get our paragraphs.
		$matches = $this->_get_text_matches( $content, $tag );

		// Kick out if we don't have any.
		if( ! count( $matches ) ) {
			return $content;
		}

		// Reference our post.
		global $post;

		// Get sorted comments and store.
		$this->comments_sorted = $this->_get_sorted_comments( $post->ID );

		// Init starting paragraph number.
		$start_num = 1;

		// Set key.
		$key = '_cp_starting_para_number';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {

			// Get it.
			$start_num = absint( get_post_meta( $post->ID, $key, true ) );

		}

		// We already have our text signatures, so set flag.
		$sig_key = 0;

		// Run through 'em.
		foreach( $matches AS $paragraph ) {

			// Get a signature for the paragraph.
			$text_signature = $this->text_signatures[$sig_key];

			// Construct paragraph number.
			$para_num = $sig_key + $start_num;

			// Increment.
			$sig_key++;

			// Get comment count.
			$comment_count = count( $this->comments_sorted[$text_signature] );

			// Get comment icon.
			$comment_icon = $this->parent_obj->display->get_comment_icon(
				$comment_count,
				$text_signature,
				'auto',
				$para_num
			);

			// Get paragraph icon.
			$paragraph_icon = $this->parent_obj->display->get_paragraph_icon(
				$comment_count,
				$text_signature,
				'auto',
				$para_num
			);

			// Set pattern by first tag.
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

			// Further checks when there's a <p> tag.
			if ( $tag == 'p' ) {

				// Set pattern by TinyMCE tag attribute, if we have one.
				if ( substr( $paragraph, 0 , 17 ) == '<p style="text-al' ) {

					// Test for left.
					if ( substr( $paragraph, 0 , 27 ) == '<p style="text-align:left;"' ) {
						$tag = 'p style="text-align:left;"';
					} elseif ( substr( $paragraph, 0 , 26 ) == '<p style="text-align:left"' ) {
						$tag = 'p style="text-align:left"';
					} elseif ( substr( $paragraph, 0 , 28 ) == '<p style="text-align: left;"' ) {
						$tag = 'p style="text-align: left;"';
					} elseif ( substr( $paragraph, 0 , 27 ) == '<p style="text-align: left"' ) {
						$tag = 'p style="text-align: left"';
					}

					// Test for right.
					if ( substr( $paragraph, 0 , 28 ) == '<p style="text-align:right;"' ) {
						$tag = 'p style="text-align:right;"';
					} elseif ( substr( $paragraph, 0 , 27 ) == '<p style="text-align:right"' ) {
						$tag = 'p style="text-align:right"';
					} elseif ( substr( $paragraph, 0 , 29 ) == '<p style="text-align: right;"' ) {
						$tag = 'p style="text-align: right;"';
					} elseif ( substr( $paragraph, 0 , 28 ) == '<p style="text-align: right"' ) {
						$tag = 'p style="text-align: right"';
					}

					// Test for center.
					if ( substr( $paragraph, 0 , 29 ) == '<p style="text-align:center;"' ) {
						$tag = 'p style="text-align:center;"';
					} elseif ( substr( $paragraph, 0 , 28 ) == '<p style="text-align:center"' ) {
						$tag = 'p style="text-align:center"';
					} elseif ( substr( $paragraph, 0 , 30 ) == '<p style="text-align: center;"' ) {
						$tag = 'p style="text-align: center;"';
					} elseif ( substr( $paragraph, 0 , 29 ) == '<p style="text-align: center"' ) {
						$tag = 'p style="text-align: center"';
					}

					// Test for justify.
					if ( substr( $paragraph, 0 , 30 ) == '<p style="text-align:justify;"' ) {
						$tag = 'p style="text-align:justify;"';
					} elseif ( substr( $paragraph, 0 , 29 ) == '<p style="text-align:justify"' ) {
						$tag = 'p style="text-align:justify"';
					} elseif ( substr( $paragraph, 0 , 31 ) == '<p style="text-align: justify;"' ) {
						$tag = 'p style="text-align: justify;"';
					} elseif ( substr( $paragraph, 0 , 30 ) == '<p style="text-align: justify"' ) {
						$tag = 'p style="text-align: justify"';
					}

				} // End check for text-align.

				// Test for Simple Footnotes para "heading".
				if ( substr( $paragraph, 0 , 16 ) == '<p class="notes"' ) {
					$tag = 'p class="notes"';
				}

				// If we fall through to here, treat it like it's just a <p> tag above.
				// This will fail if there are custom attributes set in the HTML editor,
				// but I'm not sure how to handle that without migrating to an XML parser.

			}

			/*
			--------------------------------------------------------------------
			NOTES
			--------------------------------------------------------------------

			There are also flaws with parsing nested lists, both ordered and unordered. The WordPress
			Unit Tests XML file reveals these, though the docs are hopefully clear enough that people
			won't use nested lists. However, the severity is such that I'm contemplating migrating to
			a DOM parser such as:

			phpQuery <https://github.com/c-harris/phpquery>
			QueryPath <https://github.com/technosophos/querypath>
			QuipXml <https://github.com/wittiws/quipxml>

			There are so many examples of people saying "don't use regex with HTML" that this probably
			ought to be done when time allows.

			--------------------------------------------------------------------
			*/

			// Init start (for ol attribute).
			$start = 0;

			// Further checks when there's a <ol> tag.
			if ( $tag == 'ol' ) {

				// Compat with WP Footnotes.
				if ( substr( $paragraph, 0 , 21 ) == '<ol class="footnotes"' ) {

					// Construct tag.
					$tag = 'ol class="footnotes"';

				// Add support for <ol start="n">.
				} elseif ( substr( $paragraph, 0 , 11 ) == '<ol start="' ) {

					// Parse tag.
					preg_match( '/start="([^"]*)"/i', $paragraph, $matches );

					// Construct new tag.
					$tag = 'ol ' . $matches[0];

					// Set start.
					$start = $matches[1];

				}

			}

			// Assign icons to paras.
			$pattern = [ '#<(' . $tag . '[^a^r>]*)>#' ];

			$replace = [
				$this->parent_obj->display->get_para_tag(
					$text_signature,
					$paragraph_icon . $comment_icon,
					$tag,
					$start
				)
			];

			$block = preg_replace( $pattern, $replace, $paragraph );

			// NB: because str_replace() has no limit to the replacements, I am switching to
			// preg_replace() because that does have a limit.
			//$content = str_replace( $paragraph, $block, $content );

			// Prepare paragraph for preg_replace.
			$prepared_para = preg_quote( $paragraph );

			// Because we use / as the delimiter, we need to escape all /s.
			$prepared_para = str_replace( '/', '\/', $prepared_para );

			// Protect all dollar numbers.
			$block = str_replace( "$", "\\\$", $block );

			// Only once please.
			$limit = 1;

			// Replace the paragraph in the original context, preserving all other content.
			$content = preg_replace(
				//[ $paragraph ],
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
	 * @since 3.0
	 *
	 * @param str $content The post content.
	 * @param str $tag The tag to filter by.
	 * @return array $matches The ordered array of matched items.
	 */
	public function _get_text_matches( $content, $tag = 'p|ul|ol' ) {

		// Filter out embedded tweets.
		$content = $this->_filter_twitter_embeds( $content );

		/*
		 * Get our paragraphs.
		 *
		 * This is needed to split regex into two strings since some IDEs don't
		 * like PHP closing tags, even they are part of a regex and not actually
		 * closing tags at all.
		 */
		//preg_match_all( '/<(' . $tag . ')[^>]*>(.*?)(<\/(' . $tag . ')>)/', $content, $matches );
		preg_match_all( '#<(' . $tag . ')[^>]*?' . '>(.*?)</(' . $tag . ')>#si', $content, $matches );

		// Kick out if we don't have any.
		if( ! empty($matches[0]) ) {

			// --<
			return $matches[0];

		} else {

			// --<
			return [];

		}

	}



	/**
	 * Parses the content by tag and builds text signatures array.
	 *
	 * @since 3.0
	 *
	 * @param str $content The post content.
	 * @param str $tag The tag to filter by.
	 * @return array $text_signatures The ordered array of text signatures.
	 */
	public function _generate_text_signatures( $content, $tag = 'p|ul|ol' ) {

		// Don't filter if a password is required.
		if ( post_password_required() ) {

			// Store text sigs array in global.
			$this->parent_obj->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;

		}

		// Parse standalone captioned images.
		$content = $this->_parse_captions( $content );

		// Get our paragraphs.
		$matches = $this->_get_text_matches( $content, $tag );

		// Kick out if we don't have any.
		if( ! count( $matches ) ) {

			// Store text sigs array in global.
			$this->parent_obj->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;

		}

		// Init ( [ 'text_signature' => n ], where n is the number of duplicates ).
		$duplicates = [];

		// Run through 'em.
		foreach( $matches AS $paragraph ) {

			// Get a signature for the paragraph.
			$text_signature = $this->_generate_text_signature( $paragraph );

			// Do we have one already?
			if ( in_array( $text_signature, $this->text_signatures ) ) {

				// Is it in the duplicates array?
				if ( array_key_exists( $text_signature, $duplicates ) ) {

					// Add one.
					$duplicates[$text_signature]++;

				} else {

					// Add it.
					$duplicates[$text_signature] = 1;

				}

				// Add number to end of text sig.
				$text_signature .= '_' . $duplicates[$text_signature];

			}

			// Add to signatures array.
			$this->text_signatures[] = $text_signature;

		}

		// Store text sigs array in global.
		$this->parent_obj->db->set_text_sigs( $this->text_signatures );

		// --<
		return $this->text_signatures;

	}



	/**
	 * Parse the content by line (<br />).
	 *
	 * @since 3.0
	 *
	 * @param str $content The post content.
	 * @return str $content The parsed content.
	 */
	public function _parse_lines( $content ) {

		// Parse standalone captioned images.
		$content = $this->_parse_captions( $content );

		// Get our lines.
		$matches = $this->_get_line_matches( $content );

		// Kick out if we don't have any.
		if( ! count( $matches ) ) {
			return $content;
		}

		// Reference our post.
		global $post;

		// Get sorted comments and store.
		$this->comments_sorted = $this->_get_sorted_comments( $post->ID );

		// Init starting paragraph number.
		$start_num = 1;

		// Set key.
		$key = '_cp_starting_para_number';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {

			// Get it.
			$start_num = absint( get_post_meta( $post->ID, $key, true ) );

		}

		// We already have our text signatures, so set flag.
		$sig_key = 0;

		// Init our content array.
		$content_array = [];

		// Run through 'em.
		foreach( $matches AS $line ) {

			// Is there any content?
			if ( $line != '' ) {

				// Check for paras.
				if ( $line == '<p>' OR $line == '</p>' ) {

					// Do we want to allow commenting on verses?

					// Add to content array.
					$content_array[] = $line;

				} else {

					// Line commenting.

					// Get a signature for the line.
					$text_signature = $this->text_signatures[$sig_key];

					// Construct paragraph number.
					$para_num = $sig_key + $start_num;

					// Increment.
					$sig_key++;

					// Get comment count.
					// NB: the sorted array contains whole page as key 0, so we use the incremented value.
					$comment_count = count( $this->comments_sorted[$text_signature] );

					// Get paragraph icon.
					$paragraph_icon = $this->parent_obj->display->get_paragraph_icon(
						$comment_count,
						$text_signature,
						'line',
						$para_num
					);

					// Get opening tag markup for this line.
					$opening_tag = $this->parent_obj->display->get_para_tag(
						$text_signature,
						$paragraph_icon,
						'span'
					);

					// Assign opening tag markup to line.
					$line = $opening_tag . $line;

					// Get comment icon.
					$comment_icon = $this->parent_obj->display->get_comment_icon(
						$comment_count,
						$text_signature,
						'line',
						$para_num
					);

					// Replace inline html comment with comment_icon.
					$line = str_replace( '<!-- line-end -->', ' ' . $comment_icon, $line );

					// Add to content array.
					$content_array[] = $line;

				}

			}

		}

		// Rejoin and exclude quicktag.
		$content = implode( '', $content_array );

		// --<
		return $content;

	}



	/**
	 * Splits the content into an array by line.
	 *
	 * @since 3.0
	 *
	 * @param str $content The post content.
	 * @return array $output_array The ordered array of matched items.
	 */
	public function _get_line_matches( $content ) {

		// Filter out embedded tweets.
		$content = $this->_filter_twitter_embeds( $content );

		// Wrap all lines with spans.

		// Get all instances.
		$pattern = [
			'/<br>/',
			'/<br\/>/',
			'/<br \/>/',
			'/<br>\n/',
			'/<br\/>\n/',
			'/<br \/>\n/',
			'/<p>/',
			'/<\/p>/',
		];

		// Define replacements.
		$replace = [
			'<!-- line-end --></span><br>',
			'<!-- line-end --></span><br/>',
			'<!-- line-end --></span><br />',
			'<br>' . "\n" . '<span class="cp-line">',
			'<br/>' . "\n" . '<span class="cp-line">',
			'<br />' . "\n" . '<span class="cp-line">',
			'<p><span class="cp-line">',
			'<!-- line-end --></span></p>',
		];

		// Do replacement.
		$content = preg_replace( $pattern, $replace, $content );

		// Explode by <span>.
		$output_array = explode( '<span class="cp-line">', $content );

		// Kick out if we have an empty array.
		if ( empty( $output_array ) ) {
			return [];
		}

		// --<
		return $output_array;

	}



	/**
	 * Parses the content by line (<br />) and builds text signatures array.
	 *
	 * @since 3.0
	 *
	 * @param str $content The post content.
	 * @return array $text_signatures The ordered array of text signatures.
	 */
	public function _generate_line_signatures( $content ) {

		// Don't filter if a password is required.
		if ( post_password_required() ) {

			// Store text sigs array in global.
			$this->parent_obj->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;

		}

		// Wrap all lines with spans.

		// Parse standalone captioned images.
		$content = $this->_parse_captions( $content );

		// Explode by <span>.
		$output_array = $this->_get_line_matches( $content );

		// Kick out if we have an empty array.
		if ( empty( $output_array ) ) {

			// Store text sigs array in global.
			$this->parent_obj->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;

		}

		// Reference our post.
		global $post;

		// Init our content array.
		$content_array = [];

		// Init ( [ 'text_signature' => n ], where n is the number of duplicates ).
		$duplicates = [];

		// Run through 'em.
		foreach( $output_array AS $paragraph ) {

			// Is there any content?
			if ( $paragraph != '' ) {

				// Check for paras.
				if ( $paragraph == '<p>' OR $paragraph == '</p>' ) {

					// Do we want to allow commenting on verses?

				} else {

					// Line commenting.

					// Get a signature for the paragraph.
					$text_signature = $this->_generate_text_signature( $paragraph );

					// Do we have one already?
					if ( in_array( $text_signature, $this->text_signatures ) ) {

						// Is it in the duplicates array?
						if ( array_key_exists( $text_signature, $duplicates ) ) {

							// Add one.
							$duplicates[$text_signature]++;

						} else {

							// Add it.
							$duplicates[$text_signature] = 1;

						}

						// Add number to end of text sig.
						$text_signature .= '_' . $duplicates[$text_signature];

					}

					// Add to signatures array.
					$this->text_signatures[] = $text_signature;

				}

			}

		}

		// Store text sigs array in global.
		$this->parent_obj->db->set_text_sigs( $this->text_signatures );

		// --<
		return $this->text_signatures;

	}



	/**
	 * Parses the content by comment block.
	 *
	 * @since 3.0
	 *
	 * @param str $content The post content.
	 * @return str $content The parsed content.
	 */
	public function _parse_blocks( $content ) {

		// Parse standalone captioned images.
		$content = $this->_parse_captions( $content );

		// Get our lines.
		$matches = $this->_get_block_matches( $content );

		// Kick out if we don't have any.
		if( ! count( $matches ) ) {
			return $content;
		}

		// Reference our post.
		global $post;

		// Get sorted comments and store.
		$this->comments_sorted = $this->_get_sorted_comments( $post->ID );

		// Init starting paragraph number.
		$start_num = 1;

		// Set key.
		$key = '_cp_starting_para_number';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {

			// Get it.
			$start_num = absint( get_post_meta( $post->ID, $key, true ) );

		}

		// We already have our text signatures, so set flag.
		$sig_key = 0;

		// Init content array.
		$content_array = [];

		// Run through 'em.
		foreach( $matches AS $paragraph ) {

			// Is there any content?
			if ( $paragraph != '' ) {

				// Get a signature for the paragraph.
				$text_signature = $this->text_signatures[$sig_key];

				// Construct paragraph number.
				$para_num = $sig_key + $start_num;

				// Increment.
				$sig_key++;

				// Get comment count.
				// NB: the sorted array contains whole page as key 0, so we use the incremented value
				$comment_count = count( $this->comments_sorted[$text_signature] );

				// Get comment icon.
				$comment_icon = $this->parent_obj->display->get_comment_icon(
					$comment_count,
					$text_signature,
					'block',
					$para_num
				);

				// Get paragraph icon.
				$paragraph_icon = $this->parent_obj->display->get_paragraph_icon(
					$comment_count,
					$text_signature,
					'block',
					$para_num
				);

				// Get comment icon markup.
				$icon_html = $this->parent_obj->display->get_para_tag(
					$text_signature,
					$paragraph_icon . $comment_icon,
					'div'
				);

				// Assign icons to blocks.
				$paragraph = $icon_html . $paragraph . '</div>' . "\n\n\n\n";

				// Add to content array.
				$content_array[] = $paragraph;

			}

		}

		// Rejoin and exclude quicktag.
		$content = implode( '', $content_array );

		// --<
		return $content;

	}



	/**
	 * Splits the content into an array by block.
	 *
	 * @since 3.0
	 *
	 * @param str $content The post content.
	 * @return array $output_array The ordered array of matched items.
	 */
	public function _get_block_matches( $content ) {

		// Filter out embedded tweets.
		$content = $this->_filter_twitter_embeds( $content );

		// Wp_texturize() does an okay job with creating paragraphs, but comments tend
		// to screw things up. let's try and fix:

		// First, replace all instances of '   <!--commentblock-->   ' with
		// '<p><!--commentblock--></p>\n'
		$content = preg_replace(
			'/\s+<!--commentblock-->\s+/',
			'<p><!--commentblock--></p>' . "\n",
			$content
		);

		// Next, replace all instances of '<p><!--commentblock-->fengfnefe' with
		// '<p><!--commentblock--></p>\n<p>fengfnefe'
		$content = preg_replace(
			'/<p><!--commentblock-->/',
			'<p><!--commentblock--></p>' . "\n" . '<p>',
			$content
		);

		// Next, replace all instances of 'fengfnefe<!--commentblock--></p>' with
		// 'fengfnefe</p>\n<p><!--commentblock--></p>'
		$content = preg_replace(
			'/<!--commentblock--><\/p>/',
			'</p>' . "\n" . '<p><!--commentblock--></p>' . "\n",
			$content
		);

		// Replace all instances of '<br />\n<!--commentblock--><br />\n' with
		// '</p>\n<p><!--commentblock--></p>\n<p>'
		$content = preg_replace(
			'/<br \/>\s+<!--commentblock--><br \/>\s+/',
			'</p>' . "\n" . '<p><!--commentblock--></p>' . "\n" . '<p>',
			$content
		);

		// Next, replace all instances of '<br />\n<!--commentblock--></p>\n' with
		// '</p>\n<p><!--commentblock--></p>\n<p>'
		$content = preg_replace(
			'/<br \/>\s+<!--commentblock--><\/p>\s+/',
			'</p>' . "\n" . '<p><!--commentblock--></p>' . "\n",
			$content
		);

		// Next, replace all instances of '<p><!--commentblock--><br />\n' with
		// '<p><!--commentblock--></p>\n<p>'
		$content = preg_replace(
			'/<p><!--commentblock--><br \/>\s+/',
			'<p><!--commentblock--></p>' . "\n" . '<p>',
			$content
		);

		// Repair some oddities: empty newlines with whitespace after:
		$content = preg_replace(
			'/<p><br \/>\s+/',
			'<p>',
			$content
		);

		// Repair some oddities: empty newlines without whitespace after:
		$content = preg_replace(
			'/<p><br \/>/',
			'<p>',
			$content
		);

		// Repair some oddities: empty paragraphs with whitespace inside:
		$content = preg_replace(
			'/<p>\s+<\/p>\s+/',
			'',
			$content
		);

		// Repair some oddities: empty paragraphs without whitespace inside:
		$content = preg_replace(
			'/<p><\/p>\s+/',
			'',
			$content
		);

		// Repair some oddities: any remaining empty paragraphs:
		$content = preg_replace(
			'/<p><\/p>/',
			'',
			$content
		);

		// Explode by <p> version to temp array.
		$output_array = explode( '<p><' . '!--commentblock--></p>', $content );

		// Kick out if we have an empty array.
		if ( empty( $output_array ) ) {
			return [];
		}

		// --<
		return $output_array;

	}



	/**
	 * Parses the content by comment block and generates text signature array.
	 *
	 * @since 3.0
	 *
	 * @param str $content The post content.
	 * @return array $text_signatures The ordered array of text signatures.
	 */
	public function _generate_block_signatures( $content ) {

		// Don't filter if a password is required.
		if ( post_password_required() ) {

			// Store text sigs array in global.
			$this->parent_obj->db->set_text_sigs( $this->text_signatures );

			// --<
			return $this->text_signatures;

		}

		// Parse standalone captioned images.
		$content = $this->_parse_captions( $content );

		// Get blocks array.
		$matches = $this->_get_block_matches( $content );

		// Init ( [ 'text_signature' => n ], where n is the number of duplicates ).
		$duplicates = [];

		// Run through 'em.
		foreach( $matches AS $paragraph ) {

			// Is there any content?
			if ( $paragraph != '' ) {

				// Get a signature for the paragraph.
				$text_signature = $this->_generate_text_signature( $paragraph );

				// Do we have one already?
				if ( in_array( $text_signature, $this->text_signatures ) ) {

					// Is it in the duplicates array?
					if ( array_key_exists( $text_signature, $duplicates ) ) {

						// Add one.
						$duplicates[$text_signature]++;

					} else {

						// Add it.
						$duplicates[$text_signature] = 1;

					}

					// Add number to end of text sig.
					$text_signature .= '_' . $duplicates[$text_signature];

				}

				// Add to signatures array.
				$this->text_signatures[] = $text_signature;

			}

		}

		// Store text sigs array in global.
		$this->parent_obj->db->set_text_sigs( $this->text_signatures );

		// --<
		return $this->text_signatures;

	}



	/**
	 * Utility to check if the content has our custom quicktag.
	 *
	 * @since 3.0
	 *
	 * @param str $content The post content.
	 * @return str $content The modified post content.
	 */
	public function _has_comment_block_quicktag( $content ) {

		// Init.
		$return = false;

		// Look for < !--commentblock--> comment.
		if ( strstr( $content, '<!--commentblock-->' ) !== false ) {

			// Yep.
			$return = true;

		}

		// --<
		return $return;

	}



	/**
	 * Utility to remove our custom quicktag.
	 *
	 * @since 3.0
	 *
	 * @param str $content The post content.
	 * @return str $content The modified post content.
	 */
	public function _strip_comment_block_quicktag( $content ) {

		// Look for < !--commentblock--> comment
		if ( preg_match('/<' . '!--commentblock--><br \/>/', $content, $matches) ) {

			// Derive list.
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude shortcode.
			$content = implode( '', $content );

		}

		// Look for < !--commentblock--> comment
		if ( preg_match('/<p><' . '!--commentblock--><\/p>/', $content, $matches) ) {

			// Derive list.
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude shortcode.
			$content = implode( '', $content );

		}

		// --<
		return $content;

	}



	/**
	 * Utility to strip out shortcodes from content otherwise they get formatting.
	 *
	 * @since 3.0
	 *
	 * @param str $content The post content.
	 * @return str $content The modified post content.
	 */
	public function _strip_shortcodes( $content ) {

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

		// Look for inline <!--more--> span.
		if ( preg_match('/<span id="more-(.*?)?' . '><\/span><br \/>/', $content, $matches) ) {

			// Derive list
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude shortcode.
			$content = implode( '', $content );

		}

		// Look for separated <!--more--> span.
		if ( preg_match('/<p><span id="more-(.*?)?' . '><\/span><\/p>/', $content, $matches) ) {

			// Derive list
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude shortcode.
			$content = implode( '', $content );

		}

		// Look for inline <!--more--> span correctly followed by <!--noteaser-->.
		if ( preg_match('/<span id="more-(.*?)?' . '><\/span><!--noteaser--><br \/>/', $content, $matches) ) {

			// Derive list
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude shortcode.
			$content = implode( '', $content );

		}

		// Look for separated <!--more--> span correctly followed by <!--noteaser-->.
		if ( preg_match('/<p><span id="more-(.*?)?' . '><\/span><!--noteaser--><\/p>/', $content, $matches) ) {

			// Derive list
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude shortcode.
			$content = implode( '', $content );

		}

		// Look for incorrectly placed inline <!--noteaser--> comment.
		if ( preg_match('/<' . '!--noteaser--><br \/>/', $content, $matches) ) {

			// Derive list
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude shortcode.
			$content = implode( '', $content );

		}

		// Look for incorrectly placed separated <!--noteaser--> comment.
		if ( preg_match('/<p><' . '!--noteaser--><\/p>/', $content, $matches) ) {

			// Derive list
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude shortcode.
			$content = implode( '', $content );

		}

		// This gets the additional text (not used).
		if ( ! empty($matches[1]) ) {
			//$more_link_text = strip_tags(wp_kses_no_null(trim($matches[1])));
		}

		// --<
		return $content;

	}



	/**
	 * Generates a text signature based on the content of a paragraph.
	 *
	 * @since 3.0
	 *
	 * @param str $text The text of a paragraph.
	 * @return str $text_signature The generated text signature.
	 *
	 */
	public function _generate_text_signature( $text ) {

		// Get an array of words from the text.
		$words = explode( ' ', preg_replace( '/[^A-Za-z]/', ' ', html_entity_decode($text) ) );

		// Store unique words
		// NB: this may be a mistake for poetry, which can use any words in any order.
		$unique_words = array_unique( $words );

		// Init sig
		$text_signature = null;

		// Run through our unique words.
		foreach( $unique_words AS $key => $word ) {

			// Add first letter.
			$text_signature .= substr( $word, 0, 1 );

			// Limit to 250 chars.
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
	 * @since 3.0
	 *
	 * @param str $content The post content.
	 * @return str $content The filtered post content.
	 */
	public function _filter_twitter_embeds( $content ) {

		// Test for a WP 3.4 function.
		if ( function_exists( 'wp_get_themes' ) ) {

			// Look for Embedded Tweet <blockquote>.
			if ( preg_match('#<(blockquote class="twitter-tweet)[^>]*?' . '>(.*?)</(blockquote)>#si', $content, $matches) ) {

				// Derive list.
				$content = explode( $matches[0], $content, 2 );

				// Rejoin to exclude from content to be parsed.
				$content = implode( '', $content );

				// Remove old twitter script.
				$content = str_replace(
					'<p><script src="//platform.twitter.com/widgets.js" charset="utf-8"></script></p>',
					'',
					$content
				);

				// Remove new twitter script.
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
	 * @since 3.8
	 *
	 * @param str $content The post content.
	 * @return str $content The filtered post content.
	 */
	public function _parse_captions( $content ) {

		// Filter captioned images that are *not* inside other tags.
		$pattern = [
			'/\n<!-- cp_caption_start -->/',
			'/<!-- cp_caption_end -->\n/',
		];

		// Define replacements.
		$replace = [
			"\n" . '<p><!-- cp_caption_start -->',
			'<!-- cp_caption_end --></p>' . "\n",
		];

		// Do replacement.
		$content = preg_replace( $pattern, $replace, $content );

		// Check for captions at the very beginning of content.
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
	public function _parse_audio_shortcode( $html, $atts, $file, $post_id, $library ) {

		// Wrap.
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
	public function _parse_video_shortcode( $html, $atts, $file, $post_id, $library ) {

		// Replace enclosing div with span.
		$html = str_replace( '<div', '<span', $html );
		$html = str_replace( '</div', '</span', $html );

		// Wrap
		return '<p><span class="cp-video-shortcode"><span></span>' . $html . '</span></p>';

	}



	/**
	 * Removes leading and trailing <br /> tags from embedded quotes.
	 *
	 * @since 3.0
	 *
	 * @param string $content The post content.
	 * @return string $content The filtered post content.
	 */
	public function _parse_blockquotes_in_paras( $content ) {

		// Make sure we strip leading br.
		$content = str_replace(
			'<br />' . "\n" . '<span class="blockquote-in-para">',
			"\n" . '<span class="blockquote-in-para">',
			$content
		);

		// Analyse.
		preg_match_all( '#(<span class="blockquote-in-para">(.*?)</span>)<br />#si', $content, $matches );

		// Did we get any?
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
	 * @since 3.0
	 *
	 * @param int $post_ID The numeric ID of the post.
	 * @return array $sorted_comments The array of comment data.
	 */
	public function _get_sorted_comments( $post_ID ) {

		// Init return.
		$sorted_comments = [];

		// Get all comments.
		$comments = $this->comments_all;

		// Filter out any multipage comments not on this page.
		$comments = $this->_multipage_comment_filter( $comments );

		// Get our signatures.
		$sigs = $this->parent_obj->db->get_text_sigs();

		// Assign comments to text signatures.
		$assigned = $this->_assign_comments( $comments, $sigs );

		// NB: $assigned is an array with sigs as keys and array of comments as value
		// it may be empty:

		// If we have any comments on the whole page.
		if ( isset( $assigned['WHOLE_PAGE_OR_POST_COMMENTS'] ) ) {

			// Add them first.
			$sorted_comments['WHOLE_PAGE_OR_POST_COMMENTS'] = $assigned['WHOLE_PAGE_OR_POST_COMMENTS'];

		} else {

			// Append empty array.
			$sorted_comments['WHOLE_PAGE_OR_POST_COMMENTS'] = [];

		}

		// We must have text signatures.
		if ( ! empty( $sigs ) ) {

			// Then add  in the order of our text signatures.
			foreach( $sigs AS $text_signature ) {

				// If we have any assigned.
				if ( isset( $assigned[$text_signature] ) ) {

					// Append assigned comments.
					$sorted_comments[$text_signature] = $assigned[$text_signature];

				} else {

					// Append empty array.
					$sorted_comments[$text_signature] = [];

				}

			}

		}

		// If we have any pingbacks or trackbacks.
		if ( isset( $assigned['PINGS_AND_TRACKS'] ) ) {

			// Add them last.
			$sorted_comments['PINGS_AND_TRACKS'] = $assigned['PINGS_AND_TRACKS'];

		} else {

			// Append empty array.
			$sorted_comments['PINGS_AND_TRACKS'] = [];

		}

		// --<
		return $sorted_comments;

	}



	/**
	 * Filter comments to find comments for the current page of a multipage post.
	 *
	 * @since 3.4
	 *
	 * @param array $comments The array of comment objects.
	 * @return array $filtered The array of comments for the current page.
	 */
	public function _multipage_comment_filter( $comments ) {

		// Access globals.
		global $post, $page, $multipage;

	  	// Init return.
		$filtered = [];

		// Kick out if no comments.
		if( ! is_array( $comments ) OR empty( $comments ) ) {
			return $filtered;
		}

		// Kick out if not multipage.
		if( ! isset( $multipage ) OR ! $multipage ) {
			return $comments;
		}

		// Now add only comments that are on this page or are page-level.
		foreach ( $comments AS $comment ) {

			// If it has a text sig.
			if ( ! is_null( $comment->comment_signature ) AND $comment->comment_signature != '' ) {

				// Set key.
				$key = '_cp_comment_page';

				// Does it have a comment meta value?
				if ( get_comment_meta( $comment->comment_ID, $key, true ) != '' ) {

					// Get the page number.
					$page_num = get_comment_meta( $comment->comment_ID, $key, true );

					// Is it the current one?
					if ( $page_num == $page ) {

						// Add it.
						$filtered[] = $comment;

					}

				}

			} else {

				// Page-level comment: add it.
				$filtered[] = $comment;

			}

		}

		// --<
		return $filtered;

	}



	/**
	 * Filter comments by text signature.
	 *
	 * @since 3.0
	 *
	 * @param array $comments The array of comment objects.
	 * @param array $text_signatures The array of text signatures.
	 * @param integer $confidence The confidence level of paragraph identity - default 90%.
	 * @return array $assigned The array with text signatures as keys and array of comments as values.
	 */
	public function _assign_comments( $comments, $text_signatures, $confidence = 90 ) {

	  	// Init returned array.
	  	// NB: we use a very unlikely key for page-level comments: WHOLE_PAGE_OR_POST_COMMENTS.
		$assigned = [];

		// Kick out if no comments.
		if( ! is_array( $comments ) OR empty( $comments ) ) {
			return $assigned;
		}

		// Run through our comments.
		foreach( $comments AS $comment ) {

			// Test for empty comment text signature.
			if ( ! is_null( $comment->comment_signature ) AND $comment->comment_signature != '' ) {

				// Do we have an exact match in the text sigs array?
				// NB: this will work, because we're already ensuring identical sigs are made unique.
				if ( in_array( $comment->comment_signature, $text_signatures ) ) {

					// Yes, assign to that key.
					$assigned[$comment->comment_signature][] = $comment;

				} else {

					// Init possibles array.
					$possibles = [];

					// Find the nearest matching text signature.
					foreach( $text_signatures AS $text_signature ) {

						// Compare strings.
						similar_text( $comment->comment_signature, $text_signature, $score );

						// Add to possibles array if it passes.
						if( $score >= $confidence ) { $possibles[$text_signature] = $score; }

					}

					// Did we get any?
					if ( ! empty( $possibles ) ) {

						// Sort them by score.
						arsort( $possibles );

						// Get keys.
						$keys = array_keys( $possibles );

						// Let's use the sig with the highest score.
						$highest = array_pop( $keys );

						// Assign comment to that key.
						$assigned[$highest][] = $comment;

					} else {

						// Set property in case we need it.
						$comment->orphan = true;

						// Clear text signature.
						$comment->comment_signature = '';

						// Is it a pingback or trackback?
						if ( $comment->comment_type == 'trackback' OR $comment->comment_type == 'pingback' ) {

							// We have one - assign to pings.
							$assigned['PINGS_AND_TRACKS'][] = $comment;

						} else {

							// We have comment with no text sig - assign to page.
							$assigned['WHOLE_PAGE_OR_POST_COMMENTS'][] = $comment;

						}

					}

				}

			} else {

				// Is it a pingback or trackback?
				if ( $comment->comment_type == 'trackback' OR $comment->comment_type == 'pingback' ) {

					// We have one - assign to pings.
					$assigned['PINGS_AND_TRACKS'][] = $comment;

				} else {

					// We have comment with no text sig - assign to page.
					$assigned['WHOLE_PAGE_OR_POST_COMMENTS'][] = $comment;

				}

			}

		}

		// --<
		return $assigned;

	}



//##############################################################################



} // Class ends.



