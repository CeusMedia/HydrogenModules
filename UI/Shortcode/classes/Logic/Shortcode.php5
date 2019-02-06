<?php
class Logic_Shortcode extends CMF_Hydrogen_Logic{

	const PARSE_STATUS_START					= 0;
	const PARSE_STATUS_READ_CODE				= 1;
	const PARSE_STATUS_READ_ATTR_KEY			= 2;
	const PARSE_STATUS_READ_ATTR_VALUE_QUOTE	= 3;
	const PARSE_STATUS_READ_ATTR_VALUE			= 4;
	const PARSE_STATUS_FINAL					= 5;

	protected $content;
	protected $ignoredBlocks		= array();
	protected $moduleConfig;
	protected $pattern				= "/^(.*)(\[##shortcode##(\s[^\]]+)?\])(.*)$/sU";

	public function __onInit(){
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.ui_shortcode.', TRUE );
	}

	public function find( $shortCode, $defaultAttributes = array(), $defaultMode = "allow" ){
		$mode	= $this->moduleConfig->get( 'mode' );
		if( !in_array( $mode, array( 'allow', 'deny' ) ) )
			$mode	=  $defaultMode;
		$allow	= preg_split( '/, */', $this->moduleConfig->get( 'allow' ) );
		$deny	= preg_split( '/, */', $this->moduleConfig->get( 'deny' ) );
		if( $mode === "deny" && !in_array( $shortCode, $allow ) )
			return;
		if( $mode === "allow" && in_array( $shortCode, $deny ) )
			return;

		$pattern		= $this->getShortCodePattern( $shortCode );
		if( preg_match( $pattern, $this->content ) ){
			$code		= preg_replace( $pattern, "\\2", $this->content );
			$shortcode	= $this->parse( $code );
			$attr		= array_merge( $defaultAttributes, $shortcode->attributes );
			return $attr;
		}
		return FALSE;
	}

	public function getContent(){
			if( !$this->ignoredBlocks )
			return $this->content;
		$content	= $this->content;
		foreach( $this->ignoredBlocks as $ignoredBlock ){
			$regExp		= '@'.preg_quote( '[shortcode:__ignoredBlock]', '@' ).'@';
			$content	= preg_replace( $regExp, $ignoredBlock, $content, 1 );
		}
		return $content;
	}

	protected function getShortCodePattern( $shortCode ){
		if( !strlen( trim( $shortCode ) ) || preg_match( "/\n/", $shortCode ) )
			throw new InvalidArgumentException( 'Invalid shortcode given' );
		return str_replace( "##shortcode##", preg_quote( $shortCode, '/' ), $this->pattern );
	}

	public function has( $shortCode, $defaultAttributes = array(), $defaultMode = "allow" ){
		return preg_match( $this->getShortCodePattern( $shortCode ), $this->content );
	}

	protected function parse( $string ){
		$status		= self::PARSE_STATUS_START;
		$position	= 0;
		$nodename	= '';
		$attributes	= array();
		$length		= strlen( $string );
		while( $position < $length ){
			$char	= $string[$position];
			if( $status == self::PARSE_STATUS_START ){
				if( $char !== "[" )
					throw new Exception( 'Must start with [' );
				$status = self::PARSE_STATUS_READ_CODE;
			}
			else if( $status == self::PARSE_STATUS_READ_CODE ){
				if( in_array( $char, array( " ", "\n" ) ) ){
					$bufferAttrKey	= '';
					$status	= self::PARSE_STATUS_READ_ATTR_KEY;
				}
				else{
					$nodename	.= $char;
				}
			}
			else if( $status == self::PARSE_STATUS_READ_ATTR_KEY ){
				if( in_array( $char, array( " ", "\n" ) ) ){
					if( $bufferAttrKey ){
						$attributes[$bufferAttrKey] = TRUE;
						$bufferAttrKey	= '';
						$status	= self::PARSE_STATUS_READ_ATTR_KEY;
						continue;
					}
				}
				else if( $char == "=" ){
					$status	= self::PARSE_STATUS_READ_ATTR_VALUE_QUOTE;
				}
				else if( $char == "]" ){
					if( $bufferAttrKey )
						$attributes[$bufferAttrKey] = TRUE;
					$status	= self::PARSE_STATUS_FINAL;
					break;
				}
				else if( preg_match( '/[a-z0-9_-]/', $char ) ){
					$bufferAttrKey	.= $char;
				}
			}
			else if( $status == self::PARSE_STATUS_READ_ATTR_VALUE_QUOTE ){
				if( $char !== '"' )
					throw new Exception( 'Attribute value must be double quoted' );
				$bufferAttrVal	= '';
				$status	= self::PARSE_STATUS_READ_ATTR_VALUE;
			}
			else if( $status == self::PARSE_STATUS_READ_ATTR_VALUE ){
				if( $char == '"' ){
					$attributes[$bufferAttrKey] = $bufferAttrVal;
					$bufferAttrKey	= '';
					$status	= self::PARSE_STATUS_READ_ATTR_KEY;
				}
				else{
					$bufferAttrVal	.= $char;
				}
			}
			if( $status == self::PARSE_STATUS_FINAL ){
				break;
			}
			$position++;
			continue;
		}
		return (object) array(
			'nodename'		=> $nodename,
			'attributes'	=> $attributes,
		);
	}

	/**
	 *	Remove next shortcode appearance in content.
	 *	Removal will be applied to set content.
	 *	@access		protected
	 *	@param		string		$shortCode		Shortcode to remove
	 *	@return		void
	 */
	public function removeNext( $shortCode ){
		return $this->replaceNext( $shortCode, '' );
	}

	/**
	 *	Replaces next shortcode appearance in content by replacement code.
	 *	Replacement will be applied to set content.
	 *	@access		protected
	 *	@param		string		$shortCode		Shortcode to insert content for
	 *	@param		string		$replacement	Content to insert instead of shortcode-
	 *	@return		void
	 */
	public function replaceNext( $shortCode, $replacement ){
		if( !is_string( $this->content ) )
			throw new RuntimeException( 'No content set' );
		if( !is_string( $replacement ) )
			throw new InvalidArgumentException( 'Replacement must be of string' );
		$pattern		= $this->getShortCodePattern( $shortCode );
		$replacement	= "\\1".$replacement."\\4";													//  insert content of nested page...
		$this->content	= preg_replace( $pattern, $replacement, $this->content, 1 );				//  apply replacement once
	}

	/**
	 *	Set content to process.
	 *	Will blind ignorable blocks.
	 *	@access		public
	 *	@param		string			$content		Content to process on
	 *	@return		void
	 */
	public function setContent( $content ){
		if( substr_count( $content, '<!--noShortcode-->' ) ){										//  there are blocks to ignore
			$this->ignoredBlocks	= array();														//  reset list of ignored blocks
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
	}
}
