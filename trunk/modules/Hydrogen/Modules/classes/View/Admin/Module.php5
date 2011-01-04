<?php
class View_Admin_Module extends CMF_Hydrogen_View{
	protected function onInit(){
		$this->env->page->addThemeStyle( 'site.admin.module.css' );
	}
	public function index(){}
	public function view(){}
}
?>
