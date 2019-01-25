<?php
class View_Info_Workshop extends CMF_Hydrogen_View{

	protected function __onInit(){
		$this->env->getPage()->js->addScriptOnReady( 'ModuleInfoWorkshop.init();' );
	}

	public function index(){
	}

	public function view(){
	}
}
