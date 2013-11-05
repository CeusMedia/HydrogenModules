<?php
class View_Helper_TimePhraser{

	public function __construct( CMF_Hydrogen_Environment_Abstract $env ){
		$this->env	= $env;
	}

	public function convert( $timestamp, $asHtml = FALSE ){
		$helper	= new CMF_Hydrogen_View_Helper_Timestamp( $timestamp );
		return $helper->toPhrase( $this->env, $asHtml, 'timephraser', 'phrases-time' );
	}
}
?>