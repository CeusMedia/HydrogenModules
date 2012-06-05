<?php
class View_Admin_Module_Source extends CMF_Hydrogen_View{

	protected function __onInit(){
		$this->env->getPage()->js->addUrl( $this->env->getConfig()->get( 'path.scripts' ).'site.admin.module.source.js' );
		$this->env->getPage()->addThemeStyle( 'site.admin.module.source.css' );
	}

	public function index(){}

	public function add(){}

	public function edit(){}

	public function remove(){}
}
?>