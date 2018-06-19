<?php
class View_Helper_TimePhraser{

	public function __construct( CMF_Hydrogen_Environment $env ){
		$this->env	= $env;
	}

	public function convert( $timestamp, $asHtml = FALSE, $prefix = NULL, $suffix = NULL ){
		$helper	= new CMF_Hydrogen_View_Helper_Timestamp( $timestamp );
		$phrase	= $helper->toPhrase( $this->env, $asHtml, 'timephraser', 'phrases-time' );
		if( (int) $timestamp ){
			$phrase	= $prefix ? $prefix.' '.$phrase : $phrase;
			$phrase	= $suffix ? $phrase.' '.$suffix : $phrase;
		}
		return $phrase;
	}

	static public function convertStatic( CMF_Hydrogen_Environment $env, $timestamp, $asHtml = FALSE, $prefix = NULL, $suffix = NULL ){
		$helper	= new self( $env );
		return $helper->convert( $timestamp, $asHtml, $prefix, $suffix );
	}
}
?>
