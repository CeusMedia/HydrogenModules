<?php

use CeusMedia\Common\ADT\Collection\Dictionary;
use CeusMedia\HydrogenFramework\Logic;

class Logic_Shortcode extends Logic
{
	public const PARSE_STATUS_START					= 0;
	public const PARSE_STATUS_READ_CODE				= 1;
	public const PARSE_STATUS_READ_ATTR_KEY			= 2;
	public const PARSE_STATUS_READ_ATTR_VALUE_QUOTE	= 3;
	public const PARSE_STATUS_READ_ATTR_VALUE		= 4;
	public const PARSE_STATUS_FINAL					= 5;

	public const PARSE_STATUSES						= [
		self::PARSE_STATUS_START,
		self::PARSE_STATUS_READ_CODE,
		self::PARSE_STATUS_READ_ATTR_KEY,
		self::PARSE_STATUS_READ_ATTR_VALUE_QUOTE,
		self::PARSE_STATUS_READ_ATTR_VALUE,
		self::PARSE_STATUS_FINAL,
	];

	protected ?string $content			= NULL;
	protected array $ignoredBlocks		= [];
	protected Dictionary $moduleConfig;
	protected string $pattern				= "/^(.*)(\[##shortcode##(\s[^\]]+)?\])(.*)$/sU";

	/**
	 *	@param		string		$shortCode
	 *	@param		array		$defaultAttributes
	 *	@param		string		$defaultMode
	 *	@return		bool|array
	 *	@throws		Exception
	 */
	public function find( string $shortCode, array $defaultAttributes = [], string $defaultMode = 'allow' ): bool|array
	{
		$mode	= $this->moduleConfig->get( 'mode' );
		if( !in_array( $mode, ['allow', 'deny'] ) )
			$mode	=  $defaultMode;
		$allow	= preg_split( '/, */', $this->moduleConfig->get( 'allow' ) );
		$deny	= preg_split( '/, */', $this->moduleConfig->get( 'deny' ) );
		if( 'deny' === $mode && !in_array( $shortCode, $allow ) )
			return FALSE;
		if( 'allow' === $mode && in_array( $shortCode, $deny ) )
			return FALSE;

		$pattern		= $this->getShortCodePattern( $shortCode );
		if( preg_match( $pattern, $this->content ) ){
			$code		= preg_replace( $pattern, "\\2", $this->content );
			$shortcode	= (object) $this->parse( $code );
			return array_merge( $defaultAttributes, $shortcode->attributes );
		}
		return FALSE;
	}

	/**
	 *	@return		string
	 */
	public function getContent(): string
	{
		if( !$this->ignoredBlocks )
			return $this->content;
		$content	= $this->content;
		foreach( $this->ignoredBlocks as $ignoredBlock ){
			$regExp		= '@'.preg_quote( '[shortcode:__ignoredBlock]', '@' ).'@';
			$content	= preg_replace( $regExp, $ignoredBlock, $content, 1 );
		}
		return $content;
	}

	/**
	 *	@param		string		$shortCode
	 *	@return		string
	 */
	public function has( string $shortCode ): string
	{
		return preg_match( $this->getShortCodePattern( $shortCode ), $this->content );
	}

	/**
	 *	Remove next shortcode appearance in content.
	 *	Removal will be applied to set content.
	 *	@access		public
	 *	@param		string		$shortCode		Shortcode to remove
	 *	@return		self
	 */
	public function removeNext( string $shortCode ): self
	{
		return $this->replaceNext( $shortCode, '' );
	}

	/**
	 *	Replaces next shortcode appearance in content by replacement code.
	 *	Replacement will be applied to set content.
	 *	@access		protected
	 *	@param		string		$shortCode		Shortcode to insert content for
	 *	@param		string		$replacement	Content to insert instead of shortcode-
	 *	@throws		RuntimeException			if no content has been set
	 *	@return		self
	 */
	public function replaceNext( string $shortCode, string $replacement ): self
	{
		if( !is_string( $this->content ) )
			throw new RuntimeException( 'No content set' );
		$pattern		= $this->getShortCodePattern( $shortCode );
		$replacement	= "\\1".$replacement."\\4";													//  insert content of nested page...
		$this->content	= preg_replace( $pattern, $replacement, $this->content, 1 );				//  apply replacement once
		return $this;
	}

	/**
	 *	Set content to process.
	 *	Will blind ignorable blocks.
	 *	@access		public
	 *	@param		string			$content		Content to process on
	 *	@return		self
	 */
	public function setContent( string $content ): self
	{
		if( substr_count( $content, '<!--noShortcode-->' ) ){										//  there are blocks to ignore
			$this->ignoredBlocks	= [];														//  reset list of ignored blocks
			$intro	= preg_quote( '<!--noShortcode-->', '@' );										//  prepare start string for regex
			$outro	= preg_quote( '<!--/noShortcode-->', '@' );										//  prepare stop string for regex
			$regExpReplace	= '@^(.*)('.$intro.')(.+)('.$outro.')(.*)@su';							//  prepare regex for matching and replacing
			$replacement	= '\\1[shortcode:__ignoredBlock]\\5';									//  prepare replacement
			while( preg_match( $regExpReplace, $content ) ){										//  iterate ignorable blocks
				$this->ignoredBlocks[]	= preg_replace( $regExpReplace, '\\2\\3\\4', $content );	//  note ignored block content
				$content	= preg_replace( $regExpReplace, $replacement, $content );				//  replace block by placeholder
			}
		}
		$this->content	= $content;
		return $this;
	}

	//  --  PROTECTED  --  //

	/**
	 *	@return		void
	 */
	protected function __onInit(): void
	{
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.ui_shortcode.', TRUE );
	}

	/**
	 *	Returns regular expression to find shortcode blocks for given shortcode.
	 *	@access		protected
	 *	@param		string			$shortCode		Shortcode to get regular expression for
	 *	@return		string
	 */
	protected function getShortCodePattern( string $shortCode ): string
	{
		if( !strlen( trim( $shortCode ) ) || preg_match( "/\n/", $shortCode ) )
			throw new InvalidArgumentException( 'Invalid shortcode given' );
		return str_replace( "##shortcode##", preg_quote( $shortCode, '/' ), $this->pattern );
	}

	/**
	 *	Parses shortcode string and tries to read parameters.
	 *	Returns map containing node name and attributes.
	 *	@access		protected
	 *	@param		string			$string
	 *	@return		array
	 *	@throws		RuntimeException
	 */
	protected function parse( string $string ): array
	{
		$status			= self::PARSE_STATUS_START;
		$position		= 0;
		$nodename		= '';
		$attributes		= [];
		$length			= strlen( $string );
		$bufferAttrKey	= '';
		$bufferAttrVal	= '';
		while( $position < $length ){
			$char	= $string[$position];
			if( self::PARSE_STATUS_START === $status ){
				if( $char !== "[" )
					throw new RuntimeException( 'Must start with [' );
				$status = self::PARSE_STATUS_READ_CODE;
			}
			else if( self::PARSE_STATUS_READ_CODE === $status ){
				if( in_array( $char, [" ", "\n"] ) ){
					$bufferAttrKey	= '';
					$status	= self::PARSE_STATUS_READ_ATTR_KEY;
				}
				else{
					$nodename	.= $char;
				}
			}
			else if( self::PARSE_STATUS_READ_ATTR_KEY === $status ){
				if( in_array( $char, [" ", "\n"] ) ){
					if( $bufferAttrKey ){
						$attributes[$bufferAttrKey] = TRUE;
						$bufferAttrKey	= '';
						continue;
					}
				}
				else if( '=' === $char ){
					$status	= self::PARSE_STATUS_READ_ATTR_VALUE_QUOTE;
				}
				else if( ']' === $char ){
					if( $bufferAttrKey )
						$attributes[$bufferAttrKey] = TRUE;
					$status	= self::PARSE_STATUS_FINAL;
					break;
				}
				else if( preg_match( '/[a-z0-9_-]/', $char ) ){
					$bufferAttrKey	.= $char;
				}
			}
			else if( self::PARSE_STATUS_READ_ATTR_VALUE_QUOTE === $status  ){
				if( '"' !== $char )
					throw new RuntimeException( 'Attribute value must be double quoted' );
				$bufferAttrVal	= '';
				$status	= self::PARSE_STATUS_READ_ATTR_VALUE;
			}
			else if( self::PARSE_STATUS_READ_ATTR_VALUE === $status ){
				if( '"' === $char ){
					$attributes[$bufferAttrKey] = $bufferAttrVal;
					$bufferAttrKey	= '';
					$status	= self::PARSE_STATUS_READ_ATTR_KEY;
				}
				else{
					$bufferAttrVal	.= $char;
				}
			}
			if( self::PARSE_STATUS_FINAL === $status ){
				break;
			}
			$position++;
		}
		return [
			'nodename'		=> $nodename,
			'attributes'	=> $attributes,
		];
	}
}
