<?php
class Controller_Admin_Config extends CMF_Hydrogen_Controller {
	public function index(){
		$this->addData( 'config', $this->env->getConfig()->getAll() );
	}
}
