<?php

use CeusMedia\HydrogenFramework\View;

class View_Manage_Form_Fill extends View{

	protected function __onInit(){
		$this->env->getPage()->addThemeStyle( 'module.manage.forms.css' );
	}

	public function index(){}
	public function view(){}
}
