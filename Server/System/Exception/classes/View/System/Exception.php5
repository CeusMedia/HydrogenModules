<?php
class View_System_Exception extends CMF_Hydrogen_View{
	public function index(){
		$this->env->getPage()->addCommonStyle( 'module.server.system.exception.css' );
	}
}
