<?php
class View_Admin_Module_Viewer extends View_Admin_Module {

	protected function __onInit(){
		$this->env->getLanguage()->load( 'admin/module' );
		
	}

	public function index(){}

	public function view(){
		$moduleId	= $this->getData( 'moduleId' );
		$modules	= $this->getData( 'modules' );
		if( isset( $modules[$moduleId] ) )
			$this->env->getPage()->setTitle( $modules[$moduleId]->title, 'append' );
	}
}
?>