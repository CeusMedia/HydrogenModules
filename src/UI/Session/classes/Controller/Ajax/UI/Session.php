<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Ajax_UI_Session extends Controller
{
	public function keepAlive(): never
	{
		exit;
/*		$config		= $this->env->getConfig();
		$session	= $this->env->getSession();
		$isInside	= $session->has( 'userId' );
		switch( $config->get( 'module.ui_session.keepAlive.keepAliveFor' ) ){
			case 'users':
				if( $this->env->getSession()->has( 'userId' ) ){

				}
				break;
			case 'all':
			case 'visitors':
				break;
		}
		exit;*/
	}
}
