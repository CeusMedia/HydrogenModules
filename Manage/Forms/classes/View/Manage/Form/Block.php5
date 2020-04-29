<?php
class View_Manage_Form_Block extends CMF_Hydrogen_View{

	protected function __onInit(){
		$this->env->getPage()->addThemeStyle( 'module.manage.forms.css' );
	}

	public function add(){}
	public function edit(){}
	public function index(){}
	public function view(){}
}
