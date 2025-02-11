<?php

use CeusMedia\HydrogenFramework\Controller\Ajax as AjaxController;

class Controller_Ajax_Csrf extends AjaxController
{
	protected Logic_CSRF $logic;

	/**
	 *	@return		void
	 *	@throws		JsonException
	 */
	public function getToken(): void
	{
		$formName	= $this->request->get( 'formName' );
		try{
			if( !$formName )
				throw new InvalidArgumentException( 'Form name is missing' );
			$token	= $this->logic->getToken( $formName );
			$this->respondData( ['token' => $token] );
		}
		catch( Throwable $t ){
			$this->respondException( $t );
		}
	}

	/**
	 *	@return		void
	 *	@throws		ReflectionException
	 */
	protected function __onInit(): void
	{
		$this->logic	= Logic_CSRF::getInstance( $this->env );
	}
}
