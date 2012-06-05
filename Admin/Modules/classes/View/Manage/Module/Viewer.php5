<?php
class View_Manage_Module_Viewer extends View_Manage_Module {

	protected function __onInit(){
		$this->env->getLanguage()->load( 'manage/module' );
		
	}

	public function index(){}

	public function view(){}
}
?>