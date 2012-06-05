<?php
class View_Admin_Module_Viewer extends View_Admin_Module {

	protected function __onInit(){
		$this->env->getLanguage()->load( 'admin/module' );
		
	}

	public function index(){}

	public function view(){}
}
?>