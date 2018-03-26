<?php
class Logic_Shortcode extends CMF_Hydrogen_Logic{

	protected $moduleConfig;
	protected $pattern			= "/^(.*)(\[##shortcode##( [^\]]+)?\])(.*)$/sU";

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
			$code		= preg_replace( '/(\r|\n|\t)/', " ", $code );
			$code		= preg_replace( '/( ){2,}/', " ", $code );
			$code		= str_replace( ':', "_", trim( $code ) );
			$node		= new XML_Element( '<'.substr( $code, 1, -1 ).'/>' );
			$attr		= array_merge( $defaultAttributes, $node->getAttributes() );
			return $attr;
		}
		return FALSE;
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
