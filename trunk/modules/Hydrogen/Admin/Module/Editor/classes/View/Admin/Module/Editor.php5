<?php
class View_Admin_Module_Editor extends View_Manage_Module {

	protected function __onInit(){
		$this->env->getLanguage()->load( 'admin/module' );
	}

	public function edit(){}
	
	public function index(){}
	
	public function view(){}
}
?>
