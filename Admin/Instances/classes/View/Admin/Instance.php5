<?php
class View_Admin_Instance extends CMF_Hydrogen_View{

	protected function __onInit(){
		$this->env->getPage()->addThemeStyle( 'module.admin.instances.css' );
	}

	public function index(){}

	public function add(){}

	public function edit(){}

	public function remove(){}
}
?>