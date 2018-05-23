<?php
class View_Manage_Form extends CMF_Hydrogen_View{

	public function add(){}
	public function edit(){}
	public function index(){}
	public function view(){
		$formId	= $this->getData( 'formId' );
		$helper = new View_Helper_Form( $this->env );
		print $helper->setId( $formId )->render();
		exit;
	}
}
