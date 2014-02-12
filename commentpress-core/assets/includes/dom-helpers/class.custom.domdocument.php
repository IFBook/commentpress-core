<?php /*
================================================================================
CommentPress Custom DOM Helper
================================================================================
--------------------------------------------------------------------------------
NOTES

This class merges two DOM helper classes: SmartDOMDocument and XHTMLDocument.


SmartDOMDocument
----------------
@author Artem Russakovskii
@version 0.4
@link http://beerpla.net/projects/smartdomdocument-a-smarter-php-domdocument-class/

SmartDOMDocument overcomes a few common annoyances with the DOMDocument class,
such as saving partial HTML without automatically adding extra tags and properly 
recognizing various encodings, specifically UTF-8.

Christian has modified it to reconvert back to UTF-8 when saveHTMLExact() called.


XHTMLDocument
-------------
@link http://www.php.net/manual/en/class.domdocument.php#104218

Represents an entire XHTML DOM document; serves as the root of the document tree.

--------------------------------------------------------------------------------
*/

if ( !class_exists( 'CommentPress_DOMDocument' ) ) {

	class CommentPress_DOMDocument extends DOMDocument {
	
		/**
		 * These tags must always self-terminate. Anything else must never self-terminate.
		 * 
		 * @var array
		 */
		public $selfTerminate = array(
			'area','base','basefont','br','col','frame','hr','img','input','link','meta','param'
		);
		
		/**
		 * saveXHTML
		 *
		 * Dumps the internal XML tree back into an XHTML-friendly string.
		 *
		 * @param DOMNode $node
		 * Use this parameter to output only a specific node rather than the entire document.
		 */
		public function saveXHTML(DOMNode $node=null) {
		
			//print_r( $this->lastChild ); //die();
			if (!$node) $node = $this->lastChild;
			
			$doc = new DOMDocument('1.0');
			$clone = $doc->importNode(
				$node->cloneNode(false), true
			);
			$term = in_array(strtolower($clone->nodeName), $this->selfTerminate);
			$inner='';
			
			if (!$term) {
				$clone->appendChild(new DOMText(''));
				if ($node->childNodes) foreach ($node->childNodes as $child) {
					$inner .= $this->saveXHTML($child);
				}
			}
			
			$doc->appendChild($clone);
			$out = $doc->saveXML($clone);
			
			return $term ? substr($out, 0, -2) . ' />' : str_replace('><', ">$inner<", $out);
		
		}
	
		/**
		 * Adds an ability to use the SmartDOMDocument object as a string in a string context.
		 * For example, echo "Here is the HTML: $dom";
		 */
		public function __toString() {
		
			return $this->saveHTMLExact();
		
		}
	
		/**
		 * Load HTML with a proper encoding fix/hack.
		 * Borrowed from the link below.
		 *
		 * @link http://www.php.net/manual/en/domdocument.loadhtml.php
		 *
		 * @param string $html
		 * @param string $encoding
		 */
		public function loadHTML( $html, $encoding = 'UTF-8' ) {
			
			// convert to entities to avoid bug
			$html = mb_convert_encoding($html, 'HTML-ENTITIES', $encoding);
			
			// suppress warnings
			@parent::loadHTML($html); 
		
		}
	
		/**
		 * Return HTML while stripping the annoying auto-added <html>, <body>, and doctype.
		 *
		 * @link http://php.net/manual/en/migration52.methods.php
		 *
		 * @return string
		 */
		public function saveHTMLExact() {
		
			// original regex
			$content = preg_replace(
				array( "/^\<\!DOCTYPE.*?<html><body>/si", "!</body></html>$!si" ),
				"",
				$this->saveHTML()
			);
			
			/*
			// alternative regex, from comments...
			$content = preg_replace(
				array( '#^<\!DOCTYPE.*?.*?.*?#is', '#.*?$#si'),
				'',
				trim( $this->saveHTML() )
			);
			*/
			
			// reconvert back to UTF-8, given that entities were only used to avoid bug
			$content = mb_convert_encoding( $content, 'UTF-8', 'HTML-ENTITIES' );
			
			return $content;
			
		}
	
	}

}




