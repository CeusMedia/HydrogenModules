<?php
class View_Admin_Module_Installer extends View_Admin_Module {

	protected function __onInit(){
		$this->env->getLanguage()->load( 'admin/module' );
	}

	public function index(){
	}
	
	public function view(){
		$words		= $this->env->getLanguage()->getWords( 'admin/module' );

		$moduleId	= $this->getData( 'moduleId' );
		$modules	= $this->getData( 'modules' );
		if( isset( $modules[$moduleId] ) )
			$this->env->getPage()->setTitle( $modules[$moduleId]->title, 'append' );

		$this->addData( 'wordsTypes', $words['types'] );
	}

	public function update(){
		$words		= $this->env->getLanguage()->getWords( 'admin/module' );

		$moduleId	= $this->getData( 'moduleId' );
		$modules	= $this->getData( 'modulesAvailable' );
		if( isset( $modules[$moduleId] ) )
			$this->env->getPage()->setTitle( $modules[$moduleId]->title, 'append' );

		$this->addData( 'wordsTypes', $words['types'] );
	}
}
?>
