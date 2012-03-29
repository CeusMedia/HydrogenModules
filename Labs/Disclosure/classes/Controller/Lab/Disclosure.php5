<?php
class Controller_Lab_Disclosure extends CMF_Hydrogen_Controller{
	public function index(){}

	public function clearCache(){
		$this->env->getPage()->js->clearCache();
		$this->redirect( 'lab/disclosure' );
	}
}
?>
