<?php
/**
 *	Converts HTML to plain text using a DOM parser.
 *
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\HydrogenFramework\Environment;

/**
 *	Converts HTML to plain text using a DOM parser.
 *
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 */
class View_Helper_HtmlToPlainText
{
	protected $env;

	protected $html;

	public function __construct( Environment $env )
	{
		$this->env	= $env;
	}

	/**
	 *	Converts set HTML to plain text.
	 *	@access		public
	 *	@return		string				Plain text converted from set HTML
	 *	@throws		RuntimeException	if no HTML has been set
	 */
	public function render(): string
	{
		if( !strlen( trim( $this->html ) ) )
			throw new RuntimeException( 'No HTML set' );
		return self::convert( $this->html );
	}

	/**
	 *	Sets HTML to convert to plain text.
	 *	@access		public
	 *	@param		string		$html		HTML to convert to plain text
	 *	@return		self
	 */
	public function setHtml( string $html ): self
	{
		$this->html	= $html;
		return $this;
	}

	/**
	 *	Converts set HTML to plain text, statically.
	 *	@static
	 *	@access		public
	 *	@param		string		$html		HTML to convert to plain text
	 *	@return		string		Plain text converted from set HTML
	 */
	public static function convert( string $html ): string
	{
		$html	= mb_convert_encoding( $html, 'HTML-ENTITIES', "UTF-8" );
		$doc	= new DOMDocument();
		$doc->preserveWhitespace = FALSE;
		$doc->loadHTML( $html );
		return self::convertNodes( $doc );
	}

	//  --  PROTECTED  --  //

	protected static function convertNodes( DOMNode $root ): string
	{
		$text		= '';
		$cleared	= TRUE;
		foreach( $root->childNodes as $node ){
			$nodeName	= $node->nodeName;
			$nodeType	= $node->nodeType;
			$prefix		= '';
			$suffix		= '';
			if( $node->nodeType === XML_TEXT_NODE ){
				if( !$node->isWhitespaceInElementContent() )
					$text	.= wordwrap( trim( $node->textContent, "\t\r\n" ) );
			}
			else if( $node->nodeType === XML_ELEMENT_NODE ){
				if( self::isBlockElement( $node ) ){
					if( !$cleared )
						$prefix	= PHP_EOL;
					$suffix		= PHP_EOL;
					$cleared	= TRUE;
					if( in_array( $nodeName, array( 'h1', 'h2' ) ) ){
						$prefix		.= PHP_EOL;
						$suffix		.= self::underline( $node, '=' );
					}
					else if( in_array( $nodeName, array( 'h3', 'h4', 'h5' ) ) ){
						$prefix		.= PHP_EOL;
						$suffix		.= self::underline( $node, '-' );
					}
					else if( $nodeName == "hr" ){
						$prefix		.= str_repeat( '-', 78 );
					}
					else if( $nodeName == "li" ){
						$prefix		.= '- ';
					}
					else if( in_array( $nodeName, array( "p" ) ) ){
						$prefix		.= PHP_EOL;
					}
					else if( in_array( $nodeName, array( "p", 'ul', 'div' ) ) ){
					}
				}
				else{
					$cleared	= FALSE;
					if( $nodeName == "a" ){
						$suffix		= ' ('.$node->getAttribute( 'href' ).')';
					}
					else if( in_array( $nodeName, array( "b", "strong" ) ) ){
						$prefix		= '**';
						$suffix		= '**';
					}
					else if( in_array( $nodeName, array( "em" ) ) ){
						$prefix		= '*';
						$suffix		= '*';
					}
					else if( in_array( $nodeName, array( "br" ) ) ){
						$suffix		= PHP_EOL;
						$cleared	= TRUE;
					}
				}
				$inner	= '';
				if( $node->hasChildNodes() )
					$inner	= self::convertNodes( $node );
				$text	.= $prefix.$inner.$suffix;
			}
		}
		return $text;
	}

	protected static function isBlockElement( DOMNode $node ): bool
	{
		$elements	= array_merge(
			array( 'div', 'p', 'ul', 'li', 'hr', 'blockquote', 'pre', 'xmp' ),
			array( 'h1', 'h2', 'h3', 'h4', 'h5' )
		);
		return in_array( $node->nodeName, $elements );
	}

	protected static function isInlineElement( DOMNode $node ): bool
	{
		return !self::isBlockElement( $node );
	}

	protected static function underline( DOMNode $node, string $character = '-' ): string
	{
		return str_repeat( $character, strlen( $node->textContent ) ).PHP_EOL;
	}
}
