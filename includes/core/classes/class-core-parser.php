<?php
/**
 * CommentPress Core Parser class.
 *
 * Handles parsing content and Comments.
 *
 * @package CommentPress_Core
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * CommentPress Core Parser Class.
 *
 * This class is a wrapper for parsing content and Comments.
 *
 * The aim is to migrate parsing of content to DOM parsing instead of regex.
 * When converted to DOM parsing, this class will include two other classes,
 * which help with oddities in DOMDocument. These can be found in
 * `inc/dom-helpers` in the dom-parser branch.
 *
 * @since 3.0
 */
class CommentPress_Core_Parser {

	/**
	 * Core loader object.
	 *
	 * @since 3.0
	 * @since 4.0 Renamed.
	 * @access public
	 * @var object $core The core loader object.
	 */
	public $core;

	/**
	 * Text signatures array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $text_signatures The Text Signatures array.
	 */
	public $text_signatures = [];

	/**
	 * All Comments array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $comments_all The array of all Comments.
	 */
	public $comments_all = [];

	/**
	 * Approved Comments array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $comments_approved The approved Comments array.
	 */
	public $comments_approved = [];

	/**
	 * Sorted Comments array.
	 *
	 * @since 3.0
	 * @access public
	 * @var array $comments_sorted The sorted Comments array.
	 */
	public $comments_sorted = [];

	/**
	 * Do Not Parse flag.
	 *
	 * @see CommentPress_Core_Database->do_not_parse
	 * @since 3.8.10
	 * @access public
	 * @var bool $do_not_parse False if content is parsed, true disables parsing.
	 */
	public $do_not_parse = false;

	/**
	 * Parser type.
	 *
	 * Possible values are 'tag', 'line' or 'block'.
	 *
	 * @since 3.8.10
	 * @access public
	 * @var str $parser The type of parser.
	 */
	public $parser = 'tag';

	/**
	 * Block name.
	 *
	 * @since 3.8.10
	 * @access public
	 * @var str $block_name The name of the Block (e.g. "paragraph", "line" etc).
	 */
	public $block_name = '';

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 *
	 * @param object $core Reference to the core plugin object.
	 */
	public function __construct( $core ) {

		// Store reference to core plugin object.
		$this->core = $core;

		// Init when this plugin is fully loaded.
		add_action( 'commentpress/core/loaded', [ $this, 'initialise' ] );

	}

	/**
	 * Initialises this object.
	 *
	 * @since 4.0
	 */
	public function initialise() {

		// Register hooks.
		$this->register_hooks();

	}

	/**
	 * Register hooks.
	 *
	 * @since 4.0
	 */
	public function register_hooks() {

		// Set up items when "wp" action fires.
		add_action( 'wp', [ $this, 'setup_items' ] );

		// Modify the content after all built-in WordPress filters have run.
		// TODO: Check that this priority is right.
		add_filter( 'the_content', [ $this, 'the_content' ], 20 );

	}

	/**
	 * Set up all items associated with this object.
	 *
	 * @since 3.0
	 */
	public function setup_items() {

		global $post;

		// Are we skipping parsing?
		if (

			// No need to parse 404s etc.
			! ( $post instanceof WP_Post ) ||

			// Some Post Types can be skipped.
			in_array( $post->post_type, $this->core->db->option_get( 'cp_post_types_disabled', [] ) ) || (

			// Individual entries can have parsing skipped.
			$this->core->db->option_get( 'cp_do_not_parse', 'n' ) == 'y' &&
			$post->comment_status == 'closed' &&
			empty( $post->comment_count ) )

		) {

			// Store for later reference.
			$this->do_not_parse = true;

			// Filter commentable status.
			add_filter( 'cp_is_commentable', '__return_false' );

		} else {

			// Filter shortcodes at source.
			add_filter( 'wp_audio_shortcode', [ $this, 'filter_audio_shortcode' ], 10, 5 );
			add_filter( 'wp_video_shortcode', [ $this, 'filter_video_shortcode' ], 10, 5 );

		}

	}

	// -------------------------------------------------------------------------

	/**
	 * Checks if the current Page/Post can be commented on.
	 *
	 * @since 3.4
	 *
	 * @return bool $is_commentable True if commentable, false otherwise.
	 */
	public function is_commentable() {

		// Declare access to globals.
		global $post;

		// Not on Signup Pages.
		$script = isset( $_SERVER['SCRIPT_FILENAME'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SCRIPT_FILENAME'] ) ) : '';
		if ( is_multisite() && ! empty( $script ) ) {
			if ( 'wp-signup.php' == basename( $script ) ) {
				return false;
			}
			if ( 'wp-activate.php' == basename( $script ) ) {
				return false;
			}
		}

		// Not if there's no Post object.
		if ( ! ( $post instanceof WP_Post ) ) {
			return false;
		}

		// Not if we're not on a Page/Post.
		if ( ! is_singular() ) {
			return false;
		}

		// CommentPress Special Pages are not.
		if ( $this->core->pages_legacy->is_special_page() ) {
			return false;
		}

		// BuddyPress Special Pages are not.
		if ( $this->core->bp->is_buddypress_special_page() ) {
			return false;
		}

		/**
		 * Filters "commenting allowed" status.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Core_Plugins::is_commentable() (Priority: 10)
		 *
		 * @since 3.4
		 *
		 * @param bool $is_commentable True by default.
		 */
		return apply_filters( 'cp_is_commentable', true );

	}

	// -------------------------------------------------------------------------

	/**
	 * Parses Page/Post content.
	 *
	 * @since 3.0
	 *
	 * @param str $content The content of the Page/Post.
	 * @return str $content The modified content.
	 */
	public function the_content( $content ) {

		// Reference our Post.
		global $post;

		/*
		 * JetPack 2.7 or greater parses the content in the head to create
		 * content summaries so prevent parsing unless this is the main content.
		 */
		if ( is_admin() || ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		/**
		 * Filter for plugins to disallow parsing of content.
		 *
		 * Used internally by:
		 *
		 * * CommentPress_Core_Plugins::is_theme_my_login_page() (Priority: 10)
		 * * CommentPress_Core_Plugins::is_members_list_page() (Priority: 10)
		 * * CommentPress_Core_Plugins::is_subscribe_to_comments_reloaded_page() (Priority: 10)
		 *
		 * @since 4.0
		 *
		 * @param bool False by default. Return true to skip parsing.
		 */
		if ( apply_filters( 'commentpress/core/parser/the_content/skip', false ) ) {
			return $content;
		}

		// Test for BuddyPress Special Page (compat with BuddyPress Docs).
		if ( $this->core->bp->is_buddypress() ) {

			// Do not parse a component homepage.
			if ( $this->core->bp->is_buddypress_special_page() ) {
				return $content;
			}

		}

		// Init allowed.
		$allowed = false;

		// Only parse Posts or Pages.
		if ( ( is_single() || is_page() || is_attachment() ) && ! $this->core->pages_legacy->is_special_page() ) {
			$allowed = true;
		}

		/**
		 * Parse if allowed.
		 *
		 * @since 3.0
		 *
		 * @param bool $allowed True if allowed, false otherwise.
		 */
		if ( apply_filters( 'commentpress_force_the_content', $allowed ) ) {
			$content = $this->the_content_parse( $content );
		}

		// --<
		return $content;

	}

	// -------------------------------------------------------------------------

	/**
	 * Intercept content and modify based on Paragraphs, Blocks or Lines.
	 *
	 * @since 3.0
	 *
	 * @param str $content The existing content.
	 * @return str $content The modified content.
	 */
	public function the_content_parse( $content ) {

		// Reference our Post.
		global $post;

		// Retrieve all Comments and store.
		// We need this data multiple times and only need to get it once.
		$this->comments_all = $this->comments_for_post_get_all( $post->ID );

		// Are we skipping parsing?
		if ( $this->do_not_parse ) {

			// Return content unparsed.
			return $content;

		}

		// Strip built-in Quicktags.
		$content = $this->filter_quicktags( $content );

		// Check for our "Comment Block" Quicktag.
		$has_quicktag = $this->core->editor_content->has_quicktag( $content );

		// Determine parser.
		if ( ! $has_quicktag ) {

			/**
			 * Filters the default parser.
			 *
			 * When we auto-format content, we default to 'tag'.
			 *
			 * @since 4.0
			 *
			 * @param str $parser The type of content parser to use.
			 */
			$this->parser = apply_filters( 'commentpress/core/parser/content/parser', 'tag' );

		} else {

			// Must set to Block parser.
			$this->parser = 'block';

		}

		// Determine "lexia" names.
		$this->lexia_set( $this->parser );

		// Act on parser.
		switch ( $this->parser ) {

			// For poetry.
			case 'line':

				// Generate Text Signatures array.
				$this->text_signatures = $this->line_signatures_generate( $content );

				// Only continue parsing if we have an array of sigs.
				if ( ! empty( $this->text_signatures ) ) {

					// Filter content by <br> and <br /> tags.
					$content = $this->lines_parse( $content );

				}

				break;

			// For general prose.
			case 'tag':

				// Generate Text Signatures array.
				$this->text_signatures = $this->tag_signatures_generate( $content, 'p|ul|ol' );

				// Only continue parsing if we have an array of sigs.
				if ( ! empty( $this->text_signatures ) ) {

					// Filter content by <p>, <ul> and <ol> tags.
					$content = $this->tags_parse( $content, 'p|ul|ol' );

				}

				break;

			// For Blocks.
			case 'block':

				// Generate Text Signatures array.
				$this->text_signatures = $this->block_signatures_generate( $content );

				// Only parse content if we have an array of sigs.
				if ( ! empty( $this->text_signatures ) ) {

					// Filter content by <!--commentblock--> quicktags.
					$content = $this->blocks_parse( $content );

				}

				break;

		}

		// Store Text Signatures.
		$this->text_signatures_set( $this->text_signatures );

		// --<
		return $content;

	}

	// -------------------------------------------------------------------------

	/**
	 * Store the name of the "block" for Paragraphs, Blocks or Lines.
	 *
	 * @since 3.8.10
	 *
	 * @param str $parser The type of content parser.
	 */
	public function lexia_set( $parser ) {

		// Set Block identifier.
		switch ( $parser ) {

			case 'block':
				$block_name = __( 'block', 'commentpress-core' );
				break;

			case 'line':
				$block_name = __( 'line', 'commentpress-core' );
				break;

			case 'tag':
			default:
				$block_name = __( 'paragraph', 'commentpress-core' );
				break;

		}

		/**
		 * Filters the name of the "block" for Paragraphs, Blocks or Lines.
		 *
		 * @since 3.8.10
		 *
		 * @param str $block_name The existing name of the Block.
		 * @param str $parser The type of content parser.
		 * @return str $block_name The modified name of the Block.
		 */
		$this->block_name = apply_filters( 'commentpress_lexia_block_name', $block_name, $parser );

	}

	/**
	 * Get the name of the "block" for Paragraphs, Blocks or Lines.
	 *
	 * @since 3.8.10
	 *
	 * @return str $block_name The name of the Block.
	 */
	public function lexia_get() {

		// Return existing property.
		return $this->block_name;

	}

	// -------------------------------------------------------------------------

	/**
	 * Parses the content by tag.
	 *
	 * @since 3.0
	 *
	 * @param str $content The Post content.
	 * @param str $tag The tag to filter by.
	 * @return str $content the parsed content.
	 */
	private function tags_parse( $content, $tag = 'p|ul|ol' ) {

		// Filter standalone captioned images.
		$content = $this->filter_captions( $content );

		// Filter embedded quotes.
		$content = $this->filter_blockquotes_in_paras( $content );

		// Get our Paragraphs.
		$matches = $this->tag_matches_get( $content, $tag );

		// Kick out if we don't have any.
		if ( ! count( $matches ) ) {
			return $content;
		}

		// Reference our Post.
		global $post;

		// Get sorted Comments and store.
		$this->comments_sorted = $this->comments_sorted_build( $post->ID );

		// Init starting Paragraph Number.
		$start_num = 1;

		// Set key.
		$key = '_cp_starting_para_number';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {

			// Get it.
			$start_num = absint( get_post_meta( $post->ID, $key, true ) );

		}

		// We already have our Text Signatures, so set flag.
		$sig_key = 0;

		// Run through 'em.
		foreach ( $matches as $paragraph ) {

			// Get a signature for the Paragraph.
			$text_signature = $this->text_signatures[ $sig_key ];

			// Construct Paragraph Number.
			$para_num = $sig_key + $start_num;

			// Increment.
			$sig_key++;

			// Get Comment count.
			$comment_count = count( $this->comments_sorted[ $text_signature ] );

			// Get Comment icon.
			$comment_icon = $this->core->display->get_comment_icon(
				$comment_count,
				$text_signature,
				'auto',
				$para_num
			);

			// Get Paragraph icon.
			$paragraph_icon = $this->core->display->get_paragraph_icon(
				$comment_count,
				$text_signature,
				'auto',
				$para_num
			);

			// Set pattern by first tag.
			switch ( substr( $paragraph, 0, 2 ) ) {
				case '<p':
					$tag = 'p';
					break;
				case '<o':
					$tag = 'ol';
					break;
				case '<u':
					$tag = 'ul';
					break;
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
				if ( substr( $paragraph, 0, 17 ) == '<p style="text-al' ) {

					// Test for left.
					if ( substr( $paragraph, 0, 27 ) == '<p style="text-align:left;"' ) {
						$tag = 'p style="text-align:left;"';
					} elseif ( substr( $paragraph, 0, 26 ) == '<p style="text-align:left"' ) {
						$tag = 'p style="text-align:left"';
					} elseif ( substr( $paragraph, 0, 28 ) == '<p style="text-align: left;"' ) {
						$tag = 'p style="text-align: left;"';
					} elseif ( substr( $paragraph, 0, 27 ) == '<p style="text-align: left"' ) {
						$tag = 'p style="text-align: left"';
					}

					// Test for right.
					if ( substr( $paragraph, 0, 28 ) == '<p style="text-align:right;"' ) {
						$tag = 'p style="text-align:right;"';
					} elseif ( substr( $paragraph, 0, 27 ) == '<p style="text-align:right"' ) {
						$tag = 'p style="text-align:right"';
					} elseif ( substr( $paragraph, 0, 29 ) == '<p style="text-align: right;"' ) {
						$tag = 'p style="text-align: right;"';
					} elseif ( substr( $paragraph, 0, 28 ) == '<p style="text-align: right"' ) {
						$tag = 'p style="text-align: right"';
					}

					// Test for center.
					if ( substr( $paragraph, 0, 29 ) == '<p style="text-align:center;"' ) {
						$tag = 'p style="text-align:center;"';
					} elseif ( substr( $paragraph, 0, 28 ) == '<p style="text-align:center"' ) {
						$tag = 'p style="text-align:center"';
					} elseif ( substr( $paragraph, 0, 30 ) == '<p style="text-align: center;"' ) {
						$tag = 'p style="text-align: center;"';
					} elseif ( substr( $paragraph, 0, 29 ) == '<p style="text-align: center"' ) {
						$tag = 'p style="text-align: center"';
					}

					// Test for justify.
					if ( substr( $paragraph, 0, 30 ) == '<p style="text-align:justify;"' ) {
						$tag = 'p style="text-align:justify;"';
					} elseif ( substr( $paragraph, 0, 29 ) == '<p style="text-align:justify"' ) {
						$tag = 'p style="text-align:justify"';
					} elseif ( substr( $paragraph, 0, 31 ) == '<p style="text-align: justify;"' ) {
						$tag = 'p style="text-align: justify;"';
					} elseif ( substr( $paragraph, 0, 30 ) == '<p style="text-align: justify"' ) {
						$tag = 'p style="text-align: justify"';
					}

				} // End check for text-align.

				// Test for Simple Footnotes para "heading".
				if ( substr( $paragraph, 0, 16 ) == '<p class="notes"' ) {
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

				// Compat with "WP Footnotes".
				if ( substr( $paragraph, 0, 21 ) == '<ol class="footnotes"' ) {

					// Construct tag.
					$tag = 'ol class="footnotes"';

				// Add support for <ol start="n">.
				} elseif ( substr( $paragraph, 0, 11 ) == '<ol start="' ) {

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
				$this->core->display->get_para_tag(
					$text_signature,
					$paragraph_icon . $comment_icon,
					$tag,
					$start
				),
			];

			$block = preg_replace( $pattern, $replace, $paragraph );

			// Protect all dollar numbers.
			$block = str_replace( '$', '\\$', $block );

			/*
			 * NOTE: Because "str_replace" has no limit to the replacements, I am
			 * switching to "preg_replace" because that does have a limit.
			 *
			 * $content = str_replace( $paragraph, $block, $content );
			 *
			 * This prevents repeated Paragraphs from being blanket replaced.
			 */

			// Prepare Paragraph for preg_replace.
			$prepared_para = preg_quote( $paragraph, '/' );

			// Only once please.
			$limit = 1;

			// Replace the Paragraph in the original context, preserving all other content.
			$content = preg_replace(
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
	 * @param str $content The Post content.
	 * @param str $tag The tag to filter by.
	 * @return array $matches The ordered array of matched items.
	 */
	private function tag_matches_get( $content, $tag = 'p|ul|ol' ) {

		// Filter out embedded Tweets.
		$filtered = $this->filter_twitter_embeds( $content );

		// Filter out Sharedaddy markup.
		$filtered = $this->filter_jetpack_sharing( $filtered );

		/*
		 * Get our Paragraphs.
		 *
		 * This is needed to split regex into two strings since some IDEs don't
		 * like PHP closing tags, even they are part of a regex and not actually
		 * closing tags at all.
		 */
		preg_match_all( '#<(' . $tag . ')[^>]*?' . '>(.*?)</(' . $tag . ')>#si', $filtered, $matches );

		// If we get matches, return them.
		if ( ! empty( $matches[0] ) ) {
			return $matches[0];
		}

		// --<
		return [];

	}

	// -------------------------------------------------------------------------

	/**
	 * Parses the content by tag and builds Text Signatures array.
	 *
	 * @since 3.0
	 *
	 * @param str $content The Post content.
	 * @param str $tag The tag to filter by.
	 * @return array $text_signatures The ordered array of Text Signatures.
	 */
	private function tag_signatures_generate( $content, $tag = 'p|ul|ol' ) {

		// Don't filter if a password is required.
		if ( post_password_required() ) {

			// Store Text Signatures array in global.
			$this->text_signatures_set( $this->text_signatures );

			// --<
			return $this->text_signatures;

		}

		// Filter standalone captioned images.
		$content = $this->filter_captions( $content );

		// Get our Paragraphs.
		$matches = $this->tag_matches_get( $content, $tag );

		// Kick out if we don't have any.
		if ( ! count( $matches ) ) {

			// Store Text Signatures array in global.
			$this->text_signatures_set( $this->text_signatures );

			// --<
			return $this->text_signatures;

		}

		// Init ( [ 'text_signature' => n ], where n is the number of duplicates ).
		$duplicates = [];

		// Run through 'em.
		foreach ( $matches as $paragraph ) {

			// Get a signature for the Paragraph.
			$text_signature = $this->text_signature_generate( $paragraph );

			// Do we have one already?
			if ( in_array( $text_signature, $this->text_signatures ) ) {

				// Is it in the duplicates array?
				if ( array_key_exists( $text_signature, $duplicates ) ) {

					// Add one.
					$duplicates[ $text_signature ]++;

				} else {

					// Add it.
					$duplicates[ $text_signature ] = 1;

				}

				// Add number to end of Text Signature.
				$text_signature .= '_' . $duplicates[ $text_signature ];

			}

			// Add to signatures array.
			$this->text_signatures[] = $text_signature;

		}

		// Store Text Signatures array in global.
		$this->text_signatures_set( $this->text_signatures );

		// --<
		return $this->text_signatures;

	}

	/**
	 * Parse the content by Line (<br />).
	 *
	 * @since 3.0
	 *
	 * @param str $content The Post content.
	 * @return str $content The parsed content.
	 */
	private function lines_parse( $content ) {

		// Filter standalone captioned images.
		$content = $this->filter_captions( $content );

		// Get our Lines.
		$matches = $this->line_matches_get( $content );

		// Kick out if we don't have any.
		if ( ! count( $matches ) ) {
			return $content;
		}

		// Reference our Post.
		global $post;

		// Get sorted Comments and store.
		$this->comments_sorted = $this->comments_sorted_build( $post->ID );

		// Init starting Paragraph Number.
		$start_num = 1;

		// Set key.
		$key = '_cp_starting_para_number';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {

			// Get it.
			$start_num = absint( get_post_meta( $post->ID, $key, true ) );

		}

		// We already have our Text Signatures, so set flag.
		$sig_key = 0;

		// Init our content array.
		$content_array = [];

		// Run through 'em.
		foreach ( $matches as $line ) {

			// Is there any content?
			if ( $line != '' ) {

				// Check for paras.
				if ( $line == '<p>' || $line == '</p>' ) {

					// Do we want to allow commenting on verses?

					// Add to content array.
					$content_array[] = $line;

				} else {

					// Line commenting.

					// Get a signature for the Line.
					$text_signature = $this->text_signatures[ $sig_key ];

					// Construct Paragraph Number.
					$para_num = $sig_key + $start_num;

					// Increment.
					$sig_key++;

					// Get Comment count.
					// NB: the sorted array contains "Whole Page" as key 0, so we use the incremented value.
					$comment_count = count( $this->comments_sorted[ $text_signature ] );

					// Get Paragraph icon.
					$paragraph_icon = $this->core->display->get_paragraph_icon(
						$comment_count,
						$text_signature,
						'line',
						$para_num
					);

					// Get opening tag markup for this Line.
					$opening_tag = $this->core->display->get_para_tag(
						$text_signature,
						$paragraph_icon,
						'span'
					);

					// Assign opening tag markup to Line.
					$line = $opening_tag . $line;

					// Get Comment icon.
					$comment_icon = $this->core->display->get_comment_icon(
						$comment_count,
						$text_signature,
						'line',
						$para_num
					);

					// Replace inline html Comment with comment_icon.
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
	 * Splits the content into an array by Line.
	 *
	 * @since 3.0
	 *
	 * @param str $content The Post content.
	 * @return array $output_array The ordered array of matched items.
	 */
	private function line_matches_get( $content ) {

		// Filter out embedded Tweets.
		$filtered = $this->filter_twitter_embeds( $content );

		// Filter out Sharedaddy markup.
		$filtered = $this->filter_jetpack_sharing( $filtered );

		// Wrap all Lines with spans.

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
		$filtered = preg_replace( $pattern, $replace, $filtered );

		// Explode by <span>.
		$output_array = explode( '<span class="cp-line">', $filtered );

		// Kick out if we have an empty array.
		if ( empty( $output_array ) ) {
			return [];
		}

		// --<
		return $output_array;

	}

	// -------------------------------------------------------------------------

	/**
	 * Parses the content by Line (<br />) and builds Text Signatures array.
	 *
	 * @since 3.0
	 *
	 * @param str $content The Post content.
	 * @return array $text_signatures The ordered array of Text Signatures.
	 */
	private function line_signatures_generate( $content ) {

		// Don't filter if a password is required.
		if ( post_password_required() ) {

			// Store Text Signatures array in global.
			$this->text_signatures_set( $this->text_signatures );

			// --<
			return $this->text_signatures;

		}

		// Wrap all Lines with spans.

		// Filter standalone captioned images.
		$content = $this->filter_captions( $content );

		// Explode by <span>.
		$output_array = $this->line_matches_get( $content );

		// Kick out if we have an empty array.
		if ( empty( $output_array ) ) {

			// Store Text Signatures array in global.
			$this->text_signatures_set( $this->text_signatures );

			// --<
			return $this->text_signatures;

		}

		// Reference our Post.
		global $post;

		// Init our content array.
		$content_array = [];

		// Init ( [ 'text_signature' => n ], where n is the number of duplicates ).
		$duplicates = [];

		// Run through 'em.
		foreach ( $output_array as $paragraph ) {

			// Is there any content?
			if ( $paragraph != '' ) {

				/*
				 * Check for paragraphs.
				 *
				 * TODO: Do we want to allow commenting on verses?
				 */
				if ( $paragraph !== '<p>' && $paragraph !== '</p>' ) {

					// Line commenting.

					// Get a signature for the Paragraph.
					$text_signature = $this->text_signature_generate( $paragraph );

					// Do we have one already?
					if ( in_array( $text_signature, $this->text_signatures ) ) {

						// Is it in the duplicates array?
						if ( array_key_exists( $text_signature, $duplicates ) ) {

							// Add one.
							$duplicates[ $text_signature ]++;

						} else {

							// Add it.
							$duplicates[ $text_signature ] = 1;

						}

						// Add number to end of Text Signature.
						$text_signature .= '_' . $duplicates[ $text_signature ];

					}

					// Add to signatures array.
					$this->text_signatures[] = $text_signature;

				}

			}

		}

		// Store Text Signatures array in global.
		$this->text_signatures_set( $this->text_signatures );

		// --<
		return $this->text_signatures;

	}

	/**
	 * Parses the content by Comment Block.
	 *
	 * @since 3.0
	 *
	 * @param str $content The Post content.
	 * @return str $content The parsed content.
	 */
	private function blocks_parse( $content ) {

		// Filter standalone captioned images.
		$content = $this->filter_captions( $content );

		// Get our Lines.
		$matches = $this->block_matches_get( $content );

		// Kick out if we don't have any.
		if ( ! count( $matches ) ) {
			return $content;
		}

		// Reference our Post.
		global $post;

		// Get sorted Comments and store.
		$this->comments_sorted = $this->comments_sorted_build( $post->ID );

		// Init starting Paragraph Number.
		$start_num = 1;

		// Set key.
		$key = '_cp_starting_para_number';

		// If the custom field already has a value.
		if ( get_post_meta( $post->ID, $key, true ) != '' ) {

			// Get it.
			$start_num = absint( get_post_meta( $post->ID, $key, true ) );

		}

		// We already have our Text Signatures, so set flag.
		$sig_key = 0;

		// Init content array.
		$content_array = [];

		// Run through 'em.
		foreach ( $matches as $paragraph ) {

			// Skip if there's no content.
			if ( empty( $paragraph ) ) {
				continue;
			}

			// Get a signature for the Paragraph.
			$text_signature = $this->text_signatures[ $sig_key ];

			// Construct Paragraph Number.
			$para_num = $sig_key + $start_num;

			// Increment.
			$sig_key++;

			// Get Comment count.
			// NB: the sorted array contains "Whole Page" as key 0, so we use the incremented value.
			$comment_count = count( $this->comments_sorted[ $text_signature ] );

			// Get Comment icon.
			$comment_icon = $this->core->display->get_comment_icon(
				$comment_count,
				$text_signature,
				'block',
				$para_num
			);

			// Get Paragraph icon.
			$paragraph_icon = $this->core->display->get_paragraph_icon(
				$comment_count,
				$text_signature,
				'block',
				$para_num
			);

			// Get Comment icon markup.
			$icon_html = $this->core->display->get_para_tag(
				$text_signature,
				$paragraph_icon . $comment_icon,
				'div'
			);

			// Assign icons to Blocks.
			$paragraph = $icon_html . $paragraph . '</div>' . "\n\n\n\n";

			// Add to content array.
			$content_array[] = $paragraph;

		}

		// Rejoin and exclude quicktag.
		$content = implode( '', $content_array );

		// --<
		return $content;

	}

	/**
	 * Splits the content into an array by Block.
	 *
	 * @since 3.0
	 *
	 * @param str $content The Post content.
	 * @return array $output_array The ordered array of matched items.
	 */
	private function block_matches_get( $content ) {

		// Filter out embedded Tweets.
		$filtered = $this->filter_twitter_embeds( $content );

		// Filter out Sharedaddy markup.
		$filtered = $this->filter_jetpack_sharing( $filtered );

		/*
		 *
		 * Although wp_texturize() does an okay job with creating Paragraphs,
		 *  Comments tend to screw things up. let's try and fix.
		 */

		/*
		 * First, replace all instances of
		 * '   <!--commentblock-->   '
		 * with
		 * '<p><!--commentblock--></p>\n'
		 */
		$filtered = preg_replace(
			'/\s+<!--commentblock-->\s+/',
			'<p><!--commentblock--></p>' . "\n",
			$filtered
		);

		/*
		 * Next, replace all instances of
		 * '<p><!--commentblock-->fengfnefe'
		 * with
		 * '<p><!--commentblock--></p>\n<p>fengfnefe'
		 */
		$filtered = preg_replace(
			'/<p><!--commentblock-->/',
			'<p><!--commentblock--></p>' . "\n" . '<p>',
			$filtered
		);

		/*
		 * Next, replace all instances of
		 * 'fengfnefe<!--commentblock--></p>'
		 * with
		 * 'fengfnefe</p>\n<p><!--commentblock--></p>'
		 */
		$filtered = preg_replace(
			'/<!--commentblock--><\/p>/',
			'</p>' . "\n" . '<p><!--commentblock--></p>' . "\n",
			$filtered
		);

		/*
		 * Replace all instances of
		 * '<br />\n<!--commentblock--><br />\n'
		 * with
		 * '</p>\n<p><!--commentblock--></p>\n<p>'
		 */
		$filtered = preg_replace(
			'/<br \/>\s+<!--commentblock--><br \/>\s+/',
			'</p>' . "\n" . '<p><!--commentblock--></p>' . "\n" . '<p>',
			$filtered
		);

		/*
		 * Next, replace all instances of
		 * '<br />\n<!--commentblock--></p>\n'
		 * with
		 * '</p>\n<p><!--commentblock--></p>\n<p>'
		 */
		$filtered = preg_replace(
			'/<br \/>\s+<!--commentblock--><\/p>\s+/',
			'</p>' . "\n" . '<p><!--commentblock--></p>' . "\n",
			$filtered
		);

		/*
		 * Next, replace all instances of
		 * '<p><!--commentblock--><br />\n'
		 * with
		 * '<p><!--commentblock--></p>\n<p>'
		 */
		$filtered = preg_replace(
			'/<p><!--commentblock--><br \/>\s+/',
			'<p><!--commentblock--></p>' . "\n" . '<p>',
			$filtered
		);

		// Repair some oddities: empty newlines with whitespace after.
		$filtered = preg_replace(
			'/<p><br \/>\s+/',
			'<p>',
			$filtered
		);

		// Repair some oddities: empty newlines without whitespace after.
		$filtered = preg_replace(
			'/<p><br \/>/',
			'<p>',
			$filtered
		);

		// Repair some oddities: empty Paragraphs with whitespace inside.
		$filtered = preg_replace(
			'/<p>\s+<\/p>\s+/',
			'',
			$filtered
		);

		// Repair some oddities: empty Paragraphs without whitespace inside.
		$filtered = preg_replace(
			'/<p><\/p>\s+/',
			'',
			$filtered
		);

		// Repair some oddities: any remaining empty Paragraphs.
		$filtered = preg_replace(
			'/<p><\/p>/',
			'',
			$filtered
		);

		// Explode by <p> version to temp array.
		// phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found
		$output_array = explode( '<p><' . '!--commentblock--></p>', $filtered );

		// Bail if we have an empty array.
		if ( empty( $output_array ) ) {
			return [];
		}

		// --<
		return $output_array;

	}

	// -------------------------------------------------------------------------

	/**
	 * Parses the content by Comment Block and generates Text Signature array.
	 *
	 * @since 3.0
	 *
	 * @param str $content The Post content.
	 * @return array $text_signatures The ordered array of Text Signatures.
	 */
	private function block_signatures_generate( $content ) {

		// Don't filter if a password is required.
		if ( post_password_required() ) {

			// Store Text Signatures array in global.
			$this->text_signatures_set( $this->text_signatures );

			// --<
			return $this->text_signatures;

		}

		// Filter standalone captioned images.
		$content = $this->filter_captions( $content );

		// Get Blocks array.
		$matches = $this->block_matches_get( $content );

		// Init ( [ 'text_signature' => n ], where n is the number of duplicates ).
		$duplicates = [];

		// Run through 'em.
		foreach ( $matches as $paragraph ) {

			// Is there any content?
			if ( $paragraph != '' ) {

				// Get a signature for the Paragraph.
				$text_signature = $this->text_signature_generate( $paragraph );

				// Do we have one already?
				if ( in_array( $text_signature, $this->text_signatures ) ) {

					// Is it in the duplicates array?
					if ( array_key_exists( $text_signature, $duplicates ) ) {

						// Add one.
						$duplicates[ $text_signature ]++;

					} else {

						// Add it.
						$duplicates[ $text_signature ] = 1;

					}

					// Add number to end of Text Signature.
					$text_signature .= '_' . $duplicates[ $text_signature ];

				}

				// Add to signatures array.
				$this->text_signatures[] = $text_signature;

			}

		}

		// Store Text Signatures array in global.
		$this->text_signatures_set( $this->text_signatures );

		// --<
		return $this->text_signatures;

	}

	/**
	 * Strips Quicktags from content otherwise they get formatting.
	 *
	 * @since 3.0
	 *
	 * @param str $content The Post content.
	 * @return str $content The modified Post content.
	 */
	private function filter_quicktags( $content ) {

		/*
		------------------------------------------------------------------------
		Notes added: 08 Mar 2012
		------------------------------------------------------------------------

		Here's how these Quicktags work:
		http://codex.wordpress.org/Customizing_the_Read_More

		-------------
		More Quicktag
		-------------

		However, we cannot be sure of how the Quicktags has been inserted. For example (1):

		<p>Here&#8217;s the teaser<br />
		<span id="more-689"></span><br />
		Here&#8217;s the rest of the post</p>

		Is the intention here that the teaser is a Paragraph? I'd say so.

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

		Now, for our purposes, since we currently use the excerpt in the Blog Archives, only
		(1) and (2) are truly problematic - because they cause visible formatting. (3) & (4)
		do not currently get filtered out because the spans are inline - but they do imply
		that the content before and after should be self-contained. As a result, I think it
		is probably better to add a statement about correct usage in to the help text so that
		we can reliably parse the content.

		-----------------
		NoTeaser Quicktag
		-----------------

		The Codex says "Include <!--noteaser--> in the Post text, immediately after the <!--more-->"
		which really means *on the same Line*. When this is done, our content looks like this (1):

		<p><span id="more-691"></span><!--noteaser--></p>
		<p>And this is the rest of the post blah</p>

		Or (2):

		<p><span id="more-691"></span><!--noteaser--><br />
		And this is the rest of the post blah</p>

		------------------------------------------------------------------------
		*/

		// Define noteaser Quicktag.
		$noteaser = '<!--noteaser-->';

		// Look for inline <!--more--> span.
		if ( preg_match( '/<span id="more-(.*?)?' . '><\/span><br \/>/', $content, $matches ) ) {

			// Derive list.
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude shortcode.
			$content = implode( '', $content );

		}

		// Look for separated <!--more--> span.
		if ( preg_match( '/<p><span id="more-(.*?)?' . '><\/span><\/p>/', $content, $matches ) ) {

			// Derive list.
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude shortcode.
			$content = implode( '', $content );

		}

		// Look for inline <!--more--> span correctly followed by <!--noteaser-->.
		// phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found
		if ( preg_match( '/<span id="more-(.*?)?' . '><\/span>' . $noteaser . '<br \/>/', $content, $matches ) ) {

			// Derive list.
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude shortcode.
			$content = implode( '', $content );

		}

		// Look for separated <!--more--> span correctly followed by <!--noteaser-->.
		// phpcs:ignore Generic.Strings.UnnecessaryStringConcat.Found
		if ( preg_match( '/<p><span id="more-(.*?)?' . '><\/span>' . $noteaser . '<\/p>/', $content, $matches ) ) {

			// Derive list.
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude shortcode.
			$content = implode( '', $content );

		}

		// Look for incorrectly placed inline <!--noteaser-->.
		if ( preg_match( '/' . $noteaser . '<br \/>/', $content, $matches ) ) {

			// Derive list.
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude shortcode.
			$content = implode( '', $content );

		}

		// Look for incorrectly placed separated <!--noteaser-->.
		if ( preg_match( '/<p>' . $noteaser . '<\/p>/', $content, $matches ) ) {

			// Derive list.
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude shortcode.
			$content = implode( '', $content );

		}

		/*
		// This gets the additional text.
		if ( ! empty( $matches[1] ) ) {
			$more_link_text = strip_tags( wp_kses_no_null( trim( $matches[1] ) ) );
		}
		*/

		// --<
		return $content;

	}

	/**
	 * Prevents the JetPack Sharedaddy list and Paragraph from being parsed.
	 *
	 * @since 4.0
	 *
	 * @param str $content The Post content.
	 * @return str $content The filtered Post content.
	 */
	private function filter_jetpack_sharing( $content ) {

		// Bail if JetPack isn't present.
		if ( ! defined( 'JETPACK__VERSION' ) ) {
			return $content;
		}

		// Remove by replacing "ul" with "foo" so it isn't parsed.
		$content = str_replace(
			'<ul class="jetpack-sharer-list">',
			'<foo class="jetpack-sharer-list">',
			$content
		);

		// Remove by replacing "p" with "foo" so it isn't parsed.
		$content = str_replace(
			'<p class="share-customize-link">',
			'<foo class="share-customize-link">',
			$content
		);

		// --<
		return $content;

	}

	/**
	 * Removes embedded Tweets.
	 *
	 * TODO: Make these commentable.
	 *
	 * @since 3.0
	 *
	 * @param str $content The Post content.
	 * @return str $content The filtered Post content.
	 */
	private function filter_twitter_embeds( $content ) {

		// Look for Embedded Tweet <blockquote>.
		if ( preg_match( '#<(blockquote class="twitter-tweet)[^>]*?' . '>(.*?)</(blockquote)>#si', $content, $matches ) ) {

			// Derive list.
			$content = explode( $matches[0], $content, 2 );

			// Rejoin to exclude from content to be parsed.
			$content = implode( '', $content );

			// Remove old Twitter script.
			$content = str_replace(
				// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				'<p><script src="//platform.twitter.com/widgets.js" charset="utf-8"></script></p>',
				'',
				$content
			);

			// Remove new Twitter script.
			$content = str_replace(
				// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				'<p><script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script></p>',
				'',
				$content
			);

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
	 * the Post content, they can be standalone too. Props: Ralph Bloch.
	 *
	 * @since 3.8
	 *
	 * @param str $content The Post content.
	 * @return str $content The filtered Post content.
	 */
	private function filter_captions( $content ) {

		$start = '<!-- cp_caption_start -->';
		$end = '<!-- cp_caption_end -->';

		// Filter captioned images that are *not* inside other tags.
		$pattern = [
			'/\n' . $start . '/',
			'/' . $end . '\n/',
		];

		// Define replacements.
		$replace = [
			"\n" . '<p>' . $start,
			$end . '</p>' . "\n",
		];

		// Do replacement.
		$content = preg_replace( $pattern, $replace, $content );

		// Check for captions at the very beginning of content.
		if ( substr( $content, 0, 25 ) == $start ) {
			$content = '<p>' . $content;
		}

		// --<
		return $content;

	}

	/**
	 * Wraps processed audio shortcodes in a Paragraph tag.
	 *
	 * @since 3.9.3
	 *
	 * @param string $html Shortcode HTML output.
	 * @param array $atts Array of shortcode attributes.
	 * @param string $file Media file.
	 * @param int $post_id Post ID.
	 * @param string $library Media library used for the shortcode.
	 */
	public function filter_audio_shortcode( $html, $atts, $file, $post_id, $library ) {

		// Wrap.
		return '<p><span class="cp-audio-shortcode">' . $html . '</span></p>';

	}

	/**
	 * Wraps processed video shortcodes in a Paragraph tag.
	 *
	 * @since 3.9.3
	 *
	 * @param string $html Shortcode HTML output.
	 * @param array $atts Array of shortcode attributes.
	 * @param string $file Media file.
	 * @param int $post_id Post ID.
	 * @param string $library Media library used for the shortcode.
	 */
	public function filter_video_shortcode( $html, $atts, $file, $post_id, $library ) {

		// Replace enclosing div with span.
		$html = str_replace( '<div', '<span', $html );
		$html = str_replace( '</div', '</span', $html );

		// Wrap in markup.
		return '<p><span class="cp-video-shortcode"><span></span>' . $html . '</span></p>';

	}

	/**
	 * Removes leading and trailing <br /> tags from embedded quotes.
	 *
	 * @since 3.0
	 *
	 * @param string $content The Post content.
	 * @return string $content The filtered Post content.
	 */
	private function filter_blockquotes_in_paras( $content ) {

		// Make sure we strip leading br.
		$content = str_replace(
			'<br />' . "\n" . '<span class="blockquote-in-para">',
			"\n" . '<span class="blockquote-in-para">',
			$content
		);

		// Analyse.
		preg_match_all( '#(<span class="blockquote-in-para">(.*?)</span>)<br />#si', $content, $matches );

		// Did we get any?
		if ( isset( $matches[0] ) && ! empty( $matches[0] ) ) {

			$content = str_replace(
				$matches[0],
				$matches[1],
				$content
			);

		}

		// --<
		return $content;

	}

	// -------------------------------------------------------------------------

	/**
	 * Get all WordPress Comments for a Post, unless Paged.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class and renamed.
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @return array $comments The array of Comment data.
	 */
	private function comments_for_post_get_all( $post_id ) {

		// Use the WordPress API.
		$comments = get_comments( 'post_id=' . $post_id . '&order=ASC' );

		// --<
		return $comments;

	}

	/**
	 * Gets Comments sorted by Text Signature and Paragraph.
	 *
	 * @since 3.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @return array $comments The sorted Comments array.
	 */
	public function comments_sorted_get( $post_id ) {

		// Have we already sorted the Comments?
		if ( ! empty( $this->comments_sorted ) ) {
			return $this->comments_sorted;
		}

		// --<
		return $this->comments_sorted_build( $post_id );

	}

	/**
	 * Builds an array of Comments sorted by Text Signature and Paragraph.
	 *
	 * @since 3.0
	 *
	 * @param int $post_id The numeric ID of the Post.
	 * @return array $sorted_comments The array of Comment data.
	 */
	private function comments_sorted_build( $post_id ) {

		// Init return.
		$sorted_comments = [];

		// Get all Comments.
		$comments = $this->comments_all;

		// Filter out any multipage Comments not on this Page.
		$comments = $this->comments_multipage_filter( $comments );

		// Get our Text Signatures.
		$sigs = $this->text_signatures_get();

		// Assign Comments to Text Signatures.
		$assigned = $this->comments_assign( $comments, $sigs );

		/*
		 * NOTE: $assigned is an array with sigs as keys and array of Comments
		 * as value it may be empty.
		 */

		// If we have any Comments on the whole Page, add them first.
		$sorted_comments['WHOLE_PAGE_OR_POST_COMMENTS'] = [];
		if ( isset( $assigned['WHOLE_PAGE_OR_POST_COMMENTS'] ) ) {
			$sorted_comments['WHOLE_PAGE_OR_POST_COMMENTS'] = $assigned['WHOLE_PAGE_OR_POST_COMMENTS'];
		}

		// We must have Text Signatures.
		if ( ! empty( $sigs ) ) {

			// Then add  in the order of our Text Signatures.
			foreach ( $sigs as $text_signature ) {

				// Append assigned Comments.
				$sorted_comments[ $text_signature ] = [];
				if ( ! empty( $assigned[ $text_signature ] ) ) {
					$sorted_comments[ $text_signature ] = $assigned[ $text_signature ];
				}

			}

		}

		// Add any pingbacks or trackbacks last.
		$sorted_comments['PINGS_AND_TRACKS'] = [];
		if ( ! empty( $assigned['PINGS_AND_TRACKS'] ) ) {
			$sorted_comments['PINGS_AND_TRACKS'] = $assigned['PINGS_AND_TRACKS'];
		}

		// --<
		return $sorted_comments;

	}

	/**
	 * Filter Comments to find Comments for the current Page of a multipage Post.
	 *
	 * @since 3.4
	 *
	 * @param array $comments The array of Comment objects.
	 * @return array $filtered The array of Comments for the current Page.
	 */
	private function comments_multipage_filter( $comments ) {

		// Access globals.
		global $post, $page, $multipage;

		// Init return.
		$filtered = [];

		// Kick out if no Comments.
		if ( ! is_array( $comments ) || empty( $comments ) ) {
			return $filtered;
		}

		// Kick out if not multipage.
		if ( ! isset( $multipage ) || ! $multipage ) {
			return $comments;
		}

		// Now add only Comments that are on this Page or are Page-level.
		foreach ( $comments as $comment ) {

			// If it has a Text Signature.
			if ( ! is_null( $comment->comment_signature ) && $comment->comment_signature != '' ) {

				// Set key.
				$key = '_cp_comment_page';

				// Does it have a Comment meta value?
				if ( get_comment_meta( $comment->comment_ID, $key, true ) != '' ) {

					// Get the Page number.
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
	 * Assigns the given Comments to an associative array, keyed by Text Signature.
	 *
	 * @since 3.0
	 *
	 * @param array $comments The array of Comment objects.
	 * @param array $text_signatures The array of Text Signatures.
	 * @param integer $confidence The confidence level of Paragraph identity - default 90%.
	 * @return array $assigned The array with Text Signatures as keys and array of Comments as values.
	 */
	private function comments_assign( $comments, $text_signatures, $confidence = 90 ) {

		// Init returned array.
		// NB: we use a very unlikely key for Page-level Comments: WHOLE_PAGE_OR_POST_COMMENTS.
		$assigned = [];

		// Kick out if no Comments.
		if ( ! is_array( $comments ) || empty( $comments ) ) {
			return $assigned;
		}

		// Run through our Comments.
		foreach ( $comments as $comment ) {

			// Test for empty Comment Text Signature.
			if ( ! is_null( $comment->comment_signature ) && $comment->comment_signature != '' ) {

				// Do we have an exact match in the Text Signatures array?
				// NB: this will work, because we're already ensuring identical sigs are made unique.
				if ( in_array( $comment->comment_signature, $text_signatures ) ) {

					// Yes, assign to that key.
					$assigned[ $comment->comment_signature ][] = $comment;

				} else {

					// Init possibles array.
					$possibles = [];

					// Find the nearest matching Text Signature.
					foreach ( $text_signatures as $text_signature ) {

						// Compare strings.
						similar_text( $comment->comment_signature, $text_signature, $score );

						// Add to possibles array if it passes.
						if ( $score >= $confidence ) {
							$possibles[ $text_signature ] = $score;
						}

					}

					// Did we get any?
					if ( ! empty( $possibles ) ) {

						// Sort them by score.
						arsort( $possibles );

						// Get keys.
						$keys = array_keys( $possibles );

						// Let's use the Text Signature with the highest score.
						$highest = array_pop( $keys );

						// Assign Comment to that key.
						$assigned[ $highest ][] = $comment;

					} else {

						// Set property in case we need it.
						$comment->orphan = true;

						// Clear Text Signature.
						$comment->comment_signature = '';

						// Is it a pingback or trackback?
						if ( $comment->comment_type == 'trackback' || $comment->comment_type == 'pingback' ) {

							// We have one - assign to pings.
							$assigned['PINGS_AND_TRACKS'][] = $comment;

						} else {

							// We have Comment with no Text Signature - assign to Page.
							$assigned['WHOLE_PAGE_OR_POST_COMMENTS'][] = $comment;

						}

					}

				}

			} else {

				// Is it a pingback or trackback?
				if ( $comment->comment_type == 'trackback' || $comment->comment_type == 'pingback' ) {

					// We have one - assign to pings.
					$assigned['PINGS_AND_TRACKS'][] = $comment;

				} else {

					// We have Comment with no Text Signature - assign to Page.
					$assigned['WHOLE_PAGE_OR_POST_COMMENTS'][] = $comment;

				}

			}

		}

		// --<
		return $assigned;

	}

	// -------------------------------------------------------------------------

	/**
	 * Store Text Signatures in a global.
	 *
	 * This is needed because some versions of PHP do not save properties!
	 *
	 * @since 3.4
	 *
	 * @param array $sigs An array of Text Signatures.
	 */
	public function text_signatures_set( $sigs ) {

		// Store them.
		global $ffffff_sigs;
		$ffffff_sigs = $sigs;

	}

	/**
	 * Retrieve Text Signatures.
	 *
	 * @since 3.4
	 *
	 * @return array $text_signatures An array of Text Signatures.
	 */
	public function text_signatures_get() {

		// Get them.
		global $ffffff_sigs;
		return $ffffff_sigs;

	}

	// -------------------------------------------------------------------------

	/**
	 * Generates a Text Signature based on the content of a Paragraph.
	 *
	 * @since 3.0
	 *
	 * @param str $text The text of a Paragraph.
	 * @return str $text_signature The generated Text Signature.
	 */
	private function text_signature_generate( $text ) {

		// Get an array of words from the text.
		$words = explode( ' ', preg_replace( '/[^A-Za-z]/', ' ', html_entity_decode( $text ) ) );

		// Store unique words.
		// NB: this may be a mistake for poetry, which can use any words in any order.
		$unique_words = array_unique( $words );

		// Init sig.
		$text_signature = null;

		// Run through our unique words.
		foreach ( $unique_words as $key => $word ) {

			// Add first letter.
			$text_signature .= substr( $word, 0, 1 );

			// Limit to 250 chars.
			// NB: this is because we have changed the format of Text Signatures by adding numerals
			// when there are duplicates. Duplicates add at least 2 characters, so there is the
			// (admittedly remote) possibility of exceeding the varchar(255) character limit.
			if ( $key > 250 ) {
				break;
			}

		}

		// --<
		return $text_signature;

	}

	/**
	 * Get Text Signature for a given Paragraph Number.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @param int $para_num The Paragraph Number in a Post.
	 * @return str $text_signature The Text Signature.
	 */
	public function text_signature_get( $para_num ) {

		// Get Text Signatures.
		$sigs = $this->text_signatures_get();

		// Get value at that position in array.
		$text_signature = isset( $sigs[ $para_num - 1 ] ) ? $sigs[ $para_num - 1 ] : '';

		// --<
		return $text_signature;

	}

	/**
	 * Retrieves Text Signature by Comment ID.
	 *
	 * @since 3.4
	 *
	 * @param int $comment_id The numeric ID of the Comment.
	 * @return str $text_signature The Text Signature for the Comment.
	 */
	public function text_signature_get_by_comment_id( $comment_id ) {

		// Database object.
		global $wpdb;

		// Query for signature.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$text_signature = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT comment_signature FROM $wpdb->comments WHERE comment_ID = %s",
				$comment_id
			)
		);

		// --<
		return $text_signature;

	}

	/**
	 * Retrieves the current Text Signature hidden input.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @return str $result The HTML input.
	 */
	public function text_signature_field_get() {

		// Init Text Signature.
		$text_signature = '';

		// Get Comment ID to reply to from URL query string.
		$reply_to_comment_id = isset( $_GET['replytocom'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['replytocom'] ) ) : 0;

		// Did we get a Comment ID?
		if ( $reply_to_comment_id != 0 ) {

			// Get Paragraph Text Signature.
			$text_signature = $this->core->parser->text_signature_get_by_comment_id( $reply_to_comment_id );

		} else {

			// Do we have a Paragraph Number in the query string?
			$reply_to_para_id = isset( $_GET['replytopara'] ) ? (int) sanitize_text_field( wp_unslash( $_GET['replytopara'] ) ) : 0;

			// Get Paragraph Text Signature when we get a Comment ID.
			if ( $reply_to_para_id != 0 ) {
				$text_signature = $this->core->parser->text_signature_get( $reply_to_para_id );
			}

		}

		// Get constructed hidden input for Comment form.
		$result = $this->text_signature_input_get( $text_signature );

		// --<
		return $result;

	}

	/**
	 * Gets the Text Signature input for the Comment form.
	 *
	 * @since 3.4
	 * @since 4.0 Moved to this class.
	 *
	 * @param str $text_signature The Text Signature.
	 * @return str $input The HTML input element.
	 */
	public function text_signature_input_get( $text_signature = '' ) {

		// Define input tag.
		$input = '<input type="hidden" id="text_signature" name="text_signature" value="' . $text_signature . '" />';

		// --<
		return $input;

	}

	/**
	 * Gets the Paragraph Number for a given Text Signature.
	 *
	 * This seems to be an unused utility method.
	 *
	 * @since 3.4
	 *
	 * @param str $text_signature The Text Signature.
	 * @return int $num The position in Text Signature array.
	 */
	public function paragraph_num_get_by_text_signature( $text_signature ) {

		// Get position in array.
		$num = array_search( $text_signature, $this->text_signatures_get() );

		// --<
		return $num + 1;

	}

}
