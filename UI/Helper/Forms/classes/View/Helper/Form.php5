<?php
class View_Helper_Form/* extends CMF_Hydrogen_View_Helper*/{

	public function __construct( $env ){
		$this->env	= $env;
	}

	public function render(){

	}

	public function renderStatic( $env ){
		$helper	= new View_Helper_Form( $env );
		return $helper->render();
	}
}
