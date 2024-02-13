<?php
/**
 *	Collection of functions for rendering text mails.
 *
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 */

use CeusMedia\Common\Alg\Text\Extender as TextExtender;
use CeusMedia\Common\Alg\Text\Trimmer as TextTrimmer;

/**
 *	Collection of functions for rendering text mails.
 *
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@todo		 finish code doc
 */
class View_Helper_Mail_Text
{
	static protected string $encoding	= "UTF-8";

	/**
	 *	Extends a one line string with whitespace from right (or left) to a maximum length.
	 *	With multibyte support.
	 *	@static
	 *	@access		public
	 *	@param		string		$text			One line string to extend
	 *	@param		integer		$toLength		Maximum string length
	 *	@param		boolean		$fromLeft		Flag: extend from left (= float right)
	 *	@return		string		Extended one line string
	 */
	static public function extend( string $text, int $toLength, bool $fromLeft = FALSE ): string
	{
		return TextExtender::extend( $text, $toLength, $fromLeft );
	}

	/**
	 *	Extends a one line string with whitespace from left and right to a maximum length.
	 *	With multibyte support.
	 *	@static
	 *	@access		public
	 *	@param		string		$text			One line string to extend
	 *	@param		integer		$toLength		Maximum string length
	 *	@return		string		Extended one line string
	 */
	static public function extendCentric( string $text, int $toLength ): string
	{
		return TextExtender::extendCentric( $text, $toLength );
	}

	/**
	 *	Fits a one line string into a maximum length.
	 *	Trims too long strings from left, right or centric (default).
	 *	Extends too short strings to float left (default), right or centric.
	 *	With multibyte support.
	 *	@static
	 *	@access		public
	 *	@param		string			$text			One line string to extend
	 *	@param		integer			$toLength		Maximum string length
	 *	@param		integer|string	$float			Mode: left(1), right(0), center(2)
	 *	@param		boolean			$trimCentric	Flag: trim text centric
	 *	@return		string			Fitted one line string
	 */
	static public function fit( string $text, int $toLength, int|string $float = 1, bool $trimCentric = TRUE ): string
	{
		$text		= self::trim( trim( $text ), $toLength, $trimCentric ? 2 : 0 );
		if( in_array( (string) $float, ["2", 'center', 'centric'] ) )
			return self::extendCentric( $text, $toLength );
		$floatRight	= $float === 0 || $float === 'right';
		return self::extend( $text, $toLength, $floatRight );
	}

	/**
	 *	Indents all other lines of a multi line string.
	 *	Wraps each line to an optionally given maximum line length before.
	 *	@static
	 *	@access		public
	 *	@param		string			$text				One or multi line text to extend
	 *	@param		integer			$indentLength		Length of indention
	 *	@param		integer|NULL	$lineLength			Limit line length and wrap, default: no
	 *	@return		string
	 */
	static public function indent( string $text, int $indentLength, ?int $lineLength = NULL ): string
	{
		if( $lineLength )
			$text	= wordwrap( $text, $lineLength );
		$lines	= explode( "\n", $text );
		$indent	= str_repeat( " ", $indentLength );
		return implode( "\n".$indent, $lines );
	}

	/**
	 *	Draws a line.
	 *	@static
	 *	@access		public
	 *	@param		string		$sign			Line drawing character
	 *	@param		integer		$lineLength		Line length
	 *	@return		string
	 */
	static public function line( string $sign = '-', int $lineLength = 76 ): string
	{
		$steps	= floor( $lineLength / mb_strlen( $sign ) );
		return str_repeat( $sign, $steps );
	}

	/**
	 *	Cuts a one line string from left, right or centric to a maximum length.
	 *	With multibyte support.
	 *	@static
	 *	@access		public
	 *	@param		string			$text			One line string to extend
	 *	@param		integer			$length			Maximum string length
	 *	@param		integer|string	$mode			Mode: left(1), right(0), center(2)
	 *	@return		string
	 */
	static public function trim( string $text, int $length, int|string $mode = 2 ): string
	{
		if( $mode === 2 || $mode === 'center' || $mode === 'centric' )
			return TextTrimmer::trimCentric( $text, $length );
		return TextTrimmer::trim( $text, $length, in_array( $mode, [1, 'left'] ) );
	}

	/**
	 *	Underscores one line string with repeated string.
	 *	@static
	 *	@access		public
	 *	@param		string		$text			One line string to extend
	 *	@param		string		$withString		String to repeat below string, default: "-"
	 *	@param		integer		$maxLength		Maximum string length
	 *	@return		string		Last line of text underscored
	 */
	static public function underscore( string $text, string $withString = '-', int $maxLength = 76 ): string
	{
		$parts	= explode( "\n", $text );
		$text	= array_pop( $parts );
		$text	= self::trim( trim( strip_tags( $text ) ), $maxLength );
		$repeat	= ceil( strlen( $text ) / strlen( $withString ) );
		if( function_exists( 'mb_strlen' ) ){
			$withStringLength	= mb_strlen( $withString, self::$encoding );
			$textLength			= mb_strlen( $text, self::$encoding );
			$repeat				= ceil( $textLength / $withStringLength );
		}
		$line	= str_repeat( $withString, $repeat );
		return $text."\n".$line;
	}
}
