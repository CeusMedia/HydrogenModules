<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Csrf extends AjaxController
{
	protected $logic;

	public function getToken()
	{
		$formName	= $this->request->get( 'formName' );
		try{
			if( !$formName )
				throw new InvalidArgumentException( 'Form name is missing' );
			$token	= $this->logic->getToken( $formName );
			$this->respondData( ['token' => $token] );
		}
		catch( Exception $e ){
			$this->respondException( $e );
		}
	}

	protected function __onInit(): void
	{
		$this->logic	= $this->env->getLogic()->get( 'CSRF' );
	}
}
