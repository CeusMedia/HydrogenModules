<?php
class Controller_Labs_Disclosure extends CMF_Hydrogen_Controller{
	public function index(){}

	public function clearCache(){
		$this->env->getPage()->js->clearCache();
		$this->redirect( 'labs/disclosure' );
	}
}
?>
