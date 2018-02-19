<?php
class Logic_Shortcode extends CMF_Hydrogen_Logic{

	protected $moduleConfig;

	public function __onInit(){
		$this->moduleConfig	= $this->env->getConfig()->getAll( 'module.ui_shortcode.', TRUE );
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

		$pattern		= "/^(.*)(\[".preg_quote( $shortCode, '/' )."([^\]]+)?\])(.*)$/sU";
		if( preg_match( $pattern, $content ) ){
			$code		= preg_replace( $pattern, "\\2", $data->content );
			$code		= preg_replace( '/(\r|\n|\t)/', " ", $code );
			$code		= preg_replace( '/( ){2,}/', " ", $code );
			$code		= str_replace( ':', "_", trim( $code ) );
			$node		= new XML_Element( '<'.substr( $code, 1, -1 ).'/>' );
			$attr		= array_merge( $defaultAttr, $node->getAttributes() );
			return $attr;
		}
		return FALSE;
	}

	public function replaceNext( $content, $shortCode, $replacement ){
		$pattern		= "/^(.*)(\[".preg_quote( $shortCode, '/' )."([^\]]+)?\])(.*)$/sU";
		$replacement	= "\\1".$replacement."\\4";													//  insert content of nested page...
		return preg_replace( $pattern, $replacement, $content, 1 );									//  apply replacement once
	}
}
