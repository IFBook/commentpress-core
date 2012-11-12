<?php

/**
 * This class overcomes a few common annoyances with the DOMDocument class,
 * such as saving partial HTML without automatically adding extra tags
 * and properly recognizing various encodings, specifically UTF-8.
 * 
 * Modified to reconvert back to UTF-8 when saveHTMLExact() called.
 *
 * @author Artem Russakovskii
 * @modified Christian Wach
 * @version 0.4
 * @link http://beerpla.net/projects/smartdomdocument-a-smarter-php-domdocument-class/
 * @link http://www.php.net/manual/en/class.domdocument.php
 */

if ( !class_exists( 'SmartDOMDocument' ) ) {

	class SmartDOMDocument extends DOMDocument {
	
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
		public function loadHTML($html, $encoding = 'UTF-8' ) {
			
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


