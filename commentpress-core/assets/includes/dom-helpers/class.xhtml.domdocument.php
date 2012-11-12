<?php

/**
 * XHTML Document
 *
 * Represents an entire XHTML DOM document; serves as the root of the document tree.
 */

if ( !class_exists( 'XHTMLDocument' ) ) {

	class XHTMLDocument extends DOMDocument {
	
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
		
			if (!$node) $node = $this->firstChild;
			
			$doc = new DOMDocument('1.0');
			$clone = $doc->importNode($node->cloneNode(false), true);
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
	
	}
	
}


