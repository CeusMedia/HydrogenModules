<?php
class View_Manage_Form extends CMF_Hydrogen_View{

	protected function __onInit(){
		$this->env->getPage()->addThemeStyle( 'module.manage.forms.css' );
	}

	public function add(){}

	public function edit(){}

	public function index(){}

	public function view(){
		$formId	= $this->getData( 'formId' );
		$mode	= $this->getData( 'mode', '' );

		$helper = new View_Helper_Form( $this->env );
		if( $mode )
			$helper->setMode( $mode );
		print $helper->setId( $formId )->render();
		exit;
	}
}
