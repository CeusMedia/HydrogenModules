<?php

use CeusMedia\HydrogenFramework\Controller;

class Controller_Index extends Controller
{
	public function index( $arg1 = NULL, $arg2 = NULL, $arg3 = NULL, $arg4 = NULL, $arg5 = NULL, $arg6 = NULL )
	{

		//  redirect forced by auth module ?
		$forward	= $this->env->getConfig()->getAll( 'module.resource_authentication.login.forward.', TRUE );
		if( $forward->get( 'path' ) && $forward->get( 'force' ) ){
			$isInside	= $this->env->getSession()->get( 'auth_user_id' ) > 0;
			if( $isInside ){
				$hasAccess	= $this->env->getAcl()->has( $forward->get( 'path' ), '' );
				$this->restart( $forward->get( 'path' ) );
			}
		}

		if( $arg1 ){
			$this->env->getResponse()->setStatus( 404 );
		}
		$userId		= $this->env->getSession()->get( 'auth_user_id' );
		$user		= NULL;
		if( $userId ){
			$model	= new Model_User( $this->env );
			$user	= $model->get( $userId );
			$this->env->getMessenger()->noteNotice( 'Hallo '.$user->username.'!' );
		}
		$this->addData( 'user', $user );
		$this->addData( 'sessionId', session_id() );
		$this->addData( 'sessionData', $this->env->getSession()->getAll() );
	}
}
