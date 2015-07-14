<?php
/**
 *	Collection of functions for rendering text mails.
 *
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 */
/**
 *	Collection of functions for rendering text mails.
 *
 *	@author		Christian Würker <christian.wuerker@ceusmedia.de>
 *	@todo		kriss: finish code doc
 */
class View_Helper_Mail_Text{

	static protected $encoding	= "UTF-8";

	/**
	 *	Extends a one line string with whitespace from right (or left) to a maximum length.
	 *	With multi-byte support.
	 *	@static
	 *	@access		public
	 *	@param		string		$text			One line string to extend
	 *	@param		integer		$toLength		Maximum string length
	 *	@param		boolean		$fromLeft		Flag: extend from left (= float right)
	 *	@return		string		Extended one line string
	 */
	static public function extend( $text, $toLength, $fromLeft = FALSE ){
		return Alg_Text_Extender::extend( $text, $toLength, $fromLeft, ' ' );
	}

	/**
	 *	Extends a one line string with whitespace from left and right to a maximum length.
	 *	With multi-byte support.
	 *	@static
	 *	@access		public
	 *	@param		string		$text			One line string to extend
	 *	@param		integer		$toLength		Maximum string length
	 *	@return		string		Extended one line string
	 */
	static public function extendCentric( $text, $toLength ){
		return Alg_Text_Extender::extendCentric( $text, $toLength, ' ' );
	}

	/**
	 *	Fits a one line string into a maximum length.
	 *	Trims too long strings from left, right or centric (default).
	 *	Extends too short strings to float left (default), right or centric.
	 *	With multi-byte support.
	 *	@static
	 *	@access		public
	 *	@param		string		$text			One line string to extend
	 *	@param		integer		$toLength		Maximum string length
	 *	@param		integer		$float			Mode: left(1), right(0), center(2)
	 *	@param		boolean		$trimCentric	Flag: trim text centric
	 *	@return		string		Fitted one line string
	 */
	static public function fit( $text, $toLength, $float = 1, $trimCentric = TRUE ){
		$toLength	= (int) $toLength;
		$text		= self::trim( trim( $text ), $toLength, $trimCentric ? 2 : 0 );
		if( in_array( (string) $float, array( "2", 'center', 'centric' ) ) )
			return self::extendTextCentric( $text, $toLength );
		$floatRight	= $float === FALSE || $float === 0 || $float === 'right';
		return self::extend( $text, $toLength, $floatRight );
	}

	/**
	 *	Indents all other lines of a multi line string.
	 *	Wraps each line to an optionally given maximum line length before.
	 *	@static
	 *	@access		public
	 *	@param		string		$text			One or multi line text to extend
	 *	@param		integer		$toLength		Maximum line length
	 *	@param		integer		$float			Mode: left(1), right(0), center(2)
	 *	@param		boolean		$trimCentric	Flag: trim text centric
	 *	@return		string
	 */
	static public function indent( $text, $indentLength, $lineLength = NULL ){
		if( $lineLength )
			$text	= wordwrap( $text, $lineLength );
		$lines	= explode( "\n", $text );
		$indent	= str_repeat( " ", $indentLength );
		$text	= implode( "\n".$indent, $lines );
		return $text;
	}

	/**
	 *	Indents all other lines of a multi line string.
	 *	Wraps each line to an optionally given maximum line length before.
	 *	@static
	 *	@access		public
	 *	@param		string		$text			One or multi line text to extend
	 *	@param		integer		$toLength		Maximum line length
	 *	@param		integer		$float			Mode: left(1), right(0), center(2)
	 *	@param		boolean		$trimCentric	Flag: trim text centric
	 *	@return		string
	 */
	static public function line( $sign = "-", $lineLength = 76 ){
		$steps	= floor( $lineLength / mb_strlen( $sign ) );
		return str_repeat( $sign, $steps );
	}

	/**
	 *	Cuts a one line string from left, right or centric to a maximum length.
	 *	With multi-byte support.
	 *	@static
	 *	@access		public
	 *	@param		string		$text			One line string to extend
	 *	@param		integer		$toLength		Maximum string length
	 *	@param		integer		$float			Mode: left(1), right(0), center(2)
	 *	@param		boolean		$trimCentric	Flag: trim text centric
	 *	@return		string
	 */
	static public function trim( $text, $length, $mode = 2 ){
		if( $mode === 2 || $mode === 'center' || $mode === 'centric' )
			return Alg_Text_Trimmer::trimCentric( $text, $length );
		return Alg_Text_Trimmer::trim( $text, $length, $mode );
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
	static public function underscore( $text, $withString = '-', $maxLength = 76 ){
		$text	= array_pop( explode( "\n", $text ) );
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
?>
