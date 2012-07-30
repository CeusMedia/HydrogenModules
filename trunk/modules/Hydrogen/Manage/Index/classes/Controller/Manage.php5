<?php
class Controller_Manage extends CMF_Hydrogen_Controller{

	public function index(){
		$config		= $this->env->getConfig();
		if( $config->get( 'module.manage_index.forward' ) )
			$this->restart( './'.$config->get( 'module.manage_index.forward' ) );
	}
}
?>