<?php
class Controller_Admin_Config extends CMF_Hydrogen_Controller {
	public function index(){
		$modules	= $this->env->getModules()->getAll();
/*		foreach( $modules as $moduleId => $module ){
			if( $module->config ){
				$list[$moduleId]	= $module->config;
			}
		}
		$this->addData( 'config', $list );*/
//		$this->addData( 'config', $this->env->getConfig()->getAll() );
		$this->addData( 'config', $modules );
	}
}
