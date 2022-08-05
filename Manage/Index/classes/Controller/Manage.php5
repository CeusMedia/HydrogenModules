<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Manage extends Controller
{
	public function index()
	{
		$config		= $this->env->getConfig();
		if( $config->get( 'module.manage_index.forward' ) )
			$this->restart( './'.$config->get( 'module.manage_index.forward' ) );
	}
}
