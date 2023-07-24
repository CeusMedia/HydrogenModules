<?php

use CeusMedia\HydrogenFramework\View;

class View_Manage_Download extends View{

	public function index(){
		$this->env->getPage()->addThemeStyle( 'module.manage.downloads.css' );
	}

	public function view(){}
}
