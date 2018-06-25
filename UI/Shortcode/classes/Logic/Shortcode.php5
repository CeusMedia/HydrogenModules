<?php
class Logic_Shortcode extends CMF_Hydrogen_Logic{

	protected $moduleConfig;
	protected $pattern			= "/^(.*)(\[##shortcode##(\s[^\]]+)?\])(.*)$/sU";

	protected function getShortCodePattern( $shortCode ){
		return str_replace( "##shortcode##", preg_quote( $shortCode, '/' ), $this->pattern );
	}

	public function __onInit(){
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.ui_shortcode.', TRUE );
	}

	public function has( $content, $shortCode, $defaultAttributes = array(), $defaultMode = "allow" ){
		return preg_match( $this->getShortCodePattern( $shortCode ), $content );
	}

	public function find( $content, $shortCode, $defaultAttributes = array(), $defaultMode = "allow" ){
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
		if( preg_match( $pattern, $content ) ){
			$code		= preg_replace( $pattern, "\\2", $content );
/*			$code		= preg_replace( '/(\r|\n|\t)/', " ", $code );
			$code		= preg_replace( '/( ){2,}/', " ", $code );
			$code		= preg_replace( '/^([\S]+):([\S]+)[\s]/', "\\1_\\2 ", $code );
			$code		= preg_replace( '/(=")([^"]*)(")/', "\\1<![CDATA[[\\2]]>\\3", $code );
			$code		= str_replace( ':', "<![CDATA[:]]>", trim( $code ) );
			$code		= str_replace( ':', "_", trim( $code ) );
			$node		= new XML_Element( '<'.substr( $code, 1, -1 ).'/>' );
			$attr		= array_merge( $defaultAttributes, $node->getAttributes() );*/

			$shortcode	= $this->parse( $code );
//	xmp( $code );
//	print_m( $shortcode );
//	die;
			$attr		= array_merge( $defaultAttributes, $shortcode->attributes );

			return $attr;
		}
		return FALSE;
	}

	protected function parse( $string ){
		$status		= 0;
		$position	= 0;
		$nodename	= '';
		$attributes	= array();
		$length		= strlen( $string );
		while( $position < $length ){
			$char	= $string[$position];
//print( 'lvl: '.$status.' | char: '.$char.'<br/>' );
			if( $status == 0 ){
				if( $char !== "[" )
					throw new Exception( 'Must start with [' );
				$status = 1;
			}
			else if( $status == 1 ){
				if( in_array( $char, array( " ", "\n" ) ) ){
					$bufferAttrKey	= '';
					$status	= 2;
				}
				else{
					$nodename	.= $char;
				}
			}
			else if( $status == 2 ){
				if( in_array( $char, array( " ", "\n" ) ) ){}
				else if( $char == "=" ){
					$status	= 3;
				}
				else if( $char == "]" ){
					$status	= 5;
					break;
				}
				else if( preg_match( '/[a-z0-9_-]/', $char ) ){
					$bufferAttrKey	.= $char;
				}
			}
			else if( $status == 3 ){
				if( $char !== '"' )
					throw new Exception( 'Attribute value must be double quoted' );
				$bufferAttrVal	= '';
				$status	= 4;
			}
			else if( $status == 4 ){
				if( $char == '"' ){
					$attributes[$bufferAttrKey] = $bufferAttrVal;
					$bufferAttrKey	= '';
					$status	= 2;
				}
				else{
					$bufferAttrVal	.= $char;
				}
			}
			if( $status == 5 ){
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


	public function replaceNext( $content, $shortCode, $replacement ){
		if( !is_string( $content ) )
			throw new InvalidArgumentException( 'Content must be of string' );
		if( !is_string( $replacement ) )
			throw new InvalidArgumentException( 'Replacement must be of string' );
		$pattern		= $this->getShortCodePattern( $shortCode );
		$replacement	= "\\1".$replacement."\\4";													//  insert content of nested page...
		return preg_replace( $pattern, $replacement, $content, 1 );									//  apply replacement once
	}
}
