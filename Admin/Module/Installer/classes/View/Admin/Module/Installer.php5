<?php
class View_Admin_Module_Installer extends View_Admin_Module {

	protected function __onInit(){
		$this->env->getLanguage()->load( 'admin/module' );
	}

	public function index(){
	}
	
	public function view(){
		$language	= $this->env->getLanguage();
		$language->load( 'admin/module' );
		$words		= $language->getWords( 'admin/module' );
		$this->addData( 'wordsTypes', $words['types'] );
	}
}
?>
